<?php include_once '../../../tpl/permission.php'; ?>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="resource_group" href="./content/platform/resource/resource_group.php">
			<?php echo $LANG['UI_RESOURCE_GROUP_MANAGE'] ?>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PUBLIC_ADD_OPERATION'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
	<div class="col-md-12 col-manager">
		<!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="addResourcegroup">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-ge_add_task"></i><?php echo $LANG['UI_RESOURCE_GROUP_ADD'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="form_addResourcegroup" class="form-horizontal">
					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RESOURCE_GROUP_NAME'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="resourcegroupname" id="resourcegroupname" />
									<div><span class="help-block ">
										</span></div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_PUBLIC_DESCRIPTION'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<textarea class="form-control" id="description" name="description" rows="6" placeholder="<?php echo $LANG['UI_RESOURCE_GROUP_DETAIL_INFO'] ?>"></textarea>
									<div><span class="help-block ">
										</span></div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-3 col-md-4">
								<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_CANCEL'] ?></button>
								<button type="button" id="submitBut" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php

if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./scripts/platform/resource/add_resource_group.js"></script>
<!-- END PAGE LEVEL PLUGINS -->