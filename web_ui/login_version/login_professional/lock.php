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
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/css/platform/professional_login.css" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME STYLES -->
    <link href="/assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="/assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="/css/platform/main.css" rel="stylesheet" type="text/css"/>
    <link href="/css/platform/loginbackground.css" rel="stylesheet" type="text/css"/>
    <link href="/css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
</head>
<body style="width:100%;height:100%">
<input type="text" class="systemLang display-none" value="<?php echo $_SESSION['language']?>">
    <div class="content">
        <div class="lock-content">
        <div class="rightImg" style="margin-left: 115px">
        <?php 
           if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
                echo '<img src="/img/platform/lockImg.png" alt="">';
           }
           else{
                echo '<img src="/img/platform/lockImg_en.png" alt="">';
           }

         ?>
      
        </div>
        <div class="lock">
                <div class="logo">
                    <a class="brand" href="<?php echo $app['REMOTE']['website']?>" target="_black">
                        <?php
                        if($app['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType){
                            //如果是免费版,使用专用logo
                            echo '<img src="/img/platform/logo-free.png" alt="" class="logo-default loginlogosizefree">';
                        }else{
                            //如果是其他版本
                            if(file_exists($app['SPECIAL_DIR'] . "logo.png")){
                                $logo = "/special/logo.png";
                            }else{
                                $logo = "/img/platform/logoLock.png";
                            }
                            echo '<img src="' . $logo . '" alt="" class="logo-default loginlogosize">';
                        }
                        ?>
                    </a>
                </div>
                <div class="lockTips">
                    <?php echo $LANG['UI_PUBLIC_USER']?>
                    <span id="username"><?php unset($_SESSION['userUUID']); echo $_SESSION['tenantusername'];?></span>
                    <?php echo $LANG['UI_LOCK_LOCKED']?>
                    <?php echo $LANG['UI_LOCK_INPUT_RELOGIN']?>
                </div>
                <div class="alertMsg alert alert-danger my_alert" style="display: none">
                    <button type="button" class="close" data-close="alert"></button>
                    <span id="login_tip" style="font-size: 16px">
                    <?php echo $LANG['UI_LOCK_INPUT_PASSWORD']?>
                </span>
                </div>
                <form class="lock-form" action="./">
                    <input type="password" autocomplete="new-password" hidden>
                    <div class="input-icon">
                        <i class="viconfont vicon-a-lock" style="margin-top: 18px;"></i>
                        <i class="eye">
                            <svg xmlns="http://www.w3.org/2000/svg" width="19.013" height="13.291" viewBox="0 0 19.013 13.291"><path class="a" d="M-3946.953-5924.657a.435.435,0,0,1,0-.4,12.011,12.011,0,0,1,1.692-2.514.442.442,0,0,1,.621-.041.44.44,0,0,1,.039.623,11.067,11.067,0,0,0-1.465,2.13,9.323,9.323,0,0,0,8.066,5.266,9.322,9.322,0,0,0,8.064-5.266,9.324,9.324,0,0,0-8.064-5.265,8.446,8.446,0,0,0-4.818,1.539.438.438,0,0,1-.612-.11.44.44,0,0,1,.11-.612,9.325,9.325,0,0,1,5.32-1.7,10.235,10.235,0,0,1,8.952,5.947.435.435,0,0,1,0,.4,10.235,10.235,0,0,1-8.952,5.947A10.235,10.235,0,0,1-3946.953-5924.657Zm5.533-.379a3.331,3.331,0,0,1,3.327-3.327,3.331,3.331,0,0,1,3.327,3.327,3.331,3.331,0,0,1-3.327,3.327A3.331,3.331,0,0,1-3941.42-5925.036Zm.88,0a2.449,2.449,0,0,0,2.447,2.447,2.45,2.45,0,0,0,2.447-2.447,2.45,2.45,0,0,0-2.447-2.447A2.449,2.449,0,0,0-3940.541-5925.036Z" transform="translate(3947.507 5931.5)"/></svg>
                        </i>
                        <i class="eye-close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="12.289" viewBox="0 0 18 12.289"><path class="a" d="M4193.771,2871.294a11.6,11.6,0,0,0-2.691-3.5l1.916-1.295a.44.44,0,0,0-.492-.729l-2.143,1.448a9.171,9.171,0,0,0-10.861-.171.44.44,0,1,0,.5.722,8.277,8.277,0,0,1,9.581-.024l-2.244,1.517a3.322,3.322,0,0,0-5.491,3.712l-2.533,1.712a10.628,10.628,0,0,1-2.561-3.19,11.04,11.04,0,0,1,1.466-2.131.44.44,0,0,0-.66-.581,11.972,11.972,0,0,0-1.694,2.514.442.442,0,0,0,0,.4,11.594,11.594,0,0,0,2.691,3.5l-1.916,1.295a.44.44,0,0,0,.492.729l2.143-1.448a9.373,9.373,0,0,0,5.542,1.868,10.236,10.236,0,0,0,8.953-5.947A.443.443,0,0,0,4193.771,2871.294Zm-11.493.018a2.446,2.446,0,0,1,4.331-1.56l-4.035,2.727A2.427,2.427,0,0,1,4182.277,2871.312Zm4.894,0a2.443,2.443,0,0,1-4.042,1.852l3.909-2.642A2.447,2.447,0,0,1,4187.171,2871.312Zm-2.354,5.447a8.449,8.449,0,0,1-4.763-1.515l2.327-1.573a3.325,3.325,0,0,0,5.407-3.654l2.534-1.713a10.619,10.619,0,0,1,2.561,3.19A9.324,9.324,0,0,1,4184.817,2876.758Z" transform="translate(-4175.817 -2865.348)"/></svg>
                        </i>
                        <i id="capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                        <input type="password" autocomplete="off" id="psd" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" maxlength="32"  class="form-control passwordInput" placeholder="<?php echo $LANG['UI_LOCK_INPUT_PASSWORD']?>">
                    </div>
                    <span class="errorTip"></span>
                    <span class="input-group-btn">
					<button type="button" id="login" class="btn  icn-only">
                        <?php echo $LANG['UI_LOGIN_BUTTON_LOGIN']?>
                    </button>
                </span>
                    <div class="lockLine">
                        <?php 
                                if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
                                        echo '<span class="line"></span>';
                                }
                                else{
                                    echo '<span class="line" style="width:127px;"></span>';
                                }
                        ?>
                        <a href="/login.php"><?php echo $LANG['UI_LOCK_OTHER_USER']?> </a>
                        <?php 
                                 if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
                                            echo '<span class="line"></span>';
                                    }
                                    else{
                                        echo '<span class="line" style="width:127px;"></span>';
                                    }
                            ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <dic class="shape3"></dic>
    <div class="shape4"></div>
    <div class="copyright">
        <?php echo $app['SYSTEM_INFO']['copyright'] . " &copy; " . $app['SYSTEM_INFO']['years'] . " ".
            $app['SYSTEM_INFO']['company'] . $app['SYSTEM_INFO']['version'];
        ?>
        <p>
            <?php echo $app['SYSTEM_INFO']['recommend'];?>
        </p>
    </div>
        <script src="/assets/global/plugins/respond.min.js"></script>
        <script src="/assets/global/plugins/excanvas.min.js"></script>
        <![endif]-->
        <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jquery.history.min.js" type="text/javascript"></script>
        <!-- END CORE PLUGINS -->
        <!-- BEGIN PAGE LEVEL PLUGINS -->
        <script src="/assets/global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
        <!-- END PAGE LEVEL PLUGINS -->
        <script src="/assets/global/scripts/metronic.js" type="text/javascript"></script>
        <script src="/assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
        <script src="/assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
        <script src="/lang/<?php echo $_SESSION['language']; ?>.js" type="text/javascript"></script>
        <script src="/scripts/conf/config.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
        <script src="/scripts/libs/json2.min.js" type="text/javascript"></script>
        <script src="/scripts/libs/md5.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
        <script src="/scripts/platform/lock.js"></script>
</body>
</html>