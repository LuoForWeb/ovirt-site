var OsBackup = function () {
	var data = {srcInfo:{},backupInfo:{},highInfo:{}};
	var zTreeAgent;
//	var _timeStamp = '';	//时钟时间戳,全局
	var nodeSelectFlag = false; //自定义节点选择加载标志
	var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
//	var _agentName, _agentIP;
//	var _agentuuid;
	var _archiveValue = 2;
//	var _lastDbType;
//	var authFun = [];
	var speedList = [];
	var globalStrategy = [];

	var pageIndex = 0; //轮播索引
	var _oldPassword = '';
	var passwordChangeFlag = false; // 修改任务时是否改变了密码框内容标记
	var firstInitPageFlag = false; // 首次进入页面标记
	var SETTINGS = null;

	var editFlag = false;
	var initStrategyFlag = false;
	var initSpeedFlag = false;
	var defaultStrategy = [];
	var oldNode, networkFlag = false;	//用于比对加载传输网络的节点
	var currentmax = 0;
	const TRANSFER_MEAEN_STR_EXP = /[&\\]/; // HTML转义字符校验正则
	var cloudTargetFlag = false;
	var backupStrategy = {};
	var originCloudFlag = false; //云存储任务标志
	var initData = function(){
		//setp1
		//获取cbt
		data.srcInfo.cbt_flag = SETTINGS.mode.cbt_flag;
		//获取静默快照
		data.srcInfo.silent_snapshot_flag = SETTINGS.mode.silent_snapshot_flag;
		//获取有效数据
		data.srcInfo.valid_data_flag = SETTINGS.mode.valid_data_flag;
		//获取快照
		data.srcInfo.snapshot_flag = SETTINGS.mode.serial_snapshot_flag;

		//线程模式
		data.thread_num = SETTINGS.mode.threadnum;
		//任务名称
		data.taskName = SETTINGS.taskname;
		//传输模式
		data.srcInfo.transport_priority = SETTINGS.mode.transport_priority;

		//主机独有
		data.srcInfo.backup_oss_info = []; //选择的各个虚拟机信息
		data.srcInfo.backup_oss_info = SETTINGS.backup_oss_info;
//		"backup_oss_info": [{							//选择的各个虚拟机信息
//			"agent_uuid": "xxxxxx",
//			"os_config": {
//				"disk_list": ["xxx", "xxx"]
//			},
//			"dir_path": "xxxxxx"
//		}]

		//setp2
		//备份方式:策略/时间
		data.backupInfo.type = 'strategy';
		//完备/增备/差备/永久增量
		data.backupInfo.fullInfo = {};
		data.backupInfo.incrInfo = {};
		data.backupInfo.diffInfo = {};
		data.backupInfo.pIncrInfo = {};
		//按时间备份的时间
		data.backupInfo.datetime = null;
		var timestrategy = SETTINGS.timestrategy.data;
		//按时间备份的时间
		data.backupInfo.datetime = null;
		if(SETTINGS.timestrategy.type == "oncetime"){
			data.backupInfo.type = 'oncetime';
			data.backupInfo.datetime = SETTINGS.timestrategy.data;
		}else if(SETTINGS.timestrategy.type == "manual"){
			data.backupInfo.type = 'manual';
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
				info.startTime = timestrategy[i].start_time;
				info.rollFlag = timestrategy[i].roll_flag;
				info.rollInterval = timestrategy[i].roll_interval;
				info.endTime = timestrategy[i].roll_end_time;
				info.frequency = timestrategy[i].frequency;
				info.full_backup_compensation_flag = timestrategy[i].full_backup_compensation_flag;
				if(timestrategy[i].mode == '1'){
					data.backupInfo.fullInfo = info;
				}else if(timestrategy[i].mode == '2'){
					//只有一条则是永久增量
					if (1 == timestrategy.length) {
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

		//step3
		//限速策略
		speedList = SETTINGS.speedInfo;
		data.speedLimit = SETTINGS.speedInfo;
		data.speedLimit.speed = SETTINGS.speedInfo.speedInfo;
		//保留策略
		data.highInfo.reserve = {};
		data.highInfo.reserve.type = SETTINGS.brs.type;
		data.highInfo.reserve.value = SETTINGS.brs.number;
		data.highInfo.reserve.strategyMode = SETTINGS.brs.strategyMode;

		//初始化传输信息
		data.highInfo.transfer = {};
		data.highInfo.transfer =  SETTINGS.bts;

		//初始化存储策略
		data.highInfo.store = SETTINGS.bss;
		firstInitPageFlag = true;

		//初始化节点信息
		data.highInfo.node = {};
		data.highInfo.node.nodecheck = false;
		data.highInfo.node.nodeuuid = SETTINGS.node.nodeuuid;
		data.highInfo.node.storageuuid = SETTINGS.node.storageuuid;
		if(data.highInfo.node.storageuuid == ""){
			data.highInfo.node.storagecheck = true;
		}else{
			data.highInfo.node.storagecheck = false;
		}
		//备份策略
		backupStrategy.time = {};
		backupStrategy.store = {};
		backupStrategy.reserve = {};
		backupStrategy.time.timeInfo = data.backupInfo;
        backupStrategy.time.type = data.backupInfo.type;
		backupStrategy.speedlimit = SETTINGS.speedInfo;
		backupStrategy.store.storeInfo = data.highInfo.store;
		backupStrategy.reserve.reserveInfo = data.highInfo.reserve;
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.OS, true, backupStrategy);

		// 判断原来的存储是否为云存储
		if(SETTINGS.node.storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
			originCloudFlag = true;
		}
		data.highInfo.ignore_resource_limiting_flag = SETTINGS.ignore_resource_limiting_flag;
		data.retry_strategy =  SETTINGS.retry_strategy;
	}

	var initListener = function(){
		$('#backuptype').on('change', backupTypeHandler);
		//选择备份策略复选框
		$('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);

		//搜索
		$('#searchos').on('input propertychange',function(){
			searchOS();
		});
		//初始化存储策略配置监听
		// initStoreListeners();

        $("#toAgentManager").on('click',function(){
        	LOCATION('./content/client/client.php', 'infrastructure');
        })
		
      //数据加密密码确认
		$('#repassword').on('input propertychange', function(){
			checkPassword();
		});
		//数据加密密码输入
		$('#password').on('input propertychange', function(){
			checkPassword();
		});
        // 压缩传输
        $('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);
		// 传输策略---加密传输
        $('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptChange);
		//切换自动选择存储加密密码
		$('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
		//初始化重试策略
		$('#retry_config').retryStrategy();
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


	//数据加密密码确认检测
	var checkPassword = function(){
		passwordChangeFlag = true;
	}

	// var threadChange = function(){
	// 	var num = $("#backupThreadNum").val();
	// 	if(num > 8 || num < 1 || num == ""){
	// 		//还原默认值并给出提示
	// 		$('#backupThreadNum').val(3);
	// 		$('.backupThreadDiv').spinner('value', 3);
	// 		UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
	// 	}
	// }


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

    var checkSimpleForever = function(mode){
        for(var i=0;i<speedList.length;i++){
            if(mode == speedList[i].type){
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

	//存储策略配置监听
	// var initStoreListeners = function(){
    //     $('#backupThreadNum').on('input propertychange', function(){
    //     	initHighStrategyDes();
    //     });
    //     $('#backupThreadNum').blur(threadChange);
    //     $('.backupThreadDiv .spinner-up').on('click', function(){
    //     	threadChange();
    //     	initHighStrategyDes();
    //     });
    //     $('.backupThreadDiv .spinner-down').on('click', function(){
    //     	threadChange();
    //     	initHighStrategyDes();
    //     });
    // }

	//初始化高级策略配置信息
	var initHighStrategyDes = function(){
		var des = "";
		des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $("#backupThreadNum").val();
		$('.higeDes').html(des);
        $('.higeDes').attr('title', des);
	}
	//初始化高级配置
	var initHighDes = function(){
		//高级配置(高级配置暂时隐藏)
		var cbtlabel = LANG.UI_OS_BACKUP_GET_VALID_DATA;
		var des = cbtlabel + ": " + getSwitchDes(data.srcInfo.cbt_flag)+"<br>";
		//快照
		var snapshot = $('.snapshootlabel').html();
			des += snapshot + ": " + getSwitchDes(data.srcInfo.snapshot_flag);
		$('.highshow').html(des);
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
                }else if(9 == $(strategyMode[i]).data('mode')){
					des += strategyConfig.pIncrInfo.des + ". ";
				}
            }
        }else if('oncetime' == backupType){
            des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
		}
        $('.backupTimeDes').html(des);
        $('.backupTimeDes').attr('title', des);
	}


	//搜索主机代理
	var searchOS = function(){
		var value = $('#searchos').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		var nodes = zTreeAgent.getNodes();
		if(!nodes || nodes.length == 0) return;
		//获取所勾选的
		var checkNode =zTreeAgent.getCheckedNodes(true);
		//去掉分组层级
		if(checkNode.length != 0){
			for(var i in checkNode){
				if(checkNode[i].type == 0){
					checkNode.splice(i,1);
				}
			}
		}
		var allNode = zTreeAgent.transformToArray(zTreeAgent.getNodes());
		nodeParamList = zTreeAgent.getNodesByParamFuzzy('name', value);
		if(nodeParamList.length!=0){
			zTreeAgent.hideNodes(allNode);
			$('.os-tree').show();
			$('#nosearchtips').hide();
		}else{
			$('.os-tree').hide();
			$('#nosearchtips').show();
		}
		//连接搜索的和所勾选的
		nodeParamList =nodeParamList.concat(checkNode);
		nodeParamList = zTreeAgent.transformToArray(nodeParamList);
		for(var n in nodeParamList){
			findParent(zTreeAgent,nodeParamList[n], value);
		}
		nodeParamList = $.unique(nodeParamList.sort());
		if(nodeParamList.length == 0){
			// $('.os-tree').hide();
			$('.os-tree').hide();
			$('#nosearchtips').show();
		}
		zTreeAgent.showNodes(nodeParamList);
		zTreeAgent.expandAll(true);
	}
	//找到父节点
	var findParent = function(treeObj,node){
		// zTreeAgent.expandNode(node,true,false,false);
		var pNode = node.getParentNode();
		if(pNode != null){
			nodeParamList.push(pNode);
//			 findParent(zTreeAgent, pNode);
		}
	}





	var initSpinner = function(){
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
        $('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});
	}

	var step2Valid = function(){
		if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		var results = getNodeStr();
		return results;
	}

	var getNodeStr = function(){
		let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		data.highInfo.node.nodecheck = !backupTargetInfo.node_uuid;;
		data.highInfo.node.storagecheck = !backupTargetInfo.storage_uuid;
		data.highInfo.node.storageuuid = backupTargetInfo.storage_uuid;
		data.highInfo.node.storage_pool_uuid = backupTargetInfo.storage_pool_uuid;
		data.highInfo.node.nodeuuid = backupTargetInfo.node_uuid;
		data.highInfo.node.node_pool_uuid = backupTargetInfo.node_pool_uuid;
		data.highInfo.node.storage_type = backupTargetInfo.storage_type;
		$('.nodeinfoshow').html(backupTargetInfo.node_text);
		$('.storeinfoshow').html(backupTargetInfo.storage_text);
		//多客户端直接不显示传输网络
		networkFlag = getNetworkFlag();
		//多客户端直接不显示传输网络
		if (data.srcInfo.backup_oss_info.length > 1 || false === backupTargetInfo || !backupTargetInfo.node_uuid) {
			networkFlag = false;
		}
		//初始化传输网络
		if(networkFlag){
			$('.transfernetworkDiv').show();
			initNetworkList(backupTargetInfo.node_uuid);
		}else{
			$('.transfernetworkDiv').hide();
		}
		//判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
		getTapeStrategy(backupTargetInfo.storage_uuid, backupTargetInfo.storage_type,'.threadDiv', '.backupThreadDiv', '#tab_common', CONF.MODULE_TYPE.OS);
		//云存储不支持永久增量
		let storageType = parseInt($('#selectstorage option:selected').data('type'));
		if(storageType == 9 || storageType == CONF.BD_STORAGE_TYPE.TAPE){
			cloudTargetFlag = true;
			$('.pIncr').hide();
			$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
			$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
			initTimeStrategyDes();
			$('#tab_common').backupStrategy(CONF.MODULE_TYPE.OS, true, backupStrategy, 'cloud');
			//修改时间策略后的提示信息
			$('.all-strategy-tips').hide();
			$('.no-pIncr-tips').show();
		}else{
			cloudTargetFlag = false;
			$('.pIncr').show();
			$('#tab_common').backupStrategy(CONF.MODULE_TYPE.OS, true, backupStrategy);
			$('.no-pIncr-tips').hide();
			$('.all-strategy-tips').show();
			$('#reserveMode').removeAttr('disabled');
		}
		// 云存储任务不允许修改备份方式
		if(cloudTargetFlag){
			$('#reserveMode').val(2).prop('disabled', 'true');
			//如果是云存储任务，保留策略修改为按备份链保留，因为如果在第二步提交时，需要修改保留策略
			data.highInfo.reserve.strategyMode = parseInt($('#reserveMode').val());
		}
		initReserveStrategyDes();
		//初始化传输线程
		if(backupTargetInfo.storage_type != CONF.BD_STORAGE_TYPE.TAPE && CONF.FUNCTIONS.includes('multithread')){
			//如果授权线程
			$(".threadDiv").show();
		}else{
			//默认值为1
			$('.backupThreadDiv').spinner({value: 1, step: 1, min: 1,max: 8});//传输线程
			$(".threadDiv").hide();
		}
		// 初始化重复数据删除
		if (!CONF.FUNCTIONS.includes('dedupication')) {
            $('.deduplicationDiv').hide();
        }
		return true;
	}
	var initReserveStrategyDes = function(){
        var des = "";
        var type = $('#reserveType').val();
        var value = 0;
		// 保留方式
		let reserveMode = $('#reserveMode').val();
		des +=LANG.UI_RESERVE_RETENTION_TYPE+": "
		if (reserveMode == 1) {
			des += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + ', ';
		} else {
			des += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + ', ';
		}
		des +=LANG.UI_RESERVE_RETENTION_MODE+": "
        if(CONF.RESERVE_TYPE.NUM == type){
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				des += LANG.UI_BACKUP_NUM
			}else{
				des += LANG.UI_STRATEGY_RESERVE_NUM_EN;
			}
            value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == type){
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				des += LANG.UI_BACKUP_DAY;
			}else{
				des += LANG.UI_STRATEGY_RESERVE_DAY_EN;
			}
            value = $('#spinnerDayInput').val();
        }
        value = parseInt(value);
		let methoddes = LANG.UI_STRATEGY_VALUE
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && type == CONF.RESERVE_TYPE.DAY){
			methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
		}
		des +=", "+ methoddes +": "
        des += value;

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


	var showStep3 = function(strategyMode){
		//时间策略
		$('.backupTypeInfoDiv').show();
		var strategyMode = $('#strategymode').find('input:checked');
		var backuptypeshow = $('.backuptypeshow'), backuptypeinfoshow = $('.backuptimeinfoshow');
		var backuptypeshowStr = '', backuptypeinfoshowStr = '';
		if("strategy" == data.backupInfo.type){
			backuptypeshowStr = LANG.UI_BACKUP_USE_STRATEGY;
			for(var i=0; i<strategyMode.length; i++){
				if(1 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backupInfo.fullInfo.des + "<br>";
				}else if(2 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backupInfo.incrInfo.des + "<br>";
				}else if(3 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backupInfo.diffInfo.des + "<br>";
				}else if(4 == $(strategyMode[i]).data('mode')){
					backuptypeinfoshowStr += data.backupInfo.logInfo.des + "<br>";
				}else if(9 == $(strategyMode[i]).data('mode') && !cloudTargetFlag){
					backuptypeinfoshowStr += data.backupInfo.pIncrInfo.des + "<br>";
				}
			}
		}else if("oncetime" == data.backupInfo.type){
			backuptypeshowStr = LANG.UI_BACKUP_ONCE;
			backuptypeinfoshowStr = LANG.UI_PUBLIC_START_TIME + ": " + data.backupInfo.datetime;
		}else if ('manual' === data.backupInfo.type) {
			backuptypeshowStr = LANG.UI_BACKUP_MANUAL;
			$('.backupTypeInfoDiv').hide();
		}
		backuptypeshow.html(backuptypeshowStr);
		backuptypeinfoshow.html(backuptypeinfoshowStr);


		//保留策略
		var reservetypeshow = $('.reservetypeshow');
		var selectedStorageType = data.highInfo.node.storage_type;
		if (selectedStorageType != CONF.BD_STORAGE_TYPE.TAPE) { // 选的存储类型为磁带时
			var reservetypeStr = LANG.UI_RESERVE_RETENTION_TYPE + ': ';
			if(CONF.RESERVE_STRATEGY_MODE.POINT == data.highInfo.reserve.strategyMode){
				reservetypeStr += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT;
			}else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == data.highInfo.reserve.strategyMode){
				reservetypeStr += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN;
			}
			reservetypeStr += '<br>'+ LANG.UI_RESERVE_RETENTION_MODE +': ';
			if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
				reservetypeStr += LANG.UI_BACKUP_NUM;
			}else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
				reservetypeStr += LANG.UI_BACKUP_DAY;
			}
			var methoddes = LANG.UI_STRATEGY_VALUE;
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
                methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
            }
			reservetypeStr += "<br>" + methoddes+": " + data.highInfo.reserve.value;
			//保留策略
			reservetypeshow.html(reservetypeStr);
		}


		//存储策略
		var deduplicationLabel = $('.deduplicationLabel').html();
//		var blocksizelabel = $('.blocksizelabel').html();
		var compressLabel = $('.compressLabel').html();
		var encryptStorageLabel = $('.encryptStorageLabel').html();
		var passwordAutoLabel = $('.passwordAutoLabel').html();
		var storeInfo = "";
		if(1 != CONF.SOFTWARE && 4 != CONF.SOFTWARE && CONF.FUNCTIONS.includes('dedupication')){
			storeInfo += deduplicationLabel + ": " + getSwitchDes(data.highInfo.store.deduplication) + "<br>";
		}
		// 压缩存储
		storeInfo += compressLabel + ": " + getSwitchDes(data.highInfo.store.compress) + "<br>";
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
            storeInfo += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade + "<br>";
		}
		storeInfo += encryptStorageLabel + ": " + getSwitchDes(data.highInfo.store.encrypt) + "<br>";
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
            storeInfo += encryptedMethodLabel + ": " + grade + "<br>";
		}
		if(data.highInfo.store.encrypt){
			storeInfo += passwordAutoLabel + ": " + getSwitchDes(data.highInfo.store.password_auto_flag);
		}
		$('.storageinfoshow').html(storeInfo);


		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedLimitsStr = '';
		if (data.speedLimit.speedInfo.length != 0) {
			speedLimitsStr = '';
			for (let i = 0; i < data.speedLimit.speedInfo.length; i++) {
				speedLimitsStr += data.speedLimit.speedInfo[i].des + '<br>';
			}
		}
		if (speedLimitsStr == '') {
			speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedLimitsStr);
		//高级策略
		// if (selectedStorageType == CONF.BD_STORAGE_TYPE.TAPE){
		// 	$('.highstrategyshowdiv').hide();
		// }else{
		// 	$('.highstrategyshowdiv').show();
		// 	var backupThreadNum = $("#backupThreadNum").val();
		// 	$(".hightypeshow").html(LANG.UI_GLOBAL_STRATEGY_THREAD_NUM+": "+ backupThreadNum);
		// }
		//传输策略
		var transferlabel = $('.transferlabel').html();
		var encryptlabel = LANG.UI_OS_BACKUP_TRANSFER_ENCRY;
		var transfernetworklabel = $('.transfernetworklabel').html();
		var des = encryptlabel + ": " + getSwitchDes(data.highInfo.transfer.encrypt) + '<br>';
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
            des += encryptedMethodLabel + ": " + grade + "<br>";
		}
//		des += "  " + transferlabel + ": " + $('#transport_mode').find("option:selected").text()
		//节点传输网络信息显示
		if(networkFlag){
			let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
			des += "  " + $('.transfernetworklabel').html() + ": " + networkNode.str + "<br>";
		}
		//传输线程
		if (selectedStorageType != CONF.BD_STORAGE_TYPE.TAPE && CONF.FUNCTIONS.includes('multithread')){
			var backupThreadNum = $("#backupThreadNum").val();
			des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": "+ backupThreadNum
		}
		$('.transfershow').html(des);
		//高级配置
		//快照
		var validDataFlaglabel = $('.validDataFlaglabel').html();
		des = validDataFlaglabel + ": " + getSwitchDes(data.srcInfo.valid_data_flag) +"<br>";
		var silentSnapshotlabel = $('.silentSnapshotlabel').html();
		des += silentSnapshotlabel + ": " + getSwitchDes(data.srcInfo.silent_snapshot_flag) +"<br>";
		var cbtFlaglabel = $('.cbtFlaglabel').html();
		des += cbtFlaglabel + ": " + getSwitchDes(data.srcInfo.cbt_flag) +"<br>";
		// var snapshotFlaglabel = $('.snapshotFlaglabel').html();
		// des += snapshotFlaglabel + ": " + getSwitchDes(data.srcInfo.snapshot_flag) +"<br>";
		// 过载资源保护
		des += $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.highInfo.ignore_resource_limiting_flag);
		$('.highshow').html(des);


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
		var result = getSpeedStr() & getReserveStr() & getAchiveStr() & getTransferStr() & getStoreStr() & getHighStr() & getHigh();
		if(result){
			showStep3();
		}
		return result;
	}

	//得到高级策略
	var getHigh = function(){
		//得到线程数
		data.thread_num = $("#backupThreadNum").val();
		if(data.thread_num == "" || data.thread_num > 8 || data.thread_num <= 0 || !/^\d+$/.test(data.thread_num)){
			//重置为默认值
			$('.backupThreadDiv').spinner("value", 3);
			initHighStrategyDes();
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		return true;
	}


	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	//得到时间策略
	var getTimeStr = function(){
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();//获取配置信息
		data.backupInfo.fullInfo = {};
		data.backupInfo.diffInfo = {};
		data.backupInfo.incrInfo = {};
		data.backupInfo.pIncrInfo = {};
		var strategyMode = $('#strategymode').find('input:checked');
		data.backupInfo.type = $('#backuptype').val();
//		var fullBakup = $("#fullBackup").prop("checked");//获取完全备份是否选中
//		var archivelogBackup = $("#archivelogBackup").prop("checked");//归档日志是否选中
		if("strategy" == data.backupInfo.type){
			if(0 == strategyMode.length){
				//没有选择时间策略
				if(cloudTargetFlag){
					UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_VM_BACKUP_SET_STRATEGY_TIPS);
				}else{
					UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_BACKUP_SET_STRATEGY_TIPS);
				}
				return false;
			}
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
				}else if(4 == $(strategyMode[i]).data('mode')){
					data.backupInfo.logInfo = strategyConfig.logInfo;
					if(strategyConfig.logInfo.rollFlag && !checkTime(strategyConfig.logInfo.startTime,strategyConfig.logInfo.endTime)) return;
				}else if(9 == $(strategyMode[i]).data('mode') && !cloudTargetFlag){
					data.backupInfo.pIncrInfo = strategyConfig.pIncrInfo;
					if(strategyConfig.pIncrInfo.rollFlag && !checkTime(strategyConfig.pIncrInfo.startTime,strategyConfig.pIncrInfo.endTime)) return;
				}
			}
			$('.settimetip').hide();
			return true;
		}else if("oncetime" == data.backupInfo.type){
			var onceTime = $('#oncetime').val();
			if("" != onceTime){
				$('.settimetip').hide();
				var systemTime = $('#servertime').text();
				var onceTimeSize = new Date(onceTime).getTime();
				var systemTimeSize = new Date(systemTime).getTime();
				if(onceTimeSize <= systemTimeSize){
					UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_CORRENT_TIME_TIPS);
					return false;
				}
				data.backupInfo.datetime = onceTime;
				return true;
			}else{
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
				return false;
			}
		}else if ('manual' === data.backupInfo.type) {
			return true;
		}

		return false;
	}


	//得到保留策略
	var getReserveStr = function(){
		data.highInfo.reserve.strategyMode = parseInt($('#reserveMode').val());
		data.highInfo.reserve.type = $('#reserveType').val();
		if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerDayInput').val();
		}
		if(!(data.highInfo.reserve.value > 0)){
			UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
				return false;
			}
		return true;
	}
	//得到归档策略
	var getAchiveStr = function(){return true;}

	//得到限速策略
	var getSpeedStr = function(){
		data.speedLimit = getSpeedStrategyInfo();
		return true;
	}

	//得到高级策略
	var getHighStr = function(){
		//得到有效数据
		data.srcInfo.valid_data_flag = $("#valid_data_flag").get(0).checked;
		//得到静默快照
		data.srcInfo.silent_snapshot_flag = $("#silent_snapshot").get(0).checked;
		//得到CBT
		data.srcInfo.cbt_flag = $("#cbt_flag").get(0).checked;
		//得到快照
		data.srcInfo.snapshot_flag = $("#snapshot_flag").get(0).checked;
		//得到重试策略
		data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		if (!data.retry_strategy) {
			return false;
		}
		data.highInfo.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		return true;

	}

	//得到传输策略
	var getTransferStr = function(){
		data.highInfo.transfer.encrypt = $('#transport_encrypt_flag').get(0).checked; //得到传输加密
		// 传输加密算法
		data.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
		data.srcInfo.transport_priority = $('#transport_mode').val(); //得到传输加密
		data.highInfo.transfer.network = $('#transferNetwork').val();
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
		//重复数据删除
		data.highInfo.store.deduplication = $('#deduplicationCheck').get(0).checked;
		//压缩传输
		data.highInfo.store.compress = $('#compressCheck').get(0).checked;
		// 压缩等级
        if($('#compressCheck').get(0).checked){
            data.highInfo.store.compress_method = $('#compressGrade').val();
        }
		//得到数据块大小,前端没显示 先默认传1M
		data.highInfo.store.blocksize = 1024;
		//得到是否加密
		data.highInfo.store.encrypt = $('#encryptStorageCheck').get(0).checked;
        // 存储加密
        data.highInfo.store.encrypt_method = parseInt($('#storageEncryptMethod').val());
		//得到是否生成密码
		data.highInfo.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;
		// 非法字符串校验
		if (!isNotLatinCode(getPassword())) {
			return false;
		}
		//得到密码
		data.highInfo.store.password = btoa(getPassword());
		var repassword = $.trim($('#repassword').val());

		// 特殊字符校验
		if (TRANSFER_MEAEN_STR_EXP.test($.trim($('#password').val())) || TRANSFER_MEAEN_STR_EXP.test($.trim($('#repassword').val()))) {
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_OS_RESTORE_VERIFY_SPECIFY_WORD);
			return false;
		}

		// 开启数据加密
		if (data.highInfo.store.encrypt) {
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

	//存储之前检查是否有修改过密码
	var getPassword = function(){
		var now_password = $.trim($('#password').val());
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
//            $('.step-title', $('#osbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#osbackupcontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#osbackupcontent').find('.button-previous').css('visibility', 'hidden');
                $('#osbackupcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#osbackupcontent').find('.button-previous').css('visibility', 'visible');
                $('#osbackupcontent').find('.button-next').removeClass('next-btn-margin-left');
            }
            if (current >= total) {
                $('#osbackupcontent').find('.button-next').hide();
            } else {
                $('#osbackupcontent').find('.button-next').show();
            }
            //用于判断是否展示提交按钮
            if(current < currentmax){
            	$('#osbackupcontent').find('.button-submit').css('visibility', 'hidden');
            }else{
            	$('#osbackupcontent').find('.button-submit').css('visibility', 'visible');
            }

            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#osbackupcontent').bootstrapWizard({
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
                		if (!step1Valid(() => {
                            $('a[href="#tab2"]').tab('show'); // 手动切换步骤页面
                            handleTitle(tab, navigation, index);
							pageIndex = 1;
                        })) {
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
//                var total = navigation.find('li').length;
//                var current = index + 1;
//                var $percent = (current / total) * 100;
//                $('#osbackupcontent').find('.progress-bar').css({
//                    width: $percent + '%'
//                });
            }
        });

        $('#osbackupcontent').find('.button-previous').css('visibility', 'hidden');
        // $('#osbackupcontent .button-submit').click(submit).css('visibility', 'visible');
		$('#osbackupcontent .button-submit').click(() => {
            // 授权判断
            getOsCurrentUseLicense().then(() => {
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
        }).css('visibility', 'visible');
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
		if(pageIndex == 3){
			data.taskName = $.trim($("#jobname").val());
		}
		//如果是磁带，传输线程默认给后端的值为3
		var storageType = data.highInfo.node.storage_type;
		if (storageType == CONF.BD_STORAGE_TYPE.TAPE) {
			data.thread_num =  1;
			data.highInfo.reserve.type = 1;
			data.highInfo.reserve.strategyMode = 1;
			data.highInfo.reserve.value = 30;
		}
		if(!CONF.FUNCTIONS.includes('multithread')){
			data.thread_num =  1;
		}
		//提交的时候重新调用下这个方法
		data.speedLimit = getSpeedStrategyInfo();
		//TODO提交
    	var jsonData = JSON.stringify(data);
    	Metronic.blockUI({target: '#osbackupcontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:34,f:'editOSBackupJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#osbackupcontent');
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}

	//选中或取消某个时间策略
	var strategyModeClick = function(event){
		var mode = $(this).data('mode');
		if(event.target.checked){
			//如果是取消选中
			$('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').hide();
		}else{
			//如果是选中
			$('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').show();
		}
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
				$('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
				//选中差异同时选中完备
				$('#strategymode').find('input[data-mode=1]').iCheck('check');
				$('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
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
	}
	//备份类型
	var backupTypeHandler = function(){
		data.highInfo.reserve.type = $('#reserveType').val();
		data.backupInfo.type = this.value;
		$('.settimetip').hide();
		initReserveStrategyDes();
	}

//------------------------------------------------------------------------------------------



	//初始化主机代理树
	var setTree = function(zNodes){
		//如果返回的值为空,则增加提示
		if(zNodes == "[]"){
			$('#nodatatips').show();
			$('.VMList-div').hide();
			$('.os-tree').hide();
			$(".vm_tree_div").hide();
			return;
		}else{
			$('#nodatatips').hide();
			$('.VMList-div').show();
			$('.os-tree').show();
			$(".vm_tree_div").show();
		}
		var setting = {
				check: {
					enable: true,
					autoCheckTrigger: true,
					nocheckInherit: false//true 表示 新加入子节点时，自动继承父节点 nocheck = true 的属性。false 表示 新加入子节点时，不继承父节点 nocheck 的属性。
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
					beforeClick: agentnodeSelect,
					onCheck: OsOnCheck,
				}
			};
		var nodes = JSON.parse(zNodes);
		zTreeAgent = $.fn.zTree.init($("#os_tree"), setting, nodes);


		Metronic.blockUI({target: '.VMList-div', animate: true});
		setTimeout(function(){
			for(var i=0;i<nodes.length;i++){
				if(nodes[i].checked){
					var checkNode = zTreeAgent.getNodesByParam("id", nodes[i].id, null);
					addOSList('os_tree',checkNode[0]);
				}
			}
			Metronic.unblockUI('.VMList-div');
			$('#osbackupcontent').find('.button-submit').show();
			$('#osbackupcontent').find('.button-next').show();
		},500);
		var allNodes = $.fn.zTree.getZTreeObj('os_tree').getNodes();
		for(var j=0;j<allNodes.length;j++){
			if(allNodes[j].type == -1){
				if(allNodes[j].children && allNodes[j].children.length != 0){
					$.fn.zTree.getZTreeObj('os_tree').expandNode(allNodes[j], true);
				}
			}
		}
//		var checkNodes = zTreeAgent.getCheckedNodes();
//		for (var i = 0;i < checkNodes.length;i++) {
//			zTreeHC.checkNode(checkNodes[i], true, true);
//		}
//		currentTree = zTreeHC;
//		currentTreeDivId = 'os_tree';

	};

	//勾选主机主机操作
	var OsOnCheck = function(e, Id, Node){
//		checkOsOnlineAndTips(Node);//判断是否可点击 并给出提示
//		addOSList(Id,Node);

		checkOsOnlineAndTips(Node);//判断是否可点击 并给出提示
		//勾选操作时如果是分组这一层则勾选分组下所有
		if(Node.type == 0){
			var childrenNodes = Node.children;
			if(childrenNodes != undefined || childrenNodes != "[]" || childrenNodes != "" || childrenNodes != null){
				for(var i=0; i<childrenNodes.length; i++){
					if(childrenNodes[i].open == false){
						continue;
					}
					addOSList(Id,childrenNodes[i]);
				}
			}else{
				return;
			}
		}else{
			addOSList(Id,Node);
		}

	}

	//检测主机状态 并确定是否可点击和提示
	var checkOsOnlineAndTips = function(node){

	}

	//勾选添加虚拟机显示列表
	var addOSList = function(id,node){
    		var info = "";
    		var diskinfo = "";
    		var liId = clearString(node.id + node.uuid); //添加虚拟机每列ID
    		if(node.type == 0){
    			return;
    		}
    		var treeObject = $.fn.zTree.getZTreeObj(id);
    		if(node.checked){
				//如果是离线状态 开启其勾选状态
				if( !node.online_flag){
					treeObject.setChkDisabled(node, false);//解禁用
				}
    			info +=
				'<li class="list-group-item popovers VMTips list-group-item__vm" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.name +'">' +
					'<a href="#disk' + liId + '" data-toggle="collapse" aria-expanded="true" id="disHref' + liId + '" style="display:flex;" class="accordion-toggle accordion-toggle-styled collapsed">' +
						'<div class="col1">' +
							'<div class="cont vmDetail">' +
								'<div class="cont-col1" style="padding-top: 5px;">' +
									'<div class="'+node.iconSkin+'"></div>' +
								'</div>' +
								'<div class="cont-col2">' +
									'<div class="desc list-one">' + node.name + '</div>' +
								'</div>' +
							'</div>' +
						'</div>' +
						'<div class="col2 pull-right delete-list" style="position:absolute;right:0;width:30px;padding-top: 8px;">' +
							'<span class="del'+liId+'" >' +
								'<span class="col2-i">' +
									'<i class="viconfont vicon-guanbi"></i>' +
								'</span>' +
							'</span>' +
						'</div>' +
					'</a>' +
				'</li><ul id="disk' + liId + '" class="feeds collapse diskList" style="list-style:none;"></ul>';


    			//把勾选的主机添加到右边去
    			$('#addOSList').append(info);
				 $('#' + escapeJquery(liId)).popover();	   //初始化tips

				//移除虚拟机显示
	    		$('.del'+escapeJquery(liId)).on('click', function(){
	    			//如果移除了 则把其移除标志设置为true  仅作为第二次勾选后默认勾选所有
	    			node.removeflag = true;
	    			var treeObj = $.fn.zTree.getZTreeObj(id);
	    			$('.popover.in').remove();
	        		treeObj.checkNode(node,false,true);
	        		$('#' + escapeJquery(liId)).remove();
	        		$('#disk' + escapeJquery(liId)).remove();
	        		node.initDisk = false;
	    		});

	    		$('#disHref' + liId).on('click', function(){
	    			//如果是离线状态则让其展开
	    			if(!node.online_flag){
	    				UIToastr.showWarning(LANG.UI_OS_DEVELOP_SELECT_HOST, LANG.UI_OS_DEVELOP_SELECT_HOST_TIP);
	    				return;
	    			}
	    			if(!node.initDisk){
	    				var p = {};
		    			p.uuid = node.id;
	    				var jsonData = JSON.stringify(p);
	    				Metronic.blockUI({target: '#'+liId,animate: true});
		    			$.post(CONF.AJAXPATH, {m:34,f:'getOSBackupAgentInfo',p:jsonData}, function(d){
		    				Metronic.unblockUI('#'+liId);
		    				var data = JSON.parse(d);
		    				node.initDisk = true;
		    				node.detail = data.infoVolume;//得到的是一个多维数组
		    				//初始化每台虚拟机的磁盘信息
		    				if(node.detail && node.detail != ""){//判断得到的数据是否为空的情况
		    					var diskCount = node.detail.length;//得到有多少个分区情况
		    			    	var disklist = node.detail;
								var allremove_flag =  false;
								var removenodelist = [];
		    			    	for(var i=0;i<diskCount;i++){
		    			    		var strSize = disklist[i].usable_size+LANG.UI_OS_BACKUP_DISK_TIP+disklist[i].total_size_str;
		    			    		//这里需要判断show_flag(是否显示)和rd_flag(是否勾选且不可选)
		    			    		//show_flag的已经后台限制了 这里只需要判断是否是rd_flag就行
		    			    		var checkClass = "icheckbox_square-blue"; //默认为蓝色
		    			    		var checkInput = ""; //默认可勾选
		    			    		var checkedflag = "";
		    			    		if(disklist[i].rd_flag == 1 || disklist[i].is_bitLocker == 1){
		    			    			checkClass = "icheckbox_square-aero"; //灰蓝色
		    			    		    checkInput = "disabled";  //不可点击
										checkedflag = "checked";
		    			    		}
									var lockStringHtml = "";
									//如果是1为锁定状态,则增加小锁标志
									if(disklist[i].is_bitLocker == 1){
										lockStringHtml = '<i class="icontop1 viconfont vicon-ge_lock"></i>'
									}
									var removabledes = "";
									if(disklist[i].removable_flag){
										removabledes = "("+ LANG.UI_OS_REMOVE_DEVICE +")";
										allremove_flag = true;
										removenodelist.push("disCheck" + escapeJquery(disklist[i].dev_id) + escapeJquery(disklist[i].vol_uuid));
									}
									disklist[i].display_name += removabledes;
		    						diskinfo +=
									'<li class="list-group-item popovers diskTips osLiHeight" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + disklist[i].display_name + strSize + '" id="check_' + disklist[i].vol_uuid + '">' +
		    							'<div class="col-md-10 disName">' +
											'<div class="'+ disklist[i].iconSkin+'"></div>' +
											'<span>' + disklist[i].display_name + '</span>' +lockStringHtml+
											'<br>' +
											'<span style="font-size:12px;color:#999;margin-left:20px;">' + strSize + '</span>' +
											'<span class="display-none" id="disuuid'+ i + node.id + node.uuid +'">'+ disklist[i].vol_uuid +'</span>' +
										'</div>'+
		    						 	'<div class="col-md-2 disk-checkbox">' +
											'<input '+checkInput+' class="icheck diskCheck" type="checkbox" checked = "'+checkedflag+'" id="disCheck' + escapeJquery(disklist[i].dev_id) + escapeJquery(disklist[i].vol_uuid) + '" value="' + disklist[i].name + '">' +
										'</div>' +
									'</li>';
		    					}
		    					$('#disk' + escapeJquery(liId)).append(diskinfo);
		    					$('.diskTips').popover();
		    					$('.diskCheck').iCheck({
		    						checkboxClass : 'icheckbox_square-blue',
		    					});
		    					$('.iCheck-helper').on('mouseover',function(){
		    						$('.diskTips').popover('hide');
		    					});
								$('#disk' + escapeJquery(liId) + ' .diskCheck').iCheck('check');
								if(allremove_flag && node.removeflag ){
									//第二次添加并且是可移动磁盘 不用默认勾选
									for(var i = 0;i < removenodelist.length; i++){
										$('#'+removenodelist[i]).parent().removeClass('checked');
									}
								}
		    					
								
		    					if(node.removeflag == true){
		    						return;
		    					}
		    					//先获取当旧的勾选的信息
		    					var oldSelectInfo = SETTINGS.backup_oss_info
		    					//先得到当前展开的agent_uuid
		    					var nowAgentuuid = node.uuid;
		    					//得到当前旧的已勾选的分区信息
		    					var oldDiskList = null;
		    					//得到新的所有分区信息
		    					var nowDiskList = node.detail;
		    					//得到旧的agent_uuid
		    					for(var x=0; x<oldSelectInfo.length; x++){
		    						oldAgentuuid = oldSelectInfo[x].agent_uuid;
		    						if(oldAgentuuid != nowAgentuuid){
		    							continue;
		    						}
		    						if(oldAgentuuid == nowAgentuuid){
		    							//得到旧的已勾选的分区信息uuid
		    							oldDiskList = oldSelectInfo[x].os_config.disk_list;
		    						}
		    					}
		    					if(oldDiskList == null || oldDiskList == "" || oldDiskList.length == 0){
//		    						$('#disk' + escapeJquery(liId) + ' .diskCheck').iCheck('check');
		    						return;
		    					}
		    					//获取所有数据后开始标记是否勾选
		    					for(var ii = 0; ii < nowDiskList.length; ii++){
		    						if($.inArray(nowDiskList[ii].vol_uuid, oldDiskList) != -1){
		    							$('#disCheck'+ escapeJquery(disklist[ii].dev_id) + escapeJquery(disklist[ii].vol_uuid)).iCheck('uncheck');
		    						}
		    						//如果是置灰情况,统一默认勾选
		    						if(nowDiskList[ii].rd_flag == 1){
		    							$('#disCheck'+ escapeJquery(disklist[ii].dev_id) + escapeJquery(disklist[ii].vol_uuid)).iCheck('check');
		    						}
									//取消is_bitLocker为1的勾选情况
									if(disklist[ii].is_bitLocker == 1){
										$('#disCheck'+ escapeJquery(disklist[ii].dev_id) + escapeJquery(disklist[ii].vol_uuid)).iCheck('uncheck');
									}
									
		    					}

		    				}else{
		    					diskinfo += '<li class="list-group-item">' + LANG.UI_BACKUP_NO_DISK + '</li>';
		    					$('#disk' + escapeJquery(liId)).append(diskinfo);
		    				}
		    			});
	    			}
	    		});

				// 给每个diskTips加上点击事件联动checkbox的勾选
				$('#disk' + liId).on('click', 'li', function(e) {
					var icheckbox_square = '#' + e.currentTarget.id + ' .disk-checkbox .icheckbox_square-blue';
					var icheckbox_square_input = icheckbox_square + ' input';

					// 已禁用的不能联动勾选
					if (!$(icheckbox_square).hasClass('disabled')) {
						if ($(icheckbox_square).hasClass('checked')) {
							// 已勾选取消勾选
							$(icheckbox_square).removeClass('checked');
							$(icheckbox_square_input).prop('checked', false);
						} else {
							// 未勾选则勾选
							$(icheckbox_square).addClass('checked');
							$(icheckbox_square_input).prop('checked', true);
						}
					}
				})
    		}else{
    			node.removeflag = true;
    			//检查node.id是否在虚拟化里面，如果在就要把对应的li移除
    			$('#disk' + escapeJquery(liId)).remove();
    			$('#' + escapeJquery(liId)).remove();
    			node.initDisk = false;
    		}
    	}
	//替换特殊字符
	var clearString = function (s){
	    var rs = "";
	    for (var i = 0; i < s.length; i++) {
	        rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
	    }
	    return rs;
	}
	//去掉转义
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


	//选择节点事件-点击节点勾选或移除勾选状态
	var agentnodeSelect = function(Id, Node){
//		var treeObj = $.fn.zTree.getZTreeObj(Id);
//			//得到当前节点的勾选状态
//			if(Node.checked){//如果节点是打开状态
//				treeObj.checkNode(Node,false);
//			}else{
//				treeObj.checkNode(Node,true);
//			}
//			addOSList(Id, Node);


		//先判断是否是在任务中 ,如果在任务中则提示并禁止继续执行
		var agent_msg = SETTINGS.backup_oss_info;
		var agent_uuids = [];
		for(var i=0;i<agent_msg.length;i++){
			agent_uuids.push(agent_msg[i].agent_uuid);
		}
		//判断是否有在任务中,没有则提示
		if(!agent_uuids.includes(Node.id)){
			if(Node.agentuuidInTask == true){
				UIToastr.showWarning(LANG.UI_OS_BACKUP_HOST_ALREADY_TASK, LANG.UI_OS_BACKUP_HOST_ALREADY_TASK_ERROR);
				return;
			}
		}
		if(Node.online_flag == false){
			UIToastr.showWarning(LANG.UI_OS_OFFLINE, LANG.UI_OS_OFFLINE_TIP);
			return;
		}
		var treeObj = $.fn.zTree.getZTreeObj(Id);
		//先判断是否为分组,如果为分组则展开但是不选中
		if(Node.type == 0){
			//展开节点
			if(Node.open){
				treeObj.expandNode(Node,false);
			}else{
				treeObj.expandNode(Node,true);
			}
		}else{
			//得到当前节点的勾选状态
			if(Node.checked){//如果节点是打开状态
				treeObj.checkNode(Node,false);
				//得到父节点
				var parNode = Node.getParentNode();
//				treeObj.checkNode(parNode,true);
				//得到父节点所有子的集合
				var parChildNode = Node.getParentNode().children;
				treeObj.checkNode(parNode,false);
				//循环看看
				for(var i=0; i<parChildNode.length; i++){
					if(parChildNode[i].checked){
						treeObj.checkNode(parNode,true);
						break
					}
				}

			}else{
				treeObj.checkNode(Node,true);
				//检查父节点是否有打开,如果父节点没选中则设置为选中
				//得到父节点
				var parNode = Node.getParentNode();
//				treeObj.checkNode(parNode,true);
				//得到父节点所有子的集合
				var parChildNode = Node.getParentNode().children;
				//循环看看
				for(var i=0; i<parChildNode.length; i++){
					if(parChildNode[i].checked){
						treeObj.checkNode(parNode,true);
						break;
					}
				}

			}
			addOSList(Id, Node);
		}
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
				clearTimeout(timerTask.OsBackup_serverTime);
				return;
			}
			servertime.html(getDate(timeStamp++));
			timerTask.OsBackup_serverTime = setTimeout(startClock, updateInterval);
		}
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemTime',p:{}}, function(data){
			timeStamp = data;
			startClock();
		});
	}

	var step1Valid = function(showTitleCallback){
		//获取tree所有勾选的数据集合
		var treeObj = $.fn.zTree.getZTreeObj('os_tree');
		$("#searchos").val("");
		searchOS();
		var selectNodes = treeObj.getCheckedNodes(); //获取所有勾选的集合
		if(selectNodes.length == 1 && selectNodes[0].type == 0){ //如果只勾选了一个分组
			$(".selectostip").html(LANG.UI_OS_BACKUP_PLEASE_CHOOSE_HOST).show();
			return false;
		}
		if(!selectNodes.length || selectNodes.length == 0){ //如果没有勾选任何数据 则
			$(".selectostip").html(LANG.UI_OS_BACKUP_PLEASE_CHOOSE_HOST).show();
			return false;
		}
		//开始组合数据
		data.srcInfo.backup_oss_info = [];
		var olduuidList = [];//获取以前旧的agent_uuid
		for(var i in SETTINGS.backup_oss_info){
			olduuidList.push(SETTINGS.backup_oss_info[i].agent_uuid);
		}
		var hasuuidList = []; //已经存入的磁盘uuid
		for(var i=0;i<selectNodes.length;i++){
			//得到分组uuid
			if(selectNodes[i].type == 0){
				continue;
			}
			//初始化一个对象{}
			var oneInfo = {};
			oneInfo.agent_group_uuid = selectNodes[i].getParentNode().uuid;
			oneInfo.agent_uuid = selectNodes[i].uuid; //得到每个代理的uuid
			oneInfo.agent_name = selectNodes[i].name //得到每个代理的名称
			oneInfo.os_config = {};
			oneInfo.os_config.disk_list = [];
			oneInfo.os_config.disk_des = [];
			oneInfo.os_config.disk_value = [];
			oneInfo.dir_path = "";
			//添加disk_list和最后一步显示已排除磁盘列表
			if(!selectNodes[i].initDisk){
				//如果是之前已经取消过一次了 则不再执行初始化旧数据了
				if(selectNodes[i].removeflag == true){
					data.srcInfo.backup_oss_info.push(oneInfo);
					continue;
				}

				if($.inArray(selectNodes[i].uuid,olduuidList) != -1){
					//如果没有展开,则读取之前存的默认配置
					for(var h in SETTINGS.backup_oss_info){
						//查看是否已经被存了
						if($.inArray(selectNodes[i].uuid,hasuuidList) == -1){
							if(selectNodes[i].uuid == SETTINGS.backup_oss_info[h].agent_uuid){
								hasuuidList.push(selectNodes[i].uuid);
								data.srcInfo.backup_oss_info.push(SETTINGS.backup_oss_info[h]);
								break;
							}

						}else{
							hasuuidList.push(selectNodes[i].uuid);
							data.srcInfo.backup_oss_info.push(oneInfo);
							break;
						}
					}
					continue;
				}
				hasuuidList.push(selectNodes[i].uuid);
				data.srcInfo.backup_oss_info.push(oneInfo);
				continue;
			};
			//如果没有排除磁盘 或者默认全勾选 则跳过本次循环
			var diskCount =selectNodes[i].detail.length;//获取一共有多少个分区或者磁盘
			for(var j=0;j<diskCount;j++){
				if(selectNodes[i].initDisk && !$('#disCheck'+escapeJquery(selectNodes[i].detail[j].dev_id) + escapeJquery(selectNodes[i].detail[j].vol_uuid)).parent().hasClass('checked')){
					oneInfo.os_config.disk_des.push(selectNodes[i].detail[j].display_name);
					oneInfo.os_config.disk_list.push(selectNodes[i].detail[j].vol_uuid);

					var values = {};
					values.volume_uuid = selectNodes[i].detail[j].vol_uuid;
					values.volume_des = selectNodes[i].detail[j].display_name;
					oneInfo.os_config.disk_value.push(values);
				}
			}
			data.srcInfo.backup_oss_info.push(oneInfo);
			//这里要判断是否把可勾选的数据全排除了  则要对details的数据再处理一次 排除掉默认勾选的分区
			var tmpDetails = selectNodes[i].detail;
			var tmpDetailsTmp = [];
			for(var x = 0; x<tmpDetails.length;x++){
				if(tmpDetails[x].rd_flag != 1){
					tmpDetailsTmp.push(tmpDetails[x]);
				}
			}
			//判断是否全取消了
			if(oneInfo.os_config.disk_list.length == tmpDetailsTmp.length && tmpDetailsTmp.length !=0){
				UIToastr.showWarning(LANG.UI_OS_BACKUP_ALREADY_CHOOSE_HOST, LANG.UI_OS_BACKUP_ALREADY_CHOOSE_HOST_ERROR1);
				return false;
			}

		}
//		initStrateyDes(); //初始化策略描述
		//初始化计时器
		initServerTime();
		desOfOsShow(data.srcInfo.backup_oss_info);
		getOsCurrentUseLicense(data.srcInfo.backup_oss_info.length).then(showTitleCallback);
        return false;
	}

	//组合备份源信息用于确认配置 这里先不用表格形式  所以先隐藏 但是不用先删  后面可能用到
	//	"backup_oss_info": [{							//选择的各个虚拟机信息
	//	"agent_uuid": "xxxxxx",
    //	"agent_name": "xxxxxx",
	//	"os_config": {
	//		"disk_list": ["xxx", "xxx"];
	//	    "disk_des": ["xxx", "xxx"];
	//	},
	//	"dir_path": "xxxxxx"
	//}]
	var desOfOsShow1 = function(data){
		var des = '';
		var desHead = '<tr><td>'+LANG.UI_AGENT+'</td><td>'+LANG.UI_OS_PLUG_VOL_NAME+'</td><td>'+LANG.UI_AGENT_EXCLUDE+'</td></tr>';
		//先获取添加代理数量以及内容
		var listCount = data.length;
		var desContent = '';
		for(var i = 0;i<listCount;i++){
			//得到排除的列表
			var disk_list = data[i].os_config.disk_list;
			//得到排除的列表描述
			var disk_des = data[i].os_config.disk_des;
			//判断是否有排除的磁盘
			if(disk_list.length == 0){ //未排除的情况
				desContent +='<tr>';
				desContent += '<td rowspan="1">'+ data[i].agent_name+'</td><td></td><td></td>';
				desContent += '</tr>';
			}else{  //有排除的情况
				//获取disl_list的长度
				var diskListCount = disk_list.length;
				//判断长度是否大于1
				if(diskListCount == 1){  //只有一个的情况
					desContent +='<tr>';
					desContent += '<td rowspan="1">'+data[0].agent_name+'</td>';
					desContent += '<td>'+disk_des[0]+'</td>';
					desContent += '<td>'+LANG.UI_OS_EXCLUDED+'</td>'
					desContent += '</tr>';
				}else{  //有多个排除的情况
					desContent +='<tr>';
					desContent += '<td rowspan="'+diskListCount+'">'+data[0].agent_name+'</td>';
					desContent += '<td>'+disk_des[0]+'</td>';
					desContent += '<td>'+LANG.UI_OS_EXCLUDED+'</td>'
					desContent += '</tr>';
					for(var j=1; j<diskListCount;j++){
						desContent += '<tr>';
						desContent += '<td>'+disk_des[j]+'</td>';
						desContent += '<td>'+LANG.UI_OS_EXCLUDED+'</td>'
						desContent += '</tr>';
					}
				}
			}
		}
		des = desHead+desContent;
		$(".osshow").html(des);
	}


	//最后一步的备份源显示 不以表格显示  以一行一行的形式显示
	var desOfOsShow = function(data){
		var des = '';
		//先获取添加代理数量以及内容
		var listCount = data.length;
		for(var i = 0;i<listCount;i++){
			//得到排除的列表
			var disk_list = data[i].os_config.disk_list;
			//得到排除的列表描述
			var disk_des = data[i].os_config.disk_des;
			des += data[i].agent_name+'<br>';
			//判断是否有排除的磁盘
			if(disk_list.length != 0){ //未排除的情况
				//获取disl_list的长度
				var diskListCount = disk_list.length;
				for(var j=0; j<diskListCount;j++){
					des += LANG.UI_OS_EXCLUDE_VOL+': '+disk_des[j]+'<br>'
				}
			}
		}
		$(".osshow").html(des);
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
			$('#backupTimestrategy').strategy({dom: $('#backupTimestrategy'), config: defaultStrategy, display:['display-none', 'display-none', 'display-none'], backup_flag: 1});
		});
	}





	//得到表格的配置
	var getTableDefaultsOpt = function(){
		var defaultsOpt = {
			    "searching": false,
			    "ordering": false,
			    "paging":false,
			    "info":false,
			    "sScrollY":347,
//			    "showLoading":true,
			    "language": { // language settings
	                "emptyTable": LANG.UI_TOOLS_NO_DATA,
	                "zeroRecords": LANG.UI_TOOLS_NO_DATA,
	            },
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


	//后退
	var backup = function(){
		var span = $('.agentfilelistcheckall').parent();
	    $(span).prop("class", "");
        $('.agentfilelistcheckall').prop("checked", false);
		if('' == _path){
			//是否在根目录
			UIToastr.showInfo(LANG.UI_BACKUP_FILE_NO_UP_PATH_TITLE, LANG.UI_BACKUP_FILE_NO_UP_PATH_VALUE);
			return;
		}
		var arr = _path.split("/");
		var dir = '';
		if(arr.length > 2){
			for(var i=0; i<arr.length - 2; i++){
				dir += arr[i] + "/";
			}
		}

		var params = {agentuuid:_agentUUID, start:0, limit:_pageSize, filename:'', dir:dir};
		var params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'getAgentFileDir',p:params}, setAgentFileList);
		//设置当前路径
		_path = dir;
	}

	//初始化备份主机树
	var initAgentTree = function(type) {
		//传入被勾选的agent_uuid列表
		var agent_msg = SETTINGS.backup_oss_info;
		var agent_uuids = [];
		for(var i=0;i<agent_msg.length;i++){
			agent_uuids.push(agent_msg[i].agent_uuid);
		}
		var data = {};
		data.agent_uuids = agent_uuids;
		var data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:34,f:'getOsBackupTree',p:data}, setTree);
	};
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

	//初始化策略描述
	var initStrateyDes = function(){
		//  initHighStrategyDes();//初始化高级策略
		//  initHighDes();
	}

	//初始化第二步任务信息
	var initStep2Settings = function(){
		//初始化节点
		initBackupTarget();
	}

	//初始化第三步任务信息
	var initStep3Settings = function(){
		//限速策略
		speedList = SETTINGS.speedInfo;
		//线程数量
		$('#backupThreadNum').val(SETTINGS.mode.threadnum);
		//传输模式
		$('#transport_mode').val(SETTINGS.mode.transport_priority);		//传输模式
		//传输加密
		$('#transport_encrypt_flag').bootstrapSwitch('state', SETTINGS.bts.encrypt);
		// 传输加密算法
		if(SETTINGS.bts.encrypt){
			$('.transfer-encrypt-method-form').show();
		}
		if(SETTINGS.bts.encrypt_method){
			$('#transferEncryptMethod').val(SETTINGS.bts.encrypt_method);
		}
		//存储策略
		//压缩存储
		$('#compressCheck').bootstrapSwitch('state', SETTINGS.bss.compress); 
		// 压缩等级
		if(SETTINGS.bss.compress){
			$('#compressGrade').val(SETTINGS.bss.compress_method);
		}
		//数据加密
		$('#encryptStorageCheck').bootstrapSwitch('state', SETTINGS.bss.encrypt); 
		if(SETTINGS.bss.encrypt){
            $('.storage-encrypt-div').show();
		}
		$('#storageEncryptMethod').val(SETTINGS.bss.encrypt_method);
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
		}else {
			$('.passwordDiv').hide();
		}
		//得到有效数据
		$('#valid_data_flag').bootstrapSwitch('state', SETTINGS.mode.valid_data_flag);
		//得到静默快照
		$('#silent_snapshot').bootstrapSwitch('state', SETTINGS.mode.silent_snapshot_flag);
		//得到CBT
		$('#cbt_flag').bootstrapSwitch('state', SETTINGS.mode.cbt_flag);
		//得到快照
		$('#snapshot_flag').bootstrapSwitch('state', SETTINGS.mode.snapshot_flag);
		//重试策略
        $('#retry_config').retryStrategy({'retry_strategy': SETTINGS.retry_strategy},'edit');
		//过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').bootstrapSwitch('state', SETTINGS.ignore_resource_limiting_flag);
		//任务名
		$('#jobname').val(SETTINGS.taskname);
		//任务UUID
		data.taskuuid = SETTINGS.taskuuid;

	}

	//初始化历史任务信息
	var initOldSettings = function(){
		var data = {};
		data.taskuuid = $('#task_uuid').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:34,f:'getBackupTaskAllInfo',p:data}, function(d){
			SETTINGS = JSON.parse(d);
			initAgentTree();
			initData();
			initStrategySelect(); //初始化策略选择
//			initStep1Settings();
			initStep2Settings();
			initStep3Settings();
			// initStrateyDes();
			initSpinner();
		});
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
				// 插入所有可用策略组
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
        pAjaxRequest({type: 6}, '/api/v1/strategies/select', "GET", initStrategyList, true);
	}

	// 应用全局策略并初始策略信息
	var strategyHandler = function(){
		editFlag = false;
		var index = $('#strategySelect option:selected').val();
		var strategy = globalStrategy[index];
		// 初始化全局策略数据
		if(cloudTargetFlag){
			$('#tab_common').backupStrategy(CONF.MODULE_TYPE.OS, true, strategy, 'cloud');
		}else{
			$('#tab_common').backupStrategy(CONF.MODULE_TYPE.OS, true, strategy, 0);
		}
		// 限速策略
		if(index == 0) return false;
		// 初始化存储加密密码
		_oldPassword = strategy?.store?.storeInfo?.password ?? '';
        var speedInfo = strategy.speedlimit.speedInfo;
        var check = strategy.speedlimit.check;
        if(!check || !speedInfo) return ;
		speedList = [];
        for(var i=0;i<speedInfo.length;i++){
			speedList.push(speedInfo[i]);
		}
	}

	var addSpeedList = function(list){
        var des = "";
        var uuid = list.uuid;
		des += '<li style="margin-top:15px;" class="list-group-item popovers speedTips" id="speed'+ uuid +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ list.des +
				'"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 94%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + list.des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:10px;"><a class="del'+ uuid +'" >'
				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
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

	//获取显示传输网络标志
	var getNetworkFlag = function(agent){
		var treeObj = $.fn.zTree.getZTreeObj('os_tree');
		var agent = treeObj.getCheckedNodes(); //获取所有勾选的集合
		let flag = false;
		for(var i=0;i<agent.length;i++){
			//客户端连接服务端
			if(agent[i].net_model == 2){
				flag = true;
				break;
			}
		}
		let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		if (false === backupTargetInfo || !backupTargetInfo.node_uuid) {
			flag = false;
		}
		return flag;
	}

	/**
	 * 初始化备份目的地
	 */
	const initBackupTarget = () => {
		$('#backupTarget').backupTarget({
			node_uuid: SETTINGS.node.nodeuuid,
			node_pool_uuid: SETTINGS.node.node_pool_uuid,
			storage_uuid: SETTINGS.node.storageuuid,
			storage_pool_uuid: SETTINGS.node.storage_pool_uuid,
			storage_pool_type: SETTINGS.node.storage_pool_type,
		});
	};
	var getOsCurrentUseLicense = (currentUse) => {
        return new Promise((resolve) => {
            let params = {
                module: 'os',
                currentUse,
                showMetronic: true,
                judge: true,
                showAlertMsg: true,
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
//        	initAgentTree();//初始化主机代理
        	initListener();
        	inintDatatimePicker();
//        	initData();
        	initSpinner();  //初始化数字器
        	// initStrategy();       //初始化时间策略
//			initNodeSelect();	  //初始化目标节点选择
			initOldSettings();
			initTaskCrowd();
			// initStrategySelect();
        },

    };

}();

jQuery(document).ready(function() {
	OsBackup.init();
});