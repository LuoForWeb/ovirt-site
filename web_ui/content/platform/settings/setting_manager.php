<?php include_once '../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<!-- BEGIN PAGE HEADER-->
<?php
$userAllPermission = $_SESSION['permission'];
?>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="vinchin-wrap hover-scroll-y">
	<div class="vinchin-wrap__group">
		<?php
		//根据用户权限获取展示的内容
		//网络配置
		if (in_array("system_network", $userAllPermission)) {
			$des = $LANG['UI_PLATFORM_NETWORK_SETTING_DES'];
			if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){
				$des = $LANG['UI_PLATFORM_NETWORK_SETTING_DIVS_DES'];
			}
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_network.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_NETWORK_SETTING'] . '
								</div>
								<span class="upper-region__left__des">
									' . $des . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-wangluopeizhi1"></i>
							</div>
						</div>
					</a>';
		}

		//设置时间
		if (in_array("set_time", $userAllPermission)) {
			$des = $LANG['UI_SETTINGS_SET_TIME_TIPS'];
			if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){
				$des = $LANG['UI_SETTINGS_SET_TIME_DIVS_TIPS'];
			}
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/set_time.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_SET_TIME'] . '
								</div>
								<span class="upper-region__left__des">
									' . $des . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-shijianpeizhi"></i>
							</div>
						</div>
					</a>';
		}

		//系统通知
		if (in_array("system_notice", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_notice.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_SYSTEM_NOTICE'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SETTINGS_SYSTEM_NOTICE_TIPS'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-pt_alarm_system_alarm"></i>
							</div>
						</div>
					</a>';
		}

		//安全配置
		if (in_array("system_safe", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_safe.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_SAFE_SETTING'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_PLATFORM_SAFE_SETTING_DES'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-anquanpeizhi"></i>
							</div>
						</div>
					</a>';
		}

		//关机/重启
		if (in_array("system_poweroff", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_poweroff.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_POWER'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SETTINGS_SYSTEM_POWER_TIPS'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-guanjizhongqi"></i>
							</div>
						</div>
					</a>';
		}

		//系统升级
		if (in_array("system_upgrade", $userAllPermission)) {
			$des = $LANG['UI_SETTINGS_SYSTEM_UPGRADE_TIPS'];
			if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){
				$des = $LANG['UI_SETTINGS_SYSTEM_UPGRADE_DIVS_TIPS'];
			}
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_upgrade.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_SYSTEM_UPDATE'] . '
								</div>
								<span class="upper-region__left__des">
									' . $des . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-xitongshengji"></i>
							</div>
						</div>
					</a>';
		}

		//消息推送
		if (in_array("message_push", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/message_push.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_MESSAGE_PUSH'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SETTINGS_MESSAGE_PUSH_TIPS'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-xiaoxituisong1"></i>
							</div>
						</div>
					</a>';
		}

		//可视化配置
		if (in_array("visual_config", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/visual_config.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PUBLIC_VISUAL_CONFIG'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SETTINGS_VISUAL_CONFIG_TIPS'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-keshihuapeizhi"></i>
							</div>
						</div>
					</a>';
		}

		//系统工具
		if (in_array("system_service", $userAllPermission)) {
			$des = $LANG['UI_SETTINGS_SYSTEM_TOOL_TIPS'];
			if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){
				$des = $LANG['UI_SETTINGS_SYSTEM_TOOL_DIVS_TIPS'];
			}
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_service.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_SETTINGS_SYSTEM_TOOL'] . '
								</div>
								<span class="upper-region__left__des">
									' . $des . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-xitonggongju"></i>
							</div>
						</div>
					</a>';
		}

		//系统备份/恢复
		if (in_array("system_br", $userAllPermission)) {
			$des = $LANG['UI_PLATFORM_SYSTEM_BAK_REC_DES'];
			if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){
				$des = $LANG['UI_PLATFORM_SYSTEM_BAK_REC_DIVS_DES'];
			}
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_br.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_SYSTEM_BAK_REC'] . '
								</div>
								<span class="upper-region__left__des">
									' . $des . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-xitongbeifenhuifu"></i>
							</div>
						</div>
					</a>';
		}

		// 容灾演练平台
		if (in_array("exercise_platform", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/exercise_platform.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_EXERCISE_PLATFORM'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_EXERCISE_PLATFORM_DES'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-disanfangrongzaiyanlianpingtai"></i>
							</div>
						</div>
					</a>';
		}

		//黑白名单
		if (in_array("black_white_list", $userAllPermission)) {
			$des = $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_DES'];
			if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){
				$des = $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_DIVS_DES'];
			}
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/black_white_list.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_BLACKLIST_WHITELIST'] . '
								</div>
								<span class="upper-region__left__des">
									' . $des . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-fangwenkongzhi"></i>
							</div>
						</div>
					</a>';
		}

		//$userAllPermission[] = "api_key";
		// 生成apikey
		if (in_array("api_key", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_apikey.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_SETTINGS_APIKEY_MANAGE'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SETTINGS_APIKEY_MANAGE_TIPS'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-Apikeyguanli"></i>
							</div>
						</div>
					</a>';
		}

		// 个性化配置 目前只有GMP在用
		if (in_array("system_settings", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_settings.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_PERSONAL_CONFIG'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_PLATFORM_CUSTOM_PERSONAL_CONFIG'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-setting_manager"></i>
							</div>
						</div>
					</a>';
		}

		// 能耗监控平台
		if (in_array("carbon_monitor_platform", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/carbon_monitor_platform.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_SETTINGS_CARBON_MONITOR_PLATFORM'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SETTINGS_CARBON_MONITOR_PLATFORM_TIPS'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-nenghaojiankongpingtai"></i>
							</div>
						</div>
					</a>';
		}
		?>
	</div>
</div>
<!-- END PAGE CONTENT-->


