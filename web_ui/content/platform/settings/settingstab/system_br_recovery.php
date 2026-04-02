<?php include_once '../../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/web-uploader/webuploader.css" rel="stylesheet" type="text/css" />
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
		</a>
	</li>
	<span>></span>
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_br.php">
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_BAK_REC'] ?></span>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_RC_RECOVERY'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height: calc(100% - 32px);">
	<div class="col-md-12" style="height: 100%;">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-sysrecovery"></i><?php echo $LANG['UI_PLATFORM_RC_RECOVERY'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" id="rcform" class="form-horizontal mh285">
					<div class="form-body mb15">
						<div class="form-group pt50">
							<label class="control-label col-md-3"><?php echo $LANG['UI_BR_RECOVERY_DATA_SOURCE'] ?><span class="required">
									* </span>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="rctype">
									<option value="" selected><?php echo $LANG['UI_BR_RECOVER_SELECT_DATA_SOURCE_TIPS'] ?></option>
									<option value="0"><?php echo $LANG['UI_BR_RECOVERY_AUTOBAK_DATA_SOURCE'] ?></option>
									<option value="1"><?php echo $LANG['UI_BR_RECOVERY_ONCEBAK_DATA_SOURCE'] ?></option>
								</select>
							</div>
						</div>

						<div class="form-group display-none contentdiv" id="uploaddiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_BR_RECOVERY_UPLOAD_DATA_SOURCE'] ?><span class="required">
									* </span>
							</label>
							<div class="col-md-6">
								<div id="uploader" class="wu-example">
									<div id="thelist" class="uploader-list"></div>
									<div class="btns">
										<div id="picker" class="btn-group"><?php echo $LANG['UI_SETTINGS_UPDATE_SELECT_FILE'] ?></div>
										<div class="btn-group">
											<button type="button" id="startupload" class="btn btn-sm green-haze"><?php echo $LANG['UI_SETTINGS_UPDATE_START_UPLOAD'] ?></button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group display-none contentdiv" id="tablediv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_BR_RECOVERY_SELECT_DATA_SOURCE'] ?><span class="required">
									* </span>
							</label>
							<div class="col-md-6">
								<div class="table-container">
									<table id="system_backup_point"></table>
									<div>
										<span class="help-block ">
											<?php echo $LANG['UI_BR_RECOVERY_SELECT_BACKUP_POINT'] ?>
										</span>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group display-none contentdiv" id="configdiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_BR_RECOVERY_SELECT_RECOVERY_ITEM'] ?><span class="required">
									* </span>
							</label>
							<div class="col-md-4">
								<ul id="rectree" class="ztree bd1de5 tree_div height300  overflowy-auto"></ul>
								<div><span class="help-block ">
										<?php echo $LANG['UI_BR_RECOVERY_SELECT_RECOVERY_CONTENT'] ?>
									</span></div>
							</div>
						</div>


						<div class="form-group">
							<label class="control-label col-md-3">
							</label>
							<div class="col-md-6">
								<div class="alert alert-block alert-info fade in">
									<button type="button" class="close" data-dismiss="alert"></button>
									<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
									<ol class="alert-ol">
										<li>
											<?php echo $LANG['UI_BR_RECOVERY_SELECT_RECOVERY_TIP_ONE'] ?>
										</li>
										<li>
											<?php echo $LANG['UI_BR_RECOVERY_SELECT_RECOVERY_TIP_TWO'] ?>
										</li>
										<li>
											<?php echo $LANG['UI_BR_RECOVERY_SELECT_RECOVERY_TIP_THREE'] ?>
										</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions flex-items-center justify-content-center">
						<div class="wp-50">
							<label class="control-label col-md-3"></label>
							<div class="col-md-8">
								<button type="button" id="rccancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
								<?php
								if (in_array("p_rc_recovery", $_SESSION['permissionArr'])) {
									echo '<button type="button" id="rcsubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] . '</button>';
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

<!-- BEGIN MODAL -->
<div id="recModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<!-- 		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button> -->
		<h4 class="modal-title"><i class="glyphicon glyphicon-circle-arrow-up"></i><?php echo $LANG['UI_PLATFORM_RC_RECOVERY'] ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="alert alert-warning modalalert display-none" id="recAlarm">
				<p><?php echo $LANG['UI_BR_RECOVERY_SELECT_RECOVERY_LOADING'] ?></p>
			</div>
			<div class="alert alert-success modalalert display-none" id="recSuccess">
				<p><?php echo $LANG['UI_BR_RECOVERY_SELECT_RECOVERY_SUCCESS'] ?></p>
			</div>
			<div class="alert alert-danger modalalert display-none" id="recError">
				<p><?php echo $LANG['UI_BR_RECOVERY_SELECT_RECOVERY_FAIL'] ?></p>
			</div>

			<div class="form-group">
				<div class="col-md-12 ">
					<div class="bd1d pd10">
						<div class="scroller" style="height: 360px;" data-always-visible="1" data-rail-visible="0">
							<ul class="feeds" id="runninglog">
							</ul>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" class="btn btn-default" id="cancelRecBtn"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
	</div>
</div>
<!-- END MODAL -->
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./assets/global/plugins/web-uploader/webuploader.min.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_br_recovery.js"></script>