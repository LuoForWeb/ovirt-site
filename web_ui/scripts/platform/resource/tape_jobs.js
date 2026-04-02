var TapeJobs = function () {
    var filterFlag = true;
    var totalCount = 0;
    var selectCount = 0;
    var interval = null;
    var queryParams = {};
    var changeHeightFlag = false;
    var displayFlag = false;
    var accurateFlag = false;


    let addListeners = function () {

        $('#vin_tape_job_toolbar .filters').hover(
            function () {
                $('#vin_tape_job_toolbar .filters').addClass('filter-hover');
            },
            function () {
                $('#vin_tape_job_toolbar .filters').removeClass('filter-hover');
            }
        )

        $('#vin_tape_job_toolbar').on('click', '.search-btn', function (){
            $("#tape_job_table").bootstrapTable("refresh");
        })

        //高级搜索发送参数并添加展示
        $("#vin_tape_job_toolbar #current_search_submit").on("click", adv_search);

        //高级搜索清除
        $("#vin_tape_job_toolbar .advanced_list").on("click", ".adv_clear", adv_clearSearch);

        // 高级搜索展示框hover
        $('#vin_tape_job_toolbar #advanced-search-btn').hover(
            function () {
                if ($("#vin_tape_job_toolbar .advanced_list #list_content").find("li").length > 0) {
                    $(".advanced_list").show();
                }
            },
            function () {}
        )

        //每次点击checkbox时停止刷新
        $('#vin_tape_job_toolbar #filterDiv .filter-content input[type=checkbox]').on('click', function () {
            filterFlag = false;
        });

        $(".advanced_list").on("mouseleave", function (e) {
            $(".advanced_list").hide();
        })

        //高级搜索清除所有
        $("#clear_adv").on("click", function () {
            $("#vin_tape_job_toolbar .adv_list").remove();
            $('#vin_tape_job_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP);
            $("#current_taskName").val("");
            $("#current_userName").val("");
            $("#current_hostName").val("");
            $("#current_vmName").val("");
            $("#current_tasktype").val("0");
            $("#vmTasktype").val("0");
            $("#fsTasktype").val("0");
            $("#dbTasktype").val("0");
            $("#copyTasktype").val("0");
            $("#dbCDPTaskType").val("0");
            $("#fileCDPTaskType").val("0");
            $("#osTaskType").val("0");
            $("#volCdpTaskType").val("0");
            $("#awsTasktype").val("0");
            $("#nasTaskType").val("0");
            $("#current_moduletype").val("0");
            $("#current_node").val("0");
            $("#currentdbtype").val("0");
            $("#current_node").val("0");
            $("#current_storage").val("0");
            $("#vm_hypervisor").val("0");

            $(".advanced_list").hide();
            getParams();
            $("#tape_job_table").bootstrapTable("refresh");
            advFlag = false;
        })

        //高级搜索模块类型选择
        $('#currentJobModal #current_moduletype').on('change', moduleHandler);

        //弹出高级搜索模态框
        $('#vin_tape_job_toolbar #advanced-search-btn').on('click', function () {
            $('#currentJobModal').modal({
                'width': '693px',
                'height': 'auto'
            });
            hideSelections(); //隐藏不支持磁带的相关搜索项
        });

        //过滤提交
        $('#vin_tape_job_toolbar #filterSubmit').on('click', function (options) {
            accurateFlag = true;
            $('#vin_tape_job_toolbar #filters').removeClass('show');
            $('#vin_tape_job_toolbar .filters').removeClass('filter-active');
            $('#tape_job_table').bootstrapTable('showLoading');
            //过滤参数组合
            var filters = {};
            var job_status = [];
            var module_type = [];
            var job_type = [];
            var sub_module_type = [];
            $('#vin_tape_job_toolbar .filter-content #job_status input[type=checkbox]:checked').each(function (k, v) {
                job_status.push($(this).attr('value'));
            })
            filters['job_status'] = job_status;
            $('#vin_tape_job_toolbar .filter-content #module_type input[type=checkbox]:checked').each(function (k, v) {
                if ($(this).attr('value') == 17) {
                    module_type.push(CONF.MODULE_TYPE.VM);
                    queryParams.sub_module_type = 3; //公有云判断
                    sub_module_type.push(3);
                } else if ($(this).attr('value') == CONF.MODULE_TYPE.VM) {
                    module_type.push(CONF.MODULE_TYPE.VM);
                    queryParams.sub_module_type = 1; //虚拟机判断
                    sub_module_type.push(1);
                } else {
                    module_type.push($(this).attr('value'));
                }
            })
            var vmContains = $.inArray(1, sub_module_type) !== -1;
            var awsContains = $.inArray(3, sub_module_type) !== -1;
            var privateContains = $.inArray(2, sub_module_type) !== -1;

            if (vmContains && awsContains && privateContains) {
                // 如果数组同时包含 vmContains 和 awsContains 即同时搜索虚拟机和公有云，则不传sub_module_type
                queryParams.sub_module_type = '';
            }

            filters['module_type'] = module_type;
            $('#vin_tape_job_toolbar .filter-content #job_type input[type=checkbox]:checked').each(function (k, v) {
                job_type.push($(this).attr('value'));
            })
            filters['job_type'] = job_type;

            sessionStorage.setItem('tape_job_table_filters', JSON.stringify(filters));

            if (filters.module_type != '') {
                filters.job_type = switchParams(filters.module_type, filters.job_type);
            } else {
                filters.job_type = switchJobType(filters.job_type);
            }

            displayFlag = false;
            filterFlag = true;
            $('#tape_job_table').bootstrapTable('refresh', {
                query: queryParams
            });
            accurateFlag = false;
        });

        // 过滤全选和反选
        $('#vin_tape_job_toolbar #selectAll').on('click', function (e) {
            filterFlag = false;
            $('#vin_tape_job_toolbar #filterDiv .filter-content input[type=checkbox]').each(function () {
                $('#vin_tape_job_toolbar #filterDiv .filter-content input[type=checkbox]').prop("checked", true)
                totalCount += 1;
            })
            $('#vin_tape_job_toolbar #filterBtn span').text(LANG.UI_JOB_FILTER+'(' + totalCount + '/' + totalCount + ')');
            $('#tapeJobModal #current_moduletype').parents('.list-option').hide();
            $('#tapeJobModal #vmtypeDiv').hide();
            $('#tapeJobModal #dbtypeDiv').hide();
            selectCount = 0;
            totalCount = 0;
        });

        $('#vin_tape_job_toolbar #selectNone').on('click', function () {
            filterFlag = false;
            $('#vin_tape_job_toolbar #filterDiv .filter-content input[type=checkbox]').each(function () {
                $('#vin_tape_job_toolbar #filterDiv .filter-content input[type=checkbox]').prop("checked", false)
            });
            $('#vin_tape_job_toolbar #filterBtn span').text(LANG.UI_JOB_FILTER+'('+ LANG.UI_JOB_FILTER_SELECTNONE +')');
            $('#vin_tape_job_toolbar #tapeJobModal #current_moduletype').parents('.list-option').show();
            selectCount = 0;
            totalCount = 0;
        });

        //点击过滤菜单外关闭过滤菜单
        $(document).on('click', function (e) {
            btnOpen = ''; //点击页面将展开按钮置为空
            if ($(e.target).closest('#filter_div').length > 0) {

            } else {
                // 关闭弹框
                $('#vin_tape_job_toolbar #filters').removeClass('show');
                $('#vin_tape_job_toolbar #filterBtn').removeClass('filter-hover');
                $('#vin_tape_job_toolbar #filterBtn').removeClass('filter-active');
                displayFlag = false;
            };

            if ($(e.target).closest('#addList').length > 0) {

            } else {
                $(".addTaskList").hide();
                $(".subDiv").hide();
            }
        });

        $('#vin_tape_job_toolbar .filters').on('click', function () {
            if (displayFlag == false) {
                $('#vin_tape_job_toolbar #filters').addClass('show');
                // $('#vin_tape_job_toolbar #filterBtn').css('background-color', 'rgba(15,191,152,0.1)');
                $('#vin_tape_job_toolbar #filterBtn').addClass('filter-active');

                displayFlag = true;
            } else if (displayFlag == true) {
                $('#vin_tape_job_toolbar #filters').removeClass('show');
                // $('#vin_tape_job_toolbar #filterBtn').css('background-color', '#fff');
                $('#vin_tape_job_toolbar #filterBtn').removeClass('filter-active');
                displayFlag = false;
            }
        });
    };

    //高级搜索发送参数并展示
    var adv_search = function () {
        getParams();
        $("#tape_job_table").bootstrapTable("refresh");
        accurateFlag = false;
        $('#currentJobModal').modal('hide');
        $("#vin_tape_job_toolbar .adv_list").remove();

        if ($("#current_taskName").val() != "") {
            $("#list_content").append('<li class="adv_list" value="current_taskName" title="' + $("#current_taskName").val() + '">'+ LANG.UI_SEARCH_TASK_NAME +'：' + $("#current_taskName").val() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#current_userName").val() != "") {
            $("#list_content").append('<li class="adv_list" value="current_userName" title="' + $("#current_userName").val() + '">'+ LANG.UI_MICROSOFT365_USER_NAME +'：' + $("#current_userName").val() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#current_hostName").val() != "") {
            $("#list_content").append('<li class="adv_list" value="current_hostName" title="' + $("#current_hostName").val() + '">'+ LANG.UI_BACKUP_FILE_HOSTNAME +'：' + $("#current_hostName").val() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#current_vmName").val() != "") {
            $("#list_content").append('<li class="adv_list" value="current_vmName" title="' + $("#current_vmName").val() + '">'+ LANG.UI_JOB_VM_NAME +'：' + $("#current_vmName").val() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        //任务类型处理
        if ($("#current_moduletype").val() != '' && queryParams.job_type != 0) {
            switch ($("#current_moduletype").val()) {
                case '0':
                    $("#list_content").append('<li class="adv_list" value="current_tasktype" title="' + $("#current_tasktype").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #current_tasktype").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                case '2':
                    $("#list_content").append('<li class="adv_list" value="vmTasktype" title="' + $("#vmTasktype").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #vmTasktype").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                case '3-1':
                case '3-3':
                case '3-4':
                    $("#list_content").append('<li class="adv_list" value="fsTasktype" title="' + $("#fsTasktype").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #fsTasktype").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                case '4':
                    $("#list_content").append('<li class="adv_list" value="dbTasktype" title="' + $("#dbTasktype").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #dbTasktype").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                    // case '9':
                    //     break;
                case '10000':
                    $("#list_content").append('<li class="adv_list" value="dbCDPTaskType" title="' + $("#dbCDPTaskType").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #dbCDPTaskType").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                case '10001':
                    $("#list_content").append('<li class="adv_list" value="fileCDPTaskType" title="' + $("#fileCDPTaskType").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #fileCDPTaskType").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                case '5':
                    $("#list_content").append('<li class="adv_list" value="osTaskType" title="' + $("#osTaskType").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #osTaskType").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                case '10':
                    $("#list_content").append('<li class="adv_list" value="volCdpTaskType" title="' + $("#volCdpTaskType").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #volCdpTaskType").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                case '11':
                    $("#list_content").append('<li class="adv_list" value="nasTaskType" title="' + $("#nasTaskType").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #nasTaskType").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                case '14':
                    $("#list_content").append('<li class="adv_list" value="fsTasktype" title="' + $("#fsTasktype").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #fsTasktype").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
                case '17':
                    $("#list_content").append('<li class="adv_list" value="awsTasktype" title="' + $("#awsTasktype").find('option:selected').text() + '">'+ LANG.UI_SEARCH_TASK_TYPE +'：' + $("#currentJobModal #awsTasktype").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
                    break;
            }
        }

        if ($("#current_moduletype").val() != "0" && $("#current_moduletype").val() != null) {
            $("#list_content").append('<li class="adv_list" value="current_moduletype" title="' + $("#currentJobModal #current_moduletype").find('option:selected').text() + '">'+ LANG.UI_SEARCH_MODE_TYPE +'：' + $("#current_moduletype").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#current_node").val() != "0" && $("#current_node").val() != null) {
            $("#list_content").append('<li class="adv_list" value="current_node" title="' + $("#currentJobModal #current_node").find("option:selected").text() + '">'+ LANG.UI_DB_NODE +'：' + $("#current_node").find("option:selected").text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#current_storage").val() != "0" && $("#current_storage").val() != null) {
            $("#list_content").append('<li class="adv_list" value="current_storage" title="' + $("#currentJobModal #current_storage").find("option:selected").text() + '">'+ LANG.UI_PUBLIC_STORAGE +'：' + $("#current_storage").find("option:selected").text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#vm_hypervisor").val() != "0" && $("#vm_hypervisor").val() != null) {
            $("#list_content").append('<li class="adv_list" value="vm_hypervisor" title="' + $("#currentJobModal #vm_hypervisor").find("option:selected").text() + '">'+ LANG.UI_VM_SETTING_V2_HYPER_TYPE +'：' + $("#vm_hypervisor").find("option:selected").text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#currentdbtype").val() != "0" && $("#currentdbtype").val() != null) {
            $("#list_content").append('<li class="adv_list" value="currentdbtype" title="' + $("#currentJobModal #currentdbtype").find('option:selected').text() + '">' + LANG.UI_COPY_SOURCE_DB_TYPE +'：' + $("#currentdbtype").find('option:selected').text() + '<button class="adv_clear b-btn"><i class="icon-close-small"></i></button></li>');
        }

        if ($("#list_content").find(".adv_list").length > 0) {
            $('#vin_tape_job_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP+"(" + $("#list_content").find(".adv_list").length + "/9)");
        } else {
            $('#vin_tape_job_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP);
            $("#vin_tape_job_toolbar .advanced_list").hide();
        }
    }

    //隐藏磁带不支持的搜索项
    var hideSelections = function () {
        $('#current_moduletype option[value="10"]').hide();
        $('#current_moduletype option[value="10000"]').hide();

        $('.task-type-div option[value="7"]').hide();
        $('.task-type-div option[value="8"]').hide();
        $('.task-type-div option[value="6"]').hide();
        $('.task-type-div option[value="37"]').hide();
        $('.task-type-div option[value="49"]').hide();
        $('.task-type-div option[value="50"]').hide();
    }

    /**
     * @function 获取自定义参数
     * @param bool auto 自动刷新时暂停获取参数，默认false 为获取
     */
    var getParams = function (auto = false) {
        queryParams.search = $('.currentSearch').val();
        if (!auto) {
            queryParams.job_name = $('#currentJobModal #current_taskName').val();
            queryParams.user_name = $('#currentJobModal #current_userName').val();
            queryParams.other_host_name = $('#currentJobModal #current_hostName').val();
            queryParams.other_vm_name = $('#currentJobModal #current_vmName').val();
            queryParams.vm_type = $('#currentJobModal #vm_hypervisor').val();
            queryParams.db_type = $('#currentJobModal #currentdbtype').val();
            queryParams.node_uuid = $('#currentJobModal #current_node').val();
            queryParams.storage_uuid = $('#currentJobModal #current_storage').val();
            var moduleTypeArr = $('#currentJobModal #current_moduletype').val().split('-');
            queryParams.module_type = moduleTypeArr[0];
            if (moduleTypeArr[1] != undefined) {
                queryParams.sub_module_type = moduleTypeArr[1];
            }

            //高级搜索任务类型处理
            switch (queryParams.module_type) {
                case '0':
                    queryParams.job_type = $('#currentJobModal #current_tasktype').val();
                    break;
                case '2':
                    queryParams.job_type = $('#currentJobModal #vmTasktype').val();
                    break;
                case '3-1':
                case '3-3':
                case '3-4':
                    queryParams.job_type = $('#currentJobModal #fsTasktype').val();
                    break;
                case '4':
                    queryParams.job_type = $('#currentJobModal #dbTasktype').val();
                    break;
                    // case '9':
                    //     break;
                case '10000':
                    queryParams.job_type = $('#currentJobModal #dbCDPTaskType').val();
                    break;
                case '10001':
                    queryParams.job_type = $('#currentJobModal #fileCDPTaskType').val();
                    break;
                case '5':
                    queryParams.job_type = $('#currentJobModal #osTaskType').val();
                    break;
                case '10':
                    queryParams.job_type = $('#currentJobModal #volCdpTaskType').val();
                    break;
                case '11':
                    queryParams.job_type = $('#currentJobModal #nasTaskType').val();
                    break;
                case '14':
                    queryParams.job_type = $('#currentJobModal #fsTasktype').val(); //m365和文件一样
                    break;
                case '17':
                    queryParams.job_type = $('#currentJobModal #awsTasktype').val();
                    break;
                default:
                    queryParams.job_type = undefined;
                    break;
            }
            if (queryParams.module_type == CONF.MODULE_TYPE.PUBLIC_CLOUD) {
                queryParams.module_type = CONF.MODULE_TYPE.VM;
                queryParams.sub_module_type = 3; //AWS的判断
            } else if (queryParams.module_type == CONF.MODULE_TYPE.VM) {
                queryParams.sub_module_type = 1; //虚拟机的判断
            }
        }
        return queryParams;
    }

    //高级搜索清除
    var adv_clearSearch = function () {
        $(this).parent().remove();
        if ($(this).parent().attr("value") == 'current_taskName' || $(this).parent().attr("value") == 'current_userName' ||
            $(this).parent().attr("value") == 'current_hostName' || $(this).parent().attr("value") == 'current_vmName') {
            $("#currentJobModal #" + $(this).parent().attr("value") + "").val("");
        } else {
            $("#currentJobModal #" + $(this).parent().attr("value") + "").val("0");
        }
        getParams();
        $("#tape_job_table").bootstrapTable("refresh");
        if ($("#vin_tape_job_toolbar #list_content").find(".adv_list").length > 0) {
            $('#vin_tape_job_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP+"(" + $("#vin_tape_job_toolbar #list_content").find(".adv_list").length + "/9)");
        } else {
            $('#vin_tape_job_toolbar #advanced-search-btn span').text(LANG.UI_JOB_SEARCH_EXP);
            $("#vin_tape_job_toolbar.advanced_list").hide();
        }
    }

    // 模块切换
    var moduleHandler = function () {
        $('#currentJobModal #current_tasktype').hide();
        $('#currentJobModal #vmTasktype').hide();
        $('#currentJobModal #fsTasktype').hide();
        $('#currentJobModal #dbTasktype').hide();
        $('#currentJobModal #copyTasktype').hide();
        $('#currentJobModal #vmtypeDiv').hide();
        $('#currentJobModal #dbtypeDiv').hide();
        $('#currentJobModal #osTaskType').hide();
        $('#currentJobModal #nasTaskType').hide();
        $('#currentJobModal #dbCDPTaskType').hide();
        $('#currentJobModal #volCdpTaskType').hide();
        $('#currentJobModal #awsTasktype').hide();
        $('#currentJobModal #exchangeTaskType').hide();

        if (this.value == "0") {
            $('#currentJobModal #current_tasktype').show();
        } else if (this.value == "2") {
            $('#currentJobModal #vmTasktype').show();
            $('#currentJobModal #vmtypeDiv').show();
        } else if (this.value == "3-1" || this.value == "3-3" || this.value == "3-4") {
            $('#currentJobModal #fsTasktype').show();
        } else if (this.value == "4") {
            $('#currentJobModal #dbTasktype').show();
            $('#currentJobModal #dbtypeDiv').show();
        } else if (this.value == "9") {
            $('#currentJobModal #copyTasktype').show();
        } else if (this.value == "10000") {
            $('#currentJobModal #dbCDPTaskType').show();
        } else if (this.value == "10001") {
            $('#currentJobModal #fileCDPTaskType').show();
        } else if (this.value == "5") {
            $('#currentJobModal #osTaskType').show();
        } else if (this.value == "10") {
            $('#currentJobModal #volCdpTaskType').show();
        } else if (this.value == "11") {
            $('#currentJobModal #nasTaskType').show();
        } else if (this.value == "14") {
            $('#currentJobModal #fsTasktype').show();
        } else if (this.value == "17") {
            $('#currentJobModal #awsTasktype').show();
        }
    }

    var switchParams = function (module, job_type) {
        var new_job_type = [];
        $.each(module, function (index, value) {
            switch (value) {
                case "2": //VM
                    $.each(job_type, function (k, v) {
                        if (v == 1) { //虚拟机备份
                            new_job_type.push(1);
                        } else if (v == 2) {
                            new_job_type.push(2);
                        } else {
                            new_job_type.push(v);
                        }
                    })
                    break;
                case "3": //文件
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(1);
                        } else if (v == 2) {
                            new_job_type.push(2);
                        } else if (v == 17) {
                            new_job_type.push(26);
                        } else if (v == 19) {
                            new_job_type.push(27);
                        }
                    })
                    break;
                case "4": //数据库
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(28);
                        } else if (v == 2) {
                            new_job_type.push(29);
                        } else if (v == 17) {
                            new_job_type.push(30);
                        } else if (v == 19) {
                            new_job_type.push(31);
                        }
                    })
                    break;
                case "5": //OS
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(35);
                        } else if (v == 2) {
                            new_job_type.push(36);
                        } else if (v == 17) {
                            new_job_type.push(38);
                        } else if (v == 19) {
                            new_job_type.push(39);
                        } else if (v == CONF.TASK_TYPE.VM_INSTANT_RECOVERY_MOTION) { //迁移默认是8虚拟机迁移
                            new_job_type.push(CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION);
                        } else if (v == CONF.TASK_TYPE.VM_INSTANT_RECOVERY) { //瞬时恢复默认是7虚拟机瞬时恢复
                            new_job_type.push(CONF.TASK_TYPE.OS_INSTANT_RECOVERY);
                        }
                    })
                    break;
                case "10": //卷CDP
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(32);
                        } else if (v == 2) {
                            new_job_type.push(33);
                        } else if (v == 34) {
                            new_job_type.push(34);
                        }
                    })
                    break;
                case "11": //NAS
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(1);
                        } else if (v == 2) {
                            new_job_type.push(2);
                        } else if (v == 17) {
                            new_job_type.push(44);
                        } else if (v == 19) {
                            new_job_type.push(45);
                        }
                    })
                    break;
                default:
                    break;
            }
        });
        return new_job_type;
    }

    //当模块类型未选中时处理任务类型
    var switchJobType = function (job_type) {
        var new_job_type = [];
        $.each(job_type, function (k, v) {
            switch (v) {
                case '1': //备份
                    new_job_type.push(CONF.TASK_TYPE.BACKUP, CONF.TASK_TYPE.DB_BACKUP, CONF.TASK_TYPE.VOL_CDP_BACKUP, CONF.TASK_TYPE.OS_BACKUP);
                    break;
                case '2': //恢复
                    new_job_type.push(CONF.TASK_TYPE.RECOVERY, CONF.TASK_TYPE.DB_RECOVERY, CONF.TASK_TYPE.VOL_CDP_RECOVERY, CONF.TASK_TYPE.OS_RECOVERY);
                    break;
                case '17': //副本
                    new_job_type.push(CONF.TASK_TYPE.BACKUP_COPY, CONF.TASK_TYPE.DB_BACKUP_COPY, CONF.TASK_TYPE.OS_BACKUP_COPY);
                    break;
                case '19': //归档
                    new_job_type.push(CONF.TASK_TYPE.ARCHIVE, CONF.TASK_TYPE.OS_BACKUP_ARCHIVE);
                    break;
                case '8': //迁移
                    new_job_type.push(CONF.TASK_TYPE.VM_INSTANT_RECOVERY_MOTION, CONF.TASK_TYPE.VM_CDP_INSTANT_RECOVERY_MOTION, CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION);
                    break;
                case '7': //瞬时恢复
                    new_job_type.push(CONF.TASK_TYPE.VM_INSTANT_RECOVERY, CONF.TASK_TYPE.VM_CDP_INSTANT_RECOVERY, CONF.TASK_TYPE.OS_INSTANT_RECOVERY);
                    break;
                default:
                    new_job_type.push(v);
                    break;
            }
        })
        return new_job_type;
    }

    let initTapeJobTable = function () {
        var dbTypeFormater = function (index, row) {
            switch (row.db_type) {
                case '1':
                    return 'SQL server'
                    break;
                case '2':
                    return 'Oracle'
                    break;
                case '3':
                    return 'MySQL'
                    break;
                case '4':
                    return 'DM'
                    break;
                case '5':
                    return 'PostgreSQL'
                    break;
                case '6':
                    return 'KingbaseES'
                    break;
                case '7':
                    return 'UXDB'
                    break;
                case '8':
                    return 'Highgo DB'
                    break;
                case '9':
                    return 'MariaDB'
                    break;
                case '10':
                    return 'openGauss'
                    break;
                case '11':
                    return 'Vastbase'
                    break;
                case '12':
                    return 'AntDB'
                    break;
                default:
                    if (row.module_type_value === CONF.MODULE_TYPE.DBPROTECT) {
                        return 'Unknown';
                    } else {
                        return '----';
                    }
                    break;
            }
        }

        var vmFormatter = function (index, row) {
            var vmType;

            if ($.inArray(row.vm_type, CONF.VMTYPE_GROUP.VMWARE) != -1) {
                vmType = 'VMWARE';
            } else if ($.inArray(row.vm_type, CONF.VMTYPE_GROUP.VMWARE) != -1) {
                vmType = 'XENSERVER';
            } else if ($.inArray(row.vm_type, CONF.VMTYPE_GROUP.KVM) != -1) {
                vmType = 'KVM';
            } else if ($.inArray(row.vm_type, CONF.VMTYPE_GROUP.OPENSTACK) != -1) {
                vmType = 'OPENSTACK';
            }
            return vmType;
        }

        function initTableHeight() {
            //拿到父窗口的高度
            var height;
            var panelH = window.innerHeight;

            height = panelH - 320;

            $("#tape_job .fixed-table-body").css({
                "height": height
            });
        }

        var lastIndex = [-1, -1];
        var tapeJobDetail = function (index, row, element) {
            if (index != lastIndex[1]) {
                lastIndex.push(index);
                $('#tape_job_table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                lastIndex.splice(0, 1);
            }
            let params = {
                'offset': 0,
                'limit': 1000,
                'task_uuid': row.job_uuid
            }
            Metronic.blockUI({
                target: element,
                animate: true
            });
            pAjaxRequest(params, '/api/v1/tapes/carriage_info', 'GET', function (res) {
                Metronic.unblockUI(element);
                data = res.data.rows;
                var html = '<div style=" display: flex;flex-wrap: wrap;align-items: center;"><b>'+LANG.UI_TAPE_USED_TAPE+'：</b>'
                if (data.length == 0) {
                    html += '<p> -- </p>';
                } else {
                    for (let i = 0; i <data.length; i++) {
                        html += '<p> &nbsp;' + data[i].name + '&nbsp; </p><span> | </span>';
                    }
                }

                html += '</div>'
                $(element).append(html);
            });
        }

        /**
         * @function 优先级转义
         * @param priority 参数
         */
        function priority (priority) {
            switch (priority) {
                case 1:
                    return '<span class="label label-sm label-success status-icon">'+LANG.UI_TAPE_IS_RUNNING+'</span>'
                    break;
                case 2:
                    return '<span class="label label-sm label-info status-icon">'+LANG.UI_TAPE_PRIORITY_EXECUTION+'</span>'
                    break;
                case 3:
                    return '<span class="label label-sm label-warning status-icon">'+LANG.UI_TAPE_PRIORITY_EXECUTION_LATER+'</span>'
                    break;
                case 4:
                    return '<span class="label label-sm label-default status-icon">'+LANG.UI_TAPE_PRIORITY_EXECUTION_DEFERRED+'</span>'
                    break;
                default:
                    break;
            }
        }

        var options = {
            toolbarId: '#vin_tape_job_toolbar',
            vin_toolbar: '.vin_tape_job_toolbar',
            buttonsToolbar: '#vin_tape_job_toolbar .vin_btnToolbar3',
            vin_url: '/api/v1/tapes/job',
            vin_method: 'GET',
            vin_params: function () {
                var params = {};
                var search = $('#vin_tape_job_toolbar .tapeJobSearch').val();
                if (queryParams) {
                    params = $.extend(params, queryParams);
                }
                if (search) {
                    params.search = search;
                }
                return params;
            },
            placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
            filterOption: ['job_status', 'module_type', 'job_type'], //传入每个filter选项的id ,后续会增加列，现在只有三列
            searchInput: true, //搜索框
            searchClass: 'tapeJobSearch',
            searchSelector: '.tapeJobSearch',
            paginationLoop: false,
            // dateTimePicker: {
            //     id: 'daterangepickerTapeJob'
            // }, //时间选择器
            advanceSearch: {
                module: 'current'
            }, //高级搜索
            uniqueId: 'job_uuid',
            detailFormatter: tapeJobDetail,
            resizable: true,
            onRefresh: function (params) {
                $("#tape_job_table").bootstrapTable('hideLoading');
            },
            onResetView: initTableHeight,

            onPostBody: function () {
                hideSearchOption(); //隐藏部分高级搜索条件， bug#19173
                var filtersStorageToArr = {};
                var filtersStorage = JSON.parse(sessionStorage.getItem('tape_job_table_filters'));
                let selectedFilters = 0;
                let totalFilters = 0;
                if (filterFlag == true) {
                    if (filtersStorage != '{}' && filtersStorage != undefined) {
                        filtersStorageToArr = $.each(filtersStorage, function (k, v) {
                            $.each(v, function (index, value) {
                                $('#vin_tape_job_toolbar #filterDiv .filter-content #' + k + ' input[value=' + value + ']').prop("checked", true);
                                // if (table_id == 'history_table') {
                                //     $('#vin_history_toolbar #filterDiv .filter-content #' + k + ' input[value=' + value + ']').prop("checked", true);
                                // }
                            })
                        });
                    };

                    $('#vin_tape_job_toolbar #filterDiv .filter-content input[type=checkbox]').each(function () {
                        totalFilters += 1;
                        if ($(this).prop("checked")) {
                            selectedFilters += 1
                        }
                    });

                    if (selectedFilters > 0) {
                        $('#vin_tape_job_toolbar #filterBtn span').text(''+LANG.UI_JOB_FILTER+'(' + selectedFilters + '/' + totalFilters + ')');
                    } else {
                        $('#vin_tape_job_toolbar #filterBtn span').text(''+LANG.UI_JOB_FILTER+'('+LANG.UI_PUBLIC_NOTHING+')');
                    };
                }
                if (changeHeightFlag == false) {
                    $('#tape_job_table>tbody>tr>td').css({
                        'padding-top': '4.25px',
                        'padding-bottom': '4.25px'
                    })
                    $('#vin_tape_job_toolbar .change_height i').removeClass('icon-auto-height2');
                } else if (changeHeightFlag == true) {
                    $('#tape_job_table>tbody>tr>td').css({
                        'padding-top': '10.25px',
                        'padding-bottom': '10.25px'
                    })
                    $('#vin_tape_job_toolbar .change_height i').addClass('icon-auto-height2');
                }
                addOpButton();
                initTimer();
            },

            columns: [ //列定义
                {
                    field: 'job_name', //字段名
                    title: LANG.UI_SEARCH_TASK_NAME,
                    //type: "href", //列的自定义type属性,值包括"href","label","operation",返回不同的模板
                },
                {
                    field: 'module_type',
                    title: LANG.UI_SEARCH_MODE_TYPE,
                    type: "module"
                },
                {
                    field: 'job_type',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                },
                {
                    field: 'job_status',
                    title: LANG.UI_PUBLIC_STATUS,
                    type: 'label'
                    // formatter: statusFormatter
                },
                {
                    field: 'group_name',
                    title: LANG.UI_TAPE_OWNING_TAPE_GROUP,
                    sortable: false,
                },
                {
                    field: 'driver_name',
                    title: LANG.UI_TAPE_USED_DRIVER,
                    sortable: false,
                },
                {
                    field: 'add_time',
                    title: LANG.UI_TAPE_PENDING_TIME,
                },
                {
                    field: 'continue_time',
                    title: LANG.UI_PUBLIC_CONTINUE_RUN_TIME,
                    sortable: false,
                },
                {
                    field: 'priority',
                    title: LANG.UI_TAPE_PRIORITY,
                    formatter: priority
                },
                // {
                //     title: LANG.UI_PUBLIC_OPERATION,
                //     opButton: true,
                //     sortable: false,
                //     clickToSelect: false, //不可通过点击行选中
                //     events: handlePriority,
                //     formatter: optionFormatter
                // }
            ]
        }
        $('#tape_job_table').baseTableConfig().init(options);
    }

    function hideSearchOption () {
        $('#current_taskName').parent().parent().parent().hide();
        $('#current_userName').parent().parent().parent().hide();

        $('#current_hostName').parent().parent().parent().hide();
        $('#current_vmName').parent().parent().parent().hide();
        $('#current_moduletype').parent().parent().parent().hide();
        $('.task-type-div').parent().parent().hide();
    }

    var handlePriority = {
        'click .increase': function (event, value, row, index) {
            var taskUuid = row.task_uuid;
            var groupUuid = row.group_uuid;
            var priority = row.priority - 1;
            pAjaxRequest({'task_uuid': taskUuid, 'priority': priority, 'group_uuid': groupUuid}, '/api/v1/tapes/increase', 'POST', (res)=>{
                if (operateResponseList(res)) {
                    $('#tape_job_table').bootstrapTable('refresh');
                }
            })
        },
        'click .decrease': function (event, value, row, index) {
            var taskUuid = row.task_uuid;
            var groupUuid = row.group_uuid;
            var priority = row.priority + 1;
            pAjaxRequest({'task_uuid': taskUuid, 'priority': priority, 'group_uuid': groupUuid}, '/api/v1/tapes/increase', 'POST', (res)=>{
                if (operateResponseList(res)) {
                    $('#tape_job_table').bootstrapTable('refresh');
                }
            })
        }
    }

    // function optionFormatter(value, row, index, field) {
    //     var button = '<div class="btn-group">';
    //     if (index > 5) {
    //         button = '<div class="btn-group dropup">';
    //     }
    //     button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
    //         'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
    //         '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
    //         '</button>' +
    //         '<ul class="dropdown-menu min-width100" role="menu" id="'+row.job_uuid+'">';
    //     button += '<li class="increase"><a href="javascript:;" ><i class="viconfont vicon-a-Scanning-twosaomiao"></i>提高优先级</a></li>';
    //     button += '<li class="decrease"><a href="javascript:;" ><i class="viconfont vicon-a-Find-onesoucha"></i>降低优先级</a></li>';
    //     button += '</ul></div>';
    //     return button;
    // }

    //添加禁止点击的按钮样式
    var addForbidButton = function (uuid, option) {
        $('#tape_job_table #' + uuid + ' .' + option).unbind();
        $('#tape_job_table #' + uuid + ' .' + option + ' a').css("opacity", ".4");
        $('#tape_job_table #' + uuid + ' .' + option + ' a').css("cursor", "not-allowed");
        $('#tape_job_table #' + uuid).on("click", "." + option + " a", function (e) {
            e.stopPropagation();
        });
    }

    function addOpButton () {
        var data = $("#tape_job_table").bootstrapTable("getData");
        for (let i = 0; i < data.length; i++) {
            var uuid = data[i].job_uuid;
            if (data[i].priority == 1) { //最高优先级不允许操作
                addForbidButton(uuid, 'increase');
                addForbidButton(uuid, 'decrease');
            }
            if (data[i].priority == 4) { //最低无法继续降低
                addForbidButton(uuid, 'decrease');
            }
        }
    }

    //初始化存储
    var initStorage = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 100,
            'source_type': 1
        }, '/api/v1/storages', 'GET', function (d) {
            var data = d;
            var storageSelect = $('#currentJobModal #current_storage');
            storageSelect.empty();
            var option = $("<option>").text(LANG.UI_STORAGE_ALL).val('0');
            storageSelect.append(option);
            for (var i = 0; i < data.data.rows.length; i++) {
                if (data.data.rows[i].storage_type != 10) {
                    //只看磁带存储
                    continue;
                }
                option = $("<option>").text(data.data.rows[i].storage_nickname).val(data.data.rows[i].storage_uuid).attr('type', data.data.rows[i].storage_type);
                storageSelect.append(option);
            }
            storageSelect.val('0');
        });
    }

    //初始化所有备份节点
    var initNodeSelect = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/nodes', 'GET', function (d) {
            var data = d;
            var nodeSelect = $('#currentJobModal #current_node');
            nodeSelect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
            nodeSelect.append(option);
            for (var i = 0; i < data.data.rows.length; i++) {
                option = $("<option>").text(data.data.rows[i].ip).val(data.data.rows[i].node_uuid);
                nodeSelect.append(option);
            }
            nodeSelect.val('0');
        });
    }

    //初始化虚拟化类型
    var initVMType = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/vm/platforms/hypervisors', 'GET', function (d) {
            var data = d;
            var vmSelect = $('#currentJobModal #vm_hypervisor');
            vmSelect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_HYPERVISOR).val('0');
            vmSelect.append(option);
            for (var i = 0; i < data.data.hypervisors.length; i++) {
                option = $("<option>").text(data.data.hypervisors[i].text).val(data.data.hypervisors[i].value);
                vmSelect.append(option);
            }
            vmSelect.val('0');
        });
    }

    //更新表格数据
    var update = function () {
        $('#tape_job_table').bootstrapTable('refresh');
    }

    var initTimer = function () {
        if (interval != null) { //判断计时器是否为空
            clearTimeout(interval);
        }
        interval = setTimeout(update, 5000);
    }

    return {
        init: function () {
            initTapeJobTable();
            // initTableHeight();
            addListeners();
            initNodeSelect();
            initStorage();
            initVMType();
        }
    }
}();

$(document).ready(function () {
    TapeJobs.init();
});