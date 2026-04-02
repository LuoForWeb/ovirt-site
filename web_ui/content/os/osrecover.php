<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/timePointDetail/css/timePointDetail.css" />
<!-- END PAGE LEVEL STYLES -->
<?php session_start();
session_commit(); ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="recovery" href="./content/recovery/recoverycenter.php">
			<span><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY'] ?></span>
		</a>
	</li>
	<span>
		>
	</span>
	<span class="curent"><?php echo $LANG['WEB_OS_HOST_RECOVERY'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
	<div class="col-md-12" style="height:100%;">
		<div class="portlet box blue-hoki" id="osrecovercontent" style="height:100%;margin-bottom:0;">
			<input id="externalPointUuid" value="<?php echo $_GET['point_uuid']; ?>" class="display-none"></input>
			<input id="externalTaskUuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none"></input>
			<input id="externalItemUuid" value="<?php echo $_GET['item_uuid']; ?>" class="display-none"></input>
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-huifu1"></i><?php echo $LANG['WEB_OS_NEW_RECOVERY_TASK'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<div action="#" class="form-horizontal" id="submit_form" method="POST">
					<div class="form-wizard">
						<div class="form-body">
							<ul class="nav nav-pills steps">
								<li>
									<a href="#tab1" data-toggle="tab" class="step">
										<span class="number">1 </span>
										<span class="desc">
											<?php echo $LANG['UI_BR_RECOVERY_DATA_SOURCE'] ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
										<span class="number">2 </span>
										<span class="desc">
											<?php echo $LANG['UI_RECOVERY_GOAL'] ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab3" data-toggle="tab" class="step active">
										<span class="number">3 </span>
										<span class="desc">
											<?php echo $LANG['UI_RECOVERY_VM_TYPE'] ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab4" data-toggle="tab" class="step">
										<span class="number">4 </span>
										<span class="desc">
											<?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG'] ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
							</ul>
							<div class="tab-content bakuptab">
								<div class="tab-pane active" id="tab1">
									<div class="alert alert-danger display-none selecttimepointtip">
									</div>
									<div class="row tab-pane__row">
										<div class="col-md-4 tab-pane__row__source">
											<div class="form-group src-wrap">
												<div class="src-wrap__title">
													<span>
														<?php echo $LANG['UI_RECOVERY_BACKUP_POINT'] ?>
													</span>
												</div>
												<div class="src-wrap__content">
													<div class="src-wrap__content__search">
														<select class="bs-select width100p form-control" style="height: 34px" data-show-subtext="true" id="storageselect">
														</select>
														<div class="vm_tree_div">
															<div class="input-icon width100p">
																<input type="text" maxlength="128" placeholder="<?php echo $LANG['WEB_OS_SEARCH_NAME'] ?>" class="form-control" id="searchos" onkeyup="customInputValidate('string', this.value, $(this))" />
															</div>
														</div>
													</div>
													<div class="src-wrap__content__itree">
														<div class="os_tree vcenter-tree">
															<ul id="pointtypetree" class="ztree bd1de5 ztree-fa tree_div"></ul>
														</div>
														<div class="alert alert-block alert-info fade in display-hide me-12" id="nopointtips">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
															<ol class="alert-ol">
																<li>
																	<?php echo $LANG['UI_RECOVERY_NO_VM_TITLE'] ?>
																</li>
																<li>
																	<a class="ajaxify alert-link" name="osbackup" href="./content/os/osbackup.php"><?php echo $LANG['WEB_OS_HOST_DATA_BACKUP_TIPS'] ?></a>
																</li>
															</ol>
														</div>
														<!-- 搜索后没有找到资源给出提示内容 -->
														<div class="alert alert-block alert-info fade in display-hide mt10 me-12" id="nosearchtips">
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
												<span><?php echo $LANG['UI_RECOVERY_ALREADY_SELECT_POINT'] ?></span>
											</div>
											<div class="addIt-list">
												<ul class="feeds addVMList" id="timepointGroupList">
												</ul>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane" id="tab2">
									<div class="alert alert-danger display-none setrecover2tip">
									</div>
									<div class="row tab-pane__row">
										<div class="col-md-10 col-steptwo">
											<!-- 选择已有的IP地址 ,多选-->
											<div class="form-group host_tree_div" id="selectIP">
												<label class="control-label col-md-3">
													<span class="required">* </span>
													<?php echo $LANG['WEB_OS_GOAL_IP'] ?>
												</label>
												<div class="col-md-6">
													<select id="IPlistSelect" name="" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-max-options="1" data-size="5">
													</select>
												</div>
												<div class="col-md-2">
													<button type="button" class="btn green-haze" id="linkSelectIP"><?php echo $LANG['WEB_OS_LINK_TEST'] ?></button>
												</div>
											</div>

											<!-- 是否重新分区 -->
											<div id="zoneInfo" class="display-none">
												<div class="form-group display-none">
													<label class="control-label col-md-3 compresslabel form-group-label col-md-2_en"><?php echo $LANG['WEB_OS_RESTORE_VOLUME'] ?></label>
													<div class="col-md-4 form-group-content">
														<input type="checkbox" id="compresscheck" checked class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
														<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['WEB_OS_RESTORE_VOLUME_TIPS'] ?>">
															<i class="viconfont vicon-tishi"></i>
														</a>
													</div>
												</div>
												<!-- 目标卷 -->
												<div class="form-group">
													<label class="control-label col-md-3 compresslabel"><?php echo $LANG['WEB_OS_GOAL_VOLUME'] ?></label>
													<div class="col-md-9 accordion" style="overflow: auto;">
														<div class="table-container" id="osTable">
														</div>
													</div>
												</div>
											</div>
											<div class="form-group">
												<label class="control-label col-md-3"></label>
												<div class="col-md-9">
													<div class="alert alert-block alert-info fade in" id="tabletips">
														<button type="button" class="close" data-dismiss="alert"></button>
														<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
														<ol class="alert-ol">
															<li>
																<?php echo $LANG['WEB_OS_RECOVERY_TASK_TIPS1'] ?>
															</li>
															<li>
																<?php echo $LANG['WEB_OS_RECOVERY_TASK_TIPS2'] ?>
															</li>
															<li>
																<?php echo $LANG['WEB_OS_RECOVERY_TASK_TIPS3'] ?>
															</li>
															<li>
																<?php echo $LANG['WEB_OS_RECOVERY_TASK_TIPS4'] ?>
															</li>
														</ol>
													</div>

												</div>

											</div>
										</div>
									</div>
								</div>

								<div class="tab-pane" id="tab3">
									<div class="row row-stepthree">
										<div class="tabbable-custom col-md-offset-1 col-md-10">
											<ul class="nav nav-tabs">
												<li class="active">
													<a href="#tab_common" class="" data-content="<?php echo $LANG['UI_BACKUP_COMMON_STRATEGY_DES_TIPS'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
												</li>
												<li class="transferLi">
													<a href="#tab_transfer" class="" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_TIPS'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
												</li>
												<li class="highLi">
													<a href="#tab_high" class="" data-content="<?php echo $LANG['UI_BACKUP_HIGH_CONFIG_DES'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
												</li>
												<li class="otherLi" style="display: none;">
													<a href="#tab_other" class="" data-content="<?php echo $LANG['UI_EMERGENCY_PLAN_OTHERS'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-gaojipeizhi "></i> <?php echo $LANG['UI_EMERGENCY_PLAN_OTHERS'] ?> </a>
												</li>
											</ul>
											<div class="tab-content">
												<div class="tab-pane active" id="tab_common">
													<div class="panel-body">
														<!-- 时间策略 -->
														<div class="form-group">
															<div class="col-md-offset-1  col-md-10 pt15 recoveryTimeDiv">
																<div class="accordion strategyOne">
																	<div class="panel panel-default strategy-panel">
																		<div class="panel-heading">
																			<h4 class="panel-title">
																				<a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#recoveryTime" aria-expanded="true">
																					<i class="viconfont vicon-shijian2 font-green-seagreen"></i>
																					<span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
																					<span class="strategyDes recoveryTimeDes"></span>
																				</a>
																			</h4>
																		</div>
																		<div id="recoveryTime" class="panel-collapse collapse">
																			<div class="panel-body">
																				<div class="col-md-12">
																					<div class="form-group">
																						<label class="control-label col-md-2"><?php echo $LANG['UI_RECOVERY_START_TYPE'] ?>
																						</label>
																						<div class="col-md-4">
																							<select class="form-control select2me" id="recovertype">
																								<option value="1"><?php echo $LANG['UI_RECOVERY_START_TYPE_NOW'] ?></option>
																								<option value="4"><?php echo $LANG['UI_RECOVERY_START_TYPE_TIMING'] ?></option>
																							</select>
																						</div>
																					</div>
																					<div class="form-group display-hide setOnceTime">
																						<div class="onceTime-content">
																							<label class="control-label col-md-2">
																								<span class="required">* </span>
																								<?php echo $LANG['UI_BACKUP_SET_TIME'] ?>
																							</label>
																							<div class="onceTime-content-input">
																								<div class="input-group date form_datetime">
																									<input type="text" size="16" readonly id="oncetime" class="form-control input-sm"
																										style="width: 160px;">
																									<span class="input-group-btn">
																										<button class="btn default input-sm" id="resetdate" type="button"><i
																												class="fa fa-times"></i></button>
																										<button class="btn default date-set input-sm" type="button"><i
																												class="viconfont vicon-ge_calendar"></i></button>
																									</span>
																								</div>
																							</div>
																							<div class="onceTime-content-tips">
																								<a class="popovers " data-container="body" data-trigger="hover" data-placement="right"
																									data-content="<?php echo $LANG['UI_RECOVERY_TYPE_TIMING_TIME'] ?>"
																									data-original-title="" title="">
																									<i class="viconfont vicon-tishi"></i>
																								</a>
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

														<!-- 限速策略 -->
														<div class="form-group speedlimitDiv">
															<div class="col-md-offset-1 col-md-10 accordion strategyOne">
																<div class="panel panel-default strategy-panel">
																	<div class="panel-heading">
																		<h4 class="panel-title">
																			<a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
																				<i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
																				<span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
																				<span class="strategyDes speedlimitDes"></span>
																			</a>
																		</h4>
																	</div>
																	<!--<div id="speed" class="panel-collapse collapse in">
																		<div class="panel-body">
																			<div class="col-md-12">
																				<button type="button" id="addSpeedlimit" class="btn btn-sm green-haze">
																					<i class="viconfont vicon-ge_add_task"></i><?php /*echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD'] */ ?>
																				</button>
																				<ul id="speedList" style="padding-left: 0;">

																				</ul>
																			</div>
																		</div>
																	</div>-->
																	<?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
																	<!--       全局限速策略组合的组件配置-->
																</div>
															</div>
														</div>
													</div>
												</div>

												<div class="tab-pane " id="tab_transfer">
													<div class="panel-body">
														<div class="tabbable-custom col-md-10">
															<!-- 加密传输 -->
															<div class="form-group">
																<label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
																<div class="col-md-4 form-group-content">
																	<input type="checkbox" id="encrypttransfer" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																</div>
															</div>
															<!-- 传输加密算法 -->
															<div class="form-group transfer-encrypt-method-form display-none">
																<label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
																<div class="col-md-4">
																	<select class="form-control select2me input-sm" id="transferEncryptMethod">
																		<?php

																		foreach ($CONF['TRANSPORT_ENCRYPT_METHOD'] as $index => $encryptMethod) {
																			if ($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw" && $index == 2) {
																			} else {
																				echo '<option value="' . $index . '">' . $encryptMethod . '</option>';
																			}
																		}
																		?>
																	</select>
																</div>
															</div>
															<div class="form-group display-none transfernetworkDiv">
																<label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="transferNetwork">
																	</select>
																</div>
																<div class="col-md-2 mt10">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															<!-- 传输线程 -->
															<div class="form-group threadDiv">
																<label class="control-label col-md-3 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
																</label>
																<div class="col-md-4">
																	<div class="backupThreadDiv" id="backupThreadNum">
																		<div class="input-group spinner-group">
																			<input type="text" id="osThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="2">
																			<div class="spinner-buttons input-group-btn spinner-group-btn">
																				<button type="button" class="btn spinner-up default input-sm">
																					<i class="fa fa-angle-up"></i>
																				</button>
																				<button type="button" class="btn spinner-down default input-sm">
																					<i class="fa fa-angle-down"></i>
																				</button>
																			</div>
																		</div>
																	</div>
																</div>
																<div class="col-md-2 mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['WEB_OS_THREAD_RANGE_TIPS'] ?>" data-original-title="" title="">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
														</div>
													</div>
												</div>
												<!-- 高级配置 -->
												<div class="tab-pane " id="tab_high">
													<div class="advance-config-wrap">
														<div class="advance-config-wrap__row row m0">
															<div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
																<ul class="nav nav-tabs tabs-left">
																	<li class="active">
																		<a href="#retry_strategy_pane"
																			data-toggle="tab" aria-expanded="true">
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
																	<!-- 重试策略 -->
																	<div class="tab-pane active" id="retry_strategy_pane">
																		<div class="">
																			<div class="tab-content">
																				<div id="retry_config" class="tab-pane retry_config_pane active">
																				</div>
																			</div>
																		</div>
																	</div>
																	<!-- 过载保护 -->
																	<div class="tab-pane" id="over_load_strategy_pane">
																		<div class="abnormal-handle-form">
																			<!-- 忽略节点资源限制 -->
																			<div class="form-group advancedDiv ignoreResourceLimitDiv">
																				<label for="ignoreResourceLimit" class="control-label col-md-4 ignoreResourceLimitLabel form-group-label">
																					<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?>
																				</label>
																				<div class="col-md-3 form-group-content">
																					<input checked type="checkbox" id="ignoreResourceLimit" class="make-switch" data-on-color="primary"
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
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="tab-pane" id="tab_other">
													<div class="panel-body">
														<div class="tabbable-custom pdlr15  col-md-10">
															<!-- 数据加密-->
															<div class="form-group">
																<label class="control-label col-md-3">
																	<?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?>
																</label>
																<div class="col-md-4">
																	<input class="form-control select2me" type="password" autocomplete="off" id="encryptVal" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
																</div>
																<div class="col-md-2 mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DES_DATA_ENCRYPT_TIP'] ?>" data-original-title="" title="">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
														</div>
													</div>
												</div>

											</div>
										</div>
									</div>
								</div>

								<div class="tab-pane" id="tab4">
									<div class="tab-pane__body">
										<div class="tab-pane__body__form">
											<div class="alert alert-danger display-none jobnametip"></div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
												<div class="col-md-9">
													<div class="input-icon right">
														<input type="text" maxlength="64" class="form-control" id="jobname" onkeyup="customInputValidate('string', this.value, $(this))" />
														<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
													</div>
												</div>
											</div>
											<h4 class="form-section display-none"></h4>
											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_POINT_INFO'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  vmtypeshow">
													</p>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  recovershow">
													</p>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_TYPE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  reservetypeshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  speedlimitshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  transfershow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 retryShow"></div>
											<div class="form-group mb0 highDiv">
												<label
													class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>:</label>
												<div class="col-md-9">
													<div class="form-control-static">									
														<div class="ignoreResourceLimitShow"></div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-actions form-actions--create">
							<div class="row">
								<div class="col-md-offset-6 col-md-6">
									<a href="javascript:;" class="btn default button-previous">
										<i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?> </a>
									<a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
										<?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?> <i class="viconfont vicon-xiayibu"></i>
									</a>
									<a href="javascript:;" class="btn green-haze button-submit">
										<?php echo $LANG['UI_PUBLIC_SUBMIT'] ?> <i class="viconfont vicon-xiayibu"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- BEGIN RATE LIMIT MODAL -->
		<div id="speedlimitModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">

					<div class="form-group ">
						<label class="control-label col-md-2"> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE'] ?>
						</label>
						<div class="col-md-6">
							<select class="form-control select2me inline-block" id="speedModeType" style="width:203px;">
								<option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY'] ?></option>
								<option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER'] ?></option>
							</select>
							<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_TYPE_TIPS'] ?>">
								<i class="viconfont vicon-tishi"></i>
							</a>
						</div>
					</div>

					<div class="form-group setSpeedStrategy">
						<label class="control-label col-md-2"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY'] ?>
						</label>
						<div class="col-md-10">
							<div class="portlet">
								<div class="portlet-body">
									<div class="panel-group accordion" id="speedstrategy">
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE'] ?>
						</label>
						<div class="col-md-6">
							<div id="rateDiv" style="display:inline-flex;">
								<div class="input-icon">
									<div id="speedSpinnerNum">
										<div class="input-group spinner-group">
											<input type="text" id="speedSpinnerNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" style="text-align: center;" class="spinner-input form-control">
											<div class="spinner-buttons input-group-btn spinner-group-btn">
												<button type="button" class="btn spinner-up default">
													<i class="fa fa-angle-up"></i>
												</button>
												<button type="button" class="btn spinner-down default">
													<i class="fa fa-angle-down"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
								<div class="">
									<select id="unit" style="width: 80px; margin-left: 10px;height: 33px;text-align:center; border: 1px solid #E6E6E6;">
										<option value="1">KB/s</option>
										<option selected value="2">MB/s</option>
										<option value="3">GB/s</option>
									</select>
								</div>
								<a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>

					</div>
				</div>
			</div>

			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="speed_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END RATE LIMIT MODAL -->
	</div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.osRecoveryType.js"></script>
<!-- <script src="./scripts/plugins/bootstrap-datetimepicker.js" type="text/javascript"></script> -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script src="./scripts/components/timePointDetail/js/timePointDetail.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/clipboard/clipboard.min.js"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>

<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/os/osrecover.js" type="text/javascript"></script>