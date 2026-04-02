<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE'] ?> <small><?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_TIPS'] ?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<input id="vcuuid" value="<?php echo $_GET['vcuuid']; ?>" class="display-none"></input>
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki" id="fatherdiv">
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-yingjiyuan"></i><?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE'] ?>
				</div>
			</div>
			<div class="portlet-body">
				 <div class="row">
					 <div class="col-md-3">
						 <div class="portlet ">
							<div class="portlet-title">
								<div class="caption">
									<i class="fa fa-gift"></i><?php echo $LANG['UI_EMERGENCY_PLAN_GROUPING'] ?>
								</div>
							</div>
							<div class="portlet-body">
								<div>
								   <div class="btn-group">
										<button class="btn btn-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
										<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?> <i class="fa fa-angle-down"></i>
										</button>
										<ul class="dropdown-menu" role="menu">
											<li class="dropdown-submenu">
												<a href="javascript:;">
												<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD'] ?> </a>
												<ul class="dropdown-menu" style="">
													<li>
														<a href="javascript:;" id="addplan">
														<i class="iconfont icon-gongsi"></i> <?php echo $LANG['UI_EMERGENCY_ALL_PLAN'] ?> </a>
													</li>
													<li>
														<a href="javascript:;" id="addgroup">
														<i class="iconfont icon-bumen1"></i> <?php echo $LANG['UI_EMERGENCY_GROUP_PLAN'] ?> </a>
													</li>
													<li>
														<a href="javascript:;" id="addchild">
														<i class="iconfont icon-bumen"></i> <?php echo $LANG['UI_EMERGENCY_CHILD_PLAN'] ?> </a>
													</li>
												</ul>
											</li>
											<li>
												<a href="javascript:;" id="editorch">
												<i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_PUBLIC_MODIFY'] ?> </a>
											</li>
											<li>
												<a href="javascript:;" id="deleteorch">
												<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?> </a>
											</li>
										</ul>
									</div>
								</div>
								<div class="bd1de5 plan-tree">
								   <ul id="plan_tree" class="ztree "></ul>
								</div>
							</div>
						 </div>
					 </div>
					 <div class="col-md-9">
						 <div class="portlet ">
							<div class="portlet-title">
								<div class="caption">
									<i class="fa fa-gift"></i><?php echo $LANG['UI_VCENTER_MACHINE'] ?>
								</div>
							</div>
							
							<div class="portlet-body " id="planvmtable">
								<div class="table-toolbar-wrapper">
									<div class="table-toolbar-wrapper__left">
										<div class="btn-group ">
											<button type="button" id="addvm" class="btn table-toolbar-btn">
											<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD'] ?>
											</button>
										</div>
										
										<div class="btn-group">
											<button type="button" id="deletevm" class="btn table-toolbar-btn">
											<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
											</button>
										</div>
									</div>
								</div>
								<div class="table-container ">
									<table class="table table-striped table-hover" id="vmdatatable">
									<thead>
									<tr role="row" class="heading">
										<th width="2%">
											<input type="checkbox" class="group-checkable">
										</th>
										<th width="30%">
											<?php echo $LANG['UI_VCENTER_MACHINE'] ?>
										</th>
										<th width="30%">
											<?php echo $LANG['UI_EMERGENCY_PLAN_CONFIGURE'] ?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_EMERGENCY_PLAN_RECOVER_TYPE'] ?>
										</th>
										<th width="30%">
											<?php echo $LANG['UI_EMERGENCY_PLAN_OTHERS'] ?>
										</th>
									</tr>
									</thead>
									<tbody>
									</tbody>
									</table>
								</div>
							</div>
							<div class="alert alert-block alert-info fade in" >
								<button type="button" class="close" data-dismiss="alert"></button>
								<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
								<ol class = "alert-ol">
									<li>
										<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_VM_TIPS1'] ?>
									</li>
									<li>
										<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_VM_TIPS2'] ?>
									</li>
									<li>
										<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_VM_TIPS3'] ?>
									</li>
								</ol>
							</div>
						 </div>
					 </div>
				 </div>
			</div>
		</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldivplan" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="orchtype" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_EMERGENCY_ADD_PLAN'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-group display-none" id="plansdiv">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_EMERGENCY_ALL_PLAN'] ?><span class="required"> * </span></label>
					<div class="col-md-8">
						<select class="form-control select2me" id="plans">
						</select>
						<span class="help-block">
						<?php echo $LANG['UI_EMERGENCY_ALL_PLAN_TIPS'] ?> </span>
					</div>
				</div>
				<div class="form-group display-none" id="groupsdiv">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_EMERGENCY_GROUP_PLAN'] ?><span class="required"> * </span></label>
					<div class="col-md-8">
						<select class="form-control select2me" id="groups">
						</select>
						<span class="help-block">
						<?php echo $LANG['UI_EMERGENCY_GROUP_PLAN_TIPS'] ?> </span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_NAME'] ?><span class="required"> * </span></label>
					<div class="col-md-8">
						<input type="text" maxlength="128" class="form-control" placeholder="<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_NAME'] ?>" id="diyname"/>
						<span class="help-block">
						<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_NAME_TIPS'] ?> </span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_EXPLAIN'] ?> </label>
					<div class="col-md-8">
						<textarea class="form-control" rows="3" placeholder="<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_EXPLAIN'] ?>" id="diyremark"></textarea>
						<span class="help-block">
						<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_EXPLAIN_TIPS'] ?> </span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="submitorch"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldivplanedit" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="orchuuid" class="display-none"></input>
			<input id="editorchtype" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_EMERGENCY_EDIT_PLAN'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_NAME'] ?><span class="required"> * </span></label>
					<div class="col-md-8">
						<input type="text" maxlength="128" class="form-control" placeholder="<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_NAME'] ?>" id="editname"/>
						<span class="help-block">
						<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_NAME_TIPS'] ?> </span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_EXPLAIN'] ?> </label>
					<div class="col-md-8">
						<textarea class="form-control" rows="3" placeholder="<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_EXPLAIN'] ?>" id="editremark"></textarea>
						<span class="help-block">
						<?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_EXPLAIN_TIPS'] ?> </span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="submitedit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldivplandelete" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="deleteorchuuid" class="display-none"></input>
			<input id="deleteorchtype" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_EMERGENCY_DELETE_PLAN'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label col-md-5"> <?php echo $LANG['UI_EMERGENCY_PLAN_MANAGE_NAME'] ?>:
					</label>
					<div class="col-md-6 margintop10" id="deleteplanname">
					</div>
				</div>
				<div class="form-group" id="dgroupdiv">
					<label class="control-label col-md-5"> <?php echo $LANG['UI_EMERGENCY_INCLOUD_GROUP_PLAN'] ?>
					</label>
					<div class="col-md-6 margintop10" id="groupnum">
					</div>
				</div>
				<div class="form-group" id="dchilddiv">
					<label class="control-label col-md-5"> <?php echo $LANG['UI_EMERGENCY_INCLOUD_CHILD_PLAN'] ?>
					</label>
					<div class="col-md-6 margintop10" id="childnum">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-5"> <?php echo $LANG['UI_EMERGENCY_INCLOUD_VM'] ?>
					</label>
					<div class="col-md-6 margintop10" id="vmnum">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="submitdelete"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
		<!-- BEGIN MODAL -->
		<div id="modaladdvm" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="childuuid" class="display-none"></input>
			<input id="groupuuid" class="display-none"></input>
			<input id="planuuid" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_EMERGENCY_ADD_VM_TO_CHILD_PLAN'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					<div class="row">
						<div class="col-md-3 col-sm-3 col-xs-3">
							<ul class="nav nav-tabs tabs-left" id="steps" >
								<li class="active " >
									<a href="javascript:;" class="stepvm" data-toggle="tab">
									<?php echo $LANG['UI_EMERGENCY_ADD_VM_TO_CHILD_PLAN_TIPS1'] ?> </a>
								</li>
								<li>
									<a href="javascript:;" class="stepvm" data-toggle="tab">
									<?php echo $LANG['UI_EMERGENCY_ADD_VM_TO_CHILD_PLAN_TIPS2'] ?> </a>
								</li>
								<li>
									<a href="javascript:;" class="stepvm" data-toggle="tab">
									<?php echo $LANG['UI_EMERGENCY_ADD_VM_TO_CHILD_PLAN_TIPS3'] ?> </a>
								</li>
								<li>
									<a href="javascript:;" class="stepvm" data-toggle="tab">
									<?php echo $LANG['UI_EMERGENCY_ADD_VM_TO_CHILD_PLAN_TIPS4'] ?> </a>
								</li>
							</ul>
						</div>
						<div class="col-md-9 col-sm-9 col-xs-9">
							<div class="tab-content " id="stepscontent">
								<div class="tab-pane contentpane active">
									<div class="form-group col-md-12">
										<div class="mt10 mb15 min-height250">
											<div class="two_tree">
											  <ul id="vmtree" class="ztree bd1de5 tree_div"></ul>
											</div>
										</div>
										<div class="alert alert-block alert-info fade in" >
											<button type="button" class="close" data-dismiss="alert"></button>
											<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
											<ol class = "alert-ol">
												<li>
													<?php echo $LANG['UI_EMERGENCY_SELECT_ADD_VM_TO_CHILD_PLAN'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_EMERGENCY_SELECT_ADD_VM_TO_CHILD_PLAN_TIPS'] ?>
												</li>
											</ol>
										</div>
									</div>
								</div>
								<div class="tab-pane contentpane fade ">
									<div class="form-group  col-md-12 vmconfigdiv">
										<div class="panel-group accordion" id="vmconfig">
										
										</div>
										<div class="alert alert-block alert-info fade in" >
											<button type="button" class="close" data-dismiss="alert"></button>
											<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
											<ol class = "alert-ol">
												<li>
													<?php echo $LANG['UI_EMERGENCY_CONFIGURE_ADD_VM_TO_CHILD_PLAN'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_EMERGENCY_CONFIGURE_ADD_VM_TO_CHILD_PLAN_TIPS1'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_EMERGENCY_CONFIGURE_ADD_VM_TO_CHILD_PLAN_TIPS2'] ?>
												</li>
											</ol>
										</div>
									</div>
								</div>
								<div class="tab-pane contentpane fade">
									<div class="form-group col-md-11 pt15">
										<label class="col-md-3 control-label"><?php echo $LANG['UI_EMERGENCY_PLAN_USE_BACKUP_POINT'] ?><span class="required"> * </span></label>
										<div class="col-md-8">
											<select class="form-control select2me" id="timepointtype">
												<option value="0"><?php echo $LANG['UI_EMERGENCY_PLAN_NOT_SET_BACKUP_POINT'] ?></option>
<!--             									<option value="1">最近时间的任意备份点</option> -->
<!--             									<option value="2">最近时间的完全备份点</option> -->
<!--             									<option value="3">最近时间的增量备份点</option> -->
<!--             									<option value="4">最近时间的差异备份点</option> -->
											</select>
											<span class="help-block">
											<?php echo $LANG['UI_EMERGENCY_PLAN_SELECT_RECOVER_BACKUP_POINT'] ?> </span>
										</div>
									</div>
									<div class="form-group col-md-12 pt15">
										<div class="alert alert-block alert-info fade in">
											<button type="button" class="close" data-dismiss="alert"></button>
											<ul class="alert-ul">
												<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
												<li>
													<?php echo $LANG['UI_EMERGENCY_PLAN_CONFIGURE_BACKUP_POINT_TIPS'] ?>
												</li>
											</ul>
										</div>
									</div>
									
								</div>
								<div class="tab-pane contentpane fade">
									<div class="form-group col-md-11 pt15">
										<label class="col-md-3 control-label"><?php echo $LANG['UI_EMERGENCY_PLAN_RECOVER_DESTINATION'] ?><span class="required"> * </span></label>
										<div class="col-md-9">
											<select class="form-control select2me" id="hostsetting">
											</select>
											<span class="help-block">
											<?php echo $LANG['UI_EMERGENCY_PLAN_RECOVER_DESTINATION_TIPS'] ?> </span>
										</div>
									</div>
									<div class="form-group col-md-12 pt15">
										<div class="alert alert-block alert-info fade in" >
											<button type="button" class="close" data-dismiss="alert"></button>
											<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
											<ol class = "alert-ol">
												<li>
													<?php echo $LANG['UI_EMERGENCY_PLAN_CONFIGURE_RECOVER_DESTINATION'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_EMERGENCY_PLAN_CONFIGURE_RECOVER_DESTINATION_TIPS1'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_EMERGENCY_PLAN_CONFIGURE_RECOVER_DESTINATION_TIPS2'] ?>
												</li>
											</ol>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-default display-none" id="prevstep"><?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?></button>
				<button type="button" class="btn btn-default" id="nextstep"><?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?></button>
				<button type="button" class="btn btn-primary display-none" id="submitaddvm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfigOrch.js"></script>
<script type="text/javascript" src="./scripts/platform/manoeuvre/plan.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	