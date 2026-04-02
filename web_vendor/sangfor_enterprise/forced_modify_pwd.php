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
    ?>
    <title>
        <?php
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
        <p style="font-size: 1rem;color: #80848F;">版本号：V3.0 </p>
    </div>
    <div class="modifyPwd">
        <form class="modifyPwd-form" style="padding-top: 1.5rem;position:relative;width: 400px;margin: 0 auto;">
            <div style="width: 400px;height: 50px;font-size: 14px;line-height: 50px;text-align: center;background: rgba(195,203,214,0.15);border-radius: 4px 4px 4px 4px;margin-bottom: 24px;">
                账户密码已过期，请修改密码重新登录
            </div>
            <div class="form-group" style="margin-bottom:0.7rem;">
                <div class="input-icon">
                    <i style="margin-top:14px; right:20px;" class="viconfont vicon-sangfor_pwd oldpwdIcon"></i>
                    <input class="form-control toggle-oldpassword" type="password" placeholder="请输入原密码"
                           autocomplete="off" id="oldpass" name="oldpass"
                           style="border-radius: 4px !important;height: 40px;font-size: 12px;padding-left: 10px !important;">
                    <span class="errorTipOldPass"></span>
                </div>
            </div>
            <div class="form-group">
                <div class="input-icon">
                    <i style="margin-top:14px; right:20px;" class="viconfont vicon-sangfor_pwd pwdIcon"></i>
                    <input class="form-control passwordRule toggle-password" type="password" placeholder="请输入新密码"
                           autocomplete="off" id="password" name="password"
                           style="border-radius: 4px !important;height: 40px;font-size: 12px;padding-left: 10px !important;">
                    <span class="errorTipNewPass"></span>
                </div>
            </div>
            <div class="form-group">
                <div class="input-icon">
                    <i style="margin-top:14px; right:20px;" class="viconfont vicon-sangfor_pwd repwdIcon"></i>
                    <input class="form-control toggle-repassword" type="password" placeholder="请再次输入新密码"
                           autocomplete="off" id="newPassword" name="newPassword"
                           style="border-radius: 4px !important;height: 40px;font-size: 12px;padding-left: 10px !important;">
                    <span class="errorTipNewPassSure"></span>
                </div>
            </div>
            <div class="modifyPwdLine" style="float:right;margin-top:0 !important;margin-bottom: 5px;">
                <h4 style="font-size: 12px;color: #4D8DD9;text-align:right;margin-top: 0;margin-bottom: 0px;padding-left: 6px;">
                    <a href="./login.php"><?php echo $LANG['UI_FORGETPWD_RETURNLOGIN']; ?></a>
                </h4>
            </div>
            <div class="form-action" style="margin-bottom: 5px">
                <button class="modifyPwdSure btn blue" id="editsubmit" type="submit" data-toggle="modal"
                        style="width: 400px; background: #40BAC1; padding: 15px 0;font-size: 16px;color:#fff;border-radius: 4px !important;"><?php echo $LANG['UI_RESETPWD_YES']; ?></button>
            </div>
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
<script src="./assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.history.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-validation/js/localization/messages_zh.js" type="text/javascript"></script>
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
<script src="./scripts/platform/forced_modify_pwd.js" type="text/javascript"></script>
</body>
