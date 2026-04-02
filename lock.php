<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    include_once $rootPath . '/web_ng/api/public/load.php';
    $app = xphp_get_config('app');
    if($_SESSION['language']!=null){
        $LANG = require $rootPath .'/lang/'.$_SESSION['language'].$app['ext'];
        $lang = $_SESSION['language'];
    }else{
        $LANG = require $rootPath .'/lang/'.$app['lang'].$app['ext'];
        $lang = $app['lang'];
    }
    $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();

    $token = json_decode(v1_decrypt(xphp_get_cache('Token_remember')),true);
    $remember = $token['remember'];
    /**
     * 获取页面版本
     */
    $loginUrl = $app['LOGIN_INFO']['login_url'];
    $loginLayOut = $app['LOGIN_INFO']['login_layout'];

    /**
     * 背景
     */
    $bgImg = '';
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
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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
                $loginContentStyle = 'login-content';
                break;
            case 'mediumLayout':
                $bgImg = '/img/platform/login/medium_bg.svg';
                $cardStyle = 'cardMedium';
                $ContentStyle = 'mediumLayOut-medium-content';
                $loginContentStyle = 'login-content';
                break;
            case 'rightLayout':
                $bgImg = '/img/platform/login/right_bg.svg';
                $cardStyle = 'cardLeft';
                $ContentStyle = 'leftLayOut-left-content';
                $loginContentStyle = 'login-content-right';
                break;
        }
        // 版本
        switch ($loginUrl){
            // 专业版
            case '/login_version/login_professional/':
                $path = '/login_version/login_professional/lock.php';
                break;
            // 企业版
            case '/login_version/login_project/':
                $path = '/login_version/login_project/lock.php';
                break;
            // 标准版
            case '/login_version/login_standard/':
                $path = '/login_version/login_standard/lock.php';
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
        <input type="hidden" class="remember-password" data-remember="<?php echo $remember?>"/>
        <script src="/assets/global/plugins/respond.min.js"></script>
        <script src="/assets/global/plugins/excanvas.min.js"></script>
        <![endif]-->
        <script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
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
        <script src="/scripts/public/public.js" type="text/javascript"></script>
        <script src="/scripts/libs/json2.min.js" type="text/javascript"></script>
        <script src="/scripts/libs/md5.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
        <script src="/scripts/platform/lock.js"></script>
</body>
</html>