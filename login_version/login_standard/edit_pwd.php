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
<link rel="stylesheet" href="/css/platform/standard_login.css">
<head>
    <?php
    // 获取名为 "show" 的参数的值
    $show = $_GET['show'] ?? '';
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
            <form class="modifyPwd-form">
                <h3 class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></h3>
                <div class="userTips alert alert-block alert-info first-edit" style="display: <?php echo $show == 'first_login' ? 'block' : 'none'; ?>">
                    <p><?php echo $LANG['UI_PUBLIC_USER'];?> <?php echo $username ?>  <?php echo $LANG['UI_FORCED_MODIFY_FIRST_TIPS'];?></p>
                </div>
                <div class="overtime-edit-tip" style="display: <?php echo $show == 'over_time' ? 'block' : 'none'; ?>">
                    <p><?php echo $LANG['UI_FORCED_MODIFY_TIPS'];?></p>
                </div>
                <div class="form-group">
                    <div class="input-icon userInput form-input form-oldpassword">
                        <i class="eye">
                            <img src="/img/platform/login/standard_login/eye.svg" alt="">
                        </i>
                        <i class="eye-close">
                            <img src="/img/platform/login/standard_login/eye-close.svg" alt="">
                        </i>
                        <i id="old-capslock-warning" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>">
                            <img src="/img/platform/login/standard_login/capslock.svg" alt="">
                        </i>
                        <input class="form-control toggle-oldpassword" type="password" autocomplete="off" placeholder="<?php echo $LANG['UI_USER_OLD_PASS']; ?>" autocomplete="off" id="oldpass" name="oldpass" maxlength="32" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                    </div>
                    <span class="errorTipOldPass"></span>
                </div>
                <div class="form-group">
                    <div class="input-icon userInput form-input form-password">
                        <i class="eye">
                            <img src="/img/platform/login/standard_login/eye.svg" alt="">
                        </i>
                        <i class="eye-close">
                            <img src="/img/platform/login/standard_login/eye-close.svg" alt="">
                        </i>
                        <i id="new-capslock-warning" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>">
                            <img src="/img/platform/login/standard_login/capslock.svg" alt="">
                        </i>
                        <input class="form-control passwordRule toggle-password" type="password" autocomplete="off" placeholder="<?php echo $LANG['UI_USER_NEW_PASS']; ?>" autocomplete="off" id="password" name="password" maxlength="32" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                    </div>
                </div>
                <div class="form-group form-repassword_en mb4">
                    <div class="input-icon userInput form-input form-repassword">
                        <i class="eye">
                            <img src="/img/platform/login/standard_login/eye.svg" alt="">
                        </i>
                        <i class="eye-close">
                            <img src="/img/platform/login/standard_login/eye-close.svg" alt="">
                        </i>
                        <i id="re-capslock-warning" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>">
                            <img src="/img/platform/login/standard_login/capslock.svg" alt="">
                        </i>
                        <input class="form-control toggle-repassword" type="password" autocomplete="off" placeholder="<?php echo $LANG['UI_RESETPWD_REENTER_NEWPASSWORD']; ?>" autocomplete="off" id="newPassword" name="newPassword" maxlength="32" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                    </div>
                </div>
                <div class="form-actions">
                    <button class="modifyPwdSure btn" id="editsubmit" type="submit"  data-toggle="modal"  ><?php echo $LANG['UI_RESETPWD_YES']; ?></button>
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
</body>
</html>
