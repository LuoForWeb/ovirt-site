var modifyPwd = function () {
    var passTips = "";
    var passComplexityInfo = "";   //密码复杂度
    var passLength = 0;   //密码最小长度
    var _USERUUID = window.sessionStorage.getItem('useruuid');
    var handleValidation = function (passComplexityInfo, passLength) {
        $('.modifyPwd-form').validate({
            errorElement: 'span',
            errorClass: 'help-block',
            focusInvalid: false,
            rules: {
                oldpass: {
                    required: true,
                    oldpassAvailable: true
                },
                password: {
                    minlength: passLength,
                    required: true,
                    passcomplexity: true,
                    compareOldcode: true,
                },
                newPassword: {
                    required: true,
                    equalTo: '#password'
                }
            },
            messages: {
                oldpass: {
                    required: LANG.UI_LOGIN_TIPS_INPUT_PASSWORD  //原密码
                },
                password: {
                    required: LANG.UI_LOGIN_TIPS_INPUT_PASSWORD
                },
                newPassword: {
                    required: LANG.UI_LOGIN_TIPS_INPUT_PASSWORD
                }
            },
            highlight: function (element) {
                $(element).closest('.form-group').addClass('has-error');
            },
            success: function (label, element) {
                $(element).siblings('.passwordRule').html('');
                $(element).closest('.form-group').removeClass('has-error');
            },
            submitHandler: function (form) {
                editSubmit();
            },
        })
        $.validator.addMethod("passcomplexity", function (value, element) {
            var match = "";
            switch (passComplexityInfo) {
                case 1: //弱(包含字母(不区分大小写),数字)
                    match = this.optional(element) || /^[A-Za-z0-9]/.test(value);
                    break;
                case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
                    match = this.optional(element) || /^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/.test(value);
                    break;
                case 3: //强(必须包含大小写字母,数字,特称字符)
                    match = this.optional(element) || /^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/.test(value);
                    break;
            }
            return match;
        }, passTips);
        
        $.validator.addMethod("compareOldcode",function (value, element) {
            var oldPassword =hex_md5($("input[name=oldpass]").val());
            var password = hex_md5(value);
                if(oldPassword == password){
                    return false;
                }
                return true;
            },
            LANG.UI_USER_NOT_SAME_OLD_PASSWORD
        );

        $.validator.addMethod(
            "oldpassAvailable",
            function(value, element, param) {
                var data = {password:value, useruuid:_USERUUID};
                var result = false;
                pAjaxRequest(data,'/api/v1/users/oldpass','GET',function (d){
                    let data = d.data;
                    result = data.result;
                },false);
                return result;
            },
            LANG.UI_USER_OLD_PASSWORD_ERROR
        );
    }
    var passComplexity = function (params, passLength) {
        switch (params) {
            case 1: //弱(包含字母(不区分大小写),数字)
                passTips =  LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + passLength  + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
            case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength +  LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
                break;
            case 3: //强(必须包含大小写字母,数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
                break;
            default:
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
        }
    }
    $('.modifyPwd-form input').keypress(function (e) {
        if (e.which == 13) {
            if ($('.modifyPwd-form').validate().form()) {
                editSubmit();
            }
            return false;
        }
    });
    var toggleInput = function () {
        $('.form-group .form-control').focus(function () {
            $(this).siblings().eq(0).addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('.form-group .form-control').blur(function () {
            $(this).siblings().removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })
    }
    var passwordToggle = function () {
        //默认密码是不可见的
        $('.input-icon .oldpwdIcon').click(function () {
            if ($('#oldpass').attr('type') == 'password') {
                $('#oldpass').attr('type', 'text');
            } else {
                $('#oldpass').attr('type', 'password');
            }
        })
        $('.input-icon .pwdIcon').click(function () {
            if ($('#password').attr('type') == 'password') {
                $('#password').attr('type', 'text');
            } else {
                $('#password').attr('type', 'password');
            }
        })
        $('.input-icon .repwdIcon').click(function () {
            if ($('#newPassword').attr('type') == 'password') {
                $('#newPassword').attr('type', 'text');
            } else {
                $('#newPassword').attr('type', 'password');
            }
        })
    }

    //修改密码
    var editSubmit = function () {
        var data = {};
        data.useruuid = _USERUUID;
        data.oldPassword = hex_md5($("input[name=oldpass]").val());
        data.newPassword = hex_md5($("input[name=password]").val());
        pAjaxRequest(data,'/api/v1/users/edit/password','POST',function (d){
            if(d.success){
                window.location.href='./login.php';
            }
        });
    }
    //获取密码复杂度
    var getpassConfigInfo = function () {
        pAjaxRequest({},'/api/v1/users/pass/configInfo','GET',function (d){
            var data=d.data;
            passComplexityInfo=parseInt(data.passcomplexity);
            passLength=data.passlength;
            passComplexity(passComplexityInfo, passLength);
            handleValidation(passComplexityInfo, passLength);
        });
    }

    return {
        init: function () {
            getpassConfigInfo();
            passwordToggle();
            toggleInput();
        }
    }
}();
$(function () {
    modifyPwd.init();
    // init background slide images
    $.backstretch([
            "./img/platform/bg-home.svg",
            "./img/platform/bg-home.svg",
            "./img/platform/bg-home.svg",
            "./img/platform/bg-home.svg"
        ], {
            fade: 0,
            duration: 8000
        }
    );
})