<?php include_once '../../../../tpl/permission.php'; ?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />
<!-- BEGIN FORM-->
<form action="#" id="nictemingform" class="form-horizontal">
	<div class="form-body-wrapper">
		<div class="form-body form-body wp-50 hp-100 overflow-visible width80p_en">
			<div class="form-group">
				<label class="control-label col-md-3">
					<span class="required">* </span>
					<?php echo $LANG['UI_SETTINGS_POWEROFF_SELECT_NODE'] ?>
				</label>
				<div class="col-md-6">
					<select class="form-control select2me" name="nicnodelist">
					</select>
					<div><span class="help-block ">
							<?php echo $LANG['UI_NIC_TEAMING_NODE_SELECT'] ?>
						</span></div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					<span class="required">* </span>
					<?php echo $LANG['UI_NIC_TEAMING_MODE'] ?>
				</label>
				<div class="col-md-6">
					<select class="form-control select2me" name="nictype">
						<option value="0"><?php echo $LANG['UI_NIC_TEAMING_STRATEGY_BALANCED'] ?></option>
						<option value="1"><?php echo $LANG['UI_NIC_TEAMING_STRATEGY_MASTER_BACKUP'] ?></option>
						<option value="4"><?php echo $LANG['UI_NIC_TEAMING_DYNAMIC_LINK'] ?></option>
						<option value="6"><?php echo $LANG['UI_NIC_TEAMING_ADAPTER_LOAD'] ?></option>
					</select>
					<div><span class="help-block ">

						</span></div>
				</div>
				<div class="col-md-2 mt10">
					<a class="popovers " data-container="body" data-trigger="hover" data-html="true"
						data-placement="right"
						data-content="<?php echo $LANG['UI_NIC_TEAMING_STRATEGY_BALANCED_TIP']; ?>">
						<i class="viconfont vicon-tishi"></i>
					</a>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					<span class="required">* </span>
					<?php echo $LANG['UI_SETTINGS_NETWORK_CARD'] ?>
				</label>
				<div class="col-md-6">
					<select class="selectpicker bootstrap-mutiple-select select2me show-tick" name="nicnetworkcard" multiple
						data-live-search="true">
					</select>
					<div><span class="help-block ">
							<?php echo $LANG['UI_NIC_TEAMING_NEED_MULTUPLE_CHOICE'] ?>
						</span></div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					<span class="required">* </span>
					<?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="128" class="form-control" name="nicipaddr"
							placeholder="192.168.1.168" />
						<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_IP_TIP'] ?></span></div>
					</div>
				</div>
			</div>

			<div class="form-group" id="settings_prefix_nic" style="display: none;">
				<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_PREFIX'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right ">
						<i class="fa"></i>
						<input type="number" min="1" max="128" class="form-control" name="nicprefix"
							placeholder="128" />
						<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_PREFIX_TIP'] ?></span></div>
					</div>
				</div>
			</div>

			<div class="form-group" id="settings_netmask1_nic">
				<label class="control-label col-md-3">
					<span class="required">* </span>
					<?php echo $LANG['UI_SETTINGS_NETMASK'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input style="display:none"><!-- for disable autocomplete on chrome -->
						<input type="text" maxlength="128" class="form-control" name="nicnetmask"
							placeholder="255.255.255.0" />
						<div><span class="help-block ">
								<?php echo $LANG['UI_SETTINGS_NETMASK_TIP'] ?>
							</span></div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_GATEWAY'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input style="display:none"><!-- for disable autocomplete on chrome -->
						<input type="text" maxlength="1280" class="form-control" name="nicgateway"
							placeholder="192.168.1.1" />
						<div><span class="help-block ">
								<?php echo $LANG['UI_SETTINGS_GATEWAY_TIP_OPTIONAL'] ?>
							</span></div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_DNS'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right ">
						<i class="fa"></i>
						<input type="text" maxlength="64" class="form-control" name="nicdns"
							placeholder="192.168.1.1,192.168.1.2" />
						<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_DNS_TIP'] ?></span></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="form-actions flex-items-center justify-content-center">
		<div class="wp-50">
			<label class="control-label col-md-3"></label>
			<div class="col-md-6 col-md-8_en">
				<button type="button" id="niccancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<?php
				if (in_array("p_nic_teaming_edit", $_SESSION['permissionArr'])) {
					echo '<button type="button" id="nicsubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] . '</button>' .
						'<button type="button" id="cleansubmit" class="btn green-haze btn-confirm">' . $LANG['UI_NIC_TEAMING_CLEAR'] . '</button>';
				}
				?>
			</div>
		</div>
	</div>
</form>
<!-- END FORM-->
<script type="text/javascript" src="./scripts/platform/settings/settingstab/nic_teaming.js"></script>