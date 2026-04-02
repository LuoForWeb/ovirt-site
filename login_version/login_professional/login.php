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
<link rel="stylesheet" href="./css/platform/professional_login.css">
<body>
<div class="content">
            <div class="login-content_div">
                <div class="rightImg">
                    <?php
                    if($lang == "zh-cn" || $lang == "zh-tw"){
                            echo '<img src="./img/platform/vinchinPic.png" alt="">';
                    }
                    else{
                            echo '<img src="./img/platform/vinchinPic_en.png" alt="">';
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
                                $logo = "/img/platform/logoNew.png";
                            }
                            echo '<img src="' . $logo . '" alt="" class="logo-default loginlogosize">';
                        }
                        ?>
                    </a>
                </div>
                <form class="login-form" action="index.html" method="post">
                <input type="password" autocomplete="new-password" hidden>
                    <h3 class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></h3>
                    <div class="alert display-hide my_alert" style="text-align: center">
                        <button type="button" class="close" data-close="alert" onclick="$('.my_alert').hide();"></button>
                        <span class="login-tip" style="font-size: 16px">
    			           <?php echo $LANG['UI_LOGIN_TIP_USERNAME_AND_PASSWORD']?>
                        </span>
                    </div>
                    <div class="form-group">
                        <div class="control-label visible-ie8 visible-ie9"><?php echo $LANG['UI_LOGIN_USERNAME']?></div>
                        <div class="input-icon">
                            <i class="viconfont vicon-a-user user"></i>
                            <input class="form-control placeholder-no-fix username" value="<?php echo $username?>" maxlength="64" type="text" autocomplete="off" placeholder="<?php echo $LANG['UI_LOGIN_USERNAME']?>" name="username" style="padding-left: 45px"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="control-label visible-ie8 visible-ie9"><?php echo $LANG['UI_LOGIN_PASSWORD']?></div>
                        <div class="input-icon">
                            <i class="viconfont vicon-a-lock lock"></i>
                            <i class="eye">
                                <svg xmlns="http://www.w3.org/2000/svg" width="19.013" height="13.291" viewBox="0 0 19.013 13.291"><path class="a" d="M-3946.953-5924.657a.435.435,0,0,1,0-.4,12.011,12.011,0,0,1,1.692-2.514.442.442,0,0,1,.621-.041.44.44,0,0,1,.039.623,11.067,11.067,0,0,0-1.465,2.13,9.323,9.323,0,0,0,8.066,5.266,9.322,9.322,0,0,0,8.064-5.266,9.324,9.324,0,0,0-8.064-5.265,8.446,8.446,0,0,0-4.818,1.539.438.438,0,0,1-.612-.11.44.44,0,0,1,.11-.612,9.325,9.325,0,0,1,5.32-1.7,10.235,10.235,0,0,1,8.952,5.947.435.435,0,0,1,0,.4,10.235,10.235,0,0,1-8.952,5.947A10.235,10.235,0,0,1-3946.953-5924.657Zm5.533-.379a3.331,3.331,0,0,1,3.327-3.327,3.331,3.331,0,0,1,3.327,3.327,3.331,3.331,0,0,1-3.327,3.327A3.331,3.331,0,0,1-3941.42-5925.036Zm.88,0a2.449,2.449,0,0,0,2.447,2.447,2.45,2.45,0,0,0,2.447-2.447,2.45,2.45,0,0,0-2.447-2.447A2.449,2.449,0,0,0-3940.541-5925.036Z" transform="translate(3947.507 5931.5)"/></svg>
                            </i>
                            <i class="eye-close">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="12.289" viewBox="0 0 18 12.289"><path class="a" d="M4193.771,2871.294a11.6,11.6,0,0,0-2.691-3.5l1.916-1.295a.44.44,0,0,0-.492-.729l-2.143,1.448a9.171,9.171,0,0,0-10.861-.171.44.44,0,1,0,.5.722,8.277,8.277,0,0,1,9.581-.024l-2.244,1.517a3.322,3.322,0,0,0-5.491,3.712l-2.533,1.712a10.628,10.628,0,0,1-2.561-3.19,11.04,11.04,0,0,1,1.466-2.131.44.44,0,0,0-.66-.581,11.972,11.972,0,0,0-1.694,2.514.442.442,0,0,0,0,.4,11.594,11.594,0,0,0,2.691,3.5l-1.916,1.295a.44.44,0,0,0,.492.729l2.143-1.448a9.373,9.373,0,0,0,5.542,1.868,10.236,10.236,0,0,0,8.953-5.947A.443.443,0,0,0,4193.771,2871.294Zm-11.493.018a2.446,2.446,0,0,1,4.331-1.56l-4.035,2.727A2.427,2.427,0,0,1,4182.277,2871.312Zm4.894,0a2.443,2.443,0,0,1-4.042,1.852l3.909-2.642A2.447,2.447,0,0,1,4187.171,2871.312Zm-2.354,5.447a8.449,8.449,0,0,1-4.763-1.515l2.327-1.573a3.325,3.325,0,0,0,5.407-3.654l2.534-1.713a10.619,10.619,0,0,1,2.561,3.19A9.324,9.324,0,0,1,4184.817,2876.758Z" transform="translate(-4175.817 -2865.348)"/></svg>
                            </i>
                            <i id="capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                            <input class="form-control placeholder-no-fix passwordInput" value="<?php echo $password?>" maxlength="32" type="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off" placeholder="<?php echo $LANG['UI_LOGIN_PASSWORD']?>" name="password" style="padding-left: 45px"/>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="code login-code">
                            <input type="text" name="very_code" value="" placeholder="<?php echo $LANG['UI_LOGIN_ENTER_CODE']?>" class="input-val" />
                            <a href="javascript:refresh_code();">
                                <img src="./code.php" class="code-canvas" style="float: right">
                            </a>
                        </div>
                        <div class="checkbox">
                            <span class="rememberLeft">
                                <i class="remember-true" hidden  data-checked="<?php echo $remember;?>">
                                <img src="./img/platform/rememberTrue.png" style="margin-top: -2px">
                                </i>
                                <i class="remember-false">
                                    <img src="./img/platform/rememberFalse.png" alt="" style="margin-top: -2px">
                                </i>
                                <span class="remember-pwd"><?php echo $LANG['UI_LOGIN_REMEMBER_USER']?></span>
                            </span>
                         
                            <span id="forget-pwd">
                            <a href="/login_version/login_professional/forget_pwd.php" ><?php echo $LANG['UI_LOGIN_FORGET_PASSWORD']?></a>
                            </span>
                        </div>
                        <button type="submit" class="btn  pull-right login-btn">
                            <?php echo $LANG['UI_LOGIN_BUTTON_LOGIN']?>
                        </button>
                        <div class="loginLine">
                        <!-- <span class="line login-line_en login-line_cn"></span> -->
                        <?php 
                                 if($lang == "zh-cn" || $lang == "zh-tw"){
                                            echo '<span class="line"></span>';
                                    }
                                    else{
                                        echo '<span class="line" style="width:136px;"></span>';
                                    }
                        ?>
                        <a href="javascript:;" class="download-agent" style="color: #00E2DB">
                            <?php echo $LANG['UI_LOGIN_DOWNLOAD_AGENT']?></a>
                            <?php 
                                 if($lang == "zh-cn" || $$lang == "zh-tw"){
                                            echo '<span class="line"></span>';
                                    }
                                    else{
                                        echo '<span class="line" style="width:136px;"></span>';
                                    }
                            ?>
                        <!-- <span class="line login-line_en login-line_cn"></span> -->
                        </div>
                    </div>
                </form>
                <div class="alert display-hide nosupporttips" style="text-align: center;margin-top: 15px">
                    <button type="button" class="close" data-close="alert" onclick="$('.nosupporttips').hide();">s</button>
                    <span style="font-size: 16px">
        			                <?php echo $LANG['UI_LOGIN_AGENT_NOSUPPORT']?>
                    </span>
                </div>
                <div class="contentpakage display-hide">
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
                            <div style="width: 322px;">
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
                            <div style="width: 322px;">
                                <select class="form-control ossystem_select">
                                </select>
                            </div>
                        </div>
                       <!--                        版本和架构-->
                        <div class="fsagent display-hide">
                            <div class=" divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION_ARC']?>
                            </div>
                            <div style="width: 440px;">
                                <select class="form-control filesystem_select">
                                </select>
                            </div>
                        </div>
                        <div class="dbagent display-hide">
                            <div class="divcontrol"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?>
                            </div>
                            <div style="width: 322px;">
                                <select class="form-control dbtimingClient_select">
                                </select>
                            </div>
                        </div>
                        <div class="dbcdp display-hide" >
                            <div class="divcontrol"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?>
                            </div>
                            <div style="width: 322px;">
                                <select class="form-control dbcdpClient_select">
                                </select>
                            </div>
                        </div>
                        <div class="dbprotectagent display-hide">
                            <div class="divcontrol"> <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM']?>
                            </div>
                            <div style="width: 322px;">
                                <select class="form-control " id="dbprotectsystem">
                                </select>
                            </div>
                        </div>
                        <button type="button" class="download_btn" style="position: absolute;top:100px;right: 0">
                            <i class="downLoad viconfont vicon-download">
                            </i>
                            <?php echo $LANG['UI_LOGIN_AGENT_DOWNLOAD']?>
                        </button>

                </div>
            </div>
            </div>
        </div>
<div class="overlay" style="display: none">
    <div id="modal">
        <p><?php echo $LANG['UI_LOGIN_SUGGEST_EDIT_PASSWORD_TIP']?></p>
        <div class="buttonAction">
            <button class="edit_pwd_button login-btn-font_en login-btn-font_cn"><?php echo $LANG['UI_PLATFORM_EDIT_PASSWORD'] ?></button>
            <button class="login_success_button login-btn-font_en login-btn-font_cn"><?php echo $LANG['UI_LOGIN_DIRECT_LOGIN'] ?></button>
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
<div class="shape3"></div>
<div class="shape4"></div>
</body>
</html>