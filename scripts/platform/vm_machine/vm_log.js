var vmLog = function () {
    let queryParams = {};
    let changeHeightFlag = false;
    var initListener = function () {
        var des = '';

       des += '<div class="search input-group mr6">\n' +
           '                        <input class="vmlog-search customSearch" autocomplete="off" type="text" placeholder="'+LANG.UI_VM_SOURCE_OPERATION_ITEMS_SEARCH+'">\n' +
           '                        <div class="position0" style="width:auto;height:34px">\n' +
           '                            <button class="b-btn clear hide position0"><i class="icon-close-small"></i></button>\n' +
           '                        </div>\n' +
           '                        <div class="search-btn-log positionL0" style="width:auto;height:34px;">\n' +
           '                            <button class="b-btn search-btn search-btn-log"><i class="icon-search"></i></button>\n' +
           '                        </div>\n' +
           '                    </div>';
        $('.leftTool_log').html(des);
        //if($.inArray('p_global_speed_strategy_edit', CONF.PERMISSION_ARR) !== -1) {
        //绑定事件
        toBindEvent();
        // 高级搜索初始化
        initAdvance();
    }

    // 高级搜索事件
    var initAdvance = function () {
        // 改变表格高度
        $('#vin_current_log_toolbar .change_height').on('click', change_height);

        //高级搜索发送参数并添加展示
        $("#current_search_log_submit").on("click", adv_search);

        //高级搜索清除
        $("#vin_current_log_toolbar .advanced_list").on("click", ".adv_clear", adv_clearSearch_log);
        //弹出高级搜索模态框
        $('#vin_current_log_toolbar #advanced-search-btn').on('click', function () {
            $('#vm_search_log_modal').modal({
                'width': '800px',
                'height': '398px'
            });
        });

        // 鼠标滑过按钮显示提示
        $('#vin_current_log_toolbar #advanced-search-btn').hover(
            function () {
                if ($("#vin_current_log_toolbar .advanced_list #list_content").find("li").length > 0) {
                    $("#vin_current_log_toolbar .advanced_list").show();
                }
            },
            function () {}
        )

        $('.vmlog-search').keypress(function (e) {
            if (e.which == 13) {
                getParams();
                $('#vm_log_table').bootstrapTable('refresh', {
                    query: queryParams
                });
            }
        });

        $('#vin_current_log_toolbar .leftTool .search-btn').on('click', function(){
            getParams();
            $('#vm_log_table').bootstrapTable('refresh', {
                query: queryParams
            });
        })

        // 鼠标移开关闭提示
        $("#vin_current_log_toolbar .advanced_list").on("mouseleave", function (e) {
            $("#vin_current_log_toolbar .advanced_list").hide();
        })

        //清除所有
        $("#vin_current_log_toolbar #clear_adv").on("click", function () {
            $("#vin_current_log_toolbar .adv_list").remove();
            $('#vin_current_log_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP);
            $("#daterangepickerLog").val("");
            $("#taskNameLog").val("");
            $("#item_name").val("");
            $("#nodeSelectLog").val(0);
            $("#logstatus").val(0);
            $('#vm_search_log_modal #nodeSelectLog').val(0);

            $("#vin_current_log_toolbar .advanced_list").hide();
            getParams();
            $("#vm_log_table").bootstrapTable("refresh", {
                query: queryParams
            });
        })
    }

    //高级搜索展示
    var adv_search = function () {
        getParams();
        $("#vm_log_table").bootstrapTable("refresh", {
            query: queryParams
        });
        $("#vm_search_log_modal").modal('hide');
        $("#vin_current_log_toolbar .adv_list").remove();

        if ($("#taskNameLog").val() != "") {
            let value = $("#taskNameLog").val();
            $("#vin_current_log_toolbar #list_content").append('<li class="adv_list" titles="taskNameLog" title="'+LANG.UI_JOB_SRC_TASK_NAME + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#item_name").val() != "") {
            let value = $("#item_name").val();
            $("#vin_current_log_toolbar #list_content").append('<li class="adv_list" titles="item_name" title="'+LANG.UI_CLOUD_PLATFORM_USERNAME + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#daterangepickerLog").val() != "") {
            let value = $("#daterangepickerLog").val();
            $("#vin_current_log_toolbar #list_content").append('<li class="adv_list" titles="daterangepickerLog" title="'+LANG.UI_VM_MACHINE_TIMERANGE + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#nodeSelectLog").val() != "0" && $("#nodeSelectLog").val() != null) {
            let value = $("#nodeSelectLog").find('option:selected').text();
            $("#vin_current_log_toolbar #list_content").append('<li class="adv_list" titles="nodeSelectLog" title="'+LANG.UI_DB_NODE + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#logstatus").val() != "0" && $("#logstatus").val() != null) {
            let value = $("#logstatus").find('option:selected').text();
            $("#vin_current_log_toolbar #list_content").append('<li class="adv_list" titles="logstatus" title="'+LANG.UI_PUBLIC_STATUS + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#vin_current_log_toolbar #list_content").find(".adv_list").length > 0) {
            $('#vin_current_log_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP+"(" + $("#vin_current_log_toolbar #list_content").find(".adv_list").length + "/5)");
        }

    }

    var adv_clearSearch_log = function () {
        $(this).parent().remove();
        $("#" + $(this).parent().attr("titles") + "").val("");
        getParams();
        $("#vm_log_table").bootstrapTable("refresh", {
            query: queryParams
        });
        if ($("#vin_current_log_toolbar #list_content").find(".adv_list").length > 0) {
            $('#vin_current_log_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP+"(" + $("#vin_current_log_toolbar #list_content").find(".adv_list").length + "/5)");
        } else {
            $('#vin_current_log_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP);
            $("#vin_current_log_toolbar .advanced_list").hide();
        }
    }

    // 获取参数
    var getParams = function (params) {
        queryParams.search = $('.vmlog-search').val();
        let times = $('#vm_search_log_modal #daterangepickerLog').val();
        // 处理下开始结束时间
        if (times != '') {
            times = times.split(' - ');
            queryParams.start_time = times[0]
            queryParams.end_time = times[1]
        } else {
            queryParams.start_time = ''
            queryParams.end_time = ''
        }
        queryParams.node_uuid = $('#vm_search_log_modal #nodeSelectLog').val();
        queryParams.job_name = $('#vm_search_log_modal #taskNameLog').val();
        queryParams.item_name = $('#vm_search_log_modal #item_name').val();
        queryParams.status = $('#vm_search_log_modal #logstatus').val();
        return queryParams;
    }

    // 高度改变
    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#vm_log_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'padding-bottom': '10.25px'
            })
            $('#vin_current_log_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#vm_log_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#vin_current_log_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }
    // 初始化表格
    var initDataTable = function () {
        const options = {
            toolbarId: '#vin_current_log_toolbar',
            buttonsToolbar: '.vin_log_btnToolbar',
            vin_url: "/api/v1/virtual/logs",
            vin_method: "GET",
            changeHeightBtn: true, //改变高度按钮
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            resizable: true, //可变宽度
            sortName: 'start_time',
            sortOrder: 'desc',
            /*dateTimePicker: {
                id: 'daterangepicker_role'
            }, //时间选择器*/
            advanceSearch: {
                module: 'list'
            }, //高级搜索
            onResetView: initTableHeight,
            columns: [
                {
                    field: 'item_name',
                    title: LANG.UI_VM_SOURCE_OPERATION_ITEMS,
                    align: 'center',
                },
                {
                    field: 'source_name',
                    title: LANG.UI_VM_SOURCE_OPERATION_NAME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'source',
                    title: LANG.UI_VM_SOURCE_OPERATION,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'job_name',
                    title: LANG.UI_VM_MACHINE_JOB_NAME,
                    align: 'center',
                },
                {
                    field: 'start_time',
                    title: LANG.UI_PUBLIC_START_TIME,
                    align: 'center',
                },
                {
                    field: 'end_time',
                    title: LANG.UI_PUBLIC_END_TIME,
                    align: 'center',
                },
                {
                    field: 'result',
                    title: LANG.UI_VM_SOURCE_OPERATION_RESULT,
                    align: 'center',
                    formatter: statusFormatter,
                },
                {
                    field: 'description',
                    title: LANG.UI_PUBLIC_DESCRIPTION,
                    sortable: false,
                    align: 'center',
                },
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
                $('#vm_log_table th[data-field="result"]').css('width','5%');
                $('#vm_log_table th[data-field="source_name"]').css('width','8%');
                $('#vm_log_table th[data-field="source"]').css('width','8%');
                $('#vm_log_table th[data-field="start_time"]').css('width','10%');
                $('#vm_log_table th[data-field="end_time"]').css('width','10%');
                $('#vm_log_table th[data-field="description"]').css('width','10%');
                $('#vm_log_table th[data-field="job_name"]').css('width','20%');
            }
        };
        function checkFormatter(value, row, index) {
            return {
                disabled: false, // 设置是否可用
                checked: false // 设置选中
            };
        }
        // 状态
        function statusFormatter(value, row, index) {
            if (value == 0) {
                return '<span class="label label-sm label-success status-icon">' + LANG.UI_PUBLIC_SUCCESS + '</span>';
            }
            //
            return '<span class="label label-sm label-danger status-icon">' + LANG.UI_DATACENTER_FAILURE + '</span>';
        }

        if ($('#vm_log_table').children().length === 0) {
            $('#vm_log_table').baseTableConfig().init(options);

            initListener();
        }
    }
    var modifyDelStyle = function () {
        var selectedRow = $('#vm_log_table').bootstrapTable('getSelections');
        if (selectedRow.length < 1) {
            $('#delete-vm_log').addClass('exch-forbid-event');
            $('#vin_current_log_toolbar .del-parent-div').css({"cursor": "not-allowed"});
        } else {
            $('#delete-vm_log').removeClass('exch-forbid-event');
            $('#vin_current_log_toolbar .del-parent-div').css({"cursor": "pointer"});
        }
    }

    var toBindEvent = function () {
        //删除
        $('#delete-vm_log').on('click', function () {
            deleteVmlog();
        });
    }

    //删除
    var deleteVmlog = function () {
        var selectedRow = $('#vm_log_table').bootstrapTable('getSelections');
        if (selectedRow.length == 0) {
            UIToastr.showWarning(LANG.UI_VM_MACHINE_DEL_LOG, LANG.UI_VM_MACHINE_MODIFY_CHOOSE_ONE);
            return;
        }
        //删除策略二次确认框
        bootbox.confirm({
            title: LANG.UI_VM_MACHINE_DEL_TIPS,
            message: LANG.UI_VM_MACHINE_DEL_LOG_CONTENT,
            callback: debounce(function (r) {
                if (!r) return;
                Metronic.blockUI({target: '#vm_log_table',animate: true,cenrerY: true,});
                var uuids = [];
                for (var i in selectedRow){
                    uuids.push(selectedRow[i].uuid);
                }
                pAjaxRequest({id_list:uuids}, "/api/v1/virtual/logs", "DELETE", function (result) {
                    if (result.code == 0) {
                        $('#delete-vm_log').addClass('exch-forbid-event');
                        $('#vin_current_log_toolbar .del-parent-div').css({"cursor": "not-allowed"});
                        $('#vm_log_table').bootstrapTable('refresh');
                        UIToastr.showSuccess(LANG.UI_VM_MACHINE_DEL_LOG, result.message);
                    } else {
                        UIToastr.showError(LANG.UI_VM_MACHINE_DEL_LOG, result.message);
                    }
                    Metronic.unblockUI('#vm_log_table');
                });
            }, 300)
        });
    }

    //初始化日期选择插件
    var inintDatatimePicker = function(){
        //初始化日期时间选择控件
        $('#daterangepickerLog').daterangepicker({
            "autoUpdateInput": false,											//是否自动填充input
            "startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
            "endDate": moment({hour: 23, minute: 59}),												//默认结束时间
            "maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
            "timePicker": true,													//是否显示时间,时分
            "timePicker24Hour": true,											//是否是24小时制
            "alwaysShowCalendars": true,										//是否总是显示日期选择
            "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function(start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
        });

        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        $('#daterangepickerLog').on('apply.daterangepicker', function(ev, picker) {
            //给全局变量赋值,然后设置input
            _daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_range = picker.chosenLabel;
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
        });

        $('#daterangepickerLog').on('cancel.daterangepicker', function(ev, picker) {
            //清除全局变量,然后设置input
            _daterangepicker_starttime = "";
            _daterangepicker_endtime = "";
            _daterangepicker_range = "";
            $(this).val('');
        });

        //input右侧的图标事件
        $('.daterangepickerdiv i').click(function() {
            $(this).parent().find('input').click();
        });
    }

    //初始化所有备份节点
    var initNodeSelect = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeList',p:{}}, function(d){
            var data = JSON.parse(d);
            var nodeSelect = $('#vm_search_log_modal #nodeSelectLog');
            nodeSelect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
            nodeSelect.append(option);
            for(var i=0; i<data.length; i++){
                option = $("<option>").text(data[i].node_name).val(data[i].node_uuid);
                nodeSelect.append(option);
            }
            nodeSelect.val('0');
        });
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 295;

        $("#vm_log_list .fixed-table-body").css({
            "height": height
        });
    }
    return {
        //main function to initiate the module
        init: function () {
            initDataTable();
            inintDatatimePicker(); //初始化日期选择
            initNodeSelect(); //初始化所有节点
            initTableHeight();
        },
        reload: function (options){
            // 携带着虚拟机的uuid。然后查询这个虚拟机的信息
            var vmUuid = options.vmUuid
            $("#vin_current_log_toolbar #clear_adv").click();

            $("#vm_log_table").bootstrapTable("refresh", {
                query: {vm_uuid:vmUuid}
            });
        }
    };

}();

jQuery(document).ready(function () {
    vmLog.init();
});
