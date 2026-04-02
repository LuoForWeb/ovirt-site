var StoragePool = (() => {
    let storageListTree;
    let changePoolRowHeightFlag = false;
    let editRow = null;
    const STORAGE_POOL_TYPE_ENUM = {
        unknown: 0,
        central: 1,
        nas: 2,
        cloud: 3,
    };

    const initListener = () => {
        $('#storagePoolList').on('click', '#deleteStoragePool', clickDeleteStoragePoolBtn) // 删除资源池
            .on('click', '#addStoragePool', clickAddStoragePoolBtn)  // 添加资源池
            .on('click', '.change_height', changePoolRowHeight); // 修改资源池高度
        $('#storagePoolType').on('change', changeStoragePoolType);

        // 名称和备注输入框
        $('#storagePoolName, #storagePoolRemark').on('input change', function () {
            $(this).val($(this).val().replace(/[<>"]/gi, ''));
        });

        // drawer按钮
        $('#modifyStoragePoolDrawer .drawer-footer .cancel').on('click', hideStoragePoolDrawer);
        $('#modifyStoragePoolDrawer .drawer-header .drawer-close').on('click', hideStoragePoolDrawer);
        $('#modifyStoragePoolDrawer .drawer-footer .btn-confirm').on('click', submitStoragePoolDraw);
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

    const hideStoragePoolDrawer = () => {
        $('#modifyStoragePoolDrawer').drawer('hide');
    };

    /**
     * 添加/修改资源池提交按钮
     * @returns {boolean}
     */
    const submitStoragePoolDraw = () => {
        let modifyStoragePoolForm = $('#modifyStoragePoolForm');
        let storagePoolName = $('#storagePoolName').val().trim();
        let storagePoolType = parseInt($('#storagePoolType').val());
        let checkedNodes = storageListTree?.getCheckedNodes(true)?.filter(v => v.eventtype === 'storage');

        $('#modifyStoragePoolDrawer .drawer-body .alert-danger').hide();
        if (!storagePoolName) {
            $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_STORAGE_POOL_NAME_IS_EMPTY);
            return false;
        }

        if (STORAGE_POOL_TYPE_ENUM.unknown === storagePoolType) {
            $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_STORAGE_POOL_TYPE_EMPTY);
            return false;
        }

        if (typeof checkedNodes === 'undefined' || !checkedNodes.length) {
            $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_STORAGE_POOL_LIST_EMPTY);
            return false;
        }

        if (checkedNodes.length < 2) {
            $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger').show();
            $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_STORAGE_POOL_LIST_LEAST_TWO_STORAGE);
            return false;
        }
        if (
            storagePoolType === STORAGE_POOL_TYPE_ENUM.nas ||
            storagePoolType === STORAGE_POOL_TYPE_ENUM.cloud
        ) {
            let commonNodeUuidList = checkedNodes[0].mount_point_list.map(v => v.node_uuid);
            for (const checkedNode of checkedNodes) {
                let nodeUuidList = checkedNode.mount_point_list.map(v => v.node_uuid);
                commonNodeUuidList = commonNodeUuidList.filter(v => nodeUuidList.indexOf(v) !== -1);
            }
            // 共享存储必须要有相同的挂载节点
            if (!commonNodeUuidList.length) {
                $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger').show();
                if (storagePoolType === STORAGE_POOL_TYPE_ENUM.nas) {
                    $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_STORAGE_POOL_NAS_STORAGE_POOL_HAS_NO_COMMON_NODE);
                } else {
                    $('#modifyStoragePoolDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_STORAGE_POOL_CLOUD_STORAGE_POOL_CLOUD_NO_COMMON_NODE);
                }
                return false;
            }
        }
        let reqData = {
            storage_pool_name: storagePoolName,
            remark: $('#storagePoolRemark').val().trim(),
            storage_pool_type: storagePoolType,
            storage_uuid_list: checkedNodes.map(v => v.storage_uuid),
        };
        let url = '/api/v1/storage_pools';
        let method = 'POST';
        if ('edit' === modifyStoragePoolForm.data('type')) {
            url += `/${modifyStoragePoolForm.data('uuid')}`;
            method = 'PUT';
        }
        Metronic.blockUI({target: '#modifyStoragePoolDrawer', animate: true});
        pAjaxRequest(reqData, url, method, res => {
            Metronic.unblockUI('#modifyStoragePoolDrawer');
            if (!res.success) {
                UIToastr.showWarning(modifyStoragePoolForm.data('lang'), res.message);
                return;
            }
            UIToastr.showSuccess(modifyStoragePoolForm.data('lang'), res.message);
            $('#storagePoolTable').bootstrapTable('refresh');
            $('#modifyStoragePoolDrawer').drawer('hide');
            editRow = null;
        });
    };

    /**
     * 删除网络资源池
     */
    const clickDeleteStoragePoolBtn = () => {
        let rows = $('#storagePoolTable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showWarning(LANG.UI_STORAGE_POOL_DELETE, LANG.UI_STORAGE_POOL_DELETE_EMPTY);
            return;
        }

        checkOperateAuth(checkAuth(rows), () => {
            showDeleteStoragePoolTips(rows);
        });
    };

    /**
     * 显示删除网络资源池弹窗
     * @param {Array<Object>} rows
     * @param {String} rows[].storage_pool_name
     * @param {String} rows[].storage_pool_uuid
     */
    const showDeleteStoragePoolTips = (rows) => {
        let title = `
        <div>
            <i class="viconfont vicon-a-Deleteshanchu1"></i>
            <span class="pl2">${LANG.UI_STORAGE_POOL_DELETE}</span>
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
                        <div class="title">${LANG.UI_STORAGE_POOL_DELETE_TIPS}</div>
                        <div class="text">${rows.map(v => v.storage_pool_name).join('、')}</div>
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
                deleteStoragePool(rows.map(v => v.storage_pool_uuid));
            }, 300),
        });
    };

    /**
     * 删除网络资源池
     * @param storagePoolUuidList
     */
    const deleteStoragePool = (storagePoolUuidList) => {
        Metronic.blockUI({target: '#storagePoolDiv', animate: true});
        pAjaxRequest({storage_pool_uuid_list: storagePoolUuidList}, `/api/v1/storage_pools/batch`, 'DELETE', res => {
            Metronic.unblockUI('#storagePoolDiv');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_STORAGE_POOL_DELETE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_STORAGE_POOL_DELETE, res.message);
            $('#storagePoolTable').bootstrapTable('refresh');
        });
    };

    /**
     * 编辑网络资源池
     * @param e
     * @param value
     * @param row
     */
    const editStoragePoolBtn = (e, value, row) => {
        editRow = row;
        $('#modifyStoragePoolDrawer').drawer('show');
        $('#modifyStoragePoolDrawer .drawer-body .alert-danger').hide();
        $('#modifyStoragePoolDrawer .drawer-title i.icon').addClass('vicon-a-Editbianji').removeClass('vicon-biaogetianjia');
        $('#modifyStoragePoolDrawer .drawer-title span.title').html(LANG.UI_STORAGE_POOL_EDIT_TITLE);
        $('#storagePoolName').val(row.storage_pool_name);
        $('#storagePoolRemark').val(row.remark);
        $('#modifyStoragePoolForm').data('type', 'edit')
            .data('uuid', row.storage_pool_uuid)
            .data('lang', LANG.UI_STORAGE_POOL_EDIT_TITLE);
        $('#storagePoolType').val(row.storage_pool_type).trigger('change');
    };

    /**
     * 添加网络资源池
     */
    const clickAddStoragePoolBtn = () => {
        $('#modifyStoragePoolDrawer').drawer('show');
        $('#modifyStoragePoolDrawer .drawer-body .alert-danger').hide();
        $('#modifyStoragePoolDrawer .drawer-title i.icon').removeClass('vicon-a-Editbianji').addClass('vicon-biaogetianjia');
        $('#modifyStoragePoolDrawer .drawer-title span.title').html(LANG.UI_STORAGE_POOL_ADD_TITLE);
        $('#storagePoolName').val('');
        $('#storagePoolRemark').val('');
        $('#modifyStoragePoolForm').data('type', 'add')
            .data('lang', LANG.UI_STORAGE_POOL_ADD_TITLE)
            .removeAttr('data-uuid');
        // 填充默认名称
        Metronic.blockUI({target: '#modifyStoragePoolDrawer', animate: true});
        let reqData = {
            pool_type: 'storage',
            pool_prefix_name: LANG.UI_STORAGE_POOL,
        };
        pAjaxRequest(reqData, `/api/v1/nodes/resource_pool/name`, 'GET', res => {
            Metronic.unblockUI('#modifyStoragePoolDrawer');
            if (!res.success) {
                return;
            }
            $('#storagePoolName').val(res.data.pool_name);
        });
        $('.storageListDiv').hide();
        $('#storagePoolType').val(STORAGE_POOL_TYPE_ENUM.unknown);
    };

    /**
     * 设置资源池高度
     */
    const setPoolRowHeight = () => {
        let rows = $('#storagePoolTable').bootstrapTable('getData');
        if (!rows.length) {
            return;
        }
        if (changePoolRowHeightFlag) {
            $('#storagePoolTable>tbody>tr>td').css({
                'padding-top': '15.25px',
                'padding-bottom': '15.25px'
            });
        } else {
            $('#storagePoolTable>tbody>tr>td').css({
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
            $('#vin_storage_pool_toolbar .change_height i').addClass('icon-auto-height2');
        } else {
            changePoolRowHeightFlag = false;
            $('#vin_storage_pool_toolbar .change_height i').removeClass('icon-auto-height2');
        }
        setPoolRowHeight();
    };

    const changeStoragePoolType = () => {
        let storagePoolType = parseInt($('#storagePoolType').val());
        if (STORAGE_POOL_TYPE_ENUM.unknown === storagePoolType) {
            $('.storageListDiv').hide();
        } else {
            $('.storageListDiv').show();
            if ($('#modifyStoragePoolForm').data('type') === 'edit' && editRow) {
                initStorageListTree(editRow.storage_list.map(v => v.storage_uuid));
            } else {
                initStorageListTree();
            }
        }
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
        if (treeNode.eventtype === 'storage' && treeNode.storage_in_pool_flag) {
            style.color = '#F19F00';
            style['font-weight'] = 'bold';
        }
        return style;
    };

    const getStorageListTreeSetting = () => {
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
                beforeClick: storageListTreeClick,
                onCheck: storageListTreeCheck,
                beforeExpand: storageListTreeExpand
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: setFontCss,
            }
        };
    };

    const storageListTreeClick = (treeId, treeNode) => {
        if ('more' === treeNode.eventtype) {
            let storagePoolType = parseInt($('#storagePoolType').val());
            switch (storagePoolType) {
                case STORAGE_POOL_TYPE_ENUM.central:
                    expandCentralTree(treeNode);
                    break;
                case STORAGE_POOL_TYPE_ENUM.nas:
                case STORAGE_POOL_TYPE_ENUM.cloud:
                    // expandNasAndCLoudTree(treeNode);
                    expandNasAndCLoudTree(treeNode, storagePoolType);
                    break;
                default:
                    break;
            }
        } else {
            storageListTree.checkNode(treeNode, !treeNode.checked, true);
        }
    };

    /**
     * 展开集中式存储节点
     * @param treeNode
     */
    const expandCentralTree = treeNode => {
        Metronic.blockUI({target: '#storageListTree', animate: true});
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
        };
        let excludeStorageType = [
            CONF.BD_STORAGE_TYPE.NFS,
            CONF.BD_STORAGE_TYPE.CIFS,
            CONF.BD_STORAGE_TYPE.CLOUD,
        ];
        pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
            Metronic.unblockUI('#storageListTree');
            if (!res.success) {
                UIToastr.showWarning($('#modifyStoragePoolForm').data('lang'), res.message);
                return;
            }
            let moreFlag = res.data.total !== (res.data.rows.length + parseInt(treeNode.total));
            let nodes = [];
            for (const item of res.data.rows) {
                let nodeNocheckFlag = true;
                for (const storageInfo of item.storage_list) {
                    if (excludeStorageType.includes(storageInfo.storage_type)) {
                        continue;
                    }
                    let chkDisabled = false;
                    let storageInPoolFlag = false;
                    let checked = treeNode.storage_uuid_list.includes(storageInfo.storage_uuid);
                    let name = storageInfo.storage_name + '(' + CONF.STORAGE_TYPE_DES[parseInt(storageInfo.storage_type)]
                        + ', ' + LANG.UI_JOB_TOTAL_SIZE + ': ' + storageCalculateSize(storageInfo.total_size) + ', '
                        + LANG.UI_JOB_FREE_SIZE + ': ' + storageCalculateSize(storageInfo.free_size) + ')';
                    let title = name;
                    if (!treeNode.storage_uuid_list.includes(storageInfo.storage_uuid) && storageInfo.storage_pool_list.length) {  // 未选择的节点禁用勾选
                        chkDisabled = true;
                        storageInPoolFlag = true;
                        title += '(' + LANG.UI_STORAGE_POOL_STORAGE_IN_POOL.replace('%S', storageInfo.storage_pool_list[0].storage_pool_nickname) + ')';
                    } else {
                        nodeNocheckFlag = false;
                    }
                    nodes.push({
                        id: storageInfo.storage_uuid,
                        pId: item.node_uuid,
                        title,
                        name,
                        isParent: false,
                        icon: './img/platform/node/node-storage-tree.svg',
                        eventtype: 'storage',
                        checked,
                        chkDisabled,
                        node_uuid: item.node_uuid,
                        storage_uuid: storageInfo.storage_uuid,
                        storage_uuid_list: treeNode.storage_uuid_list,
                        storage_in_pool_flag: storageInPoolFlag,
                        mount_point_list: [],
                    });
                }

                let checked = treeNode.storage_uuid_list.includes(item.node_uuid);
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
                    chkDisabled: nodeNocheckFlag,
                    node_uuid: item.node_uuid,
                    storage_uuid_list: treeNode.storage_uuid_list,
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
                    storage_uuid_list: treeNode.storage_uuid_list,
                });
            }
            storageListTree.addNodes(treeNode.getParentNode(), nodes);
            storageListTree.removeNode(treeNode);
        });
    };

    /**
     * 展开NAS和云存储节点
     * @param treeNode
     * @param storagePoolType
     */
    const expandNasAndCLoudTree = (treeNode, storagePoolType = 2) => {
        Metronic.blockUI({target: '#storageListTree', animate: true});
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
            storage_pool_type: storagePoolType,
            use_mode: 1,
        };
        pAjaxRequest(reqData, `/api/v1/storages`, 'GET', res => {
            Metronic.unblockUI('#storageListTree');
            if (!res.success) {
                UIToastr.showWarning($('#modifyStoragePoolForm').data('lang'), res.message);
                return;
            }
            let moreFlag = (treeNode.total + res.data.rows.length) !== res.data.total;
            let nodes = [];
            for (const item of res.data.rows) {
                let checked = treeNode.storage_uuid_list.includes(item.storage_uuid);
                let name = item.storage_nickname + '(' + CONF.STORAGE_TYPE_DES[parseInt(item.storage_type)]
                    + ', ' + LANG.UI_JOB_TOTAL_SIZE + ': ' + item.total_size + ', ' + LANG.UI_JOB_FREE_SIZE
                    + ': ' + item.free_size + ')';
                let title = name;
                let chkDisabled = false;
                let storageInPoolFlag = false;
                if (!treeNode.storage_uuid_list.includes(item.storage_uuid) && item.storage_pool_list.length) {  // 未选择的节点禁用勾选
                    chkDisabled = true;
                    storageInPoolFlag = true;
                    title += '(' + LANG.UI_STORAGE_POOL_STORAGE_IN_POOL.replace('%S', item.storage_pool_list[0].storage_pool_nickname) + ')';
                }
                nodes.push({
                    id: item.node_uuid,
                    pId: 'all-nas',
                    title,
                    name,
                    isParent: false,
                    icon: './img/platform/node/node-storage-tree.svg',
                    eventtype: 'storage',
                    checked,
                    chkDisabled,
                    storage_uuid: item.storage_uuid,
                    storage_uuid_list: treeNode.storage_uuid_list,
                    storage_in_pool_flag: storageInPoolFlag,
                    mount_point_list: item.mount_point_list,
                });
            }
            if (moreFlag) {
                nodes.push({
                    id: 'more_node',
                    pId: 'all-nas',
                    title: LANG.UI_NODE_MORE,
                    name: LANG.UI_NODE_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: (treeNode.total + res.data.rows.length),
                    storage_uuid_list: treeNode.storage_uuid_list,
                });
            }
            storageListTree.addNodes(treeNode.getParentNode(), nodes);
            storageListTree.removeNode(treeNode);
        });
    };

    const storageListTreeCheck = (e, treeId, treeNode) => {
        //
    };

    const storageListTreeExpand = (treeId, treeNode) => {
        //
    };

    const initStorageListTree = (storage_uuid_list = []) => {
        if (storageListTree) {
            let node = storageListTree.getNodeByParam('id', 'all-node');
            if (node) {
                storageListTree.removeNode(storageListTree.getNodeByParam('id', 'all-node'));
            }
        }
        let storagePoolType = parseInt($('#storagePoolType').val());
        switch (storagePoolType) {
            case STORAGE_POOL_TYPE_ENUM.central:
                initCentralTree(storage_uuid_list);
                break;
            case STORAGE_POOL_TYPE_ENUM.nas:
            case STORAGE_POOL_TYPE_ENUM.cloud:
                initNasAndCloudTree(storage_uuid_list, storagePoolType);
                break;
            default:
                break;
        }
    };

    /**
     * 初始化集中式存储树
     * @param storage_uuid_list
     */
    const initCentralTree = (storage_uuid_list) => {
        Metronic.blockUI({target: '.storageListDiv', animate: true});
        let reqData = {
            offset: 0,
            limit: 20,
            exclude_storage_type_list: [
                CONF.BD_STORAGE_TYPE.TAPE,  // 资源池不支持磁带
                CONF.BD_STORAGE_TYPE.REMOTE,  // 资源池不支持远程备份存储
                CONF.BD_STORAGE_TYPE.HUAWEICBR,  // 资源池不支持华为CBR
            ],
        };
        let excludeStorageType = [
            CONF.BD_STORAGE_TYPE.NFS,
            CONF.BD_STORAGE_TYPE.CIFS,
            CONF.BD_STORAGE_TYPE.CLOUD,
        ];
        pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
            if (!res.success) {
                Metronic.unblockUI('.storageListDiv');
                UIToastr.showWarning($('#modifyStoragePoolForm').data('lang'), res.message);
                return;
            }

            let moreFlag = res.data.rows.length !== res.data.total;
            /**
             * @type {Object}
             */
            let nodes = [{
                id: 'all-node',
                pId: 0,
                title: LANG.UI_STORAGE_POOL_TYPE1_ALL,
                name: LANG.UI_STORAGE_POOL_TYPE1_ALL,
                isParent: true,
                open: true,
                icon: './img/platform/node/all-node.svg',
                eventtype: 'all_node',
            }];
            for (const item of res.data.rows) {
                let nodeNocheckFlag = true;
                let noStorageFlag = true;
                for (const storageItem of item.storage_list) {
                    if (excludeStorageType.includes(storageItem.storage_type)) {
                        continue;
                    }
                    if (storageItem.lan_free_flag) {  // LAN-FREE存储不显示
                        continue;
                    }
                    noStorageFlag = false;
                    let chkDisabled = false;
                    let storageInPoolFlag = false;
                    let checked = storage_uuid_list.includes(storageItem.storage_uuid);
                    let name = storageItem.storage_name + '(' + CONF.STORAGE_TYPE_DES[parseInt(storageItem.storage_type)]
                        + ', ' + LANG.UI_JOB_TOTAL_SIZE + ': ' + storageCalculateSize(storageItem.total_size) + ', '
                        + LANG.UI_JOB_FREE_SIZE + ': ' + storageCalculateSize(storageItem.free_size) + ')';
                    let title = name;
                    if (!storage_uuid_list.includes(storageItem.storage_uuid) && storageItem.storage_pool_list.length) {  // 未选择的节点禁用勾选
                        chkDisabled = true;
                        storageInPoolFlag = true;
                        title += '(' + LANG.UI_STORAGE_POOL_STORAGE_IN_POOL.replace('%S', storageItem.storage_pool_list[0].storage_pool_nickname) + ')';
                    } else {
                        nodeNocheckFlag = false;
                    }
                    nodes.push({
                        id: storageItem.storage_uuid,
                        pId: item.node_uuid,
                        title,
                        name,
                        isParent: false,
                        icon: './img/platform/node/node-storage-tree.svg',
                        eventtype: 'storage',
                        chkDisabled,
                        checked,
                        node_uuid: item.node_uuid,
                        storage_uuid: storageItem.storage_uuid,
                        storage_uuid_list,
                        storage_in_pool_flag: storageInPoolFlag,
                        mount_point_list: [],
                    });
                }
                if (noStorageFlag) {
                    continue;
                }

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
                    chkDisabled: nodeNocheckFlag,
                    node_uuid: item.node_uuid,
                    storage_uuid_list,
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
                    storage_uuid_list,
                });
            }
            Metronic.unblockUI('.storageListDiv');
            storageListTree = $.fn.zTree.init($('#storageListTree'), getStorageListTreeSetting(), nodes);
        });
    };

    /**
     * 初始化NAS和云存储树
     * @param storage_uuid_list
     * @param storagePoolType
     */
    const initNasAndCloudTree = (storage_uuid_list, storagePoolType = 2) => {
        Metronic.blockUI({target: '.storageListDiv', animate: true});
        let reqData = {
            offset: 0,
            limit: 20,
            storage_pool_type: storagePoolType,
            use_mode: 1,
        };
        pAjaxRequest(reqData, `/api/v1/storages`, 'GET', res => {
            if (!res.success) {
                Metronic.unblockUI('.storageListDiv');
                UIToastr.showWarning($('#modifyStoragePoolForm').data('lang'), res.message);
                return;
            }

            let moreFlag = res.data.rows.length !== res.data.total;
            let allName = LANG.UI_STORAGE_POOL_TYPE2_ALL;
            if (STORAGE_POOL_TYPE_ENUM.cloud === storagePoolType) {
                allName = LANG.UI_STORAGE_POOL_TYPE3_ALL;
            }

            /**
             * @type {Object}
             */
            let nodes = [{
                id: 'all-nas',
                pId: 0,
                title: allName,
                name: allName,
                isParent: true,
                open: true,
                icon: './img/platform/node/all-node.svg',
                eventtype: 'all_nas',
            }];
            for (const item of res.data.rows) {
                let checked = storage_uuid_list.includes(item.storage_uuid);
                let name = item.storage_nickname + '(' + CONF.STORAGE_TYPE_DES[parseInt(item.storage_type)]
                    + ', ' + LANG.UI_JOB_TOTAL_SIZE + ': ' + item.total_size + ', ' + LANG.UI_JOB_FREE_SIZE
                    + ': ' + item.free_size + ')';
                let title = name;
                let chkDisabled = false;
                let storageInPoolFlag = false;
                if (!storage_uuid_list.includes(item.storage_uuid) && item.storage_pool_list.length) {  // 未选择的节点禁用勾选
                    chkDisabled = true;
                    storageInPoolFlag = true;
                    title += '(' + LANG.UI_STORAGE_POOL_STORAGE_IN_POOL.replace('%S', item.storage_pool_list[0].storage_pool_nickname) + ')';
                }
                nodes.push({
                    id: item.node_uuid,
                    pId: 'all-nas',
                    title,
                    name,
                    isParent: false,
                    icon: './img/platform/node/node-storage-tree.svg',
                    eventtype: 'storage',
                    checked,
                    chkDisabled,
                    storage_uuid: item.storage_uuid,
                    storage_uuid_list,
                    storage_in_pool_flag: storageInPoolFlag,
                    mount_point_list: item.mount_point_list,
                });
            }
            if (moreFlag) {
                nodes.push({
                    id: 'more_node',
                    pId: 'all-nas',
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
                    storage_uuid_list,
                });
            }
            Metronic.unblockUI('.storageListDiv');
            storageListTree = $.fn.zTree.init($('#storageListTree'), getStorageListTreeSetting(), nodes);
        });
    };

    ////////// 结束-计算节点树 //////////

    //////////////////// 结束-事件监听 ////////////////////

    const getQueryParams = () => {
        let params = {};
        let search = $('.storage-pool-search').val().trim();
        if (search) {
            params.storage_pool_name = search;
        }
        return params;
    };

    const initStoragePoolTableHeight = () => {
        let toolbarHeight = 57;
        let paginationHeight = 52;
        let tabTitleHeight = 48;
        let navTitleHeight = 46;
        // page-content有40px的内边距
        // portlet-body有10px的内边距
        // tab-content有20px的外边距离
        let otherHeight = 40 + 10 + 20;
        let height = window.innerHeight - toolbarHeight - paginationHeight - tabTitleHeight - navTitleHeight - otherHeight;
        $("#storagePoolList .fixed-table-body").css({
            'height': height,
        });
    };

    const formatterOperate = () => {
        let operate = `
        <div class="btn-group">
            <div class="btn_operation_vicon">
        `;
        // 修改
        if (CONF.PERMISSION_ARR.includes('p_storage_pool_delete')) {
            operate += `
            <a class="edit-storage-pool">
                <i class="viconfont vicon-a-Editbianji"></i>
            </a>
            `;
        }
        // 删除
        if (CONF.PERMISSION_ARR.includes('p_storage_pool_delete')) {
            operate += `
            <a class="delete-storage-pool">
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

    const initStoragePoolBtnOp = () => {
        return {
            'click .edit-storage-pool': (e, value, row) => {
                checkOperateAuth(checkAuth([row]), () => {
                    editStoragePoolBtn(e, value, row);
                });
            },
            'click .delete-storage-pool': (e, value, row) => {
                checkOperateAuth(checkAuth([row]), () => {
                    showDeleteStoragePoolTips([row]);
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

    const storagePoolRowCheck = () => {
        checkEvent('#storagePoolTable', '#deleteStoragePool');
    };

    const customStoragePoolTool = () => {
        let beforeInput = `<div class="btn-group" style="margin: 0">`;
        // 删除
        if (CONF.PERMISSION_ARR.includes('p_storage_pool_delete')) {
            beforeInput += `
            <button type="button" id="deleteStoragePool" class="b-btn brr2 mr12 table-toolbar-btn">
                <i class="icon-gray-delete"></i>
            </button>
            `;
        }
        beforeInput += `</div>`;
        let afterInput = `<div class="btn-group" style="margin: 0">`;
        // 添加
        if (CONF.PERMISSION_ARR.includes('p_storage_pool_add')) {
            afterInput += `
            <button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="addStoragePool" data-toggle="drawer"
                data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false" style="width: auto; height: 34px; border: 0">
                <i class="viconfont vicon-biaogetianjia"></i>
                <span class="pl2">${LANG.UI_PUBLIC_ADD}</span>
            </button>
            `;
        }
        afterInput += `</div>`;
        return {beforeInput, afterInput};
    };

    const getStoragePoolTableColumns = () => {
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
            title: LANG.UI_STORAGE_POOL_TABLE_NAME,
            field: 'storage_pool_name',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_STORAGE_POOL_TABLE_TYPE,
            field: 'storage_pool_type',
            width: '8',
            widthUnit: '%',
            formatter: (value) => {
                let text;
                switch (parseInt(value)) {
                    case 1:
                        text = LANG.UI_STORAGE_POOL_TYPE1;
                        break;
                    case 2:
                        text = LANG.UI_STORAGE_POOL_TYPE2;
                        break;
                    default:
                        text = LANG.UI_STORAGE_POOL_TYPE3;
                        break;
                }
                return `<span title="${text}">${text}</span>`;
            },
        }, {
            title: LANG.UI_STORAGE_POOL_TABLE_STORAGE,
            field: 'storage_list',
            sortable: false,
            width: '20',
            widthUnit: '%',
            /**
             * @param {Array<Object>} value
             * @param {String} value[].storage_name
             * @param {String} value[].storage_uuid
             * @param {Number} value[].storage_type
             * @param {String} value[].storage_type_des
             * @returns {string}
             */
            formatter: value => {
                if (!value.length) {
                    return '--';
                }
                let storageDes = [];
                for (const storage of value) {
                    storageDes.push(`${storage.storage_name}(${storage.storage_type_des})`);
                }
                return `<span title="${storageDes.join('\n')}">${storageDes.join('<br>')}</span>`;
            },
        }, {
            title: LANG.UI_STORAGE_POOL_TABLE_UPDATE_TIME,
            field: 'update_time',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_STORAGE_POOL_TABLE_CREATOR,
            field: 'creator',
            width: '10',
            widthUnit: '%',
        }, {
            title: LANG.UI_STORAGE_POOL_TABLE_REMARK,
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
            events: initStoragePoolBtnOp(),
            clickToSelect: false,
            switchable: false,
            formatter: formatterOperate,
            opButton: true,
        }];
    };

    const getStoragePoolTableOption = () => {
        return {
            vin_url: `/api/v1/storage_pools`,
            vin_params: getQueryParams,
            vin_method: 'get',
            buttonsToolbar: '#vin_storage_pool_toolbar .vin_btnToolbar',
            toolbarId: '#vin_storage_pool_toolbar',
            vin_toolbar: '#vin_storage_pool_toolbar',
            sortName: 'update_time',
            sortOrder: 'desc',
            // 搜索
            placeholder: LANG.UI_STORAGE_POOL_TABLE_SEARCH_PLACEHOLDER,
            searchInput: true,
            searchClass: 'storage-pool-search',
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
            onResetView: initStoragePoolTableHeight,
            onRefresh: () => {
                $("#storagePoolTable").bootstrapTable('hideLoading');
            },
            onPostBody: () => {
                $(".popovers").popover();
                storagePoolRowCheck();
                setPoolRowHeight();
            },
            onCheck: storagePoolRowCheck,
            onUncheck: storagePoolRowCheck,
            onCheckAll: storagePoolRowCheck,
            onUncheckAll: storagePoolRowCheck,
            customTool: customStoragePoolTool(),
            columns: getStoragePoolTableColumns(),
        };
    };

    const initStoragePoolTable = () => {
        $('#storagePoolTable').bootstrapTable('destroy').baseTableConfig().init(getStoragePoolTableOption());
    };

    return {
        init: () => {
            initListener();
            initStoragePoolTable();
        },
    };
})();

jQuery(document).ready(() => {
    StoragePool.init();
});
