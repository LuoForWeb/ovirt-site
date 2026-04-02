<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
			<span><?php echo $LANG['UI_PLATFORM_INFRASTRUCTURE'] ?></span>
		</a>
	</li>
	<span>></span>
	<li>
		<a class="ajaxify" name="infrastructure" href="./content/platform/storage/storage_lanfree.php">
			<?php echo $LANG['UI_STORAGE_LANFREE_SETTING'] ?>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_STORAGE_LANFREE_ADD'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
	<div class="col-md-12 col-manager">
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="addcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-ge_modify"></i><?php echo $LANG['UI_STORAGE_LANFREE_ADD'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<div class="form-horizontal">
					<div class="form-body">
						<form id="addnodeform">
						<div class="form-group pt50">
							<label class="control-label col-md-3"><?php echo $LANG['UI_NODE_IPADDR'] ?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" name="nodeselect">
								</select>
								<div><span class="help-block ">
									<?php echo $LANG['UI_STORAGE_ADD_NODE_TIPS'] ?>
								</span></div>
							</div>
						</div>
						
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_TYPE'] ?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" name="storagetype">
									<option value="0"></option>
									<option value="4"><?php echo $LANG['UI_STORAGE_TYPE4'] ?></option>
									<option value="5">iSCSI</option>
									<option value="6"><?php echo $LANG['UI_STORAGE_TYPE6'] ?></option>
								</select>
								<div><span class="help-block ">
									<?php echo $LANG['UI_STORAGE_TYPE_SELECT_TIPS'] ?>
								</span></div>
							</div>
						</div>
						</form>
						
						<div class="form-group display-none wwndiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_FIBER_CHANNEL'] ?>
							</label>
							<div class="col-md-8">
								<div class="table-container">
									<table class="table" id="wwntable">
									</table>
								</div>
								<div><span class="help-block ">
									<?php echo $LANG['UI_STORAGE_FC_TIPS'] ?>
								</span></div>
							</div>
						</div>
						
						<!-- 1-4 STORAGE DIV START -->
						<div id="morestoragediv" class="display-none asdiv">
						  <form>
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_SELECT_RES'] ?><span class="required">
								* </span>
								</label>
								<div class="col-md-8">
									<div class="table-container">
										<table class="table" id="resourcetable">

										</table>
										<div id="selecttips"><span class="help-block ">
											<?php echo $LANG['UI_STORAGE_LANFREE_SELECT_TIPS'] ?>
										</span></div>
									</div>
								</div>
							</div>
						  </form>
						</div>
						<!-- 1-4 STORAGE DIV END -->
						
						<!-- ISCSI DIV START -->
						<form id="iscsidiv"  class="display-none asdiv">
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ISCSI_NAME'] ?><span class="required">
								* </span>
								</label>
								<div class="col-md-4 margin10" id="iscsiname">
								</div>
							</div>
							
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ISCSI_HOST'] ?><span class="required">
								* </span>
								</label>
								<div class="col-md-3">
									<div class="input-icon right">
										<i class="fa"></i>
										<input type="text" maxlength="15" class="form-control" name="iscsiip" placeholder="192.168.1.10"/>
										<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_ISCSI_IP_TIPS'] ?>
										<?php echo $LANG['UI_STORAGE_ISCSI_IP_TIPS1'] ?><a id="moreiscsi"><?php echo $LANG['UI_STORAGE_ISCSI_IP_TIPS2'] ?></a>
										</span></div>
									</div>
								</div>
								<div class="col-md-1">
									<div class="input-icon right">
										<i class="fa"></i>
										<input type="text" maxlength="5" class="form-control" value='3260' name="iscsiport" placeholder="3260"/>
										<div><span class="help-block ">
										<?php echo $LANG['UI_SETTINGS_NOTICE_PORT'] ?>
										</span></div>
									</div>
								</div>
							</div>
							
							<div class="form-group">
								<div class="col-md-offset-3 col-md-4">
									<button type="button" class="btn btn-default textalignr" id="iscsiscan"><?php echo $LANG['UI_STORAGE_ISCSI_SCAN_TARGET'] ?></button>
								</div>
							</div>
							
							<div class="form-group target">
								<label class="control-label col-md-3"><?php echo 'Target LUN' ?><span class="required">
								* </span>
								</label>
								<div class="col-md-8">
									<div class="table-container">
										<table class="table table-striped table-bordered table-hover" id="targetluntable">
											<ul id="permissionTree" class="ztree bd1de5  tree_div ztree-fa"></ul>
										</table>
									</div>
									<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_LANFREE_SELECT_TIPS'] ?>
									</span></div>
								</div>
							</div>
						
						</form>
						<!-- ISCSI DIV END -->
						
						<!-- NFS DIV START -->
						<form id="nfsdiv"  class="display-none asdiv">
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_SHARED_FOLDERS'] ?><span class="required">
								* </span>
								</label>
								<div class="col-md-4">
									<div class="input-icon right">
										<i class="fa"></i>
										<input style="display:none"><!-- for disable autocomplete on chrome -->
										<input type="text" maxlength="1280" class="form-control" name="host" placeholder="192.168.1.10:/path/directory"/>
										<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_NFS_TIPS'] ?> <a id="morenfsparams"><?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS'] ?></a>
										</span></div>
									</div>
								</div>
							</div>
							
							<div class="form-group nfsConfigDiv display-hide">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_MOUNT_PARAMS'] ?>
								</label>
								<div class="col-md-4">
									<div class="input-icon right">
										<i class="fa"></i>
										<input style="display:none"><!-- for disable autocomplete on chrome -->
										<input type="text" maxlength="1280" class="form-control" id="nfsConfig" placeholder="vers=3,xxx=xxx"/>
										<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS_TIPS'] ?>
										</span></div>
									</div>
								</div>
							</div>
						</form>
						<!-- NFS DIV END -->
						
						<!-- CIFS DIV START -->
						<form id="cifsdiv"  class="display-none asdiv">
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_SHARED_FOLDERS'] ?><span class="required">
								* </span>
								</label>
								<div class="col-md-4">
									<div class="input-icon right">
										<i class="fa"></i>
										<input style="display:none"><!-- for disable autocomplete on chrome -->
										<input type="text" maxlength="1280" class="form-control" name="host"  placeholder="//192.168.1.10/path/directory"/>
										<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_CIFS_TIPS'] ?> <a id="morecifsparams"><?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS'] ?></a>
										</span></div>
									</div>
								</div>
							</div>
							<div class="form-group cifsConfigDiv display-hide">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_MOUNT_PARAMS'] ?>
								</label>
								<div class="col-md-4">
									<div class="input-icon right">
										<i class="fa"></i>
										<input style="display:none"><!-- for disable autocomplete on chrome -->
										<input type="text" maxlength="1280" class="form-control" id="cifsConfig" placeholder="vers=3,xxx=xxx"/>
										<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS_TIPS'] ?>
										</span></div>
									</div>
								</div>
							</div>
							
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_USERNAME'] ?>
								</label>
								<div class="col-md-4">
									<div class="input-icon right">
										<i class="fa"></i>
										<input style="display:none"><!-- for disable autocomplete on chrome -->
										<input type="text" maxlength="128" class="form-control" name="username"/>
										<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_CIFS_USER'] ?>
										</span></div>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
								</label>
								<div class="col-md-4">
									<div class="input-icon right">
										<i class="fa"></i>
										<input style="display:none"><!-- for disable autocomplete on chrome -->
										<input type="password" autocomplete="off" maxlength="128" class="form-control" name="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
										<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_CIFS_PASS'] ?>
										</span></div>
									</div>
								</div>
							</div>
						</form>
						<!-- CIFS DIV END -->
						
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_VCENTER_RNAME'] ?>&nbsp;&nbsp;
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="64" class="form-control" name="rname"/>
									<div><span class="help-block "><?php echo $LANG['UI_STORAGE_RNAME'] ?></span></div>
								</div>
							</div>
						</div>
						
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-6 col-md-6">
								<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
								<button type="button" id="addsubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
							</div>
						</div>
					</div>
				</div>
				<!-- END FORM-->
			</div>
		</div>
		<!-- END VALIDATION STATES-->

		<!-- BEGIN MODAL -->
		<div id="modal-iscsi-chap-div" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<h4 class="modal-title"><i class="glyphicon glyphicon-plus"></i> <?php echo $LANG['UI_ISCSI_CHAT_AUTH'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label col-md-3">
						<?php echo $LANG['UI_LOGIN_USERNAME'] ?>
						<span class="required">*</span>
					</label>
					<div class="col-md-6">
						<div class="input-icon right">
							<i class="fa"></i>
							<input style="display:none"><!-- for disable autocomplete on chrome -->
							<input type="text" maxlength="128" class="form-control" id="iscsi-chap-username"/>
							<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_REMOTE_ADMIN_USER_NAME'] ?>
										</span></div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">
						<?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
						<span class="required">*</span>
					</label>
					<div class="col-md-6">
						<div class="input-icon right">
							<i class="fa"></i>
							<input style="display:none"><!-- for disable autocomplete on chrome -->
							<input type="password" autocomplete="off" maxlength="128" class="form-control" id="iscsi-chap-userpwd" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
							<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_REMOTE_ADMIN_PASSWORD'] ?>
										</span></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="iscsi-chap-submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END MODAL -->

	</div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./scripts/platform/storage/storage_lanfree_add.js" type="text/javascript"></script>
	