/*
 * @Author: ChengJiaFu
 * @Date: 2026-02-09 09:52:44
 * @Description: 磁带报表详情LOGIC
 * @version: 1.0
 */
var TapeReportDetail = function () {
    let REPORT_TEMPLATE_UUID = '';
    let overviewPieChart = null;
    let rankBarChart = null;
    let usageTendencyChart = null;
    let storageAvaliableForecastChart = null;
    let $timeRangeType = $('#time_range_type');
    let $reportDetailTable = $('#report_detail_table');
    let CURRENT_TIME_RANGE_TYPE = 4; // 默认查看近一月的趋势
    let TABLE_CUSTOM_FIELDS = []; // 表格定制数据
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
        let echartId = 'storage_capacity_echart';
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

        overviewPieChart = echarts.init(document.getElementById('storage_capacity_echart'));

        overviewPieChart.setOption(option);

        $(window).resize(function () { // 监控屏幕大小变化，重新加载echart图
            overviewPieChart.resize();
        });
    }

    /**
     * 初始化存储容量排名echart
     * @param {*} topStorageUsageList 存储容量排名列表
     */
    const initStorageRankEchart = (topStorageUsageList) => {
        let echartId = 'capacity_rank_echart';
        if ($(`#${echartId}`).children().length > 0) {
            // 销毁上一个echart
            echarts.dispose(document.getElementById(echartId));
        }

        const option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow' // 默认为 'line'，'shadow' 效果更佳
                },
                formatter: function(params) {
                    const data = params[0];
                    return `${data.name}<br/>容量使用率: <strong>${data.value}%</strong>`;
                }
            },
            grid: {
                left: '5%',
                right: '10%',
                bottom: '3%',
                containLabel: true // 自动计算 grid 大小，防止标签溢出
            },
            xAxis: {
                type: 'value',
                min: 0,
                max: 100, // 使用率最大为100%
                axisLabel: {
                    formatter: '{value}%' // X轴刻度添加百分号
                },
                splitLine: {
                    show: true,
                    lineStyle: {
                        type: 'dashed',
                        color: '#eee'
                    }
                }
            },
            yAxis: {
                type: 'category',
                data: topStorageUsageList.map(i => { return i.name}),
                inverse: true, // 让第一名显示在最上面
                axisTick: {
                    show: false
                },
                axisLine: {
                    show: false
                }
            },
            series: [
                {
                    name: '使用率',
                    type: 'bar',
                    data: topStorageUsageList,
                    label: {
                        show: true,
                        position: 'right', // 标签显示在条形图右侧
                        formatter: '{c}%',
                        color: '#333',
                        fontWeight: 'bold'
                    },
                    barWidth: '60%', // 条形图宽度
                    itemStyle: {
                        borderRadius: [0, 5, 5, 0], // 条形图圆角
                        // 使用渐变色来突出高使用率的风险
                        color: {
                            type: 'linear',
                            x: 0,
                            y: 0,
                            x2: 1,
                            y2: 0,
                            colorStops: [
                                {
                                    offset: 0,
                                    color: '#83bff6' // 蓝色 (安全)
                                }, 
                                {
                                    offset: 0.7,
                                    color: '#fac858' // 黄色 (警告)
                                }, 
                                {
                                    offset: 1,
                                    color: '#ee6666' // 红色 (危险)
                                }
                            ]
                        }
                    },
                    // 高亮效果
                    emphasis: {
                        focus: 'series',
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        };

        rankBarChart = echarts.init(document.getElementById('capacity_rank_echart'));

        rankBarChart.setOption(option);

        $(window).resize(function () { // 监控屏幕大小变化，重新加载echart图
            rankBarChart.resize();
        });
    }

    /**
     * 获取概览数据
     */
    const getOverviewData = () => {
        pAjaxRequest({}, 'api/v1/report/storage_overview', 'GET', (res) => {
            $('.overview-card').block();
            try {
                
                $('#overview_total_storage').text(res.data.storageTotal);
                $('#overview_online_storage').text(res.data.onlineCount);
                $('#overview_offline_storage').text(res.data.offlineCount);
                $('#overview_storage_usage').text(res.data.utilizationRate);
                $('#overview_storage_usage_unit').text('%');

                let capacityOverview = {
                    totalSpace: !res.data.totalSpace ? 0 : Number(res.data.totalSpace),
                    freeSpace: !res.data.freeSpace ? 0 : Number(res.data.freeSpace),
                    usedSpace: !res.data.usedSpace ? 0 : Number(res.data.usedSpace),
                };

                let topStorageUsageList = res.data.topStorageUsageList;

                $('#overview_total_capacity').text(!capacityOverview.totalSpace ? 0 : unitConver(Number(capacityOverview.totalSpace)).size);
                $('#overview_total_capacity_unit').text(!res.data.totalSpace ? 'B' : unitConver(Number(capacityOverview.totalSpace)).unit);
                $('#overview_used_capacity').text(!capacityOverview.usedSpace ? 0 : unitConver(Number(capacityOverview.usedSpace)).size);
                $('#overview_used_capacity_unit').text(!capacityOverview.usedSpace ? 'B' : unitConver(Number(capacityOverview.usedSpace)).unit);
                $('#overview_free_capacity').text(!capacityOverview.freeSpace ? 0 : unitConver(Number(capacityOverview.freeSpace)).size);
                $('#overview_free_capacity_unit').text(!capacityOverview.freeSpace ? 'B' : unitConver(Number(capacityOverview.freeSpace)).unit);

                initStorageCapacityEchart(capacityOverview.usedSpace, capacityOverview.freeSpace);

                initStorageRankEchart(topStorageUsageList);
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
     * 获取存储使用趋势数据
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

        let params = {
            startTime: '2025-12-01',
            endTime: '2025-12-31'
        }

        pAjaxRequest(params, 'api/v1/report/storage_usage_tendency', 'GET', (res) => {
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
                    UIToastr.showWarning('获取存储使用趋势数据失败');
                }
            } catch (error) {
                UIToastr.showWarning('获取存储使用趋势数据失败');
            } finally {
                $('.tendency-card').unblock();
            }
        });
    }

    // <----------------------------- END REPORT OVERVIEW LOGIC ------------------------------->


    // <----------------------------- BEGIN REPORT DETAIL TABLE LOGIC ------------------------------->

    /**
     * 初始化数据明细表格
     */
    const initDataDetailTable = () => {
        if ($reportDetailTable.children().length === 0) {
            const options = {
                url: 'report/storage_list',
                filterBtnId: '',
                rightCustomToolbar: 'report-detail-right-toolbar-wrapper',
                buttonsToolbar: '.toolbar-buttons-wrapper.report-detail-toolbar-buttons', // 自定义按钮工具栏class
                search: true, // 是否启用搜索
                searchPlaceholder: '按存储别名搜索', // search input placeholder
                showColumns: false, // 是否启用列筛选
                showExport: false, // 是否启用导出功能
                showRefresh: true, // 是否启用刷新功能
                tableContentWrapper: '.table-content-wrapper.report-detail-table-content-wrapper',
                pageList: [10, 25, 50, 100],
                columns: [
                    {
                        field: 'name',
                        title: '存储别名',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('name')
                    },
                    {
                        field: 'type',
                        title: '类型',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('type')
                    },
                    {
                        field: 'totalCapacity',
                        title: '总容量',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('totalCapacity')
                    },
                    {
                        field: 'freeCapacity',
                        title: '可用容量',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('freeCapacity')
                    },
                    {
                        field: 'usedCapacity',
                        title: '已用容量',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('usedCapacity')
                    },
                    {
                        field: 'storageStatus',
                        title: '状态',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('storageStatus'),
                        formatter: function (value) {
                            if(value) {
                                return `<span class="badge badge-success">正常</span>`;
                             } else {
                                return `<span class="badge badge-secondary">异常</span>`;
                            }
                         }
                    },
                    {
                        field: 'node',
                        title: '节点',
                        sortable: false,
                        visible: TABLE_CUSTOM_FIELDS.includes('node'),
                    },
                    {
                        field: 'nodeStatus',
                        title: '状态',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('nodeStatus'),
                        formatter: function (value) {
                           if(value === 1) {
                               return `<span class="badge badge-success">正常</span>`;
                            } else {
                               return `<span class="badge badge-secondary">异常</span>`;
                           }
                        }
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
        pAjaxRequest({ uuid: REPORT_TEMPLATE_UUID }, 'api/v1/report/detail', 'GET', (res) => {
        try {
            if (res.success) {
                let reportName = res.data.templateName;
                let description = res.data.description;
                let viewOverview = res.data.detail.viewOverview;
                let viewUsageTendency = res.data.detail.viewUsageTendency;
                let availabilityForecase = res.data.detail.availabilityForecase;
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
                    if (TABLE_CUSTOM_FIELDS.indexOf('type') > -1) filterFields.push('type');
                    if (TABLE_CUSTOM_FIELDS.indexOf('storageStatus') > -1) filterFields.push('storageStatus');

                    if (filterFields.length > 0) {
                        let filterData = TAPE_REPORT_TABLE_FILTER_OPTIONS.filter(option => {
                            return filterFields.includes(option.field);
                        });

                        // 渲染数据明细表格过滤器
                        // initDataDetailTableFilter(filterData);
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
        });
    }
    
    return{
        init: function () {
            console.log('磁带报表来咯');
            initRouteParams();
            initListeners();
        }
    };
}();
jQuery(document).ready(function () {
    TapeReportDetail.init();
});