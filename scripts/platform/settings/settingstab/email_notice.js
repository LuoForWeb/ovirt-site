//邮件通知
var Settings_Email_Notice = function () {
	var isInit = false;
	var emailConf = {
		email_flag: false,
		email_enabled: false,
		email_address: ""
	};

	//设置开关通知函数, 开关状态bool|开关ID|响应div class
	var setSwitch = function(flag, switchID, divClass){
		//添加change事件
		$('#' + switchID).bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.' + divClass).show();	//开
			}else{
				$('.' + divClass).hide();	//关
			}
		});
		//设置默认值
		if(flag){
			$('#' + switchID).bootstrapSwitch('state', true);
			$('.' + divClass).show();
		}else{
			$('#' + switchID).bootstrapSwitch('state', false);
			$('.' + divClass).hide();
		}
	}

	//设置通知等级默认值, 开关|等级[]|设置哪个DIV
	var setNoticeLevel = function(flag, level, levelDiv){
		if(!flag) return;
		for(var i=0; i<level.length; i++){
			switch(level[i]){
				case "1":
					$('.' + levelDiv).find('input[name=level1]').iCheck('check');
					break;
				case "2":
					$('.' + levelDiv).find('input[name=level2]').iCheck('check');
					break;
				case "3":
					$('.' + levelDiv).find('input[name=level3]').iCheck('check');
					break;
			}
		}
	}

	//设置报表选择开关
	var setReportCheck = function(list,flag){
		if(!flag) return;
		if(list.storage){
			$('#storageReport').iCheck('check');
		}else{
			$('#storageReport').iCheck('uncheck');
		}
		if(list.vm){
			$('#vmReport').iCheck('check');
		}else{
			$('#vmReport').iCheck('uncheck');
		}
	}

	var setTimeStrategyCheck = function(flag,strategy){
		if(!flag) return;
		for(var i=0; i<strategy.length; i++){
			switch(strategy[i].type){
				case 1:
					$('#dayCheck').bootstrapSwitch('state', true);
					break;
				case 2:
					$('#weekCheck').bootstrapSwitch('state', true);
					break;
				case 3:
					$('#monthCheck').bootstrapSwitch('state', true);
					break;
				case 4:
					$('#yearCheck').bootstrapSwitch('state', true);
					break;
			}
		}
	}

	//设置邮件通知默认配置
	var setEmailDefault = function(){
		if (emailConf.is_enterprise_en == 1) {
			// 隐藏邮件 email_mode
			$('.is_enterprise_en').hide();
		}
		// 如果是默认的，那么隐藏不需要显示的配置项
		$('select[name=email_model]').val(emailConf.email_model).change();
		$('select[name=email_mode]').val(emailConf.is_default).change();

		//设置三个开关
		setSwitch(emailConf.email_flag, 'emailcheck', 'emailcontent');
		setSwitch(emailConf.system_flag, 'systemchecke', 'systemcheckediv');
		setSwitch(emailConf.task_flag, 'taskchecke', 'taskcheckediv');
		setSwitch(emailConf.verify_flag, 'verifychecke', 'verifycheckediv');
		setSwitch(emailConf.database_flag, 'databsecheck', 'databsecheckediv');
		if(emailConf.rec_email){
			var receEmail = emailConf.rec_email.join("\r\n");
		}
		$('#emaillist').val(receEmail);

		//设置两个等级
		setNoticeLevel(emailConf.system_flag, emailConf.system_level, 'systemcheckediv');
		setNoticeLevel(emailConf.task_flag, emailConf.task_level, 'taskcheckediv');

		setNoticeLevel(emailConf.verify_flag, emailConf.verify_level, 'verifycheckediv');

		//设置报表选项

		//初始化测试配置
		$('input[name=emailhost]').val(emailConf.host);
		$('input[name=emailport]').val(emailConf.port);
		$('input[name=emailuser]').val(emailConf.user);
		$('input[name=emailpass]').val(emailConf.pass);
		$('select[name=encryption]').val(emailConf.encryption);
		$('#ssl_check').bootstrapSwitch('state', emailConf.is_ssl, true);
		$('#debug_check').bootstrapSwitch('state', emailConf.is_debug, true);

		$('input[name=client_id]').val(emailConf.client_id);
		$('input[name=client_secret]').val(emailConf.client_secret);
		$('input[name=tenant_id]').val(emailConf.tenant_id);

		$('#testrecemail').text(emailConf.email);
		//当未初始化的时候,需要先设置邮件通知开关和测试保存按钮不可用
		if(!emailConf.email_flag){
			$('#emailcheck').bootstrapSwitch('disabled', true);
			$('#testemailsubmit').prop('disabled', true);
		}
		if (!emailConf.email_enabled) {
			// 这里控制显示 配置邮箱
			document.getElementById("email-section").innerText = (LANG.UI_EMAIL_NOTICE_SETTING_SEND_USER);
		} else {
			// 这里控制显示 真实的邮箱
			document.getElementById("email-section").innerText = emailConf.email_address;
		}

		//设置报表
		var reportConf = emailConf.report_conf;
		var reportFlag = false;
		var reportList = [];
		var timeStrategy = [];
		if(reportConf){
			reportFlag = reportConf.report_flag;
			reportList = reportConf.report_check_list;
			timeStrategy = reportConf.time_strategy;
		}
		setSwitch(reportFlag, 'reportCheck', 'reportcheckediv');
		setReportCheck(reportList, reportFlag);
		//设置通知时间策略
		initReportNotice(reportFlag, timeStrategy); //初始化报表通知
		setTimeStrategyCheck(reportFlag, timeStrategy);
	}

	//得到默认信息
	var initDefaultConf = function(){
		setSwitch(false, 'emailcheck', 'emailcontent'); //默认设置为关闭，防止页面闪烁

		Metronic.blockUI({target:'#emailtab', animate: true});
		pAjaxRequest({}, '/api/v1/system/notice/email', 'GET', function (res) {
			Metronic.unblockUI('#emailtab');
			if (res.code == 0) {
				emailConf = res.data;
				setEmailDefault();
			}
		})
	}

	//得到设置等级
	var getLevelSetting = function(divClass){
		var level1 = $('.' + divClass).find('input[name=level1]').is(':checked');
		var level2 = $('.' + divClass).find('input[name=level2]').is(':checked');
		var level3 = $('.' + divClass).find('input[name=level3]').is(':checked');
		var level = [level1, level2, level3];
		return level;
	}

	//得到选中的报表
	var getReportCheck = function(){
		var storage = $('#storageReport').is(':checked');
		var vm = $('#vmReport').is(':checked');
		var list = {
			storage: storage,
			vm: vm
		}

		return list;
	}

	//得到邮件通知时间策略配置
	var getReportStrategy = function(){
		var strategyList = [];
		if($('#dayCheck').bootstrapSwitch('state') == true){
			var time = $('#dayTime').val();
			var strategy = {
				type: 1,
				notice_time: time,
			}
			strategyList.push(strategy);
		}

		if($('#weekCheck').bootstrapSwitch('state') == true){
			var time = $('#weekTime').val();
			var days = [];
			$('.weekcheck').find('input').each(function(i, d){
				if(d.checked){
					days[i] = 1;
				}else{
					days[i] = 0;
				}
			});
			var strategy = {
				type: 2,
				notice_time: time,
				days: days
			}
			strategyList.push(strategy);
		}

		if($('#monthCheck').bootstrapSwitch('state') == true){
			var time = $('#monthTime').val();
			var days = [];
			$('.monthcheck').find('input').each(function(i, d){
				if(d.checked){
					days[i] = 1;
				}else{
					days[i] = 0;
				}
			});
			var strategy = {
				type: 3,
				notice_time: time,
				days: days
			}
			strategyList.push(strategy);
		}

		if($('#yearCheck').bootstrapSwitch('state') == true){
			var time = $('#yearTime').val();
			var strategy = {
				type: 4,
				notice_time: time
			}
			strategyList.push(strategy);
		}

		return strategyList;

	}

	//检查邮件地址
	var checkEmailList = function(emailList){
		var list = emailList.split(/[\n,]/g);
		var reg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
		var emailArr = [];
		for(var i=0; i<list.length; i++){
			var email = $.trim(list[i])
			if("" != email){
				if(!reg.test(email)){
					UIToastr.showWarning(LANG.UI_SETTING_EMAIL_INPUT, LANG.UI_SETTING_EMAIL_INPUT_TIP);
					return false;
				}else{
					emailArr.push(email);
				}
			}
		}
		return emailArr;
	}

	//类型改变事件
	var emailTypeChange = function(){
		var type = $('select[name=email_mode]').val();
		if (type == 1) {
			$('.show_email_type').hide();
			$('.show_email_types').hide();
			$('.show_email_type2').show();
			$('.show_email_common').show();
			$('input[name=emailhost]').val(emailConf.default.host);
			$('input[name=emailport]').val(emailConf.default.port);
			$('input[name=emailuser]').val(emailConf.default.user);
			$('input[name=emailpass]').val(emailConf.default.pass);
			$('select[name=encryption]').val(emailConf.default.encryption);
			$('#ssl_check').bootstrapSwitch('state', emailConf.default.is_ssl, true);
			$('#debug_check').bootstrapSwitch('state', emailConf.default.is_debug, true);
		} else {
			if ($('select[name=email_model]').val() == 1) {
				$('.show_email_type2').hide();
				$('.show_email_types').hide();
				$('.show_email_type').show();
				$('.show_email_common').show();
				$('input[name=emailhost]').val(emailConf.host);
				$('input[name=emailport]').val(emailConf.port);
				$('input[name=emailuser]').val(emailConf.user);
				$('input[name=emailpass]').val(emailConf.pass);
				$('select[name=encryption]').val(emailConf.encryption);
				$('#ssl_check').bootstrapSwitch('state', emailConf.is_ssl, true);
				$('#debug_check').bootstrapSwitch('state', emailConf.is_debug, true);
			} else {
				$('.show_email_common').hide();
				$('.show_email_type2').hide();
				$('.show_email_type').hide();
				$('.show_email_types').show();
			}
		}
	}

	//类型改变事件
	var emailTypesChange = function(){
		var type = $('select[name=email_model]').val();
		if (type == 1) {
			$('.show_email_type2').hide();
			$('.show_email_types').hide();
			$('.show_email_type').show();
			$('.show_email_common').show();
		} else {
			$('.show_email_common').hide();
			$('.show_email_type2').hide();
			$('.show_email_type').hide();
			$('.show_email_types').show();
		}
	}

	var addListeners = function() {
		$('#jump_outlook').on('click', function () {
			var emailuser = $('input[name=emailuser]').val();
			var clientId = $('input[name=client_id]').val();
			var clientSecret = $('input[name=client_secret]').val();
			var tenantId = $('input[name=tenant_id]').val();

			if (emailuser == undefined || emailuser == '') {
				UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_NOTICE_SETTING_SEND_USER);
				return false;
			}
			if (clientId == undefined || clientId == '') {
				UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_NOTICE_SETTING_CLIENT_ID);
				return false;
			}
			if (clientSecret == undefined || clientSecret == '') {
				UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_NOTICE_SETTING_CLIENT_SECRET);
				return false;
			}
			var url = './register_outlook.php?clientId=' + clientId + '&clientSecret=' + clientSecret + '&tenantId=' + tenantId;
			window.open(url, '_blank');
		});
		$('select[name=email_model]').on('change', emailTypesChange);
		$('select[name=email_mode]').on('change', emailTypeChange);

		//切换消息推送配置开关
		$('#pushCheck').on('switchChange.bootstrapSwitch', function () {
			if (this.checked) {
				$('.pushChild').show();
			} else {
				$('.pushChild').hide();
			}
		});

		$("#emailcancel").click(function () {
			LOCATION('./content/platform/settings/setting_manager.php?tab=2', 'setting_manager');
		});

		$('#cencel').on('click', () => {
			// 关闭当前任务页面提示tips时，动态调整表格高度
			$('.modal-body').css('height', 'calc(100% + 180px)')
		});
		$('#cencel2').on('click', () => {
			// 关闭当前任务页面提示tips时，动态调整表格高度
			$('.modal-body').css('height', 'calc(100% + 10px)')
		});


		$("#emailconfig").click(function () {
			$("#emailconfig_div").show();
		});
		$("#emailDelete").click(function () {
			$("#emailconfig_div").hide();
		});

		$("#emailConfirm").click(function () {
			const EmailAddress = $("#EmailAddress").val().trim();

			if (!EmailAddress) {
				UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_EMPTY_FAILED);
				return; // 阻止后续执行
			}

			const emailReg = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailReg.test(EmailAddress)) {
				UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_INPUT_REGEX_TIPS_EMAIL);
				return; // 阻止后续执行
			}

			fetchUserInfo(function (userInfo) {
				// 更新 userInfo 对象中的邮箱
				pAjaxRequest({email: EmailAddress}, '/api/v1/users/self/info', 'PUT', function (res) {
					if (res.code === 0 || res.code === 1 || res.code === 200) {
						// 更新成功
						UIToastr.showSuccess(LANG.UI_PUBLIC_SUCCESS, LANG.UI_EMAIL_UPDATE_SUCCESS);

						fetchUserInfo(function (freshUserInfo) {
							$('#testrecemail').html(freshUserInfo.email || '');
						});
					} else {
						// 更新失败
						const errorMsg = res.message || LANG.UI_EMAIL_UPDATE_FAILED;
						UIToastr.showError(LANG.UI_PUBLIC_ERROR, errorMsg);

					}
				});
			});

			$('#testrecemail').html(EmailAddress);

			// 切换相关元素的显示/隐藏
			$("#emailconfig2").show();
			$("#testrecemail").show();
			$("#xiugai2").show();
			$("#emailconfig").hide();
			$("#emailconfig_div").hide();


			$("#sendtestmail").prop("disabled", false);
			enableSendTestMailButton(true);

		});
		$("#xiugai2").click(function () {
			$("#emailconfig_div").show();
		});


		$('#emailsubmit').on('click', function () {

			var data = {};
			data.flag = $('#emailcheck').bootstrapSwitch('state');
			data.system_flag = $('#systemchecke').bootstrapSwitch('state');
			data.system_level = getLevelSetting('systemcheckediv');
			data.task_flag = $('#taskchecke').bootstrapSwitch('state');
			data.task_level = getLevelSetting('taskcheckediv');
			data.verify_flag = $('#verifychecke').bootstrapSwitch('state');
			data.database_flag = $('#databsecheck').bootstrapSwitch('state');
			data.verify_level = getLevelSetting('verifycheckediv');
			data.rec_email = checkEmailList($('#emaillist').val());
			if (false === data.rec_email) {
				return false;
			}

			data.rec_email = JSON.stringify(data.rec_email);

			//报表通知参数提交
			var reportFlag = $('#reportCheck').bootstrapSwitch('state');
			if (!data.flag) {
				reportFlag = false;
			}
			var reportCheckList = getReportCheck();
			var reportTimeStrategy = getReportStrategy();
			var conf = {
				report_flag: reportFlag,
				report_check_list: reportCheckList,
				time_strategy: reportTimeStrategy
			}

			data.report_conf = JSON.stringify(conf);

			Metronic.blockUI({target: '#emailtab', animate: true});
			pAjaxRequest(data, '/api/v1/system/notice/email', 'POST', function (res) {
				Metronic.unblockUI('#emailtab');
				if (operateResponseList(res, LANG.UI_EMAIL_NOTICE_SETTING)) {
				}
			})

		});
		$('#testemail').on('click', testEmail);

		//报表通知监听事件
		$('#dayCheck').bootstrapSwitch('onSwitchChange', function (e, data) {
			if (data) {
				$('#dayDiv').show();	//开

				$('#dayIcon').removeClass('opacity-0');
				$('#dayIcon').addClass('opacity-100');
			} else {
				$('#dayDiv').hide();	//关

				$('#dayIcon').removeClass('opacity-100');
				$('#dayIcon').addClass('opacity-0');
			}
		});
		$('#weekCheck').bootstrapSwitch('onSwitchChange', function (e, data) {
			if (data) {
				$('#weekDiv').show();	//开

				$('#weekIcon').removeClass('opacity-0');
				$('#weekIcon').addClass('opacity-100');
			} else {
				$('#weekDiv').hide();	//关

				$('#weekIcon').removeClass('opacity-100');
				$('#weekIcon').addClass('opacity-0');
			}
		});
		$('#monthCheck').bootstrapSwitch('onSwitchChange', function (e, data) {
			if (data) {
				$('#monthDiv').show();	//开

				$('#monthIcon').removeClass('opacity-0');
				$('#monthIcon').addClass('opacity-100');
			} else {
				$('#monthDiv').hide();	//关

				$('#monthIcon').removeClass('opacity-100');
				$('#monthIcon').addClass('opacity-0');
			}
		});
		$('#yearCheck').bootstrapSwitch('onSwitchChange', function (e, data) {
			if (data) {
				$('#yearDiv').show();	//开

				$('#yearIcon').removeClass('opacity-0');
				$('#yearIcon').addClass('opacity-100');
			} else {
				$('#yearDiv').hide();	//关

				$('#monthIcon').removeClass('opacity-100');
				$('#monthIcon').addClass('opacity-0');
			}
		});

		// 页面加载时获取用户信息
		fetchUserInfo(function (userInfo) {
			// 更新页面显示
			if (userInfo.email) {
				$('#testrecemail').html(userInfo.email);
				$("#emailconfig2").show();
				$("#testrecemail").show();
				$("#xiugai2").show();
				$("#emailconfig").hide();
				$("#emailconfig_div").hide();

				// 如果有邮箱，则启用发送测试邮件按钮，并设置样式为绿色
				enableSendTestMailButton(true);
			} else {
				// 如果没有邮箱，则禁用发送测试邮件按钮，并设置样式为灰色
				enableSendTestMailButton(false);
			}
		});
	}

	// 获取用户信息
	function fetchUserInfo(callback) {
		pAjaxRequest({}, '/api/v1/users/self/info', 'GET', function (res) {
			if (res.code === 0 && callback) {
				callback(res.data);
			} else {
				UIToastr.showError(LANG.UI_PUBLIC_ERROR, LANG.UI_USER_INFO_FETCH_FAILED); // 获取用户信息失败提示
			}
		});
	}

	// 启用或禁用发送测试邮件按钮的函数
	function enableSendTestMailButton(enable) {
		var $btn = $('#sendtestmail');
		if (enable) {
			// 设置按钮为可点击，背景色为绿色
			$btn.prop('disabled', false)
				.removeClass('btn-disabled disabled') // 假设这是灰色样式的类名
				.addClass('btn-enabled'); // 假设这是绿色样式的类名
		} else {
			// 设置按钮为不可点击，背景色为灰色
			$btn.prop('disabled', true)
				.removeClass('btn-enabled')
				.addClass('btn-disabled');
		}
	}

	//配置邮件服务器
	var testEmail = function(){
		//$('#testemaildiv').modal();
		$('#testemaildiv').drawer('show');
		handleEmailValidation();
	}

	//配置邮件服务器验证
	var handleEmailValidation = function() {
		if (isInit) {
			return;
		}
		isInit = true;
		var setipform = $('#testemailform');

		setipform.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				emailhost: {
					required: true,
					ipv4Ordomain: true,
				},
				emailport: {
					required: true,
					number: true,
				},
				emailuser: {
					required: true,
					email: true,
				},
//                emailpass: {
//                    required: true,
//                }
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

		$.validator.addMethod("ipv4Ordomain", function(value, element) {
			return this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
		}, LANG.UI_TOOLS_IP_OR_DOMAIN);

		var getSmtpConf = function(){
			var data = {};
			data.email_model = $('select[name=email_model]').val();
			data.host = $('input[name=emailhost]').val();
			data.port = $('input[name=emailport]').val();
			data.user = $('input[name=emailuser]').val();
			data.client_id = $('input[name=client_id]').val();
			data.client_secret = $('input[name=client_secret]').val();
			data.tenant_id = $('input[name=tenant_id]').val();
			data.pass = btoa($('input[name=emailpass]').val());
			data.is_ssl = $('#ssl_check').get(0).checked;
			data.is_debug = $('#debug_check').get(0).checked;
			data.encryption = $('select[name=encryption]').val();
			data.rec_email = $('#testrecemail').text();
			if ( $('select[name=email_mode]').val() == 1) {
				// 系统默认
				data.email_model = 1;
			}
			return data;
		}

		updateButtonState();
		function updateButtonState() {
			var button = $('#sendtestmail');
			if (emailConf.email_enabled) {
				// 已配置手机号，启用按钮（绿色）
				button.removeClass('disabled').addClass('enabled').prop('disabled', false);
			} else {
				// 未配置手机号，禁用按钮（灰色）
				button.removeClass('enabled').addClass('disabled').prop('disabled', true);
			}
		}
		//测试邮件发送
		$('#sendtestmail').unbind().on('click', function(){
			var email_model = $('select[name=email_model]').val();
			if((email_model == 1 && setipform.validate().form()) || $('select[name=email_mode]').val() == 1) {

			} else {
				// outlook
				var emailuser = $('input[name=emailuser]').val();
				var clientId = $('input[name=client_id]').val();
				var clientSecret = $('input[name=client_secret]').val();
				if (emailuser == undefined || emailuser == '') {
					UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_NOTICE_SETTING_SEND_USER);
					return false;
				}
				if (clientId == undefined || clientId == '') {
					UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_NOTICE_SETTING_CLIENT_ID);
					return false;
				}
				if (clientSecret == undefined || clientSecret == '') {
					UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_NOTICE_SETTING_CLIENT_SECRET);
					return false;
				}
			}

			var jsonData = getSmtpConf();
			Metronic.blockUI({target:'#testemaildiv', animate: true});
			pAjaxRequest(jsonData, '/api/v1/system/notice/test_email', 'POST', function (res) {
				Metronic.unblockUI('#testemaildiv');
				if (operateResponseList(res, LANG.UI_MICROSOFT365_SEND_EMAIL)){
					$('#emailcheck').bootstrapSwitch('disabled', false);
					$('#testemailsubmit').prop('disabled', false);
				}
			})
		});

		$("#testemailsubmit").unbind().on('click', function(){
			var email_model = $('select[name=email_model]').val();
			if((email_model == 1 && setipform.validate().form()) || $('select[name=email_mode]').val() == 1) {

			} else {
				// outlook
				var emailuser = $('input[name=emailuser]').val();
				var clientId = $('input[name=client_id]').val();
				var clientSecret = $('input[name=client_secret]').val();
				if (emailuser == undefined || emailuser == '') {
					UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_NOTICE_SETTING_SEND_USER);
					return false;
				}
				if (clientId == undefined || clientId == '') {
					UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_NOTICE_SETTING_CLIENT_ID);
					return false;
				}
				if (clientSecret == undefined || clientSecret == '') {
					UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_EMAIL_NOTICE_SETTING_CLIENT_SECRET);
					return false;
				}
			}

			var jsonData = getSmtpConf();
			Metronic.blockUI({target:'#testemaildiv', animate: true});
			pAjaxRequest(jsonData, '/api/v1/system/notice/smtp', 'POST', function (res) {
				Metronic.unblockUI('#testemaildiv');
				if (operateResponseList(res, LANG.UI_EMAIL_NOTICE_SETTING)){
					//$('#testemaildiv').modal('hide');
					$('#testemaildiv').drawer('hide');
				}
			})
		});
	};

	//初始化报表通知时间配置
	var initReportNotice = function(flag, strategy){
		initDays(flag, strategy);
		initWeeks(flag, strategy);
		initMonth(flag, strategy);
		initYear(flag, strategy);

		$('.starttime').timepicker({
			autoclose: true,
			minuteStep: 5,
			showSeconds: true,
			showMeridian: false,
//            defaultTime:'00:00:00'
		});

		$('.timepicker').parent('.input-group').on('click', '.input-group-btn', function(e){
			e.preventDefault();
			$(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
		});

	}

	var initDays = function(flag, strategy){
		var notice_time = "08:00:00";
		if(flag){
			for(var i=0;i<strategy.length;i++){
				if(strategy[i].type == 1){
					notice_time = strategy[i].notice_time;
				}
			}
		}
		var html = '<div class="form-group" style="margin-top: 20px;">' +
			'<div class="col-md-5 p-0">' +
			'<label style="display:block;width:80px;float:left;">' + LANG.UI_PUBLIC_NOTICE_TIME + '</label>' +
			'<div class="input-group form-group-content" style="width: 150px;float:left;">' +
			'<input type="text" value="' + notice_time + '" class="form-control timepicker timepicker-24 starttime" id="dayTime">' +
			'<span class="input-group-btn">' +
			'<button class="btn default btn-time" type="button"><i class="fa fa-clock-o"></i></button>' +
			'</span>' +
			'</div>' +
			'</div>' +
			'</div>';
		$('#dayDiv').append(html);
	}

	var initWeeks = function(flag, strategy){
		var notice_time = "08:00:00";
		var days = [1, 0, 0, 0, 0, 0, 0];
		if(flag){
			for(var i=0;i<strategy.length;i++){
				if(strategy[i].type == 2){
					notice_time = strategy[i].notice_time;
					days = strategy[i].days;
				}
			}
		}
		var html = '<div class="input-group weekcheck">' +
			'<row>' +
			'<div>';

		for(var j=0; j<7; j++){
			var checked = '';
			var weedDes = [LANG.UI_STRATEGY_MONDAY , LANG.UI_STRATEGY_TUESDAY , LANG.UI_STRATEGY_WEDNESDAY , LANG.UI_STRATEGY_THURSDAY , LANG.UI_STRATEGY_FRIDAY , LANG.UI_STRATEGY_SATURDAY , LANG.UI_STRATEGY_SUNDAY];
			//初始化每周复选框
			if(days[j]){
				checked = 'checked';
			}
			html += '<label style="padding-right: 10px;"><input type="radio" data-checkbox="icheckbox_square-blue" ' + checked + ' name="week" class="icheck"> ' + weedDes[j] + ' </label>';
		}
		html += '</div></row></div>' + '<div class="form-group" style="margin-top: 20px;">' +
			'<div class="col-md-5 p-0">' +
			'<label style="display:block;width:80px;float:left;">' + LANG.UI_PUBLIC_NOTICE_TIME + '</label>' +
			'<div class="input-group form-group-content" style="width: 150px;float:left">' +
			'<input type="text" value="' + notice_time + '" class="form-control timepicker timepicker-24 starttime" id="weekTime">' +
			'<span class="input-group-btn">' +
			'<button class="btn default btn-time" type="button"><i class="fa fa-clock-o"></i></button>' +
			'</span>' +
			'</div>' +
			'</div>' +
			'</div>';

		$('#weekDiv').append(html);

	}


	var initMonth = function(flag, strategy){
		var notice_time = "08:00:00";
		var days = [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
		if(flag){
			for(var i=0;i<strategy.length;i++){
				if(strategy[i].type == 3){
					notice_time = strategy[i].notice_time;
					days = strategy[i].days;
				}
			}
		}
		var html = '<div class="input-group monthcheck">';
		for(var j=0; j<31; j++){
			var checked = '';
			if(days[j]){
				checked = 'checked';
			}
			var thisDay = j + 1;
			html += '<label class="label60"><input type="radio" data-checkbox="icheckbox_square-blue" ' + checked + ' name="month" class="icheck"> ' + thisDay + ' </label>';
		}
		html += '</div>' + '<div class="form-group" style="margin-top: 20px;">' +
			'<div class="col-md-5 p-0">' +
			'<label style="display:block;width:80px;float:left;">' + LANG.UI_PUBLIC_NOTICE_TIME + '</label>' +
			'<div class="input-group form-group-content" style="width: 150px;float:left;">' +
			'<input type="text" value="' + notice_time + '" class="form-control timepicker timepicker-24 starttime" id="monthTime">' +
			'<span class="input-group-btn">' +
			'<button class="btn default btn-time" type="button"><i class="fa fa-clock-o"></i></button>' +
			'</span>' +
			'</div>' +
			'</div>' +
			'</div>';
		$('#monthDiv').append(html);
	}

	var initYear = function(flag, strategy){
		if(flag){
			for(var i=0;i<strategy.length;i++){
				if(strategy[i].type == 4){
					$('#yearTime').val(strategy[i].notice_time);
				}
			}
		}
		$(".form_datetime").datetimepicker({
			language:  'zh-CN',
			autoclose: true,
			isRTL: Metronic.isRTL(),
			format: "yyyy-MM-dd hh:ii:ss",
			pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left")
		});
	}


	return {
		init: function () {
			//初始化默认选项
			initDefaultConf();
			//添加事件
			addListeners();
		}
	};

}();

jQuery(document).ready(function(){
	Settings_Email_Notice.init();
});