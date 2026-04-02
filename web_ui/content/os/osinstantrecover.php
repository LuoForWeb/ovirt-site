<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row page-content-row">
	<div class="col-md-12 page-content-col">
		<div class="portlet box blue-hoki" id="recovercontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-vminstantrecover"></i><?php echo $LANG['UI_OS_CREATE_INSTANT_RECOVERY_TASK'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" id="instantrecoverform" class="form-horizontal">
					<div class="form-body form-body__instant">
						
						<div class="form-group">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_BACKUP_POINT'] ?> 
							</label>
							
							<div class="col-md-7">
								<select class="bs-select width100p form-control"  data-show-subtext="true" id="storageselect" >
								</select>
									
								<div class="vm_tree_div " style="margin-top: 10px;">
									<div class="input-icon right">
										<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>" class="form-control" id="searchos" autocomplete="off"/>
									</div>
								</div>
							</div>
							
							<div class="col-md-7 col-md-offset-3 col-tree">
								<div class="two_tree">
									<ul id="pointtypetree" class="ztree bd1de5 tree_div ztree-fa tree-scroll os-instant-tree"></ul>
									<div><span class="help-block ">
									<?php echo $LANG['UI_OS_SELECT_RECOVERY_TIME_POINT'] ?>
									</span></div>
								</div>
								<div class="alert alert-block alert-info fade in display-hide" id="nopointtips">
									<button type="button" class="close" data-dismiss="alert"></button>
									<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
									<ol class="alert-ol">
										<li>
											<?php echo $LANG['UI_RECOVERY_NO_VM_TITLE'] ?>
										</li>
										<li>
											<a class="ajaxify alert-link" name="osbackup" href="./content/os/osbackup.php"><?php echo $LANG['WEB_OS_HOST_DATA_BACKUP_TIPS'] ?></a>
										</li>
									</ol>
								</div>
								<div class="alert alert-block alert-info fade in display-hide"  id="nosearchtips">
									<button type="button" class="close" data-dismiss="alert"></button>
									<ul class="alert-ul">
										<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
										<li>
											<strong><?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?></strong>
										</li>
									</ul>
								</div>
							</div>
						</div>
					
						<!-- 密码输入框 -->
						<div class="form-group display-none passdiv" >
							
							<label class="control-label col-md-3">
							<span class="required">* </span><?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="password" autocomplete="off" maxlength="128" class="form-control" placeholder="<?php echo $LANG['UI_LOCK_INPUT_PASSWORD'] ?>" id="encryptVal" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
								</div>
							</div>
							<div class="col-md-1 vmbackup-mt10_en mt5_cn">
									<a class="popovers" data-container="body" data-trigger="hover" 
										data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DES_DATA_ENCRYPT_TIP'] ?>">
										<i class="viconfont vicon-tishi"></i>
									</a>
							</div>
						</div>

						<!-- 选择已有的IP地址 ,多选-->
						<div class="form-group display-none ipdiv" id="selectIP" >
							<label class="control-label col-md-3"> <span class="required">* </span><?php echo $LANG['WEB_OS_GOAL_IP'] ?>
							</label>
							<div class="col-md-4">
								<select id="IPlistSelect" name="" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-max-options="1" data-size ="5" style="height: 34px;">
								</select>
							</div>
						</div>
						<!-- 存储位置 -->
						<div class="form-group display-none storagediv"> 
							
							<label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_OS_CACHE_STORAGE_LOCATION'] ?>
							</label>        
							<div class="col-md-4">
								<select class="form-control select2me" id="selectstorage">
								</select>
							</div>
							<div  class="col-md-1 vmbackup-mt10_en mt5_cn">
								<a class="popovers" data-container="body" data-trigger="hover" 
									data-placement="right" data-content="<?php echo $LANG['UI_OS_CACHE_STORAGE_LOCATION_TIPS'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>
						<!-- 传输网络 -->
						<div class="form-group display-none transfernetworkDiv">
							<label class="control-label col-md-3 transfernetworklabel"><span class="required">* </span>iSCSI target ip</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="transferNetwork">
								</select>
							</div>
							<div class="col-md-1 vmbackup-mt10_en mt5_cn">
								<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_OS_TRANSFER_NETWORK_TIPS'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>

						<div class="form-group display-none dndiv">
							<label class="control-label col-md-3"><span class="required">
							* </span><?php echo $LANG['UI_JOB_RNAME'] ?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="taskname" id="taskname"/>
									<div><span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span></div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-6 col-md-6">
								<button type="button" id="cancelbtn" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
								<button type="button" disabled id="submitbtn" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmRecoveryConfig.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfigValidate.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/os/osinstantrecover.js" type="text/javascript"></script>