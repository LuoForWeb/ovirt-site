<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<!-- <link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" /> -->
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<!-- <link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" /> -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/driver_check.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/tree-grid/jquery.treegrid.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/network_config.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css" />
<link href="/assets/global/plugins/select2/select2-4.1.0/css/select2.min.css"rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/timePointDetail/css/timePointDetail.css" />

<!-- END PAGE LEVEL STYLES -->
<style>
	.recover_radio input[type="radio"]{
		margin: 0;
		margin-right: 8px;
		outline: none;
		appearance: none;
		width: 16px;
		height: 16px;
		border-radius: 50% !important;
		border: solid 1px #999999;
		box-sizing: border-box;
		vertical-align: middle;
	}
	.recover_radio input[type="radio"]:checked, .virus-item input[type="radio"]:active, .virus-item input[type="radio"]:active:checked{
		background-image: url(../../../img/platform/vinblue.svg) !important;
		background-position: -57px -12px !important;
		border: 0 !important;

	}
	div[class*='icheckbox_'], div[class*='iradio_']{
		top: 2px !important;
	}

	#machineOsrecovercontent .select-special .selection{
		height: 34px;
		display: inline-block;
		width: 100%;
		line-height: 34px;
	}
	#machineOsrecovercontent .select-special .select2-container{
		height: 34px;
		display: inline-block;
		width: 100% !important;
		line-height: 34px;
	}
	#machineOsrecovercontent .select-special .select2-selection{
		height: 34px;
		display: inline-block;
		width: 100% !important;
		line-height: 34px;
		border: 1px solid #e6e6e6;
		border-radius: 4px !important;
	}
	.select2-dropdown,.select2-container--default .select2-search--dropdown .select2-search__field {
		border: 1px solid #e6e6e6;
	}
	.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{
		background-color: #F0F0F0;
		color: #333;
	}


</style>

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
	<span class="curent"><?php echo $LANG['UI_MACHINE_OS_MACHINE_RECOVERY'] ?></span>
</h3>
<!-- END PAGE HEADER-->
 
<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
	<input id="externalPointUuid" value="<?php echo $_GET['point_uuid']; ?>" class="display-none">
	<input id="externalTaskUuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none">
	<input id="externalItemUuid" value="<?php echo $_GET['item_uuid']; ?>" class="display-none">
	<div class="col-md-12" style="height:100%;">
		<div class="portlet box blue-hoki" id="machineOsrecovercontent" style="height:100%;margin-bottom:0;">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-huifu1"></i><?php echo $LANG['WEB_MACHINE_OS_CREATE_RECOVERY_TASK'] ?>
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
									<a href="#tab3" data-toggle="tab" class="step">
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
																<input type="text" maxlength="128" placeholder="<?php echo $LANG['WEB_MACHINE_OS_SEARCH_TASK_OR_OS'] ?>" class="form-control" id="searchos" />
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
																	<a class="ajaxify alert-link" name="complete_machine" href="./content/complete_machine_os/machine_os_backup.php"><?php echo $LANG['UI_RECOVERY_MACHINE_NO_TIMEPOINT_TIPS'] ?></a>
																</li>
															</ol>
														</div>
														<!-- 搜索后没有找到资源给出提示内容 -->
														<div class="alert alert-block alert-info fade in display-hide mt10 me-12" id="nosearchtips">
															<button type="button" class="close" data-dismiss="alert"></button>
															<ul class="alert-ul">
																<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																<li>
																	<?php echo $LANG['UI_RECOVERY_NO_VM_TITLE'] ?>
																	<a class="ajaxify alert-link" name="complete_machine" href="./content/complete_machine_os/machine_os_backup.php"><?php echo $LANG['UI_RECOVERY_MACHINE_NO_TIMEPOINT_TIPS'] ?></a>
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
								<div class="tab-pane  waitInitDiv" id="tab2">	
									<div class="row tab-pane__row">
										<div class="col-md-10 col-steptwo">
										<!-- 恢复方式-->	
										<div class="form-group">
											<label class="control-label col-md-3">
												<span class="required">* </span>
												<?php echo $LANG['UI_RECOVERY_TYPE'] ?>
											</label>
											<div class="col-md-6">
												<div class="radio-group" id="radio_group_1">
													<label class="radio-group__item me-20 active" name="recoverType" value="completeMachineRecovery" style="width: 140px;"><i class="viconfont vicon-overview-complete-machine"></i><?php echo $LANG['UI_MACHINE_OS_MODE_MACHINE_RECOVERY'] ?></label>
													<label class="radio-group__item" name="recoverType" value="dataVolumeRecovery" style="width: 140px;"><i class="viconfont vicon-cipan1"></i><?php echo $LANG['UI_MACHINE_OS_MODE_DATA_VOLUME_RECOVERY'] ?></label>
													<div class="col-md-2" style="margin-top: -12px !important;display: none;">
														<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="hhhh" data-original-title="" title="">
															<i class="viconfont vicon-tishi"></i>
														</a>
													</div>
												</div>
											</div>

										</div>
										<!-- 目标机配置-->	
										<div class="form-group">
											<label class="control-label col-md-3">
												<span class="required">* </span>
												<?php echo $LANG['UI_MACHINE_OS_TARGET_HOST_CONFIG'] ?>
											</label>
											<div class="col-md-9">
													<div class="add-list">
														<div class="accordion targetBox" role="tablist">


														</div>
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
													<a href="#tab_common" class="" data-content="<?php echo $LANG['WEB_MACHINE_OS_RECOVERY_STRATEGY'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
												</li>
												<li class="transferLi">
													<a href="#tab_transfer" class="" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_TIPS'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
												</li>
												<li class="otherLi">
													<a href="#tab_safety" class="" data-content="<?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-gaojipeizhi "></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> </a>
												</li>
												<li class="highLi">
													<a href="#tab_high" class="" data-content="<?php echo $LANG['UI_BACKUP_HIGH_CONFIG_DES'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
													<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></a>
												</li>
											</ul>
											<div class="tab-content">
												<div class="tab-pane active" id="tab_common">
													<div class="panel-body">
														<!-- 时间策略 -->
														<div class="form-group">
															<div class="col-md-offset-1  col-md-10 pt15 recoveryTimeDiv">
																<div class="accordion strategyOne" >
																	<div class="panel panel-default strategy-panel">
																		<div class="panel-heading">
																			<h4 class="panel-title">
																				<a class="accordion-toggle accordion-toggle-styled popovers" 
																				data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#recoveryTime" aria-expanded="true">
																				<i class="viconfont vicon-shijian2 font-green-seagreen"></i> 
																				<span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
																				<span class="strategyDes recoveryTimeDes"></span>
																				</a>
																			</h4>
																		</div>
																		<div id="recoveryTime" class="panel-collapse collapse in">
																			<div class="panel-body">
																				<div class="col-md-12">
																					<div class="form-group">
																						<label class="control-label col-md-2"> <?php echo $LANG['UI_RECOVERY_START_TYPE'] ?>
																						</label>
																						<div class="col-md-3">
																							<select class="form-control select2me input-sm" id="recovertype">
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
														<div class="form-group">
															<div id="backupStrategyDiv" class="col-md-10 col-md-offset-1">
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
															<div class="form-group encrypt_method_div" style="display: none;">
																<label class="control-label col-md-3"> <?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="encrypt_method">
																		<option value="1">RSA</option>
																		<option value="2">SM2</option>
																	</select>
																</div>
																<div class="col-md-2 mt10" style="display:none;">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
																	data-placement="right" data-content="none">
																	<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															
															<div class="form-group transfer_threads_div">
																<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?></label>
																<div class="col-md-3">
																	<div id="Socket-thread">
																		<div class="input-group spinner-group">
																			<input type="text" id="transfer_threads" style="text-align: left;" onkeyup="value=value.replace(/[^\d]/g,'')" class="input-sm spinner-input form-control">
																			<div class="spinner-buttons input-group-btn spinner-group-btn">
																				<button type="button" class="input-sm btn spinner-up default">
																					<i class="fa fa-angle-up"></i>
																				</button>
																				<button type="button" class="input-sm btn spinner-down default">
																					<i class="fa fa-angle-down"></i>
																				</button>
																			</div>
																		</div>
																	</div>
																</div>
																<div class="col-md-2 mt10">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
																	data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_THREAD_NUM_TIPS'] ?>">
																	<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>

														</div>
													</div>
												</div>

												<div class="tab-pane" id="tab_safety">
													<div class="panel-body">
														<div class="tabbable-custom pdlr15  col-md-10">
															<!--健康检测-->
															<div class="form-group">
																<div id="virusConfig"></div>
															</div>
															<div class="form-group">
																<div id="completeConfig"></div>
															</div>
														</div>
													</div>
												</div>

												<!-- 高级配置 -->
												<div class="tab-pane " id="tab_high">
													<div class="advance-config-wrap">
														<div class="advance-config-wrap__row row" >
															<div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
																<ul class="nav nav-tabs tabs-left">
																	<li class="active retry_strategy">
																		<a href="#retry_config" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></a>
																	</li>
																	<li class="overload_protect">
																		<a href="#tab_overload_protect" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></a>
																	</li>
																</ul>
															</div>
															<div class="col-md-10 col-sm-10 col-xs-10 advance-config-wrap__row__content">
																<div class="tab-content">
																	<!-- 重试 -->
																	<div id="retry_config" class="tab-pane retry_config_pane active">
																	</div>
																	<!-- 过载保护 -->
																	<div class="tab-pane" id="tab_overload_protect">
																		<div class="col-md-12 advanced-detail-conf-district pt40">
																			<!-- 忽略节点资源限制 -->
																			<div class="form-group">
																				<label class="control-label col-md-3 ignoreResourceLimitLabel form-group-label"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></label>
																				<div class="col-md-1 form-group-content">
																					<input checked type="checkbox" id="ignore_resource_limit"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
																						   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
																						   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																				</div>
																				<div class="col-md-1 vmbackup-mt10_en mt5_cn">
																					<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
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
												<label class="control-label col-md-3"><?php echo $LANG['UI_MICROSOFT365_RECOVERY_TARGET'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  recovershow">
													</p>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_START_TYPE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  recoveryTimeDes">
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
											<div class="form-group mb0 mt10 otherLi">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  safeshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  highshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 retryShow"></div>
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
		
	</div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<!-- <script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script> -->
<!-- <script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script> -->
<!-- <script type="text/javascript" src="./scripts/plugins/datatable.js"></script> -->
<!-- <script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script> -->
<!-- <script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script> -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<!-- <script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script> -->
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<!-- <script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script> -->
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<!-- <script type="text/javascript" src="./scripts/plugins/jquery/jquery.osRecoveryType.js"></script> -->
<!-- <script src="./scripts/plugins/bootstrap-datetimepicker.js" type="text/javascript"></script> -->
<!-- <script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script> -->
<!-- <script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script> -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
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

<script src="./scripts/components/timePointDetail/js/timePointDetail.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/clipboard/clipboard.min.js"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/bootstrap-table-treegrid.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/jquery.treegrid.js"></script>
<script type="text/javascript" src="/assets/global/plugins/select2/select2-4.1.0/js/select2.min.js"></script>
<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/backup-strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/targetPlug.js"></script>
<script type="text/javascript" src="./scripts/platform/component/driver_check.js"></script>
<script type="text/javascript" src="./scripts/platform/component/network_config.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/public/initComponents.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>

<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/complete_machine_os/machine_os_recover.js" type="text/javascript"></script>