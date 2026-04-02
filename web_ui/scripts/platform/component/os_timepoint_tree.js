var RecoverTimepoint = function () {
    var moreChoose = false; // 是否支持多选
    var instantaneous = false; // 是否包含瞬时恢复快照点
    var jobType = 1;// 任务类型 默认是跨平台恢复 2瞬时恢复 4细粒度恢复
    var pointtypetree, pointtypetreeInitFlag = false;
    var currentTree; //当前展示的树
    var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
    var OSTYPE = []; //用于存放这次选择的时间点的操作系统类型  ,因为目前只选了一个时间点 暂时用一个变量先存储 后期再修改
    var nodeParamList;
    const externalPointUuid = $('#externalPointUuid').val();
    const externalTaskUuid = $('#externalTaskUuid').val();
    const externalItemUuid = $('#externalItemUuid').val();

    //事件监听
    var initListener = function(){
        //跳转到整机磁盘备份
        $('#tobackup').on('click',function(){
            var url = './content/complete_machine_os/machine_os_backup.php';
            var naviname = 'complete_machine';
            LOCATION(url, naviname);
        });
    }

    //初始化存储
    var initStorage =  function(){
        let p = {
            instantRecoverFlag: false,
            grainRecoverFlag: false,
            osinstantModuleFlag: false,
        };
        pAjaxRequest(p, "/api/v1/storages/type", "GET", function (d) {
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
    };

    //初始化时间点展示方式和事件
    var initPointShowType = function(){
        let requestData = {
            module_type : CONF.MODULE_TYPE.OS
        }
        pAjaxRequest(requestData,'/api/v1/nodes/get/timepoint','GET',(d)=>{
            let data = d.data;
            let nodeselect = $('#nodeselect');
            nodeselect.empty();
            for(let i=0; i<data.length; i++){
                let option = $("<option>").text(data[i].text).val(data[i].node_uuid);
                nodeselect.append(option);
            }
        });

        //绑定事件
        $('#nodeselect').on('change', nodeselectChange);
        $('#storageselect').on('change', storageselectChange);
        // $('#searchname').on('propertychange', searchOS).on('input', searchOS);
        $('#searchname').on('keydown', (event) => {
            if (event.key === 'Enter' || event.keyCode === 13) {
                searchOS();
            }
        });
    }

    var searchOS = function(){
        var value = $('#searchname').val();
        // 输入验证
        if(!customInputValidate('string',value)){
            return false;
        }
        var checkNode =currentTree.getCheckedNodes();
        var allNode = currentTree.transformToArray(currentTree.getNodes());
        nodeParamList = currentTree.getNodesByParamFuzzy('name', value);
        currentTree.hideNodes(allNode);
        if(nodeParamList.length == 0){
            $('.two_tree').hide();
            $('#nosearchtips').show();
        }else{
            $('.two_tree').show();
            $("#nosearchtips").hide();
        }
        //连接搜索的和所勾选的
        nodeParamList =nodeParamList.concat(checkNode);
        var nodeParamList1 = currentTree.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
            findParent(currentTree,nodeParamList1[n]);
        }
        currentTree.showNodes(nodeParamList);
    }

    //找到父节点
    var findParent = function(treeObj,node){
        currentTree.expandNode(node,true,false,false);
        if(!node.children){
            nodeParamList.push(node);
            currentTree.expandNode(node,false,false,false);
        }
        var pNode = node.getParentNode();
        if(pNode != null){
            nodeParamList.push(pNode);
            findParent(currentTree, pNode);
        }
    }

    //节点选择改变事件
    var nodeselectChange = function(){
        pointtypetreeInitFlag = false;
        initPointTree();
        $('#showGroupList li').remove();
    }

    var storageselectChange =  function(){
        pointtypetreeInitFlag = false;
        $('#searchname').val('');
        initPointTree();
    };

    var initPointTree = function() {
        var data = {};
        //data.node_uuid = $('#nodeselect').val();
        data.node_uuid = '';
        data.storageuuid = $('#storageselect').val(); // 按存储筛选;
        data.recoverflag = true;
        data.dataflag = false;
        data.instant_flag = instantaneous;
        data.notcloud = jobType != 1;
        data.nottape = true;
        Metronic.blockUI({target: '.two_tree',animate: true});
        var requestData  = function(data){
            if(data.success){
                setPointTree(data.data)
            }else{
                UIToastr.showWarning(LANG.UI_OS_RECOVERY_CHOOSE_BACKUP_TIMEPOINT, data.message);
            }
        }
        //初始化备份源
        pAjaxRequest(data, '/api/v1/complete_machine_os/timepoint_tree', "GET", requestData, true);
    };

    //初始化时间点树
    var setPointTree = function(zNodes){
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
                beforeClick: osnodeSelect,
                onCheck: timepointOnCheckValidate,
                beforeExpand: nodeExpand
            },
            view: {
                addHoverDom: addHoverDom,
                removeHoverDom: removeHoverDom,
                showTitle: true,
                nameIsHTML: true,
                fontCss: function(treeId, treeNode) {
                    let css = {};
                    if (treeNode.point_status == 1) {
                        css = { color: "#F19F00 " };
                    }
                    if (treeNode.point_status == 3) {
                        css = { color: "#F1416C " };
                    }
                    return css;
                },
            }
        };
        pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, zNodes);
        // 从备份数据跳转恢复页面，展开对象下的时间点
        let targetNode = pointtypetree.getNodeByParam('id', externalTaskUuid + externalItemUuid);
        // 第一次初始化，且有目标节点
        if(targetNode && !pointtypetreeInitFlag){
            // 异步获取时间点
            getSyncVcenterInfo('pointtypetree',targetNode, true, true, true)
        }
        pointtypetreeInitFlag = true;
        currentTree = pointtypetree;
    };

    // 树hover的时候
    const addHoverDom = function(treeId, treeNode) {
        const btnId = 'diyBtn_' + treeNode.tId;
        var btn = $("#diyBtn_"+treeNode.tId);
        if (btn.length > 0 || !(treeNode.type == 3 || treeNode.type == 2)){
            return;
        }
        var aObj = $("#" + treeNode.tId + "_a");
        var editStr =  "<i class='viconfont vicon-Frame11' title='"+LANG.UI_BACKUP_DATA_POINT_DETAIL_TITLE +"' id='diyBtn_" + treeNode.tId + "'></i>";
        aObj.append(editStr);

        // 点击打开抽屉
        aObj.off('click', '#' + btnId).on('click', '#' + btnId, function(e) {
            e.stopPropagation();
            $('.page-content').initPointDetailDrawer({ timepoint_uuid: treeNode.timepointuuid });
        });
    };
    // 失去 hover 的时候
    const removeHoverDom = function(treeId, treeNode) {
        $("#diyBtn_" +treeNode.tId).remove();
    };

    var checkTreeNodeInfo = function(zNodes){
        if(zNodes == "[]" || zNodes.length == 0){
            $("#nopointtips").show();
            $('#pointtypetree').hide();
            $(".two_tree").hide();
            return false;
        }else{
            $("#nopointtips").hide();
            $('#pointtypetree').show();
            $(".two_tree").show();
            return true;
        }
    }

    //选择时间点节点事件绑定
    var osnodeSelect = function(treeId, treeNode, clickFlag){
        if (3 == treeNode.type) {
            // 增备点或差异
            pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
        } else if (2 == treeNode.type) {
            // 完备点
            pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
            pointtypetree.expandNode(treeNode, true);
        } else {
            pointtypetree.expandNode(treeNode, true);
        }
        nodeExpand(treeId, treeNode);
    }

    const timepointOnCheckValidate = function (e, id, node) {
        if (node.checked && node.encrypted_flag) {
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
                        'timepoint_uuid': node.timepointuuid,
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

    var timepointOnCheck = function(e, id, node){
        if (moreChoose) {
            // 支持多选
            //磁带需求，磁带备份点和非磁带备份点互斥，不可同时选择
            if (!checkStorageTypePoint(id, node)) {
                return;
            }
            var flag = node.checked;
            var allNodes = pointtypetree.getCheckedNodes(true);
            if(!checkSelectInOneNode(flag, pointtypetree, node, allNodes, false)){
                $('#showGroupList li').remove();
                addPointList(id, node);
                return;
            }

            for(var i = 0; i < allNodes.length; i++){
                if(allNodes[i].agentuuid == node.agentuuid){
                    var liId = clearString(id+allNodes[i].agentuuid + allNodes[i].id); //添加虚拟机每列ID
                    //如果虚拟机一样的话,就要取消之前所有的
                    pointtypetree.checkNode(allNodes[i], false, false, false);
                    $('#' + escapeJquery(liId)).remove();
                }
            }
            pointtypetree.checkNode(node, flag, false, false);
            addPointList(id, node);
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
    //检测选择的点是否都是磁带备份点或非磁带备份点（互斥）,如果不是,需要把之前的所有点都取消选择
    var checkStorageTypePoint = function(treeId, node){
        var tree = $.fn.zTree.getZTreeObj(treeId);
        var allNodes = tree.getCheckedNodes(true);
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
                        //勾选当前时间点
                        tree.checkNode(node, true, false, false);
                        addPointList(treeId, node);
                        $(this).modal('hide');
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
    var timepointOnChecks = function(e, id, node){
        if (moreChoose) {
            // 支持多选
            var flag = node.checked;
            var allNodes = pointtypetree.getCheckedNodes(true);
            for(var i = 0; i < allNodes.length; i++){
                if(allNodes[i].agentuuid == node.agentuuid){
                    var liId = clearString(id+allNodes[i].agentuuid + allNodes[i].id); //添加虚拟机每列ID
                    //如果虚拟机一样的话,就要取消之前所有的
                    pointtypetree.checkNode(allNodes[i], false, false, false);
                    $('#' + escapeJquery(liId)).remove();
                }
            }
            pointtypetree.checkNode(node, flag, false, false);
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
        var showType = 1;
        for(var i = 0; i< allNodes.length; i++){
            if(allNodes[i].real_node_uuid != node.real_node_uuid){
                UIToastr.showInfo(LANG.UI_RECOVERY_POINT_NOT_IN_ONE_NODE, LANG.UI_RECOVERY_POINT_NOT_IN_ONE_NODE_TIPS);
                for(var i = 0; i < allNodes.length; i++){
                    tree.checkNode(allNodes[i], false, false, false);
                }
                tree.checkNode(node, flag, checkTypeFlag, false);
                return false;
            }
        }
        return true;
    }

    //勾选添加主机显示列表
    var addPointList = function(id, node){
        var info = "";
        var liId = clearString(id + node.agentuuid + node.id, false); //添加虚拟机每列ID
        if(node.checked){
            info +=
                '<li class="list-group-item popovers VMTips list-group-item__recoverlist" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +'">' +
                '<div class="col1">' +
                '<div class="cont vmDetail">' +
                '<div class="cont-col1">' +
                '<div class="'+node.iconSkin+'"></div>' +
                '</div>' +
                '<div class="cont-col2">' +
                '<div class="desc list-one" style="font-size: 14px;color: #333;padding: 10px 4px 0px 4px;">' + node.osname + '</div>' +
                '<div class="desc list-one" style="font-size: 12px;color: #666;padding: 4px 4px 0 4px;">' + node.name + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col2  pull-right delete-list" style="position:absolute;right:0;width:30px;padding-top: 8px;">' +
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
            //移除已选时间点显示
            $('.del'+escapeJquery(liId)).on('click', function(){
                var treeObj = $.fn.zTree.getZTreeObj(id);
                $('.popover.in').remove();
                treeObj.checkNode(node,false,false);
                $('#' + escapeJquery(liId)).remove();
            });
            OSTYPE[node.timepointuuid] = node.ostype;
        }else{
            //检查node.id是否在虚拟化里面，如果在就要把对应的li移除
            $('#' + escapeJquery(liId)).remove();
        }
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

    //替换特殊字符
    var clearString = function (s, flag){
        var rs = "";
        var str = '_';
        if(flag){
            str = '';
        }
        for (var i = 0; i < s.length; i++) {
            rs = rs+s.substr(i, 1).replace(_VMNAMEREG, str);
        }
        return rs;
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
    var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag, chooseFlag){
        var nodeuuid = $('#nodeselect').val();
        var storageuuid = $('#storageselect').val();
        var div = "#pointtypetree";
        var p = {taskuuid:treeNode.taskuuid, agentuuid:treeNode.agentuuid,recoverflag:true,dataflag:false,
            refresh:refreshFlag, storageuuid: storageuuid, instant_flag: instantaneous,
            notcloud: jobType != 1, nottape: true};
        Metronic.blockUI({target: div,animate: true});
        var requestData  = function(data){
            if(data.success){
                Metronic.unblockUI(div);
                // 增加title属性
                var new_data = data.data;
                for (var j in new_data){
                    if (new_data[j].title == undefined) {
                        new_data[j].title = LANG.UI_JOB_HIS_SRC_PATH + '：' + new_data[j].path;
                    }
                }
                //success
                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, new_data, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                if(expendFlag == true){
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                }
                // 从备份数据跳转恢复页面，匹配时间点并勾选
                let targetNode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam('id', externalPointUuid);
                if(targetNode && chooseFlag){
                    $.fn.zTree.getZTreeObj(treeId).checkNode(targetNode, true, true, true);
                }
            }else{
                UIToastr.showWarning(LANG.UI_OS_RECOVERY_CHOOSE_BACKUP_TIMEPOINT, data.message);
            }
        }
        //初始化备份源
        pAjaxRequest(p, '/api/v1/complete_machine_os/sync_timepoint_tree', "GET", requestData, true);
    }

    return {
        init: function (options) {
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
            $('#nodeselect').hide();
            $('#storageselect').show();

            initStorage(); //初始化存储
            initPointShowType();//初始化可选节点
            initPointTree();//初始化树形结构
            initListener();
        },
        // 提供一个对外获取配置
        getInfo: function () {
            var data = {type: '', node_uuid: '', point_info: []};
            var showStr = [];
            var vmOldName = [];
            var nodes = [];
            if (currentTree  && currentTree != undefined) {
                nodes = currentTree.getCheckedNodes(true);
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
                //按分组
                var jsondata = {
                    uuid:d.agentuuid,
                    timepoint_uuid:d.timepointuuid,
                    node_uuid:d.real_node_uuid,
                    os_type: d.ostype,
                    host_name: d.osname,
                    dir_path: d.osname,
                    name: d.name,
                    path: d.path,
                    storage_type: d.storage_type != undefined ? d.storage_type : 0,
                    encrypted_flag: d.encrypted_flag,
                    system_boot_type: d.system_boot_type,
                    integrity_check_flag: d.integrity_check_flag,
                    hypervisor: 0,
                    virus_scan_status: d.virus_scan_status != undefined ? d.virus_scan_status : 0,
                };
                data.point_info.push(jsondata);
                showStr.push(d.path + "(" + d.name + ")" + "<br>");
                vmOldName.push([d.path, d.timepoint_des]);
                data.node_uuid = jsondata.node_uuid;
                data.type = d.type;
            });
            data.show_str = showStr;
            data.vmOldName = vmOldName;
            console.log('data', data);
            return data;
        }
    };
}();
