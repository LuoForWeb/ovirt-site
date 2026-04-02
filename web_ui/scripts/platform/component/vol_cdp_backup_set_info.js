var volCdpBackupSetTimeLineInfo = function (){
    var initData = {};  // 初始化编辑数据

    var taskUuid = "";  //任务uuid
    var agentUuid = "";  //数据源客户端uuid
    var createTaskType = "";  //创建任务类型
    var nodeUuid = "";
	var selectedBackupSetId = "";  //选中的备份集ID
    var rawData = [];  //chart 数据集
    var _hisPortletBodyWidth = 0;
    var _hisportletBodyHeight = 0;
    var _timepointType = 1; //时间点类型，0：无效时间点，1：任意时间点；2：标签点；3：事件点；4：标签点和事件点
    var _eventNavTabsType = 1;  //时间点类型tab  1：任意时间点；2：标签点；3：事件点;4:验证点
    var _timeInterval = 2;  //时间轴默认加载的间隔 默认最小加载1分钟
    var _chartStartPercent = 70; //dataZoom的起始百分比,此处暂定70%,根据实际效果调整.(可以根据加载的时间区间范围确定起始百分比)
	var _chartEndPercent = 100;  //dataZoom的结束百分比,此处暂定100%,根据实际效果调整.
    var table = $('#taskLog');
	var _verifyTimepointVolInfo = [];
	var _backupSetId = 0; //选中备份集ID
	var _timepointIsValid = false; //时间点是否有效
	var _systemBootType = 0; // 0-引导方式未知 1-bios引导 2-efi引导
    var _agentOsType= "";
	var _virusScanStatus = 0;   //0 – 未扫描 1 – 扫描中 2 – 健康 3 – 感染
	var _checkedDataInfo = [];
    // 枚举定义 -暂时未使用
    const CONFIG_TABLE_TYPE_ENUM = {
        anytime: 1,  // 任意时间点
        tagpoint: 2,  // 标签点
        eventpoint: 3, //事件点
        verifypoint:4, //验证点
    };


    /**
	 * 渲染时间轴chart
	 */
    var loadTimelineChart = function(rawData){
        // 动态设置echart图高度和宽度以适应不同分辨率
		var portletBodyWidth = $('#volCdpbackupSetTabPortletBody').width();
		var portletBodyHeight = $('#volCdpbackupSetTabPortletBody').height();

		if(_hisPortletBodyWidth != 0){  //非首次加载，chart坐标取历史配置
			portletBodyWidth = _hisPortletBodyWidth;
			portletBodyHeight = _hisportletBodyHeight;
		}else{  //首次加载获取当前body尺寸，赋值历史配置
			portletBodyWidth = $('#volCdpbackupSetTabPortletBody').width();
			portletBodyHeight = $('#volCdpbackupSetTabPortletBody').height()-50;
			_hisPortletBodyWidth = portletBodyWidth;
			_hisportletBodyHeight = portletBodyHeight;
            
		}
		if(rawData.length == 0){
			echarts.init(document.getElementById('volCdpBackupSetTimeline')).dispose(); //销毁chart
			return;
		}
        var lastTimePoint = rawData[rawData.length - 1][0];
		$('.backupsettimepointview')
			.data('datetimepicker')
			.setDate(moment(lastTimePoint, "YYYY-MM-DD HH:mm:ss").toDate());
        verifyTimepointisValid(lastTimePoint);
        
        $('#volCdpBackupSetTimeline').css({"width": portletBodyWidth + 'px', "height": (portletBodyHeight) + 'px'});
        echarts.init(document.getElementById('volCdpBackupSetTimeline')).dispose(); //销毁chart
		var chartDom = document.getElementById('volCdpBackupSetTimeline');
		myChart = echarts.init(chartDom);
		var dates = rawData.map(function (item) {
			var xDataInfo = item[0];
			return item[0];
		});
		var data = rawData.map(function (item) {
			return [item[0],item[1],item[2],item[3]];
		});
		var size = [];
		var lablePointNumber = 0;  //标签点个数
        for (var i=0;i<rawData.length;i++){
			if(rawData[i][2]!=0){
				size.push(7);  	//圆点大小
				lablePointNumber = rawData[i][2];
			}else{
				size.push(0);
			}
		}
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
				splitNumber: 6,
                data: dates,
                // axisLine: { lineStyle: { color: '#8e8e8e' } }
				axisLabel: {
                        show: true,
                        // interval: 12,
                        color: '#86909C'
				},
				axisLine: {
					show: true,
					lineStyle: {
						color: '#C9CDD4',
					}
				},
            },
            yAxis: {
                // scale: true,
                type : 'value',
                axisLine: { lineStyle: { color: '#8e8e8e' } },
                splitLine: { 
					show: true,
					lineStyle: {
                        type: 'dashed',
                    },
				},
				axisLine: {
                    show: false
                },
				axisTick: {
                   show: false // Hide y-axis ticks
                },
                axisLabel : {
                    formatter: function(value, index){
                        var valueStr = flowChartUnitStr(value);
                        return valueStr;
                    },
					color: '#86909C',
                },
                name: LANG.UI_VOL_CDP_RECOVER_DATA_FLOW,
                nameTextStyle: {  
                    padding: [0, 0, 0, -50] // 设置名称文本的内边距，这里用来调整位置  
                }  
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
            color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? ['#2A87C8']:['#44b6ae']
        };
        myChart.setOption(option);
        $('#volCdpBackupSetTimeline').css({"padding-top":"20px","z-index":"9998","position":"relative"});
        myChart.getZr().on("click", params => {	// 获取点击位置
			_timepointType = 0;
			const pointInPixel = [params.offsetX, params.offsetY];
			if (myChart.containPixel("grid", pointInPixel)) {	// 获取点击位置的坐标系[x，y]
				const xIndex = myChart.convertFromPixel({seriesIndex: 0}, [params.offsetX, params.offsetY])[0];
				this.menJinTableIndex = xIndex;
				var checkTime = rawData[xIndex][0];
				$('.backupsettimepointview')
					.data('datetimepicker')
					.setDate(moment(checkTime, "YYYY-MM-DD HH:mm:ss").toDate());
				_eventNavTabsType = 1;  //时间点类型tab  1：任意时间点；2：标签点；3：事件点; 4:验证点
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
     * 校验时间点有效性
     */
    var verifyTimepointisValid = function(timePoint){
		let startTime = $('#volCdpBackupSetRange').find("option:selected").attr("start_time"); 
		let endTime = $('#volCdpBackupSetRange').find("option:selected").attr("en_time"); 
		let startTimeValue = new Date(startTime).getTime();
		let endTimeValue = new Date(endTime).getTime();
		let checkTimeValue = new Date(timePoint).getTime();
		_timepointIsValid = false;
		if(checkTimeValue>endTimeValue || checkTimeValue<startTimeValue){
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
			return false;
		}

		var params = {
			agent_uuid:agentUuid,
			node_uuid:nodeUuid,
			timepoint:timePoint,
			vol_uuid:'',
			task_type:createTaskType,
			task_uuid:taskUuid,
			backup_set_id : $("#volCdpBackupSetRange").find("option:selected").val(),
		};
		var time_data = [];
		Metronic.blockUI({target: '#volCdpBackupSetTimeline',animate: true});
		pAjaxRequest(params, "/api/v1/complete_machine_volcdp/backup_set/verify_timepoint_is_valid", "GET", function (result) {
			Metronic.unblockUI('#volCdpBackupSetTimeline');
            var data = result.data;
			if(!data){
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
				$('#timePointValidity').css('color', "#F3565D");
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
				return false;
			}

			var volInfo = data.time_vol_info;
			var encryptedFlag = data.encrypted_flag;  //是否是加密备份点
			var passwordAutoFlag = data.password_auto_flag;  //是否是自动加密备份点
			var timePointuuid =  data.timepoint_uuid;
			_systemBootType = data.system_boot_type;
			_agentOsType = data.os_type;
			_virusScanStatus = data.virus_scan_status;  //扫描状态
			_checkedDataInfo = data;
			isEncrypted(encryptedFlag,passwordAutoFlag,volInfo,timePointuuid);   //校验密码，填充数据

			switch(_eventNavTabsType){  //1：任意时间点；2：标签点；3：事件点；5：标签点和事件点
				case 1:
					// chartCheckLabelPointInfo();
					// chartCheckEventInfo();
					break;
				case 2:
					// chartCheckEventInfo();
					break;
				case 3:
					chartCheckLabelPointInfo();
					break;
			}
        });
		$('.backupsettimepointview')
			.data('datetimepicker')
			.setDate(moment(timePoint, "YYYY-MM-DD HH:mm:ss").toDate());
    }
    //图表上点击标签点
	var chartCheckLabelPointInfo = function(){
		// loadCheckVolTagPoint(agent_uuid,isRefreshLabel = false);
	}
	//解密加密时间点
	var lockTimepoint = function(){ 
		
		let encryptedFlag =_checkedDataInfo.encrypted_flag;
		let passwordAutoFlag =_checkedDataInfo.passwordAutoFlag;
		let volInfo = _checkedDataInfo.time_vol_info;
		let timePointuuid = _checkedDataInfo.time_point_uuid;
		isEncrypted(encryptedFlag,passwordAutoFlag,volInfo,timePointuuid);
	}

    /**
	 * 判断是否加密，加密未解密设置关键参数，不能继续执行下一步操作
	 * 加密解密后给出界面图示
	 */
	var isEncrypted = function(encryptedFlag,passwordAutoFlag,volInfo,timePointuuid){
		var allpointlist = [];
		backupSetLockStyle(); //备份集锁定样式
		$('.backupsettimecontrol').css("z-index","99999");
		$('.backupsetlockview').css("cursor","auto");
		if(encryptedFlag && !passwordAutoFlag){
			$('.backupsetlockview').css("cursor","pointer");
			$('.backupsetlockdiv').show(); 
			$('.backupsettimecontrol').css("z-index","9999");
			// $('.backupsetlockview').on('click');
			if($.inArray(timePointuuid,allpointlist)==-1) {//判断是否和之前点击过的是一条链的
				bootbox.prompt({
					title: LANG.UI_VM_INPUT_DB_ENCRY_PWD,
					inputType: 'password',
					callback: function (result) {
						//同步ajax校验密码
						if(result == null) return;
						var checkflag = true;


						var params = {};
						params.timepoint_uuid = timePointuuid;
						params.password = btoa(result);

						pAjaxRequest(params, "/api/v1/jobs/password_check", "GET", function (d) {
							if(d.success){
								allpointlist.push(timePointuuid);
								loadRestoreVol(volInfo);  //填充可供恢复的卷
								backupSetUnlockStyle();   //解锁样式
								_backupSetIsLock = 1;
								$('.backupsetlockview').css("cursor","auto");
								// $('.backupsetlockview').off('click');
							}else{
								UIToastr.showWarning(LANG.UI_MACHINE_OS_TIMEPOINT_PWD, LANG.UI_MACHINE_OS_PASSWORD_VERIFY_FAILED);
								checkflag = false;
								_backupSetIsLock = 2;
							}
						});
						if(!checkflag) return false;
						return true;

						/*
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
						*/
					}
				});
			}else{
				_backupSetIsLock = 1;   //之前点击过的是一条链的备份点，无需解密
				backupSetUnlockStyle();   //解锁样式
			}
		}else{
			_backupSetIsLock = 0;  //未加密备份点或自动生成密码备份点，无需解密
			$('.backupsetlockdiv').hide();
			backupSetUnlockStyle();   //解锁样式
			loadRestoreVol(volInfo);  //填充可供恢复的卷
		}
	}
    /**
	 * 解析时间点类型，填充样式，获取可操作的卷信息
	 */
	var loadRestoreVol = function(data){
		var verify_result = "";
		
		if(data){
			_timePointValidityValue = 1;
			var verify_result = data[0].verify_result;
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_EFFECTIVE_TIME);
			$('#timePointValidity').css('color', "#45B6AF");
			if(_timepointType!=2 &&_timepointType!=3 && _timepointType!=4 && _timepointType!=0){
				_timepointType =1;
			}
			_verifyTimepointVolInfo = data;
			_timepointIsValid = true;
		}else{
			_timePointValidityValue = 0;
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
			_timepointType = 0;
			_timepointIsValid = false;
			 return;
		}

		if(verify_result!=""){
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT_MESSAGE);
			$('#timePointValidity').attr("title",verify_result);
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CHECK_TIME,  LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT+","+verify_result);
			_client_vol_info = [];
			_timepointType = 0;
			_timepointIsValid = false;
			return;
		}else{
			_client_vol_info = data;
			if(_timepointType!=2 && _timepointType!=3 && _timepointType!=4 && _timepointType!=0){
				_timepointType =1;
			}
			_timepointIsValid = true;
		}
		parseTimepointType();
		// loadHostVolInfoTab(_client_vol_info); //装载可供恢复的卷信息
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
        //解析选中时间的标签点信息
        var parseLablePointInfo = function(){
            var confTime = $('#selecttimepoint').val();
            var agent_uuid = _createTaskMsgdata.master_agent_uuid;
            _timeList = [];
            var confInfo = {};
            confInfo.confTime = confTime;
            confInfo.timeInterval = _timeInterval;
            _timeList.push(confInfo);

            $("#vol_cdp_task_data_any_time").removeClass("active");
            $("#vol_cdp_task_data_tag_point").attr("class","active");
            $('#task_data_verify_point').css('visibility','initial');
            $('#task_data_event_point').css('visibility','initial');

            $('#vol_cdp_task_data_any_time').hide();
            $('#vol_cdp_task_data_tag_point').show();
            $('#task_data_verify_point').hide();
            $('#task_data_event_point').hide();

            // $('#getAllLabelPointInfo').show();
            // $('#refreshLabelPointInfo').hide();
            //loadCheckVolTagPoint(agent_uuid,isRefreshLabel= false);	//获取客户端对应的标签点提前
            //需要默认选中标签点
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
     * 切换时间间隔，加载对应范围的时间区间
     */
    var changeBackupSetTimeInterval = function(){
        var timeIntervalType = $("#changeTimeInterval").val();
        $('.selectrecoverytimeview').hide();
        if(timeIntervalType==7){
            $('.selectrecoverytimeview').show();
            $('.volCdpBackupSetStartTime').val('');
            $('.volCdpBackupSetEndTime').val('');
            loadIntervalTimePicker(); //自定义区间时间控件
            $('#resetSelectTimePoint').unbind('click').click(function(){
                $('.volCdpBackupSetStartTime').val('');
                $('.volCdpBackupSetEndTime').val('');
            });
            $('.confirmSelectTimePoint').unbind('click').click(getInputEndTimeValidity);
            var time_data = [];
            _chartStartPercent = 0;
            loadTimelineChart(time_data);
        }else{
            _chartStartPercent = 70;
            _timeInterval = timeIntervalType;
			getAgentBackupSetTimeLineData();
        }
    }
    /**
     * 校验输入时间格式正确性
     */
    var checkdate = function () {
		var reg = /^[1-9]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+(20|21|22|23|[0-1]\d):[0-5]\d:[0-5]\d$/
		var str1 = $('.selecttimepoint').val();
		if (!reg.test(str1)) {
			$('.backupsettimepointview').val('').trigger('change');
			_timepointType = 0;
			// $('.selecttimepoint').css('color', "#F3565D");
			$('.selecttimepoint').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
		}
	}
    /**
     * 获取结束时间有效性.
     * @content 获取结束时间,判断结束时间是否大于起始时间.校验结束,计算时间区间类型,根据类型获取对应数据;
     */
    var getInputEndTimeValidity = function(){
        var startTime = $(".volCdpBackupSetStartTime").val();
        var endTime =$('.volCdpBackupSetEndTime').val();
        if (startTime.length > 0 && endTime.length > 0) {
            var start=new Date(startTime.replace("-", "/").replace("-", "/"));
            var end=new Date(endTime.replace("-", "/").replace("-", "/"));
            var timeDiff = end-start; //单位毫秒
            if (start  > end || timeDiff==0) {
                UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_TIME_FRAME_SELECT,LANG.UI_VOL_CDP_RECOVER_TIME_FRAME_SELECT_MESSAGE);
                return;
            }
            _timeInterval = timeIntervalUnit(timeDiff);
			getAgentBackupSetTimeLineData();
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
		$(".starttimeview").datetimepicker({
			language:  'zh-CN',
			autoclose: true,
			isRTL: Metronic.isRTL(),
			format: "yyyy-MM-dd hh:ii:ss",
			pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		});
		
		$(".endtimeview").datetimepicker({
			language:  'zh-CN',
			autoclose: true,
			isRTL: Metronic.isRTL(),
			format: "yyyy-MM-dd hh:ii:ss",
			pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		});
		

	}
	
	/**
	 * 获取备份集时间范围
	 */
	var getBackupSetRange = function(){
		var data = {};
        data.node_uuid = nodeUuid;
		data.task_uuid = taskUuid;
		data.host_uuid = agentUuid;
		data.backup_set_id = selectedBackupSetId;

		Metronic.blockUI({target: '.vol_cdp_backup_set_data_info',animate: true});
		pAjaxRequest(data, "/api/v1/complete_machine_volcdp/backup_set/time_range", "GET", function (result) {
			Metronic.unblockUI('.vol_cdp_backup_set_data_info');
            var data = result.data;
            var volCdpBackupSetRange = $('#volCdpBackupSetRange');
            volCdpBackupSetRange.empty();
            for(var i=0; i<data.length; i++){
				var selected = "";
				if(data[i].backup_set_id == selectedBackupSetId){
					selected = "selected";
				}
				var timeRangeText = data[i].start_time + " - "+ data[i].end_time;
                var option = $("<option>").text(timeRangeText);
				var option = $("<option  "+selected+" > ").text(timeRangeText).val(data[i].backup_set_id)
					.attr('time_point_uuid', data[i].time_point_uuid)
					.attr('start_time',data[i].start_time)
					.attr('end_time',data[i].end_time);
                volCdpBackupSetRange.append(option);
				if(i==0){
					$('#volCdpBackupSetRange option:first').prop('selected', true);
					$('.timerange').html(timeRangeText);
				}
            }
			getAgentBackupSetTimeLineData();
        });
		$('#volCdpBackupSetRange').unbind('change').bind('change', changeBackupSetRange);
	}

	/**
	 * 选择备份集范围，动态赋值chart的时间范围，重载备份集范围
	 */
	var changeBackupSetRange = function(){
		$('.timerange').html("--");
		let backupSetRange = $("#volCdpBackupSetRange").find("option:selected").text();
		_backupSetId = $("#volCdpBackupSetRange").find("option:selected").val();
		let timePointUuid = $('#volCdpBackupSetRange').find("option:selected").attr('time_point_uuid')
		$('.timerange').html(backupSetRange);
		getAgentBackupSetTimeLineData();
		//获取备份集tab选项卡选中事件
		var activeLi = $('.task_data_timepoint_type li.active');
		var tabLink = activeLi.find('a').attr('href');
		if(tabLink == "#vol_cdp_task_data_tag_point"){ //标签点
			_timeList = []
			loadTagPointTableInfo();
		}else if(tabLink === "#cm_cdp_task_data_verify_point"){ //验证点
			loadVerifyPointTableInfo();
		}else if(tabLink == "#cm_cdp_task_data_safe_point"){  //病毒记录
			loadSafePointTableInfo();
			// 初始化病毒扫描历史记录表格
            // $('#cmCdpPointVirusListTable').initVirusHistoryTable({ timepoint_uuid: timePointUuid});
		}
	}
	/**
	 * 获取指定客户端对应的备份集数据，用于加载时间轴
	 */
	var getAgentBackupSetTimeLineData = function(){
		var time_data = [];
		// if(backupSetId){ backupSetId = 0; }
		var backupSetId = $("#volCdpBackupSetRange").find("option:selected").val();
		var info = {};
		info.agent_uuid = agentUuid;
		info.node_uuid = nodeUuid;
		info.backup_set_id = backupSetId;
		info.time_interval = parseInt(_timeInterval);
		info.task_uuid = taskUuid;
		info.start_time = $(".volCdpBackupSetStartTime").val() || '';
		info.end_time = $('.volCdpBackupSetEndTime').val() || '';

		Metronic.blockUI({target: '#volCdpBackupSetTimeline',animate: true});
		pAjaxRequest(info, "/api/v1/complete_machine_volcdp/backup_set/timeline", "GET", function (result) {
			Metronic.unblockUI('.volCdpBackupSetTimeline');
            var data = result.data;
			if(data){
				time_data = data['time_data'];
				loadTimelineChart(time_data);
				_timepointType = 1; 
			}else{
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA, LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA_MESSAGE);
				loadTimelineChart(time_data);
			}
        });
	}
	/**
	 * 加载病毒扫描点
	 */
	var loadSafePointTableInfo = function(){
		if(safePointTableIsLoad){
			let timePointUuid = $('#volCdpBackupSetRange').find("option:selected").attr('time_point_uuid');
			if ($('#task_searchinput').val() !== '') {
				params.timepoint_uuid = timePointUuid;
			}
			safePointTable.bootstrapTable('refresh', {
				query: params
			});

		}else{
			getSafepointRecords();  //获取病毒点记录
		}
		$(function() {
			setTimeout(function() {
				$('#cmCdpDataSafePointTableDiv > div > div:nth-of-type(2)').css({
					'height':'310px',
					'padding-bottom':'5px'
				});
			}, 500);
		});
	}
	/**
	 * 加载验证点和检测点
	 */
	var loadVerifyPointTableInfo = function(){
		if(verifyPointTableIsLoad){
			let timePointUuid = $('#volCdpBackupSetRange').find("option:selected").attr('time_point_uuid');
			if ($('#task_searchinput').val() !== '') {
				params.timepoint_uuid = timePointUuid;
			}
			verifyPointTable.bootstrapTable('refresh', {
				query: params
			});

		}else{
			getVerifypointRecords();  //获取验证点记录
		}
		$(function() {
			// 精准穿透三层结构
			setTimeout(function() {
				$('#taskVerifyPointDiv > div > div:nth-of-type(2)').css({
					'height':'310px',
					'padding-bottom':'5px'
				});
			}, 500);
		});
	}
	/**
	 * 加載标签点表格
	 */
	var loadTagPointTableInfo = function(){
		if(tagPointTableIsLoad){
			var params = {};
			if ($('#task_searchinput').val() !== '') {
				params.search = $('#task_searchinput').val();
				params.host_uuid = agentUuid;
				params.task_uuid = taskUuid;
				params.time_list = [];
				params.backup_set_id = $("#volCdpBackupSetRange").find("option:selected").val();
			}
			tagPointTable.bootstrapTable('refresh', {
				query: params
			});
		}else{
			getTagpointRecords();  //获取标签点记录
		}
		// 确保在 DOM 加载完成后执行
		$(function() {
			// 精准穿透三层结构
			setTimeout(function() {
				$('#taskTagPointDiv > div > div:nth-of-type(2)').css({
					'height':'310px',
					'padding-bottom':'5px'
				});
			}, 500);
			
		});
	}

   	//点击tab触发对应事件
	var getTabInfo = function() {
		var attrId = $(this).attr("class");
		let timePointUuid = $('#volCdpBackupSetRange').find("option:selected").attr('time_point_uuid')
		switch(attrId){
			case "nav-item task_data_tag_point":  //标签点
				if(_timepointType != 2 && _timepointType !=4) {
					loadTagPointTableInfo();
				}
				break;
            case "nav-item cm_cdp_task_data_verify_point":  //验证点 
				loadVerifyPointTableInfo();
                break;
			case "nav-item cm_cdp_task_data_safe_point":   //病毒点
				loadSafePointTableInfo();
			 	break;
		}
	}
	/**
	 * 获取当前病毒点更多扫描信息
	 */
	let expandEvents = { 
		'click .timepoint-safe-detail':(e, value, row, index) => {
			let data = {};
			$('#dataSafeDetailContentDrawer').drawer('show');
			$('#cmCdpPointVirusListTable').initVirusHistoryTable({ timepoint_uuid: row.timepoint_uuid});
			$('.backupsettimecontrol').css("z-index","9999");
		}
	}
	/**
     * 获取任务对应客户端的病毒扫描数据信息
     */
    var safePointTable = $('#cmCdpDataSafePointTable'); 
	var safePointTableIsLoad = false;
	var getSafepointRecords = function () { 
		let timePointUuid = $('#volCdpBackupSetRange').find("option:selected").attr('time_point_uuid');
		 var option = {
			tableArea: '#taskVerifyPointDiv',
			toolbarId: '#task_backupset_verify_point_toolbar',
			buttonsToolbar: '#task_backupset_verify_point_toolbar .vin_btnToolbar',
			searchClass: 'job-log-verifytimepoint',
			placeholder: LANG.UI_CM_CDP_SEARCH_BY_POINT_IN_TIME ,
            vin_url:'/api/v1/complete_machine_volcdp/backup_set/safe_point_info',
            vin_method: 'GET',
            height:'300px',
			vin_params: function () {
				var params = {};
				if ($('#task_searchinput').val() !== '') {
					params.backup_set_id = $("#volCdpBackupSetRange").find("option:selected").val();
				}
				return params;
			},
			showExport: true, //是否开启导出按钮
            showColumns: true, //是否开启列选择按钮
			singleSelect: true, 
			sortName: 'op_time',
			sortOrder: 'desc',
			placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
			onRefresh: function (a, b, c, d) {
                table.bootstrapTable('hideLoading');
            },
			onCheck: function () {
				var selectedRow = safePointTable.bootstrapTable('getSelections')[0];  
				_eventNavTabsType = 2;  //当前tab类型 ：1：任意时间点；2：标签点；3：事件点；5：标签点和事件点
				_timepointType = 2; //时间点类型，0：无效时间点，1：任意时间点；2：标签点；3：事件点；4：标签点和事件点
				verifyTimepointisValid(selectedRow['timepoint_datetime']);
			},
			PostBody: function (param) {},
			columns: [
				{
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
					forceHide: true,
				},
				{
					field: 'timepoint_datetime', //时间点
					title: LANG.UI_RECOVERY_TIMEPOINT,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (index, row) {
						var icon = '<img class = "margintop-5" src ="./img/platform/timepoint.png"> ';
						var timeStr = icon + row.timepoint_datetime ;
						return timeStr;
					}
				},
				{
					field: 'virus_scan_status',
					title: LANG.UI_CM_CDP_VIRUS_SCAN_STATUS,
					formatter: function (index, row) {
						var virusScanStatus =  row.virus_scan_status ;
						var virusStr = LANG.UI_BACKUP_DATA_POINT_VIRUS_STATUS_NOT_SCAN; //扫描中

						switch(virusScanStatus){
							case 0:  //未扫描
								virusStr = '<span class = "label ' + getVirusLevelClass(virusScanStatus) + '" >'+ LANG.UI_BACKUP_DATA_POINT_VIRUS_STATUS_NOT_SCAN +'</span>';  //未扫描
								break;
							case 1:  //扫描中
								virusStr = '<span class = "label ' + getVirusLevelClass(virusScanStatus) + '" >'+ LANG.UI_BACKUP_DATA_POINT_OPERATION_STATE_SCANNING +'</span>';  //扫描中
								break;
							case 2:  //健康
								virusStr = '<span class = "label ' + getVirusLevelClass(virusScanStatus) + '" >'+ LANG.UI_BACKUP_DATA_POINT_VIRUS_STATUS_NORMAL+'</span>';  //健康
								break;
							case 3:  //感染
							case 4:  //已感染但未完成
								virusStr = '<span class = "label ' + getVirusLevelClass(virusScanStatus) + '" >'+ LANG.UI_BACKUP_DATA_POINT_VIRUS_STATUS_ABNORMAL+'</span>';   //感染
								break;
						}
						return virusStr;
					}
				},{
					field: 'last_virus_scan_time',
					title: LANG.UI_CM_CDP_LAST_VIRUS_SCAN_TIME,
				},
				{
					field: 'operation',
					title: LANG.UI_PUBLIC_OPERATION,
					events: expandEvents,
					formatter: function (index, row) {
						let moreOperation = `<a class="timepoint-safe-detail">`+LANG.UI_PLATFORM_INDUSTRY_MORE+`</a>`;
						return moreOperation;
					}
				}
			]
		};
		safePointTableIsLoad = true;
		safePointTable.baseTableConfig().init(option);
	}
	//得到病毒状态的显示类型
	var getVirusLevelClass = function (status){
		let levelClass = '';
		switch(status){
			case 0:
				levelClass = "label-default"
				break;
			case 1:
				levelClass = "label-info";
				break;
			case 2:
				levelClass = "label-success";
				break;
			case 3:
			case 4:
				levelClass = "label-danger";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	/**
     * 获取任务对应客户端的验证数据信息
     */
    var verifyPointTable = $('#taskVerifyPointTable');
	var verifyPointTableIsLoad = false;
	var getVerifypointRecords = function(){
		let timePointUuid = $('#volCdpBackupSetRange').find("option:selected").attr('time_point_uuid');
        var option = {
			tableArea: '#taskVerifyPointDiv',
			toolbarId: '#task_backupset_verify_point_toolbar',
			buttonsToolbar: '#task_backupset_verify_point_toolbar .vin_btnToolbar',
			searchClass: 'job-log-verifytimepoint',
			placeholder: LANG.UI_CM_CDP_SEARCH_BY_POINT_IN_TIME ,
            vin_url:'/api/v1/backup_data/verify_history',
            vin_method: 'GET',
            height:'250px',
			vin_params: function () {
				return { timepoint_uuid: timePointUuid };
			},
			showExport: true, //是否开启导出按钮
            showColumns: true, //是否开启列选择按钮
			singleSelect: true, 
			sortName: 'op_time',
			sortOrder: 'desc',
			placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
			onRefresh: function (a, b, c, d) {
                table.bootstrapTable('hideLoading');
            },
			onCheck: function () {
				var selectedRow = verifyPointTable.bootstrapTable('getSelections')[0];  
				_eventNavTabsType = 2;  //当前tab类型 ：1：任意时间点；2：标签点；3：事件点；5：标签点和事件点
				_timepointType = 2; //时间点类型，0：无效时间点，1：任意时间点；2：标签点；3：事件点；4：标签点和事件点
				verifyTimepointisValid(selectedRow['timepoint']);
			},
			PostBody: function (param) {},
			columns: [
				{
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
					forceHide: true,
					width:"3%",
				},
				{
					field: 'timepoint', //时间点
					title: LANG.UI_RECOVERY_TIMEPOINT,
					sortable: false, //默认可排序，禁用排序才写此项
					width:"22%",
					formatter: function (index, row) {
						var icon = '<img class = "margintop-5" src ="./img/platform/timepoint.png"> ';
						var timeStr = icon + row.timepoint ;
						return timeStr;
					}
				},
				{
					field: 'start_time',
					title: LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_SCAN_START_TIME,
					sortable: false,
					width:"22%",
					align: 'center',
				},
				{
					field: 'end_time',
					title: LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_SCAN_END_TIME,
					sortable: false,
					width:"22%",
					align: 'center',
				},
				{
					field: 'ping_status_des',
					title: LANG.UI_VERIFY_PING_TEST,
					width:"10%",
					sortable: false,
					align: 'center',
				},
				{
					field: 'heartbeat_status_des',
					title: LANG.UI_VERIFY_HEARTBEAT,
					sortable: false,
					width:"10%",
					align: 'center',
				},
				{
					field: 'screen_status_des',
					title: LANG.UI_VERIFY_SCREEN,
					sortable: false,
					width:"10%",
					align: 'center',
				},
			]
		};
		verifyPointTableIsLoad = true;
		verifyPointTable.baseTableConfig().init(option);
    }


    /**
     * 获取任务标签点记录信息
     */
    var tagPointTable = $('#taskTagPointTable');
	var tagPointTableIsLoad = false;
    var getTagpointRecords = function(){
        var option = {
			tableArea: '#taskTagPointDiv',
			toolbarId: '#task_backupset_tag_point_toolbar',
			buttonsToolbar: '#task_backupset_tag_point_toolbar .vin_btnToolbar',
			searchClass: 'job-log-tagtimepoint',
			// placeholder: `<?php echo "按时间点搜索" ?>`,
			placeholder: LANG.UI_CM_CDP_SEARCH_BY_POINT_IN_TIME,
            vin_url:'/api/v1/complete_machine_volcdp/backup_set/tag_point_info',
            vin_method: 'GET',
            height:'300px',
			vin_params: function () {
				var params = {};
				if ($('#task_searchinput').val() !== '') {
					params.search = $('#task_searchinput').val();
                    params.host_uuid = agentUuid;
                    params.task_uuid = taskUuid;
                    params.time_list = [];
					params.backup_set_id = $("#volCdpBackupSetRange").find("option:selected").val();
				}
				return params;
			},
			showExport: true, //是否开启导出按钮
            showColumns: true, //是否开启列选择按钮
			singleSelect: true, 
			sortName: 'op_time',
			sortOrder: 'desc',
			placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
			onRefresh: function (a, b, c, d) {
                table.bootstrapTable('hideLoading');
            },
			onCheck: function () {
				var selectedRow = tagPointTable.bootstrapTable('getSelections')[0];  
				_eventNavTabsType = 2;  //当前tab类型 ：1：任意时间点；2：标签点；3：事件点；5：标签点和事件点
				_timepointType = 2; //时间点类型，0：无效时间点，1：任意时间点；2：标签点；3：事件点；4：标签点和事件点
				verifyTimepointisValid(selectedRow['tag_point']);
			},
			onPostBody: function (param) {
				let tableData = $('#taskTagPointTable').bootstrapTable('getData');

				if (tableData.length > 0 && tableData.length < 5) { 
					$('#taskTagPointDiv .fixed-table-pagination').hide();
				}
			},
			columns: [{
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
					forceHide: true,
				},
				{
					field: 'tag_point', //时间点
					title: LANG.UI_RECOVERY_TIMEPOINT,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (index, row) {
						var icon = '<img class = "margintop-5" src ="./img/platform/timepoint.png"> ';
						var timeStr = icon + row.tag_point ;
						return timeStr;
					}
				},
				{
					field: 'remarks',
					title: LANG.UI_PUBLIC_REMARK,
				}
			]
		};
		tagPointTableIsLoad = true;
		tagPointTable.baseTableConfig().init(option);
    }
	//初始化时间控件
	var loadDatatimePicker = function(){
		$(".backupsettimepointview").datetimepicker({
			language:  'zh-CN',
			autoclose: true,
			isRTL: Metronic.isRTL(),
			format: "yyyy-MM-dd hh:ii:ss",
			pickerPosition: (Metronic.isRTL() ? "top-right" : "top-left")
		});
	}
	//重置选择的时间点
	var resetTimepoint = function(){
		$('.backupsettimepointview').val('').trigger('change');
		_timepointType = 0;   //时间点校验未通过
		_timepointIsValid = false;
		$('#timePointValidity').css('color', "#F3565D");
		$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
		return false
	}

    //事件监听
    var initListener = function(){
        // loadTimelineChart();
		loadDatatimePicker();
        $('#changeTimeInterval').on('change', changeBackupSetTimeInterval); //时间轴上时间间隔类型
		$('.selecttimepoint').off('change').on('change', function(){
			var timepoint = $('.selecttimepoint').val();
			verifyTimepointisValid(timepoint);
			checkdate();
		});
        $('.task_data_timepoint_type li').unbind('click').click(getTabInfo);
		$('#resetRecoveryTimepoint').unbind('click').click(resetTimepoint);
		$('.fa-lock').unbind('click').click(lockTimepoint);

    }
    
    return {
        init: function (options) {
            if (options.task_uuid != undefined) {  //任务uuid
                taskUuid = options.task_uuid;
            }
            if (options.agent_uuid != undefined) {  //数据源客户端uuid
                agentUuid = options.agent_uuid
            }
            if (options.create_task_type != undefined) {  //创建任务类型
                createTaskType = options.create_task_type;
            }
            if (options.node_uuid !=undefined){  //节点uuid
                nodeUuid = options.node_uuid;
            }
			let timepointStr = LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TIME_POINT;
			if(createTaskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER){  //接管
				timepointStr = LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_TIME_POINT;
			}
			//获取已选中的备份集ID，通过备份数据管理页面选择备指定备份集跳转到当前恢复/接管配置页面
			if(options.backup_set_id != undefined){  
				selectedBackupSetId = options.backup_set_id;
			}
			$('.backupsettimepoint').html(timepointStr);
			if(CONF.VENDOR == "sangfor"){  //深信服OEM版本只支持整机接管，需要屏蔽接管平台和自动接管的相关配置，对应需求号：#27350
				$('.cm_cdp_task_data_safe_point').hide();
			}
            initListener();  // 初始化监听事件
			getBackupSetRange(); //获取备份集时间范围
			// getAgentBackupSetTimeLineData();
			loadIntervalTimePicker(); //初始化时间组件
			
        },

        // 提供一个对外获取所有配置的接口,获取对选中的任务的时间点和卷的配置
        getBackupSetConfigInfo: function () {
            var info = {};
			info.time_point = $('.selecttimepoint').val();
			info.time_point_uuid = $('#volCdpBackupSetRange').find("option:selected").attr('time_point_uuid');
			info.time_type = _eventNavTabsType;
			info.vol_info = _verifyTimepointVolInfo;
			info.time_point_is_valid = _timepointIsValid;
			info.system_boot_type = _systemBootType;
			info.os_type = _agentOsType; 
			info.virus_scan_status  = _virusScanStatus;  //0 – 未扫描 1 – 扫描中 2 – 健康 3 – 感染
            return info;
        }
    }
}();
