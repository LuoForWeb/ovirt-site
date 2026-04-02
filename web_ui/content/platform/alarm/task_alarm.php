<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" type="text/css" />
<link rel="stylesheet" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/cascader/css/cascader.css" />
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="table-toolbar-wrapper vin_toolbar" id="vin_task_alarm_toolbar">
			<div class="table-toolbar-wrapper__left" style="display: flex;">
				<?php
				//检查屏蔽只读观察者的操作按钮
				// 根据授权显示
				if (in_array("p_task_alarm_delete", $_SESSION['permissionArr'])) {
					// 删除
					echo '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteTaskAlarm"></button></div>';
				}
				?>
				<div class="search input-group mr12">
					<input type="search" maxlength="128" class="searchInput job-alarm-search customSearch" autocomplete="off"
						style="padding-right:32px" maxlength="64" type="text"
						placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
					<div class="position0" style="width:auto;height:34px">
						<button class="b-btn task_alarm_tableclear clear hide position0" id="task_searchBtn"><i
								class="icon-close-small"></i></button>
					</div>
					<div class="search-btn positionL0" style="width:auto;height:34px;">
						<button class="b-btn search-btn"><i class="icon-search"></i></button>
					</div>
				</div>
				<?php
				//检查屏蔽只读观察者的操作按钮
				if (in_array("p_task_alarm_response", $_SESSION['permissionArr'])) {
					// 响应
					echo '<button type="button" id="solveTaskAlarm" class="btn table-toolbar-btn">
										<i class="viconfont vicon-biaojiweixiangying"></i> ' . $LANG['UI_ALARM_RESPONSE_DO'] . '
									</button>';
				}
				?>
			</div>
			<div class="table-toolbar-wrapper__right">
				<div class="table-actions-wrapper page-right" style="display: flex;">
					<select id="taskAlarmLevelSelect"
						class="table-group-action-input form-control input-inline input-small mr12">
						<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
						<option value="3"><?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?></option>
						<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></option>
					</select>
					<button type="button" id="job_alarm_advanced_search_btn" class="btn btn-primary adv_btn brr2 p-lr8">
						<i class="adv-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
					</button>
					<div class="vin_btnToolbar">
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div id="task_searchDiv" class="searchDiv display-none">
					<?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span class="searchContent"></span><span
						class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
				</div>
			</div>
		</div>

		<div class="table-container" id="task_alarm_div">
			<table id="task_alarm_table">

			</table>
		</div>
		<!-- End: life time stats -->

		<!-- BEGIN MODAL -->
		<div id="taskAlarmModalDiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
			data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-bell"></i> <?php echo $LANG['UI_ALARM_DETAILS'] ?></h4>
			</div>
			<div class="modal-body row" style="margin: 0; padding: 16px 20px;">
				<div class="tabbable-custom">
					<ul class="nav nav-tabs " id="task_alarm_details_tab">
						<!-- 基本信息 -->
						<li id="baseInfoTabLi" class="active" data-type="primary-info">
							<a href="#baseInfo_tab" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-xiangqing"></i>
								<?php echo $LANG['UI_ALARM_BASE_INFO'] ?></a>
						</li>
						<!-- 对象信息 -->
						<li id="itemInfoTabLi" class="display-none" data-type="item-info">
							<a href="#itemInfo_tab" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-ge_vm "></i> <?php echo $LANG['UI_JOB_VM_INFO'] ?> </a>
						</li>
						<!-- 日志信息 -->
						<li class="display-none" id="logTabLi" data-type="log-info">
							<a href="#logInfo_tab" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-ge_running_log "></i> <?php echo $LANG['UI_ALARM_LOG_INFO'] ?>
							</a>
						</li>
					</ul>
					<div class="tab-content alarmDetails" style="overflow: auto;height: 420px;padding: 16px">
						<!-- 基本信息 -->
						<div class="tab-pane active" id="baseInfo_tab">
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_ALARM_ID'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_id">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_JOB_RNAME'] ?>:
								</div>
								<div class="col-md-8 value" id="task_name">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_PUBLIC_MODULE_TYPE'] ?>:
								</div>
								<div class="col-md-8 value" id="module_type">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_PUBLIC_TASK_TYPE'] ?>:
								</div>
								<div class="col-md-8 value" id="task_type">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['WEB_PLATFORM_DES_NODE'] ?>:
								</div>
								<div class="col-md-8 value" id="node">
								</div>
							</div>
							<div class="row static-info storageDiv">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_PALTFORM_STORAGE'] ?>:
								</div>
								<div class="col-md-8 value" id="storage">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_ALARM_LEVLE'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_level">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_ALARM_TIME'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_time">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_ALARM_CONTENT'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_content">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_ALARM_RESPONSE_FLAG'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_solved_des">
								</div>
							</div>
							<div class="row static-info solved_div">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_ALARM_RESPONSE_USER'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_solved_user">
								</div>
							</div>
							<div class="row static-info solved_div">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_ALARM_RESPONSE_TIME'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_solved_time">
								</div>
							</div>
							<div class="row static-info task_alarm_push_div">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_PLATFORM_ALARM_PUSH']; ?>
								</div>
								<div class="col-md-8 value">
									<a id="task_alarmPush_btn" class="c0FBF98"><?php echo $LANG['UI_PLATFORM_PUSH_THIRD']; ?></a>
								</div>
							</div>
							<div class="row static-info task_response_push_div">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_PLATFORM_RESPONSE_PUSH']; ?>
								</div>
								<div class="col-md-8 value">
									<a id="task_responsePush_btn" class="c0FBF98"><?php echo $LANG['UI_PLATFORM_RESPONSE_PUSH_THIRD']; ?></a>
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_email">
								</div>
							</div>
							<?php
							if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
								echo ' <div class="row static-info">
									<div class="col-md-4 name " >' .
									$LANG['UI_SETTINGS_NOTICE_SMS'] . ':
									</div>
									<div class="col-md-8 value" id="alarm_sms">
									</div>
								</div>';
							}
							?>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_weChat">
								</div>
							</div>
							<div class="row static-info">
								<div class="col-md-4 name ">
									<?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'] ?>:
								</div>
								<div class="col-md-8 value" id="alarm_weChat2">
								</div>
							</div>
						</div>
						<!-- 对象信息 -->
						<div class="tab-pane" id="itemInfo_tab" style="height: 420px; background: #fff;">
							<div class="table-container">
								<table id="itemInfo_table"></table>
							</div>
							<div class="itemInfo_text display-none"></div>
						</div>
						<!-- 日志信息 -->
						<div class="tab-pane" id="logInfo_tab">
							<form action="#" id="" class="form-horizontal mt10">
								<div class="form-body">
									<div class="form-group">
										<div class="col-md-12">
											<div class="changelog-list" id="task_log"
												style=" overflow-y:auto; border: 1px solid #ddd; background: #fff; padding: 20px;white-space: pre-wrap;word-break: break-word;">

											</div>
										</div>
									</div>
								</div>
							</form>
							<!-- END FORM-->
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<?php
				//检查屏蔽只读观察者的操作按钮
					if (in_array("p_task_alarm_download", $_SESSION['permissionArr'])) {
						// 下载日志
						echo ' <button type="button" class="btn green-haze" id="taskDownloadBtn">' . $LANG['UI_ALARM_LOG_DOWNLOAD'] . '</button>';
					}
					if (in_array("p_task_alarm_response", $_SESSION['permissionArr'])) {
						// 响应
						echo '<button type="button" class="btn green-haze" id="taskSolvedBtn">' . $LANG['UI_ALARM_RESPONSE_NO_DO'] . '</button>';
					}
				?>
				<button type="button" data-dismiss="modal"
					class="btn btn-primary"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
			</div>
		</div>
		<!-- END MODAL -->

		<!-- BEGIN SEARCH MODAL -->
		<div id="job_alarm_advanced_search_modal" class="modal xmodal fade form-horizontal" tabindex="-1" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="fa fa-search"></i>
					<?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					<!-- 时间范围 -->
					<div class="list-option">
						<div class="row">
							<label class="control-label col-md-4"><?php echo $LANG['UI_JOB_TIME_RANGE'] ?>
							</label>
							<div class="col-md-6 daterangepickerdiv">
								<input type="text" id="advanced_search_time_range" class="form-control" autocomplete="off">
								<i class="viconfont vicon-ge_calendar"></i>
							</div>
						</div>
					</div>
					<div class="list-option">
						<div class="row">
							<!-- 所在节点 -->
							<label class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_IN_NODE1'] ?>
							</label>
							<div class="col-md-6">
								<select class="form-control select2me" id="advanced_search_current_nodes">
								</select>
							</div>
						</div>
					</div>
					<!-- 业务类型/模块类型/任务类型 级联 -->
					<div class="list-option">
						<div class="row">
							<label
								class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TASK_TYPE']; ?>
							</label>
							<div class="col-md-6" id="current_job_advanced_search_cascader">

							</div>
						</div>
					</div>
					<!-- 是否当前任务 -->
					<div class="list-option">
						<div class="row">
							<label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_CURRENT_JOB'] ?>
							</label>
							<div class="col-md-6">
								<!-- 需求#18877，添加当前任务的告警和非当前任务的告警筛选 -->
								<select id="advanced_search_current_job_select" class="table-group-action-input form-control">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
									<option value="1"><?php echo $LANG['UI_PLATFORM_CURRENT_JOB'] ?></option>
									<option value="2"><?php echo $LANG['UI_ALARM_NOT_CURRENT_JOB'] ?></option>
								</select>
							</div>
						</div>
					</div>
					<div class="list-option">
						<div class="row">
							<!-- 告警ID -->
							<label class="control-label col-md-4"><?php echo $LANG['UI_ALARM_ID'] ?>
							</label>
							<div class="col-md-6">
								<input id="advanced_search_alarm_id" type="number" maxlength="64"
									class="table-group-action-input form-control" aria-controls="example"
									onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}">
							</div>
						</div>
					</div>
					<!-- 虚拟机类型 -->
					<div class="list-option advanced-search-vm-type display-none">
						<div class="row">
							<label
								class="control-label col-md-4"><?php echo $LANG['UI_VM_TYPE']; ?>
							</label>
							<div class="col-md-6">
								<select class="form-control" id="advanced_search_vm_type"></select>
							</div>
						</div>
					</div>
					<!-- 数据库类型 -->
					<div class="list-option advanced-search-db-type display-none">
						<div class="row">
							<label
								class="control-label col-md-4"><?php echo $LANG['UI_DB_DATABASE_TYPE']; ?>
							</label>
							<div class="col-md-6">
								<select class="form-control" id="advanced_search_db_type"></select>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal"
					class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary"
					id="job_alarm_advanced_search_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END SEARCH MODAL -->

	</div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	//如果不是英文,加载语言包
	echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/cascader/js/cascader.js"></script>
<script type="text/javascript" src="./scripts/platform/alarm/task_alarm.js"></script>
<!-- END PAGE LEVEL PLUGINS -->