<?php
include_once '../../tpl/permission.php';
$userAllPermission = $_SESSION['permission'];
?>
<link href="./css/themeSkin/standardSkin.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css" />
<div class="datacenter-content">
    <div class="row first-level">
        <div class="col-md-9 pe-20" id="overview-card">
            <div class="card">
                <div class="title">
                    <?php echo $LANG['UI_REPORT_OVERVIEW'] ?>
                </div>
                <div class="overview-content">
                    <!-- 累计保护数据 -->
                    <div class="overview-item total-data">
                        <div class="item-pic total-data-pic">
                        </div>
                        <div class="item-text">
                            <div class="item-text-title">
                                <?php echo $LANG['UI_DATACENTER_TOTAL_DATA_SIZE'] ?>
                            </div>
                            <div class="mt-8">
                                <span class="item-text-num"></span>
                                <span class="unit"></span>
                            </div>
                        </div>
                    </div>
                    <!-- 当前任务 -->
                    <div class="overview-item current-task">
                        <div class="item-pic current-task-pic">
                        </div>
                        <div class="item-text">
                            <div class="item-text-title">
                                <?php echo $LANG['UI_JOB_CURRENT'] ?>
                            </div>
                            <div class="mt-8">
                                <span class="item-text-num"></span>

                            </div>
                        </div>
                    </div>
                    <!-- 历史任务 -->
                    <div class="overview-item history-task">
                        <div class="item-pic history-task-pic">
                        </div>
                        <div class="item-text">
                            <div class="item-text-title">
                                <?php echo $LANG['UI_JOB_HISTORY'] ?>
                            </div>
                            <div class="mt-8">
                                <span class="item-text-num"></span>

                            </div>
                        </div>
                    </div>
                    <!-- 剩余存储容量 -->
                    <div class="overview-item remain-storage">
                        <div class="item-pic remain-storage-pic">
                        </div>
                        <div class="item-text">
                            <div class="item-text-title">
                                <?php echo $LANG['UI_HOMEPAGE_STANDARD_REMAIN_STORAGE'] ?>
                            </div>
                            <div class="mt-8">
                                <span class="item-text-num"></span>
                                <span class="unit"></span>
                            </div>
                        </div>
                    </div>
                    <!-- 存储使用率 -->
                    <div class="overview-item storage-percent">
                        <div class="item-pic storage-percent-pic">
                        </div>
                        <div class="item-text">
                            <div class="item-text-title">
                                <?php echo $LANG['UI_HOMEPAGE_STANDARD_STOAGE_USED_RATE'] ?>
                            </div>
                            <div class="mt-8">
                                <span class="item-text-num"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-3 pl0" id="op-log-card">
            <!-- 操作日志 -->
            <div class="card">
                <div class="title">
                    <?php echo $LANG['UI_VM_MACHINE_LOG'] ?>
                    <div class="title-href">
                        <?php echo $LANG['UI_VM_MACHINE_MORE'] ?>
                    </div>
                </div>
                <div class="log-content">
                    <div class="log-box">
                        <div class="log-title">
                            <?php echo $LANG['UI_HOMEPAGE_TASK_LOG'] ?>
                        </div>
                        <div class="d-flex mt-6 justify-content-space-between">
                            <div class="log-num" id="task-log-num">
                            </div>
                        </div>
                        <div class="log-pic task-log-pic">
                        </div>
                    </div>
                    <div class="log-box">
                        <div class="log-title">
                            <?php echo $LANG['UI_HOMEPAGE_SYSTEM_LOG'] ?>
                        </div>
                        <div class="d-flex mt-6 justify-content-space-between">
                            <div class="log-num" id="system-log-num">
                            </div>
                        </div>
                        <div class="log-pic system-log-pic">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row second-level">
        <!-- 数据保护 -->
        <div class="col-md-9 pe-20" id="data-protect-card">
            <div class="card">
                <div class="title">
                    <?php echo $LANG['UI_HOMEPAGE_DATA_PROTECT'] ?>
                    <ul class="nav nav-tabs" role="tablist">
                        <?php
                        if (in_array("backup", $userAllPermission)) {
                            echo '<li class="backup" style="display:none" role="presentation"><a href="#backup_pane" aria-controls="backup" role="tab" data-toggle="tab" id="backup_tab"> ' . $LANG['UI_PLATFORM_DATA_BACKUP'] . '</a></li>';
                        }
                        ?>
                        <?php
                        if (in_array("vol_cdp_protect", $userAllPermission)) {
                            echo '<li class="vol_cdp_protect" style="display:none" role="presentation"><a href="#vol_cdp_protect_pane" aria-controls="vol_cdp_protect" role="tab" data-toggle="tab" id="vol_cdp_protect_tab">' . $LANG['UI_PLATFORM_CDP_PROTECT'] . '</a></li>';
                        }
                        ?>
                        <?php
                        if (in_array("copy", $userAllPermission)) {
                            echo '<li class="copy" style="display:none" role="presentation"><a href="#copy_pane" aria-controls="copy" role="tab" data-toggle="tab" id="copy_tab"> ' . $LANG['UI_MICROSOFT365_COPY'] . ' </a></li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="tab-content">
                    <div class="tab-pane" id="backup_pane">
                        <!-- 里面需要用swiper -->
                        <div class="container">
                            <div class="swiper mySwiper">
                                <div class="swiper-wrapper">
                                </div>
                            </div>
                            <!-- 自定义导航按钮 -->
                            <div class="custom-navigation">
                                <div class="custom-button custom-button-prev">
                                    <img />

                                </div>
                                <div class="custom-button custom-button-next">
                                    <img />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="vol_cdp_protect_pane">
                        <!-- 里面需要用swiper -->
                        <div class="container">
                            <div class="swiper mySwiper">
                                <div class="swiper-wrapper">
                                </div>
                            </div>
                            <!-- 自定义导航按钮 -->
                            <div class="custom-navigation">
                                <div class="custom-button custom-button-prev">
                                    <img />

                                </div>
                                <div class="custom-button custom-button-next">
                                    <img />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="copy_pane">
                        <!-- 里面需要用swiper -->
                        <div class="container">
                            <div class="swiper mySwiper">
                                <div class="swiper-wrapper">
                                </div>
                            </div>
                            <!-- 自定义导航按钮 -->
                            <div class="custom-navigation">
                                <div class="custom-button custom-button-prev">
                                    <img />

                                </div>
                                <div class="custom-button custom-button-next">
                                    <img />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 pl0" id="alarm-card">
            <div class="card alarm-div">
                <div class="title">
                    <?php echo $LANG['UI_HOMEPAGE_ALARM_STATISTICS'] ?>
                </div>
                <div class="alarm-content" id="task-alarm-box">
                    <div>
                        <div class="log-title">
                            <?php echo $LANG['UI_PLATFORM_THIRD_TASK_ALARM'] ?>
                        </div>
                        <div class="alarm-total-num" id="task-alarm-total-num"></div>
                    </div>
                    <div class="alarm-detail">
                        <div>
                            <?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?><span class="alarm-detail-num warn-des" id="task-alarm-warn-num"></span>
                        </div>
                        <div class="splitline">
                        </div>
                        <div>
                            <?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?><span class="alarm-detail-num error-des" id="task-alarm-error-num"></span>
                        </div>
                        <div class="alarm-href">
                            <i class="viconfont vicon-gengduo">
                            </i>
                        </div>
                    </div>
                </div>
                <div class="alarm-content" id="system-alarm-box">
                    <div>
                        <div class="log-title">
                            <?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?>
                        </div>
                        <div class="alarm-total-num" id="system-alarm-total-num"></div>
                    </div>
                    <div class="alarm-detail">
                        <div>
                            <?php echo $LANG['WEB_PLATFORM_DES_WARNING'] ?><span class="alarm-detail-num warn-des" id="system-alarm-warn-num"></span>
                        </div>
                        <div class="splitline">
                        </div>
                        <div>
                            <?php echo $LANG['WEB_PLATFORM_DES_ERROR'] ?><span class="alarm-detail-num error-des" id="system-alarm-error-num"></span>
                        </div>
                        <div class="alarm-href">
                            <i class="viconfont vicon-gengduo">
                            </i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row third-level">
        <div class="col-md-9">
            <div class="row">
                <!-- 数据保护趋势 -->
                <div class="col-md-6" id="data-protect-trend-card">
                    <div class="card ">
                        <div class="title">
                            <?php echo $LANG['UI_HOMEPAGE_STANDARD_PROTECTED_DATA_TREND'] ?>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="data_recent_week active" role="presentation"><a href="#data_recent_week_pane" aria-controls="data_recent_week" role="tab" data-toggle="tab" id="data_recent_week"><?php echo $LANG['UI_NEARLY_A_WEEK'] ?></a></li>
                                <li class="data_recent_month" role="presentation"><a href="#data_recent_month_pane" aria-controls="data_recent_month" role="tab" data-toggle="tab" id="data_recent_month"><?php echo $LANG['UI_NEARLY_A_MONTH'] ?></a></li>
                                <li class="data_recent_year" role="presentation"><a href="#data_recent_year_pane" aria-controls="data_recent_year" role="tab" data-toggle="tab" id="data_recent_year"> <?php echo $LANG['UI_HOMEPAGE_STANDARD_RECENT_YEAR'] ?></a></li>
                            </ul>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane active" id="data_recent_week_pane">
                                <div id="data_week_line" class="data_week_line display-none"></div>
                                <div class="nodata-box week_line_nodata display-none">
                                    <div class="nodata-box-content">
                                        <img src="/img/themeSkin/standardSkin/no-data.svg" alt="">
                                        <div class="nodata-text">
                                            <?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="tab-pane" id="data_recent_month_pane">
                                <div id="data_month_line" class="data_month_line display-none">
                                </div>
                                <div class="nodata-box month_line_nodata display-none">
                                    <div class="nodata-box-content">
                                        <img src="/img/themeSkin/standardSkin/no-data.svg" alt="">
                                        <div class="nodata-text">
                                            <?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="data_recent_year_pane">
                                <div id="data_year_line" class="data_year_line display-none">
                                </div>
                                <div class="nodata-box year_line_nodata display-none">
                                    <div class="nodata-box-content">
                                        <img src="/img/themeSkin/standardSkin/no-data.svg" alt="">
                                        <div class="nodata-text">
                                            <?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 存储使用趋势 -->
                <div class="col-md-6" id="storage-trend-card">
                    <div class="card">
                        <div class="title">
                            <?php echo $LANG['UI_HOMEPAGE_STANDARD_STORAGE_USED_TREND'] ?>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="data_recent_week active" role="presentation"><a href="#storage_recent_week_pane" aria-controls="storage_recent_week" role="tab" data-toggle="tab" id="storage_recent_week"> <?php echo $LANG['UI_NEARLY_A_WEEK'] ?></a></li>
                                <li class="data_recent_month" role="presentation"><a href="#storage_recent_month_pane" aria-controls="storage_recent_month" role="tab" data-toggle="tab" id="storage_recent_month"> <?php echo $LANG['UI_NEARLY_A_MONTH'] ?></a></li>
                                <li class="data_recent_year" role="presentation"><a href="#storage_recent_year_pane" aria-controls="storage_recent_year" role="tab" data-toggle="tab" id="storage_recent_year"> <?php echo $LANG['UI_HOMEPAGE_STANDARD_RECENT_YEAR'] ?></a></li>
                            </ul>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane active" id="storage_recent_week_pane">
                                <div id="storage_week_bar" class="storage_week_bar display-none">
                                </div>
                                <div class="nodata-box week_bar_nodata display-none">
                                    <div class="nodata-box-content">
                                        <img src="/img/themeSkin/standardSkin/no-data.svg" alt="">
                                        <div class="nodata-text">
                                            <?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="storage_recent_month_pane">
                                <div id="storage_month_bar" class="storage_month_bar display-none">
                                </div>
                                <div class="nodata-box month_bar_nodata display-none">
                                    <div class="nodata-box-content">
                                        <img src="/img/themeSkin/standardSkin/no-data.svg" alt="">
                                        <div class="nodata-text">
                                            <?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="storage_recent_year_pane">
                                <div id="storage_year_bar" class="storage_year_bar display-none">
                                </div>
                                <div class="nodata-box year_bar_nodata display-none">
                                    <div class="nodata-box-content">
                                        <img src="/img/themeSkin/standardSkin/no-data.svg" alt="">
                                        <div class="nodata-text">
                                            <?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 告警详情 -->
        <div class="col-md-3 pl0" id="alarm-detail-card">
            <div class="card">
                <div class="title">
                    <?php echo $LANG['UI_ALARM_DETAILS'] ?>
                    <div class="title-href">
                        <?php echo $LANG['UI_VM_MACHINE_MORE'] ?>
                    </div>
                </div>
                <div class="alarm-box display-none">
                </div>
                <div class="nodata-box display-none">
                    <div class="nodata-box-content">
                        <img src="/img/themeSkin/standardSkin/no-data.svg" alt="">
                        <div class="nodata-text">
                            <?php echo $LANG['WEB_PLATFORM_DC_NO_DATA'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 受保护数据的tooltip -->
    <div class="protect-tooltip-content display-none">
        <div class="protect-tooltip-box">
            <div class="top-title-time">

            </div>
            <div class="top-total-info">
                <div>
                    <div class="circle">
                    </div>
                    <span><?php echo $LANG['UI_HOMEPAGE_PROTECTED_DATA'] ?></span>
                </div>
                <span class="total-des"></span>
            </div>
            <div class="modules-container">
                <div class="module-item timemodule double-column">
                    <div class="module-title">
                        <div class="split-line"></div>
                        <?php echo $LANG['UI_PLATFORM_DATA_BACKUP'] ?>
                    </div>
                    <div class="module-content">

                    </div>
                </div>
                <div class="bottom-box">
                    <div class="module-item copymodule">
                        <div class="module-title">
                            <div class="split-line"></div>
                            <?php echo $LANG['UI_MICROSOFT365_COPY'] ?>
                        </div>
                        <div class="module-content">

                        </div>
                    </div>
                    <div class="module-item cdpmodule">
                        <div class="module-title">
                            <div class="split-line"></div>
                            <?php echo $LANG['UI_PLATFORM_CDP_PROTECT'] ?>
                        </div>
                        <div class="module-content">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script src="./assets/global/plugins/echarts/V5.4.3/echarts.min.js"></script>
<script type="text/javascript" src="./scripts/platform/databackup_center_standard.js"></script>
<!-- END PAGE LEVEL PLUGINS -->