<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- <link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" /> -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats grey-cararra -->
		<div class="portlet-body" id="myagentlist">
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left">

					</div>
					<div class="table-toolbar-wrapper__right">
						<div class="btn-group">
							<button type="button" id="delete" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_AGENT_DELETE_OFFLINE'] ?>
							</button>
						</div>
					</div>
				</div>
				<div class="table-container">
					<table class="table table-striped table-hover" id="agentdatatable">
					<thead>
					<tr role="row" class="heading">
						<th width="3%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="15%">
							 <?php echo $LANG['UI_AGENT_HOST_NAME'] ?>
						</th>
						<th width="10%">
							 <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
						</th>
						<th width="22%">
							 <?php echo $LANG['UI_PUBLIC_OS'] ?>
						</th>
						<th width="15%">
							 <?php echo $LANG['UI_AGENT_REGIST_TIME'] ?>
						</th>
						<th width="7%">
							 <?php echo $LANG['UI_AGENT_AUTH_MODULE'] ?>
						</th>
						<th width="10%">
							 <?php echo $LANG['UI_PUBLIC_STATUS'] ?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
				</div>
			</div>
		<!-- End: life time stats -->
	</div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/agent/my_agent.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	