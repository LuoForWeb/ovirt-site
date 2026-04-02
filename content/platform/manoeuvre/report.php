<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_DRILLS_REPORT']?>
<!--    <small>--><?php //echo $LANG['UI_DRILLS_REPORT_TIPS']?><!--</small>-->
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki" id="historycontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-nreport"></i><?php echo $LANG['UI_DRILLS_REPORT_LIST']?>
				</div>
			</div>
			<div class="portlet-body">
			
			    <div class="table-toolbar">
					<div class="row">
						<div class="col-md-12">
							<div class="btn-group">
								<button type="button" id="delete" class="btn btn-sm green-haze">
								<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE']?>
								</button>
							</div>
						</div>
					</div>
				</div>
				
				<div class="table-container">
				    <div class="table-actions-wrapper">
						<span>
						</span>
					</div>
					<table class="table table-striped table-bordered table-hover" id="datatable">
					<thead>
					<tr role="row" class="heading">
						<th width="2%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="3%">
							<?php echo $LANG['UI_PUBLIC_NUMBER']?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_JOB_RNAME']?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_PUBLIC_MODULE_TYPE']?>
						</th>
						<th width="5%">
							<?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>
						</th>
						<th width="5%">
							<?php echo $LANG['UI_JOB_CREATOR']?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_VCENTER_MACHINE']?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_STORAGE_TOTAL_SIZE']?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_JOB_START_TIME']?>
						</th>
						<th width="8%">
						    <?php echo $LANG['UI_JOB_OVER_TIME']?>
						</th>
						<th width="5%">
						    <?php echo $LANG['UI_PUBLIC_STATUS']?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_DRILLS_REPORT_LIST_DETAIL']?>
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
<script type="text/javascript" src="./scripts/platform/manoeuvre/report.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	