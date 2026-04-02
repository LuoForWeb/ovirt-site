var taskDetail = (function() {
    const TASK_RUNNING_STATUS_TYPE = {
        ALL: 1,
        SUCCESSED: 2,
        FAILED: 3
    }; // 任务运行状态类型
    const STATUS_TYPE_TO_ECHART_ID_MAP = {
        1: 'task_report_all_chart',
        2: 'task_report_success_chart',
        3: 'task_report_failed_chart'
    }; // 任务运行趋势状态对应echart id
    const TASK_REPORT_TABLE_FILTER_OPTIONS = [
        {
            label: LANG.UI_PUBLIC_TASK_STATUS,
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
    ]; // 任务报表过滤搜索选项组
    let TASK_REPORT_TEMPLATE_UUID = '';
    let CURRENT_TASK_RUNNING_ECHART_ID = 'task_report_all_chart';
    let CURRENT_RUNNING_TENDENCY_QUERY_PARAMS = {
        type: CONF.REPORT_TYPE.TASK,
        timeInterval: CONF.RUNNING_TIME_TYPE.LAST_MONTH,
        statusType: TASK_RUNNING_STATUS_TYPE.ALL
    }; // 当前任务运行趋势查询参数对象
    let customField = {};
    let ADVANCED_SEARCH_PARAMS = {
        search: '',
        module_type: '', 
        sub_module_type: '',
        job_type: '',
        storage_location: '', 
        dev_type: '',
        job_status: ''
    }; // 高级搜索查询参数
    let taskRunningChart = null;


    // <------------------------- BEGIN OVERFIEW DATA ----------------------------------->

    const initOverviewData = () => {
        pAjaxRequest({}, '/api/v1/report/template/overview/6', 'get', function (res) {
            try {
                if (res.success) {
                    let overviewList = res.data.overviewList;
    
                    $('#task_total').text(overviewList.task_num);
                    $('#task_success').text(overviewList.success_task);
                    $('#task_abnormal').text(overviewList.abnormal_task);
                    $('#task_failed').text(overviewList.failed_task);
                    $('#task_stoped').text(overviewList.stop_task);

                    $('#history_task_total').text(overviewList.history_num);
                    $('#history_task_success').text(overviewList.success_history);
                    $('#history_task_abnormal').text(overviewList.abnormal_history);
                    $('#history_task_failed').text(overviewList.failed_history);
                    $('#history_task_stoped').text(overviewList.stop_history);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_TASK_REPORT_OVERVIEW_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_TASK_REPORT_OVERVIEW_DATA_ERROR);
            }
        });
    }

    /**
     * 获取虚任务报表详情数据
     */
    const getReportDetail = () => {
        pAjaxRequest({}, '/api/v1/report/template/detail/' + TASK_REPORT_TEMPLATE_UUID, 'get', function (res) {
            try {
                Metronic.blockUI({target: ".report-detail__content",animate: true});

                if (res.success) {
                    let { custom_field, overview, running_tendency } = { ...res.data };
    
                    // 渲染概览数据
                   if (!overview.task_num && !overview.stop_task && !overview.success_task && !overview.abnormal_task && !overview.failed_task) { // 如果全都没配置就不显示数据概览
                        $('.overview-header').addClass('display-none');
                        $('.task-wrapper').addClass('display-none');
                   } else { // 只要配置了一项就显示数据概览
                        $('.overview-header').removeClass('display-none');
                        $('.task-wrapper').removeClass('display-none');
    
                        overview.task_num ? $('.task-total-box').removeClass('display-none') : $('.task-total-box').hide();
                        overview.stop_task ? $('.task-stoped-box').removeClass('display-none') : $('.task-stoped-box').hide();
                        overview.success_task ?  $('.task-success-box').removeClass('display-none') : $('.task-success-box').hide();
                        overview.abnormal_task ? $('.task-abnormal-box').removeClass('display-none') : $('.task-abnormal-box').hide();
                        overview.failed_task ? $('.task-failed-box').removeClass('display-none') : $('.task-failed-box').hide();
    
                        initOverviewData();
                   }
    
                   // 渲染任务运行趋势echart图
                   if (running_tendency.history) {
                        $('.echart-header').removeClass('display-none');
                        $('.echart-wrapper').removeClass('display-none');
    
                        getTaskReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS, CURRENT_TASK_RUNNING_ECHART_ID);
                   } else {
                        $('.echart-header').addClass('display-none');
                        $('.echart-wrapper').addClass('display-none');
                   }
    
                   customField = { ...custom_field };

                   // 模块类型、任务类型 包含了其中一种就初始化高级搜索和级联组件
                   if (customField.module_type || customField.task_type) {
                        $('.tool-cascader').removeClass('display-none');
                        initTaskReportCascader();
                   } else {
                        $('.tool-cascader').addClass('display-none');
                   }

                   if (customField.task_status) {
                        $('.tool-filter').removeClass('display-none');
                        initTaskReportFiler();
                   } else {
                        $('.tool-filter').addClass('display-none');
                   }

    
                   // 渲染数据明细表格
                   initTaskReportDetailsTable();
                } else {
                    UIToastr.showWarning(LANG.UI_GET_TASK_REPORT_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_TASK_REPORT_DATA_ERROR);
            } finally {
                Metronic.unblockUI('.report-detail__content');
            }
        });

        
    }

    // <------------------------- END OVERFIEW DATA ------------------------------------->


    // <------------------------- BEGIN ECHART DATA ------------------------------------->

    /**
     * 初始化任务运行报表echart
     * @param {*} xData 
     * @param {*} yData 
     * @param {*} onlyData 
     * @param {*} echartId 
     * @param {*} unit 
     */
    const initTaskReportEchart = (xData, yData, onlyData, echartId, unit) => {
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
     * 获取任务报表运行趋势数据
     * @param {*} params 
     * @param {*} echartId 
     */
    const getTaskReportTaskRunningData = (params, echartId) => {
        Metronic.blockUI({target: ".charts-wrapper .tab-content",animate: true});
        
        pAjaxRequest(params, '/api/v1/report/template/tendency', 'get', (res) => {
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

                        initTaskReportEchart(xData, yData, onlyData, echartId, unit);
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
     * 处理业务类型选所有场景
     * @param {*} businessType 
     */
    const handleSelectedBusinessType = (businessType) => {
        switch (Number(businessType)) {
            case CONF.SYSTEM_BUSINESS_TYPE.DATA_BACKUP: // 数据备份所有的模块类型
                return { module_type: '2,5,3,4,11,14,28', sub_module_type: '', job_type: '' };
            case CONF.SYSTEM_BUSINESS_TYPE.CONTINUOUS_DATA_PROTECT: // 持续数据保护所有的模块类型
                return { module_type: '10', sub_module_type: '', job_type: '' };
            case CONF.SYSTEM_BUSINESS_TYPE.DATA_COPY: // 数据复制所有的模块类型
                return { module_type: '10,12,26', sub_module_type: '', job_type: '' };
        }
    }

    /**
     * 处理选择的任务类型参数
     * @param {*} taskType 
     */
    const handleSelectedTaskType = (taskType) => {
        let moduleTaskData = taskType.value.split('-');

        switch (moduleTaskData.length) {
            case 2: // 不包含子模块的模块类型：数据备份 - NAS、数据库，数据复制 - 数据库，数据复制 - 文件
                return {  module_type: moduleTaskData[0], sub_module_type: '',  job_type: moduleTaskData[1] };
            case 3: // 包含子模块的模块类型（但不包括持续数据保护和数据复制的整机模块）：数据备份 - 虚拟化、私有云、公有云、整机、卷、文件、Hadoop、对象存储
                return {  module_type: moduleTaskData[0], sub_module_type: moduleTaskData[1],  job_type: moduleTaskData[2] };
            case 4: // 包含子模块的模块类型（并包括持续数据保护和数据复制的整机模块）：连续数据保护 - 整机、卷，数据复制 - 整机、卷
                return { module_type: moduleTaskData[0], storage_location: moduleTaskData[1], dev_type: moduleTaskData[2], job_type: moduleTaskData[3] };
            default:
                return;
        }
    }

    /**
     * 初始化任务报表级联选择器
     */
    const initTaskReportCascader = () => {
        const cascader = new Cascader({
            container: "#task_report_cascader",
            data: MODULE_TASK_TYPE_CASCADER_TREE_DATA,
            placeholder: LANG.UI_CASCADER_PLACEHOLDER,
            selectFn: (val) => {
                let len = val.length;
                let taskTypeParams = {};

                switch (len) {
                    case 0: // 清除所有
                        ADVANCED_SEARCH_PARAMS = { module_type: '', sub_module_type: '', job_type: '' };
                        break;
                    case 2: // 模块类型选的所有
                        taskTypeParams = handleSelectedBusinessType(val[1].value.split('-')[0]);
                        ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, taskTypeParams);
                        break;
                    case 3: // 模块类型非所有
                        taskTypeParams = handleSelectedTaskType(val[2]);
                        ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, taskTypeParams);
                        break;
                    default:
                        break;
                }

                $('#task_report_table').bootstrapTable('refresh', { query: { ...ADVANCED_SEARCH_PARAMS } });
            },
            clearable: true
        });
    }

    const initTaskReportFiler = () => {
        $('#task_report_filter_wrapper').initFilter({
            filterSlotId: 'task_report_filter_wrapper',
            filterBtnId: 'task_report_filter_btn',
            filters:TASK_REPORT_TABLE_FILTER_OPTIONS
        });
    }

    const statusFormatter = function (index, row) {
        switch (row.task_status) {
            case 1:
            case 10://等待运行 蓝色
                return '<span class="label label-sm label-info status-icon">'+LANG.UI_PUBLIC_WAIT+'</span>';
            case 17:
            case 13:
                return '<span class="label label-sm label-success status-icon">'+LANG.UI_DATACENTER_SUCCESS+'</span>';
            case 2:
            case 9:
            case 12:
            case 14:
            case 15:
                return '<span class="label label-sm label-success status-icon">'+LANG.UI_VISUAL_RUNNING+'</span>';
            case 3:
            case 4: //灰色
                return '<span class="label label-sm label-default">'+LANG.UI_JOB_STOP+'</span>';
            case 5: //灰色
            case 11: //异常
            case 16: //灰色
                return '<span class="label label-sm label-default">'+LANG.UI_VISUAL_STOPPING+'</span>';
            case 0: //失败
            case 7: //失败
            case 6: //失败
            case 8: //失败
                return '<span class="label label-sm label-danger status-icon">'+LANG.UI_PUBLIC_ERROR+'</span>';
        }
    }

    /**
     * 获取自定义查询参数
     * @returns 
     */
    const getParams = function () {
        let queryParams = {};
        queryParams.search = $('#search').val();

        // 合并过滤器搜索参数
        queryParams = Object.assign(queryParams, ADVANCED_SEARCH_PARAMS);
        
        return queryParams;
    }

    /**
     * 初始化任务报表数据明细
     */
    const initTaskReportDetailsTable = () => {
        if ($('#task_report_table').children().length === 0) { // 表格为渲染
            let options = {
                vin_url: "/api/v1/report/template/task_data_details",
                vin_method: "GET",
                vin_toolbar: '.vin_toolbar',
                pagination: true, //分页
                resizable: true, //可变宽度
                showColumns: false,
                vin_params: function () {
                    return getParams();
                },
                onPostBody: function() {
                    let tableData = $('#task_report_table').bootstrapTable('getData');

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
                        sortable: false,
                        align: 'center',
                        visible: customField.task_name
                    },
                    {
                        field: 'module_type',
                        title: LANG.UI_SEARCH_MODE_TYPE,
                        sortable: true,
                        align: 'center',
                        type: 'module',
                        visible: customField.module_type,
                    },
                    {
                        field: 'task_type',
                        title: LANG.UI_TASK_AWS_JOB_TYPE,
                        sortable: true,
                        align: 'center',
                        visible: customField.task_type
                    },
                    {
                        field: 'task_status',
                        title: LANG.UI_SEARCH_TASK_STATUS,
                        sortable: true,
                        align: 'center',
                        formatter: statusFormatter,
                        visible: customField.task_status,
                    },
                    {
                        field: 'create_time',
                        title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
                        sortable: true,
                        align: 'center',
                        visible: customField.create_time
                    },
                    {
                        field: 'last_start_time',
                        title: LANG.UI_REPORT_LAST_TIME,
                        sortable: true,
                        align: 'center',
                        visible: customField.last_start_time,
                    },
                    {
                        field: 'uptime_time',
                        title: LANG.UI_PUBLIC_CONTINUE_RUN_TIME,
                        sortable: false,
                        align: 'center',
                        visible: customField.uptime_time,
                    },
                    {
                        field: 'finish_time',
                        title: LANG.UI_PUBLIC_FINISH_TIME,
                        sortable: true,
                        align: 'center',
                        visible: customField.finish_time,
                    },
                    {
                        field: 'next_run_time',
                        title: LANG.UI_REPORT_NEXT_RUNNING_TIME,
                        sortable: false,
                        align: 'center',
                        visible: customField.next_run_time,
                    },
                    {
                        field: 'total_object_size',
                        title: LANG.UI_HOMEPAGEPRO_BACKUP_DATA,
                        sortable: true,
                        align: 'center',
                        visible: customField.total_object_size
                    },
                    {
                        field: 'total_object_transport_size',
                        title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.total_object_transport_size
                    },
                    {
                        field: 'total_object_write_size',
                        title: LANG.UI_PUBLIC_REAL_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.total_object_write_size
                    },
                    {
                        field: 'storage_nickname',
                        title: LANG.UI_REPORT_BELONG_STORAGE,
                        sortable: false,
                        align: 'center',
                        visible: customField.storage_nickname,
                    },
                    {
                        field: 'create_user',
                        title: LANG.UI_REPORT_CREATE_NAME,
                        sortable: false,
                        align: 'center',
                        visible: customField.create_user,
                    },
                ],
            }
            $('#task_report_table').baseTableConfig().init(options);
        } else { // 表格已渲染直接刷新
            $('#task_report_table').bootstrapTable('refresh');
        }
    }

    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';
        TASK_REPORT_TEMPLATE_UUID = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存报表uuid

        if (TASK_REPORT_TEMPLATE_UUID) {
            getReportDetail();
        }
    }

    /**
     * 报表概览导出
     */
    const reportOverviewExport = () => {
        $('#task_export').attr('data-bs-indicator', 'on');

        // 使用按钮防抖1.5秒执行一次导出
        debounce(captureScrollAndGeneratePDF($('.report-detail__content').get(0), 1500, 'task-report.pdf',  LANG.UI_VINCHIN_REPORT_WATER_NAME, function() {
            $('#task_export').removeAttr('data-bs-indicator');
        }), 1500);
    }

    const initListener = function() {
        // 监听任务运行趋势tabs change
        $('#task_status_navs').on('click', function(event) {
            if (event.target.tagName === 'A') { // 检查点击的元素是否是一个 <a> 标签
                // 获取父元素 li.nav-item
                let clickedItem = event.target.parentElement;
                CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.statusType = clickedItem.getAttribute('data-type');
                CURRENT_TASK_RUNNING_ECHART_ID = STATUS_TYPE_TO_ECHART_ID_MAP[CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.statusType];

                getTaskReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS, CURRENT_TASK_RUNNING_ECHART_ID);
            }
        });

        // 监听时间类型选择change
        $('#task_running_time_select').on('change', () => {
            CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.timeInterval = $('#task_running_time_select').val();
            getTaskReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS, CURRENT_TASK_RUNNING_ECHART_ID);
        });

        // 搜索模版名称
        $('#report_search').off().on('click', () => {
            ADVANCED_SEARCH_PARAMS.search = $('#search').val();

            if (ADVANCED_SEARCH_PARAMS.search) {
                $('#task_report_table').bootstrapTable('refresh', {query: {...ADVANCED_SEARCH_PARAMS}});
            }
        });

        $('#search').on('focus', () => {
            $('#report_clear_search').removeClass('hide');
        });

        $('#search').on('keydown', (event) => {
            if (event.key === 'Enter' || event.keyCode === 13) {
                // 在这里添加你想要执行的代码
                ADVANCED_SEARCH_PARAMS.search = $('#search').val();
                if (ADVANCED_SEARCH_PARAMS.search) {
                    $('#task_report_table').bootstrapTable('refresh', {query: {...ADVANCED_SEARCH_PARAMS}});
                }
            }
        });

        // 清空搜索
        $('#report_clear_search').on('click', () => {
            $('#search').val('');
            ADVANCED_SEARCH_PARAMS.search = '';
            $('#report_clear_search').addClass('hide');
            $('#task_report_table').bootstrapTable('refresh', {query: {search: ''}})
            $('#task_report_table').bootstrapTable('resetSearch');
        });

        // 监听导出
        $('#task_export').on('click', reportOverviewExport);

        window.$off('task_report_filter_btn-updateFilterEvent');

        // 监听任务报表表格 - 过滤器组件派发的数据，以更新表格
        window.$on('task_report_filter_btn-updateFilterEvent', (filterData) => {
            if (filterData.length > 0) {
                
                let taskStatusList = getTableFilterParams('task_status', filterData);

                if (taskStatusList.value.length > 0) {
                    ADVANCED_SEARCH_PARAMS.job_status = taskStatusList.value.join(',');
                } else {
                    ADVANCED_SEARCH_PARAMS.job_status = '';
                }

            } else {
                ADVANCED_SEARCH_PARAMS.job_status = '';
            }

            $('#task_report_table').bootstrapTable('refresh', { query: { ...ADVANCED_SEARCH_PARAMS } });
        });
    }

    // <------------------------- END TABLE DATA ---------------------------------------->

    /**
     * echart图自适应
     */
    const watchEchartSizeChange = () => {
        window.onresize = function () {
            switch (CURRENT_TASK_RUNNING_ECHART_ID) {
                case 'task_report_all_chart':
                    // 有数据时，导航栏折叠/展开时，echart图要重绘
                    if (!$('#task_report_all_chart').hasClass('display-none')) {
                        let chartWidth = $('#all_tab').width();
                        let chartHeight = $('#all_tab').height();
                        $('#task_report_all_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                        taskRunningChart.resize();
                    }
                    break;
                case 'task_report_success_chart':
                    // 有数据时，导航栏折叠/展开时，echart图要重绘
                    if (!$('#task_report_success_chart').hasClass('display-none')) {
                        let chartWidth = $('#success_tab').width();
                        let chartHeight = $('#success_tab').height();
                        $('#task_report_success_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                        taskRunningChart.resize();
                    }
                    break;
                case 'task_report_failed_chart':
                    // 有数据时，导航栏折叠/展开时，echart图要重绘
                    if (!$('#task_report_failed_chart').hasClass('display-none')) {
                        let chartWidth = $('#failed_tab').width();
                        let chartHeight = $('#failed_tab').height();
                        $('#task_report_failed_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                        taskRunningChart.resize();
                    }
                    break;
                default:
                    break;
            }
        }

        $(window).on('echart-resize', function() {
            // 设置500毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.5s ease;，因此要等500毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                switch (CURRENT_TASK_RUNNING_ECHART_ID) {
                    case 'task_report_all_chart':
                        // 有数据时，导航栏折叠/展开时，echart图要重绘
                        if (!$('#task_report_all_chart').hasClass('display-none')) {
                            let chartWidth = $('#all_tab').width();
                            let chartHeight = $('#all_tab').height();
                            $('#task_report_all_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                            taskRunningChart.resize();
                        }
                        break;
                    case 'task_report_success_chart':
                        // 有数据时，导航栏折叠/展开时，echart图要重绘
                        if (!$('#task_report_success_chart').hasClass('display-none')) {
                            let chartWidth = $('#success_tab').width();
                            let chartHeight = $('#success_tab').height();
                            $('#task_report_success_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                            taskRunningChart.resize();
                        }
                        break;
                    case 'task_report_failed_chart':
                        // 有数据时，导航栏折叠/展开时，echart图要重绘
                        if (!$('#task_report_failed_chart').hasClass('display-none')) {
                            let chartWidth = $('#failed_tab').width();
                            let chartHeight = $('#failed_tab').height();
                            $('#task_report_failed_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

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
    taskDetail.init();
});
