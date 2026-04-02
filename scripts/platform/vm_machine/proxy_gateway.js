var vmProxyGateway = function () {
    let changeHeightFlag = false;
    var initListener = function () {
        //绑定事件
        toBindEvent();
    }
    // 高度改变
    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#vm_proxy_table>tbody>tr').css(
                'cssText', 'height: 60px!important',
            )
            $('#vin_current_proxy_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#vm_proxy_table>tbody>tr').css(
                'cssText', 'height: 40px!important',
            )
            $('#vin_current_proxy_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }
    
    // 初始化表格
    var initDataTable = function () {
        var operates = {
            'click .open': function (event, value, row, index) {
                operateRecord(row, 'open');
            },
            'click .look': function (event, value, row, index) {
                operateRecord(row, 'look');
            },
            'click .stop': function (event, value, row, index) {
                operateRecord(row, 'stop');
            },
            'click .force': function (event, value, row, index) {
                operateRecord(row, 'force');
            },
            'click .restart': function (event, value, row, index) {
                operateRecord(row, 'restart');
            },
        }
        const options = {
            toolbarId: '#vin_current_proxy_toolbar',
            buttonsToolbar: '.vin_proxy_btnToolbar',
            vin_url: "/api/v1/virtual/proxy",
            vin_params: function () {
                // 所有自定义携带参数，必须return
                var params = {};
                params['search'] = $('.vmproxy-search').val();
                return params;
            },
            vin_method: "GET",
            changeHeightBtn: true, //改变高度按钮
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            resizable: true, //可变宽度
            searchInput: true, //搜索框
            searchClass: 'vmproxy-search',
            searchSelector: '.vmproxy-search', //使用哪个搜索框
            placeholder: LANG.UI_VM_MACHINE_SEARCH,
            searchOnEnterKey:true, //回车搜索
            onResetView: initTableHeight,
            columns: [
                {
                    field: 'name',
                    title: LANG.UI_VM_MACHINE_NAME,
                    align: 'center',
                    sortable: false,
                },
                {
                    field: 'memory_total',
                    title: LANG.UI_VM_MACHINE_MEMS,
                    align: 'center',
                    sortable: false,
                },
                {
                    field: 'vcpu_num',
                    title: LANG.UI_VM_MACHINE_CPU,
                    align: 'center',
                    sortable: false,
                },
                /*{
                    field: 'os_type',
                    title: LANG.UI_VM_MACHINE_OS_TYPE,
                    sortable: false,
                    align: 'center',
                },*/
                {
                    field: 'source',
                    title: LANG.UI_VM_PROXY_SOURCE,
                    align: 'center',
                    sortable: false,
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    sortable: false,
                    align: 'center',
                    formatter: statusFormatter,
                },
                {
                    field: 'node',
                    title: LANG.UI_DB_NODE,
                    sortable: false,
                    align: 'center',
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    clickToSelect: false, //不可通过点击行选中
                    formatter: operateFormatter,
                    opButton:true,
                    width: "200px;",
                    events: operates, //单元点击事件
                }
            ],
            onCheck: function (rowdata) {
                modifyDelStyle();
            },
            onUncheck: function (rowdata) {
                modifyDelStyle();
            },
            onCheckAll: function (alldata) {
                modifyDelStyle();
            },
            onUncheckAll: function (alldata) {
                modifyDelStyle();
            },
            PostBody: function () {
                $('#vm_proxy_table th[data-field="name"]').css('width','25%');
                $('#vm_proxy_table th[data-field="memory_total"]').css('width','10%');
                $('#vm_proxy_table th[data-field="vcpu_num"]').css('width','10%');
                //$('#vm_proxy_table th[data-field="os_type"]').css('width','10%');
                $('#vm_proxy_table th[data-field="source"]').css('width','12%');
                $('#vm_proxy_table th[data-field="status"]').css('width','10%');
                $('#vm_proxy_table th[data-field="node"]').css('width','20%');

            }
        };
        $('#vm_proxy_table').baseTableConfig().init(options);

        function operateFormatter(value, row, index) {
            var button = '<div class="btn-group">';
            button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                '</button>' +
                '<ul class="dropdown-menu min-width100" role="menu" id='+ row.uuid +'>';

            if (row.status == 1) {
                // 已开机
                var styles = '';
                if (row.prefix_status != true) {
                    styles = 'style="pointer-events: none;opacity: 0.6; cursor: not-allowed;"';
                }
                button += '<li class="look" '+styles+'><a href="javascript:;"><i class="viconfont mr5 vicon-a-Eyesyanjing"></i>'+LANG.UI_VM_RESOURCE_CONSOLE+'</a></li>';
                if($.inArray('p_vm_machine_proxy_gateway_off', CONF.PERMISSION_ARR) !== -1) {
                    button += '<li class="stop"><a href="javascript:;"><i class="viconfont mr5 vicon-guanji"></i>'+LANG.UI_CLOUD_PLATFORM_POWER_OFF+'</a></li>';
                    button += '<li class="force"><a href="javascript:;"><i class="viconfont mr5 vicon-ge_shutdown"></i>'+LANG.UI_VM_MACHINE_POWER_OFF_FORCE+'</a></li>';
                }
                if($.inArray('p_vm_machine_proxy_gateway_restart', CONF.PERMISSION_ARR) !== -1) {
                    button += '<li class="restart"><a href="javascript:;"><i class="viconfont mr5 vicon-zhongqi"></i>'+LANG.UI_VM_MACHINE_POWER_RESTART+'</a></li>';
                }
            } else {
                // 未开机
                if($.inArray('p_vm_machine_proxy_gateway_on', CONF.PERMISSION_ARR) === -1) {
                    return '--';
                }
                button += '<li class="open"><a href="javascript:;"><i class="viconfont mr5 vicon-kaiji"></i>'+LANG.UI_CLOUD_PLATFORM_POWER_ON+'</a></li>';
            }
            button += '</ul></div>';
            return button;
        }
        initListener();
    }
    var modifyDelStyle = function () {
    }

    var toBindEvent = function () {
        // 改变表格高度
        $('#vin_current_proxy_toolbar .change_height').on('click', change_height);
    }
    // 状态
    function statusFormatter(value, row, index) {
        if (value == 1) {
            return '<span class="label label-sm label-success status-icon">' + row.status_value + '</span>';
        }
        // 停止
        return '<span class="label label-sm label-default status-icon">' + row.status_value + '</span>';
    }

    // 操作按钮事件
    var operateRecord = function (row, type) {
        var vmUuid = row.uuid;
        if (type == 'look') {
            // 查看
            // 更改为需要先请求后台，然后才能跳转
            opereate(type, vmUuid, row.vnc_url);
            return;
        }

        // 判断下是否需要提示
        if (row.job_status == 2 && type != 'open') {
            // 任务运行中 需要给个提示
            var msg = LANG.UI_VM_OPERATE_CONFIM.replace(/s%/g, row.job_name);
            bootbox.confirm({
                title: LANG.UI_VM_MACHINE_OPERATION,
                message: msg,
                callback: debounce(function (r) {
                    if (!r) return;
                    opereate(type, vmUuid);
                }, 300)
            });
        } else {
            opereate(type, vmUuid);
        }
    }

    function opereate(type, vmUuid, url = '') {
        Metronic.blockUI({target: '#vm_proxy_table',animate: true,cenrerY: true});
        pAjaxRequest({type:type}, "/api/v1/virtual/operate/"+vmUuid, "POST", function (result) {
            Metronic.unblockUI('#vm_proxy_table');
            if (result.code == 0) {
                if (type == 'look') {
                    window.open(url, '_blank');
                    return;
                }
                $('#vm_proxy_table').bootstrapTable('refresh');
                UIToastr.showSuccess(LANG.UI_VM_MACHINE_OPERATION, result.message);
            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_OPERATION, result.message);
            }
        });
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 241;

        $("#vm_proxy_list .fixed-table-body").css({
            "height": height
        });
    }
    return {
        //main function to initiate the module
        init: function () {
            initDataTable();
            initTableHeight();
        }
    };

}();

jQuery(document).ready(function () {
    vmProxyGateway.init();
});
