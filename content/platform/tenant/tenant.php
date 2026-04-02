<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/hover/hover-min.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php
//if(empty($_SESSION['tenantuuid'])){
echo '<a a class="ajaxify" name="tenant_manager" href="./content/platform/tenant/tenant_manager.php"><span style="color: #20c99a;">' . $LANG['UI_PLATFORM_TENANT'] . '</span>/</a>';
// }
?>
<span id="companyName"></span>
<div class="pull-right">
	<small><label id="userNum"></label> <?php echo $LANG['UI_PLATFORM_SAFETY_USER'] ?></small> |
	 <small><label id="userGroupNum"></label> <?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?></small>
</div>
</h3>

<input id="tenantuuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	
	<!-- BEGIN TOP CONTENT-->
	<div class="col-md-12">
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-resource_group"></i><?php echo $LANG['UI_TENANT_RESOURCE'] ?>
				</div>
			</div>
			<div class="portlet-body resourceDiv">
				<div class="row">
					<div class="col-md-3">
						<div class="tenant-resource">
							<div class="col-md-9">
							<i class="iconfont icon-xuniji "></i>
							<span><?php echo $LANG['UI_PLATFORM_VM'] ?></span>
							</div>
							<div class="col-md-3">
								<h3 id="vmNum"></h3>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="tenant-resource">
							<div class="col-md-9">
								<i class="iconfont icon-fsdata "></i>
								<span><?php echo $LANG['UI_PLATFORM_FILE_AGENT'] ?></span>
							</div>
							<div class="col-md-3">
								<h3 id="fileNum"></h3>
							</div>
						</div>
					</div>
					
					<div class="col-md-3">
						<div class="tenant-resource">
							<div class="col-md-9">
								<i class="iconfont icon-shujukudingshi "></i>
								<span><?php echo $LANG['UI_PLATFORM_DB_HOST'] ?></span>
							</div>
							<div class="col-md-3">
								<h3 id="dbNum"></h3>
							</div>
						</div>
					</div>
					
					<div class="col-md-3">
						<div class="tenant-resource">
							<div class="col-md-9">
								<i class="iconfont icon-dbcdp "></i>
								<span><?php echo $LANG['UI_PLATFORM_CDP_AGENT'] ?></span>
							</div>
							<div class="col-md-3">
								<h3 id="cdpNum"></h3>
							</div>
						</div>
					</div>
					
					
				</div>
				<div class="row mt10">
					
					<div class="col-md-3">
						<div class="tenant-resource">
							<div class="col-md-9">
								<i class="iconfont icon-appliance"></i>
								<span><?php echo $LANG['UI_PLATFORM_VM_APPLIANCE'] ?></span>
							</div>
							<div class="col-md-3">
								<h3 id="applianceNum"></h3>
							</div>
						</div>
					</div>
					
					<div class="col-md-3">
						<div class="tenant-resource">
							<div class="col-md-9">
								<i class="iconfont icon-beifenjiedian"></i>
								<span><?php echo $LANG['UI_PALTFORM_NODE'] ?></span>
							</div>
							<div class="col-md-3">
								<h3 id="nodeNum"></h3>
							</div>
						</div>
					</div>
					
					<div class="col-md-3">
						<div class="tenant-resource">
							<div class="col-md-9">
								<i class="iconfont icon-beifencunchu"></i>
								<span><?php echo $LANG['UI_PLATFORM_STORAGE_RESOURCE'] ?></span>
							</div>
							<div class="col-md-3">
								<h3 id="storageNum"></h3>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-6">
				<div class="portlet box blue-hoki" style="height: 420px;overflow:hidden;">
					<div class="portlet-title">
						<div class="caption">
							<i class="viconfont vicon-jibenxinxi-08"></i><?php echo $LANG['UI_USER_BASE_INFO'] ?>
						</div>
					</div>
					<div class="portlet-body">
						<div class="row static-info mt15">
							<div class="col-md-4 textalignr ">
								<?php echo $LANG['UI_TENANT_NAME'] ?>:
							</div>
							<div class="col-md-8 value" id="tenantName"></div>
						</div>
						<div class="row static-info mt15">
							<div class="col-md-4 textalignr ">
								<?php echo $LANG['UI_USER_MANAGERS'] ?>:
							</div>
							<div class="col-md-8 value" id="adminName"></div>
						</div>
						<div class="row static-info mt15">
							<div class="col-md-4 textalignr ">
								<?php echo $LANG['UI_TENANT_ADMIN_EMAIL'] ?>:
							</div>
							<div class="col-md-8 value" id="adminEmail"></div>
						</div>
						<div class="row static-info mt15">
							<div class="col-md-4 textalignr ">
								 <?php echo $LANG['UI_PUBLIC_CREATE_TIME'] ?>:
							</div>
							<div class="col-md-8 value" id="createTime"></div>
						</div>
					</div>
				</div>
			</div>
			
			<?php
			if (empty($_SESSION['tenantuuid'])) {
				include_once './tenant_settings_operate.php';
			} else {
				include_once './tenant_settings_info.php';
			}

			?>
			
		</div>
	</div>
	
	
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-6">
				<div class="portlet box blue-hoki">
					<div class="portlet-title">
						<div class="caption">
							<i class="viconfont vicon-pt_setting_user"></i><?php echo $LANG['UI_PLATFORM_SAFETY_USER'] ?>
						</div>
					</div>
					<div class="portlet-body" style="height:320px;overflow:hidden;overflow-y: auto;">
						<div class="table-toolbar">
							<div class="row">
								<div class="col-md-6">
									<!--  <div class="btn-group">
										<button type="button" id="user_add" class="btn btn-sm green-haze">
										<i class="fa fa-plus"></i> <?php echo $LANG['UI_PUBLIC_ADD_OPERATION'] ?>
										</button>
									</div>
									<div class="btn-group">
										<button type="button" id="user_delete" class="btn btn-sm green-haze">
										<i class="fa fa-trash-o"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
										</button>
									</div>-->
								</div>
							</div>
						</div>
						
						<div class="table-container mt10">
							<table class="table table-striped table-bordered table-hover" id="usertable">
							<thead>
							<tr role="row" class="heading">
								<th width="25%">
									<?php echo $LANG['UI_PLATFORM_SAFETY_USER'] ?>
								</th>
								<th width="35%">
									<?php echo $LANG['UI_TENANT_RELATED_USER_GROUP'] ?>
								</th>
								<th width="40%">
									<?php echo $LANG['UI_TENANT_RELATED_ROLE'] ?>
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
			<div class="col-md-6">
				<div class="portlet box blue-hoki">
					<div class="portlet-title">
						<div class="caption">
							<i class="iconfont icon-usergroup"></i><?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?>
						</div>
					</div>
					<div class="portlet-body" style="height:320px;overflow:hidden;overflow-y: auto;">
						<div class="table-toolbar">
							<div class="row">
								<div class="col-md-6">
									<!-- <div class="btn-group">
										<button type="button" id="usergroup_add" class="btn btn-sm green-haze">
										<i class="fa fa-plus"></i> <?php echo $LANG['UI_PUBLIC_ADD_OPERATION'] ?>
										</button>
									</div>
									<div class="btn-group">
										<button type="button" id="usergroup_delete" class="btn btn-sm green-haze">
										<i class="fa fa-trash-o"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
										</button>
									</div> -->
								</div>
							</div>
						</div>
						
						<div class="table-container mt10">
							<table class="table table-striped table-bordered table-hover" id="usergrouptable">
							<thead>
							<tr role="row" class="heading">
								<th width="25%">
									<?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?>
								</th>
								<th width="35%">
									<?php echo $LANG['UI_TENANT_RELATED_USER'] ?>
								</th>
								<th width="40%">
									<?php echo $LANG['UI_TENANT_RELATED_ROLE'] ?>
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
	
	<div class="col-md-12">
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-group23"></i><?php echo $LANG['UI_TENANT_RESOURCE_DETAIL'] ?>
				</div>
			</div>
			<div class="portlet-body">
				<ul class="nav nav-tabs ">
					<li class="active" id="vmtab">
						<a href="#vm_tab" data-toggle="tab" aria-expanded="false">
						<i class="iconfont icon-xuniji font-green-seagreen"></i> <?php echo $LANG['UI_PLATFORM_VM'] ?> </a>
					</li>
					<li id="filehosttab">
						<a href="#filehost_tab" data-toggle="tab" aria-expanded="false">
						<i class="iconfont icon-fsdata "></i> <?php echo $LANG['UI_PLATFORM_FILE_AGENT'] ?></a>
					</li>
					<li id="dbhosttab">
						<a href="#dbhost_tab" data-toggle="tab" aria-expanded="false">
						<i class="iconfont icon-shujukudingshi "></i> <?php echo $LANG['UI_PLATFORM_DB_HOST'] ?></a>
					</li>
					<?php
					if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
						echo '<li class="" id="cdphosttab">
							<a href="#cdphost_tab" data-toggle="tab" aria-expanded="false">
							<i class="iconfont icon-dbcdp "></i>' . $LANG['UI_PLATFORM_CDP_AGENT'] . ' </a>
						</li>';
					}
					?>
  
					<li class="" id="appliancetab">
						<a href="#appliance_tab" data-toggle="tab" aria-expanded="false">
						<i class="iconfont icon-appliance "></i> <?php echo $LANG['UI_PLATFORM_VM_APPLIANCE'] ?> </a>
					</li>
					<li class="" id="nodetab">
						<a href="#node_tab" data-toggle="tab" aria-expanded="false">
						<i class="iconfont icon-beifenjiedian "></i> <?php echo $LANG['UI_PALTFORM_NODE'] ?> </a>
					</li>
					<li class="" id="storagetab">
						<a href="#storage_tab" data-toggle="tab" aria-expanded="false">
						<i class="iconfont icon-beifencunchu "></i> <?php echo $LANG['UI_PLATFORM_STORAGE_RESOURCE'] ?> </a>
					</li>
					
				</ul>
				<div class="tab-content ">
					<div class="tab-pane active" id="vm_tab">
						<div class="portlet-body" id="">
							<div class="table-toolbar">
							</div>
							
							<div class="table-container mt15">
								<table class="table table-striped table-bordered table-hover" id="vmDatatable">
									<thead>
										<tr role="row" class="heading">
											<th width="35%">
												   <?php echo $LANG['UI_REPORT_VM_NAME'] ?>
											</th>
											<th width="25%">
												  <?php echo $LANG['UI_VCENTER_VC'] ?>
											</th>
											<th width="25%">
												<?php echo $LANG['UI_BACKUP_FILE_PATH'] ?>
											</th>
											<th width="15%">
												<?php echo $LANG['UI_PUBLIC_STATUS'] ?>
											</th>
										</tr>
									</thead>
									<tbody>
									
									</tbody>
								</table>
								
							</div>
						</div>
					</div>
					<div class="tab-pane " id="filehost_tab">
						 <div class="portlet-body" id="">
							<div class="table-toolbar">
							</div>
							
							<div class="table-container mt15">
								<table class="table table-striped table-bordered table-hover" id="fileDatatable">
									<thead>
										<tr role="row" class="heading">
											<th width="30%">
												   <?php echo $LANG['UI_AGENT_HOST_NAME'] ?>
											</th>
											<th width="30%">
												  <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_PUBLIC_OS'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_PUBLIC_STATUS'] ?>
											</th>
										</tr>
									</thead>
									<tbody>
									
									</tbody>
								</table>
								
							</div>
						</div>
					</div>
					
					<div class="tab-pane " id="dbhost_tab">
						 <div class="portlet-body" id="">
							<div class="table-toolbar">
							</div>
							
							<div class="table-container mt15">
								<table class="table table-striped table-bordered table-hover" id="dbDatatable">
									<thead>
										<tr role="row" class="heading">
											<th width="30%">
												   <?php echo $LANG['UI_AGENT_HOST_NAME'] ?>
											</th>
											<th width="30%">
												  <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_PUBLIC_OS'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_PUBLIC_STATUS'] ?>
											</th>
										</tr>
									</thead>
									<tbody>
									
									</tbody>
								</table>
								
							</div>
						</div>
					</div>
					
					<div class="tab-pane" id="cdphost_tab">
						<div class="portlet-body" id="">
							<div class="table-toolbar">
							</div>
							
							<div class="table-container mt15">
								<table class="table table-striped table-bordered table-hover" id="cdpDatatable">
									<thead>
										<tr role="row" class="heading">
											<th width="25%">
												   <?php echo $LANG['UI_AGENT_HOST_NAME'] ?>
											</th>
											<th width="25%">
												  <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_PUBLIC_OS'] ?>
											</th>
											<th width="15%">
												<?php echo $LANG['UI_PUBLIC_TYPE'] ?>
											</th>
											<th width="15%">
												<?php echo $LANG['UI_PUBLIC_STATUS'] ?>
											</th>
										</tr>
									</thead>
									<tbody>
									
									</tbody>
								</table>
								
							</div>
						</div>
					</div>
					<div class="tab-pane" id="appliance_tab">
						<div class="portlet-body" id="">
							<div class="table-toolbar">
							</div>
							
							<div class="table-container mt15">
								<table class="table table-striped table-bordered table-hover" id="applianceDatatable">
									<thead>
										<tr role="row" class="heading">
											<th width="30%">
												   <?php echo $LANG['UI_APPLIANCE_NICKNAME'] ?>
											</th>
											<th width="30%">
												  <?php echo $LANG['UI_APPLIANCE_IP'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_APPLIANCE_PORT'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_APPLIANCE_STATUS'] ?>
											</th>
										</tr>
									</thead>
									<tbody>
									
									</tbody>
								</table>
								
							</div>
						</div>
					</div>
					<div class="tab-pane" id="node_tab">
						<div class="portlet-body" id="">
							<div class="table-toolbar">
							</div>
							
							<div class="table-container mt15">
								<table class="table table-striped table-bordered table-hover" id="nodeDatatable">
									<thead>
										<tr role="row" class="heading">
											<th width="30%">
												   <?php echo $LANG['UI_NODE_NAME'] ?>
											</th>
											<th width="30%">
												  <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_NODE_DEPLOY_STATUS'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_NODE_STATUS'] ?>
											</th>
										</tr>
									</thead>
									<tbody>
									
									</tbody>
								</table>
								
							</div>
						</div>
					</div>
					<div class="tab-pane" id="storage_tab">
						<div class="portlet-body" id="">
							<div class="table-toolbar">
							</div>
							
							<div class="table-container mt15">
								<table class="table table-striped table-bordered table-hover" id="storageDatatable">
									<thead>
										<tr role="row" class="heading">
											<th width="25%">
												<?php echo $LANG['UI_STORAGE_NAME'] ?>
											</th>
											<th width="10%">
												<?php echo $LANG['UI_PUBLIC_TYPE'] ?>
											</th>
											<th width="20%">
												<?php echo $LANG['UI_STORAGE_IN_NODE'] ?>
											</th>
											<th width="10%">
												<?php echo $LANG['UI_STORAGE_TOTAL_SIZE'] ?>
											</th>
											<th width="10%">
												<?php echo $LANG['UI_RECOVERY_STORAGE_VALID_SIZE'] ?>
											</th>
											<th width="10%">
												<?php echo $LANG['UI_STORAGE_STATUS'] ?>
											</th>
											<th width="15%">
												<?php echo $LANG['UI_PUBLIC_PURPOSE'] ?>
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
	
	
	<!-- BEGIN ADD MODAL -->
	<div id="addUserModal" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		<div class="modal-header ">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			<h4 class="modal-title"><?php echo $LANG['WEB_USERS_ADD_USER'] ?></h4>
		</div>
		<div class="modal-body">
			<div class="portlet-body">
				<div class="list-option">
					<div class="row">
						<label class="control-label col-md-4"><?php echo $LANG['UI_USER_ADD_SELECT_TIPS'] ?> ：
						</label>
						<div class="col-md-6">
							<select class="bootstrap-mutiple-select select2me selectpicker show-tick" id="userList" multiple data-live-search="true" data-actions-box="true">
							</select>
						</div>
						
					</div>
				</div>
				
			</div>
		</div>
		
		<!-- modal-footer -->
		<div class="modal-footer">
			<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
			<button type="button"class="btn btn-primary" id="adduser_submit"><?php echo $LANG['UI_PUBLIC_ADD_OPERATION'] ?></button>
		</div>
	</div>	
	<!-- END ADD MODAL -->
	
	<!-- BEGIN ADD MODAL -->
	<div id="addUserGroupModal" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		<div class="modal-header ">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			<h4 class="modal-title"><?php echo $LANG['UI_USER_GROUP_ADD'] ?></h4>
		</div>
		<div class="modal-body">
			<div class="portlet-body">
				<div class="list-option">
					<div class="row">
						<label class="control-label col-md-4"><?php echo $LANG['UI_USER_GROUP_ADD_SELECT_TIPS'] ?> ：
						</label>
						<div class="col-md-6">
							<select class="bootstrap-mutiple-select select2me selectpicker show-tick" id="userGroupList" multiple data-live-search="true" data-actions-box="true">
							</select>
						</div>
						
					</div>
				</div>
				
			</div>
		</div>
		
		<!-- modal-footer -->
		<div class="modal-footer">
			<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
			<button type="button"class="btn btn-primary" id="addusergroup_submit"><?php echo $LANG['UI_PUBLIC_ADD_OPERATION'] ?></button>
		</div>
	</div>	
	<!-- END ADD MODAL -->
	
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/echarts.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/radiaindicator/radialIndicator.min.js"></script>
<script src="./assets/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	
<script type="text/javascript" src="./scripts/platform/tenant/tenant.js"></script>