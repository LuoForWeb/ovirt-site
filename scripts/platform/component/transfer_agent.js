/**
 * 传输代理组件
 *
 * 依赖组件
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
 *
 */

(() => {
    let cache = {};

    $.fn.transferAgent = function (options = {}) {
        let id = $(this).attr('id');
        if ('object' === typeof options) {
            cache[id] = {
                options: getOptions(options),
                agent_tree: null,
                element: $(`#${id}`),
                no_agent_tips_id: `${id}-no-agent-tips`,
            };
            if ($(`#${cache[id].no_agent_tips_id}`).length) {
                $(`#${cache[id].no_agent_tips_id}`).remove();
            }
            cache[id].element.before(getNoAgentTips(id));
            $(`#${cache[id].no_agent_tips_id}`).hide();
            cache[id].element.addClass('agent-tree');
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
                case 'isInit':
                    return typeof cache[id] !== 'undefined';
                default:
                    return null;
            }
        }
    }

    const getOptions = options => {
        return {
            limit: typeof options.limit === 'undefined' ? 20 : options.limit,
            agent_uuid: typeof options.agent_uuid === 'undefined' ? '' : options.agent_uuid,
            agent_pool_uuid: typeof options.agent_pool_uuid === 'undefined' ? '' : options.agent_pool_uuid,
        };
    };

    /**
     * 获取没有代理的提示
     * @param id
     */
    const getNoAgentTips = id => {
        return `
        <div class="form-group m0" id="${cache[id].no_agent_tips_id}">
            <div class="alert alert-block alert-info fade in m0">
                <button type="button" class="close" data-dismiss="alert"></button>
                <ul class="alert-ul">
                    <strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
                    <li>${LANG.UI_APPLIANCE_NO_AGENT_TIPS}</li>
                </ul>
            </div>
        </div>
        `;
    };

    //////////////////// 开始-构建树 ////////////////////

    const loadMoreAgentPoolNodes = (treeNode, id) => {
        let reqData = {
            offset: treeNode.offset,
            limit: cache[id].options.limit,
        };
        Metronic.blockUI({target: `#${id}`, animate: true});
        pAjaxRequest(reqData, `/api/v1/agent_pools`, 'GET', res => {
            Metronic.unblockUI(`#${id}`);
            if (!res.success) {
                return;
            }

            let nodes = res.data.rows.map(row => buildAgentPoolNode(row, id));
            if (res.data.total !== (res.data.rows.length + treeNode.total)) {
                nodes.push({
                    id: 'more-agent-pool',
                    pId: 'all-agent-pool',
                    title: LANG.UI_AGENT_POOL_LOAD_MORE,
                    name: LANG.UI_AGENT_POOL_LOAD_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more_agent_pool',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length + treeNode.total,
                });
            }
            cache[id].agent_tree.addNodes(treeNode.getParentNode(), nodes);
            cache[id].agent_tree.removeNode(treeNode);
        });
    };

    const loadMoreAgentNodes = (treeNode, id) => {
        let reqData = {
            offset: treeNode.offset,
            limit: cache[id].options.limit,
            transfer_agent_flag: 1,
        };
        Metronic.blockUI({target: `#${id}`, animate: true});
        pAjaxRequest(reqData, `/api/v1/agents`, 'GET', res => {
            Metronic.unblockUI(`#${id}`);
            if (!res.success) {
                return;
            }

            let nodes = res.data.rows.map(row => buildAgentNode(row, id));
            if (res.data.total !== (res.data.rows.length + treeNode.total)) {
                nodes.push({
                    id: 'more-agent',
                    pId: 'all-agent',
                    title: LANG.UI_APPLIANCE_LOAD_MORE,
                    name: LANG.UI_APPLIANCE_LOAD_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more_agent',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length + treeNode.total,
                });
            }
            cache[id].agent_tree.addNodes(treeNode.getParentNode(), nodes);
            cache[id].agent_tree.removeNode(treeNode);
        });
    };

    const agentClick = (treeId, treeNode, id) => {
        if (treeNode.eventtype === 'more_agent_pool') {
            loadMoreAgentPoolNodes(treeNode, id);
        } else if (treeNode.eventtype === 'more_agent') {
            loadMoreAgentNodes(treeNode, id);
        } else if (treeNode.eventtype === 'agent' || treeNode.eventtype === 'agent_pool') {
            cache[id].agent_tree.checkNode(treeNode, !treeNode.checked, true, true);
        }
    };

    const agentCheck = (e, treeId, treeNode, id) => {
        let checked = treeNode.checked;
        cache[id].agent_tree.checkAllNodes(false);
        cache[id].agent_tree.checkNode(treeNode, checked);
    };

    const setFontCss = (treeId, treeNode) => {
        let style = {
            // 'max-width': '200px',
            'display': 'inline-block',
            'overflow': 'hidden',
            'text-overflow': 'ellipsis',
            color: '#333',
        };

        if (treeNode.eventtype === 'agent' && !treeNode.online_status) {
            style.color = '#999';
        }

        return style;
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
                    agentClick(treeId, treeNode, id);
                },
                onCheck: (e, treeId, treeNode) => {
                    agentCheck(e, treeId, treeNode, id);
                },
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: setFontCss,
            }
        };
    };

    const buildAgentPoolNode = (row, id) => {
        return {
            id: row.agent_pool_uuid,
            pId: 'all-agent-pool',
            name: row.agent_pool_name,
            title: row.agent_pool_name,
            isParent: false,
            icon: './img/resource_pool/storage-pool-tree.svg',
            agent_pool_uuid: row.agent_pool_uuid,
            agent_uuid: '',
            eventtype: 'agent_pool',
            checked: false,
            agent_list: row.agent_list,
        };
    };

    const initAgentPoolTree = id => {
        return new Promise(resolve => {
            let reqData = {
                offset: 0,
                limit: cache[id].options.limit,
            };
            Metronic.blockUI({target: `#${id}`, animate: true});
            pAjaxRequest(reqData, `/api/v1/agent_pools`, 'GET', res => {
                Metronic.unblockUI(`#${id}`);
                if (!res.success) {
                    resolve(false);
                    return;
                }
                if (!res.data.rows.length) {
                    resolve(false);
                    return;
                }

                let nodes = [
                    {
                        id: 'all-agent-pool',
                        pId: '',
                        name: LANG.UI_AGENT_POOL,
                        title: LANG.UI_AGENT_POOL,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        icon: './img/resource_pool/all-transfer-agent-pool-tree.svg',
                        eventtype: 'all_agent_pool',
                    },
                    ...res.data.rows.map(row => buildAgentPoolNode(row, id)),
                ];
                if (res.data.total !== res.data.rows.length) {
                    nodes.push({
                        id: 'more-agent-pool',
                        pId: 'all-agent-pool',
                        title: LANG.UI_AGENT_POOL_LOAD_MORE,
                        name: LANG.UI_AGENT_POOL_LOAD_MORE,
                        isParent: false,
                        checked: false,
                        chkDisabled: true,
                        icon: './img/platform/node/node.svg',
                        eventtype: 'more_agent_pool',
                        offset: reqData.offset + reqData.limit,
                        limit: reqData.limit,
                        total: res.data.rows.length,
                    });
                }
                cache[id].agent_tree.addNodes(null, nodes);
                resolve(true);
            });
        });
    };

    const buildAgentNode = (row, id) => {
        let name = row.alias + '(' + row.agent_ip + ')';
        let chkDisabled = false;
        if (!row.online_status) {
            name = '(' + LANG.UI_VISUAL_OFF_LINE + ')' + name;
            chkDisabled = true;
        }
        return {
            id: row.agent_uuid,
            pId: 'all-agent',
            name: name,
            title: name,
            isParent: false,
            icon: './img/resource_pool/node-tree.svg',
            agent_uuid: row.agent_uuid,
            agent_pool_uuid: '',
            online_status: row.online_status,
            chkDisabled,
            checked: false,
            eventtype: 'agent',
            agent_pool_name: row.agent_pool_name,
        };
    };

    const initAgentTree = id => {
        return new Promise(resolve => {
            let reqData = {
                offset: 0,
                limit: cache[id].options.limit,
                transfer_agent_flag: 1,
            };
            Metronic.blockUI({target: `#${id}`, animate: true});
            pAjaxRequest(reqData, `/api/v1/agents`, 'GET', res => {
                Metronic.unblockUI(`#${id}`);
                if (!res.success) {
                    resolve(false);
                    return;
                }
                if (!res.data.rows.length) {
                    resolve(false);
                    return;
                }

                let nodes = [
                    {
                        id: 'all-agent',
                        pId: '',
                        name: LANG.UI_APPLIANCE,
                        title: LANG.UI_APPLIANCE,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        icon: './img/resource_pool/all-transfer-agent-tree.svg',
                        eventtype: 'all_agent',
                    },
                    ...res.data.rows.map(row => buildAgentNode(row, id)),
                ];
                if (res.data.total !== res.data.rows.length) {
                    nodes.push({
                        id: 'more-agent',
                        pId: 'all-agent',
                        title: LANG.UI_APPLIANCE_LOAD_MORE,
                        name: LANG.UI_APPLIANCE_LOAD_MORE,
                        isParent: false,
                        checked: false,
                        chkDisabled: true,
                        icon: './img/platform/node/node.svg',
                        eventtype: 'more_agent',
                        offset: reqData.offset + reqData.limit,
                        limit: reqData.limit,
                        total: res.data.rows.length,
                    });
                }
                cache[id].agent_tree.addNodes(null, nodes);
                resolve(true);
            });
        });
    };

    /**
     * 选择默认的代理
     */
    const checkDefaultAgent = id => {
        // 设置默认值
        if (cache[id].options.agent_pool_uuid) {
            let agentPoolNode = cache[id].agent_tree.getNodeByParam('agent_pool_uuid', cache[id].options.agent_pool_uuid);
            cache[id].agent_tree.checkNode(agentPoolNode, true, true, true);
            return;
        } else if (cache[id].options.agent_uuid) {
            let agentNode = cache[id].agent_tree.getNodeByParam('agent_uuid', cache[id].options.agent_uuid);
            cache[id].agent_tree.checkNode(agentNode, true, true, true);
            return;
        }

        // 如果没有勾选默认的网络，那么默认勾选排序第一的代理
        let agentNodeList = cache[id].agent_tree.getNodesByParam('eventtype', 'agent');
        for (const agentNode of agentNodeList) {
            cache[id].agent_tree.checkNode(agentNode, true, true, true);
            break;
        }
    };

    const initData = id => {
        cache[id].agent_tree = $.fn.zTree.init(cache[id].element, getTreeSetting(id), []);
        Metronic.blockUI({target: `#${id}`, animate: true});
        initAgentPoolTree(id).then((hasAgentPoolFlag) => {
            initAgentTree(id).then((hasAgentFlag) => {
                Metronic.unblockUI(`#${id}`);
                if (hasAgentPoolFlag || hasAgentFlag) {
                    checkDefaultAgent(id);
                } else {
                    $(`#${cache[id].no_agent_tips_id}`).show();
                    cache[id].element.hide();
                }
            });
        });
    };

    //////////////////// 结束-构建树 ////////////////////

    //////////////////// 开始-自定义函数 ////////////////////

    const validateSelect = id => {
        return getSelect(id) !== false;
    };

    const getSelect = id => {
        let checkAgents = cache[id].agent_tree.getCheckedNodes(true);
        if (!checkAgents.length) {
            return false;
        }
        return checkAgents[0];
    };

    //////////////////// 结束-自定义函数 ////////////////////
})();
