<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description"/>
    <meta content="vinchin.com" name="author"/>
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/css/gfonts1.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN THEME STYLES -->
    <link href="/assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="/css/platform/main.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
    <link href="/css/platform/standard_login.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<input type="text" class="systemLang display-none" value="<?php echo $_SESSION['language']?>">
<div class="content">
    <div class="login-content_div">
        <div class="backupImg">
            <?php
            if($lang == "zh-cn" || $lang == "zh-tw"){
                echo '<img src="./img/platform/login/standard_login/backup.svg" alt="">';
            }
            else{
                echo '<img src="./img/platform/login/standard_login/backup_en.svg" alt="">';
            }
            ?>
        </div>
        <div class="login">
            <div class="logo">
                <a href="<?php echo $app['REMOTE']['website']?>" target="_black">
                    <?php
                    if($app['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType){
                        //如果是免费版,使用专用logo
                        echo '<img src="./img/platform/logo-free.png" alt="" class="logo-default loginlogosizefree">';
                    }else{
                        //如果是其他版本
                        if(file_exists($app['SPECIAL_DIR'] . "logo.png")){
                            $logo = "/special/logo.png";
                        }else{
                            $logo = "/img/platform/login/standard_login/vinchin_logo.svg";
                        }
                        echo '<img src="' . $logo . '" alt="" class="logo-default loginlogosize">';
                    }
                    ?>
                </a>
            </div>
            <h3 class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></h3>
            <div class="lock-tip">
                <div class="lock"><img src="/img/platform/login/standard_login/lock.svg" alt=""></div>
                <div class="lock-user-tip">
                    <?php echo $LANG['UI_PUBLIC_USER']?>
                    <span id="username"><?php unset($_SESSION['userUUID']); echo $_SESSION['tenantusername'];?></span>
                    <?php echo $LANG['UI_LOCK_LOCKED']?>
                    <?php echo $LANG['UI_LOCK_INPUT_RELOGIN']?>
                </div>
            </div>
            <div class="alertMsg alert alert-danger my_alert" style="display: none">
                <button type="button" class="close" data-close="alert"></button>
                <span id="login_tip" style="font-size: 16px">
                    <?php echo $LANG['UI_LOCK_INPUT_PASSWORD']?>
                </span>
            </div>
            <form class="lock-form" action="./">
                <input type="password" autocomplete="new-password" hidden>
                <div class="form-group">
                    <div class="input-icon">
                        <i class="eye">
                            <img src="/img/platform/login/standard_login/eye.svg" alt="">
                        </i>
                        <i class="eye-close">
                            <img src="/img/platform/login/standard_login/eye-close.svg" alt="">
                        </i>
                        <i id="capslock-warning" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>">
                            <img src="/img/platform/login/standard_login/capslock.svg" alt="">
                        </i>
                        <input type="password" autocomplete="off" id="psd" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" maxlength="32"  class="form-control passwordInput" placeholder="<?php echo $LANG['UI_LOCK_INPUT_PASSWORD']?>">
                    </div>
                </div>
                <span class="errorTip help-block"></span>
                <div class="form-actions">
                    <button class="btn icn-only" type="button" id="login">
                        <?php echo $LANG['UI_LOGIN_BUTTON_LOGIN']?>
                    </button>
                    <div class="flex-center">
                        <a href="/login.php" class="return-login"><?php echo $LANG['UI_FORGETPWD_RETURNLOGIN']?></a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="copyright">
    <?php echo $app['SYSTEM_INFO']['copyright'] . " &copy; " . $app['SYSTEM_INFO']['years'] . " ".
        $app['SYSTEM_INFO']['company'] . $app['SYSTEM_INFO']['version'];
    ?>
    <p>
        <?php echo $app['SYSTEM_INFO']['recommend'];?>
    </p>
</div>
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<script src="/lang/<?php echo $_SESSION['language']; ?>.js" type="text/javascript"></script>
<script src="/scripts/conf/config.js" type="text/javascript"></script>
<script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="/scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="/scripts/libs/md5.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="/scripts/platform/lock.js"></script>
</body>
</html>