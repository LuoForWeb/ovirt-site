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
    #drawer-edit .col-md-12{margin-bottom: 24px;}
</style>
<div id="transfer_table_div">
    <div class="portlet box">
        <div class="portlet-body mlr10">
            <div class="obs-manager-toolbar">
                <div class="tool-left">
                    <!--<div class="tool-left-item">
                        <button type="button" id="delete_transfer_task" disabled
                            class="btn btn-toolbar-delete disabled">
                            <i class="viconfont vicon-a-Deleteshanchu1"></i>
                        </button>
                    </div>-->
                    <div class="tool-left-item">
                        <div class="search input-group">
                            <input id="transfer_task_search_ipt" class="currentSearch customSearch"
                                   style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                                   placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
                            <div class="position0" style="width:auto;height:34px">
                                <button class="b-btn clear hide position0" id="transfer_task_clear_search"><i
                                            class="icon-close-small"></i></button>
                            </div>
                            <div class="search-btn positionL0" style="width:auto;height:34px;">
                                <button id="transfer_task_search" class="b-btn search-btn"><i
                                            class="icon-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-container">
                <table id="transfer_table"></table>
            </div>
        </div>
    </div>
    <!--修改 -->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-edit" style="width: 800px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top">
                    <i class="addNewTask" style="padding-top: 3px"></i>
                    <span style="vertical-align: top;font-family: Microsoft YaHei UI, Microsoft YaHei UI;font-weight: 400;color: #333333;"><?php echo $LANG['UI_VIRUS_ADD_VIRUS'] ?></span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <div class="col-md-12 paddding-l-r-0">
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <?php echo $LANG['UI_BACKUP_FILE_CHOOSE_AGENT'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <select id="select_transfer_client" class="form-control select-with">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 paddding-l-r-0">
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <?php echo $LANG['UI_RECOVER_SAVE_PATH'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="path-tree">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 paddding-l-r-0">
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <?php echo $LANG['UI_RECOVER_FILM_SAVE_MODEL'] ?>
                        </label>
                        <div class="col-md-8">
                            <select id="fileSave" class="form-control select-with">
                                <option value="1" selected><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_COVER'] ?></option>
                                <option value="3"><?php echo $LANG['WEB_PLATFORM_DES_SKIP'] ?></option>
                                <option value="4"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_RENAME'] ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="drawer-footer">
                <button type="button" class="btn btn-primary" id="edit_submit"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CANCEL'] ?></button>
            </div>
        </div>
    </div>

</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/recovery/transfer-task.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
