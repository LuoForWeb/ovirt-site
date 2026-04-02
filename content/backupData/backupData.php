<?php include_once '../../tpl/permission.php';
$userAllPermission = $_SESSION['permission'];
session_start();
session_commit();
?>
<!-- BEGIN PAGE HEADER-->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/tree-grid/jquery.treegrid.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" href="./scripts/components/timePointDetail/css/timePointDetail.css">
<link rel="stylesheet" type="text/css" href="./scripts/components/daterangepicker/css/daterangepicker.css" />
</link>
<?php include_once '../platform/public/bs_table.php'; ?>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<style type="text/css">
	.modal-backdrop.fade.in {
		z-index: 10051 !important;
	}

	.modal-scrollable {
		z-index: 10052 !important;
	}
	.bootstrap-table .fixed-table-container .table thead th .th-inner{
		display: block;
	}
	.bootstrap-table .fixed-table-container .table thead th .sortable{
		padding-right: unset !important;
	}
</style>
<div class="row backup_data-content">
	<div class="col-md-12">
		<div class="portlet box blue-hoki" id="backup_data-content">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-shuju"></i><?php echo $LANG['UI_PLATFORM_BACKUP_DATA']; ?>
				</div>
			</div>
			<div class="portlet-body">
				<div class="storage_module-column">
					<div style="padding: 20px 20px 0 20px;width: 100%;">
						<div class="col-md-12 module-storage-switch"><i class="viconfont vicon-ge_transfer"></i><span class="switch-tab-name"><?php echo $LANG['UI_BACKUP_DATA_SWITCH_MODULE']; ?></span></div>
						<div class="module-tab_storage display-none"></div>
					</div>
					<div class="col-md-12 storage-tab">
						<div class="storage-tab-item storage-tab-all active" data-id="" data-type="" data-name="<?php echo $LANG['UI_BACKUP_DATA_STORAGE_ALL']; ?>">
							<div class="storage-tab-item-detail">
								<div class="storage-tab-item-icon">
									<i class="viconfont vicon-suoyoucunchu2"></i>
									<span class="item_detail-name"><?php echo $LANG['UI_BACKUP_DATA_STORAGE_ALL']; ?></span>
								</div>
							</div>
						</div>
						<div class="storage-tab-list"></div>
					</div>
					<div class="col-md-12 module-tab display-none">
						<div class="module-tab-item module-tab-all active">
							<div class="module-tab-item-icon">
								<i class="viconfont vicon-suoyou"></i>
							</div>
							<div class="module-tab-item-detail">
								<span class="item_detail-name"><?php echo $LANG['UI_BACKUP_DATA_MODULE_ALL']; ?></span>
							</div>
						</div>
					</div>
				</div>
				<div class="item_task-column local-tab">
					<div class="task-item-switch">
						<div class="task-item-tab task-tab active"><?php echo $LANG['UI_PLATFORM_JOB_MONITOR']; ?></div>
						<div class="task-item-tab item-tab"><?php echo $LANG['UI_BACKUP_DATA_TAB_ITEM']; ?></div>
					</div>
					<div class="task-grid-container">
						<div class="vin_toolbar" id="taskTableToolbar">
							<div class="leftTool">
							</div>
							<div class="rightTool">
								<div class="vin_taskTableToolbar">
								</div>
							</div>
						</div>
						<div class="table-container">
							<table id="taskTable"></table>
						</div>
					</div>
					<div class="item-grid-container display-none">
						<div class="vin_toolbar" id="itemTableToolbar">
							<div class="leftTool">
							</div>
							<div class="rightTool">
								<div class="vin_itemTableToolbar">
								</div>
							</div>
						</div>
						<div class="table-container">
							<table id="itemTable"></table>
						</div>
					</div>
				</div>
				<div class="item_task-column remote-tab display-none">
					<div class="task-item-switch">
						<div class="task-item-tab task-tab active" disable="true"><?php echo $LANG['UI_PLATFORM_JOB_MONITOR']; ?></div>
					</div>
					<div class="remote-grid-container">
						<div class="vin_toolbar" id="remoteTableToolbar">
							<div class="leftTool">
							</div>
							<div class="rightTool">
								<div class="vin_remoteTableToolbar">
								</div>
							</div>
						</div>
						<div class="table-container">
							<table id="remoteTable"></table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- 定时模块时间点抽屉 -->
		<div class="drawer slide backupPointDrawer" id="pointListDrawer" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" data-backdrop="pointListDrawer">
			<input id="point_table_module" class="display-none">
			<div class="drawer-content drawer-content-scrollable" role="document">
				<div class="drawer-header">
					<div class="drawer-title"><i class="viconfont vicon-tenant-detail"></i><?php echo $LANG['UI_BACKUP_DATA_DETAIL'] ?></div>
					<div class="drawer-close" data-dismiss="drawer" aria-label="Close"><i class="viconfont vicon-guanbi"></i></div>
				</div>
				<div class="drawer-body pointDrawerBody pointListBody">
					<div class="task_item-info">
						<div class="task_item-card" id="drawerCard"></div>
						<div class="search_sort-div" id="drawerSort"></div>
						<div class="task_item-tree">
							<ul id="backupDataTree" class="ztree ztree-fa tree-scroll"></ul>
						</div>
					</div>
					<div class="point_grid-div">
						<div class="point_grid-container display-none">
							<div class="vin_toolbar" id="pointTableToolbar">
								<div class="leftTool">
								</div>
								<div class="rightTool">
									<div class="vin_pointTableToolbar">
									</div>
								</div>
							</div>
							<div class="table-container">
								<table id="pointTable"></table>
							</div>
						</div>
						<div class="alert alert-block alert-info fade in noSelectTips">
							<button type="button" class="close" data-dismiss="alert" id="close_tips_btn"></button>
							<div class="alert-info-wrapper">
								<span class="alert-info-wrapper__label"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</span>
								<span class="alert-info-wrapper__content"><?php echo $LANG['UI_BACKUP_DATA_TIPS_SELECT_ITEM'] ?></span>
							</div>
						</div>
						<div class="alert alert-block alert-info fade in display-none timepoint-tips">
							<button type="button" class="close remove-point-tip" data-dismiss="alert"></button>
							<h4 class="alert-heading"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></h4>
							<ol class="alert-ol">
								<li><?php echo $LANG['UI_BACKUP_DATA_POINT_TIPS1'] ?></li>
								<li><?php echo $LANG['UI_BACKUP_DATA_POINT_TIPS2'] ?></li>
								<li><?php echo $LANG['UI_BACKUP_DATA_POINT_TIPS3'] ?></li>
								<li><?php echo $LANG['UI_BACKUP_DATA_POINT_DELETE_TIPS_WORM'] ?></li>
								<li class="cloud_storage_tips"><?php echo $LANG['UI_BACKUP_DATA_POINT_DELETE_TIPS'] ?></li>
							</ol>
						</div>
					</div>
				</div>
				<div class="drawer-footer">
					<button type="button" class="btn btn-default" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?></button>
				</div>
			</div>
		</div>
		<!-- 定时模块时间点抽屉 -->
		<!-- 实时模块时间点抽屉 -->
		<div class="drawer slide backupPointDrawer" id="realPointDrawer" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true">
			<div class="drawer-content drawer-content-scrollable" role="document">
				<div class="drawer-header">
					<div class="drawer-title"><i class="viconfont vicon-tenant-detail"></i><?php echo $LANG['UI_BACKUP_DATA_DETAIL'] ?></div>
					<div class="drawer-close" data-dismiss="drawer" aria-label="Close"><i class="viconfont vicon-guanbi"></i></div>
				</div>
				<div class="drawer-body pointDrawerBody pointListBody">
					<!-- 左侧卡片以及树结构 -->
					<div class="task_item-info">
						<div class="task_item-card" id="realCard"></div>
						<div class="search_sort-div" id="realSort"></div>
						<div class="task_item-tree">
							<ul id="realDataTree" class="ztree ztree-fa tree-scroll"></ul>
						</div>
					</div>
					<!-- 右侧表格树 -->
					<div class="point_grid-div os-cdp-div">
						<div class="real_grid-container display-none">
							<div class="cdp_point_grid-tab">
								<div class="tabbable tabbable-custom" style="overflow: visible;">
									<ul class="nav nav-tabs">
										<li class="active">
											<a href="#tab_backup_set" data-toggle="tab" aria-expanded="true">
												<i class="fa fa-area-chart"></i><?php echo $LANG['UI_VOL_CDP_BAK_DATA']; ?>
											</a>
										</li>
										<li class="" id="task_plot_li">
											<a href="#tab_backup_label_point" data-toggle="tab" aria-expanded="false">
												<i class="viconfont vicon-storage_manager"></i> <?php echo $LANG['UI_VOL_CDP_LABEL_POINTS']; ?>
											</a>
										</li>
										<li class="" id="virus_history_li">
											<a href="#cm_cdp_task_data_safe_point" data-toggle="tab" aria-expanded="false">
												<i class="viconfont vicon-yanzhengpeizhi"></i> <?php echo $LANG['UI_BACKUP_DATA_LABEL_VIRUS_HISTORY']; ?>
											</a>
										</li>
										<li class="" id="data_verify_li">
											<a href="#tab_data_verify_table" data-toggle="tab" aria-expanded="false">
												<i class="viconfont vicon-yanzhengpeizhi"></i> <?php echo $LANG['UI_BACKUP_DATA_LABEL_DATA_VERIFY']; ?>
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="tab-content cdp_point_grid-content">
								<div class="tab-pane active" id="tab_backup_set">
									<div class="vin_toolbar" id="volPointTableToolbar">
										<div class="leftTool">
										</div>
										<div class="rightTool">
											<div class="vin_volPointTableToolbar">
											</div>
										</div>
									</div>
									<div class="table-container">
										<table id="volPointTable"></table>
									</div>
								</div>
								<div class="tab-pane" id="tab_backup_label_point">
									<div class="table-container">
										<table id="tagTable"></table>
									</div>
								</div>
								<div class="tab-pane" id="cm_cdp_task_data_safe_point">
									<div class="table-container" id="cmCdpDataSafePointTableDiv">
										<table id="cmCdpDataSafePointTable"> </table>
									</div>
								</div>
								<div class="tab-pane" id="tab_data_verify_table">
									<div class="table-container">
										<table id="dataVerifyTable"></table>
									</div>
								</div>
							</div>
						</div>
						<div class="alert alert-block alert-info fade in noSelectTips">
							<button type="button" class="close" data-dismiss="alert" id="close_tips_btn"></button>
							<div class="alert-info-wrapper">
								<span class="alert-info-wrapper__label"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</span>
								<span class="alert-info-wrapper__content"><?php echo $LANG['UI_BACKUP_DATA_TIPS_SELECT_ITEM'] ?></span>
							</div>
						</div>
						<div class="alert alert-block alert-info fade in vol-tips display-none">
							<button type="button" class="close remove-real-tip" data-dismiss="alert"></button>
							<h4 class="alert-heading"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></h4>
							<ol class="alert-ol">
								<li><?php echo $LANG['UI_VOL_CDP_MANAGE_BAK_DATA_IMAGE_DATA_SIZE_TIPS']; ?></li>
								<li><?php echo $LANG['UI_VOL_CDP_MANAGE_BAK_DATA_ROLLBACK_DATA_SIZE_TIPS']; ?></li>
							</ol>
						</div>
					</div>
				</div>
				<div class="drawer-footer">
					<button type="button" class="btn btn-default" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?></button>
				</div>
			</div>
		</div>
		<!-- 实时模块时间点抽屉 -->

		<div id="setBackupDataMark" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="mark_uuid" class="display-none"></input>
			<input id="sub_type" class="display-none"></input>
			<input id="node_uuid" class="display-none"></input>
			<input id="module_type" class="display-none"></input>
			<input id="task_type" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-ge_sign"></i> <?php echo $LANG['WEB_PT_OP_GFS_FLAG'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body markList">
				</div>
			</div>

			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="mark_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<div id="wormSettingModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="latest_uuid" type="text" class="display-none">
			<input id="full_uuid" type="text" class="display-none">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_DATA_LABEL_WORM'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					<div class="worm-set-tips">
						<span></span>
					</div>
					<div class="worm-time-set">
						<label><?php echo $LANG['UI_BACKUP_DATA_LABEL_WORM_TIME'] ?></label>
						<div id="wormTimeBtn">
							<span style="display: inline-flex;" class="width100_en"><?php echo $LANG['UI_BACKUP_DATA_LABEL_WORM_UNSET'] ?></span>
							<input maxlength="3" id="wormNumInput"><i class="viconfont vicon-a-Editbianji ml5"></i>
						</div>
					</div>
					<div class="worm-tips">
						<span class="worm-tips-label"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>: </span><span class="worm-tips-content"></span>
					</div>
				</div>
			</div>

			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="setWormConfirm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
	</div>
</div>
<!-- BEGIN MODAL -->
<div id="syncPointData" class="modal xmodal fade" tabindex="-1"
	data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal"
			aria-hidden="true"></button>
		<h4 class="modal-title">
			<i class="viconfont vicon-ge_refresh mr8"></i><?php echo $LANG['WEB_M365_COMMON_OP_CODE_SYNC_META_FILE'] ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="row">
				<div class="form-group col-md-12">
					<label class="control-label col-md-3 text-align_r">
						<?php echo $LANG['WEB_M365_COMMON_OP_CODE_SIZE_META_FILE'] ?>:
					</label>
					<div class="col-md-6 pd0">
						<div class="syncSize">--</div>
					</div>
				</div>
				<div class="form-group col-md-12">
					<label class="control-label col-md-3 text-align_r">
						<?php echo $LANG['WEB_M365_SYNC_PROGRESS'] ?>:
					</label>
					<div class="syncSpeed col-md-8 pd0">
						<div class="progress progress-striped active margin0">
							<span id="total-progress" class="progress-bar progress-bar-success mr5" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></span>
							<span class="taskprogress" id="progressright">--</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="alert alert-block alert-info fade in mb0">
			<button type="button" class="close" data-dismiss="alert"></button>
			<ul class="alert-ul">
				<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
				<li> <?php echo $LANG['WEB_M365_SYNC_PROGRESS_TIPS'] ?></li>
			</ul>
		</div>
	</div>

	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal"
			class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
	</div>
</div>
<!-- END MODAL -->


<!-- BEGIN SAFE HISTORY CONTENT DRAWER -->
<div class="drawer slide min-width850" data-placement="right" tabindex="-1" role="dialog" id="dataSafeDetailContentDrawer" data-backdrop="dataSafeDetailContentDrawer">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<h4 class="drawer-title  ml-15">
				<span>
					<i class="viconfont vicon-jiaobenguanli mr8 ml15"></i>
				</span>
				<?php echo $LANG['UI_CM_CDP_SAFE_SCAN_HISTORY_INFO'];?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
			</h4>
		</div>
		<div class="drawer-body">
			<div class="portlet-body" style="height: 100%">
				<div class="form-group" style="height: 20px; margin-bottom: 16px">
					<div class="table-container" id="cmCdpPointVirusListTablediv">
                        <table id="cmCdpPointVirusListTable"> </table>
                    </div>
				</div>
			</div>
		</div>
		<!-- footer -->
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
        </div>
	</div>
</div>
<!-- END SAFE HISTORY CONTENT DRAWER -->
<script type="text/javascript" src="./scripts/libs/md5.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/components/daterangepicker/js/daterangepicker.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/jquery.treegrid.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/bootstrap-table-treegrid.js"></script>
<script type="text/javascript" src="./assets/global/plugins/clipboard/clipboard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script src="./scripts/backupData/filter.js" type="text/javascript"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
<script src="./scripts/backupData/mySelector.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/timePointDetail.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>
<script src="./scripts/backupData/backupData.js" type="text/javascript"></script>