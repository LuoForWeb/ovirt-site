<?php include_once '../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
	<a style="color: #20c99a;" class="ajaxify" name="billing_manager" href="./content/platform/billing/billing_manager.php">
		<i class="iconfont icon-feiyong" ></i>
		<?php echo $LANG['BILLING_MANAGER'] ?> -
	</a>
	<span style="color: #575962;"><?php echo $LANG['BILLING_DETAILS_TAC'] ?></span>
</h3>

<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<input value="<?php echo $_GET['billing_uuid']; ?>" id="billing_uuid" style="display: none;">
	<div class="col-md-12">
	  <div class="portlet box blue-hoki" id='authdiv'>
		<div class="portlet-title">
			<div class="caption" id="billing_name">
				<!-- 策略名字 -->
			</div>
			
		</div>

		<div class="portlet-body">
			<div class="table-toolbar">
				<div class="row">
					<div class="col-md-12">
						<div class="btn-group">
							<button type="button" id="allocation_tenant" class="btn btn-sm green-haze">
							<i class="viconfont vicon-ge_add_task"></i><?php echo $LANG['BILLING_ALLOCATION_TENANT'] ?>
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="billing_total">
				
			</div>
			<div class="table-container" style="margin-top: 20px;">
				<table class="table table-striped table-bordered table-hover lh2" id="billing_details_table">
					<thead>
						<tr role="row" class="heading">
							<th width="2%">
								<input type="checkbox" class="group-checkable">		
							</th>
							<th width="20%">
								<?php echo $LANG['UI_TENANT_NAME'] ?>	
							</th>
							<th width="20%" id="use_type">
								--
							</th>
							<th width="10%">
								<?php echo $LANG['BILLING_START_TIME'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['BILLING_END_TIME'] ?>		
							</th>
							<th width="10%">
								<?php echo $LANG['BILLING_TOTAL'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_PUBLIC_OPERATION'] ?>		
							</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	   </div> 
	</div>
</div>

<!-- modal start allocation_tenant_modal-->          
<div id="allocation_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title">
			<?php echo $LANG['BILLING_ALLOCATION_TENANT'] ?>
		</h4>
	</div>
	<form action="#" id="form_add_tenant" class="form-horizontal">
	<div class="modal-body">
		<div class="form-group billing-width">
			<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_TENANT'] ?><span class="required" aria-required="true">
				*</span>
			</label>
			<div class="col-md-9">
				<div class="input-icon right">
					<select id="tenant_list" name="tenant_list" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true">
					</select>
					<div><span class="help-block ">
					<?php echo $LANG['BILLING_ALLOCATION_TENANT_INFO'] ?>
					</span></div>
				</div>
			</div>		
		</div>
		<div class="form-group billing-width" id="display_capacity" style="display:none;">
			<label class="control-label col-md-3"><?php echo $LANG['BILLING_CAPACITY'] ?><span class="required" aria-required="true">
				*</span>
			</label>
			<div class="col-md-9">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxlength="128" class="form-control" name="num_capacity" id="num_capacity" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"/>
					<span>GB</span>
					<div><span class="help-block ">
					<?php echo $LANG['BILLING_ALLOCATION_CAPACITY_INFO'] ?>
					</span></div>
				</div>
			</div>		
		</div>
		
		<div class="form-group billing-width" id="display_vm" style="display:none;">
			<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_VIRTUAL_MACHINE_HOST'] ?>
			</label>
			<div class="col-md-9">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxlength="128" class="form-control" name="num_vm" id="num_vm" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"/>
					<span></span>
					<div><span class="help-block ">
					<?php echo $LANG['BILLING_ALLOCATION_VM_INFO'] ?>
					</span></div>
				</div>
			</div>		
		</div>
		<div class="form-group billing-width" id="display_fs" style="display:none;">
			<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_FILE_AGENT'] ?>
			</label>
			<div class="col-md-9">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxlength="128" class="form-control" name="num_fs" id="num_fs" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"/>
					<span></span>
					<div><span class="help-block ">
					<?php echo $LANG['BILLING_ALLOCATION_FS_INFO'] ?>
					</span></div>
				</div>
			</div>		
		</div>
		<div class="form-group billing-width" id="display_db" style="display:none;">
			<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_DATABASE_HOST'] ?>
			</label>
			<div class="col-md-9">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxlength="128" class="form-control" name="num_db" id="num_db" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"/>
					<span></span>
					<div><span class="help-block ">
					<?php echo $LANG['BILLING_ALLOCATION_DB_INFO'] ?>
					</span></div>
				</div>
			</div>		
		</div>
		
		
	</div>
	</form>
	<div class="modal-footer">
		<div class="form-group" id="display_time" style="display:none;">
			<label class="control-label col-md-2"><?php echo $LANG['BILLING_BUY_TIME'] ?><span class="required" aria-required="true">
				</span>
			</label>
			<div class="col-md-4">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxlength="128" class="form-control" name="billing_time" id="billing_time" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"/>
				</div>
			</div>
			<label class="control-label col-md-1" id="time_year"><?php echo $LANG['WEB_UTILS_MONTH'] ?>
			</label>
		</div>
		
		
		<div id="display_total" style="display:none;">
			<label class="control-label col-md-2"><?php echo $LANG['BILLING_UNIT_COST'] ?><span class="required" aria-required="true">
				</span>
			</label>
			<div class="col-md-3" style="padding: 0;">
				<p id="string_text"></p>
			</div>
			<label class="control-label col-md-2"><?php echo $LANG['BILLING_TOTAL'] ?><span class="required" aria-required="true">
				</span>
			</label>
			<div class="col-md-3">
				<p id="total_billing"></p>
			</div>
					
		</div>
	
		<button type="button" data-dismiss="modal" class="btn btn-default" id="consel_submit_allocation"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary" id="submit_allocation"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>	           
		
<div id="update_billing_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title">
			<?php echo $LANG['BILLING_RENEW'] ?>
		</h4>
	</div>
	<div class="modal-body">
		
		<div class="form-group billing-width">
			<label class="control-label col-md-3"><?php echo $LANG['BILLING_START_TIME'] ?><span class="required" aria-required="true">
				</span>
			</label>
			<div class="col-md-9">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxlength="128" class="form-control" name="description" id="start_time_input" disabled="disabled"/>
				</div>
			</div>		
		</div>
		<div class="form-group billing-width">
			<label class="control-label col-md-3"><?php echo $LANG['BILLING_END_TIME'] ?>:<span class="required" aria-required="true">
				</span>
			</label>
			<div class="col-md-9">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxlength="128" class="form-control" name="description" id="end_time_input" disabled="disabled"/>
				</div>
				</div>
		</div>
		<div class="form-group billing-width">
			<label class="control-label col-md-3"><?php echo $LANG['BILLING_RENEW'] ?>:<span class="required" aria-required="true">
				</span>
			</label>
			<div class="col-md-9">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxlength="128" class="form-control" name="description" id="update_time_input" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"/>
					<span id="time_update_unit"><?php echo $LANG['BILLING_MONTH'] ?></span>
				</div>
			</div>		
		</div>
		<div class="form-group billing-width">
			<label class="control-label col-md-3"><?php echo $LANG['BILLING_END_TIME_UPDATE'] ?>:<span class="required" aria-required="true">
				</span>
			</label>
			<div class="col-md-9">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxlength="128" class="form-control" name="description" id="update_new_time_input" disabled="disabled"/>
					<input type="text" maxlength="128" class="form-control" name="description" id="update_new_total_input" disabled="disabled" style="display:none;"/>
				</div>
			</div>		
		</div>
		
		
		
		
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default" id="concel_update_billing"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary" id="submit_update_billing"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
	</div>	
</div>           



			
			
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/billing/details_billing.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	