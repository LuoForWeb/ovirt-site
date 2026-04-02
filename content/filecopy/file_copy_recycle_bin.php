<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<?php
include_once '../../tpl/permission.php';
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
?>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
      type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="backupdata-wrapper file-copy">
    <div class="backupdata-wrapper__head">
        <i class="levelchild viconfont vicon-huishouzhan"></i>
        <span class="backupdata-wrapper__head__title"><?php echo $LANG['UI_FILE_COPY_RECYCLE_BIN'] ?></span>
    </div>
    <div class="backupdata-wrapper__button mt20 pl20">
        <div class="file-copy-del">
            <button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deletePathData"></button>
        </div>
        <button class="btn table-toolbar-btn" id="clearData" data-toggle="drawer" data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false">
            <i class="viconfont vicon-qingkonghuishouzhan mr4"></i>
            <span><?php echo $LANG['UI_FILE_COPY_RECYCLE_BIN_CLEAN'] ?></span>
        </button>
        <button class="btn table-toolbar-btn" id="restorePathData">
            <i class="viconfont vicon-huifu mr4"></i>
            <span><?php echo $LANG['UI_FILE_COPY_RECYCLE_BIN_RESTORE'] ?></span>
        </button>
    </div>
    <div class="backupdata-wrapper__content row">
        <div class="col-md-4 pr0 height_100">
            <div class="timepoint-wrapper">
                <div class="src-wrap__title">
                    <i class="viconfont vicon-shengchengshijian viconfont-color mr8"></i><?php echo $LANG['UI_FILE_COPY_RECYCLE_BIN_SELECT'] ?>
                </div>
                <div class="timepoint-wrapper__content">
                    <div class="src-wrap__content__search">
                        <div class="vm_tree_div">
                            <div class="input-icon width100p">
                                <input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD']?>" class="form-control" id="searchInput" style="margin-top: 0;">
                            </div>
                        </div>
                    </div>
                    <div class="timepoint-wrapper__content__tree tree-wrapper">
                        <div class="tree-wrapper__ztree recycle-bin-tree-div">
                            <span id="markStr" class="tree-wrapper__ztree__mark"></span>
                            <div class="alert alert-block alert-info fade in display-hide" id="nopointtips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <ul class="alert-ul">
                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                    <li>
                                        <?php echo $LANG['UI_DATA_NODATA_TIPS'] ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="alert alert-block alert-info fade in display-hide" id="nosearchtips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <ul class="alert-ul">
                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                    <li>
                                        <?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
                                    </li>
                                </ul>
                            </div>
                            <ul id="recycleBin" class="ztree"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 height_100">
            <div class="tabledata-wrapper">
                <div class="src-wrap__title file-copy">
                    <i class="viconfont vicon-a-Deleteshanchu2 viconfont-color mr8"></i><?php echo $LANG['UI_FILE_COPY_RECYCLE_BIN_FILE'] ?>
                </div>
                <div class="tabledata-wrapper__content file-path-content">
                    <div class="alert alert-block alert-info fade in" id="rightTips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                        <ol class="alert-ol">
                            <li><?php echo $LANG['UI_FILE_COPY_RECYCLE_BIN_TIPS1'] ?></li>
                            <li><?php echo $LANG['UI_FILE_COPY_RECYCLE_BIN_TIPS2'] ?></li>
                        </ol>
                    </div>
                    <div class="row file-path-div recycle_bin_search display-none">
                        <div class="col-md-11 pl0">
                            <input type="text" id="recycle_bin_path" placeholder="<?php echo $LANG['UI_RECOVERY_FILE_SEARCH_INPUT_TIPS'] ?>" class="form-control">
                        </div>
                        <div class="col-md-1 pd0">
                            <button type="button" class="btn exch-green" id="searchBtn"><i class="viconfont vicon-danchuangsousuo mr8"></i><?php echo $LANG['UI_PUBLIC_SEARCH'] ?></button>
                            <button type="button" class="btn stop-btn display-none" id="stopBtn"><i class="viconfont vicon-ge_suspend mr8"></i><?php echo $LANG['WEB_PLATFORM_DES_STOP'] ?></button>
                        </div>
                    </div>
                    <div class="file-path-div display-none">
                        <ul id="filePath" class="ztree"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
<!-- END PAGE CONTENT-->
<!-- BEGIN MODAL -->
<div id="searchmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1"
     data-focus-on="input:first" data-backdrop="static">
    <input id="vcenteruuid" class="display-none"></input>
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"
                aria-hidden="true"></button>
        <h4 class="modal-title"><i
                    class="viconfont vicon-gaojisousuo1"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
        </h4>
    </div>
    <div class="modal-body" style="height: unset;">
        <div class="portlet-body">

            <!-- 时间点范围 -->
            <div class="list-option" id="startTimeDiv">
                <div class="row">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_DATA_TIMEPOINT_RANGE'] ?>
                    </label>
                    <div class="col-md-7">
                        <div class="daterangepickerdiv">
                            <input type="text" id="daterangepicker" class="form-control"
                                   autocomplete="off">
                            <i class="viconfont vicon-ge_calendar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="list-option">
                <div class="row">
                    <!-- 时间点类型 -->
                    <label class="control-label col-md-3"><?php echo $LANG['UI_PUBLIC_TYPE'] ?>
                    </label>
                    <div class="col-md-5">
                        <select class="form-control select2me" id="timepointType">
                            <option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                            <option value="1"><?php echo $LANG['UI_BACKUP_FULL'] ?></option>
                            <option value="2"><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></option>
                        </select>
                    </div>

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
<!-- END MODAL -->
<!-- BEGIN MODAL -->
<div id="setAllMark" class="modal xmodal fade form-horizontal high-search" tabindex="-1"
     data-focus-on="input:first" data-backdrop="static">
    <input id="Marktimepoint_uuid" class="display-none"></input>
    <input id="MarktimepointNodeUUID" class="display-none"></input>
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"
                aria-hidden="true"></button>
        <h4 class="modal-title"><i
                    class="viconfont vicon-ge_sign"></i> <?php echo $LANG['WEB_PT_OP_GFS_FLAG'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div style="margin-top: 10px;">
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-10">
                        <label style="padding-right: 20px;">
                            <input type="checkbox" name="foreverCheck" class="icheck">
                            <i class="viconfont vicon-remark-forever"></i>
                            <?php echo $LANG['WEB_VM_GFS_FOREVER_POINT'] ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="mark_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END MODAL -->
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

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/popModal/popModal.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/filecopy/file_copy_recycle_bin.js" type="text/javascript"></script>
