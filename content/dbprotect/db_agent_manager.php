<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_DB_AGENT_MANAGE'] ?> <small><?php echo $LANG['UI_DB_AGENT_MANAGE_TIPS'] ?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki" id="dbagentcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="icon-puzzle"></i><?php echo $LANG['UI_DB_BACKUP_AGENT_LIST'] ?>
				</div>
			</div>
			<div class="portlet-body">
			
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left">
						<div class="btn-group">
							<button type="button" id="add" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_DB_ADD_AGENT'] ?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="delete" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_DB_DELETE_AGENT'] ?>
							</button>
						</div>
					</div>
					<div class="table-toolbar-wrapper__right" style="display:none;">
						<div class="table-actions-wrapper page-right" >
							<span>
							</span>
							<button type="button" id="searchAll" class="btn green-haze">
							<i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
							</button>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12" >
						<div id="searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span style="color:#5b9bd1;" class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span> </div>
					</div>
				</div>
				
				<div class="table-container">
					<table class="table table-striped table-hover lh2" id="agentTable">
					<thead>
					<tr role="row" class="heading">
						<th width="3%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="12%">
							 <?php echo $LANG['UI_AGENT_HOST_NAME'] ?>
						</th>
						<th width="8%">
							 <?php echo $LANG['UI_VCENTER_RNAME'] ?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
						</th>
						<th width="25%">
							<?php echo $LANG['UI_PUBLIC_OS'] ?>
						</th>
						<th width="12%">
							<?php echo $LANG['UI_VCENTER_ADD_TIME'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_DB_DATABASE_TYPE'] ?>
						</th>
						<th width="6%">
							<?php echo $LANG['UI_AGENT_AUTH_MODULE'] ?>
						</th>
						<th width="6%">
							<?php echo $LANG['UI_PUBLIC_STATUS'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_PUBLIC_OPERATION'] ?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
					
					<div class="alert alert-block alert-info fade in" id="marktips">
						<button type="button" class="close" data-dismiss="alert"></button>
						<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
						<ol class = "alert-ol">
							<li>
								<?php echo $LANG['UI_DB_AGENT_LIST_TIPS1'] ?>
							</li>
							<li>
								<?php echo $LANG['UI_DB_AGENT_LIST_TIPS2'] ?>
							</li>
							<li>
								<?php echo $LANG['UI_DB_AGENT_LIST_TIPS3'] ?>
							</li>
							<li>
								<?php echo $LANG['UI_DB_AGENT_LIST_TIPS4'] ?>
							</li>
						</ol>
					</div>
				</div>
			</div>
		</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN ADD MODAL -->
		<div id="addAgentModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title agentTitle"></h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					<form action="#" id="agentForm" class="form-horizontal">
						<div class="form-group mb5">
							<label class="control-label col-md-3"><?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
										<i class="fa"></i>
									<input id="agentIp" name="agentip" type="text" maxlength="64" class="form-control input input-sm" aria-controls="example">
									<div><span class="help-block ">
									<?php echo $LANG['UI_DB_AGENT_IP_TIPS'] ?>
									</span></div>
								</div>
							</div>
						</div>
						
						<div class="form-group mb5">
							<label class="control-label col-md-3"><?php echo $LANG['UI_VCENTER_RNAME'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
										<i class="fa"></i>
									<input name="nickname" id="agentName" type="text" maxlength="64" class="form-control input input-sm" aria-controls="example">
									<div><span class="help-block ">
									<?php echo $LANG['UI_AGENT_HOST_NAME_L_TIPS'] ?>
									</span></div>
								</div>
							</div>
						</div>
						
						<div class="form-group mb5">
							<label class="control-label col-md-3"><?php echo $LANG['UI_DB_AGENT_MANAGE_PORT'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-6">
								<div class="input-icon right">
									<i class="fa"></i>
									<input id="port" name="manageport" type="text" maxlength="64" class="form-control input input-sm" aria-controls="example" value="20200">
									<div><span class="help-block ">
									<?php echo $LANG['UI_DB_AGENT_MANAGE_PORT_TIPS'] ?>
									</span></div>
								</div>
							</div>
						</div>
						
						<div class="form-group highDiv mb5">
							<label class="control-label col-md-3"><?php echo $LANG['UI_DB_AGENT_TRANSPORT_PORT'] ?>
							</label>
							<div class="col-md-6">
								<input id="transferport" type="text" maxlength="64" class="form-control input input-sm" aria-controls="example" value="20300">
								<div><span class="help-block ">
								<?php echo $LANG['UI_DB_AGENT_TRANSFER_PORT_TIPS'] ?>
								</span></div>
							</div>

						</div>
						
						<div class="form-group highDiv mb5">
							<label class="control-label col-md-3"><?php echo $LANG['UI_DB_AGENT_CLIENT_PORT'] ?>
							</label>
							<div class="col-md-6">
								<input  id="clientport" type="text" maxlength="64" class="form-control input input-sm" aria-controls="example" value="20400">
								<div><span class="help-block ">
								<?php echo $LANG['UI_DB_AGENT_CLIENT_PORT_TIPS'] ?>
								</span></div>
							</div>
						</div>
					</form>
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button"class="btn btn-primary" id="addagent_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
				<button type="button"class="btn btn-primary" id="modify_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END ADD MODAL -->
		
		<!-- BEGIN AUTH MODAL -->
		<div id="authAgentModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input  id="agentNickname" type="text" maxlength="64" class="form-control input input-sm display-none">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-badge"></i> <?php echo $LANG['UI_DB_AGENT_AUTH'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="col-md-offset-3 col-md-8" id="dblisence" style="color: #ff5714;">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_AGENT_HOST_NAME_L'] ?>: </label>
					<div class="col-md-8">
						<p class="pt7" id="agentname"></p>
					</div>
				</div>
				<!--  <div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_AGENT_TO_USER'] ?><span class="required"> * </span></label>
					<div class="col-md-8">
						<select class="form-control select2me" id="user">
						</select>
						<span class="help-block">
						<?php echo $LANG['UI_AGENT_TO_USER_TIPS'] ?> </span>
					</div>
				</div>-->
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_AGENT_ADD_AUTH'] ?></label>
					<div class="col-md-8">
						<div>
							<label class="checkbox-inline pl0"><input type="checkbox" class="icheck authCheck" id="database"> 
							<a><i class="fa fa-database"></i></a> <?php echo $LANG['UI_DB_DATABASE_AUTH'] ?> </label>
						</div>
						<span class="help-block">
						<?php echo $LANG['UI_DB_AGENT_ADD_AUTH_TIPS'] ?> </span>
					</div>
				</div>
				<div class="alert alert-danger display-hide" id="modaltips">
					<button type="button" class="close" data-close="alert"></button>
					<?php echo $LANG['UI_AGENT_ADD_INPUT_TIPS'] ?>
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button"class="btn btn-primary" id="auth_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END AUTH MODAL -->
		
		
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/dbprotect/db_agent_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	