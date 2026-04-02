<?php include_once '../../../../tpl/permission.php'; ?>
<!-- BEGIN FORM-->
<form action="#" id="accountsafeform" class="form-horizontal">
	<div class="form-body-wrapper">
		<div class="form-body hp-100 overflow-visible" style="width: 80%;">
			<div class="form-group">
				<label class="control-label col-md-3"> <?php echo $LANG['UI_SETTINGS_LOGIN_OVER_TIME'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="4" onkeyup="value=value.replace(/[^\d]/g,'')" class="form-control"
							name="outtime" />
						<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_LOGIN_OVER_TIME_TIPS'] ?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_LOGIN_FAILURE_NUM'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="8" onkeyup="value=value.replace(/[^\d]/g,'')" class="form-control"
							name="faildcount" />
						<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_LOGIN_FAILURE_NUM_TIPS'] ?></span>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_LOGIN_FAILURE_TIME'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="8" onkeyup="value=value.replace(/[^\d]/g,'')" class="form-control"
							name="faild_lock_time" />
						<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_LOGIN_FAILURE_TIME_TIPS'] ?></span>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_PASSWORD_AVAILABLE_DAYS'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="8" onkeyup="value=value.replace(/[^\d]/g,'')" class="form-control"
							name="passwordtime" />
						<div><span
								class="help-block "><?php echo $LANG['UI_SETTINGS_PASSWORD_AVAILABLE_DAYS_TIPS'] ?></span>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_PASSWORD_LENGTH'] ?>
				</label>
				<div class="col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="8" onkeyup="value=value.replace(/[^\d]/g,'')" class="form-control"
							name="passlength" />
						<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_PASSWORD_LENGTH_TIPS'] ?></span>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_PASSWORD_COMPLEXITY'] ?>
				</label>
				<div class="col-md-6">
					<select class="form-control select2me" name="passcomplexity">
						<option value="1" <?php if ($_SESSION['isThreePowers']) {
							echo "style='display:none;'";
						} ?>>
							<?php echo $LANG['UI_SETTINGS_PASSWORD_COMPLEXITY_WEAK'] ?>
						</option>
						<option value="2"><?php echo $LANG['UI_SETTINGS_PASSWORD_COMPLEXITY_MEDIUM'] ?></option>
						<option value="3"><?php echo $LANG['UI_SETTINGS_PASSWORD_COMPLEXITY_STRONG'] ?></option>
					</select><span
						class="help-block "><?php echo $LANG['UI_SETTINGS_PASSWORD_COMPLEXITY_TIPS'] ?></span>
				</div>
			</div>

			<div class="form-group" style="<?php if (!$_SESSION['isThreePowers']) {
				echo 'display:none;';
			} ?>">
				<label class="control-label col-md-3">
				</label>
				<div class="col-md-6">
					<div class="alert alert-block alert-info fade in">
						<button type="button" class="close" data-dismiss="alert"></button>
						<ul class="alert-ul">
							<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
							<li>
								<?php echo $_SESSION['userLevel'] == 3 ? $LANG['UI_SETTINGS_FOR_USERS_TIPS'] : $LANG['UI_SETTINGS_FOR_USERS_TIPS2'] ?>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="form-actions flex-items-center justify-content-center">
		<div class="wp-50">
			<label class="control-label col-md-3"></label>
			<div class="col-md-6">
				<button type="button" id="safecancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <?php
                if (in_array("p_account_safe", $_SESSION['permissionArr'])) {
                    echo '<button type="button" id="safesubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] .'</button>';
                }
                ?>
			</div>
		</div>
	</div>
</form>
<!-- END FORM-->
<script type="text/javascript" src="./scripts/platform/settings/settingstab/account_safe.js"></script>