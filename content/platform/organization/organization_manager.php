<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<input id="tenantUUID" value="<?php echo $_SESSION['tenantuuid'] ?>" class="display-none"></input>
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-organization_manager"></i><?php echo $LANG['UI_ORGAN_MANAGE_TITLE'] ?>
				</div>
			</div>
			<div class="portlet-body mlr10">
				 <div class="row">
					 <div class="col-md-3">
						 <div class="portlet ">
							<div class="portlet-body">
								<div class="btn-group mb10">
									<button type="button" class="btn btn-success btn-sm dropdown-toggle bt_organization" type="button" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> 
										 <?php echo $LANG['UI_ORGAN_MANAGE'] ?> <i class="fa fa-angle-down"></i>
									</button>
									<ul class="dropdown-menu min-width130" role="menu" id="organMenu">
										<li id="addOrgan"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_ADD_ORGANIZATION'] ?></a></li>
										<li id="editOrgan"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_EDIT_ORGANIZATION'] ?></a></li>
										<li id="deleteOrgan"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_DELETE_ORGANIZATION'] ?></a></li>
										<li class="divider"></li>
										<li id="addDepartment"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_ADD_DEPARTMENT'] ?></a></li>
										<li id="editDepartment"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_EDIT_DEPARTMENT'] ?></a></li>
										<li id="deleteDepartment"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_DELETE_DEPARTMENT'] ?></a></li>
										<li id="moveDepartTo" class="display-none"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_MOVE_DEPARTMENT_TO'] ?></a></li>
										<li id="addUserTo"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_ADD_USER_TO_DEPARTMENT'] ?></a></li>
									</ul>
								</div>
								<p id="organQuota" ></p>
								<div class="organ_trees" style="margin-top: 10px">
									  <ul id="organ_tree" class="ztree bd1de5  tree_div_vcde"></ul>
								</div>
							</div>
							
						 </div>
					 </div>
					 <div class="col-md-9">
						 <div class="portlet ">
							<div class="portlet-body" id="userTable">
								<div class="table-toolbar mb10">
									<div class="row">
										<div class="col-md-4">
											<div class="btn-group">
												 <button type="button" class="btn btn-success btn-sm dropdown-toggle bt_alo_user" type="button" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> 
													<?php echo $LANG['UI_ORGAN_USER_MANAGE'] ?> <i class="fa fa-angle-down"></i>
												</button>
												<ul class="dropdown-menu min-width130" role="menu">
													<li id="moveUserTo"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_MOVE_USER_TO'] ?></a></li>
													<li id="lockUser"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_USER_DISABLE'] ?></a></li>
													<li id="unlockUser"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_USER_ENABLE'] ?></a></li>
													<li id="deleteUser"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_DELETE_USER'] ?></a></li>
													<li id="resetPassword"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_USER_RESET_PASSWORD'] ?></a></li>
													<li id="setQuota"><a href="javascript:;" ><?php echo $LANG['UI_ORGAN_USER_SET_QUOTA'] ?></a></li>
												</ul>
											</div>
										</div>
										<div class="col-md-8 page-right">
											<input id="search" style="margin-top:1px;" type="search" maxlength="128" class="searchinput table-group-action-input form-control input-inline input input-sm" placeholder="<?php echo $LANG['UI_SEARCH_USERNAME'] ?>" aria-controls="example">
											<input id="searchbtn" type="button" class="btn btn-sm default" value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>">
										</div>
									</div>
								</div>
								<div class="table-container">
									<table class="table table-striped table-bordered lh2 table-hover" id="userDatatable">
									<thead>
									<tr role="row" class="heading">
										<th width="4%">
											<input type="checkbox" class="group-checkable">
										</th>
										<th width="35%">
											<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
										</th>
										<th width="25%">
											<?php echo $LANG['UI_ORGAN_USER_QUOTA_SPACE'] ?>
										</th>
										<th width="21%">
											<?php echo $LANG['UI_USER_TYPE'] ?>
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
								<div class="alert alert-block alert-info fade in" id="tabletips">
									<button type="button" class="close" data-dismiss="alert"></button>
									<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
									<ol class = "alert-ol">
										<li>
											<?php echo $LANG['UI_ORGAN_MANAGE_TIPS1'] ?>
										</li>
										<li>
											<?php echo $LANG['UI_ORGAN_MANAGE_TIPS2'] ?>
										</li>
										<li>
											<?php echo $LANG['UI_ORGAN_MANAGE_TIPS3'] ?><a href="./content/platform/users/add_user.php" class="alert-link ajaxify" name="safety"><?php echo $LANG['WEB_USERS_ADD_USER'] ?></a>
										</li>
									</ol>
								</div>
							</div>
						 </div>
					 </div>
				 </div>
			</div>
		</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title addOrganDiv display-none" ><i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_ORGAN_ADD_ORGANIZATION'] ?></h4>
				<h4 class="modal-title editOrganDiv display-none" ><i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_ORGAN_EDIT_ORGANIZATION'] ?></h4>
				<h4 class="modal-title addDepartDiv display-none" ><i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_ORGAN_ADD_DEPARTMENT'] ?></h4>
				<h4 class="modal-title editDepartDiv display-none" ><i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_ORGAN_EDIT_DEPARTMENT'] ?></h4>
				<h4 class="modal-title moveDepartToDiv display-none" ><i class="fa fa-paper-plane"></i> <?php echo $LANG['UI_ORGAN_MOVE_DEPARTMENT'] ?></h4>
				<h4 class="modal-title addUserToDiv display-none" ><i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_ORGAN_ADD_USER_TO_DEPARTMENT'] ?></h4>
				<h4 class="modal-title moveUserToDiv display-none" ><i class="fa fa-paper-plane"></i> <?php echo $LANG['UI_ORGAN_MOVE_USER'] ?></h4>
				<h4 class="modal-title resetPasswordDiv display-none" ><i class="fa fa-pencil"></i> <?php echo $LANG['UI_ORGAN_USER_RESET_PASSWORD'] ?></h4>
				<h4 class="modal-title setQuotaDiv display-none" ><i class="fa fa-pencil"></i> <?php echo $LANG['UI_ORGAN_USER_SET_QUOTA'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="organDiv display-none">
					<form action="#" id="organForm" class="form-horizontal">
						<div class="form-group">
							<label class="col-md-3 control-label "><?php echo $LANG['UI_ORGAN_ORGANIZATION_NAME'] ?></label>
							<div class="col-md-8">
								<div class="input-icon right">
									<i class="fa"></i>
									<input  id="organName" name="organname" type="text" maxlength="64" class="form-control input-sm">
								</div>
							</div>
						</div>
				   </form>
				</div>
				<div class="departDiv display-none">
					<form action="#" id="departForm" class="form-horizontal">
						<div class="form-group">
							<label class="col-md-3 control-label "><?php echo $LANG['UI_ORGAN_DEPARTMENT_NAME'] ?></label>
							<div class="col-md-8">
								<div class="input-icon right">
									<i class="fa"></i>
									<input  id="departName" name="departname" type="text" maxlength="64" class="form-control input-sm">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label "><?php echo $LANG['UI_ORGAN_SUPERIOR_DEPARTMENT'] ?></label>
							<div class="col-md-8">
								<div class="input-icon right">
									<i class="fa"></i>
									<input id="parentDepart" type="text" maxlength="64" class="form-control input-sm" disabled="disabled">
								</div>
							</div>
						</div>
				   </form>
				</div>
				
			   <div class="setQuotaDiv display-none">
					   <p ><?php echo $LANG['UI_ORGAN_SET_USER1'] ?><span id="quotaUser" style="font-weight:bold;"></span><?php echo $LANG['UI_ORGAN_SET_USER2'] ?>：</p>
			   
			   </div>				
				
				<div class="moveTreeDiv display-none">
					<p class="moveDepartToDiv"><?php echo $LANG['UI_ORGAN_MOVE_DEPARTMENT'] ?><span id="oldDepart" style="font-weight:bold;"></span><?php echo $LANG['UI_ORGAN_MOVE_DEPARTMENT_TIPS1'] ?>：</p>
					<p class="moveUserToDiv"><?php echo $LANG['UI_ORGAN_MOVE_DEPARTMENT_TIPS2'] ?>：</p>
					<div class="organ_trees">
						  <ul id="move_organ_tree" class="ztree bd1de5" style="height: 280px;"></ul>
					</div>
				</div>
				<div class="addUserToDiv display-none">
					 <p><?php echo $LANG['UI_ORGAN_ADD_USER_TO_DEPARTMENT_TIPS'] ?><span id="addUserDepart" style="font-weight:bold;"></span></p>
					 <div class="form-group row ">
						<div class="col-md-3 control-label">
							<?php echo $LANG['UI_PLATFORM_SAFETY_USER'] ?>
						</div>
						<div class="col-md-8">
							<select class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true" data-size="3" id="selectUser">
							
							</select>
						</div>
					</div>                	
				</div>
				
				<div class="resetPasswordDiv display-none">
					<form action="#" id="repasswordForm" class="form-horizontal">
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_PASSWORD'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="password" autocomplete="off" maxlength="32" class="form-control" id="upassword" name="upassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_USER_CONFIRM'] ?> <span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="password" autocomplete="off" maxlength="32"  class="form-control" name="rpassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="form-group quotaDiv display-none">
					<label class="col-md-3 control-label "><?php echo $LANG['UI_ORGAN_USER_QUOTA_SPACE'] ?></label>
					<div class="col-md-8">
						<div id="custom" style="display:inline-flex;">
							<div class="input-icon" >
								<div id="spinnerNum" >
									<div class="input-group" >
										<input type="text" id="quotaInput" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="6">
										<div class="spinner-buttons input-group-btn">
											<button type="button" class="btn spinner-up default input-sm">
												<i class="fa fa-angle-up"></i>
											</button>
											<button type="button" class="btn spinner-down default input-sm">
												<i class="fa fa-angle-down"></i>
											</button>
										</div>
									</div>
								</div>
							</div>
							<div class="">
								<p class="ml15 mt8">GB</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
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
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/organization/organization_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	