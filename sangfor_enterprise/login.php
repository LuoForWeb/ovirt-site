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
    session_start();
    include_once $_SERVER['DOCUMENT_ROOT'].'/web_ng/api/public/load.php';
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $app = xphp_get_config('app','','',true);
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
    $password = $token['password'];
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
        ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <meta name="renderer" content="webkit">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="./assets/global/css/gfonts1.css" rel="stylesheet" type="text/css"/>
    <link href="./assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
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
    <link href="./css/platform/sangfor-login.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
    <style type="text/css">
        /*.has-success .input-icon > i {*/
        /*    color: #40BAC1;*/
        /*}*/

        /*.has-success .form-control,*/
        /*.has-success .form-control:hover,*/
        /*.has-success .form-control:focus {*/
        /*    border-color: #C3CBD6;*/
        /*}*/
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
            <a href="https://www.sangfor.com.cn/" target="_black"><img src="./img/platform/logo.svg" alt="" style="width: 3.94rem;margin-top: 3.5rem;"></a>
            <h3 style="margin-top: 1.35rem;font-size: 1.65rem;color: #495060;font-weight: bold;margin-bottom: 0.4rem;">
                深信服企业级数据备份与恢复系统</h3>
            <p style="margin-bottom: 0.54rem;font-size: 1rem;color: #3A3C3D;">
                Sangfor Enterprise Data Backup & Recovery
                System</p>
            <p style="font-size: 1rem;color: #80848F;">版本号：V3.0 </p>
        </div>
        <div class="contentlogin">
            <!-- END LOGO -->
            <!-- BEGIN LOGIN FORM -->
            <form class="login-form " action="index.html" method="post"
                  style="padding-top: 1.5rem;position:relative;width: 400px;margin: 0 auto;">
                <div class="edit-alert display-hide" style="position: absolute; color:#a94442; top: 5px; right: 35%;">
					<span id="edittips">
						<?php echo $LANG['UI_LOGIN_PASSWORD_EDIT_TIPS'] ?></span>
                </div>
                <div class="form-group" style="margin-bottom:0.7rem;">
                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                    <label class="control-label"
                           style="font-size: 12px;color: #80848F;"><?php echo $LANG['UI_LOGIN_USERNAME'] ?></label>
                    <div class="input-icon">
                        <i style="margin-top:14px; right:20px;"
                           class="viconfont vicon-a-account_circle-material11-01"></i>
                        <input style="border-radius: 4px !important;height: 40px;font-size: 14px;padding-left: 10px !important;"
                               class="form-control placeholder-no-fix" value="<?php echo $username ?>" maxlength="64"
                               type="text" autocomplete="off" placeholder="请输入用户名" name="username"/>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:17px;">
                    <label class="control-label"
                           style="font-size: 12px;color: #80848F;"><?php echo $LANG['UI_LOGIN_PASSWORD'] ?></label>
                    <div class="input-icon">
                        <i style="margin-top:14px; right:20px;" class="viconfont vicon-sangfor_pwd pwdIcon"></i>
                        <input style="border-radius: 4px !important;height: 40px;font-size: 14px;padding-left: 10px !important;"
                               class="form-control placeholder-no-fix passwordInput" value="<?php echo $password ?>"
                               maxlength="32" type="password" autocomplete="off" placeholder="请输入您的密码" name="password"/>
                    </div>
                    <div class="login-alert  display-hide" style="position: absolute; color:#FC4850;">
                        <span id="login_tip"></span>
                    </div>
                </div>
                <div class="form-actions Code" style="display: none">
                    <div id="loginCode">
                        <div style="width: 263px;display: inline-block;">
                            <input style="width: 263px;border-radius: 4px !important;height: 40px;font-size: 12px;padding-left: 10px !important;"
                                   class="form-control placeholder-no-fix input-val" maxlength="4" autocomplete="off"
                                   name="very_code"
                                   placeholder="请输入验证码"/>
                        </div>
                            <a href="javascript:refresh_code();">
                            <img src="./code.php" class="code-canvas" style="float: right;width:120px;">
                            </a>
                    </div>
                </div>
                <div class="form-actions actionLine">
                    <label class="checkbox" style="margin-top: 0px;">
                        <input class="remPwd" type="checkbox" style="margin-top: 5px;margin-left:0px;position:relative" name="remember"
                               data-check="<?php echo $remember ?>"/>
                        <h4 style="margin-top: 0px;color: #C3CBD6;font-size: 12px;display: inline-block;position:relative;top:-2px"><?php echo $LANG['UI_LOGIN_REMEMBER_USER'] ?></h4>
                    </label>
                    <div class="forget-password" style="float:right;margin-top:0 !important;">
                        <a href="./forget_pwd.php" style="color: #4D8DD9"><h4
                                    style="font-size: 12px;color: #4D8DD9;text-align:right;margin-top: 2px;margin-bottom: 0px;border-left: 1px solid #4D8DD9;padding-left: 6px;"> <?php echo $LANG['UI_LOGIN_FORGET_PASSWORD'] ?>
                        </a>
                    </div>
                    <div class="downLoad" style="float: right;margin-right: 6px;">
                        <i class="viconfont vicon-downloadPlugin"
                           style="color: #4D8DD9;margin-right: 4.3px;font-size: 12px;"></i>
                        <a href="javascript:;" id="download-agent"
                           style="float: right;color:#3EA0CC; padding:0;">
                            <span style="font-size: 12px;color: #4D8DD9;margin-top: 0px;margin-bottom: 0px;font-weight: 300;"><?php echo $LANG['UI_LOGIN_DOWNLOAD_AGENT'] ?></span>
                        </a>
                    </div>
                </div>
                <div class="form-actions" style="margin-top: 5px;">
                    <button type="submit" class="btn blue login-btn btn-green"
                            style="width: 400px; background: #17C1C5; padding: 15px 0;font-size: 16px;color:#fff;border-radius: 4px !important;">
                        立即登录
                    </button>
                </div>
            </form>
            <!-- END LOGIN FORM -->
        </div>
        <div class="contentpakage display-hide">
            <!-- BEGIN DOWNLOAD AGENT FORM -->
            <form class="agent-form form-horizontal" action="index.html" method="post"
                  style="position:relative;width: 400px;margin: 0 auto;padding-top: 30px;">
                <div style="width: 400px;height: 50px;background: rgba(195,203,214,0.15);border-radius: 4px !important;padding-top: 15px;margin-bottom: 2rem">
                    <h3 style="color:#333;font-size: 18px;margin-top: 0px;"
                        class="form-title"><?php echo $LANG['UI_LOGIN_DOWNLOAD_AGENT'] ?></h3>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label style="color: #80848F;"
                               class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_TYPE'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control agenttype-select">
                            </select>
                        </div>
                    </div>
                    <div class="form-group vmagent">
                        <label style="color: #80848F;"
                               class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_VENDOR'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control  vendor_select">
                            </select>
                        </div>
                    </div>
                    <div class="form-group vmagent display-hide vmagent_div">
                        <label style="color: #80848F;"
                               class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control version_select">
                            </select>
                        </div>
                    </div>
                    <!--操作系统-->
                    <div class="form-group osagent display-hide">
                        <label style="color: #80848F;"
                               class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control ossystem_select">
                            </select>
                        </div>
                    </div>

                    <div class="form-group fsagent display-hide">
                        <label style="color: #80848F;"
                               class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION_ARC'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control filesystem_select" >
                            </select>
                        </div>
                    </div>
                    <div class="form-group dbagent display-hide">
                        <label style="color: #80848F;"
                               class="control-label col-md-3"> <?php echo $LANG['WEB_PLATFORM_DES_OS'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control dbtimingClient_select">
                            </select>
                        </div>
                    </div>
                    <div class="form-group dbcdp display-hide">
                        <label style="color: #80848F;"
                               class="control-label col-md-3"> <?php echo $LANG['WEB_PLATFORM_DES_OS'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control dbcdpClient_select">
                            </select>
                        </div>
                    </div>
                    <div class="form-group dbprotectagent display-hide">
                        <label style="color: #80848F;"
                               class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control " id="dbprotectsystem">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-actions downloadback" style="padding-top:0 !important;margin-top: 2rem;">
                    <button type="button" id="downloadback" class="btn btn-white"
                            style="border-radius: 4px !important;width: 134px;height: 50px;font-size: 16px;margin-right: 10px;background: #F0F0F0">
                        <i class="viconfont vicon-arrow"></i> <?php echo $LANG['UI_LOGIN_BUTTON_BACK'] ?> </button>
                    <button type="button" id="download" class="btn green btn-green"
                            style="background: #40BAC1;font-size: 16px;border-radius: 4px !important;width: 252px;height: 50px;">
                        <i class=" viconfont vicon-downloadPlugin"></i> <?php echo $LANG['UI_LOGIN_AGENT_DOWNLOAD'] ?>
                    </button>
                </div>
                <div class="display-hide nosupporttips"
                     style="position: absolute; color:#FC4850; top: 5px; right: 30%;">
					<span>
					<?php echo $LANG['UI_LOGIN_AGENT_NOSUPPORT'] ?> </span>
                </div>
            </form>
            <!-- END DOWNLOAD AGENT FORM -->
        </div>


        <!-- END LOGIN -->
        <!-- BEGIN COPYRIGHT -->
        <div class="copyright" style="position: absolute;font-size: 14px;bottom: 2%;opacity: 0.7;">
            <p style="opacity: 0.7;font-size: 1rem;color: #C3CBD6;text-align: center">Copyright © 2015-2025 深信服科技股份有限公司
                版权所有</p>
        </div>

        <!-- END COPYRIGHT -->
    </div>
    <div class="product">
        <div class="productName">深信服企业级数据备份与恢复系统</div>
        <div class="productDes">
            深信服企业级备份与恢复软件为您提供高性价比备份与容灾方案，通过虚拟化无代理备份、整机备份、数据库备份、非结构化数据备份，CDP实时备份实现全平台多层次数据备份，同时搭配数据验证，应急接管，瞬时恢复等功能助力用户一站式解决数据安全保护需求。
        </div>
    </div>
    <div id="overlay" style="display: none">
        <div id="modal">
            <p><?php echo $LANG['UI_LOGIN_SUGGEST_EDIT_PASSWORD_TIP']?></p>
            <div class="buttonAction">
                <button id="editPwdButton"><?php echo $LANG['UI_PLATFORM_EDIT_PASSWORD']?></button>
                <button id="loginSuccessButton"><?php echo $LANG['UI_LOGIN_DIRECT_LOGIN'] ?></button>
            </div>
        </div>
    </div>
    
</div>
<div id="agent_info" style="display: none;"><?php echo $agent_info;?></div>
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
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="./lang/<?php echo $lang ?>.js" type="text/javascript"></script>
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
<script src="./scripts/platform/login-soft.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<!-- END JAVASCRIPTS -->
<script>

    function refresh_code()
    {
        $('.code-canvas').attr('src', './code.php?r='+Math.round(new Date().getTime()) );
    }
</script>
</body>
<!-- END BODY -->
</html>