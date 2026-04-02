var NodePool = (() => {
    let nodeListTree;
    let changePoolRowHeightFlag = false;
    let nodePoolTopological = null;

    const NODE_FUNCTION_ENUM = {
        MANAGEMENT: 1,
        CALCULATION: 2,
        MANAGEMENT_AND_CALCULATION: 3,
    };

    const initListener = () => {
        $('#nodePoolList').on('click', '#deleteNodePool', clickDeleteNodePoolBtn) // 删除资源池
            .on('click', '#addNodePool', clickAddNodePoolBtn)  // 添加资源池
            .on('click', '.change_height', changePoolRowHeight); // 修改资源池高度

        // 名称和备注输入框
        $('#nodePoolName, #nodePoolRemark').on('input change', function () {
            $(this).val($(this).val().replace(/[<>"]/gi, ''));
        });

        // drawer按钮
        $('#modifyNodePoolDrawer .drawer-footer .cancel').on('click', hideNodePoolDrawer);
        $('#modifyNodePoolDrawer .drawer-header .drawer-close').on('click', hideNodePoolDrawer);
        $('#modifyNodePoolDrawer .drawer-footer .btn-confirm').on('click', submitNodePoolDraw);
        $('#nodePoolTopologicalDrawer .drawer-footer .cancel').on('click', hideNodePoolTopologicalDrawer);
        $('#nodePoolTopologicalDrawer .drawer-footer .btn-confirm').on('click', hideNodePoolTopologicalDrawer);

        // echarts
        $(window).resize(() => {
            if (nodePoolTopological) {
                nodePoolTopological.resize();
            }
        });
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

    const hideNodePoolTopologicalDrawer = () => {
        $('#nodePoolTopologicalDrawer').drawer('hide');
    };

    const hideNodePoolDrawer = () => {
        $('#modifyNodePoolDrawer').drawer('hide');
    };

    /**
     * 添加/修改资源池提交按钮
     * @returns {boolean}
     */
    const submitNodePoolDraw = () => {
        let modifyNodePoolForm = $('#modifyNodePoolForm');
        let nodePoolName = $('#nodePoolName').val().trim();
        let checkedNodes = nodeListTree?.getCheckedNodes(true)?.filter(v => v.eventtype === 'node');

        $('#modifyNodePoolDrawer .drawer-body .alert-danger').hide();
        if (!nodePoolName) {
            $('#modifyNodePoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyNodePoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_NODE_POOL_NAME_IS_EMPTY);
            return false;
        }

        if (typeof checkedNodes === 'undefined' || !checkedNodes.length) {
            $('#modifyNodePoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyNodePoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_NODE_POOL_NODE_LIST_EMPTY);
            return false;
        }

        if (checkedNodes.length < 2) {
            $('#modifyNodePoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyNodePoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_NODE_POOL_NODE_LIST_LEAST_TWO_NODE);
            return false;
        }

        let reqData = {
            node_pool_name: nodePoolName,
            remark: $('#nodePoolRemark').val().trim(),
        };
        let url = '/api/v1/node_pools';
        let method = 'POST';
        reqData.node_uuid_list = checkedNodes.map(v => v.node_uuid);
        if ('edit' === modifyNodePoolForm.data('type')) {
            url += `/${modifyNodePoolForm.data('uuid')}`;
            method = 'PUT';
        }
        Metronic.blockUI({target: '#modifyNodePoolDrawer', animate: true});
        pAjaxRequest(reqData, url, method, res => {
            Metronic.unblockUI('#modifyNodePoolDrawer');
            if (!res.success) {
                UIToastr.showWarning(modifyNodePoolForm.data('lang'), res.message);
                return;
            }
            UIToastr.showSuccess(modifyNodePoolForm.data('lang'), res.message);
            $('#nodePoolTable').bootstrapTable('refresh');
            $('#modifyNodePoolDrawer').drawer('hide');
        });
    };

    /**
     * 删除计算资源池
     */
    const clickDeleteNodePoolBtn = () => {
        let rows = $('#nodePoolTable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showWarning(LANG.UI_NODE_POOL_DELETE, LANG.UI_NODE_POOL_DELETE_EMPTY);
            return;
        }

        checkOperateAuth(checkAuth(rows), () => {
            showDeleteNodePoolTips(rows);
        });
    };

    /**
     * 显示删除计算资源池弹窗
     * @param {Array<Object>} rows
     * @param {String} rows[].node_pool_name
     * @param {String} rows[].node_pool_uuid
     */
    const showDeleteNodePoolTips = (rows) => {
        let title = `
        <div>
            <i class="viconfont vicon-a-Deleteshanchu1"></i>
            <span class="pl2">${LANG.UI_NODE_POOL_DELETE}</span>
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
                        <div class="title">${LANG.UI_NODE_POOL_DELETE_TIPS}</div>
                        <div class="text">${rows.map(v => v.node_pool_name).join('、')}</div>
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
                deleteNodePool(rows.map(v => v.node_pool_uuid));
            }, 300),
        });
    };

    /**
     * 删除计算资源池
     * @param nodePoolUuidList
     */
    const deleteNodePool = (nodePoolUuidList) => {
        Metronic.blockUI({target: '#nodePoolList', animate: true});
        pAjaxRequest({node_pool_uuid_list: nodePoolUuidList}, `/api/v1/node_pools/batch`, 'DELETE', res => {
            Metronic.unblockUI('#nodePoolList');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_POOL_DELETE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_POOL_DELETE, res.message);
            $('#nodePoolTable').bootstrapTable('refresh');
        });
    };

    /**
     * 编辑计算资源池
     * @param e
     * @param value
     * @param row
     */
    const editNodePoolBtn = (e, value, row) => {
        $('#modifyNodePoolDrawer').drawer('show');
        $('#modifyNodePoolDrawer .drawer-body .alert-danger').hide();
        $('#modifyNodePoolTitleIcon').addClass('vicon-a-Editbianji').removeClass('vicon-biaogetianjia');
        $('#modifyNodePoolTitle').html(LANG.UI_NODE_POOL_EDIT_TITLE);
        $('#nodePoolName').val(row.node_pool_name);
        $('#nodePoolRemark').val(row.remark);
        $('#modifyNodePoolForm').data('type', 'edit')
            .data('uuid', row.node_pool_uuid)
            .data('lang', LANG.UI_NODE_POOL_EDIT_TITLE);
        initNodeListTree(row.node_list.map(v => v.node_uuid));
    };

    /**
     * 添加计算资源池
     */
    const clickAddNodePoolBtn = () => {
        $('#modifyNodePoolDrawer').drawer('show');
        $('#modifyNodePoolDrawer .drawer-body .alert-danger').hide();
        $('#modifyNodePoolTitleIcon').removeClass('vicon-a-Editbianji').addClass('vicon-biaogetianjia');
        $('#modifyNodePoolTitle').html(LANG.UI_NODE_POOL_ADD_TITLE);
        $('#nodePoolName').val('');
        $('#nodePoolRemark').val('');
        $('#modifyNodePoolForm').data('type', 'add')
            .data('lang', LANG.UI_NODE_POOL_ADD_TITLE)
            .removeAttr('data-uuid');
        // 填充默认名称
        Metronic.blockUI({target: '#modifyNodePoolDrawer', animate: true});
        let reqData = {
            pool_type: 'node',
            pool_prefix_name: LANG.UI_NODE_NODE_POOL,
        };
        pAjaxRequest(reqData, `/api/v1/nodes/resource_pool/name`, 'GET', res => {
            Metronic.unblockUI('#modifyNodePoolDrawer');
            if (!res.success) {
                return;
            }
            $('#nodePoolName').val(res.data.pool_name);
        });
        initNodeListTree();
    };

    /**
     * 设置资源池高度
     */
    const setPoolRowHeight = () => {
        let rows = $('#nodePoolTable').bootstrapTable('getData');
        if (!rows.length) {
            return;
        }
        if (changePoolRowHeightFlag) {
            $('#nodePoolTable>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            });
        } else {
            $('#nodePoolTable>tbody>tr>td').css({
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
            $('#vin_node_pool_toolbar .change_height i').addClass('icon-auto-height2');
        } else {
            changePoolRowHeightFlag = false;
            $('#vin_node_pool_toolbar .change_height i').removeClass('icon-auto-height2');
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
                beforeClick: nodeListTreeClick,
                onCheck: nodeListTreeCheck,
                beforeExpand: nodeListTreeExpand
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: setFontCss,
            }
        };
    };

    const loadMoreNodeNodes = treeNode => {
        Metronic.blockUI({target: '#nodeListTree', animate: true});
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
            node_function: NODE_FUNCTION_ENUM.CALCULATION,
        };
        pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
            Metronic.unblockUI('#nodeListTree');
            if (!res.success) {
                UIToastr.showWarning($('#modifyNodePoolForm').data('lang'), res.message);
                return;
            }
            let moreFlag = res.data.total !== (res.data.rows.length + parseInt(treeNode.total));
            let nodes = [];
            for (const item of res.data.rows) {
                let checked = treeNode.node_uuid_list.includes(item.node_uuid);
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
                    checked,
                    node_uuid: item.node_uuid,
                    node_uuid_list: treeNode.node_uuid_list,
                });
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
                    node_uuid_list: treeNode.node_uuid_list,
                });
            }
            nodeListTree.addNodes(treeNode.getParentNode(), nodes);
            nodeListTree.removeNode(treeNode);
        });
    };

    const nodeListTreeClick = (treeId, treeNode) => {
        if ('more' === treeNode.eventtype) {
            loadMoreNodeNodes(treeNode);
        } else {
            nodeListTree.checkNode(treeNode, !treeNode.checked, true);
        }
    };

    const nodeListTreeCheck = (e, treeId, treeNode) => {
        //
    };

    const nodeListTreeExpand = (treeId, treeNode) => {
        //
    };

    const initNodeListTree = (node_uuid_list = []) => {
        if (nodeListTree) {
            let node = nodeListTree.getNodeByParam('id', 'all-node');
            if (node) {
                nodeListTree.removeNode(nodeListTree.getNodeByParam('id', 'all-node'));
            }
        }
        Metronic.blockUI({target: '#nodeListTree', animate: true});
        let reqData = {
            offset: 0,
            limit: 20,
            node_function: NODE_FUNCTION_ENUM.CALCULATION,
        };
        pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
            Metronic.unblockUI('#nodeListTree');
            if (!res.success) {
                UIToastr.showWarning($('#modifyNodePoolForm').data('lang'), res.message);
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
            }];
            for (const item of res.data.rows) {
                let checked = node_uuid_list.includes(item.node_uuid);
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
                    checked,
                    node_uuid: item.node_uuid,
                    node_uuid_list,
                });
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
                    node_uuid_list,
                });
            }
            nodeListTree = $.fn.zTree.init($('#nodeListTree'), getNodeListTreeSetting(), nodes);
        });
    };

    ////////// 结束-计算节点树 //////////

    ////////// 开始-计算节点拓扑图 //////////

    /**
     * 显示拓扑图
     * @param e
     * @param value
     * @param row
     */
    const showNodePoolTopological = (e, value, row) => {
        $('#nodePoolTopologicalDrawer').drawer('show');
        $('#nodePoolTopologicalTitle').html(row.node_pool_name);

        Metronic.blockUI({target: '#nodePoolTopologicalDrawer', animate: true});
        pAjaxRequest({}, `/api/v1/node_pools/${row.node_pool_uuid}`, 'GET', res => {
            Metronic.unblockUI('#nodePoolTopologicalDrawer');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_POOL_TOPOLOGICAL, res.message);
                return;
            }
            initNodePoolLogical(res.data);
        });
    };

    /**
     * 获取资源池标签的显示格式
     * @param params
     * @returns {string}
     */
    const getNodePoolLabelFormatter = params => {
        let maxLength = 10;
        let name = params.data.name;
        name = name.length > maxLength ? name.slice(0, maxLength) + '...' : name;
        return [
            `{img|}`,
            `\n`,
            `{name|${name}}`,
        ].join('\n');
    };

    /**
     * 获取计算节点标签的显示格式
     * @param params
     * @returns {string}
     */
    const getNodeLabelFormatter = params => {
        let ret = [`{img|}`, `\n`];
        let maxLength = 30;
        if (params.data.more_node) {
            ret.push(`{more_node|${LANG.UI_CLUSTER_MORE_NODE}}`);
        } else {
            let _nodeIp = params.data.node_ip;
            _nodeIp = _nodeIp.length > maxLength ? _nodeIp.slice(0, maxLength) + '...' : _nodeIp;
            ret.push(`{node_ip|${_nodeIp}}`);
        }
        return ret.join('\n');
    };

    /**
     * 获取节点tooltip的显示格式
     * @param params
     * @returns {String|*|string}
     */
    const getNodeTooltipFormatter = params => {
        if (!params.data.more_node) {
            if (!params.data.online_flag) {
                return `
                <span style="color: #999">
                    (${LANG.UI_VISUAL_OFF_LINE})${params.data.node_ip}
                </span>
                `;
            }
            return params.data.node_ip;
        }
        let ret = `<div id="test1" class="node-more">
            <div class="node-more__header">
                <span class="text">${LANG.UI_CLUSTER_MORE_NODE}</span>
            </div>
            <div class="node-list">
        `;
        for (const _nodeInfo of params.data.node_list) {
            ret += `<div class="node-more-item">
                <span>${_nodeInfo.node_ip}</span>
            </div>`;
        }
        ret += `</div></div>`;
        return ret;
    };

    /**
     * 获取存储设备标签的显示格式
     * @param params
     * @returns {`{storage_name|${string}}`}
     */
    const getStorageLabelFormatter = params => {
        let maxLength = 30;
        let storageNameList = [];
        for (const _storageInfo of params.data.storage_list) {
            let _storageName = _storageInfo.storage_name;
            _storageName = _storageName.length > maxLength ? _storageName.slice(0, maxLength) + '...' : _storageName;
            storageNameList.push(_storageName);
        }
        let storageName = storageNameList.join('\n');
        if (storageNameList.length > 3) {
            storageName = `${storageNameList[0]}\n`;
            storageName += `${storageNameList[1]}\n`;
            storageName += '...';
        }
        return `{storage_name|${storageName}}`;
    };

    /**
     * 获取存储设备tooltip的显示格式
     * @param params
     * @returns {string}
     */
    const getStorageTooltipFormatter = params => {
        let ret = `<div id="test1" class="node-more">
            <div class="node-more__header">
                <span class="text">${LANG.UI_STORAGE_ALL_STORAGE}</span>
            </div>
            <div class="node-list">
        `;
        for (const _storageInfo of params.data.storage_list) {
            let name = _storageInfo.storage_name + '(' + CONF.STORAGE_TYPE_DES[parseInt(_storageInfo.storage_type)]
                + ', ' + LANG.UI_JOB_TOTAL_SIZE + ': ' + storageCalculateSize(_storageInfo.total_size) + ', '
                + LANG.UI_JOB_FREE_SIZE + ': ' + storageCalculateSize(_storageInfo.free_size) + ')';
            ret += `<div class="node-more-item">
                <span>${name}</span>
            </div>`;
        }
        ret += `</div></div>`;
        return ret;
    };

    /**
     * 获取网络标签的显示格式
     * @param params
     * @returns {`{network_name|${string}}`}
     */
    const getNetworkLabelFormatter = params => {
        let maxLength = 30;
        let networkNameList = [];
        for (const _networkInfo of params.data.network_list) {
            let networkName = _networkInfo.network_ip;
            networkName = networkName.length > maxLength ? networkName.slice(0, maxLength) + '...' : networkName;
            networkNameList.push(networkName);
        }
        let networkName = networkNameList.join('\n');
        if (networkNameList.length > 3) {
            networkName = `${networkNameList[0]}\n`;
            networkName += `${networkNameList[1]}\n`;
            networkName += '...';
        }
        return `{network_name|${networkName}}`;
    };

    /**
     * 获取网络tooltip的显示格式
     * @param params
     * @returns {string}
     */
    const getNetworkTooltipFormatter = params => {
        let ret = `<div id="test1" class="node-more">
            <div class="node-more__header">
                <span class="text">${LANG.UI_NODE_NETWORK_ALL_NETWORK}</span>
            </div>
            <div class="node-list">
        `;
        for (const _networkInfo of params.data.network_list) {
            let name = _networkInfo.network_ip + ':' + _networkInfo.network_port;
            ret += `<div class="node-more-item">
                <span>${name}</span>
            </div>`;
        }
        ret += `</div></div>`;
        return ret;
    };

    /**
     * 获取图片标签的样式
     * @param image
     * @param height
     * @param width
     * @returns {Object}
     */
    const getImageRichStyle = (image, height = 70, width = 80) => {
        return {
            backgroundColor: {
                image,
            },
            width,
            height,
            align: 'center',
            verticalAlign: 'center',
        };
    };

    /**
     * 获取带边框标签的样式
     * @param color
     * @returns {Object}
     */
    const getLabelBorderRichStyle = (color = '#0FBF98') => {
        return {
            padding: 2,
            borderWidth: 1,
            borderRadius: 2,
            borderColor: color,
            fontSize: 12,
            color,
            align: 'center',
            verticalAlign: 'center',
            backgroundColor: `#fff`,
        };
    };

    /**
     * 获取标签的样式
     * @returns {Object}
     */
    const getLabelRichStyle = () => {
        return {
            fontSize: 12,
            color: '#333',
            align: 'left',
            width: 50,
            verticalAlign: 'center',
        };
    };

    /**
     * 获取资源池的节点信息
     * @param nodePoolName
     * @param nodePoolX
     * @param nodePoolY
     * @returns {Object}
     */
    const getNodePoolNode = (nodePoolName, nodePoolX, nodePoolY) => {
        return {
            name: nodePoolName,
            id: 'node_pool_' + nodePoolName,
            value: [nodePoolX, nodePoolY],
            label: {
                rich: {
                    img: getImageRichStyle('img/platform/cluster/cluster-host.svg'),
                    name: getLabelBorderRichStyle(),
                },
                lineHeight: 18,
                formatter: getNodePoolLabelFormatter,
            },
            tooltip: {
                show: true,
                formatter: '{b}',
            }
        };
    };

    /**
     * 获取计算节点的节点信息
     * @param nodeIp
     * @param nodeX
     * @param nodeY
     * @param moreNode
     * @param nodeList
     * @param nodeIndex
     * @param onlineFlag
     * @param nodeImage
     * @param color
     * @returns {Object}
     */
    const getNodeNode = (nodeIp, nodeX, nodeY, moreNode, nodeList, nodeIndex, onlineFlag, nodeImage, color) => {
        return {
            node_ip: nodeIp,
            id: 'node_' + nodeIp,
            value: [nodeX, nodeY],
            more_node: moreNode,
            online_flag: onlineFlag,
            node_list: moreNode ? nodeList.slice(nodeIndex, nodeList.length) : [],
            label: {
                rich: {
                    img: getImageRichStyle(nodeImage, 70, onlineFlag ? 44 : 53),
                    node_ip: getLabelBorderRichStyle(color),
                    more_node: {
                        fontSize: 12,
                        color,
                        align: 'center',
                    },
                },
                lineHeight: 18,
                formatter: getNodeLabelFormatter,
            },
            tooltip: {
                show: true,
                formatter: getNodeTooltipFormatter,
            }
        };
    };

    /**
     * 获取存储设备和网络设备的图片节点
     * @param x
     * @param y
     * @param image
     * @returns {Object}
     */
    const getStorageAndNetworkImageNode = (x, y, image) => {
        return {
            id: 'storage_network_image_' + x + '_' + y,
            value: [x, y],
            label: {
                rich: {
                    img: getImageRichStyle(image, 30, 30),
                },
                lineHeight: 18,
                formatter: () => {
                    return `{img|}`;
                },
            },
        };
    };

    /**
     * 获取存储设备的标签节点
     * @param storageList
     * @param storageX
     * @param storageY
     * @returns {Object}
     */
    const getStorageLabelNode = (storageList, storageX, storageY) => {
        return {
            id: 'storage_label_' + storageX + '_' + storageY,
            storage_list: storageList,
            value: [storageX + 100, storageY],
            label: {
                rich: {
                    storage_name: getLabelRichStyle(),
                },
                lineHeight: 18,
                formatter: getStorageLabelFormatter,
            },
            tooltip: {
                show: true,
                formatter: getStorageTooltipFormatter,
            },
        };
    };

    /**
     * 获取网络设备的标签节点
     * @param networkList
     * @param networkX
     * @param networkY
     * @returns {Object}
     */
    const getNetworkLabelNode = (networkList, networkX, networkY) => {
        return {
            id: 'network_label_' + networkX + '_' + networkY,
            network_list: networkList,
            value: [networkX + 100, networkY],
            label: {
                rich: {
                    network_name: getLabelRichStyle(),
                },
                lineHeight: 18,
                formatter: getNetworkLabelFormatter,
            },
            tooltip: {
                show: true,
                formatter: getNetworkTooltipFormatter,
            },
        };
    };

    /**
     * 获取资源池的echarts选项
     * @param lines
     * @param nodes
     * @returns {Object}
     */
    const getNodePoolOptions = (lines, nodes) => {
        return {
            xAxis: {
                min: 0,
                max: 1000,
                show: false,
                type: 'value'
            },
            yAxis: {
                min: 0,
                max: 1000,
                show: false,
                type: 'value'
            },
            tooltip: {
                show: false,
                trigger: 'item',
                enterable: true,
            },
            series: [{
                type: 'lines',
                polyline: true,
                coordinateSystem: 'cartesian2d',
                lineStyle: {
                    color: `#72DDC8`,
                    width: 2,
                },
                effect: {
                    // show: true,
                    trailLength: 0.1,
                    symbol: 'arrow',
                    color: '#3beac9',
                    symbolSize: 8,
                    delay: 0,
                },
                data: lines,
            }, {
                type: 'graph',
                coordinateSystem: 'cartesian2d',
                symbol: 'rect',
                symbolSize: [90, 120],
                // symbolOffset: [0, -100],
                itemStyle: {
                    color: '#00000000'
                },
                label: {
                    show: true,
                },
                emphasis: {
                    disabled: true,
                },
                data: nodes,
            }]
        };
    };

    /**
     * @param {Object} row
     * @param {String} row.node_pool_name
     * @param {Array<Object>} row.node_list
     * @param {String} row.node_list[].node_ip
     * @param {String} row.node_list[].node_name
     * @param {String} row.node_list[].node_uuid
     * @param {Boolean} row.node_list[].online_flag
     * @param {Array<Object>} row.node_list[].network_list
     * @param {String} row.node_list[].network_list[].network_ip
     * @param {String} row.node_list[].network_list[].network_name
     * @param {String} row.node_list[].network_list[].network_uuid
     * @param {Number} row.node_list[].network_list[].network_port
     * @param {Array<Object>} row.node_list[].storage_list
     * @param {String} row.node_list[].storage_list[].storage_name
     * @param {String} row.node_list[].storage_list[].storage_uuid
     * @param {String} row.node_list[].storage_list[].mount_point
     * @param {Number} row.node_list[].network_list[].free_size
     * @param {Number} row.node_list[].network_list[].total_size
     * @param {Number} row.node_list[].network_list[].storage_status
     * @param {Number} row.node_list[].network_list[].storage_type
     */
    const initNodePoolLogical = row => {
        if (nodePoolTopological) {
            nodePoolTopological.dispose();
        }
        nodePoolTopological = echarts.init(
            $('#nodePoolTopological').get(0),
            null,
            {devicePixelRatio: window.devicePixelRatio * 2}
        );
        let nodePoolX = 50;
        let nodePoolY = 900;

        /**
         * @type {Array<Object>}
         */
        let nodes = [getNodePoolNode(row.node_pool_name, nodePoolX, nodePoolY)];
        let lines = [];

        let yPosition = [900, 700, 500, 300, 100];
        let moreFlag = row.node_list.length > 5;
        let nodeCount = moreFlag ? 5 : row.node_list.length;
        for (let nodeIndex in row.node_list) {
            nodeIndex = parseInt(nodeIndex);
            if (nodeIndex >= nodeCount) {
                continue;
            }
            // 构建节点
            let nodeInfo = row.node_list[nodeIndex];
            let nodeX = 400;
            let nodeY = yPosition[nodeIndex];
            let nodeImage = `img/platform/cluster/cluster-node-normal.svg`;
            let color = `#0FBF98`;
            let moreNode = false;
            if (4 === nodeIndex && moreFlag) {
                nodeImage = `img/platform/cluster/cluster-node-more.svg`;
                moreNode = true;
            } else if (!nodeInfo.online_flag) {
                nodeImage = `img/platform/cluster/cluster-node-disable.svg`;
                color = `#999`;
                nodeX += 10;
            }
            nodes.push(getNodeNode(
                nodeInfo.node_ip,
                nodeX,
                nodeY,
                moreNode,
                row.node_list,
                nodeIndex,
                nodeInfo.online_flag,
                nodeImage,
                color
            ));

            if (moreNode || !nodeInfo.online_flag || (!nodeInfo.network_list.length && !nodeInfo.storage_list.length)) {
                // 构建路径
                lines.push({
                    coords: [
                        [nodePoolX, nodePoolY + 30],
                        [nodePoolX, nodeY + 30],
                        [nodeX, nodeY + 30],
                    ],
                });
            } else if (nodeInfo.network_list.length && nodeInfo.storage_list.length) {
                let storageX = 750;
                let storageY = nodeY + 80;
                nodes.push(getStorageAndNetworkImageNode(storageX, storageY, 'img/platform/node/node-storage.svg'));
                nodes.push(getStorageLabelNode(nodeInfo.storage_list, storageX, storageY));

                let networkX = 750;
                let networkY = nodeY - 20;
                nodes.push(getStorageAndNetworkImageNode(networkX, networkY, 'img/platform/node/node-network.svg'));
                nodes.push(getNetworkLabelNode(nodeInfo.network_list, networkX, networkY));

                // 构建路径
                lines.push({
                    coords: [
                        [nodePoolX, nodePoolY + 30],
                        [nodePoolX, nodeY + 30],
                        [nodeX, nodeY + 30],
                        [600, nodeY + 30],
                        [600, storageY],
                        [storageX - 40, storageY]
                    ],
                });
                lines.push({
                    coords: [
                        [nodePoolX, nodePoolY + 30],
                        [nodePoolX, nodeY + 30],
                        [600, nodeY + 30],
                        [600, networkY],
                        [networkX - 40, networkY]
                    ],
                });
            } else if (nodeInfo.storage_list.length) {
                let storageX = 750;
                let storageY = nodeY + 30;
                nodes.push(getStorageAndNetworkImageNode(storageX, storageY, 'img/platform/node/node-storage.svg'));
                nodes.push(getStorageLabelNode(nodeInfo.storage_list, storageX, storageY));

                // 构建路径
                lines.push({
                    coords: [
                        [nodePoolX, nodePoolY + 30],
                        [nodePoolX, storageY],
                        [storageX - 30, storageY],
                    ],
                });
            } else if (nodeInfo.network_list.length) {
                let networkX = 750;
                let networkY = nodeY + 30;
                nodes.push(getStorageAndNetworkImageNode(networkX, networkY, 'img/platform/node/node-network.svg'));
                nodes.push(getNetworkLabelNode(nodeInfo.network_list, networkX, networkY));

                // 构建路径
                lines.push({
                    coords: [
                        [nodePoolX, nodePoolY + 30],
                        [nodePoolX, networkY],
                        [networkX - 30, networkY],
                    ],
                });
            }
        }
        nodePoolTopological.resize();
        nodePoolTopological.setOption(getNodePoolOptions(lines, nodes));
    };

    ////////// 开始-计算节点拓扑图 //////////

    //////////////////// 结束-事件监听 ////////////////////

    const getQueryParams = () => {
        let params = {};
        let search = $('.node-pool-search').val().trim();
        if (search) {
            params.node_pool_name = search;
        }
        return params;
    };

    const initNodePoolTableHeight = () => {
        let toolbarHeight = 57;
        let paginationHeight = 52;
        let alertHeight = 131;
        let tabTitleHeight = 48;
        let navTitleHeight = 46;
        // page-content有40px的内边距
        // portlet-body有10px的内边距
        // tab-content有20px的外边距离
        let otherHeight = 40 + 10 + 20;
        let height = window.innerHeight - toolbarHeight - paginationHeight - alertHeight - tabTitleHeight - navTitleHeight - otherHeight;
        $("#nodePoolList .fixed-table-body").css({
            'height': height,
        });
    };

    const formatterOperate = () => {
        let operate = `
        <div class="btn-group">
            <div class="btn_operation_vicon">
        `;
        // 编辑
        if (CONF.PERMISSION_ARR.includes('p_node_pool_edit')) {
            operate += `
            <a class="edit-node-pool">
                <i class="viconfont vicon-a-Editbianji"></i>
            </a>
            `;
        }
        // 查看拓扑
        operate += `
        <a class="show-node-pool">
            <i class="viconfont vicon-wangluojiedian"></i>
        </a>
        `;
        // 删除
        if (CONF.PERMISSION_ARR.includes('p_node_pool_delete')) {
            operate += `
            <a class="delete-node-pool">
                <i class="viconfont vicon-a-Deleteshanchu1"></i>
            </a>
            `;
        }
        operate += `
            </div>
        </div>
        `;
        return operate;
    };

    /**
     * 初始化节点池按钮操作
     * @returns {Object}
     */
    const initNodePoolBtnOp = () => {
        return {
            'click .edit-node-pool': (e, value, row) => {
                checkOperateAuth(checkAuth([row]), () => {
                    editNodePoolBtn(e, value, row);
                });
            },
            'click .show-node-pool': showNodePoolTopological,
            'click .delete-node-pool': (e, value, row) => {
                checkOperateAuth(checkAuth([row]), () => {
                    showDeleteNodePoolTips([row]);
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

    const nodePoolRowCheck = () => {
        checkEvent('#nodePoolTable', '#deleteNodePool');
    };

    const customNodePoolTool = () => {
        let beforeInput = `<div class="btn-group" style="margin: 0">`;
        if (CONF.PERMISSION_ARR.includes('p_node_pool_delete')) {
            beforeInput += `
            <button type="button" id="deleteNodePool" class="b-btn brr2 mr12 table-toolbar-btn">
                <i class="icon-gray-delete"></i>
            </button>
            `;
        }
        beforeInput += `</div>`;
        let afterInput = `<div class="btn-group" style="margin: 0">`;
        if (CONF.PERMISSION_ARR.includes('p_node_pool_delete')) {
            afterInput += `
            <button class="btn table-toolbar-btn" id="addNodePool" data-toggle="drawer"
                data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false" style="width: auto; height: 34px; border: 0">
                <i class="viconfont vicon-biaogetianjia"></i>
                <span class="pl2">${LANG.UI_PUBLIC_ADD}</span>
            </button>
            `;
        }
        afterInput += `</div>`;
        return {beforeInput, afterInput};
    };

    const getNodePoolTableColumns = () => {
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
            title: LANG.UI_NODE_POOL_TABLE_NAME,
            field: 'node_pool_name',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_POOL_TABLE_NODE,
            field: 'node_list',
            sortable: false,
            width: '20',
            widthUnit: '%',
            /**
             * @param {Array<Object>} value
             * @param {String} value[].node_name
             * @param {String} value[].node_ip
             * @returns {string}
             */
            formatter: value => {
                if (!value.length) {
                    return '--';
                }
                let nodeDes = [];
                for (const node of value) {
                    nodeDes.push(`${node.node_name}(${node.node_ip})`);
                }
                let name = nodeDes.join('<br>');
                let title = nodeDes.join('\n');
                return `<span title="${title}">${name}</span>`;
            },
        }, {
            title: LANG.UI_NODE_POOL_TABLE_UPDATE_TIME,
            field: 'update_time',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_POOL_TABLE_CREATOR,
            field: 'creator',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_POOL_TABLE_REMARK,
            field: 'remark',
            width: '15',
            widthUnit: '%',
            formatter: value => value ? `<span title="${value}">${value}</span>` : '--',
        }, {
            title: LANG.UI_PUBLIC_OPERATION,
            width: '15',
            widthUnit: '%',
            forceHide: true, //隐藏掉导出该列数据
            sortable: false,
            events: initNodePoolBtnOp(),
            clickToSelect: false,
            switchable: false,
            formatter: formatterOperate,
            opButton: true,
        }];
    };

    const getNodePoolTableOption = () => {
        return {
            vin_url: `/api/v1/node_pools`,
            vin_params: getQueryParams,
            vin_method: 'get',
            buttonsToolbar: '#vin_node_pool_toolbar .vin_btnToolbar',
            toolbarId: '#vin_node_pool_toolbar',
            vin_toolbar: '#vin_node_pool_toolbar',
            sortName: 'update_time',
            sortOrder: 'desc',
            // 搜索
            placeholder: LANG.UI_NODE_POOL_TABLE_SEARCH_PLACEHOLDER,
            searchInput: true,
            searchClass: 'node-pool-search',
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
            onResetView: initNodePoolTableHeight,
            onRefresh: () => {
                $("#nodePoolTable").bootstrapTable('hideLoading');
            },
            onPostBody: () => {
                $(".popovers").popover();
                nodePoolRowCheck();
                setPoolRowHeight();
            },
            onCheck: nodePoolRowCheck,
            onUncheck: nodePoolRowCheck,
            onCheckAll: nodePoolRowCheck,
            onUncheckAll: nodePoolRowCheck,
            customTool: customNodePoolTool(),
            columns: getNodePoolTableColumns(),
        };
    };

    const initNodePoolTable = () => {
        $('#nodePoolTable').bootstrapTable('destroy').baseTableConfig().init(getNodePoolTableOption());
    };

    return {
        init: () => {
            initListener();
            initNodePoolTable();
        },
    };
})();

jQuery(document).ready(() => {
    NodePool.init();
});
