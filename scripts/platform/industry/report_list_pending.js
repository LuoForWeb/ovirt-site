var ReportList = function () {
    var table = $('#report_table');
    var users = ``; //初始化时存入的用户option
    var userGroups = ``; //初始化时存入的用户组option
    var reportData = [];
    var nowOpRow; //点击操作按钮时将当前正在操作的行赋值过来
    var _UserPassword; //用户独立密码
    var initCustomPasswordFlag = false; //初始化用户独立密码flag
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

    //添加禁止点击的按钮样式
    const addForbidButton = function (uuid, option) {
        $('#report_table #' + uuid + ' .' + option).unbind();
        $('#report_table #' + uuid + ' .' + option + ' a').css("opacity", ".4");
        $('#report_table #' + uuid + ' .' + option + ' a').css("cursor", "not-allowed");
        $('#report_table #' + uuid).on("click", "." + option + " a", function (e) {
            e.stopPropagation();
        });
    }

    const addOpButton = function () {
        var data = table.bootstrapTable("getData");
        for (let i = 0; i < data.length; i++) {
            var isApprove = data[i].approval_flag;
            var uuid = data[i].uuid;
            var status = data[i].status;
            var isCreateUser = data[i].user_flag;
            var isMaster = data[i].master_flag;
            // 测试注释掉
            if (!isApprove || status == STATUS.REJECTED || status == STATUS.REVOKE) {
                addForbidButton(uuid, 'approve');
            }
            if (!isCreateUser || status == STATUS.REVOKE) {
                // 必须是当前用户创建才能操作撤销
                addForbidButton(uuid, 'cancel');
            }
            if (!isCreateUser || status != STATUS.REVOKE) { //已撤销且为当前用户创建的，才能更改审批流
                addForbidButton(uuid, 'editApproval');
                // 才可以编辑
                addForbidButton(uuid, 'edit');
            }
            if (!isMaster && !isApprove) {
                // 只有管理或者当前审批人可以更改审批人
                addForbidButton(uuid, 'editUser');
            }

        }
    }

    const addListeners = function () {
        $('#pending_li').on('click', () => {
            table.bootstrapTable('refresh');
        })

        // 切换添加抄送人员
        $('#add_copy_switch').bootstrapSwitch('onSwitchChange', function (e, data) {
            if (data) {
                $('.copy-send-div').show(); //开
            } else {
                $('.copy-send-div').hide(); //关
            }
        })
        // 删除
        $('#vin_report_pending_toolbar').on('click', '#delete', delReport);
        // 审批提交
        $('#approve_submit').on('click', approveSubmit);

        //更改审批流
        $('#edit_approval_submit').on('click', changeApproval);

        //选择存储用途复选框
        $('#approval_result').find('.icheck').on('ifClicked', modeClick);

        // 分享提交
        $('#share_submit').on('click', shareSubmit);

        // 更改审批人提交
        $('#edit_user_submit').on('click', changeUser);

        //初始化多选下拉框
        $(".selectpicker").selectpicker({
            noneSelectedText: LANG.BILLING_PLEASE_SELECT,
            deselectAllText: LANG.BILLING_DESELECT_ALL,
            selectAllText: LANG.BILLING_SELECT_ALL,
            liveSearchPlaceholder: LANG.BILLING_SEARCH,
            countSelectedText: function () {}
        });

        $('#comment_modal .cancel').on('click', ()=>{
            $('#comment_modal').modal('hide');
        })
    }

    /**
     * @function 初始化审批用户下拉框
     */
    function initUsers() {
        pAjaxRequest({'type': 'user',}, '/api/v1/industry/report/share/users', 'GET', function (d) {
            if (d.success) {
                var data = d;
                var option = ``;
                data.data.forEach(function (item, index) {
                    option += `<option value="${item.user_uuid}">${item.user_name}</option>`
                });
                users += option;
                $('#share_user').append(users);
                $('#edit_user_list').append(users);
            }
        });
    }

    /**
     * @function 初始化用户组下拉框
     */
    function initUserGroups() {
        pAjaxRequest({}, '/api/v1/industry/report/share/users', 'GET', function (d) {
            if (d.success) {
                var data = d;
                var option = ``;
                data.data.forEach(function (item, index) {
                    option += `<option value="${item.user_group_uuid}">${item.user_group_name}</option>`
                });
                userGroups += option;
                $('#share_user_group').append(userGroups);
            }
        });
    }

    const modeClick = function (event) {
        var mode = $(this).data('mode');
        if (event.target.checked) {
            //如果是取消选中
            $('#approval_result').find('input').iCheck("uncheck");
        } else {
            $('#approval_result').find('input').iCheck("uncheck");
            $('#approval_result').find('input[data-mode=' + mode + ']').iCheck("check");
        }
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
            callback: debounce(function(r) {
                if(!r) return;
                for (let i = 0; i < select.length; i++) {
                    uuids.push(select[i].uuid);
                }
                Metronic.blockUI({
                    target: '#pending_div',
                    animate: true
                });
                pAjaxRequest({uuids:uuids}, '/api/v1/industry/report/delete', 'DELETE', (res) => {
                    Metronic.unblockUI('#pending_div');
                    if (operateResponseList(res, LANG.UI_PLATFORM_INDUSTRY_REPORT_DELETE)) {
                        table.bootstrapTable('refresh');
                    }
                });
            }, 300)
        });
    }

    //初始化当前用户独立密码用于删除二次确认
    const getUserCustomPassword = function () {
        pAjaxRequest({}, '/api/v1/industry/report/custom_password', 'GET', function (res) {
            _UserPassword = res.data.custome_password;
        }, false)
    }
    
    const approveSubmit = function () {
        let params = {};
        params.report_uuid = nowOpRow.uuid;
        params.approve_result = $('#approval_result').find('input[type="checkbox"]:checked').attr('data-mode');
        params.approve_advice = $('#approve_advice').val();
        params.user = $('#user').selectpicker('val').join();
        params.user_group = $('#user_group').selectpicker('val').join();
        var initErrorFlag = false;
        if (!params.approve_result) {
            return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_SUBMIT_APPROVE, LANG.UI_PLATFORM_INDUSTRY_PLEASE_SELECT_APPROVE_RESULT);
        }
        message = LANG.UI_PLATFORM_INDUSTRY_VERIFY_CUSTOM_PWD;
        
            bootbox.prompt({
                title: message,
                inputType: 'password',
                placeholder: LANG.UI_PLATFORM_INDUSTRY_PLEASE_INPUT_CUSTOM_PWD,
                callback: function (result) {
                    if (result == null) return;
                    getUserCustomPassword();
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        $('#approve_submit').prop('disabled', true);
                        Metronic.blockUI({
                            target: '#pending_div',
                            animate: true
                        });
                        pAjaxRequest(params, '/api/v1/industry/report/approve', 'POST', (res) => {
                            Metronic.unblockUI('#pending_div');
                            $('#approve_submit').prop('disabled', false);
                            var op = LANG.UI_PLATFORM_INDUSTRY_REPORT_SEND_APPROVE_MSG;
                            if (operateResponseList(res, op)) {
                                table.bootstrapTable('refresh');
                                $('#approve_drawer').drawer('hide');
                                $('#approve_advice').val('');
                            }
                        });
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_PLATFORM_INDUSTRY_REPORT_CUSTOM_PWD_TIP + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }
            });
            var des = '<p class="help-block" style="margin-top:5px">' + LANG.UI_PLATFORM_INDUSTRY_REPORT_CUSTOM_PWD_TIP_PREFIX +`
                    <a class="ajaxify add_classify" name="safety" style="color:#00A3FF">${LANG.UI_PLATFORM_INDUSTRY_REPORT_CUSTOM_PWD_TIP_FIX}</a>` + '</p>';
            $('.bootbox-input').after(des);
            $(".add_classify").off().on('click', ()=>{
                $('#approve_drawer').drawer('hide');
                LOCATION('./content/platform/users/userinfo.php');
                bootbox.hideAll();
            })
    }

/**
 * 提交分享报告的函数
 * 
 * 此函数负责收集用户选择的分享设置（包括报告UUID、通知标志、用户、用户组和审批UUID），
 * 并通过AJAX请求将这些设置提交到服务器如果未选择任何用户或用户组，则显示警告消息
 * 在提交过程中禁用提交按钮并显示加载指示器，提交完成后隐藏加载指示器并根据结果刷新表格
 */
const shareSubmit = function () {
    // 初始化参数对象
    let params = {};
    // 将当前操作行的UUID赋值给参数对象的report_uuid属性
    params.report_uuid = nowOpRow.uuid;
    // 将通知开关的状态赋值给参数对象的notice_flag属性
    params.notice_flag = $('#notice_switch')[0].checked;
    // 将选择的用户转换为字符串并赋值给参数对象的users属性
    params.users = $('#share_user').val().join();
    // 将选择的用户组转换为字符串并赋值给参数对象的user_groups属性
    params.user_groups = $('#share_user_group').val().join();
    // 将当前操作行的approval_uuid赋值给参数对象的approval_uuid属性
    params.approval_uuid = nowOpRow.approval_uuid;
    
    // 检查是否选择了用户或用户组，如果没有，则显示警告消息并返回
    if (params.users == "" && params.user_groups == "") {
        return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_SHARE, LANG.UI_PLATFORM_INDUSTRY_REPORT_SHARE_TIP);
    }
    
    // 在提交前禁用提交按钮以防止重复提交
    $('#share_submit').prop('disabled', true);
    // 显示加载指示器
    Metronic.blockUI({
        target: '#pending_div',
        animate: true
    });
    
    // 发起AJAX请求，提交分享设置
    pAjaxRequest(params, '/api/v1/industry/report/share', 'POST', (res)=>{
        // 隐藏加载指示器
        Metronic.unblockUI('#pending_div');
        // 重新启用提交按钮
        $('#share_submit').prop('disabled', false);
        
        // 定义操作描述
        var op = LANG.UI_PLATFORM_INDUSTRY_REPORT_SEND_SHARE_MSG;
        // 处理响应结果，如果成功则刷新表格并隐藏分享抽屉
        if (operateResponseList(res, op)) {
            table.bootstrapTable('refresh');
            $('#share_drawer').drawer('hide');
        }
    });
}

    const changeUser = function () {
        var selectUser = $('#edit_user_list').val();
        if (selectUser == 0) UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_CHANGE_APPROVE_USER, LANG.UI_PLATFORM_INDUSTRY_REPORT_CHANGE_APPROVE_USER_TIP);

        var params = {};
        params.user_uuid = selectUser;
        params.report_uuid = nowOpRow.uuid;

        // 在提交前禁用提交按钮以防止重复提交
        $('#edit_user_submit').prop('disabled', true);
        // 显示加载指示器
        Metronic.blockUI({
            target: '#pending_div',
            animate: true
        });

        pAjaxRequest(params, '/api/v1/industry/report/change_user', 'POST', (res)=>{
            Metronic.unblockUI('#pending_div');
            $('#edit_user_submit').prop('disabled', false);
            
            // 定义操作描述
            var op = LANG.UI_PLATFORM_INDUSTRY_REPORT_SEND_CHANGE_APPROVE_USER_MSG;
            // 处理响应结果，如果成功则刷新表格并隐藏分享抽屉
            if (operateResponseList(res, op)) {
                table.bootstrapTable('refresh');
                $('#edit_user_drawer').drawer('hide');
            }
        });
    }

    const initTable = function () {
        let options = {
            toolbarId: '#vin_report_pending_toolbar',
            buttonsToolbar: '#vin_report_pending_toolbar .vin_btnToolbar',
            placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
            vin_url: '/api/v1/industry/report',
            vin_method: 'GET',
            vin_params: function () {
                let params = {};
                let search = $('#vin_report_pending_toolbar .report-pending-search').val();
                params.type = ''; //默认为空时待审批，1是已归档
                if ($.trim(search) != '') {
                    $('#searchDiv0 .searchContent0').text('');
                    $('#searchDiv0').hide();

                    $('#report_title0').val('');
                    $('#report_task0').val('');
                    $('#report_agent0').val('');
                    $('#report_template0').val('');
                    $('#report_template0').selectpicker('refresh');

                    params.search = $.trim(search);
                }
                return params;
            },
            sortName: 'report_time',
            sortOrder: 'desc',
            fullPage: true,
            customTool: {
                // beforeInput: `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete"></button></div>`,
                // afterInput: `<button class="btn dropdown-toggle btn-font flex_center btn-title p-lr8 table-toolbar-btn" id="download" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                //                 <i class="viconfont vicon-xiazai"></i>
                //                 <span>` + LANG.UI_PUBLIC_DOWNLOAD + `</span>
                //             </button>`,
            },
            searchInput: true, //搜索框
            searchClass: 'report-pending-search', //自定义的搜索框类名
            searchSelector: '.report-pending-search', //选择使用自定义搜索框
            onCheck: function (row) {
                reportData.push(row.uuid);
                modifyDelStyle('report_table', 'delete');
            },
            onUncheck: function (row) {
                var index = reportData.indexOf(row.uuid); // 查找元素的索引
                if (index !== -1) {
                    reportData.splice(index, 1); // 从数组中删除一个元素
                }
                modifyDelStyle('report_table', 'delete');
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = reportData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index == -1) {
                        reportData.push(row[i].uuid)
                    }
                }
                modifyDelStyle('report_table', 'delete');
            },
            onUncheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = reportData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index != -1) {
                        reportData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
                modifyDelStyle('report_table', 'delete');
            },
            PostBody: function () {
                addOpButton();
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
               /* {
                    field: 'module',
                    title: LANG.UI_SEARCH_MODE_TYPE,
                    sortable: false,
                },*/
                {
                    field: 'user_name',
                    title: LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_CREATE_NAME
                },
                {
                    field: 'report_time',
                    title: LANG.UI_PLATFORM_INDUSTRY_REPORT_CREATE_TIME
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
                                break;
                            case STATUS.ARCHIVED:
                                statusLabel = `label label-sm label-success`;
                                break;
                            case STATUS.APPROVALING:
                                statusLabel = `label label-sm label-info`;
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

                        if (row.approval_flag && value != STATUS.REVOKE && value != STATUS.REJECTED) {
                            // 如果是当前用户审批，展示审批描述，颜色为强提示
                            var approvalList = JSON.parse(row.approval_list);
                            statusLabel = `label label-sm label-warning`;
                            des = approvalList[row.now_user_depth + 1].desc;
                        } else if (value == STATUS.REVOKE || value == STATUS.REJECTED) {
                            // do nothing
                        } else {
                            des = `${LANG.UI_PLATFORM_INDUSTRY_REPORT_WAITTING_APPROVE_PREFIX}${row.now_user_name}${LANG.UI_PLATFORM_INDUSTRY_APPROVE}`;
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
        button += '<li class="view"><a href="javascript:;"><i class="viconfont vicon-a-Eyesyanjing"></i> ' + LANG.UI_PLATFORM_INDUSTRY_REPORT_VIEW + '</a></li>';

        if ($.inArray('p_industry_report_pending_edit', CONF.PERMISSION_ARR) !== -1) {
            // 编辑
            button += '<li class="edit"><a href="javascript:;"><i class="viconfont vicon-a-Editbianji"></i> ' + LANG.UI_FILE_EDIT + '</a></li>';
        }
        if ($.inArray('p_industry_report_pending_approve', CONF.PERMISSION_ARR) !== -1) {
            // 审批
            button += '<li class="approve"><a href="javascript:;" data-toggle="drawer" data-target="#approve_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-shenpi"></i> ' + LANG.UI_PLATFORM_INDUSTRY_APPROVE + '</a></li>';
        }
        if ($.inArray('p_industry_report_pending_approve_change', CONF.PERMISSION_ARR) !== -1) {
            // 审批流
            button += '<li class="approval"><a href="javascript:;" data-toggle="drawer" data-target="#approval_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-shenpiliu"></i> ' + LANG.UI_PLATFORM_INDUSTRY_APPROVAL_VIEW + '</a></li>';

            // 更改审批流
            button += '<li class="editApproval"><a href="javascript:;" data-toggle="drawer" data-target="#edit_approval_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-genggaishenpiliu"></i> ' + LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CHANGE + '</a></li>';
        }
        if ($.inArray('p_industry_report_pending_approve_user_change', CONF.PERMISSION_ARR) !== -1) {
            // 更改审批人
            button += '<li class="editUser"><a href="javascript:;" data-toggle="drawer" data-target="#edit_user_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-a-File-editingbianjiwenjian"></i> ' + LANG.UI_PLATFORM_INDUSTRY_REPORT_CHANGE_APPROVE_USER + '</a></li>';

        }
        if ($.inArray('p_industry_report_pending_approve_back', CONF.PERMISSION_ARR) !== -1) {
            // 撤销
            button += '<li class="cancel"><a href="javascript:;"><i class="viconfont vicon-chexiao"></i> ' + LANG.UI_PLATFORM_INDUSTRY_APPROVAL_REVOKE + '</a></li>';

        }
        if ($.inArray('p_industry_report_pending_share', CONF.PERMISSION_ARR) !== -1) {
            // 分享
            button += '<li class="share"><a href="javascript:;" data-toggle="drawer" data-target="#share_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-gongxiang"></i> ' + LANG.UI_PLATFORM_INDUSTRY_SHARE + '</a></li>';

        }
        if ($.inArray('p_industry_report_pending_look_comment', CONF.PERMISSION_ARR) !== -1) {
            // 评论列表
            button += '<li class="comment"><a href="javascript:;"><i class="viconfont vicon-a-Eyesyanjing"></i> ' + LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT_VIEW + '</a></li>';

        }
        button += '</ul></div>';
        return button;
    }

    const operates = {
        'click .view': function (event, value, row, index) {
            JobReportDetail.init({'uuid': row.uuid, 'pre': 1})
           // LOCATION('./content/platform/industry/job_report.php?uuid=' + row.uuid);
        },
        'click .edit': function (event, value, row, index) {
            // 打开模态
            JobReportDetail.init({'uuid': row.uuid, 'pre': 2})
        },
        'click .approve': function (event, value, row, index) {
            nowOpRow = row;
            $('#approval_result').find('input').iCheck('uncheck');
            $('#approve_drawer select[name="user"]').empty().append(users).selectpicker('refresh');
            $('#approve_drawer select[name="user_group"]').empty().append(userGroups).selectpicker('refresh');
            backupFillUsers(row); //回填抄送人
        },
        'click .approval': function (event, value, row, index) {
            nowOpRow = row;
            $('#report_name').text(row.name);
            $('#depth_div').empty().append(getApprovalList(row.approval_list, row));
        },
        'click .editApproval': function (event, value, row, index) {
            nowOpRow = row;
            $('#report_name_edit').text(row.name);
        },
        'click .editUser': function (event, value, row, index) {
            nowOpRow = row;
            $('#report_name_edit_user').text(row.name);
        },
        'click .cancel': function (event, value, row, index) {
            cancelApproval(row);
        },
        'click .share': function (event, value, row, index) {
            nowOpRow = row;
            $('#report_name_share').text(row.name);
            let userList = $('#share_drawer select[name="share_user"]');
            let userGroupList = $('#share_drawer select[name="share_user_group"]');
            userList.selectpicker('refresh');
            userGroupList.selectpicker('refresh');
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
                {
                    field: 'user_type',
                    title: LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT_SOURCE,
                    formatter: function (index, row) {
                        if (row.user_type == 1) {
                            return `<span title="${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND}">${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND}</span>`;
                        } else if (row.user_type == 2)
                        {
                            return `<span title="${LANG.UI_PLATFORM_INDUSTRY_SHARE}">${LANG.UI_PLATFORM_INDUSTRY_SHARE}</span>`;
                        }
                    },
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
        var position = '';
        for (let i = 0; i < depthContent.length; i++) {
            var eachContent = depthContent[i];
            labelClass = 'label-info';
            switch (eachContent.status) {
                case APPROVAL_RESULT.PENDING:
                    color = '#1296db';
                    icon = 'vicon-a-dengdaidaishenpi';
                    stageDes = eachContent.desc ?? LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING;
                    // 当前审批的用户观察为强提示
                    if (row.approval_flag && row.now_user_uuid == eachContent.user_uuid) {
                        labelClass = 'label-warning';
                        color = "#F19F00";
                    } else {
                        labelClass = 'label-info';
                        color = "#1296db";
                    }
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

            if (eachContent.position) {
                position = `(${eachContent.position})`;
            } else {
                position = ``;
            }

            if (i == depthContent.length - 1) {
                //最后一层
                isEndClass = 'end';
            }
            html += `<div class="depth-parent ${isEndClass}">
                        <div class="depth-title">
                            <div class="circle" style="color:${color}"><i class="viconfont ${icon}"></i></div><span class="label label-sm ${labelClass} ml15">${eachContent.desc ?? LANG.UI_PLATFORM_INDUSTRY_APPROVE}</span>
                            <span class="approve-time" style="margin-left:auto;white-space:nowrap">${eachContent.create_time}</span>
                        </div>
                        <div class="depth-connect mt8 approve-user"><span style="font-size: 14px;color: #999999;line-height: 16px;">${titleDes}：</span><span>${eachContent.user_name}<span class="position">${position}</span></span></div>
                        ${advice}
                    </div>`;
        }


        return html;

    }

    const changeApproval = function () {
        let params = {};
        params.report_uuid = nowOpRow.uuid;
        params.new_approval_uuid = $('#approve_list').val();
        if (params.new_approval_uuid == 0) {
            return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CHANGE, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_SELECT);
        }
        $('#edit_approval_submit').prop('disabled', true);
        Metronic.blockUI({target: '#pending_div',animate: true});
        pAjaxRequest(params, "/api/v1/industry/report/change_approval", "POST", function (res) {
            $('#edit_approval_submit').prop('disabled', false);
            Metronic.unblockUI('#pending_div');
            var op = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CHANGE;
            if (operateResponseList(res, op)) {
                table.bootstrapTable('refresh');
                $('#edit_approval_drawer').drawer('hide');
            }
        });
    }
    /**
     * 撤销审批函数
     *
     * @param {Object} row - 包含审批任务信息的对象
     * @param {string} row.uuid - 审批任务的唯一标识符
     */
    const cancelApproval = function (row) {
        var uuid = row.uuid;
        bootbox.confirm({
            title: `<i class="viconfont vicon-chexiao"></i> ${LANG.UI_PLATFORM_INDUSTRY_REVOKE_APPROVAL}`,
            message: LANG.UI_PLATFORM_INDUSTRY_REVOKE_APPROVAL_TIP,
            callback: debounce(function(r) {
                if(!r) return;
                Metronic.blockUI({target: '#pending_div',animate: true});
                pAjaxRequest({'report_uuid': uuid}, "/api/v1/industry/report/cancel", "POST", function (res) {
                    Metronic.unblockUI('#pending_div');
                    var op = LANG.UI_PLATFORM_INDUSTRY_SEND_REVOKE_APPROVAL_MSG;
                    if (operateResponseList(res, op)) {
                        table.bootstrapTable('refresh');
                    }
                });
            }, 300)
        });
    }

    const backupFillUsers = function (row) {
        let depth = JSON.parse(row.approval_list);
        let newDepth = depth.slice(1, -1); //去除头尾，只保留真实审批层级
        console.log(newDepth);
        $('#approve_drawer select[name="user"]').selectpicker('val',newDepth[row.now_user_depth].users.user).selectpicker('refresh');
        $('#approve_drawer select[name="user_group"]').selectpicker('val',newDepth[row.now_user_depth].users.user_group).selectpicker('refresh');
        $('#copy_switch').bootstrapSwitch('state', newDepth[row.now_user_depth].users.notice);
        
        let userDes = newDepth[row.now_user_depth].users.user_name;
        let userGroupDes = newDepth[row.now_user_depth].users.user_group_name;
        $('#approve_drawer .cs-info').empty().text(`${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND_USER}：${userDes}；${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND_USER_GROUP}：${userGroupDes}`);
    }

    //初始化当前创建的审批流
    const initApproval = function () {
        pAjaxRequest({offset:0}, '/api/v1/approvals/list', 'GET', (res) => {
            console.log(res);
            var data = res.data.rows;
            var select = $('#approve_list');
            select.empty();
            var option = $("<option>").text(LANG.UI_JOB_SELECT).val('0');
            select.append(option);
            for (var i = 0; i < data.length; i++) {
                option = $("<option>").text(data[i].name).val(data[i].approval_uuid);
                select.append(option);
            }
            select.val('0');
        });
    }

    return {
        init: function () {
            initTable();
            addListeners();
            initApproval(); //初始化当前创建的审批流
            initUsers();
            initUserGroups();
        },
        refresh: function (options){
            table.bootstrapTable('refresh', {
                query: options.queryParams
            });
        }
    }
}();

$(document).ready(function () {
    ReportList.init();
});