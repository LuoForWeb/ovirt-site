<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>

<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css"
	href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
	type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css"
	href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css"
	href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="table-toolbar-wrapper vin_toolbar" id="system_alarm_toolbar">
			<div class="table-toolbar-wrapper__left" style="display: flex;">
				<?php
				//检查屏蔽只读观察者的操作按钮
				if (in_array("p_system_alarm_delete", $_SESSION['permissionArr'])) {
					// 删除
					echo '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteSystemAlarm"></button></div>';
				}
				if (in_array("p_system_alarm_response", $_SESSION['permissionArr'])) {
					// 响应
					echo '<button type="button" id="solveSystemAlarm" class="btn table-toolbar-btn">
                						<i class="viconfont vicon-biaojiweixiangying"></i>' . $LANG['UI_ALARM_RESPONSE_DO'] . '
                					</button>';
				}
				?>
			</div>
			<div class="table-toolbar-wrapper__right">
				<div class="table-actions-wrapper page-right" style="display: flex;">
					<select id="systemAlarmLevelSelect"
						class="table-group-action-input form-control input-inline input-small mr12">
						<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
						<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_GENERAL'] ?></option>
						<option value="3"><?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?></option>
						<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></option>
					</select>
					<button type="button" id="system_searchAll" class="btn btn-primary adv_btn brr2 p-lr8">
						<i class="adv-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
					</button>
					<div class="vin_btnToolbar">
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div id="system_searchDiv" class="searchDiv display-none">
					<?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span class="searchContent"></span><span
						class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span> </div>
			</div>
		</div>

		<div class="table-container" id="system_alarm_div">
			<table class="table table-hover" id="systemAlarm"></table>
		</div>
		<!-- End: life time stats -->

		<!-- BEGIN MODAL -->
		<div id="systemAlarmModalDiv" class="modal xmodal fade form-horizontal " tabindex="-1"
			data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-bell"></i> <?php echo $LANG['UI_ALARM_DETAILS'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="row static-info">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_ALARM_ID'] ?>:
					</div>
					<div class="col-md-8 value" id="system_alarm_id">
					</div>
				</div>
				<div class="row static-info">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_ALARM_LEVLE'] ?>:
					</div>
					<div class="col-md-8 value" id="system_alarm_level">
					</div>
				</div>
				<div class="row static-info">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_ALARM_TIME'] ?>:
					</div>
					<div class="col-md-8 value" id="system_alarm_time">
					</div>
				</div>
				<div class="row static-info">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_ALARM_CONTENT'] ?>:
					</div>
					<div class="col-md-8 value" id="system_alarm_content">
					</div>
				</div>
				<div class="row static-info">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_ALARM_RESPONSE_FLAG'] ?>:
					</div>
					<div class="col-md-8 value" id="system_alarm_solved_des">
					</div>
				</div>
				<div class="row static-info system_solved_div">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_ALARM_RESPONSE_USER'] ?>:
					</div>
					<div class="col-md-8 value" id="system_alarm_solved_user">
					</div>
				</div>
				<div class="row static-info system_solved_div">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_ALARM_RESPONSE_TIME'] ?>:
					</div>
					<div class="col-md-8 value" id="system_alarm_solved_time">
					</div>
				</div>
				<div class="row static-info system_alarm_push_div">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_PLATFORM_ALARM_PUSH']; ?>
					</div>
					<div class="col-md-8 value">
						<a id="system_alarmPush_btn" class="c0FBF98"><?php echo $LANG['UI_PLATFORM_PUSH_THIRD']; ?></a>
					</div>
				</div>
				<div class="row static-info system_response_push_div">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_PLATFORM_RESPONSE_PUSH']; ?>
					</div>
					<div class="col-md-8 value">
						<a id="system_responsePush_btn" class="c0FBF98"><?php echo $LANG['UI_PLATFORM_RESPONSE_PUSH_THIRD']; ?></a>
					</div>
				</div>
				<div class="row static-info">
					<div class="col-md-4 name textalignr">
						<?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL'] ?>:
					</div>
					<div class="col-md-8 value" id="system_alarm_email">
					</div>
				</div>
				<?php
				if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
					echo ' <div class="row static-info">
						<div class="col-md-4 name textalignr" >' .
						$LANG['UI_SETTINGS_NOTICE_SMS'] . ':
						</div>
						<div class="col-md-8 value" id="system_alarm_sms">
						</div>
					</div>';
				}
				?>
				<!-- 英文版屏蔽微信和企业微信 -->
				<?php
				if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
					echo ' <div class="row static-info">
					<div class="col-md-4 name textalignr" >' .
						$LANG['UI_SETTINGS_NOTICE_WECHAT'] . ':
					</div>
					<div class="col-md-8 value" id="system_alarm_weChat">
					</div>
					</div>
					<div class="row static-info">
					<div class="col-md-4 name textalignr" >' .
						$LANG['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'] . ':
						</div>
						<div class="col-md-8 value" id="system_alarm_weChat2">
						</div>
					</div>';
				}
				?>
			</div>
			<div class="modal-footer">
				<?php
				//检查屏蔽只读观察者的操作按钮
					if (in_array("p_system_alarm_response", $_SESSION['permissionArr'])) {
						// 响应
						echo '<button type="button" class="btn green-haze" id="systemSolvedBtn">' . $LANG['UI_ALARM_RESPONSE_NO_DO'] . '</button>';
					}
				?>
				<button type="button" data-dismiss="modal"
					class="btn btn-primary"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
			</div>
		</div>
		<!-- END MODAL -->

		<!-- BEGIN SEARCH MODAL -->
		<div id="system_search_modal" class="modal xmodal fade form-horizontal high-search" tabindex="-1"
			data-focus-on="input:first" data-backdrop="static">
			<input id="system_vcenteruuid" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-gaojisousuo1"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">

					<!-- 日志时间范围 -->
					<div class="list-option" id="startTimeDiv">
						<div class="row">
							<label class="control-label col-md-4"><?php echo $LANG['UI_ALARM_TIME_RANGE'] ?>
							</label>
							<div class="col-md-5 daterangepickerdiv">
								<input type="text" id="dateRangePickerSystemAlarm" class="form-control"
									autocomplete="off">
								<i class="viconfont vicon-ge_calendar"></i>
							</div>
						</div>
					</div>

					<!-- 告警ID -->
					<div class="list-option">
						<div class="row">
							<label class="control-label col-md-4"><?php echo $LANG['UI_ALARM_ID'] ?>
							</label>
							<div class="col-md-5">
								<input id="sys_alarm_id" type="number" maxlength="64"
									class="table-group-action-input form-control" aria-controls="example"
									onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}">
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
					id="system_search_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END SEARCH MODAL -->

	</div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript"
	src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript"
	src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	//如果不是英文,加载语言包
	echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript"
	src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/alarm/system_alarm.js"></script>
<!-- END PAGE LEVEL PLUGINS -->