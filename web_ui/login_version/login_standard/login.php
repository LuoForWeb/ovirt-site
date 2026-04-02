<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
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
    <link rel="stylesheet" href="./css/platform/standard_login.css">
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
<body>
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
            <form class="login-form" action="index.html" method="post">
                <input type="password" autocomplete="new-password" hidden>
                <h3 class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></h3>
                <div class="alert display-hide textalignc">
                    <span class="login-tip">
                        <?php echo $LANG['UI_LOGIN_TIP_USERNAME_AND_PASSWORD']?>
                    </span>
                </div>
                <div class="form-group">
                    <div class="input-icon">
                        <input class="form-control username" value="<?php echo $username?>" maxlength="64" type="text" autocomplete="off" placeholder="<?php echo $LANG['UI_LOGIN_USERNAME']?>" name="username"/>
                    </div>
                </div>
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
                        <input class="form-control passwordInput" value="<?php echo $password?>" maxlength="32" type="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off" placeholder="<?php echo $LANG['UI_LOGIN_PASSWORD']?>" name="password"/>
                    </div>
                </div>
                <div class="form-actions">
                    <!-- 验证码区域 -->
                    <div class="code login-code display-none">
                        <input type="text"
                               name="very_code"
                               value=""
                               placeholder="<?php echo $LANG['UI_LOGIN_ENTER_CODE']?>"
                               class="input-val"
                               autocomplete="off">
                        <a href="javascript:refresh_code();" class="code-refresh">
                            <img src="./code.php" class="code-canvas">
                        </a>
                    </div>
                    <!-- 记住密码复选框 -->
                    <div class="checkbox">
                        <label class="rememberLeft" for="remember-pwd">
                            <i class="remember-true" hidden data-checked="<?php echo $remember;?>">
                                <img src="./img/platform/login/standard_login/rememberTrue.svg" class="mt-2">
                            </i>
                            <i class="remember-false">
                                <img src="./img/platform/login/standard_login/rememberFalse.svg" class="mt-2">
                            </i>
                            <span class="remember-pwd"><?php echo $LANG['UI_LOGIN_REMEMBER_USER']?></span>
                        </label>
                    </div>
                    <!-- 登录按钮 -->
                    <button type="submit" class="btn login-btn">
                        <?php echo $LANG['UI_LOGIN_BUTTON_LOGIN']?>
                    </button>
                    <!-- 底部链接 -->
                    <div class="loginLine">
                        <a href="javascript:;" class="download-agent">
                            <?php echo $LANG['UI_LOGIN_DOWNLOAD_AGENT']?></a>
                        </a>
                        <a href="/login_version/login_standard/forget_pwd.php" id="forget-pwd">
                            <?php echo $LANG['UI_LOGIN_FORGET_PASSWORD']?>
                        </a>
                    </div>
                </div>
            </form>
<!--            <div class="display-hide nosupporttips">-->
<!--                <button type="button" class="close" data-close="alert" onclick="$('.nosupporttips').hide();"></button>-->
<!--                <span>-->
<!--                    --><?php //echo $LANG['UI_LOGIN_AGENT_NOSUPPORT']?>
<!--                </span>-->
<!--            </div>-->
            <div class="contentpakage display-hide">
                <form class="agent-form positionrel" action="index.html" method="post">
                    <div>
                        <div class="agent-type"> <?php echo $LANG['UI_LOGIN_AGENT_TYPE']?></div>
                        <div>
                            <select class="form-control agenttype-select">
                            </select>
                        </div>
                    </div>
                    <div class="vmagent">
                        <div class="divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_VENDOR']?></div>
                        <div class="vendor-div">
                            <select class="form-control vendor_select">
                            </select>
                        </div>
                        <div class="display-hide nosupporttips"><?php echo $LANG['UI_LOGIN_AGENT_NOSUPPORT'];?></div>
                    </div>
                    <div class="vmagent display-hide vmagent_div">
                        <div class="divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION']?></div>
                        <div>
                            <select class="form-control version_select">
                            </select>
                        </div>
                    </div>
                    <!--操作系统-->
                    <div class="osagent display-hide">
                        <div class="divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM']?></div>
                        <div class="ossystem-div">
                            <select class="form-control ossystem_select">
                            </select>
                        </div>
                        <div class="display-hide nosupporttips"><?php echo $LANG['UI_LOGIN_AGENT_NOSUPPORT'];?></div>
                    </div>
                    <!--版本和架构-->
                    <div class="fsagent display-hide">
                        <div class="divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION_ARC']?></div>
                        <div>
                            <select class="form-control filesystem_select">
                            </select>
                        </div>
                    </div>
                    <div class="dbagent display-hide">
                        <div class="divcontrol"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?></div>
                        <div>
                            <select class="form-control dbtimingClient_select">
                            </select>
                        </div>
                    </div>
                    <div class="dbcdp display-hide" >
                        <div class="divcontrol"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?></div>
                        <div>
                            <select class="form-control dbcdpClient_select">
                            </select>
                        </div>
                    </div>
                    <div class="dbprotectagent display-hide">
                        <div class="divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM']?></div>
                        <div>
                            <select class="form-control " id="dbprotectsystem">
                            </select>
                        </div>
                    </div>
                    <button type="button" class="download_btn">
                        <?php echo $LANG['UI_LOGIN_AGENT_DOWNLOAD']?>
                    </button>
            </div>
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
<div class="overlay" style="display: none">
    <div id="modal">
        <div class="edit-password-tip">
            <?php echo $LANG['UI_LOGIN_SUGGEST_EDIT_PASSWORD_TIP']?>
        </div>
        <div class="buttonAction">
            <button class="edit_pwd_button login-btn-font_en login-btn-font_cn"><?php echo $LANG['UI_PLATFORM_EDIT_PASSWORD'] ?></button>
            <button class="login_success_button login-btn-font_en login-btn-font_cn"><?php echo $LANG['UI_LOGIN_DIRECT_LOGIN'] ?></button>
        </div>
    </div>
</div>
</body>
</html>