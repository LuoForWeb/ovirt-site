<!-- BEGIN BODY -->
<!-- DOC: Apply "page-header-fixed-mobile" and "page-footer-fixed-mobile" class to body element to force fixed header or footer in mobile devices -->
<!-- DOC: Apply "page-sidebar-closed" class to the body and "page-sidebar-menu-closed" class to the sidebar menu element to hide the sidebar by default -->
<!-- DOC: Apply "page-sidebar-hide" class to the body to make the sidebar completely hidden on toggle -->
<!-- DOC: Apply "page-sidebar-closed-hide-logo" class to the body element to make the logo hidden on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-hide" class to body element to completely hide the sidebar on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-fixed" class to have fixed sidebar -->
<!-- DOC: Apply "page-footer-fixed" class to the body element to have fixed footer -->
<!-- DOC: Apply "page-sidebar-reversed" class to put the sidebar on the right side -->
<!-- DOC: Apply "page-full-width" class to the body element to have full width page without the sidebar menu -->
<?php
$sidebarClass = "";
if ("1" == $_COOKIE['sidebar_closed']) {
	//如果默认是收起的,添加body收起类
	$sidebarClass = "page-sidebar-closed";
	$sideMenuClass = " page-sidebar-menu-closed";
}
?>

<?php
//权限管理,根据用户的权限,显示信息
$permission = $_SESSION['permission'] ?? [];
$currentJobShow = in_array('current_job', $permission) ? "" : "none";
$historyJobShow = in_array('history_job', $permission) ? "" : "none";
$taskAlarmShow = in_array('task_alarm', $permission) ? "" : "none";
$systemAlarmShow = in_array('system_alarm', $permission) ? "" : "none";

//如果都没有
$surveyataskShow = "none";
if (in_array('current_job', $permission) || in_array('history_job', $permission)) {
	$surveyataskShow = "";
}

$surveyalarmShow = "none";
if (in_array('task_alarm', $permission) || in_array('system_alarm', $permission)) {
	$surveyalarmShow = "";
}
?>

<body class="page-header-fixed overflowy-hide   page-quick-sidebar-over-content <?php echo $sidebarClass; ?>">
	<!-- BEGIN HEADER -->
	<div class="page-header navbar navbar-fixed-top">
		<!-- BEGIN LOGO -->
		<div class="page-logo">
			<a href="<?php echo $CONF['REMOTE']['website'] ?>" target="_black">
				<img src="./img/platform/PDlogo.png" alt="" class="logo-default logosizefree">
			</a>
			<div class="sidebar-toggle" title="<?php echo $LANG['UI_FOLD'] ?>" id="sidebar_toggle">
				<i class="viconfont vicon-menu-fold"></i>
				<i class="viconfont vicon-menu-unfold display-none"></i>
			</div>
		</div>
		<!-- END LOGO -->

		<!-- BEGIN TOP NAVIGATION MENU -->
		<div class="top-menu">
			<ul class="nav navbar-nav pull-right">
				<li class="dropdown dropdown-extended dropdown-notification dropdown-time">
					<span class="spantitle"><?php echo $LANG['UI_HOMEPAGE_SYSTEM_TIME'] ?></span>
					<span class="systemTimeTop" id="systemTimeTop" ></span>
				</li>
				<li class="separator-vertical"></li>
				<!-- BEGIN NOTIFICATION DROPDOWN -->
				<li class="dropdown dropdown-extended dropdown-notification" id="surveyatask"  style="display: <?php echo $surveyataskShow ?>">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
						<i class="fa fa-tasks"></i>
					</a>
					<ul class="dropdown-menu">
						<li>
							<ul class="dropdown-menu-list" data-handle-color="#637283">
								<li style="display: <?php echo $currentJobShow ?>">
									<a href="javascript:;" class="dropdown-menu-list__item taskhrefcurrent">
										<span class="dropdown-menu-list__item__label current-task-label me-10">
											<i class="viconfont vicon-pt_job_current_task"></i>
										</span>
										<span class="dropdown-menu-list__item__value">
											<span id="topcurrenttask"></span><?php echo $LANG['UI_ALARM_TOP_TIPS_TASK_CURRENT'] ?>
										</span>
									</a>
								</li>
								<li style="display: <?php echo $historyJobShow ?>">
									<a href="javascript:;" class="dropdown-menu-list__item taskhrefhistory">
										<span class="dropdown-menu-list__item__label history-task-label me-10">
											<i class="viconfont vicon-pt_job_historical_task"></i>
										</span>
										<span class="dropdown-menu-list__item__value">
											<span id="tophistorytask"></span><?php echo $LANG['UI_ALARM_TOP_TIPS_TASK_HISTORY'] ?>
										</span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				<li class="dropdown dropdown-extended dropdown-notification" id="surveyalarm" style="display: <?php echo $surveyalarmShow ?>">
					<a href="#" class="dropdown-toggle"  data-toggle="dropdown" data-hover="dropdown" data-close-others="true" id="alarmtotal">
						<i class="viconfont vicon-a-Remindtixing"></i>
						<span class="badge badge-danger display-hide"></span>
					</a>
					<ul class="dropdown-menu">
						<li>
							<ul class="dropdown-menu-list " data-handle-color="#637283">
								<li style="display: <?php echo $taskAlarmShow ?>">
									<a href="javascript:;" class="dropdown-menu-list__item alarmhreftask">
										<span class="dropdown-menu-list__item__label task-alarm-label me-10">
											<i class="viconfont vicon-pt_alarm_task_alarms"></i>
										</span>
										<span class="dropdown-menu-list__item__value">
											<span id="alarmtask"></span><?php echo $LANG['UI_ALARM_TOP_TIPS_ERROR'] ?>
										</span>
									</a>
								</li>
								<li style="display: <?php echo $systemAlarmShow ?>">
									<a href="javascript:;" class="dropdown-menu-list__item alarmhrefsystem">
										<span class="dropdown-menu-list__item__label system-alarm-label me-10">
											<i class="viconfont viconfont vicon-pt_alarm_system_alarm"></i>
										</span>
										<span class="dropdown-menu-list__item__value">
											<span id="alarmsystem"></span><?php echo $LANG['UI_ALARM_TOP_TIPS_WARNNING'] ?>
										</span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				
				<?php
				if (empty($_SESSION['tenantuuid'])) {
					$authfun = $_SESSION['authfun'];
					$hide = "";
					//检查当前用户是否属于Master组
					$masterFlag = (new \app\v1\user\v0\logic\User())->pCheckUserIsMaster($_SESSION['userUUID']);

					if ((!$authfun['visualization'] || !$masterFlag) && !in_array('p_visual_screen', $_SESSION['permissionArr'] ?? [])) {
						$hide = "display: none";
					}
					echo '<li class="dropdown dropdown-extended dropdown-notification" id="visualscreen" style="' . $hide . '">
							<a title="' . $LANG['UI_VISUAL_SCREEN'] . '" href="../content/visualscreen/visualscreen.php" class= "dropdown-toggle visualization" target="_blank"  data-hover="dropdown"  ><i class="viconfont vicon-a-Computerdiannao"></i></a></li>';
				}
				?>
				<!-- END NOTIFICATION DROPDOWN -->

				<!-- BEGIN USER LOGIN DROPDOWN -->
				<li class="dropdown dropdown-user">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
						<i class="viconfont vicon-a-Peoplerenyuan"></i>
						<span class="username username-hide-on-mobile" id="username"><?php echo $_SESSION['tenantusername'] ?></span>
						<i class="fa fa-angle-down"></i>
					</a>
					<ul class="dropdown-menu dropdown-menu-default">
						<li>
							<a href="./content/platform/users/userinfo.php" name="userinfo" class="ajaxify">
							<i class="viconfont vicon-a-Peoplerenyuan"></i> <?php echo $LANG['UI_USER_SELF_INFO'] ?> </a>
						</li>
						<!-- <li>
							<a href="./content/platform/users/edit_password.php" name="edit_password" class="ajaxify">
							<i class="viconfont vicon-a-Editbianji1"></i> <?php echo $LANG['UI_USER_MODIFY_PASS'] ?> </a>
						</li> -->
						<li>
							<a href="/lock.php">
							<i class="viconfont vicon-ge_lock"></i> <?php echo $LANG['UI_USER_LOCK_SCREEN'] ?> </a>
						</li>
						<?php
						if (in_array($CONF['SYSTEM_INFO']['enterprise'], $CONF['ENTERPRISE']) && $CONF['SYSTEM_INFO']['enterprise'] != 'vinchin_standard') {
							echo '<li>
										<a href="./content/platform/users/aboutus.php" name="aboutus" class="ajaxify">
										<i class="icon-flag"></i> ' . $LANG['UI_USER_ABOUT_US'] . ' </a>
									</li>';
						}
						?>
						<li class="divider"></li>
						<li>
							<a href="/loginout.php">
							<i class="viconfont vicon-a-Logouttuichu"></i> <?php echo $LANG['UI_USER_LOGIN_OUT'] ?> </a>
						</li>
						
					</ul>
				</li>
				<!-- END USER LOGIN DROPDOWN -->
			</ul>
		</div>
		<!-- END TOP NAVIGATION MENU -->
	</div>
	<!-- END HEADER -->