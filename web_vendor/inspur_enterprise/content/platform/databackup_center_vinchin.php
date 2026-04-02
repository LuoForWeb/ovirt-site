<?php 
include_once '../../tpl/permission.php';
?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<link href="../../assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css">
<!-- <link href="./css/platform/databackup-center-spur.css" rel="stylesheet" type="text/css"> -->
<link href="./css/platform/revision.css" rel="stylesheet" type="text/css">
<!-- BEGIN PAGE HEADER-->
<div class="page-bar page-spur-bar">
	<ul class="page-breadcrumb">
		<li>
			<div class="data-center-icon"></div>
			<span ><?php echo $LANG['UI_PLATFORM_HOMEPAGE']?></span>
		</li>
	</ul>
    <!-- <div class="systemTimeLabel"><?php echo $LANG['UI_DATACENTER_SYSTEM_TIME']?>: <span id="systemtime"></span></div> -->
</div>
<!-- END PAGE HEADER-->
<div class="datacenter-content datacenter-spur">
	<div class="row mb16">
		<!-- 左上角图标和备份存储 -->
		<div class="col-md-7 col-xs-7 systemboxdiv">
			<!-- 系统信息 -->
			<div class="systemdiv mb16">
				<div class="systembox col-md-3 col-xs-3">
					<div class="img">
					</div>
					<div class="systemdetail">
						<div><?php echo $LANG['UI_DATACENTER_SYSTEM_TIME'] ?></div>
						<div class="sysdate"></div>
						<div class="systime"></div>
					</div>
				</div>
				<div class="systembox col-md-3 col-xs-3">
					<div class="img">
					</div>
					<div class="systemdetail">
						<div><?php echo $LANG['UI_DATACENTER_TOTAL_RUN'] ?></div>
						<div class="sysnum">
							<span id="yearspan" style="display:none;"><span id="year"></span>
							<span class="unit"><?php echo $LANG['WEB_UTILS_YEAR'] ?></span></span>
							<span id="day"></span>
							<span class="unit"><?php echo $LANG['WEB_UTILS_DAY'] ?></span>
						</div>
					</div>
				</div>
				<div class="systembox col-md-3 col-xs-3">
					<div class="img">
					</div>
					<div class="systemdetail">
						<div><?php echo $LANG['UI_PLATFORM_CURRENT_JOB']?></div>
						<div class="sysnum" id="currentTaskNum"></div>
					</div>
				</div>
				<div class="systembox col-md-3 col-xs-3">
					<div class="img">
					</div>
					<div class="systemdetail">
						<div> <?php echo $LANG['UI_PLATFORM_HOSTORY_JOB']?></div>
						<div class="sysnum" id="historyTaskNum"></div>
					</div>
				</div>
			</div>
			<!-- 备份存储 -->
			<div class="storagediv databox">
				<div class="datatop">
					<div class="title"> <?php echo $LANG['UI_DATACENTER_BACKUP_STORAGE']?></div>
				</div>
				<div class="datacontent row">
					<!-- 备份 -->
					<div class="col-md-4 col-sm-4 padding0">
						<div class="box">
							<div id="capacityCircle"></div>
						</div>
						<div class="bottom">
							<div>
								<div class="usedbox">
									<span class="circle"></span><?php echo $LANG['WEB_USERS_QUOTA_FREE_SIZE'] ?>
								</div>
								<div>
									<span class="circle purple"></span><?php echo $LANG['UI_HOMEPAGE_USED_STORAGE'] ?>
								</div>
							</div>
							<div>
								<div class="remaining">
									<span class="remianNum"></span>
									<span class="usedNum"></span>
								</div>

							</div>
							<div>
								<div class="used">
									<span class="remainPercent"></span>
									<span class="usedPercent"></span>
								</div>
							</div>
							
						</div>
					</div>
					<!-- 最近七日用量统计用量统计 -->
					<div class="col-md-8 col-xs-8 padding0">
						<div class="consumpstatic">
							<div id="consumpChart"></div>
						</div>

					</div>
				</div>
			</div>
		
		</div>

		<!-- 任务信息 -->
		<div class="col-md-5 col-xs-5 taskinfo">
			<!-- 当前任务 -->
			<div class="currentdiv mb16 databox">
				<div class="datatop">
					<div class="title"> <?php echo $LANG['UI_PLATFORM_CURRENT_JOB']?></div>
				</div>
				<div class="currentdata">
					<div class="table-container">
						<table class="table table-hover" id="currentTaskTable" data-detail-view ="false">
							<thead>
								<tr role="row" class="heading">
									<th width="25%">
										 <?php echo $LANG['UI_JOB_RNAME']?>
									</th>
									<th width="25%">
										 <?php echo $LANG['UI_PUBLIC_MODULE_TYPE']?>
									</th>
									<th width="25%">
										 <?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>
									</th>
									<th width="25%">
										<?php echo $LANG['UI_PUBLIC_STATUS']?>
									</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>

			</div>
			<!-- 历史任务 -->
			<div class="historydiv databox">
				<div class="datatop">
					<div class="title"><?php echo $LANG['UI_PLATFORM_HOSTORY_JOB']?></div>
				</div>
				<div class="historydata">
					<div class="table-container">
						<table class="table table-hover" id="historyTable" data-detail-view ="false">
							<thead>
								<tr role="row" class="heading">
									<th width="25%">
										 <?php echo $LANG['UI_JOB_RNAME']?>
									</th>
									<th width="25%">
										 <?php echo $LANG['UI_PUBLIC_MODULE_TYPE']?>
									</th>
									<th width="25%">
										 <?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>
									</th>
									<th width="25%">
										<?php echo $LANG['UI_PUBLIC_STATUS']?>
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
	<!-- 系统监控 -->
	<div class="row ">
		<div class="col-md-12 col-xs-12">
			<div class="monitordiv databox">
				<div class="datatop">
					<div class="title"><?php echo $LANG['UI_SYSTEM_MONITOR_SYSTEM_MONITOR'] ?></div>
					<div class="floatRight">
						<select class="form-control select2me" name="standbyhost" id="node_uuid"></select>
					</div>
				</div>
				<!-- CPU使用率 -->
				<div class="col-md-4 col-xs-4 pl0 littleScreen-pr cpudiv" >
					<div class="cpu">
						<div id="cpuChart"></div>
					</div>
				</div>
				<!-- 内存使用率 -->
				<div class="col-md-4 col-xs-4 pl0 littleScreen-pr memerydiv" >
					<div class="memery">
						<div id="memeryChart"></div>
					</div>
				</div>
				<!-- 网络流量 -->
				<div class="col-md-4 col-xs-4 pl0 littleScreen-pr networkTrafficdiv">
					<div class="networkTraffic">
						<div id="networkTrafficChart"></div>
					</div>
				</div>
				<div style="clear:both"></div>
			</div>
	

		</div>

	</div>

</div>





<!-- BEGIN PAGE LEVEL PLUGINS -->
<!-- <script src="./scripts/plugins/flexible.js"></script> -->

<script src="./assets/global/plugins/echarts/V5.01/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/databackup_center.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	