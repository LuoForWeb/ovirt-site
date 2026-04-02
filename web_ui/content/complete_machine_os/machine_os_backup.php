<?php include_once '../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
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
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css" />
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />



<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height: 100%;">
	<div class="col-md-12" style="height: 100%;">
		<div class="portlet box blue-hoki" id="machineOsBackupContent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-xinjianbeifen1"></i><?php echo $_GET['uuid'] ? $LANG['UI_MACHINE_OS_EDIT_BACKUP_JOB'] : $LANG['UI_MACHINE_OS_NEW_BACKUP_JOB'];  ?>
					<input type="hidden" id="uuid" value="<?php echo $_GET['uuid'] ?>">
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" class="form-horizontal" id="submit_form" method="POST">
					<div class="form-wizard">
						<div class="form-body mlr10">
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
														<i class="viconfont vicon-beifenzhuji"></i> <?php echo $LANG['UI_MACHINE_OS_SELECT_BACKUP_HOST'] ?>
													</span>
												</div>
												<div class="src-wrap__content">
													<div class="src-wrap__content__search">
														<div class="alert alert-block alert-info fade in" id="noagent" style="display: none;">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
															<ol class="alert-ol">
																<a id="toAdd" class="alert-link"><?php echo $LANG['WEB_MACHINE_OS_BACKUP_NO_CLIENT'] ?></a>
															</ol>
														</div>
														<div class="vm_tree_div">
															<div class="searchDiv width100p">
																<div class="input-icon">
																	<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_MACHINE_OS_SEARCH_BY_HOST'] ?>" class="form-control" id="searchAgent"  onkeyup="customInputValidate('string', this.value, $(this))">
																</div>
															</div>
														</div>
													</div>
													<div class="src-wrap__content__ztree" style="height: calc(100% - 78px);">
														<div class="vcenter-tree">
															<ul id="machine_os_tree" class="ztree"></ul>
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
											<div class="alert alert-block alert-info fade in" id="step1tips" style="display: none;">
												<button type="button" class="close" data-dismiss="alert"></button>
												<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
												<ol class="alert-ol">
													<li><?php echo $LANG['UI_MACHINE_OS_BACKUP_TIPS1'] ?></li>
													<li><?php echo $LANG['UI_MACHINE_OS_BACKUP_TIPS2'] ?></li>
												</ol>
											</div>
										</div>
										<div class="col-md-8 display-hide VMList-div">
											<div class="addTitle">
												<span>
													<i class="viconfont vicon-huifu"></i><?php echo $LANG['UI_MACHINE_OS_SELECTED_BACKUP_SOURCE'] ?>
												</span>
											</div>
											<div class="addIt-list" id="checkedSourceDiv"></div>
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
										<div class="tabbable-custom col-md-offset-1 col-md-10">
											<ul class="nav nav-tabs">
												<li class="commonLi active">
													<a href="#tab_common" class="" data-content="<?php echo $LANG['WEB_MACHINE_OS_NO_STRATEGY'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
												</li>
												<li class="">
            										<a href="#tab_transfer" class="" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_TIPS'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
            										<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
            									</li>
                                                <li class="tab_safety">
            										<a href="#tab_safety" class="" data-content="<?php echo $LANG['UI_MACHINE_OS_DATA_SAFE_POLICY'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
            										<i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> </a>
            									</li>
												<li class="highLi">
            										<a href="#tab_high" class="" data-content="<?php echo $LANG['UI_BACKUP_HIGH_CONFIG_DES'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
            										<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></a>
            									</li>
												<li class="scriptLi">
            										<a href="#tab_script" class="" data-content="<?php echo $LANG['UI_VOL_CDP_SCRIPT_CONFIGURE'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
            										<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_VOL_CDP_SCRIPT_CONFIGURE'] ?></a>
            									</li>
											</ul>
											<div class="tab-content">
												<div class="tab-pane active" id="tab_common">
													<div class="panel-body">
														<!-- 选择策略 -->
														<div class="form-group" style="display:none;">
															<div class="control-label col-md-1"><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY']?></div>
															<div class="col-md-4">
																<select class="form-control select2me inline-block" id="strategySelect">
																</select>
																<a class="popovers ml15" data-container="body" data-trigger="hover"
																	data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
																	<i class="viconfont vicon-tishi"></i>
																</a>
															</div>
														</div>
														<!-- 备份策略 -->
														<div class="form-group">
															<div class="strategy-group-select-wrapper"></div>
															<div id="backupStrategyDiv" class="col-md-10 col-md-offset-1">
															</div>
														</div>
													</div>
												</div>
												<!-- 传输策略 -->
												<div class="tab-pane " id="tab_transfer">
            									     <div class="panel-body">
													 	<div class="tabbable-custom" >

                                                         <div class="form-group">
                                                                <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                <div class="col-md-3 form-group-content">
                                                                    <input type="checkbox" id="encrypt" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_MACHINE_OS_ENCRYPT_TRANSFER_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

															<div class="form-group encrypt_method_div" style="display:none;">
																<label class="control-label col-md-3"> <?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
																<div class="col-md-4">
																	<select class="form-control" id="encrypt_method">
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

															<div class="form-group">
                                                                <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_DB_SRC_COMPRESS'] ?></label>
                                                                <div class="col-md-3 form-group-content">
                                                                    <input type="checkbox" id="SourceEnd_compression" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_MACHINE_OS_SRC_COMPRESS_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <div class="form-group compression_level_div" style="display:none;">
																<label class="control-label col-md-3"> <?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
																<div class="col-md-4">
																	<select class="form-control" id="compression_level">
																		<option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
																		<option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
																		<option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
                                                                        <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
																	</select>
																</div>
																<div class="col-md-2 mt10" style="display:none;">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
																	data-placement="right" data-content="none">
																	<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>

															<!-- 传输网络 -->
															<div class="form-group transfernetworkDiv">
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
                                                <!-- 安全策略 -->
                                                <div class="tab-pane " id="tab_safety">
													<div class="panel-body">
														<div class="tabbable-custom">
                                                            <!-- WORM -->
                                                            <div id="wormConfig"></div>
                                                            <!-- 病毒检测 -->
                                                            <div id="virusConfig"></div>
                                                            <!-- 完整性校验 -->
                                                            <div id="completionConfig"></div>
															<!-- 备份后立即验证 -->
                                                            <!-- <div id="sureBackupConfig"></div> -->
														</div>
													</div>
												</div>
												<!-- 高级配置 -->
												<div class="tab-pane " id="tab_high">
												<div class="panel-body">
													<div class="row row-stepthree">
														<div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios">
															<ul class="nav nav-tabs tabs-left min-height400">
																<li class="commonLi cm-advanced-public-conf-title active">
																	<a href="#tab_snapshoot" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_SNAPSHOT'] ?> </a>
																</li>
																<li class="">
																	<a href="#tab_increment" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																	<?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_INCREMENT'] ?> </a>
																</li>
																<li class="">
																	<a href="#tab_valid_data" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																	<?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_VALIDATE_DATA'] ?> </a>
																</li>
																<li class="">
																	<a href="#retry_config" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																	<?php echo $LANG['UI_SETTINGS_UPDATE_RETRY'] ?> </a>
																</li>
																<li class="">
																	<a href="#storage_config" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																	<?php echo $LANG['UI_DRILLS_STORAGE'] ?> </a>
																</li>
																<li class="">
																	<a href="#overload_protection" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																	<?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?> </a>
																</li>

															</ul>
														</div>
														<div class="col-md-10 col-sm-9 col-xs-9">
															<div class="tab-content">
																<!-- 快照 -->
																<div class="tab-pane active" id="tab_snapshoot">
																	<div class="col-md-12 ">
																		
																	
																		<div class = "col-md-12 advanced-detail-conf-district">
																			 <!-- 静默快照 -->
																			 <div class="form-group mt45">
																				<label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_DB_SILENT_SNAPSHOT'] ?></label>
																				<div class="col-md-3 form-group-content">
																					<input type="checkbox" checked id="silent_snapshot" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_DB_SILENT_SNAPSHOT_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
									
																		</div>
																	</div>
																</div>
																<!-- 增量 -->
																<div class="tab-pane" id="tab_increment">
																	<div class="col-md-12 ">
																		
																		<div class = "col-md-12 advanced-detail-conf-district">
																			 <!-- CBT -->
																			<div class="form-group mt45">
																				<label class="control-label col-md-3 form-group-label">CBT</label>
																				<div class="col-md-3 form-group-content">
																					<input type="checkbox" id="cbt" checked class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_DB_CBT_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
																<!-- 有效数据 -->
																<div class="tab-pane" id="tab_valid_data">
																	<div class="col-md-12 ">
																		
																		<div class = "col-md-12 advanced-detail-conf-district">
																			 <!-- 跳过坏块备份 -->
																			<div class="form-group mt45">
																				<label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_MACHINE_OS_SKIP_BAD_BLOCK_BACKUP'] ?></label>
																				<div class="col-md-3 form-group-content">
																					<input type="checkbox" id="skip_bad_block" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_MACHINE_OS_SKIP_BAD_BLOCK_BACKUP_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																			 <!-- 有效数据备份 -->
																			 <div class="form-group">
																				<label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_BACKUP_VALID_DATA'] ?></label>
																				<div class="col-md-3 form-group-content">
																					<input type="checkbox" checked id="each_sector_backup" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['WEB_OS_GET_VALID_DATA_TIPS'] ?>">
																						<i class="viconfont vicon-tishi"></i>
																					</a>
																				</div>
																			</div>
																			
																		</div>
																		
																	</div>
																</div>
																<!-- 重试 -->
																<div class="tab-pane" id="retry_config">
																	
																</div>
																<!-- 重试 -->
																<div class="tab-pane" id="storage_config">
																	<div class="col-md-12 ">
																		
																		<div class = "col-md-12 advanced-detail-conf-district">
																			 <!-- 合并模式 -->
																			<div class="form-group datacontainersizediv mt45">
																				<label class="control-label col-md-3 datacontainersizelabel form-group-label"><?php echo $LANG['WEB_MACHINE_OS_DATA_CONTAINER_SIZE']?></label>
																				<div class="col-md-4 form-group-content">
																					<select class="form-control select2me" id="data_container_size">
																						<option value="1073741824">1 GB</option>
																						<option value="2147483648">2 GB</option>
																						<option value="3221225472">3 GB</option>
																						<option value="4294967296">4 GB</option>
																					</select>
																				</div>
																				<div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                        <a class="popovers" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DATA_CONTAINER_SIZE_TIPS']?> ">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
																			</div>

																			<div class="form-group mergemodediv">
																				<label class="control-label col-md-3 mergemodelabel form-group-label"><?php echo $LANG['WEB_MACHINE_OS_REDUNDANT_DATA_PROPORTION']?></label>
																				<div class="col-md-4 form-group-content">
																					<select class="form-control select2me" id="redundant_data_proportion">
																						<option value="10">10%</option>
																						<option value="30">30%</option>
																						<option value="50" selected="">50%</option>
																						<option value="70">70%</option>
																						<option value="90">90%</option>
																					</select>
																				</div>
																				<div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                        <a class="popovers" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_MERGE_REDUNDANT_DATA_PROPORTION_TIPS']?> ">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
																			</div>
																			
																		</div>
																		
																	</div>
																</div>
																<div class="tab-pane" id="overload_protection">
																	<div class="col-md-12 ">
																		
																		<div class = "col-md-12 advanced-detail-conf-district">
																			 <div class="form-group mt45">
																				<label class="control-label col-md-3"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></label>
																				<div class="col-md-1">
																					<div class="form-group-content">
																						<input type="checkbox" id="ignore_resource_limiting_flag" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																					</div>
																				</div>
																				<div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS']?>" >
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
												 <!-- 脚本配置 -->
												<div class="tab-pane " id="tab_script">
													<div class="panel-body">
														<!-- 脚本配置 -->
														<div class="form-group">
                                                                <label class="control-label col-md-2 form-group-label"><?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE'] ?></label>
                                                                <div class="col-md-4">
																	<select class="form-control" id="script_host">
																		
																	</select>
																</div>
																<div class="col-md-2">
                                                                    <button class="btn btn-light-primary me-2" id="addScript" type="button"><?php echo $LANG['UI_MACHINE_OS_ADD_SCRIPT_CONFIGURE'] ?></button>
																</div>
                                                            </div>
                                                            <!-- 详情 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-2 form-group-label"></label>
                                                                <div class="col-md-9">
                                                                    <div class="add-list">
                                                                        <div class="accordion scriptBox" role="tablist">

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
												<label class="control-label col-md-3"><?php echo $LANG['UI_MACHINE_OS_SELECTED_BACKUP_SOURCE'] ?>:</label>
												<div class="col-md-9">
													<div class="form-control-static sourceInfoShow">
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
											<div class="form-group mb0 mt10 display-none">
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
											<div class="form-group mb0 mt10 speedlimitDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static transforshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10 tab_safety">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
												<div class="col-md-9">
													<div class="form-control-static safetyshow">
													</div>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_USER_HIGH_SETTING'] ?>:</label>
												<div class="col-md-9">
													<div class="form-control-static advancedshow">
                                                      
													</div>
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
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/public/strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	//如果不是英文,加载语言包
	echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/platform/global_strategy/speed_strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/backup-strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backupSource.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_worm.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/strategy_group_selector.js"></script>
<!-- <script type="text/javascript" src="./scripts/platform/component/safe_surebackup.js"></script> -->
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/complete_machine_os/machine_os_backup.js" type="text/javascript"></script>