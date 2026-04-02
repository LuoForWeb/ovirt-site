<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
    <link rel="shortcut icon" href="favicon.ico"/>
    <link rel="stylesheet" href="/css/platform/project_login.css">
</head>
<body style="background-image: url(<?php echo $bgImg ?>)">
<div style="height: 100vh">
    <div class="<?php echo $loginContentStyle?>">
        <div class="<?php echo $cardStyle?>">
            <div class="<?php echo $ContentStyle?>">
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
                                $logo = "/img/platform/login/logo.svg";
                            }
                            echo '<img src="' . $logo . '" alt="" class="logo-default loginlogosize">';
                        }
                        ?>
                    </a>
                </div>
                <input type="text" class="systemLang display-none" value="<?php echo $_SESSION['language']?>">
                <div class="send-success-content">
                        <div class="lock_div">
                            <div class="lock-title">
                                <div class="lock-img">
                                    <img src="/img/platform/login/lock.svg" alt="">
                                </div>
                                <p class="lock-img-title"><?php echo $LANG['UI_LOCK_USER_LOCKED']?></p>
                            </div>
                            <div class="lockTips">
                        <span>
                            <?php echo $LANG['WEB_USERS_USER']?><span id="username"><?php unset($_SESSION['userUUID']); echo $_SESSION['tenantusername'];?></span>
                            <?php echo $LANG['UI_LOCK_INPUT_PASSWORD_TIPS']?>
                        </span>
                            </div>
                            <div class="alertMsg alert alert-danger my_alert" style="display: none">
                                <button type="button" class="close" data-close="alert"></button>
                                <span id="login_tip" style="font-size: 16px">
                            <?php echo $LANG['UI_LOCK_INPUT_PASSWORD']?>
                        </span>
                            </div>
                            <div class="form-group">
                                <div class="input-icon">
                                    <i class="viconfont vicon-a-lock lock"></i>
                                    <i class="eye">
                                        <img src="/img/platform/login/eye.svg" alt="">
                                    </i>
                                    <i class="eye-close">
                                        <img src="/img/platform/login/eye-close.svg" alt="">
                                    </i>
                                    <i id="capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                                    <input id="psd" class="form-control placeholder-no-fix passwordInput"
                                           value="<?php echo $password ?>"
                                           maxlength="32" type="password"
                                           oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off"
                                           placeholder="<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>" name="password"/>
                                    <div class="errorTip"></div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="lock-actions-div">
                                    <button type="submit" id="login">
                                    <?php echo $LANG['UI_LOCK_LOGIN'] ?>
                                    </button>
                                    <div class="login-line">
                                        <span class="line"></span>
                                        <a href="/login.php"><?php echo $LANG['UI_FORGETPWD_RETURNLOGIN']; ?></a>
                                        <span class="line"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
</div>
</div>
<footer class="copyright">
    <?php echo $app['SYSTEM_INFO']['copyright'] . " &copy; " . $app['SYSTEM_INFO']['years'] . " ".
        $app['SYSTEM_INFO']['company'] . $app['SYSTEM_INFO']['version'];
    ?>
    <p>
        <?php echo $app['SYSTEM_INFO']['recommend'];?>
    </p>
</footer>
</body>
<script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.history.min.js" type="text/javascript"></script>
<script src="/scripts/platform/lock.js"></script>
</html>

