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
<div class="backupdata-wrapper">
    <div class="backupdata-wrapper__head">
        <i class="levelchild viconfont vicon-vmdata"></i>
        <span class="backupdata-wrapper__head__title"><?php echo $LANG['WEB_M365_BACKUP_DATA'] ?></span>
    </div>

    <div class="backupdata-wrapper__content row">
        <div class="col-md-4 timepoint-wrapper">
            <div class="timepoint-wrapper__head">
                <i class="viconfont vicon-ge_time_point"></i>
                <span class="timepoint-wrapper__head__title"><?php echo $LANG['UI_MICROSOFT365_BACKUP_POINT'] ?></span>
            </div>
            <div class="timepoint-wrapper__content">
                <div class="timepoint-wrapper__content__btn">
                    <button type="button" id="allDelete" class="btn btn-sm green-haze display-none">
                        <i class="viconfont vicon-a-Deleteshanchu2" style="font-size: 16px;"></i>
                        <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
                    </button>
                </div>
                <div class="timepoint-wrapper__content__tree tree-wrapper">
                    <div class="tree-wrapper__operates">
                        <div class="tree-wrapper__operates__select">
                            <select class="bs-select" data-show-subtext="true" id="storageSelect">
                            </select>
                        </div>

                        <button type="button" id="filterSearch" class="btn btn-sm green-haze">
                            <i class="viconfont vicon-ge_filter"></i>
                            <?php echo $LANG['UI_DATA_SCREEN'] ?>
                        </button>

                        <!-- BEGIN FILTER-POPOVER -->
                        <div id="filter-content" class="display-none">
                            <div class="modal-body">
                                <div class="portlet-body">
                                    <div style="margin-top: 10px;">
                                        <input type="text" maxlength="128"
                                               placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>"
                                               class="form-control " id="searchfs">
                                    </div>
                                </div>
                            </div>
                            <div class="popModal_footer">
                                <button type="button" data-popModalBut="cancel"
                                        class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CANCEL'] ?></button>
                                <button type="button" data-popModalBut="ok"
                                        class="btn btn-primary"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
                            </div>
                        </div>
                        <!-- END FILTER POPOVER -->
                    </div>
                    <div class="tree-wrapper__ztree exchange-timepoint-div">
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
                        <ul id="echangetimepointtree" class="ztree"></ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 tabledata-wrapper">
            <div class="tabledata-wrapper__head">
                <i class="viconfont viconfont vicon-ge_backup_file"></i>
                <span
                        class="tabledata-wrapper__head__title"><?php echo $LANG['UI_MICROSOFT365_POINT'] ?></span>
                <span id="fsdataUrl" class="tabledata-wrapper__head__taskname"></span>
            </div>
            <div class="tabledata-wrapper__content">
                <div class="alert alert-block alert-info fade in" id="tabletips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class="alert-ol">
                        <li><?php echo $LANG['WEB_M365_BACKUP_TREE_TIPS_ONE'] ?></li>
                        <li><?php echo $LANG['WEB_M365_BACKUP_TREE_TIPS_TWO'] ?></li>
                    </ol>
                </div>

                <div id="nastablediv" class="tabledata-wrapper__content__table display-none">
                    <div class="toolbar-caption">
                        <div id="searchDiv" class="toolbar-caption__search opacity-0">
                            <?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span class="searchContent"></span><span
                                    class="clearSearch"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_FILTER_CLEAR'] ?></span>
                        </div>
                        <div class="toolbar-caption__btn">
                            <button type="button" id="searchAll" class="btn btn-sm green-haze">
                                <i class="fa fa-search"></i>
                                <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="table table-striped table-bordered table-hover lh2" id="pointTable">
                        </table>
                    </div>

                    <div class="table-tips alert alert-block alert-info fade in">
                        <button type="button" class="close" data-dismiss="alert" id="mark_tips_close"></button>
                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                        </h4>
                        <ol class="alert-ol">
                            <li>
                                <?php echo $LANG['WEB_OS_HOST_TIME_TIPS2'] ?>
                            </li>
                            <li>
                                <?php echo $LANG['WEB_M365_MARK_TIPS'] ?>
                            </li>
                        </ol>
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
<script type="text/javascript" src="./scripts/exchange/exchange_data.js"></script>
<!-- END PAGE LEVEL PLUGINS -->