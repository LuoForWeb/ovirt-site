<?php include_once '../../tpl/permission.php'; ?>
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
		<a class="ajaxify" name="infrastructure" href="./content/vm/vcenter_manager.php">
			<?php echo $LANG['UI_PLATFORM_VCENTER'] ?>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PUBLIC_MODIFY'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<?php $userPermission = $_SESSION['authfun'] ?>
<!-- BEGIN PAGE CONTENT-->
<input style="display:none">
<div class="row row-manager">
	<input type="hidden" id="lang" value="<?php echo $_SESSION['language'] ?>">
	<div class="col-md-12 col-manager">
		<input id="vcenterUUID" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
		<input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
		<!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="modifycontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-ge_modify"></i><?php echo $LANG['UI_PUBLIC_MODIFY'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="form_sample_2" class="form-horizontal">
					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_VCENTER_TYPE'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="vmtype" name="vmtype" disabled>
								</select>
								<div><span class="help-block ">
										<?php echo $LANG['UI_VECNTER_ADD_VC_TYPE_TIPS'] ?>
									</span></div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_VECNTER_ADD_VC_IP_OR_NAME'] ?>
							</label>
							<div class="col-md-4">
								<div class="col-md-3 display-none" id="httpTypeDiv" style="padding: 0 !important;">
									<select class="form-control " id="httpType" disabled>
										<option value="http://">http://</option>
										<option value="https://">https://</option>
									</select>
								</div>
								<div class="input-icon right col-md-12" id="ipaddrDiv" style="padding: 0 !important;">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="ipaddr" disabled />
								</div>
								<div class="col-md-12" style="padding: 0 !important;">
									<span class="help-block iptips"><?php echo $LANG['UI_VECNTER_ADD_VC_IP_OR_NAME_TIPS'] ?></span>
									<span class="help-block smartxtips display-none"> <?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_TOWER_OR_IP_TIPS'] ?></span>
                                    <span class="help-block arcfratips display-none"><?php echo $LANG['UI_CLOUD_PLATFORM_ADD_VC_AOC_TIPS'] ?></span>
								</div>
							</div>
						</div>
						<div class="form-group display-hide" id="hypervDiv">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_VCENTER_HYPERV_TYPE'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="hypervType" name="hypervType">
									<option value="1"><?php echo $LANG['UI_VCENTER_HYPERV_TYPE_SCVMM'] ?></option>
									<option value="3"><?php echo $LANG['UI_VCENTER_HYPERV_TYPE_FAILOVER'] ?></option>
									<option value="2"><?php echo $LANG['UI_VCENTER_HYPERV_TYPE_OWN'] ?></option>
								</select>
								<div><span class="help-block ">
										<?php echo $LANG['UI_VCENTER_HYPERV_ADD_TIPS'] ?>
									</span></div>
							</div>
						</div>

						<div class="form-group display-hide ip-port">
							<label class="control-label col-md-4"><?php echo $LANG['UI_VECNTER_ADD_VC_IP_PORT'] ?>
							</label>
							<div class="col-md-2">
								<select class="form-control select2me" id="portType">
									<option value="public">public</option>
									<option value="admin">admin</option>
									<option value="internal">internal</option>
								</select>
							</div>
							<div class="col-md-2">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" id="openstackPort" value="5000" />
								</div>
							</div>
						</div>

						<div class="form-group display-hide" id="domainDiv">
							<label class="control-label col-md-4">Domain</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input type="text" maxlength="128" class="form-control" id="domain" placeholder="Default" />
									<div><span class="help-block ">
											<?php echo $LANG['UI_VECNTER_ADD_VC_KEYSTONE_VERSION_V3'] ?>
										</span></div>
								</div>
							</div>
						</div>

						<!-- scp登录类型 -->
						<div class="form-group display-hide sangforvvdkLoginDiv">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_CLOUD_PLATFORM_LOGIN_TYPE'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="sangforvvdkLoginType">
									<option value="token"><?php echo $LANG['UI_VECNTER_ADD_VC_LOGIN_TYPE_TOKEN'] ?></option>
									<option value="ak/sk"><?php echo $LANG['UI_VECNTER_ADD_VC_LOGIN_TYPE_EC2'] ?></option>
								</select>
								<div><span class="help-block "><?php echo $LANG['UI_VECNTER_ADD_VC_LOGIN_TYPE_TIPS'] ?></span></div>
							</div>
						</div>

						<!-- ics登录类型 -->
						<div class="form-group display-hide icsLoginDiv">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_CLOUD_PLATFORM_LOGIN_TYPE'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="icsLoginType">
									<option value="token"><?php echo $LANG['UI_VECNTER_ADD_VC_LOGIN_TYPE_TOKEN'] ?></option>
									<option value="ak/sk"><?php echo $LANG['UI_VECNTER_ADD_VC_LOGIN_TYPE_AK'] ?></option>
								</select>
								<div><span class="help-block "><?php echo $LANG['UI_VECNTER_ADD_VC_LOGIN_TYPE_TIPS2'] ?></span></div>
							</div>
						</div>

                        <!-- huawei kvm 用户类型 -->
                        <div class="form-group display-hide huaweikvmLoginDiv">
                            <label class="control-label col-md-4">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_VECNTER_ADD_VC_USER_TYPE'] ?>
                            </label>
                            <div class="col-md-4">
                                <select class="form-control select2me" id="huaweikvmUserType">
                                    <option value="0"><?php echo $LANG['UI_VECNTER_ADD_VC_USER_TYPE_LOCAL'] ?></option>
                                    <option value="1"><?php echo $LANG['UI_VECNTER_ADD_VC_USER_TYPE_DOMAIN'] ?></option>
                                    <option value="2"><?php echo $LANG['UI_VECNTER_ADD_VC_USER_TYPE_INTERFACE'] ?></option>
                                </select>
                                <div><span class="help-block "><?php echo $LANG['UI_VECNTER_ADD_VC_USER_TYPE_TIPS'] ?></span></div>
                            </div>
                        </div>
                        <!-- huawei kvm 认证类型 -->
                        <div class="form-group display-hide huaweikvmLoginDiv">
                            <label class="control-label col-md-4">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_VECNTER_ADD_VC_AUTH_TYPE'] ?>
                            </label>
                            <div class="col-md-4">
                                <select class="form-control select2me" id="huaweikvmAuthType">
                                    <option value="0"><?php echo $LANG['UI_VECNTER_ADD_VC_AUTH_TYPE_LOCAL'] ?></option>
                                    <option value="1"><?php echo $LANG['UI_VECNTER_ADD_VC_AUTH_TYPE_DOMAIN'] ?></option>
                                </select>
                                <div><span class="help-block "><?php echo $LANG['UI_VECNTER_ADD_VC_AUTH_TYPE_TIPS'] ?></span></div>
                            </div>
                        </div>

						<div class="form-group username">
							<label class="control-label col-md-4 username-label">
								<span class="required">* </span>
								<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
							</label>
							<label class="control-label col-md-4 username-label2 display-hide">
								<span class="required">* </span>
								<?php echo $LANG['UI_LOGIN_AK'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input type="text" maxlength="128" class="form-control" name="username" />
									<div>
										<span class="help-block username-tip">
											<?php echo $LANG['UI_VECNTER_ADD_VC_USERNAME_TIPS'] ?>
										</span>
										<span class="help-block username-tip2 display-hide">
											<?php echo $LANG['UI_VECNTER_ADD_VC_USERNAME_TIPS2'] ?>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group password">
							<label class="control-label col-md-4 password-label">
								<span class="required">* </span>
								<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
							</label>
							<label class="control-label col-md-4 password-label2 display-hide">
								<span class="required">* </span>
								<?php echo $LANG['UI_LOGIN_SK'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input type="password" autocomplete="off" maxlength="128" class="form-control" name="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
									<div>
										<span class="help-block password-tip">
											<?php echo $LANG['UI_VECNTER_ADD_VC_PASSWORD_TIPS'] ?>
										</span>
										<span class="help-block password-tip2 display-hide">
											<?php echo $LANG['UI_VECNTER_ADD_VC_PASSWORD_TIPS2'] ?>
										</span>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group icsDiv display-none">
							<label class="control-label col-md-4">AccessKey ID
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" id="accessId" disabled/>
									<div><span class="help-block "><?php echo $LANG['UI_CLOUD_PLATFORM_ACCESSKEY_IDENTIFY_USER'] ?></span></div>
								</div>
							</div>
						</div>

						<div class="form-group icsDiv display-none">
							<label class="control-label col-md-4">AccessKey Secret
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" id="accessSecret" />
									<div><span class="help-block "><?php echo $LANG['UI_CLOUD_PLATFORM_ACCESSKEY_VERTIFY_USER'] ?></span></div>
								</div>
							</div>
						</div>

						<!-- 别名 -->
						<div class="form-group">
							<label class="control-label col-md-4">
								<span class="required">* </span>
								<?php echo $LANG['UI_VCENTER_ALIA'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="64" class="form-control" name="rname" />
									<div><span class="help-block "><?php echo $LANG['UI_VECNTER_ADD_VC_RNAME_TIPS'] ?></span></div>
								</div>
							</div>
						</div>

						<!-- Redhat oVirt/OLVM -->
						<?php
						if (true) {
							echo '
                        <div class="form-group  engineCheckDiv display-none">
							<label class="control-label col-md-4 form-group-label">' . $LANG['UI_VCENTER_ENGINE_BACKUP_AUTO'] . '
							</label>
							<div class="col-md-4 form-group-content">
								<input type="checkbox" id="enginecheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" 
								data-on-text="' . $LANG['UI_PUBLIC_ON'] . '" 
								data-off-text="' . $LANG['UI_PUBLIC_OFF'] . '">
								<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" 
    							data-placement="right" data-content="' . $LANG['UI_VCENTER_ENGINE_BACKUP_AUTO_TIPS'] . '">
    							<i class="viconfont vicon-tishi"></i>
    							</a>
							</div>
						</div>
                                ';
						}

						?>

						<div class="form-group engineDiv display-none">
							<label class="control-label col-md-4"><?php echo $LANG['UI_VCENTER_ENGINE_BACKUP_USER_NAME'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="64" class="form-control" id="ename" />
									<div><span class="help-block "></span></div>
								</div>
							</div>
						</div>

						<div class="form-group engineDiv display-none">
							<label class="control-label col-md-4"><?php echo $LANG['UI_VCENTER_ENGINE_BACKUP_PASSWORD'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="password" autocomplete="off" maxlength="64" class="form-control" id="epassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
									<div><span class="help-block "></span></div>
								</div>
							</div>
							<div class="col-md-2">
								<button type="button" id="testConnect" class="btn btn-sm green-haze">
									<?php echo $LANG['UI_VCENTER_ENGINE_BACKUP_TEST_CONNECT'] ?>
								</button>
							</div>
						</div>

						<div class="form-group engineDiv display-none">
							<label class="control-label col-md-4 form-group-label"><?php echo $LANG['UI_VCENTER_ENGINE_BACKUP_DAILY_TIME'] ?>
							</label>
							<div class="col-md-2 form-group-content">
								<div class="input-group">
									<input type="text" value="23:00:00" class="form-control timepicker timepicker-24 backupTime">
									<span class="input-group-btn">
										<button class="btn default btn-time" type="button"><i class="fa fa-clock-o"></i></button>
									</span>
								</div>
							</div>
						</div>

						<div class="form-group engineDiv display-none">
							<label class="control-label col-md-4"><?php echo $LANG['UI_BACKUP_RESERVE_NUM'] ?>
							</label>
							<div class="col-md-2">
								<div id="spinnerNum">
									<div class="input-group spinner-group">
										<input type="text" id="spinnerNumInput" class="spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}">
										<div class="spinner-buttons input-group-btn spinner-group-btn">
											<button type="button" class="btn spinner-up default">
												<i class="fa fa-angle-up"></i>
											</button>
											<button type="button" class="btn spinner-down default">
												<i class="fa fa-angle-down"></i>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group engineDiv display-none">
							<label class="control-label col-md-4"><?php echo $LANG['UI_PALTFORM_NODE'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="selectnode">
								</select>
							</div>
						</div>

						<div class="form-group engineDiv display-none">
							<label class="control-label col-md-4"><?php echo $LANG['UI_DATACENTER_BACKUP_STORAGE'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="selectstorage">
								</select>
							</div>
						</div>

						<!-- WINHONG -->
						<div class="form-group highsafeCheckDiv display-none">
							<label class="control-label col-md-4 form-group-label"><?php echo $LANG['UI_VCENTER_WINHONG_HIGH_SAFE'] ?>
							</label>
							<div class="col-md-4 form-group-content">
								<input type="checkbox" id="highsafecheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
								<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VCENTER_WINHONG_HIGH_SAFE_SWITCH'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>

							</div>
						</div>

						<div class="form-group highsafeDiv display-none">
							<label class="control-label col-md-4"><?php echo $LANG['UI_VCENTER_WINHONG_HIGH_SAFE_IP'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" placeholder="192.168.1.100:8090" maxlength="64" class="form-control" id="highsafeIP" />
									<div><span class="help-block "></span></div>
								</div>
							</div>
							<div class="col-md-2">
								<button type="button" id="testhighsafeIP" class="btn btn-sm green-haze">
									<?php echo $LANG['UI_VCENTER_ENGINE_BACKUP_TEST_CONNECT'] ?>
								</button>
							</div>
						</div>

						<div class="form-group display-hide loginDiv">
							<label class="control-label col-md-4"><?php echo $LANG['UI_CLOUD_MANAGEMENT_PLATFORM'] ?>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="loginType" disabled>
									<option value="cloud tower">CloudTower</option>
									<option value="cluster">fisheye</option>
								</select>
							</div>
						</div>

						<!-- openstack传输代理 -->
						<div class="form-group applianceDiv display-none">
							<label class="control-label col-md-4 appliancelabel form-group-label"><?php echo $LANG['UI_CLOUD_PLATFORM_ENGINE'] ?></label>
							<div class="col-md-4 form-group-content">
								<input type="checkbox" id="appliancecheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
								<a class="ml15 popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CLOUD_PLATFORM_ADD_ENGINE_TIPS'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
								<div class="applianceSelectDiv" style="width: calc(100% - 100px);float:right">
									<select class="form-control select2me" id="applianceSelect">
										<option value=""><?php echo $LANG['UI_APPLIANCE_SELECT'] ?></option>
									</select>
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
<script src="./scripts/vm/modify_vcenter.js" type="text/javascript"></script>