var Lock = function () {
    var count = 1; //记录在登录页面输错密码的次数
    var page = 'lock';
    var addListners = function(){
        $('#login').on("click",function(){
            var psd = $('#psd').val();
            var username = $('#username').text();
            if(psd == ''){
                $('.pwd-div').addClass('has-error');
                $('.error').html('请输入密码');
                return;
            }
            submit(username, psd,page,count);
        })
        $('#psd').keypress(function (e) {
            if (e.which == 13) {
                submit($('#username').text(), $('#psd').val(),page,count);
                return false;
            }
        });
    }
    var submit = function(username, password,page,count){
        var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
        var username = encrypt.encrypt(username);
        var password = encrypt.encrypt(password);
        var page = encrypt.encrypt(page);
        var count = String(count);
        count = encrypt.encrypt(count);
        //var data = JSON.stringify({username:username,password:password,page:page,count:count});
        let data = {};
        data['username'] = username;
        data['password'] = password;
        data['page'] = page;
        data['count'] = count;
        pAjaxRequest(data, '/api/v1/login', 'post', loginResult);
    }

    var loginResult = function(data){
        // var loginResult = JSON.parse(data);
        //退出登录，移除exchange用于恢复页面验证身份的session
        sessionStorage.removeItem('exchange_recovery_pass');
        var loginResult = data.data;
        if(loginResult.lockCount == '3'){
            window.location.href="/login.php";
        }
        var tips = '';
        if(1 == data.message){
            //login success
            window.location = './';
            return;
        }else if(5 == data.message){
            tips = LANG.UI_LOGIN_USER_LOGIN_OVERTIME;
        } else{
            count += 1;
            tips = LANG.UI_LOCK_ERROR_TIPS;
            $('.pwd-div').addClass('has-error');
        }
        $('.error').html(tips);
        $('#psd').val('');
    }

    //如果session已清除,需要重新登录
    var checkSessionExist = function(){
        var lang = $('.systemLang').val();
        if(lang == ""){
            window.location.href="/login.php";
        }
    }
    //
    // var passwordToggle=function(){
    //     //默认密码是不可见的
    //     $('.input-icon .eye').hide();
    //     $('.input-icon .eye-close').click(function () {
    //         $('.input-icon .eye-close').hide();
    //         $('.input-icon .eye').show();
    //         $('.passwordInput').attr('type','text');
    //     })
    //     $('.input-icon .eye').click(function () {
    //         $('.input-icon .eye').hide();
    //         $('.input-icon .eye-close').show();
    //         $('.passwordInput').attr('type','password');
    //     })
    // }
    var alertToggle = function () {
        $('.close').on('click',function () {
            $('.my_alert').hide();
            $('.lockTips').show();
        })
    }
    var toggleInput=function () {
        $('.passwordInput').focus();
        $('.passwordInput').siblings().addClass('focusIcon');
        $('.input-icon .form-control').focus(function () {
            $(this).siblings().addClass('focusIcon');
            $(this).addClass('focusInput')
        })
        $('.input-icon .form-control').blur(function () {
            $(this).siblings().removeClass('focusIcon');
            $(this).removeClass('focusInput')
        })

    }
    return {
        //main function to initiate the module
        init: function () {
            addListners();
            checkSessionExist();
            // passwordToggle();
            toggleInput();
            alertToggle();
        }
    };
}();

jQuery(document).ready(function() {
    Lock.init();
});