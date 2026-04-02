// 挂起任务js
var CurrentPendingJOb = function () {
    var isInits = false; // 标记本次是否已经初始化过
    var changeHeightFlag = false;
    var accurateFlag = false;
    var queryParams = {};
    let ADVANCED_SEARCH_PARAMS = {}; // 高级搜索查询参数
    let FILTER_PARAMS = {}; // 过滤搜索参数
    var displayFlag = false;
    let startTime = '', endTime = '', searchVal = '';

    const initCurrentJobTableDaterangePicker = () => {
        $('#current_job_pending_daterangepicker_wrapper').initDateRangePicker({
            slotId: 'current_job_pending_daterangepicker_wrapper', // 日期范围组件在父组件插槽位置的id
            dateRangePickerId: 'current_job_pending_datepicker', // 选择器button id
            startTime: '', // 开始时间
            endTime: '', // 结束时间
            maxDate: 'now', // 最大可用时间
            timePicker: true, // 是否显示时间,时分
            timePickerSeconds: true, // 是否显示秒
            timePicker24Hour: true, // 是否是24小时制
            alwaysShowCalendars: true, // 是否总是显示日期选择
        });
    }

    // 点击时间选择器，关闭其他几个下拉菜单
    $('#daterangepickerCurrentJob').on('click', function () {
        $('.addTaskList').hide();
        $('#vin_current_toolbar #filters').removeClass('show');
        $('#vin_current_toolbar .filters').removeClass('filter-active');
        $('#vin_current_toolbar #filters').addClass('bgw');
        displayFlag = false;
    });

    // 过滤器的hover变色
    $('#vin_current_pending_toolbar .filters').hover(
        function () {
            $('#vin_current_pending_toolbar .filters').addClass('filter-hover');
        },
        function () {
            $('#vin_current_pending_toolbar .filters').removeClass('filter-hover');
        }
    )

    const tableInit = () => {
        var options = {
            toolbarId: '#vin_current_pending_toolbar',
            vin_toolbar: '.vin_current_pending_toolbar',
            vin_url: '/api/v1/jobs/pending',
            vin_method: 'GET',
            vin_params: function () {
                var params = {};
                params.accurateFlag = accurateFlag;
                params = $.extend(params, getParams());
                return params;
            },
            placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
            filterOption: ['job_status', 'module_type', 'job_type'], //传入每个filter选项的id ,后续会增加列，现在只有三列
            searchInput: true, //搜索框
            searchClass: 'currentPendingSearch',
            searchSelector: '.currentPendingSearch',
            paginationLoop: false,
            hideColumns: "", //默认要隐藏的列，以“,”分割的字符串，没有就不写
            uniqueId: 'job_uuid',
            changeHeightBtn: true, //改变高度按钮
            resizable: true,
            onResetView: initTableHeight,
            // onCheck，onUncheck，onUncheckAll，onCheckAll 四个方法主要是为了控制勾选后批量操作的逻辑
            PostBody: function () {
                // 保持表格高度逻辑
                if (changeHeightFlag == false) {
                    $('#current_pending_table>tbody>tr>td').css({
                        'padding-top': '4.25px',
                        'padding-bottom': '4.25px'
                    })
                    $('#vin_current_pending_toolbar .change_height i').removeClass('icon-auto-height2');
                } else if (changeHeightFlag == true) {
                    $('#current_pending_table>tbody>tr>td').css({
                        'padding-top': '10.25px',
                        'padding-bottom': '10.25px'
                    })
                    $('#vin_current_pending_toolbar .change_height i').addClass('icon-auto-height2');
                }
            },
            columnsSwitch: function () {
                // 优化任务列表需要的逻辑，指的是，在列表操作右上角显示/隐藏列的时候需要刷新一下表格，重新获取数据，重新渲染表格
                $('#current_pending_table').bootstrapTable('refresh');
            },
        }
        if (isInits) {
            $('#current_pending_table').bootstrapTable('destroy').baseTableCacheConfig().init(options);
        } else {
            $('#current_pending_table').baseTableCacheConfig().init(options);
        }
        // 改变表格高度
        $('#vin_current_pending_toolbar .change_height').off('click').on('click', change_height);

    }

    // 表格高度改变按钮逻辑
    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#current_pending_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'padding-bottom': '10.25px'
            })
            $('#vin_current_pending_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#current_pending_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#vin_current_pending_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }

    var inits = function (){
        initCurrentJobTableDaterangePicker(); // 初始化当前任务表格日期范围选择器
        tableInit();
        initTableHeight();
    }

    /**
     * @function 获取自定义参数
     * @param bool auto 自动刷新时暂停获取参数，默认false 为获取
     */
    var getParams = function () {
        queryParams.search = $('#current_job_pending_seach_ipt').val();

        // 合并过滤器搜索参数
        queryParams = Object.assign(queryParams, FILTER_PARAMS);

        // 合并高级搜索参数
        queryParams = Object.assign(queryParams, ADVANCED_SEARCH_PARAMS);

        return queryParams;
    }

    function addListener() {
        // 监听当前任务表格 - 日期范围选择组件派发的数据，以更新表格
        window.$on('current_job_pending_datepicker-updateDateRangeEvent', (data) => {
            // 记录选择的开始时间和结束时间，用于过滤搜索的联动
            startTime = data.startTime;
            endTime = data.endTime;

            $('#current_pending_table').bootstrapTable('refresh', { query: { start_time: startTime, end_time: endTime } });
        });

        // 搜索当前任务
        $('#current_job_pending_search_btn').on('click', () => {
            searchVal = $('#current_job_pending_seach_ipt').val();

            if (searchVal) {
                $('#current_pending_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS }})
            }
        });

        $('#current_job_pending_seach_ipt').on('focus', () => {
            $('#current_job_pending_clear_search').removeClass('hide');
        });

        // 清空当前任务搜索
        $('#current_job_pending_clear_search').on('click', () => {
            $('#current_job_pending_seach_ipt').val('');
            searchVal = '';
            $('#current_job_pending_clear_search').addClass('hide');
            $('#current_pending_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } } );
        })
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;
        height = panelH - 405;

        $("#current_pending_task_drawer .fixed-table-container").css({
            "height": height
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            // 初始化表格
            inits();
            addListener();

            isInits = true;
        }
    };
}();
