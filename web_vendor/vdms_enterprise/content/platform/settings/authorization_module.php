<?php include_once '../../../tpl/permission.php';?>
<!-- <link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/> -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div id="license" style="height: 100%;">
	<!-- 第一层级 -->
	<div class="row mb-20 topdiv">
		<div class="col-md-4">
			<div id="authCard" class="basic-card">
				<div>
					<div id="statusDes"><?php echo $LANG['WEB_SYSTEM_LISENCE_UNAUTHORIZED']?></div>
				</div>
				<div>
					<div id="expireDays">0</div>
					<span class="expireDays mt-2"><?php echo $LANG['UI_REMAIN_DAYS']?></span>
				</div>
				<div>
					<div id="expireTime">----</div>
					<span class="expireTime"><?php echo $LANG['UI_SETTINGS_EXPIRE_TIME']?></span>
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
					<span><?php echo $LANG['UI_SETTINGS_AUTH_INFO']?></span>
					<div class="top-title-right"  data-toggle="popover" data-placement="bottom" data-html="true" data-content="<?php echo $LANG['UI_SETTINGS_AUTH_TIPS'] ?> " style ="display:none"  >
						<span> <?php echo $LANG['WEB_SYSTEM_LICENSE_HELP']?></span>
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
								<span><?php echo $LANG['UI_SETTINGS_AUTH_VERSION'] ?></span>
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
								<span id="softwareNameDiy"><?php echo $CONF['SYSTEM_INFO']['system_name']?></span>
							</p>
							<p>
								<span><?php echo $LANG['UI_SETTINGS_UPDATE_CURREN_VERSION'] ?></span>
								<span id="softwareVersion"></span>
							</p>
							<p>
								<span><?php echo $LANG['UI_SETTINGS_COPYRIGHT'] ?></span>
								<span><?php echo $CONF['SYSTEM_INFO']['copyright_name']?></span>
							</p>
							<div class="d-flex">
								<div>
									<button type="button" class="btn download_auth">
										<i class="viconfont vicon-ge_download"></i></span> <?php echo $LANG['UI_SETTINGS_DOWNLOAD_FILE']?>
									</button>
								</div>
								<div>
									<button type="button" id="upload_auth" class="btn btn-primary upload_auth">
										<i class="viconfont vicon-shangchuan"></i></span> <?php echo $LANG['UI_SETTINGS_UPLOAD_FILE']?>
									</button>
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
								<button type="button" class="btn download_auth">
									<i class="viconfont vicon-ge_download"></i></span> <?php echo $LANG['UI_SETTINGS_DOWNLOAD_FILE']?>
								</button>
							</div>
							<div>
								<button type="button" id="upload_noauth" class="btn btn-primary upload_auth">
									<i class="viconfont vicon-shangchuan"></i></span> <?php echo $LANG['UI_SETTINGS_UPLOAD_FILE']?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- 第二层级--详细授权信息 -->
	<div class="basic-card mb-20 second-div display-none">
		<!-- 容量模块 -->
		<div class="auth-capacity display-none" style="margin-bottom: 30px;">
			<div class="top-title mb-20" style="display: none;">
				<div class="top-title-left"></div>
				<span><?php echo $LANG['WEB_SYSTEM_LICENSE_CAPACITY_AUTH'] ?></span>
			</div>
			<div class="d-flex flex-stack">
				<!-- 总备份容量 定时容量模块 -->
				<div id="capacity_auth" class="basic-capacity-card display-none">
					<div class="d-flex flex-stack">
						<div class="capacity-card-title"><?php echo $LANG['WEB_SYSTEM_LICENSE_BACKUP_CAPACITY'] ?></div>
						<div class="lh22 d-flex">
							<div>
								<?php echo $LANG['WEB_SYSTEM_LICENSE_VALID_CAPACITY'] ?>:
							</div>
							<div class="capacity-card-num" id="capacity_auth_valid">
								
							</div>
						</div>
					</div>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
						</div>
					</div>
					<div class="d-flex flex-stack bottom-des">
						<div class="d-flex">
							<div class="capacity-usedes">
								<?php echo $LANG['WEB_SYSTEM_LICENSE_USED'] ?>:
								<span id="capacity_auth_used_des">
									
								</span>
							</div>
							<span id="capacity_auth_used_percent">
								
							</span>
						</div>
						<div>
							<?php echo $LANG['WEB_SYSTEM_LICENSE_TOTAL_CAPACITY'] ?>:
							<span id="capacity_auth_total_des">
								
							</span>
						</div>
					</div>
				</div>
				<!-- 实时容量模块 -->
				<div id="realtime_capacity_auth" class="basic-capacity-card ml20 display-none">
					<div class="d-flex flex-stack">
						<div class="capacity-card-title"><?php echo $LANG['WEB_SYSTEM_LICENSE_CONTINUOUS_DATA_CAPACITY'] ?></div>
						<div class="lh22 d-flex">
							<div>
								<?php echo $LANG['WEB_SYSTEM_LICENSE_VALID_CAPACITY'] ?>:
							</div>
							<div class="capacity-card-num" id="realtime_capacity_auth_valid">
								
							</div>
						</div>
					</div>
					<div class="progress">
						<div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
						</div>
					</div>
					<div class="d-flex flex-stack bottom-des">
						<div class="d-flex">
							<div class="capacity-usedes">
								<?php echo $LANG['WEB_SYSTEM_LICENSE_USED'] ?>:
								<span id="realtime_capacity_auth_used_des">
									
								</span>
							</div>
							<span id="realtime_capacity_auth_used_percent">
								
							</span>
						</div>
						<div>
							<?php echo $LANG['WEB_SYSTEM_LICENSE_TOTAL_CAPACITY'] ?>:
							<span id="realtime_capacity_auth_total_des">
								
							</span>
						</div>
					</div>
				</div>
				<!-- 文件生产容量 -->
				<div id="fs_capacity_auth" class="basic-capacity-card display-none">
					<div class="d-flex flex-stack">
						<div class="capacity-card-title"><?php echo $LANG['WEB_SYSTEM_LICENSE_FILE_PRODUCTION_CAPACITY'] ?></div>
						<div class="lh22 d-flex">
							<div>
								<?php echo $LANG['WEB_SYSTEM_LICENSE_VALID_CAPACITY'] ?>:
							</div>
							<div class="capacity-card-num" id="fs_capacity_auth_valid">
								
							</div>
						</div>
					</div>
					<div class="mt15 d-flex file-capacity-label">
					</div>
					<div class="progress">
						<div class="progress-bar" style="width: 50%" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
						</div>
					</div>
					<div class="d-flex flex-stack bottom-des">
						<div class="d-flex">
							<div class="capacity-usedes">
								<?php echo $LANG['WEB_SYSTEM_LICENSE_USED'] ?>:
								<span id="fs_capacity_auth_used_des">
									
								</span>
							</div>
							<span id="fs_capacity_auth_used_percent">
								
							</span>
						</div>
						<div>
							<?php echo $LANG['WEB_SYSTEM_LICENSE_TOTAL_CAPACITY'] ?>:
							<span id="fs_capacity_auth_total_des">
								
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- 数量模块 -->
		<div class="auth-num"> 
			<div class="top-title mb-20">
				<div class="top-title-left"></div>
				<span>授权详情</span>
			</div>
			<div>
				<div class="wrap" id="auth-num-card">
				</div>
			</div>
		</div>
		
	</div>
	<!-- 第三层级 联系我们 -->
	<div class="basic-card">
	<div class="top-title mb-20">
		<div class="top-title-left"></div>
		<span><?php echo $LANG['UI_SETTINGS_AUTH_CONTACT_US'] ?></span>
	</div>
	<div>
		<ul class="linonepoint">
			<li>
				<i ><img src="../../../img/platform/auth/company.svg" alt=""></i> <span><?php echo $LANG['UI_SETTINGS_AUTH_COMPANY']?></span><?php echo $CONF['SYSTEM_INFO']['copyright_name']?>
			</li>
			<li>
				<i><img src="../../../img/platform/auth/website.svg" alt=""></i>  <span> <?php echo $LANG['UI_SETTINGS_AUTH_WEBSITE']?></span><a href="<?php echo $CONF['REMOTE']['website']?>" target="_black"><?php echo $CONF['REMOTE']['website']?></a>
			</li>
			<li>
				<i ><img src="../../../img/platform/auth/phonenumber.svg" alt=""></i> <span><?php echo $LANG['UI_SETTINGS_AUTH_PHONE']?></span><?php echo $CONF['SYSTEM_INFO']['company_tel']?>
			</li>
			<li>
				<i ><img src="../../../img/platform/auth/email.svg" alt=""></i><span> <?php echo $LANG['UI_SETTINGS_AUTH_EMAIL']?> </span><?php echo $CONF['SYSTEM_INFO']['company_email']?>
			</li>
			<li class="mb0  <?php if(($CONF['SYSTEM_INFO']['vendor'] != "vinchin")||($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw" ) ){echo "display-none";} ?>">
				<i ><img src="../../../img/platform/auth/social.svg" alt=""></i> <span><?php echo $LANG['WEB_SYSTEM_LICENSE_SOCIAL_MEDIA']?></span>
				<div>
					<ul class="list-inline d-flex" id="social">
						<li>
							<a class="wechat" href="javascript:;" >
							<img class="vinchin-qrcode" src="../img/platform/vinchin-wexin.png" alt="<?php echo $LANG['UI_SETTINGS_WEIXIN']?>">
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
						<span><?php echo $LANG['WEB_SYSTEM_LICENSE_FUNCTION_AUTH_DETAIL']?></span>
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
				<button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
				<button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
			</div>

		</div>
	</div>
	<!-- drawer结束 -->
</div>
	
<!-- BEGIN PAGE LEVEL PLUGINS -->
<!-- <script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script> -->
<script type="text/javascript" src="./scripts/platform/settings/authorization_module.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	