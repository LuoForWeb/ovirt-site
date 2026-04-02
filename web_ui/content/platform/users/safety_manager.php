<?php include_once '../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
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
		//用户
		if (in_array("safety_user", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="safety" href="./content/platform/users/users.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_SAFETY_USER'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SAFETY_USER_DESCRITION'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-pt_setting_user"></i>
							</div>
						</div>
					</a>';
		}

		//用户组
		if (in_array("safety_usergroup", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="safety" href="./content/platform/users/user_group.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SAFETY_USER_GROUP_DESCRITION'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-yonghuzu1"></i>
							</div>
						</div>
					</a>';
		}

		//角色
		if (in_array("safety_role", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="safety" href="./content/platform/users/role.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_SAFETY_ROLE'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SAFETY_ROLE_DESCRITION'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="viconfont vicon-yonghu"></i>
							</div>
						</div>
					</a>';
		}

		// 域服务器
		if (in_array("safety_domain", $userAllPermission)) {
			echo '<a class="vinchin-wrap__group__card module-card no-btns ajaxify" name="safety" href="./content/platform/users/domain_server.php">
						<div class="upper-region">
							<div class="upper-region__left">
								<div class="upper-region__left__title mb-8">
									' . $LANG['UI_PLATFORM_SAFETY_DOMAIN'] . '
								</div>
								<span class="upper-region__left__des">
									' . $LANG['UI_SAFETY_DOMAIN_SERVER_DESCRITION'] . '
								</span>
							</div>
							<div class="upper-region__right">
								<i class="iconfont icon-domain"></i>
							</div>
						</div>
					</a>';
		}
		?>
	</div>
</div>

<!-- END PAGE CONTENT-->

























<!-- <div class="row"> -->
<!-- 	<a class="ajaxify"  href="./content/platform/users/users.php"> -->
<!-- 		<i class="fa fa-wrench"></i> 用户 -->
<!-- 	</a> -->
<!-- 	<a class="ajaxify"  href="./content/platform/users/user_group.php"> -->
<!-- 		<i class="fa fa-wrench"></i> 用户组 -->
<!-- 	</a> -->
<!-- 	<a class="ajaxify"  href="./content/platform/users/role.php"> -->
<!-- 		<i class="fa fa-wrench"></i> 角色 -->
<!-- 	</a> -->
<!-- 	<a class="ajaxify"  href="./content/platform/users/domain_server.php"> -->
<!-- 		<i class="fa fa-wrench"></i> 域 -->
<!-- 	</a> -->
<!-- </div> -->