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
		<div class="portlet-body" id="agentlist">
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left"></div>
					<div class="table-toolbar-wrapper__right">
						<div class="table-actions-wrapper page-right">
							<input type="search" maxlength="128" class="searchinput table-group-action-input form-control input-inline input" placeholder="<?php echo $LANG['UI_SEARCH_HOST_NAME'] ?>" aria-controls="example">
							<input id="searchbtn" type="button" class="btn btn-search" value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>">
							<button type="button" id="searchAll" class="btn green-haze">
							<i class="fa fa-search"></i><?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?></button>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12" >
							<div id="searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span style="color:#5b9bd1;" class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span> </div>
					</div>
				</div>
				<div class="table-container">
					<table class="table table-striped table-hover lh2" id="agentmanagerdatatable">
					<thead>
					<tr role="row" class="heading">
						<th width="15%">
							 <?php echo $LANG['UI_AGENT_HOST_NAME'] ?>
						</th>
						<th width="10%">
							 <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
						</th>
						<th width="25%">
							 <?php echo $LANG['UI_PUBLIC_OS'] ?>
						</th>
						<th width="15%">
							 <?php echo $LANG['UI_AGENT_REGIST_TIME'] ?>
						</th>
						<th width="7%">
							 <?php echo $LANG['UI_AGENT_AUTH_MODULE'] ?>
						</th>
						<th width="7%">
							 <?php echo $LANG['UI_AGENT_OWNER_USER'] ?>
						</th>
						<th width="10%">
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
				</div>
			</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-puzzle"></i> <?php echo $LANG['UI_AGENT_REGIST'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="col-md-offset-3 col-md-8" id="filelisence" style="color: #ff5714;">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_AGENT_HOST_NAME_L'] ?><span class="required"> * </span></label>
					<div class="col-md-8">
						<input type="text" class="form-control" maxlength="128" id="agentname">
						<span class="help-block">
						<?php echo $LANG['UI_AGENT_HOST_NAME_L_TIPS'] ?> </span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_AGENT_TO_USER'] ?><span class="required"> * </span></label>
					<div class="col-md-8">
						<select class="form-control select2me" id="user">
						</select>
						<span class="help-block">
						<?php echo $LANG['UI_AGENT_TO_USER_TIPS'] ?> </span>
					</div>
				</div>
				<div class="form-group authcheckDiv">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_AGENT_ADD_AUTH'] ?></label>
					<div class="col-md-8">
						<div >
							<label class="checkbox-inline pl0"><input type="checkbox" class="icheck" id="file"> 
							<a><i class="fa fa-file"></i></a> <?php echo $LANG['UI_SETTINGS_AUTH_FILE'] ?> </label>
						</div>
						<div class="display-hide">
							<label class="checkbox-inline pl0"><input type="checkbox" class="icheck" id="mysql"> 
							<a><i class="fa fa-database"></i></a> <?php echo $LANG['UI_SETTINGS_AUTH_MYSQL'] ?> </label>
						</div>
						<span class="help-block">
						<?php echo $LANG['UI_AGENT_ADD_AUTH_TIPS'] ?> </span>
					</div>
				</div>
				<div class="alert alert-danger display-hide" id="modaltips">
					<button type="button" class="close" data-close="alert"></button>
					<?php echo $LANG['UI_AGENT_ADD_INPUT_TIPS'] ?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
		<!-- BEGIN SEARCH MODAL -->
		<div id="searchmodal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="vcenteruuid" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-badge"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					
					
					<div class="list-option">
						<div class="row">
						
							<!-- 主机名 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_AGENT_HOST_NAME'] ?> ：
							</label>
							<div class="col-md-4">
								<input style="width:235px; height:34px;" id="hostName" type="text" maxlength="128" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>
							
							<!-- IP地址 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?> ：
							</label>
							<div class="col-md-4">
								<input style="width:235px; height:34px;" id="agentIp" type="text" maxlength="128" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>
							
						</div>
					</div>
					
					<div class="list-option">
						<div class="row">
							<!-- 代理状态 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_AGENT_STATUS'] ?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="onlineFlag">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
									<option value="1"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></option>
									<option value="2"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></option>
								</select>
							</div>
							
							<!-- 注册状态 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_AGENT_REGIST_STATUS'] ?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="registerFlag">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
									<option value="1"><?php echo $LANG['UI_AGENT_REGIST'] ?></option>
									<option value="2"><?php echo $LANG['UI_AGENT_NOT_REGIST'] ?></option>
								</select>
							</div>
							
						</div>
					</div>
					
					<div class="list-option">
						<div class="row">
							<!-- 用户名 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_LOGIN_USERNAME'] ?> ：
							</label>
							<div class="col-md-4">
								<input style="width:235px; height:34px;" id="userName" type="text" maxlength="128" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>
							
						</div>
					</div>
					
					
					
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button"class="btn btn-primary" id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END SEARCH MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/agent/agent_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	