<?php include_once '../../tpl/permission.php';?>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css"/>

<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href="./content/platform/jobs/jobs.php" >
            <span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB']?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_OS_MIGRATION']?></span>
</h3>

<div class="row" style="height: calc(100% - 28px)">
	<div class="col-md-12"  style="height:100%;">
    <input id="task_uuid" value="<?php  echo $_GET['uuid'];?>" class="display-none"></input>
	    <input id="hypervisor" value="<?php echo $_GET['submodule'];?>" class="display-none"></input>
	    <input id="tasktype" value="<?php echo $_GET['tasktype'];?>" class="display-none"></input>
		<div class="portlet box blue-hoki" id="osmotioncontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift mt2"></i><?php echo $LANG['UI_OS_CREATE_MIGRATION_TASK']?>
				</div>
			</div>
			<div class="portlet-body form">
				<div action="#" class="form-horizontal" id="submit_form" method="POST">
					<div class="form-wizard">
						<div class="form-body">
							<div class="form-body-content">
								<ul class="nav nav-pills steps">
									<li>
										<a href="#tab1" data-toggle="tab" class="step">
										<span class="number">
										1 </span>
										<span class="desc">
										<?php echo $LANG['UI_OS_MIGRATE_TARGET']?> <i class="fa fa-check"></i> </span>
										</a>
									</li>
									<li>
										<a href="#tab2" data-toggle="tab" class="step">
										<span class="number">
										2 </span>
										<span class="desc">
										<?php echo $LANG['UI_OS_MIGRATE_METHOD']?> <i class="fa fa-check"></i></span>
										</a>
									</li>
									<li>
										<a href="#tab3" data-toggle="tab" class="step active">
										<span class="number">
										3 </span>
										<span class="desc">
										<?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG']?><i class="fa fa-check"></i></span>
										</a>
									</li>
								</ul>
								<div class="tab-content bakuptab">
									<div class="tab-pane active" id="tab1">
										<div class="alert alert-danger display-none setrecover2tip">
										</div>
										<div class="row tab-pane__row">
											<div class="col-md-10 col-steptwo">
												<!-- 选择已有的IP地址 ,多选-->
												<div class="form-group host_tree_div" id="selectIP">
													<label class="control-label col-md-2"><?php echo $LANG['WEB_OS_GOAL_IP']?> <span class="required">
													* </span>
													</label>
													<div class="col-md-4">
														<select id="IPlistSelect" name="" class="selectpicker show-tick form-control" multiple data-live-search="true" data-max-options="1" data-size ="5">
														</select>
													</div>
													<div class="col-md-2">
														<button type="button" class="btn green-haze" id="linkSelectIP"><?php echo $LANG['WEB_OS_LINK_TEST']?></button>
													</div>
												</div>
												
												<!-- 是否重新分区 -->
												<div id="zoneInfo" class="display-none">
													<div class="form-group display-none">
														<label class="control-label col-md-2 compresslabel"><?php echo $LANG['WEB_OS_RESTORE_VOLUME']?></label>
														<div class="col-md-4">
															<input type="checkbox" id="compresscheck" checked class="make-switch" data-on-color="primary" data-off-color="info" 
															data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
															data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
															<a class="popovers ml15" data-container="body" data-trigger="hover" 
																data-placement="right" data-content="<?php echo $LANG['WEB_OS_RESTORE_VOLUME_TIPS']?>">
																<i class="viconfont vicon-tishi"></i>
															</a>
														</div>
													</div>
												<!-- 目标卷 -->
													<div class="form-group">
														<label class="control-label col-md-2 compresslabel"><?php echo $LANG['WEB_OS_GOAL_VOLUME']?></label>
															<div class="col-md-9 accordion" style="overflow: auto;">
																<div class="table-container" id="osTable">
																</div>
															</div>
													</div>
												</div>
												<div class="form-group">
													<label class="control-label col-md-2"></label>
													<div class="col-md-9">
														<div class="alert alert-block alert-info fade in" id="tabletips">
															<button type="button" class="close" data-dismiss="alert"></button>
															<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
															<ol class = "alert-ol">
																<li>
																	<?php echo $LANG['WEB_OS_RECOVERY_TASK_TIPS1']?> 
																</li>
																<li>
																	<?php echo $LANG['WEB_OS_RECOVERY_TASK_TIPS2']?> 
																</li>
																<li>
																	<?php echo $LANG['WEB_OS_RECOVERY_TASK_TIPS3']?> 
																</li>
																<li>
																	<?php echo $LANG['WEB_OS_RECOVERY_TASK_TIPS4']?>
																</li>
															</ol>
														</div>
													
													</div>
													
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane" id="tab2">
										<div class="row row-stepthree">
											<div class="tabbable-custom col-md-offset-1 col-md-10" style="height: 100%;">
												<ul class="nav nav-tabs ">
													<li class="active">
														<a href="#tab_common" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_COMMON_STRATEGY_DES_TIPS']?>" 
																	data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY']?> </a>
													</li>
													<li class="transferLi">
														<a href="#tab_transfer" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_TIPS']?>" 
																	data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
														<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']?> </a>
													</li>
												</ul>
												<div class="tab-content min-height300" style="height: 90%;overflow-x:hidden;overflow-y: auto;">
													<div class="tab-pane active" id="tab_common">
														<div class="panel-body">
														<!-- 时间策略 -->
															<div class="form-group display-none">
																<div class="col-md-offset-1  col-md-10 pt15 ">
																	<div class="accordion strategyOne" >
																		<div class="panel panel-default strategy-panel">
																			<div class="panel-heading">
																				<h4 class="panel-title">
																					<a class="accordion-toggle accordion-toggle-styled collapsed popovers" 
																					data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#recoveryTime" aria-expanded="true">
																					<i class="iconfont icon-time font-green-seagreen"></i> 
																					<span class="font-gree-seagreen"><?php echo $LANG['UI_STRATEGY_TIME']?></span>
																					<span class="strategyDes recoveryTimeDes"></span>
																					</a>
																				</h4>
																			</div>
																			<div id="recoveryTime" class="panel-collapse collapse">
																				<div class="panel-body">
																					<div class="col-md-12">
																						<div class="form-group">
																							<label class="control-label col-md-2"><?php echo $LANG['UI_RECOVERY_TYPE']?> 
																							</label>
																							<div class="col-md-4">
																								<select class="form-control select2me" id="recovertype">
																									<option value="1"><?php echo $LANG['UI_RECOVERY_TYPE_NOW']?></option>
																									<option value="2"><?php echo $LANG['UI_RECOVERY_TYPE_STRATEGY']?></option>
																								</select>
																							</div>
																						</div>
																						<div class="form-group backupCrowd display-none">
																							<label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_TASK_CROWD']?>
																							</label>
																							<div class="col-md-6" >
																								<div class="taskCrowd mb15" id="backupCrowd"></div>
																							</div>
																						</div>
																						<div class="form-group display-none" id="setstrategy">
																							<label class="control-label col-md-2"><?php echo $LANG['UI_BACKUP_SET_STRATEGY']?> <span class="required">
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
													
															<!-- 通用策略-限速策略 -->
															<div class="form-group speedlimitDiv">
                                                                <div class="col-md-offset-1 col-md-10 accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                   data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
                                                                                    <i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
                                                                                    <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']?></span>
                                                                                    <span class="strategyDes speedlimitDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
															
															<!-- 高级策略 --> 
															<div class="form-group">
																<div class="col-md-offset-1 col-md-10 accordion strategyOne">
																	<div class="panel panel-default strategy-panel">
																		<div class="panel-heading">
																			<h4 class="panel-title">
																				<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupHigh" aria-expanded="true" data-original-title="" title="">
																				<i class="iconfont icon-gaojicelve font-green-seagreen"></i> 
																				<span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH']?></span>
																				<span class="strategyDes highDes"></span>
																				</a>
																			</h4>
																		</div>
																		<div id="backupHigh" class="panel-collapse collapse ">
																			<div class="panel-body">
																				<div class="col-md-9">
																				
																					<div class="form-group threadDiv">
																						<label class="control-label col-md-4 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM']?> 
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
																							<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['WEB_OS_THREAD_RANGE_TIPS']?>" data-original-title="" title="">
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

													<div class="tab-pane " id="tab_transfer">
														<div class="panel-body">
															<div class="tabbable-custom pdlr15  col-md-10" >
																<!-- 加密传输 -->
																<div class="form-group">
																	<label class="control-label col-md-3 encrypttransferlabel"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']?></label>
																	<div class="col-md-4">
																		<input type="checkbox" id="encrypttransfer"  class="make-switch" data-on-color="primary" data-off-color="info" 
																		data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
																		data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
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
																	<label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']?></label>
																	<div class="col-md-4">
																		<select class="form-control select2me" id="transferNetwork">
																		</select>
																	</div>
																	<div class="col-md-2 mt10">
																		<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
																		data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS']?>">
																		<i class="viconfont vicon-tishi"></i>
																		</a>
																	</div>
																</div>
																<!-- 重连次数 -->
																<div class="form-group reconnect-times-wrapper">
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
																<!-- 重连时间间隔 -->
																<div class="form-group reconnect-Interval-wrapper">
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
											</div>
										</div>  
									</div>
									<div class="tab-pane" id="tab3">
										<div class="form-group display-none">
											<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_POINT_INFO']?>:</label>
											<div class="col-md-9">
												<p class="form-control-static  vmtypeshow">
												</p>
											</div>
										</div>
										<h4 class="form-section"></h4>
										<div class="form-group">
											<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH']?>:</label>
											<div class="col-md-9">
												<p class="form-control-static  recovershow">
												</p>
											</div>
										</div>
										<h4 class="form-section"></h4>
										<div class="form-group ">
											<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']?>:</label>
											<div class="col-md-4">
												<p class="form-control-static  speedlimitshow">
												</p>
											</div>
										</div>
										<div class="form-group ">
											<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']?>:</label>
											<div class="col-md-4">
												<p class="form-control-static  transfershow">
												</p>
											</div>
										</div>
										<div class="form-group ">
											<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_THREAD_NUM']?>:</label>
											<div class="col-md-4">
												<p class="form-control-static  threadNumshow">
												</p>
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
                                        <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']?>
                                    </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP']?> <i class="viconfont vicon-xiayibu"></i>
                                    </a>
                                    <a href="javascript:;" class="btn green-haze button-submit">
                                        <?php echo $LANG['UI_PUBLIC_SUBMIT']?> <i class="viconfont vicon-xiayibu"></i>
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
				<h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">

					<div class="form-group ">
						<label class="control-label col-md-2">  <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE']?>
						</label>
						<div class="col-md-6">
							<select class="form-control select2me inline-block" id="speedModeType"  style="width:203px;">
								<option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY']?></option>
								<option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER']?></option>
							</select>
							<a class="popovers ml15" data-container="body" data-trigger="hover" 
                            data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_TYPE_TIPS']?>">
                            <i class="viconfont vicon-tishi"></i>
                            </a>
						</div>
					</div>

					<div class="form-group setSpeedStrategy">
						<label class="control-label col-md-2"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY']?>
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
						<label class="control-label col-md-2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE']?>
						</label>
						<div class="col-md-4">
							<div id="rateDiv" style="display:inline-flex;">
								<div class="input-icon" >
									<div id="speedSpinnerNum" >
										<div class="input-group" >
											<input type="text" id="speedSpinnerNumInput" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control">
											<div class="spinner-buttons input-group-btn">
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
									<select id="unit" style="width: 60px; margin-left: 10px;height: 33px;text-align:center;">
										<option value="1">KB/s</option>
										<option selected value="2">MB/s</option>
										<option value="3">GB/s</option>
									</select>
								</div>
								<a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body" data-trigger="hover" 
								data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS']?>">
								<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>
							
					</div>
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
				<button type="button"class="btn btn-primary" id="speed_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>

<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.osRecoveryType.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/os/osmotion.js" type="text/javascript"></script>
