/*
 * @Author: ChengJiaFu
 * @Date: 2026-02-09 09:52:44
 * @Description: 磁带报表详情LOGIC
 * @version: 1.0
 */
var TapeReportDetail = function () {
    let REPORT_TEMPLATE_UUID = '';
    let overviewPieChart = null;
    let usageTendencyChart = null;
    let $timeRangeType = $('#time_range_type');
    let $reportDetailTable = $('#report_detail_table');
    let CURRENT_TIME_RANGE_TYPE = 4; // 默认查看近一月的趋势
    let TABLE_CUSTOM_FIELDS = []; // 表格定制数据
    let filterParams = {}; // 过滤器组件选择的过滤选项参数
    const TAPE_REPORT_TABLE_FILTER_OPTIONS = [
        {
            label: '状态',
            field: 'tapeStatus',
            value: [
                {
                    id: 'tape_status_online',
                    value: TAPE_STATUS.ONLINE,
                    text: '在线',
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'tape_status_offline',
                    value: TAPE_STATUS.OFFLINE,
                    text: '离线',
                    tag: true,
                    type: 'secondary'
                },
                {
                    id: 'tape_status_moving',
                    value: TAPE_STATUS.MOVING,
                    text: '移动中',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'tape_Status_reading',
                    value: TAPE_STATUS.READING,
                    text: '读取中',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'tape_status_writing',
                    value: TAPE_STATUS.WRITTING,
                    text: '写入中',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'tape_status_retrievaling',
                    value: TAPE_STATUS.RETRIEVALING,
                    text: '检索中',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'tape_status_waiting',
                    value: TAPE_STATUS.WAITING,
                    text: '等待中',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'tape_status_ready',
                    value: TAPE_STATUS.READY,
                    text: '就绪',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'tape_status_scanning',
                    value: TAPE_STATUS.SCANNING,
                    text: '扫描中',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'tape_status_exporting',
                    value: TAPE_STATUS.EXPORTING,
                    text: '导出中',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'tape_status_importing',
                    value: TAPE_STATUS.IMPORTING,
                    text: '导入中',
                    tag: true,
                    type: 'primary'
                }
            ]
        }
    ]; // 存储报表数据明细表格过滤器选项数组

    // <----------------------------- BEGIN REPORT OVERVIEW LOGIC ------------------------------->

    /**
     * 初始化存储容量饼图echart
     * @param {*} usedSpace 
     * @param {*} freeSpace 
     */
    const initStorageCapacityEchart = (usedSpace, freeSpace) => {
        let echartId = 'tape_capacity_echart';
        if ($(`#${echartId}`).children().length > 0) {
            // 销毁上一个echart
            echarts.dispose(document.getElementById(echartId));
        }

        const option = {
            backgroundColor: '#FFFFFF',
            title: {
                text: '容量分布',
                left: 'center',
                top: 20,
                textStyle: {
                    color: '#333',
                    fontSize: 20,
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                trigger: 'item',
                formatter: '{a}<br/>{b}: {c} TB ({d}%)'
            },
            legend: {
                orient: 'vertical',
                left: 'left',
                top: 'center',
                data: ['已用空间', '可用空间']
            },
            series: [
                {
                    name: '容量分布',
                    type: 'pie',
                    radius: '65%',
                    center: ['50%', '60%'],
                    avoidLabelOverlap: true,
                    label: {
                        show: true,
                        formatter: '{b}: {d}%'
                    },
                    labelLine: {
                        show: true
                    },
                    data: [
                        {
                            value: usedSpace,
                            name: '已用空间',
                            itemStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: '#83bff6' }, { offset: 1, color: '#188df0' }])
                            }
                        },
                        {
                            value: freeSpace,
                            name: '可用空间',
                            itemStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: '#91cc75' }, { offset: 1, color: '#5ba733' }])
                            }
                        }
                    ]
                }
            ]
        };

        overviewPieChart = echarts.init(document.getElementById('tape_capacity_echart'));

        overviewPieChart.setOption(option);

        $(window).resize(function () { // 监控屏幕大小变化，重新加载echart图
            overviewPieChart.resize();
        });
    }

    /**
     * 获取概览数据
     */
    const getOverviewData = () => {
        axiosGet('report/tape_overview', {}).then(res => {
            $('.overview-card').block();
            try {
                $('#overview_total_tape_library').text(res.data.totalLibraries);
                $('#overview_used_tape').text(res.data.usedTapes);
                $('#overview_online_tape').text(res.data.onlineTapes);
                $('#overview_offline_tape').text(res.data.offlineTapes);
                $('#overview_total_drive').text(res.data.totalDrivers);
                $('#overview_tape_usage').text(res.data.loadingRate);
                $('#overview_tape_usage_unit').text('%');

                let capacityOverview = {
                    totalSpace: !res.data.totalSpace ? 0 : Number(res.data.totalSpace),
                    freeSpace: !res.data.freeSpace ? 0 : Number(res.data.freeSpace),
                    usedSpace: !res.data.usedSpace ? 0 : Number(res.data.usedSpace),
                };

                $('#overview_total_capacity').text(!capacityOverview.totalSpace ? 0 : unitConver(Number(capacityOverview.totalSpace)).size);
                $('#overview_total_capacity_unit').text(!res.data.totalSpace ? 'B' : unitConver(Number(capacityOverview.totalSpace)).unit);
                $('#overview_used_capacity').text(!capacityOverview.usedSpace ? 0 : unitConver(Number(capacityOverview.usedSpace)).size);
                $('#overview_used_capacity_unit').text(!capacityOverview.usedSpace ? 'B' : unitConver(Number(capacityOverview.usedSpace)).unit);
                $('#overview_free_capacity').text(!capacityOverview.freeSpace ? 0 : unitConver(Number(capacityOverview.freeSpace)).size);
                $('#overview_free_capacity_unit').text(!capacityOverview.freeSpace ? 'B' : unitConver(Number(capacityOverview.freeSpace)).unit);

                initStorageCapacityEchart(capacityOverview.usedSpace, capacityOverview.freeSpace);
            } catch (error) {
                
            } finally {
                $('.overview-card').unblock();
            }
        });
    }

    // <----------------------------- END REPORT OVERVIEW LOGIC --------------------------------->


    // <----------------------------- BEGIN REPORT TENDENCY LOGIC ------------------------------->

    /**
     * 初始化存储使用趋势echart
     * @param {*} legend 图例
     * @param {*} series 数据系列
     * @param {*} xAxis x轴数据
     * @param {*} unit 最终存储单位
     */
    const initStorageUsageTendencyEchart = (legend, series, xAxis, unit) => {
        let echartId = 'usage_tendency_echart';
        if ($(`#${echartId}`).children().length > 0) {
            // 销毁上一个echart
            echarts.dispose(document.getElementById(echartId));
        }
        
        const option = {
            backgroundColor: '#FFFFFF',
            title: {
                text: '存储使用趋势',
                left: 'center',
                textStyle: {
                    color: '#333',
                    fontSize: 20,
                    fontWeight: 'bold'
                }
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                type: 'cross',
                label: {
                    backgroundColor: '#6a7985'
                }
                },
                formatter: function (params) {
                    let total = 0;
                    let result = params[0].name + '<br/>';
                    params.forEach(function (item) {
                        total += parseInt(item.value);
                        result += `${item.marker} ${item.seriesName}: ${parseInt(item.value)}${unit}<br/>`;
                    });

                    result += `<strong>总量${total}${unit}</strong>`;

                    return result;
                }
            },
            legend: {
                data: legend,
                top: 40,
                textStyle: {
                    color: '#555'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: [
                {
                    type: 'category',
                    boundaryGap: false,
                    data: xAxis,
                    axisLine: {
                        lineStyle: {
                            color: '#ccc'
                        }
                    },
                    axisLabel: {
                        color: '#555'
                    }
                }
            ],
            yAxis: [
                {
                type: 'value',
                name: `数据量 ${unit}`,
                axisLine: {
                    show: true,
                    lineStyle: {
                    color: '#ccc'
                    }
                },
                splitLine: {
                    lineStyle: {
                    type: 'dashed'
                    }
                },
                axisLabel: {
                    color: '#555'
                }
                }
            ],
            series: series
        };

        usageTendencyChart = echarts.init(document.getElementById(echartId));

        usageTendencyChart.setOption(option);

        $(window).resize(function () { // 监控屏幕大小变化，重新加载echart图
            usageTendencyChart.resize();
        });
    };

    /**
     * 获取磁带组使用趋势数据
     */
    const getUsageTendencyData = () => {
        let startTime = new Date(), endTime = new Date();

        switch (CURRENT_TIME_RANGE_TYPE) {
            case RUNNING_TIME_TYPE.LAST_DAY: // 近一天
                startTime.setDate(endTime.getDate() - 1);
                break;
            case RUNNING_TIME_TYPE.LAST_THREE_DAYS: // 近三天
                startTime.setDate(endTime.getDate() - 3);
                break;
            case RUNNING_TIME_TYPE.LAST_WEEK: // 近一周
                startTime.setDate(endTime.getDate() - 7);
                break;
            case RUNNING_TIME_TYPE.LAST_MONTH: // 近一个月
                startTime.setMonth(endTime.getMonth() - 1);
                break;
            default:
                break;
        }

        let params = { startTime: formatDate(startTime), endTime: formatDate(endTime), tapeStorgeFlag: true }

        axiosGet('report/storage_usage_tendency', params).then(res => {
            $('.tendency-card').block();
            try {
                if (res.success) {
                    const data = res.data || {};
                    const legend = data.legend || [];
                    const xAxisData = data.xAxis || [];
                    const seriesData = data.series || [];

                    let allDataPoints = [];
                    seriesData.forEach(series => {
                        if (series.data && series.data.length > 0) {
                            allDataPoints = allDataPoints.concat(series.data);
                        }
                    });

                    // 获取最大值以确定单位
                    const maxValue = allDataPoints.length > 0 ? Math.max(...allDataPoints) : 0;
                    const unitInfo = unitConver(maxValue);
                    const targetUnit = unitInfo.unit;

                    const convertedSeriesData = seriesData.map(item => {
                        return {
                            ...item,
                            data: item.data.map(value => unitConver(value).size)
                        }
                    });

                    initStorageUsageTendencyEchart(legend, convertedSeriesData, xAxisData, targetUnit);
                } else {
                    UIToastr.showWarning('获取磁带使用趋势数据失败');
                }
            } catch (error) {
                UIToastr.showWarning('获取磁带使用趋势数据失败');
            } finally {
                $('.tendency-card').unblock();
            }
        });
    }

    // <----------------------------- END REPORT OVERVIEW LOGIC ------------------------------->


    // <----------------------------- BEGIN REPORT DETAIL TABLE LOGIC ------------------------------->

    const initDataDetailTableFilter = (filterData) => {
        $('#tape_report_filter_wrapper').initFilter({
            filterSlotId: 'tape_report_filter_wrapper',
            filterBtnId: 'tape_report_filter_btn',
            filters: filterData
        });
    }

    /**
     * 初始化数据明细表格
     */
    const initDataDetailTable = () => {
        if ($reportDetailTable.children().length === 0) {
            const options = {
                url: 'report/tape_details',
                filterBtnId: 'tape_report_filter_btn',
                rightCustomToolbar: 'report-detail-right-toolbar-wrapper',
                buttonsToolbar: '.toolbar-buttons-wrapper.report-detail-toolbar-buttons', // 自定义按钮工具栏class
                search: true, // 是否启用搜索
                searchPlaceholder: '按磁带名搜索', // search input placeholder
                showColumns: false, // 是否启用列筛选
                showExport: false, // 是否启用导出功能
                showRefresh: true, // 是否启用刷新功能
                autoHeight: true, // 是否自适应高度，适用于父容器没有固定高度的场景
                tableContentWrapper: '.table-content-wrapper.report-detail-table-content-wrapper',
                pageList: [10, 25, 50, 100],
                columns: [
                    {
                        field: 'tapeName',
                        title: '磁带名',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('tapeName')
                    },
                    {
                        field: 'tapeType',
                        title: '类型',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('tapeType')
                    },
                    {
                        field: 'libName',
                        title: '磁带库',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('libName')
                    },
                    {
                        field: 'groupName',
                        title: '磁带组',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('groupName')
                    },
                    {
                        field: 'totalSize',
                        title: '总容量',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('totalSize')
                    },
                    {
                        field: 'freeSize',
                        title: '可用容量',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('freeSize')
                    },
                    {
                        field: 'usedSize',
                        title: '已用容量',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('usedSize')
                    },
                    {
                        field: 'tapeStatus',
                        title: '状态',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('tapeStatus'),
                        formatter: function (status) {
                            switch (status) {
                                case TAPE_STATUS.ONLINE:
                                    return '<span class="badge badge-success">' + '在线' + '</span>'
                                case TAPE_STATUS.OFFLINE:
                                    return '<span class="badge badge-secondary">' + '离线' + '</span>'
                                case TAPE_STATUS.MOVING:
                                    return '<span class="badge badge-danger">' + '移动中' + '</span>'
                                case TAPE_STATUS.READING:
                                    return '<span class="badge badge-secondary">' + '读取中' + '</span>'
                                case TAPE_STATUS.WRITTING:
                                    return '<span class="badge badge-secondary">' + '写入中' + '</span>'
                                case TAPE_STATUS.RETRIEVALING:
                                    return '<span class="badge badge-secondary">' + '检索中' + '</span>'
                                case TAPE_STATUS.WAITING:
                                    return '<span class="badge badge-secondary">' + '等待中' + '</span>'
                                case TAPE_STATUS.REWINDING:
                                    return '<span class="badge badge-secondary">' + '倒带' + '</span>'
                                case TAPE_STATUS.READY:
                                    return '<span class="badge badge-primary">' + '就绪' + '</span>'
                                case TAPE_STATUS.SCANNING:
                                    return '<span class="badge badge-secondary">' + '扫描中' + '</span>'
                                case TAPE_STATUS.EXPORTING:
                                    return '<span class="badge badge-secondary">' + '导出中' + '</span>'
                                case TAPE_STATUS.IMPORTING:
                                    return '<span class="badge badge-secondary">' + '导入中' + '</span>'
                                default:
                                    break;
                            }
                        }
                    },
                    {
                        field: 'driverPath',
                        title: '驱动器',
                        sortable: false,
                        visible: TABLE_CUSTOM_FIELDS.includes('driverPath'),
                    },
                    {
                        field: 'backupSetName',
                        title: '备份集',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('backupSetName')
                    }
                ]
            }

            $reportDetailTable.baseTableConfig().init(options);
        } else {
            $reportDetailTable.bootstrapTable('refresh');
        }
    }

    // <----------------------------- END REPORT DETAIL TABLE LOGIC ---------------------------------->


    const getReportDetail = () => {
        axiosGet('report/detail', { uuid: REPORT_TEMPLATE_UUID }).then(res => {
            try {
                if (res.success) {
                    let reportName = res.data.templateName;
                    let description = res.data.description;
                    let viewOverview = res.data.detail.viewOverview;
                    let viewUsageTendency = res.data.detail.viewUsageTendency;
                    let customFields = res.data.detail.customFields;

                    $('#overview_title').empty().html(reportName);
                    $('#overview_description').empty().html(description);

                    // 概览数据
                    if (viewOverview) {
                        $('.overview-wrapper').removeClass('display-none');
                        getOverviewData();
                    } else {
                        $('.overview-wrapper').addClass('display-none');
                    }

                    // 使用趋势
                    if (viewUsageTendency) {
                        $('.tendency-card').removeClass('display-none');

                        getUsageTendencyData();
                    } else {
                        $('.tendency-card').addClass('display-none');
                    }

                    // 数据明细
                    if (customFields.length !== 0) {
                        $('.table-data-card').removeClass('display-none');

                        TABLE_CUSTOM_FIELDS = [...customFields];
                        let filterFields = []; // 获取过滤器组件中的选项
                        if (TABLE_CUSTOM_FIELDS.indexOf('tapeStatus') > -1) filterFields.push('tapeStatus');

                        if (filterFields.length > 0) {
                            let filterData = TAPE_REPORT_TABLE_FILTER_OPTIONS.filter(option => {
                                return filterFields.includes(option.field);
                            });

                            // 渲染数据明细表格过滤器
                            initDataDetailTableFilter(filterData);
                        }

                        // 渲染数据明细表格
                        initDataDetailTable();

                    } else {
                        $('.table-data-card').addClass('display-none');
                    }
                } else {
                    UIToastr.showWarning('获取报表详情数据失败');
                }
            } catch (error) {
                console.log(error, 'error');
                UIToastr.showWarning('获取报表详情数据失败')
            }
        });
    }

    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';
        REPORT_TEMPLATE_UUID = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存报表uuid

        if (REPORT_TEMPLATE_UUID) {
            getReportDetail();
        }
    }

    const initListeners = () => {
        $timeRangeType.select2({
            minimumResultsForSearch: Infinity, // 取消下拉的搜索
            theme: 'bootstrap-5'
        });

        $timeRangeType.on('change', function () {
            CURRENT_TIME_RANGE_TYPE = parseInt($(this).val());

            getUsageTendencyData();
        });

        window.$off('tape_report_filter_btn-updateFilterEvent');

        window.$on('tape_report_filter_btn-updateFilterEvent', (filterData) => {
            if (filterData.length > 0) {
                let tapeStatusList = getTableFilterParams('tapeStatus', filterData);

                if (tapeStatusList.value.length > 0) {
                    filterParams.status = tapeStatusList.value;
                } else {
                    filterParams.status = '';
                }
            } else {
                filterParams.status = '';
            }

            $reportDetailTable.bootstrapTable('refresh', { query: { ...filterParams } });
        });
    }
    
    return{
        init: function () {
            initRouteParams();
            initListeners();
        }
    };
}();
jQuery(document).ready(function () {
    TapeReportDetail.init();
});