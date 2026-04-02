var RecoverTimepoint = function () {
    var pointtypetree, pointtypetreeInitFlag = false, vmTypetreeInitFlag = false;
    var currentTree; //当前展示的树
    var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
    var nodeParamList;
    var moreChoose = false; // 是否支持多选
    var instantaneous = false; // 是否包含瞬时恢复快照点
    var subModuleType = 1; // 区分是虚拟化还是私有云或者公有云  1 ，2 ，3
    var mplimit = 2; //存储库每页显示的数量
    var tplimit  = 2 ; //时间点每页显示的数量
    var cbrselectnode  = []; //华为CBR选择的数据
    var newtimepoint = ""; //后端返回的假的时间点
    var treeshowType  = null;
    var storagetypetreeInitFlag = false;
    var showType = 1; //展示方式
    var storage_type; //恢复源存储类型
    var jobType = 1;// 任务类型 默认是跨平台恢复 2瞬时恢复 4细粒度恢复
    const limit = 20; // 每次加载的条数
    var keywordCache; // 缓存搜索关键词
    var selectedTimepoint = []; // 保存所有已选的时间点

    // 从备份数据跳转恢复页面
    const externalPointUuid = $('#externalPointUuid').val();
    const externalTaskUuid = $('#externalTaskUuid').val();
    const externalItemUuid = $('#externalItemUuid').val();
    const externalSubType = $('#externalSubType').val();

    //事件监听
    var initListener = function(){
        //跳转到虚拟机备份任务
        $('#tobackup').on('click',function(){
            var url = './content/vm/vmbackup.php';
            var naviname = 'vmprotect';
            if (subModuleType == 2) {
                // 私有云
                url = './content/vm/vmbackup.php?sub_module_type=2';
                naviname = 'prcloud_protect';
            } else if (subModuleType == 3) {
                // 公有云
                url = './content/aws/awsbackup.php';
                naviname = 'awsprotect';
            }
            LOCATION(url, naviname);
        });
    }

    //初始化存储类型展示方式和事件
    var initStorageShowType =  function(){
        pAjaxRequest({}, "/api/v1/storages/type", "GET", function (d) {
            var data = d.data;
            var storageselect = $('#storageselect');
            storageselect.empty();
            for(var i=0; i<data.length; i++){
                if ($.inArray(jobType,[2, 4]) !== -1 && $.inArray(data[i].storagetype,[9, 10]) !== -1) {
                    // 细粒度和瞬时恢复屏蔽云存储和磁带
                    continue;
                }
                // 跨平台屏蔽磁带
                if (jobType == 1 && data[i].storagetype == 10) {
                    continue;
                }
                var option = $("<option>").text(data[i].text).val(data[i].storageid).attr("type",data[i].storagetype);
                storageselect.append(option);
            }
        }, false);
        //绑定事件
        treeshowType = $('#storageselect option:selected').attr('type');
        $('#storageselect').on('change', storageShowTypeChange); //存储类型改变事件
        // $('#searchname').on('propertychange', searchVM).on('input', searchVM);
        $('#searchname').on('keydown', (event) => {
            if (event.key === 'Enter' || event.keyCode === 13) {
                searchVM();
            }
        });
    }
    //存储类型改变事件
    var storageShowTypeChange =  function(){
        $('#searchname').val('');
        var type  = $('#storageselect option:selected').attr('type');
        treeshowType  = type;
        //如果是华为CBR 则初始化华为CBR的树
        if(12 == type){
            //隐藏虚拟机的
            //显示CBR的
            $('#showGroupList').hide();
            $('#cbrTimeGroupList').show();
            initStoragedCBRTree();
            //清空已经选择的网络传输模式
            $('#transport_mode').val('nbd');
        }else{
            $('#showGroupList').show();
            $('#cbrTimeGroupList').hide();
            initStorageTree();
            newtimepoint = ""; //创建的新的时间点清空
        }
        currentTree.checkAllNodes(false); // 清空树的已选时间点
        $('#showGroupList li').remove();
        $('#cbrTimeGroupList li').remove();
        if (12 == type) {
            var length = $('#cbrTimeGroupList>li').size();
            var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
            $('.addTitle > span').html(timestr);
        }else{
            var timestr = LANG.UI_VM_SELECTED_TIME_POINT;
            $('.addTitle > span').html(timestr);
        }
    };
    //初始化存储树
    var initStorageTree =  function(loadMoreFlag = false, offset = 0, taskNodeId = '', taskUuid = '', keyword = '', externalFlag = false){
        let p = {};
        p._rows = true;
        p.show_type = 1;//按虚拟机
        p.storage_uuid = $('#storageselect').val(); // 按存储筛选
        p.manage_flag = false; //得到备份数据管理,获取checkbox
        p.data_flag = false; //备份数据标志
        p.sub_module_type = subModuleType;
        p.instant_flag = instantaneous;
        p.notcloud = jobType != 1;
        p.nottape = true;
        p.keyword = keyword;
        if (!externalFlag) {
            p.loadmore_offset = offset;
            p.loadmore_limit = limit;
        }
        if (loadMoreFlag) {
            p.job_uuid = taskUuid;
        }
        let div = ".src-wrap__content";
        Metronic.blockUI({target: div,animate: true});
        pAjaxRequest(p, "/api/v1/vm/restore_data", "GET", function (d) {
            Metronic.unblockUI(div);
            if (loadMoreFlag) {
                // 获取任务所在树节点
                let treeNode = currentTree.getNodeByParam('id', taskNodeId);
                // 删除已有的【加载更多】节点
                currentTree.removeNode(currentTree.getNodeByParam('id', `${taskNodeId}_loadMore`));
                // 添加虚拟机的节点
                currentTree.addNodes(treeNode, d.data.rows, true);
            } else {
                // 初次加载
                setStorageTree(d.data.rows);
            }
        }, true);
    };
    //搜索虚拟机
    var searchVM = function(){
        var value = $('#searchname').val();
        // 输入验证
        if(!customInputValidate('string',value)){
            return false;
        }
        keywordCache = value;
        // 通过接口搜索
        initStorageTree(false, 0, '', '', value);
        // 前端搜索
        /*var value = $('#searchname').val();
        var nodes = currentTree.getNodes();
        if(!nodes || nodes.length == 0) return;
        var checkNode =currentTree.getCheckedNodes();
        var allNode = currentTree.transformToArray(currentTree.getNodes());
        nodeParamList = currentTree.getNodesByParamFuzzy('name', value);
        if(nodeParamList.length!=0){
            currentTree.hideNodes(allNode);
            $('.two_tree').show();
            $('#nosearchtips').hide();
        }else{
            $('.two_tree').hide();
            $('#nosearchtips').show();
        }
        //连接搜索的和所勾选的
        nodeParamList =nodeParamList.concat(checkNode);
        var nodeParamList1 = currentTree.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
            findParent(currentTree,nodeParamList1[n], value);
        }
        currentTree.showNodes(nodeParamList);*/
    }
    //找到父节点
    var findParent = function(treeObj,node, value){
        if(value == "" && node.type != -1){
            currentTree.expandNode(node,false,false,false);
        }else{
            currentTree.expandNode(node,true,false,false);
        }
        if(!node.children){
            currentTree.expandNode(node,false,false,false);
        }
        if(!node.isParent || node.type == 1){
            nodeParamList.push(node);
        }
        var pNode = node.getParentNode();
        if(pNode != null){
            nodeParamList.push(pNode);
            findParent(currentTree,pNode, value);
        }
    }
    //初始化华为CBR存储树
    var initStoragedCBRTree =  function(){
        var data  = {};
        data.type =  3;
        data.backupflag  =  true;
        data.editflag =  false;
        data.storageuuid =  $("#storageselect").val();
        data =  JSON.stringify(data);
        Metronic.blockUI({target: '.two_tree',animate: true});
        $.post(CONF.AJAXPATH,{m:CONF.M.VM,f:'getCBRDetailsTreeNew',p:data},setCBRTree);
    }
    //设置存储的树
    var setStorageTree = function(zNodes){
        Metronic.unblockUI('.two_tree');
        if(!checkTreeNodeInfo(zNodes)) return;
        var setting = {
            check: {
                enable: true,
                chkboxType: {
                    "Y": "", // 勾选时不联动
                    "N": ""  // 取消勾选时不联动
                },
                nocheckInherit: false
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
                onCheck: timepointOnCheckValidate,
                beforeExpand: nodeExpand
            },
            view: {
                addHoverDom: addHoverDom,
                removeHoverDom: removeHoverDom,
                showTitle: true,
                nameIsHTML:true,
                fontCss: (treeId, treeNode) => {
                    let style = {};
                    if (treeNode.type === 3 || treeNode.type === 4) {
                        if (!treeNode.timepoint_status.avaliable_flag) {  // 操作中
                            style = {'color': '#F19F00!important'};
                        } else if (treeNode.timepoint_status.avaliable_flag) {
                            if (parseInt(treeNode.timepoint_status.status) === 3) {
                                style = {'color': '#F1416C!important'};
                            }
                        }
                    }
                    return style;
                },
            }
        };
        pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, zNodes);
        // 从备份数据跳转恢复页面，展开对象下的时间点
        let targetNode = pointtypetree.getNodeByParam('id', externalSubType + externalItemUuid + externalTaskUuid);
        // 第一次初始化，且有目标节点
        if(targetNode && !storagetypetreeInitFlag){
            // 异步获取时间点
            getSyncVcenterInfo('pointtypetree',targetNode, true, true, true)
        }
        storagetypetreeInitFlag = true;
        currentTree = pointtypetree;
    };

    // 树hover的时候
    const addHoverDom = function(treeId, treeNode) {
        const btnId = 'diyBtn_' + treeNode.tId;
        var btn = $("#diyBtn_"+treeNode.tId);
        if (btn.length > 0 || !(treeNode.type == 3 || treeNode.type == 4)){
            return;
        }
        var aObj = $("#" + treeNode.tId + "_a");
        var editStr =  "<i class='viconfont vicon-Frame11' title='"+LANG.UI_BACKUP_DATA_POINT_DETAIL_TITLE+"' id='diyBtn_" + treeNode.tId + "'></i>";
        aObj.append(editStr);

        // 点击打开抽屉
        aObj.off('click', '#' + btnId).on('click', '#' + btnId, function(e) {
            e.stopPropagation();
            $('.page-content').initPointDetailDrawer({ timepoint_uuid: treeNode.timepoint_uuid });
        });
    };
    // 失去 hover 的时候
    const removeHoverDom = function(treeId, treeNode) {
        $("#diyBtn_" +treeNode.tId).remove();
    };

    //选择时间点节点事件绑定
    var nodeSelect = function(treeId, treeNode, clickFlag){
        if(4 == treeNode.type){
            // 增备点或差异
            pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
        }else if(3 == treeNode.type){
            // 完备点
            pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
            pointtypetree.expandNode(treeNode, true)
        }else{
            if (1 === treeNode.moreType) {
                // 加载更多虚拟机
                initStorageTree(true, treeNode.nextOffset, treeNode.pId, treeNode.task_uuid);
            } else if (3 === treeNode.moreType || 4 === treeNode.moreType) {
                // 加载更多时间点
                let parentNode = currentTree.getNodeByParam('id', treeNode.pId);
                let parentUuid = 3 === treeNode.moreType ? treeNode.vm_uuid : treeNode.pId;
                getSyncVcenterInfo(treeId, parentNode, false, false, false, true, treeNode.nextOffset, parentUuid);
            } else {
                pointtypetree.expandNode(treeNode, true)
            }
        }
        nodeExpand(treeId, treeNode);
    }
    //异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
    var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag, chooseFlag = false, loadMoreFlag = false, offset = 0, parentUuid = ''){
        var nodeuuid = $('#nodeselect').val();
        var storageuuid = $('#storageselect').val();
        if (null === nodeuuid) {
            nodeuuid = '';
        }
        var div = ".src-wrap__content";
        let p = {
            task_uuid: treeNode.task_uuid,
            vm_uuid: treeNode.vm_uuid,
            hypervisor_type: treeNode.hypervisor_type,
            disabled_flag: false, //备份数据禁用勾选增量差异标志
            manage_flag: false,
            vm_check: treeNode.checked,
            node_uuid: nodeuuid,
            storage_uuid: storageuuid,
            instant_flag: instantaneous,
            notcloud: jobType != 1,
            nottape: true,
            parent_uuid: parentUuid,
        };
        let keyword = keywordCache;
        if (keyword) {
            p.keyword = keyword;
            // 按关键词搜索时一次性加载
            loadMoreFlag = false;
        }
        if (loadMoreFlag) {
            p.loadmore_offset = offset;
            p.loadmore_limit = limit;
            p.parent_uuid = parentUuid;
        }
        Metronic.blockUI({target: div,animate: true});
        pAjaxRequest(p, "/api/v1/vm/restore_data/restore_points", "GET", function (d) {
            Metronic.unblockUI(div);
            if (d.success) {
                //success
                if (0 === offset) {
                    // 初次展开获取时需先清空
                    $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                }
                if (loadMoreFlag) {
                    // 删除已有的【加载更多】节点
                    currentTree.removeNode(currentTree.getNodeByParam('id', `${treeNode.id}_loadMore`));
                    // 在每个时间点节点的父节点下面添加
                    d.data.rows.forEach(newNode => {
                        // 如果是已选的要勾选上
                        selectedTimepoint.forEach(item => {
                            if (item.timepoint_uuid === newNode.timepoint_uuid) {
                                newNode.checked = true;
                            }
                        });
                        let parentNode = currentTree.getNodeByParam('id', newNode.pId);
                        currentTree.addNodes(parentNode, newNode, true);
                        if (expendFlag === true) {
                            currentTree.expandNode(parentNode, true, true, true);
                        }
                    });
                } else {
                    // 原有的一次性加载
                    d.data.rows.forEach(newNode => {
                        // 如果是已选的要勾选上
                        selectedTimepoint.forEach(item => {
                            if (item.timepoint_uuid === newNode.timepoint_uuid) {
                                newNode.checked = true;
                            }
                        });
                    });
                    $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
                    if (expendFlag === true) {
                        $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                    }
                }

                // 从备份数据跳转恢复页面，匹配时间点并勾选
                let targetNode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam('id', externalPointUuid);
                if(targetNode && chooseFlag){
                    // 添加到缓存
                    selectedTimepoint.push(targetNode);
                    $.fn.zTree.getZTreeObj(treeId).checkNode(targetNode, true, false, false);
                    // 时间点备份的展开文件列表
                    addPointList(treeId, targetNode);
                }
            } else {
                operateResponseList(d);
            }
        }, true);
    }

    const timepointOnCheckValidate = function (e, id, node) {
        if (node.checked && node.config.password && 2 == node.config.password_auto_flag) {
            timepointOnChecks(e, id, node);
            // 加密的时间点需弹窗输入密码
            bootbox.prompt({
                title: LANG.UI_VM_INPUT_DB_ENCRY_PWD,
                inputType: 'password',
                callback: function (pwdResult) {
                    if (pwdResult == null) {
                        // 取消输入则取消勾选时间点
                        $.fn.zTree.getZTreeObj(id).checkNode(node, false, false, false);
                        return;
                    }
                    //获取密码
                    let requestList = {
                        'timepoint_uuid': node.timepoint_uuid,
                        'password': btoa(pwdResult),
                    };
                    let checkFlag = false;
                    //获取密码是否正确
                    var requestData = function (data) {
                        if (data.success) {
                            checkFlag = true;
                            timepointOnCheck(e, id, node);
                            UIToastr.showSuccess(LANG.UI_PLATFORM_RECOVERY_TIMEPOINT_PASS, data.message);
                        } else {
                            UIToastr.showWarning(LANG.UI_PLATFORM_RECOVERY_TIMEPOINT_PASS, data.message);
                        }
                    }
                    pAjaxRequest(requestList, '/api/v1/jobs/password_check', "GET", requestData, false);
                    return checkFlag;
                }
            });
        } else {
            timepointOnCheck(e, id, node);
        }
    }

    //虚拟机分组
    var timepointOnCheck = function(e, id, node){
        storage_type = node.storage_type;
        var type  = $('#storageselect option:selected').attr('type');
        if (moreChoose) {
            // 支持多选
            checkHypervisorPoint(id, node);
            //磁带需求，磁带备份点和非磁带备份点互斥，不可同时选择
            if (!checkStorageTypePoint(id, node)) {
                return;
            }
            var flag = node.checked;
            var allNodes = pointtypetree.getCheckedNodes(true);
            if(type != 12){
                // 先统一移除再添加
                selectedTimepoint = selectedTimepoint.filter(item => item.timepoint_uuid !== node.timepoint_uuid);
                if (flag) {
                    selectedTimepoint.push(node);
                }
                allNodes = selectedTimepoint;
                if(!checkSelectInOneNode(flag, pointtypetree, node, allNodes, false)){
                    $('#showGroupList li').remove();
                    $('#cbrTimeGroupList li').remove();
                    addPointList(id, node, showType);
                    return;
                }
                for(var i = 0; i < allNodes.length; i++){
                    if(allNodes[i].vm_uuid == node.vm_uuid && allNodes[i].platform_uuid == node.platform_uuid){
                        var liId = clearString(id+allNodes[i].platform_uuid + allNodes[i].id + allNodes[i].vm_uuid); //添加虚拟机每列ID
                        //如果虚拟机一样的话,就要取消之前所有的
                        // 不能同时选磁带存储和其他存储的备份点
                        pointtypetree.checkNode(allNodes[i], false, false, false);
                        $('#' + escapeJquery(liId)).remove();
                    }
                }
                pointtypetree.checkNode(node, flag, false, false);
                addPointList(id, node, showType);
            }else{
                //华为CBR
                if(!checkSelectInOneNode(flag, pointtypetree, node, allNodes, true)){
                    $('#showGroupList li').remove();
                    $('#cbrTimeGroupList li').remove();
                    if(node.type == 3 && node.checked){
                        addPointList(id, node);
                    }
                    return;
                }
                //如果选中时间点 取消他的所有邻居节点
                if(flag){
                    var parentNode = node.getParentNode();
                    var broNodes = pointtypetree.getNodesByParam('type', 3, parentNode);
                    for(var i=0; i<broNodes.length; i++){
                        if(broNodes[i].vmuuid == node.vmuuid){
                            pointtypetree.checkNode(broNodes[i], false, false, false);
                            var liId = clearString(id+broNodes[i].vcenteruuid + broNodes[i].id + broNodes[i].vmuuid); //添加虚拟机每列ID
                            $('#' + escapeJquery(liId)).remove();
                        }
                    }
                }
                pointtypetree.checkNode(node, flag, false, false);
                addPointList(id, node);
            }
        } else {
            // 单选
            var allNodes = pointtypetree.getCheckedNodes(true);
            for(var i = 0; i < allNodes.length; i++){
                pointtypetree.checkNode(allNodes[i], false, false, false);
            }
            pointtypetree.checkNode(node, true, false, false);

            addPointList(id, node);
        }
    }

    //虚拟机分组
    var timepointOnChecks = function(e, id, node){
        storage_type = node.storage_type;
        var type  = $('#storageselect option:selected').attr('type');
        if (moreChoose) {
            // 支持多选
            checkHypervisorPoint(id, node);
            //磁带需求，磁带备份点和非磁带备份点互斥，不可同时选择
            if (!checkStorageTypePoint(id, node)) {
                return;
            }

            var flag = node.checked;
            var allNodes = pointtypetree.getCheckedNodes(true);
            if(type != 12){
                allNodes = selectedTimepoint;
                for(var i = 0; i < allNodes.length; i++){
                    if(allNodes[i].vm_uuid == node.vm_uuid && allNodes[i].platform_uuid == node.platform_uuid){
                        var liId = clearString(id+allNodes[i].platform_uuid + allNodes[i].id + allNodes[i].vm_uuid); //添加虚拟机每列ID
                        //如果虚拟机一样的话,就要取消之前所有的
                        // 不能同时选磁带存储和其他存储的备份点
                        pointtypetree.checkNode(allNodes[i], false, false, false);
                        selectedTimepoint = selectedTimepoint.filter(item => item.timepoint_uuid !== allNodes[i].timepoint_uuid);
                        $('#' + escapeJquery(liId)).remove();
                    }
                }
                pointtypetree.checkNode(node, flag, false, false);
            }else{
                //华为CBR
                //如果选中时间点 取消他的所有邻居节点
                if(flag){
                    var parentNode = node.getParentNode();
                    var broNodes = pointtypetree.getNodesByParam('type', 3, parentNode);
                    for(var i=0; i<broNodes.length; i++){
                        if(broNodes[i].vmuuid == node.vmuuid){
                            pointtypetree.checkNode(broNodes[i], false, false, false);
                            var liId = clearString(id+broNodes[i].vcenteruuid + broNodes[i].id + broNodes[i].vmuuid); //添加虚拟机每列ID
                            $('#' + escapeJquery(liId)).remove();
                        }
                    }
                }
                pointtypetree.checkNode(node, flag, false, false);
            }
        } else {
            // 单选
            var allNodes = pointtypetree.getCheckedNodes(true);
            for(var i = 0; i < allNodes.length; i++){
                pointtypetree.checkNode(allNodes[i], false, false, false);
            }
            pointtypetree.checkNode(node, true, false, false);
        }
    }

    //判断是否在一个备份节点上
    var checkSelectInOneNode = function(flag, tree, node, allNodes, checkTypeFlag){
        if(!flag) return true;
//		var showType = showType;
        for (var i = 0; i < allNodes.length; i++) {
            if (allNodes[i].node_uuid != node.node_uuid) {
                UIToastr.showInfo(LANG.UI_RECOVERY_POINT_NOT_IN_ONE_NODE, LANG.UI_RECOVERY_POINT_NOT_IN_ONE_NODE_TIPS);
                for (var j = 0; j < allNodes.length; j++) {
                    tree.checkNode(allNodes[j], false, false, false);
                    selectedTimepoint = selectedTimepoint.filter(item => item.timepoint_uuid !== allNodes[j].timepoint_uuid);
                }
                tree.checkNode(node, flag, checkTypeFlag, false);
                selectedTimepoint.push(node);
                return false;
            }
        }
        return true;
    }
    //检测选择的点时候是同一个虚拟化中心,如果不是,需要把之前的所有点都取消选择
    var checkHypervisorPoint = function(treeId, node){
        // 跨平台不需要限制，瞬时恢复只能选择一个点，更不用限制
        return;
        var tree = $.fn.zTree.getZTreeObj(treeId);
        var allNodes = tree.getCheckedNodes(true);
        for(var i = 0; i < allNodes.length; i++){
            if(allNodes[i].hypervisor_type != node.hypervisor_type){
                tree.checkAllNodes(false);
                tree.checkNode(node, !node.checked, false, false);
                $('#showGroupList li').remove();
                return;
            }
        }
    }
    //检测选择的点是否都是磁带备份点或非磁带备份点（互斥）,如果不是,需要把之前的所有点都取消选择
    var checkStorageTypePoint = function(treeId, node){
        var tree = $.fn.zTree.getZTreeObj(treeId);
        // var allNodes = tree.getCheckedNodes(true);
        var allNodes = selectedTimepoint;
        var selected_storage_type_list = [];
        for(var i = 0; i < allNodes.length; i++){
            selected_storage_type_list.push(parseInt(allNodes[i].storage_type))
        }
        let uniqueStorageTypes = [...new Set(selected_storage_type_list)];
        if(uniqueStorageTypes.length > 1 && uniqueStorageTypes.indexOf(CONF.BD_STORAGE_TYPE.TAPE) != -1){
            bootbox.confirm({
                title: LANG.UI_VERIFY_SELECT_TIMEPOINT,
                message: LANG.UI_CHOOSE_TIMEPOINT_TAPE_TIPS, // 例如："该操作需要密码验证，请确认继续？"
                buttons: {
                    confirm: {
                        label: LANG.UI_PUBLIC_CONFIRM,
                        className: 'btn-primary'
                    },
                    cancel: {
                        label: LANG.UI_PUBLIC_CANCEL,
                        className: 'btn-secondary'
                    }
                },
                callback: debounce(function (result) {
                    if (result) {
                        // 用户点击了“确定”, 取消之前勾选的时间点
                        tree.checkAllNodes(false);
                        $('#showGroupList li').remove();
                        selectedTimepoint = [];
                        //勾选当前时间点
                        tree.checkNode(node, true, false, false);
                        addPointList(treeId, node);
                        $(this).modal('hide');
                        selectedTimepoint.push(node);
                        return true;
                    } else {
                        // 用户点击了“取消”
                        tree.checkNode(node, false, false, false);
                        addPointList(treeId, node);
                        return false;
                    }
                }, 300, false)
            });
        }
        return true;
    }
    //节点展开异步添加时间点
    var nodeExpand = function(treeId, treeNode){
        if(treeNode.clickshow){
            if(treeNode.children) return true;
            getSyncVcenterInfo(treeId, treeNode, false, false, false, true, 0, treeNode.vm_uuid);
        }else{
            if (3 === treeNode.type && !treeNode.loadChildren && !keywordCache) {
                // 加载完备点下的其他点
                getSyncVcenterInfo(treeId, treeNode, false, false, false, true, 0, treeNode.id);
                treeNode.loadChildren = true;
            } else {
                return true;
            }
        }
    }
    //设置CBR的树
    var setCBRTree =  function(zNodes){
        Metronic.unblockUI('.two_tree');
        if(!checkTreeNodeInfo(zNodes)) return;
        var setting = {
            check:{
                enable:true,
                nocheckInherit:false //自动继承父节点 nocheck = true 的属性。
            },
            data:{
                simpleData:{
                    enable:true,
                    idKey:"id",
                    pIdKey:"pid",
                    rootPId: 0
                },
                key: {
                    title: "title"
                }
            },
            view: {
                // fontCss: getFontCss,
                addDiyDom: addcbrHoverDom,
                addHoverDom: addHoverDom,
                removeHoverDom: removeHoverDom,
            },
            callback:{
                beforeClick: cbrnodeSelect, //根据返回值是否允许单机操作
                onCheck: cbrOnCheck, //勾选或者取消勾选的事件回调函数
                onExpand:cbrnodeExpand //捕获节点展开事件的回调函数
            }
        };
        var nodes = JSON.parse(zNodes);//拿到备份节点
        pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, nodes);
        for(var i=0;i<nodes.length;i++){
            if(nodes[i].checked){
                var checkNode = zTreeTP.getNodesByParam("id", nodes[i].id, null);
                addMPList('cbr_tree_tp',checkNode[0]);
            }
        }
        currentTree = pointtypetree;
    }
    //检查节点信息
    var checkTreeNodeInfo = function(zNodes){
        if(zNodes == "[]" || zNodes.length == 0){
            $("#nopointtips").show();
            $('.vcenter-tree').hide();
            $('#vmtypetree').hide();
            $('#pointtypetree').hide();
            // $('#pointshowtype').prop('disabled', true);
            return false;
        }else{
            $("#nopointtips").hide();
            $('.vcenter-tree').show();
            $('#pointtypetree').show();
            $('#vmtypetree').hide();
            $("#two_tree").show();
            // $('#pointshowtype').prop('disabled', false);
            return true;
        }
    }
    //添加云上数据鼠标指上去事件
    var addcbrHoverDom  = function(treeId, treeNode){
        var nodeID = escapeJquery(treeNode.id);
        var nodeTID = escapeJquery(treeNode.tId);
        if(treeNode.pid == 1){
            var aObj = $("#" + nodeTID + "_a"); //获取节点DOM
            var expandedstr  =
                '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_BACKUP_TREE_REFRESH_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>'+
                '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' +
                '<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
            aObj.after(expandedstr);
            var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
            var hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
            var hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");
            //点击刷新
            if (hrefRefresh) hrefRefresh.bind("click", function(){
                getSyncCBRInfo(treeId, treeNode, true, false);
            });
            //点击全部展开
            if (hrefExpand) hrefExpand.bind("click", function(){
                cbrnodeExpand(event,treeId,treeNode,true);
            });
            //点击收起
            if (hrefCollapse) hrefCollapse.bind("click", function(event){
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true);
            });
        }
    }
    //CBR节点选择
    var cbrnodeSelect = function(treeId, treeNode, clickFlag){
        //如果是是名字 存储区域 直接展开
        //如果是项目 异步获取存储库那一层
        //存储库 获取虚拟机
        //虚拟机 获取时间点
        //如果是是存储库 获取虚拟机
        if('project' ==  treeNode.eventtype){
            var children = treeNode.children;
            if(!children || !clickFlag){ //没有孩子或者是没勾选
                //如果当前树是时间点 则不需要获取虚拟机
                getMPSync(treeId,treeNode,false);
            }else{
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开
            }
        }else if ('mp' == treeNode.eventtype || 'vm' == treeNode.eventtype || 'tp' ==  treeNode.eventtype){
            if('mp' == treeNode.eventtype){
                // console.log("mp select",clickFlag);
                var children = treeNode.children;
                if(!children || !clickFlag){
                    //如果是时间点 需要获取时间点
                    //存储库和项目都可以勾选
                    treeNode.nocheck = false;
                    var projectnode  = treeNode.getParentNode();
                    projectnode.nocheck  =  false;
                    getTPSync(treeId,treeNode);
                }else{
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开
                }
            }
            if('vm' == treeNode.eventtype ){
                var children = treeNode.children;
                if(!children || !clickFlag){
                    //如果当前是时间点 则异步加载
                    treeNode.nocheck = false; //虚拟机
                    var storagenode  = treeNode.getParentNode();//存储库
                    var projectnode  = storagenode.getParentNode();//项目
                    storagenode.nocheck  =  false;
                    projectnode.nocheck =  false
                    getTPSync(treeId,treeNode);
                }else{
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开
                }
            }
            if('tp' == treeNode.eventtype){
                //这里需要判断
                //  if(treeNode.checked){
                // 	var liId = treeId+ treeNode.id;
                // 	$('#' + escapeJquery(liId)).remove();
                // 	var length = $('#cbrTimeGroupList>li').size();
                // 	var timestr = "已选择时间点（"+length+"个）";
                // 	$('.addTitle > span').html(timestr);
                //  }
                var  flag  = nodeInSameVm(treeId,treeNode);
                if(flag && treeNode.getCheckStatus()){
                    UIToastr.showInfo(LANG.UI_VM_TIME_POINT_SOURCE_SAME, LANG.UI_VM_TIME_POINT_SOURCE_SAME_TIPS);
                }
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
            }
        }else if ('mploadmore' == treeNode.eventtype){
            //获取项目node
            var projectnode =  treeNode.getParentNode();
            getMPSync(treeId,projectnode,false); //不需要获取虚拟机那一层
        }else if('tploadmore'== treeNode.eventtype){
            //获取存储库node
            var storagenode =  treeNode.getParentNode()
            getTPSync(treeId,storagenode); //需要获取时间点那一层
        }else{
            //前面几层只展开
            if(!treeNode.isParent) return;//不存在子节点不展开
            // nodeExpand(treeId, treeNode);
            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
        }
    }
    //勾选添加存储库显示列表
    var addMPList = function(id,node){
        var info = "";
        var liId = id+node.id;
        //需要获取node的路径
        node.path = getnodepath(node,"");
        if(node.checked){
            //同一个存储库下的时间点只能选择一个
            //根据CBR同步类型添加每一列到列表
            //如果是选择时间点 需要展示两行
            info +=
                '<li class="list-group-item popovers VMTips list-group-item__recoverlist" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ decodeURIComponent(node.path) +'">' +
                '<div class="col1">' +
                '<div class="cont vmDetail">' +
                '<div class="cont-col1">' +
                '<div style="width:20px;height:20px;background: url(./img/vm/vm.png) 0 no-repeat;"></div>' +
                '</div>' +
                '<div class="cont-col2">' +
                '<div class="desc list-one" style="font-size: 14px;color: #333;padding: 9px 4px 0px 4px">' + node.parentname + '</div>' +
                '<div class="desc list-one" style="font-size: 12px;color: #666;padding: 4px 4px 0 4px">' +  node.name + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col2  pull-right delete-list">' +
                '<a class="del'+liId+'" >' +
                '<div class="label label-sm label-danger" style="padding:0;">' +
                '<i class="viconfont vicon-guanbi"></i>' +
                '</div>' +
                '</a>' +
                '</div>' +
                '</li>';
            $('#cbrTimeGroupList').append(info);
            var length = $('#cbrTimeGroupList>li').size();
            var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
            $('.addTitle > span').html(timestr);
            $('#' + escapeJquery(liId)).popover();	   //初始化tips
            //移除存储池显示
            $('.del'+escapeJquery(liId)).on('click', function(){
                var treeObj = $.fn.zTree.getZTreeObj(id);
                $('.popover.in').remove();
                treeObj.checkNode(node,false,true);
                $('#' + escapeJquery(liId)).remove();
                var length = $('#cbrTimeGroupList>li').size();
                var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
                $('.addTitle > span').html(timestr);
            });
        }else{
            //检查node.id是否在虚拟化里面，如果在就要把对应的li移除
            $('#' + escapeJquery(liId)).remove();
            var length = $('#cbrTimeGroupList>li').size();
            var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
            $('.addTitle > span').html(timestr);
        }
    }
    //获取节点路径 从存储开始
    var getnodepath =  function(node,nodepath){
        var nodename =  node.name;
        var parentNode =  node.getParentNode();
        if(node.pid !=  1){
            nodepath = "/" + nodename + nodepath;
            //如果是时间点 需要加上虚拟机这一层
            if(node.eventtype == "tp"){
                nodepath  = "/" + node.parentname + nodepath;
            }
            return getnodepath(parentNode,nodepath);
        }else{
            nodepath = nodename + nodepath;
            return nodepath;
        }
    }
    //CBR节点勾选
    var cbrOnCheck = function(e, id, node){
        var tree = $.fn.zTree.getZTreeObj(id);
        var allNodes = tree.getCheckedNodes(true);
        var flag  = false;
        var flag1 = false;
        var flagall =  false; //判断所有时间点是否有相同的虚拟机标志
        if(allNodes.length != 0){
            flag = existstorage(allNodes);
            if(flag){
                tree.checkAllNodes(false);
                tree.checkNode(node, !node.checked, true, false);
                UIToastr.showInfo(LANG.UI_VM_SELECT_STORAGE_DIFFERENT,LANG.UI_VM_SELECT_STORAGE_DIFFERENT_TIPS);
                moveRightAll(id); //清除右边所有的
            }
            if(node.eventtype == "project" || node.eventtype == "mp"){
                //需要判断该项目或者存储下的时间点是否都是同一个虚拟机
                flagall =  existsamevm(id,allNodes);
                if(flagall){
                    UIToastr.showInfo(LANG.UI_VM_SELECT_TIME_SOURCE_SAME,LANG.UI_VM_TIME_POINT_SOURCE_SAME_TIPS);
                    return
                }
            }
            if(node.eventtype == "tp"){
                flag1  = nodeInSameVm(id,node);
                if(flag1){
                    if(!flagall){
                        UIToastr.showInfo(LANG.UI_VM_TIME_POINT_SOURCE_SAME,LANG.UI_VM_TIME_POINT_SOURCE_SAME_TIPS);
                    }

                }
                addMPList(id,node);
            }
        }
        else{
            $('.addVMList').empty();
            var length = $('#cbrTimeGroupList>li').size();
            var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
            $('.addTitle > span').html(timestr);
            var children = node.children;
            if(children){
                addChirdMPList(id, children);
            }
        }
    }
    //异步获取CBR的信息  refreshFlag是否重新刷新
    var getSyncCBRInfo = function(treeId, treeNode, refreshFlag, expendFlag){
        var div = ".two_tree";
        var data = {
            "storage_uuid_list":[{
                "storage_uuid":treeNode.id
            }],
            "type":parseInt($('#asyncshowtype').val()),
            "backupflag":true,
            "editflag":false
        }
        //var showType = parseInt($('#asyncshowtype').val());
        var datastr =  JSON.stringify(data);
        Metronic.blockUI({target: div,animate: true});
        $.ajax({
            type:"post",
            url: CONF.AJAXPATH,
            async:true,
            data:{m:CONF.M.VM,f:'getSyncCBR',p:datastr},
            success:function(d){
                Metronic.unblockUI(div);
                var data  =  JSON.parse(d);
                if(!data.re && data.re != false){
                    // result = JSON.parse(data);
                    //刷新CBRlist
                    refreshCBRList(treeId);
                    //检测是否为同一事件返回
                    if(currentTree != $.fn.zTree.getZTreeObj(treeId)) return;
                    if(data){
                        //success
                        var allNodes = currentTree.getCheckedNodes(true);
                        if(refreshFlag && allNodes!= 0){
                            $('.popover.in').remove();
                            $('.cbrTimeGroupList').empty();
                            currentTree.checkAllNodes(false);
                        }
                        var index = treeNode.getIndex();
                        var storagenode =  treeNode.getParentNode();
                        currentTree.removeNode(treeNode);
                        currentTree.addNodes(storagenode,index,data,true);
                        currentTree.expandNode(treeNode,true);
                        if(expendFlag == true){
                            currentTree.expandNode(treeNode, true, true, true);
                        }
                    }
                }else{
                    OPREL(d);
                }
            }
        })
    }
    //是否存在同一个存储下
    var existstorage =  function(nodes){
        var  existflag =  false;
        var  storagelist = [nodes[0].vcenteruuid];
        for (var i  = 1;i < nodes.length ; i ++) {
            if(nodes[i].eventtype == "project"){
                if(storagelist.indexOf(nodes[i].vcenteruuid) == -1){
                    existflag =  true;
                    break;
                }
            }
        }
        return existflag;
    }
    var nodeInSameVm =  function(id,node){
        var existflag  = false;
        var treeObj = $.fn.zTree.getZTreeObj(id);
        var allNodes = treeObj.getCheckedNodes(true);
        for (let i = 0; i < allNodes.length; i++) {
            if(allNodes[i].eventtype == "tp" && allNodes[i].parentid == node.parentid && allNodes[i].id != node.id){
                treeObj.checkNode(allNodes[i],false,true,false);
                var liId =  id + allNodes[i].id;
                $('#' + escapeJquery(liId)).remove();

                existflag =  true;
            }
        }
        return existflag;
    }
    var existsamevm =  function(id,nodes){
        var tree = $.fn.zTree.getZTreeObj(id);
        var flag =  false;
        var  residlist = [];
        nodes.forEach(node => {
            if(node.eventtype == "tp"){
                if(residlist.indexOf(node.parentid) == -1){
                    //把当前节点勾选上
                    tree.checkNode(node,true, true, false);
                    addMPList(id,node);
                    residlist.push(node.parentid);
                }else{
                    flag =  true;
                    tree.checkNode(node,false, true, false);
                }
            }
        });
        return flag;
    }
    var moveRightAll = function(id){
        $('#cbrTimeGroupList li').remove();
        // $('.diskList').remove();
    }
    //添加cbr时间点到右边列表
    var addChirdMPList =  function(id,children){
        if(!children && children.length == 0) return;
        for (var i = 0; i < children.length; i++) {
            if(children[i].eventtype == "tp"){
                addMPList(id,children[i]);
            }else{
                var childList =  children[i].children;
                if(!childList)  continue;
                addChirdMPList(id,childList);
            }
        }
        return;
    }
    //CBR节点展开
    //flag为true表示点击了展开所有
    var cbrnodeExpand = function(event,treeId, treeNode,flag  =  false){
        // console.log("come in",flag,treeNode);
        if(!treeNode.eventtype){ //如果是前面几层 不存在子节点不展开
            if(!treeNode.isParent) return;
            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
            if(treeNode.children && flag){
                var nodelist =  treeNode.children;
                for (let index = 0; index < nodelist.length; index++) {
                    cbrnodeExpand(event,treeId,nodelist[index],true); //展开所有递归此方法
                }
            }
            // nodeExpand
        }else if(treeNode.eventtype  == "project"){
            //如果不存在子节点 则获取存储库
            var children = treeNode.children;
            if(!children){
                getMPSync(treeId,treeNode,false);
            }
            if(children && flag){
                if(treeNode.nocheck == false){
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,true,true,true,flag);
                }else{
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,false,true,true,flag);
                }
            }
        }else if(treeNode.eventtype  == "mp"){
            //如果不存在子节点 则获取存储库
            var children = treeNode.children;
            if(!children){
                treeNode.nocheck = false;
                var projectnode  = treeNode.getParentNode();
                projectnode.nocheck  =  false;
                //如果是时间点类型，则获取时间点数据 则存储库那一层加可选框 项目那一层也需要加勾选
                getTPSync(treeId,treeNode);
            }
            if(children && flag){
                // console.log("存储库有孩子---");
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,true,true,true,flag);
            }
        }
    }
    //异步获取存储库
    var getMPSync =  function(treeId, treeNode,needresource){
        var div = ".two_tree";
        // var showType = parseInt($('#asyncshowtype').val());
        // var data =  JSON.stringify({id:treeNode.id,type:showType,refresh:true});
        var showType = parseInt(3); //按照时间点方式进行展示
        var region_node =  treeNode.getParentNode(); //区域node
        //var region_id =  region_node.id;
        var storage_node = region_node.getParentNode(); //存储node
        var storage_id = storage_node.id;

        var storageregionid = region_node.id.split("_");
        var region_id  = storageregionid[1];

        var storageprojectid = treeNode.id.split("_");
        var project_id  = storageprojectid[1];

        var nextpage = getcurrentpage(treeId,treeNode); //获取即将要查询的页序号
        // var nextpage =  mpcurpage + 1;
        var jsondata =  {
            "storage_uuid":storage_id,
            "region_id":region_id,
            "project_id":project_id,
            "current_page":nextpage,//即将查询的页序号
            "limit":mplimit, //每页显示的数量
            "showtype":showType,
            "need_resource":needresource
        }
        var datastring  = JSON.stringify(jsondata);
        Metronic.blockUI({target: div,animate: true});
        $.ajax({
            type: "post",
            url: CONF.AJAXPATH,
            async:true,
            data:{m:CONF.M.VM,f:"getCBRMP",p:datastring},
            success: function(d){
                Metronic.unblockUI(div);
                var data  =  JSON.parse(d);
                if(!data.re && data.re != false){
                    // mpcurpage =  data.current_page;
                    // mptotalpage = data.total_pages;
                    $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, data.tree, true);
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                    //如果有加载更多 则需要删除加载更多 再判断是否需要新增加载更多节点
                    //如果没有加载更多 则需要判断是否需要显示加载更多
                    var treeObj = $.fn.zTree.getZTreeObj(treeId);
                    var nodes = treeObj.getNodesByParam("eventtype", "mploadmore", treeNode);
                    if(nodes.length > 0){
                        //需要删除加载更多
                        treeObj.removeNode(nodes[0]);
                    }
                    if( data.current_page < data.total_pages){
                        var morenode = {
                            "id":treeNode.id+"_"+"loadmore",
                            "pid":treeNode.id,
                            "name":LANG.UI_VM_LOAD_MORE,
                            "clickshow":false,
                            "eventtype":"mploadmore",
                            "iconSkin":"loadmore",
                            "isParent":false,
                            "nocheck":true,
                            "checked":false,
                            "type":4,
                            "title":LANG.UI_VM_LOAD_MORE,
                        }
                        $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, morenode, true);
                    }
                }else if(OPREL(d)){
                    return;
                }
            }
        });
    }
    //获取即将要查询的页序号
    var getcurrentpage =  function(treeId,treeNode){
        var nextpage = 0;
        //查询的是存储库
        if(treeNode.eventtype  == "project"){
            if(treeNode.children != null){
                //因为最后一个节点是加载更多 所以需要先将长度减1  然后向下取整 （为了修改的时候使用）
                nextpage = Math.floor((treeNode.children.length-1) / mplimit) + 1;
            }
            else{
                nextpage = 1;
            }
        }
        //查询的是时间点
        if(treeNode.eventtype == "mp"){
            if(treeNode.children != null){
                nextpage = Math.floor((treeNode.children.length-1) / mplimit) + 1;
            }
            else{
                nextpage = 1;
            }
        }
        return nextpage;
    }
    //刷新清空右边列表
    var refreshCBRList  = function(){
        var allNodes =  currentTree.getCheckedNodes();
        if (allNodes.length ==  0) return;
        $('#cbrTimeGroupList li').remove();
    }
    //异步获取时间点
    var getTPSync =  function(treeId,treeNode){
        var div = ".two_tree";
        var showType = parseInt(3);
        var project_node =  treeNode.getParentNode();
        //var project_id = project_node.id;
        var storageprojectid = project_node.id.split("_");
        var project_id  = storageprojectid[1];
        var region_node =  project_node.getParentNode(); //区域node
        //var region_id =  region_node.id;
        var storageregionid = region_node.id.split("_");
        var region_id  = storageregionid[1];
        var storage_node = region_node.getParentNode(); //存储node
        var storage_id = storage_node.id;
        var nextpage = getcurrentpage(treeId,treeNode); //获取即将要查询的页序号
        var jsondata =  {
            "storage_uuid":storage_id,
            "region_id":region_id,
            "project_id":project_id,
            "vault_id":treeNode.id,
            "current_page":nextpage,//当前查询的页序号 获取存储库现在不做分页
            "limit":tplimit, //每页显示的数量
            "showtype":showType,
        }
        var datastring =  JSON.stringify(jsondata);
        Metronic.blockUI({target: div,animate: true});
        $.ajax({
            type: "post",
            url: CONF.AJAXPATH,
            async:true,
            data:{m:CONF.M.VM,f:"getCBRTP",p:datastring},
            success: function(d){
                Metronic.unblockUI(div);
                var data  =  JSON.parse(d);
                if(!data.re && data.re != false){
                    data.tree.map((item)=>{
                        if(item.eventtype == "tp"){
                            //先获取存储库path
                            var vaultpath = getnodepath(treeNode,"");
                            item.title = LANG.UI_VM_SOURCE_PATH + ":"+vaultpath + "/" + item.parentname;
                        }
                    })
                    $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, data.tree, true);
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                    var treeObj = $.fn.zTree.getZTreeObj(treeId);
                    var nodes = treeObj.getNodesByParam("eventtype", "tploadmore", treeNode);
                    if(nodes.length > 0){
                        //需要删除加载更多
                        treeObj.removeNode(nodes[0]);
                    }

                    if( data.current_page < data.total_pages){
                        var morenode = {
                            "id":treeNode.id+"_"+"loadmore",
                            "pid":treeNode.id,
                            "name":LANG.UI_VM_LOAD_MORE,
                            "clickshow":false,
                            "eventtype":"tploadmore",
                            "iconSkin":"loadmore",
                            "isParent":false,
                            "nocheck":true,
                            "checked":false,
                            "type":6,
                            "title":LANG.UI_VM_LOAD_MORE,
                        }
                        $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, morenode, true);
                    }
                }else if(OPREL(d)){
                    return;
                }
            }
        });
    }

    // 更新右侧列表的序号
    const updateTimepointListNumber = function () {
        return;
        if (jobType !== 1) {
            // 不是跨平台就不需要
            return;
        }
        let _li = $(`.addVMList`).find(`.list-group-item__recoverlist`);
        let number = 1;
        $.each(_li, function () {
            $(this).find(`.li-number`).text(number);
            number ++;
        });
    }

    //勾选添加虚拟机显示列表
    var addPointList = function(id,node){
        var info = "";
        var liId = clearString(id + node.platform_uuid + node.id + node.vm_uuid); //添加虚拟机每列ID
        //路径再加上时间点
        node.timepath = node.path +"/"+node.name;
        if(node.checked){
            // info += '<li class="list-group-item popovers VMTips" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +
            // '"><div class="col1"><div class="cont vmDetail"><div class="cont-col1" style="padding-top: 1px;"><div class="'+node.iconSkin+'">'
            // +'</div><div style="width:30px;height:30px;background: url(./img/vm/vm.png) 0 no-repeat;"></div></div><div class="cont-col2"><div class="desc list-one">' + node.vmname + '</div><div class="desc list-one">' +  node.name + '</div></div></div></div><div class="col2  pull-right delete-list" style="margin-left:-35px;width:35px;padding-top: 7px;"><a class="del'+liId+'" >'
            // +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
            info +=
                '<li class="list-group-item popovers VMTips list-group-item__recoverlist" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ decodeURIComponent(node.path) +'">' +
                /*`<div class="li-number" style="margin: -15px -5px 0 10px;color:#0FBF98;font-weight: bold;white-space: nowrap;"></div>` +*/
                '<div class="col1">' +
                '<div class="cont vmDetail">' +
                '<div class="cont-col1">' +
                '<div class="'+node.iconSkin+'"></div>' +
                '<div style="width:20px;height:20px;background: url(./img/vm/vm.png) 0 no-repeat;"></div>' +
                '</div>' +
                '<div class="cont-col2">' +
                '<div class="desc list-one" style="font-size: 14px;color: #333;padding: 9px 4px 0px 4px">' + node.vm_name + '</div>' +
                '<div class="desc list-one" style="font-size: 12px;color: #666;padding: 4px 4px 0 4px">' +  node.name + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col2  pull-right delete-list">' +
                '<a class="del'+liId+'" >' +
                '<div class="label label-sm label-danger" style="padding:0;">' +
                '<i class="viconfont vicon-guanbi"></i>' +
                '</div>' +
                '</a>' +
                '</div>' +
                '</li>';
            //根据虚拟机树类型添加每一列到列表
            if (moreChoose) {
                $('#showGroupList').append(info);
            } else {
                $('#showGroupList').html(info);
            }

            $('#' + escapeJquery(liId)).popover();	   //初始化tips
            //移除虚拟机显示
            $('.del'+escapeJquery(liId)).on('click', function(){
                var treeObj = $.fn.zTree.getZTreeObj(id);
                $('.popover.in').remove();
                treeObj.checkNode(node,false,false);
                $('#' + escapeJquery(liId)).remove();
                updateTimepointListNumber();
                selectedTimepoint = selectedTimepoint.filter(item => item.timepoint_uuid !== node.timepoint_uuid);
            });
        }else{
            //检查node.id是否在虚拟化里面，如果在就要把对应的li移除
            $('#' + escapeJquery(liId)).remove();
        }
        updateTimepointListNumber();
    }
    var escapeJquery = function(srcString){
        // 转义之后的结果
        var escapseResult = srcString.toString();
        // javascript正则表达式中的特殊字符
        var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
            "]", "|", "{", "}"];
        // jquery中的特殊字符,不是正则表达式中的特殊字符
        var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
            ":", ";", "<", ">", ",", "/"];
        for (var i = 0; i < jsSpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp("\\"
                + jsSpecialChars[i], "g"), "\\"
                + jsSpecialChars[i]);
        }
        for (var i = 0; i < jquerySpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                "g"), "\\" + jquerySpecialChars[i]);
        }
        return escapseResult;
    }
    //替换特殊字符为下划线
    var clearString = function (s){
        var rs = "";
        for (var i = 0; i < s.length; i++) {
            rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
        }
        return rs;
    }


    return {
        init: function (options) {
            Metronic.blockUI({target: '.src-wrap__content', animate: true});

            if (options.moreChoose != undefined) {
                // 是否多选
                moreChoose = options.moreChoose
            }
            if (options.instantaneous != undefined) {
                // 是否包含瞬时恢复快照点
                instantaneous = options.instantaneous
            }

            if (options.jobType != undefined) {
                jobType = options.jobType
            }

            subModuleType = Math.max(parseInt($('#vm_ponint_subtype').val()), 1);

            $('#nodeselect').hide();
            $('#storageselect').show();
            setTimeout(() => {
                initStorageShowType(); //初始化存储类型展示方式和事件
                // initPointShowType(); //暂时使用，目前方便前端拿到数据  以后需要去掉
                if (externalItemUuid) {
                    // 备份数据页面跳转的
                    initStorageTree(false, 0, '', '', '', true);
                } else {
                    initStorageTree();//初始化树
                }
                initListener();
            }, 200);
        },
        // 提供一个对外触发时间点选中事件
        clickPoint: function (id, node){
            return new Promise((resolve, reject) => {
                // 模拟异步加载逻辑
                const result = getSyncVcenterInfo(id, node, false, false);
                resolve(result);
            });
        },
        checkPoint: function (treeObj, id, node){
            return timepointOnCheck('', id, node, treeObj);
        },

        // 提供一个对外获取配置
        getInfo: function () {
            var data = {type: '', storageuuid: '', node_uuid: '', point_info: []};
            var vmOldName = [];
            var showStr = [];
            var nodes = [];
            if (pointtypetree && pointtypetree != undefined) {
                nodes = pointtypetree.getCheckedNodes(true);
            } else {
                nodes = [];
            }
            if(!nodes.length){
                $(".selecttimepointtip").html(LANG.UI_RECOVERY_SELECT_POINT).show();
                // 动态设置tab-pane的高度
                $(".tab-pane__row").css('height', 'calc(100% - 80px)');
                return false;
            } else {
                $(".selecttimepointtip").html('').hide();
            }
            $.each(nodes, function(i, d){
                //按虚拟机分组
                if(3 == d.type || 4 == d.type){
                    var jsondata = {
                        uuid:d.vm_uuid,
                        timepoint_uuid:d.timepoint_uuid,
                        vcenter_uuid:d.platform_uuid,
                        hypervisor:d.hypervisor_type,
                        storage_type:d.storage_type,
                        node_uuid:d.node_uuid,
                        config: d.config,
                        host_name: d.vm_name,
                        dir_path: '',
                        name: d.point_name,
                        path: d.path,
                        integrity_check_flag: d.integrity_check_flag,
                        virus_scan_status: d.virus_scan_status != undefined ? d.virus_scan_status : 0, // 备份点状态
                    };
                    data.point_info.push(jsondata);
                    vmOldName.push([d.vm_name, d.point_name]);
                    showStr.push(d.path + "(" + d.point_name + ")" + "<br>");
                }
                data.type = d.hypervisor_type;
                data.node_uuid = jsondata.node_uuid;
            });
            data.vmOldName = vmOldName;
            data.show_str = showStr;
            data.storage_uuid = $("#storageselect").val();

            return data;
        }
    };
}();
