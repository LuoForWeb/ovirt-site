<?php include_once '../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<link rel="stylesheet" type="text/css" href="./scripts/components/timePointDetail/css/timePointDetail.css"/>
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
	<span class="curent"><?php echo $LANG['UI_RECOVERY_FOR_PUBLIC_CLOUD'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page" id="aws-recover">
	<div class="col-md-12" style="height:100%;">
		<span class="display-none" id="servertime"></span>
		<input id="externalPointUuid" value="<?php echo $_GET['point_uuid']; ?>" class="display-none"></input>
		<input id="externalTaskUuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none"></input>
		<input id="externalItemUuid" value="<?php echo $_GET['item_uuid']; ?>" class="display-none"></input>
		<input id="externalSubType" value="<?php echo $_GET['sub_type']; ?>" class="display-none"></input>
		<div class="portlet box blue-hoki" id="awsrecovercontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-huifu1"></i><?php echo $LANG['UI_BACKUP_NEW_PUBLIC_CLOUD_RECOVERY_JOB'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" class="form-horizontal" id="submit_form" method="POST">
					<div class="form-wizard">
						<div class="form-body">
							<ul class="nav nav-pills steps" >
								<li>
									<a href="#tab1" data-toggle="tab" class="step">
										<span class="number">1 </span>
										<span class="desc">
											<?php echo $LANG['UI_RECOVERY_DATA_SOURCE'] ?>
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
								<!-- 选择备份点 -->
								<div class="tab-pane active" id="tab1">
									<div class="alert alert-danger display-none selecttimepointtip">
									</div>
									<div class="row tab-pane__row">
										<div class="col-md-4 tab-pane__row__source">
											<div class="form-group src-wrap">
												<div class="src-wrap__title">
													<i class="viconfont vicon-vmprotect" style="margin-right: 8px"></i>
													<span><?php echo $LANG['UI_RECOVERY_SELECT_POINT'] ?></span>
												</div>
												<div class="src-wrap__content">
													<div class="src-wrap__content__search">
														<select class="bs-select width100p form-control" style="height: 34px" data-show-subtext="true" id="storageselect">
														</select>
														<div class="vm_tree_div">
															<div class="input-icon width100p">
																<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_RECOVERY_AWS_SEARCH_PLACEHOLDER'] ?>" class="form-control" id="searchvm" autocomplete="off" onkeyup="customInputValidate('string', this.value, $(this))" />
															</div>
														</div>
													</div>
													<div class="src-wrap__content__ztree" style="height: calc(100% - 92px)">
														<div class="two_tree vcenter-tree">
															<ul id="pointtypetree" class="ztree bd1de5 tree_div ztree-fa"></ul>
															<ul id="vmtypetree" class="ztree bd1de5  tree_div display-hide"></ul>
														</div>
														<div class="alert alert-block alert-info fade in display-hide" id="nopointtips">
															<button type="button" class="close" data-dismiss="alert"></button>
															<ul class="alert-ul">
																<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																<li>
																	<strong><?php echo $LANG['UI_RECOVERY_AWS_NO_TIMEPOINT'] ?></strong>
																	<a id="tobackup"><small><?php echo $LANG['UI_RECOVERY_AWS_NO_TIMEPOINT_TIPS'] ?></small></a>
																</li>
															</ul>
														</div>
														<div class="alert alert-block alert-info fade in display-hide" id="nosearchtips">
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
												<span><i class="viconfont vicon-ge_time_point" style="margin-right: 8px"></i><?php echo $LANG['UI_RECOVERY_ALREADY_SELECT_POINT'] ?></span>
											</div>
											<div class="addIt-list">
												<ul class="feeds addVMList" id="VMGroupList">
												</ul>
												<ul class="feeds addVMList" id="timepointGroupList">
												</ul>
											</div>
										</div>
									</div>
								</div>

								<!-- 恢复目标 -->
								<div class="tab-pane" id="tab2">
									<div class="alert alert-danger display-none setrecover2tip">
									</div>
									<div class="row tab-pane__row">
										<div class="col-md-12 col-steptwo">
											<!-- 选择云平台 -->
											<div class="form-group" id="platform-div">
												<label class="control-label col-md-1 aws-recovery-20_en">
													<span class="required">* </span>
													<?php echo $LANG['UI_RECOVERY_AWS_SELECT_PLATFORM'] ?>
												</label>
												<div class="col-md-3">
													<select class="form-control" id="platform-select"></select>
												</div>
											</div>
											<div class="form-group display-hide" id="noplatformtips">
												<label class="control-label col-md-1 aws-recovery-20_en">
													<span class="required">* </span>
													<?php echo $LANG['UI_RECOVERY_AWS_SELECT_PLATFORM'] ?>
												</label>
												<div class="col-md-3">
													<div class="alert alert-block alert-info fade in">
														<button type="button" class="close" data-dismiss="alert"></button>
														<ul class="alert-ul">
															<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
																:</strong>
															<li>
																<strong><?php echo $LANG['UI_RECOVERY_AWS_NO_PLATFORM'] ?></strong>
																<?php
																//if(empty($_SESSION['tenantuuid'])){
																echo '<p><a id="toaddvcenter"><small>' . $LANG['UI_RECOVERY_AWS_ADD_PLATFORM_TIPS'] . '</small></a></p>';
																//}
																?>
															</li>
														</ul>
													</div>
												</div>
											</div>
											<!-- 选择恢复类型 -->
											<div class="form-group" id="recovertype-div">
												<label class="control-label col-md-1 aws-recovery-20_en">
													<span class="required">* </span>
													<?php echo $LANG['UI_RECOVERY_AWS_SELECT_RECOVER_TYPE'] ?>
												</label>
												<div class="col-md-3">
													<select class="form-control" id="recovertype-select">
														<option value="1"><?php echo $LANG['UI_RECOVERY_AWS_INSTANCE_RECOVER'] ?></option>
														<option value="2"><?php echo $LANG['UI_RECOVERY_AWS_VOL_RECOVER'] ?></option>
													</select>
												</div>
												<div class="col-md-1 mt-8">
													<a class="popovers" data-container="body" data-trigger="hover"
													   data-placement="top" data-content="<?php echo $LANG['UI_RECOVERY_AWS_SELECT_RECOVER_TYPE_TIPS'] ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											<?php include_once('../../content/platform/component/aws_recovery.php'); ?>
										</div>
									</div>
								</div>

								<!-- 恢复方式 -->
								<div class="tab-pane" id="tab3">
									<div class="row row-stepthree">
										<div class="tabbable-custom col-md-offset-1 col-md-10">
											<ul class="nav nav-tabs">
												<li class="commonLi active">
													<a href="#tab_common" data-toggle="tab">
													<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
												</li>
												<li class="transferLi">
													<a href="#tab_transfer" data-toggle="tab">
														<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
												</li>
												<li class="safeLi">
													<a href="#tab_safe" data-toggle="tab">
														<i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> </a>
												</li>
												<li class="highLi">
													<a href="#tab_other" data-toggle="tab">
														<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
												</li>
											</ul>
											<div class="tab-content">
												<!-- 通用策略 -->
												<div class="tab-pane active" id="tab_common">
													<div class="panel-body">
														 <div class="form-group display-none" >
															<div class="control-label col-md-2" ><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?></div>
															<div class="col-md-4">
																<select class="form-control select2me inline-block" id="strategySelect">
																</select>
																<a class="popovers ml15" data-container="body" data-trigger="hover" 
																	data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
																	<i class="viconfont vicon-tishi"></i>
																</a>
															</div>
															<div class="col-md-1 mt10">
															</div>
														</div>
														<!-- 通用策略-时间策略 -->
														<div class="form-group">
															<div class="col-md-offset-2  col-md-9 pt15 recoveryTimeDiv">
																<div class="accordion strategyTwo" >
																	<div class="panel panel-default strategy-panel">
																		<div class="panel-heading">
																			<h4 class="panel-title">
																				<a class="accordion-toggle accordion-toggle-styled collapsed popovers" 
																				data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyTwo" href="#recoveryTime" aria-expanded="true">
																				<i class="iconfont icon-time font-green-seagreen"></i> 
																				<span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
																				<span class="strategyDes recoveryTimeDes"></span>
																				</a>
																			</h4>
																		</div>
																		<div id="recoveryTime" class="panel-collapse collapse in">
																			<div class="panel-body">
																				<div class="col-md-12">
																					<div class="form-group">
																						<label class="control-label col-md-2"><?php echo $LANG['UI_RECOVERY_TYPE'] ?> 
																						</label>
																						<div class="col-md-10">
																							<select class="form-control select2me" id="recovertype">
																								<option value="1"><?php echo $LANG['UI_RECOVERY_TYPE_NOW'] ?></option>
																								<option value="4"><?php echo $LANG['UI_RECOVERY_TYPE_TIMING'] ?></option>
																							</select>
																						</div>
																					</div>
																					<div class="form-group display-hide setOnceTime">
																						<label class="control-label col-md-2">
																							<span class="required">* </span>
																							<?php echo $LANG['UI_BACKUP_SET_TIME'] ?>
																						</label>
																						<div class="col-md-10">
																							<div class="input-group date form_datetime">
																								<input type="text" size="16" readonly id="oncetime" class="form-control input-sm">
																								<span class="input-group-btn">
																								<button class="btn default" id="resetdate" type="button"><i class="fa fa-times"></i></button>
																								<button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
																								</span>
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
														<!-- 通用策略-限速策略 -->
														<div class="form-group speedlimitDiv">
															<div class="col-md-offset-2 col-md-9 accordion strategyTwo" >
																<div class="panel panel-default strategy-panel">
																	<div class="panel-heading">
																		<h4 class="panel-title">
																			<a class="accordion-toggle accordion-toggle-styled collapsed popovers"  
																			data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyTwo" href="#speed" aria-expanded="true">
																			<i class="iconfont icon-xiansucelve font-green-seagreen"></i> 
																			<span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
																			<span class="strategyDes speedlimitDes"></span>
																			</a>
																		</h4>
																	</div>
																	<?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
																</div>
															</div>
														</div>
														<!-- 通用策略-高级策略 -->
														<div class="form-group highDiv display-none">
															<div class="col-md-offset-2  col-md-9 accordion strategyTwo" >
																<div class="panel panel-default strategy-panel">
																	<div class="panel-heading">
																		<h4 class="panel-title">
																			<a class="accordion-toggle accordion-toggle-styled collapsed popovers" 
																			data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyTwo" href="#recoveryHigh" aria-expanded="true"> 
																			<i class="iconfont icon-gaojicelve font-green-seagreen"></i> 
																			<span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH'] ?></span>
																			<span class="strategyDes recoveryHighDes"></span>
																			</a>
																		</h4>
																	</div>
																	<div id="recoveryHigh" class="panel-collapse collapse ">
																		<div class="panel-body">
																			<div class="col-md-9">

																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<!-- 传输策略 -->
												<div class="tab-pane " id="tab_transfer">
													<?php include_once('../../content/platform/component/aws_transfer_strategy.php'); ?>
												</div>

												<!-- 高级配置 -->
												<div class="tab-pane " id="tab_other">
													<div class="advance-config-wrap">
														<div class="advance-config-wrap__row row m0" >
															<div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios" style="align-items: flex-start;">
																<ul class="nav nav-tabs tabs-left">
																	<li class="active">
																		<a href="#tab_high_snapshot" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></a>
																	</li>
																	<li>
																		<a href="#retry_config" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></a>
																	</li>
																	<li>
																		<a href="#tab_overload_protect" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></a>
																	</li>
																</ul>
															</div>
															<div class="col-md-10 col-sm-10 col-xs-10 advance-config-wrap__row__content">
																<div class="tab-content">
																	<!-- 快照 -->
																	<div class="tab-pane active" id="tab_high_snapshot">
																		<div class="col-md-12 advanced-detail-conf-district pt40">
																			<!-- 优先快照恢复 -->
																			<div class="form-group prisnapshotdiv">
																				<label class="control-label col-md-3 prisnapshotlabel form-group-label"><?php echo $LANG['UI_RECOVERY_AWS_PRIORITY_SNAPSHOT_RECOVERY'] ?></label>
																				<div class="col-md-2 form-group-content">
																					<input type="checkbox" id="prisnapshot"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
																						   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
																						   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
																					   data-content="<?php echo $LANG['UI_RECOVERY_AWS_PRIORITY_SNAPSHOT_RECOVERY_TIPS'] ?>" >
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																		</div>
																	</div>
																	<!-- 重试 -->
																	<div id="retry_config" class="tab-pane retry_config_pane">
																	</div>
																	<!-- 过载保护 -->
																	<div class="tab-pane" id="tab_overload_protect">
																		<div class="col-md-12 advanced-detail-conf-district pt40">
																			<!-- 忽略节点资源限制 -->
																			<div class="form-group">
																				<label class="control-label col-md-3 ignoreResourceLimitLabel form-group-label"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></label>
																				<div class="col-md-2 form-group-content">
																					<input type="checkbox" id="ignore_resource_limit"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
																						   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
																						   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
																					   data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS'] ?>" >
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

												<!-- 安全策略 -->
												<div class="tab-pane" id="tab_safe">
													<div class="panel-body">
														<!-- 病毒检测 插件 -->
														<div id="virusConfig"></div>
														<!-- 备份数据完整性校验 插件 -->
														<div class="mt20" id="completeConfig"></div>
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
											<div class="alert alert-danger display-none jobnametip"></div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?></label>
												<div class="col-md-9">
													<div class="input-icon right">
														<input type="text" maxlength="64" class="form-control" id="jobname" onkeyup="customInputValidate('string', this.value, $(this))" />
														<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
													</div>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_POINT_INFO'] ?></label>
												<div class="col-md-9">
													<p class="form-control-static vmtypeshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?></label>
												<div class="col-md-9">
													<p class="form-control-static recovershow">
													</p>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_TYPE'] ?></label>
												<div class="col-md-9">
													<p class="form-control-static reservetypeshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10" id="transportmodeshowdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></label>
												<div class="col-md-9">
													<p class="form-control-static transportinfoshow">
													</p>
												</div>
											</div>
											<div class="form-group safeDiv" id="safeShowDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static safeStrategyShow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10" id="highstrategyshowdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH'] ?></label>
												<div class="col-md-9">
													<p class="form-control-static prisnapshotshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 retryShow">
											</div>
											<div class="form-group mb0 mt10" id="speedstrategyshowdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></label>
												<div class="col-md-9">
													<p class="form-control-static speedlimitshow">
													</p>
												</div>
											</div>
										</div>
									</div>
									
									<h4 class="form-section"></h4>
									<div class="form-group">
										<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?>:</label>
										<div class="col-md-9">
											<p class="form-control-static recovershow">
											</p>
										</div>
									</div>
									
									<h4 class="form-section"></h4>
									<div class="form-group">
										<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_TYPE'] ?>:</label>
										<div class="col-md-4">
											<p class="form-control-static reservetypeshow">
											</p>
										</div>
									</div>
									<div class="form-group " id="transportmodeshowdiv">
										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
										<div class="col-md-4">
											<p class="form-control-static transportinfoshow">
											</p>
											<p class="form-control-static applianceshow">
											</p>
										</div>
									</div>
									<div class="form-group safeDiv" id="safeShowDiv">
										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
										<div class="col-md-4">
											<p class="form-control-static safeStrategyShow">
											</p>
										</div>
									</div>
									<div class="form-group " id="highstrategyshowdiv">
										<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH'] ?>:</label>
										<div class="col-md-4">
											<p class="form-control-static highstrategyshow">
											</p>
										</div>
									</div>
									<div class="form-group " id="speedstrategyshowdiv">
										<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
										<div class="col-md-4">
											<p class="form-control-static speedlimitshow">
											</p>
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
				</form>
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
						<label class="control-label col-md-2">  <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE'] ?>
						</label>
						<div class="col-md-6">
							<select class="form-control select2me inline-block" id="speedModeType"  style="width:203px;">
								<option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY'] ?></option>
								<option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER'] ?></option>
							</select>
							<a class="popovers ml15" data-container="body" data-trigger="hover" 
							data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_TYPE_TIPS'] ?>">
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
								<div class="input-icon" >
									<div id="speedSpinnerNum" >
										<div class="input-group spinner-group">
											<input type="text" id="speedSpinnerNumInput" style="text-align: left;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control">
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
									<select id="unit" style="width: 80px; margin-left: 10px;height: 33px;text-align:center;border: 1px solid #E6E6E6;">
										<option value="1">KB/s</option>
										<option selected value="2">MB/s</option>
										<option value="3">GB/s</option>
									</select>
								</div>
								<a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body" data-trigger="hover" 
								data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS'] ?>">
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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	//如果不是英文,加载语言包
	echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmRecoveryConfig.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfigValidate.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/public/initComponents.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/components/timePointDetail/js/timePointDetail.js"></script>
<script type="text/javascript" src="./scripts/components/timePointDetail/js/virusHistoryTable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/clipboard/clipboard.min.js"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/aws/awsrecover.js" type="text/javascript"></script>
