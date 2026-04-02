var TemplateList = function () {
    var table = $('#template_table');
    var tempData = [];

    const initListener = function () {
        // 新建
        $('#vin_template_toolbar').on('click', '#add', goToAdd);

        // 测试
        $('#vin_template_toolbar').on('click', '#test', goToTest);

        // 启用
        $('#vin_template_toolbar').on('click', '#unlock', function (){
            if (getChooseUserUuids()) {
                checkOperateAuth(getChooseUserUuids(), unlockTemp)
            }
        });

        // 禁用
        $('#vin_template_toolbar').on('click', '#lock', function (){
            if (getChooseUserUuids()) {
                checkOperateAuth(getChooseUserUuids(), lockTemp)
            }
        });

        // 删除
        $('#vin_template_toolbar').on('click', '#delete', function (){
            if (getChooseUserUuids()) {
                checkOperateAuth(getChooseUserUuids(), delTemp)
            }
        });
    };

    const goToAdd = function () {
        LOCATION('./content/platform/industry/template_detail.php');
    }

    const goToEdit = function (uuid) {
        LOCATION('./content/platform/industry/template_detail.php?uuid=' + uuid);
    }

    const goToTest = function () {
         var select = table.bootstrapTable('getSelections');
         if (select.length != 1) {
             return UIToastr.showInfo(LANG.UI_PLATFORM_TEMPLATE_EDIT_TIP);
         }

        /*// 打开模态
        JobReportDetail.init({'uuid': 'a6c78a51-23ce-fe4f-0499-8d6649d02af4', 'agent_uuid':'123', 'pre': 2})
        return;*/

        // 请求接口，返回成功跳转页面
        var data = {
            test: 1,
            temp_uuid: select[0].temp_uuid
        };
        Metronic.blockUI({target: '#template_list_div',animate: true,cenrerY: true});
        pAjaxRequest(data, "/api/v1/industry/report", "POST", function (result) {
            Metronic.unblockUI('#template_list_div');
            if (result.code == 0) {
                // 打开模态
                JobReportDetail.init({'uuid': result.data, 'pre': 2})
            }
        });
    }

    /**
     * @function 启用模板
     */
    function unlockTemp() {
        var op = CONF.FLAG.SET; //启用
        var url = '/api/v1/industry/templates/enable';
        enableOrDisableApprovalSubmit(op, url);
    }

    /**
     * @function 禁用模板
     */
    function lockTemp() {
        var op = CONF.FLAG.UNSET; //禁用
        var url = '/api/v1/industry/templates/disable';
        enableOrDisableApprovalSubmit(op, url);
    }

    // 获取选择的用户uuid集合
    function getChooseUserUuids(){
        var select = table.bootstrapTable('getSelections');
        var uuids = [];
        for (let i = 0; i < select.length; i++) {
            uuids.push(select[i].user_uuid);
        }
        if (uuids.length == 0) {
            UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_TEMPLATE,LANG.UI_PLATFORM_TEMPLATE_PLEASE_SELECT_TEMP);
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
    function enableOrDisableApprovalSubmit(op, url) {
        var select = table.bootstrapTable('getSelections');
        var uuids = [];
        for (let i = 0; i < select.length; i++) {
            uuids.push(select[i].temp_uuid);
        }
        if (uuids.length == 0) {
            return UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_TEMPLATE,LANG.UI_PLATFORM_TEMPLATE_PLEASE_SELECT_TEMP);
        }
        Metronic.blockUI({
            target: '#template_list_div',
            animate: true
        });
        pAjaxRequest({
            'uuids': uuids,
            'enable_flag': op
        }, url, 'POST', function (d) {
            Metronic.unblockUI('#template_list_div');
            if (operateResponseList(d, LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING)) {
                table.bootstrapTable('refresh');
            }
        });
    }

    function delTemp () {
        var select = table.bootstrapTable('getSelections');
        if (select.length == 0) {
            return UIToastr.showWarning(LANG.UI_PLATFORM_TEMPLATE_PLEASE_SELECT_TEMP);
        }
        var uuids = [];
        for (let i = 0; i < select.length; i++) {
            uuids.push(select[i].temp_uuid);
        }
        Metronic.blockUI({
            target: '#template_list_div',
            animate: true
        });
        pAjaxRequest({
            'uuids': uuids
        }, '/api/v1/industry/templates', 'DELETE', function (d) {
            Metronic.unblockUI('#template_list_div');
            if (operateResponseList(d, LANG.UI_PLATFORM_TEMPLATE_DELETE)) {
                $('#delete').addClass('exch-forbid-event').removeClass('green-haze');
                $('#delete').parent().css({"cursor": "not-allowed"});
                table.bootstrapTable('refresh');
            }
        });
    }

    const initTable = function () {
        var beforeInput = ``;
        if ($.inArray('p_industry_template_delete', CONF.PERMISSION_ARR) !== -1) {
            beforeInput = `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete"></button></div>`;
        }
        var afterInput = ``;
        if ($.inArray('p_industry_template_add', CONF.PERMISSION_ARR) !== -1) {
            afterInput += `<button class="btn dropdown-toggle btn-font flex_center btn-title p-lr8 table-toolbar-btn" id="add" data-toggle="drawer" data-target="#approval_drawer" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia"></i>
                                <span>` + LANG.UI_PUBLIC_ADD + `</span>
                            </button>`;
        }
        if ($.inArray('p_industry_template_enable', CONF.PERMISSION_ARR) !== -1) {
            afterInput += ` <button class="btn table-toolbar-btn" id="unlock">
                                <i class="viconfont vicon-a-Unlockjiesuo-0111 mr4"></i>
                                <span>` + LANG.BILLING_ON_LOCK + `</span>
                            </button> `;
        }
        if ($.inArray('p_industry_template_disable', CONF.PERMISSION_ARR) !== -1) {
            afterInput += `<button class="btn table-toolbar-btn" id="lock">
                                <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i>
                                <span>` + LANG.BILLING_OFF_LOCK + `</span>
                            </button> `;
        }

        var column = [
            {
                checkbox: true,
                sortable: false, //默认可排序，禁用排序才写此项
                formatter: function (value, row, index, field) {
                    for (var i = 0; i < tempData.length; i++) {
                        if (row.temp_uuid == tempData[i]) {
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
                field: 'user_name',
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
                field: 'remark',
                title: LANG.UI_PUBLIC_REMARK
            },

        ];

        if ($.inArray('p_industry_template_modify', CONF.PERMISSION_ARR) !== -1) {
            column.push({
                title: LANG.UI_PUBLIC_OPERATION,
                sortable: false,
                clickToSelect: false, //不可通过点击行选中
                formatter: opButton,
                width: "125px",
                opButton: true,
                events: operates, //单元点击事件
                forceHide: true,
            })
        }

        let options = {
            toolbarId: '#vin_template_toolbar',
            buttonsToolbar: '#vin_template_toolbar .vin_btnToolbar',
            placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
            vin_url: '/api/v1/industry/templates',
            vin_method: 'GET',
            vin_params: function () {
                let params = {};
                let search = $('#vin_template_toolbar .temp-search').val();
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
            searchClass: 'temp-search', //自定义的搜索框类名
            searchSelector: '.temp-search', //选择使用自定义搜索框
            onCheck: function (row) {
                tempData.push(row.temp_uuid);
                modifyDelStyle('template_table', 'delete');
            },
            onUncheck: function (row) {
                var index = tempData.indexOf(row.temp_uuid); // 查找元素的索引
                if (index !== -1) {
                    tempData.splice(index, 1); // 从数组中删除一个元素
                }
                modifyDelStyle('template_table', 'delete');
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = tempData.indexOf(row[i].temp_uuid); // 查找元素的索引
                    if (index == -1) {
                        tempData.push(row[i].temp_uuid)
                    }
                }
                modifyDelStyle('template_table', 'delete');
            },
            onUncheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = tempData.indexOf(row[i].temp_uuid); // 查找元素的索引
                    if (index != -1) {
                        tempData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
                modifyDelStyle('template_table', 'delete');
            },
            showExport: false, //是否显示导出按钮
            showColumns: true, //是否开启列选择按钮

            columns: column
        };

        table.baseTableConfig().init(options);
    };

    const opButton = function (value, row, index, field) {
        var button = '<div class="btn-group">';
        
        // 修改
        button += '<div class="btn_operation_vicon" style="margin-right: 20px;"><a class="edit"><i class="viconfont vicon-a-Editbianji"></i></a></div>';

        button += '</div>';
        return button;
    }

    const operates = {
        'click .edit': function (event, value, row, index) {
            var data = {
                type: 1,
                user_uuid: row.user_uuid,
                auth: ''
            };
            checkOperateAuth(data, function (){
                goToEdit(row.temp_uuid);
            })
        },
    }

    return {
        init: function () {
            initTable();
            initListener(); 
        }
    }
}();

$(document).ready(function () {
    TemplateList.init();
});
