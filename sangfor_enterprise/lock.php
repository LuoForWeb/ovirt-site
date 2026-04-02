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
    <link href="./assets/global/css/gfonts1.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <!--    <link href="./css/platform/login.css" rel="stylesheet" type="text/css"/>-->
    <link href="./css/platform/login-soft.css" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME STYLES -->
    <link href="./assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="./assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="./css/platform/main.css" rel="stylesheet" type="text/css"/>
    <link href="./css/platform/loginbackground.css" rel="stylesheet" type="text/css"/>
    <link href="./css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
</head>
<body class="login">
<input type="text" class="systemLang display-none" value="<?php echo $_SESSION['language'] ?>">
<div class="content">
    <div style="text-align: center;">
        <img class="sangforIT" src="./img/platform/sangforIT.svg" alt=""
             style="width: 10.66rem;position: absolute;left: 1.66rem;">
        <img src="./img/platform/tel.svg" alt="" style="width: 1.33rem;position: absolute;top: 2.75rem;right: 9.16rem">
        <span style="font-size: 1rem;position: absolute;top:2.66rem;right: 1.91rem;color: #495060">400-630-6430</span>
        <a href="https://www.sangfor.com.cn/" target="_black"><img src="./img/platform/logo.svg" alt=""
                                                                   style="width: 3.94rem;margin-top: 3.5rem;"></a>
        <h3 style="margin-top: 1.35rem;font-size: 1.65rem;color: #3A3C3D;font-weight: bold;margin-bottom: 0.4rem;">
            深信服企业级数据备份与恢复系统</h3>
        <p style="margin-bottom: 0.54rem;font-size: 1rem;color: #3A3C3D;">Sangfor Enterprise Data Backup & Recovery
            System</p>
        <p style="font-size: 12px;color: #80848F;">版本号：V3.0 </p>
    </div>
    <div class="contentlogin">
        <form class="lock-form" action="./" method="post"
              style="padding-top: 1.5rem;position:relative;width: 400px;margin: 0 auto;">
            <div style="width: 400px;height: 50px;font-size: 14px;line-height: 50px;text-align: center;background: rgba(195,203,214,0.15);border-radius: 4px 4px 4px 4px;margin-bottom: 24px;">
                <?php echo $LANG['UI_PUBLIC_USER'] ?>
                <span id="username"><?php unset($_SESSION['userUUID']);
                    echo $_SESSION['tenantusername']; ?></span>
                <?php echo $LANG['UI_LOCK_LOCKED'] ?>
                <?php echo $LANG['UI_LOCK_INPUT_RELOGIN'] ?>
            </div>
            <div class="form-group" style="margin-bottom:0.7rem;">
                <label class="control-label" style="font-size: 12px;color: #80848F;">密码</label>
                <div class="input-icon">
                    <i style="margin-top:14px; right:20px;" class="viconfont vicon-sangfor_pwd pwdIcon"></i>
                    <input type="password" autocomplete="off"
                           style="border-radius: 4px !important;height: 40px;font-size: 14px;padding-left: 10px !important;"
                           id="psd" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"
                           maxlength="32" class="form-control passwordInput"
                           placeholder="<?php echo $LANG['UI_LOCK_INPUT_PASSWORD'] ?>">
                </div>
            </div>
            <span class="errorTip" style="color: #FC4850"></span>
            <div class="form-actions" style="padding-left:52px">
                <div style="float:right;margin-top:0 !important;margin-bottom: 20px;">
                    <a href="./login.php"><h4
                                style="font-size: 12px;color: #4D8DD9;text-align:right;margin-top: 0;margin-bottom: 0px;padding-left: 6px;">
                            使用其它账户登录
                    </a>
                </div>
            </div>
            <span class="input-group-btn">
                        <button type="button" id="login" class="btn  icn-only btn-green"
                                style="width: 400px; background: #40BAC1; padding: 15px 0;font-size: 16px;color:#fff;border-radius: 4px !important;">
                            立即登录
                        </button>
                        </span>
        </form>
    </div>
    <div class="copyright" style="position: absolute;font-size: 14px;bottom: 2%;opacity: 0.7;">
        <p style="opacity: 0.7;font-size: 1rem;color: #C3CBD6;text-align: center">Copyright © 2015-2025 深信服科技股份有限公司
            版权所有</p>
    </div>
</div>
<div class="product">
    <div class="productName">深信服企业级数据备份与恢复系统</div>
    <div class="productDes">
        深信服企业级备份与恢复软件为您提供高性价比备份与容灾方案，通过虚拟化无代理备份、整机备份、数据库备份、非结构化数据备份，CDP实时备份实现全平台多层次数据备份，同时搭配数据验证，应急接管，瞬时恢复等功能助力用户一站式解决数据安全保护需求。
    </div>
</div>
<input type="hidden" class="remember-password" data-remember="<?php echo $remember?>"/>
<script src="./assets/global/plugins/respond.min.js"></script>
<script src="./assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.history.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="./lang/<?php echo $_SESSION['language']; ?>.js" type="text/javascript"></script>
<script src="./scripts/conf/config.js" type="text/javascript"></script>
<script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="./scripts/public/public.js" type="text/javascript"></script>
<script src="./scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./scripts/platform/lock.js"></script>
</body>
</html>