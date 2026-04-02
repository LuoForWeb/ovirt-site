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
		<a class="ajaxify" name="cdp_takeover" href="./content/volcdp/takeover.php">
			<span><?php echo $LANG['UI_PLATFORM_TAKEOVER'] ?></span>
		</a>
	</li>
	<span>
		>
	</span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_CDP_REEL_BACKUP'] ?></span>
</h3>
<!-- END PAGE HEADER-->
 
<div class="row recover-page">
	<div class="col-md-12" style="height:100%;">
		<input id="s_vmuuid" value="<?php echo $_GET['vmuuid']; ?>" class="display-none"></input>
		<input id="s_vcenteruuid" value="<?php echo $_GET['vcenteruuid']; ?>" class="display-none"></input>
		<input id="s_showtype" value="<?php echo $_GET['showtype']; ?>" class="display-none"></input>
		<input id="s_hypervisor" value="<?php echo $_GET['hypervisor']; ?>" class="display-none"></input>
		<div class="portlet box blue-hoki" id="voltakeovercontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-vol_cdp_takeover"></i>
					<?php echo $LANG['UI_VOL_CDP_MANUAL_TAKEOVER_TASK']; ?>
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
											<?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_SOURCE']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
										<span class="number">2 </span>
										<span class="desc">
											<?php echo $LANG['UI_PLATFORM_VOL_CDP_TAKEOVER_TITLE']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab3" data-toggle="tab" class="step">
										<span class="number">3 </span>
										<span class="desc">
											<?php echo $LANG['UI_CLIENT_APPLICATION_CONFIG']; ?>
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

											<div class="form-group takeoverdatadiv">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_SOURCE_CLIENT']; ?></label>
												<div class="col-md-9">
													<div class="portlet-body">
														<div class="min-height150">
															<div class="table-container">
																<div class="table-container">
																	<table class="table table-striped table-bordered table-hover" id="takeoverDatasourceHostTable">
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
																				<th width="35%">
																					<?php echo $LANG['UI_VOL_CDP_TASK']; ?>
																				</th>
																				<th width="25%">
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
												<div class="col-md-9 ">
													<div class="portlet-body mb-15">
														<div class="alert alert-block alert-info fade in" id="">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
															<ol class="alert-ol">
																<li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TIPS'] ?>
																</li>
																<li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TIPS_REMARK'] ?>
																</li>
															</ol>
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
											<div class="form-group display-none storageObjectView">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_VOL_CDP_STORAGE_OBJ']; ?></label>
												<div class="col-md-3">
													<select class="form-control select2me display-none" id="takeoverDataSourceSelect">
														<option value="1" selected><?php echo $LANG['UI_VOL_CDP_BAK_SERVER']; ?></option>
														<!-- <option value="2"><?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE']; ?></option> -->
													</select>
												</div>
												<div class="col-md-2 mt5">
													<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_STORAGE_OBJ_TAKEOVER_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											<!-- 
														 如果选择备机,接管时间配置不可展开,默认设置最新时间点. 选择服务端,时间点可编辑,当前不考虑数据对象只为备机的情况.
											-->
											<div class="form-group takeovertimediv">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_BACKUP_SET_DES']; ?></label>
												<div class=" col-md-9 accordion conftimepointmap">
													<div class="panel panel-default strategy-panel">
														<div class="panel-heading">
															<h4 class="panel-title">
																<a class="accordion-toggle accordion-toggle-styled  popovers takevoertimeview" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".conftakeovertime" href="#takeover_timepoint_view" aria-expanded="true">
																	<i class="fa fa-desktop font-green-seagreen"></i>
																	<span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TIME_FRAME']; ?></span>
																	<span class=" timerange fs12">--</span>

																	<span class="control-label colorgreen fs12"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_TIME']; ?></span>
																	<span class="strategyDes fs12 takeoverTimepointDes">--</span>
																</a>
															</h4>
														</div>
														<div id="takeover_timepoint_view" class="panel-collapse collapse in takeovertimecollapseview">
															<div class="panel-body" style="padding: 16px;">
																<div class="tabbable tabbable-custom">
																	<ul class="nav nav-tabs" id="timepointType">
																		<li class="active" id="takeoverAnytimeLi">
																			<a href="#takeoverAnytime" data-toggle="tab" aria-expanded="true">
																				<i class="fa fa-area-chart"></i><?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT']; ?> </a>
																		</li>
																		<li class="" id="takeoverTagPointLi">
																			<a href="#takeoverTagPoint" data-toggle="tab" aria-expanded="false">
																				<i class="viconfont vicon-storage_manager "></i> <?php echo $LANG['UI_VOL_CDP_LABEL_POINTS']; ?> </a>
																		</li>
																		<!-- 
																		  -- 2024-08-13 20:52:15 因后台程序复杂的，导致监控数据不准确，暂时屏蔽
																		<li class="" id="agentEventInfoLi">
																			<a href="#agentEventInfo" data-toggle="tab" aria-expanded="false">
																				<i class="fa fa-flag "></i> <?php echo $LANG['UI_VOL_CDP_EVENT_INFO']; ?> </a>
																		</li>
																		-->
																	</ul>

																	<div class="tab-content border-bottom-none">
																		<div class="tab-pane active " id="takeoverAnytime">
																			<div class="portlet-body min-height300" id="tabPortletBody">
																				<div class="form-group" style="margin-bottom: 10px;">
																					<label class="control-label col-md-2 diynodelabel"><?php echo $LANG['UI_VOL_CDP_TIME_FRAME']; ?></label>
																					<div class="col-md-2 width200">
																						<select class="volcdp-time-range-select select2me" id="changeTimeInterval">
                                                                                            <!-- <option value="1" selected><?php echo $LANG['UI_VOL_CDP_RECENT_TENMINS']; ?></option> -->
																							<option value="2" selected><?php echo $LANG['UI_VOL_CDP_RECENT_ONEHOUR']; ?></option>
																							<option value="3"><?php echo $LANG['UI_TENANT_HOME_HISTORY_PROTECT1']; ?></option>
																							<option value="4"><?php echo $LANG['UI_TENANT_HOME_HISTORY_PROTECT7']; ?></option>
																							<option value="5"><?php echo $LANG['UI_TENANT_HOME_HISTORY_PROTECT30']; ?></option>
																							<option value="6"><?php echo $LANG['UI_VOL_CDP_RECENT_90dayes']; ?></option>
																							<option value="7"><?php echo $LANG['UI_VOL_CDP_TIME_RANGE']; ?></option>
																						</select>
																					</div>
																				</div>
																				<!-- 时间点范围 -->
																				<div class="form-group display-hide selecttakeovertimeview" style="margin-bottom: 0;">
																					<label class="control-label col-md-2"></label>
																					<div class="col-md-10 time-select-group">
																						<div class="time-select-group__item">
																							<div class="input-group date width200 takeovertimeview">
																								<input type="text" style="display:none;">
																								<input type="text" placeholder="<?php echo $LANG['UI_VOL_CDP_CONFIGURE_START_TIME']; ?>" size="16" id="selectTakeoverStartTime" class="form-control input-sm">
																								<span class="input-group-btn">
																									<!--<button class="btn default input-sm" id="resetStartTimePoint" type="button"><i class="fa fa-times"></i></button> -->
																									<button class="btn default date-set input-sm-vol-cdp" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
																								</span>
																							</div>
																						</div>
																						<div class="time-select-group__item">
																							<div class="input-group date width200 takeovertimeview">
																								<input type="text" style="display:none;">
																								<input type="text" placeholder="<?php echo $LANG['UI_VOL_CDP_CONFIGURE_END_TIME']; ?>" size="16" id="selectTakeoverEndTime" class="form-control input-sm">
																								<span class="input-group-btn">
																									<!-- <button class="btn default input-sm" id="resetEndTimepoint" type="button"><i class="fa fa-times"></i></button>-->
																									<button class="btn default date-set input-sm-vol-cdp inputendtime" id='inputendtime' type="button"><i class="viconfont vicon-ge_calendar"></i></button>
																								</span>
																							</div>
																						</div>
																						<div class="time-select-group__btns">
																							<button class="btn default input-sm" id="resetSelectTimePoint" type="button" title="<?php echo $LANG['UI_VOL_CDP_RESET']; ?>"><i class="fa fa-times"></i></button>
																							<button class="btn default input-sm green-turquoise" id="confirmSelectTimePoint" type="button" title="<?php echo $LANG['UI_VOL_CDP_CONFIRM']; ?>"><i class="fa fa-check"></i></button>
																						</div>
																					</div>
																				</div>
																				<div id="volCdpTimeline"></div>
																			</div>

																			<div class="pt30 floatl width100p mb-15 mb-0_en">
																				<label class="control-label col-md-2 ml-70 ml-0_en"><?php echo $LANG['UI_VOL_CDP_TIME_TYPE']; ?>:</label>
																				<div class="col-md-4">
																					<p class="form-control-static colorgreen" id="inputTakeoverTimepoindes">
																						<?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT']; ?>
																					</p>
																				</div>
																			</div>
																		</div>

																		<div class="tab-pane " id="takeoverTagPoint">
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
																							<table class="table table-striped table-bordered table-hover" style="word-break:break-all;" id="takeoverTagpointTable">
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
																		<div class="tab-pane " id="agentEventInfo">
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

											<!-- 接管时间点 -->
											<div class="form-group">
												<label class="control-label col-md-3 diynodelabel takeovertimepoint"><?php echo $LANG['UI_VOL_CDP_VERIF_TIME_POINT']; ?></label>
												<div class="col-md-5 form-group-timepoint">
													<div class="input-group date takevoertimepointview" id="takevoerTimepointDiv">
														<input type="text" style="display:none;">
														<input type="text" size="16" id="inputTakeoverTimepoint" class="form-control input-sm">
														<span class="input-group-btn">
															<button class="btn default input-sm-vol-cdp" id="closetakeoverTimepoint" type="button"><i class="fa fa-times"></i></button>
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
											
											<!-- 选择应用场景 -->
											<div class="form-group  appscenediv">
												<label class="control-label col-md-3 appscene-label form-group-label"><?php echo $LANG['UI_VOL_CDP_TEMP_USE_SCENE']; ?></label>
												<div class="col-md-3">
													<select class="form-control select2me" id="changeAppScened">
														<option value="100" selected><?php echo $LANG['UI_VOL_CDP_STANDBY_USE_TAKEOVER']; ?></option>
														<!-- <option value="99" ><?php echo $LANG['UI_VOL_CDP_STANDBY_USE_VERIFY']; ?></option> -->
													</select>
												</div>
												<div class="col-md-1 margintop10">
													<a class="popovers ml-10 " data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TEMP_USE_SCENE_DESC']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>

											<!-- 选择备机类型 -->
											<div class="form-group  dualmachineimageswitchdiv display-none">
												<label class="control-label col-md-3 changestandbytype form-group-label"><?php echo $LANG['UI_VOL_CDP_VERIFY_TYPE']; ?></label>
												<div class="col-md-3">
													<select class="form-control select2me" id="takeoverStandbyHostMode">
														<!--【代理客户端】替换成【挂载接管】 -->
														<option value="3" ><?php echo $LANG['UI_VOL_CDP_COMPLETE_MACHINE_VERIFY_DESC']; ?></option>
														<option value="1" selected><?php echo $LANG['UI_VOL_CDP_MOUNT_VERIFY_DESC']; ?></option>
													   
													</select>
												</div>
												<div class="col-md-1 margintop10">
													<a class="popovers ml-10 " data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_STANDBY_TYPE_DESC'] ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											<!-- 备机配置-vm -->
											<div class="form-group  takeoverStandbyconfdiv">
												<label class="control-label col-md-3 standbyconflabel form-group-label"><?php echo $LANG['UI_VOL_CDP_STANDBY_CONF']; ?></label>
												<div class="col-md-9">
													<button type="button" class="btn green-haze" id="standbyMapConf"><?php echo $LANG['UI_VOL_CDP_STANDBY_CONF_MAP']; ?></button>

													<ul id="takeoverBuiltInVmConfDes" class="pl0">

													</ul>

												</div>
											</div>
											<!--代理客户端 -->
											<div class="form-group display-none takeoverTargetView">
												<label class="control-label col-md-3 diynodelabel takeoverhostlabel"><?php echo $LANG['UI_VOL_CDP_VERIFY_STANDBY_CONF']; ?></label>
												<div class="col-md-3">
													<select class="form-control select2me" id="takeoverTargetHostSelect">
													</select>
													<p class="form-control-static colorgreen width600 targetHostUsedPartition">
													</p>
												</div>
											</div>
											
											<!-- 接管网络配置 -->
											<div class="form-group applianceselectview display-none">
												<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></label>
												<div class="col-md-9">
													<button type="button" class="btn green-haze takeovernetworkconf" id=""><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></button>
													<ul id="" style="padding-left: 0;" class="hm_takeover_ip_map_list width100p">
													</ul>
												</div>
											</div>

											<!-- 增加漂移IP提示信息 -->
											<div class="form-group  display-none takeoveripdriftview  mt-15">
												<label class="control-label col-md-3 applianceselectlabel"></label>
												<div class="col-md-9">
													
													<ul id="" style="padding-left: 0;" class=" width100p help-block font-danger">
														<?php echo $LANG['UI_VOL_CDP_TAKEOVER_IP_DRIFT_TIPS']; ?>
													</ul>
												</div>
											</div>

											<div class="form-group takeovervoldiv">  
												<label class="control-label col-md-3 takeovervollabel"><?php echo $LANG['UI_VOL_CDP_VERIFY_VOLUME']; ?></label>
												<div class="col-md-9 ">
													<div class="portlet-body">
														<div class="min-height150">
															<div class="table-container">
																<div class="table-container">
																	<table class="table table-striped table-bordered table-hover" id="takeOverVolTable">
																		<thead>
																			<tr role="row" class="heading">
																				<th width="5%">
																					<input type="checkbox" class="group-checkable" >
																				</th>
																				<th width="20%">
																					<?php echo $LANG['UI_VOL_CDP_NAME']; ?>
																				</th>
																				<th width="15%">
																					<?php echo $LANG['UI_PUBLIC_CAPACITY']; ?>
																				</th>
																				<th width="25%">
																					<?php echo $LANG['UI_DATA_TIMEPOINT']; ?>
																				</th>
																				<th width="35%" id="volTargetMountPoint">
																					<?php echo $LANG['UI_VOL_CDP_TARGET_POINT']; ?>
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
											<div class="form-group ">
												<label class="control-label col-md-3  diynodelabel"><?php echo "" ?></label>
												<div class="col-md-9 mb-15">
													<div class="portlet-body ">
														<div class="alert alert-block alert-info fade in" id="">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
															<ol class="alert-ol">
																<li>
																	<?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_TIPSONE']; ?>
																</li>
																<li>
																	<?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_TIPSTWO']; ?>
																</li>
																<li>
																	<?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_TIPSTHREE']; ?>
																</li>
																<li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_MOUNT_POINT_TIPS']; ?>
																</li>
															</ol>
														</div>

													</div>
												</div>
											</div>

										</div>
									</div>
								</div>

								<div class="tab-pane" id="tab3">
									<div class="row row-stepthree" style="overflow-x: hidden;overflow-y: auto;">
										<div class="col-md-9">
											<div class="form-group takeoverappdiv">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_TAKEOVER_APPLICATION']; ?></label>
												<div class=" col-md-9 accordion conftakeoverappmap">
													<div class="panel panel-default strategy-panel">
														<div class="panel-heading">
															<h4 class="panel-title">
																<a class="accordion-toggle accordion-toggle-styled  popovers takeoverappview" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".conftakeoverapp" href="#takeoverAppView" aria-expanded="true">
																	<i class="fa fa-desktop font-green-seagreen"></i>
																	<span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_APPLICATION_HOST']; ?></span>
																	<span class="strategyDes takeoverAppDes">--</span>
																</a>
															</h4>
														</div>
														<div id="takeoverAppView" class="panel-collapse collapse in takeoverappcollapseview">
															<div class="panel-body" style="padding: 16px;">
																<div class="tabbable tabbable-custom " style="overflow: visible;margin-bottom: 0px;">
																	<div class="tab-content" style="overflow-y: auto;border:none;padding:0;">
																		<div class="alert alert-block alert-info fade in display-hide" id="noTakeoverApp">
																			<button type="button" class="close" data-dismiss="alert"></button>
																			<ul class="alert-ul">
																				<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																				<li>
																					<?php echo $LANG['UI_VOL_CDP_TAKEOVER_TASK_APPLICATION_ALERT']; ?>
																				</li>
																			</ul>
																		</div>
																		<ul id="takeover_app_tree" class="ztree"></ul>
																	</div>
																</div>
															</div>
														</div>


													</div>
												</div>
											</div>

											<div class="form-group takeoverscriptdiv display-none">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_CONGIGURE_EXEC_SCRIPT']; ?></label>
												<div class="col-md-9 accordion ">
													<div class="panel panel-default strategy-panel">
														<div class="panel-heading">
															<h4 class="panel-title">
																<a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#takeoverScriptView" aria-expanded="true">
																	<i class="fa fa-file-code-o font-green-seagreen"></i>
																	<span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_CUSTOM_SCRIPT_CONFIGURE']; ?></span>
																	<span class="strategyDes takeoverScriptDes"></span>
																</a>
															</h4>
														</div>
														<div id="takeoverScriptView" class="panel-collapse collapse in ">
															<div class="panel-body">
																<!-- 手动接管自定义脚本接管开关 -->
																<div class="form-group">
																	<label class="control-label col-md-3 apptakeoverlabel form-group-label"><?php echo $LANG['UI_VOL_CDP_SCRIPT']; ?></label>
																	<div class="col-md-4 form-group-content">
																		<input type="checkbox" id="handover_script_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																		<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_BACKUP_CUSTOM_TIP']; ?>">
																			<i class="viconfont vicon-tishi"></i>
																		</a>
																	</div>
																</div>
																<div class="form-group display-hide handover_script_conf_view">
																	<div class="form-group">
																		<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_SCRIPT_PATH']; ?> </label>
																		<div class="col-md-7">
																			<input type="text" value="" class="form-control" id="handoverScriptPath" placeholder="<?php echo $LANG['UI_VOL_CDP_CONFIGURE_SCRIPT_PATH_PLACEHOLDER']; ?>" />
																		</div>
																		<div class="col-md-2 ml-20">
																			<button type="button" id="addHandoverScriptBut" class="btn btn-sm green-haze ml10">
																				<i class="fa fa-plus-square"></i>&nbsp;<?php echo $LANG['UI_VOL_CDP_ADD_SCRIPT']; ?>
																			</button>
																		</div>
																	</div>
																	<div class="form-group">
																		<div class="col-md-3"></div>
																		<div class="col-md-7">
																			<ul id="handoverScriptList" style="padding-left: 0;">
																			</ul>
																		</div>
																	</div>
																</div>

															</div>
														</div>
													</div>
												</div>
											</div>

											<!-- 网络传输 -->
											<div class="form-group display-none transfernetworkDiv">
												<label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?></label>
												<div class="col-md-4">
													<select class="form-control select2me" id="transferNetwork">
													</select>
												</div>
												<div class="col-md-2 mt10">
													<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											<!--
											<div class="form-group hostIPCutView">
												<label class="control-label col-md-3 diystoragelabel form-group-label"><?php echo $LANG['UI_VOL_CDP_HOST_IP_DRIFT']; ?></label>
												<div class="col-md-6 form-group-content">
													<div class="col-md-10 ml-15">
														<input type="checkbox" id="hostIpCutSwitch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
														<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_HOST_IP_DRIFT_TIPS']; ?>">
															<i class="viconfont vicon-tishi"></i>
														</a>
													</div>
													<div class="col-md-12 ml-15">
														<p class="form-control-static colorgreen hostipcutinfo">
														</p>
													</div>
												</div>
											</div>
											-->
											<!-- 接管网络配置 -->
<!--										<div class="form-group applianceselectview display-none">-->
<!--											<label class="control-label col-md-3 applianceselectlabel">--><?php //echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?><!--</label>-->
<!--											<div class="col-md-9">-->
<!--												<button type="button" class="btn green-haze takeovernetworkconf" id="">--><?php //echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?><!--</button>-->
<!--												<ul id="" style="padding-left: 0;width:90%;" class="hm_takeover_ip_map_list">-->
<!--												</ul>-->
<!--											</div>-->
<!--										</div>-->

											<div class="form-group display-none">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_TAKEOVER_IP']; ?></label>
												<div class="col-md-9">
													<div class="input-icon right">
														<i class="fa"></i>
														<input type="text" maxlength="64" value="" class="form-control" id="takeoverCutBackIp" placeholder="<?php echo $LANG['UI_VOL_CDP_TAKEOVER_IP_PLACEHOLDER']; ?>" />
													</div>
												</div>
											</div>

											<div class="form-group ">
												<label class="control-label col-md-3 diynodelabel"><?php echo "" ?></label>
												<div class="col-md-9 mt25">
													<div class="portlet-body">
														<div class="alert alert-block alert-info fade in" id="">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
															<ol class="alert-ol">
																<li class="takeovertips-one">
																	<?php echo $LANG['UI_VOL_CDP_APPLICATION_TAKEOVER_TIPSONE']; ?>
																</li>
																<li class="takeovertips-two display-none">
																	<?php echo $LANG['UI_VOL_CDP_APPLICATION_TAKEOVER_TIPSTWO']; ?>
																</li>
															</ol>
														</div>

													</div>
												</div>
											</div>
										</div>

									</div>
								</div>

								<div class="tab-pane" id="tab4">
									<div class="tab-pane__body">
										<div class="tab-pane__body__form col-md-8_en">
											<div class="alert alert-danger display-none jobnametip"></div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_JOB_RNAME']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<div class="input-icon right">
														<i class="fa"></i>
														<input type="text" maxlength="64" value="<?php echo $LANG['UI_VOL_CDP_TAKEOVER_TASK'] ?>" class="form-control" id="volCdpTakeoverName" onkeyup="customInputValidate('string', this.value, $(this))" />
														<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']; ?></span>
													</div>
												</div>
											</div>

											<h4 class="form-section"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_SOURCE']; ?></h4>
											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_VOL_CDP_STORAGE_NODE']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen storageNodeShow">
													</p>
												</div>
											</div>

											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_RECOVERY_GOAL_STORAGE']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen storeinfoshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_SOURCE_CLIENT']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen datasourcehostshow">
													</p>
												</div>
											</div>
											<h4 class="form-section volcdptaskdatatypedesc"><?php echo $LANG['UI_PLATFORM_VOL_CDP_TAKEOVER_TITLE']; ?></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_POINT']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen takeoverTimePointShow">
													</p>
												</div>
											</div>
											<!--应用场景-当备机为内嵌虚拟机时才需要显示配置信息 -->
											<div class="form-group mb0 mt10  appsceneview">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_VOL_CDP_TEMP_USE_SCENE']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen appsceneconftext">
													</p>
												</div>
											</div>

											<!--验证/接管类型-->
											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3 standbyTypeLabel col-md-4_en"><?php echo $LANG['UI_VOL_CDP_VERIFY_TYPE']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen standbyTypeShow">
													</p>
												</div>
											</div>

											<!-- 目标主机配置 -->
											<div class="form-group mb0 mt10 takeoverTargetdiv">
												<label class="control-label col-md-3 takeovertargetlabel col-md-4_en"><?php echo $LANG['UI_VOL_CDP_VERIFY_STANDBY_CONF']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen takeoverTargetShow">
													</p>
												</div>
											</div>
											<!-- 备机配置 -->
											<div class="form-group mb0 mt10 display-none vmTakeoverTargetDiv">
												<label class="control-label col-md-3 standbyconflabelsummary col-md-4_en"><?php echo $LANG['UI_VOL_CDP_VERIFY_STANDBY_CONF']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<div class="form-control-static colorgreen" id = "vmTargetShow"></div>
												</div>
											</div>
											
											<!-- 配置接管网络 -->
											<div class="form-group mb0 mt10 display-none takeoverbusinessipmapview">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen takeoverbusinessipmapshow">
													</p>
												</div>
											</div>
											<!-- 接管卷 -->
											<div class="form-group mb0  mt-25">
												<label class="control-label col-md-3 taskSummaryEventVolumeDesc col-md-4_en"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_VOL']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen takeoverVolInfoShow">
													</p>
												</div>
											</div>

											<h4 class="form-section"><?php echo $LANG['UI_CLIENT_APPLICATION_CONFIG']; ?></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_TAKEOVER_APPLICATION']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen takeoverAppShow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 display-none handoverscriptviewdiv">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_CUSTOM_SCRIPT']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen handoverScriptShow">
													</p>
												</div>
											</div>

											<div class="form-group mb0 mt10 display-none handoverscriptconfdiv">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_SCRIPT_INFO']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen handoverscriptconfinfo">
													</p>
												</div>
											</div>

											<!--
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_HOST_IP_DRIFT']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static colorgreen hostIpCutShow">
													</p>
												</div>
											</div>
											-->

											<div class="form-group mb0 mt10 transfernetworkdiv">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen transferNetwork">
													</p>
												</div>
											</div>

											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_TAKEOVER_IP']; ?>:</label>
												<div class="col-md-9 col-md-8_en">
													<p class="form-control-static colorgreen takeoverServerIpShow">
													</p>
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

		<!-- 内置虚拟机 -->
		<div id="vm_machine_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-beiji"></i> <?php echo $LANG['UI_VOL_CDP_TAKE_TEMP_AGENT_DESC']; ?>
				</h4>
			</div>
			<div class="modal-body">
				<?php include_once(ROOT_PATH . '/content/platform/vm_machine/machine.php'); ?>
			</div>
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default" id="builtInVmClose"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
				<button type="button"class="btn btn-primary" id="builtInVmSubmit"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
			</div>
		</div>

		<!-- 接管網絡配置--modal START -->
		<div id="hmTakeoverNetworkConfModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_VOL_CDP_HOST_IP_SERIVCE_CONF']; ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_TASK_IP_SERVICE_CONF']; ?></label>
					<div class="col-md-10">
						<div class="portlet">
							<div class="portlet-body panel panel-default strategy-panel">
								<div class="panel-group accordion mt10 min-height150" style="overflow-y:auto" id="hm_takeover_ip_server_conf">

								</div>
								<div class="alert alert-block alert-info fade in mt-20" style="margin-bottom:0px;" id="">
									<button type="button" class="close" data-dismiss="alert"></button>
									<ul class="alert-ul">
										<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
										<li><?php echo $LANG['UI_VOL_CDP_NET_CARD_CONF_TIP']; ?> </li>
									</ul>
								</div>
							</div>
						</div>
					</div>

					<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_STANDBY_GATEWAY_CONF']; ?></label>
					<div class="col-md-10">
						<div class="portlet">
							<div class="portlet-body panel panel-default strategy-panel">
								<div class="panel-group accordion mt10 min-height150" style="overflow-y:auto" id="hm_standby_gateway_conf">

								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
				<button type="button" class="btn btn-primary" id="submit_hm_takeover_network_conf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
			</div>
		</div>
		<!-- 接管網絡配置  --modal end> -->

	</div>
</div>

<script src="./assets/global/plugins/echarts/echarts.min.js"></script>
<script src="./scripts/public/strategy.js"></script>

<script src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script src="./scripts/plugins/jquery/jquery.strategy.js"></script>

<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./scripts/plugins/datatable.js"></script>
<script src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<!--<script  src="./scripts/plugins/echarts.min.js"></script>-->
<script src="./scripts/libs/md5.js"></script>
<script src="./scripts/volcdp/vol_cdp_takeover.js"></script>
<script src="./scripts/volcdp/takeover_ip_server_map.js"></script>