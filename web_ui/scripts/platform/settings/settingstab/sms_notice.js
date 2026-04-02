//短信通知
var Settings_Sms_Notice = function () {
	var smsConf = {
		telephone_enabled: false,
		sms_flag: false,
		device_config: undefined,
		send_type: 0
	};
	var sms_mode_type = 1;

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

	//设置短信通知默认配置
	var setSmsDefault = function(){
		//设置三个开关
		setSwitch(smsConf.sms_flag, 'smscheck', 'smscontent');
		setSwitch(smsConf.system_flag, 'systemchecks', 'systemchecksdiv');
		setSwitch(smsConf.task_flag, 'taskchecks', 'taskchecksdiv');
		//设置两个等级
		setNoticeLevel(smsConf.system_flag, smsConf.system_level, 'systemchecksdiv');
		setNoticeLevel(smsConf.task_flag, smsConf.task_level, 'taskchecksdiv');
		//初始化测试配置
		$('.testrecphone').text(smsConf.telephone);
		$('#smsquantity').text(smsConf.quantity +LANG.UI_KUBE_ITEMS);
		//短信猫初始化设置
		var deviceConfig = smsConf.device_config;
		$('input[name=modemipaddr]').val(deviceConfig.ip);
		$('input[name=modemdatabase]').val(deviceConfig.database);
		$('input[name=modemport]').val(deviceConfig.port);
		$('input[name=modemusername]').val(deviceConfig.user);
		$('input[name=modempassword]').val(atob(deviceConfig.pass));
		var receTelephone = '';
		if(deviceConfig.receive_telephone != undefined){
			receTelephone = deviceConfig.receive_telephone.join("\r\n");
		}else{
			receTelephone = smsConf.telephone;
		}
		$('#telephonelist').val(receTelephone);
		//设置发送方式描述
		if(1 === smsConf.send_type){
			$('#smssendtypedes').html($('#sendtypeinternet').html());
		}else if(2 === smsConf.send_type){
			$('#smssendtypedes').html($('#sendtypemodem').html());
		}

		//当未初始化的时候,需要先设置短信通知开关不可用
		if(!smsConf.sms_flag){
			$('#smscheck').bootstrapSwitch('disabled', true);
		}
		// 如果未配置手机号
		var phoneDisplayElement = $('.testrecphone');
		if (!smsConf.telephone_enabled) {
			// 这里控制显示 配置手机号
			phoneDisplayElement.html('<span style="color: #999; font-style: italic;">' + LANG.UI_CONFIG_PHONE_NUMBER + '</span>'); // 使用一个国际化语言变量或直接写文字
		} else {
			// 这里控制显示 真实的手机号
			if (smsConf.telephone && smsConf.telephone.trim() !== '') {
				var maskedPhone = smsConf.telephone.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2');
				phoneDisplayElement.text(maskedPhone);
			} else {
				phoneDisplayElement.html('<span style="color: #999; font-style: italic;">' + LANG.UI_CONFIG_PHONE_NUMBER + '</span>');
			}
		}
	}

	//填充自定义短互联网信通知
	var setSmsNotice = function (){
		var deviceConfig = smsConf.device_config;
		var sms_mode = deviceConfig.sms_mode ?? CONF.FLAG.SET;
		var sms_appid = deviceConfig.applicationid ?? '';
		var appsecret = deviceConfig.appsecret ?? '';
		var company_id_ = deviceConfig.company_id_ ?? '';
		var url = deviceConfig.url ?? '';
		$('select[name=sms_mode]').val(sms_mode);
		if(sms_mode == CONF.FLAG.UNSET){
			// 默认显示自定义通知模式
			$('.show_sms_type1').hide();
			$('.show_sms_type2').show();
		}else{
			// 系统默认
			$('.show_sms_type1').show();
			$('.show_sms_type2').hide();
		}
		$('input[name=sms_appid]').val(sms_appid);
		$('input[name=sms_appsecret]').val(appsecret);
		$('input[name=company]').val(company_id_);
		$('input[name=url]').val(url);
	}

	$(document).ready(function () {
		// 初始化 UI 状态
		setSwitch(false, 'smscheck', 'smscontent'); // 默认关闭

		// 页面加载时获取用户信息并显示
		fetchUserInfo(function (userInfo) {
			if (userInfo.telephone && userInfo.telephone.trim() !== '') {
				// 更新所有页面上的电话显示
				$('#testrecphone').html(userInfo.telephone);
				$('#userPhone').text(userInfo.telephone);
				$('#displayTelephone').html(userInfo.telephone);

				$("#phoneConfig2").show();
				$("#testrecphone").show();
				$("#displayTelephone").show();
				$("#editPhoneBtn").show();
				$("#xiugai").show();
				$("#smsconfig2").show();

				$("#phoneConfig").hide();
				$("#smsconfig").hide();

				$("#sendtestsms").prop("disabled", false);

				smsConf.telephone = userInfo.telephone;
				smsConf.telephone_enabled = true;
				smsConf.sms_enabled = userInfo.sms_enabled !== false;
			} else {
				// 用户未配置电话：显示配置入口
				$("#phoneConfig").show();
				$("#smsconfig").show();

				$("#phoneConfig2").hide();
				$("#displayTelephone").hide();
				$("#editPhoneBtn").hide();
				$("#smsconfig2").hide();
				$("#xiugai").hide();

				$("#sendtestsms").prop("disabled", true);
			}
		});

		// 其他初始化...
		addListeners();
	});



	function updatePhoneDisplay() {
		if (!smsConf.sms_enabled || !smsConf.telephone) {
			document.getElementById("telephone-section").innerText = (LANG.UI_PHONE_NOTICE_SETTING);
		} else {
			const phone = smsConf.telephone;
			let displayPhone = phone;
			if (phone && phone.length >= 11) {
				displayPhone = phone.substring(0, 3) + '****' + phone.substring(7);
			}
			document.getElementById("telephone-section").innerText = displayPhone;
		}

		// 同步到其他位置
		$('input[name="number"]').val(smsConf.telephone);
	}
//配置，修改监听事件
	$("#configPhoneBtn").on('click', function() {
		if (smsConf.sms_enabled && smsConf.telephone) {
			$('#telephoneInput').val(smsConf.telephone);
		} else {
			$('#telephoneInput').val('');
		}
		$('#phoneConfigDrawer').show();
	});

	$('#confirmConfig').on('click', function() {
		var telephone = $("#telephoneInput").val().trim();
		pAjaxRequest({'telephone': telephone}, '/api/v1/users/self/info', 'PUT', function (res) {
			if (res.success) {
				smsConf.telephone = telephone;
				smsConf.sms_enabled = true;
				updatePhoneDisplay();
				$('#phoneConfigDrawer').hide(); // 关闭抽屉
			} else {
				//console.error('保存电话号码失败:', res);
				UIToastr.showError(LANG.UI_PUBLIC_ERROR, res.message);
				//alert('保存失败: ' + (res.message || '未知错误'));
			}
		});
	});
	$('#cancelConfig').on('click', function() {
		$('#phoneConfigDrawer').hide(); // 关闭抽屉
	});

	//得到默认信息
	var initDefaultConf = function(){
		setSwitch(false, 'smscheck', 'smscontent'); //默认设置为关闭，防止页面闪烁
		Metronic.blockUI({target:'#smstab', animate: true});
		pAjaxRequest({}, '/api/v1/system/notice/sms', 'GET', function (res) {
			Metronic.unblockUI('#smstab');
			if (res.code == 0) {
				smsConf = res.data;
				setSmsDefault();
				setSmsNotice();
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


	function fetchUserInfo(successCallback, errorCallback) {
		pAjaxRequest(
			{},
			'/api/v1/users/self/info',
			'GET',
			function (res) {
				if (res && [0, 200].includes(res.code)) {
					const userInfo = res.data || {};
					if (successCallback) successCallback(userInfo);
				} else {
					//console.error("获取用户信息失败", res);
					if (errorCallback) errorCallback();
					else UIToastr.showError(LANG.UI_PUBLIC_ERROR, LANG.UI_USER_INFO_FETCH_FAILED);
				}
			}
		);
	}

	var addListeners = function(){

		$("#smscancel").click(function(){
			CTLSIDEBAR('setting_manager');
			LOCATION('./content/platform/settings/setting_manager.php?tab=2', 'setting_manager');
		});

		$("#smsconfig").click(function(){
			$('#smsconfig_div').show();
		});
		$("#smsDelete").click(function (){
			$("#smsconfig_div").hide();
		});


		// 是否为空，是否是正确的电话号码
		// 是的话，就请求接口，完成保存，保存成功后，把 telephone 给赋值到 testrecphone 元素上去
		// 此时需要 sendtestsms 元素设置为可以点击的效果
		$("#smsConfirm").off('click').on('click', function () {
			const telephone = $("#telephone").val().trim();
			const phoneReg = /^1\d{10}$/;

			if (!phoneReg.test(telephone)) {
				UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PHONE_ENTER_NUMBER);
				return;
			}

			//先获取完整用户信息
			fetchUserInfo(function (userInfo) {
				//修改电话
				//提交完整对象
				pAjaxRequest({telephone: telephone}, '/api/v1/users/self/info', 'PUT', function (res) {
					const successCodes = [0, 200];
					if (res && successCodes.includes(res.code)) {
						UIToastr.showSuccess(LANG.UI_PUBLIC_SUCCESS, LANG.UI_PHONE_UPDATE_SUCCESS);

						// 更新本地状态
						smsConf.telephone = telephone;
						smsConf.telephone_enabled = true;

						// 更新显示
						$('#testrecphone').html(telephone);
						$("#smsconfig2").show();
						$("#testrecphone").show();
						$("#xiugai").show();
						$("#smsconfig").hide();
						$("#smsconfig_div").hide();
						$("#sendtestsms").prop("disabled", false);

					} else {
						const errorMsg = res?.message || res?.msg || LANG.UI_PHONE_UPDATE_FAILED;
						UIToastr.showError(LANG.UI_PUBLIC_ERROR, errorMsg);
					}
				});
			});
		});
		$("#xiugai").click(function (){
			$("#smsconfig_div").show();
		});


		$('#smssubmit').off().on('click', function(){
			var data = {};
			data.flag = $('#smscheck').bootstrapSwitch('state');
			data.system_flag = $('#systemchecks').bootstrapSwitch('state');
			data.system_level = getLevelSetting('systemchecksdiv');
			data.task_flag = $('#taskchecks').bootstrapSwitch('state');
			data.task_level = getLevelSetting('taskchecksdiv');
			data.rec_telphone_list = checkTelphoneList($('#telephonelist').val());
			if (!data.rec_telphone_list) {
				return false;
			}
			Metronic.blockUI({target:'#smstab', animate: true});
			pAjaxRequest(data, '/api/v1/system/notice/sms', 'POST', function (res) {
				Metronic.unblockUI('#smstab');
				if (operateResponseList(res, LANG.UI_SMS_NOTICE_SETTING)){
				}
			})
		});
		$('#testsms').on('click', testSms);
		handleModemValidation();
		handleInternetValidation();
		$('select[name=sms_mode]').on('change', smsTypeChange);
		$('input[name=sms_appid], input[name=sms_appsecret], input[name=company], input[name=url]').blur(function(){
			updateSendsmsButtonState(); // 调用更新按钮状态
		});
	}

	//检查短信地址
	var checkTelphoneList = function(telphoneList){
		var list = telphoneList.split(/[\n,]/g);
		var reg = /^1[3-9]\d{9}$/;
		var telphoneArr = [];
		for(var i=0; i<list.length; i++){
			var telphone = $.trim(list[i])
			if("" != telphone){
				if(!reg.test(telphone)){
					UIToastr.showWarning(LANG.UI_SETTING_EMAIL_INPUT, LANG.UI_SETTING_SMS_CHECK_ERROR_TIPS);
					return false;
				}else{
					telphoneArr.push(telphone);
				}
			}
		}
		if(telphoneArr.length == 0 ){
			UIToastr.showWarning(LANG.UI_SETTING_EMAIL_INPUT, LANG.UI_SETTING_SMS_CHECK_NULL_TIPS);
			return false;
		}
		return telphoneArr;
	}

	var smsTypeChange = function (){
		sms_mode_type = $('select[name=sms_mode]').val();
		if(sms_mode_type == 1){
			$('.show_sms_type1').show();
			$('.show_sms_type2').hide();
			updateSendsmsButtonState();
		}else if(sms_mode_type == 2){
			$('.show_sms_type2').show();
			$('.show_sms_type1').hide();
			updateSendsmsButtonState();
		}
	}

	//配置短信猫验证
	var handleModemValidation = function() {
		var setipform = $('#setmodem');
		setipform.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				modemipaddr: {
					required: true,
					ipv4Ordomain: true,
				},
				modemdatabase:{
					required: true,
				},
				modemport: {
					required: true,
					number: true,
				},
				modemusername: {
					required: true,
				},
				modempassword: {
					required: true,
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

		$.validator.addMethod("ipv4Ordomain", function(value, element) {
			return this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			return domain || ipv4;
		}, LANG.UI_TOOLS_IP_OR_DOMAIN);

		var getModemConf = function(){
			var data = {};
			data.ip = $('input[name=modemipaddr]').val();
			data.database = $('input[name=modemdatabase]').val();
			data.port = $('input[name=modemport]').val();
			data.user = $('input[name=modemusername]').val();
			data.pass = btoa($('input[name=modempassword]').val());
			data.rec_phone = $('#testrecphone').text();
			return data;
		}

		//测试短信猫发送
		$("#sendmodemsms").unbind().on('click', function(){
			if(!smsConf.telephone){
				UIToastr.showWarning(LANG.UI_SETTING_NO_PHONE_NUM, LANG.UI_SETTING_NO_PHONE_NUM_TIP);
				return;
			}
			if (setipform.validate().form()) {
				var jsonData = getModemConf();
				Metronic.blockUI({target:'#testsmsdiv', animate: true});
				pAjaxRequest(jsonData, '/api/v1/system/notice/cat_sms', 'POST', function (res) {
					Metronic.unblockUI('#testsmsdiv');
					if (operateResponseList(res, LANG.UI_SMS_NOTICE_SETTING)){
						$('#testsmsdiv').modal('hide');
						$('#smscheck').bootstrapSwitch('disabled', false);
						$('#smssendtypedes').html($('#sendtypemodem').html());
					}
				})
			}
		});

	};

	// 互联网短信验证
	var handleInternetValidation = function (){
		var internetform = $('#internet');
		internetform.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				sms_appid: {
					required: true,
				},
				sms_appsecret: {
					required: true,
				},
				company: {
					required: true,
				},
				url: {
					required: true,
					ipv4v6: true
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

			},
		});
		//IP验证格式
		$.validator.addMethod("ipv4v6", function(value, element) {
			var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			//http|https
			var domainHTTP = this.optional( element ) || /^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			var returns = domain || ipv4 || domainHTTP || ipv4HTTP;
			return returns;
		}, LANG.UI_SETTING_INPUT_EXISTS_DOMAIN);
	}

	function updateSendsmsButtonState() {
		var button = $('#sendtestsms');
		if (($('input[name=sms_appid]').val() &&
			$('input[name=sms_appsecret]').val() &&
			$('input[name=company]').val() &&
			$('input[name=url]').val()) || sms_mode_type == CONF.FLAG.SET) {
			// 已配置，启用按钮并设为绿色
			button.removeClass('btn-default disabled').addClass('btn-success enabled').prop('disabled', false)

		} else {
			// 未配置，禁用按钮并设为灰色
			button.removeClass('btn-success enabled').addClass('btn-default disabled').prop('disabled', true)
		}
	}

	//测试短信
	var testSms = function () {
		$('#current_pending_task_drawer').drawer('show');

		// 更新按钮状态的函数
		function updateButtonState() {
			var button = $('#sendtestsms');
			if (smsConf.telephone) {
				// 已配置手机号，启用按钮（绿色）
				button.removeClass('disabled').addClass('enabled').prop('disabled', false);
			} else {
				// 未配置手机号，禁用按钮（灰色）
				button.removeClass('enabled').addClass('disabled').prop('disabled', true);
			}
		}

		// 初始化按钮状态
		updateButtonState();
		// 绑定点击事件
		$('#sendtestsms').off('click').on('click', function () {
			if (!smsConf.telephone_enabled){
				UIToastr.showWarning(LANG.UI_SETTING_NO_PHONE_NUM, LANG.UI_SETTING_SET_PHONE_NUM);
				return;
			};
			var sms_mode = $('select[name=sms_mode] option:selected').val();
			var appId = $('input[name=sms_appid]').val();
			var appsecret = $('input[name=sms_appsecret]').val();
			var company = $('input[name=company]').val();
			var url = $('input[name=url]').val();
			var data = {
				'telephone': smsConf.telephone,
				'sms_mode':sms_mode,
				'appId':appId,
				'appsecret':appsecret,
				'company':company,
				'url':url,
			};
			Metronic.blockUI({ target: '#testsmsdiv', animate: true });

			pAjaxRequest(data, '/api/v1/system/notice/test_sms', 'POST', function (res) {
				Metronic.unblockUI('#testsmsdiv');

				//请求结束后立即刷新按钮状态
				updateButtonState();

				if (operateResponseList(res, LANG.UI_SMS_NOTICE_SETTING)) {
					$('#testsmsdiv').modal('hide');
					$('#smscheck').bootstrapSwitch('disabled', false);
					$('#smssendtypedes').html($('#sendtypeinternet').html());
				}
				if (res.data.value != null) {
					smsConf.quantity = res.data.value;
					$('#smsquantity').text(smsConf.quantity);
				}
			});
		});
	};

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
	Settings_Sms_Notice.init();
});