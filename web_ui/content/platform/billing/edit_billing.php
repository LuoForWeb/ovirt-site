<?php include_once '../../../tpl/permission.php'; ?>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title settings-title">
	<a style="color: #20c99a;" class="ajaxify" name="billing_manager" href="./content/platform/billing/billing_manager.php">
		<i class="iconfont icon-feiyong" ></i>
		<?php echo $LANG['BILLING_MANAGER']; ?> -
	</a>
	<span style="color: #575962;"><?php echo $LANG['UI_PUBLIC_MODIFY']; ?></span>
</h3>
<style>
.billing_monetary .btn-group.bootstrap-select.show-tick.form-control.fit-width{
	height:38px;
	padding:0;
}
</style>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<input value="<?php echo $_GET['billing_uuid']; ?>" id="billing_uuid" style="display: none;">
	<div class="col-md-12">
	<!-- BEGIN VALIDATION STATES-->
	<div class="portlet box blue-hoki" id="addtenantContent">
	<div class="portlet-title">
		<div class="caption">
			<i class="viconfont vicon-ge_modify"></i><?php echo $LANG['BILLING_EDIT_BILLING']; ?>
		</div>
	</div>
	<div class="portlet-body form">
	<!-- BEGIN FORM-->
	<form action="#" id=form_add_billing class="form-horizontal">
		<div class="form-body">
	
			<div class="form-group pt70" >
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_TABLE_NAME']; ?><span class="required">
				* </span>
				</label>
				<div class="col-md-4">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="128" class="form-control" name="billing_name" id="billing_name"/>
						<div><span class="help-block ">
						<?php echo $LANG['BILLING_TABLE_NAME']; ?>
						</span></div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_TABLE_DES']; ?><span class="required">
				* </span>
				</label>
				<div class="col-md-4">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="128" class="form-control" name="description" id="billing_description"/>
						<div><span class="help-block ">
						</span></div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_TABLE_TYPE']; ?><span class="required" aria-required="true">
				* </span>
				</label>
				<div class="col-md-4">
					<div class="input-icon right">
						<i class="fa"></i>
						<select class="form-control select2me" id="billing_type" name="billing_type" disabled="disabled">
							<option value="0" style="display:none;"><?php echo $LANG['BILLING_PLEASE_SELECT']; ?></option>
							<option value="1"><?php echo $LANG['BILLING_PAY_MONTH']; ?></option>
							<option value="2"><?php echo $LANG['BILLING_PAY_YEAR']; ?></option>
							<option value="3"><?php echo $LANG['BILLING_PAY_SIZE']; ?></option>
						</select>
						<div><span class="help-block ">
						</span></div>
					</div>
				</div>
			</div>
			
			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_TABLE_UNIT']; ?><span class="required" aria-required="true">
					*</span>
				</label>
				<div class="col-md-4" id="muti-user">
					<div class="input-icon right">
						<select id="monetary_unit" name="monetary_unit" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-max-options="1" data-size="5">
						</select>
						<div><span class="help-block ">
						<?php echo $LANG['BILLING_UNIT_INFO']; ?>
						</span></div>
					</div>
				</div>		
			</div>
			
			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_MOD']; ?><span class="required" aria-required="true">
				* </span>
				</label>
				<div class="col-md-4">
					<div class="input-icon right">
						<i class="fa"></i>
						<select class="form-control select2me" id="billing_num_type" name="billing_num_type" disabled="disabled">
							<option value="0" style="display:none;"><?php echo $LANG['BILLING_PLEASE_SELECT']; ?></option>
							<option value="1"><?php echo $LANG['BILLING_PAY_CAPACITY']; ?>(GB)</option>
							<option value="2"><?php echo $LANG['BILLING_PAY_NUM']; ?></option>
						</select>
						<div><span class="help-block ">
						</span></div>
					</div>
				</div>
			</div>
			
			
			
			
			<div class="form-group" id="display_num_type" style="display:none">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_NUM_TYPE']; ?><span class="required" aria-required="true">
					*</span>
				</label>
				<div class="col-md-4" id="muti-user">
					<div class="input-icon right">
						<select id="num_selected" name="userGroupRole" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true" >
							<option value="1"><?php echo $LANG['BILLING_VM']; ?></option>
							<option value="2"><?php echo $LANG['BILLING_FS']; ?></option>
							<option value="3"><?php echo $LANG['BILLING_DB']; ?></option>
						</select>
						<div><span class="help-block ">
						<?php echo $LANG['BILLING_NUM_TYPE_INFO']; ?>
						</span></div>
					</div>
				</div>		
			</div>
			
			
			
			
			
			
			
			<div class="form-group billing_monetary" id ="display_1" style="display:none">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_VM_UNIT_COST']; ?><span class="required">
				* </span>
				</label>
				<div class="col-md-4">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="11" class="form-control" name="billing_vm" id="billing_vm" style="width: 100px;display: inline-block;"/>
						<span class="update_money"><?php echo $LANG['BILLING_TABLE_UNIT']; ?></span>
						<span>/<?php echo $LANG['WEB_PLATFORM_DC_NUM'] ?>/</span>
						<span class="update_time"><?php echo $LANG['UI_PUBLIC_TIME']; ?></span>
					</div>
				</div>
			</div>
			<div class="form-group billing_monetary" id="display_2" style="display:none">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_FS_UNIT_COST']; ?><span class="required">
				* </span>
				</label>
				<div class="col-md-4">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="11" class="form-control" name="billing_fs" id="billing_fs" style="width: 100px;display: inline-block;"/>
						<span class="update_money"><?php echo $LANG['BILLING_TABLE_UNIT']; ?></span>
						<span>/<?php echo $LANG['WEB_PLATFORM_DC_NUM'] ?>/</span>
						<span class="update_time"><?php echo $LANG['UI_PUBLIC_TIME']; ?></span>
					</div>
				</div>
			</div>
			<div class="form-group billing_monetary" id="display_3" style="display:none">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_DB_UNIT_COST']; ?><span class="required">
				* </span>
				</label>
				<div class="col-md-4">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="11" class="form-control" name="billing_db" id="billing_db" style="width: 100px;display: inline-block;"/>
						<span class="update_money"><?php echo $LANG['BILLING_TABLE_UNIT']; ?></span>
						<span>/<?php echo $LANG['WEB_PLATFORM_DC_NUM'] ?>/</span>
						<span class="update_time"><?php echo $LANG['UI_PUBLIC_TIME']; ?></span>
					</div>
				</div>
			</div>
			
			
			<div class="form-group billing_monetary" id="display_capacity" style="display:none">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_CAPACITY_UNIT_COST']; ?><span class="required">
				* </span>
				</label>
				<div class="col-md-4">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxlength="11" class="form-control" name="billing_capacity" id="billing_capacity" style="width: 100px;display: inline-block;"/>
						<span class="update_money"><?php echo $LANG['BILLING_TABLE_UNIT']; ?></span>
						<span>/GB/</span>
						<span class="update_time"><?php echo $LANG['UI_PUBLIC_TIME']; ?></span>
					</div>
				</div>
			</div>
			
			
			<div class="form-group">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_TABLE_INFORM']; ?>
				</label>
				<div class="col-md-4">
					<input type="checkbox" id="inform"  class="make-switch" data-on-color="primary" data-off-color="info" 
					data-on-text="<?php echo $LANG['WEB_PLATFORM_PUBLIC_YES'] ?>" 
					data-off-text="<?php echo $LANG['WEB_PLATFORM_PUBLIC_NO'] ?>">
					<div><span class="help-block ">
						<?php echo $LANG['BILLING_EMAIL_INFO']; ?>
					</span></div>
				</div>
			</div>
			
			<div class="form-group" id="display_inform" style="display: none;">
				<label class="control-label col-md-3"><?php echo $LANG['BILLING_TABLE_INFORM_PERIOD']; ?><span class="required" aria-required="true">
					*</span>
				</label>
				<div class="col-md-4" id="muti-user">
					<div class="input-icon right">
						<select class="form-control select2me" id="inform_period">
							<option value="0" style="display:none;"><?php echo $LANG['BILLING_PLEASE_SELECT']; ?></option>
							<option value="1"><?php echo $LANG['BILLING_INFORM_DAY']; ?></option>
							<option value="2"><?php echo $LANG['BILLING_INFORM_WEEK']; ?></option>
							<option value="3"><?php echo $LANG['BILLING_INFORM_MONTH']; ?></option>
							<option value="4"><?php echo $LANG['BILLING_INFORM_YEAR']; ?></option>
						</select>
						<div><span class="help-block ">
						<?php echo $LANG['BILLING_INFORM_INFO']; ?>
						</span></div>
					</div>
				</div>		
			</div>
			
	
			<div class="form-actions pt50">
				<div class="row">
					<div class="col-md-offset-3 col-md-4">
						<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
						<button type="button" id="submitBut" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
					</div>
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
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/billing/edit_billing.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	

