var TapeMonitor = function () {
    var table = $('#tape_monitor_table');
    var changeHeightFlag = false;
    var isInits = false; // 标记本次是否已经初始化过
    function addListener () {
        $('#vin_monitor_toolbar').on('click', '.search-btn', function () {
            table.bootstrapTable('refresh');
        });

        $('#check_log').on('click', function () {
            $('#tap_monitor_log_drawer').drawer('show');
        })

    }

    /**
     * @function 类型转义
     * @param row 行数据
     */
    function typeFormatter(index, row) {
        var content = 'UNKNOWN';
        switch (row.type) {
            case 1:
                content = LANG.UI_TAPE_MONITOR_OP_SCAN_ALL_TAPE;
                break;
            case 2:
                content = LANG.UI_TAPE_MONITOR_OP_SCAN_ONE_TAPE;
                break;
            case 3:
                content = LANG.UI_TAPE_MONITOR_OP_RETRIEVAL_ALL_CARRIAGE;
                break;
            case 4:
                content = LANG.UI_TAPE_MONITOR_OP_RETRIEVAL_ONE_CARRIAGE;
                break;
            case 5:
                content = LANG.UI_TAPE_MONITOR_OP_EXPORT_CARRIAGE;
                break;
            case 6:
                content = LANG.UI_TAPE_MONITOR_OP_IMPORT_CARRIAGE;
                break;
            case 7:
                content = LANG.UI_TAPE_MONITOR_OP_CREATE_TAPE_GROUP;
                break;
            case 8:
                content = LANG.UI_TAPE_MONITOR_OP_DELETE_TAPE_GROUP;
                break;
            case 9:
                content = LANG.UI_TAPE_MONITOR_OP_MODIFY_TAPE_GROUP;
                break;
            case 10:
                content = LANG.UI_TAPE_MONITOR_OP_DELETE_BACKUP_SET;
                break;
            case 11:
                content = LANG.UI_TAPE_MONITOR_OP_RENAME_BACKUP_SET;
                break;
            case 12:
                content = LANG.UI_TAPE_IMPORT_GROUP;
                break;
            case 13:
                content = LANG.UI_TAPE_BACKUP_SET_STATUS_FREEZE;
                break;
            case 14:
                content = LANG.UI_TAPE_BACKUP_SET_STATUS_DEFROST;
                break;
            default:
                break;
        }
        return `<span title="${content}">${content}</span>`
    }

    /**
     * @function 状态转义
     * @param row 行数据
     */
    function statusFormatter(index, row) {
        switch (row.status) {
            case 0:
                return 'UNKNOWN';
                break;
            case 1:
                return `<span class="label label-sm label-success status-icon">${LANG.UI_TAPE_IS_RUNNING}</span>`
                break;
            case 2:
                return `<span class="label label-sm label-success status-icon">${LANG.UI_PUBLIC_SUCCESS}</span>`
                break;
            case 3:
                return `<span class="label label-sm label-danger status-icon">${LANG.UI_PUBLIC_FAILED}</span>`
                break;
        }
    }

    /**
     * @function 初始化表格
     */
    function initTable() {
        let options = {
            toolbarId: '#vin_monitor_toolbar',
            buttonsToolbar: '#vin_monitor_toolbar .vin_btnToolbar',
            vin_url: '/api/v1/tapes/monitor',
            vin_method: 'GET',
            vin_params: function () {
                var params = {};
                if ($('.tapeMonitorSearch').val()) {
                    params.search = $('.tapeMonitorSearch').val();
                }
                return params;
            },
            fullPage: true,
            placeholder: LANG.UI_TAPE_MONITOR_SEARCH_AS_OP_OBJECT,
            sortName: 'start_time',
            sortOrder: 'desc',
            searchInput: true, //搜索框
            searchClass: 'tapeMonitorSearch',
            searchSelector: '.tapeMonitorSearch',
            paginationLoop: false,
            changeHeightBtn: true, //改变高度按钮
            resizable: true,


            columns: [
                {
                    field: 'object_name', //字段名
                    title: LANG.UI_TAPE_MONITOR_OP_OBJECT,
                    sortable: false,
                },
                {
                    field: 'type',
                    title: LANG.UI_TAPE_MONITOR_OP_TYPE,
                    formatter: typeFormatter
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: statusFormatter
                },
                {
                    field: 'user_name',
                    title: LANG.UI_TAPE_MONITOR_OP_USER,
                    sortable: false,
                },
                {
                    field: 'start_time',
                    title: LANG.UI_PUBLIC_START_TIME,
                },
                {
                    field: 'end_time',
                    title: LANG.UI_PUBLIC_END_TIME,
                },
                {
                    field: 'description',
                    title: LANG.UI_PUBLIC_DESCRIPTION,
                    formatter: function (value, row, index) {
						// 剔除span标签加入title
						const newRegex = /<span[^>]*>|<\/span>/gi;
						title = value.replace(newRegex, '');
						return `<span title = "${title}">${value}</span>`; //避免走入插件拼接title，导致显示不全
                    }
                },
            ],
            PostBody: function () {
                // 保持表格高度逻辑
                if (changeHeightFlag == false) {
                    $('#tape_monitor_table>tbody>tr>td').css({
                        'monitor-top': '4.25px',
                        'monitor-bottom': '4.25px'
                    })
                    $('#vin_monitor_toolbar .change_height i').removeClass('icon-auto-height2');
                } else if (changeHeightFlag == true) {
                    $('#tape_monitor_table>tbody>tr>td').css({
                        'monitor-top': '10.25px',
                        'monitor-bottom': '10.25px'
                    })
                    $('#vin_monitor_toolbar .change_height i').addClass('icon-auto-height2');
                }
            },
            onRefresh: function (params) {

            },
            columnsSwitch: function () {
                // 优化任务列表需要的逻辑，指的是，在列表操作右上角显示/隐藏列的时候需要刷新一下表格，重新获取数据，重新渲染表格
                $('#tape_monitor_table').bootstrapTable('refresh');
            },
        }
        if (isInits) {
            $('#tape_monitor_table').bootstrapTable('destroy').baseTableConfig().init(options);
        } else {
            $('#tape_monitor_table').baseTableConfig().init(options);
        }

        // 改变表格高度
        $('#vin_monitor_toolbar .change_height').off('click').on('click', change_height);
    }

    // 表格高度改变按钮逻辑
    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#tape_monitor_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'padding-bottom': '10.25px'
            })
            $('#vin_monitor_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#tape_monitor_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#vin_monitor_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 346 ;

        $("#tape_monitor_table .fixed-table-body " ).css({
            "height": height
        });
    }

    return {
        init: function () {
            initTable();
            addListener();
            initTableHeight();
            isInits = true;
        }
    }
}();

$(document).ready(function () {
    TapeMonitor.init();
});