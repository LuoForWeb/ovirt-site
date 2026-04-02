<?php include_once '../../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css"
	href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<form action="#" id="setautobakform" class="form-horizontal">
	<div class="form-body-wrapper">
		<div class="form-body wp-50 hp-100 overflow-visible width80p_en">
			<div class="form-group">
				<label class="control-label form-group-top4-label col-md-3">
					<?php echo $LANG['UI_PLATFORM_RC_AUTOBAK'] ?>
				</label>
				<div class="col-md-6">
					<input type="checkbox" id="autoBakCheck" class="make-switch" data-on-color="primary"
						data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
						data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
				</div>
			</div>

			<div class="form-group autodiv display-none" id="configdiv">
				<label class="control-label col-md-3">
					<?php echo $LANG['UI_BR_ONCEBAK_SELECT_BU'] ?>
				</label>
				<div class="col-md-6">
					<ul id="baktree" class="ztree bd1de5 tree_div " style="height: 260px;overflow: auto;"></ul>
					<div><span class="help-block ">
							<?php echo $LANG['UI_BR_ONCEBAK_SELECT_CONTENT'] ?>
						</span></div>
				</div>
			</div>

			<div class="form-group autodiv display-none">
				<label class="control-label col-md-3">
					<?php echo $LANG['UI_VCENTER_ENGINE_BACKUP_DAILY_TIME'] ?>
				</label>
				<div class="col-md-6 form-group-content">
					<div class="input-group">
						<input type="text" value="01:00:00" class="form-control timepicker timepicker-24 backupTime">
						<span class="input-group-btn">
							<button class="btn default btn-time" type="button"><i class="fa fa-clock-o"></i></button>
						</span>
					</div>
				</div>
			</div>

			<div class="form-group autodiv display-none">
				<label class="control-label col-md-3">
					<?php echo $LANG['UI_BACKUP_RESERVE_NUM'] ?>
				</label>
				<div class="col-md-6">
					<div id="spinnerNum">
						<div class="input-group spinner-group">
							<input type="text" id="spinnerNumInput" onkeyup="value=value.replace(/[^\d]/g,'')"
								class="spinner-input form-control input-sm" maxlength="3">
							<div class="spinner-buttons input-group-btn spinner-group-btn">
								<button type="button" class="btn spinner-up default input-sm">
									<i class="fa fa-angle-up"></i>
								</button>
								<button type="button" class="btn spinner-down default input-sm">
									<i class="fa fa-angle-down"></i>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group autodiv display-none">
				<div class="col-md-12" id="backupTarget"></div>
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
								<?php echo $LANG['UI_BR_AUTOBAK_SETTING_TIP_ONE'] ?>
							</li>
							<li>
								<?php echo $LANG['UI_BR_ONCEBAK_SELECT_CONTENT_TIP_TWO'] ?>
							</li>
						</ol>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="form-actions flex-items-center justify-content-center">
		<div class="wp-50">
			<label class="control-label col-md-3"></label>
			<div class="col-md-6">
				<button type="button" id="autobakcancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<?php
				if (in_array("rc_autobak_setting", $_SESSION['permissionArr'])) {
					echo '<button type="button" id="autobaksubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] . '</button>';
				}
				?>
			</div>
		</div>
	</div>
</form>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_br_autobak_setting.js"></script>