<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<?php 
    include_once $_SERVER['DOCUMENT_ROOT'].'/api/load.php';
    $CONF = Xphp::$_config;
    $LANG = require_once './lang/'.$CONF['lang'].$CONF['ext'];
    
    $systemHandler = Xphp::instance('SystemHandler');
    $softwareType = $systemHandler->getSoftwareType();  

	// 指定下语言包 因为是后台直接读取的
	Xphp::$_lang = $LANG;
	$agent = Xphp::instance('AgentHandler');
	$agent_info = $agent->getDoloadAgentName([]);


    $utils = Xphp::instance('utils');
    $token = $utils->decrypt($_COOKIE['Token']);
    $token = json_decode($token, true);
    
    $remember = $token['remember'];
    $username = $token['username'];
    $password = $token['password'];
    
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
InCloud DP
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
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login">
<div>
    <img alt="inspur" src="<?php
        if($CONF['lang'] == "en-us"){
            echo "../img/platform/inspur-blue-en.png";
        }else{
            echo "../img/platform/inspur-blue.png";
        }
    ?>" style = "position: absolute;
    top:4%;  
    left:2%;
    z-index: -1;">
</div>
<div class="login-backgroundGif">
    <img alt="logo" src="<?php
    if($CONF['lang'] == "en-us"){
        echo "../img/platform/login-background-en.png";
    }else{
        echo "../img/platform/login-background.gif";
    }
    ?>" style = "position: absolute;
        top:14%;
        left:11%;  
        z-index: -2;"></div>
<!-- BEGIN LOGO -->
<div class="logo" style="top: 128px;right: 10%;">
	<a href="<?php echo $CONF['REMOTE']['website']?>" target="_black">
	<?php 
// 	   if($CONF['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType){
// 	       //如果是免费版,使用专用logo
// 	       echo '<img src="./img/platform/logo-free.png" alt="" class="logo-default loginlogosizefree">';
// 	   }else{
// 	       //如果是其他版本
// 	       if(file_exists($CONF['SPECIAL_DIR'] . "logo.png")){
// 	           $logo = "./special/logo.png";
// 	       }else{
// 	           $logo = "./img/platform/logo.png";
// 	       }
// 	       echo '<img src="' . $logo . '" alt="" class="logo-default loginlogosize">';
// 	   }
// 	?>
	<img src="./img/platform/PDlogo.png" alt="" class="logo-default loginlogosizefree">
	</a>
</div>
<!-- END LOGO -->
<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
<div class="menu-toggler sidebar-toggler">
</div>
<!-- END SIDEBAR TOGGLER BUTTON -->
<!-- BEGIN LOGIN -->
<div >
	<div class="content contentlogin" style="top: 220px;height:385px">
       <!-- BEGIN LOGIN FORM -->
    	<form class="login-form" action="index.html" method="post">
    		
    		<div class="alert alert-danger display-hide" style="background: content-box;border: 0;text-align: center;">
                <button type="button" class="close" data-close="alert"></button>
                <span id="login_tip">
    			<?php echo $LANG['UI_LOGIN_TIP_USERNAME_AND_PASSWORD']?> </span>
            </div>
			<div class="alert alert-success display-hide" id="edittips"style="background: content-box;border: 0;text-align: center;">
				<span>
    				<?php echo $LANG['UI_LOGIN_PASSWORD_EDIT_TIPS']?> 
    			</span>
				
			</div>
    		<div class="form-group">
    			<!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
    			<label class="control-label visible-ie8 visible-ie9"><?php echo $LANG['UI_LOGIN_USERNAME']?></label>
    			<div class="input-icon">
    				<i class="fa fa-user"></i>
    				<input class="form-control placeholder-no-fix" value="<?php echo $username?>" maxlength="64" type="text" autocomplete="off"  name="username"/ style="border:0">
    			</div>
    		</div>
    		<div class="form-group">
    			<label class="control-label visible-ie8 visible-ie9"><?php echo $LANG['UI_LOGIN_PASSWORD']?></label>
    			<div class="input-icon">
    				<i class="fa fa-lock"></i>
                    <input type="password" hidden autocomplete="new-password" />
    				<input class="form-control placeholder-no-fix" value="<?php echo $password?>" maxlength="32" type="password" autocomplete="off"  name="password"/ style="border:0">
    			</div>
    		</div>
            <div class="form-group">
                <div class="code display-hide" id ="loginCode">
                    <input id="codeContent" type="text"  name="very_code" value="" placeholder="<?php echo $LANG['UI_LOGIN_ENTER_CODE']?>" class="input-val width50p" style="height: 34px;width: 120px;border: 0;"/>
                        <a href="javascript:refresh_code();">
                            <img src="./code.php" id="canvas" style="float: right">
                        </a>
                </div>
            </div>
            <div class="form-actions">

                <button type="submit" class="btn blue pull-right" style="padding: 7px 109px;background-color: #0062ac;">
                    <?php echo $LANG['UI_LOGIN_BUTTON_LOGIN']?>
                </button>
                <label class="checkbox">
                    <a href="javascript:;" id="download-agent">
                        <?php echo $LANG['UI_LOGIN_DOWNLOAD_AGENT']?></a>
                </label>
            </div>
    		
    	</form>
    	<!-- END LOGIN FORM -->
    </div>
	
	<div class="content contentpakage display-hide" style="min-height:342px;top: 220px;">
    	<!-- BEGIN DOWNLOAD AGENT FORM -->
    	<form class="agent-form form-horizontal" action="index.html" method="post">
    		<h3 class="form-title"><?php echo $LANG['UI_LOGIN_DOWNLOAD_AGENT']?></h3>
    		<div class="row">
    		    <div class="alert alert-danger display-hide nosupporttips"style="background: content-box;border: 0;text-align: center;">
<!--         			<button type="button" class="close" data-close="alert"></button> -->
        			<span>
        			<?php echo $LANG['UI_LOGIN_AGENT_NOSUPPORT']?> </span>
        		</div>
		        <div class="form-group">
					<label class="control-label col-md-3" style="padding: 7px 0 0;"> <?php echo $LANG['UI_LOGIN_AGENT_TYPE']?>
					</label>
					<div class="col-md-8">
						<select class="form-control agenttype-select" style="border:0;margin-top: 0px;">
						</select>
					</div>
				</div>
				<div class="form-group vmagent">
					<label class="control-label col-md-3" style="padding: 15px 0 0;"> <?php echo $LANG['UI_LOGIN_AGENT_VENDOR']?>
					</label>
					<div class="col-md-8">
						<select class="form-control vendor_select" style="border:0;margin-top: 9px;" >
						</select>
					</div>
				</div>
				<div class="form-group vmagent display-hide  vmagent_div">
					<label class="control-label col-md-3" style="padding: 16px 0 0;"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION']?>
					</label>
					<div class="col-md-8">
						<select class="form-control version_select" style="margin-top: 9px;">
						</select>
					</div>
				</div>
				<!--操作系统-->
				<div class="form-group osagent display-hide">
					<label
							class="control-label col-md-3" style="padding: 15px 0 0;"> <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM'] ?>
					</label>
					<div class="col-md-8">
						<select class="form-control ossystem_select" style="border:0;margin-top: 9px;" >
						</select>
					</div>
				</div>

				<div class="form-group fsagent display-hide">
					<label class="control-label col-md-3" style="padding: 16px 0 0;"> <?php echo $LANG['UI_LOGIN_AGENT_VERSION_ARC']?>
					</label>
					<div class="col-md-8">
						<select class="form-control filesystem_select" style="margin-top: 9px;">
						</select>
					</div>
				</div>
				<div class="form-group dbagent display-hide">
					<label class="control-label col-md-3" style="padding: 14px 0 0;"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?>
					</label>
					<div class="col-md-8">
						<select class="form-control dbtimingClient_select" style="margin-top: 9px;">
						</select>
					</div>
				</div>
				<div class="form-group dbcdp display-hide">
					<label class="control-label col-md-3" style="padding: 15px 0 0;"> <?php echo $LANG['WEB_PLATFORM_DES_OS']?>
					</label>
					<div class="col-md-8">
						<select class="form-control dbcdpClient_select" style="margin-top: 9px;">
						</select>
					</div>
				</div>
				
				<div class="form-group dbprotectagent display-hide">
					<label class="control-label col-md-3" style="padding: 16px 0 0;"> <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM']?>
					</label>
					<div class="col-md-8">
						<select class="form-control " id="dbprotectsystem" style="margin-top: 9px;">
						</select>
					</div>
				</div>
				
    		</div>
    		<div class="form-actions downloadback" style ="margin-right:-17px;margin-bottom: 24px;">
    			<button type="button" id="downloadback" class="btn">
    			<i class="m-icon-swapleft"></i> <?php echo $LANG['UI_LOGIN_BUTTON_BACK']?> </button>
    			<button type="button" id="download" class="btn green ml15" style ="background-color:#2977f7">
    			<?php echo $LANG['UI_LOGIN_AGENT_DOWNLOAD']?> <i class=" fa fa-download"></i>
    			</button>
    		</div>
    	</form>
    	<!-- END DOWNLOAD AGENT FORM -->
    </div>
    
    <div class="content contentpassword display-hide" style="height:342px;top: 220px;">
    	<!-- BEGIN DOWNLOAD AGENT FORM -->
    	<form action="#" id="editpass" class="form-horizontal">
    		<div class="alert alert-danger" style="background: content-box;border: 0;text-align: center;">
    			<span>
    			<?php echo $LANG['UI_LOGIN_PASSWORD_OVERDUE']?></span>
    		</div>
			<div class="form-body mt30">
				<div class="form-group">
					<label class="control-label col-md-4" style="padding: 14px 0 0;"><?php echo $LANG['UI_USER_OLD_PASS']?> 
					</label>
					<div class="col-md-7">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="password" maxlength="32" class="form-control" id="oldpass" name="oldpass" style="margin-top: 9px;" autocomplete="off"/>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-4" style="padding: 14px 0 0;"><?php echo $LANG['UI_USER_NEW_PASS']?> 
					</label>
					<div class="col-md-7">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="password" maxlength="32" class="form-control" id="password" name="editpassword" style="margin-top: 9px;" autocomplete="off"/>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-4" style="padding: 14px 0 0;"><?php echo $LANG['UI_USER_CONFIRM_PASS']?> 
					</label>
					<div class="col-md-7">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="password" maxlength="32"  class="form-control" name="rpassword" style="margin-top: 9px;" autocomplete="off" />
						</div>
					</div>
				</div>
			</div>
			<div class="form-actions">
				<div class="row">
					<div class="col-md-11 mt10 textalignr">
						<button type="button" id="editcancel" class="btn default"><?php echo $LANG['UI_LOGIN_BUTTON_BACK']?></button>
						<button type="button" id="editsubmit" class="btn blue"><?php echo $LANG['UI_PUBLIC_YES']?></button>
					</div>
				</div>
			</div>
		</form>
    	<!-- END DOWNLOAD AGENT FORM -->
    </div>
    

</div>
<!-- END LOGIN -->
<!-- BEGIN COPYRIGHT -->
<div class="copyright">
<?php
    if($CONF['lang'] == "en-us"){
        echo "";
    }else{
        echo "版权所有 © 济南浪潮数据技术有限公司 保留所有权利。";
    }
?>
	<p class="" style="margin-top: 5px;">
		<?php
        if($CONF['lang'] == "en-us"){
            echo "";
        }else{
            echo $CONF['SYSTEM_INFO']['recommend'];
        }
        ?>
	</p>
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
<div id="agent_info" style="display: none;"><?php echo $agent_info;?></div>
<input type="hidden" id="login_faild_num" value="<?php echo !empty($_SESSION['login_faild_num']) ? $_SESSION['login_faild_num'] : 0 ?>" />
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
        $('#canvas').attr('src', './code.php?r='+Math.round(new Date().getTime()) );
    }

</script>

</body>
<!-- END BODY -->
</html>