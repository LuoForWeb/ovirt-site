<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<?php 
//     $CONF = require_once './api/xphp/conf/config.php';
    
    
    include_once $_SERVER['DOCUMENT_ROOT'].'/api/load.php';
    $CONF = Xphp::$_config;
    $LANG = require_once './lang/'.$CONF['lang'].$CONF['ext'];
    
    $systemHandler = Xphp::instance('SystemHandler');
    $softwareType = $systemHandler->getSoftwareType();
    
    /**
     * 特殊配置,主要用于第三方OEM版本
     * 所有配置文件均在根目录下special文件夹,包括配置文件,logo,图片等所有信息
     */
    $configFile = $CONF['SPECIAL_DIR'] . $CONF['SPECIAL_CONFIG'];
    if(file_exists($configFile)){
        $content = file_get_contents($configFile);
        $content = json_decode($content, true);
        setSpecialConfigRecursive($CONF, $content);
    }
    
    function setSpecialConfigRecursive(&$arrA, $arrB){
        foreach ($arrB as $key => $value){
            if(is_array($value)){
                setSpecialConfigRecursive($arrA[$key], $value);
            }else{
                $arrA[$key] = $value;
            }
        }
    }
?>
<meta charset="utf-8"/>
<title>
<?php 
    if(file_exists($CONF['SYSTEM_NAME_FILE'])){
        //如果自定义系统名称存在
        echo file_get_contents($CONF['SYSTEM_NAME_FILE']);
    }else{
        //如果自定义系统名称不存在
        echo $CONF['SYSTEM_INFO']['system_name'];
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
<link href="./css/iconfont/iconfont.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
<style type="text/css">
    .has-success .input-icon > i{
    	color: #40BAC1;
    }
    .has-success .form-control,
    .has-success .form-control:hover,
    .has-success .form-control:focus{
        border-color: #40BAC1;
    }
</style>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login" >
<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
<div class="menu-toggler sidebar-toggler">
</div>
<!-- END SIDEBAR TOGGLER BUTTON -->
<!-- BEGIN LOGIN -->
<div >
	<div class="content" style="position: absolute;top: 50%;left: 50%;margin-top: -289px !important;margin-left: -250px;width: 500px; background:#fff;height: 578px; margin-top: 0;box-shadow: 0 80px 150px 50px rgba(23,88,120,0.50);border-radius: 8px !important;">
		 <!-- BEGIN LOGO -->
	    <div style="text-align: center;">
		    <a href="<?php echo $CONF['REMOTE']['website']?>" target="_black"><img src="./img/platform/logo.png" alt="" style="width: 80px;margin-top: 10px;"></a>
		    <h3 style="font-size: 24px;color: #4A4A4A;font-weight: bold;">深信服企业级数据备份与恢复系统</h3>
           	<p style="font-size: 12px;color: #4A4A4A;font-weight:bold;">Sangfor Enterprise Data Backup & Recovery System</p>
	    </div>
		<div class="contentlogin" >
            <!-- END LOGO -->
			<!-- BEGIN LOGIN FORM -->
			<form class="login-form " action="index.html" method="post" style="position:relative;width: 400px;margin: 0 auto;padding-top: 30px;">
				<div class="login-alert  display-hide" style="position: absolute; color:#a94442; top: 5px; right: 35%;">
					<span id="login_tip">
					<?php echo $LANG['UI_LOGIN_TIP_USERNAME_AND_PASSWORD']?> </span>
				</div>
				<div class="form-group" style="margin-bottom:17px;">
					<!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
					<label class="control-label" style="font-size: 12px;color: #80848F;"><?php echo $LANG['UI_LOGIN_USERNAME']?></label>
					<div class="input-icon">
						<i style="margin-top:14px; right:20px;" class="iconfont icon-username"></i>
						<input style="border-radius: 4px !important;height: 40px;font-size: 16px;padding-left: 10px !important;" class="form-control placeholder-no-fix" maxlength="64" type="text" autocomplete="off" placeholder="请输入用户名" name="username"/>
					</div>
				</div>
				<div class="form-group" style="margin-bottom:17px;">
					<label class="control-label" style="font-size: 12px;color: #80848F;"><?php echo $LANG['UI_LOGIN_PASSWORD']?></label>
					<div class="input-icon">
						<i style="margin-top:14px; right:20px;" class="iconfont icon-password"></i>
						<input style="border-radius: 4px !important;height: 40px;font-size: 16px;padding-left: 10px !important;" class="form-control placeholder-no-fix" maxlength="32" type="password" autocomplete="off" placeholder="请输入您的密码" name="password"/>
					</div>
				</div>
				<div class="form-actions">
					<label class="checkbox" style="font-size: 14px;color: #495060 !important;">
					<input type="checkbox" name="remember" /> <?php echo $LANG['UI_LOGIN_REMEMBER_USER']?> </label>
					<div class="forget-password" style="float:right;margin-top:0 !important;">
    					<h4 style="font-size: 14px;color: #495060;text-align:right;"> <?php echo $LANG['UI_LOGIN_FORGET_PASSWORD']?></h4>
    					<p class="" style="font-size: 12px;    color: #C3CBD6;">
    						<?php echo $LANG['UI_LOGIN_FORGET_PASSWORD_TIPS']?>
    					</p>
    				</div> 
				</div>
				<div class="form-actions">
					<a href="javascript:;" id="download-agent" style="color:#3EA0CC; padding:0; text-decoration:none;">
						<img src="./img/platform/download-agent.png" style="margin-top:-6px;">
						<span style="font-size: 12px;color: #4D8DD9;"><?php echo $LANG['UI_LOGIN_DOWNLOAD_AGENT']?></span></a>
				</div>
				<div class="form-actions" style="margin-top: 5px;">
					<button type="submit" class="btn blue " style="width: 400px; background: #40BAC1; padding: 15px 0;font-size: 14px;color:#fff;border-radius: 4px !important;">
					立即登录
					</button>
				</div>
				<div class="forget-password display-none">
					<h4> <?php echo $LANG['UI_LOGIN_FORGET_PASSWORD']?></h4>
					<p class="">
						<?php echo $LANG['UI_LOGIN_FORGET_PASSWORD_TIPS']?>
					</p>
				</div> 
			</form>
			<!-- END LOGIN FORM -->
		</div>
		<div class="contentpakage display-hide">
			<!-- BEGIN DOWNLOAD AGENT FORM -->
			<form class="agent-form form-horizontal" action="index.html" method="post" style="position:relative;width: 400px;margin: 0 auto;padding-top: 30px;">
				<h3 style="color:#333;" class="form-title"><?php echo $LANG['UI_LOGIN_DOWNLOAD_AGENT']?></h3>
				<div class="row">
					<div class="form-group">
        				<label style="color: #333;" class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_TYPE']?>
        				</label>
        				<div class="col-md-8">
        					<select class="form-control " id="agenttype" style="border-radius: 4px !important;">
        					</select>
        				</div>
        			</div>
        			<div class="form-group vmagent">
        				<label style="color: #333;" class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_VENDOR']?>
        				</label>
        				<div class="col-md-8">
        					<select class="form-control " id="vendor" style="border-radius: 4px !important;">
        					</select>
        				</div>
        			</div>
        			<div class="form-group vmagent display-hide" id="vmagent">
        				<label style="color: #333;" class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION']?>
        				</label>
        				<div class="col-md-8">
        					<select class="form-control " id="version" style="border-radius: 4px !important;">
        					</select>
        				</div>
        			</div>
        
        			<div class="form-group fsagent display-hide">
        				<label style="color: #333;" class="control-label col-md-3"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION_ARC']?>
        				</label>
        				<div class="col-md-8">
        					<select class="form-control " id="filesystem" style="border-radius: 4px !important;">
        					</select>
        				</div>
        			</div>
        			<div class="form-group dbagent display-hide">
        				<label style="color: #333;" class="control-label col-md-3"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?>
        				</label>
        				<div class="col-md-8">
        					<select class="form-control " id="dbtimingClient" style="border-radius: 4px !important;">
        					</select>
        				</div>
        			</div>
        			<div class="form-group dbcdp display-hide">
        				<label style="color: #333;" class="control-label col-md-3"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?>
        				</label>
        				<div class="col-md-8">
        					<select class="form-control " id="dbcdpClient" style="border-radius: 4px !important;">
        					</select>
        				</div>
        			</div>
				</div>
				<div class="form-actions downloadback" style="margin-right: -17px; padding-top:0 !important;">
					<button type="button" id="downloadback" class="btn" style="border-radius: 4px !important;">
					<i class="m-icon-swapleft"></i> <?php echo $LANG['UI_LOGIN_BUTTON_BACK']?> </button>
					<button type="button" id="download" class="btn green ml15" style="background: #40BAC1;border-radius: 4px !important;">
					<?php echo $LANG['UI_LOGIN_AGENT_DOWNLOAD']?> <i class=" fa fa-download"></i>
					</button>
				</div>
				<div class="display-hide nosupporttips" style="position: absolute; color:#a94442; top: 5px; right: 30%;">
					<span>
					<?php echo $LANG['UI_LOGIN_AGENT_NOSUPPORT']?> </span>
				</div>
			</form>
			<!-- END DOWNLOAD AGENT FORM -->
		</div>
    </div>
	
</div>
<!-- END LOGIN -->
<!-- BEGIN COPYRIGHT -->
<div class="copyright" style="position: absolute;font-size: 14px;bottom: 2%;opacity: 0.7;">
	<p style="opacity: 0.7;font-size: 14px;color: #FFFFFF;">Copyright © 2015-2020 深信服科技股份有限公司 版权所有</p>
</div>

<!-- END COPYRIGHT -->
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
<script src="./lang/<?php echo $CONF['lang']?>.js" type="text/javascript"></script>
<script src="./scripts/conf/config.js" type="text/javascript"></script>
<!-- <script src="./scripts/public/public.js" type="text/javascript"></script> -->
<script src="./scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="./scripts/libs/base64.min.js" type="text/javascript"></script>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="./scripts/platform/login-soft.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
jQuery(document).ready(function() {     
  Metronic.init(); // init metronic core components
  Layout.init(); // init current layout
  Login.init();
  Demo.init();
       // init background slide images
       $.backstretch([
        "./img/platform/bg-home.png",
        "./img/platform/bg-home.png",
        "./img/platform/bg-home.png",
        "./img/platform/bg-home.png"
        ], {
          fade: 0,
          duration: 8000
    }
    );
});
</script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>