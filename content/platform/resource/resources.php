<?php include_once '../../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title settings-title">
	<a class="ajaxify" name="resource_group" href="./content/platform/resource/resource_group.php">
		<i class="viconfont vicon-resource_group" style="color: #20c99a;"></i>
		<span><?php echo $LANG['UI_RESOURCE_GROUP_MANAGE']?></span> -
	</a>
	<span style="color: #575962;"><?php  echo $_GET['resourcegroupname'];?></span>
</h3>
<?php 
$tab = intval($_GET['tab']);
$tabActive = array("", "", "", "");
$tabActive[$tab] = "active";
?>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<input id="resourcegroupuuid" value="<?php  echo $_GET['resourcegroupuuid'];?>" class="display-none"></input>
	   <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki" id='authdiv'>
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
					<li class="<?php echo $tabActive[0]; ?> ">
						<a href="#vmdiv" data-toggle="tab">
						<i class="iconfont icon-xuniji"></i> <?php echo $LANG['UI_PLATFORM_VM']?> </a>
					</li>
					<li class="<?php echo $tabActive[1]; ?> ">
						<a href="#proxydiv" data-toggle="tab">
						<i class="iconfont icon-appliance"></i> <?php echo $LANG['UI_PLATFORM_VM_APPLIANCE']?> </a>
					</li>
					<li class="<?php echo $tabActive[2]; ?> ">
						<a href="#nodediv" data-toggle="tab">
						<i class="iconfont icon-beifenjiedian"></i> <?php echo $LANG['UI_PALTFORM_NODE']?> </a>
					</li>
					<li class="<?php echo $tabActive[3]; ?> ">
						<a href="#storagediv" data-toggle="tab">
						<i class="iconfont icon-beifencunchu"></i> <?php echo $LANG['UI_PLATFORM_STORAGE_RESOURCE']?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content row" style="margin: 0px 10px 10px 10px">
					<div class="tab-pane <?php echo $tabActive[0]; ?> " id="vmdiv">
        				<?php include_once './resourcestab/vm_resource.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[1]; ?> " id="proxydiv">
        				<?php include_once './resourcestab/appliance_resource.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[2]; ?> " id="nodediv">
        				<?php include_once './resourcestab/node_resource.php';?>
					</div>
					
					<div class="tab-pane <?php echo $tabActive[3]; ?> " id="storagediv">
        				<?php include_once './resourcestab/storage_resource.php';?>
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
<script type="text/javascript" src="./scripts/platform/resource/resources.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	