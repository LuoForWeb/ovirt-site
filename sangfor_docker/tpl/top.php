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
if("1" == $_COOKIE['sidebar_closed']){
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
if(in_array('current_job', $permission) || in_array('history_job', $permission)){
    $surveyataskShow = "";
}

$surveyalarmShow = "none";
if(in_array('task_alarm', $permission) || in_array('system_alarm', $permission)){
    $surveyalarmShow = "";
}
?>



<body class="page-header-fixed   page-quick-sidebar-over-content <?php echo $sidebarClass; ?>">
<!-- BEGIN HEADER -->
<div class="page-header navbar navbar-fixed-top">
	<!-- BEGIN HEADER INNER -->
	<div class="page-header-inner">
		<!-- BEGIN LOGO -->
		<div class="page-logo">
			<a href="./" >
			<?php 
        	   if($CONF['SOFTWARE_VERSION']['FREE_EDITION'] == $softwareType){
        	       //如果是免费版,使用专用logo
        	       echo '<img src="./img/platform/logo-home.png" alt="" class="logo-default logosizefree">';
        	   }else{
        	       //如果是其他版本
				   $logo = "./img/platform/logo-home.png";
        	       echo '<img src="' . $logo . '" alt="" class="" style="width: 350px;margin: 5px 0 0 !important;">';
        	   }
        	?>
			</a>
			<div class="menu-toggler sidebar-toggler hide">
				<!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
			</div>
		</div>
		<!-- END LOGO -->
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
		</a>
		<!-- END RESPONSIVE MENU TOGGLER -->
		<div class="hor-menu hor-menu-light  ">
			<ul class="nav navbar-nav"  data-close-others="true">
				<!-- DOC: Remove data-hover="megadropdown" and data-close-others="true" attributes below to disable the horizontal opening on mouse hover -->
				
			</ul>
		</div>
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
<!-- 		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> -->
<!-- 		</a> -->
		<!-- END RESPONSIVE MENU TOGGLER -->
		<!-- BEGIN TOP NAVIGATION MENU -->
		<div class="top-menu">
			<ul class="nav navbar-nav pull-right">
			    <!-- BEGIN NOTIFICATION DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<li class="dropdown dropdown-extended dropdown-notification" id="surveyatask"  style="display: <?php echo $surveyataskShow?>">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<i class="fa fa-tasks"></i>
				    </span>
					</a>
					<ul class="dropdown-menu">
						<li>
							<ul class="dropdown-menu-list" data-handle-color="#637283">
								<li style="display: <?php echo $currentJobShow?>">
									<a href="javascript:;" class="taskhrefcurrent">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="fa fa-tachometer"></i>
									</span>
									<span class="bold" id="topcurrenttask"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_TASK_CURRENT']?> </span>
									</a>
								</li>
								<li style="display: <?php echo $historyJobShow?>">
									<a href="javascript:;" class="taskhrefhistory">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-history"></i>
									</span>
									<span class="bold" id="tophistorytask"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_TASK_HISTORY']?> </span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				<li class="dropdown dropdown-extended dropdown-notification" id="surveyalarm" style="display: <?php echo $surveyalarmShow?>">
					<a href="#" class="dropdown-toggle"  data-toggle="dropdown" data-hover="dropdown" data-close-others="true" id="alarmtotal">
					<i class="icon-bell"></i>
					<span class="badge badge-danger display-hide" id="alarmtotal">
				    </span>
					</a>
					<ul class="dropdown-menu">
						<li>
							<ul class="dropdown-menu-list " data-handle-color="#637283">
								<li style="display: <?php echo $taskAlarmShow?>">
									<a href="javascript:;" class="alarmhreftask">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="iconfont icon-taskalarm "></i>
									</span>
									<span class="bold" id="alarmtask"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_ERROR']?> </span>
									</a>
								</li>
								<li style="display: <?php echo $systemAlarmShow?>">
									<a href="javascript:;" class="alarmhrefsystem">
									<span class="details">
									<span class="label label-sm label-icon label-info">
									<i class="iconfont icon-systemalarm "></i>
									</span>
									<span class="bold" id="alarmsystem"> </span> <?php echo $LANG['UI_ALARM_TOP_TIPS_WARNNING']?> </span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				
				<li class="dropdown dropdown-extended dropdown-notification display-none" id="visualscreen" style="<?php $authfun = $_SESSION['authfun']; if(!$authfun['visualization']){echo "display: none;";}?>">
					<a title="<?php echo $LANG['UI_VISUAL_SCREEN']?>" href="./visualization.php" class= "dropdown-toggle visualization" target="_blank"  data-hover="dropdown"  >
					<i class="icon-screen-desktop"></i>
					</a>
				</li>
				<!-- END NOTIFICATION DROPDOWN -->
				<!-- BEGIN USER LOGIN DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<li class="dropdown dropdown-user">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<i class="icon-user"></i>
					<span class="username username-hide-on-mobile" id="username">
					<?php echo $_SESSION['userName']?>
					</span>
					<i class="fa fa-angle-down"></i>
					</a>
					<ul class="dropdown-menu dropdown-menu-default">
						<li>
							<a href="./content/platform/users/userinfo.php" name="userinfo" class="ajaxify">
							<i class="icon-user"></i> <?php echo $LANG['UI_USER_SELF_INFO']?> </a>
						</li>
						<li>
							<a href="./content/platform/users/edit_password.php" name="edit_password" class="ajaxify">
							<i class="fa fa-pencil"></i> <?php echo $LANG['UI_USER_MODIFY_PASS']?> </a>
						</li>
						<li>
							<a href="/lock.php">
							<i class="icon-lock"></i> <?php echo $LANG['UI_USER_LOCK_SCREEN']?> </a>
						</li>
						
						
						<!--<li class="divider">
						</li>-->
						
						<?php 
						//如果是云祺的版本，显示帮助按钮，对应帮助文档
						if(in_array($CONF['SYSTEM_INFO']['enterprise'], $CONF['ENTERPRISE']) && $CONF['SYSTEM_INFO']['enterprise'] != 'vinchin_standard'){
						    echo   '<li>
            						    <a href="./help/help_doc.pdf" target="_blank">
            						    <i class="icon-doc"></i> ' . $LANG['UI_USER_HELP'] . '</a>
            						</li>';
						}
						
						?>
						<!--
						<li>
						    <a href="<?php echo $CONF['REMOTE']['website']?>" target="_blank">
						    <i class="icon-flag"></i> <?php echo $LANG['UI_USER_ABOUT_US']?></a>
						</li>
						-->
						<!-- 
						<li>
							<a href="./content/platform/users/feedback.php" name="feedback" class="ajaxify">
							<i class="icon-emoticon-smile"></i> <?php echo $LANG['UI_FEEDBACK_TITLE']?> </a>
						</li>
						 -->
							<!--<li>
							    <a href="<?php 
							        echo $CONF['REMOTE']['website'];
							    ?>" target="_blank">
							    <i class="icon-doc"></i> <?php echo $LANG['UI_USER_HELP']?></a>
							</li> -->
						
						<li class="divider">
						</li>
						
						<li>
							<a href="/loginout.php">
							<i class="icon-key"></i> <?php echo $LANG['UI_USER_LOGIN_OUT']?> </a>
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
<div class="clearfix">
</div>