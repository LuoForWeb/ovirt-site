<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <?php
    include_once $_SERVER['DOCUMENT_ROOT'].'/web_ng/api/public/load.php';
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $app = xphp_get_config('app');

    if($_SESSION['language'] != null){
        $LANG = require $rootPath .'/lang/'.$_SESSION['language'].$app['ext'];
        $lang = $_SESSION['language'];
    }else{
        $LANG = require $rootPath .'/lang/'.$app['lang'].$app['ext'];
        $lang = $app['lang'];
    }
    $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
    /**
     * 特殊配置,主要用于第三方OEM版本
     * 所有配置文件均在根目录下special文件夹,包括配置文件,logo,图片等所有信息
     */
    $configFile = $app['SPECIAL_DIR'] . $app['SPECIAL_CONFIG'];
    if(file_exists($configFile)){
        $content = file_get_contents($configFile);
        $content = json_decode($content, true);
        setSpecialConfigRecursiveLogin($app, $content);
    }
    function setSpecialConfigRecursiveLogin(&$arrA, $arrB){
        foreach ($arrB as $key => $value){
            if(is_array($value)){
                setSpecialConfigRecursiveLogin($arrA[$key], $value);
            }else{
                $arrA[$key] = $value;
            }
        }
    }

    /**
     * 获取页面布局
     */
    $loginLayOut = $app['LOGIN_INFO']['login_layout'];

    /**
     * 背景
     */
    $bgImg = '';
    //布局
    switch ($loginLayOut){
        case 'leftLayout':
            $bgImg = '/img/platform/login/left_bg.svg';
            $cardStyle = 'cardLeft';
            $ContentStyle = 'leftLayOut-left-content';
            $loginContentStyle = 'send-success-content';
            break;
        case 'mediumLayout':
            $bgImg = '/img/platform/login/medium_bg.svg';
            $cardStyle = 'cardMedium';
            $ContentStyle = 'mediumLayOut-medium-content';
            $loginContentStyle = 'send-success-content';
            break;
        case 'rightLayout':
            $bgImg = '/img/platform/login/right_bg.svg';
            $cardStyle = 'cardLeft';
            $ContentStyle = 'leftLayOut-left-content';
            $loginContentStyle = 'send-success-content-right';
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
                <div class="send-success_div" action="index.html" method="post">
                        <div class="welcome-login-tip"><?php echo $LANG['UI_LOGIN_BUTTON_PASSWORD']; ?></div>
                        <div class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></div>
                        <!--发送邮件-->
                        <div class="send-conent" style="display: <?php echo $show == 'send_email' ? 'block' : 'none'; ?>">
                            <div class="send-suceess-content">
                                <div class="send-suceess-tips-conent">
                                    <div class="send-suceess-img">
                                        <img src="/img/platform/login/email.svg" alt="">
                                    </div>
                                    <p class="send-suceess-tips"><?php echo $LANG['UI_EMAIL_SENDSUCCESS']; ?>
                                        <br><?php echo $LANG['UI_EMAIL_TO_EMAILBOX']; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="send-success-buttons">
                                <a href="./forget_pwd.php"><button class="return-back"><?php echo $LANG['UI_EMAIL_RETURNBACK']?></button></a>
                                <a href="/login.php"><button class="return-login"><?php echo $LANG['UI_EMAIL_RETURNLOGIN']?></button></a>
                            </div>
                        </div>
                        <!--重置密码成功-->
                        <div class="reset-content" style="display: <?php echo $show == 'reset_success' ? 'block' : 'none'; ?>">
                            <div class="reset-suceess-content">
                                <div class="reset-suceess-tips-conent">
                                    <div class="reset-suceess-img">
                                        <img src="/img/platform/login/success.svg" alt="">
                                    </div>
                                    <p class="reset-suceess-tips"><?php echo $LANG['UI_RESETPWD_PASSWORD_SUCCESS']?>
                                        <br><span class="second"></span><?php echo $LANG['UI_RESETPWD_PASSWORD_JUMP_TO_LOGIN']?>
                                    </p>
                                </div>
                            </div>
                            <div class="reset-success-button">
                                <a href="/login.php"><button class="return-login"><?php echo $LANG['UI_RESETPWD_LOGIN_NOW']?></button></a>
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
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/scripts/platform/reset_pwd_success.js"></script>
</body>
</html>
