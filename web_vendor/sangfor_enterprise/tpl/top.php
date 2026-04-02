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
$permission = $_SESSION['permission'];
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

<body class="page-header-fixed overflowy-hide page-quick-sidebar-over-content <?php echo $sidebarClass; ?>">
<!-- BEGIN HEADER -->
<div class="page-header navbar navbar-fixed-top">
	<!-- BEGIN HEADER INNER -->
	<div class="page-header-inner">
		<!-- BEGIN LOGO -->
		<div class="col-md-1 logo-box">
			<div class="page-logo">
				<a href="<?php echo $CONF['REMOTE']['website'] ?>" target="_black">
				<?php
				if ($CONF['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType) {
					//如果是免费版,使用专用logo
					echo '<img src="./img/platform/logo-free.png" alt="" class="logo-default logosizefree">';
				} else {
					//如果是其他版本
					if (file_exists($CONF['SPECIAL_DIR'] . "logo.png")) {
						$logo = "./special/logo.png";
					} else {
						$logo = "./img/platform/logo.png";
					}
					echo '<img src="' . $logo . '" alt="" class="logo-default logosize">';
				}
				?>
				</a>
				<div class="menu-toggler sidebar-toggler hide">
					<!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
				</div>
			</div>
		</div>
		<div class="col-md-2 text-box">
			<h1 class="sangfor-title">深信服企业级数据备份与恢复系统</h1>
			<div class="sangfor-subtitle">Sangfor Enterprise Data Backup & Recovery System (V3.0.6 S1)</div>
		</div>
		<!-- END LOGO -->
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
		</a>
		<!-- END RESPONSIVE MENU TOGGLER -->
		<div class="hor-menu hor-menu-light  ">
			<ul class="nav nav-tabs sangfor-nav"  data-close-others="true">
				<!-- DOC: Remove data-hover="megadropdown" and data-close-others="true" attributes below to disable the horizontal opening on mouse hover -->
				<?php
				$arr = $_SESSION['permission'];
				if (in_array("homepage", $arr)) {
					echo '<li class="classic-menu-dropdown" name="homepage">
									<img src="./img/platform/sangfor/nav-bg.png" alt="">
									<a href="index.html">
										首页 <span class="selected"></span>
									</a>
							    </li>';
				}
				if (in_array("monitor", $arr)) {
					echo '<li class="classic-menu-dropdown " name="monitor">
								<img src="./img/platform/sangfor/nav-bg.png" alt="">
								<!-- <a href="./content/platform/jobs/jobs.php" name="task" class="ajaxify"> -->
								<a href="./monitor.html">

									监控中心 <span class="selected"></span>
								</a>
							</li>';
				}

				if (
					in_array("vmprotect", $arr) || in_array("awsprotect", $arr) || in_array("host_protect", $arr) || in_array("vol_cdp_protect", $arr) || in_array("nas_protect", $arr)
					|| in_array("dbprotect", $arr) || in_array("application_protect", $arr) || in_array("data_verification", $arr) || in_array("data_archive_cd", $arr) || in_array("data_manager", $arr)
				) {
					echo '<li class="classic-menu-dropdown " name="dataprotect">
								<img src="./img/platform/sangfor/nav-bg.png" alt="">
								<!-- <a href="./content/vm/vmreport.php" name="vm_overview" class="ajaxify"> -->
								<a href="dataprotect.html">
									数据保护 <span class="selected"></span>
								</a>
							</li>';
				}
				if (in_array("resmanagement", $arr)) {
					echo '<li class="classic-menu-dropdown " name="resmanagement">
								<img src="./img/platform/sangfor/nav-bg.png" alt="">
								<a href="resmanagement.html">
								<!-- <a href="./content/platform/storage/storage_manager.php" name="storage_manager" class="ajaxify"> -->
									资源管理 <span class="selected"></span>
								</a>
							</li>';
				}
				if (in_array("sysmanagement", $arr) || in_array("tenant", $arr)) {
					echo '<li class="classic-menu-dropdown " name="sysmanagement">
								<img src="./img/platform/sangfor/nav-bg.png" alt="">
								<a href="sysmanagement.html">
								<!-- <a href="./content/platform/settings/setting_manager.php" name="setting_manager" class="ajaxify"> -->
									系统管理 <span class="selected"></span>
								</a>
							</li>';
				}
				?>
			</ul>
		</div>
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
<!-- 		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> -->
<!-- 		</a> -->
		<!-- END RESPONSIVE MENU TOGGLER -->
		<!-- BEGIN TOP NAVIGATION MENU -->
		<div class="top-menu">
			<ul class="nav navbar-nav pull-right">
				<li class="dropdown dropdown-extended dropdown-notification dropdown-time" style="display: none;" >
					<span class="spantitle"><?php echo $LANG['UI_HOMEPAGE_SYSTEM_TIME'] ?></span>
					<span class="systemTimeTop" id="systemTimeTop" ><?php echo date("Y-m-d H:i:s") ?></span>
					<script>
						var timerId;
						var systemTimeElement = document.getElementById('systemTimeTop');
						var x = 0; // 初始化计数器

						//清理定时器
						function cleanupTimer() {
							if (timerId) {
								clearInterval(timerId);
								timerId = null;
							}
						}

						//将时间转换成时间yyy-MM-DD HH:mm:ss的格式
                        function timeStyle(time){
                            return  time.getFullYear() + '-' +
                                    ('0' + (time.getMonth() + 1)).slice(-2) + '-' +
                                    ('0' + time.getDate()).slice(-2) + ' ' +
                                    ('0' + time.getHours()).slice(-2) + ':' +
                                    ('0' + time.getMinutes()).slice(-2) + ':' +
                                    ('0' + time.getSeconds()).slice(-2);
                        }

						async function updateTimeAndCheckServer() {
                            x += 1000; // 每次调用增加1000毫秒到计数器x

                            // 更新本地时间
                            var nowTime = new Date(systemTimeElement.textContent);
                            nowTime = new Date(nowTime.getTime() + 1000);
                            var formattedDateTime = timeStyle(nowTime);
                            systemTimeElement.textContent = formattedDateTime;

                            // 检查是否需要从服务器刷新时间
                            if (x >= 900000) { //10秒更新一次
                                await refreshServerTime();
                                x = 0; // 重置计数器
                            }
                        }

						// 更新服务器时间的函数保持不变
                        async function refreshServerTime() {
                            try {
                                const response = await fetch('/api/v1/system/times/info',{
                                    method: 'GET',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'x-api-version': '1.0-rev0'  // 如果 API 版本需要在 Headers 里传递
                                    },
                                }); // 替换为你的服务器端接口地址
                                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                                const data = await response.json();
                                if(data.success){
                                    var nowTime = new Date(data.data.date);
                                    nowTime = new Date(nowTime.getTime());
                                    formattedDateTime = timeStyle(nowTime)
                                    systemTimeElement.textContent = formattedDateTime; // 更新显示
                                }
                            } catch (error) {
                                console.error('There was a problem with the fetch operation:', error);
                            }
                        }


						// 外部调用的函数，用于初始化或重新初始化定时器
						function initTimer() {
							cleanupTimer(); // 先清理可能存在的旧定时器
							timerId = setInterval(updateTimeAndCheckServer, 1000); // 设置新的定时器
						}
						//启用
						initTimer();

					</script>
				</li>
				<!-- BEGIN NOTIFICATION DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<li class="dropdown dropdown-extended dropdown-notification" id="surveyatask"  style="display: <?php echo $surveyataskShow ?>">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
						<img src="./img/platform/sangfor/nav01.png" alt="">
					</a>
					<ul class="dropdown-menu">
						<li>
							<ul class="dropdown-menu-list" data-handle-color="#637283">
								<li style="display: <?php echo $currentJobShow ?>">
									<a href="./monitor.html" class="taskhrefcurrent">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="levelchild viconfont vicon-pt_job_current_task"></i>
									</span>
									<span class="bold" id="topcurrenttask"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_TASK_CURRENT'] ?> </span>
									</a>
								</li>
								<li style="display: <?php echo $historyJobShow ?>">
									<a href="./monihis.html" class="taskhrefhistory">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="levelchild viconfont vicon-pt_job_historical_task"></i>
									</span>
									<span class="bold" id="tophistorytask"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_TASK_HISTORY'] ?> </span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				<li class="dropdown dropdown-extended dropdown-notification" id="surveyalarm" style="display: <?php echo $surveyalarmShow ?>">
					<a href="#" class="dropdown-toggle"  data-toggle="dropdown" data-hover="dropdown" data-close-others="true" id="alarmtotal">
						<img src="./img/platform/sangfor/nav02.png" alt="">
						<span class=" aa" id="alarmtotal">
					</span>
					</a>
					<ul class="dropdown-menu">
						<li>
							<ul class="dropdown-menu-list " data-handle-color="#637283">
								<li style="display: <?php echo $taskAlarmShow ?>">
									<a href="./monitask.html" class="alarmhreftask">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="levelchild viconfont vicon-pt_alarm_task_alarms  "></i>
									</span>
									<span class="bold" id="alarmtask"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_ERROR'] ?> </span>
									</a>
								</li>
								<li style="display: <?php echo $systemAlarmShow ?>">
									<a href="./monisystem.html" class="alarmhrefsystem">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="levelchild viconfont vicon-pt_alarm_system_alarm "></i>
									</span>
									<span class="bold" id="alarmsystem"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_WARNNING'] ?> </span>
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
					       <a title="' . $LANG['UI_VISUAL_SCREEN'] . '" href="../content/visualscreen/visualscreen.php" class= "dropdown-toggle visualization" target="_blank"  data-hover="dropdown"  ><img src="./img/platform/sangfor/screen-logo.png" alt=""></a></li>';
				}
				?>
				<!-- END NOTIFICATION DROPDOWN -->
				<!-- BEGIN USER LOGIN DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<li class="dropdown dropdown-user">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<img src="./img/platform/sangfor/nav03.png" alt="">
					<span class="username username-hide-on-mobile" id="username">
					<?php echo $_SESSION['tenantusername'] ?>
					</span>
					<img src="./img/platform/sangfor/nav04.png" alt="">
					</a>
					<ul class="dropdown-menu dropdown-menu-default">
						<li>
							<a href="./content/platform/users/userinfo.php" name="userinfo" class="ajaxify">
							<i class="icon-user"></i> <?php echo $LANG['UI_USER_SELF_INFO'] ?> </a>
						</li>
						<!-- <li>
							<a href="./content/platform/users/edit_password.php" name="edit_password" class="ajaxify">
							<i class="levelchild viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_USER_MODIFY_PASS'] ?> </a>
						</li> -->
						<li>
							<a href="/lock.php">
							<i class="viconfont vicon-ge_lock"></i> <?php echo $LANG['UI_USER_LOCK_SCREEN'] ?> </a>
						</li>
						
						
						<!-- <li class="divider">
						</li> -->
						
						<?php
						//如果是云祺的版本，显示帮助按钮，对应帮助文档
// 						if(in_array($CONF['SYSTEM_INFO']['enterprise'], $CONF['ENTERPRISE']) && $CONF['SYSTEM_INFO']['enterprise'] != 'vinchin_standard'){
// 						    echo   '<li>
//             						    <a href="./help/help_doc.pdf" target="_blank">
//             						    <i class="icon-doc"></i> ' . $LANG['UI_USER_HELP'] . '</a>
//             						</li>';    
// 						}
						if (in_array($CONF['SYSTEM_INFO']['enterprise'], $CONF['ENTERPRISE']) && $CONF['SYSTEM_INFO']['enterprise'] != 'vinchin_standard') {
							echo '<li>
            							<a href="./content/platform/users/aboutus.php" name="aboutus" class="ajaxify">
            							<i class="icon-flag"></i> ' . $LANG['UI_USER_ABOUT_US'] . ' </a>
                                    </li>';
						}

						?>
						<!-- <li>
							<a href="<?php echo $CONF['REMOTE']['website'] ?>" target="_blank">
							<i class="icon-flag"></i> <?php echo $LANG['UI_USER_ABOUT_US'] ?></a>
						</li> -->
						<!-- 
						<li>
							<a href="./content/platform/users/feedback.php" name="feedback" class="ajaxify">
							<i class="icon-emoticon-smile"></i> <?php echo $LANG['UI_FEEDBACK_TITLE'] ?> </a>
						</li>
						 -->
							<!--<li>
								<a href="<?php
								echo $CONF['REMOTE']['website'];
								?>" target="_blank">
								<i class="icon-doc"></i> <?php echo $LANG['UI_USER_HELP'] ?></a>
							</li> -->
						
						<!-- <li class="divider">
						</li> -->
						
						<li>
							<a href="/loginout.php">
							<i class="icon-key"></i> <?php echo $LANG['UI_USER_LOGIN_OUT'] ?> </a>
						</li>
						
					</ul>
				</li>
				<!-- END USER LOGIN DROPDOWN -->
			</ul>
		</div>
		<!-- END TOP NAVIGATION MENU -->
	</div>
	<!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<!-- 渐变线条 -->
<div class="sides-to-center"></div>