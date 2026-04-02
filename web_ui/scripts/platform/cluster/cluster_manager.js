var ClusterManager = (() => {
    let topologicalChart = null;
    let prioritySortable = null;
    let loginClusterStatus = null;  // 跳转到登录页面的集群状态切换：停止=>启动， 启动=>停止
    let lastClusterStatus = null;
    let changeLastClusterStatusFlag = false;
    let jumpFlag = false;  // 是否已经显示跳转框了，不需要多次显示，显示一次即可
    const CLUSTER_STATUS_ENUM = {
        STOPPED: 1,
        STARTED: 2,
        ERROR: 3,
        STARTING: 4,
        STOPPING: 5,
        SWITCHING: 6,
        TAKEOVER: 7,  // 接管中
    };
    const NODE_ROLE_ENUM = {
        UNKNOWN: 0,
        MASTER: 1,
        BACKUP: 2,
    };
    const CLUSTER_CONFIG_MODE_ENUM = {
        INIT: 1,   // 初始化
        MODIFY: 2, // 更新配置
    };
    const CLUSTER_OPERATE_LOG_INTERVAL = 5000;
    const CLUSTER_CONFIG_INTERVAL = 5000;
    const NODE_FUNCTION_ENUM = {
        MANAGEMENT: 1,
        CALCULATION: 2,
        MANAGEMENT_AND_CALCULATION: 3,
    };
    const MAX_ADVERT_INT = 1800;
    const MIN_ADVERT_INT = 30;

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

    /**
     * 集群配置信息
     * @typedef {Object} DbConfig
     * @param {Boolean} DbConfig.config_flag - 是否已配置
     * @param {Boolean} DbConfig.cluster_status - 集群状态【1开启 2停止】
     * @param {String} DbConfig.config_service_ip - 集群虚拟IP
     * @param {Number} DbConfig.advert_int - 心跳间隔
     * @param {Boolean} DbConfig.priority_flag - 集群优先级是否开启
     * @param {Number} DbConfig.operation_progress - 操作进度
     * @param {Array<Object>} DbConfig.node_config - 节点列表
     * @param {String} DbConfig.node_config[].node_ip - 节点IP地址
     * @param {String} DbConfig.node_config[].network_ip - 网卡IP地址
     * @param {String} DbConfig.node_config[].network_name - 网卡信息
     * @param {Number} DbConfig.node_config[].node_role - 节点角色【1主节点 2子节点】
     * @param {String} DbConfig.node_config[].node_uuid - 节点uuid
     * @param {Number} DbConfig.node_config[].priority - 节点优先级
     * @param {String} DbConfig.node_config[].node_hostname - 节点名称
     * @param {Boolean} DbConfig.node_config[].online_flag - 节点在线状态
     * @param {Number} DbConfig.node_config[].node_status - 节点状态
     * @param {Array} DbConfig.node_config[].node_offline_module - 节点离线模块
     * @param {String} DbConfig.node_config[].node_offline_module_des - 节点离线模块描述
     * @param {String} DbConfig.node_config[].node_version - 节点版本
     * @param {Array<Object>} DbConfig.node_config[].network_list - 网卡列表
     * @param {String} DbConfig.node_config[].network_list[].network_alias - 网卡别名
     * @param {String} DbConfig.node_config[].network_list[].network_ip - 网卡IP
     * @param {String} DbConfig.node_config[].network_list[].network_name - 网卡名称
     * @param {Array<String>} DbConfig.node_config[].network_list[].network_pool_name - 网络资源池列表
     * @param {Number} DbConfig.node_config[].network_list[].network_port - 网卡端口
     * @param {Number} DbConfig.node_config[].network_list[].network_type - 网卡类型
     */

    /**
     * 集群操作日志列表
     * @typedef {Array<ClusterOperateLog>} ClusterOperateLogList
     */

    /**
     * 集群操作日志
     * @typedef {Object} ClusterOperateLog
     * @param {Number} ClusterOperateLog.cluster_status - 日志等级【1正常 2警告 3错误】
     * @param {String} ClusterOperateLog.op_time - 操作时间
     * @param {String} ClusterOperateLog.op_message - 操作消息
     * @param {String} ClusterOperateLog.op_user - 操作用户
     * @param {String} ClusterOperateLog.node_uuid - 节点uuid
     */

    /**
     * 初始化监听信息
     */
    const initListeners = () => {
        // 集群未配置
        // 集群配置按钮
        $('#clusterConfigBtn').on('click', () => {
            if (CONF.PERMISSION_ARR.includes('p_cluster_manager_config')) {
                showConfigClusterDrawer();
            }
        });

        // 名称输入框
        $('#clusterName').on('input change', function () {
            $(this).val($(this).val().replace(/[<>"]/gi, ''));
        });
        // drawer按钮
        $('#configClusterDrawer .drawer-footer .cancel').on('click', hideConfigClusterDrawer);
        $('#configClusterDrawer .drawer-header .drawer-close').on('click', hideConfigClusterDrawer);
        $('#configClusterDrawer .drawer-footer .btn-confirm').on('click', submitConfigClusterDraw);
        // 切换配置的节点网络
        $('#configClusterDrawer').on('change', '.nodeNetworkSelect', changeNodeNetworkSelect);

        // 切换优先级
        $('#prioritySwitch').on('switchChange.bootstrapSwitch', changePrioritySwitch);
        $('.config-priority-switch, .config-priority').hide();

        // 集群已配置
        // 修改集群配置按钮
        $('#changeConfig').on('click', () => {
            if (CONF.PERMISSION_ARR.includes('p_cluster_manager_config')) {
                showConfigClusterDrawer();
            }
        });
        // 启动集群
        $(`#startCluster`).on('click', () => {
            if (CONF.PERMISSION_ARR.includes('p_cluster_manager_config')) {
                startCluster();
            }
        });
        // 停止集群
        $(`#stopCluster`).on('click', () => {
            if (CONF.PERMISSION_ARR.includes('p_cluster_manager_config')) {
                stopCluster();
            }
        });
        // 切换集群主节点
        $('#clusterNodeSelect').on('change', () => {
            if (CONF.PERMISSION_ARR.includes('p_cluster_manager_config')) {
                changeMasterNodeSelect();
            }
        });
        $(`#switchMasterNode`).on('click', () => {
            if (CONF.PERMISSION_ARR.includes('p_cluster_manager_config')) {
                switchMasterNode();
            }
        });
        setClusterTopologicalSize();

        window.onresize = () => {
            setClusterTopologicalSize();
            topologicalChart.resize();
        };
        topologicalChart = echarts.init($('#clusterTopological').get(0));
        // 集群心跳间隔
        $('#advertInt').on('change', advertIntChange);
        $('#advertIntSpinner button').on('click', advertIntChange);
    };

    const advertIntChange = () => {
        let $advertInt = $('#advertInt');
        let value = parseInt($advertInt.val());
        if (!$advertInt.val() || value < MIN_ADVERT_INT) {
            value = MIN_ADVERT_INT;
        } else if (value > MAX_ADVERT_INT) {
            value = MAX_ADVERT_INT;
        }
        $('#advertIntSpinner').spinner('value', value);
    };

    /**
     * 初始化spinner
     */
    const initSpinner = () => {
        $('#advertIntSpinner').spinner({value: 60, min: MIN_ADVERT_INT, max: MAX_ADVERT_INT, step: 5});
    };

    /**
     * 设置集群拓扑图高度
     */
    const setClusterTopologicalSize = () => {
        let height = window.innerHeight - 48 - 20 - 20 - 20 - 20 - 46;
        let width = window.innerWidth - $('.has-config__left').outerWidth(true) - 210 - 20 - 20 - 10 - 20 - 20;
        $('#clusterTopological').css({
            'width': width,
            'height': height,
        });
    };

    //////////////////// 开始-初始化集群配置 ////////////////////

    /**
     * 切换配置的节点网络
     */
    const changeNodeNetworkSelect = function () {
        $('select.nodeNetworkSelect').closest('div.popover.in').remove();
        let id = $(this).attr('id');
        let popoverTag = $(`#${id}_popovers`);
        let content = $(this).find('option:selected').html();
        popoverTag.data('content', content).attr('data-content', content).popover();
        $(this).attr('title', content);
    };

    /**
     * 初始化集群配置
     */
    const initClusterConfig = () => {
        // if (typeof timerTask.CLUSTER_CONFIG_TIMER !== 'undefined') {
        //     clearTimeout(timerTask.CLUSTER_CONFIG_TIMER);
        //     timerTask.CLUSTER_CONFIG_TIMER = undefined;
        // }
        queryClusterConfig().then(data => {
            if (!data.config_flag) {
                return;
            }
            if (loginClusterStatus === null) {  // 这里只赋值一次
                loginClusterStatus = parseInt(data.cluster_status);
                if (
                    loginClusterStatus !== CLUSTER_STATUS_ENUM.STARTED &&
                    loginClusterStatus !== CLUSTER_STATUS_ENUM.STOPPED &&
                    loginClusterStatus !== CLUSTER_STATUS_ENUM.STARTING &&
                    loginClusterStatus !== CLUSTER_STATUS_ENUM.STOPPING
                ) {  // 登录状态只管 停止、启动、启动中、停止中 这四种状态之间的切换
                    loginClusterStatus = null;
                }
            } else {  // 表示已经赋值过了
                let currentStatus = parseInt(data.cluster_status);
                if (loginClusterStatus === CLUSTER_STATUS_ENUM.STARTED || loginClusterStatus === CLUSTER_STATUS_ENUM.STOPPING) {  // 处于 已启用 或 停止中
                    if (currentStatus === CLUSTER_STATUS_ENUM.STOPPED) {  // 表示集群已停止
                        jumpToLoginPage(data);
                    }
                } else if (loginClusterStatus === CLUSTER_STATUS_ENUM.STOPPED || loginClusterStatus === CLUSTER_STATUS_ENUM.STARTING) {  // 处于 未启用 或 启动中
                    if (currentStatus === CLUSTER_STATUS_ENUM.STARTED) {  // 表示集群已启动
                        jumpToLoginPage(data);
                    }
                }
            }
            if (lastClusterStatus === null) {
                lastClusterStatus = parseInt(data.cluster_status);
            } else {
                let currentStatus = parseInt(data.cluster_status);
                if (currentStatus !== lastClusterStatus) {  // 这里要延迟一轮在修改
                    if (!changeLastClusterStatusFlag) {
                        changeLastClusterStatusFlag = true;
                    } else {
                        lastClusterStatus = currentStatus;
                        changeLastClusterStatusFlag = false;
                    }
                }
            }
            initHasConfig(data);
            // timerTask.CLUSTER_CONFIG_TIMER = setTimeout(initClusterConfig, CLUSTER_CONFIG_INTERVAL);
        });
    };

    /**
     * 获取集群配置信息
     * @returns {Promise<unknown>}
     */
    const queryClusterConfig = () => {
        return new Promise((resolve, reject) => {
            pAjaxRequest({}, '/api/v1/cluster/config', 'GET', res => {
                if (!res.success) {
                    reject(new Error(res.message));
                } else {
                    resolve(/** @type {DbConfig} */ res.data);
                }
            });
        });
    };

    /**
     * 显示已配置的集群信息
     */
    const initHasConfig = data => {
        $('.not-config').hide();
        $('.has-config').show();
        $('#clusterOperateProgressDiv').hide();
        let clusterNodeSelect = $('#clusterNodeSelect');

        $('.cluster-status-span').hide();
        $('.cluster-operation-btn').hide();
        switch (data.cluster_status) {
            case CLUSTER_STATUS_ENUM.STARTED:
                $('#clusterStatusOnLabel').show();
                $('#stopCluster').show();
                clusterNodeSelect.removeAttr('disabled');
                $('#changeConfig').show();
                break;
            case CLUSTER_STATUS_ENUM.STARTING:
                $('#clusterStatusStartingLabel').show();
                clusterNodeSelect.attr('disabled', 'disabled');
                $('#changeConfig').hide();
                break;
            case CLUSTER_STATUS_ENUM.STOPPED:
                $('#clusterStatusOffLabel').show();
                $('#startCluster').show();
                clusterNodeSelect.attr('disabled', 'disabled');
                $('#changeConfig').show();
                break;
            case CLUSTER_STATUS_ENUM.STOPPING:
                $('#clusterStatusStoppingLabel').show();
                clusterNodeSelect.attr('disabled', 'disabled');
                $('#changeConfig').hide();
                break;
            case CLUSTER_STATUS_ENUM.ERROR:
                $('#clusterStatusErrorLabel').show();
                $('#stopCluster').show();
                clusterNodeSelect.attr('disabled', 'disabled');
                $('#changeConfig').show();
                break;
            case CLUSTER_STATUS_ENUM.SWITCHING:
                $('#clusterStatusSwitchingLabel').show();
                clusterNodeSelect.attr('disabled', 'disabled');
                $('#changeConfig').hide();
                break;
            case CLUSTER_STATUS_ENUM.TAKEOVER:
                $('#clusterStatusTakeoverLabel').show();
                clusterNodeSelect.attr('disabled', 'disabled');
                $('#changeConfig').hide();
                break;
        }
        $('#clusterServiceIp').html(data.config_service_ip);
        if (
            !clusterNodeSelect.html().trim().length || // 不会实时刷新
            (lastClusterStatus === CLUSTER_STATUS_ENUM.SWITCHING && data.cluster_status === CLUSTER_STATUS_ENUM.STARTED)
        ) {
            let options = ``;
            for (const nodeInfo of data.node_config) {
                let selected = NODE_ROLE_ENUM.MASTER === nodeInfo.node_role ? 'selected' : '';
                let disabled = nodeInfo.online_flag ? '' : 'disabled';
                let text = `${nodeInfo.node_hostname}(${nodeInfo.node_ip})`;
                if (!nodeInfo.online_flag) {
                    text = '(' + LANG.UI_VISUAL_OFF_LINE + ')' + text;
                }
                options += `<option ${disabled} data-ip="${nodeInfo.node_ip}" data-role="${nodeInfo.node_role}"
                    value="${nodeInfo.node_uuid}" ${selected}>
                    ${text}
                </option>`;
            }
            clusterNodeSelect.html(options);
            $('#switchMasterNode').hide();
        }
        initTopological(data);
        initClusterOperateProgress(data);
    };

    /**
     * 初始化集群操作进度
     * @param data
     */
    const initClusterOperateProgress = data => {
        switch (data.cluster_status) {
            case CLUSTER_STATUS_ENUM.STARTING:
            case CLUSTER_STATUS_ENUM.STOPPING:
            case CLUSTER_STATUS_ENUM.SWITCHING:
                let operationProgress = parseInt(data.operation_progress);
                if (isNaN(operationProgress)) {
                    operationProgress = 0;
                }
                if (operationProgress >= 100) {
                    operationProgress = 100;
                } else if (operationProgress < 0) {
                    operationProgress = 0;
                }
                $('#clusterOperateProgressDiv').show();
                $('#clusterOperateProgressValue')
                    .prop('aria-valuenow', operationProgress)
                    .css('width', operationProgress + '%')
                    .html(operationProgress + '%');
                break;
            case CLUSTER_STATUS_ENUM.STARTED:
            case CLUSTER_STATUS_ENUM.STOPPED:
            case CLUSTER_STATUS_ENUM.ERROR:
            case CLUSTER_STATUS_ENUM.TAKEOVER:
                $('#clusterOperateProgressDiv').hide();
                break;
        }
    };

    /**
     * 获取屏幕宽度的修正偏移 - 适配不同宽度的屏幕
     */
    const getScreenOffsetWidth = () => {
        let offset = {
            cluster_offset_x: 0,
            cluster_text_rate: 2.2,
            node_offset_x: -20,
            image_rate: 1.3,
        };
        if (window.innerWidth <= 1280) {
            offset = {
                cluster_offset_x: 80,
                cluster_text_rate: 1.4,
                node_offset_x: -100,
                image_rate: 0.8,
            };
        } else if (window.innerWidth <= 1366) {
            offset = {
                cluster_offset_x: 60,
                cluster_text_rate: 1.6,
                node_offset_x: -80,
                image_rate: 0.9,
            };
        } else if (window.innerWidth <= 1440) {
            offset = {
                cluster_offset_x: 40,
                cluster_text_rate: 1.8,
                node_offset_x: -60,
                image_rate: 1,
            };
        } else if (window.innerWidth <= 1600) {
            offset = {
                cluster_offset_x: 40,
                cluster_text_rate: 1.9,
                node_offset_x: -40,
                image_rate: 1.1,
            };
        } else if (window.innerWidth <= 1700) {
            offset = {
                cluster_offset_x: 30,
                cluster_text_rate: 2,
                node_offset_x: -50,
                image_rate: 1.2,
            };
        } else if (window.innerWidth <= 1800) {
            offset = {
                cluster_offset_x: 20,
                cluster_text_rate: 2,
                node_offset_x: -40,
                image_rate: 1.2,
            };
        }
        return offset;
    };

    /**
     * 获取节点更多信息的tooltip html
     * @param params
     * @param color
     * @param prefixName
     * @param offlineModuleDes
     * @return {string}
     */
    const getMoreNodeTooltipHtml = (params, color, prefixName, offlineModuleDes) => {
        if (!params.data.more_node) {
            let showName = params.data.node_hostname + '(' + params.data.network_ip + ')';
            if (NODE_ROLE_ENUM.MASTER === params.data.node_role) {
                showName = prefixName + '(' + LANG.UI_CLUSTER_MASTER_NODE + ')' + showName;
            } else {
                showName = prefixName + '(' + LANG.UI_CLUSTER_BACKUP_NODE + ')' + showName;
            }
            let des = `<span style="color: ${color}">${showName}</span>`;
            if (offlineModuleDes) {
                des += `<br/><span style="color: ${color}">${LANG.UI_CLUSTER_NODE_ABNORMAL_MODULE_TITLE}: ${offlineModuleDes}</span>`;
            }
            return des;
        }
        let ret = `
        <div id="test1" class="node-more">
            <div class="node-more__header">
                <span class="text">${LANG.UI_CLUSTER_MORE_NODE}</span>
            </div>
            <div class="node-list">
        `;
        for (const _nodeInfo of params.data.node_list) {
            let onlineClass = 'label-success';
            let onlineText = LANG.UI_VISUAL_ONLINE;
            let _offlineModuleDes = ``;
            if (parseInt(_nodeInfo.node_status) === NODE_OPERATE_STATUS_ENUM.OFFLINE) {
                onlineText = LANG.UI_VISUAL_OFF_LINE;
                onlineClass = 'label-default';
            } else if (!_nodeInfo.online_flag) {
                onlineText = LANG.UI_NODE_ABNORMAL;
                onlineClass = 'label-warning';
                _offlineModuleDes = _nodeInfo.node_offline_module_des;
            }
            ret += `
            <div class="node-more-item">
                <div class="node-more__left">
                    <span class="hostname">
                        ${_nodeInfo.node_hostname}(${_nodeInfo.network_ip})
                    </span>
                    <span class="nicname">
                        ${LANG.UI_NODE_NETWORK_NAME}: ${_nodeInfo.network_name}
                    </span>
                    <span class="offline-module ${_offlineModuleDes ? '' : 'display-none'}">
                        ${LANG.UI_CLUSTER_NODE_ABNORMAL_MODULE_TITLE}: ${_offlineModuleDes}
                    </span>
                </div>
                <div class="node-more__right">
                    <span class="label ${onlineClass}">${onlineText}</span>
                </div>
            </div>
            `;
        }
        ret += `</div></div>`;
        return ret;
    };

    /**
     * 初始化集群关系
     */
    const initTopological = (data) => {
        let screenOffset = getScreenOffsetWidth();
        let clusterX = 50 + screenOffset.cluster_offset_x;
        let clusterY = 500;
        let clusterImg = `img/platform/cluster/cluster-139x124.png`;
        if (parseInt(data.cluster_status) === CLUSTER_STATUS_ENUM.STOPPED) {
            clusterImg = `img/platform/cluster/cluster-offline-139x124.png`;
        } else if (parseInt(data.cluster_status) === CLUSTER_STATUS_ENUM.ERROR) {
            clusterImg = `img/platform/cluster/cluster-abnormal-139x124.png`;
        }
        /**
         * @type {Array<Object>}
         */
        let nodes = [{
            nodeName: data.cluster_name + '（' + data.config_service_ip + '）',
            value: [clusterX, clusterY],
            label: {
                rich: {
                    img: {
                        backgroundColor: {
                            image: clusterImg,
                        },
                        width: 176 * screenOffset.image_rate,
                        height: 157 * screenOffset.image_rate,
                        align: 'center',
                        verticalAlign: 'center',
                    },
                    config_service_ip: {
                        padding: [12, 8, 8, 8],
                        borderWidth: 1,
                        borderRadius: 2,
                        // borderColor: `#0FBF98`,
                        fontSize: 12,
                        fontWeight: 'bold',
                        color: `#666`,
                        align: 'center',
                        verticalAlign: 'center',
                        backgroundColor: `#ffffff00`,
                    },
                },
                lineHeight: 12 * screenOffset.cluster_text_rate,
                formatter: (params) => {
                    return [
                        `{img|}`,
                        `\n`,
                        `\n`,
                        `{config_service_ip|${params.data.nodeName}}`,
                    ].join('\n');
                },
            },
            tooltip: {
                show: true,
                // alwaysShowContent: true,
                // appendToBody: true,
                // triggerOn: 'click',
                // confine: true,
                formatter: params => {
                    return `<span style="color: #333">${params.data.nodeName}</span>`;
                },
            },
        }];
        let lines = [];  // 这个是集群向在线节点的线
        let lines2 = [];  // 这个是集群向离线节点的线
        let lines3 = [];  // 这个是在线节点往服务器的线
        // 定义y轴的位置(相对与坐标轴的位置 x: 0~1000, y: 0~1000)
        let yNodePositions = {
            1: [clusterY],
            2: [50, 950],
            3: [50, 500, 950],
            4: [50, 350, 650, 950],
            5: [50, 275, 500, 725, 950],
        };
        // 最大显示5个节点
        let moreFlag = data.node_config.length > 5;
        let nodeCount = moreFlag ? 5 : data.node_config.length;
        data.node_config.sort((a, b) => a.node_role - b.node_role)
        let yNodePosition = yNodePositions[nodeCount];
        yNodePosition = yNodePosition.reverse();
        for (let index in data.node_config) {
            index = parseInt(index);
            if (index >= nodeCount) {
                continue;
            }
            // 构建节点
            let nodeInfo = data.node_config[index];
            let image = `img/platform/cluster/node-62x124.png`;
            let color = `#333`;
            let x = 800 + screenOffset.node_offset_x;
            let y = yNodePosition[index];
            let moreNode = false;
            let prefixName = ``;
            let offlineModuleDes = '';
            if (parseInt(data.cluster_status) === CLUSTER_STATUS_ENUM.STOPPED) {
                image = `img/platform/cluster/node-offline-62x124.png`;
            }
            if (4 === index && moreFlag) {
                image = `img/platform/cluster/node-more-62x124.png`;
                moreNode = true;
                nodeInfo.online_flag = true;
            } else if (nodeInfo.node_status === NODE_OPERATE_STATUS_ENUM.OFFLINE) {
                prefixName = '(' + LANG.UI_VISUAL_OFF_LINE + ')';
                color = `#999`;
            } else if (!nodeInfo.online_flag) {
                prefixName = '(' + LANG.UI_NODE_ABNORMAL + ')';
                image = `img/platform/cluster/node-abnormal-62x124.png`;
                color = `#F19F00`;
                offlineModuleDes = nodeInfo.node_offline_module_des;
            }
            nodes.push({
                node_role: nodeInfo.node_role,
                network_ip: nodeInfo.network_ip,
                network_name: nodeInfo.network_name,
                node_uuid: nodeInfo.node_uuid,
                online_flag: nodeInfo.online_flag,
                node_hostname: nodeInfo.node_hostname,
                more_node: moreNode,
                node_list: moreNode ? data.node_config.slice(index, data.node_config.length) : [],
                value: [x, y],
                label: {
                    rich: {
                        img: {
                            backgroundColor: {
                                image,
                            },
                            width: (nodeInfo.online_flag ? 48 : 48) * screenOffset.image_rate,
                            height: 77 * screenOffset.image_rate,
                            align: 'center',
                            verticalAlign: 'center',
                        },
                    },
                    lineHeight: 18,
                    formatter: () => `{img|}`,
                },
                tooltip: {
                    show: true,
                    // alwaysShowContent: true,
                    // appendToBody: true,
                    // triggerOn: 'click',
                    // confine: true,
                    formatter: params => {
                        return getMoreNodeTooltipHtml(params, color, prefixName, offlineModuleDes);
                    },
                },
            });

            // 构建主机名
            nodes.push({
                node_role: nodeInfo.node_role,
                network_ip: nodeInfo.network_ip,
                network_name: nodeInfo.network_name,
                node_uuid: nodeInfo.node_uuid,
                online_flag: nodeInfo.online_flag,
                node_hostname: nodeInfo.node_hostname,
                more_node: moreNode,
                node_list: moreNode ? data.node_config.slice(index, data.node_config.length) : [],
                value: [x + 120 - screenOffset.node_offset_x, y + 50],
                label: {
                    // align: 'right',
                    verticalAlign: 'top',
                    width: 140,
                    backgroundColor: `#ffffff00`,
                    padding: 8,
                    rich: {
                        node_hostname: {
                            fontSize: 12,
                            color,
                            align: 'left',
                        },
                        network_ip: {
                            fontSize: 12,
                            color,
                            align: 'left',
                        },
                        master_node: {
                            fontSize: 12,
                            fontWeight: 'bold',
                            color,
                            align: 'left',
                        },
                        slave_node: {
                            fontSize: 12,
                            color,
                            align: 'left',
                        },
                    },
                    lineHeight: 14,
                    formatter: (params) => {
                        let ret = [];
                        if (NODE_ROLE_ENUM.MASTER === params.data.node_role) {
                            ret.push(`{master_node|${prefixName}${LANG.UI_CLUSTER_MASTER_NODE}:}`);
                        } else if (!params.data.more_node) {
                            ret.push(`{slave_node|${prefixName}${LANG.UI_CLUSTER_BACKUP_NODE}:}`);
                        }
                        if (params.data.more_node) {
                            ret.push(`{node_hostname|${LANG.UI_CLUSTER_MORE_NODE}}`);
                        } else {
                            let maxLength = 22;
                            let hName = params.data.node_hostname;
                            let iName = params.data.network_ip;
                            hName = hName.length > maxLength ? hName.slice(0, maxLength) + '...' : hName;
                            iName = iName.length > maxLength ? iName.slice(0, maxLength) + '...' : iName;
                            ret.push(`{node_hostname|${hName}}`);
                            ret.push(`{network_ip|${iName}}`);
                        }
                        return ret.join(`\n`);
                    },
                },
                tooltip: {
                    show: true,
                    // alwaysShowContent: true,
                    // appendToBody: true,
                    // triggerOn: 'click',
                    // confine: true,
                    formatter: params => {
                        return getMoreNodeTooltipHtml(params, color, prefixName, offlineModuleDes);
                    },
                },
            });
            if (4 === index && moreFlag) {
                lines2.push({
                    coords: [
                        [clusterX, clusterY],
                        [400, clusterY],
                        [400, y],
                        [x, y],
                    ],
                });
            } else if (!nodeInfo.online_flag) {
                lines2.push({
                    coords: [
                        [clusterX, clusterY],
                        [400, clusterY],
                        [400, y],
                        [x, y],
                    ],
                });
            } else {
                lines.push({
                    coords: [
                        [clusterX, clusterY],
                        [400, clusterY],
                        [400, y],
                        [x, y],
                    ],
                });
                lines3.push({
                    coords: [
                        [x, y],
                        [400, y],
                        [400, clusterY],
                        [clusterX, clusterY],
                    ],
                });
            }
        }

        let showEffect = true;
        if (data.cluster_status === CLUSTER_STATUS_ENUM.STOPPED && data.config_flag) {
            showEffect = false;
        }

        let option = {
            title: {
                show: true,
                text: LANG.UI_CLUSTER_TOPOLOGICAL,
                textStyle: {
                    color: '#999',
                    fontSize: 14,
                    fontWeight: 'normal',
                },
                left: '12px',
                top: '12px',
            },
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
                    width: 5,
                    color: `#F2F2F2`,
                },
                effect: {
                    show: true,
                },
                emphasis: {
                    disabled: true,
                },
                data: lines2,
            }, {
                type: 'lines',
                polyline: true,
                coordinateSystem: 'cartesian2d',
                lineStyle: {
                    width: 5,
                    color: `#ECECEC`,
                },
                effect: {
                    show: showEffect,
                    trailLength: 0,
                    symbol: 'roundRect',
                    color: '#89F5D7',
                    symbolSize: 10,
                    period: 6,
                    // constantSpeed: 100,
                    delay: 0,
                    // roundTrip: true,
                },
                emphasis: {
                    disabled: true,
                },
                data: lines,
            }, {
                type: 'lines',
                polyline: true,
                coordinateSystem: 'cartesian2d',
                lineStyle: {
                    width: 5,
                    color: `#ECECEC`,
                },
                effect: {
                    show: showEffect,
                    trailLength: 0,
                    symbol: 'roundRect',
                    color: '#94EBE6',
                    symbolSize: 10,
                    period: 6,
                    // constantSpeed: 100,
                    delay: 0,
                    // roundTrip: true,
                },
                emphasis: {
                    disabled: true,
                },
                data: lines3,
            }, {
                type: 'graph',
                coordinateSystem: 'cartesian2d',
                symbol: 'rect',
                symbolSize: [90, 120],
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
            }],
            animation: false
        };
        topologicalChart.resize();
        topologicalChart.setOption(option);
    };

    //////////////////// 结束-初始化集群配置 ////////////////////

    //////////////////// 开始-修改集群配置事件 ////////////////////

    /**
     * 显示配置集群的抽屉
     */
    const showConfigClusterDrawer = () => {
        Metronic.blockUI({target: '#clusterManager', animate: true});
        queryClusterConfig().then(data => {
            Metronic.unblockUI('#clusterManager');
            if (
                (data.cluster_status === CLUSTER_STATUS_ENUM.STARTED || data.cluster_status === CLUSTER_STATUS_ENUM.ERROR) &&
                data.config_flag
            ) {
                UIToastr.showWarning(LANG.UI_CLUSTER_CONFIG_CLUSTER_TITLE, LANG.UI_CLUSTER_CONFIG_CLUSTER_NOT_IN_STOPPED);
                return;
            }
            $('#configClusterDrawer').data('status', data.cluster_status).drawer('show');
            $('#clusterName').val(data.cluster_name);
            $('#clusterVirtualIp').removeAttr('disabled').val(data.config_service_ip);
            $('#advertInt').removeAttr('disabled').val(data.advert_int).trigger('change');
            $('#advertIntSpinner button.spinner-up, #advertIntSpinner button.spinner-up').removeAttr('disabled');
            if (parseInt(data.cluster_status) !== CLUSTER_STATUS_ENUM.STOPPED && data.config_flag) {
                $('#clusterVirtualIp').attr('disabled', 'disabled');
                $('#advertInt').attr('disabled', 'disabled');
                $('#advertIntSpinner button.spinner-up, #advertIntSpinner button.spinner-up').attr('disabled', 'disabled');
            }
            initClusterNodeTable(data);
            $('#prioritySwitch').bootstrapSwitch('state', data.priority_flag && data.config_flag);
            if (data.priority_flag && data.config_flag) {
                $('.config-priority').hide();
                // $('.config-priority').show();
            } else {
                $('.config-priority').hide();
            }
            $('#configNodePriority').html('');
            if (data.config_flag) {
                for (const nodeInfo of data.node_config) {
                    addNodePriorityLi(nodeInfo.node_hostname, nodeInfo.node_ip, nodeInfo.node_uuid, nodeInfo.config_flag, nodeInfo.node_role);
                }
            }
        });
    };

    /**
     * 添加节点优先级的li
     * @param hostname
     * @param ip
     * @param nodeUuid
     * @param configFlag
     * @param nodeRole
     */
    const addNodePriorityLi = (hostname, ip, nodeUuid, configFlag, nodeRole = NODE_ROLE_ENUM.UNKNOWN) => {
        let current = nodeRole === NODE_ROLE_ENUM.MASTER ? 'current' : '';
        let disabled = nodeRole === NODE_ROLE_ENUM.MASTER && !configFlag ? 'disabled' : '';
        let li = `<li class="node-priority-item ${current} ${disabled}" data-id="${nodeUuid}">
                    <div class="title">${hostname}(${ip})</div>
                    <div class="icon"><i class="viconfont vicon-tuodong"></i></div>
                </li>`;
        $('#configNodePriority').append(li);
    };

    //////////////////// 开始-集群节点 ////////////////////

    const getClusterNodeTableColumns = (configData) => {
        let configNodeList = configData.node_config;
        return [{
            checkbox: true,
            sortable: false,
            width: '2',
            widthUnit: '%',
            formatter: (value, row) => {
                let checked = false;
                let disabled = false;
                for (const configNode of configNodeList) {
                    if (configNode.node_uuid === row.node_uuid) {
                        if (parseInt(configNode.node_role) === NODE_ROLE_ENUM.MASTER) {  // 主节点不能从移除中移除
                            checked = true;
                            disabled = true;
                            break;
                        }
                        if (configData.config_flag) {
                            if (parseInt(configData.cluster_status) !== CLUSTER_STATUS_ENUM.STOPPED) {  // 集群未停止只能添加节点到集群中
                                checked = true;
                                disabled = true;
                                break;
                            }
                            checked = true;
                            disabled = false;
                            break;
                        }
                    }
                }
                if (parseInt(row.node_type) === NODE_ROLE_ENUM.MASTER) {
                    checked = true;
                    disabled = true;
                }
                return {checked, disabled};
            },
        }, {
            title: LANG.UI_CLUSTER_NODE_NAME,
            field: 'ip',
            width: '45',
            widthUnit: '%',
            formatter: (value, row) => {
                value = `${row['host_name']}(${row['ip']})`;
                let color = '#333';
                if (!row.online_flag) {
                    value = '(' + LANG.UI_STORAGE_STATUS_OFFLINE + ')' + value;
                    color = '#999';
                }
                return `<span title="${value}" style="color: ${color}">${value}</span>`;
            },
        }, {
            title: LANG.UI_CLUSTER_NODE_ROLE,
            field: 'node_type',
            width: '15',
            widthUnit: '%',
            sortable: false,
            formatter: (value, row) => {
                if (configData.config_flag) {
                    for (const configNode of configData.node_config) {
                        if (row.node_uuid === configNode.node_uuid) {
                            return parseInt(configNode.node_role) === NODE_ROLE_ENUM.MASTER ? LANG.UI_CLUSTER_MASTER_NODE : LANG.UI_CLUSTER_BACKUP_NODE;
                        }
                    }
                    return LANG.UI_CLUSTER_BACKUP_NODE;
                } else {
                    return parseInt(value) === NODE_ROLE_ENUM.MASTER ? LANG.UI_CLUSTER_MASTER_NODE : LANG.UI_CLUSTER_BACKUP_NODE;
                }
            }
        }, {
            title: LANG.UI_CLUSTER_NODE_NETWORK,
            field: 'network_list',
            sortable: false,
            width: '38',
            widthUnit: '%',
            clickToSelect: false,
            formatter: (networkList, row) => {
                let disabled = '';
                for (const configNode of configNodeList) {
                    if (configData.config_flag && configNode.node_uuid === row.node_uuid) {
                        if (parseInt(configData.cluster_status) !== CLUSTER_STATUS_ENUM.STOPPED) {  // 集群未停止不能修改网卡
                            disabled = 'disabled';
                            break;
                        }
                        row.network_ip = configNode['network_ip'];
                        row.network_name = configNode['network_name'];
                        break;
                    }
                }

                let id = `node-uuid-${row.node_uuid}`;
                let options = ``;
                for (const networkInfo of networkList) {
                    let selected = (networkInfo.network_ip === row.network_ip && row.network_name === networkInfo.network_name) ? 'selected' : '';
                    options += `<option value="${networkInfo.network_uuid}" ${selected} data-ip="${networkInfo.network_ip}" data-name="${networkInfo.network_name}">
                            ${networkInfo.network_ip}(${networkInfo.network_name})
                        </option>`;
                }
                return `
                <a id="${id}_popovers" style="background: transparent" class="popovers" data-container="body" data-trigger="hover"
                    data-html="true" data-placement="left" data-content="">
                    <select class="form-control select2me nodeNetworkSelect" ${disabled} id="${id}">${options}</select>
                </a>
                `;
            },
        }];
    };

    /**
     * 集群节点行点击
     */
    const clusterNodeRowCheck = () => {
        let rows = $('#clusterNodeTable').bootstrapTable('getSelections');
        let nodeLis = $('#configNodePriority li');

        // 从排序列表移除取消选择的节点
        for (const nodeLi of nodeLis) {
            let setFlag = false;
            for (const row of rows) {
                if ($(nodeLi).data('id') === row.node_uuid) {
                    setFlag = true;
                    break;
                }
            }
            if (!setFlag) {
                $(nodeLi).remove();
            }
        }

        // 添加选择的节点到排序列表中
        for (const row of rows) {
            let setFlag = false;
            for (const nodeLi of nodeLis) {
                if ($(nodeLi).data('id') === row.node_uuid) {
                    setFlag = true;
                    break;
                }
            }
            if (!setFlag) {
                addNodePriorityLi(row.host_name, row.ip, row.node_uuid, row.cluster_config_flag, 0);
            }
        }
    };

    /**
     * 设置查询参数
     * @returns {{}}
     */
    const getQueryParams = () => {
        return {
            node_function: NODE_FUNCTION_ENUM.MANAGEMENT,
        };
    };

    /**
     * 初始化集群节点表格
     */
    const initClusterNodeTable = (configData) => {
        window.sessionStorage.removeItem('clusterNodeTable_pageRecord');
        $('#clusterNodeTable').bootstrapTable('destroy').baseTableConfig().init({
            vin_url: `/api/v1/nodes`,
            vin_params: getQueryParams,
            vin_method: 'get',
            sortName: 'ip',
            sortOrder: 'asc',
            pageSize: 10,
            pageList: [10, 20, 50, 100],
            onPostBody: (data) => {
                $('select.nodeNetworkSelect').trigger('change');
                for (const key in data) {
                    data[key]['cluster_config_flag'] = configData.config_flag;
                    data[key]['in_cluster_flag'] = false;
                    if (configData.config_flag) {  // 已配置集群
                        data[key]['node_role'] = NODE_ROLE_ENUM.BACKUP;
                        for (const configNode of configData.node_config) {
                            if (data[key]['node_uuid'] === configNode['node_uuid']) {
                                data[key]['node_role'] = parseInt(configNode['node_role']);
                                data[key]['in_cluster_flag'] = true;
                            }
                        }
                    } else {
                        data[key]['node_role'] = parseInt(data[key]['node_type']);
                    }
                }
                return data;
            },
            onCheck: clusterNodeRowCheck,
            onUncheck: clusterNodeRowCheck,
            onCheckAll: clusterNodeRowCheck,
            onUncheckAll: clusterNodeRowCheck,
            columns: getClusterNodeTableColumns(configData),
        });
    };

    //////////////////// 结束-集群节点 ////////////////////

    /**
     * 隐藏抽屉
     */
    const hideConfigClusterDrawer = () => {
        $('#configClusterDrawer').drawer('hide');
    };

    /**
     * 改变了优先级开关
     */
    const changePrioritySwitch = function () {
        if (this.checked) {
            $('.config-priority').show();
        } else {
            $('.config-priority').hide();
        }
    };

    /**
     * 初始化集群优先级配置的拖动
     */
    const initNodePrioritySortable = () => {
        let priorityParent = $('#configNodePriority').get(0);
        if (prioritySortable) {
            prioritySortable.destroy();
            prioritySortable = null;
        }
        prioritySortable = new Sortable(priorityParent, {
            animation: 150,
            filter: '.disabled',
            // handle: '.icon',
            onUpdate: () => {
                // $(priorityParent).find('li').removeClass('current').first().addClass('current');
            },
            onEnd: (evt) => {
                const list = $(priorityParent).find('li');
                const oldIndex = evt.oldIndex;
                const newIndex = evt.newIndex;
                const oldNode = list[oldIndex]
                const newNode = list[newIndex];
                if ($(oldNode).hasClass('disabled')) {  // 如果第一个节点不支持选择，那么移动到这个前面的节点将设置为第二个
                    $(priorityParent).remove(newNode);
                    $(priorityParent).find('li.disabled').after(newNode);
                }
            },
        });
    };

    /**
     * 自定义列顺序排序
     * @param data
     * @param customOrder
     * @param key
     */
    const customOrderSort = (data, customOrder, key) => {
        let orderMap = customOrder.reduce((item, value, index) => {
            item[value] = index;
            return item;
        }, {});

        data.sort((item1, item2) => {
            let index1 = orderMap[item1[key]];
            let index2 = orderMap[item2[key]];
            return index1 - index2;
        });
    };

    /**
     * 显示至少需要两个节点的弹窗
     * @param nodeList
     * @returns {Promise<unknown>}
     */
    const showLessTwoNodesDialog = (nodeList) => {
        return new Promise((resolve) => {
            if (nodeList.length < 2) {
                bootbox.dialog({
                    className: 'drawer-confirm-bootbox',
                    title: LANG.UI_CLUSTER_CONFIG_CLUSTER_TITLE,
                    message: LANG.UI_CLUSTER_CONFIG_CLUSTER_LEAST_TWO_NODE,
                    buttons: {
                        cancel: {
                            label: LANG.UI_PUBLIC_CANCEL,
                            className: 'btn-default',
                            callback: function () {
                            }
                        },
                        ok: {
                            label: LANG.UI_PUBLIC_CONFIRM,
                            className: 'btn-success',
                            callback: debounce(function () {
                                resolve();
                            }, 300),
                        }
                    }
                });
            } else {
                resolve();
            }
        });
    };

    /**
     * 提交集群配置
     */
    const submitConfigClusterDraw = () => {
        let data = {
            config_service_ip: $('#clusterVirtualIp').val().trim(),
            node_config: [],
            // priority_flag: !!$('#prioritySwitch').get(0).checked,
            priority_flag: false,
            cluster_name: $('#clusterName').val().trim(),
            config_mode: CLUSTER_CONFIG_MODE_ENUM.INIT,
            advert_int: parseInt($('#advertInt').val()),
        };
        let clusterStatus = parseInt($('#configClusterDrawer').data('status'));
        if (clusterStatus !== CLUSTER_STATUS_ENUM.STOPPED && data.config_flag) {
            data.config_mode = CLUSTER_CONFIG_MODE_ENUM.MODIFY;
        }

        if (!data.cluster_name.length) {
            $('#configClusterDrawer .drawer-body .alert.alert-danger').show();
            $('#configClusterDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_CLUSTER_CONFIG_CLUSTER_NAME_NOT_EMPTY);
            return false;
        }

        let nodeList = $('#clusterNodeTable').bootstrapTable('getSelections');
        if (!nodeList.length) {
            $('#configClusterDrawer .drawer-body .alert.alert-danger').show();
            $('#configClusterDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_CLUSTER_CONFIG_CLUSTER_EMPTY_NODE);
            return false;
        }
        showLessTwoNodesDialog(nodeList).then(() => {
            if (data.priority_flag) {
                let nodePriorityList = prioritySortable.toArray();
                customOrderSort(nodeList, nodePriorityList, 'node_uuid');
            }
            for (const index in nodeList) {
                let nodeInfo = nodeList[index];
                let selectedNetwork = $(`#node-uuid-${nodeInfo.node_uuid} option:selected`);
                if (clusterStatus !== CLUSTER_STATUS_ENUM.STOPPED && data.config_flag) {
                    if (nodeInfo.in_cluster_flag) {
                        continue;
                    }
                }
                data.node_config.push({
                    node_uuid: nodeInfo.node_uuid,
                    node_role: parseInt(nodeInfo.node_role),
                    priority: data.priority_flag ? 100 - index : 100,
                    network_ip: selectedNetwork.data('ip'),
                    network_name: selectedNetwork.data('name'),
                });
            }
            if (!data.node_config.length) {
                $('#configClusterDrawer .drawer-body .alert.alert-danger').show();
                $('#configClusterDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_CLUSTER_CONFIG_CLUSTER_NO_NODE);
                return false;
            }
            $('#configClusterDrawer .drawer-body .alert-danger').hide();
            if (!ipV4V6(data.config_service_ip)) {
                $('#configClusterDrawer .drawer-body .alert.alert-danger').show();
                $('#configClusterDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_CLUSTER_CONFIG_CLUSTER_VIRTUAL_IP_INVALID);
                return false;
            }
            // 验证VIP与节点IP是否重复
            for (const index in nodeList) {
                for (const networkInfo of nodeList[index].network_list) {
                    if (networkInfo.network_ip === data.config_service_ip) {
                        $('#configClusterDrawer .drawer-body .alert.alert-danger').show();
                        $('#configClusterDrawer .drawer-body .alert.alert-danger .message').html(LANG.UI_CLUSTER_CONFIG_CLUSTER_VIRTUAL_IP_SAME_TO_NODE_IP);
                        return false;
                    }
                }
            }
            Metronic.blockUI({target: '#configClusterDrawer', animate: true});
            pAjaxRequest(data, `/api/v1/cluster/config`, 'PUT', res => {
                Metronic.unblockUI('#configClusterDrawer');
                if (res.success) {
                    UIToastr.showSuccess(LANG.UI_CLUSTER_CONFIG_CLUSTER_TITLE, res.message);
                    $('#configClusterDrawer').drawer('hide');
                    initClusterConfig();  // 重新加载配置
                } else {
                    UIToastr.showWarning(LANG.UI_CLUSTER_CONFIG_CLUSTER_TITLE, res.message);
                }
            });
        });
    };

    //////////////////// 结束-修改集群配置事件 ////////////////////

    //////////////////// 开始-已配置集群的事件处理 ////////////////////

    /**
     * 判断集群节点的版本
     * @returns {Promise<{dialog: *, node_config: *}>}
     */
    const judgeClusterNodeVersion = (dialog) => {
        return new Promise(resolve => {
            queryClusterConfig().then(data => {
                if (!data.config_flag) {
                    return;
                }
                let nodeVersion = null;
                for (const nodeInfo of data.node_config) {
                    if (nodeVersion === null) {
                        nodeVersion = nodeInfo.node_version;
                    } else {
                        if (nodeVersion !== nodeInfo.node_version) {
                            UIToastr.showWarning(LANG.UI_CLUSTER_START_CLUSTER, LANG.UI_CLUSTER_START_CLUSTER_NODE_VERSION_NOT_SAME);
                            dialog.modal('hide');
                            return;
                        }
                    }
                }
                resolve({dialog, node_config: data.node_config});
            });
        });
    };

    /**
     * 判断集群节点的数量是否符合要求
     * @param dialog
     * @param node_config
     * @returns {Promise<unknown>}
     */
    const judgeClusterNodeCount = ({dialog, node_config}) => {
        return new Promise(resolve => {
            if (node_config.length < 2) {
                UIToastr.showWarning(LANG.UI_CLUSTER_START_CLUSTER, LANG.UI_CLUSTER_START_CLUSTER_AT_LEAST_TWO_NODE);
                return;
            }
            resolve({dialog, node_config});
        });
    };

    /**
     * 测试集群节点的连通性
     * @param dialog
     * @param node_config
     * @returns {Promise<unknown>}
     */
    const testClusterNodeConnectivity = ({dialog, node_config}) => {
        return new Promise(resolve => {
            let reqData = {
                ip_list: [],
                interface: '',
            }
            for (const nodeInfo of node_config) {
                if (nodeInfo.node_role === NODE_ROLE_ENUM.MASTER) {
                    reqData.interface = nodeInfo.network_ip;
                } else {
                    reqData.ip_list.push(nodeInfo.network_ip);
                }
            }
            let metronicTarget = '.' + dialog.attr('class').split(' ').join('.') + ' .modal-content';
            Metronic.blockUI({target: metronicTarget, animate: true});
            pAjaxRequest(reqData, `/api/v1/system/ip_reachable/batch_check`, `POST`, res => {
                Metronic.unblockUI(metronicTarget);
                if (!res.success) {
                    UIToastr.showWarning(LANG.UI_CLUSTER_START_CLUSTER, res.message);
                    return;
                }
                if (parseInt(res.data.statistics.unreachable) !== 0) {
                    let unreachableList = [];
                    for (const checkedIpItem of res.data.checked_ip_list) {
                        if (!checkedIpItem.reachable) {
                            unreachableList.push(checkedIpItem.ip);
                        }
                    }
                    let message = LANG.UI_CLUSTER_START_CLUSTER_NODE_CONNECTIVITY.replace('%S', `<span style="">${unreachableList.join(', ')}</span>`);

                    UIToastr.showWarning(LANG.UI_CLUSTER_START_CLUSTER, message);
                } else {
                    resolve(dialog);
                }
            });
        });
    }

    /**
     * 启动集群
     */
    const startCluster = () => {
        let clusterServiceIp = $('#clusterServiceIp').html().trim();
        let dialog = bootbox.dialog({
            title: `<i class="viconfont vicon-ge_play"></i><span class="pl10">${LANG.UI_CLUSTER_START_CLUSTER}</span>`,
            message: LANG.UI_CLUSTER_START_CLUSTER_TIPS.replace(/%s/, clusterServiceIp),
            buttons: {
                cancel: {
                    label: LANG.UI_PUBLIC_CANCEL,
                    className: 'btn-default',
                    callback: function () {
                    }
                },
                ok: {
                    label: LANG.UI_PUBLIC_CONFIRM,
                    className: 'btn-primary',
                    callback: debounce(function () {
                        judgeClusterNodeVersion(dialog)
                            .then(judgeClusterNodeCount)
                            .then(testClusterNodeConnectivity)
                            .then(startSubmit);
                        return false;
                    }, 300, false)
                },
            }
        });
    };

    const startSubmit = (dialog) => {
        let target = '.modal-content';
        Metronic.blockUI({target, animate: true});
        pAjaxRequest({}, `/api/v1/cluster/start`, 'POST', res => {
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLUSTER_START_CLUSTER, res.message);
            } else {
                UIToastr.showSuccess(LANG.UI_CLUSTER_START_CLUSTER, res.message);
            }
            Metronic.unblockUI(target);
            dialog.modal('hide');
        });
    };

    /**
     * 集群启动成功，跳转到集群登录页面
     * @param configData
     */
    const jumpToLoginPage = (configData) => {
        if (jumpFlag) {
            return;
        }
        let routeModule = btoa('resmanagement', true);
        let routeName = btoa('backup_manager', true);
        let subRouteName = btoa('cluster_manager', true);
        let title = '';
        let message = '';
        let url = '';
        switch (parseInt(configData.cluster_status)) {
            case CLUSTER_STATUS_ENUM.STARTED:  // 启动集群
                title = `<i class="viconfont vicon-ge_play"></i><span class="pl10">${LANG.UI_CLUSTER_START_CLUSTER}</span>`;
                message = LANG.UI_CLUSTER_START_CLUSTER_FINISH.replace(/%s/, configData.config_service_ip);
                url = `https://${configData.config_service_ip}/login.php?force_route_jump=1&module=${routeModule}&name=${routeName}&sub_name=${subRouteName}`;
                break;
            case CLUSTER_STATUS_ENUM.STOPPED:  // 停止集群
                let masterIp = configData.node_config[0].network_ip;
                for (const nodeConfig of configData.node_config) {
                    if (parseInt(nodeConfig.node_role) === NODE_ROLE_ENUM.MASTER) {
                        masterIp = nodeConfig.network_ip;
                    }
                }
                title = `<i class="viconfont vicon-a-Pause-onezanting"></i><span class="pl10">${LANG.UI_CLUSTER_STOP_CLUSTER}</span>`;
                message = LANG.UI_CLUSTER_STOP_CLUSTER_FINISH.replace(/%s/, masterIp);
                url = `https://${masterIp}/login.php?force_route_jump=1&module=${routeModule}&name=${routeName}&sub_name=${subRouteName}`;
                break;
            default:
                return;
        }

        let jumpTimeout = setTimeout(() => {
            window.location = url;
        }, 5000);
        bootbox.dialog({
            title,
            message,
            buttons: {
                ok: {
                    label: LANG.UI_PUBLIC_CONFIRM,
                    className: 'btn-primary',
                    callback: function () {
                        // 这里不能直接跳转，可能会nginx还未重启或启动
                        // clearTimeout(jumpTimeout);
                        // window.location = url;
                    }
                },
            }
        });
        jumpFlag = true;
    };

    /**
     * 停止集群
     */
    const stopCluster = () => {
        let dialog = bootbox.dialog({
            title: `<i class="viconfont vicon-ge_suspend-copy"></i><span class="pl10">${LANG.UI_CLUSTER_STOP_CLUSTER}</span>`,
            message: LANG.UI_CLUSTER_STOP_CLUSTER_TIPS,
            buttons: {
                cancel: {
                    label: LANG.UI_PUBLIC_CANCEL,
                    className: 'btn-default',
                    callback: function () {
                    }
                },
                ok: {
                    label: LANG.UI_PUBLIC_CONFIRM,
                    className: 'btn-primary',
                    callback: debounce(function () {
                        stopSubmit(dialog);
                        return false;
                    }, 300, false)
                },
            }
        });
    };

    const stopSubmit = (dialog) => {
        let target = '.modal-content';
        Metronic.blockUI({target, animate: true});
        pAjaxRequest({}, `/api/v1/cluster/stop`, 'POST', res => {
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLUSTER_STOP_CLUSTER, res.message);
            } else {
                UIToastr.showSuccess(LANG.UI_CLUSTER_STOP_CLUSTER, res.message);
            }
            Metronic.unblockUI(target);
            dialog.modal('hide');
        });
    };

    /**
     * 切换集群主节点事件
     */
    const changeMasterNodeSelect = function () {
        if (1 !== parseInt($(this).find('option:selected').data('role'))) {
            $('#switchMasterNode').show();
        } else {
            $('#switchMasterNode').hide();
        }
    };

    /**
     * 切换集群主节点
     */
    const switchMasterNode = () => {
        let targetNode = $(`#clusterNodeSelect option:selected`);
        let targetNodeIp = targetNode.data('ip');
        let dialog = bootbox.dialog({
            title: `<i class="fa fa-refresh"></i><span class="pl10">${LANG.UI_CLUSTER_SWITCH_MASTER_CLUSTER}</span>`,
            message: LANG.UI_CLUSTER_SWITCH_MASTER_CLUSTER_TIPS.replace(/%s/, targetNodeIp),
            buttons: {
                cancel: {
                    label: LANG.UI_PUBLIC_CANCEL,
                    className: 'btn-default',
                    callback: function () {
                    }
                },
                ok: {
                    label: LANG.UI_PUBLIC_CONFIRM,
                    className: 'btn-primary',
                    callback: debounce(function () {
                        submitSwitchMasterNode(dialog, targetNodeIp);
                        return false;
                    }, 300, false)
                },
            }
        });
    };

    const submitSwitchMasterNode = (dialog, targetNodeIp) => {
        let data = {
            source_node_uuid: $(`#clusterNodeSelect option[data-role="1"]`).val(),
            target_node_uuid: $(`#clusterNodeSelect option:selected`).val(),
        };
        let target = '.modal-content';
        Metronic.blockUI({target, animate: true});
        pAjaxRequest(data, `/api/v1/cluster/set_master`, 'POST', res => {
            Metronic.unblockUI(target);
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_CLUSTER_SWITCH_MASTER_CLUSTER, res.message);
            } else {
                UIToastr.showSuccess(LANG.UI_CLUSTER_SWITCH_MASTER_CLUSTER, res.message);
                $('#switchMasterNode').hide();
                $(`#clusterNodeSelect`).attr('disabled', 'disabled')
            }
            dialog.modal('hide');
        });
    };

    //////////////////// 结束-已配置集群的事件处理 ////////////////////

    //////////////////// 开始-初始化集群操作日志 ////////////////////

    /**
     * 查询集群操作日志
     */
    const queryClusterOperateLog = () => {
        return new Promise(resolve => {
            pAjaxRequest({}, `/api/v1/cluster/log`, 'GET', res => {
                if (!res.success) {
                    resolve([]);
                    return;
                }
                resolve(/** @type ClusterOperateLogList */ res.data);
            });
        });
    };

    /**
     * 设置集群的操作日志
     * @param operateLogList
     */
    const setClusterOperateLog = (operateLogList) => {
        let lis = ``;
        for (const operateLog of operateLogList) {
            const newRegex = /<span[^>]*>|<\/span>/gi;
            let title = operateLog.op_message.replace(newRegex, '');
            lis += `
            <li class="log-item">
                <div class="log-item__left" title="${title}">${operateLog.op_message}</div>
                <div class="log-item__right">${operateLog.op_time}</div>
            </li>
            `;
        }
        if (!lis.length) {
            lis = LANG.UI_CLUSTER_OPERATE_LOG_EMPTY;
        }
        $('#clusterOperateLogUl').html(lis);
    };

    /**
     * 初始化集群操作日志
     */
    const initClusterOperateLog = () => {
        // if (typeof timerTask.CLUSTER_OPERATE_LOG_TIMER !== 'undefined') {
        //     clearTimeout(timerTask.CLUSTER_OPERATE_LOG_TIMER);
        //     timerTask.CLUSTER_OPERATE_LOG_TIMER = undefined;
        // }
        queryClusterOperateLog().then(operateLogList => {
            setClusterOperateLog(operateLogList);
            // timerTask.CLUSTER_OPERATE_LOG_TIMER = setTimeout(initClusterOperateLog, CLUSTER_OPERATE_LOG_INTERVAL);
        });
    };

    //////////////////// 结束-初始化集群操作日志 ////////////////////

    /**
     * 初始化定时器
     */
    const initInterval = () => {
        timerTask.clusterConfigInterval = setInterval(() => {
            if (!$('#clusterManager').length) {
                clearInterval(timerTask.clusterConfigInterval);
                return;
            }
            initClusterConfig();
        }, CLUSTER_CONFIG_INTERVAL);
        timerTask.clusterOperateLogInterval = setInterval(() => {
            if (!$('#clusterManager').length) {
                clearInterval(timerTask.clusterOperateLogInterval);
                return;
            }
            initClusterOperateLog();
        }, CLUSTER_OPERATE_LOG_INTERVAL);
    };

    return {
        init: () => {
            initListeners();
            initSpinner();
            initNodePrioritySortable();
            initInterval();
            initClusterConfig();
            initClusterOperateLog();
        },
    };
})();

jQuery(document).ready(() => {
    ClusterManager.init();
});
