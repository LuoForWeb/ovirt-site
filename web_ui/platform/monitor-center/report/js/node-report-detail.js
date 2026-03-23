var NodeReportDetail = function () {
    let REPORT_TEMPLATE_UUID = '';
    let overviewPieChart = null;
    let rankBarChart = null;
    let usageTendencyChart = null;
    let storageAvaliableForecastChart = null;
    let $timeRangeType = $('#time_range_type');
    let $reportDetailTable = $('#report_detail_table');
    let CURRENT_TIME_RANGE_TYPE = 4; // 默认查看近一月的趋势
    let CURRENT_LOAD_TIME_TYPE = 1; // 默认查看1min负载趋势
    let TABLE_CUSTOM_FIELDS = []; // 表格定制数据
    const NODE_REPORT_TABLE_FILTER_OPTIONS = [
        {
            label: '节点类型',
            field: 'node_type',
            value: [
                {
                    id: 'node_type_main',
                    value: 1,
                    text: '主节点'
                },
                {
                    id: 'node_type_child',
                    value: 2,
                    text: '子节点'
                },
            ]
        },
        {
            label: '资源限制',
            field: 'node_resource_limit_flag',
            value: [
                {
                    id: 'node_resource_limit_on',
                    value: 1,
                    text: '已限制',
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'node_resource_limit_off',
                    value: 2,
                    text: '未限制',
                    tag: true,
                    type: 'secondary'
                }
            ]
        },
        {
            label: '部署状态',
            field: 'deploy_flag',
            value: [
                {
                    id: 'deploy_flag_on',
                    value: 1,
                    text: '已部署',
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'deploy_flag_off',
                    value: 2,
                    text: '未部署',
                    tag: true,
                    type: 'secondary'
                }
            ]
        },
        {
            label: '节点状态',
            field: 'status',
            value: [
                {
                    id: 'status_normal',
                    value: 1,
                    text: '正常',
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'status_abnormal',
                    value: 2,
                    text: '异常',
                    tag: true,
                    type: 'warning'
                },
                {
                    id: 'status_modifing',
                    value: 3,
                    text: '修改中',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'status_deleting',
                    value: 4,
                    text: '删除中',
                    tag: true,
                    type: 'danger'
                },
                {
                    id: 'status_updating',
                    value: 5,
                    text: '升级中',
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'status_ofline',
                    value: 6,
                    text: '离线',
                    tag: true,
                    type: 'secondary'
                }
            ]
        }
    ];
    const LOAD_TIME_RADIO_TYPES = [
        {
            id: 'radio_load_time_1',
            label: 'load 1min',
            value: 1
        },
        {
            id: 'radio_load_time_5',
            label: 'load 5min',
            value: 2
        },
        {
            id: 'radio_load_time_15',
            label: 'load 15min',
            value: 3
        },
    ]; // 负载时间类型单选组

    /**
     * 获取概览数据
     */
    const getOverviewData = () => {
        $('.overview-card').block();
        axiosGet('report/node/overview', {}).then(res => {
            $('.overview-card').unblock();

            $('#overview_total_node').text(res.data.totalNodes || 0);
            $('#overview_online_node').text(res.data.onlineNodes || 0);
            $('#overview_offline_node').text(res.data.offlineNodes || 0);
            $('#overview_abnormal_node').text(res.data.abnormalNodes || 0);
        });
    }

    const initNodeLoadTendencyEchart = (legendData, xAxisData, seriesData) => {
        let echartId = 'usage_tendency_echart';
        if ($(`#${echartId}`).children().length > 0) {
            // 销毁上一个echart
            echarts.dispose(document.getElementById(echartId));
        }

        const option = {
            color: ['#5470c6', '#fac858', '#ee6666', '#3ba272', '#975fe4'],
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow',
                    label: { backgroundColor: '#283b4c' }
                },
                backgroundColor: '#ffffffcc',
                borderColor: '#b0c4de',
                borderWidth: 1,
                textStyle: { color: '#1e2b3a', fontSize: 12 },
                formatter: function(params) {
                    let res = `<strong style="font-size:1.05rem;margin-bottom:4px;display:block;">${params[0].axisValue}</strong>`;
                    for (let i = 0; i < params.length; i++) {
                        const colorSpan = `<span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:${params[i].color};margin-right:6px;"></span>`;
                        res += `${colorSpan}${params[i].seriesName}: <strong>${params[i].value}</strong><br>`;
                    }
                    return res;
                }
            },
            legend: { show: false, bottom: 0, data: legendData },
            grid: {
                left: '7%',
                right: '6%',
                bottom: '10%',
                top: '8%',
                borderColor: '#e2e9f0'
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: xAxisData,
                axisTick: { alignWithLabel: true, length: 8, lineStyle: { color: '#cbd5e1' } },
                axisLine: { lineStyle: { color: '#94a3b8', width: 1.5 } },
                axisLabel: { fontSize: 12, fontWeight: 400, color: '#334e68', margin: 10 },
            },
            yAxis: {
                type: 'value',
                name: '负载指数',
                nameTextStyle: { fontSize: 13, fontWeight: 400, color: '#62748c', padding: [0, 30, 0, 0] },
                min: 0,
                splitNumber: 6,
                axisLabel: { fontSize: 11, color: '#4a5f73' },
                splitLine: { lineStyle: { type: 'dashed', color: '#e2e8f0', width: 1 } },
                axisLine: { show: false }
            },
            series: seriesData
        };

        usageTendencyChart = echarts.init(document.getElementById(echartId));

        usageTendencyChart.setOption(option);

        $(window).resize(function () { // 监控屏幕大小变化，重新加载echart图
            usageTendencyChart.resize();
        });
    }

    const initLoadTimeRadioGroup = () => {
        $('#load_time_radio_group').initRadioGroup({
            name: 'loadTime',
            radioData: LOAD_TIME_RADIO_TYPES,
            selectedValue: 1,
            onChange: function (currentValue) {
                CURRENT_LOAD_TIME_TYPE = parseInt(currentValue);

                getUsageTendencyData();
            }
        });
    }

    const handleTimeRangeChange = () => {
        $timeRangeType.select2({
            minimumResultsForSearch: Infinity, // 取消下拉的搜索
            theme: 'bootstrap-5'
        });

        $timeRangeType.on('change', () => {
            CURRENT_TIME_RANGE_TYPE = parseInt($('#time_range_type').val());

            getUsageTendencyData();
        })
    }

    /**
     * 获取节点负载趋势数据
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

        let params = { startTime: formatDate(startTime), endTime: formatDate(endTime), loadMinuteType: CURRENT_LOAD_TIME_TYPE };
        
        $('.tendency-card').block();
        axiosGet('report/node/load_tendency', params).then(res => {
            try {
                if (res.success) {
                    const { legend, xAxis, series} = res.data;

                    initNodeLoadTendencyEchart(legend.data, xAxis.data, series);
                } else {
                    UIToastr.showWarning('获取节点负载趋势数据失败');
                } 
            } catch (error) {
                
            } finally {
                $('.tendency-card').unblock();
            }
        });
    }

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

                    // 负载情况
                    if (viewUsageTendency) {
                        $('.tendency-card').removeClass('display-none');

                        getUsageTendencyData();
                    } else {
                        $('.tendency-card').addClass('display-none');
                    }

                    // 数据明细
                    if (customFields.length !== 0) {
                        $('.table-card').removeClass('display-none');

                        TABLE_CUSTOM_FIELDS = [...customFields];
                        let filterFields = []; // 获取过滤器组件中的选项

                        if (TABLE_CUSTOM_FIELDS.indexOf('node_type') > -1) filterFields.push('node_type');
                        if (TABLE_CUSTOM_FIELDS.indexOf('node_resource_limit_flag') > -1) filterFields.push('node_resource_limit_flag');
                        if (TABLE_CUSTOM_FIELDS.indexOf('deploy_flag') > -1) filterFields.push('deploy_flag');
                        if (TABLE_CUSTOM_FIELDS.indexOf('status') > -1) filterFields.push('status');

                        if (filterFields.length > 0) {
                            let filterData = NODE_REPORT_TABLE_FILTER_OPTIONS.filter(option => {
                                return filterFields.includes(option.field);
                            });

                            // 渲染数据明细表格过滤器
                            initDataDetailTableFilter(filterData);
                        }

                        // 渲染数据明细表格
                        initDataDetailTable();
                    }
                } else {
                    UIToastr.showWarning('获取报表详情数据失败');
                }
            } catch (error) {
                UIToastr.showWarning('获取报表详情数据失败');
            }
        });
    }

    // <----------------------------- BEGIN REPORT DETAIL TABLE LOGIC ------------------------------->

    const initDataDetailTableFilter = (filterData) => {
        $('#node_report_table_filter_wrapper').initFilter({
            filterSlotId: 'node_report_table_filter_wrapper',
            filterBtnId: 'node_report_table_filter_btn',
            filters: filterData
        });
    }

    /**
     * 初始化数据明细表格
     */
    const initDataDetailTable = () => {
        if ($reportDetailTable.children().length === 0) {
            const options = {
                url: 'report/node_details',
                filterBtnId: '',
                rightCustomToolbar: 'report-detail-right-toolbar-wrapper',
                buttonsToolbar: '.toolbar-buttons-wrapper.report-detail-toolbar-buttons', // 自定义按钮工具栏class
                search: true, // 是否启用搜索
                searchPlaceholder: '按节点名搜索', // search input placeholder
                showColumns: false, // 是否启用列筛选
                showExport: false, // 是否启用导出功能
                showRefresh: true, // 是否启用刷新功能
                tableContentWrapper: '.table-content-wrapper.report-detail-table-content-wrapper',
                pageList: [10, 25, 50, 100],
                columns: [
                    {
                        field: 'host_name',
                        title: '节点名',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('host_name')
                    },
                    {
                        field: 'ip',
                        title: 'IP地址',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('ip')
                    },
                    {
                        field: 'node_function',
                        title: '节点功能',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('node_function')
                    },
                    {
                        field: 'node_pool_list',
                        title: '节点资源池',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('node_pool_list')
                    },
                    {
                        field: 'node_resource_limit_flag',
                        title: '资源限制',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('node_resource_limit_flag')
                    },
                    {
                        field: 'deploy_flag',
                        title: '部署状态',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('deploy_flag'),
                        formatter: function (value) {
                            if(value) {
                                return `<span class="badge badge-success">已部署</span>`;
                             } else {
                                return `<span class="badge badge-secondary">未部署</span>`;
                            }
                         }
                    },
                    {
                        field: 'status',
                        title: '节点状态',
                        sortable: true,
                        visible: TABLE_CUSTOM_FIELDS.includes('status'),
                        formatter: function (value, row) {
                            /**
                             * 1、如果节点处于未部署状态(即bd_module_server里面没有该节点任何记录)，那么显示--
                             * 2、如果bd_node的status值为0，再判断如果该节点在线(在线判定为该节点在bd_module_server里面的所有记录都在线)，显示在线；否则显示异常，鼠标移上去显示离线的服务
                             * 3、如果bd_node的status值为1，那么显示删除中，此时选择该节点删除时显示“当前节点正在删除中，请稍后重试”
                             * 4、如果bd_node的status值为2，那么显示修改中，此时选择该节点删除时显示“当前节点正在修改中，请稍后重试”
                             * 5、如果bd_node的status值为其他，那么显示为--
                             */
                            if (!!!row.deploy_flag) {
                                return '--';
                            }

                            let text = '';
                            let badgeClass = 'badge-secondary';
                            let des = '';

                            switch (parseInt(value)) {
                                case NODE_OPERATE_STATUS_ENUM.UNKNOWN:
                                    badgeClass = 'badge-warning';
                                    text = '异常';
                                    des = row.offline_module_des;
                                    if (!!row.online_flag) {
                                        badgeClass = 'badge-success';
                                        text = '正常';
                                    }
                                    break;
                                case NODE_OPERATE_STATUS_ENUM.MODIFYING:
                                    text = '修改中';
                                    break;
                                case NODE_OPERATE_STATUS_ENUM.DELETING:
                                    badgeClass = 'badge-danger';
                                    text = '删除中';
                                    break;
                                case NODE_OPERATE_STATUS_ENUM.UPGRADING:
                                    text = '升级中';
                                    break;
                                case NODE_OPERATE_STATUS_ENUM.OFFLINE:
                                    badgeClass = 'badge-warning';
                                    text = '异常';
                                    des = '节点离线';
                                    break;
                                default:
                                    return '--';
                            }

                            return `<span class="badge ${badgeClass}" data-bs-toogle="tooltip" title="${des}">${text}</span>`
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

    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';
        REPORT_TEMPLATE_UUID = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存报表uuid

        if (REPORT_TEMPLATE_UUID) {
            getReportDetail();
        }
    }

    const initListeners = () => {

        initLoadTimeRadioGroup();

        handleTimeRangeChange();
    }

    return{
        init: function () {
            initRouteParams();
            initListeners();
        }
    };
}();
jQuery(document).ready(function () {
    NodeReportDetail.init();
});