<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_DRILLS_TASK']?> <small><?php echo $LANG['UI_DRILLS_TASK_TIPS']?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats grey-cararra -->
		<div class="portlet box blue-hoki" >
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-renwu"></i><?php echo $LANG['UI_JOB_LIST']?>
				</div>
			</div>
			<div class="portlet-body" id="current_job">
    			<div class="table-toolbar">
    				<div class="row">
    					<div class="col-md-12">
    						<div class="btn-group ">
    							<button type="button" id="addtask" class="btn btn-sm green-haze">
    							<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_DRILLS_CREATE']?>
    							</button>
    						</div>
    					</div>
    				</div>
    			</div>
				<div class="table-container">
				    <div class="table-actions-wrapper">
						<span>
						</span>
						<!--<select id="moduletype" class="table-group-action-input form-control input-inline input-small input-sm">
							<option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
							<option value="2"><?php echo $LANG['UI_PLATFORM_VM']?></option>
							<option value="3"><?php echo $LANG['UI_PLATFORM_FILE']?></option>
							
						</select>-->
					</div>
					<table class="table table-striped table-bordered table-hover lh2" id="datatable">
					<thead>
					<tr role="row" class="heading">
						<th width="15%">
							 <?php echo $LANG['UI_JOB_RNAME']?>
						</th>
						<th width="10%">
							 <?php echo $LANG['UI_PUBLIC_MODULE_TYPE']?>
						</th>
						<th width="8%">
							 <?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>
						</th>
						<th width="15%">
							<?php echo $LANG['UI_JOB_CREATE_TIME']?>
						</th>
						<th width="7%">
							<?php echo $LANG['UI_PUBLIC_STATUS']?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_EMERGENCY_ALL_PLAN']?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_JOB_CREATOR']?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_PUBLIC_OPERATION']?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
					
					<div class="alert alert-block alert-info fade in" id="marktips">
						<button type="button" class="close" data-dismiss="alert"></button>
						<ul class="alert-ul">
							<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
							<li>
								<?php echo $LANG['UI_JOB_CURRENT_NAME_TIPS']?>
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
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/manoeuvre/task.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	