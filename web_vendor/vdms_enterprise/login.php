<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/web_ng/api/public/load.php';
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$app = xphp_get_config('app', '', '', true);

if ($_SESSION['language'] != null) {
    $LANG = require $rootPath . '/lang/' . $_SESSION['language'] . $app['ext'];
    $lang = $_SESSION['language'];
} else {
    $LANG = require $rootPath . '/lang/' . $app['lang'] . $app['ext'];
    $lang = $app['lang'];
}

$agent_info = (new \app\v1\resources\v0\logic\Client())->getDoloadAgentName([]);

$token = json_decode(v1_decrypt($_COOKIE['Token']), true);
// echo $_COOKIE;die;
$remember = $token['remember'];
$username = $token['username'];
$password = $token['password'];
// print_r($app);die;

/**
 * 获取页面版本
 */
$loginUrl = $app['LOGIN_INFO']['login_url'];
$loginLayOut = $app['LOGIN_INFO']['login_layout'];
?>

<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>
        <?php
        if (file_exists($app['SYSTEM_NAME_FILE'])) {
            //如果自定义系统名称存在
            echo file_get_contents($app['SYSTEM_NAME_FILE']);
        } else {
            //如果自定义系统名称不存在
            echo $app['SYSTEM_INFO']['system_name'];
        }
        ?>
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="./assets/global/css/gfonts1.css" rel="stylesheet" type="text/css" />
    <link href="./assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="./assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="./assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="./css/platform/main.css" rel="stylesheet" type="text/css" />
    <link href="./assets/global/plugins/select2/select2.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL SCRIPTS -->
    <!-- BEGIN THEME STYLES -->
    <link href="./assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css" />
    <link href="./assets/global/css/plugins.css" rel="stylesheet" type="text/css" />
    <link href="./assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css" />
    <link id="style_color" href="./assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" />
    <link href="./assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css" />
    <link href="./css/platform/loginbackground.css" rel="stylesheet" type="text/css" />
    <link href="./css/viconfont/iconfont.css" rel="stylesheet" type="text/css" />
    <link href="./css/platform/login-divs.css" rel="stylesheet" type="text/css" />
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico" />
    <style type="text/css">

    </style>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->

<body class="login">
<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
<div class="menu-toggler sidebar-toggler">
</div>
<!-- END SIDEBAR TOGGLER BUTTON -->
<!-- BEGIN LOGIN -->
<div>
    <div class="mask"
         style="width: 100%;height: 100%;position: absolute;top: 0;right: 0;z-index:1;opacity: 0.9;background:linear-gradient(to top , rgba(219,248,255,0.9) 0%, #FFFFFF 53%)">
    </div>
    <div class="content" style="position:absolute;width: 100%;height: 100%;top: 0;right: 0;background-image: url('./img/platform/bg-home-cics.png');filter: blur(5px);
            background-size: cover;">
        <!-- BEGIN LOGO -->
        <div class="contentlogin">
            <!-- END LOGO -->
            <!-- BEGIN LOGIN FORM -->

            <!-- END LOGIN FORM -->
        </div>
        <div class="contentpakage display-hide">
            <!-- BEGIN DOWNLOAD AGENT FORM -->

            <!-- END DOWNLOAD AGENT FORM -->
        </div>
        <!-- END LOGIN -->

    </div>

    <div class="login-div" style="display: flex;width: 1000px;margin: 0 auto;background: #fff;">
        <!-- 自定义logo -->
        <img class="custom-logo display-none" style="width: 150px;height: 50px" src="">

        <div style="z-index: 2;border-radius: 20px !important;box-shadow: 0px 4px 9px 0px rgba(7, 61, 106, 0.24);">
            <img src="./img/platform/login-cics.png" alt="" style="z-index: 2;">
        </div>

        <form class="login-form " action="index.html" method="post">
            <div class="logo">
                <img src="/img/platform/logo.png" alt="" class="logo-default loginlogosize">
            </div>
            <h3 class="form-title">
                <?php echo $app['SYSTEM_INFO']['system_name'] ?></h3>
            <div class="edit-alert display-hide" style="position: absolute; color:#a94442; top: 5px; right: 35%;">
                    <span id="edittips">
                        <?php echo $LANG['UI_LOGIN_PASSWORD_EDIT_TIPS'] ?></span>
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                <div class="input-icon">
                    <i style="color: #2A87C8;font-size: 20px;" class="viconfont vicon-yonghuming"></i>
                    <input
                            style="border-radius: 4px !important;height: 48px;padding-left: 48px !important;width: 400px;"
                            class="form-control placeholder-no-fix" value="<?php echo $username ?>" maxlength="64"
                            type="text" autocomplete="off" placeholder="请输入用户名" name="username" />
                </div>
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <div class="input-icon">
                    <i class="viconfont vicon-mima lock" style="color: #2A87C8;font-size: 20px;"></i>
<!--                    <i class="eye viconfont vicon-xianshimima" style="display: none;right: 16px;color: #EEEFF2;font-size: 20px;">-->
<!--                    </i>-->
<!--                    <i class="eye-close viconfont vicon-yincangmima" style="right: 16px;color: #EEEFF2;font-size: 20px;">-->
<!--                    </i>-->
                    <input class="form-control placeholder-no-fix passwordInput" value="<?php echo $password ?>"
                           maxlength="32" type="password"
                           oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off"
                           placeholder="请输入密码" name="password"
                           style="padding-left: 48px;border-radius: 4px !important;height: 48px;width: 400px;"
                           aria-invalid="false">
                </div>

            </div>
            <div class="form-actions Code" style="display: none;margin-bottom:24px;">
                <div id="loginCode" class="code login-code">
                    <div class="input-icon" style="width: 262px;height:48px;display: inline-block;">
                        <i class="viconfont vicon-yanzhengma" style="color: #2A87C8;font-size: 20px;margin-top: 12px;"></i>
                        <input
                                style="width: 244px;border-radius: 4px !important;height: 48px;padding-left: 48px !important;"
                                class="form-control placeholder-no-fix input-val" maxlength="4" autocomplete="off"
                                name="very_code" placeholder="请输入验证码" />
                    </div>
                    <a href="javascript:refresh_code();">
                        <img src="./code.php" class="code-canvas" style="float: right;width:140px;height:48px">
                    </a>
                </div>
            </div>
            <div class="login-alert" style="width:400px;padding-left:0px;font-weight: 400;font-size: 12px;color: #EC282C;">
                <span id="login_tip"></span>
            </div>
            <div class="form-actions" style="margin-top: 12px;margin-bottom:24px">
                <button type="submit" class="btn login-btn">
                    登 录
                </button>
            </div>

            <div class="form-actions actionLine col-md-10 mt25"
                 style="display: flex;justify-content: space-between;padding:0;width: 400px;">
                <label class="checked" style="display:none;margin-top: 0px;">
                    <input class="remPwd" name="remember" data-check="<?php echo $remember ?>" type="checkbox"
                           style="width: 16px;margin-left: 0px;height: 16px;margin-top: 1px;accent-color: #2A87C8;" />
                    <h4
                            style="margin-left: 8px;margin-top: 0px;color: rgba(45, 55, 72, 0.80);font-weight: 400;font-size: 12px;display: inline-block;position:relative;top:-3px">
                        <?php echo $LANG['UI_LOGIN_REMEMBER_USER'] ?>
                    </h4>
                </label>
                <div class="forget-password" style="float:right;margin-top:0 !important;">
                    <a class="forget" href="./forget_pwd.php" style="color: #2A87C8">
                        <h4
                                style="font-size: 12px;color: #2A87C8;font-weight: 400;text-align:right;margin-top: 2px;margin-bottom: 0px;padding-left: 6px;">
                            <?php echo $LANG['UI_LOGIN_FORGET_PASSWORD'] ?>
                    </a>
                </div>
            </div>
        </form>

        <div class="overlay" style="display: none">
            <div id="modal">
                <p><?php echo $LANG['UI_LOGIN_SUGGEST_EDIT_PASSWORD_TIP'] ?></p>
                <div class="buttonAction">
                    <button class="edit_pwd_button login-btn-font_en login-btn-font_cn"><?php echo $LANG['UI_PLATFORM_EDIT_PASSWORD'] ?></button>
                    <button class="login_success_button login-btn-font_en login-btn-font_cn"><?php echo $LANG['UI_LOGIN_DIRECT_LOGIN'] ?></button>
                </div>
            </div>
        </div>

        <!-- BEGIN COPYRIGHT -->
        <div class="copyright">
            <p style="font-weight: 400;font-size: 12px;color: #666666;line-height: 14px;text-align: center">
                <?php echo $app['SYSTEM_INFO']['copyright'] . " &copy; " . $app['SYSTEM_INFO']['years'] . " " .
                    $app['SYSTEM_INFO']['company'] . $app['SYSTEM_INFO']['version'];
                ?>
            </p>
        </div>

        <!-- END COPYRIGHT -->
    </div>
</div>
<div id="agent_info" style="display: none;"><?php echo $agent_info; ?></div>
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="./assets/global/plugins/respond.min.js"></script>
<script src="./assets/global/plugins/excanvas.min.js"></script>
<![endif]-->

<script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.history.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-validation/js/localization/messages_zh.js"
        type="text/javascript"></script>
<script src="./assets/global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/select2/select2.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="./lang/<?php echo $lang ?>.js" type="text/javascript"></script>
<script src="./scripts/conf/config.js" type="text/javascript"></script>
<script src="./assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="./scripts/public/public.js" type="text/javascript"></script>
<script src="./scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="./scripts/libs/base64.min.js" type="text/javascript"></script>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="./scripts/platform/code.js"></script>
<script src="./scripts/platform/login.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<!-- END JAVASCRIPTS -->
<script>
    pAjaxRequest({}, '/api/v1/system/setting', 'GET', (res)=>{
        setCustomLogo(res);
    });

    var setCustomLogo = function (res) {
        if (res.data.flag == false || res.data.logo === undefined || res.data.logo == '') {
            $('.custom-logo').hide();
        } else {
            $('.custom-logo').attr('src', res.data.logo);
            $('.custom-logo').show();
        }
    }
</script>
<script>

    function refresh_code() {
        $('.code-canvas').attr('src', './code.php?r=' + Math.round(new Date().getTime()));
    }
</script>
</body>
<!-- END BODY -->

</html>