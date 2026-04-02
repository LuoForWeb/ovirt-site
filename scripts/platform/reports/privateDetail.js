var PrivateCloudDetail = (function() {
    let PRIVATE_CLOUD_REPORT_TEMPLATE_UUID = '';
    let CURRENT_RUNNING_TENDENCY_QUERY_PARAMS = {
        type: CONF.REPORT_TYPE.PRIVATE_CLOUD,
        timeInterval: CONF.RUNNING_TIME_TYPE.LAST_MONTH,
    }; // 当前任务运行趋势查询参数对象
    let customField = {};
    let PRIVATE_REPORT_TABLE_FILTER_OPTIONS = [
        {
            label: LANG.UI_PLATFORM_PRIVATE_CLOUD_PLATFORM_TYPE,
            field: 'vm_type',
            value: []
        },
        {
            label: LANG.UI_REPORT_STATUS,
            field: 'vm_status',
            value: [
                {
                    id: 'vm_off',
                    value: CONF.VM_STATUS_TYPE.OFF,
                    text: LANG.UI_CLOUD_PLATFORM_POWER_OFF,
                    tag: true,
                    type: 'secondary'
                },
                {
                    id: 'vm_on',
                    text: LANG.UI_CLOUD_PLATFORM_POWER_ON,
                    value:  CONF.VM_STATUS_TYPE.ON,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'vm_suspend',
                    text: LANG.UI_PUBLIC_PENDING,
                    value:  CONF.VM_STATUS_TYPE.ON,
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'vm_pause',
                    text: LANG.UI_JOB_PAUSE,
                    value:  CONF.VM_STATUS_TYPE.PAUSE,
                    tag: true,
                    type: 'warning'
                }
            ]
        },
        {
            label: LANG.UI_REPORT_PROTECT,
            field: 'protect_status',
            value: [
                {
                    id: 'vm_protected',
                    value: CONF.BACKUP_STATUS_TYPE.PROTECTED,
                    text: LANG.UI_VM_REPORT_IN_BACKUP,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'vm_unprotected',
                    text: LANG.UI_VM_REPORT_NOIN_BACKUP,
                    value:  CONF.BACKUP_STATUS_TYPE.UNPROTECTED,
                    tag: true,
                    type: 'secondary'
                }
            ]
        }
    ];
    let filterParams = {
        search: ''
    }; // 过滤器组件选择的过滤选项参数
    let taskRunningChart = null;

    // <------------------------- BEGIN OVERFIEW DATA ----------------------------------->

    /**
     * 获取所有授权的私有云平台类型
     * @param {*} fields 
     */
    const initAllPrivateCloudType = (fields) => {
        let params = { cloud_flag: true, cloud_type: 'private' };

        pAjaxRequest(params, "/api/v1/vm/platforms/hypervisors", "GET", (res) => {
            if (res.success) {
                let vmList = res.data.hypervisors;

                if (vmList.length > 0) {
                    PRIVATE_REPORT_TABLE_FILTER_OPTIONS[0].value = vmList.map(item => {
                        return {
                            id: `virtual_type_${item.value}`,
                            text: item.text,
                            value: item.value,
                            tag: false
                        }
                    });
                }

                let filterData = PRIVATE_REPORT_TABLE_FILTER_OPTIONS.filter(option => {
                    return fields.includes(option.field);
                });

                // 渲染数据明细表格过滤器
                initVmTableFilter(filterData);
            } else {
                UIToastr.showWarning(LANG.UI_GET_VIRTUAL_TYPE_FAILED);
            }
        }); 
    }

    const initOverviewData = () => {
        pAjaxRequest({}, '/api/v1/report/template/overview/9', 'get', function (res) {
            try {
                if (res.success) {
                    let overviewList = res.data.overviewList;

                    $('#vm_total_num').text(overviewList.vm_number);
                    $('#vm_protected_num').text(overviewList.protected_number);
                    $('#vm_platform_num').text(overviewList.vm_platform);
                    $('#vm_backup_times_num').text(overviewList.backup_number);
                    $('#vm_backup_data_num').text(!overviewList.backup_data ? 0 : unitConver(Number(overviewList.backup_data)).size);
                    $('#vm_backup_unit').text(!overviewList.backup_data ? 'B' : unitConver(Number(overviewList.backup_data)).unit);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_PRIVATE_CLOUD_REPORT_OVERVIEW_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_PRIVATE_CLOUD_REPORT_OVERVIEW_DATA_ERROR);
            }
        });
    }

    const getReportDetail = () => {
        pAjaxRequest({}, '/api/v1/report/template/detail/' + PRIVATE_CLOUD_REPORT_TEMPLATE_UUID, 'get', (res) => {
            try {
                Metronic.blockUI({target: ".report-detail__content",animate: true});

                if (res.success) {
                    let { custom_field, overview, running_tendency } = { ...res.data };

                    // 渲染概览数据
                    if (!overview.vm_number && !overview.protected_number && !overview.vm_platform && !overview.backup_number && !overview.backup_data) {
                        $('.overview-header').addClass('display-none');
                        $('.report-detail-boxes').addClass('display-none');
                    } else { // 只要配置了一项就显示数据概览
                        $('.overview-header').removeClass('display-none');
                        $('.report-detail-boxes').removeClass('display-none');

                        overview.vm_number ? $('.vm-total-box').removeClass('display-none') : $('.vm-total-box').hide();
                        overview.protected_number ? $('.vm-protected-box').removeClass('display-none') : $('.vm-protected-box').hide();
                        overview.vm_platform ?  $('.vm-platform-box').removeClass('display-none') : $('.vm-platform-box').hide();
                        overview.backup_number ? $('.vm-backup-times-box').removeClass('display-none') : $('.vm-backup-times-box').hide();
                        overview.backup_data ? $('.vm-backup-data-box').removeClass('display-none') : $('.vm-backup-data-box').hide();

                        initOverviewData();
                    }

                    // 渲染任务运行趋势echart图
                    if (running_tendency.history) {
                        $('.echart-header').removeClass('display-none');
                        $('.echart-wrapper').removeClass('display-none');

                        getPrivateCloudReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
                    } else {
                        $('.echart-header').addClass('display-none');
                        $('.echart-wrapper').addClass('display-none');
                    }

                    customField = { ...custom_field };

                    // 获取过滤器选项数组
                    let fields = [];
                    if (customField.vm_type) fields.push('vm_type');
                    if (customField.online) fields.push('vm_status');
                    if (customField.protect_status) fields.push('protect_status');

                    if (fields.length > 0) {
                        if (fields.indexOf('vm_type') > -1) { // 包含了私有云平台类型字段则获取已授权的所有虚拟化类型后再初始化过滤器
                            initAllPrivateCloudType(fields);
                        } else { // 未包含
                            let filterData = PRIVATE_REPORT_TABLE_FILTER_OPTIONS.filter(option => {
                                return fields.includes(option.field);
                            });
            
                            // 渲染数据明细表格过滤器
                            initVmTableFilter(filterData);
                        }
                    }

                    // 渲染数据明细表格
                    initPrivateReportDetailsTable();

                } else {
                    UIToastr.showWarning(LANG.UI_GET_PRIVATE_CLOUD_REPORT_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_PRIVATE_CLOUD_REPORT_DATA_ERROR);
            } finally {
                Metronic.unblockUI('.report-detail__content');
            }
        });
    }

    // <------------------------- END OVERFIEW DATA ------------------------------------->


    // <------------------------- BEGIN ECHART DATA ------------------------------------->

    const initPrivateCloudReportEchart = (xData, yData, onlyData, echartId, unit) => {
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

    const getPrivateCloudReportTaskRunningData = (params) => {
        Metronic.blockUI({target: ".tab-content__echart",animate: true});

        pAjaxRequest(params, '/api/v1/report/template/tendency', 'get', (res) => {
            try {
                if (res.success) {
                    let xData = [], yData = [];
                    let maxValue = 0;
                    let resData = res.data
                    if (res.data.every(i => i.value === 0)) {
                        $(`#private_cloud_task_running_chart`).addClass('display-none');
                        $(`#private_cloud_task_running_chart`).siblings().removeClass('display-none');
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

                        $(`#private_cloud_task_running_chart`).removeClass('display-none');
                        $(`#private_cloud_task_running_chart`).siblings().addClass('display-none');

                        initPrivateCloudReportEchart(xData, yData, onlyData, 'private_cloud_task_running_chart', unit);
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

            $('#private_cloud_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            taskRunningChart.resize();
        }

        //  监听左侧菜单导航伸缩/展开触发的echart-resize事件
        $(window).on('echart-resize', function () {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                let chartWidth = $('.charts-wrapper .tab-content').width();
                let chartHeight = $('.charts-wrapper .tab-content').height();

                $('#private_cloud_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                taskRunningChart.resize();
            }, 300);
        });
    }

    // <------------------------- END ECHART DATA --------------------------------------->

    // <------------------------- BEGIN TABLE DATA -------------------------------------->

    const initPrivateReportDetailsTable = () => {
        if ($('#private_cloud_report_table').children().length === 0) { // 表格为渲染
            let options = {
                vin_url: "/api/v1/report/template/private_data_details",
                vin_method: "GET",
                vin_toolbar: '.vin_toolbar',
                pagination: true, //分页
                resizable: true, //可变宽度
                showColumns: false,
                onPostBody: function() {
                    let tableData = $('#private_cloud_report_table').bootstrapTable('getData');

                    if (tableData.length === 0) {
                        $('.data-detail-wrapper').addClass('nodata');
                    } else {
                        $('.data-detail-wrapper').removeClass('nodata');
                    }
                },
                columns: [
                    {
                        field: 'vm_name',
                        title: LANG.UI_DB_INSTANCE_NAME,
                        sortable: true,
                        align: 'center',
                        visible: customField.vm_name
                    },
                    {
                        field: 'ip',
                        title: LANG.UI_PLATFORM_PRIVATE_CLOUD_PLATFORM,
                        sortable: true,
                        align: 'center',
                        visible: customField.ip
                    },
                    {
                        field: 'vm_ip',
                        title: LANG.UI_TENANT_AWS_IP,
                        sortable: true,
                        align: 'center',
                        visible: customField.vm_ip
                    },
                    {
                        field: 'online',
                        title: LANG.UI_REPORT_STATUS,
                        sortable: true,
                        align: 'center',
                        visible: customField.online,
                        formatter: function (value) {
                            switch (value) {
                                case 0:
                                    return `<span class="label label-default">${LANG.UI_PUBLIC_UNKNOWN}</span>`;
                                case 1:
                                    return `<span class="label label-default">${LANG.UI_CLOUD_PLATFORM_POWER_OFF}</span>`;
                                case 2:
                                    return `<span class="label label-success">${LANG.UI_CLOUD_PLATFORM_POWER_ON}</span>`;
                                case 3:
                                    return `<span class="label label-info">${LANG.UI_PUBLIC_PENDING}</span>`;
                                case 4:
                                    return `<span class="label label-warning">${LANG.UI_JOB_PAUSE}</span>`;
                                default:
                                    return;
                            }
                        }
                    },
                    {
                        field: 'vm_type',
                        title: LANG.UI_PLATFORM_PRIVATE_CLOUD_PLATFORM_TYPE,
                        sortable: true,
                        align: 'center',
                        visible: customField.vm_type,
                    },
                    {
                        field: 'last_backup_time',
                        title: LANG.UI_CLIENT_CURRENT_TIME,
                        sortable: true,
                        align: 'center',
                        visible: customField.last_backup_time
                    },
                    {
                        field: 'backup_number',
                        title: LANG.UI_REPORT_BACKUP_COUNT,
                        sortable: true,
                        align: 'center',
                        visible: customField.backup_number
                    },
                    {
                        field: 'total_object_size',
                        title: LANG.UI_PUBLIC_VM_TOTAL_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.total_object_size,
                        formatter: function (value) {
                            return `${unitConver(Number(value)).size}${unitConver(Number(value)).unit}`;
                        }
                    },
                    {
                        field: 'backup_data',
                        title: LANG.UI_PUBLIC_VM_VALID_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.backup_data,
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
                        field: 'protect_status',
                        title: LANG.UI_REPORT_PROTECT,
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
                                    return;
                            }
                        }
                    },
                    {
                        field: 'task',
                        title: LANG.UI_REPORT_SET_TASK,
                        sortable: true,
                        align: 'center',
                        visible: customField.task,
                    },
                    {
                        field: 'storage_nickname',
                        title: LANG.UI_REPORT_STORAGE_NAME,
                        sortable: true,
                        align: 'center',
                        visible: customField.storage_nickname,
                    }
                ],
            }
            $('#private_cloud_report_table').baseTableConfig().init(options);
        } else { // 表格已渲染直接刷新
            $('#private_cloud_report_table').bootstrapTable('refresh');
        }
    }

    /**
     * 初始化虚拟机报表数据明细表格过滤器
     * @param {*} filterData 过滤器数组
     */
    const initVmTableFilter = (filterData) => {
        if ($('#private_report_filter_btn').length === 0) {
            $('#private_report_filter_wrapper').initFilter({
                filterSlotId: 'private_report_filter_wrapper',
                filterBtnId: 'private_report_filter_btn',
                filters: filterData
            });
        } else {
            $('#private_report_filter_wrapper').resetFilter({
                filterBtnId: 'private_report_filter_btn',
                filters: filterData
            });
        }
    }

    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';
        PRIVATE_CLOUD_REPORT_TEMPLATE_UUID = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存报表uuid

        if (PRIVATE_CLOUD_REPORT_TEMPLATE_UUID) {
            getReportDetail();
        }
    }

     /**
     * 报表导出
     */
     const reportOverviewExport = () => {
        $('#private_cloud_export').attr('data-bs-indicator', 'on');

        // 使用按钮防抖1.5秒执行一次导出
        debounce(captureScrollAndGeneratePDF($('.report-detail__content').get(0), 1500, 'private-cloud-report.pdf',  LANG.UI_VINCHIN_REPORT_WATER_NAME, function() {
            $('#private_cloud_export').removeAttr('data-bs-indicator');
        }), 1500);
    }


    const initListener = () => {
        // 监听时间类型选择change
        $('#task_running_time_select').on('change', () => {
            CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.timeInterval = $('#task_running_time_select').val();
            getPrivateCloudReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
        });

        // 搜索模版名称
        $('#report_search').off().on('click', () => {
            filterParams.search = $('#search').val();

            if (filterParams.search) {
                $('#private_cloud_report_table').bootstrapTable('refresh', {query: {...filterParams}})
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
                    $('#private_cloud_report_table').bootstrapTable('refresh', {query: {...filterParams}})
                }
            }
        });

        // 清空搜索
        $('#report_clear_search').on('click', () => {
            $('#search').val('');
            filterParams.search = '';
            $('#report_clear_search').addClass('hide');
            $('#private_cloud_report_table').bootstrapTable('refresh', {query: {...filterParams}})
        });

        // 监听导出
        $('#private_cloud_export').on('click', reportOverviewExport);

        window.$off('private_report_filter_btn-updateFilterEvent');

        // 监听数据明细 - 过滤器组件派发的数据，以更新表格
        window.$on('private_report_filter_btn-updateFilterEvent', (filterData) => {
            if (filterData.length > 0) {
                
                let vmTypeList = getTableFilterParams('vm_type', filterData);
                let vmStatusList = getTableFilterParams('vm_status', filterData);
                let backupStatusList = getTableFilterParams('protect_status', filterData);

                if (vmTypeList.value.length > 0) {
                    filterParams.vm_type = vmTypeList.value;
                } else {
                    filterParams.vm_type = '';
                }

                if (vmStatusList.value.length > 0) {
                    filterParams.vm_status = vmStatusList.value;
                } else {
                    filterParams.vm_status = '';
                }

                if (backupStatusList.value.length > 0) {
                    filterParams.protect_status = backupStatusList.value;
                } else {
                    filterParams.protect_status = '';
                }

            } else {
                filterParams.vm_type = '';
                filterParams.vm_status = '';
                filterParams.protect_status = '';
            }

            $('#private_cloud_report_table').bootstrapTable('refresh', { query: { ...filterParams } });
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
    PrivateCloudDetail.init();
});
