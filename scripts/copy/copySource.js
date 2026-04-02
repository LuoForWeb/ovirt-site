/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2024-12-25 15:36:36
 * @LastEditTime: 2026-03-20 11:32:34
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 * 用法：
 * 用于初始化副本相关的数据源树
 * $('.copy-source-div').mySource({editFlag: editFlag,module_type: MODULE_TYPE, sub_module_type: SUB_MODULE_TYPE, oldInfo: SETTINGS, remoteFlag: remoteFlag });
 */
(function ($) {
    $.fn.mySource = function (options, callback = null) {
        const defaults = {
            module_type: CONF.MODULE_TYPE.VM, // 模块类型
            sub_module_type: 0, // 子模块类型
            editFlag: false, // 修改页面初始化源标志
            remoteFlag: false, // 异地数据源标志
            oldInfo: '', // 修改页面初始化使用数据
            copy_back: false, // 回传源标志
            editFirstInitFlag: true, // 是否是修改首次初始化标志
            archive_flag: false, // 归档标志
            archive_back_flag: false, // 归档回传标志
        };
        const settings = { ...defaults, ...options };
        const dom = $(this);
        const READ_ONLY = CONF.BD_STORAGE_USE_MODE.READ_ONLY; // 存储只读
        // 修改页面传入参数
        const SETTINGS = settings.oldInfo;
        // 回传存储类型枚举
        const BACK_STORAGE_TYPE = [CONF.BD_STORAGE_TYPE.REMOTE, CONF.BD_STORAGE_TYPE.CLOUD];
        const COPT_BACK_STORAGE_TYPE = [CONF.BD_STORAGE_TYPE.REMOTE, CONF.BD_STORAGE_TYPE.CLOUD, CONF.BD_STORAGE_TYPE.TAPE];
        // 异地存储枚举
        const REMOTE_STORAGE_TYPE = [CONF.BD_STORAGE_TYPE.REMOTE];
        const ASYNC_API_TIME_POINT = 'timepoint';
        // 树节点类型
        const TREE_NODE_TYPE = {
            TASK: 'task',
            HOST: 'host',
            INSTANCE: 'instance',
            POINT: 'point',
            FULL: 3,
            UN_FULL: 4,
        };
        // 副本源类型枚举
        const COPY_SOURCE = {
            BACKUP_TASK: 1,
            BACKUP_DATA: 2,
            COPY_TASK: 3,
            COPY_DATA: 4,
        }
        // 模块提示信息枚举
        const MODULE_NO_DATA_TIP = {
            4: LANG.UI_COPY_NO_DB_DATA_REDIRECT,
            5: LANG.UI_COPY_NO_OS_DATA_REDIRECT,
            11: LANG.UI_COPY_NO_NAS_DATA_REDIRECT,
            14: LANG.UI_COPY_NO_M365_DATA_REDIRECT,
            28: LANG.UI_COPY_NO_KUBE_DATA_REDIRECT,
        };
        // 文件子模块
        const MODULE_FS_NO_DATA_TIP = {
            1: LANG.UI_COPY_NO_FS_DATA_REDIRECT,
            2: LANG.UI_COPY_NO_NAS_DATA_REDIRECT,
            3: LANG.UI_COPY_NO_HADOOP_DATA_REDIRECT,
            4: LANG.UI_COPY_NO_S3_DATA_REDIRECT,
        }
        // 虚拟机子模块
        const MODULE_VM_NO_DATA_TIP = {
            1: LANG.UI_COPY_NO_VM_DATA_REDIRECT,
            2: LANG.UI_COPY_NO_VM_DATA_REDIRECT,
            3: LANG.UI_COPY_NO_PUBLIC_CLOUD_DATA_REDIRECT,
        }
        const MODULE_VM_NO_DATA_TIP_ARCHIVE = {
            1: LANG.UI_ARCHIVE_NO_VM_DATA_REDIRECT,
            2: LANG.UI_ARCHIVE_NO_PRIVATE_CLOUD_DATA_REDIRECT,
            3: LANG.UI_ARCHIVE_NO_PUBLIC_CLOUD_DATA_REDIRECT,
        }
        let timePointList = [], zTree = null, nodeParamList = [];
        const _MORE_NODE_LIMIT = 20;
        let temp_item_array = {}; // 暂存对象列表
        let temp_exclude_array = {}; // 暂存排除列表
        // 数据库节点类型
        const DB_NODE_TYPE = 'db_copy';
        const _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");	//虚拟机名正则匹配
        let search = ''; // 搜索关键字
        let offLineStorage = []; // 暂存离线的存储uuid
        // 初始化存储列表
        const initStorage = () => {
            // 添加存储option
            let storageList = [];
            // 初始化存储列表
            let initStorageOption = function (res) {
                Metronic.unblockUI('.src-wrap__content');
                if (res.success && res.data) {
                    dom.find('#storage').empty();//清空下拉框
                    let data = res.data.rows;
                    if (!settings.copy_back && !settings.archive_back_flag) {
                        dom.find('#storage').append(`<option value="">${LANG.UI_STORAGE_ALL_STORAGE}</option>`);
                    }
                    for (let i = 0; i < data.length; i++) {
                        // 保存离线的存储uuid
                        if(data[i].flag == 3){
                            offLineStorage.push(data[i].storage_uuid);
                        }
                        // 副本回传源只能是异地存储或者是云存储
                        if (settings.copy_back) {
                            if (COPT_BACK_STORAGE_TYPE.includes(data[i].storage_type) && data[i].use_mode_value != READ_ONLY) {
                                let remote_ip = data[i].config.remote_ip;
                                let option = `<option data-type="${data[i].storage_type}" data-name="${data[i].storage_nickname}" value="${data[i].storage_uuid}" data-remote_ip="${remote_ip}">${data[i].storage_nickname}</option>`;
                                dom.find('#storage').append(option);
                                storageList.push(data[i].storage_uuid);
                            }
                            // 归档回传源只能是云存储
                        } else if (settings.archive_back_flag) {
                            if (data[i].storage_type == CONF.BD_STORAGE_TYPE.CLOUD && data[i].use_mode_value != READ_ONLY) {
                                let option = `<option data-type="${data[i].storage_type}" data-name="${data[i].storage_nickname}" value="${data[i].storage_uuid}">${data[i].storage_nickname}</option>`;
                                dom.find('#storage').append(option);
                                storageList.push(data[i].storage_uuid);
                            }
                            // 归档源只能是本地存储
                        } else if (settings.archive_flag) {
                            if (!BACK_STORAGE_TYPE.includes(data[i].storage_type) && data[i].use_mode_value != READ_ONLY) {
                                let option = `<option data-type="${data[i].storage_type}" data-name="${data[i].storage_nickname}" value="${data[i].storage_uuid}">${data[i].storage_nickname}</option>`;
                                dom.find('#storage').append(option);
                                storageList.push(data[i].storage_uuid);
                            }
                        } else { // 副本源只能是本地存储、云存储
                            if (data[i].storage_type != CONF.BD_STORAGE_TYPE.REMOTE && data[i].use_mode_value != READ_ONLY) {
                                let option = `<option data-type="${data[i].storage_type}" data-name="${data[i].storage_nickname}" value="${data[i].storage_uuid}">${data[i].storage_nickname}</option>`;
                                dom.find('#storage').append(option);
                                storageList.push(data[i].storage_uuid);
                            }
                        }
                    }
                    // 初始化副本源树
                    initTree();
                }
            }
            Metronic.blockUI({ target: '.src-wrap__content', animate: true });
            pAjaxRequest({ offset: 0, limit: 100, source_type: 1 }, "/api/v1/storages", "GET", initStorageOption, true);
        }

        //获取备份|副本任务和数据树
        const initTree = function () {
            let storage_uuid = $('#storage').val();//选择源存储
            let data_type = parseInt($('#dataType').val());	//选择副本数据类型
            let storageType = parseInt($('#storage option:selected').data('type'));
            // 只有备份虚拟化备份任务才显示自动添加按钮
            if (settings.module_type == CONF.MODULE_TYPE.VM && data_type == 1) {
                $('.auto-add-button-div').removeClass('display-none');
            } else {
                $('.auto-add-button-div').addClass('display-none');
            }
            // 回传提示修改
            if (settings.archive_back_flag) {
                if (CONF.MODULE_TYPE.VM == settings.module_type) {
                    $('#toBackup').text(MODULE_VM_NO_DATA_TIP_ARCHIVE[settings.sub_module_type]);
                } else if (CONF.MODULE_TYPE.OS == settings.module_type) {
                    $('#toBackup').text(LANG.UI_ARCHIVE_NO_OS_DATA_REDIRECT);
                }
            }
            // 如果是修改任务，需要传入任务uuid的数组，用于将副本的任务放到列表最前面
            let source_task_uuid = '';
            let source_item_uuid = '';
            if (settings.editFlag) {
                source_task_uuid = SETTINGS.source_task_uuid;
                source_item_uuid = SETTINGS.source_item_uuid;
            }
            // 获取副本源参数 数据类型  模块类型  存储uuid
            let params = {
                data_type: data_type,
                module_type: settings.module_type,
                storage_uuid: storage_uuid,
                sub_module_type: settings.sub_module_type,
                archive_back_flag: settings.archive_back_flag,
                archive_flag: settings.archive_flag,
                source_task_uuid: source_task_uuid,
                source_item_uuid: source_item_uuid,
            };
            Metronic.blockUI({ target: '.src-wrap__content', animate: true });
            // 回传必须有存储
            if ((settings.copy_back || settings.archive_back_flag) && (storage_uuid == '' || storage_uuid == null)) {
                Metronic.unblockUI('.src-wrap__content');
                emptyDataFunc();
                return;
            }
            // 异地副本数据需要调用异地接口获取
            if (data_type == COPY_SOURCE.COPY_DATA && REMOTE_STORAGE_TYPE.includes(storageType)) {
                pAjaxRequest(params, "/api/v1/copy/resources/remote/task", "GET", remoteTaskBack, true);
            } else {
                pAjaxRequest(params, "/api/v1/copy/resources", "POST", initTreeNode, true);
            }
        }
        //  无数据源时默认显示信息
        let emptyDataFunc = () => {
            // 销毁树
            $.fn.zTree.destroy();
            // 根据模块显示提示
            if (CONF.MODULE_TYPE.VM == settings.module_type) {
                $('#toBackup').text(MODULE_VM_NO_DATA_TIP[settings.sub_module_type]);
            } else if (CONF.MODULE_TYPE.FS == settings.module_type) {
                $('#toBackup').text(MODULE_FS_NO_DATA_TIP[settings.sub_module_type]);
            } else {
                $('#toBackup').text(MODULE_NO_DATA_TIP[settings.module_type]);
            }
            if (settings.archive_back_flag) {
                if (CONF.MODULE_TYPE.VM == settings.module_type) {
                    $('#toBackup').text(MODULE_VM_NO_DATA_TIP_ARCHIVE[settings.sub_module_type]);
                } else if (CONF.MODULE_TYPE.OS == settings.module_type) {
                    $('#toBackup').text(LANG.UI_ARCHIVE_NO_OS_DATA_REDIRECT);
                }
            }
            // 无数据展示提示
            $("#noDataTips").show();
            $('.copyTreeDiv').hide();
            $('#itemList').hide();
        };
        // 初始化回传树
        const remoteTaskBack = function (res) {
            initTreeNode(res, true);
        };
        const initTreeNode = (res, remoteFlag = false) => {
            Metronic.unblockUI('.src-wrap__content');
            let zNodes = res.data;
            //如果没有源树节点显示对应模块提示
            if (zNodes.length == 0 || !res.success) {
                emptyDataFunc();
                return;
            } else {
                $('#copySourceTree').empty().show();
                $('#itemList').show();
            }
            let setting = {
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
                    beforeClick: remoteFlag ? remoteSelect : copyNodeSelect,
                    onCheck: copyOnCheck,
                    beforeExpand: remoteFlag ? remoteExpand : copyNodeExpand,
                },
                view: {
                    nameIsHTML: true,
                    fontCss: getFontCss,
                    addHoverDom: addPointNodeHoverIcon,
                    removeHoverDom: removePointNodeHoverIcon,
                }
            };
            //初始化树对象
            zTree = $.fn.zTree.init($("#copySourceTree"), setting, zNodes);
            // 修改副本时，默认选中副本源
            if (settings.editFlag) {
                // 修改时带入任务的排除列表
                temp_exclude_array = JSON.parse(SETTINGS.copy_ignore_object_list);
                if (!remoteFlag) {
                    initEditLocalSource();
                }
            } else {
                settings.editFirstInitFlag = false;
            }
        }

        /**
         * 添加集群实例的时间点树的hover dom
         * @param treeId
         * @param treeNode
         */
        const addPointNodeHoverIcon = (treeId, treeNode) => {
            if (treeNode.event_type !== 'point' || treeNode.remote_flag) {
                return;
            }
            if ($(`#${treeNode.tId}_${treeNode.id}`).length) {
                return;
            }
            let sObj = $(`#${treeNode.tId}_span`);
            sObj.after(`<span id="${treeNode.tId}_${treeNode.id}" title="${LANG.UI_BACKUP_DATA_POINT_DETAIL_TITLE}"><i class='viconfont vicon-Frame11'></i></span>`);
            // 注册点击事件
            $(`#${treeNode.tId}_${treeNode.id}`).on("click", (ev) => {
                ev.stopPropagation();  // 阻止click事件向上冒泡
                $('.page-content').initPointDetailDrawer({ timepoint_uuid: treeNode.id });
            });
        };

        /**
         * 移除集群实例的时间点树的hover dom
         * @param treeId
         * @param treeNode
         */
        const removePointNodeHoverIcon = (treeId, treeNode) => {
            if (treeNode.event_type !== 'point') {
                return;
            }
            $(`#${treeNode.tId}_${treeNode.id}`).off().remove();
        };
        /**
         * 初始化修改页面副本源
         * @param treeId
         * @param treeNode
         */
        const initEditTaskSource = () => {
            let source_task_uuid = JSON.parse(SETTINGS.source_task_uuid);
            for (let item of source_task_uuid) {
                let itemNode = zTree.getNodeByParam('id', item + item);
                // 直接调用处理函数
                copyNodeSelect("copySourceTree", itemNode);
            }
        }
        /**
         * 修改副本任务-初始化本地源树
         */
        const initEditLocalSource = () => {
            let data_type = parseInt($('#dataType').val());
            $('#autoAddButton').bootstrapSwitch('state', SETTINGS.auto_add_flag);
            // 修改副本任务-初始化本地源树  以任务为源
            if ([1].includes(data_type) && settings.module_type == CONF.MODULE_TYPE.VM && !(settings.copy_back || settings.archive_flag || settings.archive_back_flag)) {
                initEditTaskSource();
                return;
            }
            let copy_list = SETTINGS.copy_list;
            let hostNodeId = [];
            let taskNodeIdArr = [];
            // 创建一个数组来保存所有的 Promise
            let promises = [];
            for (let item of copy_list) {
                let taskNodeId = item.task_uuid + item.task_uuid;
                let taskNode = zTree.getNodeByParam('id', taskNodeId);

                // 重复任务节点跳过
                if (!taskNodeIdArr.includes(taskNodeId)) {
                    taskNodeIdArr = [...taskNodeIdArr, ...[taskNodeId]];
                    taskNode.nocheck = false;
                    taskNode.click_show = false;
                    $.fn.zTree.getZTreeObj('copySourceTree').checkNode(taskNode, true, true, true);
                    let promise = getAsyncTreeTaskNode('copySourceTree', taskNode).then(() => {
                        if (settings.editFlag || !clickFlag) {
                            let copy_list = SETTINGS.copy_list;
                            for (let item of copy_list) {
                                let itemNodeId = item.item_uuid + '_' + item.task_uuid;
                                // 从备份数据跳转恢复页面，展开对象下的时间点
                                let itemNode = zTree.getNodeByParam('id', itemNodeId);
                                if (itemNode) {
                                    // 重复的对象节点跳过
                                    if (hostNodeId.includes(itemNodeId)) {
                                        continue;
                                    }
                                    hostNodeId = [...hostNodeId, ...[itemNodeId]]
                                    $.fn.zTree.getZTreeObj('copySourceTree').checkNode(itemNode, true, true, true);
                                    if (itemNode.click_show) {
                                        // 异步主机显示勾选框
                                        itemNode.nocheck = false;
                                        itemNode.click_show = false;
                                        $.fn.zTree.getZTreeObj('copySourceTree').checkNode(itemNode, true, true, true);
                                        let point_list = item.time_point_uuids;
                                        if (item.archive_timepoint_uuid != '' && point_list.length == 0) {
                                            point_list = [item.archive_timepoint_uuid];
                                        }
                                        // 异步获取时间点
                                        getSyncDataInfo('copySourceTree', itemNode, true, true, true, point_list);
                                    }
                                }
                            }
                        }
                    });
                    promises.push(promise);
                }
            }

            // 等待所有异步操作完成后再设置标志位
            Promise.all(promises).then(() => {
                settings.editFirstInitFlag = false;
                let checkedNodes = zTree.getCheckedNodes(true);
                if (checkedNodes.length == 0) {
                    let tipTitle = LANG.UI_COPY_MODIFY;
                    let tipContent = LANG.UI_COPY_EDIT_SOURCE_EMPTY_TIPS;
                    if (settings.archive_back_flag) {
                        tipTitle = tipTitle.replaceAll(LANG.UI_VISUAL_COPY, LANG.UI_VISUAL_ARCHIVE_CHART);
                        tipContent = tipContent.replaceAll(LANG.UI_VISUAL_COPY, LANG.UI_VISUAL_ARCHIVE_CHART);
                    }
                    // 不存在给出提示
                    UIToastr.showWarning(tipTitle, tipContent);
                }
            }).catch((error) => {
                settings.editFirstInitFlag = false; // 即使出错也要设置，避免无限等待
            });
        }
        // 勾选依赖时间点
        const checkedDependNodes = function (node, tree) {
            // 根据depend_point_uuid查询依赖点
            if (node.depend_point_uuid && node.pId != node.depend_point_uuid) {
                let parent = node.getParentNode();
                let dependNode = tree.getNodesByParam("id", node.depend_point_uuid, parent);
                tree.checkNode(dependNode[0], true, false);
                checkedDependNodes(dependNode[0], tree);
            } else {
                return true;
            }
        }
        //勾选之后 添加副本源列表展示
        const addCopyDataList = function (id, node) {
            let info = "";
            let liId = "";
            let icon = "./img/vm/host.png";
            liId = clearString(id + node.item_uuid + node.id); //添加每列ID
            // 已勾选添加列表
            if (node.checked) {
                if (timePointList.includes(liId)) {
                    return;
                }
                timePointList.push(liId);
                let name = node.name
                //按任务添加源----主机名
                if (node.data_type == DB_NODE_TYPE && node.event_type == TREE_NODE_TYPE.HOST) {
                    name = node.name;
                    if (node.instance_name) {
                        name += '(' + node.instance_name + ')';
                    }
                    if (![CONF.DB_TYPE.SQLSERVER, CONF.DB_TYPE.SAPHANA].includes(node.sub_type)) {
                        name = node.name;
                    }
                }
                if (node.icon) {
                    icon = node.icon;
                }
                info += `<li class="list-group-item popovers VMTips" id="${liId}" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="${node.show_name}(${node.task_name})" style="display:flex;align-items:center;">
                            <div class="col1">
                                <div class="cont">
                                    <div class="cont-col1">
                                        <div style="width:30px;height:27px;background: url(${icon}) 0 no-repeat;"></div>
                                    </div>
                                    <div class="cont-col2">
                                        <div class="desc list-one">${name}(${node.task_name})</div>
                                        </div>
                                    </div>
                                </div>
                            <div class="col2  pull-right delete-list" style="margin-left:-35px;width:35px;">
                                <a class="del${liId}" >
                                    <div class="label label-sm label-danger" style="padding:0;width: 16px;height: 16px;color: #D7D8D9;background-color: transparent;">
                                        <i class="viconfont vicon-guanbi"></i>
                                    </div>
                                </a>
                            </div>
                        </li>`;
                //根据树类型添加每一列到列表
                $('#itemList').append(info);
                $('#' + escapeJquery(liId)).popover();	   //初始化tips

                //移除显示
                $('.del' + escapeJquery(liId)).on('click', function () {
                    let treeObj = $.fn.zTree.getZTreeObj(id);
                    $('.popover.in').remove();
                    treeObj.checkNode(node, false, true);
                    removeListItem(liId);
                });
                // 取消勾选 删除列表
            } else {
                removeListItem(liId);
            }
        }
        // 移除列表显示
        function removeListItem(liId) {
            $('.del' + escapeJquery(liId)).off('click'); // 移除事件避免内存泄漏
            $('.del' + escapeJquery(liId)).closest('li.list-group-item').remove();
            timePointList = timePointList.filter((element) => element !== liId);
        }
        // 异地数据勾选事件
        const remoteSelect = function (treeId, treeNode, expendFlag) {
            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
            remoteExpand(treeId, treeNode);
        }

        // 展开节点
        const remoteExpand = function (treeId, treeNode, expendFlag) {
            if (treeNode.click_show) {
                if (!treeNode.isParent) return true;
                let params = { storage_uuid: treeNode.storage_uuid, module_type: treeNode.module_type, sub_module_type: treeNode.sub_module_type, }
                switch (treeNode.event_type) {
                    case TREE_NODE_TYPE.TASK:
                        treeNode.click_show = false;
                        params.task_uuid = treeNode.task_uuid;
                        params.task_name = treeNode.name;
                        getAsyncTreeNode(params, treeId, treeNode, expendFlag, TREE_NODE_TYPE.HOST);
                        break;
                    case TREE_NODE_TYPE.HOST:
                        treeNode.nocheck = false;
                        treeNode.click_show = false;
                        zTree.updateNode(treeNode);
                        let getParentNode = treeNode.getParentNode();
                        if (getParentNode) {
                            getParentNode.nocheck = false;
                            getParentNode.click_show = false;
                            // 更新节点显示
                            zTree.updateNode(getParentNode);
                        }
                        treeNode.nocheck = false;
                        params.item_uuid = treeNode.item_uuid;
                        params.task_uuid = treeNode.task_uuid;
                        params.item_name = treeNode.name;
                        params.checked = treeNode.checked;
                        getAsyncTreeNode(params, treeId, treeNode, expendFlag, ASYNC_API_TIME_POINT);
                        break;
                    default:
                        break;
                }
            } else {
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
                return true;
            }
        }

        let getAsyncTreeNode = (params, treeId, treeNode, expendFlag, nodeType) => {
            Metronic.blockUI({ target: '.src-wrap__content', animate: true });
            let backHandler = function (res) {
                Metronic.unblockUI('.src-wrap__content');
                if (res.success) {
                    $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                    $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, res.data, true);
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                    if (expendFlag == true) {
                        $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                    }
                }
            };
            pAjaxRequest(params, `/api/v1/copy/resources/remote/${nodeType}`, "get", backHandler, true);
        }
        /**
         * 获取加载更多节点的同级节点
         * 由于加载更多可能卡在增备一条链中间，所以向树节点增加节点时需要以完备点为父节点
         * @param {*} node 
         * @param {*} treeId 
         * @returns 
         */
        const getPreviousSibling = (node, treeId) => {
            const parentNode = node.getParentNode();
            let children = parentNode.children;
            children = children.filter(child => child.type == 3);
            // 找到上一个同级节点
            if (settings.module_type == CONF.MODULE_TYPE.M365) {
                return parentNode;
            } else {
                if (children && children.length > 0) {
                    return children[children.length - 1];
                } else {
                    return parentNode;
                }
            }
            return null;
        };
        /**
         * 暂存任务的所有对象
         * @param {string} task_uuid 
         */
        const addTempItemList = function (task_uuid, p_incr_flag) {
            // 添加任务，如果未存则新建
            if (!temp_item_array[task_uuid]) {
                temp_item_array[task_uuid] = {};
            }
            // 获取请求任务的对象列表参数
            let storage_uuid = $('#storage').val();//选择源存储
            let data_type = parseInt($('#dataType').val());	//选择副本数据类型
            let param = {
                task_uuid: task_uuid,
                data_type: data_type,
                module_type: settings.module_type,
                storage_uuid: storage_uuid == '' ? '' : storage_uuid,
                sub_module_type: settings.sub_module_type,
                p_incr_flag: p_incr_flag,
            }
            let addItemList = function (res) {
                // 失败则退出
                if (!res.success || res.data.length == 0) {
                    return;
                }
                let storageType = parseInt($('#storage option:selected').data('type'));
                let storage_pool_flag = storageType == '10053';
                let storage_uuid = $('#storage').val();
                let tmpData = res.data;
                tmpData.forEach(element => {
                    // 添加任务下的每个对象
                    temp_item_array[element.task_uuid][element.item_uuid] = {
                        item_uuid: element.item_uuid,
                        parent_uuid: element.parent_uuid,
                        source_storage_uuid: '', // #29526 副本源为非异地存储，不需要传存储uuid
                        source_storage_pool_uuid: '',
                        sub_type: parseInt(element.sub_type),
                        source_task_uuid: element.task_uuid,
                        detail: '',
                        dir_path: element.path ? element.path.toString() : '',
                        item_name: element.item_name,
                        storage_type: element.storage_type,
                        name: element.name,
                        node_uuid: element.node_uuid,
                        p_incr_flag: element.p_incr_flag,
                    };
                });
            };
            pAjaxRequest(param, "/api/v1/copy/resources/item", "POST", addItemList, true);
        }
        /**
         * 获取任务的对象列表
         * @param {*} task_uuid 
         * @param {*} dom 
         * @param {*} get_item_flag
         * @returns 
         */
        const getItemListInTaskAsync = function (task_uuid, storage_uuid, dom, get_item_flag, p_incr_flag) {
            // 获取任务是否勾选状态
            let checked = $(dom).find('.checkAllBtn').is(':checked');
            // 避免单个任务的对象重复加载
            if (get_item_flag == 'true') {
                return true;
            }
            // 获取请求任务的对象列表参数
            let data_type = parseInt($('#dataType').val());	//选择副本数据类型
            let param = {
                task_uuid: task_uuid,
                data_type: data_type,
                module_type: settings.module_type,
                storage_uuid: storage_uuid,
                sub_module_type: settings.sub_module_type,
                p_incr_flag: p_incr_flag,
            }
            let addItemList = function (res) {
                // 失败则退出
                if (!res.success || res.data.length == 0) {
                    return;
                }
                $(dom).attr('data-get_item_flag', 'true');
                let data = res.data;
                // 渲染对象列表
                data.forEach(element => {
                    let itemListHtml = '';
                    // 根据任务勾选状态初始化对象勾选状态
                    let checkedFlag = checked ? 'checked' : '';
                    let auto_add_flag = $(`#autoAddButton`).get(0).checked;
                    // 修改任务时，需要取消勾选排除的对象
                    if (settings.editFlag) {
                        let taskList = JSON.parse(SETTINGS.source_task_uuid);
                        if (taskList.includes(element.task_uuid)) {
                            let uuidList = JSON.parse(SETTINGS.source_item_uuid);
                            if (uuidList.length > 0) {
                                if (uuidList.includes(element.item_uuid)) {
                                    checkedFlag = 'checked';
                                } else {
                                    checkedFlag = '';
                                }
                            }
                        }
                    }
                    // 获取更多按钮显示
                    itemListHtml += `<div class="task-item-wrapper" data-item_uuid="${element.item_uuid}" data-task_uuid="${task_uuid}" data-item_name="${element.show_name}" id="${task_uuid}_${element.item_uuid}">
                                        <span class="button viconfont ${element.iconSkin}_ico_docu"></span>
                                        <span class="list-item_name" title="${element.show_name}" data-item_uuid="${element.item_uuid}">
                                            ${element.item_name}
                                            <span class="item-exclude-label red-font ${auto_add_flag && !checkedFlag ? '' : 'display-none'}">(${LANG.UI_BACKUP_EXCLUDED})</span>
                                        </span>
                                        <input type="checkbox" ${checkedFlag} data-checkbox="icheckbox_square-blue"
                                                data-item_uuid="${element.item_uuid}" data-task_uuid="${task_uuid}" data-item_name="${element.show_name}" class="iCheck input-exclude_item">
                                    </div>`;
                    // 渲染对象列表html
                    $(dom).find(`#taskList_${task_uuid} .item_list-wrapper`).append(itemListHtml);
                    // 任务勾选事件
                    $(`#${task_uuid}_${element.item_uuid}`).on('click', function (e) {
                        if ($(this).find('.iCheck').is(':checked')) {
                            $(this).find('.iCheck').iCheck('uncheck');
                        } else {
                            $(this).find('.iCheck').iCheck('check');
                        }
                        let item_uuid = $(this).attr('data-item_uuid');
                        let task_uuid = $(this).attr('data-task_uuid');
                        let item_name = $(this).attr('data-item_name');
                        if (!$(this).find('.iCheck').is(':checked')) {
                            // 新建任务记录
                            if (!temp_exclude_array.hasOwnProperty(task_uuid)) {
                                temp_exclude_array[task_uuid] = [];
                            }
                            // 增加任务下对象排除
                            temp_exclude_array[task_uuid][item_uuid] = {
                                'item_uuid': item_uuid,
                                'task_uuid': task_uuid,
                                'item_name': item_name,
                            };
                            // 如果是自动添加，如果排除列表长度为零，需要将任务的勾选状态置为勾选，否则为未勾选
                            setTimeout(() => {
                                // 增加已排除提示
                                $(this).find('.item-exclude-label').removeClass('display-none');
                                if (Object.keys(temp_exclude_array[task_uuid]).length > 0) {
                                    $(`#task_${task_uuid}`).find('.checkAllBtn').iCheck('uncheck');
                                } else {
                                    $(`#task_${task_uuid}`).find('.checkAllBtn').iCheck('check');
                                }
                            }, 0);
                        } else {
                            // 清除任务下对象排除
                            delete temp_exclude_array[task_uuid][item_uuid];
                            // 如果是自动添加，如果排除列表长度为零，需要将任务的勾选状态置为勾选，否则为未勾选
                            setTimeout(() => {
                                if (Object.keys(temp_exclude_array[task_uuid]).length > 0) {
                                    $(`#task_${task_uuid}`).find('.checkAllBtn').iCheck('uncheck');
                                } else {
                                    $(`#task_${task_uuid}`).find('.checkAllBtn').iCheck('check');
                                }
                                // 删除已排除提示
                                $(this).find('.item-exclude-label').addClass('display-none');
                            }, 0);
                        }
                    });
                });
                // 初始化勾选框
                $('.task-item-wrapper .iCheck').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                });
                // 排除对象操作
                $('.input-exclude_item').on('ifClicked', function () {
                    let item_uuid = $(this).attr('data-item_uuid');
                    let task_uuid = $(this).attr('data-task_uuid');
                    let item_name = $(this).attr('data-item_name');
                    if (this.checked) {
                        // 新建任务记录
                        if (!temp_exclude_array.hasOwnProperty(task_uuid)) {
                            temp_exclude_array[task_uuid] = [];
                        }
                        // 增加任务下对象排除
                        temp_exclude_array[task_uuid][item_uuid] = {
                            'item_uuid': item_uuid,
                            'task_uuid': task_uuid,
                            'item_name': item_name,
                        };
                        // 如果是自动添加，如果排除列表长度为零，需要将任务的勾选状态置为勾选，否则为未勾选
                        setTimeout(() => {
                            // 增加已排除提示
                            $(this).parent().siblings('.list-item_name').find('.item-exclude-label').removeClass('display-none');
                            if (Object.keys(temp_exclude_array[task_uuid]).length > 0) {
                                $(`#task_${task_uuid}`).find('.checkAllBtn').iCheck('uncheck');
                            } else {
                                $(`#task_${task_uuid}`).find('.checkAllBtn').iCheck('check');
                            }
                        }, 0);
                    } else {
                        // 清除任务下对象排除
                        delete temp_exclude_array[task_uuid][item_uuid];
                        // 如果是自动添加，如果排除列表长度为零，需要将任务的勾选状态置为勾选，否则为未勾选
                        setTimeout(() => {
                            if (Object.keys(temp_exclude_array[task_uuid]).length > 0) {
                                $(`#task_${task_uuid}`).find('.checkAllBtn').iCheck('uncheck');
                            } else {
                                $(`#task_${task_uuid}`).find('.checkAllBtn').iCheck('check');
                            }
                            // 删除已排除提示
                            $(this).parent().siblings('.list-item_name').find('.item-exclude-label').addClass('display-none');
                        }, 0);
                    }
                });
                // 勾选任务，勾选任务下所有对象
                $('.checkAllBtn').on('ifClicked', function (e) {
                    let task_uuid = $(this).attr('data-task_uuid');
                    // 操作排除列表
                    if (!this.checked) {
                        $(`#taskList_${task_uuid}`).find('.iCheck').iCheck('check');
                        // 显示已排除提示
                        $(`#taskList_${task_uuid}`).find('.list-item_name').find('.item-exclude-label').addClass('display-none');
                        delete temp_exclude_array[task_uuid];
                    } else {
                        $(`#taskList_${task_uuid}`).find('.iCheck').iCheck('uncheck');
                        temp_exclude_array[task_uuid] = { ...temp_item_array[task_uuid] };
                        // 已排除提示
                        $(`#taskList_${task_uuid}`).find('.list-item_name').find('.item-exclude-label').removeClass('display-none');
                    }
                });
            };
            pAjaxRequest(param, "/api/v1/copy/resources/item", "POST", addItemList, true);
        }
        //点击树节点回调
        const copyNodeSelect = function (treeId, treeNode) {
            let data_type = parseInt($('#dataType').val());	//选择副本数据类型
            if (treeNode.point_more) {
                // 获取加载更多的同级节点
                const previousNode = getPreviousSibling(treeNode, treeId);
                let storage_uuid = $('#storage').val();
                let parentNode = treeNode.getParentNode();
                let params = {
                    task_uuid: parentNode.task_uuid,
                    db_uuid: parentNode.db_uuid,
                    sub_type: treeNode.sub_type ? parentNode.sub_type : '',
                    agent_uuid: parentNode.agent_uuid,
                    copy_flag: parentNode.copy_flag ? parentNode.copy_flag : '',
                    data_type: data_type,
                    id: parentNode.id,
                    module_type: settings.module_type,
                    item_uuid: parentNode.item_uuid,
                    sub_module_type: settings.sub_module_type,
                    storage_uuid: storage_uuid ?? '',
                    parent_uuid: parentNode.parent_uuid ?? '',
                    db_name: parentNode.db_name ?? '',
                    instance_name: parentNode.instance_name ?? '',
                    db_cluster_uuid: parentNode.db_cluster_uuid ?? '',
                    db_agent_uuid: parentNode.db_agent_uuid ?? '',
                    point_offset: treeNode.point_offset,
                    point_limit: treeNode.point_limit,
                    point_more: treeNode.point_more,
                    pId: previousNode.id, // 如果加载更多获取的是增量点第一个  那么需要传入父节点id
                };
                let setTimePoint = (res) => {
                    if (res.success) {
                        let data = res.data.msg;
                        let parentNode = treeNode.getParentNode();
                        // 如果加载更多的父节点是时间点，那么需要获取对象节点
                        if (parentNode.event_type == 'point') {
                            if (parentNode.getParentNode()) {
                                parentNode = parentNode.getParentNode();
                            }
                        }
                        $.fn.zTree.getZTreeObj(treeId).removeNode(treeNode);
                        // 如果是链中加载更多，需要向完备点添加节点
                        if (res.data.unfull_head_flag) {
                            // 查找返回数组的完备点所在位置
                            let index = getFirstFullIndex(data);
                            // 从index将data分成两部分
                            const splitData = (data, index) => {
                                // 前面非完备点的数据
                                const before = [...data.slice(0, index)];
                                // 后面有完备点的数据
                                const after = [...data.slice(index)];
                                return [before, after];
                            };
                            // 如果没有完备点，那么直接往完备点添加
                            if (index == null) {
                                $.fn.zTree.getZTreeObj(treeId).addNodes(previousNode, data, true);
                                $.fn.zTree.getZTreeObj(treeId).expandNode(previousNode, true);
                            } else {
                                // 如果有完备点，那么前面部分往完备点添加，后面部分往对象节点添加
                                const [part1, part2] = splitData(data, index);
                                $.fn.zTree.getZTreeObj(treeId).addNodes(previousNode, part1, true);
                                $.fn.zTree.getZTreeObj(treeId).addNodes(parentNode, part2, true);
                                $.fn.zTree.getZTreeObj(treeId).expandNode(parentNode, true);
                            }
                        } else {
                            $.fn.zTree.getZTreeObj(treeId).addNodes(parentNode, data, true);
                            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                        }
                    }
                }
                pAjaxRequest(deepCloneObject(params), "/api/v1/copy/resources/data", "POST", setTimePoint, true);
            } else if (treeNode.get_item_flag && !(settings.copy_back || settings.archive_back_flag || settings.archive_flag)) {
                // 点击展开  勾选
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
                return true;
            } else {
                // 点击展开  勾选
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                copyNodeExpand(treeId, treeNode);
            }
        }
        // 获取数组中完备点的位置
        const getFirstFullIndex = function (data) {
            let index = null;
            for (let i = 0; i < data.length; i++) {
                if (data[i].type == 3) {
                    index = i;
                    return index;
                }
            }
            return index;
        }
        //得到当前节点的所有子节点
        const getAllChildren = function (node, allNode) {
            if (node.isParent) {
                let children = node.children;
                if (children) {
                    for (let i = 0; i < children.length; i++) {
                        allNode.push(children[i]);
                        if (children[i].isParent) {
                            getAllChildren(children[i], allNode);
                        }
                    }
                }
            } else {
                allNode.push(node);
            }
            return allNode;
        }

        //勾选树节点回调
        const copyOnCheck = function (e, id, treeNode) {
            if (treeNode.get_item_flag && !(settings.copy_back || settings.archive_back_flag || settings.archive_flag)) {
                // 勾选  在列表增加任务显示
                if (treeNode.checked) {
                    // 选中任务线暂存任务的所有对象
                    addTempItemList(treeNode.task_uuid, treeNode.p_incr_flag);
                    let html = `<div class="accordion strategyOne getItemInTask" id="task_${treeNode.task_uuid}" data-get_item_flag="false">
                                    <div class="panel panel-default strategy-panel">
                                        <div class="panel-heading" style="height: unset;">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers task-accordion-tab" data-container="body"
                                                    data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".parallelTransDiv"
                                                    href="#taskList_${treeNode.task_uuid}" aria-expanded="true" data-original-title="${treeNode.task_name}" title="${treeNode.task_name}" data-task_uuid="${treeNode.task_uuid}">
                                                    <i class="viconfont vicon-renwu"></i>
                                                    <span class="font-grey-999">${treeNode.task_name}</span>
                                                    <input class="iCheck checkAllBtn" type="checkbox" checked data-task_uuid="${treeNode.task_uuid}">
                                                    <i class="viconfont vicon-guanbi button-remove_task" data-task_uuid="${treeNode.task_uuid}"></i>
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="taskList_${treeNode.task_uuid}" class="panel-collapse collapse">
                                            <div class="item_list-wrapper"></div>
                                        </div>
                                    </div>
                                </div>`;
                    $('#itemList').append(html);
                    
                    // 只有副本任务才显示已副本提示信息
                    if (treeNode.inCopyTask && !(settings.archive_flag || settings.archive_back_flag || settings.copy_back)) {
                        // 修改任务第一次初始化显示提示信息
                        if (!settings.editFirstInitFlag) {
                            UIToastr.showWarning(LANG.UI_COPY_CREATE, LANG.UI_COPY_TASK_IN_COPY_TASK_TIP);
                        }
                    }
                    
                    settings.editFirstInitFlag = false;
                } else {
                    // 取消勾选，清除任务显示
                    $('#itemList').find(`#task_${treeNode.task_uuid}`).remove();
                    // 清除排除列表和对象列表暂存的数据
                    delete temp_item_array[treeNode.task_uuid];
                    delete temp_exclude_array[treeNode.task_uuid];
                }
                // 初始化勾选框
                $('.getItemInTask .iCheck').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                });
                // 获取任务的对象列表
                $('.getItemInTask').on('click', function () {
                    let get_item_flag = $(this).attr('data-get_item_flag');
                    getItemListInTaskAsync(treeNode.task_uuid, treeNode.storage_uuid, this, get_item_flag, treeNode.p_incr_flag)
                });
                // 清除勾选的任务 并且取消树节点勾选状态
                $('.button-remove_task').on('click', function (event) {
                    event.stopPropagation();
                    event.preventDefault();
                    let task_uuid = $(this).attr('data-task_uuid');
                    // 取消树节点勾选
                    let treeObj = $.fn.zTree.getZTreeObj('copySourceTree');
                    let node = treeObj.getNodeByParam('id', task_uuid + task_uuid);
                    treeObj.checkNode(node, false, true);
                    // 移除任务列表
                    $(`#task_${task_uuid}`).remove();
                    // 取消任务勾选清空记录的任务信息，以及任务相关的排除对象信息
                    delete temp_exclude_array[task_uuid];
                    delete temp_item_array[task_uuid];
                });
                // 勾选任务，勾选任务下所有对象
                $('.checkAllBtn').on('ifClicked', function (e) {
                    let task_uuid = $(this).attr('data-task_uuid');
                    let get_item_flag = $(this).attr('data-get_item_flag');
                    if (!this.checked && !get_item_flag) {
                        delete temp_exclude_array[task_uuid];
                    } else {
                        temp_exclude_array[task_uuid] = { ...temp_item_array[task_uuid] };
                    }
                });
                return true;
            }
            const tree = $.fn.zTree.getZTreeObj(id);
            let handler = '';
            let showWarningFlag = false; // 显示已副本提示信息标志
            if (!tree) {
                return false; // 获取zTree对象失败，直接返回
            }
            // 只有副本任务才显示已副本提示信息
            if (treeNode.inCopyTask && treeNode.checked && !(settings.archive_flag || settings.archive_back_flag || settings.copy_back)) {
                // 修改任务第一次初始化显示提示信息
                if (!settings.editFirstInitFlag) {
                    showWarningFlag = true;
                }
            }
            // 处理任务节点 点击添加列表显示
            const handleTaskNode = function (tree, node, id) {
                if (node.children) {
                    node.children.forEach(child => {
                        // 如果子节点为实例 则继续查询子节点
                        if (child.event_type === TREE_NODE_TYPE.INSTANCE || child.event_type === 'show_node') {
                            if (child.children) {
                                child.children.forEach(grandChild => {
                                    addCopyDataList(id, grandChild);
                                });
                            }
                        } else {
                            addCopyDataList(id, child);
                            // 只有副本任务才显示已副本提示信息
                            if (child.inCopyTask && child.checked && !(settings.archive_flag || settings.archive_back_flag || settings.copy_back)) {
                                // 修改任务第一次初始化显示提示信息
                                if (!settings.editFirstInitFlag) {
                                    showWarningFlag = true;
                                }
                            }
                        }
                    });
                }
            };
            // 分离不同节点类型的处理逻辑
            const nodeType = {
                'task': handleTaskNode,
                'host': handleHostNode,
                'instance': handleInstanceNode,
                'point': handlePointNode,
                'exchange': handleExchangeNode,
                'database': handleDatabaseNode,
                'show_node': handleTaskNode,
            };
            if (treeNode.module_type == CONF.MODULE_TYPE.M365) {
                handler = nodeType['exchange'];
            } else if (treeNode.module_type == CONF.MODULE_TYPE.DB) {
                handler = nodeType[treeNode.event_type];
                handler(tree, treeNode, id);
                handler = nodeType['database'];
            } else {
                // 根据节点类型 进行不同的处理
                handler = nodeType[treeNode.event_type];
            }
            handler(tree, treeNode, id);
            // 提示对象已经在副本任务中
            if (showWarningFlag) {
                UIToastr.showWarning(LANG.UI_COPY_CREATE, LANG.UI_COPY_ITEM_IN_COPY_TASK_TIP);
            }
        };
        // 初始化时渲染树节点样式
        const getFontCss = function (treeId, treeNode) {
            let css = { color: "#333", "font-weight": "normal" };
            if (!!treeNode.inCopyTask && !(settings.archive_flag || settings.archive_back_flag || settings.copy_back)) {
                css = { color: "green", "font-weight": "bold" };
            }
            if (!!treeNode.highlight) {
                //搜索使用的样式
                css = { color: "#A60000", "font-weight": "bold" };
            }
            if (treeNode.point_status == 1) {
                css = { color: "#F19F00 " };
            }
            if (treeNode.point_status == 3) {
                css = { color: "#F1416C " };
            }
            return css;
        }
        // 数据库只能勾选整条链，非完备点不能勾选
        const handleDatabaseNode = function (tree, node, id, showInTask) {
            let allNodes = zTree.getCheckedNodes(true);
            // 勾选并置灰非完备点
            if (node.type <= TREE_NODE_TYPE.FULL && node.checked) {
                for (let j = 0; j < allNodes.length; j++) {
                    if (allNodes[j].type == 3 && allNodes[j].isParent && allNodes[j].checked) {
                        let children = allNodes[j].children;
                        for (let i = 0; i < children.length; i++) {
                            zTree.setChkDisabled(children[i], false);  // 这里设置为false是为了勾选
                            zTree.checkNode(children[i], true, true);
                            zTree.setChkDisabled(children[i], true);
                        }
                    }
                }
                // 取消勾选并置灰非完备点
            } else if (node.type <= TREE_NODE_TYPE.FULL) {
                let allChildren = getAllChildren(node, []);
                for (let j = 0; j < allChildren.length; j++) {
                    if (allChildren[j].type == 4) {
                        zTree.setChkDisabled(allChildren[j], false);
                        zTree.checkNode(allChildren[j], false, true);
                        zTree.setChkDisabled(allChildren[j], true)
                    }
                }
            }
        }
        // exchange的完备点和非完备点在同一层需要特殊处理
        const handleExchangeNode = function (tree, node, id, showInTask) {
            if (node.event_type == TREE_NODE_TYPE.POINT) {
                let nodeArr = node.getParentNode().children;
                let checkFlag = false;//是否选中增备
                // 按顺序处理整条链
                for (let i = 0; i < nodeArr.length; i++) {
                    // 完备点选中则记录
                    if (nodeArr[i].type == TREE_NODE_TYPE.FULL && nodeArr[i].checked) {//完备是选中的
                        checkFlag = true
                        // 如果完备点标记了，则勾选非完备点且更新节点
                    } else if (nodeArr[i].type == TREE_NODE_TYPE.UN_FULL && checkFlag) {//选中完备下的增备
                        nodeArr[i].checked = true;
                        zTree.updateNode(nodeArr[i]);
                        // 如果完备点未勾选
                    } else if (nodeArr[i].type == TREE_NODE_TYPE.FULL && !nodeArr[i].checked) {//未选中
                        checkFlag = false;
                        // 如果未勾选完备点，取消勾选非完备点且更新树
                    } else if (nodeArr[i].type == TREE_NODE_TYPE.UN_FULL && !checkFlag) {//取消选中增备
                        nodeArr[i].checked = false;
                        zTree.updateNode(nodeArr[i]);
                    }
                }
                // 勾选主机节点
                checkHostNode(id, node);
                // 勾选组织层则勾选/取消所有时间点
            } else if (node.event_type == TREE_NODE_TYPE.HOST) {//选中组织
                if (node.children) {
                    let nodeArr = node.children;
                    for (let i = 0; i < nodeArr.length; i++) {
                        if (node.checked) {//选中
                            nodeArr[i].checked = true;
                            zTree.updateNode(nodeArr[i]);
                        } else if (!node.checked) {//取消选中
                            nodeArr[i].checked = false;
                            zTree.updateNode(nodeArr[i]);
                        }
                    }
                }
                addCopyDataList(id, node);
                // 勾选任务层，勾选/取消所有子节点
            } else if (node.event_type == TREE_NODE_TYPE.TASK && node.children) {//选中任务
                let nodeArr = node.children;
                // 遍历组织并勾选或取消
                for (let i = 0; i < nodeArr.length; i++) {
                    nodeArr[i].checked = node.checked;
                    zTree.updateNode(nodeArr[i]);
                    let nodeChild = nodeArr[i].children;
                    if (nodeChild) {
                        // 遍历时间点，勾选或取消
                        for (let j = 0; j < nodeChild.length; j++) {
                            nodeChild[j].checked = node.checked;
                            zTree.updateNode(nodeChild[j]);
                        }
                    }
                    addCopyDataList(id, nodeArr[i]);
                }
            }
        };
        // 处理主机节点 点击添加列表显示
        const handleHostNode = function (tree, node, id, showInTask) {
            addCopyDataList(id, node);
        };
        // 处理实例节点 点击添加列表显示
        const handleInstanceNode = function (tree, node, id, showInTask) {
            if (node.children) {
                node.children.forEach(child => {
                    addCopyDataList(id, child);
                });
            }
        };
        // 处理时间点节点 点击添加列表显示
        const handlePointNode = function (tree, node, id, showInTask) {
            // 根据勾选情况 联动勾选关联节点
            if (node.checked) {
                // 联动勾选
                checkedDependNodes(node, tree);
            } else {
                if (node.depend_point_uuid === node.pId) {
                    const parent = node.getParentNode();
                    const gParent = parent.getParentNode();
                    if (parent && gParent) {
                        tree.checkNode(parent, true, false);
                        tree.checkNode(gParent, true, false);
                    }
                }
                // 取消勾选
                uncheckedDependNodes(node, tree);
            }
            // 勾选主机节点
            checkHostNode(id, node);
        };
        // 取消勾选依赖于该时间点的
        const uncheckedDependNodes = function (node, tree) {
            if (node.depend_point_uuid) {
                let parent = node.getParentNode();
                let childNode = tree.getNodesByParam("depend_point_uuid", node.id, parent);
                if (childNode.length != 0 && childNode[0].checked) {
                    tree.checkNode(childNode[0], false, false);
                    uncheckedDependNodes(childNode[0], tree);
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
        // 勾选主机节点
        const checkHostNode = (id, node) => {
            let parent = node.getParentNode();
            if (parent.event_type == TREE_NODE_TYPE.HOST) {
                addCopyDataList(id, parent);
            } else {
                checkHostNode(id, node.getParentNode());
            }
        };

        //展开树节点回调
        const copyNodeExpand = function (treeId, treeNode) {
            let data_type = parseInt($('#dataType').val());	//选择副本数据类型
            if (treeNode.click_show) {
                if (treeNode.event_type == 'host') {
                    if (treeNode.children) return true;
                    // 异步加载时间点，才能勾选任务层
                    let parentNode = treeNode.getParentNode();
                    parentNode.nocheck = false;
                    // 存储离线不能勾选
                    if(offLineStorage.includes(treeNode.storage_uuid)){
                        // 异步主机显示勾选框
                        treeNode.nocheck = true;
                        parentNode.nocheck = true;
                    } else {
                        treeNode.nocheck = false;
                        parentNode.nocheck = false;
                    }
                    // 更新节点显示
                    zTree.updateNode(parentNode);
                    // 更新节点显示
                    zTree.updateNode(treeNode);
                    if (treeNode.module_type == CONF.MODULE_TYPE.DB) {
                        let grandParentNode = parentNode.getParentNode();
                        if (grandParentNode) {
                            grandParentNode.nocheck = false;
                            // 更新节点显示
                            zTree.updateNode(grandParentNode);
                        }
                    }
                    //异步获取时间点信息
                    getSyncDataInfo(treeId, treeNode, true, false);
                }
                if (treeNode.event_type == 'task') {
                    // 副本源为任务，异步加载则可以勾选任务
                    if ([1, 3].includes(data_type)) {
                        treeNode.nocheck = false;
                    }
                    // 副本源为数据，异步加载对象不可勾选任务
                    treeNode.click_show = false;
                    getAsyncTreeTaskNode(treeId, treeNode);
                }
            } else {
                return true;
            }
        }
        /**
         * 异步获取对象列表
         * @param {*} treeId 
         * @param {*} treeNode 
         */
        const getAsyncTreeTaskNode = function (treeId, treeNode) {
            return new Promise((resolve, reject) => {
                let initTaskNode = function (res) {
                    if (res.success) {
                        $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, res.data, true);
                        $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                        resolve(res); // 成功时resolve
                    }
                }
                let storage_uuid = $('#storage').val();//选择源存储
                let data_type = parseInt($('#dataType').val());	//选择副本数据类型
                let param = {
                    data_type: data_type,
                    module_type: settings.module_type,
                    storage_uuid: storage_uuid,
                    storage_pool_uuid: '',
                    sub_module_type: settings.sub_module_type,
                    archive_back_flag: settings.archive_back_flag,
                    archive_flag: settings.archive_flag,
                    copy_back: settings.copy_back,
                    task_uuid: treeNode.task_uuid,
                    p_incr_flag: treeNode.p_incr_flag
                }
                pAjaxRequest(deepCloneObject(param), "/api/v1/copy/resources/item", "POST", initTaskNode, true);
            });
        }

        //异步获取备份点信息
        const getSyncDataInfo = function (treeId, treeNode, refreshFlag, expendFlag, chooseFlag = false, time_point_uuids = []) {
            let storage_uuid = $('#storage').val();
            let div = "#copySourceTree";
            let data_type = parseInt($('#dataType').val());	//选择副本数据类型
            let params = {
                task_uuid: treeNode.task_uuid,
                db_uuid: treeNode.db_uuid,
                sub_type: treeNode.sub_type ? treeNode.sub_type : '',
                agent_uuid: treeNode.agent_uuid,
                copy_flag: treeNode.copy_flag ? treeNode.copy_flag : '',
                data_type: data_type,
                id: treeNode.id,
                module_type: treeNode.module_type,
                item_uuid: treeNode.item_uuid,
                sub_module_type: settings.sub_module_type,
                storage_uuid: storage_uuid ?? '',
                parent_uuid: treeNode.parent_uuid ?? '',
                db_name: treeNode.db_name ?? '',
                instance_name: treeNode.instance_name ?? '',
                db_cluster_uuid: treeNode.db_cluster_uuid ?? '',
                db_agent_uuid: treeNode.db_agent_uuid ?? '',
                point_offset: 0,
                point_limit: _MORE_NODE_LIMIT,
            };
            Metronic.blockUI({ target: div, animate: true });
            let setTimePoint = function (res) {
                Metronic.unblockUI(div);
                let result = res.data;
                // 增加并更新树节点
                if (res.success) {
                    //success
                    let treeObj = $.fn.zTree.getZTreeObj(treeId);
                    treeObj.addNodes(treeNode, result.msg, true);
                    treeObj.expandNode(treeNode, true);
                    if (expendFlag === true) {
                        treeObj.expandNode(treeNode, true, true, true);
                    }
                    // 勾选副本源时间点
                    if (chooseFlag && time_point_uuids != []) {
                        for (let i = 0; i < time_point_uuids.length; i++) {
                            let pointNode = treeObj.getNodeByParam('id', time_point_uuids[i]);
                            if (pointNode) {
                                treeObj.checkNode(pointNode, true, true, true);
                            }
                        }
                    }
                    $('.timepoint_node').on('mouseenter', function () {
                        $(this).find('.vicon-Frame11').show();
                    })
                    $('.timepoint_node').on('mouseleave', function () {
                        $(this).find('.vicon-Frame11').hide();
                    })
                    $('.node_getDetail').on('click', function () {
                        let timepoint_uuid = $(this).attr('data-timepoint_uuid');
                        $('.page-content').initPointDetailDrawer({ timepoint_uuid: timepoint_uuid });
                    })
                } else {
                    operateResponseList(res);
                }
            }
            pAjaxRequest(deepCloneObject(params), "/api/v1/copy/resources/data", "POST", setTimePoint, true);
        }
        //替换特殊字符
        const clearString = function (str) {
            let result = "";
            for (let i = 0; i < str.length; i++) {
                result = result + str.substr(i, 1).replace(_VMNAMEREG, '_');
            }
            return result;
        }
        // 字符串特殊处理
        const escapeJquery = function (srcString) {
            // 转义之后的结果  
            let escapseResult = srcString.toString();
            // javascript正则表达式中的特殊字符  
            let jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
                "]", "|", "{", "}"];
            // jquery中的特殊字符,不是正则表达式中的特殊字符  
            let jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
                ":", ";", "<", ">", ",", "/"];
            for (let i = 0; i < jsSpecialChars.length; i++) {
                escapseResult = escapseResult.replace(new RegExp("\\"
                    + jsSpecialChars[i], "g"), "\\"
                + jsSpecialChars[i]);
            }
            for (let i = 0; i < jquerySpecialChars.length; i++) {
                escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                    "g"), "\\" + jquerySpecialChars[i]);
            }
            return escapseResult;
        }
        /**
         * 搜索结果添加到树中
         * @param {*} searchStr 
         * @param {*} checkedNodeIds 
         * @param {*} checkedTaskUUids 
         */
        const addSearchNodeToTree = function (searchStr, checkedNodeIds, checkedTaskUUids) {
            let storage_uuid = $('#storage').val();//选择源存储
            let data_type = parseInt($('#dataType').val());	//选择副本数据类型
            let task_only_flag = false;
            if(settings.module_type == CONF.MODULE_TYPE.VM && data_type == 1 && !(settings.archive_flag || settings.archive_back_flag || settings.copy_back)){
                task_only_flag = true;
            }
            let params = {
                data_type: data_type,
                module_type: settings.module_type,
                storage_uuid: storage_uuid,
                sub_module_type: settings.sub_module_type,
                archive_back_flag: settings.archive_back_flag,
                archive_flag: settings.archive_flag,
                search: searchStr,
                task_only_flag: task_only_flag,
            }
            let searchBack = function (res) {
                Metronic.unblockUI('.src-wrap__content');
                if (res.success) {
                    let data = res.data;
                    // 倒序遍历，删除符合条件的元素
                    if (Array.isArray(data) && checkedNodeIds.length > 0) {
                        for (let i = data.length - 1; i >= 0; i--) {
                            if (checkedNodeIds.includes(data[i].id) || checkedTaskUUids.includes(data[i].task_uuid)) {
                                data.splice(i, 1);  // 删除该元素
                            }
                        }
                    }
                    $("#noDataTips").hide();
                    $('.copyTreeDiv').show();
                    $('#itemList').show();
                    zTree.addNodes(null, data, true);
                } else {
                    // 处理搜索失败，但是有勾选的情况
                    if (checkedNodeIds.length > 0) {
                    } else {
                        // 处理搜索失败，但是没有勾选的情况，显示无数据提示信息
                        if (CONF.MODULE_TYPE.VM == settings.module_type) {
                            $('#toBackup').text(MODULE_VM_NO_DATA_TIP[settings.sub_module_type]);
                        } else if (CONF.MODULE_TYPE.FS == settings.module_type) {
                            $('#toBackup').text(MODULE_FS_NO_DATA_TIP[settings.sub_module_type]);
                        } else {
                            $('#toBackup').text(MODULE_NO_DATA_TIP[settings.module_type]);
                        }
                        if (settings.archive_back_flag) {
                            if (CONF.MODULE_TYPE.VM == settings.module_type) {
                                $('#toBackup').text(MODULE_VM_NO_DATA_TIP_ARCHIVE[settings.sub_module_type]);
                            } else if (CONF.MODULE_TYPE.OS == settings.module_type) {
                                $('#toBackup').text(LANG.UI_ARCHIVE_NO_OS_DATA_REDIRECT);
                            }
                        }
                        // 无数据展示提示
                        $("#noDataTips").show();
                        $('.copyTreeDiv').hide();
                        $('#itemList').hide();
                    }
                }
            }
            pAjaxRequest(params, "/api/v1/copy/resources/search", "POST", searchBack, true);
        }
        //搜索功能
        const searchSrcInfo = function (event) {
            let searchStr = $('.searchCopySource').val();
            if (searchStr == '' && search == '') {
                return true;
            }
            if (event.keyCode == 13) {
                event.preventDefault(); // 阻止回车键的默认提交行为
                Metronic.blockUI({ target: '.src-wrap__content', animate: true });
                search = searchStr;
                // 获取所有的勾选节点
                let uncheckTaskNodes = getUncheckNodes();
                let checkedNodeIds = getCheckedNodeIds();
                let checkedTaskUUids = getCheckedNodeTaskIds();
                if (uncheckTaskNodes.length > 0) {
                    // 删除找到的未勾选任务节点
                    uncheckTaskNodes.forEach(node => {
                        removeUncheckTaskNodesFromTree(node);
                    });
                }
                addSearchNodeToTree(searchStr, checkedNodeIds, checkedTaskUUids);
            };
        }

        // 获取所有已勾选节点的 ID 组成的数组
        const getCheckedNodeTaskIds = () => {
            let checkedNodes = zTree.getCheckedNodes(true);  // 获取所有勾选的节点
            let checkedNodeIds = [];  // 存储 ID 的数组

            for (let i = 0; i < checkedNodes.length; i++) {
                if (!checkedNodeIds.includes(checkedNodes[i].task_uuid)) {
                    checkedNodeIds.push(checkedNodes[i].task_uuid);
                }
            }

            return checkedNodeIds;
        }
        // 获取所有已勾选节点的 ID 组成的数组
        const getCheckedNodeIds = () => {
            let checkedNodes = zTree.getCheckedNodes(true);  // 获取所有勾选的节点
            let checkedNodeIds = [];  // 存储 ID 的数组

            for (let i = 0; i < checkedNodes.length; i++) {
                checkedNodeIds.push(checkedNodes[i].id);
            }

            return checkedNodeIds;
        }
        // 删除指定节点及其子节点中未勾选的任务节点
        const removeUncheckTaskNodesFromTree = (node = null) => {

            // 如果是未勾选的任务节点，则删除
            if (!node.checked) {
                zTree.removeNode(node);
            }
            // 如果有子节点，递归处理
            else if (node.children) {
                for (let i = 0; i < node.children.length; i++) {
                    let childNode = node.children[i];
                    removeUncheckTaskNodesFromTree(childNode);
                }
            }

            return true;
        }
        // 在文件末尾添加此函数，或放在其他合适位置
        const getUncheckNodes = () => {
            let allNodes = zTree.getNodes();
            let uncheckTaskNodes = [];

            // 递归查找未勾选的任务节点
            const findUncheckTaskNodes = (nodes) => {
                for (let i = 0; i < nodes.length; i++) {
                    let node = nodes[i];

                    // 如果是任务节点且未被勾选
                    if (!node.checked && node.event_type != 'show_node') {
                        uncheckTaskNodes.push(node);
                    }

                    // 如果有子节点，递归检查
                    if (node.children) {
                        findUncheckTaskNodes(node.children);
                    }
                }
            }

            findUncheckTaskNodes(allNodes);
            return uncheckTaskNodes;
        }
        //初始化监听事件
        const initListener = function () {
            $('#dataType').on('change', getSrcTree);//数据类型选择
            $('#storage').on('change', getSrcTree);//副本存储选择
            $('.searchCopySource').on('keydown', searchSrcInfo);//搜索功能

            // 聚焦显示清空按钮
            $('.searchCopySource').on('focus', () => {
                $('.copySearchGroup .clear').addClass('show');
                $('.copySearchGroup .clear').removeClass('hide');
            });
            // 失去焦点隐藏清空按钮
            $('.searchCopySource').on('blur', () => {
                if ($('.searchCopySource').val() == '') {
                    $('.copySearchGroup .clear').removeClass('show');
                    $('.copySearchGroup .clear').addClass('hide');
                };
            });
            // 清空搜索字段
            $(`.copySearchGroup .clear`).on('click', (e) => {
                $(`.searchCopySource`).val('');
                e.preventDefault(); // 阻止回车键的默认提交行为
                let searchStr = $('.searchCopySource').val();
                if (searchStr == '' && search == '') {
                    return true;
                }
                Metronic.blockUI({ target: '.src-wrap__content', animate: true });
                search = searchStr;
                // 获取所有的勾选节点
                let uncheckTaskNodes = getUncheckNodes();
                let checkedNodeIds = getCheckedNodeIds();
                let checkedTaskUUids = getCheckedNodeTaskIds();
                if (uncheckTaskNodes.length > 0) {
                    // 删除找到的未勾选任务节点
                    uncheckTaskNodes.forEach(node => {
                        removeUncheckTaskNodesFromTree(node);
                    });
                }
                addSearchNodeToTree(searchStr, checkedNodeIds, checkedTaskUUids);
            });
            $(`.copySearchGroup .search-btn`).on('click', (event) => {
                event.preventDefault(); // 阻止回车键的默认提交行为
                let searchStr = $('.searchCopySource').val();
                if (searchStr == '' && search == '') {
                    return true;
                }
                Metronic.blockUI({ target: '.src-wrap__content', animate: true });
                search = searchStr;
                // 获取所有的勾选节点
                let uncheckTaskNodes = getUncheckNodes();
                let checkedNodeIds = getCheckedNodeIds();
                let checkedTaskUUids = getCheckedNodeTaskIds();
                if (uncheckTaskNodes.length > 0) {
                    // 删除找到的未勾选任务节点
                    uncheckTaskNodes.forEach(node => {
                        removeUncheckTaskNodesFromTree(node);
                    });
                }
                addSearchNodeToTree(searchStr, checkedNodeIds, checkedTaskUUids);
            });
        }
        //初始化副本源页面展示
        const getSrcTree = function () {
            //清空选择列表和提示信息
            $('.copyList li').remove();
            $('#itemList div').remove();
            $("#noDataTips").hide();
            $('#itemList').show();
            $('.copyTreeDiv').show();
            timePointList = [];
            temp_item_array = {};
            temp_exclude_array = {};
            initTree();
        }
        const init = () => {
            initStorage();
            initListener();
        };
        /**
         * 获取副本源
         * 自动添加副本方式
         */
        let getTaskSource = () => {
            let copy_list = [], hostList = [], copy_ignore_object_list = [], showStr = '', stopFlag = false, tapeSource = false, auto_add_flag = false, storage_type_arr = [], remote_node_list = [], p_incr_flag = false, specil_sub_type_flag = false;
            let storageType = parseInt($('#storage option:selected').data('type'));
            let storage_uuid = $('#storage').val();
            let node_uuid_list = [];
            if (!(settings.copy_back || settings.archive_flag || settings.archive_back_flag)) {
                auto_add_flag = $(`#autoAddButton`).get(0).checked;
            }
            // 遍历所有任务
            if (Object.keys(temp_item_array).length > 0) {
                for (let task_uuid in temp_item_array) {
                    let item = temp_item_array[task_uuid];
                    // 遍历任务下的所有对象
                    for (let j in item) {
                        // 排除栈是否有该任务的排除内容
                        if (temp_exclude_array.hasOwnProperty(task_uuid)) {
                            // 如果有则判断排除对象是否与当前对象匹配，匹配则跳过
                            let exclude_item_list = temp_exclude_array[task_uuid];
                            let item_uuid_list = Object.keys(exclude_item_list);
                            if (item_uuid_list.includes(j)) {
                                continue;
                            }
                        }
                        // 永久增量任务标志
                        if(item[j].p_incr_flag){
                            p_incr_flag = true;
                        }
                        // 同一主机不能在同一个副本任务中
                        if (hostList.includes(item[j].item_uuid)) {
                            UIToastr.showWarning(LANG.UI_COPY_SELECT_SAME_HOST_TIPS);
                            stopFlag = true;
                            return { copy_list: copy_list, showStr: showStr, stopFlag: stopFlag };
                        }
                        copy_list.push(item[j]);
                        hostList.push(item[j].item_uuid);
                        showStr += item[j].name + "<br>";
                        if (stopFlag) return false;
                        // 磁带标志
                        if (item[j].storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
                            tapeSource = true;
                        }
                        // 获取源的存储类型
                        if (item[j].storage_type) {
                            storage_type_arr.push(item[j].storage_type)
                        }
                        // 获取源的节点
                        if (!node_uuid_list.includes(item[j].node_uuid)) {
                            node_uuid_list.push(item[j].node_uuid);
                        }
                    }
                }
            }
            // 开启自动添加对象需要排除列表
            if (Object.keys(temp_exclude_array).length > 0) {
                let item_exclude_str = '';
                for (let task_uuid in temp_exclude_array) {
                    let exclude_item_list = temp_exclude_array[task_uuid];
                    // 处理排除列表
                    if (Object.keys(exclude_item_list).length > 0) {
                        for (let item_uuid in exclude_item_list) {
                            copy_ignore_object_list.push(temp_item_array[task_uuid][item_uuid]);
                            item_exclude_str += temp_item_array[task_uuid][item_uuid].item_name + "<br>";
                        }
                    }
                    // 判断任务的排除列表是否和任务的对象列表相同，相同则提示并阻止下一步
                    let exclude_item_keys = Object.keys(exclude_item_list);
                    if (Object.keys(temp_item_array).includes(task_uuid)) {
                        temp_item_keys = Object.keys(temp_item_array[task_uuid]);
                        // 判断是否相同
                        const isSame = temp_item_keys.length === exclude_item_keys.length &&
                            temp_item_keys.every(key => exclude_item_keys.includes(key));
                        if (isSame) {
                            UIToastr.showWarning(LANG.UI_COPY_SOURCE_EXCLUDE_TIPS);
                            stopFlag = true;
                            return { copy_list: copy_list, showStr: showStr, stopFlag: stopFlag };
                        }
                        if (stopFlag) return false;
                    }
                }
                // 确认信息增加排除对象显示
                if (auto_add_flag && item_exclude_str != '') {
                    showStr += LANG.UI_COPY_SOURCE_LABEL_EXCLUDE_ITEM + ':<br>' + item_exclude_str;
                }
            }
            let uniqueStorageTypes = [...new Set(storage_type_arr)];
            // 磁带存储和非磁带存储不能同时选择
            if (uniqueStorageTypes.length > 1 && uniqueStorageTypes.includes(CONF.BD_STORAGE_TYPE.TAPE)) {
                UIToastr.showWarning(LANG.UI_COPY_TAPE_SOURCE_UNIQUE_TIPS);
                stopFlag = true;
            }
            // 回传源为异地数据，需要以异地节点去获取传输网络
            // 源存储为异地资源池
            // if (storageType == '10053') {
            //     let remote_storage_list = storage_pool_map[storage_uuid];
            //     let remote_nodes = [];
            //     for (let i = 0; i < source_storage_list.length; i++) {
            //         if (remote_storage_list.includes(source_storage_list[i].storage_uuid)) {
            //             if (source_storage_list[i].config.remote_node_list) {
            //                 remote_nodes.push(...source_storage_list[i].config.remote_node_list);
            //             }
            //         }
            //     }
            //     remote_node_list = [...new Map(remote_nodes.map(item => [item.remote_node_uuid, item])).values()];
            // }
            // // 源为异地存储
            // if (storageType == CONF.BD_STORAGE_TYPE.REMOTE) {
            //     remote_node_list = [...source_storage_list[storage_uuid].config.remote_node_list];
            // }
            // for (let i = 0; i < remote_node_list.length; i++) {
            //     remote_node_list[i].remote_host_name = remote_node_list[i].remote_node_hostname;
            // }
            // 在确认配置显示副本源
            settings.showDom.html(showStr);
            // 副本对象列表为空，返回停止，阻止下一步
            if (copy_list.length == 0) {
                if (settings.archive_flag || settings.archive_back_flag) {
                    $(".selectCopyTip").html(LANG.UI_COPY_SELECT_EMPTY_ARCHIVE_TIPS).show();
                } else {
                    $(".selectCopyTip").html(LANG.UI_COPY_SELECT_VM_TIPS).show();
                }
                $(".tab-pane__row").css('height', 'calc(100% - 80px)');
                stopFlag = true;
                return {
                    copy_list: copy_list,
                    showStr: showStr,
                    stopFlag: stopFlag,
                    tapeSource: tapeSource,
                    // remote_node_list: remote_node_list,
                    copy_ignore_object_list: copy_ignore_object_list,
                    auto_add_flag: auto_add_flag,
                    node_uuid_list: node_uuid_list,
                    p_incr_flag: p_incr_flag,
                    specil_sub_type_flag: specil_sub_type_flag,
                };
            }
            return {
                copy_list: copy_list,
                showStr: showStr,
                stopFlag: stopFlag,
                tapeSource: tapeSource,
                // remote_node_list: remote_node_list,
                copy_ignore_object_list: copy_ignore_object_list,
                auto_add_flag: auto_add_flag,
                node_uuid_list: node_uuid_list,
                p_incr_flag: p_incr_flag,
                specil_sub_type_flag: specil_sub_type_flag,
            };
        }
        // 获取勾选的副本源信息
        const getCopySource = () => {
            let data_type = parseInt($('#dataType').val());
            // 以任务为源的展示方式不同，需要单独获取
            if ([1].includes(data_type) && !(settings.copy_back || settings.archive_flag || settings.archive_back_flag) && settings.module_type == CONF.MODULE_TYPE.VM) {
                return getTaskSource();
            }
            let nodes = zTree.getCheckedNodes();
            let hostList = [], timePointList = [], copy_list = [], remote_flag = false, showStr = '', stopFlag = false, num = 0, tapeSource = false, storage_type_arr = [], p_incr_flag = false, specil_sub_type_flag = false;
            if (!nodes.length) {
                if (settings.archive_flag || settings.archive_back_flag) {
                    $(".selectCopyTip").html(LANG.UI_COPY_SELECT_EMPTY_ARCHIVE_TIPS).show();
                } else {
                    $(".selectCopyTip").html(LANG.UI_COPY_SELECT_VM_TIPS).show();
                }
                $(".tab-pane__row").css('height', 'calc(100% - 80px)');
                stopFlag = true;
                return { copy_list: copy_list, showStr: showStr, stopFlag: stopFlag, tapeSource: tapeSource };
            }
            $.each(nodes, function (i, d) {
                remote_flag = d.remote_flag;
                if (stopFlag) return false;
                // 永久增量任务标志
                if(d.p_incr_flag){
                    p_incr_flag = true;
                }
                // 处理只勾选主机的情况
                if (TREE_NODE_TYPE.HOST == d.event_type) {//副本源节点的的处理，包括虚拟机、主机、数据库实例及数据库
                    // 统一主机不能在同一个副本任务中
                    if (hostList.includes(d.item_uuid)) {
                        UIToastr.showWarning(LANG.UI_COPY_SELECT_SAME_HOST_TIPS);
                        stopFlag = true;
                        return { copy_list: copy_list, showStr: showStr, stopFlag: stopFlag };
                    }
                    // MongoDB为源，目标屏蔽磁带存储 #28520
                    if (d.sub_type == CONF.DB_TYPE.MONGODB && d.module_type == CONF.MODULE_TYPE.DB) {
                        tapeSource = true;
                        // #28339 取消永久增量+镜像副本上云组合 特殊子类型标志，目前用去区分MongoDB
                        specil_sub_type_flag = true;
                    }
                    hostList.push(d.item_uuid);
                    let new_detail = d.detail;
                    if (settings.editFlag && SETTINGS.copy_list.length > 0) {
                        for (let j = 0; j < SETTINGS.copy_list.length; j++) {
                            if (SETTINGS.copy_list[j].item_uuid == d.item_uuid && SETTINGS.copy_list[j].source_task_uuid == d.task_uuid) {
                                let old_detail = JSON.parse(SETTINGS.copy_list[j].detail);
                                new_detail.last_success_timepoint = old_detail?.last_success_timepoint ?? '';
                            }
                        }
                    }
                    // #29526 副本源为非异地存储，不需要传存储uuid
                    let source_storage_uuid = '';
                    if(settings.copy_back || settings.archive_back_flag){
                        let storage_type = $('#storage option:selected').data('type');
                        // #29526 副本源为异地存储，存储uuid改为传异地ip
                        if(storage_type == CONF.BD_STORAGE_TYPE.REMOTE){
                            source_storage_uuid = $('#storage option:selected').data('remote_ip');
                        }
                    }
                    copy_list[num] = {
                        item_uuid: d.item_uuid,
                        parent_uuid: d.parent_uuid,
                        source_storage_uuid: source_storage_uuid,
                        sub_type: parseInt(d.sub_type),
                        source_task_uuid: d.task_uuid,
                        detail: d.detail && d.detail != '' ? JSON.stringify(d.detail) : '',
                        dir_path: d.path ? d.path.toString() : '',
                        item_name: d.item_name
                    };
                    num++;
                    showStr += d.name + "<br>";
                }
                // 如果勾选了时间点需要保存时间点信息
                if (TREE_NODE_TYPE.POINT == d.event_type) {
                    let point = {
                        'id': d.id,
                        'item_uuid': d.item_uuid,
                        'dir_path': d.path ? d.path.toString() : '',
                        'parent_uuid': d.parent_uuid,
                    }
                    timePointList.push(point);
                    // 置灰的时间点，不能被内置接口识别为是否勾选，需要遍历节点判断
                    // 数据库需要单独处理非完备点
                    if (d.isParent && settings.module_type == CONF.MODULE_TYPE.DB) {
                        let children = d.children;
                        // 数据库是整条链一起勾选，所以需要传递整条链的时间点
                        if (children.length > 0) {
                            for (let j = 0; j < children.length; j++) {
                                let point = {
                                    'id': children[j].id,
                                    'item_uuid': children[j].item_uuid,
                                    'dir_path': children[j].path ? children[j].path.toString() : '',
                                    'parent_uuid': children[j].parent_uuid,
                                }
                                timePointList.push(point);
                            }
                        }
                    }
                    // 365的非完备点和完备点在同一级需要特殊处理
                    if (settings.module_type == CONF.MODULE_TYPE.M365) {
                        // 先获取父节点，在获取所有子节点
                        let child_nodes = d.getParentNode().children;
                        // 子节点勾选则存起来
                        for (let j = 0; j < child_nodes.length; j++) {
                            if (child_nodes[j].checked && d.time_point_uuid != child_nodes[j].time_point_uuid) {//完备是选中的
                                let point = {
                                    'id': child_nodes[j].time_point_uuid,
                                    'item_uuid': child_nodes[j].item_uuid,
                                    'dir_path': child_nodes[j].path ? child_nodes[j].path.toString() : '',
                                    'parent_uuid': child_nodes[j].parent_uuid,
                                }
                                timePointList.push(point);
                            }
                        }
                    }
                }
                if (d.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
                    tapeSource = true;
                }
                if (d.storage_type) {
                    storage_type_arr.push(d.storage_type)
                }
            });
            // 添加时间点
            for (let i = 0; i < copy_list.length; i++) {
                let time_point_uuids = [];
                if (timePointList.length) {
                    for (let j = 0; j < timePointList.length; j++) {
                        if (copy_list[i].item_uuid == timePointList[j].item_uuid) {
                            time_point_uuids.push(timePointList[j].id);
                            // 异地副本数据
                            if (remote_flag) {
                                copy_list[i].dir_path = timePointList[j].dir_path;
                                copy_list[i].parent_uuid = timePointList[j].parent_uuid;
                            }
                        }
                    }
                }
                copy_list[i].timepoint_uuid_list = time_point_uuids;
                copy_list[i].archive_timepoint_uuid = time_point_uuids.length ? time_point_uuids[time_point_uuids.length - 1] : '';
            }
            settings.showDom.html(showStr);
            let uniqueStorageTypes = [...new Set(storage_type_arr)];
            // 磁带存储和非磁带存储不能同时选择
            if (uniqueStorageTypes.length > 1 && uniqueStorageTypes.includes(CONF.BD_STORAGE_TYPE.TAPE)) {
                UIToastr.showWarning(LANG.UI_COPY_TAPE_SOURCE_UNIQUE_TIPS);
                stopFlag = true;
            }
            return {
                copy_list: copy_list,
                stopFlag: stopFlag,
                tapeSource: tapeSource,
                // node_uuid_list: node_uuid_list,
                // remote_node_list: remote_node_list,
                copy_ignore_object_list: [],
                auto_add_flag: false,
                p_incr_flag: p_incr_flag,
                specil_sub_type_flag: specil_sub_type_flag,
            };
        }
        return this.each(function () {
            init();
            const privateMethod = {
                getCopySource: getCopySource
            }
            // 暴露获取副本源信息方法
            $(this).data('mySource', privateMethod);
        });
    }
})(jQuery)