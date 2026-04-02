<?php include_once '../../tpl/permission.php';
$userAllPermission = $_SESSION['permission'];
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="./scripts/components/timePointDetail/css/timePointDetail.css">
</link>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- END PAGE LEVEL STYLES -->
<?php session_start();
session_commit(); ?>
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="archive_new" href="./content/archive/archivecenter.php">
			<span><?php echo $LANG['UI_PLATFORM_ARCHIVE'] ?></span>
		</a>
	</li>
	<span>
		>
	</span>
	<span class="curent">
		<?php
		$module_type = $_GET['module_type'];
		$sub_module_type = $_GET['sub_module_type'];
		$MODULE_TYPE = $CONF['MODULE_TYPE'];
		switch ($module_type) {
			case $MODULE_TYPE['VM']:
				$page_name = $LANG['WEB_PLATFORM_DES_VM'];
				if ($sub_module_type == 2) {
					$page_name = $LANG['WEB_PLATFORM_DES_PRIVATE_CLOUD'];
				}
				if ($sub_module_type == 3) {
					$page_name = $LANG['WEB_PLATFORM_DES_PUBLIC_CLOUD'];
				}
				break;
			case $MODULE_TYPE['FS']:
				$page_name = $LANG['WEB_PLATFORM_DES_FS'];
				if ($sub_module_type == 2) {
					$page_name = $LANG['WEB_PLATFORM_DES_NAS'];
				}
				if ($sub_module_type == 3) {
					$page_name = $LANG['UI_PLATFORM_HADOOP_HDFS'];
				}
				if ($sub_module_type == 4) {
					$page_name = $LANG['UI_PLATFORM_OBS'];
				}
				break;
			case $MODULE_TYPE['NAS']:
				$page_name = $LANG['WEB_PLATFORM_DES_NAS'];
				break;
			case $MODULE_TYPE['OS']:
				if ($sub_module_type == 0) {
					$page_name = $LANG['UI_PLATFORM_MACHINE_REEL_BACKUP'];
				}
				if ($sub_module_type == 1) {
					$page_name = $LANG['UI_PLATFORM_MACHINE_COMPLETE_BACKUP'];
				}
				break;
			case $MODULE_TYPE['DB']:
				$page_name = $LANG['WEB_PLATFORM_DES_DB'];
				break;
			case $MODULE_TYPE['M365']:
				$page_name = $LANG['WEB_PLATFORM_DES_M365'];
				break;
			case $MODULE_TYPE['KUBERNETES']:
				$page_name = $LANG['UI_PLATFORM_K8S'];
				break;
		}
		//如果是英文版 需要加上空格
		if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
			$page_name .= $LANG['UI_PLATFORM_ARCHIVE_FETCH'];
		}else{
			$page_name .= ' ' . $LANG['UI_PLATFORM_ARCHIVE_FETCH'];
		}
		
		echo $page_name;
		?></span>

</h3>
<div class="recover-page">
	<div class="portlet box blue-hoki" id="archiveBackContent">
		<input id="module_type" value="<?php echo $_GET['module_type']; ?>" class="display-none"></input>
		<input id="sub_module_type" value="<?php echo $_GET['sub_module_type']; ?>" class="display-none"></input>
		<div class="portlet-title">
			<div class="caption">
				<i class="levelchild viconfont vicon-archive_back"></i><?php echo $LANG['UI_ARCHIVE_BACK_NEW_JOB'] ?>
			</div>
		</div>
		<div class="portlet-body form">
			<form action="#" class="form-horizontal" id="submit_form" method="POST">
				<div class="form-wizard">
					<div class="form-body">
						<div class="form-body-content">
							<ul class="nav nav-pills nav-justified steps">
								<li>
									<a href="#tab1" data-toggle="tab" class="step">
										<span class="number">1 </span>
										<span class="desc"> <?php echo $LANG['UI_ARCHIVE_BACK_SOURCE'] ?> <i class="fa fa-check"></i></span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
										<span class="number">2 </span>
										<span class="desc"> <?php echo $LANG['UI_ARCHIVE_BACK_TARGET'] ?><i class="fa fa-check"></i> </span>
									</a>
								</li>
								<li>
									<a href="#tab3" data-toggle="tab" class="step active">
										<span class="number">3 </span>
										<span class="desc"> <?php echo $LANG['UI_ARCHIVE_BACK_TYPE'] ?><i class="fa fa-check"></i></span>
									</a>
								</li>
								<li>
									<a href="#tab4" data-toggle="tab" class="step">
										<span class="number">4</span>
										<span class="desc"><?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG'] ?><i class="fa fa-check"></i></span>
									</a>
								</li>
							</ul>
							<div class="tab-content bakuptab">
								<!-- 归档源 -->
								<div class="tab-pane copy-source-div active" id="tab1">
									<div class="alert alert-danger display-none selectCopyTip">
									</div>
									<div class="row tab-pane__row">
										<div class="col-md-4 tab-pane__row__source">
											<div class="form-group src-wrap">
												<div class="src-wrap__title">
													<i class="viconfont vicon-archive_back"></i>
													<span><?php echo $LANG['UI_ARCHIVE_BACK_SOURCE'] ?></span>
												</div>
												<div class="src-wrap__content">
													<div class="src-wrap__content__search">
														<div class="src-search_wrapper src-data_type">
															<label class="control-label src-search_label"><?php echo $LANG['UI_ARCHIVE_SRC_DATA_TYPE'] ?></label>
															<select class="bs-select form-control" data-show-subtext="true" id="dataType">
																<option data-icon="timevmtype icon-default" value="3"> <?php echo $LANG['UI_COPY_BACK_SELECT_VM'] ?></option>
																<option data-icon="timepointtype icon-default" value="4"> <?php echo $LANG['UI_COPY_BACK_SELECT_POINT'] ?></option>
															</select>
														</div>
														<div class="src-search_wrapper src-storage">
															<label class="control-label src-search_label"><?php echo $LANG['UI_COPY_STORAGE_SELECT'] ?></label>
															<select class="bs-select form-control" id="storage">
															</select>
														</div>
													</div>
													<div class="src-wrap__content__ztree" style="height:calc(100% - 184px)">
														<div class="copyTreeDiv three_tree">
															<ul id="copySourceTree" class="ztree tree_div"></ul>
														</div>
														<div class="alert alert-block alert-info fade in display-hide" id="noDataTips">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
															<ol class="alert-ol">
																<li>
																	<?php echo $LANG['UI_COPY_BACK_NO_DATA_TITLE'] ?>
																</li>
																<li>
																	<a id="toBackup" class="alert-link"><?php echo $LANG['UI_ARCHIVE_NO_VM_DATA_REDIRECT'] ?></a>
																</li>
															</ol>
														</div>
														<div class="alert alert-block alert-info fade in display-hide" id="noSearchTips">
															<button type="button" class="close" data-dismiss="alert"></button>
															<ul class="alert-ul">
																<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																<li>
																	<?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-8 VMList-div">
											<div class="addTitle">
												<span><?php echo $LANG['UI_ARCHIVE_BACK_SOURCE_SELECT'] ?></span>
											</div>
											<div class="addIt-list">
												<ul class="feeds copyList" id="itemList"></ul>
											</div>
										</div>
									</div>
								</div>
								<!-- 备份目的地 -->
								<div class="tab-pane" id="tab2">
									<div class="row" style="height: 100%; overflow-y: auto; padding-top: 20px">
										<div class="col-md-9" id="backupTarget"></div>
									</div>
								</div>
								<!-- 备份策略 -->
								<div class="tab-pane" id="tab3">
									<div class="alert alert-danger display-none setstrategytip">
									</div>
									<div class="row row-stepthree">
										<div class="nav-tabs-wrapper col-md-offset-1 col-md-9">
											<!-- 策略导航 -->
											<ul class="nav nav-tabs nav-line-tabs">
												<li class="active nav-item" id="nodeli">
													<a href="#tabGeneral" class="nav-link" data-toggle="tab">
														<i class="icon-pointer"></i> <?php echo $LANG['UI_COPY_GENERAL_STRATEGY'] ?> </a>
												</li>
												<li class="nav-item">
													<a href="#tabTransfer" class="nav-link" data-toggle="tab">
														<i class="fa fa-exchange"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
												</li>
												<li class="nav-item <?php if($CONF['SYSTEM_INFO']['vendor'] == "sangfor"){echo 'display-none';}?>" id="sateLi">
													<a href="#safe_strategy" class="nav-link" data-toggle="tab">
														<i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_CONFIG_DES']; ?> </a>
												</li>
												<li class="highLi nav-item">
													<a href="#tab_other" class="nav-link" data-toggle="tab">
														<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
												</li>
											</ul>
											<div class="tab-content min-height300 hover-scroll-y">
												<!-- 通用策略 -->
												<div class="tab-pane active" id="tabGeneral">
													<div class="panel-body">
														<!-- 限速策略 -->
														<div id="backupStrategyDiv" class="col-md-10 col-md-offset-1 col-md-11_en"></div>
													</div>
												</div>
												<!-- 传输策略 -->
												<div class="tab-pane" id="tabTransfer">
													<div class="panel-body" style="overflow-y:auto;">
														<div class="col-md-9 tabTransferConfig">
														</div>
														<div class="col-md-9">
															<!-- 传输网络 -->
															<div class="form-group transferNet">
																<label class="control-label col-md-3 transferNetLabel"><?php echo $LANG['UI_COPY_TRANSFER_NET'] ?></label>
																<div class="col-md-4">
																	<ul class="ztree" id="transferNetworkTree"></ul>
																</div>
																<div class="col-md-2 mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
														</div>
													</div>
												</div>
												<!-- 安全策略 -->
												<div class="tab-pane" id="safe_strategy">
													<div id="wormConfig"></div>
												</div>
												<!-- 高级配置 -->
												<div class="tab-pane " id="tab_other">
													<div class="advance-config-wrap">
														<div class="advance-config-wrap__row row m0">
															<div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
																<ul class="nav nav-tabs tabs-left">
																	<li class="active">
																		<a href="#retry_config" data-toggle="tab">
																			<?php echo $LANG['UI_STRATEGY_RETRY'] ?>
																		</a>
																	</li>
																	<li class="">
																		<a href="#over_load_strategy_pane"
																			data-toggle="tab" aria-expanded="true">
																			<?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>
																		</a>
																	</li>
																</ul>
															</div>
															<div class="col-md-10 col-sm-10 col-xs-10 pe-0 advance-config-wrap__row__content">
																<div class="tab-content level1">
																	<div id="retry_config" class="tab-pane retry_config_pane active">
																	</div>
																	<div class="tab-pane fade" id="over_load_strategy_pane">
																		<div class="abnormal-handle-form">
																			<!-- 忽略节点资源限制 -->
																			<div class="form-group advancedDiv ignoreResourceLimitDiv">
																				<label for="ignoreResourceLimit" class="control-label col-md-3 ignoreResourceLimitLabel form-group-label">
																					<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?>
																				</label>
																				<div class="col-md-3 form-group-content">
																					<input type="checkbox" id="ignoreResourceLimit" class="make-switch" data-on-color="primary"
																						data-off-color="info" data-size="small"
																						data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
																						data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover"
																						data-html="true" data-placement="right"
																						data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																			<!-- 任务最大并发数 + 任务禁止运行时间段-->
																			<div class="form-group node-limit-form">
																				<div class="table-container col-md-offset-3 pl15">
																					<table class="table table-hover table-borderless" id="nodeLimitTable">
																					</table>
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
								</div>
								<!-- 确认配置 -->
								<div class="tab-pane" id="tab4">
									<div class="tab-pane__body">
										<div class="tab-pane__body__form">
											<div class="alert alert-danger display-none job_name_tip">
											</div>
											<!-- 任务名 -->
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
												<div class="col-md-4 mb15">
													<div class="input-icon right">
														<input type="text" onkeyup="customInputValidate('string', this.value, $(this))" maxlength="64" class="form-control" id="jobName" />
														<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
													</div>
												</div>
											</div>
											<!-- 副本源 -->
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_ARCHIVE_SOURCE'] ?>:</label>
												<div class="col-md-8">
													<p class="form-control-static  copy-source-list-div">
													</p>
												</div>
											</div>
											<!-- 目标存储 -->
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_TARGET_STORAGE'] ?>:</label>
												<div class="col-md-8">
													<p class="form-control-static  storage_show">
													</p>
												</div>
											</div>
											<!-- 计算节点 -->
											<div class="form-group mb0 mt10 calculateNodeShowDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_TARGET_NODE'] ?>:</label>
												<div class="col-md-8">
													<p class="form-control-static  node_show">
													</p>
												</div>
											</div>

											<!-- 副本方式 -->
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_MODE'] ?>:</label>
												<div class="col-md-8">
													<p class="form-control-static  backupTypeShow">
													</p>
												</div>
											</div>
											<!-- 传输策略 -->
											<div class="form-group mb0 mt10" id="transport_strategy_div">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
												<div class="col-md-8">
													<p class="form-control-static  transfer-strategy-show">
													</p>
												</div>
											</div>
											<!-- 限速策略 -->
											<div class="form-group mb0 mt10 speed_limit_div">
												<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
												<div class="col-md-8">
													<p class="form-control-static  speed_limit_show">
													</p>
												</div>
											</div>
											<!-- 安全策略展示 -->
											<div class="form-group mb0 mt10 safeDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static safeStrategyShow"></p>
												</div>
											</div>
											<!-- 高级配置 -->
											<div class="form-group mb0 mt10 high_strategy_div">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_GIGH_STRATEGY'] ?>:</label>
												<div class="col-md-8">
													<p class="form-control-static  high_strategy_show">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 retryShow"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-6 col-md-6">
								<a href="javascript:;" class="btn default button-previous">
									<i class="m-icon-swapleft"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?> </a>
								<a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
									<?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?><i class="m-icon-swapright m-icon-white" style="width: 19px;background-position-x:-23px "></i>
								</a>
								<a href="javascript:;" class="btn green-haze button-submit">
									<?php echo $LANG['UI_PUBLIC_SUBMIT'] ?><i class="m-icon-swapright m-icon-white"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfig.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfigValidate.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/backup-strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script type="text/javascript" src="./scripts/copy/copySource.js"></script>
<script type="text/javascript" src="./scripts/copy/initFormGroupLine.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_worm.js"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/timePointDetail.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>

<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/archive/archiveback.js" type="text/javascript"></script>