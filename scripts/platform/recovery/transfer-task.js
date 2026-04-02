//客户端分组管理 
var TransferTask = function () {
    let _pageSize = 40
    let editUuid = ''
    var table = $('#transfer_table');
    let btnOpen;
    var checkIndex;
    var interval = null;
    var expandIndex = null;
    var statusArr = {
        0:  LANG.UI_PUBLIC_UNKNOWN, // unknown transport task status
        1: LANG.UI_RECOVERY_GRAIN_JOB_STATUS_SCANNING, // transport task is scanning
        2: LANG.UI_RECOVERY_GRAIN_JOB_STATUS_TRANSPORTING, // transport task is transporting
        3: LANG.UI_RECOVERY_GRAIN_JOB_STATUS_ERROR, // transport task error
        4: LANG.UI_RECOVERY_GRAIN_JOB_STATUS_SUCCESSED, // transport task successed
        5: LANG.UI_RECOVERY_GRAIN_JOB_STATUS_STOPPED, // transport task stopped
        6: LANG.UI_NODE_ABNORMAL, // transport task yichang
    };

    //得到状态的显示类型
    var getStatusLevelClass = function(level){
        var levelClass = '';
        switch(level){
            case 1:
            case 2:
                levelClass = "label-info";
                break;
            case 3:
                levelClass = "label-danger";
                break;
            case 4:
                levelClass = "label-success";
                break;
            case 5:
                levelClass = "label-default";
                break;
            case 6:
                levelClass = 'label-warning';
                break;
            default:
                levelClass = "label-info";
                break;
        }
        return levelClass;
    }

    var initTransferTable = function () {
        var operationFormatter = function (value, row, index, field) {
            if ($.inArray('p_current_job_manager', CONF.PERMISSION_ARR) === -1) {
                return '--';
            }
            var button = '<div class="btn-group">';

            button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                '</button>' +
                '<ul class="dropdown-menu min-width100" role="menu" id='+ row.id +'>';

            button += `<li class="stop" style="${(row.task_status != 1 && row.task_status != 2) ? 'pointer-events: none;opacity: 0.6; cursor: not-allowed;' : ''}"><a href="javascript:;"><i class="viconfont vicon-a-Pause-onezanting"></i> ` + LANG.UI_MACHINE_OS_STOP_JOB + ` </a></li>`;
            // button += '<li class="reStart"><a href="javascript:;"><i class="viconfont vicon-kaijijieguo"></i> ' + '启动任务' + ' </a></li>';
            button += '</ul></div>';
            return button;
        }
        var shareOp = {
            'click .btn': function (event, value, row, index) {
                btnRecord(event.target);
            },
            'click .stop': function (event, value, row, index) {
                let currentUuid = row.id;
                let urlPath = '/api/v1/recovery/'+ currentUuid +'/graininess/stop/clients/'
                bootbox.confirm({
                    title: LANG.UI_RECOVERY_STOP_TRANSLATE_TASK,
                    message: LANG.UI_RECOVERY_IS_STOP_TRANSLATE_TASK,
                    callback: function (r) {
                        if (!r) {
                            return;
                        }
                        Metronic.blockUI({target: '#transfer_table',animate: true});
                        pAjaxRequest({}, urlPath, 'post', function (res) {
                            Metronic.unblockUI('#transfer_table');
                            if (res.success) {
                                $('#transfer_table').bootstrapTable('refresh');
                                return UIToastr.showSuccess(LANG.UI_MACHINE_OS_STOP_JOB, LANG.UI_RECOVERY_STOP_TRANSLATE_TASK_SUCCESS);
                            } else {
                                UIToastr.showWarning(LANG.UI_MACHINE_OS_STOP_JOB, LANG.UI_RECOVERY_STOP_TRANSLATE_TASK_FAIL);
                            }
                        });
                    }
                })
            },
            // 'click .reStart': function (event, value, row, index) {
            //     $('#drawer-edit').drawer('show');
            //     editUuid = row.uuid
            // }
        }
        let urlPath = $('#data_uuid').val();
        var options = {
            vin_url: "api/v1/recovery/"+ urlPath +"/graininess/clients",
            vin_method: "GET",
            vin_params: function () {
                // 所有自定义携带参数，必须return
                var params = {};
                params['search'] = $('#transfer_task_search_ipt').val();
                return params;
            },
            sortName: 'create_time',
            sortOrder: 'desc',
            changeHeightBtn: true, //改变高度按钮
            pagination: true, //分页
            singleSelect:true,
            detailView: true,
            detailFormatter: current_detail,
            PostBody: function() {
                var tableData = table.bootstrapTable('getData');
                if (tableData.length == 0)return;
                checkRecord();
                initTimer();
                $('#' + btnOpen + '').parent('.btn-group').addClass('open');
                if (null !== expandIndex) {
                    table.bootstrapTable('expandRow', expandIndex);
                }
                // initCancle()
                placholderSet()
            },
            onExpandRow: (index) => {
                if (null === expandIndex) {
                    expandIndex = index;
                } else if (index !== expandIndex) {
                    table.bootstrapTable('collapseRow', expandIndex);
                    expandIndex = index;
                }
            },
            onCollapseRow: () => {
                expandIndex = null;
            },
            onRefresh: function (params) {
                table.bootstrapTable('hideLoading');
            },
            onCheck: (row) => {
                checkEvents('#transfer_table', '#delete_transfer_task');
            },
            onUncheck: (row) => {
                checkEvents('#transfer_table', '#delete_transfer_task');
            },
            onUncheckAll: () => {
                checkEvents('#transfer_table', '#delete_transfer_task');
            },
            onCheckAll: () => {
                checkEvents('#transfer_table', '#delete_transfer_task');
            },
            resizable: true, //可变宽度
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                },
                {
                    field: 'transport_task_name',
                    title: LANG.UI_REPORT_TASKNAME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'ip',
                    title: LANG.UI_RECOVERY_IP,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'file_save_mode',
                    title: LANG.UI_FILE_PROCESS_SAME_FILE,
                    sortable: true,
                    align: 'center',
                    formatter: function (value) {
                        let strategyType;
                        switch (value) {
                            case 1:
                                strategyType = LANG.UI_FILE_PROCESS_SAME_FILE_COVER;
                                break;
                            case 2:
                                strategyType = LANG.UI_FILE_PROCESS_SAME_FILE_KEEP_LATEST;
                                break;
                            case 3:
                                strategyType = LANG.UI_RECOVERY_SKIP;
                                break;
                            case 4:
                                strategyType = LANG.UI_FILE_PROCESS_SAME_FILE_RENAME;
                                break;
                            case 5:
                                strategyType = LANG.UI_FILE_PROCESS_SAME_FILE_REPLACE;
                                break;
                            case 6:
                                strategyType = LANG.UI_NODE_ABNORMAL;
                                break;
                            default:
                                break;
                        }
                        return '<span title="' + strategyType + '">' + strategyType + '</span>';
                    }
                },
                {
                    field: 'transport_size',
                    title: LANG.UI_GRAIN_JOB_TOTAL_SIZE,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'completed_size',
                    title: LANG.UI_GRAIN_JOB_TRANSFER_SIZE,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'progress',
                    title: LANG.UI_TASK_AWS_TRANS_PROGRESS,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'create_time',
                    title: LANG.UI_PUBLIC_CREATE_TIME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'finish_time',
                    title: LANG.UI_PUBLIC_FINISH_TIME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'task_status',
                    title: LANG.UI_BLACK_WHITE_STATUS,
                    sortable: true,
                    align: 'center',
                    formatter: function (value) {
                        return '<span class="label ' + getStatusLevelClass(value) + '" >' + statusArr[value] + '</span>';
                    }
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    formatter: operationFormatter,
                    events: shareOp,
                    opButton: true,
                    clickToSelect: false, //不可通过点击行选中
                    sortable: false, //默认可排序，禁用排序才写此项
                }
            ],
        }
        table.baseTableConfig().init(options);

    };
    var initTimer = function () {
        if (interval != null) { //判断计时器是否为空
            clearTimeout(interval);
            // interval = null;
        }
        interval = setTimeout(update, 5000);
    }
    // 记录刷新按钮展开
    var btnRecord = function (target) {
        btnOpen = $(target).next('.dropdown-menu').prop('id');
    }
    //更新表格数据
    var update = function () {
        table.bootstrapTable('refresh');
    }
    //记录勾选
    var checkRecord = function () {
        var checkArr = [];
        $.each(checkIndex, function (index) {
            checkArr.push(checkIndex[index].id);
        });
        table.bootstrapTable('checkBy', {
            field: 'id',
            values: checkArr
        })
    }
    const initListeners = function () {
        //删除
        $('#delete_transfer_task').on('click', deleteTask);
        //编辑
        $('#edit_submit').on('click',editTask)

        $('#transfer_table_div .search-btn').on('click', function(){
            $('#transfer_table').bootstrapTable('refresh', {
                query: {search:$('#transfer_task_search_ipt').val()}
            });
        })


        // 监听页面的改变，已确定刷新是否展示button
        $(document).on('click', function (e) {
            var is_open = false;
            $(e.target).find('#transfer_table ul>.dropdown-menu').each(function (){
                var $this = $(this);
                var ariaExpanded = $this.attr('aria-expanded'); // 获取aria-expanded属性的值
                if (ariaExpanded !== undefined) { // 如果元素包含aria-expanded属性
                    if (ariaExpanded === 'true') {
                        is_open = true;
                    }
                }
            })

            if (is_open == false) {
                btnOpen = '';
            }
        });

        // 搜索对象存储
        $('#transfer_task_search').off().on('click', () => {
            let searchVal = $('#transfer_task_search_ipt').val();

            if (searchVal) {
                $('#transfer_table').bootstrapTable('refresh', {query: {search: searchVal}})
            }
        });

        $('#transfer_task_search_ipt').on('focus', () => {
            $('#transfer_task_clear_search').removeClass('hide');
        });

        // 清空对象存储搜索
        $('#transfer_task_clear_search').on('click', () => {
            $('#transfer_task_search_ipt').val('');
            $('#transfer_task_clear_search').addClass('hide');
            $('#transfer_table').bootstrapTable('refresh', {query: {search: ''}})
            $('#transfer_table').bootstrapTable('resetSearch');
        });
        $('#transfer_task_search_ipt').keypress(function (e) {
            if (e.which == 13) {
                $('#transfer_table').bootstrapTable('refresh', {
                    query: {search:$('#transfer_task_search_ipt').val()}
                });
            }
        });
    }
    var operateEvents = {
        'click .stopStrategy': function(e, value, row, index) {
            let currentUuid = row.uuid;
            let taskUuid = $('#data_uuid').val();
            let urlPath = '/api/v1/recovery/'+ taskUuid +'/graininess/operation/clients/' + currentUuid
            bootbox.confirm({
                title: LANG.UI_GLOBAL_REPORT_DELETE,
                message: '1111111',
                callback: function (r) {
                    if (!r) {
                        return;
                    }
                    Metronic.blockUI({target: '#resourceGroupContent',animate: true});
                    pAjaxRequest({}, urlPath, 'post', function (res) {
                        Metronic.unblockUI('#resourceGroupContent');
                        if (res.success) {
                            $('#report_table').bootstrapTable('refresh');
                            return UIToastr.showSuccess(LANG.UI_GLOBAL_TEMPLATE_DELETE, LANG.UI_GLOBAL_TEMPLATE_DELETE_SUCCESS);
                        } else {
                            UIToastr.showWarning(LANG.UI_GLOBAL_TEMPLATE_DELETE, LANG.UI_GLOBAL_TEMPLATE_DELETE_FAIL);
                        }
                    });
                }
            })
        },
    }
    var current_detail = function (index, row, element) {
        var htmlList = ``
        let transport_file_list = row.transport_file_list;
        $.each(transport_file_list.transport_file_info_list, function(index, item) {
            htmlList += `${item.path}<br>`
        })
        var transport_info = LANG.UI_PUBLIC_NOTHING;
        if (row.transport_info != '') {
            transport_info = '<a href="javascript:void(0);" class="downLoadFile" data-node_uuid="'+row.node_uuid+'" data-node_ip="'+row.node_ip+'" data-is_master_node="'+row.is_master_node+'" data-url="'+row.transport_info+'">'+LANG.UI_PUBLIC_DOWNLOAD+'</a>';
        }
        var html = `
                    <table>
                    <tbody>
                    <tr>
                        <th>`+LANG.UI_RECOVERY_IP+`</th>
                        <th>`+LANG.UI_GRAIN_JOB_GOAL_PATH+`</th>
                        <th>`+LANG.UI_RECOVERY_TRANSLATE_FILE_LIST+`</th>
                        <th>`+LANG.UI_FILE_PASSFILE_LISTS+`</th>
                    </tr>
                    <tr>
                    <td>${row.ip}</td>
                    <td>${row.file_save_path}</td>
                   <td style="padding-right: 30px">
                        <div class="historyfilelisttext">
                            `+htmlList+`
                        </div>
                    </td>
                   <td>`+transport_info+`</td>
                   </tr>
                   </tbody></table></td>
        `;
        $(element).append(html);
        $('.downLoadFile').off().on('click', function (){
            var url = $(this).attr('data-url');
            var is_master_node = $(this).attr('data-is_master_node');
            var node_ip = $(this).attr('data-node_ip');
            var node_uuid = $(this).attr('data-node_uuid');
            let urlPath = '/api/v1/system/generate/download?filepath='+url
            let data = {};
            if (is_master_node == 'false') {
                // 不是主节点，那么就要用另外的形式下载
                urlPath = '/api/v1/recovery/grains_log';
                data = {
                    url: url,
                    node_ip: node_ip,
                    node_uuid: node_uuid,
                };
            }
            pAjaxRequest(data, urlPath, 'GET', function (res) {
                if (res.success) {
                    // window.location.href = res.data.url;
                    window.open(res.data.url, '_blank');
                } else {
                    UIToastr.showWarning(LANG.UI_PUBLIC_DOWNLOAD, res.message);
                }
            },false)
        })
    }
    const placholderSet = function () {
        $('#transfer_task_search_ipt').attr('placeholder', LANG.UI_SEARCH_BY_TASK_NAME);
    };
    // const initCancle = function() {
    //     $('#virus_toolbar .clear').off().on('click', function () {
    //         $('#virus_toolbar .customSearch').val('');
    //         queryParams.search = '';
    //         $('#virus_toolbar  .clear').removeClass('show');
    //         sessionStorage.removeItem("search");
    //         $('#virus_toolbar .search input').attr('placeholder',LANG.UI_BACKUP_EXPORT_ENTER_STRATEGY);
    //         $('#virus_table').bootstrapTable('resetSearch');
    //     });
    // }
    const editTask= function(row) {
        let requestParam = {}
        requestParam.edit_name = $('#select_transfer_client').find('option:selected').val();
        requestParam.edit_password = $('#fileSave').find('option:selected').val();
        let urlPath = '/api/v1/recovery/'+ editUuid +'/graininess/operation/clients'
        pAjaxRequest(requestParam, urlPath, 'put', function (res) {
            if (res.success) {
                $('##drawer-edit').drawer('hide');
                $('#transfer_table').bootstrapTable('refresh');
                return UIToastr.showSuccess(LANG.UI_GLOBAL_SHARE_EDIT, LANG.UI_GLOBAL_SHARE_EDIT_SUCCESS);
            } else {
                UIToastr.showWarning(LANG.UI_GLOBAL_SHARE_EDIT, LANG.UI_GLOBAL_SHARE_EDIT_FAIL);
            }
        },false)
    }

    const deleteTask = function() {
        var template_uuid = getIdSelectedId('#transfer_table')

        //必选
        if (template_uuid.length !=1) {
            return tipsDeleteStrategy();
        }
        var url ='/api/v1/recovery/'+ template_uuid[0] +'/graininess/operation/clients'
        bootbox.confirm({
            title: LANG.UI_JOB_DELETE_JOB,
            message: LANG.UI_JOB_DELETE_JOB_TIPS,
            callback: function (r) {
                if (!r) {
                    return;
                }
                Metronic.blockUI({target: '.portlet',animate: true});
                pAjaxRequest({}, url, 'delete', function (res) {
                    Metronic.unblockUI('.portlet');
                    if (res.success) {
                        $('#transfer_table').bootstrapTable('refresh');
                        return UIToastr.showSuccess(LANG.UI_JOB_DELETE_JOB, res.message);
                    } else {
                        UIToastr.showWarning(LANG.UI_JOB_DELETE_JOB, res.message);
                    }
                });
            }
        })
    }



    function  getIdSelectedId(select)
    {
        return $.map($(select).bootstrapTable('getSelections'), function (row) {
            return row.id;
        })

    }

    //没有选中的策略提示
    const tipsDeleteStrategy = function () {
        return UIToastr.showInfo(LANG.UI_PUBLIC_TIPS, LANG.UI_TRANSFER_TASK_DELETE_TIPS);
    }

    const checkEvents = function (tableId, btnId) {
        var selectedRow = table.bootstrapTable('getSelections');
        checkIndex = selectedRow;

        let select = $('' + tableId + '').bootstrapTable('getSelections');
        if (select.length == 0) {
            $(btnId).addClass("disabled");
            $(btnId).attr("disabled");
        } else {
            $(btnId).removeClass("disabled");
            $(btnId).removeAttr("disabled");
        }
    }
    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 400;

        $("#transfer_table_div .fixed-table-body").css({
            "height": height
        });
    }
    return {
        //main function to initiate the module
        init: function () {
            initTransferTable()
            initListeners()
            initTableHeight();
        }
    };

}();

jQuery(document).ready(function() {
    TransferTask.init();
});