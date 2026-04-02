<?php
include_once '../../tpl/permission.php';
$authfun = $_SESSION['authfun'];
$authfun['fileArchiveMode'] = false; //屏蔽nas归档
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
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
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail">
	<div class="col-md-12 job-detail__halftop">
		<div class="col-md-8 job-detail__halftop__charts">
			<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
			<input id="task_type" value="<?php echo $_GET['type']; ?>" class="display-none"></input>
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
						<!-- echart图区域 -->
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
							<div id="total-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
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
						<div class="portlet-title init_tab_title">
							<i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
						</div>
						<div class="tab-content">
							<div class="tab-pane active">
								<div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_PUBLIC_OPERATION'] ?>:
										</div>
										<div class="col-md-8 col-operate value">
										<div class="btn-group  vmStartDiv dropdown-wrapper">
												<button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle"
														data-toggle="dropdown" data-hover="dropdown" data-delay="1000"
														data-close-others="true">
													<?php echo $LANG['UI_PUBLIC_OPERATION'] ?>
													<i class="fa fa-angle-down"></i>
												</button>
												<ul class="dropdown-menu" role="menu" id="nasOpList">
													<li class="startFull"><button class="btn dropdown-menu__item me-0" type="button"><i
																	class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?>
														</button></li>
													<li class="startIncr"><button class="btn dropdown-menu__item me-0" type="button"><i
																	class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?>
														</button></li>
													<li class="startDiff"><button class="btn dropdown-menu__item me-0" type="button">
														<i class="viconfont vicon-ge_differentia_backup"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?>
													</button></li>
													<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i
																	class="viconfont vicon-ge_suspend-copy me-4"></i><?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?>
														</button></li>
												</ul>
											</div>
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_TASK_REPORT_TASK_NAME'] ?>:
										</div>
										<div class="col-md-8 value" id="taskName" style="word-break:break-word;">
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
											<?php echo $LANG['UI_JOB_TASK_STAGE'] ?>:
										</div>
										<div class="col-md-8 value" id="task_stage">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_FILE_JOB_ACQUIRED_CAPACITY'] ?>:
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
									<div class="row static-info flex-items-center">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_MORE'] ?>:
										</div>
										<div class="col-md-8 value">
											<a id="details_more" class="green-haze" data-toggle="drawer" data-target="#nas_more_detail_drawer" href="javascript:void(0)" style="color: #0FBF98;">
												<?php echo $LANG['BILLING_VIEW_DETAILS'] ?>					   
											</a>
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
					<li class="active">
						<a href="#log" data-toggle="tab">
							<i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
					</li>
					<li>
						<a href="#nas" data-toggle="tab" id="srcList">
						</a>
					</li>
					<li>
						<a href="#history" data-toggle="tab">
							<i class="viconfont vicon-histroytask"></i> <?php echo $LANG['UI_JOB_HISTORY'] ?> </a>
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

					<div class="tab-pane overflow-y-auto" id="nas">
						<div class="table-container">
							<table class="table table-striped table-bordered table-hover" id="nastable">
								<thead>
									<tr role="row" class="heading">
										<th width="2%">
											<input type="checkbox" class="group-checkable">
										</th>
										<th width="3%">
											<?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
										</th>
										<th width="18%">
											<?php echo $LANG['UI_FILE_DETAIL_SRC_NAME'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_BACKUP_FILE_AND_DIR_IN_TOTAL'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_BACKUP_FILE_COMPLETE_NUM'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_BACKUP_FILE_DIR_NUM'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_PUBLIC_SCRIPT_RESULT'] ?>
										</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
							<div class="alert alert-block alert-info fade in display-hide" id="vmbackuptips">
								<button type="button" class="close" data-dismiss="alert"></button>
								<ul class="alert-ul">
									<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
									<li>
										<?php echo $LANG['UI_BACKUP_SELECT_VM_BACKUP_TIPS'] ?>
									</li>
								</ul>
							</div>

						</div>
					</div>

					<div class="tab-pane tab-pane-history" id="history">
						<div class="table-container">
							<table class="table table-striped table-bordered table-hover" id="historytable">
								<thead>
									<tr role="row" class="heading">
										<th width="2%">
											<?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?>
										</th>
										<th width="8%">
											<?php echo $LANG['UI_TENANT_DELETE_RESULT'] ?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_JOB_TOTAL_SIZE'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_JOB_TRANSFER_SIZE'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_JOB_REAL_SIZE'] ?>
										</th>
										<th width="19%">
											<?php echo $LANG['UI_JOB_START_TIME'] ?>
										</th>
										<th width="19%">
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
	</div>
	<!-- 跳过文件详情模态框开始 -->
	<div id="passFileModal" style="z-index: 100000;top: 400px;" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		<div class="modal-header ">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			<h4 class="modal-title"><i class="viconfont icon-v-xnjbf" style="color: gray;margin-right: 8px;"></i><?php echo $LANG['UI_VERIFY_CHECK_DETAILS'] ?>
			</h4>
		</div>
		<div class="modal-body">
			<div class="portlet-body">
				<div class="btn-group">
					<button type="button" id="downloadTxt" class="btn btn-sm green-haze">
					<i class="fa fa-download"></i><?php echo $LANG['UI_FILE_DOWNLOAD_SKIP_FILE'] ?>
					</button>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3 pl0" style="text-align: left;"><?php echo $LANG['UI_FILE_SKIP_FILE_DETAILS'] ?>:</label>
					<div class="col-md-12 pl0">
						<table class="table table-striped table-bordered" style="margin-bottom: 0;">
							<thead style="background-color: #dcdcdca1;">
								<th><?php echo $LANG['UI_FILE_SKIP_FILE_NUM'] ?></th>
								<th><?php echo $LANG['UI_FILE_SKIP_FILE_RATIO'] ?></th>
								<th><?php echo $LANG['UI_FILE_SKIP_DIRECTORY_NUM'] ?></th>
								<th><?php echo $LANG['UI_FILE_SKIP_DIRECTORY_RATIO'] ?></th>
								<th><?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_NUM'] ?></th>
								<th><?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_RATIO'] ?></th>
							</thead>
							<tbody>
								<tr class="passdetailTr">
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3 pl0" style="text-align: left;"><?php echo $LANG['UI_FILE_SKIP_REASON_DETAILS'] ?>:</label>
					<div class="col-md-12 passreasonTable pl0">
					<table class="table table-striped table-bordered">
							<thead style="background-color: #dcdcdca1;">
								<th><?php echo $LANG['UI_FILE_OCCUPIED_FILE_NUM'] ?></th>
								<th><?php echo $LANG['UI_FILE_DELETE_FILE_NUM'] ?></th>
								<th><?php echo $LANG['UI_FILE_NO_PERMISSION_FILE_NUM'] ?></th>
								<th><?php echo $LANG['UI_FILE_NO_PERMISSION_DIRECTORY_NUM'] ?></th>
								<th><?php echo $LANG['UI_FILE_DELETE_DIRECTORY_NUM'] ?></th>
								<th><?php echo $LANG['UI_FILE_OTHER_NUM'] ?></th>
							</thead>
							<tbody>
								<tr class="passreasonTr">
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
			<!-- <button type="button"class="btn btn-primary" id="mountsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button> -->
		</div>
	</div>
	<!-- 跳过文件详情模态框结束 -->
</div>
<!-- END PAGE CONTENT-->
<!-- drawer开始 -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px" aria-labelledby="obs_detail_drawer_title" aria-hidden="true" id="nas_more_detail_drawer">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<div class="drawer-title" id="nas_more_detail_drawer_title"  style="display:flex;justify-content:space-between;align-items:center">
				<div class="drawer-title-left">
					<i class="viconfont vicon-tenant-detail mr10"></i>
					<span><?php echo $LANG['UI_MORE_DETAIL'] ?></span>
				</div>
				<div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
					<i class="viconfont vicon-guanbi"></i>
				</div>
			</div>
		</div>
		<div class="drawer-body config-detail-wrap config-detail-drawer-backup">
			<!-- 通用策略 -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-celve1 me-4"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
				</div>
				<!-- START TIME STRATEGY -->
				<div class="strategy-group__title">
					<span class="decoration me-8"></span>
					<span class="strategy-group__title__text"><?php echo $LANG['WEB_COMMON_TIME_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form">
					<!-- 创建/修改时间 -->
					<div class="strategy-group__form__item is-backup-static-info">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_CREATE_TIME'] ?>
						</div>
						<div id="createTime" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 下次开始时间 -->
					<div class="strategy-group__form__item is-backup-static-info">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?>
						</div>
						<div id="nextTime" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 备份方式 -->
					<div class="strategy-group__form__item is-backup-static-info">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_TYPE'] ?>
						</div>
						<div id="timeStrategyBackupType" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 完全备份 -->
					<div class="strategy-group__form__item fullDiv is-backup-static-info">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_FULL'] ?>
						</div>
						<div id="fulldes" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 增量备份 -->
					<div class="strategy-group__form__item incrDiv is-backup-static-info">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_INCREMENT'] ?>
						</div>
						<div id="incdes" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 差异备份 -->
					<div class="strategy-group__form__item diffDiv is-backup-static-info">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?>
						</div>
						<div id="diffdes" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 永久增量 -->
					<div class="strategy-group__form__item pincrDiv is-backup-static-info">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_PERMANENT_INCREMENT'] ?>
						</div>
						<div id="pincrdes" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					 <!-- 恢复方式 -->
					<div class="strategy-group__form__item is-recovery-static-info">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_RECOVERY_TYPE'] ?>
						</div>
						<div id="recovery_type" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 定时恢复时间 -->
					<div class="strategy-group__form__item is-recovery-static-info start-time-form">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_RECOVERY_TYPE_TIMING_TIME'] ?>
						</div>
						<div id="start_time" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- END TIME STRATEGY -->
				<!-- BEGIN SPEED LIMIT STRATEGY -->
				<div class="strategy-group__title">
					<span class="decoration me-4"></span><span
					class="strategy-group__title__text"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
				</div>
				<div class="strategy-group__form">
					<!-- 限速策略 -->
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></div>
						<div id="speedlimit" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 任务等级 -->
					<div class="strategy-group__form__item taskPriorityDiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_TASK_PRIORITY'] ?></div>
						<div id="taskPriority" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- END SPEED LIMIT STRATEGY -->

				<!-- BEGIN STORAGE STRATEGY -->
				<div class="strategy-group__title storagemodelDiv">
					<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form storagemodelDiv">
					<!-- 存储设备 -->
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_STORAGE_DEV'] ?></div>
						<div id="storageinfo" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 备份节点 -->
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_BACKUP_NODE'] ?></div>
						<div id="nodeinfo" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 压缩存储 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?></div>
						<div id="compressed" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 压缩等级 -->
					<div class="strategy-group__form__item compressMethodDiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?></div>
						<div id="compressMethod" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 数据加密 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?></div>
						<div id="encryptStorage" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 存储加密算法 -->
					<div class="strategy-group__form__item encrypt-method-div" style="display: none;">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
						<div id="encryptMethod" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 自动生成密码 -->
					<div class="strategy-group__form__item passwordAutodiv mb-20" style="display: none;">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?></div>
						<div id="passwordAuto" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- END STORAGE STRATEGY -->

				<!-- BEGIN RESERVE STRATEGY -->
				<div class="reserve-div reservedDiv">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span
							class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 保留策略 -->
						<div class="strategy-group__form__item reserve-strategy-form-item">
							<div class="strategy-group__form__item__label col-md-4 name">
								<?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></div>
							<div id="reservedStrategy" class="strategy-group__form__item__value col-md-8 value"></div>
						</div>
					</div>
				</div>
				<!-- END RESERVE STRATEGY -->
			</div>
			<!-- BEGIN TRANSMIT STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group threadDiv">
				<div class="strategy-group__header">
					<i class="viconfont vicon-a-chuanshu me-4"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
				</div>
				<!-- 扫描线程 -->
				<div class="strategy-group__form is-backup-static-info">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_FILE_BAK_SCAN_THREAD'] ?></div>
						<div id="scanThreadNum" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				 <!-- 扫描文件速度 -->
				 <div class="strategy-group__form display-none scanFileDiv is-backup-static-info">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_FILE_BAK_SCAN_FILE'] ?></div>
						<div id="scanFileNum" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<?php
					if ($authfun['multithread']) {
						echo '
								<div class="strategy-group__form transfer-thread-num-form">
									<!-- 传输线程 -->
									<div class="strategy-group__form__item">
										<div class="strategy-group__form__item__label col-md-4">
											' . $LANG['UI_BACKUP_THREAD_NUM'] . '
										</div>
										<div id="threadNum" class="strategy-group__form__item__value col-md-8">
										</div>
									</div>
								</div>
							';
					}
				?>
			</div>
			<!-- END TRANSMIT STRATEGY GROUP -->
			<!-- BEGIN SAFE STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group safemodeDiv safeDiv">
				<div class="strategy-group__header">
					<i class="viconfont vicon-anquancelve me-4"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form backupSafeModeDiv display-none">
					<!-- WORM防护 -->
					<div class="strategy-group__form__item worm-form-item">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT'] ?></div>
						<div id="backup_worm_flag" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- WORM保护期限 -->
					<div class="strategy-group__form__item backup_worm_date-form-item worm-form-item">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD'] ?></div>
						<div id="backup_worm_date" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
					<!-- 完整性校验 -->
					<div class="strategy-group__form__item integrity-form-item">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK'] ?></div>
						<div id="integrity_check_flag" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
				</div>
				<!-- 完整性校验异常处理 -->
				<div class="strategy-group__form restoreSafeModeDiv display-none integrity-form-item">
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK_STRATEGY'] ?></div>
						<div id="integrity_policy" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
			</div>
			<!-- END SAFE STRATEGY GROUP -->
			<!-- BEGIN ADVANCED CONFIG GROUP -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-gaojipeizhi me-4"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></span>
				</div>
				<!--BEGIN WILDCARD GROUP -->
				<div class="wildcardmodeDiv">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_FILE_WILDCARD'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 通配符 -->
						<div class="strategy-group__form__item wildcard-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_BACKUP_FILE_WILDCARD_WAYS'] ?></div>
							<div id="wildcardmode" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!--END WILDCARD GROUP -->
				 <!-- BEGIN SNAPSHOT GROUP -->
				 <div class="is-backup-static-info snapshot-div">
                    <div class="strategy-group__title">
                        <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- 快照 -->
                        <div class="strategy-group__form__item silentsnapshotcheck-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></div>
                            <div id="silentsnapshotcheck" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
                <!-- END SNAPSHOT GROUP -->
				<!-- BEGIN PERMISSION GROUP -->
				<div class="permissionDiv">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_ROLE_PERMISSION'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 权限 -->
						<div class="strategy-group__form__item permission-form-item is-backup-static-info">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_PERMISSION_BACKUP'] ?></div>
							<div id="file_permission" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 文件权限恢复 -->
						<div class="strategy-group__form__item permission-recovery-form-item is-recovery-static-info">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_RECOVERY_PERMISSION'] ?></div>
							<div id="file_permission_recovery" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END PERMISSION GROUP -->
				<!-- BEGIN ABNORMAL GROUP -->
				<div class="">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_EXCEPTION_HANDLE'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 跳过文件告警智能判断 -->
						<div class="strategy-group__form__item passfilealarm-form-item passalarmflagdiv" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_JUDGE'] ?></div>
							<div id="passalarmflag" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 跳过文件告警个数 -->
						<div class="strategy-group__form__item pass-des-form-item passalarmDiv" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_NUM'] ?></div>
							<div id="passalarmNum" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 跳过文件告警比例 -->
						<div class="strategy-group__form__item pass-des-form-item passalarmDiv" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_RATIO'] ?></div>
							<div id="passalarmPercent" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 跳过目录树恢复 -->
						<div class="strategy-group__form__item file-skip-dir-form-item dir-tree-div" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_RECOVERY_FILE_SKIP_DIR_TREE_RECOVERY'] ?></div>
							<div id="dir_tree" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 同名文件处理 -->
						<div class="strategy-group__form__item file-same-name-form-item same-name-div" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE'] ?></div>
							<div id="same_name" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 无效快捷方式清理 -->
						<div class="strategy-group__form__item clear-shortcut-form-item no-valid-clear-div" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_RECOVERY_FILE_INVALID_SHORTCUT_CLEAN'] ?></div>
							<div id="no_valid_clear" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END ABNORMAL GROUP -->

				<!-- BEGIN RETRY STRATEGY -->
				<div class="">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 网络重连次数 -->
						<div class="strategy-group__form__item network_retry_times_show">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?></div>
							<div id="network_retry_times" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 网络重连间隔时间 -->
						<div class="strategy-group__form__item network_retry_interval_show">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?></div>
							<div id="network_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 操作异常自动重试 -->
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?></div>
							<div id="op_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 操作异常重连次数 -->
						<div class="strategy-group__form__item op-retry-form-item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?></div>
							<div id="opRetryTime" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 操作异常重连间隔时间 -->
						<div class="strategy-group__form__item op-retry-form-item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?></div>
							<div id="opRetryInterval" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 任务自动重试 -->
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?></div>
							<div id="task_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 任务重连对象 -->
						<div class="strategy-group__form__item task-retry-form-item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?></div>
							<div id="task_retry_object" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 任务重连次数 -->
						<div class="strategy-group__form__item task-retry-form-item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?></div>
							<div id="taskRetryTime" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 任务重连间隔时间 -->
						<div class="strategy-group__form__item task-retry-form-item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?></div>
							<div id="taskRetryInterval" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END RETRY STRATEGY -->
				<!-- BEGIN OVERLOAD GROUP -->
				<div class="">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 忽略节点资源限制 -->
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></div>
							<div id="ignoreResourceLimit" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END OVERLOAD GROUP -->
			</div>
			<!-- END ADVANCED CONFIG GROUP -->
		</div>
		<div class="drawer-footer">
			<button type="button" class="btn green-haze" data-dismiss="drawer"
				aria-label="Close"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
		</div>
	</div>
</div>
<!-- drawer结束 -->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/nas/nas_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->