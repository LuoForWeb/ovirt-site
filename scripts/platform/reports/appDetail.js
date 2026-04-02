var AppDetail = (function() {
    const TASK_RUNNING_STATUS_TYPE = {
        ALL: 1,
        SUCCESSED: 2,
        FAILED: 3
    }; // 任务运行状态类型
    const STATUS_TYPE_TO_ECHART_ID_MAP = {
        1: 'm365_report_all_chart',
        2: 'm365_report_success_chart',
        3: 'm365_report_failed_chart'
    }; // 任务运行趋势状态对应echart id
    const M365_FILTER_OPTIONS = [
        {
            label: LANG.UI_PUBLIC_TASK_STATUS,
            field: 'task_status',
            value: [
                {
                    id: 'history_task_status_success',
                    value: 1,
                    text: LANG.UI_PUBLIC_SUCCESS,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'history_task_status_suspend',
                    value: 2,
                    text: LANG.UI_VISUAL_SUSPEND,
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'history_task_status_abnormal',
                    value: 3,
                    text: LANG.UI_NODE_ABNORMAL,
                    tag: true,
                    type: 'warning'
                },
                {
                    id: 'history_task_status_error',
                    value: 4,
                    text: LANG.UI_VOL_CDP_OPERATION_FAIL,
                    tag: true,
                    type: 'danger'
                }
            ]
        }
    ]; // M365报表过滤选项数组
    let M365_REPORT_TEMPLATE_UUID = '';
    let CURRENT_TASK_RUNNING_ECHART_ID = 'm365_report_all_chart';
    let CURRENT_RUNNING_TENDENCY_QUERY_PARAMS = {
        type: CONF.REPORT_TYPE.M365,
        timeInterval: CONF.RUNNING_TIME_TYPE.LAST_MONTH,
        statusType: TASK_RUNNING_STATUS_TYPE.ALL
    }; // 当前任务运行趋势查询参数对象
    let taskRunningChart = null;
    let customField = {};
    let FILTER_PARAMS = {}; // 过滤器组件选择的过滤选项参数
    let startTime = '', endTime = '', searchVal = '';
    
    // <------------------------- BEGIN OVERFIEW DATA ----------------------------------->

    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';
        M365_REPORT_TEMPLATE_UUID = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存报表uuid

        if (M365_REPORT_TEMPLATE_UUID) {
            getReportDetail();
        }
    }

    /**
     * 获取M365报表概览数据
     */
    const initOverviewData = () => {
        pAjaxRequest({}, '/api/v1/report/template/overview/7', 'get', function (res) {
            try {
                if (res.success) {
                    let overviewList = res.data.overviewList;

                    $('#app_total_num').text(overviewList.app_number);
                    $('#app_online_num').text(overviewList.app_online);
                    $('#app_offline_num').text(overviewList.app_offline);
                    $('#app_protected_num').text(overviewList.app_protected);
                    $('#app_backup_data').text(!overviewList.backup_data ? 0 : unitConver(Number(overviewList.backup_data)).size);
                    $('#app_backup_data_unit').text(!overviewList.backup_data ? 'B' : unitConver(Number(overviewList.backup_data)).unit);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_M365_REPORT_OVERVIEW_DATA_FAILED);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_M365_REPORT_OVERVIEW_DATA_FAILED);
            }
        });
    }

    /**
     * 获取M365报表详情
     */
    const getReportDetail = () => {
        pAjaxRequest({}, '/api/v1/report/template/detail/' + M365_REPORT_TEMPLATE_UUID, 'GET', (res) => {
            try {
                Metronic.blockUI({target: ".report-detail__content",animate: true});

                if (res.success) {
                    let { custom_field, overview, running_tendency } = { ...res.data };

                    // 渲染概览数据
                    if (!overview.app_number && !overview.app_online && !overview.app_offline && !overview.app_protected && !overview.backup_data) {
                        $('.overview-header').addClass('display-none');
                        $('.report-detail-boxes').addClass('display-none');
                    } else { // 只要配置了一项就显示数据概览
                        $('.overview-header').removeClass('display-none');
                        $('.report-detail-boxes').removeClass('display-none');

                        overview.app_number ? $('.app-total-box').removeClass('display-none') : $('.app-total-box').hide();
                        overview.app_online ? $('.app-online-box').removeClass('display-none') : $('.app-online-box').hide();
                        overview.app_offline ? $('.app-offline-box').removeClass('display-none') : $('.app-offline-box').hide();
                        overview.app_protected ? $('.app-protected-box').removeClass('display-none') : $('.app-protected-box').hide();
                        overview.backup_data ? $('.app-backup-data-box').removeClass('display-none') : $('.app-backup-data-box').hide();

                        initOverviewData();
                    }

                    // 渲染任务运行趋势echart图
                    if (running_tendency.history) {
                        $('.echart-header').removeClass('display-none');
                        $('.echart-wrapper').removeClass('display-none');
    
                        getM365ReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS, CURRENT_TASK_RUNNING_ECHART_ID);
                    } else {
                        $('.echart-header').addClass('display-none');
                        $('.echart-wrapper').addClass('display-none');
                    }

                    customField = { ...custom_field };

                    // 获取过滤器选项数组
                    let fields = [];
                    if (customField.task_status) fields.push('task_status');

                     if (fields.length > 0) {
                        // 渲染数据明细表格过滤器
                        initM365TableFilter(M365_FILTER_OPTIONS);
                    }

                    // 定制数据选了开始时间和完成时间则渲染时间选择器
                    if (customField.start_time) {
                        initM365TableDaterangePicker();
                    }

                    // 渲染数据明细表格
                   initM365ReportDetailsTable();
                } else {
                    UIToastr.showWarning(LANG.UI_GET_M365_DATA_FAILED);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_M365_DATA_FAILED);
            } finally {
                Metronic.unblockUI('.report-detail__content');
            }
        });
    }

    // <------------------------- END OVERFIEW DATA ------------------------------------->

    // <------------------------- BEGIN ECHART DATA ------------------------------------->

    /**
     * 初始化M365任务运行报表echart
     * @param {*} xData 
     * @param {*} yData 
     * @param {*} onlyData 
     * @param {*} echartId 
     * @param {*} unit 
     */
    const initM365ReportEchart = (xData, yData, onlyData, echartId, unit) => {
        if ($(`#${echartId}`).children().length > 0) {
            // 销毁上一个echart
            echarts.dispose(document.getElementById(echartId));
        }

        let chartWidth = $('.charts-wrapper .tab-content .tab-pane').width();
        let chartHeight = $('.charts-wrapper .tab-content .tab-pane').height();
        $(`#${echartId}`).css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

        taskRunningChart = echarts.init(document.getElementById(echartId));

        const gridConfig = {
            left: '20',
            right: '2%',
            top: '15%',
            bottom: 70,
            containLabel: true
        };

        const option = {
            tooltip: {
                trigger: 'axis',
                icon: 'circle',
                enterable: false,
                backgroundColor: '#fff',
                extraCssText: 'box-shadow:0px 5px 25px 0px rgba(4, 13, 45, 0.15)',
                borderWidth: 1,
                borderColor: '#fff',
                axisPointer: {
                    show: true,
                    icon: 'circle',
                    lineStyle: {
                        type: 'dashed'
                    }
                },
                axisLabel: {
                    color: '#718096'
                },
                formatter: function(params) {
                    let relVal = params[0].name;
                    for (let i = 0, l = params.length; i < l; i++) {
                        let pvalue = `${params[i].value.toFixed(2)}${unit}`;
                        relVal += '<br/>' + ' <span style="display:inline-block;margin-right:5px;width:12px;height:12px;border-radius: 2px!important;background-color:#13C4B6;"></span>' + ' <span style="color: #2D3748!important">' + LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA  + pvalue + '</span> ' ;
                    }
                    return relVal;
                }
            },
            grid: gridConfig,
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: xData,
                axisLine: {
                    lineStyle: {
                        type: 'solid',
                        color: '#C9CDD4', // 左边线的颜色
                        width:'1'// 坐标线的宽度
                    }
                },
                axisLabel: {
                    color:'#86909C'
                }
            },
            yAxis: {
                type: 'value',
                name: `(${unit})`,
                nameTextStyle: {
                    color: '#86909C',
                    fontSize:14
                },
                splitLine:{ // 虚线
                    show:true,
                    lineStyle:{
                        type:'dashed'
                    }
                },
                axisLine: {
                    lineStyle: {
                        type: 'solid',
                        color: '#86909C', // 左边线的颜色
                        width:'1'// 坐标线的宽度
                    }
                },
                axisLabel: {
                    color:'#86909C'
                }
            },
            dataZoom: [
                {
                    height: 22, // 滚动条高度
                    moveHandleSize: 3, // 滚动Handle条高度
                    textStyle: {
                        color: 'transparent' // 隐藏两端的固定文本
                    },
                    fillerColor:'rgba(51, 175, 125,0.1)',
                    dataBackground: {
                        areaStyle: {
                            color: '#86dac2'
                        },
                        lineStyle: {
                            opacity: 0.8,
                            color: '#86dac2'
                        }
                    },
                    brushSelect: true
                }, 
                {
                    type: 'inside'
                }
            ],
            series: [
                {
                    lineStyle: { // 设置线条的style等
                        color: '#0FC6C2' // 折线线条颜色:绿色
                    },
                    areaStyle: { // 渐变色
                        color: {
                            x: 0,
                            y: 0,
                            x2: 0,
                            y2: 1,
                            colorStops: [{
                                offset: 0, color: 'aquamarine' // 0% 处的颜色
                            }, {
                                offset: 1, color: 'white' //
                            }]

                        }
                    },
                    smooth: true, // 平滑
                    symbol: 'none',
                    data: yData,
                    emphasis: {
                        areaStyle: {
                            color: {
                                x: 0,
                                y: 0,
                                x2: 0,
                                y2: 1,
                                colorStops: [{
                                    offset: 0, color: 'aquamarine' // 0% 处的颜色
                                }, {
                                    offset: 1, color: 'white' //
                                }]
                            }
                        }
                    },
                    type: 'line'
                }
            ]
        };

        taskRunningChart.setOption(option);

        const updateMarkers = () => {
            const currentOption = taskRunningChart.getOption();
            if (!currentOption || !currentOption.dataZoom || !currentOption.dataZoom[0]) {
                return;
            }
            const dataZoom = currentOption.dataZoom[0];
            const allXData = xData;
            const allYData = yData;

            const startIndex = dataZoom.startValue;
            const endIndex = dataZoom.endValue;
            
            const visibleDataCount = endIndex - startIndex + 1;

            if (xData.length === 1 || visibleDataCount === 1) {
                const index = (xData.length === 1) ? 0 : startIndex;
                const yValue = allYData[index];
                const xValue = allXData[index];

                if (parseFloat(yValue) === 0) {
                    taskRunningChart.setOption({
                        tooltip: {
                            show: false // Diagnostic: Disable tooltip
                        },
                        series: [{
                            markPoint: {
                                silent: true,
                                symbol: 'circle',
                                symbolSize: 8,
                                data: [{ xAxis: xValue, yAxis: 0 }]
                            },
                            markLine: null
                        }]
                    });
                } else {
                    taskRunningChart.setOption({
                        tooltip: {
                            show: true
                        },
                        series: [{
                            markLine: {
                                silent: true,
                                animation: false,
                                symbol: ['circle', 'arrow'],
                                symbolSize: [8, 10],
                                label: { show: false },
                                lineStyle: { type: 'dashed' },
                                data: [[
                                    { xAxis: xValue, yAxis: 0 },
                                    { xAxis: xValue, yAxis: yValue }
                                ]]
                            },
                            markPoint: null
                        }]
                    });
                }
            } else { // More than 1 day visible
                let newMarkLine = null;
                if (onlyData) {
                    // Restore the multi-day highlight line
                    newMarkLine = {
                        silent: true,
                        symbol: 'none',
                        lineStyle: { type: 'dashed' },
                        data: [{ xAxis: onlyData }]
                    };
                }
                taskRunningChart.setOption({
                    tooltip: {
                        show: true // Diagnostic: Re-enable tooltip
                    },
                    series: [{
                        markPoint: null,
                        markLine: newMarkLine
                    }]
                });
            }
        };

        // Listen for datazoom event
        taskRunningChart.on('datazoom', updateMarkers);
        
        // Initial call to set markers
        updateMarkers();

        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart图
            taskRunningChart.resize();
        });
    }

    /**
     * 获取M365报表任务运行趋势数据
     * @param {*} params 查询参数
     * @param {*} echartId echart id（全部、成功、失败）
     */
    const getM365ReportTaskRunningData = (params, echartId) => {
        Metronic.blockUI({target: ".charts-wrapper .tab-content",animate: true});

        pAjaxRequest(params, '/api/v1/report/template/tendency', 'GET', (res) => {
            try {
                if (res.success) {
                    let xData = [], yData = [];
                    let maxValue = 0;
                    if (res.data.every(i => i.value === 0)) {
                        $(`#${echartId}`).addClass('display-none');
                        $(`#${echartId}`).siblings().removeClass('display-none');
                    } else {
                        res.data.forEach(item => {
                            xData.push(item.time);
                            let value = Number(item.value);
                            yData.push(value);
                            maxValue = Math.max(maxValue, value);
                        });

                        // 确定合适的单位
                        let unit = 'B';
                        if (maxValue >= 1024) {
                            unit = 'KB';
                            yData = yData.map(value => value / 1024);
                        }
                        if (maxValue >= 1024 * 1024) {
                            unit = 'MB';
                            yData = yData.map(value => value / 1024);
                        }
                        if (maxValue >= 1024 * 1024 * 1024) {
                            unit = 'GB';
                            yData = yData.map(value => value / 1024);
                        }
                        if (maxValue >= 1024 * 1024 * 1024 * 1024) {
                            unit = 'TB';
                            yData = yData.map(value => value / 1024);
                        }

                        let onlyData = '';
                        if (xData.length === 1) {
                            onlyData = xData[0].toString();
                        }

                        $(`#${echartId}`).removeClass('display-none');
                        $(`#${echartId}`).siblings().addClass('display-none');

                        initM365ReportEchart(xData, yData, onlyData, echartId, unit);
                    }
                } else {
                    UIToastr.showWarning(LANG.UI_GET_REPORT_TASK_RUNNING_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_REPORT_TASK_RUNNING_ERROR);
            } finally {
                Metronic.unblockUI('.charts-wrapper .tab-content');
            }
        });
    }

    // <------------------------- END ECHART DATA --------------------------------------->

    // <------------------------- BEGIN TABLE DATA -------------------------------------->

    /**
     * 初始化数据明细表格过滤器
     * @param {*} filterData 
     */
    const initM365TableFilter = (filterData) => {
        if ($('#m365_report_filter_btn').length === 0) {
            $('#m365_report_filter_wrapper').initFilter({
                filterSlotId: 'm365_report_filter_wrapper',
                filterBtnId: 'm365_report_filter_btn',
                filters: filterData
            });
        } else {
            $('#m365_report_filter_wrapper').resetFilter({
                filterBtnId: 'm365_report_filter_btn',
                filters: filterData
            });
        }
    }

    /**
     * 初始化数据明细时间选择器
     */
    const initM365TableDaterangePicker = () => {
        $('#m365_report_daterangepicker_wrapper').initDateRangePicker({
            slotId: 'm365_report_daterangepicker_wrapper', // 日期范围组件在父组件插槽位置的id
            dateRangePickerId: 'm365_datepicker', // 选择器button id
            startTime: '', // 开始时间
            endTime: '', // 结束时间
            maxDate: 'now', // 最大可用时间
            timePicker: true, // 是否显示时间,时分
            timePickerSeconds: true, // 是否显示秒
            timePicker24Hour: true, // 是否是24小时制
            alwaysShowCalendars: true, // 是否总是显示日期选择
        });
    }

     const errorFormatter = function (index, row) {
        switch (row.task_status) {
            case 0: //成功
                return '<span class="label label-success">' + row.task_status_des + '</span>';
            case 2: //中止
            case 45:
                return '<span class="label label-info">' + row.task_status_des + '</span>';
            case 3: //异常
            case 47:
                return '<span class="label label-warning">' + row.task_status_des + '</span>';
            case 1: //失败
                return '<span class="label label-danger">' + row.task_status_des + '</span>';
            default:
                return '<span class="label label-danger  ">' + row.task_status_des + '</span>';
        }
    }

    const getParams = function () {
        let queryParams = {
            search: $('#search').val()
        }
        
        // 合并过滤器搜索参数
        queryParams = Object.assign(queryParams, FILTER_PARAMS);

        return queryParams;
    }

    const initM365ReportDetailsTable = () => {
        if ($('#m365_report_table').children().length === 0) { // 表格为渲染
            let options = {
                vin_url: "/api/v1/report/template/app_data_details",
                vin_method: "GET",
                vin_toolbar: '.vin_toolbar',
                pagination: true, //分页
                resizable: true, //可变宽度
                showColumns: false,
                sortName: 'start_time',
                sortOrder: 'desc',
                vin_params: function () {
                    let params = {};
                    params = $.extend(params, getParams());
                    return params;
                },
                dateRangePickerId: 'filecopy_datepicker', // 日期选择器组件button id
                onPostBody: function() {
                    let tableData = $('#m365_report_table').bootstrapTable('getData');

                    if (tableData.length === 0) {
                        $('.data-detail-wrapper').addClass('nodata');
                    } else {
                        $('.data-detail-wrapper').removeClass('nodata');
                    }
                },
                columns: [
                    {
                        field: 'task_name',
                        title: LANG.UI_SEARCH_TASK_NAME,
                        sortable: true,
                        align: 'center',
                        visible: customField.task_name
                    },
                    {
                        field: 'organization_name',
                        title: LANG.UI_MICROSOFT365_ORGANIZATION_NAME,
                        sortable: false,
                        align: 'center',
                        visible: customField.organization_name
                    },
                    {
                        field: 'start_time',
                        title: LANG.UI_PUBLIC_START_TIME,
                        sortable: true,
                        align: 'center',
                        visible: customField.start_time
                    },
                    {
                        field: 'finish_time',
                        title: LANG.UI_PUBLIC_FINISH_TIME,
                        sortable: true,
                        align: 'center',
                        visible: customField.finish_time
                    },
                    {
                        field: 'total_object_size',
                        title: LANG.UI_GRAIN_JOB_TOTAL_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.total_object_size,
                        formatter: function (value) {
                            return `${unitConver(Number(value)).size}${unitConver(Number(value)).unit}`;
                        }
                    },
                    {
                        field: 'total_object_valid_size',
                        title: LANG.UI_PUBLIC_VM_VALID_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.total_object_valid_size,
                        formatter: function (value) {
                            return `${unitConver(Number(value)).size}${unitConver(Number(value)).unit}`;
                        }
                    },
                    {
                        field: 'total_object_transport_size',
                        title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.total_object_transport_size,
                        formatter: function (value) {
                            return `${unitConver(Number(value)).size}${unitConver(Number(value)).unit}`;
                        }
                    },
                    {
                        field: 'total_object_write_size',
                        title: LANG.UI_PUBLIC_REAL_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.total_object_write_size,
                        formatter: function (value) {
                            return `${unitConver(Number(value)).size}${unitConver(Number(value)).unit}`;
                        }
                    },
                    {
                        field: 'task_status',
                        title: LANG.UI_PUBLIC_TASK_STATUS,
                        sortable: false,
                        align: 'center',
                        visible: customField.task_status,
                        formatter: errorFormatter,
                    },
                    {
                        field: 'user',
                        title: LANG.UI_MICROSOFT365_USER,
                        sortable: false,
                        align: 'center',
                        visible: customField.user,
                    }
                ],
            }
            $('#m365_report_table').baseTableConfig().init(options);
        } else { // 表格已渲染直接刷新
            $('#m365_report_table').bootstrapTable('refresh');
        }
    }

    // <------------------------- END TABLE DATA -------------------------------------->

     /**
     * 报表概览导出
     */
    const reportOverviewExport = () => {
        $('#m365_export').attr('data-bs-indicator', 'on');

        // 使用按钮防抖1.5秒执行一次导出
        debounce(captureScrollAndGeneratePDF($('.report-detail__content').get(0), 1500, 'Microsoft365-report.pdf',  LANG.UI_VINCHIN_REPORT_WATER_NAME, function() {
            $('#m365_export').removeAttr('data-bs-indicator');
        }), 1500);
    }

    const initListener = () => {
        // 监听任务运行趋势tabs change
        $('#task_status_navs').on('click', function(event) {
            if (event.target.tagName === 'A') { // 检查点击的元素是否是一个 <a> 标签
                // 获取父元素 li.nav-item
                let clickedItem = event.target.parentElement;
                CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.statusType = clickedItem.getAttribute('data-type');
                CURRENT_TASK_RUNNING_ECHART_ID = STATUS_TYPE_TO_ECHART_ID_MAP[CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.statusType];

                getM365ReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS, CURRENT_TASK_RUNNING_ECHART_ID);
            }
        });

        // 监听时间类型选择change
        $('#task_running_time_select').on('change', () => {
            CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.timeInterval = $('#task_running_time_select').val();
            getM365ReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS, CURRENT_TASK_RUNNING_ECHART_ID);
        });

         // 搜索模版名称
        $('#report_search').off().on('click', () => {
            searchVal = $('#search').val()
            FILTER_PARAMS.search = searchVal;

            if (FILTER_PARAMS.search) {
                $('#m365_report_table').bootstrapTable('refresh', {query: { ...FILTER_PARAMS }})
            }
        });

        $('#search').on('focus', () => {
            $('#report_clear_search').removeClass('hide');
        });

        $('#search').on('keydown', (event) => {
            if (event.key === 'Enter' || event.keyCode === 13) {
                searchVal = $('#search').val()
                FILTER_PARAMS.search = searchVal;

                if (searchVal) {
                    $('#m365_report_table').bootstrapTable('refresh', {query: { ...FILTER_PARAMS }})
                }
            }
        });

        // 清空搜索
        $('#report_clear_search').on('click', () => {
            $('#search').val('');
            searchVal = '';
            FILTER_PARAMS.search = '';
            $('#report_clear_search').addClass('hide');
            $('#m365_report_table').bootstrapTable('refresh', {query: {...FILTER_PARAMS}});
        });

        // 监听导出
        $('#m365_export').on('click', reportOverviewExport);

        window.off('m365_report_filter_btn-updateFilterEvent');

        // 监听数据明细 - 过滤器组件派发的数据，以更新表格
        window.$on('m365_report_filter_btn-updateFilterEvent', (filterData) => {
            if (filterData.length > 0) {
                let taskStausList = getTableFilterParams('task_status', filterData);

                if (taskStausList.value.length > 0) {
                    FILTER_PARAMS.task_status = taskStausList.value.join(',');
                } else {
                    FILTER_PARAMS.task_status = '';
                }
            } else {
                FILTER_PARAMS.task_status = '';
            }

            FILTER_PARAMS.search = searchVal;
            FILTER_PARAMS.start_time = startTime;
            FILTER_PARAMS.end_time = endTime;

            $('#m365_report_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } });
        });

        window.$off('m365_datepicker-updateDateRangeEvent');

        // 监听文件复制表格 - 日期范围选择组件派发的数据，以更新表格
        window.$on('m365_datepicker-updateDateRangeEvent', (data) => {
            // 记录选择的开始时间和结束时间，用于过滤搜索的联动
            startTime = data.startTime;
            endTime = data.endTime;

            $('#m365_report_table').bootstrapTable('refresh', { query: { start_time: startTime, end_time: endTime } });
        });
    }

    /**
     * echart图自适应
     */
    const watchEchartSizeChange = () => {
        window.onresize = function() {
            switch (CURRENT_TASK_RUNNING_ECHART_ID) {
                case 'm365_report_all_chart':
                    // 有数据时，导航栏折叠/展开时，echart图要重绘
                    if (!$('#m365_report_all_chart').hasClass('display-none')) {
                        let chartWidth = $('#all_tab').width();
                        let chartHeight = $('#all_tab').height();
                        $('#m365_report_all_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                        taskRunningChart.resize();
                    }
                    break;
                case 'm365_report_success_chart':
                    // 有数据时，导航栏折叠/展开时，echart图要重绘
                    if (!$('#m365_report_success_chart').hasClass('display-none')) {
                        let chartWidth = $('#success_tab').width();
                        let chartHeight = $('#success_tab').height();
                        $('#m365_report_success_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                        taskRunningChart.resize();
                    }
                    break;
                case 'm365_report_failed_chart':
                    // 有数据时，导航栏折叠/展开时，echart图要重绘
                    if (!$('#m365_report_failed_chart').hasClass('display-none')) {
                        let chartWidth = $('#failed_tab').width();
                        let chartHeight = $('#failed_tab').height();
                        $('#m365_report_failed_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                        taskRunningChart.resize();
                    }
                    break;
                default:
                    break;
            }
        }

        $(window).on('echart-resize', function() {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等500毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                switch (CURRENT_TASK_RUNNING_ECHART_ID) {
                    case 'm365_report_all_chart':
                        // 有数据时，导航栏折叠/展开时，echart图要重绘
                        if (!$('#m365_report_all_chart').hasClass('display-none')) {
                            let chartWidth = $('#all_tab').width();
                            let chartHeight = $('#all_tab').height();
                            $('#m365_report_all_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                            taskRunningChart.resize();
                        }
                        break;
                    case 'm365_report_success_chart':
                        // 有数据时，导航栏折叠/展开时，echart图要重绘
                        if (!$('#m365_report_success_chart').hasClass('display-none')) {
                            let chartWidth = $('#success_tab').width();
                            let chartHeight = $('#success_tab').height();
                            $('#m365_report_success_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                            taskRunningChart.resize();
                        }
                        break;
                    case 'm365_report_failed_chart':
                        // 有数据时，导航栏折叠/展开时，echart图要重绘
                        if (!$('#m365_report_failed_chart').hasClass('display-none')) {
                            let chartWidth = $('#failed_tab').width();
                            let chartHeight = $('#failed_tab').height();
                            $('#m365_report_failed_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                            taskRunningChart.resize();
                        }
                        break;
                    default:
                        break;
                }
            }, 300);
        });
    }

    return {
        init: function() {
            initRouteParams();
            initListener();
            watchEchartSizeChange();
        }
    };
})();
$(function() {
    AppDetail.init();
});
