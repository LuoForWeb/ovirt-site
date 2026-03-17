<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <?php
    /**
     * 获取页面布局
     */
    $loginLayOut = $app['LOGIN_INFO']['login_layout'];

    /**
     * 背景
     */
    //布局
    switch ($loginLayOut){
        case 'leftLayout':
            $bgImg = '/img/platform/login/left_bg.svg';
            break;
        case 'mediumLayout':
            $bgImg = '/img/platform/login/medium_bg.svg';
            break;
        case 'rightLayout':
            $bgImg = '/img/platform/login/right_bg.svg';
            break;
    }
    // 用户上传背景
    $fileNames = array(
        $_SERVER['DOCUMENT_ROOT'].'/img/platform/login/custom_login_bg.svg',
        $_SERVER['DOCUMENT_ROOT'].'/img/platform/login/custom_login_bg.jpg',
        $_SERVER['DOCUMENT_ROOT'].'/img/platform/login/custom_login_bg.png'
    );
    foreach ($fileNames as $fileName){
        if(file_exists($fileName)){
            // 用户上传的图片存在
            $bgImg = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fileName);
            break;
        }
    }
    // 获取名为 "show" 的参数的值
    $show = $_GET['show'] ?? '';
    ?>
    <title>
        <?php
        if (file_exists($app['SYSTEM_NAME_FILE'])) {
            // 如果自定义系统名称存在
            echo file_get_contents($app['SYSTEM_NAME_FILE']);
        } else {
            // 如果自定义系统名称不存在
            echo $app['SYSTEM_INFO']['system_name'];
        }
        ?>
    </title>
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
    <link rel="shortcut icon" href="/favicon.ico"/>
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
                    <form class="modifyPwd-form" action="index.html" method="post">
                        <div class="welcome-login-tip"><span class="welcome-tip"><?php echo $LANG['UI_RESET'];?></span><?php echo $LANG['UI_LOGIN_PASSWORD'];?></div>
                        <div class="form-title"><?php echo $app['SYSTEM_INFO']['system_name'] ?></div>
                        <div class="alert first-edit edit-alert" style="display: <?php echo $show == 'first_login' ? 'block' : 'none'; ?>">
                            <p><?php echo $LANG['UI_PUBLIC_USER'];?><?php echo $username ?><?php echo $LANG['UI_FORCED_MODIFY_FIRST_TIPS'];?></p>
                        </div>
                        <div class="alert overtime-edit edit-alert" style="display: <?php echo $show == 'over_time' ? 'block' : 'none'; ?>">
                            <p><?php echo $LANG['UI_FORCED_MODIFY_TIPS'];?></p>
                        </div>
                        <div class="form-group">
                            <div class="form-input form-oldpassword">
                                <i class="viconfont vicon-a-lock lock"></i>
                                <i class="eye">
                                    <img src="/img/platform/login/eye.svg" alt="">
                                </i>
                                <i class="eye-close">
                                    <img src="/img/platform/login/eye-close.svg" alt="">
                                </i>
                                <i id="old-capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                                <input class="form-control placeholder-no-fix passwordInput toggle-oldpassword" value="<?php echo $password ?>"
                                       id="oldpassword" maxlength="32" type="password"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off"
                                       placeholder="<?php echo $LANG['UI_USER_OLD_PASS']; ?>" name="oldpass"/>
                                <span class="errorTipOldPass"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-input form-password">
                                <i class="viconfont vicon-a-lock lock"></i>
                                <i class="eye">
                                    <img src="/img/platform/login/eye.svg" alt="">
                                </i>
                                <i class="eye-close">
                                    <img src="/img/platform/login/eye-close.svg" alt="">
                                </i>
                                <i id="new-capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                                <input class="form-control placeholder-no-fix passwordInput toggle-password" value="<?php echo $password ?>"
                                       id="password" maxlength="32" type="password"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off"
                                       placeholder="<?php echo $LANG['UI_USER_NEW_PASS']; ?>" name="password"/>
                                <span class="passwordRule"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-input form-repassword">
                                <i class="viconfont vicon-a-lock lock"></i>
                                <i class="eye">
                                    <img src="/img/platform/login/eye.svg" alt="">
                                </i>
                                <i class="eye-close">
                                    <img src="/img/platform/login/eye-close.svg" alt="">
                                </i>
                                <i id="re-capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                                <input class="form-control placeholder-no-fix passwordInput toggle-repassword" value="<?php echo $password ?>"
                                       id="newPassword" maxlength="32" type="password" name="newPassword"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off"
                                       placeholder="<?php echo $LANG['UI_RESETPWD_REENTER_NEWPASSWORD']; ?>" name="newPassword"/>
                                <span class="errorTip"></span>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="submit_div">
                                <button type="submit" id="editsubmit" class="btn login-btn">
                                    <?php echo $LANG['UI_CONFIRM']; ?>
                                </button>
                            </div>
                        </div>
                    </form>
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
</html>

