var  completeMachineVolcdp = function (){
    // 变量定义
    let _existTtaskName = '';          // 选中客户端的标题  （备份源主机）
    let _optionType = '';              // 所选备份源主机的操作系统
    let _inTask = false;             // 是否创建任务
    let _isHaStandby = false;        // 是否作为接管备机
    let _isRecoveryTarget = '';        // 是否作为双机镜像备机使用
    let _isFbTarget = '';              // 是否作为回切目标机器使用
    let _isTakeoverStandby = '';       // 是否作为恢复目标机器被使用
    let _agentuuid = '';               // 选中客户端的uuid
    let _agentDefaultCachePath = '';   // 选中客户端的文件缓存
    let _agentName = '';               // 选中客户端的客户端名
    let _agentIP = '';                 // 选中客户端的客户端IP
    let _agentType = '';               // 选中客户端的操作系统
    let _authInfo = [];                 // 功能授权信息
    let _takeoverModeValue = 1;          // 默认为整机接管
    let _netModel = 1; //网络模式
    let networkFlag = false;
    let _isHideSwitchBackIpFlag = false;  //是否隐藏回切通讯IP配置
    let _failbackIpVerify = false;  // 是否校验通过ip
    let _createMsgdata = {               // 消息结构对象
        backup_object:{},
        highInfo:{},
        cache_config:{},
        replication_object:{},         //备机复制对象配置
        takeover_object:{},            //接管配对象配置 
        safe_config_strategy:{},       //安全策略
        script_list:{},     //脚本列表
        dev_type:2,         //备份类型 1卷 2磁盘  *卷实时模块任务请求也需要增加该参数  
        high_pressure_strategy :{},
        backup_target_info : {},
        resource_protect_config : {}, // 自动降级优化配置
    };
    let crossPlatformInfo = {
        "cross_platform_flag" : "2",
        "cross_platform_conf" :"",
    };
    const TAKEOVER_TYPE = {
        "COMPLETE_MACHINE":1,
        "MOUNT":2
    };
    let _allDiskList = [];  //备份设备所有磁盘列表
    var _oldPassword = '',_ioReplicationMode =1;
    var resultData = {};
    let _updateAgentFlag = false;     // 是否更新客户端信息
    let netWorkInfo = {        // 主备机网卡信息
        agent_network_info : [],
        standby_network_info : [],
    };

    let initTagPointStrategyFlag = false;
    let initSpeedFlag = false;
    let speedList = [];  //限速策略设置
    let tagList = [];  //标签策略设置
    let _backupTaskType = "backup";  //备份任务类型，用以区分备份和复制；备份：backup，复制：copy
    let _takeoverIpMapListView = [];
    var zTreeAgent; //初始化备份源树形实例
    var zTreeCdpApp; //初始化备份源应用树形实例
    let EditFlag = false; // 标记是否修改
    let oldInfo = {}; // 旧任务信息
    let loadingTree = true; // 标记是否还在加载第一步
    let backupCheckedData;
    let backupinfo;
    let checkedNodes;
    let oldDataSourceDiskInfo;
    let isLicense = false; // 标记授权状态

    /**
     * 绑定自动监听事件
     */
    var initListener = function(){
        $('#toAdd').on('click',function(){
            LOCATION('./content/client/client.php', 'infrastructure');
        });

        $('#searchAgent').on('propertychange', searchAgent).on('input', searchAgent);
        $('#tran_compress_switch').on('switchChange.bootstrapSwitch', transferCompressChange);  //传输压缩开启

        $('#memory_cache_switch').on('switchChange.bootstrapSwitch', memoryCacheChange);  //内存缓存开关

        if (EditFlag) {
            // 忽略节点限制默认打开
            $('#ignoreResourceLimit').bootstrapSwitch('state', oldInfo.ignore_resource_limiting_flag);
        } else {
            // 忽略节点限制默认打开
            $('#ignoreResourceLimit').bootstrapSwitch('state', true);
        }

        // 容灾演练平台没授权的时候，这个整机接管应该不能显示出来 #26382
        if (!CONF.PERMISSION.includes('vm_machine_manager')) {
            // 没得容灾演练平台 ，那么去掉
            $('.takeovermodecmlabel').remove();
            $('.takeovermodemountlabel').removeClass('ml50');
        }

        backupTaskTypeLoadingInfo();

        if (EditFlag) {
            // 监听缓存配置的大小限制
            $('#file_cache_size').on('change', function (){
                if ($(this).val() < oldInfo.cache_config.agent_file_cache_used_space) {
                    // 选择的小于已经使用了的，那么阻止本次选择，并给出提示
                    $(this).val(oldInfo.cache_config.agent_file_cache_alloc_space);
                    UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_SETTING_USED_NUM + oldInfo.cache_config.agent_file_cache_used_space + 'MB');
                }
            })
            $('#memory_cache_size').on('change', function (){
                if ($(this).val() < oldInfo.cache_config.agent_memory_cache_used_space) {
                    // 选择的小于已经使用了的，那么阻止本次选择，并给出提示
                    $(this).val(oldInfo.cache_config.agent_memory_cache_alloc_space);
                    UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_SETTING_USED_NUM + oldInfo.cache_config.agent_memory_cache_used_space + 'MB');
                }
            })
        }
    }

    /**
     * 用以处理备份任务的备份流程和复制流程
     */
    let backupTaskTypeLoadingInfo = function(){
        _backupTaskType = $('#cm_backup_task_type').val();
        if (EditFlag) {
            if (oldInfo.job_type == CONF.TASK_TYPE.VOL_CDP_REPLICATION) {
                // 复制任务
                _backupTaskType = 'copy';
                // 回填是否备份数据
                $('.backupdatatoserver').bootstrapSwitch('state', oldInfo.take_over.config.mirror_backup_flag);
            } else {
                // 备份任务
                _backupTaskType = 'backup';
            }
        }
        if(_backupTaskType == "backup"){
            $('.replicationstandbyconfdiv').hide();
            $('.replicationbkdatatoserver').hide();
            if (EditFlag) {
                $('.backup_task_title').html(LANG.UI_CM_CDP_CREATE_BACKUP_TASK_EDIT_DESC);
            } else {
                $('.backup_task_title').html(LANG.UI_CM_CDP_CREATE_BACKUP_TASK_DESC);
            }
            _createMsgdata.is_replication = false;
        }else if(_backupTaskType == "copy"){
            $('.replicationstandbyconfdiv').show();
            $('.replicationbkdatatoserver').show();
            if (EditFlag) {
                $('.backup_task_title').html(LANG.UI_CM_CDP_CREATE_COPY_TASK_EDIT_DESC);
            } else {
                $('.backup_task_title').html(LANG.UI_CM_CDP_CREATE_COPY_TASK_DESC);
            }

            _createMsgdata.is_replication = true;
            $('.step1').html(LANG.UI_CM_CDP_COPY_SOURCE);
            $('.step2').html(LANG.UI_CM_CDP_COPY_DATA_DES);
            $('.step3').html(LANG.UI_CM_CDP_COPY_STRATEGY_DES);
        }
    }

    /**
     * 搜索客户端，隐藏非检索节点
     */
    var searchAgent = function(){
        var value = $('#searchAgent').val().toLowerCase(); // 获取输入值并转换为小写
        // 输入验证
        if(!customInputValidate('string',value)){
            return false;
        }
        var nodes = zTreeAgent.getNodes();
        if (!nodes || nodes.length == 0) return;

        // 隐藏所有节点
        zTreeAgent.hideNodes(nodes);

        // 找到匹配的节点集合
        let nodeParamList = zTreeAgent.getNodesByParamFuzzy('name', value);
        //如果没搜索到则给出提示
        if(nodeParamList.length == 0){
            $('#nosearchtips').show();
            $('.vcenter-tree').hide();
            return;
        }else{
            $('#nosearchtips').hide();
            $('.vcenter-tree').show();
        }
        // 找到父节点并添加到展示中，但不添加父节点的其他子节点
        var findParent = function(treeObj, node) {
            var pNode = node.getParentNode();
            if (pNode != null) {
                nodeParamList.push(pNode);
                // 确保父节点的其他子节点不被显示
                var siblings = pNode.children;
                for (var i = 0; i < siblings.length; i++) {
                    if (siblings[i] !== node) {
                        zTreeAgent.hideNode(siblings[i]);
                    }
                }
            }
        };

        for (var n in nodeParamList) {
            findParent(zTreeAgent, nodeParamList[n]);
        }

        // 排序
        nodeParamList = $.unique(nodeParamList.sort());
        // 展示找到的节点
        zTreeAgent.showNodes(nodeParamList);

        // 展开节点
        for (var n in nodeParamList) {
            zTreeAgent.expandNode(nodeParamList[n], true, false, true);
        }
    }
    /**
     * 获取客户端树
     * @param null
     * @return null
     */
    var initAgentTree = ()=> {
        pAjaxRequest({},'/api/v1/complete_machine_volcdp/agent_tree','GET',(d)=>{
            let zNodes = d.data;
            if(zNodes.length == 0){
                $("#novolcdpagent").show();
                $('.vcenter-tree').hide();
                $("#agent_tree").hide();
                $('.searchDiv').hide();
                return;
            }else{
                $("#novolcdpagent").hide();
                $('.vcenter-tree').show();
                $("#agent_tree").show();
                $('.searchDiv').show();
            }
            let setting = {
                check: {
                    enable: true,
                    nocheckInherit: false
                },
                data: {
                    simpleData: {
                        enable: true,
                    },
                    key:{
                        title: "title"
                    }
                },
                view: {
                    fontCss: getFontCss,
                    showLine:true,
                    showIcon:true,
                    nameIsHTML:true,
                },
                callback: {
                    beforeClick: agentnodeSelect,
                    onClick: function(event, treeId, treeNode) {
                        // 节点点击事件
                        var zTree = $.fn.zTree.getZTreeObj("agent_tree");
                        zTree.checkNode(treeNode, true, true, true);
                        zTree.selectNode(treeNode);
                    },
                    onCheck: function(event, treeId, treeNode) { // 复选框状态变化事件
                        var zTree = $.fn.zTree.getZTreeObj("agent_tree");
                        if (treeNode.checked) {
                            // 取消其他所有节点的选中状态
                            var nodes = zTree.getCheckedNodes(true);
                            nodes.forEach(function(node) {
                                if (node !== treeNode) {
                                    zTree.checkNode(node, false, true, false);
                                }
                            });
                            zTree.selectNode(treeNode);  // 选中对应节点
                            agentnodeSelect(treeId,treeNode);
                        }
                    }
                }
            };
            zTreeAgent = $.fn.zTree.init($("#agent_tree"), setting, zNodes);
            afterAgentTree(zNodes);
        })
    }

    /**
     * 初始化树之后的操作-修改
     */
    let afterAgentTree = function (zNodes){
        //获取zNodes一共有几个分组
        var big_group_num = 0;
        for(var i = 0; i < zNodes.length; i++ ){
            if(zNodes[i].type == 0){
                big_group_num ++;
            }
        }
        if(big_group_num == 1){
            //则展开所有
            zTreeAgent.expandAll(true);
        }
        //初始化完成后如果是修改还要执行修改的程序
        if(EditFlag){
            var nodes_list = zTreeAgent.transformToArray(zTreeAgent.getNodes());
            //获取历史agent_uuid
            var old_agent_uuid = oldInfo.backup_oss_info.agent_uuid;
            //查找data.data中的uuid
            for(var i = 0; i < nodes_list.length; i++) {
                var agent_uuid = nodes_list[i].uuid;
                if(old_agent_uuid == agent_uuid){
                    //先展开所有
                    zTreeAgent.expandAll(true);
                    //先解禁
                    zTreeAgent.setChkDisabled(nodes_list[i], false);
                    //勾选主机
                    zTreeAgent.checkNode(nodes_list[i], true, false);
                    //触发勾选事件
                    agentnodeSelect('agent_tree',nodes_list[i]);
                    loadingTree = false;
                }
                // 不能修改，禁用
                zTreeAgent.setChkDisabled(nodes_list[i], true);
            }
        }
    }

    /**
     * 客户端节点点击事件
     * @param treeId
     * @param treeNode
     * @param clickFlag
     * @return {boolean}
     */
    let agentnodeSelect = (treeId, treeNode) => {
        if (!loadingTree && EditFlag) {
            // 修改手动触发选择事件
            return;
        }
        _existTtaskName = "";
        _optionType = treeNode.osType;
        _inTask = false,_isHaStandby = false,_isRecoveryTarget= false,_isFbTarget=false,_isTakeoverStandby=false;
        if(treeNode.type == CONF.FLAG.SET){  //客户端分组名称
            return;
        }
        if(treeNode.onlineFlag == CONF.FLAG.UNSET){   // 客户端不在线
            _existTtaskName = treeNode.title;
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_BACKUP_CLIENT_OFF_LINE_TIPS);
            $('#hostinfo').hide();
            $('#step1tips').show();
            return false;
        }

        if(!!treeNode.inTask && !EditFlag){
            _inTask = treeNode.inTask;  // 判断是否有任务运行
            _existTtaskName = treeNode.title;
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+"," +LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
            $('#hostinfo').hide();
            $('#step1tips').show();
            return false;
        }
        if(!!treeNode.isHaStandby){  // 判断客户端是否作为接管备机
            _isHaStandby = treeNode.isHaStandby;
            _existTtaskName = treeNode.title;
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_HOST_IS_AVAILABLE_STANDBY+"," + LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
            $('#hostinfo').hide();
            $('#step1tips').show();
            return false;
        }

        if(!!treeNode.isRecoveryTarget){  // 判断客户端是否作为恢复目标机器
            _isRecoveryTarget = treeNode.isRecoveryTarget;
            _existTtaskName = treeNode.title;
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_HOST_IS_AVAILABLE_RECOVERY_TARGET+"," + LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
            $('#hostinfo').hide();
            $('#step1tips').show();
            return false;
        }

        if(!!treeNode.isFbTarget){  // 判断客户端是否作为回切目标机器
            _isFbTarget = treeNode.isFbTarget;
            _existTtaskName = treeNode.title;
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_HOST_IS_AVAILABLE_FAILBACK_TARGET+"," + LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
            $('#hostinfo').hide();
            $('#step1tips').show();
            return false;
        }

        if(!!treeNode.isTakeoverStandby){  // 判断客户端是否作接管备机
            _isTakeoverStandby = treeNode.isTakeoverStandby;
            _existTtaskName = treeNode.title;
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_HOST_IS_AVAILABLE_TAKEOVER_TARGET+"," + LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
            $('#hostinfo').hide();
            $('#step1tips').show();
            return false;
        }


        let treeObj = $.fn.zTree.getZTreeObj(treeId);
        //  treeObj.checkNode(treeNode, !treeNode.checked, true);
        _netModel = treeNode.net_model;
        // 客户端连接服务端
        if(treeNode.net_model == 2){
            networkFlag = true;
        }

        _agentuuid = treeNode.uuid;
        _agentDefaultCachePath = treeNode.agent_default_cache_path;  // 客户端文件缓存
        _agentName = treeNode.name;
        _agentIP = treeNode.title;
        _agentType = treeNode.osType;

        _createMsgdata.agentUUID = treeNode.uuid;
        _createMsgdata.agetName = treeNode.name;
        _createMsgdata.agent_hardware_info = treeNode.other_detail_hardware_info;

        verifyingVolCdpAuthNum();  //校验卷CDP客户端授权数是否充足
    }

    /**
     * 校验模块授权数是否充足，是择执行更新操作，否则提示错误信息
     */
    let verifyingVolCdpAuthNum = () => {
        let p = {};
        //备份任务类型，用以区分备份和复制；备份：backup，复制：copy
        if(_backupTaskType == "backup"){
            p.type = 'cdp';
        }else{
            p.type = 'copy_machine';
        }
        pAjaxRequest(p,'/api/v1/complete_machine_volcdp/auth_info','GET',(d)=>{
            let data = d.data;
            let authResult = data.auth_result;
            _authInfo = data.auth_function;
            if(authResult){
                $('#step1tips').hide();
                $('#hostinfo').show();
                if(!_updateAgentFlag){
                    updateAgentInfo(_agentuuid);  // 自动刷新客户端，获取到返回之后再更新应用和卷芯
                }
                loadAgentDiskInfo();
            }else{
                if(_backupTaskType == "backup"){
                    UIToastr.showWarning(LANG.UI_PUBLIC_OBTAIN_LICENSE_INFO,LANG.UI_CM_CDP_AUTH_NUM_CANNOT_CONFIG_TIP);
                }else{
                    UIToastr.showWarning(LANG.UI_PUBLIC_OBTAIN_LICENSE_INFO,LANG.UI_CM_COPY_AUTH_NUM_CANNOT_CONFIG_TIP);
                }
                return;
            }
        })
    }

    /**
     * 更新客户端信息，包括客户端对应的卷信息和应用信息
     */
    let updateAgentInfo = (agent_uuid) => {
        _updateAgentFlag = true;
        //  Metronic.blockUI({target: "#tab1",animate: true});
        pAjaxRequest({},'/api/v1/complete_machine_volcdp/update/agent/'+agent_uuid,'PUT',(d)=>{
            //  Metronic.unblockUI("#tab1");
            _updateAgentFlag = false;
            let message = LANG.UI_CLIENT_REFRESH_TITLE;
            let title =  LANG.UI_CLIENT_REFRESH_TITLE;
            if(!d.success){
                UIToastr.showWarning(title,message+LANG.UI_VOL_CDP_OPERATION_FAIL+"，"+LANG.UI_VOL_CDP_HOST_VOL_HIS_DATA);
            }
            initAppTree();
            loadAgentNetworkInfo(agent_uuid,true);
            getAgentCachePath(agent_uuid);
        })
    }

    /**
     * 获取所选主机应用信息
     * @return null
     */
    let initAppTree = () => {
        let requestData = {
            agent_uuid: _agentuuid,
            agent_name: _agentName,
            agent_ip: _agentIP
        }
        $("#cm_volcdp_app_tree").show();
        //  Metronic.blockUI({target: "#tab1",animate: true});  // 能够为页面上的任意元素添加遮层,阻止用户操作
        pAjaxRequest(requestData,'/api/v1/complete_machine_volcdp/app_info','GET',(d)=>{
            let zNodes = d.data;
            //  Metronic.unblockUI("#tab1");
            if(zNodes.length == 0){
                $("#noappinfo").show();
                $('.vcenter-tree').hide();
                $('#cm_volcdp_app_tree').hide();
                $('#checkapptips').hide();
                return;
            }else{
                $("#noappinfo").hide();
                $('.vcenter-tree').show();
                $("#cm_volcdp_app_tree").show();
                $('#checkapptips').show();
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
                // callback: {
                //     beforeClick: nodeSelect,
                //     onCheck: appOnCheck,
                // }
            };
            zTreeCdpApp = $.fn.zTree.init($("#cm_volcdp_app_tree"), setting, zNodes);
            afterAppTree();
        })
    }

    /**
     * 回填应用选择
     */
    var afterAppTree = function (){
        if (EditFlag && _createMsgdata.agentUUID == oldInfo.backup_oss_info.agent_uuid) {
            // 是修改，并且选择的客户端和旧的一致，那么此时需要回填应用选择
            var nodes_list = zTreeCdpApp.transformToArray(zTreeCdpApp.getNodes());
            //查找data.data中的uuid
            for(var i=0; i<nodes_list.length; i++){
                if(nodes_list[i].uuid != undefined && oldInfo.backup_oss_info.app_uuid == nodes_list[i].uuid){
                    //先展开所有
                    zTreeCdpApp.expandAll(true);
                    //勾选应用
                    zTreeCdpApp.checkNode(nodes_list[i], true, false);
                    // 把它的上级也勾选上
                    // ✅ 关键：向上递归勾选所有父节点
                    var parentNode = nodes_list[i].getParentNode();
                    while (parentNode) {
                        zTreeCdpApp.checkNode(parentNode, true, false); // 勾选父节点
                        parentNode = parentNode.getParentNode();        // 继续向上
                    }
                    break;
                }
            }
            /*var appTree = $.fn.zTree.getZTreeObj("cm_volcdp_app_tree");
            checkedNodes = appTree.getCheckedNodes(true);
            for(var i=0; i<nodes_list.length; i++) {
                // 不能修改，禁用
                zTreeCdpApp.setChkDisabled(nodes_list[i], true);
            }*/
        }


    }

    /**
     * 添加应用更新按钮
     * @param treeId
     * @param treeNode
     */
    var addHoverDom = (treeId, treeNode) => {
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
        Metronic.blockUI({target: "#hostappdiv",animate: true});// 能够为页面上的任意元素添加遮层,阻止用户操作
        pAjaxRequest(requestData,'/api/v1/complete_machine_volcdp/update/app','PUT',(d)=>{
            Metronic.unblockUI('#hostappdiv');
            if(operateResponseList(d)){
                initAppTree();
            }
        });
    }

    /**
     * 获取客户端网卡信息
     * @param agent_uuid 客户端uuid
     * @param isAgent 是否为客户端 true 备份客户端，false 目标客户端
     */
    let loadAgentNetworkInfo = (agent_uuid,isAgent) => {
        let requestData = {
            agent_uuid:agent_uuid
        };
        pAjaxRequest(requestData,'/api/v1/complete_machine_volcdp/networkcard_info','GET',(d)=>{
            if(isAgent){
                netWorkInfo.agent_network_info = d.data;
            }else{
                netWorkInfo.standby_network_info = d.data;
                // 这里提前把挂载接管的备机和网络给初始化下
                if (EditFlag && oldInfo.take_over.auto_takeover_enable_flag && oldInfo.take_over.config.takeover_type == 2) {
                    // 初始化网络
                    initNetwork();
                }
            }
        });
    }

    /**
     * 获取选中客户端的缓存路径
     */
    let getAgentCachePath = (agent_uuid) => {
        let requestData= {
            agent_uuid: agent_uuid
        }
        pAjaxRequest(requestData,'/api/v1/complete_machine_volcdp/agent/cache_path','GET',(d)=>{
            let data = d.data;
            _agentDefaultCachePath  = data.agent_cache_path;
        });
    }

    /*------------------非业务逻辑------------------*/
    /**
     * 动态调整客户端节点样式
     * @param treeId
     * @param treeNode
     * @return {{color: string, "font-weight": string}}
     */
    let getFontCss = (treeId, treeNode) => {
        let css = {color:"#333", "font-weight":"normal"};
        if(!!treeNode.inTask || !!treeNode.isHaStandby || !!treeNode.isRecoveryTarget || !!treeNode.isFbTarget || !!treeNode.isTakeoverStandby){
            css = {color:"green", "font-weight":"bold"};
        }
        if(treeNode.onlineFlag == CONF.FLAG.UNSET){
            css = {color:"gray", "font-weight":"normal"};
        }
        return css;
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
     * 显示第二步需要加载的数据
     */
    let showStep2 = function(){
        var options = {
            exclude_storage_type_list:[
                CONF.BD_STORAGE_TYPE.REMOTE,CONF.BD_STORAGE_TYPE.HUAWEICBR,CONF.BD_STORAGE_TYPE.CLOUD,CONF.BD_STORAGE_TYPE.TAPE,
            ]
        };
        if (EditFlag) {
            options.node_uuid = oldInfo.node.node_uuid;
            options.node_pool_uuid = oldInfo.node.node_pool_uuid;
            options.storage_uuid = oldInfo.node.storage_uuid;
            options.storage_pool_uuid = oldInfo.node.storage_pool_uuid;
            options.storage_pool_type = oldInfo.node.storage_pool_type;
        }
        $('#backupTarget').backupTarget(options);
        $(".cmcopystandbymapconf").unbind('click').click(function (){
            cmCopyStandbyMapConf(0)
        });     //整机复制备机及磁盘映射配置
        if(_backupTaskType == "backup"){
            $('.cmcdpcopystandbyconfdes').hide();
        }else{
            $('.cmcdpcopystandbyconfdes').show();
            delStandbyConfDescription();  //清空复制配置
            if (EditFlag) {
                // 回填是否备份数据
                $('.backupdatatoserver').bootstrapSwitch('state', oldInfo.take_over.config.mirror_backup_flag);
                cmCopyStandbyMapConf(1);
            }
        }

        // 这里提前把挂载接管的备机和网络给初始化下
        if (EditFlag && oldInfo.take_over.auto_takeover_enable_flag && oldInfo.take_over.config.takeover_type == 2 && _backupTaskType == "backup") {
            // 备机配置
            mountTakeoverStandbyMapConf(true);
        }
    }

    // 接管回切通讯IP配置视图  
    // bug #27217 需求放开关于回切通讯IP的判断
    let switchBackIpConfView = function(){
        let standbyAgentType = $('#cmBackupTaskCopyStandbySelect option:selected').attr('agent_type');
        if(_backupTaskType == "copy"  && standbyAgentType== CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){  //任务类型为复制，且备机客户端类型为 livecd
            $('.takeoverrestoreipview').hide();
            _isHideSwitchBackIpFlag = true;
        }else if(_backupTaskType == "copy"  && standbyAgentType== CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_NORMAL){
            $('.takeoverrestoreipview').show();
            _isHideSwitchBackIpFlag = false;
        }

    }

    /**
     * 复制任务备机及映射卷配置
     */
    let cmCopyStandbyMapConf = function(flag){
        if (flag == 0 && EditFlag && oldInfo.take_over.config.standby_agent_uuid) {
            // 修改并且是重复触发事件
            $('#copyStandbyConfModal').modal({'width':'880px', 'height':'460px'});
            $(".cmcdpcopytargetmapconfdefault").hide();
            $(".cmcdpcopytargetmapconf").show();
            $('.drivercheckview').hide();
            $('a[name="strategystandbymap"]').removeClass('collapsed');  //展开手风琴
            _createMsgdata.standby_agent_uuid = oldInfo.take_over.config.standby_agent_uuid;
            return;
        }
        var params = {};
        params.master_os_type = _agentType;
        params.master_uuid = _agentuuid;
        params.task_type = CONF.TASK_TYPE.VOL_CDP_REPLICATION;
        Metronic.blockUI({target: ".cmbackuptaskconf",animate: true});
        pAjaxRequest(params, "/api/v1/complete_machine_volcdp/takeover_target_host", "GET", function (result) {
            Metronic.unblockUI(".cmbackuptaskconf");
            if (result.success) {
                let cmBackupTaskCopyStandbySelect = $('#cmBackupTaskCopyStandbySelect');
                cmBackupTaskCopyStandbySelect.empty();
                let data = result.data;
                for(var i=0; i<data.length; i++){
                    let standbyAgentName = data[i].standby_agent_name;
                    let standbyAgentuuid = data[i].standby_agent_uuid;
                    let agentType = data[i].agent_type;
                    // agentType = 2;
                    if(standbyAgentuuid ==_agentuuid ){
                        continue;
                    }
                    let inTask  = data[i].in_task;
                    let taskName = data[i].task_name;
                    let onlineFlag = data[i].online_flag;
                    let osType = data[i].os_type;
                    var option = $("<option>").text(standbyAgentName).val(standbyAgentuuid).attr("in_task",inTask).attr("task_name",taskName).attr('online_flag',onlineFlag).attr('agent_type',agentType).attr('agent_os_type',osType);
                    cmBackupTaskCopyStandbySelect.append(option);
                }
                if (EditFlag && flag != 0) {
                    // 回填复制备机 并触发改变事件
                    cmBackupTaskCopyStandbySelect.val(oldInfo.take_over.config.standby_agent_uuid);
                    cmCopyTaskStandbySelectChange(flag);
                }
            }
        }, false);
        if (flag == 0) {
            $('#copyStandbyConfModal').modal({'width':'880px', 'height':'460px'});

            $(".cmcdpcopytargetmapconfdefault").show();
        }
        $('#cmBackupTaskCopyStandbySelect').unbind('change').bind('change', function (){
            cmCopyTaskStandbySelectChange(0)
        });  //切换复制备机
        $('.submitcopystandbyconf').unbind('click').click(function (){
            submitCopyTaskStandbyConf(0)
        });
        $(".cmcdpcopytargetmapconf").hide();
        $('.drivercheckview').hide();
        $('a[name="strategystandbymap"]').removeClass('collapsed');  //展开手风琴
    }
    /**
     * 切换复制任务备机事件
     * 需要判的备机是否在线，是否有任务关联。加载备机对应的磁盘信息
     */
    let cmCopyTaskStandbySelectChange = function(flag){
        $(".cmcdpcopytargetmapconfdefault").hide();
        $(".cmcdpcopytargetmapconf").show();
        let selectId = "cmBackupTaskCopyStandbySelect";

        var selectedOption = $(this).find('option:selected');
        let agentType = $('#'+selectId).find("option:selected").attr("agent_type");
        let standbyInTask = $('#'+selectId).find("option:selected").attr("in_task");
        let onlineFlag = $('#'+selectId).find("option:selected").attr("online_flag");
        let taskName = $('#'+selectId).find("option:selected").attr("task_name");
        let agentUuid =  $('#'+selectId).val();
        let agentName = $('#'+selectId+' option:selected').text();
        //选择第一项，移除历史配置，重置手风琴
        if (selectedOption.is(':first-child')) {
            $('.cmcdpcopytargetmapconf').html('');
            $('div[name="copyTaskStandbyMapConfCollapse"]').removeClass('in');
            $('a[name="strategystandbymap"]').addClass('collapsed');
            return;
        }
        //备机类型为内存操作系统，需要判断数据源是否选中了系统相关分区，关联bug #22531
        if(agentType == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){
            let systemPartitionCheckedInfo = systemPartitionChecked();
            let isSystemDiskFlag = systemPartitionCheckedInfo['isSystemDiskFlag'];
            let systemDiskUuid = systemPartitionCheckedInfo['systemDiskUuid'];
            let checkedDiskNode = systemPartitionCheckedInfo['checkedDiskNode'];
            //没有选中系统分区所在磁盘，不能选中内存操作系统作为备机
            if(!isSystemDiskFlag){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE, LANG.UI_CM_CDP_SYSTEM_VOL_NO_CHECKED);
                $('#cmBackupTaskCopyStandbySelect option:first').prop('selected', true);
                return false;
            }else{
                let sysVolIsSelected = getSysVolIsSelected(checkedDiskNode,systemDiskUuid);
                if(!sysVolIsSelected){
                    UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE, LANG.UI_CM_CDP_SYSTEM_VOL_NO_CHECKED);
                    $('#cmBackupTaskCopyStandbySelect option:first').prop('selected', true);
                    return false;
                }
            }

        }
        if(standbyInTask == true && !(EditFlag && agentUuid == oldInfo.take_over.config.standby_agent_uuid)){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_STANDBY_CHANGE_DESC + LANG.UI_PLATFORM_ASSOCIA_TASK+ " " + taskName + " "  );
            return false;
        }
        if(onlineFlag != CONF.FLAG.SET){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_VOL_CDP_BACKUP_AUTOTAKEOVER_STANDBY_OFFLINE_TIPS );
            return false;
        }
        Metronic.blockUI({target: "#copyStandbyConfModal",animate: true});

        //备机内存操作系统需要更新客户端
        if(agentType == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){
            pAjaxRequest( {}, '/api/v1/agents/'+agentUuid+'/refresh', 'post', (d)=>{
                loadAgentNetworkInfo(agentUuid,false);
                $('.drivercheckview').show();
                initDriverCheck(agentUuid,agentName); //初始化驱动检测
                $('#cmReplicationCheckDriver').unbind('click').click(checkDriverEvent);
                getCopyTargetConfig(flag);  //获取复制目标机器的磁盘信息，与主机磁盘信息构建映射关系
            });
        }else{
            $('.drivercheckview').hide();
            loadAgentNetworkInfo(agentUuid,false);
            getCopyTargetConfig(flag);  //获取复制目标机器的磁盘信息，与主机磁盘信息构建映射关系
        }
    }

    //判断系统分区是否选中
    let systemPartitionChecked = function(){
        let dataSouceAgentuuid = _createMsgdata.agentUUID;
        let getBackupSourceAllNodes = $("#checkedHostDiskSourceDiv").methodVinBackupSource('getBackupSourceAllNodes'); //获取数据源所有节点
        let allNodes = getBackupSourceAllNodes[dataSouceAgentuuid]['allNodes'];
        let checkedDiskNode = [];
        for(var j=0;j<allNodes.length;j++){
            if(allNodes[j].checked && allNodes[j].is_disk_flag){  //选中节点并且并且选中节点是磁盘
                checkedDiskNode.push(allNodes[j]);
            }
        }
        let isSystemDiskFlag = false;
        let systemDiskUuid = "";
        for(var m =0;m<checkedDiskNode.length;m++){
            if(checkedDiskNode[m].is_system_disk_flag =="1"){
                isSystemDiskFlag = true;
                systemDiskUuid = checkedDiskNode[m].dev_uuid;
                break;
            }
        }
        return {
            "checkedDiskNode":checkedDiskNode,
            "isSystemDiskFlag":isSystemDiskFlag,
            "systemDiskUuid":systemDiskUuid
        };
    }
    //获取系统磁盘对应的系统分区选中情况
    let getSysVolIsSelected = function(checkedDiskNode,systemDiskUuid){
        let nodeChildren = [];
        for(var h=0;h<checkedDiskNode.length;h++){
            if(checkedDiskNode[h].dev_uuid == systemDiskUuid){  //系統磁盤所在分區
                nodeChildren.push(checkedDiskNode[h].children);
            }
        }
        let noCheckedDevList = [];  //获取系统磁盘所有未选中设备的dev_type
        let nodeChildrenData = nodeChildren[0];
        for (var d =0;d<nodeChildrenData.length;d++){
            //获取所有未选中设备的dev_type;
            if(nodeChildrenData[d]['checked']==false){
                noCheckedDevList.push(nodeChildrenData[d].dev_type);
            }
        }
        let sysVolChecked = true;  //标记所有相关系统分区都被选中
        for(var n=0;n<noCheckedDevList.length;n++){
            let devType = noCheckedDevList[n];
            if(CONF.VOLUME_TYPE.BD_BOOT_VOLUME == (devType & CONF.VOLUME_TYPE.BD_BOOT_VOLUME) || CONF.VOLUME_TYPE.BD_EFI_VOLUME == (devType & CONF.VOLUME_TYPE.BD_EFI_VOLUME) || CONF.VOLUME_TYPE.BD_SYSTEM_VOLUME == (devType & CONF.VOLUME_TYPE.BD_SYSTEM_VOLUME)  ){
                sysVolChecked = false;
                break;
            }
        }
        return sysVolChecked;
    }

    /**
     * 触发检测驱动
     */
    let checkDriverEvent = function(){
        let cmStandbySelect = $('#cmBackupTaskCopyStandbySelect').val();
        if(cmStandbySelect == '' || cmStandbySelect == null){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_COPY_STANDBY_CONF_TIPS);
            return false;
        }
        $.fn.driverCheck.check($('#replicationDriverCheck'));
        // getCopyTargetConfig();  //获取复制目标机器的磁盘信息，与主机磁盘信息构建映射关系
        console.log('checkDriverEvent')
        _createMsgdata.standby_agent_uuid = cmStandbySelect;
    }
    /**
     * 初始化驱动检测
     */
    let initDriverCheck = function  (agentUuid,agentName) {
        $.fn.driverCheck.init($('#replicationDriverCheck'), {
            width: {
                'config_label': 'col-md-2',
                'config_content':'col-md-10',
                'check_name':'col-md-5',  // 检测内容的名称宽度，默认值为col-md-3
                'check_platform': 'col-md-5',  //检测内容的是否异构平台宽度，默认值为col-md-3
                // check_driver: 检测内容的驱动检测宽度，默认值为col-md-6
                // result_name: 检测结果的名称宽度，默认值为col-md-3
                // result_detail: 检测结果的详情宽度，默认值为col-md-7
                // result_upload: 检测结果的上传按钮宽度，默认值为col-md-2
            },
            getCheckObject: () => {
                return {
                    source_list: [
                        {
                            name: agentName,
                            agent_uuid: _agentuuid,
                        }],
                    target_info: {
                        target_type: $.fn.driverCheck.DRIVER_TARGET_TYPE.CLIENT,
                        client_info: {
                            agent_uuid: agentUuid,
                        },
                    }
                };
            },
            show_check_btn: false,
            driver_usage:$.fn.driverCheck.DRIVER_USAGE_ENUM.BACKUP,
            afterCheck:(result,data)=>{
                crossPlatformInfo.cross_platform_flag = data[0].diff_platform_type;
                crossPlatformInfo.cross_platform_conf = data[0].driver_hw_id_map;
            }
        });

    }

    /**
     * 提交复制备机相关配置参数
     */
    let submitCopyTaskStandbyConf = function(flag){
        let standbyHostUuid = $('#cmBackupTaskCopyStandbySelect').val();
        let standbyAgentName = $('#cmBackupTaskCopyStandbySelect option:selected').text();
        if(standbyHostUuid=="" || standbyHostUuid==undefined){
            UIToastr.showWarning(LANG.UI_CM_CDP_COPY_STANDBY_CONF, LANG.UI_CM_CDP_COPY_STANDBY_CONF_TIPS);
            return false;
        }
        //如备机为livecd，需要检测驱动是否匹配
        let standbyAgentType = $('#cmBackupTaskCopyStandbySelect option:selected').attr('agent_type');
        // standbyAgentType = CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_NORMAL;  //因livecd 驱动检测问题，临时将系统类型调整为NORMAL
        let driverHwIdMap = {};
        if(standbyAgentType== CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){  //客户端类型为 livecd
            let checkResult = $.fn.driverCheck.validCheckResult($("#replicationDriverCheck"));


            if(checkResult==undefined){
                UIToastr.showWarning(LANG.UI_CM_CDP_COPY_STANDBY_CONF, LANG.UI_DRIVER_CHECK_RESULT_NOT_CHECK);
                return false;
            }

            if(!checkResult){
                UIToastr.showWarning(LANG.UI_CM_CDP_COPY_STANDBY_CONF, LANG.UI_DRIVER_CHECK_RESULT_NOT_PASS);
                return false;
            }
            let getDriverData = $.fn.driverCheck.getCheckResult($("#replicationDriverCheck"));
            driverHwIdMap = getDriverData.driver_hwid_map;

        }
        // _createMsgdata.replication_object.cross_platform_check_result = driverHwIdMap;  //驱动检测赋值

        let changeData = $(".cmcdpcopytargetmapconf").getTargetData("getEachData",_agentuuid);
        let targetStrategyData = changeData.target_strategy;
        let standbyDevRelationSet = [];
        for(var i=0;i<targetStrategyData.length;i++){
            let info = {};
            info.dev_uuid = targetStrategyData[i].source_dev_uuid;
            info.target_dev_uuid = targetStrategyData[i].destination_dev_uuid;
            info.source_dev_name = targetStrategyData[i].source_name;
            info.target_dev_name =  targetStrategyData[i].destination_name;
            if (flag) {
                info.source_dev_name = '<i class="levelchild viconfont vicon-cipan1 ztree_icon_color"></i>' + info.source_dev_name;
            }

            standbyDevRelationSet.push(info);
        }

        $('#copyStandbyConfModal').modal('hide');
        cmBackupTaskCopyStandbyConfDes(standbyAgentName,standbyDevRelationSet);

        let replication_object = {};
        replication_object.standby_flag = 1;
        replication_object.standby_agent_uuid = standbyHostUuid;
        replication_object.cross_platform_check_result = driverHwIdMap;  //驱动检测赋值

        replication_object.cross_platform_flag = crossPlatformInfo.cross_platform_flag;
        replication_object.cross_platform_conf = crossPlatformInfo.cross_platform_conf;
        replication_object.standby_dev_relation_set = standbyDevRelationSet;  //"dev_uuid" : "xxxxxxxxxx",	*注:主机原磁盘设备uuid; "target_dev_uuid" : "xxxxxxxxx" *注:备机目标磁盘设备uuid
        _createMsgdata.replication_object = replication_object;

        if (flag != 0) {
            // 网络配置
            if (EditFlag && oldInfo.take_over.auto_takeover_enable_flag
                && oldInfo.take_over.config.network.takeover_business_ip_map.length > 0
            ) {
                // 获取网络
                submitHostNetworkConf();
            }
        }
    }
    /**
     * 复制配置备机信息汇总
     */
    var cmBackupTaskCopyStandbyConfDes = function(standbyAgentName,standbyDevRelationSet){
        $('.cmcdpcopystandbyconfdes').html('');
        let isBr = '<div>';
        for(var i=1;i<standbyDevRelationSet.length;i++){
            isBr += "<br>";
        }
        let devStr = "";

        for(var i= 0; i < standbyDevRelationSet.length; i++){
            let sourceDevName = standbyDevRelationSet[i].source_dev_name;
            let targetDevName = standbyDevRelationSet[i].target_dev_name;


            var desTitle =  sourceDevName + "-->" + targetDevName;
            devStr += "<span class ='ml10' title = '"+desTitle+"' >" + desTitle +"</span><br>";

        }
        var des = '<li style="margin-top:15px;" class="list-group-item popovers standbyConfTips" id="cmConfDesTips" '
            +'data-container="body" data-trigger="hover" data-placement="top" data-html="true" >'
            +'<div class="col1"><div class="cont ">'
            +'<div class="cont-col1"></div><div class="cont-col2">'
            +'<div class="desc list-one" id = "vmDetailConfigInfo" style="width: 100%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
            +'<sapn>'+LANG.UI_CM_CDP_DATA_SOURCE_HOST + '</sapn>:<span class ="ml10" title = "">'+ _createMsgdata.agetName +'</span><br>'
            +'<sapn>'+LANG.UI_CM_CDP_TARGET_STANDBY_TIPS + '</sapn>:<span class ="ml10">'+ standbyAgentName +'</span><br>'
            +'<div><div style="float: left">'
            +'<sapn>'+ LANG.UI_CM_DISK_MAP + '</sapn>:</div> <div style="float: left">' + devStr + '</div>'+'</div><br>'
            +'<div></div><br>'
            +'</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;">'
            +'<a class="standbyConfDesDel" >'
            +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';

        $('.cmcdpcopystandbyconfdes').append(des);
        $('.vmConfTips').popover();	   //初始化tips
        $('.standbyConfDesDel').bind("click",delStandbyConfDescription);
    }

    let delStandbyConfDescription = function(){
        $('.popover.in').remove();
        $('#cmConfDesTips').remove();
        $('.cmcdpcopystandbyconfdes').html('');
        _createMsgdata.replication_object = {};  //清空复制备机配置
        $('.backupdatatoserver').bootstrapSwitch('state', false);



    }

    // //初始化存储下拉框
    // var initStorageSelect = function () {
    //     var params = {};
    //     params.node_uuid = $('#selectnode').val();
    //     params.exchange_cloud_flag = 1;
    //     pAjaxRequest(params, "/api/v1/storages/backup", "GET", function (result) {
    //         if (result.success) {
    //             var softselect = $('#selectstorage');
    //             softselect.empty();
    //             for (var i = 0; i < result.data.length; i++) {
    //                 var option = $("<option>").text(result.data[i].text).val(result.data[i].storage_uuid).attr("storage_type",result.data[i].storage_type);
    //                 softselect.append(option);
    //             }
    //         }
    //     }, false);
    // }


    /*============================================================================ 显示并加载第3步相关数据============================================================================================*/

    /*============================================================================ 通用策略=============================================================*/
    /**
     * 初始化备份策略相关事件
     */
    var initDataChangeListeners = function (){
        initTagPointListeners();
        inintDatatimePicker();
        // initSpeedListeners();
        // initTimeListeners();
        initStoreListeners();  	//储存策略
        initReserveListeners(); //保留策略
        initStrategyDes();
        initTakeoverListeners();
    }
    // function 初始化定时标签策略 为标签策略各项操作绑定初始事件函数
    var initTagPointListeners = function (){
        //添加限速策略确定
        $('#tagpoint_submit').on('click', tagPointStrategySubmit);
        $('#add_tagpoint_limit').on('click', function(){
            // 初始化添加标签策略模态框
            if (CONF.LANGUAGE == "zh-cn" && CONF.LANGUAGE == "zh-tw") {
                $('#tagPointLimitModal').modal({'width':'800px', 'height':'380px'});
            }else{
                //英文版弹窗宽度需要长一些
                $('#tagPointLimitModal').modal({'width':'920px', 'height':'380px'});
            }
            if(!initTagPointStrategyFlag){
                initTagPointStrategy();
            }
        });
    }
    /**
     * @function 填充标签策略默认值
     */
    let initTagPointStrategy = function(){
        var strategy = [];
        strategy[0] = {
            mode: 7, //定义为标签点策略
            strategy_type: 2,
            days: [0, 0, 0, 0, 1, 0, 0],
            start_time: '23:00:00',
            end_time: '23:30:00',
            roll_interval:'01:00:00',
        };

        //延迟设置,因为这里icheck会默认修改里面的选中事件
        $('#tagpointstrategy').tagpointstrategy({config: strategy});
        initTagPointStrategyFlag = true;
    }
    /**
     * @funciton 添加标签策略
     * @return tagpointList
     */
    let tagPointStrategySubmit = function (strategyConfig = {}){
        if (strategyConfig.tagInfo == undefined) {
            strategyConfig = $('#tagpointstrategy').getTagPointStrategyConfig();
        }
        var info = {};
        var des = '';
        info.mode = strategyConfig.tagInfo.mode;
        info.type = strategyConfig.tagInfo.type;
        info.startTime = strategyConfig.tagInfo.startTime;
        info.rollFlag = strategyConfig.tagInfo.rollFlag;
        info.rollInterval = strategyConfig.tagInfo.rollInterval;
        if(info.rollFlag && info.rollInterval.split(':')[0] < 1){
            UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_LABEL_STRATEGY, LANG.UI_CM_CDP_LABEL_STRATEGY_ROLL_INTERVAL_TIP);
            return false;
        }
        info.endTime = strategyConfig.tagInfo.endTime;
        var liId = getUuid();
        info.uuid = liId;
        info.days = strategyConfig.tagInfo.days;
        info.des = strategyConfig.tagInfo.des;
        des +=
            '<li class="list-group-item popovers tagTips list-group-item__speed" id="tag'+ liId +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ info.des + '">' +
            '<div class="col1">' +
            '<div class="cont ">' +
            '<div class="cont-col1"></div>' +
            '<div class="cont-col2">' +
            '<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col2  pull-right delete-list">' +
            '<a class="del'+ liId +'" >' +
            '<div class="label label-sm label-danger" style="padding:0;">' +
            '<i class="viconfont vicon-cuowu"></i>' +
            '</div>' +
            '</a>' +
            '</div>' +
            '</li>';
        $('#tagpoint_list').append(des);
        $('.tagTips').popover();	   //初始化tips

        $('.del'+ liId).on('click', function(){
            $('.popover.in').remove();
            $('#tag' + liId).remove();
            for(var i=0;i<tagList.length; i++){
                if(liId == tagList[i].uuid){
                    tagList.splice($.inArray(tagList[i],tagList),1);
                }
            }
            addTagStrategyDes();
        });
        tagList.push(info);
        $('#tagPointLimitModal').modal('hide');
        addTagStrategyDes();
    }

    /**
     * @function 添加并展示标签策略描述信息
     */
    var addTagStrategyDes = function(){
        var titleDes = "";
        var des = "";
        if(tagList.length !=0){
            des += LANG.UI_VOL_CDP_JOB_DETAILS_LABEL_STRATEGY_NUM + ": " + tagList.length;
        }
        for(var i=0;i<tagList.length;i++){
            if(i>0){
                titleDes += tagList[i].des + '.  ';
            }else{
                titleDes += tagList[i].des + ". ";
            }
        }
        var strategyIndex = $('#strategySelect').val();
        if(strategyIndex && strategyIndex != "" && editFlag){
            var oldDes = globalStrategy[strategyIndex].speedlimit.des;
            initStrategyDesStyle($('.speedlimitDes'), titleDes, oldDes);
        }else{
            $('.speedlimitDes').removeClass('font-green-seagreen');
        }
        $('.backupTimeDes').html(des);
        $('.backupTimeDes').prop('title', titleDes);
    }
    //显示策略描述信息
    var initStrategyDes = function(){
        initStoreStrategyDes();
        initReserveStrategyDes();
    }
    //保留策略配置信息
    var initReserveListeners = function (){
        $('#spinnerDayInput').on('input propertychange', function(){
            initReserveStrategyDes();
        });
        $('.spinner-up').on('click', function(){
            initReserveStrategyDes();
            //initHighStrategyDes();
        });
        $('.spinner-down').on('click', function(){
            initReserveStrategyDes();
            //initHighStrategyDes();
        });
        $('#spinnerDayInput').blur(function(){
            initReserveStrategyDes();
        })
    }
    //显示保留策略描述信息
    var initReserveStrategyDes = function(){
        var des = "";
        var value = 0;
        let defaultReserveDay = 7;
        des += LANG.UI_STRATEGY_RESERVE_DAY;
        value = $('#spinnerDayInput').val();
        value = parseInt(value);
        des += ", " + LANG.UI_STRATEGY_RESERVE_VALUE + value;
        var strategyIndex = $('#strategySelect').val();
        if(strategyIndex && strategyIndex != "" && editFlag){
            var oldDes = globalStrategy[strategyIndex].reserve.des;
            initStrategyDesStyle($('.reserveDes'), des, oldDes);
        }else{
            $('.reserveDes').removeClass('font-green-seagreen');
        }
        $('.reserveDes').html(des);
        $('.reserveDes').prop('title', des);
    }
    /**
     *@function 初始化存储策略各项操作描述
     */
    var initStoreListeners = function(){
        //切换存储加密开关
        $('#encryptStorageCheck').on('switchChange.bootstrapSwitch', encryptChange);
        //切换自动选择存储加密密码
        $('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
        //数据加密密码确认
        $('#repassword').on('input propertychange', function(){
            var password = $('#password').val();
            var repassword = $('#repassword').val();
            if(password != repassword){
                $('.passwordTips').show();
            }else{
                $('.passwordTips').hide();
            }
        });
        $('#deduplicationCheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
        });
        $('#compressCheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
        });
        $('#encryptStorageCheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
        });
    }
    /**
     * @function 初始化存储策略描述
     */
    var initStoreStrategyDes = function(){
        var des = "";
        var diffDes = "";
        /** 卷CDP目前暂时不支持重复数据删除，此处解析暂时保留；
         if(authFun.length != 0 && authFun.dedupication){
			  des += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationCheck').get(0).checked) + ", ";
		        diffDes += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationCheck').get(0).checked) + "<br>";
		}
         */
        des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);
        diffDes += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);

        //除了hyper-v，其余虚拟化都有数据加密
        des += ","+LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);
        diffDes += LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);

        var strategyIndex = $('#strategySelect').val();
        if(strategyIndex && strategyIndex != "" && editFlag){
            var oldDes = globalStrategy[strategyIndex].store.des;
            initStrategyDesStyle($('.storeDes'), diffDes, oldDes);
        }else{
            $('.storeDes').removeClass('font-green-seagreen');
        }
        $('.storeDes').html(des);
        $('.storeDes').prop('title', des);
    }
    /**
     * 切换自动选择存储加密密码
     */
    var passwordModeChange = function(){
        if(this.checked){
            $('#password').val('');
            $('#repassword').val('');
            $('.passwordDiv').hide();
        }else{
            $('.passwordDiv').show();
        }
    }
    /**
     * 存储加密切换
     */
    var encryptChange = function(){
        if(this.checked){
            $('#passwordAutocheck').bootstrapSwitch('state', true);
            $('#password').empty();
            $('#repassword').empty();
            $('.passwordModeDiv').show();

        }else{
            $('#passwordAutocheck').bootstrapSwitch('state', false);
            $('.passwordModeDiv').hide();
            $('.passwordDiv').hide();
        }
    }
    /**
     * @function 得到开关的结果描述
     * @return 开启/关闭
     */
    let getSwitchDes = function(check){
        if(check==CONF.FLAG.SET){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }


    /*=====================================================================================传输策略相关 =============================================================*/
    // 显示压缩等级
    let transferCompressChange = function () {
        if (this.checked) {
            $('.transferCompressGradeDiv').show();
        } else {
            $('.transferCompressGradeDiv').hide();
        }
    };
    /*===============================================================================脚本相关配置======================================================================== */
    //加载并渲染脚本配置view
    let initVinScriptListeners = function(){
        //重置脚本配置view
        $('.cmBeforeBackupScript').html("");
        $('.cmAfterBackupScript').html("");
        $('.cmBeforeTakeoverScript').html("");
        $('.cmAfterTakeoverScript').html("");
        $('.cmTakeoverMonitorScript').html("");
        var before_task_script = [];
        var after_task_script = [];
        var before_take_over_script = [];
        var after_take_over_script = [];
        var take_over_script = [{
            "script_method":"0",
            "script_type":0,
            "script_uuid":"",
            "script_content":"",
            "script_name":"",
            "exec_interval":30,
            "trigger_fail_num ":1
        }];
        if (EditFlag) {
            var scripts = oldInfo.scripts;
            before_task_script = scripts.before_task_script;
            after_task_script = scripts.after_task_script;
            before_take_over_script = scripts.before_take_over_script;
            after_take_over_script = scripts.after_take_over_script;
            take_over_script = scripts.take_over_script;
        }
        againScripts('.selector', {class: 'cmBeforeBackupScript',include_list:[1,2],}, before_task_script);
        againScripts('.selector', {class: 'cmAfterBackupScript',include_list:[1,2],}, after_task_script);
        againScripts('.selector', {class: 'cmBeforeTakeoverScript',include_list:[1,2],}, before_take_over_script);
        againScripts('.selector', {class: 'cmAfterTakeoverScript',include_list:[1,2],}, after_take_over_script);
        againScripts('.selector', {
            class: 'cmTakeoverMonitorScript',
            include_list:[1,2],
            display_exec_interval:"display:block",
            display_trigger_fail_num:"display:block",
        }, take_over_script);
        // $('.selector').initVinScript({class: 'cmTakeoveExecuteScript'});
    }

    // 封装初始化脚本组件
    let againScripts = function ($div,  options, data = []){
        if (data.length > 0) {
            options.script_details = data;
        }
        $($div).initVinScript(options);
    }
    /*=====================================================================================接管策略相关 =============================================================*/
    //加载接管相关事件
    let initTakeoverListeners = function(){
        $('.backuptasktakeoverswitch').on('switchChange.bootstrapSwitch', backupTaskTakeoverSwitch);  //接管开关
        $('.apptakeoverswitch').on('switchChange.bootstrapSwitch',appTakeoverSwitch);     //应用接管开关
        $('.cmautotakeoverswitch').off('switchChange.bootstrapSwitch',).on('switchChange.bootstrapSwitch', cmAutoTakeoverSwitch);  //整机自动接管开关

        $('.appmonitorswitch').on('switchChange.bootstrapSwitch', cmAppMonitorSwitch);  //应用故障检测
        $('.cmstandbymapconf').unbind('click').click(cmTakeoverStandbyMapConf);  //接管备机配置
        $('input[name="cm_takeover_mode"]').change(changeCmTakeoverMode);   //切换接管模式

        $('#cmBackupTaskTakeoverStandbySelect').unbind('change').bind('change', function (){
            cmBackupTaskStandbySelectChange(0)
        });  //切换备机
        $('.submitmounttakeoverstandbyconf').unbind('click').click(submitMountTakeoverStandbyConf);   //提交挂载接管备机配置
        $('.takeovernetworkconf').unbind('click').click(takeoverNetworkConfModal);
        $('#linkSelectIP').unbind('click').click(checkTakeoverResIp);
        $('.takeoverfailbackupip').off().blur(takeoverRestoreIpChange);

    }
    /**
     * 当接管回切IP获取到焦点时，将接管回切通讯ip的状态设置为false，避免校验成功后又重新选择其他IP
     */
    var takeoverRestoreIpChange = function(){
        var ip = $(".takeoverfailbackupip").val();
        if((!checkIP(ip))&& ip.length>0||ip == ""){
            _failbackIpVerify = false;
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP_MESSAGE);
            $('.takeoverfailbackupip').val('');
            _createMsgdata.takeover_object.agent_failback_standby_ip = "";
            return false;
        }
    }
    /**
     * @function 校验接管服务ip
     * @description IP input 失去焦点时判断IP是否合法，并且校验当前输入IP在网络环境内是否可用
     * @return  true or false
     */
    let checkTakeoverResIp = function (){
        _failbackIpVerify = false;
        var ip = $(".takeoverfailbackupip").val();
        if((!checkIP(ip))&& ip.length>0||ip == ""){
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP_MESSAGE);
            $('.takeoverfailbackupip').val('');
            return false;
        }
        var params = {};
        params.ip = ip;

        pAjaxRequest(params, "/api/v1/volcdp/check_ip_exist", "GET", function (result) {
            let isExist = result.data.exists;
            _createMsgdata.takeover_object.agent_failback_standby_ip = ip;
            if (isExist==1) {
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP_MESSAGE);
                $('.takeoverfailbackupip').focus();
                $('.takeoverfailbackupip').css("border-color","#ff9800")
            }else{
                _failbackIpVerify = true;
                UIToastr.showSuccess(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP_USABLE_MESSAGE);
                $('.takeoverfailbackupip').css("border-color","#e5e5e5")
            }
        }, false);
    }

    //配置接管网络映射
    let takeoverNetworkConfModal = function(){
        var standbyHostUuid = _createMsgdata.standby_agent_uuid;
        if (!standbyHostUuid) {
            if (_backupTaskType == 'backup') {
                standbyHostUuid = $('#cmBackupTaskTakeoverStandbySelect').val(); //备机uuid
            } else {
                standbyHostUuid = $('#cmBackupTaskCopyStandbySelect').val(); //备机uuid
            }
        }

        if(!standbyHostUuid){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_STANDBY_CONF_TIPS);
            return false;
        }

        if(standbyHostUuid==0){
            UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION, LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION_TIPS);
            $('#script_takeover_switch').bootstrapSwitch('state', false);
            $('.script_takeover_conf_view').hide();
            return false;
        }

        $('.takeoverNetworkConfModal').modal({'width':'860px', 'height':'400px'});
        initNetwork();
        $("#submit_host_network_conf").unbind('click').click(submitHostNetworkConf);


    }

    // 初始化网络组件
    let initNetwork = function (){
        var overipConfig = {
            agentNetwork:netWorkInfo.agent_network_info,
            standbyNetwork: netWorkInfo.standby_network_info,
            takeoverHisNetwork:[],
            failbackHisNetwork:[],
        };
        if (EditFlag && oldInfo.take_over.auto_takeover_enable_flag && oldInfo.take_over.config.takeover_type == 2) {
            overipConfig.takeoverHisNetwork = oldInfo.take_over.config.network.takeover_business_ip_map;
        }
        $('#host_ip_server_conf').takeoverIpServerMapConfig(overipConfig);
        $('#standby_gateway_conf').takeoverStandbyGatewayConfig(overipConfig);
    }
    /**
     * 提交接管网络配置
     * @returns
     */
    let submitHostNetworkConf = function(){

        var info  = {};
        info.agentNetwork = netWorkInfo.agent_network_info;
        info.standbyNetwork = netWorkInfo.standby_network_info;
        let takeoverIpMapList = $('#submit_host_network_conf').takeoverGatewayMapConf(info);
        // _createMsgdata.takeover_object.takeover_business_ip_map = {};

        if (_createMsgdata && _createMsgdata.takeover_object) {
            _createMsgdata.takeover_object.takeover_business_ip_map = {};
        }
        if(takeoverIpMapList.length > 0){
            let agentNetworkMapInfo = [];
            for(let i =0;i<takeoverIpMapList.length;i++){
                let confId = takeoverIpMapList[i].conf_id;
                var  agentMapInfo = takeoverIpMapList[i].agent_map_info;
                agentMapInfo['conf_id'] = confId;

                agentNetworkMapInfo.push(agentMapInfo);
            }
            _createMsgdata.takeover_object.takeover_business_ip_map = agentNetworkMapInfo;
            _createMsgdata.takeover_object.business_ip_map_view = takeoverIpMapList;
            console.log('submitHostNetworkConf')
            $('.takeoverNetworkConfModal').modal('hide');
            $('#takeover_ip_map_list li').remove();
            hostGatewayConfDes(takeoverIpMapList);
        }

    }
    /**
     * 获取配置网卡描述信息，并渲染
     */
    var hostGatewayConfDes = function(takeoverIpMapList){
        var des = "";
        for(var i=0;i<takeoverIpMapList.length;i++){
            var conf_id = takeoverIpMapList[i].conf_id;
            var des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+ takeoverIpMapList[i].conf_id
                +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
                + takeoverIpMapList[i].conf_des + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one width90p" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
                + takeoverIpMapList[i].conf_des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;">'
                +'<a class="takeover_netcard_del'+  takeoverIpMapList[i].conf_id +'" >'
                +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
            $('#takeover_ip_map_list').append(des);
            $('.takeoverNetcardTips').popover();	   //初始化tips

            $('.takeover_netcard_del'+ conf_id ).bind("click",{index:i},clickHandler);

        }
    }
    /**
     * 为每一个<li>绑定点击事件，并处理移除数组操作
     */
    let clickHandler = function(event) {
        $('.popover.in').remove();
        var index = event.data.index;
        var confId;
        var list = _createMsgdata.takeover_object.business_ip_map_view;

        // 修正删除最后一个元素的问题
        if (index >= list.length) {
            index = list.length - 1;
        }

        // 查找并删除 _takeoverIpMapListView 中的元素
        if (index >= 0) {
            confId = list[index].conf_id;
            $('#takeover_netcard_' + confId).remove();
            _createMsgdata.takeover_object.business_ip_map_view.splice(index, 1);
        }

        // 确保 confId 已经被正确获取
        if (confId) {
            // 查找并删除 ipMapInfoList 中的元素
            // _createMsgdata.takeover_object.business_ip_map_view = ipMapInfoList.filter(function(item) {
            // 	return item.conf_id !== confId;
            // });

            // 查找并删除 _takeoverIpMapList 中的元素
            _createMsgdata.takeover_object.takeover_business_ip_map = _createMsgdata.takeover_object.takeover_business_ip_map.filter(function(item) {
                return item.conf_id !== confId;
            });
        }
    }

    //切换备机，需要判的备机是否在线，是否有任务关联。加载备机对应的磁盘信息
    let cmBackupTaskStandbySelectChange = function(flag){
        let selectId = "cmBackupTaskTakeoverStandbySelect";
        $(".cmcdptakeovertargetmapdf").hide();
        $(".cmcdptakeovertargetmapconf").show();

        var selectedOption = $(this).find('option:selected');
        if (selectedOption.is(':first-child')) {
            return;
        }
        let targetAgentUuid = $('#'+selectId).val();
        let standbyInTask = $('#'+selectId).find("option:selected").attr("in_task");
        let onlineFlag = $('#'+selectId).find("option:selected").attr("online_flag");
        let taskName = $('#'+selectId).find("option:selected").attr("task_name");

        if(standbyInTask == true){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_STANDBY_CHANGE_DESC + LANG.UI_PLATFORM_ASSOCIA_TASK+ " " + taskName + " "  );
            return false;
        }

        if(onlineFlag != CONF.FLAG.SET){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_VOL_CDP_BACKUP_AUTOTAKEOVER_STANDBY_OFFLINE_TIPS );
            return false;
        }
        $('.apptakeoverswitch').bootstrapSwitch('state', false);
        console.log('cmBackupTaskStandbySelectChange')
        _createMsgdata.standby_agent_uuid = "";  //清空选中备机UUID
        getTakeoverTargetConfig(flag);  //获取接管备机的磁盘信息，与主机磁盘信息构建映射关系
        loadAgentNetworkInfo(targetAgentUuid,false);  //获取接管备机网卡信息
    }

    //切换接管模式，整机接管需要应酬网络配置，挂载接管需要配置网络映射
    let changeCmTakeoverMode = function(){
        let takeoverMode = parseInt($(this).val());
        if (_createMsgdata && _createMsgdata.takeover_object) {
            _createMsgdata.takeover_object.takeover_mode = takeoverMode ?? TAKEOVER_TYPE.COMPLETE_MACHINE;
        }
        _takeoverModeValue = takeoverMode;
        takeoverModeChange(takeoverMode);


    }
    /**接管方式选择 */
    let takeoverModeChange = function (takeoverMode) {
        if (EditFlag && oldInfo.take_over.auto_takeover_enable_flag) {

        } else {
            console.log('takeoverModeChange')
            _createMsgdata.takeover_object = {};
            $('.mountTakeoverStandbyConfDes').html('');
            $('.cmTakeoverStandbyConfDes').html('');
            $('#takeover_ip_map_list').html('');
            $('.takeoverStandbyDes').html('');
        }

        if(_backupTaskType == "backup"){
            _createMsgdata.standby_agent_uuid = "";
            switch (takeoverMode){
                case 1:  //整机接管
                    let systemPartitionCheckedInfo = systemPartitionChecked();
                    let isSystemDiskFlag = systemPartitionCheckedInfo['isSystemDiskFlag'];
                    let systemDiskUuid = systemPartitionCheckedInfo['systemDiskUuid'];
                    let checkedDiskNode = systemPartitionCheckedInfo['checkedDiskNode'];
                    //没有选中系统分区所在磁盘，不能选中内存操作系统作为备机
                    if(!isSystemDiskFlag){
                        UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE, LANG.UI_CM_CDP_SYSTEM_VOL_NO_CHECKED_USED_CM_TAKEOVER);
                        resetRadio("cm_takeover_mode", TAKEOVER_TYPE.MOUNT)

                        return false;
                    }else{
                        let sysVolIsSelected = getSysVolIsSelected(checkedDiskNode,systemDiskUuid);
                        if(!sysVolIsSelected){
                            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE, LANG.UI_CM_CDP_SYSTEM_VOL_NO_CHECKED_USED_CM_TAKEOVER);
                            resetRadio("cm_takeover_mode", TAKEOVER_TYPE.MOUNT)
                            return false;
                        }
                    }

                    $('.takeoverNetworkConf').hide();
                    $('.mountTakeoverStandbyConfDes').hide(); //挂载接管配置描述信息
                    $('.cmTakeoverStandbyConfDes').show(); //整机接管配置描述信息

                    $('.cmtakeovermodehelp').show();
                    $('.mounttakeovermodehelp').hide();

                    $('#takeoverSafetyConf').show(); //整机接管展示安全配置
                    $('input[name="verification"][value="4"]').each(function() {  //整机接管显示接管到无网络环境
                        // 隐藏当前 input 元素的父元素
                        $(this).parent().show();
                    });
                    // $('.takeoverrestoreipview').hide();  //整机接管
                    _isHideSwitchBackIpFlag = true;
                    $('.transfernetworkDiv').show();  //挂载接管不需要指定传输网络

                    break;
                case 2:  //挂载接管
                    $('.takeoverNetworkConf').show();
                    $('.mountTakeoverStandbyConfDes').show(); //挂载接管配置描述信息
                    $('.cmTakeoverStandbyConfDes').hide(); //整机接管配置描述信息

                    $('.cmtakeovermodehelp').hide();
                    $('.mounttakeovermodehelp').show();
                    $('#takeoverSafetyConf').hide();  //隐藏安全配置
                    $('input[name="verification"][value="4"]').each(function() {  //挂载接管隐藏接管到无网络环境
                        // 隐藏当前 input 元素的父元素
                        $(this).parent().hide();
                    });
                    // $('.takeoverrestoreipview').show();
                    _isHideSwitchBackIpFlag = false;
                    $('.transfernetworkDiv').hide();  //挂载接管不需要指定传输网络
                    break;
                default:
                    $('.takeoverNetworkConf').hide();
                    $('.mountTakeoverStandbyConfDes').hide(); //挂载接管配置描述信息
                    $('.cmTakeoverStandbyConfDes').show(); //整机接管配置描述信息
                    break;
            }
        }
    }

    //接管开关控制
    let backupTaskTakeoverSwitch = function () {
        if(!_authInfo['emergencyDR']){  //应急容灾演练授权判断
            $('.backuptakeovertypediv').hide();
            _takeoverModeValue = TAKEOVER_TYPE.MOUNT;   //容灾演练未授权，默认为挂载接管
            takeoverModeChange(_takeoverModeValue);
            //默认展开接管备机配置
        }else{
            $('.backuptakeovertypediv').show();
        }
        if (this.checked) {
            let systemPartitionCheckedInfo = systemPartitionChecked();
            let isSystemDiskFlag = systemPartitionCheckedInfo['isSystemDiskFlag'];
            let systemDiskUuid = systemPartitionCheckedInfo['systemDiskUuid'];
            let checkedDiskNode = systemPartitionCheckedInfo['checkedDiskNode'];
            if(CONF.VENDOR == "sangfor"){  //深信服OEM版本只支持整机接管，需要屏蔽接管平台的选择，对应需求号：#27350
                $('.backuptakeovertypediv').hide();
                if(!isSystemDiskFlag){{ //需要判断数据源是否选择了系统分区，如果备份任务在数据源处未选择系统分区，则不能配置接管
                    UIToastr.showWarning(LANG.UI_CM_TAKEOVER_CONFIGURE, LANG.UI_CM_CDP_SYSTEM_VOL_NO_CHECKED_TAKEOVER_CONF_TIPS_SANGFOR);
                    $('.backuptasktakeoverswitch').bootstrapSwitch('state', false);
                    return false;
                }}
                $('.autotakeoverdiv').hide(); //深信服OEM版本不支持自动接管，对应需求号：#27350
            }

            resetRadio('cm_takeover_mode', TAKEOVER_TYPE.COMPLETE_MACHINE);

            //没有选中系统分区所在磁盘，不能选中内存操作系统作为备机
            // 没得容灾演练平台
            if(!isSystemDiskFlag || !CONF.PERMISSION.includes('vm_machine_manager')){
                resetRadio('cm_takeover_mode', TAKEOVER_TYPE.MOUNT);
            }else{
                let sysVolIsSelected = getSysVolIsSelected(checkedDiskNode,systemDiskUuid);
                if(!sysVolIsSelected){
                    resetRadio('cm_takeover_mode', TAKEOVER_TYPE.MOUNT);
                }
            }

            $('.backuptasktakeoverconfdiv').show();
            $('.beforetakeoverscriptview').show();
            $('.aftetakeoverscriptview').show();
            $('.takeovermonitorscriptview').show();
            let takeoverScanType = CONF.RECOVER_POLICY.DIRECT_TAKE;  //默认直接接管
            let scanStrategy = CONF.INTERRUPT.STOP;  //默认中断
            let options = {os_type: _agentType};
            var takeOver = oldInfo.take_over;
            if (EditFlag && takeOver.auto_takeover_enable_flag && takeOver.config.takeover_type == 1 && takeOver.config.safe.length > 0) {
                var safe = takeOver.config.safe[0];
                if (takeoverScanType != safe.recover_policy) {
                    // 如果不是直接恢复，才会传自定义参数
                    takeoverScanType = safe.recover_policy;
                    scanStrategy = safe.interrupt_policy;
                    for(var kn in safe) {
                        options[kn] = safe[kn];
                    }
                }
            }
            //判断病毒监测是否有授权
            if(CONF.FUNCTIONS.includes('virusKill')){
                $('#takeoverSafetyConf').takeAppOver(
                    'col-md-3',
                    takeoverScanType,
                    scanStrategy,
                    options
                );
            }else{
                $('#takeoverSafetyConf').hide(); //未授权病毒监测，隐藏病毒监测配置div
            }
            if(_backupTaskType == "copy"){
                _createMsgdata.takeover_object.takeover_standby_agent_uuid = _createMsgdata.replication_object.standby_agent_uuid;
                _createMsgdata.takeover_object.takeover_disk_list = _createMsgdata.backup_object.disk_list;
                _createMsgdata.takeover_object.vol_mount_point_set = [];
                _createMsgdata.takeover_object.takeover_vm = null;
                $('.backuptakeovertypediv').hide();  //复制任务隐藏接管方式配置
                //病毒扫描配置
                $('#takeoverSafetyConf').hide(); //在线客户端复制接管隐藏安全配置
               
                
                // bug #27217 需求放开关于回切通讯IP的判断
                // let standbyAgentType = $('#cmBackupTaskCopyStandbySelect option:selected').attr('agent_type');
                // if(standbyAgentType == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){
                //     $('.takeoverrestoreipview').hide();  //隐藏回切通讯IP配置
                // }else{
                //     $('.takeoverrestoreipview').show();  //显示回切通讯IP配置
                // }
            }
            _createMsgdata.takeover_object.takeover_mode =  TAKEOVER_TYPE.COMPLETE_MACHINE;
        } else {
            //未开启接管，需要隐藏接管相关脚本
            $('.beforetakeoverscriptview').hide();
            $('.aftetakeoverscriptview').hide();
            $('.takeovermonitorscriptview').hide();
            $('.backuptasktakeoverconfdiv').hide();
        }
    };

    //应用开关控制，开启应用配置需要校验主机是否选择应用，如果是挂载接管，需要判断选择的备机是否有匹配的应用
    let appTakeoverSwitch = function () {
        if (this.checked) {
            if(_createMsgdata.standby_agent_uuid == "" || _createMsgdata.standby_agent_uuid == undefined){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_CM_CDP_TAKEOVER_STANDBY_NOT_CONF_TIPS);
                $('.apptakeoverswitch').bootstrapSwitch('state', false);
                return false;
            }
            //校验主机是否监控应用
            let backupAgentAppInfo = resultData.host_app_info;
            if(backupAgentAppInfo.length == 0){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING_TIPS2);
                $('.apptakeoverswitch').bootstrapSwitch('state', false);
                return false;
            }
            let standbyAgentType = $('#cmBackupTaskCopyStandbySelect option:selected').attr('agent_type');  //复制任务备机类型
            if((_takeoverModeValue == TAKEOVER_TYPE.COMPLETE_MACHINE  && _backupTaskType == "backup")
                || (_takeoverModeValue == TAKEOVER_TYPE.COMPLETE_MACHINE && standbyAgentType== CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS && _backupTaskType == "copy")){
                let appSet =  _createMsgdata.backup_object.monitor_app_uuid_set;
                let takeoverSet = [];
                for(let i=0;i<appSet.length;i++){
                    let info = {};
                    info.master_app_uuid = appSet[i];
                    info.standby_app_uuid = appSet[i];
                    takeoverSet.push(info);
                }
                _createMsgdata.takeover_object.app_set = takeoverSet;
            }else{
                let info = {};
                info.agent_uuid = _createMsgdata.agentUUID;
                info.app_list = resultData.host_app_info;
                info.standby_host_uuid= _createMsgdata.standby_agent_uuid;
                pAjaxRequest(info, "/api/v1/complete_machine_volcdp/check_standby_app", "post", function (result) {
                    if (result.success) {
                        if(result.data.length>0 && result.data.length ==  resultData.host_app_info.length){  //应用类型，实例名，实例个数匹配情况下
                            _createMsgdata.takeover_object.app_set = result.data;
                        }else{
                            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_CM_CDP_VERIFY_STANDBY_APP_TIPS);
                            $('.apptakeoverswitch').bootstrapSwitch('state', false);
                            _createMsgdata.takeover_object.app_set  = [];
                            return false;
                        }
                    }else{
                        UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_CM_CDP_STANDBY_APP_NO_CONFIG);
                        $('.apptakeoverswitch').bootstrapSwitch('state', false);
                    }
                });
            }

        } else {
            $('.appmonitorswitch').bootstrapSwitch('state', false); //关闭应用故障检测开关
            $('.appmonitorconfigview').hide();
        }
    };

    //整机自动接管开关控制
    var cmAutoTakeoverSwitchFlag = false;
    var cmAutoTakeoverSwitch = function () {
       // if(cmAutoTakeoverSwitchFlag) return;  //避免事件重复健加载
       // cmAutoTakeoverSwitchFlag = true;

        if(!_authInfo['cdpAutoTakeover']){
            UIToastr.showWarning(LANG.UI_PUBLIC_OBTAIN_LICENSE_INFO, LANG.UI_CM_CDP_AUTH_AUTO_TAKEOVER_TIPS);
            $('.cmautotakeoverconfdiv').remove();
            $('.cmautotakeoverswitch').bootstrapSwitch('state', false, true);
            $('.cmautotakeoverswitch').bootstrapSwitch('disabled', true); // 直接禁用开关
           // setTimeout(() => cmAutoTakeoverSwitchFlag = false, 200); // 设置最小间隔时间
            return;
        }

        if (this.checked) {
            $('.cmautotakeoverconfdiv').show();

            $('div[name="autotakeoverconfview"]').addClass('in');
            $('a[name="cmAutoTakeoverCollapse"]').removeClass('collapsed');
            $('div[name="autotakeoverconfview"]').removeAttr('style');

        } else {
            $('.cmautotakeoverconfdiv').hide();
            $('div[name="autotakeoverconfview"]').removeClass('in');
            $('a[name="cmAutoTakeoverCollapse"]').addClass('collapsed');
        }
       // setTimeout(() => cmAutoTakeoverSwitchFlag = false, 200); // 设置最小间隔时间
    };

    //应用故障检测开关控制
    let cmAppMonitorSwitch = function () {
        if (this.checked) {
            if($('.apptakeoverswitch').is(':checked')){
                $('.appmonitorconfigview').show();
            }else{
                $('.appmonitorconfigview').hide();
                $('.appmonitorswitch').bootstrapSwitch('state', false); //关闭应用故障检测开关
                UIToastr.showWarning(LANG.UI_CM_CDP_ENABLE_FAULT_MONITOR_TAKEOVER,LANG.UI_CM_CDP_ENABLE_FAULT_MONITOR_CONF_TIPS);

            }

        } else {
            $('.appmonitorconfigview').hide();
        }
    };

    //配置接管备机
    let cmTakeoverStandbyMapConf = function(e){
        let takeoverModeValue = _takeoverModeValue;
        if(takeoverModeValue== TAKEOVER_TYPE.COMPLETE_MACHINE){  //整机接管
            //判断应急接管是否授权
            let params = {};
            params.type = 'emergency_takeover';
            Metronic.blockUI({target: "#completeMachineVolcdpbackupcontent",animate: true}); 
            pAjaxRequest(params, "/api/v1/complete_machine_volcdp/emergency_takeover_auth_info", "GET", function (d) {
                Metronic.unblockUI("#completeMachineVolcdpbackupcontent");
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
        }else{  //挂载接管
            mountTakeoverStandbyMapConf();
            $('.mountTakeoverStandbyConfDes').show();
            $('.cmTakeoverStandbyConfDes').hide();
            e.preventDefault(); // 阻止默认行为（抽屉打开）
            e.stopPropagation(); // 阻止事件冒泡
        }
       
    }
    /**
     * 加载应急内嵌接管备机配置
     */
    let loadTakeoverStandbyMapConf = function(){ 

        let dataSourceAgentUuid = _agentuuid;
        let treeObj = $.fn.zTree.getZTreeObj(dataSourceAgentUuid+"_ztree_id");
        let dataSourceDiskInfo = treeObj.getNodes();
        let newDataSourceDiskInfo = replaceNocheckInObjectArray(dataSourceDiskInfo,_backupTaskType);
        let targetDiskParams = {
            type: 3, //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管
            origin_task_uuid: "", //源任务uuid(实时,定时可不传或为空)
            origin_agent_uuid: dataSourceAgentUuid, //源代理主机uuid(实时,定时可不传或为空)
            origin_timepoint_uuid: "", //源时间点uuid或时间点(实时,定时)
            target_agent_uuid: dataSourceAgentUuid, //目标代理主机uuid(目标端主机)
            target_visible_flag: true, //控制恢复目标的列是否显示 true显示,false不显示
            recover_type: true, //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
            target_auto_flag: false, //是否显示恢复目标选择框的自动分配选项
            target_input_flag:true, //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
            target_input_min: -1, //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
            origin_data_array:newDataSourceDiskInfo, //前端源信息,当origin_data_flag为true时使用这个数据
            nocheck: true, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
            first_column_name:LANG.UI_CM_CDP_DATA_SOURCE_DISK,
            second_column_name:LANG.UI_PUBLIC_TOTAL_SIZE,
            third_column_name:LANG.UI_VOL_CDP_TAKEOVER_TARGET_DISK,
        };
        //初始化内嵌虚拟主机组件
        $.fn.vmConfig.init({
            agent_uuid: _agentuuid,  // 源机agent_uuid
            task_type: CONF.TASK_TYPE.VOL_CDP_BACKUP,
            node_uuid: _createMsgdata.node_uuid,  // 节点nodeuuid
            node_uuid_list: _createMsgdata.node_uuid_list,
            source_netcard_conf: netWorkInfo.agent_network_info,  // 源机网卡信息
            timepoint_uuid:'',
            disk_config_params:targetDiskParams,
            disk_uuids:_createMsgdata.backup_object.disk_list, //备份任务监控磁盘列表
            os_type:_agentType,
        });
        $('.cmTakeoverStandbyConfDes').show();
        $('.mountTakeoverStandbyConfDes').hide();
        $('#builtInVmSubmit').unbind('click').bind('click',getVmtemplateConfigInfo);
    }

    /**
     * 获取内嵌虚拟主机配置
     */
    let getVmtemplateConfigInfo = function(){
        console.log('getVmtemplateConfigInfo')
        _createMsgdata.takeover_object = {};
        _createMsgdata.takeover_object.agent_failback_standby_ip = ""; //接管回切通讯IP
        _createMsgdata.takeover_object.cross_platform_flag = "";  //挂载接管不需要配置,需要对应键位
        _createMsgdata.takeover_object.cross_platform_conf = []; //挂载接管不需要配置,需要对应键位
        let takeoverModeValue = _takeoverModeValue;
        _createMsgdata.takeover_object.takeover_mode = takeoverModeValue;
        let vmConf =  $.fn.vmConfig.getData();
        let vmConfDes = vmConf.vm_temp_des;
        let vmTempConfig = vmConf.vm_temp_object;
        $('.cmTakeoverStandbyConfDes').html(vmConfDes);
        let vmConfInfo = {};
        vmConfInfo.uuid = vmTempConfig.takeover_vm.uuid;
        vmConfInfo.hypervisor_type = vmTempConfig.takeover_vm.hypervisor_type;
        vmConfInfo.node_uuid = vmTempConfig.takeover_vm.node_uuid;
        vmConfInfo.config = vmTempConfig.takeover_vm.config;
        vmConfInfo.host_reset_name = vmTempConfig.host_reset_name;
        // vmConfInfo.takeover_business_ip_map = vmTempConfig.takeover_business_ip_map;
        _createMsgdata.takeover_object.takeover_vm = vmConfInfo;
        _createMsgdata.takeover_object.takeover_business_ip_map =  vmConf.takeover_business_ip_map;
        _createMsgdata.takeover_object.cross_platform_flag = vmTempConfig.cross_platform_flag;
        _createMsgdata.takeover_object.cross_platform_conf = vmTempConfig.cross_platform_conf;
        _createMsgdata.standby_agent_uuid = vmTempConfig.takeover_vm.uuid;  //将内嵌虚拟机UUID赋值给备机
    }
    /**
     * 挂载接管备机映射配置
     */
    let mountTakeoverStandbyMapConf = function(flag = false){
        if (EditFlag && !flag && oldInfo.take_over.config.takeover_type == 2 && $('#cmBackupTaskTakeoverStandbySelect').val() == oldInfo.take_over.config.takeover_standby_agent_uuid) {
            $(".cmcdptakeovertargetmapdf").hide();
            $(".cmcdptakeovertargetmapconf").show();
            $('#mountTakeoverStandbyConfModal').modal({'width':'800px', 'height':'380px'});
            return;
        }
        var params = {};
        params.master_os_type = _agentType;
        params.master_uuid = _agentuuid;
        Metronic.blockUI({target: ".backuptasktakeoverconfdiv",animate: true});  // 能够为页面上的任意元素添加遮层,阻止用户操作
        pAjaxRequest(params, "/api/v1/complete_machine_volcdp/takeover_target_host", "GET", function (result) {
            Metronic.unblockUI(".backuptasktakeoverconfdiv");
            if (result.success) {
                let cmBackupTaskTakeoverStandbySelect = $('#cmBackupTaskTakeoverStandbySelect');
                cmBackupTaskTakeoverStandbySelect.empty();
                let data = result.data;
                for(var i=0; i<data.length; i++){
                    let standbyAgentName = data[i].standby_agent_name;
                    let standbyAgentuuid = data[i].standby_agent_uuid;
                    if(standbyAgentuuid ==_agentuuid ){
                        continue;
                    }
                    let inTask  = data[i].in_task;
                    let taskName = data[i].task_name;
                    let onlineFlag = data[i].online_flag;
                    let osType = data[i].os_type;
                    var option = $("<option>").text(standbyAgentName).val(standbyAgentuuid).attr("in_task",inTask).attr("task_name",taskName).attr('online_flag',onlineFlag).attr("agent_os_type",osType);
                    cmBackupTaskTakeoverStandbySelect.append(option);
                }
                if (flag && EditFlag && oldInfo.take_over.auto_takeover_enable_flag && oldInfo.take_over.config.takeover_type == 2) {
                    cmBackupTaskTakeoverStandbySelect.val(oldInfo.take_over.config.takeover_standby_agent_uuid);
                    cmBackupTaskStandbySelectChange(1);
                    _createMsgdata.standby_agent_uuid = oldInfo.take_over.config.standby_agent_uuid
                }
            }
        }, false);
        if (!flag) {
            $('#mountTakeoverStandbyConfModal').modal({'width':'800px', 'height':'380px'});
        }

        $(".cmcdptakeovertargetmapdf").show();
        $(".cmcdptakeovertargetmapconf").hide();
    }


    /**
     * 提交接管备机相关配置，校验配置参数
     */
    let submitMountTakeoverStandbyConf = function(){
        var standbyValue = _createMsgdata.standby_agent_uuid;
        if (!standbyValue) {
            if (_backupTaskType == 'backup') {
                standbyValue = $('#cmBackupTaskTakeoverStandbySelect').val();
            } else {
                standbyValue = $('#cmBackupTaskCopyStandbySelect').val();
            }
            _createMsgdata.standby_agent_uuid = standbyValue;
        }

        if(standbyValue == ''){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_STANDBY_CONF_TIPS);
            return false;
        }
        let changeData = $(".cmcdpcopytargetmapconf").getTargetData("getEachData",_agentuuid);
        console.log('submitMountTakeoverStandbyConf')
        _createMsgdata.takeover_object = {};
        _createMsgdata.takeover_object.takeover_mode = _takeoverModeValue;
        let takeoverMountPointSet = [];  //卷挂载映射关系，仅选择挂载接管时可进行配置，不需要区分逻辑卷和普通卷，改为由后端自行区分处理
        // let takeoverDiskList = [];
        let targetStrategy = changeData.target_strategy;
        let mountDevFlag = true;
        for (let i = 0; i < targetStrategy.length; i++){
            let info = {};
            let targetDev = targetStrategy[i].destination_dev_uuid;  //挂载点，可以为空，为空则为默认挂载点
            if(targetDev == "" && targetStrategy[i].destination_dev_node_uuid==2){  //挂载点，可以为空，为空则为默认挂载点
                targetDev = "";
            }else if(targetDev == "" && targetStrategy[i].destination_dev_node_uuid!=2 && targetStrategy[i].destination_dev_node_uuid !=""){
                targetDev = targetStrategy[i].destination_dev_node_uuid;
            }else if(targetDev == 0){
                mountDevFlag = false;
                break;
            }
            info.vol_uuid = targetStrategy[i].source_dev_uuid;  //注:原主机卷uuid，选中的磁盘下的所有卷默认必须接管，不能排除
            info.target_mount_point = targetDev;
            takeoverMountPointSet.push(info);
            // takeoverDiskList.push(targetStrategy[i].targetStrategy);
        }
        if(!mountDevFlag){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_STANDBY_VOL_TARGET_TIPS);
            return false;
        }
        _createMsgdata.takeover_object.vol_mount_point_set = takeoverMountPointSet;
        _createMsgdata.takeover_object.takeover_disk_list = _createMsgdata.backup_object.disk_list;  //默认为备份对象磁盘，当前不支持修改
        _createMsgdata.takeover_object.takeover_standby_agent_uuid = standbyValue;

        cmMountTakeoverStandbyConfDes(targetStrategy);
        $('#mountTakeoverStandbyConfModal').modal('hide');

        let dataSourceHostTitle = LANG.UI_PUBLIC_HOST + ":" + _createMsgdata.agetName;
        let standbyHostTitle = LANG.UI_PUBLIC_STANDBY_HOST + ":" +  $('#cmBackupTaskTakeoverStandbySelect option:selected').text();
        let takeoverConfigTitle = "<span>"+ dataSourceHostTitle+ "</span><span class = 'ml10'>"+ standbyHostTitle +"</span>";
        $('.takeoverStandbyDes').html(takeoverConfigTitle);
    }

    /**
     * 获取挂载接管主备映射配置信息
     * @param {} standbyAgentName
     * @param {*} standbyDevRelationSet
     */
    var cmMountTakeoverStandbyConfDes = function(standbyDevRelationSet){
        let standbyAgentName = $('#cmBackupTaskTakeoverStandbySelect option:selected').text();
        $('.mountTakeoverStandbyConfDes').html('');
        let isBr = '<div>';
        for(var i=1;i<standbyDevRelationSet.length;i++){
            isBr += "<br>";
        }
        let devStr = "";
        for(var i= 0; i < standbyDevRelationSet.length; i++){
            let sourceDevName = standbyDevRelationSet[i].source_name;
            let targetDevName = standbyDevRelationSet[i].destination_name;

            var desTitle =  sourceDevName + " --> " + targetDevName;
            devStr += "<span class ='ml10' title = '"+desTitle+"' >" + desTitle +"</span><br>";

        }
        var des = '<li style="margin-top:15px;" class="list-group-item popovers standbyConfTips" id="cmMountTakeoverConfDesTips" '
            +'data-container="body" data-trigger="hover" data-placement="top" data-html="true" >'
            +'<div class="col1"><div class="cont ">'
            +'<div class="cont-col1"></div><div class="cont-col2">'
            +'<div class="desc list-one" id = "vmDetailConfigInfo" style="width: 100%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
            +'<sapn>'+LANG.UI_CM_CDP_DATA_SOURCE_HOST + '</sapn>:<span class ="ml10" title = "">'+ _createMsgdata.agetName +'</span><br>'
            +'<sapn>'+LANG.UI_CM_CDP_TARGET_STANDBY_TIPS + '</sapn>:<span class ="ml10">'+ standbyAgentName +'</span><br>'
            +'<div><div style="float: left">'
            +'<sapn>'+ LANG.UI_CM_DISK_MAP + '</sapn>:</div> <div style="float: left">' + devStr + '</div>'+'</div><br>'
            +'<div></div><br>'
            +'</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;">'
            +'<a class="cmMountTakeoverConfDesDel" >'
            +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';

        $('.mountTakeoverStandbyConfDes').append(des);
        $('.vmConfTips').popover();	   //初始化tips
        $('.cmMountTakeoverConfDesDel').bind("click",delCmMountStandbyConfDescription);
    }
    /**
     * 删除挂载接管备机配置描述信息
     */
    let delCmMountStandbyConfDescription = function(){
        $('.popover.in').remove();
        $('#cmMountTakeoverConfDesTips').remove();
        $('.mountTakeoverStandbyConfDes').html('');
        console.log('delCmMountStandbyConfDescription')
        _createMsgdata.takeover_object = {};
        $('.takeoverStandbyDes').html('');
        $('#takeover_ip_map_list').html('');
        _createMsgdata.standby_agent_uuid = "";
        $('.apptakeoverswitch').bootstrapSwitch('state', false);  //重置应用配置

    }
    /**深拷贝数据处理 */
    let deepCopy = function(obj) {
        if (obj === null || typeof obj !== 'object') {
            return obj;
        }

        if (Array.isArray(obj)) {
            return obj.map(deepCopy);
        }

        let copy = {};
        for (let key in obj) {
            if (obj.hasOwnProperty(key)) {
                copy[key] = deepCopy(obj[key]);
            }
        }
        return copy;
    };
    /**
     * 替换和过滤数据源相关设备信息
     */
    let replaceNocheckInObjectArray = function(arr,_backupTaskType){
        let devInfo =  _createMsgdata.backup_object.excluding_devices;
        let newArr = deepCopy(arr);  // 深拷贝数组
        let processArray = function(array) {
            array.forEach(function(item) {
                if (typeof item === 'object' && item !== null) {
                    if (item.hasOwnProperty('nocheck')) {
                        item.nocheck = true;
                        item.name = item.dev_name;
                    }
                    if (item.hasOwnProperty('open') && _backupTaskType== "copy") {
                        item.open = false;
                        item.name = item.dev_name;
                    }
                    if (Array.isArray(item.children)) {
                        processArray(item.children, true);
                    }
                }

            });
        }
        processArray(newArr);

        var idsToRemove = $.map(devInfo, function(item) {
            return [item.dev_node_uuid];
        });
        // newArr.filter(function(item) {
        //     return !idsToRemove.includes(item.dev_node_uuid);
        // });

        let filterArray = filterNodes(newArr)
        function filterNodes(arr) {
            return $.grep(arr, function(node) {
                if (node.children) {
                    node.children = filterNodes(node.children);
                }
                return !(node.dev_node_uuid && $.inArray(node.dev_node_uuid, idsToRemove) !== -1);
            });
        }

        return filterArray;
    }
    /**
     * 获取接管目标主机配置
     */
    let getTakeoverTargetConfig = (flag) => {
        let dataSourceAgentUuid = _agentuuid;
        let treeObj = $.fn.zTree.getZTreeObj(dataSourceAgentUuid+"_ztree_id");
        let dataSourceDiskInfo = treeObj.getNodes();
        let newDataSourceDiskInfo = replaceNocheckInObjectArray(dataSourceDiskInfo,_backupTaskType);


        let targetAgentUuid = $('#cmBackupTaskTakeoverStandbySelect').val();
        let targetOsType = $("#cmBackupTaskTakeoverStandbySelect").find("option:selected").attr("agent_os_type"); //目标设备系统类型
        // Metronic.blockUI({target: "#mountTakeoverStandbyConfModal",animate: true});   //未生效
        var options = {
            type: 3, //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管
            origin_task_uuid: "", //源任务uuid(实时,定时可不传或为空)
            target_os_type:targetOsType, //目标机操作系统类型 windows还是linux，只有type为3和6在使用,其他type可不传
            origin_agent_uuid: dataSourceAgentUuid, //源代理主机uuid(实时,定时可不传或为空)
            origin_timepoint_uuid: "", //源时间点uuid或时间点(实时,定时)
            target_agent_uuid: targetAgentUuid, //目标代理主机uuid(目标端主机)
            target_visible_flag: true, //控制恢复目标的列是否显示 true显示,false不显示
            recover_type: false, //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
            target_auto_flag: true, //是否显示恢复目标选择框的自动分配选项
            target_input_flag:true, //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
            target_input_min: 1, //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
            origin_data_array:newDataSourceDiskInfo, //前端源信息,当origin_data_flag为true时使用这个数据
            nocheck: true, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
            loading:'#mountTakeoverStandbyConfModal',
            first_column_name:LANG.UI_CM_CDP_SOURCE_DISK_NAME,
            second_column_name:LANG.UI_PUBLIC_TOTAL_SIZE,
            third_column_name:LANG.UI_CM_DISK_OR_VOL_MAP,
        };
        options.callback = function (){
            if (flag == 1) {
                // 为整机的初始化
                initMachineConfig();
            }
        }
        $('.cmcdptakeovertargetmapconf').initTargetPlug(options);
        // Metronic.unblockUI("#mountTakeoverStandbyConfModal");
    }

    /**
     * 获取复制主机与复制备机相关磁盘映射信息
     */
    let getCopyTargetConfig = (flag) => {
        if (EditFlag && flag != 0) {
            Metronic.blockUI({
                target: "#submit_form",
                animate: true
            });
        }
        let dataSourceAgentUuid = _agentuuid;
        let treeObj = $.fn.zTree.getZTreeObj(dataSourceAgentUuid+"_ztree_id");
        let dataSourceDiskInfo = treeObj.getNodes();

        let newDataSourceDiskInfo = replaceNocheckInObjectArray(dataSourceDiskInfo,_backupTaskType);

        let targetAgentUuid = $('#cmBackupTaskCopyStandbySelect').val();
        let targetOsType = $('#cmBackupTaskCopyStandbySelect').find("option:selected").attr("agent_os_type"); //目标设备系统类型
        var options = {
            type: 4, //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时复制, 5为实时整机接管,6为实时挂机接管
            origin_task_uuid: '', //源任务uuid(实时,定时可不传或为空)
            target_os_type:targetOsType, //目标机操作系统类型 windows还是linux，只有type为3和6在使用,其他type可不传
            origin_agent_uuid: dataSourceAgentUuid, //源代理主机uuid(实时,定时可不传或为空)
            origin_timepoint_uuid: '', //源时间点uuid或时间点(实时,定时)
            target_agent_uuid: targetAgentUuid, //目标代理主机uuid(目标端主机)
            target_visible_flag: true, //控制恢复目标的列是否显示 true显示,false不显示
            recover_type: true, //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
            target_auto_flag: false, //是否显示恢复目标选择框的自动分配选项
            target_input_flag:true, //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
            target_input_min: -1, //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
            origin_data_array:newDataSourceDiskInfo, //前端源信息,当origin_data_flag为true时使用这个数据
            nocheck: true, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
            first_column_name:LANG.UI_CM_CDP_DATA_SOURCE_DISK,
            second_column_name:LANG.UI_PUBLIC_TOTAL_SIZE,
            third_column_name:LANG.UI_VOL_CDP_TAKEOVER_TARGET_DISK,
        };
        if (EditFlag) {
            options.origin_task_uuid = oldInfo.job_uuid;
            if (oldInfo.take_over.config.standby_agent_list != undefined) {
                options.init_selected = oldInfo.take_over.config.standby_agent_list;
            }
        }
        options.callback = function (){
            Metronic.unblockUI("#copyStandbyConfModal");
            $('div[name="copyTaskStandbyMapConfCollapse"]').addClass('in');
            $('a[name="strategystandbymap"]').removeClass('collapsed');
            $('div[name="copyTaskStandbyMapConfCollapse"]').removeAttr('style');
            if (EditFlag && flag != 0) {
                Metronic.unblockUI("#submit_form");
                let newDataSourceDiskInfo = [];
                // 获取所有节点
                let allNodes = treeObj.transformToArray(treeObj.getNodes());
                // 遍历所有节点的复选框
                allNodes.forEach(function(node) {
                    if (node.checked) {
                        newDataSourceDiskInfo.push(node.id);
                    }
                });
                if (arraysDeepEqual(newDataSourceDiskInfo, oldDataSourceDiskInfo)) {
                    // 未修改备份磁盘才获取回填磁盘映射
                    submitCopyTaskStandbyConf(flag);
                }
            }
        };
        $('.cmcdpcopytargetmapconf').initTargetPlug(options);
    }

    /*=====================================================================================高级配置相关 =============================================================*/
    let initListenerAdvancedConf = function(){
        //监听高级配置相关事件
        fileCachePathDefault();
    }

    /**
     * 内存缓存切换
     */
    let memoryCacheChange =function(){
        if (this.checked) {
            $('.set_memory_cache_div').show();
        } else {
            $('.set_memory_cache_div').hide();
        }
    }
    //文件緩存默认路径
    let fileCachePathDefault = function(){
        var fileCacheDefaultPath = LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT;
        if(_agentDefaultCachePath!=""){
            fileCacheDefaultPath = _agentDefaultCachePath;
        }
        $('#file_cache_path').val(fileCacheDefaultPath);
        fileCachePathChange();  //切换文件缓存路径方式
    }

    //切换文件缓存路径方式
    let fileCachePathChange = function(){

        $('#customFileCachePath').click(function(){
            $('#defaultFileCachePath').show();
            $('#customFileCachePath').hide();
            $('#file_cache_path').removeAttr("readonly");
            $('#file_cache_path').val("");
        });

        $('#defaultFileCachePath').click(function(){
            $('#defaultFileCachePath').hide();
            $('#customFileCachePath').show();
            $('#file_cache_path').attr("readonly","readonly");
            if(_agentDefaultCachePath!=""){
                fileCacheDefaultPath = _agentDefaultCachePath;
            }
            $('#file_cache_path').val(fileCacheDefaultPath);
        });
    }

    /**
     * 获取第一步配置信息，并校验数据正确性
     * @returns
     */
    let step1Valid = function(){
        if (!isLicense) {
            // 授权失效。不允许下一步
            UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE,  LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
            return false;
        }
        if(!_createMsgdata.agentUUID){  //判断是否选择客户端
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT_SELECT, LANG.UI_VOL_CDP_BACKUP_CLIENT_SELECT_TIPS);
            return false;
        }

        backupCheckedData = $("#checkedHostDiskSourceDiv").methodVinBackupSource('getCheckedData');
        backupinfo = $("#checkedHostDiskSourceDiv").methodVinBackupSource('getData');

        let checkedDisk = [];
        let excludingDevices = [];
        for(var i=0;i<backupinfo.length;i++){
            if(backupinfo[i].agent_uuid == _createMsgdata.agentUUID){
                checkedDisk = backupinfo[i].disk_list;
                excludingDevices =  backupinfo[i].excluding_devices;
                break;
            }
        }
        resultData.backup_object_info = []; //获取第一步消息内容
        resultData.backup_object_info.push(excludingDevices);
        if(checkedDisk.length == 0){
            UIToastr.showWarning(LANG.UI_BR_RECOVERY_SELECT_DATA, LANG.UI_CM_CDP_CHANGE_BACKUP_DATA_TIPS);
            return false;
        }
        let diskInfo = checkedDisk;
        let diskList = [];
        let diskDisplayNameList = [];
        for(let i=0;i<diskInfo.length;i++){
            diskList.push(diskInfo[i].dev_uuid);
            diskDisplayNameList.push(diskInfo[i].dev_name);
        }

        //获取以选择的应用信息
        resultData.host_app_info = [];//置空配置应用uuid


        var appTree = $.fn.zTree.getZTreeObj("cm_volcdp_app_tree");
        checkedNodes = appTree.getCheckedNodes(true);

        var confAppInfo = [];

        if(checkedNodes.length>0){
            for(var i=0;i<checkedNodes.length;i++){
                let info = {};
                if(checkedNodes[i].isApp==true){
                    info.app_name = checkedNodes[i].name;
                    info.app_uuid = checkedNodes[i].uuid;
                    info.app_type = checkedNodes[i].app_type_value;
                    resultData.host_app_info.push(info);
                    confAppInfo.push(checkedNodes[i].uuid);
                }
            }
        }
        getAgentAllDsikList(_createMsgdata.agentUUID);  //获取选中客户端agent所有磁盘信息
        _createMsgdata.backup_object.selected_backup_data = backupCheckedData;
        _createMsgdata.backup_object.monitor_app_uuid_set = confAppInfo;
        _createMsgdata.backup_object.monitor_app_uuid_set_info = resultData.host_app_info;
        _createMsgdata.backup_object.disk_list = diskList;
        _createMsgdata.backup_object.disk_display_info = diskDisplayNameList;
        _createMsgdata.backup_object.excluding_devices = excludingDevices;
        showStep2();
    }
    /**
     * 获取选中客户端所有磁盘信息列表
     * @param agentUUID
     * @return diskList
     */
    let getAgentAllDsikList = function(agentUUID){
        let info = {};
        info.agent_uuid = agentUUID;
        info.original_flag = true;
        pAjaxRequest(info, "/api/v1/complete_machine_os/disk_tree", "GET", function (result) {
            if (result.success) {
                _allDiskList = result.data;
            }
        }, true);
    }

    //校验第二步的输入参数
    let step2Valid = function(){
        let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
        if(backupTargetInfo){
            let validateSelect = $('#backupTarget').backupTarget('validateSelect');
            if(!validateSelect && backupTargetInfo){
                initResourceLimit(backupTargetInfo.node_uuid_list);
            }
        }
        let nodeUuidList = backupTargetInfo.node_uuid_list;
        let nodeUuid = backupTargetInfo.node_uuid;  //目标节点uuid
        let storageUuid = backupTargetInfo.storage_uuid;    //目标存储uuid
        let storagePoolUuid = backupTargetInfo.storage_pool_uuid;
        let nodePoolUuid = backupTargetInfo.node_pool_uuid;
        _createMsgdata.backup_target_info = backupTargetInfo; //目标节点信息


        if(!storageUuid && !storagePoolUuid){
            // UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_BACKUP_SELECT_STORAGE);
            verifyResult =  false;
            return false;
        }

        if(_backupTaskType =="copy"){  //复制
            let replicationObject = _createMsgdata.replication_object;
            if(replicationObject.standby_agent_uuid =="" || replicationObject.standby_agent_uuid == undefined){
                UIToastr.showWarning(LANG.UI_CM_CDP_COPY_STANDBY_CONF, LANG.UI_CM_CDP_COPY_STANDBY_CONF_TIPS);
                return false;
            }
            if($('.backupdatatoserver').is(':checked')){  //数据备份
                _createMsgdata.replication_object.mirror_backup_flag = CONF.FLAG.SET;
            }else{
                _createMsgdata.replication_object.mirror_backup_flag = CONF.FLAG.UNSET;
            }
        }else{
            _createMsgdata.replication_object = null;
        }
        _createMsgdata.node_uuid_list = nodeUuidList;
        _createMsgdata.node_uuid = nodeUuid;  //封装目标节点uuid
        _createMsgdata.storage_uuid = storageUuid;  //封装目标存储uuid
        _createMsgdata.node_pool_uuid = nodePoolUuid;
        _createMsgdata.storage_pool_uuid = storagePoolUuid;
        if(!nodeUuid){
            networkFlag = false;
        }else if(_netModel == 2 && nodeUuid){
            networkFlag = true;
        }
        //选择对象不是资源池且任务类型不是复制(多客户端对象)时初始化传输网络
        $('.transfernetworkDiv').hide();
        if(nodePoolUuid=="" && storagePoolUuid=="" &&  _backupTaskType!="copy"){
            if(EditFlag){
                $('#transferNetworkTree').transferNetwork({node_uuid: nodeUuid,
                    network_uuid: oldInfo.transfer_strategy.network,
                    network_pool_uuid: oldInfo.transfer_strategy.network_pool_uuid,
                });
            }else{
                $('#transferNetworkTree').transferNetwork({node_uuid: nodeUuid}); 	//初始化节点传输网络列表
            }

            $('.transfernetworkDiv').show();
        }

        // if(networkFlag && nodeUuid!="" || nodeUuidList.length==1){   //初始化传输网络, 有确定节点或者存储资源池对应的计算节点唯一时
        // 	$('.transfernetworkDiv').show();
        // 	// initNetworkList();
        // }else{
        // 	$('.transfernetworkDiv').hide();
        // }
        resetStep3Conf();
        initListenerAdvancedConf();
        initVinScriptListeners();  //脚本相关监听事件
        // switchBackIpConfView();  // bug #27217 需求放开关于回切通讯IP的判断

        // 回填配置
        afterStep3();
    }

    let afterStep3 = function (){
        if (EditFlag) {
            // 1 --回填通用策略--
            // 标签策略--
            tagList = [];
            $('#tagpoint_list').html('');
            for (var k in oldInfo.label_strategy) {
                var strategy = {
                    mode: oldInfo.label_strategy[k].mode, //定义为标签点策略
                    type: oldInfo.label_strategy[k].strategy_type,
                    days: oldInfo.label_strategy[k].day,
                    startTime: oldInfo.label_strategy[k].start_time,
                    rollFlag: oldInfo.label_strategy[k].roll_flag,
                    endTime: oldInfo.label_strategy[k].roll_end_time,
                    rollInterval: oldInfo.label_strategy[k].roll_interval,
                    des: getLabelDesc(oldInfo.label_strategy[k])
                };
                tagPointStrategySubmit({tagInfo: strategy});
            }
            // 限速策略--
            // 初始化设置策略并且携带初始数据
            let speedStrategy = oldInfo.speed_strategy;
            $('#speedtypeselect').val(speedStrategy.type).change();
            if (speedStrategy.type == 1) {
                // 全局
                $('.strategy-table #table').bootstrapTable('checkBy', {
                    field: 'uuid',
                    values: [speedStrategy.uuid]
                });
                $('#tasklevelselect').val(speedStrategy.level);
            } else {
                addGlobalStrategy.init({'strategy_type':speedStrategy.strategy_type,speedInfo:speedStrategy.speedInfo,initSpeedFlag:1});
            }
            // 存储策略--
            // 压缩存储
            $('#compressCheck').bootstrapSwitch('state', oldInfo.storage_strategy.compress);
            // 数据加密
            $('#encryptStorageCheck').bootstrapSwitch('state', oldInfo.storage_strategy.encrypt);
            if (oldInfo.storage_strategy.encrypt) {
                // 自动生成密码
                $('#passwordAutocheck').bootstrapSwitch('state', oldInfo.storage_strategy.password_auto_flag);
                if (!oldInfo.storage_strategy.password_auto_flag) {
                    // 不是自动生成的，需要回填密码
                    // 需要注意，这个密码是进行了base64的，提交的时候要处理下
                    $('#password').val(oldInfo.storage_strategy.password);
                    $('#repassword').val(oldInfo.storage_strategy.password);
                }
            }
            // 保留策略--
            $('#spinnerDayInput').val(oldInfo.reserve_strategy.number);
            initReserveStrategyDes();
            // 2 --回填传输策略--
            // 传输网络
            if (oldInfo.transfer_strategy.network != '' || oldInfo.transfer_strategy.network_pool_uuid != '') {
                // 有选择具体的传输网络或网络池，那么就是自定义选择
                $('#transferNetworkTree-transfer-network-mode-select').val(2).change();
                // 回填网络在 初始化节点传输网络列表 上面处理了
            } else {
                $('#transferNetworkTree-transfer-network-mode-select').val(1);
            }
            // 加密传输
            $('#encrypttransfer').bootstrapSwitch('state', oldInfo.transfer_strategy.encrypt);
            // 源端压缩
            $('#tran_compress_switch').bootstrapSwitch('state', oldInfo.transfer_strategy.original_compress_flag);
            // 手动触发 change 事件，通知监听者
            $('#tran_compress_switch').trigger('switchChange.bootstrapSwitch', [oldInfo.transfer_strategy.original_compress_flag]);
            if (oldInfo.transfer_strategy.original_compress_flag) {
                // 压缩等级
                $('#transferCompressGrade').val(oldInfo.transfer_strategy.original_compress_method);
            }

            // 传输线程个数
            $('#transfer_thread_number').val(oldInfo.transfer_strategy.thread_num);
            // 传输数据包大小
            $('#transfer_datapackage_size').val(oldInfo.transfer_strategy.block_size);
            // 3 --应急接管--
            $('.backuptasktakeoverswitch').bootstrapSwitch('state', oldInfo.take_over.auto_takeover_enable_flag);
            $('.backuptasktakeoverswitch').trigger('switchChange.bootstrapSwitch', [oldInfo.take_over.auto_takeover_enable_flag]);
            var config = oldInfo.take_over.config; // 接管配置
            if (_backupTaskType == 'backup') {
                // 接管方式
                resetRadio('cm_takeover_mode', config.takeover_type);
            }

            // 接管主备映射配置
            if (config.takeover_type == 1) {
                // 接管方式为容灾演练平台的时候的初始化
                initVmConfig();
            }
            // 接管ip
            if (config.agent_failback_standby_ip != '') {
                $('.takeoverfailbackupip').val(config.agent_failback_standby_ip);
            }
            // 应用接管
            $('.apptakeoverswitch').bootstrapSwitch('state', config.app_flag);
            // 自动接管
            $('.cmautotakeoverswitch').bootstrapSwitch('state', config.auto_flag);
            $('.cmautotakeoverswitch').trigger('switchChange.bootstrapSwitch', [config.auto_flag]);
            if (config.auto_flag) {
                // 心跳失效时间
                $('#agent_heartbeat_failure_time').val(config.agent_heartbeat_failure_time);
            }

            // 应用故障监测
            $('.appmonitorswitch').bootstrapSwitch('state', config.agent_heartbeat_failure_flag);
            $('.appmonitorswitch').trigger('switchChange.bootstrapSwitch', [config.agent_heartbeat_failure_flag]);
            if (config.agent_heartbeat_failure_flag) {
                // 开启了才给初始化重新赋值
                // 连续故障次数
                $('#app_failure_number').val(config.app_consecutive_failure_num);
                // 故障监测间隔
                $('#takeover_app_interval').val(config.app_fault_detection_interval);
            }
            // 4 --脚本配置--
            // 上面初始化的时候处理了
            // 5 --高级策略--
            // 公共配置
            // 存储与数据保护功能配置
            // 存储数据块大小
            $('#storage_block_size').val(oldInfo.high_config.block_size_storge);
            // IO复制模式
            resetRadio('io_replication_modle', oldInfo.high_config.io_mode);
            // 有效数据备份
            $('#validDataBackupSwitch').bootstrapSwitch('state', oldInfo.high_config.valid_flag);
            // 跳过坏块备份
            $('#skippingBadBlocksBackupSwitch').bootstrapSwitch('state', oldInfo.high_config.skip_bad_block_flag);
            // 断点续传
            $('#automatic_fault_recovery_switch').bootstrapSwitch('state', oldInfo.high_config.continue_flag);
            // 缓存配置
            // 客户端缓存资源配置
            // 客户端文件缓存路径
            $('#file_cache_path').val(oldInfo.cache_config.agent_cache_file_storage_path);
            // 客户端文件缓存大小
            $('#file_cache_size').val(oldInfo.cache_config.agent_file_cache_alloc_space);
            // 内存缓存
            $('#memory_cache_switch').bootstrapSwitch('state', oldInfo.cache_config.agent_memory_cache_flag);
            if (oldInfo.cache_config.agent_memory_cache_flag) {
                // 客户端内存缓存大小
                $('#memory_cache_size').val(oldInfo.cache_config.agent_memory_cache_alloc_space);
            }
            // 还需判断下已经使用了的缓存大小，改变的时候，当前的
            // ... 内存已使用 agent_memory_cache_used_space 文件已使用 agent_file_cache_used_space
            // 资源监测
            // 客户端资源监测配置
            // 停止任务内存阈值
            let options = {
                osType:_agentType, data: oldInfo
            };
            if (_agentType != 'Linux') {
                options.windowsMemory = _createMsgdata.agent_hardware_info.memory_size;
            }
            ResourceMonitoring.init(options);
        } else {
            let options = {
                osType:_agentType
            };
            if (_agentType != 'Linux') {
                options.windowsMemory = _createMsgdata.agent_hardware_info.memory_size;
            }
            ResourceMonitoring.init(options);
        }
    }

    // 初始化容灾演练平台
    let initVmConfig = function (){
        console.log('initVmConfig')
        _createMsgdata.takeover_object = {};
        _createMsgdata.takeover_object.agent_failback_standby_ip = ""; //接管回切通讯IP
        _createMsgdata.takeover_object.cross_platform_flag = "";  //挂载接管不需要配置,需要对应键位
        _createMsgdata.takeover_object.cross_platform_conf = []; //挂载接管不需要配置,需要对应键位
        _createMsgdata.takeover_object.takeover_mode = 1;
        let vmConf = {
            vm_temp_object: {
                takeover_vm: {
                    config: {
                        os: {}
                    }
                }
            },
            takeover_business_ip_map: [],
        };
        var config = oldInfo.take_over.config;
        // 主机名称
        vmConf.vm_temp_object.takeover_vm.config.vm_name = config.vm_config.vm_name;
        // 重置主机名
        vmConf.vm_temp_object.host_reset_name = config.host_reset_name;
        // 插槽数
        vmConf.vm_temp_object.takeover_vm.config.cpu_slot_num = parseInt(config.vm_config.cpu_slot_num);
        // 每个插槽核心数
        vmConf.vm_temp_object.takeover_vm.config.cpu_slot_core_num = parseInt(config.vm_config.cpu_slot_core_num);
        // CPU类型
        vmConf.vm_temp_object.takeover_vm.config.cpu_mode = parseInt(config.vm_config.cpu_mode);
        // 内存
        vmConf.vm_temp_object.takeover_vm.config.memoryMB = parseInt(config.vm_config.memoryMB);
        // 磁盘配置
        vmConf.vm_temp_object.takeover_vm.config.disks = config.vm_config.disks;
        // 网络配置
        vmConf.vm_temp_object.takeover_vm.uuid = config.vm_config.vm_uuid;
        vmConf.vm_temp_object.takeover_vm.config.vm_uuid = config.vm_config.vm_uuid;
        vmConf.vm_temp_object.takeover_vm.config.interfaces = config.vm_config.interfaces;
        vmConf.vm_temp_object.takeover_vm.config.interface_model_type = 1;
        vmConf.vm_temp_object.takeover_vm.config.disk_target_bus = 1;
        vmConf.vm_temp_object.takeover_vm.config.floppys = [];
        vmConf.vm_temp_object.takeover_vm.config.cdroms = [];
        vmConf.vm_temp_object.takeover_vm.config.hostdevs = [];

        // 系统固件类型
        vmConf.vm_temp_object.takeover_vm.config.os.firmware_type = parseInt(config.vm_config.os.firmware_type);
        // 最大开机等待时间
        vmConf.vm_temp_object.takeover_vm.config.max_boot_wait_time = parseInt(config.vm_config.max_boot_wait_time);
        let diskConfigStr = '';
        var disks = config.vm_config.disks;
        let hardDiskBusType = ['ide','virtio','sata','scsi'];
        for(let j =0;j<disks.length;j++){
            diskConfigStr += disks[j].dev_name + '->' +hardDiskBusType[parseInt(disks[j].target_bus)-1] + ',';
        }
        vmConf.vm_temp_object.disk_config_str = diskConfigStr;
        // 驱动检测结果
        vmConf.vm_temp_object.cross_platform_flag = oldInfo.take_over.cross_platform_flag;
        vmConf.vm_temp_object.cross_platform_conf = oldInfo.take_over.cross_platform_conf;

        let networkConfig = config.vm_config.interfaces;
        let takeOvernetworkConfig = config.network.takeover_business_ip_map;
        for(var i =0;i<networkConfig.length;i++){
            let nic_info = {};
            nic_info.nic_gateway = networkConfig[i].gateway_address;
            nic_info.nic_mac = networkConfig[i].nic_mac;
            nic_info.nic_name = networkConfig[i].nic_name;
            nic_info.nic_ip_info = [];
            var target = takeOvernetworkConfig[i];
            // 源网卡
            let source_ip_set = networkConfig[i].ip_set
            for(var n=0;n<source_ip_set.length;n++){
                let nic_ip_info = {};
                nic_ip_info.ip = source_ip_set[n].ip_addr;
                nic_ip_info.netmask = source_ip_set[n].netmask;
                if (target !== undefined && target.nic_ip_info !== undefined && target.nic_ip_info[n] !== undefined) {
                    nic_ip_info.target_gateway = target.nic_ip_info[n].target_gateway;
                    nic_ip_info.target_nic_name = target.nic_ip_info[n].target_nic_name;
                    nic_ip_info.target_nic_mac = target.nic_ip_info[n].target_nic_mac;
                } else {
                    nic_ip_info.target_gateway = '';
                    nic_ip_info.target_nic_name = '';
                    nic_ip_info.target_nic_mac = '';
                }

                nic_info.nic_ip_info.push(nic_ip_info);
            }
            vmConf.takeover_business_ip_map.push(nic_info);
        }

        let vmConfDes = getVmconfigDesc();
        let vmTempConfig = vmConf.vm_temp_object;
        $('.cmTakeoverStandbyConfDes').html(vmConfDes);
        let vmConfInfo = {};
        vmConfInfo.uuid = vmTempConfig.takeover_vm.uuid;
        vmConfInfo.hypervisor_type = config.vm_config.takeover_vm_hypervisor;
        vmConfInfo.node_uuid = config.vm_config.takeover_vm_node_uuid;
        vmConfInfo.config = vmTempConfig.takeover_vm.config;
        vmConfInfo.host_reset_name = vmTempConfig.host_reset_name;
        // vmConfInfo.takeover_business_ip_map = vmTempConfig.takeover_business_ip_map;
        _createMsgdata.takeover_object.takeover_vm = vmConfInfo;
        _createMsgdata.takeover_object.takeover_business_ip_map = vmConf.takeover_business_ip_map;
        _createMsgdata.takeover_object.cross_platform_flag = vmTempConfig.cross_platform_flag;
        _createMsgdata.takeover_object.cross_platform_conf = vmTempConfig.cross_platform_conf;
        _createMsgdata.standby_agent_uuid = vmTempConfig.takeover_vm.uuid;  //将内嵌虚拟机UUID赋值给备机
    }
    // 获取容灾对于的描述
    let getVmconfigDesc = function (){
        let config = oldInfo.take_over.config;
        var modalVmNameStr = config.vm_config.vm_name;
        // 插槽数
        let slotsnumStr = config.vm_config.cpu_slot_num;
        let cpuSlotNum = config.vm_config.cpu_slot_core_num;

        // 每个插槽核心数
        let slotcorenumStr = cpuSlotNum + LANG.UI_VM_MACHINE_CPU_UNIT;
        // CPU类型
        let cputypeStr = LANG.UI_PUBLIC_DEFAULT;
        let data = CONF.CPU_MODE;
        let cpu_mode = parseInt(config.vm_config.cpu_mode);
        if (cpu_mode !== 0) {
            cputypeStr = data[cpu_mode];
        }
        // 内存
        let memoryStr = parseInt(config.vm_config.memoryMB);
        memoryStr = (memoryStr / 1024) + 'GB';

        // 系统固件类型
        let modalBootFirmwareStr = parseInt(config.vm_config.os.firmware_type);
        if (modalBootFirmwareStr == 1) {
            modalBootFirmwareStr = 'BIOS';
        } else {
            modalBootFirmwareStr = 'UEFI';
        }
        // 最大开机等待时间
        let maxWaitTimeStr = parseInt(config.vm_config.max_boot_wait_time) + LANG.UI_MICROSOFT365_MINUTE;
        // 磁盘配置
        let hardDiskBusType = ['ide','virtio','sata','scsi'];
        let diskConfigStr = '';
        let disks = config.vm_config.disks;
        for(let j =0;j<disks.length;j++){
            diskConfigStr += disks[j].dev_name + '->' +hardDiskBusType[parseInt(disks[j].target_bus)-1] + ',';
        }
        // 重置主机名
        let rehostname = config.host_reset_name;
        // 网络配置
        let netConfigStr = '';
        let network = config.vm_config.interfaces;
        for(var i =0;i<network.length;i++){
            var type = 1;
            let num = i+1;
            var network_name = '';
            for(var k in network[i].source_network_set) {
                network_name = network[i].source_network_set[k];
            }
            netConfigStr += '<ol class="plr15 pt-2">' + LANG.UI_VM_SETTING_V2_NETWORK + num + '：' + network[i].name + '->' + network_name + '</ol>';
            var ip_set = network[i].DNS1 != '' ? LANG.UI_PUBLIC_IP_DNS1 + ':' + network[i].DNS1 +';  ': '';
            ip_set += network[i].DNS2 != '' ? LANG.UI_PUBLIC_IP_DNS2 + ':' + network[i].DNS2 +';  ': '';
            ip_set += network[i].gateway_address != '' ? LANG.UI_PUBLIC_IP_GATEWAY + ':' + network[i].gateway_address +';  ': '';
            for(var j in network[i].ip_set) {
                var ip_sets = network[i].ip_set[j];
                ip_set += LANG.UI_PUBLIC_IP_ADDRESS + ':' + ip_sets.ip_addr;
                ip_set += LANG.UI_PUBLIC_IP_NETMASK + ':' + ip_sets.netmask;
                type = ip_sets.ip_type;
            }
            var titles = LANG.UI_PUBLIC_IPV6_CONFIG;
            if (type == 1) {
                titles = LANG.UI_PUBLIC_IPV4_CONFIG;
            }
            netConfigStr += '<ol class="plr15 pt-2">' + titles + '：' + ip_set + '</ol>';
        }


        let des = `<div class="content alert alert-block pd0 mb0"><div class="circle" style="width: 20px;
        height: 20px;background: #FFFFFF;position: absolute; border-radius: 10px !important;
        right: -7px;top: -10px; box-shadow: 5px 5px 10px rgba(4, 13, 45, 0.1);"><button type="button" class="close vmConfCloseBtn" data-dismiss="alert" style="
        position: absolute;right: 2px;top: -1px;"></button></div>`;

        des += `<ul id="takeoverBuiltInVmConfDes" class="pl0" style="background: #FAFAFA;border-radius: 4px !important;border: 1px dotted #D9D9D9;padding: 12px 16px;color: #718096;">
            <ol class="plr15 pt-2">`+LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE_NAME+`：${modalVmNameStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_V2_SOCKET+`：${slotsnumStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_V2_SCCKET_CORE+`：${slotcorenumStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_CPU_TYPE+`：${cputypeStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_PUBLIC_MEMORY+`：${memoryStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VIRTUAL_MACHINE_SYSTEM_FIRMWARE_TYPE+`：${modalBootFirmwareStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VERIFY_EFFECTIVE_TIME+`：${maxWaitTimeStr}</ol>
            <ol class="plr15 pt-2">`+LANG.UI_VM_SETTING_V2_DISK+`：${diskConfigStr}</ol>
           `;
        if(config.host_reset_name_flag){
            des += `<ol class="plr15">`+LANG.UI_VM_RESET_HOST_NAME+`：${rehostname}</ol>`;
        }
        des += `${netConfigStr}</ul></div>`;
        return des;
    }
    // 初始化整机
    let initMachineConfig = function (){

        var config = oldInfo.take_over.config;
        if (EditFlag && _backupTaskType == "backup" && oldInfo.take_over.auto_takeover_enable_flag) {
            // 备机配置
            submitMountTakeoverStandbyConf();
            // 网络配置
            if (EditFlag && oldInfo.take_over.auto_takeover_enable_flag && oldInfo.take_over.config.takeover_type == 2
                && oldInfo.take_over.config.network.takeover_business_ip_map.length > 0
            ) {
                // 获取网络
                submitHostNetworkConf();
            }
        }

        // 回切通讯IP
        if (config.agent_failback_standby_ip != '') {
            $('.takeoverfailbackupip').val(config.agent_failback_standby_ip);
        }
    }

    // 获取标签点描述
    let getLabelDesc = function (settings){
        var typeDes = ['', LANG.UI_STRATEGY_DAY, LANG.UI_STRATEGY_WEEK, LANG.UI_STRATEGY_MONTH];
        var des = '';
        des += LANG.UI_VOL_CDP_JOB_DETAILS_LABEL_STRATEGY + '(';
        des += typeDes[settings.strategy_type];
        for(var i=0; i<settings.day.length; i++){
            if(1 == settings.strategy_type) break;	//每天的话不读取days
            if(settings.day[i]){
                des += (i + 1) + ", ";
            }
        }
        var rollDes = LANG.UI_STRATEGY_ROLL_NO;
        if(settings.roll_flag){
            rollDes =LANG.UI_STRATEGY_ROLL_INTERVAL+ settings.roll_interval;
        }
        des += settings.start_time + LANG.UI_STRATEGY_START + ", ";
        des += rollDes;
        if(settings.roll_flag){
            des += ","+LANG.UI_STRATEGY_ROLL_OVER_TIME+":"+settings.roll_end_time;
        }
        des += ")";
        return des;
    }

    //重置第3步配置信息
    let resetStep3Conf = function(){
        resetTakeoverConf();
        _isHideSwitchBackIpFlag = true;
        _createMsgdata.script_list = {};//重置脚本配置
        if (EditFlag && oldInfo.take_over.auto_takeover_enable_flag){

        } else {
            _createMsgdata.takeover_object = {};
        }

        if(_backupTaskType == "backup"){
            _createMsgdata.takeover_object.takeover_mode = TAKEOVER_TYPE.COMPLETE_MACHINE;
            // $('.takeoverstrategylabel').text(LANG.UI_VOL_CDP_EMERGENCY_TAKEOVER_DESC);
            //  $('.takeoverstrategyswitch').text(LANG.UI_VOL_CDP_EMERGENCY_TAKEOVER_DESC);
        }else{  //复制任务默认为挂为挂载接管
            _createMsgdata.takeover_object.takeover_mode = TAKEOVER_TYPE.MOUNT;
            $('.takeoverstrategylabel').text(LANG.UI_PUBLIC_TAKEOVER);
            $('.takeoverstrategyswitch').text(LANG.UI_PUBLIC_TAKEOVER);
            $('.takeovertasklabel').text(LANG.UI_PUBLIC_TAKEOVER + ": ");
        }

        $('.beforetakeoverscriptview').hide();
        $('.aftetakeoverscriptview').hide();
        $('.takeovermonitorscriptview').hide();
        $('.backuptasktakeoverconfdiv').hide();
        // $('.takeoverrestoreipview').hide(); //默认隐藏回切IP配置

        loadDefaultSafeConf();
        loadDefaultScript();
        loadCommonStrageryConf();  //通用策略显示与隐藏配置
        if (!CONF.FUNCTIONS.includes('multithread')) {
            $('.transferDiv').hide();
        }
        if (!EditFlag) {
            $('.apptakeoverswitch').bootstrapSwitch('state', false);  //重置应用接管开关
        }
        if(CONF.VENDOR == "sangfor"){  //深信服OEM版本只支持整机接管，需要屏蔽接管平台和自动接管的相关配置，对应需求号：#27350
            $('#takeoverdesc2').hide();
            $('#takeoverdesc3').show();
        }else{
            $('#takeoverdesc2').show();
            $('#takeoverdesc3').hide();
        }
    }
    // 设置radio重新选中并且触发改变事件
    function resetRadio(name, value) {
        // 清除所有 span 的 checked 类
        $(`input[name="${name}"]`).each(function () {
            $(this).closest('span').removeClass('checked');
        });
        // 设置目标 radio 并加样式
        var target = $(`input[name="${name}"][value="${value}"]`);
        target.prop('checked', true).closest('span').addClass('checked');
        target.trigger('change'); // 触发 change（兼容 jQuery 和原生）
    }
    /**
     * 通用策略部分配置显示与隐藏
     */
    let loadCommonStrageryConf = function(){
        if(_backupTaskType == "copy" &&  _createMsgdata.replication_object.mirror_backup_flag !=CONF.FLAG.SET){ //如果是复制任务并且未开启数据备份
            $('.labelstrategyvew').hide();
            $('.storagestrageyview').hide();
            $('.reservestrategyview').hide();

            $('.tagpointinfoshowdiv').hide();
            $('.storageinfoshowdiv').hide();
            $('.reservetypeshowdiv').hide();

            //展开限速策略配置
            $('#speed').addClass('in');
            $('#speed').removeAttr('style');
            $('a[name="speedlimitview"]').removeClass('collapsed');
        }else{
            $('.labelstrategyvew').show();
            $('.storagestrageyview').show();
            $('.reservestrategyview').show();

            $('.tagpointinfoshowdiv').show();
            $('.storageinfoshowdiv').show();
            $('.reservetypeshowdiv').show();
        }

    }


    /**
     * 加载默认脚本配置参数
     */
    let loadDefaultScript = function(){
        _createMsgdata.script_list.before_backup_script_location = [];
        _createMsgdata.script_list.after_backup_script_location = [];
        _createMsgdata.script_list.before_takeover_script_location = [];
        _createMsgdata.script_list.after_takeover_script_location = [];
        _createMsgdata.script_list.detect_takeover_script_location = [];
    }
    /**
     * 加载默认安全配置参数
     */
    let loadDefaultSafeConf = function(){
        _createMsgdata.safe_config_strategy.worm_flag = CONF.FLAG.UNSET;
        _createMsgdata.safe_config_strategy.worm_protection_time = 0;
        _createMsgdata.safe_config_strategy.virus_scan_flag = 0;
        _createMsgdata.safe_config_strategy.virus_scan_config = {};
        _createMsgdata.safe_config_strategy.integrity_check_flag = 0;
        _createMsgdata.safe_config_strategy.integrity_check_config = {};
    }

    //重置接管配置
    let resetTakeoverConf = function(){
        if (EditFlag && oldInfo.take_over.auto_takeover_enable_flag) {
        } else {
            $('.backuptasktakeoverconfdiv').hide();
            $('.backuptasktakeoverswitch').bootstrapSwitch('state', false);

            $('.cmtakeovermode').removeAttr('checked').filter('[value="1"]').attr('checked', true);

            $("input[name='cm_takeover_mode'][value='2']").prop('checked', false);  //接管方式，默认整机接管
            $("input[name='cm_takeover_mode'][value='1']").prop('checked', true);  //接管方式，默认整机接管
            $('.takeoverStandbyDes').html("");  //主备机配置title
            $('#cmMountTakeoverConfDesTips').remove();  //清空主备映射配置
            $('.mountTakeoverStandbyConfDes').html('');
            $('#takeover_ip_map_list li').remove();  //清空主备网络映射配置

            _createMsgdata.takeover_object = {};
        }

        if(_backupTaskType == "backup"){
            //默认整机接管，需要隐藏网络配置
            $(".backuptakeovertypediv").show();
            $('.takeoverstandbyhostconf').show();
            $('.takeoverNetworkConf').hide();
            _createMsgdata.standby_agent_uuid = "";
        }else if(_backupTaskType == "copy"){
            $(".backuptakeovertypediv").hide();
            $('.takeoverstandbyhostconf').hide();
            $('.takeoverNetworkConf').show();
            _createMsgdata.standby_agent_uuid = $('#cmBackupTaskCopyStandbySelect').val();
        }
        _takeoverModeValue = 1; //整机接管
    }


    let step3Valid = function(){
        if (EditFlag) {
            $('.cmcdptaskname').val(oldInfo.job_name);
        } else {
            getTaskName();
        }

        var result = getTagPoint();
        if(!result) return false;
        var result = getSpeedStr() & getReserveStr() & getAchiveStr() & getTransferStr() & getStoreStr() & getTakeoverConf() & getBackupScriptConf() & getAdvancedConf() & takeoverSafeConfig();
        if(result){
            result = result && showStep4();
        }
        return result;
    }

    //获取接管安全配置
    let takeoverSafeConfig = function(){
        let safeConfig = $('#takeoverSafetyConf').getTakeAppOver();
        if (safeConfig === false) {  // 验证病毒扫描配置内容是否符合要求
            return false;
        }
        let virusDetectionInfo = safeData('',safeConfig,'');
        let safeConfigStr = safeConfig.str;
        _createMsgdata.safe_config_strategy = virusDetectionInfo;
        _createMsgdata.safe_config_strategy.virus_str = safeConfigStr;
        return true;
    }

    //获取备份任务脚本配置
    let getBackupScriptConf = function(){
        _createMsgdata.script_list = {};
        let cmBeforeBackupScript = $('#testDom').getVinScript('cmBeforeBackupScript');
        let cmAfterBackupScript = $('#testDom').getVinScript('cmAfterBackupScript');
        let cmBeforeTakeoverScript = $('#testDom').getVinScript('cmBeforeTakeoverScript');
        let cmAfterTakeoverScript = $('#testDom').getVinScript('cmAfterTakeoverScript');
        let cmTakeoverMonitorScript = $('#testDom').getVinScript('cmTakeoverMonitorScript');
        // let cmTakeoveExecuteScript = $('#cmTakeoveExecuteScript').getVinScript(yourClassName);
        if (!cmBeforeBackupScript || !cmAfterBackupScript || !cmBeforeTakeoverScript || !cmAfterTakeoverScript || !cmTakeoverMonitorScript) {
            return false;
        }
        _createMsgdata.script_list.before_backup_script_location = cmBeforeBackupScript;
        _createMsgdata.script_list.after_backup_script_location = cmAfterBackupScript;
        _createMsgdata.script_list.before_takeover_script_location = cmBeforeTakeoverScript;
        _createMsgdata.script_list.after_takeover_script_location = cmAfterTakeoverScript;
        _createMsgdata.script_list.detect_takeover_script_location = cmTakeoverMonitorScript;
        return true;
    }

    //接管配置策略
    let getTakeoverConf = function(){
        if(!$('.backuptasktakeoverswitch').is(':checked')){ //接管配置
            _createMsgdata.takeover_object = "";
            return true;
        }
        if (!_createMsgdata.standby_agent_uuid) {
            if (_backupTaskType == 'backup') {
                var standbyValue = $('#cmBackupTaskTakeoverStandbySelect').val();
            } else {
                var standbyValue = $('#cmBackupTaskCopyStandbySelect').val();
            }
            if (standbyValue != '') {
                _createMsgdata.standby_agent_uuid = standbyValue;
            }
        }

        let standbyHostUuid = _createMsgdata.standby_agent_uuid; //备机uuid
        let takeoverModeValue = 0;
        if(_backupTaskType == "backup"){
            takeoverModeValue = $('input[name="cm_takeover_mode"]:checked').val();
        }else{ //复制任务
            let standbyAgentType = $('#cmBackupTaskCopyStandbySelect option:selected').attr('agent_type');  //复制任务备机类型
            if(standbyAgentType== CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){
                takeoverModeValue = TAKEOVER_TYPE.COMPLETE_MACHINE;  //整机接管
            }else{
                takeoverModeValue = TAKEOVER_TYPE.MOUNT;  //挂载接管
            }
        }
        _takeoverModeValue = takeoverModeValue;
        _createMsgdata.takeover_object.host_reset_name = "";  //挂载接管不需要配置,需要对应键位
        if(!standbyHostUuid){
            UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_STANDBY_CONF_TIPS);
            return false;
        }

        if(takeoverModeValue == TAKEOVER_TYPE.COMPLETE_MACHINE && _backupTaskType != "copy"){ //整机接管
            if (!_createMsgdata.takeover_object.hasOwnProperty('takeover_vm')) {  //判断是否配置内嵌主机
                UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_STANDBY_CONF_TIPS);
                return;
            }

            let hostResetName = _createMsgdata.takeover_object.takeover_vm.host_reset_name;  //重置主机名
            _createMsgdata.takeover_object.takeover_disk_list = _createMsgdata.backup_object.disk_list;
            _createMsgdata.takeover_object.vol_mount_point_set = [];
            _createMsgdata.takeover_object.host_reset_name = _createMsgdata.takeover_object.takeover_vm.host_reset_name;
            _createMsgdata.takeover_object.takeover_standby_agent_uuid = _createMsgdata.standby_agent_uuid;
        }

        if($('.apptakeoverswitch').is(':checked')){  //应用接管
            // _createMsgdata.takeover_object.app_set = [];
            _createMsgdata.takeover_object.app_takeover_flag = CONF.FLAG.SET;
        }else{
            _createMsgdata.takeover_object.app_set = [];
            _createMsgdata.takeover_object.app_takeover_flag = CONF.FLAG.UNSET;
        }

        if($('.cmautotakeoverswitch').is(':checked')){  //应用接管
            _createMsgdata.takeover_object.auto_takeover_flag = CONF.FLAG.SET;
        }else{
            _createMsgdata.takeover_object.auto_takeover_flag = CONF.FLAG.UNSET;
        }
        //回切通讯IP判断：1.在配置回切网络情况下必须要配置回切通讯IP；2.未配置回切通讯IP时不需要配置不校验

        let businessIpMapIsConf = _createMsgdata.takeover_object.takeover_business_ip_map?.length > 0 || false;

        let takeoverRestoreIp = $('.takeoverfailbackupip').val();
        // if((takeoverRestoreIp == "" || takeoverRestoreIp == null) && _isHideSwitchBackIpFlag == false && businessIpMapIsConf){
        if((takeoverRestoreIp == "" || takeoverRestoreIp == null)){
            // Bug #27453 所有的都必须配置通信ip，不限制是否校验
            UIToastr.showWarning(LANG.UI_CM_TAKEOVER_CONFIGURE,LANG.UI_CM_TAKEOVER_CONFIGURE_TIPS);
            _createMsgdata.takeover_object.agent_failback_standby_ip = "";
            return false;
        }else{
            _createMsgdata.takeover_object.agent_failback_standby_ip = takeoverRestoreIp;
        }

        if($('.cmautotakeoverswitch').is(':checked')){  //自动接管开关
            _createMsgdata.takeover_object.auto_takeover_flag = CONF.FLAG.SET;
            let heartbeatFailureTime =  parseInt($('#agent_heartbeat_failure_time').val()); //心跳检测失效时间
            if(heartbeatFailureTime > 3600 || heartbeatFailureTime < 5 || heartbeatFailureTime == ""){
                $('#agent_heartbeat_failure_time').val(30);
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_HEARTBEAT_FAILURE_TIME, LANG.UI_VOL_CDP_BACKUP_HEARTBEAT_FAILURE_TIME_MESSAGE);
                return false;
            }
            _createMsgdata.takeover_object.agent_heartbeat_failure_time = heartbeatFailureTime;
            if($('.appmonitorswitch').is(':checked')){    //自动接管应用故障监测
                _createMsgdata.takeover_object.app_consecutive_failure_num = parseInt($('#app_failure_number').val());
                _createMsgdata.takeover_object.app_fault_detection_interval = parseInt($('#takeover_app_interval').val());
                _createMsgdata.takeover_object.app_monitor_switch = CONF.FLAG.SET;
            }else{
                _createMsgdata.takeover_object.app_consecutive_failure_num = 0;
                _createMsgdata.takeover_object.app_fault_detection_interval = 0;
                _createMsgdata.takeover_object.app_monitor_switch = CONF.FLAG.UNSET;
            }
        }else{
            _createMsgdata.takeover_object.auto_takeover_flag = CONF.FLAG.UNSET;
        }
        //主机IP漂移
        _createMsgdata.takeover_object.master_agent_ip_switch_flag = CONF.FLAG.UNSET;
        if(!_createMsgdata.takeover_object.takeover_business_ip_map){
            _createMsgdata.takeover_object.takeover_business_ip_map = {};
            _createMsgdata.takeover_object.master_agent_ip_switch_flag = CONF.FLAG.UNSET;
        }
        if(_createMsgdata.takeover_object.takeover_business_ip_map.length>0){
            _createMsgdata.takeover_object.master_agent_ip_switch_flag = CONF.FLAG.SET;
        }
        if(takeoverModeValue == TAKEOVER_TYPE.COMPLETE_MACHINE){
            _createMsgdata.takeover_object.master_agent_ip_switch_flag = CONF.FLAG.SET;
        }
        _createMsgdata.takeover_object.takeover_agent_role = CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER;  //99.验证 100.接管
        return true;
    }

    //限速策略
    var getSpeedStr = function(){
        _createMsgdata.speed_limit = speedSubmitInfo();
        return true;
    }

    // 获取设置的所有策略配置信息
    var speedSubmitInfo = function (){
        let info = {};
        info['level'] = $('#tasklevelselect').val();
        info['type'] = $('#speedtypeselect').val();
        if (info['type'] == 1) {
            // 选择策略
            var selectedRow = $('.strategy-table #table').bootstrapTable('getSelections');
            if (selectedRow.length == 1) {
                info['uuid'] = selectedRow[0].uuid
                info['name'] = selectedRow[0].name
                info['strategy_type'] = selectedRow[0].type
            }
        } else {
            // 自定义
            info['speedInfo'] = $('#speedstrategy').getSpeedStrategyConfigFinal();
        }
        return info;
    }

    //封装标签策略
    var getTagPoint = function (){
        _createMsgdata.backup_info = {};
        _createMsgdata.backup_info.type = "strategy";
        _createMsgdata.backup_info.datetime = $("#timing_start").val();
        _createMsgdata.backup_info.tag_info = tagList;
        return true;
    }

    //得到归档策略
    var getAchiveStr = function(){return true;}

    //得到保留策略
    var getReserveStr = function(){
        _createMsgdata.highInfo.reserve = {};
        _createMsgdata.highInfo.reserve.type = 2; //按天数保留
        _createMsgdata.highInfo.reserve.value = $('#spinnerDayInput').val();
        if(_createMsgdata.highInfo.reserve.value > 0){
            return true;
        }else{
            UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
            return false;
        }
    }
    /**
     * @function 获取传输策略相关配置参数
     */
    var getTransferStr = function(){
        _createMsgdata.highInfo.transfer = {};
        _createMsgdata.highInfo.transfer.mode = $('#transport_mode').val();
        let takeoverModeValue = $('input[name="cm_takeover_mode"]:checked').val();

        if(networkFlag && (_createMsgdata.node_pool_uuid=="" || _createMsgdata.storage_pool_uuid=="" ) && _createMsgdata.takeover_object.takeover_mode !=TAKEOVER_TYPE.MOUNT && _backupTaskType !="copy"){

            // if(networkFlag || _createMsgdata.backup_target_info.node_uuid_list.length ==1){
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
            _createMsgdata.highInfo.transfer.network = networkNode.network_uuid;
        }
        //如果接管方式在线接管，择不需要配置传输网络（bug #24887 需求）
        else if( !_createMsgdata.node_pool_uuid || !_createMsgdata.storage_pool_uuid || takeoverModeValue ==TAKEOVER_TYPE.COMPLETE_MACHINE){
            _createMsgdata.highInfo.transfer.network = "";
        } else{
            _createMsgdata.highInfo.transfer.network = "";
        }

        _createMsgdata.highInfo.transfer.compress = 0;
        _createMsgdata.highInfo.transfer.encrypt = 0;
        _createMsgdata.highInfo.transfer.compress_method = 0;
        _createMsgdata.highInfo.transfer.encrypt_method = 0;

        if($('#tran_compress_switch').is(':checked')){ //传输压缩
            _createMsgdata.highInfo.transfer.compress = true;  //此处只能传输boole值，因为对应的解析函数通过boole值来转换SET（1） or UNSET（2）
            _createMsgdata.highInfo.transfer.compress_method = $('#transferCompressGrade').val();  //压缩等级
        }else{
            _createMsgdata.highInfo.transfer.compress = false;
        }
        if($('#encrypttransfer').is(':checked')){ //传输加密
            _createMsgdata.highInfo.transfer.encrypt = true;  //此处只能传输boole值，因为对应的解析函数通过boole值来转换SET（1） or UNSET（2）
            // 传输加密算法
            _createMsgdata.highInfo.transfer.encrypt_method = 1;
        }else{
            _createMsgdata.highInfo.transfer.encrypt = false;
        }
        // 传输加密算法
        // _createMsgdata.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val(1));
        // 重连次数
        _createMsgdata.highInfo.transfer.reconnect_times = parseInt($('#reconnect_time').val());
        // 重连间隔时间
        _createMsgdata.highInfo.transfer.reconnect_interval = parseInt($('#reconnect_interval').val());
        // 重连次数数字检测
        if(!Number.isInteger(_createMsgdata.highInfo.transfer.reconnect_times)){
            UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER,LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_TIPS);
            return false;
        }
        // 重连次数大小1 - 999
        if (_createMsgdata.highInfo.transfer.reconnect_times < 1) {
            UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
            return false;
        }
        // 重连间隔时间最小5
        if(_createMsgdata.highInfo.transfer.reconnect_interval < 5){
            UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER,LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_MIN_TIPS);
            return false;
        }
        // 重连间隔时间最大60
        if(_createMsgdata.highInfo.transfer.reconnect_interval > 60){
            UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_MAX_TIPS);
            return false;
        }
        // 重连间隔数字检测
        if(!Number.isInteger(_createMsgdata.highInfo.transfer.reconnect_interval)){
            UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER,LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_TIPS);
            return false;
        }
        _createMsgdata.thread_num = 1; //传输线程个数,未授权，默认1
        if (CONF.FUNCTIONS.includes('multithread')) {
            var verifyThread =  checkTransferThreadNum();
            if(!verifyThread){
                return false;
            }
            _createMsgdata.thread_num = $('#transfer_thread_number').val(); //传输线程个数
        }


        var datapackage_size = $('#transfer_datapackage_size').val(); //传输数据包大小
        var transport_block_size = datapackage_size * 1024*1024;
        _createMsgdata.transport_block_size = transport_block_size; //传输数据包大小
        _createMsgdata.highInfo.transfer.block_size = transport_block_size; //传输数据包大小
        return true;
    }
    /**
     * 校验输入的传输策略策略是否符合规则
     */
    var checkTransferThreadNum = function(){
        var transferThreadNumber = $("#transfer_thread_number").val();
        if(transferThreadNumber>4){
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE1);
            $("#transfer_thread_number").val(1);
            return false;
        }
        if(transferThreadNumber<1){
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE2);
            $("#transfer_thread_number").val(1);
            return false;
        }
        return true;
    }


    /**
     * @function 高级配置相关参数检测
     */
    var getAdvancedConf = function(){
        var storage_block_size = $('#storage_block_size').val();
        var storagetUnit = 1024;
        var file_cache_path = $('#file_cache_path').val();  //文件缓存路径
        if(!$('#file_cache_path').readonly ) {
            if(file_cache_path==""){
                UIToastr.showWarning(LANG.UI_VM_SETTING_V2_HIGH_SETTING, LANG.UI_JOB_FILE_CACHE_PATH_NULL_TIPS);
                return false;
            }else {
                if(!checkFilePath(file_cache_path,_optionType)){
                    UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
                    return false;
                }
            }
        }
        // var dd = stopOrRecoveryRealTimeTaskChange();  //停止或恢复实时任务监控间隔检测

        _createMsgdata.backup_object.monitor_data_io_replication_mode = 1; //default
        _ioReplicationMode = $('input:radio[name=io_replication_modle]:checked').val(); //IO 复制模式

        _createMsgdata.backup_object.monitor_data_io_replication_mode = _ioReplicationMode;


        _createMsgdata.storage_block_size = storage_block_size * storagetUnit; //存储数据块大小


        //全数据备份，默认关
        _createMsgdata.full_backup = CONF.FLAG.SET;
        if($('#validDataBackupSwitch').is(':checked')){
            _createMsgdata.full_backup = CONF.FLAG.UNSET;
        }
        //跳过坏块备份
        _createMsgdata.skip_bad_block = CONF.FLAG.UNSET;
        if($('#skippingBadBlocksBackupSwitch').is(':checked')){
            _createMsgdata.skip_bad_block = CONF.FLAG.SET;
        }

        //断点续传-故障自动恢复
        if($('#automatic_fault_recovery_switch').is(':checked')){
            _createMsgdata.backup_object.auto_fault_resume_flag = CONF.FLAG.SET;  //默认开启
        }else {
            _createMsgdata.backup_object.auto_fault_resume_flag = CONF.FLAG.UNSET;
        }

        var file_cache_size = $('#file_cache_size').val();
        var file_cache_block_unit = 1024*1024;  //该项配置，选项单位为MB，传输到后台的值为字节；

        if($('#memory_cache_switch').is(':checked')){  //内存缓存配置
            var memory_cache_size = $('#memory_cache_size').val();
            var memory_cache_block_unit = 1024*1024;  //该项配置，选项单位为MB，传输到后台的值为字节；
            _createMsgdata.cache_config.memory_cache_alloc_size = memory_cache_size * memory_cache_block_unit;
        }else{
            _createMsgdata.cache_config.memory_cache_alloc_size = 0;
        }
        _createMsgdata.cache_config.file_cache_alloc_path = file_cache_path;  //文件缓存路径
        _createMsgdata.cache_config.file_cache_alloc_size = file_cache_size*file_cache_block_unit; //文件缓存大小

        // 获取资源监测配置
        let infos = ResourceMonitoring.getInfo({osType: _agentType});
        if (!infos) {
            return false;
        }
        _createMsgdata.resource_protect_config = infos.resource_protect_config;
        _createMsgdata.high_pressure_strategy = infos.high_pressure_strategy;

        // 资源检测配置显示
        $('.resourcemonitorstrategyshow').html(infos.resourceMonitorStr);

        return true;
    }

    /**
     * @function 获取存储策略配置参数
     */
    let getStoreStr = function(){
        _createMsgdata.highInfo.store = {};
        _createMsgdata.highInfo.store.compress_method = 1;
        _createMsgdata.highInfo.store.encrypt_method = 1;
        _createMsgdata.highInfo.store.deduplication = $('#deduplicationCheck').get(0).checked;
        let storage_block_size = $('#storage_block_size').val();
        let storagetUnit = 1024;  //传递到后台的单位为字节
        _createMsgdata.highInfo.store.block_size = storage_block_size * storagetUnit; //存储数据块大小
        // 接口处是用的这个字段名
        _createMsgdata.highInfo.store.blocksize = _createMsgdata.highInfo.store.block_size;

        _createMsgdata.highInfo.store.compress = $('#compressCheck').get(0).checked;
        // 压缩等级
        // if($('#compressCheck').get(0).checked){
        //     _createMsgdata.highInfo.store.compress_method = $('#compressGrade').val();
        // }
        _createMsgdata.highInfo.store.dataencrypt = $('#encryptStorageCheck').get(0).checked;
        // 存储加密
        if(!_createMsgdata.highInfo.store.dataencrypt){
            _createMsgdata.highInfo.store.password_auto_flag = false;
        }else{
            // _createMsgdata.highInfo.store.encrypt_method = parseInt($('#storageEncryptMethod option:first').val());
            _createMsgdata.highInfo.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;
        }
        _createMsgdata.highInfo.node = {};
        _createMsgdata.highInfo.node.nodecheck = true;
        _createMsgdata.highInfo.node.nodeuuid = _createMsgdata.node_uuid;
        _createMsgdata.highInfo.node.node_pool_uuid = _createMsgdata.node_pool_uuid;
        _createMsgdata.highInfo.node.storagecheck = true;
        _createMsgdata.highInfo.node.storageuuid = _createMsgdata.storage_uuid;
        _createMsgdata.highInfo.node.storage_pool_uuid = _createMsgdata.storage_pool_uuid;

        if (EditFlag && oldInfo.storage_strategy.password == checkpassword()) {
            // 修改并且没改密码
            _createMsgdata.highInfo.store.password = checkpassword();
            _oldPassword = _createMsgdata.highInfo.store.password;
        } else {
            _createMsgdata.highInfo.store.password = btoa(checkpassword());
        }
        if(_createMsgdata.highInfo.store.password_auto_flag){
            _createMsgdata.highInfo.store.password = "";
        }else if(_createMsgdata.highInfo.store.dataencrypt && _createMsgdata.highInfo.store.password == ""){
            UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
            return false;
        }else if(_oldPassword =='' && _createMsgdata.highInfo.store.dataencrypt && _createMsgdata.highInfo.store.password != btoa($('#repassword').val())){  //没有选择策略管理
            UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
            return false;
        }else if(_oldPassword !='' && _createMsgdata.highInfo.store.dataencrypt && _createMsgdata.highInfo.store.password != $('#repassword').val()){  //选择了策略管理
            UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
            return false;
        }
        return true;
    }

    //存储之前检查是否有修改过密码
    var checkpassword = function(){
        var now_password = $('#password').val();
        //把现在的密码和读取到的密码做对比
        //先检查旧密码是否存在
        if(_oldPassword != ''){
            if(now_password == _oldPassword){  //没有修改密码
                return atob(now_password);
            }
        }
        return now_password;

    }
    //获取配置信息并汇总显示
    let showStep4 = function(){

        //备份数据源
        let appTypeName = LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE;
        if(_createMsgdata.backup_object.monitor_app_uuid_set_info.length>0){
            appTypeName = _createMsgdata.backup_object.monitor_app_uuid_set_info[0].app_name;
        }
        let backupSource = "";
        backupSource += _createMsgdata.agetName + "<br>";
        backupSource += LANG.UI_VOL_CDP_BACKUP_CONFIGURE_APPLICATION +"：&nbsp;"+ appTypeName + "<br>";
        backupSource += LANG.UI_CM_CDP_BACKUP_MONITOR_DISK+"：&nbsp;" + _createMsgdata.backup_object.disk_display_info;

        $('.volcdphostshow').html(backupSource);

        // var nodeInfo = $('#selectnode option:selected').text();
        // var storeInfo = $('#selectstorage option:selected').text();
        let nodeInfo = _createMsgdata.backup_target_info.node_text;
        let storeInfo = _createMsgdata.backup_target_info.storage_text;
        $('.nodeinfoshow').html(nodeInfo);
        $('.storeinfoshow').html(storeInfo);
        $('#replicationBackupStandbyShow').html("");

        //病毒监测配置
        $('.takeoversafetyconfview').hide();
        if(_backupTaskType == "copy"){  //复制任务
            $('.backupstandbydiv').show();
            $('.backupdatatobackupserverdiv').show();
            let replicationObject = _createMsgdata.replication_object;
            let isBackupDataFlag = replicationObject.mirror_backup_flag;
            var sourceDiv = document.getElementById('vmDetailConfigInfo'); // 获取源div和目标div的引用
            var destinationDiv = document.getElementById('replicationBackupStandbyShow');
            var clone = sourceDiv.cloneNode(true);

            destinationDiv.appendChild(clone);
            $('.backupdatatobackupservershow').html(getSwitchDes(isBackupDataFlag)); //备份复制数据到备份服务器
            // let standbyAgentType = $('#cmBackupTaskCopyStandbySelect option:selected').attr('agent_type');
            // if(standbyAgentType == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){  //复制备机为live-cd
            //     $('.takeoversafetyconfview').show(); //病毒监测配置
            // }
        }else{
            $('.backupstandbydiv').hide();
            $('.backupdatatobackupserverdiv').hide();
            if(_takeoverModeValue==1){ //整机接管
                $('.takeoversafetyconfview').show();  //病毒监测配置
            }
        }

        generalStrategyConfDesc();  //通用策略配置描述信息汇总
        getTransportinfoConf();  //获取传输策略配置信息
        takeoverConfInfo();  //接管任务配置信息汇总
        scriptConfInfo();  //脚本配置信息汇总
        advancedConfInfo(); //高级配置信息汇总
    }

    //高级配置信息汇总并展示对应配置信息
    let advancedConfInfo = function(){
        let commonStrategyStr = "";
        let storageblocksizelabel = $('.storageblocksizelabel').html(); //存储块大小
        let ioreplicationlable = $('.ioreplicationlable').html();  //IO复制模式
        let validdatadivlable = $('.validdatadivlable').html();  //有效数据备份
        let skippingbadblocksbacklable  = $('.skippingbadblocksbacklable').html();  //跳过坏块备份
        let automaticfaultrecoverylable  = $('.automaticfaultrecoverylable').html();  //断点续传

        let storageBlockSizeStr = $('#storage_block_size option:selected').text();
        commonStrategyStr += storageblocksizelabel + ": " +  storageBlockSizeStr +" <br>";

        var ioReplicationMode = LANG.UI_VOL_CDP_BACKUP_ASY;   //IO复制模式
        if(_ioReplicationMode==1){
            ioReplicationMode = LANG.UI_VCENTER_SYNC;
        }
        commonStrategyStr += ioreplicationlable + ": " + ioReplicationMode +" <br>";
        let validDataValue = CONF.FLAG.UNSET;
        if(_createMsgdata.full_backup==CONF.FLAG.UNSET){  //此处取全量备份的反值
            validDataValue = CONF.FLAG.SET;
        }
        commonStrategyStr += validdatadivlable + ": " + getSwitchDes(validDataValue) +" <br>";  //有效数据备份
        commonStrategyStr += skippingbadblocksbacklable + ": " + getSwitchDes(_createMsgdata.skip_bad_block) +" <br>";  //跳过坏块备份
        commonStrategyStr += automaticfaultrecoverylable + ": " + getSwitchDes(_createMsgdata.backup_object.auto_fault_resume_flag) +" <br>";  //断点续传

        let filecachepathlabel = $('.filecachepathlabel').html();  //客户端文件缓存label
        let filecachesizelabel = $('.filecachesizelabel').html();  //客户端文件缓存大小label
        let memorycachelable = $('.memorycachelable').html();  //内存缓存开关label
        let memorycachesizelabel = $('.memorycachesizelabel').html();  //客户端内存缓存大小label
        let cacheConfStr = "";
        let fileCachePath = $('#file_cache_path').val();
        let fileCacheSize = $('#file_cache_size option:selected').text();
        if(fileCachePath){
            cacheConfStr += filecachepathlabel + ": " + fileCachePath + "<br>";  //客户端文件缓存
            cacheConfStr += filecachesizelabel + ": " + fileCacheSize + "<br>";  //文件缓存大小
        }
        let memorycacheFlag = CONF.FLAG.UNSET;
        if($('#memory_cache_switch').is(':checked')){  //内存缓存
            memorycacheFlag = CONF.FLAG.SET;
            cacheConfStr += memorycachelable + ": " + getSwitchDes(memorycacheFlag) + "<br>";  //内存缓存开关
            cacheConfStr += memorycachesizelabel + ": " + $('#memory_cache_size option:selected').text(); + "<br>";  //内存缓存大小
        }

        let overLoadprotectConfFlag = CONF.FLAG.UNSET;
        if($('#ignoreResourceLimit').is(':checked')){
            overLoadprotectConfFlag = CONF.FLAG.SET;
        }
        let overLoadprotectConfLable = $('.ignoreResourceLimitLabel').html();
        let overLoadprotectConf = overLoadprotectConfLable + ": " + getSwitchDes(overLoadprotectConfFlag) + "<br>";  //忽略节点资源限制

        $('.commonstrategyshow').html(commonStrategyStr);  //公共配置
        $('.cachestrategyshow').html(cacheConfStr);  //缓存配置
        $('.overloadprotectstrategyshow').html(overLoadprotectConf);  //超载保护配置

    }

    // 获取脚本配置信息
    let scriptConfInfo = function(){
        let scriptConf = _createMsgdata.script_list;
        let afterBackupScriptLocation = scriptConf.after_backup_script_location;
        let afterTakeoverScriptLocation = scriptConf.after_takeover_script_location;
        let beforeBackupScriptLocation = scriptConf.before_backup_script_location;
        let beforeTakeoverScriptLocation = scriptConf.before_takeover_script_location;
        let detectTakeoverScriptLocation = scriptConf.detect_takeover_script_location;
        let scriptConfFlag = CONF.FLAG.UNSET;
        if(afterBackupScriptLocation.length > 0 || afterTakeoverScriptLocation.length > 0 || beforeBackupScriptLocation.length > 0
            || beforeTakeoverScriptLocation.length > 0 || detectTakeoverScriptLocation.length > 0){
            scriptConfFlag = CONF.FLAG.SET;
        }else{
            loadDefaultScript();
        }
        let scriptConfStr = LANG.UI_CM_NOT_CONFIGURED;
        if(scriptConfFlag == CONF.FLAG.SET){
            scriptConfStr = LANG.UI_CM_IS_CONFIGURED;
        }
        $('.taskscriptstrategyshow').html(scriptConfStr);
    }

    // 获取备份任务接管策略配置信息并展示对应配置信息
    let takeoverConfInfo = function(){
        let takeoverFlag = CONF.FLAG.UNSET;
        $('.takeoverconfdetailview').hide();
        if($('.backuptasktakeoverswitch').is(':checked')){  //接管配置
            takeoverFlag = CONF.FLAG.SET;
            $('.takeoverconfdetailview').show();
            $('.takeoverswitchstrategyshow').html(getSwitchDes(takeoverFlag));
        }else{
            $('.takeoverswitchstrategyshow').html(getSwitchDes(takeoverFlag));
            return;
        }


        $('.takeovertypestrategyview').hide();  //复制任务隐藏接管方式的显示
        if( _backupTaskType == "backup"){
            $('.takeovertypestrategyview').show();
        }
        if(CONF.VENDOR == "sangfor"){  //深信服OEM版本只支持整机接管，需要屏蔽接管平台和自动接管的相关配置，对应需求号：#27350
            $('.takeovertypestrategyview').hide();
            $('.autotakeoverview').hide();
            $('.takeoversafetyconfview').hide();
        }

        //接管方式
        if(_createMsgdata.takeover_object.takeover_mode == TAKEOVER_TYPE.COMPLETE_MACHINE &&  _backupTaskType == "backup"){
            let takeovermodecmlabel = $('.takeovermodecmlabel').text();
            $('.takeovertypestrategyshow').html(takeovermodecmlabel);  //整机接管方式
            $('.takeovernetworkconfview').hide();
        }else if(_createMsgdata.takeover_object.takeover_mode == TAKEOVER_TYPE.MOUNT || _backupTaskType == "copy"){
            let takeovermodemountlabel = $('.takeovermodemountlabel').text();
            $('.takeovernetworkconfview').show();
            $('.takeovertypestrategyshow').html(takeovermodemountlabel);  //挂载接管方式
            //接管网络配置
            let takeoverIpserviceMapStr = LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE;
            let takeoverIpMapListView = _createMsgdata.takeover_object.business_ip_map_view;
            _takeoverIpMapListView = takeoverIpMapListView;
            if(!takeoverIpMapListView || takeoverIpMapListView.length == 0){
                UIToastr.showWarning(LANG.UI_CM_TAKEOVER_CONFIGURE, LANG.UI_CM_CDP_TAKEOVER_NIC_CONF_TIPS);
            }else{
                takeoverIpserviceMapStr = "";
                for(var i=0;i<takeoverIpMapListView.length;i++){
                    takeoverIpserviceMapStr += " <span style ='font-weight:Bold;font-size:15px;' &nbsp;>&nbsp;</span>"+takeoverIpMapListView[i].conf_des+"<br>"
                }
            }
            $('.takeovernetworkconfshow').html(takeoverIpserviceMapStr);
        }

        //接管映射配置，如果是复制此处需要隐藏
        $('#takeovermappingshow').html("");  //清空历史配置
        if(_backupTaskType == "copy"){
            $('.takeovermappingview').hide();
        }else {
            if(takeoverFlag ==CONF.FLAG.SET){
                $('.takeovermappingview').show();
                // let takeoverModeValue = $('input[name="cm_takeover_mode"]:checked').val();
                // _takeoverModeValue = takeoverModeValue;
                let sourceDiv = document.getElementById('vmDetailConfigInfo');  // 获取源div和目标div的引用  --默认挂载接管配置view
                let destinationDiv = document.getElementById('takeovermappingshow');
                if(_takeoverModeValue==1){  //整机接管
                    sourceDiv = document.getElementById('takeoverBuiltInVmConfDes');  // 获取源div和目标div的引用
                    var clone = sourceDiv.cloneNode(true);
                    var innerHTML = clone.innerHTML;
                    innerHTML = innerHTML.replace(new RegExp('plr15', 'g'), 'ml-55')
                    $('#takeovermappingshow').html(innerHTML);
                }else{
                    var clone = sourceDiv.cloneNode(true);
                    destinationDiv.appendChild(clone);
                }
                $('#takeovermappingshow').addClass("col-md-12");
            }
        }
        //回切通讯IP配置
        // if(_isHideSwitchBackIpFlag){
        //     $('.takeoverfailbackview').hide();
        // }else{
        //     $('.takeoverfailbackview').show();
        let takeoverfailbackupip = $('.takeoverfailbackupip').val();
        if(takeoverfailbackupip==""){
            takeoverfailbackupip = LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE;
        }

        $('.takeoverfailbackshow').html(takeoverfailbackupip);
        // }

        //安全配置信息汇总
        // $('.takeoversafetyconfshow').html(_createMsgdata.safe_config_strategy.virus_scan_config_list[0].scan_entire_system_flag);
        $('.takeoversafetyconfshow').html(_createMsgdata.safe_config_strategy.virus_str);


        //应用接管
        let appTakeoverFlag = CONF.FLAG.UNSET;
        if( _createMsgdata.takeover_object.app_takeover_flag){
            appTakeoverFlag = _createMsgdata.takeover_object.app_takeover_flag;
        }
        $('.apptakeovershow').html(getSwitchDes(appTakeoverFlag));
        //自动接管
        let autoTakeoverFlag = CONF.FLAG.UNSET;
        $('.autotakeoverview').hide();
        $('.autotakeoverconfview').hide();
        if( _createMsgdata.takeover_object.auto_takeover_flag && CONF.VENDOR != "sangfor"){  //深信服OEM版本只支持整机接管，需要屏蔽接管平台和自动接管的相关配置，对应需求号：#27350)
            $('.autotakeoverview').show();
            autoTakeoverFlag = _createMsgdata.takeover_object.auto_takeover_flag;
            if(autoTakeoverFlag == CONF.FLAG.SET){
                $('.autotakeoverconfview').show();
            }
        }
        $('.autotakeovershow').html(getSwitchDes(autoTakeoverFlag));

        //自动接管配置
        let heartBeatFailureTimeLabel = $('.agentheartbeatfailuretimelabel').html();
        let apptakeoverlabel  = $('.apptakeoverlabel').html(); //应用故障检测
        let appfailurenumberlabel = $('.appfailurenumberlabel').html();  //连续故障次数
        let takeoverappintervallabel = $('.takeoverappintervallabel').html();  //故障检测间隔

        let agentHeartbeatFailureTime = $('#agent_heartbeat_failure_time').val();
        let des = heartBeatFailureTimeLabel + ": " +  agentHeartbeatFailureTime +" " + LANG .UI_PUBLIC_SECOND +" <br>";
        des += apptakeoverlabel + ": " + getSwitchDes(_createMsgdata.takeover_object.app_monitor_switch) + " <br>";

        if(_createMsgdata.takeover_object.app_monitor_switch==CONF.FLAG.SET){
            let appFailureNumber = $('#app_failure_number').val();
            let takeoverAppInterval = $('#takeover_app_interval').val();
            des += appfailurenumberlabel + ": " + appFailureNumber + " " + LANG.UI_PUBLIC_UNIT_COUNT + " <br>";
            des += takeoverappintervallabel + ": " + takeoverAppInterval  + " " + LANG.UI_PUBLIC_SECOND;
        }
        $('.apptakeoverconfshow').html(des);

    }

    // 获取传输策略配置信息并展示对应配置信息
    let getTransportinfoConf = function () {

        var openstacktranslabel = $('.transferlabel').html();
        var encrypttransferlabel = $('.encrypttransferlabel').html();
        var transfernetworklabel = $('.transfernetworklabel').html();

        var transferCompress = $('.transfercompress').html();
        var transferThreadLabel = $('.transferthread').html();
        var transferDatapackageLabel = $('.transferdatapackage').html();

        var transmodeStr = $('#transport_mode option:selected').text();
        var des = openstacktranslabel + ": " +  transmodeStr +"<br>";

        var compressValue = _createMsgdata.highInfo.transfer.compress;
        var encrypttransfer = _createMsgdata.highInfo.transfer.encrypt;
        // 传输加密算法
        if($('#encrypttransfer').get(0).checked){
            let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
            let method = parseInt($('#transferEncryptMethod').val());
            let grade = '';
            switch (method) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                    break;
            };
            // des += encryptedMethodLabel + ": " + grade + '<br>';  //根据 bug #14005需求暂时屏蔽该功能
        }
        //资源池环境，复制任务类型或接管对象为挂载接管模式下，均不显示传输网络
        if(networkFlag && (_createMsgdata.node_pool_uuid=="" || _createMsgdata.storage_pool_uuid=="" ) && _createMsgdata.takeover_object.takeover_mode !=TAKEOVER_TYPE.MOUNT && _backupTaskType !="copy"){
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect')
            des += transfernetworklabel + ": " + networkNode.str + "<br>";
        }
        des += encrypttransferlabel + ": "+getSwitchDes(encrypttransfer) + "<br>";
        des += transferCompress + ": " + getSwitchDes(compressValue) + "<br>";
        if(compressValue){
            let encryptedMethodLabel = $('.transfer-compress-grade-label').html();
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
            des += encryptedMethodLabel + ": " + encryptegradestr + '<br>';  //根据 bug #14005需求暂时屏蔽该功能
        }
        if (CONF.FUNCTIONS.includes('multithread')) {
            des += transferThreadLabel +": " + _createMsgdata.thread_num + LANG.UI_PUBLIC_NUM+"<br>";
        }
        var transferDatapackageValue = $('#transfer_datapackage_size option:selected').text();
        des += transferDatapackageLabel +": " + transferDatapackageValue + "<br>"
        $('.transportinfoshow').html(des);
    }

    //获取通用策略配置描述信息汇总
    let generalStrategyConfDesc = function(){
        //标签策略信息汇总
        // let tagPointList = _createMsgdata.backup_info.tagInfo;
        // var tagPointLimitStr = '';
        // if(tagPointList.length !=0){
        // 	for(var i=0;i<tagPointList.length;i++){
        // 		tagPointLimitStr += tagPointList[i].des + '<br>';
        // 	}
        // }else{
        // 	tagPointLimitStr = LANG.UI_PUBLIC_NOTHING;
        // }
        let backupTimeShow = $('.tagpointinfoshow');
        let backupTimeStr = '';
        backupTimeStr = $('.backupTimeDes').prop('title')
        let backupTimeArray =  $.trim(backupTimeStr).split(").");
        let tagPointLimitStr = "";
        for(let i=0; i<backupTimeArray.length; i++){
            if(i == backupTimeArray.length-1){
                tagPointLimitStr += "<p>"+backupTimeArray[i] + "<p>";
            }else{
                tagPointLimitStr += "<p>"+backupTimeArray[i]+")" + "<p>";
            }
        }
        if (backupTimeStr == '') {
            tagPointLimitStr = LANG.UI_PUBLIC_NOTHING;
        }
        backupTimeShow.html(tagPointLimitStr);

        //限速策略
        let speedLimitShow = $('.speedlimitshow');
        let speedlimitsStr = $('.speedlimitDes').prop('title');
        let resultArray = $.trim(speedlimitsStr).split("/s.");
        let speedLimitHtml = "";
        for(let i=0; i<resultArray.length; i++){
            speedLimitHtml += "<p>"+resultArray[i] + "<p>";
        }
        if (speedlimitsStr == '') {
            speedLimitHtml = LANG.UI_PUBLIC_NOTHING;
        }
        speedLimitShow.html(speedLimitHtml);

        //存储策略
        let storStrategy = _createMsgdata.highInfo.store;
        let storeInfo = "";
        let compresslabel = $('.compressLabel').html();
        let encryptStoragelabel = $('.encryptStorageLabel').html();
        let passwordAutolabel = $('.passwordAutoLabel').html();

        storeInfo += compresslabel + ": " + getSwitchDes(storStrategy.compress) + "<br>";
        storeInfo += encryptStoragelabel + ": " + getSwitchDes(storStrategy.dataencrypt);
        if(storStrategy.dataencrypt){
            storeInfo += "<br>" + passwordAutolabel + ": " + getSwitchDes(storStrategy.password_auto_flag);
        }
        $('.storageinfoshow').html(storeInfo);

        //保留策略
        if(CONF.RESERVE_TYPE.NUM == _createMsgdata.highInfo.reserve.type){
            reservetypeStr = LANG.UI_STRATEGY_RESERVE_NUM;
        }else if(CONF.RESERVE_TYPE.DAY == _createMsgdata.highInfo.reserve.type){
            reservetypeStr = LANG.UI_STRATEGY_RESERVE_DAY;
        }
        reservetypeStr = reservetypeStr + "," + LANG.UI_STRATEGY_RESERVE_VALUE + _createMsgdata.highInfo.reserve.value;
        $('.reservetypeshow').html(reservetypeStr);

    }

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
            jQuery('li', $('#completeMachineVolcdpbackupcontent')).removeClass("done");
            let li_list = navigation.find('li');
            for (let i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#completeMachineVolcdpbackupcontent').find('.button-previous').css('visibility', 'hidden');
            } else {
                $('#completeMachineVolcdpbackupcontent').find('.button-previous').css('visibility', 'visible');
            }

            if (current >= total) {
                $('#completeMachineVolcdpbackupcontent').find('.button-next').hide();
                $('#completeMachineVolcdpbackupcontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#completeMachineVolcdpbackupcontent').find('.button-next').show();
                $('#completeMachineVolcdpbackupcontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#completeMachineVolcdpbackupcontent').bootstrapWizard({
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
                $('#completeMachineVolcdpbackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#completeMachineVolcdpbackupcontent').find('.button-previous').css('visibility', 'hidden');
        $('#completeMachineVolcdpbackupcontent .button-submit').click(submit).css('visibility', 'hidden');

    };

    let loadAgentDiskInfo = ()=>{
        var options = {
            agent_uuid: _agentuuid, // 代理uuid(必传)
            agent_name: _agentName, //代理名称
            check_flag: true,   // 勾选状态,勾选还是取消勾选
            relation_flag: true, // 关联选择
            show_collapse: true, //是否展开
            show_title: false, // 是否显示标题
            blockUI: '#tab1',
        };
        if (EditFlag && _createMsgdata.agentUUID == oldInfo.backup_oss_info.agent_uuid) {
            //获取排除磁盘列表
            var exclude_uuid_list = [];
            for (let index = 0; index < oldInfo.backup_oss_info.exclude_devices_list.length; index++) {
                exclude_uuid_list.push(oldInfo.backup_oss_info.exclude_devices_list[index].dev_node_uuid)
            }
            options.exclude_uuid_list = exclude_uuid_list;
            // 关联选择
            options.relation_flag = oldInfo.backup_oss_info.disk_struct_backup_mode;
        }
        options.callback = function (){
            if (EditFlag) {
                backupCheckedData = $("#checkedHostDiskSourceDiv").methodVinBackupSource('getCheckedData');
                backupinfo = $("#checkedHostDiskSourceDiv").methodVinBackupSource('getData');
                let treeObj = $.fn.zTree.getZTreeObj(_agentuuid + '_ztree_id');
                // 记录下此时之前的备份的磁盘
                oldDataSourceDiskInfo = [];
                // 获取所有节点
                let allNodes = treeObj.transformToArray(treeObj.getNodes());
                // 遍历并禁用所有节点的复选框
                allNodes.forEach(function(node) {
                    if (node.checked) {
                        oldDataSourceDiskInfo.push(node.id);
                    }
                    // treeObj.setChkDisabled(node, true);  // 第二个参数 true 表示禁用
                });
            }
        }
        var monitorData = [options]
        $('#checkedHostDiskSourceDiv').html('');
        $("#checkedHostDiskSourceDiv").initBackupSource(monitorData);
    };

    var inintDatatimePicker = function(){
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            //英文独有的
            $(".form_datetime").datetimepicker({
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-mm-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }else{
            $(".form_datetime").datetimepicker({
                language:  'zh-CN',
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-MM-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }
    }

    //提交任务创建相关配置参数
    let submit = function(){
        let info = {};
        if (EditFlag) {
            info.task_uuid = oldInfo.job_uuid;
            info.old_node_uuid = oldInfo.node.node_uuid;
        }
        info.task_name = $('.cmcdptaskname').val();
        let jobName = $.trim($(".cmcdptaskname").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
        let ignoreResourceLimitingFlag = CONF.FLAG.UNSET;
        if($('#ignoreResourceLimit').is(':checked')){  //忽略资源限制
            ignoreResourceLimitingFlag = CONF.FLAG.SET;
        }
        info.time_strategy_list = [];
        info.high_info = _createMsgdata.highInfo;
        info.is_replication = _createMsgdata.is_replication;
        info.node_uuid = _createMsgdata.node_uuid;
        info.safe_config_strategy = _createMsgdata.safe_config_strategy;
        info.script_list = _createMsgdata.script_list;
        info.tag_list = _createMsgdata.backup_info;
        info.speed_info = [];
        info.speed_limit = _createMsgdata.speed_limit;
        info.dev_type = _createMsgdata.dev_type;
        // 忽略节点资源限制
        info.ignore_resource_limiting_flag = ignoreResourceLimitingFlag;
        info.master_agent_uuid = _createMsgdata.agentUUID;
        info.backup_object = _createMsgdata.backup_object;  //备份对象
        info.replication_object = _createMsgdata.replication_object;  //复制相关配置
        info.breakpoint_resume = _createMsgdata.backup_object.auto_fault_resume_flag;
        info.takeover_object = _createMsgdata.takeover_object;
        info.cache_config = _createMsgdata.cache_config;
        info.high_pressure_strategy = _createMsgdata.high_pressure_strategy; //高负载配置
        info.thread_num = _createMsgdata.thread_num;
        info.storage_block_size = _createMsgdata.storage_block_size;
        info.transport_block_size = _createMsgdata.transport_block_size;
        info.full_backup = _createMsgdata.full_backup;
        info.skip_bad_block = _createMsgdata.skip_bad_block;
        info.all_disk_list = _allDiskList;  //主机磁盘逻辑结构扫描结果，传json格式的字符串
        info.resource_protect_config = _createMsgdata.resource_protect_config;  // 自动降级优化配置
        Metronic.blockUI({target: ".cmbackuptaskconf",animate: true});
        pAjaxRequest(info, "/api/v1/complete_machine_volcdp/backup/create_job", "post", function (result) {
            Metronic.unblockUI(".cmbackuptaskconf");
            $(".button-submit").removeClass('disabled');  //移除button禁用样式
            $(".button-submit").css("pointer-events", "auto"); //移除button不可点击样式
            if(result.success){
                UIToastr.showSuccess(result.title,result.message);
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }else{
                UIToastr.showWarning(result.title,result.message);
            }
        });

    }

    //得到任务名,这里需要注意的是在跨虚拟化平台恢复的时候需要用目的地的名字,
    //这里是需要在选择目的地节点后再初始化.
    let getTaskName = function(){
        let defaultTaskname = LANG.UI_CM_CDP_BACKUP_DEFAULT_NAME;
        if(_backupTaskType=="copy"){
            defaultTaskname = LANG.UI_VOL_CDP_COPY_TASK_NAME;
        }
        let info = {};
        info.task_type = CONF.MODULE_TYPE.VOL_CDP;
        info.task_name = defaultTaskname

        pAjaxRequest(info, "/api/v1/complete_machine_volcdp/default_task_name", "GET", function (result) {
            if (result.success) {
                $('.cmcdptaskname').val(result.data['task_name']);
            }
        }, false);

    }

    /**
     * @function 生成uuid
     */
    var getUuid = function() {
        var len = 36;//36长度
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if(len) {
            for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
        } else {
            var r;
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
     * @function 校验IP
     * @description 校验IP格式是否合法
     * @return  true or false
     */
    let checkIP = function (value){
        return ipV4V6(value);
    }
    //声明和定义Spinner控件
    var initSpinner = function(){
        $('.takeover_heartbeat_interval_div').spinner({value:60, step: 1, min: 1, max: 3600});
        $('.takeover_app_interval_div').spinner({value:30, step: 1, min:5, max: 3600});
        $('.app_failure_number_div').spinner({value:3, step: 1, min: 1, max: 100});

        $('.exec_script_interval_div').spinner({value:30, step: 1, min: 1, max: 3600});
        $('.script_failure_number_div').spinner({value:1, step: 1, min: 1, max: 100});

        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
        $('#spinnerDay').spinner({value:7, step: 1, min: 1, max: 9999});
        $('#transfer_thread_div').spinner({value:1, step: 1, min: 1, max: 4});
        $('#transfer_datapackage_div').spinner({value:4, step: 1, min: 1, max: 1024});
        $('#memory_cache_div').spinner({value:512, step: 1, min: 1, max: 10000000000});
        $('.deduplicationDiv').hide();
        // 默认关闭压缩等级
        $('#compressCheck').bootstrapSwitch('state', false);
        // $('.compressGradeDiv').hide();
    }

    // 获取修改信息
    var getOldInfo = function (){
        //获取uuid是否有值,如果有值则表示是修改
        var uuid = $("#uuid").val();
        if(uuid != "" && uuid != undefined && uuid != null){
            EditFlag = true;
            //修改备份任务
            var requestData  = function(data){
                if(data.success){
                    oldInfo = data.data;
                    //只有修改任务有task_uuid
                    _createMsgdata.task_uuid = oldInfo.taskuuid;
                    iniStart();
                }else{
                    UIToastr.showWarning(LANG.UI_MACHINE_OS_GET_BACKUP_INFO, data.message);
                }
            }
            pAjaxRequest({job_uuid:uuid}, '/api/v1/complete_machine_volcdp/job_info', "GET", requestData, true);
        } else {
            iniStart();
        }
    }

    // 递归的去比较数组元素
    function arraysDeepEqual(arr1, arr2) {
        if (!Array.isArray(arr1) || !Array.isArray(arr2) || arr1.length !== arr2.length) {
            return false;
        }
        for (let i = 0; i < arr1.length; i++) {
            if (!deepEqual(arr1[i], arr2[i])) {
                return false;
            }
        }
        return true;
    }
    // 比较数组
    function deepEqual(obj1, obj2) {
        if (obj1 === obj2) return true;

        if (typeof obj1 !== 'object' || obj1 === null || typeof obj2 !== 'object' || obj2 === null) {
            return false;
        }

        const keys1 = Object.keys(obj1);
        const keys2 = Object.keys(obj2);

        if (keys1.length !== keys2.length) {
            return false;
        }

        for (let key of keys1) {
            if (!keys2.includes(key) || !deepEqual(obj1[key], obj2[key])) {
                return false;
            }
        }

        return true;
    }

    // 开始初始化数据
    var iniStart = function (){
        initAgentTree();
        initListener();
        initSpinner();
        initDataChangeListeners(); //初始化备份策略事件
    }

    // 判断授权个数是否充足
    var checkAuth = function () {
        getModuleAuthInfo({module: 'cdp'}).then(function (flag){
            isLicense = flag;
        });
    }

    return {
        init: ()=>{
            checkAuth();
            wizardInit();
            getOldInfo();
        }
    };
}();

jQuery(document).ready(
    function (){
        completeMachineVolcdp.init();
    }
)
