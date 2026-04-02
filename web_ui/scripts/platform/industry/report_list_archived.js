var ArchivedReport = function () {
    var table = $('#archived_report_table');
    var reportData = [];
    const STATUS = {
        PENDING: 0,
        ARCHIVED: 1,
        APPROVALING: 2,
        REJECTED: 3,
        REVOKE: 4,
        GENERATED: 9
    };
    const APPROVAL_RESULT = {
        PENDING: 0,
        PASSED: 1,
        REJECTED: 2,
    };
    const addListeners = function () {
        // 删除
        $('#vin_report_archived_toolbar').on('click', '#delete_archived', delReport);
        //下载报告
        $('#vin_report_archived_toolbar').on('click', '#download', downloadReport);

        $('#archived_li').on('click', () => {
            table.bootstrapTable('refresh');
        })
    }

    const delReport = function () {
        var select = table.bootstrapTable('getSelections');
        var uuids = [];
        if (select.length == 0) {
            return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_DELETE, LANG.UI_PLATFORM_INDUSTRY_REPORT_DELETE_TIP);
        }
        bootbox.confirm({
            title: LANG.UI_PLATFORM_INDUSTRY_REPORT_DELETE,
            message: LANG.UI_PLATFORM_INDUSTRY_REPORT_DELETE_CONFIRM,
            callback: function(r) {
                if(!r) return;
                for (let i = 0; i < select.length; i++) {
                    uuids.push(select[i].uuid);
                }
                Metronic.blockUI({
                    target: '#archived_div',
                    animate: true
                });
                pAjaxRequest({uuids:uuids}, '/api/v1/industry/report/delete', 'DELETE', (res) => {
                    Metronic.unblockUI('#archived_div');
                    if (operateResponseList(res, LANG.UI_PLATFORM_INDUSTRY_REPORT_DELETE)) {
                        table.bootstrapTable('refresh');
                    }
                });
            }
        });
    }

    const downloadReport = function () {
        var select = table.bootstrapTable('getSelections');
        var param = {};
        if (select.length < 1) {
            return UIToastr.showInfo(LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD, LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD_TIP);
        } else if (select.length == 1) {
            var uuid = select[0].uuid;
            var url = `/api/v1/industry/report/${uuid}/download`;
        } else {
            var uuids = [];
            for (let i = 0; i < select.length; i++) {
                uuids.push(select[i].uuid);
            }
            param.report_uuid = uuids;
            var url = `/api/v1/industry/report/download`;
        }

        pAjaxRequest(param, url, 'POST', (result)=>{
            if (result.code == 0) {
                UIToastr.showSuccess(LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD, LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD_TIPS);
                location.href = result.data.url;
            } else {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD, result.message);
            }
        })
    }

    const initTable = function () {
        var afterInput = ``;
        if ($.inArray('p_industry_report_archived_download', CONF.PERMISSION_ARR) !== -1) {
            afterInput = `<button class="btn dropdown-toggle btn-font flex_center btn-title p-lr8 table-toolbar-btn" id="download" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-xiazai"></i>
                                <span>` + LANG.UI_PUBLIC_DOWNLOAD + `</span>
                            </button>`;
        }
        let options = {
            toolbarId: '#vin_report_archived_toolbar',
            buttonsToolbar: '#vin_report_archived_toolbar .vin_btnToolbar',
            placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
            vin_url: '/api/v1/industry/report',
            vin_method: 'GET',
            vin_params: function () {
                let params = {};
                let search = $('#vin_report_archived_toolbar .report-archived-search').val();
                params.type = 1; //默认为空时待审批，1是已归档
                if (search) {
                    params.search = search;
                    $('#searchDiv1 .searchContent1').text('');
                    $('#searchDiv1').hide();

                    $('#report_title1').val('');
                    $('#report_task1').val('');
                    $('#report_agent1').val('');
                    $('#report_template1').val('');
                    $('#report_template1').selectpicker('refresh');
                }
                return params;
            },
            sortName: 'create_time',
            sortOrder: 'desc',
            fullPage: true,
            customTool: {
                // beforeInput: `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete_archived"></button></div>`,
                afterInput: afterInput,
            },
            searchInput: true, //搜索框
            searchClass: 'report-archived-search', //自定义的搜索框类名
            searchSelector: '.report-archived-search', //选择使用自定义搜索框
            onCheck: function (row) {
                reportData.push(row.uuid);
                modifyDelStyle('archived_report_table', 'delete_archived');
            },
            onUncheck: function (row) {
                var index = reportData.indexOf(row.uuid); // 查找元素的索引
                if (index !== -1) {
                    reportData.splice(index, 1); // 从数组中删除一个元素
                }
                modifyDelStyle('archived_report_table', 'delete_archived');
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = reportData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index == -1) {
                        reportData.push(row[i].uuid)
                    }
                }
                modifyDelStyle('archived_report_table', 'delete_archived');
            },
            onUncheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = reportData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index != -1) {
                        reportData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
                modifyDelStyle('archived_report_table', 'delete_archived');
            },
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
                        // if (row.approval_flag && value != STATUS.REVOKE && value != STATUS.REJECTED && value != STATUS.ARCHIVED) {
                        //     // 如果是当前用户审批，展示审批描述，颜色为强提示
                        //     var approvalList = JSON.parse(row.approval_list);
                        //     statusLabel = `label label-sm label-warning`;
                        //     des = approvalList[row.now_user_depth + 1].desc;
                        // } else if (value == STATUS.REVOKE || value == STATUS.REJECTED || value == STATUS.ARCHIVED) {
                        //     // do nothing
                        // } else {
                        //     des = `${LANG.UI_PLATFORM_INDUSTRY_REPORT_WAITTING_APPROVE_PREFIX}${row.now_user_name}${LANG.UI_PLATFORM_INDUSTRY_APPROVE}`;
                        // }
                        
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
        if ($.inArray('p_industry_report_archived_approve', CONF.PERMISSION_ARR) !== -1) {
            // 审批流
            button += '<li class="approval"><a href="javascript:;" data-toggle="drawer" data-target="#approval_drawer2" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-shenpiliu"></i> ' + LANG.UI_PLATFORM_INDUSTRY_REPORT_WAITTING_APPROVAL + '</a></li>';

        }
        if ($.inArray('p_industry_report_archived_comment', CONF.PERMISSION_ARR) !== -1) {
            // 评论列表
            button += '<li class="comment"><a href="javascript:;"><i class="viconfont vicon-a-Eyesyanjing"></i> ' + LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT_LIST + '</a></li>';

        }

        button += '</ul></div>';
        return button;
    }

    const operates = {
        'click .view': function (event, value, row, index) {
            JobReportDetail.init({'uuid': row.uuid, 'pre': 1})
            //LOCATION('./content/platform/industry/job_report.php?uuid=' + row.uuid);
        },
        'click .approval': function (event, value, row, index) {
            nowOpRow = row;
            console.log(JSON.parse(row.approval_list));
            $('#report_name2').text(row.name);
            $('#depth_div2').empty().append(getApprovalList(row.approval_list, row));
        },
        'click .comment': function (event, value, row, index) {
            nowOpRow = row;
            $('#comment_modal').modal('show');
            $('.own_approver .name').text(row.user_name);
            if (row.user_uuid == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9") {
                // admin查看全部评论,不显示所属审批人
                $('.own_approver').hide();
            } else {
                $('.own_approver').show();
            }
            initCopySendTable(row);
            initShareTable(row);
        },
    }

    const getApprovalList = function (list, row) {
        var depthContent = JSON.parse(list);
        var html = '';
        var stageDes = LANG.UI_PLATFORM_INDUSTRY_START_APPROVE;
        var advice = ''; //审批建议
        var color = '#2A87C8'; //默认绿色
        var labelClass = 'label-success'; //默认绿色label
        var isEndClass = ''; //是否是最后一层
        var icon = 'vicon-tongguo'; //默认通过
        var titleDes = LANG.UI_PLATFORM_INDUSTRY_START_APPROVE_USER;
        for (let i = 0; i < depthContent.length; i++) {
            var eachContent = depthContent[i];
            labelClass = 'label-info';
            switch (eachContent.status) {
                case APPROVAL_RESULT.PENDING:
                    color = '#1296db';
                    labelClass = 'label-info';
                    icon = 'vicon-a-dengdaidaishenpi';
                    stageDes = eachContent.desc ?? LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING;
                    break;
                case APPROVAL_RESULT.PASSED:
                    color = '#2A87C8';
                    labelClass = 'label-success';
                    icon = 'vicon-tongguo';
                    stageDes = eachContent.desc ?? LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PASS;
                    break;
                case APPROVAL_RESULT.REJECTED:
                    color = '#F1416C';
                    labelClass = 'label-danger';
                    icon = 'vicon-bohui';
                    stageDes = eachContent.desc ?? LANG.UI_PLATFORM_INDUSTRY_APPROVAL_REJECT;
                    break;
                // 内部暂时只有三个状态
                // case APPROVAL_RESULT.ARCHIVED:
                //     color = '#0FBF98';
                //     labelClass = 'label-success';
                //     stageDes = eachContent.desc ?? '已归档';
                //     break;
                // case APPROVAL_RESULT.REVOKE:
                //     color = '#F0F3F5';
                //     labelClass = 'label-default';
                //     stageDes = eachContent.desc ?? '已撤销';
                //     break;
            }

            // 第一个是发起人，后面是审批人
            if (i != 0) {
                titleDes = LANG.UI_PLATFORM_INDUSTRY_APPROVE_USER;
            }

            //是否有审批建议
            if (eachContent.remark) {
                advice = `<div class="depth-connect mt8 approve-advice"><span>${eachContent.remark}</span></div>`;
            } else {
                advice = '';
            }

            if (i == depthContent.length - 1) {
                //最后一层
                isEndClass = 'end';
            }

            html += `<div class="depth-parent ${isEndClass}">
                        <div class="depth-title">
                            <div class="circle" style="color:${color}"><i class="viconfont ${icon}"></i></div><span class="label label-sm ${labelClass} ml15">${eachContent.desc}</span>
                        </div>
                        <div class="depth-connect mt8 approve-user"><span style="font-size: 14px;color: #999999;line-height: 16px;">${titleDes}：</span><span>${eachContent.user_name}</span></div>
                        <div class="depth-connect mt8 approve-time"><span>${eachContent.create_time}</span></div>
                        ${advice}
                    </div>`;
        }


        return html;

    }

    /**
     * 初始化抄送表格
     * @param {Object} row - 触发初始化操作的行对象，包含报告UUID
     */
    const initCopySendTable = function (row) {
        let option = {
            toolbarId: '#vin_report_pending_toolbar',
            buttonsToolbar: '#vin_report_pending_toolbar .vin_btnToolbar',
            placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
            vin_url: '/api/v1/industry/report/remark',
            vin_method: 'GET',
            vin_params: function () {
                let params = {};
                params.type = 1; //1抄送，2分享
                params.report_uuid = row.uuid;
                return params;
            },
            showExport: false, //是否显示导出按钮
            showColumns: false, //是否开启列选择按钮
            columns: [
                {
                    field: 'user_name',
                    title: LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT_USER,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'content',
                    title: LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT_CONTENT,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'time',
                    title: LANG.UI_JOB_CROWD_TIME,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
            ]
        }

        $('#cs_table').bootstrapTable('destroy');
        sessionStorage.removeItem("cs_table_pageRecord");
        $('#cs_table').baseTableConfig().init(option);
    }

    /**
     * 初始化分享表格
     * @param {Object} row - 触发初始化操作的行对象，包含报告UUID
     */
        const initShareTable = function (row) {
            let option = {
                toolbarId: '#vin_report_pending_toolbar',
                buttonsToolbar: '#vin_report_pending_toolbar .vin_btnToolbar',
                placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
                vin_url: '/api/v1/industry/report/remark',
                vin_method: 'GET',
                vin_params: function () {
                    let params = {};
                    params.type = 2; //1抄送，2分享
                    params.report_uuid = row.uuid;
                    return params;
                },
                showExport: false, //是否显示导出按钮
                showColumns: false, //是否开启列选择按钮
                columns: [
                    {
                        field: 'user_name',
                        title: LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT_USER,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                    {
                        field: 'content',
                        title: LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT_CONTENT,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                    {
                        field: 'time',
                        title: LANG.UI_JOB_CROWD_TIME,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                ]
            }
    
            $('#share_table').bootstrapTable('destroy');
            sessionStorage.removeItem("share_table_pageRecord");
            $('#share_table').baseTableConfig().init(option);
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
    ArchivedReport.init();
});
