var forgrtPwd = function () {
    var handleForgetPwd = function () {
        $('.forget-form').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            rules: {
                username: {
                    required: true
                },
                email: {
                    required: true
                }
            },
            messages: {
                username: {
                    required: LANG.UI_FORGETPASSWORD_TIPS_INPUT_USERNAME
                },
                email: {
                    required: LANG.UI_FORGETPASSWORD_TIPS_INPUT_USEREMAIL
                }
            },
            //  element出错的时候触发
            highlight: function (element) {
                $('.emailTips').html('');
                $(element).closest('.form-group').addClass('has-error');
            },
            //  input成功时触发
            unhighlight: function (element) {
                $('.emailTips').html('');
                $(element).closest('.form-group').removeClass('has-error');
            },
            submitHandler: function () {//成功执行
                submit();
            }
        });
        $('.forget-form input').keypress(function (e) {
            if (e.which == 13) {
                $('.emailTips').html('');
                if ($('.forget-form').validate().form()) {
                    submit();
                }
                return false;
            }
        });
    }
    /**
     *submit为提交用户名和邮箱地址
     */
    var submit = function () {
        var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
        var username = encrypt.encrypt($.trim($('input[name=username]').val()));
        var email = encrypt.encrypt($('input[name=email]').val().toLowerCase());
        var data = {username:username,email:email};
        $("#modalShow").modal({
            backdrop: "static",//点击空白处不关闭对话框
            show:true
        });
        $('#modalShow').modal('show');
        pAjaxRequest(data,'/api/v1/users/verify/email','GET',function (d){
            $('#modalShow').modal('hide');
            var data = d.data;
            var tip = '';
            $('.usernameTips').html('');
            $('.emailTips').html('');
            switch (data.result) {
                case 0:
                    //   用户未配置邮箱
                    tips = LANG.UI_FORGETPASSWORD_TIPS_NO_EMAIL;
                    $('.form-group').addClass('has-error');
                    $('.emailTips').html(tips).css({
                        color: '#FC4850'
                    });
                    $('.usernameTips').html('');
                    break;
                case 1:
                    //  成功发送邮件
                    $('.emailTips').html('');
                    var showPage = 'send_email';
                    window.location.href = "./message_page.php?show="+showPage;
                    break;
                case 2:
                    //   用户名不存在
                    tips = LANG.UI_FORGETPASSWORD_TIPS_USERNAME_NOTEXIST;
                    $('.form-group').addClass('has-error');
                    $('.usernameTips').html(tips).css({
                        color: '#FC4850'
                    });
                    break;
                case 3:
                    //   用户名和邮箱不匹配
                    tips = LANG.UI_FORGETPASSWORD_TIPS_DIFFERENT_EMAIL;
                    $('.form-group').addClass('has-error');
                    $('.usernameTips').html('');
                    $('.emailTips').html(tips).css({
                        color: '#FC4850'
                    });
                    break;
                case 4:
                    //用户未配置邮件服务器
                    tips = LANG.UI_FORGETPASSWORD_TIPS_NO_CONFIGEMAIL;
                    $('.form-group').addClass('has-error');
                    $('.usernameTips').html(tips).css({
                        color: '#FC4850'
                    });
                    break;
                case 5:
                    //邮件服务器在配置的情况下，是否能正常发送邮件
                    tips = LANG.UI_FORGETPASSWORD_TIPS_SEND_EMAIL_FAILED;
                    $('.form-group').addClass('has-error');
                    $('.usernameTips').html(tips).css({
                        color: '#FC4850'
                    });
                    break;
                case 6:
                    //域用户不支持发送邮件找回密码
                    tips = LANG.UI_FORGETPASSWORD_TIPS_SEND_DOAMIN_FAILED;
                    $('.form-group').addClass('has-error');
                    $('.usernameTips').html(tips).css({
                        color: '#FC4850'
                    });
                    break;
            }
            $('input[name=email]').val('');
        });
    }
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
    return {
        init: function () {
            handleForgetPwd();
            toggleInput();
        }
    };
}();
$(function () {
    forgrtPwd.init();
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