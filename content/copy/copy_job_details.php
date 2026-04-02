<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<?php
		if ($_GET['orcPlan']) {
			$url = './content/platform/orchestration/orchestration_details.php?uuid=' . $_GET['orcPlan'];
			echo "<a href=\"$url\" class='ajaxify' name='task'><span>" . $LANG['UI_JOB_TASK_ORCHESTRATION_DETAILS'] . "</span></a>";
		} else {
			echo '<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php"><span>' . $LANG['UI_PLATFORM_CURRENT_JOB'] . '</span></a>';
		}
		?>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS'] ?></span>
</h3>

<?php
$module_type = $_GET['module'];
$sub_module_type = $_GET['subType'];
$MODULE_TYPE = $CONF['MODULE_TYPE'];
?>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail copy-job-detail" id="jobDetail">
	<div class="col-md-12 job-detail__halftop">
		<div class="col-md-8 job-detail__halftop__charts">
			<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
			<input id="module_type" value="<?php echo $_GET['module']; ?>" class="display-none"></input>
			<input id="subType" value="<?php echo $_GET['subType']; ?>" class="display-none"></input>
			<input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
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
						<div class="progressDiv__label"><?php echo $LANG['UI_JOB_TOTAL_PROGRESS'] ?></div>
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
					<div class="portlet-title init_tab_title">
						<i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
					</div>
					<!--BEGIN TABS-->
					<!-- 详细信息-概要 存储 策略 -->
					<div class="tab-content" style="padding:16px;">
						<!-- 概要 -->
						<div class="tab-pane active" id="tab_1_1">
							<div class="portlet-body" style="overflow-y: auto">
								<!-- 按钮操作 -->
								<div class="row static-info <?php if (!in_array("global_write", $_SESSION['permission']) && in_array("global_observer", $_SESSION['permission'])) {
																echo "display-none";
															} ?>">
									<div class="col-md-4 name">
										<?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?>:
									</div>
									<div class="col-md-8 value">
										<div class="btn-group dropdown-wrapper">
											<button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
												<?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
											</button>
											<ul class="dropdown-menu" role="menu" id="jobOperationGroup">
											</ul>
										</div>
									</div>
								</div>
								<div class="row static-info">
									<div class="col-md-4 name">
										<?php echo $LANG['UI_TASK_REPORT_TASK_NAME'] ?>:
									</div>
									<div class="col-md-8 value" id="taskName" style="word-break:break-word; ">
									</div>
								</div>
								<div class="row static-info">
									<div class="col-md-4 name">
										<?php echo $LANG['UI_BACKUP_DATA_REPORT_MODULE_TYPE'] ?>:
									</div>
									<div class="col-md-8 value" id="moduleType">
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
								<!-- 更多详请 -->
								<div class="row static-info">
									<div class="col-md-4 name">
										<?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_MORE'] ?>:
									</div>
									<div class="col-md-8 value">
										<a class="colorgreen" id="details_more" data-toggle="drawer" data-target="#drawer-1"><?php echo $LANG['BILLING_VIEW_DETAILS'] ?></a>
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
							<i class="viconfont vicon-ge_running_log"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
					</li>
					<li id="copyLi">
						<a href="#copyItem" data-toggle="tab">
							<i class="viconfont vicon-pt_report_vm_report"></i>
							<span id="copyLiLabel"><?php echo $LANG['UI_VCENTER_MACHINE_LIST'] ?></span>
						</a>
					</li>
					<li id="historyLi">
						<a href="#copyHistory" data-toggle="tab">
							<i class="viconfont vicon-pt_job_historical_task"></i><?php echo $LANG['UI_JOB_HISTORY'] ?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content">
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

					<div class="tab-pane" id="copyItem" style="overflow: auto;">
						<div class="vin_toolbar" id="copy_details_toolbar">
							<div class="leftTool">
							</div>
							<div class="rightTool">
								<div class="vin_btnToolbar"></div>
							</div>
						</div>
						<div class="table-container">
							<table id="copyTable"></table>
						</div>
					</div>
					<div class="tab-pane" id="copyHistory">
						<div class="vin_toolbar" id="history_toolbar">
							<div class="leftTool">
							</div>
							<div class="rightTool">
								<div class="vin_btnToolbar"></div>
							</div>
						</div>
						<div class="table-container">
							<table id="historyTable">
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- END TAB PORTLET-->


	</div>
</div>
<div class="drawer slide aws-drawer-width_en" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<h4 class="drawer-title" id="drawer-1-title">
				<i class="viconfont vicon-tenant-detail mr8"></i><?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_DETAILS'] ?>
				<span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
			</h4>
		</div>
		<div class="drawer-body config-detail-wrap config-detail-drawer-backup">
			<!-- BEGIN COMMON STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-celve1 me-8"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__title copy-mode-title">
					<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_JOB_COPY_TYPE'] ?></span>
				</div>
				<div class="strategy-group__form mb-20 copy-mode-item">
					<!-- 副本类型 -->
					<div class="strategy-group__form__item copy-mode-div">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_JOB_COPY_TYPE'] ?></div>
						<div id="copyType" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 数据链长度 -->
					<div class="strategy-group__form__item chain-length-div  display-none">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_COPY_CHAIN_LENGTH'] ?></div>
						<div id="chainLength" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 副本策略 -->
					<div class="strategy-group__form__item custom-source-div display-none">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_COPY_STRATEGY_LABEL_CUSTOM_SOURCE'] ?>:
						</div>
						<div class="col-md-8 strategy-group__form__item__value" id="customSourceDes">
						</div>
					</div>
					<!-- 自动加入副本 -->
					<div class="strategy-group__form__item auto-join-div " style="<?php
																			if ($module_type != $MODULE_TYPE['VM']) {
																				echo 'display:none';
																			}
																			?>">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_COPY_SOURCE_LABEL_AUTO_ADD'] ?></div>
						<div id="autoJoinCopy" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 时间策略 -->
				<div class="strategy-group__title">
					<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['WEB_COMMON_TIME_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form mb-20">
					<!-- 任务创建时间 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_JOB_CREATE_TIME'] ?></div>
						<div id="createTime" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 下次运行时间 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?></div>
						<div class="strategy-group__form__item__value col-md-8">
							<span class="label label-success" id="nextTime"></span>
						</div>
					</div>
					<!-- 副本策略 -->
					<div class="strategy-group__form__item time-strategy-div">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_COPY_STRATEGY'] ?></div>
						<div id="timeDes" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 限速策略 -->
				<div class="strategy-group__title">
					<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['WEB_COMMON_SPEED_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form mb-20">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></div>
						<div id="speedLimit" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 存储策略 -->
				<div class="strategy-group__title">
					<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form mb-20">
					<!-- 存储资源池 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STORAGE_POOL'] ?></div>
						<div id="storagePool" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 存储设备 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_JOB_STORAGE_DEV'] ?></div>
						<div id="storageInfo" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 计算资源池 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_PLATFORM_NODE_POOL'] ?></div>
						<div id="nodePool" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 计算节点 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_COPY_TARGET_NODE'] ?></div>
						<div id="nodeInfo" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 保留策略 -->
				<div class="strategy-group__title reserved_strategy_title">
					<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form mb-20 reserved_strategy_div">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></div>
						<div id="reservedStrategy" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
			</div>
			<!-- END COMMON STRATEGY GROUP -->

			<!-- BEGIN TRANSPORT STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-celve1 me-8"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
				</div>
				<!-- 传输加密 -->
				<div class="strategy-group__form mb-20">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></div>
						<div id="transportEncrypt" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 传输数据压缩 -->
				<div class="strategy-group__form mb-20 strategy-group__form-compress">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_COPY_COMPRESS_TRANSFER'] ?></div>
						<div id="transportCompress" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 传输线程 -->
				<div class="strategy-group__form mb-20 threadCountDiv">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?></div>
						<div id="threadCount" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 网络资源池 -->
				<div class="strategy-group__form mb-20">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_NODE_NETWORK_POOL'] ?></div>
						<div id="networkPool" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 传输网络 -->
				<div class="strategy-group__form mb-20">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></div>
						<div id="transferNetwork" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 对比增量 -->
				<div class="strategy-group__form mb-20 hashIncDiv">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_COPY_TRANSFER_INCREASE'] ?></div>
						<div id="hashIncFlag" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
			</div>
			<!-- END TRANSPORT STRATEGY GROUP -->
			<!-- BEGIN SAFE STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group safe-strategy-group <?php if ($CONF['SYSTEM_INFO']['vendor'] == "sangfor") {
																							echo 'display-none';
																						} ?>">
				<div class="strategy-group__header">
					<i class="viconfont vicon-anquancelve me-8"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form backupSafeModeDiv">
					<!-- WORM防护 -->
					<div class="strategy-group__form__item  worm-form-item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT'] ?></div>
						<div id="backup_worm_flag" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- WORM保护期限 -->
					<div class="strategy-group__form__item backup_worm_date-form-item  worm-form-item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD'] ?></div>
						<div id="backup_worm_date" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
				</div>
			</div>
			<!-- END SAFE STRATEGY GROUP -->
			<!-- BEGIN HIGH STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-celve1 me-8"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_USER_HIGH_SETTING'] ?></span>
				</div>
				<div class="strategy-group__title">
					<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
				</div>
				<!-- 网络重试次数 -->
				<div class="strategy-group__form mb-20">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?></div>
						<div id="network_retry_times" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 网络重试间隔 -->
				<div class="strategy-group__form mb-20">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?></div>
						<div id="network_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 操作异常自动重试 -->
				<div class="strategy-group__form mb-20">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?></div>
						<div id="op_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 操作异常重连次数 -->
				<div class="strategy-group__form mb-20 opRetryTimesDiv">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?></div>
						<div id="op_retry_times" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 操作异常重连时间间隔 -->
				<div class="strategy-group__form mb-20 opRetryIntervalDiv">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?></div>
						<div id="op_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 任务自动重试 -->
				<div class="strategy-group__form mb-20">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?></div>
						<div id="task_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 任务重连对象 -->
				<div class="strategy-group__form mb-20 taskRetryObjectDiv">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?></div>
						<div id="task_retry_object" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 任务重连次数 -->
				<div class="strategy-group__form mb-20 taskRetryTimesDiv">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?></div>
						<div id="task_retry_times" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 任务重连间隔时间 -->
				<div class="strategy-group__form mb-20 taskRetryIntervalDiv">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?></div>
						<div id="task_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 过载保护 -->
				<div class="strategy-group__title">
					<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></span>
				</div>
				<!-- 忽略节点资源限制 -->
				<div class="strategy-group__form mb-20">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></div>
						<div id="ignoreResourceLimit" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- 存储 -->
				<div class="strategy-group__title merge-mode-div">
					<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_PALTFORM_STORAGE'] ?></span>
				</div>
				<!-- 合并模式 -->
				<div class="strategy-group__form mb-20 merge-mode-div display-none">
					<div class="strategy-group__form__item passfilealarm-form-item dataContainerSizeDiv display-none">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_MACHINE_OS_DATA_CONTAINER_SIZE'] ?></div>
						<div id="data_container_size" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<div class="strategy-group__form__item redundantDataProportionDiv">
						<div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_MERGE_REDUNDANT_DATA_PROPORTION'] ?></div>
						<div id="redundantDataProportion" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
			</div>
			<!-- END HIGH STRATEGY GROUP -->
			<!-- END PROTECT STRATEGY GROUP -->
		</div>
		<div class="drawer-footer">
			<button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			<button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/speed_chart.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/copy/copy_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->