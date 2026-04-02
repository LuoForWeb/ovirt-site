var resetPwd = function () {
    var passTips = "";
    var passComplexityInfo = "";   //密码复杂度
    var passLength = 0;   //密码最小长度
    var handleValidation = function (passComplexityInfo, passLength) {
        $('.resetpwd-form').validate({
            errorElement: 'span',
            errorClass: 'help-block',
            focusInvalid: false,
            rules: {
                password: {
                    minlength: passLength,
                    required: true,
                    passcomplexity: true
                },
                resetPassWord: {
                    required: true,
                    equalTo: '#password'
                }
            },
            messages: {
                password: {
                    required: LANG.UI_LOGIN_TIPS_INPUT_PASSWORD
                },
                resetPassWord: {
                    required: LANG.UI_LOGIN_TIPS_INPUT_PASSWORD
                }
            },
            highlight: function (element) {
                $(element).closest('.form-input').addClass('has-error');
            },
            success: function (label, element) {
                $(element).siblings('.passwordRule').html('');
                $(element).closest('.form-input').removeClass('has-error');
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
                    match = this.optional(element) || /^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ])[0-9a-zA-Z~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ]/.test(value);
                    break;
                case 3: //强(必须包含大小写字母,数字,特称字符)
                    match = this.optional(element) || /^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ])[0-9a-zA-Z~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ]/.test(value);
                    break;
            }
            return match;
        }, passTips);
    }
    var passComplexity = function (params,passLength) {
        switch (params) {
            case 1: //弱(包含字母(不区分大小写),数字)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
            case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
                break;
            case 3: //强(必须包含大小写字母,数字,特称字符)
                passTips =  LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
                break;
            default:
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
        }
    }
    $('.resetpwd-form input').keypress(function (e) {
        if (e.which == 13) {
            if ($('.resetpwd-form').validate().form()) {
                editSubmit();
            }
            return false;
        }
    });
    var toggleInput = function () {
        $('#password').focus(function () {
            $(this).siblings('i').addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('#password').blur(function () {
            $(this).siblings().removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })
        $('#repassWord').focus(function () {
            $(this).siblings('i').addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('#repassWord').blur(function () {
            $(this).siblings('i').removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })

    }
    var passwordToggle = function () {
        //  默认密码是不可见的
        $('.input-icon .pwdIcon').click(function () {
            if ($('.passwordInput').attr('type') == 'password') {
                $('.passwordInput').attr('type', 'text');
            } else {
                $('.passwordInput').attr('type', 'password');
            }
        })
        $('.input-icon .repwdIcon').click(function () {
            if ($('.repasswordInput').attr('type') == 'password') {
                $('.repasswordInput').attr('type', 'text');
            } else {
                $('.repasswordInput').attr('type', 'password');
            }
        })

    }
    var editSubmit = function () {
        var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
        var username = encrypt.encrypt($.trim($('input[name=username]').val()));
        var password = encrypt.encrypt($('input[name=password]').val());
        var resetpassWord = encrypt.encrypt($('input[name=resetPassWord]').val());
        var data={username:username,password:password,resetpassWord:resetpassWord};
        pAjaxRequest(data,'/api/v1/users/reset/password','POST',function (d){
            var data=d.data;
            if(data.result){
                var showPage = 'reset_success';
                window.location.href='/message_page.php?show='+showPage;
            }
        })
    }
    //获取密码复杂度
    var getpassConfigInfo=function () {
        pAjaxRequest({},'/api/v1/users/pass/configInfo','GET',function (d) {
            var data=d.data;
            passComplexityInfo=parseInt(data.passcomplexity);
            passLength=data.passlength;
            passComplexity(passComplexityInfo, passLength);
            handleValidation(passComplexityInfo, passLength);
        });
    }
    var linkTimeOut = function () {
        var time = $('.sessionTime').html();
        var nowtime = $('.sessionNowTime').html();
        var timeout = $('.sessionTimeOut').html();
        // 倒计时五分钟后隐藏form内容
        setInterval(function () {
            if (nowtime - time > timeout) {
                $('.resetpwd-form .form-group').hide();
                $('#resetBtn').hide();
                $('#returnLogin').show();
                $('.resetpwd-form .tip').html(LANG.UI_USER_RESEND_EMAIL_TIPS);
            }
        }, 1000)
    }
    return {
        init: function () {
            linkTimeOut();
            getpassConfigInfo();
            toggleInput();
            passwordToggle();
        }
    }
}();
$(function () {
    resetPwd.init();
    // init background slide images
    $.backstretch([
            "/img/platform/bg-home.svg",
            "/img/platform/bg-home.svg",
            "/img/platform/bg-home.svg",
            "/img/platform/bg-home.svg"
        ], {
            fade: 0,
            duration: 8000
        }
    );
})