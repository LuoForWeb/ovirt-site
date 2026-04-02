var VerificationJob = function(){
	let data = {basic_info:{},item_list:[],time_strategy:{}, high_strategy:{}};
	let initTypeFlag = false; //初始化类型标志
	let selectIPFlag = true;	//选择挂载IP标志
	let ztree, nodeParamList;
	let point_list = [];
	let initPointFlag = false;
	let editFlag = false; //是否是修改
	let pageIndex = 0; //轮播索引
	let initNodeFlag = false;
	let pointList = [];
	let initEditFlag = false;
	let zTreeFile = [];
	let _Template;
	let source_list = [];
	let networkUuidList = {};
	let UN_FULL = []; // 时间表格 用于存联动勾选的非完备点
	let _pageSize = 40; //代理端文件列表每次显示条数;
	let timepointParams;
	let cpu_info_list = [], os_info_list = [];
	let _SETTINGS;
	let threadShowFlag = false;
	let storageList = [];
	let order = "desc";
	let selectModule = [];
	let appgroupList = [];
	let appgroupuuidList = [];
	let currentModule;
	let nextFlag = false;
	let selectfile =  true; //是否选择文件
	let initFilterFlag =  false; //初始化过滤器标志
	let backupModeDes = [
		LANG.UI_PUBLIC_UNKNOWN,
		LANG.UI_DATA_TYPE_FULL,
		LANG.UI_DATA_TYPE_INCR,
		LANG.UI_DATA_TYPE_DIFF,
		LANG.UI_PUBLIC_BACKUP_LOG,
		LANG.UI_PUBLIC_BACKUP_ARCHIVE_LOG,

	];
	let OS_TYPE = ['',
		'Mac OS',
		'Windows',
		'Linux'];
	let OS_TYPE_INDEX = {
		'Mac OS': 1,
		'Windows': 2,
		'Linux': 3
	};
	let jobTitle = LANG.UI_VERIFY_ADD_JOB;
	//获取时间点原配置信息
	let hostSettings = {};
	let _timepointIsValid = false;
	let cdpInfo = {};
	const _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	const netmaskVal=/^(254|252|248|240|224|192|128|0)\.0\.0\.0|255\.(254|252|248|240|224|192|128|0)\.0\.0|255\.255\.(254|252|248|240|224|192|128|0)\.0|255\.255\.255\.(254|252|248|240|224|192|128|0)$/;
	//模块类型初始化 业务类型product_type 1数据备份 2连续数据保护 3数据复制
	const moduleList = [
		{
			name: LANG.UI_PUBLIC_VM,
			module: 2,
			submodule: 1,
			product_type: 1
		},
		{
			name: LANG.UI_PUBLIC_PRIVATE_CLOUD,
			module: 2,
			submodule: 2,
			product_type: 1
		},
		{
			name: LANG.UI_PUBLIC_PUBLIC_CLOUD,
			module: 2,
			submodule: 3,
			product_type: 1
		},
		{
			name: LANG.UI_BACKUP_DATA_MODULE_OS,
			module: 5,
			submodule: 1,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_FILE_FILE,
			module: 3,
			submodule: 1,
			product_type: 1
		},
		{
			name: LANG.UI_BACKUP_DATA_MODULE_NAS,
			module: 11,
			submodule: 2,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_OBS,
			module: 3,
			submodule: 4,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_HADOOP,
			module: 3,
			submodule: 3,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_M365,
			module: 14,
			submodule: 0,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_MODULE_DB,
			module: 4,
			submodule: 0,
			product_type: 1
		},
		{
			name: LANG.UI_BACKUP_DATA_MODULE_K8S,
			module: 28,
			submodule: 0,
			product_type: 1
		},
		{
			name: LANG.UI_BACKUP_DATA_MODULE_OS,
			module: 10,
			submodule: 0,
			product_type: 2
		},
	];

	const TIMEPOINT_TYPE_ALL = 1; // 所有时间点
	const TIMEPOINT_TYPE_LAST = 2; // 最新时间点
	const TIMEPOINT_TYPE_SELECT = 3; // 指定时间点
	let nodeMap = {}; // 存放liID => node的键值对
	let objectTimepointInfo = []; //存放每个对象时间点信息回显

	const WINDOWS_INDEX = [2008,2127,2126,2128,2129,2130,2012,2013,2014,2015,2016,2017,2018,2019,2020,2021,2022,2023,2024,2025,2026,2029, 2030, 2033,2034,2035,2036,2038,2039,2047,2048,2049,2050];	//用于处理windows20082及2003网卡驱动默认推荐修改为RTL8139|windows 2008R2版本除外

	//初始化回调事件函数
	let initListeners = function(){
		//验证对象排序
		$('.orderDesc').on('click', function(){
			$('.orderDesc').hide();
			$('.orderAsc').show();
			order = "asc";
			getObjectInfo();
		});

		$(".backupsettimepointview").datetimepicker({
			language:  'zh-CN',
			autoclose: true,
			isRTL: Metronic.isRTL(),
			format: "yyyy-MM-dd hh:ii:ss",
			pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		});

		$('.orderAsc').on('click', function(){
			$('.orderAsc').hide();
			$('.orderDesc').show();
			order = "desc";
			getObjectInfo();
		});


		// 监听过滤器组件派发的数据，以更新数据
		window.$on('verify_filter_btn-updateFilterEvent', (filterData) => {
			selectModule = [];
			for (var i=0;i<filterData.length;i++){
				selectModule = $.merge(selectModule, filterData[i].value);
			}
			getObjectInfo();
		});
		//初始化icheck
		$('.icheck').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});


		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});

		//切换验证模式
		$('#verifyMode').on('change', function(e, oldValue, newValud) {
			if(newValud == 1){
				$('.appgroupDiv').hide();
				$('.labTypeDiv').hide();
				$('.virtualLabDiv').hide();
				$('.verifyTypeDiv').hide();
				$('#verifyType').find('input[data-mode=1]').iCheck('uncheck');
				$('#verifyType').find('input[data-mode=0]').iCheck('check');

			}else{
				$('.appgroupDiv').show();
				if ($('#oem_version').val() != CONF.VENDOR_LIST.gmp){
					$('.verifyTypeDiv').show();
					if($.inArray("dataVerificationDR", CONF.FUNCTIONS) == -1){
						$('.labTypeDiv').hide();
						$('#labType').find('input[data-mode=2]').iCheck('check');
						$('#labType').find('input[data-mode=1]').iCheck('uncheck');
						$('.virtualLabDiv').show();
					}else{
						$('.labTypeDiv').show();
						$('#labType').find('input[data-mode=1]').iCheck('check');
						$('#labType').find('input[data-mode=2]').iCheck('uncheck');
						$('.virtualLabDiv').hide();
					}
				}

			}
		});

		//验证类型
		$('#verifyType').find('.icheck').on('ifClicked', function(){
			let mode = $(this).data('mode');
			if (0 == mode) {
				//自动验证
				$('#verifyType').find('input[data-mode=1]').iCheck('uncheck');
				$('#startType').attr('disabled', false);
			} else {
				//手动验证
				$('#verifyType').find('input[data-mode=0]').iCheck('uncheck');
				$('#startType').val(1).attr('disabled', true);
				$('#setstrategy').hide();
			}
		});

		//虚拟演练室构建类型
		$('#labType').find('.icheck').on('ifClicked', function(){
			let mode = $(this).data('mode');
			if (2 == mode) {
				$('.autolabTips').hide();
				$('.virtualLabDiv').show();
				$('#labType').find('input[data-mode=1]').iCheck('uncheck');
			} else {
				$('.autolabTips').show();
				$('.virtualLabDiv').hide();
				$('#labType').find('input[data-mode=2]').iCheck('uncheck');
			}
		});

		//切换时间策略类型
		$('#startType').on('change', function(){
			var value = this.value;
			if(value == 2){
				$('#setstrategy').show();
			}else{
				$('#setstrategy').hide();
			}
		});

		//选择对应的时间点
		$('#select_point_submit').on('click', function(){
			selectPointSubmit();
		});

		//切换时间点类型
		$('#pointType').on('change', function(){
			let value = $(this).val();
			switch (parseInt(value)){
				case 1:
					$('.pointNumDiv').show();
					initPointTable(timepointParams);
					$('.pointTableDiv').show();
					$('.cdptimeDiv').hide();
					break;
				case 2:
					$('.pointNumDiv').hide();
					$('.pointTableDiv').hide();
					$('.cdptimeDiv').hide();
					break;
				case 3:
					//如果是整机实时
					if(currentModule == CONF.MODULE_TYPE.VOL_CDP){
						$('.cdptimeDiv').show();
						$('.pointTableDiv').hide();
						$('.pointNumDiv').hide();
					}else{
						initPointTable(timepointParams);
						$('.cdptimeDiv').hide();
						$('.pointTableDiv').show();
						$('.pointNumDiv').hide();
					}

					//GMP屏蔽最大验证数量显示，只支持选中一个点
					if($('#oem_version').val() == CONF.VENDOR_LIST.gmp){
						$('.pointNumDiv').hide();
					}
					break;
			}

			//完全可恢复性验证-手动验证 屏蔽最大验证数量
			if(data.basic_info.verify_mode == 2 && data.basic_info.automatic_verifitied_flag == 1){
				$('.pointNumDiv').hide();
			}
		});

		//自动选择IP
		$('#diyserverip').on('click', function(){
			$('.selectipdiv').hide();
			$('.inputipdiv').show();
			selectIPFlag = false;
		});

		//手动输入IP
		$('#selectserverip').on('click', function(){
			$('.selectipdiv').show();
			$('.inputipdiv').hide();
			selectIPFlag = true;
		});

		//配置访问地址
		$('#setAddr').on('click', function (){
			$('#setAddrModal').modal();
		});

		//点击确定关闭验证报告模板
		$('#chooseItems').on('click', function(){
			$('#drawer-indust_template_config').drawer('hide');
		});

		//搜索对象
		$('#search').on('propertychange', searchObject).on('input', searchObject);

		//查看虚拟演练室详情
		$('#showLabDetail').on('click', function(){
			initLabConfig();
		});

		//切换验证报告模板
		$('select[name=template]').on('change', function(){
			let templateuuid = $('select[name=template]').val();
			if(templateuuid != _Template){
				TemplateChoose.init({uuid: templateuuid, 'source': 2, init:2, again_init:true});
				_Template = templateuuid;
			}
		});

		//编辑验证报告模板
		$('#editTemplate').on('click', function(){
			$('#drawer-indust_template_config').drawer('show');
		});

		$('.selecttimepoint').blur(function () {checkdate()});
		$('.selecttimepoint').on('change', function(){var timepoint = $('.selecttimepoint').val();verifyTimepointisValid(timepoint);});

	}

	/**
	 * 校验时间点有效性
	 */
	var verifyTimepointisValid = function(timePoint){
		let startTime = $('#cdptimerange').find("option:selected").attr("start_time");
		let endTime = $('#volCdpBackupSetRange').find("option:selected").attr("end_time");
		let startTimeValue = new Date(startTime).getTime();
		let endTimeValue = new Date(endTime).getTime();
		let checkTimeValue = new Date(timePoint).getTime();
		_timepointIsValid = false;
		if(checkTimeValue>endTimeValue || checkTimeValue<startTimeValue){
			_timepointIsValid = false;
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
			UIToastr.showWarning(jobTitle, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
			return false;
		}

		var params = {
			agent_uuid:cdpInfo.host_uuid,
			node_uuid:cdpInfo.node_uuid,
			timepoint:timePoint,
			vol_uuid:'',
			task_type:33,
			task_uuid:cdpInfo.task_uuid,
			backup_set_id : $("#cdptimerange").find("option:selected").val(),
		};
		Metronic.blockUI({target: '#verificationContent',animate: true});
		pAjaxRequest(params, "/api/v1/complete_machine_volcdp/backup_set/verify_timepoint_is_valid", "GET", function (result) {
			Metronic.unblockUI('#verificationContent');
			var data = result.data;
			if(!data){
				_timepointIsValid = false;
				UIToastr.showWarning(jobTitle, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
				$('#timePointValidity').css('color', "#F3565D");
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
				return false;
			}
			var volInfo = data.time_vol_info;
			if(volInfo){
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_EFFECTIVE_TIME);
				$('#timePointValidity').css('color', "#45B6AF");
				_timepointIsValid = true;
			}

		});
		$(".selecttimepoint").val(timePoint);

	}

	/**
	 * 校验输入时间格式正确性
	 */
	var checkdate = function () {
		var reg = /^[1-9]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+(20|21|22|23|[0-1]\d):[0-5]\d:[0-5]\d$/
		var str1 = $('.selecttimepoint').val();
		if (!reg.test(str1)) {
			_timepointIsValid = false;
			$('.selecttimepoint').val('');
			$('#timePointValidity').css('color', "#F3565D");
			$('.selecttimepoint').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
		}else{
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_EFFECTIVE_TIME);
			$('#timePointValidity').css('color', "#45B6AF");
			_timepointIsValid = true;
		}
	}

	//搜索虚拟机
	var searchObject = function(){
		var value = $('#search').val();
		// // 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		var nodes = ztree.getNodes();
		if(!nodes || nodes.length == 0) return;
		var checkNode =ztree.getCheckedNodes();
		var checkVmNode = [];
		$.each(checkNode, function (i, v) {
			checkVmNode.push(v);
		});
		var allNode = ztree.transformToArray(ztree.getNodes());
		nodeParamList = ztree.getNodesByParamFuzzy('name', value);
		if(nodeParamList.length!=0){
			ztree.hideNodes(allNode);
			$('.three_tree').show();
			$('#nosearchtips').hide();
		}else{
			$('.three_tree').hide();
			$('#nosearchtips').show();
		}
		//连接搜索的和所勾选的
		nodeParamList =nodeParamList.concat(checkVmNode);
		var nodeParamList1 = ztree.transformToArray(nodeParamList);
		for(var n in nodeParamList1){
			findParent(ztree,nodeParamList1[n]);
		}
		ztree.showNodes(nodeParamList);
	}

	//找到父节点
	var findParent = function(treeObj,node){
		ztree.expandNode(node,true,false,false);
		if(!node.children){
			nodeParamList.push(node);
			ztree.expandNode(node,false,false,false);
		}
		var pNode = node.getParentNode();
		if(pNode != null){
			nodeParamList.push(pNode);
			findParent(ztree, pNode);
		}
	}

	//确认选择时间点
	let selectPointSubmit = function(){
		let pointType = parseInt($('#pointType').val());
		let pointNum = $('#pointnum').val();
		let liID = clearString("object_tree" + $('#src_task_uuid').val() + $('#object_uuid').val());
		
		let selectTimepointInfo = {};
		selectTimepointInfo.timepoint_uuids = [];
		
		if(pointType != 3){
			//选择最新时间点 或全部时间点
			
			$('.select_point_' +liID).attr('data-type', 1);
			$('.select_point_' +liID).attr('data-uuid', "");
			initDriver(liID, nodeMap[liID], pointType);
			if(pointType == 1){
				//单次最大验证数量
				if(!pointNum || pointNum == "" || pointNum == 0){
					UIToastr.showInfo(jobTitle,LANG.UI_VERIFY_SET_MAX_TIMEPOINT_NUM_TIPS);
					return false;
				}
				$(' .select_point_' +liID).attr('data-maxnum', pointNum);
			}
			$('.select_point_' +liID).html( $('#pointType').find('option:selected').text());
		}else{
			let list = []; //存放时间点信息
			//如果是整机实时
			if(currentModule == CONF.MODULE_TYPE.VOL_CDP){
				let timepoint = $('.selecttimepoint').val();
				let timepointuuid = $('#cdptimerange').find('option:selected').attr('time_point_uuid');
				let systemFlag = $('#cdptimerange').find('option:selected').data('system');
				if(!systemFlag && data.basic_info.verify_mode == 2){
					UIToastr.showWarning(jobTitle, LANG.UI_VERIFY_NOT_FIND_SYSTEM_DISK_TIPS);
					return false;
				}
				$('.select_point_' +liID).attr('data-uuid', timepointuuid);
				$('.select_point_' +liID).attr('data-timepoint', timepoint);
				$('.select_point_' +liID).attr('data-system', systemFlag);
				$('.select_point_' +liID).html(timepoint);
				list.push({
					timepoint_uuid: timepointuuid,
					timepoint: timepoint,
					backup_mode: 1
				});
				selectTimepointInfo.vol_cdp_datetime = timepoint;
				initDriver(liID, nodeMap[liID], pointType);
			}else{
				//指定时间点
				let selectPoint = $('#point_table').bootstrapTable('getSelections');
				if (selectPoint.length == 0){
					UIToastr.showInfo(jobTitle,LANG.UI_VERIFY_SELECT_TIMEPOINT_TIPS);
					return false;
				}
				if(!pointNum || pointNum == "" || pointNum == 0){
					UIToastr.showInfo(jobTitle,LANG.UI_VERIFY_SELECT_TIMEPOINT_TIPS);
					return false;
				}
				for (let i=0; i<selectPoint.length;i++){
					let backup_mode = selectPoint[i].backup_mode;
					//数据库模块判断归档日志备份和日志备份描述显示
					if (selectPoint[i].module_type == CONF.MODULE_TYPE.DB && selectPoint[i].backup_mode == CONF.TIME_STRATEGY_MODE.DB_LOG){
						//归档日志备份索引替换成5适配描述
						if (
							selectPoint[i].sub_type == CONF.DB_TYPE.DM ||  // 归档日志备份点
							selectPoint[i].sub_type == CONF.DB_TYPE.ORACLE ||
							selectPoint[i].sub_type == CONF.DB_TYPE.POSTGRE ||
							selectPoint[i].sub_type == CONF.DB_TYPE.ANTDB ||
							selectPoint[i].sub_type == CONF.DB_TYPE.KINGBASE ||
							selectPoint[i].sub_type == CONF.DB_TYPE.UXDB ||
							selectPoint[i].sub_type == CONF.DB_TYPE.HIGHGO ||
							selectPoint[i].sub_type == CONF.DB_TYPE.OPENGAUSS ||
							selectPoint[i].sub_type == CONF.DB_TYPE.VASTBASE
						) {
							backup_mode = 5;
						}
					}
					let info = {
						timepoint_uuid: selectPoint[i].timepoint_uuid,
						timepoint: selectPoint[i].timepoint,
						backup_mode: backup_mode
					}
					list.push(info);
				}
				pointList[$('#object_uuid').val() + '_' +  $('#src_task_uuid').val()] = list;
				selectTimepointInfo.timepoint_uuids = list;
				let des = "";
				for(let i=0;i<selectPoint.length;i++){
					des += selectPoint[i].timepoint + '('+ selectPoint[i].backup_mode_des +')' +'<br>';
				}
				$('.select_point_' +liID).attr('data-type', selectPoint[0].task_type);
				$('.select_point_' +liID).attr('data-uuid', selectPoint[0].timepoint_uuid);
				$('.select_point_' +liID).html(des);
				$('.select_point_' +liID).attr('data-timepoint', selectPoint[0].timepoint);
				$('.select_point_' +liID).attr('data-modeDes', selectPoint[0].backup_mode_des);
				$(' .select_point_' +liID).attr('data-maxnum', pointNum);
				
				initDriver(liID, nodeMap[liID], pointType, selectPoint);
			}
		}
		selectTimepointInfo.timepoint_range = pointType;
		selectTimepointInfo.max_timepoint_verify = pointNum;
		$('.select_point_' +liID).attr('data-pointType', pointType);
		objectTimepointInfo[liID] = selectTimepointInfo;
		$('#select_point_drawer').drawer('hide');
	}

	/************初始化修改配置信息***************/


	/************创建数据验证步骤***********/
	let wizardInit = function(){
		if (!jQuery().bootstrapWizard) {
			return;
		}
		let form = $('#submit_form');
		let error = $('.alert-danger', form);
		let success = $('.alert-success', form);
		let handleTitle = function(tab, navigation, index) {
			let total = navigation.find('li').length;//总共的步骤数
			let current = index + 1;      //当前步骤

			// set done steps
			jQuery('li', $('#verificationContent')).removeClass("done");
			let li_list = navigation.find('li');
			for (let i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}

			//如果第一步 上一步按钮隐藏
			if (current == 1) {
				$('#verificationContent').find('.button-previous').css('visibility', 'hidden');
				$('#verificationContent').find('.button-next').addClass('next-btn-margin-left');
			} else {
				$('#verificationContent').find('.button-previous').css('visibility', 'visible');
				$('#verificationContent').find('.button-next').removeClass('next-btn-margin-left');
			}

			//处理最后一步
			if (current >= total) {
				$('#verificationContent').find('.button-next').hide();
				$('#verificationContent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#verificationContent').find('.button-next').show();
				$('#verificationContent .button-submit').css('visibility', 'hidden');
			}

			//滑动步骤条
			Metronic.scrollTo($('.page-title'));

		}

		// default form wizard
		var wizard = $('#verificationContent').bootstrapWizard({
			'nextSelector': '.button-next',
			'previousSelector': '.button-previous',
			onTabClick: function (tab, navigation, index, clickedIndex) {
				return false;
			},

			//下一步
			onNext: function (tab, navigation, index) {
				success.hide();
				error.hide();
				switch(index){
					case 1:
						if(step1Valid() == false){
							return false;
						}

						//修改任务获取步骤索引
						if (editFlag) {
							pageIndex = 1;
						}
						break;
					case 2:
						if(step2Valid() == false){
							return false;
						}else if (!selectfile && !nextFlag && selectfileFlag){
							bootbox.confirm({
								title: jobTitle,
								message: LANG.UI_VERIFY_NOT_SELECT_FILE_TIPS,
								buttons: {
									confirm: {
										label: LANG.UI_PUBLIC_CONTINUE,
									},
									cancel: {
										label: LANG.UI_PUBLIC_CANCEL,
						}
								},
								callback: function(r) {
									if (!r) return;
									nextFlag = true;
									wizard.bootstrapWizard('next');
								}
							});
							return false;
						}

						//修改任务获取步骤索引
						if (editFlag) {
							pageIndex = 2;
						}
						break;
					case 3:
						if(step3Valid() == false){
							return false;
						}

						//修改任务获取步骤索引
						if (editFlag) {
							pageIndex = 3;
						}
						break;
				}
				handleTitle(tab, navigation, index);
			},

			//上一步
			onPrevious: function (tab, navigation, index) {
				success.hide();
				error.hide();
				if(index == 1){ //从第三步步到第二步
					nextFlag =  false; //将第二步的flag还原
				}
				handleTitle(tab, navigation, index);
			},

			//进度条显示
			onTabShow: function (tab, navigation, index) {
				let total = navigation.find('li').length;
				let current = index + 1;
				let $percent = (current / total) * 100;
				$('#verificationContent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});
		$('#verificationContent .button-submit').click(submit).css('visibility', 'hidden');
	}

	//获取第一步配置信息
	let step1Valid = function(){

		/***********获取第一步配置信息************/
		//验证方式
		data.basic_info.verify_mode = parseInt($('#verifyMode .radio-group__item.active').attr('value'));

		//验证类型
		data.basic_info.automatic_verifitied_flag = parseInt($('#verifyType').find('.icheck:checked').attr('data-mode'));

		//验证报告模板
		data.basic_info.template_uuid = $('select[name=template]').val();
		//获取是否包含文件对比配置
		if ($('select[name=template] option:selected').data('flag')){
			selectfileFlag = true;
		}else{
			selectfileFlag = false;
		}
		//审批流
		data.basic_info.approval_uuid = $('select[name=approval]').val();

		//虚拟演练室
		data.basic_info.virtual_lab_uuid = $('select[name=virtualLab]').val();

		//gmp必须选择模板和审批流
		if($('#oem_version').val() == CONF.VENDOR_LIST.gmp){
			if(!data.basic_info.template_uuid ||data.basic_info.template_uuid == "" || !data.basic_info.approval_uuid || data.basic_info.approval_uuid == ""){
				UIToastr.showInfo(jobTitle, LANG.UI_VERIFY_SELECT_TEMPLATE_AND_APPROVAL_TIPS);
				return false;
			}
		}

		//选择已创建演练室
		if(data.basic_info.verify_mode == 2 && parseInt($('#labType').find('.icheck:checked').attr('data-mode')) == 2 && data.basic_info.virtual_lab_uuid == ""){
			UIToastr.showInfo(jobTitle, LANG.UI_VERIFY_SELECT_LAB_TIPS);
			return false;
		}

		//自动构建虚拟演练室
		if(parseInt($('#labType').find('.icheck:checked').attr('data-mode')) == 1){
			data.basic_info.virtual_lab_uuid = "";
		}


		//手动验证
		if(data.basic_info.automatic_verifitied_flag == 1){
			$('#startType').val(1).attr('disabled', true);
			$('#setstrategy').hide();
		}else{
			$('#startType').val(2).attr('disabled', false);
			$('#setstrategy').show();
		}

		//选择第三方虚拟化
		if(data.basic_info.virtual_lab_uuid == ""){
			$('#storagemount_component_body').hide();
		}else{
			$('#storagemount_component_body').show();
		}

		let CURRENT_JOB_TABLE_FILTER_OPTIONS = [
			{
				label: LANG.UI_BACKUP_DATA_LABEL_SERVICE_BACKUP,
				field: 'timing_data_protect',
				value: [
					{
						id: 'vmprotect',
						value: '2_1',
						text: LANG.UI_BACKUP_DATA_MODULE_VM,
						hostflag: true,
					},
					{
						id: 'prcloud_protect',
						value: '2_2',
						text: LANG.UI_PUBLIC_PRIVATE_CLOUD,
						hostflag: true,
					},
					{
						id: 'awsprotect',
						value: '2_3',
						text: LANG.UI_PUBLIC_PUBLIC_CLOUD,
						hostflag: true,
					},
					{
						id: 'complete_machine',
						value: '5_1',
						text: LANG.UI_BACKUP_DATA_MODULE_OS,
						hostflag: true,
					},
					{
						id: 'filebackup',
						value: '3_1',
						text: LANG.UI_FILE_FILE,
						hostflag: false,
					},
					{
						id: 'nas_protect',
						value: '11_2',
						text: LANG.UI_REPORT_NAS,
						hostflag: false,
					},
					{
						id: 'hadoop_protect',
						value: '3_3',
						text: LANG.UI_PLATFORM_DES_HADOOP,
						hostflag: false,
					},
					{
						id: 'obs_protect',
						value: '3_4',
						text: LANG.UI_VISUAL_OBS,
						hostflag: false,
					},
					{
						id: 'office365_protect',
						value: '14_0',
						text: LANG.UI_BACKUP_DATA_MODULE_M365,
						hostflag: false,
					},
					{
						id: 'k8s_protect',
						value: '28_0',
						text: LANG.UI_BACKUP_DATA_MODULE_K8S,
						hostflag: false,
					},
					{
						id: 'db_protect',
						value: '4_0',
						text: LANG.UI_AGENT_MODULE_DB,
						hostflag: false,

					},
				]
			},
			{
				label: LANG.UI_BACKUP_DATA_LABEL_SERVICE_CDP,
				field: 'real_time_data_protect',
				value: [
					{
						id: 'complete_cdp_backup',
						value: '10_0_32',
						text: LANG.UI_BACKUP_DATA_MODULE_OS,
						hostflag: true,
					}
				]
			},
			{
				label: LANG.UI_BACKUP_DATA_LABEL_SERVICE_REPLICATION,
				field: 'real_time_data_protect',
				value: [
					{
						id: 'machine_copy',
						value: '10_0_65',
						text: LANG.UI_BACKUP_DATA_MODULE_OS,
						hostflag: true,
					}
				]
			},

		];
		// 对过滤器选项数组前三列，即定时备份、实时备份和数据复制列进行过滤
		const filteredOptions = CURRENT_JOB_TABLE_FILTER_OPTIONS.slice(0, 3).filter(item => {
			// 对每个 item 的 value 进行过滤
			const filteredValues = item.value.filter(val => CONF.PERMISSION.includes(val.id));

			// 如果过滤后的 value 数组不为空，则更新原对象的 value 并保留该对象
			if (filteredValues.length > 0) {
				item.value = filteredValues;
				return true;
			}
			return false; // 如果过滤后没有元素，则不保留该项
		});
		// 将前三项替换为过滤后的结果
		CURRENT_JOB_TABLE_FILTER_OPTIONS.splice(0, 3, ...filteredOptions);

		if(!initFilterFlag){
			if (data.basic_info.verify_mode == 2){
				//完全可恢复性验证
				selectModule = ['2_1','2_2','2_3','5_1', '10_0_32', '10_0_65'];
				let filterData =  CURRENT_JOB_TABLE_FILTER_OPTIONS.map(option => {
					if (option.field === 'timing_data_protect') {
						option.value = option.value.filter(item => item.hostflag);
					}
					return option;
				});
				$('#verify_filter_wrapper').initFilter({
					filterSlotId: 'verify_filter_wrapper',
					filterBtnId: 'verify_filter_btn',
					filters: filterData
				});
				// initFilterFlag = true;
			}else{
				//备份数据验证及安全扫描
				selectModule = ['2_1','2_2','2_3','3_1','3_2','3_3','3_4','4_0','5_1', '10_0_32', '10_0_65', '14_0','11_2','28_0'];

				$('#verify_filter_wrapper').initFilter({
					filterSlotId: 'verify_filter_wrapper',
					filterBtnId: 'verify_filter_btn',
					filters: CURRENT_JOB_TABLE_FILTER_OPTIONS
				});
				// initFilterFlag = true;
			}
		}
		//清空驱动检测源
		source_list = [];
		initStorageShowType();//初始化存储


		//完全可恢复性验证-手动验证 屏蔽最大验证数量
		if(data.basic_info.automatic_verifitied_flag == 1 || $('#oem_version').val() == CONF.VENDOR_LIST.gmp){
			$('.threadNumDiv').hide();
		}else{
			$('.threadNumDiv').show();
		}


		//初始化第一步描述
		showStep1();
		return true;
	}

	//第一步确认信息显示
	let showStep1 = function(){

		/************获取第一步配置确认信息************/

		//验证方式
		$('.verifymodeshow').html($('#verifyMode .radio-group__item.active strong').text());

		//验证类型
		$('.verifytypeshow').html($('#verifyType').find('.icheck:checked').parent().parent().text());

		//验证报告模板
		if(data.basic_info.template_uuid && data.basic_info.template_uuid != ""){
			$('.templeteshow').html($('select[name=template] option:selected').text());
		}else{
			$('.templeteshow').html(LANG.UI_VERIFY_NOT_SET);
		}
		//审批流
		if(data.basic_info.approval_uuid && data.basic_info.approval_uuid != ""){
			$('.approvalshow').html($('select[name=approval] option:selected').text());
		}else{
			$('.approvalshow').html(LANG.UI_VERIFY_NOT_SET);
		}

		//应用组
		if(data.basic_info.appgroup_uuid && data.basic_info.appgroup_uuid != ""){
			$('.appgroupshow').html($('select[name=appgroup] option:selected').text());
		}else{
			$('.appgroupshow').html(LANG.UI_VERIFY_NOT_SET);
		}

		//虚拟演练室
		$('.virtuallabshow').html($('select[name=virtualLab] option:selected').text());
		if(parseInt($('#labType').find('.icheck:checked').attr('data-mode')) == 1){
			//自动构建
			$('.virtuallabshow').html(LANG.UI_VERIFY_AUTO_SET);
		}
		if (data.basic_info.verify_mode == 1){
			$('.labshowDiv').hide();
		}else{
			$('.labshowDiv').show();
		}

		if(!editFlag){
			//初始化数据验证默认名字
			pAjaxRequest({}, "/api/v1/verification/task_name", "GET", function (result) {

				$('#jobname').val(result.data.task_name);

			});
		}
	}

	//第二步
	let step2Valid = function(){
		//获取被选中的节点
		let nodes = [];
		if (ztree){
			nodes = ztree.getCheckedNodes();
		}
		if(nodes.length == 0){
			UIToastr.showInfo(jobTitle, LANG.UI_VERIFY_ADD_JOB_SELECT_TIPS);
			return false;
		}
		//初始化对象列表
		let list = [];
		let driverList = [];
		if (data.basic_info.verify_mode == 2){
			// 驱动检测结果
			driverList = $.fn.driverCheck.getCheckResult($(`#driverCheck_vm`));
			if (!driverList) {
				UIToastr.showWarning(jobTitle, LANG.UI_VERIFY_DRIVER_CHECK_TIPS);
				return false;
			}else{
				$.each(driverList, function(i,driver){
					if(driverList[i].driver_check_status == 8){
						driverList[i].driver_hw_id_map = "";
					}
				});
			}
		}
		//初始化对象描述
		let objectDes = "";
		let dirverCheckFlag = true;
		for (let i=0;i<nodes.length;i++){
			//如果不是对象节点直接跳过
			if(nodes[i].eventtype != "object") continue;
			let networkFlag = false;

			//初始化验证对象配置空对象
			let general_config = {},verify_config = {},network_config = [], safe_config = {}, driver_config = {}, file_compare_config = {};

			//处理特殊字符转义
			let liID = clearString('object_tree' + nodes[i].task_uuid + nodes[i].object_uuid);

			//通用配置
			general_config.item_uuid = nodes[i].object_uuid;
			general_config.item_name= nodes[i].name;
			general_config.backup_task_uuid = nodes[i].task_uuid;
			general_config.cpu_mode = $('#object'+ liID + ' select[name=cpumode]').val();
			general_config.cpu_arch = $('#object'+ liID + ' select[name=cpuArch]').val();
			general_config.cpu_num = $('#object'+ liID + ' select[name=cpunum]').val();
			general_config.core_num = $('#object'+ liID + ' select[name=corenum]').val();
			general_config.memory_size = $('#object'+ liID + ' select[name=memory]').val();
			general_config.os_type = OS_TYPE[parseInt($('#object'+ liID + ' select[name=ostype]').val())];
			general_config.os_version = parseInt($('#object'+ liID + ' select[name=osversion]').val());
			general_config.os_version_name = $('#object'+ liID + ' select[name=osversion]').find('option:selected').data('name');
			general_config.disk_target_bus = parseInt($('#object'+ liID + ' select[name=disk_target_bus]').val());
			general_config.netcard_target_bus = parseInt($('#object'+ liID + ' select[name=netcard_target_bus]').val());
			general_config.start_vm_flag = false;
			general_config.module_type= nodes[i].module_type;
			general_config.submodule_type= nodes[i].submodule_type;
			general_config.role = 1;	//验证对象角色
			//检查CPU架构和操作系统类型和版本是否选择
			if(data.basic_info.verify_mode != 1){
				if(general_config.cpu_arch == "0" || general_config.os_type == "0" || general_config.os_version == "0"){
					UIToastr.showWarning(jobTitle, LANG.UI_VERIFY_OBJECT+"("+nodes[i].name + ")" +LANG.UI_VERIFY_OBJECT_HARDWARE_INFO_SET_TIPS);
					return false;
				}
			}
			if(data.basic_info.automatic_verifitied_flag == 1){
				//手动验证支持自动开机
				general_config.start_vm_flag = $('#object'+liID+" .autostartCheck").get(0).checked;
			}
			//验证配置

			verify_config.timepoint_uuid_list = {};
			verify_config.timepoint_uuid_list.timepoint_uuids = [];
			verify_config.timepoint_range = objectTimepointInfo[liID]?objectTimepointInfo[liID].timepoint_range:$('.select_point_' + liID).data('pointtype');
			verify_config.max_timepoint_verify = 1;
			if(verify_config.timepoint_range == 3 && nodes[i].module_type != CONF.MODULE_TYPE.VOL_CDP){
				//指定时间点
				verify_config.timepoint_uuid_list.timepoint_uuids = pointList[nodes[i].id];
				verify_config.max_timepoint_verify = $('.select_point_' + liID).data('maxnum');
			}else if(verify_config.timepoint_range == 1){
				//所有未验证时间点
				verify_config.max_timepoint_verify = $('.select_point_' + liID).data('maxnum');
			}

			//处理整机实时
			if(nodes[i].module_type == CONF.MODULE_TYPE.VOL_CDP){
				let timepoint = $('.select_point_' + liID).data('timepoint');
				let timepointuuid = $('.select_point_' + liID).data('uuid');
				let systemFlag = $('.select_point_' + liID).data('system');

				let info = {
					timepoint_uuid: timepointuuid,
					timepoint: timepoint,
					backup_mode: 0
				}
				verify_config.timepoint_uuid_list.timepoint_uuids = [info];
				verify_config.vol_cdp_datetime = timepoint;
				if (nodes[i].timepointInfo){
					if(!systemFlag && data.basic_info.verify_mode == 2){
						UIToastr.showWarning(jobTitle, LANG.UI_VERIFY_NOT_FIND_SYSTEM_DISK_TIPS);
						return false;
					}
				}
			}

			verify_config.ping_test_flag = $('#object' +liID+" .pingCheck").get(0).checked;
			verify_config.heartbeat_flag = $('#object' +liID+" .heartCheck").get(0).checked;
			verify_config.print_screen_flag = $('#object' +liID+" .screenCheck").get(0).checked;
			verify_config.max_boot_time = $('#object' +liID+" input[name=boottime]").val();
			verify_config.max_ping_wait_time = $('#object' +liID+" input[name=max_ping_wait_time]").val();

			//备份数据验证及安全扫描验证默认关闭
			if (data.basic_info.verify_mode == 1){
				verify_config.ping_test_flag = false;
				verify_config.heartbeat_flag = false;
				verify_config.print_screen_flag = false;
			}

			//网络配置
			network_config = getNetworkConf(liID, '#object');
			//必须配置一张网卡信息
			if (network_config.length != 0){
				for (let j=0;j<network_config.length; j++){
					// 输入验证
					if(network_config[j].ip != "" && network_config[j].netmask != "" && customInputValidate('ipv4',$.trim(network_config[j].ip) ) && netmaskVal.test(network_config[j].netmask) ){
						networkFlag = true
					}

				}
			}

			//检查网卡信息
			if(!networkFlag && data.basic_info.verify_mode == 2){
				UIToastr.showWarning(jobTitle, LANG.UI_VERIFY_SET_NETWORK_INFO_TIPS);
				return false;
			}

			//获取验证源描述信息
			objectDes += nodes[i].task_name + "/" + nodes[i].name + "/" + $('#object' + escapeJquery(liID) + ' .select_point_' + liID).text() + "</br>";
			//获取病毒扫描参数
			let virusConfig = $('#virusConfig_'+liID).getVirusDetectionBackup();
			if (virusConfig === false) {  // 验证病毒扫描配置内容是否符合要求
				return false;
			}
			//安全配置
			safe_config.vir_det_kill_flag = virusConfig.virus_scan_flag;
			safe_config.virus_scan_config_list = JSON.stringify(virusConfig.virus_scan_config_list);

			if (safe_config.vir_det_kill_flag  == 1){
				//开启病毒扫描后验证扫描线程数
				let scanNum = $('#virusConfig_'+liID + '_scan-thread').val();
				if (parseInt(scanNum) > parseInt(general_config.core_num)){
					UIToastr.showWarning(jobTitle, LANG.UI_VERIFY_OBJECT+"("+nodes[i].name + ")" +LANG.UI_VERIFY_OBJECT_VIRUS_SCAN_NUM_ERROR_TIPS);
					return false;
				}
			}

			//完整性校验
			safe_config.integrity_check_flag = $('#object' +liID+" .integrity_check_flag").get(0).checked;


			//文件对比验证配置
			if ($('#oem_version').val() == CONF.VENDOR_LIST.gmp && (nodes[i].module_type == 5 || nodes[i].module_type == 10)){
				file_compare_config.doc_consistency_flag = $('#object' +liID+" .compare_mode").val();
				let list = [];
				selectfile = true;
				if(zTreeFile[liID]){
					//树对象存在
					let checkedNodes = zTreeFile[liID].getCheckedNodes();
					if (checkedNodes.length == 0){
						selectfile = false;
					}
					checkedNodes = filterFile(checkedNodes);

					for (let j=0;j<checkedNodes.length; j++){
						let fileInfo = {};
						fileInfo.doc_path = checkedNodes[j].filepath;
						fileInfo.encode = checkedNodes[j].code_type;
						fileInfo.path_name =  checkedNodes[j].filepath;
						fileInfo.path_type =  checkedNodes[j].type;
						list.push(fileInfo);
					}
				}
				file_compare_config.doc_list = list;
				threadShowFlag = true;
			}else{
				file_compare_config.doc_consistency_flag =2;
				file_compare_config.doc_list = [];
			}
			//驱动检测
			for (let j=0; j<driverList.length; j++){
				if(!nodes[i].timepointInfo) continue;
				if(driverList[j].timepoint_uuid == nodes[i].timepointInfo.timepoint_uuid || (nodes[i].module_type == CONF.MODULE_TYPE.VOL_CDP && driverList[j].timepoint_uuid == nodes[i].timepointInfo.new_timepoint_uuid)){
					driver_config = driverList[j];
					if(driverList[j].driver_check_status == 8){
						dirverCheckFlag = false;
					}
				}
			}
			//整合参数
			let item = {
				general_config: general_config,
				verify_config: verify_config,
				network_config: network_config,
				safe_config: safe_config,
				driver_config: driver_config,
				file_compare_config: file_compare_config
			};

			//添加到列表里
			list.push(item);

		}

		//选择应用组
		data.basic_info.appgroup_uuid = $('select[name=appgroup]').val();
		if(data.basic_info.appgroup_uuid != ""){
			//组装应用组配置信息
			for(var i = 0;i<appgroupList.length;i++){

				let networkFlag = false;
				//初始化验证对象配置空对象
				let general_config = {},verify_config = {},network_config = [], safe_config = {}, driver_config = {}, file_compare_config = {};

				//处理特殊字符转义
				let liID = clearString('object_tree' + appgroupList[i].general_config.task_uuid + appgroupList[i].general_config.item_uuid);

				//通用配置
				general_config.item_uuid = appgroupList[i].general_config.item_uuid;
				general_config.item_name= appgroupList[i].general_config.item_name;
				general_config.backup_task_uuid = appgroupList[i].general_config.task_uuid;
				general_config.cpu_mode = $('#appgroup'+ liID + ' select[name=cpumode]').val();
				general_config.cpu_arch = $('#appgroup'+ liID + ' select[name=cpuArch]').val();
				general_config.cpu_num = $('#appgroup'+ liID + ' select[name=cpunum]').val();
				general_config.core_num = $('#appgroup'+ liID + ' select[name=corenum]').val();
				general_config.memory_size = $('#appgroup'+ liID + ' select[name=memory]').val();
				general_config.os_type = OS_TYPE[parseInt($('#appgroup'+ liID + ' select[name=ostype]').val())];
				general_config.os_version = parseInt($('#appgroup'+ liID + ' select[name=osversion]').val());
				general_config.start_vm_flag = false;
				general_config.module_type= appgroupList[i].general_config.module_type;
				general_config.submodule_type= appgroupList[i].general_config.sub_module_type;
				general_config.role = 2;	//应用组对象角色
				if(data.basic_info.automatic_verifitied_flag == 1){
					//手动验证支持自动开机
					general_config.start_vm_flag = $('#appgroup' +liID+" .autostartCheck").get(0).checked;
				}

				//验证配置
				verify_config.timepoint_uuid_list = {};
				verify_config.timepoint_uuid_list.timepoint_uuids = [];
				verify_config.timepoint_range = $('.select_point_' + liID).data('pointtype');
				if(verify_config.timepoint_range == 3){
					//指定时间点
					verify_config.timepoint_uuid_list.timepoint_uuids = pointList[nodes[i].id];
				}else if(verify_config.timepoint_range == 1){
					//所有未验证时间点
					verify_config.max_timepoint_verify = $('.select_point_' + liID).data('maxnum');
				}

				verify_config.ping_test_flag = $('#appgroup' +liID+" .pingCheck").get(0).checked;
				verify_config.heartbeat_flag = $('#appgroup' +liID+" .heartCheck").get(0).checked;
				verify_config.print_screen_flag = $('#appgroup' +liID+" .screenCheck").get(0).checked;
				verify_config.max_boot_time = $('#appgroup' +liID+" input[name=boottime]").val();

				//备份数据验证及安全扫描验证默认关闭
				if (data.basic_info.verify_mode == 1){
					verify_config.ping_test_flag = false;
					verify_config.heartbeat_flag = false;
					verify_config.print_screen_flag = false;
				}

				//网络配置
				network_config = getNetworkConf(liID, '#appgroup');

				//必须配置一张网卡信息
				if (network_config.length != 0){
					for (let j=0;j<network_config.length; j++){
						// 输入验证
						if(network_config[j].ip != "" && network_config[j].netmask != "" && (customInputValidate('ipv4',$.trim(network_config[j].ip) ) || customInputValidate('ipv6',$.trim(network_config[j].ip) ) ) && netmaskVal.test(network_config[j].netmask) ){
							networkFlag = true
						}

					}
				}
				//检查网卡信息
				if(!networkFlag && data.basic_info.verify_mode == 2){
					UIToastr.showWarning(jobTitle, LANG.UI_VERIFY_SET_NETWORK_INFO_TIPS);
					return false;
				}

				//获取验证源描述信息
				// objectDes += nodes[i].task_name + "/" + nodes[i].name + "/" + $('#object' + escapeJquery(liID) + ' .select_point_' + liID).text() + "</br>";
				//获取病毒扫描参数
				let virusConfig = $('#virusConfig_'+liID).getVirusDetectionBackup();
				//安全配置
				safe_config.vir_det_kill_flag = virusConfig.virus_scan_flag;
				safe_config.virus_scan_config_list = JSON.stringify(virusConfig.virus_scan_config_list);
				safe_config.integrity_check_flag = $('#appgroup' +liID+" .integrity_check_flag").get(0).checked;

				//文件对比验证配置
				if ($('#oem_version').val() == CONF.VENDOR_LIST.gmp && (appgroupList[i].general_config.module_type == 5 || appgroupList[i].general_config.module_type == 10)){
					file_compare_config.doc_consistency_flag = $('#appgroup' +liID+" .compare_mode").val();
					let checkedNodes = zTreeFile[liID].getCheckedNodes();
					checkedNodes = filterFile(checkedNodes);

					let list = [];
					for (let j=0;j<checkedNodes.length; j++){
						let fileInfo = {};
						fileInfo.doc_path = checkedNodes[j].filepath;
						fileInfo.encode = checkedNodes[j].code_type;
						fileInfo.path_name =  checkedNodes[j].filepath;
						fileInfo.path_type =  checkedNodes[j].type;
						list.push(fileInfo);
					}
					file_compare_config.doc_list = list;
					threadShowFlag = true;
				}else{
					file_compare_config.doc_consistency_flag = 1;
					file_compare_config.doc_list = [];
				}

				//驱动检测
				for (let j=0; j<driverList.length; j++){
					if(driverList[j].timepoint_uuid == appgroupList[i].verify_config.newest_timepoint.timepoint_uuid){
						driver_config = driverList[j];
						if(driverList[j].driver_check_status == 8){
							dirverCheckFlag = false;
						}
					}
				}
				//整合参数
				let item = {
					general_config: general_config,
					verify_config: verify_config,
					network_config: network_config,
					safe_config: safe_config,
					driver_config: driver_config,
					file_compare_config: file_compare_config
				};

				//添加到列表里
				list.push(item);
			}
		}


		data.item_list = list;
		// data.node_uuid = $('#storagetypeselect ').find('option:selected').attr('nodeuuid');
		data.node_uuid = $('#nodeSelect ').val();
		data.storage_uuid = $('#storagetypeselect ').val();


		if(data.basic_info.automatic_verifitied_flag == 1){
			$('#startType').val(1);
			$('#setstrategy').hide();
		}

		//显示文件对比线程数量配置项
		if(threadShowFlag){
			$('.threadShowDiv').show();
		}else{
			$('.threadShowDiv').hide();
		}

		//初次化存储挂载
		initStorageMount();
		//初始化备份节点IP
		initBackupServerAddr(data.node_uuid);


		if(!dirverCheckFlag){
			UIToastr.showInfo(jobTitle, LANG.UI_DRIVER_CHECK_RESULT_NONSUPPORT_OS_INSTALL_DRIVER);
		}

		//展示第二步信息
		showStep2(objectDes);
		//检查授权
		if(!getVerifyCurrentUseLicense()){
			return false;
		}
		return true;
	}

	//过滤文件
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
		return allCheckedNode;
	}

	//网络配置
	let getNetworkConf = function(liID,div){
		let list = [];
		let network = networkUuidList[liID];
		if (!network) return list;
		for(let i=0; i< network.length; i++){
			let info = {};
			info.network_name = $(div + liID + " #network_" + networkUuidList[liID][i] + " input[name=networkname]").val();
			info.ip = $(div + liID + " #network_" + networkUuidList[liID][i] + " input[name=ipaddress]").val();
			info.gateway = $(div + liID + " #network_" + networkUuidList[liID][i] + " input[name=gateway]").val();
			info.netmask =$(div + liID + " #network_" + networkUuidList[liID][i] + " input[name=netmask]").val();
			info.mactype =$(div + liID + " #network_" + networkUuidList[liID][i] + " select[name=mactype]").val();
			if(info.mactype == 1){
				info.mac_address =$(div + liID + " #network_" + networkUuidList[liID][i] + " input[name=macaddress]").val();
			}else{
				info.mac_address = "";
			}
			list.push(info);
		}

		return list;

	}

	//第二步确认信息显示
	let showStep2 = function(des){
		$('.srcinfoshow').html(des);
		initResourceLimit([data.node_uuid]);
	}

	//第三步
	let step3Valid = function(){
		//获取时间策略
		let getTimeStr = function(){
			data.time_strategy.type = parseInt($('#startType').val());
			//如果是按时间策略
			if(2 == data.time_strategy.type){
				let strategyConfig = $('#verifyTimestrategy').getStrategyConfig();
				data.time_strategy.strategy = strategyConfig.verifyInfo;
				data.time_strategy.strategy.rollFlag = 2;
				data.time_strategy.strategy.rollInterval = 0;
			}

			return true;
		}


		//获取高级策略
		let getHighStr = function(){
			let mountInfo = false;
			//判断对象存在再处理
			if (typeof storageMount !== "undefined"){
				mountInfo = storageMount.getAmountInfo();
			}
			if(mountInfo == false){
				UIToastr.showWarning(jobTitle, LANG.UI_VERIFY_GET_STORAGE_MOUNT_INFO_TIPS);
				return false;
			}
			data.high_strategy.limit_boot_vm_num = parseInt($('#vmnum').val());
			data.high_strategy.doc_compare_thread = parseInt($('#fileThreadNum').val());
			
			//并发验证对象数量必须为正整数

			if(!data.high_strategy.limit_boot_vm_num || data.high_strategy.limit_boot_vm_num < 1){
				$('#hostNumDiv').spinner("value", 1);
				UIToastr.showWarning(LANG.UI_VERIFY_OBJECT_NUM, LANG.UI_VERIFY_OBJECT_NUM_WARNING_TIPS);
				return false;
			}

			var thread = parseInt($('#fileThreadNum').val());
			if(!thread || thread > 10 || thread <= 0){
				//重置为默认值
				$('.threadDiv').spinner("value", 1);
				UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_VERIFY_INIT_FILE_THREAD_NUM_TIPS);
				return false;
			}

			//存储挂载点IP或域名
			data.high_strategy.backup_server_ip = $('#serveripaddr').val();

			//存储挂载点IP或域名
			data.high_strategy.nfs_server_ip = mountInfo.ip_domain;

			//
			data.high_strategy.limit_ip = mountInfo.limit_ip;

			data.high_strategy.mount_protocol = mountInfo.protocol;
			data.high_strategy.ignore_resource_limiting_flag = $('#ignore_resource_limit').get(0).checked;

			return true;
		}

		//获取策略配置信息
		let result = getTimeStr() && getHighStr();

		if(result){
			showStep3();
		}

		return result;
	}

	//第三步确认信息显示
	let showStep3 = function(){
		//定义时间策略信息
		let timeStrategyStr = $('#startType option:selected').text();

		//按时间策略读取信息
		if(2 == data.time_strategy.type){
			timeStrategyStr = data.time_strategy.strategy.des;
		}

		//时间策略信息显示
		$('.timestrategyshow').html(timeStrategyStr);

		//定义高级配置信息
		let highStr = "";

		//选择第三方虚拟化
		if(data.basic_info.virtual_lab_uuid != ""){
			//存储挂载点IP
			highStr += LANG.UI_VERIFY_MOUNT_IP_OR_DOMAIN + ": " +  data.high_strategy.nfs_server_ip + "<br>";

			//挂载协议
			highStr += LANG.UI_VERIFY_MOUNT_PROTOCOL + ": " +  $('.amount_service option:selected').text() + "<br>";
			if(data.high_strategy.limit_ip){
				highStr += LANG.UI_VERIFY_VISIT_IP + ": " + data.high_strategy.limit_ip + "<br>";
			}
		}

		//备份节点IP地址
		highStr += $('.serveriplabel').text() + ": " +  data.high_strategy.backup_server_ip + "<br>";

		//自动验证
		if(data.basic_info.automatic_verifitied_flag == 0 && $('#oem_version').val() != CONF.VENDOR_LIST.gmp){
			//同时处理机器数量
			highStr += $('.vmnumlabel').text() + ": " +  data.high_strategy.limit_boot_vm_num + "<br>";
		}

		if (threadShowFlag){
			//线程数量
			highStr += $('.threadlabel').text() + ": " +  data.high_strategy.doc_compare_thread + "<br>";
		}

		// 忽略资源限制
		highStr +=  $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.high_strategy.ignore_resource_limiting_flag);

		//高级配置信息显示
		$('.highshow').html(highStr);

	}


	//提交
	let submit = function(){
		//输入任务名检查
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_VERIFY_INPUT_TASK_NAME).show();
			return;
		}
		$('.jobnametip').hide();
		// 输入验证
		if(!customInputValidate('string',$.trim($("#jobname").val()))){
			return false;
		}
		data.task_name = $.trim($("#jobname").val());


		if (editFlag) { // 修改数据验证任务
			if (pageIndex === 0) {
				if (!step1Valid()) {
					return false;
				}
			} else if (pageIndex === 1) {
				if (!step2Valid()) {
					return false;
				}
			} else if (pageIndex === 2) {
				if (!step3Valid()) {
					return false;
				}
			}
			data.task_uuid = $('#task_uuid').val();
			//锁住界面
			Metronic.blockUI({target: '#verificationContent',animate: true,cenrerY: true,});

			pAjaxRequest(data, "/api/v1/verification", "PUT", function (result) {
				//解锁界面
				Metronic.unblockUI('#verificationContent');

				//处理返回消息
				if (result.success) {

					//立即验证
					if(data.time_strategy.type == 1){
						let params = {
							'start_type': 1
						};
						pAjaxRequest(params, "/api/v1/jobs/start/" + result.data.info.sr_task_uuid + "", 'POST', function (data) {

						});
					}
					//生成任务报告
					if ($('#oem_version').val() == CONF.VENDOR_LIST.gmp ){
						initJobReport(data.task_uuid);
					}else{
						LOCATION('./content/platform/jobs/jobs.php', 'task');
					}
				} else {
					UIToastr.showWarning(LANG.UI_VERIFY_EDIT_JOB_FAILD, result.message);
				}

			});

		} else { //创建数据验证任务
			//锁住界面
			Metronic.blockUI({target: '#verificationContent',animate: true,cenrerY: true,});

			pAjaxRequest(data, "/api/v1/verification", "POST", function (result) {
				//解锁界面
				Metronic.unblockUI('#verificationContent');

				//处理返回消息
				if (result.success) {
					//立即验证
					if(data.time_strategy.type == 1){
						let params = {
							'start_type': 1
						};
						pAjaxRequest(params, "/api/v1/jobs/start/" + result.data.info.sr_task_uuid + "", 'POST', function (data) {

						});
					}
					//生成任务报告
					if ($('#oem_version').val() == CONF.VENDOR_LIST.gmp ){
						initJobReport(result.data.info.sr_task_uuid);
					}else{
						LOCATION('./content/platform/jobs/jobs.php', 'task');
					}

				} else {
					UIToastr.showWarning(LANG.UI_VERIFY_ADD_JOB_SUCCESS, result.message);
				}

			});
		}


	}

	let initJobReport = function(taskuuid){
		// 获取配置
		let info = TemplateChoose.getInfo();
		// 模拟接口请求返回信息
		// 请求接口，返回成功跳转页面
		let list = [];
		for (let i=0;i<data.item_list.length;i++){
			let timepointuuids = data.item_list[i].verify_config.timepoint_uuid_list.timepoint_uuids;
			let timepoint = "";
			if(timepointuuids.length !=0){
				timepoint = data.item_list[i].verify_config.timepoint_uuid_list.timepoint_uuids[0];
			}
			let p = {
				'timepoint_uuid': timepoint,
				'agent_uuid': data.item_list[i].general_config.item_uuid
			}
			list.push(p);
		}
		let params = {
			test: 2,
			temp_uuid: $('select[name=template]').val(),
			approval_uuid: $('select[name=approval]').val(),
			module_type:  $('select[name=module]').selectpicker('val'),
			job_uuid: taskuuid,
			job_name: $('#jobname').val(),
			agent: list,
			template: info
		};
		pAjaxRequest(params, "/api/v1/industry/report", "POST", function (result) {
			UIToastr.showSuccess(jobTitle, jobTitle+LANG.UI_PUBLIC_SUCCESS);
			if ($('#oem_version').val() == CONF.VENDOR_LIST.gmp ){
				LOCATION('./content/platform/jobs/verify_jobs.php', 'verification_job');
			}else{
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}
		});
	}

	/******************修改数据验证任务*******************/
	let editVerifyJob = function(){
		let taskuuid = $('#task_uuid').val();
		if(taskuuid && taskuuid != ""){
			editFlag = true;
		}

		$('.orderAsc').hide();
		initSpinner();		//初始化个数选择器
		//检查容灾演练平台授权屏蔽虚拟演练室自动构建
		if ($('#oem_version').val() != CONF.VENDOR_LIST.gmp){
			if($.inArray("dataVerificationDR", CONF.FUNCTIONS) == -1){
				$('.labTypeDiv').hide();
				$('#labType').find('input[data-mode=2]').iCheck('check');
				$('#labType').find('input[data-mode=1]').iCheck('uncheck');
				$('.virtualLabDiv').show();
			}else{
				$('.labTypeDiv').show();
				$('#labType').find('input[data-mode=1]').iCheck('check');
				$('#labType').find('input[data-mode=2]').iCheck('uncheck');
				$('.virtualLabDiv').hide();
			}
		}
		//初始化修改信息
		if(editFlag){
			jobTitle = LANG.UI_VERIFY_EDIT_JOB
			pAjaxRequest({}, "/api/v1/verification/job/"+taskuuid, "GET", function (result) {
				_SETTINGS = result.data;
				initOldData(result.data);
				initStep1Settings(result.data);
				initStep2Settings(result.data);
				initStep3Settings(result.data);
			});
		}else{
			if (CONF.VENDOR == "sangfor"){
				$('#verifyType').find('input[data-mode=0]').iCheck('uncheck');
				$('#verifyType').find('input[data-mode=1]').iCheck('check');
			}
			initVerifyTemplate();	//初始化验证报告模板选择
			initApproval();	//初始化审批流选择
			initAppGroup();	//初始化应用组选择
			initLabSelect();	//初始化虚拟机实验室选择
			initTimeStretegy();	//初始化时间策略
		}

	}

	//初始化节点下拉框
	var initNodeSelect = function() {
		var p = {};
		p.storage_uuid = $('#storagetypeselect').val();
		pAjaxRequest(p, "/api/v1/nodes/storage", "GET", function (d) {
			let list = d.data;
			var nodeSelect = $('#nodeSelect');
			var labNodeuuid =$('select[name=virtualLab] ').find('option:selected').data('nodeuuid');
			var labHypervisor =$('select[name=virtualLab] ').find('option:selected').data('type');
			nodeSelect.empty();
			for (var i = 0; i < list.length; i++) {
				if (labHypervisor == 108 && data.basic_info.virtual_lab_uuid != "" && list[i].uuid != labNodeuuid) continue;
				var option = $("<option>").text(list[i].text).val(list[i].uuid);
				nodeSelect.append(option);

			}
			if(editFlag && _SETTINGS.basic_info.node_uuid){
				$('#nodeSelect').val(_SETTINGS.basic_info.node_uuid);
			}
		}, false);
	}

	//初始化数据验证每个步骤配置信息
	let initOldData = function(settings){
		//任务uuid
		data.task_uuid = settings.basic_info.task_uuid;

		//任务名称
		data.task_name = settings.basic_info.task_name;

		//节点uuid
		data.node_uuid = settings.basic_info.node_uuid;
		data.storage_uuid = settings.basic_info.storage_uuid;

		//基础信息
		data.basic_info = {
			verify_mode: settings.basic_info.verify_mode,
			automatic_verifitied_flag: settings.basic_info.automatic_verifitied_flag,
			appgroup_uuid: settings.high_strategy.appgroup_uuid,
			virtual_lab_uuid: settings.high_strategy.virtual_lab_uuid,
			template_uuid: settings.basic_info.template_uuid,
			approval_uuid: settings.basic_info.approval_uuid,
		};

		//对象列表
		data.item_list = [];
		for (let i=0;i<settings.item_list.length;i++){
			//初始化验证对象配置空对象
			let general_config = {},verify_config = {},network_config = [], safe_config = {}, driver_config = {}, file_compare_config = {};

			//通用配置
			general_config.item_uuid = settings.item_list[i].general_config.item_uuid;
			general_config.item_name=  settings.item_list[i].general_config.item_name;
			general_config.backup_task_uuid = settings.item_list[i].general_config.task_uuid;
			general_config.cpu_mode = settings.item_list[i].general_config.cpu_mode;
			general_config.cpu_arch = settings.item_list[i].general_config.cpu_arch;
			general_config.cpu_num = settings.item_list[i].general_config.cpu_num;
			general_config.core_num = settings.item_list[i].general_config.core_num;
			general_config.memory_size = settings.item_list[i].general_config.memory_size;
			general_config.memory_size_int = settings.item_list[i].general_config.memory_size_int;
			general_config.memory_size_unit = settings.item_list[i].general_config.memory_size_unit;
			general_config.os_type = settings.item_list[i].general_config.os_type;
			general_config.os_version = settings.item_list[i].general_config.os_version;
			general_config.disk_target_bus = settings.item_list[i].general_config.disk_target_bus;
			general_config.netcard_target_bus = settings.item_list[i].general_config.netcard_target_bus;
			general_config.start_vm_flag = settings.item_list[i].general_config.start_vm_flag;
			general_config.module_type = settings.item_list[i].general_config.module_type;
			general_config.submodule_type = settings.item_list[i].general_config.submodule_type;
			general_config.role = settings.item_list[i].verify_config.role;	//验证对象角色

			//验证配置
			verify_config.timepoint_uuid_list = {};
			verify_config.timepoint_uuid_list.timepoint_uuids = settings.item_list[i].verify_config.timepoint_uuid_list.timepoint_uuids;
			verify_config.timepoint_range = settings.item_list[i].verify_config.timepoint_uuid_list.timepoint_range;
			verify_config.max_timepoint_verify = settings.item_list[i].verify_config.timepoint_uuid_list.max_timepoint_verify;
			verify_config.vol_cdp_datetime = settings.item_list[i].verify_config.timepoint_uuid_list.vol_cdp_datetime;
			//初始化时间点信息保存
			let liID = clearString("object_tree" + settings.item_list[i].general_config.task_uuid + settings.item_list[i].general_config.item_uuid);
			objectTimepointInfo[liID] = {};
			objectTimepointInfo[liID].timepoint_range = settings.item_list[i].verify_config.timepoint_uuid_list.timepoint_range;
			objectTimepointInfo[liID].max_timepoint_verify = settings.item_list[i].verify_config.timepoint_uuid_list.max_timepoint_verify;
			objectTimepointInfo[liID].timepoint_uuids = settings.item_list[i].verify_config.timepoint_uuid_list.timepoint_uuids;
			objectTimepointInfo[liID].vol_cdp_datetime = settings.item_list[i].verify_config.timepoint_uuid_list.vol_cdp_datetime;

			verify_config.ping_test_flag = settings.item_list[i].verify_config.ping_test_flag;
			verify_config.heartbeat_flag = settings.item_list[i].verify_config.heartbeat_flag;
			verify_config.print_screen_flag = settings.item_list[i].verify_config.print_screen_flag;
			verify_config.max_boot_time = settings.item_list[i].verify_config.max_boot_time;
			verify_config.max_ping_wait_time = settings.item_list[i].verify_config.max_ping_wait_time;


			//网络配置
			network_config = settings.item_list[i].network_config;

			//安全配置
			safe_config.vir_det_kill_flag = settings.item_list[i].verify_config.vir_det_kill_flag;
			safe_config.virus_scan_config_list = JSON.stringify(settings.item_list[i].verify_config.virus_scan_config_list);
			safe_config.integrity_check_flag = settings.item_list[i].verify_config.integrity_check_flag;

			//文件对比验证配置
			if ($('#oem_version').val() == CONF.VENDOR_LIST.gmp && (settings.item_list[i].general_config.module_type == 5 || settings.item_list[i].general_config.module_type == 10)){
				file_compare_config.doc_consistency_flag = settings.item_list[i].verify_config.doc_consistency_flag;
				if(settings.item_list[i].verify_config.doc_list){
					file_compare_config.doc_list = settings.item_list[i].verify_config.doc_list;
				}else{
					file_compare_config.doc_list = [];
				}
			}else{
				file_compare_config.doc_consistency_flag = 2;
				file_compare_config.doc_list = [];
			}
			//驱动检测
			driver_config.driver_check_status = settings.item_list[i].verify_config.driver_check_status;
			driver_config.driver_hw_id_map = settings.item_list[i].verify_config.driver_hw_id_map;

			//整合参数
			let item = {
				general_config: general_config,
				verify_config: verify_config,
				network_config: network_config,
				safe_config: safe_config,
				driver_config: driver_config,
				file_compare_config: file_compare_config
			};

			//添加到列表里
			data.item_list.push(item);
		}

		let strategy = {
			'mode': settings.time_strategy.info.mode,
			'type': settings.time_strategy.info.strategy_type,
			'days': settings.time_strategy.info.days,
			'startTime': settings.time_strategy.info.start_time,
			'roll_flag': settings.time_strategy.info.roll_flag,
			'roll_interval': settings.time_strategy.info.roll_interval,
			'endTime': settings.time_strategy.info.roll_end_time,
			'frequency': settings.time_strategy.info.frequency
		}

		//时间策略
		data.time_strategy = {
			type: settings.time_strategy.type,
			strategy: strategy
		};


		//高级配置
		data.high_strategy = {
			limit_boot_vm_num: settings.high_strategy.limit_boot_vm_num,
			mount_protocol: settings.high_strategy.mount_protocol,
			backup_server_ip: settings.high_strategy.backup_server_ip,
			nfs_server_ip: settings.high_strategy.nfs_server_ip,
			doc_compare_thread: settings.high_strategy.doc_compare_thread,
			mount_protocol: settings.high_strategy.mount_protocol,
			ignore_resource_limiting_flag: settings.high_strategy.ignore_resource_limiting_flag,
			limit_ip: "",
		};

	}

	//初始化第一步配置信息
	let initStep1Settings = function(settings){
		let mode = settings.basic_info.verify_mode;
		//验证方式
		if($('#oem_version').val() == CONF.VENDOR_LIST.gmp){
			mode = 2;

		}
		$('#verifyMode label').removeClass('active');
		$('#verifyMode label[value='+mode+']').addClass('active');
		$('#verifyMode').change();	//根据验证方式初始化显示

		//验证方式修改界面回显
		if(mode == 1){
			$('.appgroupDiv').hide();
			$('.labTypeDiv').hide();
			$('.virtualLabDiv').hide();
			$('.verifyTypeDiv').hide();
			$('#verifyType').find('input[data-mode=1]').iCheck('uncheck');
			$('#verifyType').find('input[data-mode=0]').iCheck('check');
		}else{
			$('.appgroupDiv').show();
			if ($('#oem_version').val() != CONF.VENDOR_LIST.gmp){
				if($.inArray("dataVerificationDR", CONF.FUNCTIONS) == -1){
					$('.labTypeDiv').hide();
					$('#labType').find('input[data-mode=2]').iCheck('check');
					$('#labType').find('input[data-mode=1]').iCheck('uncheck');
					$('.virtualLabDiv').show();
				}else{
					$('.labTypeDiv').show();
					$('#labType').find('input[data-mode=1]').iCheck('check');
					$('#labType').find('input[data-mode=2]').iCheck('uncheck');
					$('.virtualLabDiv').hide();
				}
			}

		}

		//验证类型
		if(settings.basic_info.automatic_verifitied_flag == 1){
			$('#verifyType').find('input[data-mode=1]').iCheck('check');
			$('#verifyType').find('input[data-mode=0]').iCheck('uncheck');
			$('#startType').attr('disabled', true);
		}else{
			$('#verifyType').find('input[data-mode=0]').iCheck('check');
			$('#verifyType').find('input[data-mode=1]').iCheck('uncheck');
		}


		initVerifyTemplate();	//初始化验证报告模板选择
		initApproval();	//初始化审批流选择
		initLabSelect();	//初始化虚拟机实验室选择
		//任务名
		$('#jobname').val(settings.basic_info.task_name);

		//虚拟演练室
		if(settings.high_strategy.virtual_lab_uuid == ""){
			$('#labType').find('input[data-mode=1]').iCheck('check');
		}else{
			if($('#oem_version').val() != CONF.VENDOR_LIST.gmp) {
				$('.virtualLabDiv').show();
				$('#labType').find('input[data-mode=1]').iCheck('uncheck');
				$('#labType').find('input[data-mode=2]').iCheck('check');
			}
		}


	}

	//初始化第二步配置信息
	let initStep2Settings = function(settings){
		// initNodeSelect(settings.basic_info.node_uuid);
		// initStorageShowType();
	}

	//初始化第三步配置信息
	let initStep3Settings = function(settings){
		//初始化时间策略
		//启动方式
		$('#startType').val(settings.time_strategy.type);
		if(settings.time_strategy.type == 2){

			//按时间策略验证
			initTimeStretegy(settings.time_strategy.info);	//初始化时间策略
			$('#setstrategy').show();

		}else{
			//立即验证
			$('#setstrategy').hide();
			initTimeStretegy();
		}

		//初始化高级配置
		//同时处理机器数量
		$('#vmnum').val(settings.high_strategy.limit_boot_vm_num);
		$('#fileThreadNum').val(settings.high_strategy.doc_compare_thread);

		//存储挂载点IP或域名
		$('input[name=backupserveraddr]').val(settings.high_strategy.nfs_server_ip);

		//选择挂载IP
		if(selectIPFlag){
			//自动选择
			$('#serveripaddr').val(settings.high_strategy.nfs_server_ip);
		}

		//挂载协议
		$('#protocol').val(settings.high_strategy.mount_protocol);

		//过载保护
		$('#ignore_resource_limit').bootstrapSwitch('state', settings.high_strategy.ignore_resource_limiting_flag);
	}




	/******************一些初始化函数************************/

		//初始化验证报告模板选择
	let initVerifyTemplate = function(){
			let params = {};
			params.offset = 0;
			params.limit = 100;
			pAjaxRequest(params, "/api/v1/industry/templates", "GET", function (result) {
				let list = result.data.rows;
				let template =$('select[name=template]');
				template.empty();
				for(let i=0; i<list.length; i++){
					let option = $("<option>").text(list[i].name).val(list[i].temp_uuid).attr('data-flag', list[i].show_files);
					template.append(option);
				}

				if (editFlag){
					let taskuuid = $('#task_uuid').val();
					template.val(_SETTINGS.basic_info.temp_uuid);
					if(_SETTINGS.basic_info.temp_uuid != _Template){
						TemplateChoose.init({uuid: _SETTINGS.basic_info.temp_uuid, 'source': 2, init:2, job_uuid: taskuuid});
						_Template = _SETTINGS.basic_info.temp_uuid;
					}

				}else{
					let templateuuid = $('select[name=template]').val();
					if(templateuuid != _Template){
						TemplateChoose.init({uuid: templateuuid, 'source': 2, init:2, again_init:true});
						_Template = templateuuid;
					}
				}

			});
		}

	//初始化审批流选择
	let initApproval = function(){
		let params = {};
		params.sort = "classify_name";
		params.order = "desc";

		pAjaxRequest(params, "/api/v1/approvals/list", "GET", function (result) {
			let list = result.data.rows;
			let approval =$('select[name=approval]');
			approval.empty();
			for(let i=0; i<list.length; i++){
				let option = $("<option>").text(list[i].name+ "(" + list[i].classify_name +")").val(list[i].approval_uuid);
				approval.append(option);
			}
			if(editFlag){
				approval.val(_SETTINGS.basic_info.approval_uuid);
			}
		});
	}

	//初始化应用组详细信息
	let initAppGroupDetails = function (){
		let uuid = $('select[name=appgroup]').val();
		$('#appgroupList').empty();
		if(uuid !=""){
			pAjaxRequest({}, '/api/v1/verification/app_group/' + uuid, 'GET', (result) => {
				if (result.success) {
					let data = result.data.object_list;
					appgroupList = data;
					appgroupuuidList = [];
					if (editFlag){
						let list = _SETTINGS.item_list;
						for (let j=0;j<data.length;j++){
							for (let i=0;i<list.length;i++){
								if(list[i].verify_config.role == 2 && (data[j].general_config.item_uuid == list[i].general_config.item_uuid && data[j].general_config.task_uuid == list[i].general_config.task_uuid)){
									let node = {
										'id': data[j].general_config.item_uuid + '_' + data[j].general_config.task_uuid,
										'object_uuid': data[j].general_config.item_uuid,
										'name': data[j].general_config.item_name,
										'task_uuid': data[j].general_config.task_uuid,
										'module_type': data[j].general_config.module_type,
										'submodule_type': data[j].general_config.sub_module_type,
										'integrity_check_flag': 2,
										'timepointInfo': data[j].verify_config.newest_timepoint,
									}
									list[i].general_config.os_type = OS_TYPE_INDEX[list[i].general_config.os_type];
									list[i].safe_config = {};
									list[i].safe_config.vir_det_kill_flag = list[i].verify_config.vir_det_kill_flag;
									list[i].safe_config.integrity_check_flag = false;
									list[i].safe_config.virus_scan_config_list = JSON.stringify(list[i].verify_config.virus_scan_config_list);
									list[i].file_compare_config = {};
									list[i].file_compare_config.doc_consistency_flag = list[i].verify_config.doc_consistency_flag;
									list[i].file_compare_config.doc_list = list[i].verify_config.doc_list;
									addObjectList('object_tree', node, true, list[i], true);
								}
								continue;
							}
						}
					}else{
						for (let i=0;i<data.length;i++){
							let node = {
								'id': data[i].general_config.item_uuid + '_' + data[i].general_config.task_uuid,
								'object_uuid': data[i].general_config.item_uuid,
								'name': data[i].general_config.item_name,
								'task_uuid': data[i].general_config.task_uuid,
								'module_type': data[i].general_config.module_type,
								'submodule_type': data[i].general_config.sub_module_type,
								'integrity_check_flag': 2,
								'timepointInfo': data[i].verify_config.newest_timepoint,
							}
							data[i].general_config.os_type = OS_TYPE_INDEX[data[i].general_config.os_type];
							data[i].safe_config = {};
							data[i].safe_config.vir_det_kill_flag = data[i].verify_config.vir_det_kill_flag;
							data[i].safe_config.virus_scan_config_list = JSON.stringify(data[i].verify_config.virus_scan_config_list);
							data[i].safe_config.integrity_check_flag = false;
							data[i].file_compare_config = {};
							data[i].file_compare_config.doc_consistency_flag = data[i].verify_config.doc_consistency_flag;
							data[i].file_compare_config.doc_list = data[i].verify_config.doc_list;
							addObjectList('object_tree', node, true, data[i], true);
							appgroupuuidList.push(data[i].general_config.item_uuid);
						}
					}
					$('#appgroupList').show();
					$('#noAppTips').hide();
				}
			});
		}else{
			$('#appgroupList').hide();
			$('#noAppTips').show();
		}
	}

	//初始化应用组选择
	let initAppGroup = function(){
		let params = {};
		params.sort = "sag.create_time";
		params.order = "desc";

		pAjaxRequest(params, "/api/v1/verification/app_group", "GET", function (result) {
			let list = result.data.rows;
			let appgroup =$('select[name=appgroup]');
			appgroup.empty();
			let option = $("<option>").text(LANG.UI_VERIFY_SELECT_APP_GROUP).val("");
			appgroup.append(option);
			for(let i=0; i<list.length; i++){
				let option = $("<option>").text(list[i].appgroup_name).val(list[i].appgroup_uuid);
				appgroup.append(option);
			}
			if(editFlag){
				//应用组
				appgroup.val(_SETTINGS.high_strategy.appgroup_uuid);
				initAppGroupDetails();
			}

			//切换应用组
			$('select[name=appgroup]').off().on('change', function(){
				initAppGroupDetails();
			});
		});

	}

	//初始化选择虚拟实验室下拉框
	let initLabSelect = function(){
		let params = {};
		params.sort = "svl.hypervisor_type";
		params.order = "desc";
		pAjaxRequest(params, "/api/v1/verification/lab", "GET", function (result) {
			let list = result.data.rows;
			let labselect =$('select[name=virtualLab]');
			labselect.empty();
			let option = $("<option>").text(LANG.UI_JOB_SELECT).val("");
			labselect.append(option);
			for(let i=0; i<list.length; i++){
				let option = $("<option>").text(list[i].lab_name + "(" +list[i].hypervisor_type_des+ ")").val(list[i].lab_uuid).attr('data-type', list[i].hypervisor_type).attr('data-nodeuuid', list[i].node_uuid);
				labselect.append(option);
			}
			if (editFlag){
				$('select[name=virtualLab]').val(_SETTINGS.high_strategy.virtual_lab_uuid);
			}

			//加载切换虚拟实验室选择事件
			$('select[name=virtualLab]').on('change', labSelectChange);
		});
	}


	//切换虚拟实验室加载相关信息
	let labSelectChange = function(){
		//重新加载虚拟实验室信息显示z
		// initLabConfig();
		// 默认得显示
		$('#tab_high .nfsiplabel').parent().show();
		$('a[href="#tab_high"]').show();
		// 判断类型
		let type = $('select[name=virtualLab] option[value="' + $('select[name=virtualLab]').val() + '"]').data('type');
		if (type == 1) {
			// 容灾演练平台的虚拟试验室
			// 1、自动验证任务屏蔽存储挂载点IP或域名
			// 2、手动验证任务高级配置只有存储挂载点IP或域名，所以直接屏蔽高级配置
			if ($('#authselect').val() == 1) {
				// 手动验证
				$('a[href="#tab_high"]').hide();
				$('a[href="#tab_common"]').click();
			} else if ($('#authselect').val() == 0) {
				// 自动验证
				$('#tab_high .nfsiplabel').parent().hide();
			}
		}
	}

	//初始化状态值显示
	const setStatusDes = function(status){
		let labelClass = "label-info";
		switch(status){
			case 0:	//未知
				labelClass = "label-info";
				break;
			case 1:	//部署中
				labelClass = "label-success";
				break;
			case 2:	//在线
				labelClass = "label-success";
				break;
			case 3:	//离线
				labelClass = "label-default";
				break;
			case 4:	//异常
				labelClass = "label-warning";
				break;
			case 5:	//错误
				labelClass = "label-danger";
				break;
			case 6:	//修改
				labelClass = "label-info";
				break;
			case 7:	//已部署
				labelClass = "label-success";
				break;
			default:
				labelClass ="label-success";
				break;
		}
		return labelClass;
	}

	//初始化虚拟实验室信息抽屉展示
	let initLabConfig = function(){
		let labuuid = $('select[name=virtualLab]').val();
		if(labuuid == ""){
			UIToastr.showInfo(LANG.UI_PLATFORM_INDUSTRY_REPORT_VIEW_DETAIL, LANG.UI_VERIFY_SELECT_LAB_TIPS);
			return;
		}
		pAjaxRequest({}, "/api/v1/verification/lab/"+  labuuid, "GET", function (result) {
			let data = result.data;

			//演练室名称
			$('#labName').html(data.lab_name);

			//状态
			let labelClass = setStatusDes(data.status);
			let div = '<span class="label label-sm status-icon ' + labelClass + '">' + data.status_des + '</span>';
			$('#labStatus').html(div);

			//代理网关
			$('#proxy').html(data.proxy_info.proxy_name);

			//IP地址
			$('#labIpaddr').html(data.proxy_info.ip);

			//子网掩码
			$('#labNetmask').html(data.proxy_info.netmask);

			//默认网关
			$('#labGateway').html(data.proxy_info.gateway);

			//网络
			$('#labNetwork').html(data.proxy_info.network_name);

			//存储
			$('#labStorage').html(data.proxy_info.mount_storage);

			//虚拟化中心
			$('#vcenterName').html(data.vcenter_name);

			//宿主机
			$('#hostName').html(data.host_name);

			let productList = [], isolatedList = [];
			for (let i=0;i<data.network_list.length;i++){
				let productInfo = {
					'name': data.network_list[i].product_network_network_name,
					'netmask': data.network_list[i].product_network_netmask,
					'gateway': data.network_list[i].product_network_gateway,
				}
				let isolatedInfo = {
					'name': data.network_list[i].isolate_network_network_name,
					'netmask': data.network_list[i].isolate_network_netmask,
					'gateway': data.network_list[i].isolate_network_gateway,
				}
				productList.push(productInfo);
				isolatedList.push(isolatedInfo);
			}
			//初始化生产网络表格
			initNetworkTable(productList, '#product_network_table', LANG.UI_DRILLS_PRODUCT_NETWORK);

			//初始化隔离网络表格
			initNetworkTable(isolatedList, '#isolated_network_table', LANG.UI_DRILLS_ISOLATED_NETWORK);

			$('#show_labdetail_drawer').drawer('show');
		});

	}

	//初始化网络信息表格
	let initNetworkTable = function(data, div, des){
		//表格初始化配置项
		let options = {
			data: data,
			pagination: false,
			columns: [
				{
					field: 'name',
					sortable: false,
					title: des,
				},
				{
					field: 'netmask',
					sortable: false,
					title: LANG.UI_PUBLIC_IP_NETMASK,
				},
				{
					field: 'gateway',
					sortable: false,
					title: LANG.UI_PUBLIC_IP_GATEWAY,
				}]
		}
		$(div).bootstrapTable('destroy');
		$(div).baseTableConfig().init(options);
	}



	//初始化数量控制插件
	let initSpinner = function(){
		// if(data.basic_info.automatic_verifitied_flag == 0 && $('#oem_version').val() != CONF.VENDOR_LIST.gmp){
		$('#hostNumDiv').spinner({value:1, step: 1, min: 1, max: 999});
		// }
		$('#threadDiv').spinner({value:3, step: 1, min: 1, max: 10});	//文件对比线程数量默认3
		$('#pointNumDiv').spinner({value:1, step: 1, min: 1, max: 99});	//最大验证时间点个数默认1
	}


	//初始化时间策略
	let initTimeStretegy = function(info = {}){

		let strategy = [];
		//修改任务
		if(editFlag && info.strategy_type){
			strategy[0] = {
				mode: 8,	//数据验证策略
				strategy_type: info.strategy_type,
				days:  info.days,
				start_time: info.start_time,
				roll_flag: info.roll_flag,
				roll_interval: info.roll_interval,
				roll_end_time: info.roll_end_time,
				frequency: info.frequency,
			}
		}else{
			strategy[0] = {
				mode: 8,	//数据验证策略
				strategy_type: 2,
				days: [0, 0, 0, 0, 1, 0, 0],
				start_time: '23:00:00',
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: '23:59:59',
				frequency: ""
			}
		}
		//延迟设置,因为这里icheck会默认修改里面的选中事件
		setTimeout(function(){
			$('#verifyTimestrategy').strategy({dom: $('#verifyTimestrategy'), config: strategy, backup_flag: 3});
			$('.rollDiv').hide(); //隐藏滚动执行
		}, 2000);

	}

	//初始化存储挂载点IP或域名
	let initBackupServerAddr = function(nodeuuid){
		let reqData = {
			offset: 0,
			limit: 100,
		};
		pAjaxRequest(reqData, `/api/v1/nodes/${nodeuuid}/network`, "GET", function (result) {
			var data;
			if (result.code == 200) {
				data = result.data.rows;
			} else {
				data = [window.location.host];
			}
			let serveripaddr = $('#serveripaddr');
			serveripaddr.empty();
			for(let i=0; i<data.length; i++){
				//本地默认IP
				if(data[i].network_type == 1){
					let option = $("<option>").text(data[i].network_ip).val(data[i].network_ip);
					serveripaddr.append(option);
				}
			}
		});
	}

	//初始化对象树
	let initObjectTree = function(nodes){
		if(nodes.length == 0){
			$('#noobjecttips').show();
			$('.three_tree').hide();
			Metronic.unblockUI('.src-wrap__content');
			return;
		}else{
			$('#noobjecttips').hide();
			$('.three_tree').show();
		}
		let setting = {
			check: {
				enable: true,
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true
				},
				key: {
					title: "title"
				}
			},
			view: {
				fontCss: getFontCss,
			},
			callback: {
				onCheck: objectOnCheck,
			}
		};
		let zNodes = [];//拿到备份节点
		let taskList = [];
		let oldInfo = [];
		let oldTaskList = [];
		if(editFlag && !initEditFlag){
			for (let i =0;i<data.item_list.length;i++){
				if (data.item_list[i].general_config.role == 1){
					//验证对象
					oldInfo.push(data.item_list[i].general_config.backup_task_uuid + data.item_list[i].general_config.item_uuid);
					if($.inArray(data.item_list[i].general_config.backup_task_uuid, oldTaskList) == -1){
						oldTaskList.push(data.item_list[i].general_config.backup_task_uuid);
					}
				}
			}
		}
		for(let i=0; i<nodes.length; i++){
			//添加任务节点
			let moduleDes = getModuleDes(nodes[i].module_type, nodes[i].sub_module_type);
			let taskDes = CONF.TASK_TYPE_DES[nodes[i].task_type]; //获取任务类型描述
			let typeDes = moduleDes + taskDes;
			//数据库模块特特殊处理成备份描述
			if( nodes[i].task_type == 28){
				typeDes = taskDes;
			}
			if($.inArray(nodes[i].task_uuid, taskList) == -1){
				let info1 = {
					pId: 0,
					id: nodes[i].task_uuid,
					name: nodes[i].task_name + '(' + typeDes + ')',
					type: 1,
					module_type: nodes[i].module_type,
					sub_module_type: nodes[i].sub_module_type,
					chkDisabled: false,
					eventtype: "task",
					task_uuid: nodes[i].task_uuid,
					title: nodes[i].task_name + '(' + typeDes + ')',
					icon: "./img/platform/task.png"
				};
				if($.inArray(nodes[i].task_uuid, oldTaskList) != -1){
					info1.checked = true;
					info1.open = true;
				}
				zNodes.push(info1);
				taskList.push(nodes[i].task_uuid);

			}
			//数据库模块添加子任务节点
			if(nodes[i].module_type == CONF.MODULE_TYPE.DB && nodes[i].other_info && nodes[i].other_info.task_uuid){
				let child = {
					pId: nodes[i].task_uuid,
					id: nodes[i].other_info.task_uuid,
					name: nodes[i].other_info.task_name,
					type: 2,
					module_type: nodes[i].module_type,
					sub_module_type: nodes[i].sub_module_type,
					chkDisabled: false,
					eventtype: "task",
					task_uuid:nodes[i].other_info.task_uuid,
					title: nodes[i].other_info.task_name,
					icon: "./img/platform/task.png",
					nocheck:true
				};
				
				zNodes.push(child);
			}

			//添加对象节点
			let info2 = {
				pId: nodes[i].task_uuid,
				id: nodes[i].object_uuid + '_' +nodes[i].task_uuid,
				name: nodes[i].object_name,
				type: 2,
				module_type: nodes[i].module_type,
				submodule_type: nodes[i].sub_module_type,
				chkDisabled: false,
				eventtype: "object",
				object_uuid: nodes[i].object_uuid,
				task_uuid: nodes[i].task_uuid,
				task_name: nodes[i].task_name + '(' + typeDes + ')',
				title: nodes[i].title,
				integrity_check_flag: nodes[i].integrity_check_flag,
				parent_uuid: nodes[i].parent_uuid,
				host_uuid: nodes[i].host_uuid,
				icon: "./img/platform/host.png"
			};
			let ID = nodes[i]['task_uuid'] + nodes[i]['object_uuid'];
			if($.inArray(ID, oldInfo) != -1){
				info2.checked = true;
			}

			zNodes.push(info2);
		}
		ztree = $.fn.zTree.init($("#object_tree"), setting, zNodes);//第一种
		
		for(let i=0;i<zNodes.length;i++){
			if(zNodes[i].checked && zNodes[i].eventtype == "object"){
				for (let j=0;j<data.item_list.length;j++){
					let liID = clearString('object_tree' + zNodes[i].task_uuid + zNodes[i].object_uuid);
					if(data.item_list[j].general_config.item_uuid == zNodes[i].object_uuid 
						&& data.item_list[j].general_config.backup_task_uuid == zNodes[i].task_uuid
					){
						let checkNode = ztree.getNodesByParam("id", zNodes[i].id, null);
						initObjectConfig('object_tree', checkNode[0], true, data.item_list[j]);
					}
				}
			}
		}

		Metronic.unblockUI('.src-wrap__content');

	}

	let getFontCss = function(treeId, treeNode) {
		let css = {color:"#333", "font-weight":"normal"};
		if(!!treeNode.highlight){
			//搜索使用的样式
			css = {color:"#A60000", "font-weight":"bold"};
		}
		return css;
	}

	//替换特殊字符
	let clearString = function (s){
		let rs = "";
		for (let i = 0; i < s.length; i++) {
			rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
		}
		return rs;
	}

	let escapeJquery = function(srcString){
		// 转义之后的结果
		let escapseResult = srcString.toString();
		// javascript正则表达式中的特殊字符
		let jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
			"]", "|", "{", "}"];
		// jquery中的特殊字符,不是正则表达式中的特殊字符
		let jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
			":", ";", "<", ">", ",", "/"];
		for (let i = 0; i < jsSpecialChars.length; i++) {
			escapseResult = escapseResult.replace(new RegExp("\\"
				+ jsSpecialChars[i], "g"), "\\"
				+ jsSpecialChars[i]);
		}
		for (let i = 0; i < jquerySpecialChars.length; i++) {
			escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
				"g"), "\\" + jquerySpecialChars[i]);
		}
		return escapseResult;
	}

	//勾选节点
	let objectOnCheck = function(e, id, node){
		var checkNodes = ztree.getCheckedNodes();
		if(node.eventtype == "task"){
			let children = node.children;
			for(var i=0;i<children.length;i++){
				if(children[i].eventtype == "task") continue;
				//判断是否与应用组对象重复
				for(var j=0;j<appgroupList.length;j++){
					if (appgroupList[j].general_config.task_uuid == children[i].task_uuid && appgroupList[j].general_config.item_uuid == children[i].object_uuid){
						ztree.checkNode(children[i], !children[i].checked, true, false);
						UIToastr.showWarning(LANG.UI_VERIFY_SELECT_SRC, LANG.UI_VERIFY_SELECT_SRC_TIPS);
					}
				}
				
				//判断勾选相同对象，不允许勾选这一次
				for(var l=0;l<checkNodes.length;l++){
					if(checkNodes[l].id != children[i].id && checkNodes[l].object_uuid == children[i].object_uuid){
						ztree.checkNode(children[i], !children[i].checked, true, false);
						UIToastr.showWarning(LANG.UI_VERIFY_SELECT_SRC, LANG.UI_VERIFY_SELECT_SRC_TIPS);
					}
				}

				initObjectConfig(id,children[i],node.checked, {});
			}
		}else{
			for(var i=0;i<appgroupList.length;i++){
				if (appgroupList[i].general_config.task_uuid == node.task_uuid && appgroupList[i].general_config.item_uuid == node.object_uuid){
					ztree.checkNode(node, !node.checked, true, false);
					UIToastr.showWarning(LANG.UI_VERIFY_SELECT_SRC, LANG.UI_VERIFY_SELECT_SRC_TIPS);
				}
			}

			//判断勾选相同对象，不允许勾选这一次
				for(var l=0;l<checkNodes.length;l++){
					if(checkNodes[l].id != node.id && checkNodes[l].object_uuid == node.object_uuid){
						ztree.checkNode(node, !node.checked, true, false);
						UIToastr.showWarning(LANG.UI_VERIFY_SELECT_SRC, LANG.UI_VERIFY_SELECT_SRC_TIPS);
					}
				}
			initObjectConfig(id, node, node.checked, {});
		}

	}

	//获取CPU模式
	var getCpuMode = function(oldnum){
		var div = '';
		var list = [LANG.UI_PUBLIC_DEFAULT, "custom","host-passthrough", "host-model", 'EPYC', 'CORTEX'];
		for(var i=0; i<list.length; i++){
			if(i == 1) continue;
			if(i == oldnum){
				if( oldnum == hostSettings.cpu_mode){
					//默认选中并标注原配置
					div += '<option value="' + i + '" selected="selected">'+ list[i] + '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')' +'</option>';
				}else{
					//默认选中并标注原配置
					div += '<option value="' + i + '" selected="selected">'+ list[i]  +'</option>';
				}
			}else{
				div += '<option value="' + i + '">' + list[i] + '</option>';
			}
		}
		return div;
	}
	//获取socket和Cores
	let getSocketAndCoresOption = function(oldnum, hostnum, memoryFlag = false){
		let div = '';
		let num = 40;
		//内存设置128最大值
		if (memoryFlag){
			num = 1023;
		}
		for(let i=1; i<=num; i++){
			let oldDes = "";
			if(i == hostnum){
				oldDes = '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')';
			}
			if(i == oldnum){
				//默认选中并标注原配置
				div += '<option value="' + i + '" selected="selected">'+ i +oldDes+'</option>';
			}else{
				div += '<option value="' + i + '">' + i +oldDes+'</option>';
			}
		}
		return div;
	}

	//获取磁盘驱动器类型选项
	let getDiskTypeOption = function(oldNum){
		var div = '';
		var list = [
			{ value: 1, label: 'IDE' },
			{ value: 4, label: `VIRTIO(${LANG.UI_SETTING_DISK_TYPE_RECOMMEND_SOURCE})` },
			{ value: 5, label: 'SATA' },
			{ value: 2, label: 'SCSI' }
		];
		if (!oldNum){
			oldNum = 4;//默认按virtio
		}
		for(var i=0; i<list.length; i++){
			if(list[i].value == oldNum){

				//默认选中并标注原配置
				div += '<option value="' + list[i].value + '" selected="selected">'+ list[i].label  +'</option>';
			}else{
				div += '<option value="' + list[i].value + '">' + list[i].label + '</option>';
			}
		}
		return div;
	}

	//获取网络适配器类型选项
	let getNetworkTypeOption = function(oldNum, driverFlag, osversion){
		var div = '';
		var list = [
			{ value: 4, label: `VIRTIO(${LANG.UI_SETTING_DISK_TYPE_RECOMMEND_SOURCE})` },
			{ value: 6, label: 'E1000' },
			{ value: 7, label: 'RTL8139' }
		];
		
		if (driverFlag == 1 || $.inArray(parseInt(osversion), WINDOWS_INDEX) !== -1){	//已经预安装驱动网卡类型默认RTL8139
			oldNum = 7;
			list = [
				{ value: 4, label: 'VIRTIO' },
				{ value: 6, label: 'E1000' },
				{ value: 7, label: `RTL8139(${LANG.UI_SETTING_DISK_TYPE_RECOMMEND_SOURCE})` }
			];
	
		}
		for(var i=0; i<list.length; i++){
			if(list[i].value == oldNum){

				//默认选中并标注原配置
				div += '<option value="' + list[i].value + '" selected="selected">'+ list[i].label  +'</option>';
			}else{
				div += '<option value="' + list[i].value + '">' + list[i].label + '</option>';
			}
		}
		return div;
	}

	// 获取操作系统类型
	let getOsOption = function (oldnum, arch_type) {
		var div = '';
		var list = os_info_list[0].os_type_list;
		for (var j=0;j<os_info_list.length;j++){
			if(os_info_list[j].arch_type == arch_type){
				list = os_info_list[j].os_type_list;
			}
		}
		div += '<option value="0">' + LANG.UI_JOB_SELECT  + '</option>';
		for(var i=0; i<list.length; i++){
			let oldDes = "";
			if(list[i].os_value == hostSettings.os_type){
				oldDes = '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')';
			}
			if(list[i].os_value == oldnum){
				//默认选中并标注原配置
				div += '<option value="' + list[i].os_value + '" selected="selected">'+ list[i].os_name +oldDes+'</option>'
			}else{
				div += '<option value="' + list[i].os_value + '">' + list[i].os_name + oldDes +'</option>';
			}
		}
		return div;
	}

	//获取操作系统版本号
	let getOSVersionOption = function (oldnum, ostype, arch_type) {
		//默认widows;
		var list = os_info_list[0].os_list;
		for (var j=0;j<os_info_list.length;j++){
			if(os_info_list[j].arch_type == arch_type){
				list = os_info_list[j].os_list;
			}
		}
		var div = '';
		let oldDes = "";
		div += '<option value="0">' + LANG.UI_JOB_SELECT  + '</option>';
		for(var i=0; i<list.length; i++){
			let oldDes = "";
			if (list[i].os_type != ostype) continue;
			if(list[i].os_version == hostSettings.os_version){
				oldDes = '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')';
			}
			let osDescription = list[i].os_description ?? '';
			// 名称优先使用后台返回的os_description
			let osVersionName = '' !== osDescription ? osDescription : list[i].os_version_name;
			if(list[i].os_version == oldnum){
				//默认选中并标注原配置
				div += '<option data-name="'+osVersionName+'" value="' + list[i].os_version + '" selected="selected">'+ osVersionName + oldDes +'</option>';
			}else{
				div += '<option data-name="'+osVersionName+'" value="' + list[i].os_version + '" >'+ osVersionName + oldDes + '</option>';
			}
		}
		return div;
	}

	// 获取cpu架构
	let getCpuArchOption = function (oldnum) {
		var div = '';
		var list = cpu_info_list;
		div += '<option value="0">' + LANG.UI_JOB_SELECT  + '</option>';
		for(let i=0; i<list.length; i++){
			let oldDes = "";
			if(list[i].arch_type == hostSettings.cpu_arch){
				oldDes = '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')';
			}
			if(list[i].arch_type == oldnum && oldnum != 0){
				//默认选中并标注原配置
				div += '<option value="' + list[i].arch_type + '" selected="selected">'+ list[i].arch_name + oldDes +'</option>';
			}else{
				div += '<option value="' + list[i].arch_type + '">' + list[i].arch_name + oldDes + '</option>';
			}
		}

		return div;
	}

	//获取内存的单位
	let getMomoryUnit = function(oldnum){
		var div = '';
		var units = ["MB", "GB", "TB"];
		for(let i=0; i<units.length; i++){
			if(units[i] == oldnum || (!oldnum && units[i] == "GB")){
				//默认选中原来的单位
				div += '<option value="' + units[i] + '" selected="selected">' + units[i] + '</option>';
			}else{
				div += '<option value="' + units[i] + '">' + units[i] + '</option>';
			}
		}

		return div;
	}

	//初始化操作系统配置项
	var initOSSettings = function(){
		var s = {};
		s.hypervisor_type = 108;
		s.platform_uuid = storageList[1].node_uuid;
		s.host_uuid = storageList[1].node_uuid;
		pAjaxRequest(s, "/api/v1/vm/platforms/support_info", "GET", function (d) {
			if (d.success){
				cpu_info_list = d.data.cpu_info_list;
				os_info_list = d.data.os_info_list;
			}
		});
	}

	//初始化对象时间点配置信息
	var initObjectConfig = function(id,node,checkFlag, config){
		if (!checkFlag){
			let liID = clearString(id + node.task_uuid + node.object_uuid);
			let domID = 'object';
			//移除未选中的验证对象选项
			$('#' + domID + escapeJquery(liID)).remove();
			let checkNodes = ztree.getCheckedNodes();
			if(checkNodes.length == 0){
				$('#driverCheck_vm').hide();
			}
			let list = [];
			for (var i=0;i<source_list.length;i++){
				if(node.timepointInfo.timepoint_uuid != source_list[i].timepoint_uuid){
					list.push(source_list[i]);
				}
			}
			source_list = list;
			return;
		}
		var p = {};
		p.object_uuid = node.object_uuid;
		p.task_uuid = node.task_uuid;
		p.module_type = node.module_type;
		p.sub_module_type = node.sub_module_type;
		Metronic.blockUI({target: '.src-wrap__content',animate: true});
		pAjaxRequest(p, "/api/v1/verification/get_timepoint", "GET", function (d) {
			var timepointInfo = d.data;
			if (timepointInfo.timepoint_uuid){
				var p = {};
				p.timepoint_uuid = timepointInfo.timepoint_uuid
				if (node.module_type == 10){
					p.timepoint_uuid = timepointInfo.new_timepoint_uuid
				}
				p.agent_uuid = node.object_uuid;
				if(node.module_type != 5 &&node.module_type != 10){
					p.agent_uuid = "";
				}
				let integrity_check_flag = timepointInfo.integrity_check_flag;
				node.timepointInfo = timepointInfo;
				//非整机模块处理
				if($.inArray(node.module_type, [2,5,10]) == -1){
					Metronic.unblockUI('.src-wrap__content');
					if (editFlag && config.general_config){
						config.general_config.os_type = OS_TYPE_INDEX[config.general_config.os_type];
						addObjectList(id,node,checkFlag, config);
					}else {
						let info = {
							general_config: {},
							network_config: [],
							verify_config: {},
							safe_config: {},
							driver_config: {},
							file_compare_config: {}
						}
						info.safe_config.integrity_check_flag = integrity_check_flag;
						addObjectList(id,node,checkFlag, info);
					}
					return;
				}
				pAjaxRequest(p, "/api/v1/recovery/timepoint_config", "GET", function (d) {
					Metronic.unblockUI('.src-wrap__content');
					if(!d.success) {
						UIToastr.showWarning(LANG.UI_VERIFY_GET_OBJECT_SETTINGS_TIPS,d.message);
						return;
					}
					//加载默认选项
					let settings = {};
					settings.general_config = {
						'cpu_arch' : d.data['cpu_arch'],
						'core_num' : d.data['cpu_core'],
						'cpu_num' : d.data['cpu_socket'],
						'memory_size' : d.data['memory_size'],
						'memory_size_int' : d.data['memory_size_int'],
						'memory_size_unit' : d.data['memory_size_unit'],
						'os_type' : d.data['os_type'],
						'os_version' : d.data['os_version'],
						'preinstall_driver_success' : parseInt(d.data['preinstall_driver_success'])

					}
					hostSettings = settings.general_config;

					let networkList = [];
					let macList =[];
					if(d.data.network_list){
						for (let i=0;i<d.data.network_list.length;i++){
							let info = {};
							info.network_name = d.data.network_list[i].network_name;
							let src_adapter_list = d.data.network_list[i].src_adapter_list;
							if(src_adapter_list){
								for (let j=0;j<src_adapter_list.length;j++){
									if (src_adapter_list[j].ip_protocol == 1){
										info.gateway = src_adapter_list[j].gateway;
										info.ip = src_adapter_list[j].ip_list[0].ip_addr;
										info.netmask = src_adapter_list[j].ip_list[0].netmask;
										info.mac_address = d.data.network_list[i].mac_address;
										macList[info.ip] = info.mac_address;
									}
								}
							}else{
								info.ip = "";
								info.netmask = "";
								info.gateway = "";
								info.mac_address = "";
							}
							networkList.push(info);
						}

						settings.network_config = networkList;
						settings.verify_config = {};
						settings.safe_config = {};
						settings.safe_config.integrity_check_flag = integrity_check_flag;
						settings.driver_config = {};
						settings.file_compare_config = {};
						if (editFlag && config.general_config){
							config.general_config.os_type = OS_TYPE_INDEX[config.general_config.os_type];
							for (var i=0;i<config.network_config.length;i++){
								if (config.network_config[i].mactype == 2){
									config.network_config[i].mac_address = macList[config.network_config[i].ip];
								}
							}
							return addObjectList(id,node,checkFlag, config);
						}else{
							addObjectList(id,node,checkFlag, settings);
						}
					}
				});
			}else{
				//获取没有时间点的整机信息
				if (node.module_type == 5 || node.module_type == 10){
					//有代理整机调用接口获取原配置
					let p = {};
					p.agent_uuid = node.object_uuid;
					Metronic.blockUI({target: '.src-wrap__content',animate: true});
					pAjaxRequest(p, "/api/v1/complete_machine_os/get_client_info", "GET", function (d) {
						Metronic.unblockUI('.src-wrap__content');
						//加载默认选项
						let settings = {};
						settings.general_config = {
							'cpu_arch' : d.data['cpu_arch'],
							'core_num' : d.data['cpu_core'],
							'cpu_num' : d.data['cpu_socket'],
							'memory_size' : d.data['mem_size'],
							'memory_size_int' : d.data['memory_size_int'],
							'memory_size_unit' : d.data['memory_size_unit'],
							'os_type' : d.data['os_type'],
							'os_version' : d.data['os_version']

						}
						hostSettings = settings.general_config;
						if (editFlag && config.general_config){
							config.general_config.os_type = OS_TYPE_INDEX[config.general_config.os_type];
							addObjectList(id,node,checkFlag, config);
						}else{
							let networkList = [];
							let network = d.data.nic_list;
							if(network){
								for (let i=0;i<network.length;i++){
									let info = {};
									info.network_name = network[i].name;
									info.gateway = network[i].gateway_address;
									info.ip = network[i].ip_set[0].ip_addr;
									info.netmask = network[i].ip_set[0].netmask;
									info.mac_address = network[i].mac_address;
									networkList.push(info);
								}
							}
							settings.network_config = networkList;
							settings.verify_config = {};
							settings.safe_config = {};
							settings.driver_config = {};
							settings.file_compare_config = {};
							addObjectList(id,node,checkFlag, settings);
						}
					});
					return;
				}else{
					Metronic.unblockUI('.src-wrap__content');
					let info = {
						general_config: {},
						network_config: [],
						verify_config: {},
						safe_config: {},
						driver_config: {},
						file_compare_config: {}
					}
					addObjectList(id,node,checkFlag, info);
					return;
				}
			}
		});

	}


	//勾选树节点添加右侧虚拟机列表
	let addObjectList = function(id,node,checkFlag, config, appFlag = false){
		let liID = clearString(id + node.task_uuid + node.object_uuid);
		let dom = "#objectList";
		let domID = 'object';
		if(appFlag){
			dom = "#appgroupList";
			domID = 'appgroup';
		}
		if(checkFlag){
			//初始化整机对象配置信息
			let info = "";
			let nodeName = node.name+' | '+node.task_name;
			if (appFlag){
				nodeName = node.name;
			}
			let validate = "value=value.replace(/[^\d]/g,'')";
			let authType = $('#authselect').val();
			let vt_flag = $('#nodeselect').find('option:selected').attr('data-vt');
			let proper = LANG.UI_VM_MACHINE_VT_CPU_CUSTOM + '</br>';
			let displayHide = "";
			let hostflag = "";
			let gmpDisplay = "";
			let ping_test_flag = config.verify_config.ping_test_flag == false ? "" : "checked";
			let heartbeat_flag = config.verify_config.heartbeat_flag == false ? "" : "checked";
			let print_screen_flag = config.verify_config.print_screen_flag == false ? "" : "checked";
			let integrity_check_flag = config.safe_config.integrity_check_flag == false ?  "" : "checked";
			let max_boot_time = config.verify_config.max_boot_time ? config.verify_config.max_boot_time : "00:15:00";
			let max_ping_wait_time = config.verify_config.max_ping_wait_time ? config.verify_config.max_ping_wait_time : "00:01:00";
			//获取验证对象时间点信息
			let timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-type="1" data-pointType="2" data-uuid="">'+LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST+'</a>';
			if (node.timepointInfo){
				timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-type="1" data-pointType="2" data-uuid="'+node.timepointInfo.timepoint_uuid+'">'+LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST + node.timepointInfo.timepoint + '(' + node.timepointInfo.backup_mode_des  + ')'+'</a>';
				if(node.module_type == CONF.MODULE_TYPE.VOL_CDP){
					timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-system="'+node.timepointInfo.is_system_disk_flag+'" data-type="1" data-pointType="2" data-uuid="'+node.timepointInfo.new_timepoint_uuid+'" data-timepoint="'+node.timepointInfo.timepoint+'">'+LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST + node.timepointInfo.timepoint+'</a>';
				}
			}

			//修改任务获取时间点信息
			if (editFlag && config.verify_config.timepoint_uuid_list){
				let timepointInfo = config.verify_config.timepoint_uuid_list;
				if (config.verify_config.timepoint_range ==1){
					//选择所有验证点
					timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-type="1" data-pointType="1" data-uuid="" data-maxnum="'+timepointInfo.max_timepoint_verify+'">'+LANG.UI_VERIFY_SELECT_TIMEPOINT_ALL+'</a>';
				}else if (config.verify_config.timepoint_range ==3){
					let list = timepointInfo.timepoint_uuids;
					let des = "";
					for (var i=0;i<list.length;i++){
						des += list[i].timepoint + '('+ backupModeDes[list[i].backup_mode] +')' +'<br>';
					}
					timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-modeDes="'+backupModeDes[list[0].backup_mode]+'" data-pointType="3" data-timepoint="'+list[0].timepoint+'" data-type="'+list[0].backup_mode+'" data-uuid="'+list[0].timepoint_uuid+'" >'+des+'</a>';
					pointList[node.id] = list;
					if (node.module_type == CONF.MODULE_TYPE.VOL_CDP){
						timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-pointType="3" data-timepoint="'+list[0].timepoint+'" data-type="1" data-uuid="'+list[0].timepoint_uuid+'" >'+list[0].timepoint+'</a>';
						$('.selecttimepoint').val(config.verify_config.vol_cdp_datetime);
					}
				}
			}
			networkUuidList[liID] = [];
			//备份数据验证及安全扫描 或者手动验证
			if(data.basic_info.verify_mode == 1 || data.basic_info.automatic_verifitied_flag == 1){
				//不支持完整性校验
				displayHide = "display-hide";
			}
			if ($('#oem_version').val() == CONF.VENDOR_LIST.gmp ){
				gmpDisplay = "display-hide";
				displayHide = "";
			}

			//仅整机支持病毒查杀、通用配置、驱动检测
			if($.inArray(node.module_type, [3,4,11,14,28]) != -1){
				hostflag = "display-hide";
			}
			//根据cpu模式显示描述
			if (vt_flag == 1) {
				// 开启 custom/ host_passthrough
				proper += LANG.UI_VM_MACHINE_VT_CPU_PASSTHROUGH;
			} else {
				proper += LANG.UI_VM_MACHINE_VT_CPU_MODEL;
			}
			proper += '</br>' + LANG.UI_VERIFY_CPU_MODE_TIPS;
			//head
			info +=
				'<li class="panel panel-default mb10" id="'+domID+liID+'">' +
				'<div class="panel-heading">' +
				'<h4 class="panel-title">' +
				'<a title="'+nodeName+'" class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" href="#config'+liID+'" aria-expanded="true" data-parent="'+dom+'" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">'+
				'<span class="font-green-seagreen"><i class="viconfont vicon-zhuji2 mr5"></i>'+nodeName+'</span>' +
				'</a>' +
				'</h4>' +
				'</div>';
			//content
			info +=
				'<div class="panel-collapse collapse" id="config'+liID+'">' +
				'<div class="panel-body" style="padding: 16px"><div class="tabs ">' +
				'<ul class="nav nav-tabs nav-line-tabs">';

			//整机支持基本信息修改
			if ($.inArray(node.module_type, [3,4,11,14,28]) == -1){
				info += '<li class="nav-item ">' +
					'<a class="nav-link" href="#common_tab'+liID+'" data-toggle="tab" aria-expanded="false">'+LANG.UI_VM_SETTING_V2_GENERAL+'</a>' +
					'</li>';
			}
			info +=	'<li class="nav-item active">' +
				'<a class="nav-link" href="#verify_tab'+liID+'" data-toggle="tab" aria-expanded="false">'+ LANG.UI_VERIFY_VERIFY_CONFIG +'</a>' +
				'</li>';
			if (data.basic_info.verify_mode == 1){

			}else{
				info += '<li class="nav-item ">' +
					'<a class="nav-link" href="#network_tab'+liID+'" data-toggle="tab" aria-expanded="false">'+ LANG.UI_VERIFY_NETWORK_CONFIG +'</a>' +
					'</li>';
			}

			info += '</ul><div class="tab-content">';
			//通用配置
			info +=
				'<div class="tab-pane " id="common_tab'+liID+'"><div class="row">';

			if ($.inArray(node.module_type, [3,4,11,14,28]) == -1){
				info += '<div class="form-group">' +
					'<label class="control-label col-md-4">'+LANG.UI_VERIFY_SELECT_TIMEPOINT+'</label>' +
					'<div class="col-md-8 pt7">' +
					timepointDes +
					'</div>' +
					'</div>';
			}

			//整机模块的备份数据验证及安全扫描仅保留操作类型
			if (data.basic_info.verify_mode != 1 ){
				info +='<div class="form-group"><label class="control-label col-md-4">'+ LANG.UI_VERIFY_CPU_MODE +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="cpumode">' + getCpuMode(config.general_config.cpu_mode) + '</select>' +
					'</div>' +
					'<div class="col-md-2"><a style="position:absolute;left:0;top:5px;" class="popovers configTips" data-container="body" data-html="true" data-trigger="hover" data-placement="right" data-content="'+proper+'">' +
					'<i class="viconfont vicon-tishi"></i>' +
					'</a></div>' +
					'</div>' +
					'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VM_SETTING_CPU_ARCH +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="cpuArch">' + getCpuArchOption(config.general_config.cpu_arch) + '</select></div></div>' +
					'<div class="form-group"><label class="control-label col-md-4">'+LANG.UI_VERIFY_CPU_NUM+'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="cpunum">' + getSocketAndCoresOption(config.general_config.cpu_num, hostSettings.cpu_num) + '</select></div></div>' +
					'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+LANG.UI_VERIFY_CPU_EVERY_CORE+'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="corenum">' + getSocketAndCoresOption(config.general_config.core_num,hostSettings.core_num) + '</select></div></div>' +
					'<div class="form-group"><label class="control-label col-md-4">'+LANG.UI_VERIFY_MEMORY_SIZE+'</label><div class="col-md-6"><div style="display:inline-flex;width:100%;"><select class="form-control select2me input-sm" name="memory">' + getSocketAndCoresOption(config.general_config.memory_size_int, hostSettings.memory_size_int, true) + '</select><select class="form-control select2me input-sm" name="unit" style="margin-left:5px;width:65px;">' + getMomoryUnit(config.general_config.memory_size_unit) + '</select></div></div></div>' +
					'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VM_OS_TYPE +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="ostype">' + getOsOption(config.general_config.os_type, config.general_config.cpu_arch) + '</select></div></div>' +
					'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VM_OS_VERSION +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="osversion">' + getOSVersionOption(config.general_config.os_version, config.general_config.os_type, config.general_config.cpu_arch) + '</select></div></div>' +
					'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VERIFY_OBJECT_DISK_TARGET_BUS +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="disk_target_bus">' + getDiskTypeOption(config.general_config.disk_target_bus) + '</select></div></div>' +
					'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VERIFY_OBJECT_NETCARD_TARGET_BUS +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="netcard_target_bus">' + getNetworkTypeOption(config.general_config.netcard_target_bus, config.general_config.preinstall_driver_success, config.general_config.os_version) + '</select></div></div>';
			}else{
				info +='<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VM_OS_TYPE +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="ostype">' + getOsOption(config.general_config.os_type, config.general_config.cpu_arch) + '</select></div></div>'+
					'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VM_OS_VERSION +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="osversion">' + getOSVersionOption(config.general_config.os_version, config.general_config.os_type, config.general_config.cpu_arch) + '</select></div></div>';
			}


			//手动验证
			if(data.basic_info.automatic_verifitied_flag == 1){
				let checked = "checked";
				if(editFlag && !config.general_config.start_vm_flag){
					checked = "";
				}
				// 手动验证 加上自动开机选项
				info += '<div class="form-group">' +
					'<label class="control-label col-md-4">'+LANG.UI_VERIFY_AUTO_START+'</label>' +
					'<div class="col-md-6 form-group-top4-label">' +
					'<input type="checkbox" class="make-switch configCheck autostartCheck" '+checked+' data-on-color="primary" data-off-color="info" data-size="small" data-on-text="'+LANG.UI_PUBLIC_ON_ONE+'" data-off-text="'+LANG.UI_PUBLIC_OFF_ONE+'">' +
					'<a class="popovers ml15 configTips" data-container="body" data-trigger="hover" data-placement="right" data-content="'+LANG.UI_VERIFY_AUTO_START_TIPS+'">' +
					'<i class="viconfont vicon-tishi"></i>' +
					'</a>' +
					'</div>' +
					'</div>';
			}

			info += '</div></div>';

			//验证配置
			info +=
				'<div class="tab-pane active" id="verify_tab'+liID+'">' +
				'<div class="row">';
			if ($.inArray(node.module_type, [3,4,11,14,28]) != -1){
				info += '<div class="form-group">' +
					'<label class="control-label col-md-4">'+LANG.UI_VERIFY_SELECT_TIMEPOINT+'</label>' +
					'<div class="col-md-8 pt7">' +
					timepointDes +
					'</div>' +
					'</div>';
			}
			info +='<div class="form-group '+displayHide+gmpDisplay+ '">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_PING_TEST+'</label>' +
				'<div class="col-md-8 form-group-top4-label">' +
				'<input type="checkbox" class="make-switch configCheck pingCheck" '+ping_test_flag+' data-on-color="primary" data-off-color="info" data-size="small" data-on-text="" data-off-text="">' +
				'<a class="popovers ml15 configTips" data-container="body" data-trigger="hover" data-placement="right" data-content="'+LANG.UI_VERIFY_PING_TIPS+'">' +
				'<i class="viconfont vicon-tishi"></i>' +
				'</a>' +
				'</div>' +
				'</div>' +
				'<div class="form-group '+ displayHide + gmpDisplay +'">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_HEARTBEAT+'</label>' +
				'<div class="col-md-8 form-group-top4-label">' +
				'<input type="checkbox" class="make-switch configCheck heartCheck" '+heartbeat_flag+' data-on-color="primary" data-off-color="info" data-size="small"' + 'data-on-text="" data-off-text="">' +
				'<a class="popovers ml15 configTips" data-container="body" data-trigger="hover" data-placement="right" data-content="'+LANG.UI_VERIFY_HEARTBEAT_TIPS+'">' +
				'<i class="viconfont vicon-tishi"></i>' +
				'</a>' +
				'</div>' +
				'</div>' +
				'<div class="form-group '+displayHide+'">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_SCREEN+'</label>' +
				'<div class="col-md-8 form-group-top4-label">' +
				'<input type="checkbox" class="make-switch configCheck screenCheck" '+print_screen_flag+' data-on-color="primary" data-off-color="info" data-size="small" data-on-text="" data-off-text="">' +
				'<a class="popovers ml15 configTips" data-container="body" data-trigger="hover" data-placement="right" data-content="'+LANG.UI_VERIFY_SCREEN_TIPS+'">' +
				'<i class="viconfont vicon-tishi"></i>' +
				'</a>' +
				'</div>' +
				'</div>' +


				'<div class="form-group '+ hostflag + gmpDisplay + '" id="virusConfig_'+ liID +'" >' +
				'</div>';

			if(node.timepointInfo && node.timepointInfo.integrity_check_flag){
				info+='<div class="form-group '+ gmpDisplay + '">' +
					'<label class="control-label col-md-4">'+LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK+'</label>' +
					'<div class="col-md-8 form-group-top4-label">' +
					'<input type="checkbox" class="make-switch configCheck integrity_check_flag" '+integrity_check_flag+' data-on-color="primary" data-off-color="info" data-size="small"' + 'data-on-text="" data-off-text="">' +
					'<a class="popovers ml15 configTips" data-container="body" data-trigger="hover" data-placement="right" data-content="'+LANG.UI_SAFE_STRATEGY_BACKUP_POINT_CHECK+'">' +
					'<i class="viconfont vicon-tishi"></i>' +
					'</a>' +
					'</div>' +
					'</div>';
			}else{
				info+='<div class="form-group '+ gmpDisplay + '">' +
					'<label class="control-label col-md-4">'+LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK+'</label>' +
					'<div class="col-md-8 form-group-top4-label">' +
					'<input type="checkbox" class="make-switch configCheck integrity_check_flag" disabled data-on-color="primary" data-off-color="info" data-size="small"' + 'data-on-text="" data-off-text="">' +
					'<a class="popovers ml15 configTips" data-container="body" data-trigger="hover" data-placement="right" data-content="'+LANG.UI_SAFE_STRATEGY_BACKUP_POINT_CHECK+'">' +
					'<i class="viconfont vicon-tishi"></i>' +
					'</a>' +
					'</div>' +
					'</div>';
			}
			//开机上线检测时间
			info += '<div class="form-group '+displayHide+'">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_OBJECT_PING_WAIT_TIME+'</label>' +
				'<div class="col-md-6 form-group-content" style="height:revert;line-height: revert;">' +
				'<div class="input-group">' +
				'<input type="text" value="'+max_ping_wait_time+'" name="max_ping_wait_time"  class="form-control timepicker timepicker-24"><span class="input-group-btn">' +
				'<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button></span>'+
				'</div>' +
				'<div>' +
				'<span class="help-block ">' + LANG.UI_VERIFY_OBJECT_PING_WAIT_TIME_TIPS + '</span>' +
				'</div></div></div>';
			//最大开机等待时间
			info += '<div class="form-group '+displayHide+'">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_EFFECTIVE_TIME+'</label>' +
				'<div class="col-md-6 form-group-content" style="height:revert;line-height: revert;">' +
				'<div class="input-group">' +
				'<input type="text" value="'+max_boot_time+'" name="boottime"  class="form-control timepicker timepicker-24"><span class="input-group-btn">' +
				'<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button></span>'+
				'</div>' +
				'<div>' +
				'<span class="help-block ">' + LANG.UI_VERIFY_EFFECTIVE_TIME_TIPS + '</span>' +
				'</div></div></div>';


			if ($('#oem_version').val() == CONF.VENDOR_LIST.gmp && (node.module_type == 5 || node.module_type == 10)){
				//文件对比分析配置
				info +=
					'<div class="form-group">' +
					'<label class="control-label col-md-4">'+LANG.UI_VERIFY_FILE_COMPARE_TYPE+'</label>' +
					'<div class="col-md-4 form-group-top4-label">' +
					'<select class="form-control select2me input-sm compare_mode" ><option value="1">MD5</option></select>' +
					'</div>' +
					'</div>' +
					'<div class="form-group fileTreeDiv">' +
					'<label class="control-label col-md-4">'+LANG.UI_FILE_SELECT_FILE_AND_DIR+'</label>' +
					'<div class="col-md-8 form-group-top4-label">' +
					'<ul class="ztree allFileTree" id="'+domID+liID+'"></ul>' +
					'</div>' +
					'</div>';

			}

			info +=
				'</div>' +
				'</div>';
			//网络配置
			info +=
				'<div class="tab-pane pd10" id="network_tab'+liID+'">' +
				'<div class="row">' +
				'<button type="button"  class="btn btn-sm green-haze data-resource2 mb10 addNetwork"><i class="viconfont vicon-ge_add_task"></i>'+LANG.UI_VERIFY_ADD_NETWORK+'</button>' +
				'<ul class="feeds accordion addNetworkDiv"></ul>' +
				'</div></div>';




			//foot
			info += '</div></div></div></div></li>';

			$(dom).append(info);
			//初始化病毒扫描策略
			let virus_scan_config_list = [];
			if (config.safe_config.virus_scan_config_list){
				virus_scan_config_list = JSON.parse(config.safe_config.virus_scan_config_list);
			}
			if (editFlag && virus_scan_config_list && virus_scan_config_list.length != 0){
				$('#virusConfig_'+liID).virusDetectionBackup('col-md-4', 1 == config.safe_config.vir_det_kill_flag, virus_scan_config_list[0].interrupt_policy, virus_scan_config_list[0].all_timepoints_flag, virus_scan_config_list[0]);
			}else{
				$('#virusConfig_'+liID).virusDetectionBackup('col-md-4', false, true, false, {virus_thread_num:1});
			}



			//屏蔽扫描所有未扫描备份点
			$('#virusConfig_'+liID + ' .virusBackupSelect .virus-item').eq(1).hide();

			for (var i=0;i<config.network_config.length;i++){
				initNetwork(config.network_config[i], liID, domID);
			}

			//初始化文件树
			if ($('#oem_version').val() == CONF.VENDOR_LIST.gmp && (node.module_type == 5 || node.module_type == 10)){
				initFileTree(node, liID, config, appFlag);

			}
			//初始化icheck
			$('.addIt-list .icheck').iCheck({
				checkboxClass: 'icheckbox_square-blue',
				radioClass: 'iradio_square-blue strategy-radio-custom',
				increaseArea: '20%' // optional
			});

			$('input[name=boottime]').timepicker({
				autoclose: true,
				minuteStep: 5,
				showSeconds: true,
				showMeridian: false,
			});

			$('input[name=max_ping_wait_time]').timepicker({
				autoclose: true,
				minuteStep: 5,
				showSeconds: true,
				showMeridian: false,
			});

			$('input[name=max_ping_wait_time]').parent('.input-group').on('click', '.input-group-btn', function(e){
				e.preventDefault();
				$(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
			});

			$('input[name=boottime]').parent('.input-group').on('click', '.input-group-btn', function(e){
				e.preventDefault();
				$(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
			});

			$('#' + domID + escapeJquery(liID) + ' .select_point_' + liID).on('click', function(){
				//获取打开的对象的时间点类型回显
				$('.pointTableDiv').hide();
				$('.pointNumDiv').hide();
				$('.cdptimeDiv').hide();
				$('#object_name').html(node.name);
				$('#object_uuid').val(node.object_uuid);
				$('#src_task_uuid').val(node.task_uuid);

				//清空时间点搜索框内容
				$('.customSearch').val('');

				//全局获取当前模块类型
				currentModule = node.module_type;

				let name = LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST;
				if (node.timepointInfo){
					name = LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST + node.timepointInfo.timepoint + '(' +node.timepointInfo.backup_mode_des  + ')';
					if(node.module_type == CONF.MODULE_TYPE.VOL_CDP){
						name = LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST+ node.timepointInfo.timepoint;
					}
				}
				let option = ` <option value="2">`+name +`</option>
                                <option value="1">`+LANG.UI_VERIFY_SELECT_TIMEPOINT_ALL+`</option>
                                <option value="3">`+LANG.UI_VERIFY_SELECT_TIMEPOINT_SELECT+`</option>`;

				//应用、整机实时、手动验证或者GMP都不支持所有时间点
				if(appFlag || node.module_type == CONF.MODULE_TYPE.VOL_CDP || $('#oem_version').val() == CONF.VENDOR_LIST.gmp || data.basic_info.automatic_verifitied_flag == 1){
					option = ` <option value="2">`+name +`</option>
                                <option value="3">`+LANG.UI_VERIFY_SELECT_TIMEPOINT_SELECT+`</option>`;
				}

				let params = {};
				params.item_uuid = [node.object_uuid];
				params.task_uuid = node.task_uuid;
				params.taskGridFlag = true;
				params.verifyFlag = true;
				params.subTaskFlag = true;
				params.module_type = node.module_type;
				params.sub_module_type = node.submodule_type;
				params.storage_uuid = [$('#storagetypeselect').val()];
				timepointParams = params;
				$('#pointType').empty().html(option);
				if (objectTimepointInfo[liID] && objectTimepointInfo[liID].timepoint_range){
					$('#pointType').val(objectTimepointInfo[liID].timepoint_range).change();
				}

				if (objectTimepointInfo[liID] && objectTimepointInfo[liID].max_timepoint_verify){
					$('#pointnum').val(objectTimepointInfo[liID].max_timepoint_verify);
				}
				if(node.module_type == CONF.MODULE_TYPE.VOL_CDP){
					if (objectTimepointInfo[liID] && objectTimepointInfo[liID].vol_cdp_datetime){
						$('.selecttimepoint').val(node.timepointInfo.vol_cdp_datetime);
					}else{
						$('.selecttimepoint').val(node.timepointInfo.timepoint);
					}
					cdpInfo = {
						'node_uuid': $('#storagetypeselect').find('option:selected').attr('nodeuuid'),
						'task_uuid': node.task_uuid,
						'host_uuid': node.object_uuid,
					}
					getBackupSetRange(cdpInfo);
				}
				//显示抽屉
				$('#select_point_drawer').drawer('show');
				// initPointTable(params);
			});

			$('#' + domID + escapeJquery(liID) + ' .vir_det_kill_flag').on('switchChange.bootstrapSwitch', function(){
				if(this.checked){
					$('#' + domID + escapeJquery(liID) + " .virusSelect").show();
				}else{
					$('#' + domID + escapeJquery(liID) + " .virusSelect").hide();
				}
			});

			$('#' + domID + escapeJquery(liID) + " .configTips").popover();	   //初始化tips
			$('#' + domID + escapeJquery(liID) + " .configCheck").bootstrapSwitch();
			$('#' + domID + escapeJquery(liID) + " .keyupInput").off().keyup(function(){
				let value = $(this).val().replace(/[^\d]/g,'');
				$(this).val(value);
			});

			$('#' + domID + escapeJquery(liID) + " .pingCheck").on('switchChange.bootstrapSwitch', function(){
				if(this.checked){
					$('#' + domID + escapeJquery(liID) + " .ipDiv").show();
				}else{
					$('#' + domID + escapeJquery(liID) + " .ipDiv").hide();
				}
			});


			$('#' + domID + escapeJquery(liID) + " .addNetwork").on('click', function(){
				initNetwork({}, liID, domID);
			});

			$('#' + domID + escapeJquery(liID) + " .heartCheck").on('switchChange.bootstrapSwitch', function(){
				if(this.checked){
					$('#' + domID + escapeJquery(liID) + " .pingCheck").bootstrapSwitch('disabled', false);
				}else{
					$('#' + domID + escapeJquery(liID) + " .pingCheck").bootstrapSwitch('state', true);
					$('#' + domID + escapeJquery(liID) + " .pingCheck").bootstrapSwitch('disabled', true);
				}
			});
			$('#' + domID + escapeJquery(liID) + " .pingCheck").on('switchChange.bootstrapSwitch', function(){
				if(this.checked){
					$('#' + domID + escapeJquery(liID) + " .heartCheck").bootstrapSwitch('disabled', false);
				}else{
					$('#' + domID + escapeJquery(liID) + " .heartCheck").bootstrapSwitch('state', true);
					$('#' + domID + escapeJquery(liID) + " .heartCheck").bootstrapSwitch('disabled', true);

				}
			});

			$('#' + domID + escapeJquery(liID)).find('select[name=ostype]').on('change', function () {
				$('#' + domID + escapeJquery(liID)).find(`select[name=osversion]`).empty().html(getOSVersionOption("",$('#' + domID + escapeJquery(liID)).find(`select[name=ostype]`).val(), $('#' + domID + escapeJquery(liID)).find('select[name=cpuArch]').val() ));
			});

			// 初始化驱动检测
			initDriver(liID, node, TIMEPOINT_TYPE_LAST);
		}else{
			//移除未选中的验证对象选项
			$('#' + domID + escapeJquery(liID)).remove();
			let checkNodes = ztree.getCheckedNodes();
			if(checkNodes.length == 0){
				$('#driverCheck_vm').hide();
			}
			let list = [];
			for (var i=0;i<source_list.length;i++){
				if(node.timepointInfo.timepoint_uuid != source_list[i].timepoint_uuid){
					list.push(source_list[i]);
				}
			}
			//移除对应对象的时间点信息
			if (objectTimepointInfo.hasOwnProperty(liID)) {
				delete objectTimepointInfo[liID];
			}
			source_list = list;


		}
	}

	/**
	 * 获取备份集时间范围
	 */
	var getBackupSetRange = function(cdpInfo){
		var data = {};
		data.node_uuid = cdpInfo.node_uuid;
		data.task_uuid = cdpInfo.task_uuid;
		data.host_uuid = cdpInfo.host_uuid;

		Metronic.blockUI({target: '#verificationContent',animate: true});
		pAjaxRequest(data, "/api/v1/complete_machine_volcdp/backup_set/time_range", "GET", function (result) {
			Metronic.unblockUI('#verificationContent');
			var data = result.data;
			var volCdpBackupSetRange = $('#cdptimerange');
			volCdpBackupSetRange.empty();
			for(var i=0; i<data.length; i++){
				var timeRangeText = data[i].start_time + " - "+ data[i].end_time;
				var option = $("<option>").text(timeRangeText).val(data[i].backup_set_id)
					.attr('time_point_uuid', data[i].time_point_uuid)
					.attr('start_time',data[i].start_time)
					.attr('end_time',data[i].end_time)
					.attr('data-system',data[i].is_system_disk_flag);
				volCdpBackupSetRange.append(option);
			}
		});
		$('#cdptimerange').unbind('change').bind('change', function(){
			$('.selecttimepoint').val($(this).find('option:selected').start_time);
		});
	}

	//初始化网络
	let initNetwork = function(info, liID, domID){
		let network = '';
		let collapsed = "collapsed";
		let collapsedIn = "";
		let uuid = getUuid();
		let network_name = LANG.UI_VERIFY_NEW_NETWORK;
		let ip = "", netmask="", gateway = "",macaddress = "";
		let disabled = "disabled";
		if (info && info.network_name){
			network_name = info.network_name;
		}
		if (info != {} && info.ip){
			ip = info.ip;
		}

		if (info != {} && info.netmask){
			netmask = info.netmask;
		}

		if (info != {} && info.gateway){
			gateway = info.gateway;
		}


		//头部
		network += '<li  class="panel panel-default mb10" style="position: relative;width:90%;" id="network_'+uuid+'"><div class="panel-heading"><h4 class="panel-title"><a class="accordion-toggle accordion-toggle-styled popovers '+collapsed+'"'+
			'data-container="body" data-trigger="hover" data-parent=".addNetworkDiv" data-placement="top" data-toggle="collapse"  href="#network_content_'+uuid+'"  ><span class="font-green-seagreen">'+network_name+'</span></a></h4></div>' +
			'<div class="panel-collapse collapse '+collapsedIn+'" id="network_content_'+uuid+'"><div class="panel-body"><div class="form-group col-md-12 ">';

		//网络配置部分
		network += `<div class="form-group display-hide">
			<label class="control-label col-md-4 iplabel">`+LANG.UI_VIRTUAL_LAB_NETWORK_NAME+`</label>
			<div class="col-md-6">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxLength="128" class="form-control" name="networkname" value="`+network_name+`">
				</div>
			</div>
		</div>`;
		network += `<div class="form-group ">
					<label class="control-label col-md-4 iplabel"><span class="required">* </span>`+LANG.UI_PUBLIC_IP_ADDRESS+`</label>
					<div class="col-md-6">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="text" maxLength="128" class="form-control" name="ipaddress" value="`+ip+`" placeholder="192.168.1.110">
						</div>
					</div>
				</div>`;
		network += `<div class="form-group ">
					<label class="control-label col-md-4 iplabel"><span class="required">* </span>`+LANG.UI_PUBLIC_IP_NETMASK+`</label>
					<div class="col-md-6">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="text" maxLength="128" class="form-control" name="netmask" value="`+netmask+`" placeholder="255.255.255.0">
						</div>
					</div>
				</div>`;
		network += `<div class="form-group ">
					<label class="control-label col-md-4 iplabel">`+LANG.UI_PUBLIC_IP_GATEWAY+`</label>
					<div class="col-md-6">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="text" maxLength="128" class="form-control" name="gateway" value="`+gateway+`" placeholder="192.168.1.1">
						</div>
					</div>
				</div>`;

		if(info != {} && info.mac_address){
			disabled = "";
		}
		network += `<div class="form-group ">
				<label class="control-label col-md-4">`+LANG.UI_VM_SETTING_MAC+`</label>
				<div class="col-md-6">
					<select class="form-control select2me input-sm" name="mactype" `+disabled+`>
							<option value="2">`+LANG.UI_VM_SETTING_V2_AUTO_PRODUCT+`</option>
							<option value="1">`+LANG.UI_VM_SETTING_SAVE_MAC+`</option>
						
					</select>
				</div>
			</div>`;
		network += `<div class="form-group macdiv display-hide">
				<div class="col-md-offset-4 col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxLength="128" disabled class="form-control" name="macaddress" value="`+info.mac_address+`">
					</div>
				</div>
			</div>`;

		//结尾拼接
		network += '</div></div></div>';
		//添加删除按钮
		network += '<button type="button" class="btn viconfont vicon-a-Deleteshanchu1 green-haze b-btn" id="delete_' + uuid + '" style="position: absolute;right: -45px;top:0;"></button>';
		network += '</li>';
		//添加到对应页面
		$('#' + domID + escapeJquery(liID) + " .addNetworkDiv").append(network);
		networkUuidList[liID].push(uuid);
		//获取MAC地址类型
		if (info != {} && info.mactype){
			$('#' + domID + escapeJquery(liID) + " .addNetworkDiv #network_"+uuid + " select[name=mactype]").val(info.mactype);
			if(info.mactype == 1){
				$('#' + domID + escapeJquery(liID) + " .addNetworkDiv #network_"+uuid + " .macdiv").show();
			}else{
				$('#' + domID + escapeJquery(liID) + " .addNetworkDiv #network_"+uuid + " .macdiv").hide();
			}
		}

		$('#' + domID + escapeJquery(liID) + " .addNetworkDiv #network_"+uuid + " select[name=mactype]").on('change', function(){
			let type = $(this).val();
			if(type == 1){
				$('#' + domID + escapeJquery(liID) + " .addNetworkDiv #network_"+uuid + " .macdiv").show();
			}else{
				$('#' + domID + escapeJquery(liID) + " .addNetworkDiv #network_"+uuid + " .macdiv").hide();
			}
		});
		$('#delete_' + uuid).on('click', function(){
			$('#network_'+ uuid).remove();
			//检查已经取消的网络
			for(let i=0;i<networkUuidList[liID].length;i++){
				//网络未选中时移除
				if(uuid == networkUuidList[liID][i]){
					networkUuidList[liID].splice(i,1);
				}
			}

		});
	}

	// 初始化驱动检测插件，时间点类型修改后需重新初始化，指定时间点时需传入timepointList
	const initDriver = function (liID, node, pointType, timepointList = []) {
		if (data.basic_info.verify_mode == 1) return;
		$('#driverCheck_vm').show();
		if (!nodeMap[liID]) {
			nodeMap[liID] = node;
		}
		// let moduleType = node.module_type;
		let moduleType = CONF.MODULE_TYPE.VM;
		// 获取内嵌虚拟化的磁盘总线列表和网络类型列表
		const HYPERVISOR_EMD = 108;
		let emd_uuid = storageList[1].node_uuid;
		let p = {};
		let vm_info = {};
		p.hypervisor = HYPERVISOR_EMD;
		p.vcenter_uuid = emd_uuid;
		p.host_uuid = emd_uuid;
		pAjaxRequest(p, '/api/v1/recovery/vm/disk_network', 'GET', function (result) {
			if (result.success) {
				// 驱动检测
				vm_info.vm_type = p.hypervisor;
				vm_info.vm_uuid = emd_uuid;
				vm_info.host_uuid = emd_uuid;
				vm_info.disk_bus_list = result.data.disk_bus;
				vm_info.net_bus_list = result.data.network_bus;
				vm_info.default_disk_bus = result.data.default_disk;
				vm_info.default_net_bus = result.data.default_network;
				vm_info.bus_changeable_flag = result.data.enable_change;
			} else {
				operateResponseList(result);
			}
		});
		// 目标配置
		let target_type;
		let target_info = {};
		if (CONF.MODULE_TYPE.VM === moduleType) {
			target_type = $.fn.driverCheck.DRIVER_TARGET_TYPE.VM;
			target_info.vm_info = vm_info;
		} else {
			target_type = $.fn.driverCheck.DRIVER_TARGET_TYPE.CLIENT;
			target_info.client_info = {
				agent_uuid: node.object_uuid
			}
		}
		target_info.target_type = target_type;
		// 实例化驱动检测组件
		$.fn.driverCheck.init($('#driverCheck_vm'), {
			width: {
				config_label: 'display-hide',
				config_content: 'pl0 col-md-10',
				check_name: 'col-md-6',
				check_platform: 'col-md-3',
				check_driver: 'col-md-3',
			},
			getCheckObject: () => {
				return {
					source_list: getSourceList(),
					target_info: target_info
				};
			},
			show_check_btn: true,
		});
	}

	let getSourceList = function(){
		source_list = [];
		if(ztree){
			let nodes = ztree.getCheckedNodes();
			for (let i=0;i<nodes.length;i++) {
	
				//如果不是对象节点直接跳过
				if (nodes[i].eventtype != "object") continue;
				//处理特殊字符转义
				let liID = clearString('object_tree' + nodes[i].task_uuid + nodes[i].object_uuid);
				// 源配置
				let ostype = OS_TYPE[parseInt($(`#common_tab${liID}`).find(`select[name=ostype]`).val())];
				let osversion = $(`#common_tab${liID}`).find(`select[name=osversion]`).val();
				let cpuArch = $(`#common_tab${liID}`).find(`select[name=cpuArch]`).val();
				let disk_target_bus = $(`#common_tab${liID}`).find(`select[name=disk_target_bus]`).val()? parseInt($(`#common_tab${liID}`).find(`select[name=disk_target_bus]`).val()) : 4;
				let netcard_target_bus = $(`#common_tab${liID}`).find(`select[name=netcard_target_bus]`).val() ? parseInt($(`#common_tab${liID}`).find(`select[name=netcard_target_bus]`).val()) : 4;
				let moduleType = nodes[i].module_type;
				let name =  nodes[i].name;
				let timepointuuid = "";
				let pointType = parseInt($('.select_point_' +liID).attr('data-pointType'));
				if (nodes[i].timepointInfo){
					name =  nodes[i].name + '/' + nodes[i].timepointInfo.timepoint + ' (' + nodes[i].timepointInfo.backup_mode_des + ')';
					if(moduleType == CONF.MODULE_TYPE.VOL_CDP){
						name =  nodes[i].name + '/' + nodes[i].timepointInfo.timepoint;
					}
					timepointuuid = nodes[i].timepointInfo.timepoint_uuid;
					if(moduleType == CONF.MODULE_TYPE.VOL_CDP){
						timepointuuid = nodes[i].timepointInfo.new_timepoint_uuid;
					}
				}

				if (pointType == TIMEPOINT_TYPE_SELECT){
					timepointuuid = $('.select_point_' +liID).attr('data-uuid');
					name = nodes[i].name + '/' + $('.select_point_' +liID).attr('data-timepoint') + '(' + $('.select_point_' +liID).attr('data-modedes')  +')';
				}

				if(data.basic_info.verify_mode != 1){
					if(cpuArch == "0" || ostype == "0" || osversion == "0"){
						UIToastr.showWarning(jobTitle, LANG.UI_VERIFY_OBJECT+"("+nodes[i].name + ")" +LANG.UI_VERIFY_OBJECT_HARDWARE_INFO_SET_TIPS);
						return false;
					}
				}
				switch (pointType) {
					case TIMEPOINT_TYPE_LAST:
					case TIMEPOINT_TYPE_ALL:
						// 最新时间点和全部时间点都只传最新时间点
						if (nodes[i].timepointInfo) {
							source_list.push({
								name: name,
								timepoint_uuid: timepointuuid,
								module_type: moduleType,
								os_type: ostype,
								os_arch: cpuArch,
								os_version: osversion,
								target_hypervisor_disk_bus_list: [disk_target_bus], // 默认磁盘总线为VIRTO
								target_hypervisor_net_bus_list: [netcard_target_bus], // 默认网卡总线为VMXNET3
							});
						} else {
							// 时间点不存在
							if(moduleType == CONF.MODULE_TYPE.OS || moduleType == CONF.MODULE_TYPE.VOL_CDP ){
								source_list.push({
									name: nodes[i].name,
									timepoint_uuid: '',
									module_type: moduleType,
									agent_uuid: nodes[i].object_uuid,
									os_type: ostype,
									os_arch: cpuArch,
									os_version: osversion,
									check_agent_flag: true,
									target_hypervisor_disk_bus_list: [disk_target_bus], // 默认磁盘总线为VIRTO
									target_hypervisor_net_bus_list: [netcard_target_bus], // 默认网卡总线为VMXNET3
								});
							}else{
								source_list.push({
									name: nodes[i].name,
									timepoint_uuid: '',
									module_type: moduleType,
									os_type: ostype,
									os_arch: cpuArch,
									os_version: osversion,
									check_vm_flag: true,
									vm_uuid: nodes[i].object_uuid,
									vcenter_uuid: nodes[i].parent_uuid,
									target_hypervisor_disk_bus_list: [disk_target_bus], // 默认磁盘总线为VIRTO
									target_hypervisor_net_bus_list: [netcard_target_bus], // 默认网卡总线为VMXNET3
								});
							}
						}
						break;
					case TIMEPOINT_TYPE_SELECT:
						// 指定时间点传选择的第一个
						// for (let i in timepointList) {
						source_list.push({
							name: name,
							timepoint_uuid: timepointuuid,
							module_type: moduleType,
							os_type: ostype,
							os_version: osversion,
							os_arch: cpuArch,
							target_hypervisor_disk_bus_list: [disk_target_bus], // 默认磁盘总线为VIRTO
							target_hypervisor_net_bus_list: [netcard_target_bus], // 默认网卡总线为VMXNET3
						});
						// }
						break;
					default:
						break;
				}

			}
		}

		//应用组
		for (var i=0;i<appgroupList.length;i++){
			//处理特殊字符转义
			let liID = clearString('object_tree' + appgroupList[i].general_config.task_uuid + appgroupList[i].general_config.item_uuid);
			// 源配置
			let ostype = OS_TYPE[parseInt($(`#common_tab${liID}`).find(`select[name=ostype]`).val())];
			let osversion = $(`#common_tab${liID}`).find(`select[name=osversion]`).val();
			let cpuArch = $(`#common_tab${liID}`).find(`select[name=cpuArch]`).val();
			let moduleType = appgroupList[i].general_config.module_type;
			let name =  appgroupList[i].general_config.item_name + '/' + appgroupList[i].verify_config.newest_timepoint.timepoint + ' (' + appgroupList[i].verify_config.newest_timepoint.backup_mode_des + ')';
			let pointType = parseInt($('.select_point_' +liID).attr('data-pointType'));
			let timepointuuid = appgroupList[i].verify_config.newest_timepoint.timepoint_uuid;
			if (pointType == TIMEPOINT_TYPE_SELECT){
				timepointuuid = $('.select_point_' +liID).attr('data-uuid');
				name = appgroupList[i].general_config.item_name + '/' + $('.select_point_' +liID).attr('data-timepoint') + '(' + $('.select_point_' +liID).attr('data-modedes')  +')';
			}
			if(moduleType == CONF.MODULE_TYPE.VOL_CDP){
				name =  appgroupList[i].general_config.item_name + '/' + appgroupList[i].verify_config.newest_timepoint.timepoint;
			}
			switch (pointType) {
				case TIMEPOINT_TYPE_LAST:
				case TIMEPOINT_TYPE_ALL:
					// 最新时间点和全部时间点都只传最新时间点
					if (appgroupList[i].verify_config.newest_timepoint) {
						source_list.push({
							name: name,
							timepoint_uuid: timepointuuid,
							module_type: moduleType,
							os_type: ostype,
							os_arch: cpuArch,
							os_version: osversion,
							target_hypervisor_disk_bus_list: [4], // 默认磁盘总线为VIRTO
							target_hypervisor_net_bus_list: [4], // 默认网卡总线为VMXNET3
						});
					} else {
						// 时间点不存在
						if(moduleType == CONF.MODULE_TYPE.OS){
							source_list.push({
								name: appgroupList[i].general_config.item_name,
								timepoint_uuid: '',
								module_type: moduleType,
								agent_uuid:appgroupList[i].general_config.item_uuid,
								check_agent_flag: true
							});
						}else{
							source_list.push({
								name: appgroupList[i].general_config.item_name,
								timepoint_uuid: '',
								module_type: moduleType,
								check_vm_flag: true,
								vm_uuid: appgroupList[i].general_config.item_uuid,
								vcenter_uuid: ""
							});
						}
					}
					break;
				case TIMEPOINT_TYPE_SELECT:
					// 指定时间点传选择的第一个
					source_list.push({
						name: name,
						timepoint_uuid: timepointuuid,
						module_type: moduleType,
						os_type: ostype,
						os_version: osversion,
						os_arch: cpuArch,
						target_hypervisor_disk_bus_list: [4],
						target_hypervisor_net_bus_list: [4],
					});
					break;
				default:
					break;
			}
		}
		return source_list;
	}

	//获取唯一标识
	let getUuid = function() {
		let len = 36;//36长度
		let radix = 16;//16进制
		let chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
		let uuid = [], i;
		radix = radix || chars.length;
		if(len) {
			for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
		} else {
			let r;
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

	//初始化时间点表格
	let initPointTable = function(tableParams){
		let pointType = $('#pointType').val();
		let flag = false;
		//指定时间点显示
		if(pointType == 3){
			flag = true;
		}

		let columns = [
			{
				checkbox: flag,
				sortable: false, //默认可排序，禁用排序才写此项
				formatter: function (value, row, index, field) {
					let liID = clearString("object_tree" + tableParams.task_uuid + tableParams.item_uuid);
					//根据上一次勾选的时间点进行回显勾选
					if (objectTimepointInfo[liID] && objectTimepointInfo[liID].timepoint_uuids.length != 0){
						let timepointList = objectTimepointInfo[liID].timepoint_uuids;
						for(var i=0;i<timepointList.length;i++){
							if(timepointList[i].timepoint_uuid == row.timepoint_uuid){
								if(pointType == 3){
									return true;
								}else{
									return '-';
								}
								
							}
						}
					}
				}
			},
			{
				field: 'timepoint',
				title: LANG.UI_RECOVERY_TIMEPOINT,
				events: expandEvents,
				formatter: function (value, row, index, field) {
					let html = '';
					if (row.hasChildren > 0) {
						html = `<div style="padding-left:12px !important;display: inline;"><span class="treegrid-expander"></span><span style="cursor:pointer;" class="treegrid-expander-collapsed root-collapsed get-child-node"></span><span title="${row.timepoint}">${row.timepoint}</span></div>`;
					} else {
						html = `<span title="${row.timepoint}">${row.timepoint}</span>`;
					}
					return html;
				}
			},
			{
				field: '',
				title: LANG.UI_SEARCH_TYPE,
				formatter: function (value, row, index, field) {
					return row.backup_mode_des;
				}
			},
			{
				field: 'total_size',
				title: LANG.UI_COPY_DATA_SIZE,
				formatter: function (value, row, index, field) {
					return row.total_size;
				}
			},
			{
				field: 'write_size',
				title: LANG.UI_PUBLIC_REAL_SIZE,
				formatter: function (value, row, index, field) {
					return row.write_size;
				}
			},
			{
				field: 'verify_flag',
				title: LANG.UI_PUBLIC_STATUS,
				formatter: function (value, row, index, field) {
					return row.verify_flag_des;
				}
			},
		];
		//整机实时
		if(tableParams.module_type == 10){
			columns = [
				{
					checkbox: flag,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (value, row, index, field) {
						// if (row.checked === false) {
						// 	return {
						// 		disabled: true
						// 	};
						// }
					}
				},
				{
					field: 'start_timestamp',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_START_TIME,
					formatter: function (value, row, index, field) {
						let html = `<span title="${row.start_timestamp}">${row.start_timestamp}</span>`;
						return html;
					}
				},
				{
					field: 'end_timestamp',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_END_TIME,
					formatter: function (value, row, index, field) {
						let html = `<span title="${row.end_timestamp}">${row.end_timestamp}</span>`;
						return html;
					}
				},
				{
					field: 'backup_file_size',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_VOL_DATA_SIZE,
					formatter: function (value, row, index, field) {
						return row.backup_file_size;
					}
				},
				{
					field: 'log_file_total_size',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_VOL_LOG_SIZE,
					formatter: function (value, row, index, field) {
						return row.log_file_total_size;
					}
				},
				{
					field: 'verify_flag',
					title: LANG.UI_PUBLIC_STATUS,
					formatter: function (value, row, index, field) {
						return row.verify_flag_des;
					}
				},
			];
		}
		//表格初始化配置项
		let options = {
			toolbarId: '#vin_point_toolbar',
			buttonsToolbar: '#vin_point_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/backup_data/points',
			vin_method: 'POST',
			vin_params: function(){
				return tableParams;
			},
			placeholder: LANG.UI_VERIFY_SEARCH_BY_TIME, //搜索框的placeholder
			searchInput: true, //搜索框
			searchClass: 'pointSearch', //自定义的搜索框类名
			searchSelector: '.pointSearch', //选择使用自定义搜索框
			parentIdField: 'pid', // 确定字段作为父级字段
			idField: 'timepoint_uuid', // 确认字段作为id
			treeShowField: 'timepoint', // 确认显示展开图标的字段
			treegrid: true, // 使用树形格式
			showColumns: true,
			showExport: true,
			paginationLoop: false,
			fileName:LANG.UI_VERIFY_TIMEPOINT_LIST,
			PostBody: () => {
				// 渲染树形结构
				let columns = $('#point_table').bootstrapTable('getOptions').columns;
				if (columns && columns[0][1].visible) {
					// 二次渲染展开的列，增加展开图标
					$('#point_table').treegrid({
						treeColumn: 1,
						onChange: function () {
							$('#point_table').bootstrapTable('resetView')
						}
					});
				}
			},
			columns: columns
		}
		//gmp 手动验证 指定时间点为单选
		if (data.basic_info.automatic_verifitied_flag == 1 || $('#oem_version').val() == CONF.VENDOR_LIST.gmp){
			options.singleSelect = true;
		}
		$('#point_table').bootstrapTable('destroy');
		$('#point_table').baseTableConfig().init(options);
		
		//点击搜索图标搜索
		$('.search-btn').off().on('click', function () {
		
			tableParams.search = $('.pointSearch').val();
			$("#point_table").bootstrapTable('refresh',{query:tableParams});
		});

		//回车键执行搜索
		$('.pointSearch').off().keypress(function (e) {
            if (e.which == 13) {
                tableParams.search = $('.pointSearch').val();
				$("#point_table").bootstrapTable('refresh',{query:tableParams});
            }
        });

		//清除搜索内容
		$('#vin_point_toolbar').off().on('click', '.clear', function () {
			$('.pointSearch').val('')
			$('.point_tableclear').addClass('hide');
            tableParams.search = $('.pointSearch').val();
			$("#point_table").bootstrapTable('refresh',{query:tableParams});
        });

		//检查输入搜索内容
		$('.pointSearch').on('input', function () {
			if ($('.pointSearch').val()) {
				$('#vin_point_toolbar .clear').removeClass('hide');
			} else {
				$('#vin_point_toolbar .clear').addClass('hide');
			}
        });

		//获取焦点检查是否有搜索内容·
		$('#vin_lab_toolbar').on('focus', function () {
			 if ($('.pointSearch').val()) {
				 $('#vin_point_toolbar .clear').removeClass('hide');
			}
		});
		
	}

	let checkSubRow = (row, checked) => {
		// 勾选整条链
		if (checked) {
			// 如果是有子节点的完备点， 联动勾选所有子节点
			if (row.hasChildren) {
				// 暂存完备点id,跳过已经勾选的点
				if (!FULL_CHECK.includes(row.id)) {
					FULL_CHECK.push(row.id);
					// 联动勾选子记录
					$('#taskTable').bootstrapTable('checkBy', {
						field: 'pid',
						values: [row.id]
					});
				}
			} else {
				// 已经勾选的点跳过
				if (!UN_FULL.includes(row.id)) {
					UN_FULL.push(row.id);
					// 联动勾选兄弟节点
					$('#taskTable').bootstrapTable('checkBy', {
						field: 'pid',
						values: [row.pid]
					});
					// 如果父节点未被勾选,勾选父节点
					if (!FULL_CHECK.includes(row.pid)) {
						$('#taskTable').bootstrapTable('checkBy', {
							field: 'id',
							values: [row.pid]
						});
					}
				}
			}
		} else {
			// 如果是有子节点的完备点,联动取消所有非完备点
			if (row.hasChildren) {
				// 取消勾选,未被取消的完备点
				if (FULL_CHECK.includes(row.id)) {
					// 清除暂存的id
					FULL_CHECK = FULL_CHECK.filter(item => item !== row.id);
					// 联动取消子节点
					$('#taskTable').bootstrapTable('uncheckBy', {
						field: 'pid',
						values: [row.id]
					});
				}
			} else {
				// 只有勾选状态才取消
				if (UN_FULL.includes(row.id)) {
					// 清除暂存的id
					UN_FULL = UN_FULL.filter(item => item !== row.id);
					// 取消勾选兄弟节点
					$('#taskTable').bootstrapTable('uncheckBy', {
						field: 'pid',
						values: [row.pid]
					});
					// 取消父节点勾选，不在数组说明已经取消
					if (FULL_CHECK.includes(row.pid)) {
						$('#taskTable').bootstrapTable('uncheckBy', {
							field: 'id',
							values: [row.pid]
						});
					}
				}
			}
		}
	}

	/**
	 * 模拟点击树形表格的展开和折叠
	 * @param {*} row
	 * @param {*} ids
	 * @param {*} expandedClass
	 * @returns
	 */
	function toggleTreeGridExpander(timepoint_uuid, ids, expandedClass) {
		if (!Array.isArray(ids) || ids.length === 0) return;
		const timepointUuid = timepoint_uuid;
		if (timepointUuid == null) return;
		// 缓存DOM选择器
		const expanders = ids.map(id => $(`.treegrid-${id} .treegrid-expander`));
		for (let i = 0; i < ids.length; i++) {
			if (timepointUuid !== ids[i]) {
				const expander = expanders[i];
				if (!expander.hasClass(expandedClass)) {
					expander.trigger('click');
				}
			}
		}
	}

	let expandEvents = {
		'click .root-collapsed': function (e, value, row, index) {
			let checked = row.checked;
			let children = JSON.parse(row.children);
			let rows = $('#point_table').bootstrapTable("getData");
			if (children.length > 0) {
				if (checked) {
					for (let i = 0; i < children.length; i++) {
						children[i].checked = true;
					}
				}
				$('#point_table').bootstrapTable('append', children);
				// 获取依赖点后重新计算数量
				$('.pointTableDiv .pagination-info').html(LANG.UI_TOOLS_TOTAL + (rows.length + children.length) + LANG.UI_BACKUP_DATA_TABLE_LABEL_PAGE_NUM);
			}
		}
	}

	const initRadio = () => {
		const LEFT_AND_RIGHT_PADDING_WIDTH = 40; // 左右总padding宽度
		const LEFT_AND_RIGHT_BORDER_WIDTH =2; // 左右总border宽度
		let radioButtonElList = [].slice.call(document.querySelectorAll('.radio-group'));

		radioButtonElList.forEach(el => {
			let max_width = 0;
			$(el).find('.radio-group__item:not(.with-svg)').each(function() {
				// 获取每一组 radio-group 中 radio-group__item 的完整宽度（自身宽度 + 左右padding + 左右border）
				let fullWidth = $(this).width() + LEFT_AND_RIGHT_PADDING_WIDTH + LEFT_AND_RIGHT_BORDER_WIDTH;
				if(fullWidth > max_width) {
					max_width = fullWidth;
				}
			});
			$(el).find('.radio-group__item:not(.with-svg)').each(function() {
				// 将每一组 radio-group 中 radio-group__item 的最大宽度设置为其余项的宽度
				$(this).width(max_width - LEFT_AND_RIGHT_PADDING_WIDTH - LEFT_AND_RIGHT_BORDER_WIDTH);
			});

			$(el).on('click', '.radio-group__item', function(e) {
				let oldValue = $(el).find('.radio-group__item.active').attr("value");
				// 移除上一个的active样式
				$(el).find('.radio-group__item.active').removeClass('active');

				// 触发 change 事件
				let newValue = $(this).attr("value");
				if (oldValue !== newValue) {
					$(el).trigger('change', [oldValue, newValue]);
				}

				let radioButtonItemList = [].slice.call(el.querySelectorAll('.radio-group__item'));

				radioButtonItemList.forEach(i => {
					let value = $(i).attr("value");
					let currentValue = $(e.target).attr("value");
					if(currentValue == undefined){
						currentValue = $(e.target).parent().attr("value");
					}

					if (value === currentValue) {
						$(i).addClass('active');
					}
				});
			});
		})
	}



	//初始化存储挂载
	let initStorageMount = function(){
		let protocol = [{'name':'NFS', 'value': 1},{'name':'iSCSI','value': 2}]; // 默认协议
		let hypervisor = $('select[name=virtualLab]').find('option:selected').data('type');
		if(hypervisor == CONF.VM_TYPE.VMWARE){
			//第三方虚拟化
			protocol = [{'name':'NFS', 'value': 1}];
		}
		let init = {
			'node_uuid': data.node_uuid,
			'protocol': protocol
		}
		//判断对象存在再处理
		if (typeof storageMount !== "undefined"){
			storageMount.init(init);
		}
	}

	let getObjectInfo = function(){
		//初始化清空列表
		$('#objectList').empty();
		$('#driverCheck_vm').hide();
		let params = {} ;
		params.storage_uuid = $('#storagetypeselect').val();
		params.edit_flag = editFlag;
		params.task_uuid = $('#task_uuid').val();
		params.order = order;
		params.sort = 'bt.create_time';
		params.module_list = selectModule;
		params.verify_type = parseInt($('#verifyType').find('.icheck:checked').attr('data-mode'));
		Metronic.blockUI({target: '.src-wrap__content',animate: true,cenrerY: true,});
		pAjaxRequest(params, "/api/v1/verification/select_object_list", "GET", function (result) {
			let data = result.data.rows;
			initObjectTree(data);
		});

	}

	//得到开关的结果描述   开启/关闭
	let getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

	//初始化文件树
	var initFileTree = function(node, liID, config, appFlag = false){
		var _path = node.path;
		var params = {agentuuid: node.object_uuid, start:0, limit:40, filename:'', dir:"",pid:0, groupuuid:""};
		if (editFlag && config.file_compare_config.doc_list){
			params = {agentuuid: node.object_uuid, start:0, limit:40, filename:'', dir:"",pid:0, groupuuid:"", editFlag: true,taskuuid: "",applyPathList: config.file_compare_config.doc_list};
		}
		var params = JSON.stringify(params);
		$.ajax({
			type: "post",
			url: CONF.AJAXPATH,
			async:true,
			data:{m:CONF.M.FILE,f:'getFileDirTree',p:params},
			success: function(data){
				result = JSON.parse(data);
				if(result.re){
					let domID = "#object";
					if(appFlag){
						domID = '#appgroup';
					}
					setFileTree(result, liID, appFlag);
					$(domID + escapeJquery(liID) + ' .fileTreeDiv').show();
				}else{
					$(domID + escapeJquery(liID) + ' .fileTreeDiv').hide();
				}
			}
		});
	}

	var setFileTree = function(data, liID, appFlag = false){
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
		let domID = "#object";
		if(appFlag){
			domID = '#appgroup';
		}
		zTreeFile[liID] = $.fn.zTree.init($(domID + escapeJquery(liID) + ' .allFileTree'), setting, data['fileNodes']);

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
		var params = {agentuuid:pNode.uuid, start:0, limit:40, filename:'', dir:pNode.filepath,pid:pNode.filepath,groupuuid:pNode.groupuuid,code_type:pNode.code_type};
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

	//获取模块类型描述
	let getModuleDes = function(module, submodule){
		let des = "";
		for (let j=0;j<moduleList.length;j++){
			if(moduleList[j].module == module && moduleList[j].submodule == submodule){
				des += moduleList[j].name;
			}
		}
		return des;
	}


	//初始化存储类型展示方式和事件
	var initStorageShowType =  function(){
		pAjaxRequest({}, "/api/v1/storages/type", "GET", function (d) {
			var list = d.data;
			var storagetypeselect = $('#storagetypeselect');
			var labNodeuuid =$('select[name=virtualLab] ').find('option:selected').data('nodeuuid');
			var labHypervisor =$('select[name=virtualLab] ').find('option:selected').data('type');
			storagetypeselect.empty();
			for(var i=0; i<list.length; i++){
				//屏蔽云存储和异地存储及磁带,选择容灾演练室需要屏蔽和演练室不在同一个节点的存储
				if(list[i].storageid == "" || $.inArray(list[i].storagetype, [8,9,10]) != -1 || (labHypervisor == 108 && data.basic_info.virtual_lab_uuid != "" && labNodeuuid != list[i].node_uuid)) continue;
				var option = $("<option>").text(list[i].text).val(list[i].storageid).attr("type",list[i].storagetype).attr("nodeuuid", list[i].node_uuid);
				storagetypeselect.append(option);
			}
			storageList = list;
			if(editFlag && _SETTINGS.basic_info.storage_uuid){
				//存储
				$('#storagetypeselect').val(_SETTINGS.basic_info.storage_uuid);
				initAppGroup();	//初始化应用组选择
			}

			//存储改变事件
			$('#storagetypeselect').off().on('change', function(){
				//初始化验证对象树
				getObjectInfo();
				initNodeSelect();//初始化节点
			});
			//初始化操作系统和底层架构配置信息
			if ($('#storagetypeselect').val() && $('#storagetypeselect').val() != ""){
				getObjectInfo();
			}else{
				$('#noobjecttips').show();
				$('.three_tree').hide();
			}
			initOSSettings();
			initNodeSelect();
		}, false);

	}

	//优先判断授权
	var getVerifyCurrentUseLicense = function(){
		var requestList = {};
		requestList.type = "a";
		requestList.module = "verify";
		let currentUse = data.item_list.length;
		var checkFlag = false;

		var requestAuth = function(d){
			if(d.success){
				if (!d.data.license_flag) {  // 授权过期了
					UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
					checkFlag = false;
					return;
				}
				var authData = d.data;
				//如果验证数量为无限制直接跳过
				if(authData.total == -1){
					checkFlag = true;
					return;
				}
				//获取总共数量
				var total = parseInt(authData.total);
				//获取已使用数量
				var used = parseInt(authData.used);
				//获取当前一共应该使用的数量
				var total_used = used + currentUse;
				if(total_used > total){
					let remain = total - used;
					remain = remain < 0 ? 0 : remain;
					let message = LANG.UI_PUBLIC_OBTAIN_LICENSE_INSUFFICIENT_AMOUNT
						.replace('%REMAIN%', remain)
						.replace('%USE%', currentUse);
					UIToastr.showWarning(LANG.UI_PUBLIC_OBTAIN_LICENSE_INFO, message);
					checkFlag = false;
				}else{
					checkFlag = true;
				}

			}else{
				UIToastr.showWarning(LANG.UI_PUBLIC_OBTAIN_LICENSE_INFO, d.message);
				checkFlag = false;
			}
		}
		//获取授权信息
		pAjaxRequest(requestList, "/api/v1/system/auth/base_info", "GET", requestAuth,false);
		return checkFlag;
	}



	return {
		init: function(){
			initListeners(); 	//初始化回调事件
			initRadio();		//初始化验证方式选项框
			editVerifyJob();	//初始化修改任务
			wizardInit();         //初始化执行步骤插件
		}
	}
}();

jQuery(document).ready(function(){
	VerificationJob.init();
});