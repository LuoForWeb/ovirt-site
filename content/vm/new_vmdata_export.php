<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_BACKUP_VM_DATA_EXPORT'] ?> <small><?php echo $LANG['UI_BACKUP_VM_DATA_EXPORT_DESCRIPTION'] ?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue-hoki" id="exportcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i><?php echo $LANG['UI_BACKUP_NEW_TASK']?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" id="instantrecoverform" class="form-horizontal">
				    <div class="form-body min-height480">
						
						<div class="form-group pt50">
							<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_BACKUP_POINT']?> <span class="required">
							* </span>
							</label>
							
							<div class="col-md-7">
								<div class="vm_tree_div ">
							        <select class="bs-select width300" data-show-subtext="true" id="nodeselect" >
                                    </select>
    								
							        <select class="bs-select" data-show-subtext="true" id="pointshowtype">
                                        <option data-icon="timevmtype icon-default" value="1" > <?php echo $LANG['UI_RECOVERY_BACKUP_POINT_VM_GROUP']?></option>
                                        <option data-icon="timepointtype icon-default" value="2"> <?php echo $LANG['UI_RECOVERY_BACKUP_POINT_TIME_GROUP']?></option>
                                    </select>
							    </div>
							</div>
							
							<div class="col-md-6 col-md-offset-3 mt10 min-height250">
								<div class="two_tree">
								    <ul id="pointtypetree" class="ztree bd1de5 tree_div"></ul>
    								<ul id="vmtypetree" class="ztree bd1de5  tree_div display-hide"></ul>
    								<div><span class="help-block ">
    									<?php echo $LANG['UI_BACKUP_VM_DATA_EXPORT_SELECT_TIMEPOINT']?>
    								</span></div>
								</div>
								<div class="alert alert-block alert-info fade in display-hide"  id="nopointtips">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <ul class="alert-ul">
                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                        <li>
                                            <strong><?php echo $LANG['UI_RECOVERY_NO_VM_TITLE']?></strong><a id="tobackup"><small><?php echo $LANG['UI_RECOVERY_NO_VM_TIPS']?></small></a>
                                        </li>
                                    </ul>
    							</div>
							</div>
						</div>
						
						<div class="form-group dndiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_VM_DATA_EXPORT_PURPOSE_STORAGE'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
                                <select class="form-control select2me" id="storageselect">
                                </select>
							</div>
						</div>
						
						<div class="form-group dndiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="taskname"/>
									<div><span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']?></span></div>
								</div>
							</div>
						</div>
						
					</div>
					<div class="form-actions pt50">
						<div class="row">
							<div class="col-md-offset-6 col-md-6">
								<button type="button" id="cancelbtn" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
								<button type="button" id="submitbtn" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/vm/new_vmdata_export.js" type="text/javascript"></script>