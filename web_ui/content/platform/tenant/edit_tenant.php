<?php include_once '../../../tpl/permission.php';?>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title settings-title">
	<a style="color: #20c99a;" class="ajaxify" name="tenant_manager" href="./content/platform/tenant/tenant_manager.php">
		<i class="iconfont icon-zuhu" ></i>
		<?php echo $LANG['UI_PLATFORM_TENANT']?> -
	</a>
	<span style="color: #575962;"><?php echo $LANG['UI_PUBLIC_MODIFY']?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<input id="tenant_uuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
<div class="row">
	<div class="col-md-12">
	<!-- BEGIN VALIDATION STATES-->
	<div class="portlet box blue-hoki" id="addtenantContent">
	<div class="portlet-title">
		<div class="caption">
			<i class="viconfont vicon-ge_modify"></i><?php echo $LANG['UI_TENANT_MODIFY_TIPS']?>
		</div>
	</div>
	<div class="portlet-body form">
	<!-- BEGIN FORM-->
	<form action="#" id="form_addTenant" class="form-horizontal">
		<div class="form-body">
	
        	<div class="form-group pt70" >
    			<label class="control-label col-md-3"><?php echo $LANG['UI_TENANT_NAME']?><span class="required">
    			* </span>
    			</label>
    			<div class="col-md-4">
    				<div class="input-icon right">
    					<i class="fa"></i>
    					<input type="text" maxlength="128" class="form-control" name="name" id="tenantname"/>
    					<div><span class="help-block ">
    					<?php echo $LANG['UI_TENANT_INPUT_RULE']?>
    					</span></div>
    				</div>
        		</div>
        	</div>
        	<div class="form-group">
    			<label class="control-label col-md-3"><?php echo $LANG['UI_TENANT_FULL_NAME']?><span class="required">
    			* </span>
    			</label>
    			<div class="col-md-4">
    				<div class="input-icon right">
    					<i class="fa"></i>
    					<input type="text" maxlength="128" class="form-control" name="nickname" id="nickname"/>
    					<div><span class="help-block ">
    					<?php echo $LANG['UI_TENANT_FULL_NAME_TIPS']?>
    					</span></div>
        			</div>
            	</div>
        	</div>
        	<div class="form-group" >
    			<label class="control-label col-md-3"><?php echo $LANG['UI_TENANT_ADMIN_ACCOUNT']?><span class="required">
    			* </span>
    			</label>
    			<div class="col-md-4">
    				<div class="input-icon right">
    					<i class="fa"></i>
    					<input disabled type="text" maxlength="128" class="form-control" name="username" id="adminname"/>
    					<div><span class="help-block ">
    					<?php echo $LANG['UI_TENANT_ADMIN_ACCOUNT_TIPS']?>	
    					</span></div>
        			</div>
            	</div>
        	</div>
        	<div class="form-group">
    			<label class="control-label col-md-3"><?php echo $LANG['UI_TENANT_ADMIN_PASSWORD']?><span class="required">
    			* </span>
    			</label>
    			<div class="col-md-4">
    				<div class="input-icon right">
    					<i class="fa"></i>
    					<input type="password" autocomplete="off" maxlength="128" class="form-control" name="password" id="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
    					<div><span class="help-block ">
    					<?php echo $LANG['UI_TENANT_ADMIN_PASSWORD_TIPS']?>
    					</span></div>
        			</div>
            	</div>
        	</div>
        	<div class="form-group">
    			<label class="control-label col-md-3"><?php echo $LANG['UI_TENANT_ADMIN_CONFIRM_PASSWORD']?><span class="required">
    			* </span>
    			</label>
    			<div class="col-md-4">
    				<div class="input-icon right">
    					<i class="fa"></i>
    					<input type="password" autocomplete="off" maxlength="128" class="form-control" name="rpassword" id="rpassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
    					<div><span class="help-block ">
    					<?php echo $LANG['UI_TENANT_ADMIN_CONFIRM_PASSWORD_TIPS']?>
    					</span></div>
        			</div>
            	</div>
        	</div>
        	<div class="form-group" >
    			<label class="control-label col-md-3"><?php echo $LANG['UI_TENANT_ADMIN_EMAIL']?>
    			</label>
    			<div class="col-md-4">
    				<div class="input-icon right">
    					<i class="fa"></i>
    					<input type="text" maxlength="128" class="form-control" name="email" id="email"/>
    					<div><span class="help-block ">
    					<?php echo $LANG['UI_TENANT_ADMIN_EMAIL_TIPS']?>
    					</span></div>
        			</div>
            	</div>
        	</div>
    	</div>
        <div class="form-actions pt50">
            <div class="row">
                <div class="col-md-offset-3 col-md-4">
                    <button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_CANCEL']?></button>
                    <button type="button" id="submitBut" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_CONFIRM']?></button>
                </div>
            </div>
        </div>
	</form>
	</div>
	</div>
	
</div>
</div>

<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php 
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' . 
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/tenant/edit_tenant.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	

