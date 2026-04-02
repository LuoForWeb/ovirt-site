/**
 * 备份目的地插件
 * 1. 目标存储
 * 2. 目标节点
 *
 * 依赖组件
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
 * <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
 *
 * 逻辑说明
 * 选择目标存储
 * 1. 选择了集中式存储资源池或集中式存储设备，显示关联节点
 * 2. 离线的存储设备不能选
 * 3. 如果资源池的资源全部不可用，那么该资源池也不可用
 * 4. 如果选择了单个共享存储设备，那么计算节点必须要在挂载节点中选择
 * 5. 如果选择了单个共享存储设备，那么选择计算资源池必须是挂载节点的组合（如挂载在ABC三个节点上，那么计算资源池的节点组成为：ABC，AB，AC，BC，不能有其他节点）
 * 6. 如果选择了共享存储资源池，那么计算节点必须在这个资源池里面公共节点中选择（如资源包含存储1和存储2，存储1挂载AB节点，存储2挂载BC节点，那么可选的节点为B）
 * 7. 如果选择了共享存储资源池，那么选择计算资源池必须是这个资源池里面公共节点的组合（如资源包含存储1和存储2，存储1挂载ABC节点，存储2挂载BCD节点，那么计算资源池的节点组成为：BC，不能有其他节点）
 * 8. 如果选择了异地备份存储，那么显示所有计算节点及计算资源池
 *
 * 选择目标节点
 * 1. 离线的计算节点不能选
 */

(() => {
    let cache = {};
    let shareStorageType = [
        CONF.BD_STORAGE_TYPE.NFS,
        CONF.BD_STORAGE_TYPE.CIFS,
        CONF.BD_STORAGE_TYPE.CLOUD,
    ];
    const STORAGE_STATUS_ENUM = {
        ONLINE: 1,
        CREATING: 2,
        OFFLINE: 3,
        UNMOUNT: 4,
    };

    /**
     * 备份目的地组件(目标存储、目标节点)
     * @param {Object|String} options
     * @returns {*|null|boolean}
     * @example - 初始化组件
     * // return {storage_tree: null, node_tree: null, options: {limit: 0}}
     * $('#element').backupTarget({limit: 30});
     * @example - 获取选项
     * // return {limit: 20}
     * $('#element').backupTarget('getOptions');
     * @example - 获取选择的目标存储和目标节点
     * // return [false|{storage_uuid:'', storage_pool_uuid:'', node_uuid:'', node_pool_uuid:'', storage_type: 0}]
     * $('#element').backupTarget('getSelect');
     * @example - 验证选择的节点
     * // return [true|false]
     * $('#element').backupTarget('validateSelect');
     */
    $.fn.backupTarget = function (options = {}) {
        let id = $(this).attr('id');
        if ('object' === typeof options) {
            cache[id] = {
                options: getOptions(options),
                storage_tree: null,
                node_tree: null,
                central_pool_node_tree: null,
            };
            $(`#${id}`).html(initContent(id));
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
                default:
                    return null;
            }
        }
    };

    /**
     * 存储资源池类别枚举
     */
    const STORAGE_POOL_TYPE_ENUM = {
        unknown: 0,
        central: 1,
        nas: 2,
        cloud: 3,
    };

    /**
     * 节点功能枚举
     */
    const NODE_FUNCTION_ENUM = {
        MANAGEMENT: 1,
        CALCULATION: 2,
        MANAGEMENT_AND_CALCULATION: 3,
    };

    const getOptions = options => {
        let allowNodeUuidList = true;
        if (Array.isArray(options['allow_node_uuid_list'])) {
            allowNodeUuidList = options['allow_node_uuid_list'];
        }
        return {
            limit: typeof options['limit'] !== 'undefined' ? options['limit'] : 40,
            node_uuid: typeof options['node_uuid'] !== 'undefined' ? options['node_uuid'] : '',
            node_pool_uuid: typeof options['node_pool_uuid'] !== 'undefined' ? options['node_pool_uuid'] : '',
            storage_uuid: typeof options['storage_uuid'] !== 'undefined' ? options['storage_uuid'] : '',
            storage_pool_uuid: typeof options['storage_pool_uuid'] !== 'undefined' ? options['storage_pool_uuid'] : '',
            // storage_pool_type: typeof options['storage_pool_type'] !== 'undefined' ? parseInt(options['storage_pool_type']) : 0,
            exclude_storage_type_list: Array.isArray(options['exclude_storage_type_list']) ? options['exclude_storage_type_list'] : [
                CONF.BD_STORAGE_TYPE.REMOTE,  // 备份目标默认不支持远程备份存储
                CONF.BD_STORAGE_TYPE.HUAWEICBR,  // 备份目标默认不支持华为CBR
            ],
            /**
             * 允许作为备份目标的节点：
             * 1、本地存储：能勾选允许作为备份目标的节点添加的本地存储
             * 2、本地存储池：本地存储池不能包含其他节点的本地存储
             * 3、共享存储：挂载的节点至少有一个是允许作为备份目标的节点
             * 4、共享存储池：共享存储池里面的共享存储的公共挂载节点至少有一个是允许作为备份目标的节点
             * 5、计算节点：不能选择其他节点
             * 6、计算资源池：计算资源池里面不能由其他节点
             */
            allow_node_uuid_list: allowNodeUuidList,
            not_allow_node_suffix: typeof options['not_allow_node_suffix'] !== 'undefined' ? options['not_allow_node_suffix'] : '',
            share_storage_type: shareStorageType,
            hide_storage_pool_flag: typeof options['hide_storage_pool_flag'] !== 'undefined' ? !!options['hide_storage_pool_flag'] : false,
            hide_node_pool_flag: typeof options['hide_node_pool_flag'] !== 'undefined' ? !!options['hide_node_pool_flag'] : false,
            hide_backup_storage_flag: typeof options['hide_backup_storage_flag'] !== 'undefined' ? !!options['hide_backup_storage_flag'] : false,
            hide_backup_node_flag: typeof options['hide_backup_node_flag'] !== 'undefined' ? !!options['hide_backup_node_flag'] : false,
            after_init_callback: typeof options['after_init_callback'] !== 'undefined' ? options['after_init_callback'] : null,
        };
    };

    const initContent = id => {
        return initStorage(id) + initNode(id) + initTips(id);
    };

    /**
     * 初始化目标存储树
     */
    const initStorage = id => {
        return `
        <div class="form-group" id="${id}StorageWrapper">
            <label class="control-label col-md-3">${LANG.UI_PUBLIC_BACKUP_STORAGE}</label>
            <div class="col-md-9">
                <ul class="ztree storage-tree" id="${id}StorageTree"></ul>
                <div class="alert alert-info mb-0 display-none" id="${id}NoStorageTips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading">
                        <strong>${LANG.UI_PUBLIC_TIPS}</strong>
                    </h4>
                    <ol class="alert-ol">
                        <li>${LANG.UI_PUBLIC_NO_STORAGE_TIPS1}</li>
                        <li>
                            <a class="ajaxify" name="storage_manager" href="./content/platform/storage/storage.php">
                                ${LANG.UI_PUBLIC_NO_STORAGE_TIPS2}
                            </a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
        `;
    };

    /**
     * 初始化目标节点树
     */
    const initNode = id => {
        return `
        <div class="form-group" id="${id}NodeWrapper">
            <label class="control-label col-md-3">${LANG.UI_PUBLIC_BACKUP_NODE}</label>
            <div class="col-md-9">
                <ul class="ztree node-tree" id="${id}NodeTree"></ul>
                <ul class="ztree central-pool-node-tree" id="${id}CentralPoolNodeTree"></ul>
                <div class="alert alert-info mb-0 display-none" id="${id}NoNodeTips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <ul class="alert-ul">
                        <strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
                        <li>${LANG.UI_PUBLIC_NO_CALCULATE_NODE_TIPS}</li>
                    </ul>
                </div>
            </div>
        </div>
        `;
    };

    /**
     * 处死话提示信息
     */
    const initTips = (id) => {
        return `
        <div class="form-group display-none" id="${id}TipsWrapper">
            <div class="col-md-offset-3 col-md-9">
                <div class="alert alert-info">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading">
                        <strong>${LANG.UI_PUBLIC_TIPS}</strong>
                    </h4>
                    <ol class="alert-ol">
                        <li>${LANG.UI_BACKUP_TARGET_TIPS1}</li>
                        <li>${LANG.UI_BACKUP_TARGET_TIPS2}</li>
                        <li>${LANG.UI_BACKUP_TARGET_TIPS3}</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="form-group display-none" id="${id}OnlyNodeTipsWrapper">
            <div class="col-md-offset-3 col-md-9">
                <div class="alert alert-block alert-info fade in">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <ul class="alert-ul">
                        <strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
                        <li>${LANG.UI_BACKUP_TARGET_ONLY_NODE_TIPS}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="form-group display-none" id="${id}OnlyStorageTipsWrapper">
            <div class="col-md-offset-3 col-md-9">
                <div class="alert alert-block alert-info fade in">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <ul class="alert-ul">
                        <strong class="alert-ul-head">${LANG.UI_PUBLIC_TIPS}:</strong>
                        <li>${LANG.UI_BACKUP_TARGET_ONLY_STORAGE_TIPS}</li>
                    </ul>
                </div>
            </div>
        </div>
        `;
    };

    //////////////////// 开始-构建树 ////////////////////

    const initData = id => {
        initTargetStorageTree(id).then((hasStorageFlag) => {
            initNodeTree(id).then((hasNodeFlag) => {
                $(`#${id}TipsWrapper`).hide();
                $(`#${id}OnlyNodeTipsWrapper`).hide();
                $(`#${id}OnlyStorageTipsWrapper`).hide();
                if (hasNodeFlag && hasStorageFlag) {
                    setAllowNode(id);
                    checkDefaultNodes(id);
                    $(`#${id}TipsWrapper`).show();
                } else if (!hasStorageFlag && cache[id].options.hide_backup_storage_flag) {
                    setAllowNode(id);
                    checkDefaultNodes(id);
                    $(`#${id}OnlyNodeTipsWrapper`).show();
                } else if (!hasNodeFlag && cache[id].options.hide_backup_node_flag) {
                    setAllowNode(id);
                    checkDefaultNodes(id);
                    $(`#${id}OnlyStorageTipsWrapper`).show();
                } else if (
                    (!hasStorageFlag || !hasNodeFlag) &&
                    !cache[id].options.hide_backup_storage_flag &&
                    !cache[id].options.hide_backup_node_flag
                ) {
                    $(`#${id}TipsWrapper`).show();
                }
                if (typeof cache[id].options.after_init_callback === 'function') {
                    cache[id].options.after_init_callback();
                }
            });
        });
    };

    //////////////////// 开始-构建目标存储树 ////////////////////

    const getStoragePoolTypeDes = storagePoolType => {
        switch (parseInt(storagePoolType)) {
            case STORAGE_POOL_TYPE_ENUM.central:
                return LANG.UI_STORAGE_POOL_TYPE1;
            case STORAGE_POOL_TYPE_ENUM.nas:
                return LANG.UI_STORAGE_POOL_TYPE2;
            default:
                return LANG.UI_STORAGE_POOL_TYPE3;
        }
    };

    /**
     * 加载更多的存储资源池
     * @param treeNode
     * @param id
     */
    const loadMoreStoragePoolNodes = (treeNode, id) => {
        Metronic.blockUI({
            target: `#${id}StorageTree`,
            animate: true
        });
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
            exclude_storage_type_list: cache[id].options.exclude_storage_type_list,  // 排除存储类型
        };
        pAjaxRequest(reqData, `/api/v1/storage_pools`, 'GET', res => {
            Metronic.unblockUI(`#${id}StorageTree`);
            if (!res.success) {
                return;
            }
            let nodes = [];
            for (const row of res.data.rows) {
                nodes.push(buildStoragePoolTreeNode(row, 'all-storage-pool'));
                for (const storage of row.storage_list) {
                    let storageInfo = storage;
                    storageInfo.total_size_value = storage.total_size;
                    storageInfo.free_size_value = storage.free_size;
                    storageInfo.flag = storage.storage_status;
                    storageInfo.status = storage.node_online_flag;
                    storageInfo.worm = {
                        flag: storage.worm.worm_flag,
                        type: storage.worm.worm_allocate_type,
                        value: storage.worm.worm_allocate_value,
                    };
                    storage.mount_point_list = storage.mount_point_list || [];
                    nodes.push(buildStorageTreeNode(storageInfo, row.storage_pool_uuid));
                }
            }
            if (res.data.total !== (res.data.rows.length + treeNode.total)) {
                nodes.push({
                    id: 'more-storage-pool',
                    pId: 'all-storage-pool',
                    title: LANG.UI_STORAGE_POOL_LOAD_MORE,
                    name: LANG.UI_STORAGE_POOL_LOAD_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more_storage_pool',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length + treeNode.total,
                    exclude_storage_type_list: cache[id].options.exclude_storage_type_list,
                });
            }
            cache[id].storage_tree.addNodes(treeNode.getParentNode(), nodes);
            cache[id].storage_tree.removeNode(treeNode);
        });
    };

    /**
     * @param {Object} row
     * @param {String} row.storage_nickname
     * @param {String} row.storage_type
     * @param {String} row.node_ip
     * @param {String} row.total_size
     * @param {String} row.free_size
     * @param {String} row.total_size_value
     * @param {String} row.free_size_value
     * @param {String} row.storage_pool_uuid
     * @param {String} row.storage_uuid
     * @param {String} row.node_uuid
     * @param {String} row.flag
     * @param {String} row.status
     * @param {Array} row.storage_pool_list
     * @param {Array<Object>} row.mount_point_list
     * @param {Object} row.worm
     * @param {String} row.worm.flag
     * @param {String} row.worm.type
     * @param {String} row.worm.value
     * @param pId
     * @returns {Object}
     */
    const buildStorageTreeNode = (row, pId) => {
        let name = row.storage_nickname;
        let chkDisabled = false;
        let checked = false;
        let storageType = parseInt(row.storage_type);
        let storageFlag = parseInt(row.flag);
        name += '(';
        if (!shareStorageType.includes(storageType)) {
            if (storageType === CONF.BD_STORAGE_TYPE.REMOTE) {
                name += row.config.remote_ip + ', ';
            } else {
                name += row.node_ip + ', ';
            }
            if (storageFlag !== 1) {
                chkDisabled = true;
                switch (storageFlag) {
                    case 2:
                        name = '(' + LANG.UI_STORAGE_STATUS_CREATING + ')' + name;
                        break;
                    case 3:
                        name = '(' + LANG.UI_STORAGE_STATUS_OFFLINE + ')' + name;
                        break;
                    default:
                        name = '(' + LANG.UI_STORAGE_STATUS_UNMOUNT + ')' + name;
                        break;
                }
            }
        } else if (storageType === CONF.BD_STORAGE_TYPE.CLOUD) {  // 云存储
            if (storageFlag !== STORAGE_STATUS_ENUM.ONLINE) {
                chkDisabled = true;
                switch (storageFlag) {
                    case STORAGE_STATUS_ENUM.CREATING:
                        name = '(' + LANG.UI_STORAGE_STATUS_CREATING + ')' + name;
                        break;
                    case STORAGE_STATUS_ENUM.OFFLINE:
                        name = '(' + LANG.UI_STORAGE_STATUS_OFFLINE + ')' + name;
                        break;
                    case STORAGE_STATUS_ENUM.UNMOUNT:
                        name = '(' + LANG.UI_STORAGE_STATUS_UNMOUNT + ')' + name;
                        break;
                    default:
                        name = '(' + LANG.UI_STORAGE_STATUS_OFFLINE + ')' + name;
                        break;
                }
            }
        } else {  // NAS存储判断所有节点挂载状态，只要有一个节点挂载正常即在线
            chkDisabled = true;
            for (const mountPointInfo of row.mount_point_list) {
                if (parseInt(mountPointInfo.mount_status) === 1) {
                    chkDisabled = false;
                    storageFlag = STORAGE_STATUS_ENUM.ONLINE;
                    break;
                }
            }
            if (chkDisabled) {
                name = '(' + LANG.UI_STORAGE_STATUS_OFFLINE + ')' + name;
            }
        }
        name += CONF.STORAGE_TYPE_DES[storageType] + ', ';
        name += LANG.UI_PUBLIC_TOTAL_SIZE2 + ': ' + storageCalculateSize(row.total_size_value) + ', ';
        name += LANG.UI_PUBLIC_FREE_SIZE + ': ' + storageCalculateSize(row.free_size_value) + ')';
        return {
            id: row.storage_uuid,
            pId: pId,
            name: name,
            title: name,
            isParent: false,
            icon: './img/resource_pool/storage-tree.svg',
            eventtype: 'storage',
            checked,
            chkDisabled,
            storage_type: storageType,
            storage_uuid: row.storage_uuid,
            storage_flag: storageFlag,
            online_status: row.status,
            node_uuid: row.node_uuid,
            node_ip: row.node_ip,
            total_size: row.total_size_value,
            free_size: row.free_size_value,
            worm: row.worm,
            mount_point_list: row.mount_point_list,
            allow_node_check: true,
        };
    };

    /**
     * 加载更多的存储设备
     * @param treeNode
     * @param id
     */
    const loadMoreStorageNodes = (treeNode, id) => {
        Metronic.blockUI({
            target: `#${id}StorageTree`,
            animate: true
        });
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
            use_mode: 1,  // 可用存储
            exclude_storage_type_list: cache[id].options.exclude_storage_type_list,  // 排除存储类型
            tape_available_flag: 1,  // 磁带库可用
        };
        pAjaxRequest(reqData, `/api/v1/storages`, 'GET', res => {
            Metronic.unblockUI(`#${id}StorageTree`);
            if (!res.success) {
                return;
            }
            let nodes = [];
            for (const row of res.data.rows) {
                if (row.storage_pool_list.length && !cache[id].options.hide_storage_pool_flag) {
                    continue;
                }
                nodes.push(buildStorageTreeNode(row, 'all-storage'));
            }
            if (res.data.total !== (res.data.rows.length + treeNode.total)) {
                nodes.push({
                    id: 'more-storage',
                    pId: 'all-storage',
                    title: LANG.UI_STORAGE_MORE,
                    name: LANG.UI_STORAGE_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more_storage',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length + treeNode.total,
                    exclude_storage_type_list: cache[id].options.exclude_storage_type_list,
                });
            }
            cache[id].storage_tree.addNodes(treeNode.getParentNode(), nodes);
            cache[id].storage_tree.removeNode(treeNode);
        });
    };

    /**
     * 点击了目标存储
     * @param treeId
     * @param treeNode
     * @param id
     */
    const storageClick = (treeId, treeNode, id) => {
        if (treeNode.eventtype === 'more_storage_pool') {
            loadMoreStoragePoolNodes(treeNode, id);
        } else if (treeNode.eventtype === 'more_storage') {
            loadMoreStorageNodes(treeNode, id);
        } else {
            cache[id].storage_tree.checkNode(treeNode, !treeNode.checked, true, true);
        }
    };

    /**
     * 构建集中式存储资源池的目标节点树
     * @param nodeList
     * @param id
     */
    const initCentralPoolNodeTree = (nodeList, id) => {
        $(`#${id}NodeTree`).hide();
        $(`#${id}CentralPoolNodeTree`).show();

        /**
         * @type {Array<Object>}
         */
        let nodes = [{
            id: 'central-pool-node',
            pId: '',
            title: LANG.UI_NODE_POOL_RELATION_NODE,
            name: LANG.UI_NODE_POOL_RELATION_NODE,
            isParent: true,
            open: true,
            nocheck: true,
            icon: './img/resource_pool/all-node-pool-tree.svg',
            eventtype: 'central_pool_node',
        }];
        for (const nodeInfo of nodeList) {
            nodes.push({
                id: nodeInfo.node_uuid,
                pId: 'central-pool-node',
                title: nodeInfo.name,
                name: nodeInfo.name,
                isParent: false,
                nocheck: true,
                node_uuid: nodeInfo.node_uuid,
                online_flag: nodeInfo.online_flag,
                icon: './img/resource_pool/node-tree.svg',
                eventtype: 'central_node',
            });
        }
        let oldNodes = cache[id].central_pool_node_tree.getNodes();
        for (const oldNode of oldNodes) {
            cache[id].central_pool_node_tree.removeNode(oldNode);
        }
        cache[id].central_pool_node_tree.addNodes(null, nodes);
    };

    /**
     * 选择了集中式存储资源池
     * @param checked
     * @param nodeNodes
     * @param treeNode
     * @param id
     */
    const checkCentralPoolNode = (checked, nodeNodes, treeNode, id) => {
        if (checked) {
            let allNodeUuidList = {};
            for (const childNode of treeNode.children) {
                allNodeUuidList[childNode.node_uuid] = childNode;
            }
            allNodeUuidList = Object.keys(allNodeUuidList);
            let nodeList = {};
            for (const nodeNode of nodeNodes) {
                if (allNodeUuidList.includes(nodeNode.node_uuid)) {
                    nodeList[nodeNode.node_uuid] = nodeNode;
                }
            }
            initCentralPoolNodeTree(Object.values(nodeList), id);
        } else {
            for (const nodeNode of nodeNodes) {
                cache[id].node_tree.checkNode(nodeNode, false);
            }
        }
    };

    /**
     * 选择了远程存储设备
     * @param treeNode
     * @param id
     */
    const checkRemoteStorageNode = (treeNode, id) => {
        $(`#${id}NodeTree`).show();
        $(`#${id}CentralPoolNodeTree`).hide();
    };

    /**
     * 选择了集中式存储设备
     * @param checked
     * @param nodeNodes
     * @param treeNode
     * @param id
     */
    const checkCentralStorageNode = (checked, nodeNodes, treeNode, id) => {
        if (checked) {
            if (parseInt(treeNode.storage_type) === CONF.BD_STORAGE_TYPE.REMOTE) {  // 异地备份系统显示所有计算节点和资源池
                checkRemoteStorageNode(treeNode, id);
            } else {
                let nodeList = {};
                for (const nodeNode of nodeNodes) {
                    if (nodeNode.node_uuid === treeNode.node_uuid) {
                        nodeList[nodeNode.node_uuid] = nodeNode;
                    }
                }
                initCentralPoolNodeTree(Object.values(nodeList), id);
            }
        }
    };

    /**
     * 选择了共享存储设备/资源池
     * @param checked
     * @param nodeNodes
     * @param nodePoolNodes
     * @param treeNode
     * @param id
     * @param commonNodeUuidList
     */
    const checkShareStorageOrPoolNode = (checked, nodeNodes, nodePoolNodes, treeNode, id, commonNodeUuidList) => {
        if (checked) {
            /**
             * 选择云存储、NAS存储设备/资源池
             * 1、计算节点：必须要在公共挂载节点中选择（如资源包含存储1和存储2，存储1挂载ABC节点，存储2挂载BCD节点，那么可选的节点为BC）
             * 2、计算资源池：必须是公共挂载节点的组合（如资源包含存储1和存储2，存储1挂载ABC节点，存储2挂载BCD节点，那么可选的资源池包含节点为BC）
             */
            // 计算节点
            for (const nodeNode of nodeNodes) {
                if (!commonNodeUuidList.includes(nodeNode.node_uuid)) {
                    cache[id].node_tree.setChkDisabled(nodeNode, true);
                }
            }
            // 计算资源池
            for (const nodePoolNode of nodePoolNodes) {
                let nodeUuidList = nodePoolNode.node_list.map(v => v.node_uuid);
                let diffNodeUuidList = nodeUuidList.filter(v => !commonNodeUuidList.includes(v));
                if (diffNodeUuidList.length) {
                    cache[id].node_tree.setChkDisabled(nodePoolNode, true);
                }
            }
        }
    };

    /**
     * 目标存储选中了
     * @param e
     * @param treeId
     * @param treeNode
     * @param id
     */
    const storageCheck = (e, treeId, treeNode, id) => {
        let checked = treeNode.checked;
        cache[id].storage_tree.checkAllNodes(false);
        cache[id].storage_tree.checkNode(treeNode, checked);
        /**
         * 选择目标存储
         * 1. 选择了集中式存储资源池，选择了集中式存储设备，显示关联节点
         * 2. 选择了NAS或云存储资源池，可以选择计算资源池或计算节点
         * 3. 选择了集中式存储设备，自动勾选所在的计算节点
         * 4. 选择了NAS或云存储设备，可以选择计算资源池或计算节点
         * 5. 离线的存储设备不能选
         */
        let nodePoolNodes = cache[id].node_tree.getNodesByParam('eventtype', 'node_pool');
        let nodeNodes = cache[id].node_tree.getNodesByParam('eventtype', 'node');
        nodeNodes = nodeNodes.filter(node => node.online_flag);

        for (const nodePoolNode of nodePoolNodes) {
            if (nodePoolNode.allow_node_check) {
                cache[id].node_tree.setChkDisabled(nodePoolNode, false);
            }
            cache[id].node_tree.checkNode(nodePoolNode, false);
        }
        for (const nodeNode of nodeNodes) {
            if (nodeNode.allow_node_check) {
                cache[id].node_tree.setChkDisabled(nodeNode, false);
            }
            cache[id].node_tree.checkNode(nodeNode, false);
        }

        if (treeNode.eventtype === 'storage_pool') {
            if (parseInt(treeNode.storage_pool_type) === STORAGE_POOL_TYPE_ENUM.central) {
                // 集中式存储资源池选择拥有全部存储节点的计算资源池
                checkCentralPoolNode(checked, nodeNodes, treeNode, id);
            } else {
                /**
                 * 选择云存储、NAS存储资源池
                 * 1、计算节点：如果选择了共享存储资源池，那么计算节点必须在这个资源池里面公共节点中选择（如资源包含存储1和存储2，存储1挂载AB节点，存储2挂载BC节点，那么可选的节点为B）
                 * 2、计算资源池：如果选择了共享存储资源池，那么选择计算资源池必须是这个资源池里面公共节点
                 */
                let commonNodeUuidList = [];
                if (treeNode.children.length) {
                    commonNodeUuidList = treeNode.children[0].mount_point_list.map(row => row.node_uuid);
                }
                for (const child of treeNode.children) {
                    let nodeUuidList = child.mount_point_list.map(v => v.node_uuid);
                    commonNodeUuidList = commonNodeUuidList.filter(v => nodeUuidList.indexOf(v) !== -1);
                }
                checkShareStorageOrPoolNode(checked, nodeNodes, nodePoolNodes, treeNode, id, commonNodeUuidList)
            }
        } else {
            if (!shareStorageType.includes(parseInt(treeNode.storage_type))) {
                checkCentralStorageNode(checked, nodeNodes, treeNode, id);
            } else {
                /**
                 * 选择云存储、NAS存储设备
                 * 1、计算节点：如果选择了单个共享存储设备，那么计算节点必须要在挂载节点中选择
                 * 2、计算资源池：如果选择了单个共享存储设备，那么选择计算资源池必须是挂载节点的组合（如挂载在ABC三个节点上，那么计算资源池的节点组成为：ABC，AB，AC，BC，不能有其他节点）
                 */
                let commonNodeUuidList = treeNode.mount_point_list.map(v => v.node_uuid);
                checkShareStorageOrPoolNode(checked, nodeNodes, nodePoolNodes, treeNode, id, commonNodeUuidList)
            }
        }
    };

    /**
     * @param treeId
     * @param {Object} treeNode
     * @param {Boolean} treeNode.online_status
     * @param {Boolean} treeNode.online_flag
     * @param {String} treeNode.eventtype
     * @param {String} treeNode.storage_flag
     * @param {String} treeNode.storage_type
     * @param {Boolean} treeNode.no_storage_available
     * @param {Boolean} treeNode.allow_node_check
     * @returns {Object}
     */
    const setFontCss = (treeId, treeNode) => {
        let style = {
            // 'max-width': '200px',
            'display': 'inline-block',
            'overflow': 'hidden',
            'text-overflow': 'ellipsis',
            color: '#333',
        };
        if (treeNode.eventtype === 'node') {
            if (typeof treeNode.online_flag !== 'undefined' && !treeNode.online_flag) {
                style.color = '#999';
            }
        }
        if (treeNode.eventtype === 'storage') {
            if (!shareStorageType.includes(parseInt(treeNode.storage_type))) {
                // 节点离线
                if (!treeNode.online_status) {
                    style.color = '#999';
                }
                // 存储离线
                if (parseInt(treeNode.storage_flag) !== 1) {
                    style.color = '#999';
                }
            } else {
                if (treeNode.chkDisabled) {
                    style.color = '#999';
                }
            }
        }
        if (treeNode.eventtype === 'storage_pool') {
            // 节点离线
            if (treeNode.no_storage_available) {
                style.color = '#999';
            }
        }
        if (typeof treeNode.allow_node_check !== 'undefined' && !treeNode.allow_node_check) {
            style.color = '#999';
        }
        return style;
    };

    /**
     * 获取目标存储树的配置
     * @param id
     * @returns {Object}
     */
    const getStorageTreeSetting = id => {
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
                    storageClick(treeId, treeNode, id);
                },
                onCheck: (e, treeId, treeNode) => {
                    $(`#${id}NodeTree`).show();
                    $(`#${id}CentralPoolNodeTree`).hide();
                    storageCheck(e, treeId, treeNode, id);
                },
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: setFontCss,
            }
        };
    };

    /**
     * 构建存储资源池树
     * @param row
     * @param pId
     */
    const buildStoragePoolTreeNode = (row, pId) => {
        let name = row.storage_pool_name + '(' + getStoragePoolTypeDes(row.storage_pool_type) + ')';
        let checked = false;
        let chkDisabled = true;
        let noStorageAvailable = true;
        let storagePoolType = parseInt(row.storage_pool_type);
        if (
            storagePoolType === STORAGE_POOL_TYPE_ENUM.central ||
            storagePoolType === STORAGE_POOL_TYPE_ENUM.cloud  // 云存储也是通过status判断
        ) {
            for (const storage of row.storage_list) {
                if (parseInt(storage.storage_status) === 1) {
                    chkDisabled = false;
                    noStorageAvailable = false;
                }
            }
        } else {
            for (const storage of row.storage_list) {
                let onlineFlag = false;
                for (const mountPointInfo of storage.mount_point_list) {
                    if (parseInt(mountPointInfo.mount_status) === 1) {
                        onlineFlag = true;
                        break;
                    }
                }
                if (onlineFlag) {
                    chkDisabled = false;
                    noStorageAvailable = false;
                    break;
                }
            }
        }
        return {
            id: row.storage_pool_uuid,
            pId,
            name: name,
            title: name,
            isParent: true,
            open: true,
            icon: './img/resource_pool/storage-pool-tree.svg',
            storage_pool_uuid: row.storage_pool_uuid,
            storage_pool_type: row.storage_pool_type,
            eventtype: 'storage_pool',
            checked,
            chkDisabled,
            storage_list: row.storage_list,
            no_storage_available: noStorageAvailable,
            allow_node_check: true,
        };
    };

    /**
     * 添加存储资源池树
     * @param id
     * @returns {Promise<unknown>}
     */
    const addStoragePoolTreeNode = id => {
        return new Promise(resolve => {
            if (cache[id].options.hide_storage_pool_flag) {
                resolve(false);
                return;
            }
            let reqData = {
                offset: 0,
                limit: cache[id].options.limit,
                exclude_storage_type_list: cache[id].options.exclude_storage_type_list,  // 排除存储类型
            };
            pAjaxRequest(reqData, `/api/v1/storage_pools`, 'GET', res => {
                if (!res.success) {
                    resolve(false);
                    return;
                }
                if (!res.data.rows.length) {
                    resolve(false);
                    return;
                }
                /**
                 * @type {Object}
                 */
                let nodes = [
                    {
                        id: 'all-storage-pool',
                        pId: '',
                        name: LANG.UI_STORAGE_POOL,
                        title: LANG.UI_STORAGE_POOL,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        icon: './img/resource_pool/all-storage-pool-tree.svg',
                        eventtype: 'all_storage_pool',
                    },
                ];
                for (const row of res.data.rows) {
                    nodes.push(buildStoragePoolTreeNode(row, 'all-storage-pool'));
                    for (const storage of row.storage_list) {
                        let storageInfo = storage;
                        storageInfo.total_size_value = storage.total_size;
                        storageInfo.free_size_value = storage.free_size;
                        storageInfo.flag = storage.storage_status;
                        storageInfo.status = storage.node_online_flag;
                        storageInfo.worm = {
                            flag: storage.worm.worm_flag,
                            type: storage.worm.worm_allocate_type,
                            value: storage.worm.worm_allocate_value,
                        };
                        storage.mount_point_list = storage.mount_point_list || [];
                        nodes.push(buildStorageTreeNode(storageInfo, row.storage_pool_uuid));
                    }
                }
                if (res.data.total !== res.data.rows.length) {
                    nodes.push({
                        id: 'more-storage-pool',
                        pId: 'all-storage-pool',
                        title: LANG.UI_STORAGE_POOL_LOAD_MORE,
                        name: LANG.UI_STORAGE_POOL_LOAD_MORE,
                        isParent: false,
                        checked: false,
                        chkDisabled: true,
                        icon: './img/platform/node/node.svg',
                        eventtype: 'more_storage_pool',
                        offset: reqData.offset + reqData.limit,
                        limit: reqData.limit,
                        total: res.data.rows.length,
                        exclude_storage_type_list: cache[id].options.exclude_storage_type_list,
                    });
                }
                cache[id].storage_tree.addNodes(null, nodes);
                resolve(true);
            });
        });
    };

    /**
     * 添加存储设备树
     * @param id
     * @returns {Promise<unknown>}
     */
    const addStorageTreeNode = id => {
        return new Promise(resolve => {
            let reqData = {
                offset: 0,
                limit: cache[id].options.limit,
                use_mode: 1,  // 可用存储
                exclude_storage_type_list: cache[id].options.exclude_storage_type_list,  // 排除存储类型
                tape_available_flag: 1,  // 磁带库可用
            };
            pAjaxRequest(reqData, `/api/v1/storages`, 'GET', res => {
                if (!res.success) {
                    resolve(false);
                    return;
                }
                if (!res.data.rows.length) {
                    resolve(false);
                    return;
                }
                /**
                 * @type {Object}
                 */
                let nodes = [
                    {
                        id: 'all-storage',
                        pId: '',
                        name: LANG.UI_STORAGE,
                        title: LANG.UI_STORAGE,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        icon: './img/resource_pool/all-storage-tree.svg',
                        eventtype: 'all_storage',
                    },
                ];
                let hasStorageNodeFlag = false;
                for (const row of res.data.rows) {
                    if (row.storage_pool_list.length && !cache[id].options.hide_storage_pool_flag) {
                        // 获取存储池是否显示出来了
                        let findTreeNodeFlag = false;
                        for (const storagePoolInfo of row.storage_pool_list) {
                            let storagePoolNodes = cache[id].storage_tree.getNodesByParam('id', storagePoolInfo.storage_pool_uuid);
                            if (storagePoolNodes.length) {
                                findTreeNodeFlag = true;
                                break;
                            }
                        }
                        if (findTreeNodeFlag) {
                            continue;
                        }
                    }
                    hasStorageNodeFlag = true;
                    nodes.push(buildStorageTreeNode(row, 'all-storage'));
                }
                if (!hasStorageNodeFlag && res.data.total === res.data.rows.length) {
                    resolve(false);
                    return;
                }
                if (res.data.total !== res.data.rows.length) {
                    nodes.push({
                        id: 'more-storage',
                        pId: 'all-storage',
                        title: LANG.UI_STORAGE_MORE,
                        name: LANG.UI_STORAGE_MORE,
                        isParent: false,
                        checked: false,
                        chkDisabled: true,
                        icon: './img/platform/node/node.svg',
                        eventtype: 'more_storage',
                        offset: reqData.offset + reqData.limit,
                        limit: reqData.limit,
                        total: res.data.rows.length,
                        exclude_storage_type_list: cache[id].options.exclude_storage_type_list,
                    });
                }
                cache[id].storage_tree.addNodes(null, nodes);
                resolve(true);
            });
        });
    };

    /**
     * 初始化目标存储树
     * @param id
     */
    const initTargetStorageTree = id => {
        return new Promise(resolve => {
            cache[id].storage_tree = $.fn.zTree.init($(`#${id}StorageTree`), getStorageTreeSetting(id), []);
            if (cache[id].options.hide_backup_storage_flag) {
                $(`#${id}StorageWrapper`).hide();
                resolve(false);
                return;
            }
            Metronic.blockUI({
                target: `#${id}StorageTree`,
                animate: true
            });
            addStoragePoolTreeNode(id).then((hasStoragePoolFlag) => {
                addStorageTreeNode(id).then((hasStorageFlag) => {
                    Metronic.unblockUI(`#${id}StorageTree`);
                    if (!hasStorageFlag && !hasStoragePoolFlag) {
                        $(`#${id}StorageTree`).hide();
                        $(`#${id}NoStorageTips`).show();
                        resolve(false);
                    } else {
                        $(`#${id}StorageTree`).show();
                        $(`#${id}NoStorageTips`).hide();
                        resolve(true);
                    }
                });
            });
        });
    };

    //////////////////// 结束-构建目标存储树 ////////////////////

    //////////////////// 开始-构建目标节点树 ////////////////////

    /**
     * 加载更多节点池
     * @param treeNode
     * @param id
     */
    const loadMoreNodePoolNodes = (treeNode, id) => {
        Metronic.blockUI({
            target: `#${id}NodeTree`,
            animate: true
        });
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
        };
        pAjaxRequest(reqData, `/api/v1/node_pools`, 'GET', res => {
            Metronic.unblockUI(`#${id}NodeTree`);
            if (!res.success) {
                return;
            }
            let nodes = [];
            for (const row of res.data.rows) {
                nodes.push(buildNodePoolTreeNode(row, id));
                for (const node of row.node_list) {
                    let nodeInfo = node;
                    nodeInfo.host_name = node.node_name;
                    nodeInfo.ip = node.node_ip;
                    nodes.push(buildNodeTreeNode(nodeInfo, row.node_pool_uuid));
                }
            }
            if (res.data.total !== (res.data.rows.length + treeNode.total)) {
                nodes.push({
                    id: 'more-node-pool',
                    pId: 'all-node-pool',
                    title: LANG.UI_NODE_POOL_LOAD_MORE,
                    name: LANG.UI_NODE_POOL_LOAD_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more_node_pool',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length + treeNode.total,
                });
            }
            cache[id].node_tree.addNodes(treeNode.getParentNode(), nodes);
            cache[id].node_tree.removeNode(treeNode);
        });
    };

    /**
     * 构建目标节点
     * @param row
     * @param {Array} row.node_pool_list
     * @param pId
     */
    const buildNodeTreeNode = (row, pId) => {
        let name = row.host_name + '(' + row.ip + ')';
        let chkDisabled = false;
        let checked = false;
        if (!row.online_flag) {
            name = `(${LANG.UI_VISUAL_OFF_LINE})${name}`;
            chkDisabled = true;
        }
        return {
            id: pId + '_' + row.node_uuid,
            pId,
            name: name,
            title: name,
            isParent: false,
            icon: './img/resource_pool/node-tree.svg',
            chkDisabled,
            node_uuid: row.node_uuid,
            node_type: row.node_type,
            eventtype: 'node',
            checked,
            online_flag: row.online_flag,
            allow_node_check: true,
        };
    };

    /**
     * 加载更多节点
     * @param treeNode
     * @param id
     */
    const loadMoreNodeNodes = (treeNode, id) => {
        Metronic.blockUI({
            target: `#${id}NodeTree`,
            animate: true
        });
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
            node_function: NODE_FUNCTION_ENUM.CALCULATION,
        };
        pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
            Metronic.unblockUI(`#${id}NodeTree`);
            if (!res.success) {
                return;
            }
            let nodes = [];
            for (const row of res.data.rows) {
                if (row.node_pool_list.length && !cache[id].options.hide_node_pool_flag) {
                    continue;
                }
                nodes.push(buildNodeTreeNode(row, 'all_node'));
            }
            if (res.data.total !== (res.data.rows.length + treeNode.total)) {
                nodes.push({
                    id: 'more-node',
                    pId: 'all-node',
                    title: LANG.UI_NODE_LOAD_MORE,
                    name: LANG.UI_NODE_LOAD_MORE,
                    isParent: false,
                    checked: false,
                    chkDisabled: true,
                    icon: './img/platform/node/node.svg',
                    eventtype: 'more_node',
                    offset: reqData.offset + reqData.limit,
                    limit: reqData.limit,
                    total: res.data.rows.length + treeNode.total,
                });
            }
            cache[id].node_tree.addNodes(treeNode.getParentNode(), nodes);
            cache[id].node_tree.removeNode(treeNode);
        });
    };

    /**
     * 点击了目标节点
     * @param treeId
     * @param treeNode
     * @param id
     */
    const nodeClick = (treeId, treeNode, id) => {
        if (treeNode.eventtype === 'more_node_pool') {
            loadMoreNodePoolNodes(treeNode, id);
        } else if (treeNode.eventtype === 'more_node') {
            loadMoreNodeNodes(treeNode, id);
        } else {
            cache[id].node_tree.checkNode(treeNode, !treeNode.checked, true, true);
        }
    };

    /**
     * 选中了目标节点树
     * @param e
     * @param treeId
     * @param treeNode
     * @param id
     */
    const nodeCheck = (e, treeId, treeNode, id) => {
        let checked = treeNode.checked;
        cache[id].node_tree.checkAllNodes(false);
        cache[id].node_tree.checkNode(treeNode, checked);
    };

    /**
     * 获取目标节点树的配置
     * @param id
     */
    const getNodeTreeSetting = id => {
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
                    nodeClick(treeId, treeNode, id);
                },
                onCheck: (e, treeId, treeNode) => {
                    nodeCheck(e, treeId, treeNode, id);
                },
                // beforeExpand: (treeId, treeNode) => {
                //     nodeExpand(treeId, treeNode, id);
                // },
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: setFontCss,
            }
        };
    };

    /**
     * 构建节点池树节点
     * @param row
     * @param id
     */
    const buildNodePoolTreeNode = (row, id) => {
        let checked = false;
        let chkDisabled = true;
        let nodeNodeAvailable = true;
        for (const nodeInfo of row.node_list) {
            if (nodeInfo.online_flag) {
                chkDisabled = false;
                nodeNodeAvailable = false;
                break;
            }
        }
        return {
            id: row.node_pool_uuid,
            pId: 'all-node-pool',
            name: row.node_pool_name,
            title: row.node_pool_name,
            isParent: true,
            open: true,
            icon: './img/resource_pool/node-pool-tree.svg',
            node_pool_uuid: row.node_pool_uuid,
            eventtype: 'node_pool',
            chkDisabled,
            checked,
            node_list: row.node_list,
            node_node_available: nodeNodeAvailable,
            allow_node_check: !nodeNodeAvailable,
        };
    };

    /**
     * 添加节点池树
     * @param id
     * @return {Promise<unknown>}
     */
    const addNodePoolTreeNode = id => {
        return new Promise(resolve => {
            if (cache[id].options.hide_node_pool_flag) {
                resolve(false);
                return;
            }
            let reqData = {
                offset: 0,
                limit: cache[id].options.limit,
            };
            pAjaxRequest(reqData, `/api/v1/node_pools`, 'GET', res => {
                if (!res.success) {
                    resolve(false);
                    return;
                }
                if (!res.data.rows.length) {
                    resolve(false);
                    return;
                }
                /**
                 * @type {Object}
                 */
                let nodes = [
                    {
                        id: 'all-node-pool',
                        pId: '',
                        name: LANG.UI_NODE_POOL,
                        title: LANG.UI_NODE_POOL,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        icon: './img/resource_pool/all-node-pool-tree.svg',
                        eventtype: 'all_node_pool',
                    },
                ];
                for (const row of res.data.rows) {
                    nodes.push(buildNodePoolTreeNode(row, id));
                    for (const node of row.node_list) {
                        let nodeInfo = node;
                        nodeInfo.host_name = node.node_name;
                        nodeInfo.ip = node.node_ip;
                        nodes.push(buildNodeTreeNode(nodeInfo, row.node_pool_uuid));
                    }
                }
                if (res.data.total !== res.data.rows.length) {
                    nodes.push({
                        id: 'more-node-pool',
                        pId: 'all-node-pool',
                        title: LANG.UI_NODE_POOL_LOAD_MORE,
                        name: LANG.UI_NODE_POOL_LOAD_MORE,
                        isParent: false,
                        checked: false,
                        chkDisabled: true,
                        icon: './img/platform/node/node.svg',
                        eventtype: 'more_node_pool',
                        offset: reqData.offset + reqData.limit,
                        limit: reqData.limit,
                        total: res.data.rows.length,
                    });
                }
                cache[id].node_tree.addNodes(null, nodes);
                resolve(true);
            });
        });
    };

    /**
     * 添加节点树
     * @param id
     * @return {Promise<unknown>}
     */
    const addNodeTreeNode = id => {
        return new Promise(resolve => {
            let reqData = {
                offset: 0,
                limit: cache[id].options.limit,
                node_function: NODE_FUNCTION_ENUM.CALCULATION,
            };
            pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
                if (!res.success) {
                    resolve(false);
                    return;
                }
                if (!res.data.rows.length) {
                    resolve(false);
                    return;
                }
                /**
                 * @type {Object}
                 */
                let nodes = [
                    {
                        id: 'all-node',
                        pId: '',
                        name: LANG.UI_NODE,
                        title: LANG.UI_NODE,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        icon: './img/resource_pool/all-node-tree.svg',
                        eventtype: 'all_node',
                    },
                ];
                let hasNodeFlag = false;
                for (const row of res.data.rows) {
                    if (row.node_pool_list.length && !cache[id].options.hide_node_pool_flag) {
                        // 获取存储池是否显示出来了
                        let findTreeNodeFlag = false;
                        for (const nodePoolInfo of row.node_pool_list) {
                            let nodePoolNodes = cache[id].node_tree.getNodesByParam('id', nodePoolInfo.node_pool_uuid);
                            if (nodePoolNodes.length) {
                                findTreeNodeFlag = true;
                                break;
                            }
                        }
                        if (findTreeNodeFlag) {
                            continue;
                        }
                    }
                    hasNodeFlag = true;
                    nodes.push(buildNodeTreeNode(row, 'all_node'));
                }
                if (!hasNodeFlag && res.data.total === res.data.rows.length) {
                    resolve(false);
                    return;
                }
                if (res.data.total !== res.data.rows.length) {
                    nodes.push({
                        id: 'more-node',
                        pId: 'all-node',
                        title: LANG.UI_NODE_LOAD_MORE,
                        name: LANG.UI_NODE_LOAD_MORE,
                        isParent: false,
                        checked: false,
                        chkDisabled: true,
                        icon: './img/platform/node/node.svg',
                        eventtype: 'more_node',
                        offset: reqData.offset + reqData.limit,
                        limit: reqData.limit,
                        total: res.data.rows.length,
                    });
                }
                cache[id].node_tree.addNodes(null, nodes);
                resolve(true);
            });
        });
    };

    /**
     * 获取集中式存储的树配置
     * @param id
     */
    const getCentralPoolNodeTreeSetting = id => {
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
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: (treeId, treeNode) => {
                    return {};
                },
            }
        };
    };

    /**
     * 初始化目标节点树
     * @param id
     * @return {Promise<unknown>}
     */
    const initNodeTree = id => {
        return new Promise(resolve => {
            cache[id].node_tree = $.fn.zTree.init($(`#${id}NodeTree`), getNodeTreeSetting(id), []);
            cache[id].central_pool_node_tree = $.fn.zTree.init($(`#${id}CentralPoolNodeTree`), getCentralPoolNodeTreeSetting(id), []);
            if (cache[id].options.hide_backup_node_flag) {
                $(`#${id}NodeWrapper`).hide();
                resolve(false);
                return;
            }
            Metronic.blockUI({
                target: `#${id}NodeTree`,
                animate: true
            });
            addNodePoolTreeNode(id).then((hasNodePoolFlag) => {
                addNodeTreeNode(id).then((hasNodeFLag) => {
                    Metronic.unblockUI(`#${id}NodeTree`);
                    $(`#${id}CentralPoolNodeTree`).hide();
                    if (!hasNodePoolFlag && !hasNodeFLag) {
                        $(`#${id}NodeTree`).hide();
                        $(`#${id}NoNodeTips`).show();
                        resolve(false);
                    } else {
                        $(`#${id}NodeTree`).show();
                        $(`#${id}NoNodeTips`).hide();
                        resolve(true);
                    }
                });
            });
        });
    };

    /**
     * 设置允许选择的节点
     * @param id
     */
    const setAllowNode = id => {
        /**
         * 允许作为备份目标的节点：
         * 1、本地存储：能勾选允许作为备份目标的节点添加的本地存储
         * 2、本地存储池：本地存储池不能包含其他节点的本地存储
         * 3、共享存储：挂载的节点至少有一个是允许作为备份目标的节点
         * 4、共享存储池：共享存储池里面的共享存储的公共挂载节点至少有一个是允许作为备份目标的节点
         * 5、计算节点：不能选择其他节点
         * 6、计算资源池：计算资源池里面不能由其他节点
         */
        if (cache[id].options.allow_node_uuid_list === true) {
            return;
        }
        if (!cache[id].options.hide_backup_storage_flag) {
            let allStoragePoolNodes = cache[id].storage_tree.getNodesByParam('eventtype', 'storage_pool');
            let allStorageNodes = cache[id].storage_tree.getNodesByParam('eventtype', 'storage');
            // 存储设备
            for (const storageNode of allStorageNodes) {
                if (shareStorageType.includes(parseInt(storageNode.storage_type))) {  // 共享存储
                    // 挂载的节点至少有一个是允许作为备份目标的节点
                    let nodeUuidList = storageNode.mount_point_list.map(v => v.node_uuid);
                    let commonUuidList = cache[id].options.allow_node_uuid_list.filter(v => nodeUuidList.includes(v));
                    if (!commonUuidList.length) {
                        storageNode.allow_node_check = false;
                        storageNode.name += '(' + cache[id].options.not_allow_node_suffix + ')';
                        cache[id].storage_tree.updateNode(storageNode);
                        cache[id].storage_tree.setChkDisabled(storageNode, true);
                    }
                } else {
                    // 能勾选允许作为备份目标的节点添加的本地存储
                    if (!cache[id].options.allow_node_uuid_list.includes(storageNode.node_uuid)) {
                        storageNode.allow_node_check = false;
                        storageNode.name += '(' + cache[id].options.not_allow_node_suffix + ')';
                        cache[id].storage_tree.updateNode(storageNode);
                        cache[id].storage_tree.setChkDisabled(storageNode, true);
                    }
                }
            }
            // 存储资源池
            for (const storagePoolNode of allStoragePoolNodes) {
                let childrenAllowNodeCheck = storagePoolNode.children.map(v => !!v.allow_node_check);
                if (childrenAllowNodeCheck.includes(false)) {
                    storagePoolNode.allow_node_check = false;
                    cache[id].storage_tree.updateNode(storagePoolNode);
                    cache[id].storage_tree.setChkDisabled(storagePoolNode, true);
                }
            }
        }
        if (!cache[id].options.hide_backup_node_flag) {
            let allNodePoolNodes = cache[id].node_tree.getNodesByParam('eventtype', 'node_pool');
            let allNodeNodes = cache[id].node_tree.getNodesByParam('eventtype', 'node');
            // 计算节点
            for (const nodeNode of allNodeNodes) {
                if (!cache[id].options.allow_node_uuid_list.includes(nodeNode.node_uuid)) {
                    nodeNode.allow_node_check = false;
                    nodeNode.name += '(' + cache[id].options.not_allow_node_suffix + ')';
                    cache[id].node_tree.updateNode(nodeNode);
                    cache[id].node_tree.setChkDisabled(nodeNode, true);
                }
            }
            // 计算资源池
            for (const nodePoolNode of allNodePoolNodes) {
                let nodeUuidList = nodePoolNode.node_list.map(v => v.node_uuid);
                let diffUuidList = nodeUuidList.filter(v => !cache[id].options.allow_node_uuid_list.includes(v));
                if (diffUuidList.length) {
                    nodePoolNode.allow_node_check = false;
                    cache[id].node_tree.updateNode(nodePoolNode);
                    cache[id].node_tree.setChkDisabled(nodePoolNode, true);
                }
            }
        }
    };

    /**
     * 勾选默认的节点
     */
    const checkDefaultNodes = (id) => {
        // 默认隐藏集中是存储树
        $(`#${id}CentralPoolNodeTree`).hide();
        // 存储勾选
        if (cache[id].options.storage_pool_uuid) {  // 资源池的优先级比节点高
            let storagePoolNode = cache[id].storage_tree.getNodeByParam('storage_pool_uuid', cache[id].options.storage_pool_uuid);
            if (!storagePoolNode.allow_node_check) {
                return;
            }
            cache[id].storage_tree.checkNode(storagePoolNode, true, true, true);
            // if (cache[id].options.storage_pool_type === STORAGE_POOL_TYPE_ENUM.central) { // 集中式存储不需要选择节点和节点池
            //     return;
            // }
        } else if (cache[id].options.storage_uuid) {
            let storageNode = cache[id].storage_tree.getNodeByParam('storage_uuid', cache[id].options.storage_uuid);
            if (!storageNode.allow_node_check) {
                return;
            }
            cache[id].storage_tree.checkNode(storageNode, true, true, true);
        }

        // 节点勾选
        if (cache[id].options.node_pool_uuid) {  // 资源池的优先级比节点高
            let nodePoolNode = cache[id].node_tree.getNodeByParam('node_pool_uuid', cache[id].options.node_pool_uuid);
            if (!nodePoolNode.allow_node_check) {
                return;
            }
            cache[id].node_tree.checkNode(nodePoolNode, true, true, true);
        } else if (cache[id].options.node_uuid) {
            let nodeNode = cache[id].node_tree.getNodeByParam('node_uuid', cache[id].options.node_uuid);
            if (!nodeNode.allow_node_check) {
                return;
            }
            cache[id].node_tree.checkNode(nodeNode, true, true, true);
        }

        // 如果没有勾选默认的存储、节点，那么默认勾选最大的本地存储
        if (
            cache[id].options.storage_pool_uuid ||
            cache[id].options.storage_uuid ||
            cache[id].options.node_pool_uuid ||
            cache[id].options.node_uuid
        ) {
            return;
        }
        let defaultStorageUuid = null;
        let maxStorageSize = 0;
        let storageNodeList = cache[id].storage_tree.getNodesByParam('eventtype', 'storage');
        storageNodeList = storageNodeList.filter(item => {
            if (shareStorageType.includes(parseInt(item.storage_type))) {
                return true;
            }
            if (!item.online_status) {  // 过滤掉离线的
                return false;
            }
            if (!shareStorageType.includes(parseInt(item.storage_type))) {  // 过滤掉集中式存储状态不对的
                if (parseInt(item.storage_flag) !== 1) {
                    return false;
                }
                if (!item.allow_node_check) {
                    return false;
                }
            }
            return true;
        });
        for (const storageNode of storageNodeList) {
            if (shareStorageType.includes(parseInt(storageNode.storage_type))) {
                continue;
            }
            if (maxStorageSize < parseInt(storageNode.free_size)) {
                maxStorageSize = parseInt(storageNode.free_size);
                defaultStorageUuid = storageNode.storage_uuid;
            }
        }
        if (defaultStorageUuid) {
            let storageNode = cache[id].storage_tree.getNodeByParam('storage_uuid', defaultStorageUuid);
            cache[id].storage_tree.checkNode(storageNode, true, true, true);
        } else {
            if (storageNodeList.length) {
                cache[id].storage_tree.checkNode(storageNodeList[0], true, true, true);
            }
        }
        if (cache[id].options.hide_backup_storage_flag) {  // 隐藏存储，需要勾选节点
            let nodeNodeList = cache[id].node_tree.getNodesByParam('eventtype', 'node');
            let checkNode = null;
            for (const nodeNode of nodeNodeList) {
                if (!nodeNode.allow_node_check) {
                    continue;
                }
                checkNode = nodeNode;
                break;
            }
            cache[id].node_tree.checkNode(checkNode, true, true, true);
        }
    };

    //////////////////// 结束-构建目标节点树 ////////////////////
    //////////////////// 结束-构建树 ////////////////////

    //////////////////// 开始-自定义接口 ////////////////////

    /**
     * 验证选择的是否符合要求
     * @param id
     */
    const validateSelect = id => {
        if (cache[id].storage_tree === null || cache[id].node_tree === null) {
            return false;
        }
        return getSelect(id) !== false;
    };

    /**
     * 根据checkTargetNode获取选择的目标节点
     * @param checkTargetNode
     * @param id
     */
    const getSelectTargetNodeByCheckTargetNode = (checkTargetNode, id = null) => {
        let nodeUuid = '';
        let nodePoolUuid = '';
        let nodeText = '--';
        let nodeUuidList = [];
        if (!checkTargetNode.length) {
            UIToastr.showWarning(LANG.UI_PUBLIC_BACKUP_NODE, LANG.UI_STORAGE_TARGET_NODE_TIPS2);
            return false;
        }
        if (checkTargetNode.length === 1) {
            nodeText = checkTargetNode[0].name;
            if (checkTargetNode[0].eventtype === 'node_pool') {
                nodePoolUuid = checkTargetNode[0].node_pool_uuid;
                for (const nodeInfo of checkTargetNode[0].node_list) {
                    nodeUuidList.push(nodeInfo.node_uuid);
                }
            } else if (checkTargetNode[0].eventtype === 'node') {
                nodeUuid = checkTargetNode[0].node_uuid;
                nodeUuidList = [nodeUuid];
            }
        }
        return {
            node_uuid: nodeUuid,
            node_pool_uuid: nodePoolUuid,
            node_uuid_list: nodeUuidList,
            node_text: nodeText,
            storage_uuid: '',
            storage_pool_uuid: '',
            storage_text: '',
            storage_type: '',
            storage_pool_type: '',
            storage_worm_config: {
                flag: false,
                type: 1,
                value: '',
            },
        };
    };

    /**
     * 获取集中式存储选择的目标节点
     * @param checkTargetNode
     * @param storageType
     * @param id
     */
    const getCentralSelectTargetNode = (checkTargetNode, storageType = 0, id = null) => {
        let nodeUuid = '';
        let nodePoolUuid = '';
        let nodeText = '--';
        let nodeUuidList = [];
        if (storageType === CONF.BD_STORAGE_TYPE.REMOTE) {  // 异地备份系统需要勾选计算节点或者计算资源池
            return getSelectTargetNodeByCheckTargetNode(checkTargetNode, id);
        } else {
            let centralNodes = cache[id].central_pool_node_tree.getNodesByParam('eventtype', 'central_node');
            for (const centralNode of centralNodes) {
                nodeUuidList.push(centralNode.node_uuid);
                nodeUuid = centralNode.node_uuid;
                nodeText = centralNode.name;
            }
            return {
                node_uuid: nodeUuid,
                node_pool_uuid: nodePoolUuid,
                node_uuid_list: nodeUuidList,
                node_text: nodeText,
                storage_uuid: '',
                storage_pool_uuid: '',
                storage_text: '',
                storage_type: '',
                storage_pool_type: '',
                storage_worm_config: {
                    flag: false,
                    type: 1,
                    value: '',
                },
            };
        }
    }

    /**
     * 获取选择的目标节点
     * @param checkTargetNode
     * @param storageType
     * @param id
     * @returns {Object|false}
     */
    const getSelectTargetNode = (checkTargetNode, storageType = 0, id = null) => {
        if (!shareStorageType.includes(storageType) && storageType !== 0) {  // 集中式存储
            return getCentralSelectTargetNode(checkTargetNode, storageType, id);
        }

        return getSelectTargetNodeByCheckTargetNode(checkTargetNode, id);
    };

    /**
     * 获取选择的目标存储和节点
     * @param id
     */
    const getSelectTargetStorageAndNode = (id) => {
        let checkTargetStorage = cache[id].storage_tree.getCheckedNodes(true);
        let checkTargetNode = cache[id].node_tree.getCheckedNodes(true);
        if (!checkTargetStorage.length && !checkTargetNode.length) {
            checkTargetStorage = cache[id].storage_tree.getNodesByParam('checked', true);
            checkTargetNode = cache[id].node_tree.getNodesByParam('checked', true);
            if (!checkTargetStorage.length && !checkTargetNode.length) {
                UIToastr.showWarning(LANG.UI_NODE_TARGET, LANG.UI_NODE_TARGET_TIPS1);
                return false;
            }
        }
        if (!checkTargetStorage.length) {
            UIToastr.showWarning(LANG.UI_PUBLIC_BACKUP_STORAGE, LANG.UI_STORAGE_TARGET_STORAGE_TIPS2);
            return false;
        }
        if (checkTargetStorage.length >= 2) {
            UIToastr.showWarning(LANG.UI_PUBLIC_BACKUP_STORAGE, LANG.UI_STORAGE_TARGET_STORAGE_TIPS1);
            return false;
        }
        if (checkTargetNode.length >= 2) {
            UIToastr.showWarning(LANG.UI_PUBLIC_BACKUP_NODE, LANG.UI_STORAGE_TARGET_NODE_TIPS1);
            return false;
        }
        let storageUuid = '';
        let storagePoolUuid = '';
        let storageText = '--';
        let nodeUuid = '';
        let nodePoolUuid = '';
        let nodeUuidList = [];
        let nodeText = '--';
        let storageType = '';
        let storagePoolType = '';
        let storageWormConfig = {
            flag: false,
            type: 1,
            value: '',
        };

        if (checkTargetStorage.length === 1) {
            storageText = checkTargetStorage[0].name;
            if (checkTargetStorage[0].eventtype === 'storage_pool') {
                storagePoolUuid = checkTargetStorage[0].storage_pool_uuid;
                storagePoolType = parseInt(checkTargetStorage[0].storage_pool_type);
                if (STORAGE_POOL_TYPE_ENUM.central !== storagePoolType) {  // 集中式存储资源池不需要指定节点
                    checkTargetNode = cache[id].node_tree.getNodesByParam('checked', true);
                    let targetNode = getSelectTargetNode(checkTargetNode);
                    if (false === targetNode) {
                        return false;
                    }
                    nodePoolUuid = targetNode.node_pool_uuid;
                    nodeUuid = targetNode.node_uuid;
                    nodeUuidList = targetNode.node_uuid_list;
                    nodeText = targetNode.node_text;
                } else {
                    let centralNodes = cache[id].central_pool_node_tree.getNodesByParam('eventtype', 'central_node');
                    for (const centralNode of centralNodes) {
                        nodeUuidList.push(centralNode.node_uuid);
                    }
                }
            } else if (checkTargetStorage[0].eventtype === 'storage') {
                storageUuid = checkTargetStorage[0].storage_uuid;
                storageType = parseInt(checkTargetStorage[0].storage_type);
                let targetNode = getSelectTargetNode(checkTargetNode, storageType, id);
                if (false === targetNode) {
                    return false;
                }
                nodePoolUuid = targetNode.node_pool_uuid;
                nodeUuid = targetNode.node_uuid;
                nodeUuidList = targetNode.node_uuid_list;
                nodeText = targetNode.node_text;
                storageWormConfig = checkTargetStorage[0].worm;
            }
        }

        return {
            storage_uuid: storageUuid,
            storage_pool_uuid: storagePoolUuid,
            storage_text: storageText,
            node_uuid: nodeUuid,
            node_pool_uuid: nodePoolUuid,
            node_uuid_list: nodeUuidList,
            node_text: nodeText,
            storage_type: storageType,
            storage_pool_type: storagePoolType,
            storage_worm_config: storageWormConfig,
        };
    };

    /**
     * 获取选择的目标存储
     * @param id
     */
    const getSelectTargetStorage = (id) => {
        let storageUuid = '';
        let storagePoolUuid = '';
        let storageText = '--';
        let storageType = '';
        let storagePoolType = '';
        let storageWormConfig = {
            flag: false,
            type: 1,
            value: '',
        };
        let checkTargetStorage = cache[id].storage_tree.getCheckedNodes(true);
        if (!checkTargetStorage.length) {
            checkTargetStorage = cache[id].storage_tree.getNodesByParam('checked', true);
        }
        if (!checkTargetStorage.length) {
            UIToastr.showWarning(LANG.UI_NODE_TARGET, LANG.UI_STORAGE_TARGET_NODE_TIPS3);
            return false;
        }
        storageText = checkTargetStorage[0].name;
        if (checkTargetStorage[0].eventtype === 'storage_pool') {
            storagePoolType = parseInt(checkTargetStorage[0].storage_pool_type);
            storagePoolUuid = checkTargetStorage[0].storage_pool_uuid;
        } else if (checkTargetStorage[0].eventtype === 'storage') {
            storageUuid = checkTargetStorage[0].storage_uuid;
            storageType = parseInt(checkTargetStorage[0].storage_type);
            storageWormConfig = checkTargetStorage[0].worm;
        }

        return {
            storage_uuid: storageUuid,
            storage_pool_uuid: storagePoolUuid,
            storage_text: storageText,
            node_uuid: '',
            node_pool_uuid: '',
            node_uuid_list: [],
            node_text: '',
            storage_type: storageType,
            storage_pool_type: storagePoolType,
            storage_worm_config: storageWormConfig,
        };
    };

    /**
     * 获取选择的节点
     * @param id
     * @returns {boolean|Object}
     */
    const getSelect = id => {
        let ret = {
            storage_uuid: '',
            storage_pool_uuid: '',
            storage_text: '',
            node_uuid: '',
            node_pool_uuid: '',
            node_uuid_list: [],
            node_text: '',
            storage_type: '',
            storage_pool_type: 0,
            storage_worm_config: {
                flag: false,
                type: 1,
                value: '',
            },
        }
        if (!cache[id].options.hide_backup_storage_flag && !cache[id].options.hide_backup_node_flag) {
            ret = getSelectTargetStorageAndNode(id);
        } else if (!cache[id].options.hide_backup_storage_flag) {
            ret = getSelectTargetStorage(id);
        } else if (!cache[id].options.hide_backup_node_flag) {
            let checkTargetNode = cache[id].node_tree.getCheckedNodes(true);
            ret = getSelectTargetNode(checkTargetNode);
        }
        return ret;
    };

    //////////////////// 结束-自定义接口 ////////////////////
})();