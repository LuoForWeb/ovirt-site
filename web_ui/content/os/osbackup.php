<?php include_once '../../tpl/permission.php'; ?>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<?php session_start();
session_commit(); ?>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:100%;">
	<div class="col-md-12" style="height:100%;">
		<input id="s_vmuuid" value="<?php echo $_GET['vmuuid']; ?>" class="display-none"></input>
		<input id="s_vcenteruuid" value="<?php echo $_GET['vcenteruuid']; ?>" class="display-none"></input>
		<input id="s_showtype" value="<?php echo $_GET['showtype']; ?>" class="display-none"></input>
		<input id="s_hypervisor" value="<?php echo $_GET['hypervisor']; ?>" class="display-none"></input>
		<span class="display-none" id="servertime"></span>
		<div class="portlet box blue-hoki backup-page" id="osbackupcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-xinjianbeifen1"></i><?php echo $LANG['WEB_OS_NEW_HOST_BACKUP_TASK'] ?>
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
											<?php echo $LANG['UI_BACKUP_SOURCE'] ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
										<span class="number">2 </span>
										<span class="desc">
											<?php echo $LANG['UI_BACKUP_DATA_DES'] ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab3" data-toggle="tab" class="step active">
										<span class="number">3 </span>
										<span class="desc">
											<?php echo $LANG['UI_JOB_STRATEGY_INFO'] ?>
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
									<div class="alert alert-danger display-none selectostip">
									</div>
									<div class="row tab-pane__row">
										<div class="col-md-4 tab-pane__row__source">
											<div class="form-group src-wrap">
												<div class="src-wrap__title">
													<span>
														<?php echo $LANG['WEB_OS_CHOOSE_HOST'] ?>
													</span>
												</div>
												<div class="src-wrap__content">
													<div class="src-wrap__content__search">
														<div class="vm_tree_div">
															<div class="width100p">
																<div class="input-icon">
																	<input type="text" maxlength="128" placeholder="<?php echo $LANG['WEB_OS_SEARCH_NAME'] ?>" class="form-control" id="searchos" onkeyup="customInputValidate('string', this.value, $(this))">
																</div>
															</div>
														</div>
													</div>
													<div class="src-wrap__content__ztree">
														<div class="os-tree">
															<ul id="os_tree" class="ztree tree_div"></ul>
														</div>
														<!-- 没有找到代理时给出提示 -->
														<div class="alert alert-block alert-info fade in display-hide" id="nodatatips">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
															<ol class="alert-ol">
																<a id=toAgentManager class="alert-link"><?php echo $LANG['WEB_OS_PLEASE_ADD_NEW_AGENT_TIPS1'] ?></a>
															</ol>
														</div>
														<!-- 搜索后没有找到资源给出提示内容 -->
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
												<span><?php echo $LANG['WEB_OS_ALREADY_CHOOSE_HOST'] ?></span>
											</div>
											<div class="addIt-list">
												<ul class="feeds addVMList" id="addOSList">
												</ul>
											</div>
										</div>
									</div>
								</div>
								<!-- 备份目的地 -->
								<div class="tab-pane" id="tab2">
									<div class="backup-destination-wrap row">
										<div class="col-md-9 hp-100" id="backupTarget"></div>
									</div>
								</div>
								<div class="tab-pane" id="tab3">
									<div class="alert alert-info display-none hypervTips">
									</div>
									<div class="row row-stepthree">
										<div class="tabbable-custom col-md-offset-1 col-md-10">
											<ul class="nav nav-tabs">
												<li class="commonLi active">
													<a href="#tab_common" class="" data-content="<?php echo $LANG['UI_BACKUP_COMMON_STRATEGY_DES'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
												</li>
												<li class="" style="display: block;">
													<a href="#tab_transfer" class="" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_TIPS'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
												</li>
												<!-- 高级配置暂时隐藏 -->
												<li class="highLi">
													<a href="#tab_other" class="" data-content="<?php echo $LANG['UI_BACKUP_HIGH_CONFIG_DES'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
												</li>
											</ul>
											<div class="tab-content">
												<div class="tab-pane active" id="tab_common">
													<div class="panel-body">
														<!-- 选择策略 -->
														<div class="form-group">
															<div class="control-label col-md-1"><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?></div>
															<div class="col-md-4">
																<select class="form-control select2me inline-block" id="strategySelect">
																</select>
																<a class="popovers ml15" data-container="body" data-trigger="hover"
																	data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
																	<i class="viconfont vicon-tishi"></i>
																</a>
															</div>
														</div>
														<!-- 时间策略 -->
														<div class="form-group">
															<div class="col-md-offset-1 col-md-10  ">
																<?php include('../platform/strategy/timeStrategy.php'); ?>
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
																	<!--<div id="speed" class="panel-collapse collapse ">
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
														<!-- 存储策略 -->
														<div class="form-group">
															<div class="col-md-offset-1 col-md-10  ">
																<?php include('../platform/strategy/storeStrategy.php') ?>
															</div>
														</div>
														<!-- 保留策略 -->
														<div class="form-group" id="reserveShowDiv">
															<div class="col-md-offset-1 col-md-10  ">
																<?php include('../platform/strategy/reserveStrategy.php') ?>
															</div>
														</div>
													</div>
												</div>
												<!-- 传输策略 -->
												<div class="tab-pane " id="tab_transfer">
													<div class="panel-body">
														<div class="tabbable-custom col-md-10">
															<!-- 传输模式 -->
															<div class="form-group transportdiv display-none">
																<label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="transport_mode">
																		<option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
																		<!-- <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option> -->
																	</select>
																</div>
																<div class="col-md-2 mt10">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_DB_TRANSPORT_TIPS'] ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															<!-- 传输加密 -->
															<div class="form-group">
																<label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
																<div class="col-md-4 form-group-content">
																	<input type="checkbox" id="transport_encrypt_flag" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																	<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['WEB_OS_TRANSFER_ENCRY_TIPS'] ?>">
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
															<!-- 传输线程 -->
															<div class="form-group threadDiv">
																<label class="control-label col-md-3 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
																</label>
																<div class="col-md-4">
																	<div class="backupThreadDiv">
																		<div class="input-group spinner-group">
																			<input type="text" id="backupThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="2">
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
												<div class="tab-pane " id="tab_other">
													<div class="advance-config-wrap">
														<div class="advance-config-wrap__row row m0">
															<div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
																<ul class="nav nav-tabs tabs-left">
																	<li class="active">
																		<a href="#snapshot_handle_pane"
																			data-toggle="tab" aria-expanded="true">
																			<?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?>
																		</a>
																	</li>
																	<li class="">
																		<a href="#increment_handle_pane"
																			data-toggle="tab" aria-expanded="true">
																			<?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_INCREMENT'] ?>
																		</a>
																	</li>
																	<li class="">
																		<a href="#validdata_handle_pane"
																			data-toggle="tab" aria-expanded="true">
																			<?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_VALIDATE_DATA'] ?>
																		</a>
																	</li>
																	<li class="">
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
																	<!-- 快照 -->
																	<div class="tab-pane fade in active" id="snapshot_handle_pane">
																		<div class="abnormal-handle-form">
																			<div class="form-group">
																				<label class="control-label col-md-3 silentSnapshotlabel form-group-label"><?php echo $LANG['UI_DB_SILENT_SNAPSHOT'] ?></label>
																				<div class="col-md-4 form-group-content">
																					<input checked type="checkbox" id="silent_snapshot" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_DB_SILENT_SNAPSHOT_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																			<div class="form-group display-none">
																				<label class="control-label col-md-3 snapshotFlaglabel form-group-label"><?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></label>
																				<div class="col-md-4 form-group-content">
																					<input checked type="checkbox" id="snapshot_flag" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-html="true" data-content="<?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>

																					</a>
																				</div>
																			</div>
																		</div>
																	</div>
																	<!-- 增量 -->
																	<div class="tab-pane fade" id="increment_handle_pane">
																		<div class="abnormal-handle-form">
																			<div class="form-group">
																				<label class="control-label col-md-3 cbtFlaglabel form-group-label"><?php echo $LANG['UI_DB_CBT'] ?></label>
																				<div class="col-md-4 form-group-content">
																					<input checked type="checkbox" id="cbt_flag" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_DB_CBT_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																		</div>
																	</div>
																	<!-- 有效数据 -->
																	<div class="tab-pane fade" id="validdata_handle_pane">
																		<div class="abnormal-handle-form">
																			<div class="form-group">
																				<label class="control-label col-md-3 validDataFlaglabel form-group-label"><?php echo $LANG['UI_DB_VALID_DATA'] ?></label>
																				<div class="col-md-4 form-group-content">
																					<input checked type="checkbox" id="valid_data_flag" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['WEB_OS_GET_VALID_DATA_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																		</div>
																	</div>
																	<!-- 重试策略 -->
																	<div class="tab-pane fade" id="retry_strategy_pane">
																		<div class="">
																			<div class="tab-content">
																				<div id="retry_config" class="tab-pane retry_config_pane active">
																				</div>
																			</div>
																		</div>
																	</div>
																	<!-- 过载保护 -->
																	<div class="tab-pane fade" id="over_load_strategy_pane">
																		<div class="abnormal-handle-form">
																			<!-- 忽略节点资源限制 -->
																			<div class="form-group advancedDiv ignoreResourceLimitDiv">
																				<label for="ignoreResourceLimit" class="control-label col-md-4 ignoreResourceLimitLabel form-group-label">
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
											<div class="alert alert-danger display-none jobnametip"></div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
												<div class="col-md-9">
													<div class="input-icon right">
														<i class="fa"></i>
														<input type="text" maxlength="64" class="form-control" id="jobname" onkeyup="customInputValidate('string', this.value, $(this))"/>
														<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
													</div>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_AGENT'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  agentshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SOURCE'] ?>:</label>
												<div class="col-md-9">
													<!--                                             <table border="1" class="form-control-static borderTableColorGreen osshow"> -->
													<!--                                             </table> -->
													<div class="form-control-static osshow"></div>

												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static storeinfoshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_NODE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static nodeinfoshow">
													</p>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TYPE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static backuptypeshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 backupTypeInfoDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_TIME'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static backuptimeinfoshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 speedlimitDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  speedlimitshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  storageinfoshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 reserveDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  reservetypeshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 transfershowdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  transfershow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 retryShow"></div>
											<!-- 高级配置展示隐藏 -->
											<div class="form-group mb0 mt10 highshowdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_USER_HIGH_SETTING'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  highshow">
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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.backupStrategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/os/osbackup.js" type="text/javascript"></script>