/*
 * @note: 用于插件化生成数据库实时时间点范围流线图
 * @author: chenyunfeng@vinchin.com
 * @Description: 数据库实时备份时间断插件
 * @Date: 2024-10-23 11:13:48
 * @LastEditTime: 2024-12-04 15:31:13
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */
(function($) {
    $.fn.myTimeChart = function(options) {
        const defaults = {};
        const settings = {...defaults, ...options};
        const dom = $(this);
        // 可选地时间范围
        const TIME_RANGE = {
            1: LANG.UI_BACKUP_DATA_TIMEPOINT_RANGE_LAST_TEN_MINUTE, // 最近十分钟
            2: LANG.UI_BACKUP_DATA_TIMEPOINT_RANGE_LAST_ONE_HOUR, // 最近一小时
            3: LANG.UI_BACKUP_DATA_TIMEPOINT_RANGE_LAST_ONE_DAY, // 最近一天
            4: LANG.UI_BACKUP_DATA_TIMEPOINT_RANGE_LAST_ONE_WEEK, // 最近一周
        }
        // 生成dbcdp图表的标题
        const getPanelHeader = function() {
            let html = `<div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#timePointRegion" aria-expanded="true">
                                    <i class="viconfont vicon-shijian2 font-green-seagreen"></i>
                                    <span class="font-green-seagreen">${LANG.UI_BACKUP_DATA_TIMEPOINT_RANGE}</span>
                                    <span class="time-range-des"></span>
                                </a>
                            </h4>
                        </div>`;
            return html;
        }
        // 获取时间点范围的可选值
        const getOptions = function (){
            let html = '';
            for (const key of Object.keys(TIME_RANGE)) {
                html += `<option value="${key}">${TIME_RANGE[key]}</option>`;
            }
            return html;
        }
        // 生成图标的展开内容
        const getPanelBody = function() {
            let html = `<div id="timePointRegion" class="panel-collapse collapse in">
                            <div class="panel-body">
                                <div class="tabbable-custom">
                                    <ul class="nav nav-tabs">
                                        <li class="timepointLi active">
                                            <a href="#anyPoint" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                <i class="viconfont vicon-renyishijiandian"></i>${LANG.UI_VOL_CDP_RECOVER_ANY_POINT_TIME}</a>
                                        </li>
                                        <li class="eventLi">
                                            <a href="#eventInfo" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                <i class="viconfont vicon-shijian1"></i>${LANG.UI_VOL_CDP_RECOVER_TIME_POINT}</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="anyPoint">
                                            <div class="form-group">
                                                <label class="control-label">${LANG.UI_VM_MACHINE_TIMERANGE}</label>
                                                <div class="time-range-select-wrapper">
                                                    <select class="form-control select2me input-sm time-range-select">
                                                        ${getOptions()}
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="flow_chart-wrapper min-height360">
                                                <div id="flow_chart-content"></div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="eventInfo"><table id="transactionInfoTable" class="min-height360"></table></div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
            return html;
        };
        // 初始化页面显示
        const initDom = function() {
            let html = `<div class="accordion">
                            <div class="panel panel-default strategy-panel">
                            ${getPanelHeader()}
                            ${getPanelBody()}
                            </div>
                        </div>`;
            dom.html(html);
            dom.find('.popovers').popover();
        }
        // 更新时间点范围图示
        const updateChart = function() {
        }
        return this.each(function () {
            const $this = $(this);
            const privateMethod = {
                updateChart: updateChart
            }
            // 将获取过滤参数暴露给外部使用
            $this.data('myTimeChart', privateMethod);
            // 初始化页面
            initDom();
        });
    };
})(jQuery);
