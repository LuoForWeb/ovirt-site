<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $LANG;
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<style>
    .fileText {
        font-weight: 400;
        font-size: 12px;
        color: rgba(126,130,153,0.8);
        line-height: 20px;
        text-align: left;
        cursor: pointer;
    }
    .fileText span{
        width: 88px;
        text-align: right;
        display: inline-block;
        margin-right: 8px;
    }
    .redText {
        font-family: Microsoft YaHei, Microsoft YaHei;
        font-weight: 400;
        font-size: 12px;
        color: red;
        line-height: 20px;
    }
    .path-tree {
        width: 100%;
        height: 400px;
        background: #FFFFFF;
        border-radius: 2px 2px 2px 2px;
        border: 1px solid #E6E6E6;
    }
    .drawer .col-md-12{margin-bottom: 24px;}
</style>
<div id="share_task_tab">
    <div class="portlet box">
        <div class="portlet-body mlr10">
            <div class="obs-manager-toolbar">
                <div class="tool-left">
                    <div class="tool-left-item <?php if (!in_array("p_current_job_manager", $_SESSION['permissionArr'])) {echo 'display-none';}?>">
                        <button type="button" id="delete_share_task" disabled
                                class="btn btn-toolbar-delete disabled">
                            <i class="viconfont vicon-a-Deleteshanchu1"></i>
                        </button>
                    </div>
                    <div class="tool-left-item">
                        <div class="search input-group">
                            <input id="share_task_search_ipt" class="currentSearch customSearch"
                                   style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                                   placeholder="<?php echo $LANG['UI_PLACEHOLDER_SEARCH_BY_SHARE_PATH'] ?>">
                            <div class="position0" style="width:auto;height:34px">
                                <button class="b-btn clear hide position0" id="share_task_clear_search"><i
                                            class="icon-close-small"></i></button>
                            </div>
                            <div class="search-btn positionL0" style="width:auto;height:34px;">
                                <button id="share_task_search" class="b-btn search-btn"><i
                                            class="icon-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table" id="share_table"></table>
            </div>

        </div>
    </div>
    <!--修改 -->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="share_task_edit" style="width: 600px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header ">
                <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top">
                    <i class="addNewTask" style="padding-top: 3px"></i>
                    <span style="vertical-align: top"><?php echo $LANG['UI_RESOURCE_GROUP_ADD'] ?></span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
                </h4>

            </div>
            <div class="drawer-body">
                <?php include_once '../component/storage_mount.php'; ?>

                <div class="col-md-12 paddding-l-r-0">
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <?php echo $LANG['UI_NAS_CLOUD_USERNAME'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <input type="text" maxlength="128" class="form-control" id="grainness_edit_name"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 paddding-l-r-0">
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <?php echo $LANG['UI_NAS_CLOUD_PASSWORD'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <input type="password" maxlength="128" class="form-control" id="grainness_edit_password"/>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="drawer-footer">
                <button type="button" class="btn btn-primary" id="graininess_edit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
            </div>
        </div>
    </div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/libs/md5.js"></script>
<script type="text/javascript" src="./scripts/platform/recovery/share-task-tab.js"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
