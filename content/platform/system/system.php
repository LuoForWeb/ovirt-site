<?php include_once '../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css"/>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>




<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="systemMonitorPage">
<div class="row">
	<div class="col-md-12">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki" id='authdiv'>
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
					<li class="active">
						<a id="system_monitor_tab" href="#systemMonitordiv" data-toggle="tab">
						<i class="viconfont  vicon-system"></i> <?php echo $LANG['UI_SYSTEM_MONITOR_SYSTEM_MONITOR'] ?> </a>
					</li>
					<li>
						<a id="base_info_tab" href="#baseInfodiv" data-toggle="tab">
						<i class="viconfont  vicon-pt_system_information"></i> <?php echo $LANG['UI_SYSTEM_MONITOR_SYSTEM_MSG'] ?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content row margin10">
					<div class="tab-pane active" id="systemMonitordiv">
						<div class="col-md-12">
							<div class="row">

								<div class="col-md-3 system-nodewidth_en" >
									<label class="system-node-text_en system-node-text_cn" style="float: left;display: inline-block;margin: 8px 0;padding: 0px;"> <?php echo $LANG['WEB_PLATFORM_DES_NODE'] ?>:</label>
									<div class="col-md-10" style="padding: 0;">
										<select class="form-control select2me" name="standbyhost" id="node_uuid">
										</select>
									</div>
								</div>
								<div class="col-md-3 system-timewidth_en" >
									<label class="system-text_en system-text_cn" style="float: left;display: inline-block;margin: 8px 0;padding: 0px;"> <?php echo $LANG['UI_VOL_CDP_TIME'] ?>:</label>
									<div class="col-md-8" style="padding: 0;">
										<select class="form-control select2me" name="standbyhost" id="time_range">
											<option value=""><?php echo $LANG['UI_SYSTEM_MONITOR_REAL_MONITOR'] ?></option>
											<option value="1"><?php echo $LANG['UI_SYSTEM_MONITOR_LAST_1_HOUR'] ?></option>
											<option value="2"><?php echo $LANG['UI_SYSTEM_MONITOR_LAST_3_HOUR'] ?></option>
											<option value="3"><?php echo $LANG['UI_SYSTEM_MONITOR_LAST_6_HOUR'] ?></option>
											<option value="4"><?php echo $LANG['UI_SYSTEM_MONITOR_LAST_1_DAY'] ?></option>
											<option value="5"><?php echo $LANG['UI_SYSTEM_MONITOR_LAST_7_DAY'] ?></option>
											<option value="6"><?php echo $LANG['UI_SYSTEM_MONITOR_LAST_1_MONTH'] ?></option>
											<option value="self_time"><?php echo $LANG['UI_SYSTEM_MONITOR_CUSTOM'] ?></option>
										</select>
									</div>
								</div>
								<div class="col-md-4  system-pickerwidth_en"  style="padding: 0;">
									<div class="daterangepickerdiv display-none col-md-11  system-picker_en">
										<input type="text" id="daterangepicker" class="form-control" autocomplete="off">
										<i class="viconfont vicon-ge_calendar"></i>
									</div>
								</div>
								<div class="col-md-2" style="padding-right: 0px">
									<div class="page-right" style="display: flex;align-items: center;justify-content: flex-end;">
										<?php
										//检查屏蔽只读观察者的操作按钮
											if (in_array("p_system_log_download", $_SESSION['permissionArr'])) {
												// 下载日志
												echo '<button type="button" id="downloadLogBtn" class="btn green-haze mr10">
														<i class="viconfont vicon-download"></i>' . $LANG['UI_LOG_SYSTEM_LOG_DOWNLOAD_NOW'] . '
													</button>';
											}
										?>
										<button type="button" id="setAlarm" class="btn green-haze" style="<?php if (!in_array("p_system_rule", $_SESSION['permissionArr'])) {echo 'display: none';} ?>">
										<i class="viconfont vicon-pt_system_alarm_rule"></i> <?php echo $LANG['UI_SYSTEM_MONITOR_SET_ALARM'] ?></button>
									</div>
								</div>
							</div>
						
						</div>

						<div class="chartAllDiv" style="padding:5px;">
							<div class="row">
								<div class="col-md-4 card-echart" style="padding:10px;"><div id="chart_cpu" style="height:350px;border: solid 1px #d1d1d1;"></div></div>
								<div class="col-md-4 card-echart" style="padding:10px;"><div id="chart_ram" style="height:350px;border: solid 1px #d1d1d1;"></div></div>
								<div class="col-md-4 card-echart" style="padding:10px;"><div id="chart_load" style="height:350px;border: solid 1px #d1d1d1;"></div></div>
							</div>
							<div class="row">
								<div class="col-md-4 card-echart" style="padding:10px;"><div id="chart_network" style="height:350px;border: solid 1px #d1d1d1;"></div></div>
								<div class="col-md-4 card-echart" style="padding:10px;"><div id="chart_bps" style="height:350px;border: solid 1px #d1d1d1;"></div></div>
								<div class="col-md-4 card-echart" style="padding:10px;"><div id="chart_iops" style="height:350px;border: solid 1px #d1d1d1;"></div></div>
							</div>
						</div>
					</div>
					
					<div class="tab-pane" id="baseInfodiv">
					<div class="col-md-12 blockUI">
						<div class="row" style="margin-bottom: 15px;">
							<div class="col-md-3 system-nodewidth_en">
								<label class="system-node-text_en system-node-text_cn" style="float: left;display: inline-block;margin: 8px 0;padding: 0px;"> <?php echo $LANG['WEB_PLATFORM_DES_NODE'] ?>:</label>
								<div class="col-md-10" style="padding: 0;">
									<select class="form-control select2me" name="standbyhost" id="msg_node_uuid">
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<!-- 基本信息 -->
							<div class="col-md-8 msg_border" style="margin-top: 0;">
							<h3 class="msg_title"><?php echo $LANG['UI_USER_BASE_INFO'] ?></h3>
								<div class="col-md-6" style="padding: 0;">
									<ul class="msg_ul">
										<li class="msg_li">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_NODE_NAME'] ?>:</p></div>
											<div class="col-md-8"><p id="nodename">--</p></div>
										</li>
										<li class="msg_li" style="display: none;">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_KERNEL_NAME'] ?>:</p></div>
											<div class="col-md-8" id="kernel_name"><p>--</p></div>
										</li>
										<li class="msg_li">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_KERNEL_RELEASE'] ?>:</p></div>
											<div class="col-md-8"><p id="kernel_release">--</p></div>
										</li>
										<li class="msg_li" style="display: none;">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_KERNEL_VERSION'] ?>:</p></div>
											<div class="col-md-8"><p id="kernel_version">--</p></div>
										</li>
										<li id="AIO_div">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_AIO'] ?>:</p></div>
											<div class="col-md-8"><p id="AIO_version">--</p></div>
										</li>
									</ul>
								</div>
								<div class="col-md-6">
									<ul class="msg_ul">
										<li class="msg_li"  style="display: none;">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_MACHINE'] ?>:</p></div>
											<div class="col-md-8"><p id="machine">--</p></div>
										</li>
										<li class="msg_li">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_PROCESSOR_TYPE'] ?>:</p></div>
											<div class="col-md-8"><p id="processor">--</p></div>
										</li>
										<li class="msg_li" style="display: none;">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_MACHINE_PLATFORM'] ?>:</p></div>
											<div class="col-md-8"><p id="hardware_platform">--</p></div>
										</li>
										<li class="msg_li">
											<div class="col-md-4"><p><?php echo $LANG['WEB_OS_HOST'] ?>:</p></div>
											<div class="col-md-8"><p id="operating_system">--</p></div>
										</li>
										<li>
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_IQN'] ?>:</p></div>
											<div class="col-md-8"><p id="storage_adapter_IQN">--</p></div>
										</li>
									</ul>
								</div>
							</div>
							<!-- CPU和内存 -->
							<div class="col-md-4" style="padding-right: 0;">
							<div class="msg_border" style="margin-top: 0;">
								<h3 class="msg_title"><?php echo $LANG['UI_SYSTEM_MONITOR_CPU_ARM'] ?></h3>
								<div class="row" style="margin: 0;">
									<ul class="msg_ul">
										<li class="msg_li">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_CPU_NAME'] ?>:</p></div>
											<div class="col-md-8"><p id="cpu_name">--</p></div>
										</li>
										<li class="msg_li">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_CPU_NUM'] ?>:</p></div>
											<div class="col-md-8"><p id="cpu_count">--</p></div>
										</li>
										<li class="msg_li">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_CPU_EACH_NUM'] ?>:</p></div>
											<div class="col-md-8"><p id="cpu_cores">--</p></div>
										</li>
										<li class="msg_li">
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_CPU_ALL_NUM'] ?>:</p></div>
											<div class="col-md-8"><p id="cpu_processor">--</p></div>
										</li>
										<li>
											<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_ARM_TOTAL'] ?>:</p></div>
											<div class="col-md-8"><p id="menTotal">--</p></div>
										</li>
									</ul>
								</div>
							</div>
							</div>
						</div>
						
						<!-- 磁盘和根分区 -->
						<div class="row msg_border">
							<h3 class="msg_title"><?php echo $LANG['UI_SYSTEM_MONITOR_DISK_ROOT'] ?> </h3>
							<!-- 磁盘 -->
<!--     						<div class="col-md-8 "> -->
								<div class="row">
									<div class="col-md-12" style="position: relative;">
										<div class="col-md-8">
											<table border="1" class="msg_table" rules="rows" id="disk_table">
											  <tr class="msg_table_title">
												<th class="col-md-2"><?php echo $LANG['UI_SYSTEM_MONITOR_DEVICE_TYPE'] ?></th>
												<th class="col-md-2"><?php echo $LANG['UI_SYSTEM_MONITOR_DEVICE_FIRM'] ?></th>
												<th class="col-md-2"><?php echo $LANG['UI_SYSTEM_MONITOR_DEVICE_MODE'] ?></th>
												<th class="col-md-2"><?php echo $LANG['UI_SYSTEM_MONITOR_DEVICE_VERSION'] ?></th>
												<th class="col-md-2"><?php echo $LANG['UI_SYSTEM_MONITOR_DEVICE_NODE_NAMR'] ?></th>
												<th class="col-md-2"><?php echo $LANG['UI_SYSTEM_MONITOR_MBIT'] ?></th>
											  </tr>
											</table>
										</div>
										<div class="col-md-4" style="position: absolute;top: 50%;transform: translateY(-50%);right: 0;">
											<ul class="msg_ul">
												<li class="msg_li">
													<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_ROOT_TOTAL'] ?>:</p></div>
													<div class="col-md-8"><p id="root_total">--</p></div>
												</li>
												<li class="msg_li">
													<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_ROOT_USED'] ?>:</p></div>
													<div class="col-md-8"><p id="root_used">--</p></div>
												</li>
												<li>
													<div class="col-md-4"><p><?php echo $LANG['UI_SYSTEM_MONITOR_PERCENTAGE'] ?>:</p></div>
													<div class="col-md-8"><p id="root_percentage">--</p></div>
												</li>
											</ul>
										</div>
									</div>
								</div>
<!--     						</div> -->
							<!-- 根分区 -->
							<div class="col-md-4">
								<div class="row">
									
								</div>
							</div>
						</div>
						<!-- 网卡和HBA卡 -->
						<div class="row msg_border">
							<h3 class="msg_title" id="network_HBA_title"><?php echo $LANG['UI_SYSTEM_MONITOR_NET_HBA'] ?> </h3>
							<!-- 网卡 -->
							<div class="row" style="margin: 0;">
								<div class="col-md-12">
								<table border="1" class="msg_table" rules="rows" id="network_table">
								  <tr class="msg_table_title">
									<th class="col-md-7"><?php echo $LANG['UI_SYSTEM_MONITOR_NET_MSG'] ?></th>
									<th class="col-md-1"><?php echo $LANG['UI_SYSTEM_MONITOR_NET_NAME'] ?></th>
									<th class="col-md-2"><?php echo $LANG['UI_SYSTEM_MONITOR_MAC'] ?></th>
									<th class="col-md-1"><?php echo $LANG['UI_SYSTEM_MONITOR_NET_SPEED'] ?></th>
									<th class="col-md-1"><?php echo $LANG['UI_SYSTEM_MONITOR_NET_STATUS'] ?></th>
								  </tr>
								</table>
								</div>
							</div>
							<!-- HBA卡 -->
							<div class="row" style="margin: 0;" id="HBA_div">
								<div class="col-md-12">
								<table border="1" class="msg_table" rules="rows" id="HBA_table">
								  <tr class="msg_table_title">
									<th class="col-md-6"><?php echo $LANG['UI_SYSTEM_MONITOR_HBA_MSG'] ?></th>
									<th class="col-md-1"><?php echo $LANG['UI_SYSTEM_MONITOR_HBA_NAME'] ?></th>
									<th class="col-md-1"><?php echo $LANG['UI_SYSTEM_MONITOR_HBA_MODE'] ?></th>
									<th class="col-md-2"><?php echo $LANG['UI_SYSTEM_MONITOR_HBA_WWN'] ?></th>
									<th class="col-md-1"><?php echo $LANG['UI_SYSTEM_MONITOR_HBA_SPEED'] ?></th>
									<th class="col-md-1"><?php echo $LANG['UI_SYSTEM_MONITOR_HBA_STATUS'] ?></th>
								  </tr>
								</table>
								</div>
							</div>
						</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- END TAB PORTLET-->
	</div>
</div>
<!-- END PAGE CONTENT-->
<!-- BEGIN MODAL -->
	<div id="setAlarmModal" class="modal xmodal fade " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="closeSetAlarm"></button>
			<h4 class="modal-title"><?php echo $LANG['UI_SYSTEM_MONITOR_SET_ALARM'] ?></h4>
		</div>
		<div class="modal-body">
				<div class="list-option">
						<div class="row">
							<label class="col-md-5" style="margin-top:7px;text-align: right;"><?php echo $LANG['UI_SYSTEM_MONITOR_ON_ALARM'] ?> ：
							</label>
							<div class="col-md-5">
								<input type="checkbox" id="alarm_flag"   class="make-switch" data-on-color="primary" data-off-color="info"
								data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" 
								data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
							</div>
							<div class="col-md-2 mt5">
								<a class="popovers " data-container="body" data-trigger="hover" 
								data-placement="right" data-content="<?php echo $LANG['UI_SYSTEM_MONITOR_ON_ALARM_DES'] ?>">
								<i class="fa fa-info-circle fa-lg"></i>
								</a>
							</div>
						</div>
					</div>
				<div id="noClickDiv">
					<div class="list-option">
						<div class="row">
							<label class="col-md-5" style="margin-top:7px;text-align: right;"><?php echo $LANG['UI_SYSTEM_MONITOR_CPU_THAN'] ?> ：
							</label>
							<div class="col-md-3">
								<input placeholder="<?php echo $LANG['UI_SYSTEM_MONITOR_VALUE'] ?>" id="cpu_alarm" type="text" maxlength="2" class="table-group-action-input form-control input" aria-controls="example" oninput="value=value.replace(/^(0+)|[^\d]+/g,'')">
								<span style="position: absolute; top: 0; right: 0; display: table-cell; white-space: nowrap; padding: 9px 0;">%</span>
							</div>
							<div class="col-md-2"></div>
						</div>
					</div>
					<div class="list-option">
						<div class="row">
							<label class="col-md-5" style="margin-top:7px;text-align: right;"><?php echo $LANG['UI_SYSTEM_MONITOR_RAM_THAN'] ?> ：
							</label>
							<div class="col-md-3">
								<input placeholder="<?php echo $LANG['UI_SYSTEM_MONITOR_VALUE'] ?>" id="ram_alarm" type="text" maxlength="2" class="table-group-action-input form-control input" aria-controls="example" oninput="value=value.replace(/^(0+)|[^\d]+/g,'')">
								<span style="position: absolute; top: 0; right: 0; display: table-cell; white-space: nowrap; padding: 9px 0;">%</span>
							</div>
							<div class="col-md-2"></div>
						</div>
					</div>
					<div class="list-option">
						<div class="row">
							<label class="col-md-5" style="margin-top:7px;text-align: right;"><?php echo $LANG['UI_SYSTEM_MONITOR_ROOT_THAN'] ?> ：
							</label>
							<div class="col-md-3">
								<input placeholder="<?php echo $LANG['UI_SYSTEM_MONITOR_VALUE'] ?>" id="root_alarm" type="text" maxlength="2" class="table-group-action-input form-control input" aria-controls="example" oninput="value=value.replace(/^(0+)|[^\d]+/g,'')">
								<span style="position: absolute; top: 0; right: 0; display: table-cell; white-space: nowrap; padding: 9px 0;">%</span>
							</div>
							<div class="col-md-2"></div>
						</div>
					</div>
					<div class="list-option">
						<div class="row">
							<label class="col-md-5" style="margin-top:7px;text-align: right;"><?php echo $LANG['UI_SYSTEM_MONITOR_SDLC'] ?> ：
							</label>
							<div class="col-md-5">
								<select class="form-control select2me" id=item_period>
									<option value="5"><?php echo $LANG['UI_SYSTEM_MONITOR_5_MIN'] ?></option>
									<option value="15"><?php echo $LANG['UI_SYSTEM_MONITOR_15_MIN'] ?></option>
									<option value="30"><?php echo $LANG['UI_SYSTEM_MONITOR_30_MIN'] ?></option>
									<option value="60"><?php echo $LANG['UI_SYSTEM_MONITOR_1_HOUR'] ?></option>
								</select>
							</div>
							<div class="col-md-2 mt5">
								<a class="popovers " data-container="body" data-trigger="hover" 
								data-placement="right" data-content="<?php echo $LANG['UI_SYSTEM_MONITOR_SDLC_DES'] ?>">
								<i class="fa fa-info-circle fa-lg"></i>
								</a>
							</div>
						</div>
					</div>
					<div class="list-option">
						<div class="row">
							<label class="col-md-5" style="margin-top:7px;text-align: right;"><?php echo $LANG['UI_SYSTEM_MONITOR_SILENCE_TIME'] ?> ：
							</label>
							<div class="col-md-5">
								<select class="form-control select2me" id="silence_time">
									<option value="3"><?php echo $LANG['UI_SYSTEM_MONITOR_3_HOUR'] ?></option>
									<option value="6"><?php echo $LANG['UI_SYSTEM_MONITOR_6_HOUR'] ?></option>
									<option value="12"><?php echo $LANG['UI_SYSTEM_MONITOR_12_HOUR'] ?></option>
									<option value="24"><?php echo $LANG['UI_SYSTEM_MONITOR_24_HOUR'] ?></option>
								</select>
							</div>
							<div class="col-md-2 mt5">
								<a class="popovers " data-container="body" data-trigger="hover" 
								data-placement="right" data-content="<?php echo $LANG['UI_SYSTEM_MONITOR_SILENCE_TIME_DES'] ?>">
								<i class="fa fa-info-circle fa-lg"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
		</div>
		
		<!-- modal-footer -->
		<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default" id="cancel_alarm"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
		<button type="button" class="btn btn-primary" id="alarm_submit"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
		</div>
	</div>	
	<!-- END MODAL -->


<!-- BEGIN MODAL -->
<div id="downloadLogModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="viconfont vicon-ge_download"></i> <?php echo $LANG['UI_LOG_SYSTEM_LOG_DOWNLOAD'] ?></h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="table-toolbar">
				<div class="row">
					<div class="col-md-8">
						<label style="margin-right: 12px;"><?php echo $LANG['UI_PALTFORM_NODE'] ?></label>
						<select id="nodeSelect" class="table-group-action-input form-control input-inline input-large ">
							<option><?php echo $LANG['BILLING_PLEASE_SELECT'] ?></option>
						</select>
					</div>
					<div class="col-md-4 page-right">
						<div class="btn-group">
							<button type="button" id="downloadPackage" class="btn btn-sm green-haze">
								<i class="viconfont vicon-ge_download"></i> <?php echo $LANG['UI_LOG_SYSTEM_LOG_DOWNLOAD_NOW'] ?>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="table-container">
				<table id="downloadLogTable">
					<!-- <thead>
					<tr role="row" class="heading">
						<th width="2%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="30%">
							<?php echo $LANG['UI_BACKUP_FILE_FILENAME'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_BACKUP_FILE_FILESIZE'] ?>
						</th>
						<th width="20%">
							<?php echo $LANG['UI_LOG_SYSTEM_LOG_LAST_MODIFY_TIME'] ?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody> -->
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END MODAL -->	
	
	
	
	<!-- BEGIN MODAL -->
	<div id="detailsEchart" class="modal xmodal fade " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			<h4 class="modal-title">--</h4>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="chart_details_class" id="chart_cpu_details"></div>
				<div class="chart_details_class" id="chart_ram_details"></div>
				<div class="chart_details_class" id="chart_load_details"></div>
				<div class="chart_details_class" id="chart_network_details"></div>
				<div class="chart_details_class" id="chart_bps_details"></div>
				<div class="chart_details_class" id="chart_iops_details"></div>
			</div>
		</div>
		
		<!-- modal-footer -->
		<div class="modal-footer">
			<button type="button" data-dismiss="modal" class="btn btn-default" id="cancel_alarm"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
		</div>
	</div>	
	<!-- END MODAL -->
	
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/echarts/V5.01/echarts.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script src="./scripts/platform/system/system.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->	