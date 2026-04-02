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
        $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
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
    <link rel="stylesheet" href="/assets/global/plugins/font-awesome/css/font-awesome-animation.css" rel="stylesheet"
        type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/css/platform/main.css" rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/select2/select2.css" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL SCRIPTS -->
    <!-- BEGIN THEME STYLES -->
    <link href="/assets/global/css/plugins.css" rel="stylesheet" type="text/css" />
    <link href="/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css" />
    <link id="style_color" href="/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" />
    <link href="/assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css" />
    <link href="/css/platform/loginbackground.css" rel="stylesheet" type="text/css" />
    <link href="/css/viconfont/iconfont.css" rel="stylesheet" type="text/css" />
    <link href="./css/platform/login-divs.css" rel="stylesheet" type="text/css" />
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php
    echo '<link href="/css/platform/lang/' . $lang . '.css" rel="stylesheet" type="text/css"/>';
    ?>
</head>

<body>
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
        <form class="forget-form">
            <div class="logo">
                <img src="/img/platform/logo.png" alt="" class="logo-default loginlogosize">
            </div>
            <h3 class="form-title">
                <?php echo $app['SYSTEM_INFO']['system_name'] ?></h3>
            <div class="tip"><?php echo $LANG['UI_FORGETPWD_SENDEMAIL']; ?></div>

            <div class="form-group" style="margin-bottom:24px;">
                <div class="input-icon userInput">
                    <i class="viconfont vicon-yonghuming" style="color: #2A87C8 !important;font-size:20px"></i>
                    <input class="form-control" type="text" placeholder="<?php echo $LANG['UI_FORGETPWD_USERNAME']; ?>"
                        autocomplete="off" name="username" maxlength="64" id="username">
                    <span class="usernameTips"></span>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <div class="input-icon emailInput">
                    <i class="viconfont vicon-youxiang" style="color: #2A87C8 !important;font-size:20px"></i>
                    <input class="form-control" type="email"
                        placeholder="<?php echo $LANG['UI_FORGETPWD_EMAILADDRESS']; ?>" autocomplete="off" name="email"
                        id="email">
                    <span class="emailTips"></span>
                </div>
            </div>
            <div class="form-action">
                <button class="resetPwd" style="width: 400px;height:56px" type="submit" data-toggle="modal">重 置 密
                    码</button>
                <div class="forgetPwdLine" style="display: flex;justify-content: space-between;">
                    <div class="d-flex forgetpwdleft">
                        <a href="/login.php"><i class="viconfont vicon-a-Arrow-leftjiantouzuo"></i></a>
                        <a href="/login.php" class="ms-4"><?php echo $LANG['UI_FORGETPWD_RETURNLOGIN']; ?></a>
                    </div>

                    <div class="help d-flex popovers forgetpwdleft" data-content="
                            <p><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_ONE']; ?></p>
                            <p><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_TWO']; ?></p>
                            <p><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_THREE']; ?></p>
                            <p><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_FOUR']; ?></p>" data-original-title=""
                            title=""  data-trigger="hover"  data-container="body" data-placement="bottom"  data-html="true">
                        <a><?php echo $LANG['UI_USER_HELP'] ?></a>
                        <a class=" ms-5 display-none" style="display: inline;">
                            <i class="viconfont vicon-bangzhuzhongxin"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- BEGIN COPYRIGHT -->
        <!-- <div class="copyright" style="position: absolute;bottom: 2%;z-index:2;left: 50%;margin-left: -100.5px;">
            <p style="font-weight: 400;font-size: 12px;color: #666666;line-height: 14px;text-align: center">
                <?php echo $app['SYSTEM_INFO']['copyright'] . " &copy; " . $app['SYSTEM_INFO']['years'] . " " .
                    $app['SYSTEM_INFO']['company'] . $app['SYSTEM_INFO']['version'];
                ?>
            </p>
        </div> -->
            <div class="copyright">
                <p style="font-weight: 400;font-size: 12px;color: #666666;line-height: 14px;text-align: center">
                    <?php echo $app['SYSTEM_INFO']['copyright'] . " &copy; " . $app['SYSTEM_INFO']['years'] . " " .
                        $app['SYSTEM_INFO']['company'] . $app['SYSTEM_INFO']['version'];
                    ?>
                </p>
            </div>
        <!-- END COPYRIGHT -->
        
    </div>
    <!--        模态框-->
    <div class="container" id="myModal">
        <div class="modal fade" id="modalShow" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"
            style="padding:50px;top:10px">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <div class="faa-parent animated-hover"
                                style="font-size: 36px;color:#fff;margin-left: 30%;margin-top: 50px"> <i
                                    class="fa fa-spinner faa-spin animated"></i><span
                                    style="font-size: 16px"><?php echo $LANG['UI_FORGETPWD_EMAIL_SENDING']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    <script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
    <script src="/scripts/public/public.js" type="text/javascript"></script>
    <script src="/scripts/libs/json2.min.js" type="text/javascript"></script>
    <script src="/scripts/libs/base64.min.js" type="text/javascript"></script>
    <script src="/scripts/libs/md5.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="/scripts/platform/forget_pwd.js" type="text/javascript"></script>
</body>