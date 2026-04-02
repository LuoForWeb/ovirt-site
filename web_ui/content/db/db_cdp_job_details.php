<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<?php
		echo
			'<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">'; ?>
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
<div class="row job-detail">
	<div class="col-md-12 job-detail__halftop">
		<div class="col-md-8 job-detail__halftop__charts">
			<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
			<!-- BEGIN DYNAMIC CHART PORTLET-->
			<div class="portlet-charts">
				<div class="portlet-body width700 margin0auto portlet-charts__body">
					 <div class="row detaildiv">
							<div class="col-md-offset-1 col-md-2">
								<div>
								<img id="productdb" src="./img/db/task/db-running.gif">
								</div>
								<div>
								<img id="producthost" src="./img/db/task/product-host.png" style="margin-left: 40px;">
								</div>
								<div class="textonline pt15">
									<div style="width: 115px;margin-left: 40px;">生产主机</div>
									<div style="width: 200px;height: 80px;text-align: center;margin: 10px 0 0 -30px;word-wrap:break-word;word-break:normal;"><span class="font-blue" id="producthostname"></span></div>
								</div>
							</div>
							<div class="col-md-6 " style="margin: 60px 0 0 0px;">
								<div class="pt15">
									<img id="dataline" src="./img/db/task/data-line.png">
								</div>
							</div>
							<div class="col-md-2 ">
								<div>
								<img id="standbydb" src="./img/db/task/db-waiting.png">
								</div>
								<div>
								<img id="standbyhost" src="./img/db/task/standby-host.png"  style="margin-left: 40px;">
								</div>
								<div  class="min-width500 pt15">
								<div style="width: 115px;margin-left: 40px;">备份主机</div>
									<div style="width: 200px;height: 80px;text-align: center;margin: 10px 0 0 -30px;word-wrap:break-word;word-break:normal;"><span class="font-blue" id="standbyhostname"></span></div>
								</div>
							</div>
					 </div>
				</div>
			</div>
			<!-- END DYNAMIC CHART PORTLET-->
			
			
		</div>
		
		<div class="col-md-4 hp-100" style="padding: 0px;">
			<!-- BEGIN PORTLET-->
			<div class="portlet paddingless hp-100">
				<div class="portlet-body">
					<!--BEGIN TABS-->
					<div class="tabbable tabbable-custom" style="overflow: inherit;height:89%">
						<ul class="nav nav-tabs">
							<li class="active">
								<a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
								<i class="fa fa-info-circle"></i> 概要信息 </a>
							</li>
							<li class="" id="">
								<a href="#tab_1_2" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-ge_advanced"></i> 高级配置 </a>
							</li>
							<li class="" style="display: none;" id="takeovertab">
								<a href="#tab_1_3" data-toggle="tab" aria-expanded="false">
								<i class="fa fa-random"></i> 业务接管 </a>
							</li>
							<li class="" id="hostlog">
								<a href="#tab_1_4" data-toggle="tab" aria-expanded="false">
								<i class="fa fa-list-alt"></i> 主机日志 </a>
							</li>
						</ul>
						<div class="tab-content min-height266 hp-100">
							<div class="tab-pane active" id="tab_1_1">
								<div class="portlet-body ml15">
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_RNAME'] ?>:
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
											 备份类型:
										</div>
										<div class="col-md-8 value" id="backupType">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 数据库类型:
										</div>
										<div class="col-md-8 value" id="vendor">
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
									<div class="row static-info">
										<div class="col-md-4 name">
											 操作:
										</div>
										<div class="col-md-8 value">
											<div class="btn-group dropdown-wrapper">
												<button class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
												<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_GRAIN_OPERATE'] ?> <i class="fa fa-angle-down"></i>
												</button>
												<ul class="dropdown-menu" role="menu">
													<li id="start">
														<button class="btn dropdown-menu__item me-0" type="button">
														<i class="glyphicon glyphicon-play me-4"></i> <?php echo $LANG['WEB_JOB_START'] ?> </button>
													</li>
													<li id="stop">
														<button class="btn dropdown-menu__item me-0" type="button">
														<i class="glyphicon glyphicon-stop me-4"></i> <?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?> </button>
													</li>
													<li class="divider stakeover"></li>
													<li class="stakeover" id="takeover">
														<button class="btn dropdown-menu__item me-0" type="button">
														<i class="fa fa-random me-4"></i> 启动接管</button>
													</li>
													<li class="stakeover"  id="stoptakeover">
														<button class="btn dropdown-menu__item me-0" type="button">
														<i class="fa fa-square me-4"></i> 停止接管</button></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<div class="tab-pane" id="tab_1_2">
								<div class="portlet-body ml15">
									<div class="row static-info">
										<div class="col-md-4 name">
											 生产主机:
										</div>
										<div class="col-md-8 value" id="productHost">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 备份主机:
										</div>
										<div class="col-md-8 value" id="standbyHost">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 日志配置:
										</div>
										<div class="col-md-8 value" id="logSetting">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											备份数据目录:
										</div>
										<div class="col-md-8 value" id="backupDir">
										</div>
									</div>
									<div class="row static-info hisdiv">
										<div class="col-md-4 name">
											历史数据目录:
										</div>
										<div class="col-md-8 value" id="historyDir">
										</div>
									</div>
									<div class="row static-info hisdiv">
										<div class="col-md-4 name">
											历史数据份数:
										</div>
										<div class="col-md-8 value" id="historyCopys">
										</div>
									</div>
								</div>
							</div>
							
							<div class="tab-pane" id="tab_1_3">
								<div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name">
											接管配置:
										</div>
										<div class="col-md-8 value" id="takeoverSetting">
										</div>
									</div>
									<div class="row static-info stakeover">
										<div class="col-md-4 name">
											接管方式:
										</div>
										<div class="col-md-8 value" id="takeovertype">
										</div>
									</div>
									<div class="row static-info stakeover">
										<div class="col-md-4 name">
											接管网卡:
										</div>
										<div class="col-md-7 value " style="border: 1px solid #ddd;height: 70px;overflow-y: auto;" id="takeoverCard">
										</div>
									</div>
									<div class="row static-info stakeover">
										<div class="col-md-4 name">
											启动服务:
										</div>
										<div class="col-md-7 value " style="border: 1px solid #ddd;height: 80px;overflow-y: auto;" id="takeoverService">
										</div>
									</div>
								</div>
							</div>
							
							<div class="tab-pane" id="tab_1_4">
								<div class="portlet-body ml15">
									<div class="row static-info">
										<div class="col-md-4 name" style="margin-top: 7px;">
											 生产主机:
										</div>
										<div class="col-md-8 value">
											<button type="button" class="btn btn-sm green-haze" id="downloadplog">
												<i class="viconfont vicon-ge_download"></i> 下载日志
											</button>
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name" style="margin-top: 7px;">
											 备份主机:
										</div>
										<div class="col-md-8 value">
											<button type="button" class="btn btn-sm green-haze" id="downloadslog">
												<i class="viconfont vicon-ge_download"></i> 下载日志
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
				<ul class="nav nav-tabs floatl">
					<li class="active ">
						<a href="#log" data-toggle="tab">
						<i class="viconfont vicon-ge_running_log "></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
					</li>
					<li>
						<a href="#database" data-toggle="tab">
						<i class="fa fa-list-alt "></i> 数据库详情 </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content">
					<div class="tab-pane active" id="log">
						<ul class="feeds" id="runninglog">
						</ul>
					</div>
					
					<div class="tab-pane" id="database">
						<div class="table-container margin10" style="margin-top: -10px;">
							<table class="table table-striped table-bordered table-hover" id="databasetable">
							<thead>
							<tr role="row" class="heading">
								<th width="2%">
									<?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
								</th>
								<th width="15%">
									 数据库
								</th>
								<th width="10%">
									 实例
								</th>
								<th width="15%">
									 类型
								</th>
								<th width="15%">
									备份数据
								</th>
								<th width="10%">
									同步进度
								</th>
								<th width="10%">
									同步平均速度
								</th>
								<th width="15%">
									历史数据
								</th>
								<th width="15%">
									接管数据
								</th>
								<th width="15%">
									状态
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
<script type="text/javascript" src="/assets/global/plugins/anime/anime.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/db/db_cdp_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	