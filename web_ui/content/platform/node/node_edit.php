<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_NODE_EDIT']?> <small><?php echo $LANG['UI_NODE_EDIT_INFO']?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	   <input id="uuid" value="<?php  echo $_GET['uuid'];?>" class="display-none"></input>
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="addcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i><?php echo $LANG['UI_NODE_EDIT_INFO']?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="addnodeform" class="form-horizontal">
					<div class="form-body">
						<div class="form-group pt50">
							<label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_NODE_IPADDR']?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="ipaddr"/>
									<div><span class="help-block "><?php echo $LANG['UI_NODE_EDIT_ADDR_TIPS']?></span></div>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_NODE_SSH_PORT']?>
							</label>
							<div class="col-md-9">
								<div id="spinnerNum">
									<div class="input-group" style="width:150px;">
										<input type="text" id="spinnerNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" name="port" class="spinner-input form-control" maxlength="5">
										<div class="spinner-buttons input-group-btn">
											<button type="button" class="btn spinner-up default">
											<i class="fa fa-angle-up"></i>
											</button>
											<button type="button" class="btn spinner-down default">
											<i class="fa fa-angle-down"></i>
											</button>
										</div>
									</div>
									<div><span class="help-block "><?php echo $LANG['UI_NODE_SSH_PORT_TIPS']?></span></div>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_LOGIN_USERNAME']?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input type="text" maxlength="128" class="form-control" name="username"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_NODE_SSH_USER_TIPS']?>
									</span></div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_LOGIN_PASSWORD']?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input type="password" autocomplete="off" maxlength="128" class="form-control" name="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_NODE_SSH_PASS_TIPS']?>
									</span></div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_VCENTER_RNAME']?>&nbsp;&nbsp;
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="64" class="form-control" name="rname"/>
									<div><span class="help-block "><?php echo $LANG['UI_NODE_RNAME']?></span></div>
								</div>
							</div>
						</div>
						
					</div>
					<div class="form-actions pt50">
						<div class="row">
							<div class="col-md-offset-3 col-md-4">
								<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
								<button type="button" id="addsubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
							</div>
						</div>
					</div>
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<!-- END VALIDATION STATES-->
	</div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php 
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' . 
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./scripts/platform/node/node_edit.js" type="text/javascript"></script>
	