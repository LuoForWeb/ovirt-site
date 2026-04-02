var ObsBackup = function () {
    const OBS_GROUP_ID = {
        AWS: 0,
        OSS: 1,
        COS: 2,
        OBS: 3,
        CEPH: 4,
        WASABI: 5,
        MINIO: 6,
        AZURE: 7
    };
    const TIME_BACKUP_STRATEGY_TYPE = {
        STRATEGY: 1,
        ONCETIME: 2,
        MANUAL: 3 
    }
    const VERIFY_CYCLE_DESC_MAP = {
        0: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_WEEK,
        1: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_DAY,
        2: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_EVERY_TIME
    }; // 验证策略 - 校验周期描述
    const BACKUP_POINT_ABNORMAL_DESC_MAP = {
        0: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE1,
        1: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE2
    }; // 验证策略 - 备份点异常描述
    let ajaxData = {
        srcInfo: {},
        backupInfo: {},
        highInfo: {},
        speedLimit: {},
        safe_config_strategy: {
            worm_flag: 2, // 是否开启worm防护，默认关闭
            worm_protection_time: 99, // worm防护保护期限
            virus_scan_flag: 2, // 是否开启病毒防护（对象存储模块不涉及，默认关闭）
            virus_scan_config_list: [], // 病毒防护配置参数（对象存储模块不涉及，默认为空）
            integrity_check_flag: 2, // 是否启用完整性检查
            integrity_check_config: { // 完整性检查参数
                check_strategy: 2, // 完整性检查策略(默认每次)：0 每周，1 每天，2 每次
                full_error_policy: 1, // 备份完备点异常策略：0 停止， 1 重做完备（默认）
                inc_error_policy: -1, // 增备点异常策略：0 停止，1 重做完备，2重做增备（默认）对象存储模块不涉及传-1
                recovery_error_policy: -1 // 恢复异常策略：0 停止（默认），1 继续正常恢复，2 恢复到无网络环境 对象存储备份不涉及传-1
            }
        }
    };
    let zTree = null;
    let zTreeFile = []; // 选中的对象存储中的文件
    let _pageSize = 20; // 代理端文件列表每次显示条数;
    let _path = '';		 // 当前路径
    let searchFlag = false;
    let _timeStamp = '';	// 时钟时间戳,全局
    let nodeSelectFlag = false; //自定义节点选择加载标志
    let initSpeedFlag = false;
    let speedList = [];
    let oldNode, networkFlag = false;	//用于比对加载传输网络的节点
    let initStrategyFlag = false;
    let globalStrategy = [];
    let defaultStrategy = [];
    let initErrorFlag = false;
    let nodeParamList;//用于保存搜索agent的结果
    // ------------ 以下是修改备份所需参数 ----------
    let EDIT_BACKUP_FLAG = false; // 创建备份 | 修改备份标记
    let pageIndex = 0; //轮播索引
    let firstInitPageFlag = false; // 首次进入页面标记
    let PASSWORD_HASCHANGED_FLAG = false; // 是否改变了密码框内容标记
    let taskId = ''; // 修改备份记录task uuid
    let taskInfoSetting = {}; // 修改备份任务详情对象
    let APPLIANCE_AGENCY_HAS_CONFIGED = false; // 传输代理是否已配置标记
    let appliedGlobalStrategy = {
        flag: false,
        uuid: '',
        strategy: {}
    } // 应用的全局策略
    let storageList = []; // 存储列表
    let selectedStorageType = 0; // 选择的存储类型
    let currentmax = 0; // 修改备份任务时记录步骤条当前最大下标
    let checkedOfflineObsFiles = []; // 修改备份任务时记录离线状态勾选的对象存储对应的文件数组
    let checkedOfflineObsWildcards = []; //修改备份任务时记录离线状态勾选的对象存储对应的通配符数组
    let LICENSE_QUANTITY_IS_ENOUGH = true; // 授权数量足够标记

    // <-------------------------   BEGIN STEP ONE BACKUP SOURCE    ------------------------------>

    const findParent = (treeObj, node) => {
        zTree.expandNode(node,true,false,false);
        if(!node.children){
            nodeParamList.push(node);
            zTree.expandNode(node,false,false,false);
        }
        let pNode = node.getParentNode();
        if(pNode != null){
            nodeParamList.push(pNode);
            findParent(zTree, pNode);
        }
    }

    const debounce = () => {
        let value = $('#searchAgent').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
        let nodes = zTree.getNodes();
        if (!nodes || nodes.length === 0) return;

        let checkNode =zTree.getCheckedNodes();
        let checkFsNode = [];

        $.each(checkNode, function (i, v) {
            if (v.type == "obsItem") {
                checkFsNode.push(v);
            }
        });

        let allNode = zTree.transformToArray(zTree.getNodes());
        nodeParamList = zTree.getNodesByParamFuzzy('name', value);

        if (nodeParamList.length !== 0) {
            zTree.hideNodes(allNode);
            $('.src-wrap__content__ztree .vcenter-tree').show();
            $('#nosearchtips').hide();
        } else {
            $('.src-wrap__content__ztree .vcenter-tree').hide();
            $('#nosearchtips').show();
        }

        // 连接搜索的和所勾选的
        nodeParamList = nodeParamList.concat(checkFsNode);
        let nodeParamList1 = zTree.transformToArray(nodeParamList);

        for(let n in nodeParamList1){
            findParent(zTree, nodeParamList1[n]);
        }

        zTree.showNodes(nodeParamList);
        searchFlag = true;
    }

    /**
     * 检测对象存储是否在线或已授权
     * @param treeNode
     */
    const checkObsOnlineOrAuthed = (treeNode) => {
        if (treeNode.chkDisabled) {
            UIToastr.showWarning(LANG.UI_OBS_OFFLINE_OR_NOAUTHORIZED, LANG.UI_OBS_OFFLINE_OR_NOAUTHORIZED_TIPS);
            return;
        }
    }

    /**
     * 点击节点前回调
     * @param treeId
     * @param treeNode
     */
    const nodeClick = function(treeId, treeNode) {
        if (EDIT_BACKUP_FLAG && treeNode.chkDisabled && ajaxData.srcInfo.agentList.indexOf(treeNode.id) > -1) {
            ajaxData.srcInfo.agentList.splice(ajaxData.srcInfo.agentList.indexOf(treeNode.id), 1);
        }

        // 检测对象存储是否在线或已授权
        checkObsOnlineOrAuthed(treeNode);

        if (treeNode.type === 'obsGroup') {
            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);//单击展开节点
            return;
        }

        // 未被禁用，单击选中或取消选中
        if (!treeNode.chkDisabled && treeNode.type === 'obsItem') {
            $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true);
        }

        nodeExpand(treeId, treeNode);
    };

    /**
     * 生成选中的对象存储对应的目录树
     * @param {*} treeNode 选中的对象存储树节点
     * @param {*} wildcardObj 对应对象存储信息（group_uuid, obs_uuid, 通配符配置）用于修改时回显通配符
     */
    const addObsAccordionList = (treeNode, wildcardObj = {}) => {
        let obsAccordions =  $('#allFileTree').children();

        if (obsAccordions.length > 0) {
            for (let i = 0; i < obsAccordions.length; i++) {
                if (obsAccordions[i].id === treeNode.id) {
                    // 移除重复的
                    $('#allFileTree').find('#' + treeNode.id + '.add-list').remove();
                }
            }
        }

        let obsPathTreeContent = '';
        obsPathTreeContent +=
            '<div id="'+ treeNode.id +'" class="add-list">' +
                '<div class="accordion file-accordion">' +
                    '<div class="panel panel-default panel-file">' +
                        '<div class="panel-heading">' +
                            '<h4 class="panel-title">' +
                                '<a class="accordion-toggle accordion-toggle-styled popovers" style="display: inline-block; width: 99%;text-decoration: none;" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".fileAccordion" href="#fileClientInfo_'+ treeNode.id +'" aria-expanded="true">' +
                                    '<span class="font-green-seagreen">'+ treeNode.name +'</span>' +
                                '</a>' +
                            '</h4>' +
                        '</div>' +
                        '<div id="fileClientInfo_' + treeNode.id + '" class="panel-collapse collapse in">' +
                            '<div class="panel-body">' +
                                '<div class="nav-tabs-wrapper">' +
                                    '<ul class="nav nav-tabs nav-line-tabs" id="fileClientInfo_tabs_' + treeNode.id + '">' +
                                        '<li id="commontab' + treeNode.id + '" class="active nav-item">' +
                                            '<a class="nav-link" href="#common_tabagent_tree_' + treeNode.id + '" data-toggle="tab" aria-expanded="false">' + LANG.UI_OBS_SELECT_BACKUP_OBJECT +'</a>' +
                                        '</li>' +
                                        '<li class="nav-item">' +
                                            '<a class="nav-link" href="#high_tabagent_tree_' + treeNode.id + '" data-toggle="tab" aria-expanded="false">' + LANG.UI_PUBLIC_MORE +'</a>' +
                                        '</li>' +
                                    '</ul>' +
                                    '<div class="tab-content hover-scroll-y">' +
                                        '<div class="tab-pane active" id="common_tabagent_tree_' + treeNode.id + '">' +
                                            '<div class="row" style="margin: 0">' +
                                                '<div class="form-group" style="margin: 0">' +
                                                    '<ul id="fileClientTree_' + treeNode.id + '" class="ztree"></ul>' +
                                                '</div>' +
                                            '</div>' +
                                        '</div>' +
                                        '<div class="tab-pane" id="high_tabagent_tree_' + treeNode.id + '">' +
                                            '<div class="row" style="margin: 0">' +
                                                '<div class="form-group wildmode">' +
                                                    '<label class="control-label col-md-3 wildcardmodelabel">' + LANG.UI_FILE_WILDCARD_BAK_WAY +'</label>' +
                                                    '<div class="col-md-5 flex-items-center">' +
                                                        '<select class="wildcardmode form-control">' +
                                                            '<option value="0">' + LANG.UI_FILE_WILDCARD_RULES_NO_USE + '</option>' +
                                                            '<option value="1">' + LANG.UI_FILE_WILDCARD_BAK_FILTER + '</option>' +
                                                            '<option value="2">' + LANG.UI_FILE_WILDCARD_BAK_SELECT + '</option>' +
                                                        '</select>' +
                                                        '<a class="popovers position-absolute" style="right: -10px" data-container="body" data-trigger="hover" data-placement="right" data-html="true" data-content="'+ LANG.UI_OBS_WILDCARD_BAK_MODE_TIPS +'" style="margin-left: 0" data-original-title="" title="">' +
                                                            '<i class="viconfont vicon-tishi"></i>' +
                                                        '</a>' +
                                                    '</div>' +
                                                '</div>' +
                                                '<div class="form-group wildcarddiv display-none">' +
                                                    '<label class="control-label col-md-3 wildcardlabel">' + LANG.UI_FILE_WILDCARD + '</label>' +
                                                    '<div class="col-md-5 allWildcardInput flex-items-center">' +
                                                        '<input type="text" class="form-control wildcardInputdiv input-sm wildcardInput">' +
                                                        '<button type="button" class="btn btn-primary addInput min-w-52px h-34px ms-10">'+ LANG.UI_BACKUP_FILE_ADD +'</button>' +
                                                        '<a class="popovers position-absolute" style="right: -10px" data-container="body" data-trigger="hover" data-placement="right" data-content="'+ LANG.UI_FILE_WILDCARD_RULES_ADD_TIPS +'">' +
                                                            '<i class="viconfont vicon-tishi"></i>' +
                                                        '</a>' +
                                                    '</div>' +
                                                '</div>' +
                                                '<div class="form-group wildcard-list display-none">' + 
                                                    '<label class="control-label col-md-3"></label>' +
                                                    '<div class="col-md-5 form-group-wildcards">' + 
                                                        
                                                    '</div>' +
                                                '</div>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                '</div>'
            '</div>';

        $('#allFileTree').append(obsPathTreeContent);
        $('.popovers').popover({ html:true });

        // 修改备份任务如果配置过通配符 要回显上，如果wildcardObj是空对象，为非修改任务页面初始化回显对象存储目录树场景
        if (EDIT_BACKUP_FLAG && JSON.stringify(wildcardObj) !== '{}') {
            let wildcardmode = parseInt(wildcardObj.wildcard_mode);

            // 回显通配符备份方式
            $(`#high_tabagent_tree_${treeNode.id}`).find('.wildcardmode').val(wildcardmode);

            if (wildcardmode !== 0) {
                $(`#high_tabagent_tree_${treeNode.id}`).find('.wildcarddiv').show();
                $(`#high_tabagent_tree_${treeNode.id}`).find('.wildcard-list').show();

                wildcardObj.wildcard.forEach(item => {
                    let wildcardItemHtml = '<div class="form-group-wildcards__item" title="' + item + '">' + 
                                        '<span class="form-group-wildcards__item__text">' + item + '</span>' +
                                        '<span class="form-group-wildcards__item__close" title="' + LANG.UI_OBS_REMOVE_ITEM +'">×</span>' +
                                    '</div>';
                    
                    $(`#high_tabagent_tree_${treeNode.id}`).find('.wildcard-list').find('.form-group-wildcards').append(wildcardItemHtml);
                })
            }
        }  
    }

    /**
     * 通配符过滤下拉框change事件
     */
    const wildcardmodeTypeHandler = function () {
        let wildcardmode = parseInt($(this).val());

        if (wildcardmode !== 0) {
            $(this).parents('.form-group.wildmode').siblings('.wildcarddiv').show();
            $(this).parents('.form-group.wildmode').siblings('.wildcard-list').show();
            $(this).parents('.form-group.wildmode').siblings('.wildcard-list').find('.form-group-wildcards__item').remove();
        } else {
            $(this).parents('.form-group.wildmode').siblings('.wildcarddiv').hide();
            $(this).parents('.form-group.wildmode').siblings('.wildcard-list').find('.form-group-wildcards__item').remove();
            $(this).parents('.form-group.wildmode').siblings('.wildcard-list').hide();
        }
    }

    /**
     * 处理通配符输入框
     * @returns {boolean}
     */
    const wildInputAdd = function() {
        let wildcardVal = $.trim($(this).siblings('.wildcardInputdiv').val());

        if (wildcardVal) {
            let wildcardItemHtml = '<div class="form-group-wildcards__item" title="' + wildcardVal + '">' + 
                                        '<span class="form-group-wildcards__item__text">' + wildcardVal + '</span>' +
                                        '<span class="form-group-wildcards__item__close" title="' + LANG.UI_OBS_REMOVE_ITEM +'">×</span>' +
                                    '</div>';
            
            $(this).parents('.form-group.wildcarddiv').siblings('.form-group.wildcard-list').find('.form-group-wildcards').append(wildcardItemHtml);
        } else {
            UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
            return false;
        }

        // 清空通配符输入框
        $(this).siblings('.wildcardInputdiv').val('');
    }

    /**
     * 删除通配符
     */
    const delInput = function () {
        $(this).parent('.form-group-wildcards__item').remove();
    }

    /**
     * 备份源对象存储树勾选节点
     * @param treeId
     * @param treeNode
     */
    const nodeCheck = (treeId, id, treeNode) => {
        // 检查是否在线和已授权
        if (treeNode.chkDisabled) {
            UIToastr.showWarning(LANG.UI_OBS_OFFLINE_OR_NOAUTHORIZED, LANG.UI_OBS_OFFLINE_OR_NOAUTHORIZED_TIPS);
            return;
        }

        // 展开节点
        nodeExpand(treeId, treeNode);
    };

    /**
     * 加载更多节点
     * @param treeId
     * @param pNode
     */
    const getMoreTree = (treeId, pNode) => {
        // 判断父节点下的子节点是否全选
        let checkeFlag = pNode.getParentNode().check_Child_State === 2;
        let params = {
            limit: _pageSize,
            dir: pNode.dir_path,
            pid: pNode.pid,
            obs_uuid: pNode.uuid,
            startAfter: pNode.search_file_name
        }

        let targetId = `#fileClientTree_${pNode.uuid}`;
        Metronic.blockUI({target: targetId,animate: true});

        pAjaxRequest(params, '/api/v1/s3/file_dir_son_tree', 'GET', (result) => {
            Metronic.unblockUI(targetId);

            if (result.success) {
                if (checkeFlag && result.data.fileNodes.length > 0) {
                    for(let i= 0; i < result.data.fileNodes.length; i++) {
                        result.data.fileNodes[i].checked = true;
                    }
                }

                // 给每层节点加上对象存储的group_uuid
                let fileNodes = result.data.fileNodes || [];
                fileNodes = fileNodes.map(item => {
                    return {
                        ...item,
                        group_uuid: pNode.group_uuid
                    }
                });

                $.fn.zTree.getZTreeObj(treeId).removeNode(pNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(pNode.getParentNode(), fileNodes, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_OBS_SUBTREE, `${result.message}`);
            }
        });
    }

    /**
     * 文件目录点击前回调
     * @param treeId
     * @param pNode
     */
    const fileNodeClick = function (treeId, pNode) {
        //是否被禁用
        if(pNode.chkDisabled) {
            return;
        }
        //1文件 2 文件夹 3 磁盘
        if(pNode.type == 1){
            return;
        } else {
            //如果不是文件 加载文件/目录树
            if(pNode.more) {//加载更多
                getMoreTree(treeId, pNode);
                return;
            }
            _path = pNode.filepath;
            fileNodeExpand(treeId, pNode);
            $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
        }
    }

    const getFileSonTree = (treeId, pNode, expandFlag) => {
        let params = {
            obs_uuid: pNode.uuid,
            start: 0,
            limit: _pageSize,
            filename: '',
            dir: pNode.filepath,
            pid: pNode.filepath,
            code_type: pNode.code_type
        };

        let targetId = `#fileClientTree_${pNode.uuid}`;
        Metronic.blockUI({target: targetId, animate: true});

        pAjaxRequest(params, '/api/v1/s3/file_dir_son_tree', 'GET', (result) => {
            Metronic.unblockUI(targetId);

            if (result.success) {
                // 给每层节点加上对象存储的group_uuid
                let fileNodes = result.data.fileNodes || [];
                fileNodes = fileNodes.map(item => {
                    return {
                        ...item,
                        group_uuid: pNode.group_uuid
                    }
                });

                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(pNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(pNode, fileNodes, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);

                if(expandFlag){
                    $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true, true, true);
                }

                if(pNode.checked && pNode.children) {
                    pNode.children.forEach(item=>{
                        $.fn.zTree.getZTreeObj(treeId).checkNode(item,true);
                    });
                }
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_OBS_SUBTREE, `${result.message}`);
            }
        })
    }

    /**
     * 文件目录树展开
     * @param treeId
     * @param pNode
     * @returns {boolean}
     */
    const fileNodeExpand = (treeId, pNode) => {
        if(pNode.children) return true;
        getFileSonTree(treeId, pNode, false);
    }

    /**
     * 渲染对象存储对应的目录树
     * @param fileNodes
     */
    const setFileTree = (fileNodes) => {
        let setting = {
            check: {
                enable: true
            },
            data: {
                simpleData: {
                    enable: true,
                },
                key:{
                    title: "title"
                }
            },
            callback: {
                beforeClick: fileNodeClick,
                // onCheck: fileNodeCheck,
                beforeExpand: fileNodeExpand
            },
            view: {
                dblClickExpand: false
            }
        };

        let agent_uuid =  (fileNodes.length === 0 ? "" : fileNodes[0].uuid);

        zTreeFile[agent_uuid] = $.fn.zTree.init($(`#fileClientTree_${agent_uuid}`), setting, fileNodes);
        $('#fileClientTree li').css("background-color","white");
    }

    /**
     * 获取对象存储下的目录树
     * @param treeNode
     * @param obsuuid
     * @param groupuuid
     * @param EDIT_BACKUP_FLAG
     */
    const initFileTree = (treeNode, obsuuid, groupuuid, EDIT_BACKUP_FLAG) => {
        let _path = treeNode.path;
        let params = {
            start: 0,
            limit: _pageSize,
            filename: '',
            dir: _path,
            pid: 0,
            obs_uuid: obsuuid,
            editFlag: EDIT_BACKUP_FLAG,
            startAfter: '',
            taskId: EDIT_BACKUP_FLAG ? taskInfoSetting.job_uuid : ''
        };

        Metronic.blockUI({target: `#${treeNode.id}`, animate: true});
        pAjaxRequest(params, "/api/v1/s3/file_dir_tree", "GET", (result) => {
            Metronic.unblockUI(`#${treeNode.id}`);

            if (result.success) {
                let fileNodes = result.data.fileNodes || [];
                let finalFileNodes = [];

                if (EDIT_BACKUP_FLAG) { // 修改备份任务时单独处理下多余的 加载更多 节点，如果这个 加载更多 节点的前后节点pId一致说明该 加载更多 节点是无用节点要删除，否则保留

                    for (let i = 0; i < fileNodes.length; i++) {
                        const item = fileNodes[i];

                        // 如果不是 "加载更多" 节点，直接加入新数组
                        if (!item.more) {
                            finalFileNodes.push(item);
                        } else {
                            // 检查当前 "加载更多" 节点的前后节点
                            const prevItem = i > 0 ? fileNodes[i - 1] : null;
                            const nextItem = i < fileNodes.length - 1 ? fileNodes[i + 1] : null;

                            // 判断是否保留该 "加载更多" 节点
                            if (
                                !prevItem || !nextItem ||
                                prevItem.pId !== nextItem.pId
                            ) {
                                finalFileNodes.push(item); // 不删除，保留该节点
                            }
                            // 否则不添加，即删除该项
                        }
                    }
                } else {
                    finalFileNodes = fileNodes;
                }

                finalFileNodes = finalFileNodes.map(item => {
                    return {
                        ...item,
                        group_uuid: groupuuid
                    }
                })

                setFileTree(finalFileNodes);
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_OBS_FILE_TREE_FAILED, result.message);
            }
        });

        if (ajaxData.srcInfo.groupList.indexOf(groupuuid) === -1) {
            ajaxData.srcInfo.groupList.push(groupuuid);
        }

        ajaxData.srcInfo.agentList.push(obsuuid);
        $('#step1tips').hide();
        $('#agentfilediv').show();
		$('#filelistdiv').show();
    }

    /**
     * 备份源对象存储树节点展开
     * @param treeId
     * @param treeNode
     */
    const nodeExpand = (treeId, treeNode) => {
        if (EDIT_BACKUP_FLAG) { // 修改备份任务时，禁用节点特殊处理
            if (treeNode.chkDisabled) { // 离线对象存储只能取消，不能选中
                treeNode.chkDisabled = false;
                zTree.checkNode(treeNode, false, true);
                treeNode.chkDisabled = true;
                zTree.updateNode(treeNode);
            }

            // 批量取消选中离线对象存储
            if (!treeNode.checked && treeNode.type == 1 && treeNode.children) {
                for (let i = 0; i < treeNode.children.length; i++) {
                    if (!treeNode.children[i].chkDisabled) { // 避免将未禁用的对象存储设置为禁用状态
                        continue;
                    }
                    treeNode.children[i].chkDisabled = false;
                    zTree.checkNode(treeNode.children[i], false, true);
                    treeNode.children[i].chkDisabled = true;
                    zTree.updateNode(treeNode.children[i]);
                }
            }
        }
        
        if (treeNode.checked) { // 勾选
            if (treeNode.type === 'obsGroup') { // 勾选组
                let childNodes = treeNode.children;

                if (childNodes.length > 0) {
                    childNodes.forEach((item) => {
                        if (item.checked) {
                            addObsAccordionList(item);
                            initFileTree(item, item.id, treeNode.id, false);
                        }
                    })
                }
            } else { // 勾选单项
                addObsAccordionList(treeNode);
                initFileTree(treeNode, treeNode.id, treeNode.pId, false);
            }
        } else { // 取消勾选
            if (treeNode.chkDisabled) {
                return;
            }

            if (zTree.getCheckedNodes(true).length !== 0) { // 未全部取消勾选
                let groupuuid = '';
                let allPid = [];

                if (treeNode.type === 'obsGroup') { // 取消勾选组
                    let childNodes = treeNode.children;

                    if (childNodes.length > 0) {
                        childNodes.forEach((item) => {
                            delete zTreeFile[item.id];
                            // 移除DOM中的该对象存储目录树
                            $('#allFileTree').find('#' + item.id + '.add-list').remove();
                            if(ajaxData.srcInfo.agentList.indexOf(item.id) > -1) {
                                ajaxData.srcInfo.agentList.splice(ajaxData.srcInfo.agentList.indexOf(item.id), 1);
                            }
                        });
                    }
                } else { // 取消勾选单项
                    ajaxData.srcInfo.agentList.splice(ajaxData.srcInfo.agentList.indexOf(treeNode.id),1);

                    // 删除取消选中的树对象
                    delete zTreeFile[treeNode.id];
                    // 移除DOM中的该对象存储目录树
                    $('#allFileTree').find('#' + treeNode.id + '.add-list').remove();
                }
                // 删除取消选中的groupuuid
                treeNode.type === 'obsItem' ? groupuuid = treeNode.pId : groupuuid = treeNode.id;
                zTree.getCheckedNodes(true).forEach(item => {
                    if(item.type === "obsItem") {
                        allPid.push(item.pId);
                    }
                });

                if (allPid.indexOf(groupuuid) === -1 && ajaxData.srcInfo.groupList.indexOf(groupuuid) !== -1) { // 该分组下没有其他对象存储
                    ajaxData.srcInfo.groupList.splice(ajaxData.srcInfo.groupList.indexOf(groupuuid), 1);
                }
            } else { // 全部取消勾选
                ajaxData.srcInfo.agentList = []; // 清空agentList
                ajaxData.srcInfo.groupList = []; // 清空groupList
                zTreeFile = [];//清空树对象
				$('#allFileTree').html('');
				$('#agentfilediv').hide();
				$('#step1tips').show();
            }
        }
    }

    const setFontCss = (treeId, treeNode) => {
        return treeNode.check_disabled ? {color:"grey"} : {};
    }

    /**
     * 初始化备份源对象存储树 
     */
    const initTree = () => {
        Metronic.blockUI({target: '.src-wrap__content', animate: true, cenrerY: true,});
        pAjaxRequest({}, "/api/v1/s3/backup_source_tree", "GET", function (result) {
            if (result.success) {
                let zNodes = result.data;
                let setting = {
                    check: {
                        enable: true,
                        nocheckInherit: false
                    },
                    data: {
                        simpleData: {
                            enable: true,
                        },
                        key:{
                            title: "title"
                        }
                    },
                    callback: {
                        beforeClick: nodeClick,
                        onCheck: nodeCheck,
                        beforeExpand: nodeExpand
                    },
                    view: {
                        fontCss: setFontCss,
                    }
                }

                if (zNodes.length === 0) {
                    $("#noagent").show();
                    $(".vcenter-tree").hide();

                    if(searchFlag) {
                        $('#nosearchtips').show();
                        $("#noagent").hide();
                    }

                    Metronic.unblockUI('.src-wrap__content');

                    return;
                } else {
                    $("#noagent").hide();
                    $(".vcenter-tree").show();
                    $(".searchDiv").show();
                    $('#nosearchtips').hide();

                    // 修改备份时回显所勾选的对象存储
                    if (EDIT_BACKUP_FLAG) {
                        if (taskInfoSetting.checkedObsList && taskInfoSetting.checkedObsList.length > 0) {
                            let checkedObsList = taskInfoSetting.checkedObsList;
                            let wildcardList = taskInfoSetting.high.wild_card_info;
                            let checkedObsInfo = []; // 每项记录每个所选择的对象存储的信息（包含通配符）

                            if (checkedObsList.findIndex(i => i.status === 0) > -1) {
                                UIToastr.showWarning(LANG.UI_OBS_OFFLINE_OR_NOAUTHORIZED, LANG.UI_OBS_OFFLINE_OR_NOAUTHORIZED_TIPS);
                            }

                            // 循环处理给checkedObsList每项添加其对应的通配符配置，用于下一步生成ObsAccordion回显通配符
                            checkedObsList.forEach(item => {
                                let tmp = { ...item };

                                wildcardList.forEach(i => {
                                    if (i.obs_uuid === item.obs_uuid) {
                                        tmp =  {
                                            ...item,
                                            wildcard_mode: i.wildcard_mode,
                                            wildcard: i.wildcard ? i.wildcard : []
                                        }
                                    }
                                })
                            
                                checkedObsInfo.push(tmp);
                            });
                            
                            zNodes.forEach(item => {
                                checkedObsInfo.forEach(i => {
                                    if (parseInt(i.group_uuid) === item.id) {
                                        item.open = true;
                                        item.checked = true;
                                    }

                                    if (i.obs_uuid === item.id) {
                                        item.checked = true;
                                        // 手动触发查询非离线状态的对象存储的目录树
                                        if (!item.chkDisabled) {
                                            addObsAccordionList(item, i);
                                            initFileTree(item, item.id, item.pId, true);
                                        } else {
                                            if (ajaxData.srcInfo.groupList.indexOf(parseInt(i.group_uuid)) === -1) {
                                                ajaxData.srcInfo.groupList.push(parseInt(i.group_uuid));
                                            }
                                    
                                            ajaxData.srcInfo.agentList.push(i.obs_uuid);
                                        }
                                    }
                                })
                            });
                        }
                    } else {
                        // 如果只有一种对象存储类型时，则默认展开其所有对象存储
                        if (zNodes.filter(item => item.type === 'obsGroup').length === 1) {
                            zNodes[0].open = true;
                        }
                    }
                }

                // 初始化zTree
                zTree = $.fn.zTree.init($("#obs_tree"), setting, zNodes);
                if (searchFlag) {
                    zTree.expandAll(true);
                }
                searchFlag = false;
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_BACKUP_SOURCE_FAILED, result,message);
            }

            Metronic.unblockUI('.src-wrap__content');
        });
    }

    const deleteRegionStr = (str) => {
        if (!str) return '';
        let arr = str.split('/');
        arr.shift();

        let path = str.split('/')[0];
        let resultStr = `${path.split('|')[0]}/${arr.join('/')}`;

        return resultStr;
    }

    /**
     * 步骤一校验通过后给ajax data赋值
     * @returns {*[]}
     */
    const confirmStep1Config = () => {
        let backupSourceNodes = zTree.getCheckedNodes(true);
        let checkedChildNodes = [];
        let backupFileListWrapper = ''; // 确认配置 - 备份对象列表 要展示的内容
        let wildcardModeHtml = ''; // 备份模式
        let wildcardListHtml = ''; // 通配符列表

        if ($.trim($('#jobname').val()) === "") {
            pAjaxRequest({job_name: 'WEB_OBS_BACKUP_TASKNAME'}, '/api/v1/jobs/name', "GET", (result) => {
                if (result.success) {
                    $('#jobname').val(result.data.value);
                }
            })
        }

        // 循环遍历选择的对象存储目录树节点数组
        let fileInfoArr = ajaxData.srcInfo.fileInfo;
        for (let i = 0; i < fileInfoArr.length; i++) {
            let eachObsCheckedNodes = [];
            // 过滤出全选的节点和勾选的子节点
            for (let j = 0; j < fileInfoArr[i].length; j++) {
                // 2：节点全选 -1：不存在子节点
                if (fileInfoArr[i][j].check_Child_State === 2 || fileInfoArr[i][j].check_Child_State === -1) {
                    eachObsCheckedNodes.push(fileInfoArr[i][j]);
                }
            }

            // 过滤掉对象存储中全选的节点下的节点
            for (let m = 0; m < eachObsCheckedNodes.length; m++) {
                if (parseInt(eachObsCheckedNodes[m].type) !== 1) {
                    for (let n = 0; n < eachObsCheckedNodes.length; n++) {
                        let pid = eachObsCheckedNodes[n].pId === 0 || eachObsCheckedNodes[n].pId === null ? '' : eachObsCheckedNodes[n].pId;
                        let filepath = eachObsCheckedNodes[m].filepath;

                        //判断全选的文件夹下是否还有文件  有则从数组中删除
                        if (pid.includes(filepath) && eachObsCheckedNodes.indexOf(eachObsCheckedNodes[n]) > -1) {
                            eachObsCheckedNodes.splice(eachObsCheckedNodes.indexOf(eachObsCheckedNodes[n]), 1);
                            n--;
                        }
                    }
                }
            }
            checkedChildNodes.push(eachObsCheckedNodes);
        }

        checkedChildNodes = [].concat.apply([], checkedChildNodes); // 二维数组转一维数组
        ajaxData.srcInfo.fileInfo = []; // 清空fileInfo
        
        // 如果是修改备份任务，checkedChildNodes还要加上离线对象存储所选择的对象文件数组
        if (EDIT_BACKUP_FLAG) {
            // 组合离线状态的对象存储所选择的文件数组
            checkedChildNodes = checkedChildNodes.concat(checkedOfflineObsFiles);
        }

        // 生成要展示的备份文件列表内容
        backupSourceNodes.forEach(item => {
            if (item.type === "obsItem") {
                // 生成所备份的对象存储
                backupFileListWrapper += `<strong>${item.name}</strong><br>`;

                checkedChildNodes.forEach(i => {
                    if (item.id === i.uuid) {
                        // 去掉 region
                        let filepathWithoutRegion = deleteRegionStr(i.filepath);

                        // 生成所备份的对象存储下的文件列表
                        backupFileListWrapper += `${filepathWithoutRegion.replace(/</g, '&lt;').replace(/>/g, '&gt;')}<br>`;

                        // 处理需要传给接口的fileInfo参数
                        ajaxData.srcInfo.fileInfo.push({
                            type: i.type,
                            filePath: i.filepath,
                            obs_uuid: i.uuid,
                            group_uuid: i.group_uuid,
                            codeType: i.code_type
                        })
                    }
                });

                // 设置通配符显示
                wildcardListHtml += `<strong>${item.name}</strong><br>`;
                ajaxData.highInfo.newstr.wildcardList.forEach(m => {
                    if (item.id === m.obsuuid) {
                        if (m.wildcardList.length > 0) {
                            wildcardListHtml += `${LANG.UI_FILE_WILDCARD}: ${m.wildcardList.join(',')};<br>`;
                        }

                        if (m.wildcardMode === 0) {
                            wildcardModeHtml = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                        } else if (m.wildcardMode === 1) {
                            wildcardModeHtml = LANG.UI_FILE_WILDCARD_BAK_FILTER;
                        } else {
                           wildcardModeHtml = LANG.UI_FILE_WILDCARD_BAK_SELECT;
                        }

                        wildcardListHtml += `${LANG.UI_FILE_WILDCARD_BAK_MODE}: ${wildcardModeHtml};<br>`;
                    }
                })
            }
        });

        // 确认配置 - 备份文件列表 和 通配符
        $('.control-label-wildcard').html(`${$('.wildcardlabel').html()}: `);
        $('.form-control-wildcard-list').html(wildcardListHtml);
        $('#file_list').html(backupFileListWrapper);

        // 所有对象存储都隐藏权限备份
        $('.file-permission-div').hide();

        // 判断是否有选择AWS的对象存储，否的话则隐藏 权限备份
        // if (ajaxData.srcInfo.groupList.filter(i => { return i === OBS_GROUP_ID.AWS}).length === 0) {
        //     $('.file-permission-div').hide();
        // } else {
        //     $('.file-permission-div').show();
        // }
    }

    /**
     * 获取显示传输网络标志
     * @param agent
     * @returns {boolean}
     */
    const getNetworkFlag = (agent) => {
        for(let i= 0; i < agent.length; i++){
            //客户端连接服务端
            if(agent[i].net_model === 2){
                networkFlag = true;
            }
        }

        return networkFlag;
    }

    const checkChildFile = (filelist) => {
        let allfilestate = [];
        filelist.forEach(eachAgent => {
            let filestate = [];
            eachAgent.forEach(item => {
                if (item.check_Child_State === -1) {
                    filestate.push(item.check_Child_State);
                }
            });
            allfilestate.push(filestate);
        });

        let result = allfilestate.some(eacharr => {
            return eacharr.length === 0;
        });

        return result;
    }

    /**
     * 初始化策略描述信息
     */
    const initStrategyDes = () => {
        initStoreStrategyDes();
        initReserveStrategyDes();
        initHighStrategyDes();//初始化高级策略
        initVerifyStrategyDes(); // 初始化安全策略描述
    }

    /**
     * 授权判断,type: a 表示 容量/数量授权, module: obs 表示对象存储模块 
     */
    const licenseCheck = () => {
        return new Promise((resolve) => {
            let params = {
                module: 'obs',
                currentUse: ajaxData.srcInfo.agentList.length,
                showMetronic: false,
                judge: true,
                showAlertMsg: true,
				uuids: ajaxData.srcInfo.agentList,
                task_uuid: EDIT_BACKUP_FLAG ? taskInfoSetting.job_uuid : ''
            }
            getModuleAuthInfo(params).then(result => {
                if (result) {
                    resolve(true);
                }
            });
        });   
    }

    /**
     * 步骤一 - 备份源校验
     * @returns {boolean}
     */
    const step1Valid = (showTitleCallback) => {
        ajaxData.srcInfo.fileInfo = [];
		ajaxData.highInfo.newstr.wildcardList = [];

        let allBackupSourceNodes = [];
        if (EDIT_BACKUP_FLAG) { // 修改备份任务时获取所有勾选的节点数组（包含禁用勾选的）
            allBackupSourceNodes = zTree.transformToArray(zTree.getNodes());
            allBackupSourceNodes.forEach(item => {
                if(item.chkDisabled) {
                    item.chkDisabled = false; // 取消chkDisabled，使getCheckedNodes获取到离线的对象存储
                }
            });
        }

        let nodes = zTree.getCheckedNodes(true);
        let checkChildFileArr = [];
        let checkoutFlag = true; // 通配符校验 false：未校验通过 true：校验通过
        let filecheck = false; // 判断每个对象存储是否都选择了文件 true:未选择 false:已选择

        // 是否需要显示传输网络标记
        // networkFlag = false;
		// networkFlag = getNetworkFlag(nodes);

        if (EDIT_BACKUP_FLAG) {
            // 组合离线状态的对象存储所选择的通配符数组
            nodes = nodes.map(i => {  
                if (i.isOfflineNode) {  
                    // 对checkedOfflineObsWildcards进行过滤并获取第一个匹配项  
                    const matchedWildcardItem = checkedOfflineObsWildcards.find(v => v.obs_uuid === i.id);  
              
                    // 如果找到了匹配的项，则扩展i对象并添加wildcardList和wildcardMode属性  
                    if (matchedWildcardItem) {  
                        return {  
                            ...i,  
                            wildcardList: matchedWildcardItem.wildcardList,  
                            wildcardMode: matchedWildcardItem.wildcardMode,  
                        };  
                    }  
                }  
              
                // 如果没有找到匹配的项或者i不是离线节点，则直接返回i  
                return i;  
            });
        }

        // 遍历树
        nodes.forEach(item => {
            if (!item.isOfflineNode) { // 非禁用节点
                if (item.type === "obsItem" && zTreeFile[item.id] !== undefined && zTreeFile[item.id].getCheckedNodes(true).length !== 0) {

                    ajaxData.srcInfo.fileInfo.push(zTreeFile[item.id].getCheckedNodes(true));
                    //用于检测应用到其他客户端  在其他客户端一个文件都未找到时，只选中父级的情况
                    checkChildFileArr.push(zTreeFile[item.id].getCheckedNodes(true));
                } else if (item.type === "obsItem" && zTreeFile.length === 0 || (zTreeFile[item.id] !== undefined && zTreeFile[item.id].getCheckedNodes(true).length === 0)) {
                    filecheck = true; // 判断每个客户端是否都选择了文件
                }
            }
            
            // 通配符校验
            if (item.type === "obsItem") {
                let wildcardInput = []; // 所有通配符
                let wildcardMode = ''; // 通配符匹配模式

                if (!item.isOfflineNode) { // 非禁用节点
                    let wildcardInputLength = $(`#high_tabagent_tree_${item.id}`).find('span.form-group-wildcards__item__text').length;
                    let wildcardmodeVal = parseInt($(`#high_tabagent_tree_${item.id}`).find('.wildcardmode').val());

                    if (wildcardmodeVal !== 0 && wildcardInputLength === 0 && !item.isOfflineNode) {
                        UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
                        checkoutFlag = false;
                        return false;
                    }

                    for(let i= 0; i < $(`#high_tabagent_tree_${item.id}`).find('span.form-group-wildcards__item__text').length; i++) {
                        let str = $.trim($(`#high_tabagent_tree_${item.id}`).find('span.form-group-wildcards__item__text').eq(i)[0].innerText);
                        if(str !== "") {
                            //通配符输入不能包含特殊符号,不包含  /  :  "  <  >  | \
                            let specialchar = ['/', ':','"','<','>','|','\\'];
                            for (let key in specialchar) {
                                if (str.indexOf(specialchar[key]) !== -1) {
                                    UIToastr.showWarning(LANG.UI_FILE_WILDCARD_RULES_TIPS1);
                                    checkoutFlag = false;
                                    return false;
                                }
                            }
                            // 不允许*和？相邻时 输入*在前？在后的情况
                            if (str.indexOf("*?") !== -1){
                                UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS2);
                                checkoutFlag = false;
                                return false;
                            }
                        } else if ($(`#high_tabagent_tree_${item.id}`).find('.wildcardmode').val()!=0){
                            UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
                            checkoutFlag = false;
                            return false;
                        }
                        wildcardInput.push(str);
                    }

                    wildcardMode = parseInt($(`#high_tabagent_tree_${item.id}`).find('.wildcardmode').val());
                } else { // 禁用且勾选的节点
                    wildcardInput = item.wildcardList;
                    wildcardMode = item.wildcardMode
                }
                

                // 接口参数赋值
                ajaxData.highInfo.newstr.wildcardList.push({
                    obsuuid: item.id, // 对象存储uuid
                    wildcardList: wildcardInput, // 通配符list
                    wildcardMode: wildcardMode // 通配符备份方式
                });
            }
        });

        if (!filecheck) {
            // 校验对象存储目录树子级是否被选择，false为已选择
            filecheck = checkChildFile(checkChildFileArr);
        }

        if(!checkoutFlag) return false;

        if (nodes.length === 0 || ajaxData.srcInfo.agentList.length === 0) {
            UIToastr.showWarning(LANG.UI_OBS_NO_CHOOSE_BACKUP_PROXY, LANG.UI_OBS_NO_CHOOSE_BACKUP_PROXY_TIPS);
			return false;
        }

        if(filecheck){//检查是否每个已选中客户端都选中了文件
            UIToastr.showWarning(LANG.UI_BACKUP_OBS_NO_SELECT_TITLE, LANG.UI_BACKUP_OBS_NO_SELECT_VALUE);
            return false;
        }

        // 初始化时间策略模块
		Strategy.initModule(ObsBackup);
        //初始化策略描述
        initStrategyDes();
        confirmStep1Config();
        licenseCheck().then(showTitleCallback); 

        if (EDIT_BACKUP_FLAG) {
            allBackupSourceNodes.forEach(item => {
                if(item.isOfflineNode) { // 如果是禁用且已勾选节点则重置chkDisabled
                    item.chkDisabled = true;
                }
            });
        }

        return false;
    }

    // <-------------------------   END STEP ONE BACKUP SOURCE  ------------------------------------->


    // <-------------------------   BEGIN STEP TWO BACKUP DESTINATION   ------------------------------>

    /**
     * 存储下拉change事件
     */
    const storageTypeChangeEvent = () => {
        let type = parseInt($('#selectstorage').find('option:selected').attr('storage_type'));

        if (type === CONF.BD_STORAGE_TYPE.TAPE) {
            $('#backupThreadNum').val(1).prop('disabled', true);

            $('.form-group-panel-reserve').hide();
            $('.threadDiv').hide();
            $('.backupThreadDiv .spinner-group-btn .btn').prop('disabled', true);
        } else {
            if (EDIT_BACKUP_FLAG) { // 修改备份切换时保证保留策略数值不为0
                if (taskInfoSetting.brs.type === CONF.RESERVE_TYPE.NUM) { // 按个数
                    if (!taskInfoSetting.brs.number) {
                        $('#spinnerNumInput').val(30);
                    } else {
                        $('#spinnerNumInput').val(taskInfoSetting.brs.number);
                    }

                    ajaxData.highInfo.reserve.value = $('#spinnerNumInput').val();
                } else { // 按天数
                    if (!taskInfoSetting.brs.number) {
                        $('#spinnerDayInput').val(30);
                    } else {
                        $('#spinnerDayInput').val(taskInfoSetting.brs.number);
                    }

                    ajaxData.highInfo.reserve.value = $('#spinnerDayInput').val();
                }
            } else {
                $('#spinnerDayInput').val(30);
                let type = parseInt($('#reserveType').val());

                if (CONF.RESERVE_TYPE.NUM === type) { // 按个数
                    $('#spinnerNumInput').val(30);
                } else { //按天数
                    $('#spinnerDayInput').val(30);
                }
            }

            $('.form-group-panel-reserve').show();
            $('.threadDiv').show();
            $('#backupThreadNum').prop('disabled', false);
            $('.backupThreadDiv .spinner-group-btn .btn').prop('disabled', false);
            initReserveStrategyDes();
        }

        ajaxData.highInfo.newstr.backupThreadNum = CONF.FUNCTIONS.includes('multithread') ? parseInt($('#backupThreadNum').val()) : 1; // 修改备份任务步骤二直接提交场景(未授权时值为1)

        initHighStrategyDes();
    }

    /**
     * 获取目标存储下拉列表
     */
    const initStorageSelect = () => {
        let param = {};
        param.node_uuid = $('#selectnode').val();
        param.exchange_cloud_flag = true; // 用于查云存储

        pAjaxRequest(param, "api/v1/storages/backup", "GET", (result) => {
            if (result.success) {
               let data = result.data;
               let softselect = $('#selectstorage');
               softselect.empty();

               if (data.length > 0) {
                   storageList = [];
                   for(let i= 0; i < data.length; i++){
                       let option = $("<option>").text(data[i].text).val(data[i].storage_uuid).attr("storage_type", data[i].storage_type);
                       softselect.append(option);
                       storageList.push({...data[i]});
                   }

                   if (EDIT_BACKUP_FLAG) {
                        if (storageList.findIndex(i => i.storage_uuid === taskInfoSetting.node.storage_uuid) > -1) {
                            $('#selectstorage').val(taskInfoSetting.node.storage_uuid);
                            
                            // 手动触发一次存储下拉select change，以便于切换节点时第一个存储恰好为磁带存储，传输线程置为1
                            storageTypeChangeEvent();
                        }
                   }
               }
            }
        });
    }

    /**
     * 初始化目标存储资源池和计算节点资源池
     */
    const initTargetStorageAndNode = () => {
        $('#backupTarget').backupTarget();
    };

    /**
     * 初始化已选择的节点和存储
     */
    const initCheckedNodeAndStorage = () => {
        $('#backupTarget').backupTarget({
			node_uuid: taskInfoSetting.node.node_uuid,
			node_pool_uuid: taskInfoSetting.node.node_pool_uuid,
			storage_uuid: taskInfoSetting.node.storage_uuid,
			storage_pool_uuid: taskInfoSetting.node.storage_pool_uuid,
			storage_pool_type: taskInfoSetting.node.storage_pool_type,
		});
    }
    /**
     * 初始化节点下拉列表
     */
    const initNodeSelect = () => {
        if (nodeSelectFlag) {
            return;
        }

        pAjaxRequest({}, "/api/v1/nodes/select", "GET", (result) => {
            if (result.success) {
                let data = result.data;
                let softselect = $('#selectnode');

                if (data.length > 0) {
                    softselect.empty();
                    for(let i= 0; i < data.length; i++){
                        let option = $("<option>").text(data[i].text).val(data[i].uuid);
                        softselect.append(option);
                    }
                    // 修改备份给节点赋默认值
                    if (EDIT_BACKUP_FLAG) {
                        $('#selectnode').val(taskInfoSetting.node.node_uuid);
                    }

                    nodeSelectFlag = true;
                    initStorageSelect();
                }
            }
        });
    }

    /**
     * 初始化节点传输网络列表
     * @param {*} nodeUUID 节点uuid
     */
    const initNetworkList = (nodeUUID) => {
        let data = {
            nodes_uuid: nodeUUID
        }

        pAjaxRequest(data, '/api/v1/nodes/network_card', 'GET', (result) => {
            if (result.success) {
                $('#transferNetwork').empty();
                let networkList = result.data;

                if (networkList && networkList.length > 0) {
                    networkList.forEach(item => {
                        let name = `${item.ip}:${item.port}`;

                        if (item.alias_name) {
                            name += `(${item.alias_name})`;
                        }

                        let option = `<option value="${item.network_uuid}">${name}</option>`;
                        $('#transferNetwork').append(option);
                    });

                    if (EDIT_BACKUP_FLAG) {
                        // 传输网络
                        $('#transferNetwork').val(taskInfoSetting.bts.network);
                    }
                }
            }
        })
    }

    /**
     * 初始化传输代理列表
     */
    const initApplianceAgencyOLD = () => {
        pAjaxRequest({offset: 0, limit: 1000, transfer_agent_flag: 1, h_online_status: 1}, 'api/v1/agents', 'GET', (result) => {
             if (result.success) {
                 if (result.data.rows.length > 0) {
                     let data = result.data.rows;
                     let applianceSelect = $('#applianceSelect');
 
                     applianceSelect.empty();
             
                     for (let i = 0; i < data.length; i++){
                         let option = $("<option>").text(data[i].agent_ip).val(data[i].agent_uuid);
                         applianceSelect.append(option);
                     }

                     APPLIANCE_AGENCY_HAS_CONFIGED = true;

                     if (EDIT_BACKUP_FLAG) {
                        if (taskInfoSetting.bts.appliance_agency_flag) {
                            $('.applianceselectdiv').show();
                            $('#applianceSelect').val(taskInfoSetting.bts.appliance_uuid);
                        } else {
                            if ($('#appliance_agency_flag').get(0).checked) { // 虽然数据库中taskInfoSetting.bts.appliance_agency_flag是false，但是如果已经开启了再点上一步 下一步的场景还是要开启
                                $('.applianceselectdiv').show();
                            } else {
                                $('.applianceselectdiv').hide();
                            }
                        }
                     }
                 } else {
                    APPLIANCE_AGENCY_HAS_CONFIGED = false;
                 }
             } else {
                APPLIANCE_AGENCY_HAS_CONFIGED = false;

                UIToastr.showWarning(LANG.UI_OBS_GET_APPLIANCE_AGENCY_FAILED, result.message);
             }
        });
    }

    /**
     * 初始化传输代理
     */
    const initApplianceAgency = () => {
        if (EDIT_BACKUP_FLAG) {
            $('#transferAgentTree').transferAgent({
                agent_uuid: taskInfoSetting.bts.appliance_uuid,
                agent_pool_uuid: taskInfoSetting.bts.appliance_pool_uuid.agent_pool_uuid,
            });
        } else {
            $('#transferAgentTree').transferAgent();// 传输代理资源池
        }
    }

    /**
     * 根据磁带存储uuid获取磁带组策略
     * @param {*} storageUUID 
     */
    const initTapeStrategy = (storageUUID) => {
        pAjaxRequest({ group_uuid: storageUUID }, '/api/v1/tapes/group/strategy', 'GET', (result) => {
            $('.form-group-tape-strategy__value.generate-strategy').html(result.data.backup_set_strategy_des);
            $('.form-group-tape-strategy__value.reserve-strategy').html(result.data.reserve_strategy_des);
        })
    }

    /**
     * 步骤二校验通过后
     */
    const confirmStep2Config = () => {
        let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
        let permanentIncreChecked = $('#pincrBackup').is(':checked'); // 永久增量勾选状态，如果已经勾选，那么worm防护开关就要禁用，无论选择何种存储

        if (!permanentIncreChecked) {
            // WORM防护禁用逻辑
            if (!backupTargetInfo.storage_uuid) { // 如果选择的是存储资源池则禁用worm防护，且开关保持关闭
                $('#worm_protect_check').bootstrapSwitch('state', false);
                $('#worm_protect_check').bootstrapSwitch('disabled', true);

                $('.form-group-unselected-storage').removeClass('display-none');
                $('.form-group-unworm-config').addClass('display-none');
            } else { // 如果选择的是存储设备
                if (backupTargetInfo.storage_worm_config.flag) { // 存储设备开启了worm防护，则worm不禁用
                    $('#worm_protect_check').bootstrapSwitch('disabled', false);

                    $('.form-group-unselected-storage').addClass('display-none');
                    $('.form-group-unworm-config').addClass('display-none');
                } else { // 存储设备未开启worm防护，则worm关闭且禁用
                    $('#worm_protect_check').bootstrapSwitch('state', false);
                    $('#worm_protect_check').bootstrapSwitch('disabled', true);

                    $('.form-group-unselected-storage').addClass('display-none');
                    $('.form-group-unworm-config').removeClass('display-none');
                }
            }
        } else {
            $('#worm_protect_check').bootstrapSwitch('state', false);
            $('#worm_protect_check').bootstrapSwitch('disabled', true);
        }

        selectedStorageType = backupTargetInfo.storage_type;

        // 记录系统 同时未授权worm配置和完整性校验配置 标记
        let UN_AUTHED_WORM_AND_INTEGRALITY_FLAG = !CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity');

        switch (selectedStorageType) {
            case CONF.BD_STORAGE_TYPE.TAPE: // 选的存储类型为磁带时
                $('.form-group-panel-reserve').hide(); // 隐藏保留策略

                $('.threadDiv').hide(); // 隐藏传输线程
                $('#backupThreadNum').val(1).prop('disabled', true); // 传输线程数字输入框禁用并赋值1
                $('.backupThreadDiv .spinner-group-btn .btn').prop('disabled', true); // 传输线程数字输入框按钮禁用

                $('.form-group-panel-tape').show(); // 显示磁带组策略panel
                initTapeStrategy(backupTargetInfo.storage_uuid);

                // 磁带存储屏蔽安全策略
                $('.safetyLi').hide();
                $('#tab_safety').addClass('display-none');
                break;
            case CONF.BD_STORAGE_TYPE.CLOUD: // 选的存储类型为云存储时
                $('.form-group-panel-tape').hide(); // 隐藏磁带组策略panel
                
                initReserveStrategyDes();

                $('.form-group-panel-reserve').show(); // 显示保留策略
                $('.threadDiv').show(); // 显示传输线程
                $('#backupThreadNum').prop('disabled', false); // 传输线程数字输入框取消禁用
                $('.backupThreadDiv .spinner-group-btn .btn').prop('disabled', false); // 传输线程数字输入框按钮取消禁用

                if (UN_AUTHED_WORM_AND_INTEGRALITY_FLAG) { // 若系统未授权worm和完整性校验仍不展示安全策略tab
                    $('.safetyLi').hide();
                    $('#tab_safety').addClass('display-none');
                } else {
                    $('.safetyLi').show();
                    $('#tab_safety').removeClass('display-none');
                }
                
                break;
            default:
                $('.form-group-panel-tape').hide(); // 隐藏磁带组策略panel

                $('.form-group-panel-reserve').show(); // 显示保留策略
                $('.threadDiv').show(); // 显示传输线程
                $('#backupThreadNum').prop('disabled', false); // 传输线程数字输入框取消禁用
                $('.backupThreadDiv .spinner-group-btn .btn').prop('disabled', false); // 传输线程数字输入框按钮取消禁用

                if (UN_AUTHED_WORM_AND_INTEGRALITY_FLAG) { // 若系统未授权worm和完整性校验仍不展示安全策略tab
                    $('.safetyLi').hide();
                    $('#tab_safety').addClass('display-none');
                } else {
                    $('.safetyLi').show();
                    $('#tab_safety').removeClass('display-none');
                }

                break;
        }

        // Ajax data 赋值
        ajaxData.highInfo.node.node_uuid = backupTargetInfo.node_uuid;
        ajaxData.highInfo.node.node_pool_uuid = backupTargetInfo.node_pool_uuid;
        ajaxData.highInfo.node.storage_uuid = backupTargetInfo.storage_uuid;
        ajaxData.highInfo.node.storagecheck = !ajaxData.highInfo.node.storage_uuid;
        ajaxData.highInfo.node.storage_pool_uuid = backupTargetInfo.storage_pool_uuid;

        $('.confirm-backup-target-node').html(backupTargetInfo.node_text);
        $('.confirm-backup-target-storage').html(backupTargetInfo.storage_text);
        //初始化传输网络
        // $('.transfernetworkDiv').show();

        $('.deduplicationDiv').hide();

        initNetworkList(backupTargetInfo.node_uuid);
        initApplianceAgency(); // 获取代理传输组件
        initResourceLimit(backupTargetInfo.node_uuid_list);// 高级配置 - 过载保护 显示资源的配置信息
    }

    /**
     * 步骤二 - 备份目的地校验
     * @returns {boolean}
     */
    const step2Valid = () => {
        // 1.校验
        if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}

        confirmStep2Config();

        return true;
    }
    // <-------------------------END STEP TWO BACKUP DESTINATION-------------------------------->


    // <-------------------------BEGIN STEP THREE BACKUP STRATEGY------------------------------->
    /**
     * 获取时间策略描述
     */
    const initTimeStrategyDes = () => {
        let des = "";
        let diffDes = "";
        //备份
        let backupType = $('#backupType').val();
        let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
        if(!strategyConfig.fullInfo) return;
        let timeStrategyMode = $('#strategymode').find('input:checked');
        let strategyIndex = $('#strategySelect').val();

        switch (backupType) {
            case 'strategy': // 按策略备份
                for(let i= 0; i < timeStrategyMode.length; i++){
                    if(1 === $(timeStrategyMode[i]).data('mode')){
                        des += strategyConfig.fullInfo.des + ". ";
                        diffDes += strategyConfig.fullInfo.des + "<br>";
                    }else if(2 === $(timeStrategyMode[i]).data('mode')){
                        des += strategyConfig.incrInfo.des + ". ";
                        diffDes += strategyConfig.incrInfo.des + "<br>";
                    }else if(3 === $(timeStrategyMode[i]).data('mode')){
                        des += strategyConfig.diffInfo.des + ". ";
                        diffDes += strategyConfig.diffInfo.des + "<br>";
                    }
                }

                break;
            case 'oncetime': // 一次性备份
                des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
                diffDes += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();

                break;
            case 'manual': // 手动备份
                des += LANG.UI_BACKUP_MANUAL;

                break;
            default:
                break;
        }

        if(strategyIndex && strategyIndex !== "" && EDIT_BACKUP_FLAG){
            let oldDes = globalStrategy[strategyIndex].time.des;
            initStrategyDesStyle($('.backupTimeDes'), diffDes, oldDes);
        }else{
            $('.backupTimeDes').removeClass('font-green-seagreen');
        }

        $('.backupTimeDes').html(des);
        $('.backupTimeDes').prop('title', des);
    }

    /**
     * 策略设置 iCheck box 的 click事件
     * @param event
     */
    const strategyModeClick = function (event) {
        let mode = $(this).data('mode');
        let setDisabled = "disable";

        if (event.target.checked){
            //如果是取消选中
            $('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').hide();
            setDisabled = "enable";
        } else {
            //如果是选中
            $('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').show();
            setDisabled = "disable";
        }
        if (2 === mode){
            //增量备份
            $('#strategymode').find('input[data-mode=3]').iCheck(setDisabled);
        } else if (3 === mode){
            //差异备份
            $('#strategymode').find('input[data-mode=2]').iCheck(setDisabled);
        }
    }

    /**
     * 校验周期 mode change
     */
    const verifyModeChange = function() {
        let verifyCycleMode = parseInt($(this).data('mode'));
        let backupPointAbnormalMode = parseInt($('#backup_point_abnormal').find('.iradio_square-blue.checked').find('input').attr('data-mode'));
        $('.verify-strategy-desc').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD}：${VERIFY_CYCLE_DESC_MAP[verifyCycleMode]}；${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_ABNORMAL}：${BACKUP_POINT_ABNORMAL_DESC_MAP[backupPointAbnormalMode]}`);
    }

    /**
     * 完整性校验 mode change
     */
    const backupPointModeChange = function() {
        let backupPointAbnormalMode = parseInt($(this).data('mode'));
        let verifyCycleMode = parseInt($('#check_cycle').find('.iradio_square-blue.checked').find('input').attr('data-mode'));
        // $('.verify-strategy-desc').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD}：${VERIFY_CYCLE_DESC_MAP[verifyCycleMode]}；${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_ABNORMAL}：${BACKUP_POINT_ABNORMAL_DESC_MAP[backupPointAbnormalMode]}`);
        $('.verify-strategy-desc').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_ABNORMAL}：${BACKUP_POINT_ABNORMAL_DESC_MAP[backupPointAbnormalMode]}`);
    }

    /**
     * 创建备份 - 获取任务分布区间图和各个备份策略accordion
     */
    const initTimeStrategy = () => {
        pAjaxRequest({}, "/api/v1/jobs/time_crow_list", "GET", (result) => {
            if (result.success) {
                let data = result.data;
                let suggestInfo = data.suggest_time;

                if (data.time_list.length > 0) {
                    $('#backupCrowd').taskCrowd({timeList: data.time_list, showFlag: data.show_flag});
                }

                defaultStrategy[0] = { // 完备
                    mode: 1,
                    strategy_type: 2,
                    days: [0, 0, 0, 0, 1, 0, 0],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time
                };

                defaultStrategy[1] = { // 增备
                    mode: 2,
                    strategy_type: 1,
                    days: [],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time
                };

                defaultStrategy[2] = { // 差备
                    mode: 3,
                    strategy_type: 1,
                    days: [],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time
                };

                defaultStrategy[3] = { // 永久增量
                    mode: 9,
                    strategy_type: 1,
                    days: [],
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time,
                };

                //延迟设置,因为这里icheck会默认修改里面的选中事件
                setTimeout(function(){
                    $('#backupTimestrategy').strategy({dom: $('#backupTimestrategy'), config: defaultStrategy, display:['display-none', 'display-none', 'display-none', 'display-none'], backup_flag: 1});
                }, 2000);
            }
        })
    }

    /**
     * 修改备份 - 获取任务分布区间图
     */
    const initCheckedTaskCrowList = () => {
        pAjaxRequest({}, "/api/v1/jobs/time_crow_list", "GET", (result) => {
            if (result.success) {
                let data = result.data;
                let suggestInfo = data.suggest_time;
                let timeStrategyBackupType = taskInfoSetting.time_strategy_backup_type;

                if (data.time_list.length > 0) {
                    $('#backupCrowd').taskCrowd({timeList: data.time_list, showFlag: data.show_flag});
                }

                defaultStrategy[0] = { // 完全备份
                    mode: 1,
                    strategy_type: 2,
                    days: [0, 0, 0, 0, 1, 0, 0],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time
                };

                defaultStrategy[1] = { // 增量备份
                    mode: 2,
                    strategy_type: 1,
                    days: [],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time
                };

                defaultStrategy[2] = { // 差异备份
                    mode: 3,
                    strategy_type: 1,
                    days: [],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time
                };

                defaultStrategy[3] = { // 永久增量
                    mode: 9,
                    strategy_type: 1,
                    days: [],
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time,
                };
            }
        })
    }

    /**
     * 修改备份 - 获取任务分布区间图和各个备份策略accordion
     */
    const initCheckedTimeStrategy = () => {
        // 获取timeStrategy信息
        let timeStrategy = taskInfoSetting.time_strategy;
        let timeStrategyBackupType = taskInfoSetting.time_strategy_backup_type;
        let des = '';

        switch (timeStrategyBackupType) {
            case TIME_BACKUP_STRATEGY_TYPE.STRATEGY: // 按策略备份
                $('#backuptype').val('strategy'); // 设置备份方式

                $('.setStrategy').show();
                $('.setOnceTime').hide();

                let timeStrategyConfigurations = [
                    {
                        mode: 1,
                        strategy_type: 2,
                        days: [0, 0, 0, 0, 1, 0, 0],
                        frequency: '',
                        start_time: '23:00:00',
                        roll_flag: false,
                        roll_interval: '01:00:00',
                        roll_end_time: '23:59:59'
                    },
                    {
                        mode: 2,
                        strategy_type: 1,
                        days: [],
                        frequency: '',
                        start_time: '23:00:00',
                        roll_flag: false,
                        roll_interval: '01:00:00',
                        roll_end_time: '23:59:59'
                    },
                    {
                        mode: 3,
                        strategy_type: 1,
                        days: [],
                        frequency: '',
                        start_time: '23:00:00',
                        roll_flag: false,
                        roll_interval: '01:00:00',
                        roll_end_time: '23:59:59'
                    },
                    {
                        mode: 9,
                        strategy_type: 1,
                        days: [],
                        frequency: '',
                        start_time: '23:00:00',
                        roll_flag: false,
                        roll_interval: '01:00:00',
                        roll_end_time: '23:59:59'
                    }
                ]; // 配置的时间策略
                let displayArr = ['display-none', 'display-none', 'display-none', 'display-none'];
    
                if (timeStrategy.data && timeStrategy.data.length > 0) {
                    timeStrategy.data.forEach(item => {
                        if (!item.roll_flag) {
                            item.roll_interval = '01:00:00';
                        }

                        switch (item.mode) {
                            case 1: // 完全备份
                                timeStrategyConfigurations[0] = { ...item };
                                displayArr[0] = '';
                                break;
                            case 2: // 增量备份 || 永久增量
                                // 永久增量和增量备份后台存的MODE都是2，如果timeStrategy.data只有一条即为永久增量，否则为增量备份
                                if (timeStrategy.data.length === 1) {
                                    // 后台返回的mode是2，为方便插件处理改成对应的永久增量9
                                    timeStrategyConfigurations[3] = Object.assign({ ...item }, { mode: 9 });
                                    displayArr[3] = '';
                                } else {
                                    timeStrategyConfigurations[1] = { ...item };
                                    displayArr[1] = '';
                                }
                                break;
                            case 3: // 差异备份
                                timeStrategyConfigurations[2] = { ...item };
                                displayArr[2] = '';
                                break;
                            case 9: // 永久增量
                                timeStrategyConfigurations[3] = { ...item };
                                displayArr[3] = '';
                                break;
                            default:
                                break;
                        }
                    });
                }
               
                // 初始化时间策略插件
                $('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: timeStrategyConfigurations, display: displayArr, backup_flag: 1 });
                
                // 获取时间策略配置
                let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
    
                // 策略 accordion 的显隐
                timeStrategy.data.forEach(item => {
                    $('#stragegyaccordion').find('.strategy-panel[data-mode=' + item.mode + ']').show();
                    
                    switch (item.mode) {
                        case 1: // 完全备份
                            des += strategyConfig.fullInfo.des + ". ";
                            $('#fullBackup').iCheck('check');

                            break;
                        case 2: // 增量备份 || 永久增量
                            // 永久增量和增量备份后台存的MODE都是2，如果timeStrategy.data只有一条即为永久增量，否则为增量备份
                            if (timeStrategy.data.length === 1) {
                                des += strategyConfig.pIncrInfo.des + ". ";
                                $('#pincrBackup').iCheck('check');
                                
                                initReserveStrategyDes();
                            } else {
                                des += strategyConfig.incrInfo.des + ". ";
                                $('#incrBackup').iCheck('check');
                                $('#fullBackup').iCheck('check');
                            }
                            
                            break;
                        case 3: // 差异备份
                            des += strategyConfig.diffInfo.des + ". ";
                            $('#diffBackup').iCheck('check');
                            $('#fullBackup').iCheck('check');

                            break;
                        case 9: // 永久增量
                            des += strategyConfig.pIncrInfo.des + ". ";
                            $('#pincrBackup').iCheck('check');
                            
                            initReserveStrategyDes();
                            break;
                        default:
                            break;
                    }
                });

                break;
            case TIME_BACKUP_STRATEGY_TYPE.ONCETIME: // 一次性备份
                $('#backuptype').val('oncetime'); // 设置备份方式

                des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + timeStrategy.data;
                $('.setStrategy').hide();
                $('.setOnceTime').show();
                $('#oncetime').val(timeStrategy.data);
                $('#reserveType').prop('disabled', true);
                $('#spinnerNum').spinner('disable');
                $('#spinnerDay').spinner('disable');

                break;
            case TIME_BACKUP_STRATEGY_TYPE.MANUAL: // 手动备份
                $('#backuptype').val('manual'); // 设置备份方式

                des += LANG.UI_BACKUP_MANUAL;
                $('.setStrategy').hide();
                $('.setOnceTime').hide();

                break;
            default:
                break;
        }

        // 策略 accordion 的描述信息
        $('.backupTimeDes').html(des);
        $('.backupTimeDes').attr('title', des);
    }

    /**
     * 修改备份 - 初始化选择的限速策略
     */
    const initCheckedSpeedLimitStrategy = () => {
        let titleDes = '', des = '';
        $('#tasklevelselect').val(taskInfoSetting.speedInfo.level); // 任务级别
        $('#speedtypeselect').val(taskInfoSetting.speedInfo.type); // 策略方式

        if (taskInfoSetting.speedInfo.type === 1) { // 全局策略
            $('#show_type_1').show();
            $('#show_type_2').hide();
            $('#task_type_global_speed_strategy').show();

            let globalSpeedLimit = taskInfoSetting.speedInfo.uuid;
            $(".strategy-table #table").bootstrapTable('checkBy', {
                field: 'uuid',
                values: [globalSpeedLimit]
            });

            // 初始化全局限速策略表格
            let rowData = $(".strategy-table #table").bootstrapTable("getData");
            let datas = [];
            for(let j in rowData) {
                if (rowData[j].uuid == globalSpeedLimit) {
                    datas = rowData[j].detail;
                    datas = datas.split('</br>');
                }
            }

            if(datas.length > 0){
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + datas.length;
            }

            for(let i in datas){
                titleDes += datas[i] + '. ' + "<br>";
            }
        } else { // 自定义策略
            $('#show_type_2').show();
            $('#show_type_1').hide();
            $('#task_type_global_speed_strategy').hide();

            if (taskInfoSetting.speedInfo.speedInfo.length > 0) {
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + taskInfoSetting.speedInfo.speedInfo.length;

                taskInfoSetting.speedInfo.speedInfo.forEach(item => {
                    titleDes += `${item.des}. <br>`;
                });

                addGlobalStrategy.init({'strategy_type': taskInfoSetting.speedInfo.strategy_type, speedInfo: taskInfoSetting.speedInfo.speedInfo, initSpeedFlag: 1});
            }
        }

        $('.speedlimitDes').html(des);
        $('.speedlimitDes').prop('title', titleDes);
    }

    /**
     * 初始化各个数字输入框
     */
    const initSpinner = () => {
        initReserveSpinner($('#spinnerDay'));
        initReserveSpinner($('#spinnerNum'));
        $('#speedSpinnerNum').spinner({value: 10, step: 5, min: 1, max: 10000000000});
        $('.backupThreadDiv').spinner({value: 3, step: 1, min: 1, max: 32});
        $('.scanThreadDiv').spinner({value: 3, step: 1, min: 1, max: 32});
        $('#spinnerpercent').spinner({value: 20, step: 5, min: 1,max: 100});//跳过文件告警比例
        $('.passfilenumDiv').spinner({value: 10, step: 5, min: 1, max: 999999999});//跳过文件告警个数
        $('#worm_protect_term_spinner_group').spinner({value: 7, step: 1, min: 1, max: 999999999}); // WORM保护期限
        
        $('#network_retry_times_spinner').spinner({value: 10, step: 5, min: 1, max: 60}); // 网络重连次数
        $('#network_retry_interval_spinner').spinner({value: 60, step: 5, min: 5, max: 60}); // 网络重连间隔时间
        $('#op_retry_times_spinner').spinner({value: 3, step: 1, min: 1, max: 5}); // 操作重连次数
        $('#op_retry_interval_spinner').spinner({value: 60, step: 5, min: 5, max: 60}); // 操作重连间隔时间
        $('#task_retry_times_spinner').spinner({value: 3, step: 1, min: 1, max: 5}); // 任务重连次数
        $('#task_retry_interval_spinner').spinner({value: 10, step: 5, min: 1, max: 60}); // 任务重连间隔时间
    }

    /**
     * 备份方式 select change 
     */
    const backupTypeHandler = function () {

        ajaxData.backupInfo.type = this.value;
        
        switch (this.value) {
            case 'strategy': //按策略备份
                $('#fullBackup').iCheck('uncheck');
                $('#incrBackup').iCheck('uncheck');
                $('#diffBackup').iCheck('uncheck');
                $('#pincrBackup').iCheck('uncheck');
                $('.setStrategy').show();
                $('.setOnceTime').hide();
                $('#reserveType').prop('disabled', false);
                $('#spinnerNum').spinner('enable');
                $('#spinnerDay').spinner('enable');
                // $('#spinnerNum').spinner('value', 30);
                // $('#spinnerDay').spinner('value', 30);

                // 重置时间策略描述信息
                $('.backupTimeDes').html('');
                $('.backupTimeDes').prop('title', '');

                // 重置设置时间策略accordion
                $('#backupTimestrategy').strategy({dom: $('#backupTimestrategy'), config: defaultStrategy, display:['display-none', 'display-none', 'display-none', 'display-none'], backup_flag: 1});

                break;
            case 'oncetime': //一次性备份
                $('.setStrategy').hide();
                $('.setOnceTime').show();
                $('#spinnerNum').spinner('disable');
                $('#spinnerDay').spinner('disable');
                $('#spinnerNum').spinner('value', 1);
                $('#spinnerDay').spinner('value', 1);
                $('#reserveType').prop('disabled', true);

                // 重置时间策略描述信息
                $('.backupTimeDes').html('');
                $('.backupTimeDes').prop('title', '');

                // 重置时间
                $('#oncetime').val('');

                break;
            case 'manual': // 手动备份
                $('.setStrategy').hide();
                $('.setOnceTime').hide();
                $('#reserveType').prop('disabled', false);
                $('#spinnerNum').spinner('enable');
                $('#spinnerDay').spinner('enable');
                // $('#spinnerNum').spinner('value', 30);
                // $('#spinnerDay').spinner('value', 30);

                // 重置时间策略描述信息
                $('.backupTimeDes').html('');
                $('.backupTimeDes').prop('title', '');

                break;
            default:
                break;
        }

        initReserveStrategyDes();
    }

    const speedModeHandler = function(){
        if(this.value === 2){
            $('.setSpeedStrategy').hide();
        }else{
            $('.setSpeedStrategy').show();
        }
    }

    const checkTime = (start, end) => {
        let startnum = new Date("1970-01-01" + " " + start).getTime();
		let endnum = new Date("1970-01-01" + " " + end).getTime();
		if(endnum <= startnum) {//按策略限速才判断
			UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
            return false;
		}else {
			return true;
		}
    }

    /**
     * 存储策略 - 数据加密 switch change
     */
    const encryptChange = function() {
		if (this.checked){
			$('#passwordAutocheck').bootstrapSwitch('state', true);
			$('#password').empty();
			$('#repassword').empty();
			$('.passwordModeDiv').show();
            $('.storage-encrypt-div').show();
		} else {
			$('.passwordModeDiv').hide();
			$('.passwordDiv').hide();
            $('.storage-encrypt-div').hide();
		}
	}

    /**
     * 存储策略 - 自动生成密码 switch change
     */
    const passwordModeChange = function(){
        if (EDIT_BACKUP_FLAG) { // 修改备份
            if ($('#passwordAutocheck').bootstrapSwitch('state')){
                $('#password').val('');
                $('#repassword').val('');
                $('.passwordDiv').hide();
            } else {
                if (firstInitPageFlag) {
                    PASSWORD_HASCHANGED_FLAG = false;
                } else {
                    $('#password').attr('placeholder', LANG.UI_PUBLIC_ENTER_PASSWORD);
                    $('#repassword').attr('placeholder', LANG.UI_PUBLIC_ENTER_REPASSWORD);
                    PASSWORD_HASCHANGED_FLAG = true;
                }

                $('.passwordDiv').show();
            }

            firstInitPageFlag = false;
        } else { // 创建备份
            if ($('#passwordAutocheck').bootstrapSwitch('state')){
                $('#password').val('');
                $('#repassword').val('');
                $('.passwordDiv').hide();
            } else {
                $('.passwordDiv').show();
            }
        }
	}

    const showPassword = function (e) {
        e.stopImmediatePropagation();
        let $input = $(this).parent().find('input');
        let $btn = $(this);
        let inputType = $input.attr('type');
        // 修改输入框的类型，控制显示隐藏
        if (inputType === 'password') {
            $input.attr('type', 'text');
            $btn.find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
        } else {
            $input.attr('type', 'password');
            $btn.find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
        }
    }

    const getSwitchDes = (check) => {
		if(check){
			return LANG.UI_PUBLIC_ON;
		}

		return LANG.UI_PUBLIC_OFF;
	}

    const getScanSpeed = (level) => {
        let des = "";
        switch(level) {
            case 0:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_FIVE;
                break;
            case 1000:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_FOUR;
                break;
            case 800:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_THREE;
                break;
            case 600:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_TWO;
                break;
            case 400:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_ONE;
                break;
            default:
                break;
        }
        return des;
    }

    /**
     * 存储策略 - 获取存储策略描述信息
     */
    const initStoreStrategyDes = () => {
        let des = "";
		des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);

        // 压缩等级
        if ($('#compressCheck').get(0).checked){
            let gradeValue = $('#compressGrade').val();
            let grade = '';
            switch (parseInt(gradeValue,10)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                    break;
                case 3:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                    break;
                case 4:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                    break;
            }
            des += "," + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade + ",";
        }

		des += LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);

        // 存储加密算法
        if($('#encryptStorageCheck').get(0).checked){
            let encryptedMethodLabel = $('.storage-encrypt-label').html();
            let method = $('#storageEncryptMethod').val();
            let grade = '';
            switch (parseInt(method)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            }
            des += "," + encryptedMethodLabel + ": " + grade;
        }

        $('.storeDes').html(des);
        $('.storeDes').attr('title', des);
    }

    /**
     * 存储策略 - 修改备份 - 初始化选择的存储策略
     */
    const initCheckedStorageStrategy = () => {
        // 压缩存储
        $('#compressCheck').bootstrapSwitch('state', taskInfoSetting.bss.compress);

        if (taskInfoSetting.bss.compress) {
            $('.compressGradeDiv').show();
            $('#compressGrade').val(taskInfoSetting.bss.compress_method);
        } else {
            $('.compressGradeDiv').hide();
        }

        // 数据加密
        $('#encryptStorageCheck').bootstrapSwitch('state', taskInfoSetting.bss.encrypt);

        if (taskInfoSetting.bss.encrypt) {
            $('.storage-encrypt-div').show(); // 加密算法div
            $('.passwordModeDiv').show(); // 自动生成密码div
            $('#storageEncryptMethod').val(taskInfoSetting.bss.encrypt_method)

            // 自动生成密码
            $('#passwordAutocheck').bootstrapSwitch('state', taskInfoSetting.bss.password_auto_flag);

            if (!taskInfoSetting.bss.password_auto_flag) {
                $('.passwordModeDiv').show();
                $('.passwordDiv').show();
                $('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
                $('#repassword').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
            } else {
                // 自动生成密码默认为checked，首次进入页面不会触发change事件，因此手动触发一次从而改变firstInitPageFlag状态
                passwordModeChange();
            }
        } else {
            $('.storage-encrypt-div').hide(); // 加密算法div
            $('.passwordModeDiv').hide(); // 自动生成密码div
        }
    }

    /**
     * 存储策略 - 压缩存储 switch change
     */
    const compressChange = function () {
        if (this.checked) {
            $('.compressGradeDiv').show();
        } else {
            $('.compressGradeDiv').hide();
        }
    }

    /**
     * 传输策略 - 修改备份 - 初始化选择的传输策略
     */
    const initCheckedTransmitStrategy = () => {
        // 传输代理
        $('#appliance_agency_flag').bootstrapSwitch('state', taskInfoSetting.bts.appliance_agency_flag);

        // 传输代理资源池
        $('#transferAgentTree').transferAgent({
			agent_uuid: taskInfoSetting.bts.appliance_uuid,
			agent_pool_uuid: taskInfoSetting.bts.appliance_pool_uuid.agent_pool_uuid,
		});

        // 加密传输
        $('#transport_encrypt_flag').bootstrapSwitch('state', taskInfoSetting.bts.encrypt);

        if (taskInfoSetting.bts.encrypt) {
            $('.encrypt-transfer-method-form-group').show();

            $('#transferEncryptMethod').val(taskInfoSetting.bts.encrypt_method);
        } else {
            $('.encrypt-transfer-method-form-group').hide();
        }
    }

    /**
     * 安全策略 - 修改备份 - 初始化选择的安全策略
     */
    const initCheckedSafeStrategy = () => {
        // WORM防护
        let wormCheckFlag = taskInfoSetting.safe_config_strategy.worm_flag === 1 ? true : false;

        $('#worm_protect_check').bootstrapSwitch('state', wormCheckFlag);

        if (wormCheckFlag) {
            $('.worm-protect-term-form-group').show();
            $('#worm_protect_term_spinner').val(taskInfoSetting.safe_config_strategy.worm_protection_time);
        } else {
            $('.worm-protect-term-form-group').hide();
        }

        // 完整性校验
        let integrityCheckFlag = taskInfoSetting.safe_config_strategy.integrity_check_flag === 1 ? true : false;
        $('#integrality_check').bootstrapSwitch('state', integrityCheckFlag);

        if (integrityCheckFlag) {
            $('.integrality-check-form-group').show();

            let verifyCycleMode = taskInfoSetting.safe_config_strategy.integrity_check_config.check_strategy;
            let backupPointAbnormalMode = taskInfoSetting.safe_config_strategy.integrity_check_config.full_error_policy;
            $('#check_cycle').find(`input[data-mode=${verifyCycleMode}]`).iCheck("check");
            $('#backup_point_abnormal').find(`input[data-mode=${backupPointAbnormalMode}]`).iCheck("check");
        } else {
            $('.integrality-check-form-group').hide();
        }
    }

    /**
     * 高级策略 - 传输线程 扫描线程
     */
    const intTransThreadNum = () => {
        let transSpeed = parseInt($("#scanThreadNum").val());

		if (transSpeed === 1) { // 为1（极慢）时显示文件扫描速度
			$(".scanFileDiv").show();
		} else {
			$(".scanFileDiv").hide();
		}

		initHighStrategyDes();
    }

    /**
     * 存储策略 - 初始化存储策略监听器
     */
    const initStoreListeners = () => {
        $('#compressCheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
        });
        $('#encryptStorageCheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
        });
		$('#backupThreadNum').on('input propertychange', function(){
        	initHighStrategyDes();
        });
        $('.backupThreadDiv .spinner-up').on('click', function(){
        	initHighStrategyDes();
        });
        $('.backupThreadDiv .spinner-down').on('click', function(){
        	initHighStrategyDes();
        });
		$('#scanThreadNum').on('input propertychange', function(){
        	intTransThreadNum();
        });
		$('.scanThreadDiv .spinner-up').on('click', function(){
        	intTransThreadNum();
        });
        $('.scanThreadDiv .spinner-down').on('click', function(){
        	intTransThreadNum();
        });
        // 存储加密算法
        $('#storageEncryptMethod').on("change", function () {
            initStoreStrategyDes();
        });
    }

    /**
     * 保留策略 - 获取保留策略描述信息
     */
    const initReserveStrategyDes = () => {
        let des = "";
        let reserveModeType = parseInt($('#reserveMode').val());
        let type = parseInt($('#reserveType').val());
        let value = 0;

        if (reserveModeType === CONF.RESERVE_STRATEGY_MODE.POINT){ // 按备份点保留
            des += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '；';
        } else { // 按备份链保留
            des += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '；';
        }

        des += LANG.UI_RESERVE_RETENTION_MODE + '：';
        if (CONF.RESERVE_TYPE.NUM === type){
            des += LANG.UI_BACKUP_NUM;
            value = $('#spinnerNumInput').val();
        } else if (CONF.RESERVE_TYPE.DAY === type){
            des += LANG.UI_BACKUP_DAY;
            value = $('#spinnerDayInput').val();
        } else if (CONF.RESERVE_TYPE.PERMANENT === type) {
            des += LANG.UI_FILE_PERMANENT;
        }

        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == type){
            des += "，" + LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY +'：' + value;
        }else{
            des += "，" + LANG.UI_STRATEGY_VALUE +'：' + value;
        }

        $('.reserveDes').html(des);
        $('.reserveDes').attr('title', des);
    }

    /**
     * 保留策略 - 修改备份 - 初始化选择的保留策略
     */
    const initCheckedReserveStrategy = () => {
        // 适配604版本创建的旧任务，604保留策略只有按备份链保留，返回的0
        if (taskInfoSetting.brs.strategy_mode === 0) {
            taskInfoSetting.brs.strategy_mode = 2; // 改成按备份链保留
        }

        // 备份数据保留类型
        $('#reserveMode').val(taskInfoSetting.brs.strategy_mode);

        // 数据保留方式
        $('#reserveType').val(taskInfoSetting.brs.type);

        if (taskInfoSetting.brs.type === 1) {
            $('.reserveNum').show();
            $('.reserveDay').hide();
            // 保留个数
            $('#spinnerNumInput').val(taskInfoSetting.brs.number);
        } else {
            $('.reserveNum').hide();
            $('.reserveDay').show();
            // 保留天数
            $('#spinnerDayInput').val(taskInfoSetting.brs.number);
        }

        // 获取保留策略描述信息
        initReserveStrategyDes();
    }

    /**
     * 保留策略 - 数据保留方式 select change
     */
    const reserveTypeHandler = function() {
        let value = parseInt(this.value)
		if (CONF.RESERVE_TYPE.NUM === value) {
			$('.reserveNum').show();
			$('.reserveDay').hide();
			ajaxData.highInfo.reserve.type = this.value;
		} else if (CONF.RESERVE_TYPE.DAY === value) {
			$('.reserveNum').hide();
			$('.reserveDay').show();
			ajaxData.highInfo.reserve.type = this.value;
		} else if (undefined === this.value){
			$('.reserveNum').hide();
			$('.reserveDay').hide();
			ajaxData.highInfo.reserve.type = 3;
		}
		//修改保留策略信息
		initReserveStrategyDes();
	}

    /**
     * 保留策略 - 初始化保留策略监听器
     */
    const initReserveListeners = () => {
        //保留类型切换
		$('#reserveType').on('change', reserveTypeHandler);
        $('#spinnerNumInput').on('input propertychange', function(){
            initReserveStrategyDes();
        });
        $('#spinnerDayInput').on('input propertychange', function(){
            initReserveStrategyDes();
        });

        $('.reserveNum .spinner-up').on('click', function(){
			initReserveStrategyDes();
        });
        $('.reserveNum .spinner-down').on('click', function(){
			initReserveStrategyDes();
        });

        $('.reserveDay .spinner-up').on('click', function(){
			initReserveStrategyDes();
        });
        $('.reserveDay .spinner-down').on('click', function(){
			initReserveStrategyDes();
        });
    }

    /**
     * 高级策略 - 获取高级策略描述信息
     */
    const initHighStrategyDes = () => {
        let des = "";

        let type = parseInt($('#selectstorage').find('option:selected').attr('storage_type'));
        if (type !== CONF.BD_STORAGE_TYPE.TAPE) {
            des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $("#backupThreadNum").val() + ",";
        }
        
        des += $.trim($(".scanthreadlabel").text()) + ": " + $("#scanThreadNum").val();

        if ($("#scanThreadNum").val() === 1) {
            des += ", " + $.trim($(".scanfilelabel").text()) + ": " + $("#scanFileNum").find("option:selected").text();
        }

        $('.higeDes').html(des);
        $('.higeDes').attr('title', des);
    }

    /**
     * 初始化安全策略描述
     */
    const initVerifyStrategyDes = () => {
        let verifyCycleMode = parseInt($('#check_cycle').find('.iradio_square-blue.checked').find('input').attr('data-mode'));

        let backupPointAbnormalMode = parseInt($('#backup_point_abnormal').find('.iradio_square-blue.checked').find('input').attr('data-mode'));

        // $('.verify-strategy-desc').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD}：${VERIFY_CYCLE_DESC_MAP[verifyCycleMode]}；${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_ABNORMAL}：${BACKUP_POINT_ABNORMAL_DESC_MAP[backupPointAbnormalMode]}`);
        $('.verify-strategy-desc').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_ABNORMAL}：${BACKUP_POINT_ABNORMAL_DESC_MAP[backupPointAbnormalMode]}`);
    }

    /**
     * 高级策略 - 初始化修改备份时选择的高级策略
     */
    const initCheckedAdvancedStrategy = () => {
        // 传输线程
        $('#backupThreadNum').val(taskInfoSetting.high.thread_num);

        // 扫描线程
        $('#scanThreadNum').val(taskInfoSetting.high.scan_thread_num);

        if (parseInt(taskInfoSetting.high.scan_thread_num) === 1) { // 为1（极慢）时显示文件扫描速度
            $(".scanFileDiv").show();
            // 扫描文件速度
            $('#scanFileNum').val(taskInfoSetting.high.scan_file_num);
        } else {
            $(".scanFileDiv").hide();
        }

        // 初始化高级策略描述信息
        initHighStrategyDes();
    }

    /**
     * 高级配置 - 初始化修改备份选择的高级配置
     */
    const initCheckedAdvancedConfig = () => {
        // 对象权限备份
        $('#file-permission').bootstrapSwitch('state', taskInfoSetting.high.permission_operate_flag);

        // 跳过文件告警智能判断
        $('#passfilealarmcheck').bootstrapSwitch('state', taskInfoSetting.high.skip_file_alarm_flag);

        if (taskInfoSetting.high.skip_file_alarm_flag) {
            $('.passfilenumDiv').show();
            $('.warnningdiv').show();

            $('#passFileNum').val(taskInfoSetting.high.skip_file_alarm_min_num); // 跳过文件告警个数
            $('#warningpercent').val(taskInfoSetting.high.skip_file_alarm_min_ratio); // 跳过文件告警比例
        } else {
            $('.passfilenumDiv').hide();
            $('.warnningdiv').hide();
        }

        // 网络重试
        $('#network_retry_times').val(taskInfoSetting.retry_strategy.network_retry_times);
        $('#network_retry_interval').val(taskInfoSetting.retry_strategy.network_retry_interval);

        // 操作异常重试
        if (taskInfoSetting.retry_strategy.op_retry_flag) {
            $('#op_retry_flag').bootstrapSwitch('state', taskInfoSetting.retry_strategy.op_retry_flag);
            $('.operation-retry-wrap').show();
            $('#op_retry_times').val(taskInfoSetting.retry_strategy.op_retry_times);
            $('#op_retry_interval').val(taskInfoSetting.retry_strategy.op_retry_interval);
        } else {
            $('#op_retry_flag').bootstrapSwitch('state', false);
            $('.operation-retry-wrap').hide();
            $('#op_retry_times').val(60);
            $('#op_retry_interval').val(30);
        }

        // 任务重连
        if (taskInfoSetting.retry_strategy.task_retry_flag) {
            $('#task_retry_flag').bootstrapSwitch('state', taskInfoSetting.retry_strategy.task_retry_flag);
            $('.task-retry-wrap').show();
            $('#task_retry_object').val(taskInfoSetting.retry_strategy.task_retry_object);
            $('#task_retry_times').val(taskInfoSetting.retry_strategy.task_retry_times);
            $('#task_retry_interval').val(taskInfoSetting.retry_strategy.task_retry_interval / 60);
        } else {
            $('#task_retry_flag').bootstrapSwitch('state', false);
            $('.task-retry-wrap').hide();
            $('#task_retry_object').val('1');
            $('#task_retry_times').val(3);
            $('#task_retry_interval').val(5);
        }

        // 过载保护
        $('#ignore_resource_limiting_flag').bootstrapSwitch('state', taskInfoSetting.high.ignore_resource_limiting_flag);
    }

    /**
     * 传输代理 switch change
     */
    const applianceSwitchChange = () => {
        let flag = $('#appliance_agency_flag').get(0).checked;

        if (flag) {
            $('.applianceselectdiv').show();
            $('.encrypt-transfer-form-group').show();
        } else {
            $('.applianceselectdiv').hide();
            $('.encrypt-transfer-form-group').hide();
            $('#transport_encrypt_flag').bootstrapSwitch('state', false);
            $('.encrypt-transfer-method-form-group').hide();
        }
    };

    /**
     * 加密传输 switch change
     */
    const transferEncryptSwitchChange = () => {
        let flag = $('#transport_encrypt_flag').get(0).checked;

        if (flag) {
            $('.encrypt-transfer-method-form-group').show();
        } else {
            $('.encrypt-transfer-method-form-group').hide();
        }
    }

    /**
     * 安全策略 - worm防护 switch change
     */
    const wormProtectSwitchChange = () => {
        let flag = $('#worm_protect_check').get(0).checked;

        if (flag) {
            $('.worm-protect-term-form-group').show();
        } else {
            $('.worm-protect-term-form-group').hide();
        }
    }

    /**
     * 安全策略 - 完整性校验 switch change
     */
    const integralitySwitchChange = () => {
        let flag = $('#integrality_check').get(0).checked;

        if (flag) {
            $('.integrality-check-form-group').show();
        } else {
            $('.integrality-check-form-group').hide();
        }
    }

    /**
     * 高级配置 - 跳过文件告警智能判断 change
     */
    const passAlarmChange = () => {
        let flag = $('#passfilealarmcheck').get(0).checked;

		if(flag) {
			$('.passfilenumDiv').show();
			$('.warnningdiv').show();
		} else {
			$('.passfilenumDiv').hide();
			$('.warnningdiv').hide();
		}
    }

    /**
     * 高级配置 - 重试策略 - 操作异常重试 - 自动重试 change
     */
    const opRetrySwitchChange = () => {
        let flag = $('#op_retry_flag').get(0).checked;

		if(flag) {
			$('.operation-retry-wrap').show();
		} else {
			$('.operation-retry-wrap').hide();
		}
    }

    /**
     * 高级配置 - 重试策略 - 任务重试 - 自动重试 change
     */
    const taskRetrySwitchChange = () => {
        let flag = $('#task_retry_flag').get(0).checked;

		if(flag) {
			$('.task-retry-wrap').show();
		} else {
			$('.task-retry-wrap').hide();
		}
    }

    /**
     * 校验时间策略和生成步骤四 确认配置 - 时间策略 配置信息
     * @returns {boolean}
     */
    const checkAndGetTimeStrategy = () => {
        ajaxData.backupInfo.full_info = {};
		ajaxData.backupInfo.incr_info = {};
		ajaxData.backupInfo.diff_info = {};
        ajaxData.backupInfo.pincr_info = {};

        let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
        let timeStrategyMode = $('#strategymode').find('input:checked');

        let backupTypeHtml = '';
        let timeStrategyHtml = '';
        ajaxData.backupInfo.type = $('#backuptype').val();

        if ("strategy" === ajaxData.backupInfo.type) {
            backupTypeHtml = LANG.UI_BACKUP_USE_STRATEGY;
            if (0 === timeStrategyMode.length){
				//没有选择时间策略
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_FILE_BACKUP_SET_STRATEGY_TIPS);
				return false;
			} else {
				for (let i=0; i < timeStrategyMode.length; i++){
                    switch ($(timeStrategyMode[i]).data('mode')) {
                        case 1: // 完全备份
                            // 完全备份校验
                            if (strategyConfig.fullInfo.rollFlag && !checkTime(strategyConfig.fullInfo.startTime, strategyConfig.fullInfo.endTime)) return false;

                            // Ajax Data 时间策略 - 完全备份赋值
                            ajaxData.backupInfo.full_info = {
                                mode: strategyConfig.fullInfo.mode,
                                type: strategyConfig.fullInfo.type,
                                start_time: strategyConfig.fullInfo.startTime,
                                roll_flag: strategyConfig.fullInfo.rollFlag,
                                roll_interval: strategyConfig.fullInfo.rollInterval,
                                roll_end_time: strategyConfig.fullInfo.endTime,
                                days: strategyConfig.fullInfo.days,
                                frequency: strategyConfig.fullInfo.frequency,
                                des: strategyConfig.fullInfo.des,
                                full_backup_compensation_flag: strategyConfig.fullInfo.full_backup_compensation_flag
                            };

                            // 确认配置 - 时间策略 - 全量备份
                            timeStrategyHtml += `${ajaxData.backupInfo.full_info.des}<br>`;

                            break;
                        case 2: // 增量备份
                            // 增量备份校验
                            if (strategyConfig.incrInfo.rollFlag && !checkTime(strategyConfig.incrInfo.startTime, strategyConfig.incrInfo.endTime)) return false;

                            // Ajax Data 时间策略 - 增量备份赋值
                            ajaxData.backupInfo.incr_info = {
                                mode: strategyConfig.incrInfo.mode,
                                type: strategyConfig.incrInfo.type,
                                start_time: strategyConfig.incrInfo.startTime,
                                roll_flag: strategyConfig.incrInfo.rollFlag,
                                roll_interval: strategyConfig.incrInfo.rollInterval,
                                roll_end_time: strategyConfig.incrInfo.endTime,
                                days: strategyConfig.incrInfo.days,
                                des: strategyConfig.incrInfo.des
                            };

                            // 确认配置 - 时间策略 - 增量备份
                            timeStrategyHtml += ajaxData.backupInfo.incr_info.des;

                            break;
                        case 3: // 差异备份
                            // 差异备份校验
                            if (strategyConfig.diffInfo.rollFlag && !checkTime(strategyConfig.diffInfo.startTime, strategyConfig.diffInfo.endTime)) return false;

                            // Ajax Data 时间策略 - 差异备份赋值
                            ajaxData.backupInfo.diff_info = {
                                mode: strategyConfig.diffInfo.mode,
                                type: strategyConfig.diffInfo.type,
                                start_time: strategyConfig.diffInfo.startTime,
                                roll_flag: strategyConfig.diffInfo.rollFlag,
                                roll_interval: strategyConfig.diffInfo.rollInterval,
                                roll_end_time: strategyConfig.diffInfo.endTime,
                                days: strategyConfig.diffInfo.days,
                                des: strategyConfig.diffInfo.des
                            };

                            // 确认配置 - 时间策略 - 差异备份
                            timeStrategyHtml += ajaxData.backupInfo.diff_info.des;

                            break;
                        case 9: // 永久增量
                            // 永久增量校验
                            if (strategyConfig.pIncrInfo.rollFlag && !checkTime(strategyConfig.pIncrInfo.startTime, strategyConfig.pIncrInfo.endTime)) return false;

                            // Ajax Data 时间策略 - 永久增量赋值
                            ajaxData.backupInfo.pincr_info = {
                                mode: strategyConfig.pIncrInfo.mode,
                                type: strategyConfig.pIncrInfo.type,
                                start_time: strategyConfig.pIncrInfo.startTime,
                                roll_flag: strategyConfig.pIncrInfo.rollFlag,
                                roll_interval: strategyConfig.pIncrInfo.rollInterval,
                                roll_end_time: strategyConfig.pIncrInfo.endTime,
                                days: strategyConfig.pIncrInfo.days,
                                des: strategyConfig.pIncrInfo.des
                            }

                            // 确认配置 - 时间策略 - 差异备份
                            timeStrategyHtml += ajaxData.backupInfo.pincr_info.des;

                            break;
                        default:
                            break;
                    }
				}

                // 确认配置 - 备份方式 && 时间策略
                $('.confirm-backup-type').html(backupTypeHtml);
                $('.form-group-confirm-time-strategy').show();
                $('.confirm-time-strategy').html(timeStrategyHtml);

				return true;
			}
        } else if ("oncetime" === ajaxData.backupInfo.type) {
            let onceTime = $('#oncetime').val();

            backupTypeHtml = LANG.UI_BACKUP_ONCE;

			if (onceTime) {
				$('.settimetip').hide();
                // Ajax Data 时间策略 - 一次性备份赋值
				ajaxData.backupInfo.datetime = onceTime;
                timeStrategyHtml = LANG.UI_PUBLIC_START_TIME + ": " + ajaxData.backupInfo.datetime;

                // 确认配置 - 备份方式 && 时间策略
                $('.confirm-backup-type').html(backupTypeHtml);
                $('.form-group-confirm-time-strategy').show();
                $('.confirm-time-strategy').html(timeStrategyHtml);

				return true;
			} else {
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);

				return false;
			}
        } else {
            backupTypeHtml = LANG.UI_BACKUP_MANUAL;

            // 确认配置 - 备份方式 && 时间策略
            $('.confirm-backup-type').html(backupTypeHtml);
            $('.form-group-confirm-time-strategy').hide();

            return true;
        }
    }

    /**
     * 校验限速策略和生成步骤四 确认配置 - 限速策略 配置信息
     * @returns {boolean}
     */
    const checkAndGetSpeedLimitStrategy = () => {
        // Ajax Data 限速策略赋值
        ajaxData.speedLimit = getSpeedStrategyInfo();

        // 确认配置 - 限速策略配置
        let speedLimitHtml = $('.speedlimitDes').prop('title') || LANG.UI_PUBLIC_NOTHING;
        $('.confirm-speed-limit-strategy').html(speedLimitHtml);

        return true;
    }

    const isNotLatinCode = (str) => {
        let latin1Regex = /[^\x00-\xFF]/;

		if (latin1Regex.test(str)) {
			UIToastr.showWarning(LANG.UI_DB_BACKUP_PASSWORD_ILLEAGLE, LANG.UI_DB_BACKUP_PASSWORD_TIPS);
			return false;
		}

		return true;
    }

    /**
     * 校验存储策略和生成步骤四 确认配置 - 存储策略 配置信息
     * @returns {boolean}
     */
    const checkAndGetStoreStrategy = () => {
        let compressFlag = $('#compressCheck').get(0).checked; // 是否开启压缩存储
        let encryptStorageFlag = $('#encryptStorageCheck').get(0).checked; // 是否开启数据加密
        let passwordAutoFlag = $('#passwordAutocheck').get(0).checked; // 是否自动生成密码
        let password = $.trim($('#password').val()); // 密码
        let confirmPassword = $.trim($('#repassword').val()); // 确认密码
        // 非法字符串校验
		if (!isNotLatinCode(password)) {
			return false;
		}

        // 密码和确认密码校验
        if (!passwordAutoFlag && encryptStorageFlag) { // 开启数据加密 和 不开启自动生成密码
            if (!EDIT_BACKUP_FLAG) { // 创建备份 - 密码校验
                if (!password || !confirmPassword) {
                    UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                    return false;
                }

                if (password !== confirmPassword) {
                    UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
                    return false;
                }
            } else { // 修改备份 - 密码校验
                // 是否触发过密码输入框的change事件且密码最终值还是空,则提示 请输入数据加密的密码;没有触发过则无需校验,直接向后端提交此前接口返回的密码
                if (PASSWORD_HASCHANGED_FLAG && !password) {
                    UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                    return false;
                }

                // 触发过密码输入框的change事件,但和确认密码不一致时
                if (PASSWORD_HASCHANGED_FLAG && password !== confirmPassword) {
                    UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
                    return false;
                }
            }
		}

        // Ajax Data 存储策略赋值
        ajaxData.highInfo.store.valid = true;
        ajaxData.highInfo.store.compress = compressFlag; // 是否开启压缩存储
        ajaxData.highInfo.store.compress_method = $('#compressGrade').val() || 0; // 压缩等级
        ajaxData.highInfo.store.dataencrypt = encryptStorageFlag; // 是否开启数据加密
		ajaxData.highInfo.store.password_auto_flag = passwordAutoFlag; //自动生成密码开关
        ajaxData.highInfo.store.encrypt_method = parseInt($('#storageEncryptMethod').val()); // 加密算法

        // 密码赋值
        if (!EDIT_BACKUP_FLAG) { // 创建备份
            if (appliedGlobalStrategy.flag) { // 应用了全局的备份策略(若某个策略未配置，插件返回的是 空数组，否则是对象)
                if (appliedGlobalStrategy.strategy.store && !Array.isArray(appliedGlobalStrategy.strategy.store) && appliedGlobalStrategy.strategy.store.storeInfo.dataencrypt && !appliedGlobalStrategy.strategy.store.storeInfo.password_auto_flag) { // 开启了存储策略且包含自定义密码
                    if (PASSWORD_HASCHANGED_FLAG) { // 修改过密码
                        if (password === appliedGlobalStrategy.strategy.store.storeInfo.password) { // 修改过但修改后的密码恰好和原先自定义base64编码的一致，则不需要转码直接提交
                            ajaxData.highInfo.store.password = password;
                        } else {
                            ajaxData.highInfo.store.password = btoa(password);
                        }
                    } else { // 未修改过密码则直接提交已经是base64编码过的密码
                        ajaxData.highInfo.store.password = password;
                    }
                } else { // 虽应用全局策略，但全局策略中未开启存储策略
                    ajaxData.highInfo.store.password = btoa(password);
                }
            } else { // 未应用全局的备份策略
                ajaxData.highInfo.store.password = btoa(password);
            }
        } else { // 修改备份
            if (appliedGlobalStrategy.flag) { // 应用了全局的备份策略(若某个策略未配置，插件返回的是 空数组，否则是对象)
                if (appliedGlobalStrategy.strategy.store && !Array.isArray(appliedGlobalStrategy.strategy.store) && appliedGlobalStrategy.strategy.store.storeInfo.dataencrypt && !appliedGlobalStrategy.strategy.store.storeInfo.password_auto_flag) { // 开启了存储策略且包含自定义密码
                    if (PASSWORD_HASCHANGED_FLAG) { // 修改过密码
                        if (password === appliedGlobalStrategy.strategy.store.storeInfo.password) { // 修改过但修改后的密码恰好和原先自定义base64编码的一致，则不需要转码直接提交
                            ajaxData.highInfo.store.password = password;
                        } else {
                            if ($.trim($('#password').val()) === taskInfoSetting.bss.password) { // 若修改后的密码不跟全局策略中的密码一致，但是，却恰好跟原先备份任务创建时设置的密码一致时，则直接提交原密码
                                ajaxData.highInfo.store.password = password;
                            } else {
                                ajaxData.highInfo.store.password = btoa(password);
                            }
                        }
                    } else { // 未修改过密码则直接提交已经是base64编码过的密码
                        ajaxData.highInfo.store.password = password;
                    }
                } else { // 虽应用全局策略，但全局策略中未开启存储策略 或 未开启手动加密
                    if (!PASSWORD_HASCHANGED_FLAG) { // 密码未被修改过，全局策略中又没开启，这里password是个空字符串
                        ajaxData.highInfo.store.password = password; 
                    } else { // 修改过则直接提交修改过后base64编码过后的密码
                        if ($.trim($('#password').val()) === taskInfoSetting.bss.password) { // 若修改后的密码恰好跟原先备份任务创建时设置的密码一致时，则直接提交原密码
                            ajaxData.highInfo.store.password = password;
                        } else {
                            ajaxData.highInfo.store.password = btoa(password);
                        }
                    }
                }
            } else { // 未应用全局的备份策略
                if (!PASSWORD_HASCHANGED_FLAG) { // 未应用也未修改过
                    ajaxData.highInfo.store.password = taskInfoSetting.bss.password; // 未修改过密码直接提交原先返回的
                } else {
                    // 若密码输入框触发过change事件,判断现密码和原配置的密码_oldPassword是否相等(存在用户输的密码与返回的编码过的密码一样的场景),相等则不需要base64编码;不相等则需要编码
                    if ($.trim($('#password').val()) === taskInfoSetting.bss.password) {
                        ajaxData.highInfo.store.password = password;
                    } else {
                        ajaxData.highInfo.store.password = btoa(password);
                    }
                }
            }
        }

        // 确认配置 - 存储策略配置
        let storageHtml = `${$('.compressLabel').html()}: ${getSwitchDes(compressFlag)}<br>`; // 压缩存储

        if (compressFlag) {
            let gradeValue = $('#compressGrade').val();
            let grade = '';
            switch (parseInt(gradeValue,10)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                    break;
                case 3:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                    break;
                case 4:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                    break;
            }

            storageHtml += `${LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE}: ${grade}<br>`; // 压缩等级
        }

        storageHtml += `${$('.encryptStorageLabel').html()}: ${getSwitchDes(encryptStorageFlag)}<br>`; // 数据加密

        if (encryptStorageFlag) {
            let encryptedMethodLabel = $('.storage-encrypt-label').html();
            let method = $('#storageEncryptMethod').val();
            let grade = '';
            switch (parseInt(method)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            }

            storageHtml += `${encryptedMethodLabel}: ${grade}<br>${$('.passwordAutoLabel').html()}: ${getSwitchDes(passwordAutoFlag)}`;
        }

        $('.confirm-storage-strategy').html(storageHtml);

        return true;
    }

    /**
     * 校验保留策略和生成步骤四 确认配置 - 保留策略 配置信息 
     * @returns {boolean}
     */
    const checkAndGetReserveStrategy = () => {
        if (selectedStorageType === CONF.BD_STORAGE_TYPE.TAPE) { // 选择的存储类型为磁带时
            $('.form-group-confirm-reserve-strategy').hide(); // 隐藏保留策略
            
            let tapGenerateStrategyDes = $('.form-group-tape-strategy__value.generate-strategy').text();
            let tapReserveStrategtDes = $('.form-group-tape-strategy__value.reserve-strategy').text();
            let tapeStrategyDesHtml = `${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}: ${tapGenerateStrategyDes} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}: ${tapReserveStrategtDes}`;
            $('.form-group-confirm-tape-strategy').show(); // 显示磁带组策略
            $('.confirm-tape-strategy').html(tapeStrategyDesHtml);
        } else {
            $('.form-group-confirm-tape-strategy').hide(); // 隐藏磁带组策略
            $('.form-group-confirm-reserve-strategy').show(); // 显示保留策略
            let reserveType = '';

            // Ajax Data 保留策略赋值
            ajaxData.highInfo.reserve.strategyMode = $('#reserveMode').val(); // 备份数据保留类型
            ajaxData.highInfo.reserve.type = $('#reserveType').val(); // 数据保留方式

            if (CONF.RESERVE_TYPE.NUM == ajaxData.highInfo.reserve.type) {
                ajaxData.highInfo.reserve.value = $('#spinnerNumInput').val();
                reserveType = LANG.UI_STRATEGY_RESERVE_NUM;
            } else if (CONF.RESERVE_TYPE.DAY == ajaxData.highInfo.reserve.type) {
                ajaxData.highInfo.reserve.value = $('#spinnerDayInput').val();
                reserveType = LANG.UI_STRATEGY_RESERVE_DAY;
            } else if (CONF.RESERVE_TYPE.PERMANENT == ajaxData.highInfo.reserve.type){
                ajaxData.highInfo.reserve.value = '';
                reserveType = LANG.UI_FILE_PERMANENT;
            }

            // 校验保留个数 | 保留天数 是否为0
            if (parseInt(ajaxData.highInfo.reserve.value) <= 0 || !ajaxData.highInfo.reserve.value) {
                UIToastr.showWarning(LANG.UI_BACKUP_RESERVE_TIPS);
                return false;
            }

            let reserveModeHtml = '';
            if (parseInt(ajaxData.highInfo.reserve.strategyMode) === CONF.RESERVE_STRATEGY_MODE.POINT) { // 按备份点保留
                reserveModeHtml = `${LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE}：${LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT}`;
            } else { // 按备份链保留
                reserveModeHtml = `${LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE}：${LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN}`;
            }
            
            let methoddes = LANG.UI_STRATEGY_VALUE;
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == ajaxData.highInfo.reserve.type){
                methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
            }

            // 确认配置 - 保留策略配置
            let reserveStrategyHtml = `${reserveModeHtml}<br>`;
            let reserveTypeHtml = `${LANG.UI_GLOBAL_STRATEGY_DATA_RESERVE_TYPE}：${reserveType}<br>${methoddes}：${ajaxData.highInfo.reserve.value}`;
            $('.confirm-reserve-strategy').html(reserveStrategyHtml + reserveTypeHtml);
        }

        return true;
    }

    /**
     * 校验高级策略和生成步骤四 确认配置 - 高级策略 配置信息
     * @returns {boolean}
     */
    const checkAndGetAdvancedStrategy = () => {
        const REG_EXP = /^\d+$/;

        // 多线程未授权时，传输线程默认传 1
        let transmitThreadNum = CONF.FUNCTIONS.includes('multithread') ? parseInt($('#backupThreadNum').val()) : 1; // 传输线程数量
        let scanThreadNum = parseInt($('#scanThreadNum').val()); // 扫描线程数量

        // 传输线程数量校验
        if (!transmitThreadNum || transmitThreadNum > 32 || transmitThreadNum <= 0 || !REG_EXP.test(transmitThreadNum)) {
            // 重置为默认值
            $('.backupThreadDiv').spinner("value", 3);
            // 重置描述
            initHighStrategyDes();

            UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
            return false;
        }

        // 扫描线程数量校验
        if (!scanThreadNum || scanThreadNum > 32 || scanThreadNum <=0 || !REG_EXP.test(scanThreadNum)) {
            // 重置为默认值
            $('.scanThreadDiv').spinner("value", 3);
            // 重置描述
            initHighStrategyDes();

            UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
            return false;
        }

        // Ajax Data 高级策略赋值
        ajaxData.highInfo.newstr.backupThreadNum = transmitThreadNum;
        ajaxData.highInfo.newstr.scanThreadNum = scanThreadNum;

        // 扫描文件数量--扫描线程数为1时 文件扫描速度可选  其他情况默认文件扫描速度为0（无线）
        if (parseInt(ajaxData.highInfo.newstr.scanThreadNum) === 1) {
            ajaxData.highInfo.newstr.scanFileNum = $('#scanFileNum').val();
        } else {
            ajaxData.highInfo.newstr.scanFileNum = 0;
        }

        // 确认配置 - 高级策略 配置
        let transmitThreadHtml = `${LANG.UI_GLOBAL_STRATEGY_THREAD_NUM}: ${transmitThreadNum}`;
        let scanThreadHtml = `${$('.scanthreadlabel').html()}: ${scanThreadNum}`;

        if (selectedStorageType === CONF.BD_STORAGE_TYPE.TAPE) { // 磁带类型不显示传输线程
            $('.confirm-advanced-strategy-transmit-thread').hide();
        } else {
            $('.confirm-advanced-strategy-transmit-thread').show();
            $('.confirm-advanced-strategy-transmit-thread').html(transmitThreadHtml + "<br>");
        }

        $('.confirm-advanced-strategy-scan-thread').html(scanThreadHtml);

        if (scanThreadNum === 1) {
            $('.confirm-advanced-strategy-scan-speed').show();
            let scanSpeedHtml = `${$('.scanfilelabel').html()}: ${getScanSpeed(parseInt(ajaxData.highInfo.newstr.scanFileNum))}`;
            $('.confirm-advanced-strategy-scan-speed').html(scanSpeedHtml);
        } else {
            $('.confirm-advanced-strategy-scan-speed').hide();
        }

        return true;
    }

    /**
     * 校验传输策略和生成步骤四 确认配置 - 传输策略 配置信息
     * @returns {boolean}
     */
    const checkAndGetTransmitStrategy = () => {
        APPLIANCE_AGENCY_HAS_CONFIGED = $('#transferAgentTree').transferAgent('validateSelect');
        if ($('#appliance_agency_flag').get(0).checked && !APPLIANCE_AGENCY_HAS_CONFIGED) { // 未配置传输代理时无法下一步
            UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);

            return false;
        }

        let applianceNode = $('#transferAgentTree').transferAgent('getSelect');

		ajaxData.srcInfo.transport_priority = $('#transport_mode').val(); // 传输模式
		ajaxData.highInfo.transfer.network = ''; // 传输网络
        ajaxData.highInfo.transfer.appliance_agency_flag = $('#appliance_agency_flag').get(0).checked; // 是否开启传输代理
        ajaxData.highInfo.transfer.appliance_uuid = $('#appliance_agency_flag').get(0).checked ? applianceNode.agent_uuid : ''; // 传输代理uuid
        ajaxData.highInfo.transfer.appliance_pool_uuid = $('#appliance_agency_flag').get(0).checked ? applianceNode.agent_pool_uuid : ''; // 传输代理uuid
        ajaxData.highInfo.transfer.encrypt = $('#transport_encrypt_flag').get(0).checked; // 是否开启加密传输
        ajaxData.highInfo.transfer.encrypt_method = $('#transport_encrypt_flag').get(0).checked ? $('#transferEncryptMethod').val() : ''; // 加密传输算法


        // 确认配置 - 传输策略配置
        let transDes = LANG.UI_PUBLIC_APPLICE + ': ' + getSwitchDes(ajaxData.highInfo.transfer.appliance_agency_flag) + '<br>';

        if (ajaxData.highInfo.transfer.appliance_agency_flag) {
            transDes += $('.applianceselectlabel').text() + ': ' + applianceNode.name + '<br>';
        }

        transDes += LANG.UI_COPY_BACK_ENCRYPT + ': ' + getSwitchDes(ajaxData.highInfo.transfer.encrypt) + '<br>';

        if (ajaxData.highInfo.transfer.encrypt) {
            transDes += $('.transfer-encrypt-method-label').text() + ": " + $('#transferEncryptMethod').find("option:selected").text() + '<br>';
        }

        $('.confirm-transmit-strategy').html(transDes);

        let transfernetworklabel = $('.transfernetworklabel').html();
        $('.confirm-transmit-network').html(transfernetworklabel + ": " + $('#transferNetwork').find("option:selected").text()); // 节点传输网络

		return true;
    }

    /**
     * 校验安全策略和生成步骤四 确认配置 - 安全策略配置信息
     */
    const checkAndGetSafeStrategy = () => {
        let wormFlag = $('#worm_protect_check').get(0).checked;
        // let integrityCheckFlag = $('#integrality_check').get(0).checked;
        let integrityCheckFlag = true; // 默认开启完整性校验
        // 磁带存储不涉及完整性校验
        if (selectedStorageType === CONF.BD_STORAGE_TYPE.TAPE) {
            integrityCheckFlag = false;
        }

        // Ajax data 赋值
        ajaxData.safe_config_strategy.worm_flag = wormFlag ? 1 : 2; // 是否开启WORM防护
        ajaxData.safe_config_strategy.integrity_check_flag = integrityCheckFlag ? 1 : 2; // 是否开启完整性校验
        $('.confirm-worm-protect').html(`${LANG.UI_SAFE_STRATEGY_WORM_PROTECT}：${getSwitchDes(wormFlag)}`);
        // $('.confirm-integrity-check').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK}：${getSwitchDes(integrityCheckFlag)}`);
        $('.confirm-integrity-check').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK}：${LANG.UI_PUBLIC_ON}`);

        if (wormFlag) { // 开启WORM防护
            // Ajax data 赋值
            ajaxData.safe_config_strategy.worm_protection_time = parseInt($('#worm_protect_term_spinner').val()); // WORM防护期限

            // 确认配置
            $('.confirm-worm-protect-term').removeClass('display-none');
            $('.confirm-worm-protect-term').html(`${LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD}：${ajaxData.safe_config_strategy.worm_protection_time}${LANG.UI_STRATEGY_RESERVE_DAY_EN}`);
        } else {
            $('.confirm-worm-protect-term').addClass('display-none');
        }

        
        if (selectedStorageType === CONF.BD_STORAGE_TYPE.TAPE) { // 选择磁带存储时屏蔽安全策略下的worm保护和完整性校验配置
            $('.confirm-config-safety-form-group').addClass('display-none');
        } else {
            $('.confirm-config-safety-form-group').removeClass('display-none');
        }

        if (integrityCheckFlag) { // 开发完整性校验
            let verifyCycleMode = parseInt($('#check_cycle').find('.iradio_square-blue.checked').find('input').attr('data-mode'));
            let backupPointAbnormalMode = parseInt($('#backup_point_abnormal').find('.iradio_square-blue.checked').find('input').attr('data-mode'));

            // Ajax data 赋值
            ajaxData.safe_config_strategy.integrity_check_config.check_strategy = 2; // 校验周期
            ajaxData.safe_config_strategy.integrity_check_config.full_error_policy = backupPointAbnormalMode; // 备份异常点处理方式

            // 确认配置
            // $('.confirm-integrity-check-cycle').removeClass('display-none');
            $('.confirm-config-interity-wrapper').removeClass('display-none');
            $('.confirm-integrity-check-backup-point-abnormal').removeClass('display-none');
            // $('.confirm-integrity-check-cycle').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD}：${VERIFY_CYCLE_DESC_MAP[verifyCycleMode]}`);
            $('.confirm-integrity-check-backup-point-abnormal').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_ABNORMAL}：${BACKUP_POINT_ABNORMAL_DESC_MAP[backupPointAbnormalMode]}`);
        } else {
            $('.confirm-config-interity-wrapper').addClass('display-none');
            $('.confirm-integrity-check-cycle').addClass('display-none');
            $('.confirm-integrity-check-backup-point-abnormal').addClass('display-none');
        }

        return true;
    }

    /**
     * 校验高级配置和生成步骤四 确认配置 - 高级配置 配置信息
     * @returns {boolean}
     */
    const checkAndGetAdvancedConfig = () => {
        const REG_EXP = /^\d+$/;
        let skipFileAlarmMinNum = parseInt($('#passFileNum').val()); // 跳过文件告警个数
        let skipFileAlarmMinRatio = parseInt($('#warningpercent').val()); // 调过文件告警比例
        let networkRetryTimes = parseInt($('#network_retry_times').val()); // 网络重试重连次数
        let networkRetryInterval = parseInt($('#network_retry_interval').val()); // 网络重试重连间隔时间
        let opRetryTimes = parseInt($('#op_retry_times').val()); // 操作异常重试次数
        let opRetryInterval = parseInt($('#op_retry_interval').val()); // 操作异常重试间隔时间
        let taskRetryTimes = parseInt($('#task_retry_times').val()); // 任务重试次数
        let taskRetryInterval = parseInt($('#task_retry_interval').val()); // 任务重连间隔时间
        
        let permissionBackupHtml = '';
        let ignoreWarningHtml = '';
        let ignoreWarningNumHtml = '';
        let ignoreWarningPercentHtml = '';
        let networkRetryTimesHtml = '';
        let networkRetryIntervalHtml = '';
        let opRetryHtml = '';
        let opRetryTimesHtml = '';
        let opRetryIntervalHtml = '';
        let taskRetryHtml = '';
        let taskRetryObjetHtml = '';
        let taskRetryTimesHtml = '';
        let taskRetryIntervalHtml = '';
        let overloadProtectionHtml = '';

        // Ajax Data 高级配置赋值
        ajaxData.highInfo.permission_operate_flag = $('#file-permission').get(0).checked; // 是否开启文件权限备份
        ajaxData.highInfo.skip_file_alarm_flag = $('#passfilealarmcheck').get(0).checked; // 是否调过文件智能告警
        ajaxData.highInfo.op_retry_flag = $('#op_retry_flag').get(0).checked; // 操作异常自动重试
        ajaxData.highInfo.task_retry_flag = $('#task_retry_flag').get(0).checked; // 任务重试自动重试

        ignoreWarningHtml = `${$('.passfilealarmlabel').html()}: ${getSwitchDes(ajaxData.highInfo.skip_file_alarm_flag)}`;
        $('.confirm-advanced-config-ignore-warning').html(ignoreWarningHtml);
        if (ajaxData.highInfo.skip_file_alarm_flag) {
            // 校验跳过文件告警个数
            if (!skipFileAlarmMinNum || skipFileAlarmMinNum > 9999999999 || skipFileAlarmMinNum <= 0 || !REG_EXP.test(skipFileAlarmMinNum)) {
                // 重置为默认值
                $('.passfilenumDiv').spinner('value', 10);
                UIToastr.showWarning(LANG.UI_OBS_SKIP_FILE_ALARM_NUM, LANG.UI_OBS_SKIP_FILE_ALARM_NUM_TIPS);

                return false;
            }

            if (!skipFileAlarmMinRatio || skipFileAlarmMinRatio > 100 || skipFileAlarmMinRatio <= 0 || !REG_EXP.test(skipFileAlarmMinRatio)) {
                // 重置为默认值
                $('#spinnerpercent').spinner('value', 20);
                UIToastr.showWarning(LANG.UI_OBS_SKIP_FILE_ALARM_RATIO, LANG.UI_OBS_SKIP_FILE_ALARM_RATIO_TIPS);

                return false;
            }

            // Ajax Data 高级配置赋值
            ajaxData.highInfo.skip_file_alarm_min_num = skipFileAlarmMinNum;
            ajaxData.highInfo.skip_file_alarm_min_ratio = skipFileAlarmMinRatio;

            $('.confirm-advanced-config-ignore-warning-num').show();
            $('.confirm-advanced-config-ignore-warning-percent').show();

            // 确认配置 - 高级配置
            // permissionBackupHtml = `${$('.file-permission-label').html()}: ${getSwitchDes(ajaxData.highInfo.permission_operate_flag)}`;
            // $('.confirm-advanced-config-permission-backup').html(permissionBackupHtml);
            ignoreWarningNumHtml = `${$('.passfilenumlabel').html()}: ${ajaxData.highInfo.skip_file_alarm_min_num}`;
            ignoreWarningPercentHtml = `${$('.passfilepercentlabel').html()}: ${ajaxData.highInfo.skip_file_alarm_min_ratio}%`;
            $('.confirm-advanced-config-ignore-warning-num').html(ignoreWarningNumHtml);
            $('.confirm-advanced-config-ignore-warning-percent').html(ignoreWarningPercentHtml);
        } else {
            ajaxData.highInfo.skip_file_alarm_min_num = '';
            ajaxData.highInfo.skip_file_alarm_min_ratio = '';
            $('.confirm-advanced-config-ignore-warning-num').hide();
            $('.confirm-advanced-config-ignore-warning-percent').hide();
        }

        if (!Number.isInteger(networkRetryTimes)){ // 校验网络重试重连次数
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_TIMES);
            return false;
        }

        if (networkRetryTimes < 1) { // 网络重试重连次数小于1
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
            return false;
        }

        if (networkRetryTimes > 60) { // 网络重试重连次数大于60
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_MAX_TIMES);
            return false;
        }

        if (!Number.isInteger(networkRetryInterval)){ // 校验网络重试重连间隔时间
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_INTERVAL);
            return false;
        }
        
        if (networkRetryInterval < 5) { // 网络重试重连间隔时间小于5
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_MIN_RECONNECT_INTERVAL);
            return false;
        }

        if (networkRetryInterval > 60) { // 网络重试重连间隔时间大于60
            UIToastr.showWarning(LANG.UI_OBS_NETWORK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_MAX_RECONNECT_INTERVAL);
            return false;
        }

        // Ajax Data 高级配置赋值
        ajaxData.highInfo.network_retry_times = networkRetryTimes; // 网络重试次数
        ajaxData.highInfo.network_retry_interval = networkRetryInterval; // 网络重试间隔时间

        // 确认配置 - 高级配置
        networkRetryTimesHtml = `${LANG.UI_RETRY_STRATEGY_NETWORK_TIMES}：${networkRetryTimes}`;
        networkRetryIntervalHtml = `${LANG.UI_OBS_NETWORK_RECONNECT_INTERVAL}：${networkRetryInterval}${LANG.UI_PUBLIC_SECOND}`;
        $('.confirm-advanced-config-network-retry-times').html(networkRetryTimesHtml);
        $('.confirm-advanced-config-network-retry-interval').html(networkRetryIntervalHtml);

        opRetryHtml = `${LANG.UI_OBS_OPERATE_ABNORMAL_RETRY}: ${getSwitchDes(ajaxData.highInfo.op_retry_flag)}`;
        $('.confirm-advanced-config-op-retry').html(opRetryHtml);
        if (ajaxData.highInfo.op_retry_flag) { // 开启了操作异常重试
            if (!Number.isInteger(opRetryTimes)){ // 校验操作异常重试重连次数
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_TIMES);
                return false;
            }
    
            if (opRetryTimes < 1) { // 操作异常重试重连次数小于1
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
                return false;
            }
    
            if (opRetryTimes > 60) { // 操作异常重试重连次数大于60
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_MAX_TIMES);
                return false;
            }

            if (!Number.isInteger(opRetryInterval)){ // 校验操作异常重试重连间隔时间
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_INTERVAL);
                return false;
            }
            
            if (opRetryInterval < 5) { // 操作异常重试重连间隔时间小于5
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_MIN_RECONNECT_INTERVAL);
                return false;
            }
    
            if (opRetryInterval > 60) { // 网络重试重连间隔时间大于60
                UIToastr.showWarning(LANG.UI_OBS_OPERATE_ABNORMAL_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_MAX_RECONNECT_INTERVAL);
                return false;
            }

            // Ajax Data 高级配置赋值
            ajaxData.highInfo.op_retry_times = opRetryTimes; // 操作异常重试次数
            ajaxData.highInfo.op_retry_interval = opRetryInterval; // 操作异常重连间隔时间
            $('.confirm-advanced-config-op-retry-times').show();
            $('.confirm-advanced-config-op-retry-interval').show();

            // 确认配置 - 高级配置
            opRetryTimesHtml = `${LANG.UI_OBS_OPERATE_ABNORMAL_RETRY_TIMES}：${ajaxData.highInfo.op_retry_times}`;
            opRetryIntervalHtml = `${LANG.UI_OBS_OPERATE_ABNORMAL_RETRY_INERVAL}：${ajaxData.highInfo.op_retry_interval}${LANG.UI_PUBLIC_SECOND}`;
            $('.confirm-advanced-config-op-retry-times').html(opRetryTimesHtml);
            $('.confirm-advanced-config-op-retry-interval').html(opRetryIntervalHtml);
        } else {
            ajaxData.highInfo.op_retry_times = 0; // 操作异常重试次数
            ajaxData.highInfo.op_retry_interval = 0; // 操作异常重连间隔时间
            $('.confirm-advanced-config-op-retry-times').hide();
            $('.confirm-advanced-config-op-retry-interval').hide();
        }
        
        taskRetryHtml = `${LANG.UI_OBS_TASK_RETRY}：${getSwitchDes(ajaxData.highInfo.task_retry_flag)}`;
        $('.confirm-advanced-config-task-retry').html(taskRetryHtml);
        if (ajaxData.highInfo.task_retry_flag) { // 开启了任务重试
            if (!Number.isInteger(taskRetryTimes)){ // 校验任务重试重连次数
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_TIMES);
                return false;
            }
    
            if (taskRetryTimes < 1) { // 任务重试重连次数小于1
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
                return false;
            }
    
            if (taskRetryTimes > 3) { // 任务重试重连次数大于3
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_TASK_RETRY_MAX_TIMES);
                return false;
            }

            if (!Number.isInteger(taskRetryInterval)){ // 校验任务重试重连间隔时间
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_NETWORK_RETRY_CORRENT_RECONNECT_INTERVAL);
                return false;
            }
            
            if (taskRetryInterval < 1) { // 任务重试重连间隔时间小于1
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_TASK_RETRY_MIN_INTERVAL);
                return false;
            }
    
            if (taskRetryInterval > 60) { // 任务重试重连间隔时间大于60
                UIToastr.showWarning(LANG.UI_OBS_TASK_RETRY, LANG.UI_OBS_TASK_RETRY_MAX_INTERVAL);
                return false;
            }

            // Ajax Data 高级配置赋值
            ajaxData.highInfo.task_retry_times = taskRetryTimes; // 任务重试重连次数
            ajaxData.highInfo.task_retry_interval = taskRetryInterval * 60; // 任务重试重连间隔时间
            ajaxData.highInfo.task_retry_object = parseInt($('#task_retry_object').val()); // 任务重连对象 1: 仅重试任务中失败的对象 2: 重试任务中所有的对象
            
            // 确认配置 - 高级配置
            $('.confirm-advanced-config-task-retry-object').show();
            $('.confirm-advanced-config-task-retry-times').show();
            $('.confirm-advanced-config-task-retry-interval').show();
            
            taskRetryObjetHtml = `${LANG.UI_OBS_RETRY_OBJECT}：${ ajaxData.highInfo.task_retry_object === 1 ? LANG.UI_RETRY_STRATEGY_OBJECT_OPTION1 : LANG.UI_RETRY_STRATEGY_OBJECT_OPTION2 }`;
            taskRetryTimesHtml = `${LANG.UI_OBS_TASK_RETRY_TIMES}：${ajaxData.highInfo.task_retry_times}`;
            taskRetryIntervalHtml = `${LANG.UI_OBS_TASK_RETRY_INTERVAL}：${taskRetryInterval}${LANG.UI_MICROSOFT365_MINUTE}`;
            $('.confirm-advanced-config-task-retry-object').html(taskRetryObjetHtml);
            $('.confirm-advanced-config-task-retry-times').html(taskRetryTimesHtml);
            $('.confirm-advanced-config-task-retry-interval').html(taskRetryIntervalHtml);
        } else {
            ajaxData.highInfo.task_retry_times = 0; // 任务重试重连次数
            ajaxData.highInfo.task_retry_interval = 0; // 任务重试重连间隔时间
            ajaxData.highInfo.task_retry_object = ''; //任务重连对象

            $('.confirm-advanced-config-task-retry-object').hide();
            $('.confirm-advanced-config-task-retry-times').hide();
            $('.confirm-advanced-config-task-retry-interval').hide();
        }

        // Ajax Data 高级配置赋值
        ajaxData.highInfo.ignore_resource_limiting_flag = $('#ignore_resource_limiting_flag').get(0).checked;

        // 确认配置 - 高级配置
        overloadProtectionHtml = `${$('.overload-protection-label').html()}: ${getSwitchDes(ajaxData.highInfo.ignore_resource_limiting_flag)}`;
        $('.confirm-advanced-config-overload-protection').html(overloadProtectionHtml);

        return true;
    }

    /**
     * 步骤三 - 备份策略校验
     * @returns {number|boolean}
     */
    const step3Valid = () => {

        let result =
            checkAndGetTimeStrategy() &&
                checkAndGetSpeedLimitStrategy() &&
                    checkAndGetStoreStrategy() &&
                        checkAndGetReserveStrategy() &&
                            checkAndGetAdvancedStrategy() &&
                                checkAndGetTransmitStrategy() &&
                                    checkAndGetSafeStrategy() &&
                                        checkAndGetAdvancedConfig();

        return result;
    }
    // <-------------------------END STEP THREE BACKUP STRATEGY--------------------------------->


    // <-------------------------BEGIN STEP FOUR BACKUP CONFIRM--------------------------------->

    /**
     * 创建备份请求
     */
    const requestForCreateBackup = () => {
        // 处理传递给接口的参数
        let params = deepCloneObject(ajaxData);

        Metronic.blockUI({target: '#obs_backup_content',animate: true, cenrerY: true,});
        pAjaxRequest(params, "/api/v1/s3/backup_job", "POST", (result) => {
            Metronic.unblockUI('#obs_backup_content');

            if (result.success) {
                UIToastr.showSuccess(LANG.UI_OBS_CREATE_BACKUP_TASK_SUCCESS);

                LOCATION('./content/platform/jobs/jobs.php', 'task');
            } else {
                UIToastr.showWarning(LANG.UI_OBS_CREATE_BACKUP_TASK_FAILED, result.message);
            }
        })
    }

    /**
     * 修改备份请求
     */
    const requestForEditBackup = () => {
        // 处理传递给接口的参数
        let params = deepCloneObject(ajaxData);

        Metronic.blockUI({target: '#obs_backup_content',animate: true, cenrerY: true,});
        pAjaxRequest(params, "/api/v1/s3/backup_job", "PUT", (result) => {
            Metronic.unblockUI('#obs_backup_content');

            if (result.success) {
                UIToastr.showSuccess(LANG.UI_OBS_EDIT_BACKUP_TASK_SUCCESS);

                LOCATION('./content/platform/jobs/jobs.php', 'task');
            } else {
                UIToastr.showWarning(LANG.UI_OBS_EDIT_BACKUP_TASK_FAILED, result.message);
            }
        })
    }

    /**
     * 创建备份表单提交
     */
    const submit = () => {
        if('' === $.trim($("#jobname").val())){
            $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
            return;
        }

        $('.jobnametip').hide();
        let jobName = $.trim($("#jobname").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
        ajaxData.job_name = $.trim($("#jobname").val());
        ajaxData.strategy_group_uuid = $('#strategySelect option:selected').val();

        if (EDIT_BACKUP_FLAG) { // 修改备份
            if (pageIndex === 0) {
                if (!step1Valid((valid) => {
                    if (valid) {
                        requestForEditBackup();
                    }
                })) {
                    return false;
                }
            } else if (pageIndex === 1) {
                if (!step2Valid()) {
                    return false;
                }
            } else if (pageIndex === 2) {
                if (!step3Valid()) {
                    return false;
                }
            }

            requestForEditBackup();
        } else { // 创建备份
            requestForCreateBackup();
        }
    }

    // <-------------------------END STEP FOUR BACKUP CONFIRM----------------------------------->


    /**
     * 步骤导航栏初始化
     */
    const wizardInit = () => {
        if (!jQuery().bootstrapWizard) {
            return;
        }

        let form = $('#obs_backup_form');
        let error = $('.alert-danger', form);
        let success = $('.alert-success', form);

        const handleTitle = (tab, navigation, index) => {
            let total = navigation.find('li').length;
            let current = index + 1;
            let li_list = navigation.find('li');

            jQuery('li', $('#obs_backup_content')).removeClass("done");

            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            // 修改备份任务时把最大步骤存入内存中 用于判断提交的按钮显示
            if (EDIT_BACKUP_FLAG && current >= currentmax){
            	currentmax = current;
            }

            if (current === 1) {
                $('#obs_backup_content').find('.button-previous').css('visibility', 'hidden');
                $('#obs_backup_content').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#obs_backup_content').find('.button-previous').css('visibility', 'visible');
                $('#obs_backup_content').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#obs_backup_content').find('.button-next').hide();
                $('#obs_backup_content').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#obs_backup_content').find('.button-next').show();
                $('#obs_backup_content').find('.button-submit').css('visibility', 'hidden');
            }

            // 需求变动，暂时注释之前修改第一步第二步第三步也显示提交按钮的逻辑 -> 创建和修改都只能第四步提交
            // if (current >= total) {
            //     $('#obs_backup_content').find('.button-next').hide();
            //     if (!EDIT_BACKUP_FLAG) {
            //         $('#obs_backup_content').find('.button-submit').css('visibility', 'visible');
            //     }
            // } else {
            //     $('#obs_backup_content').find('.button-next').show();
            //     if (!EDIT_BACKUP_FLAG) {
            //         $('#obs_backup_content').find('.button-submit').css('visibility', 'hidden');
            //     }
            // }

            // 用于判断修改备份任务时 是否展示提交按钮
            // if (EDIT_BACKUP_FLAG) {
            //     if (current < currentmax){
            //         $('#obs_backup_content').find('.button-submit').css('visibility', 'hidden');
            //     } else {
            //         $('#obs_backup_content').find('.button-submit').css('visibility', 'visible');
            //     }
            // }

            if(current < total){
                $('#obs_backup_content').find('.button-submit').css('visibility', 'hidden');
            }else{
                $('#obs_backup_content').find('.button-submit').css('visibility', 'visible');
            }
			
            Metronic.scrollTo($('.page-title'));
        }

        // 初始化步骤导航栏
        $('#obs_backup_content').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: () => {
                return false;
            },
            onNext: (tab, navigation, index) => {
                switch(index){
                	case 1:
                        if(step1Valid(
							() => {
								$('a[href="#tab2"]').tab('show'); // 手动切换步骤页面
								handleTitle(tab, navigation, index);
								}
						) == false){
                            if (EDIT_BACKUP_FLAG) {
                                pageIndex = 1;
                            }

                			return false;
                		}

                        if (EDIT_BACKUP_FLAG) {
                            pageIndex = 1;
                        }

                		break;
                	case 2:
                		if (step2Valid() === false){
                			return false;
                		}

                        if (EDIT_BACKUP_FLAG) {
                            pageIndex = 2;
                        }

                		break;
                	case 3:
                		if (step3Valid() === false){
                			return false;
                		}

                        if (EDIT_BACKUP_FLAG) {
                            pageIndex = 3;
                        }

                		break;
                }

                handleTitle(tab, navigation, index);
            },
            onPrevious: (tab, navigation, index) => {
                if (EDIT_BACKUP_FLAG) {
                    pageIndex = index;
                }
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },
            onTabShow: (tab, navigation, index) => {
                let total = navigation.find('li').length;
                let current = index + 1;
                let $percent = (current / total) * 100;
                $('#filebackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#obs_backup_content').find('.button-previous').css('visibility', 'hidden');
        $('#obs_backup_content .button-submit').click(submit).css('visibility', 'hidden');

        // 需求变动，暂时注释之前修改第一步第二步第三步也显示提交按钮的逻辑 -> 创建和修改都只能第四步提交
        // if (EDIT_BACKUP_FLAG) {
        //     $('#obs_backup_content .button-submit').click(submit).css('visibility', 'visible');
        // } else {
        //     $('#obs_backup_content .button-submit').click(submit).css('visibility', 'hidden');
        // }
    }

    /**
     * 初始化创建备份各监听器
     */
    const initListener = () => {
        // 跳转到对象存储列表页面
        $('#toAdd').on('click',function(){
	    	LOCATION('./content/s3/obsmanager.php', 'infrastructure');
		})

        $('#allFileTree').on('click','button.addInput', wildInputAdd); // 添加通配符输入框
        $('#allFileTree').on('click','.form-group-wildcards__item__close', delInput);
        $('#searchAgent').on('propertychange', debounce).on('input', debounce);
        $('#backuptype').on('change', backupTypeHandler);
        $('#reserveType').on('change', reserveTypeHandler);
        $('#allFileTree').on('change','.wildcardmode', wildcardmodeTypeHandler);

        //节点改变
		$('#selectnode').on('change', initStorageSelect);

        //选择备份策略复选框
        $('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);

        //切换限速模式
        $('#speedModeType').on('change', speedModeHandler);

        //切换存储加密开关
		$('#encryptStorageCheck').on('switchChange.bootstrapSwitch', encryptChange);
		//切换自动选择存储加密密码
		$('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
        // 密码展示
        $('.show-password-btn').on('click', showPassword);
        // 确认密码展示
        $('.show-rePassword-btn').on('click', showPassword);

        //数据加密密码确认
		$('#repassword,#password').on('input propertychange', function(){
            if (EDIT_BACKUP_FLAG) {
                $('#password').attr('placeholder', '');
                $('#repassword').attr('placeholder', '');
            }

            PASSWORD_HASCHANGED_FLAG = true; // 触发修改过密码flag

			let password = $.trim($('#password').val());
			let repassword = $.trim($('#repassword').val());
			if(password !== repassword){
				$('.passwordTips').show();
			} else {
				$('.passwordTips').hide();
			}
		});

        // 完备勾选
        $('#fullBackup').on('ifChecked', function() {
            // 取消勾选永久增量
            $('#pincrBackup').iCheck('uncheck');

            $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
            $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
            let des = '';
            let strategyConfig = $('#backupTimestrategy').getStrategyConfig();

            des += strategyConfig.fullInfo.des + ". ";

            $('.backupTimeDes').html(des);
            $('.backupTimeDes').prop('title', des);
        })
        // 完备取消勾选
        $('#fullBackup').on('ifUnchecked', function() {
            // 取消勾选增备、差备和永久增量
            $('#incrBackup').iCheck('uncheck');
            $('#diffBackup').iCheck('uncheck');

            $('.backupTimeDes').html('');
        })

        // 增备勾选
        $('#incrBackup').on('ifChecked', function() {
            $('#fullBackup').iCheck('check'); // 勾选完备
            $('#diffBackup').iCheck('enable'); // 启用差备
            $('#diffBackup').iCheck('uncheck'); // 取消勾选差备
            $('#pincrBackup').iCheck('uncheck'); // 取消勾选永久增量
            $('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
            $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();

            let des = '';
            let strategyConfig = $('#backupTimestrategy').getStrategyConfig();

            des += strategyConfig.fullInfo.des + ". " + strategyConfig.incrInfo.des + ". ";
            $('.backupTimeDes').html(des);
            $('.backupTimeDes').prop('title', des);
        })
        // 增备取消勾选
        $('#incrBackup').on('ifUnchecked', function() {
            let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
            let des = strategyConfig.fullInfo.des + ". ";

            $('.backupTimeDes').html(des);
            $('.backupTimeDes').prop('title', des);
        })

        // 差备勾选
        $('#diffBackup').on('ifChecked', function() {
            $('#fullBackup').iCheck('check'); // 勾选完备
            $('#incrBackup').iCheck('enable'); // 启用增备
            $('#incrBackup').iCheck('uncheck'); // 取消勾选增备
            $('#pincrBackup').iCheck('uncheck'); // 取消勾选永久增量
            $('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
            $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();

            let des = '';
            let strategyConfig = $('#backupTimestrategy').getStrategyConfig();

            des += strategyConfig.fullInfo.des + ". " + strategyConfig.diffInfo.des + ". ";
            $('.backupTimeDes').html(des);
            $('.backupTimeDes').prop('title', des);
        });
        // 差备取消勾选
        $('#diffBackup').on('ifUnchecked', function() {
            let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
            let des = strategyConfig.fullInfo.des + ". ";

            $('.backupTimeDes').html(des);
            $('.backupTimeDes').prop('title', des);
        });

        // 永增勾选
        $('#pincrBackup').on('ifChecked', function() {
            // 取消勾选完备、增备和差备
            $('#fullBackup').iCheck('uncheck');
            $('#incrBackup').iCheck('uncheck');
            $('#diffBackup').iCheck('uncheck');

            $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').hide();
            $('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
            $('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();

            let des = '';
            let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
            des += strategyConfig.pIncrInfo.des + ". ";

            $('.backupTimeDes').html(des);
            $('.backupTimeDes').prop('title', des);
            
            initReserveStrategyDes();

            // 禁用worm配置
            $('#worm_protect_check').bootstrapSwitch('state', false);
            $('#worm_protect_check').bootstrapSwitch('disabled', true);

            $('.worm-disabled-form-group').removeClass('display-none');
            // 将选择存储资源池的提示隐藏
            $('.form-group-unselected-storage').addClass('display-none');
            // 将未配置worm的存储设备的提示隐藏
            $('.form-group-unworm-config').addClass('display-none');
        });
        // 永增取消勾选
        $('#pincrBackup').on('ifUnchecked', function() {
            $('.backupTimeDes').html('');

            $('#worm_protect_check').bootstrapSwitch('disabled', false);

            $('.worm-disabled-form-group').addClass('display-none');

            let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
            // WORM防护禁用逻辑
            if (!backupTargetInfo.storage_uuid) { // 如果选择的是存储资源池则禁用worm防护，且开关保持关闭
                $('#worm_protect_check').bootstrapSwitch('state', false);
                $('#worm_protect_check').bootstrapSwitch('disabled', true);

                $('.form-group-unselected-storage').removeClass('display-none');
                $('.form-group-unworm-config').addClass('display-none');
            } else { // 如果选择的是存储设备
                if (backupTargetInfo.storage_worm_config.flag) { // 存储设备开启了worm防护，则worm不禁用
                    $('#worm_protect_check').bootstrapSwitch('disabled', false);

                    $('.form-group-unselected-storage').addClass('display-none');
                    $('.form-group-unworm-config').addClass('display-none');
                } else { // 存储设备未开启worm防护，则worm关闭且禁用
                    $('#worm_protect_check').bootstrapSwitch('state', false);
                    $('#worm_protect_check').bootstrapSwitch('disabled', true);

                    $('.form-group-unselected-storage').addClass('display-none');
                    $('.form-group-unworm-config').removeClass('display-none');
                }
            }
        });

        // 一次性备份日期选择change
        $('#oncetime').on('change', () => {
            let des = LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
            
            $('.backupTimeDes').html(des);
            $('.backupTimeDes').prop('title', des);
        });

        $('#resetdate').on('click',function(){
            $('#oncetime').val('');
            $('.backupTimeDes').html('');
            $('.backupTimeDes').prop('title', '');
        });

        //初始化存储策略配置监听
		initStoreListeners();
		//初始化保留策略配置监听
		initReserveListeners();
        //扫描文件
		$("#scanFileNum").on('change', function(){
            initHighStrategyDes();
		});

        //跳过文件告警智能判断
		$('#passfilealarmcheck').on('switchChange.bootstrapSwitch', passAlarmChange);
        // 压缩传输
        $('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);
        // 压缩等级改变
        $('#compressGrade').on('change', initStoreStrategyDes);

        // 传输代理switch change
        $('#appliance_agency_flag').on('switchChange.bootstrapSwitch', applianceSwitchChange);

        // 加密传输 switch change
        $('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptSwitchChange);

        // 操作异常重试 - 自动重试 switch change
        $('#op_retry_flag').on('switchChange.bootstrapSwitch', opRetrySwitchChange);
        
        // 任务重试 - 自动重试 switch change
        $('#task_retry_flag').on('switchChange.bootstrapSwitch', taskRetrySwitchChange);

        // 目标存储 select change
        $('#selectstorage').on('change', storageTypeChangeEvent);

        // 保留策略 - 备份数据保留类型显示
        $('.reserveModeDiv').show();

        // 保留策略 - 备份数据保留类型change
        $('#reserveMode').on('change', () => {
            initReserveStrategyDes();
        });

        // 安全策略 wrom防护 switch change
        $('#worm_protect_check').on('switchChange.bootstrapSwitch', wormProtectSwitchChange);

        // 安全策略 - 完整性校验
        $('#integrality_check').on('switchChange.bootstrapSwitch', integralitySwitchChange);

        // 初始化安全策略单选框组 
        $('#verify_strategy_panel input[type=radio]').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});

        // 安全策略 - 校验周期 radio group change
        $('#check_cycle').find('.icheck-verify-cycle').on('ifClicked', verifyModeChange);

        // 安全策略 - 备份点异常 radio group change
        $('#backup_point_abnormal').find('.icheck-backup-point-abnormal').on('ifClicked', backupPointModeChange);

        // 高级配置 - 过载保护 显示资源的配置信息

    }

    const inintDatatimePicker = () => {
        if (CONF.LANGUAGE !== "zh-cn" && CONF.LANGUAGE !== "zh-tw") {
            //英文独有的
            $(".form_datetime").datetimepicker({
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-mm-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        } else {
            $(".form_datetime").datetimepicker({
                language:  'zh-CN',
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-MM-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }
    }

    /**
     * 应用全局备份策略
     * @returns {boolean}
     */
    const strategyHandler = () => {
        $('.passwordTips').hide(); // 隐藏上一次的校验提示

        let strategyId = $('#strategySelect option:selected').val();

        if (!strategyId) { // 未应用全局策略
            // 重置当前应用的全局策略对象
            appliedGlobalStrategy = {
                flag: false,
                uuid: '',
                strategy: {}
            };

            return
        } else { // 应用了全局策略
            let strategy = globalStrategy[strategyId];

            // 赋值当前应用的全局策略对象
            appliedGlobalStrategy.flag = true;
            appliedGlobalStrategy.uuid = strategyId;
            appliedGlobalStrategy.strategy = strategy;

            // 初始化全局策略数据
            $('#tab_common').backupStrategy(CONF.MODULE_TYPE.OBS, false, strategy, 0);

            // 限速策略
            let speedInfo = strategy.speedlimit;
            let check = strategy.speedlimit.check;

            if(!check || !speedInfo) return ;
            speedList = [];
            // 将限速策略放进消息中
            speedList = speedInfo.map(i => { return { ...i }});

            // 时间策略
            initTimeStrategyDes();

            // 存储策略
            initStoreStrategyDes();

            // 保留策略
            initReserveStrategyDes();

        }  
    }

    /**
     * 初始化策略选择下拉框
     */
    const initStrategySelect = () => {
        if (initStrategyFlag) {
            return;
        }

        pAjaxRequest({type: CONF.MODULE_TYPE.OBS}, "/api/v1/strategies/select", "GET", (result) => {
            if (result.success) {
                $('#strategySelect').empty();

                if (result.data && result.data.length > 0) {
                    result.data.forEach(item => {
                        let option = '<option  value="' + item.uuid + '">' + item.text + '</option>';
                        globalStrategy[item.uuid] = item.strategy || [];
                        $('#strategySelect').append(option);
                    })

                    if (!initStrategyFlag) {
                        $('#strategySelect').searchableSelect();
                        $('#obs_backup_strategy_select .searchable-select-item').on('click', strategyHandler);
                        initStrategyFlag = true;
                    }
                }
            }
        })
    }

    /**
     * 初始化修改备份任务时选择的全局策略
     */
    const initCheckedStrategySelect = () => {
        if (initStrategyFlag) {
            return;
        }

        pAjaxRequest({type: CONF.MODULE_TYPE.OBS}, "/api/v1/strategies/select", "GET", (result) => {
            if (result.success) {
                $('#strategySelect').empty();

                if (result.data && result.data.length > 0) {
                    result.data.forEach(item => {
                        let option = '<option  value="' + item.uuid + '">' + item.text + '</option>';
                        globalStrategy[item.uuid] = item.strategy || [];
                        $('#strategySelect').append(option);
                    })

                    if (taskInfoSetting.strategy_group_uuid) {
                        $('#strategySelect').val(taskInfoSetting.strategy_group_uuid)
                    }

                    if (!initStrategyFlag) {
                        $('#strategySelect').searchableSelect();
                        $('#obs_backup_strategy_select .searchable-select-item').on('click', strategyHandler);
                        initStrategyFlag = true;
                    }
                }
            }
        })
    }

    const initData = function (){
        //setp1
        ajaxData.srcInfo.fileInfo = [];
        // ajaxData.srcInfo.agentUUID = '';
        ajaxData.srcInfo.agentList = [];
        ajaxData.srcInfo.groupList = [];
        //setp2
        //备份方式:策略/时间
        ajaxData.backupInfo.type = 'strategy';
        //完备/增备/差备
        ajaxData.backupInfo.full_info = {};
        ajaxData.backupInfo.incr_info = {};
        ajaxData.backupInfo.diff_info = {};
        //按时间备份的时间
        ajaxData.backupInfo.datetime = '';
        //setp3
        //保留策略
        ajaxData.highInfo.reserve = {};
        ajaxData.highInfo.reserve.type = 1;
        ajaxData.highInfo.transfer = {};
        ajaxData.highInfo.store = {};
        ajaxData.highInfo.node = {};
        ajaxData.highInfo.newstr = {wildcardList:[]};

        $('#encryptStorageCheck').bootstrapSwitch('state', false);  //默认关闭数据加密
        // 文件备份不支持功能
        $('.deduplicationdiv').hide();
        // $('.pIncr').hide();
        $('.GFSdiv').hide();
    }

    /**
     * 接口返回的任务信息赋值给ajax data对象
     */
    const initDefaultAjaxData = () => {
        // STEP ONE 备份源
        ajaxData.srcInfo.fileInfo = [];
        ajaxData.srcInfo.agentList = [];
        ajaxData.srcInfo.groupList = [];
        ajaxData.srcInfo.fileInfo = taskInfoSetting.fileInfo && taskInfoSetting.fileInfo.length > 0 ? taskInfoSetting.fileInfo.map(i => { return {...i} }) : [];

        if (ajaxData.srcInfo.fileInfo.length > 0) {
            let offlineObsList = taskInfoSetting.checkedObsList.filter(i => { return i.status === 0 }); // 过滤出离线或未授权状态的对象存储ObsList

            const obsUuidToGroupUuidMap = new Map(offlineObsList.map(v => [v.obs_uuid, v.group_uuid])); // 创建一个obs_uuid到group_uuid 的Map
  
            checkedOfflineObsFiles = ajaxData.srcInfo.fileInfo.filter(i => {  
                // 检查 Map 中是否存在当前 fileInfo 的 agent_uuid  
                return obsUuidToGroupUuidMap.has(i.agent_uuid);  
            }).map(i => ({  
                // 如果存在，则返回带有 group_uuid 的新对象  
                type: i.type,
                code_type: i.code_type,
                uuid: i.agent_uuid,
                obs_uuid: i.agent_uuid,
                filepath: i.path,
                group_uuid: parseInt(obsUuidToGroupUuidMap.get(i.agent_uuid))  
            }));

            checkedOfflineObsWildcards = taskInfoSetting.high.wild_card_info.filter(i => {
                // 检查 Map 中是否存在当前 wild_card_info 的 obs_uuid  
                return obsUuidToGroupUuidMap.has(i.obs_uuid);  
            }).map(i => ({ 
                obs_uuid: i.obs_uuid, 
                wildcardList: parseInt(i.wildcard_mode) === 0 ? [] : i.wildcard,  
                wildcardMode: parseInt(i.wildcard_mode),  
            }));
        }

        // STEP TWO 备份目的地
        ajaxData.highInfo.node = {};
        ajaxData.highInfo.node.nodecheck = false;
        ajaxData.highInfo.node.node_uuid = taskInfoSetting.node.node_uuid;
        ajaxData.highInfo.node.node_pool_uuid = taskInfoSetting.node.node_pool_uuid;
        ajaxData.highInfo.node.storage_uuid = taskInfoSetting.node.storage_uuid;
        ajaxData.highInfo.node.storagecheck = !ajaxData.highInfo.node.storage_uuid;
        ajaxData.highInfo.node.storage_pool_uuid = taskInfoSetting.node.storage_pool_uuid;

        // STEP THREE 备份策略
        let timeStrategyBackupType = taskInfoSetting.time_strategy_backup_type;

        // 备份策略 - 时间策略
        ajaxData.backupInfo.full_info = {};
        ajaxData.backupInfo.incr_info = {};
        ajaxData.backupInfo.diff_info = {};
        ajaxData.backupInfo.pincr_info = {};

        switch (timeStrategyBackupType) {
            case TIME_BACKUP_STRATEGY_TYPE.STRATEGY: // 按策略备份
                ajaxData.backupInfo.type = 'strategy'; // 备份方式
                $('#backuptype').val('strategy'); // 设置备份方式

                let timeStrategys = taskInfoSetting.time_strategy.data;

                for (let i = 0; i < timeStrategys.length; i++) {
                    let info = {}, days = [];

                    for (let j = 0; j < timeStrategys[i].days.length; j++) {
                        if (timeStrategys[i].days.length === 1 && !timeStrategys[i].days[j]) {
                            days = [];
                        } else if (timeStrategys[i].days[j]) {
                            timeStrategys[i].days[j] = 1;
                            days.push(timeStrategys[i].days[j]);
                        } else if (!timeStrategys[i].days[j]) {
                            timeStrategys[i].days[j] = 0;
                            days.push(timeStrategys[i].days[j]);
                        }
                    }

                    info.days = days;
                    info.mode = timeStrategys[i].mode;
                    info.type = timeStrategys[i].strategy_type;
                    info.start_time = timeStrategys[i].start_time;
                    info.roll_flag = timeStrategys[i].roll_flag;
                    info.roll_interval = timeStrategys[i].roll_interval;
                    info.roll_end_time = timeStrategys[i].roll_end_time;
                    info.frequency = timeStrategys[i].frequency;
                    info.full_backup_compensation_flag = timeStrategys[i].full_backup_compensation_flag;

                    switch (timeStrategys[i].mode) {
                        case 1: // 完全备份
                            ajaxData.backupInfo.full_info = info;
                            break;
                        case 2: // 增量备份 || 永久增量
                            // 永久增量和增量备份后台存的MODE都是2，如果timeStrategys只有一条即为永久增量，否则为增量备份
                            if (timeStrategys.length === 1) {
                                // 后台返回的mode是2，为方便插件处理改成对应的永久增量9
                                ajaxData.backupInfo.pincr_info = Object.assign(info, { mode: 9});
                            } else {
                                ajaxData.backupInfo.incr_info = info;
                            }
                            break;
                        case 3: // 差异备份
                            ajaxData.backupInfo.diff_info = info;
                            break;
                        case 9: // 永久增量
                            ajaxData.backupInfo.pincr_info = info;
                            break;
                        default:
                            break;
                    }
                }

                break;
            case TIME_BACKUP_STRATEGY_TYPE.ONCETIME: // 一次性备份
                ajaxData.backupInfo.type = 'oncetime'; // 备份方式
                $('#backuptype').val('oncetime'); // 设置备份方式

                ajaxData.backupInfo.datetime = taskInfoSetting.time_strategy.data;

                break;
            case TIME_BACKUP_STRATEGY_TYPE.MANUAL: // 手动备份
                ajaxData.backupInfo.type = 'manual'; // 备份方式
                $('#backuptype').val('manual'); // 设置备份方式

                break;
            default:
                break;
        }

        // 备份策略 - 限速策略
        speedList = taskInfoSetting.speedInfo;
        ajaxData.speedLimit = taskInfoSetting.speedInfo;
        ajaxData.speedLimit.speed = taskInfoSetting.speedInfo.speedInfo;
        if (taskInfoSetting.speedInfo.type === 1) { // 全局限速
            ajaxData.speedLimit.uuid = taskInfoSetting.speedInfo.uuid;
        }

        // 备份策略 - 保留策略
        ajaxData.highInfo.reserve = {};
        ajaxData.highInfo.reserve.strategyMode = taskInfoSetting.brs.strategy_mode;
        ajaxData.highInfo.reserve.type = taskInfoSetting.brs.type;
        ajaxData.highInfo.reserve.value = taskInfoSetting.brs.number === 0 ? 30 : taskInfoSetting.brs.number;

        // 备份策略 - 传输策略
        ajaxData.highInfo.transfer = {};
        ajaxData.highInfo.transfer.mode = taskInfoSetting.bts.mode;
        ajaxData.highInfo.transfer.network = taskInfoSetting.bts.network;
        ajaxData.highInfo.transfer.appliance_agency_flag = taskInfoSetting.bts.appliance_agency_flag;
        ajaxData.highInfo.transfer.appliance_uuid = taskInfoSetting.bts.appliance_uuid;
        ajaxData.highInfo.transfer.appliance_pool_uuid = taskInfoSetting.bts.appliance_pool_uuid.appliance_pool_uuid;
        ajaxData.highInfo.transfer.encrypt = taskInfoSetting.bts.encrypt;
        ajaxData.highInfo.transfer.encrypt_method = taskInfoSetting.bts.encrypt_method;

        // 备份策略 - 存储策略
        ajaxData.highInfo.store = {};
        ajaxData.highInfo.store.deduplication_flag = taskInfoSetting.bss.deduplication_flag; // 重复数据删除
        ajaxData.highInfo.store.compress = taskInfoSetting.bss.compress;//压缩
        ajaxData.highInfo.store.dataencrypt = taskInfoSetting.bss.encrypt; //数据加密
        ajaxData.highInfo.store.password_auto_flag = taskInfoSetting.bss.password_auto_flag;//自动生成密码
        ajaxData.highInfo.store.password = taskInfoSetting.bss.password; // 密码
        ajaxData.highInfo.store.compress_method = taskInfoSetting.bss.compress_method; // 压缩等级
        ajaxData.highInfo.store.encrypt_method = taskInfoSetting.bss.encrypt_method; // 加密算法
        firstInitPageFlag = true;

        // 备份策略 - 高级策略
        ajaxData.highInfo.newstr = {};
        ajaxData.highInfo.newstr.backupThreadNum = taskInfoSetting.high.thread_num;//线程数量
        ajaxData.highInfo.newstr.scanThreadNum = taskInfoSetting.high.scan_thread_num;//扫描线程
        ajaxData.highInfo.newstr.scanFileNum = taskInfoSetting.high.scan_file_num;//扫描文件速度
        ajaxData.highInfo.permission_operate_flag = taskInfoSetting.high.permission_operate_flag;//文件权限备份

        // 高级配置 - 异常处理
        ajaxData.highInfo.skip_file_alarm_flag = taskInfoSetting.high.skip_file_alarm_flag;//跳过文件告警
        ajaxData.highInfo.skip_file_alarm_min_num = taskInfoSetting.high.skip_file_alarm_min_num;
        ajaxData.highInfo.skip_file_alarm_min_ratio = taskInfoSetting.high.skip_file_alarm_min_ratio;

        // 高级配置 - 重试策略
        ajaxData.highInfo.network_retry_times = taskInfoSetting.retry_strategy.network_retry_times;
        ajaxData.highInfo.network_retry_interval = taskInfoSetting.retry_strategy.network_retry_interval;

        ajaxData.highInfo.op_retry_flag = taskInfoSetting.retry_strategy.op_retry_flag;
        ajaxData.highInfo.op_retry_times = taskInfoSetting.retry_strategy.op_retry_times;
        ajaxData.highInfo.op_retry_interval = taskInfoSetting.retry_strategy.op_retry_interval;

        ajaxData.highInfo.task_retry_flag = taskInfoSetting.retry_strategy.task_retry_flag;
        ajaxData.highInfo.task_retry_object = taskInfoSetting.retry_strategy.task_retry_object;
        ajaxData.highInfo.task_retry_times = taskInfoSetting.retry_strategy.task_retry_times;
        ajaxData.highInfo.task_retry_interval = taskInfoSetting.retry_strategy.task_retry_interval;

        // 高级配置 - 过载保护
        ajaxData.highInfo.ignore_resource_limiting_flag = taskInfoSetting.high.ignore_resource_limiting_flag;

        // 安全策略
        ajaxData.safe_config_strategy.worm_flag = taskInfoSetting.safe_config_strategy.worm_flag;
        ajaxData.safe_config_strategy.worm_protection_time = taskInfoSetting.safe_config_strategy.worm_protection_time;
        ajaxData.safe_config_strategy.integrity_check_flag = taskInfoSetting.safe_config_strategy.integrity_check_flag;
        ajaxData.safe_config_strategy.integrity_check_config.check_strategy = taskInfoSetting.safe_config_strategy.integrity_check_config.check_strategy;
        ajaxData.safe_config_strategy.integrity_check_config.full_error_policy = taskInfoSetting.safe_config_strategy.integrity_check_config.full_error_policy;

        //任务信息
        ajaxData.taskName = taskInfoSetting.job_name;
        ajaxData.taskuuid = taskInfoSetting.job_uuid;

        $('#encryptStorageCheck').bootstrapSwitch('state', false);  //默认关闭数据加密
        // 文件备份不支持功能
        $('.deduplicationdiv').hide();
        // $('.pIncr').hide();
        $('.GFSdiv').hide();
        // 任务名
        $('#jobname').val(taskInfoSetting.job_name);
    }

    /**
     * 获取备份任务信息
     */
    const initTaskInfoSettings = () => {
        pAjaxRequest({info_uuid: taskId}, '/api/v1/s3/backup_info', 'get', (res) => {
            if (res.success) {
                taskInfoSetting = { ...res.data, checkedObsList: res.data.checkedObsList };
                
                initDefaultAjaxData(); // ajax data赋默认值
                initTree(); // 初始化备份源对象存储树
                initCheckedNodeAndStorage(); // 初始化已选择的节点和存储
                initCheckedTaskCrowList(); // 初始化修改时选择的任务分布区间
                initCheckedStrategySelect(); // 初始化修改时选择的全局策略下拉列表
                initCheckedTimeStrategy(); // 初始化修改时选择的时间策略
                initCheckedSpeedLimitStrategy(); // 初始化修改时选择的限速策略
                initCheckedStorageStrategy(); // 初始化修改时选择的存储策略
                initCheckedReserveStrategy(); // 初始化修改时选择的保留策略
                initCheckedAdvancedStrategy(); // 初始化修改时选择的高级策略
                initNetworkList(taskInfoSetting.node.node_uuid);// 获取传输网络列表
                initCheckedTransmitStrategy(); // 初始化修改时选择的传输策略
                initCheckedSafeStrategy(); // 初始化修改时选择的安全策略
                initCheckedAdvancedConfig(); // 初始化修改时选择的高级配置
            }
        });
    }

    /**
     * 初始化路由参数
     */
    const initRouteParams = () => {
        let route = History.getState();
        let uuidStr = route.data.url.split('?')[1] || '';

        EDIT_BACKUP_FLAG = !!route.data.url.split('?')[1]; // 判断是创建备份还是修改备份
        taskId = !!route.data.url.split('?')[1] ? uuidStr.split('=')[1] : ''; // 保存taskId

        if (EDIT_BACKUP_FLAG) {
            $('.caption_text').html(LANG.UI_OBS_EDIT_OBS_BACKUP_TASK);
            initTaskInfoSettings(); // 获取任务信息
        } else {
            $('.caption_text').html(LANG.UI_OBS_CREATE_OBS_BACKUP_TASK);
            initData(); // 初始化创建备份ajax data
            initTree(); // 初始化备份源对象存储树
            initTargetStorageAndNode(); // 初始化目标存储和节点
            initStrategySelect(); // 初始化全局策略下拉列表
            initTimeStrategy(); // 初始化时间策略
            $('#integrality_check').bootstrapSwitch('state', true); // 创建备份默认开启完整性校验
            $('.integrality-check-form-group').removeClass('display-none');
            $('#task_retry_flag').bootstrapSwitch('state', false); // 创建备份默认关闭任务自动重试
            $('.task-retry-wrap').hide();
        }

        // 未授权worm时隐藏worm配置选项及提示
        if (!CONF.FUNCTIONS.includes('worm')) {
            // 步骤三 worm配置选项隐藏
            $('.worm-config-wrapper').addClass('display-none');

            // 步骤四 worm确认配置选项隐藏
            $('.confirm-config-wrom-config-wrapper').addClass('display-none');
        } else {
            $('.worm-config-wrapper').show();
            $('.confirm-config-wrom-config-wrapper').removeClass('display-none');
        }

        // 未授权完整性校验时隐藏完整性配置选项
        if (!CONF.FUNCTIONS.includes('integrity')) {
            // 步骤三 完整性校验配置选项隐藏
            $('.integrality-check-wrapper').addClass('display-none');

            // 完整性校验开关置为false
            $('#integrality_check').bootstrapSwitch('state', false);
            $('.integrality-check-form-group').addClass('display-none');

            // 步骤四 完整性校验确认配置选项隐藏
            $('.confirm-config-interity-wrapper').addClass('display-none');

        } else {
            $('.integrality-check-wrapper').removeClass('display-none');
            $('.confirm-config-interity-wrapper').removeClass('display-none');
        }

        // worm配置和完整性配置均为授权，直接隐藏 tab_safety
        if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
            // 均未授权隐藏整个安全策略tab
            $('.safetyLi').hide();
            $('#tab_safety').addClass('display-none');

            // 均未授权隐藏整个安全策略确认配置
            $('.confirm-config-safety-form-group').addClass('display-none');
        } else {
            $('.safetyLi').show();
            $('#tab_safety').removeClass('display-none');
            $('.confirm-config-safety-form-group').removeClass('display-none');
        }
    }

    return {
        init: () => {
            initRouteParams();
            wizardInit();
            inintDatatimePicker();
            initSpinner();
            initListener();
        }
    }
}();

jQuery(document).ready(function() {
    ObsBackup.init();
});