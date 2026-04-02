<?php include_once '../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- BEGIN PAGE HEADER-->


<?php

//权限管理,根据用户的权限,显示tab
$permission = $_SESSION['permission'];
$tab1 = in_array('task_alarm', $permission);
$tab2 = in_array('system_alarm', $permission);

//控制tab显示
$tab1Show = $tab1 ? "" : "none";
$tab2Show = $tab2 ? "" : "none";
//控制tab active状态
//两个都有,第一个active;只有一个,直接active
if ($tab1 && $tab2) {
	$tab1Active = "active";
	$tab2Active = "";
} else {
	$tab1Active = $tab1 ? "active" : "";
	$tab2Active = $tab2 ? "active" : "";
}

//如果是跳转过来的
$tab = intval($_GET['tab']);
if (!empty($tab)) {
	$tab1Active = "active";
	$tab2Active = "";
	if (1 == $tab) {
		$tab1Active = "";
		$tab2Active = "active";
	}
}

?>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="vinchin-wrapper">
	<div class="vinchin-wrapper__tabs">
		<!-- BEGIN TAB PORTLET-->
		<div class="nav-tabs-wrapper">
			<ul class="nav nav-tabs nav-line-tabs" id="alarm_nav">
				<li class="<?php echo $tab1Active ?> nav-item" style="display: <?php echo $tab1Show ?>" data-type="task_alarm">
					<a href="#taskalarmdiv" data-toggle="tab" class=" nav-link">
						<i class="levelchild viconfont vicon-pt_alarm_task_alarms "></i> <?php echo $LANG['UI_PLATFORM_ALARM_TASK'] ?> </a>
				</li>
				<?php
				// 深信服这里需要单独处理
				if ($CONF['SYSTEM_INFO']['enterprise'] == 'sangfor_enterprise') {
					if (empty($_SESSION['tenantuuid'])) {
						echo '<li class=" ' . $tab2Active . ' nav-item" style="display: ' . $tab2Show . '" data-type="system_alarm">
								  <a href="#systemalarmdiv" data-toggle="tab" class=" nav-link"><i class="levelchild viconfont vicon-pt_alarm_system_alarm "></i>' . $LANG['UI_PLATFORM_ALARM_SYSTEM'] . '</a></li>';
					}
				} else {
					echo '<li class=" ' . $tab2Active . ' nav-item" style="display: ' . $tab2Show . '" data-type="system_alarm">
							<a href="#systemalarmdiv" data-toggle="tab" class=" nav-link"><i class="levelchild viconfont vicon-pt_alarm_system_alarm "></i>' . $LANG['UI_PLATFORM_ALARM_SYSTEM'] . '</a></li>';
				}

				?>
			</ul>
			<div class="tab-content hover-scroll-y">
				<div class="tab-pane <?php echo $tab1Active ?> " id="taskalarmdiv" style="display: <?php echo $tab1Show ?>">
					<?php
					// 深信服单独处理
					if ($CONF['SYSTEM_INFO']['enterprise'] == 'sangfor_enterprise') {
						if (include_once './task_alarm.php') {
							include_once './task_alarm.php';
						} else {
							include_once './content/platform/alarm/task_alarm.php';
						}
					} else {
						include_once './task_alarm.php';
					}

					?>
				</div>

				<div class="tab-pane <?php echo $tab2Active ?> " id="systemalarmdiv" style="display: <?php echo $tab2Show ?>">
					<?php
					// 深信服单独处理
					if ($CONF['SYSTEM_INFO']['enterprise'] == 'sangfor_enterprise') {
						if (empty($_SESSION['tenantuuid'])) {
							if (include_once './system_alarm.php') {
								include_once './system_alarm.php';
							} else {
								include_once './content/platform/alarm/system_alarm.php';
							}
						}
					} else {
						include_once './system_alarm.php';
					}

					?>
				</div>
			</div>
		</div>
		<!-- END TAB PORTLET-->
	</div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<!-- END PAGE LEVEL PLUGINS -->