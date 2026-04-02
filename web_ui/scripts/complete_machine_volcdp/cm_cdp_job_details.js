var cmCdpJobDetails = function () {

    var initChartFlag = false; //任务流量图初始化标志
    var _chartTabSelected = false;_dataFlowSelected = false;
    var myChart;
    var initDevGridFlag = false;
    var expandIndex = null;
    var devColums = [];
    var ztreeDetails, ztreeId, zTreeSetting, zTreeData;
    var _tempConsoleUrl = "";
    var _vmUuid = "";

    const RESTORE_MODE = {
        'CM_RESTORE': 1,  //整机恢复
        'VOL_RESTORE': 2,  //卷恢复
    };
    //任务相关状态、阶段等信息
    var taskInfo = {
        "auto_takeover_enable_flag":CONF.FLAG.UNKNOW,  //_autoTtakeoverEnableFlag
        "current_stage":0,  //任务运行阶段
        "task_status":0,  //任务状态
        "task_type":0,  //任务类型
        "task_uuid": "",  //任务uuid
        "data_source_agent": "", //数据源客户端
        "data_source_agent_uuid":'', //数据源客户端UUID

        "backup_server": "", //备份服务器
        "target_agent": "",  //目标客户端
        "target_agent_uuid":'', //目标客户端UUID
        "temp_console_url":"", //接管备机为内嵌时对应console地址
        'backup_task_takeover_conf': CONF.FLAG.UNSET,  //备份（复制）任务是否配置接管
        'speed':0,
        'is_replication_task':false,  //是否为复制任务
        'failbackup_ip':"", //回切故障IP
    };
    var vmTemplateEmbedQemuKvm = 108;  //容灾演练平台
	var _agentNetworkInfo = [];  //主机客户端对应的网卡信息
	var _standbyNetworkInfo = []; //备机客户端对应的网卡信息
    var _takeoverHisBusinessIpMap = "";
    var _standbyGatewayConf = [];
    var os_type = ''; //操作系统
    var _failbackHisBusinessIpMap = ''; //回切网络配置
    var columnData = [];
    let initListener = function(){
        $('#takeoverFailbackConf').unbind('click').bind('click', cmCdpTaskFailbackConf);
        $('#taskTakeoverNetworkConf').unbind('click').click(taskTakeoverNetworkConf);
        $('.tempagentconfherf').off('click').on('click', function(event) {
            event.preventDefault();
            getTakeoverVmConfigInfo();
        });
        $('#details_more_failback').off().click(parseFailbackConfig);
    }
    /**
     * 接管传输网络配置信息
     */
    let taskTakeoverNetworkConf = function(){
        var p = {};
	   	p.task_uuid = $("#task_uuid").val();
    	p.task_type = taskInfo.task_type;
    	p.failbackhost = "";
        getTaskNetCardHistoryConf();
    	Metronic.blockUI({target: '#jobDetailDiv',animate: true});
        pAjaxRequest(p, "/api/v1/complete_machine_volcdp/jobs/network_conf", "GET", function (d) {
            Metronic.unblockUI('#jobDetailDiv');
            if(d.success){
                let data = d.data;
                $('.cdptaskTakeoverNetworkConfModal').modal({'width':'850px', 'height':'460px'});
    			_agentNetworkInfo = data['agent_nic_list'];
    			_standbyNetworkInfo = data['standby_nic_list'];

        		$('#task_takeover_ip_server_conf').takeoverIpServerMapConfig({
        			agentNetwork:_agentNetworkInfo,
        			standbyNetwork: _standbyNetworkInfo,
        			takeoverHisNetwork:_takeoverHisBusinessIpMap,
        			failbackHisNetwork:[],
					failbackupCationIp:taskInfo.failback_ip,
					failbackupCationConf:false,
    	  		});

    	  		$('#task_standby_gateway_conf').takeoverStandbyGatewayConfig({
					agentNetwork:_agentNetworkInfo,
					standbyNetwork: _standbyNetworkInfo,
					takeoverHisNetwork:_takeoverHisBusinessIpMap,
					failbackHisNetwork:[],
					failbackupCationIp:'',
					failbackupCationConf:false,
    	  		});
            }
        }, true);
    	$("#submit_task_takeover_network_conf").unbind('click').click(submitTaskHostNetworkConf);
    }
    /**
	 * 修改任务网络配置
	 */
	var submitTaskHostNetworkConf = function(){
		var taskIpMapInfoList = [];

		var standbyNetCardConfList = standbyNetCardConf();
		for(var i=0;i<_agentNetworkInfo.length;i++){
			var ipMapInfo = {};
			var nicIpList = [];
			var agentMapInfo  = {};
			ipMapInfo.nic_gateway = _agentNetworkInfo[i].gateway_address;
			ipMapInfo.nic_name = _agentNetworkInfo[i].name;
			ipMapInfo.nic_mac = _agentNetworkInfo[i].mac_address;

			var ipSet = _agentNetworkInfo[i].ip_set;
			if(!ipSet){
				break;
			}
			for(var j=0;j<ipSet.length;j++){
				var nicIpInfo = {};
				var standbyNetCardSelect =  "standby_network_card_"+i+j;
				var hostIp = $("#"+standbyNetCardSelect).find("option:selected").attr("host-ipdrr");
				var netMask = $("#"+standbyNetCardSelect).find("option:selected").attr("agent-netmask");
				var targetGateway = $("#"+standbyNetCardSelect).find("option:selected").attr("standby-gateway");
				var targetNicName = $("#"+standbyNetCardSelect).find("option:selected").text();
				var targetHostMac = $("#"+standbyNetCardSelect).val();
				if(hostIp){
					nicIpInfo.ip = hostIp;
					nicIpInfo.netmask = netMask;
					nicIpInfo.target_nic_name = targetNicName;
					nicIpInfo.target_nic_mac = targetHostMac;
					var result = standbyNetCardConfList.some(item=>{
						 if(item.selectNicname==targetNicName){
							 targetGateway = item.selectGateway;
						 }
					});
					nicIpInfo.target_gateway = targetGateway;
					nicIpList.push(nicIpInfo);
				}
			}
			if(nicIpList.length>0){
				ipMapInfo.nic_ip_info = nicIpList;
				agentMapInfo.agent_map_info = ipMapInfo;
				taskIpMapInfoList.push(agentMapInfo);
				// _takeoverIpMapListView.push(agentMapInfo);
			}
		}
		if(taskIpMapInfoList.length>0){
			_takeoverIpMapList = taskIpMapInfoList;
		}
		_standbyGatewayConf = getStandbyGateWayConf();
		var checkIp = checkIpFormat(_standbyGatewayConf);
		if(checkIp){  //校验IP正确性，需要同时满足IPV4和IPV6
			var takeoverBusinessIpMap = groupTakeoverBusinessIpMap();
			modifyTaskTakeoverNetwork(takeoverBusinessIpMap);
		}else{
			_takeoverIpMapList = [];
			UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP,LANG.UI_VOL_CDP_STANDBY_DEFAULT_NETCARD_FOFMAT_ERROR);
			return ;
		}
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
	 * 修改任务接管网络配置
	 */
	var modifyTaskTakeoverNetwork = function(takeoverBusinessIpMap){
		var taskNetcardInfo = {};
		taskNetcardInfo.takeover_business_ip_map = takeoverBusinessIpMap;
		taskNetcardInfo.task_uuid = $("#task_uuid").val();
		// var p = JSON.stringify(taskNetcardInfo);
		Metronic.blockUI({target: '.cdptaskTakeoverNetworkConfModal',animate: true});
        pAjaxRequest(taskNetcardInfo, "/api/v1/complete_machine_volcdp/jobs/modify_network_conf", "put", function (d) {
    		Metronic.unblockUI('.cdptaskTakeoverNetworkConfModal');
    		if(d.success){
    			UIToastr.showSuccess(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, d.message);
                getTaskNetCardHistoryConf();
    			$('.cdptaskTakeoverNetworkConfModal').modal('hide');
    		}else{
                UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK_MESSAGE5);
    			return;
            }

    	});
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
     * 获取任务历史网络配合
     */
    var getTaskNetCardHistoryConf = function(){
        var p = {};
        p.task_uuid = $("#task_uuid").val();
        p.task_type = taskInfo.task_type;
        if(taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_TAKEOVER || taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_REPLICATION){
            pAjaxRequest(p, "/api/v1/volcdp/job/network/history_conf", "GET", function (res) {  //获取任务主机网卡信息
                var res = res.data;
                if(res.failback_business_ip_map.length == 0){
                    $('#failback_network_config').html(LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE);
                }
                _takeoverHisBusinessIpMap = res.takeover_business_ip_map; //任务接管网络配置
                _failbackHisBusinessIpMap = res.failback_business_ip_map; //回切网络配置
                if(_failbackHisBusinessIpMap.length != 0){
                    if(_failbackHisBusinessIpMap.length>0){
                        for(var i =0;i < _failbackHisBusinessIpMap.length;i++){
                            var nicName = _failbackHisBusinessIpMap[i].nic_name;
                            var targetNicInfo = _failbackHisBusinessIpMap[i].nic_ip_info;
                            var gatewayStr = "";
                            for(var j= 0;j <targetNicInfo.length;j++){
                                var hostIp = targetNicInfo[j].ip;
                                var targetGateway = targetNicInfo[j].target_gateway;
                                var targetNicName = targetNicInfo[j].target_nic_name;
                                gatewayStr += hostIp+"("+LANG.UI_DRILLS_NETWORK_WAY +":"+ targetGateway +")" + LANG.UI_VOL_CDP_FAILBACK_TARGET_IP_SERVICE_CONF +":"+targetNicName;
                            }
                            var nicConfDes = LANG.UI_VOL_CDP_STANDBY_NETCARD +": " + nicName+"["+gatewayStr+"]";
                            $('#failback_network_config').html(nicConfDes);
                        }
                    }
                }
            });
        }
    }
    /**
     * 获取当前任务基本信息，配置回切任务
     */
    let cmCdpTaskFailbackConf = function(){
        $('#cmCdpFailbackTargetDiskInfo').html('');
        columnData = [];
        let data = {};
        data.jobs_uuid = $("#task_uuid").val();
        pAjaxRequest(data, "/api/v1/complete_machine_volcdp/jobs/failback_devices_list", "GET", function (d) {
            let devList = d.data;
            $.each(devList, function (index, row) {
                columnData.push(row['dev_uuid']);
            });
            $.fn.taskFailbackConf({
                task_info:taskInfo,
                task_dev_list:columnData
            });
        }, true);
    }

    /**
     * 回切配置显示
     */
    let parseFailbackConfig = () => {
        var info = {};
        info.task_uuid = $("#task_uuid").val();;
        info.task_type = taskInfo.task_type;
        Metronic.blockUI({target: '#failbackConfigView',animate: true});
        pAjaxRequest(info, "/api/v1/complete_machine_volcdp/jobs/failback_conf", "GET", function (res) {
            Metronic.unblockUI("#failbackConfigView");
            var msg = res.data;
            let data = msg[0];
            $('#failback_target_host').html(data.failback_target_agent_name);
            if(data.mirror_backup_flag){
                $('#failback_backup_set').html(getFlagLevelInfo(CONF.FLAG.SET));
                $('.failback_target_storage_view').show();
                $('#failback_target_storage').html(data.storage_nickname + "(" + LANG.UI_PUBLIC_TOTAL_SIZE2+": "+data.storage_total_size + " "+LANG.UI_PUBLIC_FREE_SIZE + ":" + data.storage_free_size+")");
            }else{
                $('#failback_backup_set').html(getFlagLevelInfo(CONF.FLAG.UNSET));
                $('.failback_target_storage_view').hide();
            }
            if(data.transport_encrypt_flag){
                $('#failback_encrypt_transfer_switch').html(getFlagLevelInfo(CONF.FLAG.SET));
            }else{
                $('#failback_encrypt_transfer_switch').html(getFlagLevelInfo(CONF.FLAG.UNSET));
            }
            if(data.transport_compress_flag){
                $('#failback_tran_compress_switch').html(getFlagLevelInfo(CONF.FLAG.SET));
                let transport_compress_method_str = '';
                switch (data.transport_compress_method){
                    case 1:
                        transport_compress_method_str = LANG.UI_JOB_COMPRESS_PRIORITY_FASTER;
                        break;
                    case 2:
                        transport_compress_method_str = LANG.UI_JOB_COMPRESS_PRIORITY_NORMAL;
                        break;
                    case 3:
                        transport_compress_method_str = LANG.UI_JOB_COMPRESS_PRIORITY_BETTER;
                        break;
                    case 4:
                        transport_compress_method_str = LANG.UI_JOB_COMPRESS_PRIORITY_BEST;
                        break;
                }
                $('#failback_compress_grade').html(transport_compress_method_str);
                $('.failback_compress_grade_view').show();
            }else{
                $('#failback_tran_compress_switch').html(getFlagLevelInfo(CONF.FLAG.UNSET));
                $('.failback_compress_grade_view').hide();
            }
            $('#failback_transfer_thread_number').html(data.transport_thread_num);
            $('#failback_transfer_datapackage_size').html(data.transport_block_size_des);
            $('#failback_memory_cache').html(data.memory_cache_alloc_space_des);
            if(!!data.memory_cache_alloc_space){
                $('#failback_memory_cache').html(getFlagLevelInfo(CONF.FLAG.SET));
                $('#failback_memory_cache_size').html(data.memory_cache_alloc_space_des);
                $('.failback_memory_cache_size_view').show();
            }else{
                $('#failback_memory_cache').html(getFlagLevelInfo(CONF.FLAG.UNSET));
                $('.failback_memory_cache_size_view').hide();
            }
            let file_cache_storage_path = '';
            if(data.file_cache_storage_path ==""){
                file_cache_storage_path = LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT;
            }else{
                file_cache_storage_path = data.file_cache_storage_path.replace(/\\\\/g, "\\")
            }
            $('#failback_file_cache_path').html(file_cache_storage_path);
            $('#failback_file_cache_size').html(data.file_cache_alloc_space_des);
            let io_replication_mode_str = '';
            switch(data.io_replication_mode){
                case 1:
                    io_replication_mode_str = LANG.UI_CLOUD_PLATFORM_SYNC;
                    break;
                case 2:
                    io_replication_mode_str = LANG.UI_VOL_CDP_BACKUP_ASY;
                    break;
            }
            $('#failback_io_replication_mode').html(io_replication_mode_str);
            let highPressureStrategy = data.high_pressure_strategy;
            let memThreshold = highPressureStrategy.mem_threshold;   //内存最低阈值，低于该值后任务自动停止
            let cbtMemThreshold = highPressureStrategy.cbt_mem_threshold;  //cbt触发阈值，低于该值后切换至cbt模式
            let cbtDetectInterval = highPressureStrategy.cbt_detect_interval;   //cbt期间内存检测间隔，单位min
            let cbtCnableFlag = highPressureStrategy.cbt_enable_flag;   //cbt模式标志：0.未知 1.开启 2.禁用
            if(highPressureStrategy.detect_type == 1){  //按百分比
                $("#failback_stop_task_threshold").html(LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS1 + memThreshold + "% " + "，"+LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS2);
                $('#failback_cmcdp_demotion_threshold').html(LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS1 + cbtMemThreshold + "% " + "，"+ LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS3);
            }else if(highPressureStrategy.detect_type == 2 || highPressureStrategy.detect_type == 3){  //按具体数值
                $("#failback_stop_task_threshold").html(LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS1 + storageCalculateSize(memThreshold) + "，"+LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS2);
                $('#failback_cmcdp_demotion_threshold').html(LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS1 + storageCalculateSize(cbtMemThreshold) + "，"+LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS3);
            }

            $('#failback_cmcdp_demotion_switch').html(getFlagLevelInfo(cbtCnableFlag));  //持续数据保护降级
            $('#failback_recovery_cdpinterval_value').html(cbtDetectInterval+ LANG.UI_MICROSOFT365_MINUTE);  //恢复持续数据保护监控间隔
            if(cbtCnableFlag == CONF.FLAG.UNSET){
                $('.failback_cmcdpdemotionthreshold_view').hide();
                $('.failback_recoverycdpinterval_view').hide();
            }
            if (os_type == 'Linux') {
                $('.failback_windows_cache_config').hide();
                // 解析新加的资源监测信息
                let resource_protect_config = highPressureStrategy.resource_protect_config;
                // 停止任务触发条件
                let stop_task_cl = LANG.UI_CM_CDP_STOP_CONDITION_USED_MEM_MAX + resource_protect_config.memory_used_stop_percent + '% ' +
                    LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.memory_used_stop_detect_time + LANG.UI_PUBLIC_SECOND;
                if (parseInt(resource_protect_config.stop_io_flag) === 1) {
                    // 磁盘I/O延迟 开关
                    stop_task_cl += '</br>' + LANG.UI_CM_CDP_STOP_CONDITION_IO_MAX + resource_protect_config.stop_io_delay + LANG.UI_CM_CDP_STOP_CONDITION_MILLOW + ' ' +
                        LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.io_stop_detect_time + LANG.UI_PUBLIC_SECOND;
                }
                if (parseInt(resource_protect_config.stop_cpu_flag) === 1) {
                    // cpu占用率超过 开关
                    stop_task_cl += '</br>' + LANG.UI_CM_CDP_PARSE_CONDITION_CPU_MAX + resource_protect_config.stop_used_cpu_percent + '% ' +
                        LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.stop_cpu_detect_time + LANG.UI_PUBLIC_SECOND;
                }
                $('#failback_stop_task_condition_list').html(stop_task_cl);
                // 持续数据保护降级
                if (parseInt(resource_protect_config.enable_cbt_flag) === 1) {
                    // 开启
                    // 任务暂停持续数据保护条件
                    let parse_task_cl = LANG.UI_CM_CDP_PARSE_CONDITION_USED_MAX + resource_protect_config.driver_used_memory_percent + '%';
                    parse_task_cl += '</br>' + LANG.UI_CM_CDP_STOP_CONDITION_USED_MEM_MAX + resource_protect_config.to_cbt_used_memory_percent + '% ' +
                        LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cbt_memory_duration + LANG.UI_PUBLIC_SECOND;
                    if (parseInt(resource_protect_config.io_cbt_flag) === 1) {
                        // 磁盘I/O延迟 开关
                        parse_task_cl += '</br>' + LANG.UI_CM_CDP_STOP_CONDITION_IO_MAX + resource_protect_config.to_cbt_io_delay + LANG.UI_CM_CDP_STOP_CONDITION_MILLOW + ' ' +
                            LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cbt_io_duration + LANG.UI_PUBLIC_SECOND;
                    }
                    if (parseInt(resource_protect_config.cpu_cbt_flag) === 1) {
                        //cpu占用率 开关
                        parse_task_cl += '</br>' + LANG.UI_CM_CDP_PARSE_CONDITION_CPU_MAX + resource_protect_config.to_cbt_cpu_used_percent  + '% ' +
                            LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cbt_cpu_duration + LANG.UI_PUBLIC_SECOND;
                    }
                    $('#failback_parse_task_condition_list').html(parse_task_cl);
                    // 任务恢复持续数据保护条件
                    let recovery_task_cl = LANG.UI_CM_CDP_RECOVERY_CONDITION_USED_MEM_MIN + resource_protect_config.to_cdp_used_memory + '% ' +
                        LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cdp_memory_duration / 60 + LANG.UI_JOB_MINUTE;
                    if (parseInt(resource_protect_config.io_cbt_flag) === 1) {
                        // 磁盘I/O延迟 开关
                        recovery_task_cl += '</br>' + LANG.UI_CM_CDP_RECOVERY_CONDITION_IO_MIN + resource_protect_config.to_cdp_io_delay + LANG.UI_CM_CDP_STOP_CONDITION_MILLOW + ' ' +
                            LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cdp_io_duration / 60 + LANG.UI_JOB_MINUTE;
                    }
                    if (parseInt(resource_protect_config.cpu_cbt_flag) === 1) {
                        //cpu占用率 开关
                        recovery_task_cl += '</br>' + LANG.UI_CM_CDP_RECOVERY_CONDITION_CPU_MIN + resource_protect_config.to_cdp_cpu_used_percent  + '% ' +
                            LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cdp_cpu_duration / 60 + LANG.UI_JOB_MINUTE;
                    }
                    $('#failback_recovery_task_condition_list').html(recovery_task_cl);
                    // 检测时间设置 开关
                    $('#failback_stop_task_condition_times_label').html(getFlagLevelInfo(resource_protect_config.time_windows_flag));
                    // 检测时间设置 详细
                    if (parseInt(resource_protect_config.time_windows_flag) === 1) {
                        let cbt_time_windows = resource_protect_config.cbt_time_windows;
                        let stop_task_condition_time = [];
                        for (var k in cbt_time_windows) {
                            cbt_time_windows[k].hour = cbt_time_windows[k].hour < 10 ? '0'+cbt_time_windows[k].hour : cbt_time_windows[k].hour;
                            cbt_time_windows[k].minute = cbt_time_windows[k].minute < 10 ? '0'+cbt_time_windows[k].minute : cbt_time_windows[k].minute;
                            var item = LANG.UI_PUBLIC_START_TIME + cbt_time_windows[k].hour + ':' + cbt_time_windows[k].minute + ' '
                                + LANG.UI_CM_CDP_INTERVAL_TIME + cbt_time_windows[k].duration + LANG.UI_JOB_MINUTE;
                            stop_task_condition_time.push(item);
                        }
                        $('#failback_stop_task_condition_times_list').html(stop_task_condition_time.join('</br>'));
                    } else {
                        $('.failback_stop_task_condition_times_list_view').hide();
                    }
                } else {
                    $('.failback_parse_task_condition_list_view').hide();
                    $('.failback_recovery_task_condition_list_view').hide();
                    $('.failback_stop_task_condition_times_label_view').hide();
                    $('.failback_stop_task_condition_times_list_view').hide();
                }
            } else {
                $('.failback_linux_cache_config').hide();
            }
            // 回切网络
            cmCdpTaskFailbackConf();
            getTaskNetCardHistoryConf();
            // 主备卷映射关系
            var failbackTargetDev = data.failback_target_dev;
            parseFailBackHistDevConf(failbackTargetDev);  //解析并组装回切设备信息
            getFailbackTargetConfigData(data.failbackup_target_uuid);
        });
    }

    /**
     * 获取回切时间点信息
     */
    let getFailbackTargetConfigData = (failbackup_target_uuid) => {
        var p = {};
        p.task_uuid = $('#task_uuid').val();;
        p.task_type = $('#task_type').val();
        Metronic.blockUI({target: '#failbackConfigView',animate: true});
        pAjaxRequest(p, "/api/v1/complete_machine_volcdp/jobs/time_point_uuid", "GET", function (res) {
            let data = res.data;
            let timepoint_uuid = data.timepoint_uuid;
            let getFailbackTargetConfig = (timepoint_uuid,failbackup_target_uuid) => {
                $('#cmCdpFailbackTargetDiskInfo').initTargetPlug({
                    type: 7, //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管,7,接管任务回切
                    origin_task_uuid: "",   //源任务uuid(实时,定时可不传或为空)  node.task_uuid
                    origin_agent_uuid: "", //源代理主机uuid(实时,定时可不传或为空)  node.agent_uuid
                    origin_timepoint_uuid: timepoint_uuid, //源时间点uuid或时间点(实时,定时)  node.timepoint_uuid
                    target_agent_uuid: failbackup_target_uuid,  //目标代理主机uuid(目标端主机)  目标机
                    target_visible_flag: true,  //控制恢复目标的列是否显示 true显示,false不显示
                    recover_type: true, //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
                    target_auto_flag: false, //是否显示恢复目标选择框的自动分配选项
                    target_input_flag:false, //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
                    target_input_min: -1, //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
                    origin_data_array:[], //前端源信息,当origin_data_flag为true时使用这个数据
                    nocheck: true, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
                    forced_relationship: true, //是否强制关联勾选.默认为强制关联勾选
                    match_dev_uuid_list: columnData,
                    first_column_name:LANG.UI_CM_CDP_SOURCE_DISK_NAME,
                    second_column_name:LANG.UI_SYSTEM_MONITOR_MBIT,
                    third_column_name:LANG.UI_CM_CDP_JOB_DETAILS_FAILBACK_TARGET,
                    init_selected: _hisDevConf,
                });
                setTimeout(function(){
                    Metronic.unblockUI("#failbackConfigView");
                },1000);
            }
            setTimeout(()=>{
                let changeData = $("#cmCdpFailbackTargetDiskInfo").getTargetData("getEachData",timepoint_uuid);
                if(!changeData){
                    return false;
                }
                let str = '';
                for(var item of changeData.target_strategy){
                    str += '<i class="levelchild viconfont vicon-cipan1 ztree_icon_color"></i>' + item.source_name + '->' +item.destination_name + '<br>';
                }
                $('#failback_target_disk_config').html(str);
            },1500)
            getFailbackTargetConfig(timepoint_uuid,failbackup_target_uuid);
        });
    }

    /**
     * 解析回切目标设备信息
     */
    let parseFailBackHistDevConf = function(failbackTargetDev){
        if(failbackTargetDev.length>0){
            var info = {};
            for (var i = 0; i < failbackTargetDev.length; i++){
                let sourceDev = failbackTargetDev[i]['dev_uuid'];
                let targetDev = failbackTargetDev[i]['failback_target_dev_uuid'];
                let selectedType = "manual";
                if(targetDev==""){
                    selectedType = "selected";
                    targetDev = 2;  //回切目标设备为空，默认为2时表示自动分配
                }
                info[sourceDev] = {
                    'selected_type': "selected",
                    'selected_value':targetDev
                };
            }
            _hisDevConf = info;
        }
    }

        //初始化详情流量图和数据流向tab
	var initTabShowEvent = function(){
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	        var tab = e.target;
	        if(tab.hash == "#tab_cm_chart"){
	        	$('#tab_cm_chart').show();
				$('#tab_cm_task_map').hide();
				_chartTabSelected = true;
				_dataFlowSelected = false;
				// 根据不同分辨率动态计算echart的高度和宽度
				var chartWidth = $('.portlet-charts__body__speedchart').width();
				var chartHeight = $('.portlet-charts__body__speedchart').height();
				$('#cmSpeedChart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
				myChart.resize();
	        }else if(tab.hash == "#tab_cm_task_map"){
	        	$('#tab_cm_chart').hide();
				$('#tab_cm_task_map').show();
				_chartTabSelected = false;
				_dataFlowSelected = true;
	        }
            if(tab.hash == "#cmdiskinfo"){
                initMonitorDeviceGrid();
            }else if(tab.hash == "#cmhistory"){
                initHistoryGrid();
                const targetTabId = $(e.target).attr('href'); // 新激活的选项卡ID
                const $targetTable = $(targetTabId).find('.bootstrap-table table');
                // 刷新表格数据并重置展开状态
                $targetTable.bootstrapTable('refresh');
            }
	    });
	}
    //获取任务基本信息
    let initBasicInfo = function(){
        var updateInterval = 5000;
		var init = function(){
			var requestData  = function(refs){
				if(refs.success){
					if(!refs.data['flag']){
                        UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
                        setTimeout(function(){
                            LOCATION('./content/platform/jobs/jobs.php','task');
                        }, 5000);
                        clearTimeout(timerTask.cmCdpJobDetails_taskRunningInfo);
                        return;
                    }
                    setBasicInfo(refs.data);
				}else{
					UIToastr.showWarning(LANG.UI_CM_CDP_GET_TASK_BASICS_CONF_INFO, refs.message);
                    return;
				}
			}
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.cmCdpJobDetails_taskRunningInfo);
        		return;
        	}
			var data = {};
			data.task_uuid = $("#task_uuid").val();
			//初始化备份源
			pAjaxRequest(data, '/api/v1/complete_machine_volcdp/jobs/basic_info', "GET", requestData, true);
			timerTask.cmCdpJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
    }
    //解析配置信息
    let setBasicInfo = function(data){
        let taskConfInfo = data['task_conf_info'];
        let taskRuningInfo = data['task_runing_info'];
        let safeStrategy = taskConfInfo.safe_strategy;  //安全策略

        taskInfo.task_confg_info = taskConfInfo;
        taskInfo.task_runing_info = taskRuningInfo;
        taskInfo.speed = taskRuningInfo.speed;
        safeStrategy.virus_scan_flag = true;
        if(safeStrategy.virus_scan_flag == true){
            virusScanStr = LANG.UI_SAFE_TAKE_SCAN;
            $('.verificationtypediv').show();
            // $("#verificationType").html($.fn.getVirusConfigDes(safeStrategy, 'takeover'));
        }else{
            $('.safescandiv').hide();
        }
        $('.takeoversafeconfgswtich').html(getFlagLevelInfo(safeStrategy.virus_scan_flag));


        defaultConfView();  //重置页面显示部分的内容
        $('.replicationtaskbackupdataview').hide();  //备份模式
        let taskTypeStr = taskConfInfo.task_type;
        if((taskConfInfo.task_type_value == CONF.TASK_TYPE.VOL_CDP_BACKUP  || taskConfInfo.task_type_value == CONF.TASK_TYPE.VOL_CDP_REPLICATION )&& taskConfInfo.standby_agent_uuid !=""){
            taskTypeStr = LANG.UI_CM_CDP_REPLICATION;
            $('.replicationtaskbackupdataview').show();  //备份模式
        }
        $(".cmbackupmode").html(getFlagLevelInfo(taskConfInfo.mirror_backup_flag));  //复制数据是否备份

        //--任务名
		$('#taskName').html(taskConfInfo.task_name);  //任务名
        // $('#taskDetailBackupMode').html(taskConfInfo.backup_mode);
        $('.cmtaskstatus').html('<span class="label ' + getStatusLevelClass(taskRuningInfo.status_value) + '" >' + taskRuningInfo.status + '</span>');   //任务状态

        $('#cmtaskType').html(taskTypeStr);

        $('.cmtaskstage').html(taskRuningInfo.current_task_running_stage);  //运行阶段
        $('#currentCheckVolume').html(taskRuningInfo.consistency_check_vol); //校验卷

        $('#totalSize').html(taskRuningInfo.totalSize);  //总容量
        $('#currentSize').html(taskRuningInfo.currentSize);  //已完成容量
        $('#startTime').html(taskRuningInfo.start_time);  //开始时间
        $('#intervalTime').html(taskRuningInfo.interval_time);  //持续时间

        $('#createTime').html(taskConfInfo.create_time); //创建时间
        $('#nextTime').html(taskRuningInfo.next_time);       //下次执行时间

        //更多任务配置
        $('#speedlimit').html(taskConfInfo.speed_limit.value);
		$('#speedlimit').prop('title', taskConfInfo.speed_limit.des);

        $("#transportEncrypt").html(getFlagLevelInfo(taskConfInfo.transport_strategy.encrypt));  //传输加密

        if(taskConfInfo.task_type_value == CONF.TASK_TYPE.VOL_CDP_REPLICATION){
            taskInfo.is_replication_task  = true;
            $('.takeoverconftext').text(LANG.UI_PUBLIC_TAKEOVER + ":");  //复制任务需要将应急接管描述配置修改为接管
        }

        if(taskConfInfo.transport_strategy.encrypt){
            $('.transfer-encrypt-method-div').hide();  //暂时屏蔽传输加密的加密算法，创建流程显示了，任务详情再显示
            let encryptMethod = taskConfInfo.transport_strategy.encrypt;
            let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
            if(encryptMethod == 2){
                method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
            }
            $('#transferEncryptMethod').html(method);
        }else{
            $('.transfer-encrypt-method-div').hide();
        }
        parseTransportConf(taskConfInfo); //解析传输配置
        parseAdvancedConfig(taskConfInfo,taskRuningInfo);  //解析高级配置
        parseScriptConfig(taskConfInfo);  //解析任务脚本配置
        parseTakeoverConf(taskConfInfo);    //解析接管配置0

        switch (taskRuningInfo.current_task_running_stage_value){
            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                $('.takeoverFailbackConfDiv').show();  //获取/设置接管回切配置
                $('.set_failback_conf_view').show();
                $('.view_failback_conf_view').hide();
                break;
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
                $('.takeoverFailbackConfDiv').show();  //获取/设置接管回切配置
                $('.set_failback_conf_view').hide();
                $('.view_failback_conf_view').show();
                break;
        }

        parseTaskProgress(taskConfInfo,taskRuningInfo);  //解析任务进度
        taskInfo.data_source_agent = taskConfInfo.master_agent_info;
        taskInfo.data_source_agent_uuid = taskConfInfo.master_agent_uuid;
        taskInfo.master_agent_os_type = taskConfInfo.master_agent_detail.os_type;
        taskInfo.backup_server = taskConfInfo.storageInfo.node.name+"("+ taskConfInfo.storageInfo.node.ip+")";
        taskInfo.master_os_type = taskConfInfo.master_agent_detail.os_type;
        taskInfo.master_memory_size = taskConfInfo.master_memory_size;
        taskMoreConfDetail(taskConfInfo);  //任务更多详情相关显示与隐藏

        let takeoverInfo = taskConfInfo.takeover_info;  //备份任务接管配置
        let handoverInfo = taskConfInfo.handover_info;  //手动接管配置
        switch(taskConfInfo.task_type_value){  //备份 or 复制

			case CONF.TASK_TYPE.VOL_CDP_BACKUP:
            case CONF.TASK_TYPE.VOL_CDP_REPLICATION:
                $('#tagdesView').show();
                $('.transportThreadNumdiv').show();
                $('.backupdestinationview').show();

                if(Object.keys(takeoverInfo).length === 0){
                    taskInfo.target_agent = taskConfInfo.standby_agent_info;  //复制备机
                    taskInfo.target_agent_uuid = taskConfInfo.standby_agent_uuid;  //复制备机UUID
                }else{
                    taskInfo.target_agent = taskConfInfo.takeover_info.standby_agent;  //备机目标
                    taskInfo.target_agent_uuid = taskConfInfo.takeover_info.takeover_standby_agent_uuid;
                 }

                let nodeConf = taskConfInfo.storageInfo.node;
                let storageConf = taskConfInfo.storageInfo.storage;
                let storageInfo = "";
                if(storageConf){	//没有存储信息,自动选择存储
                    if (taskConfInfo.storageInfo.storage_pool_uuid) {
                        storageInfo += taskConfInfo.storageInfo.storage_pool_nickname + '<br>';
                    }
                    storageInfo += storageConf.name + "(" + storageConf.type + ")<br>";
                    if(!storageConf.quotaFlag){
                        storageInfo += LANG.UI_JOB_TOTAL_SIZE + ":" + storageConf.size + ", " +
                        LANG.UI_JOB_FREE_SIZE + ":" + storageConf.freesize;
                    }else{
                        storageInfo += storageConf.quotades;
                    }
                }

                let nodeDes = "";
                if (taskConfInfo.storageInfo.node_pool_uuid) {
                    nodeDes += taskConfInfo.storageInfo.node_pool_nickname + '<br>';
                }
                if (taskConfInfo.storageInfo.node_uuid) {
                    nodeDes += nodeConf.name + '<br>' + nodeConf.ip;
                }
                if (nodeDes.length) {
                    $('#nodeinfo').html(nodeDes);  // 备份所在所用节点
                } else {
                    $('#nodeinfo').html(`--`);
                }

                // $('#nodeinfo').html(nodeConf.name + "<br>" + nodeConf.ip);		//备份所在所用节点
                // $('#nodeReInfo').html(nodeConf.name + "<br>" + nodeConf.ip);	//恢复所用节点
                $('#storageinfo').html(storageInfo);  //存储信息

                parseStartStrategy(taskConfInfo.time_strategy,taskConfInfo.task_type_value);
                if(taskConfInfo.auto_takeover_flag == CONF.FLAG.SET){
                    $("#takeoverType").text(LANG.UI_VOL_CDP_JOB_DETAILS_MANUAL_TAKEOVER);
                }
                $('.reserved_strategy_view').show(); //保留策略view
                $('#reservedStrategy').html(getReservedStrategy(taskConfInfo.reserved_strategy));  //保留策略

                $('#compressed').html(getFlagLevelInfo(taskConfInfo.storageInfo.high.compressed));  //数据压缩

                if(taskConfInfo.storageInfo.high.compressed==false){
                    $('.compressMethodDiv').hide();
                }else{
                    $('.compressMethodDiv').hide();  //bug 22772 调整隐藏压缩等级的显示
                    let compressMethodStr = parseCompressMethod(taskConfInfo.storageInfo.high.compress_method);
                    $('#compressMethod').html(compressMethodStr);
                }
                $('#encryptStorage').html(getFlagLevelInfo(taskConfInfo.storageInfo.high.encrypt_flag));  //数据加密
                if(taskConfInfo.storageInfo.high.password_auto_flag){
                    $('.passwordAutodiv').show();   //自动生成密码配置
                    $('#passwordAuto').html(getFlagLevelInfo(taskConfInfo.storageInfo.high.password_auto_flag));
                }

                tempAgentControl(takeoverInfo.takeover_agent_type,takeoverInfo.temp_status,takeoverInfo.temp_console_url,takeoverInfo.takeover_standby_agent_uuid,takeoverInfo.prefix_status);
                 $(".safescandetail").html($.fn.getVirusConfigDes(safeStrategy, 'takeover'));

                // if(taskConfInfo.auto_takeover_flag){
                if(taskConfInfo.takeover_info.takeover_agent_type  == vmTemplateEmbedQemuKvm){
                    $('.tempagentconfherf').show();
                }else{
                    $('.tempagentconfherf').hide();
                }
                // }
                break;
            case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
				$("#takeoverType").text(LANG.UI_VOL_CDP_JOB_DETAILS_MANUAL_TAKEOVER);
                $('.nodeDiv').hide();
                $('.storageDiv').hide();
                $('.tagexecutetimediv').hide();
                $('.takevertransportNetworkdiv').show();
                // $('.taskmoreconfdiv').hide();
                $('.cmtakeoverconfdiv').hide();
                $('.takeoverconfdiv').show(); //接管配置详情
                $('.takeovertimepointdiv').show(); //接管时间点
                $('.volCdpMasterIpSwitchDiv').show();  //主机IP漂移

                $('.transportconfview').hide();

                taskInfo.target_agent = taskConfInfo.handover_info.standby_agent;
                taskInfo.target_agent_uuid = taskConfInfo.handover_info.takeover_standby_agent_uuid;
                taskInfo.temp_console_url = taskConfInfo.handover_info.temp_console_url;

                tempAgentControl(handoverInfo.takeover_agent_type,handoverInfo.temp_status,handoverInfo.temp_console_url,handoverInfo.takeover_standby_agent_uuid,handoverInfo.prefix_status);
                 $(".safescandetail").html($.fn.getVirusConfigDes(safeStrategy, 'takeover'));

                if(taskConfInfo.handover_info.takeover_agent_type  == vmTemplateEmbedQemuKvm){
                    $('.tempagentconfherf').show();
                }else{
                    $('.tempagentconfherf').hide();
                }
                break;
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
                $('.nodeDiv').hide();
                $('.storageDiv').hide();
                $('.tagexecutetimediv').hide();
                 //查看更多任务配置div
                $('.taskdetailstoragepolicydiv').hide();  //存储策略
                $('.transportThreadNumdiv').show();
                taskInfo.target_agent = taskConfInfo.recovery_target_agent_info;  //恢复备机
                taskInfo.target_agent_uuid = taskConfInfo.recovery_target_agent_uuid;
                $('#takeoverli').hide();
                $('.transportconfview').show();
                $(".safescandetail").html($.fn.getVirusConfigDes(safeStrategy, 'recovery'));
                break;
        }
        if (!CONF.FUNCTIONS.includes('multithread')) {
			$('.transportThreadNumdiv').hide();
		}
		//--任务类型
		$('#taskType').html(data.module_type_des + data.job_type_des);
		//--任务状态
		if(data.job_status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.job_status) + '" >' + getStatusDes(data.job_status) + '</span>');
		}
        taskInfo.current_stage = taskRuningInfo.current_task_running_stage_value;  //当前运行阶段
        taskInfo.current_stage_des = taskRuningInfo.current_task_running_stage;
        taskInfo.task_status = taskRuningInfo.status_value;  //任务状态
        taskInfo.task_type = taskConfInfo.task_type_value;  //任务类型
        taskInfo.task_type_str = taskConfInfo.task_type; ///任务类型描述
        taskInfo.task_uuid = $("#task_uuid").val();  //任务uuid
        taskInfo.failback_target_info = taskConfInfo.failback_target_info;  //回切目标信息

        /**
         * 任务操作控制,相关控制方法在 cm_cdp_job_details_operation.js 文件中
         */
        initOpButton(data,taskConfInfo.auto_takeover_flag,taskInfo);
        initTaskCurrentStage();
        $.fn.dataFlow({
            task_info:taskInfo
        });
    }

    /**
	 * 控制模板机，需要判断接管备机是否为内嵌虚拟主机，虚拟机状态是否处于运行中
	 */
	var tempAgentControl = function (takeoverAgentType,tempStatus,tempConsoleUrl,vmUuid,vmPrefixStatus){
	    let isGmpFlag = CONF.VENDOR_LIST.gmp == CONF.VENDOR;
	    let isvmPrefixStatusFlag = !isGmpFlag || (vmPrefixStatus === CONF.PREFIX_STATUS.STATUS_SUCCESS);
		//tempStatus：1（在线）2（离线）；_VMPrefixStatus:8 内嵌虚拟机创建成功
		if(takeoverAgentType== vmTemplateEmbedQemuKvm && tempStatus ==CONF.FLAG.SET && isvmPrefixStatusFlag){
			// $('#standbyIp').addClass('colorgreen');
            setTimeout(()=>{
                $("#standbyHostRemoteControl").css({
                    "text-decoration": "underline",
                    "pointer-events": "initial",
                    "cursor": "pointer"
                });
                $('#standbyHostRemoteControl').unbind('click').click(takeoverStandbyOpereate);
            },1500);
            $('.clickicon').show();
			$("#cmTakeoverStandbyRemoteControl").css({
				"text-decoration": "underline",
				"pointer-events": "initial",
				"cursor": "pointer"
			});
			$('#cmTakeoverStandby').addClass('colorgreen');
			$('#cmTakeoverStandbyRemoteControl').unbind('click').click(takeoverStandbyOpereate);

			_tempConsoleUrl = tempConsoleUrl;
			_vmUuid = vmUuid;

		}else{  //移除a标签点击属性及样式
			$('#cmTakeoverStandbyRemoteControl').removeAttr('href').css({
				'pointer-events': 'none',
				'color':'#333333',
				'cursor': 'default',
				'text-decoration':'none'
			});
			$('.clickicon').hide();

			$('#cmTakeoverStandbyRemoteControl').removeAttr('colorgreen');
			$("#cmTakeoverStandbyRemoteControl").css("text-decoration","auto");  //移除下划线

			$("#standbyHostRemoteControl").css("text-decoration","auto");	//移除下划线
			// $('#standbyIp').removeAttr('colorgreen');
			// $('#cmTakeoverStandby').removeAttr('colorgreen');

			$('#standbyHostRemoteControl').removeAttr('href').css({
				'pointer-events': 'none',
				'cursor': 'default',
				'text-decoration':'none'
			});
			$('#standbyHostRemoteControl').removeAttr('colorgreen');

			_tempConsoleUrl = "";
			_vmUuid = "";
		}
	}
	/**
	 * 同步内嵌虚拟化平台vnc token
	 */
	var takeoverStandbyOpereate = function(){
		Metronic.blockUI({target: '#jobDetailDiv',animate: true,cenrerY: true});
		pAjaxRequest({type:'look'}, "/api/v1/virtual/operate/"+_vmUuid, "POST", function (result) {
			Metronic.unblockUI('#jobDetailDiv');
			if (result.code == 0) {
				window.open(_tempConsoleUrl, '_blank');
				return;
			} else {
				UIToastr.showError(LANG.UI_VM_MACHINE_OPERATION, result.message);
			}
		});
	}


    /**
     * 不同任务类型下相关相关配置信息显示与隐藏
     * 表格描述信息动态调整
     */
    var taskMoreConfDetail = function(taskConfInfo){
        let taskType = taskConfInfo.task_type_value;
        switch (taskType){
            case CONF.TASK_TYPE.VOL_CDP_BACKUP:
                $('.transportconfview').show();
                $('.transportpolicyview').show();
                $('.advancedconfigdiv').show();
                $('.speedlimitconfview').show();
                $('.tagpointconfview').show();
                backupTalbeCurrentStageFit(); //表格在不同阶段下的title调整
                break;
            case CONF.TASK_TYPE.VOL_CDP_REPLICATION:
                $('.transportconfview').show();
                $('.transportpolicyview').show();
                $('.advancedconfigdiv').show();
                $('.speedlimitconfview').show();
                $('.backuptaskconfview').show();
                $('.tagpointconfview').show();
                if(!taskConfInfo.mirror_backup_flag){  //未开启备份数据到备份服务器
                    // $('.tagpointconfview').hide();
                    $('.backuptaskconfview').hide();
                }
                backupTalbeCurrentStageFit(); //表格在不同阶段下的title调整
                break;
            case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
                $('.backuptaskconfview').hide();
                $('.backupdestinationview').hide();
                $('.transportpolicyview').hide();
                $('.advancedconfigdiv').hide();
                $('.speedlimitconfview').hide();
                break;
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
                $('.backuptaskconfview').hide();
                $('.backupdestinationview').hide();
                $('.transportpolicyview').show();
                $('.speedlimitconfview').show();
                break;
        }
    }
    /**
     * 处理不同任务阶段下页面元素显示
     */
    var initTaskCurrentStage = function(){
        let taskCurrentStage = taskInfo.current_stage;
        let taskType = taskInfo.task_type;
        let taskTypeStr = taskInfo.task_type_str;
        let taskStatus = taskInfo.task_status;
        let currentTaskRunningStage = taskInfo.current_stage_des;
        //任务阶段值不为0、任务状态不为停止、任务类型不为恢复
		if(taskCurrentStage!=0 && taskStatus!=CONF.TASK_STATUS.STOPPED && taskType!=CONF.TASK_TYPE.VOL_CDP_RECOVERY){
            $('.taskstageview').show();
			$('#taskStage').html('<span class = "label ' + getRunningStageClass(taskStatus,taskCurrentStage) +'" >' + currentTaskRunningStage+ '</span>');
		}else{
			$('.taskstageview').hide();
		}

        $('#currentSizeView').hide();
        switch (taskCurrentStage){
            case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC: //初始化同步
                $('#currentSizeView').show();
                $('.totalsizeDiv').show();  //任务总容量
                break;
            case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:  //实时同步
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:  //回切实时同步
                $('#currentSizeView').show();  //处理数据量
                $('.totalsizeDiv').hide();  //任务总容量
                $('#currentSizeStr').text(LANG.UI_VOL_CDP_JOB_DETAILS_REALTIME_SYN_DATA + ":");
                if(taskCurrentStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){
                    $('#currentSizeStr').text(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_REALTIME_SYN_DATA + ":");
                }
                break;
            case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
            case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK: //备机的数据一致性校验
            case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK: //实时数据校验
                $('#currentSizeView').show();
                $('.totalsizeDiv').show();  //任务总容量
                $("#taskTotalSizeStr").text(LANG.UI_VOL_CDP_JOB_DETAILS_CHECK_CAPACITY+":");
				$("#currentSizeStr").text(LANG.UI_VOL_CDP_JOB_DETAILS_CHECKED_CAPACITY+":");
                break;
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_WAIT_CONVERT_TO_CDP:
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_SERVER_DATA_CONSISTENCY_CHECK:
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STANDBY_DATA_CONSISTENCY_CHECK:
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_SERVER_REALTIME_CONSISTENCY_CHECK:
                $('.totalsizeDiv').show();  //任务总容量
                $("#taskTotalSizeStr").text(LANG.UI_VOL_CDP_JOB_DETAILS_TASK_ALL_CAPACITY+":");
                break;
        }

    }
    /**
     * 备份或复制任务表格在不同阶段下的title调整
     */
    var backupTalbeCurrentStageFit = function(){
        // 获取当前配置并修改列名
        var options = $('#cmMonitorDeviceTable').bootstrapTable('getOptions');
        let thirdColumnTitle = LANG.UI_CM_CDP_DEVICE_INITIAL_SYNC_CAPACITY_TITLE;  //第三列 初始同步容量/容量
        let rourColumnTitle  = LANG.UI_CM_CDP_DEVICE_INITIAL_SYNC_DATA_SIZE_TITLE;  //第四列 初始同步数据量/数据量
        let taskCurrentStage = taskInfo.current_stage;
        switch (taskCurrentStage){
            case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:  //实时同步
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:  //回档实时同步
                thirdColumnTitle = LANG.UI_CM_CDP_DEVICE_REALTIME_SYNC_CAPACITY_TITLE;
                rourColumnTitle = LANG.UI_CM_CDP_DEVICE_REALTIMEL_SYNC_DATA_SIZE_TITLE;
                break;
        }
        // options.columns[0][3].title = thirdColumnTitle;     // 修改第N列第N组的标题
        // options.columns[0][4].title = rourColumnTitle;     // 修改第N列第N组的标题

        // $('#cmMonitorDeviceTable').bootstrapTable('refreshOptions', options);
        $('#cmMonitorDeviceTable').bootstrapTable('updateColumnTitle', {
            field: 'data_size',
            title:thirdColumnTitle
        });
         $('#cmMonitorDeviceTable').bootstrapTable('updateColumnTitle', {
            field: 'sync_data_size',
            title: rourColumnTitle
        });



    }
    /**
     * 解析任务进度
     * @param {*} taskRuningInfo
     */
    var parseTaskProgress = function(taskConfInfo,taskRuningInfo){
        let taskRunningStage = taskRuningInfo.current_task_running_stage_value;  //任务运行阶段
        let taskStatus = taskRuningInfo.status_value;  //任务状态
        let taskType = taskConfInfo.task_type_value;  //任务类型

        //任务进度默认显示状态
        $('.progressDiv').hide();
		$('#total-progress').css({width: 0});
		$('#progressright').html('');
		$('.task_consistency_progress').hide();
        $('#currentSizeView').hide();
        switch(taskType){
            case CONF.TASK_TYPE.VOL_CDP_BACKUP:  //备份 or 复制
            case CONF.TASK_TYPE.VOL_CDP_REPLICATION:
                if(taskStatus==CONF.TASK_STATUS.RUNNING) {
                    $('.progressDiv').show();
                    $('#total-progress').css({width: taskRuningInfo.totalprogress});
                    $('#progressright').html(taskRuningInfo.progress);
                    $('#currentSizeView').show();
                    if(taskRunningStage ==	CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC || taskRunningStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC){
                        $('.task_consistency_progress').hide();
                        $('.task_total_progress').show();
                    }else if(taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK
                        || taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK
                        || taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK 
                        || taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_SERVER_DATA_CONSISTENCY_CHECK 
                        || taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STANDBY_DATA_CONSISTENCY_CHECK 
                        || taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_SERVER_REALTIME_CONSISTENCY_CHECK )
                    {   //校验
                        $('.task_consistency_progress').show();
                        $('.task_total_progress').hide();
                    }else{
                        $('.progressDiv').hide();
		                $('#total-progress').css({width: 0});
		                $('#progressright').html('');
                    }
                }
                break;
            case CONF.TASK_TYPE.VOL_CDP_TAKEOVER: //接管
                if(taskRunningStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC  || taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTIN){
                    $('.progressDiv').show();
			        $('#total-progress').css({width: taskRuningInfo.totalprogress});
			        $('#progressright').html(taskRuningInfo.progress);
                    $('.task_consistency_progress').hide();
                    $('.task_total_progress').show();
                }if(taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_SERVER_DATA_CONSISTENCY_CHECK 
                    || taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STANDBY_DATA_CONSISTENCY_CHECK 
                    || taskRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_SERVER_REALTIME_CONSISTENCY_CHECK){
                        $('.progressDiv').show();
                        $('.task_consistency_progress').show();
                        $('.task_total_progress').hide();
                }
                break;
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY:  //恢复
                if (taskStatus == CONF.TASK_STATUS.RUNNING){   //恢复任务处于运行中
                    $('.progressDiv').show();
			        $('#total-progress').css({width: taskRuningInfo.totalprogress});
			        $('#progressright').html(taskRuningInfo.progress);
                    $("#currentSizeView").show();
                }
                break;
        }
    };
    /**
     * 解析接管配置
     * @param {*} taskConfInfo
     */
    let parseTakeoverConf = function(taskConfInfo){
        let takeoverInfo = taskConfInfo.takeover_info;  //备份任务接管配置
        let handoverInfo = taskConfInfo.handover_info;  //手动接管配置

        let taskTypeValue = taskConfInfo.task_type_value;


        let takeoverAgentType = takeoverInfo.takeover_agent_type;
        let handoverTakeoverType = handoverInfo.takeover_agent_type;


        let takeoverModeStr = LANG.UI_VOL_CDP_MOUNT_TAKEOVER_DESC;  //挂载接管
        // if(takeoverInfo.length==0){  //启用接管
        if(Object.keys(takeoverInfo).length === 0){
            $('.takeoverconfdiv').hide();  //展示接管配置
            takeoverFlag = CONF.FLAG.UNSET;
        }else{
            $('.takeoverconfdiv').show();  //展示接管配置
            takeoverFlag = CONF.FLAG.SET;
        }
        taskInfo.backup_task_takeover_conf  = takeoverFlag;
        taskInfo.failbackup_ip = takeoverInfo.failbackup_ip;
        $('.cdpautotakeoverconfdiv').hide();
        $('.takeoverTimestampdiv').hide();
        $('.takeoverNetworConfDiv').show();  //获取/业务IP接管配置

        $('.safesettingview').hide();  //整机接管才展示其安全相关配置
         //复制任务隐藏接管模式的显示
         //深信服OEM版本只支持整机接管，需要屏蔽接管平台和自动接管的相关配置，对应需求号：#27350)
        if(taskTypeValue == CONF.TASK_TYPE.VOL_CDP_REPLICATION || CONF.VENDOR == "sangfor"){
            $('.takeoverModeview').hide();
        }

        if(takeoverAgentType == vmTemplateEmbedQemuKvm || handoverTakeoverType == vmTemplateEmbedQemuKvm){  //内嵌虚拟化平台，归属于整机接管
            takeoverModeStr = LANG.UI_VOL_CDP_COMPLETE_MACHINE_TAKEOVER_DESC;  //整机接管方式
            if(CONF.VENDOR != "sangfor"){  //深信服OEM版本只支持整机接管，需要屏蔽接管平台和自动接管的相关配置，对应需求号：#27350)
                $('.safesettingview').show();  //整机接管才展示其安全相关配置
            }

        }
        let virusScanStr = LANG.UI_SAFE_DECTION_TAKEOVER;
        $('.verificationtypediv').hide();

        // if(safeStrategy.virus_scan_flag == false){
        //     virusScanStr = LANG.UI_SAFE_TAKE_SCAN;
        //     $('.verificationtypediv').show();

        //     $("#verificationType").html($.fn.getVirusConfigDes(safeStrategy, 'takeover'));
        // }


        if(taskTypeValue == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskTypeValue == CONF.TASK_TYPE.VOL_CDP_REPLICATION){  //备份 or 复制
            if(CONF.VENDOR != "sangfor"){  //深信服OEM版本只支持整机接管，需要屏蔽接管平台和自动接管的相关配置，对应需求号：#27350)
                $('.cdpautotakeoverconfdiv').show();
            }
            $('#takeoverMode').html(takeoverModeStr);  //接管方式
            $('#cmTakeoverStandby').html(takeoverInfo.standby_agent);  //接管备机
            $('#volCdpMasterIpSwitch').html(takeoverInfo.failbackup_ip);  //主机IP漂移
            $('#cdpTakeoverApp').html(getFlagLevelInfo(takeoverInfo.app_takeover_flag));
             $('.volCdpMasterIpSwitchDiv').hide();
            if(takeoverInfo.failbackup_ip != ""){
                $('.volCdpMasterIpSwitchDiv').show();
            }
            if(taskConfInfo.auto_takeover_flag == CONF.FLAG.SET && taskConfInfo.auto_takeover_enable_flag == true){
                $('#cdpAutoTakeoverConf').html(getFlagLevelInfo(CONF.FLAG.SET));  //启用自动接管
                $('.autoTakeoverConfig').show();
            }else{
                $('#cdpAutoTakeoverConf').html(getFlagLevelInfo(CONF.FLAG.UNSET));  //警用自动接管
                $('.autoTakeoverConfig').hide();
            }

        }else if(taskTypeValue == CONF.TASK_TYPE.VOL_CDP_TAKEOVER){ //手动接管
            $('.takeoverTimestampdiv').show();
            $('.takeoverTimestamp').html(handoverInfo.takeover_timestamp);  //接管时间
            $('#cmTakeoverStandby').html(handoverInfo.standby_agent);  //接管备机
            // $('#volCdpMasterIpSwitch').html(handoverInfo.failbackup_ip);  //主机IP漂移
            $('#cdpTakeoverApp').html(getFlagLevelInfo());  //应用接管
            $('#takeoverMode').html(takeoverModeStr);  //接管方式
            $('#takeovertimepoint').html(taskConfInfo.handover_info.takeover_timestamp);


            $('#volCdpMasterIpSwitch').html(LANG.UI_FILE_DISABLE);
            if(handoverInfo.master_ip_switch==1){
                $('#volCdpMasterIpSwitch').html(LANG.UI_FILE_ENABLE);
            }

        }
        if(taskConfInfo.auto_takeover_flag == CONF.FLAG.SET){
            // 自动接管
            $('#heartbeatFailureTime').html(takeoverInfo.heartbeat_failure_time + " " + LANG.UI_PUBLIC_SECOND);  //心跳失效时间
            if(takeoverInfo.app_takeover_flag == CONF.FLAG.SET && takeoverInfo.app_consecutive_failure_num>0 && takeoverInfo.app_fault_detection_interval>0){
                $('.appconsefailurenumdiv').show();
                $('.appfaultdetectioninterdiv').show();
                $('#volCdpTaskAppMonitor').html(getFlagLevelInfo(CONF.FLAG.SET));
                $('#appConseFailureNum').html(takeoverInfo.app_consecutive_failure_num + " " + LANG.UI_PUBLIC_UNIT_COUNT);  //连续故障次数
                $('#appFaultDetectionInter').html(takeoverInfo.app_fault_detection_interval + " " + LANG.UI_PUBLIC_SECOND);  //故障监测间隔
            }else{
                $('.appconsefailurenumdiv').hide();
                $('.appfaultdetectioninterdiv').hide();
                $('#volCdpTaskAppMonitor').html(getFlagLevelInfo(CONF.FLAG.UNSET));
            }
        }





        // let virusDes = "";
        // if(!safeStrategy.virus_scan_flag){
        //     virusDes = getFlagLevelInfo(safeStrategy.virus_scan_flag);
        // }else{
        //     virusDes = ($.fn.getVirusConfigDes(safeStrategy, 'takeover'));
        // }
        // $("#takeoverSafeConf").html(virusDes);
        // $('#takeoverSafeConf').html(getFlagLevelInfo(safeStrategy.virus_scan_flag));  //接管安全配置
        $('#takeoverConfFlag').html(getConfFlagInfo(takeoverFlag));  //是否启用接管配置

    }

    /**
     * 解析任务脚本配置
     * 1.接管前主机运行 2.接管后备机运行 3.备份前主机运行 4.备份后主机运行 5.恢复后备机运行 6.主机自动接管检测脚本
     * @param {} taskConfInfo
     */
    let parseScriptConfig = function(taskConfInfo){
        let scriptInfo = taskConfInfo.task_common_script;
        let beforeBackupScriptLocation = [];  //备份前
        let afterBackupScriptLocation = [];  //备份后
        let beforeTakeoverScriptLocation = [];  //接管前
        let afterTakeoverScriptLocation = [];  //接管后
        let detectTakeoverScriptLocation = []; //主机自动接管检测脚本
        let afterRestoreScriptLocation = [];  //恢复后执行脚本

        for(let i=0;i<scriptInfo.length;i++){
            let script = scriptInfo[i];
            let scriptType = script.exec_type;  //脚本运行场景
            let scriptName = script.script_name;  //脚本名
            let scriptContent = script.script_content;  //脚本内容
            // let scriptObj = {script_name:scriptName,script_content:scriptContent};
             let scriptObj = {script};
            switch(scriptType){  //脚本运行场景
                case 1:  //接管前主机运行
                    beforeTakeoverScriptLocation.push(scriptObj);
                    break;
                case 2:  //接管后备机运行
                    afterTakeoverScriptLocation.push(scriptObj);
                    break;
                case 3:  //备份前主机运行
                    beforeBackupScriptLocation.push(scriptObj);
                    break;
                case 4:  //备份后主机运行
                    afterBackupScriptLocation.push(scriptObj);
                    break;
                case 5: //恢复后备机运行
                    afterRestoreScriptLocation.push(scriptObj);
                    break;
                case 6: //主机自动接管检测脚本
                    detectTakeoverScriptLocation.push(scriptObj);
                    break;
            }
        }
        if(beforeBackupScriptLocation.length>0){ //备份前
            let scriptDesc = inputScriptConf(beforeBackupScriptLocation);
            $('.scriptbeforebackup').html(scriptDesc);
        }else{
            $('.scriptbeforebackup').html(LANG.UI_VERIFY_NOT_SET);
        }

        if(afterBackupScriptLocation.length>0){ //备份后
            let scriptDesc = inputScriptConf(afterBackupScriptLocation);
            $('.scriptafterbackup').html(scriptDesc);
        }else{
            $('.scriptafterbackup').html(LANG.UI_VERIFY_NOT_SET);
        }

        if(beforeTakeoverScriptLocation.length>0){ //接管前
            let scriptDesc = inputScriptConf(beforeTakeoverScriptLocation);
            $('.scriptbeforetakeover').html(scriptDesc);
        }else{
            $('.scriptbeforetakeover').html(LANG.UI_VERIFY_NOT_SET);
        }

        if(afterTakeoverScriptLocation.length>0){ //接管后
            let scriptDesc = inputScriptConf(afterTakeoverScriptLocation);
            $('.scriptaftertakeover').html(scriptDesc);
        }else{
            $('.scriptaftertakeover').html(LANG.UI_VERIFY_NOT_SET);
        }

        if(detectTakeoverScriptLocation.length>0){ //主机自动接管检测脚本
            let scriptDesc = inputScriptConf(detectTakeoverScriptLocation);
            $('.scripttakeoverdetection').html(scriptDesc);
        }else{
            $('.scripttakeoverdetection').html(LANG.UI_VERIFY_NOT_SET);
        }

        if(afterRestoreScriptLocation.length>0){ //恢復后執行
            let scriptDesc = inputScriptConf(afterRestoreScriptLocation);
            $('.scriptafterrecovery').html(scriptDesc);
        }else{
            $('.scriptafterrecovery').html(LANG.UI_VERIFY_NOT_SET);
        }
        //为每一个脚本绑定事件
        for(var i =0;i<scriptInfo.length;i++){
            let scriptUuid = scriptInfo[i]['script_uuid'];
            $("#"+scriptUuid).unbind('click').bind('click', getScriptDetailInfo);
            //  let execType = scriptInfo[i]['exec_type'];
            //  let scriptName = scriptInfo[i]['script_name'];
            // $("#"+scriptUuid+execType+scriptName).unbind('click').bind('click', getScriptDetailInfo);
        }

        if(taskConfInfo.task_type_value == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskConfInfo.task_type_value == CONF.TASK_TYPE.VOL_CDP_REPLICATION){  //备份
            $('.scriptview').show();
            $('.backupscripdiv').show();
            $('.takeoverscripdiv').show();
            $('.recoveryscriptdiv').hide();
            $('.takeovermonitorscripdiv').show();
        }else if(taskConfInfo.task_type_value == CONF.TASK_TYPE.VOL_CDP_TAKEOVER) {  //接管
             $('.scriptview').show();
            $('.backupscripdiv').hide();
            $('.takeoverscripdiv').show();
            $('.recoveryscriptdiv').hide();
            $('.takeovermonitorscripdiv').hide();
        }else{
             $('.scriptview').hide();
            $('.backupscripdiv').hide();
            $('.takeoverscripdiv').hide();
            $('.recoveryscriptdiv').show();
             $('.takeovermonitorscripdiv').hide();
        }
    }
    //填充脚本配置内容
    let inputScriptConf = function(scriptObj){
        let str = "";
        for(let i=0;i<scriptObj.length;i++){
            let scriptUuid = scriptObj[i]['script']['script_uuid'];
            let scriptId = scriptUuid;
            // let execType = scriptObj[i]['script']['exec_type'];
            // let scriptName = scriptObj[i]['script']['script_name'];
            // let scriptId = scriptUuid+execType+scriptName;
            str += "<a href='javascript:void(0)' id = '"+scriptId+"' class = 'colorgreen' >" + scriptObj[i]['script']['script_name'] + "</a>   ";
        }
        return str;
    }
    //获取指定脚本详细配置信息
    let getScriptDetailInfo = function(){
        let clickedId = $(this).attr('id');
        let scriptInfo = taskInfo.task_confg_info.task_common_script;
        let scriptContentInfo = "";
        for(var i=0;i<scriptInfo.length;i++){
            let scriptUuid = scriptInfo[i].script_uuid;
            if(scriptUuid ==  clickedId){
                scriptContentInfo = scriptInfo[i];
            }
            // let execType = scriptInfo[i].exec_type;
            // let scriptName = scriptInfo[i].script_name;
            // if(scriptUuid+execType+scriptName ==  clickedId){
            //     scriptContentInfo = scriptInfo[i];
            // }
        }
        if(scriptContentInfo.exec_type == 6){  //自动接管检测脚本
            $('#scriptExecInterval').html(scriptContentInfo.exec_interval);
            $('#triggerFailNum').html(scriptContentInfo.trigger_fail_num);
            $('.scriptexecintervalview').show();
            $('.triggerfailnumview').show();
        }else{
            $('.scriptexecintervalview').hide();
            $('.triggerfailnumview').hide();
        }
        // $('#scriptContentDrawer .drawer-title .name').html(scriptContentInfo.script_name);
        $('#scriptName').html(scriptContentInfo.script_name);
        $('#scriptContentType').html(getScriptDesByType(scriptContentInfo.type));
        $('#scriptContent').html(scriptContentInfo.script_content);


        $('#scriptContentDrawer').drawer('show');
        $('#closeScriptDrawer').click(function(){
            $('#scriptContentDrawer').drawer('hide');
        });
        $('#closeScriptContentDrawer').click(function(){
            $('#scriptContentDrawer').drawer('hide');
        })

    }
     const getScriptDesByType = scriptType => {
        scriptType = parseInt(scriptType);
        switch (scriptType) {
            case 1:
                return LANG.UI_PUBLIC_SCRIPT_TYPE1;
            case 2:
                return LANG.UI_PUBLIC_SCRIPT_TYPE2;
            case 3:
                return LANG.UI_PUBLIC_SCRIPT_TYPE3;
            case 4:
                return LANG.UI_PUBLIC_SCRIPT_TYPE4;
            case 5:
                return LANG.UI_PUBLIC_SCRIPT_TYPE5;
            case 6:
                return LANG.UI_PUBLIC_SCRIPT_TYPE6;
            case 7:
                return LANG.UI_PUBLIC_SCRIPT_TYPE7;
            case 8:
                return LANG.UI_PUBLIC_SCRIPT_TYPE8;
            case 9:
                return LANG.UI_PUBLIC_SCRIPT_TYPE9;
            default:
                return '--';
        }
    };
    //解析高级配置
    let parseAdvancedConfig = function(taskConfInfo,taskRuningInfo){
        let cacheInfo = taskRuningInfo.cache_info;
        let taskTypeValue = taskConfInfo.task_type_value;
        let highPressureStrategy = taskConfInfo.high_pressure_strategy;  //高负载保护配置
        $('.isrecovery_advanced_conf').hide();
        $('.isbackup_advanced_conf').hide();
        $('.overloadprotectdiv').hide();   //过载保护
        os_type = taskConfInfo.os_type;
        switch(taskTypeValue){
            case CONF.TASK_TYPE.VOL_CDP_BACKUP:  //备份
            case CONF.TASK_TYPE.VOL_CDP_REPLICATION:  //复制
                let activeDataBackupFlag = false;
                if(!taskConfInfo.full_backup_flag){
                    activeDataBackupFlag = true
                }
                $('.isbackup_advanced_conf').show();
                $('.overloadprotectdiv').show();  //过载保护
                $('#resourceLimitIgnore').html(getFlagLevelInfo(taskConfInfo.ignore_resource_limiting_flag));  //忽略过载保护开关
                $('#storeDatabaseSize').html(taskConfInfo.storageInfo.high.blocksize);  //存储块大小
                $('#ioReplicationMode').html(taskConfInfo.monitor_data_io_replication_mode);  //IO复制模式
                $('#activeDataBackup').html(getFlagLevelInfo(activeDataBackupFlag));  //全量数据备份
                $('#skipBadBlockBackupsdiv').html(getFlagLevelInfo(taskConfInfo.skip_bad_block_flag));  //跳过坏块
                $('#autoMaticFaultRecoveryFlag').html(getFlagLevelInfo(taskConfInfo.auto_fault_resume_flag));  //断点续传

                $('#agentFileCachePath').html(cacheInfo.file_cach_path);  //客户端文件缓存路径
                $('#agentFileCacheSize').html(cacheInfo.agent_file_cache_des);  //文件缓存大小

                $('#memoryCacheSwitchFlag').html(getFlagLevelInfo(cacheInfo.memory_cache_flag));  //内存缓存开关
                $('.memorycachesizediv').hide();
                if(cacheInfo.memory_cache_flag == CONF.FLAG.SET){  //开启内存缓存
                    $('.memorycachesizediv').show();
                    $('#memoryCacheSize').html(cacheInfo.agent_memory_cache_des);  //客户端内存缓存使用情况
                }

                let memThreshold = highPressureStrategy.mem_threshold;   //内存最低阈值，低于该值后任务自动停止
                let cbtMemThreshold = highPressureStrategy.cbt_mem_threshold;  //cbt触发阈值，低于该值后切换至cbt模式
                let cbtDetectInterval = highPressureStrategy.cbt_detect_interval;   //cbt期间内存检测间隔，单位min
                let cbtCnableFlag = highPressureStrategy.cbt_enable_flag;   //cbt模式标志：0.未知 1.开启 2.禁用

                if(highPressureStrategy.detect_type == 1){  //按百分比
                    $("#stopTaskThreshold").html(LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS1 + memThreshold + "% " + "，"+LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS2);
                    $('#cmCdpDemotionThreshold').html(LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS1 + cbtMemThreshold + "% " + "，"+ LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS3);
                }else if(highPressureStrategy.detect_type == 2 || highPressureStrategy.detect_type == 3){  //按具体数值
                    $("#stopTaskThreshold").html(LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS1 + storageCalculateSize(memThreshold) + "，"+LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS2);
                    $('#cmCdpDemotionThreshold').html(LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS1 + storageCalculateSize(cbtMemThreshold) + "，"+LANG.UI_CM_CDP_MEMORY_USABLE_RATE_TIPS3);
                }

                $('#cmCdpDemotionSwitch').html(getFlagLevelInfo(cbtCnableFlag));  //持续数据保护降级
                $('#recovery_cdp_interval_str').html(cbtDetectInterval+ LANG.UI_MICROSOFT365_MINUTE);  //恢复持续数据保护监控间隔
                if(cbtCnableFlag == CONF.FLAG.UNSET){
                    $('.cmcdpdemotionthresholddiv').hide();
                    $('.recoverycdpintervaldiv').hide();
                }
                if (taskConfInfo.os_type == 'Linux') {
                    $('.windows_cache_config').hide();
                    // 解析新加的资源监测信息
                    let resource_protect_config = highPressureStrategy.resource_protect_config;
                    // 停止任务触发条件
                    let stop_task_cl = LANG.UI_CM_CDP_STOP_CONDITION_USED_MEM_MAX + resource_protect_config.memory_used_stop_percent + '% ' +
                    LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.memory_used_stop_detect_time + LANG.UI_PUBLIC_SECOND;
                    if (parseInt(resource_protect_config.stop_io_flag) === 1) {
                        // 磁盘I/O延迟 开关
                        stop_task_cl += '</br>' + LANG.UI_CM_CDP_STOP_CONDITION_IO_MAX + resource_protect_config.stop_io_delay + LANG.UI_CM_CDP_STOP_CONDITION_MILLOW + ' ' +
                            LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.io_stop_detect_time + LANG.UI_PUBLIC_SECOND;
                    }
                    if (parseInt(resource_protect_config.stop_cpu_flag) === 1) {
                        // cpu占用率超过 开关
                        stop_task_cl += '</br>' + LANG.UI_CM_CDP_PARSE_CONDITION_CPU_MAX + resource_protect_config.stop_used_cpu_percent + '% ' +
                            LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.stop_cpu_detect_time + LANG.UI_PUBLIC_SECOND;
                    }
                    $('#stop_task_condition_list').html(stop_task_cl);
                    // 持续数据保护降级
                    if (parseInt(resource_protect_config.enable_cbt_flag) === 1) {
                        // 开启
                        // 任务暂停持续数据保护条件
                        let parse_task_cl = LANG.UI_CM_CDP_PARSE_CONDITION_USED_MAX + resource_protect_config.driver_used_memory_percent + '%';
                        parse_task_cl += '</br>' + LANG.UI_CM_CDP_STOP_CONDITION_USED_MEM_MAX + resource_protect_config.to_cbt_used_memory_percent + '% ' +
                            LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cbt_memory_duration + LANG.UI_PUBLIC_SECOND;
                        if (parseInt(resource_protect_config.io_cbt_flag) === 1) {
                            // 磁盘I/O延迟 开关
                            parse_task_cl += '</br>' + LANG.UI_CM_CDP_STOP_CONDITION_IO_MAX + resource_protect_config.to_cbt_io_delay + LANG.UI_CM_CDP_STOP_CONDITION_MILLOW + ' ' +
                                LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cbt_io_duration + LANG.UI_PUBLIC_SECOND;
                        }
                        if (parseInt(resource_protect_config.cpu_cbt_flag) === 1) {
                            //cpu占用率 开关
                            parse_task_cl += '</br>' + LANG.UI_CM_CDP_PARSE_CONDITION_CPU_MAX + resource_protect_config.to_cbt_cpu_used_percent  + '% ' +
                                LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cbt_cpu_duration + LANG.UI_PUBLIC_SECOND;
                        }
                        $('#parse_task_condition_list').html(parse_task_cl);
                        // 任务恢复持续数据保护条件
                        let recovery_task_cl = LANG.UI_CM_CDP_RECOVERY_CONDITION_USED_MEM_MIN + resource_protect_config.to_cdp_used_memory + '% ' +
                            LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cdp_memory_duration / 60 + LANG.UI_JOB_MINUTE;
                        if (parseInt(resource_protect_config.io_cbt_flag) === 1) {
                            // 磁盘I/O延迟 开关
                            recovery_task_cl += '</br>' + LANG.UI_CM_CDP_RECOVERY_CONDITION_IO_MIN + resource_protect_config.to_cdp_io_delay + LANG.UI_CM_CDP_STOP_CONDITION_MILLOW + ' ' +
                                LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cdp_io_duration / 60 + LANG.UI_JOB_MINUTE;
                        }
                        if (parseInt(resource_protect_config.cpu_cbt_flag) === 1) {
                            //cpu占用率 开关
                            recovery_task_cl += '</br>' + LANG.UI_CM_CDP_RECOVERY_CONDITION_CPU_MIN + resource_protect_config.to_cdp_cpu_used_percent  + '% ' +
                                LANG.UI_CM_CDP_STOP_CONDITION_DURATION_MAX + resource_protect_config.to_cdp_cpu_duration / 60 + LANG.UI_JOB_MINUTE;
                        }
                        $('#recovery_task_condition_list').html(recovery_task_cl);
                        // 检测时间设置 开关
                        $('#stop_task_condition_times_label').html(getFlagLevelInfo(resource_protect_config.time_windows_flag));
                        // 检测时间设置 详细
                        if (parseInt(resource_protect_config.time_windows_flag) === 1) {
                            let cbt_time_windows = resource_protect_config.cbt_time_windows;
                            let stop_task_condition_time = [];
                            for (var k in cbt_time_windows) {
                                cbt_time_windows[k].hour = cbt_time_windows[k].hour < 10 ? '0'+cbt_time_windows[k].hour : cbt_time_windows[k].hour;
                                cbt_time_windows[k].minute = cbt_time_windows[k].minute < 10 ? '0'+cbt_time_windows[k].minute : cbt_time_windows[k].minute;
                                var item = LANG.UI_PUBLIC_START_TIME + cbt_time_windows[k].hour + ':' + cbt_time_windows[k].minute + ' '
                                + LANG.UI_CM_CDP_INTERVAL_TIME + cbt_time_windows[k].duration + LANG.UI_JOB_MINUTE;
                                stop_task_condition_time.push(item);
                            }
                            $('#stop_task_condition_times_list').html(stop_task_condition_time.join('</br>'));
                        } else {
                            $('.stop_task_condition_times_list').hide();
                        }
                    } else {
                        $('.parse_task_condition_list').hide();
                        $('.recovery_task_condition_list').hide();
                        $('.stop_task_condition_times_label').hide();
                        $('.stop_task_condition_times_list').hide();
                    }
                } else {
                    $('.linux_cache_config').hide();
                }
                break;
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY:

                $('.cmscriptdiv').hide(); //隐藏备份脚本配置
                $('.isrecovery_advanced_conf').show();
                let hostResetName = taskConfInfo.host_reset_name;
                if(taskConfInfo.restore_mode == RESTORE_MODE.VOL_RESTORE){  //恢复模式 1:整机，2：数据卷
                    $('.restoresystemview').hide();
                }else{
                    $('.restoresystemview').show();
                }
                if(hostResetName != ""){
                    $('.cmrecoveryresethostname').html(hostResetName);
                }else{
                    let hostResetNameFlag = CONF.FLAG.UNSET;
                    $('.cmrecoveryresethostname').html(getFlagLevelInfo(hostResetNameFlag));
                }
                break;

        }
    }

    //解析传输配置
    let parseTransportConf = function(taskConfInfo){
        $('#transportThreadNum').html(taskConfInfo.thread_num + LANG.UI_PUBLIC_NUM);  //传输线程个数
        $('#transportPacketSize').html(taskConfInfo.transport_strategy.transport_block_size + " MB");  //传输数据包大小

        $('#takeovertransportip').html(taskConfInfo.transport_strategy.transport_ip); //接管时显示的传输网络配置

        $('#transportCompress').html(getFlagLevelInfo(taskConfInfo.transport_strategy.compress));  //传输压缩开关
        // $('#transportCompress').html(getFlagLevelInfo(taskConfInfo.transport_strategy.compress));  //传输压缩等级
        let transportStrategyStr = "";
        if(taskConfInfo.transport_strategy.network_pool_nickname){
            transportStrategyStr += taskConfInfo.transport_strategy.network_pool_nickname + "<br>";
            transportStrategyStr += taskConfInfo.transport_strategy.transport_ip;
        }else{
            if(taskConfInfo.transport_strategy.transport_ip=="" || taskConfInfo.transport_strategy.transport_ip ==null){
                transportStrategyStr += LANG.UI_STORAGE_AUTO_ASSIGN;
            }else{
                transportStrategyStr += taskConfInfo.transport_strategy.transport_ip;
            }
        }
        $('#transportNetworkInfo').html(transportStrategyStr);  //传输网络IP
        //如果是资源池或者多对象任务（复制任务或挂载任务）择不显示传输网络配置
        let nodePoolUuid = taskConfInfo.storageInfo.node_pool_uuid;
        let storage_pool_uuid = taskConfInfo.storageInfo.storage_pool_uuid;
        let taskTypeValue = taskConfInfo.task_type_value;
        if(taskTypeValue == CONF.TASK_TYPE.VOL_CDP_REPLICATION){
            if(nodePoolUuid!="" || storage_pool_uuid!=""){
                $('.transportNetworkDiv').hide();
            }
        }
        if(taskTypeValue ==CONF.TASK_TYPE.VOL_CDP_BACKUP){
            let takeoverInfo = taskConfInfo.takeover_info;  //备份任务接管配置
            let takeoverAgentType = takeoverInfo.takeover_agent_type;
            //备份任务开启接管并且接管类型为
            if(Object.keys(takeoverInfo).length > 0 && takeoverAgentType != vmTemplateEmbedQemuKvm){
                $('.transportNetworkDiv').hide();
            }
        }

        // 压缩等级
        if(taskConfInfo.transport_strategy.compress && (taskConfInfo.task_type_value == CONF.TASK_TYPE.VOL_CDP_BACKUP
            || taskConfInfo.task_type_value == CONF.TASK_TYPE.VOL_CDP_REPLICATION || taskConfInfo.task_type_value == CONF.TASK_TYPE.VOL_CDP_RECOVERY)){
            var method = '';
            switch(taskConfInfo.transport_strategy.compress_method) {
                case 1:
                    method = LANG.UI_JOB_COMPRESS_PRIORITY_FASTER;
                    break;
                case 2:
                    method = LANG.UI_JOB_COMPRESS_PRIORITY_NORMAL;
                    break;
                case 3:
                    method = LANG.UI_JOB_COMPRESS_PRIORITY_BETTER;
                    break;
                case 4:
                    method = LANG.UI_JOB_COMPRESS_PRIORITY_BEST;
                    break;
            }
            $('#transportCompressMethod').html(method);
            $('.transportCompressMethodDiv').show();
        }else{
            $('.transportCompressMethodDiv').hide();
        }
    }
    //配置信息默认状态
    let defaultConfView = function(){
        $('#tagdesView').hide();
        $('.passwordAutodiv').hide();  //自动生成密码配置
        $('.reserved_strategy_view').hide(); //保留策略配置
        $('.transportThreadNumdiv').hide();
    }

    //得到保留策略描述信息
	let getReservedStrategy = function(msg){
		var reservedStr = '';
		if(!msg){
			reservedStr = LANG.UI_PUBLIC_NOTHING;
			return reservedStr;
		}
		if(CONF.RESERVE_TYPE.NUM == msg.type){
			reservedStr += LANG.UI_STRATEGY_RESERVE_NUM + "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.value + LANG.UI_STRATEGY_RESERVE_NUM_VALUE;
		}else if(CONF.RESERVE_TYPE.DAY == msg.type){
			reservedStr += LANG.UI_STRATEGY_RESERVE_DAY + "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.value + LANG.UI_STRATEGY_RESERVE_DAY_VALUE;
		}
		return reservedStr;
	}


    let parseCompressMethod = function(compressMethod){
        var compressMethodStr = "--";
        switch (compressMethod){
            case 1:
                compressMethodStr = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                break;
            case 2:
                compressMethodStr = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                break;
            case 3:
                compressMethodStr = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                break;
            case 4:
                compressMethodStr = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                break;
        }
        return compressMethodStr
    }

    //获取任务执行速度
    let initSpeed = function () {
        // 根据不同分辨率动态计算echart的高度和宽度
        var chartWidth = $('.portlet-charts__body__speedchart').width();
        var chartHeight = $('.portlet-charts__body__speedchart').height();
        $('#cmSpeedChart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
        // 基于准备好的dom，初始化echarts实例
        myChart = echarts.init(document.getElementById('cmSpeedChart'));
        // 指定图表的配置项和数据
        var option = {
            tooltip: {
                trigger: 'axis',
                formatter: function (params, ticket, callback) {
                    var value = params[0].data;
                    if (value >= 1024) {
                        return Math.round(value * 100 / 1024) / 100 + " MB/s";
                    } else {
                        return value + " KB/s";
                    }
                }
            },
            grid: {
                top: 15,
                left: 5,
                right: 5,
                bottom: 5,
                containLabel: true,
                show: false,
                borderWidth: 0,
            },
            xAxis:
                {
                    type: 'category',
                    boundaryGap: false,
                    splitNumber: 6,
                    splitLine: {
                        show: false
                    },
                    axisLabel: {
                        show: true,
                        interval: 12,
                        color: '#86909C'
                    },
                    axisLine: {
                        show: true,
                        lineStyle: {
                            color: '#C9CDD4',
                        }
                    },
                    data: []
                },
            yAxis :
                {
                    type : 'value',
                    splitLine: {
                        show: true,
                        lineStyle: {
                            type: 'dashed',
                        },
                    },
                    axisLine: {
                        show: false,
                    },
                    axisTick: {
                        show: false // Hide y-axis ticks
                    },
                    axisLabel : {
                        formatter: function(value, index){
                            //向上取整显示纵坐标
                            if(value >= 1024){
								return Math.ceil(value / 1024)+ "MB/s";
							}else{
                                if(value < 1){
                                    return value + "KB/s";
                                }else{
                                    return Math.ceil(value)+ "KB/s";
                                }
							}
                        },
                        color: '#86909C',
                    },
                },
            series : [
                {
                    name:'net',
                    type:'line',
                    stack: 'total',
                    showSymbol: false,
                    hoverAnimation: false,
                    smoothMonotone: 'x',
                    animation: false,
                    smooth: true,
                    areaStyle: {normal: {
                            color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? '#2A87C8':new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                {
                                    offset: 0,
                                    color: 'rgba(59, 179, 70, 0.2)'
                                },
                                {
                                    offset: 1,
                                    color: 'rgba(59, 179, 70,0)'
                                }
                            ]),
                            opacity: CONF.VENDOR == CONF.VENDOR_LIST.gmp ? 0.2 : 1,
                        }},
                    data:[]
                },
            ],
            color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? ['#2A87C8']:['#44b6ae']
        };

        var updateInterval = 2000;
        var data = [], nowTime = [];

        var parseNum = function (num) {
            num = parseInt(num);
            num = num >= 10 ? num : "0" + num;
            return num;
        }

        var getShowTime = function (timeStamp) {
            var myDate = new Date(parseInt(timeStamp));
            var date = myDate.toLocaleDateString();
            var hours = myDate.getHours();
            var minutes = myDate.getMinutes();
            var seconds = myDate.getSeconds();
            return parseNum(hours) + ":" + parseNum(minutes) + ":" + parseNum(seconds);
        }

        //初始化任务进度曲线图
        var initTaskSpeed = function (d) {

            if (initChartFlag) {
                return; //初始化了就直接返回
            }

            var serverTime = d.t * 1000;
            for (var i = 100; i > 0; i--) {
                data.push(0);
                nowTime.push(getShowTime(serverTime - i * 3000));
            }
            option.series[0].data = data
            option.xAxis.data = nowTime;
            myChart.setOption(option);

            initChartFlag = true;
        }

        var p = {};
        p.jobs_uuid = $("#task_uuid").val();

        function update()
        {
            if (0 == $('#cmSpeedChart').size()) {
                clearTimeout(timerTask.machineCdpSpeed);
                return;
            }
            pAjaxRequest(p, "/api/v1/complete_machine_volcdp/jobs/speed", "GET", function (result) {
                if(!result.success){
                    return;
                }
                initTaskSpeed(result.data);
                data.shift();
                data.push(result.data.speed);
                option.series[0].data = data;
                nowTime.shift();
                nowTime.push(result.data.nowTime);
                option.xAxis.data = nowTime;
                myChart.setOption(option);
            }, true)
            timerTask.machineCdpSpeed = setTimeout(update, updateInterval);
        }
        update();
        window.onresize = function () {
            myChart.resize();
        }
    }
    const watchEchartSizeChange = function() {
		window.onresize = function() {
			var chartWidth = $('.portlet-charts__body__speedchart').width();
			var chartHeight = $('.portlet-charts__body__speedchart').height();
			$('#cmSpeedChart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
			myChart.resize();
		}

        //  监听左侧菜单导航伸缩/展开触发的echart-resize事件
		$(window).on('echart-resize', function () {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                let chartWidth = $('.portlet-charts__body__speedchart').width();
                let chartHeight = $('.portlet-charts__body__speedchart').height();

                $('#cmSpeedChart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                myChart.resize();
            }, 300);
        });
	}
    //运行日志
    let initLogGrid = function(){
        $('#runninglog').runningLog({
            job_uuid: $('#task_uuid').val()
        });
    }
    //获取任务监控相关数据
    var devTable = $('#cmMonitorDeviceTable');
    let initMonitorDeviceGrid = function(){
        let taskUuid = $('#task_uuid').val();
        let taskType = $('#task_type').val();
        let columns = tableColums();
        var options = {
            searchInput: true,
            pagination: true,
            pageList: [5, 10, 25, 50],
            sortName:'start_time',
            sortOrder:'desc',
            detailView: true,
            detailFormatter: cmCdpDevDetail,
            uniqueId: 'dev_uuid',
            vin_url: '/api/v1/complete_machine_volcdp/jobs/monitor_device_info',
            vin_method: "GET",
            vin_params: function (params) {
                let  info = {};
                info.task_uuid = taskUuid;
                info.task_type = taskType;
                return info;
            },
            onRefresh: function (params) {
                $("#cmMonitorDeviceTable").bootstrapTable('hideLoading');
             },
            onPostBody: function () {
                $('#cmMonitorDeviceTable th[data-field="num"]').css('width','3%');
				$('#cmMonitorDeviceTable th[data-field="host_name"]').css('width','20%');
				$('#cmMonitorDeviceTable th[data-field="backup_disk"]').css('width','10%');
				$('#cmMonitorDeviceTable th[data-field="data_size"]').css('width','10%');
				$('#cmMonitorDeviceTable th[data-field="sync_data_size"]').css('width','10%');
				$('#cmMonitorDeviceTable th[data-field="transfer_size"]').css('width','8%');
				$('#cmMonitorDeviceTable th[data-field="write_size"]').css('width','8%');
                $('#cmMonitorDeviceTable th[data-field="standby"]').css('width','20%');
                $('#cmMonitorDeviceTable th[data-field="mapping_disk"]').css('width','10%');


                $('#cmMonitorDeviceTable th[data-field="data_source_host"]').css('width','20%');
                $('#cmMonitorDeviceTable th[data-field="valid_data_size"]').css('width','8%');
                $('#cmMonitorDeviceTable th[data-field="app_is_conf"]').css('width','8%');
                $('#cmMonitorDeviceTable th[data-field="takeover_standby_host"]').css('width','20%');

                // if (null !== expandIndex) {
				// 	devTable.bootstrapTable('expandRow', expandIndex);
				// }

                clearTimeout(timerTask.cm_dev_timetask);
                timerTask.cm_dev_timetask = setTimeout(function(){
                    // $('#cmMonitorDeviceTable').bootstrapTable('refreshOptions', {columns: columns});
                    $('#cmMonitorDeviceTable').bootstrapTable('refresh');
                    $("#cmMonitorDeviceTable").bootstrapTable('hideLoading');
                }, 5000);
                if (ztreeDetails) {
                    var expandedNodeIds = [];
                    var allNodes = ztreeDetails.transformToArray(ztreeDetails.getNodes());
                    // 获取之前的展开节点
                    allNodes.forEach(function(node) {
                        if (node.open) {
                            expandedNodeIds.push(node.id); // 保存展开状态的节点 ID
                        }
                    });
                    // 给节点对象重新配置open属性
                    zTreeData.forEach(function(node) {
                        if ($.inArray(node.id, expandedNodeIds) != -1) {
                            node.open = true;
                        } else {
                            node.open = false;
                        }
                    });
                    ztreeDetails = $.fn.zTree.destroy(ztreeId);
                    ztreeDetails = $.fn.zTree.init($("#"+ztreeId), zTreeSetting, zTreeData);
                }
            },
            columns:columns,
        }
        if (0 == $('#task_uuid').size()) {
            clearTimeout(timerTask.cm_dev_timetask);
            return;
        }
        if (!initDevGridFlag) {
            $('#cmMonitorDeviceTable').baseTableConfig().init(options);
            initDevGridFlag = true;
        } else {
            $('#cmMonitorDeviceTable').bootstrapTable('refresh');
        }

    }

    //监控设备列表
    var tableColums = function(){
        let taskType = $('#task_type').val();
        let currentStage = _taskInfo.task_current_stage;
        let dataSizeTitle = LANG.UI_CM_CDP_DEVICE_INITIAL_SYNC_CAPACITY_TITLE;  //初始同步容量/容量
        let syncDataSizeTitle = LANG.UI_CM_CDP_DEVICE_INITIAL_SYNC_DATA_SIZE_TITLE;  //初始同步数据量/数据量
        // debugger;
        // if(currentStage == CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC){  //  初始同步
        //     dataSizeTitle = LANG.UI_CM_CDP_DEVICE_INITIAL_SYNC_CAPACITY_TITLE;      //初始同步容量/容量
        //     syncDataSizeTitle = LANG.UI_CM_CDP_DEVICE_INITIAL_SYNC_DATA_SIZE_TITLE;    //初始同步数据量/数据量

        // }else if(currentStage == CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC){  //实时同步
        //     dataSizeTitle = LANG.UI_CM_CDP_DEVICE_REALTIME_SYNC_CAPACITY_TITLE;
        //     syncDataSizeTitle = LANG.UI_CM_CDP_DEVICE_REALTIMEL_SYNC_DATA_SIZE_TITLE;
        // }



        devColums = [
            {field: 'num',title: LANG.UI_PUBLIC_TABLE_ID,sortable: false,width: "10px"},
            {field: 'host_name',title: LANG.UI_PUBLIC_HOST,sortable: true},
            {field: 'backup_disk',title: LANG.UI_CM_CDP_JOB_BACKUP_DEV_TIPS, sortable: false },
            {field: 'data_size',title:dataSizeTitle, sortable: false},
            {field: 'sync_data_size',title: syncDataSizeTitle, sortable: false},
            {field: 'transfer_size',title: LANG.UI_PUBLIC_TRANSFER_SIZE,sortable: false},
            {field: 'write_size',title: LANG.UI_PUBLIC_REAL_SIZE, sortable: false},//写入大小
            {field: 'standby', title: LANG.UI_PUBLIC_STANDBY_HOST,sortable: false },
            {field: 'mapping_disk', title: LANG.UI_CM_CDP_JOB_TARGET_DEV_TIPS,sortable: false}
        ];
        if(taskType == CONF.TASK_TYPE.VOL_CDP_RECOVERY){  //恢复
            devColums = [
                {field: 'num',title: LANG.UI_PUBLIC_TABLE_ID,sortable: false,width: "10px"},
                {field: 'data_source_host',title: LANG.UI_CM_CDP_SOURCE_HOST,sortable: true},
                {field: 'recovery_dev',title: LANG.UI_CM_CDP_JOB_DATASOURCE_DEVICE, sortable: false },
                {field: 'data_size',title: LANG.UI_CM_CDP_DEVICE_CAPACITY_TITLE, sortable: false},
                {field: 'sync_data_size',title: LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_DATA_VOLUME, sortable: false},
                {field: 'transfer_size',title: LANG.UI_PUBLIC_TRANSFER_SIZE,sortable: false},
                {field: 'recovery_time_point',title: LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TIME_POINT, sortable: false},
                {field: 'recovery_target_host', title: LANG.UI_VOL_CDP_RECOVER_TARGET_HOST,sortable: false },
                {field: 'recovery_target_dev', title: LANG.UI_CM_CDP_JOB_TARGET_DEV_TIPS,sortable: false}
            ];
        }else if(taskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER){  //接管
            devColums = [
                {field: 'num',title: LANG.UI_PUBLIC_TABLE_ID,sortable: false,width: "10px"},
                {field: 'data_source_host',title: LANG.UI_CM_CDP_SOURCE_HOST,sortable: true},
                {field: 'takeover_dev',title: LANG.UI_CM_CDP_TAKEOVER_DEVICE, sortable: false },
                {field: 'data_size',title: LANG.UI_CM_CDP_DEVICE_CAPACITY, sortable: false},
                {field: 'valid_data_size',title: LANG.UI_CM_CDP_VALID_DATA, sortable: false},
                {field: 'takeover_time_point',title: LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_TIME_POINT,sortable: false},
                {field: 'app_is_conf',title: LANG.UI_VOL_CDP_JOB_DETAILS_IF_CONFIG_APPLICATION, sortable: false},
                {field: 'takeover_standby_host', title: LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE,sortable: false },
                {
                    field: 'mount_point',
                    title: LANG.UI_CM_CDP_JOB_TARGET_DEV_TIPS,
                    sortable: false,
                    formatter: function (value, data, row) {
                        if(data['mount_point'] =="--" || data['mount_point'] ==""){
                            return LANG.UI_COMPONENT_AUTO_ALLOCATE;
                        }else{
                            return data['mount_point'];
                        }
                    }
                }
            ];
        }

        return devColums;
    }
    /**
     * 获取监控磁盘对应详细信息
     */
    var lastIndex = [-1, -1];
    var cmCdpDevDetail = function(index,row, element){
        //行展开互斥操作
        if (index != lastIndex[1]) {
            lastIndex.push(index);
            $('#cmMonitorDeviceTable').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
            lastIndex.splice(0, 1);
        }
        element = '.detail-view td';
        let html = ' <div class = "d-flex mt-5">'
        Metronic.blockUI({target:  $(element),animate: true,timeout: 100,allowMultiple: false});

        var treeId = "taskDevTree"+row.dev_uuid;
        html += ' <div style = "flex: 1; box-sizing: border-box;" ><b>'+LANG.UI_CM_CDP_MONITOR_VOLUMES_IFNO+'</b>:</div>'
                +' <div style = "flex: 9; box-sizing: border-box;margin-top:-8px;"> '
                +' <ul id="'+treeId+'" class="ztree" style="display:inline-block"></ul>'
                +' </div>';
        html += ' </div>';
        $(element).append(html);
        let info = {};
        info.task_uuid = $('#task_uuid').val();
        info.dev_uuid = row.dev_uuid;
        info.task_type = taskInfo.task_type;
        pAjaxRequest(info, '/api/v1/complete_machine_volcdp/jobs/monitor_device_details', 'GET', function (res) {
            var data = res.data;
            setTaskDevTree(data,treeId);
            setTimeout(Metronic.unblockUI($(element)), 10);
        });
    }
    //渲染设备树形结构
    var setTaskDevTree = function (zNodes,treeId) {
        ztreeId = treeId;
        zTreeData = zNodes;
        var setting = {
			check: {
				enable: false,
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
                nameIsHTML: true
			},
		};
        zTreeSetting = setting;
		ztreeDetails = $.fn.zTree.init($("#"+treeId), setting, zNodes);
        ztreeDetails.expandAll(true);
    }

    //获取历史任务相关数据
    var initHistoryGrid = function(){

        let taskUuid = $('#task_uuid').val();
        var options = {
            searchInput: true,
            pagination: true,
            pageList: [5, 10, 25, 50],
            sortName:'start_time',
            sortOrder:'desc',
            detailView: true,
            detailFormatter: historyDetail,
            vin_url: '/api/v1/jobs/' + taskUuid + '/history',
            vin_method: "GET",
            columns: [
            {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                    sortable: false,
                    align: 'center',
                    width: "10px",
            },{
                    field: 'job_name',
                    title: LANG.UI_REPORT_TASKNAME,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'job_type',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'job_status',
                    title: LANG.UI_VISUAL_RESULT,
                    sortable: true,
                    align: 'center',
                    formatter: function (value, data, row) {
                        let html = '';
                        switch (data.job_status_value) {
                            case 0: //成功
                                return '<span class="label label-sm label-success">' + data.job_status + '</span>';
                            case 2: //中止
                            case 45:
                                return '<span class="label label-sm label-info">' + data.job_status + '</span>';
                            case 3: //异常
                            case 47:
                                return '<span class="label label-sm label-warning">' + data.job_status + '</span>';
                            case 1: //失败
                                return '<span class="label label-sm label-danger">' + data.job_status + '</span>';
                            default:
                                return '<span class="label label-sm label-danger">' + data.job_status + '</span>';
                        }
                        return html;
                    },
            },
                {
                    field: 'all_size',
                    title: LANG.UI_MICROSOFT365_ALL_SIZE,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'speed_size',
                    title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'write_size',
                    title: LANG.UI_PUBLIC_REAL_SIZE,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'start_time',
                    title: LANG.UI_PUBLIC_START_TIME,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'finish_time',
                    title: LANG.UI_PUBLIC_END_TIME,
                    sortable: true,
                    align: 'center',
            },
            ],
        }
		$('#cmHistoryTable').baseTableConfig().init(options);
    };

    //获取并解析历史任务详情
    let historyDetail = function (index, row, element){
         let html = '<table id = "historyDetailtab">'
         Metronic.blockUI({target: element,animate: true});
        $(element).append(html);
        pAjaxRequest({}, '/api/v1/jobs/history/' + row.job_id + '', 'GET', function (res) {
            Metronic.unblockUI(element);
            var data = res.data;
            if (!res.success) {
                $(element).append(LANG.UI_PUBLIC_NOTHING);
                return;
            }
            if (data.list.resource_limiting_node_config) { // 如果开启了资源限制，只显示资源限制内容详情
                html += `<tr>
                            <th width="25%">` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                            <th width="25%">` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                            <th width="35%">` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
                        </tr>`;

                let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
                 html += `<tr>
                        <td>` + LANG.UI_PUBLIC_ON + `</td>
                        <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                        <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                    </tr>`;
                // return `<table>${thead}${tbody}</table>`;

            } else {//未开启资源限制
                if(taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_REPLICATION){  //备份
                    html += `
                        <th width="15%">` + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT + `</th>
                        <th width="8%">` + LANG.UI_MACHINE_OS_BACKUP_DISK + `</th>
                        <th width="7%">` + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY_FINISHED + `</th>  
                        <th width="7">` + LANG.UI_VOL_CDP_JOB_DETAILS_VALID_DATA_FINISHED + `</th>
                        <th width="5">` + LANG.UI_VOL_CDP_JOB_DETAILS_AVERAGE_SPEED + `</th>
                        <th width="15%">` + LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE + `</th>
                        <th width="10%">` + LANG.UI_VOL_CDP_JOB_DETAILS_MAP_VOL + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_STATUS + `</th>
                        <th width="20%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                        `
                }else if(taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_RECOVERY){  //恢复
                    html += `
                    <th width="10%">` + LANG.UI_JOB_HIS_BAK_TIMEPOINT + `</th>
                    <th width="15%">` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_CLIENT + `</th>  
                    <th width="10%">` + LANG.UI_MACHINE_OS_RECOVERT_DISK + `</th>
                    <th width="10%">` + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY + `</th>
                    <th width="10%">` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_DATA_VOLUME + `</th>
                    <th width="15%">` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_SERVER + `</th>
                    <th width="10%">` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_VOL + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_STATUS + `</th>
                    <th width="15%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                    `
                }else {  //接管
                    html += `
                    <th width="15%">` + LANG.UI_EMERGENCY_RECOVERY_TIMEPOINT + `</th>
                    <th width="15%">` + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT + `</th>
                    <th width="10%">` + LANG.UI_CM_CDP_TAKEOVER_DISK + `</th>
                    <th width="20%">` + LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER + `</th>
                    <th width="8%">` + LANG.UI_CM_CDP_JOB_TARGET_DEV_TIPS + `</th>
                    <th width="7%">` + LANG.UI_PUBLIC_STATUS + `</th>
                    <th width="25%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                    `
                }
                //组装内容
                for (let i = 0; i < data.list.length; i++) {
                    if (taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_REPLICATION) { //备份or复制

                        let standbyMountInfo = "--";
                        if( data.list[i].standby_map_mount_point !="" &&  data.list[i].takeover_target_mount_point ==""){
                            standbyMountInfo = data.info[i].standby_map_mount_point;
                        }else if( data.list[i].standby_map_mount_point =="" &&  data.list[i].takeover_target_mount_point !=""){
                            standbyMountInfo = data.info[i].takeover_target_mount_point;
                        }else if( data.list[i].standby_map_mount_point !="" &&  data.list[i].takeover_target_mount_point !=""){
                            standbyMountInfo = data.info[i].standby_map_mount_point;
                        }

                        let standbyMountPoint = data.list[i].takeover_target_mount_point;
                        if(data.list[i].takeover_target_mount_point ==""){
                            standbyMountPoint = LANG.UI_STORAGE_AUTO_ASSIGN;
                        }
                        let takeoverHostName = data.list[i].takeover_host_name;
                        if(takeoverHostName=="" || takeoverHostName==undefined){
                            takeoverHostName = "--";
                            standbyMountPoint = "--";  //备机未配置，此时备机挂载点应给为--
                        }
                        html += `<tr>
                            <td>` + data.list[i].agent_name + `</td> 
                            <td>` + data.list[i].vol_display_name + `</td>
                            <td>` + data.list[i].vol_complete_size + "/"+ data.list[i].vol_size +  `</td>
                            <td>` + data.list[i].real_complete_size + "/"+ data.list[i].real_size + `</td>
                            <td>` + data.list[i].draw_speed + `</td>
                            <td>` +  takeoverHostName + `</td>
                            <td>` + standbyMountPoint  + `</td>
                            <td>` + data.list[i].task_status + `</td>
                            <td>` + data.list[i].description + `</td>
                        </tr>`
                    } else if (taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_RECOVERY) { //恢复
                        html += `<tr>
                        <td>` + data.list[i].recovery_target_time + `</td>
                            <td>` + data.list[i].agent_name + `</td>
                            <td>` + data.list[i].vol_display_name + `</td>
                            <td>` + data.list[i].vol_complete_size +" /" + data.list[i].vol_size + `</td>
                            <td>` + data.list[i].real_complete_size + " /"+ data.list[i].real_size + `</td>
                            <td>` + data.list[i].recovery_host_name + `</td>
                            <td>` + data.list[i].recovery_target_mount_point + `</td>
                            <td>` + data.list[i].task_status + `</td>
                            <td>` + data.list[i].description + `</td>
                        </tr>`
                    } else  { //接管
                        let mountPointDesc = data.list[i].takeover_target_mount_point;
                        if(data.list[i].takeover_target_mount_point==""){
                            mountPointDesc = "--";
                        }
                        html += `<tr>
                            <td>` + data.list[i].takeover_target_time + `</td>
                            <td>` + data.list[i].vol_display_name + `</td>
                            <td>` + data.list[i].vol_display_name + `</td>
                            <td>` + data.list[i].takeover_host_name + `</td>
                            <td>` + mountPointDesc + `</td>
                            <td>` + data.list[i].task_status + `</td>
                            <td>` + data.list[i].description + `</td>
                        </tr>`
                    }
                }
            }
            html += '</table>'
            $(element).append(html);
        });
    }
    //得到状态的显示类型
	let getStatusLevelClass = function(level){
		let levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-info";
				break;
			case 2:
			case 3:
				levelClass = "label-success";
				break;
			case 4:
				levelClass = "label-default";
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
    //得到任务阶段的显示底色
	var getRunningStageClass = function(taskStatus,runningStage){
		var levelClass = '';
		switch(runningStage){
			case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC:
				levelClass = "label-info";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
				levelClass = "label-success";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
			case CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC:
				levelClass = "label-success";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK:
				levelClass = "label-primary fs12";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK:
				levelClass = "label-primary fs12";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
				levelClass = "label-green fs12";
				break;
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:  //回切初始同步
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:  //逆向实时同步
            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:  //回切启动中
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_WAIT_CONVERT_TO_CDP:  //等待恢复回切实时保护
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_SERVER_DATA_CONSISTENCY_CHECK:  //回切数据致性校验
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STANDBY_DATA_CONSISTENCY_CHECK:  //回切机数据一致性校验
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_SERVER_REALTIME_CONSISTENCY_CHECK:  //回切实时数据一致性校验
				levelClass = "label-blue-madison fs12";
				break;
			default:
				levelClass = "label-default";
				break;
		}
		return levelClass;
	}
    //得到开启和关闭的HTML内容
	let getFlagLevelInfo = function(flag){
		let html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
		if(flag==CONF.FLAG.SET || flag==true){
			html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
		}
		return html;
	}
    //得到配置状态的HTML内容
    let getConfFlagInfo = function(flag){
        let html = '<span class="label label-warning">' + LANG.UI_CM_NOT_CONFIGURED + '</span>';
        if(flag==CONF.FLAG.SET){
            html = '<span class="label label-success">' + LANG.UI_CM_IS_CONFIGURED + '</span>';
        }
        return html;
    }

    /**
     * 解析离线状态,返回string
     * @descript:params 0:离线,1:在线;other:离线
     */
    let parseOnlineStatusStr = function(status){
        var statusStr = LANG.UI_VOL_CDP_JOB_DETAILS_OFF_LINE;
        if(status==1){  //设备在线，服务在线
            statusStr = LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE;
        }else if(status ==2){  //设备在线，服务离线
            statusStr = LANG.UI_VOL_CDP_JOB_DETAILS_APPLICATION_OFF_LINE;
        }
        return statusStr;
    }


    /**
     * 获取整机接管内嵌虚拟机信息
     */
    let getTakeoverVmConfigInfo = function (){
        let params = {};
        params.task_uuid = $("#task_uuid").val();
        Metronic.blockUI({target: '#drawer-2',animate: true});
        pAjaxRequest(params,'/api/v1/complete_machine_volcdp/jobs/takeover_vm_config','GET',function (res){
            Metronic.unblockUI('#drawer-2');
            let data = res.data;
            $('#vm_name').html(data.vm_name);
            $('#cpu_slot_num').html(data.cpu_slot_num + LANG.UI_PUBLIC_NUM);
            $('#cpu_slot_core_num').html(data.cpu_slot_core_num + LANG.UI_VM_MACHINE_CPU_UNIT);
            // 1:自适应 3:host-passthough
            let cpu_mode = {0:LANG.UI_PUBLIC_DEFAULT,2:'host-model',3:'host-passthrough',4:'EPYC'};
            $('#cpu_mode').html(cpu_mode[parseInt(data.cpu_mode)]);
            $('#memory_mb').html(data.memoryMB);
            // 1:bios 2:efi
            let firmware_type = ['bios','efi'];
            $('#firmware_type').html(firmware_type[parseInt(data.firmware_type)-1]);
            $('#max_boot_wait_time').html(data.max_boot_wait_time + LANG.UI_JOB_MINUTE);
            let host_reset_name = data.host_reset_name !== '' ? data.host_reset_name : LANG.UI_GLOBAL_STRATEGY_EMPTY;
            $('#host_reset_name').html(host_reset_name);
            // 磁盘配置
            let disk_config_des = '';
            let target_bus = ['ide','virtio','sata','scsi'];
            for(let i=0;i<data.disks.length;i++){
                disk_config_des += `<div class="strategy-group__form__item align-items-baseline">`;
                disk_config_des += `<div class="strategy-group__form__item__label col-md-4 name">${LANG.UI_BACKUP_DATA_TABLE_LABEL_PARITION_INFO}:</div><div class="strategy-group__form__item__value col-md-8 value">${LANG.UI_VM_SETTING_DISK_NAME}:${data.disks[i].dev_name};${LANG.UI_PUBLIC_TOTAL_SIZE}: ${storageCalculateSize(data.disks[i].total_size)};${LANG.UI_VIRTUAL_MACHINE_HARD_DISK_BUS_TYPE}: ${target_bus[parseInt(data.disks[i].target_bus)-1]};</div></div>`;
            }
            $('#disk_config').html(disk_config_des);
            // 网络配置
            let network_config_des = '';
            let model_type = ['VirtIO','e1000','rtl8139'];
            let source_net_name = '';
            let node_uuid_list = data.node_uuid_list;
            for(let i=0;i<data.interfaces.length;i++){
                let source_network_set = data.interfaces[i].source_network_set;
                for (const uuid of node_uuid_list) {
                    if (source_network_set.hasOwnProperty(uuid)) {
                        source_net_name += source_network_set[uuid];
                        break; // 找到第一个匹配的就停止
                    }
                }
                network_config_des += `<div class="strategy-group__form__item align-items-baseline">`;
                network_config_des += `<div class="strategy-group__form__item__label col-md-4 name">${LANG.UI_SYSTEM_MONITOR_NET_MSG}:</div><div class="strategy-group__form__item__value col-md-8 value">${LANG.UI_CLIENT_NETWORK_NIC_NAME}:${data.interfaces[i].origin_nic_name}; ${LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONF_TITLE}: ${source_net_name}; ${LANG.UI_PUBLIC_NET_BUS_TYPE}: ${model_type[parseInt(data.interfaces[i].model_type)-1]};</div></div>`;
            }
            $('#network_config').html(network_config_des);
        });
    }
    return {
        //main function to initiate the module
        init: function () {
			initListener();
            initTabShowEvent();
			//获取基本信息
			initBasicInfo();
			initSpeed();
			initLogGrid();
            initMonitorDeviceGrid();  //监控磁盘
			initHistoryGrid();
            watchEchartSizeChange();
        }

    };

}();

jQuery(document).ready(function() {
	cmCdpJobDetails.init();
});