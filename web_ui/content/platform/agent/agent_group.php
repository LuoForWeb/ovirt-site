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
		<div class="portlet-body" id="agentGroupList">
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left">
						<div class="btn-group">
							<button type="button" id="addGroup" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD'] ?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="editGroup" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_PUBLIC_MODIFY'] ?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="deleteGroup" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
							</button>
						</div>
					</div>
					<div class="table-toolbar-wrapper__right">
						<div class="table-actions-wrapper page-right">
							<input id="searchName" style="margin-top:1px;" type="search" maxlength="128" class="searchinput table-group-action-input form-control input-inline input input-sm" placeholder="<?php echo $LANG['UI_AGENT_GROUP_SEARCH'] ?>" aria-controls="example">
							<input id="searchNameBtn" type="button" class="btn btn-sm default" value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>">
						</div>
					</div>
				</div>
				<div class="table-container">
					<table class="table table-striped table-hover" id="agentGroupTable">
					<thead>
					<tr role="row" class="heading">
						<th width="4%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="26%">
							 <?php echo $LANG['UI_AGENT_GROUP_NAME'] ?>
						</th>
						<th width="25%">
							<?php echo $LANG['UI_PUBLIC_REMARK'] ?>
						</th>
						<th width="10%">
							 <?php echo $LANG['UI_AGENT_GROUP_NUM'] ?>
						</th>
						<th width="20%">
							 <?php echo $LANG['UI_JOB_CREATE_TIME'] ?>
						</th>
						<th width="15%">
							 <?php echo $LANG['UI_JOB_CREATOR'] ?>
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
<script type="text/javascript" src="./scripts/platform/agent/agent_group.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	