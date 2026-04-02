<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats grey-cararra -->
		<div class="portlet-body" id="current_cdp_job">
				<!-- search -->
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left"></div>
					<div class="table-toolbar-wrapper__right">
						<div class="table-actions-wrapper page-right">
							<span>
							</span>
							<input type="search" maxlength="128" class="cdpjob_search table-group-action-input form-control input-inline input" placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>" aria-controls="example">
							<input id="cdpjob_searchbtn" type="button" class="btn btn-search default" value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>">
							<button type="button" id="cdpjob_searchAll" class="btn green-haze">
							<i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
							</button>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div id="cdp_searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span> </div>
					</div>
				</div>
				<div class="table-container">
					<table class="table table-striped table-hover" id="cdpJobTable">
					<thead>
					<tr role="row" class="heading">
						<th width="20%">
							 <?php echo $LANG['UI_JOB_RNAME'] ?>
						</th>
						<th width="12%">
							 <?php echo $LANG['UI_PUBLIC_TASK_TYPE'] ?>
						</th>
						<th width="16%">
							<?php echo $LANG['UI_JOB_PRODUCT_HOST'] ?>
						</th>
						<th width="16%">
							<?php echo $LANG['UI_JOB_BACKUP_HOST'] ?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_PUBLIC_STATUS'] ?>
						</th>
						<th width="14%">
							<?php echo $LANG['UI_JOB_INTERVAL_TIME'] ?>
						</th>
						<th width="14%">
							<?php echo $LANG['UI_PUBLIC_OPERATION'] ?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
					
					<div class="alert alert-block alert-info fade in" id="marktips">
						<button type="button" class="close" data-dismiss="alert"></button>
						<ul class="alert-ul">
							<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
							<li>
								<?php echo $LANG['UI_JOB_CURRENT_NAME_TIPS'] ?>
							</li>
						</ul>
					</div>
					
				</div>
			</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="cdpJobModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1"  data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					
					<!-- 任务开始时间范围 -->
					<div class="list-option" >
						<div class="row">
							<label class="control-label col-md-2"><?php echo $LANG['UI_JOB_CREATE_MODIFY_TIME_RANGE'] ?> ：
							</label>
							<div class="col-md-6 daterangepickerdiv">
								<input type="text" id="daterangepickerCurrentCdp" class="form-control" autocomplete="off">
								<i class="viconfont vicon-ge_calendar"></i>
							</div>
						</div>
					</div>
					
					<div class="list-option">
						<div class="row">
							
							<!-- 任务名 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_JOB_RNAME'] ?> ：
							</label>
							<div class="col-md-4">
								<input style="width:252px; height:34px;" id="cdp_taskName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>
							
							<!-- 任务类型 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_TASK_TYPE'] ?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="cdp_tasktype">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
									<option value="21"><?php echo $LANG['WEB_PLATFORM_DES_CDP'] ?></option>
									<option value="22"><?php echo $LANG['UI_PLATFORM_DB_DATA_RECOVERY'] ?></option>
									<option value="24"><?php echo $LANG['WEB_PLATFORM_DES_REAL_TIME_SYN'] ?></option>
								</select>
							</div>
						</div>
					</div>
					
					
					<div class="list-option">
						<div class="row">
							<!-- 任务状态 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_JOB_TYPE'] ?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="cdp_status">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
									<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_WAITING'] ?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_RUNNING'] ?></option>
									<option value="4"><?php echo $LANG['WEB_PLATFORM_DES_STOP'] ?></option>
									<option value="8"><?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?></option>
									<option value="5"><?php echo $LANG['WEB_PLATFORM_DES_STOPPING'] ?></option>
								</select>
							</div>						
						</div>
					</div>
					
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button"class="btn btn-primary" id="cdp_serach_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<script type="text/javascript" src="./scripts/platform/jobs/job_tab/current_cdp_job.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	