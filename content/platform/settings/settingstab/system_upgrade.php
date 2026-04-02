<?php include_once '../../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/web-uploader/webuploader.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/timeline.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php" >
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
		</a>
	</li>
	<span>
		>
	</span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_SYSTEM_UPDATE'] ?></span>
</h3>
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('upgrade_manage', 'upgrade_history', 'upgrade_check');
$activeClassArr = array();  //active类
$displayArr = array();      //是否显示

$setFlag = false;
foreach ($tabNameArr as $key => $value) {
	if (in_array($value, $userAllPermission)) {
		//如果有权限
		$displayArr[$value] = "";
		if (!$setFlag) {
			$activeClassArr[$value] = " active ";
			$setFlag = true;
		} else {
			$activeClassArr[$value] = "";
		}
	} else {
		//如果没有权限
		$activeClassArr[$value] = "";
		$displayArr[$value] = "displaynone";
	}
}
?>

<!-- BEGIN PAGE CONTENT -->
<div class="resource-manager-wrap">
	<div class="resource-manager-wrap__header">
		<ul class="nav nav-tabs" id="updateUL">
			<li class="<?php echo $activeClassArr['upgrade_manage'] ?> <?php echo $displayArr['upgrade_manage'] ?>">
				<a href="#autoup_tab" data-toggle="tab" aria-expanded="false">
					<i class="levelchild viconfont vicon-pt_setting_package"></i> <?php echo $LANG['UI_SETTINGS_UPDATE_MANAGE'] ?>
				</a>
			</li>
			<li class="<?php echo $activeClassArr['upgrade_history'] ?> <?php echo $displayArr['upgrade_history'] ?>">
				<a href="#handup_tab" data-toggle="tab" aria-expanded="false">
					<i class="levelchild viconfont vicon-pt_setting_history"></i> <?php echo $LANG['UI_SETTINGS_UPDATE_HISTORY'] ?>
				</a>
			</li>
			<li class="<?php echo $activeClassArr['upgrade_check'] ?> <?php echo $displayArr['upgrade_check'] ?>" style="display: none">
				<a href="#updatetab" data-toggle="tab" aria-expanded="false">
					<i class="levelchild viconfont vicon-pt_setting_update"></i> <?php echo $LANG['WEB_SETTINGS_UPDATE_CHECK_ONLINE'] ?>
				</a>
			</li>
		</ul>
	</div>

	<div class="resource-manager-wrap__content">
		<div class="tab-content">
			<div class="tab-pane <?php echo $activeClassArr['upgrade_manage'] ?>" id="autoup_tab">
				<div class="flex-items-center h-20px mb-10">
					<?php echo $LANG['UI_SETTINGS_UPDATE_CURREN_VERSION'] ?>: <span id="currentVersion"><?php echo $CONF['SYSTEM_INFO']['enterprise'] . " " . $CONF['SYSTEM_INFO']['version'] ?></span>
				</div>
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left display-inline-flex">
                        <?php if (in_array("p_setting_manager_delete", $_SESSION['permissionArr'])) { ?>
                            <div>
                                <button type="button" id="deletePatch" class="b-btn brr2 mr12 table-toolbar-btn cancel-delete-btn" style="cursor: not-allowed">
                                    <i class="icon-gray-delete me-0"></i>
                                </button>
                            </div>
                        <?php } ?>
                        <?php if (in_array("p_setting_manager_upload", $_SESSION['permissionArr'])) { ?>
                            <div class="btn-group">
                                <button type="button" id="fileupload" class="btn table-toolbar-btn">
                                    <i class="viconfont vicon-shangchuan"></i> <?php echo $LANG['UI_SETTINGS_UPLOAD_UPGRADE_PATCH'] ?>
                                </button>
                            </div>
                        <?php } ?>
                        <?php if (in_array("p_setting_manager_upgrade", $_SESSION['permissionArr'])) { ?>
                            <div class="btn-group">
                                <button type="button" id="upgrade" class="btn table-toolbar-btn">
                                    <i class="viconfont vicon-shengji"></i> <?php echo $LANG['UI_SETTINGS_UPDATE_IMMEDIATELY'] ?>
                                </button>
                            </div>
                        <?php } ?>
						<div class="btn-group <?php echo $displayArr['upgrade_check'] ?>"  style="display: none">
							<button type="button" id="upgrade_cloud" class="btn table-toolbar-btn">
								<i class="glyphicon glyphicon-cloud-download"></i> <?php echo $LANG['WEB_SETTINGS_UPDATE_ONLINE'] ?>
							</button>
						</div>
					</div>
				</div>

				<div class="table-container upgrade-manager-table-container">
					<table id="patchTable"></table>
				</div>

				<div class="alert alert-block alert-info fade in h-50px m0"  id="upgrade_manage_tip">
					<button type="button" class="close" data-dismiss="alert"></button>
					<ul class="alert-ul">
						<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
						<li>
							<?php echo $LANG['UI_SETTINGS_UPDATE_MANAGE_TIPS'] ?>
						</li>
					</ul>
				</div>
			</div>

			<div class="tab-pane <?php echo $activeClassArr['upgrade_history'] ?>" id="handup_tab">
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left display-inline-flex">
                        <?php if (in_array("p_upgrade_history_delete", $_SESSION['permissionArr'])) { ?>
						<div>
							<button type="button" id="deleteHistoryLog" class="b-btn brr2 mr12 table-toolbar-btn cancel-delete-btn" style="cursor: not-allowed">
								<i class="icon-gray-delete me-0"></i>
							</button>
						</div>
                        <?php } ?>
						<div class="btn-group">
							<button type="button" id="downloadHistoryLog" class="btn table-toolbar-btn">
								<i class="viconfont vicon-xiazai"></i> <?php echo $LANG['UI_SETTINGS_UPDATE_DOWNLOAD_HISTORY_LOG'] ?>
							</button>
						</div>
					</div>
				</div>

				<div class="table-container upgrade-history-table-container">
					<table id="patchHistoryTable"></table>
				</div>
			</div>

			<div class="tab-pane" id="updatetab">
				<div class="row">
					<form action="#" class="form-horizontal mt10">
						<div class="form-body pding15 pb0">
						<div class="form-group pt50">
							<label class="control-label col-md-3"><?php echo $LANG['WEB_SETTINGS_UPDATE_CHECK_ONLINE'] ?>:</label>
							<div class="col-md-8">
								<input type="checkbox" id="updatecheck"  class="make-switch" data-on-color="primary" data-off-color="info"
								data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
								data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
								<div style="margin-top: 2px;"><label><input type="checkbox" id="updateTCP"><span><?php echo $LANG['WEB_SETTINGS_UPDATE_AUTHORIZATION_CONTENT1'] ?><a id="TCPdetails">《<?php echo $LANG['WEB_SETTINGS_UPDATE_MSG_AUTHORIZATION'] ?>》</a><?php echo $LANG['WEB_SETTINGS_UPDATE_AUTHORIZATION_CONTENT2'] ?></span></label></div>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['WEB_SETTINGS_UPDATE_CHECK_PERIOD'] ?>:</label>
							<div class="col-md-3">
							<select class="form-control select2me" id="updatetime">
								<option value="no"><?php echo $LANG['WEB_SETTINGS_UPDATE_CHECK_PERIOD_NO'] ?></option>
								<option value="day"><?php echo $LANG['WEB_SETTINGS_UPDATE_CHECK_PERIOD_DAY'] ?></option>
								<option value="week"><?php echo $LANG['WEB_SETTINGS_UPDATE_CHECK_PERIOD_WEEK'] ?></option>
								<option value="month"><?php echo $LANG['WEB_SETTINGS_UPDATE_CHECK_PERIOD_MONTH'] ?></option>
							</select>
							</div>
							<div class="col-md-2 mt10">
							<a class="popovers dm-tips" data-container="body" data-trigger="hover" data-placement="right" data-html="true"
								data-content="<?php echo $LANG['WEB_SETTINGS_UPDATE_CHECK_PERIOD_TIPS'] ?>"><i class="fa fa-info-circle fa-lg"></i>
								</a>
							</div>
						</div>

						<div class="form-actions mt235">
							<div class="row">
								<div class="col-md-offset-3 col-md-8">
									<button type="button" id="updatecancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
									<button type="button" id="updatesubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
								</div>
							</div>
						</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN UPLOAD PACKET MODAL -->
<div id="uploadModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="viconfont vicon-shangchuan" style="color:#0FBF98"></i> <?php echo $LANG['UI_SETTINGS_UPLOAD_UPGRADE_PATCH'] ?></h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div id="uploader" class="mb-10">
				<div id="thelist" class="uploader-list"></div>
				<div class="btns">
					<div id="picker" class="btn-group"><?php echo $LANG['UI_SETTINGS_UPDATE_SELECT_FILE'] ?></div>
					<div class="btn-group">
						<button type="button" id="ctlBtn" class="btn btn-sm green-haze"><?php echo $LANG['UI_SETTINGS_UPDATE_START_UPLOAD'] ?></button>
					</div>
					<div class="btn-group pauseDiv" style="display:none;">
						<button type="button" id="pause" class="btn btn-sm green-haze">
						<i class="fa fa-pause"></i> <?php echo $LANG['WEB_VM_PAUSE'] ?>
						</button>
					</div>
					<div class="btn-group startDiv" style="display: none;">
						<button type="button" id="continue" class="btn btn-sm green-haze">
						<i class="fa  fa-play"></i> <?php echo $LANG['UI_SETTINGS_UPDATE_CONTINUE'] ?>
						</button>
					</div>
					<div class="btn-group retryDiv" style="display: none;">
						<button type="button" id="retry" class="btn btn-sm green-haze">
						<i class="fa  fa-refresh"></i> <?php echo $LANG['UI_SETTINGS_UPDATE_RETRY'] ?>
						</button>
					</div>
				</div>
			</div>
			<div class="alert alert-block alert-info fade in m0">
				<button type="button" class="close" data-dismiss="alert"></button>
				<ul class="alert-ul">
					<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
					<li>
						<?php echo $LANG['UI_SETTINGS_UPDATE_UPLOAD_PATCH_TIPS'] ?>
					</li>
				</ul>
			</div>
		</div>
	</div>

</div>
<!-- END UPLOAD PACKET MODAL -->

<!-- BEGIN UPGRADE NOW MODAL -->
<div id="upgradeModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="glyphicon glyphicon-circle-arrow-up"></i> <?php echo $LANG['UI_PLATFORM_SYSTEM_UPDATE'] ?></h4>
	</div>

	<div class="modal-body upgrade-modal-body">
		<input id="patch_uuid" class="display-none"></input>
		<div class="alert alert-warning display-none" id="updateAlarm">
			<p><?php echo $LANG['UI_SETTINGS_UPDATE_RUN_TIPS'] ?></p>
		</div>
		<div class="alert alert-success display-none" id="updateSuccess"></div>
		<div class="upgrade-form">
			<div class="upgrade-form-item">
				<div class="upgrade-form-item__label col-md-2">
					<?php echo $LANG['UI_SETTINGS_UPDATE_PATCH_NAME'] ?>:
				</div>
				<div class="upgrade-form-item__content col-md-10">
					<span id="patchName"></span>
				</div>
			</div>
			<div class="upgrade-form-item">
				<div class="upgrade-form-item__label col-md-2">
					<?php echo $LANG['UI_SETTINGS_UPDATE_WAY'] ?>:
				</div>
				<div class="upgrade-form-item__content col-md-10">
					<select class="form-control select2me" id="nodeSelect"></select>
				</div>
			</div>
            <div class="upgrade-form-item">
                <div class="upgrade-form-item__label col-md-2">
                    <?php echo $LANG['UI_SETTINGS_UPDATE_CUSTOM_OPTION'] ?>:
                </div>
                <div class="col-md-10">
                    <div class="input-group form-group-boxes" id="strategymode">
                        <label class="pr20 mb0 "><input id="update_web_config" type="checkbox"  data-checkbox="icheckbox_square-blue" class="icheck" checked><?php echo $LANG['UI_SETTINGS_UPDATE_WEB_CONFIG'] ?></label>
                        <label class="pr20 mb0"><input id="update_web_cert" type="checkbox" data-checkbox="icheckbox_square-blue"  class="icheck" checked><?php echo $LANG['UI_SETTINGS_UPDATE_WEB_CERT'] ?></label>
                        <label class="mb0 "><input id="update_firewall_rule" type="checkbox"  data-checkbox="icheckbox_square-blue"  class="icheck" checked><?php echo $LANG['UI_SETTINGS_UPDATE_FIREWALL_RULE'] ?></label>
                        <span class="help-block"><?php echo $LANG['UI_SETTINGS_UPDATE_CUSTOM_OPTION_TIPS'] ?></span>
                    </div>
                </div>
            </div>
			<div class="upgrade-form-item display-none" id="childDiv">
				<div class="upgrade-form-item__label col-md-2">
					<?php echo $LANG['UI_SETTINGS_UPDATE_SELECT_CHILD_NODE'] ?>:
				</div>
				<div class="upgrade-form-item__content col-md-10">
					<div class="childnode-wrapper" id="childNodes">

					</div>
				</div>
			</div>
			<div class="upgrade-form-item display-none" id="progressDiv">
				<div class="upgrade-form-item__label col-md-2">
					<?php echo $LANG['UI_SETTINGS_UPDATE_PROGRESS'] ?>:
				</div>
				<div class="upgrade-form-item__content col-md-10">
					<div class="upgrade-wrapper">
						<div class="upgrade-wrapper-progress" id="updateList"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<button type="button"  class="btn btn-default display-none" id="closeBtn"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
		<button type="button" data-dismiss="modal" class="btn btn-default" id="cancelUpdate"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary" id="upgradeSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>
<!-- END UPGRADE NOW MODAL -->

<!-- BEGIN PACKET DETAIL MODAL -->
<div id="detailsModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="glyphicon glyphicon-circle-arrow-up"></i> <?php echo $LANG['WEB_SETTINGS_UPDATE_LOG_DETAILS'] ?></h4>
	</div>
	<div class="modal-body" style="max-height:700px;">
		<div class="portlet-body">
			<ul class="timeLine" style="width: 1200px;">
			</ul>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button"  data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
	</div>
</div>
<!-- END PACKET DETAIL MODAL -->

<!-- BEGIN PERSONAL INFO AUTHORIZATION MODAL -->
<div id="authDetails" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="glyphicon glyphicon-circle-arrow-up"></i> <?php echo $LANG['WEB_SETTINGS_UPDATE_MSG_AUTHORIZATION'] ?>
				</h4>
			</div>
			<div class="modal-body" style="max-height:700px;">
				<div class="portlet-body">
					<div><?php include_once './updateTCP.php'; ?></div>
				</div>
			</div>

			<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" class="btn btn-default" id="TCPcancel"><?php echo $LANG['UI_PUBLIC_CANCEL'] ?></button>
		<button type="button" class="btn btn-primary" id="TCPsubmit"><?php echo $LANG['WEB_SETTINGS_UPDATE_AUTHORIZATION_AND_CONTINUE'] ?></button>
	</div>
</div>
<!-- END PERSONAL INFO AUTHORIZATION MODAL -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/web-uploader/webuploader.min.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>

<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_upgrade.js"></script>