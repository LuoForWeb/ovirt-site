<!DOCTYPE html>
<html lang="en">
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
<link rel="stylesheet" href="/css/platform/professional_login.css">
<head>
    <?php
    // 获取名为 "show" 的参数的值
    $show = $_GET['show'] ?? '';
    ?>
</head>
<body>
<div class="content">
    <div class="modifyPwd-content">
        <div class="rightImg"style="margin-left: 115px" ><img src="./img/platform/vinchinPic.png" alt="">
        </div>
        <div class="modifyPwd">
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
            <form class="modifyPwd-form">
                <h3 class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></h3>
                <div class="userTips alert alert-block alert-info first-edit" style="display: <?php echo $show == 'first_login' ? 'block' : 'none'; ?>">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <p><?php echo $LANG['UI_PUBLIC_USER'];?> <?php echo $username ?>  <?php echo $LANG['UI_FORCED_MODIFY_FIRST_TIPS'];?></p>
                </div>
                <div class="userTips alert alert-block alert-info overtime-edit" style="display: <?php echo $show == 'over_time' ? 'block' : 'none'; ?>">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <p><?php echo $LANG['UI_FORCED_MODIFY_TIPS'];?></p>
                </div>
                <div class="form-group">
                    <div class="input-icon userInput form-input form-oldpassword">
                        <i class="viconfont vicon-a-lock"></i>
                        <i class="eye">
                            <svg xmlns="http://www.w3.org/2000/svg" width="19.013" height="13.291" viewBox="0 0 19.013 13.291"><path class="a" d="M-3946.953-5924.657a.435.435,0,0,1,0-.4,12.011,12.011,0,0,1,1.692-2.514.442.442,0,0,1,.621-.041.44.44,0,0,1,.039.623,11.067,11.067,0,0,0-1.465,2.13,9.323,9.323,0,0,0,8.066,5.266,9.322,9.322,0,0,0,8.064-5.266,9.324,9.324,0,0,0-8.064-5.265,8.446,8.446,0,0,0-4.818,1.539.438.438,0,0,1-.612-.11.44.44,0,0,1,.11-.612,9.325,9.325,0,0,1,5.32-1.7,10.235,10.235,0,0,1,8.952,5.947.435.435,0,0,1,0,.4,10.235,10.235,0,0,1-8.952,5.947A10.235,10.235,0,0,1-3946.953-5924.657Zm5.533-.379a3.331,3.331,0,0,1,3.327-3.327,3.331,3.331,0,0,1,3.327,3.327,3.331,3.331,0,0,1-3.327,3.327A3.331,3.331,0,0,1-3941.42-5925.036Zm.88,0a2.449,2.449,0,0,0,2.447,2.447,2.45,2.45,0,0,0,2.447-2.447,2.45,2.45,0,0,0-2.447-2.447A2.449,2.449,0,0,0-3940.541-5925.036Z" transform="translate(3947.507 5931.5)"/></svg>
                        </i>
                        <i class="eye-close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="12.289" viewBox="0 0 18 12.289"><path class="a" d="M4193.771,2871.294a11.6,11.6,0,0,0-2.691-3.5l1.916-1.295a.44.44,0,0,0-.492-.729l-2.143,1.448a9.171,9.171,0,0,0-10.861-.171.44.44,0,1,0,.5.722,8.277,8.277,0,0,1,9.581-.024l-2.244,1.517a3.322,3.322,0,0,0-5.491,3.712l-2.533,1.712a10.628,10.628,0,0,1-2.561-3.19,11.04,11.04,0,0,1,1.466-2.131.44.44,0,0,0-.66-.581,11.972,11.972,0,0,0-1.694,2.514.442.442,0,0,0,0,.4,11.594,11.594,0,0,0,2.691,3.5l-1.916,1.295a.44.44,0,0,0,.492.729l2.143-1.448a9.373,9.373,0,0,0,5.542,1.868,10.236,10.236,0,0,0,8.953-5.947A.443.443,0,0,0,4193.771,2871.294Zm-11.493.018a2.446,2.446,0,0,1,4.331-1.56l-4.035,2.727A2.427,2.427,0,0,1,4182.277,2871.312Zm4.894,0a2.443,2.443,0,0,1-4.042,1.852l3.909-2.642A2.447,2.447,0,0,1,4187.171,2871.312Zm-2.354,5.447a8.449,8.449,0,0,1-4.763-1.515l2.327-1.573a3.325,3.325,0,0,0,5.407-3.654l2.534-1.713a10.619,10.619,0,0,1,2.561,3.19A9.324,9.324,0,0,1,4184.817,2876.758Z" transform="translate(-4175.817 -2865.348)"/></svg>
                        </i>
                        <i id="old-capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                        <input class="form-control toggle-oldpassword" type="password" autocomplete="off" placeholder="<?php echo $LANG['UI_USER_OLD_PASS']; ?>" autocomplete="off" id="oldpass" name="oldpass" maxlength="32" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                        <span class="errorTipOldPass"></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-icon userInput form-input form-password">
                        <i class="viconfont vicon-a-lock"></i>
                        <i class="eye">
                            <svg xmlns="http://www.w3.org/2000/svg" width="19.013" height="13.291" viewBox="0 0 19.013 13.291"><path class="a" d="M-3946.953-5924.657a.435.435,0,0,1,0-.4,12.011,12.011,0,0,1,1.692-2.514.442.442,0,0,1,.621-.041.44.44,0,0,1,.039.623,11.067,11.067,0,0,0-1.465,2.13,9.323,9.323,0,0,0,8.066,5.266,9.322,9.322,0,0,0,8.064-5.266,9.324,9.324,0,0,0-8.064-5.265,8.446,8.446,0,0,0-4.818,1.539.438.438,0,0,1-.612-.11.44.44,0,0,1,.11-.612,9.325,9.325,0,0,1,5.32-1.7,10.235,10.235,0,0,1,8.952,5.947.435.435,0,0,1,0,.4,10.235,10.235,0,0,1-8.952,5.947A10.235,10.235,0,0,1-3946.953-5924.657Zm5.533-.379a3.331,3.331,0,0,1,3.327-3.327,3.331,3.331,0,0,1,3.327,3.327,3.331,3.331,0,0,1-3.327,3.327A3.331,3.331,0,0,1-3941.42-5925.036Zm.88,0a2.449,2.449,0,0,0,2.447,2.447,2.45,2.45,0,0,0,2.447-2.447,2.45,2.45,0,0,0-2.447-2.447A2.449,2.449,0,0,0-3940.541-5925.036Z" transform="translate(3947.507 5931.5)"/></svg>
                        </i>
                        <i class="eye-close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="12.289" viewBox="0 0 18 12.289"><path class="a" d="M4193.771,2871.294a11.6,11.6,0,0,0-2.691-3.5l1.916-1.295a.44.44,0,0,0-.492-.729l-2.143,1.448a9.171,9.171,0,0,0-10.861-.171.44.44,0,1,0,.5.722,8.277,8.277,0,0,1,9.581-.024l-2.244,1.517a3.322,3.322,0,0,0-5.491,3.712l-2.533,1.712a10.628,10.628,0,0,1-2.561-3.19,11.04,11.04,0,0,1,1.466-2.131.44.44,0,0,0-.66-.581,11.972,11.972,0,0,0-1.694,2.514.442.442,0,0,0,0,.4,11.594,11.594,0,0,0,2.691,3.5l-1.916,1.295a.44.44,0,0,0,.492.729l2.143-1.448a9.373,9.373,0,0,0,5.542,1.868,10.236,10.236,0,0,0,8.953-5.947A.443.443,0,0,0,4193.771,2871.294Zm-11.493.018a2.446,2.446,0,0,1,4.331-1.56l-4.035,2.727A2.427,2.427,0,0,1,4182.277,2871.312Zm4.894,0a2.443,2.443,0,0,1-4.042,1.852l3.909-2.642A2.447,2.447,0,0,1,4187.171,2871.312Zm-2.354,5.447a8.449,8.449,0,0,1-4.763-1.515l2.327-1.573a3.325,3.325,0,0,0,5.407-3.654l2.534-1.713a10.619,10.619,0,0,1,2.561,3.19A9.324,9.324,0,0,1,4184.817,2876.758Z" transform="translate(-4175.817 -2865.348)"/></svg>
                        </i>
                        <i id="new-capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                        <input class="form-control passwordRule toggle-password" type="password" autocomplete="off" placeholder="<?php echo $LANG['UI_USER_NEW_PASS']; ?>" autocomplete="off" id="password" name="password" maxlength="32" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-icon userInput form-input form-repassword">
                        <i class="viconfont vicon-a-lock"></i>
                        <i class="eye">
                            <svg xmlns="http://www.w3.org/2000/svg" width="19.013" height="13.291" viewBox="0 0 19.013 13.291"><path class="a" d="M-3946.953-5924.657a.435.435,0,0,1,0-.4,12.011,12.011,0,0,1,1.692-2.514.442.442,0,0,1,.621-.041.44.44,0,0,1,.039.623,11.067,11.067,0,0,0-1.465,2.13,9.323,9.323,0,0,0,8.066,5.266,9.322,9.322,0,0,0,8.064-5.266,9.324,9.324,0,0,0-8.064-5.265,8.446,8.446,0,0,0-4.818,1.539.438.438,0,0,1-.612-.11.44.44,0,0,1,.11-.612,9.325,9.325,0,0,1,5.32-1.7,10.235,10.235,0,0,1,8.952,5.947.435.435,0,0,1,0,.4,10.235,10.235,0,0,1-8.952,5.947A10.235,10.235,0,0,1-3946.953-5924.657Zm5.533-.379a3.331,3.331,0,0,1,3.327-3.327,3.331,3.331,0,0,1,3.327,3.327,3.331,3.331,0,0,1-3.327,3.327A3.331,3.331,0,0,1-3941.42-5925.036Zm.88,0a2.449,2.449,0,0,0,2.447,2.447,2.45,2.45,0,0,0,2.447-2.447,2.45,2.45,0,0,0-2.447-2.447A2.449,2.449,0,0,0-3940.541-5925.036Z" transform="translate(3947.507 5931.5)"/></svg>
                        </i>
                        <i class="eye-close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="12.289" viewBox="0 0 18 12.289"><path class="a" d="M4193.771,2871.294a11.6,11.6,0,0,0-2.691-3.5l1.916-1.295a.44.44,0,0,0-.492-.729l-2.143,1.448a9.171,9.171,0,0,0-10.861-.171.44.44,0,1,0,.5.722,8.277,8.277,0,0,1,9.581-.024l-2.244,1.517a3.322,3.322,0,0,0-5.491,3.712l-2.533,1.712a10.628,10.628,0,0,1-2.561-3.19,11.04,11.04,0,0,1,1.466-2.131.44.44,0,0,0-.66-.581,11.972,11.972,0,0,0-1.694,2.514.442.442,0,0,0,0,.4,11.594,11.594,0,0,0,2.691,3.5l-1.916,1.295a.44.44,0,0,0,.492.729l2.143-1.448a9.373,9.373,0,0,0,5.542,1.868,10.236,10.236,0,0,0,8.953-5.947A.443.443,0,0,0,4193.771,2871.294Zm-11.493.018a2.446,2.446,0,0,1,4.331-1.56l-4.035,2.727A2.427,2.427,0,0,1,4182.277,2871.312Zm4.894,0a2.443,2.443,0,0,1-4.042,1.852l3.909-2.642A2.447,2.447,0,0,1,4187.171,2871.312Zm-2.354,5.447a8.449,8.449,0,0,1-4.763-1.515l2.327-1.573a3.325,3.325,0,0,0,5.407-3.654l2.534-1.713a10.619,10.619,0,0,1,2.561,3.19A9.324,9.324,0,0,1,4184.817,2876.758Z" transform="translate(-4175.817 -2865.348)"/></svg>
                        </i>
                        <i id="re-capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                        <input class="form-control toggle-repassword" type="password" autocomplete="off" placeholder="<?php echo $LANG['UI_RESETPWD_REENTER_NEWPASSWORD']; ?>" autocomplete="off" id="newPassword" name="newPassword" maxlength="32" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                    </div>
                </div>
                <div class="form-action">
                    <button class="modifyPwdSure" id="editsubmit" type="submit"  data-toggle="modal"  ><?php echo $LANG['UI_RESETPWD_YES']; ?></button>
                    <div class="modifyPwdLine">
                        <span class="line  width165_en"></span>
                        <a href="./login.php"><?php echo $LANG['UI_FORGETPWD_RETURNLOGIN']; ?></a>
                        <span class="line  width165_en"></span>
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
<div class="shape3"></div>
<div class="shape4"></div>
</body>
</html>
