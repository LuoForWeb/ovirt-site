<?php include_once '../../../tpl/permission.php'; ?>

<link href="./css/platform/report.css" rel="stylesheet" type="text/css"/>
<link href="./scripts/components/filter/css/filter.css" rel="stylesheet" type="text/css" />
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
    <span class="curent"><?php echo $LANG['UI_REPORT_PUBLIC_REPORT'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="report-detail">
    <div class="report-detail__header">
        <span class="report-detail__header__title">
            <i class="viconfont vicon-vmprotect me-4"></i>
            <span class="report-detail__header__title__text"><?php echo $LANG['UI_REPORT_PUBLIC_REPORT'] ?></span>
        </span>
        <button type="button" id="public_cloud_export" class="btn green-haze <?php if (!in_array('p_vm_report_export', $_SESSION['permissionArr'])) {
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
        <div class="report-detail-boxes display-none">
            <div class="report-detail-boxes__item box-wrapper vm-total-box display-none">
                <div class="box-wrapper__left">
                    <span class="box-wrapper__left__nums" id="vm_total_num"></span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_REPORT_DATA_REPORT_NUMBER'] ?></span>
                </div>
                <div class="box-wrapper__right vm-total-svg"></div>
            </div>
            <div class="report-detail-boxes__item box-wrapper vm-protected-box display-none">
                <div class="box-wrapper__left">
                    <span class="box-wrapper__left__nums" id="vm_protected_num"></span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_VISUAL_PROTECT_EG'] ?></span>
                </div>
                <div class="box-wrapper__right vm-protected-svg"></div>
            </div>
            <div class="report-detail-boxes__item box-wrapper vm-platform-box display-none">
                <div class="box-wrapper__left">
                    <span class="box-wrapper__left__nums" id="vm_platform_num"></span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_PLATFORM_VM_CLOUD_PLATFORM'] ?></span>
                </div>
                <div class="box-wrapper__right vm-virtual-svg"></div>
            </div>
            <div class="report-detail-boxes__item box-wrapper vm-backup-times-box display-none">
                <div class="box-wrapper__left">
                    <span class="box-wrapper__left__nums" id="vm_backup_times_num"></span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_VM_REPORT_BACKUP_COUNT'] ?></span>
                </div>
                <div class="box-wrapper__right backup-times-svg"></div>
            </div>
            <div class="report-detail-boxes__item box-wrapper vm-backup-data-box display-none">
                <div class="box-wrapper__left">
                    <span class="nums-wrapper">
                        <span class="box-wrapper__left__nums" id="vm_backup_data_num"></span>
                        <span class="box-wrapper__left__unit" id="vm_backup_unit"></span>
                    </span>
                    <span class="box-wrapper__left__des"><?php echo $LANG['UI_COPY_SOURCE_BACKUP_DATA'] ?></span>
                </div>
                <div class="box-wrapper__right backup-data-svg"></div>
            </div>
        </div>

        <div class="report-detail-header echart-header display-none">
            <span class="decoration me-8"></span>
            <span class="report-detail-header__text"><?php echo $LANG['UI_REPORT_RENCENTLY_OPERATE'] ?></span>
        </div>
        <div class="charts-wrapper echart-wrapper display-none">
            <div class="charts-wrapper__toolbar">
                <div></div>
                <select class="charts-wrapper__toolbar__select form-control" id="task_running_time_select">
                    <option value="1"><?php echo $LANG['UI_NEARLY_A_DAY'] ?></option>
                    <option value="2"><?php echo $LANG['UI_NEARLY_THREE_DAY'] ?></option>
                    <option value="3"><?php echo $LANG['UI_NEARLY_A_WEEK'] ?></option>
                    <option selected value="4"><?php echo $LANG['UI_NEARLY_A_MONTH'] ?></option>
                </select>
            </div>
            <div class="tab-content">
                <div class="tab-content__echart" id="public_cloud_task_running_chart"></div>
                <div class="tab-content__nodata display-none">
                    <div class="tab-content__nodata__img">
                        <img src="./img/platform/report/nodata.svg" alt="">
                    </div>
                    <div class="tab-content__nodata__info"><?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?></div>
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
                                    placeholder="<?php echo $LANG['UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_SEARCH_TIPS'] ?>">
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
                            <div id="public_report_filter_wrapper" class="position-relative"></div>
                        </div>
                    </div>
                </div>
                <div class="vin_toolbar mb-0">
                    <div class="vin_btnToolbar"></div>
                </div>
            </div>
            <div class="data-detail-wrapper__content">
                <table id="public_cloud_report_table"></table>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/reports/publicDetail.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/echarts/V5.4.3/echarts.min.js"></script>
<script type="text/javascript" src="./scripts/components/filter/js/filter.js"></script>
<!-- END PAGE LEVEL PLUGINS -->