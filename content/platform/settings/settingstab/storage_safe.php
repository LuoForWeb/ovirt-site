<?php include_once '../../../../tpl/permission.php'; ?>
<!-- BEGIN FORM-->
<form action="#" id="storagesafeform" class="form-horizontal">
	<div class="form-body-wrapper">
		<div class="form-body hp-100 overflow-visible" style="width: 80%;">
			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_STORAGE_SAFE'] ?>
				</label>
				<div class="col-md-6">
					<input type="checkbox" id="storageprotectcheck" class="make-switch" data-on-color="primary"
						data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
						data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
					<div><span class="help-block ">
							<?php echo $LANG['UI_SETTINGS_STORAGE_SAFE_SET'] ?>
						</span></div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
				</label>
				<div class="col-md-6">
					<div class="alert alert-block alert-info fade in">
						<button type="button" class="close" data-dismiss="alert"></button>
						<ul class="alert-ul">
							<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
							<li>
								<?php echo $LANG['UI_SETTINGS_STORAGE_SAFE_SET_TIPS'] ?>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!--<div class="form-actions flex-items-center justify-content-center">
		<div class="wp-50">
			<label class="control-label col-md-3"></label>
			<div class="col-md-6">
				<button type="button" id="spcancel" class="btn default"><?php /*echo $LANG['UI_PUBLIC_NO'] */?></button>
				<button type="button" id="spsubmit"
					class="btn green-haze btn-confirm"><?php /*echo $LANG['UI_PUBLIC_YES'] */?></button>
			</div>
		</div>
	</div>-->
</form>
<!-- END FORM-->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/storage_safe.js"></script>