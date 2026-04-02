<?php
include_once '../../../tpl/permission.php';
global $LANG;
?>

<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./css/platform/common_delete.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/storage/storage_pool.css" />
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE HEADER -->
<!-- END PAGE HEADER -->

<!-- BEGIN PAGE CONTENT -->
<div class="table-toolbar" id="storagePoolList">
    <div class="vin_toolbar" id="vin_storage_pool_toolbar">
        <div class="leftTool"></div>
        <div class="rightTool">
            <div class="vin_btnToolbar"></div>
        </div>
    </div>
</div>
<div class="table-container reset-td-width storage-pool-table-container">
    <table class="table" id="storagePoolTable"></table>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN MODAL -->
<!-- END MODAL -->

<!-- BEGIN DRAWER -->

<!-- BEGIN ADD/EDIT STORAGE POOL DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="modifyStoragePoolDrawer" data-backdrop="static">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title">
                <span>
                    <i class="viconfont vicon-biaogetianjia icon"></i>
                </span>
                <span class="title">
                    <?php echo $LANG['UI_STORAGE_POOL_ADD_TITLE'] ?>
                </span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <div class="drawer-body">
            <!-- BEGIN FORM-->
            <form action="#" id="modifyStoragePoolForm" class="form-horizontal" data-lang="" data-type="" data-uuid="">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>
                        <span class="message"></span>
                    </div>

                    <!-- 资源池名称 -->
                    <div class="form-group">
                        <label for="storagePoolName" class="control-label drawer-item-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_STORAGE_POOL_NAME']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" id="storagePoolName" maxlength="60" />
                            <div class="help-block">
                                <?php echo $LANG['UI_STORAGE_POOL_NAME_TIPS']; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 存储池类别 -->
                    <div class="form-group">
                        <label for="storagePoolType" class="control-label drawer-item-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_STORAGE_POOL_TYPE']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <select class="form-control select2me" id="storagePoolType">
                                <option value="0"><?php echo $LANG['UI_STORAGE_POOL_TYPE_TIPS'] ?></option>
                                <option value="1"><?php echo $LANG['UI_STORAGE_POOL_TYPE1'] ?></option>
                                <option value="2"><?php echo $LANG['UI_STORAGE_POOL_TYPE2'] ?></option>
                                <option value="3"><?php echo $LANG['UI_STORAGE_POOL_TYPE3'] ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- 选择存储设备 -->
                    <div class="form-group storageListDiv">
                        <label class="control-label drawer-item-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_STORAGE_POOL_STORAGE']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <ul class="ztree" id="storageListTree"></ul>
                        </div>
                    </div>

                    <!-- 备注 -->
                    <div class="form-group">
                        <label for="storagePoolRemark" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_STORAGE_POOL_REMARK']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" id="storagePoolRemark"
                                   placeholder="<?php echo $LANG['UI_STORAGE_POOL_REMARK_PLACEHOLDER'] ?>" />
                        </div>
                    </div>
                </div>
            </form>
            <!-- END FORM-->
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions me-0">
                <button type="button" class="btn default cancel">
                    <?php echo $LANG['UI_PUBLIC_NO'] ?>
                </button>
                <button type="button" class="btn green-haze btn-confirm">
                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END ADD/EDIT STORAGE POOL DRAWER -->

<!-- END DRAWER -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./scripts/platform/storage/storage_pool.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
