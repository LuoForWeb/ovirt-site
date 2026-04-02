(function($){
	$.fn.takeoverIpServerMapConfig = function(options){
		var _standbyNetworkInfo = options.standbyNetwork;
		var _agentNetworkInfo = options.agentNetwork;

		var _takeoverHisnetworkInfo = options.takeoverHisNetwork;
		var _failbackHisNetworkInfo = options.failbackHisNetwork;
		var _failbackupCationConf = options.failbackupCationConf;

		try {
			var _taskFailbackupIp = options.failbackupCationIp;
		} catch(e) {
			var _taskFailbackupIp = "";
		}
		//任务历史配置信息
		var _historyNetworkInfo = [];
		var _agentNetworkStr = "";
		if(_takeoverHisnetworkInfo.length==0 && _failbackHisNetworkInfo.length!=0){
			_historyNetworkInfo = _failbackHisNetworkInfo;
			_agentNetworkStr ="fh";
		}else if(_takeoverHisnetworkInfo.length !=0 && _failbackHisNetworkInfo.length==0){
			_historyNetworkInfo = _takeoverHisnetworkInfo;
			_agentNetworkStr ="th";
		}


		//得到每一个 网卡信息
		var getEachNetworkCard = function(agentNetworkInfo, standbyNetworkInfo){
			var networkCardhtml = "";
			var tabNumber = 0;
			for(var i=0; i<agentNetworkInfo.length; i++){
				var active = "";
				var checked = '';
				if(agentNetworkInfo[i].ip_set.length==0){
					continue;
				}
				tabNumber +=1;
				if(i==0 || tabNumber ==1){
					active = "active";
					checked = 'checked';
				}
				networkCardhtml += '<li class="'+active+' dwm  ">'+
					'<a href="#agent_network'+i+_agentNetworkStr+'" data-toggle="tab" aria-expanded="true" >'+
					'<span class = "ml10">'+agentNetworkInfo[i].name+'</span> '+
					'</a>'+
					'</li>';
			}
			var html = '<div class = "col-md-3 col-sm-3 col-xs-3">' +
				'<ul class="nav nav-tabs tabs-left " id = "hostNetCardTab">'+
				networkCardhtml +'</ul>' +
				'</div>';
			return html;
		};

		//得到每一张网卡的IP信息
		var getNetworkIpinfo = function(agentNetworkInfo, standbyNetworkInfo){
			var networkIphtml = "";
			var tabNumber = 0;
			for(var i=0; i<agentNetworkInfo.length; i++){
				var active = "";
				
				var agentNetworkIpContent = getAgentNetworkIpContent(agentNetworkInfo,i,standbyNetworkInfo);
				if(!agentNetworkIpContent){
					continue;
				};
				tabNumber +=1;
				if(i==0 || tabNumber ==1){
					active = "active";
				}
				networkIphtml  +=  '<div class="tab-pane '+active+'" id="agent_network'+i+_agentNetworkStr+'">'+
					agentNetworkIpContent+
					'</div>';
			}
			var html = '<div class = "col-md-9 col-sm-9 col-xs-9 margintop-15">' +
				'<div class="tab-content" >' +
				networkIphtml +
				'</div>'+
				'</div>';
			return html;
		};

		//得到主机网卡IP详情，配置备机映射
		var getAgentNetworkIpContent = function(agentNetworkInfo, num,standbyNetworkInfo){
			var hostIpServiceDesc = LANG.UI_VOL_CDP_HOST_IP_SERVICE; //数据源主机IP
			var _takeoverHisnetworkInfo = options.takeoverHisNetwork;
			var _failbackHisNetworkInfo = options.failbackHisNetwork;
			if (_takeoverHisnetworkInfo.length==0 && _failbackHisNetworkInfo.length!=0){
				hostIpServiceDesc = LANG.UI_VOL_CDP_FAILBACK_IP_SERVICE;  //接管备机IP
			}else if(_takeoverHisnetworkInfo.length!=0 && _failbackHisNetworkInfo.length==0){
				hostIpServiceDesc = LANG.UI_VOL_CDP_HOST_IP_SERVICE; //数据源主机IP
			}

			var targetHostGatewayDesc = LANG.UI_VOL_CDP_TASK_TAKEOVER_STANDBY_NETCARD;
			if(_failbackupCationConf){
				hostIpServiceDesc = LANG.UI_VOL_CDP_FAILBACK_IP_SERVICE;
				targetHostGatewayDesc = LANG.UI_VOL_CDP_FAILBACK_TARGET_IP_SERVICE_CONF;
			}
			var ipContent = getEachIpContent(agentNetworkInfo,num,standbyNetworkInfo);
			if(ipContent ==""){
				return false;
			}
			var div = '<div class="tab-pane" id="network_ip' + num + '">' +
				'<span class="display-none" id = "host_network_mac_'+num+'">'+ agentNetworkInfo[num].mac_address +'</span>'+
				'<div class="portlet-body">' +
				'<div class = "margintop20 ">' +
				'<div class="table-scrollable">' +
				'<table id="agent_network_ip_tab' + num + '" class="table table-striped table-bordered table-advance table-hover table-tac ">' +
				'<thead>' +
				'<tr>' +
				'<th width="50%">' + hostIpServiceDesc + '</th>' +
				'<th width="">' + targetHostGatewayDesc + '</th>' +
				'</tr>' +
				'</thead>' +
				'<tbody>' +
				ipContent +
				'</tbdoy>' +
				'</table>' +
				'</div>'+
				'</div>'+
				'</div>' +
				'</div>';
			return div;
		}

		/**
		 * 遍历网卡对应IP信息
		 */
		var getEachIpContent = function(agentNetworkInfo,num,standbyNetworkInfo){
			var div = '';
			var gateway = agentNetworkInfo[num].gateway_address;
			var nicName = agentNetworkInfo[num].name;
			var ipInfo = agentNetworkInfo[num].ip_set;
			var macAddress = agentNetworkInfo[num].mac_address;
			if(ipInfo.length>0){
				for(var i=0; i<ipInfo.length; i++){
					if(_taskFailbackupIp==ipInfo[i].ip_addr && _taskFailbackupIp!= ""){
						continue;
					}
					//添加本行正表格
					var ipAddrStr = ipInfo[i].ip_addr;
					if(gateway=="" || gateway==null){
						gateway = "--";
					}
					ipAddrStr = ipInfo[i].ip_addr+ "("+gateway+")";
					div += '<tr class="iptr" id="agent_network_ip' + i + '">' +
						'<td class="highlight">' + ipAddrStr+ '</td>' +
						'<td class="" id="">'+
						'<select class="form-control input-sm" name="standbyNetworkCard" id = "standby_network_card_'+num+i+'" >'+setNetworkCard(ipInfo[i],gateway,nicName,standbyNetworkInfo,macAddress)+'</select>'+
						'</td>' +
						'<td class="display-none" id="">'+ipInfo[i].netmask+'</td>' +
						'</tr>';
				}
			}
			return div;
		}

		/**
		 * 设置备机网卡信息
		 */
		var setNetworkCard = function(hostIpInfo,gateway,nicName,standbyNetworkCard,macAddress){
			var option = '<option value="0">' + LANG.UI_VOL_CDP_CHANGE_MAP_NETCARD + '</option>';
			var historyConf = _historyNetworkInfo;
			for(var i = 0;i<standbyNetworkCard.length;i++){
				var nicCurrentIp = hostIpInfo.ip_addr;
				var hisGateway = eachNetCardHisConf(nicCurrentIp,gateway,nicName);
				var selected = "";
				var ipIsConf = historyNetcardIpMapInfo(nicCurrentIp);
				if(hisGateway!="" && standbyNetworkCard[i].name==hisGateway && ipIsConf){
					selected = 'selected="selected"';
				}
				option += '<option '+selected+' value="'
					+ standbyNetworkCard[i].mac_address
					+ '" standby-gateway = "'+ standbyNetworkCard[i].gateway_address
					+ '" agent-gateway = "'+ gateway
					+ '" agent-nicname = "'+ standbyNetworkCard[i].name
					+ '" host-ipdrr = "'+ hostIpInfo.ip_addr
					+ '" host-mac = "'+ macAddress
					+ '" agent-netmask = "'+ hostIpInfo.netmask+'" >'
					+ standbyNetworkCard[i].name
					+ '</option>';
			}
			return option;
		}
		/**
		 * 解析主机历史网卡配置
		 * @param currentIp
		 * @returns {boolean}
		 */
		var historyNetcardIpMapInfo = function(currentIp){
			var historyConf = _historyNetworkInfo;
			var isConf = false;
			for(var i=0;i<historyConf.length;i++){
				var nicIpInfo = historyConf[i].nic_ip_info;
				for(var j=0;j<nicIpInfo.length;j++){
					var ip = nicIpInfo[j].ip;
					if(ip == currentIp){
						isConf = true;
						break;
					}
				}
			}
			return isConf;
		}
		/**
		 * 遍历历史配置信息，默认选中默认备机网卡信息
		 */
		var eachNetCardHisConf = function(nicIp,nicGateway,nicName){
			var gateway = "";
			var hisTargetNicname = "";
			for(var i = 0;i<_historyNetworkInfo.length;i++){
				if(_historyNetworkInfo[i]["nic_gateway"] == nicGateway && _historyNetworkInfo[i]["nic_name"] ==nicName){
					var nicIpInfo = _historyNetworkInfo[i].nic_ip_info;
					for(var j=0;j<nicIpInfo.length;j++){
						if(nicIpInfo[j].ip == nicIp){
							hisTargetNicname = nicIpInfo[j].target_nic_name;
						}
					}
				}
			}
			return hisTargetNicname;
		}
		return this.each(function(){
			var _this = $(this);
			_takeoverHisnetworkInfo,_failbackHisNetworkInfo

			var hostNetworkInfo = getEachNetworkCard(_agentNetworkInfo,_standbyNetworkInfo) + getNetworkIpinfo(_agentNetworkInfo,_standbyNetworkInfo);
			_this.html(hostNetworkInfo);
		});
	}
	
	//解析并渲染备机网关信息
	$.fn.takeoverStandbyGatewayConfig = function(options){
		var _standbyNetworkInfo = options.standbyNetwork;
		var _agentNetworkInfo = options.agentNetwork;

		var _takeoverHisnetworkInfo = options.takeoverHisNetwork;
		var _failbackHisNetworkInfo = options.failbackHisNetwork;
		var _failbackupCationConf = options.failbackupCationConf;
		//任务历史配置信息
		var _historyNetworkInfo = [];
		var standbyNetCardStr = LANG.UI_VOL_CDP_STANDBY_NETCARD;
		if(_takeoverHisnetworkInfo.length==0 && _failbackHisNetworkInfo.length!=0){
			_historyNetworkInfo = _failbackHisNetworkInfo;
		}else if(_takeoverHisnetworkInfo.length !=0 && _failbackHisNetworkInfo.length==0){
			_historyNetworkInfo = _takeoverHisnetworkInfo;
		}

		if(_takeoverHisnetworkInfo.length==0 && _failbackHisNetworkInfo.length!=0){
			standbyNetCardStr = LANG.UI_VOL_CDP_FAILBACK_TARGET_IP_SERVICE_CONF;
		}
		if(_failbackupCationConf){
			standbyNetCardStr = LANG.UI_VOL_CDP_FAILBACK_TARGET_IP_SERVICE_CONF;
		}
		
		var getStandbyNetworkIpinfo = function(agentNetworkInfo,standbyNetworkInfo){
			var div= '<div class="panel-group accordion" id="takeoverStandbyGatewayConf">';
			div += '<div class="col-md-12 ">'+
				'<div class="tab-pane">' +
				'<div class="portlet-body">' +
				'<div class="table-scrollable">' +
				'<table id="network_ip_tab" class="table table-striped table-bordered table-advance table-hover table-tac">' +
				'<thead>' +
				'<tr>' +
				'<th width="50%">' + standbyNetCardStr + '</th>' +
				'<th width="">' + LANG.UI_DRILLS_NETWORK_WAY + '</th>' +
				'</tr>' +
				'</thead>' +
				'<tbody>' +
				getEachStandbyGatewayContent(agentNetworkInfo,standbyNetworkInfo) +
				'</tbdoy>' +
				'</table>' +

				'</div>'+
				'</div>' +
				'</div>'+
				'</div></div>';
			return div;
		}

		/**
		 * 填充备机网卡信息，配置默认网关（备机默认网关数据来源于主机IP映射的配置）
		 */
		var getEachStandbyGatewayContent = function(agentNetworkInfo,standbyNetworkInfo){
			var div = '';
			var gateHistoryConf = eachStandbyGatewayHisConf();
			for(var i=0; i<standbyNetworkInfo.length; i++){
				//添加本行正表格
				var defaultGateway = "";
				var result = gateHistoryConf.some(item=>{
					if(item.nic_name===standbyNetworkInfo[i].name){
						defaultGateway = item.gateway
						return true;
					}else{
						return false;
					}
				})
				var ipAddress = standbyNetworkInfo[i].gateway_address;
				if(result){
					ipAddress = defaultGateway;
				}
				var standbyNetCardName = $.trim(standbyNetworkInfo[i].mac_address);
				standbyNetCardName = standbyNetCardName.replace(/:/g,'');
				div += '<tr class="iptr" id="' + standbyNetworkInfo[i].targetNicName + '">' +
					'<td class="highlight">' + standbyNetworkInfo[i].name + '</td>' +
					'<td class="vmstorage storage-active" id="">'+
					'<input class="form-control input-sm" standby-netcard = "'+standbyNetworkInfo[i].name+'" id= "standby_netcard_'+standbyNetCardName+'" value = "'+ipAddress+'"></input>'+
					'</td>' +
					'</tr>';
			}
			return div;
		}


		/**
		 * 遍历备机历史配置信息，默认填充历史网关配置信息
		 */
		var eachStandbyGatewayHisConf = function(){
			var standbyGatewayList = [];
			for (var i=0;i<_historyNetworkInfo.length;i++){
				var nicIpInfo = _historyNetworkInfo[i].nic_ip_info;
				for(var j=0;j<nicIpInfo.length;j++){
					var gatewayInfo = {};
					var nicName = nicIpInfo[j].target_nic_name;
					var gateway = nicIpInfo[j].target_gateway;
					gatewayInfo.nic_name = nicName;
					gatewayInfo.gateway = gateway;
					standbyGatewayList.push(gatewayInfo);
				}
			}
			return standbyGatewayList;
		}

		return this.each(function(){
			var _this = $(this);
			var getStandbyGatewayInfo = getStandbyNetworkIpinfo(_agentNetworkInfo,_standbyNetworkInfo);
			_this.html(getStandbyGatewayInfo);
		});
	}
	/**
	 * 获取网卡映射配置信息
	 */
	$.fn.takeoverGatewayMapConf = function(options){
		debugger;
		var _standbyNetworkInfo = options.standbyNetwork;
		var _agentNetworkInfo = options.agentNetwork;
		var takeoverIpMapListView = [];
		var standbyGatewayConf =[];

		var getHostNetworkconf = function(){
			standbyGatewayConf = getStandbyGateWayConf();
			var checkIp = checkIpFormat(standbyGatewayConf);
			if (!checkIp) {
				_takeoverIpMapList = [];
				UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_STANDBY_DEFAULT_NETCARD_FOFMAT_ERROR);
				return false;
			}
			var standbyNetCardConfList = standbyNetCardConf();
			var ipMapInfoList = [];
			takeoverIpMapListView = [];
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
				if (!ipSet || ipSet.length ==0) {
					continue;
				}
				var liId = getUuid();
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
					takeoverIpMapListView.push(agentMapInfo);
				}

				if (selectedNetCards == 0) {  // 如果没有选中任何网卡，弹出提示
					UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_CHANGE_MAP_NETCARD);
					return;
				}
			}
			return takeoverIpMapListView;
		};
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
		 * 校验网关格式
		 */
		var checkIpFormat = function (){
			var ipVerify = true;
			for(var i=0;i<standbyGatewayConf.length;i++){
				var gateway = standbyGatewayConf[i].gateway;
				if(gateway=="" && !ipV4V6(gateway)){
					ipVerify = false;
					break;
				}
			}
			return ipVerify;
		}

		/**
		 * 获取备机网卡配置
		 */
		var  getStandbyGateWayConf = function(){
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
		};
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
		};

		return getHostNetworkconf();
	}
})(jQuery)
