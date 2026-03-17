<?php include_once '../../../tpl/permission.php'; ?>
<?php
$userAllPermission = $_SESSION['permissionArr'];
?>
<!-- BEGIN PAGE HEADER-->
<link rel="stylesheet" type="text/css" href="./css/dbprotect/custom_config.css" />
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="jobs-wrapper">
    <div class="vin_toolbar" id="vin_history_toolbar">
        <div class="left-toolbar">
            <div class="left-toolbar-item me-10 <?php if (!in_array('p_history_job_delete', $userAllPermission)) {
                echo 'display-none';
            } ?>">
                <button type="button" id="delete_history_task_btn" disabled class="btn btn-toolbar-delete disabled">
                    <i class="viconfont vicon-a-Deleteshanchu1"></i>
                </button>
            </div>
            <div class="left-toolbar-item me-10"> 
                <div class="search input-group">
                    <input id="history_job_seach_ipt" class="table-toolbar-search-ipt" autocomplete="off" type="text"
                        placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
                    <div class="position0" style="width:auto;height:34px">
                        <button class="b-btn clear hide position0" id="history_job_clear_search"><i
                                class="icon-close-small"></i></button>
                    </div>
                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                        <button id="history_job_search_btn" class="b-btn search-btn"><i
                                class="icon-search"></i></button>
                    </div>
                </div>
            </div>

            <div class="left-toolbar-item me-10">
                <div id="history_job_filter_wrapper" class="position-relative"></div>
            </div>

            <div class="left-toolbar-item">
                <div id="history_job_daterangepicker_wrapper" class="position-relative"></div>
            </div>
        </div>

        <div class="flex-items-center">
            <div class="position-relative me-8" id="history_job_advanced_search_wrapper"></div>
            <div class="customBtn4 <?php if (!in_array('p_history_job_download', $userAllPermission)) {
                echo 'display-none';
            } ?>">
                <button class="btn btn-primary b-btn btn-table brr2 mr6 download" id="downloadHistory" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" title="<?php echo $LANG['UI_ALARM_LOG_DOWNLOAD'] ?>">
                    <i class="log_download"></i>
                </button>
            </div>
            <div class="rightTool">
                <div class="vin_history_btnToolbar"></div>
            </div>
        </div>
    </div>
    <div class="table-container history-job-table-container">
        <table id="history_table">
        </table>
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN ADVANCED SEARCH MODAL -->
<div id="history_advanced_search_modal" class="modal xmodal fade form-horizontal" tabindex="-1" data-backdrop="static" aria-hidden="true">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="fa fa-search"></i>
            <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <!-- 任务名 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TASK_RNAME']; ?>：
                    </label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="task_name" id="history_advanced_search_task_name">
                    </div>
                </div>
            </div>
            <!-- 用户名 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_LOGIN_USERNAME']; ?>：
                    </label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="user_name" id="history_advanced_search_user_name">
                    </div>
                </div>
            </div>
            <!-- 主机名 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_AGENT_HOST_NAME']; ?>：
                    </label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="host_name" id="history_advanced_search_host_name">
                    </div>
                </div>
            </div>
            <!-- 业务类型/模块类型/任务类型 级联 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TASK_TYPE']; ?>：
                    </label>
                    <div class="col-md-6" id="history_job_advanced_search_cascader">

                    </div>
                </div>
            </div>
            <!-- 虚拟机名 -->
            <div class="list-option history-advanced-search-vm-name display-none">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_BACKUP_REPORT_NAME']; ?>：
                    </label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="vm_name" id="history_advanced_search_vm_name">
                    </div>
                </div>
            </div>
            <!-- 虚拟机类型 -->
            <div class="list-option history-advanced-search-vm-type display-none">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_VM_TYPE']; ?>：
                    </label>
                    <div class="col-md-6">
                        <select class="form-control" id="history_advanced_search_vm_type"></select>
                    </div>
                </div>
            </div>
            <!-- 数据库类型 -->
            <div class="list-option history-advanced-search-db-type display-none">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_DB_DATABASE_TYPE']; ?>：
                    </label>
                    <div class="col-md-6">
                        <select class="form-control" id="history_advanced_search_db_type"></select>
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
            id="history_job_advanced_search_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END ADVANCED SEARCH MODAL -->

<!-- 跳过文件详情模态框开始 -->
<div id="passFileModal" style="z-index: 100000;top: 400px;" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="viconfont vicon-ge_summary" style="color: gray;margin-right: 8px;"></i><?php echo $LANG['UI_VERIFY_CHECK_DETAILS']; ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="btn-group">
                <button type="button" id="downloadTxt" class="btn btn-sm green-haze">
                    <i class="fa fa-download"></i><?php echo $LANG['UI_FILE_DOWNLOAD_SKIP_FILE']; ?>
                </button>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3" style="text-align: left;"><?php echo $LANG['UI_FILE_SKIP_FILE_DETAILS']; ?>:</label>
                <div class="col-md-12">
                    <table class="table table-striped table-bordered" style="margin-bottom: 0;">
                        <thead style="background-color: #dcdcdca1;">
                        <th><?php echo $LANG['UI_FILE_SKIP_FILE_NUM']; ?></th>
                        <th><?php echo $LANG['UI_FILE_SKIP_FILE_RATIO']; ?></th>
                        <th><?php echo $LANG['UI_FILE_SKIP_DIRECTORY_NUM']; ?></th>
                        <th><?php echo $LANG['UI_FILE_SKIP_DIRECTORY_RATIO']; ?></th>
                        <th><?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_NUM']; ?></th>
                        <th><?php echo $LANG['UI_FILE_SKIP_FILE_ALARM_RATIO']; ?></th>
                        </thead>
                        <tbody>
                        <tr class="passdetailTr">
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3" style="text-align: left;"><?php echo $LANG['UI_FILE_SKIP_REASON_DETAILS']; ?>:</label>
                <div class="col-md-12 passreasonTable">
                    <table class="table table-striped table-bordered" >
                        <thead style="background-color: #dcdcdca1;">
                        <th><?php echo $LANG['UI_FILE_OCCUPIED_FILE_NUM']; ?></th>
                        <th><?php echo $LANG['UI_FILE_DELETE_FILE_NUM']; ?></th>
                        <th><?php echo $LANG['UI_FILE_NO_PERMISSION_FILE_NUM']; ?></th>
                        <th><?php echo $LANG['UI_FILE_NO_PERMISSION_DIRECTORY_NUM']; ?></th>
                        <th><?php echo $LANG['UI_FILE_DELETE_DIRECTORY_NUM']; ?></th>
                        <th><?php echo $LANG['UI_FILE_OTHER_NUM']; ?></th>
                        </thead>
                        <tbody>
                        <tr class="passreasonTr">
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        <!-- <button type="button"class="btn btn-primary" id="mountsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button> -->
    </div>
</div>
<!-- 跳过文件详情模态框结束 -->

<!-- 跳过文件详情drawer开始 -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px" aria-labelledby="obs_detail_drawer_title" aria-hidden="true" id="passFileDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <div class="drawer-title" id="obs_detail_drawer_title"
                style="display:flex;justify-content:space-between;align-items:center">
                <div class="drawer-title-left">
                    <i class="viconfont vicon-tenant-detail mr10"></i>
                    <span><?php echo $LANG['UI_FILE_SKIP_FILE_DETAILS'] ?></span>
                </div>
                <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                    <i class="viconfont vicon-guanbi"></i>
                </div>
            </div>
        </div>
        <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
        <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
            <!-- 跳过文件详情 -->
            <div class="config-detail-wrap__group strategy-group">
                <!-- 小标题 -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['UI_FILE_SKIP_FILE_STATISTICS'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 跳过文件个数 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_FILE_NUM'] ?>
                        </div>
                        <div id="file_skip_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过文件比例 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_FILE_RATIO'] ?>
                        </div>
                        <div id="file_skip_radio" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过目录个数 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_DIRECTORY_NUM'] ?>
                        </div>
                        <div id="dir_skip_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过目录比例 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_DIRECTORY_RATIO'] ?>
                        </div>
                        <div id="dir_skip_radio" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过文件告警个数 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_NUM'] ?>
                        </div>
                        <div id="file_skip_alarm_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过文件告警比例 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_RATIO'] ?>
                        </div>
                        <div id="file_skip_alarm_radio" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END SPEED LIMIT STRATEGY -->
            </div>
            <!-- 跳过文件详情 -->

            <!-- 具体跳过原因 -->
            <div class="config-detail-wrap__group strategy-group">
                <!-- 小标题 -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['UI_FILE_SKIP_REASON_DETAILS'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 被占用文件个数 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                        <?php echo $LANG['UI_FILE_OCCUPIED_FILE_NUM'] ?></div>
                        <div id="file_occupied_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 被删除文件个数 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_DELETE_FILE_NUM'] ?></div>
                        <div id="file_delete_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 无权限文件个数 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_NO_PERMISSION_FILE_NUM'] ?></div>
                        <div id="no_permission_file_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                     <!-- 无权限目录个数 -->
                     <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_NO_PERMISSION_DIRECTORY_NUM'] ?></div>
                        <div id="no_permission_dir_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                     <!-- 被删除目录个数 -->
                     <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_DELETE_DIRECTORY_NUM'] ?></div>
                        <div id="dir_delete_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                     <!-- 其他个数 -->
                     <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_OTHER_NUM'] ?></div>
                        <div id="other_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
            </div>
            <!-- 具体跳过原因 -->
        </div>
        <!-- END BACKUP TASK DETAIL DRAWER BODY -->

        <div class="drawer-footer">
            <button type="button" class="btn btn-primary" id="downloadDrawer" style="width: 128px;"><i class="viconfont vicon-baogaoxiazaishijian"></i><?php echo $LANG['UI_FILE_DOWNLOAD_SKIP_FILE'] ?></button>
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        </div>
    </div>
</div>
<!-- 跳过文件详情drawer结束 -->

<!-- BEGIN MODAL -->
<div id="reportModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
    data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <?php echo $LANG['UI_VERIFY_REPORT'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div id="reportContent">
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">
            <?php echo $LANG['UI_PUBLIC_NO'] ?>
        </button>
        <button type="button" class="btn btn-primary" id="downloadReport">
            <?php echo $LANG['UI_VERIFY_REPORT_DOWNLOAD'] ?>
        </button>
        <button type="button" class="btn btn-primary" id="reportEmail">
            <?php echo $LANG['UI_VERIFY_REPORT_SEND_TO_EMAIL'] ?>
        </button>
    </div>
</div>
<!-- END MODAL -->

<!-- BEGIN DRAWER -->
<!-- BEGIN SCRIPT CONTENT DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="scriptContentDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title">
                <span>
                    <i class="viconfont vicon-jiaobenguanli"></i>
                </span>
                <span style="position: relative; top: -1px" class="name"></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <div class="drawer-body">
            <div class="portlet-body" style="height: 100%">
                <div class="form-group" style="height: 20px; margin-bottom: 16px">
                    <label class="col-md-3" for=""><?php echo $LANG['UI_PUBLIC_SCRIPT_TYPE'] ?></label>
                    <div class="col-md-6">
                        <div id="scriptContentType"></div>
                    </div>
                </div>
                <div class="form-group" style="height: 20px; margin-bottom: 16px">
                    <label class="col-md-3" for=""><?php echo $LANG['UI_PUBLIC_SCRIPT_RESULT'] ?></label>
                    <div class="col-md-6">
                        <div id="scriptContentResult"></div>
                    </div>
                </div>
                <div class="col-md-12 pd0" style="height: calc(100% - 72px)">
                    <pre id="scriptContent" style="height: 100%"></pre>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <button type="button" class="btn default cancel">
                    <?php echo $LANG['UI_PUBLIC_CLOSE'] ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END SCRIPT CONTENT DRAWER -->

<!-- BEGIN DB VALIDATE REPORT DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="dbValidateReportDrawer" style="width: 800px">
    <div class="drawer-content drawer-content-scrollable" role="document" data-db-index="">
        <!-- header -->
        <div class="drawer-header" style="padding: 16px 20px">
            <h4 class="drawer-title" style="margin: 0; height: 20px;">
                <span>
                    <i class="viconfont vicon-baogaoliebiao"></i>
                </span>
                <span style="position: relative; top: -1px; font-size: 16px; color: #333;" class="name"><?php echo $LANG['UI_DB_RECOVERY_VALIDATE_REPORT'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <!-- body -->
        <div class="drawer-body" style="padding: 40px 28px">
            <div class="portlet-body" style="height: 100%">
                <div id="dbValidateReport"></div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="col-md-9" style="float: right; padding-right: 10px;">
                    <button type="button" class="btn green-haze btn-confirm send-email" style="width: 100px">
                        <i class="viconfont vicon-fasongyoujian"></i>
                        <span><?php echo $LANG['UI_PUBLIC_SEND_EMAIL'] ?></span>
                    </button>
                    <button type="button" class="btn green-haze btn-confirm download" style="width: 72px">
                        <i class="viconfont vicon-baogaoxiazaishijian"></i>
                        <span><?php echo $LANG['UI_PUBLIC_DOWNLOAD'] ?></span>
                    </button>
                    <button type="button" class="btn default cancel"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END DB VALIDATE REPORT DRAWER -->

<!-- BEGIN VIEW DRAWER -->
<div class="drawer slide width600" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title"
    id="view_log_drawer">
    <form id="view_form" class="drawer-content drawer-content-scrollable form-horizontal" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-2-title">
                <i class="viconfont vicon-log mr8 iconList"></i>
                <span><?php echo $LANG['UI_JOB_RUNING_LOG'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body history-running-logs-drawer-body" id="history_logs_drawer_body">
            <div class="logs-container" id="logs_container"></div>
        </div>
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
            </button>
        </div>
    </form>
</div>
<!-- END VIEW DRAWER -->

<!-- BEGIN RECOVERY CONTENT DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="oracleRecoveryContentDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <!-- header -->
        <div class="drawer-header">
            <h4 class="drawer-title">
                <span>
                    <i class="viconfont vicon-gaojipeizhi"></i>
                </span>
                <span class="name"><?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <!-- body -->
        <div class="drawer-body">
            <div class="portlet-body">
                <!-- 恢复内容树 -->
                <div class="form-group">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?>
                    </label>
                    <div class="col-md-10">
                        <ul id="oracle-recovery-content-tree" class="ztree"></ul>
                    </div>
                </div>

                <!-- 文件内容 -->
                <!-- spfile -->
                <div class="form-group pfileDiv">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_RECOVERY_PFILE'] ?>
                    </label>
                    <div class="col-md-10">
                        <div id="pfile-content"></div>
                    </div>
                </div>
                <!-- listener.ora-->
                <div class="form-group listenerDiv">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_RECOVERY_LISTENER'] ?>
                    </label>
                    <div class="col-md-10">
                        <div id="listener-content"></div>
                    </div>
                </div>
                <!-- tnsnames.ora-->
                <div class="form-group tnsnamesDiv">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_RECOVERY_TNSNAMES'] ?>
                    </label>
                    <div class="col-md-10">
                        <div id="tnsnames-content"></div>
                    </div>
                </div>
                <!-- sqlnet.ora-->
                <div class="form-group sqlnetDiv">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_RECOVERY_SQLNET'] ?>
                    </label>
                    <div class="col-md-10">
                        <div id="sqlnet-content"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn default cancel" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END RECOVERY CONTENT DRAWER -->

<!-- BEGIN HISTORY ERROR DETAIL DRAWER -->
<div id="historyErrorDetailDrawer" class="drawer slide" tabindex="-1" data-placement="right" role="dialog">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <!-- header -->
        <div class="drawer-header">
            <h4 class="drawer-title">
                <i class="viconfont vicon-tenant-detail"></i>
                <span class="text"><?php echo $LANG['WEB_LOG_TASK_ERROR_DETAIL']; ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <!-- body -->
        <div class="drawer-body">
            <div class="portlet-body">
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn default cancel" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END HISTORY ERROR DETAIL DRAWER -->

<!-- END DRAWER -->

<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/exportpdf/html2canvas.js"></script>
<script type="text/javascript" src="./scripts/plugins/exportpdf/jspdf.min.js"></script>
<script type="text/javascript" src="./scripts/platform/jobs/history_job.js"></script>
<!-- END PAGE LEVEL PLUGINS -->