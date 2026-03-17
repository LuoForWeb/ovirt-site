var RecoverTimepoint = function (){

    let _timePointValidityValue = 0;   // 时间点是否有效  0:无效 1：有效
    let _timepointType = 1;            // 时间点类型，0:无效时间点，1:任意时间点；2:标签点；3:事件点；4:标签点和事件点
    let _timeInterval =1;              // 时间轴默认加载的间隔  比如10min/1小时
    let _chartStartPercent = 70;       // dataZoom的起始百分比,此处暂定70%,根据实际效果调整.(可以根据加载的时间区间范围确定起始百分比)
    let _hisPortletBodyWidth = 0;      // 首次加载echarts获取历史宽度
    let _hisportletBodyHeight = 0;     // 首次加载echarts获取历史高度
    let _chartEndPercent = 100;        // dataZoom的结束百分比,此处暂定100%,根据实际效果调整
    let _eventNavTabsType = 1;         // 时间点类型tab  1：任意时间点；2：标签点；3：事件点；
    var jobType = 1;// 任务类型 默认是跨平台恢复 2瞬时恢复 4细粒度恢复
    var zTree;
    var nodeParamList;
    let _createTaskMsgdata = {  // 消息结构
        timeInfo:{},
        highInfo:{}
    };
    var pointtypetreeInitFlag = false;

    /**
     * 初始化节点列表
     */
    let initPointShowType = () => {

        // 跳转复制页面
        $('#tobackup').on('click', function () {
            LOCATION('./content/complete_machine_volcdp/cm_volcdp_backup.php', 'complete_cdp_backup');
        });

        let requestData = {
            module_type : CONF.MODULE_TYPE.VOL_CDP
        }
        pAjaxRequest(requestData,'/api/v1/nodes/get/timepoint','GET',(d)=>{
            let data = d.data;
            let nodeselect = $('#nodeselect');
            nodeselect.empty();
            for(let i=0; i<data.length; i++){
                let option = $("<option>").text(data[i].text).val(data[i].node_uuid);
                nodeselect.append(option);
            }
        });
        //绑定事件
        $('#nodeselect').on('change', nodeselectChange);
        $('#storageselect').on('change', storageselectChange);

        $('#searchAgent').on('propertychange', searchMachine).on('input', searchMachine);
    }

    //初始化存储
    var initStorage =  function(){
        let p = {
            instantRecoverFlag: false,
            grainRecoverFlag: false,
            osinstantModuleFlag: false,
        };
        pAjaxRequest(p, "/api/v1/storages/type", "GET", function (d) {
            var data = d.data;
            var storageselect = $('#storageselect');
            storageselect.empty();
            for(var i=0; i<data.length; i++){
                if ($.inArray(jobType,[2, 4]) !== -1 && $.inArray(data[i].storagetype,[9, 10]) !== -1) {
                    // 细粒度和瞬时恢复屏蔽云存储和磁带
                    continue;
                }
                // 跨平台屏蔽磁带
                if (jobType == 1 && data[i].storagetype == 10) {
                    continue;
                }
                var option = $("<option>").text(data[i].text).val(data[i].storageid).attr("type",data[i].storagetype);
                storageselect.append(option);
            }
        }, false);
    };

    var searchMachine = function(){
        var value = $('#searchAgent').val();
        var checkNode = zTree.getCheckedNodes();
        var allNode = zTree.transformToArray(zTree.getNodes());
        nodeParamList = zTree.getNodesByParamFuzzy('name', value);
        zTree.hideNodes(allNode);
        if(nodeParamList.length == 0){
            $('.two_tree').hide();
            $('#nosearchtips').show();
        }else{
            $('.two_tree').show();
            $("#nosearchtips").hide();
        }
        //连接搜索的和所勾选的
        nodeParamList =nodeParamList.concat(checkNode);
        var nodeParamList1 = zTree.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
            findParent(zTree,nodeParamList1[n]);
        }
        zTree.showNodes(nodeParamList);
    }

    //找到父节点
    var findParent = function(treeObj,node){
        zTree.expandNode(node,true,false,false);
        if(!node.children){
            nodeParamList.push(node);
            zTree.expandNode(node,false,false,false);
        }
        var pNode = node.getParentNode();
        if(pNode != null){
            nodeParamList.push(pNode);
            findParent(zTree, pNode);
        }
    }

    /**
     * 切换节点列表触发事件
     */
    let nodeselectChange = () => {
        pointtypetreeInitFlag = false;
        $('#searchAgent').val('');
        initAgentTree();
    }

    var storageselectChange =  function(){
        pointtypetreeInitFlag = false;
        $('#searchAgent').val('');
        initAgentTree();
    };
    /**
     * 搜索框事件
     */
    // let searchAgent = () => {
    //     let searchValue = $("#searchAgent").val();
    //     if(searchValue==""){
    //         UIToastr.showInfo(LANG.UI_VOL_CDP_BACKUP_SET_SEARCH_CLIENT,LANG.UI_VOL_CDP_BACKUP_SET_INPUT_MESSAGE);
    //     }
    //     let node = $('#nodeselect').val();
    //     var requestData = {
    //         node:node,
    //         search_info:searchValue
    //     }
    //     Metronic.blockUI({target:'#recovery_agent_tree',animate: true});
    //     pAjaxRequest(requestData,'/api/v1/volcdp/agent_tree','GET',init);
    // }

    /**
     * 获取恢复源树结构
     */
    let initAgentTree = ()=>{
        let node_uuid = $('#nodeselect').find('option:selected').val();
        let requestData = {
            node_uuid:node_uuid,
            storage_uuid: $('#storageselect').val(),
            notcloud: jobType != 1,
            nottape: true,
        };
        Metronic.blockUI({target:'#recovery_agent_tree',animate: true});
        pAjaxRequest(requestData,'/api/v1/complete_machine_volcdp/data/agent/tree','GET',(d)=>{
            $("#recovery_agent_tree").show();
            $("#nopointtips").hide();
            $("#nosearchtips").hide();
            Metronic.unblockUI('#recovery_agent_tree');
            let zNodes = d.data;
            if(zNodes.length == 0){
                $(".two_tree").hide();
                if (requestData.storage_uuid != '') {
                    $('#nosearchtips').show();
                } else {
                    $("#nopointtips").show();
                }
                return;
            }
            $(".two_tree").show();
            let setting = {
                check: {
                    enable: true,
                    nocheckInherit: false,
                    chkStyle: "radio",
                    radioType: "all"
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
                    beforeClick: "",
                    onCheck: nodeCheck,
                    beforeClick: nodeSelect,
                },
                view: {
                    nameIsHTML:true
                }
            };
            zTree = $.fn.zTree.init($("#recovery_agent_tree"), setting, zNodes);
            pointtypetreeInitFlag = true;
        });
    }

    /**
     * 客户端节点 checkbox 点击事件
     * @param e
     * @param id
     * @param node
     */
    let nodeCheck = (e, id, node) => {
        let parentNode = node.getParentNode();  // 获取父节点
        let agent_uuid = node.agent_uuid;       // 选中客户端的uuid
        let task_uuid = node.task_uuid;         // 选中客户端相关联的task_uuid
        let agent_name = node.name;             // 选中客户端名
        // 消息结构
        _createTaskMsgdata.agent_uuid = agent_uuid;
        _createTaskMsgdata.name = agent_name;
        _createTaskMsgdata.task_uuid = task_uuid;
        _createTaskMsgdata.os_type = parentNode.os_type;
        _createTaskMsgdata.node_uuid = parentNode.node_uuid;

        let params = {
            'task_uuid': node.task_uuid,
            'agent_uuid': node.agent_uuid,
            'create_task_type': CONF.TASK_TYPE.VOL_CDP_RECOVERY,
            'node_uuid': node.node_uuid,
        }

        if(node.checked){
            // 备份集组件
            $('#volBackupSet').show();
            $('#noCheckTips').hide();
            volCdpBackupSetTimeLineInfo.init(params);
        }else{
            $('#volBackupSet').hide();
            $('#noCheckTips').show();
        }
    }

    /**
     * 客户端节点点击事件
     * @param treeId
     * @param treeNode
     * @param clickFlag
     */
    let nodeSelect = (treeId, treeNode, clickFlag) => {
        let treeObj = $.fn.zTree.getZTreeObj(treeId);
        treeObj.checkNode(treeNode, !treeNode.checked, true);
        nodeCheck('',treeId,treeNode);
    }

    /**
     * 流量图单位换算
     * @param value
     */
    let flowChartUnitStr =(value) => {
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
    let loadIntervalTimePicker = function(){
        $(".recoverytimeview").datetimepicker({
            language:  'zh-CN',
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-MM-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
        });
    }

    /**
     * 通过时间间隔计算时间区间类型
     */
    let timeIntervalUnit = (value)=> {
        let secondTimeDiff = value/1000; //单位秒
        let unit;
        if(secondTimeDiff<=600){ //10分钟内
            unit =1;
        }else if(secondTimeDiff>600 && secondTimeDiff<=3600){ //1小时内
            unit =2;
        }else if(secondTimeDiff>3600 && secondTimeDiff<=86400){ //最近1天
            unit =3;
        }else if(secondTimeDiff>86400 && secondTimeDiff<=604800){ //最近7天
            unit =4;
        }else if(secondTimeDiff>604800 && secondTimeDiff <=2592000){ //最近1个月
            unit =5;
        }else if(secondTimeDiff>2592000 && secondTimeDiff <=7776000){ //最近3个月
            unit =6;
        }else if(secondTimeDiff >7776000){
            unit =6;
        }else {
            unit = 1;
        }
        return unit;
    }

    /**
     * 根据时间点的选择途径解析时间点类型
     */
    let parseTimepointType = ()=> {
        let type = _timepointType;
        let timePointDesc = "--";
        let title = '';
        switch(type){
            case 1:
                timePointDesc = LANG.UI_VOL_CDP_RECOVER_ANY_POINT_TIME;
                $('#inputRecoveryTimepoindes').html(timePointDesc);
                break;
            case 2:
                timePointDesc = LANG.UI_VOL_CDP_RECOVER_LABEL_POINT;
                title = LANG.UI_VOL_CDP_RECOVER_LABEL_POINT_TITLE;
                $('#inputRecoveryTimepoindes').html('<a id ="lablePointDetail" title = "'+title+'">'+timePointDesc+'</a>');
                $('#lablePointDetail').click(parseLablePointInfo);
                break;
            case 3:
                timePointDesc = LANG.UI_VOL_CDP_RECOVER_MARK_POINT;
                title = LANG.UI_VOL_CDP_RECOVER_TIME_POINT_TITLE;
                $('#inputRecoveryTimepoindes').html('<a id ="markPointDetail" title = "'+title+'">'+timePointDesc+'</a>');
                $('#markPointDetail').unbind('click').click(parseMarkPointInfo);
                break;
            case 4:
                var lablePointDes = LANG.UI_VOL_CDP_RECOVER_LABEL_POINT;
                var markPointDes = LANG.UI_VOL_CDP_RECOVER_MARK_POINT;
                title = LANG.UI_VOL_CDP_RECOVER_MARK_POINT_TITLE;
                $('#inputRecoveryTimepoindes').html('<a id ="lablePointDetail" title = "'+title+'">'+lablePointDes+'</a>&nbsp;&nbsp;\
	        			<a id ="markPointDetail" title = "'+title+'">'+markPointDes+'</a>');
                $('#markPointDetail').unbind('click').click(parseMarkPointInfo);
                $('#lablePointDetail').unbind('click').click(parseLablePointInfo);
                break;
            default:
                $('#inputRecoveryTimepoindes').html(timePointDesc);
                $('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
                $('#timePointValidity').css('color', "#F3565D");
                _timepointType = 0;
                break;
        }
    }

    return {
        init:(options)=>{
            if (options.jobType != undefined) {
                jobType = options.jobType
            }
            initStorage(); //初始化存储
            initPointShowType();
            initAgentTree();
        },
        // 提供一个对外获取配置
        getInfo: function () {
            var info = volCdpBackupSetTimeLineInfo.getBackupSetConfigInfo();  //根据实际使用情况，调用该方法
            if (!info.time_point_is_valid) {
                // 时间点无效
                UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TIME_MESSAGE);
                return false;
            }
            if ($('#recovery_job_type').val() == 1) {
                // 跨平台恢复
                // 这里需要判断下当前的点是否有系统分区
                var systemVolume = false; // 标记是否有系统分区
                for (var k in info.vol_info) {
                    if (info.vol_info[k].system_volume) {
                        systemVolume = true;
                        break;
                    }
                }
                if (!systemVolume) {
                    // 该备份集没有系统分区，不能进行跨平台恢复；
                    UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TIME, LANG.UI_CM_VOL_CDP_PLATFORM_RECOVERY_TIPS);
                    return false;
                }
            }

            if (!info.time_point_is_valid) {
                return false;
            }
            var allNodes = [];
            if (zTree && zTree != undefined) {
                allNodes = zTree.getCheckedNodes(true);
            }

            if (allNodes.length <= 0 || info.time_point == '') {
                $(".selecttimepointtip").html(LANG.UI_RECOVERY_SELECT_POINT).show();
                // 动态设置tab-pane的高度
                $(".tab-pane__row").css('height', 'calc(100% - 80px)');
                return false;
            }
            // 获取节点和名称
            var node_uuid,agent_uuid,name,type,task_uuid,storage_uuid;
            var showStr = [];
            for (var i in allNodes) {
                if (allNodes[i].level == 2) {
                    node_uuid = allNodes[i].node_uuid;
                    agent_uuid = allNodes[i].agent_uuid;
                    name = allNodes[i].name;
                    type = allNodes[i].type;
                    task_uuid = allNodes[i].task_uuid;
                    storage_uuid = allNodes[i].storage_uuid;
                    showStr.push( allNodes[i].name + "<br>");
                    //info.timepoint_uuid = allNodes[i].timepoint_uuid;
                }
            }

            // 处理封装一下
            var jsondata = {
                uuid:agent_uuid,
                timepoint_uuid:info.time_point_uuid,
                node_uuid:node_uuid,
                agent_uuid:agent_uuid,
                storage_uuid:storage_uuid,
                os_type: info.os_type,
                host_name: name,
                name: info.time_point,
                path: name,
                dir_path: name,
                hypervisor: 0,
                storage_type: info.storage_type != undefined ? info.storage_type : 0,
                system_boot_type: info.system_boot_type,
                integrity_check_flag: 2, // 备份没得配置，那么恢复就要直接禁用
                cdp_datetime: info.time_point, // 实时的时间
                virus_scan_status: info.virus_scan_status != undefined ? info.virus_scan_status : 0, // 备份点状态
            };
            console.log('volCdpBackupSetTimeLineInfo', info);
            return {
                'info': info,
                'point_info': [jsondata],
                'node_uuid': node_uuid,
                'storage_uuid': storage_uuid,
                'agent_uuid': agent_uuid,
                'name': name,
                'type': type,
                'task_uuid': task_uuid,
                'vmOldName': [[name, info.time_point]],
                // 'show_str': showStr,
                'show_str': [name + '('+ info.time_point + ')'], // 因为实时只会显示一个点
            };
        }
    }
}();
