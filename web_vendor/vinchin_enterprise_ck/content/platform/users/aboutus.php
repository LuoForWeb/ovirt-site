<?php 
    include_once '../../../tpl/permission.php';
	
?>
<link href="./css/platform/setting.css" rel="stylesheet" type="text/css"/>

<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="icon-flag"></i><?php echo $LANG['UI_USER_ABOUT_US']?>
				</div>
			</div>
			<div class="portlet-body">
				<div class="row">
					<div class="col-md-12">
						<div id="aboutus" style="border: 1px solid #eee;line-height: 28px;">
							<div style="padding: 20px;border-bottom: 1px solid #eee;" class="">
								<h3 style="font-weight:bold; color:#333;font-size: 20px;margin: 0 0 30px;"><?php echo $LANG['UI_SETTINGS_SYSTEM_INFO']?></h3>
    				            <div class="">
    				                <label class="width120_cn width130_en"><i class="viconfont vicon-ge_product"></i> <?php echo $LANG['UI_SETTINGS_PRODUCT_NAME']?>：</label><span  id="systemname" ><?php echo $CONF['SYSTEM_INFO']['system_name']?></span>
    				            </div>
								<div class="">
    				                <label class="width120_cn width130_en"><i class="viconfont vicon-ge_copyright"></i> <?php echo $LANG['UI_SETTINGS_AUTH_VERSION']?>：</label><span id="version"><?php echo 'V' . substr(explode(' ', $CONF['SYSTEM_INFO']['version'])[1], 0, 3)?></span>
    				            </div>
    				            <div class="">
    				                <label class="width120_cn width130_en"><i class="viconfont vicon-ge_copyright"></i> <?php echo $LANG['UI_SETTINGS_AUTH_VERSION_DETAIL']?>：</label><span id="version"><?php echo $CONF['SYSTEM_INFO']['version']?></span>
    				            </div>
    				            
    				            <div class="">
    				                <label class="width120_cn width130_en"><i class="viconfont vicon-ge_copyrighted"></i> <?php echo $LANG['UI_SETTINGS_COPYRIGHT']?>：</label><span><?php echo $LANG['UI_SETTINGS_COPYRIGHT_TIPS']?></span>
    				            </div>
    				            
    				        </div>
    				        <div style="padding: 20px;border-bottom: 1px solid #eee; " class="">
								<h3 style="font-weight:bold; color:#333;font-size: 20px;margin: 0 0 30px"><?php echo $LANG['UI_SETTINGS_AUTH_CONTACT_US']?></h3>
    				            <div class="">
    				                <label class="width120_cn width130_en"><i class="viconfont vicon-ge_website"></i> <?php echo $LANG['UI_SETTINGS_AUTH_WEBSITE']?>：</label> <a id="website" href="<?php echo $CONF['REMOTE']['website']?>" target="_black"><?php echo $CONF['REMOTE']['website']?></a>
    				            </div>
    				            <div class="">
    				                <label class="width120_cn width130_en"><i class="viconfont vicon-ge_phone"></i> <?php echo $LANG['UI_SETTINGS_AUTH_PHONE']?>：</label><span id="phone"><?php echo $CONF['SYSTEM_INFO']['company_tel']?></span>
    				            </div>
    				            <div class="">
    				                <label class="width120_cn width130_en"><i class="viconfont vicon-ge_mailbox"></i> <?php echo $LANG['UI_SETTINGS_AUTH_EMAIL']?>：</label><label id="email"><?php echo $CONF['SYSTEM_INFO']['company_email']?></label>
    				            </div>
    				            
    				        </div>
						<?php 						
							if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
									echo '<div style="padding: 20px;" class="">
									<h3 style="font-weight:bold; color:#333;font-size: 20px; margin: 0 0 30px">'.$LANG['UI_SETTINGS_SOCIAL_PLATFORM'].'</h3>
									<ul class="list-inline" id="social">
										<li><a class="wechat" href="javascript:;" >
										<img class="vinchin-qrcode" src="../../../img/platform/vinchin-wexin.png" alt="'.$LANG['UI_SETTINGS_WEIXIN'].'">
										<i class="fa fa-wechat"></i>
									</a></li>
										<li><a target="_blank" href="http://weibo.com/u/6390473032"><i class="fa fa-weibo"></i></a></li>
										<li><a target="_blank" href="https://www.facebook.com/VinchinSoftware/"><i class="fa fa-facebook"></i></a></li>
										<li><a target="_blank" href="https://twitter.com/VinchinSoftware"><i class="fa fa-twitter"></i></a></li>
										<li><a target="_blank" href="https://www.linkedin.com/company/13404407/"><i class="fa fa-linkedin"></i></a></li>
										<li><a target="_blank" href="https://space.bilibili.com/512883078/video"><i style="font-size:24px;" class="iconfont icon-bilibili"></i></a></li>
										<li><a target="_blank" href="https://plus.google.com/b/116955405095790434139/116955405095790434139?hl=zh-CN"><i class="fa fa-google-plus"></i></a></li>
										
									</ul> 
								</div>';
							}else{
								echo '<div style="padding: 20px;" class="about-border_en">
								<h3 style="font-weight:bold; color:#333;font-size: 20px; margin: 0 0 30px">'.$LANG['UI_SETTINGS_SOCIAL_PLATFORM'].'</h3>
								<ul class="list-inline" id="social">
									<li><a target="_blank" href="https://www.linkedin.com/company/13404407/"><i class="fa fa-linkedin"></i></a></li>
            						<li><a target="_blank" href="https://www.facebook.com/VinchinSoftware/"><i class="fa fa-facebook"></i></a></li>
            						<li><a target="_blank" href="https://twitter.com/VinchinSoftware"><i class="fa fa-twitter"></i></a></li>
            						<li><a target="_blank" href="https://www.youtube.com/c/VinchinBackup"><i class="fa fa-youtube"></i></a></li>
									<li style="position: relative;"><a class="spicework" target="_blank" href="https://community.spiceworks.com/pages/vinchin/follow?utm_campaign=follow&utm_medium=button&utm_source=vinchin"></a></li>
            					</ul> 
    				        </div>
    				        <div style="padding: 20px 20px 10px;" class="">
								<h3 style="font-weight:bold; color:#333;font-size: 20px;margin: 0 0 30px">'.$LANG['UI_SETTINGS_USER_SURVER'].'</h3>
								<p>'.$LANG['UI_SETTINGS_USER_SURVER_DEAR'].'</p>
								<p style="max-width: 665px;margin:0;">'.$LANG['UI_SETTINGS_USER_SURVER_TIPS1'].'</p>
								<p style="max-width: 665px;font-size: 12px; font-style: italic;margin-bottom: 15px;">'.$LANG['UI_SETTINGS_USER_SURVER_TIPS2'].'</p>
    				            <ul class="list-inline" id="comment">
            						<li><a target="_blank" href="https://it.gtnr.io/0ZlQ0e7Pp"><i class="iconfont icon-gartner"></i></a></li>
            						<li><a target="_blank" href="https://www.g2.com/products/vinchin-backup-recovery/take_survey"><i class="iconfont icon-g2"></i></a></li>
            						<li><a target="_blank" href="https://reviews.capterra.com/new/180134"><i class="iconfont icon-capttera"></i></a></li>
            					</ul> 
							</div>';
							}
						?>
					</div>
				</div>
			</div>
		</div>
		</div>
		<!-- END VALIDATION STATES-->
	</div>
</div>
<!-- END PAGE CONTENT-->
<script src="./scripts/platform/users/aboutus.js" type="text/javascript"></script>