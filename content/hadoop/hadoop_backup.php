<?php include_once '../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<?php session_start();
session_commit(); ?>
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height: 100%;">
	<div class="col-md-12" style="height: 100%;">

	<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none">
	<input id="edit_flag" value="<?php echo $_GET['editflag']; ?>" class="display-none">
		<div class="portlet box blue-hoki backup-page" id="hadoopbackupcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont  vicon-xinjianbeifen1"></i><span id="title"><?php echo $LANG['WEB_HADOOP_CREATE_BACKUP_TASK'] ?></span>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" class="form-horizontal" id="submit_form" method="POST">
					<div class="form-wizard" style="height:100%;">
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
									<div class="row tab-pane__row">
										<div class="col-md-4 tab-pane__row__source">
											<div class="form-group src-wrap">
												<div class="src-wrap__title">
													<span>
														<i class="levelchild viconfont vicon-ge_backup_host"></i><?php echo $LANG['WEB_HADOOP_SELETE_BACKUP_CLUSTER'] ?>
													</span>
												</div>
												<div class="src-wrap__content">
													<div class="src-wrap__content__search">
														<div class="alert alert-block alert-info fade in display-none" id="noagent">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
															<ol class="alert-ol">
																<li>
																	<?php echo $LANG['WEB_HADOOP_SELETE_BACKUP_CLUSTER_TIPS1'] ?>
																</li>
																<li>
																	<a id="toAdd" class="alert-link"><?php echo $LANG['WEB_HADOOP_SELETE_BACKUP_CLUSTER_TIPS2'] ?></a>
																</li>
															</ol>
														</div>
														<div class="vm_tree_div">
															<div class="searchDiv width100p display-none">
																<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>" class="form-control" id="searchCluster" style="margin: 0" onkeyup="customInputValidate('string', this.value, $(this))">
															</div>
														</div>
													</div>
													<div class="src-wrap__content__ztree">
														<div class="three_tree vcenter-tree">
															<ul id="cluster_tree" class="ztree"></ul>
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
										<div class="col-md-8">
											<div class="alert alert-block alert-info fade in" id="step1tips">
												<button type="button" class="close" data-dismiss="alert"></button>
												<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
												<ol class="alert-ol">
													<li><?php echo $LANG['WEB_HADOOP_SELETE_BACKUP_FILE_TIPS1'] ?></li>
													<li><?php echo $LANG['UI_BACKUP_FILE_BAK_TIPS2'] ?></li>
													<li><?php echo $LANG['WEB_HADOOP_SELETE_BACKUP_FILE_TIPS2'] ?></li>
												</ol>
											</div>
										</div>
										<div class="col-md-8 display-hide VMList-div" id="clusterfilediv">
											<!-- 勾选需要备份的文件 -->
											<div class="addTitle">
												<span><i class="viconfont vicon-ge_folder"></i> <?php echo $LANG['UI_BACKUP_FILE_BAC_SRC'] ?></span>
											</div>
											<div class="file-source-list" id="allClusterTree"></div>
										</div>
									</div>
								</div>
								<div class="tab-pane" id="tab2">
									<div class="row" style="height: 100%; overflow-y: auto; padding-top: 20px">
										<div class="col-md-9" id="backupTarget"></div>
									</div>
								</div>
								<div class="tab-pane" id="tab3">
									<div class="row row-stepthree">
										<div class="nav-tabs-wrapper col-md-offset-1 col-md-10">
											<ul class="nav nav-tabs nav-line-tabs">
												<li class="commonLi active nav-item">
													<a href="#tab_common" class="nav-link" data-toggle="tab">
														<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
												</li>
												<li class="nav-item">
													<a href="#tab_transfer" class="nav-link" data-toggle="tab">
														<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
												</li>
												<li class="safetyLi nav-item">
													<a href="#tab_safety" class="nav-link" data-toggle="tab">
													<i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> </a>
												</li>
												<li class="highLi nav-item">
													<a href="#tab_other" class="nav-link" data-toggle="tab">
														<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
												</li>
											</ul>
											<div class="tab-content hover-scroll-y">
												<div class="tab-pane active" id="tab_common">
													<div class="panel-body">
														<!-- 通用策略-选择策略 -->
														<div class="form-group">
															<div class="control-label col-md-1"><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?></div>
															<div class="col-md-4">
																<select class="form-control select2me inline-block" id="strategySelect">
																</select>
																<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
																	<i class="viconfont vicon-tishi"></i>
																</a>
															</div>
														</div>
														<!-- 时间策略 -->
														<div class="form-group">
															<div class="col-md-offset-1 col-md-10 ">
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
																	<?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
																	<!--       全局限速策略组合的组件配置-->
																</div>
															</div>
														</div>
														<!-- 存储策略 -->
														<div class="form-group">
															<div class="col-md-offset-1 col-md-10 ">
																<?php include('../platform/strategy/storeStrategy.php') ?>
															</div>
														</div>
														<!-- 保留策略 -->
														<div class="form-group form-group-panel-reserve">
															<div class="col-md-offset-1 col-md-10 ">
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
											
															<div class="form-group display-none transfernetworkDiv">
																<label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="transferNetwork">
																	</select>
																</div>
																<div class="col-md-2 mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															<div class="form-group appliancediv">
																<label class="control-label col-md-3 appliancelabel form-group-label"><?php echo $LANG['UI_HADOOP_PROXY'] ?></label>
																<div class="col-md-4 form-group-content">
																	<input type="checkbox" id="appliancecheck"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
																			data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
																			data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																	<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_HADOOP_APPLIANCE_DES'] ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
										
															</div>
															<div class="form-group display-hide applianceselectdiv">
																<label class="control-label col-md-3 applianceselectlabel form-group-label"><?php echo $LANG['UI_HADOOP_SELECT_PROXY'] ?></label>
																<div class="col-md-4 content">
																	<ul class="ztree" id="transferAgentTree"></ul>
																</div>
															</div>
															<!-- 传输加密 -->
															<div class="form-group display-hide encrypttransferdiv">
																<label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
																<div class="col-md-4 form-group-content">
																	<input type="checkbox" id="transport_encrypt_flag" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
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
																		<option value="1"><?php echo $LANG['UI_BACKUP_TRANSFER_ENCRYPT_RSA'] ?></option>
																		<?php
																		if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
																			echo '<option value="2">' . $LANG['UI_BACKUP_TRANSFER_ENCRYPT_SM'] . '</option>';
																		}
																		?>
																	</select>
																</div>
															</div>
															<!-- 扫描线程数 -->
															<div class="form-group scanthreadDiv">
																<label class="control-label col-md-3 scanthreadlabel"> <?php echo $LANG['UI_FILE_BAK_SCAN_THREAD'] ?>
																</label>
																<div class="col-md-2">
																	<div class="scanThreadDiv">
																		<div class="input-group spinner-group">
																			<input type="text" id="scanThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm">
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
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_FILE_SCAN_THREADBUM_TIPS'] ?>" data-original-title="" title="">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															<!-- 扫描文件数 -->
															<div class="form-group scanFileDiv display-none">
																<label class="control-label col-md-3 scanfilelabel"><?php echo $LANG['UI_FILE_BAK_SCAN_FILE'] ?>
																</label>
																<div class="col-md-2">
																	<div class="scanFileDiv">
																		<div class="input-group">
																			<div class="spinner-buttons input-group-btn">
																				<select class="form-control select2me input-sm" id="scanFileNum">
																					<option value="0"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_FIVE'] ?></option>
																					<option value="1000"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_FOUR'] ?></option>
																					<option value="800"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_THREE'] ?></option>
																					<option value="600"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_TWO'] ?></option>
																					<option value="400"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_ONE'] ?></option>
																				</select>
																			</div>
																		</div>
																	</div>
																</div>
																<div class="col-md-2 mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_BAK_SCAN_FILE_TIPS'] ?>" data-original-title="" title="">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															<!-- 传输线程 -->
															<div class="form-group threadDiv">
																<label class="control-label col-md-3 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
																</label>
																<div class="col-md-2">
																	<div class="backupThreadDiv">
																		<div class="input-group spinner-group">
																			<input type="text" id="backupThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm">
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
												<!-- 安全策略 -->
												<div class="tab-pane " id="tab_safety">
													<div class="panel-body">
														<div class="tabbable-custom">
															<!-- WORM -->
															<div id="wormConfig"></div>
															<!-- 病毒检测 -->
															<div id="virusConfig"></div>
															<!-- 完整性校验 -->
															<div id="completeConfig"></div>
														</div>
													</div>
												</div>
												<!-- 高级配置 -->
												<div class="tab-pane " id="tab_other">
													<div class="advance-config-wrap">
														<div class="advance-config-wrap__row  row m0">
															<div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios">
																<ul class="nav nav-tabs tabs-left">
																	<li class="commonLi active">
																		<a href="#tab_snapshot_conf" class="popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?> </a>
																	</li>
																	<li >
																		<a href="#tab_permission_conf" class="popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo $LANG['UI_ROLE_PERMISSION'] ?> </a>
																	</li>
																	<li >
																		<a href="#tab_except_handle_conf" class="popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		 <?php echo $LANG['UI_EXCEPTION_HANDLE'] ?> </a>
																	</li>
																	<li>
																		<a href="#tab_retry_conf" class="popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		 <?php echo $LANG['UI_STRATEGY_RETRY'] ?> </a>
																	</li>
																	<li>
																		<a href="#over_load_strategy_pane" class="popovers" data-toggle="tab" aria-expanded="true">
																			<?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>
																		</a>
																	</li>
																</ul>
															</div>
															<div class="col-md-10 col-sm-10 col-xs-10 pe-0 advance-config-wrap__row__content">
																<div class="tab-content level1">
																	<!-- 快照 -->
																	<div class="tab-pane active" id="tab_snapshot_conf">
																		<!-- <div class="advanced-conf-title-icon floatl mt2"></div>
																		<div class="floatl ">
																			<span class="ms-12">存储与数据保护功能配置</span>
																		</div> -->
																		<div class = "abnormal-handle-form">
																			<!-- 快照 -->
																			<div class="form-group mt45 snapshotdiv">
																				<label class="control-label col-md-3 silentsnapshotlabel"><?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?>
																				</label>
																				<div class="col-md-4">
																					<input type="checkbox" id="silentsnapshotcheck" checked class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a id="snapshotchecktips1" class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																					<a id="snapshotchecktips2" class="popovers ml15 display-none" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_HADOOP_FILE_SHOOTSNAP_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																		</div>
																	</div>
																	<!-- 权限 -->
																	<div class="tab-pane" id="tab_permission_conf">
																		<div class = "abnormal-handle-form">
																			<!-- 文件权限备份 -->
																			<div class="form-group mt45 file-permission-div">
																				<label class="control-label col-md-3 file-permission-label"><?php echo $LANG['UI_FILE_PERMISSION_BACKUP'] ?>
																				</label>
																				<div class="col-md-4">
																					<input type="checkbox" id="file-permission" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_PERMISSION_BACKUP_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																		</div>
																	</div>
																	<!-- 异常处理 -->
																	<div class="tab-pane" id="tab_except_handle_conf">
																		<div class = "abnormal-handle-form">
																			<!-- 跳过文件告警智能判断 -->
																			<div class="form-group mt45 passfilealarmdiv">
																				<label class="control-label col-md-3 passfilealarmlabel"><?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_JUDGE'] ?>
																				</label>
																				<div class="col-md-4">
																					<input type="checkbox" id="passfilealarmcheck" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_JUDGE_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																			<!-- 跳过文件告警个数 -->
																			<div class="form-group passfilenumDiv display-none">
																				<label class="control-label col-md-3 passfilenumlabel"><?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_NUM'] ?>
																				</label>
																				<div class="col-md-4">
																					<div >
																						<div class="input-group spinner-group">
																							<input type="text" maxlength="9" id="passFileNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm">
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
																			</div>
																			<!-- 跳过文件告警比例 -->
																			<div class="form-group warnningdiv display-none">
																				<label class="control-label col-md-3 passfilepercentlabel"><?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_RATIO'] ?>
																				</label>
																				<div class="col-md-4">
																					<div>
																						<div class="input-group spinner-group">
																							<input type="text" id="warningpercent" style="text-align: left;" class="input-sm spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
																				<div class="col-md-1 pd0" style="line-height: 28px;">%</div>
																			</div>
																		</div>
																	</div>
																	<!-- 重试 -->
																	<div class="tab-pane" id="tab_retry_conf">
																		<div id="retry_config" class="tab-pane retry_config_pane active">
																		</div>
																	</div>
																	<div class="tab-pane" id="over_load_strategy_pane">
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
								<div class="tab-pane" id="tab4">
									<div class="tab-pane__body">
										<div class="tab-pane__body__form">
											<div class="alert alert-danger display-none jobnametip"></div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
												<div class="col-md-9">
													<div class="input-icon right">
														<i class="fa"></i>
														<input type="text" maxlength="64" class="form-control" id="jobname" onkeyup="customInputValidate('string', this.value, $(this))" />
														<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
													</div>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_FILE_BAK_FILE_LIST'] ?>:</label>
												<div class="col-md-9">
													<div class="form-control-static" id="filelist">

													</div>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_STORAGE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  storageInfoShow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_NODE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  nodeInfoShow">
													</p>
												</div>
											</div>

											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TYPE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  backuptypeshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 backupTypeInfoDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STRATEGY_OR_TIME'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  backuptypeinfoshow">
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
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static">
														<span class="transferinfoshow"></span>
														<span class="applianceshow"></span>
														<span class="backupmodeshow5"></span>
														<span class="backupmodeshow6 dispaly-none"></span>
														<span class="backupmodeshow3"></span>
									
													</p>
												</div>
											</div>
											<!-- 安全策略展示 -->
											<div class="form-group mb0 mt10 safeDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static safeStrategyShow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 speedlimitDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>:</label>
												<div class="col-md-9">
													<div class="form-control-static">
														<div class="backupmodeshow1"></div>
														<div class="archiveshow"></div>
														<div class="archiveselectshow display-none"></div>
														<div class="filepermissionshow"></div>
														<div class="passalarmshow"></div>
														<div class="passalarmnumshow display-none"></div>
														<div class="passalarmpercentshow display-none"></div>
														<div class="ignoreResourceLimitShow"></div>
													</div>
												</div>
											</div>
											<!-- 重试策略显示 -->
											<div class="form-group mb0 mt10 retryShow"></div>
											<div class="form-group mb0 mt10 speedlimitDiv">
												<label class="control-label col-md-3 backupmodeshow4">:</label>
												<div class="col-md-9">
													<div class="form-control-static  backupmodeshow4list"></div>
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
										<i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?>
									</a>
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
							<div >
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
<script type="text/javascript" src="./scripts/public/strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript"src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" ></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	//如果不是英文,加载语言包
	echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.backupStrategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_worm.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_agent.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/hadoop/hadoop_backup.js" type="text/javascript"></script>
