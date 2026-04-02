var shareTaskTab = function () {
    let _pageSize = 40
    let editUuid = ''
    var _UserPassword = '';
    var initDataTable = function () {
        var operationFormatter = function (value, row, index, field) {
            var button = '<div class="btn-group">';
            if (index > 5) {
                button = '<div class="btn-group dropup">';
            }

            button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                '</button>' +
                '<ul class="dropdown-menu min-width100" role="menu">';

            button += '<li class="stop"><a href="javascript:;"><i class="viconfont vicon-a-Pause-onezanting"></i> ' + LANG.UI_JOB_STOP + ' </a></li>';
            button += '<li class="reShare"><a href="javascript:;"><i class="viconfont vicon-gongxiang"></i> ' + LANG.UI_RECOVERY_RESHARE + ' </a></li>';
            button += '<li class="editSetting"><a href="javascript:;"><i class="viconfont vicon-a-Editbianji1"></i> ' + LANG.UI_DRIVER_CHANGE_BUG_TYPE + ' </a></li>';
            button += '</ul></div>';
            return button;
        }
        var shareOp = {
            'click .stop': function (event, value, row, index) {
                let currentUuid = row.uuid;
                let taskUuid = $('#data_uuid').val();
                let params = {
                    action: 'stop'
                }
                let urlPath = '/api/v1/recovery/'+ taskUuid +'/graininess/operation/network/' + currentUuid
                Metronic.blockUI({target: '#share_task_tab',animate: true});
                pAjaxRequest(params, urlPath, 'post', function (res) {
                    Metronic.unblockUI('#share_task_tab');
                    if (res.success) {
                        $('#share_table').bootstrapTable('refresh');
                        return UIToastr.showSuccess(LANG.UI_GLOBAL_TEMPLATE_DELETE, LANG.UI_GLOBAL_TEMPLATE_DELETE_SUCCESS);
                    } else {
                        UIToastr.showWarning(LANG.UI_GLOBAL_TEMPLATE_DELETE, LANG.UI_GLOBAL_TEMPLATE_DELETE_FAIL);
                    }
                });
            },

            'click .reShare': function (event, value, row, index) {
                let currentUuid = row.uuid;
                let taskUuid = $('#data_uuid').val();
                let params = {
                    action: 'start'
                }
                let urlPath = '/api/v1/recovery/'+ taskUuid +'/graininess/operation/network/' + currentUuid
                Metronic.blockUI({target: '#share_task_tab',animate: true});
                pAjaxRequest(params, urlPath, 'post', function (res) {
                    Metronic.unblockUI('#share_task_tab');
                    if (res.success) {
                        $('#share_table').bootstrapTable('refresh');
                        return UIToastr.showSuccess(LANG.UI_GLOBAL_TEMPLATE_DELETE, LANG.UI_GLOBAL_TEMPLATE_DELETE_SUCCESS);
                    } else {
                        UIToastr.showWarning(LANG.UI_GLOBAL_TEMPLATE_DELETE, LANG.UI_GLOBAL_TEMPLATE_DELETE_FAIL);
                    }
                });

            },
            'click .editSetting': function (event, value, row, index) {
                $('#share_task_edit').drawer('show');
                editUuid = row.uuid
                $('#grainness_edit_name').val(row.user_name)
                console.log(row,row)
                var init = {
                    'node_uuid': '5efa0bde-11b6-4c97-802a-4e39833a4f72',
                    'data': {'ip_domain':'192.168.28.14', 'protocol':row.protocol, 'limit_ip':'192.168.28.15,192.168.24.14'},
                    'class': 'col-md-8'
                }
                storageMount.init(init);
            },
            'click #eyeClose': function(event, value, row, index) {
                if($(`.eyeClose-${index} i`).hasClass('vicon-a-yanjing1')) {
                    // $(`.eyeClose-${index} span`).text(value)
                    showPwd(row.task_uuid, row.uuid, index);
                    $(`.eyeClose-${index} i`).removeClass('vicon-a-yanjing1')
                    $(`.eyeClose-${index} i`).addClass('vicon-yanjing')
                } else {
                    $(`.eyeClose-${index} span`).text('')
                    $(`.eyeClose-${index} i`).addClass('vicon-a-yanjing1')
                    $(`.eyeClose-${index} i`).removeClass('vicon-yanjing')
                }

                eyeFlag = false
            }
        }
        let urlPath = $('#data_uuid').val();
        var options = {
            vin_url: "api/v1/recovery/"+ urlPath +"/graininess/network",
            vin_method: "GET",
            vin_params: function () {
                // 所有自定义携带参数，必须return
                var params = {};
                params['search'] = $('#share_task_search_ipt').val();
                return params;
            },
            buttonsToolbar: '#vin_share_job_toolbar .vin_btnToolbar',
            toolbarId: '#vin_share_job_toolbar',
            vin_toolbar: '#vin_share_job_toolbar',
            sortName: 'create_time',
            sortOrder: 'desc',
            changeHeightBtn: false, //改变高度按钮
            pagination: true, //分页
            fullPage: true,
            singleSelect:true,
            detailView: true,
            showExport: false,  // 显示导出按钮
            detailFormatter: current_detail,
            PostBody: function() {
                $('#share_table th[data-field="mount_protocol"]').css('width','10%');
                $('#share_table th[data-field="create_time"]').css('width','12%');
                $('#share_table th[data-field="access_name"]').css('width','8%');
                $('#share_table th[data-field="access_password"]').css('width','12%');
                $('#share_table th[data-field="task_status"]').css('width','8%');

                checkEvent('#share_table', '#delete_share_task');
            },
            onCheck: function () {
                checkEvent('#share_table', '#delete_share_task');
            },
            onUncheck: function () {
                checkEvent('#share_table', '#delete_share_task');
            },
            onCheckAll: function () {
                checkEvent('#share_table', '#delete_share_task');
            },
            onUncheckAll: function () {
                checkEvent('#share_table', '#delete_share_task');
            },
            resizable: true, //可变宽度
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                },
                /*{
                    field: 'mount_path',
                    title: LANG.UI_RECOVERY_GUAZAI_PATH,
                    sortable: false,
                    align: 'left',
                },*/
                {
                    field: 'access_path',
                    title: LANG.UI_REPORT_SHARE_PATH,
                    sortable: true,
                    align: 'left',
                },
                {
                    field: 'mount_protocol',
                    title: LANG.UI_RECOVERY_PROTOCOL,
                    sortable: true,
                    align: 'left',
                    formatter: function (value) {
                        let strategyType;
                        switch (value) {
                            case 1:
                                strategyType = 'NFS';
                                break;
                            case 2:
                                strategyType = 'SMB';
                                break;
                            default:
                                break;
                        }
                        return '<span title="' + strategyType + '">' + strategyType + '</span>';
                    }
                },
                {
                    field: 'create_time',
                    title: LANG.UI_RECOVERY_SHARE_TIME,
                    sortable: true,
                    align: 'left',
                },
                {
                    field: 'access_name',
                    title: LANG.UI_RECOVERY_FANGWEI,
                    sortable: true,
                    align: 'left',
                },
                {
                    field: 'access_password',
                    title: LANG.UI_RECOVERY_FANGWEI_PASSWORD,
                    sortable: true,
                    align: 'left',
                    events: shareOp,
                    formatter: function (value,row,index) {
                        return '<div type = "button" id="eyeClose" class="dropdown-toggle btn-font btn eyeClose-' + index + '" style="margin-left: 0px;padding-left: 0px">'+
                            '<i class="viconfont vicon-a-yanjing1 mr5 c0FBF98"></i>'+
                            '<span class="eyeText"></span>'
                    }
                },
                {
                    field: 'task_status',
                    title: LANG.UI_BLACK_WHITE_STATUS,
                    sortable: true,
                    align: 'left',
                    type: "label",
                },
                // {
                //     title: LANG.UI_PUBLIC_OPERATION,
                //     formatter: operationFormatter,
                //     events: shareOp,
                //     opButton: true,
                //     clickToSelect: false, //不可通过点击行选中
                //     sortable: false, //默认可排序，禁用排序才写此项
                // }
            ],
        }
        $('#share_table').baseTableConfig().init(options);

    };

    var showPwd = function (job_uuid, uuid, index){
        var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
        bootbox.prompt({
            title: LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM,
            inputType: 'password',
            callback: function (result) {
                if (result == null) {
                    $(`.eyeClose-${index} i`).addClass('vicon-a-yanjing1')
                    $(`.eyeClose-${index} i`).removeClass('vicon-yanjing')
                    return;
                }
                var password = encrypt.encrypt(result);
                var params = {
                    password: password,
                    job_uuid: job_uuid,
                    uuid: uuid,
                };
                pAjaxRequest(params, "/api/v1/recovery/graininess/network_look", "GET", function (result) {
                    if (result.success) {
                        $(`.eyeClose-${index} span`).text(result.data)
                    } else {
                        $(`.eyeClose-${index} i`).addClass('vicon-a-yanjing1')
                        $(`.eyeClose-${index} i`).removeClass('vicon-yanjing')
                        UIToastr.showWarning(result.title, result.message);
                    }
                });
            }
        });


    }
    const initListeners = function () {

        $('#share_task_tab .search-btn').on('click', function(){
            $('#share_table').bootstrapTable('refresh', {
                query: {search:$('#share_task_search_ipt').val()}
            });
        })
        $('#share_task_search_ipt').keypress(function (e) {
            if (e.which == 13) {
                $('#share_table').bootstrapTable('refresh', {
                    query: {search:$('#share_task_search_ipt').val()}
                });
            }
        });
        //删除
        $('#delete_share_task').on('click', deleteTask);
        //修改确认按钮
        $('#graininess_edit').on('click',editSubmit);

        // 搜索对象存储
        $('#share_task_search').off().on('click', () => {
            let searchVal = $('#share_task_search_ipt').val();

            if (searchVal) {
                $('#share_table').bootstrapTable('refresh', {query: {search: searchVal}})
            }
        });

        $('#share_task_search_ipt').on('focus', () => {
            $('#share_task_clear_search').removeClass('hide');
        });

        // 清空对象存储搜索
        $('#share_task_clear_search').on('click', () => {
            $('#share_task_search_ipt').val('');
            $('#share_task_clear_search').addClass('hide');
            $('#share_table').bootstrapTable('refresh', {query: {search: ''}})
            $('#share_table').bootstrapTable('resetSearch');
        });

        initUserPassword();
    }
    var current_detail = function (index, row, element) {
        var html = `
        <div style="display: flex;align-items: flex-start">
        <div style="line-height: 20px">${LANG.UI_RECOVERY_GUAZAI_MINGLING}:</div>
        <div class="${row.uuid}-shareList" style="margin-top: 20px"></div>
        </div> 
        `
        $(element).append(html);

        let share_command = row.share_command;
        var htmlList = ``
        if (share_command.linux != undefined) {
            // 使用模板字符串构建HTML
            htmlList += `
                    <div class="fileText">
                        <div title="${LANG.UI_PLATFORM_COPY_TIPS}" data-content="${escapeHtml(share_command['file_explorer'])}"><span>file_explorer：</span>${escapeHtml(share_command['file_explorer'])}</div>
                        <div title="${LANG.UI_PLATFORM_COPY_TIPS}" data-content="${escapeHtml(share_command['linux'])}"><span>linux：</span>${escapeHtml(share_command['linux'])}</div>
                        <div title="${LANG.UI_PLATFORM_COPY_TIPS}" data-content="${escapeHtml(share_command['windows'])}"><span>windows：</span>${escapeHtml(share_command['windows'])}</div>
                    </div>
                `;
        }
        $(`.${row.uuid}-shareList`).append(htmlList)

        if (row.allow_ip_list != '') {
            html = `
        <div style="display: flex;align-items: flex-start">
        <div style="line-height: 20px">${LANG.UI_PLATFORM_RECOVERY_MOUNT_LIMIT_IP}:</div>
        <div style="margin-left: 20px">
         <div class="fileText">
         <div>`+row.allow_ip_list+` </div>
            </div>
        </div>
        </div> 
        `
            $(element).append(html);
        }
    }
    $(document).off('click', '.fileText div').on('click', '.fileText div', async function () {
        try {
            const $element = $(this); // 获取当前点击的 <div>
            const htmlContent = $element.attr('data-content');
            // 如果需要解码转义字符
            // const decodedContent = $('<div>').html(htmlContent).text();
            // await navigator.clipboard.writeText(decodedContent);
            await navigator.clipboard.writeText(htmlContent);
            UIToastr.showSuccess(LANG.UI_PUBLIC_NOTICE, LANG.UI_PLATFORM_COPY_SUCCESS_TIPS);
        } catch (err) {
            // console.error('复制失败:', err);
            // alert('复制失败，请重试！');
            UIToastr.showWarning(LANG.UI_PUBLIC_NOTICE, LANG.UI_PLATFORM_COPY_FAIL_TIPS);
        }
    });
    // 辅助函数：转义HTML特殊字符
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    const deleteTask = function() {
        var template_uuid = getIdSelectedId('#share_table')
        var url ='/api/v1/recovery/'+ template_uuid[0] +'/graininess/operation/network'
        if (template_uuid.length == 0) {
            return;
        }
        //必选
        if (template_uuid.length !=1) {
            return tipsDeleteStrategy();
        }
        bootbox.confirm({
            title: LANG.UI_RECOVERY_DELETE_SHARE_NETWORK,
            message: LANG.UI_RECOVERY_DELETE_NETWORK_SHARE_MAY_RELASH_COVERY,
            callback: function (r) {
                if (!r) {
                    return;
                }

                bootbox.prompt({
                    title: LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM,
                    inputType: 'password',
                    callback: function (result) {
                        if (result == null) return;
                        if (hex_md5(result) == _UserPassword) {
                            Metronic.blockUI({target: '#share_task_tab',animate: true});
                            pAjaxRequest({}, url, 'delete', function (res) {
                                Metronic.unblockUI('#share_task_tab');
                                if (res.success) {
                                    $('#share_table').bootstrapTable('refresh');
                                    return UIToastr.showSuccess(LANG.UI_RECOVERY_DELETE_SHARE_NETWORK, LANG.UI_RECOVERY_DELETE_SHARE_NETWORK_SUCCESS);
                                } else {
                                    UIToastr.showWarning(LANG.UI_RECOVERY_DELETE_SHARE_NETWORK, res.message);
                                }
                            });
                            return true;
                        } else {
                            UIToastr.showWarning(LANG.UI_RECOVERY_DELETE_SHARE_NETWORK, LANG.UI_SETTINGS_STORAGE_SAFE_PWD_ERR);
                            return false;
                        }
                    }
                });
            }
        })
    }
    //修改确认按钮
    const editSubmit = function() {
        let requestParam = storageMount.getAmountInfo();
        if (requestParam == false) {
            return false;
        }
        requestParam.edit_name = $('#grainness_edit_name').val()
        requestParam.edit_password = $('#grainness_edit_password').val()
        let urlPath = '/api/v1/recovery/'+ editUuid +'/graininess/operation/clients'
        pAjaxRequest(requestParam, urlPath, 'put', function (res) {
            if (res.success) {
                $('#share_task_edit').drawer('hide');
                $('#share_table').bootstrapTable('refresh');
                return UIToastr.showSuccess(LANG.UI_GLOBAL_SHARE_EDIT, LANG.UI_GLOBAL_SHARE_EDIT_SUCCESS);
            } else {
                UIToastr.showWarning(LANG.UI_GLOBAL_SHARE_EDIT, LANG.UI_GLOBAL_SHARE_EDIT_FAIL);
            }
        },false)
    }

    //没有选中的策略提示
    const tipsDeleteStrategy = function () {
        return UIToastr.showInfo(LANG.UI_PUBLIC_TIPS, LANG.UI_SHARE_STRATEGY_DELETE_TIPS);
    }

    function  getIdSelectedId(select)
    {
        return $.map($(select).bootstrapTable('getSelections'), function (row) {
            return row.uuid;
        })

    }
    const checkEvent = function (tableId, btnId) {
        let select = $('' + tableId + '').bootstrapTable('getSelections');
        if (select.length == 0) {
            $(btnId).addClass("disabled");
            $(btnId).attr("disabled");
        } else {
            $(btnId).removeClass("disabled");
            $(btnId).removeAttr("disabled");
        }
    }
    //初始化当前用户密码用于删除二次确认
    var initUserPassword = function () {
        pAjaxRequest({}, "/api/v1/users/password", "GET", function (result) {
            if (result.success) {
                _UserPassword = result.data.password;
            }
        });
    }
    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 400;

        $("#share_task_tab .fixed-table-body").css({
            "height": height
        });
    }
    return {
        //main function to initiate the module
        init: function () {
            initDataTable()
            initListeners()
            initTableHeight();
        }
    };

}();

jQuery(document).ready(function() {
    shareTaskTab.init();
});