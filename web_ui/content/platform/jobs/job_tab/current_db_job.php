<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats grey-cararra -->
		<div class="portlet-body" id="current_db_job">
			<!-- search -->
			<div class="table-toolbar-wrapper">
				<div class="table-toolbar-wrapper__left">
				</div>
				<div class="table-toolbar-wrapper__right">
					<div class="table-actions-wrapper page-right">
						<span>
						</span>
						<input type="search" maxlength="128" class="dbjob_search table-group-action-input form-control input-inline input" placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>" aria-controls="example">
						<input id="dbjob_searchbtn" type="button" class="btn btn-search" value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>">
						<button type="button" id="dbjob_searchAll" class="btn green-haze">
							<i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
						</button>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div id="db_searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span> </div>
				</div>
			</div>
			<div class="table-container">
				<table class="table table-striped table-hover" id="dbJobTable">
					<thead>
						<tr role="row" class="heading">
							<th width="10%">
								<?php echo $LANG['UI_JOB_RNAME'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_PUBLIC_TASK_TYPE'] ?>
							</th>
							<th width="5%">
								<?php echo $LANG['UI_DB_DATABASE_TYPE'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_JOB_AGENT'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['WEB_PLATFORM_DES_NODE'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_PUBLIC_NEXT_RUN_TIME'] ?>
							</th>
							<th width="5%">
								<?php echo $LANG['UI_PUBLIC_STATUS'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_JOB_INTERVAL_TIME'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_JOB_SPEED'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_JOB_TRANSFER_SIZE'] ?>
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
		<div id="dbJobModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">

					<!-- 任务开始时间范围 -->
					<div class="list-option" id="createTimeDiv">
						<div class="row">
							<label class="control-label col-md-4"><?php echo $LANG['UI_JOB_CREATE_MODIFY_TIME_RANGE'] ?> ：
							</label>
							<div class="col-md-5 daterangepickerdiv">
								<input type="text" id="daterangepickerCurrentDb" class="form-control" autocomplete="off">
								<i class="viconfont vicon-ge_calendar"></i>
							</div>
						</div>
					</div>

					<div class="list-option">
						<div class="row">

							<!-- 任务名 -->
							<label class="control-label col-md-4"><?php echo $LANG['UI_JOB_RNAME'] ?> ：
							</label>
							<div class="col-md-5">
								<input style="width:322px; height:34px;" id="db_taskName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>
						</div>
					</div>

					<div class="list-option">
						<div class="row">
							<!-- 任务类型 -->
							<label class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TASK_TYPE'] ?> ：
							</label>
							<div class="col-md-5">
								<select class="form-control select2me" id="db_tasktype">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
									<option value="28"><?php echo $LANG['WEB_PLATFORM_DES_BACKUP'] ?></option>
									<option value="29"><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY'] ?></option>
								</select>
							</div>
						</div>
					</div>


					<div class="list-option">
						<div class="row">
							<!-- 所在节点 -->
							<label class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_IN_NODE'] ?> ：
							</label>
							<div class="col-md-5">
								<select class="form-control select2me" id="db_node">
								</select>
							</div>
						</div>
					</div>
					<div class="list-option">
						<div class="row">
							<!-- 任务状态 -->
							<label class="control-label col-md-4"><?php echo $LANG['UI_JOB_TYPE'] ?> ：
							</label>
							<div class="col-md-5">
								<select class="form-control select2me" id="db_status">
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

					<div class="list-option">
						<div class="row">

							<!-- 数据库类型 -->
							<div id="dbtypeDiv" class="">
								<label class="control-label col-md-4"><?php echo $LANG['UI_DB_DATABASE_TYPE'] ?> ：
								</label>
								<div class="col-md-5">
									<select class="form-control select2me" id="dbtype" name="dbtype">
										<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
										<option value="1">SQL Server</option>
										<option value="2">Oracle</option>
										<option value="3">MySQL</option>
										<option value="4">DM</option>
										<option value="5">PostgreSQL</option>
										<option value="6">KingbaseES</option>
										<option value="7">UXDB</option>
										<option value="8">Highgo DB</option>
										<option value="9">MariaDB</option>
										<option value="10">openGauss</option>
										<option value="11">Vastbase</option>
										<option value="12">AntDB</option>
									</select>
								</div>
							</div>

							<!-- 代理 -->
							<div class="display-none">
								<label class="control-label col-md-4"><?php echo $LANG['UI_VCENTER_MACHINE_NAME'] ?> ：
								</label>
								<div class="col-md-5">
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
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="db_serach_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END MODAL -->

	</div>
</div>
<!-- END PAGE CONTENT-->


<script type="text/javascript" src="./scripts/platform/jobs/job_tab/current_db_job.js"></script>
<!-- END PAGE LEVEL PLUGINS -->