<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_VCENTER_ENGINE_BACKUP_DATA_MANAGE'] ?>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki" id="engineContent">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-list"></i><?php echo $LANG['UI_VCENTER_ENGINE_BACKUP_DATA_LIST'] ?>
				</div>
			</div>
			<div class="portlet-body">
			
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left">
						<div class="btn-group">
							<button type="button" id="delete" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="returnVcenter" class="btn table-toolbar-btn">
							<i class="icon-action-undo"></i> <?php echo $LANG['UI_VCENTER_ENGINE_RETURN_VCENTER'] ?>
							</button>
						</div>
					</div>
				</div>
				
				<div class="table-container">
					<table class="table table-striped table-hover" id="engine_datatable">
					<thead>
					<tr role="row" class="heading">
						<th width="3%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="6%">
							<?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
						</th>
						<th width="12%">
							<?php echo $LANG['UI_VCENTER_ENGINE_VCENTER_IP'] ?>
						</th>
						<th width="12%">
							<?php echo $LANG['UI_VCENTER_ENGINE_VCENTER_NICKNAME'] ?>
						</th>
						<th width="12%">
							<?php echo $LANG['UI_VCENTER_TYPE'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_VCENTER_ENGINE_VCENTER_BACKUP_TIME'] ?>
						</th>
						<th width="9%">
							<?php echo $LANG['UI_BACKUP_FILE_FILESIZE'] ?>
						</th>
						<th width="15%">
							<?php echo $LANG['UI_REPORT_NODE'] ?>
						</th>
						<th width="11%">
							<?php echo $LANG['UI_DATA_OF_STORAGE'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
					<div class="alert alert-block alert-info fade in" id="marktips">
						<button type="button" class="close" data-dismiss="alert"></button>
						<ul class="alert-ul">
							<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
							<li>
								<?php echo $LANG['UI_VCENTER_ENGINE_MANAGE_TIPS'] ?>
							</li>
						</ul>
					</div>
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
<script type="text/javascript" src="./scripts/vm/engine_data_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	