var storageDetail = (function() {
    const TASK_RUNNING_STATUS_TYPE = {
        BACKUP: 1,
        COPY: 2,
        ARCHIVE: 3
    }; // 任务运行状态类型
    const STATUS_TYPE_TO_ECHART_ID_MAP = {
        1: 'storage_report_backup_chart',
        2: 'storage_report_copy_chart',
        3: 'storage_report_archive_chart'
    }; // 任务运行趋势状态对应echart id
    let NAS_REPORT_TEMPLATE_UUID = '';
    let CURRENT_TASK_RUNNING_ECHART_ID = 'storage_report_backup_chart';
    let CURRENT_RUNNING_TENDENCY_QUERY_PARAMS = {
        type: CONF.REPORT_TYPE.STORAGE,
        timeInterval: CONF.RUNNING_TIME_TYPE.LAST_MONTH,
        statusType: TASK_RUNNING_STATUS_TYPE.BACKUP
    }; // 当前任务运行趋势查询参数对象
    let customField = {};
    let STORAGE_REPORT_TABLE_FILTER_OPTIONS = [
        {
            label: LANG.UI_SEARCH_STORAGE_TYPE,
            field: 'storage_type',
            value: [
                {
                    id: 'storage_disk',
                    value: CONF.BD_STORAGE_TYPE.DISK,
                    text: LANG.UI_STORAGE_TYPE_DISK,
                },
                {
                    id: 'storage_lvm',
                    value: CONF.BD_STORAGE_TYPE.LVM,
                    text: LANG.UI_STORAGE_TYPE_LOGICAL_VOLUME_LVM,
                },
                {
                    id: 'storage_partition',
                    value: CONF.BD_STORAGE_TYPE.PARTITION,
                    text: LANG.UI_STORAGE_TYPE_LOCAL_PARTITION,
                },
                {
                    id: 'storage_fc',
                    value: CONF.BD_STORAGE_TYPE.FC,
                    text: LANG.UI_STORAGE_TYPE_DETAIL_FC,
                },
                {
                    id: 'storage_iscsi',
                    value: CONF.BD_STORAGE_TYPE.ISCSI,
                    text: LANG.UI_STORAGE_TYPE_ISCSI,
                },
                {
                    id: 'storage_nfs',
                    value: CONF.BD_STORAGE_TYPE.NFS,
                    text: LANG.UI_STORAGE_TYPE_NFS,
                },
                {
                    id: 'storage_cifs',
                    value: CONF.BD_STORAGE_TYPE.CIFS,
                    text: LANG.UI_STORAGE_TYPE_CIFS,
                },
                {
                    id: 'storage_remote',
                    value: CONF.BD_STORAGE_TYPE.REMOTE,
                    text: LANG.UI_STORAGE_TYPE_REMOTE,
                },
                {
                    id: 'storage_cloud',
                    value: CONF.BD_STORAGE_TYPE.CLOUD,
                    text: LANG.UI_STORAGE_TYPE_COUND,
                },
                {
                    id: 'storage_tape',
                    value: CONF.BD_STORAGE_TYPE.TAPE,
                    text: LANG.UI_STORAGE_TYPE_TAPE,
                },
                {
                    id: 'storage_localdir',
                    value: CONF.BD_STORAGE_TYPE.LOCALDIR,
                    text: LANG.UI_STORAGE_TYPE_LOCAL_CATALOGUE,
                },
                {
                    id: 'storage_huawei_cbr',
                    value: CONF.BD_STORAGE_TYPE.HUAWEI_CBR,
                    text: LANG.UI_STORAGE_TYPE_HUAWEI_CBR,
                }
            ]
        },
        {
            label: LANG.UI_SEARCH_STORAGE_STATUS,
            field: 'storage_status',
            value: [
                {
                    id: 'storage_status_online',
                    value: CONF.STORAGE_STATUS.ONLINE,
                    text: LANG.UI_NODE_NORMAL,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'storage_status_creating',
                    value: CONF.STORAGE_STATUS.CREATING,
                    text: LANG.UI_PUBLIC_CREATING,
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'storage_status_offline',
                    value: CONF.STORAGE_STATUS.OFFLINE,
                    text: LANG.UI_CLOUD_PLATFORM_OFFLINE,
                    tag: true,
                    type: 'secondary'
                },
                {
                    id: 'storage_status_unmount',
                    value: CONF.STORAGE_STATUS.UNMOUNT,
                    text: LANG.UI_STORAGE_STATUS_UNMOUNT,
                    tag: true,
                    type: 'secondary'
                },
                {
                    id: 'storage_status_warning',
                    value: CONF.STORAGE_STATUS.WARNING,
                    text: LANG.UI_PLATFORM_DES_WARNING,
                    tag: true,
                    type: 'warning'
                },
            ]
        },
        // {
        //     label: LANG.UI_NODE_NODE_STATUS,
        //     field: 'node_status',
        //     value: [
        //         {
        //             id: 'node_status_normal',
        //             value: CONF.REPORT_NODE_STATUS_TYPE.NORMAL,
        //             text: LANG.UI_NODE_NORMAL,
        //             tag: true,
        //             type: 'success'
        //         },
        //         {
        //             id: 'node_status_abnormal',
        //             value: CONF.REPORT_NODE_STATUS_TYPE.ABNORMAL,
        //             text: LANG.UI_NODE_ABNORMAL,
        //             tag: true,
        //             type: 'warning'
        //         },
        //     ]
        // }
    ];
    let filterParams = {
        search: ''
    }; // 过滤器组件选择的过滤选项参数
    let taskRunningChart = null;

    // <------------------------- BEGIN OVERFIEW DATA ----------------------------------->

    const initOverviewData = () => {
        pAjaxRequest({}, '/api/v1/report/template/overview/5', 'get', function (res) {
            try {
                if (res.success) {
                    let overviewList = res.data.overviewList;

                    $('#storage_total').text(overviewList.storage_device);
                    $('#storage_online').text(overviewList.online_device);
                    $('#storage_offline').text(overviewList.offline_device);
                    
                    $('#storage_backup_data').text(!overviewList.backup_data ? 0 : unitConver(Number(overviewList.backup_data)).size);
                    $('#storage_backup_data_unit').text(!overviewList.backup_data ? 'B' : unitConver(Number(overviewList.backup_data)).unit);

                    $('#storage_copy_data').text(!overviewList.copy_data ? 0 : unitConver(Number(overviewList.copy_data)).size);
                    $('#storage_copy_data_unit').text(!overviewList.copy_data ? 'B' : unitConver(Number(overviewList.copy_data)).unit);

                    $('#storage_archive_data').text(!overviewList.archived_data ? 0 : unitConver(Number(overviewList.archived_data)).size);
                    $('#storage_archive_data_unit').text(!overviewList.archived_data ? 'B' : unitConver(Number(overviewList.archived_data)).unit);
                } else {
                    UIToastr.showWarning(LANG.UI_GET_STORAGE_REPORT_OVERVIEW_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_STORAGE_REPORT_OVERVIEW_DATA_ERROR);
            }
        });
    }

    const getReportDetail = () => {
        pAjaxRequest({}, '/api/v1/report/template/detail/' + NAS_REPORT_TEMPLATE_UUID, 'get', (res) => {
            try {
                Metronic.blockUI({target: ".report-detail__content",animate: true});
                
                if (res.success) {
                    let { custom_field, overview, running_tendency } = { ...res.data };

                    // 渲染概览数据
                    if (!overview.storage_device && !overview.online_number && !overview.offline_number && !overview.backup_data && !overview.copy_data && !overview.archived_data) {
                        $('.overview-header').addClass('display-none');
                        $('.report-detail-boxes').addClass('display-none');
                    } else { // 只要配置了一项就显示数据概览
                        $('.overview-header').removeClass('display-none');
                        $('.report-detail-boxes').removeClass('display-none');

                        overview.storage_device ? $('.storage-total-box').removeClass('display-none') : $('.storage-total-box').hide();
                        overview.online_number ? $('.storage-online-box').removeClass('display-none') : $('.storage-online-box').hide();
                        overview.offline_number ?  $('.storage-offline-box').removeClass('display-none') : $('.storage-offline-box').hide();
                        overview.backup_data ? $('.storage-backup-data-box').removeClass('display-none') : $('.storage-backup-data-box').hide();
                        overview.copy_data ? $('.storage-copy-data-box').removeClass('display-none') : $('.storage-copy-data-box').hide();
                        overview.archived_data ? $('.storage-archive-data-box').removeClass('display-none') : $('.storage-archive-data-box').hide();

                        initOverviewData();
                    }

                    // 渲染任务运行趋势echart图
                    if (running_tendency.history) {
                        $('.echart-header').removeClass('display-none');
                        $('.echart-wrapper').removeClass('display-none');

                        getStorageReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS, CURRENT_TASK_RUNNING_ECHART_ID);
                    } else {
                        $('.echart-header').addClass('display-none');
                        $('.echart-wrapper').addClass('display-none');
                    }

                    customField = { ...custom_field };

                    // 获取过滤器选项数组
                    let fields = [];
                    if (customField.type) fields.push('storage_type');
                    if (customField.storage_status) fields.push('storage_status');
                    // if (customField.node_status) fields.push('node_status');

                    if (fields.length > 0) {
                        let filterData = STORAGE_REPORT_TABLE_FILTER_OPTIONS.filter(option => {
                            return fields.includes(option.field);
                        });
        
                        // 渲染数据明细表格过滤器
                        initStorageTableFilter(filterData);
                    }

                    // 渲染数据明细表格
                    initStorageReportDetailsTable();

                } else {
                    UIToastr.showWarning(LANG.UI_GET_STORAGE_REPORT_DATA_ERROR);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_STORAGE_REPORT_DATA_ERROR);
            } finally {
                Metronic.unblockUI('.report-detail__content');
            }
        });
    }

    // <------------------------- END OVERFIEW DATA ------------------------------------->

    // <------------------------- BEGIN ECHART DATA ------------------------------------->

    /**
     * 初始化存储任务运行报表echart
     * @param {*} xData 
     * @param {*} yData 
     * @param {*} onlyData 
     * @param {*} echartId 
     * @param {*} unit
     */
    const initStorageReportEchart = (xData, yData, onlyData, echartId, unit) => {
        if ($(`#${echartId}`).children().length > 0) {
            // 销毁上一个echart
            echarts.dispose(document.getElementById(echartId));
        }

        let chartWidth = $('.charts-wrapper .tab-content .tab-pane.active').width();
        let chartHeight = $('.charts-wrapper .tab-content .tab-pane.active').height();

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
     * 获取存储报表任务运行趋势数据
     * @param {*} params 
     * @param {*} echartId 
     */
    const getStorageReportTaskRunningData = (params, echartId) => {
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
    
                        initStorageReportEchart(xData, yData, onlyData, echartId, unit);
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
     * 初始化存储报表数据明细表格过滤器
     * @param {*} filterData 
     */
    const initStorageTableFilter = (filterData) => {
        if ($('#storage_report_filter_btn').length === 0) {
            $('#storage_report_filter_wrapper').initFilter({
                filterSlotId: 'storage_report_filter_wrapper',
                filterBtnId: 'storage_report_filter_btn',
                filters: filterData
            });
        } else {
            $('#storage_report_filter_wrapper').resetFilter({
                filterBtnId: 'storage_report_filter_btn',
                filters: filterData
            });
        }
    }

    const initStorageReportDetailsTable = () => {
        if ($('#storage_report_table').children().length === 0) { // 表格为渲染
            let options = {
                vin_url: "/api/v1/report/template/storage_data_details",
                vin_method: "GET",
                vin_toolbar: '.vin_toolbar',
                pagination: true, //分页
                resizable: true, //可变宽度
                showColumns: false,
                onPostBody: function() {
                    let tableData = $('#storage_report_table').bootstrapTable('getData');

                    if (tableData.length === 0) {
                        $('.data-detail-wrapper').addClass('nodata');
                    } else {
                        $('.data-detail-wrapper').removeClass('nodata');
                    }
                },
                columns: [
                    {
                        field: 'name',
                        title: LANG.UI_REPORT_STORAGE_NAME,
                        sortable: false,
                        align: 'center',
                        visible: customField.name
                    },
                    {
                        field: 'type',
                        title: LANG.UI_SEARCH_STORAGE_TYPE,
                        sortable: true,
                        align: 'center',
                        visible: customField.type
                    },
                    {
                        field: 'total_capacity',
                        title: LANG.UI_OS_PLUG_TOTAL_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.total_capacity,
                    },
                    {
                        field: 'available_capacity',
                        title: LANG.UI_PUBLIC_FREE_STORAGE_SIZE,
                        sortable: true,
                        align: 'center',
                        visible: customField.available_capacity,
                    },
                    {
                        field: 'used_capacity',
                        title: LANG. UI_HOMEPAGE_USED_STORAGE,
                        sortable: true,
                        align: 'center',
                        visible: customField.used_capacity
                    },
                    {
                        field: 'storage_status',
                        title: LANG.UI_SEARCH_STORAGE_STATUS,
                        sortable: true,
                        align: 'center',
                        visible: customField.storage_status,
                        formatter: function (value) {
                            if(value) {
                                return `<span class="label label-success status-icon">${LANG.UI_NODE_NORMAL}</span>`;
                             } else {
                                return `<span class="label label-default">${LANG.UI_NODE_ABNORMAL}</span>`;
                            }
                         }
                    },
                    {
                        field: 'node',
                        title: LANG.UI_PUBLIC_STORAGE_IN_NODE,
                        sortable: false,
                        align: 'center',
                        visible: customField.node,
                    },
                    {
                        field: 'node_status',
                        title: LANG.UI_NODE_NODE_STATUS,
                        sortable: true,
                        align: 'center',
                        visible: customField.node_status,
                        formatter: function (value) {
                           if(value === 1) {
                               return `<span class="label label-sm label-success status-icon">${LANG.UI_NODE_NORMAL}</span>`;
                            } else {
                               return `<span class="label label-sm label-default">${LANG.UI_NODE_ABNORMAL}</span>`;
                           }
                        }
                    }
                ],
            }
            $('#storage_report_table').baseTableConfig().init(options);
        } else { // 表格已渲染直接刷新
            $('#storage_report_table').bootstrapTable('refresh');
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
     * 报表概览导出
     */
    const reportOverviewExport = () => {
        $('#storage_export').attr('data-bs-indicator', 'on');

        // 使用按钮防抖1.5秒执行一次导出
        debounce(captureScrollAndGeneratePDF($('.report-detail__content').get(0), 1500, 'storage-report.pdf',  LANG.UI_VINCHIN_REPORT_WATER_NAME, function() {
            $('#storage_export').removeAttr('data-bs-indicator');
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

                getStorageReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS, CURRENT_TASK_RUNNING_ECHART_ID);
            }
        });

        // 监听时间类型选择change
        $('#task_running_time_select').on('change', () => {
            CURRENT_RUNNING_TENDENCY_QUERY_PARAMS.timeInterval = $('#task_running_time_select').val();
            getStorageReportTaskRunningData(CURRENT_RUNNING_TENDENCY_QUERY_PARAMS, CURRENT_TASK_RUNNING_ECHART_ID);
        });

        // 搜索模版名称
        $('#report_search').off().on('click', () => {
            filterParams.search = $('#search').val();

            if (filterParams.search) {
                $('#storage_report_table').bootstrapTable('refresh', {query: {...filterParams}})
            }
        });

         $('#search').keypress(function (e) {
            if (e.which == 13) {
                searchVal = $('#search').val();
                filterParams.search = searchVal;

                $('#storage_report_table').bootstrapTable('refresh', { query: { ...filterParams }});
            }
        });

        $('#search').on('focus', () => {
            $('#report_clear_search').removeClass('hide');
        });

        // 清空搜索
        $('#report_clear_search').on('click', () => {
            $('#search').val('');
            filterParams.search = '';
            $('#report_clear_search').addClass('hide');
            $('#storage_report_table').bootstrapTable('refresh', {query: {...filterParams}})
        });

        // 监听导出
        $('#storage_export').on('click', reportOverviewExport);

        window.$off('storage_report_filter_btn-updateFilterEvent');

        // 监听数据明细 - 过滤器组件派发的数据，以更新表格
        window.$on('storage_report_filter_btn-updateFilterEvent', (filterData) => {
            if (filterData.length > 0) {

                if (customField.type) {
                    let storageTypeList = getTableFilterParams('storage_type', filterData);

                    
                    if (storageTypeList.value.length > 0) {
                        filterParams.storage_type = storageTypeList.value;
                    } else {
                        filterParams.storage_type = '';
                    }
                }

                if (customField.storage_status) {
                    let storageStatusList = getTableFilterParams('storage_status', filterData);

                    if (storageStatusList.value.length > 0) {
                        filterParams.storage_status = storageStatusList.value;
                    } else {
                        filterParams.storage_status = '';
                    }
                }
            } else {
                filterParams.storage_type = '';
                filterParams.storage_status = '';
            }

            $('#storage_report_table').bootstrapTable('refresh', { query: { ...filterParams } });
        });
    }

    // <------------------------- END TABLE DATA ---------------------------------------->

    /**
     * echart图自适应
     */
    const watchEchartSizeChange = () => {
        window.onresize = function () {
            switch (CURRENT_TASK_RUNNING_ECHART_ID) {
                case 'storage_report_backup_chart':
                    // 有数据时，导航栏折叠/展开时，echart图要重绘
                    if (!$('#storage_report_backup_chart').hasClass('display-none')) {
                        let chartWidth = $('#backup_tab').width();
                        let chartHeight = $('#backup_tab').height();
                        $('#storage_report_backup_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                        taskRunningChart.resize();
                    }
                    break;
                case 'storage_report_copy_chart':
                    // 有数据时，导航栏折叠/展开时，echart图要重绘
                    if (!$('#storage_report_copy_chart').hasClass('display-none')) {
                        let chartWidth = $('#copy_tab').width();
                        let chartHeight = $('#copy_tab').height();
                        $('#storage_report_copy_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                        taskRunningChart.resize();
                    }
                    break;
                case 'storage_report_archive_chart':
                    // 有数据时，导航栏折叠/展开时，echart图要重绘
                    if (!$('#storage_report_archive_chart').hasClass('display-none')) {
                        let chartWidth = $('#archive_tab').width();
                        let chartHeight = $('#archive_tab').height();
                        $('#storage_report_archive_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

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
                    case 'storage_report_backup_chart':
                        // 有数据时，导航栏折叠/展开时，echart图要重绘
                        if (!$('#storage_report_backup_chart').hasClass('display-none')) {
                            let chartWidth = $('#backup_tab').width();
                            let chartHeight = $('#backup_tab').height();
                            $('#storage_report_backup_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                            taskRunningChart.resize();
                        }
                        break;
                    case 'storage_report_copy_chart':
                        // 有数据时，导航栏折叠/展开时，echart图要重绘
                        if (!$('#storage_report_copy_chart').hasClass('display-none')) {
                            let chartWidth = $('#copy_tab').width();
                            let chartHeight = $('#copy_tab').height();
                            $('#storage_report_copy_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

                            taskRunningChart.resize();
                        }
                        break;
                    case 'storage_report_archive_chart':
                        // 有数据时，导航栏折叠/展开时，echart图要重绘
                        if (!$('#storage_report_archive_chart').hasClass('display-none')) {
                            let chartWidth = $('#archive_tab').width();
                            let chartHeight = $('#archive_tab').height();
                            $('#storage_report_archive_chart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

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
    storageDetail.init();
});
