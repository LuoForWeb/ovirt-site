<?php include_once '../../tpl/permission.php'; ?>
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
					<div class="portlet-title init_tab_title">
						<i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
					</div>
					<div class="tab-content">
						<div class="tab-pane active">
							<div class="portlet-body">
								<div class="row static-info taskOperateDiv <?php
								//检查屏蔽只读观察者的操作按钮
								if (!in_array("global_write", $_SESSION['permission']) && in_array("global_observer", $_SESSION['permission'])) {
									echo 'display-none';
								}
								?>">
									<div class="col-md-4 name">
										<?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?>:
									</div>
									<div class="col-md-8 col-operate value">
										<div class="btn-group dropdown-wrapper">
											<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
												 <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
											</button>
											<ul class="dropdown-menu min-width100" role="menu" id="osOpList">
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
								<!-- MORE CONFIG -->
								<div class="row static-info flex-items-center">
									<div class="col-md-4 name">
										<?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_MORE'] ?>:
									</div>
									<div class="col-md-8 value">
										<a class="green-haze" data-toggle="drawer"
											data-target="#os_more_detail_drawer" href="javascript:void(0)" style="color: #0FBF98;">
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
					<li class="active">
						<a href="#log" data-toggle="tab">
							<i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
					</li>
					<li>
						<a href="#files" data-toggle="tab">
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
					<div class="tab-pane" id="files">
						<div class="table-toolbar os-table-toolbar">
							<div class="row">
								<div class="col-md-12 d-flex">
									<div class="search input-group mr12">
										<input class="os-detail-search customSearch" autocomplete="off" type="text" maxlength="64" placeholder="<?php echo $LANG['UI_SEARCH_HOST_NAME'] ?>">
										<div class="position0">
											<button class="b-btn clear position0 hide"><i class="icon-close-small"></i></button>
										</div>
										<div class="positionL0">
											<button class="b-btn search-btn"><i class="icon-search"></i></button>
										</div>
									</div>
									<div class="btn-group dropdown-wrapper osStartDiv <?php
									//检查屏蔽只读观察者的操作按钮
									if (!in_array("global_write", $_SESSION['permission']) && in_array("global_observer", $_SESSION['permission'])) {
										echo 'display-none';
									}
									?>">
										<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
											<?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
										</button>
										<ul class="dropdown-menu min-width100" role="menu" id="">
											<li id="start"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></button></li>
											<li id="startIncr"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></button></li>
											<li id="startDiff"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></button></li>
										</ul>
									</div>
								</div>
							</div>
						</div>


						<div class="table-container">
							<table class="table table-striped table-bordered table-hover" id="oslist">
								<thead>
									<tr role="row" class="heading">
										<th width="2%">
										</th>
										<th width="2%">
											<?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
										</th>
										<th width="20%">
											<?php echo $LANG['UI_SETTINGS_SYSTEM_NAME'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['WEB_OS_BACKUP_TYPE'] ?>
										</th>
										<th width="8%">
											<?php echo $LANG['WEB_OS_HOST_SIZE'] ?>
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
											<?php echo $LANG['UI_DB_AVE_SPEED'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_JOB_TRA_PROGRESS'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_PUBLIC_STATUS'] ?>
										</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>


							<div class="alert alert-block alert-info fade in display-hide" id="osbackuptips">
								<button type="button" class="close" data-dismiss="alert"></button>
								<ul class="alert-ul">
									<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
									<li>
										<?php echo $LANG['WEB_OS_HOST_INFO_TIPS'] ?>
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
										<th width="20%">
											<?php echo $LANG['WEB_OS_BACKUP_TYPE'] ?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_TENANT_DELETE_RESULT'] ?>
										</th>
										<th width="10%">
											<?php echo $LANG['WEB_OS_TASK_SIZE'] ?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_OS_VALID_DATA_SIZE'] ?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_JOB_TRANSFER_SIZE'] ?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_JOB_REAL_SIZE'] ?>
										</th>
										<th width="15%">
											<?php echo $LANG['UI_JOB_START_TIME'] ?>
										</th>
										<th width="15%">
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
<!-- BEGIN TASK DETAIL DRAWER -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px"
	aria-labelledby="os_detail_drawer_title" aria-hidden="true" id="os_more_detail_drawer">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<div class="drawer-title" id="os_detail_drawer_title"
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
					<div class="strategy-group__form__item backup_style">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_CREATE_TIME'] ?>
						</div>
						<div id="createTime" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 下次开始时间 -->
					<div class="strategy-group__form__item backup_style">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8"> 
							<span class="label label-success" id="nextTime">
							</span>
						</div>
					</div>
					<!-- 备份方式 -->
					<div class="strategy-group__form__item backup_style">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_TYPE'] ?>
						</div>
						<div id="timeStrategyBackupType" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 完全备份 -->
					<div class="strategy-group__form__item backup_time_strategy_div backup_style">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_FULL'] ?>
						</div>
						<div id="fulldes" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 增量备份 -->
					<div class="strategy-group__form__item backup_time_strategy_div backup_style">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_INCREMENT'] ?>
						</div>
						<div id="incdes" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 差异备份 -->
					<div class="strategy-group__form__item backup_time_strategy_div backup_style">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?>
						</div>
						<div id="diffdes" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 永久增量 -->
					<div class="strategy-group__form__item permanent-increment-div backup_time_strategy_div backup_style">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_PERMANENT_INCREMENT'] ?>
						</div>
						<div id="pincrdes" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 恢复方式 -->
					<div class="strategy-group__form__item is-recovery-static-info recovery_style">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_RECOVERY_TYPE'] ?>
						</div>
						<div id="recovery_type" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 定时恢复时间 -->
					<div class="strategy-group__form__item is-recovery-static-info start_time_form recovery_style">
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
				<div class="strategy-group__title storageDiv">
					<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form storageDiv">
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
				  
					<!-- 重复数据删除 -->
					<div class="strategy-group__form__item deduplicationdiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_DEDUPLICATION'] ?></div>
						<div id="deduplication" class="strategy-group__form__item__value col-md-8"></div>
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
					<div class="strategy-group__form__item encryptStoragediv">
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
				<div class="reserve-div backup_style reserved_strategy_div">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span
							class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
					</div>
					<div class="strategy-group__form mb-20">
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
			<!-- END COMMON STRATEGY GROUP -->

			<!-- BEGIN TRANSMIT STRATEGY GROUP -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-a-chuanshu me-8"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
				</div>
				<div class="strategy-group__form">
					<!-- 加密传输 -->
					<div class="strategy-group__form__item backup-encrypt-transmit-form-item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></div>
						<div id="encryptStrategy" class="strategy-group__form__item__value col-md-8"></div>
					</div>
					<!-- 传输加密算法 -->
					<div class="strategy-group__form__item transfer-encrypt-method-div" style="display: none;">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
						<div id="transferEncryptMethod" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
					<!-- 传输网络 -->
					<div class="strategy-group__form__item transferNetworkdiv" style="display: none;">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?>
						</div>
						<div id="transferNetwork" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
					<!-- 传输模式 -->
					<div class="strategy-group__form__item" style="display: none;">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>
						</div>
						<div id="transMode" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
					<!-- 传输线程 -->
					<div class="strategy-group__form__item threadCountDiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
						</div>
						<div id="threadCount" class="strategy-group__form__item__value col-md-8">
						</div>
					</div>
				</div>
			</div>
			<!-- END TRANSMIT STRATEGY GROUP -->

			<!-- BEGIN ADVANCED CONFIG GROUP -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-a-chuanshu me-8"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></span>
				</div>

				<!-- BEGIN SNAPSHOT GROUP -->
				<div class="config-detail-wrap__group strategy-group silentSnapshotdiv">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_SNAPSHOT'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- 快照 -->
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_DB_SILENT_SNAPSHOT'] ?></div>
							<div id="silentSnapshot" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END SNAPSHOT GROUP -->
				<!-- BEGIN CBT GROUP -->
				<div class="config-detail-wrap__group strategy-group cbtdiv">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_INCREMENT'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- CBT -->
						<div class="strategy-group__form__item archive-transmit-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								CBT</div>
							<div id="CBT" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END CBT GROUP -->
				<!-- BEGIN BUILT GROUP -->
				<div class="config-detail-wrap__group strategy-group display-none builtFlag">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['WEB_OS_RESTORE_VOLUME'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- CBT -->
						<div class="strategy-group__form__item archive-transmit-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['WEB_OS_RESTORE_VOLUME'] ?></div>
							<div id="builtSize" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END BUILT GROUP -->
				<!-- BEGIN BUILT GROUP -->
				<div class="config-detail-wrap__group strategy-group display-none recoverOsFlag">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['WEB_OS_RESTORE_RECOVERY_OS'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- CBT -->
						<div class="strategy-group__form__item archive-transmit-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['WEB_OS_RESTORE_RECOVERY_OS'] ?></div>
							<div id="recoverOS" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END BUILT GROUP -->
				<!-- BEGIN VALID DATA GROUP -->
				<div class="config-detail-wrap__group strategy-group validDatadiv">
					<div class="strategy-group__title">
						<span class="decoration me-8"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_VALIDATE_DATA'] ?></span>
					</div>
					<div class="strategy-group__form">
						<!-- CBT -->
						<div class="strategy-group__form__item archive-transmit-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_DB_VALID_DATA'] ?></div>
							<div id="valid_data" class="strategy-group__form__item__value col-md-8"></div>
						</div>
					</div>
				</div>
				<!-- END VALID DATA GROUP -->
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
							<div id="op_retry_times" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 操作异常重连间隔时间 -->
						<div class="strategy-group__form__item op-retry-form-item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?></div>
							<div id="op_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
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
							<div id="task_retry_times" class="strategy-group__form__item__value col-md-8"></div>
						</div>
						<!-- 任务重连间隔时间 -->
						<div class="strategy-group__form__item task-retry-form-item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?></div>
							<div id="task_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
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
<!-- END TASK DETAIL DRAWER -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/speed_chart.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/os/os_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->