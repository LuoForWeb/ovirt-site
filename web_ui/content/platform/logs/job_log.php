<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
    type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/cascader/css/cascader.css" />

<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <!-- Begin: life time stats -->
        <div class="table-toolbar-wrapper vin_toolbar" id="job_log_toolbar">
            <div class="table-toolbar-wrapper__left" style="display: flex;">
                <?php
                //检查屏蔽只读观察者的操作按钮
                if (in_array("p_job_log_delete", $_SESSION['permissionArr'])) {
                    // 删除
                    echo '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="jobLogDelete"></button></div>';
                }
                ?>
                <div class="search input-group mr12">
                    <input type="search" maxlength="128" id="task_searchInput" class="searchinput job-log-search customSearch" autocomplete="off"
                        style="padding-right:32px" maxlength="64" type="text"
                        placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
                    <div class="position0" style="width:auto;height:34px">
                        <button class="b-btn taskLogclear clear hide position0" id="job_searchBtn"><i
                                class="icon-close-small"></i></button>
                    </div>
                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                        <button class="b-btn search-btn"><i class="icon-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="table-toolbar-wrapper__right">
                <div class="table-actions-wrapper page-right" style="display: flex;">
                    <button type="button" id="job_log_advanced_search_btn" class="btn btn-primary adv_btn brr2 p-lr8">
                        <i class="adv-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
                    </button>
                    <div class="vin_btnToolbar">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="job_searchDiv" class="searchDiv display-none">
                    <?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span class="searchContent"></span><span
                        class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span> </div>
            </div>
        </div>
        <div class="table-container" id="taskLogDiv">
            <table id="taskLog">

            </table>
        </div>
        <!-- End: life time stats -->

        <!-- BEGIN MODAL -->
        <div id="job_log_advanced_search_modal" class="modal xmodal fade form-horizontal" tabindex="-1" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="fa fa-search"></i>
                    <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="list-option">
                        <div class="row">
                            <label class="control-label col-md-4"><?php echo $LANG['UI_JOB_TIME_RANGE'] ?>
                            </label>
                            <div class="col-md-6 daterangepickerdiv">
                                <input type="text" id="advanced_search_time_range" class="form-control" autocomplete="off">
                                <i class="viconfont vicon-ge_calendar"></i>
                            </div>
                        </div>
                    </div>
                    <!-- 任务名 -->
                    <div class="list-option">
                        <div class="row">
                            <label
                                class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TASK_RNAME']; ?>
                            </label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="task_name" id="advanced_search_task_name">
                            </div>
                        </div>
                    </div>
                    <div class="list-option">
                        <div class="row">
                            <!-- 日志状态 -->
                            <label class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_STATUS'] ?>
                            </label>
                            <div class="col-md-6">
                                <select class="form-control select2me" id="advanced_search_log_status">
                                    <option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                                    <option value="1"><?php echo $LANG['WEB_PLATFORM_DES_NORMAL'] ?></option>
                                    <option value="2"><?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?></option>
                                    <option value="3"><?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- 用户名 -->
                    <div class="list-option">
                        <div class="row">
                            <label
                                class="control-label col-md-4"><?php echo $LANG['UI_LOGIN_USERNAME']; ?>
                            </label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="user_name" id="advanced_search_user_name">
                            </div>
                        </div>
                    </div>
                    <!-- 业务类型/模块类型/任务类型 级联 -->
                    <div class="list-option">
                        <div class="row">
                            <label
                                class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TASK_TYPE']; ?>
                            </label>
                            <div class="col-md-6" id="current_job_advanced_search_cascader">

                            </div>
                        </div>
                    </div>
                    <!-- 虚拟机名 -->
                    <div class="list-option advanced-search-vm-name display-none">
                        <div class="row">
                            <label
                                class="control-label col-md-4"><?php echo $LANG['UI_BACKUP_REPORT_NAME']; ?>
                            </label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="vm_name" id="advanced_search_vm_name">
                            </div>
                        </div>
                    </div>
                    <!-- 虚拟机类型 -->
                    <div class="list-option advanced-search-vm-type display-none">
                        <div class="row">
                            <label
                                class="control-label col-md-4"><?php echo $LANG['UI_VM_TYPE']; ?>
                            </label>
                            <div class="col-md-6">
                                <select class="form-control" id="advanced_search_vm_type"></select>
                            </div>
                        </div>
                    </div>
                    <!-- 数据库类型 -->
                    <div class="list-option advanced-search-db-type display-none">
                        <div class="row">
                            <label
                                class="control-label col-md-4"><?php echo $LANG['UI_DB_DATABASE_TYPE']; ?>
                            </label>
                            <div class="col-md-6">
                                <select class="form-control" id="advanced_search_db_type"></select>
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
                    id="job_log_advanced_search_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END MODAL -->
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript"
    src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript"
    src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/cascader/js/cascader.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    //如果不是英文,加载语言包
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>


<!-- END PAGE LEVEL PLUGINS -->