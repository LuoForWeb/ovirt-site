<?php include_once '../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
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
<div class="row job-detail">
	<div class="col-md-12 job-detail__halftop">
		<div class="col-md-8 job-detail__halftop__charts">
			<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none">
			<input id="task_type" value="<?php echo $_GET['type']; ?>" class="display-none">
			<input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none">
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
					<div class="progressDiv" style="width: auto;margin-top: 10px;">
						<div class="progressDiv__label" style="width: auto;">
							<span class="current_object">
							<?php echo $LANG['WEB_KUBE_DETAILS_BACKING_UP_RESOURCE']; ?>:
							</span>
							<span class="progressDiv_value"></span>
						</div>
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
					<div class="tab-content" style="padding:16px;">
						<div class="tab-pane active">
							<div class="portlet-body">
								<div class="row static-info">
									<div class="col-md-4 name">
										<?php echo $LANG['UI_PUBLIC_OPERATION'] ?>:
									</div>
									<div class="col-md-8 col-operate value">
										<div class="btn-group dropdown-wrapper vmStartDiv">
											<button type="button" class="btn btn-primary btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
												</i> <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
											</button>
											<ul class="dropdown-menu" role="menu" id="k8sOpList">
												<li class="startFull"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></button></li>
												<li class="startIncr"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></button></li>
												<!-- <li class="startDiff backup_div"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></button></li> -->
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
										<?php echo $LANG['UI_JOB_TASK_PHASE'] ?>:
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
						<a href="#runList" data-toggle="tab">
							<i class="viconfont vicon-dbhost"></i> <?php echo $LANG['WEB_KUBE_DETAILS_RESOURCE_LIST']; ?> </a>
					</li>
					<li>
						<a href="#pvcList" data-toggle="tab">
							<i class="viconfont vicon-dbhost"></i> <?php echo $LANG['WEB_KUBE_DETAILS_PERSISTENT_VOLUME_LIST']; ?> </a>
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

					<div class="tab-pane" id="runList">
						<!--                        <div class="table-container">-->
						<!-- 表格 -->
						<table id="resource_table"></table>
						<!-- 表格 -->
						<!--                        </div>-->
					</div>

					<div class="tab-pane" id="pvcList">
						<!--                        <div class="table-container">-->
						<!-- 表格 -->
						<table id="pvc_table"></table>
						<!-- 表格 -->
						<!--                        </div>-->
					</div>

					<div class="tab-pane" id="history">
						<!--                        <div class="table-container">-->
						<!-- 表格 -->
						<table id="history_table"></table>
						<!-- 表格 -->
						<!--                        </div>-->
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
							<?php echo $LANG['UI_JOB_CREATE_TIME'] ?>
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
							<?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8">

							<span class="label label-success" id="nextTime">
							</span>
						</div>
					</div>

					<div class="strategy-group__form__item align-items-baseline backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_TYPE'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="timeStrategyBackupType">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_FULL'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="fulldes">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_INCREMENT'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="incdes">
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
							<?php echo $LANG['UI_JOB_STORAGE_DEV'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="storageinfo">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_BACKUP_NODE'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="nodeinfo">
						</div>
					</div>
					
					<div class="strategy-group__form__item align-items-baseline backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="compressed">
						</div>
					</div>
					<!-- 压缩等级 -->
					<div class="strategy-group__form__item align-items-baseline  compressMethodDiv backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="compressed_method">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline encryptStoragediv backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="encryptStorage">
						</div>
					</div>
					<!-- 存储加密算法 -->
					<div class="strategy-group__form__item align-items-baseline display-none encrypt-method-div backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="encryptMethod">
						</div>
					</div>
					<div class="strategy-group__form__item align-items-baseline passwordAutodiv display-none backup_div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?>
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
					<!-- 加密传输 -->
					<div class="strategy-group__form__item backup-encrypt-transmit-form-item">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="encryptStrategy">
						</div>
					</div>
					<!-- 传输加密算法 -->
					<div class="strategy-group__form__item backup-encrypt-transmit-form-item transfer-encrypt-method-div">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="transferEncryptMethod">
						</div>
					</div>
					<div class="strategy-group__form__item backup-encrypt-transmit-form-item transferNetworkdiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="transport_network">
						</div>
					</div>
					<div class="strategy-group__form__item backup-encrypt-transmit-form-item threadCountDiv">
						<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="threadCount">
						</div>
					</div>
				  
				</div>
			</div>
			 <!-- BEGIN TRANSMIT STRATEGY GROUP -->
			 <div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-anquancelve me-4"></i><span
						class="strategy-group__header__text"><?php echo $LANG['WEB_KUBE_SECURITY_POLICY'] ?></span>
				</div>
				<div class="strategy-group__form">
					<div class="strategy-group__form__item backup_div">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="worm">
						</div>
					</div>
					<!-- 完整性校验 -->
					<div class="strategy-group__form__item integrity_div">
						<div class="strategy-group__form__item__label col-md-4">
						<?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK'] ?>
						</div>
						<div class="strategy-group__form__item__value col-md-8" id="integrity">
						</div>
					</div>
				  
				</div>
			</div>
			<!-- END TRANSMIT STRATEGY GROUP -->
			<!-- BEGIN ADVANCED CONFIG GROUP -->
			<div class="config-detail-wrap__group strategy-group">
				<div class="strategy-group__header">
					<i class="viconfont vicon-gaojipeizhi me-4"></i><span
						class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></span>
				</div>

				<!-- BEGIN RETRY STRATEGY -->
				<div class="backup_div">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_CM_CDP_BACKUP_COMMON_CONF'] ?></span>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item silentSnapshotdiv backup_div">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_KUBE_DETAILS_BLOCK_SIZE']; ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="block_size">
							</div>
						</div>
					</div>
				</div> 
				<div class="">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['WEB_KUBE_BACKUP_SNAPSHOT'] ?></span>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item backup_div">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_KUBE_DETAILS_PVC_SNAPSHOT_COPIES']; ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="pvc_copy">
							</div>
						</div>
						<div class="strategy-group__form__item backup_div">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_KUBE_DETAILS_IGNORE_SNAPSHOT_ERRORS']; ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="ignoring_snapshot">
							</div>
						</div>
						<div class="strategy-group__form__item display-none recover_div">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_KUBE_DETAILS_USE_LOCAL_SNAPSHOT']; ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="use_snapshoot">
							</div>
						</div>
					   
						<!-- <div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
							覆盖原PVC:
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="cover_pvc">
							</div>
						</div> -->
						
					</div>
				</div> 
				<div class="display-none recover_div">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['WEB_KUBE_RECOVERY_RESOURCE_MONITORING']; ?></span>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_KUBE_DETAILS_SKIP_OR_OVERRIDE_RESOURCE']; ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="skip_resource">
							</div>
						</div>
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_KUBE_DETAILS_RELEASE_WORKLOAD_NODE_LIMIT']; ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="relieve_limit">
							</div>
						</div>
						<div class="strategy-group__form__item">
							<div class="strategy-group__form__item__label col-md-4">
							<?php echo $LANG['WEB_KUBE_NAMESPACE_DETAILS']; ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="ns_redirect">
							</div>
						</div>
					</div>
				</div> 
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
				<div class="">
					<div class="strategy-group__title">
						<span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_SETTINGS_UPDATE_RETRY'] ?></span>
					</div>
					<div class="strategy-group__form">
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="network_retry_times">
								
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="network_retry_interval">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="op_retry_flag">
								<?php echo $LANG['UI_PUBLIC_ON_TWO'] ?>
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item op_retry_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="op_retry_times">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item op_retry_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="op_retry_interval">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="task_retry_flag">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item task_retry_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="task_retry_object">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item task_retry_div">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="task_retry_times">
							</div>
						</div>
						<div class="strategy-group__form__item passfilealarm-form-item">
							<div class="strategy-group__form__item__label col-md-4">
								<?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?>
							</div>
							<div class="strategy-group__form__item__value col-md-8" id="task_retry_interval">
							</div>
						</div>
					</div>
				</div>
				<!-- END RETRY STRATEGY -->
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
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/speed_chart.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/kubernetes/kubernetes_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->