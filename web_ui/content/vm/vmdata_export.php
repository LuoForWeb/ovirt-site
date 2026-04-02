<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_BACKUP_DATA_EXPORT'] ?> <small><?php echo $LANG['UI_BACKUP_DATA_EXPORT_DESCRIPTION'] ?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i><?php echo $LANG['UI_BACKUP_DATA_EXPORT_JOB'] ?>
				</div>
			</div>
			<div class="portlet-body"  id="current_export_job">
			     <div class="row">
			         <div class="col-md-12">
			             <div class="portlet ">
                			<div class="portlet-body">
                			
                			    <div class="table-toolbar">
                					<div class="row">
                						<div class="col-md-12">
                							<div class="btn-group">
                								<button type="button" id="add" class="btn btn-sm green-haze">
								                <i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_BACKUP_NEW_TASK']?>
								                </button>
                							</div>
                						</div>
                					</div>
                				</div>
                			    <div class="table-container">
                					<table class="table table-striped table-bordered table-hover lh2" id="datatable">
                					<thead>
                					<tr role="row" class="heading">
                						<th width="15%">
                							<?php echo $LANG['UI_BACKUP_DATA_EXPORT_JOB_NAME'] ?>
                						</th>
                						<th width="10%">
                							<?php echo $LANG['UI_BACKUP_DATA_EXPORT_HYPERVISOR'] ?>
                						</th>
                						<th width="20%">
                							<?php echo $LANG['UI_BACKUP_DATA_EXPORT_DESTINATION'] ?>
                						</th>
                						<th width="8%">
                							<?php echo $LANG['UI_BACKUP_DATA_EXPORT_SPEED'] ?>
                						</th>
                						<th width="8%">
                							<?php echo $LANG['UI_BACKUP_DATA_EXPORT_PROGRESS'] ?>
                						</th>
                						<th width="8%">
                							<?php echo $LANG['UI_BACKUP_DATA_EXPORT_STATE'] ?>
                						</th>
                						<th width="10%">
                							<?php echo $LANG['UI_BACKUP_DATA_EXPORT_CREATE_TIME'] ?>
                						</th>
                						<th width="10%">
                							<?php echo $LANG['UI_BACKUP_DATA_EXPORT_OPERATION'] ?>
                						</th>
                					</tr>
                					</thead>
                					<tbody>
                					</tbody>
                					</table>
                				</div>
                			</div>
                			
			             </div>
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
<script type="text/javascript" src="./scripts/vm/vmdata_export.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	