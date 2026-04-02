var HadoopDetail = (function() {
    let HADOOP_REPORT_TEMPLATE_UUID = '';
    let CURRENT_RUNNING_TENDENCY_QUERY_PARAMS = {
        type: CONF.REPORT_TYPE.HADOOP,
        timeInterval: CONF.RUNNING_TIME_TYPE.LAST_MONTH,
    }; // 当前任务运行趋势查询参数对象
    let customField = {};
    let taskRunningChart = null;

    // <------------------------- BEGIN OVERFIEW DATA ----------------------------------->

    const initOverviewData = () => {
        pAjaxRequest({}, '/api/v1/report/template/overview/11', 'get', function (res) {
            try {
                if (res.success) {
                    let overviewList = res.data.overviewList;

                    $('#hadoop_total_num').text(overviewList.hadoop_total);
                    $('#hadoop_protected_num').text(overviewList.hadoop_auth);
                    $('#hadoop_online_num').text(overviewList.hadoop_online);
                    $('#hadoop_offline_num').text(overviewList.hadoop_offline);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_HADOOP_REPORT_OVERVIEW_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_HADOOP_REPORT_OVERVIEW_DATA_ERROR);
            }
        });
    }

    const getReportDetail = () => {
        pAjaxRequest({}, '/api/v1/report/template/detail/' + HADOOP_REPORT_TEMPLATE_UUID, 'get', (res) => {
            try {
                Metronic.blockUI({target: ".report-detail__content",animate: true});

                if (res.success) {
                    let { custom_field, overview, running_tendency } = { ...res.data };

                    // 渲染概览数据
                    if (!overview.hadoop_total && !overview.hadoop_auth && !overview.hadoop_online && !overview.hadoop_offline) {
                        $('.overview-header').addClass('display-none');
                        $('.report-detail-boxes').addClass('display-none');
                    } else { // 只要配置了一项就显示数据概览
                        $('.overview-header').removeClass('display-none');
                        $('.report-detail-boxes').removeClass('display-none');

                        overview.hadoop_total ? $('.hadoop-total-box').removeClass('display-none') : $('.hadoop-total-box').hide();
                        overview.hadoop_auth ? $('.hadoop-protected-box').removeClass('display-none') : $('.hadoop-protected-box').hide();
                        overview.hadoop_online ?  $('.hadoop-online-box').removeClass('display-none') : $('.hadoop-online-box').hide();
                        overview.hadoop_offline ?  $('.hadoop-offline-box').removeClass('display-none') : $('.hadoop-offline-box').hide();

                        initOverviewData();
                    }

                    // 渲染任务运行趋势echart图
                    if (running_tendency.history) {
                        $('.echart-header').removeClass('display-none');
                        $('.echart-wrapper').removeClass('display-none');

                        getHadoopReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
                    } else {
                        $('.echart-header').addClass('display-none');
                        $('.echart-wrapper').addClass('display-none');
                    }

                    customField = { ...custom_field };

                    // 渲染数据明细表格
                    initHadoopReportDetailsTable();

                } else {
                    UIToastr.showWarning(LANG.UI_GET_HADOOP_REPORT_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_HADOOP_REPORT_DATA_ERROR);
            } finally {
                Metronic.unblockUI('.report-detail__content');
            }
        });
    }

    // <------------------------- END OVERFIEW DATA ------------------------------------->

    // <------------------------- BEGIN ECHART DATA ------------------------------------->

    const initHadoopReportEchart = (xData, yData, onlyData, echartId, unit) => {
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

    const getHadoopReportTaskRunningData = (params) => {
        Metronic.blockUI({target: ".tab-content__echart",animate: true});

        pAjaxRequest(params, '/api/v1/report/template/tendency', 'get', (res) => {
            try {
                if (res.success) {
                    let xData = [], yData = [];
                    let maxValue = 0;
                    let resData = res.data
                    if (res.data.every(i => i.value === 0)) {
                        $(`#hadoop_task_running_chart`).addClass('display-none');
                        $(`#hadoop_task_running_chart`).siblings().removeClass('display-none');
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

                        $(`#hadoop_task_running_chart`).removeClass('display-none');
                        $(`#hadoop_task_running_chart`).siblings().addClass('display-none');

                        initHadoopReportEchart(xData, yData, onlyData, 'hadoop_task_running_chart', unit);
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

            $('#hadoop_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            taskRunningChart.resize();
        }

        //  监听左侧菜单导航伸缩/展开触发的echart-resize事件
        $(window).on('echart-resize', function () {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                let chartWidth = $('.charts-wrapper .tab-content').width();
                let chartHeight = $('.charts-wrapper .tab-content').height();

                $('#hadoop_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                taskRunningChart.resize();
            }, 300);
        });
    }

    // <------------------------- END ECHART DATA --------------------------------------->

    // <------------------------- BEGIN TABLE DATA -------------------------------------->

    const initHadoopReportDetailsTable = () => {
        if ($('#hadoop_report_table').children().length === 0) { // 表格为渲染
            let options = {
                vin_url: "/api/v1/report/template/hadoop_data_details",
                vin_method: "GET",
                vin_toolbar: '.vin_toolbar',
                pagination: true, //分页
                resizable: true, //可变宽度
                showColumns: false,
                onPostBody: function() {
                    let tableData = $('#hadoop_report_table').bootstrapTable('getData');

                    if (tableData.length === 0) {
                        $('.data-detail-wrapper').addClass('nodata');
                    } else {
                        $('.data-detail-wrapper').removeClass('nodata');
                    }
                },
                columns: [
                    {
                        field:"cluster_name",
                        title:LANG.UI_HADOOP_CLUSTER_NAME,
                        sortable:true,
                        align:"center",
                        visible: customField.cluster_name,
                    },
                    {
                        field:"node_number",
                        title:LANG.UI_HADOOP_NODE_NUM,
                        sortable:true,
                        align:"center",
                        visible: customField.node_number,
                    },
                    {
                        field:"add_time",
                        title:LANG.UI_PUBLIC_ADD_TIME,
                        sortable:true,
                        align:"center",
                        visible: customField.add_time,
                    },
                    // {
                    //     field:"auth_status",
                    //     title:LANG.UI_CLOUD_PLATFORM_AUTHORIZE_STATUS,
                    //     sortable:true,
                    //     align:"center",
                    //     visible: customField.auth_status,
                    //     formatter:function(value){
                    //         let labelHtml = "";
                    //         switch(value){
                    //             case 1:
                    //                 labelHtml = '<span class="label label-sm label-success status-icon table-label_en width80_en">' +LANG.UI_CLOUD_PLATFORM_AUTHORIZED + '</span>';
                    //                 break;
                    //             case 2:
                    //             case 0:
                    //                 labelHtml = '<span class="label label-sm label-default status-icon table-label_en width80_en">' + LANG.UI_CLOUD_PLATFORM_UNAUTHORIZED + '</span>';
                    //                 break;
                    //         }
                    //         return labelHtml;
                    //     }
                    // },
                    {
                        field:"online_flag",
                        title:LANG.UI_HADOOP_CLUSTER_STATUS,
                        sortable:true,
                        align:"center",
                        visible: customField.online_flag,
                        formatter:function(value){
                            let labelHtml = "";
                            switch(value){
                                case 1:
                                    labelHtml = '<span class="label label-sm label-success status-icon">' + LANG.UI_CLOUD_PLATFORM_ONLINE + '</span>';
                                    break;
                                case 2:
                                    labelHtml = '<span class="label label-sm label-danger status-icon">' + LANG.UI_CLOUD_PLATFORM_OFFLINE + '</span>';
                                    break;
                                case 3:
                                    labelHtml = '<span class="label label-sm label-danger status-icon">' + LANG.UI_NODE_ABNORMAL + '</span>';
                                    break;
                                default:
                                    labelHtml = '<span class="label label-sm label-info status-icon">' + LANG.UI_PUBLIC_UNKNOWN + '</span>';
                                    break;
                            }
                            return labelHtml;
                        }
                    },
                ],
            }
            $('#hadoop_report_table').baseTableConfig().init(options);
        } else { // 表格已渲染直接刷新
            $('#hadoop_report_table').bootstrapTable('refresh');
        }
    }

    /**
     * 报表导出
     */
    const reportOverviewExport = () => {
        $('#hadoop_export').attr('data-bs-indicator', 'on');

        // 使用按钮防抖1.5秒执行一次导出
        debounce(captureScrollAndGeneratePDF($('.report-detail__content').get(0), 1500, 'hadoop-report.pdf',  LANG.UI_VINCHIN_REPORT_WATER_NAME, function() {
            $('#hadoop_export').removeAttr('data-bs-indicator');
        }), 1500);
    }

    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';
        HADOOP_REPORT_TEMPLATE_UUID = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存报表uuid

        if (HADOOP_REPORT_TEMPLATE_UUID) {
            getReportDetail();
        }
    }

    const initListener = () => {
        // 监听时间类型选择change
        $('#task_running_time_select').on('change', () => {
            CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.timeInterval = $('#task_running_time_select').val();
            getHadoopReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
        });

        // 搜索模版名称
        $('#report_search').off().on('click', () => {
            let searchVal = $('#search').val();

            if (searchVal) {
                $('#hadoop_report_table').bootstrapTable('refresh', {query: {search: searchVal}})
            }
        });

        $('#search').on('focus', () => {
            $('#report_clear_search').removeClass('hide');
        });

        // 清空搜索
        $('#report_clear_search').on('click', () => {
            $('#search').val('');
            $('#report_clear_search').addClass('hide');
            $('#hadoop_report_table').bootstrapTable('refresh', {query: {search: ''}})
            $('#hadoop_report_table').bootstrapTable('resetSearch');
        });

        // 监听导出
        $('#hadoop_export').on('click', reportOverviewExport);
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
    HadoopDetail.init();
});
