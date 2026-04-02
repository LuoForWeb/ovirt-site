var Approval = function () {
    var table = $('#approval_table');
    var users = ``; //初始化时存入的用户option
    var userGroups = ``; //初始化时存入的用户组option
    var editRow; //当前修改的行
    var approvalData = [];
    var copyItem; //记录当前正在操作抄送的item的ID
    var id = 0;
    const APPROVAL_STAGE = {
        PENDING: 1,
        PASS: 2,
        REJECT: 3,
    }
    /**
     * @function 事件监听
     */
    function addListeners() {
        //初始化多选下拉框
        $(".selectpicker").selectpicker({
            noneSelectedText: LANG.BILLING_PLEASE_SELECT,
            deselectAllText: LANG.BILLING_DESELECT_ALL,
            selectAllText: LANG.BILLING_SELECT_ALL,
            liveSearchPlaceholder: LANG.BILLING_SEARCH,
            countSelectedText: function () {}
        });
        // 点击新建按钮
        $('#vin_approval_toolbar').on('click', '#add', function () {
            $('#approval_drawer #titleDes').text(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_ADD);
            $('#approvalForm #submit').attr('data-action', 'add'); //点击添加时，drawer的submit属性是添加
            resetForm();
            id = 0;
        })

        $(".add_classify").on('click', ()=>{
            $('#approval_drawer').drawer('hide');
        })

        // 启用
        $('#vin_approval_toolbar').on('click', '#unlock', function (){
            if (getChooseUserUuids()) {
                checkOperateAuth(getChooseUserUuids(), unlockApproval)
            }
        });

        // 禁用
        $('#vin_approval_toolbar').on('click', '#lock', function (){
            if (getChooseUserUuids()) {
                checkOperateAuth(getChooseUserUuids(), lockApproval)
            }
        });

        // 分组
        $('#vin_approval_toolbar').on('click', '#classify', goClassify);

        // 删除
        $('#vin_approval_toolbar').on('click', '#delete', function (){
            if (getChooseUserUuids()) {
                checkOperateAuth(getChooseUserUuids(), delApproval)
            }
        });

        $('#submit').on('click', function () {
            var action = $('#submit').attr('data-action');
            addSubmit(action);
        })
        //删除层级
        $('#approval_drawer').on('click', '.close-btn', function () {
            if ($('select[name="depth"]').length == 1) {
                return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_DELETE_FAIL, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_DELETE_FAIL_TIP);
            }
            //判断上方是否有箭头，如果有就删除，没有就代表这个层级是第一层，则删除下面的箭头
            if ($(this).parent().parent().parent().prev('.arrow').length == 0) {
                $(this).parent().parent().parent().next('.arrow').remove();
            } else {
                $(this).parent().parent().parent().prev('.arrow').remove();
            }
            $(this).parent().parent().parent().remove();
        })

        // 添加层级
        $('.add-depth').on('click', function () {
            id += 1;
            var temp = `<div class="arrow" style="color:#EAEAEA">
                            <i class="viconfont vicon-shenpiliujiantou"></i>
                        </div>
                        <div class="col-md-11 sort-div-parent">
                            <div class="sort-div" id="item${id}">
                                <!-- <i class="viconfont vicon-tuozhuai"></i> -->
                                <div style="display: flex; align-items: center;" class="col-md-12">
                                    <select class="form-control select2me" style="width: auto;" name="type">
                                        <option value="1">${LANG.UI_PLATFORM_INDUSTRY_USER}</option>
                                        <option value="2">${LANG.UI_PLATFORM_INDUSTRY_USER_GROUP}</option>
                                    </select>
                                    <select class="form-control select2me ml8" name="depth" style="width: 40%;">
                                        <option value="0">${LANG.UI_USER_ALLOCATION_SELECT}</option>
                                        ${users}
                                    </select>
                                    <input type="text" name="description" class="form-control ml8" style="width: 117px;"
                                        autocomplete="off" placeholder="${LANG.UI_PLATFORM_INDUSTRY_CUSTOM_DESC}" value="">
                                    <a class="ml8 copy-send" style="color:#00A3FF">${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND}</a>
                                    <span class="ml8 close-btn"><i class="viconfont vicon-a-Reduce-onejianshao"></i></span>
                                </div>
                            </div>
                            <span class="cs-info ml15 display-none"></span>
                        </div>`;
            $('#depth').append(temp);
            // makeDraggable('.sort-div'); //增加层级后再初始化一次拖拽方法
        });

        // 切换用户用户组
        $('#depth').on('change', 'select[name="type"]', function () {
            var type = $(this).val();
            var parent = $(this).parent();
            var select = parent.find('select[name="depth"]');
            if (type == 1) {
                select.empty().append(`<option value="0">${LANG.UI_USER_ALLOCATION_SELECT}</option>`).append(users);
            } else {
                select.empty().append(`<option value="0">${LANG.UI_PLATFORM_INDUSTRY_APPROVAL_SELECT_USER_GROUP}</option>`).append(userGroups);
            }
        });

        // 抄送
        $('#depth').on('click', '.copy-send', function (event) {
            var itemId = $(this).parent().parent().prop('id');
            var userList = $('#copy_send_modal select[name="user"]');
            var userGroupList = $('#copy_send_modal select[name="user_group"]');
            copyItem = itemId;
            $('#copy_send_modal').show();
            userList.empty().append(users);
            userGroupList.empty().append(userGroups);
            userList.selectpicker('refresh');
            userGroupList.selectpicker('refresh');
            backupFillCopySend(); //点击抄送回填抄送人等
        })

        // 抄送模态控制（因为是在抽屉上再弹出模态，如果用.modal的话，动画会把模态卡住，所以只能使用原始的show和hide操作）
        $('#copy_send_modal .cancel').on('click', ()=>{
            $('#copy_send_modal').hide();
        })
        $('#copy_send_modal .close').on('click', ()=>{
            $('#copy_send_modal').hide();
        })

        // 抄送人确定
        $('#copy_send_modal .add_submit').on('click', ()=>{
            var user = $('#copy_send_modal button[data-id="user"]').attr('title');
            var userGroup = $('#copy_send_modal button[data-id="user_group"]').attr('title');
            var userValue = $('#copy_send_modal select[name="user"]').selectpicker('val');
            var userGroupValue = $('#copy_send_modal select[name="user_group"]').selectpicker('val');
            var noticeFlag = false;
            if (userValue.length == 0) {
                user = LANG.UI_PUBLIC_NOTHING;
            }
            if (userGroupValue.length == 0) {
                userGroup = LANG.UI_PUBLIC_NOTHING;
            }

            if ($('#notice_switch')[0].checked) {
                noticeFlag = true;
            }

            var item = $('#' + copyItem);
            item.parent().find('.cs-info').empty().text(`${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND_USER}：${user}；${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND_USER_GROUP}：${userGroup}`).show();
            item.parent().find('.cs-info').attr('data-user', userValue.join()).attr('data-group', userGroupValue.join()).attr('data-notice', noticeFlag);
            if (userValue.length == 0 && userGroupValue.length == 0) {
                item.parent().find('.cs-info').empty().hide();
            }
            console.log(userValue.join(), userGroupValue.join());
            $('#copy_send_modal').hide();
        })
    }

    /**
     * 跳转到分组审批设置页面
     * 该函数通过调用LOCATION函数，导航到特定的URL地址，用于处理平台设置中的分组审批设置
     */
    function goClassify() {
        LOCATION('./content/platform/industry/approval_classify.php');
    }

    /**
     * @function 启用审批流程
     */
    function unlockApproval() {
        var op = CONF.FLAG.SET; //启用
        var opName = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_UNLOCK;
        var url = '/api/v1/approvals/unlock';
        enableOrDisableApprovalSubmit(op, url, opName);
    }

    /**
     * @function 禁用审批流程
     */
    function lockApproval() {
        var op = CONF.FLAG.UNSET; //禁用
        var opName = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_LOCK;
        var url = '/api/v1/approvals/lock';
        enableOrDisableApprovalSubmit(op, url, opName);
    }

    // 获取选择的用户uuid集合
    function getChooseUserUuids(){
        var select = table.bootstrapTable('getSelections');
        var uuids = [];
        for (let i = 0; i < select.length; i++) {
            uuids.push(select[i].create_uuid);
        }
        if (uuids.length == 0) {
            UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_WAITTING_APPROVAL,LANG.UI_PLATFORM_INDUSTRY_PLEASE_SELECT_APPROVAL);
            return false;
        }
        return {
            type: 1,
            user_uuid: uuids.join(','),
            auth: ''
        };
    }

    /**
     * 启用/禁用审批提交
     * 
     * @param {number} op 操作类型，1为启用，2为禁用
     * @param {string} url 请求的URL地址
     */
    function enableOrDisableApprovalSubmit(op, url, opName) {
        var select = table.bootstrapTable('getSelections');
        var uuids = [];
        for (let i = 0; i < select.length; i++) {
            uuids.push(select[i].approval_uuid);
            if (select[i].status == 1 && op == CONF.FLAG.SET) {
                return UIToastr.showWarning(opName, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_UNLOCK_TIP);
            }
            if (select[i].status == 2 && op == CONF.FLAG.UNSET) {
                return UIToastr.showWarning(opName, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_LOCK_TIP);
            }
        }
        if (uuids.length == 0) {
            return UIToastr.showWarning(opName, LANG.UI_PLATFORM_INDUSTRY_PLEASE_SELECT_APPROVAL);
        }
        Metronic.blockUI({
            target: '#approval_tab',
            animate: true
        });
        pAjaxRequest({
            'uuids': uuids,
            'enable_flag': op
        }, url, 'PUT', function (d) {
            Metronic.unblockUI('#approval_tab');
            if (operateResponseList(d, opName)) {
                table.bootstrapTable('refresh');
            }
        });
    }

    /**
     * 删除选中的审批流程
     * 该函数首先获取用户在表格中选中的项，然后确认是否选择了至少一项
     * 删除操作完成后，函数会刷新表格以反映最新的数据状态
     */
    function delApproval () {
        var select = table.bootstrapTable('getSelections');
        if (select.length == 0) {
            return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_PLEASE_SELECT_APPROVAL);
        }
        var uuids = [];
        for (let i = 0; i < select.length; i++) {
            uuids.push(select[i].approval_uuid);
        }
        Metronic.blockUI({
            target: '#approval_tab',
            animate: true
        });
        pAjaxRequest({
            'approval_list': uuids
        }, '/api/v1/approvals/list', 'DELETE', function (d) {
            Metronic.unblockUI('#approval_tab');
            if (operateResponseList(d, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_DELETE)) {
                $('#delete').addClass('exch-forbid-event').removeClass('green-haze');
		        $('#delete').parent().css({"cursor": "not-allowed"});
                table.bootstrapTable('refresh');
            }
        });
    }

    /**
     * @function 初始化审批用户下拉框
     */
    function initUsers() {
        pAjaxRequest({
            'offset': 0,
            'limit': 10000
        }, '/api/v1/users', 'GET', function (d) {
            if (d.success) {
                var data = d;
                var option = ``;
                data.data.rows.forEach(function (item, index) {
                    option += `<option value="${item.user_uuid}">${item.user_name}</option>`
                });
                users += option;
                $('select[name="depth"]').append(users);
            }
        });
    }

    /**
     * @function 初始化用户组下拉框
     */
    function initUserGroups() {
        pAjaxRequest({
            'offset': 0,
            'limit': 10000
        }, '/api/v1/usergroups', 'GET', function (d) {
            if (d.success) {
                var data = d;
                var option = ``;
                data.data.rows.forEach(function (item, index) {
                    option += `<option value="${item.user_group_uuid}">${item.user_group_name}</option>`
                });
                userGroups += option;
            }
        });
    }
    
    /**
     * 初始化分组下拉列表
     * 该函数通过发送AJAX请求获取分组数据，并将其填充到名为"classify"的下拉列表中
     */
    function initClassify() {
        pAjaxRequest({
            'offset': 0
        }, '/api/v1/approvals/classify', 'GET', function (d) {
            if (d.success) {
                var data = d;
                data.data.rows.forEach(function (item, index) {
                    var option = `<option value="${item.classify_uuid}">${item.name}</option>`;
                    $('select[name="classify"]').append(option);
                })
            }
        })
    }

    /**
     * @function 拖拽
     */
    function makeDraggable(selector) {
        var dragParent; //获取拖拽的dom的父级容器
        // 先去掉原有的拖动
        $(".selector").draggable("destroy");
        $(".selector").droppable("destroy");
        
        // 重新初始化
        $(selector).draggable({
            axis: "y",
            containment: '#depth', // 限制拖拽在#container内  
            revert: true, // 拖拽后返回原位
            start: function (event, ui) {
                dragParent = $(this).parent();
            },
        });

        $(selector).droppable({
            accept: '.sort-div',
            drop: function (event, ui) {
                // 当一个元素被放置到另一个元素上时，交换它们的位置
                var draggedId = $(ui.draggable).attr('id');
                var droppedOnId = $(this).attr('id');
                var droppedOnParent = $('#' + droppedOnId).parent(); //获取接受的dom的父级容器
                if (draggedId !== droppedOnId) {
                    // 交换两个元素的位置
                    var droppedOnElement = $('#' + droppedOnId);

                    dragParent.append(droppedOnElement);
                    droppedOnParent.append(ui.draggable);
                }
            }
        });
    }

    /**
     * @function 添加/修改审批流程提交
     */
    function addSubmit(action) {
        var data = {};
        var depthContent = [];
        var isValid = true;
        var title = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_ADD_FAIL_TIP;
        if (action == 'edit') {
            title = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_EDIT_FAIL_TIP;
        }

        $('.sort-div').each(function (index, item) {
            var eachContent = {};
            var depthName = $(item).find('select[name="depth"]').val();
            var description = $(item).find('input[name="description"]').val();
            var copyUser = $(item).parent().find('.cs-info').attr('data-user');
            var copyUserGroup = $(item).parent().find('.cs-info').attr('data-group');

            var user = $('#copy_send_modal button[data-id="user"]').attr('title');
            var userGroup = $('#copy_send_modal button[data-id="user_group"]').attr('title');
            
            copyUser = copyUser ? copyUser.split(',') : [];
            copyUserGroup = copyUserGroup ? copyUserGroup.split(',') : [];

            if (description == '') {
                description = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_PENDING;
            }
            if (depthName == '0') {
                isValid = false;
            }
            eachContent.approval_user_uuid = $(item).find('select[name="depth"] option:selected').val();
            eachContent.approval_user_name = $(item).find('select[name="depth"] option:selected').text();
            eachContent.type = $(item).find('select[name="type"] option:selected').val();
            eachContent.users = {};
            eachContent.users.user = copyUser;
            eachContent.users.user_name = copyUser.length == 0 ? LANG.UI_PUBLIC_NOTHING : user;
            eachContent.users.user_group = copyUserGroup;
            eachContent.users.user_group_name = copyUserGroup.length == 0 ? LANG.UI_PUBLIC_NOTHING : userGroup;
            var notice =  $(item).parent().find('.cs-info').attr('data-notice');
            if (notice == undefined || notice == '' || notice == 'false') {
                notice = false;
            } else {
                notice = true;
            }
            eachContent.users.notice = notice;
            eachContent.position = ""; //添加时职位默认空
            eachContent.description = description; // 详情信描述息，表示默认值为待审批的输入框的值
            depthContent.push(eachContent);
        })
        
        if ($('#name').val() == '') {
            return UIToastr.showWarning(title, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_EDIT_CANNOT_EMPTY_NAME);
        }

        if ($('select[name="classify"]').val() == '0' || $('select[name="classify"]').val() == null) {
            return UIToastr.showWarning(title, LANG.UI_PLATFORM_INDUSTRY_GROUP_SELECT_TIPS);
        }

        if (!isValid) {
            return UIToastr.showWarning(title, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_EDIT_CANNOT_EMPTY_DEPTH);
        }

        // if ($('#actions').val() == '0') {
        //     return UIToastr.showWarning(title, '请选择类型');
        // }

        data.name = $('#name').val();
        data.pid = $('select[name="classify"]').val();
        data.depth = $('.sort-div-parent').length;
        data.status = $('#status').get(0).checked;
        data.action = $('#actions').val();
        data.content = depthContent;
        if (action == 'add') {
            addRequest(data);
        } else if (action == 'edit') {
            data.approval_uuid = editRow.approval_uuid;
            editRequest(data);
        }

    }

    /**
     * 添加发送请求
     * 
     * 该函数用于发送添加请求到服务器
     * 
     * @param {Object} data - 请求的数据，通常是一个对象
     */
    function addRequest(data) {
        Metronic.blockUI({
            target: '#approvalForm',
            animate: true
        });
        pAjaxRequest(data, '/api/v1/approvals/list', 'POST', function (res) {
            Metronic.unblockUI('#approvalForm');
            if (operateResponseList(res, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_ADD)) {
                $('#approval_drawer').drawer('hide');
                table.bootstrapTable('refresh');
            }
        });
    }

    /**
     * 修改发送请求
     * 
     * 该函数用于发送修改请求到服务器
     * 
     * @param {Object} data - 请求的数据，通常是一个对象
     */
    function editRequest(data) {
        Metronic.blockUI({
            target: '#approvalForm',
            animate: true
        });
        pAjaxRequest(data, '/api/v1/approvals/list', 'PUT', function (res) {
            Metronic.unblockUI('#approvalForm');
            if (operateResponseList(res, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_EDIT)) {
                $('#approval_drawer').drawer('hide');
                table.bootstrapTable('refresh');
            }
        });
    }

    function getDepthContent(depthContent, createName) {
        var html = '';
        var isPass = 0; //是否通过, 0否，1是，2驳回
        var stageDes = LANG.UI_PLATFORM_INDUSTRY_START_APPROVE;
        var color = '#2A87C8'; //默认蓝色
        var labelClass = 'label-success'; //默认label
        var icon = 'vicon-tongguo'; //默认通过
        html += `<div class="depth-parent">
                    <div class="depth-title">
                        <div class="circle" style="color:${color}"><i class="viconfont ${icon}"></i></div><span class="label label-sm ${labelClass} ml15" id="create_user">${stageDes}</span>
                    </div>
                    <div class="depth-connect mt8"><span style="
                    font-size: 14px;
                    color: #999999;
                    line-height: 16px;
                ">${LANG.UI_PLATFORM_INDUSTRY_START_APPROVE_USER}：</span><span id="create_user">${createName}</span></div>
                </div>`;
        for (let i = 0; i < depthContent.length; i++) {
            var eachContent = depthContent[i];
            color = '#1296db';
            labelClass = 'label-info';
            icon = "vicon-a-dengdaidaishenpi";
            // switch (eachContent.approval_stage) {
            //     case APPROVAL_STAGE.PENDING:
            //         color = '#F0F0F0';
            //         labelClass = 'label-info';
            //         stageDes = '待审批';
            //         isPass = 9;
            //         break;
            //     case APPROVAL_STAGE.PASS:
            //         color = '#0FBF98';
            //         labelClass = 'label-success';
            //         stageDes = '审批通过';
            //         isPass = 1;
            //         break;
            //     case APPROVAL_STAGE.REJECT:
            //         color = '#F1416C';
            //         labelClass = 'label-danger';
            //         stageDes = '驳回';
            //         isPass = 2;
            //         break;
            // }

            html += `<div class="depth-parent">
                        <div class="depth-title">
                            <div class="circle" style="color:${color}"><i class="viconfont ${icon}"></i></div><span class="label label-sm ${labelClass} ml15" id="create_user">${LANG.UI_PLATFORM_INDUSTRY_APPROVE}</span>
                        </div>
                        <div class="depth-connect mt8"><span style="
                        font-size: 14px;
                        color: #999999;
                        line-height: 16px;
                    ">${LANG.UI_PLATFORM_INDUSTRY_APPROVE_USER}：</span><span>${eachContent.approval_user_name}</span></div>
                    </div>`;

        }

        html += `<div class="depth-parent end">
                    <div class="depth-title">
                        <div class="circle" style="color:#2A87C8"><i class="viconfont vicon-tongguo"></i></div><span class="label label-sm label-success ml15" id="create_user">${LANG.UI_PLATFORM_INDUSTRY_APPROVE_COMPLETE}</span>
                    </div>
                    <div class="depth-connect mt8"><span style="
                    font-size: 14px;
                    color: #999999;
                    line-height: 16px;
                ">${LANG.UI_PLATFORM_INDUSTRY_APPROVE_USER}：</span><span>system</span></div>
                </div>`;
        return html;
    }

    /**
     * @function 表格初始化
     */
    function initTable() {
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

            button += '<li class="view"><a href="javascript:;" data-toggle="drawer" data-target="#view_drawer" aria-haspopup="true" aria-expanded="false" ><i class="viconfont vicon-a-Eyesyanjing"></i> ' + LANG.UI_PUBLIC_LOOK + ' </a></li>';
            if ($.inArray('p_industry_approval_modify', CONF.PERMISSION_ARR) !== -1) {
                // 修改
                button += '<li class="edit"><a href="javascript:;"><i class="viconfont vicon-edit-new"></i> ' + LANG.UI_FILE_EDIT + ' </a></li>';
            }

            button += '</ul></div>';
            return button;
        }

        var approvalOp = {
            'click .view': function (e, value, row, index) {
                var name = row.name;
                var depthContent = row.content;
                $('#name_display').text(name);
                $('#depth_div').empty().append(getDepthContent(depthContent, row.create_name));
            },

            'click .edit': function (e, value, row, index) {
                editRow = row;
                var data = {
                    type: 1,
                    user_uuid: row.create_uuid,
                    auth: ''
                };
                checkOperateAuth(data, function (){
                    $('#approval_drawer').drawer('show');
                    $('#approval_drawer #titleDes').text(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_EDIT);
                    $('#approvalForm #submit').attr('data-action', 'edit'); //点击修改时，drawer的submit属性是修改
                    var userList = $('#copy_send_modal select[name="user"]');
                    var userGroupList = $('#copy_send_modal select[name="user_group"]');
                    userList.empty().append(users);
                    userGroupList.empty().append(userGroups);
                    userList.selectpicker('refresh');
                    userGroupList.selectpicker('refresh');
                    backupFill(row);
                })
            }
        }

        var beforeInput = ``;
        if ($.inArray('p_industry_approval_delete', CONF.PERMISSION_ARR) !== -1) {
            beforeInput = `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete"></button></div>`
        }
        var afterInput = ``;
        if ($.inArray('p_industry_approval_add', CONF.PERMISSION_ARR) !== -1) {
            afterInput += `<button class="btn dropdown-toggle btn-font flex_center btn-title p-lr8 table-toolbar-btn" id="add" data-toggle="drawer" data-target="#approval_drawer" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia"></i>
                                <span>` + LANG.UI_PUBLIC_ADD + `</span>
                            </button> `;
        }
        if ($.inArray('p_industry_approval_enable', CONF.PERMISSION_ARR) !== -1) {
            afterInput += `<button class="btn table-toolbar-btn" id="unlock">
                                <i class="viconfont vicon-a-Unlockjiesuo-0111 mr4"></i>
                                <span>` + LANG.BILLING_ON_LOCK + `</span>
                            </button>`;
        }

        if ($.inArray('p_industry_approval_disable', CONF.PERMISSION_ARR) !== -1) {
            afterInput += `<button class="btn table-toolbar-btn" id="lock">
                                <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i>
                                <span>` + LANG.BILLING_OFF_LOCK + `</span>
                            </button>`;
        }

        if ($.inArray('industry_approval_group', CONF.PERMISSION_ARR) !== -1) {
            afterInput += `<button class="btn table-toolbar-btn" id="classify">
                                <i class="viconfont vicon-fenlei mr4"></i>
                                <span>` + LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_GROUP + `</span>
                            </button>`;
        }

        let options = {
            toolbarId: '#vin_approval_toolbar',
            buttonsToolbar: '#vin_approval_toolbar .vin_btnToolbar',
            placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
            vin_url: '/api/v1/approvals/list',
            vin_method: 'GET',
            vin_params: function () {
                let params = {};
                let search = $('#vin_approval_toolbar .approval-search').val();
                if (search) {
                    params.search = search;
                }
                return params;
            },
            fullPage: true,
            customTool: {
                beforeInput: beforeInput,
                afterInput: afterInput,
            },
            searchInput: true, //搜索框
            searchClass: 'approval-search', //自定义的搜索框类名
            searchSelector: '.approval-search', //选择使用自定义搜索框
            onCheck: function (row) {
                approvalData.push(row.approval_uuid);
                modifyDelStyle('approval_table', 'delete');
            },
            onUncheck: function (row) {
                var index = approvalData.indexOf(row.approval_uuid); // 查找元素的索引
                if (index !== -1) {
                    approvalData.splice(index, 1); // 从数组中删除一个元素
                }
                modifyDelStyle('approval_table', 'delete');
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = approvalData.indexOf(row[i].approval_uuid); // 查找元素的索引
                    if (index == -1) {
                        approvalData.push(row[i].approval_uuid)
                    }
                }
                modifyDelStyle('approval_table', 'delete');
            },
            onUncheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = approvalData.indexOf(row[i].approval_uuid); // 查找元素的索引
                    if (index != -1) {
                        approvalData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
                modifyDelStyle('approval_table', 'delete');
            },
            showExport: true, //是否开启导出按钮
            showColumns: true, //是否开启列选择按钮

            columns: [{
                    checkbox: true,
                    sortable: false, //默认可排序，禁用排序才写此项
                    formatter: function (value, row, index, field) {
                        for(var i=0; i<approvalData.length; i++) {
                            if(row.approval_uuid == approvalData[i]){
                                return true
                            }
                        }
                    }
                },
                {
                    field: 'name',
                    title: LANG.UI_STORAGE_NAME,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'classify_name',
                    title: LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_GROUP,
                },
                {
                    field: 'depth',
                    title: LANG.UI_PLATFORM_INDUSTRY_APPROVAL_DEPTH,
                },
                {
                    field: 'create_name',
                    title: LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_CREATE_NAME,
                },
                {
                    field: 'create_time',
                    title: LANG.UI_STORAGE_LUN_CREATE_TIME
                },
                {
                    field: 'update_time',
                    title: LANG.UI_SETTING_MODIFY_TIME
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: function (value, row, index) {
                        if (value == 1) {
                            return '<span class="label label-success">' + LANG.BILLING_ON_LOCK + '</span>';
                        } else if (value == 2) {
                            return '<span class="label label-danger">' + LANG.BILLING_OFF_LOCK + '</span>';
                        }
                    }
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    formatter: operationFormatter,
                    events: approvalOp,
                    opButton: true,
                    clickToSelect: false, //不可通过点击行选中
                    sortable: false, //默认可排序，禁用排序才写此项
                }
            ]
        };

        table.baseTableConfig().init(options);
    }

    /**
     * @function 重置表单的函数
     */
    function resetForm() {
        $('#name').val('');
        $('#classify').val('0');
        $('#actions').val('0');
        $('#status').bootstrapSwitch('state', true);

        var depthContent = `<div class="col-md-11 sort-div-parent">
                        <div class="sort-div" id="item0">
                             <!-- <i class="viconfont vicon-tuozhuai"></i> -->
                            <div style="display: flex; align-items: center;" class="col-md-12">
                                <select class="form-control select2me" style="width: auto;" name="type">
                                        <option value="1">${LANG.UI_PLATFORM_INDUSTRY_USER}</option>
                                        <option value="2">${LANG.UI_PLATFORM_INDUSTRY_USER_GROUP}</option>
                                </select>
                                <select class="form-control select2me ml8" name="depth" style="width: 40%;">
                                    <option value="0">${LANG.UI_USER_ALLOCATION_SELECT}</option>
                                    ${users}
                                </select>
                                <input type="text" name="description" class="form-control ml8" style="width: 117px;"
                                    autocomplete="off" placeholder="${LANG.UI_PLATFORM_INDUSTRY_CUSTOM_DESC}" value="">
                                <a class="ml8 copy-send" style="color:#00A3FF">${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND}</a>
                                <span class="ml8 close-btn"><i class="viconfont vicon-a-Reduce-onejianshao"></i></span>
                            </div>
                        </div>
                        <span class="cs-info ml15 display-none"></span>
                    </div>`;
        $('#depth').empty().append(depthContent);
        // makeDraggable('.sort-div');
    }

    /**
     * 根据给定的数据备份填充
     * @param {Object} row - 表格中的一行数据对象，包含name, classify, action, status和content等属性
     */
    const backupFillCopySend = function () {
        let div = $('#' + copyItem).parent().find('.cs-info'); //当前的层级div
        let users = div.attr('data-user') == undefined ? [] : div.attr('data-user').split(',');
        let userGroups = div.attr('data-group') == undefined ? [] : div.attr('data-group').split(',');
        console.log(users, userGroups);
        $('#copy_send_modal').find('select[name="user"]').selectpicker('val', users);
        $('#copy_send_modal').find('select[name="user_group"]').selectpicker('val', userGroups);
        $('#copy_send_modal').find('select[name="user"]').selectpicker('refresh');
        $('#copy_send_modal').find('select[name="user_group"]').selectpicker('refresh');

        var notice = div.attr('data-notice');
        if (notice == undefined || notice == '' || notice == 'false') {
            notice = false;
        } else {
            notice = true;
        }
        $('#copy_send_modal #notice_switch').bootstrapSwitch('state', notice);
    }

    /**
     * 根据给定的行数据备份填充表单
     * @param {Object} row - 表格中的一行数据对象，包含name, classify, action, status和content等属性
     */
    function backupFill(row) {
        var type = row.content[0].type;
        var selectOptions = users;
        if (type == 2) {
            selectOptions = userGroups;
        } else {
            selectOptions = users;
        }
        $('#name').val(row.name);
        $('select[name="classify"]').val(row.classify_uuid);
        $('#actions').val(row.action);

        if (row.status == CONF.FLAG.SET) {
            $('#status').bootstrapSwitch('state', true);
        } else {
            $('#status').bootstrapSwitch('state', false);
        }

        var depth = `<div class="col-md-11 sort-div-parent">
                        <div class="sort-div" id="item0">
                             <!-- <i class="viconfont vicon-tuozhuai"></i> -->
                            <div style="display: flex; align-items: center;" class="col-md-12">
                                <select class="form-control select2me" style="width: auto;" name="type">
                                    <option value="1">${LANG.UI_PLATFORM_INDUSTRY_USER}</option>
                                    <option value="2">${LANG.UI_PLATFORM_INDUSTRY_USER_GROUP}</option>
                                </select>
                                <select class="form-control select2me ml8" name="depth" style="width: 40%;">
                                    <option value="0">${LANG.UI_USER_ALLOCATION_SELECT}</option>
                                    ${selectOptions}
                                </select>
                                <input type="text" name="description" class="form-control ml8" style="width: 117px;"
                                    autocomplete="off" placeholder="${LANG.UI_PLATFORM_INDUSTRY_CUSTOM_DESC}" value="">
                                <a class="ml8 copy-send" style="color:#00A3FF">${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND}</a>
                                <span class="ml8 close-btn"><i class="viconfont vicon-a-Reduce-onejianshao"></i></span>
                            </div>
                        </div>
                        <span class="cs-info ml15 display-none"></span>
                    </div>`;
        var depthContent = depth;
        for (let i = 1; i < row.content.length; i++) {
            var id = i + 1;
            type = row.content[i].type
            if (type == 2) {
                selectOptions = userGroups;
            } else {
                selectOptions = users;
            }
            depthContent += `<div class="arrow" style="color:#EAEAEA">
                                <i class="viconfont vicon-shenpiliujiantou"></i>
                            </div>`;
            depthContent += `<div class="col-md-11 sort-div-parent">
                                <div class="sort-div" id="item${id}">
                                     <!-- <i class="viconfont vicon-tuozhuai"></i> -->
                                    <div style="display: flex; align-items: center;" class="col-md-12">
                                        <select class="form-control select2me" style="width: auto;" name="type">
                                            <option value="1">${LANG.UI_PLATFORM_INDUSTRY_USER}</option>
                                            <option value="2">${LANG.UI_PLATFORM_INDUSTRY_USER_GROUP}</option>
                                        </select>
                                        <select class="form-control select2me ml8" name="depth" style="width: 40%;">
                                            <option value="0" value="0">${LANG.UI_USER_ALLOCATION_SELECT}</option>
                                            ${selectOptions}
                                        </select>
                                        <input type="text" name="description" class="form-control ml8" style="width: 117px;"
                                            autocomplete="off" placeholder="${LANG.UI_PLATFORM_INDUSTRY_CUSTOM_DESC}" value="">
                                        <a class="ml8 copy-send" style="color:#00A3FF">${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND}</a>
                                        <span class="ml8 close-btn"><i class="viconfont vicon-a-Reduce-onejianshao"></i></span>
                                    </div>
                                </div>
                                <span class="cs-info ml15 display-none"></span>
                            </div>`;
        }

        $('#depth').empty().append(depthContent);
        $('.sort-div').each(function (index, item) {
            $(item).find('select[name="type"]').val(row.content[index].type);
            $(item).find('select[name="depth"]').val(row.content[index].approval_user_uuid);
            $(item).find('input[name="description"]').val(row.content[index].description);
            $('#copy_send_modal').find('select[name="user"]').selectpicker('val', row.content[index].users.user).selectpicker('refresh');
            $('#copy_send_modal').find('select[name="user_group"]').selectpicker('val', row.content[index].users.user_group).selectpicker('refresh');
            var user = $('#copy_send_modal button[data-id="user"]').attr('title');
            var userGroup = $('#copy_send_modal button[data-id="user_group"]').attr('title');
            if (row.content[index].users.user == 0) {
                user = LANG.UI_PUBLIC_NOTHING;
            }
            if (row.content[index].users.user_group == 0) {
                userGroup = LANG.UI_PUBLIC_NOTHING;
            }

            if ($('#notice_switch')[0].checked) {
                noticeFlag = true;
            }
            $(item).parent().find('.cs-info').attr('data-user', row.content[index].users.user.join())
            .attr('data-group', row.content[index].users.user_group.join())
            .attr('data-notice', row.content[index].users.notice)
            .text(`${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND_USER}：${user}；${LANG.UI_PLATFORM_INDUSTRY_COPY_SEND_USER_GROUP}：${userGroup}`)
            .show();
            if (row.content[index].users.user == 0 && row.content[index].users.user_group == 0) {
                $(item).parent().find('.cs-info').empty().hide();
            }
        })

        // makeDraggable('.sort-div');
    }





    return {
        init: function () {
            initTable();
            initUsers();
            initUserGroups();
            initClassify();
            addListeners();
        }
    }




}();


$(document).ready(function () {
    Approval.init();
});