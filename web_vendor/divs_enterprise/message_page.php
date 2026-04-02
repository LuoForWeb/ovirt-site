<!DOCTYPE html>
<html lang="en">

<head>
    <?php
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
    // 获取名为 "show" 的参数的值
    $show = $_GET['show'] ?? '';

    /**
     * 特殊配置,主要用于第三方OEM版本
     * 所有配置文件均在根目录下special文件夹,包括配置文件,logo,图片等所有信息
     */
    $configFile = $app['SPECIAL_DIR'] . $app['SPECIAL_CONFIG'];
    if (file_exists($configFile)) {
        $content = file_get_contents($configFile);
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
    <link href="/css/platform/professional_login.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/css/gfonts1.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/css/platform/main.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/select2.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL SCRIPTS -->
    <!-- BEGIN THEME STYLES -->
    <link href="/assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css" />
    <link href="/assets/global/css/plugins.css" rel="stylesheet" type="text/css" />
    <link href="/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css" />
    <link id="style_color" href="/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" />
    <link href="/assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css" />
    <link href="./css/viconfont/iconfont.css" rel="stylesheet" type="text/css" />
    <link href="/css/platform/loginbackground.css" rel="stylesheet" type="text/css" />
    <link href="./css/platform/login-divs.css" rel="stylesheet" type="text/css" />
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="/favicon.ico" />
</head>

<body>
    <div class="reset-content" style="display: <?php echo $show == 'reset_success' ? 'block' : 'none'; ?>"></div>
    <div class="mask"
        style="width: 100%;height: 100%;position: absolute;top: 0;right: 0;z-index:1;opacity: 0.9;background:linear-gradient(to top , rgba(219,248,255,0.9) 0%, #FFFFFF 53%)">
    </div>
    <div class="content" style="position:absolute;width: 100%;height: 100%;left:0;top: 0;right: 0;background-image: url('./img/platform/bg-home-cics.png');filter: blur(5px);
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
        <form class="forget-form" style="padding-top: 1.5rem;position:relative;width: 500px;background: #fff;z-index:2;
                border-top-right-radius: 20px;border-bottom-right-radius: 20px;display: flex;flex-direction: column;align-items: center;justify-content: center;
                box-shadow: 0px 4px 9px 0px rgba(7,61,106,0.24);">
            <div class="logo" style="margin-bottom: 12px;">
                <img src="/img/platform/logo.png" alt="" class="logo-default loginlogosize">
            </div>
            <h3 class="form-title"
                style="margin: 0 0 24px 0;color: #283243;font-size: 24px;line-height: 32px;font-weight: 400;">
                <?php echo $app['SYSTEM_INFO']['system_name'] ?></h3>
            <div class="tip" style="margin: 0 0 24px 0;height:104px"><i class="viconfont vicon-youjian1"
                    style="color: #2A87C8;font-size: 54px;margin-right: 24px"></i><span
                    style="width: 55%;width: 62%;font-weight: 400;font-size: 16px;color: #333333;">系统已成功向您的邮箱发送说明，请移至邮箱操作！</span>
            </div>

            <div class="form-action" style="display: flex;flex-direction: column;">
                <a href="./login.php"><button class="resetPwd" style="width: 400px;height:56px" type="button"
                        data-toggle="modal">返 回 登 录 页</button></a>
                <a href="./forget_pwd.php"><button class="resetPwd go-email" style="width: 400px;height:56px"
                        type="button" data-toggle="modal">前 往 邮 箱</button></a>
            </div>
        </form>

        <!-- BEGIN COPYRIGHT -->
        <div class="copyright">
            <p style="font-weight: 400;font-size: 12px;color: #666666;line-height: 14px;text-align: center">
                <?php echo $app['SYSTEM_INFO']['copyright'] . " &copy; " . $app['SYSTEM_INFO']['years'] . " " .
                    $app['SYSTEM_INFO']['company'] . $app['SYSTEM_INFO']['version'];
                ?>
            </p>
        </div>
        <!-- END COPYRIGHT -->
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
    if ($lang != "en-us") {
        //如果不是英文,加载语言包
        echo '<script src="/assets/global/plugins/jquery-validation/js/localization/messages-' .
            $lang . '.js" type="text/javascript"></script>';
    }
    ?>
    <script src="/assets/global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/assets/global/plugins/select2/select2.min.js"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="/lang/<?php echo $lang ?>.js" type="text/javascript"></script>
    <script src="/scripts/conf/config.js" type="text/javascript"></script>
    <!-- <script src="./scripts/public/public.js" type="text/javascript"></script> -->
    <script src="/scripts/libs/json2.min.js" type="text/javascript"></script>
    <script src="/scripts/libs/base64.min.js" type="text/javascript"></script>
    <script src="/scripts/libs/md5.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="/scripts/platform/reset_pwd_success.js"></script>
</body>

</html>