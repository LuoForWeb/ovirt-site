<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    include_once $_SERVER['DOCUMENT_ROOT'].'/web_ng/api/public/load.php';
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $app = xphp_get_config('app');
    if ($_SESSION['language'] != null) {
        $LANG = require $rootPath .'/lang/'.$_SESSION['language'].$app['ext'];
        $lang = $_SESSION['language'];
    } else {
        $LANG = require $rootPath .'/lang/'.$app['lang'].$app['ext'];
        $lang = $app['lang'];
    }
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
        $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
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
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css"
        rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/assets/global/plugins/font-awesome/css/font-awesome-animation.css"
          rel="stylesheet" type="text/css" />
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css"
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
    <link href="/css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="/favicon.ico" />

    <?php
        echo '<link href="/css/platform/lang/' . $lang. '.css" rel="stylesheet" type="text/css"/>';
    ?>
</head>
<body>
    <div class="content">
        <div class="forget-content">
        <div class="rightImg"style="margin-left: 115px" >
            <?php
                if($lang == "zh-cn" || $lang == "zh-tw"){
                        echo '<img src="/img/platform/vinchinPic.png" alt="">';
                }
                else{
                        echo '<img src="/img/platform/vinchinPic_en.png" alt="">';
                }
                ?>
        </div>
        <div class="forgetPwd">
                <div class="logo">
                    <a href="<?php echo $app['REMOTE']['website']?>" target="_black">
                        <?php
                        if ($app['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType) {
                            // 如果是免费版,使用专用logo
                            echo '<img src="/system/login/img/logo-free.png" alt="" class="logo-default loginlogosizefree">';
                        } else {
                            // 如果是其他版本
                            if (file_exists($app['SPECIAL_DIR'] . "logo.png")) {
                                $logo = "/special/logo.png";
                            } else {
                                $logo = "/system/login/img/logoNew.png";
                            }
                            echo '<img src="' . $logo . '" alt="" class="logo-default loginlogosize">';
                        }
                        ?>
                    </a>
                </div>
                <form class="forget-form">
                    <h3 class="form-title"><?php echo $app['SYSTEM_INFO']['system_name']?></h3>
                    <div class="tip"><?php echo $LANG['UI_FORGETPWD_SENDEMAIL'];?></div>
                    <div class="userTips alert alert-block alert-info" style="margin-top: 10px;background-color: #003C40;color: #00E2DB;border-color:transparent;">
                        <button type="button" class="close" data-dismiss="alert"></button>
                       <p><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_ONE'];?></p>
                       <p><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_TWO'];?></p>
                       <p><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_THREE'];?></p>
                       <p><?php echo $LANG['UI_FORGETPWD_EMAIL_USERTIPS_FOUR'];?></p>
                    </div>
                    <div class="form-group">
                        <div class="input-icon userInput">
                            <i class="viconfont vicon-a-user"></i>
                            <input class="form-control" type="text" placeholder="<?php echo $LANG['UI_FORGETPWD_USERNAME']; ?>" autocomplete="off"
                                   name="username" maxlength="64" id="username">
                            <span class="usernameTips"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-icon emailInput">
                            <i class="viconfont vicon-a-email"></i>
                            <input class="form-control" type="email" placeholder="<?php echo $LANG['UI_FORGETPWD_EMAILADDRESS']; ?>" autocomplete="off"
                                   name="email" id="email">
                            <span class="emailTips"></span>
                        </div>
                    </div>
                    <div class="form-action">
                        <button class="resetPwd" type="submit" data-toggle="modal"><?php echo $LANG['UI_LOGIN_BUTTON_PASSWORD']; ?></button>
                        <div class="forgetPwdLine">
                            <div class="line width165_en"></div>
                            <a href="/login.php"><?php echo $LANG['UI_FORGETPWD_RETURNLOGIN']; ?></a>
                            <div class="line width165_en"></div>
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
                            <div class="faa-parent animated-hover" style="font-size: 36px;color:#fff;margin-left: 30%;margin-top: 50px"> <i class="fa fa-spinner faa-spin animated"></i><span style="font-size: 16px"><?php echo $LANG['UI_FORGETPWD_EMAIL_SENDING'];?></span></div>
                        </div>
                    </div>
                </div>
            </div>
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
    <script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
    <script src="/js/public.js" type="text/javascript"></script>
    <script src="/js/libs/json2.min.js" type="text/javascript"></script>
    <script src="/js/libs/base64.min.js" type="text/javascript"></script>
    <script src="/js/libs/md5.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="/system/login/js/forget_pwd.js" type="text/javascript"></script>
</body>
