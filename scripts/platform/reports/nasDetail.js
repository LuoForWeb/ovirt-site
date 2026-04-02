var NasDetail = (function() {
    const TASK_RUNNING_STATUS_TYPE = {
        ALL: 1,
        SUCCESSED: 2,
        FAILED: 3
    }; // 任务运行状态类型
    let NAS_REPORT_TEMPLATE_UUID = '';
    let CURRENT_RUNNING_TENDENCY_QUERY_PARAMS = {
        type: CONF.REPORT_TYPE.NAS,
        timeInterval: CONF.RUNNING_TIME_TYPE.LAST_MONTH,
        statusType: TASK_RUNNING_STATUS_TYPE.ALL
    }; // 当前任务运行趋势查询参数对象
    let customField = {};
    let filterParams = { search: '' };
    const NAS_REPORT_TABLE_FILTER_OPTIONS = [
        {
            label: LANG.UI_SYSTEM_MONITOR_DEVICE_TYPE,
            field: 'device_type',
            value: [
                {
                    id: 'device_type_nfs',
                    value: CONF.BD_STORAGE_TYPE.NFS,
                    text: 'NFS',
                },
                {
                    id: 'device_type_cifs',
                    value: CONF.BD_STORAGE_TYPE.CIFS,
                    text: 'CIFS',
                }
            ]
        },
        {
            label: LANG.UI_REPORT_DEVICE_STATUS,
            field: 'status',
            value: [
                {
                    id: 'nas_status_online',
                    value: CONF.NAS_STATUS_TYPE.ONLINE,
                    text: LANG.UI_CLOUD_PLATFORM_ONLINE,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'nas_status_abnormal',
                    value: CONF.NAS_STATUS_TYPE.ABNORMAL,
                    text: LANG.UI_NODE_ABNORMAL,
                    tag: true,
                    type: 'warning'
                },
                {
                    id: 'nas_status_offline',
                    value: CONF.NAS_STATUS_TYPE.OFFLINE,
                    text: LANG.UI_CLOUD_PLATFORM_OFFLINE,
                    tag: true,
                    type: 'secondary'
                },
            ]
        },
        {
            label: LANG.UI_REPORT_PROTECT,
            field: 'protect_status',
            value: [
                {
                    id: 'protect_status_protected',
                    value: CONF.BACKUP_STATUS_TYPE.PROTECTED,
                    text: LANG.UI_VM_REPORT_IN_BACKUP,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'protect_status_unprotected',
                    text: LANG.UI_VM_REPORT_NOIN_BACKUP,
                    value:  CONF.BACKUP_STATUS_TYPE.UNPROTECTED,
                    tag: true,
                    type: 'secondary'
                }
            ]
        },
    ];
    let taskRunningChart = null;

    // <------------------------- BEGIN OVERFIEW DATA ----------------------------------->

    /**
     * 初始化NAS报表概览数据
     */
    const initOverviewData = () => {
        pAjaxRequest({}, '/api/v1/report/template/overview/4', 'get', function (res) {
            try {
                if (res.success) {
                    let overviewList = res.data.overviewList;

                    $('#nas_total').text(overviewList.device_number);
                    $('#nas_online').text(overviewList.online_number);
                    $('#nas_offline').text(overviewList.offline_number);
                    $('#nas_protected').text(overviewList.protected_number);
                    $('#nas_unprotected').text(overviewList.unprotected_number);
                    $('#nas_backup_data').text(!overviewList.backup_data ? 0 : unitConver(Number(overviewList.backup_data)).size);
                    $('#nas_backup_data_unit').text(!overviewList.backup_data ? 'B' : unitConver(Number(overviewList.backup_data)).unit);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_NAS_REPORT_OVERVIEW_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_NAS_REPORT_OVERVIEW_DATA_ERROR);
            }
        });
    }

    /**
     * 获取NAS报表详情数据
     */
    const getReportDetail = () => {
        pAjaxRequest({}, '/api/v1/report/template/detail/' + NAS_REPORT_TEMPLATE_UUID, 'get', (res) => {
            try {
                Metronic.blockUI({target: ".report-detail__content",animate: true});

                if (res.success) {
                    let { custom_field, overview, running_tendency } = { ...res.data };

                    // 渲染概览数据
                    if (!overview.device_number && !overview.online_number && !overview.offline_number && !overview.protected_number && !overview.unprotected_number && !overview.backup_data) {
                        $('.overview-header').addClass('display-none');
                        $('.report-detail-boxes').addClass('display-none');
                    } else { // 只要配置了一项就显示数据概览
                        $('.overview-header').removeClass('display-none');
                        $('.report-detail-boxes').removeClass('display-none');

                        overview.device_number ? $('.nas-total-box').removeClass('display-none') : $('.nas-total-box').hide();
                        overview.online_number ? $('.nas-online-box').removeClass('display-none') : $('.nas-online-box').hide();
                        overview.offline_number ?  $('.nas-offline-box').removeClass('display-none') : $('.nas-offline-box').hide();
                        overview.protected_number ? $('.nas-protected-box').removeClass('display-none') : $('.nas-protected-box').hide();
                        overview.unprotected_number ? $('.nas-unprotected-box').removeClass('display-none') : $('.nas-unprotected-box').hide();
                        overview.backup_data ? $('.nas-backup-data-box').removeClass('display-none') : $('.nas-backup-data-box').hide();

                        initOverviewData();
                    }

                    // 渲染任务运行趋势echart图
                    if (running_tendency.history) {
                        $('.echart-header').removeClass('display-none');
                        $('.echart-wrapper').removeClass('display-none');

                        getNasReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
                    } else {
                        $('.echart-header').addClass('display-none');
                        $('.echart-wrapper').addClass('display-none');
                    }

                    customField = { ...custom_field };

                    // 获取过滤器选项数组
                    let fields = [];
                    if (customField.device_type) fields.push('device_type');
                    if (customField.status) fields.push('status');
                    if (customField.protect_status) fields.push('protect_status');

                    if (fields.length > 0) {
                        let filterData = NAS_REPORT_TABLE_FILTER_OPTIONS.filter(option => {
                            return fields.includes(option.field);
                        });
        
                        // 渲染数据明细表格过滤器
                        initNasTableFilter(filterData);
                    }

                    // 渲染数据明细表格
                    initNasReportDetailsTable();

                } else {
                    UIToastr.showWarning(LANG.UI_GET_NAS_REPORT_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_NAS_REPORT_DATA_ERROR);
            } finally {
                Metronic.unblockUI('.report-detail__content');
            }
        });
    }

    // <------------------------- END OVERFIEW DATA ------------------------------------->


    // <------------------------- BEGIN ECHART DATA ------------------------------------->

    /**
     * 初始化NAS报表任务运行趋势echart图
     * @param {*} xData 
     * @param {*} yData 
     * @param {*} onlyData 
     * @param {*} echartId 
     */
    const initNasReportEchart = (xData, yData, onlyData, echartId, unit) => {
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
     * 获取nas任务运行数据
     * @param {*} params 
     */
    const getNasReportTaskRunningData = (params) => {
        Metronic.blockUI({target: ".tab-content__echart",animate: true});

        pAjaxRequest(params, '/api/v1/report/template/tendency', 'get', (res) => {
            try {
                if (res.success) {
                    let xData = [], yData = [];
                    let maxValue = 0;
                    if (res.data.every(i => i.value === 0)) {
                        $(`#nas_task_running_chart`).addClass('display-none');
                        $(`#nas_task_running_chart`).siblings().removeClass('display-none');
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

                        $(`#nas_task_running_chart`).removeClass('display-none');
                        $(`#nas_task_running_chart`).siblings().addClass('display-none');

                        initNasReportEchart(xData, yData, onlyData, 'nas_task_running_chart', unit);
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

            $('#nas_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            taskRunningChart.resize();
        }

        //  监听左侧菜单导航伸缩/展开触发的echart-resize事件
        $(window).on('echart-resize', function () {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                let chartWidth = $('.charts-wrapper .tab-content').width();
                let chartHeight = $('.charts-wrapper .tab-content').height();

                $('#nas_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                taskRunningChart.resize();
            }, 300);
        });
    }

    // <------------------------- END ECHART DATA --------------------------------------->

    // <------------------------- BEGIN TABLE DATA -------------------------------------->

    /**
     * 初始化NAS报表数据明细表格过滤器
     * @param {*} filterData 
     */
    const initNasTableFilter = (filterData) => {
        if ($('#nas_report_filter_btn').length === 0) {
            $('#nas_report_filter_wrapper').initFilter({
                filterSlotId: 'nas_report_filter_wrapper',
                filterBtnId: 'nas_report_filter_btn',
                filters: filterData
            });
        } else {
            $('#nas_report_filter_wrapper').resetFilter({
                filterBtnId: 'nas_report_filter_btn',
                filters: filterData
            });
        }
    }

    /**
     * 初始化nas数据明细
     */
    const initNasReportDetailsTable = () => {
        if ($('#nas_report_table').children().length === 0) { // 表格为渲染
            let options = {
                vin_url: "/api/v1/report/template/nas_data_details",
                vin_method: "GET",
                vin_toolbar: '.vin_toolbar',
                pagination: true, //分页
                resizable: true, //可变宽度
                showColumns: false,
                onPostBody: function() {
                    let tableData = $('#nas_report_table').bootstrapTable('getData');

                    if (tableData.length === 0) {
                        $('.data-detail-wrapper').addClass('nodata');
                    } else {
                        $('.data-detail-wrapper').removeClass('nodata');
                    }
                },
                columns: [
                    {
                        field: 'ip',
                        title: LANG.UI_NODE_NETWORK_TABLE_IP,
                        sortable: true,
                        align: 'center',
                        visible: customField.ip
    
                    },
                    {
                        field: 'device_name',
                        title: LANG.UI_REPORT_DEVICE_NAME,
                        sortable: false,
                        align: 'center',
                        visible: customField.device_name,
                        formatter: function (value) {
                            let strategyType;
                            if (value) {
                                strategyType =  value;
                            } else {
                                strategyType =  '--';
                            }
                            return '<span title="' + strategyType + '">' + strategyType + '</span>';
                        }
                    },
                    {
                        field: 'shared_path',
                        title: LANG.UI_REPORT_SHARE_PATH,
                        sortable: false,
                        align: 'center',
                        visible: customField.shared_path,
                    },
                    {
                        field: 'device_type',
                        title: LANG.UI_SYSTEM_MONITOR_DEVICE_TYPE,
                        sortable: true,
                        align: 'center',
                        visible: customField.device_type,
                        formatter: function (value) {
                            let strategyType;
                            if (value === 6) {
                                strategyType =  'NFS';
                            } else {
                                strategyType =  'CIFS';
                            }
                            return '<span title="' + strategyType + '">' + strategyType + '</span>';
                        }
    
    
                    },
                    {
                        field: 'add_time',
                        title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
                        sortable: true,
                        align: 'center',
                        visible: customField.add_time
    
                    },
                    {
                        field: 'status',
                        title: LANG.UI_REPORT_DEVICE_STATUS,
                        sortable: true,
                        align: 'center',
                        visible: customField.status,
                        formatter: function (value) {
                            if (value === 1) {
                                return `<span class="label label-success">${LANG.UI_REPORT_ONLINE}</span>`;
                            } else if(value === 2) {
                                return `<span class="label label-warning">${LANG.UI_NODE_ABNORMAL}</span>`;
                            } else {
                                return `<span class="label label-default">${LANG.UI_VISUAL_OFF_LINE}</span>`;
                            }
                        }
                    },
                    {
                        field: 'protect_status',
                        title: LANG.UI_CLIENT_PROTECT,
                        sortable: true,
                        align: 'center',
                        visible: customField.protect_status,
                        formatter: function (value) {
                            switch (value) {
                                case 0:
                                    return `<span class="label label-default">${LANG.UI_VM_REPORT_NOIN_BACKUP}</span>`;
                                case 1:
                                    return `<span class="label label-success">${LANG.UI_VM_REPORT_IN_BACKUP}</span>`;
                                default:
                                    break;
                            }
                        }
                    },
                    {
                        field: 'task',
                        title: LANG.UI_REPORT_SET_TASK,
                        sortable: false,
                        align: 'center',
                        visible: customField.task,
                    },
                    {
                        field: 'backup_data',
                        title: LANG.UI_HOMEPAGEPRO_BACKUP_DATA,
                        sortable: true,
                        align: 'center',
                        visible: customField.backup_data,
                    }
                ],
            }
            $('#nas_report_table').baseTableConfig().init(options);
        } else { // 表格已渲染直接刷新
            $('#nas_report_table').bootstrapTable('refresh');
        }
    }

    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';
        NAS_REPORT_TEMPLATE_UUID = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存报表uuid

        if (NAS_REPORT_TEMPLATE_UUID) {
            getReportDetail();
        }
    }

    /**
     * 报表导出
     */
    const reportOverviewExport = () => {
        $('#nas_export').attr('data-bs-indicator', 'on');

        // 使用按钮防抖1.5秒执行一次导出
        debounce(captureScrollAndGeneratePDF($('.report-detail__content').get(0), 1500, 'nas-report.pdf',  LANG.UI_VINCHIN_REPORT_WATER_NAME, function() {
            $('#nas_export').removeAttr('data-bs-indicator');
        }), 1500);
    }

    const initListener = () => {
        // 监听时间类型选择change
        $('#task_running_time_select').on('change', () => {
            CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.timeInterval = $('#task_running_time_select').val();
            getNasReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
        });

        // 搜索模版名称
        $('#report_search').off().on('click', () => {
            filterParams.search = $('#search').val();

            if (filterParams.search) {
                $('#nas_report_table').bootstrapTable('refresh', {query: {...filterParams}})
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
                    $('#nas_report_table').bootstrapTable('refresh', {query: {...filterParams}})
                }
            }
        });

        // 清空搜索
        $('#report_clear_search').on('click', () => {
            $('#search').val('');
            filterParams.search = '';
            $('#report_clear_search').addClass('hide');
            $('#nas_report_table').bootstrapTable('refresh', {query: {...filterParams}})
        });

        // 监听导出
        $('#nas_export').on('click', reportOverviewExport);

        window.$off('nas_report_filter_btn-updateFilterEvent');

        // 监听数据明细 - 过滤器组件派发的数据，以更新表格
        window.$on('nas_report_filter_btn-updateFilterEvent', (filterData) => {
            if (filterData.length > 0) {
                
                let deviceTypeList = getTableFilterParams('device_type', filterData);
                let nasStatusList = getTableFilterParams('status', filterData);
                let protectStatusList = getTableFilterParams('protect_status', filterData);

                if (deviceTypeList.value.length > 0) {
                    filterParams.device_type = deviceTypeList.value;
                } else {
                    filterParams.device_type = '';
                }

                if (nasStatusList.value.length > 0) {
                    filterParams.status = nasStatusList.value;
                } else {
                    filterParams.status = '';
                }


                if (protectStatusList.value.length > 0) {
                    filterParams.protect_status = protectStatusList.value;
                } else {
                    filterParams.protect_status = '';
                }
            } else {
                filterParams.device_type = '';
                filterParams.protect_status = '';
                filterParams.status = '';
            }

            $('#nas_report_table').bootstrapTable('refresh', { query: { ...filterParams } });
        });
    }

    // <------------------------- END TABLE DATA ---------------------------------------->
    
    return {
        init: function() {
            initRouteParams();
            initListener();
            watchEchartSizeChange();
        }
    };
})();
$(function() {
    NasDetail.init();
});
