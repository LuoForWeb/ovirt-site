var resetPwd=function () {
    var passTips = "";
    var passComplexityInfo="";   //密码复杂度
    var passLength=0;   //密码最小长度
    var handleValidation = function (passComplexityInfo, passLength) {
        $('.resetpwd-form').validate({
            errorElement: 'div',
            errorClass: 'help-block',
            focusInvalid: false,
            rules: {
                password: {
                    minlength:passLength,
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
                    required: LANG.UI_PUBLIC_ENTER_PASSWORD
                },
                resetPassWord:{
                    required:LANG.UI_PUBLIC_ENTER_PASSWORD
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
                    match = this.optional( element ) || /^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ])[0-9a-zA-Z~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ]/.test(value);
                    break;
                case 3: //强(必须包含大小写字母,数字,特称字符)
                    match = this.optional( element ) || /^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ])[0-9a-zA-Z~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ]/.test(value);
                    break;
            }
            return match;
        }, passTips);
    }
    var passComplexity = function(params, passLength){
        switch(params){
            case 1: //弱(包含字母(不区分大小写),数字)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
            case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
                break;
            case 3: //强(必须包含大小写字母,数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
                break;
            default:
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + passLength + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
        }
    };
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
        $('.form-password #password').focus(function () {
            $(this).siblings('i').addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('.form-password #password').blur(function () {
            $(this).siblings().removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })
        $('.form-repassword #repassWord').focus(function () {
            $(this).siblings('i').addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('.form-repassword #repassWord').blur(function () {
            $(this).siblings('i').removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })

    }
    // var passwordToggle=function(){
    //     //  默认密码是不可见的
    //     $('.form-input .eye').hide();
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

    var editSubmit=function(){
        var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
        var username = encrypt.encrypt($.trim($('input[name=username]').val()));
        var password = encrypt.encrypt($('input[name=password]').val());
        var resetpassWord=encrypt.encrypt($('input[name=resetPassWord]').val());
        var data={username:username,password:password,resetpassWord:resetpassWord};
        pAjaxRequest(data,'/api/v1/users/reset/password','POST',function (d){
            var data=d.data;
            if(data.result){
                var showPage = 'reset_success';
                window.location.href='./message_page.php?show='+showPage;
            }
        });
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
    var linkTimeOut=function () {
        var time = $('.sessionTime').html();
        var nowtime=$('.sessionNowTime').html();
        var timeout=$('.sessionTimeOut').html();
       // 倒计时五分钟后隐藏form内容
       setInterval(function () {
            if(nowtime-time>timeout){
                $('.resetpwd-form').hide();
                $('#returnLogin').show();
                $('.link-failure').show();
                $('.tip').html(LANG.UI_USER_RESEND_EMAIL_TIPS);
            }
       },1000)
    }

    return {
        init:function(){
            linkTimeOut();
            getpassConfigInfo();
            focusState();
            toggleInput();
            // passwordToggle();
        }
    }
}();
$(function (){
    resetPwd.init();
})