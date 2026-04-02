var DbData = function () {

    // 源机树(所有)
    let zTreeAgent= [];
    // 时间范围标志
    let time_unit;
    // dataZoom的起始百分比,此处暂定70%,根据实际效果调整.(可以根据加载的时间区间范围确定起始百分比)
    let _chartStartPercent = 70;
    let _hisPortletBodyWidth = 0;
    let _hisportletBodyHeight = 0;
    // dataZoom的结束百分比,此处暂定100%,根据实际效果调整.
    let _chartEndPercent = 100;
    // 选中的时间点
    var _checkTime = '';
    // 恢复-恢复源相关联的同步源(同步任务的源机uuid)
    let syncSourceAgentUuid = '';
    // 恢复源机uuid
    let recoverSourceAgentUuid = '';
    // 时间轴默认加载的间隔
    let _timeInterval = 1;


    const initListeners = () =>{
        // 切换时间范围
        $('#changeTimeInterval').on('change', changeTimeIntervalType);

        // 点击li事件信息  获取所有的事件信息
        $('#timepointType li').on('click', getTabInfo);
    }

    /**
     * 切换时间范围
     */
    var changeTimeIntervalType = function () {
        // 时间范围
        var timeIntervalType = $('#changeTimeInterval').val();
        _chartStartPercent = 70;
        _timeInterval = timeIntervalType;
        getAgentTimelineData(recoverSourceAgentUuid);
    }

    /**
     * 点击nav触发事件
     */
    const getTabInfo = function () {
        let attrId = $(this).attr("id")
        switch (attrId) {
            case "recoveryAnytimeLi":
                $('#anyPointTime').show();
                $('#eventInfo').hide();
                $('#transactionInfo').hide();
                break;
            case "agentTransactionInfoLi":
                $('#anyPointTime').hide();
                $('#eventInfo').hide();
                $('#transactionInfo').show();
                _checkTime = '';
                loadCheckAgentEventInfo();
                break;
        }
    }



    /**
     * step1-客户端树
     */
    const initAgentTree = () => {
        Metronic.blockUI({target: "#agent_tree", animate: true});
        let param = {db_type: CONF.DB_TYPE.ORACLE};
        // 获取实例列表
        pAjaxRequest(param, '/api/v1/dbcdp/restore/instances', 'GET', res => {
            // 过滤掉有任务的的客户端
            let rows = res.data.rows.filter(item => item.db_cdp_recover_info.length != 0);
            setOracleRecoverTree(rows,'#agent_tree',checkOracleRecoverNode);
            //测试
            // setOracleRecoverTree(res.data.rows,'#agent_tree',checkOracleRecoverNode);
        });
    }

    /**
     * initInstanceTree的回调函数
     * @param instanceList
     */
    const setOracleRecoverTree = (instanceList,id,checkedFn) => {
        // 单节点
        let standaloneNodes = [];
        // 集群
        let clusterNodes = {};

        for (const instanceInfo of instanceList) {
            if (!instanceInfo.cluster_info.cluster_flag) {
                // 单节点
                standaloneNodes.push(instanceInfo);
            } else {
                if (typeof clusterNodes[instanceInfo.cluster_info.cluster_uuid] === 'undefined') {
                    clusterNodes[instanceInfo.cluster_info.cluster_uuid] = {
                        clsuter_info: instanceInfo.cluster_info,
                        instance_list: [],
                    };
                }
                clusterNodes[instanceInfo.cluster_info.cluster_uuid].instance_list.push(instanceInfo);
            }
        }
        // 构建单机还是实例
        let nodes = [];
        let noInstance = true;
        if (Object.keys(clusterNodes).length) {
            // 如果有集群
            nodes.push({
                id: 'cluster',
                pId: 0,
                name: LANG.UI_DB_CLUSTER,
                open: true,
                nocheck: true,
                eventtype: 'category',
                isParent: true,
                icon: './img/vm/hostcluster.png',
            });
            noInstance = false;
        }
        if (standaloneNodes.length) {
            // 如果有单节点
            nodes.push({
                id: 'standalone',
                pId: 0,
                name: LANG.UI_DB_STANDALONE,
                open: true,
                nocheck: true,
                eventtype: 'category',
                isParent: true,
                icon: './img/vm/host.png',
            });
            noInstance = false;
        }
        if (noInstance) {
            // 如果无实例（集群/单节点）
            $("#noagenttips").show();
            $("#agent_tree").hide();
            return;
        }
        for (const standaloneNode of standaloneNodes) {
            let chkDisabled = false;
            let name = standaloneNode.instance_name + '(' + standaloneNode.agent_info.ip + ')';  // 实例名+ip
            if (!standaloneNode.agent_info.authorization_module.dbcdp) {  // 未授权
                // 未授权不能被点击
                chkDisabled = true;
                name = `(${LANG.UI_DB_RECOVERY_AGENT_UNAUTHIORIZED})` + name;
            } else if (!standaloneNode.agent_info.online_flag) {
                // 离线不能被点击
                chkDisabled = true;
                name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
            }
            nodes.push({
                id: standaloneNode.app_uuid,
                pId: 'standalone',
                name,
                title: name,
                isParent: false,
                eventtype: 'instance',
                chkDisabled,
                icon: './img/platform/storage.png',
                instance_name: standaloneNode.instance_name,
                app_uuid: standaloneNode.app_uuid,
                app_type:standaloneNode.app_type,
                app_version:standaloneNode.app_version,
                agent_uuid: standaloneNode.agent_info.agent_uuid,
                cluster_flag: false,
                cluster_uuid: '',
                online_flag: standaloneNode.agent_info.online_flag,
                net_model: standaloneNode.agent_info.net_model,
                auth_flag: standaloneNode.agent_info.authorization_module.dbcdp,
                db_cdp_recover_info:[],  // 是否有任务运行
                delay_time_flag:standaloneNode.delay_time_flag,
                source_agent_uuid:standaloneNode.source_agent_uuid
            });
        }
        for (const clusterUuid in clusterNodes) {
            let clusterInfo = clusterNodes[clusterUuid].clsuter_info;
            let clusterOnlineFlag = false;
            let clusterAuthFlag = true;
            for (const instanceInfo of clusterNodes[clusterUuid].instance_list) {
                let chkDisabled = false;
                let name = instanceInfo.instance_name + '(' + instanceInfo.agent_info.ip + ')';  // 实例名+ip
                // 优先级，先显示未授权状态，在显示离线状态
                if (!instanceInfo.agent_info.authorization_module.dbcdp) {  // 未授权
                    chkDisabled = true;
                    name = `(${LANG.UI_DB_RECOVERY_AGENT_UNAUTHIORIZED})` + name;
                    clusterAuthFlag = false;
                } else if (!instanceInfo.agent_info.online_flag) {  // 离线
                    chkDisabled = true;
                    name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
                } else {  // 需要全部离线才离线
                    clusterOnlineFlag = true;
                }
                nodes.push({
                    id: instanceInfo.app_uuid,
                    pId: clusterUuid,
                    name,
                    title: name,
                    isParent: false,
                    eventtype: 'cluster_instance',
                    icon: './img/platform/storage.png',
                    nocheck: true,
                    chkDisabled,
                    instance_name: instanceInfo.instance_name,
                    app_uuid: instanceInfo.app_uuid,
                    app_type:instanceInfo.app_type,
                    app_version:instanceInfo.app_version,
                    agent_uuid: instanceInfo.agent_info.agent_uuid,
                    cluster_flag: instanceInfo.cluster_info.cluster_flag,
                    cluster_uuid: instanceInfo.cluster_info.cluster_uuid,
                    online_flag: instanceInfo.agent_info.online_flag,
                    net_model: instanceInfo.agent_info.net_model,
                    auth_flag: instanceInfo.agent_info.authorization_module.dbcdp,
                    db_cdp_recover_info:[],
                    delay_time_flag:instanceInfo.delay_time_flag,
                    source_agent_uuid:instanceInfo.source_agent_uuid
                });
            }
            let clusterChkDisabled = false;
            let clusterName = clusterInfo.app_service_name + '(' + clusterInfo.cluster_name + ')';
            if (!clusterInfo.cluster_name) {
                clusterName = clusterInfo.app_service_name;
            }
            if (!clusterAuthFlag) {
                clusterChkDisabled = true;
                clusterName = `(${LANG.UI_DB_RECOVERY_AGENT_UNAUTHIORIZED})` + clusterName;
            } else if (!clusterOnlineFlag) {
                clusterChkDisabled = true;
                clusterName = `(${LANG.UI_VISUAL_OFF_LINE})` + clusterName;
            }
            nodes.push({
                id: clusterUuid,
                pId: 'cluster',
                name: clusterName,
                title: clusterName,
                isParent: true,
                open: true,
                eventtype: 'cluster',
                icon: './img/platform/db-cluster.png',
                chkDisabled: clusterChkDisabled,
                cluster_uuid: clusterInfo.cluster_uuid,
                cluster_name: clusterInfo.cluster_name,
                cluster_flag:true,
                cluster_service_ip: clusterInfo.cluster_service_ip,
                app_service_name: clusterInfo.app_service_name,
                app_type:clusterInfo.app_type,
                app_version:clusterInfo.app_version,
                online_flag: clusterOnlineFlag,
                auth_flag: clusterAuthFlag,
            });
        }
        zTreeAgent = $.fn.zTree.init($(id), getBackupTreeSetting(checkedFn), nodes);
    };

    /**
     * 获取备份树的配置
     * @returns
     */
    const getBackupTreeSetting = (checkedFn) => {
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
                    title: 'title',
                }
            },
            callback: {
                beforeClick: clickOracleBackupNode,
                onCheck: checkedFn,
            },
            view: {
                fontCss: (treeId, treeNode) => {
                    let style = {};
                    if (treeNode.eventtype === 'cluster_instance') {
                        style = {'padding-left': '16px'};
                    }
                    if (treeNode.chkDisabled) {
                        style.color = 'grey';
                    }
                    return style;
                },
            }
        };
    };

    /**
     * Oracle节点点击了文字
     * @param {*} treeId
     * @param {*} treeNode
     */
    const clickOracleBackupNode = (treeId, treeNode) => {
        if (treeNode.eventtype === 'cluster') {
            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true);
        }
        switch (treeNode.eventtype) {
            case 'cluster':
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true);
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
                break;
            case 'instance':
                if (treeNode.pId === 'standalone') {
                    $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
                }
                break;
        }
    };

    const checkOracleRecoverNode = (e, treeId, treeNode) => {
        $('#tabletips').hide();
        $('#drData').show();
        // 单选节点
        let allNodes = zTreeAgent.getCheckedNodes();
        for(let i = 0; i < allNodes.length; i++){
            zTreeAgent.checkNode(allNodes[i], false, false, false);
        }
        zTreeAgent.checkNode(treeNode, true, false, false);
        // 容灾备机
        $('#drBackup').html(treeNode.name);
        let agent_uuid = '';
        if (treeNode.eventtype === 'cluster') {
            // 遍历集群下的单节点
            for(let node of treeNode.children){
                if(node.online_flag){
                    // 在线单节点
                    agent_uuid = node.agent_uuid;
                    break;
                }
            }
        }else if (treeNode.eventtype === 'instance' && treeNode.pId ==='standalone') {
            agent_uuid = treeNode.agent_uuid;
        }
        recoverSourceAgentUuid = agent_uuid;
        // 获取容灾备机、监控应用、已处理容量等信息
        getDrInfo(recoverSourceAgentUuid);
        // 获取备机相关联的主机信息
        getHostInfo(recoverSourceAgentUuid);
        // 获取时间范围
        getTimeRange(recoverSourceAgentUuid);
        // 获取流量信息
        getAgentTimelineData(recoverSourceAgentUuid);
    }

    /**
     * 获取勾选备机的时间范围
     * @param uuid
     */
    const getTimeRange =  (agentuuid) => {
        //参数--备机的uuid
        pAjaxRequest({}, '/api/v1/dbcdp/jobs/restore_data/' + agentuuid + '/time_range', 'GET', (d) => {
            let data = d.data;
            let lastReplayTime = data.last_replay_time;
            let latestRecvTransactionTime = data.latest_recv_transaction_time;
            _lastReplayTimestamp = new Date(data.last_replay_time).getTime();
            _lastRecvTransactionTimestamp = new Date(data.latest_recv_transaction_time).getTime();
            $('#timeRange').html(lastReplayTime + '-' + latestRecvTransactionTime);
        },false);
    }

    /**
     * @function 获取时间轴data
     * @desc 通过选定数据源UUID获取客户端备份时间轴对应的数据，并加载和渲染时间轴chart
     */
    const getAgentTimelineData =  (agentuuid) => {
        let time_data = [];
        Metronic.blockUI({target: '#RecoveryTimeline', animate: true});
        //时间范围标志
        time_unit = $('#changeTimeInterval option:selected').val();
        let param = {'time_unit': time_unit};
        pAjaxRequest(param, '/api/v1/dbcdp/restore_data/' + agentuuid + '/data_flow', 'GET',  (d) => {
            Metronic.blockUI({target: '#RecoveryTimeline'});
            let data = d.data;
            var dataLength = data.time_data.length;
            _chartStartPercent = dataZoomUnit(dataLength);  //数据的个数
            if (dataLength > 0) {
                // 所有得时间点
                time_data = data['time_data'];
                loadTimelineChart(time_data);   //echarts图
                loadDatatimePicker();
            } else {
                UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA, LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA_MESSAGE);
                loadTimelineChart(time_data);
            }
        });
    }

    /**
     * 通过获取的数据量,设置dataZoom的加载百分比
     */
    const dataZoomUnit = function (value) {
        //dataZoom的起始百分比,默认值为70%
        var startPercent = 70;
        if (value < 100) {
            startPercent = 0;
        } else if (value > 100 && value < 300) {
            startPercent = 500;
        } else if (value > 300 && value < 600) {
            startPercent = 70;
        } else if (value > 600 && value < 1000) {
            startPercent = 80;
        } else if (value > 100 && value < 2000) {
            startPercent = 85;
        } else if (value > 2000 && value < 5000) {
            startPercent = 90;
        } else if (value > 5000 && value < 8000) {
            startPercent = 95;
        } else if (value > 8000) {
            startPercent = 95;
        } else {
            startPercent = 98;
        }
        return startPercent;
    }

    /**
     * 渲染时间轴chart
     */
    const loadTimelineChart = function (rawData) {
        // 动态设置echart图高度和宽度以适应不同分辨率
        let portletBodyWidth = $('#anyPointTime').width();
        let portletBodyHeight = $('#anyPointTime').height();

        if(_hisPortletBodyWidth != 0){  //非首次加载，chart坐标取历史配置
            portletBodyWidth = _hisPortletBodyWidth;
            portletBodyHeight = _hisportletBodyHeight;
        }else{  //首次加载获取当前body尺寸，赋值历史配置
            portletBodyWidth = $('#anyPointTime').width();
            portletBodyHeight = $('#anyPointTime').height();
            _hisPortletBodyWidth = $('#anyPointTime').width();
            _hisportletBodyHeight = $('#anyPointTime').height();
        }
        $('#RecoveryTimeline').css({"width": portletBodyWidth + 'px', "height": (portletBodyHeight - 34) + 'px'});

        echarts.init(document.getElementById('RecoveryTimeline')).dispose(); //销毁chart
        let chartDom = document.getElementById('RecoveryTimeline');
        myChart = echarts.init(chartDom);
        let dates = rawData.map(function (item) {
            let xDataInfo = item[0];
            return xDataInfo;  //返回范围内的每个时间点
        });
        let data = rawData.map(function (item) {
            return [item[0], item[1], item[2]];
        });
        let size = [];
        let lablePointNumber = 0;
        for (let i = 0; i < rawData.length; i++) {
            if (rawData[i][2] != 0 && rawData[i][3] == 2) {
                // 事务点
                size.push(7);  	//圆点大小
                lablePointNumber = rawData[i][2];
            }else if(rawData[i][2] != 0 && rawData[i][3] == 1){
                // 除事务点之外的事件点
                size.push(7);  	//圆点大小
            } else {
                size.push(0);
            }
        }
        var option = {
            legend: {
                data: [LANG.UI_VOL_CDP_RECOVER_IO_FLOW],
                inactiveColor: '#666666',
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    animation: false,
                    type: 'cross',
                    lineStyle: {
                        color: '#666666',
                        width: 1,
                        opacity: 1
                    }
                },
                formatter: function (params) {
                    // 流量
                    let value = params[0].data;
                    let valueStr = flowChartUnitStr(value);
                    return valueStr;
                }

            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: dates,
                axisLine: {lineStyle: {color: '#8e8e8e'}},
                axisLabel: {
                    fontSize: 14,
                }
            },
            yAxis: {
                scale: true,
                type: 'value',
                axisTick: {
                    show: true
                },
                axisLine: {
                    show: false
                },
                splitLine: {show: true},
                axisLabel: {
                    fontSize: 12,
                    lineStyle: {color: '#999999'},
                    formatter: function (value) {
                        // 纵坐标
                        let valueStr = flowChartUnitStr(value);
                        return valueStr;
                    }
                },
                name: LANG.UI_VOL_CDP_RECOVER_DATA_FLOW,
                nameTextStyle: {
                    fontSize: 14, // 设置字号大小为 14px
                    padding: [0, 0, 0, -60]
                }
            },
            toolbox: {  //图形转换
                feature: {
                    restore: {  // 刷新数据
                        show: true,
                        title:LANG.UI_DB_CDP_RECOVER_REFRESH_GET_LATEST_TIME_POINT,
                    },
                },
                right:'10%',
            },
            grid: {
                x: 90, // 设置 x 轴方向距左上角的距离
                y: 30  // 设置 y 轴方向距左上角的距离
            },
            dataZoom: [
                {
                    height: 18,//滚动条高度
                    moveHandleSize: 3, //滚动Handle条高度
                    textStyle: {
                        color: '#8392A5'
                    },
                    fillerColor: "rgba(14, 178, 142, 0.2)",
                    dataBackground: {
                        areaStyle: {
                            color: '#0FC6C2'
                        },
                        lineStyle: {
                            opacity: 0.8,
                            color: 'linear-gradient(180deg, rgba(59,179,70,0.2) 0%, rgba(59,179,70,0.01) 100%);'
                        }
                    },
                    brushSelect: true,
                    start: _chartStartPercent, //dataZoom的起始百分比,此处暂定70%,根据实际效果调整
                    end: _chartEndPercent, //dataZoom的结束位置,此处暂定100%
                }, {
                    type: 'inside',
                }
            ],
            series: [
                {
                    name: LANG.UI_VOL_CDP_TAKEOVER_IO_FLOW,
                    type: 'line',
                    data: calculateMA(1, data),
                    smooth: true,
                    showSymbol: true,
                    symbol: 'circle',     //设定为实心点
                    symbolSize: 10,       //设定实心点的大小
                    itemStyle: {
                        normal: {
                            lineStyle: {
                                width: 1,
                                color: '#0FC6C2'
                            }
                        }
                    },
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0,
                                color: 'rgba(59,179,70,0)'
                            }, {
                                offset: 1,
                                color: '#fff'
                            }])
                        }
                    },
                    //使用回调函数,重绘圆点(可根据自己逻辑添加相应的判断，让指定的坐标显示/隐藏圆点)
                    //控制纵坐标的点的大小
                    symbolSize: (rawValue, params) => {
                        params.symbolSize = size[params.dataIndex];
                        return params.symbolSize;
                    },
                    markPoint: {
                        symbolSize: 18,
                        data: markPointData(data),
                        label: {
                            formatter: '' 	//formatter:'{c}Mb/s'
                        }
                    }
                }
            ],
            color: ['#86dac2']
        };
        myChart.setOption(option);

        // 点击刷新获取最新时间点
        myChart.on('restore', function (){
            getTimeRange(data.recovery_object.source_agent_uuid);
            getAgentTimelineData(data.recovery_object.source_agent_uuid);
        });

        //时间轴流量范围内的点击事件
        myChart.getZr().on("click", params => {	// 获取点击位置
            _timepointType = 0;
            const pointInPixel = [params.offsetX, params.offsetY];
            if (myChart.containPixel("grid", pointInPixel)) {
                // 获取点击位置的坐标系[x，y]
                let xIndex = myChart.convertFromPixel({seriesIndex: 0}, [params.offsetX, params.offsetY])[0];
                this.menJinTableIndex = xIndex;
                let checkTime = rawData[xIndex][0];
                _checkTime = checkTime;
                if (rawData[xIndex][2] != 0) {
                    // 事件点
                    _timepointType = 2
                    if(rawData[xIndex][3] ==2 ){
                        // 事件类型-事务
                        _timepointEventType = 2;
                        parseMarkPointInfo();
                    }
                } else {
                    _timepointType = 1  //任意时间点
                }
            }
        });

        //组装标事件点坐标信息
        function markPointData(data) {
            let itemStyle = {color: '#2ea1fc'};
            let result = [];
            for (let i = 0; i < data.length; i++) {
                let coord = data[i][0];  //时间点
                let isMarkPoint = data[i][2];  //事件点
                if (isMarkPoint > 0) {
                    let obj = {};
                    let coordArray = [];
                    coordArray.push(coord);  //事件点
                    coordArray.push(data[i][1]);  //数据流量
                    coordArray.push(data[i][2]);  //标签点
                    coordArray.push(data[i][3]);  //事件点
                    obj.value = data[i][1];  //数据流量
                    obj.coord = coordArray;  //数组
                    obj.itemStyle = itemStyle;  //样式
                    result.push(obj);
                }
            }
            return result;
        }

        //纵坐标数据
        function calculateMA(dayCount, data) {
            let result = [];
            let len = data.length;
            for (let i = 0; i < len; i++) {
                let sum = 0;
                for (let j = 0; j < dayCount; j++) {
                    sum += data[i - j][1];
                }
                result.push(sum / dayCount);
            }
            return result;
        }
        // 获取 canvas 元素
        let canvasElement = chartDom.getElementsByTagName('canvas')[0];
        // 修改 canvas 元素的定位方式为相对定位
        canvasElement.style.position = 'relative';
    }

    /**
     * 流量图单位换算
     * @param value
     */
    const flowChartUnitStr = (value) => {
        let timeUnit = parseInt(_timeInterval);
        if(value >=1024*1024){
            let timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_GB;
            switch (timeUnit){
                case 1:
                case 2:
                    timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_GB;
                    break;
                case 3:
                case 4:
                    timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_GB_MIN;
                    break;
                case 5:
                case 6:
                    timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_GB_HOUR;
                    break;
            }
            return Math.round(value /1024/1024) +timeUnitStr;
        }else if(value >= 1024){
            let timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB;
            switch (timeUnit){
                case 1:
                case 2:
                    timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB;
                    break;
                case 3:
                case 4:
                    timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB_MIN;
                    break;
                case 5:
                case 6:
                    timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB_HOUR;
                    break;
            }
            return Math.round(value / 1024) +timeUnitStr;
        }else{
            let timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB;
            switch (timeUnit){
                case 1:
                case 2:
                    timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB;
                    break;
                case 3:
                case 4:
                    timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB_MIN;
                    break;
                case 5:
                case 6:
                    timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB_HOUR;
                    break;
            }
            return value + timeUnitStr;  //返回数据流量最小单位为KB
        }
    }

    /**
     * 初始化时间控件
     */
    const loadDatatimePicker = () => {
        $(".recoverytimepointview").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-MM-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
        });
    }

    /**
     * 获取容灾备机、监控应用、已处理容量等信息
     */
    const getDrInfo =  (agentuuid)=> {
        pAjaxRequest({}, '/api/v1/dbcdp/restore_data/' + agentuuid, 'GET',  (d) => {
            let data = d.data;
            let appName = data[0].app_info.app_name;
            let completedSize = storageCalculateSize(data[0].completed_size);
            // 监控应用
            $('#monitorApp').html(appName);
            // 已处理容量
            $('#processedCapacity').html(completedSize);  //已处理容量
        });
    }

    /**
     * 获取备机相关联的主机信息
     */
    const getHostInfo =  (agentuuid) => {
        pAjaxRequest({}, '/api/v1/dbcdp/restore_data/' + agentuuid + '/sourceHostInfo', 'GET', (d) => {
            // 同步时的源机信息
            let data = d.data;
            syncSourceAgentUuid = data.agent_uuid;
        })
    }

    /**
     * 初始化表格
     * type==1:事件信息
     * type==2:事务信息
     * 恢复这没有时间信息，会改
     */
    var loadCheckAgentEventInfo = () => {
        var columns = [
            {
                field: 'id',
                title: 'id',
                sortable: false,
                align: 'center',
            },
            {
                field: 'transaction_time',
                title: LANG.UI_DB_CDP_RECOVER_TRANSACTION_TIME,
                sortable: false,
                align: 'center',
            },
            {
                field: 'transaction_detail_scn',
                title: 'SCN',
                sortable: true,
                align: 'center',
            },
        ];
        var options = {
            vin_url: '/api/v1/dbcdp/restore_data/' + recoverSourceAgentUuid +'/sync/'+syncSourceAgentUuid+'/transaction',
            queryParams: {'event_time':_checkTime},
            vin_method: 'GET',
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
            singleSelect:true,
            onPostBody: ()=>{
                $('#transactionInfoTable').bootstrapTable('expandAllRows');
            },
            onExpandRow: (index, row, $detail) => {
                $detail.empty();

                // 判断是否是JSON格式 如果不是则是事件 反之是事务
                function isJSON(str) {
                    try {
                        JSON.parse(str);
                        return true;
                    } catch (e) {
                        return false;
                    }
                }
                if(!isJSON(row.transaction_detail)){
                    $detail.html(row.transaction_detail);
                    return;
                }

                var sqlTable = document.createElement('table');
                var headerRow = sqlTable.insertRow();
                var headers = ['id','sql'];
                var widths = ['50%','50%'];
                var transactionSql = row.transaction_sql;
                for (var j = 0; j < headers.length; j++) {
                    var th = document.createElement("th");
                    th.textContent = headers[j];
                    th.style.width = widths[j];
                    headerRow.appendChild(th);
                }
                transactionSql.forEach((ObjValue, index)=>{
                    var row = sqlTable.insertRow();
                    var cell1 = row.insertCell(0);
                    var cell2 = row.insertCell(1);
                    cell1.textContent = index+1;
                    cell2.textContent = ObjValue;
                    // 将表格添加到 $detail 中
                    $detail.append(sqlTable);
                });

                // var transactionDetail = JSON.parse(row.transaction_detail);
                // var cOp = [];
                // var uOp = [];
                // var dOp = [];
                // var ddlOp = [];
                //
                // transactionDetail.forEach((ObjValue, index) => {
                //     var payloadOp = ObjValue.payload[0].op;
                //
                //     switch (payloadOp) {
                //         case 'c':
                //             cOp.push(ObjValue);
                //             break;
                //         case 'u':
                //             uOp.push(ObjValue);
                //             break;
                //         case 'd':
                //             dOp.push(ObjValue);
                //             break;
                //         case 'ddl':
                //             ddlOp.push(ObjValue);
                //             break;
                //     }
                // });
                //
                // var des = `<div>`+LANG.UI_DB_CDP_RECOVER_TRANSACTION_ADD_OP_TIPS+`: ${cOp.length}</div>`+
                //     `<div>`+LANG.UI_DB_CDP_RECOVER_TRANSACTION_EDIT_OP_TIPS+`: ${uOp.length}</div>`+
                //     `<div>`+LANG.UI_DB_CDP_RECOVER_TRANSACTION_DELETE_OP_TIPS+`: ${dOp.length}</div>`+
                //     `<div>`+LANG.UI_DB_CDP_RECOVER_TRANSACTION_OP_NUM_TIPS+`: ${ddlOp.length}</div>`;
                //
                // $detail.append(des);
                //
                // // 修改
                // if (uOp.length !== 0) {
                //     var uOpTable = document.createElement('table');
                //     var headerRow = uOpTable.insertRow();
                //     var headers = [LANG.UI_DB_CDP_RECOVER_TABLE_NAME, LANG.UI_DB_CDP_RECOVER_COLUMN_NAME, LANG.UI_DB_CDP_RECOVER_MODIFIED_BEFORE_DATA, LANG.UI_DB_CDP_RECOVER_MODIFIED_AFTER_DATA];
                //     var widths = ['25%','25%','25%','25%'];
                //
                //     for (var j = 0; j < headers.length; j++) {
                //         var th = document.createElement("th");
                //         th.textContent = headers[j];
                //         th.style.width = widths[j];
                //         headerRow.appendChild(th);
                //     }
                //
                //     for (var i = 0; i < uOp.length; i++) {
                //         // 遍历before对象
                //         for (var key in uOp[i].payload[0].before) {
                //             var tableName = uOp[i].payload[0].schema.table;  //表名
                //             var columnName = key;  // 列名
                //             var beforeData = uOp[i].payload[0].before[columnName] ? uOp[i].payload[0].before[columnName] : '--';  //修改前数据
                //             var afterData = uOp[i].payload[0].after[columnName];  //修改后数据
                //             var row = uOpTable.insertRow();
                //             var cell1 = row.insertCell(0);
                //             var cell2 = row.insertCell(1);
                //             var cell3 = row.insertCell(2);
                //             var cell4 = row.insertCell(3);
                //             cell1.textContent = tableName;
                //             cell2.textContent = columnName;
                //             cell3.textContent = beforeData;
                //             cell4.textContent = afterData;
                //         }
                //     }
                //     // 将表格添加到 $detail 中
                //     $detail.append(uOpTable);
                // }
                //
                // // 增加
                // if (cOp.length !== 0) {
                //     var cOpTable = document.createElement('table');
                //     var headerRow = cOpTable.insertRow();
                //     var headers = [LANG.UI_DB_CDP_RECOVER_TABLE_NAME, LANG.UI_DB_CDP_RECOVER_COLUMN_NAME, LANG.UI_DB_CDP_RECOVER_ADDED_DATA];
                //     var widths = ['33%','33%','33%'];
                //
                //     for (var j = 0; j < headers.length; j++) {
                //         var th = document.createElement("th");
                //         th.textContent = headers[j];
                //         th.style.width = widths[j];
                //         headerRow.appendChild(th);
                //     }
                //
                //     for (var i = 0; i < cOp.length; i++) {
                //         // 遍历after对象
                //         for (var key in cOp[i].payload[0].after) {
                //             var tableName = cOp[i].payload[0].schema.table;  //表名
                //             var columnName = key;  // 列名
                //             var afterData = cOp[i].payload[0].after[columnName] ? cOp[i].payload[0].after[columnName] : '--';  // 增加的数据
                //             var row = cOpTable.insertRow();
                //             var cell1 = row.insertCell(0);
                //             var cell2 = row.insertCell(1);
                //             var cell3 = row.insertCell(2);
                //             cell1.textContent = tableName;
                //             cell2.textContent = columnName;
                //             cell3.textContent = afterData;
                //         }
                //     }
                //     // 将表格添加到 $detail 中
                //     $detail.append(cOpTable);
                // }
                //
                // // 删除
                // if (dOp.length !== 0) {
                //     var dOpTable = document.createElement('table');
                //     var headerRow = dOpTable.insertRow();
                //     var headers = [LANG.UI_DB_CDP_RECOVER_TABLE_NAME, LANG.UI_DB_CDP_RECOVER_COLUMN_NAME, LANG.UI_DB_CDP_RECOVER_DELETE_DATA];
                //     var widths = ['33%','33%','33%'];
                //
                //     for (var j = 0; j < headers.length; j++) {
                //         var th = document.createElement("th");
                //         th.textContent = headers[j];
                //         th.style.width = widths[j];
                //         headerRow.appendChild(th);
                //     }
                //
                //     for (var i = 0; i < dOp.length; i++) {
                //         // 遍历before对象
                //         for (var key in dOp[i].payload[0].before) {
                //             var tableName = dOp[i].payload[0].schema.table;  //表名
                //             var columnName = key;  // 列名
                //             var afterData = dOp[i].payload[0].before[columnName] ? dOp[i].payload[0].before[columnName] : '--';  // 增加的数据
                //             var row = dOpTable.insertRow();
                //             var cell1 = row.insertCell(0);
                //             var cell2 = row.insertCell(1);
                //             var cell3 = row.insertCell(2);
                //             cell1.textContent = tableName;
                //             cell2.textContent = columnName;
                //             cell3.textContent = afterData;
                //         }
                //     }
                //     // 将表格添加到 $detail 中
                //     $detail.append(dOpTable);
                // }
                //
                // // ddl
                // if (ddlOp.length !== 0) {
                //     var ddlOpTable = document.createElement('table');
                //     var headerRow = ddlOpTable.insertRow();
                //     var headers = [LANG.UI_DB_CDP_RECOVER_TABLE_NAME, 'sql'];
                //     var widths = ['50%','50%'];
                //     for (var j = 0; j < headers.length; j++) {
                //         var th = document.createElement("th");
                //         th.textContent = headers[j];
                //         th.style.width = widths[j];
                //         headerRow.appendChild(th);
                //     }
                //
                //     for (var i = 0; i < ddlOp.length; i++) {
                //         var tableName = ddlOp[i].payload[0].schema.table;  //表名
                //         var sqlData = ddlOp[i].payload[0].sql ? ddlOp[i].payload[0].sql : '--';  // 增加的数据
                //         var row = ddlOpTable.insertRow();
                //         var cell1 = row.insertCell(0);
                //         var cell2 = row.insertCell(1);
                //         cell1.textContent = tableName;
                //         cell2.textContent = sqlData;
                //     }
                //     // 将表格添加到 $detail 中
                //     $detail.append(ddlOpTable);
                // }

            },
            // onCheck :(row) =>{
            //     // 如果开启了延迟重放，选择的事务在时间范围内就填充到 恢复时间点
            //     // 事务时间
            //     let transactionTime = new Date(row.event_time).getTime();
            //     if(delayTimeFlag && transactionTime >= _lastReplayTimestamp && transactionTime <= _lastRecvTransactionTimestamp){
            //         $('#inputRecoveryTimepoint').val(row.event_time);
            //         verifyTimepointisValid(row.event_time);
            //     }
            // },
            resizable: false,
            singleSelect: true,
            columns: columns,
        }

        $('#transactionInfoTable').bootstrapTable('destroy');
        $('#transactionInfoTable').baseTableConfig().init(options);
    }


    return {
        //main function to initiate the module
        init: function () {
            initListeners();
            initAgentTree();
        }
    };
}();

jQuery(document).ready(function () {
    DbData.init();
});