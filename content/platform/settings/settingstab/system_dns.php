<?php include_once '../../../../tpl/permission.php'; ?>
<form action="#" class="form-horizontal" id="dnsform">
	<div class="form-body-wrapper">
		<div class="form-body wp-50 hp-100 overflow-visible width80p_en">
			<div class="form-group">
				<label class="control-label col-md-3">
					<span class="required">* </span>
					<?php echo $LANG['UI_SETTINGS_POWEROFF_SELECT_NODE'] ?>
				</label>
				<div class="col-md-6">
					<select class="form-control select2me" name="dnsnodelist">
					</select>
					<div><span class="help-block ">
							<?php echo $LANG['UI_SETTINGS_DNS_NODE_TIPS'] ?>
						</span></div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					<span class="required">* </span>
					<?php echo $LANG['UI_SETTINGS_DNS_SETTING'] ?>
				</label>
				<div class="col-md-6">
					<textarea class="form-control" id="dnslist" rows="8"
						placeholder="192.168.1.110  example.com"></textarea>
					<div><span class="help-block ">
							<?php echo $LANG['UI_SETTINGS_DNS_SETTING_TIPS'] ?>
						</span></div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_SETTINGS_DNS_SYNC_NODE'] ?>
				</label>
				<div class="col-md-6 form-group-content">
					<input type="checkbox" id="dnscheck" data-size="small" class="make-switch" data-on-color="primary"
						data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
						data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
					<div><span class="help-block ">
							<?php echo $LANG['UI_SETTINGS_DNS_SYNC_NODE_TIPS'] ?>
						</span></div>
				</div>
			</div>
		</div>
	</div>

	<div class="form-actions flex-items-center justify-content-center">
		<div class="wp-50">
			<label class="control-label col-md-3"></label>
			<div class="col-md-6">
				<button type="button" id="dnscancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <?php
                if (in_array("p_system_dns_edit", $_SESSION['permissionArr'])) {
                    echo '<button type="button" id="dnssubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] .'</button>';
                }
                ?>
			</div>
		</div>
	</div>
</form>
<!-- END PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_dns.js"></script>