<!-- BEGIN PLUGIN STYLES -->
<link href="./platform/monitor-center/report/css/report.css" rel="stylesheet" type="text/css" />
<link href="./plugins/table/css/table.css" rel="stylesheet" type="text/css" />
<!-- END PLUGIN STYLES -->

<div class="report-detail">
    <div class="report-detail__header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a class="ajaxify" name="storage_report" route-id="parent_monitor" href="./platform/monitor-center/report/html/report.php">报表模板</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">磁带报表</li>
            </ol>
        </nav>
        <button type="button" class="btn btn-primary btn-md">
            <i class="viconfont vicon-a-Share-threefenxiang3 me-2"></i>
            导出
        </button>
    </div>

    <div class="report-detail__body hover-scroll-y">
        <div class="detail-card overview-card">
            <div class="header-level1">
                <span id="overview_title" class="title">磁带报表</span>
                <span id="overview_description" class="description">磁带报表描述</span>
            </div>
            <div class="overview-wrapper">
                <div class="header-level2">
                    <span class="title">数据概览</span>
                </div>
                <div class="tape-overview">
                    <div class="tape-overview-cards">
                        <div class="tape-overview-cards__item">
                            <span class="name">磁带库总数</span>
                            <span class="value" id="overview_total_tape_library"></span>
                        </div>
                        <div class="tape-overview-cards__item">
                            <span class="name">已使用磁带</span>
                            <span class="value" id="overview_used_tape"></span>
                        </div>
                        <div class="tape-overview-cards__item">
                            <span class="name">在线磁带</span>
                            <span class="value" id="overview_online_tape"></span>
                        </div>
                        <div class="tape-overview-cards__item">
                            <span class="name">离线磁带</span>
                            <span class="value" id="overview_offline_tape"></span>
                        </div>
                        <div class="tape-overview-cards__item">
                            <span class="name">驱动器总数</span>
                            <span class="value" id="overview_total_drive"></span>
                        </div>
                        <div class="tape-overview-cards__item">
                            <span class="name">装载率</span>
                            <span class="value">
                                <span id="overview_tape_usage"></span>
                                <span class="unit" id="overview_tape_usage_unit"></span>
                            </span>
                        </div>
                    </div>
                    <div class="overview-echarts-container tape-ecahrts-container">
                        <div class="storage-echarts">
                            <div class="storage-echarts__chart" id="tape_capacity_echart"></div>
                            <div class="storage-echarts__illustration">
                                <div class="sumarry-wrap">
                                    <span class="name">磁带总容量</span>
                                    <span class="value">
                                        <span id="overview_total_capacity"></span>
                                        <span class="unit" id="overview_total_capacity_unit"></span>
                                    </span>
                                </div>
                                <div class="particulars-wrap">
                                    <div class="particulars-wrap__item">
                                        <span class="name">
                                            <span class="square square-primary me-8"></span>
                                            已用容量
                                        </span>
                                        <span class="value">
                                            <span id="overview_used_capacity"></span>
                                            <span class="unit" id="overview_used_capacity_unit"></span>
                                        </span>
                                    </div>
                                    <div class="particulars-wrap__item">
                                        <span class="name">
                                            <span class="square square-secondary me-8"></span>
                                            剩余容量
                                        </span>
                                        <span class="value">
                                            <span id="overview_free_capacity"></span>
                                            <span class="unit" id="overview_free_capacity_unit"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card tendency-card">
            <div class="header-level2">
                <span class="title">使用情况</span>

                <div class="time-range-wrap">
                    <select class="form-control select2" id="time_range_type">
                        <option value="1">近一天</option>
                        <option value="2">近三天</option>
                        <option value="3">近一周</option>
                        <option value="4" selected>近一月</option>
                    </select>
                </div>
            </div>
            <div class="tendency-card__echarts">
                <div class="detail-card__chart" id="usage_tendency_echart"></div>
            </div>
        </div>

        <div class="detail-card table-card">
            <div class="header-level2">
                <span class="title">数据明细</span>
            </div>
            <div class="table-container">
                <div class="table-toolbar-wrapper">
                    <!-- BEGIN LEFT TOOLBAR -->
                    <div class="table-toolbar-wrapper__left">
                        <div id="tape_report_table_filter_wrapper" class="position-relative me-10"></div>
                    </div>
                    <!-- END LEFT TOOLBAR -->

                    <!-- BEGIN RIGHT TOOLBAR -->
                    <div class="table-toolbar-wrapper__right report-detail-right-toolbar-wrapper">
                        <div class="toolbar-buttons-wrapper report-detail-toolbar-buttons"></div>
                    </div>
                    <!-- END RIGHT TOOLBAR -->
                </div>
                <div class="table-content-wrapper report-detail-table-content-wrapper">
                    <table id="report_detail_table"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BEGIN PLUGIN SCRIPTS -->
<script src="./plugins/table/js/table.js" type="text/javascript"></script>
<script src="./platform/monitor-center/report/js/tape-report-detail.js" type="text/javascript"></script>
<!-- END PLUGIN SCRIPTS -->