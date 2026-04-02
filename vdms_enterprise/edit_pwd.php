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
    $urlInfo = $_GET['key'];
    $token = v1_decrypt($urlInfo);
    $token = json_decode($token, true);
    //获取用户名
    $username = $token['username'];
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
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="./assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="./css/viconfont/iconfont.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="favicon.ico" />
    <?php
    echo '<link href="/css/platform/lang/' . $lang . '.css" rel="stylesheet" type="text/css"/>';
    ?>
    <style type="text/css">
        .input-icon>i {
            margin: 16px 2px 4px 16px;
        }

        .form-control:focus {
            border-color: #2A87C8 !important;
            outline: 0;
        }

        .login-div {
            margin-top: 10% !important;
        }

        @media (min-width: 1440px) and (max-width: 1919px) {
            .login-div {
                margin-top: 7% !important;
            }
        }

        .login-btn:hover {
            background: #2B98E4 !important;
        }

        .remPwd:checked {
            background-image: url('/img/platform/table-icon/checked-cics.png');
        }

        .remPwd:hover {
            border-color: #2A87C8;
        }

        .forget:hover {
            color: #2B98E4 !important;
        }

        .has-error .form-control {
            border-color: #EC282C;
        }

        .has-error .form-control:focus {
            border-color: #EC282C;
        }

        .eye:hover,
        .eye-close:hover {
            color: #DDE1E6 !important;
        }

        .has-error .help-block {
            color: #EC282C;
            font-weight: 400;
            font-size: 12px;
            margin: 0;
        }

        .form-control {
            width: 400px;
            height: 48px;
            padding-left: 48px;
        }

        .forget-form .form-action .resetPwd {
            background: #2A87C8;
            box-shadow: 0px 4px 9px 0px rgba(7, 61, 106, 0.24);
            font-weight: bold;
            font-size: 20px;
            color: #FFFFFF;
            line-height: 23px;
            margin: 0 0 32px 0;
        }

        .forget-form .form-action .resetPwd:hover {
            background: #2B98E4;
        }

        .form-control::placeholder {
            font-weight: 400;
            font-size: 16px;
            color: rgba(113, 128, 150, 0.4);
        }

        .forget-form .forgetPwdLine a {
            color: #2A87C8;
        }

        .eye:hover,
        .eye-close:hover {
            color: #DDE1E6 !important;
        }

        .tip {
            width: 400px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #EAF3FA;
            font-weight: 400;
            font-size: 14px;
            color: #666666;
            border-radius: 4px 4px 4px 4px;
        }

        a:hover {
            text-decoration: none;
            color: #2B98E4 !important;
            cursor: pointer;
        }

        .form-group {
            width: 400px;
            position: relative;
        }

        .input-icon i {
            position: absolute;
            margin-top: 12px;
            font-size: 20px;
            color: #2A87C8;
        }

        .input-icon .eye,
        .input-icon .eye-close {
            color: #EEEFF2;
            right: 16px;
        }

        .btn:focus {
            outline: none !important;
        }

        .alert-info {
            width: 400px;
            height: 40px;
            padding-top: 10px;
            text-align: center;
        }

        .loginlogosize {
            max-width: 700px;
            max-height: 30px;
        }
    </style>
</head>

<head>
    <?php
    // 获取名为 "show" 的参数的值
    $show = $_GET['show'] ?? '';
    ?>
</head>

<body>
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
        <form class="modifyPwd-form" style="padding-top: 1.5rem;position:relative;width: 500px;background: #fff;z-index:2;
                border-top-right-radius: 20px;border-bottom-right-radius: 20px;display: flex;flex-direction: column;align-items: center;justify-content: center;
                box-shadow: 0px 4px 9px 0px rgba(7,61,106,0.24);">
            <div class="logo" style="margin-bottom: 12px;">
                <img src="/img/platform/logo.png" alt="" class="logo-default loginlogosize">
            </div>
            <h3 class="form-title"
                style="margin: 0 0 24px 0;color: #283243;font-size: 24px;line-height: 32px;font-weight: 400;">
                <?php echo $app['SYSTEM_INFO']['system_name'] ?>
            </h3>
            <div class="userTips alert alert-block alert-info first-edit"
                style="display: <?php echo $show == 'first_login' ? 'block' : 'none'; ?>">
                <button type="button" class="close" data-dismiss="alert"></button>
                <p><?php echo $LANG['UI_PUBLIC_USER']; ?> <?php echo $username ?>
                    <?php echo $LANG['UI_FORCED_MODIFY_FIRST_TIPS']; ?>
                </p>
            </div>
            <div class="userTips alert alert-block alert-info overtime-edit"
                style="display: <?php echo $show == 'over_time' ? 'block' : 'none'; ?>">
                <button type="button" class="close" data-dismiss="alert"></button>
                <p><?php echo $LANG['UI_FORCED_MODIFY_TIPS']; ?></p>
            </div>
            <div class="form-group">
                <div class="input-icon userInput form-input form-oldpassword">
                    <i class="viconfont vicon-mima"></i>
<!--                    <i class="eye viconfont vicon-xianshimima">-->
<!--                    </i>-->
<!--                    <i class="eye-close viconfont vicon-yincangmima">-->
<!--                    </i>-->
                    <input class="form-control toggle-oldpassword" type="password" autocomplete="off"
                        placeholder="<?php echo $LANG['UI_USER_OLD_PASS']; ?>" autocomplete="off" id="oldpass"
                        name="oldpass" maxlength="32"
                        oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                    <span class="errorTipOldPass"></span>
                </div>
            </div>
            <div class="form-group">
                <div class="input-icon userInput form-input form-password">
                    <i class="viconfont vicon-mima"></i>
<!--                    <i class="eye viconfont vicon-xianshimima">-->
<!--                    </i>-->
<!--                    <i class="eye-close viconfont vicon-yincangmima">-->
<!--                    </i>-->
                    <input class="form-control passwordRule toggle-password" type="password" autocomplete="off"
                        placeholder="<?php echo $LANG['UI_USER_NEW_PASS']; ?>" autocomplete="off" id="password"
                        name="password" maxlength="32"
                        oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                    <span class="errorTipNewPass"></span>
                </div>
            </div>
            <div class="form-group form-repassword_en">
                <div class="input-icon userInput form-input form-repassword">
                    <i class="viconfont vicon-mima"></i>
<!--                    <i class="eye viconfont vicon-xianshimima">-->
<!--                    </i>-->
<!--                    <i class="eye-close viconfont vicon-yincangmima">-->
<!--                    </i>-->
                    <input class="form-control toggle-repassword" type="password" autocomplete="off"
                        placeholder="<?php echo $LANG['UI_RESETPWD_REENTER_NEWPASSWORD']; ?>" autocomplete="off"
                        id="newPassword" name="newPassword" maxlength="32"
                        oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                    <span class="errorTipNewPassSure"></span>
                </div>
            </div>
            <div class="form-action">
                <button class="modifyPwdSure btn icn-only blue login-btn btn-green"
                    style="letter-spacing: 4px;margin-bottom: 12px;width: 400px;font-weight: bold; background: #2A87C8; padding: 15px 0;font-size: 20px;color:#fff;border-radius: 4px !important;box-shadow: 0px 4px 9px 0px rgba(7,61,106,0.24);"
                    id="editsubmit" type="submit" data-toggle="modal"><?php echo $LANG['UI_RESETPWD_YES']; ?></button>
                <div class="modifyPwdLine">
                    <span class="line  width165_en"></span>
                    <a href="./login.php"><?php echo $LANG['UI_FORGETPWD_RETURNLOGIN']; ?></a>
                    <span class="line  width165_en"></span>
                </div>
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

    <script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="./assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="./assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
    <script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
    <?php
    if ($lang != "en-us") {
        //如果不是英文,加载语言包
        echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
            $lang . '.js" type="text/javascript"></script>';
    }
    ?>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="./lang/<?php echo $lang ?>.js" type="text/javascript"></script>
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