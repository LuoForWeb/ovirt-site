<?php include_once '../../../tpl/permission.php'; ?>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="safety" href="./content/platform/users/safety_manager.php">
			<?php echo $LANG['UI_PLATFORM_SAFETY'] ?>
		</a>
	</li>
	<span>></span>
	<li>
		<a class="ajaxify" name="safety" href="./content/platform/users/user_group.php">
			<?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_USER_GROUP_ADD'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	<!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="addcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-ge_add_task"></i><?php echo $LANG['UI_USER_GROUP_ADD'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="form_addUserGroup" class="form-horizontal">
					<div class="form-body">
						<div class="form-group" id="vmtypediv" style="margin-top: 15px">
							<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_STORAGE_NAME'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="64" class="form-control" name="userGroupName" id="userGroupName"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_USER_GROUP_NAME_TIPS'] ?>
									</span></div>
								</div>
							</div>
						</div>
					
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_USER_GROUP_EXPLAIN'] ?><span class="required">
							 </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<textarea class="form-control" id="userGroupDescription" style="margin: 0px 904.828px 0px 0px; height: 150px;" maxlength="300";></textarea>
									<div><span class="help-block ">
									<?php echo $LANG['UI_USER_GROUP_EXPLAIN_TIPS'] ?>
									</span></div>
								</div>
							</div>
						</div>
					
						<!-- <div class="form-group <?php if (!empty($_SESSION['tenantuuid'])) {
							echo "display-none";
						} ?>">
							<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_TENANT'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<select class="form-control select2me" id="userGroup_Tenant" name="userGroupTenant">
										<option value=""><?php echo $LANG['UI_ROLE_GLOBAL'] ?></option>
									</select>
									<div><span class="help-block ">
									<?php echo $LANG['UI_USER_SELECT_TENANT_TIPS'] ?>
									</span></div>
								</div>
							</div>
						</div> -->

                        <div class="form-group" id="div_global_auth">
                            <label class="control-label col-md-3" style="margin-top:-6px;"><?php echo $LANG['UI_PLATFORM_GLOBAL_OBSERBER']?></label>
                            <div class="col-md-4">
                                <input type="checkbox"
                                       id="choose_global_flag"
                                       class="make-switch" data-on-color="primary"
                                       data-size="small" data-off-color="info"
                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <div><span class="help-block ">
									<?php echo $LANG['UI_PLATFORM_GLOBAL_USER_GROUP_TIPS']?>
									</span></div>
                            </div>
                        </div>

						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_SAFETY_ROLE'] ?><span class="required">
								</span>
							</label>
							<div class="col-md-4" id="muti-user">
								<div class="input-icon right">
									<select id="userGroup_Role" name="userGroupRole" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true">
									</select>
									<div><span class="help-block ">
									<?php echo $LANG['UI_USER_SELECT_ROLE_TIPS'] ?>
									</span></div>
								</div>
							</div>	
						</div>	
					
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_SAFETY_USER'] ?><span class="required">
								</span>
							</label>
							<div class="col-md-4" id="muti-user">
								<div class="input-icon right">
									<select id="userGroup_User" name="userGroupUser" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true">
									</select>
									<div><span class="help-block ">
									<?php echo $LANG['UI_USER_SELECT_USER_TIPS'] ?>
									</span></div>
								</div>
							</div>	
						</div>
					</div>
					
					<div class="form-actions pt50">
						<div class="row">
							<div class="col-md-offset-3 col-md-4">
								<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_CANCEL'] ?></button>
								<button type="button" id="addsubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./scripts/platform/users/add_usergroup.js" type="text/javascript"></script>