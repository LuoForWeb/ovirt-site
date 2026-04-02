<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <link rel="stylesheet" href="css/platform/project_login.css">
    <link rel="shortcut icon" href="favicon.ico"/>
    <?php
        if(!empty($lang)){
            if($lang == "zh-cn" || $lang == "zh-tw"){
                echo '<link href="./css/platform/lang/zh-cn.css" rel="stylesheet" type="text/css"/>';
            }
            else{
                echo '<link href="./css/platform/lang/en-us.css" rel="stylesheet" type="text/css"/>';
            }
        }
    ?>
</head>
<body style="background-image: url(<?php echo $bgImg ?>);background-attachment:fixed;">
<div class="<?php echo $loginContentStyle?>">
<div class="<?php echo $cardStyle?>">
    <div class="<?php echo $ContentStyle?>">
        <div class="logo" style="display: <?php echo ($ContentStyle == 'mediumLayOut-medium-content') ? 'none' : 'block'; ?>">
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
        <form class="login-form" action="index.html" method="post">
            <div class="welcome-login-tip" style="display: <?php echo ($ContentStyle == 'mediumLayOut-medium-content') ? 'none' : 'block'; ?>">
            <span class="welcome-tip"><?php echo $LANG['UI_LOGIN_WELCOME'] ?></span><?php echo $LANG['UI_LOGIN_LOGIN'] ?>
            </div>
            <div class="logoMedium" style="display: <?php echo ($ContentStyle == 'mediumLayOut-medium-content') ? 'block' : 'none'; ?>">
                <img src="/img/platform/login/logo.svg" alt="">
            </div>
            <div class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></div>
            <div class="alert display-hide my_alert">
                <button type="button" class="close" data-close="alert" onclick="$('.my_alert').hide();"></button>
                <span class="login-tip">
                     <?php echo $LANG['UI_LOGIN_TIP_USERNAME_AND_PASSWORD'] ?>
            </span>
            </div>
            <div class="form-group">
                <div class="input-icon">
                    <i class="viconfont vicon-a-user user"></i>
                    <input class="form-control placeholder-no-fix username" value="<?php echo $username ?>" maxlength="64"
                           type="text" autocomplete="off" placeholder="<?php echo $LANG['UI_LOGIN_USERNAME'] ?>"
                           name="username"/>
                </div>
            </div>
            <div class="form-group">
                <div class="input-icon">
                    <i class="viconfont vicon-a-lock lock"></i>
                    <i class="eye">
                        <img src="./img/platform/login/eye.svg" alt="">
                    </i>
                    <i class="eye-close">
                        <img src="./img/platform/login/eye-close.svg" alt="">
                    </i>
                    <i id="capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                    <input class="form-control placeholder-no-fix passwordInput" value="<?php echo $password ?>"
                           maxlength="32" type="password"
                           oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off"
                           placeholder="<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>" name="password"/>
                </div>
            </div>
            <div class="form-actions">
                <div class="code login-code">
                    <input type="text" name="very_code" value="" placeholder="<?php echo $LANG['UI_LOGIN_ENTER_CODE'] ?>"
                           class="input-val" autocomplete="off"/>
                    <a href="javascript:refresh_code();">
                        <img src="./code.php" class="code-canvas">
                    </a>
                </div>
                <div class="checkbox">
                <span class="rememberLeft">
                    <i class="remember-true" hidden data-checked="<?php echo $remember; ?>">
                        <img src="./img/platform/login/rememberTrue.svg">
                    </i>
                    <i class="remember-false">
                        <img src="./img/platform/login/rememberFalse.svg" alt="">
                    </i>
                    <span class="remember-pwd">
                        <?php echo $LANG['UI_LOGIN_REMEMBER_USER'] ?>
                    </span>
                </span>
                    <span class="forget-pwd">
                    <a href="/login_version/login_project/forget_pwd.php"><?php echo $LANG['UI_LOGIN_FORGET_PASSWORD'] ?></a>
                </span>
                </div>
                <div class="submit_div">
                    <button type="submit" class="btn login-btn">
                        <?php echo $LANG['UI_LOGIN_BUTTON_LOGIN'] ?>
                    </button>
                </div>
                <div class="login-line">
                    <?php
                    if ($lang == "zh-cn" || $lang == "zh-tw") {
                        echo '<span class="line"></span>';
                    } else {
                        echo '<span class="line"></span>';
                    }
                    ?>
                    <a href="javascript:;" class="download-agent">
                        <?php echo $LANG['UI_LOGIN_DOWNLOAD_AGENT'] ?>
                    </a>
                    <?php
                    if ($lang == "zh-cn" || $$lang == "zh-tw") {
                        echo '<span class="line"></span>';
                    } else {
                        echo '<span class="line"></span>';
                    }
                    ?>
                </div>
            </div>
        </form>
        <div class="alert display-hide nosupporttips" style="text-align: center;margin-top: 15px">
            <button type="button" class="close" data-close="alert" onclick="$('.nosupporttips').hide();">s</button>
            <span style="font-size: 16px">
            <?php echo $LANG['UI_LOGIN_AGENT_NOSUPPORT']?>
        </span>
        </div>
        <div class=" contentpakage display-hide">
            <form class="agent-form form-horizontal" action="index.html" method="post" style="position: relative">
                <div>
                    <div class="agent-type"> <?php echo $LANG['UI_LOGIN_AGENT_TYPE']?>
                    </div>
                    <div>
                        <select class="form-control agenttype-select">
                        </select>
                    </div>
                </div>
                <div class="vmagent">
                    <div class="divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_VENDOR']?>
                    </div>
                    <div style="width: 394px;">
                        <select class="form-control vendor_select">
                        </select>
                    </div>
                </div>
                <div class="vmagent display-hide vmagent_div">
                    <div class="divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION']?>
                    </div>
                    <div>
                        <select class="form-control version_select">
                        </select>
                    </div>
                </div>
                <!--                        操作系统-->
                <div class="osagent display-hide">
                    <div class=" divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM']?>
                    </div>
                    <div style="width: 394px;">
                        <select class="form-control ossystem_select">
                        </select>
                    </div>
                </div>
                <!--                        版本和架构-->
                <div class="fsagent display-hide">
                    <div class=" divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION_ARC']?>
                    </div>
                    <div style="width: 510px;">
                        <select class="form-control filesystem_select">
                        </select>
                    </div>
                </div>
                <div class="dbagent display-hide">
                    <div class="divcontrol"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?>
                    </div>
                    <div style="width: 394px;">
                        <select class="form-control dbtimingClient_select">
                        </select>
                    </div>
                </div>
                <div class="dbcdp display-hide" >
                    <div class="divcontrol"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?>
                    </div>
                    <div style="width: 394px;">
                        <select class="form-control dbcdpClient_select">
                        </select>
                    </div>
                </div>
                <div class="dbprotectagent display-hide">
                    <div class="divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM']?>
                    </div>
                    <div style="width: 394px;">
                        <select class="form-control dbprotectsystem_select">
                        </select>
                    </div>
                </div>
                <button type="button" class="download_btn download_btn_project">
                    <i class="down-load-icon viconfont vicon-download">
                    </i>
                    <?php echo $LANG['UI_LOGIN_AGENT_DOWNLOAD']?>
                </button>
            </form>
        </div>
    </div>
</div>
    <!--    模态框-->
    <div class="overlay" style="display: none">
        <div id="modal">
            <p><?php echo $LANG['UI_LOGIN_SUGGEST_EDIT_PASSWORD_TIP']?></p>
            <div class="buttonAction">
                <button class="edit_pwd_button login-btn-font_en login-btn-font_cn"><?php echo $LANG['UI_PLATFORM_EDIT_PASSWORD'] ?></button>
                <button class="login_success_button login-btn-font_en login-btn-font_cn"><?php echo $LANG['UI_LOGIN_DIRECT_LOGIN'] ?></button>
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