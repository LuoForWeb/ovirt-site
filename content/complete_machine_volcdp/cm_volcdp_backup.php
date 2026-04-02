<?php include_once '../../tpl/permission.php';
include_once '../platform/public/bs_table.php';
?>
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

<link href="/assets/global/plugins/select2/select2-4.1.0/css/select2.min.css"rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/driver_check.css" />
<link rel = "stylesheet" type="text/css" href="./css/platform/component/safe_virus.css" />
<link rel = "stylesheet" type="text/css" href="./css/platform/component/network_config.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />

<?php
session_start();
session_commit();
?>
<!-- BEGIN PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:100%; ">
	<div class="col-md-12" style="height:100%;">
		<div class="portlet box blue-hoki backup-page" id="completeMachineVolcdpbackupcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-xinjianbeifen1"></i>
					<span class="backup_task_title">
						<?php echo !empty($_GET['uuid']) ? $LANG['UI_COMPLETE_MACHINE_VOL_CDP_CREATE_BAK_JOB_EDIT'] : $LANG['UI_COMPLETE_MACHINE_VOL_CDP_CREATE_BAK_JOB']; ?>
					</span>

				</div>
			</div>
			<div class="portlet-body form cmbackuptaskconf">
				<input type="hidden" id="cm_backup_task_type" value="<?php echo $_GET['task_type']; ?>" class="display-none"/>
                <input type="hidden" id="uuid" value="<?php echo $_GET['uuid'] ?>" class="display-none"/>
				<div class="form-horizontal" id="submit_form">
					<div class="form-wizard">
						<div class="form-body">
							<!--           导航步骤                 -->
							<ul class="nav nav-pills steps">
								<li>
									<a href="#tab1" data-toggle="tab" class="step">
										<span class="number">1 </span>
										<span class="desc step1">
											<?php echo $LANG['UI_BACKUP_SOURCE']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
										<span class="number">2 </span>
										<span class="desc step2">
											<?php echo $LANG['UI_BACKUP_DATA_DES']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab3" data-toggle="tab" class="step active">
										<span class="number">3 </span>
										<span class="desc step3">
											<?php echo $LANG['UI_JOB_STRATEGY_INFO']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab4" data-toggle="tab" class="step">
										<span class="number">4 </span>
										<span class="desc step4">
											<?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
							</ul>
							<!--           中间内容        -->
							<div class="tab-content bakuptab">
								<div class="tab-pane active" id="tab1">
									<div class="row tab-pane__row">
										<!-- 备份客户端 -->
										<div class="col-md-3 tab-pane__row__source">
											<div class="form-group src-wrap">
												<div class="src-wrap__title">
													<span>
														<i class="levelchild viconfont vicon-ge_backup_host"></i> <?php echo $LANG['UI_VOL_CDP_SELECT_HOST']; ?>
													</span>
												</div>
												<div class="src-wrap__content">
													<div class="src-wrap__content__search">
														<div class="vm_tree_div">
															<div class="width100p searchDiv">
																<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_SEARCH_CLIENT_KEY_WORD_TIPS']; ?>" class="form-control" id="searchAgent" onkeyup="customInputValidate('string', this.value, $(this))" />
															</div>
														</div>
													</div>
													<div class="src-wrap__content__ztree">
														<div class="vcenter-tree">
															<ul id="agent_tree" class="ztree "></ul>
														</div>
														<div class="alert alert-block alert-info fade in display-hide" id="novolcdpagent">
															<button type="button" class="close" data-dismiss="alert"></button>
                                                            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                            <ol class="alert-ol">
                                                                <a id="toAdd" class="alert-link"><?php echo $LANG['WEB_MACHINE_OS_BACKUP_NO_CLIENT'] ?></a>
                                                            </ol>
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
										<!--没有选择客户端时的提示信息-->
										<div class="col-md-9">
											<div class="alert alert-block alert-info fade in" id="step1tips">
												<button type="button" class="close" data-dismiss="alert"></button>
												<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
												<ol class="alert-ol">
													<li>
														<?php echo $LANG['UI_VOL_CDP_BAK_TIPSONE']; ?>
													</li>
													<li>
														<?php echo $LANG['UI_VOL_CDP_BAK_TIPSTWO']; ?>
													</li>
												</ol>
												<div class="alert-line"></div>
												<p><?php echo $LANG['UI_VOL_CDP_BAK_TIPS_PS']; ?></p>
											</div>
										</div>
										<!--选择的客户端对应的应用信息-->
										<div class="col-md-9 display-hide host-info-col" id="hostinfo">
											<div class="col-md-5 host-info-col__app" id="hostappdiv">
												<div class="form-group src-wrap">
													<div class="src-wrap__title">
														<span>
															<i class="levelchild viconfont vicon-ge_monitoring_application"></i> <?php echo $LANG['UI_VOL_CDP_MONITOR_DEVICE_SELECT']; ?>
														</span>
													</div>
													<div class="src-wrap__content">
														<div class="src-wrap__content__voltree">
															<div class="vcenter-tree">
																<ul id="cm_volcdp_app_tree" class="ztree tree_div "></ul>
															</div>
															<div class="alert alert-block alert-info fade in display-hide" id="noappinfo">
																<button type="button" class="close" data-dismiss="alert"></button>
																<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
																<ol class="alert-ol">
																	<li>
																		<?php echo $LANG['UI_VOL_CDP_MONITOR_TIPSONE']; ?>
																	</li>
																	<li>
																		<?php echo $LANG['UI_VOL_CDP_MONITOR_TIPSTWO']; ?>
																	</li>
																</ol>
															</div>
															<div class="alert alert-block alert-info fade in" id="checkapptips">
																<button type="button" class="close" data-dismiss="alert"></button>
																<ul class="alert-ul">
																	<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																	<li><?php echo $LANG['UI_CM_CDP_MONITOR_TIPS']; ?></li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-7 host-info-col__app" id="hostvoldiv">
												<div class="form-group src-wrap">
													<div class="src-wrap__title">
														<span>
															<i class="levelchild viconfont vicon-ge_monitoring_application"></i> <?php echo $LANG['UI_VOL_CDP_MONITOR_SELECT']; ?>
														</span>
													</div>
													<div class="src-wrap__content">
														<div class="src-wrap__content__voltree">
															<div class="vcenter-tree">
																<div id="checkedHostDiskSourceDiv" ></div>
															</div>
															 <div class="alert alert-block alert-info fade in" id="checkapptips">
																<button type="button" class="close" data-dismiss="alert"></button>
																<ul class="alert-ul">
																	<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																	<li><?php echo $LANG['UI_CM_CDP_BACKUP_DISK_TIPS']; ?></li>
																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- 备份目的地	 -->
								<div class="tab-pane vol-cdp-backup-tab" id="tab2">
									<div class="row">
										<div class="col-md-9" id="backupTarget"></div>
											<!--
											<div class="form-group diynodediv">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_NODE']; ?></label>
												<div class="col-md-9">
													<select class="form-control " id="selectnode">
													</select>
												</div>
											</div>
											<div class="form-group diystoragediv">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE']; ?></label>
												<div class="col-md-9">
													<select class="form-control " id="selectstorage">
													</select>
												</div>
											</div>
											-->
											<!-- 存储节点及存储路径提示 -->
											<!--
											<div class="form-group">
												<div class="col-md-offset-3 col-md-9">
													<div class="alert alert-block alert-info fade in" id="step1tips">
														<button type="button" class="close" data-dismiss="alert"></button>
														<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
														<ol class="alert-ol">
															<li><?php echo $LANG['UI_BACKUP_DES_NODE_TIPS']; ?></li>
															<li><?php echo $LANG['UI_BACKUP_DES_STORAGE_TIPS']; ?></li>
														</ol>
													</div>
												</div>
											</div>
											-->
										<div class="col-md-9">
											 <!-- 复制 --备机配置 -->
											<div class="form-group  replicationstandbyconfdiv">
												<label class="control-label col-md-3 standbyconflabel form-group-label"><?php echo $LANG['UI_CM_CDP_COPY_STANDBY_CONF']; ?></label>
												<div class="col-md-9">
													<button type="button" class="btn btn-light-primary cmcopystandbymapconf" id=""><?php echo $LANG['UI_VOL_CDP_STANDBY_CONF_MAP']; ?></button>
													<ul id="" class="pl0 display-none cmcdpcopystandbyconfdes">

													</ul>
												</div>
											</div>

											<!-- 复制 -- 备份数据到备份服务器  -->
											<div class="form-group encrypttransferdiv  replicationbkdatatoserver">
												<label class="control-label col-md-3 doublehostmirrorlabel form-group-label"><?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET']; ?></label>
												<div class="col-md-4 form-group-content">
													<input type="checkbox"  class="make-switch backupdatatoserver" data-size="small" data-on-color="primary" data-off-color="info" checked="checked" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>">
													<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_COL_CDP_BACKUP_MIRROR_DATA_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>

										</div>
									</div>
								</div>
								<div class="tab-pane vol-cdp-backup-tab" id="tab3">
									<div class="row row-stepthree">
										<div class="tabbable-custom col-md-offset-1 col-md-10">
											<ul class="nav nav-tabs">
												<li class="commonLi active">
													<a href="#tab_common" data-toggle="tab">
														<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
												</li>
												<li class="">
													<a href="#tab_transfer" data-toggle="tab">
													<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
												</li>
												<li class="">
													<a href="#tab_takeover" data-toggle="tab">
													<i class="viconfont vicon-jieguan"></i> <span class="takeoverstrategylabel"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE_EMERGENCY'] ?></span> </a>
												</li>
												<li class="">
													<a href="#tab_script" data-toggle="tab">
													<i class="viconfont vicon-ziyuanxiangqing"></i> <?php echo $LANG['UI_CM_CDP_SCRIPT_CONFIGURE']; ?> </a>
												</li>
												<li class="highLi">
													<a href="#tab_high" data-toggle="tab">
													<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_CM_CDP_ADVANCDED_STRATEGY'] ?></a>
												</li>
											</ul>
											<div class="tab-content">
												<!-- 通用策略 -->
												<div class="tab-pane active" id="tab_common">
												<div class="panel-body">
														<div class="form-group labelstrategyvew">
															<div class="col-md-offset-1 col-md-10 accordion strategyOne ">
																<div class="panel panel-default strategy-panel">
																	<div class="panel-heading">
																		<h4 class="panel-title">
																			<a class="accordion-toggle accordion-toggle-styled  popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupTime" aria-expanded="true">
																				<i class="viconfont vicon-shijian font-green-seagreen"></i>
																				<span class="font-green-seagreen"><?php echo $LANG['UI_JOB_LABEL_STRATEGY']; ?></span>
																				<span class="strategyDes backupTimeDes"></span>
																			</a>
																		</h4>
																	</div>
																	<div id="backupTime" class="panel-collapse collapse in">
																		<div class="panel-body">
																			<div class="col-md-12">
																				<div class="form-group display-none">
																					<label class="control-label col-md-2"><?php echo $LANG['UI_VOL_CDP_TIMED_START_STRATEGY']; ?></label>
																					<div class="col-md-6">
																						<div class="input-group date form_datetime">
																							<input type="text" size="16" id="timing_start" class="form-control input-sm">
																							<span class="input-group-btn">
																								<button class="btn default input-sm" id="resetstartdate" type="button"><i class="fa fa-times"></i></button>
																								<button class="btn default date-set input-sm" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
																							</span>
																						</div>
																					</div>
																				</div>

																				<div class="form-group setTagsStrategy">
																					<!-- <label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_TIMED_START_STRATEGY']; ?></label> -->
																					<div class="col-md-12">
																						<button type="button" id="add_tagpoint_limit" class="btn btn-light-primary">
																							<i class="viconfont vicon-ge_add_task"></i><?php echo $LANG['UI_VOL_CDP_ADD_LABEL_STRATEGY']; ?>
																						</button>

																						<ul id="tagpoint_list" style="padding-left: 0;">

																						</ul>
																					</div>
																				</div>
																				<div class="alert alert-block alert-info fade in mx-15 " id="">
																					<button type="button" class="close" data-dismiss="alert"></button>
																					<ul class="alert-ul">
																						<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																						<li><?php echo $LANG['UI_BACKUP_TASK_BACKUP_TAG_TIPS']; ?> </li>
																					</ul>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>

														<div class="form-group speedlimitDiv">
															<div class="col-md-offset-1 col-md-10 accordion strategyOne">
																<div class="panel panel-default strategy-panel">
																	<div class="panel-heading">
																		<h4 class="panel-title">
																			<a class="accordion-toggle accordion-toggle-styled collapsed popovers" name = "speedlimitview" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
																				<i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
																				<span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']; ?></span>
																				<span class="strategyDes speedlimitDes"></span>
																			</a>
																		</h4>
																	</div>
																	<?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
																	<!--       全局限速策略组合的组件配置-->
																</div>
															</div>
														</div>
														<div class="form-group storagestrageyview">
															<div class="col-md-offset-1 col-md-10 accordion strategyOne ">
																<div class="accordion strategyOne store-strategy-form">
																	<div class="panel panel-default strategy-panel">
																		<div class="panel-heading">
																			<h4 class="panel-title">
																				<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#storeconf" aria-expanded="true">
																					<i class="iconfont icon-cunchu font-green-seagreen"></i>
																					<span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
																					<span class="strategyDes storeDes"></span>
																				</a>
																			</h4>
																		</div>
																		<div id="storeconf" class="panel-collapse collapse ">
																			<div class="panel-body">
																				<div class="col-md-12">
																					<!-- 重复数据删除 -->
																					<div class="form-group display-none deduplicationDiv">
																						<label class="control-label col-md-2 deduplicationLabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_DEDUPLICATION'] ?></label>
																						<div class="col-md-4"><input type="checkbox" id="deduplicationCheck" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>"></div>
																						<div class="col-md-2">
																							<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DEDUPLICATION_TIPS'] ?>">
																								<i class="viconfont vicon-tishi"></i>
																							</a>
																						</div>
																					</div>
																					<!-- 压缩存储 -->
																					<div class="form-group">
																						<label class="control-label col-md-2 compressLabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?></label>
																						<div class="col-md-1">
																							<input type="checkbox" id="compressCheck" checked class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																						</div>
																						<div class="col-md-2">
																							<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_COMPRESS_TIPS'] ?>">
																								<i class="viconfont vicon-tishi"></i>
																							</a>
																						</div>
																					</div>
																					<!-- 数据加密 -->
																					<div class="form-group">
																						<label class="control-label col-md-2 encryptStorageLabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?></label>
																						<div class="col-md-1">
																							<input type="checkbox" id="encryptStorageCheck" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																						</div>
																						<div class="col-md-2">
																							<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DATE_ENCRY_TIPS'] ?>">
																								<i class="viconfont vicon-tishi"></i>
																							</a>
																						</div>
																					</div>
																					<!-- 自动生成密码 -->
																					<div class="form-group display-none passwordModeDiv">
																						<label class="control-label col-md-2 passwordAutoLabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?></label>
																						<div class="col-md-4">
																							<input type="checkbox" id="passwordAutocheck" class="make-switch" checked data-on-color="primary" data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																						</div>
																					</div>
																					<!-- 密码 -->
																					<div class="form-group display-none passwordDiv">
																						<label class="control-label col-md-2 passwordlabel"><?php echo $LANG['UI_BACKUP_PASSWORD'] ?></label>
																						<div class="col-md-4">
																							<input type="password" autocomplete="off" maxlength="128" class="form-control input-sm" id="password" placeholder="" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
																						</div>
																					</div>
																					<!-- 确认密码 -->
																					<div class="form-group display-none passwordDiv">
																						<label class="control-label col-md-2 repasswordlabel"><?php echo $LANG['UI_BACKUP_PASSWORD_CONFIRM'] ?></label>
																						<div class="col-md-4">
																							<input type="password" autocomplete="off" maxlength="128" class="form-control input-sm" id="repassword" placeholder="" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
																						</div>
																						<div class="col-md-4 display-none passwordTips" style="padding: 10px 0 0 0;color: #F3565D;"><?php echo $LANG['UI_BACKUP_PASSWORD_CONFIRM_ERROR'] ?></div>
																					</div>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														<!-- 保留策略view -->
														<div class="form-group reservestrategyview">
															<div class="col-md-offset-1 col-md-10 accordion strategyOne">
																<div class="panel panel-default strategy-panel">
																	<div class="panel-heading">
																		<h4 class="panel-title">
																			<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#reserve" aria-expanded="true">
																				<i class="iconfont icon-baoliu font-green-seagreen"></i>
																				<span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY']; ?></span>
																				<span class="strategyDes reserveDes"></span>
																			</a>
																		</h4>
																	</div>
																	<div id="reserve" class="panel-collapse collapse">
																		<div class="panel-body">
																			<div class="col-md-12">
																				<div class="form-group reserveDay">
																					<label class="control-label col-md-2"><?php echo $LANG['WEB_VOL_CDP_BACKUP_RESERVE_DAY']; ?>
																					</label>
																					<div class="col-md-4">
																						<div id="spinnerDay">
																							<div class="input-group spinner-group">
																								<input type="text" id="spinnerDayInput" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="3">
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
																					<div class="col-md-2 mt5">
																						<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_BACKUP_RESERVE_DAY_TIPS']; ?>">
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
												<!-- 传输策略 -->
												<div class="tab-pane " id="tab_transfer">
												<div class="panel-body">
														<div class="tabbable-custom col-md-10">
															<!-- 传输模式 -->
															<div class="form-group transportdiv">
																<label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']; ?></label>
																<div class="col-md-4">
																	<select class="form-control " id="transport_mode">
																		<option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']; ?></option>
																		<!--  该阶段暂时仅支持网络传输  modify time:2022-4-8 15:09:42
																		<option value="nbdssl"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBDSSL']; ?></option>
																		<option value="san"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN']; ?></option>
																		<option value="hotadd"><?php echo $LANG['UI_BACKUP_TRANSPORT_HOT']; ?></option>
																		-->
																	</select>
																</div>
																<div class="col-md-2 mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_ICS_VVDK_SELECT_TIPS']; ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>

															<!-- <div class="form-group display-none transfernetworkDiv">
																<label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?></label>
																<div class="col-md-4">
																	<select class="form-control " id="transferNetwork">
																	</select>
																</div>
																<div class="col-md-2 mt10">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS']; ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div> -->


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

															<!-- 加密传输-->
															<div class="form-group encrypttransferdiv">
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
																	<select class="form-control  input-sm" id="transferEncryptMethod">
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
																<label class="control-label col-md-3 transfercompress form-group-label pl0_en"><?php echo $LANG['UI_PLATFORM_SRC_COMPRESS']; ?></label>
																<div class="col-md-4 form-group-content">
																	<input type="checkbox" id="tran_compress_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																	<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_COMPRESSION_TIPS']; ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															<!-- 压缩等级选择 -->
															<div class="form-group transferCompressGradeDiv display-none">
																<label class="control-label transfer-compress-grade-label col-md-3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
																<div class="col-md-4">
																	<select class="form-control  input-sm" id="transferCompressGrade">
																		<option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
																		<option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
																		<option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
																		<option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
																	</select>
																</div>
															</div>
															<!-- 传输线程个数 -->
															<div class="form-group transferDiv">
																<label class="control-label col-md-3 transferthread"><?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS']; ?>
																</label>
																<div class="col-md-4">
																	<div id="transfer_thread_div">
																		<div class="input-group spinner-group">
																			<input type="text" id="transfer_thread_number" oninput="if(!/^[1-4]+$/.test(value)) value=value.replace(/\D/g,'');if(value>4)value=4;if(value<1)value=1" class="spinner-input form-control" maxlength="1">
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
																<div class="col-md-2 mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS_TIPS']; ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															<!-- 传输数据包大小 -->
															<div class="form-group transferDataPackage">
																<label class="control-label col-md-3 transferdatapackage"><?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE']; ?>
																</label>
																<div class="col-md-4">
																	<select class="form-control " id="transfer_datapackage_size">
																		<option value=1> 1 MB</option>
																		<option value=2>2 MB</option>
																		<option value=4 selected>4 MB</option>
																		<option value=8>8 MB</option>
																		<option value=16>16 MB</option>
																	</select>
																</div>
																<div class="col-md-2  mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TRANS_DATA_PACKED_SIZE_TIPS']; ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															<!-- 重连次数 Bug #14474需求暂时屏蔽-->
															<div class="form-group display-none  reconnect-times-wrapper">
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
															<!-- 重连时间间隔 Bug #14474需求暂时屏蔽 -->
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
												<!-- 接管配置 -->
												<div class="tab-pane " id="tab_takeover">
													<?php include_once('cm_cdp_backup_takeover_config.php'); ?>

												</div>
												<!-- 脚本配置 -->
												<div class="tab-pane " id="tab_script">
													<div class="advance-config-wrap">
														<div class="row row-stepthree advance-config-wrap__row m0">
															<div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios">
																<ul class="nav nav-tabs tabs-left">
																	<li class="commonLi active ">
																		<a href="#tab_before_backup_script" class="popovers" data-content="<?php echo $LANG['UI_CM_CDP_BEFORE_BACKUP_EXE_SCRIPT']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																			<?php echo $LANG['UI_PUBLIC_BEFORE_BACKUP_SCRIPT']; ?> </a>
																	</li>
																	<li class="">
																		<a href="#tab_after_backup_script" class="popovers" data-content="<?php echo $LANG['UI_CM_CDP_AFTER_BACKUP_EXE_SCRIPT']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo $LANG['UI_PUBLIC_AFTER_BACKUP_SCRIPT']; ?> </a>
																	</li>
																	<li class="display-none beforetakeoverscriptview">
																		<a href="#tab_before_takeover_script" class="popovers" data-content="<?php echo $LANG['UI_CM_CDP_TAKEOVER_EXE_BEFORE_START_SCRIPT']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo $LANG['UI_CM_CDP_BEFORE_TAKEOVER']; ?> </a>
																	</li>
																	<li class="display-none aftetakeoverscriptview">
																		<a href="#tab_after_takeover_script" class="popovers" data-content="<?php echo $LANG['UI_CM_CDP_TAKEOVER_EXE_FINISH_SCRIPT']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo $LANG['UI_CM_CDP_AFTER_TAKEOVER']; ?> </a>
																	</li>
																	<li class="display-none takeovermonitorscriptview">
																		<a href="#tab_takeover_monitor_script" class="popovers" data-content="<?php echo $LANG['UI_CM_CDP_TAKEOVER_MONITOR_SCRIPT_TIPS']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo $LANG['UI_CM_CDP_TAKEOVER_MONITOR'] ?> </a>
																	</li>
																	<!-- <li class="">
																		<a href="#tab_takeover_execute_script" class="popovers" data-content="<?php echo "启动接管前或启动接管后需要执行的脚本"; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo "接管执行脚本" ?> </a>
																	</li> -->
																</ul>
															</div>

															<div class="col-md-10 col-sm-9 col-xs-9">
																<div class="tab-content">
																	<div class="tab-pane active" id="tab_before_backup_script">
																		<div class="col-md-10 ">
																			<div class = "cmBeforeBackupScript"></div>
																		</div>
																	</div>
																	<div class="tab-pane " id="tab_after_backup_script">
																		<div class="col-md-10 ">
																			<div class = "cmAfterBackupScript"></div>
																		</div>
																	</div>
																	<div class="tab-pane " id="tab_before_takeover_script">
																		<div class="col-md-10 ">
																			<div class = "cmBeforeTakeoverScript"></div>
																		</div>
																	</div>
																	<div class="tab-pane " id="tab_after_takeover_script">
																		<div class="col-md-10 ">
																			<div class = "cmAfterTakeoverScript"></div>
																		</div>
																	</div>
																	<div class="tab-pane " id="tab_takeover_monitor_script">
																		<div class="col-md-10 ">
																			<div class = "cmTakeoverMonitorScript"></div>
																		</div>
																	</div>
																	<!-- <div class="tab-pane " id="tab_takeover_execute_script">
																		<div class="col-md-10 ">
																			<div class = "cmTakeoveExecuteScript"></div>
																		</div>
																	</div> -->
																</div>
															</div>
														</div>


													</div>
												</div>
												<!-- 高级配置 -->
												<div class="tab-pane " id="tab_high">
													<div class="advance-config-wrap">
														<div class="row row-stepthree advance-config-wrap__row m0">
															<div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios" style="align-items: flex-start;">
																<ul class="nav nav-tabs tabs-left">
																	<!-- <li class="commonLi active ">
																		<a href="#tab_common_conf" class="popovers" data-content="<?php echo $LANG['UI_CM_CDP_BACKUP_COMMON_CONF_TIPS']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																			 <?php echo $LANG['UI_CM_CDP_BACKUP_COMMON_CONF']; ?> </a>
																	</li>
																	<li class="">
																		<a href="#tab_cache_conf" class="popovers" data-content="<?php echo $LANG['UI_CM_CDP_BACKUP_CACHE_CONF_TIPS']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo $LANG['UI_CM_CDP_BACKUP_CACHE_CONF']; ?> </a>
																	</li>
																	<li class="">
																		<a href="#tab_resources_monitor_conf" class="popovers" data-content="<?php echo $LANG['UI_CM_CDP_BACKUP_RESOURCES_MONITOR_CONF_TIPS']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		 <?php echo $LANG['UI_CM_CDP_BACKUP_RESOURCES_MONITOR_CONF']; ?> </a>
																	</li> -->
																	<li class="commonLi active ">
																		<a href="#tab_common_conf" class="popovers" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																			 <?php echo $LANG['UI_CM_CDP_BACKUP_COMMON_CONF']; ?> </a>
																	</li>
																	<li class="">
																		<a href="#tab_cache_conf" class="popovers" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		<?php echo $LANG['UI_CM_CDP_BACKUP_CACHE_CONF']; ?> </a>
																	</li>
																	<li class="">
																		<a href="#tab_resources_monitor_conf" class="popovers" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																		 <?php echo $LANG['UI_CM_CDP_BACKUP_RESOURCES_MONITOR_CONF']; ?> </a>
																	</li>
																	<li class="">
																		<a href="#tab_over_load_strategy_pane" data-toggle="tab" aria-expanded="true" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																			<?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></a>
																	</li>
																</ul>
															</div>

															<div class="col-md-10 col-sm-9 col-xs-9">
																<div class="tab-content">
																	<!-- 公共配置 -->
																	<div class="tab-pane active" id="tab_common_conf">
																		<div class="col-md-12 ">
																			<!--<div class="advanced-conf-title-icon floatl mt2"></div>
																			<div class="floatl ">
																				<span class="ms-12"><?php /*echo $LANG['UI_CM_CDP_BACKUP_STORAGE_CONF_TIPS']; */?></span>
																			</div>-->

																			<div class = "col-md-12">
																				<!-- 存储数据库大小 -->
																				<div class="form-group standbyconf">
																					<label class="control-label col-md-3 storageblocksizelabel"><?php echo $LANG['UI_VOL_CDP_STORAGE_BLOCK_SIZE']; ?>
																					</label>
																					<div class="col-md-4">
																						<select class="form-control " id="storage_block_size">
																							<option value=4 selected>4 KB</option>
																							<option value=32>32 KB</option>
																							<option value=64>64 KB</option>
																							<option value=1024>1 MB</option>
																						</select>
																					</div>
																				</div>
																				<!-- 数据IO复制模式 -->
																				<div class="form-group ioreplicationdiv">
																					<label class="control-label col-md-3 ioreplicationlable"><?php echo $LANG['UI_VOL_CDP_IO_REPLICATION_MODE']; ?></label>
																					<div class="col-md-5 col-md-8_en">
																						<label class="control-label ">
																							<input type="radio" name="io_replication_modle" id="ioReplicationModle" value="1" /> <?php echo $LANG['UI_VOL_CDP_SYN']; ?>
																						</label>
																						<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_SYN_TIPS']; ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																						<label class="control-label ml50">
																							<input class="ml15" type="radio" name="io_replication_modle" id="ioReplicationModle" value="2" checked /> <?php echo $LANG['UI_VOL_CDP_ASY']; ?>

																						</label>
																						<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ASY_TIPS']; ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>

																				<!--有效数据备份-->
																				<div class="form-group validdatadiv">
																					<label class="control-label col-md-3 validdatadivlable form-group-label"><?php echo $LANG['UI_BACKUP_VALID_DATA']; ?></label>
																					<div class="col-md-4 form-group-content">
																						<input type="checkbox" id="validDataBackupSwitch" checked="checked" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"   data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																						<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_BACKUP_VALID_DATA_TIPS']; ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>

																				</div>
																				<!--跳过坏块备份-->
																				<div class="form-group skippingbadblocksbackupdiv">
																					<label class="control-label col-md-3 skippingbadblocksbacklable form-group-label"><?php echo $LANG['UI_CM_CDP_SKIP_BAD_BLOCK_BACKUP']; ?></label>
																					<div class="col-md-4 form-group-content">
																						<input type="checkbox" id="skippingBadBlocksBackupSwitch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"   data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																						<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_SKIP_BAD_BLOCK_BACKUP_TIPS']; ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>

																				</div>
																				<!--故障自动恢复【断点续传】配置-->
																				<div class="form-group automaticfaultrecoverydiv">
																					<label class="control-label col-md-3 automaticfaultrecoverylable form-group-label"><?php echo $LANG['UI_VOL_CDP_AUTO_FAULT_RECOVERY']; ?></label>
																					<div class="col-md-4 form-group-content">
																						<input type="checkbox" id="automatic_fault_recovery_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"  checked="checked" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																						<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_AUTO_FAULT_RECOVERY_TIPS']; ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>

																					</div>

																				</div>
																			</div>
																		</div>

																	</div>
																	<!-- 缓存配置 -->
																	<div class="tab-pane " id="tab_cache_conf">
																		<div class="col-md-12 ">
																			<!--<div class="advanced-conf-title-icon floatl mt2"></div>
																			<div class="floatl ">
																				<span class="ms-12"><?php /*echo $LANG['UI_CM_CDP_AGENT_CACHE_CONF']; */?></span>
																			</div>-->
																			<div class = "col-md-12 ">
																				<!-- 客户端文件缓存路径 -->
																				<div class="form-group file_cache_div">
																					<label class="control-label col-md-3 filecachepathlabel"><?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_PATH']; ?>
																					</label>
																					<div class="col-md-4 col-md-5_en">
																						<input type="text" maxlength="1024" readonly class="form-control" id="file_cache_path" value=<?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_DEFAULT']; ?> />
																					</div>
																					<div class="col-md-3 mt2 ml-10 ">
																						<button  id="customFileCachePath" class="btn btn-light-primary">
																							<span class="font-green-seagreen"><?php echo $LANG['UI_JOB_CHANGE_CUSTOM']; ?></span>
																						</button>

																						<button id="defaultFileCachePath" class="btn btn-light-primary display-none">

																							<span class="font-green-seagreen "><?php echo $LANG['UI_JOB_CHANGE_SYSTEM_DEFAULT']; ?></span>
																						</button>
																					</div>
																					<div class="col-md-2 mt5 col-md-1_en display-none ">
																						<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_PATH_TIPS']; ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>
																				<!-- 客户端文件缓存大小-->
																				<div class="form-group file_cache_div">
																					<label class="control-label col-md-3 filecachesizelabel"><?php echo $LANG['UI_VOL_CDP_CLIENT_FILE_CACHE_SIZE']; ?>
																					</label>
																					<div class="col-md-4 col-md-5_en">
																						<select class="form-control " id="file_cache_size">
																							<option value=4096 > 4 GB </option>
																							<option value=8192 selected> 8 GB </option>
																							<option value=16384> 16 GB</option>
																							<option value=32768> 32 GB </option>
																						</select>
																					</div>

																					<div class="col-md-2 mt5">
																						<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_SIZE_TIPS']; ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>
																				<div class="form-group vertical-dashed-line  ml50"></div>
																				<!-- 内存缓存 -->
																				<div class="form-group memorycachediv">
																					<label class="control-label col-md-3 memorycachelable form-group-label"><?php echo $LANG['UI_VOL_CDP_MEMORY_CACHE']; ?></label>
																					<div class="col-md-4 form-group-content col-md-5_en">
																						<input type="checkbox" id="memory_cache_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" checked="checked" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																						<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_MEMORY_CACHE_SIZE_TIPS']; ?>">
																							<i class="viconfont vicon-tishi"></i>
																						</a>
																					</div>
																				</div>

																				<!-- 设置客户端内存缓存大小-->
																				<div class="form-group set_memory_cache_div ">
																					<label class="control-label col-md-3 memorycachesizelabel"><?php echo $LANG['UI_VOL_CDP_CLIENT_MEMORY_CACHE_SIZE']; ?>
																					</label>
																					<div class="col-md-4 col-md-5_en">
																						<select class="form-control " id="memory_cache_size">
																							<!-- <option value=128>128 MB</option> -->
																							<option value=512 selected>512 MB</option>
																							<option value=1024>1 GB</option>
																							<option value=2048>2 GB</option>
																							<option value=4096>4 GB</option>
																							<option value=8192>8 GB</option>
																							<option value=16384>16 GB</option>

																						</select>
																					</div>
																				</div>


																			</div>
																		</div>

																	</div>
																	<!-- 资源监测配置 -->
																	<div class="tab-pane " id="tab_resources_monitor_conf">
																		<div class="" style="font-size: 14px !important;">
																			<?php require_once  '../platform/component/resource_monitoring.php'?>
																		</div>
																	</div>
																	<!-- 过载保护 -->
																	<div class="tab-pane fade" id="tab_over_load_strategy_pane">

																		<div class="col-md-12 ">
																			<!--<div class="advanced-conf-title-icon floatl mt2"></div>
																			<div class="floatl ">
																					<span class="ms-12"><?php /*echo $LANG['UI_CM_CDP_NODE_OVER_LOAD_CONF']; */?></span>
																			</div>-->
																			<div class = "col-md-12   height380 ">
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
									</div>
								</div>
								<!-- 任务配置汇总信息 -->
								<div class="tab-pane vol-cdp-backup-tab" id="tab4">
								<div class="tab-pane__body">
										<div class="tab-pane__body__form">
											<div class="alert alert-danger display-none jobnametip"></div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']; ?>:</label>
												<div class="col-md-9">
													<div class="input-icon right">
														<i class="fa"></i>
														<input type="text" maxlength="64" value=<?php echo $LANG['WEB_PT_OP_BACKUP_CREATE']; ?> class="form-control cmcdptaskname" id=""  onkeyup="customInputValidate('string', this.value, $(this))"/>
														<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']; ?></span>
													</div>
												</div>
											</div>
											<!-- 生产客户端 -->
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_PRODUCTION_CLIENT']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static volcdphostshow">
													</p>
												</div>
											</div>

											<h4 class="form-section"></h4>
											<!-- 目标节点 -->
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_NODE']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static colorgreen nodeinfoshow">
													</p>
												</div>
											</div>
											<!-- 目标存储-->
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static colorgreen storeinfoshow">
													</p>
												</div>
											</div>

											<!-- 复制配置 -->
											<div class="form-group mb0 mt10  backupstandbydiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_REPLICATION_MAP'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static colorgreen " id = "replicationBackupStandbyShow">
													</p>
												</div>
											</div>

											<!-- 复制 -备份数据到备份服务器 -->
											<div class="form-group mb0 mt10 backupdatatobackupserverdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static colorgreen backupdatatobackupservershow">
													</p>
												</div>
											</div>

											<!-- 备份策略 -->
											<h4 class="form-section"></h4>

											<!-- 定时标签策略 -->
											<div class="form-group mb0 mt10 tagpointinfoshowdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_TIMED_LABEL']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  tagpointinfoshow">
													</p>
												</div>
											</div>
											<!-- 限速策略 -->
											<div class="form-group mb0 mt10 speedlimitDiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  speedlimitshow">
													</p>
												</div>
											</div>
											<!-- 存储策略 -->
											<div class="form-group mb0 mt10 storageinfoshowdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  storageinfoshow">
													</p>
												</div>
											</div>

											<!-- 保留策略 -->
											<div class="form-group mb0 mt10 reservetypeshowdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  reservetypeshow">
													</p>
												</div>
											</div>

											<!-- 传输策略 -->
											<div class="form-group mb0 mt10" id="transportmodeshowdiv">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  transportinfoshow">
													</p>
												</div>
											</div>

											<!-- 接管配置策略 -->
											<div class="form-group mb0 mt10 takeoverswitchstrategyview" id="">
												<label class="control-label col-md-3 takeovertasklabel"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE_EMERGENCY']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  takeoverswitchstrategyshow">
													</p>
												</div>
											</div>

											<!-- 接管配置详情 -->
											<div class = "display-none takeoverconfdetailview">
																		<!-- 接管方式-->
												<div class="form-group mb0 mt10 takeovertypestrategyview" id="">
													<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  takeovertypestrategyshow">
														</p>
													</div>
												</div>
																		<!-- 接管主备映射-->
												<div class="form-group mb0 mt10 takeovermappingview" id="">
													<label class="control-label col-md-3"><?php echo $LANG['UI_CM_TAKEOVER_CONFIGURE']; ?>:</label>
													<div class="col-md-9 ">
														<p class="form-control-static  " id = "takeovermappingshow">
														</p>
													</div>
												</div>
																		<!-- 接管网络配置 -->
												<div class="form-group mb0 mt10 takeovernetworkconfview" id="">
													<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_TAKEOVER_NET_CONF']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  takeovernetworkconfshow" id = "takeovernetworkconfshow">
														</p>
													</div>
												</div>

																		<!-- 接管回切通讯网络 -->
												<div class="form-group mb0 mt10 takeoverfailbackview" id="">
													<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_TAKEOVER_FAILBACK_IP_CONF']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  takeoverfailbackshow">
														</p>
													</div>
												</div>
																		<!-- 接管安全配置 -->
												<div class="form-group mb0 mt10 takeoversafetyconfview" id="">
													<label class="control-label col-md-3"><?php echo $LANG['UI_SAFE_STRATEGY_VIRUS_CHECK']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  takeoversafetyconfshow">

														</p>
													</div>
												</div>
																		<!-- 应用接管 -->
												<div class="form-group mb0 mt10 apptakeoverview" id="">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_APPLICATION_TAKEOVER']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  apptakeovershow">
														</p>
													</div>
												</div>
																		<!-- 自动接管开关 -->
												<div class="form-group mb0 mt10 autotakeoverview" id="">
													<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_AUTO_TAKEOVER']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  autotakeovershow">
														</p>
													</div>
												</div>
																		<!-- 自动接管配置详情 -->
												<div class="form-group mb0 mt10 autotakeoverconfview" id="">
													<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_AUTO_TAKEOVER_CONF_TITLE']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  apptakeoverconfshow">
														</p>
													</div>
												</div>

											</div>





											<!-- 脚本配置策略 -->
											<div class="form-group mb0 mt10 taskscriptstrategyview" id="">
												<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_TASK_SCRIPT_STRATEGY']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  taskscriptstrategyshow">
													</p>
												</div>
											</div>

											<!-- 高级配置公共策略 -->
											<div class="form-group mb0 mt10 advancedstrategyview" id="">
												<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_ADVANCED_CONF_COMMON_POLICY']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  commonstrategyshow">
													</p>
												</div>
											</div>
											<!-- 高级配置缓存策略 -->
											<div class="form-group mb0 mt10 advancedstrategyview" id="">
												<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_ADVANCED_CONF_CACHE_POLICY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  cachestrategyshow">
													</p>
												</div>
											</div>
											<!-- 高级配置资源检测策略 -->
											<div class="form-group mb0 mt10 advancedstrategyview" id="">
												<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_ADVANCED_CONF_RESOURCE_MONITOR_POLICY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  resourcemonitorstrategyshow">
													</p>
												</div>
											</div>
											<!-- 高级配置过载保护策略 -->
											<div class="form-group mb0 mt10 advancedstrategyview" id="">
												<label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_ADVANCED_CONF_OVER_PROTECT_POLICY'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  overloadprotectstrategyshow">
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
									<a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
										<?php echo $LANG['UI_PUBLIC_NEXT_STEP']; ?> <i class="viconfont vicon-xiayibu"></i>
									</a>
									<a href="javascript:;" class="btn  green-turquoise button-submit">
										<?php echo $LANG['UI_PUBLIC_SUBMIT']; ?> <i class="viconfont vicon-xiayibu"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- 标签点策略模态框 -->
		<?php include_once('../../content/volcdp/tag_point_strategy.php'); ?>
		<!-- 备份任务复制模式模态配置框-->
		<?php include_once('cm_cdp_copy.php'); ?>
		<?php include_once('../../content/platform/component/takeover_network_map.php'); ?>
	</div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/public/strategy.js"></script>

<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>

<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="/assets/global/plugins/select2/select2-4.1.0/js/select2.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/libs/md5.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backupSource.js"></script>

<script type="text/javascript" src="./scripts/platform/component/targetPlug.js"></script>
<script type="text/javascript" src="./scripts/platform/component/network_config.js"></script>
 <script type="text/javascript" src="./scripts/platform/component/driver_check.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>

<!-- 脚本编辑器相关插件需要映入的内容 -->
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>

<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>

<script src="./scripts/volcdp/tagpoint_strategy.js"></script>
<script src="./scripts/complete_machine_volcdp/cm_volcdp_backup.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
