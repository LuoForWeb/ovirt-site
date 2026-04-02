<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="storage_manager" href="./content/platform/storage/storagedevice.php">
            <span><?php echo $LANG['UI_STORAGE_MANAGER'] ?></span>
        </a>
    </li>
    <span>></span>
    <li>
        <a class="ajaxify" name="storage_manager" href="./content/platform/storage/storage.php">
            <?php echo $LANG['UI_DATACENTER_BACKUP_STORAGE'] ?>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PALTFORM_STORAGE_DATA'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap">
    <div class="resource-manager-wrap__header">
        <span>
            <i class="levelchild viconfont vicon-a-View-listxiangqingliebiao1 me-10"></i>
            <span class="resource-manager-wrap__header__text"><?php echo $LANG['UI_STORAGE_DATA_OLD_TASK_LIST'] ?></span>
        </span>
    </div>
    <div class="resource-manager-wrap__content">
        <div class="table-toolbar-wrapper vin_toolbar" id="vin_current_toolbar">
            <div class="table-toolbar-wrapper__left leftTool_vm"></div>
            <div class="table-toolbar-wrapper__right">
                <div class="rightTool">
                    <div class="vin_btnToolbar"></div>
                </div>
            </div>
        </div>

        <div class="table-container storage-manager-table-container">
            <table id="backupdatatable"></table>
        </div>
    </div>
</div>

<!-- BEGIN MODAL -->
<div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="fa fa-share-square-o"></i> <?php echo $LANG['UI_STORAGE_DATA_TO_BACKUPDATA'] ?></h4>
    </div>
    <div class="modal-body">
        <div class="form-group">
            <label class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_DELETE_TASK_COUNT'] ?>
            </label>
            <div class="col-md-6 margintop10" id="taskcount">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_DATA_TO_USER'] ?><span class="required">
            * </span>
            </label>
            <div class="col-md-6">
                <select class="form-control select2me" name="userselect">
                </select>
                <div><span class="help-block ">
                    <?php echo $LANG['UI_STORAGE_DATA_SELECT_TO_USER'] ?>
                </span></div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>	
<!-- END MODAL -->
<!-- END PAGE CONTENT-->
    

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/storage/storage_data.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	