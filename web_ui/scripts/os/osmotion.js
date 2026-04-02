var OSMotion = function () {
    var data = {recoverInfo:{},timeInfo:{}, highInfo:{}, taskName:'', strategygroupuuid: '',timepoint_password:''};
    var linkInfo;
    var linkFlag = false; //链接测试 只有当为true才能通过下一步 
    var OSTYPE; //用于存放这次选择的时间点的操作系统类型  ,因为目前只选了一个时间点 暂时用一个变量先存储 后期再修改
    var networkFlag = false; //是否显示传输网络
	var timepointNode;
	var initSpeedFlag = false; //是否初始化限速策略
	var speedList = [];

    var inintDatatimePicker = function(){
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
		 //英文独有的
	  $(".form_datetime").datetimepicker({
	   autoclose: true,
	   isRTL: Metronic.isRTL(),
	   showSecond: true,
	   format: "yyyy-mm-dd hh:ii:ss",
	   pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
	   minuteStep: 1
	  });
		}else{
		 $(".form_datetime").datetimepicker({
		  language:  'zh-CN', 
		  autoclose: true,
		  showSecond: true,
		  isRTL: Metronic.isRTL(),
		  format: "yyyy-MM-dd hh:ii:ss",
		  pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		  minuteStep: 1
		 });
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
            jQuery('li', $('#osmotioncontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#osmotioncontent').find('.button-previous').css('visibility', 'hidden');
                $('#osmotioncontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#osmotioncontent').find('.button-previous').css('visibility', 'visible');
                $('#osmotioncontent').find('.button-next').removeClass('next-btn-margin-left');
            }
            if (current >= total) {
                $('#osmotioncontent').find('.button-next').hide();
                $('#osmotioncontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#osmotioncontent').find('.button-next').show();
                $('#osmotioncontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#osmotioncontent').bootstrapWizard({
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
                $('#osmotioncontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#osmotioncontent').find('.button-previous').css('visibility', 'hidden');
        $('#osmotioncontent .button-submit').click(submit).css('visibility', 'hidden');
	};
    //初始化监听
    var initListener = function(){
        $("#IPlistSelect").on('change',function(){
			linkFlag = false;
			linkInfo = "";
			$("#zoneInfo").hide();
		});
        //链接测试,测试通过了才能进行下一步 选择已有代理
		$("#linkSelectIP").on('click',function(){
			funcInputIP();
		});
		//初始化添加限速策略模态框
		$('#addSpeedlimit').on('click', function(){
			$('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
			if(!initSpeedFlag){
				initSpeedTimeStrategy();
			}
		});
		//切换限速模式
        $('#speedModeType').on('change', speedModeHandler);
        //添加限速策略确定
        $('#speed_submit').on('click', speedSubmit);
		 //切换线程数量事件
		 $('#osThreadNum').on('input propertychange', function(){
            initHighStrategyDes();
		});
        $('#osThreadNum').blur(threadChange);
        
        $('.backupThreadDiv .spinner-up').on('click', function(){
        	threadChange();
        	initHighStrategyDes();
        });
        $('.backupThreadDiv .spinner-down').on('click', function(){
        	threadChange();
        	initHighStrategyDes();
        });
		// 传输策略---加密传输
        $('#encrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);
	}
	// 显示加密算法
	var transferEncryptChange = function(){
		if(this.checked){
			$('.transfer-encrypt-method-form').show();
		}else{
			$('.transfer-encrypt-method-form').hide();
		}
	}
	//初始化微调器
	var initSpinner = function(){
		$('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('#backupThreadNum').spinner({value:3, step: 1, min: 1, max: 8});
	}
    var step1Valid = function(){
        //检查是否链接测试成功
		if(!linkFlag){
			UIToastr.showWarning(LANG.UI_OS_RECOVERY_LINK_TEST, LANG.UI_OS_TEST_SUCCESS_TIP);
			return false;
		}
        var dataList = $("#osTable").getosRecoveryData(linkInfo);
		if(!dataList){
			return false;
		}
		data.recoverInfo = dataList.recovery_oss_info;
        //初始化第二步迁移方式的一些显示
		initStrategyDes();
		//这个表格暂时不用 后面需要可打印在html上查看
//		showStep1(dataList.TableStr);
		showStep1(data.recoverInfo);
		return true;
    }
    var step2Valid = function(){
		//得到线程数量
		data.highInfo.threadnum  = $('#osThreadNum').val();
		//限速策略
		data.speedInfo = speedList;
		//传输策略
		data.highInfo.transfer = {};
		data.highInfo.transfer.encrypt = $('#encrypttransfer').get(0).checked; //得到传输加密
		// 传输加密算法
		data.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
		// 重连次数
		data.highInfo.transfer.reconnect_times = parseInt($('#reconnect_time').val());
		// 重连间隔时间
		data.highInfo.transfer.reconnect_interval = parseInt($('#reconnect_interval').val());
		// 重连次数数字检测
		if(!Number.isInteger(data.highInfo.transfer.reconnect_times)){
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_TIPS);
			return false;
		}
		// 重连次数大小1 - 999
		if (data.highInfo.transfer.reconnect_times < 1) {
			UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
			return false;
		}
		// 重连间隔时间最小5
		if(data.highInfo.transfer.reconnect_interval < 5){
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_MIN_TIPS);
			return false;
		}
		// 重连间隔时间最大60
		if(data.highInfo.transfer.reconnect_interval > 60){
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_MAX_TIPS);
			return false;
		}
		// 重连间隔数字检测
		if(!Number.isInteger(data.highInfo.transfer.reconnect_interval)){
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_TIPS);
			return false;
		}
		data.highInfo.transfer.network = $('#transferNetwork').val();
		showStep2(data);
		return true;
    }

    var showStep1 = function(data){
		var des = "";
		des += LANG.UI_JOB_TIMEPOINT_INFO+':' + data[0].os_timepoint_str + "(" + data[0].os_name + ")" + "<br>";
		des += LANG.UI_OS_TARGET_IP + data[0].destination_agent_ip + "<br>";
		des +=LANG.UI_OS_PLUG_REBUILT_VOL+ ":" + intToStr(data[0].reparted_flag) + "<br>";
		if("1" != data[0].os_type){
			des +=LANG.UI_OS_PLUG_REPAIR_DIFF_MACHINE+ ":" + intToStr(data[0].repair_linux_flag) + "<br>";
		}
		var desdetails = "";
		//得到分区信息
		if(data[0].reparted_flag == 2){
			var allocation_data = data[0].partition_allocation_strategy
		}else{
			var allocation_data = data[0].disk_allocation_strategy
		}
		//循环读取信息
		for(var i in allocation_data){
			desdetails += allocation_data[i].source_name+"("+allocation_data[i].source_sizeStr+")"+"->" + allocation_data[i].target_name+"("+allocation_data[i].target_sizeStr+")" +"<br>";
		}
		
		des += "" + desdetails + "<br>";
		$('.recovershow').html(des);
	}
	var showStep2 =  function(data){
		//得到描述
		var des = LANG.UI_COPY_BACK_ENCRYPT + ": " + getSwitchDes(data.highInfo.transfer.encrypt) + '<br>';
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
			des += encryptedMethodLabel + ": " + grade + "<br>";
		}
		//传输网络
		if(networkFlag){
			var transfernetworklabel = $('.transfernetworklabel').html();
			des += transfernetworklabel + ": " + $('#transferNetwork').find("option:selected").text()  + '<br>';
		}
		// 重连次数
		if(parseInt($('#reconnect_time').val()) == 0){
			des += $('.reconnect-times-Label').text() + ": " + LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE +"<br>";
		}else{
			des += $('.reconnect-times-Label').text() + ": " + $('#reconnect_time').val() + LANG.UI_VOL_CDP_BACKUP_TIMES + '<br>';
		}
		// 重连间隔时间
		des += $('.reconnect-Interval-Label').text() + ": " + $('#reconnect_interval').val() + LANG.UI_PUBLIC_SECOND;
		$('.transfershow').html(des);
		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedlimitsStr = '';
		speedlimitsStr = $('.speedlimitDes').prop('title');

		if (speedlimitsStr == '') {
			speedlimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedlimitsStr);
		if(data.highInfo.threadnum){
			$('.threadNumshow').html(data.highInfo.threadnum);
		}

	}
    //int类型 1转换成开启,2转化成关闭
	var intToStr = function(data){
		var str = "";
		 if(data ==1){
			 str = LANG.UI_PUBLIC_ON
		 }else{
			 str = LANG.UI_PUBLIC_OFF
		 }
		 return str;
	}
    //得到可用的代理主机IP
    var getHostIP = function(){
        var data = {};
        data.taskuuid = $('#task_uuid').val();
        var params = JSON.stringify(data);
        //得到所有可用代理主机IP
		$.post(CONF.AJAXPATH, {m:34,f:'getRecoverHostIP',p:params}, function(datas){
            if(datas == "" || datas == null || datas == "[]"){
				UIToastr.showInfo(LANG.UI_OS_RECOVERY_HOST_NULL, LANG.UI_OS_RECOVERY_HOST_NULL_ERROR);
			}
            var p = JSON.parse(datas);
            //先清空option
			$("#IPlistSelect").empty();
			var authorizationGroup = "";
			var NoAuthorizationGroup = "";
            //------
			//添加授权分组
			for(var i=0; i<p.length; i++){
				if(p[i].type == 2){
					authorizationGroup += "<option value='"+p[i].agent_uuid+"'>"+p[i].agent_name+"</option>";
				}
			}
			for(var i=0; i<p.length; i++){
				//如果是授权
				if(p[i].type == 2){
					$("#IPlistSelect").append('<optgroup label='+LANG.UI_OS_AUTHORIZE_HOST+'>'+authorizationGroup+'</optgroup>');
					break;
				}
			}
			//------
			//添加未授权分组
			for(var i=0; i<p.length; i++){
				if(p[i].type == 0 || p[i].type == 1){
					NoAuthorizationGroup += "<option value='"+p[i].agent_uuid+"'>"+p[i].agent_name+"</option>";
				}
			}
			for(var i=0; i<p.length; i++){
				if(p[i].type == 0 || p[i].type == 1){
					$("#IPlistSelect").append('<optgroup label='+LANG.UI_OS_UNAUTHORIZE_HOST+'>'+NoAuthorizationGroup+'</optgroup>');
					break
				}
			}
			//------
			//初始化插件
			initSelectIp();
			$(".selectpicker").selectpicker('refresh');
			$("#zoneInfo").hide();
        });
    }
    //初始化下拉框
    var initSelectIp = function(){
		//初始化下拉框
		$(".selectpicker").selectpicker({
			liveSearch: true, //是否显示搜索框,
			actionsBox: false,//是否显示全部
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});
	}
    //选择目标主机时的链接测试
    var funcInputIP = function(){
        $agent_uuid_list = $('#IPlistSelect').selectpicker('val');
		if($agent_uuid_list.length == 0){
			UIToastr.showInfo(LANG.UI_OS_RECOVERY_LINK_TEST, LANG.UI_OS_RECOVERY_LINK_TEST_ERROR);
			return;
		}
		var data = {};
		data.agentList = $agent_uuid_list;
        data.taskuuid  = $('#task_uuid').val();
        data = JSON.stringify(data);
		Metronic.blockUI({target: '#tab1',animate: true});
		$.post(CONF.AJAXPATH, {m:34,f:'osInstantlinkTest',p:data}, function(datas){
			var p = JSON.parse(datas);
			linkInfo = p;
			if(p.re){
				UIToastr.showSuccess(LANG.UI_OS_RECOVERY_LINK_TEST, LANG.UI_OS_RECOVERY_LINK_TEST_SUCCES);
				linkFlag = true; 
				//是否需要显示传输网络标记
				networkFlag = false;
				networkFlag = getNetworkFlag(p.newList);
				// //设置假数据
				// networkFlag = true;
				//初始化传输网络
				if(networkFlag){
					$('.transfernetworkDiv').show();
					initNetworkList();
				}else{
					$('.transfernetworkDiv').hide();
				}
				$("#osTable").osRecoveryConfig(p);
				$("#zoneInfo").show();
				Metronic.unblockUI('#tab1');
			}else{
				UIToastr.showWarning(p.title, p.msg);
				Metronic.unblockUI('#tab1');
			}
		});
    }
    //获取显示传输网络标志
	var getNetworkFlag = function(agent){
		for(var i=0;i<agent.length;i++){
			//客户端连接服务端
			if(agent[i].net_model == 2){
				networkFlag = true;
			}
		}
		
		return networkFlag;
	}
    //初始化节点传输网络列表
	var initNetworkList = function(){
		var data = {};
		data.taskuuid = $('#task_uuid').val();;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:34,f:'osInstantgetNetList',p:p}, function(d){
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
			
    	});
	}
    //加载策略对应描述
	var initStrategyDes = function(){
		initHighStrategyDes();
	}
    //初始化高级策略
	var initHighStrategyDes = function(){
		des = "";
		var osThreadNum = $("#osThreadNum").val();
		des +=LANG.UI_GLOBAL_STRATEGY_THREAD_NUM+": " + osThreadNum
		$('.highDes').html(des);
		$('.highDes').prop('title', des);
	}
	//初始化限速策略
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
	//限速模式改变
	var speedModeHandler = function(){
        if(this.value == 2){
            $('.setSpeedStrategy').hide();
        }else{
            $('.setSpeedStrategy').show();
        }
	}
	//传输线程改变
	var threadChange = function(){
		var num = $("#osThreadNum").val();
		if(num > 8 || num < 1 || num == ""){
			//还原默认值并给出提示
			$('#osThreadNum').val(3);
			$('#backupThreadNum').spinner('value',3);
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
		}
		initHighStrategyDes();
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
					'<div class="cont">' + 
						'<div class="cont-col1"></div>' + 
						'<div class="cont-col2">' + 
							'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>'+ 
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
					'<div class="cont">' + 
						'<div class="cont-col1"></div>' + 
						'<div class="cont-col2">' + 
							'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap"> '+ info.des +  '</div>' + 
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
		//检测结束时间是否大于开始时间
		if(!checkTime(info.startTime,info.endTime)) return;
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
		});
		speedList.push(info);
		$('#speedlimitModal').modal('hide');
	}
	var checkSimpleForever = function(mode){
        for(var i=0;i<speedList.length;i++){
            if(mode == speedList[i].mode){
                return false;
            }
        }
        return true;
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
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

    //提交
    var submit = function(){
		data.taskuuid = $('#task_uuid').val();
		data.speedLimit = speedSubmitInfo();
		var jsonData =  JSON.stringify(data);
		Metronic.blockUI({target: '#osmotioncontent',animate: true, cenrerY: true,});
		$.post(CONF.AJAXPATH, {m:34,f:'createMotionJob',p:jsonData}, function(d){
			Metronic.unblockUI('#osmotioncontent');
			if(OPREL(d)){
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
				info['speed'] = [{'des': selectedRow[0].detail}]
			}
		} else {
			// 自定义
			info['speed'] = $('#speedstrategy').getSpeedStrategyConfigFinal();
		}
		return info;
	}

    return {
        init:function(){
            inintDatatimePicker();
            wizardInit();
			getHostIP(); //得到可用的代理主机IP
            initListener();
			initSpinner();
        }
    }


}();

jQuery(document).ready(function(){
    OSMotion.init();
})