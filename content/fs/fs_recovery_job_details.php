<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <?php
        if($_GET['isCurrent'])
            echo
            '<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">';
        else
            echo
            '<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php? tab=6">';  ?>
        <span>
                <?php
                if($_GET['isCurrent'])
                    echo $LANG['UI_PLATFORM_CURRENT_JOB'];
                else
                    echo 'CDP';  ?>
            </span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS']?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail">
	<div class="col-md-12 job-detail__halftop">
		<div class="col-md-8 job-detail__halftop__charts">
			<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
			<!-- BEGIN DYNAMIC CHART PORTLET-->
			<div class="portlet-charts">
				<div class="portlet-charts__title">
					<div class="caption">
						<i class="viconfont vicon-ge_task_flow"></i> <?php echo $LANG['UI_JOB_FLOW'] ?>
					</div>
				</div>
				<div class="portlet-charts__body">
					<div class="portlet-charts__body__speedchart">
						<div id="speedcharthover"></div>
						<div id="speedchart"></div>
					</div>
					<div class="progressDiv">
						<div class="progressDiv__label">
							<span>
								<?php echo $LANG['UI_JOB_TOTAL_PROGRESS'] ?>
							</span>
						</div>
						<div class="progress progress-striped active">
							<div id="total-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
							</div>
						</div>
						<div class="taskprogress" id="progressright"></div>
					</div>
				</div>
			</div>
			<!-- END DYNAMIC CHART PORTLET-->
		</div>
		<div class="col-md-4 job-detail__halftop__navtabs">
			<!-- BEGIN PORTLET-->
			<div class="portlet">
				<div class="portlet-body">
					<!--BEGIN TABS-->
					<div class="tabbable tabbable-custom">
						<ul class="nav nav-tabs">
							<li class="active">
								<a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
									<i class="fa fa-info-circle "></i> <?php echo $LANG['UI_JOB_DETAILS_SUMMARY_INFO'] ?> </a>
							</li>
							<li class="" id="modeli">
								<a href="#tab_1_2" data-toggle="tab" aria-expanded="false">
									<i class="viconfont vicon-ge_advanced "></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
							</li>
							<li class="" id="hostlog">
								<a href="#tab_1_4" data-toggle="tab" aria-expanded="false">
									<i class="fa fa-list-alt "></i> <?php echo $LANG['WEB_PLATFORM_DES_LOG_HOST'] ?> </a>
							</li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane active" id="tab_1_1">
								<div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_DETAILS_OPERATE'] ?>
										</div>
										<div class="col-md-8 value">
											<div class="btn-group">
												<button class="btn green-haze btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
													<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_GRAIN_OPERATE'] ?> <i class="fa fa-angle-down"></i>
												</button>
												<ul class="dropdown-menu min-width100" role="menu">
													<li id="start">
														<a href="javascript:;">
															<i class="glyphicon glyphicon-play"></i> <?php echo $LANG['WEB_JOB_START'] ?> </a>
													</li>
													<li id="stop">
														<a href="javascript:;">
															<i class="glyphicon glyphicon-stop"></i> <?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?> </a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_TASK_REPORT_TASK_NAME'] ?>:
										</div>
										<div class="col-md-8 value" id="taskName" style="word-break:break-word; max-width:300px;">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?>:
										</div>
										<div class="col-md-8 value" id="taskType">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE'] ?>:
										</div>
										<div class="col-md-8 value" id="status">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_TOTAL_SIZES'] ?>:
										</div>
										<div class="col-md-8 value" id="totalSize">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_ALREADY_COMPLETED_SIZE'] ?>:
										</div>
										<div class="col-md-8 value" id="currentSize">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_START_TIME'] ?>:
										</div>
										<div class="col-md-8 value" id="startTime">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_INTERVAL_TIME'] ?>:
										</div>
										<div class="col-md-8 value" id="intervalTime">
										</div>
									</div>
								</div>
							</div>

							<div class="tab-pane" id="tab_1_2">
								<div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_BACKUP_HOST'] ?>:
										</div>
										<div class="col-md-8 value" id="standbyHost">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_MACHINE'] ?>:
										</div>
										<div class="col-md-8 value" id="productHost">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_DETAILS_RECOVERY_LIST'] ?>:
										</div>
										<div class="col-md-8">
											<div class="filelisttext" id="filelist" style="margin-left:0;height: 100px;">

											</div>
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_DETAILS_PURPOSE_PATH'] ?>:
										</div>
										<div class="col-md-8 value" id="distDir">
										</div>
									</div>
								</div>
							</div>

							<div class="tab-pane" id="tab_1_4">
								<div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name" style="margin-top: 7px;">
											<?php echo $LANG['UI_JOB_BACKUP_HOST'] ?>:
										</div>
										<div class="col-md-8 value">
											<button type="button" class="btn btn-sm green-haze" id="downloadslog">
												<i class="viconfont vicon-ge_download"></i> <?php echo $LANG['UI_LOG_SYSTEM_LOG_DOWNLOAD_NOW'] ?>
											</button>
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name" style="margin-top: 7px;">
											<?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_MACHINE'] ?>
										</div>
										<div class="col-md-8 value">
											<button type="button" class="btn btn-sm green-haze" id="downloadplog">
												<i class="viconfont vicon-ge_download"></i><?php echo $LANG['UI_LOG_SYSTEM_LOG_DOWNLOAD_NOW'] ?>
											</button>
										</div>
									</div>
								</div>
							</div>

						</div>
					</div>
					<!--END TABS-->
				</div>
			</div>
			<!-- END PORTLET-->
		</div>
	</div>
	<div class="col-md-12 job-detail__halfbottom">
		<!-- BEGIN TAB PORTLET-->
		<div class="job-detail__halfbottom__portlet">
			<div class="portlet-title">
				<ul class="nav nav-tabs">
					<li class="active ">
						<a href="#log" data-toggle="tab">
							<i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content">
					<div class="tab-pane active" id="log">
						<ul class="feeds" id="runninglog"></ul>
					</div>
				</div>
			</div>
		</div>
		<!-- END TAB PORTLET-->
	</div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/fs/fs_recovery_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->