<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php
    session_start();
    include_once $_SERVER['DOCUMENT_ROOT'] . '/web_ng/api/public/load.php';
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $app = xphp_get_config('app');
    if ($_SESSION['language'] != null) {
        $LANG = require $rootPath . '/lang/' . $_SESSION['language'] . $app['ext'];
        $lang = $_SESSION['language'];
    } else {
        $LANG = require $rootPath . '/lang/' . $app['lang'] . $app['ext'];
        $lang = $app['lang'];
    }
    $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
    /**
     * 特殊配置,主要用于第三方OEM版本
     * 所有配置文件均在根目录下special文件夹,包括配置文件,logo,图片等所有信息
     */
    $configFile = $app['SPECIAL_DIR'] . $app['SPECIAL_CONFIG'];
    if (file_exists($configFile)) {
        $content = file_get_contents($configFile);
        // 去除 BOM 字符
		$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $content = json_decode($content, true);
        setSpecialConfigRecursiveLogin($app, $content);
    }
    function setSpecialConfigRecursiveLogin(&$arrA, $arrB)
    {
        foreach ($arrB as $key => $value) {
            if (is_array($value)) {
                setSpecialConfigRecursiveLogin($arrA[$key], $value);
            } else {
                $arrA[$key] = $value;
            }
        }
    }

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
        $_SERVER['DOCUMENT_ROOT'] . '/img/platform/login/custom_login_bg.svg',
        $_SERVER['DOCUMENT_ROOT'] . '/img/platform/login/custom_login_bg.jpg',
        $_SERVER['DOCUMENT_ROOT'] . '/img/platform/login/custom_login_bg.png'
    );
    foreach ($fileNames as $fileName) {
        if (file_exists($fileName)) {
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
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description" />
    <meta content="vinchin.com" name="author" />
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="/assets/global/css/gfonts1.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME STYLES -->
    <link href="/assets/global/css/plugins.css" rel="stylesheet" type="text/css" />
    <link href="/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css" />
    <link href="/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color" />
    <link href="/assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css" />
    <link href="/css/platform/main.css" rel="stylesheet" type="text/css" />
    <link href="/css/platform/loginbackground.css" rel="stylesheet" type="text/css" />
    <link href="/css/viconfont/iconfont.css" rel="stylesheet" type="text/css" />
    <link href="./css/platform/login-divs.css" rel="stylesheet" type="text/css" />
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico" />
</head>

<body>
    <input type="text" class="systemLang display-none" value="<?php echo $_SESSION['language'] ?>">
    <div class="mask"
        style="width: 100%;height: 100%;position: absolute;top: 0;right: 0;z-index:1;opacity: 0.9;background:linear-gradient(to top , rgba(219,248,255,0.9) 0%, #FFFFFF 53%)">
    </div>
    <div class="content" style="position:absolute;width: 100%;height: 100%;top: 0;right: 0;background-image: url('./img/platform/bg-home-cics.png');filter: blur(5px);
            background-size: cover;">
        <!-- BEGIN LOGO -->
        <div class="contentlogin">
            <!-- END LOGO -->
            <!-- BEGIN LOGIN FORM -->

            <!-- END LOGIN FORM -->
        </div>
        <div class="contentpakage display-hide">
            <!-- BEGIN DOWNLOAD AGENT FORM -->

            <!-- END DOWNLOAD AGENT FORM -->
        </div>
        <!-- END LOGIN -->

    </div>
    <div class="login-div" style="display: flex;width: 1000px;margin: 0 auto;background: #fff;">
        <div style="z-index: 2;border-radius: 20px !important;box-shadow: 0px 4px 9px 0px rgba(7, 61, 106, 0.24);">
            <img src="./img/platform/login-cics.png" alt="" style="z-index: 2;">
        </div>
        <form class="lock-form" action="./">
            <input type="password" autocomplete="new-password" hidden>
            <div class="logo">
                <img src="/img/platform/logo.png" alt="" class="logo-default loginlogosize">
            </div>
            <h3 class="form-title">
                <?php echo $app['SYSTEM_INFO']['system_name'] ?></h3>
            <div class="tip" style="margin: 0 0 24px 0;"><?php echo $LANG['UI_PUBLIC_USER'] ?>
                <span id="username"><?php unset($_SESSION['userUUID']);
                echo $_SESSION['tenantusername']; ?></span>
                <?php echo $LANG['UI_LOCK_LOCKED'] ?>
                <?php echo $LANG['UI_LOCK_INPUT_RELOGIN'] ?>
            </div>
            <div class="form-group pwd-div">
                <div class="input-icon" style="position: relative;">
                    <i class="viconfont vicon-mima lock" style="margin-top:16px;color: #2A87C8;position: absolute;font-size:20px;"></i>
                    <i class="eye viconfont vicon-xianshimima"
                        style="position: absolute;display: none;right: 16px;color: #EEEFF2;font-size:20px;"></i>
                    <i class="eye-close viconfont vicon-yincangmima"
                        style="position: absolute;right: 16px;color: #EEEFF2;font-size:20px;"></i>
                    <input type="password" autocomplete="off" id="psd"
                        oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" maxlength="32"
                        class="form-control passwordInput" placeholder="<?php echo $LANG['UI_LOCK_INPUT_PASSWORD'] ?>">
                </div>
                <span class="error" style="color: #EC282C;font-weight: 400;font-size: 12px;"></span>
            </div>
            <div class="form-group">
                <span class="errorTip"></span>
            </div>

            <button type="button" id="login" class="btn icn-only login-btn" style="margin-bottom: 24px;">
                <?php echo $LANG['UI_LOGIN_BUTTON_LOGIN'] ?>
            </button>
            <div class="lockLine">
                <?php
                if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
                    echo '<span class="line"></span>';
                } else {
                    echo '<span class="line" style="width:127px;"></span>';
                }
                ?>
                <a href="/login.php"><?php echo $LANG['UI_LOCK_OTHER_USER'] ?> </a>
                <?php
                if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
                    echo '<span class="line"></span>';
                } else {
                    echo '<span class="line" style="width:127px;"></span>';
                }
                ?>
            </div>
        </form>

        <!-- BEGIN COPYRIGHT -->
        <div class="copyright" style="position: absolute;bottom: 2%;z-index:2;left: 50%;margin-left: -100.5px;">
                <p style="font-weight: 400;font-size: 12px;color: #666666;line-height: 14px;text-align: center">
                    <?php echo $app['SYSTEM_INFO']['copyright'] . " &copy; " . $app['SYSTEM_INFO']['years'] . " " .
                        $app['SYSTEM_INFO']['company'] . $app['SYSTEM_INFO']['version'];
                    ?>
                </p>
            </div>

            <!-- END COPYRIGHT -->
    </div>
    <script src="/assets/global/plugins/respond.min.js"></script>
    <script src="/assets/global/plugins/excanvas.min.js"></script>
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