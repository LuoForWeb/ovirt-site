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
            $loginContentStyle = 'forget-pwd-content';
            break;
        case 'mediumLayout':
            $bgImg = '/img/platform/login/medium_bg.svg';
            $cardStyle = 'cardMedium';
            $ContentStyle = 'mediumLayOut-medium-content';
            $loginContentStyle = 'forget-pwd-content';
            break;
        case 'rightLayout':
            $bgImg = '/img/platform/login/right_bg.svg';
            $cardStyle = 'cardLeft';
            $ContentStyle = 'leftLayOut-left-content';
            $loginContentStyle = 'login-content-right';
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
    <link rel="stylesheet" href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" type="text/css"/>
    <link rel="stylesheet" href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" type="text/css" />
    <link rel="stylesheet" href="/assets/global/plugins/font-awesome/css/font-awesome-animation.css" type="text/css" />
    <link href="/css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
    <link rel="shortcut icon" href="/favicon.ico"/>
    <link rel="stylesheet" href="/css/platform/project_login.css">
</head>
<body style="background-image: url(<?php echo $bgImg ?>)">
<div style="height: 100vh">
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
                <form class="forget-form" action="index.html" method="post">
                    <div class="welcome-login-tip" style="display: <?php echo ($ContentStyle == 'mediumLayOut-medium-content') ? 'none' : 'block'; ?>"><?php echo $LANG['UI_LOGIN_BUTTON_PASSWORD'] ?></div>
                    <div class="logoMedium" style="display: <?php echo ($ContentStyle == 'mediumLayOut-medium-content') ? 'block' : 'none'; ?>">
                        <img src="/img/platform/login/logo.svg" alt="">
                    </div>
                    <div class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></div>
                    <div class="alert email-alert">
                        <?php echo $LANG['UI_FORGETPWD_SENDEMAIL'];?>
                    </div>
                    <div class="forget-pwd-tips">
                        <div><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_ONE'];?></div>
                        <div><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_TWO'];?></div>
                        <div><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_THREE'];?></div>
                        <div><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_FOUR'];?></div>
                    </div>
                    <div class="form-group">
                        <div class="control-label visible-ie8 visible-ie9"></div>
                        <div class="input-icon">
                            <i class="viconfont vicon-a-user user"></i>
                            <input class="form-control placeholder-no-fix username" maxlength="64"
                                   type="text" autocomplete="off" placeholder="<?php echo $LANG['UI_LOGIN_USERNAME'] ?>"
                                   name="username"/>
                            <div class="usernameTips"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="control-label visible-ie8 visible-ie9"></div>
                        <div class="input-icon">
                            <i class="viconfont vicon-a-email"></i>
                            <input class="form-control placeholder-no-fix passwordInput"
                                   type="email"
                                   autocomplete="off"
                                   placeholder="<?php echo $LANG['UI_FORGETPWD_EMAILADDRESS']; ?>"
                                   name="email"/>
                            <div class="emailTips"></div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="submit_div">
                            <button type="submit" class="resetPwd">
                                <?php echo $LANG['UI_LOGIN_BUTTON_PASSWORD']; ?>
                            </button>
                            <div class="login-line">
                                <span class="line"></span>
                                <a href="/login.php"><?php echo $LANG['UI_FORGETPWD_RETURNLOGIN']; ?></a>
                                <span class="line"></span>
                            </div>
                        </div>
                    </div>
                </form>
        </div>
        </div>
</div>
        <!--        模态框-->
    <div class="container" id="myModal">
            <div class="modal fade" id="modalShow" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel" style="padding:50px;top:10px">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row">
                                <div class="faa-parent animated-hover" style="font-size: 36px;color:#fff;margin-left: 30%;margin-top: 50px"> <i class="fa fa-spinner faa-spin animated"></i>
                                    <span style="font-size: 16px;color: #3A73FB;">
                                        <?php echo $LANG['UI_FORGETPWD_EMAIL_SENDING'];?>
                                    </span>
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

<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<?php
if($lang != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="/assets/global/plugins/jquery-validation/js/localization/messages-'.
        $lang.'.js" type="text/javascript"></script>';
}
?>
<script src="/lang/<?php echo $lang?>.js" type="text/javascript"></script>
<script src="/scripts/conf/config.js" type="text/javascript"></script>
<script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="/scripts/public/public.js" type="text/javascript"></script>
<script src="/scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="/scripts/libs/base64.min.js" type="text/javascript"></script>
<script src="/scripts/libs/md5.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="/assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="/scripts/platform/forget_pwd.js" type="text/javascript"></script>
</body>
</html>
