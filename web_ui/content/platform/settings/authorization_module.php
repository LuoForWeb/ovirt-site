<?php
    include_once '../../../tpl/permission.php';
    $userAllPermission = $_SESSION['permissionArr'];
?>
<!-- <link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/> -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div id="license" style="height: 100%;">
	<!-- 第一层级 -->
	<div class="row topdiv">
		<div class="col-md-4">
			<div id="authCard" class="basic-card">
				<div>
					<div id="statusDes"><?php echo $LANG['WEB_SYSTEM_LISENCE_UNAUTHORIZED'] ?></div>
				</div>
				<div>
					<div id="expireDays">0</div>
					<span class="expireDays mt-2"><?php echo $LANG['UI_REMAIN_DAYS'] ?></span>
				</div>
				<div>
					<div id="expireTime">----</div>
					<span class="expireTime"><?php echo $LANG['UI_SETTINGS_EXPIRE_TIME'] ?></span>
				</div>
				<div class="basic-card-back"></div>
				<div class="imgico">
				</div>
			</div>
		</div>
		<div class="col-md-8 pl0">
			<div id="authInfoCard" class="basic-card">
				<div class="top-title">
					<div class="top-title-left">
					</div>
					<span><?php echo $LANG['UI_SETTINGS_AUTH_INFO'] ?></span>
					<div class="top-title-right" id="authtip-box">
						<span> <?php echo $LANG['WEB_SYSTEM_LICENSE_HELP'] ?></span>
						<i id="authtips" class="viconfont vicon-bangzhuzhongxin"></i>
					</div>
				</div>
				<div class="card-info auth-card display-none">
					<div class="d-flex">
						<div class="card-info-left">
							<p>
								<span><?php echo $LANG['UI_SETTINGS_AUTH_USERNAME'] ?></span>
								<span id="customer"></span>
							</p>
							<p>
								<span id="softWareTitle"><?php echo $LANG['UI_SETTINGS_AUTH_VERSION'] ?></span>
								<span id="software"></span>
							</p>
							<p id="serviceTypeSpan">
								<span><?php echo $LANG['UI_SETTINGS_AUTH_SERVER_TYPE'] ?></span>
								<span id="serviceType"></span>
							</p>
							<p id="serviceTimeSpan">
								<span><?php echo $LANG['UI_SETTINGS_AUTH_SERVER_ENDTIME'] ?></span>
								<span id="serviceTime"></span>
							</p>
							<p id="softwareNameModelSpan">
								<span><?php echo $LANG['UI_MODEL'] ?></span>
								<span id="softwareNameModel"></span>
							</p>
						</div>
						<div class="card-info-right">
							<p>
								<span><?php echo $LANG['UI_SETTINGS_PRODUCT_NAME'] ?></span>
								<span id="softwareNameDiy"><?php echo $CONF['SYSTEM_INFO']['system_name'] ?></span>
							</p>
							<p class="no-ck-show">
								<span><?php echo $LANG['UI_SETTINGS_UPDATE_CURREN_VERSION'] ?></span>
								<span id="softwareVersion"></span>
							</p>
							<p class="ck-show display-none">
								<span><?php echo $LANG['UI_SETTINGS_AUTH_VERSION'] ?></span>
								<span><?php echo 'V' . substr(explode(' ', $CONF['SYSTEM_INFO']['version'])[1], 0, 3)?></span>
							</p>
							<p>
								<span><?php echo $LANG['UI_SETTINGS_COPYRIGHT'] ?></span>
								<span id="copyRight">
									<?php  
										if($CONF['SYSTEM_INFO']['vendor'] == 'backup-system' && ($CONF['lang'] != "zh-cn" && $CONF['lang'] != "zh-tw")){
											//如果改了配置文件 ，则显示配置文件中的公司名 如果没改的话就默认显示xxxx Co., Ltd. 这里不能提语言包 提了语言包英文版环境判断有问题
											if($CONF['SYSTEM_INFO']['copyright_name'] == 'xxxx有限公司'){
												echo "xxxx Co., Ltd.";
											}else{
												echo $CONF['SYSTEM_INFO']['copyright_name'];
											}
										}
										else{
											echo $CONF['SYSTEM_INFO']['copyright_name'];
										}
									?>
								
								</span>
							</p>
							<div class="d-flex" style="margin-top: 14px;">
								<div>
									<?php 
										if (in_array("p_authorization_module_download", $userAllPermission)) {
											echo '<button type="button" class="btn download_auth"><i class="viconfont vicon-ge_download"></i></span> '.$LANG['UI_SETTINGS_DOWNLOAD_FILE'].'</button>';
										}
									?>
								</div>
								<div>
									<?php 
										if (in_array("p_authorization_module_upload", $userAllPermission)) {
											echo '<button type="button" id="upload_auth" class="btn btn-primary upload_auth">
											<i class="viconfont vicon-shangchuan"></i></span> '.$LANG['UI_SETTINGS_UPLOAD_FILE'].'</button>';
										}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card-info no-auth-card display-none">
					<div class="no-auth-info">
						<div class="no-auth-title"><?php echo $LANG['WEB_SYSTEM_LICENSE_NO_AUTH_TIPS'] ?></div>
						<div class="d-flex justify-content-center">
							<div>
								<?php 
									if (in_array("p_authorization_module_download", $userAllPermission)) {
										echo '<button type="button" class="btn download_auth"><i class="viconfont vicon-ge_download"></i></span> '.$LANG['UI_SETTINGS_DOWNLOAD_FILE'].'</button>';
									}
								?>
							</div>
							<div>
								<?php 
									if (in_array("p_authorization_module_upload", $userAllPermission)) {
										echo '<button type="button" id="upload_noauth" class="btn btn-primary upload_auth">
										<i class="viconfont vicon-shangchuan"></i></span> '.$LANG['UI_SETTINGS_UPLOAD_FILE'].'</button>';
									}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- 第二模块 -->
	<div class="second-div display-none">
		<!-- 主控服务器 -->
		<div class="basic-card mt20 storage-module display-none">
			<div class="inner-card">
				<div class="box left-box d-flex">
					<i class="viconfont vicon-zhukongfuwuqi"></i>
					<div >
						<div class="title">
							<?php echo $LANG['UI_SYSTEM_LICENSE_MASTER_SERVER'] ?>
						</div>
						<div>
							<span> <?php echo $LANG['UI_SYSTEM_LICENSE_BACKUP_NODE_NUM'] ?> </span>
							<span id="node_num"></span>
						</div>
					</div>
				
					
				</div>
				<div class="box right-box file-auth-capacity display-none">
					<!-- 总备份容量 -->
					<div class="d-flex flex-stack mt5">
						<div class="capacity-card-title"><?php echo $LANG['WEB_SYSTEM_LICENSE_FILE_PRODUCTION_CAPACITY'] ?></div>
						<div class="d-flex flex-stack">
							<div>
								<?php echo $LANG['WEB_SYSTEM_LICENSE_VALID_CAPACITY'] ?>：
							</div>
							<div class="capacity-card-content">
								<span id="file_capacity_auth_valid_des">
									0B
								</span> /
								<span id="file_capacity_auth_total_des">
									0B
								</span>
							</div>
						</div>
					</div>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
						</div>
					</div>

				</div>
				<div class="box right-box auth-capacity display-none">
					<!-- 总备份容量 -->
					<div class="d-flex flex-stack mt5">
						<div class="capacity-card-title"><?php echo $LANG['WEB_SYSTEM_LICENSE_AUTH_TOTAL_CAPACITY'] ?></div>
						<div class="d-flex flex-stack">
							<div>
								<?php echo $LANG['WEB_SYSTEM_LICENSE_VALID_CAPACITY'] ?>：
							</div>
							<div class="capacity-card-content">
								<span id="capacity_auth_valid_des">
									0B
								</span> /
								<span id="capacity_auth_total_des">
									0B
								</span>
							</div>
						</div>
					</div>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
						</div>
					</div>

				</div>

			</div>
		
		</div>
		<!-- 备份模块 -->
		<div class="basic-card mt20 backup-module display-none">
			<div class="module-title-box">
				<div class="module-icon">
					
				</div>
				<div class="module-title">
					<?php echo $LANG['WEB_PLATFORM_DES_BACKUP'] ?>
				</div>
				<div class="module-auth-des display-none">
					<div>
						<span class="module-auth-title">
							<?php echo $LANG['UI_STORAGE_REMOTE_CAPACITY'] ?>
						</span>
						<span class="module-auth-num ml8 mr8">
							
						</span>
					</div>
				</div>
			</div>
			<div>
				<div class="module-content module-content-top">
					
				</div>
				<div class="extend-content display-none">
					<div class="module-split-line">
						<span class="extend-text"><?php echo $LANG['UI_SYSTEM_LICENSE_EXTEND_MODULE'] ?></span>
					</div>
				</div>
				<div class="module-content module-content-bottom">
					
				</div>
			</div>
		</div>
		<!-- 复制和实时模块 -->
		<div class="copy-cdp-row">
			<!-- 复制模块 -->
			<div class="basic-card mt20 copy-module display-none">
				<div class="module-title-box">
					<div class="module-icon">
						<i class="viconfont vicon-Frame-32">
						</i>
					</div>
					<div class="module-title">
						<?php echo $LANG['WEB_PLATFORM_REPLICATION'] ?>
					</div>
					<div class="module-auth-des display-none">
						<div>
							<span class="module-auth-title">
								<?php echo $LANG['UI_SYSTEM_LICENSE_COUNT'] ?>
							</span>
							<span class="module-auth-num">
							
							</span>
							
						</div>
					</div>
				</div>
				<div>
					<div class="module-content module-content-top">
					</div>
					<div class="extend-content display-none">
						<div class="module-split-line">
							<span class="extend-text"><?php echo $LANG['UI_SYSTEM_LICENSE_EXTEND_MODULE'] ?></span>
						</div>
					</div>
					<div class="module-content module-content-bottom">
					</div>
				</div>
			</div>
			<!-- 实时保护 -->
			<div class="basic-card mt20 cdp-module display-none">
				<div class="module-title-box">
					<div class="module-icon">
						<i class="viconfont vicon-cdpshishijieguan">
						</i>
					</div>
					<div class="module-title">
						<?php echo $LANG['UI_REPORT_CDP_PROTECT'] ?>
					</div>
					<div class="module-auth-des display-none">
						<div>
							<span class="module-auth-title">
								<?php echo $LANG['UI_STORAGE_REMOTE_CAPACITY'] ?>
							</span>
							<span class="module-auth-num ml8 mr8">
								
							</span>
						</div>
					</div>
					
				</div>
				<div>
					<div class="module-content module-content-top">
					</div>
					<div class="extend-content display-none">
						<div class="module-split-line">
							<span class="extend-text"><?php echo $LANG['UI_SYSTEM_LICENSE_EXTEND_MODULE'] ?></span>
						</div>
					</div>
					<div class="module-content module-content-bottom">
					</div>
				</div>
			</div>
		</div>
		<!-- 高级模块 -->
		<div class="basic-card advanced-module mt20 display-none">
			<div class="module-title-box">
				<div class="module-icon">
					
				</div>
				<div class="module-title">
					<?php echo $LANG['UI_SYSTEM_LICENSE_ADVANCE_MODULE'] ?>
				</div>
				<div class="module-auth-des display-none">
					<div>
						<span class="module-auth-title">
							<?php echo $LANG['UI_SYSTEM_LICENSE_COUNT'] ?>
						</span>
						<span class="module-auth-num">
						
						</span>
						
					</div>
				</div>
			</div>
			<div>
				<div class="module-content module-content-top">
				</div>
				
			</div>

		</div>
	</div>
	<!-- 第三层级 联系我们 -->
	<div class="basic-card display-none">
		<div class="top-title mb-20">
			<div class="top-title-left"></div>
			<span><?php echo $LANG['UI_SETTINGS_AUTH_CONTACT_US'] ?></span>
		</div>
		<div>
			<ul class="linonepoint">
				<li>
					<i ><img src="../../../img/platform/auth/company.svg" alt=""></i> <span><?php echo $LANG['UI_SETTINGS_AUTH_COMPANY'] ?></span><?php echo $CONF['SYSTEM_INFO']['copyright_name'] ?>
				</li>
				<li>
					<i><img src="../../../img/platform/auth/website.svg" alt=""></i>  <span> <?php echo $LANG['UI_SETTINGS_AUTH_WEBSITE'] ?></span><a href="<?php echo $CONF['REMOTE']['website'] ?>" target="_black"><?php echo $CONF['REMOTE']['website'] ?></a>
				</li>
				<li class="teleli">
					<i ><img src="../../../img/platform/auth/phonenumber.svg" alt=""></i> <span><?php echo $LANG['UI_SETTINGS_AUTH_PHONE'] ?></span><?php echo $CONF['SYSTEM_INFO']['company_tel'] ?>
				</li>
				<li>
					<i ><img src="../../../img/platform/auth/email.svg" alt=""></i><span> <?php echo $LANG['UI_SETTINGS_AUTH_EMAIL'] ?> </span><div id="emailinfo"><?php echo $CONF['SYSTEM_INFO']['company_email'] ?></div>
				</li>
				<li class="mb0  <?php if (($CONF['SYSTEM_INFO']['vendor'] != "vinchin") || ($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw")) {
					echo "display-none";
				} ?>">
					<i ><img src="../../../img/platform/auth/social.svg" alt=""></i> <span><?php echo $LANG['WEB_SYSTEM_LICENSE_SOCIAL_MEDIA'] ?></span>
					<div>
						<ul class="list-inline d-flex" id="social">
							<li>
								<a class="wechat" href="javascript:;" >
								<img class="vinchin-qrcode" src="../img/platform/vinchin-wexin.png" alt="<?php echo $LANG['UI_SETTINGS_WEIXIN'] ?>">
								<i class="viconfont vicon-wechat-fill"></i>
								</a>
							</li>
							<li class="icon-margin"><a target="_blank" href="http://weibo.com/u/6390473032"><i class="viconfont vicon-weibo-fill"></i></a></li>
							<li class="icon-margin"><a target="_blank" href="https://www.facebook.com/VinchinSoftware/"><i class="viconfont vicon-facebook-box-fill"></i></a></li>
							<li class="icon-margin"><a target="_blank" href="https://twitter.com/VinchinSoftware"><i class="viconfont vicon-twitter-fill"></i></a></li>
							<li class="icon-margin"><a target="_blank" href="https://www.linkedin.com/company/vinchin/"><i class="viconfont vicon-linkedin-box-fill"></i></a></li>
							<li class="icon-margin"><a target="_blank" href="https://space.bilibili.com/512883078/video"><i class="viconfont vicon-bilibili-fill"></i></a></li>
							<li class="icon-margin"><a target="_blank" href="https://plus.google.com/b/116955405095790434139/116955405095790434139?hl=zh-CN"><i class="viconfont vicon-google-fill"></i></a></li>
						</ul> 
					</div>
				</li>
			</ul>
		</div>
	</div>
	<div style="height: 20px;"></div>
	<!-- drawer开始 -->
	<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px" aria-labelledby="function_detail_drawer_title" aria-hidden="true" id="function_detail_drawer">
		<div class="drawer-content drawer-content-scrollable" role="document">
			<div class="drawer-header">
				<div class="drawer-title" id="function_detail_drawer_title"
					style="display:flex;justify-content:space-between;align-items:center">
					<div class="drawer-title-left">
						<i class="viconfont vicon-gongnengshouquan" style="margin-right: 3px;"></i>
						<span><?php echo $LANG['WEB_SYSTEM_LICENSE_FUNCTION_AUTH_DETAIL'] ?></span>
					</div>
					<div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
						<i class="viconfont vicon-guanbi"></i>
					</div>
				</div>
			</div>
			<!-- BEGIN DETAIL DRAWER BODY -->
			<div class="drawer-body" id="function_detail_drawer_body">
			</div>
			<!-- END DETAIL DRAWER BODY -->
			<div class="drawer-footer">
				<button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?></button>
			</div>

		</div>
	</div>
	<!-- drawer结束 -->
</div>
	
<!-- BEGIN PAGE LEVEL PLUGINS -->
<!-- <script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script> -->
<script type="text/javascript" src="./scripts/platform/settings/authorization_module.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	