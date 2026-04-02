//API消息推送
var Settings_Message_Push = function () {
	var sortObj, initSort = false;	//排序对象
	var autoPushFlag = false;
	var autoResponseFlag = false;
	var beiDouMessageArr = [];  		// 北斗模板结构
	var editRow = {};          			// 修改的列
	var beiDouTemplateArr = [
		{
			"index":1,
			"alarm_server_ip":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_SERVER_IP,
			"content":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT1,
		},
		{
			"index":2,
			"alarm_message_type":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_MESSAGE_TYPE,
			"content":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT2,
		},
		{
			"index":3,
			"alarm_event_id":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_EVENT_ID,
			"content":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT3,
		},
		{
			"index":4,
			"alarm_source_ip":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_NODE_IP,
			"content":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT4,
		},
		{
			"index":5,
			"alarm_source_name":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_SYSTEM_NAME,
			"content":"",
		},
		{
			"index":6,
			"alarm_keyword":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_KEY,
			"content":"eth1",
		},
		{
			"index":7,
			"alarm_type":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_GROUP,
			"content":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT7,
		},
		{
			"index":8,
			"alarm_level":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_LEVEL,
			"content":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT8,
		},
		{
			"index":9,
			"alarm_time":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_TIME,
			"content":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT9,
		},
		{
			"index":10,
			"alarm_notifier":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_NOTIFIER,
			"content":"",
		},
		{
			"index":11,
			"alarm_content":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_CONTENT,
			"content":LANG.UI_ALARM_WRITE_ALARM_CONTENT,
		}
	];

	var customMessageArr = [];  // 自定义模板结构

	var customTemplateArr = [
		{
			"index":1,
			"alarm_server_ip":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_SERVER_IP,
			"customContent":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT1,
		},
		{
			"index":2,
			"alarm_message_type":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_MESSAGE_TYPE,
			"customContent":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT2,
		},
		{
			"index":3,
			"alarm_event_id":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_EVENT_ID,
			"customContent":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT3,
		},
		{
			"index":4,
			"alarm_source_ip":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_NODE_IP,
			"customContent":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT4,
		},
		{
			"index":5,
			"alarm_source_name":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_SYSTEM_NAME,
			"customContent":"",
		},
		{
			"index":6,
			"alarm_keyword":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_KEY,
			"customContent":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT6,
		},
		{
			"index":7,
			"alarm_type":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_GROUP,
			"customContent":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT7,
		},
		{
			"index":8,
			"alarm_level":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_LEVEL,
			"customContent":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT8,
		},
		{
			"index":9,
			"alarm_time":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_TIME,
			"customContent":LANG.UI_PLATFORM_THIRD_MONITOR_CONTENT9,
		},
		{
			"index":10,
			"alarm_notifier":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_NOTIFIER,
			"customContent":"",
		},
		{
			"index":11,
			"alarm_content":"",
			"title":LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_CONTENT,
			"customContent":LANG.UI_ALARM_WRITE_ALARM_CONTENT,
		}
	];
	var associatedTaskArr = [];  // 关联任务数组
	var oldTaskList;  // 关联数组

	var Message = function(){
		var data = {};
		data.pushflag = $('#pushCheck').get(0).checked;
		data.pushtype = parseInt($('#pushType').val());
		data.mode = parseInt($('#pushMode').val());
		data.protocol = parseInt($('#protocol').val());
		data.domain = $('input[name=pushDomain]').val();
		data.port = $('input[name=pushPort]').val();
		data.username = $('input[name=pushName]').val();
		data.password = btoa($('input[name=pushPassword]').val());
		var jsonData = JSON.stringify(data);
		Metronic.blockUI({target: '#messagepushdiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'messagePush',p:jsonData}, function(data){
			Metronic.unblockUI('#messagepushdiv');
			if(OPREL(data)){
			}
		});
	}

	var addListeners = function(){
		var messagepushform = $('#messagepushform');
		messagepushform.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				pushDomain: {
					required: true,
					domain: true,
				},
				pushPort: {
					required: true,
					port: true,
				}
			},

			invalidHandler: function (event, validator) { //display error alert on form submit
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

			unhighlight: function (element) { // revert the change done by hightlight

			},

			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			},

			submitHandler: function (form) {

			}

		});

		//IP域名验证格式
		$.validator.addMethod("domain", function(value, element) {
			var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			return domain || ipv4;
			//        	return this.optional(element) || /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /([a-z0-9][a-z0-9\-]*?\.(?:com|cn|net|org|gov|info|la|cc|co)(?:\.(?:cn|jp))?)$/i.test(value);
		}, LANG.UI_SETTING_INPUT_DOMAIN);
		//端口号验证
		$.validator.addMethod("port", function(value, element) {
			return this.optional(element) ||  /^([0-9]|[1-9]\d|[1-9]\d{2}|[1-9]\d{3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/i.test(value);
		}, LANG.UI_SETTING_INPUT_PORT);

		$("#messageSubmit").click(function(){
			if (messagepushform.validate().form()) {
				Message();
			}
		});

		$("#messageCancel").click(function(){
			LOCATION('./content/platform/settings/setting_manager.php?tab=6', 'setting_manager');
		});

		$('#protocol').on("change", function(){
			if("1" == this.value){
				$('input[name=pushPort]').val(61613);
			}else if("2" == this.value){
				$('input[name=pushPort]').val(61616);
			}
		});

		//切换消息推送配置开关
		$('#pushCheck').on('switchChange.bootstrapSwitch', function(){
			if(this.checked){
				$('.pushChild').show();
			}else{
				$('.pushChild').hide();
			}
		});

		if(2 != CONF.SOFTWARE){
			$('#messagepush').hide();
			$('#smsDiv').hide();
		}


		/* 告警推送 */
		$('#autoAlarmPush').on('switchChange.bootstrapSwitch', function () {
			if(this.checked){
				autoPushFlag = true;
				$('#autoResponsePush').bootstrapSwitch('disabled', false);
				removeCursorStyle();

			}else{
				autoPushFlag = false;
				$('#autoResponsePush').bootstrapSwitch('state', false);
				$('#autoResponsePush').bootstrapSwitch('disabled', true);
				addCursorStyle();
			}
		})
		/* 响应推送*/
		$('#autoResponsePush').bootstrapSwitch('disabled', true);
		addCursorStyle();
		$('#autoResponsePush').on('switchChange.bootstrapSwitch', function () {
			if(this.checked){
				autoResponseFlag = true;
			}else{
				autoResponseFlag = false;
			}
		})


		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function () {}
		});
		//监听全部取消事件
		$('.bs-deselect-all').on('click',function (){
			$('#taskListErrorTips').show();
		})
		//监听全部选中事件
		$('.bs-select-all').on('click',function (){
			$('#taskListErrorTips').hide();
		})
		// 切换模板
		$('select[name=pushContent]').on('change',function (e){
			if(parseInt($(this).find('option:selected').val()) == CONF.FLAG.SET){
				$('.addTemplate_div').hide();
				// 北斗模板
				initBeiDouSortTable();
			}else{
				$('.addTemplate_div').show();
				// 自定义模板
				initCustomSortTable();
			}
		});
		$('select[name=pushTimeType]').on('change',function (){
			var pushTimeTypeVal = parseInt($(this).find('option:selected').val());
			switch (pushTimeTypeVal){
				case 1:
					$('.lastPushTimeDiv').show();
					$('.timePickerDiv').hide();
					break;
				case 2:
					$('.lastPushTimeDiv').hide();
					$('.timePickerDiv').hide();
					break;
				case 3:
					$('.lastPushTimeDiv').hide();
					$('.timePickerDiv').hide();
					break;
				case 4:
					$('.lastPushTimeDiv').hide();
					$('.timePickerDiv').show();
					break;
			}
		});
		// 取消按钮隐藏抽屉
		$('#add_third_strategy_drawer .cancel').click(function (){
			editRow = {};
			$('#add_third_strategy_drawer').drawer('hide');
		});
		// 切换告警类型  系统告警不能选择任务
		$('select[name=strategy_pushType]').on('change', function () {
			var selectedValue = parseInt($(this).val());
			$('.task_Div').toggle(selectedValue === CONF.FLAG.SET);
		});
		// 点击跳转到监控平台
		$('#to_monitor_platform').on('click',toThirdMonitorPlatform);
		// 添加推送
		$('#add_third_strategy').on('click',initThirdStrategy);
		// 批量删除
		$('#vin_third_push_toolbar #delete_user').on('click', delthirdPushs);
		// 批量启用
		$('#vin_third_push_toolbar #unlock_third_strategy').on('click',unlockThirdStrategy);
		// 批量禁用
		$('#vin_third_push_toolbar #lock_third_strategy').on('click',lockThirdStrategy);
		// 批量删除监控平台
		$('#delete_push_platform').on('click',deletePushPlatform);
		$('#add_template_btn').on('click',addTemplate);
	}


	//加载历史消息推送数据
	var initOldInfo = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'messagePushOldInfo',p:{}}, function(data){
			data = JSON.parse(data);
			$('#pushCheck').bootstrapSwitch('state', data.pushflag);
			if(!data.pushtype){
				$('#pushType').val('1');
			}else{
				$('#pushType').val(data.pushtype);
			}
			if(!data.mode){
				$('#pushMode').val('1');
			}else{
				$('#pushMode').val(data.mode);
			}
			if(!data.protocol){
				$('#protocol').val('1');
			}else{
				$('#protocol').val(data.protocol);
			}
			$('input[name=pushDomain]').val(data.domain);
			if(data.port){
				$('input[name=pushPort]').val(data.port);
			}
			$('input[name=pushName]').val(data.username);
			$('input[name=pushPassword]').val(data.password);
		});
	}

	function initTableHeight() {
		//拿到父窗口的高度
		var height;
		var panelH = window.innerHeight;

		height = panelH - 381;

		$("#pushMangerDiv .fixed-table-body").css({
			"height": height
		});
	}

	/**
	 * 添加第三方监控平台
	 */
	var toThirdMonitorPlatform = function (){
		LOCATION('./content/platform/settings/settingstab/monitor_platform.php', 'setting_manager');
	}

	/**
	 * 添加第三方策略
	 */
	var initThirdStrategy = function (){
		// 默认推送名
		getMessageDefaultName();
		clickEffect(this);
		// 清空
		clearAllOption();
		addPushNameRule();
		initTimePicker();
		initBeiDouSortTable();
	}

	var clearAllOption = function (){
		editRow = {};
		$('.lastPushTimeSpinnerDiv').spinner('value', 1);
		$('input[name=strategyName]').val('');
		$('#taskList').selectpicker('val','');
		// 推送类型
		var pushTypeEle = document.getElementById('strategy_pushType');
		for (var i =0;i<pushTypeEle.options.length;i++){
			if(pushTypeEle.options[i].value == CONF.FLAG.SET){
				$('.task_Div').show();
				pushTypeEle.options[i].selected = true;
			}
		}
		// 同步起始点
		$('.lastPushTimeDiv').show();
		var pushTimeTypeEle = document.getElementById('pushTimeType');
		for (var i =0;i<pushTimeTypeEle.options.length;i++){
			if(pushTimeTypeEle.options[i].value == CONF.FLAG.SET){
				pushTimeTypeEle.options[i].selected = true;
			}
		}
		// 模板
		var pushContentEle = document.getElementById('pushContent');
		for (var i =0;i<pushContentEle.options.length;i++){
			if(pushContentEle.options[i].value == CONF.FLAG.SET){
				pushContentEle.options[i].selected = true;
				break;
			}
		}
		// 告警自动推送
		$('#autoAlarmPush').bootstrapSwitch('state', false);
		// 响应自动推送
		$('#autoResponsePush').bootstrapSwitch('state', false);
		// 隐藏显示元素
		$('#taskListErrorTips').hide();
		$('.timePickerDiv').hide();
		$('.add_push_title').show();
		$('.edit_push_title').hide();
		$('.view_push_title').hide();
		$('#add_submit').show();
		$('#edit_submit').hide();
		clearValidate($('#form_sample_1'));
	}

	var clickEffect = function (element) {
		$(element).addClass('btn-hover');
		setTimeout(function() {
			$(element).removeClass('btn-hover');
		}, 300); // 0.3秒后恢复原样
	}

	/**
	 * 初始化时间控件
	 */
	var initTimePicker = function (){
		$(".form_datetime").datetimepicker({
			language:  'zh-CN',
			autoclose: true,
			isRTL: Metronic.isRTL(),
			format: "yyyy-MM-dd hh:ii:ss",
			pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
			forceParse:false
		});
	}

	/**
	 * 初始化排序表格(北斗模板)
	 */
	var initBeiDouSortTable = function (alarmMessageStructure){
		if(sortObj){
			sortObj.destroy();
		}
		// 如果alarmMessageStructure存在 循环
		if(!!alarmMessageStructure){
			for(var i=0;i<beiDouTemplateArr.length;i++){
				alarmMessageStructure[i].title = beiDouTemplateArr[i].title;
				alarmMessageStructure[i].title = beiDouTemplateArr[i].title;
			}
		}
		var structureObj = alarmMessageStructure ?? beiDouTemplateArr;
		var pushTemplate = document.getElementById('pushTemplate');
		$('#pushTemplate').empty();
		pushTemplate.innerHTML = ''; // Clear existing content
		beiDouMessageArr = [];
		// 北斗模板是固定顺序
		for(var i =0;i<structureObj.length;i++){
			var li = document.createElement('li');
			li.className = 'list-group-item';
			// li.setAttribute('data-id', 'sffss');
			// 添加内容名称
			var titleContent = `<div class="title" style="width: 100px;text-align: right;padding-right: 20px;">${structureObj[i].title}</div>`;
			li.insertAdjacentHTML('beforeend', titleContent);
			if(i !== 4 && i !== 9){
				// 添加内容
				var Content = `<div class="content" style="flex-grow: 1">${structureObj[i].content}</div>`;
				li.insertAdjacentHTML('beforeend', Content);
			}
			if(i == 4){
				// 输入框
				var inputEle = `<div class="" style="flex-grow: 1"><input class="content form-control select2me input_${structureObj[i].index}" value="${structureObj[i].alarm_source_name}"></div>`;
				li.insertAdjacentHTML('beforeend', inputEle);
			}
			if(i == 9){
				// 输入框
				var inputEle = `<div class="" style="flex-grow: 1"><input class="content form-control select2me input_${structureObj[i].index}" value="${structureObj[i].alarm_notifier}"></div>`;
				li.insertAdjacentHTML('beforeend', inputEle);
			}
			// 创建图标
			var iconContent = `<div class="icon display-inline-flex" style="margin-left: auto;"></div>`;
			li.insertAdjacentHTML('beforeend', iconContent);
			pushTemplate.appendChild(li);
			switch (structureObj[i].index){
				case 1:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 2:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 3:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 4:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 5:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 6:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 7:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 8:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 9:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 10:
					beiDouMessageArr.push(structureObj[i]);
					break;
				case 11:
					beiDouMessageArr.push(structureObj[i]);
					break;
			}
		}
	}


	/**
	 * 初始化排序表格(自定义模板)
	 */
	var initCustomSortTable = function(alarmMessageStructure) {
		var structureObj = alarmMessageStructure ?? customTemplateArr;
		var pushTemplate = document.getElementById('pushTemplate');
		$('#pushTemplate').empty();
		customMessageArr = []; // Clear existing customMessageArr
		structureObj.forEach(function(item, index) {
			var li = document.createElement('li');
			li.className = 'list-group-item';
			if (item.content) {
				var titleContent = `<div class="title" index="${item.index}" style="width: 100px;text-align: right;padding-right: 20px;"><input class="content form-control select2me title_input_${item.index}" value="${item.title}"></div>`;
				// 添加自定义内容
				var inputEle = `<div class="" ><input class="content form-control select2me content_input_${item.index}" value="${item.customContent}"></div>`;
				li.insertAdjacentHTML('beforeend', titleContent);
				li.insertAdjacentHTML('beforeend', inputEle);

				var titleInput = li.querySelector(`.title_input_${item.index}`);
				var contentInput = li.querySelector(`.content_input_${item.index}`);

				// 添加输入事件监听器
				titleInput.addEventListener('input', function() {
					var title = this.value;
					var content = contentInput.value;
					var itemIndex = item.index - 1;
					customMessageArr[itemIndex].title = title;
					customMessageArr[itemIndex].content = content;
				});

				contentInput.addEventListener('input', function() {
					var title = titleInput.value;
					var content = this.value;
					var itemIndex = item.index - 1;
					customMessageArr[itemIndex].title = title;
					customMessageArr[itemIndex].content = content;
				});
			} else{
				// 添加内容名称
				var titleContent = `<div class="title" index="${item.index}" style="width: 100px;text-align: right;padding-right: 20px;">${item.title}</div>`;
				// 添加自定义内容
				var inputEle = `<div class="">${item.customContent}</div>`;
				li.insertAdjacentHTML('beforeend', titleContent);
				li.insertAdjacentHTML('beforeend', inputEle);
			}

			// 如果是应用系统名称或告警通知人，则添加对应的输入框
			if (item.title == LANG.UI_PLATFORM_THIRD_MESSAGE_SYSTEM_NAME) {
				var inputEle = `<div class="" style="flex-grow: 1;"><input class="content form-control select2me input_5" value="${item.alarm_source_name}"></div>`;
				li.insertAdjacentHTML('beforeend', inputEle);
			} else if (item.title == LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_NOTIFIER) {
				var inputEle = `<div class="" style="flex-grow: 1;"><input class="content form-control select2me input_10" value="${item.alarm_notifier}"></div>`;
				li.insertAdjacentHTML('beforeend', inputEle);
			}

			// 创建图标和删除按钮，并为删除按钮添加自定义属性存储对应的索引值
			var iconContent = `<div class="icon display-inline-flex" style="margin-left: auto">
            <button class="deleteButton bdnone bgtransparent">
                <a onclick="return false;"><i class="viconfont vicon-a-Deleteshanchu1 deleteIcon"></i></a>
            </button>
            <i class="viconfont vicon-a-Direction-adjustment-threefangxiangxiaozhun"></i>
        </div>`;
			li.insertAdjacentHTML('beforeend', iconContent);

			pushTemplate.appendChild(li);
			customMessageArr.push(item); // 将每个项目添加到 customMessageArr
		});
		// 给每个按钮添加点击事件监听器
		var deleteButtons = document.querySelectorAll('.deleteButton');
		deleteButtons.forEach(function(deleteButton) {
			deleteButton.addEventListener('click', function() {
				var listItem = this.closest('.list-group-item');
				if (listItem) {
					// 获取删除按钮所在的位置（即要删除的项目的位置）
					var indexToRemove = Array.from(listItem.parentNode.children).indexOf(listItem);

					// 从 customMessageArr 中删除对应的项目
					customMessageArr.splice(indexToRemove, 1);

					// 更新剩余项目的 index
					customMessageArr.forEach(function(item, i) {
						item.index = i + 1;
					});

					// 从 DOM 中移除列表项
					listItem.remove();

					if (customMessageArr.length <= 5) {
						var excessItemIndex = 6; // 6代表剩余项目数达到5时，多余的项的index值
						var excessListItem = document.querySelector(`.list-group-item .title[index="${excessItemIndex}"]`);
						if (excessListItem) {
							excessListItem.closest('.list-group-item').remove();
							customMessageArr.splice(excessItemIndex - 1, 1);
							customMessageArr.forEach(function(item, i) {
								item.index = i + 1;
							});
						}
					}
				}
			});
		});

		// 使用 Sortable 库对列表进行排序
		sortObj = new Sortable(pushTemplate, {
			animation: 150,
			swap: true,
			swapClass: 'highlight',
			onEnd: function(evt) {
				// 获取所有列表项
				var listItems = pushTemplate.querySelectorAll('.list-group-item');

				// 更新数组customMessageArr中对应位置的元素的索引值
				var updatedCustomMessageArr = [];
				listItems.forEach(function(listItem, newIndex) {
					var oldIndex = parseInt(listItem.querySelector('.title').getAttribute('index'));

					// Check if the item exists in customMessageArr before updating its index
					if (oldIndex > 0 && oldIndex <= customMessageArr.length) {
						var item = customMessageArr[oldIndex - 1];
						item.index = newIndex + 1;
						updatedCustomMessageArr.push(item);

						// 更新DOM中的索引值
						listItem.querySelector('.title').setAttribute('index', newIndex + 1);
					}
				});

				// 更新customMessageArr数组
				customMessageArr = updatedCustomMessageArr;
			},
		});

	}


	var checkEvent = function (tableId, btnId) {
		let select = $('' + tableId + '').bootstrapTable('getSelections');
		if (select.length == 0) {
			$('' + btnId + '').addClass('icon-gray-delete');
			$('' + btnId + '').removeClass('icon-white-delete');
			$('' + btnId + '').removeClass('select-delete-btn');
			$('' + btnId + '').addClass('cancel-delete-btn');
		} else {
			$('' + btnId + '').removeClass('icon-gray-delete');
			$('' + btnId + '').addClass('icon-white-delete');
			$('' + btnId + '').removeClass('cancel-delete-btn');
			$('' + btnId + '').addClass('select-delete-btn');
		}
	}

	/**
	 * 校验表单
	 */
	var handleAddValidation = function (){
		var form1 = $('#form_sample_1');
		var success1 = $('.alert-success', form1);
		var error1 = $('.alert-danger', form1);

		form1.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "", // validate all fields including form hidden input
			rules:{
				strategyName:{
					required:true,
					nicknameAvailable:true,
				},
			},

			invalidHandler: function (event, validator) { //display error alert on form submit
				// 表单验证失败触发
				success1.hide();
				error1.show();
			},

			highlight: function (element) { // hightlight error inputs
				// 表单验证失败，对未通过的字段
				$(element).closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},

			success: function (label, element) {
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
			},

			submitHandler: function (form) {
				success1.show();
				error1.hide();
			}

		});

		$.validator.addMethod('nicknameAvailable',function (value){
			var data = {};
			data.strategy_name = value;
			data.edit_strategy_name = editRow.push_strategy_name??'';
			var result= false;
			pAjaxRequest(data,'/api/v1/system/message/check','GET',function (res){
				result = res.data.result;
			},async = false);
			return result;
		},LANG.UI_PLATFORM_THIRD_MESSAGE_NICKNAME_EXIST);

		$('#add_submit').on('click',function (){
			if(form1.validate().form()){
				// 提交
				addSubmit();
			}else{
				UIToastr.showWarning(LANG.UI_PLATFORM_THIRD_MESSAGE_CHECK_INFO);
			}
		});

		$('#edit_submit').on('click',function (){
			if(form1.validate().form()){
				// 提交
				editSubmit(editRow);
			}else{
				UIToastr.showWarning(LANG.UI_PLATFORM_THIRD_MESSAGE_CHECK_INFO);
			}
		})

	}

	// 添加提交
	var addSubmit = function (){
		var data = {};
		// 别名
		data.nickname = $('input[name=strategyName]').val();
		// 推送类型
		data.alarm_type = $('select[name=strategy_pushType]').find('option:selected').val();
		// 监控平台
		data.monitor_platform_uuid = $('select[name=monitorPlatform]').find('option:selected').val();
		// 同步起始点
		data.sync_time_type = $('select[name=pushTimeType]').find('option:selected').val();
		if(data.sync_time_type == 1){
			data.last_push_time = getDataTime(parseInt($('input[name=lastPushTime]').val()));
			data.start_push_time = getDataTime(parseInt($('input[name=lastPushTime]').val()));
		}else if(data.sync_time_type == 3){
			data.last_push_time = getDataTime(0);
			data.start_push_time = getDataTime(0);
		} else if(data.sync_time_type == 4){
			data.last_push_time = $('input[name=timeinput]').val();
			data.start_push_time = $('input[name=timeinput]').val();
		}else{
			data.last_push_time = '';
			data.start_push_time = '';
		}
		// 关联任务 array type
		data.all_task_flag = selectTask();
		if(parseInt(data.alarm_type) == CONF.FLAG.UNSET){
			data.task_uuid = [];
		}else{
			associatedTaskArr.push($('select[name=taskList]').find('option:selected').val());
			data.task_uuid = $('#taskList').selectpicker('val');
			if(data.task_uuid.length == 0){
				$('#taskListErrorTips').show();
				return;
			}
		}
		// 推送模板选项
		data.push_template_type = $('select[name=pushContent]').find('option:selected').val();
		if(data.push_template_type == CONF.FLAG.SET){
			// 北斗模板
			// 模板内容
			for(var i=0;i<beiDouMessageArr.length;i++){
				if(i == 4){
					beiDouMessageArr[i].alarm_source_name = $('.input_5').val();
				}
				if(i == 9){
					beiDouMessageArr[i].alarm_notifier = $('.input_10').val();
				}
			}
			data.alarm_message_structure = beiDouMessageArr;
		}else if(data.push_template_type == CONF.FLAG.UNSET){
			// 自定义模板
			for(var i=0;i<customMessageArr.length;i++){
				if(customMessageArr[i].title == LANG.UI_PLATFORM_THIRD_MESSAGE_SYSTEM_NAME){
					customMessageArr[i].alarm_source_name = $('.input_5').val();
				}
				if(customMessageArr[i].title == LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_NOTIFIER){
					customMessageArr[i].alarm_notifier = $('.input_10').val();
				}
			}
			data.alarm_message_structure = customMessageArr;
		}
		// 告警自动推送
		data.auto_push_flag = autoPushFlag;
		// 响应自动推送
		data.push_response_type = autoResponseFlag;
		$('#add_third_strategy_drawer').drawer('hide');
		Metronic.blockUI({
			target: '#pushMangerDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/system/message','POST',function (res){
			Metronic.unblockUI('#pushMangerDiv');
			if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MESSAGE_ADD)) {
				$('#thirdpushTable').bootstrapTable('refresh', {query: {offset:0}});
			}
		})
	}


	/**
	 * 初始化第三方推送表格
	 */
	var initThirdTable =function (){
		var operationFormatter = function (value, row, index, field) {
			var button = '<div class="btn-group">';
			if (index > 5) {
				button = '<div class="btn-group dropup">';
			}

			button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
				'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
				'' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
				'</button>' +
				'<ul class="dropdown-menu min-width100" role="menu">';

			if(row.status == CONF.FLAG.UNSET){
				// 状态 1:启用 2:禁用
				// 启用状态时，不能进行编辑、删除操作
				// 查看&修改
				if($.inArray("p_message_push_third_edit", CONF.PERMISSION_ARR)!= -1){
					button += '<li class="edit"><a href="javascript:;"  data-toggle="drawer" data-target="#add_third_strategy_drawer" aria-haspopup="true" aria-expanded="false" ><i class="viconfont vicon-edit-new"></i>'+ LANG.UI_PUBLIC_EDIT +'</a></li>';
				}
			}
			if(row.status == CONF.FLAG.SET){
				// 启用状态
				button += '<li class="view"><a href="javascript:;"  data-toggle="drawer" data-target="#add_third_strategy_drawer" aria-haspopup="true" aria-expanded="false" ><i class="viconfont vicon-a-Eyesyanjing"></i>'+ LANG.UI_MICROSOFT365_VIEW_DETAILS +'</a></li>';
			}
			button += '</ul></div>';
			return button;
		}

		var op = {
			'click .edit':function (event, value, row, index){
				editRow = row;
				// 隐藏提交按钮
				$('#taskListErrorTips').hide();
				$('#add_submit').hide();
				$('#edit_submit').show();
				$('.add_push_title').hide();
				$('.edit_push_title').show();
				$('.view_push_title').hide();
				// 回填表格
				backfillForm(row);
				// 清除验证
				clearValidate($('#form_sample_1'));
				initTimePicker();
				addPushNameRule();
			},
			'click .delete':function (event, value, row, index){
				var data = {};
				data.strategy_uuid = [];
				data.strategy_uuid.push(row.push_strategy_uuid);
				$('#add_third_strategy_drawer').drawer('hide');
				Metronic.blockUI({
					target: '#pushMangerDiv',
					animate: true,
					cenrerY: true,
				});
				pAjaxRequest(data,'/api/v1/system/message','DELETE',function (res){
					Metronic.unblockUI('#pushMangerDiv');
					if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MESSAGE_DELETE)) {
						$('#thirdpushTable').bootstrapTable('refresh', {query: {offset:0}});
					}
				});
			},
			'click .unlock':function (event, value, row, index){
				var data = {};
				data.strategy_uuid = [];
				data.strategy_uuid.push(row.push_strategy_uuid);
				Metronic.blockUI({
					target: '#pushMangerDiv',
					animate: true,
					cenrerY: true,
				});
				pAjaxRequest(data,'/api/v1/system/message/unlock','PUT',function (res){
					Metronic.unblockUI('#pushMangerDiv');
					if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MESSAGE_UNLOCK)) {
						$('#thirdpushTable').bootstrapTable('refresh', {query: {offset:0}});
					}
				});
			},
			'click .lock':function (event, value, row, index){
				var data = {};
				data.strategy_uuid = [];
				data.strategy_uuid.push(row.push_strategy_uuid);
				Metronic.blockUI({
					target: '#pushMangerDiv',
					animate: true,
					cenrerY: true,
				});
				pAjaxRequest(data,'/api/v1/system/message/lock','PUT',function (res){
					Metronic.unblockUI('#pushMangerDiv');
					if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MESSAGE_LOCK)) {
						$('#thirdpushTable').bootstrapTable('refresh', {query: {offset:0}});
					}
				});
			},
			'click .view':function (event,value,row,inde){
				$('#add_submit').hide();
				$('#edit_submit').hide();
				$('.add_push_title').hide();
				$('.edit_push_title').hide();
				$('.view_push_title').show();
				$('#taskListErrorTips').hide();
				// 回填表格
				backfillForm(row);
				// 清除验证
				clearValidate($('#form_sample_1'));
				removePushNameRule();
			}
		};

		var beforeInput = '';
		var afterInput = '';
		if($.inArray("p_message_push_third_delete", CONF.PERMISSION_ARR)!= -1){
			beforeInput += `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete_push_platform"></button></div>`;
		}
		if($.inArray("p_message_push_third_add", CONF.PERMISSION_ARR)!= -1){
			afterInput += `<button type="button" class="btn table-toolbar-btn dropdown-toggle btn-font flex_center btn-title p-lr8" id="add_third_strategy" data-toggle="drawer" data-target="#add_third_strategy_drawer" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
		}
		if($.inArray("p_message_push_third_enable", CONF.PERMISSION_ARR)!= -1){
			afterInput += `<button type="button" class="btn table-toolbar-btn dropdown-toggle btn-font flex_center btn-title p-lr8" id="unlock_third_strategy" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-a-Unlockjiesuo-0111 mr4"></i>
                                <span>` + LANG.BILLING_ON_LOCK + `</span>
                            </button>`;
		}
		if($.inArray("p_message_push_third_disable", CONF.PERMISSION_ARR)!= -1){
			afterInput += `<button type="button" class="btn table-toolbar-btn dropdown-toggle btn-font flex_center btn-title p-lr8" id="lock_third_strategy" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i>
                                <span>` + LANG.BILLING_OFF_LOCK + `</span>
                            </button>`;
		}
		if($.inArray("p_message_push_third_config", CONF.PERMISSION_ARR)!= -1){
			afterInput += `<button type="button" class="btn table-toolbar-btn dropdown-toggle btn-font flex_center btn-title p-lr8" id="to_monitor_platform" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-a-Electrocardiogramxindiantu mr4"></i>
                                <span>` + LANG.UI_PLATFORM_THIRD_MESSAGE_PUSH_MONITOR_PLATFORM_CONFIG + `</span>
                            </button>`;
		}
		let options = {
			toolbarId: '#vin_third_push_toolbar',
			buttonsToolbar: '#vin_third_push_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/system/message',
			vin_method: 'GET',
			placeholder: LANG.UI_SEARCH_NICKNAME, //搜索框的placeholder
			searchInput: true, //搜索框
			searchClass: 'platformSearch', //自定义的搜索框类名
			searchSelector: '.platformSearch', //选择使用自定义搜索框
			showColumns: true,
			showExport: true,
			paginationLoop: false,
			onResetView: initTableHeight,
			vin_params: function () {
				let params = {};
				params.search = $('#vin_select_resource_toolbar .resourceSearch').val();
				return params;
			},
			onCheck: function () {
				modifyDelStyle('thirdpushTable', 'delete_push_platform');
			},
			onUncheck: function () {
				modifyDelStyle('thirdpushTable', 'delete_push_platform');
			},
			onCheckAll: function () {
				modifyDelStyle('thirdpushTable', 'delete_push_platform');
			},
			onUncheckAll: function () {
				modifyDelStyle('thirdpushTable', 'delete_push_platform');
			},
			PostBody: function (){
				$('#thirdpushTable th[data-field = "push_strategy_name"]').css('width','15%');
				$('#thirdpushTable th[data-field = "platform_name"]').css('width','15%');
				$('#thirdpushTable th[data-field = "start_push_time"]').css('width','15%');
				$('#thirdpushTable th[data-field = "status"]').css('width','10%');
				$('#thirdpushTable th[data-field = "auto_push_flag"]').css('width','10%');
				$('#thirdpushTable th[data-field = "push_response_type"]').css('width','10%');
				$('#thirdpushTable th[data-field = "task_names"]').css('width','20%');
				$('#thirdpushTable th[data-field = "operation"]').css('width','5%');
			},
			customTool: {
				beforeInput: beforeInput,
				afterInput: afterInput,
			},
			columns:[
				{
					// field:'checked',
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (value, row, index, field) {
						if (row.checked === false) {
							return {
								disabled: true
							};
						}
					},
				},
				{
					field: 'push_strategy_name',
					title: LANG.UI_SEARCH_NICKNAME,
				},
				{
					field: 'platform_name',
					title: LANG.UI_PLATFORM_THIRD_MESSAGE_PUSH_MONITOR_PLATFORM,
				},
				{
					field: 'start_push_time',
					title: LANG.UI_PLATFORM_THIRD_MESSAGE_PUSH_LAST_PUSH_TIME,
					formatter:function (value){
						if(value == '0000-00-00 00:00:00'){
							return '--'
						}
						return value;
					}
				},
				{
					field: 'status',
					title: LANG.UI_PUBLIC_STATUS,
					sortable: false,
					formatter: function (value, row, index, field) {
						if (value == CONF.FLAG.SET) {
							return '<span class="label label-sm label-success status-icon">' + LANG.BILLING_ON_LOCK + '</span>'
						} else {
							return '<span class="label label-sm label-danger status-icon"  style="width:auto; min-width:40px">' + LANG.BILLING_OFF_LOCK + '</span>';
						}
					},
				},
				{
					field: 'auto_push_flag',
					title: LANG.UI_PLATFORM_THIRD_MESSAGE_AUTO_PUSH_ALARM,
					formatter: function (value, row, index, field){
						if (value == CONF.FLAG.SET) {
							return '<span class="label label-sm label-success status-icon">' + LANG.BILLING_ON_LOCK + '</span>'
						} else {
							return '<span class="label label-sm label-default status-icon"  style="width:auto; min-width:40px">' + LANG.UI_PUBLIC_OFF + '</span>';
						}
					},
				},
				{
					field: 'push_response_type',
					title: LANG.UI_PLATFORM_THIRD_MESSAGE_PUSH_RESPONSE_MESSAGE,
					formatter: function (value, row, index, field){
						if (value == CONF.FLAG.UNSET) {
							return '<span class="label label-sm label-success status-icon">' + LANG.BILLING_ON_LOCK + '</span>'
						} else {
							return '<span class="label label-sm label-default status-icon"  style="width:auto; min-width:40px">' + LANG.UI_PUBLIC_OFF + '</span>';
						}
					},
				},
				{
					field: 'task_names',
					title: LANG.UI_PLATFORM_ASSOCIA_TASK,
				},
				{
					field: 'operation',
					title: LANG.UI_PUBLIC_OPERATION,
					formatter: operationFormatter,
					events: op,
					opButton: true,
					clickToSelect: false, //不可通过点击行选中
					sortable: false, //默认可排序，禁用排序才写此项
				},
			],
		};
		$('#thirdpushTable').baseTableConfig().init(options);
	}

	/**
	 * 提交修改
	 * @param row
	 */
	var editSubmit = function (row){
		var data = {};
		// 别名
		data.nickname = $('input[name=strategyName]').val();
		//
		data.strategy_uuid = row.push_strategy_uuid;
		// 推送类型
		data.alarm_type = $('select[name=strategy_pushType]').find('option:selected').val();
		// 监控平台
		data.monitor_platform_uuid = $('select[name=monitorPlatform]').find('option:selected').val();
		// 同步起始点
		data.sync_time_type = $('select[name=pushTimeType]').find('option:selected').val();
		if(data.sync_time_type == 1){
			data.last_push_time = getDataTime(parseInt($('input[name=lastPushTime]').val()));
			data.start_push_time = getDataTime(parseInt($('input[name=lastPushTime]').val()));
		} else if(data.sync_time_type == 3){
			data.last_push_time = getDataTime(0);
			data.start_push_time = getDataTime(0);
		} else if(data.sync_time_type == 4){
			data.last_push_time = $('input[name=timeinput]').val();
			data.start_push_time = $('input[name=timeinput]').val();
		} else{
			data.last_push_time = '';
			data.start_push_time = '';
		}
		// 关联任务 array type
		data.all_task_flag = selectTask();
		if(parseInt(data.alarm_type) == CONF.FLAG.UNSET){
			data.task_uuid = [];
		}else{
			associatedTaskArr.push($('select[name=taskList]').find('option:selected').val());
			data.task_uuid = $('#taskList').selectpicker('val');
			if(data.task_uuid.length == 0){
				console.log($('#taskListErrorTips'));
				$('#taskListErrorTips').show();
				return;
			}
		}

		// 推送模板选项
		data.push_template_type = $('select[name=pushContent]').find('option:selected').val();
		// 模板内容
		if(parseInt(data.push_template_type) == CONF.FLAG.SET){
			// 北斗模板
			// 模板内容
			for(var i=0;i<beiDouMessageArr.length;i++){
				if(i == 4){
					beiDouMessageArr[i].alarm_source_name = $('.input_5').val();
				}
				if(i == 9){
					beiDouMessageArr[i].alarm_notifier = $('.input_10').val();
				}
			}
			data.alarm_message_structure = beiDouMessageArr;
		}else if(parseInt(data.push_template_type) == CONF.FLAG.UNSET){
			// 自定义模板
			for(var i=0;i<customMessageArr.length;i++){
				if(customMessageArr[i].title == LANG.UI_PLATFORM_THIRD_MESSAGE_SYSTEM_NAME){
					customMessageArr[i].alarm_source_name = $('.input_5').val();
				}
				if(customMessageArr[i].title == LANG.UI_PLATFORM_THIRD_MESSAGE_ALARM_NOTIFIER){
					customMessageArr[i].alarm_notifier = $('.input_10').val();
				}
			}
			data.alarm_message_structure = customMessageArr;
		}
		// 告警自动推送
		data.auto_push_flag = autoPushFlag;
		// 响应自动推送
		data.push_response_type = autoResponseFlag;
		$('#add_third_strategy_drawer').drawer('hide');
		Metronic.blockUI({
			target: '#pushMangerDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/system/message','PUT',function (res){
			Metronic.unblockUI('#pushMangerDiv');
			if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MESSAGE_MODIFY)) {
				$('#thirdpushTable').bootstrapTable('refresh', {query: {offset:0}});
			}
		});

	}

	/**
	 * 初始化spinner
	 */
	var initSpinner = function(){
		$('.lastPushTimeSpinnerDiv').spinner({value:1, step: 1, min: 1, max: 999});
	}

	/**
	 * 获取监控平台列表
	 */
	var getThirdMonitorPlatform = function (){
		pAjaxRequest({},'/api/v1/system/message/monitor_platform','GET',function (res){
			var rows = res.data.rows;
			var monitorPlatformEle = document.getElementById('monitorPlatform');
			rows.forEach(item=>{
				var option = document.createElement('option');
				option.value = item.platform_uuid;
				option.text = item.platform_name;
				monitorPlatformEle.appendChild(option);
			});
		});
	}

	/**
	 * 获取关联任务列表
	 */
	var getAssociatedTask = function (){
		pAjaxRequest({},'/api/v1/system/message/associated_task','GET',function (res){
			var rows = res.data.rows;
			var taskListEle = document.getElementById('taskList');
			var taskList = $('#taskList');
			taskList.empty();
			rows.forEach(item=>{
				var option = document.createElement('option');
				option.value = item.task_uuid;
				option.text = item.task_name;
				taskListEle.appendChild(option);
			});
			taskList.selectpicker('refresh');
		});
	}

	/**
	 * 获取日期  0000-00-00 00:00:00
	 */
	var getDataTime = function (day){
		// 获取当前日期
		var currentDate = new Date();

		currentDate.setDate(currentDate.getDate() - day);

		// 获取x天前的年、月、日、时、分、秒
		var year = currentDate.getFullYear();
		var month = ('0' + (currentDate.getMonth() + 1)).slice(-2); // 月份从0开始，需要加1，并且确保两位数
		var day = ('0' + currentDate.getDate()).slice(-2); // 确保两位数
		var hours = ('0' + currentDate.getHours()).slice(-2); // 确保两位数
		var minutes = ('0' + currentDate.getMinutes()).slice(-2); // 确保两位数
		var seconds = ('0' + currentDate.getSeconds()).slice(-2); // 确保两位数

		// 格式化日期字符串
		var daysAgo = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
		return daysAgo;
	}

	/**
	 * 回填表单
	 */
	var backfillForm = function (row){
		pAjaxRequest({},'/api/v1/system/message/'+row.push_strategy_uuid,'GET',function (res){
			var data = res.data;
			// 别名
			$('input[name=strategyName]').val(data.push_strategy_name);
			// 推送类型
			var strategyPushTypeEle = document.getElementById('strategy_pushType');
			for (var i =0;i<strategyPushTypeEle.options.length;i++){
				if(strategyPushTypeEle.options[i].value == data.alarm_type){
					strategyPushTypeEle.options[i].selected = true;
					break;
				}
			}
			$('.task_Div').toggle(data.alarm_type === CONF.FLAG.SET);
			// 监控平台
			var monitorPlatformEle = document.getElementById('monitorPlatform');
			for (var i =0;i<monitorPlatformEle.options.length;i++){
				if(monitorPlatformEle.options[i].value == data.platform_uuid){
					monitorPlatformEle.options[i].selected = true;
					break;
				}
			}
			// 同步起始点
			var pushTimeTypeEle = document.getElementById('pushTimeType');
			$('.lastPushTimeDiv').hide();
			$('.timePickerDiv').hide();
			for (var i =0;i<pushTimeTypeEle.options.length;i++){
				if(pushTimeTypeEle.options[i].value == data.push_time_type){
					pushTimeTypeEle.options[i].selected = true;
					if(data.push_time_type == 1){
						$('.lastPushTimeDiv').show();
						$('.timePickerDiv').hide();
						var days = Math.floor((new Date().getTime() - new Date(data.start_push_time).getTime()) / (1000 * 60 * 60 * 24));
						$('.lastPushTimeSpinnerDiv').spinner('value', days);
					}else if(data.push_time_type == 4){
						$('.lastPushTimeDiv').hide();
						$('.timePickerDiv').show();
						$('input[name=timeinput]').val(data.start_push_time);
					}
					break;
				}
			}
			// 关联任务
			oldTaskList = JSON.parse(data.task_uuid);
			var taskList = $('#taskList');
			$('#taskList').selectpicker('val', oldTaskList);
			taskList.selectpicker('refresh');

			// 推送模板
			var pushContentEle = document.getElementById('pushContent');
			for (var i =0;i<pushContentEle.options.length;i++){
				if(pushContentEle.options[i].value == data.push_template_type){
					pushContentEle.options[i].selected = true;
					break;
				}
			}
			var alarmMessageStructure = JSON.parse(data.alarm_message_structure);
			if(data.push_template_type == CONF.FLAG.SET){
				// 推送内容-北斗模板
				initBeiDouSortTable(alarmMessageStructure);
			}else if(data.push_template_type == CONF.FLAG.UNSET){
				// 推送内容-自定义模板
				initCustomSortTable(alarmMessageStructure);
				$('.addTemplate_div').show();
			}
			// 告警自动推送
			$('#autoAlarmPush').bootstrapSwitch('state', data.auto_push_flag);
			$('#autoResponsePush').bootstrapSwitch('state', data.push_response_type);
		});
	}

	/**
	 * 提示
	 */
	var tipDeletePush = function () {
		UIToastr.showInfo(LANG.UI_PLATFORM_THIRD_MESSAGE_DELETE, LANG.UI_PLATFORM_THIRD_MESSAGE_DELETE_TIP);
	}

	var tipLockPush = function () {
		UIToastr.showInfo(LANG.UI_PLATFORM_THIRD_MESSAGE_LOCK, LANG.UI_PLATFORM_THIRD_MESSAGE_LOCK_TIP);
	}

	var tipunLockPush = function () {
		UIToastr.showInfo(LANG.UI_PLATFORM_THIRD_MESSAGE_UNLOCK, LANG.UI_PLATFORM_THIRD_MESSAGE_UNLOCK_TIP);
	}

	var tipDeleteunLockPush = function () {
		UIToastr.showInfo(LANG.UI_PLATFORM_THIRD_MESSAGE_UNLOCK, LANG.UI_PLATFORM_THIRD_MESSAGE_UNLOCK_TIP2);
	}


	/**
	 * 批量删除
	 */
	var delthirdPushs = function (){
		clickEffect(this);
		let select = $('#thirdpushTable').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].push_strategy_uuid);
		}
		if (!select.length) {
			return tipDeletePush();
		}
		var data = {};
		data.strategy_uuid = ids;
		Metronic.blockUI({
			target: '#pushMangerDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/system/message','DELETE',function (res){
			Metronic.unblockUI('#pushMangerDiv');
			if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MESSAGE_DELETE)) {
				$('#thirdpushTable').bootstrapTable('refresh', {query: {offset:0}});
			}
		});
	}

	/**
	 * 批量启用
	 */
	var unlockThirdStrategy = function (){
		clickEffect(this);
		let select = $('#thirdpushTable').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].push_strategy_uuid);
		}
		if (!select.length) {
			return tipunLockPush();
		}
		var data = {};
		data.strategy_uuid = ids;
		Metronic.blockUI({
			target: '#pushMangerDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/system/message/unlock','PUT',function (res){
			Metronic.unblockUI('#pushMangerDiv');
			if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MESSAGE_UNLOCK)) {
				$('#thirdpushTable').bootstrapTable('refresh', {query: {offset:0}});
			}
		});
	}

	/**
	 * 批量禁用
	 */
	var lockThirdStrategy = function (){
		clickEffect(this);
		let select = $('#thirdpushTable').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].push_strategy_uuid);
		}
		if (!select.length) {
			return tipLockPush();
		}
		var data = {};
		data.strategy_uuid = ids;
		Metronic.blockUI({
			target: '#pushMangerDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/system/message/lock','PUT',function (res){
			Metronic.unblockUI('#pushMangerDiv');
			if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MESSAGE_LOCK)) {
				$('#thirdpushTable').bootstrapTable('refresh', {query: {offset:0}});
			}
		});
	}

	/**
	 * 批量删除
	 * @return {*}
	 */
	var deletePushPlatform = function (){
		clickEffect(this);
		let select = $('#thirdpushTable').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			if(select[i].status == CONF.FLAG.SET){
				tipDeleteunLockPush();
				return ;
			}
			ids.push(select[i].push_strategy_uuid);
		}
		if (!select.length) {
			return tipDeletePush();
		}
		var data = {};
		data.strategy_uuid = ids;
		Metronic.blockUI({
			target: '#monitorPlatformDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/system/message','DELETE',function (res){
			Metronic.unblockUI('#pushMangerDiv');
			if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MESSAGE_DELETE)) {
				$('#thirdpushTable').bootstrapTable('refresh', {query: {offset:0}});
			}
		});
	}

	/**
	 * 自定义添加模板
	 */
	var addTemplate = function() {
		var pushTemplate = document.getElementById('pushTemplate');
		var li = document.createElement('li');
		li.className = 'list-group-item';
		var index = customMessageArr.length + 1;
		// 添加内容名称
		var titleContent = `<div class="title mr15" style="width: 100px;" index="${index}"><input class="content form-control select2me title_input_${index}" value=""></div>`;
		li.insertAdjacentHTML('beforeend', titleContent);
		var inputEle = `<div class="" style="flex-grow: 1;"><input class="content form-control select2me content_input_${index}" value=""></div>`;
		li.insertAdjacentHTML('beforeend', inputEle);

		var iconContent = `<div class="icon display-inline-flex" style="margin-left: auto;">
        <button class="deleteButton bdnone bgtransparent">
            <a onclick="return false;"><i class="viconfont vicon-a-Deleteshanchu1 deleteIcon"></i></a>
        </button>
        <i class="viconfont vicon-a-Direction-adjustment-threefangxiangxiaozhun"></i>
    </div>`;
		li.insertAdjacentHTML('beforeend', iconContent);
		pushTemplate.appendChild(li);

		// 添加新项到 customMessageArr 数组中
		var titleInput = li.querySelector('.title_input_' + index);
		var contentInput = li.querySelector('.content_input_' + index);
		var newItem = {
			index: index,
			title: "",
			content: ""
		};
		customMessageArr.push(newItem);

		// 给每个按钮添加点击事件监听器
		var deleteButton = li.querySelector('.deleteButton');
		deleteButton.addEventListener('click', function() {
			// 获取被点击的删除按钮所在的列表项
			var listItem = this.closest('.list-group-item');
			// 执行删除操作
			listItem.remove();
			// 从 customMessageArr 数组中删除对应的项
			var indexToRemove = Array.from(pushTemplate.children).indexOf(listItem);
			customMessageArr.splice(indexToRemove, 1);
			// 更新 customMessageArr 数组中所有项的索引值
			customMessageArr.forEach(function(item, i) {
				item.index = i + 1;
			});
			// 打印更新后的数组
			console.log(customMessageArr);
		});

		// 为每个输入框添加输入事件监听器
		titleInput.addEventListener('input', function() {
			var title = this.value;
			var content = contentInput.value;
			var itemIndex = index - 1;
			customMessageArr[itemIndex].title = title;
			customMessageArr[itemIndex].content = content;
		});
		contentInput.addEventListener('input', function() {
			var title = titleInput.value;
			var content = this.value;
			var itemIndex = index - 1;
			customMessageArr[itemIndex].title = title;
			customMessageArr[itemIndex].content = content;
		});

		// 重新初始化 Sortable 库，以使新的列表项具有拖拽功能
		sortObj = new Sortable(pushTemplate, {
			animation: 150,
			swap: true,
			swapClass: 'highlight',
			onEnd: function(evt) {
				// 获取所有列表项
				var listItems = pushTemplate.querySelectorAll('.list-group-item');

				// 更新数组customMessageArr中对应位置的元素的索引值
				var updatedCustomMessageArr = [];
				listItems.forEach(function(listItem, newIndex) {
					var oldIndex = parseInt(listItem.querySelector('.title').getAttribute('index'));
					var item = customMessageArr[oldIndex - 1];
					item.index = newIndex + 1;
					updatedCustomMessageArr.push(item);
					// 更新DOM中的索引值
					listItem.querySelector('.title').setAttribute('index', newIndex + 1);
				});

				// 更新customMessageArr数组
				customMessageArr = updatedCustomMessageArr;

			},
		});
	}

	/**
	 * 全选任务
	 */
	var selectTask = function (){
		var allOptions = $('#taskList option');
		var allTaskFlag = true;
		allOptions.each(function () {
				if (!$(this).prop('selected')) {
					allTaskFlag = false;
				}
			}
		)
		return allTaskFlag;
	}

	/**
	 * 默认推送名
	 */
	var getMessageDefaultName = function (){
		var data = {};
		data.name = LANG.UI_PLATFORM_THIRD_MONITOR_ALARM_PUSH_STRATEGY;
		pAjaxRequest(data,'/api/v1/system/message/name','GET',function (res){
			$('input[name=strategyName]').val(res.data.name);
		});
	}

	/**
	 * 增加鼠标样式
	 */
	var addCursorStyle = function (){
		$('#autoResponsePush').closest('.bootstrap-switch-container') // 找到最近的 .bootstrap-switch 容器
			.find('span.bootstrap-switch-handle-on, span.bootstrap-switch-handle-off, label.bootstrap-switch-label')
			.addClass('disabled-cursor');
	}

	/**
	 * 移除鼠标样式
	 */
	var removeCursorStyle = function (){
		$('#autoResponsePush').closest('.bootstrap-switch-container') // 找到最近的 .bootstrap-switch 容器
			.find('span.bootstrap-switch-handle-on, span.bootstrap-switch-handle-off, label.bootstrap-switch-label')
			.removeClass('disabled-cursor');
	}

	/**
	 * 移除推送名规则
	 */
	var removePushNameRule = function (e,row){
		var strategyNameInput = $('input[name=strategyName]');
		strategyNameInput.each(function() {
			$(this).rules('remove'); // 移除该元素上的所有验证规则
		});
	}

	/**
	 * 添加推送名规则
	 */
	var addPushNameRule = function (){
		var strategyNameInput = $('input[name=strategyName]');
		strategyNameInput.rules('add', {
			required:true,
			nicknameAvailable:true
		});
	}

	/**
	 * 清除验证消息
	 * @param event
	 */
	var clearValidate = function (element){
		element.find('.form-group').removeClass('has-error').removeClass('has-success'); // 移除验证状态类名
		element.find('.help-block').remove(); // 移除验证消息
	}


	return {
		init: function () {
			//添加事件
			initThirdTable();
			addListeners();
			initOldInfo();
			initSpinner();
			getThirdMonitorPlatform();
			getAssociatedTask();
			handleAddValidation();
		}
	};

}();

jQuery(document).ready(function(){
	Settings_Message_Push.init();
});