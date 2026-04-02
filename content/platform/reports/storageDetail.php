<?php include_once '../../../tpl/permission.php'; ?>

<link href="./css/platform/report.css" rel="stylesheet" type="text/css"/>
<link href="../../../scripts/components/filter/css/filter.css" rel="stylesheet" type="text/css" />
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
    <span class="curent"><?php echo $LANG['UI_PLATFORM_STORAGE_REPORT'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="report-detail">
    <div class="report-detail__header">
        <span class="report-detail__header__title">
            <i class="viconfont vicon-ge_backup_client me-4"></i>
            <span class="report-detail__header__title__text"><?php echo $LANG['UI_PLATFORM_STORAGE_REPORT'] ?></span>
        </span>
        <button type="button" id="storage_export" class="btn green-haze <?php if (!in_array('p_vm_report_export', $_SESSION['permissionArr'])) {
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
        <div class="report-detail-boxes client-detail-boxes display-none">
            <div class="report-detail-boxes__item box-wrapper storage-total-box display-none">
                <div class="box-wrapper__left">
                    <span class="box-wrapper__left__nums" id="storage_total"></span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_TOTAL_STORAGES'] ?></span>
                </div>
                <div class="box-wrapper__right storage-total-svg"></div>
            </div>
            <div class="report-detail-boxes__item box-wrapper storage-online-box display-none">
                <div class="box-wrapper__left">
                    <span class="box-wrapper__left__nums" id="storage_online"></span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_ONLINE_STORAGES'] ?></span>
                </div>
                <div class="box-wrapper__right storage-online-svg"></div>
            </div>
            <div class="report-detail-boxes__item box-wrapper storage-offline-box display-none">
                <div class="box-wrapper__left">
                    <span class="box-wrapper__left__nums" id="storage_offline"></span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_OFFLINE_STORAGES'] ?></span>
                </div>
                <div class="box-wrapper__right storage-offline-svg"></div>
            </div>
            <div class="report-detail-boxes__item box-wrapper storage-backup-data-box display-none">
                <div class="box-wrapper__left">
                    <span class="nums-wrapper">
                        <span class="box-wrapper__left__nums" id="storage_backup_data"></span>
                        <span class="box-wrapper__left__unit" id="storage_backup_data_unit"></span>
                    </span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_COPY_SOURCE_BACKUP_DATA'] ?></span>
                </div>
                <div class="box-wrapper__right backup-data-svg"></div>
            </div>
            <div class="report-detail-boxes__item box-wrapper storage-copy-data-box display-none">
                <div class="box-wrapper__left">
                    <span class="nums-wrapper">
                        <span class="box-wrapper__left__nums" id="storage_copy_data"></span>
                        <span class="box-wrapper__left__unit" id="storage_copy_data_unit"></span>
                    </span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_COPY_SOURCE_COPY_DATA'] ?></span>
                </div>
                <div class="box-wrapper__right backup-copy-svg"></div>
            </div>
            <div class="report-detail-boxes__item box-wrapper storage-archive-data-box display-none">
                <div class="box-wrapper__left">
                    <span class="nums-wrapper">
                        <span class="box-wrapper__left__nums" id="storage_archive_data"></span>
                        <span class="box-wrapper__left__unit" id="storage_archive_data_unit"></span>
                    </span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_ARCHIVE_DATA'] ?></span>
                </div>
                <div class="box-wrapper__right backup-archive-svg"></div>
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
                        <a href="#backup_tab" data-toggle="tab" aria-expanded="true"><?php echo $LANG['WEB_PLATFORM_DES_BACKUP'] ?></a>
                    </li>
                    <li class="nav-item" data-type="2">
                        <a href="#copy_tab" data-toggle="tab" aria-expanded="true"><?php echo $LANG['WEB_PLATFORM_DES_COPY'] ?></a>
                    </li>
                    <li class="nav-item" data-type="3">
                        <a href="#archive_tab" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_PLATFORM_ARCHIVE'] ?></a>
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
                <div class="tab-pane fade in active" id="backup_tab">
                    <div class="tab-content__echart" id="storage_report_backup_chart"></div>
                    <div class="tab-content__nodata display-none">
                        <div class="tab-content__nodata__img">
                            <img src="./img/platform/report/nodata.svg" alt="">
                        </div>
                        <div class="tab-content__nodata__info"><?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="copy_tab">
                    <div class="tab-content__echart" id="storage_report_copy_chart"></div>
                        <div class="tab-content__nodata display-none">
                            <div class="tab-content__nodata__img">
                                <img src="./img/platform/report/nodata.svg" alt="">
                            </div>
                        <div class="tab-content__nodata__info"><?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="archive_tab">
                    <div class="tab-content__echart" id="storage_report_archive_chart"></div>
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
                        <div class="tool-left-item">
                            <div class="search input-group">
                                <input id="search" class="currentSearch customSearch"
                                    style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                                    placeholder="<?php echo $LANG['UI_SEARCH_AS_STORAGE_NAME'] ?>">
                                <div class="position0" style="width:auto;height:34px">
                                    <button class="b-btn clear hide position0" id="report_clear_search"><i
                                            class="icon-close-small"></i></button>
                                </div>
                                <div class="search-btn positionL0" style="width:auto;height:34px;">
                                    <button id="report_search" class="b-btn search-btn"><i class="icon-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="tool-left-item">
                            <div id="storage_report_filter_wrapper" class="position-relative"></div>
                        </div>
                    </div>
                </div>
                <div class="vin_toolbar mb-0">
                    <div class="vin_btnToolbar"></div>
                </div>
            </div>
            <div class="data-detail-wrapper__content">
                <table id="storage_report_table"></table>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/reports/storageDetail.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/echarts/V5.4.3/echarts.min.js"></script>
<script type="text/javascript" src="./scripts/components/filter/js/filter.js"></script>
<!-- END PAGE LEVEL PLUGINS -->