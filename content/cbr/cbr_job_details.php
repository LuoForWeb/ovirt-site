<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<?php
		echo
			'<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">';
		?>
		<span>
				<?php
				echo $LANG['UI_PLATFORM_CURRENT_JOB'];
				?>
			</span>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS'] ?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail" id="jobDetail">
	<div class="col-md-12 job-detail__halftop">
		<div class="col-md-8 job-detail__halftop__charts">
			<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
			<input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
			<!-- BEGIN DYNAMIC CHART PORTLET-->
			<div class="portlet-charts">
				<div class="portlet-charts__title">
					<div class="caption">
						<i class="viconfont vicon-ge_task_flow"></i><?php echo $LANG['UI_JOB_FLOW'] ?>
					</div>
				</div>
				<!-- <div class="portlet-body">
					<div id="speedcharthover"></div>
					<div id="speedchart" class="height200"></div>
					<div><span><h4><?php echo $LANG['UI_JOB_TOTAL_PROGRESS'] ?></h4></span><span class="taskprogress" id="progressright"></span></div>
					<div class="progress progress-striped active">
						<div id="total-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
						</div>
					</div>
				</div> -->
				<div class="portlet-charts__body">
					<div class="portlet-charts__body__speedchart">
						<div id="speedcharthover"></div>
						<div id="speedchart"></div>
					</div>
					<div class="progressDiv display-none">
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
							<li class="active nolb">
								<a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
								<i class="viconfont vicon-gaiyao1"></i><?php echo $LANG['UI_JOB_SUMMARY'] ?> </a>
							</li>
							<li class="" id="storageli">
								<a href="#tab_1_2" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-cunchu"></i> <?php echo $LANG['UI_PALTFORM_STORAGE'] ?> </a>
							</li>
							<li class="" id="strategyli">
								<a href="#tab_1_3" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_JOB_STRATEGY'] ?> </a>
							</li>
							<li class="" id="modeli">
								<a href="#tab_1_4" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-ge_advanced"></i> <?php echo $LANG['UI_JOB_HIGH'] ?> </a>
							</li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane active" id="tab_1_1">
								<div class="portlet-body">
									<div class="row static-info taskOperateDiv <?php if (!in_array("global_write", $_SESSION['permission']) && in_array("global_observer", $_SESSION['permission'])) {
										echo "display-none";
									} ?> ">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?>:
										</div>
										<div class="col-md-8 col-operate value">
											<div class="btn-group dropdown-wrapper">
												<button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> 
													<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
												</button>
												<ul class="dropdown-menu" role="menu" id="" >
													<li id="starttask"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['WEB_JOB_START'] ?></button></li>
													<li id="stoptask"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_suspend-copy me-4"></i><?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?></button></li>
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
									<div class="row static-info display-none">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_ESTIMATED_OVER_TIME'] ?>:
										</div>
										<div class="col-md-8 value" id="endTime">
										</div>
									</div>
								</div>
							</div>
							
							<div class="tab-pane" id="tab_1_2">
								<div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_COPY_TARGET_STORAGE'] ?>:
										</div>
										<div class="col-md-8 value" id="storageinfo">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_COPY_TARGET_NODE'] ?>:
										</div>
										<div class="col-md-8 value" id="nodeinfo">
										</div>
									</div>
									<div class="row static-info deduplicationdiv display-none">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_DEDUPLICATION'] ?>:
										</div>
										<div class="col-md-8 value" id="deduplication">
										</div>
									</div>
									<div class="row static-info compresseddiv display-none">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?>:
										</div>
										<div class="col-md-8 value" id="compressed">
										</div>
									</div>
									<!-- 压缩等级 -->
									<div class="row static-info  compressMethodDiv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?>:
										</div>
										<div class="col-md-8 value" id="compressMethod">
										</div>
									</div>
									<div class="row static-info display-none encryptStoragediv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?>:
										</div>
										<div class="col-md-8 value" id="encryptStorage">
										</div>
									</div>
									<!-- 存储加密算法 -->
									<div class="row static-info display-none encrypt-method-div">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>:
										</div>
										<div class="col-md-8 value" id="encryptMethod">
										</div>
									</div>

									<div class="row static-info display-none passwordAutodiv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?>:
										</div>
										<div class="col-md-8 value" id="passwordAuto">
										</div>
									</div>
								</div>
							</div>
								
								
							<div class="tab-pane" id="tab_1_3">
								<div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_CREATE_TIME'] ?>:
										</div>
										<div class="col-md-8 value" id="createTime">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?>:
										</div>
										<div class="col-md-8 value">
											 
											 <span class="label label-success" id="nextTime">
											 </span>
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_CBR_SYNC_BY_STRATEGY'] ?>:
										</div>
										<div class="col-md-8 value" id="strategydes">
										</div>
									</div>
									<div class="row static-info display-none" >
										<div class="col-md-4 name" >
											<?php echo $LANG['UI_CBR_SYNC_ONCE_TIME'] ?>:
										</div>
										<div class="col-md-8 value">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?>:
										</div>
										<div class="col-md-8 value" id="reservedStrategy">
										</div>
									</div>
									<div class="row static-info  transportModediv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>:
										</div>
										<div class="col-md-8 value" id="transportMode">
										</div>
									</div>
									<div class="row static-info  transportEncryptdiv display-none">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>:
										</div>
										<div class="col-md-8 value" id="transportEncrypt">
										</div>
									</div>
									<!-- 传输加密算法 -->
									<div class="row static-info transfer-encrypt-method-div display-none">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>:
										</div>
										<div class="col-md-8 value" id="transferEncryptMethod">
										</div>
									</div>

								</div>
							</div>
							
							<div class="tab-pane" id="tab_1_4">
								<div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_CBR_SYNC_DATA_INTEGRALITY_CHECK'] ?>:
										</div>
										<div class="col-md-8 value" id="verify">
										</div>
									</div>
									<!-- <div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE'] ?>:
										</div>
										<div class="col-md-8 value" id="status">
										</div>
									</div> -->
									<div class="row static-info threadDiv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>:
										</div>
										<div class="col-md-8 value" id="threadNum">
										</div>
										
									</div>
									
									<div class="row static-info speedDiv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:
										</div>
										<div class="col-md-8 value" id="speedlimit" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 4;overflow: hidden;">
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
						<i class="viconfont vicon-tasklog "></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
					</li>
					<li>
						<a href="#vms" data-toggle="tab">
						<i class="viconfont vicon-tongbulidu"></i> <?php echo $LANG['UI_CBR_SYNC_OBJ_LIST'] ?> </a>
					</li>
					<li id="historyli">
						<a href="#history" data-toggle="tab">
						<i class="viconfont vicon-histroytask"></i> <?php echo $LANG['UI_JOB_HISTORY'] ?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content">
					<div class="tab-pane active" id="log">
						<ul class="feeds" id="runninglog">
						</ul>
					</div>
					
					<div class="tab-pane" id="vms">
						<div class="table-toolbar">
							<div class="row">
								<div class="col-md-12">
									<div class="btn-group dropdown-wrapper vmStartDiv <?php
									//检查屏蔽只读观察者的操作按钮
									if (!in_array("global_write", $_SESSION['permission']) && in_array("global_observer", $_SESSION['permission'])) {
										echo 'display-none';
									}
									?>">
										<button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> 
											<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
										</button>
										<ul class="dropdown-menu" role="menu" id="" >
											<li id="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['WEB_JOB_START'] ?> </button></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="table-container">
							<table class="table table-striped table-bordered table-hover" id="vmstable">
							<thead>
							<tr role="row" class="heading">
								<th width="2%">
									<input type="checkbox" class="group-checkable">
								</th>
								<th width="2%">
									<?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
								</th>
								<th width="15%" id="vmname">
									<?php echo $LANG['UI_VCENTER_MACHINE_NAME'] ?>
								</th>
								<th width="10%">
									<span id="th-backupmode"><?php echo $LANG['UI_BACKUP_MODE'] ?></span>
									<span id="th-tasktype" class="display-none"><?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?></span>
								</th>
								<th width="8%" id="vmsize">
									<?php echo $LANG['UI_JOB_VM_SIZE'] ?>
								</th>
								<th width="8%">
									<?php echo $LANG['UI_OS_VALID_DATA_SIZE'] ?>
								</th>
								<th width="8%">
									<?php echo $LANG['UI_JOB_TRANSFER_SIZE'] ?>
								</th>
								<th width="8%">
									<?php echo $LANG['UI_JOB_REAL_SIZE'] ?>
								</th>
								<th width="8%">
									<?php echo $LANG['UI_JOB_SPEED'] ?>
								</th>
								<th width="10%">
									 <?php echo $LANG['UI_JOB_TRA_PROGRESS'] ?>
								</th>
								<th width="10%">
									 <?php echo $LANG['UI_PUBLIC_STATUS'] ?>
								</th>
								<th width="18%">
									 <?php echo $LANG['UI_PUBLIC_DESCRIPTION'] ?>
								</th>
							</tr>
							</thead>
							<tbody>
							</tbody>
							</table>
							<div class="alert alert-block alert-info fade in" id="vmbackuptips">
								<button type="button" class="close" data-dismiss="alert"></button>
								<ul class="alert-ul">
									<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
									<li>
										<?php echo $LANG['UI_CBR_SYNC_SELECT_OBJ_TIPS'] ?>
									</li>
								</ul>
							</div>
						</div>
					</div>
					
					<div class="tab-pane" id="history">
						<div class="table-container">
							<table class="table table-striped table-bordered table-hover" id="historytable">
							<thead>
							<tr role="row" class="heading">
								<th width="2%">
									<?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
								</th>
								<th width="12%">
									 <?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?>
								</th>
								<th width="8%">
									 <?php echo $LANG['UI_PUBLIC_STATUS'] ?>
								</th>
								<th width="12%" id="hisvmsize">
									 <?php echo $LANG['UI_JOB_VM_SIZE'] ?>
								</th>
								<th width="9%">
									<?php echo $LANG['UI_OS_VALID_DATA_SIZE'] ?>
								</th>
								<th width="12%">
									<?php echo $LANG['UI_JOB_TRANSFER_SIZE'] ?>
								</th>
								<th width="12%">
									<?php echo $LANG['UI_JOB_REAL_SIZE'] ?>
								</th>
								<th width="20%">
									 <?php echo $LANG['UI_JOB_START_TIME'] ?>
								</th>
								<th width="20%">
									 <?php echo $LANG['UI_JOB_OVER_TIME'] ?>
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
		<!-- END TAB PORTLET-->
		
		
	</div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./scripts/cbr/cbr_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	