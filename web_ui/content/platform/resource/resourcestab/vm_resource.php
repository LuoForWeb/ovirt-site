<?php include_once '../../../../tpl/permission.php';?>
<div class="row">
	<div class="col-md-12">
	    <div class="portlet-body" id="">
		    <div class="table-toolbar">
				<div class="row">
					<div class="col-md-8">
						<div class="btn-group">
							<button type="button" id="vm_add" class="btn btn-sm green-haze">
							<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD_OPERATION']?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="vm_delete" class="btn btn-sm green-haze">
							<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE']?>
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<select class="form-control select2me width220 floatr" id="userHypervisor">
                        </select>
					</div>
				</div>
			</div>
			
			<div class="table-container">
				<table class="table table-striped table-bordered table-hover" id="vmDatatable">
					<thead>
						<tr role="row" class="heading">
							<th width="5%">
								<input type="checkbox" class="group-checkable">
							</th>
							<th width="35%">
							   	<?php echo $LANG['UI_REPORT_VM_NAME']?>
							</th>
							<th width="20%">
							  	<?php echo $LANG['UI_VCENTER_VC']?>
							</th>
							<th width="30%">
								<?php echo $LANG['UI_BACKUP_FILE_PATH']?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_PUBLIC_STATUS']?>
							</th>
						</tr>
					</thead>
					<tbody>
					
					</tbody>
				</table>
				
			</div>
		</div>
	</div>
	
	<!-- BEGIN MODAL -->
	<div id="vmModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		<div class="modal-header ">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			<h4 class="modal-title"><?php echo $LANG['UI_RESOURCE_GROUP_ALLOCATION_VM']?></h4>
		</div>
		<div class="modal-body">
			<div class="portlet-body">
			    <div class="table-toolbar">
					<div class="row">
						<div class="col-md-6">
						    
						    <button type="button" id="vm_submit" class="btn btn-sm green-haze">
							<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD_OPERATION']?>
							</button>
						</div>
						<div class="col-md-6">
							<div class="table-actions-wrapper page-right">
								<select class="select2me width220 input-inline input-sm" id="allHypervisor">
                       			</select>
								<input id="searchVm" style="margin-top:1px;" type="search" maxlength="128" class="searchinput table-group-action-input form-control input-inline input input-sm" placeholder="<?php echo $LANG['UI_VM_REPORT_SEARCH_TIPS']?>" aria-controls="example">
								<input id="searchVmBtn" type="button" class="btn btn-sm default" value="<?php echo $LANG['UI_PUBLIC_SEARCH']?>">
							</div>
							
						</div>
					</div>
				</div>
                <div class="table-container">
					<table class="table table-striped table-bordered table-hover" id="userVM">
					<thead>
					<tr role="row" class="heading">
						<th width="5%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="35%">
						   	<?php echo $LANG['UI_REPORT_VM_NAME']?>
						</th>
						<th width="20%">
						  	<?php echo $LANG['UI_VCENTER_VC']?>
						</th>
						<th width="30%">
							<?php echo $LANG['UI_BACKUP_FILE_PATH']?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_PUBLIC_STATUS']?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
					
				</div>
			</div>
		</div>
	</div>	
	<!-- END MODAL -->
</div>