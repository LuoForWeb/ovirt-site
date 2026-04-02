<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats grey-cararra -->
		<div class="portlet-body" id="current_job">
				<!-- search -->
				<div class="table-toolbar">
					<div class="row">
						<div class="col-md-4">
							<div class="btn-group display-none">
                            	<button type="button" class="btn btn-success btn-sm   dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> 
                            		<i class="fa fa-share"></i> <?php echo $LANG['UI_BACKUP_NEW_JOB']?> <i class="fa fa-angle-down"></i>
                            	</button>
                            	<ul class="dropdown-menu min-width130" role="menu" id="" >
                            		<li id="backupvm"><a href="javascript:;" ><i class="iconfont icon-v-xnjbf"></i><?php echo $LANG['UI_PLATFORM_VM']?></a></li>
                            		<li id="backupfile"><a href="javascript:;" ><i class="iconfont icon-v-wjbf"></i><?php echo $LANG['UI_PLATFORM_FILE']?></a></li>
                            		<!-- <li id="backupdatabase"><a href="javascript:;" ><i class="iconfont icon-c-databackup"></i><?php echo $LANG['UI_PLATFORM_DB']?></a></li>-->
                            		<li id="backupcopy" style="<?php $authfun = $_SESSION['authfun']; if(!$authfun['copy']){echo "display: none";}?>"><a href="javascript:;" ><i class="iconfont icon-v-fuben"></i><?php echo $LANG['WEB_PLATFORM_DES_COPY']?></a></li>
                            		<li id="backuparchive" style="<?php $authfun = $_SESSION['authfun']; if(!$authfun['archive']){echo "display: none";}?>"><a href="javascript:;" ><i class="fa fa-archive "></i><?php echo $LANG['UI_PLATFORM_ARCHIVE']?></a></li>
                            	</ul>
                            </div>
						</div>
						<div class="col-md-8">
							 <div class="table-actions-wrapper page-right">
								<span>
								</span>
								<input style="margin-top:1px;" type="search" maxlength="128" class="current_searchinput table-group-action-input form-control input-inline input input-sm" placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME']?>" aria-controls="example">
								<input id="current_searchbtn" type="button" class="btn btn-sm default" value="<?php echo $LANG['UI_PUBLIC_SEARCH']?>">
								<button id="current_searchAll" class="btn btn-sm green-haze">
								<i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
								</button>
							</div>
						</div>
						<div class="col-md-12">
							<div id="current_searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']?> <span class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR']?></span> </div>
						</div>
					</div>
				</div>
				<div class="table-container">
					<table class="table table-striped table-bordered table-hover lh2" id="currentTable">
					<thead>
					<tr role="row" class="heading">
						<th width="20%">
							 <?php echo $LANG['UI_JOB_RNAME']?>
						</th>
						<th width="16%">
							 <?php echo $LANG['UI_PUBLIC_MODULE_TYPE']?>
						</th>
						<th width="8%">
							 <?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>
						</th>
						<th width="14%">
							<?php echo $LANG['UI_JOB_CREATE_TIME']?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_PUBLIC_STATUS']?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_JOB_SPEED']?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_JOB_PROGRESS']?>
						</th>
						<th width="8%">
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
						<p>
        					<?php echo $LANG['UI_JOB_CURRENT_NAME_TIPS']?>
        				</p>
					</div>
					
				</div>
			</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="currentJobModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
				</h4>
			</div>
			<div class="modal-body">
    			<div class="portlet-body">
    				
    				<!-- 任务开始时间范围 -->
    			    <div class="list-option" id="createTimeDiv">
    					<div class="row">
    						<label class="control-label col-md-2"><?php echo $LANG['UI_JOB_CREATE_TIME_RANGE']?> ：
    						</label>
    						<div class="col-md-4" >
    						    <div class="input-group date form_datetime">
    								<input type="text" size="16" readonly id="currentstartTime" class="form-control">
    								<span class="input-group-btn">
    									<button class="btn default" id="resetStartTime" type="button"><i class="fa fa-times"></i></button>
	    								<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
    								</span>
    							</div>
    						</div>
    						<div class="col-md-4" >
    						    <div class="input-group date form_datetime">
    								<input type="text" size="16" readonly id="currentendTime" class="form-control">
    								<span class="input-group-btn">
    									<button class="btn default" id="resetEndTime" type="button"><i class="fa fa-times"></i></button>
	    								<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
    								</span>
    							</div>
    						</div>
    					</div>
    				</div>
    				
    			    <div class="list-option">
    					<div class="row">
							
							<!-- 任务名 -->
    						<label class="control-label col-md-2"><?php echo $LANG['UI_JOB_RNAME']?> ：
							</label>
							<div class="col-md-4">
								<input style="width:252px; height:34px;" id="taskName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>
							
							<!-- 任务状态 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_JOB_TYPE']?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="taskStatus">
									<option value="0"><?php echo $LANG['UI_JOB_ALL']?></option>
									<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_WAITING']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_RUNNING']?></option>
									<option value="4"><?php echo $LANG['WEB_PLATFORM_DES_STOP']?></option>
									<option value="8"><?php echo $LANG['WEB_PLATFORM_DES_ERROR']?></option>
								</select>
							</div>
    					</div>
    				</div>
    				
    				
    				<div class="list-option">
    					<div class="row">
							<!-- 所在节点 -->
    						<label class="control-label col-md-2"><?php echo $LANG['UI_STORAGE_IN_NODE']?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="nodeSelect" >
								</select>
							</div>
							<!-- 任务创建用户 -->
    						<label class="control-label col-md-2"><?php echo $LANG['UI_JOB_CREATOR']?> ：
							</label>
							<div class="col-md-4">
								<input style="width:252px; height:34px;" id="currentuser" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>
							
    					</div>
    				</div>
    				
    				<div class="list-option">
    					<div class="row">
    						<!-- 模块类型 -->
    						<label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_MODULE_TYPE']?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="moduletype">
									<option value="0"><?php echo $LANG['UI_JOB_ALL']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_VM']?></option>
									<option value="3"><?php echo $LANG['WEB_PLATFORM_DES_FS']?></option>
									<option value="9"><?php echo $LANG['UI_PLATFORM_COPY_OR_ARCHIVE']?></option>
								</select>
							</div>
							<!-- 任务类型 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_TASK_TYPE']?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="vmTasktype">
									<option value="0"><?php echo $LANG['UI_JOB_ALL']?></option>
									<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_BACKUP']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY']?></option>
									<option value="7"><?php echo $LANG['WEB_PLATFORM_DES_INSTANT_RECOVERY']?></option>
									<option value="8"><?php echo $LANG['WEB_PLATFORM_DES_MOTION']?></option>
									<option value="6"><?php echo $LANG['WEB_PLATFORM_DES_FILE_REC']?></option>
								</select>
								<select class="form-control select2me display-none" id="fsTasktype">
									<option value="0"><?php echo $LANG['UI_JOB_ALL']?></option>
									<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_BACKUP']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY']?></option>
								</select>
								<select class="form-control select2me display-none" id="copyTasktype">
									<option value="0"><?php echo $LANG['UI_JOB_ALL']?></option>
									<option value="17"><?php echo $LANG['UI_PLATFORM_BACKUP_COPY']?></option>
									<option value="18"><?php echo $LANG['UI_COPY_BACK']?></option>
									<option value="19"><?php echo $LANG['UI_PLATFORM_ARCHIVE']?></option>
									<option value="20"><?php echo $LANG['UI_PLATFORM_ARCHIVE_FETCH']?></option>
								</select>
							</div>
    					</div>
    				</div>
    				
    				<div class="list-option">
    					<div class="row">
							
							<!-- 虚拟化类型 -->
							<div id="vmtypeDiv" class="display-none">
								<label class="control-label col-md-2"><?php echo $LANG['UI_VCENTER_TYPE']?> ：
								</label>
								<div class="col-md-4">
									<select class="form-control select2me" id="vmtype" name="vmtype">
									</select>
								</div>
							</div>
							
							<!-- 所包含虚拟机名 -->
							<div class="display-none">
	    						<label class="control-label col-md-2"><?php echo $LANG['UI_VCENTER_MACHINE_NAME']?> ：
								</label>
								<div class="col-md-4">
									<select class="form-control select2me" id="">
									</select>
								</div>
							</div>
							
    					</div>
    				</div>
    				
    				
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
				<button type="button"class="btn btn-primary" id="current_serach_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/jobs/current_job.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	