var InstantaneousData = function () {
    var grid = $('#datatable'), gridInitFlag = false,nodeTypetreeInitFlag = false;
    var zTree,nodeParamList, timepointList; //节点树，节点搜索列表，时间点存放列表
    var agent_uuid, task_uuid;
    var _UserPassword;
    var initErrorFlag = false;
    //时间选择器全局变量,方便提交搜索的时候直接使用
    var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;

    var setTree = function(zNodes){
        $("#vcenter_tree").show();
        $("#nopointtips").hide();
        Metronic.unblockUI('#vcenter_tree');
        if(!zNodes.length){
            $("#vcenter_tree").hide();
            $("#nopointtips").show();
            return;
        }
        function showTitleForTree(treeId, treeNode) {
            return treeNode.type != 1;
        }
        var setting = {
            check: {
                enable: true,
                nocheckInherit: false,
            },
            data: {
                simpleData: {
                    enable: true
                },
                key: {
                    title: "title"
                }
            },
            callback: {
                beforeClick: nodeSelect,
                onCheck: nodeCheck,
                beforeExpand: nodeExpand
            },
            view: {
                showTitle: showTitleForTree,
                nameIsHTML:true
            }
        };
        zTree = $.fn.zTree.init($("#vcenter_tree"), setting, zNodes);
        nodeTypetreeInitFlag = true;
    }

    //点击节点，选中并展开
    var nodeSelect = function(treeId, treeNode, clickFlag){
        if(treeNode.type == 1){
            $('#vmdataUrl').text('');
            $('.searchContent').text('');
            $('#searchDiv').hide();
            var parent = treeNode.getParentNode();
            var info = parent.name + "---" + treeNode.name;
            $('#vmdataUrl').append(info);
            let storage_uuid = $('#storageselect').val(); // 按存储筛选
            handleRecords(treeNode.agent_uuid, treeNode.task_uuid, storage_uuid); //录入备份数据列表
        }
        //展开不需要选中
//		zTree.checkNode(treeNode, !treeNode.checked, true, true);
        nodeExpand(treeId, treeNode);
        zTree.expandNode(treeNode, true);
    }

    //节点展开异步添加时间点
    var nodeExpand = function(treeId, treeNode){
        if(treeNode.clickshow){
            if(treeNode.children) return true;
            getSyncVcenterInfo(treeId, treeNode, false, false);
        }else{
            return true;
        }

    }

    //异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
    var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag){
        var nodeuuid = $('#nodeselect').val();
        var storageuuid = $('#storageselect').val();
        if (null === nodeuuid) {
            nodeuuid = '';
        }
        var div = ".vcenter-tree";
        Metronic.blockUI({target: div,animate: true});
        let p = {
            task_uuid: treeNode.task_uuid,
            agent_uuid: treeNode.agent_uuid,
            manage_flag: true,
            vm_check: treeNode.checked,
            node_uuid: nodeuuid,
            storage_uuid: storageuuid,
            point_type: 'agent'
        };
        pAjaxRequest(p, "/api/v1/recovery/instantaneous/points", "GET", function (d) {
            Metronic.unblockUI(div);
            if(d.success){
                //success
                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
                if(expendFlag == true){
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                }
            }
        }, false);
    }

    //得到当前节点的所有子节点
    var getAllChildren = function(node, allNode){
        if(node.isParent){
            var children = node.children;
            if(children){
                for(var i=0;i<children.length;i++){
                    allNode.push(children[i]);
                    if(children[i].isParent){
                        getAllChildren(children[i], allNode);
                    }
                }
            }
        }else{
            allNode.push(node);
        }
        return allNode;
    }

    //选择备份节点
    var nodeCheck = function (e, id, node){
        var allNodes = zTree.getCheckedNodes(true);
        //如果选中的是完备点,勾选和未勾选时同步处理其子节点
        if(node.type <= 3 && node.checked ){
            for(var j=0;j<allNodes.length;j++){
                if(allNodes[j].type == 3 &&allNodes[j].isParent && allNodes[j].checked){
                    var children = allNodes[j].children;
                    for(var i=0;i<children.length;i++){
                        zTree.setChkDisabled(children[i], false);
                        zTree.checkNode(children[i], true, true);
                        zTree.setChkDisabled(children[i], true);
                    }
                }
            }
        }else if(node.type <= 3){
            var allChildren = getAllChildren(node,[]);
            for(var j=0;j<allChildren.length;j++){
                if(allChildren[j].type == 4){
                    zTree.setChkDisabled(allChildren[j], false);
                    zTree.checkNode(allChildren[j],false,true);
                    zTree.setChkDisabled(allChildren[j],true);
                }
            }
        }
    }

    //初始化虚拟机节点树
    var initTree = function() {
        let p = {};
        p.show_type = 1;//按虚拟机
        p.storage_uuid = $('#storageselect').val(); // 按存储筛选
        if (null === p.node_uuid) {
            p.node_uuid = '';
        }
        p.manage_flag = true; //得到备份数据管理,获取checkbox
        p.data_flag = true; //备份数据标志
        p.point_type = 'agent';
        pAjaxRequest(p, "/api/v1/recovery/instantaneous/points/tree", "GET", function (d) {
            setTree(d.data.info);
        }, false);
    }

    //新增方法开始-----------------
    var initStorageShowType =  function(){
        $.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getStorageType',p:JSON.stringify({backupDataFlag: true})}, function(d){
            var data = JSON.parse(d);
            // console.log("data----",data);
            var stroageselect = $('#storageselect')
            stroageselect.empty();
            for(var i  = 0;i<data.length;i++){
                var option =  $("<option>").text(data[i].text).val(data[i].storageid);
                stroageselect.append(option);
            }
        });
        //绑定事件
        $('#storageselect').on('change', storageselectChange);
    }
    //存储选择改变事件
    var storageselectChange  = function(){
        $("#markStr").empty();
        $('#nosearchtips').hide();
        nodeTypetreeInitFlag = false;
        initTree();
    }
    //新增方法结束----------------------

    //删除时间点
    var deletePoint = function(rowData){
        if (CONF.BD_STORAGE_TYPE.TAPE === parseInt(rowData.storage_type)) {
            // 时间点所在存储是磁带则无法删除
            UIToastr.showWarning(LANG.UI_DATA_DELETE_TIMEPOINT, LANG.UI_TAPE_DELETE_BACKUP_POINT_TIPS);
            return;
        }
        bootbox.confirm({
            title: LANG.UI_DATA_DELETE_TIMEPOINT,
            message: LANG.UI_DATA_DELETE_TIMEPOINT_TIPS,
            callback: function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({
                    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
                    inputType: 'password',
                    callback: function (result) {
                        if(result == null) return;
                        if(hex_md5(result) == _UserPassword){
                            submitDelete(rowData);
                            return true;
                        }else{
                            $('.bootbox-input').css('border-color', "#a94442");
                            if(!initErrorFlag){
                                var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+ LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS +'</p>';
                                $('.bootbox-input').after(des);
                                initErrorFlag = true;
                            }
                            return false;
                        }
                    }
                });
            }
        });
    }

    //初始化监听事件
    var initListener = function(){
        $('#allDelete').on('click', deleteSelectPoint);
        //弹出高级搜索模态框
        $('#searchAll').on('click', function(){
            $('#searchmodal').modal({'width':'800px', 'height':'300px'});
        });

        //高级搜索发送请求到服务端
        $('#serach_submit').on('click', function(){
            var p = {};
            p.accurate_flag = true;
            p.start_time = _daterangepicker_starttime; //任务开始时间查询范围开头
            p.end_time = _daterangepicker_endtime;		//任务开始时间查询范围结尾
            p.offset = 0;
            p.limit = 20;
            p.sort = 'timepoint';
            p.order = 'desc';
            p.agent_uuid = agent_uuid;
            p.task_uuid = task_uuid;

            grid.bootstrapTable('refreshOptions', {
                queryParams: function (queryParams) {
                    return $.extend(queryParams, p);
                }
            });
            //添加搜索条件显示
            addSearchContent(p);
            $('#searchmodal').modal('hide');
        });
        $('.icheck').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue'
        });
    }

    //显示搜索内容
    var addSearchContent = function(p){
        var info = "";
        $('.searchContent').text('');
        if(p.start_time && p.end_time){
            info += '<span id="time" title="' + p.start_time + "~" + p.end_time + '"> '+ LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.start_time + "~" + p.end_time + '</i><em>X</em></span>';
        }

        $('.searchContent').append(info);
        $('#searchDiv').show();
        $('#searchDiv .searchContent em').on('click', function(){
            $(this).parent().remove();
            var searchContent = $('#searchDiv .searchContent');
            if(searchContent[0].children.length == 0){
                $('#searchDiv').hide();
            }
            var parent  = $(this).parent();
            var id = parent[0].id;
            if(id == "time"){
                p.start_time = "";
                p.end_time = "";
            }else{
                p[id] = "";
            }
            grid.bootstrapTable('refresh', {query: p});

        });
        $('#searchDiv .clearSearch').on('click',function(){
            $('#searchDiv .searchContent').text('');
            $('#searchDiv').hide();
            grid.bootstrapTable('refreshOptions', {
                queryParams: function (queryParams) {
                    return {
                        offset: 0,
                        limit: 20,
                        sort: 'timepoint',
                        order: 'desc',
                        agent_uuid: agent_uuid,
                        task_uuid: task_uuid
                    }
                }
            });
        });
        //如果没搜索条件，先隐藏div
        if(!info){
            $('#searchDiv').hide();
        }
    }

    //批量删除所选时间点
    var deleteSelectPoint = function(){
        var node = zTree.getCheckedNodes(true);
        if(node.length == 0){
            UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_DATA_BATCH_DELETE_NOT_CHECK_TIPS);
            return;
        }
        timepointList = [];					//虚拟机列表

        for(var i=0; i<node.length; i++){
            if(!node[i].children){
                timepointList.push(node[i].timepoint_uuid);
            }
        }

        //如果未选中，提示用户未勾选节点
        bootbox.confirm({
            title: LANG.UI_DATA_DELETE_TIMEPOINT,
            message: LANG.UI_DATA_CONFIRM_DELETE_TIPS1 + LANG.UI_DATA_CONFIRM_DELETE_TIPS2,
            callback: function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({
                    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
                    inputType: 'password',
                    callback: function (result) {
                        if(result == null) return;
                        if(hex_md5(result) == _UserPassword){
                            submitSelectDelete();
                            return true;
                        }else{
                            $('.bootbox-input').css('border-color', "#a94442");
                            if(!initErrorFlag){
                                var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+ LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS +'</p>';
                                $('.bootbox-input').after(des);
                                initErrorFlag = true;
                            }
                            return false;
                        }
                    }
                });
            }
        });
    };

    //左侧批量删除所选择的
    var submitSelectDelete = function(){
        var node = zTree.getCheckedNodes(true);
        var params = {tree_list: timepointList};
        Metronic.blockUI({target: '.batchDeleteDiv',animate: true});
        pAjaxRequest(params, "/api/v1/recovery/instantaneous/points/tree", "DELETE", function (d) {
            Metronic.unblockUI('.batchDeleteDiv');
            if (operateResponseList(d)) {
                for(var i=0;i<node.length;i++){
                    var halfCheck = node[i].getCheckStatus();
                    if(!halfCheck.half){
                        refreshAllTree(node[i].id);
                    }else{
                        zTree.checkNode(node[i],!node[i].checked,false,false);
                    }
                }
                grid.bootstrapTable('refresh');
            }
        }, true);
    }

    //一一对应timepointuuid进行清除树节点
    var refreshAllTree = function(nodeid){
        var nodes = zTree.getNodesByParam("id", nodeid, null);
        if(0 == nodes.length){
            return;
        }
        //要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
        var parent = nodes[0].getParentNode();
        if(parent !=null){
            var childrenNum = parent.children.length;
        }
        if(1 == childrenNum){
            if (nodes[0].children) {
                getSyncVcenterInfo("vcenter_tree", parent, true, true);
            } else {
                zTree.removeNode(parent);
            }
        }else{
            zTree.removeNode(nodes[0]);
        }
    }

    //单个删除
    var submitDelete = function(rowData){
        var timepoint_uuid = rowData.timepoint_uuid;
        var params = {timepoint_uuid: timepoint_uuid};
        Metronic.blockUI({target: '#datatable',animate: true});
        pAjaxRequest(params, "/api/v1/recovery/instantaneous/points", "DELETE", function (d) {
            Metronic.unblockUI('#datatable');
            if (operateResponseList(d, LANG.UI_DATA_DELETE_TIMEPOINT)) {
                grid.bootstrapTable('refresh');
                var idStr = rowData.detail.task_uuid + rowData.detail.agent_uuid;
                //先刷新界面树
                refreshTree(rowData.timepoint_uuid);
                var node = zTree.getNodesByParam("id", idStr, null);
                //如果还有时间节点执行刷新树操作
                if(node.length !=0){
                    getSyncVcenterInfo("vcenter_tree", node[0], true, true);
                }
            }
        }, true);
    }

    //找到父节点
    var findParent = function(treeObj,node){
        zTree.expandNode(node,true,false,false);
        if(!node.children){
            nodeParamList.push(node);
            zTree.expandNode(node,false,false,false);
        }
        var pNode = node.getParentNode();
        if(pNode != null){
            nodeParamList.push(pNode);
            findParent(zTree, pNode);
        }
    }

    //如果把一个虚拟机下所有时间点删除后,需要把这个树上的虚拟机移除
    var refreshTree = function(nodeid){
        var nodes = zTree.getNodesByParam("id", nodeid, null);
        if(0 == nodes.length){
            return;
        }
        //要删除的节点
        var vmNode = nodes[0];
        //要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
        var parent = vmNode.getParentNode();
        if(!parent) return;
        var childrenNum = parent.children.length;
        if(1 == childrenNum && vmNode.type == 3){
            if (vmNode.children) {
                //完备点下还有其他时间点则刷新
                getSyncVcenterInfo("vcenter_tree", parent, true, true);
            } else {
                zTree.removeNode(parent);
                removeParentNode(parent);
            }
        }else{
            zTree.removeNode(vmNode);
        }


    }

    //删除父节点
    var removeParentNode = function(node){
        var parent = node.getParentNode();
        var childrenNum = parent.children.length;
        if(0 == childrenNum){
            zTree.removeNode(parent);
            removeParentNode(parent);
        }else{
            return;
        }

    }

    // 渲染表格
    var handleRecords = function (vmuuid, taskuuid, storage_uuid) {
        agent_uuid = vmuuid;
        task_uuid = taskuuid;
        let params = {
            agent_uuid : vmuuid,
            task_uuid: taskuuid,
            storage_uuid: storage_uuid
        };
        if(!gridInitFlag){
            //初始化表格
            let options = {
                vin_url: '/api/v1/recovery/instantaneous/points',
                vin_method: 'GET',
                queryParamsType: 'limit',
                queryParams: function (p) {
                    return {
                        limit: p.limit,
                        offset: p.offset,
                        order: p.order,
                        sort: p.sort,
                        task_uuid: params.task_uuid,
                        agent_uuid: params.agent_uuid,
                        storage_uuid: params.storage_uuid,
                        point_type: 'agent'
                    }
                },
                pagination: true,
                sidePagination: 'server',
                pageNumber: 1,
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                paginationLoop: false,
                uniqueId: 'timepoint_uuid',
                lineHeight: '60px',
                changeHeightBtn: true, //改变高度按钮
                batchOperation: true, // 批量操作
                sortable: true,
                sortName: 'timepoint',
                sortOrder: 'desc',
                resizable: true,
                onRefresh: function (params) {
                    grid.bootstrapTable('hideLoading');
                },

                columns: [
                    {
                        field: 'no_html',
                        title: LANG.UI_PUBLIC_TABLE_ID,
                        sortable: false,
                        width: '100px',
                        formatter: function (value, data) {
                            return '<span tid="' + data.timepoint_uuid + '" sid="' + data.storage_uuid + '">' + value + '</span>';
                        }
                    },
                    {
                        field: 'timepoint',
                        title: LANG.UI_COPY_TIMEPOINT,
                        formatter: function (value, data) {
                            return data.timepoint_html;
                        },
                    },
                    {
                        field: 'data_size',
                        title: LANG.UI_COPY_DATA_SIZE,
                    },
                    {
                        field: 'write_size',
                        title: LANG.UI_PUBLIC_REAL_SIZE,
                    },
                    {
                        field: 'storage',
                        title:  LANG.UI_COPY_DATA_STORAGE,
                        formatter: function (value, data) {
                            return data.storage_des;
                        }
                    },
                    {
                        field: 'user_name',
                        title: LANG.UI_DATA_AWS_USER_NAME,
                        formatter: function (value, data) {
                            return data.user_name;
                        }
                    },
                    {
                        field: 'operations',
                        title: LANG.UI_PUBLIC_OPERATION,
                        sortable: false,
                        clickToSelect: false,
                        events: {
                            'click .delete': function (event, value, row, index) {
                                deletePoint(row);
                            },
                        },
                        formatter: function (value, data, index) {
                            return '<div class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i></a></div>';
                        },
                    }
                ]
            };
            grid.baseTableConfig().init(options);
            gridInitFlag = true;
            $('#tabletips').hide();
            $('#vmtable').show();
            $('.remarktips').popover();	   //初始化tips
        }else{
            //刷新表格
            grid.bootstrapTable('refreshOptions', {
                queryParams: function (p) {
                    return {
                        limit: p.limit,
                        offset: p.offset,
                        order: p.order,
                        sort: p.sort,
                        task_uuid: params.task_uuid,
                        agent_uuid: params.agent_uuid,
                        storage_uuid: params.storage_uuid
                    }
                }
            });
        }
    }

    //初始化日期选择插件
    var inintDatatimePicker = function(){
        //初始化日期时间选择控件
        $('#daterangepickerVmData').daterangepicker({
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
        $('#daterangepickerVmData').on('apply.daterangepicker', function(ev, picker) {
            //给全局变量赋值,然后设置input
            _daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_range = picker.chosenLabel;
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
        });

        $('#daterangepickerVmData').on('cancel.daterangepicker', function(ev, picker) {
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

    //初始化当前用户密码用于删除二次确认
    var initUserPassword = function(){
        $.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
            var data = JSON.parse(d);
            _UserPassword = data.password;
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            initStorageShowType(); //初始化存储展示方式
            inintDatatimePicker();    //初始化时间
            initTree();
            initListener();
            initUserPassword();
        }
    };
}();

jQuery(document).ready(function() {
    InstantaneousData.init();
});