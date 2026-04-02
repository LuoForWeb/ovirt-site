<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_USER_INFO']?> <small><?php echo $LANG['UI_USER_INFO_DESCRIPTION']?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="icon-user"></i><?php echo $LANG['UI_USER_SELF_INFO']?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="userinfofform" class="form-horizontal">
					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_USERNAME']?> <span class="required">
							 </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<p class="mt8" id="uservalue"></p>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_USER_EMAIL']?> <span class="required">
							 </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="email"/>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_USER_PHONE']?> <span class="required">
							 </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="20" class="form-control" name="number"/>
								</div>
							</div>
						</div>
						<div class="form-group display-none">
							<label class="control-label col-md-3"><?php echo $LANG['UI_USER_LANG']?> <span class="required">
							 </span>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="langtype" name="langtype">
								</select>
							</div>
						</div>
						
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-7 textalignr">
								<button type="button" id="cancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
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
<script src="./assets/global/plugins/jquery-validation/js/localization/messages_zh.js" type="text/javascript"></script>
<script src="./scripts/platform/users/userinfo.js" type="text/javascript"></script>
	