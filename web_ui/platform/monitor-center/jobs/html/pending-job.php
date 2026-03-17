<!--挂起任务列表-->
<style>
    #current_pending_table tr td{
        cursor: grab;
    }
    .change_sort{
        color:#0FBF98;
        padding:6px 10px;
        width: 28px;
        height: 28px;
        background-color: rgba(15, 191, 152, 0.10);
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 2px 2px 2px 2px !important;
    }
    .change_sort:hover{
        color:#0FBF98;
    }
</style>
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="current_pending_task_drawer" style="width: 80%">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top;padding-left:0;font-weight:400;font-size:16px;">
                <i class="viconfont vicon-Frame10" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['WEB_TASK_PENDING'];?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <!-- BEGIN FORM-->
        <div class="drawer-body form-horizontal">
            <div class="portlet-body" style="padding: 0 !important;">
                <div class="jobs-wrapper">
                    <div class="vin_toolbar" id="vin_current_pending_toolbar">
                        <div class="left-toolbar">
                            <div class="left-toolbar-item me-10">
                                <div class="search input-group">
                                    <input id="current_job_pending_seach_ipt" class="currentPendingSearch customSearch"
                                           style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                                           placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
                                    <div class="position0" style="width:auto;height:34px">
                                        <button class="b-btn clear hide position0" id="current_job_pending_clear_search"><i
                                                    class="icon-close-small"></i></button>
                                    </div>
                                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                                        <button id="current_job_pending_search_btn" class="b-btn search-btn"><i
                                                    class="icon-search"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="left-toolbar-item">
                                <div id="current_job_pending_filter_wrapper" class="position-relative"></div>
                            </div>

                            <div class="left-toolbar-item">
                                <div id="current_job_pending_daterangepicker_wrapper" class="position-relative"></div>
                            </div>
                        </div>

                        <div class="flex-items-center">
                            <div class="rightTool">
                                <div class="vin_pending_btnToolbar">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-container current-job-table-container">
                        <table id="current_pending_table"></table>
                    </div>
                </div>
            </div>
            <div class="alert alert-block alert-info fade in mt15" id="marktips_pending" style="display: block;margin-bottom: 0;">
                <button type="button" class="close" data-dismiss="alert"></button>
                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                <ol class="alert-ol">
                    <li><?php echo $LANG['WEB_TASK_PENDING_HELP_TIPS1']?></li>
                    <li><?php echo $LANG['WEB_TASK_PENDING_HELP_TIPS2']?></li>
                    <li><?php echo $LANG['WEB_TASK_PENDING_HELP_TIPS3']?></li>
                </ol>
            </div>
        </div>
        <div class="drawer-footer">
            <div id="bottom-btn">
                <button type="button" id="sortSubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default" style="margin-right:0;"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
            </div>
        </div>
    </div>
</div>
<!-- END MODAL -->
<script type="text/javascript" src="./assets/global/plugins/sortable/Sortable.js"></script>
<script type="text/javascript" src="./scripts/platform/jobs/cache_table.js"></script>
<script type="text/javascript" src="./scripts/platform/jobs/pending_job.js"></script>
