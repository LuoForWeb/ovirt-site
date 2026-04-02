<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css"
	href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
	type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
			<span><?php echo $LANG['UI_PLATFORM_INFRASTRUCTURE'] ?></span>
		</a>
	</li>
	<span>
		>
	</span>
	<span class="curent"><?php echo $LANG['UI_PALTFORM_STORAGE_LANFREE'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap">
	<div class="resource-manager-wrap__header">
		<span>
			<i class="viconfont vicon-storage_lanfree me-10"></i>
			<span class="resource-manager-wrap__header__text"><?php echo $LANG['UI_PALTFORM_STORAGE_LANFREE'] ?></span>
		</span>
	</div>
	<div class="resource-manager-wrap__content">
		<div class="table-toolbar-wrapper vin_toolbar" id="lan_free_toolbar">
			<div class="table-toolbar-wrapper__left">
				<?php
				if (in_array("p_storage_lanfree_delete", $_SESSION['permissionArr'])) {
					// 删除
					echo '<div>
					<button type="button" id="delete" class="b-btn brr2 mr12 table-toolbar-btn" style="cursor:not-allowed;background-color:#F4F4F5">
						<i class="icon-gray-delete"></i>
					</button>
				</div>';
				}
				echo '<div class="input-group mr12">
						<input type="search" maxlength="128" id="searchInput" class="searchinput customSearch" autocomplete="off"
							style="padding-right:32px;min-width: 220px;" maxlength="64" type="text"
							placeholder="' . $LANG['UI_SEARCH_AS_STORAGE_NAME'] . '">
						<div class="position0" style="width:auto;height:34px">
							<button class="b-btn task_alarm_tableclear clear hide position0" id="searchbtn"><i
									class="icon-close-small"></i></button>
						</div>
						<div class="search-btn positionL0" style="width:auto;height:34px;">
							<button class="b-btn search-btn"><i class="icon-search"></i></button>
						</div>
					</div>';
				if (in_array("p_storage_lanfree_add", $_SESSION['permissionArr'])) {
					// 新建
					echo '<div class="btn-group">
					<button type="button" id="add" class="btn table-toolbar-btn">
						<i class="viconfont vicon-biaogetianjia"></i> ' . $LANG['UI_PUBLIC_ADD_OPERATION'] . '
					</button>
				</div>';
				}
				if (in_array("p_storage_lanfree_edit", $_SESSION['permissionArr'])) {
					// 修改
					echo '<div class="btn-group">
					<button type="button" id="edit" class="btn table-toolbar-btn">
						<i class="viconfont vicon-xiugai"></i> ' . $LANG['UI_PUBLIC_MODIFY'] . '
					</button>
				</div>';
				}
				?>
			</div>

			<div class="table-toolbar-wrapper__right">
				<div class="table-actions-wrapper page-right">
					<!-- <input id="searchInput" type="search" maxlength="128"
						class="searchinput table-group-action-input form-control input-inline input"
						placeholder="<?php echo $LANG['UI_SEARCH_AS_STORAGE_NAME'] ?>" aria-controls="example">
					<input id="searchbtn" type="button" class="btn btn-search"
						value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>"> -->
					<button type="button" id="searchAll" class="btn btn-primary adv_btn brr2 p-lr8 list">
						<i class="adv-search"></i><span style="color: #FFFFFF"> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?></span>
					</button>
				</div>
			</div>
		</div>

		<div id="searchDiv" class="search-content searchDiv display-none">
			<?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?>
			<span class="searchContent"></span>
			<span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
		</div>

		<div class="table-container lan-free-table-container">
			<table id="lanfreetable"></table>
		</div>

		<div class="alert alert-block alert-info fade in h-130px m0" id="lan_free_tip">
			<button id="lan_free_tip_close" type="button" class="close" data-dismiss="alert"></button>
			<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
			<ol class="alert-ol">
				<li>
					<?php echo $LANG['UI_STORAGE_LANFREE_TIPS1'] ?>
				</li>
				<li>
					<?php echo $LANG['UI_STORAGE_LANFREE_TIPS2'] ?>
				</li>
				<li>
					<?php echo $LANG['UI_STORAGE_LANFREE_TIPS3'] ?>
				</li>
			</ol>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN MODAL EDIT-->
<div id="modaldivedit" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
	data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="viconfont vicon-ge_modify"></i>
			<?php echo $LANG['UI_STORAGE_LANFREE_EDIT'] ?></h4>
	</div>
	<div class="modal-body">
		<div class="form-group">
			<label class="col-md-3 control-label"><?php echo $LANG['UI_STORAGE_NAME'] ?><span class="required">
					* </span></label>
			<div class="col-md-8">
				<input type="text" class="form-control display-none" maxlength="128" id="storageuuid">
				<input type="text" class="form-control" maxlength="128" id="storagename">
				<span class="help-block">
					<?php echo $LANG['UI_STORAGE_EDIT_NAME_TIPS'] ?> </span>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" data-dismiss="modal"
			class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary"
			id="editsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>
<!-- END MODAL -->

<!-- BEGIN MODAL DELETE-->
<div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
	data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="viconfont vicon-ge_delete"></i>
			<?php echo $LANG['UI_STORAGE_DELETE'] ?></h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body" id="childrendiv">
			<div class="alert alert-warning">
				<button type="button" class="close" data-dismiss="alert"></button>
				<ul class="alert-ul">
					<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
					<li>
						<i class="fa fa-info-circle "></i>
						<?php echo $LANG['UI_STORAGE_DELETE_TIP_TITLE'] ?>
						<?php echo $LANG['UI_STORAGE_DELETE_TIP_CONTENT'] ?>
					</li>
				</ul>
			</div>
		</div>
		<div class="row static-info">
			<div class="col-md-4 name textalignr">
				<?php echo $LANG['UI_STORAGE_DELETE_POINT_COUNT'] ?>:
			</div>
			<div class="col-md-8 value" id="timepointcount">
			</div>
		</div>
		<div class="row static-info">
			<div class="col-md-4 name textalignr">
				<?php echo $LANG['UI_STORAGE_DELETE_BACKUP_SIZE'] ?>:
			</div>
			<div class="col-md-8 value" id="timepointsize">
			</div>
		</div>
		<div class="row static-info">
			<div class="col-md-4 name textalignr">
				<?php echo $LANG['UI_STORAGE_DELETE_TASK_COUNT'] ?>:
			</div>
			<div class="col-md-8 value" id="taskcount">
			</div>
		</div>
		<div class="row static-info">
			<div class="col-md-4 name textalignr">
				<?php echo $LANG['UI_JOB_RNAME'] ?>:
			</div>
			<div class="col-md-8 value" id="taskname">
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" data-dismiss="modal"
			class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>
<!-- END MODAL -->

<!-- BEGIN SEARCH MODAL -->
<div id="searchmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1"
	data-focus-on="input:first" data-backdrop="static">
	<input id="vcenteruuid" class="display-none"></input>
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">


            <div class="row" style="padding: 10px;">
                <!-- 存储别名 -->
                <label class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_NAME'] ?> ： </label>
                <div class="col-md-6">
                    <input style="width:100%; max-width: 698px;" id="nickName" type="text" maxlength="64" height="28px;"
                           class="form-control input-sm" aria-controls="example">
                </div>
            </div>

            <div class="row" style="padding: 10px;">
                <!-- 类型 -->
                <label class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TYPE'] ?> ：</label>
                <div class="col-md-6">
                    <select class="form-control select2me input-sm" id="storageType">
                        <option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                        <option value="4"><?php echo $LANG['UI_STORAGE_TYPE4'] ?></option>
                        <option value="5"><?php echo $LANG['UI_STORAGE_TYPE5'] ?></option>
                        <option value="6"><?php echo $LANG['UI_STORAGE_TYPE6'] ?></option>
                    </select>
                </div>
            </div>

            <div class="row" style="padding: 10px;">
                <!-- 所在节点 -->
                <label class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_IN_NODE'] ?> ：</label>
                <div class="col-md-6">
                    <select class="form-control select2me input-sm" id="nodeSelect">
                    </select>
                </div>
            </div>

            <div class="row" style="padding: 10px;">
                <!-- 存储状态 -->
                <label class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_STATUS'] ?> ：</label>
                <div class="col-md-6">
                    <select id="storageStatus" class="form-control input-sm">
                        <option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                        <option value="1"><?php echo $LANG['WEB_PLATFORM_DES_NORMAL'] ?></option>
                        <option value="2"><?php echo $LANG['WEB_STORAGE_STATUS_CREATING'] ?></option>
                        <option value="3"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></option>
                        <option value="3"><?php echo $LANG['WEB_STORAGE_STATUS_UNMOUNT'] ?></option>
                    </select>
                </div>
            </div>


		</div>
	</div>

	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal"
			class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button" class="btn btn-primary"
			id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>
<!-- END SEARCH MODAL -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript"
	src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/storage/storage_lanfree.js"></script>
<!-- END PAGE LEVEL PLUGINS -->