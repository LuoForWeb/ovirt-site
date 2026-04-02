var AgentPool = (() => {
    let agentListTree;
    let changePoolRowHeightFlag = false;

    const initListener = () => {
        $('#agentPoolList').on('click', '#deleteAgentPool', clickDeleteAgentPoolBtn) // 删除资源池
            .on('click', '#addAgentPool', clickAddAgentPoolBtn)  // 添加资源池
            .on('click', '.change_height', changePoolRowHeight); // 修改资源池高度

        // 名称和备注输入框
        $('#agentPoolName, #agentPoolRemark').on('input change', function () {
            $(this).val($(this).val().replace(/[<>"]/gi, ''));
        });

        // drawer按钮
        $('#modifyAgentPoolDrawer .drawer-footer .cancel').on('click', hideAgentPoolDrawer);
        $('#modifyAgentPoolDrawer .drawer-header .drawer-close').on('click', hideAgentPoolDrawer);
        $('#modifyAgentPoolDrawer .drawer-footer .btn-confirm').on('click', submitAgentPoolDraw);
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

    const hideAgentPoolDrawer = () => {
        $('#modifyAgentPoolDrawer').drawer('hide');
    };

    /**
     * 添加/修改资源池提交按钮
     * @returns {boolean}
     */
    const submitAgentPoolDraw = () => {
        let modifyAgentPoolForm = $('#modifyAgentPoolForm');
        let agentPoolName = $('#agentPoolName').val().trim();
        let checkedNodes = agentListTree?.getCheckedNodes(true)?.filter(v => v.eventtype === 'agent');

        $('#modifyAgentPoolDrawer .drawer-body .alert-danger').hide();
        if (!agentPoolName) {
            $('#modifyAgentPoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyAgentPoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_AGENT_POOL_NAME_IS_EMPTY);
            return false;
        }

        if (typeof checkedNodes === 'undefined' || !checkedNodes.length) {
            $('#modifyAgentPoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyAgentPoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_AGENT_POOL_LIST_EMPTY);
            return false;
        }

        if (checkedNodes.length < 2) {
            $('#modifyAgentPoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyAgentPoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_AGENT_POOL_LIST_LEAST_TWO_NODE);
            return false;
        }

        let reqData = {
            agent_pool_name: agentPoolName,
            remark: $('#agentPoolRemark').val().trim(),
            agent_uuid_list: checkedNodes.map(v => v.agent_uuid),
        };
        let url = '/api/v1/agent_pools';
        let method = 'POST';
        if ('edit' === modifyAgentPoolForm.data('type')) {
            url += `/${modifyAgentPoolForm.data('uuid')}`;
            method = 'PUT';
        }
        Metronic.blockUI({target: '#modifyAgentPoolDrawer', animate: true});
        pAjaxRequest(reqData, url, method, res => {
            Metronic.unblockUI('#modifyAgentPoolDrawer');
            if (!res.success) {
                UIToastr.showWarning(modifyAgentPoolForm.data('lang'), res.message);
                return;
            }
            UIToastr.showSuccess(modifyAgentPoolForm.data('lang'), res.message);
            $('#agentPoolTable').bootstrapTable('refresh');
            $('#modifyAgentPoolDrawer').drawer('hide');
        });
    };

    /**
     * 删除传输代理资源池
     */
    const clickDeleteAgentPoolBtn = () => {
        let rows = $('#agentPoolTable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showWarning(LANG.UI_AGENT_POOL_DELETE, LANG.UI_AGENT_POOL_DELETE_EMPTY);
            return;
        }

        checkOperateAuth(checkAuth(rows), () => {
            showDeleteAgentPoolTips(rows);
        });
    };

    /**
     * 显示删除传输代理资源池弹窗
     * @param {Array<Object>} rows
     * @param {String} rows[].agent_pool_name
     * @param {String} rows[].agent_pool_uuid
     */
    const showDeleteAgentPoolTips = (rows) => {
        let title = `
        <div>
            <i class="viconfont vicon-a-Deleteshanchu1"></i>
            <span class="pl2">${LANG.UI_AGENT_POOL_DELETE}</span>
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
                        <div class="title">${LANG.UI_AGENT_POOL_DELETE_TIPS}</div>
                        <div class="text">${rows.map(v => v.agent_pool_name).join('、')}</div>
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
                deleteAgentPool(rows.map(v => v.agent_pool_uuid));
            }, 300),
        });
    };

    /**
     * 删除传输代理资源池
     * @param agentPoolUuidList
     */
    const deleteAgentPool = (agentPoolUuidList) => {
        Metronic.blockUI({target: '.agent-pool-table-container', animate: true});
        pAjaxRequest({agent_pool_uuid_list: agentPoolUuidList}, `/api/v1/agent_pools/batch`, 'DELETE', res => {
            Metronic.unblockUI('.agent-pool-table-container');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_AGENT_POOL_DELETE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_AGENT_POOL_DELETE, res.message);
            $('#agentPoolTable').bootstrapTable('refresh');
        });
    };

    /**
     * 编辑传输代理资源池
     * @param e
     * @param value
     * @param row
     */
    const editAgentPoolBtn = (e, value, row) => {
        $('#modifyAgentPoolDrawer').drawer('show');
        $('#modifyAgentPoolDrawer .drawer-body .alert-danger').hide();
        $('#modifyAgentPoolDrawer .drawer-title i.icon').addClass('vicon-a-Editbianji').removeClass('vicon-biaogetianjia');
        $('#modifyAgentPoolDrawer .drawer-title span.title').html(LANG.UI_AGENT_POOL_EDIT_TITLE);
        $('#agentPoolName').val(row.agent_pool_name);
        $('#agentPoolRemark').val(row.remark);
        $('#modifyAgentPoolForm').data('type', 'edit')
            .data('uuid', row.agent_pool_uuid)
            .data('lang', LANG.UI_AGENT_POOL_EDIT_TITLE);
        initAgentListTree(row.agent_list.map(v => v.agent_uuid));
    };

    /**
     * 添加传输代理资源池
     */
    const clickAddAgentPoolBtn = () => {
        $('#modifyAgentPoolDrawer').drawer('show');
        $('#modifyAgentPoolDrawer .drawer-body .alert-danger').hide();
        $('#modifyAgentPoolDrawer .drawer-title i.icon').removeClass('vicon-a-Editbianji').addClass('vicon-biaogetianjia');
        $('#modifyAgentPoolDrawer .drawer-title span.title').html(LANG.UI_AGENT_POOL_ADD_TITLE);
        $('#agentPoolName').val('');
        $('#agentPoolRemark').val('');
        $('#modifyAgentPoolForm').data('type', 'add')
            .data('lang', LANG.UI_AGENT_POOL_ADD_TITLE)
            .removeAttr('data-uuid');
        // 填充默认名称
        Metronic.blockUI({target: '#modifyAgentPoolDrawer', animate: true});
        let reqData = {
            pool_type: 'agent',
            pool_prefix_name: LANG.UI_AGENT_POOL,
        };
        pAjaxRequest(reqData, `/api/v1/nodes/resource_pool/name`, 'GET', res => {
            Metronic.unblockUI('#modifyAgentPoolDrawer');
            if (!res.success) {
                return;
            }
            $('#agentPoolName').val(res.data.pool_name);
        });
        initAgentListTree();
    };

    /**
     * 设置资源池高度
     */
    const setPoolRowHeight = () => {
        let rows = $('#agentPoolTable').bootstrapTable('getData');
        if (!rows.length) {
            return;
        }
        if (changePoolRowHeightFlag) {
            $('#agentPoolTable>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            });
        } else {
            $('#agentPoolTable>tbody>tr>td').css({
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
            $('#vin_agent_pool_toolbar .change_height i').addClass('icon-auto-height2');
        } else {
            changePoolRowHeightFlag = false;
            $('#vin_agent_pool_toolbar .change_height i').removeClass('icon-auto-height2');
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
        if (treeNode.eventtype === 'agent' && !treeNode.online_status) {
            style.color = '#999';
        }
        return style;
    };

    const getAgentListTreeSetting = () => {
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
                beforeClick: agentListTreeClick,
                onCheck: agentListTreeCheck,
                beforeExpand: agentListTreeExpand
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: setFontCss,
            }
        };
    };

    const loadMoreAgentNodes = treeNode => {
        Metronic.blockUI({target: '#agentListTree', animate: true});
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
            transfer_agent_flag: 1,
        };
        pAjaxRequest(reqData, `/api/v1/agents`, 'GET', res => {
            Metronic.unblockUI('#agentListTree');
            if (!res.success) {
                UIToastr.showWarning($('#modifyAgentPoolForm').data('lang'), res.message);
                return;
            }
            let moreFlag = res.data.total !== (res.data.rows.length + parseInt(treeNode.total));
            let nodes = [];
            for (const item of res.data.rows) {
                let name = item.alias + '(' + item.agent_ip + ')';
                if (!item.online_status) {
                    name = `(${LANG.UI_VISUAL_OFF_LINE})${name}`;
                }
                let checked = treeNode.agent_uuid_list.includes(item.agent_uuid);
                nodes.push({
                    id: item.agent_uuid,
                    pId: 'all-agent',
                    title: name,
                    name: name,
                    isParent: false,
                    open: false,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'agent',
                    online_status: item.online_status,
                    checked,
                    agent_uuid: item.agent_uuid,
                    agent_uuid_list: treeNode.agent_uuid_list,
                });
            }
            if (moreFlag) {
                nodes.push({
                    id: 'more_node',
                    pId: 'all-agent',
                    title: LANG.UI_NODE_MORE,
                    name: LANG.UI_NODE_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: treeNode.total + res.data.rows.length,
                    agent_uuid_list: treeNode.agent_uuid_list,
                });
            }
            agentListTree.addNodes(treeNode.getParentNode(), nodes);
            agentListTree.removeNode(treeNode);
        });
    };

    const agentListTreeClick = (treeId, treeNode) => {
        if ('more' === treeNode.eventtype) {
            loadMoreAgentNodes(treeNode);
        } else {
            agentListTree.checkNode(treeNode, !treeNode.checked, true);
        }
    };

    const agentListTreeCheck = (e, treeId, treeNode) => {
        //
    };

    const agentListTreeExpand = (treeId, treeNode) => {
        //
    };

    const initAgentListTree = (agent_uuid_list = []) => {
        if (agentListTree) {
            let node = agentListTree.getNodeByParam('id', 'all-agent');
            if (node) {
                agentListTree.removeNode(agentListTree.getNodeByParam('id', 'all-agent'));
            }
        }

        Metronic.blockUI({target: '#agentListTree', animate: true});
        let reqData = {offset: 0, limit: 20, transfer_agent_flag: 1};
        pAjaxRequest(reqData, `/api/v1/agents`, 'GET', res => {
            if (!res.success) {
                Metronic.unblockUI('#agentListTree');
                UIToastr.showWarning($('#modifyAgentPoolForm').data('lang'), res.message);
                return;
            }

            let moreFlag = res.data.rows.length !== res.data.total;
            /**
             * @type {Object}
             */
            let nodes = [{
                id: 'all-agent',
                pId: 0,
                title: LANG.UI_AGENT_POOL_ALL,
                name: LANG.UI_AGENT_POOL_ALL,
                isParent: true,
                open: true,
                icon: './img/platform/node/all-transfer-agent.svg',
                eventtype: 'all_agent',
            }];
            for (const item of res.data.rows) {
                let name = item.alias + '(' + item.agent_ip + ')';
                if (!item.online_status) {
                    name = `(${LANG.UI_VISUAL_OFF_LINE})${name}`;
                }
                let checked = agent_uuid_list.includes(item.agent_uuid);
                nodes.push({
                    id: item.agent_uuid,
                    pId: 'all-agent',
                    title: name,
                    name: name,
                    isParent: false,
                    open: false,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'agent',
                    online_status: item.online_status,
                    checked,
                    agent_uuid: item.agent_uuid,
                    agent_uuid_list,
                });
            }
            if (moreFlag) {
                nodes.push({
                    id: 'more_node',
                    pId: 'all-agent',
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
                    agent_uuid_list,
                });
            }
            Metronic.unblockUI('#agentListTree');
            agentListTree = $.fn.zTree.init($('#agentListTree'), getAgentListTreeSetting(), nodes);
        });
    };

    ////////// 结束-计算节点树 //////////

    //////////////////// 结束-事件监听 ////////////////////

    const getQueryParams = () => {
        let params = {};
        let search = $('.agent-pool-search').val().trim();
        if (search) {
            params.agent_pool_name = search;
        }
        return params;
    };

    // const initAgentPoolTableHeight = () => {
    //     let toolbarHeight = 57;
    //     let paginationHeight = 52;
    //     let tabTitleHeight = 48;
    //     let navTitleHeight = 46;
    //     // page-content有40px的内边距
    //     // portlet-body有10px的内边距
    //     // tab-content有20px的外边距离
    //     let otherHeight = 40 + 10 + 20;
    //     let height = window.innerHeight - toolbarHeight - paginationHeight - tabTitleHeight - navTitleHeight - otherHeight;
    //     $("#agentPoolList .fixed-table-body").css({
    //         'height': height,
    //     });
    // };

    const formatterOperate = () => {
        let operate = `
        <div class="btn-group">
            <div class="btn_operation_vicon">
        `;
        // 编辑
        if (CONF.PERMISSION_ARR.includes('p_agent_pool_edit')) {
            operate += `
            <a class="edit-agent-pool">
                <i class="viconfont vicon-a-Editbianji"></i>
            </a>
            `;
        }
        // 删除
        if (CONF.PERMISSION_ARR.includes('p_agent_pool_delete')) {
            operate += `
            <a class="delete-agent-pool">
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

    const initAgentPoolBtnOp = () => {
        return {
            'click .edit-agent-pool': (e, value, row) => {
                checkOperateAuth(checkAuth([row]), () => {
                    editAgentPoolBtn(e, value, row);
                });
            },
            'click .delete-agent-pool': (e, value, row) => {
                checkOperateAuth(checkAuth([row]), () => {
                    showDeleteAgentPoolTips([row]);
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

    const agentPoolRowCheck = () => {
        checkEvent('#agentPoolTable', '#deleteAgentPool');
    };

    /**
     * 自定义代理资源池工具
     * @returns {{beforeInput: string, afterInput: string}}
     */
    const customAgentPoolTool = () => {
        let beforeInput = `<div class="btn-group" style="margin: 0">`;
        // 删除
        if (CONF.PERMISSION_ARR.includes('p_agent_pool_delete')) {
            beforeInput += `
            <button type="button" id="deleteAgentPool" class="b-btn brr2 mr12 table-toolbar-btn">
                <i class="icon-gray-delete"></i>
            </button>
            `;
        }
        beforeInput += `</div>`;
        let afterInput = `<div class="btn-group" style="margin: 0">`;
        // 添加
        if (CONF.PERMISSION_ARR.includes('p_agent_pool_add')) {
            afterInput += `
            <button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="addAgentPool" data-toggle="drawer"
                data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false" style="width: auto; height: 34px; border: 0">
                <i class="viconfont vicon-biaogetianjia"></i>
                <span class="pl2">${LANG.UI_PUBLIC_ADD}</span>
            </button>
            `;
        }
        afterInput += `</div>`;
        return {beforeInput, afterInput};
    };

    const getAgentPoolTableColumns = () => {
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
            title: LANG.UI_AGENT_POOL_TABLE_NAME,
            field: 'agent_pool_name',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_AGENT_POOL_TABLE_AGENT,
            field: 'agent_list',
            sortable: false,
            width: '20',
            widthUnit: '%',
            /**
             * @param {Array<Object>} value
             * @param {String} value[].agent_name
             * @param {String} value[].agent_uuid
             * @param {String} value[].agent_ip
             * @param {String} value[].hostname
             * @returns {string}
             */
            formatter: value => {
                if (!value.length) {
                    return '--';
                }
                let agentDes = [];
                for (const agent of value) {
                    if (agent.agent_name === agent.agent_ip || !agent.agent_name) {
                        agentDes.push(`${agent.hostname}(${agent.agent_ip})`);
                    } else {
                        agentDes.push(`${agent.agent_name}(${agent.agent_ip})`);
                    }
                }
                return `<span title="${agentDes.join('\n')}">${agentDes.join('<br>')}</span>`;
            },
        }, {
            title: LANG.UI_AGENT_POOL_TABLE_UPDATE_TIME,
            field: 'update_time',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_AGENT_POOL_TABLE_CREATOR,
            field: 'creator',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_AGENT_POOL_TABLE_REMARK,
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
            events: initAgentPoolBtnOp(),
            clickToSelect: false,
            switchable: false,
            formatter: formatterOperate,
            opButton: true,
        }];
    };

    const getAgentPoolTableOption = () => {
        return {
            vin_url: `/api/v1/agent_pools`,
            vin_params: getQueryParams,
            vin_method: 'get',
            buttonsToolbar: '#vin_agent_pool_toolbar .vin_btnToolbar',
            toolbarId: '#vin_agent_pool_toolbar',
            vin_toolbar: '#vin_agent_pool_toolbar',
            sortName: 'update_time',
            sortOrder: 'desc',
            // 搜索
            placeholder: LANG.UI_AGENT_POOL_TABLE_SEARCH_PLACEHOLDER,
            searchInput: true,
            searchClass: 'agent-pool-search',
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
            // onResetView: initAgentPoolTableHeight,
            onRefresh: () => {
                $("#agentPoolTable").bootstrapTable('hideLoading');
            },
            onPostBody: () => {
                $(".popovers").popover();
                agentPoolRowCheck();
                setPoolRowHeight();
            },
            onCheck: agentPoolRowCheck,
            onUncheck: agentPoolRowCheck,
            onCheckAll: agentPoolRowCheck,
            onUncheckAll: agentPoolRowCheck,
            customTool: customAgentPoolTool(),
            columns: getAgentPoolTableColumns(),
        };
    };

    const initAgentPoolTable = () => {
        $('#agentPoolTable').bootstrapTable('destroy').baseTableConfig().init(getAgentPoolTableOption());
    };

    return {
        init: () => {
            initListener();
            initAgentPoolTable();
        },
    };
})();

jQuery(document).ready(() => {
    AgentPool.init();
});
