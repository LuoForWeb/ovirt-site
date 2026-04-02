var resourceGroup = function () {

    var changeHeightFlag = false;
    var addtype = 3;
    var editType = 3;
    var editUuid = '';
    var editData = [];
    var addData = [];
    var selectData = [];
    var selectValue = 0;

    var vm_type = 0; // 虚拟化类型
    var ed_type = 0;
    var selectType = 0; // ---------初始化页面-------

    //初始化虚拟化类型
    var initVMType = function(){
        var data = {};
        pAjaxRequest(data, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
            let hypervisors = d.data.hypervisors;
            var vmtypeselect = $('#vmtype');
            vmtypeselect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_HYPERVISOR).val('0');
            vmtypeselect.append(option);
            for (var i = 0; i < hypervisors.length; i++) {
                option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
                vmtypeselect.append(option);
            }
            var vmtypeselect = $('#edtype');
            vmtypeselect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_HYPERVISOR).val('0');
            vmtypeselect.append(option);
            for (var i = 0; i < hypervisors.length; i++) {
                option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
                vmtypeselect.append(option);
            }
            vmtypeselect.val('0');
        });
    }

    var initPrivateType = function(){
        var data = {};
        data.cloud_flag = true;
        data.cloud_type = 'private';
        pAjaxRequest(data, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
            let hypervisors = d.data.hypervisors;
            var vmtypeselect = $('#vmPrivatetype');
            vmtypeselect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_CLOUD_PLATFORM).val('0');
            vmtypeselect.append(option);
            for (var i = 0; i < hypervisors.length; i++) {
                option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
                vmtypeselect.append(option);
            }
            var vmtypeselect = $('#edPrivatetype');
            vmtypeselect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_CLOUD_PLATFORM).val('0');
            vmtypeselect.append(option);
            for (var i = 0; i < hypervisors.length; i++) {
                option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
                vmtypeselect.append(option);
            }
            vmtypeselect.val('0');
        });
    }

    var initPublicType = function(){
        var data = {};
        data.cloud_flag = true;
        data.cloud_type = 'public';
        pAjaxRequest(data, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
            let hypervisors = d.data.hypervisors;
            var vmtypeselect = $('#vmPublictype');
            vmtypeselect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_CLOUD_PLATFORM).val('0');
            vmtypeselect.append(option);
            for (var i = 0; i < hypervisors.length; i++) {
                option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
                vmtypeselect.append(option);
            }
            var vmtypeselect = $('#edPublictype');
            vmtypeselect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_CLOUD_PLATFORM).val('0');
            vmtypeselect.append(option);
            for (var i = 0; i < hypervisors.length; i++) {
                option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
                vmtypeselect.append(option);
            }
            vmtypeselect.val('0');
        });
    }

    // 初始化策略表格

    var initDataTable = function () {
        var before = ``;
        if ($.inArray('p_resource_group_delete', CONF.PERMISSION_ARR) !== -1) {
            before = `<button type="button" id="delete" class="b-btn brr2 mr12 table-toolbar-btn" style="cursor:not-allowed;background-color: #F4F4F5">
                                         <i class="icon-gray-delete"></i>
                                     </button>`;
        }
        var after = ``;
        if ($.inArray('p_resource_group_add', CONF.PERMISSION_ARR) !== -1) {
            after +=  `<button type = "button" id="addShow" class="btn table-toolbar-btn" data-toggle="drawer" data-target="#drawer-1">
                                        <i class="viconfont vicon-biaogetianjia"></i>`+ LANG.UI_PUBLIC_ADD +
                `</button>`;
        }
        if ($.inArray('p_resource_group_edit', CONF.PERMISSION_ARR) !== -1) {
            after +=  `<button type = "button" id="editShow" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-xiugai"></i>`+ LANG.UI_PUBLIC_EDIT +
                `</button>`;
        }

        var options = {
            vin_url: "/api/v1/resources/group",
            vin_method: "GET",
            toolbarId: '#vin_current_toolbar',
            buttonsToolbar: '#vin_current_toolbar .vin_btnToolbar',
            searchInput:true,
            placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
            searchOnEnterKey:true, //回车搜索
            changeHeightBtn: true, //改变高度按钮
            showExport: true, //是否开启导出按钮
            showColumns: true, //是否开启列选择按钮
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            resizable: true, //可变宽度
            sortName: 'create_time',
            sortOrder: 'desc',
            PostBody: function() {
                initCancle()
            },
            customTool: {
                beforeInput: before,
                afterInput: after
            },
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                },
                {
                    field: 'resource_group_name',
                    title: LANG.UI_RESOURCE_GROUP_NAME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'source_type_des',
                    title: LANG. UI_RECOURSE_TYPE,
                    sortable: true,
                    align: 'center',
                    formatter: function (value) {
                        return '<span title="' + value + '">' + value + '</span>';
                    }
                },
                {
                    field: 'description',
                    title: LANG.UI_PUBLIC_DESCRIPTION,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'create_time',
                    title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'create_user_name',
                    title: LANG.UI_GLOBAL_STRATEGY_UPDATE_USER,
                    sortable: true,
                    align: 'center',
                },
            ],
            onCheck: function () {
                checkEvent('#resource_table', '#delete');
            },
            onUncheck: function () {
                checkEvent('#resource_table', '#delete');
            },
            onCheckAll: function () {
                checkEvent('#resource_table', '#delete');
            },
            onUncheckAll: function () {
                checkEvent('#resource_table', '#delete');
            },
        }
        $('#resource_table').bootstrapTable('destroy');
        sessionStorage.removeItem("resource_table_pageRecord");
        $('#resource_table').baseTableConfig().init(options);
    };

    var checkEvent = function (tableId, btnId) {
        let select = $('' + tableId + '').bootstrapTable('getSelections');
        if (select.length == 0) {
            $('' + btnId + ' i').addClass('icon-gray-delete');
            $('' + btnId + ' i').removeClass('icon-white-delete');
            $('' + btnId + '').removeClass('select-delete-btn');
            $('' + btnId + '').addClass('cancel-delete-btn');
            $('' + btnId).css('cursor', 'not-allowed');
        } else {
            $('' + btnId + ' i').removeClass('icon-gray-delete');
            $('' + btnId + ' i').addClass('icon-white-delete');
            $('' + btnId + '').removeClass('cancel-delete-btn');
            $('' + btnId + '').addClass('select-delete-btn');
            $('' + btnId).css('cursor', 'pointer');
        }
    }

    // ----------页面交互-----------
    // 初始化监听时间
    var initListeners = function () {
        $('#vin_current_toolbar .search-btn').unbind("click").on('click', function (options) {
            queryParams = {}
            queryParams.search = $('#vin_current_toolbar .customSearch').val();
            queryParams.offset = 0
            queryParams.sortColumn = 3
            $('#resource_table').bootstrapTable('refresh', {query: queryParams});
        })
        $('#vin_current_toolbar .customSearch').keypress(function (e) {
            if (e.which == 13) {
                queryParams = {}
                queryParams.search = $('#vin_current_toolbar .customSearch').val();
                queryParams.offset = 0
                queryParams.sortColumn = 3
                $('#resource_table').bootstrapTable('refresh', {query: queryParams});
            }
        });

        // 删除
        $('#delete').on('click', deleteStrategyBatchSubmit);
        // 改变表格高度
        $('.change_height').on('click', changeHeight);
        //添加里面的筛选
        $('#addSelect').unbind('change').on('change', function (){addSelect();});
        //筛选里面的筛选
        $('#editSelect').unbind('change').on('change', function (){editSelect();});
        //添加确认
        $('#add_submit').unbind('click').on('click', addSubmit);
        //修改确认
        $('#modify_submit').unbind('click').on('click', editSubmit);
        $('#modify_close').unbind('click').on('click', function () {
            $('#resource_table').bootstrapTable('uncheckAll');
        });
        // 添加-打开抽屉
        $('#addShow').unbind('click').on('click', addShow);

        // 添加-打开抽屉
        $('#editShow').unbind('click').on('click', editShow);

        // 虚拟化类型改变事件
        $('#vmtype').on('change', function (){
            addSelect(1)
        });

        // 私有云类型改变事件
        $('#vmPrivatetype').on('change', function (){
            addSelect(2)
        });

        // 公有云类型改变事件
        $('#vmPublictype').on('change', function (){
            addSelect(3)
        });

        //修改虚拟化改变事件
        // 虚拟化类型改变事件
        $('#edtype').on('change', function (){
            editSelect(1)
        });

        // 私有云类型改变事件
        $('#edPrivatetype').on('change', function (){
            editSelect(2)
        });

        // 公有云类型改变事件
        $('#edPublictype').on('change', function (){
            editSelect(3)
        });
    }
    const initCancle = function() {
        $('#resource_table th[data-field="resource_group_name"]').css('width','30%');
        $('#resource_table th[data-field="source_type_des"]').css('width','15%');
        $('#resource_table th[data-field="description"]').css('width','30%');
        $('#resource_table th[data-field="create_time"]').css('width','13%');
        $('#resource_table th[data-field="create_user_name"]').css('width','12%');
        $('#vin_current_toolbar .clear i').off().on('click', function () {
            $('#vin_current_toolbar .customSearch').val('');
            queryParams.search = '';
            $('#vin_current_toolbar .clear').removeClass('show');
            sessionStorage.removeItem("search");
            $('#vin_current_toolbar .search .customSearch').attr('placeholder',LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH);
            $('#resource_table').bootstrapTable('resetSearch');
        });
    }

    const initCancle0 = function() {
        $('#vin_select_resource_toolbar .search-btn').unbind("click").on('click', function (options) {
            var queryParams = {}
            queryParams.search = $('#vin_select_resource_toolbar .customSearch').val();
            queryParams.offset = 0
            $('#editReport').bootstrapTable('refresh', {query: queryParams});
        })
        $('#vin_select_resource_toolbar .clear i').off().on('click', function () {
            $('#vin_select_resource_toolbar .customSearch').val('');
            $('#vin_select_resource_toolbar .clear').removeClass('show');
            $('#vin_select_resource_toolbar .search .customSearch').attr('placeholder',LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH);
            $('#editReport').bootstrapTable('resetSearch');
        });
    }

    const initCancle1 = function() {
        $('#vin_select_resource_toolbar1 .search-btn').unbind("click").on('click', function (options) {
            var queryParams = {}
            queryParams.search = $('#vin_select_resource_toolbar1 .customSearch').val();
            queryParams.offset = 0
            $('#backupReport').bootstrapTable('refresh', {query: queryParams});
        })

        $('#vin_select_resource_toolbar1 .clear').unbind('click').on('click', function () {

            $('#vin_select_resource_toolbar1 .customSearch').val('');
            $('#vin_select_resource_toolbar1 .clear').removeClass('show');
            $('#vin_select_resource_toolbar1 .search .customSearch').attr('placeholder',LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH);

            var queryParams = {}
            queryParams.search = '';
            queryParams.offset = 0
            $('#backupReport').bootstrapTable('refresh', {query: queryParams});
        });
    }

    // 改变表格高度
    var changeHeight = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#resource_table>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            })
            $('#vin_current_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#resource_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#vin_current_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }

    var addSelect = function (type = 0) {
        selectValue = $('#addSelect').find('option:selected').val();
        addtype = selectValue;
        $('#vmtype').hide();
        $('#vmPrivatetype').hide();
        $('#vmPublictype').hide();
        if (selectValue == 3) {
            // 虚拟化
            $('#vmtype').show();
        }
        if (selectValue == 61) {
            // 私有云
            $('#vmPrivatetype').show();
        }
        if (selectValue == 58) {
            //公有云
            $('#vmPublictype').show();
        }


        if (type == 1) {
            // 虚拟化的类型切换事件
            vm_type = $('#vmtype').find('option:selected').val();
        }
        if (type == 2) {
            //私有云的类型切换事件
            vm_type = $('#vmPrivatetype').find('option:selected').val();
        }
        if (type == 3) {
            //公有云的类型切换事件
            vm_type = $('#vmPublictype').find('option:selected').val();
        }

        if (type == 0) {
            $('#vmtype').val('0');
            $('#vmPrivatetype').val('0');
            $('#vmPublictype').val('0');
            vm_type = 0;
        }

        addData = [];
        queryParams = {};
        queryParams.source_type = addtype;
        queryParams.vm_type = vm_type;
        queryParams.offset = 0;
        sessionStorage.removeItem("backupReport_pageRecord");
        $('#backupReport').bootstrapTable('selectPage', 1);
        $('#backupReport').bootstrapTable('refresh', {query: queryParams});

    }
    var addShow = function () {
        $('#addName').val('');
        $('#addDescription').val('');
        addtype = 8
        $('#addSelect').val(addtype)
        addData = [];
        queryParams = {};
        queryParams.source_type = addtype;
        queryParams.offset = 0;
        $('#backupReport').bootstrapTable('selectPage', 1);
        sessionStorage.removeItem("backupReport_pageRecord");
        $('#resource_table').bootstrapTable('uncheckAll');
        $('#backupReport').bootstrapTable('refresh', {query: queryParams});
    }
    var addBackupRecords = function () {
        var options = {
            vin_url: "/api/v1/resources/source",
            vin_method: "GET",
            paginationSuccessivelySize: 0,
            paginationPagesBySide: 0,
            toolbarId: '#vin_select_resource_toolbar1',
            buttonsToolbar: '#vin_select_resource_toolbar1 .vin_btnToolbar1',
            placeholder: LANG.UI_USER_SEARCH_BY_RESOURCE_NAME,
            searchInput: true, //搜索框
            searchOnEnterKey: true,
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            vin_params:function () {

                let params = {
                    source_type: addtype,
                    vm_type: vm_type,
                };
                params.search = $('#vin_select_resource_toolbar1 .resourceSearch').val();
                return params;
            },
            PostBody: function() {
                initCancle1()
                $('#backupReport th[data-field="name"]').css('width', '70%');
                $('#backupReport th[data-field="status"]').css('width', '20%');
            },
            onCheck: function (row) {
                addData.push(row.uuid)
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = addData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index == -1) {
                        addData.push(row[i].uuid)
                    }
                }
            },
            onUncheckAll: function (a,row) {
                for (var i = 0; i < row.length; i++) {
                    var index = addData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index != -1) {
                        addData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
            },
            onUncheck: function (row) {
                var index = addData.indexOf(row.uuid); // 查找元素的索引
                if (index !== -1) {
                    addData.splice(index, 1); // 从数组中删除一个元素
                }
            },
            columns:[
                {
                    checkbox: true,
                    sortable: false,
                    formatter : function (index,row) {
                        if (row.is_active == false) {
                            return {
                                disabled: true
                            }
                        }
                        for (var i = 0; i < addData.length; i++) {
                            if (row.uuid == addData[i]) {
                                return true
                            }
                        }

                    }
                },
                {
                    field: 'name',
                    title: LANG.UI_RESOURCE_NAME,
                    sortable: false,
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    sortable: false,
                    formatter: function (value, row, index, field) {
                        return '<span class="label label-sm label-'+ row.class + ' status-icon">' + value + '</span>'
                    }
                },

            ],
        };
        sessionStorage.removeItem("backupReport_pageRecord");
        $('#backupReport').baseTableConfig().init(options);
    }
    var addSubmit = function () {
        if (addData.length <= 0 ) {
            return UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_VM_MACHINE_MODIFY_CHOOSE_ONE);
        }
        var data = {};
        data.name = $('#addName').val();
        data.source_type = addtype;
        data.remark = $('#addDescription').val();
        data.source_list = addData;
        pAjaxRequest(data, "/api/v1/resources/group_add", "POST", function (res) {
            if (operateResponseList(res)) {
                $('#resource_table').bootstrapTable('refresh');
                $('#drawer-1').drawer('hide');
            }
        });

    }
    var editShow = function (){
        var selectedRow = $('#resource_table').bootstrapTable('getSelections');
        if (selectedRow.length != 1) {
            return UIToastr.showWarning(LANG.UI_RESOURCE_GROUP_MODIFY, LANG.UI_VM_MACHINE_MODIFY_CHOOSE);
        }
        var row = selectedRow[0];
        checkOperateAuth(
            {
                type: 1,
                user_uuid: row.user_uuid,
                auth: 'resmanagement'
            },
            function (){
                $('#resourcegroupname').val(row.resource_group_name).prop("disabled", true);
                $('#description').val(row.description);
                editUuid = row.uuid;
                editData = [];
                selectData = [];
                selectType = 3;
                editType = 3;
                pAjaxRequest({}, '/api/v1/resources/groupDetail/' + editUuid, 'get', function (res) {
                    if (res.success) {
                        editData  = res.data.sourceList;
                        editType = res.data.type;
                        if ( editType == 0 ) {
                            editType = 3
                        }
                        selectData = res.data.sourceList;
                        selectType = res.data.type;
                    }
                    $('#editSelect').val(editType);

                    switch (editType) {
                        case 3:
                            $('#edtype').show();
                            $('#edtype').val(0);
                            $('#edPrivatetype').hide();
                            $('#edPublictype').hide();
                            break;
                        case 61:
                            $('#edtype').hide();
                            $('#edPrivatetype').show();
                            $('#edPrivatetype').val(0);
                            $('#edPublictype').hide();
                            break;
                        case 58:
                            $('#edtype').hide();
                            $('#edPrivatetype').hide();
                            $('#edPublictype').show();
                            $('#edPublictype').val(0);
                            break;
                        default:
                            $('.sub-type-select').hide(); // 所有子类型隐藏
                            break;
                    }


                    ed_type = 0;
                    params = {};
                    params.source_type = editType;
                    params.offset = 0;
                    params.uuid = editUuid;
                    editRecords()
                    sessionStorage.removeItem("editReport_pageRecord");
                    $('#editReport').bootstrapTable('selectPage', 1);
                    $('#editReport').bootstrapTable('refresh', {query: params});

                });
                $('#drawer-edit').drawer('show');
            }
        )
    }
    var editSelect = function (type = 0) {
        var editSelectValue = $('#editSelect').find('option:selected').val();
        editData = []
        editType = editSelectValue

        if (editType == selectType) {
            editData = selectData
        }

        //修改
        $('#edtype').hide();
        $('#edPrivatetype').hide();
        $('#edPublictype').hide();
        if (editSelectValue == 3) {
            // 虚拟化
            $('#edtype').show();
        }
        if (editSelectValue == 61) {
            // 私有云
            $('#edPrivatetype').show();
        }
        if (editSelectValue == 58) {
            //公有云
            $('#edPublictype').show();
        }


        if (type == 1) {
            // 虚拟化的类型切换事件
            ed_type = $('#edtype').find('option:selected').val();
        }
        if (type == 2) {
            //私有云的类型切换事件
            ed_type = $('#edPrivatetype').find('option:selected').val();
        }
        if (type == 3) {
            //公有云的类型切换事件
            ed_type = $('#edPublictype').find('option:selected').val();
        }

        if (type == 0){
            $('#edtype').val('0');
            $('#edPrivatetype').val('0');
            $('#edPublictype').val('0');
            ed_type = 0;
        }

        var params = {}
        params = {};
        params.source_type = editType;
        params.vm_type = ed_type;
        params.offset = 0;
        sessionStorage.removeItem("editReport_pageRecord");
        $('#editReport').bootstrapTable('selectPage', 1);

        $('#editReport').bootstrapTable('refresh', {query: params});

    }
    var editRecords = function (a) {
        var options = {
            vin_url: "/api/v1/resources/source",
            vin_method: "GET",
            paginationSuccessivelySize:0,
            paginationPagesBySide:0,
            toolbarId: '#vin_select_resource_toolbar',
            buttonsToolbar: '#vin_select_resource_toolbar .vin_btnToolbar',
            placeholder: LANG.UI_USER_SEARCH_BY_RESOURCE_NAME,
            searchOnEnterKey: true,
            searchInput: true, //搜索框
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            vin_params:function () {
                var params = {
                    source_type: editType,
                    uuid: editUuid,
                    vm_type:ed_type
                };
                params.search = $('#vin_select_resource_toolbar .resourceSearch').val();
                return params;
            },
            PostBody: function() {
                initCancle0()
                $('#editReport th[data-field="name"]').css('width', '70%');
                $('#editReport th[data-field="status"]').css('width', '20%');
            },
            onCheck: function (row) {
                editData.push(row.uuid)
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = editData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index == -1) {
                        editData.push(row[i].uuid)
                    }
                }
            },
            onUncheckAll: function (a,row) {
                for (var i = 0; i < row.length; i++) {
                    var index = editData.indexOf(row[i].uuid); // 查找元素的索引
                    if (index != -1) {
                        editData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
            },
            onUncheck: function (row) {
                var index = editData.indexOf(row.uuid); // 查找元素的索引
                if (index !== -1) {
                    editData.splice(index, 1); // 从数组中删除一个元素
                }
            },
            columns:[
                {
                    checkbox: true,
                    sortable: false,
                    formatter : function (index,row) {
                        if (row.is_active == false) {
                            return {
                                disabled: true
                            }
                        }
                        for (var i = 0; i < editData.length; i++) {
                            if (row.uuid == editData[i]) {
                                return true
                            }
                        }
                    }
                },
                {
                    field: 'name',
                    title: LANG.UI_RESOURCE_NAME,
                    sortable: false,
                },
                {
                    field: 'status',
                    title: LANG.UI_BLACK_WHITE_STATUS,
                    sortable: false,
                    formatter: function (value, row, index, field) {
                        return '<span class="label label-sm label-'+ row.class + ' status-icon">' + value + '</span>'
                    }
                },

            ],
        };
        sessionStorage.removeItem("editReport_pageRecord");
        $('#editReport').baseTableConfig().init(options);
    }
    //修改
    var editSubmit = function () {
        if (editData.length <= 0 ) {
            return UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_VM_MACHINE_MODIFY_CHOOSE_ONE);
        }
        var data = {};
        data.name = $('#resourcegroupname').val();
        data.source_type = editType;
        data.remark = $('#description').val();
        data.source_list = editData
        Metronic.blockUI({target: "#modifyModal", animate: true});
        pAjaxRequest(data, '/api/v1/resources/group/' + editUuid, 'post', function (d) {
            Metronic.unblockUI("#modifyModal");
            if (operateResponseList(d)) {
                $('#resource_table').bootstrapTable('refresh');
                $('#drawer-edit').drawer('hide');
                $('#resource_table').bootstrapTable('uncheckAll');
            }
        })

    }

    //删除策略确认
    var deleteStrategyBatchSubmit = function (grid) {
        var ids = getIdSelectedId('#resource_table')
        //策略必选
        if (ids.length == 0) {
            return tipsDeleteStrategy();
        }

        checkOperateAuth(
            {
                type: 1,
                user_uuid: getIdSelectedUseruuid('#resource_table').join(','),
                auth: 'resmanagement'
            },
            function (){
                bootbox.confirm({
                    title: LANG.UI_RESOURCE_GROUP_DELETE,
                    message: LANG.UI_RESOURCE_GROUP_DELETE_CONFIRM,
                    callback: debounce(function (r) {
                        if (!r) {
                            return;
                        }
                        Metronic.blockUI({target: '#resourceGroupContent',animate: true});
                        pAjaxRequest({uuids: ids}, '/api/v1/resources/group/', 'delete', function (res) {
                            Metronic.unblockUI('#resourceGroupContent');
                            if (operateResponseList(res)) {
                                $('#resource_table').bootstrapTable('refresh');
                                $('#resource_table').bootstrapTable('uncheckAll');
                            }
                        });
                    }, 300)
                })
            }
        )
    }

    //没有选中的策略提示
    var tipsDeleteStrategy = function () {
        return UIToastr.showInfo(LANG.UI_RESOURCE_GROUP_DELETE, LANG.UI_VM_MACHINE_MODIFY_CHOOSE_ONE);
    }

    //获取选中项uuid
    function getIdSelectedId(select) {
        return $.map($(select).bootstrapTable('getSelections'), function (row) {
            return row.uuid;
        })
    }
    //获取选中项
    function getIdSelectedUseruuid(select) {
        return $.map($(select).bootstrapTable('getSelections'), function (row) {
            return row.user_uuid;
        })
    }
    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 310;

        $("#resource_group_div .fixed-table-body").each(function() {
            this.style.setProperty('height', height + 'px', 'important');
        });
    }
    return{
        init: function () {
            initVMType(); //初始化虚拟化类型
            initPublicType(); //初始化虚拟化类型
            initPrivateType(); //初始化虚拟化类型
            initDataTable();
            initListeners();
            addBackupRecords();
            initTableHeight();
        }
    };
}();
jQuery(document).ready(function () {
    resourceGroup.init();
});