<?php include_once '../../../tpl/permission.php'; ?>
<?php
session_start();
session_commit();
$authfun = $_SESSION['authfun'];
?>
<!-- BEGIN PAGE HEADER-->
<link rel="stylesheet" type="text/css" href="./css/dbprotect/custom_config.css" />
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="jobs-wrapper">
    <div class="vin_toolbar" id="vin_current_verify_toolbar">
        <div class="left-toolbar">
            <div class="left-toolbar-item me-10">
                <div class="search input-group">
                    <input id="history_job_seach_ipt" class="currentSearch customSearch"
                        style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                        placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
                    <div class="position0" style="width:auto;height:34px">
                        <button class="b-btn clear hide position0" id="history_job_clear_search"><i
                                class="icon-close-small"></i></button>
                    </div>
                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                        <button id="history_job_search_btn" class="b-btn search-btn">
                            <i class="icon-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="left-toolbar-item me-10">
                <div id="history_job_filter_wrapper" class="position-relative"></div>
            </div>

            <div class="left-toolbar-item me-10">
                <div id="history_job_daterangepicker_wrapper" class="position-relative"></div>
            </div>
        </div>

        <div class="flex-items-center">
            <div class="customBtn4"><button class="btn btn-primary b-btn btn-table brr2 mr6 download" id="downloadHistory" title="<?php echo $LANG['UI_ALARM_LOG_DOWNLOAD'] ?>"><i class="log_download"></i></button></div>
            <div class="rightTool">
                <div class="vin_history_verify_btnToolbar"></div>
            </div>
        </div>
    </div>

    <div class="table-container history-job-table-container">
        <table id="history_verify_table"></table>
    </div>
</div>

<!-- END PAGE CONTENT-->

<!-- BEGIN RECOVERY CONTENT MODAL -->
<div id="oracleRecoveryContentModal" class="modal xmodal fade form-horizontal " tabindex="-1"
     data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"> <?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <!-- 恢复内容树 -->
            <div class="form-group pd-20">
                <label class="control-label col-md-2">
                    <?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?>
                </label>
                <div class="col-md-10">
                    <ul id="oracle-recovery-content-tree" class="ztree"></ul>
                </div>
            </div>

            <!-- 文件内容 -->
            <!-- spfile -->
            <div class="form-group pfileDiv pd-20">
                <label class="control-label col-md-2">
                    <?php echo $LANG['UI_DB_RECOVERY_PFILE'] ?>
                </label>
                <div class="col-md-10">
                    <div id="pfile-content"></div>
                </div>
            </div>
            <!-- listener.ora-->
            <div class="form-group listenerDiv pd-20">
                <label class="control-label col-md-2">
                    <?php echo $LANG['UI_DB_RECOVERY_LISTENER'] ?>
                </label>
                <div class="col-md-10">
                    <div id="listener-content"></div>
                </div>
            </div>
            <!-- tnsnames.ora-->
            <div class="form-group tnsnamesDiv pd-20">
                <label class="control-label col-md-2">
                    <?php echo $LANG['UI_DB_RECOVERY_TNSNAMES'] ?>
                </label>
                <div class="col-md-10">
                    <div id="tnsnames-content"></div>
                </div>
            </div>
            <!-- sqlnet.ora-->
            <div class="form-group sqlnetDiv pd-20">
                <label class="control-label col-md-2">
                    <?php echo $LANG['UI_DB_RECOVERY_SQLNET'] ?>
                </label>
                <div class="col-md-10">
                    <div id="sqlnet-content"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">
            <?php echo $LANG['UI_PUBLIC_OFF_TWO']; ?>
        </button>
    </div>
</div>
<!-- END RECOVERY CONTENT MODAL -->

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

<!-- BEGIN HISTORY ERROR DETAIL MODAL -->
<div id="historyErrorDetailModal" class="modal xmodal fade form-horizontal " tabindex="-1"
     data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <?php echo $LANG['WEB_LOG_TASK_ERROR_DETAIL'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
        </div>
    </div>
    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">
            <?php echo $LANG['UI_PUBLIC_OFF_TWO']; ?>
        </button>
    </div>
</div>
<!-- END HISTORY ERROR DETAIL MODAL -->

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

<!-- BEGIN VERIFY JOB REPORT DRAWER -->
<?php
include_once './../industry/job_report.php';
?>
<!-- END VERIFY JOB REPORT DRAWER -->

<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/exportpdf/html2canvas.js"></script>
<script type="text/javascript" src="./scripts/plugins/exportpdf/jspdf.min.js"></script>
<script type="text/javascript" src="./scripts/platform/jobs/verify_history_job.js"></script>
<!-- END PAGE LEVEL PLUGINS -->