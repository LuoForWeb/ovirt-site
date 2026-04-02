<?php include_once '../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- BEGIN PAGE HEADER-->

<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	  <div class="portlet box blue-hoki" id='authdiv'>
		<div class="portlet-title">
			<div class="caption">
				<i class="levelchild viconfont vicon-billing_manager"></i><?php echo $LANG['BILLING_TABLE'] ?>
			</div>
			
		</div>

		<div class="portlet-body mlr10">
			<div class="table-toolbar-wrapper">
				<div class="table-toolbar-wrapper__left">
					<div class="btn-group">
						<button type="button" id="newBut" class="btn table-toolbar-btn">
						<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD'] ?>
						</button>
					</div>
					<div class="btn-group">
						<button type="button" id="modifyBut" class="btn table-toolbar-btn">
						<i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_PUBLIC_MODIFY'] ?>
						</button>
					</div>
						<div class="btn-group">
						<button type="button" id="deleteBut" class="btn table-toolbar-btn">
						<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
						</button>
					</div>
					<div class="btn-group">
						<button type="button" id="unlockBut" class="btn table-toolbar-btn">
						<i class="viconfont vicon-ge_unlock"></i> <?php echo $LANG['UI_TENANT_BUTTON_ENABLE'] ?>
						</button>
					</div>
					<div class="btn-group">
						<button type="button" id="lockBut" class="btn table-toolbar-btn">
						<i class="viconfont vicon-ge_lock"></i> <?php echo $LANG['UI_TENANT_BUTTON_DISABLE'] ?>
						</button>
					</div>
				</div>
			</div>
			<div class="table-container" >
				<table class="table table-striped table-hover" id="billing_table">
					<thead>
						<tr role="row" class="heading">
							<th width="2%">
								<input type="checkbox" class="group-checkable">		
							</th>
							<th width="15%">
							<?php echo $LANG['BILLING_TABLE_NAME'] ?>
							</th>
							<th width="15%">
							<?php echo $LANG['BILLING_TABLE_DES'] ?>
							</th>
							<th width="10%">
							<?php echo $LANG['BILLING_TABLE_UNIT'] ?>
							</th>
							<th width="10%">
							<?php echo $LANG['BILLING_TABLE_TYPE'] ?>
							</th>
							<th width="10%">
							<?php echo $LANG['BILLING_TABLE_CREATE_TIME'] ?>
							</th>
							<th width="20%">
							<?php echo $LANG['BILLING_TABLE_UNIT_COST'] ?>
							</th>
							<th width="10%">
							<?php echo $LANG['BILLING_TABLE_INFORM'] ?>
							</th>
							<th width="10%">
							<?php echo $LANG['BILLING_TABLE_INFORM_PERIOD'] ?>
							</th>
							<th width="10%">
							<?php echo $LANG['BILLING_STATE'] ?>
							</th>
						</tr>
					</thead>
				</table>
				<div class="alert alert-block alert-info fade in mt-10" id="marktips">
					<button type="button" class="close" data-dismiss="alert"></button>
					<ul class="alert-ul">
						<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
						<li>
							<?php echo $LANG['UI_PLATFORM_ASSIGN_TENANT_POLICI_TIPS'] ?>
						</li>
					</ul>
				</div>
				
				
			</div>
		
		</div>
	   </div> 
	</div>
</div>


			
			
			
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/billing/billing_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	