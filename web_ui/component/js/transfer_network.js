/**
 * 传输网络组件
 *
 * 依赖组件
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.js"></script>
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.js"></script>
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.js"></script>
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.js"></script>
 *
 */

(() => {
    let cache = {};

    // 传输网络模式枚举
    const TRANSFER_NETWORK_MODE_ENUM = {
        AUTO_MATCH: 1,  // 自动匹配
        CUSTOM_SELECT: 2,  // 自定义选择
    };

    /**
     * @typedef {Object} transferNetworkData
     * @property {string} network_uuid
     * @property {string} network_pool_uuid
     * @property {string} name
     * @property {string} network_ip
     * @property {string} eventtype
     * @property {string} str
     * @property {string} network_port
     * @property {number} transfer_network_mode
     */

    /**
     * @typedef {Object} transferNetworkMode
     * @property {number} AUTO_MATCH
     * @property {number} CUSTOM_SELECT
     */

    /**
     * @param options
     * @returns {transferNetworkData|null|Object|transferNetworkMode}
     */
    $.fn.transferNetwork = function (options = {}) {
        let id = $(this).attr('id');
        if ('object' === typeof options) {
            cache[id] = {
                options: getOptions(options),
                network_tree: null,
                $element: $(`#${id}`),
                select_id: id + '-transfer-network-mode-select',
                $select_element: null,
            };
            if (!cache[id].options.node_uuid) {
                UIToastr.showWarning(LANG.UI_NODE_NETWORK_TRANSFER, LANG.UI_NODE_NETWORK_TRANSFER_INIT_ERROR);
                return null;
            }
            if ($(`#${cache[id].select_id}`).length > 0) {
                $(`#${cache[id].select_id}`).remove();
            }
            cache[id].$element.before(initSelectContent(id));
            cache[id].$select_element = $(`#${cache[id].select_id}`);
            initListener(id);
            cache[id].$element.addClass('network-tree');
            initData(id);
            return cache[id];
        } else if ('string' === typeof options) {
            switch (options) {
                case 'getOptions':
                    return cache[id].options;
                case 'getSelect':
                    return getSelect(id);
                case 'validateSelect':
                    return validateSelect(id);
                case 'transfer_network_mode':
                    return TRANSFER_NETWORK_MODE_ENUM;
                default:
                    return null;
            }
        }
    }

    const getOptions = options => {
        return {
            limit: typeof options.limit === 'undefined' ? 20 : options.limit,
            node_uuid: typeof options.node_uuid === 'undefined' ? '' : options.node_uuid,
            storage_uuid: typeof options.storage_uuid === 'undefined' ? '' : options.storage_uuid,
            network_uuid: typeof options.network_uuid === 'undefined' ? '' : options.network_uuid,
            network_pool_uuid: typeof options.network_pool_uuid === 'undefined' ? '' : options.network_pool_uuid,
            onChange: typeof options.onChange === 'function' ? options.onChange : null,
            remote_flag: typeof options.remote_flag === 'undefined' ? false : options.remote_flag,
            TRANSFER_NETWORK_MODE_ENUM,
        }
    };

    //////////////////// 开始-构建树 ////////////////////

    const loadMoreNetworkPoolNodes = (treeNode, id) => {
        let reqData = {
            offset: treeNode.offset,
            limit: cache[id].options.limit,
            node_uuid: cache[id].options.node_uuid,
        };
        Metronic.blockUI({target: `#${id}`, animate: true});
        pAjaxRequest(reqData, `/api/v1/network_pools`, 'GET', res => {
            Metronic.unblockUI(`#${id}`);
            if (!res.success) {
                return;
            }

            let nodes = res.data.rows.map(row => buildNetworkPoolNode(row, id));
            if (res.data.total !== (res.data.rows.length + treeNode.total)) {
                nodes.push({
                    id: 'more-network-pool',
                    pId: 'all-network-pool',
                    title: LANG.UI_NODE_NETWORK_POOL_LOAD_MORE,
                    name: LANG.UI_NODE_NETWORK_POOL_LOAD_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more_network_pool',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length + treeNode.total,
                });
            }
            cache[id].network_tree.addNodes(treeNode.getParentNode(), nodes);
            cache[id].network_tree.removeNode(treeNode);
        });
    };

    const loadMoreNetworkNodes = (treeNode, id) => {
        let reqData = {
            offset: treeNode.offset,
            limit: cache[id].options.limit,
        };
        Metronic.blockUI({target: `#${id}`, animate: true});
        let url = `/api/v1/nodes/${cache[id].options.node_uuid}/network`;
        pAjaxRequest(reqData, url, 'GET', res => {
            Metronic.unblockUI(`#${id}`);
            if (!res.success) {
                return;
            }

            let nodes = res.data.rows.map(row => buildNetworkNode(row, id));
            if (res.data.total !== (res.data.rows.length + treeNode.total)) {
                nodes.push({
                    id: 'more-network',
                    pId: 'all-network',
                    title: LANG.UI_NODE_NETWORK_LOAD_MORE,
                    name: LANG.UI_NODE_NETWORK_LOAD_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more_network',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length + treeNode.total,
                });
            }
            cache[id].network_tree.addNodes(treeNode.getParentNode(), nodes);
            cache[id].network_tree.removeNode(treeNode);
        });
    };

    const networkClick = (treeId, treeNode, id) => {
        if (treeNode.eventtype === 'more_network_pool') {
            loadMoreNetworkPoolNodes(treeNode, id);
        } else if (treeNode.eventtype === 'more_network') {
            loadMoreNetworkNodes(treeNode, id);
        } else if (treeNode.eventtype === 'network' || treeNode.eventtype === 'network_pool') {
            cache[id].network_tree.checkNode(treeNode, !treeNode.checked, true, true);
        }
    };

    const networkCheck = (e, treeId, treeNode, id) => {
        let checked = treeNode.checked;
        cache[id].network_tree.checkAllNodes(false);
        cache[id].network_tree.checkNode(treeNode, checked);
        if (cache[id].options.onChange !== null) {
            cache[id].options.onChange(treeNode);
        }
    };

    const setFontCss = () => {
        return {
            // 'max-width': '200px',
            'display': 'inline-block',
            'overflow': 'hidden',
            'text-overflow': 'ellipsis',
            color: '#333',
        };
    };

    const getTreeSetting = (id) => {
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
                beforeClick: (treeId, treeNode) => {
                    networkClick(treeId, treeNode, id);
                },
                onCheck: (e, treeId, treeNode) => {
                    networkCheck(e, treeId, treeNode, id);
                },
                onNodeCreated: function(ev, treeId) {
                    $(`#${treeId} [data-toggle="tooltip"]`).tooltip();
                },
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: setFontCss,
            }
        };
    };

    const buildNetworkPoolNode = (row, id) => {
        let checked = false;
        return {
            id: row.network_pool_uuid,
            pId: 'all-network-pool',
            name: row.network_pool_name,
            title: row.network_pool_name,
            isParent: false,
            icon: './img/resource_pool/storage-pool-tree.svg',
            node_uuid: cache[id].options.node_uuid,
            network_uuid: '',
            network_pool_uuid: row.network_pool_uuid,
            network_ip: '',
            network_port: '',
            eventtype: 'network_pool',
            checked,
            network_list: row.network_list,
        };
    };

    const initNetworkPoolTree = id => {
        return new Promise(resolve => {
            let reqData = {
                offset: 0,
                limit: cache[id].options.limit,
                node_uuid: cache[id].options.node_uuid,
            };
            if (cache[id].options.remote_flag) {
                resolve();
                return;
            }
            Metronic.blockUI({target: `#${id}`, animate: true});
            pAjaxRequest(reqData, `/api/v1/network_pools`, 'GET', res => {
                Metronic.unblockUI(`#${id}`);
                if (!res.success) {
                    resolve();
                    return;
                }
                if (!res.data.rows.length) {
                    resolve();
                    return;
                }

                let nodes = [
                    {
                        id: 'all-network-pool',
                        pId: '',
                        name: LANG.UI_NODE_NETWORK_POOL,
                        title: LANG.UI_NODE_NETWORK_POOL,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        icon: './img/resource_pool/all-network-pool-tree.svg',
                        eventtype: 'all_network_pool',
                    },
                    ...res.data.rows.map(row => buildNetworkPoolNode(row, id)),
                ];
                if (res.data.total !== res.data.rows.length) {
                    nodes.push({
                        id: 'more-network-pool',
                        pId: 'all-network-pool',
                        title: LANG.UI_NODE_NETWORK_POOL_LOAD_MORE,
                        name: LANG.UI_NODE_NETWORK_POOL_LOAD_MORE,
                        isParent: false,
                        checked: false,
                        chkDisabled: true,
                        icon: './img/platform/node/node.svg',
                        eventtype: 'more_network_pool',
                        offset: reqData.offset + reqData.limit,
                        limit: reqData.limit,
                        total: res.data.rows.length,
                    });
                }
                cache[id].network_tree.addNodes(null, nodes);
                resolve();
            });
        });
    };

    const buildNetworkNode = (row) => {
        let name = row.network_ip + ':' + row.network_port;
        let checked = false;
        if (row.network_alias) {
            name += '(' + row.network_alias + ')';
        }
        return {
            id: row.network_uuid,
            pId: 'all-network',
            name: name,
            title: name,
            isParent: false,
            icon: './img/platform/node/node-network-tree.svg',
            network_uuid: row.network_uuid,
            network_pool_uuid: '',
            node_uuid: row.node_uuid,
            network_type: row.network_type,
            network_ip: row.network_ip,
            network_port: row.network_port,
            eventtype: 'network',
            checked,
        };
    };

    const setDefaultNetwork = (id) => {
        return new Promise(resolve => {
            let reqData = {
                offset: 0,
                limit: cache[id].options.limit,
            };
            Metronic.blockUI({target: `#${id}`, animate: true});
            let url = `/api/v1/nodes/${cache[id].options.node_uuid}/network`;
            pAjaxRequest(reqData, url, 'GET', res => {
                Metronic.unblockUI(`#${id}`);
                if (!res.success) {
                    resolve();
                    return;
                }
                if (!res.data.rows.length) {
                    resolve();
                    return;
                }

                let nodes = [
                    {
                        id: 'all-network',
                        pId: '',
                        name: LANG.UI_NODE_NETWORK_TRANSFER,
                        title: LANG.UI_NODE_NETWORK_TRANSFER,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        icon: './img/resource_pool/all-network-tree.svg',
                        eventtype: 'all_network',
                    },
                    ...res.data.rows.map(row => buildNetworkNode(row, id)),
                ];
                if (res.data.total !== res.data.rows.length) {
                    nodes.push({
                        id: 'more-network',
                        pId: 'all-network',
                        title: LANG.UI_NODE_NETWORK_LOAD_MORE,
                        name: LANG.UI_NODE_NETWORK_LOAD_MORE,
                        isParent: false,
                        checked: false,
                        chkDisabled: true,
                        icon: './img/platform/node/node.svg',
                        eventtype: 'more_network',
                        offset: reqData.offset + reqData.limit,
                        limit: reqData.limit,
                        total: res.data.rows.length,
                    });
                }
                cache[id].network_tree.addNodes(null, nodes);
                resolve();
            });
        });
    };

    const setRemoteNetwork = (id) => {
        return new Promise(resolve => {
            let reqData = {
                storage_uuid: cache[id].options.storage_uuid,
                node_uuid: cache[id].options.node_uuid,
            };
            Metronic.blockUI({target: `#${id}`, animate: true});
            pAjaxRequest(reqData, `/api/v1/copy/nodes/net/remote`, 'GET', res => {
                Metronic.unblockUI(`#${id}`);
                if (!res.success) {
                    resolve();
                    return;
                }
                if (!res.data.msg.length) {
                    resolve();
                    return;
                }

                /**
                 * @type {Object}
                 */
                let nodes = [
                    {
                        id: 'all-network',
                        pId: '',
                        name: LANG.UI_NODE_NETWORK_TRANSFER,
                        title: LANG.UI_NODE_NETWORK_TRANSFER,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        icon: './img/resource_pool/all-network-tree.svg',
                        eventtype: 'all_network',
                    },
                ];
                for (const index in res.data.msg) {
                    let row = res.data.msg[index]
                    let name = row.ip + ':' + row.port;
                    nodes.push({
                        id: 'remote-storage-' + index,
                        pId: 'all-network',
                        name: name,
                        title: name,
                        isParent: false,
                        icon: './img/platform/node/node-network-tree.svg',
                        network_uuid: '',
                        network_pool_uuid: '',
                        network_ip: row.ip,
                        network_port: row.port,
                        node_uuid: '',
                        network_type: 1,
                        eventtype: 'network',
                        sub_eventtype: 'remote',
                        checked: false,
                    });
                }
                cache[id].network_tree.addNodes(null, nodes);
                resolve();
            });
        });
    };

    const initNetworkTree = id => {
        return new Promise(resolve => {
            if (!cache[id].options.remote_flag) {
                setDefaultNetwork(id).then(resolve);
            } else {
                setRemoteNetwork(id).then(resolve);
            }
        });
    };

    const initData = id => {
        cache[id].network_tree = $.fn.zTree.init(cache[id].$element, getTreeSetting(id), []);
        Metronic.blockUI({target: `#${id}`, animate: true});
        initNetworkPoolTree(id).then(() => {
            initNetworkTree(id).then(() => {
                Metronic.unblockUI(`#${id}`);
                // 设置默认值
                if (cache[id].options.network_pool_uuid) {
                    let networkPoolNode = cache[id].network_tree.getNodeByParam('network_pool_uuid', cache[id].options.network_pool_uuid);
                    if (networkPoolNode) {
                        cache[id].network_tree.checkNode(networkPoolNode, true, true, true);
                        cache[id].$select_element.val(TRANSFER_NETWORK_MODE_ENUM.CUSTOM_SELECT).trigger('change');
                        return;
                    }
                } else if (cache[id].options.network_uuid) {
                    let networkNode = cache[id].network_tree.getNodeByParam('network_uuid', cache[id].options.network_uuid);
                    if (networkNode) {
                        cache[id].network_tree.checkNode(networkNode, true, true, true);
                        cache[id].$select_element.val(TRANSFER_NETWORK_MODE_ENUM.CUSTOM_SELECT).trigger('change');
                        return;
                    }
                }

                // 如果没有勾选默认的网络，那么默认勾选排序第一的网络
                let networkNodeList = cache[id].network_tree.getNodesByParam('eventtype', 'network');
                if (networkNodeList.length) {
                    cache[id].network_tree.checkNode(networkNodeList[0], true, true, true);
                }
            });
        });
    };

    /**
     * 初始化事件
     * @param id
     */
    const initListener = id => {
        cache[id].$select_element.on('change', () => {
            let transferNetworkMode = parseInt(cache[id].$select_element.val());
            if (transferNetworkMode === TRANSFER_NETWORK_MODE_ENUM.AUTO_MATCH) {
                cache[id].$element.hide();
            } else {
                cache[id].$element.show();
            }
        }).trigger('change');
    };

    /**
     * 初始化选择器
     * @param id
     */
    const initSelectContent = id => {
        return `
        <select class="form-control select2me" id="${cache[id].select_id}">
            <option value="${TRANSFER_NETWORK_MODE_ENUM.AUTO_MATCH}">
                ${LANG.UI_NODE_NETWORK_MODE_AUTO_MATCH}
            </option>
            <option value="${TRANSFER_NETWORK_MODE_ENUM.CUSTOM_SELECT}">
                ${LANG.UI_NODE_NETWORK_MODE_CUSTOM_SELECT}
            </option>
        </select>
        `;
    };

    //////////////////// 结束-构建树 ////////////////////

    //////////////////// 开始-自定义函数 ////////////////////

    const validateSelect = id => {
        return getSelect(id) !== false;
    };

    const getSelect = id => {
        let transferNetworkMode = parseInt(cache[id].$select_element.val());
        if (transferNetworkMode === TRANSFER_NETWORK_MODE_ENUM.AUTO_MATCH) {
            return {
                network_uuid: '',
                network_pool_uuid: '',
                network_ip: '',
                network_port: '',
                name: '',
                eventtype: '',
                str: cache[id].$select_element.find('option:selected').text().trim(),
                transfer_network_mode: transferNetworkMode,
            };
        }
        let checkNetworks = cache[id].network_tree.getCheckedNodes(true);
        if (!checkNetworks.length) {
            UIToastr.showWarning(LANG.UI_NODE_NETWORK_TRANSFER, LANG.UI_NODE_NETWORK_TRANSFER_EMPTY);
            return false;
        }
        return {
            network_uuid: checkNetworks[0].network_uuid,
            network_pool_uuid: checkNetworks[0].network_pool_uuid,
            network_ip: checkNetworks[0].network_ip,
            network_port: checkNetworks[0].network_port,
            name: checkNetworks[0].name,
            eventtype: checkNetworks[0].eventtype,
            str: cache[id].$select_element.find('option:selected').text().trim() + ', ' + checkNetworks[0].name,
            transfer_network_mode: transferNetworkMode,
        };
    };

    //////////////////// 结束-自定义函数 ////////////////////
})();