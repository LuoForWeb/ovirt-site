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
	include_once $_SERVER['DOCUMENT_ROOT'].'/web_ng/api/public/load.php';
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
    $app = xphp_get_config('app');
    if($_SESSION['language']!=null){
        $LANG = require $rootPath .'/lang/'.$_SESSION['language'].$app['ext'];
        $lang = $_SESSION['language'];
    }else{
        $LANG = require $rootPath .'/lang/'.$app['lang'].$app['ext'];
        $lang = $app['lang'];
    }
    $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
    
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
InCloud DP</title>
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
<link href="./css/platform/loginbackground.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body style="background-color:#121C2D !important">
<input type="text" class="systemLang display-none" value="<?php echo $_SESSION['language']?>">
<div class="page-lock">
	<div class="page-logo">
		<a class="brand" href="<?php echo $app['REMOTE']['website']?>" target="_black">
		<?php 
    	   if($app['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType){
    	       //如果是免费版,使用专用logo
    	       echo '<img src="./img/platform/logo-free.png" alt="" class="logo-default loginlogosizefree">';
    	   }else{
    	       //如果是其他版本
    	       if(file_exists($app['SPECIAL_DIR'] . "logo.png")){
    	           $logo = "./special/logo.png";
    	       }else{
    	           $logo = "./img/platform/logo.png";
    	       }

               if ($app['lang'] == "en-us") {
                   $logo = "./img/platform/logo-en.png";
               }

               echo '<img src="' . $logo . '" alt="" class="logo-default loginlogosize">';

           }
    	?>
		</a>
	</div>
	<div class="page-body">
		<img class="page-lock-img" src="./img/platform/lock.png" alt="">
		<div class="page-lock-info">
			<h1 id="username"><?php unset($_SESSION['userUUID']); echo $_SESSION['tenantusername'];?></h1>
			<span class="email" >
			<?php echo $_SESSION['email'];?> </span>
			<span class="locked">
			<?php echo $LANG['UI_LOCK_LOCKED']?> </span>
			<br>
			<div class="alert margintop10 alert-danger display-hide">
    			<button type="button" class="close" data-close="alert"></button>
    			<span id="login_tip">
    			<?php echo $LANG['UI_LOCK_INPUT_RELOGIN']?> </span>
    		</div>
			<form class="form-inline" action="./">
				<div class="input-group input-medium">
					<input type="password" id="psd" maxlength="32" class="form-control" placeholder="<?php echo $LANG['UI_LOCK_INPUT_PASSWORD']?>"  autocomplete="off">
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
	<div class="page-footer-custom">
        <?php
        if($app['lang'] == "en-us"){
            echo "";
        }else{
            echo "版权所有 © 济南浪潮数据技术有限公司 保留所有权利。";
        }
        ?>
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
<script src="/assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
 <script src="./scripts/public/public.js" type="text/javascript"></script>
<script src="./scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./scripts/platform/lock.js"></script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>