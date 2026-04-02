<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/web_ng/api/public/load.php';
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $app = xphp_get_config('app','','',true);
    $version = $app['SYSTEM_INFO']['version'];
    if($_SESSION['language'] != null){
        $LANG = require $rootPath .'/lang/'.$_SESSION['language'].$app['ext'];
        $lang = $_SESSION['language'];
    }else{
        $LANG = require $rootPath .'/lang/'.$app['lang'].$app['ext'];
        $lang = $app['lang'];
    }
    $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
    $agent_info = (new \app\v1\resources\v0\logic\Client())->getDoloadAgentName([]);

    $token = json_decode(v1_decrypt(xphp_get_cache('Token_remember')),true);
    $remember = $token['remember'];
    $username = $token['username'];
    // 转义
    $password = htmlspecialchars($token['password'], ENT_QUOTES, 'UTF-8');

    /**
     * 获取页面版本
     */
    $loginUrl = $app['LOGIN_INFO']['login_url'];
    $loginLayOut = $app['LOGIN_INFO']['login_layout'];
?>
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
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="./assets/global/css/gfonts1.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/select2/select2.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="./css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
    <link href="./css/platform/main.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="favicon.ico"/>
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
<body>
    <div class="" style="height: 100vh">
        <?php
                // 系统背景
                $path = '';
                $bgImg = '';
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
                        $path = '/login_version/login_professional/login.php';
                        break;
                        // 企业版
                    case '/login_version/login_project/':
                        $path = '/login_version/login_project/login.php';
                        break;
                        // 标准版
                    case '/login_version/login_standard/':
                        $path = '/login_version/login_standard/login.php';
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
    <div id="agent_info" style="display: none;"><?php echo $agent_info ?></div>
    <input type="hidden" class="login_faild_num" value="<?php echo !empty($_SESSION['login_faild_num']) ? $_SESSION['login_faild_num'] : 0 ?>" />
    <input type="hidden" class="version" value="<?php echo $version?>" />
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
    <script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
    <script src="./assets/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="./assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
    <script src="./assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
    <script src="./scripts/platform/code.js"></script>
    <script src="scripts/platform/login.js" type="text/javascript"></script>
    <script>

        function refresh_code()
        {
            $('.code-canvas').attr('src', './code.php?r='+Math.round(new Date().getTime()) );
        }

    </script>
</body>
</html>
