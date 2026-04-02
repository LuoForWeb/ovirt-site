var CdpDetail = (function() {
    let CDP_REPORT_TEMPLATE_UUID = '';
    let CURRENT_RUNNING_TENDENCY_QUERY_PARAMS = {
        type: CONF.REPORT_TYPE.VOL_CDP,
        timeInterval: CONF.RUNNING_TIME_TYPE.LAST_MONTH,
    }; // 当前任务运行趋势查询参数对象
    let customField = {};
    let filterParams = { search: '' };
    const CDP_REPORT_TABLE_FILTER_OPTIONS = [
        {
            label: LANG.UI_REPORT_PROTECT,
            field: 'protect_status',
            value: [
                {
                    id: 'cdp_protected',
                    value: CONF.BACKUP_STATUS_TYPE.PROTECTED,
                    text: LANG.UI_VM_REPORT_IN_BACKUP,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'cdp_unprotected',
                    text: LANG.UI_VM_REPORT_NOIN_BACKUP,
                    value:  CONF.BACKUP_STATUS_TYPE.UNPROTECTED,
                    tag: true,
                    type: 'secondary'
                }
            ]
        },
        {
            label: LANG.UI_SEARCH_BACKUP_STATUS,
            field: 'task_status',
            value: [
                {
                    id: 'task_status_success',
                    value: CONF.TASK_STATUS.SUCCESSED,
                    text: LANG.UI_PUBLIC_SUCCESS,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'task_status_waiting',
                    value: CONF.TASK_STATUS.WAITTING,
                    text: LANG.UI_PUBLIC_WAIT,
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'task_status_error',
                    value: CONF.TASK_STATUS.ERROR,
                    text: LANG.UI_PLATFORM_DES_ERROR,
                    tag: true,
                    type: 'danger'
                },
                {
                    id: 'task_status_stopped',
                    value: CONF.TASK_STATUS.STOPPED,
                    text: LANG.UI_JOB_STOP,
                    tag: true,
                    type: 'secondary'
                },
                {
                    id: 'task_status_abnormal',
                    value: CONF.TASK_STATUS.ABNORMAL,
                    text: LANG.UI_NODE_ABNORMAL,
                    tag: true,
                    type: 'warning'
                },
                {
                    id: 'task_status_running',
                    value: CONF.TASK_STATUS.RUNNING,
                    text: LANG.UI_PUBLIC_RUNNING,
                    tag: true,
                    type: 'success'
                }
            ]
        }
    ]; // 连续数据保护过滤选项参数
    let taskRunningChart = null;

    // <------------------------- BEGIN OVERFIEW DATA ----------------------------------->

    /**
     * 初始化实时容灾报表概览数据
     */
    const initOverviewData = () => {
        pAjaxRequest({}, '/api/v1/report/template/overview/3', 'get', function (res) {
            try {
                if (res.success) {
                    let overviewList = res.data.overviewList;

                    $('#client_total').text(overviewList.host_number);
                    $('#client_protected').text(overviewList.protected_total);
                    $('#client_unprotected').text(overviewList.unprotected_total);
                    $('#client_task_total').text(overviewList.task_number);
                    $('#client_backup_set').text(overviewList.backup_set_number);
                    $('#client_backup_data').text(!overviewList.backup_data ? 0 : unitConver(Number(overviewList.backup_data)).size);
                    $('#client_backup_data_unit').text(!overviewList.backup_data ? 'B' : unitConver(Number(overviewList.backup_data)).unit);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_CDP_REPORT_OVERVIEW_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_CDP_REPORT_OVERVIEW_DATA_ERROR);
            }
        });
    }

    /**
     * 获取实时容灾报表详情数据
     */
    const getReportDetail = () => {
        pAjaxRequest({}, '/api/v1/report/template/detail/' + CDP_REPORT_TEMPLATE_UUID, 'get', (res) => {
            try {
                Metronic.blockUI({target: ".report-detail__content",animate: true});

                if (res.success) {
                    let { custom_field, overview, running_tendency } = { ...res.data };

                    // 渲染概览数据
                    if (!overview.host_number && !overview.protected_number && !overview.unprotected_number && !overview.task_number && !overview.backup_set_number && !overview.backup_data) {
                        $('.overview-header').addClass('display-none');
                        $('.report-detail-boxes').addClass('display-none');
                    } else { // 只要配置了一项就显示数据概览
                        $('.overview-header').removeClass('display-none');
                        $('.report-detail-boxes').removeClass('display-none');

                        overview.host_number ? $('.client-total-box').removeClass('display-none') : $('.client-total-box').hide();
                        overview.protected_number ? $('.client-protected-box').removeClass('display-none') : $('.client-protected-box').hide();
                        overview.unprotected_number ?  $('.client-unprotected-box').removeClass('display-none') : $('.client-unprotected-box').hide();
                        overview.task_number ? $('.client-task-total-box').removeClass('display-none') : $('.client-task-total-box').hide();
                        overview.backup_set_number ? $('.client-backup-set-box').removeClass('display-none') : $('.client-backup-set-box').hide();
                        overview.backup_data ? $('.client-backup-data-box').removeClass('display-none') : $('.client-backup-data-box').hide();

                        initOverviewData();
                    }

                    // 渲染任务运行趋势echart图
                    if (running_tendency.history) {
                        $('.echart-header').removeClass('display-none');
                        $('.echart-wrapper').removeClass('display-none');

                        getCdpReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
                    } else {
                        $('.echart-header').addClass('display-none');
                        $('.echart-wrapper').addClass('display-none');
                    }

                    customField = { ...custom_field };

                    // 获取过滤器选项数组
                    let fields = [];
                    if (customField.protect) fields.push('protect_status');
                    if (customField.backup_status) fields.push('task_status');

                    if (fields.length > 0) {
                        let filterData = CDP_REPORT_TABLE_FILTER_OPTIONS.filter(option => {
                            return fields.includes(option.field);
                        });
        
                        // 渲染数据明细表格过滤器
                        initCdpTableFilter(filterData);
                    }

                    // 渲染数据明细表格
                    initCdpReportDetailsTable();

                } else {
                    UIToastr.showWarning(LANG.UI_GET_CDP_REPORT_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_CDP_REPORT_DATA_ERROR);
            } finally {
                Metronic.unblockUI('.report-detail__content');
            }
        });
    }

    // <------------------------- END OVERFIEW DATA ------------------------------------->


    // <------------------------- BEGIN ECHART DATA ------------------------------------->

    /**
     * 初始化实时容灾报表任务运行趋势echart图
     * @param {*} xData 
     * @param {*} yData 
     * @param {*} onlyData 
     * @param {*} echartId 
     */
    const initCdpReportEchart = (xData, yData, onlyData, echartId, unit) => {
        if ($(`#${echartId}`).children().length > 0) {
            // 销毁上一个echart
            echarts.dispose(document.getElementById(echartId));
        }

        let chartWidth = $('.charts-wrapper .tab-content').width();
        let chartHeight = $('.charts-wrapper .tab-content').height();
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
     * 获取实时容灾任务运行数据
     * @param {*} params 
     */
    const getCdpReportTaskRunningData = (params) => {
        Metronic.blockUI({target: ".tab-content__echart",animate: true});

        pAjaxRequest(params, '/api/v1/report/template/tendency', 'get', (res) => {
            try {
                if (res.success) {
                    let xData = [], yData = [];
                    let maxValue = 0;
                    if (res.data.every(i => i.value === 0)) {
                        $(`#cdp_task_running_chart`).addClass('display-none');
                        $(`#cdp_task_running_chart`).siblings().removeClass('display-none');
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

                        $(`#cdp_task_running_chart`).removeClass('display-none');
                        $(`#cdp_task_running_chart`).siblings().addClass('display-none');

                        initCdpReportEchart(xData, yData, onlyData, 'cdp_task_running_chart', unit);
                    }
                } else {
                    UIToastr.showWarning(LANG.UI_GET_REPORT_TASK_RUNNING_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_REPORT_TASK_RUNNING_ERROR);
            } finally {
                Metronic.unblockUI('.tab-content__echart');
            }
        });
    }

    const watchEchartSizeChange = () => {
        window.onresize = function() {
            let chartWidth = $('.charts-wrapper .tab-content').width();
            let chartHeight = $('.charts-wrapper .tab-content').height();

            $('#cdp_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            taskRunningChart.resize();
        }

        //  监听左侧菜单导航伸缩/展开触发的echart-resize事件
        $(window).on('echart-resize', function () {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                let chartWidth = $('.charts-wrapper .tab-content').width();
                let chartHeight = $('.charts-wrapper .tab-content').height();

                $('#cdp_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                taskRunningChart.resize();
            }, 300);
        });
    }

    // <------------------------- END ECHART DATA --------------------------------------->


    // <------------------------- BEGIN TABLE DATA -------------------------------------->

    const statusFormatter = function (index,row) {
        switch (row.backup_status) {
            case 1:
            case 10://等待运行 蓝色
                return '<span class="label label-sm label-info status-icon">' + LANG.UI_PUBLIC_WAIT + '</span>';
            case 17:
            case 13:
                return '<span class="label label-sm label-success status-icon">' + LANG.UI_DATACENTER_SUCCESS + '</span>';
            case 2:
            case 9:
            case 12:
            case 14:
            case 15:
                return '<span class="label label-sm label-success status-icon">' + LANG.UI_VISUAL_RUNNING + '</span>';
            case 3:
            case 4: //灰色
                return '<span class="label label-sm label-default">' + LANG.UI_JOB_STOP + '</span>';
            case 5: //灰色
            case 11: //异常
            case 16: //灰色
                return '<span class="label label-sm label-default">' + LANG.UI_VISUAL_STOPPING + '</span>';
            case 0: //失败
            case 7: //失败
            case 6: //失败
            case 8: //失败
                return '<span class="label label-sm label-danger status-icon">' + LANG.UI_PUBLIC_ERROR + '</span>';
        }
    }

    /**
     * 初始化实时容灾报表数据明细表格过滤器
     * @param {*} filterData 
     */
    const initCdpTableFilter = (filterData) => {
        if ($('#cdp_report_filter_btn').length === 0) {
            $('#cdp_report_filter_wrapper').initFilter({
                filterSlotId: 'cdp_report_filter_wrapper',
                filterBtnId: 'cdp_report_filter_btn',
                filters: filterData
            });
        } else {
            $('#cdp_report_filter_wrapper').resetFilter({
                filterBtnId: 'cdp_report_filter_btn',
                filters: filterData
            });
        }
    }

    /**
     * 初始化实时容灾保护数据明细
     */
    const initCdpReportDetailsTable = () => {
        if ($('#cdp_report_table').children().length === 0) { // 表格为渲染
            let options = {
                vin_url: "/api/v1/report/template/cdp_data_details",
                vin_method: "GET",
                vin_toolbar: '.vin_toolbar',
                pagination: true, //分页
                resizable: true, //可变宽度
                showColumns: false,
                onPostBody: function() {
                    let tableData = $('#cdp_report_table').bootstrapTable('getData');

                    if (tableData.length === 0) {
                        $('.data-detail-wrapper').addClass('nodata');
                    } else {
                        $('.data-detail-wrapper').removeClass('nodata');
                    }
                },
                columns: [
                    {
                        field: 'host_name',
                        title: LANG.UI_COPY_DETAIL_HOST_NAME,
                        sortable: false,
                        align: 'center',
                        visible: customField.host_name
                    },
                    {
                        field: 'ip',
                        title: LANG.UI_OS_DETAILS_IP,
                        sortable: false,
                        align: 'center',
                        visible: customField.ip
    
                    },
                    {
                        field: 'os_type',
                        title: LANG.UI_PLATFORM_DES_OS,
                        sortable: false,
                        align: 'center',
                        visible: customField.os_type,
                    },
                    {
                        field: 'user',
                        title: LANG.UI_CLIENT_OWNER,
                        sortable: false,
                        align: 'center',
                        visible: customField.user,
                    },
                    {
                        field: 'add_time',
                        title: LANG.UI_PUBLIC_ADD_TIME,
                        sortable: true,
                        align: 'center',
                        visible: customField.add_time
    
                    },
                    {
                        field: 'protect',
                        title: LANG.UI_REPORT_PROTECT,
                        sortable: true,
                        align: 'center',
                        visible: customField.protect,
                        formatter: function (value) {
                            switch (value) {
                                case 0:
                                    return `<span class="label label-default">${LANG.UI_VM_REPORT_NOIN_BACKUP}</span>`;
                                case 1:
                                    return `<span class="label label-success">${LANG.UI_VM_REPORT_IN_BACKUP}</span>`;
                                default:
                                    return;
                            }
                        }
                    },
                    {
                        field: 'protect_app',
                        title: LANG.UI_REPORT_PROTECT_APP,
                        sortable: true,
                        align: 'center',
                        visible: customField.protect_app,
                        formatter: function (value) {
                            let strategyType;
                            switch (value) {
                                case 0:
                                    strategyType = LANG.UI_REPORT_NOHAVE;
                                    break;
                                case 1:
                                    strategyType = LANG.UI_REPORT_HAVE;
                                    break;
                                default:
                                    break;
                            }
                            return '<span title="' + strategyType + '">' + strategyType + '</span>';
                        }
                    },
                    {
                        field: 'task',
                        title: LANG.UI_PLATFORM_ASSOCIA_TASK,
                        sortable: false,
                        align: 'center',
                        visible: customField.task,
                    },
    
                    {
                        field: 'last_backup_time',
                        title: LANG.UI_REPORT_LAST_TIME,
                        sortable: true,
                        align: 'center',
                        visible: customField.last_backup_time,
                    },
                    {
                        field: 'backup_set',
                        title: LANG.UI_REPORT_BACKUP_SET_COUNT,
                        sortable: false,
                        align: 'center',
                        visible: customField.backup_set,
                    },
                    {
                        field: 'backup_status',
                        title: LANG.UI_SEARCH_BACKUP_STATUS,
                        sortable: true,
                        align: 'center',
                        formatter: statusFormatter,
                        visible: customField. backup_status,
                    },
                    {
                        field: 'backup_data',
                        title: LANG.UI_HOMEPAGEPRO_BACKUP_DATA,
                        sortable: false,
                        align: 'center',
                        visible: customField.backup_data,
                    },
                    {
                        field: 'storage_nickname',
                        title: LANG.UI_REPORT_STORAGE_NAME,
                        sortable: false,
                        align: 'center',
                        visible: customField.storage_nickname,
                    },
                    {
                        field: 'auto_takeover',
                        title: LANG.UI_REPORT_TACKOVER,
                        sortable: false,
                        align: 'center',
                        visible: customField.auto_takeover,
                        formatter: function (value) {
                            let strategyType;
                            switch (value) {
                                case 1:
                                    strategyType = LANG.UI_REPORT_YES;
                                    break;
                                case 2:
                                    strategyType = LANG.UI_REPORT_NO;
                                    break;
                                default:
                                    break;
                            }
                            return '<span title="' + strategyType + '">' + strategyType + '</span>';
                        }
                    }
                ]
            }
            $('#cdp_report_table').baseTableConfig().init(options);
        } else { // 表格已渲染直接刷新
            $('#cdp_report_table').bootstrapTable('refresh');
        }
    }
    
    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';
        CDP_REPORT_TEMPLATE_UUID = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存报表uuid

        if (CDP_REPORT_TEMPLATE_UUID) {
            getReportDetail();
        }
    }

    /**
     * 报表导出
     */
    const reportOverviewExport = () => {
        $('#cdp_export').attr('data-bs-indicator', 'on');

        // 使用按钮防抖1.5秒执行一次导出
        debounce(captureScrollAndGeneratePDF($('.report-detail__content').get(0), 1500, 'cdp-report.pdf',  LANG.UI_VINCHIN_REPORT_WATER_NAME, function() {
            $('#cdp_export').removeAttr('data-bs-indicator');
        }), 1500);
    }

    // <------------------------- END TABLE DATA ---------------------------------------->


    const initListener = () => {
        // 监听时间类型选择change
        $('#task_running_time_select').on('change', () => {
            CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.timeInterval = $('#task_running_time_select').val();
            getCdpReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
        });

        // 搜索模版名称
        $('#report_search').off().on('click', () => {
            filterParams.search = $('#search').val();

            if (filterParams.search) {
                $('#cdp_report_table').bootstrapTable('refresh', {query: {...filterParams}})
            }
        });

        $('#search').on('focus', () => {
            $('#report_clear_search').removeClass('hide');
        });

        $('#search').on('keydown', (event) => {
            if (event.key === 'Enter' || event.keyCode === 13) {
                // 在这里添加你想要执行的代码
                filterParams.search = $('#search').val();
                if (filterParams.search) {
                    $('#cdp_report_table').bootstrapTable('refresh', {query: {...filterParams}})
                }
            }
        });

        // 清空搜索
        $('#report_clear_search').on('click', () => {
            $('#search').val('');
            filterParams.search = '';
            $('#report_clear_search').addClass('hide');
            $('#cdp_report_table').bootstrapTable('refresh', {query: {...filterParams}})
        });

        // 监听导出
        $('#cdp_export').on('click', reportOverviewExport);

        window.$off('cdp_report_filter_btn-updateFilterEvent');

        // 监听数据明细 - 过滤器组件派发的数据，以更新表格
        window.$on('cdp_report_filter_btn-updateFilterEvent', (filterData) => {
            if (filterData.length > 0) {
                
                if (customField.protect) {
                    let protectStatusList = getTableFilterParams('protect_status', filterData);

                    if (protectStatusList.value.length > 0) {
                        filterParams.protect_status = protectStatusList.value;
                    } else {
                        filterParams.protect_status = '';
                    }
                }
                
                if (customField.backup_status) {
                    let backupStatusList = getTableFilterParams('task_status', filterData);

                    if (backupStatusList.value.length > 0) {
                        filterParams.task_status = backupStatusList.value;
                    } else {
                        filterParams.task_status = '';
                    }
                }
            } else {
                filterParams.protect_status = '';
                filterParams.task_status = '';
            }

            $('#cdp_report_table').bootstrapTable('refresh', { query: { ...filterParams } });
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
    CdpDetail.init();
});
