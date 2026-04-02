<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<link rel="stylesheet" href="./scripts/components/timePointDetail/css/timePointDetail.css"></link>
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
	<span class="curent"><?php echo $LANG['UI_RECOVERY_FOR_HADOOP'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
	<div class="col-md-12" style="height: 100%;">
		<div class="portlet box blue-hoki" id="hadooprecoverycontent">
			<input id="externalPointUuid" value="<?php echo $_GET['point_uuid']; ?>" class="display-none"></input>
			<input id="externalTaskUuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none"></input>
			<input id="externalItemUuid" value="<?php echo $_GET['item_uuid']; ?>" class="display-none"></input>
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-huifu1"></i><?php echo $LANG['WEB_HADOOP_CREATE_RECOVERY_TASK'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" class="form-horizontal" id="submit_form" method="POST">
					<div class="form-wizard">
						<div class="form-body">
							<div class="form-body-content">
								<ul class="nav nav-pills steps">
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
									<div class="tab-pane active" id="tab1">
										<div class="alert alert-warning display-none selecttimepointtip">
										</div>
										<div class="row tab-pane__row">
											<div class="col-md-4 tab-pane__row__source">
												<div class="form-group src-wrap">
													<div class="src-wrap__title">
														<span>
															<?php echo $LANG['UI_RECOVERY_FILE_SELECT_POINT'] ?>
														</span>
													</div>
													<div class="src-wrap__content">
														<div class="src-wrap__content__search">
															<select class="bs-select width100p form-control" data-show-subtext="true" id="storageselect">
															</select>
															<div class="vm_tree_div">
																<div class="input-icon width100p">
																	<input type="text" maxlength="128" placeholder="<?php echo $LANG['WEB_OS_SEARCH_NAME'] ?>" class="form-control" id="searchfs" onkeyup="customInputValidate('string', this.value, $(this))" />
																	<div class="daterangepickerdiv mt10 file-recovery">
																		<input type="text" id="daterangepicker"
																			class="form-control" autocomplete="off">
																		<i class="viconfont vicon-ge_calendar"></i>
																	</div>
																</div>
															</div>
														</div>
														<div class="src-wrap__content__ztree" style="height: calc(100% - 137px)">
															<div class="fs_tree vcenter-tree">
																<ul id="agent_point_tree" class="ztree"></ul>
															</div>
															<div class="alert alert-block alert-info fade in display-hide" id="nopointtips">
																<button type="button" class="close" data-dismiss="alert"></button>
																<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
																<ol class="alert-ol">
																	<li>
																		<?php echo $LANG['UI_RECOVERY_NO_VM_TITLE'] ?>
																	</li>
																	<li>
																		<a id="tobackup" class="alert-link"><?php echo $LANG['WEB_HADOOP_BACKUP_FIRST_TIPS'] ?></a>
																	</li>
																</ol>
															</div>
															<div class="alert alert-block alert-info fade in display-hide" id="step1tips">
																<button type="button" class="close" data-dismiss="alert"></button>
																<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
																<p>
																	<?php echo $LANG['UI_RECOVERY_FILE_TIPS1'] ?>
																</p>
																<p>
																	<?php echo $LANG['UI_RECOVERY_FILE_TIPS2'] ?>
																</p>
																<br><br>
																<p>
																	<?php echo $LANG['UI_RECOVERY_FILE_TIPS3'] ?>
																</p>
															</div>
															<div class="alert alert-block alert-info fade in display-hide"  id="nosearchtips">
																<button type="button" class="close" data-dismiss="alert"></button>
																<ul class="alert-ul">
																	<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																	<li>
																		<strong><?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?></strong>
																	</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-8 VMList-div">
												<div class="addTitle">
													<span><?php echo $LANG['UI_RECOVERY_FILE_SRC'] ?></span>
												</div>
												<div class="addIt-list">
													<div class="input-group reco-search-group display-none">
														<input id="reco-search-input" type="text" class="form-control" placeholder="<?php echo $LANG['UI_RECOVERY_FILE_SEARCH_INPUT_TIPS'] ?>" aria-describedby="reco-search-btn">
														<span name="start" class="input-group-addon" id="reco-search-btn"><?php echo $LANG['UI_PUBLIC_SEARCH'] ?></span>
													</div>
													<div id="seachdes" class="addIt-list__search display-none">
														<div class="ispinner">
															<div class="ispinner-blade"></div>
															<div class="ispinner-blade"></div>
															<div class="ispinner-blade"></div>
															<div class="ispinner-blade"></div>
															<div class="ispinner-blade"></div>
															<div class="ispinner-blade"></div>
															<div class="ispinner-blade"></div>
															<div class="ispinner-blade"></div>
														</div>
														<span class="countnum"></span>
														<span class="colorgreen selectall" style="cursor: pointer;">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $LANG['UI_RECOVERY_FILE_SELECT_ALL'] ?></span>
														<span class="colorgreen cancelselect" style="cursor: pointer;">&nbsp;&nbsp;<?php echo $LANG['UI_RECOVERY_FILE_DESELECT'] ?></span>
														<span class="colorgreen clearval" style="cursor: pointer;">&nbsp;&nbsp;<?php echo $LANG['UI_RECOVERY_FILE_CLEAR_FILTER'] ?></span>
													</div>
													<div class="recover-source-wrapper__ztree">
														<ul id="recoverFileTree" class="ztree">
														<ul>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="tab-pane" id="tab2">
										<div class="alert alert-warning display-none setrecover2tip">
										</div>
										<div class="row tab-pane__row">
											<div class="col-md-12 col-steptwo-recover" style="overflow: auto;">
												<div class="form-group " id="recoverpath">
													<label class="control-label col-md-4">
														<span class="required">* </span>
														<?php echo $LANG['UI_NAS_RECOVER_DEVICE'] ?>
													</label>
													<div class="col-md-4">
														<select class="form-control select2me" id="deviceSelect">
															<option value="1"><?php echo $LANG['WEB_HADOOP_CLUSTER'] ?></option>
															<option value="2"><?php echo $LANG['UI_FILE_RECOVERY_AGENT'] ?></option>
															<option value="3"><?php echo $LANG['UI_PLATFORM_NAS_DEVICE'] ?></option>
															<option value="4"><?php echo $LANG['UI_PLATFORM_OBS'] ?></option>
														</select>
													</div>
												</div>
												<!-- 选择客户端 -->
												<div class="form-group form-select-host" id="selectHost">
													<label class="control-label col-md-4">
														<span class="required">* </span>
														<span class="devicelabel"><?php echo $LANG['UI_HADOOP_CHOOSE_CLUSTER'] ?></span>
													</label>
													<div class="col-md-4 tree_div2" id="agenttree">
														<input type="text" id="searchHost" class="form-control" placeholder="<?php echo $LANG['UI_RECOVERY_FILE_SEARCH_CONDITION_TIPS'] ?>">
														<ul id="host_tree" class="ztree"></ul>
														<div class="col-md-12 alert alert-block alert-info fade in mt10" id="noagenttips">
															<button type="button" class="close" data-dismiss="alert"></button>
															<p>
															</p>
														</div>
													</div>
												</div>
												<div class="form-group" id="recoverpath">
													<label class="control-label col-md-4">
														<span class="required">* </span>
														<?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?>
													</label>
													<div class="col-md-4">
														<select class="form-control select2me" id="pathtype">
															<option value="2"><?php echo $LANG['UI_RECOVERY_FILE_DIY_PATH'] ?></option>
															<option value="1"><?php echo $LANG['UI_RECOVERY_FILE_OLD_PATH'] ?></option>
														</select>
													</div>
												</div>

												<div class="form-group form-recover-path display-none" id="selectPath">
													<label class="control-label col-md-4">
														<span class="required">* </span>
														<?php echo $LANG['UI_RECOVERY_FILE_SELECT_PATH'] ?>
													</label>
													<div class="col-md-4 tree_div2" id="pathtreediv">
														<ul id="path_tree" class="ztree"></ul>
													</div>
													<div class=" col-md-4 alert alert-block alert-warning fade in ml15 display-none" id="nopathtips">
														<button type="button" class="close" data-dismiss="alert"></button>
														<p>
															<?php echo $LANG['UI_RECOVERY_FILE_GET_PATH_FAILURE'] ?>
														</p>

													</div>
												</div>

											</div>
										</div>
									</div>

									<div class="tab-pane" id="tab3">
										<div class="row row-stepthree">
											<div class="nav-tabs-wrapper col-md-offset-1 col-md-10">
												<ul class="nav nav-tabs nav-line-tabs" id="fs_recovery_mode_tabs">
													<li class="active nav-item">
														<a href="#tab_common" class="nav-link" data-toggle="tab">
															<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
													</li>
													<li class="transferLi nav-item">
														<a href="#tab_transfer" class="nav-link" data-toggle="tab">
															<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
													</li>
													<li class="safetyLi nav-item">
														<a href="#tab_safety" class="nav-link" data-toggle="tab">
															<i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> 
														</a>
													</li>
													<li class="highLi nav-item">
														<a href="#tab_other" class="nav-link" data-toggle="tab">
															<i class="viconfont vicon-gaojipeizhi"></i>
															<?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
													</li>
												</ul>
												<div class="tab-content hover-scroll-y" id="fs_recovery_mode_tab_content">
													<div class="tab-pane fs-tab-content__pane active" id="tab_common">
														<div class="panel-body">
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
																							<label class="control-label col-md-2"><?php echo $LANG['UI_RECOVERY_START_TYPE'] ?> 
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
															<!-- 限速策略 -->
															<div class="form-group speedlimitDiv">
																<div class="col-md-offset-1 col-md-10 accordion strategyOne">
																	<div class="panel panel-default strategy-panel">
																		<div class="panel-heading">
																			<h4 class="panel-title">
																				<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
																					<i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
																					<span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
																					<span class="strategyDes speedlimitDes"></span>
																				</a>
																			</h4>
																		</div>
																		<?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
																	</div>
																</div>
															</div>
														</div>
													</div>

													<div class="tab-pane fs-tab-content__pane" id="tab_transfer">
														<div class="panel-body">
															<div class="tabbable-custom pdlr15  col-md-10">
																<!-- 传输网络 -->
																<div class="form-group display-none transfernetworkDiv">
																	<label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></label>
																	<div class="col-md-4">
																		<ul class="ztree" id="transferNetworkTree"></ul>
																	</div>
																	<div class="col-md-2 mt5">
																		<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
																			<i class="viconfont vicon-tishi"></i>
																		</a>
																	</div>
																</div>
																<!-- 传输代理 -->
																<div class="form-group appliancediv ">
																	<label class="control-label col-md-3 appliancelabel form-group-label"><?php echo $LANG['UI_HADOOP_PROXY'] ?></label>
																	<div class="col-md-2 form-group-content">
																		<input type="checkbox" id="appliancecheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" 
																		data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" 
																		data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																		<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" 
																		data-placement="right" data-content="<?php echo $LANG['UI_HADOOP_APPLIANCE_DES'] ?>">
																		<i class="viconfont vicon-tishi"></i>
																		</a>
																	</div>
																</div>
																<!-- 传输代理选择 -->
																<div class="form-group display-hide applianceselectdiv">
																	<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_HADOOP_SELECT_PROXY'] ?></label>
																	<div class="col-md-4">
																		<!-- <select class="form-control select2me" id="applianceSelect">
																		</select> -->
																		<ul class="ztree" id="transferAgentTree"></ul>
																	</div>
																</div>
																	<!-- 加密传输 -->
																	<div class="form-group encryptdiv display-hide">
																	<label class="control-label col-md-3 transferlabel form-group-label"> <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
																	<div class="col-md-2 form-group-content">
																		<input type="checkbox" id="encrypt" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																		<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_FILE_NETWORK_ENCRIPT_TRANSFER'] ?>">
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
																<!-- 传输线程 -->
																<div class="form-group threadDiv">
																	<label class="control-label col-md-3 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
																	</label>
																	<div class="col-md-4">
																		<div id="recoveryThreadDiv">
																			<div class="input-group spinner-group">
																				<input type="text" id="recoveryThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control">
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
																		<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_FILE_THREADBUM_TIPS'] ?>" data-original-title="" title="">
																			<i class="viconfont vicon-tishi"></i>
																		</a>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<!-- 安全配置 -->
													<div class="tab-pane fs-tab-content__pane" id="tab_safety">
														<div class="panel-body">
															<!-- 备份数据完整性校验 -->
															<div id="completeConfig"></div>
														</div>
													</div>
													<!-- 高级配置 -->
													<div class="tab-pane fs-tab-content__pane" id="tab_other">
														<div class="advance-config-wrap">
															<div class="advance-config-wrap__row row m0">
																<div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
																	<ul class="nav nav-tabs tabs-left">
																		<li class="permissionLi active">
																			<a href="#tab_permission_conf" data-toggle="tab" aria-expanded="true">
																			<?php echo $LANG['UI_ROLE_PERMISSION'] ?> </a>
																		</li>
																		<li class="exceptionLi">
																			<a href="#tab_except_handle_conf" data-toggle="tab" aria-expanded="true">
																			<?php echo $LANG['UI_EXCEPTION_HANDLE'] ?> </a>
																		</li>
																		<li class="retryLi">
																			<a href="#tab_retry_conf" data-toggle="tab" aria-expanded="true">
																			<?php echo $LANG['UI_STRATEGY_RETRY'] ?> </a>
																		</li>
																		<li class="overLoadLi">
																			<a href="#tab_over_load_conf" class="popovers" data-toggle="tab" aria-expanded="true">
																				<?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>
																			</a>
																		</li>
																	</ul>
																</div>
																<div class="col-md-10 col-sm-10 col-xs-10 pe-0 advance-config-wrap__row__content">
																	<div class="tab-content level1">
																		<!-- 权限 -->
																		<div class="tab-pane active" id="tab_permission_conf">
																			<div class = "abnormal-handle-form">
																				<!-- 文件权限恢复 -->
																				<div class="form-group permission-recovery-div advanced-config-file-form-group">
																					<label class="control-label col-md-4 permission-recovery-label form-group-label"><?php echo $LANG['UI_FILE_RECOVERY_PERMISSION'] ?></label>
																					<div class="col-md-3 form-group-content">
																						<input type="checkbox" id="permission-recovery" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																						<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_RECOVERY_PERMISSION_TIPS'] ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>
																				<!-- 对象存储 文件权限恢复 -->
																				<div
																					class="form-group obs-permission-recover-form-group display-hide">
																					<label
																						class="control-label col-md-4 obs-permission-recovery-label form-group-label"><?php echo $LANG['UI_OBS_RECOVERY_PERMISSION'] ?></label>
																					<div class="col-md-3 form-group-content">
																						<input type="checkbox"
																							id="obs_permission_recovery_flag"
																							class="make-switch" data-on-color="primary"
																							data-size="small" data-off-color="info"
																							data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
																							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																						<a class="popovers ml15" data-container="body"
																							data-trigger="hover" data-html="true"
																							data-placement="right"
																							data-content="<?php echo $LANG['UI_FILE_RECOVERY_PERMISSION_TIPS'] ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>
																			</div>
																		</div>
																		<!-- 异常处理 -->
																		<div class="tab-pane" id="tab_except_handle_conf">
																			<div class = "abnormal-handle-form">
																				<!-- 目录树恢复 -->
																				<div class="form-group dir-tree-div advanced-config-file-form-group">
																					<label class="control-label col-md-4 dir-tree-label form-group-label"><?php echo $LANG['UI_RECOVERY_FILE_SKIP_DIR_TREE_RECOVERY'] ?></label>
																					<div class="col-md-3 form-group-content">
																						<input type="checkbox" id="dir-tree" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																						<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_RECOVERY_FILE_SKIP_DIR_TREE_RECOVERY_TIPS'] ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>
																				<!-- 对象存储 跳过目录树恢复 -->
																				<div class="form-group advanced-config-obs-form-group">
																					<label
																						class="control-label col-md-4 obs-skip-dir-tree-label form-group-label"><?php echo $LANG['UI_OBS_REMOVE_PREFIX'] ?></label>
																					<div class="col-md-3 form-group-content">
																						<input type="checkbox"
																							id="obs_skip_dir_tree_flag"
																							class="make-switch" data-on-color="primary"
																							data-size="small" data-off-color="info"
																							data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
																							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																						<a class="popovers ml15" data-container="body"
																							data-trigger="hover" data-html="true"
																							data-placement="right"
																							data-content="<?php echo $LANG['UI_OBS_REMOVE_PREFIX_TIP'] ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>
																				<!-- 同名文件处理 -->
																				<div class="form-group same-name-div advanced-config-file-form-group">
																					<label class="control-label col-md-4 same-name-label form-group-label"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE'] ?></label>
																					<div class="col-md-3 form-group-content">
																						<select name="" id="same-name" class="form-control">
																								<option value="1"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_COVER'] ?></option>
																								<option value="2"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_KEEP_LATEST'] ?></option>
																								<option value="3"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_ADD'] ?></option>
																								<option value="4"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_RENAME'] ?></option>
																								<option value="5"><?php echo $LANG['UI_FILE_COPY_REPLACE'] ?></option>
																						</select>
																					</div>
																					<div class="col-md-1 pd0" style="margin-top: 10px;">
																						<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_TIPS'] ?>" data-original-title="" title="">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>
																				<!-- 对象存储 同名文件处理 -->
																				<div class="form-group advanced-config-obs-form-group">
																					<label
																						class="control-label col-md-4 obs-same-name-label form-group-label"><?php echo $LANG['UI_RECOVERY_OBS_PROCESS_SAME_OBS'] ?></label>
																					<div class="col-md-3 form-group-content">
																						<select name="" id="obs_same_name_handle_type"
																							class="form-control">
																							<option value="1"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_COVER'] ?></option>
																							<option value="2"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_KEEP_LATEST'] ?></option>
																							<option value="3"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_ADD'] ?></option>
																							<option value="4"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_RENAME'] ?></option>
																							<option value="5"><?php echo $LANG['UI_FILE_COPY_REPLACE'] ?></option>
																						</select>
																					</div>
																					<div class="col-md-1 pd0" style="margin-top: 10px;">
																						<a class="popovers" data-container="body"
																							data-trigger="hover" data-html="true"
																							data-placement="right"
																							data-content="<?php echo $LANG['UI_RECOVERY_OBS_PROCESS_SAME_OBS_TIPS'] ?>"
																							data-original-title="" title="">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>

																				<!-- 无效快捷方式清理 -->
																				<div class="form-group clear-shortcut-div display-none">
																					<label class="control-label col-md-4 clear-shortcut-label form-group-label"><?php echo $LANG['UI_RECOVERY_FILE_INVALID_SHORTCUT_CLEAN'] ?></label>
																					<div class="col-md-3 form-group-content">
																						<input type="checkbox" id="clear-shortcut" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																						<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_RECOVERY_FILE_INVALID_SHORTCUT_CLEAN_TIPS'] ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>
																			</div>
																		</div>
																		<!-- 重试 -->
																		<div class="tab-pane fade" id="tab_retry_conf">
																			<div class="">
																				<div class="tab-content">
																					<div id="retry_config" class="tab-pane retry_config_pane active">
																				</div>
																			</div>
																			</div>
																		</div>
																		<div class="tab-pane fade" id="tab_over_load_conf">
																			<div class="abnormal-handle-form">
																				<!-- 忽略节点资源限制 -->
																				<div class="form-group advancedDiv ignoreResourceLimitDiv">
																					<label for="ignoreResourceLimit" class="control-label col-md-3 ignoreResourceLimitLabel form-group-label">
																						<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?>
																					</label>
																					<div class="col-md-3 form-group-content">
																						<input type="checkbox" checked id="ignoreResourceLimit" class="make-switch" data-on-color="primary"
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

									<div class="tab-pane" id="tab4">
										<div class="tab-pane__body">
											<div class="tab-pane__body__form">
												<div class="alert alert-warning display-none jobnametip"></div>
												<div class="form-group mb0 mt10">
													<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
													<div class="col-md-9">
														<div class="input-icon right">
															<input type="text" maxlength="64" class="form-control" id="jobname" onkeyup="customInputValidate('string', this.value, $(this))" />
															<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
														</div>
													</div>
												</div>
												<h4 class="form-section"></h4>
												<div class="form-group mb0 mt10">
													<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_DATA_SRC'] ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static srcagentinfo">
														</p>
													</div>
												</div>
												<div class="form-group mb0 mt10">
													<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_DATA_SRC_TASK'] ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static srctaskname">
														</p>
													</div>
												</div>
												<div class="form-group mb0 mt10">
													<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_BACKUP_POINT'] ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static srctimepoint">
														</p>
													</div>
												</div>
												<div class="form-group mb0 mt10">
													<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_RC_LIST'] ?>:</label>
													<div class="col-md-9">
														<div class="filelisttext" id="filelist" style="margin-left:0;">

														</div>
													</div>
												</div>

												<h4 class="form-section"></h4>
												<div class="form-group mb0 mt10 recover2agentdiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_GOAL'] ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static recover2agent">
														</p>
													</div>
												</div>
												<div class="form-group mb0 mt10">
													<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static recover2path">
														</p>
													</div>
												</div>

												<h4 class="form-section"></h4>
												<div class="form-group mb0 mt10">
													<label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_TIME'] ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static reservetypeshow">
														</p>
													</div>
												</div>
												<div class="form-group mb0" id="speedstrategyshowdiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static speedlimitshow">
														</p>
													</div>
												</div>
												<div class="form-group mb0 transferDiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
													<div class="col-md-9">
														<div class="form-control-static">
															<div class="transfershow"></div>
															<div class="transthreadshow"></div>
														</div>
													</div>
												</div>
												<div class="form-group mb0 safeDiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static safeStrategyShow">
														</p>
													</div>
												</div>
												<div class="form-group mb0 highDiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>:</label>
													<div class="col-md-9">
														<div class="form-control-static">
															<div class="dirtreeshow"></div>
															<div class="samenameshow"></div>
															<div class="clearshortcutshow display-none"></div>
															<div class="permissionrecoveryshow"></div>
															<div class="ignoreResourceLimitShow"></div>
														</div>
													</div>
												</div>
												<!-- 重试策略显示 -->
												<div class="form-group mb0 mt10 retryShow"></div>
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
				</form>
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
										<input type="text" id="speedSpinnerNumInput" style="text-align: left;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
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
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	//如果不是英文,加载语言包
	echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.GFSStrategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_agent.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./assets/global/plugins/clipboard/clipboard.min.js"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/timePointDetail.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/hadoop/hadoop_recovery.js" type="text/javascript"></script>