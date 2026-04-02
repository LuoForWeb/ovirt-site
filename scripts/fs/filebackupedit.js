var FileBackup = function () {
	var data = {srcInfo:{agentList:[]},backupInfo:{},highInfo:{}};
	var zTree;
	var zTreeFile = [];
	var editFlag = true;
	var searchFlag = false;
	var rechoose = false
	var _pageSize = 40; //代理端文件列表每次显示条数;
	var _path = '';		 //当前路径
	var _timeStamp = '';	//时钟时间戳,全局
	var nodeSelectFlag = false; //自定义节点选择加载标志
	var SETTINGS;
	var pageIndex = 0; //轮播索引
	var initSpeedFlag = false;
	var speedList = [];
	var initStrategyFlag = false;
	var defaultStrategy = [];
	var globalStrategy = [];
	var oldNode, networkFlag = false;	//用于比对加载传输网络的节点
	var currentmax = 0;
	var nextFlag = true;//判断修改是否能进入下一步
	var offlineAgent = [];//用于保存离线客户端数据
	var passwordChangeFlag = false; // 修改任务时是否改变了密码框内容标记
	var firstInitPageFlag = false; // 首次进入页面标记
	var nodeParamList;//用于保存搜索agent的结果
	var _oldPassword = '';
	var backupStrategy = {};
	let firstInitStep2 = false;
	let backupTargetInfo = '';
	let firstInitWorm = true;//是否是修改任务第一次初始化worm
	var initData = function(){
		//setp1
		data.srcInfo.fileInfo = [];
		data.srcInfo.agentList = [];
		data.srcInfo.groupList = [];
		
		for(var i = 0; i<SETTINGS.fileinfo.length; i++){
			data.srcInfo.fileInfo.push([SETTINGS.fileinfo[i].type,
										SETTINGS.fileinfo[i].path,
										SETTINGS.fileinfo[i].name],
										SETTINGS.fileinfo[i].codetype);
		}
		//setp2
		//备份方式:策略/时间
		data.backupInfo.type = SETTINGS.timestrategy.type;
		//完备/增备/差备
		data.backupInfo.fullInfo = {};
		data.backupInfo.incrInfo = {};
		data.backupInfo.diffInfo = {};
		var timestrategy = SETTINGS.timestrategy.data;
		//按时间备份的时间
		data.backupInfo.datetime = null;
		if(SETTINGS.timestrategy.type == "oncetime"){
			data.backupInfo.datetime = SETTINGS.timestrategy.data;
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
                info.start_time = timestrategy[i].start_time;
                info.roll_flag = timestrategy[i].roll_flag;
                info.roll_interval = timestrategy[i].roll_interval;
                info.end_time = timestrategy[i].roll_end_time;
                info.roll_end_time = timestrategy[i].roll_end_time;
                info.frequency = timestrategy[i].frequency;
				info.startTime = timestrategy[i].start_time;
				info.endTime = timestrategy[i].roll_end_time;
				info.rollFlag = timestrategy[i].roll_flag;
                info.rollInterval = timestrategy[i].roll_interval;
				info.full_backup_compensation_flag = timestrategy[i].full_backup_compensation_flag;
				if(timestrategy[i].mode == '1'){
					data.backupInfo.fullInfo = info;
				}else if(timestrategy[i].mode == '2'){
					//只有一条则是永久增量
					if (1 == timestrategy.length) {
						//php组合消息的参数
						data.backupInfo.pincrInfo = info;
						data.backupInfo.pincrInfo.mode = '9';
						//插件选中的参数
						data.backupInfo.pIncrInfo = info;
						data.backupInfo.pIncrInfo.mode = '9';
					} else {
						data.backupInfo.incrInfo = info;
					}
				}else if(timestrategy[i].mode == '3'){
					data.backupInfo.diffInfo = info;
				}
			}
		}
		
		//setp3
		//保留策略
		data.highInfo.reserve = {};
		data.highInfo.reserve.type = SETTINGS.brs.type;
		data.highInfo.reserve.value = SETTINGS.brs.number;
		data.highInfo.reserve.strategyMode = SETTINGS.brs.strategy_mode;
		
		data.highInfo.transfer = {};
		data.highInfo.transfer.encrypt = SETTINGS.bts.encrypt;
		data.highInfo.transfer.encrypt_method = SETTINGS.bts.encrypt_method;
		data.highInfo.transfer.mode = SETTINGS.bts.mode;
		data.highInfo.transfer.network = SETTINGS.bts.network;
		data.highInfo.transfer.network_pool_uuid = SETTINGS.bts.network_pool_uuid;
		data.highInfo.transfer.reconnect_times = SETTINGS.bts.reconnect_times;
		data.highInfo.transfer.reconnect_interval = SETTINGS.bts.reconnect_interval;
		
		data.highInfo.store = {};
		data.highInfo.store.compress = true;
		data.highInfo.store.encrypt = true;
		data.highInfo.store.valid = true;
		
		data.highInfo.node = {};
		data.highInfo.node = SETTINGS.node;
		
		data.highInfo.newstr = {};
		data.highInfo.newstr.silentsnapshotcheck = SETTINGS.high.snap_shot_flag;//快照
        data.highInfo.newstr.backupThreadNum = SETTINGS.high.thread_num;//线程数量
		data.highInfo.newstr.scanThreadNum = SETTINGS.high.scan_thread_num;//扫描线程
		data.highInfo.newstr.scanFileNum = SETTINGS.high.scan_file_num;//扫描文件速度
		data.highInfo.permission_operate_flag = SETTINGS.high.permission_operate_flag;//文件权限备份
		data.highInfo.skip_file_alarm_flag = SETTINGS.high.skip_file_alarm_flag;//跳过文件告警
		data.highInfo.skip_file_alarm_min_num = SETTINGS.high.skip_file_alarm_min_num;
		data.highInfo.skip_file_alarm_min_ratio = SETTINGS.high.skip_file_alarm_min_ratio;
		//通配符
		// data.highInfo.newstr.wildcardmode = SETTINGS.high.wildcard_mode;
        // data.highInfo.newstr.wildcardstr = SETTINGS.high.wildcard;
		
			
		//存储策略
		data.highInfo.store.compress = SETTINGS.bss.compress;//压缩
		data.highInfo.store.dataencrypt = SETTINGS.bss.encrypt; //数据加密
		data.highInfo.store.password_auto_flag = SETTINGS.bss.password_auto_flag;//自动生成密码
		data.highInfo.store.password = SETTINGS.bss.password;
		data.highInfo.store.compress_method = SETTINGS.bss.compress_method;
		data.highInfo.store.encrypt_method = SETTINGS.bss.encrypt_method;
		firstInitPageFlag = true;
		
		//限速策略
		speedList = SETTINGS.speedInfo;
		data.speedLimit = SETTINGS.speedInfo;
		data.speedLimit.speed = SETTINGS.speedInfo.speedInfo;
		
		//任务信息
		data.taskName = SETTINGS.taskname;
		data.taskuuid = SETTINGS.taskuuid;
		//备份策略
		backupStrategy.time = {};
		backupStrategy.store = {};
		backupStrategy.reserve = {};
		backupStrategy.time.timeInfo = data.backupInfo;
        backupStrategy.time.type = data.backupInfo.type;
		backupStrategy.speedlimit = SETTINGS.speedInfo;
		backupStrategy.store.storeInfo = SETTINGS.bss;
		backupStrategy.reserve.reserveInfo = data.highInfo.reserve;
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.FS, true, backupStrategy);
		//重试策略
		data.retry_strategy = SETTINGS.high.retry_strategy;
		//安全策略
		data.safe_strategy = SETTINGS.high.safe_strategy
		data.safe_strategy.integrity_check_flag = SETTINGS.high.safe_strategy.integrity_check_flag ? 1 : 0;
		data.safe_strategy.virus_scan_flag = SETTINGS.high.safe_strategy.virus_scan_flag ? 1 : 0;
		data.safe_strategy.worm_flag = SETTINGS.high.safe_strategy.worm_flag ? 1 : 0;
		data.safe_strategy.virus_scan_config_list = "";
		data.safe_strategy.integrity_check_config.recovery_error_policy = -1;
		//过载保护
		data.highInfo.ignore_resource_limiting_flag = SETTINGS.high.ignore_resource_limiting_flag
	}
	
	var initListener = function(){
		$('#toAdd').on('click',function(){
	    	LOCATION('./content/client/client.php', 'client');
		});
		$('#allFileTree').on('click','button.addInput',wildInputAdd);//添加通配符输入框
		$('#allFileTree').on('click','.delInput',delInput);
		$('#searchAgent').on('propertychange', debounceFs).on('input', debounceFs);
		$('#allFileTree').on('change','.wildcardmode', wildcardmodeTypeHandler);
		
		//选择备份策略复选框
		$('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);

		//切换自动选择存储加密密码
		// $('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
		
		//数据加密密码确认
		$('#repassword,#password').on('input propertychange', function(){
			passwordChangeFlag = true;
		});
		$('#allFileTree').on('click','input.apply-to-all-client',applyToAllClient);
		//扫描文件
		$("#scanFileNum").on('change', function(){
            initHighStrategyDes();
		});
		initStoreListeners();
		//跳过文件告警智能判断
		$('#passfilealarmcheck').on('switchChange.bootstrapSwitch', passAlarmChange);
		// 传输策略---加密传输
        $('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptChange);
		$('#backuptype').on('change', backupTypeHandler);
		// 保留策略 - 备份数据保留类型change
        $('#reserveMode').on('change', () => {
            initReserveStrategyDes();
        });
		$('#spinnerNum').on('input propertychange', initReserveStrategyDes);
		$('#spinnerDay').on('input propertychange', initReserveStrategyDes);
		$('#fullBackup').on('ifChecked ifUnchecked ',specialHandler);
	}

	var backupTypeHandler = function(){
		specialHandler();
	}

	// 显示加密算法
	var transferEncryptChange = function(){
		if(this.checked){
			$('.transfer-encrypt-method-form').show();
		}else{
			$('.transfer-encrypt-method-form').hide();
		}
	}
    
    // 显示压缩等级
    var compressChange = function () {
        if (this.checked) {
            $('.compressGradeDiv').show();
        } else {
            $('.compressGradeDiv').hide();
        }
    };
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
		}else {
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
	//存储加密切换
	var encryptChange = function(){
		if(this.checked){
			$('#passwordAutocheck').bootstrapSwitch('state', true);  
			$('#password').empty();
			$('#repassword').empty();
			$('.passwordModeDiv').show();
            $('.storage-encrypt-div').show();
		}else{
			$('.passwordModeDiv').hide();
			$('.passwordDiv').hide();
            $('.storage-encrypt-div').hide();
		}
	}
	var wildcardmodeTypeHandler = function() {
		var wildcardmode = $(this).val();
		if(wildcardmode != 0) {
			$(this).parents('.form-group.wildmode').siblings('.wildcarddiv').show();
		}else {
			$(this).parents('.form-group.wildmode').siblings('.wildcarddiv').hide();
			$(this).parents('.form-group.wildmode').siblings().last().find('div.delcontent').remove();
		}
		
	}
	var wildInputAdd = function () {
		var content = $.trim($(this).prev('.wildcardInputdiv').val());
		if(content!='') {
			let html = '<div class="delcontent" title="' + content + '">' + 
							'<span class="wildcardInput">' + content + '</span>' +
							'<span class="delInput">×</span>' +
						'</div>';
			let agent_uuid = $(this).attr('agent_uuid');
			$('.wilcardsList_' + agent_uuid).append(html);
			$(this).prev('.wildcardInputdiv').val('');
		}else {
			UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
			return false;
		}
	}
	var delInput = function() {
		$(this).parents('.delcontent')[0].remove();
	}
	//初始化时间策略描述
	var initSpeedStrategyDes = function(){
		var titleDes = "";
		var des = "";
		if (speedList.length == 0) {
			return
		}

		$('#tasklevelselect').val(speedList.level);
		$('#speedtypeselect').val(speedList.type);
		if (speedList.type == 1) {
			$('#show_type_1').show();
			$('#show_type_2').hide();
			$('#task_type_global_speed_strategy').show();
		} else {
			$('#show_type_2').show();
			$('#show_type_1').hide();
			$('#task_type_global_speed_strategy').hide();
		}

		// 如果是之前的自定义的 方式不变 但是如果是选择的全局限速策略的话，那么需要读取出所有的全局限速策略列表，然后根据列表的id取出限速信息
		if (speedList.type == 1) {
			var global_speed_limit = speedList.uuid
			setTimeout(function (){
				$(".strategy-table #table").bootstrapTable('checkBy', {
					field: 'uuid',
					values: [global_speed_limit]
				})
				// 下面的需要初始化全局限速策略表格并携带参数
				let rowData = $(".strategy-table #table").bootstrapTable("getData");

				var datas = [];
				for(var j in rowData) {
					if (rowData[j].uuid == global_speed_limit) {
						datas = rowData[j].detail;
						datas = datas.split('</br>');
					}
				}
				if(datas.length !=0){
					des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + datas.length;
				}

				for(var i in datas){
					titleDes += datas[i] + '. ';
				}

				$('.speedlimitDes').html(des);
				$('.speedlimitDes').prop('title', titleDes);
			},1500);
		} else {
			var speedInfo = speedList.speedInfo;
			// 自定义
			if(speedInfo.length > 0){
				des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedInfo.length;
			}

			var strategy_type = speedList.strategy_type;
			for(var i in speedInfo){
				titleDes += speedInfo[i].des + '. ';
			}
			addGlobalStrategy.init({'strategy_type':strategy_type,speedInfo:speedInfo,initSpeedFlag:1});

			setTimeout(function (){
				$('.speedlimitDes').html(des);
				$('.speedlimitDes').prop('title', titleDes);
			}, 1000);
		}
	}
	
	var checkSimpleForever = function(mode){
        for(var i=0;i<speedList.length;i++){
            if(mode == speedList[i].type){
                return false;
            }
        }

        return true;
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
            info.type = 4;
            if(!checkSimpleForever(info.type)){
                UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD_FOREVER_TIPS);
                return false;
            } 
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
            initSpeedStrategyDes();
        });
        speedList.push(info);
        $('#speedlimitModal').modal('hide');
        initSpeedStrategyDes();
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
	
	var speedModeHandler = function(){
        if(this.value == 2){
            $('.setSpeedStrategy').hide();
        }else{
            $('.setSpeedStrategy').show();
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

	var strategyModeClick = function(event){
		var mode = $(this).data('mode');
		if(event.target.checked){
			//如果是取消选中
			$('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').hide();
		}else{
			//如果是选中
			$('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').show();
		}
		$('.reserveNum').show();
        $('.reserveDay').hide();
        $('#reserveType').removeAttr("disabled");
        $('#reserveType option[value="3"]').hide();
        $('#reserveType').val(1);
		if (1 == mode) {
			//完全备份
			if (event.target.checked) {
				//取消完备同时取消增量
				$('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
				//取消完备同时取消差异
				$('#strategymode').find('input[data-mode=3]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
			} else {
				//选中完备同时取消永久增量
				$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
			}
		} else if (2 == mode) {
			//增量备份
			if (!event.target.checked) {
				//选中增量同时选中完备 取消差异和永久增量
				$('#strategymode').find('input[data-mode=1]').iCheck('check');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
				$('#strategymode').find('input[data-mode=3]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
				$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
			}
		} else if (3 == mode) {
			//差异备份
			if (!event.target.checked) {
				//选中差异同时取消增量和永久增量
				$('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
				//选中差异同时选中完备
				$('#strategymode').find('input[data-mode=1]').iCheck('check');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
				$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
			}
		} else if (9 == mode) {
            //永久增量
            if (!event.target.checked) {
                //选中永久增量其他全部取消
                $('#strategymode').find('input[data-mode=1]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').hide();
                $('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
                $('#strategymode').find('input[data-mode=3]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
            }
        }
		initReserveStrategyDes();
		setTimeout(initWormConfig, 0);
	}

	/**
	 * 初始化备份目的地
	 */
	const initBackupTarget = () => {
		if(firstInitStep2) return;
		$('#backupTarget').backupTarget({
			node_uuid: SETTINGS.node.nodeuuid,
			node_pool_uuid: SETTINGS.node.node_pool_uuid,
			storage_uuid: SETTINGS.node.storageuuid,
			storage_pool_uuid: SETTINGS.node.storage_pool_uuid,
			storage_pool_type: SETTINGS.node.storage_pool_type,
		});
		firstInitStep2 = true;
	};
	
	var initSpinner = function(){
		initReserveSpinner($('#spinnerDay'));
		initReserveSpinner($('#spinnerNum'));
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
		$('.scanThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
		$('#spinnerpercent').spinner({value: 20, step: 5, min: 1,max: 100});//跳过文件告警比例
		$('.passfilenumDiv').spinner({value:10, step: 5, min: 1, max: 999999999});//跳过文件告警个数
		// 不显示永久增量描述
		$('.all-strategy-tips').hide();
		$('.no-pIncr-tips').show();
	}
	
	var reserveTypeHandler = function(){
		if(CONF.RESERVE_TYPE.NUM == this.value){
			$('.reserveNum').show();
			$('.reserveDay').hide();
			$('#spinnerNum').spinner('value', 30);
		}else if(CONF.RESERVE_TYPE.DAY == this.value){
			$('.reserveNum').hide();
			$('.reserveDay').show();
			$('#spinnerDay').spinner('value', 30);
		}else if(CONF.RESERVE_TYPE.PERMANENT == this.value){
			$('.reserveNum').hide();
			$('.reserveDay').hide();
		}
		data.highInfo.reserve.type = this.value;
		//修改保留策略信息
		initReserveStrategyDes();
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
		offlineAgent = [];
		// data.srcInfo.agentOnline = [];
		//未选择代理返回
		var allnodes = zTree.transformToArray(zTree.getNodes());
		allnodes.forEach(item => {
			if(item.chkDisabled) {
				item.chkDisabled = false;//取消chkDisabled，使getCheckedNodes获取到离线的主机
			}
		});
		var nodes = zTree.getCheckedNodes(true);
		data.srcInfo.fileInfo = [];
		data.highInfo.newstr.wildcard_list = [];
		var showFileArr = [];
		var allCheckedNode = [];
		var checkChildFileArr = [];
		var checkoutFlag = true;//检测通配符
		var filecheck = false;
		
		//是否需要显示传输网络标记
		networkFlag = false;
		nodes.forEach(item => {
			//所有选中的文件（未过滤）
			if(item.eventtype == "agent" && zTreeFile[item.uuid]!=undefined && zTreeFile[item.uuid].getCheckedNodes(true).length !=0) {
				data.srcInfo.fileInfo.push(zTreeFile[item.uuid].getCheckedNodes(true));
				//用于检测应用到其他客户端  在其他客户端一个文件都未找到时，只选中父级的情况
				checkChildFileArr.push(zTreeFile[item.uuid].getCheckedNodes(true));
			}else if(item.eventtype == "agent" && zTreeFile[item.uuid]!=undefined && zTreeFile[item.uuid].getCheckedNodes(true).length ==0) {
				filecheck = true;//判断每个客户端是否都选择了文件
			}else if(zTreeFile[item.uuid]==undefined) {//保存离线客户端信息
				SETTINGS.fileinfo.forEach(eachfile=> {
					if(eachfile.agentuuid == item.uuid) {
						offlineAgent.push([eachfile.type.toString(),eachfile.path,eachfile.agentuuid,eachfile.groupuuid,eachfile.codetype]);
					}
				});
			}
			//通配符
			if(item.eventtype == "agent"){
				var wildcardInput = [];//所有通配符
				var wildcardRealLen = [];//所有通配符的长度（用于修改时更新数据库）
				if($('#high_tabagent_tree_'+item.uuid).find('span.wildcardInput').length==0 && $('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val() != undefined && $('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val() != 0) {
					UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
						checkoutFlag = false;
						return false;
				}
				for(var i=0;i<$('#high_tabagent_tree_'+item.uuid).find('span.wildcardInput').length;i++) {
					var str = $.trim($('#high_tabagent_tree_'+item.uuid).find('span.wildcardInput').eq(i)[0].innerText);
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
					}else if($('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val()!=0){
						UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
						checkoutFlag = false;
						return false;
					}
					wildcardInput.push(str.replace(/\*+/g,'*')); //把所有多个*的地方替换成1个*
					str = str.replace(/[\?\*]/g, '');//去掉所有问号和星号，用于计算长度
					wildcardRealLen.push(str.length.toString());
				}
				if(item.isCheckFlag && item.checked) {//离线且选中的
					//通配符
					SETTINGS.high.wildcardinfo.forEach(eachwildcard=> {
						if(eachwildcard[0] == item.uuid) {
							if(eachwildcard.wildcard_mode==0) {
								data.highInfo.newstr.wildcard_list.push([item.uuid,[],"0",[]]);
							}else{
								data.highInfo.newstr.wildcard_list.push([item.uuid,eachwildcard.wildcard,eachwildcard.wildcard_mode,eachwildcard.wildcard_real_length]);
							}
						}
					});
					// 分组
					if($.inArray(item.pId, data.srcInfo.groupList) == -1) {
						data.srcInfo.groupList.push(item.pId);
					}
				}else {
					data.highInfo.newstr.wildcard_list.push([item.uuid,wildcardInput,$('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val(),wildcardRealLen]);
				}
			}
		});
		//得到所有勾选状态是全选中的节点
		for(var i=0; i<data.srcInfo.fileInfo.length; i++){
			var eachAgentNode = [];
			for(var j=0; j<data.srcInfo.fileInfo[i].length; j++) {
				//check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
				if(data.srcInfo.fileInfo[i][j].check_Child_State == 2 || data.srcInfo.fileInfo[i][j].check_Child_State == -1) {
					eachAgentNode.push(data.srcInfo.fileInfo[i][j]);
				}
			}
			//循环完一个客户端之后  过滤掉单个客户端中重复的
			for(var m = 0;m<eachAgentNode.length;m++) {
				if(eachAgentNode[m].type != 1) {//磁盘或文件夹
					for(var n = 0;n<eachAgentNode.length;n++) {
						var str = eachAgentNode[n].pId==null ?'':eachAgentNode[n].pId;
						if(str.includes(eachAgentNode[m].filepath) && eachAgentNode.indexOf(eachAgentNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
							eachAgentNode.splice(eachAgentNode.indexOf(eachAgentNode[n]),1);
							n--;
						}
					}
				}
			}
			allCheckedNode.push(eachAgentNode);
		}
		//二维数组转一维数组
		allCheckedNode = [].concat.apply([], allCheckedNode);
		data.srcInfo.fileInfo = [];
		allCheckedNode.forEach(item=> {
			data.srcInfo.fileInfo.push([item.type,item.filepath,item.uuid,item.groupuuid,item.code_type]);//组装发送给后台的文件信息
			showFileArr.push([item.filepath,item.uuid]);
		});
		// 加入离线客户端
		offlineAgent.forEach(offagent=> {
			data.srcInfo.fileInfo.push(offagent);
			showFileArr.push([offagent[1],offagent[2]]);
		})
		//还原chkDisabled
		allnodes.forEach(item => {
			if(item.isCheckFlag) {
				item.chkDisabled = true;
			}
		});
		if(nodes.length == 0){
			UIToastr.showWarning(LANG.UI_DB_NO_CHOOSE_BACKUP_PROXY, LANG.UI_DB_CHOOSE_BACKUP_PROXY_FIRST);
			return false;
		}
		// 判断修改备份源是否加载完成
		if(!nextFlag) {
			UIToastr.showWarning(LANG.UI_DATA_FILE_FILE + LANG.UI_PUBLIC_BACKUP, LANG.UI_FILE_BAK_LOADING);
			return false;
		}
		if(!filecheck) {//用于检测应用到其他客户端  在其他客户端一个文件都未找到时，只选中父级的情况
			filecheck = checkChildFile(checkChildFileArr);
		}
		if(data.srcInfo.fileInfo.length==0) {//判断网速极慢时快速切换代理  当前代理的树还没加载出来直接点提交
			filecheck = true;
		}
		if(filecheck){//检查是否每个已选中客户端都选中了文件
			UIToastr.showWarning(LANG.UI_BACKUP_FILE_NO_SELECT_TITLE1, LANG.UI_BACKUP_FILE_NO_SELECT_VALUE1);
			return false;
		}
		if(!checkoutFlag) return false; 
		//初始化时间策略模块
		Strategy.initModule(FileBackup);
		showStep1(showFileArr);
		
		//初始化节点
		// initNodeSelect();
		initBackupTarget();
		getFsCurrentUseLicense().then(showTitleCallback);
		return false;
	}
	var checkChildFile = function(filelist) {
		var allfilestate = [];
		filelist.forEach(eachAgent => {
			var filestate = [];
			eachAgent.forEach(item => {
				if(item.check_Child_State == -1) {
					filestate.push(item.check_Child_State);
				}
			});
			allfilestate.push(filestate);
		});
		var result = allfilestate.some(eacharr=>{
			return eacharr.length == 0;
		});
		return result;
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
	
	var showStep1 = function(showFileArr){
		//初始化计时器
		initServerTime();
		var nodes = zTree.getCheckedNodes(true);
		var showStr = '';
		var backupmode4Info = '';
		var eachwildcard2 = '';
		data.highInfo.newstr.wildcard_list.forEach(item => {
			var node = zTree.getNodesByParam("uuid", item[0], null);
			//设置文件列表显示
			showStr +='<strong>'+ node[0].name+ ':' + '</strong><br>';
			showFileArr.forEach(eachpath=> {
				if(node[0].uuid==eachpath[1]) {
					showStr += eachpath[0]+ ';' + '<br>';
				}
			});
			//设置通配符显示
			backupmode4Info += '<strong>'+ node[0].name+ ':' + '</strong><br>';
			data.highInfo.newstr.wildcard_list.forEach(eachwildcard=> {
				if(eachwildcard[0]==node[0].uuid) {
					if(eachwildcard[1]!="") {
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
		});
		$('.backupmodeshow4').html(LANG.UI_FILE_WILDCARD +':');
		$('.backupmodeshow4list').html(backupmode4Info);
		$('#filelist').html(showStr);

	}

	//获取显示传输网络标志
	var getNetworkFlag = function () {
		backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		let agentNodes = zTree.getCheckedNodes();
		let flag = false;
		for (const agentNode of agentNodes) {
			//客户端连接服务端
			if (parseInt(agentNode.net_model) === 2) {
				flag = true;
				break;
			}
		}
		//多客户端直接不显示传输网络
		if (data.srcInfo.agentList.length > 1 || false === backupTargetInfo || !backupTargetInfo.node_uuid) {
			flag = false;
		}
		return flag;
	}
	
	var step2Valid = function(){
		if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		//是否需要显示传输网络标记
		networkFlag = getNetworkFlag();
		showStep2();
		return true;
	}
	
	var showStep2 = function(){
		//备份目的地(节点)
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
		//初始化传输网络
		if(networkFlag && data.highInfo.node.nodeuuid != ""){
			$('.transfernetworkDiv').show();
			initNetworkList(backupTargetInfo.node_uuid);
		}else{
			$('.transfernetworkDiv').hide();
		}
		//云存储默认备份数据保留类型为按备份链保留
		// if(backupTargetInfo.storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
		// 	$('#reserveMode').val('2').prop('disabled', true); // 禁用备份数据保留类型
		// } else {
		// 	$('#reserveMode').prop('disabled', false); // 启用备份数据保留类型
		// }
		//存储为磁带时，屏蔽保留策略，传输线程禁用，默认是1，屏蔽提示信息
		//判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
		getTapeStrategy(backupTargetInfo.storage_uuid, backupTargetInfo.storage_type, '.threadDiv', '.backupThreadDiv');
		if(backupTargetInfo.storage_type != CONF.BD_STORAGE_TYPE.TAPE) {
			initReserveStrategyDes();
			$('.pIncr').show();
			$('.all-strategy-tips').show();
			$('.no-pIncr-tips').hide();
			$('.safeLi').show();
			$('.safeDiv').show();
		} else {//磁带
			$('#pincrBackup').iCheck('uncheck');
			$('.pIncr').hide();
			$('.all-strategy-tips').hide();
			$('.no-pIncr-tips').show();
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
			data.highInfo.newstr.backupThreadNum = 1;
		}


		if(Object.keys($('#archivecheck')).length != 0 && $('#archivecheck').get(0).checked) {
			//选择磁带存储时，不能开启归档
			if (backupTargetInfo.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
				UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_DES, LANG.UI_FILE_ARCHIVE_TIPS_TAPE);
				return false;
			}
		}
		initWormConfig();
		return true;
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
		let pIncrBackup = $('#pincrBackup').prop('checked');  // 永久增量是否选中
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
				$('#wormConfig').wormProtectionBackup(true, 'col-md-3',false, SETTINGS.high.safe_strategy.worm_flag, SETTINGS.high.safe_strategy.worm_protection_time);
				firstInitWorm = false;
			} else {
				$('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal);
			}
        } 
	};
	var getTimeStr =function () {
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		data.backupInfo.fullInfo = {};
		data.backupInfo.incrInfo = {};
		data.backupInfo.diffInfo = {};
		data.backupInfo.pincrInfo = {};
		data.backupInfo.type = $('#backuptype').val();
		var strategyMode = $('#strategymode').find('input:checked');
		if("strategy" == data.backupInfo.type){
			if(0 == strategyMode.length){
				//没有选择时间策略
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_FILE_BACKUP_SET_STRATEGY_TIPS);
				return false;
			} else {
				for(var i=0; i<strategyMode.length; i++){
					if(1 == $(strategyMode[i]).data('mode')){
						data.backupInfo.fullInfo = strategyConfig.fullInfo;
						if(strategyConfig.fullInfo.rollFlag && !checkTime(strategyConfig.fullInfo.startTime,strategyConfig.fullInfo.endTime)) return;
					}else if(2 == $(strategyMode[i]).data('mode')){
						data.backupInfo.incrInfo = strategyConfig.incrInfo;
						if(strategyConfig.incrInfo.rollFlag && !checkTime(strategyConfig.incrInfo.startTime,strategyConfig.incrInfo.endTime)) return;
					}else if(3 == $(strategyMode[i]).data('mode')){
						data.backupInfo.diffInfo = strategyConfig.diffInfo;
						if(strategyConfig.diffInfo.rollFlag && !checkTime(strategyConfig.diffInfo.startTime,strategyConfig.diffInfo.endTime)) return;
					} else if (9 == $(strategyMode[i]).data('mode')) {
						data.backupInfo.pincrInfo = strategyConfig.pIncrInfo;
                        if (strategyConfig.pIncrInfo.rollFlag && !checkTime(strategyConfig.pIncrInfo.startTime,strategyConfig.pIncrInfo.endTime)) {
                            return;
                        }
                    }
				}
				$('.settimetip').hide();
				return true;
			}
		}else if("oncetime" == data.backupInfo.type){
			var onceTime = $('#oncetime').val();
			if("" != onceTime){
				$('.settimetip').hide();
				data.backupInfo.datetime = onceTime;
				return true;
			}else{
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
				return false;
			}
		} else if ('manual' === data.backupInfo.type) {
			return true;
		}
		return false;
	}
	
	var getStrategyDes = function(strategy, typeDes){
		//每天12:12:12开始,不滚动
		//每天12:12:12开始,滚动间隔01:11:11,滚动结束时间23:11:11
		//每周1,2,3,4,5,6,
		var des = typeDes + ": ";
		if(CONF.STRATEGY_TYPE.DAY == strategy.type){
			des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy); 
		}else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
			des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
		}else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
			des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
		}else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
			des += LANG.UI_STRATEGY_GLOBAL;
		}else{
			des += LANG.UI_PUBLIC_NOTHING + "<br><br>";
		}
		return des;
	}
	
	var getEachStrategy = function(strategy){
		var desEach = '';
		desEach += strategy.startTime;
		desEach += LANG.UI_STRATEGY_START + ", ";
		if(strategy.rollFlag){
			desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
		}else{
			desEach += LANG.UI_STRATEGY_ROLL_NO;
		}
		desEach += "<br><br>";
		return desEach;
	}
	
	var getStrategyDays = function(days){
		var desDays = '';
		$.each(days, function(i,d){
			if(1 == d){
				var day = i+1;
				desDays += day + ", ";
			}
		});
		return desDays;
	}
	
	var step3Valid = function(){
		var result = getTimeStr();
		if(!result) return false;
		var result = getReserveStr() & getAchiveStr() & getTransferStr() & getStoreStr() & getSpeedStr() & getSafeStr();
		
		if(result){
			var strategyMode = $('#strategymode').find('input:checked');
			return showStep3(strategyMode);
		}
		return result;
	}
	
	var showStep3 = function(strategyMode){
		$('.backupTypeInfoDiv').show();
		var backuptypeshow = $('.backuptypeshow'), backuptypeinfoshow = $('.backuptypeinfoshow');
		var backuptypeshowStr = '', backuptypeinfoshowStr = '';
		if("strategy" == data.backupInfo.type){
			backuptypeshowStr = LANG.UI_BACKUP_USE_STRATEGY;
			for(var i=0; i<strategyMode.length; i++){
				if(1 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backupInfo.fullInfo.des + "<br>";
				}else if(2 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backupInfo.incrInfo.des;
				}else if(3 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backupInfo.diffInfo.des;
				} else if (9 == $(strategyMode[i]).data('mode')) {
                    backuptypeinfoshowStr += data.backupInfo.pincrInfo.des + "<br>";
                }
			}
		}else if("oncetime" == data.backupInfo.type){
			backuptypeshowStr = LANG.UI_BACKUP_ONCE;
			backuptypeinfoshowStr = LANG.UI_PUBLIC_START_TIME + ": " + data.backupInfo.datetime;
		} else if ('manual' === data.backupInfo.type) {
			backuptypeshowStr = LANG.UI_BACKUP_MANUAL;
			$('.backupTypeInfoDiv').hide();
		}
		backuptypeshow.html(backuptypeshowStr);
		backuptypeinfoshow.html(backuptypeinfoshowStr);
		//传输策略
        let transDes = '';
        transDes += LANG.UI_COPY_BACK_ENCRYPT + ': ' + getSwitchDes(data.highInfo.transfer.encrypt);
		// 传输加密算法
		if($('#transport_encrypt_flag').get(0).checked){
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
            transDes += "<br>" + encryptedMethodLabel + ": " + grade;
		}
		$('.transferinfoshow').html(transDes);
		//节点传输网络信息显示
		if(networkFlag){
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
			$('.applianceshow').html($('.transfernetworklabel').html() + ": " + networkNode.str + '<br>');
		}
		//高级策略-快照、线程数量、通配符
		data.highInfo.newstr.silentsnapshotcheck = $('#silentsnapshotcheck').get(0).checked;//快照
        //归档
		if(Object.keys($('#archivecheck')).length != 0) {
			$('.archiveshow').html($('.archivelabel').html() + ": " + getSwitchDes($('#archivecheck').get(0).checked));
			if($('#archivecheck').get(0).checked) {
				$('.archiveselectshow').show();
				$('.archiveselectshow').html($('.archiveselectdivlabel').html() + ": " + $("#archiveSelect").find("option:selected").text());
			}
		}
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
			//未授权多线程直接传1
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
		//通配符
		var backupmode1Info = $('.silentsnapshotlabel').html() + ": " + getSwitchDes(data.highInfo.newstr.silentsnapshotcheck);
		//存储策略
		data.highInfo.store.compress = $('#compressCheck').get(0).checked;//压缩
		// 压缩等级
        if($('#compressCheck').get(0).checked){
            data.highInfo.store.compress_method = parseInt($('#compressGrade').val());
        }
		data.highInfo.store.dataencrypt = $('#encryptStorageCheck').get(0).checked; //数据加密
		if(data.highInfo.store.dataencrypt) {
			data.highInfo.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;//自动生成密码
		} else {
			data.highInfo.store.password_auto_flag = false;
		}
		data.highInfo.store.password = btoa(getPassword());
		
		//存储策略描述信息
		var storeInfo = "";
		// 压缩存储
		storeInfo += $('.compressLabel').html() + ": " + getSwitchDes(data.highInfo.store.compress);
		// 压缩等级
		if(data.highInfo.store.compress){
			let gradeValue = $('#compressGrade').val();
            let grade = '';
            switch (parseInt(gradeValue,10)) {
                case 1: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                    break;
                case 2: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                    break;
                case 3: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                    break;
                case 4: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                    break;
            };
            storeInfo += "<br>" + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
		}
		storeInfo += "<br>" +$('.encryptStorageLabel').html() + ": " + getSwitchDes(data.highInfo.store.dataencrypt) ;
        // 存储加密
        data.highInfo.store.encrypt_method = parseInt($('#storageEncryptMethod').val());
		// 存储加密
		if($('#encryptStorageCheck').get(0).checked){
			let encryptedMethodLabel = $('.storage-encrypt-label').html();
            let method = $('#storageEncryptMethod').val();
            let grade = '';
            switch (parseInt(method)) {
                case 1: 
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2: 
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            };
            storeInfo += '<br>' + encryptedMethodLabel + ": " + grade + "<br>";
		}
		if(data.highInfo.store.dataencrypt){
			storeInfo += "<br>" + $('.passwordAutoLabel').html() + ": " + getSwitchDes(data.highInfo.store.password_auto_flag);
		}
		//归档
		if(Object.keys($('#archivecheck')).length != 0 && $('#archivecheck').get(0).checked) {
			//选择磁带存储时，不能开启归档
			if(data.highInfo.node.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
				UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_DES, LANG.UI_FILE_ARCHIVE_TIPS_TAPE);
				return false;
			}
			//归档开启，只能选择完备，并且保留策略只能是永久保留
			if(Object.keys(data.backupInfo.diffInfo).length !== 0 || Object.keys(data.backupInfo.incrInfo).length !== 0 || data.highInfo.reserve.type != 3) {
				UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_DES, LANG.UI_FILE_ARCHIVE_TIPS);
				return false;
			}
		}
		$('.storageinfoshow').html(storeInfo);

		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedLimitsStr = '';
		if (data.speedLimit.speedInfo && data.speedLimit.speedInfo.length != 0) {
			speedLimitsStr = '';
			for (let i = 0; i < data.speedLimit.speedInfo.length; i++) {
				speedLimitsStr += data.speedLimit.speedInfo[i].des + '<br>';
			}
		}
		if (speedLimitsStr == '') {
			speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedLimitsStr);
		$('.backupmodeshow1').html(backupmode1Info);
		if(data.highInfo.node.storage_type != CONF.BD_STORAGE_TYPE.TAPE) {
			//---------------------------------保留策略----------------------------------------------------
			var reservetypeStr = '';
            if(CONF.RESERVE_STRATEGY_MODE.POINT == data.highInfo.reserve.strategyMode){
				reservetypeStr = LANG.UI_RESERVE_RETENTION_TYPE + ': '+ LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
			}else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == data.highInfo.reserve.strategyMode){
				reservetypeStr = LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
			}
            if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
                reservetypeStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_NUM + '<br>';
            }else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
                reservetypeStr +=  LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_DAY + '<br>';
            }else if(CONF.RESERVE_TYPE.PERMANENT == data.highInfo.reserve.type) {
                reservetypeStr += LANG.UI_FILE_PERMANENT + '<br>';
            }
			var methoddes = LANG.UI_STRATEGY_VALUE;
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
                methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
            }
			if (SETTINGS.high.file_archive_flag == 2) {
				reservetypeStr += methoddes + ': ' + data.highInfo.reserve.value;
			}
			$('.reservetypeshow').html(reservetypeStr);
//---------------------------------保留策略----------------------------------------------------
			$('.backupmodeshow3').html(backupmode3Info + '<br>');
		} else {
			$('.backupmodeshow3').hide();
		}
		$('.backupmodeshow5').html(backupmode5Info);
		$('.backupmodeshow6').html(backupmode6Info);
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
		//重试策略
		data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		if (!data.retry_strategy) {
			return false;
		}
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

	// 得到存储加密密码
	var getPassword = function(){
		var now_password = $.trim($('#password').val().replace(/\s+/g, ''));
		// 备份策略已配置密码时
		if(_oldPassword != ''){
			// 若密码输入框未触发过change事件,现密码和原配置的密码_oldPassword必然相等,则返回解码后的密码
			if (!passwordChangeFlag) {
				return atob(_oldPassword);
			} else { // 若密码输入框触发过change事件,判断现密码和原配置的密码_oldPassword是否相等,相等则返回解码后的密码;不相等则返回现密码
				if (now_password === _oldPassword) {
					return atob(now_password);
				}
			}
		} else {
			// 未应用备份策略且未触发过密码输入框的change事件,则return之前接口返回的密码
			if (!passwordChangeFlag) {
				return atob(SETTINGS.bss.password);
			} else {
				// 触发过密码输入框的change事件则return密码输入框本身的值
				return now_password;
			}
		}
	}

	//数据加密密码确认检测
	var checkPassword = function(){
		passwordChangeFlag = true;
		// var password = $('#password').val();
		// var repassword = $('#repassword').val();
		// if(password != repassword){
		// 	$('.passwordTips').show();
		// }else{
		// 	$('.passwordTips').hide();
		// }
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
	
	//得到保留策略
	var getReserveStr = function(){
		data.highInfo.reserve.type = $('#reserveType').val();
		data.highInfo.reserve.strategyMode = $('#reserveMode').val();
		if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerDayInput').val();
		}else if(CONF.RESERVE_TYPE.PERMANENT == data.highInfo.reserve.type) {
			data.highInfo.reserve.value = "";
		}
		if($('#reserveType').val() !== "3") { //不是永久保留
			if(data.highInfo.reserve.value > 0){
				$('.setreservtip').hide();
				return true;
			}else{
				UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
				return false;
			}
		}else {
			return true;
		}
	}
	//得到归档策略
	var getAchiveStr = function(){return true;}
	
	//得到限速策略
	var getSpeedStr = function(){
		data.speedLimit = getSpeedStrategyInfo();
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

	//得到传输策略
	var getTransferStr = function(){
		data.highInfo.transfer.encrypt = $('#transport_encrypt_flag').get(0).checked;
		// 传输加密算法
		data.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
		data.highInfo.transfer.network  = '';
        data.highInfo.transfer.network_pool_uuid = '';
        if (networkFlag) {
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
            if (false === networkNode) {
                return false;
            }
            if (networkNode.eventtype === 'network') {
                data.highInfo.transfer.network = networkNode.network_uuid;
            } else {
                data.highInfo.transfer.network_pool_uuid = networkNode.network_pool_uuid;
            }
        }
		return true;
	}
	//得到存储策略
	var getStoreStr = function(){
		var now_password = $.trim($('#password').val());
		var repassword = $.trim($('#repassword').val());
		data.highInfo.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;//自动生成密码
		if (!isNotLatinCode(now_password)) {
			return false;
		}
		data.highInfo.store.dataencrypt = $('#encryptStorageCheck').get(0).checked; //数据加密
		data.highInfo.store.password = btoa(getPassword());

		// 开启数据加密
		if (data.highInfo.store.dataencrypt) {
			// 开启自动生成密码
			if (data.highInfo.store.password_auto_flag) {
				data.highInfo.store.password = "";
			} else {
				var tmp = $.trim($('#password').val());
				// 未应用备份策略配置的密码
				if (!_oldPassword) {
					// 是否触发过密码输入框的change事件且密码最终值还是空,则提示 请输入数据加密的密码;没有触发过则无需校验,直接向后端提交此前接口返回的密码
					if (passwordChangeFlag && !data.highInfo.store.password) {
						UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
						return false;
					}
					// 触发过密码输入框的change事件,但和确认密码不一致时
					if (passwordChangeFlag && tmp !== repassword) {
						UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
						return false;
					}
					if(!data.highInfo.store.password){
						UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
						return false;
					}
				} else {// 应用备份策略配置的密码
					// 触发过密码框的change事件,且密码为空时
					if (passwordChangeFlag && !tmp) {
						UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
						return false;
					}
					// 没有触发过密码输入框的change事件,则是备份策略配置过的原密码和原确认密码;触发过,就要比对改变后的密码和确认密码是否一致
					if (passwordChangeFlag && tmp !== repassword) {
						UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
						return false;
					}
				}
			}
		}

		return true;
	}

	//判断字符传知否在Latin1字符集中
	function isNotLatinCode(string) {
		var latin1Regex = /[^\x00-\xFF]/;
		if(latin1Regex.test(string)){
			UIToastr.showWarning(LANG.UI_DB_BACKUP_PASSWORD_ILLEAGLE, LANG.UI_DB_BACKUP_PASSWORD_TIPS);
			return false;
		}
		return true;
	}
	
	//得到备份节点信息
	var getNodeStr = function(){
		//TODO
		data.highInfo.node.nodecheck = true;
		data.highInfo.node.nodeuuid = '';
		data.highInfo.node.storagecheck = true;
		data.highInfo.node.storageuuid = '';
		data.highInfo.node.nodecheck = false;
		data.highInfo.node.nodeuuid = $('#selectnode').val();
		if(!data.highInfo.node.nodeuuid){
			UIToastr.showWarning(LANG.UI_BACKUP_SELECT_NODE, LANG.UI_BACKUP_SELECT_NODE_TIPS);
			return false;
		}
		data.highInfo.node.storagecheck = false;
		data.highInfo.node.storageuuid = $('#selectstorage').val();
		if(!data.highInfo.node.storageuuid){
			UIToastr.showWarning(LANG.UI_BACKUP_SELECT_STORAGE, LANG.UI_BACKUP_SELECT_STORAGE_TIPS);
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
//            $('.step-title', $('#filebackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#filebackupcontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#filebackupcontent').find('.button-previous').css('visibility', 'hidden');
                $('#filebackupcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#filebackupcontent').find('.button-previous').css('visibility', 'visible');
                $('#filebackupcontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#filebackupcontent').find('.button-next').hide();
				$('#filebackupcontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#filebackupcontent').find('.button-next').show();
				$('#filebackupcontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#filebackupcontent').bootstrapWizard({
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
                $('#filebackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#filebackupcontent').find('.button-previous').css('visibility', 'hidden');
        $('#filebackupcontent .button-submit').click(() => {
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
		//检测保留个数是否为0
		if(data.highInfo.reserve.value <= 0 && data.highInfo.reserve.value != ""){
			UIToastr.showWarning(LANG.UI_BACKUP_RESERVE_TIPS);
			return false;
		}
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
		//提交的时候重新调用下这个方法
		data.speedLimit = getSpeedStrategyInfo();
		//TODO提交
    	var jsonData = JSON.stringify(data);
    	Metronic.blockUI({target: '#filebackupcontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'editFsBackupJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#filebackupcontent');
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}

	var setTree = function(zNodes){
		if(zNodes == "[]"){
			$("#noagent").show();
			$("#agent_tree").hide();
			if(searchFlag) {
				$('#nosearchtips').show();
				$("#noagent").hide();
			}
		}else{
			$("#noagent").hide();
			$("#agent_tree").show();
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
		zTree = $.fn.zTree.init($("#agent_tree"), setting, zNodes);
		
		//初始化时间策略模块
		Strategy.initModule(FileBackup);
    	//初始化原始数据
    	if(editFlag) {
			initOldSettings();
		}
		// setTimeout(function(){
			for(var i=0;i<zNodes.length;i++){
				if(zNodes[i].eventtype == "agent" && zNodes[i].checked){
					nodeExpand("agent_tree_" + (i+1),zNodes[i]);
				}
			}
		// },500);
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
		nodeExpand(treeId, treeNode);
	}
	//点击代理端节点
	var nodeClick = function(treeId, treeNode){
		if(treeNode.eventtype == "group") {
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);//单击展开节点
			return;
		}
		//未被禁用，单击选中或取消选中
		if(!treeNode.chkDisabled && treeNode.eventtype == "agent") {
			$.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true);
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
		//批量取消选中离线客户端
		if (!treeNode.checked && treeNode.type == 1 && treeNode.children) {
			for (var i = 0; i < treeNode.children.length; i++) {
				if (!treeNode.children[i].chkDisabled) {//避免将未禁用的客户端设置为禁用状态
					continue;
				}
				treeNode.children[i].chkDisabled = false;
				zTree.checkNode(treeNode.children[i], false, true);
				treeNode.children[i].chkDisabled = true;
				zTree.updateNode(treeNode.children[i]);
			}
		}
		checkAgentOnlineTips(treeNode);
		var flag = treeNode.checked;
		if(flag){
			if(treeNode.eventtype == "agent"){
				//选中代理节点
				addAgentList(treeNode);
				initFileTree(treeId, treeNode,treeNode.uuid,treeNode.pId,false,'');
			}else if(treeNode.eventtype == "group"){
				//选中分组节点
				var children = treeNode.children;
				if(children.length !=0){
					children.forEach((item,index) => {
						if(item.checked) {
							addAgentList(item);
							initFileTree(treeId, item,item.uuid,treeNode.id,false,'');
						}
					});
				}
			}
		}else {
			// 取消勾选
			if(treeNode.chkDisabled)return;
			editFlag = false;
			if(zTree.getCheckedNodes(true).length != 0) {
					var groupuuid = "";
					var allpid = [];
					//删除取消选中的agentuuid
					if(treeNode.eventtype == "group" &&treeNode.agentlist && treeNode.children) {
						treeNode.children.forEach(item=> {
							//删除取消选中的树对象
							delete zTreeFile[item.uuid];
							$('#allFileTree').find('#'+ item.id + '.add-list').remove();
							if(data.srcInfo.agentList.indexOf(item.uuid) != -1) {
								data.srcInfo.agentList.splice(data.srcInfo.agentList.indexOf(item.uuid),1);
							}
						});
					}else {
						data.srcInfo.agentList.splice(data.srcInfo.agentList.indexOf(treeNode.uuid),1);
						//删除取消选中的树对象
						delete zTreeFile[treeNode.uuid];
						$('#allFileTree').find('#'+ treeNode.id + '.add-list').remove();
					}
					//删除取消选中的groupuuid
					treeNode.eventtype=="agent"?groupuuid=treeNode.pId:groupuuid=treeNode.id;
					zTree.getCheckedNodes(true).forEach(item => {
						if(item.eventtype=="agent") {
							allpid.push(item.pId);
						}
					});
					if(allpid.indexOf(groupuuid)==-1 && data.srcInfo.groupList.indexOf(groupuuid) != -1) {//该分组下没有其他客户端
						data.srcInfo.groupList.splice(data.srcInfo.groupList.indexOf(groupuuid),1);
					}
			}else {
				//一个都未选中
				data.srcInfo.agentList = [];//清空agentList
				data.srcInfo.groupList = [];//清空groupuuid
				zTreeFile = [];//清空树对象
				$('#allFileTree').html('');
				$('#agentfilediv').hide();
				$('#step1tips').show();
			}
		}
		
	}
	//检查代理是否离线或者未授权
	var checkAgentOnlineTips = function (treeNode) {
		if(treeNode.chkDisabled){
			UIToastr.showWarning(LANG.UI_FILE_CLIENT_OFFLINE_OR_NOAUTHORIZED, LANG.UI_FILE_CLIENT_OFFLINE_OR_NOAUTHORIZED_TIPS);
			return;
		}else {
			return true;
		}
	  }

	
	// 将选中的单个客户端加入到右边
	var addAgentList = function (treeNode) {
		var divChildren = $('#allFileTree').children();
		for(var i = 0; i < divChildren.length; i++) {
			if(divChildren[i].id == treeNode.id) {
				$('#allFileTree').find('#'+treeNode.id+'.add-list' ).remove();//移除重复的
			}
		}
	
		var agentContent = "";
		treeNode.path = '';
		agentContent += 
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
						'<div id="fileClientInfo_' + treeNode.id+'" class="panel-collapse collapse in">' + 
							'<div class="panel-body">' + 
								'<div class="pabel-body-btns"><input class="btn green-turquoise apply-to-all-client" type="button" value="'+ LANG.UI_FILE_SELECT_APPLY_OTHER_CLIENT +'"></div>' + 
								'<div class="nav-tabs-wrapper">' + 
									'<ul class="nav nav-tabs nav-line-tabs">' + 
										'<li id="commontab'+ treeNode.uuid +'" class="active nav-item">' + 
											'<a class="nav-link" href="#common_tabagent_tree_'+ treeNode.uuid +'"data-toggle="tab"aria-expanded="false">'+ LANG.UI_FILE_SELECT_FILE_AND_DIR +'</a>' + 
										'</li>' + 
										'<li class="nav-item">' + 
											'<a class="nav-link" href="#high_tabagent_tree_'+ treeNode.uuid +'"data-toggle="tab"aria-expanded="false">'+ LANG.UI_PUBLIC_MORE +'</a>' + 
										'</li>' + 
									'</ul>' + 
									'<div class="tab-content hover-scroll-y">' + 
										'<div class="tab-pane active"id="common_tabagent_tree_'+ treeNode.uuid +'">' + 
											'<div class="row" style="margin: 0">' + 
												'<div class="form-group" style="margin: 0">' + 
													'<ul id="fileClientTree_'+ treeNode.uuid +'"class="ztree"></ul>' + 
												'</div>' + 
											'</div>' + 
										'</div>' + 
										'<div class="tab-pane"id="high_tabagent_tree_'+ treeNode.uuid +'">' + 
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
															'<a class="popovers"data-container="body"data-trigger="hover"data-placement="right"data-html="true"data-content="'+ LANG.UI_FILE_WILDCARD_BAK_MODE_TIPS +'"style="line-height: 25px;"data-original-title=""title="">' +
																'<i class="viconfont vicon-tishi"></i>' + 
															'</a>' + 
														'</div>' + 
													'</div>' + 
													'<div class="form-group wildcarddiv display-none"style="margin-bottom: 0;">' + 
														'<label class="control-label col-md-3 wildcardlabel">'+LANG.UI_FILE_WILDCARD+'</label>' + 
														'<div class="col-md-5 allWildcardInput pr0">' + 
															'<input type="text"class="wildcardInputdiv form-control" class="form-control input-sm wildcardInput">' + 
															'<button type="button"class="btn btn-primary addInput" style="height: 33px" agent_uuid = "'+ treeNode.uuid +'">'+ LANG.UI_BACKUP_FILE_ADD +'</button>' +
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
															'<div class="wilcardsList_'+ treeNode.uuid +'">' + 
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
		$('#allFileTree').append(agentContent);
		// if(divChildren.length == 0) {
		// }
		$('.popovers').popover({
			html:true
		});
		// 通配符旧信息
		// SETTINGS.agentList.forEach((agentuuid,i)=> {
			SETTINGS.high.wildcardinfo.forEach((item,j)=> {
				if(treeNode.uuid == item[0]) {
					var des = ''
					$('#high_tabagent_tree_'+item[0]).find('.wildcardmode').val(item.wildcard_mode);//方式
					if(item.wildcard_mode != 0) {//选择了通配符过滤方式
						$('.wilcardsList_' + item[0]).html('');
						$('#high_tabagent_tree_' + item[0]).find('.wildcarddiv').show();
						item.wildcard.forEach(eachwildcard=> {//添加旧通配符规则
							let html = '<div class="delcontent" title="' + eachwildcard + '">' + 
								'<span class="wildcardInput">' + eachwildcard + '</span>' +
								'<span class="delInput">×</span>' +
							'</div>';
							$('.wilcardsList_' + item[0]).append(html);
						});
					}
				}
			});
			
		// });
	  }
	  var debounceFs = function () {
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
			if (v.type == 2) {
				checkFsNode.push(v);
			}
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
	var initFileTree = function(treeId,treeNode,agentList,groupuuid,applyFlag,applyPathList){
		if(treeNode.chkDisabled) return;
		var _path = treeNode.path;
		if(applyFlag) {
			var params = {agentuuid:treeNode.uuid, start:0, limit:_pageSize, filename:'', dir:_path,pid:0,groupuuid:groupuuid,
			editFlag: true, taskuuid: $('#task_uuid').val(),applyPathList:applyPathList};
		}else {
			var params = {agentuuid:treeNode.uuid, start:0, limit:_pageSize, filename:'', dir:_path,pid:0,groupuuid:groupuuid, editFlag: true, taskuuid: $('#task_uuid').val()};
		}
		var params = JSON.stringify(params);
		var div = "#" + groupuuid + '_' + treeNode.uuid;
		nextFlag = false;
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.FILE,f:'getFileDirTree',p:params},
	        success: function(data){
	        	Metronic.unblockUI(div);
	        	result = JSON.parse(data);
	        	if(result.re){
	        		setFileTree(result)
					nextFlag = true;
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
		if(data.srcInfo.groupList.indexOf(groupuuid) ==-1) {
			data.srcInfo.groupList.push(groupuuid);
		}
		if(data.srcInfo.agentList.indexOf(agentList) == -1) {
			data.srcInfo.agentList.push(agentList);
		}
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
				onCheck: fileNodeCheck,
				beforeExpand: fileNodeExpand
			},
			view: {
				dblClickExpand: false
			}
		};
		var agent_uuid =  (data['fileNodes']==undefined? "":data['fileNodes'][0].uuid);
		zTreeFile[agent_uuid] = $.fn.zTree.init($('#fileClientTree_' + agent_uuid), setting, data['fileNodes']);
		$('#fileClientTree li').css("background-color","white");
		//检测是不是有子节点  没有不展开
		var allnodes = zTreeFile[agent_uuid].transformToArray(zTreeFile[agent_uuid].getNodes());
		allnodes.forEach(item => {
			if(item.children == undefined && item.open == true) {
				item.open = false;
				zTreeFile[agent_uuid].updateNode(item);
			}
		});
	}
	var fileNodeCheck = function (event, treeId, treeNode) {
		rechoose = true;//进行了重新选择
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
		getFileSonTree(treeId,pNode,false);
	}

	
	// 获取文件子树
	var getFileSonTree = function (treeId,pNode,expendFlag) {
		 _path = pNode.filepath;
		pNode.pid == null ? pNode.pid = 0 : pNode.pid;
		var params = {agentuuid:pNode.uuid, start:0, limit:_pageSize, filename:'', dir:_path,pid:pNode.pid,groupuuid:pNode.groupuuid,code_type:pNode.code_type};
		var params = JSON.stringify(params);
		var div = "#" + pNode.groupuuid + "_" + pNode.uuid;
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.FILE,f:'getFileDirSonTree',p:params},
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
		var checkeFlag = pNode.getParentNode().check_Child_State==2 ? true : false;
		pNode.pid == undefined ? pNode.pid = 0 : pNode.pid;
		pNode.next_index == undefined ? pNode.next_index = 0 : pNode.next_index;
		pNode.search_file_name == undefined ? pNode.search_file_name = "" : pNode.search_file_name;
		pNode.dir_path == undefined ? _path = pNode.filepath : _path = pNode.dir_path;
		var params = {agentuuid:pNode.uuid, start:pNode.next_index, limit:_pageSize, filename:pNode.search_file_name, dir:_path,pid:pNode.pid,groupuuid:pNode.groupuuid};
		var params = JSON.stringify(params);
		var div = "#" + pNode.groupuuid + "_" + pNode.uuid;
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
		    url: CONF.AJAXPATH, 
		    async:true, 
		    data:{m:CONF.M.FILE,f:'getFileDirSonTree',p:params},
		    success: function(data){ 
		    	Metronic.unblockUI(div);
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
		//通配符信息
		var wildcardarr = [];
		var wildcardmode = $("#high_tabagent_tree_" + str).find(".wildcardmode").val();
		var inputdiv = $("#high_tabagent_tree_" + str).find(".wildcardInput");
		for(var i = 0; i < inputdiv.length; i++) {
			wildcardarr.push(inputdiv[i].innerHTML);
		}
		agentNodes.forEach(item => {
			//循环分组中选中的子节点，不包括当前应用的文件树
			if(!item.isParent && item.uuid != str) {
				//得到当前树选中的节点
				var fileNodes = zTreeFile[str].getCheckedNodes(true);
				fileNodes = filterFile(fileNodes);
				if(fileNodes.length == 0) {
					UIToastr.showWarning(LANG.UI_FILE_SELECT_PLEASE, LANG.UI_FILE_NO_FILE_BE_SELECTED);
					return;
				}
				initFileTree(item.tId,item,item.uuid,item.pId,true,fileNodes);
				//应用通配符到其他客户端
				applyWildcard(wildcardmode,wildcardarr,item.uuid);
			}
		});
	}
	var filterFile = function (allfileNodes) {
		var allCheckedNode = [];
		//得到所有勾选状态是全选中的节点
		for(var i=0; i<allfileNodes.length; i++){
			//check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
			if(allfileNodes[i].check_Child_State == 2 || allfileNodes[i].check_Child_State == -1) {
				allCheckedNode.push(allfileNodes[i]);
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
		//获取pathname和pathtype
		var arr = [];
		allCheckedNode.forEach(item => {
			arr.push({
				"path_name": item.filepath,
				"path_type": item.type
			});
		});
		return arr;
	}
	var applyWildcard = function(wildcardmode,wildcardarr,agentuuid) {
		$('.wilcardsList_' + agentuuid).html('');
		$("#high_tabagent_tree_" + agentuuid).find(".wildcardmode").val(wildcardmode);
		if(wildcardmode != 0) {
			$(".wildcarddiv").show();
		}else {
			return;
		}
		wildcardarr.forEach(item => {
			let html = '<div class="delcontent" title="' + item + '">' + 
				'<span class="wildcardInput">' + item + '</span>' +
				'<span class="delInput">×</span>' +
			'</div>';
			$('.wilcardsList_' + agentuuid).append(html);
		});
		
	}
	
	var initTree = function(type) {
		
		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'getAgentGroupBackupTree',p:{}}, setTree);
	};
	
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
		$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'getBackupTaskAllInfo',p:data}, function(d){
			SETTINGS = JSON.parse(d);
			initData();
			initStrategySelect(); //初始化策略选择
			initStep1Settings();
			initStep2Settings();
			initStep3Settings();
			initListener();
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
			data.taskuuid = $('#task_uuid').val();
			data.agentlist = SETTINGS.agentList//客户端列表
			data.grouplist = SETTINGS.groupList//分组列表
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'getBackupTreeOldInfo',p:data}, function(d){
				setTree(d);
			});
		}
		editFlag = false;
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
	
	//初始化第二步任务信息
	var initStep2Settings = function(){
		var timeStrategy = SETTINGS.timestrategy;
		//设置时间策略类型
		$('#backuptype').val(timeStrategy.type);
		var sdata = timeStrategy.data;
		if('strategy' == timeStrategy.type){
			//按策略备份,设置时间策略
			var strategy = [];
			strategy[0] = {
				mode: 1,
				strategy_type: 2,
				days: [0, 0, 0, 0, 1, 0, 0],
				frequency: '',
				start_time: '23:00:00',
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: '23:59:59'
			};
			strategy[1] = {
				mode: 2,
				strategy_type: 1,
				days: [],
				frequency: '',
				start_time: '23:00:00',
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: '23:59:59'	
			};
			strategy[2] = {
				mode: 3,
				strategy_type: 1,
				days: [],
				frequency: '',
				start_time: '23:00:00',
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: '23:59:59'	
			};
			var display = ['display-none', 'display-none', 'display-none'];
			for(var i=0; i<sdata.length; i++){
				if(!sdata[i].roll_flag) {
					sdata[i].roll_interval = '01:00:00';
				}
				if("1" == sdata[i].mode){
					//完全备份
					strategy[0] = sdata[i];
					display[0] = '';
					$('#fullBackup').iCheck('check');
					// $('#stragegyaccordion').find('.strategy-panel[data-mode=' + sdata[i].mode + ']').show()
				}else if("2" == sdata[i].mode){
					//增量备份
					strategy[1] = sdata[i];
					display[1] = '';
					$('#incrBackup').iCheck('check');
					// $('#stragegyaccordion').find('.strategy-panel[data-mode=' + sdata[i].mode + ']').show()
				}else if("3" == sdata[i].mode){
					//差异备份
					strategy[2] = sdata[i];
					display[2] = '';
					$('#diffBackup').iCheck('check');
					// $('#stragegyaccordion').find('.strategy-panel[data-mode=' + sdata[i].mode + ']').show()
				}
			}
			$('#backupTimestrategy').strategy({dom:$('#backupTimestrategy'), config: strategy, display:display});
			for(var i=0; i<sdata.length; i++){
				if("1" == sdata[i].mode){
					$('#stragegyaccordion').find('.strategy-panel[data-mode=' + sdata[i].mode + ']').show()
				}else if("2" == sdata[i].mode){
					$('#stragegyaccordion').find('.strategy-panel[data-mode=' + sdata[i].mode + ']').show()
				}else if("3" == sdata[i].mode){
					$('#stragegyaccordion').find('.strategy-panel[data-mode=' + sdata[i].mode + ']').show()
				}
			}
		}else if('oncetime' == timeStrategy.type){
			//一次性备份,设置时间
			$('.setStrategy').hide();
			$('.setOnceTime').show();
			$('#oncetime').val(sdata);
			data.backupInfo.type = 'oncetime';
			$('#spinnerNum').spinner('disable');
			$('#spinnerDay').spinner('disable');
		}
	}
	
	//初始化时间策略
	var initStrategy = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB, f:"getTimeCrowdList", p:{}}, function(d){
			var jsonData = JSON.parse(d);
			if(jsonData.timeList.length != 0){
				$('#backupCrowd').taskCrowd({timeList:jsonData.timeList, showFlag: jsonData.showFlag});
			}
			var suggestInfo = jsonData.suggestTime;
			defaultStrategy[0] = {
				mode: 1,
				strategy_type: 2,
				days: [0, 0, 0, 0, 1, 0, 0],
				frequency: '',
				start_time: suggestInfo.start_time,
				end_time: suggestInfo.end_time,
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: suggestInfo.roll_end_time
			};
			defaultStrategy[1] = {
				mode: 2,
				strategy_type: 1,
				days: [],
				frequency: '',
				start_time: suggestInfo.start_time,
				end_time: suggestInfo.end_time,
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: suggestInfo.roll_end_time
			};
			defaultStrategy[2] = {
				mode: 3,
				strategy_type: 1,
				days: [],
				frequency: '',
				start_time: suggestInfo.start_time,
				end_time: suggestInfo.end_time,
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: suggestInfo.roll_end_time
			};
			defaultStrategy[3] = {
				mode: 9,
				strategy_type: 1,
				days: [],
				frequency: '',
				start_time: suggestInfo.start_time,
				end_time: suggestInfo.end_time,
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: suggestInfo.roll_end_time
			};
			//延迟设置,因为这里icheck会默认修改里面的选中事件
			$('#backupTimestrategy').strategy({dom: $('#backupTimestrategy'), config: defaultStrategy, display:['display-none', 'display-none', 'display-none', 'display-none'], backup_flag: 1});
		});
	}
	//初始化第三步任务信息
	var initStep3Settings = function(){
		//重试策略
        $('#retry_config').retryStrategy({'retry_strategy': SETTINGS.high.retry_strategy},'edit');
		//默认节点选中因为要再节点初始化以后,所以选择事件初始化,移动到第一次点击下一步的位置设置
		//传输策略bts
		$('#transfercheck').bootstrapSwitch('state', SETTINGS.bts.encrypt); 
		$('#reconnect_time').val(SETTINGS.bts.reconnect_times); //重连次数
		$('#reconnect_interval').val(SETTINGS.bts.reconnect_interval); //重连时间间隔
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
		if (SETTINGS.bss.encrypt) {
			$('#storageEncryptMethod').val(SETTINGS.bss.encrypt_method);
		}
		$('#passwordAutocheck').bootstrapSwitch('state', SETTINGS.bss.password_auto_flag);
		if(SETTINGS.bss.encrypt) {
			if (!SETTINGS.bss.password_auto_flag) {
				$('.passwordModeDiv').show();
				$('.passwordDiv').show();
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
		$('#transport_encrypt_flag').bootstrapSwitch('state', SETTINGS.bts.encrypt);
		// 传输加密算法
		if(SETTINGS.bts.encrypt){
			$('.transfer-encrypt-method-form').show();
		}
		if(SETTINGS.bts.encrypt_method){
			$('#transferEncryptMethod').val(SETTINGS.bts.encrypt_method);
		}
		if(SETTINGS.high.scan_thread_num == 1) {
			$(".scanFileDiv").show();
			$('#scanFileNum').val(SETTINGS.high.scan_file_num);
		}
		//归档
		if(SETTINGS.high.file_archive_flag != 2) {//开启
			if(Object.keys($('#archivecheck')).length != 0) {
				$('#archivecheck').bootstrapSwitch('state', true);
			}
			$('#reserveType').append('<option value="3" selected>' + LANG.UI_FILE_PERMANENT + '</option>');
			$('#reserveType').prop("disabled","disabled");
			$('.reserveDay').hide();
			$('.reserveNum').hide();
			$('.reservetip1').hide();
			$('.reservetip2').show();
			$('#incrBackup').iCheck('disable');
			$('#diffBackup').iCheck('disable');
			$(".archiveselectdiv").show();
			$("#archiveSelect").val(SETTINGS.high.file_archive_flag);
			$("#archiveSelect").attr("disabled","disabled");
		} else {//关闭
			if(Object.keys($('#archivecheck')).length != 0)  {
				$('#archivecheck').bootstrapSwitch('state', false);
			}
		}
		//保留策略brs
		$('#reserveType').val(SETTINGS.brs.strategy_mode);
		$('#reserveType').val(SETTINGS.brs.type);
		if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
			$('.reserveNum').show();
			$('.reserveDay').hide();
			$('#spinnerNumInput').val(SETTINGS.brs.number);
		}else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
			$('.reserveNum').hide();
			$('.reserveDay').show();
			$('#spinnerDayInput').val(SETTINGS.brs.number);
		}else if (CONF.RESERVE_TYPE.PERMANENT == data.highInfo.reserve.type) {
			$('.reserveNum').hide();
			$('.reserveDay').hide();
			$('#spinnerDayInput').val(3);
			$('#reserveType').append('<option value="3" selected>' + LANG.UI_FILE_PERMANENT + '</option>');
			$('#reserveType').prop("disabled","disabled");
		}
		//文件权限备份
		$('#file-permission').bootstrapSwitch('state', SETTINGS.high.permission_operate_flag);
		//跳过文件告警
		$('#passfilealarmcheck').bootstrapSwitch('state', SETTINGS.high.skip_file_alarm_flag);
		if(SETTINGS.high.skip_file_alarm_flag) {//开启
			passAlarmChange();
			$('#passFileNum').val(SETTINGS.high.skip_file_alarm_min_num);
			$('#warningpercent').val(SETTINGS.high.skip_file_alarm_min_ratio);
		}
		if(Object.keys($('#archivecheck')).length != 0) {
			$('#archivecheck').bootstrapSwitch("disabled",true);//归档不允许修改
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
		for(var i=0;i<speedList.length;i++){
			addSpeedList(speedList[i]);
		}
		initSpeedStrategyDes();
		//安全策略
        let complate_info = [];
        complate_info.push(SETTINGS.high.safe_strategy.integrity_check_config.check_strategy);
        complate_info.push(SETTINGS.high.safe_strategy.integrity_check_config.full_error_policy);
        complate_info.push(SETTINGS.high.safe_strategy.integrity_check_config.inc_error_policy);
        // 初始化安全策略
		$('#completeConfig').completeDetectionBackup('col-md-3', SETTINGS.high.safe_strategy.integrity_check_flag,CONF.MODULE_TYPE.FS, false, complate_info[0], complate_info[1], complate_info[2]);
		//过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').bootstrapSwitch('state', SETTINGS.high.ignore_resource_limiting_flag);
		specialHandler();//修改任务赋值完成之后调用一下
	}
	
	var addSpeedList = function(list){
        var des = "";
        var uuid = list.uuid;
		des += 
		'<li class="list-group-item popovers speedTips list-group-item__speed" id="speed'+ uuid +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ list.des +'">' + 
			'<div class="col1">' + 
				'<div class="cont">' + 
					'<div class="cont-col1"></div>' + 
					'<div class="cont-col2">' + 
						'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + list.des + '</div>' + 
					'</div>' + 
				'</div>' + 
			'</div>' + 
			'<div class="col2  pull-right delete-list">' + 
				'<a class="del'+ uuid +'" >' + 
					'<div class="label label-sm label-danger" style="padding:0;">' + 
						'<i class="viconfont vicon-cuowu"></i>' + 
					'</div>' + 
				'</a>' + 
			'</div>' + 
		'</li>';
        $('#speedList').append(des);
        $('.del'+ uuid).on('click', function(){
            $('.popover.in').remove();
            $('#speed' + uuid).remove();
            for(var j=0;j<speedList.length; j++){
                if(uuid == speedList[j].uuid){
                    speedList.splice($.inArray(speedList[j],speedList),1);
                }
			}
			initSpeedStrategyDes();
        });
    }

	// 应用全局策略并初始化策略信息
	var strategyHandler = function(){
		 newEditFlag = false;
		var index = $('#strategySelect option:selected').val();
		var strategy = globalStrategy[index];
		$('#tab_common').backupStrategy(CONF.MODULE_TYPE.FS, true, strategy, 0,function () {
			specialHandler();
		});
		if(index != 0) {
			_oldPassword = strategy?.store?.storeInfo?.password ?? '';;
			var speedInfo = strategy.speedlimit.speedInfo;
			var check = strategy.speedlimit.check;
			if(!check || !speedInfo) return ;
			speedList = [];
			for(var i=0;i<speedInfo.length;i++){
				speedList.push(speedInfo[i]);
			}
		}
	}

	//归档的特殊处理
	var specialHandler = function () {
		let type = $('#backuptype').val();
		if('strategy' == type){
			//按策略备份
			$('.setStrategy').show();
			$('.setOnceTime').hide();
            $('#reserveType').removeAttr("disabled");
			$('#spinnerNum').spinner('enable');
			$('#spinnerDay').spinner('enable');
		}else if('oncetime' == type){
			//一次性备份
			$('.setStrategy').hide();
			$('.setOnceTime').show();
            $('#reserveType').prop("disabled","disabled");
			$('#spinnerNum').spinner('disable');
			$('#spinnerDay').spinner('disable');
			$('#spinnerNum').spinner('value', 1);
			$('#spinnerDay').spinner('value', 1);
		}else if('manual' == type){
            $('.setStrategy').hide();
			$('.setOnceTime').hide();
            $('#reserveType').removeAttr("disabled");
			$('#spinnerNum').spinner('enable');
			$('#spinnerDay').spinner('enable');
        }
		data.backupInfo.type = type;
		initReserveStrategyDes();
		if(Object.keys($('#archivecheck')).length != 0 && $('#archivecheck').get(0).checked){
			// 默认保留策略为永久保留
			// 先移除所有value等于3的option
			$('#reserveType option[value="3"]').remove();
			$('#reserveType').append('<option value="3" selected>' + LANG.UI_FILE_PERMANENT + '</option>');
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
			$('#pincrBackup').iCheck('uncheck');
			$('#pincrBackup').iCheck('disable');
			$('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
			$('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
			$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
			initReserveStrategyDes();
		} else {
			$('#reserveType').find('option[value="3"]').remove();
		}
	}


	//初始化策略选择列表
	var initStrategySelect = function(){
		if(initStrategyFlag) return; //加载一次
		function initStrategyList(res){
			if(!res.success) return;
			if(res.data.length > 0){
				// 清空策略列表，再插入新的策略列表
				var data = res.data;
				var strategyselect = $('#strategySelect');
				strategyselect.empty();
				for(var i=0; i<data.length; i++){
					var option = '<option  value="' + data[i].uuid + '">' + data[i].text + '</option>';
					globalStrategy[data[i].uuid] = data[i].strategy;
					strategyselect.append(option);
				}
				// 是否初始化策略
				if(SETTINGS.strategyuuid){
					// 默认选中选中所使用策略组
					strategyselect.val(SETTINGS.strategyuuid);
				}
				if(!initStrategyFlag){
					//初始化前先清除一遍
					$('.searchable-select').remove();
					$('#strategySelect').searchableSelect();
					$('.searchable-select-item').on('click', strategyHandler);
					initStrategyFlag = true;
				}
			}

		}
		
        pAjaxRequest({type: CONF.MODULE_TYPE.FS}, '/api/v1/strategies/select', "GET", initStrategyList, true);
	}
	var initDataChangeListeners = function(){
        initTimeListeners();
        initSpeedListeners();
        initStoreListeners();
        initReserveListeners();
        initHighListeners();
    }

    var initTimeListeners = function(){
        $('#fullBackup').on('ifChecked ifUnchecked ', function(){
            initTimeStrategyDes();
        });
        $('#incrBackup').on('ifChecked ifUnchecked ', function(){
            initTimeStrategyDes();
        });
        $('#diffBackup').on('ifChecked ifUnchecked ', function(){
            initTimeStrategyDes();
		});
		
		$('#oncetime').on('change', function(){
            initTimeStrategyDes();
        });
    }
	var initSpeedListeners = function(){
        //添加限速策略确定
        $('#speed_submit').on('click', speedSubmit);
	}
	var initHighListeners = function(){
        $('#backupThreadNum').on('input propertychange', function(){
            initHighStrategyDes();
        });
    }
	

	//初始化策略描述
	var initStrateyDes = function(){
		initTimeStrategyDes();
		initSpeedStrategyDes();
		initReserveStrategyDes();
		initStoreStrategyDes();
		initHighStrategyDes();//初始化高级策略
   }
	
   //初始化时间策略配置信息
	var initTimeStrategyDes = function(){
		var des = '';
		//备份
        var backupType = $('#backuptype').val();
        var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
        var strategyMode = $('#strategymode').find('input:checked');
        if('strategy' == backupType){
            for(var i=0; i<strategyMode.length; i++){
                if(1 == $(strategyMode[i]).data('mode')){
                    des += strategyConfig.fullInfo.des + ". ";
                }else if(2 == $(strategyMode[i]).data('mode')){
                    des += strategyConfig.incrInfo.des + ". ";
                }else if(3 == $(strategyMode[i]).data('mode')){
                    des += strategyConfig.diffInfo.des + ". ";
                }else if(4 == $(strategyMode[i]).data('mode')){
                    des += strategyConfig.logInfo.des + ". ";
                }
            }
        }else if('oncetime' == backupType){
            des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
		}
        $('.backupTimeDes').html(des);
        $('.backupTimeDes').attr('title', des);
	}

	//初始化保留策略配置信息
	var initReserveStrategyDes = function(){
        var des = "";
        var reserveModeType = parseInt($('#reserveMode').val());
        var type = $('#reserveType').val();
        var value = 0;
        if (reserveModeType === CONF.RESERVE_STRATEGY_MODE.POINT){ // 按备份点保留
            des += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '，';
        } else { // 按备份链保留
            des += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '，';
        }
       	if (!CONF.FUNCTIONS.includes("fileArchiveMode") || (CONF.FUNCTIONS.includes("fileArchiveMode") && !$('#archivecheck').get(0).checked)) {
			des += LANG.UI_RESERVE_RETENTION_MODE + '：';
        	if(CONF.RESERVE_TYPE.NUM == type){
				des += $('#reserveType option:selected').text();
        	    value = $('#spinnerNumInput').val();
			}else if(CONF.RESERVE_TYPE.DAY == type){
				des += $('#reserveType option:selected').text();
        	    value = $('#spinnerDayInput').val();
        	}
	   	}
		if(CONF.RESERVE_TYPE.PERMANENT == type || (CONF.FUNCTIONS.includes("fileArchiveMode") && $('#archivecheck').get(0).checked)) {
			des += LANG.UI_FILE_PERMANENT;
            value = '';
		}
        if($('#reserveType').val() !== "3") { //不是永久保留
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == type){
                des += "，" + LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY +'：' + value;
            }else{
                des += "，" + LANG.UI_STRATEGY_VALUE +'：' + value;
            }
        }
		$('.reserveDes').html(des);
        $('.reserveDes').attr('title', des);
    }

	//	初始化存储策略配置信息
	var initStoreStrategyDes = function(){
		var des = "";
//		if(authFun.length != 0 && authFun.dedupication){
	//	des += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationCheck').get(0).checked) + ", ";
//		}
        // 压缩传输
        des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);
		// 压缩等级
        if($('#compressCheck').get(0).checked){
            var gradeValue = $('#compressGrade').val();
            var grade = '';
            switch (parseInt(gradeValue,10)) {
                case 1: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                    break;
                case 2: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                    break;
                case 3: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                    break;
                case 4: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                    break;
            };
            des += "," + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade + ",";
        }
		des += LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);
        // 存储加密算法
        if($('#encryptStorageCheck').get(0).checked){
            var encryptedMethodLabel = $('.storage-encrypt-label').html();
            let method = $('#storageEncryptMethod').val();
            var grade = '';
            switch (parseInt(method)) {
                case 1: 
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2: 
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            };
            des += "," + encryptedMethodLabel + ": " + grade;
        }
		$('.storeDes').html(des);
        $('.storeDes').attr('title', des);
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

	var initReserveListeners = function(){
        //保留类型切换
		$('#reserveType').on('change', reserveTypeHandler);
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
		$('#spinnerNum .spinner-up').on('click', function(){
			initReserveStrategyDes();
        });
        $('#spinnerNum .spinner-down').on('click', function(){
        	initReserveStrategyDes();
        });
		$('#spinnerDay .spinner-up').on('click', function(){
			initReserveStrategyDes();
        });
        $('#spinnerDay .spinner-down').on('click', function(){
        	initReserveStrategyDes();
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
	//初始化节点传输网络列表
	var initNetworkList = function(nodeUuid){
        if (oldNode === nodeUuid) {
            return;
		}

        $('#transferNetworkTree').transferNetwork({
			node_uuid: nodeUuid,
			network_uuid: SETTINGS.bts.network,
			network_pool_uuid: SETTINGS.bts.network_pool_uuid
		});
		return;
	}

	var getFsCurrentUseLicense = () => {
		let uuids = data.srcInfo.agentList;
		let currentUse = uuids.length;
        return new Promise((resolve) => {
            let params = {
                module: 'file',
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
        //main function to initiate the module
        init: function () {
        	wizardInit();
        	initTree();	//原始数据在加载树后初始化
        	inintDatatimePicker();
			initTaskCrowd();
			initStrategy();       //初始化时间策略
        },
        
    };

}();

jQuery(document).ready(function() {   
	FileBackup.init();
});