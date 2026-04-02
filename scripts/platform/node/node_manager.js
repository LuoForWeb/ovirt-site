var NodeManager = function () {
    var _ipisCustom = false;
    var _masterIpIsCustom = false;
    var _nodeCacheHisConf = [];
    const NODE_LIST_INTERVAL = 8000;  // 节点表格刷新频率8秒
    let taleCheckNodeIp = null;  // 表格选中的IP

    const NODE_FUNCTION_ENUM = {
        MANAGEMENT: 1,
        CALCULATION: 2,
        MANAGEMENT_AND_CALCULATION: 3,
    };

    /**
     * 节点操作状态枚举
     * @type {Object}
     */
    const NODE_OPERATE_STATUS_ENUM = {
        UNKNOWN: 0,
        DELETING: 1,
        MODIFYING: 2,
        UPGRADING: 3,
        OFFLINE: 4,
        UNREACHABLE: 5,
    };

    let applyOtherNodeTree;
    //初始化事件
    var addListeners = function () {
        $('#add').on('click', addNode);  // 添加节点按钮
        $('#delete').on('click', deleteNode); // 删除节点按钮
        $('#edit').on('click', editNode);  // 修改节点
        $('#deploy').on('click', deploy);
        $('#submit').on('click', serverSubmit);
        $('#remoteSubmit').on('click', remoteSubmit);  // 确认修改节点
        $('#setnetwork').on('click', setNetwork);
        $('#setnodecache').on('click', setNodeCache);
        $('#switchToInputNodeIp').unbind('click').click(switchChildnodeIpInput);
        $('#switchToSelectNodeIp').unbind('click').click(switchChildnodeIpSelect);

        $('#switchToInptMasterNodeIp').unbind('click').click(switchMasterNodeIpInput);
        $('#switchToSelectMasterNodeIp').unbind('click').click(switchMasterNodeIpSelect);

        $('#remoteNodename').off().blur(remoteNodenameVerify);
        $('#cacheType').unbind('change').bind('change', cacheTypeChange);

        // 资源限制
        $('#resourceLimit').on('click', showResourceLimitModal);
        $('#taskMaxConcurrent').on('change', taskMaxConcurrentChange);
        $('#applyOtherNode').on('switchChange.bootstrapSwitch', changeApplyOtherNode);
        $('#resourceLimitSubmit').on('click', resourceLimitSubmit);
        // 修改节点功能
        $('input[name="master_node_function"], input[name="sub_node_function"]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    }

    /**
     * 操作权限校验
     * @returns {{type: number, source_uuid, source_type: number}|boolean}
     */
    const checkAuth = function() {
        const select = $('#nodeTable').bootstrapTable('getSelections');
        if (!select.length) {
            return false;
        }

        return {
            type: 2,
            source_uuid: select[0].node_uuid,
            source_type: 7,
        };
    };

    //////////////////// 开始-资源限制 ////////////////////

    const getApplyNodeUuidList = () => {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        let nodeUuidList = [rows[0].node_uuid];
        if (!!$('#applyOtherNode').get(0).checked) {
            let nodes = applyOtherNodeTree.getCheckedNodes(true);
            for (const node of nodes) {
                if ('node' === node.eventtype && !nodeUuidList.includes(node.node_uuid)) {
                    nodeUuidList.push(node.node_uuid);
                }
            }
        }
        return nodeUuidList;
    };

    const getTimeStrategyList = (timeItem) => {
        let timeInfoList = {};
        if (timeItem.config.length) {
            for (const configItem of timeItem.config) {
                let dayDes = configItem.days.join('');
                if (typeof timeInfoList[dayDes] === 'undefined') {
                    timeInfoList[dayDes] = {
                        days: configItem.days,
                        time_list: []
                    };
                }
                timeInfoList[dayDes].time_list.push({
                    start_time: configItem.start_timestamp,
                    end_time: configItem.end_timestamp,
                });
            }
        }
        return Object.values(timeInfoList);
    }

    /**
     * 资源限制提交
     */
    const resourceLimitSubmit = () => {
        let timeItem = $('#prohibitTimePeriod').customTimeStrategy('getConfigData');

        let reqData = {
            node_config_flag: !!$('#configStatus').get(0).checked,
            max_task_running_num: parseInt($('#taskMaxConcurrent').val()),
            prohibit_time_type: timeItem.type,
            prohibit_time_info: getTimeStrategyList(timeItem),
            node_uuid_list: getApplyNodeUuidList(),
        };

        Metronic.blockUI({target: '#resourceLimitModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes/resources_limit`, 'PUT', res => {
            Metronic.unblockUI('#resourceLimitModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_RESOURCE_LIMIT, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_RESOURCE_LIMIT, res.message);
            $('#resourceLimitModal').modal('hide');
        });
    };

    const changeApplyOtherNode = function () {
        if (this.checked) {
            $('.selectLimitNodeDiv').show();
        } else {
            $('.selectLimitNodeDiv').hide();
        }
    };

    const taskMaxConcurrentChange = function () {
        let value = parseInt(this.value);
        if (value < 1 || !this.value) {
            value = 1;
        }
        if (value > 65535) {
            value = 65535;
        }
        $(this).val(value);
    };

    const showResourceLimitModal = () => {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        if (rows.length !== 1) {
            return UIToastr.showInfo(LANG.UI_NODE_RESOURCE_LIMIT, LANG.UI_NODE_RESOURCE_LIMIT_SELECT);
        }

        checkOperateAuth(checkAuth(), doShowResourceLimitModal);
    };

    /**
     * 执行显示资源限制弹窗操作
     */
    const doShowResourceLimitModal = () => {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        initResourceLimit(rows[0]);
        initApplyOtherNodeTree(rows[0]);
        if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
            $('#resourceLimitModal').modal({width: '800px', height: '587px'});
        }else{
            //英文版调整宽度为850px
            $('#resourceLimitModal').modal({width: '850px', height: '587px'});
        }
    };

    ////////// 开始-应用到其他节点树 //////////

    const getNodeTreeSetting = () => {
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
                beforeClick: applyOtherNodeTreeClick,
                onCheck: applyOtherNodeTreeCheck,
                beforeExpand: applyOtherNodeTreeExpand
            },
            view: {
                showTitle: true,
                nameIsHTML: true
            }
        };
    };

    const applyOtherNodeTreeClick = (treeId, treeNode) => {
        if ('more' !== treeNode.eventtype) {
            return;
        }
        Metronic.blockUI({target: '#applyOtherNodeTree', animate: true});
        let reqData = {
            offset: parseInt(treeNode.offset),
            limit: parseInt(treeNode.limit),
        };
        pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
            Metronic.unblockUI('#applyOtherNodeTree');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_RESOURCE_LIMIT, res.message);
                return;
            }
            let moreFlag = res.data.total !== (res.data.rows.length + parseInt(treeNode.total));
            let nodes = [];
            for (const item of res.data.rows) {
                let checked = item.node_uuid === treeNode.select_node_uuid;
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
                    checked,
                    chkDisabled: checked,
                    node_uuid: item.node_uuid,
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
                    limit: treeNode.limit,
                    total: res.data.rows.length + parseInt(treeNode.total),
                    select_node_uuid: treeNode.node_uuid,
                });
            }
            applyOtherNodeTree.addNodes(treeNode.getParentNode(), nodes);
            applyOtherNodeTree.removeNode(treeNode);
        });
    };

    const applyOtherNodeTreeCheck = (e, treeId, treeNode) => {
        //
    };

    const applyOtherNodeTreeExpand = (treeId, treeNode) => {
        //
    };

    const initApplyOtherNodeTree = row => {
        Metronic.blockUI({target: '#applyOtherNodeTree', animate: true});
        let reqData = {
            offset: 0,
            limit: 20
        };
        pAjaxRequest(reqData, `/api/v1/nodes`, 'GET', res => {
            Metronic.unblockUI('#applyOtherNodeTree');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_RESOURCE_LIMIT, res.message);
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
                let checked = item.node_uuid === row.node_uuid;
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
                    checked,
                    chkDisabled: checked,
                    node_uuid: item.node_uuid,
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
                    select_node_uuid: row.node_uuid,
                });
            }
            applyOtherNodeTree = $.fn.zTree.init($('#applyOtherNodeTree'), getNodeTreeSetting(), nodes);
        });
    };

    ////////// 结束-应用到其他节点树 //////////

    /**
     * 构建时间策略的配置
     * @param timeData
     */
    const getTimeStrategyOption = timeData => {
        let options = {
            title: LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD,
        };
        if (!timeData.init_flag) {
            return options;
        }
        let timeTypeMapping = {
            1: 'day',
            2: 'week',
            3: 'month',
            4: 'custom',
        };
        let active = timeTypeMapping[timeData.prohibit_time_type];
        options.active = typeof active === 'undefined' ? 'day' : active;
        let config = [];
        for (const timeItem of timeData.prohibit_time_info) {
            config.push({
                idx: $.fn.customTimeStrategy('getConfigIdx', {
                    prefixId: 'prohibitTimePeriod',
                    type: options.active,
                    days: timeItem.days,
                    startTimestamp: timeItem.start_timestamp,
                    endTimestamp: timeItem.end_timestamp,
                }),
                start_time: timeItem.start_time,
                end_time: timeItem.end_time,
                start_timestamp: timeItem.start_timestamp,
                end_timestamp: timeItem.end_timestamp,
                des: $.fn.customTimeStrategy('getConfigDes', {
                    type: options.active,
                    title: options.title,
                    days: timeItem.days,
                    startTime: timeItem.start_time,
                    endTime: timeItem.end_time,
                }),
                days: timeItem.days,
            });
        }
        options.list = {};
        options.list[options.active] = {
            config,
        }
        return options;
    };

    const initResourceLimit = (row) => {
        Metronic.blockUI({target: '#resourceLimitModal', animate: true});
        pAjaxRequest({node_uuid: row.node_uuid}, `/api/v1/nodes/resources_limit`, 'GET', res => {
            Metronic.unblockUI('#resourceLimitModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_RESOURCE_LIMIT, res.message);
                return;
            }
            $('#taskMaxConcurrentSpinner').spinner('value', res.data.max_task_running_num);
            $('#configStatus').bootstrapSwitch('state', !!res.data.node_config_flag);
            $('#prohibitTimePeriod').customTimeStrategy(getTimeStrategyOption(res.data));
        });
    };

    //////////////////// 结束-资源限制 ////////////////////

    const initIpAddress = () => {
        //获取url中的ip地址
        var ipAddress = getUrlIpAddress();

        $('#nodeEditInput').val(ipAddress)

        $('#serverIp').val(ipAddress)
        $('#nodeAddInput').val(ipAddress);
    };

    const initSpinner = () => {
        $('#editNode').spinner({value: 22710, step: 1, min: 0, max: 65535});
        $('#remoteEdit').spinner({value: 22711, step: 1, min: 0, max: 65535});
        $('#serverPort').spinner({value: 22710, step: 1, min: 0, max: 65535});
    };

    /**
     *  获取主节点IP信息
     */
    const initMasterNodeSelect = function (ipAddress) {
        Metronic.blockUI({target: '#remotemodaldiv', animate: true});
        getMasterNodeNetworkList().then(masterNodeNetworkList => {
            Metronic.unblockUI('#remotemodaldiv');
            let options = ``;
            for (const /** @type {{network_ip: string}} */ row of masterNodeNetworkList) {
                let selected = row.network_ip === ipAddress ? 'selected' : '';
                options += `<option value="${row.network_ip}" ${selected}>${row.network_ip}</option>`;
            }
            $('#nodeEditSelect').html(options);
        });
    };

    /**
     * 查询节点列表
     * @returns {Promise<unknown>}
     */
    const queryNodes = () => {
        return new Promise((resolve, reject) => {
            pAjaxRequest({offset: 0, limit: 1}, `/api/v1/nodes`, 'GET', res => {
                if (!res.success) {
                    reject(res.message);
                    return;
                }
                resolve(res.data);
            });
        });
    };

    /**
     * 切换主节点IP录入方式为输入
     */
    var switchMasterNodeIpInput = function () {
        $('.masternodeipselect').hide();
        $('.masternodeipinput').show();
        $('#nodeIpError').hide();
        _masterIpIsCustom = true;
        $('#nodeEditInput').off().blur(function () {
            $value = $('#nodeEditInput').val();
            if (!ipV4V6($value)) {
                $('#nodeEditInput').val('');
                $('#nodeIpError').show();
            } else {
                $('#nodeIpError').hide();
            }
        });
    }
    /**
     * 切换主节点IP录入方式选择
     */
    var switchMasterNodeIpSelect = function () {
        $('.masternodeipinput').hide();
        $('.masternodeipselect').show();
        _masterIpIsCustom = false;
    }
    /**
     * 修改备份节点,节点名校验
     */
    var remoteNodenameVerify = function () {
        var remoteNodeName = $('#remoteNodename').val();
        remoteNodeName = remoteNodeName.replace(/\s+/g, "");
        if (remoteNodeName === "") {
            UIToastr.showWarning(LANG.UI_NODE_REMOTE_NODE_NAME, LANG.UI_NODE_REMOTE_NODE_NAME_NULL_TIPS);
            return false;
        } else {
            return true;
        }
    }
    /**
     * 获取url中的ip地址
     * @returns {string}
     */
    var getUrlIpAddress = function () {
        var url = window.location.href;
        const ipAddressRegex = /https:\/\/([\d.]+)\//;
        const match = url.match(ipAddressRegex);
        var ipAddress = '';
        if (match) {
            ipAddress = match[1];
        }
        return ipAddress;
    }

    //跳转到节点网络配置
    var setNetwork = function () {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        if (rows.length !== 1) {
            UIToastr.showInfo(LANG.UI_NODE_NETWORK_CONFIG, LANG.UI_NODE_NETWORK_CONFIG_SELECT_TIPS);
            return false;
        }
        let nodeName = buildNodeName(rows[0].host_name, rows[0]);
        let url = "./content/platform/node/node_network.php?uuid=" + rows[0].node_uuid + "&nodename=" + nodeName;
        LOCATION(url, 'backup_manager');
    }

    /**
     * 获取主节点的uuid
     */
    const getMasterNodeNetworkList = () => {
        return new Promise((resolve) => {
            pAjaxRequest({}, `/api/v1/nodes/master_network`, 'GET', res => {
                if (!res.success) {
                    UIToastr.showInfo(LANG.UI_NODE_DES, LANG.UI_NODE_NOT_FOUND_MASTER_NODE);
                    return;
                }
                resolve(res.data.rows);
            });
        });
    };

    //添加节点
    var addNode = function () {
        Metronic.blockUI({target: '#nodeManagerDiv', animate: true});
        getMasterNodeNetworkList().then(masterNodeNetworkList => {
            Metronic.unblockUI('#nodeManagerDiv');
            if (!masterNodeNetworkList) {
                UIToastr.showInfo(LANG.UI_NODE_ADD, LANG.UI_NODE_NOT_FOUND_MASTER_NODE);
                return;
            }
            LOCATION(`./content/platform/node/node_add.php?node_uuid=${masterNodeNetworkList[0].node_uuid}`, 'backup_manager');
        });
    }

    //删除节点
    var deleteNode = function () {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        if (rows.length === 0 || rows.length > 1) {
            return UIToastr.showInfo(LANG.UI_NODE_DELETE_NODE, LANG.UI_NODE_DELETE_SELECT);
        }

        checkOperateAuth(checkAuth(), doDeleteNode);
    }

    /**
     * 执行删除节点操作
     */
    const doDeleteNode = () => {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        let nodeStatus = parseInt(rows[0].status);
        if (nodeStatus === NODE_OPERATE_STATUS_ENUM.MODIFYING) {  //修改中
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_STATUS_MODIFYING_DENY);
            return;
        } else if (nodeStatus === NODE_OPERATE_STATUS_ENUM.DELETING) {  //删除中
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_STATUS_DELETING_DENY);
            return;
        } else if (nodeStatus === NODE_OPERATE_STATUS_ENUM.UPGRADING) {  //升级中
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_STATUS_UPGRADING_DENY);
            return;
        }
        bootbox.confirm({
            title: LANG.UI_NODE_DELETE_NODE,
            message: LANG.UI_NODE_DELETE_TIPS,
            callback: debounce(function (r) {
                if (!r) return;
                Metronic.blockUI({target: '#nodeList', animate: true});
                pAjaxRequest({}, `/api/v1/nodes/${rows[0].node_uuid}`, 'DELETE', res => {
                    Metronic.unblockUI('#nodeList');
                    if (!res.success) {
                        UIToastr.showWarning(LANG.UI_NODE_DELETE_NODE, res.message);
                        return;
                    }
                    UIToastr.showSuccess(LANG.UI_NODE_DELETE_NODE, res.message);
                    taleCheckNodeIp = null;
                    $('#nodeTable').bootstrapTable('refresh');
                });
            }, 300)
        });
    };

    /**
     * 切换字节点IP录入方式为输入
     */
    var switchChildnodeIpInput = function () {
        $('.backupchildnodeselect').hide();
        $('.backupchildnodeinput').show();
        $('#childNodeIpError').hide();
        $('#inputchildnode').off().blur(function () {
            let childValue = $('#inputchildnode').val();
            if (!ipV4V6(childValue)) {
                $('#inputchildnode').val('');
                $('#childNodeIpError').show();
            } else {
                $('#childNodeIpError').hide();
            }
        });
    }

    /**
     * 切换子节点IP录入方式选择
     */
    var switchChildnodeIpSelect = function () {
        $('.backupchildnodeinput').hide();
        $('.backupchildnodeselect').show();
        _ipisCustom = false;
    }

    /**
     * 设置节点缓存配置
     */
    var setNodeCache = function () {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        if (rows.length !== 1) {
            UIToastr.showInfo(LANG.UI_NODE_CACHE_CONF, LANG.UI_NODE_CACHE_CONF_NO_SELECT);
            return;
        }

        checkOperateAuth(checkAuth(), doSetNodeCache);
    }

    /**
     * 执行设置节点缓存配置操作
     */
    const doSetNodeCache = () => {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        let nodeStatus = parseInt(rows[0].status);
        if (nodeStatus === NODE_OPERATE_STATUS_ENUM.MODIFYING) {  //修改中
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_STATUS_MODIFYING_DENY);
            return;
        } else if (nodeStatus === NODE_OPERATE_STATUS_ENUM.DELETING) {  //删除中
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_STATUS_DELETING_DENY);
            return;
        } else if (nodeStatus === NODE_OPERATE_STATUS_ENUM.UPGRADING) {  //升级中
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_STATUS_UPGRADING_DENY);
            return;
        }

        let nodeName = buildNodeName(rows[0].host_name, rows[0]);
        getNodeHisCacheConf(rows[0].node_uuid, nodeName);
    };

    /**
     * 格式化存储设备名称
     * @param storageName
     * @param storageType
     * @param freeSize
     * @param totalSize
     */
    const formatStorageName = (storageName, storageType, freeSize, totalSize) => {
        storageName = storageName + '(' + CONF.STORAGE_TYPE_DES[storageType] + ', ';
        storageName += LANG.UI_PUBLIC_TOTAL_SIZE2 + ': ' + storageCalculateSize(totalSize) + ', ';
        storageName += LANG.UI_PUBLIC_FREE_SIZE + ': ' + storageCalculateSize(freeSize) + ')';
        return storageName;
    };

    /**
     * 获取节点缓存历史配置信息
     */
    const getNodeHisCacheConf = function (nodeUuid, nodeName) {
        $('#setNodeCacheDiv').modal({'width': "700px", 'margin-top': "-15%"});
        $('#nodeinfo').val(nodeName).css('color', '#5f5d5d');
        Metronic.blockUI({target: '#setNodeCacheDiv', animate: true});
        pAjaxRequest({}, `/api/v1/nodes/${nodeUuid}/cache`, 'get', res => {
            Metronic.unblockUI('#setNodeCacheDiv');
            if (!res.success) {
                UIToastr.showInfo(LANG.UI_NODE_CACHE_CONF, res.message);
                return;
            }
            _nodeCacheHisConf = res.data;
            let hisCacheStorageUuid = res.data.cache_storage_uuid;  //缓存历史存储uuid
            let diskStorageResource = res.data.disk_storage_resource;

            $('#cacheType').val(res.data.cache_type).change();  //缓存类型
            $('#nodeCachePathInfo').val(res.data.cache_dir_path);  //缓存路径
            $('#cacheStrategyType').val(res.data.switch_strategy);  //缓存策略配置
            $('#nodeAlarmThresholdValue').val((res.data.warning_value / 1024 / 1024 / 1024).toFixed(0));

            let options = ``;
            for (const diskStorageInfo of diskStorageResource) {
                let selected = '';
                if (hisCacheStorageUuid === diskStorageInfo.storage_uuid) {
                    selected = 'selected';
                }
                let name = formatStorageName(
                    diskStorageInfo.storage_name,
                    parseInt(diskStorageInfo.storage_type),
                    parseInt(diskStorageInfo.free_size),
                    parseInt(diskStorageInfo.total_size),
                );
                let disabled = '';
                if (!diskStorageInfo.storage_online_flag) {
                    disabled = 'disabled';
                    name = '(' + LANG.UI_STORAGE_STATUS_OFFLINE + ')' + name;
                }
                options += `<option value="${diskStorageInfo.storage_uuid}" ${selected} ${disabled}>${name}</option>`;
            }
            $('#localStorageDirectory').html(options);

            if (res.data.warning_flag) {  //高级阈值开关
                $('#nodeAlarmThresholdConf').bootstrapSwitch('state', true);
                $('.nodealarmthresholdview').show();
            } else {
                $('#nodeAlarmThresholdConf').bootstrapSwitch('state', false);
                $('.nodealarmthresholdview').hide();
            }

            if (parseInt(res.data.cache_type) === 1) {  // 本地目录
                $('.nodecachepathinfoview').show();
                $('.localStoragedirectoryview').hide();
            } else {  // 存储设备
                $('.nodecachepathinfoview').hide();
                $('.localStoragedirectoryview').show();
            }
            bindNodeCacheConfEvent();
        });
    };

    /**
     * 绑定节点缓存配置事件信息
     */
    var bindNodeCacheConfEvent = function () {
        $('#nodeAlarmThresholdConf').on('switchChange.bootstrapSwitch', alarmThresholdConfChange);
        $('#nodeCacheSubmit').unbind('click').click(nodeCacheSubmit);
        $('#spinnerpercent').spinner({value: 1, step: 1, min: 1, max: 100});
    }

    /**
     * 提交节点缓存配置
     */
    var nodeCacheSubmit = function () {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        let warningFlag = !!$('#nodeAlarmThresholdConf').get(0).checked;
        let warningValue = warningFlag ? parseInt($('#nodeAlarmThresholdValue').val()) : 0;
        let cacheTypeValue = parseInt($('#cacheType').val());
        let localStorageDirectory = $('#localStorageDirectory').val();
        if (!cacheTypeValue) {
            UIToastr.showInfo(LANG.UI_NODE_CACHE_CONF, LANG.UI_NODE_NO_CACHE_TYPE);
            return;
        }
        // 目录和存储二者选其一即可
        let cacheDirPath = $('#nodeCachePathInfo').val();
        if (cacheTypeValue === 1) {  //本地目录
            if (cacheDirPath == null || cacheDirPath === "") {
                UIToastr.showInfo(LANG.UI_NODE_CACHE_CONF, LANG.UI_NODE_CACHE_CONF_NO_DIRECTORY);
                return;
            }
        } else if (cacheTypeValue === 2) {  //存储设备
            if (!localStorageDirectory) {
                UIToastr.showInfo(LANG.UI_NODE_CACHE_CONF, LANG.UI_NODE_CACHE_CONF_NO_STORAGE);
                return;
            }
        }
        let reqData = {
            cache_type: cacheTypeValue,
            cache_dir_path: cacheDirPath,
            cache_storage_uuid: localStorageDirectory,
            switch_strategy: parseInt($('#cacheStrategyType').val()),
            warning_flag: warningFlag,
            warning_value: warningValue * 1024 * 1024 * 1024,
        };
        Metronic.blockUI({target: '#setNodeCacheDiv', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes/${rows[0].node_uuid}/cache`, 'PATCH', res => {
            Metronic.unblockUI('#setNodeCacheDiv');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_CACHE_CONF, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_CACHE_CONF, res.message);
            taleCheckNodeIp = null;
            $('#nodeTable').bootstrapTable('refresh');
            $('#setNodeCacheDiv').modal('hide');
        });
    }

    /**
     * 切换存储缓存类型后需要处理的隐藏与显示
     */
    var cacheTypeChange = function () {
        var standbyHostValue = $('#cacheType').val();   //节点缓存类型1.目录；2.存储
        if (standbyHostValue == 1) {
            $('.nodecachepathinfoview').show();
            $('.localStoragedirectoryview').hide();
        } else {
            if (
                typeof _nodeCacheHisConf['disk_storage_resource'] === 'undefined' ||
                !_nodeCacheHisConf.disk_storage_resource.length
            ) {
                UIToastr.showInfo(LANG.UI_NODE_CACHE_CONF, LANG.UI_NODE_CACHE_STORAGE_IS_NULL);
            }
            $('.nodecachepathinfoview').hide();
            $('.localStoragedirectoryview').show();
        }
    }

    /**
     * 告警阈值配置
     */
    var alarmThresholdConfChange = function () {
        if (this.checked) {
            $('.nodealarmthresholdview').show();
        } else {
            $('.nodealarmthresholdview').hide();
        }
    }

    /**
     * 修改节点
     */
    const editNode = function () {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        if (1 !== rows.length) {
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_EDIT_TIPS);
            return;
        }

        checkOperateAuth(checkAuth(), doEditNode);
    };

    /**
     * 执行修改节点操作
     */
    const doEditNode = () => {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        let nodeStatus = parseInt(rows[0].status);
        if (nodeStatus === NODE_OPERATE_STATUS_ENUM.MODIFYING) {  //修改中
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_STATUS_MODIFYING_DENY);
            return;
        } else if (nodeStatus === NODE_OPERATE_STATUS_ENUM.DELETING) {  //删除中
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_STATUS_DELETING_DENY);
            return;
        } else if (nodeStatus === NODE_OPERATE_STATUS_ENUM.UPGRADING) {  //升级中
            UIToastr.showInfo(LANG.UI_NODE_EIDT, LANG.UI_NODE_STATUS_UPGRADING_DENY);
            return;
        }

        let nodeType = parseInt(rows[0].node_type);
        let ipAddress = getUrlIpAddress();
        switchChildnodeIpSelect();  //  默认通过select选择
        initMasterNodeSelect(ipAddress);  //获取主节点IP信息
        let nodeName = rows[0].node_nickname;
        if (!nodeName) {
            nodeName = rows[0].host_name;
        }
        $('#nodeEditInput').val(ipAddress);
        // 设置节点功能默认值
        let nodeFunction = parseInt(rows[0].node_function);
        if (nodeType === 1) {  // 修改主节点
            $('#masterNodeFunctionManagement').iCheck('check').iCheck('disable');
            if (nodeFunction === NODE_FUNCTION_ENUM.MANAGEMENT_AND_CALCULATION) {
                $('#masterNodeFunctionCalculation').iCheck('check');
            } else if (nodeFunction === NODE_FUNCTION_ENUM.CALCULATION) {
                $('#masterNodeFunctionCalculation').iCheck('check');
            }
            Metronic.blockUI({target: '#modaldiv', animate: true});
            queryNodes().then(nodeData => {
                Metronic.unblockUI('#modaldiv');
                if (nodeData.total === 1) {  // 只有一个主节点，不能修改任何节点功能配置
                    $('#masterNodeFunctionCalculation').iCheck('check').iCheck('disable');
                }
                $('#modaldiv').modal({'width': "700px"});
                $('#nodeuuid').val(rows[0].node_uuid);
                $('#nodename').val(nodeName);
                $('#remotemodaldiv').modal('hide');

                $('.child-node-ip-view').hide();
                $('.child-node-port-view').hide();
            });
        } else {  // 修改子节点
            if (nodeFunction === NODE_FUNCTION_ENUM.MANAGEMENT_AND_CALCULATION) {
                $('#subNodeFunctionManagement').iCheck('check');
                $('#subNodeFunctionCalculation').iCheck('check');
            } else if (nodeFunction === NODE_FUNCTION_ENUM.MANAGEMENT) {
                $('#subNodeFunctionManagement').iCheck('check');
                $('#subNodeFunctionCalculation').iCheck('uncheck');
            } else if (nodeFunction === NODE_FUNCTION_ENUM.CALCULATION) {
                $('#subNodeFunctionManagement').iCheck('uncheck');
                $('#subNodeFunctionCalculation').iCheck('check');
            }
            $('#remotemodaldiv').modal({'width': "700px"});
            $('#remoteNodeuuid').val(rows[0].node_uuid);
            $('#remoteNodename').val(nodeName);
            $('#modaldiv').modal('hide');
            $('.child-node-ip-view').show();
            $('.child-node-port-view').show();
            initBackupNodeSelect(rows[0].network_list);
        }
    };

    /**
     * 初始化备份节点的ip选择
     * @param networkList
     */
    const initBackupNodeSelect = function (networkList) {
        let options = ``;
        for (const /** @type {{network_ip: string}} */ row of networkList) {
            options += `<option value="${row.network_ip}">${row.network_ip}</option>`;
        }
        $('#remoteEditInput').html(options);
    };

    const serverSubmit = function () {
        let managementFlag = $('#masterNodeFunctionManagement:checked');
        let calculationFlag = $('#masterNodeFunctionCalculation:checked');
        let nodeFunction = 0;
        if (managementFlag.length && calculationFlag.length) {
            nodeFunction = NODE_FUNCTION_ENUM.MANAGEMENT_AND_CALCULATION;
        } else if (managementFlag.length) {
            nodeFunction = NODE_FUNCTION_ENUM.MANAGEMENT;
        } else if (calculationFlag.length) {
            nodeFunction = NODE_FUNCTION_ENUM.CALCULATION;
        }
        let reqData = {
            node_nickname: $('#nodename').val(),
            node_type: 1,
            node_function: nodeFunction,
        };

        Metronic.blockUI({target: '#modaldiv', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes/${$('#nodeuuid').val()}`, 'PATCH', res => {
            Metronic.unblockUI('#modaldiv');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_EIDT, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_EIDT, res.message);
            taleCheckNodeIp = null;
            $('#nodeTable').bootstrapTable('refresh');
            $('#modaldiv').modal('hide');
        });
    };

    var remoteSubmit = function () {
        var customChidNodeIp = $("#inputchildnode").val();
        var remoteIp = $("#remoteEditInput").val()
        if (_ipisCustom === true) {
            remoteIp = customChidNodeIp;
            if (!ipV4V6(remoteIp)) {
                UIToastr.showWarning(LANG.UI_NODE_REMOTE_IPADDR, LANG.UI_NODE_REMOTE_IPADDR_FORMAT_ERROR_TIPS);
                $('#nodeEditInput').val('');
                return;
            }
        }

        var masterIp = $('#nodeEditSelect').val();
        if (_masterIpIsCustom) {
            masterIp = $('#nodeEditInput').val();
            if (!ipV4V6(masterIp)) {
                UIToastr.showWarning(LANG.UI_NODE_REMOTE_IPADDR, LANG.UI_NODE_REMOTE_IPADDR_FORMAT_ERROR_TIPS);
                $('#nodeEditInput').val('');
                return;
            }
        }
        if (remoteNodenameVerify()) {
            var remoteNodename = xssEncode($('#remoteNodename').val());
            remoteNodename = remoteNodename.replace(/\s+/g, "");
        } else {
            return;
        }
        var serverPort = $('input[name=editport]').val();
        var childNodeport = $("input[name=remoteeditport]").val();
        if (serverPort.length < 2 || childNodeport.length < 2) {
            UIToastr.showWarning(LANG.UI_NODE_EIDT, LANG.UI_NODE_PORT_TIPS);
            return false;
        }
        let managementFlag = $('#subNodeFunctionManagement:checked');
        let calculationFlag = $('#subNodeFunctionCalculation:checked');
        if (!managementFlag.length && !calculationFlag.length) {
            UIToastr.showWarning(LANG.UI_NODE_EIDT, LANG.UI_NODE_ADD_SUB_NODE_NO_NODE_FUNCTION);
            return;
        }
        let nodeFunction = 0;
        if (managementFlag.length && calculationFlag.length) {
            nodeFunction = NODE_FUNCTION_ENUM.MANAGEMENT_AND_CALCULATION;
        } else if (managementFlag.length) {
            nodeFunction = NODE_FUNCTION_ENUM.MANAGEMENT;
        } else if (calculationFlag.length) {
            nodeFunction = NODE_FUNCTION_ENUM.CALCULATION;
        }
        let reqData = {
            node_nickname: remoteNodename,
            remote_ip: remoteIp,
            remote_port: childNodeport,
            node_type: 2,
            node_function: nodeFunction,
            common_server_info: {
                database_ip: masterIp,
                database_name: '',
                database_passwd: '',
                database_port: 3306,
                database_user: '',
                server_ip: masterIp,
                server_port: serverPort,
            },
        };
        Metronic.blockUI({target: '#remotemodaldiv', animate: true});
        pAjaxRequest(reqData, `/api/v1/nodes/${$('#remoteNodeuuid').val()}`, 'PATCH', res => {
            Metronic.unblockUI('#remotemodaldiv');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_NODE_EIDT, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_NODE_EIDT, res.message);
            taleCheckNodeIp = null;
            $('#nodeTable').bootstrapTable('refresh');
            $('#remotemodaldiv').modal('hide');
        });
    }
    //修改节点名称提交

    //部署软件
    var deploy = function () {
        let rows = $('#nodeTable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showInfo(LANG.UI_NODE_DEPLOY, LANG.UI_NODE_DEPLOY_TIPS1);
            return;
        }

        let params = {
            uuids: rows.map(v => v.node_uuid),
        };
        let p = JSON.stringify(params);
        //检查所选的节点是否都支持远程部署
        $.post(CONF.AJAXPATH, {m: CONF.M.NODE, f: 'checkAutoDeploy', p}, function (d) {
            let data = JSON.parse(d);
            if (data.re) {
                let url = './content/platform/node/deploy_soft.php?uuids=' + params.uuids.join(',');
                LOCATION(url, 'backup_manager');
            } else {
                UIToastr.showWarning(LANG.UI_NODE_DEPLOY, LANG.UI_NODE_DEPLOY_TIPS2);
            }
        });
    }

    const buildNodeName = (value, row) => {
        let showName = row.host_name;
        if (row.ip !== row.node_nickname && row.node_nickname) {
            showName = row.node_nickname;
        }
        if (1 === parseInt(row.node_type)) {
            showName += `(${LANG.UI_CLUSTER_MASTER_NODE})`;
        } else {
            showName += `(${LANG.UI_CLUSTER_BACKUP_NODE})`;
        }
        return showName;
    };

    const formatterNodeName = (value, row) => {
        let nodeName = buildNodeName(value, row);
        return `<span title="${nodeName}">${nodeName}</span>`;
    };

    const getNodeTableColumns = () => {
        let columns = [{
            checkbox: true,
            sortable: false,
            width: '2',
            widthUnit: '%',
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
            title: LANG.UI_NODE_NODE_NAME,
            field: 'node_nickname',
            width: '15',
            widthUnit: '%',
            formatter: formatterNodeName,
        }, {
            title: LANG.UI_NODE_IP_ADDRESS,
            field: 'ip',
            width: '15',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_FUNCTION,
            field: 'node_function',
            sortable: true,
            width: '12',
            widthUnit: '%',
            formatter: (value) => {
                value = parseInt(value);
                let name = [];
                if (value === NODE_FUNCTION_ENUM.MANAGEMENT) {
                    name.push(LANG.UI_NODE_FUNCTION_MANAGEMENT);
                } else if (value === NODE_FUNCTION_ENUM.CALCULATION) {
                    name.push(LANG.UI_NODE_FUNCTION_CALCULATION);
                } else if (value === NODE_FUNCTION_ENUM.MANAGEMENT_AND_CALCULATION) {
                    name.push(LANG.UI_NODE_FUNCTION_MANAGEMENT);
                    name.push(LANG.UI_NODE_FUNCTION_CALCULATION);
                } else {
                    name.push('--');
                }
                return `<span title="${name.join('、')}">${name.join('、')}</span>`;
            },
        }, {
            title: LANG.UI_NODE_NODE_POOL,
            field: 'node_pool_name',
            sortable: false,
            width: '12',
            widthUnit: '%',
            formatter: (value) => {
                if (!Array.isArray(value) || !value.length) {
                    return '--';
                }
                let name = value.join('<br>');
                let title = value.join('\n');
                return `<span title="${title}">${name}</span>`;
            },
        }, {
            title: LANG.UI_NODE_RESOURCE_LIMIT,
            field: 'node_resource_limit_flag',
            width: '6',
            widthUnit: '%',
            sortable: false,
            formatter: (value) => {
                if (!!value) {
                    return `<span class="label label-sm label-success" title="${LANG.UI_NODE_RESOURCE_LIMIT_ENABLE}">
                        ${LANG.UI_NODE_RESOURCE_LIMIT_ENABLE}
                    </span>`;
                }
                return `<span class="label label-sm label-warning" title="${LANG.UI_NODE_RESOURCE_LIMIT_DISABLE}">
                    ${LANG.UI_NODE_RESOURCE_LIMIT_DISABLE}
                </span>`;
            },
        }, {
            title: LANG.UI_NODE_VERSION,
            field: 'version',
            sortable: false,
            width: '8',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_REGISTER_TIME,
            field: 'register_time',
            width: '12',
            widthUnit: '%',
        }, {
            title: LANG.UI_NODE_DEPLOY_STATUS,
            field: 'deploy_flag',
            sortable: false,
            width: '5',
            widthUnit: '%',
            formatter: value => {
                if (!!value) {
                    return `<span class="label label-sm label-success" title="${LANG.UI_NODE_DEPLOYED}">
                        ${LANG.UI_NODE_DEPLOYED}
                    </span>`;
                }
                return `<span class="label label-sm label-warning" title="${LANG.UI_NODE_UNDEPLOY}">
                    ${LANG.UI_NODE_UNDEPLOY}
                </span>`;
            },
        }, {
            title: LANG.UI_NODE_NODE_STATUS,
            field: 'status',
            sortable: false,
            width: '5',
            widthUnit: '%',
            formatter: (value, row) => {
                /**
                 * 1、如果节点处于未部署状态(即bd_module_server里面没有该节点任何记录)，那么显示--
                 * 2、如果bd_node的status值为0，再判断如果该节点在线(在线判定为该节点在bd_module_server里面的所有记录都在线)，显示在线；否则显示异常，鼠标移上去显示离线的服务
                 * 3、如果bd_node的status值为1，那么显示删除中，此时选择该节点删除时显示“当前节点正在删除中，请稍后重试”
                 * 4、如果bd_node的status值为2，那么显示修改中，此时选择该节点删除时显示“当前节点正在修改中，请稍后重试”
                 * 5、如果bd_node的status值为其他，那么显示为--
                 */
                if (!!!row.deploy_flag) {
                    return '--';
                }
                let text = '';
                let labelClass = 'label-info';
                let des = ``;
                switch (parseInt(value)) {
                    case NODE_OPERATE_STATUS_ENUM.UNKNOWN:
                        labelClass = 'label-warning';
                        text = LANG.UI_NODE_ABNORMAL;
                        des = row.offline_module_des;
                        if (!!row.online_flag) {
                            labelClass = 'label-success';
                            text = LANG.UI_NODE_NORMAL;
                        }
                        break;
                    case NODE_OPERATE_STATUS_ENUM.MODIFYING:
                        text = LANG.UI_NODE_STATUS_MODIFYING;
                        break;
                    case NODE_OPERATE_STATUS_ENUM.DELETING:
                        labelClass = 'label-danger';
                        text = LANG.UI_NODE_STATUS_DELETING;
                        break;
                    case NODE_OPERATE_STATUS_ENUM.UPGRADING:
                        text = LANG.UI_NODE_STATUS_UPGRADING;
                        break;
                    case NODE_OPERATE_STATUS_ENUM.OFFLINE:
                        labelClass = 'label-warning';
                        text = LANG.UI_NODE_ABNORMAL;
                        des = LANG.UI_NODE_STATUS_OFFLINE;
                        break;
                    default:
                        return '--';
                }
                return `<span class="label label-sm ${labelClass}">
					<a style="background: transparent; color: inherit" class="popovers" data-container="body" data-trigger="hover"
						data-placement="left" data-content="${des}">
                        ${text}
                    </a>
				</span>`;
            },
        }];

        // 如果没有授权计算资源池，这里不显示计算资源池列
        if (!CONF.PERMISSION.includes('node_pool')) {
            let nodePoolIndex = -1;
            for (const index in columns) {
                if (columns[index].field === 'node_pool_name') {
                    nodePoolIndex = index;
                    break;
                }
            }
            if (nodePoolIndex !== -1) {
                columns.splice(nodePoolIndex, 1);
            }
        }
        return columns;
    };

    const initNodeTableHeight = () => {
        let toolbarHeight = 57;
        let paginationHeight = 52;
        let alertHeight = 71;
        let tabTitleHeight = 48;
        let navTitleHeight = 46;
        // page-content有40px的内边距
        // portlet-body有10px的内边距
        // tab-content有20px的外边距离
        let otherHeight = 40 + 10 + 20;
        let height = window.innerHeight - toolbarHeight - paginationHeight - alertHeight - tabTitleHeight - navTitleHeight - otherHeight;
        $("#nodeList .fixed-table-body").css({
            'max-height': height,
            'height': 'auto',
        });
    };

    /**
     * 刷新当前任务表格数据
     */
    const refreshNodeTable = () => {
        if (timerTask.NodeListTimer) {
            clearTimeout(timerTask.NodeListTimer);
            timerTask.NodeListTimer = null;
        }
        timerTask.NodeListTimer = setTimeout(() => {
            $('#nodeTable').bootstrapTable('refresh');
        }, NODE_LIST_INTERVAL);
    };

    const getQueryParams = () => {
        return {};
    };

    const getNodeTableOption = () => {
        return {
            vin_url: `/api/v1/nodes`,
            vin_params: getQueryParams,
            vin_method: 'get',
            buttonsToolbar: '#vin_node_toolbar .vin_btnToolbar',
            toolbarId: '#vin_node_toolbar',
            vin_toolbar: '#vin_node_toolbar',
            sortName: 'register_time',
            sortOrder: 'desc',
            // 隐藏行
            hideColumns: '',
            showButtonText: false,
            clickToSelect: true,
            pagination: true,
            pageList: [10, 20, 50, 100, 150, 200],
            pageSize: 20,
            resizable: true,
            showRefresh: false,
            showExport: false,
            singleSelect: true,
            // changeHeightBtn: true,
            // onResetView: initNodeTableHeight,
            onRefresh: () => {
                $("#nodeTable").bootstrapTable('hideLoading');
            },
            onPostBody: () => {
                $('div.popover.in').hide();
                $(".popovers").popover();
                refreshNodeTable();
                if (taleCheckNodeIp) {
                    $('#nodeTable').bootstrapTable('checkBy', {field: 'ip', values: [taleCheckNodeIp]});
                }
                checkEvent('#nodeTable', '#delete');
            },
            onCheck: function (row) {
                taleCheckNodeIp = row.ip;
                checkEvent('#nodeTable', '#delete');
            },
            onUncheck: function () {
                taleCheckNodeIp = null;
                checkEvent('#nodeTable', '#delete');
            },
            onCheckAll: function () {
                checkEvent('#nodeTable', '#delete');
            },
            onUncheckAll: function () {
                checkEvent('#nodeTable', '#delete');
            },
            columns: getNodeTableColumns(),
        };
    };

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

    /**
     * 初始化节点表格
     */
    const initNodeTable = () => {
        $('#nodeTable').bootstrapTable('destroy').baseTableConfig().init(getNodeTableOption());
    };

    const initTableHeight = () => {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 331;

        $("#node_manager_div .fixed-table-body").css({
            "height": height
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            initNodeTable();
            addListeners();
            initSpinner();
            initIpAddress();
            initTableHeight();
        }
    };

}();

jQuery(document).ready(function () {
    NodeManager.init();
});