<?php 
	include_once '../../../../tpl/permission.php'; 
	$userAllPermission = $_SESSION['permissionArr'];
 ?>
<link rel="stylesheet" type="text/css"
	href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css"
	href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css"
	href="./assets/global/plugins/jquery-editable-select/jquery-editable-select.min.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PUBLIC_VISUAL_CONFIG'] ?></span>
</h3>
<div class="row row-manager">
	<div class="col-md-12 col-manager">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki" id=''>
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-visual"></i><?php echo $LANG['UI_PUBLIC_VISUAL_CONFIG'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="visualform" class="form-horizontal">
					<div class="form-body-wrapper">
						<div class="form-body wp-50 hp-100 overflow-visible width80p_en">
							<div class="form-group" id="configdiv">
								<label
									class="control-label min-w-145px col-md-3"><?php echo $LANG['UI_VISUAL_CONFIG_TITLE'] ?><span
										class="required">
										* </span>
								</label>
								<div class="col-md-6">
									<div class="input-icon right">
										<i class="fa"></i>
										<!-- <input type="text" maxlength="22" class="form-control" name="pushTitle" id="visualTitle" /> -->
										<input type="text" maxlength="<?php if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
											echo "22";
										} else {
											echo "40";
										}
										?>" class="form-control" name="pushTitle" id="visualTitle" />
										<div><span class="help-block ">
												<?php echo $LANG['UI_VISUAL_SET_TITLE'] ?>
											</span></div>
									</div>
								</div>
							</div>

							<div class="form-group ">
								<label
									class="control-label form-group-top4-label min-w-145px col-md-3"><?php echo $LANG['UI_PLATFORM_ALARM_TASK'] ?>
								</label>
								<div class="col-md-6">
									<input type="checkbox" id="taskAlertCheck" class="make-switch" data-size="small"
										data-on-color="primary" data-off-color="info"
										data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
										data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
									<div><span class="help-block ">
											<?php echo $LANG['UI_VISUAL_SET_ALARM_TASK_SHOW'] ?>
										</span></div>
								</div>
							</div>

							<div class="form-group ">
								<label
									class="control-label form-group-top4-label min-w-145px col-md-3"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?>
								</label>
								<div class="col-md-6">
									<input type="checkbox" id="systemAlertCheck" class="make-switch" data-size="small"
										data-on-color="primary" data-off-color="info"
										data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
										data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
									<div><span class="help-block ">
											<?php echo $LANG['UI_VISUAL_SET_ALARM_SYSTEM_SHOW'] ?>
										</span></div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-actions flex-items-center justify-content-center">
						<div class="wp-50">
							<label class="control-label min-w-145px col-md-3"></label>
							<div class="col-md-6">
								<button type="button" id="visualcancel"
									class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
									<?php 
										if (in_array("p_setting_manager_visualization", $userAllPermission)) {
											echo '<button type="button" id="visualsubmit"
											class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] . '</button>';
										}
									?>
							</div>
						</div>
					</div>
				</form>
				<!-- END FORM-->
			</div>
		</div>
	</div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script type="text/javascript"
	src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript"
	src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-editable-select/jquery-editable-select.min.js">
</script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/visual_config.js"></script>