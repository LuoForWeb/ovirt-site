<?php include_once '../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
			<span><?php echo $LANG['UI_PLATFORM_INFRASTRUCTURE'] ?></span>
		</a>
	</li>
	<span>></span>
	<li>
		<?php
		if ('private' == $_GET['cloudType']) {
			echo '<a class="ajaxify" name="infrastructure" href="./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=private">'
				. $LANG['UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM'] . '</a>';
		} else {
			echo '<a class="ajaxify" name="infrastructure" href="./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=public">'
				. $LANG['UI_PLATFORM_VM_CLOUD_PLATFORM'] . '</a>';
		}
		?>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_CLOUD_PLATFORM_MODIFY_VC'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<?php $userPermission = $_SESSION['authfun'] ?>
<!-- BEGIN PAGE CONTENT-->
<input style="display:none">
<div class="row row-manager">
	<div class="col-md-12 col-manager">
		<input id="vcenterUUID" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
		<input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
		<!-- 类型：public/private -->
		<input id="cloudType" value="<?php echo $_GET['cloudType']; ?>" class="display-none">
		<!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="modifycontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-ge_modify"></i><?php echo $LANG['UI_CLOUD_PLATFORM_MODIFY_VC'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="form_sample_2" class="form-horizontal">
					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_CLOUD_PLATFORM_TYPE'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="vmtype" name="vmtype" disabled>
								</select>
								<div><span class="help-block ">
										<?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_TYPE_TIPS'] ?>
									</span></div>
							</div>
						</div>

						<div class="form-group display-none publicCloudDiv">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_CLOUD_PLATFORM_BACKUP_SERVER_LOCATION'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="bsLocation" name="bsLocation">
									<option value="local"><?php echo $LANG['UI_CLOUD_PLATFORM_BACKUP_SERVER_LOCATION_LOCAL'] ?></option>
									<option value="cloud"><?php echo $LANG['UI_CLOUD_PLATFORM_BACKUP_SERVER_LOCATION_CLOUD'] ?></option>
								</select>
								<div>
									<span class="help-block ">
										<?php echo $LANG['UI_CLOUD_PLATFORM_BACKUP_SERVER_LOCATION_TIPS'] ?>
									</span>
								</div>
							</div>
						</div>

						<div class="form-group display-hide" id="awsDiv">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_CLOUD_PLATFORM_ACCOUNT_TYPE'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="accountType" name="accountType">
									<option value="cn"><?php echo $LANG['UI_CLOUD_PLATFORM_ACCOUNT_TYPE_CN'] ?></option>
									<option value="global"><?php echo $LANG['UI_CLOUD_PLATFORM_ACCOUNT_TYPE_GLOBAL'] ?></option>
								</select>
								<div>
									<span class="help-block ">
										<?php echo $LANG['UI_CLOUD_PLATFORM_ACCOUNT_TYPE_TIPS'] ?>
									</span>
								</div>
							</div>
						</div>
						<div class="form-group <?php echo 'public' == $_GET['cloudType'] ? 'display-none' : '' ?>" id="ipDiv">
							<label class="control-label col-md-4">
                                <span class="required">* </span>
								<?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_IP_OR_NAME'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="ipaddr" disabled />
									<div><span class="help-block iptips"><?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_IP_OR_NAME_TIPS'] ?></span>
										<span class="help-block smartxtips display-none"><?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_TOWER_OR_IP_TIPS'] ?></span>
									</div>
								</div>
							</div>
						</div>

                        <!-- ZStack登录类型 -->
                        <div class="form-group display-hide zstackLoginDiv">
                            <label class="control-label col-md-4">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_CLOUD_PLATFORM_LOGIN_TYPE'] ?>
                            </label>
                            <div class="col-md-4">
                                <select class="form-control select2me" id="zstackLoginType">
                                    <option value="account"><?php echo $LANG['UI_VECNTER_ADD_VC_LOGIN_TYPE_ACCOUNT'] ?></option>
                                    <option value="tenant"><?php echo $LANG['UI_VECNTER_ADD_VC_LOGIN_TYPE_TENANT'] ?></option>
                                </select>
                                <div><span class="help-block "><?php echo $LANG['UI_VECNTER_ADD_VC_LOGIN_TYPE_TIPS3'] ?></span></div>
                            </div>
                        </div>

                        <!-- Huawei Cloud Stack -->
                        <div class="form-group display-none" id="huaweistackPortDiv">
                            <label class="control-label col-md-4">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_IP_PORT'] ?>
                            </label>
                            <div class="col-md-4">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" maxlength="128" class="form-control" id="huaweistackPort" value="443" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group display-none" id="huaweistackDomainDiv">
                            <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_TENANT'] ?></label>
                            <div class="col-md-4">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input style="display:none"><!-- for disable autocomplete on chrome -->
                                    <input type="text" maxlength="128" class="form-control" id="huaweistackDomain" value="" />
                                </div>
                            </div>
                        </div>

						<div class="form-group <?php echo 'public' == $_GET['cloudType'] ? 'display-none' : '' ?>" id="highConfigDiv">
							<label class="control-label col-md-4 form-group-label"><?php echo $LANG['UI_USER_HIGH_SETTING'] ?></label>
							<div class="col-md-4 form-group-content">
								<input type="checkbox" id="showHighInfo" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
							</div>
						</div>
						<div class="form-group highInfo display-none">
							<div class="col-md-offset-4 col-md-4 accordion">
								<div class="panel panel-default strategy-panel">
									<div id="highInfo" class="panel-collapse">
										<div class="panel-body">
											<div class="col-md-12">
												<div class="form-group display-hide ip-port">
													<label class="control-label col-md-4"><?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_IP_PORT'] ?>
													</label>
													<div class="col-md-4">
														<select class="form-control select2me" id="portType">
															<option value="public">public</option>
															<option value="admin">admin</option>
															<option value="internal">internal</option>
														</select>
													</div>
													<div class="col-md-4">
														<div class="input-icon right">
															<i class="fa"></i>
															<input type="text" maxlength="128" class="form-control" id="openstackPort" value="5000" />
														</div>
													</div>
												</div>

												<div class="form-group display-hide" id="domainDiv">
													<label class="control-label col-md-4">Domain
													</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa"></i>
															<input style="display:none"><!-- for disable autocomplete on chrome -->
															<input type="text" maxlength="128" class="form-control" id="domain" placeholder="Default" />
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

                        <div class="form-group applianceDiv display-none">
                            <label class="control-label col-md-4 appliancelabel form-group-label"><?php echo $LANG['UI_CLOUD_PLATFORM_ENGINE'] ?></label>
                            <div class="col-md-4 form-group-content">
                                <input type="checkbox" id="appliancecheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <a class="ml15 popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CLOUD_PLATFORM_ADD_ENGINE_TIPS2'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                        <div class="form-group display-none applianceDiv applianceSelectDiv">
                            <label class="control-label col-md-4 applianceselectlabel"><?php echo $LANG['UI_APPLIANCE_SELECT'] ?></label>
                            <div class="col-md-4">
                                <select class="form-control select2me" id="applianceSelect">
                                </select>
                            </div>
                        </div>

						<div class="form-group">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<span class="username-label"><?php echo 'private' == $_GET['cloudType'] ? $LANG['UI_LOGIN_USERNAME'] : 'Access key ID' ?></span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input type="text" maxlength="128" class="form-control" name="username" disabled/>
									<div><span class="help-block ">
											<?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_USERNAME_TIPS'] ?>
										</span></div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<span class="password-label"><?php echo 'private' == $_GET['cloudType'] ? $LANG['UI_LOGIN_PASSWORD'] : 'Secret access key' ?></span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input type="password" autocomplete="off" maxlength="128" class="form-control" name="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
									<div><span class="help-block ">
											<?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_PASSWORD_TIPS'] ?>
										</span></div>
								</div>
							</div>
						</div>

                        <!-- zstack租户用户的角色 -->
                        <div class="form-group zstackRoleDiv display-none">
                            <label class="control-label col-md-4">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_CLOUD_PLATFORM_USER_ROLE'] ?>
                            </label>
                            <div class="col-md-4">
                                <select class="form-control select2me" id="zstackRole" disabled>
                                    <option value=""> -- </option>
                                </select>
                                <div><span class="help-block "><?php echo $LANG['UI_CLOUD_PLATFORM_USER_ROLE_TIPS'] ?></span></div>
                            </div>
                            <a href="javascript:;" id="getZstackRole" class="display-none" style="position:relative;top:8px;text-decoration: none">
                                <i class="viconfont vicon-ge_refresh"></i>
                                <?php echo $LANG['UI_PUBLIC_TOOLS_RELOAD'] ?>
                            </a>
                        </div>

						<div class="form-group">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_CLOUD_PLATFORM_RNAME'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="64" class="form-control" name="rname" />
									<div><span class="help-block "><?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_RNAME_TIPS'] ?></span></div>
								</div>
							</div>
						</div>

					</div>

					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-4 pl24 col-md-6">
								<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
								<button type="button" id="addsubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
							</div>
						</div>
					</div>
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<!-- END VALIDATION STATES-->
	</div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./scripts/vm/cloudplatform/modify_cloud_platform.js" type="text/javascript"></script>