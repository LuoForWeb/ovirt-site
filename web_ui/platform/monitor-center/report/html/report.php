<!-- BEGIN PLUGIN STYLES -->
<link href="./plugins/table/css/table.css" rel="stylesheet" type="text/css" />
<link href="./plugins/filter/css/filter.css" rel="stylesheet" type="text/css" />
<link href="./plugins/radio-button-group/css/radio-button-group.css" rel="stylesheet" type="text/css" />
<link href="./plugins/inline-label-select/css/inline-label-select.css" rel="stylesheet" type="text/css" />
<link href="./plugins/cascader-checkbox-group/css/cascader-checkbox-group.css" rel="stylesheet" type="text/css" />
<link href="./plugins/search-input/css/search-input.css" rel="stylesheet" type="text/css" />
<link href="./platform/monitor-center/report/css/report.css" rel="stylesheet" type="text/css" />
<!-- END PLUGIN STYLES -->

<div class="vinchin-wrapper">
    <div class="vinchin-wrapper__header">
        <i class="viconfont vicon-moban me-8"></i>
        <span class="vinchin-wrapper__header__text">报表模板</span>
    </div>
    <div class="vinchin-wrapper__content d-flex p-0">
        <div class="tree-wrapper">
            <div class="tree-wrapper__toolbar">
                <button class="btn btn-primary new-report-btn me-8" type="button" id="open_report_drawer_btn">
                    <i class="viconfont vicon-biaogetianjia me-4"></i>
                    新建
                </button>
                <div id="report_search_container"></div>
            </div>
            <div class="tree-wrapper__tree hover-scroll-y">
                <div id="report_tree"></div>
            </div>
        </div>

        <div class="table-wrapper">
            <div class="table-toolbar-wrapper">
                <!-- BEGIN LEFT TOOLBAR -->
                <div class="table-toolbar-wrapper__left">
                    <button class="btn btn-icon-secondary-primary me-10" disabled id="delete_custom_report_btn">
                        <i class="viconfont vicon-a-Deleteshanchu2"></i>
                    </button>
                    <div id="report_table_filter_wrapper" class="position-relative"></div>
                </div>
                <!-- END LEFT TOOLBAR -->

                <!-- BEGIN RIGHT TOOLBAR -->
                <div class="table-toolbar-wrapper__right report-right-toolbar-wrapper">
                    <div class="toolbar-buttons-wrapper report-toolbar-buttons"></div>
                </div>
                <!-- END RIGHT TOOLBAR -->
            </div>

            <div class="table-content-wrapper report-table-content-wrapper">
                <table id="report_table"></table>
            </div>
        </div>
    </div>
</div>

<!-- BEGIN REPORT OFFCANVAS -->
<div class="offcanvas offcanvas-end offcanvas-end-xl report-offcanvas" tabindex="-1" id="report_offcanvas" aria-labelledby="offcanvasExampleLabel" data-bs-backdrop="static">
    <div class="offcanvas-header">
        <div class="offcanvas-title">
            <span class="offcanvas-title__text" id="report_offcanvas_title"></span>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="report_form" class="form system-notice-form needs-validation" novalidate>
            <!-- BEGIN TEMPLATE TYPE -->
            <div class="form-group">
                <div class="form-group__label col-md-2">
                    模板类型
                </div>
                <div class="form-group__content col-md-10">
                    <select class="select2" id="template_type_select">
                        <option selected value="1">备份资源</option>
                        <option value="2">生产资源</option>
                        <option value="3">数据保护</option>
                        <option value="4">任务</option>
                        <option value="5">用户</option>
                    </select>
                </div>
            </div>
            <!-- END TEMPLATE TYPE -->
            <!-- BEGIN SUB TEMPLATE TYPE -->
            <div class="form-group backup-resource-form-group">
                <div class="form-group__label col-md-2">
                    资源类型
                </div>
                <div class="form-group__content col-md-10">
                    <div id="resource_type_radio_group"></div>
                </div>
            </div>
            <!-- END SUB TEMPLATE TYPE -->
            <!-- BEGIN REPORT NAME -->
            <div class="form-group report-name-form-group is-required display-none">
                <div class="form-group__label col-md-2">
                    报表名称
                </div>
                <div class="form-group__content col-md-10">
                    <input type="text" id="report_name" class="form-control" data-v-message="报表名称不能为空" required placeholder="请输入报表名称" />
                </div>
            </div>
            <!-- END REPORT NAME -->

            <!-- BEGIN RESOURCE LIST -->
            <div class="form-group resource-list-form-group align-items-baseline is-required display-none">
                <div class="form-group__label col-md-2">
                    <span id="resource_list_form_group_label"></span>
                </div>
                <div class="form-group__content col-md-10">
                    <div class="accordion resource-list-form-group-accordion" id="resource_list_form_group_accordion">
                        <div class="accordion-header collapsed" data-bs-toggle="collapse" data-bs-target="#resource_list_form_group_accordion_body" aria-expanded="false">
                            <div class="accordion-button" id="resource_list_form_group_accordion_btn"></div>    
                        </div>
                        <div id="resource_list_form_group_accordion_body" class="collapse" data-bs-parent="#resource_list_form_group_accordion">
                            <div class="accordion-panel">
                                <div class="table-toolbar-wrapper">
                                    <div class="table-toolbar-wrapper__left"></div>
                                    <div class="table-toolbar-wrapper__right">
                                        <div class="table-toolbar-wrapper__right resource-list-right-toolbar-wrapper">
                                            <div class="toolbar-buttons-wrapper resource-list-toolbar-buttons"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-content-wrapper resource-list-content-wrapper">
                                    <table id="resource_list_table"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="custom-validate-tip validate-reousrce-list-tip display-none">至少选择一项</div>
                </div>
            </div>
            <!-- END RESOURCE LIST -->

            <!-- BEGIN OVERVIEW SWITCH-->
            <div class="form-group overview-form-group mb-40 display-none">
                <div class="form-group__label col-md-2">
                    数据概览
                </div>
                <div class="form-group__content col-md-10">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="overview_switch">
                    </div>
                    <div class="tips-info overview-tips-info"></div>
                </div>
            </div>
            <!-- END OVERVIEW SWITCH -->

            <!-- BEGIN NODE SELECT2 -->
            <div class="form-group node-form-group is-required display-none">
                <div class="form-group__label col-md-2">
                    节点
                </div>
                <div class="form-group__content col-md-10">
                    <select id="node_data_select2" class="form-control select2" multiple></select>
                </div>
            </div>
            <!-- END NODE SELECT2 -->

            <!-- BEGIN TENDENCY SWITCH -->
            <div class="form-group tendency-form-group display-none">
                <div class="form-group__label col-md-2">
                    <span id="tendency_label">使用情况</span>
                </div>
                <div class="form-group__content col-md-10">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="tendency_switch" data-bs-toggle="collapse" data-bs-target="#tendency_wrap" aria-expanded="false">
                    </div>
                </div>
            </div>
            <!-- END TENDENCY SWITCH -->

            <div id="tendency_wrap" class="collapse tendency-wrap">
                <!-- BEGIN TIME RANGE SELECT -->
                <div class="form-group time-range-select-form-group">
                    <div class="form-group__label col-md-2"></div>
                    <div class="form-group__content col-md-10">
                        <div id="time_range_inline_select"></div>
                    </div>
                </div>
                <!-- END TIME RANGE SELECT -->
                <!-- BEGIN TIME RANGE PICKER -->
                <div class="form-group time-range-picker-form-group display-none">
                    <div class="form-group__label col-md-2"></div>
                    <div class="form-group__content col-md-4" id="form_group_timerange_picker">
                        <input class="form-control form-daterangepicker" name="customizedTimeRange" placeholder="选择一个日期范围" id="time_range_picker" readonly="">
                        <i class="viconfont vicon-rili2 input-i"></i>
                        <div class="custom-validate-tip time-range-tip display-none">请选择自定义日期范围</div>
                    </div>
                </div>
                <!-- END TIME RANGE PICKER -->
                <!-- BEGIN MODULES CHECKBOX GROUP -->
                <div class="form-group modules-checkbox-group">
                    <div class="form-group__label col-md-2"></div>
                    <div class="form-group__content col-md-10" id="module_checkbox_groups_wrap"></div>
                </div>
                <!-- END MODULES CHECKBOX GROUP -->
            </div>

            <!-- BEGIN INTELIGENTIZE FORECAST -->
            <div class="form-group inteligentize-forecast-form-group display-none">
                <div class="form-group__label col-md-2">
                    使用天数预测
                </div>
                <div class="form-group__content col-md-10">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="availability_forecast_switch">
                    </div>
                </div>
            </div>
            <!-- END INTELIGENTIZE FORECAST -->

            <!-- BEGIN CUSTOMIZED DATA -->
            <div class="form-group customized-data-form-group is-required display-none">
                <div class="form-group__label col-md-2">
                    定制数据
                </div>
                <div class="form-group__content col-md-10">
                    <select id="customized_data_select2" class="form-control select2" multiple data-allow-clear="true" data-placeholder="请至少选择五项" data-v-min-length="5" data-v-message="至少选择五项"></select>
                </div>
            </div>
            <!-- END CUSTOMIZED DATA -->

            <!-- BEGIN SAVE PATH -->
            <div class="form-group report-path-form-group align-items-start is-required display-none">
                <div class="form-group__label col-md-2">
                    保存路径
                </div>
                <div class="form-group__content col-md-10">
                    <div class="report-path-tree" id="report_path_tree"></div>
                    <div class="custom-validate-tip validate-customized-path-tip display-none">请选择保存路径</div>
                </div>
            </div>
            <!-- END SAVE PATH -->

            <!-- BEGIN DESCRIPTION -->
            <div class="form-group report-description-form-group align-items-baseline display-none">
                <div class="form-group__label col-md-2">
                    描述
                </div>
                <div class="form-group__content col-md-10">
                    <textarea type="textarea" class="form-control form-control-textarea" id="description" name="description" rows="5"></textarea>
                </div>
            </div>
            <!-- END DESCRIPTION -->

        </form>
    </div>
    <div class="offcanvas-footer flex-items-center justify-content-end">
        <div class="btn-group">
            <button class="btn btn-lg btn-outline-primary me-16" data-bs-dismiss="offcanvas" aria-label="Close">取消</button>
            <button class="btn btn-lg btn-primary" id="report_form_submit">确认</button>
        </div>
    </div>
</div>
<!-- END REPORT OFFCANVAS -->

<!-- BEGIN PLUGIN SCRIPTS -->
<script src="/assets/global/plugins/jstree/jstree.js" type="text/javascript"></script>
<script src="/assets/global/plugins/echarts/echarts.common.min.js" type="text/javascript"></script>
<script src="./plugins/filter/js/filter.js" type="text/javascript"></script>
<script src="./plugins/table/js/table.js" type="text/javascript"></script>
<script src="./plugins/radio-button-group/js/radio-button-group.js"></script>
<script src="./plugins/inline-label-select/js/inline-label-select.js" type="text/javascript"></script>
<script src="/plugins/cascader-checkbox-group/js/cascader-checkbox-group.js" type="text/javascript"></script>
<script src="/plugins/search-input/js/search-input.js" type="text/javascript"></script>
<script src="./platform/monitor-center/report/js/report.js" type="text/javascript"></script>
<!-- END PLUGIN SCRIPTS -->
