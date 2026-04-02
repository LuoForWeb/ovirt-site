var OrchEnvironment = function () {
	
	var grid, zTree, gridInitFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	
	var initRow = function(){
		var data = grid.getDataTable().data();
		var statusDiv = $('tbody > tr').find('td:eq(8)');
		for(var i=0; i<statusDiv.length; i++){
			setStatus(statusDiv[i], data[i]);
		}
		$(".popovers").popover();
		addDetailsInfo();
	}
	
	var setStatus = function(div, data){
		var labelClass = getLevelClass(data[7]);
		var content = '<span class="label label-sm ' + labelClass + '">' + 
			'<a class="popovers colorwhite" data-container="body" data-trigger="hover" data-placement="left" data-content="' + 
			data[8].popover + '" >'+ data[8].statusdes + '</a></span>';
		$(div).html(content);
	}
	
	//检测刷新的时候是否有展开项目，如果有的话就添加
	var addDetailsInfo = function(){
		var currentPage = parseInt($('.pagination-panel-input').val());
		if(currentPage != pageIndex){
			return true;
		}
		if(detailsInfo){
			var openTr = $('tbody > tr')[detailsIndex];
			$(openTr).after(detailsInfo);
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
	}
	
	//得到日志的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-info";		//部署中
				break;
			case 2:
				levelClass = "label-success";	//正常
				break;
			case 3:
				levelClass = "label-default";	//离线
				break;
			case 4:
				levelClass = "label-warning";	//异常
				break;
			case 5:
				levelClass = "label-danger";	//错误
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	
	//初始化事件
	var addListeners = function(){
		$('#addhost').on('click', addHost);
		$('#edithost').on('click', edithost);
		$('#deletehost').on('click', deletehost);
		handleValidationVMConfig();
		handleValidationSegment();
		$('.autocheck').on('blur', autoCheck);
		$("#cancel, .close").on("click", function() {
		    $('#segmenttbody input').val('');
		    $('.mycheck').removeClass('has-success has-error');
		    $('.mycheck i').removeClass('fa-check fa-warning');
		});
	}
	
	//自动检查网段映射输入已经自动设置新的掩码和网关
	var autoCheck = function(){
		//首先找到第1 2 3行
		var thisTr = $(this).closest("tr");
		var firstTr = searchFirstTr(thisTr);
		var secondTr = $(firstTr).next("tr");
		var thirdTr = $(secondTr).next("tr");
		
		//然后找到原网段,原子网掩码,原默认网关和新网段,如果都可以,就可以生成新的子网掩码和默认网关
		var oldsegment = $(firstTr).find(".oldsegment");
		var newsegment = $(firstTr).find(".newsegment");
		var oldnetmask = $(secondTr).find(".oldnetmask");
		var newnetmask = $(secondTr).find(".newnetmask");
		var oldgateway = $(thirdTr).find(".oldgateway");
		var newgateway = $(thirdTr).find(".newgateway");
		var validResutl = $(oldsegment).valid() && $(newsegment).valid() && $(oldnetmask).valid() && $(oldgateway).valid();
		if(validResutl){
			//设置新掩码
			$(newnetmask).val($(oldnetmask).val());
		}else{
			return;
		}
		//检测新的掩码和新的网段是否对应
		var newsegmentValue = $(newsegment).val();
		var newnetmaskValue = $(newnetmask).val();
		if(!checkSegmentAndNetmask(newsegmentValue, newnetmaskValue)){
			return;
		}
		
		var oldsegmentValue = $(oldsegment).val();
		var oldgatewayValue = $(oldgateway).val();
		var newgatewayValue = getNewGateway(oldsegmentValue, oldgatewayValue, newsegmentValue);
		$(newgateway).val(newgatewayValue);
	}
	
	//递归查找first tr
	var searchFirstTr = function(thisTr){
		if($(thisTr).hasClass("first")){
			return thisTr;
		}else{
			var preTr = $(thisTr).prev("tr");
			if(preTr.length){
				return searchFirstTr(preTr);
			}
		}
	}
	
	//虚拟机配置验证
	var handleValidationVMConfig = function() {
        $('.vmconfigdiv').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	vmname: {
                    required: true,
                },
                vmstorage: {
                    required: true,
                },
                vmnetwork: {
                    required: true,
                },
                vmip: {
                    required: true,
                    ipv4: true,
                },
                vmnetmask: {
                    required: true,
                },
                vmgateway: {
                    ipv4: true,
                },
                serverip: {
                    serverip: true,
                },
                servernetmask:{
                	required: true,
                },
                servergateway:{
                    ipv4: true,
                },
            },
            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");  
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },
            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group   
            },
            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },
            
            
        });
        
        $.validator.addMethod("serverip", function(value, element) {
        	if($('select[name=hosttype]').val() == "2"){
        		return true;
        	}
        	if($.trim(value) == "") return false;
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_IP);
        
        
        $.validator.addMethod("netmask", function(value, element) {
        	return this.optional(element) || /^(254|252|248|240|224|192|128|0)\.0\.0\.0|255\.(254|252|248|240|224|192|128|0)\.0\.0|255\.255\.(254|252|248|240|224|192|128|0)\.0|255\.255\.255\.(254|252|248|240|224|192|128|0)$/i.test(value);
        }, LANG.UI_PLATFORM_ENTER_VALID_MASK_ADDRESS);
        
	};
	
	var handleValidationSegment = function(){
		var segmentConfig = {
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block help-block-error', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
	            ignore: "",  // validate all fields including form hidden input
	            errorPlacement: function (error, element) { // render error placement for each input type
	                var icon = $(element).parent('.input-icon').children('i');
	                icon.removeClass('fa-check').addClass("fa-warning");  
	                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
	            },
	            highlight: function (element) { // hightlight error inputs
	                $(element)
	                    .closest('.mycheck').removeClass("has-success").addClass('has-error'); // set error class to the control group   
	            },
	            success: function (label, element) {
	                var icon = $(element).parent('.input-icon').children('i');
	                $(element).closest('.mycheck').removeClass('has-error').addClass('has-success'); // set success class to the control group
	                icon.removeClass("fa-warning").addClass("fa-check");
	            },
	        };
	        
		
	        $('.networkconfig').validate(segmentConfig);
	        
	        
	        $.validator.addMethod("ipv4", function(value, element) {
	        	return this.optional(element) || ipV4V6(value);
	        }, LANG.UI_SETTING_INPUT_IP);
	}
	
	//检查网段,掩码,网关是否符合规定
	var checkSegmentNetmaskGateway = function(segment){
		for(var i=0; i<segment.length; i++){
			if(!checkOneSegment(segment[i][0], segment[i][2], segment[i][4]) || 
			   !checkOneSegment(segment[i][1], segment[i][3], segment[i][5])){
				return false;
			}
		}
		return true;
	}
	
	//检测网段和掩码是否对应
	var checkSegmentAndNetmask = function(segment, netmask){
		var segmentInt = ipToInt(segment);
		var netmaskInt = ipToInt(netmask);
		var trueSegment = segmentInt & netmaskInt;
		var trueSegment = trueSegment >>> 0;
		if(segmentInt == trueSegment){
			return true;
		}else{
			var successSegment = intToIp(trueSegment);
			UIToastr.showWarning(LANG.UI_DRILLS_INPUT_NETWORK_SEGMENT + segment + LANG.UI_DRILLS_INPUT_NETMASK + netmask + LANG.UI_DRILLS_INPUT_NETMASK_TIPS,
					LANG.UI_DRILLS_NETWORK_SEGMENT + netmask + LANG.UI_DRILLS_AND_NETMASK + netmask + LANG.UI_DRILLS_INPUT_NETWORK_SEGMENT_TIPS + successSegment);
			return false;
		}
	}
	
	//通过原网段,原网关,新网段计算新的网关
	var getNewGateway = function(oldSegment, oldGateway, newSegment){
		var oldSegmentInt = ipToInt(oldSegment);
		var oldGatewayInt = ipToInt(oldGateway);
		var newSegmentInt = ipToInt(newSegment);
		//新的网关 = 原网关 - 原网段 + 新网段
		var newGatewayInt = oldGatewayInt - oldSegmentInt + newSegmentInt;
		var newGatewayInt = newGatewayInt >>> 0;
		var newGateway = intToIp(newGatewayInt);
		return newGateway;
	}
	
	//检查每个网段掩码网关
	var checkOneSegment = function(segment, netmask, gateway){
		//先将所有地址都转换成二进制
		var segmentInt = ipToInt(segment);
		var netmaskInt = ipToInt(netmask);
		var gatewayInt = ipToInt(gateway);
		var trueSegment = netmaskInt & gatewayInt;	//用掩码和网关做与运算,出来的值应该就是网段第一个地址
		var trueSegment = trueSegment >>> 0;		//把这个数字转换成无符号的等价形式（尽管该数字本身还是有符号的）
		if(segmentInt == trueSegment){
			//正确
			return true;
		}else{
			//错误
			var successSegment = intToIp(trueSegment);
			UIToastr.showWarning(LANG.UI_DRILLS_INPUT_NETWORK_SEGMENT + segment + LANG.UI_DRILLS_INPUT_NETMASK + netmask + LANG.UI_DRILLS_INPUT_GATEWAY + gateway + LANG.UI_DRILLS_INPUT_NETMASK_TIPS,
					LANG.UI_DRILLS_NETMASK + netmask + LANG.UI_DRILLS_AND_GATEWAY + gateway + LANG.UI_DRILLS_INPUT_NETWORK_SEGMENT_TIPS + successSegment);
			return false;
		}
	}
	
	//将ip转换成二进制
	var ipToInt = function(ip){
		var REG =/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/;
	    var xH = "",result = REG.exec(ip);
	    if(!result) return -1;
	    return (parseInt(result[1]) << 24 
	        | parseInt(result[2]) << 16
	        | parseInt(result[3]) << 8
	        | parseInt(result[4]))>>>0;
	}
	
	//整型解析为IP地址
	var intToIp = function(num){
	    var str;
	    var tt = new Array();
	    tt[0] = (num >>> 24) >>> 0;
	    tt[1] = ((num << 8) >>> 24) >>> 0;
	    tt[2] = (num << 16) >>> 24;
	    tt[3] = (num << 24) >>> 24;
	    str = String(tt[0]) + "." + String(tt[1]) + "." + String(tt[2]) + "." + String(tt[3]);
	    return str;
	}
	
	//添加代理
	var addHost = function(){
		var _STEP = 1, _TOTALSTEP = 4, _VMSETTINGS = {}, _SEGMENTNUM = 1;
		var _STORAGE = [];	//目的宿主机存储信息,用户最后检测存储剩余空间是否足够
		var _PRIMARYHOST = []; //主服务器列表
		var setTree = function(zNodes){
			var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true,
						idKey: "id",
						pIdKey: "pid",
						rootPId: 0
					}
				},
				callback: {
					beforeClick: nodeSelect,
//					beforeExpand: nodeExpand,
					onCheck: nodeOnCheck,
				}
			};
			var nodes = JSON.parse(zNodes);
			zTree = $.fn.zTree.init($("#hosttree"), setting, nodes);
			
		};
		var nodeSelect = function(treeId, treeNode, clickFlag){
			if(treeNode.chkDisabled){
				UIToastr.showInfo(LANG.UI_DRILLS_HOST_DEPLOY_ENVIRONMENT, LANG.UI_DRILLS_HOST + treeNode.name + LANG.UI_DRILLS_DEPLOY_ENVIRONMENT);
				return true;
			}
			zTree.checkNode(treeNode, !treeNode.checked, false, true);
			zTree.expandNode(treeNode, true)
		}
		
		var nodeOnCheck = function(e, id, node){
			var allNodes = zTree.getCheckedNodes(true);
			for(var i = 0; i < allNodes.length; i++){
				zTree.checkNode(allNodes[i], false, false, false);	
			}
			zTree.checkNode(node, true, false, false);
		}
		
		var hostTypeChange = function(){
			var hosttype = $(this).val();
			if("1" == hosttype){
				//主服务器,需要配置演练网络
				_TOTALSTEP = 4;
				$('#masterli').show();
				$('#masterhostdiv').hide();
				//配置备份服务器IP
				$('.priserver').show();
			}else if("2" == hosttype){
				//从服务器
				_TOTALSTEP = 3;
				$('#masterli').hide();
				$('#masterhostdiv').show();
				//配置备份服务器IP
				$('.priserver').hide();
			}
		}
		
		//初始化模态事件
		var initaddModalListeners = function(){
			//禁用step超链接
			$('.stepvm').unbind().on('click', function(){
				event.preventDefault();
				return false;
			})
			
			$('#prevstep').unbind().on('click', prevStep);
			$('#nextstep').unbind().on('click', nextStep);
			$('#submitaddvm').unbind().on('click', submitAddProxy);
			
			$('select[name=hosttype]').unbind().on('change', hostTypeChange);
			$('#addsegment').unbind().on('click', addSegment);
		}
		
		//添加一个网段配置
		var addSegment = function(){
			var tbody = $('#segmenttbody');
			var newHtml = '';
			for(var i=0; i<3; i++){
				if(0 == i){
					newHtml += '<tr class="first">';
				}else{
					newHtml += '<tr>';
				}
				newHtml +=  $(tbody[0].children[i]).html() + "</tr>";
			}
			var nowIndex = ++_SEGMENTNUM;
			newHtml = newHtml.replace(/#1/, "#" + nowIndex); 
			newHtml = newHtml.replace(/oldsegment1/, "oldsegment" + nowIndex);
			newHtml = newHtml.replace(/oldnetmask1/, "oldnetmask" + nowIndex);
			newHtml = newHtml.replace(/oldgateway1/, "oldgateway" + nowIndex);
			newHtml = newHtml.replace(/newsegment1/, "newsegment" + nowIndex);
			newHtml = newHtml.replace(/newnetmask1/, "newnetmask" + nowIndex);
			newHtml = newHtml.replace(/newgateway1/, "newgateway" + nowIndex);
			//本例会将全部匹配项替换为第二个参数。
			tbody.append(newHtml);
//			handleValidationSegment();
		}
		
		//统一控制上一步下一步(上一步,下一步)
		var stepCtlShow = function(prev, next){
			var leftStep = _STEP - 1;
			var rightStep = _STEP - 2;
			if(next){
				//下一步
				var rightStep = _STEP;
			}
			var steps = $('#steps').find('li');
			$(steps[leftStep]).removeClass('active');
			$(steps[rightStep]).addClass('active');
			var stepscontent = $('#stepscontent').find('.contentpane');
			$(stepscontent[leftStep]).removeClass('active').addClass('fade');
			$(stepscontent[rightStep]).removeClass('fade').addClass('active');
		}
		
		//上一步
		var prevStep = function(){
			stepCtlShow(true, false);
			_STEP--;
			if(1 == _STEP){
				$('#prevstep').hide();
			}
			$('#submitaddvm').hide();
			$('#nextstep').show();
			return;
		}
		
		//初始化虚拟机配置的
		var initVMConfigs = function(nodes){
			//创建参数
			var selectHost = {};
			selectHost.hypervisor = nodes[0].hypervisor;
			selectHost.vcenteruuid = nodes[0].vcenteruuid;
			selectHost.hostuuid = nodes[0].id;
			
			_VMSETTINGS.host = selectHost;
			
			selectHost = JSON.stringify(selectHost);
			Metronic.blockUI({target: '#modaldproxy',animate: true});
			$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getOrchProxyVMConfig',p:selectHost}, function(data){
				Metronic.unblockUI('#modaldproxy');
				var d = JSON.parse(data);
				$('#modaldproxy').find('input[name=vmname]').val(d.vmname);
				$('#modaldproxy').find('input[name=vmip]').val(d.vmip);
				$('#modaldproxy').find('input[name=vmnetmask]').val(d.vmnetmask);
				$('#modaldproxy').find('input[name=vmgateway]').val(d.vmgateway);
				var hoststorage = $('#modaldproxy').find('select[name=vmstorage]');
				var hostnetwork = $('#modaldproxy').find('select[name=vmnetwork]');
				var netwrokcard = $('#modaldproxy').find('select[name=networkcard]');
				hoststorage.empty();
				hostnetwork.empty();
				netwrokcard.empty();
				for(var i=1; i<d.storage.length; i++){
					var option = $("<option>").text(d.storage[i].text).val(d.storage[i].uuid);
					hoststorage.append(option);
				}
				for(var i=1; i<d.network.length; i++){
					var option = $("<option>").text(d.network[i].text).val(d.network[i].uuid);
					hostnetwork.append(option);
				}
				for(var i=0; i<d.networkcard.length; i++){
					var option = $("<option>").text(d.networkcard[i].name).val(d.networkcard[i].name);
					netwrokcard.append(option);
				}
				_STORAGE = d.storage;
			});
			return;
		}
		
		//得到最后的虚拟机配置信息
		var getVMconfig = function(){
			var vmConfig = {};
			vmConfig.name = $('#modaldproxy').find('input[name=vmname]').val();
			vmConfig.storage = $('#modaldproxy').find('select[name=vmstorage]').val();
			vmConfig.network = $('#modaldproxy').find('select[name=vmnetwork]').val();
			vmConfig.ip = $('#modaldproxy').find('input[name=vmip]').val();
			vmConfig.netmask = $('#modaldproxy').find('select[name=vmnetmask]').val();
			vmConfig.gateway = $('#modaldproxy').find('input[name=vmgateway]').val();
			vmConfig.networkcard = $('#modaldproxy').find('select[name=networkcard]').val();
			vmConfig.serverip = $('#modaldproxy').find('input[name=serverip]').val();
			vmConfig.servernetmask = $('#modaldproxy').find('select[name=servernetmask]').val();
			vmConfig.servergateway = $('#modaldproxy').find('input[name=servergateway]').val();
			
			_VMSETTINGS.vmConfig = vmConfig;
			_VMSETTINGS.segment = {};	//初始一下网络参数,防止从服务器的时候没有这个参数
		}
		
		//得到演练网络配置信息
		var getSegment = function(){
			var segment = [];
			var inputs = $('.networkconfig').find('input');
			var network = [];
			for(var i=0; i<inputs.length; i++){
				network.push($(inputs[i]).val());
				if((i+1) % 6 == 0){
					segment.push(network);
					network = [];
				}
			}
			_VMSETTINGS.segment = segment;
		}
		
		//验证代理网关虚拟机的存储空间
		var validateVMStorage = function(){
			var selectStorage = $('#modaldproxy').find('select[name=vmstorage]').val();
			for(var i=0; i<_STORAGE.length; i++){
				if(selectStorage == _STORAGE[i].uuid){
					if(_STORAGE[i].freesize > 2*1024*1024*1024){
						//虚拟机存储大于2个GB
						return true;
					}else{
						UIToastr.showInfo(LANG.UI_DRILLS_CHECK_AGENT_GATEWAY, LANG.UI_DRILLS_AGENT_GATEWAY_STORAGE_NEED_BIGGER);
						return false;
					}
				}
			}
			return false;
		}
		
		//下一步
		var nextStep = function(){
			switch(_STEP){
				case 1:
					var hosttype = $('select[name=hosttype]').val();
					var primaryhost = '';
					if("1" == hosttype){
						_TOTALSTEP = 4;
					}else if("2" == hosttype){
						_TOTALSTEP = 3;
						primaryhost = $('#masterhostdiv').find('select[name=primaryhost]').val();
						if(!primaryhost){
							UIToastr.showInfo(LANG.UI_DRILLS_SELECT_HOST_SERVER, LANG.UI_DRILLS_SELECT_HOST_SERVER_TIPS);
							return false;
						}
					}
					_VMSETTINGS = {};
					_VMSETTINGS.hosttype = hosttype;
					_VMSETTINGS.primaryhost = primaryhost;
					break;
				case 2:
					var hostNode = zTree.getCheckedNodes(true);
					if(0 == hostNode.length){
						UIToastr.showInfo(LANG.UI_DRILLS_SELECT_HOST, LANG.UI_DRILLS_SELECT_HOST_TIPS);
						return false;
					}
					initVMConfigs(hostNode);
					setPrimaryInfo(true);
					break;
				case 3:
					if(!$('.vmconfigdiv').validate().form()) return false;
					if(!validateVMStorage()) return false;
					getVMconfig();
					break;
				case 4:
					break;
			}
			stepCtlShow(false, true);
			_STEP++;
			if(_TOTALSTEP == _STEP){
				$('#nextstep').hide();
				$('#submitaddvm').show();
			}
			$('#prevstep').show();
			return;
		}
		
		//设置主服务器信息
		var setPrimaryInfo = function(primaryFlag){
			var serverip = "", servernetmask = "", servergateway = "";
			if(!primaryFlag){
				//如果是从服务器
				var primaryhost = $('#masterhostdiv').find('select[name=primaryhost]');
				for(var i=0; i<_PRIMARYHOST.length; i++){
					if(primaryhost = _PRIMARYHOST[i].uuid){
						serverip = _PRIMARYHOST[i]['serverip'];
						servernetmask = _PRIMARYHOST[i]['servernetmask'];
						servergateway = _PRIMARYHOST[i]['servergateway'];
					}
				}
			}
			$('#modaldproxy').find('input[name=serverip]').val(serverip);
			$('#modaldproxy').find('select[name=servernetmask]').val(servernetmask);
			$('#modaldproxy').find('input[name=servergateway]').val(servergateway);
		}
		
		//提交添加
		var submitAddProxy = function(){
			if(3 == _TOTALSTEP){
				//从
				setPrimaryInfo(false);
				if(!$('.vmconfigdiv').validate().form()) return false;
				getVMconfig();
			}else if(4 == _TOTALSTEP){
				//主
				if(!$('.networkconfig').validate().form()) return false;
				getSegment();
			}
			
			if(!checkSegmentNetmaskGateway(_VMSETTINGS.segment)) return false;
			
			var p = {}
			p = JSON.stringify(_VMSETTINGS);
			Metronic.blockUI({target: '#modaldproxy',animate: true});
			$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'addOrchProxy',p:p}, function(data){
				Metronic.unblockUI('#modaldproxy');
				if(OPREL(data)){
					grid.getRefresh({});
					$('#modaldproxy').modal('hide');
				}
			});
		}
		
		//初始化添加宿主机模态
		var initAddHostmodal = function(){
			//初始化各项参数和默认显示
			_STEP = 1;
			//内容
			$('#steps').find('li').removeClass('active');
			$('#steps').find('li').first().addClass('active');
			$('#stepscontent').find('.contentpane').removeClass('active').addClass('fade');
			$('#stepscontent').find('.contentpane').first().removeClass('fade').addClass('active');
			$('#prevstep').hide();
			$('#nextstep').show();
			$('#submitaddvm').hide();
			initaddModalListeners();
			
			
			//初始化备份虚拟机树(提前初始化不影响体验)
			$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'getOrchProxyTree',p:{}}, setTree);
			//初始化主服务器下拉(提前初始化不影响体验)
			$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getOrchPrimaryHostSelect',p:{}}, function(data){
				var d = JSON.parse(data);
				var primaryhost = $('#masterhostdiv').find('select[name=primaryhost]');
				primaryhost.empty();
				for(var i=0; i<d.length; i++){
					var option = $("<option>").text(d[i].text).val(d[i].uuid);
					primaryhost.append(option);
				}
				
				_PRIMARYHOST = d;
			});
			$('#modaldproxy').modal({'width':'1000px', 'height':'500px'});
		}
		
		
		initAddHostmodal();
	}
	//修改代理
	var edithost = function(){
		return;
	}
	//删除代理
	var deletehost = function(){
		var select = grid.getSelectedRows();
		if(1 != select.length){
			return UIToastr.showInfo(LANG.UI_DRILLS_DELETE_HOST, LANG.UI_DRILLS_DELETE_HOST_TIPS1);
		}
		
		bootbox.confirm({
            title: LANG.UI_DRILLS_DELETE_HOST,
            message: LANG.UI_DRILLS_DELETE_HOST_TIPS2,
            callback: function(r) {
                if(!r) return;
            	var data = {};
        		data.uuid = select[0];
        		data = JSON.stringify(data);
        		Metronic.blockUI({target: '#datatable',animate: true});
        		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'deleteOrchProxy',p:data}, function(d){
        			Metronic.unblockUI('#datatable');
        			if(OPREL(d)){
            			grid.getRefresh(getParams());
            		}
            	});
            }
        });
	}
	
	//得到参数
	var getParams = function(){
		//页码
		var page = $('.pagination-panel-input').val();
		//每页条数
		var size = $('select[name=datatable_length]').val();
		//搜索项
		var p ={start:0, length:10};
		if(undefined != page){
			p.start = parseInt(page) - 1;
			p.length = 10;
		}
		return p;
	}
	
    var handleRecords = function () {
    	var updateInterval = 10000;
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 1, 2, 3, 4, 5, 6, 7]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
    	
    	var initGrid = function(){
    		if(0 == $('#proxycontent').size()){
        		clearTimeout(timerTask.Environment_List);
        		return;
        	}
    		if(!gridInitFlag){
            	grid = new Datatable();
        		var data = {m:CONF.M.MANOEUVRE,f:'getProxyInfo',p:getParams()};
        		grid.setAjaxParam(data);
            	grid.init({src: $("#datatable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:initRow});
            	gridInitFlag = true;
    		}else{
    			grid.getRefresh(getParams());
    		}
    		timerTask.Environment_List = setTimeout(initGrid, updateInterval);
    	}
    	initGrid();
    	
    	//添加详情信息 
    	var addDetails = function(nTr, data){
    		if(!data){
    			return;
    		}
        	var sOut = '<tr class="details"><td class="details" colspan="9">';
        	sOut += getVerifyVMInfo(data[8]['vm']);
        	sOut += getBackupServerInfo(data[8]['vm']);
        	sOut += getSegmentInfo(data[8]['network']);
            sOut += '</td></tr>';
    		$(nTr).after(sOut);
    	}
    	
    	//得到备份服务器信息
    	var getBackupServerInfo = function(vm){
    		var table = '<table class="detailstable"><thead><tr>' + 
						'<th width="20%">' + LANG.UI_DRILLS_BACKUP_NETWORK_CARD + '</th><th width="10%">' + LANG.UI_DRILLS_VIP_ADDRESS + '</th><th width="10%">' + LANG.UI_DRILLS_NETMASK + '</th><th width="10%">' + LANG.UI_DRILLS_GATEWAY + '</th><th width="50%"></th></tr></thead>' + 
						'<tbody><tr><td>' + vm.servernetcard + '</td><td>' + vm.serverip + '</td><td>' + vm.servernetmask + '</td><td>' + 
						vm.servergateway + '</td><td></td></tr></tbody></table>';
    		return table;
    	}
    	
    	//得到验证虚拟机信息
    	var getVerifyVMInfo = function(vm){
    		var table = '<table class="detailstable"><thead><tr>' + 
    						'<th width="20%">' + LANG.UI_DRILLS_AGENT_GATEWAY + '</th><th width="10%">' + LANG.UI_DRILLS_IP_ADDRESS + '</th><th width="10%">' + LANG.UI_DRILLS_NETMASK + '</th><th width="10%">' + LANG.UI_DRILLS_GATEWAY + '</th><th>' + LANG.UI_VM_SETTING_STORAGE + '</th></tr></thead>' + 
    					'<tbody><tr><td>' + vm.name + '</td><td>' + vm.ip + '</td><td>' + vm.netmask + '</td><td>' + vm.gateway + 
    					'</td><td>' + vm.datastore + '</td></tr></tbody></table>';
    		if(vm.primaryhost != ""){
			var table = '<table class="detailstable"><thead><tr>' + 
					'<th width="20%">' + LANG.UI_DRILLS_AGENT_GATEWAY + '</th><th width="10%">' + LANG.UI_DRILLS_IP_ADDRESS + '</th><th width="10%">' + LANG.UI_DRILLS_NETMASK + '</th><th width="10%">' + LANG.UI_DRILLS_GATEWAY + '</th><th>' + LANG.UI_VM_SETTING_STORAGE + '</th><th>' + LANG.UI_DRILLS_HOST_SERVER + '</th></tr></thead>' + 
					'<tbody><tr><td>' + vm.name + '</td><td>' + vm.ip + '</td><td>' + vm.netmask + '</td><td>' + vm.gateway + 
					'</td><td>' + vm.datastore + '</td><td>' + vm.primaryhost + '</td></tr></tbody></table>';
    		}
    		return table;				
    	}
    	
    	//得到验证网络信息
    	var getSegmentInfo = function(network){
    		var table = '<table class="detailstable"><thead><tr>' + 
						'<th width="20%">' + LANG.UI_DRILLS_NETWORK_MAPPING + '</th><th width="10%">' + LANG.UI_DRILLS_OPTION + '</th><th  width="10%">' + LANG.UI_DRILLS_PRODUCT_NETWORK + '</th><th>' + LANG.UI_DRILLS_ISOLATED_NETWORK + '</th></tr></thead>' + 
						'<tbody>';
    		for(var i=0; i<network.length; i++){
    			table += '<tr><td rowspan="3">#' + (i + 1) + '</td>' + 
    						  '<td>' + LANG.UI_DRILLS_NETWORK_SEGMENT + '</td>' + 
    						  '<td>' + network[i].old_segment + '</td>' + 
    						  '<td>' + network[i].verify_segment + '</td></tr>' + 
    					  '<tr><td>' + LANG.UI_DRILLS_NETMASK + '</td>' + 
    					  '<td>' + network[i].old_netmask + '</td>' + 
    					  '<td>' + network[i].verify_netmask + '</td></tr>' +
    					  '<tr><td>' + LANG.UI_DRILLS_GATEWAY + '</td>' + 
    					  '<td>' + network[i].old_gateway + '</td>' + 
    					  '<td>' + network[i].verify_gateway + '</td></tr>'; 
    		}
    		table += '</tbody></table>';
			return table;
    	}
    	
    	$('#datatable').on('click', ' tbody td .row-details', function () {
        	var data = grid.getDataTable().data();
    		
            var nTr = $(this).parents('tr')[0];
            
            if($(this).hasClass('row-details-open')){
            	//如果是展开的
            	//收起所有展开项
            	$(this).addClass("row-details-close").removeClass("row-details-open");
            	$(this).parent().parent().next().remove();
            	detailsInfo = null;
            }else{
            	//如果是收起的
            	$('tr .details').parent().remove();
            	$('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('tbody tr .details').parents('tr')[0];
            	pageIndex = parseInt($('.pagination-panel-input').val());
            }
            return;
        });
    	
    	return;
    }
    

    return {
        //main function to initiate the module
        init: function () {
            handleRecords();
            addListeners();
        }

    };

}();

jQuery(document).ready(function() {    
	OrchEnvironment.init();
});