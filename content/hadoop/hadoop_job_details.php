<?php
include_once '../../tpl/permission.php';
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
$authfun = $_SESSION['authfun'];
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
<div class="row job-detail" id="hadoop_job_details">
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
					<!-- 多集群时进度条出显示的信息 -->
					<div class="multiProgressDiv display-none">
						<div class="multiProgressDiv__label">
							<span>
								<?php echo $LANG['UI_HADOOP_JOB_BACKUP_CLUSTER'] ?>
							</span>
						</div>
						<div class="agentName">--</div>
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
											<div class="btn-group dropdown-wrapper vmStartDiv">
												<button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
													 <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
												</button>
												<ul class="dropdown-menu" role="menu" id="fsOpList">
													<li class="startFull"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></button></li>
													<li class="startIncr"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></button></li>
													<li class="startDiff"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></button></li>
													<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i><?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?></button></li>
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
											<!-- <button type="button" class="btn btn-sm green-haze"
												data-target="#hdfs_more_detail_drawer" data-toggle="drawer">
												<i class="viconfont vicon-tenant-detail"></i>
												<?php echo $LANG['BILLING_VIEW_DETAILS'] ?> </button> -->

												<a id="details_more" class="green-haze" data-toggle="drawer"
													data-target="#hdfs_more_detail_drawer" href="javascript:void(0)" style="color: #0FBF98;">
													<?php echo $LANG['UI_VERIFY_CHECK_DETAILS'] ?>
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
					<li class="active ">
						<a href="#log" data-toggle="tab">
							<i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
					</li>
					<li>
						<a href="#hadoop" data-toggle="tab">
							<i class="viconfont vicon-ge_backup_host "></i><span id="list_title"></span> </a>
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

					<div class="tab-pane" id="hadoop">
						<div class="table-toolbar">
							<div class="vin_toolbar display-none" id="vin_hadoop_detail_toolbar">
								<div class="leftTool">
										<div class="search input-group mr12">
											<input type="search" maxlength="128" id="searchVal" class="searchinput customSearch" autocomplete="off"
												style="padding-right:32px;min-width: 220px;" maxlength="64" type="text"
												placeholder="<?php echo $LANG['WEB_HADOOP_SEARCH_BY_CLUSTER_NAME'] ?>">
											<div class="position0" style="width:auto;height:34px">
												<button class="b-btn clear hide position0" id="clearSearchBtn">
												<i class="icon-close-small"></i></button>
											</div>
											<div class="search-btn positionL0" style="width:auto;height:34px;">
												<button class="b-btn search-btn" id="searchSubmit"><i class="icon-search"></i></button>
											</div>
										</div>

										<div class="btn-group dropdown-wrapper fsStartDiv">
												<button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
													<?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
												</button>
												<ul class="dropdown-menu" role="menu">
													<li class="startFullTable"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></button></li>
													<li class="startIncrTable"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></button></li>
													<li class="startDiffTable"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></button></li>
												</ul>
										</div>
								</div>	
								

							</div>
						</div>
				
				
						<div class="table-container" style="margin-top: 10px;">
							<table class="table table-hover table-borderless" id="hadooptable">
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

					<div class="tab-pane" id="history">
						<div class="table-container">
							<table  class="table table-hover table-borderless" id="historytable">
							</table>
						</div>
					</div>
					<!-- 跳过文件详情模态框开始 -->
					<div id="passFileModal" style="z-index: 100000;top: 400px;"
						class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
						data-backdrop="static">
						<div class="modal-header ">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
							<h4 class="modal-title"><i class="viconfont icon-v-xnjbf"
									style="color: gray;margin-right: 8px;"></i><?php echo $LANG['BILLING_VIEW_DETAILS'] ?>
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
									<div class="col-md-12 p-0 obs-passfile-detail-wrapper">
										<table id="pass_files_details_table"></table>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-md-3 pl0"
										style="text-align: left;"><?php echo $LANG['UI_FILE_SKIP_REASON_DETAILS'] ?>:</label>
									<div class="col-md-12 p-0 obs-passfile-detail-wrapper">
										<table id="pass_files_reason_table"></table>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
						</div>
					</div>
					<!-- 跳过文件详情模态框结束 -->
				</div>
			</div>
		</div>
		<!-- END TAB PORTLET-->
	</div>
</div>
<!-- END PAGE CONTENT-->


 <!-- drawer开始 -->
 <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px" aria-labelledby="hdfs_more_detail_drawer_title" aria-hidden="true" id="hdfs_more_detail_drawer">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<div class="drawer-title" id="hdfs_more_detail_drawer_title"
				style="display:flex;justify-content:space-between;align-items:center">
				<div class="drawer-title-left">
					<i class="viconfont vicon-tenant-detail mr10"></i>
					<span><?php echo $LANG['UI_MORE_CONFIG_DETAIL'] ?></span>
				</div>
				<div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
					<i class="viconfont vicon-guanbi"></i>
				</div>
			</div>
		</div>
		<!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
		<div class="drawer-body config-detail-wrap config-detail-drawer-backup">
			<!-- BEGIN COMMON STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-celve1 me-8"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
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
					<div class="strategy-group__form__item is-recovery-static-info start_time_form">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_RECOVERY_TYPE_TIMING_TIME'] ?>
						</div>
						<div id="start_time" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- END TIME STRATEGY -->
				<!-- BEGIN SPEED LIMIT STRATEGY -->
				<div class="strategy-group__title">
					<span class="decoration me-8"></span><span
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
					<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form storagemodelDiv">
					<!-- 存储设备 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_STORAGE_DEV'] ?></div>
						<div id="storageinfo" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 备份节点 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_BACKUP_NODE'] ?></div>
						<div id="nodeinfo" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 压缩存储 -->
					<div class="strategy-group__form__item compressMethodDiv">
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
					<div class="strategy-group__form__item encryptStoragediv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?></div>
						<div id="encryptStorage" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 存储加密算法 -->
					<div class="strategy-group__form__item display-none encrypt-method-div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
						<div id="encryptMethod" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 自动生成密码 -->
					<div class="strategy-group__form__item display-none passwordAutodiv mb-20">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?></div>
						<div id="passwordAuto" class="strategy-group__form__item__value col-md-8"></div>
					</div>
				</div>
				<!-- END STORAGE STRATEGY -->
				<!-- BEGIN RESERVE STRATEGY -->
				<div class="reservedDiv">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span
							class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
					</div>
					<div class="strategy-group__form mb-20">
						<!-- 保留策略 -->
						<div class="strategy-group__form__item reserve-strategy-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></div>
							<div id="reservedStrategy" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END RESERVE STRATEGY --> 
			</div>
			<!-- END COMMON STRATEGY GROUP -->

			<!-- BEGIN TRANSMIT STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-a-chuanshu me-8"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form">
					<!-- 传输代理 -->
					<div class="strategy-group__form__item appliance-agency-flag-info" style="display: none;">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_HADOOP_PROXY'] ?></div>
						<div id="appliance_agency_flag" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 选择的传输代理 -->
					<div class="strategy-group__form__item appliance-agency-info" style="display: none;">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_HADOOP_PROXY'] ?></div>
						<div id="appliance_agency" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 选择的传输代理池 -->
					<div class="strategy-group__form__item appliance-agency-info" style="display: none;">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_AGENT_POOL'] ?></div>
						<div id="agentPool" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 加密传输是否开启 -->
					<div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></div>
						<div id="encryptStrategy" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 传输加密算法 -->
					<div class="strategy-group__form__item transfer-encrypt-method-div" style="display: none;">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
						<div id="transferEncryptMethod" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 传输网络 -->
					<div class="strategy-group__form__item  display-none transferNetworkdiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?>:
						</div>
						<div id="transferNetwork" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
					 <!-- 扫描线程 -->
					<div class="strategy-group__form mb-20 scanThreadDiv">
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_BAK_SCAN_THREAD'] ?></div>
							<div id="scanThreadNum" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
					 <!-- 扫描文件速度 -->
					 <div class="strategy-group__form mb-20 scanFileDiv" style="display: none;">
						<div class="strategy-group__form__item reserve-strategy-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_BAK_SCAN_FILE'] ?></div>
							<div id="scanFileNum" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
					 <!-- 传输线程 -->
					 <div class="strategy-group__form__item threadDiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
						</div>
						<div id="threadNum" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
				</div>
			</div>
			<!-- END TRANSMIT STRATEGY GROUP -->
			 
			<!-- BEGIN SAFE STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group backup-safety-strategy-group">
				<div class="strategy-group__header"> 
					<i class="viconfont vicon-anquancelve me-8"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form backupSafeModeDiv display-none">
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
					<!-- 病毒检测 -->
					<!-- <div class="strategy-group__form__item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_SAFE_STRATEGY_VIRUS_CHECK'] ?></div>
						<div id="virus_check_flag" class="strategy-group__form__item__value col-md-8"></div>
					</div> -->
					<!-- 病毒检测方式 -->
					<!-- <div class="strategy-group__form__item virus-check-way-div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_SAFE_STRATEGY_VIRUS_CHECK_METHOD'] ?></div>
						<div id="virus_check_way" class="strategy-group__form__item__value col-md-8"></div>
					</div> -->
					<!-- 完整性校验 -->
					<div class="strategy-group__form__item integrity-form-item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK'] ?></div>
						<div id="integrity_check_flag" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
				</div>
				 <!-- 完整性校验异常处理 -->
				<div class="strategy-group__form restoreSafeModeDiv display-none">
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
					<i class="viconfont vicon-gaojipeizhi me-8"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_COPY_GIGH_STRATEGY'] ?></span>
				</div>
				<!-- BEGIN WILDCARD GROUP -->
				<div class="config-detail-wrap__group strategy-group wildcardmodeDiv">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_FILE_WILDCARD'] ?></span>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_BACKUP_FILE_WILDCARD_WAYS'] ?></div>
							<div id="wildcardmode" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END WILDCARD GROUP -->
				<!-- BEGIN SNAPSHOT GROUP -->
				<div class="config-detail-wrap__group strategy-group snapshotDiv">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 快照 -->
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></div>
							<div id="snapshot" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END SNAPSHOT GROUP -->
				<!-- BEGIN PERMISSION GROUP -->
				<div class="config-detail-wrap__group strategy-group permissionDiv">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_ROLE_PERMISSION'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 权限 -->
						<div class="strategy-group__form__item file-permission-backup-div" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_PERMISSION_BACKUP'] ?></div>
							<div id="file_permission" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 文件权限恢复 -->
						<div class="strategy-group__form__item file-permission-recovery-div" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_FILE_RECOVERY_PERMISSION'] ?></div>
							<div id="file_permission_recovery" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END PERMISSION GROUP -->
				<!-- BEGIN ABNORMAL GROUP -->
				<div class="config-detail-wrap__group strategy-group">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_EXCEPTION_HANDLE'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 跳过文件告警智能判断 -->
						<div class="strategy-group__form__item passalarmflagdiv" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_JUDGE'] ?></div>
							<div id="passalarmflag" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 跳过文件告警个数 -->
						<div class="strategy-group__form__item passalarmDiv" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_NUM'] ?></div>
							<div id="passalarmNum" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 跳过文件告警比例 -->
						<div class="strategy-group__form__item passalarmDiv" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_RATIO'] ?></div>
							<div id="passalarmPercent" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 跳过目录树恢复 -->
						<div class="strategy-group__form__item dir-tree-div" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_RECOVERY_FILE_SKIP_DIR_TREE_RECOVERY'] ?></div>
							<div id="dir_tree" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 同名文件处理 -->
						<div class="strategy-group__form__item same-name-div" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE'] ?></div>
							<div id="same_name" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 无效快捷方式清理 -->
						<div class="strategy-group__form__item no-valid-clear-div" style="display: none;">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_RECOVERY_FILE_INVALID_SHORTCUT_CLEAN'] ?></div>
							<div id="no_valid_clear" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END ABNORMAL GROUP -->
				<!-- BEGIN RETRY STRATEGY -->
				<div class="config-detail-wrap__group strategy-group">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 网络重连次数 -->
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?></div>
							<div id="network_retry_times" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 网络重连间隔时间 -->
						<div class="strategy-group__form__item">
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
				<div class="config-detail-wrap__group strategy-group">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></span>
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
		<!-- END BACKUP TASK DETAIL DRAWER BODY -->
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
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/speed_chart.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/hadoop/hadoop_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->