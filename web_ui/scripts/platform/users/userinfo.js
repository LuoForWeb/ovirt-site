var UserInfo = function(){
	var _OLDLANG = '';	//默认的语言
	var _NEWLANG = '';  //新的语言
	var _OLDUSERNAME = '';	//默认的用户名
	var _NEWUSERNAME = '';  //新的用户名
	var _UserPassword; // 用户的用户密码
	var initErrorFlag = false;
	var _USERUUID = '';  //用户id
	var _USERTYPE = '';  //用户类型
	var _USERLEVEL = ''; //用户等级
	var _PRODUCTTYPE = ''; //版本
	var _ISTHREEPOWERS = ''; //是否是三权
	var passTips = "";
	var oldCustomerPasswordRequireFlag = false;
	// validation using icons
	// 验证用户名
	var handleValidation_username = function() {
		var form1 = $('#form_sample_1');
		form1.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				new_username: {
					required: true,
					usernameAvailable: true,
					minlength: 2
				}
			},
			invalidHandler: function (event, validator) {
				event.preventDefault(); // 阻止表单提交
				return false;
			},
			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({
					'container': 'body'
				});
				icon.show();
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
		});
		$("#username_submit").click(function(){
			if (form1.validate().form()) {
				var data = {};
				data.username = $('input[name=new_username]').val();
				data.language = $('#langtype').val();
				data.oldlang = _OLDLANG;
				_NEWUSERNAME = data.username;
				_NEWLANG = data.language;
				submit(data,true);
			}
		});
		$.validator.addMethod("username", function(value, element) {
			return this.optional(element) || /^[a-zA-Z\u4e00-\u9fa5][a-zA-Z0-9_\u4e00-\u9fa5@.\-]{0,63}$/i.test(value);
		}, LANG.UI_USER_NAME_RULE);
		$.validator.addMethod("usernameAvailable", function (value, element, param) {
				var data = {};
				data.username = $('input[name=new_username]').val();
				data.useruuid = _USERUUID;
				data.user_type = _USERTYPE;
				var result = false;
				pAjaxRequest(data, '/api/v1/users/check/username', 'GET', function (res) {
					result = res.data.success;
				}, async = false);
				return result;
			},
			LANG.UI_USER_NAME_EXISTS
		);
	};

	// 验证联系电话
	var handleValidation_number = function() {
		var form2 = $('#form_sample_2');
		form2.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				number:{
					required: false
				}
			},
			invalidHandler: function (event, validator) { //display error alert on form submit
				return true;
			},
			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({
					'container': 'body'
				});
				icon.show();
			},
			highlight: function (element) { // hightlight error inputs
				$(element).closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},
			unhighlight: function (element) { // revert the change done by hightlight

			},
			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			},
		});
		$("#number_submit").click(function(){
			if (form2.validate().form()) {
				var data = {};
				data.telephone = $('input[name=number]').val();
				data.language = $('#langtype').val();
				data.oldlang = _OLDLANG;
				_NEWLANG = data.language;
				submit(data);
			}
		});
	};

	// 验证登录密码
	var handleValidation_password = function(passComplexityInfo,passLength) {
		var form3 = $('#form_sample_3');
		form3.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				old_password:{
					required: true,
					oldpassAvailable: true,
				},
				new_password:{
					required: true,
					passcomplexity: true,
					compareOldcode: true
				},
				confirm_new_password:{
					required: true,
					equalTo: '[name="new_password"]'
				},
			},
			invalidHandler: function (event, validator) {
			},
			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({
					'container': 'body'
				});
				icon.show();
			},
			highlight: function (element) {
				$(element).closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},
			unhighlight: function (element) {
			},
			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			},
		});
		$.validator.addMethod("oldpassAvailable", function(value, element, param) {
				var data = {password:value, useruuid:_USERUUID};
				var result = false;
				pAjaxRequest(data,'/api/v1/users/oldpass','GET',function (d){
					result = d.data.result;
				},false);
				return result;
			},
			LANG.UI_USER_OLD_PASSWORD_ERROR
		);
		$.validator.addMethod("passcomplexity", function(value, element) {
			var match = "";
			switch(passComplexityInfo){
				case 1: //弱(包含字母(不区分大小写),数字,特殊字符(不是必须))
					var pattern = "^[\\S]{" + CONF.PASS_LENGTH + ",}$";
					match = this.optional(element) || new RegExp(pattern).test(value);
					break;
				case 2: //中(必须包含字母(不区分大小写),数字,特殊字符(不是必须))
					var pattern = "^(?=.*[0-9])(?=.*[A-Za-z])[\\S]{" + CONF.PASS_LENGTH + ",}$";
					match = this.optional(element) || new RegExp(pattern).test(value);
					break;
				case 3: //强(必须包含大小写字母,数字,特殊字符(不是必须))
					var pattern = "^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])[\\S]{" + CONF.PASS_LENGTH + ",}$"
					match = this.optional(element) || new RegExp(pattern).test(value);
					break;
			}
			return match;
		}, passTips);
		$.validator.addMethod("compareOldcode",function (value, element) {
				var oldPassword =hex_md5($("input[name=old_password]").val());
				var password = hex_md5(value);
				if(oldPassword == password){
					return false;
				}
				return true;
			},
			LANG.UI_USER_NOT_SAME_OLD_PASSWORD
		);
		$("#password_submit").click(function(){
			if (form3.validate().form()) {
				var data = {};
				var encrypt = new JSEncrypt();
				encrypt.setPublicKey(CONF.PUBLIC_KEY);
				data.password = encrypt.encrypt($('input[name=new_password]').val());
				data.language = $('#langtype').val();
				data.oldlang = _OLDLANG;
				_NEWLANG = data.language;
				submit(data);
			}
		});
	};

	// 验证邮箱
	var handleValidation_email = function() {
		var form4 = $('#form_sample_4');
		form4.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				email:{
					required: false,
					checkEmail: true
				}
			},
			invalidHandler: function (event, validator) { //display error alert on form submit
				return true;
			},
			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({
					'container': 'body'
				});
				icon.show();
			},
			highlight: function (element) { // hightlight error inputs
				$(element).closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},
			unhighlight: function (element) { // revert the change done by hightlight

			},
			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			},
		});
		//检查邮件地址
		var checkEmail = function(email){
			var reg = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
			if("" != email){
				if(!reg.test(email)){
					UIToastr.showWarning(LANG.UI_SETTING_EMAIL_INPUT, LANG.UI_SETTING_EMAIL_INPUT_TIP);
					return false;
				}
			}else if(email == ""){
				return "";
			}
			return email;
		}

		$.validator.addMethod("checkEmail", function(value, element, param) {
			if(value == ''){
				return true;
			}
			var reg = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
			return reg.test(value);
			},
			LANG.UI_SETTING_EMAIL_INPUT_TIP
		);

		$("#email_submit").click(function(){
			if (form4.validate().form()) {
				var data = {};
				data.email = $('input[name=email]').val();
				data.language = $('#langtype').val();
				data.oldlang = _OLDLANG;
				_NEWLANG = data.language;
				submit(data);
			}
		});
	};

	// 验证独立密码
	var handleValidation_custome_password = function(passComplexityInfo,passLength,oldCustomerPasswordRequireFlag) {
		var form5 = $('#form_sample_5');
		form5.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				old_custome_password:{
					required:oldCustomerPasswordRequireFlag,
					oldCustomePasswordAvailable:oldCustomerPasswordRequireFlag,
				},
				new_custome_password:{
					required: true,
					passcomplexity: true,
				},
				confirm_custome_password:{
					required: true,
					equalTo: '[name="new_custome_password"]'
				},
			},
			invalidHandler: function (event, validator) {
				event.preventDefault(); // 阻止表单提交
				return false;
			},
			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({
					'container': 'body'
				});
				icon.show();
			},
			highlight: function (element) {
				$(element).closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},
			unhighlight: function (element) {
			},
			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
				icon.show();
			},
		});
		$.validator.addMethod("passcomplexity", function(value, element) {
			var match = "";
			switch(passComplexityInfo){
				case 1: //弱(包含字母(不区分大小写),数字,特殊字符(不是必须))
					var pattern = "^[\\S]{" + CONF.PASS_LENGTH + ",}$";
					match = this.optional(element) || new RegExp(pattern).test(value);
					break;
				case 2: //中(必须包含字母(不区分大小写),数字,特殊字符(不是必须))
					var pattern = "^(?=.*[0-9])(?=.*[A-Za-z])[\\S]{" + CONF.PASS_LENGTH + ",}$";
					match = this.optional(element) || new RegExp(pattern).test(value);
					break;
				case 3: //强(必须包含大小写字母,数字,特殊字符(不是必须))
					var pattern = "^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])[\\S]{" + CONF.PASS_LENGTH + ",}$"
					match = this.optional(element) || new RegExp(pattern).test(value);
					break;
			}
			return match;
		}, passTips);
		$.validator.addMethod("oldCustomePasswordAvailable", function(value, element, param) {
				var data = {custome_password:value, useruuid:_USERUUID};
				var result = false;
				pAjaxRequest(data,'/api/v1/users/oldcustomepass','GET',function (d){
					result = d.data.result;
				},false);
				return result;
			},
			LANG.UI_USER_INFO_OLD_CUSTOME_PASSWORD_ERROR
		);
		$("#custome_password_submit").click(function(){
			if (form5.validate().form()) {
				var data = {};
				var encrypt = new JSEncrypt();
				encrypt.setPublicKey(CONF.PUBLIC_KEY);
				data.custome_password = encrypt.encrypt($('input[name=new_custome_password]').val());
				data.language = $('#langtype').val();
				data.oldlang = _OLDLANG;
				_NEWLANG = data.language;
				submit(data);
			}
		});
	};

	var submit = function(data,flag = false){
		if(flag){
			// 需要判读下，如果修改了用户名，那么需要二次密码验证
			initErrorFlag = false;
			bootbox.prompt({
				title: LANG.UI_PLATFORM_TENANT_INPUT_PSW,
				inputType: 'password',
				callback: function (result) {
					if(result == null) return;
					if(hex_md5(result) == _UserPassword){
						// 进行保存修改
						Metronic.blockUI({target:".drawer-body",animate: true});
						pAjaxRequest(data,'/api/v1/users/self/info','PUT',editResult);
					}else{
						$('.bootbox-input').css('border-color', "#a94442");
						if(!initErrorFlag){
							var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+ LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS +'</p>';
							$('.bootbox-input').after(des);
							initErrorFlag = true;
						}
						return false;
					}
				}
			});
		}else{
			Metronic.blockUI({target:".drawer-body",animate: true});
			pAjaxRequest(data,'/api/v1/users/self/info','PUT',editResult);
		}
	};

	var editResult = function(d){
		Metronic.unblockUI('.drawer-body');
		if (d['success'] && _OLDUSERNAME != _NEWUSERNAME) {
			d['message'] += "," + LANG.UI_USER_RELOGIN_TIPS;
		}else if(d['success'] && _OLDLANG != _NEWLANG){
			d['message'] += "," + LANG.UI_USER_EIDT_SELF_TIPS;
		}
		if(operateResponseList(d)){
			if(_OLDUSERNAME != _NEWUSERNAME){
				//如果修改了用户名,5秒后跳转到登录页面
				setTimeout(function(){window.location.href='/loginout.php';}, 5000);
			} else if(_OLDLANG != _NEWLANG){
				//如果修改了语言,重新加载页面
				setTimeout(function(){window.location.reload();}, 5000);
			} else{
				$('.drawer').drawer('hide');
				initData();
			}
		}
	};
	// 添加事件
	var addListeners = function (){
		// 清空表单
		$('#edit_username').off().on('click',function (){
			document.getElementById("form_sample_1").reset();
			removeValidateStyle();
		})
		$('#set_number').off().on('click',function (){
			document.getElementById("form_sample_2").reset();
			removeValidateStyle();
		})
		$('#edit_password').off().on('click',function (){
			document.getElementById("form_sample_3").reset();
			removeValidateStyle();
		})
		$('.set_email_des').off().on('click',function (){
			document.getElementById("form_sample_4").reset();
			removeValidateStyle();
		})
		$('.set_custome_password_des').off().on('click',function (){
			document.getElementById("form_sample_5").reset();
			removeValidateStyle();
		})
		// 隐藏抽屉
		$('.cancel').off().on('click',function (){
			$('.drawer').drawer('hide');
		})
	}

	/**
	 * 移除所有提示信息
	 */
	var removeValidateStyle = ()=>{
		$('.alert-danger').hide();
		$(".fa-warning").hide();
		$(".form-group").removeClass('has-error');
		$(".fa-check").hide();
		$(".form-group").removeClass('has-success');
	}

	//初始化数据
	var initData = function(){
		pAjaxRequest({},'/api/v1/users/self/info','GET',setInfo,false);
	}

	var initPassComplexity = function () {
		switch (CONF.PASS_COMPLEXITY) {
			case 1: //弱(包含字母(不区分大小写),数字,特殊字符)
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
				break;
			case 2: //中(必须包含字母(不区分大小写),数字,特称字符)
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
				break;
			case 3: //强(必须包含大小写字母,数字,特称字符)
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
				break;
			default:
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
				break;
		}
	}

	var setInfo = function(d){
		var data = d.data;
		if(d.success){
			$('#uservalue').html(data.username);
			$('.role_str').html(data.role_str || LANG.UI_USER_INFO_NOT_CONFIGURED);
			$('.username_content').html(data.username);
			var user_level = data.user_level;
			var user_level_des = '';
			switch (user_level) {
				case 1:
				case 2:
				case 3:
					user_level_des = LANG.UI_TENANT_ADMIN;
					break;
				default:
					user_level_des = LANG.UI_USER_INFO_REGULAR_USER;
					break;
			}
			$('#usertype_content').html(user_level_des);
			$('#last_login_time_content').html(data.last_login_time);
			$('#number').html(data.telephone || LANG.UI_BACKUP_DATA_POINT_DES_NOT_CONFIGED);
			if(data.telephone != null && !!data.telephone){
				$('.set_number_des').html(LANG.UI_PUBLIC_EDIT);
				$('.telnumber_des').html(LANG.UI_USER_INFO_MODIFY_CONTACT_NUMBER);
				$('.telnumber_title_icon').removeClass('vicon-Frame15').addClass('vicon-a-Editbianji');
			}else{
				$('.set_number_des').html(LANG.UI_USER_INFO_SETTINGS);
				$('.telnumber_des').html(LANG.UI_USER_INFO_SET_CONTACT_PHONE_NUMBER);
				$('.telnumber_title_icon').removeClass('vicon-a-Editbianji').addClass('vicon-Frame15');
			}
			if(data.email != null && !!data.email){
				$('.set_email_des').html(LANG.UI_JOB_MODIFY);
				$('.email_des').html(LANG.UI_USER_INFO_EDIT_EMAIL_ADDRESS);
				$('.email_title_icon').removeClass('vicon-Frame15').addClass('vicon-a-Editbianji');
			}else{
				$('.set_email_des').html(LANG.UI_USER_INFO_SETTINGS);
				$('.email_des').html(LANG.UI_USER_INFO_SET_EMAIL_ADDRESS);
				$('.email_title_icon').removeClass('vicon-a-Editbianji').addClass('vicon-Frame15');
			}
			$('input[name=password]').val(data.password);
			$('#email_address').html(data.email || LANG.UI_CM_NOT_CONFIGURED);
			if(data.custome_password != null && !!data.custome_password){
				$('input[name=custome_password]').attr('type','password');
				$('input[name=custome_password]').val(data.custome_password);
				$('.set_custome_password_des').html(LANG.UI_USER_INFO_MODIFY);
				$('.old_custome_password_div').show();
				$('.custome_password_des').html(LANG.UI_USER_INFO_MODIFY_CUSTOME_PASSWORD);
				oldCustomerPasswordRequireFlag = true;
				$('.custome_password_title_icon').removeClass('vicon-Frame15').addClass('vicon-a-Editbianji');
			}else{
				$('input[name=custome_password]').attr('type','text');
				$('input[name=custome_password]').val(LANG.UI_CM_NOT_CONFIGURED);
				$('.set_custome_password_des').html(LANG.UI_USER_INFO_SETTINGS);
				$('.old_custome_password_div').hide();
				oldCustomerPasswordRequireFlag = false;
				$('.custome_password_des').html(LANG.UI_USER_INFO_SET_CUSTOME_PASSWORD);
				$('.custome_password_title_icon').removeClass('vicon-a-Editbianji').addClass('vicon-Frame15');
			}

			if(!data.auth_code){
				$('.authCodeDiv').hide();
			}else{
				$('#auth_code').html(data.auth_code);
				if(clipboard){
					//销毁上一次
					clipboard.destroy();
				}
				clipboard = new ClipboardJS('.btn').on('success', function(e) {
					UIToastr.showSuccess(LANG.UI_PUBLIC_ALREADY_COPY);
					e.clearSelection();　　//取消选择节点
				});
			}
			var select = $('#langtype');
			var option = "";
			for(var i=0; i<data.list.length; i++){
				option += '<option value="' + data.list[i][0] + '">' + data.list[i][1] + '</option>';
			}
			select.append(option);
			select.val(data.language);
			_OLDLANG = data.language;
			_OLDUSERNAME = data.username;
			_NEWUSERNAME = data.username;
			_USERUUID = data.user_uuid;
			_USERTYPE = data.user_type;
			_USERLEVEL = data.user_level;
			_PRODUCTTYPE = data.product_type;
			_ISTHREEPOWERS = data.is_three_powers;
		}else{
			operateResponseList(d)
		}
	}

	//初始化当前用户密码用于删除二次确认
	var initUserPassword = function(){
		pAjaxRequest({},'/api/v1/users/password','GET',function(d){
			_UserPassword = d.data.password;
		});
	}

	//获取密码复杂度
	var getpassConfigInfo=function () {
		pAjaxRequest({},'/api/v1/users/pass/configInfo','GET',function (d){
			var data=d.data;
			var passComplexityInfo=parseInt(data.passcomplexity);
			var passLength=data.passlength;
			handleValidation_password(passComplexityInfo, passLength);
			handleValidation_custome_password(passComplexityInfo, passLength,oldCustomerPasswordRequireFlag);
		});
	}

	return {
		//main function to initiate the module
		init: function () {
			addListeners();
			initData();
			initPassComplexity();
			handleValidation_username();
			handleValidation_number();
			getpassConfigInfo();
			handleValidation_email();
			initUserPassword();
		}

	};
}();

jQuery(document).ready(function() {
	UserInfo.init();
});