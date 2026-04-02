<?php include_once '../../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/web-uploader/webuploader.css" rel="stylesheet" type="text/css"/>
<!-- BEGIN PAGE HEADER-->
<style>
    #system-service .fixed-table-container .fixed-table-body {
        height: 500px;
        overflow: visible !important;
    }
</style>
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php" >
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_SETTINGS_SYSTEM_TOOL'] ?></span>
</h3>
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('service_manage', 'network_tool', 'remote_control');
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

// var_dump($activeClassArr, $displayArr);

?>
<div class="row" id="system-service">
	<div class="col-md-12">
		<!-- BEGIN TAB PORTLET-->
		 <div class="portlet box blue-hoki">
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
					<li class="<?php echo $activeClassArr['service_manage'] ?> <?php echo $displayArr['service_manage'] ?>">
						<a href="#service_tab" data-toggle="tab" aria-expanded="false">
						<i class="levelchild viconfont vicon-pt_setting_service_management"></i> <?php echo $LANG['UI_SETTINGS_SERVICE_MANAGE'] ?> </a>
					</li>
					<li class="<?php echo $activeClassArr['network_tool'] ?> <?php echo $displayArr['network_tool'] ?>">
						<a href="#telnet_tab" data-toggle="tab" aria-expanded="false">
						<i class="levelchild viconfont vicon-pt_setting_service_network"></i> <?php echo $LANG['UI_SETTINGS_NETWORK_TOOL'] ?> </a>
					</li>
					<li class="<?php echo $activeClassArr['remote_control'] ?> <?php echo $displayArr['remote_control'] ?>">
						<a href="#ssh_tab" data-toggle="tab" aria-expanded="false">
						<i class="levelchild viconfont vicon-pt_setting_teamviewer"></i> <?php echo $LANG['UI_PLATFORM_REMOTE_CONTROL'] ?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body pding20">
				<div class="tab-content">
					<div class="tab-pane <?php echo $activeClassArr['service_manage'] ?>" id="service_tab">
						<div class="portlet-body" id="serviceManage">
							<div class="table-container">
                                <div class="mb10">
                                    <select id="servicenodeSelect" class="width364 form-control nodeselect inline-block"></select>
                                    <a class="popovers ml12" data-container="body" data-trigger="hover"
                                       data-placement="right" data-content="<?php echo $LANG['UI_SETTINGS_SERVICE_SELECT_NODE_TIPS'] ?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                                <div class="vin_toolbar" id="vin_service_toolbar">
                                    <div class="leftTool">
                                    </div>
                                    <div class="rightTool">
                                        <div class="vin_btnToolbar">
                                        </div>
                                    </div>
                                </div>
								<table id="serviceTable"></table>

								<div class="alert alert-block alert-info fade in"  id="marktips">
									<button type="button" class="close" data-dismiss="alert"></button>
									<ul class="alert-ul">
										<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
										<li>
											<?php echo $LANG['UI_SETTINGS_SERVICE_LIST_MARK_TIPS'] ?>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>

					<div class="tab-pane <?php echo $activeClassArr['network_tool'] ?>" id="telnet_tab">
						<div class="portlet-body" id="networkTool">
							<form action="#" id="toolform" class="form-horizontal">
								<div class="form-body">
									<div class="row">
										<div class="col-md-5 mb15">
											<select id="toolnodeSelect" class="width100p form-control nodeselect"></select>
										</div>
										<div class="col-md-1 mt10">
											<a class="popovers" data-container="body" data-trigger="hover"
												data-placement="right" data-content="<?php echo $LANG['UI_SETTINGS_NETWORK_TOOL_SELECT_NODE_TIPS'] ?>">
												<i class="fa fa-info-circle fa-lg"></i>
											</a>
										</div>
									</div>
									<div class="row">
										<div class="col-md-2">
											<select id="toolSelect" class="width100p">
												<option value="ping">Ping</option>
												<!--<option value="ping6">Ping-V6</option>-->
												<option value="telnet">Telnet</option>
												<!--<option value="telnet6">Telnet-V6</option>-->
											</select>
										</div>
										<div class="col-md-3 form-test">
											<div class="input-icon right">
												<i class="fa"></i>
												<input style="display:none"><!-- for disable autocomplete on chrome -->
												<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_SETTINGS_NETWORK_TOOL_IP_TIPS'] ?>" class="form-control" id="testIP" name="toolip" style="height: 36px"/>
											</div>
										</div>
										<div class="col-md-2 form-test portDiv display-none">
											<div class="input-icon right">
												<i class="fa"></i>
												<input style="display:none"><!-- for disable autocomplete on chrome -->
												<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_SETTINGS_NETWORK_TOOL_PORT_TIPS'] ?>" class="form-control" id="testport" name="toolport"/ onkeyup="this.value=this.value.replace(/\D/g,'')"  onafterpaste="this.value=this.value.replace(/\D/g,'')">
											</div>
										</div>
										<div class="col-md-4">
											<button type="button" id="testTool" class="btn green-haze">
												 <?php echo $LANG['UI_SETTINGS_NETWORK_TOOL_TEST'] ?>
											</button>
										</div>
									</div>

								   </div>
							   </form>
							<div class="alert alert-block alert-info fade in mt15">
								<button type="button" class="close" data-dismiss="alert"></button>
								<ul class="alert-ul">
									<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
									<li id="pingContent">
										<?php echo $LANG['UI_SETTINGS_NETWORK_TOOL_PING_MARK_TIPS'] ?>
									</li>
									<li id="telnetContent" class="display-none">
										<?php echo $LANG['UI_SETTINGS_NETWORK_TOOL_TELNET_MARK_TIPS'] ?>
									</li>
								</ul>
							</div>


						</div>
					</div>

					<div class="tab-pane ssh_control <?php echo $activeClassArr['remote_control'] ?>" id="ssh_tab">
						<div class="ssh_p">
							<div class="portlet-title">
								<div class="caption_title">
									<i class="fa fa-television"></i><?php echo $LANG['UI_SETTINGS_REMOTE_SSH'] ?>
								</div>
							</div>
							<hr/>
							<div class="portlet-body" id="">
								<button type="button" class="btn green-haze" id ="WebSSH"><?php echo $LANG['UI_SETTINGS_REMOTE_SSH'] ?></button>
							</div>
							<div class="alert alert-block alert-info fade in mt15">
								<button type="button" class="close" data-dismiss="alert"></button>
								<ul class="alert-ul">
									<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
									<li>
										<?php echo $LANG['UI_SETTINGS_SSH_HINT'] ?>
									</li>
								</ul>
							</div>

						</div>
						<div class="ssh_p" style="margin-top: 10px;">
							<div class="portlet-title">
								<div class="caption_title">
									<i class="fa fa-upload"></i><?php echo $LANG['UI_SETTINGS_UPLOAD_TO'] ?>
								</div>
							</div>
							<hr/>
							<div class="portlet-body" id="">
								<div id="uploader" class="wu-example uploadfile">
										<!--用来存放文件信息-->
										<div class="btns">
											<div class="row">
												<div class="col-md-4">
													<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_PLATFORM_SETTING_CHOOSE_FILE_UPLOAD'] ?>" class="form-control" id="input_filename" name=""  disabled="disabled"><i class="fa fa-times" id="bnt_del_name"></i>
												</div>
												<div class="col-md-1"><div id="picker"><?php echo $LANG['UIS_SETTINGS_UPLOAD_SELECT_FILE'] ?></div></div>
												<div class="col-md-1"><button id="ctlBtn" class="btn btn-sm green-haze"><?php echo $LANG['UIS_SETTINGS_UPLOAD_START'] ?></button></div>
												</div>
												<div class="hint_content">
													<div class="row">
														<div class="col-md-4 state_top"><span id="state_upload"><?php echo $LANG['UIS_SETTINGS_UPLOAD_WAITING'] ?></span></div>
														<div class="col-md-8"></div>
													</div>
													<div class="row">
														 <div class="col-md-4">
															<div class="progress progress-striped active">
															<div class="progress-bar progress-bar-success" id="uploadProgress" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">
															</div>
														</div>
														 </div>
														<div class="col-md-8"></div>
													</div>
												</div>
											</div>
									<div id="thelist" class="uploader-list"></div>
								</div>
							</div>
							<div class="alert alert-block alert-info fade in mt15">
								<button type="button" class="close" data-dismiss="alert"></button>
								<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
								<ol class = "alert-ol">
									<li id="address_file">
										<?php echo $LANG['UIS_SETTINGS_UPLOAD_HINT_1']; ?><?php echo $CONF['UPLOAD_DIR']; ?>
									</li>
									<li>
										<?php echo $LANG['UIS_SETTINGS_UPLOAD_HINT_2'] ?>
									</li>
									<li>
										<?php echo $LANG['UIS_SETTINGS_UPLOAD_HINT_3'] ?>
									</li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./assets/global/plugins/web-uploader/webuploader.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_service.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
