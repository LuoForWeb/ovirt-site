<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <!-- Begin: life time stats -->
        <div class="table-toolbar-wrapper vin_toolbar" id="system_log_toolbar">
            <div class="table-toolbar-wrapper__left" style="display: flex;">
                <?php
                //检查屏蔽只读观察者的操作按钮
                if (in_array("p_system_log_delete", $_SESSION['permissionArr'])) {
                    // 删除
                    echo '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="systemLogDelete"></button></div>';
                }
                ?>
                <div class="search input-group mr12">
                    <input type="search" maxlength="128" id="system_searchInput" class="searchinput sys-log-search customSearch" autocomplete="off"
                        style="padding-right:32px" maxlength="64" type="text"
                        placeholder="<?php echo $LANG['UI_SEARCH_USERNAME'] ?>">
                    <div class="position0" style="width:auto;height:34px">
                        <button class="b-btn systemLogclear clear hide position0" id="system_searchBtn"><i
                                class="icon-close-small"></i></button>
                    </div>
                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                        <button class="b-btn search-btn"><i class="icon-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="table-toolbar-wrapper__right">
                <div class="table-actions-wrapper page-right" style="display: flex;">
                    <button type="button" id="system_searchAll" class="btn btn-primary adv_btn brr2 p-lr8">
                        <i class="adv-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
                    </button>
                    <div class="vin_btnToolbar">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="system_searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span> </div>
            </div>
        </div>
        <div class="table-container" id="systemLogDiv">
            <table id="systemLogTable">

            </table>
        </div>
        <!-- End: life time stats -->

        <!-- BEGIN SEARCH MODAL -->
        <div id="system_searchModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <input id="system_vcenteruuid" class="display-none"></input>
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="list-option">
                        <div class="row">
                            <label class="control-label col-md-4"><?php echo $LANG['UI_JOB_TIME_RANGE'] ?>
                            </label>
                            <div class="col-md-6 daterangepickerdiv">
                                <input type="text" id="advanced_search_time_range_system" class="form-control" autocomplete="off">
                                <i class="viconfont vicon-ge_calendar"></i>
                            </div>
                        </div>
                    </div>

                    <div class="list-option">
                        <div class="row">
                            <!-- 日志状态 -->
                            <label class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_STATUS'] ?>
                            </label>
                            <div class="col-md-6">
                                <select class="form-control select2me" id="logStatus">
                                    <option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                                    <option value="1"><?php echo $LANG['WEB_PLATFORM_DES_NORMAL'] ?></option>
                                    <option value="2"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></option>
                                    <option value="3"><?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="list-option">
                        <div class="row">
                            <!-- 操作类型 -->
                            <label class="control-label col-md-4"><?php echo $LANG['UI_LOG_OPERATE_TYPE'] ?>
                            </label>
                            <div class="col-md-6">
                                <select class="form-control select2me" id="opType">
                                    <option value=""><?php echo $LANG['UI_PUBLIC_ALL']; ?></option>
                                    <option value="SYSTEM_USER_LOGIN"><?php echo $LANG['UI_PLATFORM_SYSTEM_LOGIN']; ?></option>
                                    <option value="SYSTEM_USER_LOGINOUT"><?php echo $LANG['UI_USER_LOGIN_OUT']; ?></option>
                                    <option value="SYSTEM_USER"><?php echo $LANG['UI_ORGAN_USER_MANAGE']; ?></option>
                                    <option value="BD_SYSTEMLOG_DESC_KEY_NODE"><?php echo $LANG['UI_VCENTER_ALLOCATION_NODE']; ?></option>
                                    <option value="BD_SYSTEMLOG_DESC_KEY_AGENT_"><?php echo $LANG['UI_CLIENT_MANAGER']; ?></option>
                                    <option value="BD_SYSTEMLOG_DESC_KEY_STORAGE"><?php echo $LANG['UI_DATACENTER_BACKUP_STORAGE']; ?></option>
                                    <option value="UI_PLATFORM_SYSTEM_SET"><?php echo $LANG['UI_PLATFORM_SYSTEM_SET']; ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="list-option">
                        <div class="row">
                            <!-- 用户名 -->
                            <label class="control-label col-md-4"><?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                            </label>
                            <div class="col-md-6">
                                <input id="system_user" type="text" maxlength="64" class="form-control" aria-controls="example">
                            </div>
                        </div>
                    </div>



                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary" id="system_search_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END SEARCH MODAL -->

    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    //如果不是英文,加载语言包
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/moment.min.js"></script>

<!-- END PAGE LEVEL PLUGINS -->