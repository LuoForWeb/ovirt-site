<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- END PAGE LEVEL STYLES -->
 <!-- BEGIN PAGE HEADER -->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php">
			<span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
		</a>
	</li>
	<span>
		>
	</span>
	<span class="curent"><?php echo $LANG['UI_GLOBAL_STRATEGY_LIST'] ?></span>
</h3>
<!-- END PAGE HEADER -->
<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap">
	<div class="resource-manager-wrap__header">
		<span>
			<i class="levelchild viconfont vicon-global_strategy"></i>
			<span class="resource-manager-wrap__header__text"><?php echo $LANG['UI_GLOBAL_STRATEGY_LIST'] ?></span>
		</span>
	</div>
	<div class="resource-manager-wrap__content" id="strategyTableDiv">
		<div class="table-toolbar">
			<div class="vin_toolbar" id="vin_strategy_toolbar">
				<div class="leftTool"></div>
				<div class="rightTool">
					<div class="vin_btnToolbar"></div>
				</div>
			</div>
		</div>

		<div class="table-container">
			<table id="strategy_table"></table>
		</div>
	</div>
</div>

<!-- BEGIN MODAL DELETE-->
<div id="deletemodaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_DELETE'] ?></h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="alert alert-warning">
				<button type="button" class="close" data-dismiss="alert"></button>
				<ul class="alert-ul">
					<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
					<li>
						<i class="viconfont vicon-ge_summary"></i>
						<?php echo $LANG['UI_GLOBAL_STRATEGY_DELETE_TIPS1'] ?>
					</li>
				</ul>
			</div>
		</div>
		<div class="row static-info">
			<div class="col-md-11">
				<ul id="strategy_tree" class="ztree bd1de5  tree_div"></ul>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary" id="delete_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>
<!-- END MODAL -->
<!-- BEGIN MODAL DELETE-->
<div id="distributeModalDiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="viconfont vicon-a-Efferent-threechuanchu3"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_DISPENSE'] ?></h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="table-container">
				<table id="task_table"></table>
				<div class="alert alert-block alert-info flex-items-center justify-content-space-between fade in h-50px mb-0">
					<div class="alert-info-wrapper">
						<span class="alert-info-wrapper__label"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?>: </strong></span>
						<span class="alert-info-wrapper__label"><?php echo $LANG['UI_GLOBAL_STRATEGY_DISPENSE_TIPS1'] ?></span>
					</div>
					<button type="button" class="close" data-dismiss="alert"></button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary" id="dispense_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>
<!-- END MODAL -->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/strategy/global_strategy.js"></script>
<!-- END PAGE LEVEL PLUGINS -->