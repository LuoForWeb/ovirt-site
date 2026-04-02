<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<?php 
    session_start();
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
    
    header("Location: ./login.php");
?>
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
<link href="./assets/admin/pages/css/lock2.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="./assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="./assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="./assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link href="./assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="./assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<link href="./css/platform/main.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body>
<div class="page-lock">
	<div class="page-logo" style="display:flex;width:510px;padding-bottom:0;margin:0  0 10px;">
		<img src="./img/platform/logo-home.png" style="width: 100%;margin-left: -13px;" alt="">
	</div>
	<div class="page-body">
		<img class="page-lock-img" src="./img/platform/lock.png" alt="">
		<div class="page-lock-info">
			<h1 id="username"><?php  unset($_SESSION['userUUID']); echo $_SESSION['userName'];?></h1>
			<span class="email" >
			<?php echo $_SESSION['email'];?> </span>
			<span class="locked">
			<?php echo $LANG['UI_LOCK_LOCKED']?> </span>
			<br>
			<div class="alert margintop10 alert-danger display-hide">
    			<button class="close" data-close="alert"></button>
    			<span id="login_tip">
    			<?php echo $LANG['UI_LOCK_INPUT_RELOGIN']?> </span>
    		</div>
			<form class="form-inline" action="./">
				<div class="input-group input-medium">
					<input type="password" id="psd" maxlength="32" class="form-control" placeholder="<?php echo $LANG['UI_LOCK_INPUT_PASSWORD']?>">
					<span class="input-group-btn">
					<button type="button" id="login" class="btn blue icn-only"><i class="m-icon-swapright m-icon-white"></i></button>
					</span>
				</div>
				<!-- /input-group -->
				<div class="relogin">
					<a href="login.php">
					<?php echo $LANG['UI_LOCK_OTHER_USER']?> </a>
				</div>
			</form>
		</div>
	</div>
    <div class="copyright page-footer-custom" style="position:relative;">
		<p style="opacity: 0.7;font-size: 14px;color: #FFFFFF;">Copyright © 2015-2020 深信服科技股份有限公司 版权所有</p>
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
<script src="./assets/global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="./lang/<?php echo $_SESSION['language']; ?>.js" type="text/javascript"></script>
<script src="./scripts/conf/config.js" type="text/javascript"></script>
<!--  <script src="./scripts/public/public.js" type="text/javascript"></script> -->
<script src="./scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./scripts/platform/lock.js"></script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>