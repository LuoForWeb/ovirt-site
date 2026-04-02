var modifyPwd=function () {
    var passTips = "";
    var passComplexityInfo="";   //密码复杂度
    var passLength=0;   //密码最小长度
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
                    minlength:passLength,
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
                newPassword:{
                    required:LANG.UI_LOGIN_TIPS_INPUT_PASSWORD,
                    equalTo: LANG.UI_LOGIN_TIPS_CONFIRM_PASSWORD
                }
            },
            highlight: function(element){
                $(element).closest('.form-group').addClass('has-error');
            },
            success:function(label,element){
                $(element).siblings('.passwordRule').html('');
                $(element).closest('.form-group').removeClass('has-error');
            },
            submitHandler: function (form) {
                editSubmit();
            },
        })
        $.validator.addMethod("passcomplexity", function(value, element) {
            var match = "";
            switch(passComplexityInfo){
                case 1: //弱(包含字母(不区分大小写),数字)
                    match = this.optional( element ) || /^[A-Za-z0-9!@#$%^&*,.]/.test(value);
                    break;
                case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
                    match = this.optional( element ) || /^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/.test(value);
                    break;
                case 3: //强(必须包含大小写字母,数字,特称字符)
                    match = this.optional( element ) || /^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/.test(value);
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
    }
    var passComplexity = function(params,passLength){
        switch(params){
            case 1: //弱(包含字母(不区分大小写),数字)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
            case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
                break;
            case 3: //强(必须包含大小写字母,数字,特称字符)
                passTips =LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
                break;
            default:
                passTips =LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
        }
    }
    var focusState=function () {
        $('input[name=password]').focus(function () {
            $('.lock svg .password').css('fill','#00E2D9');
        })
        $('input[name=resetPassWord]').focus(function () {
            $('.lock svg .repassword').css('fill','#00E2D9');
        })
        $('input[name=password]').blur(function () {
            $('.lock svg .password').css('fill','#fff');
        })
        $('input[name=resetPassWord]').blur(function () {
            $('.lock svg .repassword').css('fill','#fff');
        })
    }
    var toggleInput=function () {
        $('.userInput #username').focus(function () {
            $(this).siblings('i').addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('.userInput #username').blur(function () {
            $(this).siblings().removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })
        $('.userInput #oldpass').focus(function () {
            $(this).siblings('i').addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('.userInput #oldpass').blur(function () {
            $(this).siblings('i').removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })
        $('.userInput #password').focus(function () {
            $(this).siblings('i').addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('.userInput #password').blur(function () {
            $(this).siblings().removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })
        $('.userInput #newPassword').focus(function () {
            $(this).siblings('i').addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('.userInput #newPassword').blur(function () {
            $(this).siblings().removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })
    }
    // var passwordToggle=function(){
    //     //  默认密码是不可见的
    //     $('.form-input .eye').hide();
    //     $('.form-oldpassword .eye-close').click(function () {
    //         $('.form-oldpassword .eye-close').hide();
    //         $('.form-oldpassword .eye').show();
    //         $('.toggle-oldpassword').attr('type','text');
    //     })
    //     $('.form-oldpassword .eye').click(function () {
    //         $('.form-oldpassword .eye').hide();
    //         $('.form-oldpassword .eye-close').show();
    //         $('.toggle-oldpassword').attr('type','password');
    //     })
    //     $('.form-password .eye-close').click(function () {
    //         $('.form-password .eye-close').hide();
    //         $('.form-password .eye').show();
    //         $('.toggle-password').attr('type','text');
    //     })
    //     $('.form-password .eye').click(function () {
    //         $('.form-password .eye').hide();
    //         $('.form-password .eye-close').show();
    //         $('.toggle-password').attr('type','password');
    //     })
    //     $('.form-repassword .eye-close').click(function () {
    //         $('.form-repassword .eye-close').hide();
    //         $('.form-repassword .eye').show();
    //         $('.toggle-repassword').attr('type','text');
    //     })
    //     $('.form-repassword .eye').click(function () {
    //         $('.form-repassword .eye').hide();
    //         $('.form-repassword .eye-close').show();
    //         $('.toggle-repassword').attr('type','password');
    //     })
    // }


    //修改密码
    var editSubmit=function(){
        var data = {};
        data.useruuid = _USERUUID;
        data.oldPassword = hex_md5($("input[name=oldpass]").val());
        data.newPassword = hex_md5($("input[name=password]").val());
        pAjaxRequest(data,'/api/v1/users/edit/password','POST',function (d){
            var data=d.data;
            if(d.success){
                window.location.href='./login.php';
            }
        });
    }
    //获取密码复杂度
    var getpassConfigInfo=function () {
        pAjaxRequest({},'/api/v1/users/pass/configInfo','GET',function (d){
            var data=d.data;
            passComplexityInfo=parseInt(data.passcomplexity);
            passLength=data.passlength;
            passComplexity(passComplexityInfo, passLength);
            handleValidation(passComplexityInfo, passLength);
        });
    }


    return {
        init:function(){
            getpassConfigInfo();
            focusState();
            // passwordToggle();
            toggleInput();
        }
    }
}();
$(function (){
    modifyPwd.init();
})