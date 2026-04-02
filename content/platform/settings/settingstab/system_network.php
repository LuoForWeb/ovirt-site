<?php
include_once '../../../../tpl/permission.php';
include_once   '../../../platform/public/bs_table.php';
?>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
		</a>
	</li>
	<span>
		>
	</span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_NETWORK_SETTING'] ?></span>
</h3>

<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('set_ip', 'system_dns', 'nic_teaming', 'card_bridge');
$activeClassArr = array();  //active类
$displayArr = array();      //是否显示

$setFlag = false;
foreach ($tabNameArr as $key => $value) {
	if (in_array($value, $userAllPermission)) {
		//如果有权限
		$displayArr[$value] = "";
		if (!$setFlag) {
			$activeClassArr[$value] = " active ";
			$setFlag = true;
		} else {
			$activeClassArr[$value] = "";
		}
	} else {
		//如果没有权限
		$activeClassArr[$value] = "";
		$displayArr[$value] = "displaynone";
	}
}

// var_dump($activeClassArr, $displayArr);

?>
<div class="row row-manager">
	<div class="col-md-12 col-manager">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
					<li class="<?php echo $activeClassArr['set_ip'] ?> <?php echo $displayArr['set_ip'] ?>">
						<a href="#ipdiv" data-toggle="tab">
							<i class="levelchild viconfont vicon-pt_setting_ip_address"></i> <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?> </a>
					</li>
					<li class="<?php echo $activeClassArr['system_dns'] ?> <?php echo $displayArr['system_dns'] ?>">
						<a href="#dnsdiv" data-toggle="tab">
							<i class="levelchild viconfont vicon-pt_setting_dns"></i> <?php echo $LANG['UI_PLATFORM_SYSTEM_DNS'] ?> </a>
					</li>
					<li class="<?php echo $activeClassArr['nic_teaming'] ?> <?php echo $displayArr['nic_teaming'] ?>">
						<a href="#nicteamingdiv" data-toggle="tab">
							<i class=" levelchild viconfont vicon-pt_setting_netcard"></i> <?php echo $LANG['UI_PLATFORM_NIC_TEAMING'] ?> </a>
					</li>
                    <li class="<?php echo $activeClassArr['card_bridge'] ?> <?php echo $displayArr['card_bridge'] ?>">
                        <a href="#cardbridgediv" data-toggle="tab">
                            <i class=" levelchild viconfont vicon-card_bridge"></i> <?php echo $LANG['UI_PLATFORM_CARD_BRIDEG'] ?> </a>
                    </li>
				</ul>
			</div>
			<div class="portlet-body form">
				<div class="tab-content">

					<div class="tab-pane <?php echo $activeClassArr['set_ip'] ?>" id="ipdiv">
						<?php
                        if (empty($displayArr['set_ip'])) {
                            // 显示才加载
                            include_once './set_ip.php';
                        }
                        ?>
					</div>

					<div class="tab-pane <?php echo $activeClassArr['system_dns'] ?>" id="dnsdiv">
						<?php
                        if (empty($displayArr['system_dns'])) {
                            // 显示才加载
                            include_once './system_dns.php';
                        }
                         ?>
					</div>

					<div class="tab-pane <?php echo $activeClassArr['nic_teaming'] ?>" id="nicteamingdiv">
						<?php
                        if (empty($displayArr['nic_teaming'])) {
                            // 显示才加载
                            include_once './nic_teaming.php';
                        }
                        ?>
					</div>

                    <div class="tab-pane <?php echo $activeClassArr['card_bridge'] ?>" id="cardbridgediv">
                        <?php
                            if (empty($displayArr['card_bridge'])) {
                                // 显示才加载
                                include_once './card_bridge.php';
                            }
                         ?>
                    </div>
				</div>
			</div>
		</div>
		<!-- END FORM-->
	</div>
</div>
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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./scripts/libs/base64.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
