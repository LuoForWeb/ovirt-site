<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <?php
    session_start();
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
    $urlInfo=$_GET['key'];
    $token = v1_decrypt($urlInfo);
    $token=json_decode($token,true);
    //获取用户名
    $username=$token['username'];
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
    session_start();

    /**
     * 获取页面版本
     */
    $loginUrl = $app['LOGIN_INFO']['login_url'];
    $loginLayOut = $app['LOGIN_INFO']['login_layout'];
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
    <link href="./assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="./css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
    <link href="./css/platform/main.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="favicon.ico"/>
    <?php 
        echo '<link href="/css/platform/lang/' . $lang. '.css" rel="stylesheet" type="text/css"/>';
    ?>
</head>
<body>
    <div class="" style="height: 100vh">
        <?php
    // 已知布局
    $path = '';
    $bgImg = '';
    //布局
    switch ($loginLayOut){
        case 'leftLayout':
            $bgImg = '/img/platform/login/left_bg.svg';
            $cardStyle = 'cardLeft';
            $ContentStyle = 'leftLayOut-left-content';
            $loginContentStyle = 'edit-pwd-content';
            break;
        case 'mediumLayout':
            $bgImg = '/img/platform/login/medium_bg.svg';
            $cardStyle = 'cardMedium';
            $ContentStyle = 'mediumLayOut-medium-content';
            $loginContentStyle = 'edit-pwd-content';
            break;
        case 'rightLayout':
            $bgImg = '/img/platform/login/right_bg.svg';
            $cardStyle = 'cardMedium';
            $cardStyle = 'cardLeft';
            $ContentStyle = 'leftLayOut-left-content';
            $loginContentStyle = 'edit-pwd-content-right';
            break;
    }
    // 版本
    switch ($loginUrl){
        // 专业版
        case '/login_version/login_professional/':
            $path = '/login_version/login_professional/edit_pwd.php';
            break;
        // 企业版
        case '/login_version/login_project/':
            $path = '/login_version/login_project/edit_pwd.php';
            break;
        // 标准版
        case '/login_version/login_standard/':
            $path = '/login_version/login_standard/edit_pwd.php';
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
    include_once $_SERVER['DOCUMENT_ROOT'].$path;
    ?>
    </div>
<script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<?php
if($lang != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-'.
        $lang.'.js" type="text/javascript"></script>';
}
?>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="./lang/<?php echo $lang?>.js" type="text/javascript"></script>
<script src="./scripts/conf/config.js" type="text/javascript"></script>
<script src="./assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="./scripts/public/public.js" type="text/javascript"></script>
<script src="./scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="./scripts/libs/base64.min.js" type="text/javascript"></script>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="./scripts/platform/edit_pwd.js" type="text/javascript"></script>
</body>
</html>
