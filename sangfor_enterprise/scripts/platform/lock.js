var Lock = function () {
    var count = 1; //记录在登录页面输错密码的次数
    var page = 'lock';
    var addListners = function () {
        $('#login').on("click", function () {
            var psd = $('#psd').val();
            var username = $('#username').text();
            if (psd == '') {
                $('.input-icon').removeClass('has-error');
                $('.form-group').addClass('has-error');
                $('.errorTip').html('密码不能为空');
                return;
            }
            submit(username, psd, page, count);
        })
        $('#psd').keypress(function (e) {
            if (e.which == 13) {
                submit($('#username').text(), $('#psd').val(), page, count);
                return false;
            }
        });
    }
    var submit = function (username, password, page, count) {
        var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
        var username = encrypt.encrypt(username);
        var password = encrypt.encrypt(password);
        var page = encrypt.encrypt(page);
        var count = String(count);
        count = encrypt.encrypt(count);
        var remember = $('.remember-password').attr('data-remember') === '1';
        var data = {username: username, password: password, page: page, count: count,remember: remember};
        pAjaxRequest(data, '/api/v1/login', 'post', loginResult);
    }

    var loginResult = function (data) {
        // var loginResult = JSON.parse(data);
        //退出登录，移除exchange用于恢复页面验证身份的session
        sessionStorage.removeItem('exchange_recovery_pass');
        var loginResult = data.data;
        if (loginResult.lockCount == '3') {
            window.location.href = "/login.php";
        }
        var tips = '';
        if (1 == data.message) {
            //login success
            window.location = './';
            return;
        } else if(5 == data.message) {
            tips = LANG.UI_LOGIN_USER_LOGIN_OVERTIME;
        } else {
            count += 1;
            tips = LANG.UI_LOCK_ERROR_TIPS;
            $('.input-icon').addClass('has-error');

        }
        $(' .errorTip').html(tips);
        $('#psd').val('');
    }

    //如果session已清除,需要重新登录
    var checkSessionExist = function () {
        var lang = $('.systemLang').val();
        if (lang == "") {
            window.location.href = "/login.php";
        }
    }
    //密码是否可见
    var passwordToggle = function () {
        //默认密码是不可见的
        $('.input-icon .pwdIcon').click(function () {
            if ($('.passwordInput').attr('type') == 'password') {
                $('.passwordInput').attr('type', 'text');
            } else {
                $('.passwordInput').attr('type', 'password');
            }
        })
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
        //main function to initiate the module
        init: function () {
            addListners();
            checkSessionExist();
            passwordToggle();
            toggleInput();
        }
    };
}();

jQuery(document).ready(function () {
    Lock.init();
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
});