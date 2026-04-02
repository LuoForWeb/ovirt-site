<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki" id="applianceContent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-appliance_manager"></i><?php echo $LANG['UI_APPLIANCE_NAME'] ?>
				</div>
			</div>
			<div class="portlet-body m-10">
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left">
                        <?php
                        if(in_array("p_appliance_manager_add", $_SESSION['permissionArr'])) {
                            // 新建
                            echo '<div class="btn-group">
							<button type="button" id="addAppliance" class="btn table-toolbar-btn">
								<i class="viconfont vicon-ge_add_task"></i> '. $LANG['UI_PUBLIC_ADDNEW'] .'
							</button>
						</div>';
                        }
                        if(in_array("p_appliance_manager_edit", $_SESSION['permissionArr'])) {
                            // 修改
                            echo '<div class="btn-group">
							<button type="button" id="editAppliance" class="btn table-toolbar-btn">
								<i class="viconfont vicon-ge_modify"></i> '.$LANG['UI_PUBLIC_MODIFY'] .'
							</button>
						</div>';
                        }
                        if(in_array("p_appliance_manager_delete", $_SESSION['permissionArr'])) {
                            // 删除
                            echo '<div class="btn-group">
							<button type="button" id="deleteAppliance" class="btn table-toolbar-btn">
								<i class="viconfont vicon-ge_delete"></i>'. $LANG['UI_PUBLIC_DELETE'] .'
							</button>
						</div>';
                        }

                        ?>
					</div>
				</div>

				<div class="table-container" >
					<table class="table table-hover" id="appliance_datatable">
					<thead>
					<tr role="row" class="heading">
						<th width="3%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="6%">
							<?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
						</th>
						<th width="16%">
							<?php echo $LANG['UI_APPLIANCE_NICKNAME'] ?>
						</th>
						<th width="16%">
							<?php echo $LANG['UI_APPLIANCE_IP'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_APPLIANCE_PORT'] ?>
						</th>
						<th width="14%">
							<?php echo $LANG['UI_APPLIANCE_STATUS'] ?>
						</th>
						<th width="20%">
							<?php echo $LANG['UI_APPLIANCE_CREATE_TIME'] ?>
						</th>
						<th width="15%">
							<?php echo $LANG['UI_APPLIANCE_CREATE_USER'] ?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- End: life time stats -->

	</div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/appliance/appliance_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->