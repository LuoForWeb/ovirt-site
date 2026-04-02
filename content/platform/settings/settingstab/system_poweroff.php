<?php include_once '../../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
	type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_POWER'] ?></span>
</h3>
<div class="row row-manager">
	<div class="col-md-12 col-manager">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki" id=''>
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-offrestart"></i><?php echo $LANG['UI_PLATFORM_POWER'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" class="form-horizontal" id="powerform">
					<div class="form-body-wrapper">
						<div class="form-body wp-50 hp-100 overflow-visible width80p_en">
							<div class="form-group">
								<label
									class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_SETTINGS_POWEROFF_SELECT_NODE'] ?><span
										class="required">
										* </span>
								</label>
								<div class="col-md-6">
									<select class="form-control select2me" name="powernodelist">
									</select>
									<div><span class="help-block ">
											<?php echo $LANG['UI_SETTINGS_POWEROFF_SELECT_NODE_TIP'] ?>
										</span></div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-actions flex-items-center justify-content-center">
						<div class="wp-50">
							<label class="control-label col-md-3"></label>
							<div class="col-md-6">
								<button type="button" id="powerreboot" class="btn btn-warning">
									<?php echo $LANG['UI_SETTINGS_POWEROFF_RESTART'] ?> </button>
								<button type="button" id="poweroff" class="btn btn-danger btn-confirm">
									<?php echo $LANG['UI_SETTINGS_POWEROFF_POWEROFF'] ?> </button>
							</div>
						</div>
					</div>
				</form>
				<!-- END FORM-->
			</div>
		</div>
	</div>
</div>


<!-- BEGIN MODAL -->
<div id="powermodaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
	data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"></h4>
	</div>
	<input type="text" class="display-none" name="powertype" />
	<div class="modal-body">
		<div class="portlet-body" id="childrendiv">
			<div class="alert alert-warning">
				<?php echo $LANG['UI_SETTINGS_POWEROFF_RUNNING_TASK_TIPS'] ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_POWEROFF_RUNNING_TASKS'] ?>:</label>
			<div class="col-md-8 margintop10" id="runningtaskdiv">
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_POWEROFF_PASSWORD_TIPS'] ?>:</label>
			<div class="col-md-8">
				<input class="form-control" type="password" autocomplete="off" name="password">
			</div>

		</div>
	</div>
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary" id="powersubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>
<!-- END MODAL -->

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
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_poweroff.js"></script>
<!-- END PAGE LEVEL PLUGINS -->