<?php
include_once '../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
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
	<span class="curent"><?php echo $LANG['UI_RECOVERY_FOR_VOL_CDP'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<div class="row recover-page">
	<div class="col-md-12" style="height:100%;">
		<input id="s_vmuuid" value="<?php echo $_GET['vmuuid']; ?>" class="display-none"></input>
		<input id="s_vcenteruuid" value="<?php echo $_GET['vcenteruuid']; ?>" class="display-none"></input>
		<input id="s_showtype" value="<?php echo $_GET['showtype']; ?>" class="display-none"></input>
		<input id="s_hypervisor" value="<?php echo $_GET['hypervisor']; ?>" class="display-none"></input>
		<div class="portlet box blue-hoki" id="volcdprecovercontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-huifu1"></i>
					<?php echo $LANG['UI_VOL_CDP_NEW_RECOVERY_TASK']; ?>
				</div>
			</div>
			<div class="portlet-body form">
				<div class="form-horizontal" id="submit_form">
					<div class="form-wizard">
						<div class="form-body">
							<ul class="nav nav-pills steps">
								<li>
									<a href="#tab1" data-toggle="tab" class="step">
										<span class="number">1 </span>
										<span class="desc">
											<?php echo $LANG['UI_VOL_CDP_RECOVERY_DATA_SOURCE']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
										<span class="number">2 </span>
										<span class="desc">
											<?php echo $LANG['UI_RECOVERY_GOAL']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab3" data-toggle="tab" class="step active">
										<span class="number">3 </span>
										<span class="desc">
											<?php echo $LANG['UI_RECOVERY_TYPE']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab4" data-toggle="tab" class="step">
										<span class="number">4 </span>
										<span class="desc">
											<?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
							</ul>
							<!-- 中间内容 -->
							<div class="tab-content bakuptab">
								<div class="tab-pane active" id="tab1">
									<div class="row tab-pane__row">
										<div class="col-md-10" style="height: 100%;overflow:auto;">
											<div class="form-group recoverTargetView display-none">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_VOL_CDP_STORAGE_NODE']; ?></label>
												<div class="col-md-3">
													<select class="form-control select2me" id="nodeselect">
													</select>
												</div>
											</div>
											<div class="form-group diystoragediv display-none">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE']; ?></label>
												<div class="col-md-3">
													<select class="form-control select2me" id="selectstorage">
													</select>
												</div>
											</div>

											<div class="form-group recoverydatasourcediv">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_VOL_CDP_RECOVERY_DATA_SOURCE_CLIENT']; ?></label>
												<div class="col-md-9">
													<div class="portlet-body">
														<div class="min-height150">
															<div class="table-container">
																<div class="table-container">
																	<table class="table table-striped table-bordered table-hover" id="recoveryDatasourceHostTable">
																		<thead>
																			<tr role="row" class="heading">
																				<th width="5%">
																					<input type="checkbox" class="group-checkable" disabled>
																				</th>
																				<th width="20%">
																					<?php echo $LANG['UI_VOL_CDP_CLIENT_NAME']; ?>
																				</th>
																				<th width="15%">
																					<?php echo $LANG['UI_VOL_CDP_RECOVER_IP']; ?>
																				</th>
																				<th width="30%">
																					<?php echo $LANG['UI_VOL_CDP_TASK']; ?>
																				</th>
																				<th width="30%">
																					<?php echo $LANG['UI_VOL_CDP_SYSTEM_TYPE']; ?>
																				</th>
																			</tr>
																		</thead>
																		<tbody></tbody>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="form-group">
												<label class="control-label col-md-3 diynodelabel"><?php echo "" ?></label>
												<div class="col-md-9">
													<div class="portlet-body">
														<div class="alert alert-block alert-info fade in" id="">
															<button type="button" class="close" data-dismiss="alert"></button>
															<ul class="alert-ol">
																<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
																<ol>
																	<li><?php echo $LANG['UI_VOL_CDP_RECOVER_TIPS']; ?></li>
																	<li>
																		<?php echo $LANG['UI_DB_CDP_RECOVER_PROCEED_FIRST']; ?>
																		<a id="tobackup" class="alert-link"><?php echo $LANG['UI_VOL_CDP_CREATE_BAK_JOB']; ?></a>
																	</li>
																</ol>
															</ul>
														</div>
													</div>
												</div>
											</div>


										</div>
									</div>
								</div>
								<div class="tab-pane vol-cdp-backup-tab" id="tab2">
									<div class="row">
										<div class="col-md-10" style="height: 100%;">
											<!-- 存储对象 -->
											<div class="form-group recoveryTargetView display-none">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_VOL_CDP_STORAGE_OBJ']; ?></label>
												<div class="col-md-3">
													<select class="form-control select2me" id="recoveryDataSourceSelect">
														<option value="1" selected><?php echo $LANG['UI_VOL_CDP_BAK_SERVER']; ?></option>
														<option value="2"><?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE']; ?></option>
													</select>
												</div>
												<div class="col-md-2 mt5">
													<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_STORAGE_OBJ_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											<!-- 
														 如果选择备机,接管时间配置不可展开,默认设置最新时间点. 选择服务端,时间点可编辑,当前不考虑数据对象只为备机的情况.
											-->
											<div class="form-group recoverytimediv">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_BACKUP_SET_DES']; ?></label>
												<div class="col-md-9 accordion conftimepointmap">
													<div class="panel panel-default strategy-panel">
														<div class="panel-heading">
															<h4 class="panel-title">
																<a class="accordion-toggle accordion-toggle-styled  popovers recoverytimecollapseview" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confrecoverytime" href="#recovery_timepoint_view" aria-expanded="true">
																	<i class="viconfont vicon-ge_vm font-green-seagreen"></i>
																	<span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TIME_FRAME']; ?></span>
																	<span class=" timerange fs12">--</span>

																	<span class="control-label colorgreen fs12"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_TIME']; ?></span>
																	<span class="strategyDes fs12 recoveryTimepointDes">--</span>
																</a>
															</h4>
														</div>
														<div id="recovery_timepoint_view" class="panel-collapse collapse in recoverytimecollapseview">
															<div class="panel-body" style="padding: 16px;">
																<div class="tabbable tabbable-custom ">
																	<ul class="nav nav-tabs" id="timepointType">
																		<li class="active" id="recoveryAnytimeLi">
																			<a href="#recoveryAnytime" data-toggle="tab" aria-expanded="true">
																				<i class="viconfont vicon-ge_time_point"></i> <?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT']; ?> </a>
																		</li>
																		<li class="" id="recoveryTagPointLi">
																			<a href="#recoveryTagPoint" data-toggle="tab" aria-expanded="false">
																				<i class="viconfont vicon-ge_sign"></i> <?php echo $LANG['UI_VOL_CDP_LABEL_POINTS']; ?> </a>
																		</li>
																		<!-- 
																		  -- 2024-08-13 20:52:15 因后台程序复杂的，导致监控数据不准确，暂时屏蔽
																		<li class="" id="agentEventInfoLi">
																			<a href="#agentEventInfo" data-toggle="tab" aria-expanded="false">
																				<i class="viconfont vicon-ge_basic_information"></i> <?php echo $LANG['UI_VOL_CDP_EVENT_INFO']; ?> </a>
																		</li>
																		 -->
																	</ul>

																	<div class="tab-content border-bottom-none">
																		<div class="tab-pane active " id="recoveryAnytime">
																			<div class="portlet-body min-height300" id="tabPortletBody">
																				<div class="form-group" style="margin-bottom: 10px;">
																					<label class="control-label col-md-2 diynodelabel"><?php echo $LANG['UI_VOL_CDP_TIME_FRAME']; ?></label>
																					<div class="col-md-4 width200">
																						<select class="volcdp-time-range-select select2me input-sm" id="changeTimeInterval">
                                                                                            <!-- <option value="1" selected><?php echo $LANG['UI_VOL_CDP_RECENT_10MINS']; ?></option> -->
																							<option value="2" selected><?php echo $LANG['UI_VOL_CDP_RECENT_ONEHOUR'] ?></option>
																							<option value="3"><?php echo $LANG['UI_TENANT_HOME_HISTORY_PROTECT1']; ?></option>
																							<option value="4"><?php echo $LANG['UI_TENANT_HOME_HISTORY_PROTECT7']; ?></option>
																							<option value="5"><?php echo $LANG['UI_TENANT_HOME_HISTORY_PROTECT30']; ?></option>
																							<option value="6"><?php echo $LANG['UI_VOL_CDP_RECENT_90dayes']; ?></option>
																							<option value="7"><?php echo $LANG['UI_VOL_CDP_TIME_RANGE']; ?></option>
																						</select>
																					</div>
																				</div>
																				<!-- 时间点范围 -->
																				<div class="form-group display-hide selectrecoverytimeview" style="margin-bottom: 0;">
																					<label class="control-label col-md-2"></label>
																					<div class="col-md-10 time-select-group">
																						<div class="time-select-group__item">
																							<div class="input-group date width200 recoverytimeview">
																								<input type="text" style="display:none;">
																								<input type="text" placeholder="<?php echo $LANG['UI_VOL_CDP_CONFIGURE_START_TIME']; ?>" size="16" id="selectRecoveryStartTime" class="form-control input-sm">
																								<span class="input-group-btn">
																									<!--<button class="btn default input-sm" id="resetStartTimePoint" type="button"><i class="fa fa-times"></i></button> -->
																									<button class="btn default date-set input-sm-vol-cdp" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
																								</span>
																							</div>
																						</div>
																						<div class="time-select-group__item">
																							<div class="input-group date width200 recoverytimeview">
																								<input type="text" style="display:none;">
																								<input type="text" placeholder="<?php echo $LANG['UI_VOL_CDP_CONFIGURE_END_TIME']; ?>" size="16" id="selectRecoveryEndTime" class="form-control input-sm">
																								<span class="input-group-btn">
																									<!-- <button class="btn default input-sm" id="resetEndTimepoint" type="button"><i class="fa fa-times"></i></button>-->
																									<button class="btn default date-set input-sm-vol-cdp inputendtime" id='inputendtime' type="button"><i class="viconfont vicon-ge_calendar"></i></button>
																								</span>
																							</div>
																						</div>
																						<div class="time-select-group__btns">
																							<button class="btn default input-sm" id="resetSelectTimePoint" type="button" title=<?php echo $LANG['UI_VOL_CDP_RESET']; ?>> <i class="fa fa-times"></i></button>
																							<button class="btn default input-sm green-turquoise" id="confirmSelectTimePoint" type="button" title=<?php echo $LANG['UI_VOL_CDP_CONFIRM']; ?>><i class="fa fa-check"></i></button>
																						</div>
																					</div>

																				</div>
																				<div id="volCdpRecoveryTimeline"></div>
																			</div>

																			<div class="pt30 floatl width100p mb-15 mb-0_en">
																				<label class="control-label col-md-2 ml-70  ml-0_en"><?php echo $LANG['UI_VOL_CDP_TIME_TYPE']; ?>:</label>
																				<div class="col-md-4">
																					<p class="form-control-static colorgreen" id="inputRecoveryTimepoindes">
																						<?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT']; ?>
																					</p>
																				</div>
																			</div>
																		</div>

																		<div class="tab-pane " id="recoveryTagPoint">
																			<div class="portlet-body">
																				<div class="table-actions-wrapper page-right">
																					<span>
																					</span>
																					<button type="button" id="refreshLabelPointInfo" class="btn btn-sm green-haze">
																						<i class="viconfont vicon-ge_refresh"></i> <?php echo $LANG['UI_PUBLIC_TOOLS_RELOAD']; ?>
																					</button>
																					<button type="button" id="getAllLabelPointInfo" class="btn btn-sm green-haze">
																						<i class="viconfont vicon-ge_sort"></i> <?php echo $LANG['UI_VOL_CDP_GET_ALL']; ?>
																					</button>
																				</div>
																				<div class="min-height300">
																					<div class="table-container">
																						<div class="table-container">
																							<table class="table table-striped table-bordered table-hover" style="word-break:break-all;" id="recoveryTagpointTable">
																								<thead>
																									<tr role="row" class="heading">
																										<th width="5%">
																											<input type="checkbox" class="group-checkable" disabled>
																										</th>
																										<th width="10%">
																											<?php echo $LANG['UI_PUBLIC_NUMBER']; ?>
																										</th>
																										<th width="35%" id="recoverTimepoint">
																											<?php echo $LANG['UI_DATA_TIMEPOINT']; ?>
																										</th>
																										<th width="50%">
																											<?php echo $LANG['UI_VOL_CDP_LABEL_REMARK']; ?>
																										</th>
																									</tr>
																								</thead>
																								<tbody></tbody>
																							</table>
																						</div>
																					</div>
																				</div>
																			</div>
																		</div>
																		<div class="tab-pane display-none" id="agentEventInfo">
																			<div class="portlet-body">
																				<div class="table-actions-wrapper page-right">
																					<span>
																					</span>
																					<button type="button" id="refreshEventInfo" class="btn btn-sm green-haze">
																						<i class="viconfont vicon-ge_refresh"></i> <?php echo $LANG['UI_PUBLIC_TOOLS_RELOAD']; ?>
																					</button>
																					<button type="button" id="getAllEventInfo" class="btn btn-sm green-haze">
																						<i class="viconfont vicon-ge_sort"></i> <?php echo $LANG['UI_VOL_CDP_GET_ALL']; ?>
																					</button>
																				</div>
																				<div class="min-height300">
																					<div class="table-container">
																						<div class="table-container">
																							<table class="table table-striped table-bordered table-hover" id="agentEventInfoTable">
																								<thead>
																									<tr role="row" class="heading">
																										<th width="5%">
																											<input type="checkbox" class="group-checkable" disabled>
																										</th>
																										<th width="25%" id="timePoint">
																											<?php echo $LANG['UI_VOL_CDP_TIME']; ?>
																										</th>
																										<th width="15%">
																											<?php echo $LANG['UI_VOL_CDP_TYPE']; ?>
																										</th>
																										<th width="10%">
																											<?php echo $LANG['UI_VOL_CDP_EVENT_LEVEL']; ?>
																										</th>
																										<th width="45%">
																											<?php echo $LANG['UI_VOL_CDP_EVENT_DES']; ?>
																										</th>
																									</tr>
																								</thead>
																								<tbody></tbody>
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
											<!-- 恢复时间点 -->
											<div class="form-group">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_VOL_CDP_RECOVERY_TIME_POINT']; ?></label>
												<div class="col-md-5 form-group-timepoint">
													<div class="input-group date recoverytimepointview">
														<input type="text" style="display:none;">
														<input type="text" size="16" id="inputRecoveryTimepoint" class="form-control input-sm">
														<span class="input-group-btn">
															<button class="btn default input-sm-vol-cdp" id="resetRecoveryTimepoint" type="button"><i class="fa fa-times"></i></button>
															<button class="btn default date-set input-sm-vol-cdp" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
														</span>
													</div>
													<span class="backupsetlockdiv">
														<i class="fa fa-lock backupsetlock backupsetlockview"></i>
													</span>
													<span class="control-label text-danger" id="timePointValidity">
														<?php echo $LANG['UI_VOL_CDP_UNCHECKED']; ?>
													</span>
												</div>
											</div>
											<!-- 恢复数据源备机 -->
											<div class="form-group recoverTargetView">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_MACHINE']; ?></label>
												<div class="col-md-3">
													<select class="form-control select2me" id="recoverTargetHost">
													</select>
												</div>
											</div>
											<div class="form-group diskgenView display-none">
												<label class="control-label col-md-3 diynodelabel form-group-label"><?php echo $LANG['WEB_OS_RESTORE_VOLUME']; ?></label>
												<div class="col-md-3 form-group-content">
													<input type="checkbox" id="rebuildPartSwitch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
													<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_RESTORE_VOLUME_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											<div class="form-group recoveryvoldiv">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_VOL_CDP_RECOVERY_VOL']; ?></label>
												<div class="col-md-9">
													<div class="portlet-body">
														<div class="min-height150">
															<div class="table-container">
																<div class="table-container">
																	<table class="table table-striped table-bordered table-hover" id="recoveryVolTable">
																		<thead>
																			<tr role="row" class="heading">
																				<th width="5%">
																					<input type="checkbox" class="group-checkable" >
																				</th>
																				<th width="20%">
																					<?php echo $LANG['UI_VOL_CDP_NAME']; ?>
																				</th>
																				<th width="15%">
																					<?php echo $LANG['UI_VOL_CDP_VOL_CAPACITY']; ?>
																				</th>
																				<th width="25%">
																					<?php echo $LANG['UI_VOL_CDP_RECOVERY_TIMEPOINT']; ?>
																				</th>
																				<th width="35%" id="volTargetMountPoint">
																					<?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_VOL']; ?>
																				</th>
																			</tr>
																		</thead>
																		<tbody></tbody>
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="form-group">
												<label class="control-label col-md-3 diynodelabel"><?php echo "" ?></label>
												<div class="col-md-9">
													<div class="portlet-body">
														<div class="alert alert-block alert-info fade in" id="">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
															<ol class="alert-ol">
																<li><?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_TIPS'] ?>
																</li>
																<li>
																	<?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_DISKGEN'] ?>
																</li>
																<li>
																	<?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_DISKGEN_DSIK'] ?>
																</li>
																<li>
																	<?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_DISKGEN_VOL'] ?>
																</li>
															</ol>
														</div>

													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- <div class="row tab-pane__row">
										<div class="col-md-9" style="height: 100%;overflow:auto;">
											
											

											

											

											

										</div>
									</div> -->
								</div>
								<div class="tab-pane" id="tab3" style="height: 100%;">
									<div class="row" style="height: 100%;">
										<div class="tabbable-custom col-md-offset-1 col-md-10" style="height: 100%;">
											<div class="panel-body">
												<!-- 时间策略 -->
												<div class="form-group">
													<div class="col-md-offset-1  col-md-10 ">
														<div class="accordion strategyOne display-none">
															<div class="panel panel-default strategy-panel">
																<div class="panel-heading">
																	<h4 class="panel-title">
																		<a class="accordion-toggle accordion-toggle-styled  popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#recoveryTime" aria-expanded="true">
																			<i class="viconfont vicon-shijian font-green-seagreen"></i>
																			<span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME']; ?></span>
																			<span class="strategyDes recoveryTimeDes"></span>
																		</a>
																	</h4>
																</div>
																<div id="recoveryTime" class="panel-collapse collapse in">
																	<div class="panel-body">
																		<div class="col-md-12">
																			<div class="form-group">
																				<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_TYPE']; ?>
																				</label>
																				<div class="col-md-4">
																					<select class="form-control select2me" id="recovertype">
																						<option value="1"><?php echo $LANG['UI_VM_MANUAL_START']; ?></option>
																						<option value="2"><?php echo $LANG['UI_RECOVERY_TYPE_STRATEGY']; ?></option>
																					</select>
																				</div>
																			</div>
																			<div class="form-group backupCrowd display-none">
																				<label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_TASK_CROWD']; ?>
																				</label>
																				<div class="col-md-6">
																					<div class="taskCrowd mb15" id="backupCrowd"></div>
																				</div>
																			</div>
																			<div class="form-group display-none" id="setstrategy">
																				<label class="control-label col-md-2"><?php echo $LANG['UI_BACKUP_SET_STRATEGY']; ?> <span class="required">
																						* </span>
																				</label>
																				<div class="col-md-10">
																					<div class="portlet">
																						<div class="portlet-body">
																							<div class="panel-group accordion" id="recoveryTimestrategy">
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

												<!-- 限速策略 -->
												<div class="form-group speedlimitDiv">
													<div class="col-md-offset-1 col-md-10 accordion strategyOne">
														<div class="panel panel-default strategy-panel">
															<div class="panel-heading">
																<h4 class="panel-title">
																	<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
																		<i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
																		<span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']; ?></span>
																		<span class="strategyDes speedlimitDes"></span>
																	</a>
																</h4>
															</div>
															<!--<div id="speed" class="panel-collapse collapse in">
																<div class="panel-body">
																	<div class="col-md-12">
																		<button type="button" id="addSpeedlimit" class="btn btn-sm green-haze">
																			<i class="viconfont vicon-ge_add_task"></i><?php /*echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD']; */ ?>
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
												<!-- 网络策略 -->
												<div class="form-group networkDiv">
													<div class="col-md-offset-1 col-md-10 accordion strategyOne">
														<div class="panel panel-default strategy-panel">
															<div class="panel-heading">
																<h4 class="panel-title">
																	<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#transfernetwork" aria-expanded="true">
																		<i class="viconfont vicon-ge_transfer font-green-seagreen"></i>
																		<span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?></span>
																		<span class="strategyDes transfernetStrategyDes"></span>
																	</a>
																</h4>
															</div>

															<!-- 传输网络 -->
															<div id="transfernetwork" class="panel-collapse collapse ">
																<div class="form-group mt25 transfernetworkview">
																	<label class="control-label col-md-3 encrypttransferlabels"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?></label>
																	<div class="col-md-4">
																		<select class="form-control select2me" id="transferNetwork">
																		</select>
																	</div>
																	<div class="col-md-4 ">
																		<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS']; ?>">
																			<i class="viconfont vicon-tishi"></i>
																		</a>
																	</div>
																</div>
																<!-- 加密传输-->
																<div class="form-group mt25 encrypttransferdiv">
																	<label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']; ?></label>
																	<div class="col-md-4 form-group-content">
																		<input type="checkbox" id="encrypttransfer" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																		<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ENCRYPTED_TRANSMISSION_TIPS']; ?>">
																			<i class="viconfont vicon-tishi"></i>
																		</a>
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

																<!-- 传输压缩 -->
																<div class="form-group trancompressdiv">
																	<label class="control-label col-md-3 transfercompress form-group-label"><?php echo $LANG['UI_COPY_COMPRESS_TRANSFER']; ?></label>
																	<div class="col-md-4 form-group-content">
																		<input type="checkbox" id="tran_compress_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																		<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_COMPRESSION_TIPS']; ?>">
																			<i class="viconfont vicon-tishi"></i>
																		</a>
																	</div>
																</div>
																<!-- 压缩等级选择 -->
																<div class="form-group transferCompressGradeDiv display-none">
																	<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
																	<div class="col-md-4">
																		<select class="form-control select2me input-sm" id="transferCompressGrade">
																			<option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
																			<option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
																			<option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
																			<option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
																		</select>
																	</div>
																</div>

																<!-- 传输数据包大小 -->
																<div class="form-group transferDataPackage">
																	<label class="control-label col-md-3 transferdatapackage"><?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE']; ?>
																	</label>
																	<div class="col-md-4">
																		<select class="form-control select2me" id="transfer_datapackage_size">
																			<option value=1>1 MB</option>
																			<option value=2>2 MB</option>
																			<option value=4 selected>4 MB</option>
																			<option value=8>8 MB</option>
																			<option value=16>16 MB</option>
																		</select>
																	</div>
																	<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TRANS_DATA_PACKED_SIZE_TIPS']; ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
																<!-- 重连次数 Bug #14474需求暂时屏蔽-->
																<div class="form-group display-none reconnect-times-wrapper">
																	<label class="control-label col-md-3 reconnect-times-Label"><?php echo $LANG['UI_BACKUP_RECONNECT_TIMES'] ?></label>
																	<div class="col-md-4">
																		<input type="text" id="reconnect_time" class="spinner-input form-control input-sm" value="60" maxlength="3" onkeyup="value=value.replace(/[^\d]/g,'')">
																	</div>
																	<div class="col-md-2 mt2">
																		<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_RECONNECT_TIMES_TIPS'] ?>" data-original-title="" title="">
																			<i class="viconfont vicon-tishi"></i>
																		</a>
																	</div>
																</div>
																<!-- 重连时间间隔  Bug #14474需求暂时屏蔽-->
																<div class="form-group display-none reconnect-Interval-wrapper">
																	<label class="control-label col-md-3 reconnect-Interval-Label"><?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL'] ?></label>
																	<div class="col-md-4">
																		<input type="text" id="reconnect_interval" class="spinner-input form-control input-sm" value="30" onkeyup="value=value.replace(/[^\d]/g,'')">
																	</div>
																	<div class="col-md-2 mt2">
																		<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL_TIPS'] ?>" data-original-title="" title="">
																			<i class="viconfont vicon-tishi"></i>
																		</a>
																	</div>
																</div>

															</div>

														</div>
													</div>
												</div>
												<!-- 高级策略 -->
												<div class="form-group transferthreadview">
													<div class="col-md-offset-1 col-md-10 accordion strategyOne">
														<div class="panel panel-default strategy-panel">
															<div class="panel-heading">
																<h4 class="panel-title">
																	<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupHigh" aria-expanded="true" data-original-title="" title="">
																		<i class="viconfont vicon-gaoji1 font-green-seagreen"></i>
																		<span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH']; ?></span>
																		<span class="strategyDes highDes"></span>
																	</a>
																</h4>
															</div>
															<div id="backupHigh" class="panel-collapse collapse ">
																<div class="panel-body">
																	<div class="col-md-9">

																		<div class="form-group threadDiv">
																			<label class="control-label col-md-3 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM']; ?>
																			</label>
																			<div class="col-md-4">
																				<div class="backupThreadDiv" id="transferThreadNum">
																					<div class="input-group spinner-group">
																						<input type="text" id="volcdpThreadNum" value='1' onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="1">
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
																				<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_THREAD_NUM_TIPS']; ?>" data-original-title="" title="">
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
									</div>
								</div>
								<div class="tab-pane" id="tab4" style="height: 100%;">
									<div class="tab-pane__body">
										<div class="tab-pane__body__form">
											<div class="alert alert-danger display-none jobnametip"></div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']; ?>:</label>
												<div class="col-md-9">
													<div class="input-icon right">
														<i class="fa"></i>
														<input type="text" maxlength="64" value=<?php echo $LANG['WEB_PT_OP_RECOVERY_CREATE'] ?> class="form-control" id="volCdpRecoverName" onkeyup="customInputValidate('string', this.value, $(this))" />
														<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']; ?></span>
													</div>
												</div>
											</div>

											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_RECOVERY_DATA_SOURCE_CLIENT']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  datasourcehostshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_RECOVER_DATA']; ?>:</label>
												<div class="col-md-9">
													<table border="0" class="borderTableColorGreen" style="border-color:green;color: green;width:98%; margin: 5px !important;" id="recoverDataTable">
														<thead>
															<tr role="row" class="heading">
																<th width="50%">
																	<?php echo $LANG['UI_VOL_CDP_RECOVERY_VOL']; ?>
																</th>
																<th width="50%">
																	<?php echo $LANG['UI_VOL_CDP_RECOVER_TIME']; ?>
																</th>
															</tr>
														</thead>
													</table>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_MACHINE']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  recovertargethostshow">
													</p>
												</div>
											</div>

											<div class="form-group mb0 mt10 display-none diskgenviewdiv">
												<label class="control-label col-md-3"><?php echo $LANG['WEB_OS_RESTORE_VOLUME']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  diskgenviewshow">
													</p>
												</div>
											</div>

											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_RECOVER_MAP']; ?>:</label>
												<div class="col-md-9">
													<table border="0" class="borderTableColorGreen" style="border-color:green;color: green;width:98%; margin: 5px !important;" id="recoverTargetConfTable">
														<thead>
															<tr role="row" class="heading">
																<th width="50%">
																	<?php echo $LANG['UI_VOL_CDP_DATA_SOURCE_VOL']; ?>
																</th>
																<th width="50%">
																	<?php echo $LANG['UI_RECOVERY_GOAL']; ?>
																</th>
															</tr>
														</thead>
													</table>
												</div>
											</div>

											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_TYPE']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  cdprecoverymode">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 threadnumdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  threadNumshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  speedlimitstrategy">
													</p>
												</div>
											</div>

											<div class="form-group mb0 mt10 transferNetworkdesdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  transferNetworkdes">
													</p>
												</div>
											</div>
											<!-- 传输加密 -->
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  encrypttransferdes">
													</p>
												</div>
											</div>
											<!-- 加密算法 -->
											<div class="form-group mb0 mt10 display-none transferencryptmethod">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  transferencryptmethoddes">
													</p>
												</div>
											</div>
											<!-- 传输压缩 -->
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_COMPRESS_TRANSFER']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  compresstransferdes">
													</p>
												</div>
											</div>
											<!-- 压缩等级 -->
											<div class="form-group mb0 mt10 display-none compresspriority">
												<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  compressprioritydes">
													</p>
												</div>
											</div>
											<!-- 传输数据包大小  -->
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  transferdatapackagesizedes">
													</p>
												</div>
											</div>
											<!-- 重连次数  Bug #14474需求暂时屏蔽-->
											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RECONNECT_TIMES'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  recoveryreconnecttimesdes">
													</p>
												</div>
											</div>
											<!-- 重连间隔时间 Bug #14474需求暂时屏蔽 -->
											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  recoveryreconnectintervaldes">
													</p>
													<span class="form-control-static"><?php echo $LANG['WEB_UTILS_SECOND'] ?></span>
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
										<i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']; ?> </a>
									<a href="javascript:;" class="btn green-turquoise button-next">
										<?php echo $LANG['UI_PUBLIC_NEXT_STEP']; ?> <i class="viconfont vicon-xiayibu"></i>
									</a>
									<a href="javascript:;" class="btn green-haze button-submit">
										<?php echo $LANG['UI_PUBLIC_SUBMIT']; ?> <i class="viconfont vicon-xiayibu"></i>
									</a>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>

		<!-- BEGIN RATE LIMIT MODAL VIEW-->
		<div id="speedlimitModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']; ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					<div class="form-group ">
						<label class="control-label col-md-2"> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE']; ?>
						</label>
						<div class="col-md-6">
							<select class="form-control select2me inline-block" id="speedModeType" style="width:203px;">
								<option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY']; ?></option>
								<option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER']; ?></option>
							</select>
							<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_TYPE_TIPS']; ?>">
								<i class="viconfont vicon-tishi"></i>
							</a>
						</div>
					</div>
					<div class="form-group setSpeedStrategy">
						<label class="control-label col-md-2"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY']; ?>
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
						<label class="control-label col-md-2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE']; ?>
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
								<a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS']; ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>

					</div>
				</div>
			</div>

			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
				<button type="button" class="btn btn-primary" id="speed_submit"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
			</div>
		</div>
		<!-- END RATE LIMIT MODAL VIEW -->
	</div>
</div>
<script src="./assets/global/plugins/echarts/echarts.min.js"></script>
<script type="text/javascript" src="./scripts/public/strategy.js"></script>

<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<!-- <script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script> -->

<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script src="./scripts/volcdp/vol_cdp_recover.js"></script>