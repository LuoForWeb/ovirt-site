var vmNetwork = function () {
    let queryParams = {};
    let changeHeightFlag = false;
    let uuid;
    let isEdit = false;
    let initResource = {};
    var networkEcharts1,networkEcharts2,networkEcharts3;
    var initListenerNetwork = function () {
        var des = '';
        if($.inArray('p_vm_machine_network_delete', CONF.PERMISSION_ARR) !== -1){
            des += '<div class="btn-group del-parent-div" style="margin-right: 0px; ">' +
                '<div id="delete-vm_network" class="exch-forbid-event" style="">' +
                '<i class="viconfont vicon-a-Deleteshanchu1"></i>' +
                '</div>' +
                '</div>';
        }

        des += $('#vin_network_current_toolbar .leftTool_network').html();
        if($.inArray('p_vm_machine_network_add', CONF.PERMISSION_ARR) !== -1) {
            des += '<div class="btn-group">' +
                '<label class="">' +
                '<button class="btn table-toolbar-btn" id="add-vm_network" style="">' +
                '<i class="viconfont vicon-biaogetianjia"></i><span>' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + '</span>' +
                '</button>' +
                '</label>' +
                '</div>';
        }
        /*if($.inArray('p_vm_machine_network_modify', CONF.PERMISSION_ARR) !== -1) {
        des += '<div class="btn-group">' +
            '<label class="">' +
            '<button class="btn table-toolbar-btn" id="edit-vm_network" style="">' +
            '<i class="viconfont vicon-xiugai"></i><span>' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY + '</span>' +
            '</button>' +
            '</label>' +
            '</div>';
        }*/

        $('.leftTool_network').html(des);
        //绑定事件
        toBindEventNetwork();
        // 高级搜索初始化
        initAdvanceNetwork();

        $('#moreMachine').on('click', function () {
            $('a[href="#vmMachinediv"]').click();
        });
    }

    // 高级搜索事件
    var initAdvanceNetwork = function () {
        // 改变表格高度
        $('#vin_network_current_toolbar .change_height').on('click', change_height_network);

        //高级搜索发送参数并添加展示
        $("#current_network_search_submit").on("click", adv_search_network);

        //高级搜索清除
        $("#vin_network_current_toolbar .advanced_list").on("click", ".adv_clear", adv_clearSearchNetwork);
        //弹出高级搜索模态框
        $('#vin_network_current_toolbar #advanced-search-btn').on('click', function () {
            $('#vm_network_searchmodal').modal({
                'width': '800px',
                'height': '250px'
            });
        });

        // 鼠标滑过按钮显示提示
        $('#vin_network_current_toolbar #advanced-search-btn').hover(
            function () {
                if ($("#vin_network_current_toolbar .advanced_list #list_content").find("li").length > 0) {
                    $("#vin_network_current_toolbar .advanced_list").show();
                }
            },
            function () {}
        )

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

        // 鼠标移开关闭提示
        $("#vin_network_current_toolbar .advanced_list").on("mouseleave", function (e) {
            $("#vin_network_current_toolbar .advanced_list").hide();
        })

        //清除所有
        $("#vin_network_current_toolbar #clear_adv").on("click", function () {
            $("#vin_network_current_toolbar .adv_list").remove();
            $('#vin_network_current_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP);
            $("#card_type").val(0);
            $('#vm_network_searchmodal #nodeSelectNetwork').val(0);

            $("#vin_network_current_toolbar .advanced_list").hide();
            getParamsNetwork();
            $("#vm_network_table").bootstrapTable("refresh", {
                query: queryParams
            });
        })
    }

    //高级搜索展示
    var adv_search_network = function () {
        getParamsNetwork();
        $("#vm_network_table").bootstrapTable("refresh", {
            query: queryParams
        });
        $('#vm_network_searchmodal').modal('hide');
        $("#vin_network_current_toolbar .adv_list").remove();

        if ($("#nodeSelectNetwork").val() != "0" && $("#nodeSelectNetwork").val() != null) {
            let value = $("#nodeSelectNetwork").find('option:selected').text();
            $("#vin_network_current_toolbar #list_content").append('<li class="adv_list" titles="nodeSelect" title="'+LANG.UI_PUBLIC_BACKUP_NODE + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#card_type").val() != "0" && $("#card_type").val() != null) {
            let value = $("#card_type").find('option:selected').text();
            $("#vin_network_current_toolbar #list_content").append('<li class="adv_list" titles="card_type" title="'+LANG.UI_NODE_NETWORK_TABLE_TYPE + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#vin_network_current_toolbar #list_content").find(".adv_list").length > 0) {
            $('#vin_network_current_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP + "(" + $("#vin_network_current_toolbar #list_content").find(".adv_list").length + "/2)");
        }
    }

    var adv_clearSearchNetwork = function () {
        $(this).parent().remove();
        $("#" + $(this).parent().attr("titles") + "").val("");
        getParamsNetwork();
        $("#vm_network_table").bootstrapTable("refresh", {
            query: queryParams
        });
        if ($("#vin_network_current_toolbar #list_content").find(".adv_list").length > 0) {
            $('#vin_network_current_toolbar #advanced-search-btn span').text( LANG.UI_JOB_SEARCH_EXP+"(" + $("#vin_network_current_toolbar #list_content").find(".adv_list").length + "/2)");
        } else {
            $('#vin_network_current_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP);
            $("#vin_network_current_toolbar .advanced_list").hide();
        }
    }

    // 获取参数
    var getParamsNetwork = function (params) {
        queryParams.search = $('.vm-search_network').val();
        queryParams.node_uuid = $('#vm_network_searchmodal #nodeSelectNetwork').val();
        queryParams.card_type = $('#vm_network_searchmodal #card_type').val();
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
            vin_url: "/api/v1/virtual/network",
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
            // advanceSearch: {
            //    module: 'list'
            // }, //高级搜索
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
                    align: 'center',
                },
                {
                    field: 'bridge_name',
                    title: LANG.UI_VM_MACHINE_NETWORK_TYPE2,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'network',
                    title: LANG.UI_VM_MACHINE_NETWORK_IP,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'flag_value',
                    title: LANG.UI_VM_MACHINE_TYPE,
                    align: 'center',
                },
                {
                    field: 'node',
                    title: LANG.UI_DB_NODE,
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
                $('#vm_network_table th[data-field="flag_value"]').css('width','10%');
                $('#vm_network_table th[data-field="type_value"]').css('width','15%');
                $('#vm_network_table th[data-field="name"]').css('width','20%');

            }
        };

        if ($('#vm_network_table').children().length === 0) {
            $('#vm_network_table').baseTableConfig().init(options);
            initListenerNetwork();
        }
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
        // 添加
        $('#add-vm_network').on('click', function () {
            isEdit = false; // 表示添加
            $('#show_class').attr('class', 'viconfont vicon-danchuangtianjia1');
            $('#carkName').val('');
            $('#card_type_id').val('0');
            $('#show_network_name').html(LANG.UI_VM_MACHINE_ADD_NETWORKS);
            // 打开模态
            $('#vm_network_modal').modal({'width': '800px', 'height': '360px'});
        });
        //修改
        $('#edit-vm_network').on('click', function () {
            isEdit = true; // 表示修改
            $('#show_class').attr('class', 'viconfont vicon-ge_modify');
            $('#show_network_name').html(LANG.UI_VM_MACHINE_MODIFY_NETWORKS);
            editVmNetwork();
        });
        //删除
        $('#delete-vm_network').on('click', function () {
            deleteVmNetwork();
        });

        // 添加/修改 网卡事件
        $("#current_work_submit").on("click", function (){
            var forword_mode = 4;
            if ($('#card_type_id').val() != '0') {
                forword_mode = 3;
            }
            let data = {
                name: $('#carkName').val(),
                forword_mode: forword_mode,
                node_uuid: $('#nodeSelect2').val(),
                bridge_name: $('#card_type_id').val()
            };

            let url = '';
            let msg = LANG.UI_VM_MACHINE_ADD_NETWORKS;
            if (data.bridge_name == '0') {
                // 暂时不让创建隔离网络
                UIToastr.showWarning(msg, LANG.UI_VM_MACHINE_CHOOSE_ONE_CARD);
                return;
            }

            const regex = /^[a-zA-Z0-9_-]+$/; // 注意这里使用了 + 而不是 *
            if (!regex.test(data.name)){
                $('#carkName').focus();
                UIToastr.showWarning(msg, LANG.UI_CARD_BRIDEG_ADD_NAME_TIPS2);
                return;
            }

            if (isEdit) {
                // 修改
                url = '/api/v1/virtual/network/' + uuid;
                msg = LANG.UI_VM_MACHINE_MODIFY_NETWORKS;
            } else {
                // 添加
                url = '/api/v1/virtual/network';
            }
            if (data.forword_mode == 3 && data.bridge_name == '0') {
                UIToastr.showWarning(LANG.UI_VM_MACHINE_CHOOSE_ONE_CARD, msg);
                return;
            }
            Metronic.blockUI({target: '#vm_network_modal',animate: true,cenrerY: true,});
            pAjaxRequest(data, url, "POST", function (result) {
                Metronic.unblockUI('#vm_network_modal');
                if (result.code == 0) {
                    // 刷新列表
                    $('#vm_network_table').bootstrapTable('refresh', {
                        query: {offset:0}
                    });
                    UIToastr.showSuccess(msg, result.message);
                    // 关闭模态
                    $('#vm_network_modal').modal('hide');
                    // 再次初始化网卡，因为网卡桥接后就不是普通网卡了，所以添加
                    initCardSelect();
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

    //修改
    var editVmNetwork = function () {
        var selectedRow = $('#vm_network_table').bootstrapTable('getSelections');
        if (selectedRow.length > 1 || selectedRow.length == 0) {
            UIToastr.showWarning(LANG.UI_VM_MACHINE_MODIFY_NETWORKS, LANG.UI_VM_MACHINE_MODIFY_CHOOSE);
            return;
        }
        Metronic.blockUI({target: '#vm_network_table',animate: true,cenrerY: true,});
        uuid = selectedRow[0].uuid;
        pAjaxRequest({}, "/api/v1/virtual/network/" + uuid, "GET", function (result) {
            Metronic.unblockUI('#vm_network_table');
            if (result.code == 0) {
                let data = result.data;
                $('#nodeSelect2').val(data.node_uuid);
                $('#carkName').val(data.name);

                $('#card_type_id').val(data.bridge_name);
                initCardSelect(data.bridge_name);

                // 打开模态
                $('#vm_network_modal').modal({'width': '800px', 'height': '360px'});
            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_MODIFY_NETWORKS, result.message);
                return;
            }
        });
    }

    //删除
    var deleteVmNetwork = function () {
        var selectedRow = $('#vm_network_table').bootstrapTable('getSelections');
        if (selectedRow.length == 0) {
            UIToastr.showWarning(LANG.UI_VM_MACHINE_DEL_NETWORKS, LANG.UI_VM_MACHINE_MODIFY_CHOOSE_ONE);
            return;
        }
        //删除策略二次确认框
        bootbox.confirm({
            title: LANG.UI_VM_MACHINE_DEL_TIPS,
            message: LANG.UI_VM_MACHINE_DEL_NETWORK_CONTENT,
            callback: debounce(function (r) {
                if (!r) return;
                Metronic.blockUI({target: '#vm_network_table',animate: true,cenrerY: true,});
                var uuids = [];
                for (var i in selectedRow){
                    uuids.push(selectedRow[i].uuid);
                }
                pAjaxRequest({id_list:uuids}, "/api/v1/virtual/network", "DELETE", function (result) {
                    if (result.code == 0) {
                        $('#delete-vm_network').addClass('exch-forbid-event');
                        $('#vin_network_current_toolbar .del-parent-div').css({"cursor": "not-allowed"});
                        $('#vm_network_table').bootstrapTable('refresh');
                        UIToastr.showSuccess(LANG.UI_VM_MACHINE_DEL_NETWORKS, result.message);
                    } else {
                        UIToastr.showError(LANG.UI_VM_MACHINE_DEL_NETWORKS, result.message);
                    }
                    Metronic.unblockUI('#vm_network_table');
                });
            }, 300)
        });
    }

    //初始化所有备份节点
    var initNodeSelectNetwork = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeList',p:{}}, function(d){
            var data = JSON.parse(d);
            var nodeSelect = $('#vm_network_searchmodal #nodeSelectNetwork');
            nodeSelect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
            nodeSelect.append(option);
            for(var i=0; i<data.length; i++){
                option = $("<option>").text(data[i].node_name).val(data[i].node_uuid);
                nodeSelect.append(option);
            }
            nodeSelect.val('0');

            var nodeSelect = $('#vm_network_modal #nodeSelect2');
            nodeSelect.empty();
            for(var i=0; i<data.length; i++){
                option = $("<option>").text(data[i].node_name).val(data[i].node_uuid);
                nodeSelect.append(option);
            }

            var nodeSelect2 = $('#vm_resource_modal #nodeSelectResource');
            nodeSelect2.empty();
            var option = '';
            for(var i=0; i<data.length; i++){
                option = $("<option>").text(data[i].node_name).val(data[i].node_uuid);
                nodeSelect2.append(option);
            }

            initCardSelect()
        });
    }

    //初始化所有网卡
    var initCardSelect = function(network_uuid = '0'){
        pAjaxRequest({node_uuid: $('#nodeSelect2').val()}, "/api/v1/virtual/netcard", "GET", function (result) {
            if (result.code == 0) {
                var data = result.data.rows;
                var cardSelect = $('#vm_network_modal #card_type_id');
                cardSelect.empty();
                var option = $("<option>").text(LANG.UI_NIC_TEAMING_SELECT).val('0');
                cardSelect.append(option);
                for(var i=0; i<data.length; i++){
                    // 去除下 vir 和 vnet的网卡
                    if (data[i].name.startsWith('virbr')) {
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
        $("#vm_network_list .fixed-table-body").css({
            "height": 300
        });

        $("#vm_machine_list .fixed-table-body").css({
            "height": 162
        });

        $("#vm_machine_list .fixed-table-body .no-records-found").css({
            "top": 80
        });

        $("#vm_node_list .fixed-table-body").css({
            "height": 162
        });

        $("#vm_node_list .fixed-table-body .no-records-found").css({
            "top": 80
        });
    }

    // 获取系统的资源配置
    var getResource = function (){
        var node_uuid = $("#vm_resource_modal #nodeSelectResource").val();
        // 获取系统的资源配置
        Metronic.blockUI({target: '#vm_resource_modal',animate: true,cenrerY: true,});
        pAjaxRequest({node_uuid: node_uuid}, "/api/v1/virtual/resources", "GET", function (result) {
            Metronic.unblockUI('#vm_resource_modal');
            if (result.code == 0) {
                let data = result.data
                initResource = data;
                $('#cpu_total').text(data.cpu_total);
                $('#cpus').val(data.cpus);
                $('#cpus_virus').val(data.cpus_virus);
                $('#used_cpu').text(data.used_cpu);
                $('#emdCpuMode').val(data.cpu_mode);
                // 返回的mb，需要转为gb
                var memory_total = data.memory_total / 1024;
                $('#memory_total').text(parseFloat(memory_total.toFixed(2)));
                var mems = data.mems / 1024;
                $('#mems').val(parseFloat(mems.toFixed(2)));
                $('#modal_resource_memory_type').val(2);

                var mems_virus = data.mems_virus / 1024;
                $('#mems_virus').val(parseFloat(mems_virus.toFixed(2)));
                $('#modal_resource_memory_type_virus').val(2);

                var used_memory = data.used_memory / 1024;
                $('#used_memory').text(parseFloat(used_memory.toFixed(2)));

                // if (data.cpus_virus == 0 || data.mems_virus == 0) {
                // 关闭沙箱配置
                //  $('#virus_witch').bootstrapSwitch('state', false);
                //} else {
                $('#virus_witch').bootstrapSwitch('state', true);
                //}
            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_RESOURCES, result.message);
            }
        });
    }

    var initResources = function () {
        // 节点事件
        $("#vm_resource_modal #nodeSelectResource").on("change", function (){
            // 桥接 获取网卡列表
            getResource();
        });
        // 初始化cpu模式下拉
        var emdCpuMode = $('#vm_resource_modal #emdCpuMode');
        emdCpuMode.empty();
        var option = '';
        option = $("<option>").text(LANG.UI_VERIFY_CPU_MODE_CUSTOM).val(0);
        emdCpuMode.append(option);
        var data = CONF.CPU_MODE;
        for(var i in data){
            option = $("<option>").text(data[i]).val(i);
            emdCpuMode.append(option);
        }

        // 资源隔离
        $('#quar-vm_list').on('click', function (){
            getResource();
            // 打开模态
            $('#vm_resource_modal').modal({'width': '800px', 'height': '100%'});
        });
        // 监听核心数的输入
        $('#cpus').on('input', function (){
            // 获取当前输入框的值
            var value = $(this).val();

            // 移除所有非数字字符
            value = value.replace(/[^\d]/g, '');
            $(this).val(value);
            // 检查长度是否超过10位
            if (value.length > 10) {
                // 截断到10位
                $(this).val(value.substring(0, 10));
            }
        })
        $('#mems').on('input', function (){
            var value = $(this).val();

            // 移除所有非数字字符，但保留小数点
            value = value.replace(/[^\d.]/g, '');
            $(this).val(value);
            // 如果输入超过10位，则截断它
            if (value.length > 10) {
                $(this).val(value.substring(0, 10));
            }
        })

        $('#virus_witch').bootstrapSwitch('onSwitchChange', function (e, data) {
            if(data){
                $('.virus_item_div').show();	//开
            }else{
                $('.virus_item_div').hide();	//关
            }
        });

        // 监听核心数的输入
        $('#cpus_virus').on('input', function (){
            // 获取当前输入框的值
            var value = $(this).val();

            // 移除所有非数字字符
            value = value.replace(/[^\d]/g, '');
            $(this).val(value);
            // 检查长度是否超过10位
            if (value.length > 10) {
                // 截断到10位
                $(this).val(value.substring(0, 10));
            }
        })
        $('#mems_virus').on('input', function (){
            var value = $(this).val();

            // 移除所有非数字字符，但保留小数点
            value = value.replace(/[^\d.]/g, '');
            $(this).val(value);
            // 如果输入超过10位，则截断它
            if (value.length > 10) {
                $(this).val(value.substring(0, 10));
            }
        })

        // 资源隔离修改事件绑定
        $("#current_sqr_submit").on("click", function (){
            var resourceName = LANG.UI_VM_MACHINE_RESOURCES;
            // 系统的资源配置
            var cpus = $('#cpus').val();
            var cpus_virus = $('#cpus_virus').val();
            var cpu_mode = $('#emdCpuMode').val();
            var mems_now = $('#mems').val();
            var mems_now_virus = $('#mems_virus').val();
            if (cpus == 0 || mems_now == 0) {
                UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_MEMS_CPUS);
                return;
            }
            // 需要把如果内存的改为mb
            var memory_type = $('#modal_resource_memory_type').val();
            var mems = memory_type > 1 ?  Math.pow(1024, memory_type - 1) * mems_now : mems_now;

            var memory_type_virus = $('#modal_resource_memory_type_virus').val();
            var mems_virus = memory_type_virus > 1 ?  Math.pow(1024, memory_type_virus - 1) * mems_now_virus : mems_now_virus;

            var node_uuid = $('#vm_resource_modal #nodeSelectResource').val();

            if (cpus < initResource['used_cpu']) {
                UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_RESOURCES_USED_CPU + '：' + initResource['used_cpu']);
                return;
            }
            var initMemsTotal = parseFloat(initResource['memory_total'].toFixed(2));
            if (mems > initMemsTotal) {
                var msg = initMemsTotal + 'MB';
                if (memory_type == 2) {
                    msg = initMemsTotal / 1024 + 'GB';
                } else if (memory_type == 3) {
                    msg = initMemsTotal / 1024 / 1024 + 'TB';
                }

                UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_RESOURCES_MAX_MEMS + '：' + msg);
                return;
            }
            if (mems < initResource['used_memory']) {
                if (memory_type == 2) {
                    var tmpmems = initResource['used_memory'] / 1024;
                    tmpmems = tmpmems + 'GB';
                } else if (memory_type == 3) {
                    var tmpmems = initResource['used_memory'] / 1024 / 1024;
                    tmpmems = tmpmems + 'TB';
                } else {
                    tmpmems = initResource['used_memory'] + 'MB';
                }
                UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_RESOURCES_USED_MEMS + '：' + tmpmems);
                return;
            }

            // cpu 在 备份系统的1.5-3倍之间
            var min_cpu = initResource.cpu_total * 0.5;
            var max_cpu = initResource.cpu_total * 3;
            if (cpus < min_cpu || cpus > max_cpu) {
                UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_RESOURCES_CPU_RANGE);
                return;
            }
            // 内存：30%-90%
            var min_mems = initMemsTotal * 0.3;
            var max_mems = initMemsTotal * 0.9;
            if (mems < min_mems || mems > max_mems) {
                UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_RESOURCES_MEMS_RANGE);
                return;
            }

            if ($('#virus_witch').get(0).checked) {
                cpus_virus = parseInt(cpus_virus);
                // 沙箱配置开启 需要判断下设置的范围，最小4-4.最大不能超过主机的总数
                if (cpus_virus == initResource.cpu_total && initResource.cpu_total < 4) {
                    // 备份系统的cpu小于 4，此时可以配置备份系统的最大

                } else if (cpus_virus < 4) {
                    UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_RESOURCES_CPU_SAFE);
                    return;
                } else if (cpus_virus > initResource.cpu_total) {
                    UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_RESOURCES_CPU_SAFE_MAX);
                    return;
                }

                if (mems_virus / 1024 < 4) {
                    UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_RESOURCES_CPU_SAFE_MIN);
                    return;
                }
                mems_virus = parseFloat(mems_virus);
                var mems_virus_tmp = parseFloat(mems_virus.toFixed(2))
                if (mems_virus_tmp > initMemsTotal) {
                    UIToastr.showWarning(resourceName, LANG.UI_VM_MACHINE_RESOURCES_CPU_SAFE_MAX_TIP);
                    return;
                }
            } else {
                cpus_virus = 0;
                mems_virus = 0;
            }

            Metronic.blockUI({target: '#vm_resource_modal',animate: true,cenrerY: true,});
            let data = {
                cpus: cpus,
                mems: mems,
                cpus_virus: cpus_virus,
                mems_virus: mems_virus,
                node_uuid: node_uuid,
                cpu_mode:cpu_mode
            };
            pAjaxRequest(data, "/api/v1/virtual/resources", "POST", function (result) {
                Metronic.unblockUI('#vm_resource_modal');
                if (result.code == 0) {
                    UIToastr.showSuccess(resourceName, result.message);
                    // 关闭模态
                    $('#vm_resource_modal').modal('hide');
                    // 重新渲染echars 资源信息
                    $('#vm_node_table').bootstrapTable('refresh');
                    // 销毁图表
                    networkEcharts1.dispose();
                    networkEcharts2.dispose();
                    networkEcharts3.dispose();
                    initEchars();
                } else {
                    UIToastr.showError(resourceName, result.message);
                }
            });
        });

    }

    // 初始化简单版的虚拟机列表
    var initDataTableMachine = function (){
        const options = {
            toolbarId: '#vin_current_toolbar_network_vm',
            buttonsToolbar: '#vin_current_toolbar_network_vm .vin_btnToolbar',
            vin_url: "/api/v1/virtual",
            vin_method: "GET",
            vin_params: function () {
                // 所有自定义携带参数，必须return
                var params = {};
                params['limit'] = 3;
                return params;
            },
            sortName: 'num',
            sortOrder: 'asc',
            pagination: true, //分页
            paginationParts: ['pageInfo', 'pageList'],
            pageList: [3, 10], //每页数量
            pageSize:3,
            resizable: true, //可变宽度
            columns: [
                {
                    field: 'num',
                    title: LANG.UI_VM_MACHINE_NUM,
                    align: 'center',
                },
                {
                    field: 'vm_name',
                    title: LANG.UI_VM_MACHINE_NAME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'memory_total',
                    title: LANG.UI_VM_MACHINE_MEMS,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'vcpu_num',
                    title: LANG.UI_VM_MACHINE_CPU,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    align: 'center',
                    formatter: statusFormatter,
                }
            ],
        };
        // 状态
        function statusFormatter(value, row, index) {
            if (value == 1) {
                return '<span class="label label-sm label-success status-icon">' + row.status_value + '</span>';
            }
            // 停止
            return '<span class="label label-sm label-default status-icon">' + row.status_value + '</span>';
        }
        $('#vm_machine_table').baseTableConfig().init(options);
    }

    // 初始化节点资源隔离列表
    var initDataTableNode = function (){
        const options = {
            toolbarId: '#vin_current_toolbar_network_node',
            buttonsToolbar: '#vin_current_toolbar_network_node .vin_btnToolbar',
            vin_url: "/api/v1/virtual/node_source",
            vin_method: "GET",
            vin_params: function () {
                // 所有自定义携带参数，必须return
                var params = {};
                params['limit'] = 3;
                return params;
            },
            pagination: true, //分页
            paginationParts: ['pageInfo', 'pageList'],
            pageList: [3, 10], //每页数量
            pageSize:3,
            resizable: true, //可变宽度
            columns: [
                {
                    field: 'name',
                    title: LANG.UI_NODE_NODE_NAME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'cpu',
                    title: LANG.UI_VM_RESOURCE_CPU,
                    sortable: false,
                    align: 'center',
                    formatter:function (value, row, index){
                        return row.cpu_used + '/' + row.cpu_max + LANG.UI_VM_MACHINE_CPU_UNIT
                    }
                },
                {
                    field: 'mems',
                    title: LANG.UI_VM_RESOURCE_MEMS,
                    sortable: false,
                    align: 'center',
                    formatter:function (value, row, index){
                        return row.mems_used + '/' + row.mems_max + LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_GB
                    }
                },
                {
                    field: 'nodestatus',
                    title: LANG.UI_NODE_NODE_STATUS,
                    sortable: false,
                    align: 'center',
                    formatter: statusFormatter2,
                },
                {
                    field: 'status',
                    title: LANG.UI_VM_STATUS + '<a class="popovers modal_use_actions ml5" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="'+LANG.UI_VM_VT_TIPS+'" data-original-title="" title="">\n' +
                        '                                        <i class="viconfont vicon-tishi"></i>\n' +
                        '                                    </a>',
                    sortable: false,
                    align: 'center',
                    formatter: statusFormatter,
                },
                /*{
                    field: 'use_actions',
                    title: LANG.UI_VM_MACHINE_VT_STAUS,
                    sortable: false,
                    align: 'center',
                    /!*events: operateEvents,*!/
                    // 生成
                    formatter: function (value, row, index) {
                        var button = '';
                          if (row.use_flag == 1) {
                              // 启用 有禁用状态
                              button = makeSwitch(row, 'checked');
                          } else {
                              // 启用按钮
                              button = makeSwitch(row, '');
                          }
                        return button;
                    }
                },*/
            ],
            onPostBody: function () {
                initializeSwitches();
                $('#vm_node_table').find(".popovers").popover(); // 初始化tips
                if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                    $('#vm_node_table th[data-field="cpu"]').css('width', '20%');
                    $('#vm_node_table th[data-field="mems"]').css('width', '25%');
                    $('#vm_node_table th[data-field="nodestatus"]').css('width', '13%');
                    $('#vm_node_table th[data-field="status"]').css('width', '23%');
                }
            }
        };
        // 状态
        function statusFormatter(value, row, index) {
            if (value == 1) {
                return '<span class="label label-sm label-success status-icon">' + LANG.UI_PUBLIC_ON + '</span>';
            }
            // 关闭
            return '<span class="label label-sm label-default status-icon">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        function statusFormatter2(value, row, index) {
            if (row.mems_max != 0) {
                return '<span class="label label-sm label-success status-icon">' + LANG.UI_NODE_NORMAL + '</span>';
            }
            // 异常
            return '<span class="label label-sm label-default status-icon">' + LANG.UI_NODE_ABNORMAL + '</span>';
        }
        $('#vm_node_table').baseTableConfig().init(options);
        /*setTimeout(function (){
            initializeSwitches();
        },1000)*/

    }

    // 动态生成开关控件
    function generateSwitch(uuid, status, checked) {
        var dis = '';
        if (status != 1) {
            dis = 'disabled';
        }
        return '<input type="checkbox" data-uuid="'+uuid+'" '+ dis +' data-status="'+status+'" '+checked+' class="make-switch switch-node-source" data-size="small"'+
            'data-on-color="primary" data-off-color="info" data-on-text="'+LANG.UI_VM_MACHINE_VT_STAUS_ENABLE+'"'+
            'data-off-text="'+LANG.UI_VM_MACHINE_VT_STAUS_DISABLED+'">';
    }
    // 添加按钮点击事件
    function makeSwitch(row, checked){
        return generateSwitch(row.node_uuid, row.status, checked);
    }

    // 初始化开关控件
    function initializeSwitches() {
        $('.make-switch').bootstrapSwitch();
        // 绑定事件处理器
        $('.switch-node-source').bootstrapSwitch('onSwitchChange', function (e, data) {
            var node_uuid =  $(this).attr('data-uuid');
            var node_status =  $(this).attr('data-status');
            if (node_status != 1) {
                // 不是启用的
                e.preventDefault();
                // 恢复滑块状态
                $(this).bootstrapSwitch('state', !data);
                return false;
            }
            if(data){
                //开
                var param = {
                    node_uuid: node_uuid,
                    type: 1,
                };
                pAjaxRequest(param, '/api/v1/virtual/node_source', 'POST', function (res) {
                    if (res.code == 0) {
                        UIToastr.showSuccess(LANG.UI_VM_MACHINE_VT_STAUS_ENABLE, LANG.UI_VM_MACHINE_VT_STAUS_ENABLE_SUCCESS);
                        $('#vm_node_table').bootstrapTable('selectPage', 1);
                        $('#vm_node_table').bootstrapTable('refresh');
                    } else {
                        UIToastr.showWarning(LANG.UI_VM_MACHINE_VT_STAUS_ENABLE, LANG.UI_VM_MACHINE_VT_STAUS_ENABLE_FAIL);
                    }
                });
            }else{
                var param = {
                    node_uuid: node_uuid,
                    type: 2,
                };
                pAjaxRequest(param, '/api/v1/virtual/node_source', 'POST', function (res) {
                    if (res.code == 0) {
                        UIToastr.showSuccess(LANG.UI_VM_MACHINE_VT_STAUS_DISABLED, LANG.UI_VM_MACHINE_VT_STAUS_DISABLED_SUCCESS);
                        $('#vm_node_table').bootstrapTable('selectPage', 1);
                        $('#vm_node_table').bootstrapTable('refresh');
                    } else {
                        UIToastr.showWarning(LANG.UI_VM_MACHINE_VT_STAUS_DISABLED, LANG.UI_VM_MACHINE_VT_STAUS_DISABLED_FAIL);
                    }
                });
            }
        });
    }

    //操作监听事件
    var operateEvents = {
        //禁用事件
        'click .disabled': function (e, value, row, index) {
            var param = {
                node_uuid: row.node_uuid,
                type: 2,
            };
            pAjaxRequest(param, '/api/v1/virtual/node_source', 'POST', function (res) {
                if (res.code == 0) {
                    UIToastr.showSuccess(LANG.UI_VM_MACHINE_VT_STAUS_DISABLED, LANG.UI_VM_MACHINE_VT_STAUS_DISABLED_SUCCESS);
                    $('#vm_node_table').bootstrapTable('selectPage', 1);
                    $('#vm_node_table').bootstrapTable('refresh');
                } else {
                    UIToastr.showWarning(LANG.UI_VM_MACHINE_VT_STAUS_DISABLED, LANG.UI_VM_MACHINE_VT_STAUS_DISABLED_FAIL);
                }
            });
        },
        //启用事件
        'click .enable': function (e, value, row, index) {
            var param = {
                node_uuid: row.node_uuid,
                type: 1,
            };
            pAjaxRequest(param, '/api/v1/virtual/node_source', 'POST', function (res) {
                if (res.code == 0) {
                    UIToastr.showSuccess(LANG.UI_VM_MACHINE_VT_STAUS_ENABLE, LANG.UI_VM_MACHINE_VT_STAUS_ENABLE_SUCCESS);
                    $('#vm_node_table').bootstrapTable('selectPage', 1);
                    $('#vm_node_table').bootstrapTable('refresh');
                } else {
                    UIToastr.showWarning(LANG.UI_VM_MACHINE_VT_STAUS_ENABLE, LANG.UI_VM_MACHINE_VT_STAUS_ENABLE_FAIL);
                }
            });
        },
    };

    var initEchars = function (){
        // 获取虚拟机和计算资源的一些统计信息
        Metronic.blockUI({target: '#vm_machine_list',animate: true,cenrerY: true,});
        Metronic.blockUI({target: '#vm_node_list',animate: true,cenrerY: true,});
        pAjaxRequest({}, "/api/v1/virtual/statist", "GET", function (result) {
            Metronic.unblockUI('#vm_machine_list');
            Metronic.unblockUI('#vm_node_list');
            if (result.code == 0) {
                initsEcharts(result.data);
            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_RESOURCES, result.message);
            }
        });
    }
    function initsEcharts(data) {
        networkEcharts1 = echarts.init(document.getElementById('network_echars1'));
        // `{val|${nasonlineNum + nasofflineNum}}\n{name|${LANG.UI_HOMEPAGE_NAS_DEVICE}}`,
        let option = {
            title: [{
                text: `{name|${data.vm[0]}}\n{val|${LANG.UI_VM_RESOURCE_TOTAL}}`,
                top: '50px',
                left: 'center',
                textStyle: {
                    rich: {
                        name: {
                            fontSize: 24,
                            color: '#4C5563',
                            fontWeight: 500
                        },
                        val: {
                            fontSize: 14,
                            fontWeight: 400,
                            color: '#ADADB0'
                        }
                    }
                }
            }],
            legend: {
                top: '20px',
                orient: 'vertical',
                right: '10px',
                itemGap: 20,
                icon: 'circle'
            },
            series: [
                {
                    name: '',
                    type: 'pie',
                    silent: true,
                    clockWise: false, //顺时加载
                    radius: ['80%', '99%'],
                    center: ["50%", "50%"],
                    color: ["#199FE4","#F19F00", "#E8EEF3"],
                    label: {
                        show: false,
                    },
                    emphasis: {
                        label: {
                            show: false
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: [
                        { value: data.vm[1], name: LANG.UI_VISUAL_RUN + ' ' + data.vm[1] },
                        { value: data.vm[2], name: LANG.UI_VISUAL_NODE_ABNORMAL + ' ' + data.vm[2] },
                        { value: data.vm[3], name: LANG.UI_PUBLIC_OFF + ' ' + data.vm[3] }
                    ]
                }
            ]
        };
        networkEcharts1.setOption(option);

        networkEcharts2 = echarts.init(document.getElementById('network_echars2'));
        let option2 =  {
            title: [{
                text: `{name|${data.cpus[1]}}{name2|${LANG.UI_VM_MACHINE_CPU_UNIT}}\n{val|${LANG.UI_VM_RESOURCE_OUT}}`,
                top: '60px',
                left: 'center',
                textStyle: {
                    rich: {
                        name: {
                            fontSize: 20,
                            color: '#2D3748',
                            padding:[0, 5],
                            fontWeight: 400
                        },
                        name2: {
                            fontSize: 12,
                            color: '#979797'
                        },
                        val: {
                            fontSize: 12,
                            padding:[4, 0],
                            color: '#979797'
                        },
                    }
                }
            },{
                text: `{name|${LANG.UI_VM_RESOURCE_CPU_NAME} (${LANG.UI_VM_RESOURCE_TOTAL}：${data.cpus[0]}${LANG.UI_VM_MACHINE_CPU_UNIT})}`,
                top: '-10px',
                left: 'center',
                textStyle: {
                    rich: {
                        name: {
                            fontSize: 14,
                            color: '#76767B',
                            fontWeight: 400,
                            padding: [7, 0]
                        }
                    }
                }
            }],
            tooltip: {
                trigger: 'item',
                // 确保tooltip不影响底色
                formatter: function(params) {
                    if(params.name === '') return null; // 不显示空白名称的tooltip
                    return params.name + ': ' + params.value;
                }
            },
            legend: {
                bottom: '10px',
                left: 'center',
                icon: 'square'
            },
            series: [
                {
                    name: '',
                    type: 'pie',
                    radius: ['85%', '95%'],
                    center: ['50%', '70%'],
                    color: ["#50CD89","#E8EEF3"],
                    startAngle: 180,
                    endAngle: 360,
                    label: {
                        show: false, // 隐藏扇区上的标签
                    },
                    labelLine: {
                        show: false // 隐藏扇区上的引导线
                    },
                    data: [
                        {
                            value: data.cpus[2],
                            name: LANG.UI_PUBLIC_VM_USED
                        },
                        {
                            value: data.cpus[1] - data.cpus[2],
                            name: '',
                            // 彻底锁定底色样式
                            itemStyle: {
                                color: '#E8EEF3', // 明确指定颜色
                                opacity: 1,
                                borderWidth: 0
                            },
                            emphasis: {
                                itemStyle: {
                                    color: '#E8EEF3', // 悬停时保持相同颜色
                                    opacity: 1,
                                    borderWidth: 0
                                }
                            },
                            // 禁用此项的所有交互
                            silent: true,
                            tooltip: {
                                show: false // 完全禁用tooltip
                            }
                        },
                    ]
                }
            ]
        };
        networkEcharts2.setOption(option2);

        networkEcharts3 = echarts.init(document.getElementById('network_echars3'));
        let option3 =  {
            title: [{
                text: `{name|${data.mems[1]}}{name2|GB}\n{val|${LANG.UI_VM_RESOURCE_OUT}}`,
                top: '60px',
                left: 'center',
                textStyle: {
                    rich: {
                        name: {
                            fontSize: 20,
                            color: '#2D3748',
                            padding:[0, 5],
                            fontWeight: 400
                        },
                        name2: {
                            fontSize: 12,
                            color: '#979797'
                        },
                        val: {
                            fontSize: 12,
                            padding:[4, 0],
                            color: '#979797'
                        },
                    }
                }
            },{
                text: `{name|${LANG.UI_VM_RESOURCE_MEMS_NAME} (${LANG.UI_VM_RESOURCE_TOTAL}：${data.mems[0]}GB)}`,
                top: '-10px',
                left: 'center',
                textStyle: {
                    rich: {
                        name: {
                            fontSize: 14,
                            color: '#76767B',
                            fontWeight: 400,
                            padding: [7, 0]
                        }
                    }
                }
            }],
            tooltip: {
                trigger: 'item',
                // 确保tooltip不影响底色
                formatter: function(params) {
                    if(params.name === '') return null; // 不显示空白名称的tooltip
                    return params.name + ': ' + params.value;
                }
            },
            legend: {
                bottom: '10px',
                left: 'center',
                icon: 'square'
            },
            series: [
                {
                    name: '',
                    type: 'pie',
                    radius: ['85%', '95%'],
                    center: ['50%', '70%'],
                    color: ["#33AFE4","#E8EEF3"],
                    startAngle: 180,
                    endAngle: 360,
                    label: {
                        show: false, // 隐藏扇区上的标签
                    },
                    labelLine: {
                        show: false // 隐藏扇区上的引导线
                    },
                    data: [
                        { value: data.mems[2], name: LANG.UI_PUBLIC_VM_USED },
                        {
                            value: data.mems[1] - data.mems[2],
                            name: '',
                            // 彻底锁定底色样式
                            itemStyle: {
                                color: '#E8EEF3', // 明确指定颜色
                                opacity: 1,
                                borderWidth: 0
                            },
                            emphasis: {
                                itemStyle: {
                                    color: '#E8EEF3', // 悬停时保持相同颜色
                                    opacity: 1,
                                    borderWidth: 0
                                }
                            },
                            // 禁用此项的所有交互
                            silent: true,
                            tooltip: {
                                show: false // 完全禁用tooltip
                            }
                        },
                    ]
                }
            ]
        };
        networkEcharts3.setOption(option3);
        $(window).resize(function() { // 监控屏幕大小变化，重新加载echart图
            networkEcharts1.resize();
            networkEcharts2.resize();
            networkEcharts3.resize();
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            initDataTableMachine(); // 虚拟机列表
            initDataTableNode(); // 节点资源隔离列表
            initEchars(); // 渲染echars
            initDataTable(); // 网络管理
            initNodeSelectNetwork(); //初始化所有节点
            initTableHeight();
            initResources(); // 资源隔离
        }
    };

}();

jQuery(document).ready(function () {
    vmNetwork.init();
});
