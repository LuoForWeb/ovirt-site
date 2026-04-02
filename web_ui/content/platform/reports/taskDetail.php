<?php include_once '../../../tpl/permission.php'; ?>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/cascader/css/cascader.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/filter/css/filter.css" />
<link href="./css/platform/report.css" rel="stylesheet" type="text/css"/>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="storage_report" href="./content/platform/reports/strategy.php">
            <span><?php echo $LANG['UI_REPORT_TEMPLATE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_TASK_REPORT'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="report-detail">
    <div class="report-detail__header">
        <span class="report-detail__header__title">
            <i class="viconfont vicon-ge_backup_client me-4"></i>
            <span class="report-detail__header__title__text"><?php echo $LANG['UI_TASK_REPORT'] ?></span>
        </span>
        <button type="button" id="task_export" class="btn green-haze <?php if (!in_array('p_vm_report_export', $_SESSION['permissionArr'])) {
            echo 'display-none';
        } ?>">
            <span class="indicator-label">
                <i class="viconfont vicon-a-Share-threefenxiang3 me-2"></i>
                <?php echo $LANG['UI_PUBLIC_EXPORT'] ?>
            </span>
            <span class="indicator-progress">
                <span class="spinner-border spinner-border-sm me-2"></span><?php echo $LANG['UI_EXPORTING_REPORT'] ?>
            </span>
        </button>
    </div>
    <div class="report-detail__content hover-scroll-y">
        <div class="report-detail-header overview-header display-none">
            <span class="decoration me-8"></span>
            <span class="report-detail-header__text"><?php echo $LANG['UI_REPORT_DATA_OVERVIEW'] ?></span>
        </div>
        <div class="task-wrapper display-none">
            <div class="charts-wrapper__toolbar">
                <ul class="nav nav-pills" id="task_type_navs">
                    <li class="nav-item active" data-type="1">
                        <a href="#current_job_tab" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_TASK_OVERVIEW'] ?></a>
                    </li>
                    <li class="nav-item" data-type="2">
                        <a href="#history_job_tab" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_HISTORY_OVERVIEW'] ?></a>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade in active" id="current_job_tab">
                    <div class="report-detail-boxes">
                        <div class="report-detail-boxes__item box-wrapper task-total-box display-none">
                            <div class="box-wrapper__left">
                                <span class="box-wrapper__left__nums" id="task_total"></span>
                                <span class="box-wrapper__left__des"><?php echo $LANG['UI_HOMEPAGE_TASK_NUM'] ?></span>
                            </div>
                            <div class="box-wrapper__right task-total-svg"></div>
                        </div>
                        <div class="report-detail-boxes__item box-wrapper task-success-box display-none">
                            <div class="box-wrapper__left">
                                <span class="box-wrapper__left__nums" id="task_success"></span>
                                <span class="box-wrapper__left__des"><?php echo $LANG['UI_REPORT_SUCCESS_TASK'] ?></span>
                            </div>
                            <div class="box-wrapper__right task-success-svg"></div>
                        </div>
                        <div class="report-detail-boxes__item box-wrapper task-abnormal-box display-none">
                            <div class="box-wrapper__left">
                                <span class="box-wrapper__left__nums text-warning" id="task_abnormal"></span>
                                <span class="box-wrapper__left__des"><?php echo $LANG['UI_REPORT_ABNORMAL_TASK'] ?></span>
                            </div>
                            <div class="box-wrapper__right task-abnormal-svg"></div>
                        </div>
                        <div class="report-detail-boxes__item box-wrapper task-failed-box display-none">
                            <div class="box-wrapper__left">
                                <span class="box-wrapper__left__nums text-danger" id="task_failed"></span>
                                <span class="box-wrapper__left__des"><?php echo $LANG['UI_REPORT_FAIL_TASK'] ?></span>
                            </div>
                            <div class="box-wrapper__right task-failed-svg"></div>
                        </div>
                        <div class="report-detail-boxes__item box-wrapper task-stoped-box display-none">
                            <div class="box-wrapper__left">
                                <span class="box-wrapper__left__nums" id="task_stoped"></span>
                                <span class="box-wrapper__left__des"><?php echo $LANG['API_CODE_JOBS_STOP'] ?></span>
                            </div>
                            <div class="box-wrapper__right task-stoped-svg"></div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="history_job_tab">
                    <div class="report-detail-boxes">
                        <div class="report-detail-boxes__item box-wrapper task-total-box display-none">
                            <div class="box-wrapper__left">
                                <span class="box-wrapper__left__nums" id="history_task_total"></span>
                                <span class="box-wrapper__left__des"><?php echo $LANG['UI_HOMEPAGE_TASK_NUM'] ?></span>
                            </div>
                            <div class="box-wrapper__right task-total-svg"></div>
                        </div>
                        <div class="report-detail-boxes__item box-wrapper task-success-box display-none">
                            <div class="box-wrapper__left">
                                <span class="box-wrapper__left__nums" id="history_task_success"></span>
                                <span class="box-wrapper__left__des"><?php echo $LANG['UI_REPORT_SUCCESS_TASK'] ?></span>
                            </div>
                            <div class="box-wrapper__right task-success-svg"></div>
                        </div>
                        <div class="report-detail-boxes__item box-wrapper task-abnormal-box display-none">
                            <div class="box-wrapper__left">
                                <span class="box-wrapper__left__nums text-warning" id="history_task_abnormal"></span>
                                <span class="box-wrapper__left__des"><?php echo $LANG['UI_REPORT_ABNORMAL_TASK'] ?></span>
                            </div>
                            <div class="box-wrapper__right task-abnormal-svg"></div>
                        </div>
                        <div class="report-detail-boxes__item box-wrapper task-failed-box display-none">
                            <div class="box-wrapper__left">
                                <span class="box-wrapper__left__nums text-danger" id="history_task_failed"></span>
                                <span class="box-wrapper__left__des"><?php echo $LANG['UI_REPORT_FAIL_TASK'] ?></span>
                            </div>
                            <div class="box-wrapper__right task-failed-svg"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="report-detail-header echart-header display-none">
            <span class="decoration me-8"></span>
            <span class="report-detail-header__text"><?php echo $LANG['UI_REPORT_RENCENTLY_OPERATE'] ?></span>
        </div>
        <div class="charts-wrapper echart-wrapper display-none">
            <div class="charts-wrapper__toolbar">
                <ul class="nav nav-pills" id="task_status_navs">
                    <li class="nav-item active" data-type="1">
                        <a href="#all_tab" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_PUBLIC_ALL'] ?></a>
                    </li>
                    <li class="nav-item" data-type="2">
                        <a href="#success_tab" data-toggle="tab" aria-expanded="true"><?php echo $LANG['WEB_PUBLIC_SUCCESS'] ?></a>
                    </li>
                    <li class="nav-item" data-type="3">
                        <a href="#failed_tab" data-toggle="tab" aria-expanded="true"><?php echo $LANG['WEB_PUBLIC_FAILURE'] ?></a>
                    </li>
                </ul>
                <select class="charts-wrapper__toolbar__select form-control" id="task_running_time_select">
                    <option value="1"><?php echo $LANG['UI_NEARLY_A_DAY'] ?></option>
                    <option value="2"><?php echo $LANG['UI_NEARLY_THREE_DAY'] ?></option>
                    <option value="3"><?php echo $LANG['UI_NEARLY_A_WEEK'] ?></option>
                    <option selected value="4"><?php echo $LANG['UI_NEARLY_A_MONTH'] ?></option>
                </select>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade in active" id="all_tab">
                    <div class="tab-content__echart" id="task_report_all_chart"></div>
                    <div class="tab-content__nodata display-none">
                        <div class="tab-content__nodata__img">
                            <img src="./img/platform/report/nodata.svg" alt="">
                        </div>
                        <div class="tab-content__nodata__info"><?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="success_tab">
                    <div class="tab-content__echart" id="task_report_success_chart"></div>
                        <div class="tab-content__nodata display-none">
                            <div class="tab-content__nodata__img">
                                <img src="./img/platform/report/nodata.svg" alt="">
                            </div>
                        <div class="tab-content__nodata__info"><?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="failed_tab">
                    <div class="tab-content__echart" id="task_report_failed_chart"></div>
                        <div class="tab-content__nodata display-none">
                            <div class="tab-content__nodata__img">
                                <img src="./img/platform/report/nodata.svg" alt="">
                            </div>
                        <div class="tab-content__nodata__info"><?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-detail-header data-detail-header">
            <span class="decoration me-8"></span>
            <span class="report-detail-header__text"><?php echo $LANG['UI_REPORT_DATA_DETAIL'] ?></span>
        </div>
        <div class="data-detail-wrapper">
            <div class="data-detail-wrapper__toolbar toolbar-wrap">
                <div class="obs-manager-toolbar">
                    <div class="tool-left">
                        <div class="tool-left-item me-8">
                            <div class="search input-group">
                                <input id="search" class="currentSearch customSearch"
                                    style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                                    placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
                                <div class="position0" style="width:auto;height:34px">
                                    <button class="b-btn clear hide position0" id="report_clear_search"><i
                                            class="icon-close-small"></i></button>
                                </div>
                                <div class="search-btn positionL0" style="width:auto;height:34px;">
                                    <button id="report_search" class="b-btn search-btn"><i class="icon-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="tool-left-item tool-cascader display-none me-8">
                            <div id="task_report_cascader"></div>
                        </div>
                        <div class="tool-left-item tool-filter display-none">
                            <div id="task_report_filter_wrapper" class="position-relative"></div>
                        </div>
                    </div>
                </div>
                <div class="vin_toolbar mb-0">
                    <div class="vin_btnToolbar"></div>
                </div>
            </div>
            <div class="data-detail-wrapper__content">
                <table id="task_report_table"></table>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN ADVANCED SEARCH MODAL -->
<script type="text/javascript" src="./assets/global/plugins/cascader/js/cascader.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/echarts/V5.4.3/echarts.min.js"></script>
<script type="text/javascript" src="./scripts/components/filter/js/filter.js"></script>
<script type="text/javascript" src="./scripts/platform/reports/taskDetail.js"></script>
<!-- END PAGE LEVEL PLUGINS -->