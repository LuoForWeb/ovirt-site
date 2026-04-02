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
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:100%;">
	<div class="col-md-12" style="height:100%;">
		<input id="s_vmuuid" value="<?php echo $_GET['vmuuid']; ?>" class="display-none"></input>
		<input id="s_vcenteruuid" value="<?php echo $_GET['vcenteruuid']; ?>" class="display-none"></input>
		<input id="s_showtype" value="<?php echo $_GET['showtype']; ?>" class="display-none"></input>
		<input id="s_hypervisor" value="<?php echo $_GET['hypervisor']; ?>" class="display-none"></input>
		<div class="portlet box blue-hoki backup-page" id="volcdpbackupcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-xinjianbeifen1"></i>
					<span class = "vol_cdp_backup_task_title"><?php echo $LANG['UI_VOL_CDP_CREATE_BAK_JOB']; ?></span>
				</div>
			</div>
			<div class="portlet-body form">
				<!--  
					<div action="#" class="form-horizontal" id="submit_form" method="POST" style="height:100%;"> 
				-->
				<input type="hidden" id="volcdp_backup_task_type" value="<?php echo $_GET['task_type']; ?>" class="display-none"/>
				<div class="form-horizontal" id="submit_form">
					<div class="form-wizard">
						<div class="form-body">
							<ul class="nav nav-pills steps">
								<li>
									<a href="#tab1" data-toggle="tab" class="step">
										<span class="number">1 </span>
										<span class="desc">
											<?php echo $LANG['UI_BACKUP_SOURCE']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
										<span class="number">2 </span>
										<span class="desc">
											<?php echo $LANG['UI_BACKUP_DATA_DES']; ?>
											<i class="fa fa-check"></i>
										</span>
									</a>
								</li>
								<li>
									<a href="#tab3" data-toggle="tab" class="step active">
										<span class="number">3 </span>
										<span class="desc">
											<?php echo $LANG['UI_JOB_STRATEGY_INFO']; ?>
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
															<div class="width100p searchDiv display-none">
																<div class="input-icon">
																	<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD']; ?>" class="form-control" id="searchAgent" onkeyup="customInputValidate('string', this.value, $(this))" />
																</div>
															</div>
														</div>
													</div>
													<div class="src-wrap__content__ztree">
														<div class="vcenter-tree">
															<ul id="agent_tree" class="ztree ml-28"></ul>
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
										<!-- 没有客户端时展示提示信息 -->
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
										<!-- 选择的客户端对应的应用信息 -->
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
															<div class="alert alert-block alert-info fade in" id="checkapptips">
																<button type="button" class="close" data-dismiss="alert"></button>
																<ul class="alert-ul">
																	<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																	<li><?php echo $LANG['UI_VOL_CDP_MONITOR_TIPS']; ?></li>
																</ul>
															</div>
															<div class="vcenter-tree">
																<ul id="cdp_app_tree" class="ztree tree_div "></ul>
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
														</div>
													</div>
												</div>
											</div>
											<!-- 选择的客户端对应的卷信息  -->
											<div class="col-md-7 host-info-col__vol" id="hostvoldiv">
												<div class="form-group src-wrap">
													<div class="src-wrap__title">
														<span>
															<i class="levelchild viconfont vicon-ge_roll"></i> <?php echo $LANG['UI_VOL_CDP_MONITOR_SELECT']; ?>
														</span>
													</div>
													<div class="src-wrap__content">
														<div class="src-wrap__content__table">
															<div class="alert alert-block alert-info fade in" id="">
																<button type="button" class="close" data-dismiss="alert"></button>
																<ul class="alert-ul">
																	<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																	<li><?php echo $LANG['UI_VOL_CDP_MONITOR_AGENT_SELECT']; ?> </li>
																</ul>
															</div>
															<div class="backupVolDetails">
																<div class="table-container">
																	<div class="table-container">
																		<table class="table table-striped table-bordered table-hover" id="host_vol_table">
																			<thead>
																				<tr role="row" class="heading">
																					<th width="5%">
																						<input type="checkbox" class="group-checkable" >
																					</th>
																					<th width="35%">
																						<?php echo $LANG['UI_VOL_CDP_NAME']; ?>
																					</th>
																					<th width="20%">
																						<?php echo $LANG['UI_VOL_CDP_MOUNT_POINT']; ?>
																					</th>
																					<th width="20%">
																						<?php echo $LANG['UI_STORAGE_TOTAL_SIZE']; ?>
																					</th>
																					<th width="20%">
																						<?php echo $LANG['WEB_USERS_QUOTA_FREE_SIZE']; ?>
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

								<div class="tab-pane vol-cdp-backup-tab" id="tab2">
									<div class="row">
										<div class="col-md-9">
											<div class="form-group diynodediv">
												<label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_NODE']; ?></label>
												<div class="col-md-9">
													<select class="form-control select2me" id="selectnode">
													</select>
												</div>
											</div>
											<div class="form-group diystoragediv">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE']; ?></label>
												<div class="col-md-9">
													<select class="form-control select2me" id="selectstorage">
													</select>
												</div>
											</div>
											<!-- 存储节点及存储路径提示 -->
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
											<!-- 选择备份模式 -->
											<div class="form-group backupmodediv margintop-15">
												<label class="control-label col-md-3 doublehostmirrorlabel form-group-label"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD_JOBS_EMPTY_MODE']; ?></label>
												<div class="col-md-5">
													<select class="form-control select2me" id="backup_mode">
														<option value="1" selected><?php echo $LANG['UI_VOL_CDP_BACKUP_MODE_REAL_TIME_SYNC']; ?></option>
														<option value="2"><?php echo $LANG['UI_VOL_CDP_BACKUP_MODE_REAL_TIME_REPLICATION']; ?></option>
														<!-- <option value="3"><?php echo $LANG['UI_VOL_CDP_BACKUP_MODE_REAL_TIME_SYNC_AND_REPLICATION']; ?></option> -->
													</select>
												</div>
												<div class="col-md-1 ml5" style="padding-left: 0;">
													<button type="button" id="task_exec_mode" class="btn table-toolbar-btn" data-toggle="drawer" data-target="#task_exec_mode_drawer">
														<span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_BACKUP_EXECUTION_MODE_DESC']; ?></span>
													</button>
												</div>
											</div>
											<!-- 是否备份镜像数据到备份服务器 -->
											<div class="form-group encrypttransferdiv display-none bkimagedatatoserver">
												<label class="control-label col-md-3 doublehostmirrorlabel form-group-label"><?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET']; ?></label>
												<div class="col-md-4 form-group-content">
													<input type="checkbox" id="bk_imagedata_to_server" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" checked="checked" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>">
													<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_COL_CDP_BACKUP_MIRROR_DATA_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											<!-- 自动接管开关-实时同步 -->
											<div class="form-group realsyncautotakeoverdiv">
												<label class="control-label col-md-3 copytakeoverconfiglabel form-group-label"><?php echo $LANG['UI_VOL_CDP_ENABLE_AUTO_TAKEOVER']; ?></label>
												<div class="col-md-4 form-group-content">
													<input type="checkbox" id="real_sync_takeover_config_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
													<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>


											<!-- 选择备机类型 -->
											<div class="form-group display-none dualmachineimageswitchdiv">
												<label class="control-label col-md-3 changestandbytype form-group-label"><?php echo $LANG['UI_JOB_TAKEOVER_TYPE']; ?></label>
												<div class="col-md-5">
													<select class="form-control select2me" id="standby_host_mode">
														<option value="0" ><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TYPE_CHANGE']; ?></option>
														<option value="1" selected ><?php echo $LANG['UI_VOL_CDP_MOUNT_TAKEOVER']; ?></option>
<!--                                                        <option value="2" disabled>--><?php //echo "第三方虚拟化"; ?><!--</option>-->
														<option value="3"><?php echo $LANG['UI_VOL_CDP_COMPLETE_MACHINE_TAKEOVER']; ?></option>
													</select>
												</div>
												<div class="col-md-1 margintop10">
													<a class="popovers ml-10 " data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_BACKUP_TASK_STANDBY_TYPE_DESC']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											<!-- 备机配置 -->
											<div class="form-group display-none standbyconfdiv">
												<label class="control-label col-md-3 standbyconflabel form-group-label"><?php echo $LANG['UI_VOL_CDP_STANDBY_CONF']; ?></label>
												<div class="col-md-9">
													<button type="button" class="btn green-haze" id="standbyMapConf"><?php echo $LANG['UI_VOL_CDP_STANDBY_CONF_MAP']; ?></button>
													<ul id="standbyConfDes" class="pl0 display-none">

													</ul>
													<ul id="builtInVmConfDes" class="pl0 display-none">

													</ul>
													<ul id="otherVmConfDes" class="pl0 display-none">
<!--                                                        <span>第三方虚拟主机</span>-->
													</ul>
												</div>
											</div>



											<!--
											<div class="form-group doublehostmirrorswitchdiv margintop-15">
												<label class="control-label col-md-3 doublehostmirrorlabel form-group-label"><?php echo $LANG['UI_VOL_CDP_OPEN_DUAL_MACHINE']; ?></label>
												<div class="col-md-4 form-group-content">
													<input type="checkbox" id="double_host_mirror" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
													<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_OPEN_DUAL_MACHINE_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											-->
											<!-- 是否备份镜像数据到备份服务器
											<div class="form-group encrypttransferdiv display-none bkimagedatatoserver">
												<label class="control-label col-md-3 doublehostmirrorlabel form-group-label"><?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET']; ?></label>
												<div class="col-md-4 form-group-content">
													<input type="checkbox" id="bk_imagedata_to_server" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" checked="checked" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>">
													<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_COL_CDP_BACKUP_MIRROR_DATA_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>
											-->
											<!-- 双机镜像客户端
											<div class="form-group display-hide doublehostmirrordiv">
												<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE_SELECT']; ?></label>
												<div class="col-md-9">
													<select class="form-control select2me" id="doubleHostMirrorSelect">
													</select>
												</div>
											</div>

											<div class="form-group diskgenView display-none">
												<label class="control-label col-md-3 diynodelabel form-group-label"><?php echo $LANG['WEB_OS_RESTORE_VOLUME']; ?></label>
												<div class="col-md-4 form-group-content">
													<input type="checkbox" id="rebuildPartSwitch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
													<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_RESTORE_VOLUME_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>

											<!-- 配置双机镜像映射卷
											<div class="form-group display-hide doublehostmirrorvoldiv">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_MAPPED_VOLUMES']; ?></label>
												<div class=" col-md-9 accordion confvolmap">
													<div class="panel panel-default strategy-panel">
														<div class="panel-heading">
															<h4 class="panel-title">
																<a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#map_volume_view" aria-expanded="true">
																	<i class="fa fa-desktop font-green-seagreen"></i>
																	<span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_HOST']; ?></span>
																	<span class="strategyDes standbyhostDes"></span>
																</a>
															</h4>
														</div>
														<div id="map_volume_view" class="panel-collapse collapse stdmapvolume">
															<div class="panel-body">
																<div class="col-md-12">
																	<div class="table-container">
																		<div class="table-container">
																			<table class="table table-striped table-bordered table-hover" id="backup_host_vol_table">
																				<thead>
																					<tr role="row" class="heading">
																						<th width="25%">
																							<?php echo $LANG['UI_VOL_CDP_NAME']; ?>
																						</th>
																						<th width="25%">
																							<?php echo $LANG['UI_STORAGE_TOTAL_SIZE']; ?>
																						</th>
																						<th width="50%">
																							<?php echo $LANG['UI_VOL_CDP_INFORMATION']; ?>
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
											-->
											<!-- 配置双机镜像映射卷--end -->

											<!-- 自动接管开关 -->
											<div class="form-group display-none cdpautotakeoverswitchdiv">
												<label class="control-label col-md-3 autotakeoverconfiglabel form-group-label"><?php echo $LANG['UI_VOL_CDP_ENABLE_AUTO_TAKEOVER']; ?></label>
												<div class="col-md-4 form-group-content">
													<input type="checkbox" id="takeover_config_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
													<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER_TIPS']; ?>">
														<i class="viconfont vicon-tishi"></i>
													</a>
												</div>
											</div>

											<!-- 配置自动接管相关参数 -->
											<div class="form-group display-hide takeoverconfigveiw">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_CONFIGURE']; ?></label>
												<div class=" col-md-9 accordion autotakeover">
													<div class="panel panel-default strategy-panel">
														<div class="panel-heading">
															<h4 class="panel-title">
																<a class="accordion-toggle accordion-toggle-styled popovers autotakeoverconfdiv" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".autotakeover" href="#takeover_config_view" aria-expanded="true">
																	<i class="fa fa-desktop font-green-seagreen"></i>
																	<span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_SERVER']; ?></span>
																	<span class="strategyDes takeoverhostDes h-20px"><?php echo $LANG['UI_VOL_CDP_BACKUP_STANDBY_NOT_CONF_DESC']; ?> </span>
																</a>
															</h4>
														</div>
														<div id="takeover_config_view" class="panel-collapse collapse takeovercollapseview">
															<div class="panel-body" style="padding: 16px">
																<div class="col-md-12" style="padding: 0;">
																	<div class="row" style="height: 100%; margin: 0;">
																		<div class="tabbable-custom col-md-12" style="height: 100%;padding: 0;">
																			<ul class="nav nav-tabs ">
																				<li class="active">
																					<a href="#tab_takeover_common" data-toggle="tab">
																						<i class="iconfont icon-tongyongcelve font-green-seagreen"></i> <?php echo $LANG['UI_VOL_CDP_GENERAL_CONFIGURE']; ?> </a>
																				</li>
																				<li class="">
																					<a href="#tab_takeover_app_conf" data-toggle="tab">
																						<i class="viconfont vicon-ge_transfer font-green-seagreen"></i> <?php echo $LANG['UI_VOL_CDP_APPLICATION_CONFIGURE']; ?> </a>
																				</li>
																				<li class="">
																					<a href="#tab_takeover_script_conf" data-toggle="tab">
																						<i class="fa fa-file-code-o font-green-seagreen"></i> <?php echo $LANG['UI_VOL_CDP_SCRIPT_CONFIGURE']; ?> </a>
																				</li>

																			</ul>
																			<!-- 自动接管通用配置 -->
																			<div class="tab-content" style="height: calc(100% - 43px);overflow-x:hidden;overflow-y: auto;">
																				<div class="tab-pane active" id="tab_takeover_common">
																					<div class="panel-body">
																						<!--  重构调整
																						<div class="form-group">
																							<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE_SELECT']; ?></label>
																							<div class="col-md-8">
																								<select class="form-control select2me" id="sh_for_takeover">
																								</select>
																							</div>
																						</div>
																						-->
																						<!-- 接管网络配置 -->
																						<div class="form-group takeoverNetworkConf">
																							<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></label>
																							<div class="col-md-9">
																								<button type="button" class="btn green-haze" id="takeover_network_conf"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></button>
																								<ul id="takeover_ip_map_list" style="padding-left: 0;width:90%;">
																								</ul>
																							</div>
																						</div>

																						<div class="form-group takeoverrestoreipview">
																							<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_RECOVER_IP']; ?></label>
																							<div class="col-md-5">
																								<input type="text" maxlength="64" class="form-control" id="takeover_restore_ip" />
																							</div>
																							<div class="col-md-1" style="padding-left: 0;">
																								<div class=" popovers mt8">
																									<a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_BACKCUT_COMMUNICATION_IP_TIPS']; ?>">
																										<i class="viconfont vicon-tishi"></i>
																									</a>
																								</div>
																							</div>
																							<div class="col-md-3 ml-50 ml-26_en">
																								<button type="button" class="btn table-toolbar-btn" id="linkSelectIP">
																									<span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_FAILBACK_IP_PING']; ?></span>
																								</button>
																							</div>
																						</div>

																						<div class="form-group">
																							<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME']; ?></label>
																							<div class="col-md-5">
																								<div class="takeover_app_interval_div">
																									<div class="input-group spinner-group">
																										<input type="number" id="agent_heartbeat_failure_time" min="5" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
																										<div class="spinner-buttons input-group-btn spinner-group-btn">
																											<button type="button" class="btn default input-sm" id="addButton">
																												<i class="fa fa-angle-up"></i>
																											</button>
																											<button type="button" class="btn default input-sm" id="reduceButton">
																												<i class="fa fa-angle-down"></i>
																											</button>
																										</div>
																									</div>
																									<span class="control-label threadnumlabel"><?php echo $LANG['WEB_UTILS_SECOND']; ?> </span>
																								</div>
																							</div>
																							<div class="popovers mt8">
																								<a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME_TIPS']; ?>">
																									<i class="viconfont vicon-tishi"></i>
																								</a>
																							</div>
																						</div>
																						<div class="form-group" id="autoTakeoverHostTips">
																							<label class="control-label col-md-3 "></label>
																							<div class="col-md-8">
																								<div class="alert alert-block alert-info fade in" id="">
																									<button type="button" class="close" data-dismiss="alert"></button>
																									<ul class="alert-ul">
																										<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
																										<li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_HOST_TIPS']; ?></li>
																									</ul>
																								</div>
																							</div>
																						</div>

																						<div class="form-group display-none" id="auto_takeover_target_view">
																							<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_TARGET_POINT_CONFIGURE']; ?></label>
																							<div class="col-md-8">
																								<div class="table-container">
																									<div class="table-container">
																										<table class="table table-striped table-bordered table-hover" id="takeover_vol_target_table">
																											<thead>
																												<tr role="row" class="heading">
																													<!--  <th width="5%"> 
																														 <input type="checkbox" class="group-checkable" disabled>
																													</th> -->
																													<th width="25%">
																														<?php echo $LANG['UI_VOL_CDP_NAME']; ?>
																													</th>
																													<th width="25%">
																														<?php echo $LANG['UI_STORAGE_TOTAL_SIZE']; ?>
																													</th>
																													<th width="50%">
																														<?php echo $LANG['UI_VOL_CDP_TARGET_POINT']; ?>
																													</th>
																												</tr>
																											</thead>
																											<tbody></tbody>
																										</table>
																									</div>
																								</div>
																								<!-- 存储节点及存储路径提示 -->
																								<div class="form-group">
<!--																									<div class="col-md-12">-->
																										<div class="alert alert-block alert-info fade in" id="mountpointTips">
																											<button type="button" class="close" data-dismiss="alert"></button>
																											<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
																											<ol class="alert-ol">
																												<li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TIPSONE']; ?>
																												</li>
																												<li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TIPSTWO']; ?>
																												</li>
																												<li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TIPSTHREE']; ?>
																												</li>
																												<li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_MOUNT_POINT_TIPS']; ?>
																												</li>
																											</ol>
																										</div>
<!--																									</div>-->
																								</div>

																							</div>
																						</div>
																					</div>
																				</div>

																				<div class="tab-pane" id="tab_takeover_app_conf">
																					<div class="panel-body">
																						<!-- 自动接管开关 -->
																						<div class="form-group">
																							<label class="control-label col-md-3 apptakeoverlabel form-group-label"><?php echo $LANG['UI_VOL_CDP_ENABLE_APP_TAKEOVER']; ?></label>
																							<div class="col-md-4 form-group-content">
																								<input type="checkbox" id="app_takeover_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																								<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ENABLE_APP_TAKEOVER_TIPS']; ?>">
																									<i class="viconfont vicon-tishi"></i>
																								</a>
																							</div>
																						</div>

																						<div class="form-group display-none appmonitorswitchdiv">
																							<label class="control-label col-md-3 apptakeoverlabel form-group-label"><?php echo $LANG['UI_VOL_CDP_ENABLE_FAULT_MONITOR_TAKEOVER']; ?></label>
																							<div class="col-md-4 form-group-content">
																								<input type="checkbox" id="app_monitor_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																								<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ENABLE_FAULT_MONITOR_TAKEOVER_TIPS']; ?>">
																									<i class="viconfont vicon-tishi"></i>
																								</a>
																							</div>
																						</div>
																						<!-- 应该故障监测 -->
																						<div class="form-group display-none apptakeoverconfview">
																							<!-- 连续故障次数 -->
																							<div class="form-group display-none appfailurenumberview" style="display:flex;align-items:center;">
																								<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_FAULT_TIMES']; ?> </label>
																								<div class="col-md-4">
																									<div class="app_failure_number_div">
																										<div class="input-group spinner-group">
																											<input type="number" id="app_failure_number" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" min="1" max="100">
																											<div class="spinner-buttons input-group-btn spinner-group-btn">
																												<button type="button" class="btn default input-sm" id="addFailureNumber">
																													<i class="fa fa-angle-up"></i>
																												</button>
																												<button type="button" class="btn  default input-sm" id="reduceFailureNumber">
																													<i class="fa fa-angle-down"></i>
																												</button>
																											</div>
																										</div>
																									</div>
																								</div>
																								<span style="color:#666;"><?php echo $LANG['UI_VOL_CDP_TIMES']; ?> </span>
																								<div class="popovers" style="margin-left: 4px;">
																									<a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_FAULT_TIMES_TIPS']; ?>">
																										<i class="viconfont vicon-tishi"></i>
																									</a>
																								</div>
																							</div>

																							<!-- 故障检测频次 -->
																							<div class="form-group display-none takeoverappintervalview" style="display:flex;align-items:center;">
																								<label class="control-label col-md-3 takeoverappintervallabel"><?php echo $LANG['UI_VOL_CDP_FAULT_INTERVA']; ?> </label>
																								<div class="col-md-4">
																									<div class="takeover_app_interval_div">
																										<div class="input-group spinner-group">
																											<input type="number" id="takeover_app_interval" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" min="5" max="3600">
																											<div class="spinner-buttons input-group-btn spinner-group-btn">
																												<button type="button" class="btn  addTakeover default input-sm" id="heartBeatspinnerUp">
																													<i class="fa fa-angle-up"></i>
																												</button>
																												<button type="button" class="btn   default input-sm" id="reduceTakeover">
																													<i class="fa fa-angle-down"></i>
																												</button>
																											</div>
																										</div>
																									</div>
																								</div>
																								<span style="color:#666;"><?php echo $LANG['WEB_UTILS_SECOND']; ?> </span>
																								<div class="popovers" style="margin-left: 4px;">
																									<a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_FAULT_INTERVA_TIPS']; ?>">
																										<i class="viconfont vicon-tishi"></i>
																									</a>
																								</div>
																							</div>

																							<!-- 配置监测应用 -->
																							<div class="form-group display-none">
																								<label class="control-label col-md-3 monitoringapplabel"><?php echo $LANG['UI_VOL_CDP_MONITOR_APPLICATION']; ?> </label>
																								<div class="col-md-8">
																									<div class=" min-height100" id="takeover_app_tree_div">
																										<ul id="takeover_app_infotree" class="ztree tree_div height150"></ul>
																									</div>
																								</div>
																							</div>
																						</div>
																					</div>

																				</div>
																				<!-- 自定义监控脚本配置 -->
																				<div class="tab-pane" id="tab_takeover_script_conf">
																					<div class="panel-body">
																						<!-- 自定义脚本接管开关 -->
																						<div class="form-group">
																							<label class="control-label col-md-3 apptakeoverlabel form-group-label"><?php echo $LANG['UI_VOL_CDP_SCRIPT']; ?></label>
																							<div class="col-md-4 form-group-content">
																								<input type="checkbox" id="script_takeover_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																								<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_SCRIPT_TIPS']; ?>">
																									<i class="viconfont vicon-tishi"></i>
																								</a>
																							</div>
																						</div>
																						<div class="form-group display-hide script_takeover_conf_view">
																							<div class="form-group">
																								<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_SCRIPT_CONFIGURE']; ?> </label>
																								<div class="col-md-9" style="padding: 0">
																									<button type="button" id="addTakeoverScriptBut" class="btn btn-sm green-haze ml10">
																										<i class="viconfont vicon-ge_add_task"></i>&nbsp;<?php echo $LANG['UI_VOL_CDP_ADD_CUSTOM_SCRIPT']; ?>
																									</button>
																									<ul id="takeoverScriptList" style="padding-left: 0;margin-left: 10px;">

																									</ul>
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
									</div>
								</div>

								<div class="tab-pane" id="tab3">
									<div class="row row-stepthree">
										<div class="tabbable-custom col-md-offset-1 col-md-10">
											<ul class="nav nav-tabs ">
												<li class="active">
													<a href="#tab_common" data-toggle="tab">
														<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY']; ?> </a>
												</li>
												<li class="">
													<a href="#tab_transfer" data-toggle="tab">
														<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?> </a>
												</li>
												<li class="highLi">
													<a href="#tab_other" data-toggle="tab">
														<i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG']; ?> </a>
												</li>
											</ul>

											<div class="tab-content">
												<div class="tab-pane active" id="tab_common">
													<div class="panel-body">
														<div class="form-group">
															<div class="col-md-offset-1 col-md-10 accordion strategyOne">
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
																						<button type="button" id="add_tagpoint_limit" class="btn btn-sm green-haze">
																							<i class="viconfont vicon-ge_add_task"></i><?php echo $LANG['UI_VOL_CDP_ADD_LABEL_STRATEGY']; ?>
																						</button>

																						<ul id="tagpoint_list" style="padding-left: 0;">

																						</ul>
																					</div>
																				</div>
																				<div class="alert alert-block alert-info fade in" id="">
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
																			<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
																				<i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
																				<span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']; ?></span>
																				<span class="strategyDes speedlimitDes"></span>
																			</a>
																		</h4>
																	</div>
																	<!--<div id="speed" class="panel-collapse collapse ">
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
														<div class="form-group">
															<div class="col-md-offset-1 col-md-10 accordion strategyOne">
																<div class="accordion strategyOne store-strategy-form">
																	<div class="panel panel-default strategy-panel">
																		<div class="panel-heading">
																			<h4 class="panel-title">
																				<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#store" aria-expanded="true">
																					<i class="iconfont icon-cunchu font-green-seagreen"></i>
																					<span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
																					<span class="strategyDes storeDes"></span>
																				</a>
																			</h4>
																		</div>
																		<div id="store" class="panel-collapse collapse ">
																			<div class="panel-body">
																				<div class="col-md-12">
																					<!-- 重复数据删除 -->
																					<div class="form-group deduplicationDiv">
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
																						<div class="col-md-4">
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
																						<div class="col-md-4">
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
														<div class="form-group">
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

												<div class="tab-pane" id="tab_transfer">
													<div class="panel-body">
														<div class="tabbable-custom col-md-10">
															<!-- 传输模式 -->
															<div class="form-group transportdiv">
																<label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']; ?></label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="transport_mode">
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
																<label class="control-label col-md-3 transfercompress form-group-label"><?php echo $LANG['UI_PLATFORM_SRC_COMPRESS']; ?></label>
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
																	<select class="form-control select2me input-sm" id="transferCompressGrade">
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
																	<select class="form-control select2me" id="transfer_datapackage_size">
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

												<div class="tab-pane " id="tab_other">
													<div class="panel-body">
														<div class="tabbable-custom col-md-10">
															<!-- 存储数据块大小-->
															<div class="form-group storage_block_div">
																<label class="control-label col-md-3 storageblocklabel"><?php echo $LANG['UI_VOL_CDP_STORAGE_BLOCK_SIZE']; ?>
																</label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="storage_block_size">
																		<option value=4>4 KB</option>
																		<option value=32>32 KB</option>
																		<option value=64 selected>64 KB</option>
																		<option value=1024>1 MB</option>
																	</select>
																</div>
																<div class="col-md-2 mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_STORAGE_BLOCK_SIZE_TIPS']; ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>
															<!-- 客户端文件缓存 路径-->
															<div class="form-group file_cache_div">
																<label class="control-label col-md-3 filecachepathlabel"><?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_PATH']; ?>
																</label>
																<div class="col-md-4">
																	<input type="text" maxlength="1024" readonly class="form-control" id="file_cache_path" value=<?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_DEFAULT']; ?> />
																</div>
																<div class="col-md-3 mt2 ml-10 ">
																	<a href="javascript:void(0)" id="customFileCachePath" class="colorgreen">
																		<i class="levelchild viconfont vicon-ge_editable_item"></i>
																		<?php echo $LANG['UI_JOB_CHANGE_CUSTOM']; ?>
																	</a>
																	<a href="javascript:void(0)" id="defaultFileCachePath" class="colorgreen display-none">
																		<i class="levelchild viconfont vicon-ge_editable_item"></i>
																		<?php echo $LANG['UI_JOB_CHANGE_SYSTEM_DEFAULT']; ?>
																	</a>
																</div>
																<div class="col-md-2 mt5 display-none">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_PATH_TIPS']; ?>">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>

															<!-- 客户端文件缓存大小-->
															<div class="form-group file_cache_div">
																<label class="control-label col-md-3 filecachesizelabel"><?php echo $LANG['UI_VOL_CDP_CLIENT_FILE_CACHE_SIZE']; ?>
																</label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="file_cache_size">
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

															<!-- 内存缓存 -->
															<div class="form-group memorycachediv">
																<label class="control-label col-md-3 memorycachelable form-group-label"><?php echo $LANG['UI_VOL_CDP_MEMORY_CACHE']; ?></label>
																<div class="col-md-4 form-group-content">
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
																<div class="col-md-4">
																	<select class="form-control select2me" id="memory_cache_size">
																		<option value=128>128 MB</option>
																		<option value=512 selected>512 MB</option>
																		<option value=1024>1 GB</option>
																		<option value=2048>2 GB</option>
																	</select>
																</div>
															</div>
															<!-- 数据IO复制模式 -->
															<div class="form-group ioreplicationdiv">
																<label class="control-label col-md-3 ioreplicationlable"><?php echo $LANG['UI_VOL_CDP_IO_REPLICATION_MODE']; ?></label>
																<div class="col-md-5 col-md-7_en">
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
															<!--故障自动恢复配置-->
															<div class="form-group automaticfaultrecoverydiv">
																<label class="control-label col-md-3 automaticfaultrecoverylable form-group-label"><?php echo $LANG['UI_VOL_CDP_AUTO_FAULT_RECOVERY']; ?></label>
																<div class="col-md-4 form-group-content">
																	<input type="checkbox" id="automatic_fault_recovery_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" checked="checked" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
																	<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_AUTO_FAULT_RECOVERY_TIPS']; ?>">
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

								<div class="tab-pane" id="tab4">
									<div class="tab-pane__body">
										<div class="tab-pane__body__form user-width60_en">
											<div class="alert alert-danger display-none jobnametip"></div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']; ?>:</label>
												<div class="col-md-9">
													<div class="input-icon right">
														<i class="fa"></i>
														<input type="text" maxlength="64" value=<?php echo $LANG['WEB_PT_OP_BACKUP_CREATE']; ?> class="form-control" id="volcdpname" onkeyup="customInputValidate('string', this.value, $(this))" />
														<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']; ?></span>
													</div>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_PRODUCTION_CLIENT']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static volcdphostshow">
													</p>
												</div>
											</div>
											<h4 class="form-section"></h4>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_NODE']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static colorgreen nodeinfoshow">
													</p>
												</div>
											</div>
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE'] ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static colorgreen storeinfoshow">
													</p>
												</div>
											</div>

											<!-- 双机镜像config summary -->
											<div id="doubleHostMirrorConfSummary">
												<div class="form-group mb0 mt10 display-none">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_DUAL_MACHINE']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static colorgreen doublehostmirrorshow">
														</p>
													</div>
												</div>


												<!-- 备份模式 -->
												<div class="form-group mb0 mt10" id="backupModeDiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_MODE']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static colorgreen backupmodeshow">
														</p>
													</div>
												</div>

												<!--实时复制下的备机类型配置-->
												<div class="form-group mb0 mt10 display-none copy-takeoverstandbyhosttype">
													<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_TAKEOVER_TYPE']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  takeoverstandbytypesummary">
														</p>
													</div>
												</div>
												
												<!-- 镜像数据备份到备机 -->
												<div class="form-group mb0 mt10 display-none" id="dataBackupDiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static colorgreen databackupshow">
														</p>
													</div>
												</div>						

												<div class="form-group mb0 mt10 display-none" id="stdmapvolumediv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_MAP_RELATION']; ?>:</label>
													<div class="col-md-9">
														<table border="0" class="borderTableColorGreen task-conf-summary-tab" style="border-color:green;color: green;width:98%; margin: 5px !important;" id="fillStdMapVolumeTable">
															<thead>
																<tr role="row" class="heading">
																	<th width="50%">
																		<?php echo $LANG['WEB_VM_TREE_OVIRT_FAKE_HOST_FOLDER']; ?>
																	</th>
																	<th width="50%">
																		<?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE']; ?>
																	</th>
																</tr>
															</thead>
														</table>

													</div>
												</div>
												<!-- 镜像数据备机，是否重建分区 -->
												<div class="form-group mb0 mt10 display-none diskgenview">
													<label class="control-label col-md-3"><?php echo $LANG['WEB_OS_RESTORE_VOLUME']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  diskgenviewshow">
														</p>
													</div>
												</div>
											</div>
											<!-- 自动接管conf -->
											<div id="autoTakeoverConfSummary">
												<div class="form-group mb0 mt10">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  autotakeoverswitchshow">
														</p>
													</div>
												</div>
												<!--备机类型-->
												<div class="form-group mb0 mt10 display-none takeoverstandbyhosttype">
													<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_TAKEOVER_TYPE']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  takeoverstandbytypesummary">
														</p>
													</div>
												</div>

												<!--接管备机接管-->
												<div class="form-group mb0 mt10 display-none autotakeoverstandbydiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER_STANDBY_MACHINE']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static   takeoverstandbyshow">
														</p>
													</div>
												</div>

												<div class="form-group mb0 takeoveripservicemapdiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_BACKUP_TAKEOVER_NET_CONF'] ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static colorgreen takeoveripservicemapshow">
														</p>
													</div>
												</div>


												<div class="form-group mb0 mt10 display-none takeoverrestoreipdiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_RECOVER_IP']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static   takeoverrestoreipshow">
														</p>
													</div>
												</div>
												<div class="form-group mb0 mt10 display-none heartbeatfailuretimeview">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static   heartbeatfailuretimeshow">
														</p>
													</div>
												</div>

												<div class="form-group mb0 mt10 display-none takeovermountpointview">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER_VOL']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  takeovermountpointinfo">
														</p>
													</div>
												</div>

												<!-- 应用接管 -->
												<div class="form-group mb0 mt10 display-none apptakeoverswitchview">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_ENABLE_APP_TAKEOVER']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static   apptakeoverswitchshow">
														</p>
													</div>
												</div>

												<!-- 应用故障监测 -->
												<div class="form-group mb0 mt10 display-none apptakeovermonitswitchview">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_ENABLE_FAULT_MONITOR_TAKEOVER']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static   apptakeovermonitorswitchshow">
														</p>
													</div>
												</div>

												<!-- 应用故障接管 -->
												<div class="form-group mb0 mt10 display-none appfailurenumberconfview">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_FAULT_TIMES']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static   appfailurenumbershow">
														</p>
													</div>
												</div>

												<div class="form-group mb0 mt10 display-none takeoverappintervalconfview">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_FAULT_INTERVAL_TIMES']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static   takeoverappintervalshow">
														</p>
													</div>
												</div>

												<div class="form-group mb0 mt10 display-none takeoverappdiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_MONITOR_DEVICE']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static   takeoverappshow">
														</p>
													</div>
												</div>
												<!-- 接管自定义脚本 -->
												<div class="form-group mb0 mt10">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_CUSTOM_SCRIPT']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static  takeoverScriptswitchshow">
														</p>
													</div>
												</div>
												<div class="form-group mb0 mt10 display-none takeoverscriptconfdiv">
													<label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_SCRIPT_INFO']; ?>:</label>
													<div class="col-md-9">
														<p class="form-control-static colorgreen  takeoverScriptConfInfo">
														</p>
													</div>
												</div>
											</div>

											<h4 class="form-section"></h4>
											<!-- 定时标签策略 -->
											<div class="form-group mb0 mt10 display-none">
												<label class="control-label col-md-3"><?php echo $LANG['UI_VM_TIMING_START']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  timetostartshow">
													</p>
												</div>
											</div>
											<!-- 定时标签策略 -->
											<div class="form-group mb0 mt10">
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
											<div class="form-group mb0 mt10">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  storageinfoshow">
													</p>
												</div>
											</div>

											<!-- 保留策略 -->
											<div class="form-group mb0 mt10">
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

											<!-- 高级策略 -->
											<div class="form-group mb0 mt10" id="advancedStrategy">
												<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG']; ?>:</label>
												<div class="col-md-9">
													<p class="form-control-static  advancedStrategyshow">
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

		<!-- BEGIN RATE LIMIT MODAL -->
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

		<!-- 接管網絡配置--modal START -->
		<div id="takeoverNetworkConfModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
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
								<div class="panel-group accordion mt10 min-height150" style="overflow-y:auto" id="host_ip_server_conf">

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
								<div class="panel-group accordion mt10 min-height150" style="overflow-y:auto" id="standby_gateway_conf">

								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
				<button type="button" class="btn btn-primary" id="submit_host_network_conf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
			</div>
		</div>
		<!-- 接管網絡配置  --modal end>
		
		
		<！-- 添加自动接管自定义脚本 modal START  -->
		<div id="takeoverScriptModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_VOL_CDP_SCRIPT']; ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					<div class="form-group margintop10">
						<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_SCRIPT_TYPE']; ?></label>
						<div class="col-md-9">
							<select class="form-control select2me" id="takeoverScriptType">
								<option value="0"><?php echo $LANG['UI_VOL_CDP_SCRIPT_TYPE_SELECT']; ?></option>
								<option value="2"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_MONITOR_SCRIPT']; ?></option>
								<option value="1"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_EXECUTION_SCRIPT']; ?></option>
								
							</select>
						</div>
					</div>
					<div class="form-group display-none execsequenceview">
						<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_EXECUTION_SEQUENCE']; ?></label>
						<div class="col-md-9">
							<label class="control-label ">
								<input type="radio" name="scriptExecuteOrder" id="scriptExecuteFormer" value="1" />
								<span><?php echo $LANG['UI_VOL_CDP_EXECUTION_BEFORE_TAKEOVER']; ?></span>
							</label>
							<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ADD_SCRIPT_BEFORE_TAKEOVER']; ?>">
								<i class="viconfont vicon-tishi"></i>
							</a>
							<label class="control-label ml50">
								<input class="ml15" type="radio" name="scriptExecuteOrder" id="scriptExecuteAfter" value="2" />
								<span><?php echo $LANG['UI_VOL_CDP_EXECUTION_AFTER_TAKEOVER']; ?> </span>
							</label>
							<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ADD_SCRIPT_AFTER_TAKEOVER']; ?>">
								<i class="viconfont vicon-tishi"></i>
							</a>
						</div>
					</div>
					<!-- 执行间隔 -->
					<div class="form-group display-none execintervalview">
						<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_EXECUTION_INTERVAL']; ?></label>
						<div class="col-md-4">
							<div class="exec_script_interval_div">
								<div class="input-group spinner-group">
									<input type="number" id="takeover_script_interval" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm">
									<div class="spinner-buttons input-group-btn spinner-group-btn">
										<button type="button" class="btn  spinner-up default input-sm">
											<i class="fa fa-angle-up"></i>
										</button>
										<button type="button" class="btn  spinner-down default input-sm">
											<i class="fa fa-angle-down"></i>
										</button>
									</div>
								</div>
								<span class="control-label threadnumlabel"><?php echo $LANG['WEB_UTILS_SECOND']; ?> </span>
							</div>
						</div>
						<div class="popovers mt8">
							<a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_MONITOR_SCRIPT_STATE']; ?>">
								<i class="viconfont vicon-tishi"></i>
							</a>
						</div>
					</div>
					<!-- 累计错误次数 -->
					<div class="form-group display-none execfailureview">
						<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_TOTAL_FAILURE_TIMES']; ?></label>
						<div class="col-md-4">
							<div class="script_failure_number_div">
								<div class="input-group spinner-group">
									<input type="number" id="script_failure_number" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm">
									<div class="spinner-buttons input-group-btn spinner-group-btn">
										<button type="button" class="btn  spinner-up default input-sm">
											<i class="fa fa-angle-up"></i>
										</button>
										<button type="button" class="btn  spinner-down default input-sm">
											<i class="fa fa-angle-down"></i>
										</button>
									</div>
								</div>
								<span class="control-label threadnumlabel"><?php echo $LANG['WEB_PALTFORM_DC_TIME']; ?> </span>
							</div>
						</div>
						<div class="popovers mt8">
							<a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TOTAL_FAILURE_TIMES_ALLOWED']; ?>">
								<i class="viconfont vicon-tishi"></i>
							</a>
						</div>
					</div>

					<!-- 自定义脚本路径 -->
					<div class="form-group">
						<label class="control-label col-md-2 "><?php echo $LANG['UI_VOL_CDP_SCRIPT_PATH']; ?></label>
						<div class="col-md-9">
							<input type="text" maxlength="64" class="form-control" id="takeoverScriptPath" placeholder="<?php echo $LANG['UI_VOL_CDP_INPUT_MONITOR_PATH']; ?>" />
						</div>
					</div>

					<!-- 自定义脚本配置相关提示 -->
					<div class="form-group">
						<label class="col-md-2"></label>
						<div class="col-md-9">
							<div class="alert alert-block alert-info fade in" id="mountpointTips">
								<button type="button" class="close" data-dismiss="alert"></button>
								<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
								<ol class="alert-ol">
									<li><?php echo $LANG['UI_VOL_CDP_CUSTOM_SCRIPT_CONFIGURE_TIPSTWO']; ?>
									</li>
									<li><?php echo $LANG['UI_VOL_CDP_CUSTOM_SCRIPT_CONFIGURE_TIPSONE']; ?>
									</li>
									<li><?php echo $LANG['UI_VOL_CDP_CUSTOM_SCRIPT_CONFIGURE_TIPSTHREE']; ?>
									</li>
								</ol>
							</div>

						</div>
					</div>

				</div>
			</div>
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
				<button type="button" class="btn btn-primary" id="submitTakeoverScript"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
			</div>
		</div>
		<!-- 添加自动接管自定义脚本--modal END -->

		<!-- BEGIN ADD DRAWER -->
		<?php include_once './vol_cdp_mode_desc.php'; ?>

		<!-- END RATE LIMIT MODAL -->
		<?php include_once './standby_map_conf.php'; ?>
		<?php include_once './tag_point_strategy.php'; ?>
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


<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/libs/md5.js"></script>


<script src="./scripts/volcdp/vol_cdp_backup.js"></script>
<script src="./scripts/volcdp/tagpoint_strategy.js"></script>
<script src="./scripts/volcdp/takeover_ip_server_map.js"></script>