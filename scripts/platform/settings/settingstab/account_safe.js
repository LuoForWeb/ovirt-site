var Settings_ACCOUNT_SAFE = function () {
	let is_three_powers = false; // 是否是三权定义
	let user_level = 5; // 当前用户级别

	var handleValidation = function() {
        var safeform = $('#accountsafeform');

        safeform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
                outtime: {
                    required: true,
                    timeout: true,
                },
                faildcount: {
                	required: true,
                	faildtime: true,
                },
				faild_lock_time: {
					required: true,
					locktime: true,
				},
                passwordtime: {
                	required: true,
                	passwordday: true
                },
                passlength: {
                	required: true,
                	passwordday: true
                },
                passcomplexity: {
                	required: true,
                },
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
        
        //超时时间IDLETIMEOUT
        $.validator.addMethod("timeout", function(value, element) {
        	return this.optional(element) || /^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/i.test(value);
        }, LANG.UI_SETTING_INPUT_NUM);
        
        //最大登录失败次数
        $.validator.addMethod("faildtime", function(value, element) {
        	return this.optional(element) || /^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/i.test(value);
        }, LANG.UI_SETTING_INPUT_NUM);

		//登录失败锁定时间
		$.validator.addMethod("locktime", function(value, element) {
			return this.optional(element) || /^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/i.test(value);
		}, LANG.UI_SETTING_INPUT_NUM);
        
        //密码可用天数
        $.validator.addMethod("passwordday", function(value, element) {
        	return this.optional(element) || /^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/i.test(value);
        }, LANG.UI_SETTING_INPUT_NUM);
        
        
        $("#safesubmit").click(function(){
        	if (safeform.validate().form()) {
        		submitSafe();
            }
        });
        
        $("#safecancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php?tab=9', 'setting_manager');
        });
        
	};
	
	var submitSafe = function(){
		var data = {};
		data.out_time = $('input[name=outtime]').val();
		data.faild_count =$('input[name=faildcount]').val();
		data.faild_lock_time =$('input[name=faild_lock_time]').val();
		data.password_time = $('input[name=passwordtime]').val();
		data.passlength = $('input[name=passlength]').val();
		data.passcomplexity = $('select[name=passcomplexity]').val();

		if (data.passlength > 32) {
			return UIToastr.showWarning(LANG.UI_ACCOUNT_SAFE_TITLE, LANG.UI_ACCOUNT_SAFE_PASSWORD_LENGTH_MAX);
		}

		if (is_three_powers) {
			if(data.out_time > 600){
				return UIToastr.showWarning(LANG.UI_ACCOUNT_SAFE_TITLE, LANG.UI_ACCOUNT_SAFE_OUT_TIME);
			}
			if(data.faild_count > 5){
				return UIToastr.showWarning(LANG.UI_ACCOUNT_SAFE_TITLE, LANG.UI_ACCOUNT_SAFE_OUT_NUM);
			}
			if(data.faild_lock_time < 1800){
				return UIToastr.showWarning(LANG.UI_ACCOUNT_SAFE_TITLE,  LANG.UI_ACCOUNT_SAFE_LOCK_TIME);
			}
			if(data.password_time > 7){
				return UIToastr.showWarning(LANG.UI_ACCOUNT_SAFE_TITLE, LANG.UI_ACCOUNT_SAFE_AVAILABLE_DAYS);
			}
			if(data.passlength < 8){
				return UIToastr.showWarning(LANG.UI_ACCOUNT_SAFE_TITLE, LANG.UI_ACCOUNT_SAFE_PASSWORD_LENGTH);
			}
		}

		pAjaxRequest(data, '/api/v1/system/safe/account', 'POST', function (res) {
			if (res.code == 0 && !is_three_powers) {
				res.message += "," + LANG.UI_SETTING_SYSTEM_WILL_REFRESH;
			}
			if (operateResponseList(res, LANG.UI_ACCOUNT_SAFE_TITLE)){
				!is_three_powers && setTimeout(function(){window.location.reload();}, 5000);
			}
		})
	}
	
	var initOldData = function(){
		pAjaxRequest({}, '/api/v1/system/safe/account', 'GET', function (res) {
			var data = res.data
			$('input[name=outtime]').val(data.out_time);
			$('input[name=faildcount]').val(data.faild_count);
			$('input[name=faild_lock_time]').val(data.faild_lock_time);
			$('input[name=passwordtime]').val(data.password_time);
			$('input[name=passlength]').val(data.passlength);
			$('select[name=passcomplexity]').val(data.passcomplexity);
			is_three_powers = data.is_three_powers;
			user_level = data.user_level;
		})
	}
	
	return {
		init: function(){
			initOldData();
			handleValidation();
		}
	}

}();

jQuery(document).ready(function(){
	Settings_ACCOUNT_SAFE.init();
});