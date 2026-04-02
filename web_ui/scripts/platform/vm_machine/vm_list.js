var vmList = function () {
    let queryParams = {};
    let changeHeightFlag = false;
    let btnOpen;
    var checkIndex;
    var interval = null;
    var initListener = function () {
        var des = '';
        if($.inArray('p_vm_machine_delete', CONF.PERMISSION_ARR) !== -1){
            des += '<div class="btn-group del-parent-div" style="margin-right: 0px;">' +
                '<div id="delete-vm_list" class="exch-forbid-event" style="">' +
                '<i class="viconfont vicon-a-Deleteshanchu1"></i>' +
                '</div>' +
                '</div>';
        }

        des += $('#vin_current_toolbar .leftTool_vm').html();

        $('.leftTool_vm').html(des);
        //绑定事件
        toBindEvent();
        // 高级搜索初始化
        initAdvance();

        // 监听页面的改变，已确定刷新是否展示button
        $(document).on('click', function (e) {
            var is_open = false;
            $(e.target).find('#vm_list_table ul>.dropdown-menu').each(function (){
                var $this = $(this);
                var ariaExpanded = $this.attr('aria-expanded'); // 获取aria-expanded属性的值
                if (ariaExpanded !== undefined) { // 如果元素包含aria-expanded属性
                    if (ariaExpanded === 'true') {
                        is_open = true;
                    }
                }
            })

            if (is_open == false) {
                btnOpen = '';
            }
        });
        $('.vm_list_tableclear').on('click', function (){
            Metronic.blockUI({target: '#vmMachinediv',animate: true,cenrerY: true,});
            $('#vin_current_toolbar .customSearch').val('');
            getParams();
            $('#vm_list_table').bootstrapTable('refresh', {
                query: queryParams
            });
        })
    }

    // 高级搜索事件
    var initAdvance = function () {
        // 改变表格高度
        $('#vin_current_toolbar .change_height').on('click', change_height);

        //高级搜索发送参数并添加展示
        $("#current_search_submit").on("click", adv_search);

        //高级搜索清除
        $("#vin_current_toolbar .advanced_list").on("click", ".adv_clear", adv_clearSearch);
        //弹出高级搜索模态框
        $('#vin_current_toolbar #advanced-search-btn').on('click', function () {
            $('#vm_searchmodal').modal({
                'width': '800px',
                'height': '398px'
            });
        });

        // 鼠标滑过按钮显示提示
        $('#vin_current_toolbar #advanced-search-btn').hover(
            function () {
                if ($("#vin_current_toolbar .advanced_list #list_content").find("li").length > 0) {
                    $("#vin_current_toolbar .advanced_list").show();
                }
            },
            function () {}
        )

        $('.vm-search').keypress(function (e) {
            if (e.which == 13) {
                Metronic.blockUI({target: '#vmMachinediv',animate: true,cenrerY: true,});
                getParams();
                $('#vm_list_table').bootstrapTable('refresh', {
                    query: queryParams
                });
            }
        });

        $('#vin_current_toolbar .leftTool_vm .search-btn').on('click', function(){
            Metronic.blockUI({target: '#vmMachinediv',animate: true,cenrerY: true,});
            getParams();
            $('#vm_list_table').bootstrapTable('refresh', {
                query: queryParams
            });
        })

        // 鼠标移开关闭提示
        $("#vin_current_toolbar .advanced_list").on("mouseleave", function (e) {
            $("#vin_current_toolbar .advanced_list").hide();
        })

        //清除所有
        $("#vin_current_toolbar #clear_adv").on("click", function () {
            $("#vin_current_toolbar .adv_list").remove();
            $('#vin_current_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP);
            $("#daterangepickerTaskAlarm").val("");
            $("#taskName").val("");
            $("#nodeSelect").val(0);
            $("#vmstatus").val(0);
            $('#vm_searchmodal #nodeSelect').val(0);
            $("#vin_current_toolbar .advanced_list").hide();
            Metronic.blockUI({target: '#vmMachinediv',animate: true,cenrerY: true,});
            getParams();
            $("#vm_list_table").bootstrapTable("refresh", {
                query: queryParams
            });
        })
    }

    //高级搜索展示
    var adv_search = function () {
        Metronic.blockUI({target: '#vmMachinediv',animate: true,cenrerY: true,});
        getParams();
        $("#vm_list_table").bootstrapTable("refresh", {
            query: queryParams
        });
        $('#vm_searchmodal').modal('hide');
        $("#vin_current_toolbar .adv_list").remove();

        if ($("#taskName").val() != "") {
            let value = $("#taskName").val();
            $("#vin_current_toolbar #list_content").append('<li class="adv_list" titles="taskName"  title="' + LANG.UI_JOB_SRC_TASK_NAME + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#daterangepickerTaskAlarm").val() != "") {
            let value = $("#daterangepickerTaskAlarm").val();
            $("#vin_current_toolbar #list_content").append('<li class="adv_list" titles="daterangepickerTaskAlarm" title="' + LANG.UI_SEARCH_TIME_RANGE + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#nodeSelect").val() != "0" && $("#nodeSelect").val() != null) {
            let value = $("#nodeSelect").find('option:selected').text();
            $("#vin_current_toolbar #list_content").append('<li class="adv_list" titles="nodeSelect" title="' + LANG.UI_PUBLIC_BACKUP_NODE + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#vmstatus").val() != "0" && $("#vmstatus").val() != null) {
            let value = $("#vmstatus").find('option:selected').text();
            $("#vin_current_toolbar #list_content").append('<li class="adv_list" titles="vmstatus" title="' + LANG.UI_BLACK_WHITE_STATUS + value + '"><span>' +value + '</span><button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#vin_current_toolbar #list_content").find(".adv_list").length > 0) {
            $('#vin_current_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP + "(" + $("#vin_current_toolbar #list_content").find(".adv_list").length + "/3)");
        }
    }

    var adv_clearSearch = function () {
        $(this).parent().remove();
        $("#" + $(this).parent().attr("titles") + "").val("");
        Metronic.blockUI({target: '#vmMachinediv',animate: true,cenrerY: true,});
        getParams();
        $("#vm_list_table").bootstrapTable("refresh", {
            query: queryParams
        });
        if ($("#vin_current_toolbar #list_content").find(".adv_list").length > 0) {
            $('#vin_current_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP +"(" + $("#vin_current_toolbar #list_content").find(".adv_list").length + "/3)");
        } else {
            $('#vin_current_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP);
            $("#vin_current_toolbar .advanced_list").hide();
        }
    }

    // 获取参数
    var getParams = function (params) {
        queryParams.search = $('.vm-search').val();
        let times = $('#vm_searchmodal #daterangepickerTaskAlarm').val();
        // 处理下开始结束时间
        if (times != undefined && times != '') {
            times = times.split(' - ');
            queryParams.start_time = times[0]
            queryParams.end_time = times[1]
        }
        queryParams.node_uuid = $('#vm_searchmodal #nodeSelect').val();
        queryParams.job_name = $('#vm_searchmodal #taskName').val();
        queryParams.status = $('#vm_searchmodal #vmstatus').val();
        return queryParams;
    }

    // 高度改变
    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#vm_list_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'padding-bottom': '10.25px'
            })
            $('#vin_current_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#vm_list_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#vin_current_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }
    // 初始化表格
    var initDataTable = function () {
        var operates = {
            'click .btn': function (event, value, row, index) {
                btnRecord(event.target);
            },

            'click .edit': function (event, value, row, index) {
                operateRecord(row, 'edit');
            },

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
            'click .log': function (event, value, row, index) {
                operateRecord(row, 'log');
            },
        }
        var expandIndex = null;
        const options = {
            toolbarId: '#vin_current_toolbar',
            buttonsToolbar: '#vin_current_toolbar .vin_btnToolbar',
            vin_url: "/api/v1/virtual",
            vin_method: "GET",
            vin_params: function () {
                // 所有自定义携带参数，必须return
                var params = {};
                params['search'] = $('.role-search').val();
                return params;
            },
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
            detailFormatter: current_detail, //详情展开
            changeHeightBtn: true, //改变高度按钮
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            resizable: true, //可变宽度
            singleSelect:true,
            searchInput: true, //搜索框
            searchClass: 'vm-search',
            searchSelector: '.vm-search', //使用哪个搜索框
            placeholder: LANG.UI_VM_MACHINE_SEARCH,
            searchOnEnterKey:true, //回车搜索
            // showRefresh:true,// 显示刷新按钮
            advanceSearch: {
                module: 'list'
            }, //高级搜索
            onResetView: initTableHeight,
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                    formatter: checkFormatter
                },
                {
                    field: 'vm_name',
                    title: LANG.UI_VM_MACHINE_NAME,
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
                    field: 'os_type',
                    title: LANG.UI_VM_MACHINE_OS_TYPE,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'job_name',
                    title: LANG.UI_VM_MACHINE_JOB_NAME,
                    align: 'center',
                },
                {
                    field: 'job_type',
                    title: LANG.UI_VM_JOB_TYPE,
                    sortable: false,
                    align: 'center'
                },
                {
                    field: 'node',
                    title: LANG.UI_DB_NODE,
                    align: 'center',
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    align: 'center',
                    formatter: statusFormatter,
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
            onLoadSuccess: function (){
                Metronic.unblockUI('#vmMachinediv');
            },
            onPostBody: function () {
                // var tableData = $('#vm_list_table').bootstrapTable('getData');
                // if (tableData.length == 0)return;
                checkRecord();
                initTimer();
                $('#' + btnOpen + '').parent('.btn-group').addClass('open');
                if (null !== expandIndex) {
                    $("#vm_list_table").bootstrapTable('expandRow', expandIndex);
                }
            },
            onExpandRow: (index) => {
                if (null === expandIndex) {
                    expandIndex = index;
                } else if (index !== expandIndex) {
                    $("#vm_list_table").bootstrapTable('collapseRow', expandIndex);
                    expandIndex = index;
                }
            },
            onCollapseRow: () => {
                expandIndex = null;
            },
            onRefresh: function (params) {
                $("#vm_list_table").bootstrapTable('hideLoading');
            }
        };
        function checkFormatter(value, row, index) {
            if (row.status == 1) {
                // 开机不能删除
                return {
                    disabled: true,// 设置是否可用
                    checked: false // 设置选中
                };
            }
            return {
                disabled: false,// 设置是否可用
                checked: false // 设置选中
            };
        }
        function operateFormatter(value, row, index) {
            var button = '<div class="btn-group">';
            button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                '</button>' +
                '<ul class="dropdown-menu min-width100" role="menu" id='+ row.vm_uuid +'>';
            var style = '';
            /*if (row.close_flag) {
                style = "style='opacity:0.4;cursor:not-allowed'";
            }*/

            if($.inArray('p_vm_machine_modify', CONF.PERMISSION_ARR) !== -1) {
                button += '<li class="edit"><a href="javascript:;"><i class="viconfont mr5 vicon-xiugai"></i>'+LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY+'</a></li>';
            }

            if (row.status == 1) {
                // 已开机
                var styles = '';
                if (row.prefix_status != true) {
                    styles = 'style="pointer-events: none;opacity: 0.6; cursor: not-allowed;"';
                }
                button += '<li class="look" '+styles+'><a href="javascript:;"><i class="viconfont mr5 vicon-a-Workbenchgongzuotai"></i>'+LANG.UI_VM_RESOURCE_CONSOLE+'</a></li>';

                if($.inArray('p_vm_machine_off', CONF.PERMISSION_ARR) !== -1) {
                    button += '<li class="stop"><a href="javascript:;" '+style+'><i class="viconfont mr5 vicon-guanji"></i>'+LANG.UI_CLOUD_PLATFORM_POWER_OFF+'</a></li>';
                    button += '<li class="force"><a href="javascript:;" '+style+'><i class="viconfont mr5 vicon-ge_shutdown"></i>'+LANG.UI_VM_MACHINE_POWER_OFF_FORCE+'</a></li>';
                }

                if($.inArray('p_vm_machine_restart', CONF.PERMISSION_ARR) !== -1) {
                    button += '<li class="restart"><a href="javascript:;" '+style+'><i class="viconfont mr5 vicon-zhongqi"></i>'+LANG.UI_VM_MACHINE_POWER_RESTART+'</a></li>';
                }
            } else {
                // 未开机
                if($.inArray('p_vm_machine_on', CONF.PERMISSION_ARR) !== -1) {
                    button += '<li class="open"><a href="javascript:;" '+style+'><i class="viconfont mr5 vicon-kaiji"></i>'+LANG.UI_CLOUD_PLATFORM_POWER_ON+'</a></li>';
                }
            }
            button += '<li class="log"><a href="javascript:;"><i class="viconfont mr5 vicon-ge_log"></i>'+LANG.UI_VM_MACHINE_LOG+'</a></li>';
            button += '</ul></div>';
            return button;
        }
        // 状态
        function statusFormatter(value, row, index) {
            if (value == 1) {
                return '<span class="label label-sm label-success status-icon">' + row.status_value + '</span>';
            }
            // 停止
            return '<span class="label label-sm label-default status-icon">' + row.status_value + '</span>';
        }

        if ($('#vm_list_table').children().length === 0) {
            Metronic.blockUI({target: '#vmMachinediv',animate: true,cenrerY: true});
            $('#vm_list_table').baseTableConfig().init(options);

            initListener();
        }
    }

    //记录勾选
    var checkRecord = function () {
        var checkArr = [];
        $.each(checkIndex, function (index) {
            checkArr.push(checkIndex[index].vm_uuid);
        });
        $('#vm_list_table').bootstrapTable('checkBy', {
            field: 'vm_uuid',
            values: checkArr
        })
    }
    var initTimer = function () {
        if (interval != null) { //判断计时器是否为空
            clearTimeout(interval);
            // interval = null;
        }
        interval = setTimeout(update, 5000);
    }
    //更新表格数据
    var update = function () {
        getParams(true);
        queryParams.refresh = 1; // 表示刷新
        $('#vm_list_table').bootstrapTable('refresh', {
            query: queryParams
        });
    }
    var modifyDelStyle = function () {
        var selectedRow = $('#vm_list_table').bootstrapTable('getSelections');
        checkIndex = selectedRow;
        if (selectedRow.length < 1) {
            $('#delete-vm_list').addClass('exch-forbid-event');
            $('#vin_current_toolbar .del-parent-div').css({"cursor": "not-allowed"});
        } else {
            $('#delete-vm_list').removeClass('exch-forbid-event');
            $('#vin_current_toolbar .del-parent-div').css({"cursor": "pointer"});
        }
    }
    // 管理详情显示
    var lastIndex = [-1, -1];
    var current_detail = function (index, row, element) {
        // 控制只显示一个
        if (index != lastIndex[1]) {
            lastIndex.push(index);
            $('#vm_list_table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
            lastIndex.splice(0, 1);
        }
        // 具体的内容
        var content = '<table><tbody>';
        /* content += '<tr><td style="width:200px;">'+LANG.UI_VM_MACHINE_DATA+':</td><td>' + row.back_source + '</td></tr>';*/
        var job_name = row.job_name;
        if (row.job_uuid != 0) {
            var url = '';
            if (CONF.TASK_TYPE.SURE_BACKUP == row.job_type_value) {
                // 数据验证
                url = "./content/platform/dataverification/verification_job_details.php";
            } else if (CONF.TASK_TYPE.PLATFORM_RECOVERY == row.job_type_value) {
                // 跨平台恢复
                url = './content/platform/recovery/platform_job.php';
            } else if (CONF.TASK_TYPE.GRAIN_RECOVERY == row.job_type_value) {
                // 细粒度恢复
                url = './content/platform/recovery/graininess_job.php';
            } else if (CONF.TASK_TYPE.INSTANT_RECOVERY == row.job_type_value) {
                // 瞬时恢复
                url = './content/platform/recovery/instantaneous_job.php';
            } else if (CONF.MODULE_TYPE.VOL_CDP == row.module_type_value) {
                // dbcdp
                url = './content/volcdp/cdp_job_details.php';
                if (row.dev_type == 2) {
                    // 磁盘
                    url = './content/complete_machine_volcdp/cm_cdp_job_details.php';
                }
            }
            var name = 'task';
            // gmp的额外判读
            if (CONF.VENDOR == CONF.VENDOR_LIST.gmp && CONF.TASK_TYPE.SURE_BACKUP == row.job_type_value) {
                name = 'verification_job';
            }
            job_name = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid + '" name="'+name+'" class="ajaxify" title="'+row.job_name +'">'+row.job_name +'</a>';
        }
        content += '<tr><td style="width:200px;">'+LANG.UI_REPORT_TASKNAME+':</td><td>' + job_name + '</td></tr>';

        content += '<tr><td style="width:200px;">'+LANG.UI_VM_JOB_TYPE+':</td><td>' + row.job_type + '</td></tr>';
        content += '<tr><td style="width:200px;">'+LANG.UI_VM_MACHINE_DISKS+':</td><td>' + row.desk_list.join('</br>') + '</td></tr>';
        content += '<tr><td style="width:200px;">'+LANG.UI_VM_MACHINE_NETWORK_SET+':</td><td>' + row.net_list.join('</br>') + '</td></tr>';
        content += '<tr><td style="width:200px;">'+LANG.UI_VM_MACHINE_OS_TYPE+':</td><td>' + row.os_type + '</td></tr>';
        content += '<tr><td style="width:200px;">'+LANG.UI_PUBLIC_STORAGE_IN_NODE+':</td><td>' + row.node + '</td></tr>';
        content += '</tbody></table>';
        $(element).append(content);
    }

    // 记录刷新按钮展开
    var btnRecord = function (target) {
        btnOpen = $(target).next('.dropdown-menu').prop('id');
    }
    // 操作按钮事件
    var operateRecord = function (row, type) {
        var vmUuid = row.vm_uuid;
        if (type == 'look') {
            // 查看
            // 更改为需要先请求后台，然后才能跳转
            opereate(type, vmUuid, row.vnc_url);
            return;
        }
        if (type == 'log') {
            // 日志
            $('a[href="#vmMachineLogdiv"]').click();
            vmLog.reload({vmUuid:vmUuid});
            return;
        }

        if (type == 'edit') {
            editVmlist(vmUuid);
            return;
        }

        if (row.close_flag) {
            // 表示不允许操作
            // return;
        }

        // 判断下是否需要提示
        var cdpTaskStage = [
            CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER,
            CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING,
            CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC,
            CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC,
            CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING
        ];

        if (
            row.job_status > 0 &&
            ($.inArray(row.job_status, [CONF.TASK_STATUS.FINISHED, CONF.TASK_STATUS.STOPPED]) === -1)
        ) {
            // 任务不是停止和已完成需要提示
            var msg = LANG.UI_VM_OPERATE_CONFIM.replace(/s%/g, row.job_name);
            bootbox.confirm({
                title: LANG.UI_VM_MACHINE_OPERATION,
                message: msg,
                callback: function (r) {
                    if (!r) return;
                    opereate(type, vmUuid);
                }
            });
        } else {
            opereate(type, vmUuid);
        }
    }

    function opereate(type, vmUuid, url = '') {
        Metronic.blockUI({target: '#vm_list_table',animate: true,cenrerY: true});
        pAjaxRequest({type:type}, "/api/v1/virtual/operate/"+vmUuid, "POST", function (result) {
            Metronic.unblockUI('#vm_list_table');
            if (result.code == 0) {
                if (type == 'look') {
                    window.open(url, '_blank');
                    return;
                }
                $('#vm_list_table').bootstrapTable('refresh');
                UIToastr.showSuccess(LANG.UI_VM_MACHINE_OPERATION, result.message);
            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_OPERATION, result.message);
            }
        });
    }

    var toBindEvent = function () {
        //删除
        $('#delete-vm_list').on('click', function () {
            deleteVmlist();
        });

    }

    //修改
    var editVmlist = function (vmUuid) {
        Metronic.blockUI({target: '#vm_list_table',animate: true,cenrerY: true,});
        var uuid = vmUuid;
        pAjaxRequest({uuid:uuid}, "/api/v1/virtual/" + uuid, "GET", function (result) {
            Metronic.unblockUI('#vm_list_table');
            if (result.code == 0) {
                let data = result.data;
                $('#strategy_uuid').val(uuid);
                // 打开模态
                $('#vm_machine_modal').modal({'width': '800px', 'height': '470px'});
                vm_Mchine.init({'data': data});
            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_MODIFY, result.message);
                return;
            }
        });
    }

    //删除
    var deleteVmlist = function () {
        var selectedRow = $('#vm_list_table').bootstrapTable('getSelections');
        if (selectedRow.length == 0) {
            UIToastr.showWarning(LANG.UI_VM_MACHINE_DEL, LANG.UI_VM_MACHINE_MODIFY_CHOOSE_ONE);
            return;
        }
        //删除策略二次确认框
        bootbox.confirm({
            title: LANG.UI_VM_MACHINE_DEL_TIPS,
            message: LANG.UI_VM_MACHINE_DEL_CONTENT,
            callback: function (r) {
                if (!r) return;
                Metronic.blockUI({target: '#vm_list_table',animate: true,cenrerY: true,});
                var uuids = [];
                for (var i in selectedRow){
                    uuids.push(selectedRow[i].vm_uuid);
                }
                pAjaxRequest({id_list:uuids}, "/api/v1/virtual", "DELETE", function (result) {
                    if (result.code == 0) {
                        $('#delete-vm_list').addClass('exch-forbid-event');
                        $('#vin_current_toolbar .del-parent-div').css({"cursor": "not-allowed"});
                        $('#vm_list_table').bootstrapTable('refresh');
                        UIToastr.showSuccess(LANG.UI_VM_MACHINE_DEL, result.message);
                    } else {
                        UIToastr.showError(LANG.UI_VM_MACHINE_DEL, result.message);
                    }
                    Metronic.unblockUI('#vm_list_table');
                });
            }
        });
    }

    //初始化所有备份节点
    var initNodeSelect = function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeList',p:{}}, function(d){
            var data = JSON.parse(d);
            var nodeSelect = $('#vm_searchmodal #nodeSelect');
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

        height = panelH - 241;

        $("#vmMachinediv .fixed-table-body").css({
            "height": height
        });
    }

    function submitInfo(data) {
        Metronic.blockUI({target: '#vm_machine_modal',animate: true,cenrerY: true,});
        pAjaxRequest(data, "/api/v1/virtual/" + data.temp_agent.uuid, "POST", function (result) {
            Metronic.unblockUI('#vm_machine_modal');
            if (result.code == 0) {
                UIToastr.showSuccess(LANG.UI_VM_MACHINE_MODIFY, result.message);
                $('#vm_machine_modal').modal('hide');
            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_MODIFY, result.message);
            }
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            initDataTable();
            // inintDatatimePicker(); //初始化日期选择
            initNodeSelect(); //初始化所有节点
            initTableHeight();



            // 提交修改虚拟机信息事件
            $('#current-vm-machine-submit').on('click', function (){
                var info = vm_Mchine.getMachineInfo();
                if (info === false) {
                    return;
                }
                // console.log('info', info);
                // 进行接口请求
                submitInfo(info);
            });
        }
    };

}();

jQuery(document).ready(function () {
    vmList.init();
});
