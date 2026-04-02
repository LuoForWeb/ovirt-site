var cmVolcdpRecovery = function (){
    // STEP1
    let nodeParamList;
    let zTreeAgent;
    let backupSetConfigInfo;
    let sourceNode = [];                     // 选择的源节点
    let networkFlag = false;
    let os_type = '';
    // STEP2
    let _recoveryTargetUuid = '';           // 恢复目标机的uuid
    let _selectTargetType = true;          // 选择目标类型  true:整机恢复；false：卷恢复
    let _selectTargetProxy = true;         // 代理
    let driverCheckRes = {};                // 驱动结果
    let _createTaskMsgdata = {
        // ***** 公共配置部分 ***** //
        'task_name' : '',
        'module_type' : '',
        'task_type' : '',
        'transport_strategy': {
            'speed_limit_flag':0,
            'max_speed':0,
            'network_uuid':'',
            'encrypt_flag':false,
            'encrypt_method':'',
            'compress_flag':false,
            'compress_method':'',
            'block_size':'',
        },
        'speed_limit_strategy': '',
        'strategy_group_uuid': '',
        'storage_uuid': '',
        'node_uuid': '',
        'backup_server_ip' : '',
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
        'script_list' : {
            'after_restore_script_location' : []
        },

        // ***** 任务配置 ***** //
        'dev_type' : '2',
        'master_agent_uuid' : '',
        'restore_mode' : '1',
        'restore_data_source' : '1',
        'recovery_target_agent_uuid' : '',
        'rebuild_partition_flag' : '1',
        'thread_num' : '1',
        'recovery_object':{
            'relation_set' : [],
            'restore_ip_change_flag' : '',
            'restore_business_ip_map' : [],
            'host_reset_name':'',
            'driver_consistent_flag':2,
            'cross_platform_flag':2,
            'cross_platform_conf':'',
            'recovery_timepoint_uuid':'',
            'recovery_datetime':'',
            'excluding_devices':[],
        },
        'recovery_position' : 0,
        'typeInfo':{high:{trasfer:{}}},
        'timepoint_uuid':'',
    }
    let task_uuid = '';
    let agent_uuid = '';
    let timepoint_uuid = '';
    let node_uuid = '';
    let storage_uuid = '';
    let validateFlag = true;

    let targetVisibleFlag = true;
    // 主机网卡信息
    let source_netcard_conf = [];
    // 目标机网卡信息
    let target_netcard_conf = [];
    // 恢复目标主机配置
    let recoverTargetHostDes = '';
    // 脚本
    let beforeScriptContent = '';
    let afterScriptContent = '';
    // 网络列表
    let networkList;
    // 安全策略描述
    // let virusDetectionStr;
    // 启动时间描述
    let startTimeDes = LANG.UI_BACKUP_MANUAL;
    // 备份集id
    let backup_set_id = '';
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
            jQuery('li', $('#completeMachineVolcdprecoverycontent')).removeClass("done");
            let li_list = navigation.find('li');
            for (let i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#completeMachineVolcdprecoverycontent').find('.button-previous').css('visibility', 'hidden');
            } else {
                $('#completeMachineVolcdprecoverycontent').find('.button-previous').css('visibility', 'visible');
            }

            if (current >= total) {
                $('#completeMachineVolcdprecoverycontent').find('.button-next').hide();
                $('#completeMachineVolcdprecoverycontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#completeMachineVolcdprecoverycontent').find('.button-next').show();
                $('#completeMachineVolcdprecoverycontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#completeMachineVolcdprecoverycontent').bootstrapWizard({
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

        $('#completeMachineVolcdprecoverycontent').find('.button-previous').css('visibility', 'hidden');
        $('#completeMachineVolcdprecoverycontent .button-submit').click(submit).css('visibility', 'hidden');
    };

    /**
     * 监听事件
     */
    let addListeners = () => {
        // caption的描述
        let titleDes = '';
        let groupDes = '';
        let currentDes = '';
        let authedCmCdpFlag = CONF.PERMISSION.includes('complete_cdp_backup');
        let authVolCdpFlag = CONF.PERMISSION.includes('vol_cdp_backup');
        let authedDbCdpFlag = CONF.PERMISSION.includes('dbcdpbackup');
        if (authedCmCdpFlag || authVolCdpFlag) { // 授权整机或卷实时其中一个，描述加上  实时保护
            groupDes = LANG.UI_REAL_TIME_PROTECT_RECOVERY;
            currentDes = LANG.UI_REAL_TIME_PROTECT_RECOVERY;
            if (authedDbCdpFlag) {
                groupDes = LANG.UI_CONTINUOUS_DATA_PROTECT_REPLICATION_DATA_RECOVERY;
                currentDes = LANG.UI_CONTINUOUS_DATA_PROTECT_REPLICATION_DATA_RECOVERY;
            }
        } else { // 整机和卷实时都没有授权
            if (authedDbCdpFlag) {
                groupDes = LANG.UI_REPLICATION_DISASTER_RECOVERY;
                currentDes = LANG.UI_REPLICATION_DISASTER_RECOVERY;
            }
        }
        titleDes = LANG.UI_JOB_NEW_TASK + groupDes + LANG.UI_DATACENTER_JOB;
        $('.caption_des').html(titleDes);
        $('.current_des').html(currentDes);

        // 搜索
        $('#searchAgent').on('propertychange', searchMachine).on('input', searchMachine);

        // 按照存储节点筛选
        $('#storageselect').on('change', storageselectChange);

        // 重置主机名
        $('#rename').on('switchChange.bootstrapSwitch',rename);

        //
        $('input[name=rehostname]').on('blur',blurHostName);

        // 跳转复制页面
        $('#tobackup').on('click', function () {
            LOCATION('./content/complete_machine_volcdp/cm_volcdp_backup.php', 'complete_cdp_backup');
        });

        // 切换恢复目标主机
        $('#recoverTargetHost').on('change',changeRecoverTargetHost);

        // 切换目标类型
        $('#radio_group_1').on('change',changeRadio);

        // 传输数据压缩
        $('#tran_compress_switch').on('switchChange.bootstrapSwitch',switchCompress);

        //选择启动时间：手动启动/指定时间启动
        $('#start_time_type').on('change',function(){
            initTimeStrategyDes();
            if(this.value == '1'){
                _createTaskMsgdata.typeInfo.strategy = {};
                $('.setOnceTime').hide();
            }else if(this.value == '4'){
                $('.setOnceTime').show();
            }
        });

        //清空时间选择
        $('#resetdate').on('click',function(){
            $('#oncetime').val('');
        });

        //修改定时恢复时间
        $('#oncetime').on('change', function(){
            initTimeStrategyDes();
        });
    }

    var initTimeStrategyDes = function(){
        let des = "";
        //备份
        let start_time_type = $('#start_time_type').val();
        if(start_time_type == '1'){
            des += LANG.UI_BACKUP_MANUAL;
        }else if(start_time_type == '4'){
            des +=  LANG.UI_GLOBAL_STRATEGY_TYPE_START_AT_TIME + ': ' + $('#oncetime').val();
        }
        startTimeDes = des;
        $('.recoveryTimeDes').html(des);
        $('.recoveryTimeDes').prop('title', des);
    }

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
            $('.recoveryAgentTree').hide();
            $('#nosearchtips').show();
        }else{
            $('.recoveryAgentTree').show();
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
        $('.recoveryAgentTree').show();
        initAgentTree();
    };

    /**
     * 重置主机名
     */
    let rename = function (event,state){
        $('input[name=rehostname]').val('');
        if(state){
            $('.reset_hostname_div').show();
        }else{
            $('.reset_hostname_div').hide();
            validateFlag = true;
        }
    }

    let blurHostName = function (){
        let reset_hostname = $('input[name=rehostname]').val();
        validateFlag = validateHostName('string',reset_hostname,os_type);
    }

    /**
     * 切换恢复目标主机
     */
    let changeRecoverTargetHost = ()=>{
        // 驱动检测
        // 选中的目标恢复机
        _recoveryTargetUuid = $('#recoverTargetHost option:selected').val();
        _createTaskMsgdata.recovery_target_agent_uuid = _recoveryTargetUuid;
        if(_recoveryTargetUuid != ''){
            if(_selectTargetType){
                // 只有整机恢复才有驱动检测
                $('.driverCheckConf').show();
                getDriver(_createTaskMsgdata.timepoint_uuid,_recoveryTargetUuid);
                // 获取目标机网卡信息
                loadStandbyNetworkInfo();
                // 网络配置
                getNetworkList();
                loadAgentNetworkInfo();
                initNetWorkConfig();
            }

            $('.notargetHostTips').hide();
            $('.hostConf').show();
            let targetHostName = $('#recoverTargetHost option:selected').text();
            $('.targetHostName').html(targetHostName);
            // 恢复目标主机配置 组件
            initTargetConfig();
        }else{
            $('.notargetHostTips').show();
            $('.driverCheckConf').hide();
            $('.hostConf').hide();
        }
    }

    /**
     * 切换目标类型
     */
    let changeRadio = (e,oldValue, newValue) => {
        $('.hostConf').hide();
        $('.notargetHostTips').show();
        // 恢复目标主机配置
        $('#config_ul li').removeClass('active').eq(0).addClass('active');
        $('.configContent').children('div').removeClass('active').eq(0).addClass('active');
        if(newValue == 'completeMachineRecovery'){
            // 整机
            _recoveryTargetUuid = '';
            _selectTargetType = true;
            _selectTargetProxy = true;
            getCompleteMachineRecovery();
            targetVisibleFlag = true;
            $('.driverCheckConf').hide();
            _createTaskMsgdata.restore_mode = '1';

            // 获取网络列表
            getNetworkList();
            // 源机网卡信息
            loadAgentNetworkInfo();
            document.getElementById('ipConfig').classList.remove('display-none-force');
            document.getElementById('net_config_li').classList.remove('display-none-force');
            document.getElementById('system_config_li').classList.remove('display-none-force');
        }else if(newValue == 'dataVolumeRecovery'){
            // 卷
            _selectTargetType = false;
            _selectTargetProxy = false;
            getDataVolumeRecovery();
            $('.driverCheckConf').hide();
            targetVisibleFlag = false;
            _createTaskMsgdata.restore_mode = '2';
            document.getElementById('ipConfig').classList.add('display-none-force');
            document.getElementById('net_config_li').classList.add('display-none-force');
            document.getElementById('system_config_li').classList.add('display-none-force');
        }
    }

    /**
     * 传输数据压缩
     */
    let switchCompress = function(){
        if(this.checked){
            $('.transferCompressGradeDiv').show();
        }else{
            $('.transferCompressGradeDiv').hide();
        }
    }

    /**
     * 显示网络配置(整机恢复显示 卷恢复不显示)
     */


    /**
     * 初始化各个数字输入框
     */
    let initSpinner = () => {
        $('#recoveryThreadDiv').spinner({value:1, step: 1, min: 1, max: 4});
    }

    var inintDatatimePicker = function () {
        if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
            //英文独有的
            $(".form_datetime").datetimepicker({
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-mm-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        } else {
            $(".form_datetime").datetimepicker({
                language: 'zh-CN',
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-MM-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }
    }

    /**
     * step2的有效验证
     * @return {boolean}
     */
    let getStep2Msg = () =>{
        // STEP2检测
        if(_recoveryTargetUuid == ''){
            UIToastr.showWarning(LANG.UI_CM_VOL_CDP_RECOVERY, LANG.UI_CM_VOL_CDP_RECOVERY_PLEASE_SELECT_THE_RECOVERY_STANDBY_MACHINE);
            return false;
        }
        // 整机必须选择驱动
        if (_selectTargetType && _recoveryTargetUuid != ''){
            driverCheckRes = $.fn.driverCheck.getCheckResult($('#driverCheck'));
            if (!driverCheckRes) {
                UIToastr.showWarning(LANG.UI_CM_CDP_BACKUP_CREATE_TIPS, LANG.UI_VERIFY_DRIVER_CHECK_TIPS);
                return false;
            }else{
                $.each(driverCheckRes, function(i,driver){
                    if(driverCheckRes[i].driver_check_status == 8){
                        // 操作系统版本不支持添加驱动的情况
                        _createTaskMsgdata.recovery_object.cross_platform_flag = false;
                        _createTaskMsgdata.recovery_object.cross_platform_conf = '';
                    }else{
                        _createTaskMsgdata.recovery_object.cross_platform_flag = driverCheckRes[i].diff_platform_type;
                        _createTaskMsgdata.recovery_object.cross_platform_conf = driverCheckRes[i].driver_hw_id_map;
                    }
                });
            }
            networkConfigGetData();
        }
        if(!validateFlag){
            return false;
        }
        if (!CONF.FUNCTIONS.includes('multithread')) {
            $('.threadDiv').hide();
        }
        // 获取脚本配置
        getScriptConfig();
        // 获取恢复目标卷
        getRecoverTargetVol();
        // 获取系统配置
        getSystemConfig();
        // 初始化传输网络
        initNetworkList();
        // 初始化病毒检测
        initVirusCheck();
        UIToastr.showWarning(LANG.UI_CM_VOL_CDP_RECOVERY, LANG.UI_CM_CDP_RECOVERY_TASK_SELECT_TARGET_DEV_TIPS);
        return true;
    }


    let step2Valid = ()=> {
        return getStep2Msg();
    }

    /**
     * 获取恢复目标卷
     */
    let getRecoverTargetVol = () => {
        recoverTargetHostDes = '';
        let changeData = $('#volinfo').getTargetData('getEachData',timepoint_uuid);
        _createTaskMsgdata.recovery_object.relation_set = [];
        for(let i=0;i<changeData.target_strategy.length;i++){
            let relation_set_list = {};
            relation_set_list.dev_uuid = changeData.target_strategy[i].source_dev_uuid;
            relation_set_list.target_dev_uuid = changeData.target_strategy[i].destination_dev_uuid;
            relation_set_list.backup_time = $('.selecttimepoint').val();
            _createTaskMsgdata.recovery_object.relation_set.push(relation_set_list);
        }
        for(let i=0;i<changeData.disk_list.length;i++){
            recoverTargetHostDes += changeData.disk_list[i]['dev_name'] + ',';
        }
        _createTaskMsgdata.recovery_object.excluding_devices = changeData.exclude_dev;
    }

    /**
     * 初始化病毒检测
     */
    let initVirusCheck = () => {
        // 定义病毒检测组件
        let isNoScan = false;
        let isHealthy = false;
        let isInfect = false;
        switch (backupSetConfigInfo.virus_scan_status) {
            case 0:
            case 1:
                isNoScan = true;
                break;
            case 2:
                isHealthy = true;
                break;
            case 3:
                isInfect = true;
                break;
        }
        $('#wormConfig').virusDetectionCover(isNoScan,isHealthy,isInfect);
    };

    /**
     * 获取脚本配置
     */
    let getScriptConfig = () => {
        afterScriptContent = LANG.UI_CM_NOT_CONFIGURED;
        let cmAfterRecoveryScript = $('#recoveryScript').getVinScript('recoveryScript');
        _createTaskMsgdata.script_list.after_restore_script_location = cmAfterRecoveryScript;
        // 恢复后
        if(cmAfterRecoveryScript.length >0){
            afterScriptContent = '';
            for(const script of cmAfterRecoveryScript){
                afterScriptContent += `${script.script_content},`;
            }
        }
    }

    /**
     * 获取系统配置
     */
    let getSystemConfig = () =>{
        // 重置主机名
        _createTaskMsgdata.recovery_object.host_reset_name = $('input[name=rehostname]').val();
    }

    /* -------------------STEP1-------------------*/

    /**
     * 初始化节点列表
     */
    let initPointShowType = () => {
        let requestData = {
            module_type : CONF.MODULE_TYPE.VOL_CDP
        }
        pAjaxRequest({},'/api/v1/nodes/select','GET',(d)=>{
            let data = d.data;
            let nodeselect = $('#nodeselect');
            nodeselect.empty();
            for(let i=0; i<data.length; i++){
                let option = $("<option>").text(data[i].text).val(data[i].uuid);
                nodeselect.append(option);
            }
        },false);
        //绑定事件
        $('#nodeselect').on('change', nodeselectChange);
    }

    /**
     * 切换节点列表触发事件
     */
    let nodeselectChange = () => {
        initAgentTree();
    }

    /**
     * 获取恢复源树结构
     */
    let initAgentTree = ()=>{
        let storage_uuid = $('#storageselect').find('option:selected').val();
        let requestData = {
            storage_uuid:storage_uuid,
        };
        Metronic.blockUI({target:'#recovery_agent_tree',animate: true});
        pAjaxRequest(requestData,'/api/v1/complete_machine_volcdp/data/agent/tree','GET',(d)=>{
            let backup_set_task_uuid = $('#task_uuid').val();
            backup_set_id = $('#backup_set_id').val();
            $("#recovery_agent_tree").show();
            $("#nopointtips").hide();
            Metronic.unblockUI('#recovery_agent_tree');
            let zNodes = d.data;
            if(zNodes.length == 0){
                $("#recovery_agent_tree").hide();
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
                    nameIsHTML:true
                }
            };
            zTreeAgent = $.fn.zTree.init($("#recovery_agent_tree"), setting, zNodes);
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
    let nodeCheck = (e, id, node) => {
        if(node.chkDisabled){
            return;
        }
        if(node.type == 'data_center' || node.type == 'task' || node.type == 'vol'){
            return;
        }
        sourceNode = node;
        agent_uuid = node.agent_uuid;                       // 选中客户端的uuid
        task_uuid = node.task_uuid;                         // 选中客户端相关联的task_uuid
        let agent_name = node.name;                         // 选中客户端名
        node_uuid = node.node_uuid;                         // 节点uuid
        storage_uuid = node.storage_uuid;                   // 存储uuid
        os_type = node.os_type;                             // 操作系统
        // 消息结构
        _createTaskMsgdata.master_agent_uuid = agent_uuid;
        _createTaskMsgdata.name = agent_name;
        _createTaskMsgdata.task_uuid = task_uuid;
        _createTaskMsgdata.os_type = node.os_type;
        _createTaskMsgdata.node_uuid = node.node_uuid;
        _createTaskMsgdata.storage_uuid = storage_uuid;
        // 客户端连接服务端
        if(node.net_model == 2){
            networkFlag = true;
        }

        let params = {
            'task_uuid': task_uuid,
            'agent_uuid': agent_uuid,
            'create_task_type': CONF.TASK_TYPE.VOL_CDP_RECOVERY,
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
     * 初始化传输网络
     */
    let initNetworkList = () => {
        let nodeUuid = _createTaskMsgdata.node_uuid;
        if(networkFlag && nodeUuid!=""){   //初始化传输网络
            $('.transfernetworkview').show();
            $('#transferNetworkTree').transferNetwork({node_uuid: nodeUuid}); 	//初始化节点传输网络列表
        }else{
            $('.transfernetworkview').hide();
        }
    }

    /**
     * 获取恢复目标主机(数据卷恢复)
     */
    let getDataVolumeRecovery = () => {
        let requestData = {
            master_os_type:_createTaskMsgdata.os_type,
            standby_uuid:_createTaskMsgdata.master_agent_uuid
        }
        Metronic.blockUI({target: '#completeMachineVolcdprecoverycontent',animate: true, cenrerY: true,});
        pAjaxRequest(requestData,'/api/v1/complete_machine_volcdp/recovery/vol/target_host','GET',(d) => {
            Metronic.unblockUI('#completeMachineVolcdprecoverycontent');
            let data = d.data;
            let ele = $('#recoverTargetHost');
            ele.empty();
            data.forEach((item) => {
                ele.append($("<option>").text(item.text).val(item.uuid).attr("agent_os_type",item.os_type));
            })
        });
    }


    /**
     * 获取恢复目标主机配置
     */
    let initTargetConfig = () => {
        Metronic.unblockUI('#completeMachineVolcdprecoverycontent');
        let targetOsType = $("#recoverTargetHost").find("option:selected").attr('agent_os_type'); //目标设备系统类型
        $('#volinfo').initTargetPlug({
            type: 6, // 6为实时恢复
            origin_task_uuid: task_uuid,            //源任务uuid(实时,定时可不传或为空)  node.task_uuid
            origin_os_type: os_type,
            target_os_type:targetOsType,            //目标机操作系统类型 windows还是linux，只有type为3和6在使用,其他type可不传
            origin_agent_uuid: agent_uuid,          //源代理主机uuid(实时,定时可不传或为空)  node.agent_uuid
            origin_timepoint_uuid: timepoint_uuid,  //源时间点uuid或时间点(实时,定时)  node.timepoint_uuid
            target_agent_uuid: _createTaskMsgdata.recovery_target_agent_uuid,  //目标代理主机uuid(目标端主机)  目标机
            target_visible_flag: true,              //控制恢复目标的列是否显示 true显示,false不显示
            recover_type: targetVisibleFlag,        //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
            target_auto_flag: false,                //是否显示恢复目标选择框的自动分配选项
            target_input_flag:false,                //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
            target_input_min: -1,                   //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
            origin_data_array:[],                   //前端源信息,当origin_data_flag为true时使用这个数据
            nocheck: false,                         //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
            forced_relationship: true,              //是否强制关联勾选.默认为强制关联勾选
            first_column_name:LANG.UI_CM_CDP_SOURCE_DISK_NAME,
            second_column_name:LANG.UI_PUBLIC_TOTAL_SIZE,
            third_column_name:LANG.UI_RECOVERY_GOAL,
            loading:'#targetHostConf',
        });
        Metronic.unblockUI('#completeMachineVolcdprecoverycontent');
    }

    /**
     * 脚本初始化
     * @return {boolean}
     */
    let initScript = () => {
        $('.recoveryScript').initVinScript({class: 'recoveryScript',include_list:[1,2]});
    }

    /**
     * 获取主机网卡信息
     */
    let loadAgentNetworkInfo =  () => {
        let data = {};
        data.agent_uuid = _createTaskMsgdata.master_agent_uuid;
        data.task_uuid = task_uuid;
        pAjaxRequest(data,'/api/v1/complete_machine_volcdp/agent/network_info','GET',(d)=>{
            source_netcard_conf = d.data;
        },false)
    }


    /**
     * 获取备机网卡信息
     */
    let loadStandbyNetworkInfo = () => {
        let data = {'agent_uuid': _recoveryTargetUuid};
        Metronic.blockUI({target: '#completeMachineVolcdprecoverycontent',animate: true, cenrerY: true,});
        pAjaxRequest(data,'/api/v1/complete_machine_volcdp/standby/network_info','GET',(d)=>{
            Metronic.unblockUI('#completeMachineVolcdprecoverycontent');
            target_netcard_conf = d.data;
            target_netcard_conf = target_netcard_conf.map(row => {
                return {
                    ...row,
                    value: row.mac_address,
                }
            })
        },false);
    }

    /**
     * 初始化网络配置
     */
    let initNetWorkConfig = () => {
        // 初始化网络配置插件结构体
        let networkInit = [];
        //遍历nic_list数组
        for (let i = 0; i < source_netcard_conf.length; i++) {
            let eachNetwork = {};

            eachNetwork.checked = true;
            eachNetwork.chk_disabled = false;
            eachNetwork.net_uuid = getUuid();
            eachNetwork.show_config_type_flag = true;
            eachNetwork.net_name = source_netcard_conf[i]['name'];
            eachNetwork.net_mac = source_netcard_conf[i]['mac_address'];
            eachNetwork.mac_address = source_netcard_conf[i]['mac_address'];
            eachNetwork.show_advance_flag = true;
            eachNetwork.change_net_flag = false;
            eachNetwork.network_list = target_netcard_conf;
            eachNetwork.show_ip_switch_nav_tabs_flag = false;
            eachNetwork.ipv4_set = {
                config_type: $.fn.NetworkConfig.CONFIG_TYPE_ENUM.auto,
                ip_set: [],
                dns1: '',
                dns2: ''
            };
            eachNetwork.ipv6_set = {
                config_type: $.fn.NetworkConfig.CONFIG_TYPE_ENUM.auto,
                ip_set: [],
                dns1: '',
                dns2: ''
            };
            //获取ip类型
            let ip_set = source_netcard_conf[i]['ip_set'];
            for (let j = 0; j < ip_set.length; j++) {
                let eachIp_set = {};
                eachIp_set.ip = ip_set[j].ip_addr;
                eachIp_set.netmask = ip_set[j].netmask;
                if(ip_set[j].ip_type == 2){
                    // ipv6
                    eachNetwork.ipv6_set.gateway = source_netcard_conf[i]['ipv6_gateway_address'] != undefined ? source_netcard_conf[i]['ipv6_gateway_address'] : '';
                    eachNetwork.ipv6_set.ip_set.push(eachIp_set);
                } else {
                    eachNetwork.ipv4_set.dns1 = source_netcard_conf[i]['dns'];
                    eachNetwork.ipv4_set.dns2 = source_netcard_conf[i]['dns2'];
                    eachNetwork.ipv4_set.gateway = source_netcard_conf[i]['gateway_address'];
                    eachNetwork.ipv4_set.ip_set.push(eachIp_set);
                }
            }

            networkInit.push(eachNetwork);
        }

        $.fn.NetworkConfig.init($('#ipConfig'), {
            custom_vm_net_list: networkInit,
            show_advance_flag: true,  // 是否显示高级
        }, $.fn.NetworkConfig.USAGE_TYPE_ENUM.CUSTOM_VM);
    }

    /**
     * @function 生成uuid
     */
    let getUuid = () => {
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
     * 获取网络列表
     */
    let getNetworkList = () => {
        pAjaxRequest({'offset':0,'limit':10},'/api/v1/virtual/network','GET',(res)=>{
            let rows = res.data.rows;
            networkList = rows;
        },false)
    }

    /**
     * 获取网络配置数据
     */
    let networkConfigGetData = () => {
        let networkData =  $.fn.NetworkConfig.getData($('#ipConfig'));
        _createTaskMsgdata.recovery_object.restore_business_ip_map = [];
        if(!networkData){
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_CM_VOL_CDP_RECOVERY_CHECK_NETWORK_CONFIG);
            return false;
        }
        _createTaskMsgdata.recovery_object.restore_ip_change_flag = networkData[0].change_net_flag;
        // 如果打开了修改网络配置

        for(let i=0;i<networkData.length;i++){
            if(networkData[i].checked){
                // 选中网卡
                let interfacesList = {};
                interfacesList.uuid = getUuid();  // 随机生成uuid
                interfacesList.model_type = "";
                interfacesList.source_nic_name = networkData[i].net_name;   // 源虚拟机网卡名称
                interfacesList.source_nic_mac = networkData[i].net_mac;     // 源虚拟机网卡地址
                interfacesList.name = networkData[i].network_name;          // 目标
                interfacesList.mac_address = networkData[i].network;        // 目标网卡地址
                interfacesList.DNS1 = networkData[i].ipv4_set.dns1;
                interfacesList.DNS2 = networkData[i].ipv4_set.dns2;
                interfacesList.gateway_address = networkData[i].ipv4_set.gateway;    // 目标网关地址
                if(networkData[i].change_net_flag && networkData[i].ipv4_set.config_type == CONF.FLAG.UNSET){
                    // ipv4
                    interfacesList.DNS1 = networkData[i].ipv4_set.dns1;
                    interfacesList.DNS2 = networkData[i].ipv4_set.dns2;
                    interfacesList.gateway_address = networkData[i].ipv4_set.gateway;    // 目标网关地址
                }
                interfacesList.ipv6_DNS1 = '';
                interfacesList.ipv6_DNS2 = '';
                interfacesList.ipv6_gateway_address = networkData[i].ipv6_set.gateway;    // 目标网关地址
                if(networkData[i].change_net_flag && networkData[i].ipv6_set.config_type == CONF.FLAG.UNSET){
                    // ipv6
                    interfacesList.ipv6_DNS1 = networkData[i].ipv6_set.dns1;
                    interfacesList.ipv6_DNS2 = networkData[i].ipv6_set.dns2;
                    interfacesList.ipv6_gateway_address = networkData[i].ipv6_set.gateway;    // 目标网关地址
                }
                interfacesList.ip_set = [];
                // ipv4
                for(let j=0;j<networkData[i].ipv4_set.ip_set.length;j++){
                    interfacesList.ip_set.push(
                        {
                            "ip_addr":networkData[i].ipv4_set.ip_set[j].ip,
                            "ip_type":1,
                            "netmask":networkData[i].ipv4_set.ip_set[j].netmask
                        }
                    )
                }

                // ipv6
                for(let j=0;j<networkData[i].ipv6_set.ip_set.length;j++){
                    interfacesList.ip_set.push(
                        {
                            "ip_addr":networkData[i].ipv6_set.ip_set[j].ip,
                            "ip_type":2,
                            "netmask":networkData[i].ipv6_set.ip_set[j].netmask
                        }
                    )
                }
                _createTaskMsgdata.recovery_object.restore_business_ip_map.push(interfacesList);
            }
        }
    }


    /**
     * test 第一步
     */
    let step1Valid = ()=> {
        return getStep1Msg();
    }

    let getStep1Msg = () => {
        // STEP1校验
        // 勾选数据源
        if(!sourceNode.checked || sourceNode.length == 0){
            UIToastr.showWarning(LANG.UI_CM_VOL_CDP_RECOVERY, LANG.UI_CM_VOL_CDP_RECOVERY_PLEASE_SELECT_THE_RECOVERY_DATA_SOURCE_FIRST);
            return false;
        }
        // 校验恢复时间点
        backupSetConfigInfo = volCdpBackupSetTimeLineInfo.getBackupSetConfigInfo();
        timepoint_uuid = backupSetConfigInfo.time_point_uuid;
        _createTaskMsgdata.timepoint_uuid = timepoint_uuid;
        _createTaskMsgdata.recovery_object.recovery_timepoint_uuid = timepoint_uuid;
        if(!backupSetConfigInfo.time_point_is_valid){
            UIToastr.showWarning(LANG.UI_CM_VOL_CDP_RECOVERY, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
            return false;
        }
        _createTaskMsgdata.recovery_object.recovery_datetime = $('.selecttimepoint').val();
        $('.notargetHostTips').show();
        $('.driverCheckConf').hide();
        $('.hostConf').hide();
        _recoveryTargetUuid = '';
        // 获取恢复目标主机(数据卷恢复)
        getCompleteMachineRecovery();
        // 脚本组件
        initScript();
    }


    /* -------------------STEP2-------------------*/

    /**
     * 获取整机恢复目标
     */
    let getCompleteMachineRecovery = () => {
        Metronic.blockUI({target: '#completeMachineVolcdprecoverycontent',animate: true, cenrerY: true,});
        pAjaxRequest({},'/api/v1/complete_machine_volcdp/recovery/host','GET',(d)=>{
            Metronic.unblockUI('#completeMachineVolcdprecoverycontent');
            let data = d.data;
            let ele = $('#recoverTargetHost');
            ele.empty();
            data.forEach((item) => {
                ele.append($("<option>").text(item.text).val(item.agent_uuid).attr("agent_os_type",item.agent_type));
            })
        },false);
    }

    /**
     * 获取驱动信息
     */
    let getDriver = (timepoint_uuid,agent_uuid) => {
        $.fn.driverCheck.init($('#driverCheck'), {
            getCheckObject:() => {
                return {
                    source_list:[{
                        name:_createTaskMsgdata.name,    // 客户端名
                        timepoint_uuid:timepoint_uuid,   // 时间点
                        module_type:CONF.MODULE_TYPE.VOL_CDP
                    }],
                    target_info:{
                        target_type:$.fn.driverCheck.DRIVER_TARGET_TYPE.CLIENT,
                        client_info:{
                            agent_uuid:agent_uuid,      // 目标机uuid
                        },
                        vm_info:{
                            vm_type: 1,
                            vm_uuid:'',
                            host_uuid:''
                        }
                    }
                }
            },
            show_check_btn:true,
            afterCheck: (result,data) =>{
            }
        });
    }

    /* -------------------STEP3-------------------*/


    /**
     * 安全策略(病毒扫描)
     */
    let getScanStrategy = () => {
        let wormConfig = $('#wormConfig').getVirusDetectionCover();
        if (wormConfig === false) {  // 验证病毒扫描配置内容是否符合要求
            return false;
        }
        let virusDetectionInfo = safeData('',wormConfig,'');
        // _createTaskMsgdata.safe_config_strategy = virusDetectionInfo;
        // 安全策略病毒描述
        // virusDetectionStr = $('#wormConfig').getVirusDetectionCover().str
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

    /**
     * 获取任务名
     */
    let getTaskName = () => {
        let info = {};
        info.task_type = CONF.MODULE_TYPE.VOL_CDP;
        info.task_name = LANG.UI_VOL_CDP_RECOVER_TASK;

        pAjaxRequest(info, "/api/v1/complete_machine_volcdp/default_task_name", "GET", function (result) {
            if (result.success) {
                $('.cmcdptaskname').val(result.data['task_name']);
            }
        }, false);
    }

    let getStep3Msg = () => {
        _createTaskMsgdata.typeInfo.type = parseInt($('#start_time_type option:selected').val());
        _createTaskMsgdata.typeInfo.strategy = {};
        _createTaskMsgdata.typeInfo.strategy.start_time = $('#oncetime').val();  // 指定时间启动
        _createTaskMsgdata.typeInfo.strategy.type = _createTaskMsgdata.typeInfo.type;
        if(_createTaskMsgdata.typeInfo.type == 4 && _createTaskMsgdata.typeInfo.strategy.start_time == ''){
            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
            return false;
        }
        // 限速策略
        _createTaskMsgdata.speed_limit_strategy = getSpeedStrategyInfo();
        // 获取传输网络
        if(networkFlag){
            _createTaskMsgdata.transport_strategy.network_uuid = $('#transferNetworkTree').transferNetwork('getSelect').network_uuid;
        }
        // 获取加密传输
        _createTaskMsgdata.transport_strategy.encrypt_flag = $("#encrypttransfer").is(':checked');
        if(_createTaskMsgdata.transport_strategy.encrypt_flag){
            _createTaskMsgdata.transport_strategy.encrypt_method = '1';  // 加密方式暂时写死为RSA ‘1’
        }
        // 获取压缩传输
        _createTaskMsgdata.transport_strategy.compress_flag = $("#tran_compress_switch").is(':checked');
        // 获取压缩等级
        if(_createTaskMsgdata.transport_strategy.compress_flag){
            _createTaskMsgdata.transport_strategy.compress_method = $("#transferCompressGrade option:selected").val();
        }
        // 获取传输包大小
        _createTaskMsgdata.transport_strategy.block_size = $("#transfer_datapackage_size option:selected").val() * 1024 * 1024;
        // 线程数
        _createTaskMsgdata.thread_num = $("#recoveryThreadNum").val();
        // 安全策略(病毒扫描)
        if (!getScanStrategy()) {
            return false;
        }
        getTaskName();
        step4Show();
        return true;
    }

    let step3Valid = () => {
        return getStep3Msg();
    }

    let step4Show = () => {
        // 恢复数据源客户端
        $('#recovery_data_source_agent').html(_createTaskMsgdata.name);
        // 恢复时间
        let timeponit = $('.selecttimepoint').val();
        $('#recovery_time').html(timeponit);
        // 目标类型
        $('#target_type').html(_selectTargetType ? LANG.UI_CM_VOL_CDP_RECOVERY : LANG.UI_CM_VOL_CDP_RECOVERY_DATA_VOLUME_RECOVERY);
        // 目标主机
        $('#recovery_target_host').html($('#recoverTargetHost option:selected').text());
        // 目标主机配置
        $('#restoreTargetHostDes').html(LANG.UI_CM_CDP_BACKUP_MONITOR_DISK + ':' + recoverTargetHostDes);
        // 脚本配置
        $('#scriptDes').html(LANG.UI_PUBLIC_AFTER_RECOVERY_SCRIPT_SHOW + ':' + afterScriptContent);
        // 系统配置
        let hostName = _createTaskMsgdata.recovery_object.host_reset_name != '' ? LANG.UI_VM_RESET_HOST_NAME +':' + _createTaskMsgdata.recovery_object.host_reset_name : LANG.UI_JOB_FILTER_SELECTNONE;
        $('#systemConfigDes').html(hostName);
        // 启动时间
        $('#start_time_des').html(startTimeDes);
        // 限速策略
        let speedlimitsStr = '';
        speedlimitsStr = $('.speedlimitDes').prop('title');
        if (speedlimitsStr == '') {
            speedlimitsStr = LANG.UI_PUBLIC_NOTHING;
        }
        $('#speed_limit_strategy').html(speedlimitsStr);
        // 传输网络
        var openstacktranslabel = $('.transferlabel').html();
        var transmodeStr = $('#transport_mode option:selected').text();
        var des = openstacktranslabel + ": " +  transmodeStr +"<br>";
        var compressFlag = _createTaskMsgdata.transport_strategy.compress_flag;
        var encryptFlag = _createTaskMsgdata.transport_strategy.encrypt_flag;
        if(networkFlag){
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect')
            des += LANG.UI_KUBE_TRANSFER_NETWORK + ": " + networkNode.str + "<br>";
        }
        des += LANG.UI_COPY_BACK_ENCRYPT + ": "+getSwitchDes(encryptFlag) + "<br>";
        des += LANG.UI_COPY_BACK_COMPRESS + ": " + getSwitchDes(compressFlag) + "<br>";
        if(compressFlag){
            let encryptemethod = parseInt($('#transferCompressGrade').val());
            let encryptegradestr = '';
            switch (encryptemethod) {
                case 1:
                    encryptegradestr = LANG.UI_JOB_COMPRESS_PRIORITY_FASTER;
                    break;
                case 2:
                    encryptegradestr = LANG.UI_JOB_COMPRESS_PRIORITY_NORMAL;
                    break;
                case 3:
                    encryptegradestr = LANG.UI_JOB_COMPRESS_PRIORITY_BETTER;
                    break;
                case 4:
                    encryptegradestr = LANG.UI_JOB_COMPRESS_PRIORITY_BEST;
                    break;
            };
            des += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + encryptegradestr + '<br>';
        }
        if(CONF.FUNCTIONS.includes('multithread')){
            des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM +": " + _createTaskMsgdata.thread_num + LANG.UI_PUBLIC_NUM+"<br>";
        }
        var transferDatapackageValue = $('#transfer_datapackage_size option:selected').text();
        des += LANG.UI_VOL_CDP_RECOVER_TRANS_PACKAGE_SIZE +": " + transferDatapackageValue + "<br>"
        $('.transportinfoshow').html(des);
    }

    /* -------------------提交任务-------------------*/
    let submit = () => {
        if ($.trim($(".cmcdptaskname").val()) == '') {
            $('.jobnametip').html(LANG.UI_RECOVERY_RENAME).show();
            return;
        }
        $('.jobnametip').hide();
        _createTaskMsgdata.task_name = $('.cmcdptaskname').val();
        let jobName = $.trim($(".cmcdptaskname").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
        Metronic.blockUI({target: '#completeMachineVolcdprecoverycontent', animate: true, cenrerY: true,});
        pAjaxRequest(_createTaskMsgdata, '/api/v1/complete_machine_volcdp/create_recover_job', 'POST', (d)=>{
            Metronic.unblockUI('#completeMachineVolcdprecoverycontent');
            if (operateResponseList(d)) {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
        })
    }


    return {
        init:()=>{
            wizardInit();
            initStorage();
            initPointShowType();
            addListeners();
            initAgentTree();
            initSpinner();
            inintDatatimePicker();
        }
    }
}();

jQuery(document).ready(()=>{
    cmVolcdpRecovery.init();
})