<?php include_once '../../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_PLATFORM_BACKUP_AGENT']?> <small><?php echo $LANG['UI_AGENT_DES']?></small>
</h3>

<?php 

//权限管理,根据用户的权限,显示tab
// $permission = $_SESSION['permission'];
// $tab1 = in_array('my_agent', $permission);
// $tab2 = in_array('agent_manager', $permission);

// //控制tab显示
// $tab1Show = $tab1 ? "" : "none";
// $tab2Show = $tab2 ? "" : "none";
// //控制tab active状态
// //两个都有,第一个active;只有一个,直接active
// if($tab1 && $tab2){
//     $tab1Active = "active";
//     $tab2Active = "";
// }else{
//     $tab1Active = $tab1 ? "active" : "";
//     $tab2Active = $tab2 ? "active" : "";
// }
$permission = $_SESSION['permission'];
$tab = intval($_GET['tab']);
$tabActive = array("", "", "");
$tabActive[$tab] = "active";

?>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
					<?php 
    				    if(in_array('my_agent', $permission)){
    					   echo '<li class="'.$tabActive[0].'">
            						<a href="#myagentdiv" data-toggle="tab">
            						<i class="fa fa-puzzle-piece "></i>'.$LANG['UI_AGENT_MY_AGENT'].'</a>
            					</li>';    
    					}
    					
    					if(in_array('agent_manager', $permission)){
    					    echo '<li class="'.$tabActive[1].'">
            						<a href="#agentmanagerdiv" data-toggle="tab">
            						<i class="icon-puzzle "></i>'.$LANG['UI_AGENT_MANAGER'].'</a>
            					</li>';
    					}
					?>
					<li class="<?php echo $tabActive[2];?>" >
						<a href="#agentgroupdiv" data-toggle="tab">
						<i class="fa fa-list"></i> <?php echo $LANG['UI_AGENT_GROUP_MANAGE']?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content row margin10">
					<div class="tab-pane <?php echo $tabActive[0];?> " id="myagentdiv" >
                        <?php 
                            if(in_array('my_agent', $permission)){
                                include_once './my_agent.php';
                            }
                        ?>						
					</div>
					
					<div class="tab-pane <?php echo $tabActive[1];?> " id="agentmanagerdiv" >
        				<?php 
                            if(in_array('agent_manager', $permission)){
                                include_once './agent_manager.php';
                            }
                        ?>	
					</div>
					<div class="tab-pane  <?php echo $tabActive[2];?>" id="agentgroupdiv" >
        				<?php include_once './agent_group.php';?>
					</div>
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