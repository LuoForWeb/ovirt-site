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
			var j = 0;
			for(var i=0; i<agentNetworkInfo.length; i++){
				var active = "";
				var checked = '';
				if(!agentNetworkInfo[i].hasOwnProperty('ip_set') || agentNetworkInfo[i].ip_set.length == 0){
					continue;
				}
				j++;
				if(j == 1){
					active = "active";
					checked = 'checked';
				}
				if(agentNetworkInfo[i].ip_set.length > 0){
					const networkCard = agentNetworkInfo[i];
					const isCluster = networkCard.is_cluster;
					const ipAddr = networkCard.ip_set[0].ip_addr;
					const displayText = isCluster ? ipAddr : networkCard.name;

					networkCardhtml += `
							<li class="${active} dwm">
								<a href="#agent_network${i}${_agentNetworkStr}" data-toggle="tab" aria-expanded="true">
									<span class="ml10" id="${ipAddr}">${displayText}</span>
								</a>
							</li>
						`;
				}
			}
			var html = '<div class = "col-md-3 col-sm-3 col-xs-3 nav-tab-radios" style="height: 285px;">' +
				'<ul class="nav nav-tabs tabs-left " id = "hostNetCardTab">'+
				networkCardhtml +'</ul>' +
				'</div>';
			return html;
		};

		//得到每一张网卡的IP信息
		var getNetworkIpinfo = function(agentNetworkInfo, standbyNetworkInfo){
			var networkIphtml = "";
			var j = 0;
			for(var i=0; i<agentNetworkInfo.length; i++){
				var active = "";
				if(!agentNetworkInfo[i].hasOwnProperty('ip_set') || agentNetworkInfo[i].ip_set.length == 0){
					continue;
				};
				j++;
				if(j == 1){
					active = "active";
				}
				networkIphtml  +=  '<div class="tab-pane '+active+'" id="agent_network'+i+_agentNetworkStr+'">'+
					getAgentNetworkIpContent(agentNetworkInfo,i,standbyNetworkInfo)+
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
			let is_cluster = standbyNetworkInfo[0].is_cluster;  // 集群网卡
			if(is_cluster){
				targetHostGatewayDesc = LANG.UI_DB_CDP_SELECT_SOURCE_HOST_IP;
			}
			var div = '<div class="tab-pane" id="network_ip' + num + '">' +
				'<span class="display-none" id = "host_network_mac_'+num+'">'+ agentNetworkInfo[num].mac_address +'</span>'+
				'<div class="portlet-body">' +
				'<div class = "margintop20 ">' +
				'<div class="table-scrollable">' +
				'<table id="agent_network_ip_tab' + num + '" class="table table-striped table-bordered table-advance table-hover table-tac takeover-net-table">' +
				'<thead>' +
				'<tr>' +
				'<th width="50%">' + hostIpServiceDesc + '</th>' +
				'<th width="">' + targetHostGatewayDesc + '</th>' +
				'</tr>' +
				'</thead>' +
				'<tbody>' +
				getEachIpContent(agentNetworkInfo,num,standbyNetworkInfo) +
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
			var ipInfo = agentNetworkInfo[num].hasOwnProperty('ip_set') ? agentNetworkInfo[num].ip_set : [];
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
			var option = '';
			var historyConf = _historyNetworkInfo;
			if(!standbyNetworkCard[0].is_cluster){
				// 单机网卡
				option = '<option value="0">' + LANG.UI_VOL_CDP_CHANGE_MAP_NETCARD + '</option>';
				for(var i = 0;i<standbyNetworkCard.length;i++){
					if(!standbyNetworkCard[i].hasOwnProperty('ip_set')){
						continue;
					}
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
						+ '" ip_type = "'+ hostIpInfo.ip_type
						+ '" prefix = "'+ hostIpInfo.prefix
						+ '" host-mac = "'+ macAddress
						+ '" agent-netmask = "'+ hostIpInfo.netmask+'" >'
						+ standbyNetworkCard[i].name
						+ '</option>';
				}
			}else{
				// 集群网卡
				option = '<option value="0">' + LANG.UI_DB_CDP_SELECT_SOURCE_HOST_IP + '</option>';
				option += '<option '+' value="" standby-gateway = "" agent-gateway = "'+ gateway
					+ '" agent-nicname = "'+ hostIpInfo.ip_addr
					+ '" host-ipdrr = "'+ hostIpInfo.ip_addr
					+ '" ip_type = "'+ hostIpInfo.ip_type
					+ '" prefix = "'+ hostIpInfo.prefix
					+ '" host-mac = "" agent-netmask = "'+ hostIpInfo.netmask+'" >'
					+ hostIpInfo.ip_addr
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
			var div= '<div class="panel-group accordion display-none" id="takeoverStandbyGatewayConf">';
			div += '<div class="col-md-12 ">'+
				'<div class="tab-pane">' +
				'<div class="portlet-body">' +
				'<div class="table-scrollable">' +
				'<table id="network_ip_tab" class="table table-striped table-bordered table-advance table-hover table-tac takeover-net-table">' +
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
				if(!standbyNetworkInfo[i].gateway_address){
					ipAddress = '0.0.0.0';
				}
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
			if(!_standbyNetworkInfo[0].is_cluster){
				// 单机
				$('.backupGateway_li').show();
				$('#takeoverStandbyGatewayConf').show();
			}else{
				$('.backupGateway_li').hide();
				$('#takeoverStandbyGatewayConf').hide();
			}
		});
	}
})(jQuery)
