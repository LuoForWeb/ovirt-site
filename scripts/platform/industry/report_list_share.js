var ShareReportList = function () {
    var table = $('#share_report_table');
    var reportData = [];
    var nowOpRow;
    const STATUS = {
        PENDING: 0,
        ARCHIVED: 1,
        APPROVALING: 2,
        REJECTED: 3,
        REVOKE: 4,
        GENERATED: 9
    };
    
    const addListeners = function () {
        $('#share_li').on('click', () => {
            table.bootstrapTable('refresh');
        });
        
        $('#remark_submit').on('click', ()=>{
            let params = {};
            params.comment = $('#remark_content').val();
            params.report_uuid = nowOpRow.uuid;
            if (params.comment == "") {
                // 评论内容不能为空!
                return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_SUBMIT_COMMENT_VIEW, LANG.UI_PLATFORM_INDUSTRY_REPORT_SUBMIT_COMMENT_VIEW_TIP);
            }
            $('#remark_submit').prop('disabled', true);
            Metronic.blockUI({target: '#share_div',animate: true});
            pAjaxRequest(params, '/api/v1/industry/report/remark', 'POST', (res)=>{
                $('#remark_submit').prop('disabled', false);
                Metronic.unblockUI('#share_div');
                var op = LANG.UI_PLATFORM_INDUSTRY_REPORT_SUBMIT_COMMENT_VIEW;
                if (operateResponseList(res, op)) {
                    table.bootstrapTable('refresh');
                    $('#remark_drawer').drawer('hide');
                }
            })
        })
    }

    const initTable = function () {
        let options = {
            toolbarId: '#vin_report_share_toolbar',
            buttonsToolbar: '#vin_report_share_toolbar .vin_btnToolbar',
            placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
            vin_url: '/api/v1/industry/report',
            vin_method: 'GET',
            vin_params: function () {
                let params = {};
                let search = $('#vin_report_share_toolbar .report-share-search').val();
                params.type = 2; //默认为空时待审批，1是已归档,2分享/抄送
                if (search) {
                    params.search = search;

                    $('#searchDiv2 .searchContent2').text('');
                    $('#searchDiv2').hide();
                    $('#report_title2').val('');
                    $('#report_task2').val('');
                    $('#report_agent2').val('');
                    $('#report_template2').val('');
                    $('#report_template2').selectpicker('refresh');
                }
                return params;
            },
            sortName: 'create_time',
            sortOrder: 'desc',
            fullPage: true,
            searchInput: true, //搜索框
            searchClass: 'report-share-search', //自定义的搜索框类名
            searchSelector: '.report-share-search', //选择使用自定义搜索框
            showExport: false, //是否显示导出按钮
            showColumns: true, //是否开启列选择按钮

            columns: [{
                checkbox: true,
                sortable: false, //默认可排序，禁用排序才写此项
                formatter: function (value, row, index, field) {
                    for (var i = 0; i < reportData.length; i++) {
                        if (row.uuid == reportData[i]) {
                            return true
                        }
                    }
                }
            },
                {
                    field: 'job_name',
                    title: LANG.UI_VM_MACHINE_JOB_NAME,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'name',
                    title: LANG.UI_PLATFORM_INDUSTRY_REPORT_NAME,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'timepoint',
                    title: LANG.UI_PLATFORM_INDUSTRY_REPORT_TIMEPOTINT,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                /*{
                    field: 'module',
                    sortable: false,
                    title: LANG.UI_SEARCH_MODE_TYPE,
                },*/
                {
                    field: 'user_name',
                    title: LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_CREATE_NAME
                },
                {
                    field: 'report_time',
                    title: LANG.UI_STORAGE_LUN_CREATE_TIME
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: function (value, row, index) {
                        var statusLabel = ``;
                        var des = row.status_value;
                        switch (value) {
                            case STATUS.PENDING:
                                statusLabel = `label label-sm label-info`;
                                if (row.approval_flag) {
                                    var approvalList = JSON.parse(row.approval_list);
                                    $.each(approvalList, function (index, item){
                                       if (item.user_uuid == row.now_user_uuid) {
                                            des = item.desc;
                                       }
                                    })
                                } else {
                                    des = `${LANG.UI_PLATFORM_INDUSTRY_REPORT_WAITTING_APPROVE_PREFIX}${row.now_user_name}${LANG.UI_PLATFORM_INDUSTRY_APPROVE}`;
                                }
                                break;
                            case STATUS.ARCHIVED:
                                statusLabel = `label label-sm label-success`;
                                break;
                            case STATUS.APPROVALING:
                                statusLabel = `label label-sm label-info`;
                                if (row.approval_flag) {
                                    var approvalList = JSON.parse(row.approval_list);
                                    $.each(approvalList, function (index, item){
                                       if (item.user_uuid == row.now_user_uuid) {
                                            des = item.desc;
                                       }
                                    })
                                } else {
                                    des = `${LANG.UI_PLATFORM_INDUSTRY_REPORT_WAITTING_APPROVE_PREFIX}${row.now_user_name}${LANG.UI_PLATFORM_INDUSTRY_APPROVE}`;
                                }
                                break;
                            case STATUS.REJECTED:
                                statusLabel = `label label-sm label-danger`;
                                break;
                            case STATUS.REVOKE:
                                statusLabel = `label label-sm label-default`;
                                break;
                            default:
                                statusLabel = `label label-sm label-default`;
                                break;
                        }
                        return `<span class='${statusLabel}'>${des}</span>`;
                    }
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    clickToSelect: false, //不可通过点击行选中
                    formatter: opButton,
                    width: "125px",
                    opButton: true,
                    events: operates, //单元点击事件
                    forceHide: true,
                }
            ]
        };

        table.baseTableConfig().init(options);
    }

    const opButton = function (value, row, index, field) {
        var button = '<div class="btn-group">';
        
        if (index > 5) {
            button = '<div class="btn-group dropup">';
        }

        button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
            'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
            '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
            '</button>' +
            '<ul class="dropdown-menu min-width100" role="menu" id="' + row.uuid + '">';

        // 查看
        button += '<li class="view"><a href="javascript:;"><i class="viconfont vicon-a-Eyesyanjing"></i> ' + LANG.UI_PUBLIC_LOOK + '</a></li>';
        if ($.inArray('p_industry_report_share_operation', CONF.PERMISSION_ARR) !== -1) {
            // 评论
            button += '<li class="remark"><a href="javascript:;" data-toggle="drawer" data-target="#remark_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-shenpiliu"></i> ' + LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT + '</a></li>';
        }
        button += '</ul></div>';
        return button;
    }

    const operates = {
        'click .view': function (event, value, row, index) {
            JobReportDetail.init({'uuid': row.uuid, 'pre': 1})
            //LOCATION('./content/platform/industry/job_report.php?uuid=' + row.uuid);
        },
        'click .remark': function (event, value, row, index) {
            nowOpRow = row;
            $('#report_name_remark').text(row.name);
            $('#remark_content').empty().val(row.content);
            if (row.content != "") {
                $('#remark_content').prop('disabled', true);
            } else {
                $('#remark_content').prop('disabled', false);
            }
        },
    }

    return {
        init: function () {
            initTable();
            addListeners();
        },
        refresh: function (options){
            table.bootstrapTable('refresh', {
                query: options.queryParams
            });
        }
    }
}();

$(document).ready(function () {
    ShareReportList.init();
});
