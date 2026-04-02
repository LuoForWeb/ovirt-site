<?php
include_once '../../tpl/permission.php';
// $userAllPermission = $_SESSION['permission'];
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
			<span><?php echo $LANG['UI_PLATFORM_INFRASTRUCTURE'] ?></span>
		</a>
	</li>
	<span>
		>
	</span>
	<span class="curent"><?php echo $LANG['UI_NAS_DEVICE_NAME'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap" id="nas_content">
	<div class="resource-manager-wrap__header">
		<span>
			<i class="levelchild viconfont vicon-nasmanager me-10"></i>
			<span class="resource-manager-wrap__header__text"><?php echo $LANG['UI_NAS_DEVICE_NAME'] ?></span>
		</span>
	</div>
	<div class="resource-manager-wrap__content">
		<div class="table-toolbar">
			<div class="vin_toolbar" id="vin_nas_toolbar">
				<div class="leftTool"></div>
				<div class="rightTool">
					<div class="vin_btnToolbar"></div>
				</div>
			</div>
		</div>

		<div class="table-container nas-manager-table-container">
			<table class="table" id="table"></table>
		</div>

		<div class="alert alert-block alert-info fade in h-150px m0" id="nas_manager_tip">
			<button id="nas_manager_tip_close" type="button" class="close" data-dismiss="alert"></button>
			<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
			<ol class = "alert-ol">
				<li>
					<?php echo $LANG['UI_NAS_DEVICE_MANAGE_TIPSONE'] ?>
				</li>
				<li>
					<?php echo $LANG['UI_NAS_DEVICE_MANAGE_TIPSTWO'] ?>
				</li>
				<li>
					<?php echo $LANG['UI_NAS_DEVICE_MANAGE_TIPSTHREE'] ?>
				</li>
				<li>
					<?php echo $LANG['UI_NAS_DEVICE_MANAGE_TIPSFOUR'] ?>
				</li>
			</ol>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN ADD MODAL -->
<div id="addNasModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<input id="edit_nas_uuid" class="display-none"></input>
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title" id="addTitle" style="display: flex;align-items: center">
			<i class="viconfont vicon-danchuangtianjia1 mr5"></i>
			<span><?php echo $LANG['UI_NAS_DEVICE_ADD'] ?></span>
		</h4>
	</div>
	<div class="modal-body">
		<form action="#" id="nasForm" class="form-horizontal">
			<div class="form-group" id="usemodeDiv">
				<label class="control-label col-md-3" style="line-height: 27px;"><?php echo $LANG['UI_NAS_AGREEMENT_TYPE'] ?></label>
				<div class="col-md-8 mt8" id="useMode">
					<label class="cifsDiv pr20">
						<input type="radio" id="cifsCheck" name="radiobox" data-mode="1" class="mr5">CIFS
					</label>
					<label class="nfsDiv">
						<input type="radio" id="nfsCheck" name="radiobox" data-mode="2">NFS
					</label>
				</div>
			</div>
			<!-- 厂商 -->
			<div class="form-group">
				<label class="col-md-3 control-label"><?php echo $LANG['UI_LOGIN_AGENT_VENDOR'] ?></label>
				<div class="col-md-8">
					<select class="form-control inputversion" id="vendor">
						<option value="0"><?php echo $LANG['UI_EMERGENCY_PLAN_OTHERS'] ?></option>
						<option value="1"><?php echo $LANG['UI_NAS_HUAWEI_DORADO'] ?></option>
					</select>
				</div>
			</div>
			<div class="vendor-info display-none">
				<!-- DeviceManager 配置 -->
				<div class="form-group simpleDiv">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_NAS_DEVICE_MANAGER_CONFIG'] ?></label>
					<div class="col-md-8 accordion mb0">
						<div class="panel panel-default mb0">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a class="accordion-toggle accordion-toggle-styled popovers" data-content="" 
									data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent="" href="#DeviceManager">
									<?php echo $LANG['UI_NAS_DEVICE_MANAGER_CONFIG'] ?></a>
								</h4>
							</div>
							<div id="DeviceManager" class="panel-collapse collapse in">
								<div class="panel-body">
									<!-- DeviceManager ip -->
									<div class="form-group simpleDiv">
										<label class="col-md-3 control-label"><?php echo $LANG['UI_EXERCISE_PLATFORM_IP_OR_NAME'] ?></label>
										<div class="col-md-8">
											<div class="input-icon right">
												<i class="fa"></i>
												<input type="text" class="form-control" maxlength="1280" name="rest_ip" id="rest_ip" placeholder="http://100.115.9.10:8088">
											</div>
											<span class="help-block "><?php echo $LANG['UI_NAS_DEVICE_MANAGER_CONFIG_IP'] ?></span>
										</div>
									</div>
									<!-- DeviceManager用户名 -->
									<div class="form-group simpleDiv">
										<label class="col-md-3 control-label"><?php echo $LANG['UI_NAS_CLOUD_USERNAME'] ?></label>
										<div class="col-md-8">
											<div class="input-icon right">
												<i class="fa"></i>
												<input type="text" class="form-control" maxlength="1280" name="rest_username" id="rest_username" placeholder="admin">
											</div>
											<span class="help-block "><?php echo $LANG['UI_NAS_DEVICE_MANAGER_CONFIG_USERNAME'] ?></span>
										</div>
									</div>
									<!-- DeviceManager密码 -->
									<div class="form-group simpleDiv">
										<label class="col-md-3 control-label"><?php echo $LANG['UI_NAS_CLOUD_PASSWORD'] ?></label>
										<div class="col-md-8">
											<div class="input-icon right">
												<i class="fa"></i>
												<input type="password" autocomplete="off" class="form-control" maxlength="64" name="rest_password" id="rest_password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
											</div>
											<span class="help-block "><?php echo $LANG['UI_NAS_DEVICE_MANAGER_CONFIG_PSW'] ?></span>
										</div>
									</div>
									<!-- 租户 ID-->
									<div class="form-group simpleDiv">
										<label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_CLIENT_TENANT'] ?></label>
										<div class="col-md-8">
											<div class="input-icon right">
												<i class="fa"></i>
												<input type="text" autocomplete="off" class="form-control" maxlength="64" name="rest_vstoreid" id="rest_vstoreid" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
											</div>
											<span class="help-block "><?php echo $LANG['UI_NAS_DEVICE_MANAGER_ID_DES'] ?></span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group simpleDiv">
				<label class="col-md-3 control-label"><?php echo $LANG['UI_EXERCISE_PLATFORM_IP_OR_NAME'] ?></label>
				<div class="col-md-8">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" class="form-control" maxlength="128" name="ipaddress" id="ipaddress" placeholder="<?php echo $LANG['UI_NAS_PLEASE_INPUT_IP'] ?>">
					</div>
				</div>
			</div>
			<div class="form-group simpleDiv">
				<label class="col-md-3 control-label"><?php echo $LANG['UI_STORAGE_SHARED_FOLDERS'] ?></label>
				<div class="col-md-8">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" class="form-control" maxlength="1280" name="naspath" id="naspath" placeholder="/Vol_OA/home">
					</div>
				</div>
			</div>
			<!-- 读写权限 -->
			<div class="form-group" id="writereadDiv">
				<label class="control-label col-md-3" style="line-height: 27px;"><?php echo $LANG['UI_NAS_MANAGE_READ_WRITE_PERMISSION'] ?></label>
				<div class="col-md-8 mt8" id="writereadMode">
					<label class="writereadDiv pr20">
						<input type="radio" id="writereadCheck" name="icheckbox" data-mode="1" class="mr5"><?php echo $LANG['UI_NAS_MANAGE_WRITE_AND_READ'] ?>
					</label>
					<label class="readDiv">
						<input type="radio" id="readCheck" name="icheckbox" data-mode="2"><?php echo $LANG['UI_NAS_MANAGE_ONLY_READ'] ?>
					</label>
				</div>
			</div>
			<div class="form-group simpleDiv">
				<label class="col-md-3 control-label"><?php echo $LANG['UI_NAS_AGREEMENT_VERSION'] ?></label>
				<div class="col-md-8">
					<div class="selecversiondiv">
						<select class="form-control inputversion" id="nasversion">
							<option value=""><?php echo $LANG['UI_NAS_DEFAULT_VERSION'] ?></option>
							<option value="2.0">v2.0</option>
							<option value="3.0">v3.0</option>
						</select>
						<div><span class="help-block "><?php echo $LANG['UI_NAS_TO_DIY_VESION'] ?><a id="diynasversion"><?php echo $LANG['UI_NAS_DIY_VERSION'] ?></a></span></div>
					</div>
					<div class="input-icon right display-none" id="inputversiondiv">
						<i class="fa"></i>
						<input type="text" maxlength="128" class="form-control inputversion" id="inputversion">
						<div><span class="help-block "><?php echo $LANG['UI_NAS_TO_SELECT_VERSION'] ?><a id="selectversion"><?php echo $LANG['UI_NAS_SELECT_VERSION'] ?></a></span></div>
					</div>
				</div>
			</div>
			<div class="form-group simpleDiv">
				<label class="col-md-3 control-label"><?php echo $LANG['UI_VCENTER_RNAME'] ?></label>
				<div class="col-md-8 ">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" class="form-control" maxlength="64" name="nickname" id=nickname>
					</div>
				</div>
			</div>
			<!-- 类型是cifs -->
			<div class="cifsdiv">
				<div class="form-group simpleDiv">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_NAS_CLOUD_USERNAME'] ?></label>
					<div class="col-md-8">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="text" class="form-control" maxlength="64" name="adminname" id="adminname">
							<div><span class="help-block "><?php echo $LANG['UI_NAS_CLOUD_USERNAME_DES'] ?></span></div>
						</div>
					</div>
				</div>
				<div class="form-group simpleDiv">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_NAS_CLOUD_PASSWORD'] ?></label>
					<div class="col-md-8 ">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="password" autocomplete="off" class="form-control" maxlength="64" name="password" id="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
						</div>
					</div>
				</div>
			</div>
			
			<div class="form-group simpleDiv">
				<label class="col-md-3 control-label"><?php echo $LANG['UI_NAS_SELECT_MOUNT_NODE'] ?></label>
				<div class="col-md-8">
					<select id="nodesSelect" name="" class="bootstrap-mutiple-select selectpicker show-tick" multiple></select>
				</div>
			</div>
			<!-- 高级配置 -->
			<div class="form-group simpleDiv">
				<label class="col-md-3 control-label"><?php echo $LANG['UI_NAS_HIGH_SETTING'] ?></label>
				<div class="col-md-8 accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-content="" 
								data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent="" href="#highconfig">
									<?php echo $LANG['UI_NAS_HIGH_SETTING'] ?></a>
							</h4>
						</div>
						<div id="highconfig" class="panel-collapse collapse">
							<div class="panel-body">
									<div class="col-md-12">
										<!-- 类型是nfs -->
									<div class="">
										<div class="form-group simpleDiv">
											<label class="col-md-4 control-label"><?php echo $LANG['UI_SETTINGS_NOTICE_PORT'] ?></label>
											<div class="col-md-8">
												<div class="input-icon right">
													<i class="fa"></i>
													<input type="text" class="form-control" maxlength="64" name="nasport" id="nasport">
												</div>
											</div>
										</div>
									</div>
									<div class="form-group simpleDiv configDiv">
										<label class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_MOUNT_PARAMS'] ?>
										</label>
										<div class="col-md-8">
											<div class="input-icon right">
												<i class="fa"></i>
												<input style="display:none"><!-- for disable autocomplete on chrome -->
												<input type="text" maxlength="1280" class="form-control" id="config" placeholder="vers=x.0,xxx=xxx"/>
												<span class="help-block"><?php echo $LANG['UI_NAS_MANAGE_CONFIG_TIPS'] ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
		<button type="button" class="btn btn-primary" id="editsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>	
<!-- END ADD MODAL -->

<!-- BEGIN AUTH MODAL -->
<div id="nasAuthModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="icon-badge"></i> <?php echo $LANG['UI_NAS_DEVICE_AUTHORIZATION'] ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="form-group alldirdiv">
				<div class="col-md-12">
					<!-- 添加授权 -->
					<div class="btn-group">
						<button type="button" id="authClient" class="btn btn-sm green-haze">
						<i class="viconfont vicon-ge_authorization2"></i> <?php echo $LANG['UI_VCENTER_AUTH_ADD'] ?>
						</button>
					</div>
					<!-- 取消授权 -->
					<div class="btn-group">
						<button type="button" id="authClientRemove" class="btn btn-sm green-haze">
						<i class="viconfont vicon-guanbi"></i> <?php echo $LANG['UI_VCENTER_AUTH_DELETE'] ?>
						</button>
					</div>
					<span id="nasDes" class="floatRight" style="margin-top: 7px;"></span>
					<div class="table-container mt20">
						<table class="table table-striped table-bordered table-hover lh2" id="nasIpDatatable">
							<thead>
								<tr role="row" class="heading">
									<th width="3%">
										<input type="checkbox" class="group-checkable">
									</th>
									<th width="40%">
											IP
									</th>
									<th width="20%">
										<?php echo $LANG['UI_DRILLS_DETAIL_STATUS'] ?>
									</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?></button>
	</div>
</div>	
<!-- END AUTH MODAL -->

<!-- BEGIN mount MODAL -->
<div id="nasMountModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="fa fa-chain (alias)-badge"></i> <?php echo $LANG['UI_NAS_MOUNT_NODE'] ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="form-group" id="authwaysDiv">
				<label class="control-label col-md-3"><?php echo $LANG['UI_NAS_CANCLE_SELECT'] ?></label>
				<div class="col-md-7 pr0">
					<select id="nodesMountSelect" name="" class="bootstrap-mutiple-select selectpicker show-tick" multiple></select>
				</div>
				<div class="col-md-1" style="margin-top: 10px">
					<a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_NAS_MANAGE_MOUNT_TIPS'] ?>" data-original-title="" title="">
						<i class="viconfont vicon-tishi"></i>
					</a>
				</div>
			</div>
		</div>
	</div>
	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button"class="btn btn-primary" id="mountsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>	
<!-- END mount MODAL -->

<!-- BEGIN umount MODAL -->
<div id="nasUmountModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="fa fa-chain-broken"></i> <?php echo $LANG['UI_NAS_UMOUNT_NODE'] ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="form-group" id="authwaysDiv">
				<label class="control-label col-md-3"><?php echo $LANG['UI_NAS_CANCLE_SELECT'] ?></label>
				<div class="col-md-7 pr0">
					<select id="nodesUmountSelect" name="" class="bootstrap-mutiple-select selectpicker show-tick" multiple></select>
				</div>
				<div class="col-md-1" style="margin-top: 10px">
					<a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_NAS_MANAGE_UMOUNT_TIPS'] ?>" data-original-title="" title="">
						<i class="viconfont vicon-tishi"></i>
					</a>
				</div>
			</div>
		</div>
	</div>
	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button"class="btn btn-primary" id="umountsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>	
<!-- END umount MODAL -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>

<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>

<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/nas/nasmanager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	