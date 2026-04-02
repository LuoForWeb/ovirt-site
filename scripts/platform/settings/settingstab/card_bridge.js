var vmNetwork = function () {
    let queryParams = {};
    let changeHeightFlag = false;
    var initListenerNetwork = function () {
        var des = '';
        if($.inArray('p_card_bridge_delte', CONF.PERMISSION_ARR) !== -1){
            des += '<div class="btn-group del-parent-div">' +
                '<div id="delete-vm_network" class="exch-forbid-event" style="">' +
                '<i class="viconfont vicon-a-Deleteshanchu1"></i>' +
                '</div>' +
                '</div>';
        }

        des += $('#vin_network_current_toolbar .leftTool_network').html();
        if($.inArray('p_card_bridge_add', CONF.PERMISSION_ARR) !== -1) {
            des += '<div class="btn-group">' +
                '<label class="">' +
                '<button class="btn table-toolbar-btn" id="add-vm_network" style="color: inherit;">' +
                '<i class="viconfont vicon-biaogetianjia"></i><span>' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + '</span>' +
                '</button>' +
                '</label>' +
                '</div>';
        }
        if($.inArray('p_card_bridge_isolate_network', CONF.PERMISSION_ARR) !== -1) {
            des += '<div class="btn-group">' +
                '<label class="">' +
                '<button class="btn table-toolbar-btn" id="edit-vm_network" style="color: inherit;">' +
                '<i class="viconfont vicon-wangluo"></i><span>' + LANG.UI_PLATFORM_CARD_BRIDEG_ISOLATE + '</span>' +
                '</button>' +
                '</label>' +
                '</div>';
        }

        $('.leftTool_network').html(des);
        //绑定事件
        toBindEventNetwork();
    }


    // 获取参数
    var getParamsNetwork = function (params) {
        queryParams.search = $('.vm-search_network').val();
        return queryParams;
    }

    // 高度改变
    var change_height_network = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#vm_network_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'height': '40px',
                'padding-bottom': '10.25px'
            })
            $('#vm_network_table>tbody>tr').css(
                'cssText', 'height: 60px!important',
            )
            $('#vin_network_current_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#vm_network_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'height': '30px',
                'padding-bottom': '4.25px'
            })
            $('#vm_network_table>tbody>tr').css(
                'cssText', 'height: 40px!important',
            )
            $('#vin_network_current_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }

    // 初始化表格 网络列表
    var initDataTable = function () {
        const options = {
            toolbarId: '#vin_network_current_toolbar',
            buttonsToolbar: '.vin_network_btnToolbar',
            vin_url: "/api/v1/system/network/bridge",
            vin_method: "GET",
            vin_params: function () {
                // 所有自定义携带参数，必须return
                var params = {};
                params['search'] = $('.vm-search_network').val();
                return params;
            },
            changeHeightBtn: true, //改变高度按钮
            singleSelect:true,
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            // resizable: true, //可变宽度
            searchInput: true, //搜索框
            searchClass: 'vm-search_network',
            searchSelector: '.vm-search_network', //使用哪个搜索框
            placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
            searchOnEnterKey:true, //回车搜索
            // showExport: false, //是否开启导出按钮
            onResetView: initTableHeight,
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                    formatter : function (index,row) {
                        if (row.flag == true) {
                            return {
                                disabled: true
                            }
                        }
                    }
                },
                {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'name',
                    title: LANG.UI_VM_MACHINE_NAME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'child',
                    title: LANG.UI_CARD_BRIDGE_CHILD,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'ipaddr',
                    title: LANG.UI_NODE_IP_ADDRESS,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'gateaway',
                    title: LANG.UI_DRILLS_NETWORK_WAY,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'node',
                    title: LANG.UI_DB_NODE,
                    sortable: false,
                    align: 'center',
                },

            ],
            onCheck: function (rowdata) {
                modifyDelStyleNetwork();
            },
            onUncheck: function (rowdata) {
                modifyDelStyleNetwork();
            },
            onCheckAll: function (alldata) {
                modifyDelStyleNetwork();
            },
            onUncheckAll: function (alldata) {
                modifyDelStyleNetwork();
            },
            PostBody: function () {
                $('#vm_network_table th[data-field="node"]').css('width','30%');
                $('#vm_network_table th[data-field="ipaddr"]').css('width','13%');
                $('#vm_network_table th[data-field="gateaway"]').css('width','12%');
                $('#vm_network_table th[data-field="child"]').css('width','10%');
                $('#vm_network_table th[data-field="name"]').css('width','20%');
                modifyDelStyleNetwork();
            }
        };
        $('#vm_network_table').baseTableConfig().init(options);

        initListenerNetwork();
    }

    var modifyDelStyleNetwork = function () {
        var selectedRow = $('#vm_network_table').bootstrapTable('getSelections');
        if (selectedRow.length < 1) {
            $('#delete-vm_network').addClass('exch-forbid-event');
            $('#vin_network_current_toolbar .del-parent-div').css({"cursor": "not-allowed"});
        } else {
            $('#delete-vm_network').removeClass('exch-forbid-event');
            $('#vin_network_current_toolbar .del-parent-div').css({"cursor": "pointer"});
        }
    }

    var toBindEventNetwork = function () {

        // 改变表格高度
        $('#vin_network_current_toolbar .change_height').on('click', change_height_network);

        $('.vm-search_network').keypress(function (e) {
            if (e.which == 13) {
                getParamsNetwork();
                $('#vm_network_table').bootstrapTable('refresh', {
                    query: queryParams
                });
            }
        });

        $('#vin_network_current_toolbar .leftTool_network .search-btn').on('click', function(){
            getParamsNetwork();
            $('#vm_network_table').bootstrapTable('refresh', {
                query: queryParams
            });
        })

        // 添加
        $('#add-vm_network').on('click', function () {
            $('#name').val('');
            $('#card_type_id').val('0');
            // 打开模态
            $('#vm_network_modal').modal({'width': '800px', 'height': '360px'});
        });
        // 网段隔离
        $('#edit-vm_network').on('click', function () {
            Metronic.blockUI({target: '#edit_vm_network_modal',animate: true,cenrerY: true,});
            pAjaxRequest({}, '/api/v1/system/network/isolate', "GET", function (result) {
                Metronic.unblockUI('#edit_vm_network_modal');
                if (result.code == 0) {
                    // 赋值
                    var data = result.data;
                    $('#ip_start').val(data.ip_start);
                    $('#ip_end').val(data.ip_end);
                    $('#ip_mask').val(data.ip_mask);
                    // 打开模态
                    $('#edit_vm_network_modal').modal({'width': '800px', 'height': '300px'});
                } else {
                    UIToastr.showError(msg, result.message);
                }
            });
        });
        //删除
        $('#delete-vm_network').on('click', function () {
            deleteVmNetwork();
        });

        // 添加/修改 网卡事件
        $("#current_work_submit").on("click", function (){
            let data = {
                name: $('#name').val(),
                node_uuid: $('#nodeSelect2').val(),
                card: $('#card_type_id').val()
            };

            let msg = LANG.UI_CARD_BRIDEG_ADD;

            if (data.card == '0') {
                // UIToastr.showWarning(msg, LANG.UI_CARD_BRIDEG_ADD_TIPS);
                // return;
            }

            if (data.name == '') {
                UIToastr.showWarning(msg, LANG.UI_CARD_BRIDEG_ADD_NAME_TIPS);
                return;
            }

            const regex = /^[a-zA-Z0-9_-]+$/; // 注意这里使用了 + 而不是 *
            if (!regex.test(data.name)){
                $('#name').focus();
                UIToastr.showWarning(msg, LANG.UI_CARD_BRIDEG_ADD_NAME_TIPS2);
                return;
            }

            Metronic.blockUI({target: '#vm_network_modal',animate: true,cenrerY: true,});
            pAjaxRequest(data, '/api/v1/system/network/bridge', "POST", function (result) {
                Metronic.unblockUI('#vm_network_modal');
                if (result.code == 0) {
                    // 刷新列表
                    $('#vm_network_table').bootstrapTable('refresh', {
                        query: {offset:0}
                    });
                    initCardSelect();
                    UIToastr.showSuccess(msg, result.message);
                    // 关闭模态
                    $('#vm_network_modal').modal('hide');
                } else {
                    UIToastr.showError(msg, result.message);
                }
            });
        });

        // 清除 网卡事件
        $("#current_sqr_submit").on("click", function (){
            var selectedRow = $('#vm_network_table').bootstrapTable('getSelections');
            if (selectedRow.length == 0) {
                UIToastr.showWarning(LANG.UI_CARD_BRIDEG_DELETE, LANG.UI_VM_MACHINE_MODIFY_CHOOSE_ONE);
                return;
            }
            var uuids = [selectedRow[0].uuid];
            Metronic.blockUI({target: '#vm_resource_modal',animate: true,cenrerY: true,});
            pAjaxRequest({uuids:uuids}, "/api/v1/system/network/bridge", "DELETE", function (result) {
                if (result.code == 0) {
                    $('#delete-vm_network').addClass('exch-forbid-event');
                    $('#vin_network_current_toolbar .del-parent-div').css({"cursor": "not-allowed"});
                    $('#vm_network_table').bootstrapTable('refresh');
                    initCardSelect();
                    UIToastr.showSuccess(LANG.UI_CARD_BRIDEG_DELETE, result.message);
                    $('#vm_resource_modal').modal('hide');
                } else {
                    UIToastr.showError(LANG.UI_CARD_BRIDEG_DELETE, result.message);
                }
                Metronic.unblockUI('#vm_resource_modal');
            });
        });

        // 网段隔离配置网卡事件
        $("#isolate_work_submit").on("click", function (){
            let data = {
                ip_start: $('#ip_start').val(),
                ip_end: $('#ip_end').val(),
                ip_mask: $('#ip_mask').val()
            };
            let msg = LANG.UI_PLATFORM_CARD_BRIDEG_ISOLATE;
            if (!ipV4V6(data.ip_start) || !ipV4V6(data.ip_end) || !ipV4V6(data.ip_mask)) {
                UIToastr.showWarning(msg, LANG.UI_SETTING_INPUT_IP);
                return;
            }

            Metronic.blockUI({target: '#edit_vm_network_modal',animate: true,cenrerY: true,});
            pAjaxRequest(data, '/api/v1/system/network/isolate', "POST", function (result) {
                Metronic.unblockUI('#edit_vm_network_modal');
                if (result.code == 0) {
                    UIToastr.showSuccess(msg, result.message);
                    // 关闭模态
                    $('#vm_network_table').bootstrapTable('refresh');
                    $('#edit_vm_network_modal').modal('hide');
                } else {
                    UIToastr.showError(msg, result.message);
                }
            });
        });

        // 节点事件
        $("#nodeSelect2").on("change", function (){
            // 桥接 获取网卡列表
            initCardSelect()
        });
    }

    //删除
    var deleteVmNetwork = function () {
        var selectedRow = $('#vm_network_table').bootstrapTable('getSelections');
        if (selectedRow.length == 0) {
            UIToastr.showWarning(LANG.UI_CARD_BRIDEG_DELETE, LANG.UI_VM_MACHINE_MODIFY_CHOOSE_ONE);
            return;
        }
        // 打开模态
        $('#vm_resource_modal').modal({'width': '800px', 'height': '215px'});
    }

    //初始化所有备份节点
    var initNodeSelectNetwork = function(){
        pAjaxRequest({}, '/api/v1/nodes/select', 'GET', function (d) {
            var data = d.data;
            var nodeSelect = $('#vm_network_searchmodal #nodeSelectNetwork');
            nodeSelect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
            nodeSelect.append(option);
            for(var i=0; i<data.length; i++){
                option = $("<option>").text(data[i].text).val(data[i].uuid);
                nodeSelect.append(option);
            }
            nodeSelect.val('0');

            var nodeSelect = $('#vm_network_modal #nodeSelect2');
            nodeSelect.empty();
            for(var i=0; i<data.length; i++){
                option = $("<option>").text(data[i].text).val(data[i].uuid);
                nodeSelect.append(option);
            }

            var nodeSelect2 = $('#vm_resource_modal #nodeSelectResource');
            nodeSelect2.empty();
            var option = '';
            for(var i=0; i<data.length; i++){
                option = $("<option>").text(data[i].text).val(data[i].uuid);
                nodeSelect2.append(option);
            }

            initCardSelect()
        });
    }

    //初始化所有网卡
    var initCardSelect = function(network_uuid = '0'){
        var data = {
            node_uuid: $('#nodeSelect2').val(),
            type: 'NIC_DEVICE_TYPE_COMMON,NIC_DEVICE_TYPE_BOND'
        };
        pAjaxRequest(data, "/api/v1/virtual/netcard", "GET", function (result) {
            if (result.code == 0) {
                var data = result.data.rows;
                var cardSelect = $('#vm_network_modal #card_type_id');
                cardSelect.empty();
                var option = $("<option>").text(LANG.UI_NIC_TEAMING_SELECT).val('0');
                cardSelect.append(option);
                for(var i=0; i<data.length; i++){
                    // 去除下 vir 和 vnet的网卡
                    if (data[i].name.startsWith('vir') || data[i].name.startsWith('vnet') ) {
                        continue;
                    }
                    option = $("<option>").text(data[i].name).val(data[i].value);
                    cardSelect.append(option);
                }
                cardSelect.val(network_uuid);
            }
        });
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 381;

        $("#vm_network_list .fixed-table-body").css({
            "height": height
        });

    }

    return {
        init: function () {
            initDataTable(); // 网络管理
            initNodeSelectNetwork(); //初始化所有节点
            initTableHeight();
        }
    };

}();

jQuery(document).ready(function () {
    vmNetwork.init();
});
