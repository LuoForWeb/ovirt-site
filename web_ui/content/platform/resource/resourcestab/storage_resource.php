<?php include_once '../../../../tpl/permission.php'; ?>
<div class="row">
	<div class="col-md-12">
		<div class="portlet-body" id="">
			<div class="table-toolbar-wrapper">
				<div class="table-toolbar-wrapper__left">
					<div class="btn-group">
						<button type="button" id="storage_add" class="btn table-toolbar-btn">
						<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD_OPERATION'] ?>
						</button>
					</div>
					<div class="btn-group">
						<button type="button" id="storage_delete" class="btn table-toolbar-btn">
						<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
						</button>
					</div>
				</div>
			</div>
			
			<div class="table-container mt15">
				<table class="table table-striped table-hover" id="storageDatatable">
					<thead>
						<tr role="row" class="heading">
							<th width="5%">
								<input type="checkbox" class="group-checkable">
							</th>
							<th width="20%">
								<?php echo $LANG['UI_STORAGE_NAME'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_PUBLIC_TYPE'] ?>
							</th>
							<th width="20%">
								<?php echo $LANG['UI_STORAGE_IN_NODE'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_STORAGE_TOTAL_SIZE'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_RECOVERY_STORAGE_VALID_SIZE'] ?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_STORAGE_STATUS'] ?>
							</th>
							<th width="15%">
								<?php echo $LANG['UI_PUBLIC_PURPOSE'] ?>
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
	<div id="storageModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		<div class="modal-header ">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			<h4 class="modal-title"><?php echo $LANG['UI_RESOURCE_GROUP_ALLOCATION_STORAGE'] ?></h4>
		</div>
		<div class="modal-body">
			<div class="portlet-body">
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left">
						<button type="button" id="storage_submit" class="btn table-toolbar-btn">
						<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD_OPERATION'] ?>
						</button>
					</div>
					<div class="table-toolbar-wrapper__right">
						<div class="table-actions-wrapper page-right">
							<input id="searchStorage" type="search" maxlength="128" class="searchinput table-group-action-input form-control input-inline input" placeholder="<?php echo $LANG['UI_RESOURCE_GROUP_ALLOCATION_STORAGE_SEARCH'] ?>" aria-controls="example">
							<input id="searchStorageBtn" type="button" class="btn btn-search" value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>">
						</div>
					</div>
				</div>
				<div class="table-container">
					<table class="table table-striped table-hover" id="userStorage">
					<thead>
					<tr role="row" class="heading">
						<th width="5%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="20%">
							<?php echo $LANG['UI_STORAGE_NAME'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_PUBLIC_TYPE'] ?>
						</th>
						<th width="20%">
							<?php echo $LANG['UI_STORAGE_IN_NODE'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_STORAGE_TOTAL_SIZE'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_RECOVERY_STORAGE_VALID_SIZE'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_STORAGE_STATUS'] ?>
						</th>
						<th width="15%">
							<?php echo $LANG['UI_PUBLIC_PURPOSE'] ?>
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