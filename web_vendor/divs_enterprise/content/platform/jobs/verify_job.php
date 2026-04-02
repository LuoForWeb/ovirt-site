<?php include_once '../../../tpl/permission.php'; ?>

<!-- BEGIN TABPANE CONTENT-->
<div class="jobs-wrapper">
    <div class="vin_toolbar" id="vin_current_verify_toolbar">
        <div class="left-toolbar">
            <div class="left-toolbar-item me-10">
                <div class="search input-group">
                    <input id="current_job_seach_ipt" class="currentSearch customSearch"
                        style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                        placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
                    <div class="position0" style="width:auto;height:34px">
                        <button class="b-btn clear hide position0" id="current_job_clear_search"><i
                                class="icon-close-small"></i></button>
                    </div>
                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                        <button id="current_job_search_btn" class="b-btn search-btn"><i
                                class="icon-search"></i></button>
                    </div>
                </div>
            </div>

            <div class="left-toolbar-item me-10">
                <button id="verify_new_task" class="btn btn-dropdown-menu dropdown-toggle">
                    <i class="viconfont vicon-biaogetianjia me-4"></i><?php echo $LANG['UI_PUBLIC_ADD'] ?>
                </button>
            </div>

            <div class="left-toolbar-item me-10">
                <div id="current_job_filter_wrapper" class="position-relative"></div>
            </div>

            <div class="left-toolbar-item me-10">
                <div id="current_job_daterangepicker_wrapper" class="position-relative"></div>
            </div>
        </div>

        <div class="flex-items-center">
            <div class="rightTool">
                <div class="vin_btnToolbar">
                </div>
            </div>
        </div>
    </div>

    <div class="table-container current-job-table-container">
        <table id="current_verify_table"></table>
    </div>

    <div class="alert alert-block alert-info fade in h-50px mb-0" id="marktips">
        <div class="alert-info-wrapper">
            <span class="alert-info-wrapper__label"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</span>
            <span class="alert-info-wrapper__content"><?php echo $LANG['UI_JOB_CURRENT_NAME_TIPS'] ?></span>
        </div>
        <button type="button" class="close" data-dismiss="alert" id="close_tips_btn"></button>
    </div>
</div>

<!-- EMD TABPANE CONTENT -->

<script src="./scripts/platform/jobs/verify_jobs.js"></script>