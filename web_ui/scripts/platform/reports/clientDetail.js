var clientDetail = (function() {
    let CLIENT_REPORT_TEMPLATE_UUID = '';
    let CURRENT_RUNNING_TENDENCY_QUERY_PARAMS = {
        type: CONF.REPORT_TYPE.CLIENT,
        timeInterval: CONF.RUNNING_TIME_TYPE.LAST_MONTH,
    }; // 当前任务运行趋势查询参数对象
    let customField = {};
    let CLIENT_REPORT_TABLE_FILTER_OPTIONS = [
        {
            label: LANG.UI_VISUAL_BACKUP,
            field: 'timing_data_protect',
            value: [
                {
                    id: 'complete_machine',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_DISK,
                    text: LANG.UI_BACKUP_DATA_MODULE_OS,
                },
                {
                    id: 'osbackup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_VOLUME,
                    text: LANG.UI_VOL_CDP_RECOVER_VOL,
                },
                {
                    id: 'filebackup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.FILE,
                    text: LANG.UI_FILE_FILE,
                },
                {
                    id: 'db_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DB,
                    text: LANG.UI_AGENT_MODULE_DB,
                }
            ]
        },
        {
            label: LANG.UI_REPORY_CDP,
            field: 'real_time_data_protect',
            value: [
                {
                    id: 'complete_cdp_backup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_DISK,
                    text: LANG.UI_BACKUP_DATA_MODULE_OS,
                },
                {
                    id: 'vol_cdp_backup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_VOLUME,
                    text: LANG.UI_VOL_CDP_RECOVER_VOL,
                },
            ]
        },
        {
            label: LANG.UI_CM_CDP_REPLICATION,
            field: 'data_copy',
            value: [
                {
                    id: 'machine_copy',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_DISK,
                    text: LANG.UI_BACKUP_DATA_MODULE_OS,
                },
                {
                    id: 'vol_cdp_copy',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_VOLUME,
                    text: LANG.UI_VOL_CDP_RECOVER_VOL,
                },
                {
                    id: 'dbcdpcopy',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_DB,
                    text: LANG.UI_AGENT_MODULE_DB,
                },
                {
                    id: 'file_copy_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_FILE,
                    text: LANG.UI_FILE_FILE,
                },
            ]
        },
        {
            label: LANG.UI_VM_OS_TYPE,
            field: 'os_type',
            value: [
                {
                    id: 'linux',
                    value: CONF.OS_TYPE.LINUX,
                    text: 'Linux'
                },
                {
                    id: 'windows',
                    value: CONF.OS_TYPE.WINDOWS,
                    text: 'Windows'
                }
            ]
        },
        {
            label: LANG.UI_REPORT_STATUS,
            field: 'plugin_deploy_status',
            value: [
                {
                    // 在线部署中
                    id: 'online_deploying',
                    value:  CONF.CLIENT_STATUS_TYPE.ONLINE_DEPLOYING,
                    text: LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE_OFF,
                    tag: true,
                    type: 'success'
                },
                {
                    // 离线部署中
                    id: 'offline_deploying',
                    value: CONF.CLIENT_STATUS_TYPE.OFFLINE_DEPLOYING,
                    text: LANG.UI_VOL_CDP_JOB_DETAILS_OFF_LINEING,
                    tag: true,
                    type: 'secondary'
                },
                {
                    // 在线部署成功
                    id: 'online_deploy_success',
                    value:  CONF.CLIENT_STATUS_TYPE.ONLINE_DEPLOY_SUCCESS,
                    text: LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE_SUCCESS,
                    tag: true,
                    type: 'success'
                },
                {
                    // 离线部署成功
                    id: 'offline_deploy_success',
                    value:  CONF.CLIENT_STATUS_TYPE.OFFLINE_DEPLOY_SUCCESS,
                    text: LANG.UI_VOL_CDP_JOB_DETAILS_SUCCESS,
                    tag: true,
                    type: 'secondary'
                },
                {
                    // 离线部署失败
                    id: 'offline_deploy_failed',
                    value:  CONF.CLIENT_STATUS_TYPE.OFFLINE_DEPLOY_FAILED,
                    text: LANG.UI_VOL_CDP_JOB_DETAILS_FAIL,
                    tag: true,
                    type: 'secondary'
                },
                {
                    // 在线升级中
                    id: 'online_upgrading',
                    value:  CONF.CLIENT_STATUS_TYPE.ONLINE_UPGRADING,
                    text: LANG.UI_CLIENT_STATUS_ONLINE_UPGRADING,
                    tag: true,
                    type: 'success'
                },
                {
                    // 离线升级中
                    id: 'offline_upgrading',
                    value:  CONF.CLIENT_STATUS_TYPE.OFFLINE_UPGRADING,
                    text: LANG.UI_CLIENT_STATUS_OFFLINE_UPGRADING,
                    tag: true,
                    type: 'secondary'
                },
                {
                    // 在线升级成功
                    id: 'online_upgrade_success',
                    value:  CONF.CLIENT_STATUS_TYPE.ONLINE_UPGRADE_SUCCESS,
                    text: LANG.UI_CLIENT_STATUS_ONLINE_UPGRADE_SUCCESS,
                    tag: true,
                    type: 'success'
                },
                {
                    // 离线升级成功
                    id: 'offline_upgrade_success',
                    value:  CONF.CLIENT_STATUS_TYPE.OFFLINE_UPGRADE_SUCCESS,
                    text: LANG.UI_CLIENT_STATUS_OFFLINE_UPGRADE_SUCCESS,
                    tag: true,
                    type: 'secondary'
                },
                {
                    // 在线升级失败
                    id: 'online_upgrade_failed',
                    value:  CONF.CLIENT_STATUS_TYPE.ONLINE_UPGRADE_FAILED,
                    text: LANG.UI_CLIENT_STATUS_ONLINE_UPGRADE_FAILED,
                    tag: true,
                    type: 'danger'
                },
                {
                    // 离线升级失败
                    id: 'offline_upgrade_failed',
                    value:  CONF.CLIENT_STATUS_TYPE.OFFLINE_UPGRADE_FAILED,
                    text: LANG.UI_CLIENT_STATUS_OFFLINE_UPGRADE_FAILED,
                    tag: true,
                    type: 'secondary'
                },
                {
                    // 在线AccessKey无效
                    id: 'online_access_key_invalid',
                    value:  CONF.CLIENT_STATUS_TYPE.ONLINE_ACCESSKEY_INVALID,
                    text: LANG.UI_CLIENT_STATUS_ONLINE_ACCESSKEY_INVALID,
                    tag: true,
                    type: 'danger'
                },
                {
                    // 离线AccessKey无效
                    id: 'offline_access_key_invalid',
                    value:  CONF.CLIENT_STATUS_TYPE.OFFLINE_ACCESSKEY_INVALID,
                    text: LANG.UI_CLIENT_STATUS_OFFLINE_ACCESSKEY_INVALID,
                    tag: true,
                    type: 'secondary'
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
    let taskRunningChart = null;
    let FILTER_PARAMS = {}; // 过滤器组件选择的过滤选项参数
    let startTime = '', endTime = '', searchVal = '';

    // <------------------------- BEGIN OVERFIEW DATA ----------------------------------->

    /**
     * 获取客户端报表概览数据
     */
    const initOverviewData = () => {
        pAjaxRequest({}, '/api/v1/report/template/overview/2', 'get', function (res) {
            try {
                if (res.success) {
                    let overviewList = res.data.overviewList;

                    $('#client_total').text(overviewList.host_number);
                    $('#client_online').text(overviewList.online_host);
                    $('#client_offline').text(overviewList.offline_host);
                    $('#client_protected').text(overviewList.protected_client);
                    $('#client_unprotected').text(overviewList.unprotected_client);
                    $('#client_backup_data').text(!overviewList.backup_data ? 0 : unitConver(Number(overviewList.backup_data)).size);
                    $('#client_backup_data_unit').text(!overviewList.backup_data ? 'B' : unitConver(Number(overviewList.backup_data)).unit);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_CLIENT_REPORT_OVERVIEW_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_CLIENT_REPORT_OVERVIEW_DATA_ERROR);
            }
        });
    }

    /**
     * 获取客户端报表详情数据
     */
    const getReportDetail = () => {
        pAjaxRequest({}, '/api/v1/report/template/detail/' + CLIENT_REPORT_TEMPLATE_UUID, 'get', (res) => {
            try {
                Metronic.blockUI({target: ".report-detail__content",animate: true});
                if (res.success) {
                    let { custom_field, overview, running_tendency } = { ...res.data };

                    // 渲染概览数据
                    if (!overview.host_number && !overview.online_host && !overview.offline_host && !overview.protected_client && !overview.unprotected_number && !overview.backup_data) {
                        $('.overview-header').addClass('display-none');
                        $('.report-detail-boxes').addClass('display-none');
                    } else { // 只要配置了一项就显示数据概览
                        $('.overview-header').removeClass('display-none');
                        $('.report-detail-boxes').removeClass('display-none');

                        overview.host_number ? $('.client-total-box').removeClass('display-none') : $('.client-total-box').hide();
                        overview.online_host ? $('.client-online-box').removeClass('display-none') : $('.client-online-box').hide();
                        overview.offline_host ?  $('.client-offline-box').removeClass('display-none') : $('.client-offline-box').hide();
                        overview.protected_client ? $('.client-protected-box').removeClass('display-none') : $('.client-protected-box').hide();
                        overview.unprotected_number ? $('.client-unprotected-box').removeClass('display-none') : $('.client-unprotected-box').hide();
                        overview.backup_data ? $('.client-backup-data-box').removeClass('display-none') : $('.client-backup-data-box').hide();

                        initOverviewData();
                    }

                    // 渲染任务运行趋势echart图
                    if (running_tendency.history) {
                        $('.echart-header').removeClass('display-none');
                        $('.echart-wrapper').removeClass('display-none');

                        getClientReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
                    } else {
                        $('.echart-header').addClass('display-none');
                        $('.echart-wrapper').addClass('display-none');
                    }

                    customField = { ...custom_field };

                    // 获取过滤器选项数组
                    let fields = [];
                    if (customField.module_type) fields.push('timing_data_protect'); // 定时数据保护
                    if (customField.module_type) fields.push('real_time_data_protect'); // 实时数据保护
                    if (customField.module_type) fields.push('data_copy'); // 数据复制
                    if (customField.os_type) fields.push('os_type');
                    if (customField.online) fields.push('plugin_deploy_status');
                    if (customField.protect_status) fields.push('protect_status');

                    if (fields.length > 0) {
                        if (customField.module_type) { // 报表勾选了对象类型，则先将对象类型进行过滤，获得已授权的对象类型
                            // 对过滤器选项数组前三列，即定时备份、实时备份和数据复制列进行过滤
                            let filteredOptions = CLIENT_REPORT_TABLE_FILTER_OPTIONS.slice(0, 3).filter(item => {
                                // 对每个 item 的 value 进行过滤
                                const filteredValues = item.value.filter(val => CONF.PERMISSION.includes(val.id));
                                
                                // 如果过滤后的 value 数组不为空，则更新原对象的 value 并保留该对象
                                if (filteredValues.length > 0) {
                                    item.value = filteredValues;
                                    return true;
                                }
                                return false; // 如果过滤后没有元素，则不保留该项
                            });

                            // 将前三项替换为过滤后的结果
                            CLIENT_REPORT_TABLE_FILTER_OPTIONS.splice(0, 3, ...filteredOptions);
                        }

                        let filterData = CLIENT_REPORT_TABLE_FILTER_OPTIONS.filter(option => {
                            return fields.includes(option.field);
                        });
        
                        // 渲染数据明细表格过滤器
                        initClientTableFilter(filterData);
                    }

                    // 定制数据选了创建/修改时间则渲染时间选择器
                    if (customField.add_time) {
                        initClientTableDaterangePicker();
                    }

                    // 渲染数据明细表格
                    initClientReportDetailsTable();

                } else {
                    UIToastr.showWarning(LANG.UI_GET_CLIENT_REPORT_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_CLIENT_REPORT_DATA_ERROR);
            } finally {
                Metronic.unblockUI('.report-detail__content');
            }
        });
    }

    /**
     * 报表导出
     */
    const reportOverviewExport = () => {
        $('#client_export').attr('data-bs-indicator', 'on');

        // 使用按钮防抖1.5秒执行一次导出
        debounce(captureScrollAndGeneratePDF($('.report-detail__content').get(0), 1500, 'client-report.pdf',  LANG.UI_VINCHIN_REPORT_WATER_NAME, function() {
            $('#client_export').removeAttr('data-bs-indicator');
        }), 1500);
    }

    // <------------------------- END OVERFIEW DATA ------------------------------------->


    // <------------------------- BEGIN ECHART DATA ------------------------------------->

    /**
     * 初始化客户端报表echart
     * @param {*} xData 
     * @param {*} yData 
     * @param {*} onlyData 
     * @param {*} echartId 
     */
    const initClientReportEchart = (xData, yData, onlyData, echartId, unit) => {
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
     * 获取客户端任务运行数据
     * @param {*} params 
     */
    const getClientReportTaskRunningData = (params) => {
        Metronic.blockUI({target: ".tab-content__echart",animate: true});
        
        pAjaxRequest(params, '/api/v1/report/template/tendency', 'get', (res) => {
            try {
                if (res.success) {
                    let xData = [], yData = [];
                    let maxValue = 0;
                    if (res.data.every(i => i.value === 0)) {
                        $(`#client_task_running_chart`).addClass('display-none');
                        $(`#client_task_running_chart`).siblings().removeClass('display-none');
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

                        $(`#client_task_running_chart`).removeClass('display-none');
                        $(`#client_task_running_chart`).siblings().addClass('display-none');

                        initClientReportEchart(xData, yData, onlyData, 'client_task_running_chart', unit);
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

            $('#client_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            taskRunningChart.resize();
        }

        //  监听左侧菜单导航伸缩/展开触发的echart-resize事件
        $(window).on('echart-resize', function () {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                let chartWidth = $('.charts-wrapper .tab-content').width();
                let chartHeight = $('.charts-wrapper .tab-content').height();

                $('#client_task_running_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                taskRunningChart.resize();
            }, 300);
        });
    }

    // <------------------------- END ECHART DATA --------------------------------------->


    // <------------------------- BEGIN TABLE DATA -------------------------------------->

    const getParams = function () {
        let queryParams = {
            search: $('#search').val()
        }
        
        // 合并过滤器搜索参数
        queryParams = Object.assign(queryParams, FILTER_PARAMS);

        return queryParams;
    }

    /**
     * 初始化客户端报表数据明细表格过滤器
     * @param {*} filterData 过滤器数组
     */
    const initClientTableFilter = (filterData) => {
        if ($('#client_report_filter_btn').length === 0) {
            $('#client_report_filter_wrapper').initFilter({
                filterSlotId: 'client_report_filter_wrapper',
                filterBtnId: 'client_report_filter_btn',
                filters: filterData
            });
        } else {
            $('#client_report_filter_wrapper').resetFilter({
                filterBtnId: 'client_report_filter_btn',
                filters: filterData
            });
        }
    }

    const initClientTableDaterangePicker = () => {
        $('#client_report_daterangepicker_wrapper').initDateRangePicker({
            slotId: 'client_report_daterangepicker_wrapper', // 日期范围组件在父组件插槽位置的id
            dateRangePickerId: 'client_datepicker', // 选择器button id
            startTime: '', // 开始时间
            endTime: '', // 结束时间
            maxDate: 'now', // 最大可用时间
            timePicker: true, // 是否显示时间,时分
            timePickerSeconds: true, // 是否显示秒
            timePicker24Hour: true, // 是否是24小时制
            alwaysShowCalendars: true, // 是否总是显示日期选择
        });
    }

    /**
     * 初始化客户端数据明细表格
     */
    const initClientReportDetailsTable = () => {
        try {
            Metronic.blockUI({target: ".data-detail-wrapper",animate: true});
            if ($('#client_report_table').children().length === 0) { // 表格未渲染
                let options = {
                    vin_url: "/api/v1/report/template/agent_data_details",
                    vin_method: "GET",
                    toolbarId: '#vin_current_toolbar',
                    vin_toolbar: '.vin_toolbar',
                    pagination: true, //分页
                    resizable: true, //可变宽度
                    showColumns: false,
                    filterBtnId: 'client_report_filter_btn', // 过滤器组件button id
                    dateRangePickerId: 'client_datepicker', // 日期选择器组件button id
                    vin_params: function () {
                        let params = {};
                        params = $.extend(params, getParams());
                        return params;
                    },
                    exportSettings: {
                        showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
                        custom: [
                            {
                                label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
                                class: 'export-all-excel' 
                            }
                        ]
                    },
                    onPostBody: function() {
                        let tableData = $('#client_report_table').bootstrapTable('getData');
    
                        if (tableData.length === 0) {
                            $('.data-detail-wrapper').addClass('nodata');
                        } else {
                            $('.data-detail-wrapper').removeClass('nodata');
                        }

                        const exportOptions = {
                            toolbarId: 'vin_current_toolbar',
                            url: '/api/v1/report/client_export',
                            fileName: LANG.UI_CLIENT_REPORT_DATA_DETAIL
                        }

                        // 监听导出全部数据
                        exportAllTableData(exportOptions);
                    },
                    columns: [
                        {
                            field: 'name',
                            title: LANG.UI_BACKUP_FILE_HOSTNAME,
                            sortable: false,
                            align: 'center',
                            visible: customField.name,
                            formatter: function(index,row) {
                                let client_name = row.hostname + '/' + row.name
                                return client_name
                            }
                        },
                        {
                            field: 'ip',
                            title: LANG.UI_NODE_NETWORK_TABLE_IP,
                            sortable: true,
                            align: 'center',
                            visible: customField.ip
                        },
                        {
                            field: 'os_version',
                            title: LANG.UI_CLIENT_OS,
                            sortable: true,
                            align: 'center',
                            visible: customField.os_type
                        },
                        {
                            field: 'module_type',
                            title: LANG.UI_SEARCH_OBJ_TYPE,
                            sortable: false,
                            align: 'center',
                            visible: customField.module_type,
                            formatter: function (value, row) {
                                return `${row.module_type_des}`;
                            }
                        },
                        {
                            field: 'user',
                            title: LANG.UI_CLIENT_OWNER,
                            sortable: false,
                            align: 'center',
                            visible: customField.user,
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
                            field: 'online',
                            title: LANG.UI_MICROSOFT365_ONLINE_FLAG,
                            sortable: true,
                            align: 'center',
                            visible: customField.online,
                            formatter: function(index,row) {
                                switch (row.online) {
                                    case 2: //离线
                                        if (row.plugin_deploy_status == 1) { //离线部署中
                                            return '<span class="label label-default">' + LANG.UI_VOL_CDP_JOB_DETAILS_OFF_LINEING + '</span>';
                                        }
                                        if (row.plugin_deploy_status == 2) { //离线部署成功
                                            return '<span class="label label-default" >' + LANG.UI_VOL_CDP_JOB_DETAILS_SUCCESS + '</span>';
                                        }
                                        if (row.plugin_deploy_status == 3) { //离线部署失败
                                            return '<span class="label label-default" >' + LANG.UI_VOL_CDP_JOB_DETAILS_FAIL + '</span>';
                                        }
                                        if (row.plugin_deploy_status == 4) { // 离线升级中
                                            return '<span class="label label-default" >' + LANG.UI_CLIENT_STATUS_OFFLINE_UPGRADING + '</span>';
                                        }
                                        if (row.plugin_deploy_status == 5) { // 离线升级成功
                                            return '<span class="label label-default" >' + LANG.UI_CLIENT_STATUS_OFFLINE_UPGRADE_SUCCESS + '</span>';
                                        }
                                        if (row.plugin_deploy_status == 6) { // 离线升级失败
                                            return '<span class="label label-default" >' + LANG.UI_CLIENT_STATUS_OFFLINE_UPGRADE_FAILED + '</span>';
                                        }
                                        if (row.plugin_deploy_status == 7) { // 离线AccessKey失效
                                            return '<span class="label label-default" >' + LANG.UI_CLIENT_STATUS_OFFLINE_ACCESSKEY_INVALID + '</span>';
                                        }

                                        break;
                                    case 1: //在线
                                        if (row.plugin_deploy_status == 1) { //在线部署中
                                            return '<span class="label label-success">'+LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE_OFF+'</span>';
                                        }
                                        if (row.plugin_deploy_status == 2) { //在线部署成功
                                            return '<span class="label label-success" >'+LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE_SUCCESS+'</span>';
                                        }
                                        if (row.plugin_deploy_status == 4) { // 在线升级中
                                            return '<span class="label label-success" >' + LANG.UI_CLIENT_STATUS_ONLINE_UPGRADING + '</span>';
                                        }
                                        if (row.plugin_deploy_status == 5) { // 在线升级成功
                                            return '<span class="label label-success" >' + LANG.UI_CLIENT_STATUS_ONLINE_UPGRADE_SUCCESS + '</span>';
                                        }
                                        if (row.plugin_deploy_status == 6) { // 在线升级失败
                                            return '<span class="label label-danger" >' + LANG.UI_CLIENT_STATUS_ONLINE_UPGRADE_FAILED + '</span>';
                                        }
                                        if (row.plugin_deploy_status == 7) { // 在线AccessKey失效
                                            return '<span class="label label-danger" >' + LANG.UI_CLIENT_STATUS_ONLINE_ACCESSKEY_INVALID + '</span>';
                                        }

                                        break;
                                    default:
                                        break;
                                }
                            }
                        },
                        {
                            field: 'add_time',
                            title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
                            sortable: true,
                            align: 'center',
                            visible: customField.add_time,
                        },
                        {
                            field: 'full_backup_number',
                            title: LANG.UI_REPORT_FULL_BACKUP,
                            sortable: false,
                            align: 'center',
                            visible: customField.full_backup_number
                        },
                        {
                            field: 'incre_backup_number',
                            title: LANG.UI_REPORT_INCRE_BACKUP,
                            sortable: false,
                            align: 'center',
                            visible: customField.incre_backup_number
                        },
                        {
                            field: 'archived_log_number',
                            title: LANG.UI_DATA_TYPE_ARCHIVELOG_NUM,
                            sortable: false,
                            align: 'center',
                            visible: customField.archived_log_number
                        },
                        {
                            field: 'dif_backup_number',
                            title: LANG.UI_REPORT_DIFF_BACKUP,
                            sortable: false,
                            align: 'center',
                            visible: customField.dif_backup_number
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
                            title: LANG.UI_SEARCH_BACKUP_TASK,
                            sortable: false,
                            align: 'center',
                            visible: customField.task,
                        },
                        {
                            field: 'backup_data',
                            title: LANG.UI_GRAIN_JOB_TOTAL_SIZE,
                            sortable: false,
                            align: 'center',
                            visible: customField.backup_data,
                        },
                        {
                            field: 'total_object_valid_size',
                            title: LANG.UI_PUBLIC_VM_VALID_SIZE,
                            sortable: false,
                            align: 'center',
                            visible: customField.total_object_valid_size,
                        },
                        {
                            field: 'total_object_write_size',
                            title: LANG.UI_DB_WRITE_SIZE,
                            sortable: false,
                            align: 'center',
                            visible: customField.total_object_write_size,
                        }
                    ],
                }

                $('#client_report_table').baseTableConfig().init(options);
            } else { // 表格已渲染直接刷新
                $('#client_report_table').bootstrapTable('refresh');
            }
        } catch (error) {
            
        } finally {
            Metronic.unblockUI('.data-detail-wrapper');
        }
    }

    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';
        CLIENT_REPORT_TEMPLATE_UUID = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存报表uuid

        if (CLIENT_REPORT_TEMPLATE_UUID) {
            getReportDetail();
        }
    }

    // <------------------------- END TABLE DATA ---------------------------------------->


    const handleObjectTypeFilterParams = (filterData) => {
        let checkedFilterParams = { module_type: [], sub_module_type: [], storage_location: [], dev_type: [] };
        let moduleTypeArr = [], subModuleTypeArr = [], storageLocationArr = [], devTypeArr = [];

        const timingProtectData = filterData.find(i => i.key === "timing_data_protect")?.value || []; // 定时保护勾选数据
        const realTimeProtectData = filterData.find(i => i.key === "real_time_data_protect")?.value || []; // 实时保护勾选数据
        const dataCopyData = filterData.find(i => i.key === "data_copy")?.value || []; // 数据复制勾选数据

        // 勾选了定时保护或实时保护或数据复制时，处理 module_type 和 sub_module_type
        if (!timingProtectData.length && !realTimeProtectData.length && !dataCopyData.length) {
            checkedFilterParams = { module_type: [], sub_module_type: [], storage_location: [], dev_type: [] };
        } else {
            if (timingProtectData.length > 0) { // 勾选了定时保护列的数据
                timingProtectData.forEach(item => {
                    const itemData = item.split('-');

                    // 定时备份模块不含子模块的模块，sub_module_type默认给0，所以length总是2
                    moduleTypeArr.push(itemData[0]);
                    subModuleTypeArr.push(itemData[1]);
                });
            }

            if (realTimeProtectData.length > 0) { // 勾选了实时保护列的数据，split分隔后，第一个元素表示 module_type,第二个元素表示storage_location（1：备份 3：复制），第三个元素表示 dev_type（1：卷 2：磁盘）
                realTimeProtectData.forEach(item => {
                    const itemData = item.split('-');

                    moduleTypeArr.push(itemData[0]);
                    storageLocationArr.push(itemData[1]);
                    devTypeArr.push(itemData[2]);
                });
            }

            if (dataCopyData.length > 0) { // 勾选了数据复制列的数据
                dataCopyData.forEach(item => {
                    const itemData = item.split('-');

                    if (itemData.length === 1) { // 勾选的文件复制或数据库复制
                        moduleTypeArr.push(itemData[0]);
                    } else { // 勾选的整机或卷时：split分隔后，第一个元素表示 module_type,第二个元素表示storage_location（1：备份 3：复制），第三个元素表示 dev_type（1：卷 2：磁盘）
                        moduleTypeArr.push(itemData[0]);
                        storageLocationArr.push(itemData[1]);
                        devTypeArr.push(itemData[2]);
                    }
                });
            }

            checkedFilterParams = { 
                module_type: moduleTypeArr.join(','), 
                sub_module_type: subModuleTypeArr.join(','), 
                storage_location: storageLocationArr.join(','), 
                dev_type: devTypeArr.filter(i => i !== undefined).join(',') 
            };
        }

        return checkedFilterParams;
    }

    const initListener = () => {

        // 监听时间类型选择change
        $('#task_running_time_select').on('change', () => {
            CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.timeInterval = $('#task_running_time_select').val();
            getClientReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS);
        });

        // 搜索模版名称
        $('#report_search').off().on('click', () => {
            searchVal = $('#search').val();
            FILTER_PARAMS.search = searchVal;

            if (FILTER_PARAMS.search) {
                $('#client_report_table').bootstrapTable('refresh', {query: {...FILTER_PARAMS}})
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
                    $('#client_report_table').bootstrapTable('refresh', {query: {...FILTER_PARAMS}})
                }
            }
        });

        // 清空搜索
        $('#report_clear_search').on('click', () => {
            $('#search').val('');
            searchVal = '';
            FILTER_PARAMS.search = '';
            $('#report_clear_search').addClass('hide');
            $('#client_report_table').bootstrapTable('refresh', {query: {...FILTER_PARAMS}})
        });

        // 监听导出
        $('#client_export').on('click', reportOverviewExport);

        // 移除所有旧的过滤器监听
        window.$off('client_report_filter_btn-updateFilterEvent');

        // 监听数据明细 - 过滤器组件派发的数据，以更新表格
        window.$on('client_report_filter_btn-updateFilterEvent', (filterData) => {
            if (filterData.length > 0) {
                let objTypeList = {};
                if (customField.module_type) {
                    objTypeList = handleObjectTypeFilterParams(filterData);
                }

                FILTER_PARAMS = Object.assign(FILTER_PARAMS, objTypeList);

                let osTypeList = getTableFilterParams('os_type', filterData);
                let pluginDeployStatusList = getTableFilterParams('plugin_deploy_status', filterData);
                let backupStatusList = getTableFilterParams('protect_status', filterData);

                if (customField.os_type && osTypeList.value.length > 0) {
                    FILTER_PARAMS.os_type = osTypeList.value.join(',');
                } else {
                    FILTER_PARAMS.os_type = '';
                }

                if (customField.online && pluginDeployStatusList.value.length > 0) {
                    let selectedOnlineList = [];
                    let selectedDeployStatusList = new Set(); // 用 set 去重收集部署状态
                    pluginDeployStatusList.value.forEach(item => {
                        let onlineStatus = Number(item.split('')[0]);
                        let deployStatus = Number(item.split('')[1]);

                        // online_flag  在线或离线 仅push一次
                        if ((onlineStatus === 1 && selectedOnlineList.indexOf(1) === -1) || (onlineStatus === 2 && selectedOnlineList.indexOf(2) === -1)) {
                            selectedOnlineList.push(onlineStatus);
                        }

                        selectedDeployStatusList.add(deployStatus);
                    });

                    FILTER_PARAMS.online = selectedOnlineList.join(',');
                    FILTER_PARAMS.plugin_deploy_status = Array.from(selectedDeployStatusList).join(','); // 将 Set 转换为数组;
                } else {
                    FILTER_PARAMS.online = '';
                    FILTER_PARAMS.plugin_deploy_status = '';
                }

                if (customField.protect_status && backupStatusList.value.length > 0) {
                    FILTER_PARAMS.protect_status = backupStatusList.value.join(',');
                } else {
                    FILTER_PARAMS.protect_status = '';
                }
            } else {
                FILTER_PARAMS.module_type = '';
                FILTER_PARAMS.sub_module_type = '';
                FILTER_PARAMS.storage_location = '';
                FILTER_PARAMS.dev_type = '';
                FILTER_PARAMS.os_type = '';
                FILTER_PARAMS.online = '';
                FILTER_PARAMS.plugin_deploy_status = '';
                FILTER_PARAMS.protect_status = '';
            }

            FILTER_PARAMS.search = searchVal;
            FILTER_PARAMS.start_time = startTime;
            FILTER_PARAMS.end_time = endTime;

            $('#client_report_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } });
        });

        window.$off('client_datepicker-updateDateRangeEvent');

        // 监听数据明细 - 日期范围选择组件派发的数据，以更新表格
        window.$on('client_datepicker-updateDateRangeEvent', (data) => {
            // 记录选择的开始时间和结束时间，用于过滤搜索的联动
            startTime = data.startTime;
            endTime = data.endTime;

            FILTER_PARAMS = Object.assign({}, FILTER_PARAMS, { start_time: startTime, end_time: endTime });

            $('#client_report_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } });
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
    clientDetail.init();
});
