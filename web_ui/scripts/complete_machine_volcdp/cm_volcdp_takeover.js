var cmVolcdpTakeOver = (function (){
    let _createTaskMsgdata = {
        "task_name" : "",
        "module_type" : "",
        "task_type" : "",
        "strategy_group_uuid": "",
        "storage_uuid": "",
        "node_uuid": "",
        "backup_server_ip" : "",
        "transport_strategy":{
            "speed_limit_flag":0,
            "max_speed":0,
            "network_uuid":"",
            "encrypt_flag":2,
            "compress_flag":2,
            "compress_method":"",
            "block_size":"",
        },
        'safe_config_strategy': {
            'worm_flag': 1,
            'worm_protection_time': 99,
            'virus_scan_flag': 1,
            'virus_scan_config_list':'',
            'integrity_check_flag': 1,
            'integrity_check_config': {
                'check_strategy': 0,
                'full_error_policy': 0,
                'inc_error_policy': 0,
                'recovery_error_policy': 0
            }
        },
        "script_list" : {
            "before_takeover_script_location" : [],
            "after_takeover_script_location" : []
        },
        "dev_type" : "2",
        "master_agent_uuid" : "",
        "takeover_data_source" : "1",
        "takeover_timestamp" : "",
        "takeover_object" : {
            "takeover_standby_agent_uuid" : "",
            "master_agent_ip_switch_flag" : "1",
            "agent_failback_standby_ip" : "",
            "takeover_agent_role" : "100",
            "takeover_business_ip_map" : [],
            "app_takeover_flag" : "1",
            "app_set" : [
                {
                    "master_app_uuid" : "",
                    "standby_app_uuid" : ""
                },
            ],
            "takeover_disk_list" :[],
            "vol_mount_point_set": [],
            "takeover_vm":{},
            "host_reset_name":"",
            "cross_platform_flag":2,
            "cross_platform_conf":""
        },
        "recovery_position" : 0,
        "time_strategy_list" : [],
        "timepoint_uuid":"",
    };

    let nodeParamList;
    let sourceNode = [];                     // 选择的源节点
    let task_uuid = '';
    let agent_uuid = '';
    let agent_ip = '';
    let agent_name = '';
    let timepoint_uuid = '';
    let node_uuid = '';
    let storage_uuid = '';
    let os_type = '';
    let targetVisibleFlag = false;
    let targetInputFlag = false;
    let zTreeAgent;
    let networkFlag = false;

    let _selectTargetType = true;             // 选择目标类型  true:容灾演练平台；false：整机接管
    let diskConfigParams = {};
    let _agentNetworkInfo = [];                // 主机网卡信息
    let _standbyNetworkInfo = [];              // 备机网卡信息
    let _networkList = [];                     // 网络列表
    let vmTempText = '';                       // 内嵌虚拟机的描述
    let takeoverTargetHostDes = '';            // 接管目标主机配置
    let afterScriptContent= '';                // 接管后脚本
    let _standbyGatewayConf = [];              //
    let _takeoverIpMapList =[];
    let _takeoverIpMapListView = [];
    let _standbyIsConf = false;                // 接管备机是否已配置
    let ipMapInfoList = [];
    let takeoverMethodFlag = false;            // 接管方式 false:挂载接管
    let proxyMethod = false;                   // 代理模式
    let recoverType = false;
    let takeoverSourceUuid = '';               // 接管源uuid
    let takeoverTargetName = '';               // 接管目标主机名称
    let type = 5;                              // 5:整机接管 6:挂载接管
    let virusDetectionStr;                     // 安全策略描述

    let app_info = [];
    let zTreeApp;

    let targetOsType = '';
    let node_uuid_list = [];
    let appTakeoverSwitchFlag = false;
    let backup_agent_flag = false;
    let systemVolume = false; // 标记是否有系统分区
    // 备份集id
    let backup_set_id = '';
    let isLicense = false; // 标记授权状态
    /**
     * 步骤
     */
    let wizardInit = ()=>{
        if (!jQuery().bootstrapWizard) {
            return;
        }
        let form = $('#submit_form');
        let error = $('.alert-danger', form);
        let success = $('.alert-success', form);
        let handleTitle = (tab, navigation, index)=> {
            let total = navigation.find('li').length;
            let current = index + 1;
            jQuery('li', $('#completeMachineVolcdptakeovercontent')).removeClass("done");
            let li_list = navigation.find('li');
            for (let i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#completeMachineVolcdptakeovercontent').find('.button-previous').css('visibility', 'hidden');
            } else {
                $('#completeMachineVolcdptakeovercontent').find('.button-previous').css('visibility', 'visible');
            }

            if (current >= total) {
                $('#completeMachineVolcdptakeovercontent').find('.button-next').hide();
                $('#completeMachineVolcdptakeovercontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#completeMachineVolcdptakeovercontent').find('.button-next').show();
                $('#completeMachineVolcdptakeovercontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#completeMachineVolcdptakeovercontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch(index){
                    case 1:
                        if(step1Valid() == false){
                            return false;
                        }
                        break;
                    case 2:
                        if(step2Valid() == false){
                            return false;
                        }
                        break;
                    case 3:
                        if(step3Valid() == false){
                            return false;
                        }
                        break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                let total = navigation.find('li').length;
                let current = index + 1;
                let $percent = (current / total) * 100;
                $('#dbbackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#completeMachineVolcdptakeovercontent').find('.button-previous').css('visibility', 'hidden');
        $('#completeMachineVolcdptakeovercontent .button-submit').click(submit).css('visibility', 'hidden');
    };

    let addListeners = () => {
        // 搜索
        $('#searchAgent').on('propertychange', searchMachine).on('input', searchMachine);
        // 按照存储节点筛选
        $('#storageselect').on('change', storageselectChange);

        // 定义iCheck样式
        $('input[type=radio]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%', // optional
        });

        // 默认选中挂载接管
        $('#takeoverMethod').find('input[data-mode=1]').iCheck("check");

        // 切换接管方式
        $('#takeoverMethod').find('.icheck').on('ifClicked', changeRadio);

        // 接管备机
        $('#takeoverHost').on('change',changeTakeoverHost);

        // (挂载接管)网络配置
        $('.takeovernetworkconf').unbind('click').click(takeoverNetworkConfModal);

        // 备机映射
        $('#builtInVmSubmit').off().unbind('click').click(builtInVmSubmitInfo);

        // (整机接管)配置备机映射
        $('#cmstandbymapconf').off().on('click',standbyMapConf);

        // 应用接管开关
        $('.apptakeoverswitch').off('switchChange.bootstrapSwitch').on('switchChange.bootstrapSwitch', appTakeoverSwitch); // 应用开关
    }

    /* -------------------STEP1-------------------*/

    /**
     * 初始化存储
     */
    var initStorage =  function(){
        var data = {
            instantRecoverFlag :true
        }
        pAjaxRequest(data, "/api/v1/storages/type", "GET", function (d) {
            var data = d.data;
            var storageselect = $('#storageselect');
            storageselect.empty();
            for(var i=0; i<data.length; i++){
                var option = $("<option>").text(data[i].text).val(data[i].storageid).attr("type",data[i].storagetype);
                storageselect.append(option);
            }
        }, false);
    };

    let searchMachine = function(){
        var value = $('#searchAgent').val();
        // 输入验证
        if(!customInputValidate('string',value)){
            return false;
        }
        var checkNode = zTreeAgent.getCheckedNodes();
        var allNode = zTreeAgent.transformToArray(zTreeAgent.getNodes());
        nodeParamList = zTreeAgent.getNodesByParamFuzzy('name', value);
        zTreeAgent.hideNodes(allNode);
        if(nodeParamList.length == 0){
            $('.takeoverAgentTree').hide();
            $('#nosearchtips').show();
        }else{
            $('.takeoverAgentTree').show();
            $("#nosearchtips").hide();
        }
        //连接搜索的和所勾选的
        nodeParamList =nodeParamList.concat(checkNode);
        var nodeParamList1 = zTreeAgent.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
            findParent(zTreeAgent,nodeParamList1[n]);
        }
        zTreeAgent.showNodes(nodeParamList);
    }

    // 找到父节点
    let findParent = function(treeObj,node){
        zTreeAgent.expandNode(node,true,false,false);
        if(!node.children){
            nodeParamList.push(node);
            zTreeAgent.expandNode(node,false,false,false);
        }
        var pNode = node.getParentNode();
        if(pNode != null){
            nodeParamList.push(pNode);
            findParent(zTreeAgent, pNode);
        }
    }

    let storageselectChange =  function(){
        $('#searchAgent').val('');
        $('#nosearchtips').hide();
        $('.takeoverAgentTree').show();
        initAgentTree();
    };

    /**
     * 获取接管源树结构
     */
    let initAgentTree = ()=>{
        let storage_uuid = $('#storageselect').find('option:selected').val();
        let requestData = {
            storage_uuid:storage_uuid,
        };
        Metronic.blockUI({target:'#takeover_agent_tree',animate: true});
        pAjaxRequest(requestData,'/api/v1/complete_machine_volcdp/data/agent/tree','GET',(d)=>{
            Metronic.unblockUI('#takeover_agent_tree');
            let backup_set_task_uuid = $('#task_uuid').val();
            backup_set_id = $('#backup_set_id').val();
            let zNodes = d.data;
            if(zNodes.length == 0){
                $("#takeover_agent_tree").hide();
                $("#nopointtips").show();
                return;
            }
            let setting = {
                check: {
                    enable: true,
                    nocheckInherit: false,
                    chkStyle: "radio",
                    radioType: "all",
                    autoCheckTrigger: true
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
                    onCheck: nodeCheck,
                    beforeClick: nodeSelect,
                },
                view: {
                    nameIsHTML:true,
                }
            };
            zTreeAgent = $.fn.zTree.init($("#takeover_agent_tree"), setting, zNodes);
            let backup_set_node = zTreeAgent.getNodeByParam('task_uuid', backup_set_task_uuid, null);
            if (backup_set_node) {
                zTreeAgent.expandNode(backup_set_node, true, true, true); 
                zTreeAgent.checkNode(backup_set_node, true, true, true); // 选中节点
            }
        });
    }

    /**
     * 客户端节点 checkbox 点击事件
     * @param e
     * @param id
     * @param node
     */
    let host_app_info = [];
    let nodeCheck = (e, id, node) => {
        if(node.chkDisabled){
            return;
        }
        if(node.type == 'data_center' || node.type == 'task' || node.type == 'vol'){
            return;
        }
        sourceNode = node;
        agent_uuid = node.agent_uuid;                       // 选中客户端的uuid
        agent_ip = node.host_ip;                            // 选中客户端的ip
        task_uuid = node.task_uuid;                         // 选中客户端相关联的task_uuid
        agent_name = node.name;                             // 选中客户端名
        node_uuid = node.node_uuid;                         // 节点uuid
        storage_uuid = node.storage_uuid;                   // 存储uuid
        os_type = node.os_type;                             // 操作系统
        backup_agent_flag = node.backup_agent_flag;         // 备份时是否勾选数据库应用
        // 客户端连接服务端
        if(node.net_model == 2){
            networkFlag = true;
        }
        // 消息结构
        _createTaskMsgdata.master_agent_uuid = agent_uuid;      // 源客户端uuid
        _createTaskMsgdata.master_agent_ip = agent_ip;          // 源客户端ip
        _createTaskMsgdata.name = agent_name;                   // 源客户端名
        _createTaskMsgdata.task_uuid = task_uuid;               // 源任务uuid
        _createTaskMsgdata.os_type = node.os_type;
        _createTaskMsgdata.node_uuid = node.node_uuid;
        _createTaskMsgdata.storage_uuid = node.storage_uuid;
        _createTaskMsgdata.os_type = os_type;
        // 先在step1获取磁盘uuid
        _createTaskMsgdata.takeover_object.takeover_disk_list = [];
        for(let i =0;i<node.children.length;i++){
            _createTaskMsgdata.takeover_object.takeover_disk_list.push(node.children[i].uuid);
        }
        host_app_info = [];
        let info = {};
        info.app_name = node.app_name;
        info.app_uuid = node.app_uuid;
        info.app_type = node.app_type;
        host_app_info.push(info);


        let params = {
            'task_uuid': task_uuid,
            'agent_uuid': agent_uuid,
            'create_task_type': CONF.TASK_TYPE.VOL_CDP_TAKEOVER,
            'node_uuid': node_uuid,
            'backup_set_id':backup_set_id
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

    let getTimePointDetails = () => {
        pAjaxRequest({ timepoint_uuid: timepoint_uuid }, `/api/v1/backup_data/points/details`, "GET", (res)=>{
            let data = res.data;
            let storage_type = data.detail.storage_type;
            let storage_mount_point_list = data.detail.storage_mount_point_list;
            let node_uuid = data.detail.node_uuid;
            node_uuid_list = [];
            // 6/7/9:共享存储
            if(storage_type == CONF.BD_STORAGE_TYPE.NFS || storage_type == CONF.BD_STORAGE_TYPE.CIFS || storage_type == CONF.BD_STORAGE_TYPE.CLOUD){
                for(const list of storage_mount_point_list){
                    node_uuid_list.push(list.node_uuid);
                }
            }else{
                node_uuid_list.push(node_uuid);
            }
        });
    }


    /**
     * 客户端节点点击事件
     * @param treeId
     * @param treeNode
     * @param clickFlag
     */
    let nodeSelect = (treeId, treeNode, clickFlag) => {
        let treeObj = $.fn.zTree.getZTreeObj(treeId);
        // 单选
        treeObj.checkNode(treeNode, !treeNode.checked, true);
        nodeCheck('',treeId,treeNode);
    }

    /**
     * 获取主机网卡信息
     */
    let loadAgentNetworkInfo =  (agent_uuid) => {
        let data = {};
        data.agent_uuid = _createTaskMsgdata.master_agent_uuid;
        data.task_uuid = _createTaskMsgdata.task_uuid;
        pAjaxRequest(data,'/api/v1/complete_machine_volcdp/agent/network_info','GET',(d)=>{
            _agentNetworkInfo = d.data;
        })
    }

    /**
     *
     */
    let getTargetNetworkInfo = () => {
        pAjaxRequest({'offset':0,'limit':10},'/api/v1/virtual/network','GET',(res)=>{
            let rows = res.data.rows;
            _networkList = rows;
        })
    }

    /**
     * 获取备机网卡信息
     */
    let loadStandbyNetworkInfo = () => {
        let data = {};
        data.agent_uuid = _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid;
        pAjaxRequest(data,'/api/v1/complete_machine_volcdp/networkcard_info','GET',(d)=>{
            _standbyNetworkInfo = d.data;
        })
    }

    /**
     * 获取接管备机
     */
    let getTakeoverTargetHost = () => {
        let data = {
            master_uuid: _createTaskMsgdata.master_agent_uuid,
            master_os_type: _createTaskMsgdata.os_type,
            task_type: CONF.TASK_TYPE.VOL_CDP_TAKEOVER
        }
        Metronic.blockUI({target:'#completeMachineVolcdptakeovercontent',animate: true});
        pAjaxRequest(data,'/api/v1/complete_machine_volcdp/takeover_target_host','GET',(result)=>{
            Metronic.unblockUI('#completeMachineVolcdptakeovercontent');

            let cmBackupTaskTakeoverStandbySelect = $('#takeoverHost');
            cmBackupTaskTakeoverStandbySelect.empty();
            let data = result.data;
            for(let i=0; i<data.length; i++){
                let standbyAgentName = data[i].standby_agent_name;
                let standbyAgentuuid = data[i].standby_agent_uuid;
                let inTask  = data[i].in_task;
                let taskName = data[i].task_name;
                let onlineFlag = data[i].online_flag;
                let os_type = data[i].os_type;
                let option = $("<option>").text(standbyAgentName).val(standbyAgentuuid).attr("in_task",inTask).attr("task_name",taskName).attr('online_flag',onlineFlag).attr('agent_os_type',os_type);
                cmBackupTaskTakeoverStandbySelect.append(option);
            }
        });
    }

    /**
     * 脚本初始化
     * @return {boolean}
     */
    let initScript = () => {
        $('.takeoverAfterScript').initVinScript({class: 'takeoverAfterScript',include_list:[1,2]});
    }


    /**
     * test 第一步
     */
    let step1Valid = ()=>{
        if (!isLicense) {
            // 授权失效。不允许下一步
            UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE,  LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
            return false;
        }
        // STEP1校验
        // 勾选数据源
        if(!sourceNode.checked || sourceNode.length == 0){
            UIToastr.showWarning(LANG.UI_CM_VOL_CDP_TAKEOVER, LANG.UI_CM_VOL_CDP_TAKEOVER_PLEASE_SELECT_THE_TAKEOVER_DATA_SOURCE_FIRST);
            return false;
        }
        let backupSetConfigInfo =  volCdpBackupSetTimeLineInfo.getBackupSetConfigInfo();
        if (!backupSetConfigInfo.time_point_is_valid) {
            // 时间点无效
            UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TIME_MESSAGE);
            return false;
        }
        // 这里需要判断下当前的点是否有系统分区
        systemVolume = false; // 标记是否有系统分区
        for (var k in backupSetConfigInfo.vol_info) {
            if (backupSetConfigInfo.vol_info[k].system_volume) {
                systemVolume = true;
                break;
            }
        }
        _createTaskMsgdata.takeover_timestamp = backupSetConfigInfo.time_point;
        timepoint_uuid = backupSetConfigInfo.time_point_uuid;
        _createTaskMsgdata.timepoint_uuid = backupSetConfigInfo.time_point_uuid;
        // 校验接管时间点
        if(!backupSetConfigInfo.time_point_is_valid){
            UIToastr.showWarning(LANG.UI_CM_VOL_CDP_TAKEOVER, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
            return false;
        }
        // 预加载
        $('.takeoverBuiltInVmConf_div').empty();
        $('.takeoverNetConf').hide();
        $('.mountTakeover').hide();
        $('.notakeoverTargetTips').show();
        // 获取主机网卡信息
        loadAgentNetworkInfo();
        // 获取接管备机
        getTakeoverTargetHost();
        // 初始化脚本
        initScript();
        getTimePointDetails();
        initVirusCheck();
        let isFlag = isSystemVolume();
        return isFlag;
    }


    /* -------------------STEP2-------------------*/

    /**
     * 切换接管方式
     */
    let changeRadio = function (){
        let mode = $(this).data('mode');
        if(!systemVolume && mode == 1){
            UIToastr.showWarning(
                LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,
                LANG.UI_CM_CDP_SYSTEM_VOL_NO_CHECKED_USED_CM_TAKEOVER
            );
            setTimeout(() => {
                $('#takeoverMethod').find('input[data-mode=2]').iCheck("check");
            }, 10); // 10ms 延迟确保 iCheck 已完成内部处理
            return;
        }
        // 1:整机接管
        // 2:挂载接管
        $('.notakeoverTargetTips').show();
        $('.apptakeoverswitch').bootstrapSwitch('state', false, true);
        $('.takeover_app_tree_div').hide();
        switch (Number(mode)){
            case 1:
                takeoverMethodFlag = true;
                proxyMethod = true;
                recoverType = true;
                takeoverSourceUuid = '';
                $('#takeoverHost').closest('.form-group').hide();
                $('.takeovernetworkconf').closest('.form-group').hide();
                $('#cmstandbymapconf').closest('.form-group').show();
                $('#completeMachineTakeover').show();
                if(_standbyIsConf){
                    $('.takeoveripdriftview').show();
                }
                $('.mountTakeover').hide();
                $('.takeoverBuiltInVmConf_div').empty();
                targetVisibleFlag = false;
                targetInputFlag = false;
                _selectTargetType = true;
                type = 5;
                _createTaskMsgdata.takeover_object.takeover_business_ip_map = [];
                targetOsType = '';
                $('.tab_transfer_li').addClass('active');
                $('#tab_transfer').addClass('active');
                $('.safe_strategy_li').removeClass('display-none-force');
                $('.safe_strategy_li').removeClass('active');
                $('#safe_strategy').removeClass('display-none-force');
                $('#safe_strategy').removeClass('active');
                $('.safe_stategy_des_div').show();
                $('.cmtakeovermodehelp').show();
                $('.mounttakeovermodehelp').hide();
                break;
            case 2:
                checkCompleteTakeover();
                break;
        }
    }

    /**
     * 默认选中整机接管 type == 6
     */
    let checkCompleteTakeover = function (){
        takeoverMethodFlag = false;
        proxyMethod = false;
        recoverType = false;
        takeoverSourceUuid = _createTaskMsgdata.master_agent_uuid;
        getTakeoverTargetHost();
        $('.takeoverNetConf').hide();
        $('.mountTakeover').hide();
        $('#takeoverHost').closest('.form-group').show();
        $('#cmstandbymapconf').closest('.form-group').hide();
        _takeoverIpMapList = [];
        $('.hm_takeover_ip_map_list').empty();
        targetVisibleFlag = true;
        targetInputFlag = true;
        _selectTargetType = false;
        type = 6;
        _createTaskMsgdata.takeover_object.takeover_vm = {};
        _createTaskMsgdata.takeover_object.takeover_business_ip_map = [];
        $('.tab_transfer_li').addClass('active');
        $('#tab_transfer').addClass('active');
        $('.safe_strategy_li').addClass('display-none-force');
        $('#safe_strategy').addClass('display-none-force');
        $('.safe_stategy_des_div').hide();
        $('.cmtakeovermodehelp').hide();
        $('.mounttakeovermodehelp').show();
    }

    /**
     * 切换接管备机
     */
    let changeTakeoverHost = () => {
        $('.hm_takeover_ip_map_list li').remove();
        $('.apptakeoverswitch').bootstrapSwitch('state', false, true);
        $('.takeover_app_tree_div').hide();
        let selectedOption = $(this).find('option:selected');
        if (selectedOption.is(':first-child')) {
            return;
        }
        let selectId = "takeoverHost";
        let standbyInTask = $('#'+selectId).find("option:selected").attr("in_task");
        let onlineFlag = $('#'+selectId).find("option:selected").attr("online_flag");
        let taskName = $('#'+selectId).find("option:selected").attr("task_name");

        let takeoverHostUuid = $('#takeoverHost option:selected').val();
        let takeoverHostText = $('#takeoverHost option:selected').text();
        if(takeoverHostUuid == ''){
            _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid = '';
            $('.takeoverNetConf').hide();
            $('.mountTakeover').hide();
            $('.notakeoverTargetTips').show();
            return false;
        }

        if(standbyInTask == true){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_STANDBY_CHANGE_DESC + LANG.UI_PLATFORM_ASSOCIA_TASK+ " " + taskName + " "  );
            return false;
        }

        if(onlineFlag != CONF.FLAG.SET && takeoverHostUuid != ''){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_VOL_CDP_BACKUP_AUTOTAKEOVER_STANDBY_OFFLINE_TIPS );
            return false;
        }

        // 隐藏提示
        $('.notakeoverTargetTips').hide();

        // 显示配置接管网络
        $('.takeoverNetConf').show();
        $('.mountTakeover').show();
        $('.takeoverHostName').html(takeoverHostText);
        // 接管卷配置
        _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid = takeoverHostUuid;
        takeoverTargetName = $('#takeoverHost option:selected').text();
        // 目标设备系统类型
        targetOsType = $("#takeoverHost").find("option:selected").attr('agent_os_type');
        getTargetConfig();
        // 获取备机网卡信息
        loadStandbyNetworkInfo();
        // 应用配置
        getClientTakeoverAppInfo();
    }

    /**
     * 更新客户端信息，包括客户端对应的卷信息和应用信息
     */
    let updateAgentInfo = function(agent_uuid){
        Metronic.blockUI({target: "#completeMachineVolcdptakeovercontent",animate: true});
        pAjaxRequest({},'/api/v1/complete_machine_volcdp/update/agent/'+agent_uuid,'PUT',(d)=>{
            Metronic.unblockUI("#completeMachineVolcdptakeovercontent");
            let data = d.data;
            if(operateResponseList(d)){
                let appScenedValue = $('#changeAppScened').val();
                if(appScenedValue== CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER){
                    $('.applianceselectview').show();
                }else{
                    $('.applianceselectview').hide();
                }

                _standbyIsConf = true;
                _standbyHostMountPoint = $("#takeoverTargetHostSelect").find("option:selected").attr("data-mountpoint");  //备机已占用的挂载点
                _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid = agent_uuid;

                $('.targetHostUsedPartition').css("display","block");
            }
            if(!data.success){
                $('#takeoverTargetHostSelect option:first').prop('selected', true);
                $('.targetHostUsedPartition').css("display","none");
                _standbyIsConf = false;
                return false;
            }else{
                getTakeOverTargetHost();
            }
        })
    }

    /**
     * 获取接管目标卷配置
     */
    let getTargetConfig = () => {
        diskConfigParams = {
            type: type,                             //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管
            origin_task_uuid: task_uuid,            //源任务uuid(实时,定时可不传或为空)  node.task_uuid
            target_os_type:targetOsType,            //目标机操作系统类型 windows还是linux，只有type为3和6在使用,其他type可不传
            origin_agent_uuid: agent_uuid,          //源代理主机uuid(实时,定时可不传或为空)  node.agent_uuid
            origin_timepoint_uuid: timepoint_uuid,  //源时间点uuid或时间点(实时,定时)  node.timepoint_uuid
            target_agent_uuid: _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid,                  //目标代理主机uuid(目标端主机)  目标机
            target_visible_flag: targetVisibleFlag, //控制恢复目标的列是否显示 true显示,false不显示
            recover_type: false,                    //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
            target_auto_flag: true,                //是否显示恢复目标选择框/磁盘挂载点的自动分配选项
            target_input_flag:targetInputFlag,      //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
            target_input_min: 1,                    //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
            origin_data_array:[],                   //前端源信息,当origin_data_flag为true时使用这个数据
            nocheck: !targetInputFlag,                         //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
            forced_relationship: true,              //是否强制关联勾选.默认为强制关联勾选
            first_column_name:LANG.UI_MACHINE_OS_SOURCE_DISK_NAME,
            second_column_name:LANG.UI_MACHINE_OS_SIZE,
            third_column_name:LANG.UI_CM_VOL_CDP_TAKEOVER_DISK_MOUNT_POINT,
            loading:'#takeoverConf',
        }
        $('#volinfo').initTargetPlug(diskConfigParams);
    }

    /**
     * 挂载接管（配置接管网络）
     */
    let takeoverNetworkConfModal = () => {

        // 清除接管网络
        _createTaskMsgdata.takeover_object.takeover_business_ip_map = [];

        if(!_createTaskMsgdata.takeover_object.takeover_standby_agent_uuid){ //备机类型为代理客户端，备机uuid为空时
            UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION, LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION_TIPS);
            return false;
        }
        if(_standbyNetworkInfo.length == 0 ){
            UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION, LANG.UI_VOL_CDP_TAKEOVER_NETWORK_NULL_TIPS);
            return false;
        }

        $('#hmTakeoverNetworkConfModal').modal({'width':'800px', 'height':'380px'});
        $('#hm_takeover_ip_server_conf').takeoverIpServerMapConfig({
            agentNetwork:_agentNetworkInfo,
            standbyNetwork: _standbyNetworkInfo,
            takeoverHisNetwork:[],
            failbackHisNetwork:[],
        });

        $('#hm_standby_gateway_conf').takeoverStandbyGatewayConfig({
            agentNetwork:_agentNetworkInfo,
            standbyNetwork: _standbyNetworkInfo,
            takeoverHisNetwork:[],
            failbackHisNetwork:[],
        });
        $("#submit_hm_takeover_network_conf").unbind('click').click(submitHostNetworkConf);
    }

    /**
     * @function 生成uuid
     */
    let getUuid = function() {
        let len = 36;//36长度
        let radix = 16;//16进制
        let chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        let uuid = [], i;
        radix = radix || chars.length;
        if(len) {
            for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
        } else {
            let r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for(i = 0; i < 36; i++) {
                if(!uuid[i]) {
                    r = 0 | Math.random() * 16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    }

    /**
     * 配置备机映射
     */
    let builtInVmSubmitInfo = () => {
        let data = $.fn.vmConfig.getData();
        if(!data){
            return false;
        }
        _createTaskMsgdata.takeover_object.takeover_vm = data.vm_temp_object.takeover_vm;
        _createTaskMsgdata.takeover_object.host_reset_name = data.vm_temp_object.host_reset_name;
        _createTaskMsgdata.takeover_object.cross_platform_flag = data.vm_temp_object.cross_platform_flag;
        _createTaskMsgdata.takeover_object.cross_platform_conf = data.vm_temp_object.cross_platform_conf;
        _createTaskMsgdata.takeover_object.takeover_business_ip_map = data.takeover_business_ip_map;

        _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid = data.vm_temp_object.takeover_vm.config.vm_uuid;
        takeoverTargetName = data.vm_temp_object.takeover_vm.config.vm_name;
        vmTempText = data.vm_temp_text;
        takeoverTargetHostDes = data.vm_temp_object.disk_config_str;
        $('.takeoverBuiltInVmConf_div').empty();
        $('.takeoverBuiltInVmConf_div').append(data.vm_temp_des);
        // 整机接管备机
        _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid = data.vm_temp_object.takeover_vm.uuid;
        $('.mountTakeover').show();
        $('.takeoverHostName').html(data.modal_vm_name);
        $('.notakeoverTargetTips').hide();
        // 接管卷配置
        getTargetConfig();
        // 应用配置
        getClientTakeoverAppInfo();
        $('.apptakeoverswitch').bootstrapSwitch('state', false, true);
        $('.takeover_app_tree_div').hide();
    }

    /**
     * 获取配置网卡描述信息，并渲染配置
     */
    let submitHostNetworkConf = () => {
        let networCardConfInfo = {};
        let gateAwyConfDesList = [];

        _standbyGatewayConf = getStandbyGateWayConf();
        let checkIp = checkIpFormat(_standbyGatewayConf);
        if (!checkIp) {
            _takeoverIpMapList = [];
            UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_STANDBY_DEFAULT_NETCARD_FOFMAT_ERROR);
            return false;
        }
        let standbyNetCardConfList = standbyNetCardConf();
        let ipMapInfoList = [];
        _takeoverIpMapListView = [];
        let selectedNetCards = 0;  // 新增变量，用于跟踪选中的网卡数量

        for (let i = 0; i < _agentNetworkInfo.length; i++) {
            let ipSetStr = "";
            let ipMapInfo = {};
            let nicIpList = [];
            let agentMapInfo = {};
            ipMapInfo.nic_gateway = _agentNetworkInfo[i].gateway_address;
            ipMapInfo.nic_name = _agentNetworkInfo[i].name;
            ipMapInfo.nic_mac = _agentNetworkInfo[i].mac_address;

            let ipSet = _agentNetworkInfo[i].ip_set;
            if (!ipSet) {
                break;
            }

            let liId = getUuid();
            let hostMac = $("#host_network_mac_" + i).text();
            //当前选中主机网卡名
            let activeNetCardText = $('#hostNetCardTab').find('li.active').filter(function () {
                return $(this).closest('ul').is('ul');
            }).text();

            for (let j = 0; j < ipSet.length; j++) {
                let nicIpInfo = {};
                let standbyNetCardSelect = "standby_network_card_" + i + j;
                let targetHostMacStr = $("#" + standbyNetCardSelect).find("option:selected").attr("host-mac");
                let targetHostMac = $("#" + standbyNetCardSelect).val();

                let hostIp = $("#" + standbyNetCardSelect).find("option:selected").attr("host-ipdrr");
                let netMask = $("#" + standbyNetCardSelect).find("option:selected").attr("agent-netmask");
                let targetGateway = $("#" + standbyNetCardSelect).find("option:selected").attr("standby-gateway");
                let targetNicName = $("#" + standbyNetCardSelect).find("option:selected").text();

                if (typeof (netMask) == 'undefined' && typeof (targetGateway) == "undefined") {
                    continue;
                }

                if (hostIp) {
                    selectedNetCards++;  // 增加选中的网卡数量
                    nicIpInfo.ip = hostIp;
                    nicIpInfo.netmask = netMask;
                    nicIpInfo.target_nic_name = targetNicName;
                    nicIpInfo.target_nic_mac = targetHostMac;
                    standbyNetCardConfList.some(item => {
                        if (item.selectNicname == targetNicName) {
                            targetGateway = item.selectGateway;
                        }
                    });
                    let targetGatewayStr = targetGateway || "--";
                    nicIpInfo.target_gateway = targetGateway;
                    nicIpList.push(nicIpInfo);
                    let gatewayStr = "(" + LANG.UI_DRILLS_NETWORK_WAY + ":" + targetGatewayStr + ")";
                    ipSetStr += hostIp + gatewayStr + LANG.UI_VOL_CDP_HOST_IP_MAP_TO_STANDBY + ":" + targetNicName + "  ";
                }
            }

            if (nicIpList.length > 0) {
                ipMapInfo.nic_ip_info = nicIpList;
                agentMapInfo.agent_map_info = ipMapInfo;
                agentMapInfo.conf_id = liId;
                agentMapInfo.conf_des = LANG.UI_VOL_CDP_HOST_NETCARD + ": " + _agentNetworkInfo[i].name + " [" + ipSetStr + "] ";
                ipMapInfoList.push(agentMapInfo);
                _takeoverIpMapListView.push(agentMapInfo);
            }
        }
        if (selectedNetCards == 0) {  // 如果没有选中任何网卡，弹出提示
            UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_CHANGE_MAP_NETCARD)
            return;
        }

        if (ipMapInfoList.length > 0) {
            _takeoverIpMapList = ipMapInfoList;
            $('.hm_takeover_ip_map_list li').remove();
            hostGatewayConfDes();
        }

        $('#hmTakeoverNetworkConfModal').modal('hide');
    }

    /**
     * 获取备机网卡配置
     */
    let getStandbyGateWayConf = () => {
        let list = [];
        for(let i = 0; i<_standbyNetworkInfo.length;i++){
            let gatewayInfo = {};
            let hostMac = _standbyNetworkInfo[i].mac_address
            hostMac = hostMac.replace(/:/g,'');
            gatewayInfo.netcard = _standbyNetworkInfo[i].name;
            let hostGateway = $('#standby_netcard_'+hostMac).val()
            if(hostGateway==""){
                hostGateway = "0.0.0.0";
            }
            gatewayInfo.gateway = hostGateway;
            list.push(gatewayInfo);
        }
        return list;
    }

    //获取备机网卡信息，用于替换主机映射网卡中的备机网关
    let standbyNetCardConf = function(){
        let standbyNetCardInfo = {};
        let standbyNetCardList = [];
        for(let i = 0;i<_standbyNetworkInfo.length;i++){
            let standbyGatewaySelectId = "standby_netcard_macaddress_"+_standbyNetworkInfo[i].name;
            let selectNicname = $("#"+standbyGatewaySelectId).find("option:selected").attr("agent-nicname");
            let selectGateway = $("#"+standbyGatewaySelectId).val();
            standbyNetCardInfo.selectNicname = selectNicname;
            standbyNetCardInfo.selectGateway = selectGateway;
            standbyNetCardList.push(standbyNetCardInfo);
        }
        return standbyNetCardList;
    }

    /**
     * 校验网关格式
     */
    let checkIpFormat = function (){
        let ipVerify = true;
        for(let i=0;i<_standbyGatewayConf.length;i++){
            let gateway = _standbyGatewayConf[i].gateway;
            if(gateway=="" && !ipV4V6(gateway)){
                ipVerify = false;
                break;
            }
        }
        return ipVerify;
    }

    /**
     * 为每一个<li>绑定点击事件，并处理移除数组操作
     */
    let clickHandler = function(event){
        let className = this.className;
        let splitClassName = className.split('takeover_netcard_del');
        let eventConfID = splitClassName[1];
        $('.popover.in').remove();
        let index = event.data.index;
        let confId;
        let list = _takeoverIpMapListView;
        $('.takeoveripdriftview').hide(); 	//隐藏ip漂移提示
        // 修正删除最后一个元素的问题
        if (index >= list.length) {
            index = list.length - 1;
        }

        // 查找并删除 _takeoverIpMapListView 中的元素
        if (index >= 0) {
            confId = list[index].conf_id;
            $('#takeover_netcard_' + confId).remove();
            _takeoverIpMapListView.splice(index, 1);
            _createTaskMsgdata.takeover_object.takeover_business_ip_map.splice($.inArray(confId, _createTaskMsgdata.takeover_object.takeover_business_ip_map), 1);
        }

        // 确保 confId 已经被正确获取
        if (confId) {
            // 查找并删除 ipMapInfoList 中的元素
            ipMapInfoList = ipMapInfoList.filter(function(item) {
                return item.conf_id !== confId;
            });

            // 查找并删除 _takeoverIpMapList 中的元素
            _takeoverIpMapList = _takeoverIpMapList.filter(function(item) {
                return item.conf_id !== confId;
            });
        }

    }

    /**
     * 获取配置网卡描述信息，并渲染
     */
    let hostGatewayConfDes = () => {
        let des = "";
        for (let i = 0; i < _takeoverIpMapList.length; i++) {
            let conf_id = _takeoverIpMapList[i].conf_id;
            des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+ _takeoverIpMapList[i].conf_id
                +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
                + _takeoverIpMapList[i].conf_des + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 100%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
                + _takeoverIpMapList[i].conf_des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="takeover_netcard_del'+  _takeoverIpMapList[i].conf_id +'" >'
                +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
            $('.hm_takeover_ip_map_list').append(des);
            $('.takeoverNetcardTips').popover();	   //初始化tips
            ipMaplist = {};
            ipMaplist.nic_name = _takeoverIpMapList[i].agent_map_info.nic_name;
            ipMaplist.nic_gateway = _takeoverIpMapList[i].agent_map_info.nic_gateway;
            ipMaplist.nic_mac = _takeoverIpMapList[i].agent_map_info.nic_mac;
            ipMaplist.nic_ip_info = _takeoverIpMapList[i].agent_map_info.nic_ip_info;
            ipMaplist.nic_netmask = _takeoverIpMapList[i].agent_map_info.nic_ip_info[0].netmask;
            ipMaplist.conf_id = _takeoverIpMapList[i].conf_id;
            ipMaplist.conf_des = _takeoverIpMapList[i].conf_des;
            _createTaskMsgdata.takeover_object.takeover_business_ip_map.push(ipMaplist);
            $('.takeover_netcard_del'+ conf_id ).bind("click",{index:i},clickHandler);
        }
        let appScened = $('#changeAppScened').val();
        if(appScened==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER){
            $('.takeoveripdriftview').show(); 	//隐藏ip漂移提示
        }else{
            $('.takeoveripdriftview').hide(); 	//隐藏ip漂移提示
        }
    }

    /**
     * 内嵌虚拟机相关映射配置，需要根据备份模式判断加载的内容
     */
    let standbyMapConf = function (e){
        // 阻止默认行为
        e.preventDefault();
        // 阻止事件冒泡
        e.stopPropagation();
        //判断应急接管是否授权
        let params = {};
        params.type = 'emergency_takeover';
        Metronic.blockUI({target: "#completeMachineVolcdptakeovercontent",animate: true});
        pAjaxRequest(params, "/api/v1/complete_machine_volcdp/emergency_takeover_auth_info", "GET", function (d) {
            Metronic.unblockUI("#completeMachineVolcdptakeovercontent");
            let data = d.data;
            let authResult = data.auth_result;
            if(authResult){
                $('#standby_map_conf_drawer').drawer('show');
                loadTakeoverStandbyMapConf();  //加载接管备机
            }else{
                UIToastr.showWarning(LANG.UI_PUBLIC_OBTAIN_LICENSE_INFO, LANG.UI_CM_CDP_EMERGENCY_TAKEOVER_AUTH_NUM_TIPS);
                return false;
            }
        });
    }

    /**
     * 加载接管备机
     */
    let loadTakeoverStandbyMapConf = () => {
        // $('#vm_machine_modal').modal({'width':'800px', 'height':'380px'});
        // 参数
        // 源机agentuuid  _createTaskMsgdata.master_agent_uuid
        // 任务类型task_type
        // 节点nodeuuid  $('#nodeselect option:selected').val();
        // 源机网卡信息 source _agentNetworkInfo
        // 网络列表 target  bd_emd_network
        diskConfigParams = {
            type: type,                             //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管
            origin_task_uuid: task_uuid,            //源任务uuid(实时,定时可不传或为空)  node.task_uuid
            origin_agent_uuid: agent_uuid,          //源代理主机uuid(实时,定时可不传或为空)  node.agent_uuid
            origin_timepoint_uuid: timepoint_uuid,  //源时间点uuid或时间点(实时,定时)  node.timepoint_uuid
            target_agent_uuid: '',                  //目标代理主机uuid(目标端主机)  目标机
            target_visible_flag: targetVisibleFlag, //控制恢复目标的列是否显示 true显示,false不显示
            recover_type: false,                    //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
            target_auto_flag: true,                //是否显示恢复目标选择框/磁盘挂载点的自动分配选项
            target_input_flag:targetInputFlag,      //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
            target_input_min: 1,                    //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
            origin_data_array:[],                   //前端源信息,当origin_data_flag为true时使用这个数据
        }
        $.fn.vmConfig.init({
            agent_uuid: _createTaskMsgdata.master_agent_uuid,
            task_type:CONF.TASK_TYPE.VOL_CDP_TAKEOVER,
            node_uuid:_createTaskMsgdata.node_uuid,
            node_uuid_list:node_uuid_list,
            source_netcard_conf: _agentNetworkInfo,    // 源机网卡信息
            timepoint_uuid:_createTaskMsgdata.timepoint_uuid,
            disk_config_params:diskConfigParams,      // 磁盘参数
            disk_uuids:[],                            // 备份选中磁盘
            os_type:_createTaskMsgdata.os_type
        });
    }


    /**
     * 应用配置
     */
    let getClientTakeoverAppInfo = () => {
        if(!_createTaskMsgdata.takeover_object.takeover_standby_agent_uuid){
            $('#noTakeoverApp').show();
            $('#takeover_app_tree').empty();
            return;
        }else{
            $('#noTakeoverApp').hide();
        }
        let data = {};
        data.agent_uuid = _createTaskMsgdata.master_agent_uuid;
        data.agent_ip = _createTaskMsgdata.master_agent_ip;
        data.agent_name = _createTaskMsgdata.name;
        data.take_over_time = _createTaskMsgdata.takeover_timestamp;
        data.standby_host_uuid = _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid;
        data.task_uuid = _createTaskMsgdata.task_uuid;
        Metronic.blockUI({target: "#appConfig",animate: true});
        pAjaxRequest(data,'/api/v1/complete_machine_volcdp/app_info','GET',setTree);
    }

    let setTree = (d) => {
        Metronic.unblockUI("#appConfig");
        let zNodes = d.data;
        if(zNodes.length == 0){
            return;
        }
        let setting = {
            check: {
                enable: true,
                nocheckInherit: false
            },
            data: {
                simpleData: {
                    enable: true,
                    rootPId: 0
                },key: {
                    title: "title"
                }
            },
            view: {
                fontCss: getAppFontCss,
                addDiyDom: addHoverDom,
            },
        };
        zTreeApp = $.fn.zTree.init($("#takeover_app_tree"), setting, zNodes);
    }

    let escapeJquery = (srcString) => {
        // 转义之后的结果
        let escapseResult = srcString.toString();
        // javascript正则表达式中的特殊字符
        let jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
            "]", "|", "{", "}"];
        // jquery中的特殊字符,不是正则表达式中的特殊字符
        let jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
            ":", ";", "<", ">", ",", "/"];
        for (let i = 0; i < jsSpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp("\\"
                + jsSpecialChars[i], "g"), "\\"
                + jsSpecialChars[i]);
        }
        for (let i = 0; i < jquerySpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                "g"), "\\" + jquerySpecialChars[i]);
        }
        return escapseResult;
    }

    /**
     * 动态调整应用节点样式
     * @param treeId
     * @param treeNode
     * @return {{color: string, "font-weight": string}}
     */
    let getAppFontCss = (treeId, treeNode) => {
        let css = {color:"#333", "font-weight":"normal"};
        if(!!treeNode.inbackup){
            css = {color:"green", "font-weight":"bold"};
        }
        if(!!treeNode.highlight){
            //搜索使用的样式
            css = {color:"#A60000", "font-weight":"bold"};
        }
        return css;
    }

    /**
     * 添加应用更新按钮
     * @param treeId
     * @param treeNode
     */
    let addHoverDom = (treeId, treeNode) => {
        let nodeID = escapeJquery(treeNode.id);
        let nodeTID = escapeJquery(treeNode.tId);
        let aObj;
        let str;
        if(treeNode.nodeType=='host'){  //主机
            aObj = $("#" + nodeTID + "_a");
            str = '<a id="refresh_' + treeNode.tId + treeNode.id + '_host" title="'+LANG.UI_VOL_CDP_BACKUP_REFRESH_CLIENT_APPLICATION+'" class="apptreehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>';
        }else if(treeNode.nodeType=='app'){  //应用
            aObj = $("#" + nodeTID + "_a");
            str = '<a id="refresh_' + treeNode.tId + treeNode.id + '_app" title="'+LANG.UI_VOL_CDP_BACKUP_REFRESH_APPLICATION+'" class="apptreehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>';
        }else{ return;}
        aObj.after(str);
        let hrefRefreshHost = $("#refresh_" + nodeTID + nodeID + "_host");
        let hrefRefreshApp = $("#refresh_" + nodeTID + nodeID + "_app");

        if (hrefRefreshHost) hrefRefreshHost.bind("click", function(){
            getSyncHostAppInfo(treeId, treeNode, 'host',true);
        });
        if (hrefRefreshApp) hrefRefreshApp.bind("click", function(){
            getSyncHostAppInfo(treeId, treeNode, 'app',true);
        });
    }

    /**
     * 应用接管
     */
    let appTakeoverSwitch = function (){
        if(!backup_agent_flag){
            // 先解除事件绑定，避免循环触发
            $('.apptakeoverswitch').off('switchChange.bootstrapSwitch').bootstrapSwitch('state', false);
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_CM_CDP_BACKUP_APP_NO_CONFIG);
            // 重新绑定事件
            $('.apptakeoverswitch').on('switchChange.bootstrapSwitch', appTakeoverSwitch);
            return false;
        }
        appTakeoverSwitchFlag = this.checked;
        if(this.checked){
            if(type == 5){
                // 容灾演练平台
                app_info = [];
                for(let i=0;i<host_app_info.length;i++){
                    let info = {};
                    info.master_app_uuid = host_app_info[i].app_uuid;
                    info.standby_app_uuid = host_app_info[i].app_uuid;
                    info.app_name = host_app_info[i].app_name;
                    app_info.push(info);
                }
                _createTaskMsgdata.takeover_object.app_set = app_info;
            }else{
                let info = {};
                info.agent_uuid = _createTaskMsgdata.master_agent_uuid;
                info.app_list = host_app_info;
                info.standby_host_uuid = _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid;
                pAjaxRequest(info, "/api/v1/complete_machine_volcdp/check_standby_app", "post", function (result) {
                    if(result.success){
                        if(result.data.length == 0){
                            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_CM_CDP_VERIFY_STANDBY_APP_TIPS);
                            $('.apptakeoverswitch').bootstrapSwitch('state', false);
                            $('.takeover_app_tree_div').hide();
                            return false;
                        }else{
                            $('.takeover_app_tree_div').show();
                        }
                    }else{
                        $('.apptakeoverswitch').bootstrapSwitch('state', false);
                        $('.takeover_app_tree_div').hide();
                        UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_CM_CDP_STANDBY_APP_NO_CONFIG);
                    }
                });
            }
        }else{
            $('.appmonitorswitch').bootstrapSwitch('state', false); //关闭应用故障检测开关
            $('.takeover_app_tree_div').hide();
            _createTaskMsgdata.takeover_object.app_set = [];
        }
    }

    /**
     * 更新客户端下所有应用信息/更新选定应用信息
     * @param treeId
     * @param treeNode
     * @param refreshType
     * @param expendFlag
     */
    let getSyncHostAppInfo = (treeId, treeNode,refreshType) => {
        let requestData = {
            id:treeNode.id,
            uuid:treeNode.uuid,  // 客户端uuid or 应用uuid
            agent_uuid:treeNode.agent_uuid,
            refresh_type:refreshType  // 客户端 or 应用
        };
        Metronic.blockUI({target: "#appConfig",animate: true});
        pAjaxRequest(requestData,'/api/v1/complete_machine_volcdp/update/app','PUT',(d)=>{
            if(operateResponseList(d)){
                setTree();
            }
        });
    }

    /**
     * 获取接管目标卷
     */
    let getTakeoverTargetVol = () => {
        takeoverTargetHostDes = '';
        let changeData = $('#volinfo').getTargetData('getEachData',timepoint_uuid);
        _createTaskMsgdata.takeover_object.vol_mount_point_set = [];
        for(let i=0;i<changeData.target_strategy.length;i++){
            let relation_set_list = {};
            relation_set_list.vol_uuid = changeData.target_strategy[i].source_dev_uuid;
            if(changeData.target_strategy[i].destination_dev_node_uuid == '2' && changeData.target_strategy[i].destination_dev_uuid == ''){
                relation_set_list.target_mount_point = '';
            }else{
                relation_set_list.target_mount_point = changeData.target_strategy[i].destination_name
            }
            relation_set_list.source_name = changeData.target_strategy[i].source_name;
            relation_set_list.destination_name = changeData.target_strategy[i].destination_name;
            _createTaskMsgdata.takeover_object.vol_mount_point_set.push(relation_set_list);
        }
        _createTaskMsgdata.takeover_object.takeover_disk_list = [];
        for(let i=0;i<changeData.disk_list.length;i++){
            takeoverTargetHostDes += changeData.disk_list[i]['dev_name'] + ',';
            _createTaskMsgdata.takeover_object.takeover_disk_list.push(changeData.disk_list[i].dev_uuid);
        }
    }

    /**
     * 获取脚本配置
     */
    let getScriptConfig = () => {
        afterScriptContent = LANG.UI_CM_NOT_CONFIGURED;
        // 接管后
        let cmAfterTakeoverScript = $('#takeoverAfterScript').getVinScript('takeoverAfterScript');
        _createTaskMsgdata.script_list.after_takeover_script_location = cmAfterTakeoverScript;
        if(cmAfterTakeoverScript.length >0){
            afterScriptContent = '';
            for(const script of cmAfterTakeoverScript){
                afterScriptContent += `${script.script_content},`;
            }
        }
    }

    /**
     * 获取step2选择的应用信息
     * @returns {boolean}
     */
    let getAppInfo = () => {
        if(appTakeoverSwitchFlag){
            var appTree = $.fn.zTree.getZTreeObj("takeover_app_tree");
            var checkedNodes = appTree.getCheckedNodes(true);
            _createTaskMsgdata.takeover_object.app_set = [];
            if(checkedNodes.length>0){
                app_info = [];
                for(var i=0;i<checkedNodes.length;i++){
                    let info = {};
                    if(checkedNodes[i].isApp){
                        info.master_app_uuid = checkedNodes[i].uuid;
                        info.standby_app_uuid = checkedNodes[i].uuid;
                        info.app_name = checkedNodes[i].name;
                        app_info.push(info);
                    }
                }
                _createTaskMsgdata.takeover_object.app_set = app_info;
            }
        }else{
            _createTaskMsgdata.takeover_object.app_set = [];
        }
    }


    let step2Valid = ()=>{
        // STEP2检测
        if(type ==6 && _createTaskMsgdata.takeover_object.takeover_standby_agent_uuid == ''){
            UIToastr.showWarning(LANG.UI_VOL_CDP_MOUNT_TAKEOVER_DESC,LANG.UI_CM_VOL_CDP_TAKEOVER_PLEASE_SELECT_THE_STANDBY_MACHINE_FOR_TAKEOVER);
            return false;
        }


        if(type ==6 && _createTaskMsgdata.takeover_object.takeover_business_ip_map.length == 0){
            // 挂载接管未配置接管网络
            _createTaskMsgdata.takeover_object.master_agent_ip_switch_flag = '2';
            UIToastr.showWarning(LANG.UI_CM_TAKEOVER_CONFIGURE,LANG.UI_CM_CDP_TAKEOVER_NIC_CONF_TIPS);
        }
        if(type ==5 && Object.keys(_createTaskMsgdata.takeover_object.takeover_vm).length === 0){
            // 整机接管未配置备机映射
            UIToastr.showWarning(LANG.UI_CM_VOL_CDP_TAKEOVER,LANG.UI_CM_VOL_CDP_TAKEOVER_NO_STANDBY_MACHINE_MAPPING_CONFIGURED);
            return false;
        }
        // 接管卷
        if(type == 6){
            getTakeoverTargetVol();
            // 获取以选择的应用信息
            getAppInfo();
        }
        // 脚本
        getScriptConfig();
        preStep3();
    }

    let preStep3 = () => {
        // 初始化传输网络
        initNetworkList();
    }

    /**
     * 初始化传输网络
     */
    let initNetworkList = () => {
        let nodeUuid = _createTaskMsgdata.node_uuid;
        if(networkFlag && nodeUuid!=""){   //初始化传输网络
            $('.transfernetworkview').show();
            $('.transfer_network_des_div').show();
            $('#transferNetworkTree').transferNetwork({node_uuid: nodeUuid}); 	//初始化节点传输网络列表
        }else{
            $('.transfernetworkview').hide();
            $('.transfer_network_des_div').hide();
        }
    }

    /**
     * 初始化病毒检测
     */
    let initVirusCheck = () => {
        // 定义病毒检测组件
        $('#wormConfig').takeAppOver(
            'col-md-2',
            CONF.RECOVER_POLICY.DIRECT_TAKE,
            CONF.INTERRUPT.STOP,
            {
                os_type: os_type
            },
        );
    };

    /**
     * 是否有系统分区的备份点
     */
    let isSystemVolume = () => {
        let isSystemVolumeFlag = true;
        if(!systemVolume && CONF.VENDOR == 'sangfor'){
            UIToastr.showWarning(
                LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,
                LANG.UI_CM_CDP_SYSTEM_VOL_NO_CHECKED_USED_CM_TAKEOVER
            );
            isSystemVolumeFlag = false;
        }else{
            if (!systemVolume) {
                // 1. 默认选中 data-mode=2
                $('#takeoverMethod').find('input[data-mode=2]').iCheck("check");
                checkCompleteTakeover();
                UIToastr.showWarning(
                    LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,
                    LANG.UI_CM_CDP_SYSTEM_VOL_NO_CHECKED_USED_CM_TAKEOVER
                );
            }
            if(!CONF.PERMISSION.includes('vm_machine_manager')){
                // 容灾演练未授权
                $('#takeoverMethod').find('input[data-mode=1]').closest('label').hide();
                $('#takeoverMethod').find('input[data-mode=2]').iCheck("check");
                checkCompleteTakeover();
            }
        }
        return isSystemVolumeFlag;
    };

    /**
     * 组合接管业务网络
     */
    let groupTakeoverBusinessIpMap = function(){
        let takeoverIpMap = [];
        for(let i = 0;i<_takeoverIpMapList.length;i++){
            let ipMapConf = _takeoverIpMapList[i].agent_map_info;
            takeoverIpMap.push(ipMapConf);
        }

        for(let j=0;j<takeoverIpMap.length;j++){
            let nicIpInfo =  takeoverIpMap[j].nic_ip_info;
            for(let i =0;i<nicIpInfo.length;i++){
                let nicName = nicIpInfo[i].target_nic_name;
                let standbyGateway = replaceStandbyNetcard(nicName);
                if(standbyGateway!=""){
                    nicIpInfo[i].target_gateway = standbyGateway;
                }
            }
        }
        return takeoverIpMap;
    }

    /**
     * 因后台调整消息结构困难，这里web将备机配置的网卡对应网关赋值给主机映射的备机网关
     */
    let replaceStandbyNetcard = function(hostCardName){
        let gateway = "";
        for(let i = 0;i<_standbyGatewayConf.length;i++){
            if(_standbyGatewayConf[i]["netcard"] == hostCardName){
                gateway = _standbyGatewayConf[i]["gateway"];
                break;
            }
        }
        return gateway;
    }

    /* -------------------STEP3-------------------*/
    let step3Valid = () => {
        getNetworkUuid();
        if (!getScanStrategy()) {
            return false;
        }
        preStep4();
        step4Show();
        return true;
    }

    /**
     * 初始化网络传输（网络模式为2时）
     */
    let getNetworkUuid = () => {
        _createTaskMsgdata.transport_strategy.network_uuid = '';
        if(networkFlag){
            _createTaskMsgdata.transport_strategy.network_uuid = $('#transferNetworkTree').transferNetwork('getSelect').network_uuid;
        }
    }

    /**
     * 安全策略(病毒扫描)
     */
    let getScanStrategy = () => {
        if(type == 5){
            let wormConfig = $('#wormConfig').getTakeAppOver();
            if (wormConfig === false) {  // 验证病毒扫描配置内容是否符合要求
                return false;
            }
            let virusDetectionInfo = safeData('',wormConfig,'');
            _createTaskMsgdata.safe_config_strategy = virusDetectionInfo;
            // 安全策略病毒描述
            virusDetectionStr = wormConfig.str
        }
        return true;
    }

    /**
     * @function 得到开关的结果描述
     * @return 开启/关闭
     */
    var getSwitchDes = function(check){
        if(check==CONF.FLAG.SET){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    let preStep4 = () => {
        getTaskName();
    }

    let step4Show = () => {
        // 接管数据源客户端
        $('#takeoverDataSourceAgentDes').html(_createTaskMsgdata.name);
        // 接管时间
        let selecttimepoint = $('.selecttimepoint').val();
        $('#takeoverTimeDes').html(selecttimepoint);
        // 目标类型
        $('#targetTypeDes').html(_selectTargetType? LANG.UI_VM_MACHINE_MANAGER : LANG.UI_BACKUP_DATA_MODULE_OS);
        // 目标主机
        $('#takeoverTargetHostDes').html(takeoverTargetName);
        // 接管网络
        if (!_selectTargetType){
            $('.takeoverNetwork_div').show();
            $('.standbymapConfig_div').hide();
            let takeover_business_ip_map = _createTaskMsgdata.takeover_object.takeover_business_ip_map;
            let des = '';
            des += takeover_business_ip_map.length
                ? takeover_business_ip_map.map(item => item.conf_des).join('')
                : LANG.UI_CM_NOT_CONFIGURED;
            $('#takeoverNetworkDes').html(des);
            $('.targetpointConfig_div').show();
        }else{
            $('.standbymapConfig_div').show();
            $('.takeoverNetwork_div').hide();
            $('#standbymapDes').html(vmTempText);
            $('.targetpointConfig_div').hide();
        }
        // 目标主机配置
        $('#takeoverDiskDes').html(LANG.UI_CM_CDP_BACKUP_MONITOR_DISK + ':' + takeoverTargetHostDes);
        // 应用配置
        let appTypeName = LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE;
        if(app_info.length>0){
            appTypeName = app_info[0].app_name;
        }
        let vol_mount_point_set = _createTaskMsgdata.takeover_object.vol_mount_point_set;
        let targetVolStr = '';
        for(let i =0;i<vol_mount_point_set.length;i++){
            let sourceDevName = vol_mount_point_set[i].source_name;
            let targetDevName = vol_mount_point_set[i].destination_name;
            var desTitle =  sourceDevName + "-->" + targetDevName;
            targetVolStr += "<span title = '"+desTitle+"' >" + desTitle +"</span><br>";
        }
        // 目标卷
        $('#targetVolDes').html(targetVolStr);
        // 应用配置
        $('#appDes').html(appTypeName);
        // 脚本配置
        $('#scriptDes').html(LANG.UI_CM_VOL_CDP_TAKEOVER_POST_TAKEOVER_SCRIPT + ':' + afterScriptContent);
        // 传输网络
        let des = '';
        if(networkFlag){
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
            des += networkNode.str + '<br>';
        }
        $('.modeDes').html($('#transport_mode').find('option:selected').text());
        $('.networkDes').html(des);
        // 安全策略
        $('.safeStrategyDes').html(virusDetectionStr);
    }



    /**
     * 获取任务名
     */
    let getTaskName = () => {
        let info = {};
        info.task_type = CONF.MODULE_TYPE.VOL_CDP;
        info.task_name = LANG.UI_VOL_CDP_TAKEOVER_TASK;

        pAjaxRequest(info, "/api/v1/complete_machine_volcdp/default_task_name", "GET", function (result) {
            if (result.success) {
                $('.cmcdptaskname').val(result.data['task_name']);
            }
        }, false);
    }

    let submit = () => {
        if ($.trim($(".cmcdptaskname").val()) == '') {
            $('.jobnametip').html(LANG.UI_CM_CDP_TAKEOVER_TASK_NAME_TIPS).show();
            return;
        }
        $('.jobnametip').hide();
        _createTaskMsgdata.task_name = $('.cmcdptaskname').val();
        let jobName = $.trim($(".cmcdptaskname").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
        Metronic.blockUI({target: '#completeMachineVolcdptakeovercontent', animate: true, cenrerY: true,});
        pAjaxRequest(_createTaskMsgdata, '/api/v1/complete_machine_volcdp/create_takeover_job', 'POST', (d)=>{
            Metronic.unblockUI('#completeMachineVolcdptakeovercontent');
            if (operateResponseList(d)) {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
        })
    }

    // 判断授权个数是否充足
    var checkAuth = function () {
        getModuleAuthInfo({module: 'cdp'}).then(function (flag){
            isLicense = flag;
        });
    }

    return {
        init:()=>{
            wizardInit();
            checkAuth();
            initStorage();
            getTargetNetworkInfo();
            initAgentTree();
            addListeners();
        },
    }
})();
jQuery(document).ready(()=>{
    cmVolcdpTakeOver.init();
})