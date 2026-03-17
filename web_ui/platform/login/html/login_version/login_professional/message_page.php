<!DOCTYPE html>
<html lang="en">
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
        // 获取名为 "show" 的参数的值
        $show = $_GET['show'] ?? '';

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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="renderer" content="webkit">
    <link href="/system/login/css/professional_login.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/css/gfonts1.css" rel="stylesheet"
          type="text/css" />
    <link
        href="/assets/global/plugins/font-awesome/css/font-awesome.min.css"
        rel="stylesheet" type="text/css" />
    <link
        href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css"
        rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css"
          rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/uniform/css/uniform.default.css"
          rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/css/main.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/select2.css"
          rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL SCRIPTS -->
    <!-- BEGIN THEME STYLES -->
    <link href="/assets/global/css/components.css" id="style_components"
          rel="stylesheet" type="text/css" />
    <link href="/assets/global/css/plugins.css" rel="stylesheet"
          type="text/css" />
    <link href="/assets/admin/layout/css/layout.css" rel="stylesheet"
          type="text/css" />
    <link id="style_color"
          href="/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet"
          type="text/css" />
    <link href="/assets/admin/layout/css/custom.css" rel="stylesheet"
          type="text/css" />
    <link href="/system/login/css/loginbackground.css" rel="stylesheet"
          type="text/css" />
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="/favicon.ico" />
</head>
<body>
<div class="sendSuccessContent" style="display: <?php echo $show == 'send_email' ? 'block' : 'none'; ?>">
    <div class="content">
    <div class="sendSuccess-content">
        <div class="rightImg" style="margin-left: 115px" ><img src="/img/platform/vinchinPic.png" alt="">
        </div>
        <div class="contentTip">
            <div class="logo"><img src="/img/platform/logoEmailSuccess.png" alt=""></div>
            <div class="tips">
                <div class="email"><img src="/img/platform/email.png" alt=""></div>
                <p class="emailTips"><?php echo $LANG['UI_EMAIL_SENDSUCCESS']; ?>
                    <br><?php echo $LANG['UI_EMAIL_TO_EMAILBOX']; ?>
                </p>
            </div>
            <div class="actions">
                <div class="returnBack"><a href="./forget_pwd.php"><?php echo $LANG['UI_EMAIL_RETURNBACK']?></a></div>
                <div class="returnLogin"><a href="./login.php"><?php echo $LANG['UI_EMAIL_RETURNLOGIN']?></a></div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="reset-content" style="display: <?php echo $show == 'reset_success' ? 'block' : 'none'; ?>">
    <div class="form-content">
        <div class="logo">
            <a href="<?php echo $app['REMOTE']['website']?>" target="_black">
                <?php
                if($app['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType){
                    //如果是免费版,使用专用logo
                    echo '<img src="/system/login/img/logo-free.png" alt="" class="logo-default loginlogosizefree">';
                }else{
                    //如果是其他版本
                    if(file_exists($app['SPECIAL_DIR'] . "logo.png")){
                        $logo = "/special/logo.png";
                    }else{
                        $logo = "/system/login/img/logoNew.png";
                    }
                    echo '<img src="' . $logo . '" alt="" class="logo-default loginlogosize">';
                }
                ?>
            </a>
        </div>
        <div class="tips">
            <div class="success"><img src="/system/login/img/resetPwdSuccess.png" alt=""></div>
            <p class="successTips"> <?php echo $LANG['UI_RESETPWDSUCCESS_TIPS']?>
                <br><span class="second"></span><?php echo $LANG['UI_RESETPWDSUCCESS_JUMP'];?>
            </p>
        </div>
        <div class="form-action">
            <a href="/system/login/html/login.php"><button class="btn" type="submit"><?php echo $LANG['UI_RESETPWDSUCCESS_LOGIN'];?></button></a>
            <div class="line"></div>
            <div class="returnBack"><a href="./forget_pwd.php"><?php echo $LANG['UI_RESETPWDSUCCESS_RETURNBACK'];?></a></div>
            <div class="line"></div>
        </div>
        <div class="image-shape1"></div>
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
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.history.min.js" type="text/javascript"></script>
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
<script src="/assets/global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
<script type="text/javascript" src="/assets/global/plugins/select2/select2.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="/lang/<?php echo $lang?>.js" type="text/javascript"></script>
<script src="/js/config.js" type="text/javascript"></script>
<!-- <script src="./scripts/public/public.js" type="text/javascript"></script> -->
<script src="/js/libs/json2.min.js" type="text/javascript"></script>
<script src="/js/libs/base64.min.js" type="text/javascript"></script>
<script src="/js/libs/md5.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="/assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="/system/login/js/reset_pwd_success.js"></script>
</body>
</html>