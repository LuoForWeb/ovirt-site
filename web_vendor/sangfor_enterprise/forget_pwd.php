<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
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
    <meta charset="utf-8"/>
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
    <meta content="" name="author"/>
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="./assets/global/css/gfonts1.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="./assets/global/plugins/font-awesome/css/font-awesome-animation.css" type="text/css"/>
    <link href="./assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="./css/platform/main.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/select2/select2.css" rel="stylesheet" type="text/css"/>
    <link href="./css/platform/login-soft.css" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL SCRIPTS -->
    <!-- BEGIN THEME STYLES -->
    <link href="./assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link id="style_color" href="./assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="./css/platform/loginbackground.css" rel="stylesheet" type="text/css"/>
    <link href="./css/viconfont/iconfont.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
    <style type="text/css">
        .has-success .input-icon > i {
            color: #40BAC1;
        }

        .has-success .form-control,
        .has-success .form-control:hover,
        .has-success .form-control:focus {
            border-color: #40BAC1;
        }
    </style>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login">
<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
<div class="menu-toggler sidebar-toggler">
</div>
<!-- END SIDEBAR TOGGLER BUTTON -->
<!-- BEGIN LOGIN -->
<div>
    <div class="content">
        <!-- BEGIN LOGO -->
        <div style="text-align: center;">
            <img class="sangforIT" src="./img/platform/sangforIT.svg" alt=""
                 style="width: 10.66rem;position: absolute;left: 1.66rem;">
            <img src="./img/platform/tel.svg" alt=""
                 style="width: 1.33rem;position: absolute;top: 2.75rem;right: 9.16rem"> <span
                    style="font-size: 1rem;position: absolute;top:2.66rem;right: 1.91rem;color: #495060">400-630-6430</span>
            <a href="https://www.sangfor.com.cn/" target="_black"><img src="./img/platform/logo.svg" alt=""
                                                                       style="width: 3.94rem;margin-top: 3.5rem;"></a>
            <h3 style="margin-top: 1.35rem;font-size: 1.65rem;color: #3A3C3D;font-weight: bold;margin-bottom: 0.4rem;">
                深信服企业级数据备份与恢复系统</h3>
            <p style="margin-bottom: 0.54rem;font-size: 1rem;color: #3A3C3D;">Sangfor Enterprise Data Backup & Recovery
                System</p>
            <p style="font-size: 12px;color: #80848F;">版本号：V3.0 </p>
        </div>
        <div class="contentlogin">
            <!-- END LOGO -->
            <!-- BEGIN LOGIN FORM -->
            <form class="forget-form" action="index.html" method="post"
                  style="padding-top: 1.5rem;position:relative;width: 400px;margin: 0 auto;">
                <div class="edit-alert display-hide" style="position: absolute; color:#a94442; top: 5px; right: 35%;">
					<span id="edittips">
						<?php echo $LANG['UI_LOGIN_PASSWORD_EDIT_TIPS'] ?></span>
                </div>
                <div style="width: 400px;height: 50px;font-size: 14px;line-height: 50px;text-align: center;background: rgba(195,203,214,0.15);border-radius: 4px 4px 4px 4px;margin-bottom: 24px;">
                    请输入您的电子邮箱，系统将向你发送说明！
                </div>
                <div class="form-group" style="margin-bottom:0.7rem;">
                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                    <label class="control-label"
                           style="font-size: 12px;color: #80848F;"><?php echo $LANG['UI_LOGIN_USERNAME'] ?></label>
                    <div class="input-icon">
                        <i style="margin-top:14px; right:20px;"
                           class="viconfont vicon-a-account_circle-material11-01"></i>
                        <input style="border-radius: 4px !important;height: 40px;font-size: 14px;padding-left: 10px !important;"
                               class="form-control placeholder-no-fix" maxlength="64" type="text" autocomplete="off"
                               placeholder="请输入用户名" name="username"/>
                    </div>
                    <span class="usernameTips"></span>
                </div>
                <div class="form-group" style="margin-bottom:17px;">
                    <label class="control-label" style="font-size: 12px;color: #80848F;">邮箱</label>
                    <div class="input-icon">
                        <i style="margin-top:14px; right:20px;" class="viconfont vicon-email"></i>
                        <input style="border-radius: 4px !important;height: 40px;font-size: 14px;padding-left: 10px !important;"
                               class="form-control placeholder-no-fix passwordInput" maxlength="32" type="email"
                               autocomplete="off" placeholder="请输入邮箱地址" name="email"/>
                    </div>
                    <span class="emailTips"></span>
                </div>
                <div class="form-actions">
                    <div class="forget-password" style="float:right;margin-top:0 !important;">
                        <a href="./login.php"><h4
                                    style="font-size: 12px;color: #4D8DD9;text-align:right;margin-top: 0;margin-bottom: 0px;">
                                返回登录页面</a>
                    </div>
                </div>
                <div class="form-actions" style="margin-top: 5px;">
                    <button type="submit" class="btn blue login-btn btn-green"
                            style="width: 400px; background: #40BAC1; padding: 15px 0;font-size: 16px;color:#fff;border-radius: 4px !important;">
                        重置密码
                    </button>
                </div>
            </form>
            <!-- END LOGIN FORM -->
        </div>
        <!-- END LOGIN -->
        <!-- BEGIN COPYRIGHT -->
        <div class="copyright" style="position: absolute;font-size: 14px;bottom: 2%;opacity: 0.7;">
            <p style="opacity: 0.7;font-size: 1rem;color: #C3CBD6;text-align: center">Copyright © 2015-2025 深信服科技股份有限公司
                版权所有</p>
        </div>

        <!-- END COPYRIGHT -->
    </div>
    <!--        模态框-->
    <div class="container" id="myModal">
        <div class="modal fade" id="modalShow" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"
             style="padding:50px;top:34%;left:55.5%;">
            <div class="modal-dialog" role="document" style="width: 400px;height: 102px;">
                <div class="modal-content"
                     style="background: #FFFFFF;border-radius: 4px 4px 4px 4px;box-shadow:0 5px 15px rgb(0 0 0 / 5%);">
                    <div class="modal-body" style="padding: 40px;">
                        <div class="row">
                            <div class="faa-parent animated-hover"
                                 style="font-size: 14px;color:#495060;margin-left: 30%;margin-top: 10px"><i
                                        class="fa fa-spinner faa-spin animated"></i><span
                                        style="font-size: 16px"><?php echo $LANG['UI_FORGETPWD_EMAIL_SENDING']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="product">
        <div class="productName">深信服企业级数据备份与恢复系统</div>
        <div class="productDes">
            深信服企业级备份与恢复软件为您提供高性价比备份与容灾方案，通过虚拟化无代理备份、整机备份、数据库备份、非结构化数据备份，CDP实时备份实现全平台多层次数据备份，同时搭配数据验证，应急接管，瞬时恢复等功能助力用户一站式解决数据安全保护需求。
        </div>
    </div>
</div>
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
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
<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-validation/js/localization/messages_zh.js" type="text/javascript"></script>
<script src="./assets/global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/select2/select2.min.js"></script>
<script src="./assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="./lang/<?php echo $lang ?>.js" type="text/javascript"></script>
<script src="./scripts/conf/config.js" type="text/javascript"></script>
<script src="./scripts/public/public.js" type="text/javascript"></script>
<script src="./scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="./scripts/libs/base64.min.js" type="text/javascript"></script>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="./scripts/platform/forget_pwd.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>