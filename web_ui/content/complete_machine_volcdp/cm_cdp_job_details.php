<?php 
	include_once '../../tpl/permission.php';
	$cdpRecoveryType = $_SESSION['CONF']['TASK_TYPE']['VOL_CDP_RECOVERY'];
	$cdpReplictaionType = $_SESSION['CONF']['TASK_TYPE']['VOL_CDP_REPLICATION'];
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link href="/assets/global/plugins/select2/select2-4.1.0/css/select2.min.css"rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">
            <span> <?php echo $LANG['UI_PLATFORM_CURRENT_JOB']?></span>
        </a>
    </li>
	<span>></span>
	<span class="curent"> <?php echo $LANG['UI_PLATFORM_JOB_DETAILS'] ?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail cmcdpjobdetaildiv" id="">
    <div class="col-md-12 job-detail__halftop">
        <div class="portlet-body" id="jobDetail">
			<div class="col-md-7 job-detail__halftop__charts">
				<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
				<input id="task_type" value="<?php echo $_GET['type']; ?>" class="display-none"></input>
				<input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
				<!-- BEGIN DYNAMIC CHART PORTLET-->
				<div class="portlet-charts">
					<div class="portlet-body">
						<div class="tabbable tabbable-custom" id="taskFlowOrMapTabs">
								<ul class="nav nav-tabs" id="taskFlowOrMapNavTabs">
									<li class="active" id="task_plot_li">
										<a href="#tab_cm_task_map" data-toggle="tab" aria-expanded="true">
											<i class="viconfont vicon-ge_data_flow"></i> <?php echo $LANG['UI_JOB_TASK_DATA_FLOW']; ?> </a>
									</li>

									<li class="nolb">
										<a href="#tab_cm_chart" data-toggle="tab" aria-expanded="false">
											<i class="viconfont vicon-ge_task_flow"></i> <?php echo $LANG['UI_JOB_FLOW']; ?> </a>
									</li>
								</ul>
							</div>
							
							<div class="tab-content">
								<!-- 数据流量chart -->
								<div class="tab-pane  display-none" id="tab_cm_chart">
									<div class="portlet-charts__body" style="height: 100%;">
										<div class="portlet-charts__body__speedchart">
											<div id="speedcharthover"></div>
											<div id="cmSpeedChart"></div>
										</div>
										<div class="progressDiv display-none">
											<div class="progressDiv__label task_total_progress">
												<span><?php echo $LANG['UI_JOB_TOTAL_PROGRESS']; ?></span>
											</div>

											<div class="progressDiv__label task_consistency_progress display-none">
												<span><?php echo $LANG['UI_JOB_DATA_CONSISTENCY_PROGRESS']; ?></span>
											</div>
											
											<div class="progress progress-striped active">
												<div id="total-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
											</div>
											<div class="taskprogress" id="progressright"></div>
										</div>
									</div> 
								</div>
								<!-- 数据流向示意图 -->
                                <?php include_once './cm_cdp_job_details_dataflow.php'; ?>
							</div>
						</div>
					</div>
			</div>
			
			<div class="col-md-5 job-detail__halftop__navtabs">
				<!-- BEGIN PORTLET-->
				<div class="portlet">
					<div class="portlet-body">
						<!--BEGIN TABS-->
						<div class="tabbable tabbable-custom swiper-detail">
						<?php 
							$taskType = $_GET['type'];
							if($taskType == $cdpRecoveryType){  //cdp恢复任务
						?>
							<div class="portlet-title init_tab_title">
								<i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
							</div>
						<?php } else { ?>
							<ul class="nav nav-tabs">
								<li class="active nolb">
									<a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
										<i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY']; ?> </a>
								</li>
								<?php 
									$taskType = $_GET['type'];
									if($taskType != $cdpRecoveryType){  //cdp恢复任务
								?>
								<li id="takeoverli">
									<a href="#tab_takeover" data-toggle="tab" aria-expanded="false">
										<i class="icon-puzzle "></i> 
										<span id="takeover_title">
											<?php 
												if($taskType == $cdpReplictaionType){
													echo $LANG['WEB_PLATFORM_DES_TAKEOVER']; 
												}else{
													echo $LANG['UI_CM_CDP_TAKEOVER_MODE_EMERGENCY']; 
												}
												
											?>
										</span>
									 </a>
								</li>
								<?php } ?>
								
							</ul>
						<?php } ?>
							<div class="tab-content">
								<!-- 概览-- -->
								<div class="tab-pane active" id="tab_1_1">
									<div class="portlet-body">
										<!-- 操作 -->
										<div class="row static-info taskOperateDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE']; ?>:
											</div>
											<div class="col-md-8 col-operate value">
												<div class="btn-group">
													
													<button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle hover-initialized taskOperateButton" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
															<?php echo $LANG['UI_PUBLIC_OPERATION']; ?> <i class="fa fa-angle-down"></i>
													</button>
													<ul class="dropdown-menu min-width100" role="menu" id="cmCdpOpList">
													</ul>
												</div>
											</div>
										</div>
										<!-- 任务名 -->
										<div class="row static-info">
											<div class="col-md-4 name" id="">
												<?php echo $LANG['UI_TASK_REPORT_TASK_NAME']; ?>:
											</div>
											<div class="col-md-8 value col-taskname cmtaskname" id="taskName">
											</div>
										</div>
										<!-- 任务类型 -->
										<div class="row static-info">
											<div class="col-md-4 name" id="">
												<?php echo $LANG['UI_PUBLIC_TASK_TYPE']; ?>:
											</div>
											<div class="col-md-8 value col-taskname cmtasktype" id="cmtaskType">
											</div>
										</div>
										<!-- 复制任务数据备份 -->
										<div class="row static-info display-none replicationtaskbackupdataview">
											<div class="col-md-4 name" id="">
												<?php echo $LANG['UI_PLATFORM_BACKUPDATA']; ?>:
											</div>
											<div class="col-md-8 value col-taskname cmbackupmode" id="">
											</div>
										</div>
										
										<!-- 备份模式 -->
										<div class="row static-info display-none task_backup_mode_view">
											<div class="col-md-4 name" id="">
												<?php echo $LANG['UI_BACKUP_MODE']; ?>:
											</div>
											<div class="col-md-8 value col-taskname cmbackupmode" id="taskDetailBackupMode">
											</div>
										</div>
										<!-- 任务状态 -->
										<div class="row static-info">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE']; ?>:
											</div>
											<div class="col-md-8 value cmtaskstatus" id="status">
											</div>
										</div>
										<!-- 任务阶段 -->
										<div class="row static-info taskstageview">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_TASK_PHASE']; ?>:
											</div>
											<div class="col-md-8 value cmtaskstage" id="taskStage">
											</div>
										</div>
										<!--当前校验卷 -->
										<div class="row static-info display-none currentcheckvolumeDiv">
											<div class="col-md-4 name" >
												<?php echo $LANG['UI_VOL_CDP_CURRENT_CHECK_VOLUME']; ?>:
											</div>
											<div class="col-md-8 value" id="currentCheckVolume">
											</div>
										</div>
										<!-- 任务总大小 -->
										<div class="row static-info totalsizeDiv">
											<div class="col-md-4 name" id="taskTotalSizeStr">
												<?php echo $LANG['UI_JOB_TOTAL_SIZES']; ?>:
											</div>
											<div class="col-md-8 value" id="totalSize">
											</div>
										</div>
										
										<!-- 显示已处理数据大小 -->
										<div class="row static-info display-none" id="currentSizeView">
											<div class="col-md-4 processeddata" id="currentSizeStr">
												<?php echo $LANG['UI_JOB_ALREADY_COMPLETED_SIZE']; ?>:
											</div>
											<div class="col-md-8 value cmcurrentsize" id="currentSize">
											</div>
										</div>

										<!-- 接管类型 -->
										<div class="row static-info display-none takeoverTypeDiv">
											<div class="col-md-4 name" id="takeoverTypeStr">
												<?php echo $LANG['UI_JOB_TAKEOVER_TYPE']; ?>:
											</div>
											<div class="col-md-8 value" id="takeoverType">
											</div>
										</div>

										<!-- 接管数据源 -->
										<div class="row static-info display-none" id="takeoverDataView">
											<div class="col-md-4 name ">
												<?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_SOURCE']; ?>:
											</div>
											<div class="col-md-8 value" id="takeoverDataSource">
											</div>
										</div>
										<!-- 显示任务开始时间 -->
										<div class="row static-info">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_START_TIME']; ?>:
											</div>
											<div class="col-md-8 value" id="startTime">
											</div>
										</div>
										<!-- 显示任务持续时间 -->
										<div class="row static-info">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_INTERVAL_TIME']; ?>:
											</div>
											<div class="col-md-8 value" id="intervalTime">
											</div>
										</div>
										<!-- 任务更多配置详情 -->
										<div class="row static-info taskmoreconfdiv">
											<div class="col-md-4 name">
											<?php echo $LANG['UI_PUBLIC_MORE_CONFIGURATION'] ?>:
											</div>
											<div class="col-md-8 value" id="">
												<a href="javascript:void(0)" id="details_more" class="colorgreen" data-toggle="drawer" data-target="#drawer-1"><?php echo $LANG['UI_PUBLIC_MORE_DETAIL']; ?></a>
											</div>
											<!-- <div class="col-md-8 value">
												<button id="details_more" class="btn green-haze" data-toggle="drawer" data-target="#drawer-1"><i class="viconfont vicon-tenant-detail mr8"></i><?php echo $LANG['UI_PUBLIC_MORE_DETAIL']; ?></button>
											</div> -->
										</div>
										
									</div>
								</div>
								<!-- 接管相关配置 -->
								<div class="tab-pane" id="tab_takeover">
									<div class="portlet-body">
										<!-- 接管配置 -->
										<div class="row static-info cmtakeoverconfdiv">
											<div class="col-md-4 name takeoverconftext " id="">
												<?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE_EMERGENCY']; ?>:
											</div>
											<div class="col-md-8 value cmtakeoverconf" id="takeoverConfFlag"> 
											</div>
										</div>
										<!-- 接管配置详情 -->
										<div class="row static-info display-none takeoverconfdiv">
											<!-- 接管方式 -->
											<div class="row static-info takeoverModeview">
												<div class="col-md-4 name" id="">
													<?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE']; ?>:
												</div>
                                                <div class="col-md-2 value cmtakeovermode" id="takeoverMode">
												</div>
                                                <a href="javascript:void(0)" class="ml-30 display-none colorgreen tempagentconfherf" data-toggle="drawer" data-target="#drawer-2">
                                                    <?php echo $LANG['UI_JOB_TEMP_AGENT_CONF'] ?>
                                                </a>
											</div>

											<!-- 接管备机 -->
											<div class="row static-info">
												<div class="col-md-4 name" id="">
													<?php echo $LANG['UI_VOL_CDP_STANDBY']; ?>:
												</div>
												<div class="col-md-8 value" >
													<a href="javascript:void(0)" id="cmTakeoverStandbyRemoteControl" target="">
														<i class="viconfont vicon-web-console fs18 clickicon display-none"></i>	
														<span id="cmTakeoverStandby"></span>
													</a>
												</div>
											</div>
											
				
											<!-- 手动接管时间点 -->
											<div class="row static-info display-none takeoverTimestampiv">
												<div class="col-md-4 name">
													<?php echo $LANG['UI_VOL_CDP_TAKEOVER_POINT']; ?>:
												</div>
												<div class="col-md-8 value" id="takeoverTimestamp">
												</div>
											</div>

											<!-- 主机IP漂移 -->
											<div class="row static-info display-none volCdpMasterIpSwitchDiv">
												<div class="col-md-4 name">
													<?php echo $LANG['UI_VOL_CDP_HOST_IP_DRIFT']; ?>:
												</div>
												<div class="col-md-8 value" id="volCdpMasterIpSwitch">
												</div>
											</div>

											<!-- 安全配置
											<div class="row static-info takeoversafe">
												<div class="col-md-4 name">
													<?php echo $LANG['UI_PLATFORM_SAFE_SETTING']; ?>:
												</div>
												<div class="col-md-8 value" id="takeoverSafeConf">
												</div>
											</div>
											
											<div class="row static-info display-none verificationtypediv">
												<div class="col-md-4 name">
													<?php echo $LANG['UI_PLATFORM_ABNORMAL_SCAN']; ?>:
												</div>
												<div class="col-md-8 value" id="verificationType">
												</div>
											</div>
											-->

											<!-- 接管应用-->
											<div class="row static-info cdpTakeoverAppDiv display-none">
												<div class="col-md-4 name">
													<?php echo $LANG['UI_VOL_CDP_TAKEOVER_APPLICATION']; ?>:
												</div>
												<div class="col-md-8 value" id="cdpTakeoverApp">
												</div>
											</div>

											<!-- 自动接管 -->
                                            <div class="row static-info cdpautotakeoverconfdiv display-none">
                                                <div class="col-md-4 name">
                                                    <?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER']; ?>:
                                                </div>
                                                <div class="col-md-2 value">
                                                    <div class="floatl" id = "cdpAutoTakeoverConf"></div>
                                                </div>
                                                <div class="col-md-2">
                                                    <a href="javascript:void(0)" class="ml-50 colorgreen autoTakeoverConfig" data-toggle="drawer" data-target="#drawer-3">
                                                        <?php echo $LANG['UI_CM_CDP_VIEW_AUTO_TAKEOVER_CONFIG']?>
                                                    </a>
                                                </div>
                                            </div>

											<!-- 接管时间 -->
											<div class="row static-info takeovertimepointdiv display-none">
												<div class="col-md-4 name">
													<?php echo $LANG['UI_CM_CDP_DETAILS_TAKEOVER_TIME_POINT']; ?>:
												</div>
												<div class="col-md-8 value" id="takeovertimepoint">
												</div>
											</div>

											<!-- 获取/业务IP接管配置 -->
											<div class="row static-info display-none takeoverNetworConfDiv ">
												<div class="col-md-4 name">
													<?php echo $LANG['UI_VOL_CDP_HOST_IP_SERIVCE_CONF']; ?>:
												</div>
												<div class="col-md-8 value" id="">
													<a href="javascript:void(0)" id="taskTakeoverNetworkConf" class="colorgreen"><?php echo $LANG['UI_VOL_CDP_VIEW_MODIFY_IP_SERVICE_CONF']; ?></a>
												</div>
											</div>

											<!-- 获取/设置接管回切配置 -->
											<div class="row static-info display-none takeoverFailbackConfDiv ">
												<div class="col-md-4 name">
													<?php echo $LANG['UI_JOB_TASK_FAILBACK_CONFIGURE']; ?>:
												</div>
												<div class="col-md-8 value set_failback_conf_view">
													<a href="javascript:void(0)" id="takeoverFailbackConf" class="colorgreen" data-toggle="drawer" data-target="#cmCdpFailbackTaskConf"><?php echo $LANG['UI_JOB_VIEW_MODIFY_FAILBACK']; ?></a>
												</div>
                                                <div class="col-md-8 value view_failback_conf_view">
                                                    <a href="javascript:void(0)" id="details_more_failback" class="colorgreen" data-toggle="drawer" data-target="#failbackConfigView"><?php echo $LANG['UI_PUBLIC_MORE_DETAIL']; ?></a>
                                                </div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
    <div class="col-md-12 job-detail__halfbottom">
        <!-- BEGIN TAB PORTLET-->
        <div class="job-detail__halfbottom__portlet">
			<div class="portlet-title">
				<ul class="nav nav-tabs">
					<li class="active ">
						<a href="#log" data-toggle="tab">
							<i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG']; ?> </a>
					</li>
					<li>
						<a href="#cmdiskinfo" data-toggle="tab">
							<i class="viconfont vicon-ge_volume_information"></i> <?php echo $LANG['UI_CM_CDP_TASK_DETAIL_DISK_OR_APP_TEXT']; ?> </a>
					</li>
					<li id="historyli">
						<a href="#cmhistory" data-toggle="tab">
							<i class="viconfont vicon-histroytask"></i> <?php echo $LANG['UI_JOB_HISTORY']; ?> </a>
					</li>
				</ul>
			</div>
            <div class="portlet-body">
                <div class="tab-content">
                    <!-- 运行日志 -->
                    <div class="tab-pane active running-log-pane" id="log">
                        <div class="time-range-wrapper">
                            <div id="running_log_daterangepicker_wrapper">
                                <i class="viconfont vicon-shijiankongjian"></i>
                                <span class="running-log-search"><?php echo $LANG['UI_TOOLS_TABLE_START_END_TIME'] ?></span>
                            </div>
                        </div>
                        <div class="running-log-content">
                            <ul id="runninglog"></ul>
                        </div>
                    </div>
					<!-- 监控设备信息 -->
                    <div class="tab-pane" id="cmdiskinfo">
                        <div class="table-container">
							<table class="table table-striped table-bordered table-hover" id="cmMonitorDeviceTable">
							</table>
                        </div>
                    </div>
                    <!-- 历史任务列表 -->
                    <div class="tab-pane" id="cmhistory">
						<div class="table-container">
							<table class="table table-striped table-bordered table-hover" id="cmHistoryTable">
							</table>
						</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->

<!-- 任务接管主机应用IP映射配置  START -->
<div id="" class="modal xmodal fade form-horizontal cdptaskTakeoverNetworkConfModal" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close " data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_VOL_CDP_HOST_IP_SERIVCE_CONF']; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_TASK_IP_SERVICE_CONF']; ?></label>
			<div class="col-md-10">
				<div class="portlet">
					<div class="portlet-body panel panel-default strategy-panel">
						<div class="panel-group accordion mt10 height200" style="overflow-y:auto" id="task_takeover_ip_server_conf">

						</div>
                        <div class="alert alert-block alert-info fade in mt-20" style="margin-bottom:0px;" id="">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <ul class="alert-ul">
                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                <li><?php echo $LANG['UI_VOL_CDP_NET_CARD_CONF_TIP']; ?> </li>
                            </ul>
                        </div>
					</div>
				</div>
			</div>

			<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_STANDBY_GATEWAY_CONF']; ?></label>
			<div class="col-md-10">
				<div class="portlet">
					<div class="portlet-body panel panel-default strategy-panel">
						<div class="panel-group accordion mt10 height200" style="overflow-y:auto" id="task_standby_gateway_conf">

						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
		<button type="button" class="btn btn-primary" id="submit_task_takeover_network_conf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
	</div>
</div>
<!-- 任务接管主机应用IP映射配置  END -->


<!-- drawer开始 -->
<div class="drawer slide aws-drawer-width_en min-width620" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<h4 class="drawer-title ml-15" id="drawer-1-title">
				<!--更多详请-->
                <i class="viconfont vicon-tenant-detail mr8"></i><?php echo $LANG['UI_CM_CDP_TASK_DETAIL_MORE_TEXT']; ?>
				<span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
			</h4>
		</div>
		<div class="drawer-body config-detail-wrap config-detail-drawer-backup">
            <div class="mt20 strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-celve1 me-4"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
                </div>
				<div class="strategy-group__title">
					<span class="decoration me-4"></span><span
						class="strategy-group__title__text"><?php echo $LANG['UI_VERIFY_COMMON_SETTING'];?></span>
				</div>
				<div class="strategy-group__form">
					<!-- 创建/修改时间 -->
                    <div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_JOB_CREATE_TIME'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="createTime" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
						</div>
					</div>
					<div class="backupdestinationview ">
						<!-- 目标节点 -->
						<div class="strategy-group__form">
							<div class="strategy-group__form__item align-items-baseline">
								<div class="strategy-group__form__item__label col-md-4 name">
									<?php echo $LANG['UI_JOB_BACKUP_NODE'] ?>:
								</div>
								<div class="strategy-group__form__item__value col-md-8 value" id="nodeinfo" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
								</div>
							</div>
						</div>
						<!-- 存储设备-->
                        <div class="strategy-group__form">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_JOB_STORAGE_DEV'] ?>:
								</div>
								<div class="strategy-group__form__item__value col-md-8 value" id="storageinfo" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 5;overflow: hidden;">
								</div>
                            </div>
                        </div>
					</div>
                    <div class="speedlimitconfview">
                    </div>
					<div class="row static-info">
						<div class="advanced-conf-title-icon floatl mt2"></div>
						<div class="floatl ">
							<!-- 限速策略-->
							<span class="ms-12"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'];?></span>
						</div>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="speedlimit" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
							</div>
						</div>
					</div>
				</div>
				<div class="display-none tagpointconfview">
                    <div class="row static-info">
                        <div class="advanced-conf-title-icon floatl mt2"></div>
                        <div class="floatl ">
							<!-- 标签策略-->
                            <span class="ms-12"><?php echo $LANG['UI_JOB_LABEL_STRATEGY'];?></span>
                        </div>
					</div>
					<!-- 标签策略-->
					<div class="strategy-group__form">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_JOB_LABEL_STRATEGY'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="tagdes" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
							</div>
						</div>
					</div>
					<!-- 标签策略下次执行时间-->
					<div class="strategy-group__form">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_JOB_NEXT_LABEL_EXECUTION_TIME'] ?>:
							</div>
							<div class="strategy-group__form__item__value  col-md-8 value" id="nextTime" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
							</div>
						</div>
					</div>
				</div>
				<div class="backuptaskconfview">
					<div class = "taskdetailstoragepolicydiv"> 
						<div class="row static-info ">
							<div class="advanced-conf-title-icon floatl mt2"></div>
							<div class="floatl ">
								<!-- 存储策略 -->
								<span class="ms-12"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY']; ?></span>
							</div>
						</div>
						<div class="strategy-group__form">
							<div class="strategy-group__form__item align-items-baseline">
								<div class="strategy-group__form__item__label col-md-4 name">
									<?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?>:
								</div>
								<div class="strategy-group__form__item__value col-md-8 value" id="compressed">
								</div>
							</div>
						</div>
						<!-- 压缩等级 -->
							
						<div class="strategy-group__form compressMethodDiv">
							<div class="strategy-group__form__item align-items-baseline">
								<div class="strategy-group__form__item__label col-md-4 name">
									<?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?>:
								</div>
								<div class="strategy-group__form__item__value col-md-8 value" id="compressMethod">
								</div>
							</div>
						</div>
						<!-- 数据加密 --> 
						<div class="strategy-group__form encryptStoragediv">
							<div class="strategy-group__form__item align-items-baseline">
								<div class="strategy-group__form__item__label col-md-4 name">
									<?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?>:
								</div>
								<div class="strategy-group__form__item__value col-md-8 value" id="encryptStorage">
								</div>
							</div>
						</div>
						<!-- 存储加密算法 -->
						<div class="strategy-group__form display-none encryptmethoddiv">
							<div class="strategy-group__form__item align-items-baseline">
								<div class="strategy-group__form__item__label col-md-4 name">
									<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>:
								</div>
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="encryptMethod">
							</div>
						</div>
						<!-- 密码自动生成 -->
						<div class="strategy-group__form passwordAutodiv display-none">
							<div class="strategy-group__form__item align-items-baseline">
								<div class="strategy-group__form__item__label col-md-4 name">
									<?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?>:
								</div>
								
								<div class="strategy-group__form__item__value col-md-8 value" id="passwordAuto">
								</div>
							</div>
						</div>
					</div>
					<!-- 保留策略 -->
                    <div class="display-none reserved_strategy_view">
						<div class="row static-info">
							<div class="advanced-conf-title-icon floatl mt2"></div>
							<div class="floatl ">
								<span class="ms-12"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'];?> </span>
							</div>
						</div>
						<div class="strategy-group__form reserved_strategy_div">
							<div class="strategy-group__form__item align-items-baseline">
								<div class="strategy-group__form__item__label col-md-4 name">
									<?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?>:
								</div>
								<div class="strategy-group__form__item__value col-md-8 value" id="reservedStrategy">
								</div>
							</div>
						</div>
					</div>
				</div>	
				<hr class="details_more_hr">
				<div class="details_more_box ">
					<div class="details_more_head">
						<div class="row">
							<!-- 传输策略-->
							<i class="viconfont vicon-a-chuanshu mr8"></i><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?>
						</div>
					</div>
				</div>
				<!-- 传输网络 -->
				<div class="strategy-group__form  transportNetworkDiv">
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="transportNetworkInfo">
						</div>
					</div>
				</div>
				<div class="transportpolicyview">
					<!-- 传输加密 -->
					<div class="strategy-group__form">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="transportEncrypt"> 
							</div>
						</div>
					</div>
					<!-- 传输加密算法 -->
					<div class="strategy-group__form transfer-encrypt-method-div ">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="transferEncryptMethod">
							</div>
						</div>
					</div>	
					<!-- 压缩传输 -->
					<div class="strategy-group__form  transportCompressdiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_PLATFORM_SRC_COMPRESS']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="transportCompress">
							</div>
						</div>
					</div>
					<!-- 传输压缩等级 -->
					<div class="strategy-group__form  transportCompressMethodDiv display-none">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="transportCompressMethod">
							</div>
						</div>
					</div>
					<!-- 传输线程个数 -->
					<div class="strategy-group__form  transportThreadNumdiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="transportThreadNum">
							</div>
						</div>
					</div>

					<!-- 传输大小 -->
					<div class="strategy-group__form  transportPacketSizediv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="transportPacketSize">
							</div>
						</div>
					</div>
				</div>
				<!-- 脚本配置 --> 
				<div class="scriptview">
					<hr class="details_more_hr">
					<div class="details_more_box cmscriptdiv">
						<div class="details_more_head">
							<div class="row">
								<i class="viconfont vicon-anquancelve mr8"></i><?php echo $LANG['UI_CM_CDP_SCRIPT_CONFIGURE']; ?>
							</div>
						</div>
					</div>
					<div class="strategy-group__form backupscripdiv">
						<!--备份前脚本 -->
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['WEB_KUBE_BACKUP_BEFORE_BACKUP']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value scriptbeforebackup" id="">
							</div>
						</div>
					</div>
					<!-- 备份后脚本 -->
					<div class="strategy-group__form backupscripdiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_PUBLIC_AFTER_BACKUP_SCRIPT']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value scriptafterbackup" id="">
							</div>
						</div>
					</div>	
					<!-- 接管前脚本 -->
					<!-- bug #26101  需求进行屏蔽 【数据管理-整机接管】手动接管任务-任务详情里更多详情中屏蔽接管前显示 -->
					<div class="strategy-group__form beforetakeoverscripdiv display-none">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_BEFORE_TAKEOVER']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value scriptbeforetakeover" id="">
							</div>
						</div>
					</div>
					<!-- 接管后脚本 -->
					<div class="strategy-group__form takeoverscripdiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_AFTER_TAKEOVER']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value scriptaftertakeover" id="">
								
							</div>
						</div>
					</div>
					<!-- 接管检测脚本 -->
					<div class="strategy-group__form takeovermonitorscripdiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_TAKEOVER_MONITOR']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value scripttakeoverdetection" id="">
								
							</div>
						</div>
					</div>
				</div>
				<!-- 安全配置 -->
				<div class="safesettingview display-none">
					<hr class="details_more_hr">
					<div class="details_more_box cmsafesettingdiv">
						<div class="details_more_head">
							<div class="row">
								<i class="viconfont vicon-anquancelve mr8"></i><?php echo $LANG['UI_PLATFORM_SAFE_SETTING']; ?>
							</div>
						</div>
					</div>
					<!-- 病毒监测 开关-->
					<div class="strategy-group__form takeoversafeconfgdiv display-none">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_SAFE_STRATEGY_VIRUS_CHECK']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value takeoversafeconfgswtich" id="">
								
							</div>
						</div>
					</div>
					<!-- 病毒扫描异常处理 --> 
					<div class="strategy-group__form safescandiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_PLATFORM_SAFE_SETTING_STRATEGY']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value safescandetail" id="">
								
							</div>
						</div>
					</div>
				</div>
				<div class="details_more_box advancedconfigdiv">
					<hr class="details_more_hr">
					<div class="details_more_head">
						<div class="row">
							<!--  高级配置 -->
							<i class="viconfont vicon-gaojipeizhi mr8"></i><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'];?> 
						</div>
					</div>
				</div>
				<!-- 备份高级配置 -->
				<div class = "isbackup_advanced_conf">
					<!-- 公共配置 -->
					<div class="row static-info">
						<div class="advanced-conf-title-icon floatl mt2"></div>
						<div class="floatl ">
							<span class="ms-12"><?php echo $LANG['UI_CM_CDP_BACKUP_COMMON_CONF'];?></span>
						</div>
					</div>
					<!-- 存储数据块大小 -->
					<div class="strategy-group__form storedatabasesizediv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_STORAGE_BLOCK_SIZE']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value " id="storeDatabaseSize">
							</div>
						</div>
					</div>
					<!-- IO复制模式 -->
					<div class="strategy-group__form ioreplicationmodediv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_IO_REPLICATION_MODE']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="ioReplicationMode">
							</div>
						</div>
					</div>
					<!-- 全量数据备份 -->
					<div class="strategy-group__form alldatabackupdiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_BACKUP_VALID_DATA']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="activeDataBackup">
							</div>
						</div>
					</div>
					<!-- 跳过坏块配置 -->
					<div class="strategy-group__form skipbadblockbackupsdiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_SKIP_BAD_BLOCK_BACKUP']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="skipBadBlockBackupsdiv">
							</div>
						</div>
					</div>
					<!-- 断点续传 -->
					<div class="strategy-group__form  automaticfaultrecoverydiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_AUTO_FAULT_RECOVERY'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="autoMaticFaultRecoveryFlag">
							</div>
						</div>
					</div>
					<!-- 缓存配置 -->
					<div class="row static-info alldatabackupdiv">
						<div class="advanced-conf-title-icon floatl mt2"></div>
						<div class="floatl ">
							<span class="ms-12"><?php echo $LANG['UI_NODE_CACHE_CONFIG']; ?></span>
						</div>
					</div>
					<!-- 客户端文件缓存 -->
					<div class="strategy-group__form  agentfilecachediv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_CLIENT_FILE_CACHE']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="agentFileCachePath">
							</div>
						</div>
					</div>
					<!-- 客户端文件缓存大小 -->
					<div class="strategy-group__form  agentfilecachesizediv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_VOL_CDP_CLIENT_FILE_CACHE_SIZE']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="agentFileCacheSize">
							</div>
						</div>
					</div>
					<!-- 内存缓存 -->
					<div class="strategy-group__form  memorycacheswitchdiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_MEMORY_CACHE']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="memoryCacheSwitchFlag">
							</div>
						</div>
					</div>
					<!-- 内存缓存大小 -->
					<div class="strategy-group__form  display-none memorycachesizediv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_MEMORY_CACHE_SIZE']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="memoryCacheSize">
							</div>
						</div>
					</div>
					<!-- 资源检测 -->
					<div class="row static-info resourcedetectiondiv">
						<div class="advanced-conf-title-icon floatl mt2"></div>
						<div class="floatl ">
							<span class="ms-12"><?php echo $LANG['UI_CM_CDP_BACKUP_RESOURCES_MONITOR_CONF'];?></span>
						</div>
					</div>
					<!-- 停止任务阈值配置 -->
					<div class="strategy-group__form stoptaskthresholddiv windows_cache_config">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_BACKUP_STOP_TASK_THRESHOLD']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="stopTaskThreshold">
							</div>
						</div>
					</div>
                    <!-- 停止任务触发条件 -->
                    <div class="strategy-group__form linux_cache_config">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_CM_CDP_START_STOP_CONDITION']; ?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value" id="stop_task_condition_list">
                            </div>
                        </div>
                    </div>
					<!-- 持续数据保护降级 -->
					<div class="strategy-group__form cmcdpdemotionswitchdiv">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_DEMOTION_CONF']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="cmCdpDemotionSwitch">
							</div>
						</div>
					</div>
					<!-- 持续数据保护降级阈值 -->
					<div class="strategy-group__form cmcdpdemotionthresholddiv windows_cache_config">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_DEMOTION_CONF_VALUE']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="cmCdpDemotionThreshold">
							</div>
						</div>
					</div>
					<!-- 恢复持续数据保护监控间隔 -->
					<div class="strategy-group__form recoverycdpintervaldiv windows_cache_config">
						<div class="strategy-group__form__item align-items-baseline">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_CM_CDP_RESTORE_CDP_PROTECTION_INTERVAL']; ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8 value" id="recovery_cdp_interval_str">
							</div>
						</div>
					</div>
                    <!-- 任务暂停持续数据保护条件 -->
                    <div class="strategy-group__form linux_cache_config parse_task_condition_list">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_CM_CDP_PARSE_CONDITION']; ?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value" id="parse_task_condition_list">
                            </div>
                        </div>
                    </div>
                    <!-- 任务恢复持续数据保护条件 -->
                    <div class="strategy-group__form linux_cache_config recovery_task_condition_list">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_CM_CDP_RECOVERY_CONDITION']; ?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value" id="recovery_task_condition_list">
                            </div>
                        </div>
                    </div>
                    <!-- 检测时间设置 开关 -->
                    <div class="strategy-group__form linux_cache_config stop_task_condition_times_label">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_CM_CDP_STOP_TIME_SET']; ?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value" id="stop_task_condition_times_label">
                            </div>
                        </div>
                    </div>
                    <!-- 检测时间设置 -->
                    <div class="strategy-group__form linux_cache_config stop_task_condition_times_list">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD_CONFIGURED']; ?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value" id="stop_task_condition_times_list">
                            </div>
                        </div>
                    </div>

					<!-- 过载保护策略 -->
					<div class="overloadprotectdiv display-none">
						<div class="row static-info ">
							<div class="advanced-conf-title-icon floatl mt2"></div>
							<div class="floatl ">
								<span class="ms-12"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'];?></span>
							</div>
						</div>

						<!-- 过载保护开关 -->
						<div class="strategy-group__form ">
							<div class="strategy-group__form__item align-items-baseline">
								<div class="strategy-group__form__item__label col-md-4 name">
									<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE']; ?>:
								</div>
								<div class="strategy-group__form__item__value col-md-8 value" id="resourceLimitIgnore">
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- 恢复高级配置 -->
				<div class = "isrecovery_advanced_conf">
					<!-- 恢复脚本 -->
                    <div class="row static-info restorescriptdiv">
                        <div class="advanced-conf-title-icon floatl mt2"></div>
                        <div class="floatl ">
                            <span class="ms-12"><?php echo $LANG['UI_CM_CDP_SCRIPT_CONFIGURE'];?></span>
                        </div>
                    </div>
					<!-- 恢复后执行脚本 -->
                    <div class="strategy-group__form">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_COMPLETE_MACHINE_VOL_CDP_RECOVERY_SAFE_CONFIG_SCRIPT']; ?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value scriptafterrecovery" id="">
                            </div>
                        </div>
                    </div>
					<!-- 系统配置div -->
                    <div class="restoresystemview">
                        <!-- 系统配置title -->
                        <div class="row static-info restoresystemconfdiv">
                            <div class="advanced-conf-title-icon floatl mt2"></div>
                            <div class="floatl">
                                <span class="ms-12"><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'];?></span>
                            </div>
                        </div>
                        <!-- 重置主机名-->
                        <div class="strategy-group__form ">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_VIRTUAL_MACHINE_RESET_HOSTNAME']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value cmrecoveryresethostname" id="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
        </div>
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES']?>
            </button>
        </div>
    </div>
</div>
<!-- drawer结束 -->

<!-- drawer开始 -->
<div class="drawer slide aws-drawer-width_en min-width620" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-2-title" aria-hidden="true" id="drawer-2">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<h4 class="drawer-title ml-15" id="drawer-2-title">
				<!--更多详请-->
				<i class="viconfont vicon-tenant-detail mr8"></i><?php echo $LANG['UI_JOB_TEMP_AGENT_CONF'];?>
				<span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
			</h4>
		</div>
		<div class="drawer-body config-detail-wrap config-detail-drawer-backup">
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-celve1 me-4"></i>
					<span class="strategy-group__header__text"><?php echo $LANG['UI_VERIFY_COMMON_SETTING'];?></span>
				</div>
				<div class="strategy-group__form">
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_VM_MACHINE_TITLE'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="vm_name" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_VERIFY_CPU_NUM'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="cpu_slot_num" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_VIRTUAL_MACHINE_CORE_NUMBER_PER_SLOT'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="cpu_slot_core_num" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_VIRTUAL_MACHINE_CPU_MODE'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="cpu_mode" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_VERIFY_MEMORY_SIZE'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="memory_mb" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
						</div>
					</div>
				</div>
			</div>
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-cipanpeizhi me-4"></i>
					<span class="strategy-group__header__text"><?php echo $LANG['UI_VIRTUAL_MACHINE_DISK_CONFIGURATION'];?></span>
				</div>
				<div class="strategy-group__form" id="disk_config">
				</div>
			</div>
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-wangluopeizhi"></i>
					<span class="strategy-group__header__text"><?php echo $LANG['UI_VOL_CDP_FAILBACK_NETWORK_CONF_TEXT'];?></span>
				</div>
				<div class="strategy-group__form" id="network_config">
				</div>
			</div>
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__title">
					<i class="viconfont vicon-a-chuanshu me-4"></i>
					<span class="group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'];?></span>
				</div>
				<div class="strategy-group__form">
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_VIRTUAL_MACHINE_SYSTEM_FIRMWARE_TYPE']?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="firmware_type" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_VIRTUAL_MACHINE_MAXIMUM_BOOT_WAITING_TIME']?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="max_boot_wait_time" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4 name">
							<?php echo $LANG['UI_VIRTUAL_MACHINE_RESET_HOSTNAME']?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8 value" id="host_reset_name" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- drawer结束 -->

<!-- drawer开始 -->
<div class="drawer slide aws-drawer-width_en min-width620" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-3-title" aria-hidden="true" id="drawer-3">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title ml-15" id="drawer-3-title">
                    <!--自动接管配置-->
                    <i class="viconfont vicon-jieguan mr8"></i><?php echo $LANG['UI_CM_CDP_AUTO_TAKEOVER_CONF_TITLE'];?>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
                <div class="config-detail-wrap__group strategy-group">
                    <div class="strategy-group__header">
                        <i class="viconfont vicon-celve1 me-4"></i>
                        <span class="strategy-group__header__text"><?php echo $LANG['UI_CM_CDP_COMMON_SETTING'];?></span>
                    </div>
                    <div class="strategy-group__form">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME'] ?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value" id="heartbeatFailureTime" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="config-detail-wrap__group strategy-group appConfigDiv">
                    <div class="strategy-group__header">
                        <i class="viconfont vicon-ge_configuration me-4"></i>
                        <span class="strategy-group__header__text"><?php echo $LANG['UI_CM_CDP_APP_SETTING'];?></span>
                    </div>
                    <div class="strategy-group__form">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_CLIENT_APPLICATION_CONFIG']?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value" id="volCdpTaskAppMonitor" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline appconsefailurenumdiv">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_VOL_CDP_FAULT_TIMES']?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value" id="appConseFailureNum" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline appfaultdetectioninterdiv">
                            <div class="strategy-group__form__item__label col-md-4 name">
                                <?php echo $LANG['UI_VOL_CDP_FAULT_INTERVA']?>:
                            </div>
                            <div class="strategy-group__form__item__value col-md-8 value" id="appFaultDetectionInter" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- drawer结束 -->
 
<!-- BEGIN SCRIPT CONTENT DRAWER -->
<div class="drawer slide min-width620" data-placement="right" tabindex="-1" role="dialog" id="scriptContentDrawer">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<h4 class="drawer-title  ml-15">
				<span>
					<i class="viconfont vicon-jiaobenguanli mr8 ml15"></i>
				</span>
				<?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE_DETAIL'];?>
				<span  aria-label="Close" class="drawer-close" id = "closeScriptContentDrawer"><i class="viconfont vicon-guanbi"></i></span>
			</h4>
		</div>
	

		<div class="drawer-body">
			<div class="portlet-body" style="height: 100%">
				<div class="form-group" style="height: 20px; margin-bottom: 16px">
					<label class="col-md-3" for=""><?php echo $LANG['WEB_SCRIPT_NAME'] ?> :</label>
					<div class="col-md-6">
						<div id="scriptName"></div>
					</div>
				</div>

				<div class="form-group" style="height: 20px; margin-bottom: 16px">
					<label class="col-md-3" for=""><?php echo $LANG['UI_PUBLIC_SCRIPT_TYPE'] ?> :</label>
					<div class="col-md-6">
						<div id="scriptContentType"></div>
					</div>
				</div>

				<div class="form-group scriptexecintervalview" style="height: 20px; margin-bottom: 16px;">
					<label class="col-md-3" for=""><?php echo $LANG['UI_VOL_CDP_EXECUTION_INTERVAL'] ?>(<?php echo $LANG['UI_DRILLS_SECOND']; ?> ) :</label>
					<div class="col-md-6">
						<div id="scriptExecInterval"></div>
					</div>
				</div>
				
				<div class="form-group triggerfailnumview" style="height: 20px; margin-bottom: 16px">
					<label class="col-md-3" for=""><?php echo $LANG['UI_VOL_CDP_TOTAL_FAILURE_TIMES'] ?> :</label>
					<div class="col-md-6">
						<div id="triggerFailNum"></div>
					</div>
				</div>

				<div class="col-md-12 pd0" style="height: calc(100% - 80px)">
					<pre id="scriptContent" style="height: 100%"></pre>
				</div>
			</div>
		</div>
		<!-- footer -->
		<div class="drawer-footer">
			<button type="button" data-dismiss=""  class="btn btn-primary" id="closeScriptDrawer"><?php echo $LANG['UI_PUBLIC_YES']?></button>
		</div>
	</div>
</div>
<!-- END SCRIPT CONTENT DRAWER -->

<!-- drawer开始 -->
<div class="drawer slide aws-drawer-width_en min-width620" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-4-title" aria-hidden="true" id="failbackConfigView">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title ml-15" id="drawer-4-title">
                <!--更多详请-->
                <i class="viconfont vicon-tenant-detail mr8"></i><?php echo $LANG['UI_CM_CDP_TASK_DETAIL_MORE_TEXT']; ?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
            <div class="mt20 strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-celve1 me-4"></i>
                    <span class="strategy-group__header__text">
                        <?php echo $LANG['UI_VERIFY_COMMON_SETTING'];?>
                    </span>
                </div>
                <div class="strategy-group__form">
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4 name">
                            <?php echo $LANG['UI_JOB_TARGET_HOST'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8 value" id="failback_target_host" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                        </div>
                    </div>
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4 name">
                            <?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8 value" id="failback_backup_set" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                        </div>
                    </div>
                    <div class="strategy-group__form__item align-items-baseline failback_target_storage_view">
                        <div class="strategy-group__form__item__label col-md-4 name">
                            <?php echo $LANG['UI_BACKUP_TARGET_STORAGE'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8 value" id="failback_target_storage" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                        </div>
                    </div>
                    <hr class="details_more_hr">
                    <div class="details_more_box">
                        <div class="details_more_head">
                            <div class="row">
                                <i class="viconfont vicon-a-chuanshu mr8"></i>
                                <span><?php echo $LANG['UI_VOL_CDP_FAILBACK_TRANSFER_CONF_TEXT']?></span>
                            </div>
                        </div>
                    </div>
                    <div class="failback_transfer_config_view">
                        <div class="strategy-group__form">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_encrypt_transfer_switch" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                                </div>
                            </div>
                        </div>
                        <div class="strategy-group__form">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_DB_MYSQL_SRC_COMPRESSED'] ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_tran_compress_switch" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                                    </div>
                                </div>
                        </div>
                        <div class="strategy-group__form failback_compress_grade_view display-none">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_compress_grade" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                                </div>
                            </div>
                        </div>
                        <div class="strategy-group__form">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS'] ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_transfer_thread_number" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                                </div>
                            </div>
                        </div>
                        <div class="strategy-group__form">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE'] ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_transfer_datapackage_size" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="details_more_hr">
                    <div class="details_more_box">
                        <div class="details_more_head">
                            <div class="row">
                                <i class="viconfont vicon-wangluopeizhi1 mr8"></i>
                                <span><?php echo $LANG['UI_PLATFORM_NETWORK_SETTING'];?></span>
                            </div>
                        </div>
                    </div>
                    <div class="failback_network_config_view">
                        <div class="strategy-group__form">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_DB_CDP_CREATE_EDIT_NETWORK_TAKEOVER'] ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_network_config">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="details_more_hr">
                    <div class="details_more_box">
                        <div class="details_more_head">
                            <div class="row">
                                <i class="viconfont vicon-a-Group1321315332 mr8"></i>
                                <span><?php echo $LANG['UI_CM_CDP_RESOURCES_MONITOR_CONF']?></span>
                            </div>
                        </div>
                    </div>
                    <div class="failback_resource_monitor_config_view">
                        <div class = "taskdetailstoragepolicydiv">
                            <div class="row static-info ">
                                <div class="advanced-conf-title-icon floatl mt2"></div>
                                <div class="floatl ">
                                    <!-- 存储策略 -->
                                    <span class="ms-12"><?php echo $LANG['UI_CM_CDP_RESOURCES_MONITOR_CONF']; ?></span>
                                </div>
                            </div>
                            <!-- 停止任务阈值配置 -->
                            <div class="strategy-group__form stoptaskthresholddiv failback_windows_cache_config">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_CM_CDP_BACKUP_STOP_TASK_THRESHOLD']; ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_stop_task_threshold">
                                    </div>
                                </div>
                            </div>
                            <!-- 停止任务触发条件 -->
                            <div class="strategy-group__form failback_linux_cache_config">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_CM_CDP_START_STOP_CONDITION']; ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_stop_task_condition_list">
                                    </div>
                                </div>
                            </div>
                            <!-- 持续数据保护降级 -->
                            <div class="strategy-group__form cmcdpdemotionswitchdiv">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_CM_CDP_DEMOTION_CONF']; ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_cmcdp_demotion_switch">
                                    </div>
                                </div>
                            </div>
                            <!-- 持续数据保护降级阈值 -->
                            <div class="strategy-group__form failback_cmcdpdemotionthreshold_view failback_windows_cache_config">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_CM_CDP_DEMOTION_CONF_VALUE']; ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_cmcdp_demotion_threshold">
                                    </div>
                                </div>
                            </div>
                            <!-- 恢复持续数据保护监控间隔 -->
                            <div class="strategy-group__form failback_recoverycdpinterval_view failback_windows_cache_config">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_CM_CDP_RESTORE_CDP_PROTECTION_INTERVAL']; ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_recovery_cdpinterval_value">
                                    </div>
                                </div>
                            </div>
                            <!-- 任务暂停持续数据保护条件 -->
                            <div class="strategy-group__form failback_linux_cache_config failback_parse_task_condition_list_view">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_CM_CDP_PARSE_CONDITION']; ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_parse_task_condition_list">
                                    </div>
                                </div>
                            </div>
                            <!-- 任务恢复持续数据保护条件 -->
                            <div class="strategy-group__form failback_linux_cache_config failback_recovery_task_condition_list_view">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_CM_CDP_RECOVERY_CONDITION']; ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_recovery_task_condition_list">
                                    </div>
                                </div>
                            </div>
                            <!-- 检测时间设置 开关 -->
                            <div class="strategy-group__form failback_linux_cache_config failback_stop_task_condition_times_label_view">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_CM_CDP_STOP_TIME_SET']; ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_stop_task_condition_times_label">
                                    </div>
                                </div>
                            </div>
                            <!-- 检测时间设置 -->
                            <div class="strategy-group__form failback_linux_cache_config failback_stop_task_condition_times_list_view">
                                <div class="strategy-group__form__item align-items-baseline">
                                    <div class="strategy-group__form__item__label col-md-4 name">
                                        <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD_CONFIGURED']; ?>:
                                    </div>
                                    <div class="strategy-group__form__item__value col-md-8 value" id="failback_stop_task_condition_times_list">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="details_more_hr">
                    <div class="details_more_box">
                        <div class="details_more_head">
                            <div class="row">
                                <i class="viconfont vicon-gaojipeizhi mr8"></i>
                                <span><?php echo $LANG['UI_VOL_CDP_FAILBACK_ADVANCED_CONF_TEXT'];?></span>
                            </div>
                        </div>
                    </div>
                    <div class="failback_high_conf_view">
                        <div class="strategy-group__form storedatabasesizediv">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_VOL_CDP_MEMORY_CACHE']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value " id="failback_memory_cache">
                                </div>
                            </div>
                        </div>
                        <div class="strategy-group__form failback_memory_cache_size_view">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_VOL_CDP_CLIENT_MEMORY_CACHE_SIZE']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_memory_cache_size">
                                </div>
                            </div>
                        </div>
                        <div class="strategy-group__form">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_JOB_FILE_CACHE_PATH']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_file_cache_path">
                                </div>
                            </div>
                        </div>
                        <div class="strategy-group__form">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_JOB_FILE_CACHE_SIZE']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_file_cache_size">
                                </div>
                            </div>
                        </div>
                        <div class="strategy-group__form  automaticfaultrecoverydiv">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_CM_CDP_IO_REPLICATION_MODE'] ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_io_replication_mode">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="details_more_hr">
                    <div class="details_more_box">
                        <div class="details_more_head">
                            <div class="row">
                                <i class="viconfont vicon-a-chuanshu mr8"></i>
                                <span><?php echo $LANG['UI_VOL_CDP_MAP_RELATION'];?></span>
                            </div>
                        </div>
                    </div>
                    <div class="failback_target_disk_conf_view">
                        <div class="strategy-group__form ">
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_CM_CDP_FAILBACK_TARGET_DISK_CONF']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8 value" id="failback_target_disk_config">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
        </div>
    </div>
</div>
<!-- drawer结束 -->

<script type="text/javascript" src="./assets/global/plugins/select2/select2-4.1.0/js/select2.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/targetPlug.js"></script>

<?php include_once './cm_cdp_failback_task_conf.php'; ?>

<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>

<script type="text/javascript" src="./scripts/complete_machine_volcdp/cm_cdp_job_details.js"></script>
<script type="text/javascript" src="./scripts/complete_machine_volcdp/cm_cdp_job_details_time_strategy_parse.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/complete_machine_volcdp/cm_cdp_job_details_operation.js"></script>





