<!-- BEGIN PLUGIN STYLES -->
<link href="./plugins/filter/css/filter.css" rel="stylesheet" type="text/css" />
<link href="./plugins/radio-group/css/radio-group.css" rel="stylesheet" type="text/css" />
<link href="./plugins/table/css/table.css" rel="stylesheet" type="text/css" />
<link href="./platform/monitor-center/report/css/report.css" rel="stylesheet" type="text/css" />
<!-- END PLUGIN STYLES -->

<div class="report-detail">
    <div class="report-detail__header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a class="ajaxify" name="storage_report" route-id="parent_monitor" href="./platform/monitor-center/report/html/report.php">报表模板</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">节点报表</li>
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
                <span id="overview_title" class="title"></span>
                <span id="overview_description" class="description"></span>
            </div>
            <div class="overview-wrapper display-none">
                <div class="header-level2">
                    <span class="title">数据概览</span>
                </div>
                <div class="storage-overview-group">
                    <div class="storage-overview-group__item">
                        <span class="name">节点总数</span>
                        <span class="value" id="overview_total_node"></span>
                    </div>
                    <div class="storage-overview-group__item">
                        <span class="name">在线节点</span>
                        <span class="value" id="overview_online_node"></span>
                    </div>
                    <div class="storage-overview-group__item">
                        <span class="name">离线节点</span>
                        <span class="value" id="overview_offline_node"></span>
                    </div>
                    <div class="storage-overview-group__item">
                        <span class="name">异常节点</span>
                        <span class="value" id="overview_abnormal_node"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card tendency-card">
            <div class="header-level2">
                <span class="title">负载情况</span>

                <div class="flex-items-center">
                    <div class="me-10" id="load_time_radio_group"></div>
                    <div class="time-range-wrap">
                        <select class="form-control select2" id="time_range_type">
                            <option value="1">近一天</option>
                            <option value="2">近三天</option>
                            <option value="3">近一周</option>
                            <option value="4" selected>近一月</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="tendency-card__echarts">
                <div class="detail-card__chart" id="usage_tendency_echart"></div>
            </div>
        </div>

        <div class="detail-card table-card display-none">
            <div class="header-level2">
                <span class="title">数据明细</span>
            </div>
            <div class="table-container">
                <div class="table-toolbar-wrapper">
                    <!-- BEGIN LEFT TOOLBAR -->
                    <div class="table-toolbar-wrapper__left">
                        <div id="node_report_table_filter_wrapper" class="position-relative"></div>
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
<script src="./plugins/filter/js/filter.js" type="text/javascript"></script>
<script src="./plugins/table/js/table.js" type="text/javascript"></script>
<script src="./plugins/radio-group/js/radio-group.js" type="text/javascript"></script>
<script src="./platform/monitor-center/report/js/node-report-detail.js" type="text/javascript"></script>
<!-- END PLUGIN SCRIPTS -->