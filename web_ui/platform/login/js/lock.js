var Lock = function () {
    var count = 1; //记录在登录页面输错密码的次数
    var page = 'lock';
    var addListners = function(){
        $('#login').on("click",function(){
            var psd = $('#psd').val();
            var username = $('#username').text();
            if(psd == ''){
                $('.errorTip').html('');
                $('.alertMsg').show();
                $('.lockTips').hide();
                return;
            }
            submit(username, psd,page,count);
        });
        $('#psd').keypress(function (e) {
            if (e.which == 13) {
                submit($('#username').text(), $('#psd').val(),page,count);
                return false;
            }
        });
        //切换到大写给出提示
        //检测键盘事件
        $('#psd').on('keyup', function(e) {
            const capslockWarning = document.getElementById('capslock-warning');
            // 获取原生事件对象
            const originalEvent = e.originalEvent || e;

            if (originalEvent.getModifierState && originalEvent.getModifierState('CapsLock')) {
                capslockWarning.style.display = 'block';
            } else {
                capslockWarning.style.display = 'none';
            }
        });
        // 当输入框失去焦点时隐藏警告
        $('#psd').on('blur', function() {
            const capslockWarning = document.getElementById('capslock-warning');
            capslockWarning.style.display = 'none';
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
        var remember = $('.remember-password').attr('data-remember') === '1';
        let data = {};
        data['username'] = username;
        data['password'] = password;
        data['page'] = page;
        data['count'] = count;
        data['remember'] = remember;
        pAjaxRequest(data, '/api/v1/login', 'post', loginResult);
    }

    var loginResult = function(data){
        // var loginResult = JSON.parse(data);
        //退出登录，移除exchange用于恢复页面验证身份的session
        sessionStorage.removeItem('exchange_recovery_pass');
        var loginResult = data.data;
        if(loginResult.lockCount == '3'){
            window.location.href="/login";
        }
        var tips = '';
        if(1 == data.message){
            //login success
            window.location = '/';
            return;
        }else if(5 == data.message){
            tips = LANG.UI_LOGIN_USER_LOGIN_OVERTIME;
        } else{
            count += 1;
            tips = LANG.UI_LOCK_ERROR_TIPS;
            $('.alertMsg').hide();
            $('.lockTips').show();
            $('.input-icon').addClass('has-error');

        }
        $('.errorTip').html(tips);
        $('#psd').val('');
    }

    //如果session已清除,需要重新登录
    var checkSessionExist = function(){
        var lang = $('.systemLang').val();
        if(lang == ""){
            window.location.href="/login";
        }
    }
    //
    var passwordToggle=function(){
        $('.input-icon .capslock-icon').hide();
        //默认密码是不可见的
        $('.input-icon .eye').hide();
        $('.input-icon .eye-close').click(function () {
            $('.input-icon .eye-close').hide();
            $('.input-icon .eye').show();
            $('.passwordInput').attr('type','text');
        })
        $('.input-icon .eye').click(function () {
            $('.input-icon .eye').hide();
            $('.input-icon .eye-close').show();
            $('.passwordInput').attr('type','password');
        })
    }
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
            passwordToggle();
            toggleInput();
            alertToggle();
        }
    };
}();

jQuery(document).ready(function() {
    Lock.init();
});