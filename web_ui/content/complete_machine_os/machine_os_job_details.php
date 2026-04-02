<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>

<!-- END PAGE LEVEL STYLES -->


<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php"><span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB'] ?></span></a>
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
			<input id="sub_module_type" value="<?php echo $_GET['sub_module_type']; ?>" class="display-none"></input>
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
					<div class="portlet-title init_tab_title">
						<i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
					</div>
					<div class="tab-content">
						<div class="portlet-body" style="overflow: auto;">
							<div class="row static-info">
								<div class="col-md-4 name">
									<?php echo $LANG['UI_PUBLIC_OPERATION'] ?>:
								</div>
								<div class="col-md-8 col-operate value">
									<div class="btn-group dropdown-wrapper vmStartDiv">
										<button type="button" class="btn btn-primary btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
											<?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
										</button>
										<ul class="dropdown-menu" role="menu" id="nasOpList">
											<li class="startFull backup_div"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></button></li>
											<li class="startIncr backup_div"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></button></li>
											<li class="startDiff backup_div"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></button></li>
											<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i><?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?></button></li>
										</ul>
									</div>
								</div>
							</div>
							
							<div class="row static-info">
								<div class="col-md-4 name">
									<?php echo $LANG['UI_JOB_RNAME'] ?>:
								</div>
								<div class="col-md-8 value" id="taskName" style="word-break:break-word; max-width:300px;">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name">
									<?php echo $LANG['UI_PUBLIC_TASK_TYPE'] ?>:
								</div>
								<div class="col-md-8 value" id="taskType">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name">
									<?php echo $LANG['UI_JOB_TYPE'] ?>:
								</div>
								<div class="col-md-8 value" id="status">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name">
								<?php echo $LANG['UI_JOB_TASK_STAGE'] ?>:
								</div>
								<div class="col-md-8 value" id="current_stage">
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
								<?php echo $LANG['UI_PUBLIC_MORE_DETAIL'] ?>:
								</div>
								<div class="col-md-8 value">
									<a id="details_more" class="green-haze" data-toggle="drawer" data-target="#drawer-1" style="color: #0FBF98;">
										<?php echo $LANG['BILLING_VIEW_DETAILS'] ?>					   
									</a>
								</div>
							</div>
							
						</div>
					</div>
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
						<a href="#host" data-toggle="tab">
							<i class="viconfont vicon-dbhost"></i> <?php echo $LANG['WEB_OS_LIST'] ?> </a>
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

					<div class="tab-pane" id="host">
						<div class="table-container">
							<div class="vin_toolbar backup_div">
								<div class="leftTool">
									<div class="search input-group mr12">
										<input type="search" maxlength="128" id="searchVal" class="searchinput customSearch" autocomplete="off"
											style="padding-right:32px;min-width: 220px;" maxlength="64" type="text"
											placeholder="<?php echo $LANG['WEB_MACHINE_SEARCH_IP_NAME'] ?>">
										<div class="position0" style="width:auto;height:34px">
											<button class="b-btn clear hide position0" id="clearSearchBtn">
											<i class="icon-close-small"></i></button>
										</div>
										<div class="search-btn positionL0" style="width:auto;height:34px;">
											<button class="b-btn search-btn" id="searchSubmit"><i class="icon-search"></i></button>
										</div>
									</div>
									<div class="col-md-8 col-operate value">
										<div class="btn-group dropdown-wrapper vmStartDiv">
											<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
												<?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
											</button>
											<ul class="dropdown-menu" role="menu" id="nasOpList" style="margin-top: 0;">
												<li class="startFullList"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></button></li>
												<li class="startIncrList"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></button></li>
												<li class="startDiffList"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></button></li>
											</ul>
										</div>
									</div>
									
								</div>
									
							</div>
							<table  id="hostlist">
							</table>
							
							
						</div>
					</div>

					<div class="tab-pane" id="history">
						<div class="table-container">
							<table  id="historytable">
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













<!-- drawer开始 -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px"
	aria-labelledby="drawer-1_title" aria-hidden="true" id="drawer-1">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<div class="drawer-title" id="drawer-1_title"
				style="display:flex;justify-content:space-between;align-items:center">
				<div class="drawer-title-left">
					<i class="viconfont vicon-tenant-detail mr10"></i>
					<span><?php echo $LANG['UI_MORE_DETAIL'] ?></span>
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
					<i class="viconfont vicon-celve1 me-4"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__title">
					<span class="decoration me-4"></span><span
						class="strategy-group__title__text"><?php echo $LANG['WEB_KUBE_DETAILS_TIME_STRATEGY']; ?></span>
				</div>
				<div class="strategy-group__form">
					<!-- 时间策略 -->
					<div class="strategy-group__form__item align-items-baseline">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_CREATE_TIME'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="createTime">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline recover_div">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['WEB_KUBE_TIME_STRATEGY'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="time_recovery_strategy">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8">

							<span class="label label-success" id="nextTime">
							</span>
						</div>
					</div>

					<div class="strategy-group__form__item align-items-baseline backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_TYPE'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="timeStrategyBackupType">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_FULL'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="fulldes">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_INCREMENT'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="incdes">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="diffdes">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div permanent-increment-div backup_time_strategy_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_PERMANENT_INCREMENT'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="pincrdes">
						</div>
					</div>
				  
				</div>
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
					<!-- <div class="strategy-group__form__item task-priority-form-item-backup">
						<div class="strategy-group__form__item__label col-md-4">
							ss</div>
						<div id="task_priority_backup" class="strategy-group__form__item__value col-md-8"></div>
					</div> -->
				</div>
				<!-- END SPEED LIMIT STRATEGY -->

				<!-- BEGIN STORAGE STRATEGY -->
				<div class="strategy-group__title is-backup-static-info backup_div">
					<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form is-backup-static-info">
					<div class="strategy-group__form__item align-items-baseline backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_STORAGE_DEV'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="storageinfo">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_BACKUP_NODE'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="nodeinfo">
						</div>
					</div>
					
					<div class="strategy-group__form__item align-items-baseline backup_div">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_BACKUP_DEDUPLICATION'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="deduplication">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="compressed">
						</div>
					</div>
					<!-- 压缩等级 -->
					<div class="strategy-group__form__item align-items-baseline backup_div compressMethodDiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?></div>
						<div id="compressMethod" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<div class="strategy-group__form__item align-items-baseline encryptStoragediv backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="encryptStorage">
						</div>
					</div>
					
					<div class="strategy-group__form__item align-items-baseline passwordAutodiv display-none backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="passwordAuto">
						</div>
					</div>
				</div>
				<!-- END STORAGE STRATEGY -->

				<!-- BEGIN RESERVE STRATEGY -->
				<div class="reserve-div backup_div">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span
							class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
					</div>
					<div class="strategy-group__form">
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
					<i class="viconfont vicon-a-chuanshu me-4"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form">
					<div class="strategy-group__form__item backup-encrypt-transmit-form-item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="encryptStrategy">
						</div>
					</div>
					<div class="strategy-group__form__item backup-encrypt-transmit-form-item backup_div">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_DB_SRC_COMPRESS'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="original_compress_flag">
						</div>
					</div>	
					<div class="strategy-group__form__item backup-encrypt-transmit-form-item  display-none transferNetworkdiv backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="transferNetwork">
						</div>
					</div>
					<div class="strategy-group__form__item backup-encrypt-transmit-form-item threadCountDiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="threadCount">
						</div>
					</div>
				  
				</div>
			</div>
			<!-- END TRANSMIT STRATEGY GROUP -->

			<!-- BEGIN SAFE STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group is-backup-static-info safty_show">
				<div class="strategy-group__header">
					<i class="viconfont vicon-anquancelve me-4"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form">
					<div class="strategy-group__form__item backup_div worm_show">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="worm">
						</div>
					</div>
					<!-- 病毒检测 -->
					<div class="strategy-group__form__item virus_show">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_SAFE_STRATEGY_VIRUS_CHECK'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="virus">
						</div>
					</div>	
					<!-- 完整性校验 -->
					<div class="strategy-group__form__item integrity_show">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK'] ?>:
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="integrity">
						</div>
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

				<!-- BEGIN RETRY STRATEGY -->
				<div class="backup_div">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_SNAPSHOT'] ?></span>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item silentSnapshotdiv backup_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_DB_SILENT_SNAPSHOT'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="silentSnapshot">
							</div>
						</div>
					   
					   
					</div>
				</div> 
				<div class="backup_div">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_INCREMENT'] ?></span>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item silentSnapshotdiv backup_div">
							<div class="strategy-group__form__item__label col-md-4">
								CBT:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="CBT">
							</div>
						</div>
					   
					   
					</div>
				</div> 
				<!-- END RETRY STRATEGY -->
				<!-- BEGIN OVERLOAD GROUP -->
				<div class="backup_div">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_VALIDATE_DATA'] ?></span>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item passfilealarm-form-item backup_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_MACHINE_OS_SKIP_BAD_BLOCK_BACKUP'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="skipBadBlock">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item backup_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_BACKUP_VALID_DATA'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="validData">
							</div>
						</div>
					</div>
				</div>

				<div class="backup_div">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DRILLS_STORAGE'] ?>:</span>
					</div>
					<div class="strategy-group__form">
						<!-- 合并模式 -->
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_MACHINE_OS_DATA_CONTAINER_SIZE']?></div>
							<div id="data_container_size" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item redundant_data_proportion_div">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_MACHINE_OS_REDUNDANT_DATA_PROPORTION']?></div>
							<div id="redundant_data_proportion" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>

				<div class="">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>:</span>
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
				
				<div class="">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_SETTINGS_UPDATE_RETRY'] ?></span>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="network_retry_times">
								
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="network_retry_interval">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="op_retry_flag">
								<?php echo $LANG['UI_PUBLIC_ON_TWO'] ?>
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item op_retry_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="op_retry_times">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item op_retry_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="op_retry_interval">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="task_retry_flag">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item task_retry_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="task_retry_object">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item task_retry_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="task_retry_times">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item task_retry_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?>:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="task_retry_interval">
							</div>
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
















































 <!-- BEGIN SCRIPT CONTENT DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="scriptContentDrawer">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<h4 class="drawer-title">
				<span>
					<i class="viconfont vicon-jiaobenguanli"></i>
				</span>
				<span style="position: relative; top: -1px" class="name"></span>
				<span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
					<i class="viconfont vicon-guanbi"></i>
				</span>
			</h4>
		</div>
		<div class="drawer-body">
			<div class="portlet-body" style="height: 100%">
				<div class="form-group" style="height: 20px; margin-bottom: 16px">
					<label class="col-md-3" for=""><?php echo $LANG['UI_PUBLIC_SCRIPT_TYPE'] ?></label>
					<div class="col-md-6">
						<div id="scriptContentType"></div>
					</div>
				</div>
				<div class="col-md-12 pd0" style="height: calc(100% - 36px)">
					<pre id="scriptContent" style="height: 100%"></pre>
				</div>
			</div>
		</div>
		<!-- footer -->
		<div class="drawer-footer">
			<button type="button" class="btn default" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
		</div>
	</div>
</div>
<!-- END SCRIPT CONTENT DRAWER -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>

<script type="text/javascript" src="./scripts/platform/component/safe_worm.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/speed_chart.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/complete_machine_os/machine_os_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->