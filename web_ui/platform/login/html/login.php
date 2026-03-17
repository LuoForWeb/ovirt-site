<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <title data-i18n="WEB_PLATFORM_TOKEN_ERROR">云祺备份与恢复系统</title>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap-toastr/css/toastr.min.css" rel="stylesheet" type="text/css" />

    <!-- BEGIN THEME STYLES -->
    <link href="/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css" />
    <link href="/assets/admin/layout/css/component.css" rel="stylesheet" type="text/css" />
    <!-- END THEME STYLES -->

    <!-- BEGIN LOCAL STYLES -->

    <link href="/css/viconfont/iconfont.css" rel="stylesheet" type="text/css" />
    <link href="/css/main.css" rel="stylesheet" type="text/css" />
    <link href="/css/vincomponent/css/vincomponent.css" rel="stylesheet" type="text/css" />
    <link href="/css/revision.css" rel="stylesheet" type="text/css" />
    <!-- END LOCAL STYLES -->
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
    <link href="/css/lang/zh-cn.css" rel="stylesheet" type="text/css"/>
</head>
<body>
    <div class="" style="height: 100vh">
        <?php
            $bgImg = '/platform/login/img/left_bg.svg';
            $cardStyle = 'cardLeft';
            $ContentStyle = 'leftLayOut-left-content';
            $loginContentStyle = 'login-content';
            $path = 'login_version/login_professional/login.php';
            include_once $path;
         ?>
    </div>
    <div id="agent_info" style="display: none;"></div>
    <input type="hidden" class="login_faild_num" value="<?php echo !empty($_SESSION['login_faild_num']) ? $_SESSION['login_faild_num'] : 0 ?>" />
    <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-blockUI/jquery.blockUI.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jquery-validation/js/localization/messages-zh-cn.js" type="text/javascript"></script>

    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
    <script src="/assets/admin/layout/scripts/vinchinui.js" type="text/javascript"></script>
    <script src="/assets/admin/layout/scripts/layout.js" type="text/javascript"></script>

    <script src="/js/config.js" type="text/javascript"></script>

    <!-- 核心i18n库 -->
    <script src="/js/libs/i18n-core.js" defer></script>
    <script src="/js/libs/ajax-i18n.js" defer></script>

    <script src="/js/public.js" type="text/javascript"></script>
    <script src="/js/libs/json2.min.js" type="text/javascript"></script>
    <script src="/js/libs/base64.min.js" type="text/javascript"></script>
    <script src="/js/libs/md5.js" type="text/javascript"></script>
    <script src="/platform/login/js/code.js"></script>
    <script src="/platform/login/js/login.js" type="text/javascript"></script>
    <script>

        function refresh_code()
        {
            $('.code-canvas').attr('src', '/platform/login/html/code.php?r='+Math.round(new Date().getTime()) );
        }

    </script>
</body>
</html>
