<?php
include_once '../../../tpl/permission.php';
?>
<link href="./css/platform/aboutus.css" rel="stylesheet" type="text/css"/>

<!-- BEGIN PAGE CONTENT  -->
<div class="about-us">
	<div class="about-us__header">
		<i class="viconfont vicon-guanyu me-4"></i>
		<span class="about-us__header__text"><?php echo $LANG['UI_USER_ABOUT_US'] ?></span>
	</div>
	<div class="about-us__content">
		<div class="card product-card">
			<div class="card__hrader">
				<span class="decoration me-8"></span>
				<span class="card__hrader__title"><?php echo $LANG['UI_SETTINGS_SYSTEM_INFO'] ?></span>
			</div>
			<div class="card-form">
				<div class="card-form-item">
					<div class="card-form-item__label">
						<span class="text"><?php echo $LANG['UI_SETTINGS_PRODUCT_NAME'] ?></span>
					</div>
					<div class="card-form-item__value" id="systemname"><?php echo $CONF['SYSTEM_INFO']['system_name'] ?></div>
				</div>
				<div class="card-form-item">
					<div class="card-form-item__label">
						<span class="text"><?php echo $LANG['UI_LOGIN_AGENT_VERSION'] ?></span>
					</div>
					<div class="card-form-item__value" id="version"><?php echo $CONF['SYSTEM_INFO']['version'] ?></div>
				</div>
				<div class="card-form-item">
					<div class="card-form-item__label">
						<span class="text"><?php echo $LANG['UI_SETTINGS_COPYRIGHT'] ?></span>
					</div>
					<div class="card-form-item__value"><?php echo $LANG['UI_SETTINGS_COPYRIGHT_TIPS'] ?></div>
				</div>
			</div>
		</div>

		<div class="card contract-card mt-20">
			<div class="card__hrader">
				<span class="decoration me-8"></span>
				<span class="card__hrader__title"><?php echo $LANG['UI_SETTINGS_AUTH_CONTACT_US'] ?></span>
			</div>
			<div class="card-form">
				<div class="card-form-item">
					<div class="card-form-item__label">
						<i class="viconfont vicon-ge_website me-4"></i>
						<span class="text"><?php echo $LANG['UI_SETTINGS_AUTH_WEBSITE'] ?></span>
					</div>
					<div class="card-form-item__value">
						<a id="website" href="<?php echo $CONF['REMOTE']['website'] ?>" target="_black"><?php echo $CONF['REMOTE']['website'] ?></a>
					</div>
				</div>
				<div class="card-form-item teleitem">
					<div class="card-form-item__label">
						<i class="viconfont vicon-ge_phone me-4"></i>
						<span class="text"><?php echo $LANG['UI_SETTINGS_AUTH_PHONE'] ?></span>
					</div>
					<div class="card-form-item__value" id="phone"><?php echo $CONF['SYSTEM_INFO']['company_tel'] ?></div>
				</div>
				<div class="card-form-item">
					<div class="card-form-item__label">
						<i class="viconfont vicon-ge_mailbox me-4"></i>
						<span class="text"><?php echo $LANG['UI_SETTINGS_AUTH_EMAIL'] ?></span>
					</div>
					<div class="card-form-item__value" id="email"><?php echo $CONF['SYSTEM_INFO']['company_email'] ?></div>
				</div>
				<div class="card-form-item">
					<div class="card-form-item__label">
						<i class="viconfont vicon-social-media me-4"></i>
						<span class="text"><?php echo $LANG['UI_SETTINGS_SOCIAL_PLATFORM'] ?></span>
					</div>
					<div class="card-form-item__value social-media-wrapper">
						<span class="social-media-wrapper__item" <?php if($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") echo 'style="display:none"' ?>">
							<a target="_blank" href="http://weibo.com/u/6390473032">
								<i class="viconfont vicon-weibo-fill"></i>
							</a>
						</span>
						<span class="social-media-wrapper__item">
							<a target="_blank" href="https://www.facebook.com/VinchinSoftware/">
								<i class="viconfont vicon-facebook-box-fill"></i>
							</a>
						</span>
						<span class="social-media-wrapper__item">
							<a target="_blank" href="https://twitter.com/VinchinSoftware">
								<i class="viconfont vicon-twitter-fill"></i>
							</a>
						</span>
						<span class="social-media-wrapper__item">
							<a target="_blank" href="https://www.linkedin.com/company/vinchin/">
								<i class="viconfont vicon-linkedin-box-fill"></i>
							</a>
						</span>
						<span class="social-media-wrapper__item" <?php if($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") echo 'style="display:none"'  ?>">
							<a target="_blank" href="https://space.bilibili.com/512883078/video">
								<i class="viconfont vicon-bilibili-fill"></i>
							</a>
						</span>
						<span class="social-media-wrapper__item" style="display:none">
							<a target="_blank" href="https://plus.google.com/b/116955405095790434139/116955405095790434139?hl=zh-CN">
								<i class="viconfont vicon-google-fill"></i>
							</a>
						</span>
					</div>
				</div>
			</div>
			<div class="card-footer <?php if($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") echo 'display-none'?>">
				<div class="card-footer__img">
					<img src="../../../img/platform/vinchin-qrcode.svg" />
				</div>
				<span class="card-footer__text"><?php echo $LANG['UI_FOLLOW_THE_OFFICIAL_ACCOUNT'] ?></span>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT -->
<script src="./scripts/platform/users/aboutus.js" type="text/javascript"></script>