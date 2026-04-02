var ApprovalClassify = function () {
    var classifyData = [];
    var table = $('#classify_table');
    var editRow;

    function addListeners () {
        // 删除
        $('#vin_classify_toolbar').on('click', '#delete', function (){
            if (getChooseUserUuids()) {
                checkOperateAuth(getChooseUserUuids(), delCLassify)
            }
        });
        // 启用
        $('#vin_classify_toolbar').on('click', '#unlock', function (){
            if (getChooseUserUuids()) {
                checkOperateAuth(getChooseUserUuids(), unlockClassify)
            }
        });
        // 禁用
        $('#vin_classify_toolbar').on('click', '#lock', function (){
            if (getChooseUserUuids()) {
                checkOperateAuth(getChooseUserUuids(), lockClassify)
            }
        });

        $('#vin_classify_toolbar').on('click', '#add', function () {
            $('#classify_drawer #titleDes').text(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_ADD);
            $('#classify_drawer #submit').attr('data-action', 'add'); //点击添加时，drawer的submit属性是添加
            resetForm();
        })

        $('#submit').on('click', function () {
            var action = $('#submit').attr('data-action');
            addSubmit(action);
        })
    }

    // 获取选中的用户信息
    function getChooseUserUuids(){
        var select = table.bootstrapTable('getSelections');
        if (select.length == 0) {
            UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_GROUP, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_SELECT_CLASSIFY);
            return false;
        }
        var uuids = [];
        for (let i = 0; i < select.length; i++) {
            uuids.push(select[i].create_uuid);
        }
        return {
            type: 1,
            user_uuid: uuids.join(','),
            auth: ''
        }
    }

    /**
     * 删除选中的审批分组
     * 该函数首先获取用户在表格中选中的项，然后确认是否选择了至少一项
     * 删除操作完成后，函数会刷新表格以反映最新的数据状态
     */
    function delCLassify () {
        var select = table.bootstrapTable('getSelections');
        if (select.length == 0) {
            return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_GROUP, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_SELECT_CLASSIFY);
        }
        var uuids = [];
        for (let i = 0; i < select.length; i++) {
            uuids.push(select[i].classify_uuid);
        }
        Metronic.blockUI({
            target: '#classify_tab',
            animate: true
        });
        pAjaxRequest({
            'classify_list': uuids
        }, '/api/v1/approvals/classify', 'DELETE', function (d) {
            Metronic.unblockUI('#classify_tab');
            if (operateResponseList(d)) {
                $('#delete').addClass('exch-forbid-event').removeClass('green-haze');
		        $('#delete').parent().css({"cursor": "not-allowed"});
                table.bootstrapTable('refresh');
            }
        });
    }

    /**
     * @function 启用审批流程
     */
    function unlockClassify() {
        var op = CONF.FLAG.SET; //启用
        var url = '/api/v1/approvals/classify/unlock';
        enableOrDisableApprovalSubmit(op, url);
    }

    /**
     * @function 禁用审批流程
     */
    function lockClassify() {
        var op = CONF.FLAG.UNSET; //禁用
        var url = '/api/v1/approvals/classify/lock';
        enableOrDisableApprovalSubmit(op, url);
    }

    /**
     * 启用/禁用审批提交
     * 
     * @param {number} op 操作类型，1为启用，2为禁用
     * @param {string} url 请求的URL地址
     */
    function enableOrDisableApprovalSubmit(op, url) {
        var select = table.bootstrapTable('getSelections');
        var uuids = [];
        for (let i = 0; i < select.length; i++) {
            uuids.push(select[i].classify_uuid);
        }
        if (uuids.length == 0) {
            return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_SELECT_CLASSIFY);
        }
        Metronic.blockUI({
            target: '#classify_tab',
            animate: true
        });
        pAjaxRequest({
            'uuids': uuids,
            'enable_flag': op
        }, url, 'PUT', function (d) {
            Metronic.unblockUI('#classify_tab');
            if (operateResponseList(d)) {
                table.bootstrapTable('refresh');
            }
        });
    }

    /**
     * @function 添加/修改审批分组提交
     */
    function addSubmit(action) {
        var data = {};
        var title = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_ADD_FAIL;
        if (action == 'edit') {
            title = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_EDIT_FAIL;
        }

        if ($('#name').val() == '') {
            return UIToastr.showWarning(title, LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_EDIT_CANNOT_EMPTY_NAME);
        }

        data.name = $('#name').val();
        data.remark = $('#remark').val();
        data.status = $('#status').get(0).checked;
        if (action == 'add') {
            addRequest(data);
        } else if (action == 'edit') {
            data.approval_classify_uuid = editRow.classify_uuid;
            editRequest(data);
        }
    }

    /**
     * 发送添加请求
     * @param {Object} data - 请求数据，一个包含需要发送数据的对象
     * 
     * 此函数用于向服务器发送添加请求在特定的列表或分组中添加数据
     * 它首先会阻塞用户界面(UI)以防止在等待响应时的重复操作，然后发送异步请求，
     * 请求成功后，会刷新数据表以显示最新数据，并关闭添加抽屉
     */
    function addRequest (data) {
        Metronic.blockUI({
            target: '#classify_drawer',
            animate: true
        });
        pAjaxRequest(data, '/api/v1/approvals/classify', 'POST', function (res) {
            Metronic.unblockUI('#classify_drawer');
            if (operateResponseList(res)) {
                $('#classify_drawer').drawer('hide');
                table.bootstrapTable('refresh');
            }
        });
    }

    /**
     * 编辑请求
     * 
     * @param {Object} data - 请求的数据
     * 该函数的目标是更新分组请求的状态，通过向后端发送PUT请求实现
     */
    function editRequest (data) {
        Metronic.blockUI({
            target: '#classify_drawer',
            animate: true
        });
        pAjaxRequest(data, '/api/v1/approvals/classify', 'PUT', function (res) {
            Metronic.unblockUI('#classify_drawer');
            if (operateResponseList(res)) {
                $('#classify_drawer').drawer('hide');
                table.bootstrapTable('refresh');
            }
        });
    }

    /**
     * @function 重置表单的函数
     */
    function resetForm() {
        $('#name').val('');
        $('#remark').val('');
        $('#status').bootstrapSwitch('state', true);
    }

    /**
     * 将给定行的数据回填到表单中
     * 主要用于在用户界面上预填表单字段
     * 
     * @param {Object} row - 包含name、remark和status属性的对象
     *  - row.name: 表单名称
     *  - row.remark: 表单备注
     *  - row.status: 表单状态，用于确定状态开关的位置
     */
    function backupFill(row) {
        $('#name').val(row.name);
        $('#remark').val(row.remark);
        if (row.status == CONF.FLAG.SET) {
            $('#status').bootstrapSwitch('state', true);
        } else {
            $('#status').bootstrapSwitch('state', false);
        }
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

            // 修改
            button += '<li class="edit"><a href="javascript:;"><i class="viconfont vicon-edit-new"></i> ' + LANG.UI_FILE_EDIT + ' </a></li>';

            button += '</ul></div>';
            return button;
        }

        var approvalOp = {
            'click .edit': function (e, value, row, index) {
                editRow = row;
                var data = {
                    type: 1,
                    user_uuid: row.create_uuid,
                    auth: ''
                };
                checkOperateAuth(data, function (){
                    $('#classify_drawer').drawer('show');
                    $('#classify_drawer #titleDes').text(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_EDIT);
                    $('#classify_drawer #submit').attr('data-action', 'edit'); //点击修改时，drawer的submit属性是修改
                    backupFill(row);
                })
            }
        }
        var beforeInput = ``;
        if ($.inArray('p_industry_approval_group_delete', CONF.PERMISSION_ARR) !== -1) {
            beforeInput = `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete"></button></div>`;
        }
        var afterInput = ``;
        if ($.inArray('p_industry_approval_group_add', CONF.PERMISSION_ARR) !== -1) {
            afterInput += `<button class="btn dropdown-toggle btn-font flex_center btn-title p-lr8 table-toolbar-btn" id="add" data-toggle="drawer" data-target="#classify_drawer" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia"></i>
                                <span>` + LANG.UI_PUBLIC_ADD + `</span>
                            </button>`;
        }
        if ($.inArray('p_industry_approval_group_enable', CONF.PERMISSION_ARR) !== -1) {
            afterInput += `<button class="btn table-toolbar-btn" id="unlock">
                                <i class="viconfont vicon-a-Unlockjiesuo-0111 mr4"></i>
                                <span>` + LANG.BILLING_ON_LOCK + `</span>
                            </button>`;
        }
        if ($.inArray('p_industry_approval_group_disable', CONF.PERMISSION_ARR) !== -1) {
            afterInput += `<button class="btn table-toolbar-btn" id="lock">
                                <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i>
                                <span>` + LANG.BILLING_OFF_LOCK + `</span>
                            </button>`;
        }

        var column =  [{
            checkbox: true,
            sortable: false, //默认可排序，禁用排序才写此项
            formatter: function (value, row, index, field) {
                for(var i=0; i<classifyData.length; i++) {

                    if(row.classify_uuid == classifyData[i]){
                        return true
                    }
                }
            }
        },
            {
                field: 'name',
                title: LANG.UI_PLATFORM_INDUSTRY_APPROVAL_CLASSIFY_NAME,
                sortable: false, //默认可排序，禁用排序才写此项
            },
            {
                field: 'remark',
                title: LANG.UI_PUBLIC_DESCRIPTION,
                sortable: false, //默认可排序，禁用排序才写此项
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
        ];
        if ($.inArray('p_industry_approval_group_modify', CONF.PERMISSION_ARR) !== -1) {
            column.push({
                title: LANG.UI_PUBLIC_OPERATION,
                formatter: operationFormatter,
                events: approvalOp,
                opButton: true,
                clickToSelect: false, //不可通过点击行选中
                sortable: false, //默认可排序，禁用排序才写此项
            });
        }

        let options = {
            toolbarId: '#vin_classify_toolbar',
            buttonsToolbar: '#vin_classify_toolbar .vin_btnToolbar',
            placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
            vin_url: '/api/v1/approvals/classify',
            vin_method: 'GET',
            vin_params: function () {
                let params = {};
                let search = $('#vin_classify_toolbar .classify-search').val();
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
            searchClass: 'classify-search', //自定义的搜索框类名
            searchSelector: '.classify-search', //选择使用自定义搜索框
            onCheck: function (row) {
                classifyData.push(row.classify_uuid);
                modifyDelStyle('classify_table', 'delete');
            },
            onUncheck: function (row) {
                var index = classifyData.indexOf(row.classify_uuid); // 查找元素的索引
                if (index !== -1) {
                    classifyData.splice(index, 1); // 从数组中删除一个元素
                }
                modifyDelStyle('classify_table', 'delete');
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = classifyData.indexOf(row[i].classify_uuid); // 查找元素的索引
                    if (index == -1) {
                        classifyData.push(row[i].classify_uuid)
                    }
                }
                modifyDelStyle('classify_table', 'delete');
            },
            onUncheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = classifyData.indexOf(row[i].classify_uuid); // 查找元素的索引
                    if (index != -1) {
                        classifyData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
                modifyDelStyle('classify_table', 'delete');
            },
            showExport: true, //是否开启导出按钮
            showColumns: true, //是否开启列选择按钮

            columns: column
        };

        table.baseTableConfig().init(options);
    }

    return {
        init: function () {
            initTable();
            addListeners();
        }
    }
}();


$(document).ready(function () {
    ApprovalClassify.init();
});