<?php include_once '../../../tpl/permission.php';?>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_NODE_DEPLOY']?><small><?php echo $LANG['UI_NODE_DEPLOY_TIPS']?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	   <input id="uuids" value="<?php  echo $_GET['uuids'];?>" class="display-none"></input>
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="addcontent">
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
					<li class="active ">
						<a href="#deploy_soft" data-toggle="tab">
						<i class="icon-action-redo"></i>  <?php echo $LANG['UI_NODE_DEPLOY']?></a>
					</li>
					<li>
						<a href="#soft_manager" data-toggle="tab">
						<i class="fa fa-briefcase"></i> <?php echo $LANG['UI_NODE_DEPLOY_SOFT_MANAGER']?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content row margin10">
				    <!-- BEGIN deploy_soft CONTENT-->
					<div class="tab-pane active " id="deploy_soft">
                        <form action="#" id="deploy_soft_form" class="form-horizontal mh520">
                        	<div class="form-body">
                        		<div class="form-group pt50">
                        			<label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_NODE_DEPLOY_NODES']?>
                        			</label>
                        			<div class="col-md-6" id="nodenamediv">
                        			</div>
                        		</div>
                        		<div class="form-group ">
                        			<label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_NODE_DEPLOY_SELECT_SOFT']?>
                        			</label>
                        			<div class="col-md-6">
                        				<select class="form-control select2me" name="softselect">
                        				</select>
                        				<div><span class="help-block ">
                        					<?php echo $LANG['UI_NODE_DEPLOY_SELECT_SOFT_TIPS']?>
                        				</span></div>
                        			</div>
                        		</div>
                        	</div>
                        	<div class="form-actions pt50">
                        		<div class="row">
                        			<div class="col-md-offset-3 col-md-4">
                        				<button type="button" id="deploycancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                        				<button type="button" id="deploysubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                        			</div>
                        		</div>
                        	</div>
                        </form>						
					</div>
					<!-- END deploy_soft CONTENT-->
					
					<!-- BEGIN soft_manager CONTENT-->
					<div class="tab-pane" id="soft_manager">
        				<div class="portlet-body mh520">
            			    <div class="table-toolbar">
            					<div class="row">
            						<div class="col-md-12">
            							<div class="btn-group">
            								<span class="btn green-haze fileinput-button">
            								<i class="fa fa-upload"></i>
            								<span>
            								 <?php echo $LANG['UI_NODE_UPLOAD']?></span>
            								<input type="file" id="uploadsoft" name="files" multiple="">
            								</span>
            							</div>
            							<div class="btn-group">
            								<button type="button" id="downloadsoft" class="btn green-haze">
            								<i class="viconfont vicon-ge_download"></i> <?php echo $LANG['UI_NODE_DOWNLOAD']?>
            								</button>
            							</div>
            							<div class="btn-group">
            								<button type="button" id="deletesoft" class="btn green-haze">
            								<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE']?>
            								</button>
            							</div>
            						</div>
            					</div>
            				</div>
            				
            				<div class="table-container">
            					<table class="table table-striped table-bordered table-hover" id="softtable">
            					<thead>
            					<tr role="row" class="heading">
            						<th width="2%">
            							<input type="checkbox" class="group-checkable">
            						</th>
            						<th width="8%">
            							 <?php echo $LANG['UI_PUBLIC_NUMBER']?>
            						</th>
            						<th width="50%">
            						     <?php echo $LANG['UI_NODE_SOFT_PAKAGES']?>
            						</th>
            						<th width="20%">
            							 <?php echo $LANG['UI_BACKUP_FILE_FILESIZE']?>
            						</th>
            						<th width="20%">
            							 <?php echo $LANG['UI_NODE_UPLOAD_TIME']?>
            						</th>
            					</tr>
            					</thead>
            					<tbody>
            					</tbody>
            					</table>
            				</div>
            			</div>
					</div>
					<!-- END soft_manager CONTENT-->
				</div>
			</div>
		</div>
		<!-- END VALIDATION STATES-->
	</div>
</div>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script src="./scripts/platform/node/deploy_soft.js" type="text/javascript"></script>
	