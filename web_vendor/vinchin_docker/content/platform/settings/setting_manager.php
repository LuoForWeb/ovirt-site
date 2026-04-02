<?php include_once '../../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_PLATFORM_SYSTEM_SET_MANAGER']?> <small><?php echo $LANG['UI_SETTINGS_SET_DES']?></small>
</h3>
<?php 
$tab = intval($_GET['tab']);
$tabActive = array("", "", "", "", "", "", "", "", "");
$tabActive[$tab] = "active";
?>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki" id='authdiv'>
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
					<li class="<?php echo $tabActive[0]; ?> ">
						<a href="#ipdiv" data-toggle="tab">
						<i class="icon-pointer "></i> <?php echo $LANG['UI_PLATFORM_EDIT_IP']?> </a>
					</li>
					<!-- 
					<li class="<?php echo $tabActive[1]; ?> ">
						<a href="#timediv" data-toggle="tab">
						<i class="icon-clock "></i> <?php echo $LANG['UI_PLATFORM_SET_TIME']?> </a>
					</li>
					<li>
						<a href="#systemnamediv" data-toggle="tab">
						<i class="fa fa-edit "></i><?php echo $LANG['UI_PLATFORM_SET_DIY']?> </a>
					</li>
					<li>
						<a href="#systemdatadiv" data-toggle="tab">
						<i class="fa fa-database "></i><?php echo $LANG['UI_PLATFORM_SYSTEM_DATA']?> </a>
					</li>
					 -->
					<li class="<?php echo $tabActive[2]; ?> ">
						<a href="#noticediv" data-toggle="tab">
						<i class="fa fa-bullhorn "></i> <?php echo $LANG['UI_PLATFORM_SYSTEM_NOTICE']?> </a>
					</li>
					<li class="<?php echo $tabActive[3]; ?> ">
						<a href="#dnsdiv" data-toggle="tab">
						<i class="fa fa-retweet "></i> <?php echo $LANG['UI_PLATFORM_SYSTEM_DNS']?> </a>
					</li>
					<li class="<?php echo $tabActive[4]; ?> ">
						<a href="#poweroffdiv" data-toggle="tab">
						<i class="fa fa-power-off "></i> <?php echo $LANG['UI_PLATFORM_POWER']?> </a>
					</li>
					<li class="<?php echo $tabActive[5]; ?> ">
						<a href="#upgradediv" data-toggle="tab">
						<i class="fa fa-rocket "></i> <?php echo $LANG['UI_PLATFORM_SYSTEM_UPDATE']?> </a>
					</li>
					<!-- 
					<li>
						<a href="#factoryresetdiv" data-toggle="tab">
						<i class="fa fa-undo "></i> <?php echo $LANG['UI_PLATFORM_RESET_FACTORY']?> </a>
					</li> 
					 -->
					 <li id="messagepush" class="<?php echo $tabActive[6]; ?> ">
						<a href="#messagepushdiv" data-toggle="tab">
						<i class="fa fa-send "></i> <?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH']?> </a>
					</li>
					
					 <li id="visualconfig" class="<?php echo $tabActive[7]; ?> " style="<?php $authfun = $_SESSION['authfun']; if(!$authfun['visualization']){echo "display: none";}?>">
						<a href="#visualconfigdiv" data-toggle="tab">
						<i class="fa fa-desktop "></i> <?php echo $LANG['UI_PUBLIC_VISUAL_CONFIG']?> </a>
					</li>
					
					<li id="systemservice" class="<?php echo $tabActive[8]; ?>">
						<a href="#servicediv" data-toggle="tab">
						<i class="fa fa-wrench"></i> <?php echo $LANG['UI_SETTINGS_SYSTEM_TOOL']?></a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content row margin10">
					<div class="tab-pane <?php echo $tabActive[0]; ?> " id="ipdiv">
                        <?php include_once './settingstab/set_ip.php';?>						
					</div>
					
					<div class="tab-pane <?php echo $tabActive[1]; ?> " id="timediv">
        				<?php include_once './settingstab/set_time.php';?>
					</div>
					
					<div class="tab-pane " id="systemnamediv">
        				<?php include_once './settingstab/system_name.php';?>
					</div>
					
					<div class="tab-pane " id="systemdatadiv">
        				<?php include_once './settingstab/data_export.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[2]; ?> " id="noticediv">
        				<?php include_once './settingstab/system_notice.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[3]; ?> " id="dnsdiv">
        				<?php include_once './settingstab/system_dns.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[4]; ?> " id="poweroffdiv">
        				<?php include_once './settingstab/system_poweroff.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[5]; ?> " id="upgradediv">
        				<?php include_once './settingstab/system_upgrade.php';?>
					</div>
					
					<div class="tab-pane " id="factoryresetdiv">
        				<?php include_once './settingstab/factory_reset.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[6]; ?> " id="messagepushdiv">
        				<?php include_once './settingstab/message_push.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[7]; ?> " id="visualconfigdiv">
        				<?php include_once './settingstab/visual_config.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[8]; ?> " id="servicediv">
        				<?php include_once './settingstab/system_service.php';?>
					</div>
					
				</div>
			</div>
		</div>
		<!-- END TAB PORTLET-->
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<?php 
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' . 
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/settings/setting_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	