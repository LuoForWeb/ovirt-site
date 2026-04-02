<?php include_once '../../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php" >
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_SYSTEM_BAK_REC'] ?></span>
</h3>
<?php
$userAllPermission = $userAllPermission = $_SESSION['permission'];
?>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="vinchin-wrap row-manager hover-scroll-y">
	<div class="vinchin-wrap__group">
		<?php
		//根据用户权限获取展示的内容
		//手动备份
		if (in_array("rc_oncebak", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_br_oncebak.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">' . $LANG['UI_PLATFORM_RC_ONCEBAK'] . '</div>
								<span class="upper-region__left__des">' . $LANG['UI_BR_ONCEBAK_ONE_TIME'] . '</span>
							</div>
							<div class="upper-region__right">
								<i class="iconfont icon-opbak"></i>
							</div>
						</div>	
					</a>';
		}

		//自动备份
		if (in_array("rc_autobak", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_br_autobak.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">' . $LANG['UI_PLATFORM_RC_AUTOBAK'] . '</div>
								<span class="upper-region__left__des">' . $LANG['UI_BR_ONCEBAK_ONE_DAY'] . '</span>
							</div>
							<div class="upper-region__right">
								<i class="iconfont icon-autobak"></i>
							</div>
						</div>	
					</a>';
		}

		// 系统恢复
		if (in_array("rc_recovery", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_br_recovery.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">' . $LANG['UI_PLATFORM_RC_RECOVERY'] . '</div>
								<span class="upper-region__left__des">' . $LANG['UI_BR_ONCEBAK_BY_FILE'] . '</span>
							</div>
							<div class="upper-region__right">
								<i class="iconfont icon-sysrecovery"></i>
							</div>
						</div>	
					</a>';
		}
		?>
	</div>
	<?php
	?>
</div>
<!-- END PAGE CONTENT-->
