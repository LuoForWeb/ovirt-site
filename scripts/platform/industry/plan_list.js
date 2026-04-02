var planList = function () {
    return function (){
        const STATUS = {
            PENDING: 0,
            ARCHIVED: 1,
            APPROVALING: 2,
            REJECTED: 3,
            REVOKE: 4
        };

        const APPROVAL_RESULT = {
            PENDING: 0,
            PASSED: 1,
            REJECTED: 2,
        };
        let _UserPassword; //用户独立密码
        let users = ``; //初始化时存入的用户option
        let userGroups = ``; //初始化时存入的用户组option
        let nowOpRow; //点击操作按钮时将当前正在操作的行赋值过来
        let level = 0;
        let mainUuid = 0; // 查看某一个大版本内的所有小版本
        let tables = 'plan_data_table';
        let toolbarId = '#vin_plan_data_toolbar';
        let searchClass = 'plan-data-search';
        var itemPlanEditor = null; // 方案内容编辑器
        var editorHeight = 400;// 编辑器的高度
        var planType = 1;
        var planUuid = 0; // 标记选中的方案
        var nowAction = 0; //  0 look 1 add 2 edit 3 copy

        function getTable() {
            return $('#' + tables);
        }
        function getLevel() {
            return level;
        }
        function getTables(level) {
            level = parseInt(level);
            if (level == 0) {
                // 数据验证
                tables = 'plan_data_table';
            } else if (level == 1) {
                // 功能验证
                tables = 'plan_function_table';
            } else {
                // 分享、抄送
                tables = 'plan_share_table';
            }
            return tables;
        }

        const initTable = function () {
            let columns = [
                {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                },
                {
                    field: 'name',
                    title: LANG.UI_PLATFORM_INDUSTRY_PLAN_TITLE,
                    formatter: function (value, row, index, field){
                        let ext = '';
                        if (getLevel() == 1) {
                            ext = '_function';
                        }
                        if (mainUuid == 0 && getLevel() < 2 && $.inArray('p_industry_plan_look_version'+ext, CONF.PERMISSION_ARR) !== -1) {
                            return '<a href="javascript:void(0)" class="view_plan" title="'+LANG.UI_PLATFORM_INDUSTRY_PLAN_LOOK_VERSION+'" data-uuid="'+row.main_uuid+'">'+value+'</a>';
                        } else {
                            return `<span title="` + value + `">` + value + `</span>`;
                        }
                    }
                },
            ];
            if (level == 2) {
                // 分享、抄送 显示方案类型
                columns.push(
                    {
                        field: 'plan_type',
                        title: LANG.UI_PLATFORM_INDUSTRY_PLAN_TYPE_TITLE,
                        formatter: function (value, row, index, field){
                            if (value == 1) {
                                // 数据验证
                                return LANG.UI_PLATFORM_INDUSTRY_PLAN_TYPE_TITLE1;
                            } else {
                                // 功能验证
                                return LANG.UI_PLATFORM_INDUSTRY_PLAN_TYPE_TITLE2;
                            }
                        }
                    }
                );
            }
            if (mainUuid == 0 && getLevel() < 2) {
                // latest
                columns.push(
                    {
                        field: 'version',
                        title: LANG.UI_PLATFORM_INDUSTRY_PLAN_LATEST_VERSION
                    },
                    {
                        field: 'total_version',
                        title: LANG.UI_PLATFORM_INDUSTRY_PLAN_VERSION_NUM
                    },
                );
            } else {
                // one big version
                columns.push(
                    {
                        field: 'version',
                        title: LANG.UI_PLATFORM_INDUSTRY_PLAN_VERSION
                    }
                );
            }
            columns.push(
                {
                    field: 'update_time',
                    title: LANG.UI_PLATFORM_INDUSTRY_PLAN_UPDATE_TIME
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: function (value, row, index) {
                        var statusLabel = ``;
                        var des = row.status_value;
                        switch (value) {
                            case STATUS.PENDING: // 0
                                statusLabel = `label label-sm label-info`;
                                break;
                            case STATUS.ARCHIVED: // 1
                                statusLabel = `label label-sm label-success`;
                                break;
                            case STATUS.APPROVALING: // 2
                                statusLabel = `label label-sm label-info`;
                                break;
                            case STATUS.REJECTED: // 3
                                statusLabel = `label label-sm label-danger`;
                                break;
                            case STATUS.REVOKE: // 4
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
                        } else if (value == STATUS.REVOKE || value == STATUS.REJECTED || value == STATUS.ARCHIVED) {
                            // do nothing
                        } else {
                            des = `${LANG.UI_PLATFORM_INDUSTRY_REPORT_WAITTING_APPROVE_PREFIX}${row.now_user_name}${LANG.UI_PLATFORM_INDUSTRY_APPROVE}`;
                        }

                        return `<span class='${statusLabel}'>${des}</span>`;
                    }
                },
                {
                    field: 'approval_time',
                    title: LANG.UI_PLATFORM_INDUSTRY_PLAN_APPROVAL_TIME
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    field: 'operates',
                    clickToSelect: false, //不可通过点击行选中
                    formatter: opButton,
                    width: "125px",
                    opButton: true,
                    events: operates, //单元点击事件
                    forceHide: true,
                }
            );

            var afterInput = ``;
            let ext = '';
            if (getLevel() == 1) {
                ext = '_function';
            }
            if ($.inArray('p_industry_plan_add' + ext, CONF.PERMISSION_ARR) !== -1 && mainUuid == 0 && getLevel() != 2) {
                afterInput += `<button class="btn dropdown-toggle btn-font flex_center btn-title p-lr8 table-toolbar-btn" id="add_plan`+getLevel()+`" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia"></i>
                                <span>` + LANG.UI_PUBLIC_ADD + `</span>
                            </button>`;
            }

            let options = {
                toolbarId: toolbarId,
                buttonsToolbar: toolbarId + ' .vin_btnToolbar',
                placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
                vin_url: '/api/v1/industry/plan',
                vin_method: 'GET',
                vin_params: function () {
                    let params = {};
                    let search = $(toolbarId + ' .'+searchClass).val();
                    if (mainUuid != 0) {
                        params.main_uuid = mainUuid;
                    } else if (getLevel() == 0) {
                        params.type = 1; // 数据验证
                    } else if (getLevel() == 1) {
                        params.type = 2; // 数据验证
                    } else {
                        params.type = 3; // 分享、抄送
                    }

                    if ($.trim(search) != '') {

                        $('#searchDiv'+getLevel()+' .searchContent'+getLevel()).text('');
                        $('#searchDiv'+getLevel()).hide();

                        $('#plan_title'+getLevel()).val('');
                        $('#plan_num'+getLevel()).val('');
                        $('#plan_version'+getLevel()).val('');
                        $('#plan_approval'+getLevel()).val('');
                        $('#plan_approval'+getLevel()).selectpicker('refresh');

                        params.search = $.trim(search);
                    }
                    return params;
                },
                fullPage: true,
                sortName: 'update_time',
                sortOrder: 'desc',
                customTool: {
                    beforeInput: ``,
                    afterInput: afterInput,
                },
                searchInput: true, //搜索框
                searchClass: searchClass, //自定义的搜索框类名
                searchSelector: '.' + searchClass, //选择使用自定义搜索框
                PostBody: function () {
                    $('#'+tables+' th[data-field="num"]').css('width','8%');
                    $('#'+tables+' th[data-field="version"]').css('width','8%');
                    $('#'+tables+' th[data-field="total_version"]').css('width','8%');
                    $('#'+tables+' th[data-field="plan_type"]').css('width','8%');
                    $('#'+tables+' th[data-field="update_time"]').css('width','10%');
                    $('#'+tables+' th[data-field="approval_time"]').css('width','10%');
                    $('#'+tables+' th[data-field="status"]').css('width','10%');
                    $('#'+tables+' th[data-field="operates"]').css('width','8%');
                    addOpButton();
                },
                showExport: false, //是否显示导出按钮
                showColumns: true, //是否开启列选择按钮

                columns: columns
            };
            getTable().bootstrapTable('destroy');
            getTable().baseTableConfig().init(options);
        }

        const addOpButton = function () {
            var data = getTable().bootstrapTable("getData");
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
                if (!isCreateUser || status != STATUS.REVOKE) {
                    //已撤销且为当前用户创建的，才能更改审批流
                    addForbidButton(uuid, 'editApproval');
                }

                if (!isCreateUser || status == STATUS.APPROVALING) {
                    //且为当前用户创建的，审批中，无法更新操作
                    addForbidButton(uuid, 'edit');
                }

                if (!isMaster && !isApprove) {
                    // 只有管理或者当前审批人可以更改审批人
                    addForbidButton(uuid, 'editUser');
                }
            }
            $('.view_plan').off('click').on('click', function (){
                let planUuid2 = $(this).attr('data-uuid');
                // 查看子版本
                // 打开抽屉，重新初始化这个js
                var planLists = planList();
                planLists.init({'level':0, 'main_uuid': planUuid2})
                // 页面加载时初始化一次
                $('#more_version_drawer').drawer('show');

                const zIndex = 10050;

                // 3.给新的蒙层添加z-index
                $('.drawer-backdrop[data-backdrop="pointListDrawer"]').css('z-index', `${zIndex - 2}`);
                $('#more_version_drawer').css('z-index', `${zIndex - 1}`);
            })
        }

        //添加禁止点击的按钮样式
        const addForbidButton = function (uuid, option) {
            $('#' + tables + ' #' + uuid + ' .' + option).unbind();
            $('#' + tables + '  #' + uuid + ' .' + option + ' a').css("opacity", ".4");
            $('#' + tables + '  #' + uuid + ' .' + option + ' a').css("cursor", "not-allowed");
            $('#' + tables + '  #' + uuid).on("click", "." + option + " a", function (e) {
                e.stopPropagation();
            });
        }

        const opButton = function (value, row, index, field) {
            var button = '<div class="btn-group">';

            if (index > 8 && getLevel() < 2) {
                button = '<div class="btn-group dropup">';
            }
            let ext = '';
            if (getLevel() == 1) {
                ext = '_function';
            }

            button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                '</button>' +
                '<ul class="dropdown-menu min-width100" role="menu" id="' + row.uuid + '">';

            // 方案详情
            button += '<li class="view"><a href="javascript:;"><i class="viconfont vicon-fangan"></i> ' + LANG.UI_PLATFORM_INDUSTRY_PLAN_DETAIL + '</a></li>';
            if (getLevel() < 2) {
                if ($.inArray('p_industry_plan_edit'+ext, CONF.PERMISSION_ARR) !== -1) {
                    // 编辑
                    button += '<li class="edit"><a href="javascript:;"><i class="viconfont vicon-a-Editbianji"></i> ' + LANG.UI_FILE_EDIT + '</a></li>';
                }
                if ($.inArray('p_industry_plan_copy'+ext, CONF.PERMISSION_ARR) !== -1) {
                    // 复制
                    button += '<li class="copy" data-level="'+getLevel()+'"><a href="javascript:;"><i class="viconfont vicon-a-Copyfuzhi"></i> ' + LANG.UI_PLATFORM_INDUSTRY_PLAN_COPY + '</a></li>';
                }

                if ($.inArray('p_industry_plan_look_version'+ext, CONF.PERMISSION_ARR) !== -1 && mainUuid == 0) {
                    // 查看子版本
                    button += '<li class="look"><a href="javascript:;"><i class="viconfont vicon-a-View-grid-listliebiaochakanmoshi"></i> ' + LANG.UI_PLATFORM_INDUSTRY_PLAN_LOOK_VERSION + '</a></li>';
                }

                if ($.inArray('p_industry_plan_approve'+ext, CONF.PERMISSION_ARR) !== -1 && row.status != STATUS.ARCHIVED) {
                    // 审批
                    button += '<li class="approve"  data-level="'+getLevel()+'"><a href="javascript:;" data-toggle="drawer" data-target="#approve_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-shenpi"></i> ' + LANG.UI_PLATFORM_INDUSTRY_APPROVE + '</a></li>';
                }

                if ($.inArray('p_industry_plan_approve_look'+ext, CONF.PERMISSION_ARR) !== -1) {
                    // 审批流
                    button += '<li class="approval"><a href="javascript:;" data-toggle="drawer" data-target="#approval_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-shenpiliu"></i> ' + LANG.UI_PLATFORM_INDUSTRY_APPROVAL_VIEW + '</a></li>';
                }

                if ($.inArray('p_industry_plan_approve_change'+ext, CONF.PERMISSION_ARR) !== -1 && row.status != STATUS.ARCHIVED) {
                    // 更改审批流
                    button += '<li class="editApproval"  data-level="'+getLevel()+'"><a href="javascript:;" data-toggle="drawer" data-target="#edit_approval_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-genggaishenpiliu"></i> ' + LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CHANGE + '</a></li>';
                }
                if ($.inArray('p_industry_plan_approve_user_change'+ext, CONF.PERMISSION_ARR) !== -1 && row.status != STATUS.ARCHIVED) {
                    // 更改审批人
                    button += '<li class="editUser" data-level="'+getLevel()+'"><a href="javascript:;" data-toggle="drawer" data-target="#edit_user_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-a-File-editingbianjiwenjian"></i> ' + LANG.UI_PLATFORM_INDUSTRY_REPORT_CHANGE_APPROVE_USER + '</a></li>';
                }
                if ($.inArray('p_industry_plan_approve_back'+ext, CONF.PERMISSION_ARR) !== -1 && row.status != STATUS.ARCHIVED) {
                    // 撤销
                    button += '<li class="cancel"  data-level="'+getLevel()+'"><a href="javascript:;"><i class="viconfont vicon-chexiao"></i> ' + LANG.UI_PLATFORM_INDUSTRY_APPROVAL_REVOKE + '</a></li>';
                }

                if ($.inArray('p_industry_plan_share'+ext, CONF.PERMISSION_ARR) !== -1) {
                    // 分享
                    button += '<li class="share"><a href="javascript:;" data-toggle="drawer" data-target="#share_drawer" aria-haspopup="true" aria-expanded="false"><i class="viconfont vicon-gongxiang"></i> ' + LANG.UI_PLATFORM_INDUSTRY_SHARE + '</a></li>';
                }
                if ($.inArray('p_industry_plan_look_comment'+ext, CONF.PERMISSION_ARR) !== -1) {
                    // 评论列表
                    button += '<li class="comment"><a href="javascript:;"><i class="viconfont vicon-a-Eyesyanjing"></i> ' + LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT_VIEW + '</a></li>';
                }
            } else {
                // 评论
                if ($.inArray('p_industry_plan_share_operation', CONF.PERMISSION_ARR) !== -1) {
                    button += '<li class="remark"><a href="javascript:;"><i class="viconfont vicon-shenpiliu"></i> ' + LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT + '</a></li>';
                }
            }

            button += '</ul></div>';
            return button;
        }

        const operates = {
            'click .view': function (event, value, row, index) {
                // 方案详情
                nowAction = 0;
                $('#nowAction').val(0);
                $('#planUUid').val(0);
                planUuid = 0;
                getPlanDetail(row.uuid);
            },
            'click .edit': function (event, value, row, index) {
                // 编辑
                nowAction = 2;
                $('#nowAction').val(2);
                getPlanDetail(row.uuid);
            },
            'click .copy': function (event, value, row, index) {
                // 复制
                var $approveItem = $(event.currentTarget);
                level = $approveItem.attr('data-level');
                $('#planLevel').val(level);
                nowAction = 3;
                $('#nowAction').val(3);
                getPlanDetail(row.uuid);
            },
            'click .look': function (event, value, row, index) {
                // 查看子版本
                // 打开抽屉，重新初始化这个js
                var planLists = planList();
                planLists.init({'level':0, 'main_uuid': row.main_uuid})
                $('#more_version_drawer').drawer('show');

                const zIndex = 10050;

                // 3.给新的蒙层添加z-index
                $('.drawer-backdrop[data-backdrop="pointListDrawer"]').css('z-index', `${zIndex - 2}`);
                $('#more_version_drawer').css('z-index', `${zIndex - 1}`);
            },
            'click .approve': function (event, value, row, index) {
                // 审批
                var $approveItem = $(event.currentTarget);
                level = $approveItem.attr('data-level');
                $('#planLevel').val(level);
                nowOpRow = row;
                $('#planUUid').val(row.uuid);
                $('#approval_result').find('input').iCheck('uncheck');
                $('#approve_drawer select[name="user"]').empty().append(users).selectpicker('refresh');
                $('#approve_drawer select[name="user_group"]').empty().append(userGroups).selectpicker('refresh');
                backupFillUsers(row); //回填抄送人
            },
            'click .approval': function (event, value, row, index) {
                // 查看审批流
                nowOpRow = row;
                $('#planUUid').val(row.uuid);
                $('#report_name').text(row.name);
                $('#depth_div').empty().append(getApprovalList(row.approval_list, row));
            },
            'click .editApproval': function (event, value, row, index) {
                // 更改审批流
                var $approveItem = $(event.currentTarget);
                level = $approveItem.attr('data-level');
                $('#planLevel').val(level);
                nowOpRow = row;
                $('#planUUid').val(row.uuid);
                $('#report_name_edit').text(row.name);
            },
            'click .editUser': function (event, value, row, index) {
                // 更改审批人
                var $approveItem = $(event.currentTarget);
                level = $approveItem.attr('data-level');
                $('#planLevel').val(level);
                nowOpRow = row;
                $('#planUUid').val(row.uuid);
                $('#report_name_edit_user').text(row.name);
            },
            'click .cancel': function (event, value, row, index) {
                // 撤销
                var $approveItem = $(event.currentTarget);
                level = $approveItem.attr('data-level');
                $('#planLevel').val(level);
                cancelApproval(row);
            },
            'click .share': function (event, value, row, index) {
                nowOpRow = row;
                $('#planUUid').val(row.uuid);
                $('#approvalUuid').val(row.approval_uuid);
                $('#report_name_share').text(row.name);
                let userList = $('#share_drawer select[name="share_user"]');
                let userGroupList = $('#share_drawer select[name="share_user_group"]');
                userList.selectpicker('refresh');
                userGroupList.selectpicker('refresh');
            },
            'click .comment': function (event, value, row, index) {
                nowOpRow = row;
                $('#planUUid').val(row.uuid);
                $('#comment_modal').drawer('show');
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
            'click .remark': function (event, value, row, index) {
                nowOpRow = row;
                $('#planUUid').val(row.uuid);
                $('#remark_drawer').drawer('show');
                $('#report_name_remark').text(row.name);
                $('#remark_content').empty().val(row.content_remark);
                if (row.content_remark != "") {
                    $('#remark_content').prop('disabled', true);
                    $('#remark_submit').hide();
                } else {
                    $('#remark_content').prop('disabled', false);
                    $('#remark_submit').show();
                }
            }
        }

        const backupFillUsers = function (row) {
            let depth = JSON.parse(row.approval_list);
            let newDepth = depth.slice(1, -1); //去除头尾，只保留真实审批层级

            $('#approve_drawer select[name="user"]').selectpicker('val',newDepth[row.now_user_depth].users.user).selectpicker('refresh');
            $('#approve_drawer select[name="user_group"]').selectpicker('val',newDepth[row.now_user_depth].users.user_group).selectpicker('refresh');
            $('#copy_switch').bootstrapSwitch('state', newDepth[row.now_user_depth].users.notice);

            let userDes = newDepth[row.now_user_depth].users.user_name;
            let userGroupDes = newDepth[row.now_user_depth].users.user_group_name;
            $('#approve_drawer .cs-info').empty().text(`${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND_USER}：${userDes}；${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND_USER_GROUP}：${userGroupDes}`);
        }

        // 查看审批流
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
                    pAjaxRequest({'report_uuid': uuid, 'is_plan': 1}, "/api/v1/industry/report/cancel", "POST", function (res) {
                        Metronic.unblockUI('#pending_div');
                        var op = LANG.UI_PLATFORM_INDUSTRY_SEND_REVOKE_APPROVAL_MSG;
                        if (operateResponseList(res, op)) {
                            level = $('#planLevel').val();
                            tables = getTables(level);
                            $('#' + tables).bootstrapTable('refresh', {query:{type:level + 1}});
                        }
                    });
                }, 300)
            });
        }

        /**
         * 初始化抄送表格
         * @param {Object} row - 触发初始化操作的行对象，包含报告UUID
         */
        const initCopySendTable = function (row) {
            let option = {
                toolbarId: toolbarId,
                buttonsToolbar: toolbarId + ' .vin_btnToolbar',
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
                toolbarId: toolbarId,
                buttonsToolbar: toolbarId + ' .vin_btnToolbar',
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

        const addListeners = function () {
            $('#plan_ul_div li').on('click', () => {
                // getTable().bootstrapTable('refresh');
            })

            // 添加新方案
            $('#add_plan0').off('click').on('click', () => {
               showDrawer();
               planType = 1;
               $('#planType').val(1);
            })
            $('#add_plan1').off('click').on('click', () => {
                showDrawer();
                planType = 2;
                $('#planType').val(2);
            })
            function showDrawer(){
                // 1、打开抽屉
                $('#plan_add_drawer').drawer('show');
                // 2、清空输入
                $('#plan_name').val('');
                $('#serial_number').val('');
                $('#approval_uuid').val('0');
                itemPlanEditor.setContent('');
                // 3、初始化状态为 1（add）
                nowAction = 1;
                $('#nowAction').val(1);
                $('#planUUid').val(0);
                planUuid = 0;

                $('#drawer-plan-title').find('.title-i').removeClass('vicon-danchuangtianjia1');
                $('#drawer-plan-title').find('.title-i').removeClass('vicon-a-Editbianji');
                $('#drawer-plan-title').find('.title-i').removeClass('vicon-a-Copyfuzhi');
                $('#drawer-plan-title').find('.title-i').removeClass('vicon-xiangqing');

                $('#drawer-plan-title').find('.title-i').addClass('vicon-danchuangtianjia1');
                $('#drawer-plan-titles').html(LANG.UI_PLATFORM_INDUSTRY_PLAN_ADD_TITLE);

            }

            // 添加、编辑和复制方案的保存事件
            $('#plan_submit').off('click').on('click', () => {
                let name = $.trim($('#plan_name').val());
                if (!customInputValidate('string', name)) {
                    return false;
                }
                if (name == '') {
                    UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_INDUSTRY_PLAN_TITLE_REQUIRE);
                    return false;
                }

                let serial_number = $.trim($('#serial_number').val());
                if (!customInputValidate('string', serial_number)) {
                    return false;
                }
                if (serial_number == '') {
                   // UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_INDUSTRY_PLAN_NUM_REQUIRE);
                   // return false;
                }

                let approval_uuid = $('#approval_uuid').val();
                if (approval_uuid == 0) {
                    UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_INDUSTRY_PLAN_APPROVAL_REQUIRE);
                    return false;
                }
                var item_plan = itemPlanEditor.getContent(); // 方案的内容


                if (item_plan == '') {
                   // UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_INDUSTRY_PLAN_CONTENT_REQUIRE);
                  //  return false;
                }
                let nowActions = $('#nowAction').val();
                let planUuids = $('#planUUid').val();
                let planTypes = parseInt($('#planType').val());
                let url = '/api/v1/industry/plan';
                let action = 'POST';
                if (nowActions == 2) {
                    // edit
                    url = '/api/v1/industry/plan/' + planUuids;
                } else if (nowActions == 3) {
                    // copy
                    url = '/api/v1/industry/plan/' + planUuids;
                    action = 'PUT';
                }
                let data = {
                    name: name,
                    serial_number: serial_number,
                    approval_uuid: approval_uuid,
                    content: item_plan,
                    plan_type: planTypes
                };
                Metronic.blockUI({target: '#plan_add_drawer',animate: true,cenrerY: true});
                pAjaxRequest(data, url, action, function (result) {
                    Metronic.unblockUI('#plan_add_drawer');
                    if (result.code == 0) {
                        $('#plan_add_drawer').drawer('hide');
                        // 返回列表
                        UIToastr.showSuccess(LANG.UI_PUBLIC_TIPS, result.message);
                        if (planTypes == 1) {
                            $('#plan_data_table').bootstrapTable('refresh', {query:{type:$('#planType').val()}});
                        } else {
                            $('#plan_function_table').bootstrapTable('refresh', {query:{type:$('#planType').val()}});
                        }
                    } else {
                        UIToastr.showError(LANG.UI_PUBLIC_TIPS, result.message);
                    }
                });
            })

            // 切换添加抄送人员
            $('#add_copy_switch').bootstrapSwitch('onSwitchChange', function (e, data) {
                if (data) {
                    $('.copy-send-div').show(); //开
                } else {
                    $('.copy-send-div').hide(); //关
                }
            })

            // 审批提交
            $('#approve_submit').off('click').on('click', approveSubmit);

            //更改审批流
            $('#edit_approval_submit').off('click').on('click', changeApproval);

            //选择存储用途复选框
            $('#approval_result').find('.icheck').on('ifClicked', modeClick);

            // 分享提交
            $('#share_submit').off('click').on('click', shareSubmit);

            // 更改审批人提交
            $('#edit_user_submit').off('click').on('click', changeUser);

            // 评论
            $('#remark_submit').off('click').on('click', ()=>{
                let params = {};
                params.comment = $('#remark_content').val();
                params.report_uuid = $('#planUUid').val();
                params.is_plan = 1;
                if (params.comment == "") {
                    // 评论内容不能为空!
                    return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT, LANG.UI_PLATFORM_INDUSTRY_REPORT_SUBMIT_COMMENT_VIEW_TIP);
                }
                $('#remark_submit').prop('disabled', true);
                Metronic.blockUI({target: '#comment_modal',animate: true});
                pAjaxRequest(params, '/api/v1/industry/report/remark', 'POST', (res)=>{
                    $('#remark_submit').prop('disabled', false);
                    Metronic.unblockUI('#comment_modal');
                    var op = LANG.UI_PLATFORM_INDUSTRY_REPORT_COMMENT;
                    if (operateResponseList(res, op)) {
                        $('#plan_share_table').bootstrapTable('refresh', {query:{type:3}});
                        $('#remark_drawer').drawer('hide');
                    }
                })
            })

            //初始化多选下拉框
            $(".selectpicker").selectpicker({
                noneSelectedText: LANG.BILLING_PLEASE_SELECT,
                deselectAllText: LANG.BILLING_DESELECT_ALL,
                selectAllText: LANG.BILLING_SELECT_ALL,
                liveSearchPlaceholder: LANG.BILLING_SEARCH,
                countSelectedText: function () {}
            });

            getUserCustomPassword();

            initEditor();
        }

        // 获取方案详情
        const getPlanDetail = function (id){
            let url = '/api/v1/industry/plan/'+id;
            Metronic.blockUI({target: '#plan_add_drawer',animate: true,cenrerY: true});
            pAjaxRequest({}, url, 'GET', function (result) {
                Metronic.unblockUI('#plan_add_drawer');
                if (result.code != 0) {
                    return UIToastr.showError(LANG.UI_PUBLIC_TIPS, result.message);
                }

                planUuid = result.data.plan_uuid;
                $('#planUUid').val(planUuid);
                showDivByType(result);
            });
        }

        // 根据类型不同，初始化不同的显示
        const showDivByType = function (result){
            nowAction = $('#nowAction').val();
            if (nowAction == 0) {
                // look
                $('#plan_look_drawer').drawer('show');
                $('#plan_name_look').html(decodeURIComponent(result.data.name));
                $('#plan_version_look').html(result.data.version);
                $('#plan_serial_number_look').html(result.data.serial_number);
                $('#plan_approval_uuid_look').html(result.data.approval_name);
                $('#plan_content_look').html(result.data.content);
                return;
            }

            // 填充表单
            $('#plan_name').val(decodeURIComponent(result.data.name));
            $('#serial_number').val(result.data.serial_number);
            $('#approval_uuid').val(result.data.approval_uuid);
            itemPlanEditor.setContent(result.data.content);
            // 1、打开抽屉
            $('#plan_add_drawer').drawer('show');
            // 2、初始化编辑器
            $('#drawer-plan-title').find('.title-i').removeClass('vicon-danchuangtianjia1');
            $('#drawer-plan-title').find('.title-i').removeClass('vicon-a-Editbianji');
            $('#drawer-plan-title').find('.title-i').removeClass('vicon-a-Copyfuzhi');
            $('#drawer-plan-title').find('.title-i').removeClass('vicon-xiangqing');
            if (nowAction == 2) {
                // edit
                $('#drawer-plan-title').find('.title-i').addClass('vicon-a-Editbianji');
                $('#drawer-plan-titles').html(LANG.UI_PLATFORM_INDUSTRY_PLAN_EDIT_TITLE);
            } else if (nowAction == 3){
                // copy
                $('#drawer-plan-title').find('.title-i').addClass('vicon-a-Copyfuzhi');
                $('#drawer-plan-titles').html(LANG.UI_PLATFORM_INDUSTRY_PLAN_COPY_TITLE);
            } else {
                // look
                $('#drawer-plan-title').find('.title-i').addClass('vicon-xiangqing');
                $('#drawer-plan-titles').html(LANG.UI_PLATFORM_INDUSTRY_PLAN_LOOK_TITLE);
            }
        }

        //初始化当前用户独立密码用于删除二次确认
        const getUserCustomPassword = function () {
            pAjaxRequest({}, '/api/v1/industry/report/custom_password', 'GET', function (res) {
                _UserPassword = res.data.custome_password;
            }, false)
        }

        // 审批
        const approveSubmit = function () {
            let params = {};
            params.report_uuid = $('#planUUid').val();
            params.approve_result = $('#approval_result').find('input[type="checkbox"]:checked').attr('data-mode');
            params.approve_advice = $('#approve_advice').val();
            params.user = $('#user').selectpicker('val').join();
            params.user_group = $('#user_group').selectpicker('val').join();
            params.is_plan = 1;
            var initErrorFlag = false;
            if (!params.approve_result) {
                return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_SUBMIT_APPROVE, LANG.UI_PLATFORM_INDUSTRY_PLEASE_SELECT_APPROVE_RESULT);
            }

            bootbox.prompt({
                title: LANG.UI_PLATFORM_INDUSTRY_VERIFY_CUSTOM_PWD,
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
                                level = $('#planLevel').val();
                                tables = getTables(level);
                                $('#' + tables).bootstrapTable('refresh', {query:{type:level + 1}});

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

        // 更改审批流
        const changeApproval = function () {
            let params = {};
            params.report_uuid = $('#planUUid').val();
            params.is_plan = 1;
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
                    $('#edit_approval_drawer').drawer('hide');
                    level = $('#planLevel').val();
                    tables = getTables(level);
                    $('#' + tables).bootstrapTable('refresh', {query:{type:level + 1}});
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

        // 分享
        const shareSubmit = function () {
            // 初始化参数对象
            let params = {};
            // 将当前操作行的UUID赋值给参数对象的report_uuid属性
            params.report_uuid = $('#planUUid').val();
            // 将通知开关的状态赋值给参数对象的notice_flag属性
            params.notice_flag = $('#notice_switch')[0].checked;
            // 将选择的用户转换为字符串并赋值给参数对象的users属性
            params.users = $('#share_user').val().join();
            // 将选择的用户组转换为字符串并赋值给参数对象的user_groups属性
            params.user_groups = $('#share_user_group').val().join();
            // 将当前操作行的approval_uuid赋值给参数对象的approval_uuid属性
            params.approval_uuid = $('#approvalUuid').val();
            params.is_plan = 1;

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
                    getTable().bootstrapTable('refresh', {query:{type:$('#planType').val()}});
                    $('#share_drawer').drawer('hide');
                }
            });
        }

        // 更改审批人
        const changeUser = function () {
            var selectUser = $('#edit_user_list').val();
            if (selectUser == 0) UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_CHANGE_APPROVE_USER, LANG.UI_PLATFORM_INDUSTRY_REPORT_CHANGE_APPROVE_USER_TIP);

            var params = {};
            params.user_uuid = selectUser;
            params.report_uuid = $('#planUUid').val();
            params.is_plan = 1;

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
                    $('#edit_user_drawer').drawer('hide');
                    level = $('#planLevel').val();
                    tables = getTables(level);
                    $('#' + tables).bootstrapTable('refresh', {query:{type:level + 1}});
                }
            });
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

                var select = $('#approval_uuid');
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

        // 初始化编辑器
        function initEditor(){
            if (itemPlanEditor && itemPlanEditor.destroy) {
                try {
                    itemPlanEditor.destroy();
                } catch (e) {
                    console.warn('UEditor destroy error:', e);
                }
            }
            itemPlanEditor = null; // 明确置空
            // 初始化编辑器
            itemPlanEditor = UE.getEditor('content_editor', {
                // 隐藏工具栏
                // toolbars: [],
                // 隐藏元素路径提示
                elementPathEnabled: false,
                initialFrameHeight:editorHeight - 80,
                wordCountMsg: LANG.UI_PLATFORM_INDUSTRY_EDITOR_LEFT + ' {#count} '+LANG.UI_PLATFORM_INDUSTRY_EDITOR_RIGHT
            });
            listenEditor(itemPlanEditor, 'content');
        }

        // 编辑器监听事件
        function listenEditor(editor, id){
            // 等待编辑器加载完成
            var is_show = false;
            editor.ready(function() {
                // 鼠标离开编辑器区域时隐藏工具栏
                $('#' + id + '_editor').find('*').filter(function() {
                    return this.id && this.id.endsWith('_toolbarbox');
                }).hide();
                $('#' + id + '_editor').find('*').filter(function() {
                    return this.id && this.id.endsWith('_iframeholder');
                }).css({
                    'height': editorHeight
                });
                setTimeout(function() {
                    const $editor = $('.edui-default .edui-editor');
                    $editor.css('overflow-x', 'hidden');
                }, 100);

                // 获取工具栏 DOM 元素
                $('#plan_content_div .item-plan-right').on('click', function (){
                    if (is_show) {
                        // 鼠标离开编辑器区域时隐藏工具栏
                        $('#' + id + '_editor').find('*').filter(function() {
                            return this.id && this.id.endsWith('_toolbarbox');
                        }).hide();
                        $('#' + id + '_editor').find('*').filter(function() {
                            return this.id && this.id.endsWith('_iframeholder');
                        }).css({
                            'height': editorHeight
                        });
                        $(this).html('<i class="viconfont vicon-gongjuyincang"></i>');
                        $(this).attr('title', LANG.UI_PLATFORM_INDUSTRY_EDITOR_TOOLS);
                        is_show = false;
                    } else {
                        // 鼠标进入编辑器区域时显示工具栏
                        $('#' + id + '_editor').find('*').filter(function() {
                            return this.id && this.id.endsWith('_toolbarbox');
                        }).show();
                        $('#' + id + '_editor').find('*').filter(function() {
                            return this.id && this.id.endsWith('_iframeholder');
                        }).css({
                            'height': editorHeight - 80
                        });
                        $(this).attr('title', LANG.UI_PLATFORM_INDUSTRY_EDITOR_TOOLS2);
                        $(this).html('<i class="viconfont vicon-gongju"></i>');
                        is_show = true;
                    }
                })
            });
        }

        // 动态的生成一个遮罩层

        return {
            init: function (options = {}) {
                if (options.level != undefined) {
                    level = options.level;
                } else {
                    level = 0;
                }
                if (options.main_uuid != undefined) {
                    mainUuid = options.main_uuid;
                } else {
                    mainUuid = 0;
                }
                if (level == 0) {
                    // 数据验证
                    tables = 'plan_data_table';
                    toolbarId = '#vin_plan_data_toolbar';
                    searchClass = 'plan-data-search';
                } else if (level == 1) {
                    // 功能验证
                    tables = 'plan_function_table';
                    toolbarId = '#vin_plan_function_toolbar';
                    searchClass = 'plan-function-search';
                } else {
                    // 分享、抄送
                    tables = 'plan_share_table';
                    toolbarId = '#vin_plan_share_toolbar';
                    searchClass = 'plan-share-search';
                }

                if (mainUuid) {
                    tables = 'plan_table_version';
                    toolbarId = '#vin_plan_toolbar_version';
                    searchClass = 'plan-search_version';
                }

                initTable();
                addListeners();
                initApproval(); //初始化当前创建的审批流
                initUsers();
                initUserGroups();
            },
            refresh: function (options) {
                if (options.level != undefined) {
                    level = options.level;
                } else {
                    level = 0;
                }
                if (options.main_uuid != undefined) {
                    mainUuid = options.main_uuid;
                } else {
                    mainUuid = 0;
                }
                if (level == 0) {
                    // 数据验证
                    tables = 'plan_data_table';
                    toolbarId = '#vin_plan_data_toolbar';
                    searchClass = 'plan-data-search';
                } else if (level == 1) {
                    // 功能验证
                    tables = 'plan_function_table';
                    toolbarId = '#vin_plan_function_toolbar';
                    searchClass = 'plan-function-search';
                } else {
                    // 分享、抄送
                    tables = 'plan_share_table';
                    toolbarId = '#vin_plan_share_toolbar';
                    searchClass = 'plan-share-search';
                }

                if (mainUuid) {
                    tables = tables + '_version';
                    toolbarId = toolbarId + '_version';
                    searchClass = searchClass + '_version';
                }

                getTable().bootstrapTable('refresh', {
                    query: options.queryParams
                });
            }
        };
    }
}();
