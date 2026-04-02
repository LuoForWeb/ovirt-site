var ExchangeData = function () {
    //时间点树
    var zTree,pointList,agentList,taskuuidList;
    var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
    var agent_uuid, task_uuid;
    var _UserPassword;
    var initErrorFlag = false;
    var grid, gridInitFlag = false
    var gfsList = {};//主要用于判断是否修改了GFS,如果没修改则不提交后台
    var _Remark = ""; //GFS标记用备注
    var showNode;//搜索到的时间点
    var currentNode;//用于高级搜索
    var op_id = '';
    //事件监听
    var initListener = function () {
        $('#allDelete').on('click', deleteSelectPoint);
        //弹出高级搜索模态框
        $('#searchAll').on('click', function () {
            $('#searchmodal').modal({'width':'700px'});
        });
        //设置GFS标记
        // $("#mark_submit").on('click',function () {
        //     markSubmit();
        // });
        //高级搜索发送请求到服务端
        $('#serach_submit').on('click', function () {
            let storage_uuid = $('#storageSelect').val();
            var params = {
                job_uuid:currentNode.job_uuid,
                node_uuid:storage_uuid,
                organization_uuid:currentNode.pId,
                data_flag:1,
                start_time : _daterangepicker_starttime ?  _daterangepicker_starttime : '', //任务开始时间查询范围开头
                end_time : _daterangepicker_endtime ?  _daterangepicker_endtime : '',      //任务开始时间查询范围结尾
                timepoint_type : parseInt($('#searchmodal #timepointType').val()),
            };
            initPointTable(params);
            //添加搜索条件显示
            addSearchContent(params);
        });
        //搜索模态框
        $('#filterSearch').click(function () {
            $('#filterSearch').popModal({
                html : $('#filter-content'),
                placement : 'bottomLeft',
                showCloseBut : true,
                onDocumentClickClose : true,
                onOkBut : searchExchange,
                onCancelBut : function (){},
                onLoad : function (){},
                onClose : function (){},
                maxWidth: 300,
                maxHeight: 'auto',
            });
        });
        $('.icheck').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
        });
        //设置GFS标记
        $("#mark_submit").on('click',function(){
            markSubmit();
        })
        $('#mark_tips_close').on('click', () => {
            $('.tabledata-wrapper__content__table .table-container').css('height', 'calc(100% - 60px)');
        })
        if (CONF.PERMISSION_ARR.includes('p_exchange_data_delete')) {
            $('#allDelete').show();
        }
    }
    //布尔类型转成int1和2
    var boolToInt = function (thisbool) {
        if (thisbool) {
            return 1;
        } else {
            return 2;
        }
    }

    //显示搜索内容
    var addSearchContent = function (p) {
        var info = "";
        $('.searchContent').text('');
        if (p.start_time && p.end_time) {
            info += '<span id="time" title="' + p.start_time + "~" + p.end_time + '"> ' + LANG.UI_SEARCH_TIME_RANGE + ': <i>' + p.start_time + "~" + p.end_time + '</i><em>X</em></span>';
        }
        if (p.timepoint_type != "0") {
            info += '<span id="timepoint_type" title="' + $('#timepointType').find("option:selected").text() + '"> ' + LANG.UI_SEARCH_TYPE + ': <i>' + $('#timepointType').find("option:selected").text() + '</i><em>X</em></span>';
        }
        $('.searchContent').show();
        $('.searchContent').append(info);
        $('.searchContent em').on('click', function () {
            $(this).parent().remove();
            var searchContent = $('.searchContent');
            if (searchContent[0].children.length == 0) {
                $('.searchContent').hide();
            }
            var parent  = $(this).parent();
            var id = parent[0].id;
            if (id == "time") {
                p.start_time = "";
                p.end_time = "";
            } else if (id == "timepoint_type") {
                p.timepoint_type = "";
            }
            let storage_uuid = $('#storageSelect').val();
            var params = {
                job_uuid:currentNode.job_uuid,
                node_uuid:storage_uuid,
                organization_uuid:currentNode.pId,
                data_flag:1,
                start_time : p.start_time , //任务开始时间查询范围开头
                end_time : p.end_time,      //任务开始时间查询范围结尾
                timepoint_type : p.timepoint_type,
            };
            initPointTable(params);
        });
        //如果没搜索条件，先隐藏div
        if (!info) {
            $('.searchContent').hide();
        }
    }

    //初始化日期选择插件
    var inintDatatimePicker = function () {
        //初始化日期时间选择控件
        $('#daterangepicker').daterangepicker({
            "autoUpdateInput": false,                                           //是否自动填充input
            "startDate": moment().subtract(6, 'days').startOf('day'),           //默认开始时间
            "endDate": moment({hour: 23, minute: 59}),  //默认结束时间
            "minDate": moment().subtract(1, 'month'), //最早可以选的日期
            "maxDate": moment({hour: 23, minute: 59}),                                              //最大可用时间
            "timePicker": true,                                                 //是否显示时间,时分
            "timePicker24Hour": true,                                           //是否是24小时制
            "alwaysShowCalendars": true,                                        //是否总是显示日期选择
            "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),    //根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),     //根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function (start, end, label) {
//          console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
        });

        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        $('#daterangepicker').on('apply.daterangepicker', function (ev, picker) {
            //给全局变量赋值,然后设置input
            _daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_range = picker.chosenLabel;
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
        });

        $('#daterangepicker').on('cancel.daterangepicker', function (ev, picker) {
            //清除全局变量,然后设置input
            _daterangepicker_starttime = "";
            _daterangepicker_endtime = "";
            _daterangepicker_range = "";
            $(this).val('');
        });

        //input右侧的图标事件
        $('.daterangepickerdiv i').click(function () {
            $(this).parent().find('input').click();
        });

    }

    var searchExchange = function () {
        if ($.trim($('#searchfs').val()) == "" && !$("input[name=foreverFilter]").get(0).checked) {
            return;
        }
        searchMark()
        //异步搜索时间点
        var data = {}
        data.storage_uuid = $('#storageSelect').val();
        data.search = $.trim($('#searchfs').val());
        data.data_flag = 1,
        Metronic.blockUI({target:'.exchange-timepoint-div',animate: true});
        pAjaxRequest(data, "/api/v1/exchange/restore_data/restore_points", "GET", function (result) {
            var setting = {
                check: {
                    enable: true,
                    nocheckInherit: false
                },
                view: {
                    showTitle: true,
                    nameIsHTML: true
                },
                data: {
                    simpleData: {
                        enable: true
                    },
                    key:{
                        title: "title"
                    },
                },
                callback: {
                    onCheck: pointOnCheck,
                    beforeClick: nodeSelect,
                    beforeExpand: nodeExpand
                }
            };
            Metronic.unblockUI('.exchange-timepoint-div');
            if (result.success) {
                zTree = $.fn.zTree.init($("#echangetimepointtree"), setting, result.data);
                zTree.expandAll(true);
            } else {
                $('#nosearchtips').show();
                $('#nopointtips').hide();
                $("#echangetimepointtree").hide();
            }
        });
    }

    var searchMark = function () {
        var markStr = LANG.UI_SETTING_VM_DATA_SCREEN + ": ";
        //获得数据
        var value = $('#searchfs').val();
        if (value == "") {
            $("#markStr").empty();
            return true;
        }
        markStr += value;
        markStr += '<a id="clearGFS" style="text-decoration: underline;margin-left: 10px;">' + LANG.UI_SETTING_VM_DATA_SCREEN_CLEAN + '</a>'
        $("#markStr").html(markStr);
        //添加清除点击事件
        $("#clearGFS").on('click',function () {
            //显示div
            initTree()
            $("#echangetimepointtree").show();
            $("#nopointtips").hide();
            $('#nosearchtips').hide();
            $("#markStr").empty();
        })
    }
    // 得到选定的时间点
    var getSelectNode = function () {
        var arr = [];
        var selectNode = zTree.getCheckedNodes(true);
        if (selectNode.length != 0) {
            selectNode.forEach(item => {
                if (item.level == 2) {
                    arr.push(item);
                }
            });
            if (arr.length != 0) {
                return arr;
            } else {
                UIToastr.showWarning(LANG.UI_DATA_FILE_NO_POINT_TITLE, LANG.UI_DATA_FILE_NO_POINT_VALUE);
                return false;
            }
        }
        UIToastr.showWarning(LANG.UI_DATA_FILE_NO_POINT_TITLE, LANG.UI_DATA_FILE_NO_POINT_VALUE);
        return false;
    }

    //删除时间点
    var deleteSelectPoint = function () {
        var node = zTree.getCheckedNodes(true);
        if (node.length == 0) {
            UIToastr.showWarning(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_DATA_BATCH_DELETE_NOT_CHECK_TIPS);
            return;
        }
        var pointList = [];
        var taskList = [];
        for (var i = 0; i < node.length; i++) {
            if (node[i].level == 1 && node[i].check_Child_State == -1) {//任务
                if (!node[i].children) {
                    var task = {};
                    task.job_uuid = node[i].job_uuid;
                    task.node_uuid = node[i].node_uuid;
                    task.organization_uuid = node[i].pId;
                    task.type = 'task';
                    taskList.push(task);
                } else if (node[i].children && node[i].children[0].nocheck) {//有子节点但是没有复选框，是搜索的结果
                    node[i].children.forEach(item => {
                        var point = {};
                        point.timepoint_uuid = item.timepoint_uuid;
                        point.node_uuid = item.node_uuid;
                        point.type = 'point';
                        pointList.push(point);
                    });
                }
            } else if (node[i].level == 2 && node[i].check_Child_State == -1) {//时间点
                if (node[i].mode == 1 && !pointList.some(item => item.timepoint_uuid === node[i].timepoint_uuid)) {//完备点--找到下面同一条链的增备点，一起删除
                    var child_nodes = node[i].getParentNode().children;
                    var checkFlag = false;
                    for (var j = 0; j < child_nodes.length; j++) {
                        if (child_nodes[j].mode == 1 && child_nodes[j].checked) {//完备是选中的
                            checkFlag = true
                            var point = {};
                            point.timepoint_uuid = child_nodes[j].timepoint_uuid;
                            point.node_uuid = child_nodes[j].node_uuid;
                            point.type = 'point';
                            pointList.push(point);
                        } else if (child_nodes[j].mode == 2 && checkFlag) {//选中完备下的增备
                            var point = {};
                            point.timepoint_uuid = child_nodes[j].timepoint_uuid;
                            point.node_uuid = child_nodes[j].node_uuid;
                            point.type = 'point';
                            pointList.push(point);
                        } else if (child_nodes[j].mode == 1 && !child_nodes[j].checked) {//未选中
                            checkFlag = false;
                        }
                    }
                }
            }
        }
        bootbox.confirm({
            title: LANG.UI_DATA_DELETE_TIMEPOINT,
            message: LANG.UI_DATA_DELETE_TIMEPOINT_TIPS,
            callback: debounce(function (r) {
                if (!r) {
                    return;
                }
                initErrorFlag = false;
                bootbox.prompt({
                    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
                    inputType: 'password',
                    callback: debounce(function (result) {
                        if (result == null) {
                            return;
                        }
                        if (hex_md5(result) == _UserPassword) {
                            submitSelectDelete(pointList,taskList);
                            return true;
                        } else {
                            $('.bootbox-input').css('border-color', "#a94442");
                            if (!initErrorFlag) {
                                var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS + '</p>';
                                $('.bootbox-input').after(des);
                                initErrorFlag = true;
                            }
                            return false;
                        }
                    }, 300),
                });
            }, 300),
        });
    }

    //递归移除节点,如果是增量的话是相互依赖的(都是依赖于上一个备份点)
    var removeNode = function (nodes, treeNode) {
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].type == 3) {
                if (nodes[i].depend_uuid == treeNode.point_uuid) {
                    zTree.removeNode(nodes[i]);
                    removeNode(nodes, nodes[i]);
                }
            }
        }
    }

    //提交删除备份时间点
    var submitSelectDelete = function (pointList,taskList) {
        // var node = zTree.getCheckedNodes(true);
        let storage_uuid = $('#storageSelect').val();
        var params = {point_list:pointList, task_list: taskList, storage_uuid:storage_uuid};
        Metronic.blockUI({target: '.exchange-timepoint-div',animate: true});
        //发送到自己模块
        pAjaxRequest(params, "/api/v1/exchange/manage_data", "DELETE", function (result) {
            Metronic.unblockUI('.exchange-timepoint-div');
            if (operateResponseList(result,LANG.UI_MICROSOFT365_DELETE_TIME_POINT)) {
                var deleteArr = pointList.concat(taskList);
                for (var i = 0; i < deleteArr.length; i++) {
                    if (deleteArr[i].type == 'task') {
                        refreshAllTree(deleteArr[i].organization_uuid + '_' + deleteArr[i].job_uuid);
                    } else if (deleteArr[i].type == 'point') {
                        refreshAllTree(deleteArr[i].timepoint_uuid);
                    }
                }
                $('#pointTable').bootstrapTable('refresh');
            }
        });
    }
    //一一对应timepointuuid进行清除树节点
    var refreshAllTree = function (nodeid) {
        var nodes = zTree.getNodesByParam("id", nodeid, null);
        if (0 == nodes.length) {
            return;
        }
        //要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
        var parent = nodes[0].getParentNode();
        if (parent != null) {
            var childrenNum = parent.children.length;
        }
        if (1 == childrenNum) {
            zTree.removeNode(parent);
        } else {
            zTree.removeNode(nodes[0]);
        }
    }

    //删除父节点
    var removeParentNode = function (node) {
        var parent = node.getParentNode();
        if (!parent) {
            return;
        }
        var childrenNum = parent.children.length;
        if (0 == childrenNum) {
            zTree.removeNode(parent);
            removeParentNode(parent);
        } else {
            return;
        }

    }


    //标星统一处理
    var starHandler = function (params, funName) {
        Metronic.blockUI({target: '#setAllMark',animate: true});
        //发送到数据管理统一处理
        $.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:funName,p:params}, function (data) {
            var d = JSON.parse(data);
            //如果失败
            if (!d.re) {
                UIToastr.showWarning(LANG.UI_OS_DATA_SET_MARK, LANG.UI_SETTING_VM_MARK_POINT_FAIL);
            } else {
                UIToastr.showSuccess(LANG.UI_OS_DATA_SET_MARK, LANG.UI_NAS_DATA_SET_MARK_SUCCESS);
                grid.getRefresh({});
                Metronic.unblockUI('#setAllMark');
            }
        });
    }

    //设置时间点树
    var setTree = function (result) {
        Metronic.unblockUI('.exchange-timepoint-div');
        if (result.success) {
            $('#echangetimepointtree').show();
            $("#nopointtips").hide();
        } else {
            $('#echangetimepointtree').hide();
            $("#nopointtips").show();
            return;
        }
        // function showTitleForTree(treeId, treeNode)
        // {
        //     return treeNode.type != 1;
        // };
        var setting = {
            check: {
                enable: true,
                nocheckInherit: false
            },
            view: {
                showTitle: true,
                nameIsHTML: true
            },
            data: {
                simpleData: {
                    enable: true
                },
                key:{
                    title: "title"
                },
            },
            callback: {
                onCheck: pointOnCheck,
                beforeClick: nodeSelect,
                beforeExpand: nodeExpand
            }
        };
        zTree = $.fn.zTree.init($("#echangetimepointtree"), setting, result.data);
    };

    //选中路径
    var pointOnCheck = function (e, id, node) {
        //选中完备点 自动勾选增备点
        if (node.level == 2 && node.mode == 1) {
            var nodeArr = node.getParentNode().children;
            var checkFlag = false;//是否选中增备
            for (var i = 0; i < nodeArr.length; i++) {
                if (nodeArr[i].mode == 1 && nodeArr[i].checked) {//完备是选中的
                    checkFlag = true
                } else if (nodeArr[i].mode == 2 && checkFlag) {//选中完备下的增备
                    nodeArr[i].checked = true;
                    zTree.updateNode(nodeArr[i]);
                } else if (nodeArr[i].mode == 1 && !nodeArr[i].checked) {//未选中
                    checkFlag = false;
                } else if (nodeArr[i].mode == 2 && !checkFlag) {//取消选中增备
                    nodeArr[i].checked = false;
                    zTree.updateNode(nodeArr[i]);
                }
            }
        } else if (node.level == 1 && node.children) {//选中任务
            var nodeArr = node.children;
            for (var i = 0; i < nodeArr.length; i++) {
                if (node.checked) {//选中
                    nodeArr[i].checked = true;
                    zTree.updateNode(nodeArr[i]);
                } else if (!node.checked) {//取消选中
                    nodeArr[i].checked = false;
                    zTree.updateNode(nodeArr[i]);
                }
            }
        }
        // nodeExpand(id, node);
    }
    //得到当前节点的所有子节点
    var getAllChildren = function (node, allNode) {

        if (node.isParent) {
            var children = node.children;
            for (var i = 0; i < children.length; i++) {
                allNode.push(children[i]);
                if (children[i].isParent) {
                    getAllChildren(children[i], allNode);
                }
            }
        } else {
            allNode.push(node);
        }
        return allNode;
    }


    //选择节点事件
    var nodeSelect = function (treeId, treeNode, clickFlag) {
        if (0 == treeNode.level) {
            return;
        }
        // $('#fsdataUrl').text('');
        //     var parent = treeNode.getParentNode();
        //     var info = parent.name + "---" + treeNode.name;
        //     $('#fsdataUrl').append(info);
        //异步加载时间点
        nodeExpand(treeId, treeNode);
        zTree.expandNode(treeNode, true);
    }

    //节点展开异步添加时间点
    var nodeExpand = function (treeId, treeNode) {
        if (treeNode.level == 2) {
            return;
        }
        if (treeNode.level == 1) {
            getSyncVcenterInfo(treeId, treeNode);
        }
    }

    //异步获取文件备份数据  refreshFlag是否重新刷新
    var getSyncVcenterInfo = function (treeId, treeNode) {
        currentNode = treeNode;
        var checkeFlag = treeNode.checked;
        let storage_uuid = $('#storageSelect').val();
        var params = {
            job_uuid:treeNode.job_uuid,
            storage_uuid:storage_uuid,
            organization_uuid:treeNode.pId,
            data_flag:1,
        };
        Metronic.blockUI({target: ".exchange-timepoint-div",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points", "GET", function (result) {
            if (result.success) {
                //success
                $('#tabletips').hide();
                $('#nastablediv').show();
                $('#marktips').show();
                if (checkeFlag) {
                    for (var i = 0; i < result.data.rows.length; i++) {
                        result.data.rows[i].checked = true;
                    }
                }
                initPointTable(params);//加载时间点表格
                Metronic.unblockUI(".exchange-timepoint-div");
                zTree.removeChildNodes(treeNode);
                zTree.addNodes(treeNode, result.data.rows, true);
                zTree.expandNode(treeNode, true);
                $('.exchange-timepoint-div .remarktips').popover();     //初始化tips
            } else {
                OPREL(data);
            }
        });
    }

    //初始化时间点表格
    var initPointTable = function (params) {
        var i = 1;
        var operates = {
            'click .remark': function (event, value, row, index) {
                remarkPoint(row);
            },
            'click .sync-data': function (event, value, row, index) {
                $('#syncPointData').modal({'width': '660px', 'height': '100%'});
                syncPointData(row);
            },
            'click .set-mark': function (event, value, row, index) {
                setMark(row);
            },
        }
        var options = {
            // resizable: false,
            searchInput: true,
            pagination:true,
            pageList: [5,10,25,50],
            vin_params:function () {
                params.table_flag = 1;
                return params;
            },
            sortName: 'time_point',
            sortOrder: 'desc',
            vin_url:"/api/v1/exchange/restore_data/restore_points",
            vin_method:"GET",
            onPostBody: function () {
                $('#pointTable .remarktips').popover();     //初始化tips
                $('#pointTable th[data-field="time_point"]').css('width', '25%');
                $('#pointTable th[data-field="type_des"]').css('width', '10%');
                $('#pointTable th[data-field="total_size"]').css('width', '10%');
                $('#pointTable th[data-field="write_size"]').css('width', '10%');
                $('#pointTable th[data-field="storage_name"]').css('width', '25%');
                $('#pointTable th[data-field="owner"]').css('width', '10%');
                $('#pointTable th[data-field="operate"]').css('width', '10%');
            },
            columns:[
                {
                    field: 'time_point',
                    title: LANG.UI_COPY_TIMEPOINT,
                    sortable: true,
                    align: 'center',
                    formatter: function (timepoint,row) {
                        return '<span title="' + row.timepoint_uuid + '"> ' + timepoint + ' ' + row.remarks_span + '</span>';
                    }
            },
                {
                    field: 'type_des',
                    title: LANG.UI_COPY_TYPE,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'total_size',
                    title: LANG.UI_COPY_DATA_SIZE,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'write_size',
                    title: LANG.UI_PUBLIC_REAL_SIZE,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'storage_name',
                    title: LANG.UI_COPY_DATA_STORAGE,
                    sortable: false,
                    align: 'center',
            },{
                    field: 'owner',
                    title: LANG.UI_CLIENT_OWNER,
                    sortable: false,
                    align: 'center',
                },

                {
                    field: 'operate',
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    align: 'center',
                    opButton: true,
                    clickToSelect: false,//不可以通过点击列选中
                    events:operates,
                    formatter: function (value,data,row) {
                        var syncDiv = '', foreverDiv = '',starDiv = '';
                        //云存储上的完备点和磁带完备点有同步
                        if (CONF.PERMISSION_ARR.includes('p_exchange_data_sync')) {
                            if ((data.mode == 1 && data.storage_type == 9)
                            || (data.mode == 1 && data.storage_type == 10)) {
                                syncDiv = '<li class="sync-data"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_refresh me-4"></i>'+ LANG.UI_VCENTER_SYNC +'</button></li>';
                            }
                        }
                        if (CONF.PERMISSION_ARR.includes('p_exchange_data_star')) {
                            //完备点增加永久标记
                            if (data.mode == 1 && data.storage_type != 10) {
                                foreverDiv = '<li class="set-mark"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_sign me-4"></i> ' + LANG.UI_SETTING_VM_DATA_SET_MARK + '</button></li>';
                            }
                        }
                        if (CONF.PERMISSION_ARR.includes('p_exchange_data_remark')) {
                            starDiv = '<li class="remark"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_modify me-4"></i>'+ LANG.UI_PUBLIC_REMARK +'</button></li>' ;
                        }
                        if (starDiv +  syncDiv + foreverDiv == '') {
                            return '--';
                        }
                        return  '<div class="btn-group dropdown-wrapper">' +
                            '<button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">'
                            + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i></button>' +
                            '<ul class="dropdown-menu" role="menu">' +
                            starDiv +
                            syncDiv +
                            foreverDiv +
                            '</ul></div>';
                    }
            }],
        }
        sessionStorage.removeItem("pointTable_pageRecord");
        $('#pointTable').bootstrapTable('destroy');
        $('#pointTable').baseTableConfig().init(options);
        $('#searchmodal').modal('hide');
    }

    //初始化时间点树
    var initTree = function () {
        var params = {};
        params.storage_uuid = $('#storageSelect').val();
        params.data_flag = 1;
        Metronic.blockUI({target: '.exchange-timepoint-div',animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data", "GET", setTree, true);
    };
    //初始化时间点展示方式和事件
    var initStorageSelect = function(){
        pAjaxRequest({backupDataFlag: true}, '/api/v1/storages/type', "GET", (result) => {
            if (result.success) {
                let data = result.data;
                let storageSelect = $('#storageSelect')
                storageSelect.empty();
                for (let i = 0; i < data.length; i++) {
                    let option = $("<option>").text(data[i].text).val(data[i].storageid);
                    storageSelect.append(option);
                }
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_STORAGE_FAILED, res.message);
            }
        })
        //绑定事件
        $('#storageSelect').on('change', storageSelectChange);
    }

    var storageSelectChange = function () {
        initTree();
    }
    //初始化当前用户密码用于删除二次确认
    var initUserPassword = function () {
        pAjaxRequest({}, "/api/v1/users/password", "GET", function (result) {
            if (result.success) {
                _UserPassword = result.data.password;
            }
        });
    }

    //布尔类型转成int1和2
    var boolToInt = function (thisbool) {
        if (thisbool) {
            return 1;
        } else {
            return 2;
        }

    }
    //添加备注
    var remarkPoint = function (row) {
        var value = row.remarks;
        bootbox.prompt({
            title: LANG.UI_DATA_ADD_REMARK,
            value:value,
            callback: debounce(function (result) {
                if (result == null || $.trim(result) == value) {
                    return;
                }
                submitRemark($.trim(result), row);
            }, 300),
        });
    }

    var submitRemark = function (remark, params) {
        var p = {};
        p.remark = remark;
        p.time_point_uuid = params.timepoint_uuid;
        Metronic.blockUI({target: '#datatable',animate: true});
        //发送到数据管理统一处理
        pAjaxRequest(p, "/api/v1/exchange/manage_data", "PUT", function (result) {
            if (operateResponseList(result,LANG.UI_MICROSOFT365_ADD_REMARK)) {
                $('#pointTable').bootstrapTable('refresh');
            }
        });
    }
    // 云存储上的完备点索引数据同步
    var syncPointData = function (row) {
        var p = {"timepoint_uuid":row.timepoint_uuid, "op_id":op_id, "node_uuid":row.node_uuid};
        pAjaxRequest(p, "/api/v1/exchange/manage_sync", "GET", function (result) {
            if (!result.success) {
                $('#syncPointData').modal('hide');
                op_id = '';
                UIToastr.showWarning(LANG.UI_MICROSOFT365_INDEX_DATA_SYNC, result.message);
            } else if (result.data.op_status == 1) {//请求中
                if (op_id == '') {
                    op_id = result.data.op_id
                } else {
                    $('.syncSize').html(result.data.detail.total_size);
                    $('#total-progress').css({width: result.data.detail.progress - 14 + '%'});
                    $('#progressright').html(result.data.detail.progress.toFixed(2) + '%');
                }
                setTimeout(syncPointData(row),1000);
            } else if (result.data.op_status == 2) {//请求完成
                $('.syncSize').html(result.data.detail.total_size);
                $('#total-progress').css({width: result.data.detail.progress - 14 + '%'});
                $('#progressright').html(result.data.detail.progress.toFixed(2) + '%');
                $('#syncPointData').modal('hide');
                op_id = '';
                UIToastr.showSuccess(LANG.UI_MICROSOFT365_INDEX_DATA_SYNC, result.message);
            }

        });
    }

    //设置时间点保留标记
    var setMark = function(rowData){
        //得到GFS保留标记
        gfsList = {};
        gfsList.forever = rowData.star;//永久保留
        //初始化清空所有勾选项
        $("input[name=foreverCheck]").iCheck('uncheck');
        //设置值
        $("#Marktimepoint_uuid").val(rowData.timepoint_uuid);
        $("#MarktimepointNodeUUID").val(rowData.node_uuid);
        //设置勾选
        if(gfsList.forever){
            $("input[name=foreverCheck]").iCheck('check');
        }
        $('#setAllMark').modal('show');
    }

    //设置标记确认
    const markSubmit = function(){
        //先得到所有信息
        let forever = $("input[name=foreverCheck]").get(0).checked;
        //得到信息后先判断是否有做修改 如果没有做修改则不提交后台
        if(gfsList.forever === forever){
            $('#setAllMark').modal('hide');
            gfsList = {};
            return;
        }
        //设置永久标记
        if(gfsList.forever !== forever){
            var p = {};
            p.time_point_uuid = $("#Marktimepoint_uuid").val();
            if(forever){
                Metronic.blockUI({target: '#setAllMark',animate: true});
                pAjaxRequest(p, "/api/v1/exchange/star", "POST", function (result) {
                    if (operateResponseList(result, LANG.UI_MICROSOFT365_SET_MARK)) {
                        $('#pointTable').bootstrapTable('refresh');
                    }
                    Metronic.unblockUI('#setAllMark');
                });
            } else {
                Metronic.blockUI({target: '#setAllMark',animate: true});
                pAjaxRequest(p, '/api/v1/exchange/star', 'DELETE', (result) => {
                    if (operateResponseList(result, LANG.UI_MICROSOFT365_CANCEL_MARK)) {
                        $('#pointTable').bootstrapTable('refresh');
                    }
                    Metronic.unblockUI('#setAllMark');
                });
            }
        }
        $('#setAllMark').modal('hide');
        editZtreeName($("#Marktimepoint_uuid").val(),forever);
    }
    //静态修改Ztree的名称
    var editZtreeName = function(timepointUUID, forever){
        //根据timeUUID得到ztree的node数据，没有搜到返回null
        var node = zTree.getNodeByParam('timepoint_uuid', timepointUUID, null);
        if (!node) {
            return;
        }
        //获得原来的name
        var old_name = node.old_name;
        var markStr = '';
        if(forever){
            markStr +='<span title="'+LANG.UI_SETTING_VM_GFS_FOREVER_POINT+'" class="viconfont vicon-remark-forever"></span>';
        }
        var new_name = old_name+" "+markStr;
        node.name = new_name;
        zTree.updateNode(node);
    }

    return {
        //main function to initiate the module
        init: function () {
            inintDatatimePicker();
            initStorageSelect(); //初始化存储下拉列表
            initListener();
            initUserPassword();
            initTree();
        }

    };

}();

jQuery(document).ready(function () {
    ExchangeData.init();
});