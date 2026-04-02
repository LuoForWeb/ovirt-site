var FileBackup = function () {
	var data = {srcInfo:{},backupInfo:{},highInfo:{}};
	var zTree;
	var zTreeFile = [];
	//用于STEP1:代理端UUID,
	var _pageSize = 40; //代理端文件列表每次显示条数;
	var _path = '';		 //当前路径
	var searchFlag = false;
	var _timeStamp = '';	//时钟时间戳,全局
	var nodeSelectFlag = false; //自定义节点选择加载标志
	var initSpeedFlag = false;
	var speedList = [];
	var oldNode, networkFlag = false;	//用于比对加载传输网络的节点
	var initStrategyFlag = false;
	var globalStrategy = [];
	var editFlag = false;
	var defaultStrategy = [];
	var initErrorFlag = false;
	var nodeParamList;//用于保存搜索agent的结果
	var _oldPassword = '';
	var passwordChangeFlag = false; // 是否改变了密码框内容标记
	let backupTargetInfo = '';
	var initData = function(){
		//setp1
		data.srcInfo.fileInfo = [];
		// data.srcInfo.agentUUID = '';
		data.srcInfo.agentList = [];
		data.srcInfo.groupList = [];
		//setp2
		//备份方式:策略/时间
		data.backupInfo.type = 'strategy';
		//完备/增备/差备
		data.backupInfo.fullInfo = {};
		data.backupInfo.incrInfo = {};
		data.backupInfo.diffInfo = {};
		//按时间备份的时间
		data.backupInfo.datetime = null;
		//setp3
		//保留策略
		data.highInfo.reserve = {};
		data.highInfo.reserve.type = 1;
		data.highInfo.transfer = {};
		data.highInfo.store = {};
		data.highInfo.node = {};
		data.highInfo.newstr = {wildcard_list:[]};
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.FS, false);
	}
	
	var initListener = function(){
		$('#toAdd').on('click',function(){
	    	LOCATION('./content/client/client.php', 'client');
		});
		//添加客户端
		$('#allFileTree').on('click','button.addInput',wildInputAdd);//添加通配符输入框
		$('#allFileTree').on('click','.delInput',delInput);
		$('#searchAgent').on('propertychange', debounceFs).on('input', debounceFs);
		$('#allFileTree').on('change','.wildcardmode', wildcardmodeTypeHandler);
		//选择备份策略复选框
		$('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);
		$('#allFileTree').on('click','input.apply-to-all-client',applyToAllClient);
		//初始化存储策略配置监听
		initStoreListeners();
		//初始化保留策略配置监听
		initReserveListeners();
		//扫描文件
		$("#scanFileNum").on('change', function(){
            initHighStrategyDes();
		});
		//归档开启--保留策略默认永久
		$('#archivecheck').on('switchChange.bootstrapSwitch', archiveChange);
		//跳过文件告警智能判断
		$('#passfilealarmcheck').on('switchChange.bootstrapSwitch', passAlarmChange);
        // 压缩传输
        $('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);
		//数据加密密码确认
		$('#repassword').on('input propertychange', function(){
			checkPassword();
		});
		//数据加密密码输入
		$('#password').on('input propertychange', function(){
			checkPassword();
		});
		// 传输策略---加密传输
        $('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptChange);
		$('#backuptype').on('change', backupTypeHandler);
		//初始化重试策略
		$('#retry_config').retryStrategy();
		// 保留策略 - 备份数据保留类型change
        $('#reserveMode').on('change', () => {
            initReserveStrategyDes();
        });
		$('#spinnerNum').on('input propertychange', initReserveStrategyDes);
		$('#spinnerDay').on('input propertychange', initReserveStrategyDes);
		$('#fullBackup').on('ifChecked ifUnchecked ',specialHandler);
	}

	var backupTypeHandler = function () {
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
	var archiveChange = function () {
		var flag = $('#archivecheck').get(0).checked;
		if(flag) {
			data.highInfo.reserve.type = 3;
			//归档开启二次确认+输入密码，下面显示红色提示
				bootbox.confirm({
					title: LANG.UI_NAS_IF_OPEN_ARCHIVE,
					message: LANG.UI_NAS_IF_OPEN_ARCHIVE_TIPS,
					callback: debounce(function(r) {
						if(!r) {
							$('#archivecheck').bootstrapSwitch('state', false);  //归档关闭
							return true;
						} 
						initErrorFlag = false;
						$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
							var data = JSON.parse(d);
							var userPassword = data.password;
							bootbox.prompt({ 
								title: LANG.UI_NAS_OPEN_ARCHIVE_CONFIRM_TIPS,
								inputType: 'password',
								callback: function (result) {
									if(result == null) {
										$('#archivecheck').bootstrapSwitch('state', false);  //归档关闭
										initReserveStrategyDes();
										return true;
									} 
									if(hex_md5(result) == userPassword){
										//密码正确
										$('#archivecheck').bootstrapSwitch('state', true);  //归档开启
										$(".archivedes").show();
										// 默认保留策略为永久保留
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
				        				return true;
									}else{
										$(".archivedes").hide();
										$('#reserveType').removeAttr("disabled");
										$('#reserveType').val(1);
										$('.reserveNum').show();
										$('#reserveType option[value="3"]').hide();
										$('.reservetip2').hide();
										$('.reservetip1').show();
										$('.archiveselectdiv').hide();//归档目标
										//启用增备和差异
										$('#incrBackup').iCheck('enable');
										$('#diffBackup').iCheck('enable');
										$('#pincrBackup').iCheck('enable');
										$('.bootbox-input').css('border-color', "#a94442");
										if(!initErrorFlag){
											var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';
											$('.bootbox-input').after(des);
											initErrorFlag = true;
										}
										initReserveStrategyDes();
										return false;
									}
								}
							});
						});
					},300)
				});
		}else {
			data.highInfo.reserve.type = 1;
			$(".archivedes").hide();
			$('#reserveType').removeAttr("disabled");
			$('#reserveType').val(1);
			$('.reserveNum').show();
			$('#reserveType option[value="3"]').hide();
			$('.reservetip2').hide();
			$('.reservetip1').show();
			$('.archiveselectdiv').hide();//归档目标
			//启用增备和差异
			$('#incrBackup').iCheck('enable');
			$('#diffBackup').iCheck('enable');
			$('#pincrBackup').iCheck('enable');
			initReserveStrategyDes();
			return true;
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
        // 存储加密算法
        // $('#storageEncryptMethod').on("change", function () {
        //     initStoreStrategyDes();
        // });
    }
	var initReserveListeners = function(){
        //复写保留类型切换监听
		$('#reserveType').on('change', reserveTypeHandler);
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
	
	var checkSimpleForever = function(mode){
        for(var i=0;i<speedList.length;i++){
            if(mode == speedList[i].mode){
                return false;
            }
        }

        return true;
	}
	//切换自动选择存储加密密码
	var passwordModeChange = function(){
		if(this.checked){
			$('#password').val('');
			$('#repassword').val('');
			$('.passwordDiv').hide();
		}else{
			$('.passwordDiv').show();
		}
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
							'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' + 
						'</div>' + 
					'</div>' + 
				'</div>' + 
				'<div class="col2 pull-right delete-list">' + 
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
			'<li class="list-group-item popovers speedTips input-sm" id="speed'+ liId+'"  data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' +info.des + '">' + 
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
		$('#backupTarget').backupTarget();
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
		}else if(CONF.RESERVE_TYPE.DAY == this.value){
			$('.reserveNum').hide();
			$('.reserveDay').show();
		}else if(CONF.RESERVE_TYPE.PERMANENT == this.value){
			$('.reserveNum').hide();
			$('.reserveDay').hide();
		}
		data.highInfo.reserve.type = this.value;
		//修改保留策略信息
		initReserveStrategyDes();
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
		// $(this).remove();
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
	
	var step1Valid = function(showTitleCallback){
		//未选择代理返回
		var nodes = zTree.getCheckedNodes(true);
		data.srcInfo.fileInfo = [];
		data.highInfo.newstr.wildcard_list = [];
		var checkChildFileArr = [];
		var checkoutFlag = true;
		var filecheck = false;
		//是否需要显示传输网络标记
		networkFlag = false;
		nodes.forEach(item => {
			//所有选中的文件（未过滤）
			if(item.eventtype == "agent" && zTreeFile[item.uuid]!=undefined && zTreeFile[item.uuid].getCheckedNodes(true).length !=0) {
				data.srcInfo.fileInfo.push(zTreeFile[item.uuid].getCheckedNodes(true));
				//用于检测应用到其他客户端  在其他客户端一个文件都未找到时，只选中父级的情况
				checkChildFileArr.push(zTreeFile[item.uuid].getCheckedNodes(true));
			}else if(item.eventtype == "agent" && zTreeFile.length==0 || (zTreeFile[item.uuid]!=undefined && zTreeFile[item.uuid].getCheckedNodes(true).length ==0)) {
				filecheck = true;//判断每个客户端是否都选择了文件
			}
			//通配符
			if(item.eventtype == "agent"){
				var wildcardInput = [];//所有通配符
				if($('#high_tabagent_tree_'+item.uuid).find('span.wildcardInput').length==0 && $('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val() != 0 && $('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val() != undefined) {
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
					wildcardInput.push(str); 
				}
				data.highInfo.newstr.wildcard_list.push([item.uuid,wildcardInput,$('#high_tabagent_tree_'+item.uuid).find('.wildcardmode').val()]);
			}
		});
		if(!filecheck) {
			filecheck = checkChildFile(checkChildFileArr);
		}
		if(!checkoutFlag) return false; 
		if(nodes.length == 0 || data.srcInfo.agentList.length == 0){
			UIToastr.showWarning(LANG.UI_DB_NO_CHOOSE_BACKUP_PROXY, LANG.UI_DB_CHOOSE_BACKUP_PROXY_FIRST);
			return false;
		}
		if(filecheck){//检查是否每个已选中客户端都选中了文件
			UIToastr.showWarning(LANG.UI_BACKUP_FILE_NO_SELECT_TITLE1, LANG.UI_BACKUP_FILE_NO_SELECT_VALUE1);
			return false;
		}
		//初始化时间策略模块
		Strategy.initModule(FileBackup);
		//初始化策略描述
		initStrateyDes();
		showStep1();
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
	//初始化策略描述
	var initStrateyDes = function(){
		initStoreStrategyDes();
		initReserveStrategyDes();
		initHighStrategyDes();//初始化高级策略
	}
	var showStep1 = function(){
		//初始化计时器
		initServerTime();
		var nodes = zTree.getCheckedNodes(true);
		var agentInfo = '';
		var showStr = '';
		var showFileArr = [];
		var backupmode4Info = '';
		var eachwildcard2 = '';
		var allCheckedNode = [];
		//任务名
		if($.trim($('#jobname').val()) == "") {
			$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'getFileBackupTaskName',p:{}}, function(data){
				$('#jobname').val(data);
			});
		}
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
		})

		nodes.forEach(item1=> {
			//设置代理端
			if(item1.eventtype == "group") {
				// agentInfo +=  item1.name + ':' + '<br>';
				// item1.children.forEach(item2=> {
				// 	if(item2.checked) {
				// 		agentInfo +=  item2.name + ';' + '<br>';
				// 	}
				// });
			}else {
			//设置文件列表显示
				showStr +='<strong>'+ item1.name+ ':' + '</strong><br>';
				showFileArr.forEach(eachpath=> {
					if(item1.uuid==eachpath[1]) {
						showStr += eachpath[0]+ ';' + '<br>';

					}
				});
			//设置通配符显示
			backupmode4Info += '<strong>'+ item1.name+ ':' + '</strong><br>';
			data.highInfo.newstr.wildcard_list.forEach(eachwildcard=> {
				if(eachwildcard[0]==item1.uuid) {
					if(eachwildcard[1]!="") {
						backupmode4Info += LANG.UI_FILE_WILDCARD +'：'+ eachwildcard[1]+';' + '<br>';
					}
					if(eachwildcard[2]==0) {
						eachwildcard2 = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
					}else if(eachwildcard[2]==1) {
						eachwildcard2 = LANG.UI_FILE_WILDCARD_BAK_FILTER;
					}else {
						eachwildcard2 =LANG.UI_FILE_WILDCARD_BAK_SELECT;
					}
					backupmode4Info += LANG.UI_FILE_WILDCARD_BAK_MODE +'：'+ eachwildcard2 +';' + '<br>';
				}
				

			});
				
			}
		});
		
		
		$('.backupmodeshow4').html($('.wildcardlabel').html()+':');
		$('.backupmodeshow4list').html(backupmode4Info);
		// $('.agentshow').html(agentInfo);
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
		//判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
		getTapeStrategy(backupTargetInfo.storage_uuid, backupTargetInfo.storage_type, '.threadDiv', '.backupThreadDiv', '#tab_common', CONF.MODULE_TYPE.FS);
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
		}
		initHighStrategyDes();
		initWormConfig();
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
            }else {
                $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal);
            }
        } 
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
		var result = getReserveStr() & getAchiveStr() & getTransferStr() & getSpeedStr() & getStoreStr() & getSafeStr();
		if(result){
			var strategyMode = $('#strategymode').find('input:checked');
			return showStep3(strategyMode);
		}
		return result;
	}
	
	var showStep3 = function(strategyMode){
		//传输策略
		let transDes = '';
		transDes = LANG.UI_COPY_BACK_ENCRYPT + ': ' + getSwitchDes(data.highInfo.transfer.encrypt) + '<br>';
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
            transDes += encryptedMethodLabel + ": " + grade + "<br>";
		}
		$('.transferinfoshow').html(transDes);
		//节点传输网络信息显示
		if(networkFlag){
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
			$('.applianceshow').html($('.transfernetworklabel').html() + ": " + networkNode.str + '<br>');
		}
		//高级策略-快照、线程数量、通配符
		data.highInfo.newstr.silentsnapshotcheck = $('#silentsnapshotcheck').get(0).checked;//快照
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
		var backupmode1Info = $('.silentsnapshotlabel').html() + ": " + getSwitchDes(data.highInfo.newstr.silentsnapshotcheck);
		data.highInfo.store.compress = $('#compressCheck').get(0).checked;//压缩
        data.highInfo.store.compress_method = 0;
		// 压缩等级
        if($('#compressCheck').get(0).checked){
            data.highInfo.store.compress_method = parseInt($('#compressGrade').val());
        }
		data.highInfo.store.dataencrypt = $('#encryptStorageCheck').get(0).checked; //数据加密
		//存储策略
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
		storeInfo += "<br>" + $('.encryptStorageLabel').html() + ": " + getSwitchDes(data.highInfo.store.dataencrypt) ;

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
		//归档  归档目标选择文件3  选择文件和目录1  关闭是2
		if(Object.keys($('#archivecheck')).length != 0 && $('#archivecheck').get(0).checked) {
			data.highInfo.file_archive = parseInt($("#archiveSelect").val());
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
		}else {
			data.highInfo.file_archive = 2
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
		//时间策略
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
		if(Object.keys($('#archivecheck')).length != 0) {
			var archiveshowInfo = $('.archivelabel').html() + ": " + getSwitchDes($('#archivecheck').get(0).checked) ;
			var archiveselectInfo = $('.archiveselectdivlabel').html() + ": " + $("#archiveSelect").find("option:selected").text() ;
			$('.archiveshow').html(archiveshowInfo);//归档
			if($('#archivecheck').get(0).checked) {
				$('.archiveselectshow').show();
				$('.archiveselectshow').html(archiveselectInfo);//归档目标
			}
		}
		backuptypeshow.html(backuptypeshowStr);
		backuptypeinfoshow.html(backuptypeinfoshowStr);
		$('.backupmodeshow1').html(backupmode1Info);
		// $('.backupmodeshow2').html(backupmode2Info);

		if(data.highInfo.node.storage_type != CONF.BD_STORAGE_TYPE.TAPE) {
			$('.backupmodeshow3').show();
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
			if (data.highInfo.file_archive == 2) {
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
	

	//得到时间策略
	var getTimeStr =function () {
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		data.backupInfo.fullInfo = {};
		data.backupInfo.incrInfo = {};
		data.backupInfo.diffInfo = {};
		data.backupInfo.type = $('#backuptype').val();
		var strategyMode = $('#strategymode').find('input:checked');
		if("strategy" == data.backupInfo.type){
			if(0 == strategyMode.length){
				//没有选择时间策略
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_FILE_BACKUP_SET_STRATEGY_TIPS);
				return false;
			} {
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
				// showStep3(strategyMode);
				return true;
			}
		}else if("oncetime" == data.backupInfo.type){
			var onceTime = $('#oncetime').val();
			if("" != onceTime){
				$('.settimetip').hide();
				data.backupInfo.datetime = onceTime;
				// showStep3(strategyMode);
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
	//得到保留策略
	var getReserveStr = function(){
		data.highInfo.reserve.type = $('#reserveType').val();
		data.highInfo.reserve.strategyMode = $('#reserveMode').val();
		if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerDayInput').val();
		}else if(CONF.RESERVE_TYPE.PERMANENT == data.highInfo.reserve.type){
			data.highInfo.reserve.value = '';
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
		data.highInfo.transfer.encrypt = $('#transport_encrypt_flag').get(0).checked; //得到传输加密
		// 传输加密算法
		data.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
		data.srcInfo.transport_priority = $('#transport_mode').val(); //得到传输加密
        data.highInfo.transfer.network = '';
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
		// 非法字符串校验
		if (!isNotLatinCode($.trim($('#password').val()))) {
			return false;
		}
		data.highInfo.store.dataencrypt = $('#encryptStorageCheck').get(0).checked; //数据加密
		data.highInfo.store.valid = true;
		data.highInfo.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;//自动生成密码开关
		data.highInfo.store.password = btoa(getPassword());

		var repassword = $.trim($('#repassword').val());
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

	// 得到存储加密密码
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
			// 未应用备份策略且未触发过密码输入框的change事件,则当前输入框的密码
			return now_password;
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
	
	//判断字符传知否在Latin1字符集中
	function isNotLatinCode(string) {
		var latin1Regex = /[^\x00-\xFF]/;
		if(latin1Regex.test(string)){
			UIToastr.showWarning(LANG.UI_DB_BACKUP_PASSWORD_ILLEAGLE, LANG.UI_DB_BACKUP_PASSWORD_TIPS);
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
                		if(step1Valid(
							() => {
								$('a[href="#tab2"]').tab('show'); // 手动切换步骤页面
								handleTitle(tab, navigation, index);
								}
						) == false){
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
                $('#filebackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#filebackupcontent').find('.button-previous').css('visibility', 'hidden');
        $('#filebackupcontent .button-submit').click(() => {
            getFsCurrentUseLicense().then(submit);
        }).css('visibility', 'hidden');
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
		data.taskName = $.trim($("#jobname").val());
		data.strategygroupuuid = $('#strategySelect option:selected').val();
		//TODO提交
    	var jsonData = JSON.stringify(data);
    	Metronic.blockUI({target: '#filebackupcontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'createFsBackupJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#filebackupcontent');
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}

	var initTree = function(type) {
		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'getAgentGroupBackupTree',p:{}}, setTree);
	};
	//代理端树
	var setTree = function(zNodes){
		if(zNodes == "[]"){
			$("#noagent").show();
			$(".vcenter-tree").hide();
			if(searchFlag) {
				$('#nosearchtips').show();
				$("#noagent").hide();
			}
			return;
		}else{
			$("#noagent").hide();
			$(".vcenter-tree").show();
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
						enable: true,
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
		zTree = $.fn.zTree.init($("#agent_tree"), setting, JSON.parse(zNodes));
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
		checkAgentOnlineTips(treeNode);
		nodeExpand(treeId, treeNode);
	}
	//点击代理端节点
	var nodeClick = function(treeId, treeNode){
		checkAgentOnlineTips(treeNode);
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
			if(treeNode.chkDisabled)return;
			// 取消勾选
			if(zTree.getCheckedNodes(true).length != 0) {
					var groupuuid = "";
					var allpid = [];
					//删除取消选中的agentuuid
					if(treeNode.eventtype == "group"&&treeNode.agentlist && treeNode.children) {
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
										'<li id="commontab'+ treeNode.uuid +'"class="active nav-item">' + 
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
		$('.popovers').popover({
			html:true
		});
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
		var _path = treeNode.path;
		if(applyFlag) {
			var params = {agentuuid:treeNode.uuid, start:0, limit:_pageSize, filename:'', dir:_path,pid:0,groupuuid:groupuuid,
			editFlag: true, taskuuid: $('#task_uuid').val(),applyPathList:applyPathList};
		}else {
			var params = {agentuuid:treeNode.uuid, start:0, limit:_pageSize, filename:'', dir:_path,pid:0,groupuuid:groupuuid};
		}
		var params = JSON.stringify(params);
		var div = "#" + groupuuid + '_' + treeNode.uuid;
		Metronic.blockUI({target: div,animate: true,cenrerY: true});
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
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
		if(data.srcInfo.groupList.indexOf(groupuuid) == -1) {
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
				// onCheck: fileNodeCheck,
				beforeExpand: fileNodeExpand
			},
			view: {
				dblClickExpand: false
			}
		};
		var agent_uuid =  (data['fileNodes']==undefined? "":data['fileNodes'][0].uuid);
		zTreeFile[agent_uuid] = $.fn.zTree.init($('#fileClientTree_' + agent_uuid), setting, data['fileNodes']);

		$('#fileClientTree li').css("background-color","white");
		
	}
	var fileNodeClick = function(treeId, pNode,clickshow){
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
		var params = {agentuuid:pNode.uuid, start:0, limit:_pageSize, filename:'', dir:pNode.filepath,pid:pNode.filepath,groupuuid:pNode.groupuuid,code_type:pNode.code_type};
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
				//应用选中文件到其他客户端
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
			setTimeout(function(){
				$('#backupTimestrategy').strategy({dom: $('#backupTimestrategy'), config: defaultStrategy, display:['display-none', 'display-none', 'display-none', 'display-none'], backup_flag: 1});
			}, 2000);
		});
	}

	//初始化安全策略 
    var initSecurityStrategy  = function(){
        //完整性校验
        $('#completeConfig').completeDetectionBackup('col-md-3', true, CONF.MODULE_TYPE.FS);
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
				if(!initStrategyFlag){
					$('#strategySelect').searchableSelect();
					$('.searchable-select-item').on('click', strategyHandler);
					initStrategyFlag = true;
				}
			}

		}
		
        pAjaxRequest({type: CONF.MODULE_TYPE.FS}, '/api/v1/strategies/select', "GET", initStrategyList, true);
	}
	
	// 应用全局策略并初始化策略信息
	var strategyHandler = function(){
		editFlag = false;
		var index = $('#strategySelect option:selected').val();
		var strategy = globalStrategy[index];
		// 初始化全局策略数据
		$('#tab_common').backupStrategy(CONF.MODULE_TYPE.FS, true, strategy, 0,function () {
			specialHandler();
		});
		if(index != 0) {
			_oldPassword = strategy?.store?.storeInfo?.password ?? '';
			var speedInfo = strategy.speedlimit.speedInfo;
			var check = strategy.speedlimit.check;
			if(!check || !speedInfo) return ;
			speedList = [];
			// 将限速策略放进消息中
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
			// $('#spinnerNum').spinner('value', 30);
			// $('#spinnerDay').spinner('value', 30);
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
            // $('#spinnerNum').spinner('value', 30);
			// $('#spinnerDay').spinner('value', 30);
        }
		data.backupInfo.type = type;
		initReserveStrategyDes();
		if(Object.keys($('#archivecheck')).length != 0 && $('#archivecheck').get(0).checked){
			// 默认保留策略为永久保留
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

	var initTimeStrategyDes = function(){
		var des = "";
		var diffDes = "";
		//备份
		var backupType = $('#backuptype').val();
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		if(!strategyConfig.fullInfo) return;
		var strategyMode = $('#strategymode').find('input:checked');
		if('strategy' == backupType){
			for(var i=0; i<strategyMode.length; i++){
				if(1 == $(strategyMode[i]).data('mode')){
					des += strategyConfig.fullInfo.des + ". ";
					diffDes += strategyConfig.fullInfo.des + "<br>";
				}else if(2 == $(strategyMode[i]).data('mode')){
					des += strategyConfig.incrInfo.des + ". ";
					diffDes += strategyConfig.incrInfo.des + "<br>";
				}else if(3 == $(strategyMode[i]).data('mode')){
					des += strategyConfig.diffInfo.des + ". ";
					diffDes += strategyConfig.diffInfo.des + "<br>";
				}
			}
		}else if('oncetime' == backupType){
			des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
			diffDes += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].time.des;
			initStrategyDesStyle($('.backupTimeDes'), diffDes, oldDes);
		}else{
			$('.backupTimeDes').removeClass('font-green-seagreen');
		}
		$('.backupTimeDes').html(des);
		$('.backupTimeDes').prop('title', des);
	}

    var initSpeedStrategyDes = function(){
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
	

	var initStoreStrategyDes = function(){
		var des = "";
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
	//初始化节点传输网络列表
	var initNetworkList = function(nodeUuid){
        if (oldNode === nodeUuid) {
            return;
        }
        $('#transferNetworkTree').transferNetwork({node_uuid: nodeUuid});
        oldNode = nodeUuid;
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
        	initTree();
        	inintDatatimePicker();
        	initData();
        	initSpinner();
			initStrategy();       //初始化时间策略
			// initNodeSelect();	  //初始化目标节点选择
			initBackupTarget(); // 创建的初始化
			initListener();
			initStrategySelect(); //初始化策略选择
			initSecurityStrategy();  //初始化安全策略
        },
        
    };

}();

jQuery(document).ready(function() {   
	FileBackup.init();
});