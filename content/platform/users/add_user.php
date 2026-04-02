<?php include_once '../../../tpl/permission.php'; ?>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="safety" href="./content/platform/users/safety_manager.php">
			<?php echo $LANG['UI_PLATFORM_SAFETY'] ?>
		</a>
	</li>
	<span>></span>
	<li>
		<a class="ajaxify" name="safety" href="./content/platform/users/users.php">
			<?php echo $LANG['UI_PLATFORM_SAFETY_USER'] ?>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_ADD_USER'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
	<div class="col-md-12 col-manager">
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="adduserContent">
			<div class="portlet-title">
				<div class="caption">
					<i class="icon-user-follow"></i><?php echo $LANG['UI_PLATFORM_ADD_USER'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="form_sample_2" class="form-horizontal">
					<input type="password" autocomplete="new-password" hidden>
					<div class="form-body">
						<div class="alert alert-danger display-hide">
							<button type="button" class="close" data-close="alert"></button>
							<?php echo $LANG['UI_USER_BASE_INFO_TIPS'] ?>
						</div>
						
						<div class="form-group" style="margin-top: 15px;">
							<label class="control-label col-md-4"><?php echo $LANG['UI_USER_TYPE'] ?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="usertype" name="usertype">
									<option value="1"><?php echo $LANG['UI_USER_LOCATION'] ?></option>
									<option value="2"><?php echo $LANG['UI_USER_EXTERNAL'] ?></option>
								</select>
							</div>
						</div>	
						
						<div class="form-group domainDiv display-none">
							<label class="control-label col-md-4"><?php echo $LANG['UI_USER_EXTERNAL_PROVIDER'] ?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="domainlist" name="domainlist">
								</select>
							</div>
						</div>						
						
						<div class="form-group">
							<label class="control-label col-md-4"><?php echo $LANG['UI_LOGIN_USERNAME'] ?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="64"  class="form-control" id="name" name="name"/>
								</div>
							</div>
						</div>
						<div class="form-group locationDiv">
							<label class="control-label col-md-4"><?php echo $LANG['UI_LOGIN_PASSWORD'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="password" autocomplete="off" maxlength="32" class="form-control" id="password" name="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
								</div>
							</div>
						</div>
						<div class="form-group locationDiv">
							<label class="control-label col-md-4"><?php echo $LANG['UI_USER_CONFIRM'] ?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="password" autocomplete="off" maxlength="32"  class="form-control" name="rpassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-4"><?php echo $LANG['UI_USER_EMAIL'] ?> <span class="required">
							 </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="email"/>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-4"><?php echo $LANG['UI_USER_PHONE'] ?> <span class="required">
							 </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="20" class="form-control" name="number"/>
								</div>
							</div>
						</div>
						
						<!--<div class="form-group tenantSelectDiv <?php if (!empty($_SESSION['tenantuuid'])) {
							echo "display-none";
						} ?> ">
							<label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_TENANT'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<select class="form-control select2me" id="user_Tenant" name="userTenant">
									</select>
									<div><span class="help-block ">
									<?php echo $LANG['UI_USER_SELECT_TENANT_TIPS'] ?>
									</span></div>
								</div>
							</div>
						</div> -->
					
						<div class="form-group">
							<label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SAFETY_ROLE'] ?><span class="required">
								</span>
							</label>
							<div class="col-md-4" id="muti-user">
								<div class="input-icon right">
									<select id="user_Role" name="userGroupRole" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true">
									</select>
									<div><span class="help-block ">
									<?php echo $LANG['UI_USER_SELECT_USER_TIPS1'] ?>
									</span></div>
								</div>
							</div>	
						</div>	
					
						<div class="form-group">
							<label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?><span class="required">
								</span>
							</label>
							<div class="col-md-4" id="muti-user">
								<div class="input-icon right">
									<select id="user_Group" name="usergroup" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true">
									</select>
									<div><span class="help-block ">
									<?php echo $LANG['UI_USER_SELECT_USER_GROUP_TIPS'] ?>
									</span></div>
								</div>
							</div>	
						</div>
						
						<div class="form-group">
							<label class="control-label col-md-4"><?php echo $LANG['UI_USER_BACKUP_STORAGE_CAPACITY'] ?>
								<span class="required">*</span>
							</label>
							<div class="col-md-4">
								<select id="storageMode" class="form-control">
									<option value="1"><?php echo $LANG['UI_USER_UNLIMITED_CAPACITY'] ?></option>
									<option value="2"><?php echo $LANG['UI_USER_CUSTOM_CAPACITY'] ?></option>
								</select>
								<div id="custom" style="margin-top: 15px; display:inline-flex;">
									<div class="input-icon" >
										<div id="spinnerNum" style="width: 140px;">
											<div class="input-group" style="width:140px;">
												<input type="text" id="spinnerNumInput" style="text-align: center;" onkeyup="value=value.replace(/^(0+)|[^\d]+/g,'')" class="spinner-input form-control" maxlength="3">
												<div class="spinner-buttons input-group-btn">
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
									<div class="">
										<select id="unit" style="width: 45px; margin-left: 10px;height: 33px;text-align:center;">
											<option value="1">MB</option>
											<option value="2">GB</option>
											<option value="3">TB</option>
										</select>
									</div>
									<div class="maxNum">
										<span id="maxNum" style="color: #737373;"><?php echo $LANG['UI_USER_TOTAL_STORAGE'] ?></span>
									</div>
								</div>
							</div>
							<div class="col-md-1" style="margin-top: 10px;">
								 <a class="popovers" data-container="body" data-trigger="hover" 
								   data-placement="right" data-content="<?php echo $LANG['UI_USER_QUOTA'] ?>">
									<i class="fa fa-info-circle fa-lg"></i>
								</a>
							</div>
								
						</div>
						<div class="form-group" id="force-edit-pwd">
							<div class="control-label col-md-4">
								<?php echo $LANG['UI_USER_FORCE_CHANGE_PASSWORD'] ?>
							</div>
							<div>
								<div class="col-md-4">
									<input type="checkbox" id="editPassWord" class="make-switch"
										   data-on-color="primary" data-off-color="info"
										   data-size="small"
										   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
										   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
									<a class="popovers ml15" data-container="body"
									   data-trigger="hover"
									   data-placement="right" data-content="<?php echo $LANG['UI_PALTFORM_FIRST_CHANGE_PASSWORD'] ?>;">
										<i class="fa fa-info-circle fa-lg"></i>
									</a>
								</div>
							</div>
						</div>
					</div>	
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-6 col-md-6">
								<button type="button" id="cancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
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
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	//如果是中文简体|中文繁体,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
} else if ($_SESSION['language'] != "en-us" && $_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") {
	//如果不是英文|中文简体|中文繁体,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages_' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./scripts/platform/users/add_user.js" type="text/javascript"></script>
	