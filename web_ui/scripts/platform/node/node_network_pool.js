var NodeNetworkPool = (() => {
    let nodeNetworkListTree;
    let changePoolRowHeightFlag = false;

    const initListener = () => {
        $('#nodeNetworkPoolList').on('click', '#deleteNodeNetworkPool', clickDeleteNodeNetworkPoolBtn) // 删除资源池
            .on('click', '#addNodeNetworkPool', clickAddNodeNetworkPoolBtn)  // 添加资源池
            .on('click', '.change_height', changePoolRowHeight); // 修改资源池高度

        // 名称和备注输入框
        $('#nodeNetworkPoolName, #nodeNetworkPoolRemark').on('input change', function () {
            $(this).val($(this).val().replace(/[<>"]/gi, ''));
        });

        // drawer按钮
        $('#modifyNodeNetworkPoolDrawer .drawer-footer .cancel').on('click', hideNodeNetworkPoolDrawer);
        $('#modifyNodeNetworkPoolDrawer .drawer-header .drawer-close').on('click', hideNodeNetworkPoolDrawer);
        $('#modifyNodeNetworkPoolDrawer .drawer-footer .btn-confirm').on('click', submitNodeNetworkPoolDraw);
    };

    /**
     * 操作权限校验
     * @returns {{type: number, user_uuid: string, auth: string}|boolean}
     */
    const checkAuth = function(rows) {
        return {
            type: 1,
            user_uuid: [...new Set(rows.map(row => row.user_uuid))].join(','),
            auth: 'resmanagement',
        };
    };

    //////////////////// 开始-事件监听 ////////////////////

    const hideNodeNetworkPoolDrawer = () => {
        $('#modifyNodeNetworkPoolDrawer').drawer('hide');
    };

    /**
     * 添加/修改资源池提交按钮
     * @returns {boolean}
     */
    const submitNodeNetworkPoolDraw = () => {
        let modifyNodeNetworkPoolForm = $('#modifyNodeNetworkPoolForm');
        let nodeNetworkPoolName = $('#nodeNetworkPoolName').val().trim();
        let checkedNodes = nodeNetworkListTree?.getCheckedNodes(true)?.filter(v => v.eventtype === 'network');

        $('#modifyNodeNetworkPoolDrawer .drawer-body .alert-danger').hide();
        if (!nodeNetworkPoolName) {
            $('#modifyNodeNetworkPoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyNodeNetworkPoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_NODE_NETWORK_POOL_NAME_IS_EMPTY);
            return false;
        }

        if (typeof checkedNodes === 'undefined' || !checkedNodes.length) {
            $('#modifyNodeNetworkPoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyNodeNetworkPoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_NODE_NETWORK_POOL_LIST_EMPTY);
            return false;
        }

        if (checkedNodes.length < 2) {
            $('#modifyNodeNetworkPoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyNodeNetworkPoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_NODE_NETWORK_POOL_LIST_LEAST_TWO_NODE);
            return false;
        }

        let reqData = {
            network_pool_name: nodeNetworkPoolName,
            remark: $('#nodeNetworkPoolRemark').val().trim(),
            network_uuid_list: checkedNodes.map(v => v.network_uuid),
            node_uuid: checkedNodes[0].node_uuid,
        };
        let url = '/api/v1/network_pools';
        let method = 'POST';
        if ('edit' === modifyNodeNetworkPoolForm.data('type')) {
            url += `/${modifyNodeNetworkPoolForm.data('uuid')}`;
            method = 'PUT';
        }
        Metronic.blockUI({target: '#modifyNodeNetworkPoolDrawer', animate: true});
        pAjaxRequest(reqData, url, method, res => {
            Metronic.unblockUI('#modifyNodeNetworkPoolDrawer');
            if (!res.success) {
                UIToastr.showWarning(modifyNodeNetworkPoolForm.data('lang'), res.message);
                return;
            }
            UIToastr.showSuccess(modifyNodeNetworkPoolForm.data('lang'), res.message);
            $('#nodeNetworkPoolTable').bootstrapTable('refresh');
            $('#modifyNodeNetworkPoolDrawer').drawer('hide');
        });
    };

    /**
     * 删除网络资源池
     */
    const clickDeleteNodeNetworkPoolBtn = () => {
        let rows = $('#nodeNetworkPoolTable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showWarning(LANG.UI_NODE_NETWORK_POOL_DELETE, LANG.UI_NODE_NETWORK_POOL_DELETE_EMPTY);
            return;
        }

        checkOperateAuth(checkAuth(rows), () => {
            showDeleteNodeNetworkPoolTips(rows);
        });
    };

    /**
     * 显示删除网络资源池弹窗
     * @param {Array<Object>} rows
     * @param {String} rows[].network_pool_name
     * @param {String} rows[].network_pool_uuid
     */
    const showDeleteNodeNetworkPoolTips = (rows) => {
        let title = `
        <div>
            <i class="viconfont vicon-a-Deleteshanchu1"></i>
            <span class="pl2">${LANG.UI_NODE_NETWORK_POOL_DELETE}</span>
        </div>
        `;
        let message = `
        <div class="delete-tips-div">
            <div class="delete-tips__wrapper">
                <div class="delete-tips">
                    <div class="delete-tips__icon">
                        <i class="viconfont vicon-a-Close-oneguanbi"></i>
                    </div>
                    <div class="delete-tips__text" style="">
                        <div class="title">${LANG.UI_NODE_NETWORK_POOL_DELETE_TIPS}</div>
                        <div class="text">${rows.map(v => v.network_pool_name).join('、')}</div>
                    </div>
                </div>
            </div>
        </div>
        `;
        bootbox.confirm({
            title,
            message,
            callback: debounce(result => {
                if (!result) {
                    return;
                }
                deleteNodeNetworkPool(rows.map(v => v.network_pool_uuid));
            }, 300),
        });
    };

    /**
     * 删除网络资源池
     * @param nodeNetworkPoolUuidList
     */
    const deleteNodeNetworkPool = (nodeNetworkPoolUuidList) => {
        Metronic.blockUI({target: '#nodeNetworkPoolList', animate: true});
        pAjaxRequest({network_pool_uuid_list: nodeNetworkPoolUuidList}, `/api/v1/network_pools/batch`, 'DELETE', res => {
            Metronic.unblockUI('#nodeNetworkPoolList');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_NETWORK_POOL_DELETE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_NETWORK_POOL_DELETE, res.message);
            $('#nodeNetworkPoolTable').bootstrapTable('refresh');
        });
    };

    /**
     * 编辑网络资源池
     * @param e
     * @param value
     * @param row
     */
    const editNodeNetworkPoolBtn = (e, value, row) => {
        $('#modifyNodeNetworkPoolDrawer').drawer('show');
        $('#modifyNodeNetworkPoolDrawer .drawer-body .alert-danger').hide();
        $('#modifyNodeNetworkPoolDrawer .drawer-title i.icon').addClass('vicon-a-Editbianji').removeClass('vicon-biaogetianjia');
        $('#modifyNodeNetworkPoolDrawer .drawer-title span.title').html(LANG.UI_NODE_NETWORK_POOL_EDIT_TITLE);
        $('#nodeNetworkPoolName').val(row.network_pool_name);
        $('#nodeNetworkPoolRemark').val(row.remark);
        $('#modifyNodeNetworkPoolForm').data('type', 'edit')
            .data('uuid', row.network_pool_uuid)
            .data('lang', LANG.UI_NODE_NETWORK_POOL_EDIT_TITLE);
        initNodeNetworkListTree(row.network_list.map(v => v.network_uuid));
    };

    /**
     * 添加网络资源池
     */
    const clickAddNodeNetworkPoolBtn = () => {
        $('#modifyNodeNetworkPoolDrawer').drawer('show');
        $('#modifyNodeNetworkPoolDrawer .drawer-body .alert-danger').hide();
        $('#modifyNodeNetworkPoolDrawer .drawer-title i.icon').removeClass('vicon-a-Editbianji').addClass('vicon-biaogetianjia');
        $('#modifyNodeNetworkPoolDrawer .drawer-title span.title').html(LANG.UI_NODE_NETWORK_POOL_ADD_TITLE);
        $('#nodeNetworkPoolName').val('');
        $('#nodeNetworkPoolRemark').val('');
        $('#modifyNodeNetworkPoolForm').data('type', 'add')
            .data('lang', LANG.UI_NODE_NETWORK_POOL_ADD_TITLE)
            .removeAttr('data-uuid');
        // 填充默认名称
        Metronic.blockUI({target: '#modifyNodeNetworkPoolDrawer', animate: true});
        let reqData = {
            pool_type: 'network',
            pool_prefix_name: LANG.UI_NODE_NETWORK_POOL,
        };
        pAjaxRequest(reqData, `/api/v1/nodes/resource_pool/name`, 'GET', res => {
            Metronic.unblockUI('#modifyNodeNetworkPoolDrawer');
            if (!res.success) {
                return;
            }
            $('#nodeNetworkPoolName').val(res.data.pool_name);
        });
        initNodeNetworkListTree();
    };

    /**
     * 设置资源池高度
     */
    const setPoolRowHeight = () => {
        let rows = $('#nodeNetworkPoolTable').bootstrapTable('getData');
        if (!rows.length) {
            return;
        }
        if (changePoolRowHeightFlag) {
            $('#nodeNetworkPoolTable>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            });
        } else {
            $('#nodeNetworkPoolTable>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            });
        }
    };

    /**
     * 修改资源池高度
     */
    const changePoolRowHeight = () => {
        if (!changePoolRowHeightFlag) {
            changePoolRowHeightFlag = true;
            $('#vin_node_network_pool_toolbar .change_height i').addClass('icon-auto-height2');
        } else {
            changePoolRowHeightFlag = false;
            $('#vin_node_network_pool_toolbar .change_height i').removeClass('icon-auto-height2');
        }
        setPoolRowHeight();
    };

    ////////// 开始-计算节点树 //////////

    const setFontCss = (treeId, treeNode) => {
        let style = {
            // 'max-width': '200px',
            'display': 'inline-block',
            'overflow': 'hidden',
            'text-overflow': 'ellipsis',
            color: '#333',
        };
        if (treeNode.eventtype === 'node' && !treeNode.online_flag) {
            style.color = '#999';
        }
        return style;
    };

    const getNodeListTreeSetting = () => {
        return {
            check: {
                enable: true,
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
                beforeClick: nodeNetworkListTreeClick,
                onCheck: nodeNetworkListTreeCheck,
                beforeExpand: nodeNetworkListTreeExpand
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: setFontCss,
            }
        };
    };

    const loadMoreNetworkNodes = treeNode => {
        Metronic.blockUI({target: '#nodeNetworkListTree', animate: true});
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
        };
        pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
            Metronic.unblockUI('#nodeNetworkListTree');
            if (!res.success) {
                UIToastr.showWarning($('#modifyNodeNetworkPoolForm').data('lang'), res.message);
                return;
            }
            let moreFlag = res.data.total !== (res.data.rows.length + parseInt(treeNode.total));
            let nodes = [];
            for (const item of res.data.rows) {
                let checked = treeNode.node_network_uuid_list.includes(item.node_uuid);
                let name = item.host_name + '(' + item.ip + ')';
                if (!item.online_flag) {
                    name = `(${LANG.UI_VISUAL_OFF_LINE})${name}`;
                }
                nodes.push({
                    id: item.node_uuid,
                    pId: 'all-node',
                    title: name,
                    name: name,
                    isParent: false,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'node',
                    online_flag: item.online_flag,
                    open: true,
                    checked,
                    node_uuid: item.node_uuid,
                    node_network_uuid_list: treeNode.node_network_uuid_list,
                });

                for (const networkItem of item.network_list) {
                    let checked = treeNode.node_network_uuid_list.includes(networkItem.network_uuid);
                    let name = networkItem.network_ip + ':' + networkItem.network_port;
                    nodes.push({
                        id: networkItem.network_uuid,
                        pId: item.node_uuid,
                        title: name,
                        name: name,
                        isParent: false,
                        icon: './img/platform/node/node-network-tree.svg',
                        eventtype: 'network',
                        checked,
                        node_uuid: item.node_uuid,
                        network_uuid: networkItem.network_uuid,
                        node_network_uuid_list: treeNode.node_network_uuid_list,
                    });
                }
            }
            if (moreFlag) {
                nodes.push({
                    id: 'more_node',
                    pId: 'all-node',
                    title: LANG.UI_NODE_MORE,
                    name: LANG.UI_NODE_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length + parseInt(treeNode.total),
                    node_network_uuid_list: treeNode.node_network_uuid_list,
                });
            }
            nodeNetworkListTree.addNodes(treeNode.getParentNode(), nodes);
            nodeNetworkListTree.removeNode(treeNode);
        });
    };

    const nodeNetworkListTreeClick = (treeId, treeNode) => {
        if ('more' === treeNode.eventtype) {
            loadMoreNetworkNodes(treeNode);
        } else {
            nodeNetworkListTree.checkNode(treeNode, !treeNode.checked, true, true);
        }
    };

    /**
     * 选择了节点
     * @param {*} e
     * @param {*} treeId
     * @param {*} treeNode
     */
    const nodeNetworkListTreeCheck = (e, treeId, treeNode) => {
        if (!treeNode.checked) {
            return;
        }
        let checkNodes = nodeNetworkListTree.getCheckedNodes(true);
        let resetOldNetworkFlag = false;
        for (const checkNode of checkNodes) {
            if (checkNode.node_uuid !== treeNode.node_uuid) {
                resetOldNetworkFlag = true;
            }
        }
        if (resetOldNetworkFlag) {
            nodeNetworkListTree.checkAllNodes(false);
            nodeNetworkListTree.checkNode(treeNode, true);
            if (treeNode.eventtype === 'node' && Array.isArray(treeNode.children)) {
                for (const childNode of treeNode.children) {
                    nodeNetworkListTree.checkNode(childNode, true);
                }
            }
        }
    };

    const nodeNetworkListTreeExpand = (treeId, treeNode) => {
        //
    };

    const initNodeNetworkListTree = (node_network_uuid_list = []) => {
        /**
         * 1. 节点
         * 2. 节点网络
         */
        if (nodeNetworkListTree) {
            let node = nodeNetworkListTree.getNodeByParam('id', 'all-node');
            if (node) {
                nodeNetworkListTree.removeNode(nodeNetworkListTree.getNodeByParam('id', 'all-node'));
            }
        }
        Metronic.blockUI({target: '#nodeNetworkListTree', animate: true});
        let reqData = {offset: 0, limit: 20};
        pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
            Metronic.unblockUI('#nodeNetworkListTree');
            if (!res.success) {
                UIToastr.showWarning($('#modifyNodeNetworkPoolForm').data('lang'), res.message);
                return;
            }

            let moreFlag = res.data.rows.length !== res.data.total;
            let nodes = [{
                id: 'all-node',
                pId: 0,
                title: LANG.UI_SEARCH_ALL_NODE,
                name: LANG.UI_SEARCH_ALL_NODE,
                isParent: true,
                open: true,
                icon: './img/platform/node/all-node.svg',
                eventtype: 'all_node',
                nocheck: true,
            }];
            for (const item of res.data.rows) {
                let name = item.host_name + '(' + item.ip + ')';
                if (!item.online_flag) {
                    name = `(${LANG.UI_VISUAL_OFF_LINE})${name}`;
                }
                nodes.push({
                    id: item.node_uuid,
                    pId: 'all-node',
                    title: name,
                    name: name,
                    isParent: true,
                    open: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'node',
                    online_flag: item.online_flag,
                    checked: false,
                    node_uuid: item.node_uuid,
                    node_network_uuid_list,
                });

                for (const networkItem of item.network_list) {
                    let checked = node_network_uuid_list.includes(networkItem.network_uuid);
                    let name = networkItem.network_ip + ':' + networkItem.network_port;
                    nodes.push({
                        id: networkItem.network_uuid,
                        pId: item.node_uuid,
                        title: name,
                        name: name,
                        isParent: false,
                        icon: './img/platform/node/node-network-tree.svg',
                        eventtype: 'network',
                        checked,
                        node_uuid: item.node_uuid,
                        network_uuid: networkItem.network_uuid,
                        node_network_uuid_list,
                    });
                }
            }
            if (moreFlag) {
                nodes.push({
                    id: 'more_node',
                    pId: 'all-node',
                    title: LANG.UI_NODE_MORE,
                    name: LANG.UI_NODE_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length,
                    node_network_uuid_list,
                });
            }
            nodeNetworkListTree = $.fn.zTree.init($('#nodeNetworkListTree'), getNodeListTreeSetting(), nodes);
        });
    };

    ////////// 结束-计算节点树 //////////

    //////////////////// 结束-事件监听 ////////////////////

    const getQueryParams = () => {
        let params = {};
        let search = $('.node-pool-network-search').val().trim();
        if (search) {
            params.network_pool_name = search;
        }
        return params;
    };

    const initNodeNetworkPoolTableHeight = () => {
        let breadcrumbHeight = 38;
        let toolbarHeight = 57;
        let paginationHeight = 52;
        let tabTitleHeight = 48;
        let navTitleHeight = 46;
        // page-content有40px的内边距
        // portlet-body有10px的内边距
        // tab-content有20px的外边距离
        let otherHeight = 40 + 10 + 20;
        let height = window.innerHeight - breadcrumbHeight - toolbarHeight - paginationHeight - tabTitleHeight - navTitleHeight - otherHeight;
        $("#nodeNetworkPoolList .fixed-table-body").css({
            'height': height,
        });
    };

    const formatterOperate = () => {
        return `
        <div class="btn-group">
            <div class="btn_operation_vicon">
                <a class="edit-node-network-pool">
                    <i class="viconfont vicon-a-Editbianji"></i>
                </a>
                <a class="delete-node-network-pool">
                    <i class="viconfont vicon-a-Deleteshanchu1"></i>
                </a>
            </div>
        </div>
        `;
    };

    const initNodeNetworkPoolBtnOp = () => {
        return {
            'click .edit-node-network-pool': (e, value, row) => {
                checkOperateAuth(checkAuth([row]), () => {
                    editNodeNetworkPoolBtn(e, value, row);
                });
            },
            'click .delete-node-network-pool': (e, value, row) => {
                checkOperateAuth(checkAuth([row]), () => {
                    showDeleteNodeNetworkPoolTips([row]);
                });
            }
        };
    };

    /**
     * 设置选中事件
     */
    const checkEvent = function (tableId, btnId) {
        let select = $('' + tableId + '').bootstrapTable('getSelections');
        if (select.length == 0) {
            $('' + btnId + ' i').addClass('icon-gray-delete');
            $('' + btnId + ' i').removeClass('icon-white-delete');
            $('' + btnId + '').removeClass('select-delete-btn');
            $('' + btnId + '').addClass('cancel-delete-btn');
        } else {
            $('' + btnId + ' i').removeClass('icon-gray-delete');
            $('' + btnId + ' i').addClass('icon-white-delete');
            $('' + btnId + '').removeClass('cancel-delete-btn');
            $('' + btnId + '').addClass('select-delete-btn');
        }
    }

    const nodeNetworkPoolRowCheck = () => {
        checkEvent('#nodeNetworkPoolTable', '#deleteNodeNetworkPool');
    };

    const customNodeNetworkPoolTool = () => {
        let beforeInput = `<div class="btn-group" style="margin: 0">
            <button type="button" id="deleteNodeNetworkPool" class="b-btn brr2 mr12 table-toolbar-btn">
                <i class="icon-gray-delete"></i>
            </button>
        </div>`;
        let afterInput = `
        <div class="btn-group" style="margin: 0">
            <button class="btn table-toolbar-btn" id="addNodeNetworkPool" data-toggle="drawer"
                data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false" style="width: auto; height: 34px; border: 0">
                <i class="viconfont vicon-biaogetianjia"></i>
                <span class="pl2">${LANG.UI_PUBLIC_ADD}</span>
            </button>
        </div>
        `;
        return {beforeInput, afterInput};
    };

    const getNodeNetworkPoolTableColumns = () => {
        return [{
            checkbox: true,
            sortable: false,
            width: '2',
            widthUnit: '%',
            forceHide: true, //隐藏掉导出该列数据
        }, {
            title: LANG.UI_PUBLIC_TABLE_ID,
            field: '',
            sortable: false,
            width: '4',
            widthUnit: '%',
            formatter: (value, row, index) => {
                return index + 1;
            },
        }, {
            title: LANG.UI_NODE_NETWORK_POOL_TABLE_NAME,
            field: 'network_pool_name',
            width: '12',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_NETWORK_POOL_TABLE_NETWORK,
            field: 'network_list',
            sortable: false,
            width: '15',
            widthUnit: '%',
            /**
             * @param {Array<Object>} value
             * @param {String} value[].network_name
             * @param {String} value[].network_uuid
             * @param {String} value[].network_ip
             * @param {Number} value[].network_port
             * @returns {string}
             */
            formatter: value => {
                if (!value.length) {
                    return '--';
                }
                let networkDes = [];
                for (const network of value) {
                    networkDes.push(`${network.network_ip}:${network.network_port}`);
                }
                let name = networkDes.join('<br>');
                let title = networkDes.join('\n');
                return `<span title="${title}">${name}</span>`;
            },
        }, {
            title: LANG.UI_PUBLIC_NETWORK_POOL_IN_NODE,
            field: 'node_info',
            width: '15',
            widthUnit: '%',
            formatter: nodeInfo => {
                let showName = nodeInfo.host_name + '(' + nodeInfo.node_ip + ')';
                if (nodeInfo.node_nickname && nodeInfo.node_nickname != nodeInfo.node_ip) {
                    showName = nodeInfo.node_nickname + '(' + nodeInfo.node_ip + ')';
                }
                return `<span title="${showName}">${showName}</span>`;
            }
        }, {
            title: LANG.UI_NODE_NETWORK_POOL_TABLE_UPDATE_TIME,
            field: 'update_time',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_NETWORK_POOL_TABLE_CREATOR,
            field: 'creator',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_NETWORK_POOL_TABLE_REMARK,
            field: 'remark',
            width: '15',
            widthUnit: '%',
            formatter: value => value ? `<span title="${value}">${value}</span>` : '--',
        }, {
            title: LANG.UI_PUBLIC_OPERATION,
            width: '10',
            widthUnit: '%',
            forceHide: true, //隐藏掉导出该列数据
            sortable: false,
            events: initNodeNetworkPoolBtnOp(),
            clickToSelect: false,
            switchable: false,
            formatter: formatterOperate,
            opButton: true,
        }];
    };

    const getNodeNetworkPoolTableOption = () => {
        return {
            vin_url: `/api/v1/network_pools`,
            vin_params: getQueryParams,
            vin_method: 'get',
            buttonsToolbar: '#vin_node_network_pool_toolbar .vin_btnToolbar',
            toolbarId: '#vin_node_network_pool_toolbar',
            vin_toolbar: '#vin_node_network_pool_toolbar',
            sortName: 'update_time',
            sortOrder: 'desc',
            // 搜索
            placeholder: LANG.UI_NODE_NETWORK_POOL_TABLE_SEARCH_PLACEHOLDER,
            searchInput: true,
            searchClass: 'node-pool-network-search',
            // searchTimeOut: 0,
            searchOnEnterKey: false,
            // 隐藏行
            hideColumns: '',
            showButtonText: false,  // 显示按钮文字
            clickToSelect: true,  // 点击行选中
            // 分页
            pagination: true,
            pageList: [10, 20, 50, 100, 150, 200],
            pageSize: 20,
            resizable: true,
            showRefresh: false,  // 显示refresh按钮
            showExport: true,  // 显示导出按钮
            singleSelect: false,  // 单选
            changeHeightBtn: true,  // 改变高度的按钮
            onResetView: initNodeNetworkPoolTableHeight,
            onRefresh: () => {
                $("#nodeNetworkPoolTable").bootstrapTable('hideLoading');
            },
            onPostBody: () => {
                $(".popovers").popover();
                nodeNetworkPoolRowCheck();
                setPoolRowHeight();
            },
            onCheck: nodeNetworkPoolRowCheck,
            onUncheck: nodeNetworkPoolRowCheck,
            onCheckAll: nodeNetworkPoolRowCheck,
            onUncheckAll: nodeNetworkPoolRowCheck,
            customTool: customNodeNetworkPoolTool(),
            columns: getNodeNetworkPoolTableColumns(),
        };
    };

    const initNodeNetworkPoolTable = () => {
        $('#nodeNetworkPoolTable').bootstrapTable('destroy').baseTableConfig().init(getNodeNetworkPoolTableOption());
    };

    return {
        init: () => {
            initListener();
            initNodeNetworkPoolTable();
        },
    };
})();

jQuery(document).ready(() => {
    NodeNetworkPool.init();
});
