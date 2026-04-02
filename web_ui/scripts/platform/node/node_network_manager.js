var NodeNetworkManager = function () {
    let sortObj;	//排序对象
    let networkForm;
    let changeNodeNetworkRowHeightFlag = false;

    //初始化事件
    const addListeners = function () {
        $('#add').on('click', addNetwork);
        $('#delete').on('click', deleteNetwork);
        $('#edit').on('click', editNetwork);
        $('#order').on('click', orderNetwork);
        $('#addsubmit').on('click', addNetworkSubmit);
        $('#editsubmit').on('click', editNetworkSubmit);
        $('#ordersubmit').on('click', orderNetworkSubmit);
        $('#ipaddr').on('change', function () {
            $('#nickname').val(this.value);
        });
        $('#nodeNetworkList').on('click', '.change_height', changeNodeNetworkRowHeight); // 修改资源池高度
    };

    /**
     * 操作权限校验
     * @returns {{type: number, source_uuid, source_type: number}|boolean}
     */
    const checkAuth = function() {
        return {
            type: 2,
            source_uuid: $('#nodeUUID').val(),
            source_type: 7,
        };
    }

    //添加节点网络确认
    const addNetworkSubmit = function () {
        if (!networkForm.validate().form()) {
            return false;
        }
        let reqData = {
            network_ip: $('#ipaddr').val(),
            network_alias: $('#nickname').val(),
            network_port: parseInt($('#connectport').val()),
        };
        Metronic.blockUI({target: '#addModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes/${$('#nodeUUID').val()}/network`, 'POST', res => {
            Metronic.unblockUI('#addModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_NETWORK_ADD, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_NETWORK_ADD, res.message);
            //成功模态框消失，显示列表
            $('#addModal').modal('hide');
            $('#nodeNetworkTable').bootstrapTable('refresh');
        });
    };

    //修改节点网络确认
    const editNetworkSubmit = function () {
        if (!networkForm.validate().form()) {
            return false;
        }
        let reqData = {
            network_ip: $('#ipaddr').val(),
            network_alias: $('#nickname').val(),
            network_port: $('#connectport').val(),
        };
        Metronic.blockUI({target: '#addModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes/${$('#nodeUUID').val()}/network/${$('#networkuuid').val()}`, 'PATCH', res => {
            Metronic.unblockUI('#addModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_NETWORK_EDIT, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_NETWORK_EDIT, res.message);
            $('#addModal').modal('hide');
            $('#nodeNetworkTable').bootstrapTable('refresh');
        });
    };

    const initModal = function (row = null) {
        let ipAddrTag = $('#ipaddr');
        ipAddrTag.prop('disabled', false);
        //修改显示原来的信息
        if (row !== null) {
            ipAddrTag.val(row.network_ip);
            if (row.network_type === 1) {
                //新建映射网络可以修改IP,默认网络不能修改IP
                ipAddrTag.prop('disabled', true);
            }
            $('#connectport').val(row.network_port);
            $('#nickname').val(row.network_alias);
            $('#networkuuid').val(row.network_uuid);
            $('.addDiv').hide();
            $('.editDiv').show();
        } else {
            $('.addDiv').show();
            $('.editDiv').hide();
        }
        $('#addModal').modal({'width': "600px", 'height': "300px"});
    };

    //添加节点网络
    const addNetwork = function () {
        checkOperateAuth(checkAuth(), initModal);
    };

    //删除节点网络
    const deleteNetwork = function () {
        let rows = $('#nodeNetworkTable').bootstrapTable('getSelections');
        if (rows.length === 0) {
            return UIToastr.showInfo(LANG.UI_NODE_NETWORK_DELETE, LANG.UI_NODE_NETWORK_DELETE_SELECT_TIPS);
        }

        checkOperateAuth(checkAuth(), doDeleteNetwork);
    };

    /**
     * 执行删除节点网络操作
     */
    const doDeleteNetwork = () => {
        let rows = $('#nodeNetworkTable').bootstrapTable('getSelections');
        bootbox.confirm({
            title: LANG.UI_NODE_NETWORK_DELETE,
            message: LANG.UI_NODE_NETWORK_DELETE_CONFIRM_TIPS,
            callback: debounce(function (r) {
                if (!r) return;
                let reqData = {
                    network_uuid_list: rows.map(v => v.network_uuid),
                };
                Metronic.blockUI({target: '#nodeNetworkList', animate: true});
                pAjaxRequest(reqData, `/api/v1/nodes/${$('#nodeUUID').val()}/network`, 'DELETE', res => {
                    Metronic.unblockUI('#nodeNetworkList');
                    if (!res.success) {
                        UIToastr.showWarning(LANG.UI_NODE_NETWORK_DELETE, res.message);
                        return;
                    }
                    UIToastr.showSuccess(LANG.UI_NODE_NETWORK_DELETE, res.message);
                    $('#nodeNetworkTable').bootstrapTable('refresh');
                });
            }, 300)
        });
    };

    //修改节点网络
    const editNetwork = function () {
        let rows = $('#nodeNetworkTable').bootstrapTable('getSelections');
        if (1 !== rows.length) {
            return UIToastr.showInfo(LANG.UI_NODE_NETWORK_EDIT, LANG.UI_NODE_NETWORK_EDIT_SELECT_TIPS);
        }

        checkOperateAuth(checkAuth(), doEditNetwork);
    };

    /**
     * 执行修改节点网络操作
     */
    const doEditNetwork = () => {
        let rows = $('#nodeNetworkTable').bootstrapTable('getSelections');
        initModal(rows[0]);
    };

    //节点网络排序
    const orderNetwork = function () {
        checkOperateAuth(checkAuth(), initNetworkList);
    };

    /**
     * 网络排序提交
     */
    const orderNetworkSubmit = function () {
        let reqData = {
            network_uuid_list: sortObj.toArray(),
        };
        Metronic.blockUI({target: '#orderModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes/${$('#nodeUUID').val()}/network/sort`, 'POST', res => {
            Metronic.unblockUI('#orderModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_NETWORK_ORDER, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_NETWORK_ORDER, res.message);
            $('#orderModal').modal('hide');
            $('#nodeNetworkTable').bootstrapTable('refresh');
        });
    };

    /**
     * 设置节点网络高度
     */
    const setNodeNetworkRowHeight = () => {
        let rows = $('#nodeNetworkTable').bootstrapTable('getData');
        if (!rows.length) {
            return;
        }
        if (changeNodeNetworkRowHeightFlag) {
            $('#nodeNetworkTable>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            });
        } else {
            $('#nodeNetworkTable>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            });
        }
    };

    /**
     * 修改节点网络高度
     */
    const changeNodeNetworkRowHeight = () => {
        if (!changeNodeNetworkRowHeightFlag) {
            changeNodeNetworkRowHeightFlag = true;
            $('#vin_node_network_toolbar .change_height i').addClass('icon-auto-height2');
        } else {
            changeNodeNetworkRowHeightFlag = false;
            $('#vin_node_network_toolbar .change_height i').removeClass('icon-auto-height2');
        }
        setNodeNetworkRowHeight();
    };

    /**
     * 初始化网络排序的网络拖拽
     */
    const initNetworkList = function () {
        let reqData = {
            offset: 0,
            limit: 100,
        };
        Metronic.blockUI({target: '#nodeNetworkList', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes/${$('#nodeUUID').val()}/network`, 'GET', res => {
            Metronic.unblockUI('#nodeNetworkList');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_NETWORK_ORDER, res.message);
                return;
            }
            if (!res.data.rows.length) {
                UIToastr.showInfo(LANG.UI_NODE_NETWORK_ORDER, LANG.UI_NODE_NETWORK_ORDER_SELECT_TIPS);
                return;
            }

            let divs = ``;
            for (const row of res.data.rows) {
                let name = row.network_ip + ':' + row.network_port;
                if (row.network_alias) {
                    name += '(' + row.network_alias + ')';
                }
                divs += `
				<div class="list-group-item" data-id="${row.network_uuid}">
					${name}
				</div>
				`;
            }
            sortObj = new Sortable($('#networkList').html(divs).get(0), {
                animation: 150,
                swap: true, // Enable swap plugin
                swapClass: 'highlight', // The class applied to the hovered swap item
            });
            $('#orderModal').modal({'width': "600px", 'height': "300px"});
        });
    };

    //添加/修改客户端数据格式校验
    const networkValidate = function () {
        networkForm = $('#networkForm');

        networkForm.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
                ipaddr: {
                    required: true,
                    ipv4: true
                },
                connectport: {
                    required: true,
                    port: true
                }

            },

            invalidHandler: function (event, validator) { //display error alert on form submit
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                let icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change done by hightlight

            },

            success: function (label, element) {
                let icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {

            }

        });

        //IP验证格式
        $.validator.addMethod("ipv4", function (value, element) {
            return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_IP);

        //端口验证格式
        $.validator.addMethod("port", function (value) {
            return value >= 0 && value <= 65535;
        }, LANG.UI_NODE_PORT_TIPS);
    };

    const getNodeNetworkTableColumns = () => {
        return [{
            checkbox: true,
            width: '2',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_NETWORK_TABLE_IP,
            field: 'network_ip',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_NETWORK_TABLE_ALIAS,
            field: 'network_alias',
            width: '15',
            widthUnit: '%',
            formatter: value => value ? `<span title="${value}">${value}</span>` : '--',
        }, {
            title: LANG.UI_NODE_NETWORK_POOL,
            field: 'network_pool_name',
            sortable: false,
            width: '15',
            widthUnit: '%',
            formatter: value => {
                if (!Array.isArray(value) || !value.length) {
                    return '--';
                }
                let name = value.join('<br>');
                let title = value.join('\n');
                return `<span title="${title}">${name}</span>`;
            },
        }, {
            title: LANG.UI_NODE_NETWORK_TABLE_TYPE,
            field: 'network_type_des',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_NETWORK_TABLE_PORT,
            field: 'network_port',
            width: '10',
            widthUnit: '%',
        }]
    };

    /**
     * 设置选中事件
     */
    const checkEvent = function (tableId, btnId) {
        let select = $('' + tableId + '').bootstrapTable('getSelections');
        if (select.length == 0) {
            $('' + btnId + ' i').addClass('icon-gray-delete');
            $('' + btnId + ' i').removeClass('icon-white-delete');
            $('' + btnId + '').removeClass('select-delete-btn');
            $('' + btnId + '').addClass('cancel-delete-btn');
        } else {
            $('' + btnId + ' i').removeClass('icon-gray-delete');
            $('' + btnId + ' i').addClass('icon-white-delete');
            $('' + btnId + '').removeClass('cancel-delete-btn');
            $('' + btnId + '').addClass('select-delete-btn');
        }
    }

    const nodeNetworkRowCheck = () => {
        checkEvent('#nodeNetworkTable', '#delete');
    };

    const initNodeNetworkTableHeight = () => {
        let breadcrumbHeight = 38;
        let toolbarHeight = 57;
        let paginationHeight = 52;
        let alertHeight = 155;
        let tabTitleHeight = 48;
        let navTitleHeight = 46;
        // page-content有40px的内边距
        // portlet-body有10px的内边距
        // tab-content有20px的外边距离
        let otherHeight = 40 + 10 + 20;
        let height = window.innerHeight - toolbarHeight - paginationHeight - alertHeight
            - tabTitleHeight - navTitleHeight - otherHeight - breadcrumbHeight;
        $("#nodeNetworkList .fixed-table-body").css({
            // 'max-height': height,
            'height': height,
        });
    };

    const getQueryParams = () => {
        return {};
    };

    const getNodeNetworkTableOption = () => {
        let nodeUuid = $('#nodeUUID').val();
        return {
            vin_url: `/api/v1/nodes/${nodeUuid}/network`,
            vin_params: getQueryParams,
            vin_method: 'get',
            buttonsToolbar: '#vin_node_network_toolbar .vin_btnToolbar',
            toolbarId: '#vin_node_network_toolbar',
            vin_toolbar: '#vin_node_network_toolbar',
            sortable: false,
            // 隐藏行
            hideColumns: '',
            showButtonText: false,
            clickToSelect: true,
            pagination: true,
            pageList: [10, 20, 50, 100, 150, 200],
            pageSize: 20,
            resizable: true,
            showRefresh: false,
            showExport: false,
            singleSelect: false,
            changeHeightBtn: true,
            onResetView: initNodeNetworkTableHeight,
            onRefresh: () => {
                $("#nodeNetworkTable").bootstrapTable('hideLoading');
            },
            onPostBody: () => {
                $(".popovers").popover();
                nodeNetworkRowCheck();
                setNodeNetworkRowHeight();
            },
            onCheck: nodeNetworkRowCheck,
            onUncheck: nodeNetworkRowCheck,
            onCheckAll: nodeNetworkRowCheck,
            onUncheckAll: nodeNetworkRowCheck,
            columns: getNodeNetworkTableColumns(),
        };
    };

    const initNodeNetworkTable = () => {
        $('#nodeNetworkTable').bootstrapTable('destroy').baseTableConfig().init(getNodeNetworkTableOption());
    };

    return {
        //main function to initiate the module
        init: function () {
            initNodeNetworkTable();
            addListeners();
            networkValidate();	//初始化添加修改代理数据格式验证
        }

    };

}();

jQuery(document).ready(function () {
    NodeNetworkManager.init();
});