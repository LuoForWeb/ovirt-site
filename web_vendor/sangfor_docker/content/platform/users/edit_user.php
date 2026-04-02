<?php include_once '../../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_USER_EDIT_USER']?> <small><?php echo $LANG['UI_USER_EDIT_USER_DESCRIPTION']?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	   <input id="useruuid" value="<?php  echo $_GET['useruuid'];?>" class="display-none"></input>
	   <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="adduserContent">
			<div class="portlet-title">
				<div class="caption">
					<i class="icon-user-following"></i><?php echo $LANG['UI_PLATFORM_EDIT_USER']?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="form_sample_2" class="form-horizontal">
					<div class="form-body">
					    <h4 class="form-section"><?php echo $LANG['UI_USER_BASE_INFO']?></h4>
						<div class="alert alert-danger display-hide">
							<button class="close" data-close="alert"></button>
							<?php echo $LANG['UI_USER_BASE_INFO_TIPS']?>
						</div>
						<div class="form-group" style="margin-top: 15px;">
							<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_USERNAME']?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="32"  class="form-control" id="name" name="name"/>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_PASSWORD']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="password" maxlength="32" class="form-control" id="password" name="password"/>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_USER_CONFIRM']?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="password" maxlength="32"  class="form-control" name="rpassword"/>
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
						    <label class="control-label col-md-3"><?php echo $LANG['UI_USER_BACKUP_STORAGE_CAPACITY']?>
						        <span class="required">*</span>
						    </label>
						    <div class="col-md-3">
							    <select id="storageMode" class="form-control">
							        <option value="1"><?php echo $LANG['UI_USER_UNLIMITED_CAPACITY']?></option>
							        <option value="2"><?php echo $LANG['UI_USER_CUSTOM_CAPACITY']?></option>
							    </select>
							    <div id="custom" style="margin-top: 15px; display:inline-flex;">
								    <div class="input-icon" >
										<div id="spinnerNum" style="width: 140px;">
				                        	<div class="input-group" style="width:140px;">
					                        	<input type="text" id="spinnerNumInput" style="text-align: center;" class="spinner-input form-control" maxlength="3">
					                        	<div class="spinner-buttons input-group-btn">
					                        		<button type="button" class="btn spinner-up default">
					                        			<i class="fa fa-angle-up"></i>
					                        		</button>
					                        		<button type="button" class="btn spinner-down default">
					                        			<i class="fa fa-angle-down"></i>
					                        		</button>
					                        	</div>
				                        	</div>
			                        	</div>
									</div>
									<div class="">
										<select id="unit" style="width: 45px; margin-left: 10px;height: 33px;text-align:center;">
										    <option value="1">MB</option>
										    <option value="2">GB</option>
										    <option value="3">TB</option>
									    </select>
								    </div>
								    <div class="maxNum">
								    	<span id="maxNum" style="color: #737373;"><?php echo $LANG['UI_USER_TOTAL_STORAGE']?></span>
						    	    </div>
								</div>
						    </div>
						    <div class="col-md-1" style="margin-top: 5px;">
						     	<a class="popovers" data-container="body" data-trigger="hover" 
                                   data-placement="right" data-content="<?php echo $LANG['UI_USER_QUOTA']?>">
        						    <i class="fa fa-info-circle fa-lg"></i>
        						</a>
						    </div>
						    	
						</div>
						
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_USER_TYPE']?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="usertype" name="usertype">
									<option value="operator"><?php echo $LANG['UI_USER_OPERATOR']?></option>
									<option value="manager"><?php echo $LANG['UI_USER_MANAGERS']?></option>
									<option value="auditor"><?php echo $LANG['UI_USER_AUDIT']?></option>
								</select>
							</div>
						</div>
						
						<div id="user-auth" >
						<h4 class="form-section"><?php echo $LANG['UI_USER_PERMISSION']?></h4>
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_USER_PERMISSION_TYPE']?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="radio-list" data-error-container="#form_2_membership_error">
									<label class="radio-inline">
									<input type="radio" name="auth_type"  value="1"/>
									<?php echo $LANG['UI_USER_DEFAULT_PMS']?> </label>
									<label class="radio-inline">
									<input type="radio" name="auth_type" checked value="2"/>
									<?php echo $LANG['UI_USER_HIGH_SETTING']?> </label>
								</div>
								</div>
							</div>
						</div>
						
						<div class="form-group  auth">
							<label class="control-label col-md-3"><?php echo $LANG['UI_USER_PERMISSION_SETTING']?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="min-height250 mt10">
									<ul id="permissionTree" class="ztree bd1de5  tree_div ztree-fa"></ul>
								</div>
							</div>
						</div>
						
						
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-3 col-md-4">
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
<?php 
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' . 
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script src="./scripts/platform/users/edit_user.js" type="text/javascript"></script>
	