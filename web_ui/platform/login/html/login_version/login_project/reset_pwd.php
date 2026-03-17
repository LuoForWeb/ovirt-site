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

    //  提示信息
    $tips=$LANG['UI_RESETPWD_TIPS'];
    //  获取接收到邮件的时间
    $urlInfo=$_GET['key'];
    setcookie('resetPwdToken', $urlInfo, [
        'expires' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => [],
    ]);
    $token = v1_decrypt($urlInfo);
    $token=json_decode($token,true);
    $_SESSION['time']=$token['time']; //发送邮件的时间
    //        加载此页面的时间
    $_SESSION['nowTime']=time(); //进入页面的时间
    $_SESSION['timeout']=xphp_get_config('email','EMAIL')['sendTimeOut'];
    //        获取用户名
    $username=$token['username'];


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
            $loginContentStyle = 'reset-pwd-content';
            break;
        case 'mediumLayout':
            $bgImg = '/img/platform/login/medium_bg.svg';
            $cardStyle = 'cardMedium';
            $ContentStyle = 'mediumLayOut-medium-content';
            $loginContentStyle = 'reset-pwd-content';
            break;
        case 'rightLayout':
            $bgImg = '/img/platform/login/right_bg.svg';
            $cardStyle = 'cardLeft';
            $ContentStyle = 'leftLayOut-left-content';
            $loginContentStyle = 'reset-pwd-content-right';
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
                    <div style="display: none" class="sessionTime"><?php echo $_SESSION['time'] ?></div>
                    <div style="display: none" class="sessionNowTime"><?php echo $_SESSION['nowTime']?></div>
                    <div style="display: none" class=sessionTimeOut><?php echo $_SESSION['timeout']?></div>
                    <form class="resetpwd-form" action="index.html" method="post">
                        <div class="welcome-login-tip"><span class="welcome-tip"><?php echo $LANG['UI_RESET'] ?></span><?php echo $LANG['UI_LOGIN_PASSWORD'] ?></div>
                        <div class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></div>
                        <div class="alert reset-alert">
                        <?php echo $LANG['UI_RESETPWD_RESET_PASSWORD'] ?>
                        </div>
                        <div class="form-group">
                            <div class="form-input">
                                <i class="viconfont vicon-a-user user"></i>
                                <input class="form-control placeholder-no-fix username" value="<?php echo $username ?>" maxlength="64" readonly
                                       type="text" autocomplete="off" placeholder="<?php echo $LANG['UI_LOGIN_USERNAME'] ?>"
                                       name="username"/>
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
                                <i id="capslock-warning" class="capslock-icon viconfont vicon-daxie" data-toggle="tooltip" title="<?php echo $LANG['UI_USER_CAPS_LOCK_IS_ON']?>"></i>
                                <input class="form-control placeholder-no-fix passwordInput toggle-password" value="<?php echo $password ?>"
                                       id="password" maxlength="32" type="password"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off"
                                       placeholder="<?php echo $LANG['UI_RESETPWD_ENTER_NEWPASSWORD'] ?>" name="password"/>
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
                                       id="repassWord" maxlength="32" type="password" name="resetPassWord"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" autocomplete="off"
                                       placeholder="<?php echo $LANG['UI_RESETPWD_REENTER_NEWPASSWORD'] ?>" name="password"/>
                                <span class="errorTip"></span>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="submit_div">
                                <button type="submit" class="btn login-btn">
                                <?php echo $LANG['UI_CONFIRM'] ?>
                                </button>
                            </div>
                        </div>
                    </form>
                    <!--                链接失效-->
                    <div class="link-failure display-none">
                        <div class="welcome-login-tip"><?php echo $LANG['UI_LOGIN_BUTTON_PASSWORD'] ?></div>
                        <div class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></div>
                        <div class="link-content" style="display: block;">
                            <div class="link-failure-content">
                                <div class="link-failure-tips-conent">
                                    <div class="link-failure-img">
                                        <img src="/img/platform/login/unlink.svg" alt="">
                                    </div>
                                    <p class="link-failure-tips"><?php echo $LANG['UI_RESETPWD_LINK_INVALID'] ?>
                                        <br><?php echo $LANG['UI_RESETPWD_PAGE_INVALID'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <a href="/login.php"><button class="return-login"><?php echo $LANG['UI_EMAIL_RETURNLOGIN']?></button></a>
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
<script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<?php
if($lang != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="/assets/global/plugins/jquery-validation/js/localization/messages-'.
        $lang.'.js" type="text/javascript"></script>';
}
?>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="/lang/<?php echo $lang?>.js" type="text/javascript"></script>
<script src="/scripts/conf/config.js" type="text/javascript"></script>
<script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="/scripts/public/public.js" type="text/javascript"></script>
<script src="/scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="/scripts/libs/base64.min.js" type="text/javascript"></script>
<script src="/scripts/libs/md5.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="/assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="/scripts/platform/reset_pwd.js"></script>
</body>
</html>

