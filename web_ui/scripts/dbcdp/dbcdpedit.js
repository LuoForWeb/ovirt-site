var Dbcdpbackup = function () {
    /*
      ##特别注意:
      1:源机和目标机的限制情况
      2:回切通讯IP和网卡配置什么情况下能配置
     */

    // 全局变量
    //----------------STEP1----------------
    // 管理节点uuid
    let nodeUuid = '';
    // 源机树(所有)
    let zTreeAgent= [];
    // 存储选择源的uuid
    let agentList = [];
    // 选中的源机树节点
    let sourcetreeNode = {};
    // 目标机树(所有)
    let zTreetTargetAgent = [];
    // 选择的源树节点
    let zTreeDB = [];
    // 源机的应用uuid/集群uuid
    let selectedBackupUuid = '';
    // 源机客户端对应的网卡信息
    let agentNetworkInfo = [];
    // 源机的应用信息
    let sourceAgentAppInfo = {};
    // 目标机的客户端信息
    let targetAgentAppInfo = {};
    // 可选备机数组
    let targetArr = [];
    //----------------STEP2----------------
    // 源映射树
    let sourceInstanceUserTree = [];
    // 目标映射树
    let targetInstanceUserTree = [];
    // 选择的目标树节点
    let targettreeNode = {};
    // 扫描源用户
    let sourceScanUserFlag = true;
    // 扫描源用户错误信息
    let sourceScanUserTip = '';
    // 备机客户端对应的网卡信息
    let standbyNetworkInfo = [];
    // 备机网关设置
    let standbyGatewayConf = [];
    let _takeoverIpMapList =[];
    let _takeoverIpMapListView = [];
    // 映射ip列表
    let ipMapInfoList = [];
    // 自动接管标志
    let autoTakeoverFlag = false;
    // 回切通讯IP显示标志
    let isIpShow = false;
    // 回切通讯IP测试
    let ipCheckFlag = false;
    // 网卡配置显示标志
    let isNetWorkShow = false;
    // 应用有效标志
    let appValidFlag = false;

    // 之前的设置
    let SETTINGS;

    let editFlag = false;

    // 存储所有选择的IP映射列表
    let allIpMapInfoList = [];


    // 服务IP漂移开关
    let switchIpFlag = false;
    // 延迟加载时间标志
    let delayedLoadFlag = false;
    // 源机文件默认缓存路径标志
    let sourcefilePathDefault = true;
    // 备机文件默认缓存路径标志
    let backupfilePathDefault = true;
    let _failbackIpVerify = false;
    // 存储选择目标的uuid
    let targetAgentList = [];
    let encryptMethod = {
        'RSA':1,
        'SM2':2,
    };

    let compressMethod = {
        '极速':1,
        '标准':2,
        '最大':3,
        '极限':4,
    };

    // 限速策略
    let speedList = [];
    let node_pool_uuid = '';
    let network_uuid = '';

    let backupTargetInfo;
    let networkFlag = false;
    let net_model = '';
    let ip_scope_info = '';
    // 消息结构
    let data = {
        "job_uuid":"",
        "job_name":"",
        "module_type":CONF.MODULE_TYPE.DBCDP,
        "task_type":CONF.TASK_TYPE.DB_CDP_BACKUP,
        "auto_takeover_flag":false,
        "exp_max_process_num":"",
        "imp_max_process_num":"",
        "sync_object":{
            "source_app_uuid": "",
            "target_app_uuid":"",
            "source_agent_uuid": "",
            "target_agent_uuid":"",
            "source_cluster_flag": false,
            "target_cluster_flag":false,
            "sync_direction":1,
            "sync_level":1,
        },
        "takeover_object":{
            "switch_ip_flag":false,
            "failback_target_ip":"",
            "app_consecutive_failure_num":"",
            "app_fault_detection_interval":"",
            "agent_heartbeat_failure_time":"",
            "takeover_business_ip_map":[],
        },
        'cache_config': {
            "source_file_cache_alloc_space": 0,
            "source_file_cache_storage_path": "",
            "file_cache_alloc_space": 0,
            "file_cache_alloc_space_text": '',
            "file_cache_storage_path": '',
            "delay_load_time": 0,
        },
        "transport_strategy":{
            "encrypt_flag": false,
            "compress_flag": false,
            "network_uuid":"",
            "transport_compress_method": 0,
            'transport_encrypt_method': 0,
            "block_size": 0,
        },
        "node_uuid":"",
        "node_text":"",
        "app_tablespace_dir":"",
        "extend_advanced_config":{
            "use_dbms":false,
            "timed_gen_txn":true,
            "sync_big_object":true,
            "replay_ddl":true,
            "ignore_nonsupport_data_type": true,
        },
        "node_pool_uuid":"",
        "ignore_resource_limiting_flag":false
    }

    /**
     * 步骤流程
     */
    const wizardInit = function () {
        if (!jQuery().bootstrapWizard) {
            return;
        }
        let form = $('#submit_form');
        let error = $('.alert-danger', form);
        let success = $('.alert-success', form);
        let handleTitle = function (tab, navigation, index) {
            let total = navigation.find('li').length;
            let current = index + 1;
            jQuery('li', $('#dbcdpbackupcontent')).removeClass("done");
            let li_list = navigation.find('li');
            for (let i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#dbcdpbackupcontent').find('.button-previous').css('visibility', 'hidden');
                $('#dbcdpbackupcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#dbcdpbackupcontent').find('.button-previous').css('visibility', 'visible');
                $('#dbcdpbackupcontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#dbcdpbackupcontent').find('.button-next').hide();
                $('#dbcdpbackupcontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#dbcdpbackupcontent').find('.button-next').show();
                $('#dbcdpbackupcontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }
        // default form wizard
        $('#dbcdpbackupcontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch (index) {
                    case 1:
                        if (step1Valid() == false) {
                            return false;
                        }
                        break;
                    case 2:
                        if (step2Valid() == false) {
                            return false;
                        }
                        break;
                    case 3:
                        if (step3Valid() == false) {
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
                $('#dbcdpbackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#dbcdpbackupcontent').find('.button-previous').css('visibility', 'hidden');
        $('#dbcdpbackupcontent .button-submit').click(submit).css('visibility', 'hidden');
    };


    /**
     * 监听事件
     */
    const initListeners = () => {
        // 数据表空间存储目录
        $('#tablespaceDir').on('change',changeTableSpaceDirSelect);

        // 自动接管开关
        $('#autoTakeOver').on('switchChange.bootstrapSwitch',showTakeoverConfig);

        // 服务IP漂移
        $('#serviceIpDrift').on('switchChange.bootstrapSwitch', showTakeoverNet);

        // 回切通讯ip
        $('#failbackTargetIp').off().blur(failbackTargetIpChange);

        // 配置接管网络
        $('#takeover_network_conf').on('click',takeoverNetworkConfModal);

        // 延迟加载关闭
        $('#delayedLoad').bootstrapSwitch('state', false); //自动接管默认关闭


        // 加密传输
        $('#encrypttransfer').on('switchChange.bootstrapSwitch', encryptTransfer)

        // 压缩传输
        $('#compresstransfer').on('switchChange.bootstrapSwitch', compressTransfer)

        // 打开延迟加载开关
        $('#delayedLoad').on('switchChange.bootstrapSwitch', showDelayTime);

        // 自定义源机文件缓存路径
        $('#source_customFileCachePath').on('click', clicksourceCustomFileCachePath);
        // 自定义备机文件缓存路径
        $('#backup_customFileCachePath').on('click', clickbackupCustomFileCachePath);

        // 默认源机文件缓存路径
        $('#source_defaultFileCachePath').on('click', clicksourcefaultFileCachePath);
        // 默认备机文件缓存路径
        $('#backup_defaultFileCachePath').on('click', clickbackupfaultFileCachePath);
        // 回切通讯IP检测
        $('#linkSelectIP').unbind('click').click(checkTakeoverResIp);
        // 文件缓存大小
        $('.unlimit_limit_toggle').off().on('click',toggleLimitMode);
        // 关闭抽屉
        $('#takeover_conf_drawer .cancel').on('click', function () {
            $('#takeover_conf_drawer').drawer('hide');
        });
    }

    /**
     * 初始化spinner
     */
    const initSpinner = function () {
        $('.heartFailureDiv').spinner({value:60, step: 1, min: 5, max: 3600});
        $('#heartFailure_num').off().on('blur',function (){
            var newValue = parseInt($(this).val());
            if(newValue < 5 || newValue > 3600 ){
                $('.heartFailureDiv').spinner('value', 5);
            }
        });
        $('.numberFailuresDiv').spinner({value: 3, step: 1, min: 1, max: 100});
        $('#Failures_num').off().on('blur',function (){
            var newValue = parseInt($(this).val());
            if(newValue < 1 || newValue > 100){
                $('.numberFailuresDiv').spinner('value', 1);
            }
        });
        $('.faultMonitorIntervalDiv').spinner({value: 30, step: 1, min: 5, max: 3600});
        $('#monitor_Interval').off().on('blur',function (){
            var newValue = parseInt($(this).val());
            if(newValue < 5 || newValue > 3600){
                $('.faultMonitorIntervalDiv').spinner('value', 5);
            }
        });
        $('#speedSpinnerNum').spinner({value: 1, step: 1, min: 1, max: 8});
        $('.exp_thread_div').spinner({value: 1, step: 1, min: 1, max: 8});
        $('.imp_thread_div').spinner({value: 1, step: 1, min: 1, max: 8});
        $('#exp_thread').off().on('blur',function (){
            var newValue = parseInt($(this).val());
            if(newValue < 1 || newValue >8){
                $('.exp_thread_div').spinner('value', 1);
            }
        })
        $('#imp_thread').off().on('blur',function (){
            var newValue = parseInt($(this).val());
            if(newValue < 1 || newValue >8){
                $('.imp_thread_div').spinner('value', 1);
            }
        })
        $('.source_fileCacheSizeDiv').spinner({value: 4, step: 1, min: 4, max: 1024});
        $('.backup_fileCacheSizeDiv').spinner({value: 64, step: 1, min: 4,max: 1024});
    }

    /**
     * 数据表空间存储目录
     */
    const showTableSpaceDir = function (){
        let tablespace_dir = SETTINGS.app_tablespace_dir;
        if(tablespace_dir !== ""){
            $('#tablespaceDir option[value="1"]').prop('selected', true);
            $('.customDirectory_div').show();
            $('#customDirectory').val(tablespace_dir);
        }

        if(!editFlag){
            // 新建状态下可修改
            $('#tablespaceDir').prop('disabled', true);
            $('#customDirectory').prop('disabled', true);
        }
    }

    /**
     * 切换数据表空间存储目录
     */
    const changeTableSpaceDirSelect = function (){
        let tablespaceDirSelectValue = $('#tablespaceDir option:selected').val();
        if(tablespaceDirSelectValue == '1'){
            // 自定义目录
            $('.customDirectory_div').show();
            $('#customDirectory').val('');
        }else{
            $('.customDirectory_div').hide();
        }
    }

    /**
     * 自动接管开关
     */
    const showTakeoverConfig = function(event,state){
        // 未选择备机不能打开自动接管开关
        if(Object.keys(targettreeNode).length == 0){
            // 判断空对象
            $('#autoTakeOver').bootstrapSwitch('state', false, true);
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC, LANG.UI_VOL_CDP_BACKUP_STANDBY_MACHINE_SELECT);
            return;
        }
        if(state && delayedLoadFlag){
            $('#autoTakeOver').bootstrapSwitch('state',false);
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC, LANG.UI_DB_CDP_BACKUP_LOAD_TIME_TIP);
            return;
        }
        autoTakeoverFlag = state;
        if (autoTakeoverFlag) {
            $("#takeoverConfigContent").show();
        } else {
            $("#takeoverConfigContent").hide();
        }
    }

    /**
     * 回切通讯IP、网卡配置显示
     */
    const showTakeoverNet = function (){
        data.takeover_object.switch_ip_flag = this.checked;
        if (this.checked) {
            $('.takeoverNetConf').show();
            switchIpFlag = true;
            // 源机为单节点才会显示回切通讯IP
            if(!data.sync_object.source_cluster_flag){
                isIpShow = true;
                $('#failbackTargetIpContent').show();
            }
            // 显示网络配置
            isNetWorkShow = true;
            $('#takeover_ip_map_list').empty();
        } else {
            data.takeover_object.takeover_business_ip_map = [];
            switchIpFlag = false;
            isIpShow = false;
            isNetWorkShow = false;
            $('#failbackTargetIpContent').hide();
            $('.takeoverNetConf').hide();
        }
    }

    /**
     * 接管网络配置
     */
    const takeoverNetworkConfModal =  () =>{
        if(data.sync_object.source_cluster_flag){
            // 源是集群
            scanIP(data.sync_object.source_agent_uuid,data.sync_object.source_app_uuid);
        }else{
            initTakeoverConfig();
        }
        // 提交配置
        $("#submit_host_network_conf").unbind('click').click(submitHostNetworkConf);
    }

    /**
     * 获取配置网卡描述信息，并渲染配置
     */
    const submitHostNetworkConf = () =>{
        $('#takeover_conf_drawer').drawer('hide');
        let _standbyGatewayConf = getStandbyGateWayConf();
        let is_cluster = _standbyGatewayConf[0].is_cluster;  // 备机集群网卡
        var checkIp = checkIpFormat(_standbyGatewayConf);
        if (!checkIp) {
            _takeoverIpMapList = [];
            UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_STANDBY_DEFAULT_NETCARD_FOFMAT_ERROR);
            return false;
        }

        var standbyNetCardConfList = standbyNetCardConf();
        var ipMapInfoList = [];
        _takeoverIpMapListView = [];
        var selectedNetCards = 0;  // 新增变量，用于跟踪选中的网卡数量

        for (var i = 0; i < agentNetworkInfo.length; i++) {
            var ipSetStr = "";
            var ipMapInfo = {};
            var nicIpList = [];
            var agentMapInfo = {};
            ipMapInfo.nic_gateway = agentNetworkInfo[i].gateway_address;
            ipMapInfo.nic_name = agentNetworkInfo[i].name;
            ipMapInfo.nic_mac = agentNetworkInfo[i].mac_address;

            var ipSet = agentNetworkInfo[i].ip_set;
            if (!ipSet) {
                break;
            }

            var liId = getUuid();
            var hostMac = $("#host_network_mac_" + i).text();
            //当前选中主机网卡名
            var activeNetCardText = $('#hostNetCardTab').find('li.active').filter(function () {
                return $(this).closest('ul').is('ul');
            }).text();

            for (var j = 0; j < ipSet.length; j++) {
                var nicIpInfo = {};
                var standbyNetCardSelect = "standby_network_card_" + i + j;
                var targetHostMacStr = $("#" + standbyNetCardSelect).find("option:selected").attr("host-mac");
                var targetHostMac = $("#" + standbyNetCardSelect).val();

                var hostIp = $("#" + standbyNetCardSelect).find("option:selected").attr("host-ipdrr");
                var netMask = $("#" + standbyNetCardSelect).find("option:selected").attr("agent-netmask");
                var targetGateway = $("#" + standbyNetCardSelect).find("option:selected").attr("standby-gateway");
                var targetNicName = $("#" + standbyNetCardSelect).find("option:selected").text();
                var ip_type = $("#" + standbyNetCardSelect).find("option:selected").attr("ip_type");
                var prefix = $("#" + standbyNetCardSelect).find("option:selected").attr("prefix");

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
                    var targetGatewayStr = targetGateway || "--";
                    nicIpInfo.target_gateway = targetGateway;
                    nicIpInfo.ip_type = ip_type;
                    nicIpInfo.prefix = prefix;
                    nicIpList.push(nicIpInfo);
                    var gatewayStr = "(" + LANG.UI_DRILLS_NETWORK_WAY + ":" + targetGatewayStr + ")";
                    if(is_cluster){
                        ipSetStr += hostIp;
                    }else{
                        ipSetStr += hostIp + gatewayStr + LANG.UI_VOL_CDP_HOST_IP_MAP_TO_STANDBY + ":" + targetNicName + "  ";
                    }
                }
            }

            if (nicIpList.length > 0) {
                ipMapInfo.nic_ip_info = nicIpList;
                agentMapInfo.agent_map_info = ipMapInfo;
                agentMapInfo.conf_id = liId;
                agentMapInfo.conf_des = LANG.UI_VOL_CDP_HOST_NETCARD + ": " + agentNetworkInfo[i].name + " [" + ipSetStr + "] ";
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
            $('#takeover_ip_map_list li').remove();
            hostGatewayConfDes();
        }

        $('#takeoverNetworkConfModal').modal('hide');
    }

    /**
     * 校验网关格式
     */
    const checkIpFormat =  () =>{
        var ipVerify = true;
        for(var i=0;i<standbyGatewayConf.length;i++){
            var gateway = standbyGatewayConf[i].gateway;
            if(gateway=="" && !ipV4V6(gateway)){
                ipVerify = false;
                break;
            }
        }
        return ipVerify;
    }

    /**
     * 获取备机网卡信息，用于替换主机映射网卡中的备机网关
     * @returns {*[]}
     */
    const standbyNetCardConf = () =>{
        let standbyNetCardInfo = {};
        let standbyNetCardList = [];
        for(let i = 0;i<standbyNetworkInfo.length;i++){
            let standbyGatewaySelectId = "standby_netcard_macaddress_"+standbyNetworkInfo[i].name;
            let selectNicname = $("#"+standbyGatewaySelectId).find("option:selected").attr("agent-nicname");
            let selectGateway = $("#"+standbyGatewaySelectId).val();
            standbyNetCardInfo.selectNicname = selectNicname;
            standbyNetCardInfo.selectGateway = selectGateway;
            standbyNetCardList.push(standbyNetCardInfo);
        }
        return standbyNetCardList;
    }

    /**
     * 获取配置网卡描述信息，并渲染
     */
    const hostGatewayConfDes = () =>{
        let des = "";
        data.takeover_object.takeover_business_ip_map = [];
        for(let i=0;i<_takeoverIpMapList.length;i++){
            let conf_id = _takeoverIpMapList[i].conf_id;
            des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+ _takeoverIpMapList[i].conf_id
                +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
                + _takeoverIpMapList[i].conf_des + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 80%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
                + _takeoverIpMapList[i].conf_des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="takeover_netcard_del'+  _takeoverIpMapList[i].conf_id +'" >'
                +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
            $('#takeover_ip_map_list').append(des);
            $('.takeoverNetcardTips').popover();	   //初始化tips
            ipMaplist = {};
            ipMaplist.nic_name = _takeoverIpMapList[i].agent_map_info.nic_name;
            ipMaplist.nic_gateway = _takeoverIpMapList[i].agent_map_info.nic_gateway;
            ipMaplist.nic_mac = _takeoverIpMapList[i].agent_map_info.nic_mac;
            ipMaplist.nic_ip_info = _takeoverIpMapList[i].agent_map_info.nic_ip_info;
            ipMaplist.nic_netmask = _takeoverIpMapList[i].agent_map_info.nic_ip_info[0].netmask;
            ipMaplist.conf_id = _takeoverIpMapList[i].conf_id;
            ipMaplist.conf_des = _takeoverIpMapList[i].conf_des;
            data.takeover_object.takeover_business_ip_map.push(ipMaplist);
            $('.takeover_netcard_del'+ conf_id ).bind("click",{index:i},clickHandler);
        }
    }

    /**
     * 为每一个<li>绑定点击事件，并处理移除数组操作
     */
    const clickHandler = (event) => {
        $('.popover.in').remove();
        var index = event.data.index;
        var confId;
        var list = _takeoverIpMapListView;

        // 修正删除最后一个元素的问题
        if (index >= list.length) {
            index = list.length - 1;
        }

        // 查找并删除 _takeoverIpMapListView 中的元素
        if (index >= 0) {
            confId = list[index].conf_id;
            $('#takeover_netcard_' + confId).remove();
            _takeoverIpMapListView.splice(index, 1);
            data.takeover_object.takeover_business_ip_map.splice($.inArray(confId, data.takeover_object.takeover_business_ip_map), 1);
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
     * 获取备机网卡配置
     */
    const getStandbyGateWayConf = () => {
        var list = [];
        for(var i = 0; i<standbyNetworkInfo.length;i++){
            var gatewayInfo = {};
            var hostMac = standbyNetworkInfo[i].mac_address
            hostMac = hostMac.replace(/:/g,'');
            gatewayInfo.netcard = standbyNetworkInfo[i].name;
            var hostGateway = $('#standby_netcard_'+hostMac).val();
            if(hostGateway==""){
                hostGateway = "0.0.0.0";
            }
            gatewayInfo.gateway = hostGateway;
            gatewayInfo.is_cluster = standbyNetworkInfo[i].is_cluster;
            list.push(gatewayInfo);
        }
        return list;
    }

    /**
     * 获取uuid
     * @return {string}
     */
    const getUuid =  () => {
        var len = 36;//36长度
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if (len) {
            for (i = 0; i < len; i++) uuid[i] = chars[0 | Math.random() * radix];
        } else {
            var r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for (i = 0; i < 36; i++) {
                if (!uuid[i]) {
                    r = 0 | Math.random() * 16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    }

    /**
     * 回切通讯IP失去焦点时
     * @return {boolean}
     */
    const failbackTargetIpChange =  () => {
        ipCheckFlag = false;
        let ip = $('#failbackTargetIp').val();
        if (!ipV4V6(ip) && ip.length > 0 || ip == '' && autoTakeoverFlag && !data.sync_object.source_cluster_flag) {
            UIToastr.showWarning(LANG.UI_DB_CDP_BACKUP_FAILBACK_IP_CONFIG, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP_MESSAGE);
            return false;
        }
    }

    /**
     * 加密开关显示
     */
    const encryptTransfer = function () {
        if (this.checked) {
            $(".encrypttransfermethoddiv").show();
        } else {
            $(".encrypttransfermethoddiv").hide();
        }
    }

    /**
     * 压缩开关显示
     */
    const compressTransfer = function () {
        if (this.checked) {
            $(".compressgradediv").show();
        } else {
            $(".compressgradediv").hide();
        }
    }

    /**
     * 延迟加载开关显示
     */
    const showDelayTime = function (event,state) {
        // 开始自动接管就不能打开延迟重放开关
        if(state && autoTakeoverFlag){
            $('#delayedLoad').bootstrapSwitch('state',false);
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC, LANG.UI_DB_CDP_AUTO_TAKEOVER_TIP2);
            return;
        }
        delayedLoadFlag = state;
        if (delayedLoadFlag) {
            $("#delayedLoadTimeDiv").show();
        } else {
            $("#delayedLoadTimeDiv").hide();
            data.cache_config.delay_load_time = 0;
        }
    }

    /**
     * 自定义源机文件缓存路径
     */
    const clicksourceCustomFileCachePath = () => {
        sourcefilePathDefault = false;
        $('#source_defaultFileCachePath').show();
        $('#source_customFileCachePath').hide();
        $('#source_fileCachePath').removeAttr("readonly");
        $('#source_fileCachePath').val("");
    }

    /**
     * 自定义备机文件缓存路径
     */
    const clickbackupCustomFileCachePath = () => {
        backupfilePathDefault = false;
        $('#backup_defaultFileCachePath').show();
        $('#backup_customFileCachePath').hide();
        $('#backup_fileCachePath').removeAttr("readonly");
        $('#backup_fileCachePath').val("");
    }

    /**
     * 默认源机文件缓存路径
     */
    const clicksourcefaultFileCachePath = () => {
        backupfilePathDefault = true;
        $('#source_defaultFileCachePath').hide();
        $('#source_customFileCachePath').show();
        $('#source_fileCachePath').prop("readonly", true);
        $('#source_fileCachePath').val("/opt/vinchin/agent/dbcdp_cache/");
    }

    /**
     * 默认备机文件缓存路径
     */
    const clickbackupfaultFileCachePath = () => {
        backupfilePathDefault = true;
        $('#backup_defaultFileCachePath').hide();
        $('#backup_customFileCachePath').show();
        $('#backup_fileCachePath').prop("readonly", true);
        $('#backup_fileCachePath').val("/opt/vinchin/agent/dbcdp_cache/");
    }

    /**
     * @function 校验IP
     * @description 校验IP格式是否合法
     * @return  true or false
     */
    let checkIP = function (value){
        return ipV4V6(value);
    }

    /**
     * @function 校验接管服务ip
     * @description IP input 失去焦点时判断IP是否合法，并且校验当前输入IP在网络环境内是否可用
     * @return  true or false
     */
    var checkTakeoverResIp = () => {
        _failbackIpVerify = false;
        let ip = $("#failbackTargetIp").val();
        if((!checkIP(ip))&& ip.length>0||ip == ""){
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP_MESSAGE);
            $('#failbackTargetIp').val('');
            return false;
        }
        let params = {};
        params.ip = ip;
        pAjaxRequest(params,'/api/v1/volcdp/check_ip_exist','GET',(result) => {
            let isExist = result.data.exists;
            ipCheckFlag = true;
            if(isExist){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP_MESSAGE);
                $('#failbackTargetIp').focus();
                $('#failbackTargetIp').css("border-color","#ff9800")
                _failbackIpVerify = false;
            }else{
                UIToastr.showSuccess(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP_USABLE_MESSAGE);
                _failbackIpVerify = true;
                $('#failbackTargetIp').css("border-color","#e5e5e5")
            }
            return true;
        });
    }

    /**
     * 切换备机文件缓存大小设置模式
     */
    let sourceToggleLimitModeFlag = false;
    let backupToggleLimitModeFlag = false;
    var toggleLimitMode = function () {
        let value = this.dataset.value;
        if(value == 'source_cache_button'){
            // 默认源机自定义设置缓存大小
            if(sourceToggleLimitModeFlag){
                $('.source_custom_cache_size_div').show();
                $('.source_cache_size_unlimited_div').hide();
                $('.source_unlimit_limit_toggle').html(LANG.UI_DB_CDP_BACKUP_SWITCH_TO_UNLIMITED);
                sourceToggleLimitModeFlag = false;
            }else{
                $('.source_custom_cache_size_div').hide();
                $('.source_cache_size_unlimited_div').show();
                $('.source_unlimit_limit_toggle').html(LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM);
                sourceToggleLimitModeFlag = true;
            }
        }else if(value == 'backup_cache_button'){
            // 默认备机自定义设置缓存大小
            if(backupToggleLimitModeFlag){
                $('.backup_custom_cache_size_div').show();
                $('.backup_cache_size_unlimited_div').hide();
                $('.backup_unlimit_limit_toggle').html(LANG.UI_DB_CDP_BACKUP_SWITCH_TO_UNLIMITED);
                backupToggleLimitModeFlag = false;
            }else{
                $('.backup_custom_cache_size_div').hide();
                $('.backup_cache_size_unlimited_div').show();
                $('.backup_unlimit_limit_toggle').html(LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM);
                backupToggleLimitModeFlag = true;
            }
        }
    }

    /**
     * 初始化switch的状态
     */
    let initExtendAdvancedConfig = (extend_advanced_config)=>{
        $('#use_dbms_Switch').bootstrapSwitch('state',extend_advanced_config.use_dbms);
        $('#sync_big_object_Switch').bootstrapSwitch('state',extend_advanced_config.sync_big_object);
        $('#timed_gen_txn_Switch').bootstrapSwitch('state',extend_advanced_config.timed_gen_txn);
        $('#replay_ddl_Switch').bootstrapSwitch('state',extend_advanced_config.replay_ddl);
        $('#ignore_nonsupport_data_type_Switch').bootstrapSwitch('state',extend_advanced_config.ignore_nonsupport_data_type);
    }

    /**
     * 初始化备份目的地
     */
    const initBackupTarget = () => {
        $('#backupTarget').backupTarget({
            hide_backup_storage_flag: true,  // 是否隐藏备份存储
            hide_tips_flag: true,            // 是否隐藏提示
            node_uuid: SETTINGS.node_uuid,
            node_pool_uuid: SETTINGS.node_pool_uuid,
        });
    };

    // -------------------------------------------------STEP1-------------------------------------------------
    /**
     * step1-初始化数据库类型选择(目前是写死了Oracle)
     */
    const initDbTypeSelect = function () {
        let dbType = $('#dbtype');
        let option = $("<option>").text('ORACLE').val('2');
        dbType.append(option);
    }

    /**
     * step1-初始化Oracle备份树
     */
    const initInstanceTree = function () {
        Metronic.blockUI({target: "#agent_tree", animate: true});
        let param = {db_type: CONF.DB_TYPE.ORACLE};
        // 获取实例列表
        pAjaxRequest(param, '/api/v1/db/instances', 'GET', res => {
            // 过滤出已经配置的实例
            let rows;
            if(SETTINGS.source_cluster_flag){
                // 集群
                rows = res.data.rows.filter(item => item.cluster_info.cluster_uuid  == SETTINGS.source_cluster_uuid);
            }else{
                rows = res.data.rows.filter(item => item.app_uuid  == SETTINGS.source_app_uuid);
            }
            setOracleBackupTree(rows,'#agent_tree',checkOracleBackupNode);
        });
    };

    /**
     * initInstanceTree的回调函数
     * @param instanceList
     */
    const setOracleBackupTree = (instanceList,id,checkedFn) => {
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
            $("#noagent").show();
            $(".vcenter-tree").hide();
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
                db_backup_info: standaloneNode.db_backup_info,
                auth_flag: standaloneNode.agent_info.authorization_module.dbcdp,
                db_cdp_backup_info:standaloneNode.db_cdp_backup_info  // 是否有任务运行
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
                    db_backup_info: instanceInfo.db_backup_info,
                    auth_flag: instanceInfo.agent_info.authorization_module.dbcdp,
                    db_cdp_backup_info:instanceInfo.db_cdp_backup_info
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
        let nodeSelected = zTreeAgent.getNodes();  //分组下的所有节点
        zTreeAgent.checkNode(nodeSelected[0].children[0], true, true, true);
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

    /**
     * Oracle节点选中了
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const checkOracleBackupNode = (ev, treeId, treeNode) => {
        sourcetreeNode = treeNode;
        net_model = treeNode.net_model;
        // 客户端连接服务端
        if(net_model == 2){
            networkFlag = true;
        }
        // 单选节点
        let allNodes = zTreeAgent.getCheckedNodes();
        for(let i = 0; i < allNodes.length; i++){
            zTreeAgent.checkNode(allNodes[i], false, false, false);
        }
        zTreeAgent.checkNode(treeNode, true, false, false);
        if (treeNode.eventtype === 'cluster') {
            // 显示集群
            data.sync_object.source_cluster_flag = true;
            selectedBackupUuid = treeNode.cluster_uuid;
            // 遍历集群下的单节点
            for(let node of treeNode.children){
                if(node.online_flag){
                    // 在线单节点
                    data.sync_object.source_app_uuid = node.app_uuid;
                    data.sync_object.source_agent_uuid = node.agent_uuid;
                    data.sync_object.source_cluster_flag = true;
                    data.sync_object.source_cluster_uuid = node.cluster_uuid;
                    break;
                }
            }
            addOracleBackupNodetList(treeNode);
            initOracleSelectedBackupNodeTree(treeNode);
        } else if (treeNode.eventtype === 'instance' && treeNode.pId ==='standalone') {
            // 消息结构
            data.sync_object.source_app_uuid = treeNode.app_uuid;
            data.sync_object.source_agent_uuid = treeNode.agent_uuid;
            data.sync_object.source_cluster_flag = false;
            data.sync_object.source_cluster_uuid = treeNode.cluster_uuid;
            selectedBackupUuid = treeNode.app_uuid;
            addOracleBackupNodetList(treeNode);
            initOracleSelectedBackupNodeTree(treeNode);
            // 源机为单节点，高级配置dbms接口只能为关闭状态
            $('#use_dbms_Switch').bootstrapSwitch('disabled', true);
        }

        // 获取源机的应用信息
        sourceAgentAppInfo.app_type = treeNode.app_type;
        sourceAgentAppInfo.app_version = treeNode.app_version;


        let divChildren = $('#allDbTree').children();
        //未选中任何节点展示提示信息
        if (!divChildren.length) {
            $('.notipsDiv').show();
            $('#dbtreediv').hide();
        } else {
            $('.notipsDiv').hide();
            $('#dbtreediv').show();
        }
    };

    /**
     * 选择目标机节点
     */
    const checkOraclesStandbyNode = (ev, treeId, treeNode) =>{
        $('.dbMapDetaildiv').show();
        $('.synNodediv').show();
        $('.standByTips').hide();
        $('#serviceIpDrift').bootstrapSwitch('state', false, true);
        $('.takeoverNetConf').hide();
        isIpShow = false;
        isNetWorkShow = false;
        $('#failbackTargetIpContent').hide();
        targettreeNode = treeNode;
        //接管服务器
        $('.takeoverConfigDes').html(targettreeNode.name);
        // 单选节点
        let allNodes = zTreetTargetAgent.getCheckedNodes(true);
        for(let i = 0; i < allNodes.length; i++){
            zTreetTargetAgent.checkNode(allNodes[i], false, false, false);
        }
        zTreetTargetAgent.checkNode(treeNode, true, false, false);
        targetAgentList = [];
        if (treeNode.eventtype === 'cluster') {  // 显示集群
            data.sync_object.target_cluster_flag = true;
            addInstanceMapList(treeNode);
            initInstanceUserTree(sourcetreeNode,true);
            initInstanceUserTree(treeNode,false);
            targetAgentList.push(treeNode.cluster_uuid);
            // 遍历集群下的单节点
            for(let node of treeNode.children){
                if(node.online_flag){
                    // 在线单节点
                    data.sync_object.target_app_uuid = node.app_uuid;
                    data.sync_object.target_agent_uuid = node.agent_uuid;
                    data.sync_object.target_cluster_flag = true;
                    // 应用类型相同 源机应用版本需大于等于主机
                    targetAgentAppInfo.app_type = node.app_type;
                    targetAgentAppInfo.app_version = node.app_version;
                    scanIP(node.agent_uuid,node.app_uuid);
                    let targetFlag =true;
                    loadClusterNetworkInfo(node.agent_uuid,targetFlag);
                    break;
                }
            }
        } else if (treeNode.eventtype === 'instance' && treeNode.pId ==='standalone') {
            // 消息结构
            addInstanceMapList(treeNode);
            initInstanceUserTree(sourcetreeNode,true);
            initInstanceUserTree(treeNode,false);
            loadStandbyNetworkInfo(treeNode.agent_uuid);
            data.sync_object.target_app_uuid = treeNode.app_uuid;
            data.sync_object.target_agent_uuid = treeNode.agent_uuid;
            data.sync_object.target_cluster_flag = false;
            targetAgentAppInfo.app_type = treeNode.app_type;
            targetAgentAppInfo.app_version = treeNode.app_version;
            targetAgentList.push(treeNode.app_uuid);
        }
        addDataSynNode(treeNode);
        contrastApp(sourceAgentAppInfo, targetAgentAppInfo);
    }

    /**
     * 显示目标机的映射列表
     * @param {*} treeNode
     */
    const addInstanceMapList = (treeNode) => {
        // 集群uuid 或 应用uuid
        let selectedBackupKey;
        if (treeNode.eventtype === 'cluster') {
            selectedBackupKey = treeNode.cluster_uuid;
        } else if (treeNode.eventtype === 'instance') {
            selectedBackupKey = treeNode.app_uuid;
        }
        $('#instanceMapList').empty();
        $('#sourceInstanceTree').empty();
        $('#targetInstanceTree').empty();
        if (treeNode.checked) {
            let agentContent =
                '<div id="dbsourceAgentDiv_' + selectedBackupUuid + '" class="add-list">' +
                '<div class="accordion fileAccordion">' +
                '<div class="panel panel-default">' +
                '<div class="panel-heading">' +
                '<h4 class="panel-title">' +
                '<a class="accordion-toggle accordion-toggle-styled collapsed popovers" ' +
                'style="display: inline-block; width: 99%;text-decoration: none;" data-container="body" ' +
                'data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".fileAccordion" ' +
                'href="#dbsourceAgentInfo_' + selectedBackupUuid + '" aria-expanded="true">' +
                '<span class="font-green-seagreen">' + LANG.UI_PUBLIC_HOST + ':' + sourcetreeNode.name + '</span>' +
                '</a>' +
                '</h4>' +
                '</div>' +
                '<div id="dbsourceAgentInfo_' + selectedBackupUuid + '" class="panel-collapse collapse in" ' +
                'style="height: calc(100% - 34px);">' +
                '<div class="panel-body" style="padding: 16px; max-height: 250px; overflow-y: auto">' +
                '<ul id="dbsourceAgentTree_' + selectedBackupUuid + '" class="ztree col-md-6" style="overflow: hidden">' +
                '<li>'+ LANG.UI_DB_CDP_BACKUP_SOURCE_DB +':</li>'+
                '</ul>' +
                '<ul id="dbtargetAgentTree_' + selectedBackupKey + '" class="ztree col-md-6" style="overflow: hidden">' +
                '<li>'+ LANG.UI_DB_TARGET_DATABASE +':</li>'+
                '</ul>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            $('#instanceMapList').append(agentContent);
            $('#instanceMapList #dbsourceAgentInfo_' + selectedBackupUuid).collapse('show');
        } else {
            $('#dbsourceAgentDiv_' + selectedBackupUuid).remove();
        }
    };

    /**
     * 添加数据同步节点
     * @param treeNode
     */
    const addDataSynNode = (treeNode) => {
        if(treeNode.checked){
            $('#synNode').empty();
            let agentContent =
                '<div id="dbsourceNodeDiv_' + selectedBackupUuid + '" class="add-list">' +
                '<div class="accordion fileAccordion">' +
                '<div class="panel panel-default">' +
                '<div class="panel-heading">' +
                '<h4 class="panel-title">' +
                '<a class="accordion-toggle accordion-toggle-styled collapsed popovers" ' +
                'style="display: inline-block; width: 99%;text-decoration: none;" data-container="body" ' +
                'data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".fileAccordion" ' +
                'href="#dbsourceNodeInfo_' + selectedBackupUuid + '" aria-expanded="true">' +
                '<span class="font-green-seagreen">' + LANG.UI_PUBLIC_HOST + ':' + sourcetreeNode.name + '</span>' +
                '</a>' +
                '</h4>' +
                '</div>' +
                '<div id="dbsourceNodeInfo_' + selectedBackupUuid + '" class="panel-collapse collapse in" ' +
                'style="height: calc(100% - 34px);">' +
                '<div class="panel-body" style="padding: 16px; max-height: 250px; overflow-y: auto">' +
                '<div class="col-md-6">' +
                '<select id="hostNode" class="form-control select2me" style="overflow: hidden">' +
                '</select>' +
                '</div>' +
                '<div class="col-md-6">' +
                '<select id="slaveNode" class="form-control select2me" style="overflow: hidden">' +
                '</select>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            $('#synNode').append(agentContent);
            $('#synNode #dbsourceNodeInfo_' + selectedBackupUuid).collapse('show');
        }
        let hostNodeEle = $('#hostNode');
        let slaveNodeEle = $('#slaveNode');

        // 源机
        if(!sourcetreeNode.cluster_flag){
            hostNodeEle.append(
                `<option value="${sourcetreeNode.agent_uuid}" data-app-uuid = "${sourcetreeNode.app_uuid}">${sourcetreeNode.title}</option>`
            );
        }else{
            for(const node of sourcetreeNode.children){
                if(editFlag){
                    // 新建状态下 集群可修改
                    if(node.agent_uuid === SETTINGS.source_agent_uuid){
                        hostNodeEle.append(
                            `<option value="${node.agent_uuid}" data-app-uuid = "${node.app_uuid}" selected>${node.name}</option>`
                        );
                    }else{
                        hostNodeEle.append(
                            `<option value="${node.agent_uuid}" data-app-uuid = "${node.app_uuid}">${node.name}</option>`
                        );
                    }
                }

                if(SETTINGS.source_cluster_flag && node.agent_uuid === SETTINGS.source_agent_uuid && !editFlag){
                    // 源为集群 非新建状态  则不可修改
                    hostNodeEle.append(
                        `<option value="${node.agent_uuid}" data-app-uuid = "${node.app_uuid}" selected>${node.name}</option>`
                    );
                    let hostNodeSelect = document.getElementById('hostNode'); // 获取 select 元素
                    hostNodeSelect.disabled = true;
                }
            }
        }

        // 目标机
        if(!targettreeNode.cluster_flag){
            slaveNodeEle.append(
                `<option value="${targettreeNode.agent_uuid}" data-app-uuid = "${targettreeNode.app_uuid}">${targettreeNode.title}</option>`
            );
        }else{
            for(const node of targettreeNode.children){
                if(editFlag){
                    // 新建状态下 集群可修改
                    if(node.agent_uuid === SETTINGS.target_agent_uuid){
                        slaveNodeEle.append(
                            `<option value="${node.agent_uuid}" data-app-uuid = "${node.app_uuid}" selected>${node.name}</option>`
                        );
                    }else{
                        slaveNodeEle.append(
                            `<option value="${node.agent_uuid} data-app-uuid = "${node.app_uuid}"">${node.name}</option>`
                        );
                    }
                }

                if(SETTINGS.target_cluster_flag && node.agent_uuid === SETTINGS.target_agent_uuid && !editFlag){
                    // 目标为集群 非新建状态  则不可修改
                    slaveNodeEle.append(
                        `<option value="${node.agent_uuid}" data-app-uuid = "${node.app_uuid}" selected>${node.name}</option>`
                    );
                    let slaveNodeSelect = document.getElementById('slaveNode'); // 获取 select 元素
                    slaveNodeSelect.disabled = true;
                }
            }
        }
    }


    /**
     * 初始化已选择的备份树
     * @param {*} selectedTreeNode
     */
    const initInstanceUserTree = (selectedTreeNode,flag) => {
        let nodes = [];
        let selectedBackupKey;
        targetInstanceUserTree = [];
        if (selectedTreeNode.eventtype === 'cluster') {
            let selectedChildren = selectedTreeNode.children;
            let agentUuid = '';
            let appUuid = '';
            let appType = '';
            let appVersion = '';
            let instanceName = '';
            // 是否存在任务
            let dbcdpBackupInfo = [];
            for (const selectedChild of selectedChildren) {
                if (selectedChild.online_flag && selectedChild.auth_flag) {
                    agentUuid = selectedChild.agent_uuid;
                    appUuid = selectedChild.app_uuid;
                    appType = selectedChild.app_type;
                    appVersion = selectedChild.app_version;
                    instanceName = selectedChild.instance_name;
                    dbcdpBackupInfo = selectedChild.db_cdp_backup_info;
                    break;
                }
            }
            selectedBackupKey = selectedTreeNode.cluster_uuid;
            let chkDisabled = false;
            let name  = selectedTreeNode.name;
            let title = selectedTreeNode.name;
            let inbackup = false;
            let checked = true;
            if (dbcdpBackupInfo.length != 0) {
                // 存在备份任务
                title += "('" + dbcdpBackupInfo[0].job_name + "'" + LANG.UI_DB_BACKUP_TASK_PROTECTED + ')';
                inbackup = true;
                chkDisabled = true;
                checked = false;
            }
            nodes.push({
                id: selectedBackupKey,
                pId: 0,
                name,
                title: title,
                eventtype: 'cluster',
                icon: selectedTreeNode.icon,
                isParent: true,
                open: false,
                cluster_uuid: selectedTreeNode.cluster_uuid,
                instance_name: instanceName,
                app_uuid: appUuid,
                app_type:appType,
                app_version:appVersion,
                agent_uuid: agentUuid,
                checked,
                chkDisabled,
                inbackup,
                instance_node_list: selectedTreeNode.children,
                app_service_name: selectedTreeNode.app_service_name,
            });
        } else if (selectedTreeNode.eventtype === 'instance') {
            selectedBackupKey = selectedTreeNode.app_uuid;
            let chkDisabled = false;
            let name  = selectedTreeNode.name;
            let title = selectedTreeNode.name;
            let inbackup = false;
            let checked = true;

            if (selectedTreeNode.db_cdp_backup_info.length) {  // 存在备份任务
                title += "('" + selectedTreeNode.db_cdp_backup_info[0].job_name + "'" + LANG.UI_DB_BACKUP_TASK_PROTECTED + ')';
                inbackup = true;
                chkDisabled = true;
                checked = false;
            }

            nodes.push({
                id: selectedBackupKey,
                pId: 0,
                name,
                title,
                eventtype: 'instance',
                icon: selectedTreeNode.icon,
                isParent: true,
                open: false,
                instance_name: selectedTreeNode.instance_name,
                app_uuid: selectedTreeNode.app_uuid,
                agent_uuid: selectedTreeNode.agent_uuid,
                checked,
                chkDisabled,
                inbackup,
            });
        }
        if(flag){
            // 映射源
            sourceInstanceUserTree[selectedBackupKey] = $.fn.zTree.init($('#dbsourceAgentTree_' + selectedBackupKey), getInstanceUserTreeSetting(), nodes);
            // 默认展开
            let allNodes = sourceInstanceUserTree[selectedBackupKey].getNodes();
            sourceInstanceUserTree[selectedBackupKey].expandNode(allNodes[0], true, false, true, true);
        }else {
            // 映射目标
            targetInstanceUserTree[selectedBackupKey] = $.fn.zTree.init($('#dbtargetAgentTree_' + selectedBackupKey), getInstanceUserTreeSetting(), nodes);
            // 默认展开
            let allNodes = targetInstanceUserTree[selectedBackupKey].getNodes();
            targetInstanceUserTree[selectedBackupKey].expandNode(allNodes[0], true, false, true, true);
        }

    };

    /**
     * 获取Oracle备份树的设置
     */
    const getInstanceUserTreeSetting = () => {
        return {
            check: {
                enable: false,
                nocheckInherit: false
            },
            data: {
                simpleData: {
                    enable: true,
                    idKey: 'id',
                    pIdKey: 'pId',
                    rootPId: 0,
                },
                key: {
                    title: 'title',
                }
            },
            view: {
                fontCss: function (treeId, treeNode) {
                    let css = {color: "#333", "font-weight": "normal"};
                    if (!!treeNode.inbackup) {
                        css = {color: "green", "font-weight": "bold"};
                    }
                    if (!!treeNode.highlight) {
                        //搜索使用的样式
                        css = {color: "#A60000", "font-weight": "bold"};
                    }
                    return css;
                },
            },
            callback: {
                beforeExpand: expandInstanceUserTreeNode
            }
        };
    };

    const expandInstanceUserTreeNode = (treeId, treeNode) => {
        if (treeNode.eventtype !== 'cluster' && treeNode.eventtype !== 'instance') {
            return;
        }
        if (typeof treeNode.children !== 'undefined' && treeNode.children.length) {
            return;
        }
        let selectedBackupKey;
        let agentUuid = treeNode.agent_uuid;
        let appUuid = treeNode.app_uuid;
        if (treeNode.eventtype === 'cluster') {
            selectedBackupKey = treeNode.cluster_uuid;
        } else if (treeNode.eventtype === 'instance') {
            selectedBackupKey = treeNode.app_uuid;
        }
        let select_copy_users_flag = false;
        if(treeId == 'dbsourceAgentTree_'+selectedBackupKey){
            // 源
            select_copy_users_flag = true;
        }

        // 加载实例用户信息
        Metronic.blockUI({target: "#dbcdpbackupcontent", animate: true});
        let task_uuid = $('#task_uuid').val();
        let param =  {
            "refresh_agent_set":[
                {
                    "agent_uuid":agentUuid,
                    "app_uuid_set":[
                        {
                            app_uuid:appUuid
                        }
                    ]
                }
            ],
            "task_uuid":task_uuid,
            "select_copy_users_flag":select_copy_users_flag
        }
        pAjaxRequest(param, `/api/v1/dbcdp/databases`, 'POST', res => {
            Metronic.unblockUI("#dbcdpbackupcontent");
            if (!res.success) {
                sourceScanUserFlag = false;
                sourceScanUserTip = res.message;
                UIToastr.showWarning(LANG.UI_DB_CDP_SCAN_APP_INFO, res.message);
                return;
            }
            if(res.data.rows.length == 0){
                sourceScanUserFlag = false;
                sourceScanUserTip = LANG.UI_DB_CDP_BACKUP_SELECT_DATABASE_REPLICATION_HOST_TIPS;
                UIToastr.showWarning(LANG.UI_DB_CDP_SCAN_APP_INFO,LANG.UI_DB_CDP_BACKUP_SELECT_DATABASE_REPLICATION_HOST_TIPS);
                return;
            }
            sourceScanUserFlag = true;
            let nodes = [];
            let users = [];
            let rows = res.data.rows;
            let select_copy_users = res.data.select_copy_users;
            for(const row of rows){
                for(const user of select_copy_users){
                    if(row.con_id == user.con_id){
                        users.push(user);
                    }
                }
            }
            // 筛选出创建时选择的用户
            for (const user of users) {
                if(user.con_id == 0){
                    for(const user_list of user.user_list){
                        // 非容器数据库
                        nodes.push({
                            id: user_list.user_name,
                            pId: selectedBackupKey,
                            name: user_list.name,
                            title: user_list.name,
                            eventtype: 'user',
                            // app_uuid: user.app_uuid,
                            // agent_uuid: user.agent_uuid,
                            // instance_name: user.instance_name,
                            isParent: false,
                            nocheck: true,
                            icon: './img/dbcdp/user.png',
                            pdb_name:user.pdb_name,
                            con_id:user.con_id
                        });
                    }
                }else{
                    let userNodes = [];
                    for(const user_list of user.user_list){
                        // 容器数据库
                        userNodes.push({
                            id: getUuid(),
                            pId: user.pdb_name,
                            name: user_list.name,
                            title: user_list.name,
                            eventtype: 'user',
                            // app_uuid: user.app_uuid,
                            // agent_uuid: user.agent_uuid,
                            // instance_name: user.instance_name,
                            isParent: false,
                            nocheck: true,
                            icon: './img/dbcdp/user.png',
                            pdb_name:user.pdb_name,
                            con_id:user.con_id
                        })
                    }
                    nodes.push({
                        id: user.pdb_name,
                        pId: selectedBackupKey,
                        name: user.pdb_name,
                        title: user.pdb_name,
                        eventtype: 'pdb',
                        nocheck: true,
                        icon: './img/platform/storage.png',
                        isParent: true,
                        open:true,
                        children:userNodes,
                    });
                }
            }
            $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, nodes, true);
        });
    }

    /**
     * 将Oracle的备份节点添加到右侧中
     * @param {*} treeNode
     */
    const addOracleBackupNodetList = (treeNode) => {
        // 集群uuid 或 应用uuid
        let selectedBackupKey;
        if (treeNode.eventtype === 'cluster') {
            selectedBackupKey = treeNode.cluster_uuid;
            selectedBackupUuid = selectedBackupKey;
        } else if (treeNode.eventtype === 'instance') {
            selectedBackupKey = treeNode.app_uuid;
            selectedBackupUuid = selectedBackupKey;
        }
        $('#allDbTree').empty();
        agentList = [];
        if (treeNode.checked) {
            let agentContent =
                '<div id="dbAgentDiv_' + selectedBackupKey + '" class="add-list">' +
                '<div class="accordion fileAccordion">' +
                '<div class="panel panel-default">' +
                '<div class="panel-heading">' +
                '<h4 class="panel-title">' +
                '<a class="accordion-toggle accordion-toggle-styled collapsed popovers" ' +
                'style="display: inline-block; width: 99%;text-decoration: none;" data-container="body" ' +
                'data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".fileAccordion" ' +
                'href="#dbAgentInfo_' + selectedBackupKey + '" aria-expanded="true">' +
                '<span class="font-green-seagreen">' + treeNode.name + '</span>' +
                '</a>' +
                '</h4>' +
                '</div>' +
                '<div id="dbAgentInfo_' + selectedBackupKey + '" class="panel-collapse collapse in" ' +
                'style="height: calc(100% - 34px);">' +
                '<div class="panel-body" style="padding: 16px; max-height: 250px; overflow-y: auto">' +
                '<ul id="dbAgentTree_' + selectedBackupKey + '" class="ztree" style="overflow: hidden"></ul>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            $('#allDbTree').append(agentContent);
            agentList.push(selectedBackupKey);
            $('#allDbTree #dbAgentInfo_' + selectedBackupKey).collapse('show');
        } else {
            $('#dbAgentDiv_' + selectedBackupKey).remove();
            agentList = agentList.filter(item => item !== selectedBackupKey);
        }
    };

    /**
     * 构建目标树
     * @param instanceList
     * @param id
     * @param checkedFn
     */
    const setOracleTargetTree = (instanceList,id,checkedFn) => {
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

            // 如果备机已选
            let isChecked = false;
            nodes.push({
                id: standaloneNode.app_uuid,
                pId: 'standalone',
                name,
                title: name,
                isParent: false,
                eventtype: 'instance',
                chkDisabled,
                checked:isChecked,
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
                db_backup_info: standaloneNode.db_backup_info,
                auth_flag: standaloneNode.agent_info.authorization_module.dbcdp,
                db_cdp_backup_info:standaloneNode.db_cdp_backup_info  // 是否有任务运行
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
                    db_backup_info: instanceInfo.db_backup_info,
                    auth_flag: instanceInfo.agent_info.authorization_module.dbcdp,
                    db_cdp_backup_info:instanceInfo.db_cdp_backup_info
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
                cluster_flag: true,
                cluster_name: clusterInfo.cluster_name,
                cluster_service_ip: clusterInfo.cluster_service_ip,
                app_service_name: clusterInfo.app_service_name,
                online_flag: clusterOnlineFlag,
                auth_flag: clusterAuthFlag,
            });
        }
        zTreetTargetAgent = $.fn.zTree.init($(id), getBackupTreeSetting(checkedFn), nodes);
        let nodeSelected = zTreetTargetAgent.getNodes();  //分组下的所有节点
        zTreetTargetAgent.checkNode(nodeSelected[0].children[0], true, true, true);
    };

    /**
     * 初始化已选择的备份树
     * @param {*} selectedTreeNode
     */
    const initOracleSelectedBackupNodeTree = (selectedTreeNode) => {
        let nodes = [];
        let selectedBackupKey;
        if (selectedTreeNode.eventtype === 'cluster') {
            let selectedChildren = selectedTreeNode.children;
            let agentUuid = '';
            let appUuid = '';
            let appType = '';
            let appVersion = '';
            let instanceName = '';
            // 是否存在任务
            let dbcdpBackupInfo = [];
            for (const selectedChild of selectedChildren) {
                if (selectedChild.online_flag && selectedChild.auth_flag) {
                    agentUuid = selectedChild.agent_uuid;
                    appUuid = selectedChild.app_uuid;
                    appType = selectedChild.app_type;
                    appVersion = selectedChild.app_version;
                    instanceName = selectedChild.instance_name;
                    dbcdpBackupInfo = selectedChild.db_cdp_backup_info;
                    break;
                }
            }
            selectedBackupKey = selectedTreeNode.cluster_uuid;
            let chkDisabled = false;
            let name  = selectedTreeNode.name;
            let title = selectedTreeNode.name;
            let inbackup = false;
            let checked = true;
            nodes.push({
                id: selectedBackupKey,
                pId: 0,
                name,
                title: title,
                eventtype: 'cluster',
                icon: selectedTreeNode.icon,
                isParent: true,
                open: false,
                cluster_uuid: selectedTreeNode.cluster_uuid,
                instance_name: instanceName,
                app_uuid: appUuid,
                app_type:appType,
                app_version:appVersion,
                agent_uuid: agentUuid,
                checked,
                chkDisabled,
                inbackup,
                instance_node_list: selectedTreeNode.children,
                app_service_name: selectedTreeNode.app_service_name,
            });
        } else if (selectedTreeNode.eventtype === 'instance') {
            selectedBackupKey = selectedTreeNode.app_uuid;
            let chkDisabled = false;
            let name  = selectedTreeNode.name;
            let title = selectedTreeNode.name;
            let inbackup = false;
            let checked = true;

            nodes.push({
                id: selectedBackupKey,
                pId: 0,
                name,
                title,
                eventtype: 'instance',
                icon: selectedTreeNode.icon,
                isParent: true,
                open: false,
                instance_name: selectedTreeNode.instance_name,
                app_uuid: selectedTreeNode.app_uuid,
                agent_uuid: selectedTreeNode.agent_uuid,
                checked,
                chkDisabled,
                inbackup,
            });
        }
        zTreeDB[selectedBackupKey] = $.fn.zTree.init($('#dbAgentTree_' + selectedBackupKey), getOracleSelectedBackupNodeTreeSetting(), nodes);
        // 默认展开
        let allNodes = zTreeDB[selectedBackupKey].getNodes();
        zTreeDB[selectedBackupKey].expandNode(allNodes[0], true, false, true, true);
    };

    /**
     * 获取Oracle备份树的设置
     */
    const getOracleSelectedBackupNodeTreeSetting = () => {
        return {
            check: {
                enable: true,
                nocheckInherit: false
            },
            data: {
                simpleData: {
                    enable: true,
                    idKey: 'id',
                    pIdKey: 'pId',
                    rootPId: 0,
                },
                key: {
                    title: 'title',
                }
            },
            view: {
                fontCss: function (treeId, treeNode) {
                    let css = {color: "#333", "font-weight": "normal"};
                    if (!!treeNode.inbackup) {
                        css = {color: "green", "font-weight": "bold"};
                    }
                    if (!!treeNode.highlight) {
                        //搜索使用的样式
                        css = {color: "#A60000", "font-weight": "bold"};
                    }
                    return css;
                },
            },
            callback: {
                beforeClick: clickOracleSelectedBackupNode,
                onCheck: checkOracleSelectedBackupNode,
                beforeExpand: expandOracleSelectedBackupNode
            }
        };
    };

    /**
     * 点击了Oracle已选的节点
     * @param {*} treeId
     * @param {*} treeNode
     */
    const clickOracleSelectedBackupNode = (treeId, treeNode) => {
        switch (treeNode.eventtype) {
            case 'cluster':
            case 'instance':
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
                break;
        }
    };

    /**
     * 选择了Oracle已选的节点
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const checkOracleSelectedBackupNode = (ev, treeId, treeNode) => {
        if (treeNode.eventtype === 'cluster' || treeNode.eventtype === 'instance') {
            if (typeof treeNode.children === 'undefined' || !treeNode.children.length) {
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, false, true, true);
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, false, true);
                return;
            }
        }
        if (!treeNode.checked) {
            return;
        }
    };

    /**
     * 展开了了Oracle已选的节点
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const expandOracleSelectedBackupNode = (treeId, treeNode) => {
        if (treeNode.eventtype !== 'cluster' && treeNode.eventtype !== 'instance') {
            return;
        }
        if (typeof treeNode.children !== 'undefined' && treeNode.children.length) {
            return;
        }
        let selectedBackupKey;
        let agentUuid = treeNode.agent_uuid;
        let appUuid = treeNode.app_uuid;
        if (treeNode.eventtype === 'cluster') {
            selectedBackupKey = treeNode.cluster_uuid;
            selectedBackupUuid = selectedBackupKey;
        } else if (treeNode.eventtype === 'instance') {
            selectedBackupKey = treeNode.app_uuid;
            selectedBackupUuid = selectedBackupKey;
        }

        // 加载实例用户信息
        Metronic.blockUI({target: "#dbcdpbackupcontent", animate: true});
        let task_uuid = $('#task_uuid').val();
        let param =  {
            "refresh_agent_set":[
                {
                    "agent_uuid":agentUuid,
                    "app_uuid_set":[
                        {
                            app_uuid:appUuid
                        }
                    ]
                }
            ],
            "task_uuid":task_uuid
        }
        pAjaxRequest(param, `/api/v1/dbcdp/databases`, 'POST', res => {
            Metronic.unblockUI("#dbcdpbackupcontent");
            if (!res.success) {
                return;
            }
            ip_scope_info = res.data.ip_scope_info;
            let nodes = [];
            let rows = res.data.rows;
            for (const dbInfo of rows) {
                if(!dbInfo.is_cdb){
                    for(const user of dbInfo.user_list){
                        // 非容器数据库
                        nodes.push({
                            id: user.user_name,
                            pId: selectedBackupKey,
                            name: user.user_name,
                            title: user.user_name,
                            eventtype: 'user',
                            app_uuid: user.app_uuid,
                            agent_uuid: user.agent_uuid,
                            instance_name: user.instance_name,
                            isParent: false,
                            nocheck: true,
                            icon: './img/dbcdp/user.png',
                            pdb_name:dbInfo.pdb_name,
                            con_id:dbInfo.con_id
                        });
                    }
                }else{
                    let userNodes = [];
                    for(const user of dbInfo.user_list){
                        // 容器数据库
                        userNodes.push({
                            id: getUuid(),
                            pId: dbInfo.pdb_name,
                            name: user.user_name,
                            title: user.user_name,
                            eventtype: 'user',
                            app_uuid: user.app_uuid,
                            agent_uuid: user.agent_uuid,
                            instance_name: user.instance_name,
                            isParent: false,
                            nocheck: true,
                            icon: './img/dbcdp/user.png',
                            pdb_name:dbInfo.pdb_name,
                            con_id:dbInfo.con_id
                        })
                    }
                    nodes.push({
                        id: dbInfo.pdb_name,
                        pId: selectedBackupKey,
                        name: dbInfo.pdb_name,
                        title: dbInfo.pdb_name,
                        eventtype: 'pdb',
                        nocheck: true,
                        icon: './img/platform/storage.png',
                        isParent: true,
                        open:true,
                        children:userNodes,
                    });
                }
            }
            $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, nodes, true);
        });
    };

    /**
     * 获取客户端网卡信息(源机)
     */
    const loadAgentNetworkInfo =  (agent_uuid) => {
        Metronic.blockUI({target: "#dbcdpbackupcontent", animate: true});
        let param = {'agent_uuid':agent_uuid};
        pAjaxRequest(param,'/api/v1/dbcdp/jobs/networkInfo','GET',(d) => {
            Metronic.unblockUI("#dbcdpbackupcontent");
            agentNetworkInfo = d.data;
            // 如果源机是单机 则需要过滤 ip_scope_info 中的 ip
            if (!data.sync_object.source_cluster_flag) {
                // 遍历数组中的每个 agentNetworkInfo 对象
                agentNetworkInfo.forEach(networkInfo => {
                    networkInfo.ip_set = networkInfo.ip_set.filter(item => {
                        // 检查当前IP是否存在于ip_scope_info数组的任何元素中
                        return !ip_scope_info.some(scope => scope.ip === item.ip_addr);
                    });
                });
            }
            return agentNetworkInfo;
        },false);
    }

    /**
     *  获取目标端网卡信息
     */
    const loadStandbyNetworkInfo =  (agent_uuid)=> {
        let param = {'agent_uuid': agent_uuid};
        pAjaxRequest(param,'/api/v1/dbcdp/jobs/networkInfo','get',(d) => {
            standbyNetworkInfo = d.data;
        },false);
    }

    /**
     * 集群IP网卡信息
     */
    const loadClusterNetworkInfo =  (agent_uuid,targetFlag = false) => {
        Metronic.blockUI({target: "#dbcdpbackupcontent", animate: true});
        let param = {'agent_uuid':agent_uuid};
        pAjaxRequest(param,'/api/v1/dbcdp/jobs/sourcecluster/networkInfo','get',(d) => {
            Metronic.unblockUI("#dbcdpbackupcontent");
            if(targetFlag){
                if(d.success){
                    standbyNetworkInfo = d.data;
                }else{
                    // 网卡查询失败
                    standbyNetworkInfo = [{
                        "download_size": 0,
                        "flag": 69699,
                        "gateway_address": "00:0C:29:13:76:AF",
                        "ip_set": [
                            {
                                "ip_addr": "192.168.47.130",
                                "ip_type": 1,
                                "netmask": "255.255.192.0"
                            }
                        ],
                        "mac_address": "",
                        "name": "ens192:2",
                        "upload_size": 0,
                        "is_cluster":true
                    }];
                }
            }else{
                agentNetworkInfo = d.data;
            }
        });
    }

    /**
     * 初始化接管网络配置
     */
    const initTakeoverConfig  = () => {
        // 业务IP配置
        $('#host_ip_server_conf').takeoverIpServerMapConfig({
            agentNetwork:agentNetworkInfo,  //主机的网卡信息
            standbyNetwork:standbyNetworkInfo, //备机的网卡信息
            takeoverHisNetwork:[],
            failbackHisNetwork:[],
        });
        // 备机网关信息
        $('#standby_gateway_conf').takeoverStandbyGatewayConfig({
            agentNetwork:agentNetworkInfo,  //主机的网卡信息
            standbyNetwork:standbyNetworkInfo, //备机的网卡信息
            takeoverHisNetwork:[],
            failbackHisNetwork:[],
        });
    }

    /**
     * 源机为集群时，需要ip扫描
     */
    const scanIP =  (agent_uuid,app_uuid) => {
        Metronic.blockUI({target: "#takeover_conf_drawer", animate: true});
        let param = {
            'refresh_agent_set':[
                {
                    'agent_uuid': agent_uuid,
                    'node_uuid':nodeUuid,
                    'app_uuid_set':[
                        {
                            'app_uuid':app_uuid,
                        }
                    ]
                }
            ],
        };
        pAjaxRequest(param,'/api/v1/dbcdp/jobs/scanIp','POST', (res) =>{
            Metronic.unblockUI("#takeover_conf_drawer");
            let app_detail = JSON.parse(res.message.app_detail);
            agentNetworkInfo = app_detail.nic_list;
            agentNetworkInfo.forEach(function(nic) {
                nic.is_cluster = true;
            });
            initTakeoverConfig();
        });
    }

    /**
     * 初始化步骤4的数据库备份源树
     */
    const initStep4Tree = (agentList,dom,tree) => {
        let dbType = parseInt($('#dbtype').val());
        let setting = {
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
        };
        let nodes = [];
        let clusterTopFlag = false;
        let standaloneTopFlag = false;
        for (let agentKey of agentList) {
            let checkNodes = tree[agentKey].getNodes();
            // 选择数据库的需要将实例或集群也加入
            let additionalNodes = [];
            for (const checkNode of checkNodes) {
                if (checkNode.eventtype === 'db') {
                    additionalNodes.push(checkNode.getParentNode());
                }
            }
            for (const additionalNode of additionalNodes) {
                let inCheckFlag = false;
                for (const checkNode of checkNodes) {
                    if (additionalNode.id === checkNode.id) {
                        inCheckFlag = true;
                        break;
                    }
                }
                if (!inCheckFlag) {
                    checkNodes.push(additionalNode);
                }
            }
            $.each(checkNodes, (index, checkNode) => {
                if (checkNode.eventtype === 'cluster') {
                    if (!clusterTopFlag) {
                        clusterTopFlag = true;
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
                    }
                    nodes.push({
                        id: checkNode.cluster_uuid,
                        pId: 'cluster',
                        name: checkNode.name,
                        title: checkNode.name,
                        isParent: true,
                        open: true,
                        nocheck: true,
                        eventtype: 'cluster',
                        icon: './img/platform/db-cluster.png',
                    });
                    for (const instanceNode of checkNode.instance_node_list) {
                        nodes.push({
                            id: instanceNode.agent_uuid + '_' + instanceNode.instance_name,
                            pId: checkNode.cluster_uuid,
                            name: instanceNode.name,
                            title: instanceNode.name,
                            isParent: false,
                            nocheck: true,
                            eventtype: 'cluster_instance',
                            icon: './img/platform/storage.png',
                        });
                    }
                } else if (checkNode.eventtype === 'instance') {
                    if (!standaloneTopFlag) {
                        standaloneTopFlag = true;
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
                    }
                    nodes.push({
                        id: checkNode.agent_uuid + '_' + checkNode.instance_name,
                        pId: 'standalone',
                        name: checkNode.name,
                        title: checkNode.name,
                        nocheck: true,
                        eventtype: 'instance',
                        icon: './img/platform/storage.png',
                    });
                }
            });
        }
        $.fn.zTree.init(dom, setting, nodes);
    };

    /**
     * 初始化备机树
     */
    const initStandBySelect =  () => {
        let param = {db_type: CONF.DB_TYPE.ORACLE};
        // 获取实例列表
        // 对备机的要求 排除源机和任务中的机器和离线的机器
        pAjaxRequest(param, '/api/v1/db/instances', 'GET', res => {
            let backupAgentArr = [];
            let rows = res.data.rows;
            if(SETTINGS.target_cluster_flag){
                // 集群
                rows.forEach(item => {
                    if (item.cluster_info.cluster_uuid === SETTINGS.target_cluster_uuid) {
                        backupAgentArr.push(item);
                    }
                });
            }else if(!SETTINGS.target_cluster_flag){
                // 单机
                rows.forEach(item => {
                    if (item.app_uuid === SETTINGS.target_app_uuid) {
                        backupAgentArr.push(item);
                    }
                });
            }
            targetArr = backupAgentArr;
            $('.dbMapDetaildiv').hide();
            $('.synNodediv').hide();
            $('#autoTakeOver').bootstrapSwitch('state', false, true);
            $('#takeoverConfigContent').hide();
            setOracleTargetTree(backupAgentArr,'#target_agent_tree',checkOraclesStandbyNode);
        },false);
    }

    /**
     * 比较源机和目标机的应用
     * 应用类型相同 源机应用版本需大于等于主机
     */
    const contrastApp =  (sourceApp, targetApp) => {
        // 源、目标机类型需相同
        // 目标机版本应大于等于源机
        if (sourceApp.app_type !== targetApp.app_type) {
            appValidFlag = false;
            UIToastr.showWarning('', LANG.UI_DB_CDP_BACKUP_SOURCE_APP_SELECT_TIP1);
            return false;
        } else {
            // 提取版本号中的数字部分
            var v1Parts = sourceApp.app_version.match(/\d+(?:\.\d+)*/g).map(part => part.split('.').map(num => parseInt(num)));
            var v2Parts = targetApp.app_version.match(/\d+(?:\.\d+)*/g).map(part => part.split('.').map(num => parseInt(num)));
            // 比较主版本号
            for (var i = 0; i < Math.max(v1Parts.length, v2Parts.length); i++) {
                var v1Num = v1Parts[i]? v1Parts[i][0] : 0;
                var v2Num = v2Parts[i]? v2Parts[i][0] : 0;
                if (v1Num > v2Num) {
                    UIToastr.showWarning('', LANG.UI_DB_CDP_BACKUP_APP_SELECT_TIPS+'<br>'+LANG.UI_DB_CDP_BACKUP_SOURCE_VERSION+':'+sourceApp.app_version+'<br>'+LANG.UI_DB_CDP_BACKUP_END_VERSION+':'+targetApp.app_version);
                    appValidFlag = false;
                    return false;
                } else if (v1Num < v2Num) {
                    appValidFlag = true;
                    return true;
                }
                // 如果主版本号相同，比较次版本号等
                if (v1Num === v2Num && v1Parts[i] && v2Parts[i]) {
                    for (var j = 1; j < Math.max(v1Parts[i].length, v2Parts[i].length); j++) {
                        var subV1Num = v1Parts[i][j]? v1Parts[i][j] : 0;
                        var subV2Num = v2Parts[i][j]? v2Parts[i][j] : 0;
                        if (subV1Num > subV2Num) {
                            UIToastr.showWarning('', LANG.UI_DB_CDP_BACKUP_APP_SELECT_TIPS+'<br>'+LANG.UI_DB_CDP_BACKUP_SOURCE_VERSION+':'+sourceApp.app_version+'<br>'+LANG.UI_DB_CDP_BACKUP_END_VERSION+':'+targetApp.app_version);
                            appValidFlag = false;
                            return false;
                        } else if (subV1Num < subV2Num) {
                            appValidFlag = true;
                            return true;
                        }
                    }
                }
            }
            appValidFlag = true;
            return true;
        }
    }

    /**
     * 预加载自动接管
     */
    const initAutoTakeover = () => {
        $('#autoTakeOver').bootstrapSwitch('state', SETTINGS.auto_takeover_flag, true);
        if(SETTINGS.auto_takeover_flag){
            $("#takeoverConfigContent").show();
            $('#serviceIpDrift').bootstrapSwitch('state', SETTINGS.switch_ip_flag, true);  //服务IP漂移
            if (SETTINGS.switch_ip_flag) {
                if(!SETTINGS.target_cluster_flag){
                    $('.takeoverNetConf').show();
                }else{
                    $('.takeoverNetConf').hide();
                }
            }
            if(!SETTINGS.source_cluster_flag && switchIpFlag){
                $('#failbackTargetIpContent').show();
            }else{
                $('#failbackTargetIpContent').hide();
            }
            //网卡配置
            var takeover_business_ip_map = {};
            if(SETTINGS.takeover_business_ip_map){
                takeover_business_ip_map = SETTINGS.takeover_business_ip_map.takeover_business_ip_map;
            }
            if(takeover_business_ip_map.length > 0){
                $('.takeoverNetConf').show();
                var businessIpList = takeover_business_ip_map;  //对象
                _takeoverIpMapList = [];
                businessIpList.forEach((item,index)=>{
                    var list = {
                        'conf_des':'',
                        'conf_id':'',
                        'agent_map_info':{
                            'nic_ip_info':[],
                        },
                    };
                    list.agent_map_info.nic_name = item.nic_name;
                    list.agent_map_info.nic_mac = item.nic_mac;
                    list.agent_map_info.nic_gateway = item.nic_gateway;
                    list.agent_map_info.nic_ip_info.push(item.nic_ip_info[0]);
                    list.conf_des = item.conf_des;
                    list.conf_id = item.conf_id;
                    _takeoverIpMapList.push(list);
                    _takeoverIpMapListView.push(list);
                    allIpMapInfoList.push(list);
                    ipMapInfoList.push(list);
                })
                hostGatewayConfDes();
            }

            $('#failbackTargetIp').val(SETTINGS.failback_target_ip);
            //心跳失效时间
            $('.heartFailureDiv').spinner('value',SETTINGS.agent_heartbeat_failure_time);
            //连续故障次数
            $('.numberFailuresDiv').spinner('value',SETTINGS.app_consecutive_failure_num);
            //故障监测间隔
            $('.faultMonitorIntervalDiv').spinner('value',SETTINGS.app_fault_detection_interval);
        }
    }

    /**
     * 预加载任务名
     */
    const initTaskName = () => {
        $('#jobname').val(SETTINGS.task_name);
    }

    /*-------------------------------------------------------------------------*/

    /**
     * 步骤1有效
     * @return {boolean}
     */
    const step1Valid = () => {
        // 判断数量授权是否足够
        if(!moduleAvailable){
            return false;
        }

        let agentNodes = zTreeAgent.getCheckedNodes();
        if (!agentNodes.length) {
            UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, LANG.UI_DB_BACKUP_SELECT_CLIENT);
            return false;
        }


        let dbType = parseInt($('#dbtype').val());
        let showStr = '';
        for (let i = 0; i < agentList.length; i++) {
            let nodes = zTreeDB[agentList[i]].getCheckedNodes();
            let selectFlag = false;
            $.each(nodes, function (i, d) {
                switch (dbType) {
                    case CONF.DB_TYPE.ORACLE:
                        if (d.eventtype === 'cluster') {
                            showStr += d.name + '<br>';
                            for (const instanceNode of d.instance_node_list) {
                                showStr += '&emsp;' + instanceNode.name + '<br>';
                            }
                            selectFlag = true;
                        } else if (d.eventtype === 'instance') {
                            showStr += d.name + '<br>';
                            loadAgentNetworkInfo(d.agent_uuid);
                            selectFlag = true;
                        }
                        break;
                }
            })
            if (!selectFlag) {
                let tips = LANG.UI_DB_BACKUP_NO_SELECT_INSTANCE_TIPS;
                UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, tips);
                return false;
            }
        }

        // 预加载备机
        initStandBySelect();
        // 预加载自动接管
        initAutoTakeover();
        // 预加载任务名
        initTaskName();
        initStep4Tree(agentList,$('#dbcdpShowSourceTree'),zTreeDB);
    }

    /**
     * 步骤2有效
     * @return {boolean}
     */
    const step2Valid = () => {
        // 验证备机
        if(data.sync_object.target_agent_uuid == ''){
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC, LANG.UI_VOL_CDP_BACKUP_STANDBY_MACHINE_SELECT);
            return false;
        }

        // 验证应用有效
        if(!appValidFlag){
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC,LANG.UI_DB_CDP_BACKUP_APP_SELECT_TIPS);
            return false;
        }

        backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
        if(!backupTargetInfo){
            return false;
        }
        if(backupTargetInfo.node_uuid == ''){
            $('.transfernetworkDiv').hide();
        }else{
            $('.transfernetworkDiv').show();
        }
        data.node_uuid = backupTargetInfo.node_uuid;
        data.node_pool_uuid = backupTargetInfo.node_pool_uuid;
        data.node_text = backupTargetInfo.node_text;

        // 数据同步节点
        data.sync_object.source_agent_uuid = $('#hostNode option:selected').val();
        data.sync_object.target_agent_uuid = $('#slaveNode option:selected').val();
        data.sync_object.source_app_uuid = $('#hostNode option:selected').attr('data-app-uuid');
        data.sync_object.target_app_uuid = $('#slaveNode option:selected').attr('data-app-uuid');

        // 数据表空间存储目录
        let tablespaceDirSelectValue = $('#tablespaceDir option:selected').val();
        if(tablespaceDirSelectValue == '1'){
            data.app_tablespace_dir = $('#customDirectory').val();
            if(data.app_tablespace_dir == ''){
                UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC,LANG.UI_DB_CDP_DETAILS_STORAGE_DIR_TIPS);
                return false;
            }
            // 1、如果目标端是单机，则路径应该是以Linux的路径，如：/DATA_01/app/oracle/oradata/monitor/；
            // 2、如果目标端是集群，则路径应该是以+开头的路径，如：+DATADG/capcom/datafile/；

            if(data.app_tablespace_dir != '' && !checkFilePath(data.app_tablespace_dir,'Linux') && !data.sync_object.target_cluster_flag){
                UIToastr.showWarning(LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG, LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG_TIPS);
                return false;
            }

            if(data.sync_object.target_cluster_flag){
                const appTablespaceDir = data.app_tablespace_dir;
                let pathFlag = appTablespaceDir.startsWith('+');
                let newPath = '/' + appTablespaceDir.slice(1);
                if(!pathFlag || !checkFilePath(newPath)){
                    UIToastr.showWarning(LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG, LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG_TIPS);
                    return false;
                }
            }
        }else{
            data.app_tablespace_dir = '';
        }

        data.auto_takeover_flag = $('#autoTakeOver').is(':checked');
        if(data.auto_takeover_flag){
            // 自动接管
            data.takeover_object.failback_target_ip = $('#failbackTargetIp').val();
            data.takeover_object.app_consecutive_failure_num = parseInt($('#Failures_num').val());
            data.takeover_object.app_fault_detection_interval = parseInt($('#monitor_Interval').val());
            data.takeover_object.agent_heartbeat_failure_time = parseInt($('#heartFailure_num').val());

            // 验证回切通讯ip
            if(isIpShow && failbackTargetIp == ''){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP, LANG.UI_DB_CDP_AUTO_TAKEOVER_TIP1);
                return false;
            }
            if(isIpShow && !ipCheckFlag){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP, LANG.UI_DB_CDP_AUTO_TAKEOVER_TIP3);
                return false;
            }
            if(isIpShow && !_failbackIpVerify && ipCheckFlag){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP_MESSAGE);
                return false;
            }

            // 验证回切业务网卡
            if(isNetWorkShow && data.takeover_object.takeover_business_ip_map.length ==0 ){
                UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC, LANG.UI_DB_CDP_BACKUP_TAKEOVER_NIC_CONF_TIPS);
                return false;
            }
        }else{
            data.takeover_object.switch_ip_flag = false;
            data.takeover_object.failback_target_ip = '';
            data.takeover_object.app_consecutive_failure_num = '';
            data.takeover_object.app_fault_detection_interval = '';
            data.takeover_object.agent_heartbeat_failure_time = '';
            data.takeover_object.takeover_business_ip_map = [];
        }
        initNetworkList();
        initStep4Tree(targetAgentList,$('#dbcdpShowTargetTree'),targetInstanceUserTree);
        preStep3();
    }

    /**
     *
     */
    const preStep3 = () => {
        // 限速策略
        initSpeedStrategyDes();
        // 加密传输
        $('#encrypttransfer').bootstrapSwitch('state', SETTINGS.encrypt_flag, true);
        if($('#encrypttransfer').get(0).checked){
            $('.encrypttransfermethoddiv').show();
            for(key in encryptMethod){
                if(SETTINGS.transport_encrypt_method == encryptMethod[key]){
                    var elements = document.getElementById('encrypttransfermethodselect');
                    for (var i = 0;i<elements.length;i++){
                        if(elements[i].value == encryptMethod[key]){
                            elements[i].selected = true;
                            break;
                        }
                    }
                    break;
                }
            }
        }
        // 压缩传输
        $('#compresstransfer').bootstrapSwitch('state', SETTINGS.compress_flag, true);
        if($('#compresstransfer').get(0).checked){
            $('.compressgradediv').show();
            for(key in compressMethod){
                if(SETTINGS.transport_compress_method == compressMethod[key]){
                    var elements = document.getElementById('compresstransferselect');
                    for (var i = 0;i<elements.length;i++){
                        if(elements[i].value == compressMethod[key]){
                            elements[i].selected = true;
                            break;
                        }
                    }
                    break;
                }
            }
        }
        // 导出传输线程
        $('.exp_thread_div').spinner('value',SETTINGS.exp_max_process_num);
        // 导入传输线程
        $('.imp_thread_div').spinner('value',SETTINGS.imp_max_process_num);

        // 延迟加载时间
        if(!!SETTINGS.delay_load_time && !autoTakeoverFlag){
            //打开延迟加载开关
            $('#delayedLoad').bootstrapSwitch('state',true,true);
            $("#delayedLoadTimeDiv").show();

            let times = secondsToTime(SETTINGS.delay_load_time);
            $('.days').val(times.days);
            $('.hours').val(times.hours);
            $('.minutes').val(times.minutes);
            $('.seconds').val(times.seconds);
        }

        // 源机文件缓存大小
        if(SETTINGS.source_file_cache_alloc_space == '-1'){
            $('.source_custom_cache_size_div').hide();
            $('.source_cache_size_unlimited_div').show();
            // 使用原生 JavaScript 的 innerHTML 属性
            document.querySelector('button[data-value="source_cache_button"]').innerHTML = LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM;
            sourceToggleLimitModeFlag = true;
        }else{
            let source_file_cache_alloc_space = SETTINGS.source_file_cache_alloc_space/1024/1024/1024;
            const source_Select = document.querySelector('select[name="source_file_cache_unit"]');
            if(source_file_cache_alloc_space >= 1024){
                source_file_cache_alloc_space = SETTINGS.source_file_cache_alloc_space/1024/1024/1024/1024;
                source_Select.value = '2';
            }else{
                source_Select.value = '1';
            }
            $('.source_fileCacheSizeDiv').spinner('value', source_file_cache_alloc_space);
            sourceToggleLimitModeFlag = false;
        }
        // 源机文件缓存路径(可修改)
        if(SETTINGS.source_file_cache_storage_path != '' && SETTINGS.source_file_cache_storage_path != '/opt/vinchin/agent/dbcdp_cache/'){
            sourcefilePathDefault = false;
            $('#source_fileCachePath').val(SETTINGS.source_file_cache_storage_path);
            $('#source_defaultFileCachePath').show();
            $('#source_customFileCachePath').hide();
        }else{
            $('#source_fileCachePath').val('/opt/vinchin/agent/dbcdp_cache/');
            $('#source_defaultFileCachePath').hide();
            $('#source_customFileCachePath').show();
        }
        // 备机文件缓存大小
        if(SETTINGS.file_cache_alloc_space == '-1'){
            $('.backup_custom_cache_size_div').hide();
            $('.backup_cache_size_unlimited_div').show();
            // 使用原生 JavaScript 的 innerHTML 属性
            document.querySelector('button[data-value="backup_cache_button"]').innerHTML = LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM;
            backupToggleLimitModeFlag = true;
        }else{
            let backup_file_cache_alloc_space = SETTINGS.file_cache_alloc_space/1024/1024/1024;
            const backup_Select = document.querySelector('select[name="backup_file_cache_unit"]');
            if(backup_file_cache_alloc_space >= 1024){
                backup_file_cache_alloc_space = SETTINGS.file_cache_alloc_space/1024/1024/1024/1024;
                backup_Select.value = '2';
            }else{
                backup_Select.value = '1';
            }
            $('.backup_fileCacheSizeDiv').spinner('value', backup_file_cache_alloc_space);
            backupToggleLimitModeFlag = false;
        }
        //备机文件缓存路径
        if(SETTINGS.file_cache_storage_path != '' && SETTINGS.file_cache_storage_path != '/opt/vinchin/agent/dbcdp_cache/'){
            backupfilePathDefault = false;
            $('#backup_fileCachePath').val(SETTINGS.file_cache_storage_path);
            $('#backup_defaultFileCachePath').show();
            $('#backup_customFileCachePath').hide();
        }else{
            $('#backup_fileCachePath').val('/opt/vinchin/agent/dbcdp_cache/');
            $('#backup_defaultFileCachePath').hide();
            $('#backup_customFileCachePath').show();
        }
        editFlag && !sourcefilePathDefault ? $('#source_fileCachePath').prop('readonly',false) : $('#source_fileCachePath').prop('readonly',true);
        editFlag && !backupfilePathDefault ? $('#backup_fileCachePath').prop('readonly',false) : $('#backup_fileCachePath').prop('readonly',true);
        if(!editFlag){
            // 非新建不能修改路径
            $('.source_fileCachePath_div').hide();
            $('.backup_fileCachePath_div').hide();
            $('#source_fileCachePath').prop('disabled',true);
            $('#backup_fileCachePath').prop('disabled',true);
        }else{
            $('.source_fileCachePath_div').show();
            $('.backup_fileCachePath_div').show();
            $('#source_fileCachePath').prop('disabled',false);
            $('#backup_fileCachePath').prop('disabled',false);
        }
        $('#ignoreResourceLimit').bootstrapSwitch('state', SETTINGS.ignore_resource_limiting_flag);
        initResourceLimit(backupTargetInfo.node_uuid_list); // 初始化忽略资源限制表格
    }

    //初始化时间策略描述
    var initSpeedStrategyDes = function () {
        var titleDes = "";
        var des = "";
        $('#tasklevelselect').val(speedList.level);
        $('#speedtypeselect').val(speedList.type);
        if (speedList.type == 1) {
            $('#show_type_1').show();
            $('#show_type_2').hide();
            $('#task_type_global_speed_strategy').show();
        } else {
            $('#show_type_2').show();
            $('#show_type_1').hide();
            $('#task_type_global_speed_strategy').hide();
        }

        // 如果是之前的自定义的 方式不变 但是如果是选择的全局限速策略的话，那么需要读取出所有的全局限速策略列表，然后根据列表的id取出限速信息
        if (speedList.type == 1) {
            var global_speed_limit = speedList.uuid
            setTimeout(function () {
                $(".strategy-table #table").bootstrapTable('checkBy', {
                    field: 'uuid',
                    values: [global_speed_limit]
                })
                // 下面的需要初始化全局限速策略表格并携带参数
                let rowData = $(".strategy-table #table").bootstrapTable("getData");

                var datas = [];
                for (var j in rowData) {
                    if (rowData[j].uuid == global_speed_limit) {
                        datas = rowData[j].detail;
                        datas = datas.split('</br>');
                    }
                }
                if (datas.length != 0) {
                    des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + datas.length;
                }

                for (var i in datas) {
                    titleDes += datas[i] + '. ';
                }

                $('.speedlimitDes').html(des);
                $('.speedlimitDes').prop('title', titleDes);
            },50);
        } else {
            var speedInfo = speedList.speedInfo;
            // 自定义
            if (speedInfo.length > 0) {
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedInfo.length;
                var strategy_type = speedList.strategy_type;
                for (var i in speedInfo) {
                    titleDes += speedInfo[i].des + '. ';
                }
                addGlobalStrategy.init({'strategy_type':strategy_type,speedInfo:speedInfo,initSpeedFlag:1});
            }
        }
        $('.speedlimitDes').html(des);
        $('.speedlimitDes').prop('title', titleDes);
    }

    /**
     * 步骤3有效
     */
    const step3Valid = () => {
        // 加密传输是否打开
        data.transport_strategy.encrypt_flag = $('#encrypttransfer').get(0).checked;
        // 加密传输方式
        if(data.transport_strategy.encrypt_flag){
            data.transport_strategy.transport_encrypt_method = $('#encrypttransfermethodselect option:selected').val();
        }
        // 压缩传输是否打开
        data.transport_strategy.compress_flag = $('#compresstransfer').get(0).checked;
        // 压缩等级
        if(data.transport_strategy.compress_flag){
            data.transport_strategy.transport_compress_method = $('#compresstransferselect option:selected').val();
        }
        // 导出线程个数
        data.exp_max_process_num = parseInt($('#exp_thread').val());
        // 导出线程个数
        data.imp_max_process_num = parseInt($('#imp_thread').val());
        // 源机文件缓存大小 单位：字节
        if(!sourceToggleLimitModeFlag){
            let source_file_cache_unit = $('select[name=source_file_cache_unit] option:selected').val();
            data.cache_config.source_file_cache_alloc_space = parseInt($('#source_fileCacheSize').val()) * Math.pow(1024,(parseInt(source_file_cache_unit)+2));
        }else{
            data.cache_config.source_file_cache_alloc_space = -1;
        }
        // 备机文件缓存大小 单位：字节
        if (!backupToggleLimitModeFlag){
            let backup_file_cache_unit = $('select[name=backup_file_cache_unit] option:selected').val();
            data.cache_config.file_cache_alloc_space = parseInt($('#backup_fileCacheSize').val()) * Math.pow(1024,(parseInt(backup_file_cache_unit)+2));
        }else{
            data.cache_config.file_cache_alloc_space = -1;
        }

        // 源机文件缓存路径
        if(sourcefilePathDefault){
            data.cache_config.source_file_cache_storage_path = '';
        }else{
            data.cache_config.source_file_cache_storage_path = $('#source_fileCachePath').val();
            if(!checkFilePath(data.cache_config.source_file_cache_storage_path,'Linux')){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
                return false;
            }
        }

        // 备机文件缓存路径
        if(backupfilePathDefault){
            data.cache_config.file_cache_storage_path = '';
        }else{
            data.cache_config.file_cache_storage_path = $('#backup_fileCachePath').val();
            if(!checkFilePath(data.cache_config.file_cache_storage_path,'Linux')){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
                return false;
            }
        }

        if(delayedLoadFlag){
            // 延迟加载时间
            let days = parseInt($('.days').val());
            let hours = parseInt($('.hours').val());
            let minutes = parseInt($('.minutes').val());
            let seconds = parseInt($('.seconds').val());
            let daysLimit = 7;
            let validateTimeTip = validateTimeInputDetailed(days,daysLimit,hours,minutes,seconds);
            if(!validateTimeTip.isValid){
                UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC,validateTimeTip.message);
                return false;
            }
            let delayTime = days*86400 + hours*3600 + minutes*60 + seconds;
            data.cache_config.delay_load_time = delayTime;
        }else{
            data.cache_config.delay_load_time = 0;
        }

        //传输限速
        data.speed_limit_strategy_list = getSpeedStrategyInfo();
        // 传输网络
        if(networkFlag && data.node_pool_uuid == ''){
            data.transport_strategy.network_uuid = $('#transferNetworkTree').transferNetwork('getSelect').network_uuid;
        }
        // 选择了自定义路径但是没填写
        if(!backupfilePathDefault && data.cache_config.file_cache_storage_path == ''){
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC, LANG.UI_DB_CDP_BACKUP_CUSTOM_PATH_CANNOT_BE_EMPTY);
            return false;
        }

        // dbms接口等高级配置
        data.extend_advanced_config.use_dbms = $('#use_dbms_Switch').get(0).checked;
        data.extend_advanced_config.timed_gen_txn = $('#timed_gen_txn_Switch').get(0).checked;
        data.extend_advanced_config.sync_big_object = $('#sync_big_object_Switch').get(0).checked;
        data.extend_advanced_config.replay_ddl = $('#replay_ddl_Switch').get(0).checked;
        data.extend_advanced_config.ignore_nonsupport_data_type = $('#ignore_nonsupport_data_type_Switch').get(0).checked;
        // 忽略节点资源限制
        data.ignore_resource_limiting_flag = $('#ignoreResourceLimit').get(0).checked;
        // 预加载STEP4
        preStep4();
    }

    /**
     * 预加载STEP4
     */
    const preStep4 = () => {
        // 同步应用
        let synAppStr = 'Oracle' + LANG.UI_CM_CDP_REPLICATION + ":" + sourcetreeNode.name;
        $('.synAppShow').html(synAppStr);
        // 目标节点
        $('.computeNodeShow').html(data.node_text);
        // 备机
        let targetStandByStr = targettreeNode.name;
        $('.standbyShow').html(targetStandByStr);
        // 数据复制节点
        let dataSynNodeHostStr = $('#hostNode option:selected').text();
        let dataSynNodeSlaveStr = $('#slaveNode option:selected').text();
        let dataSynNodeStr = dataSynNodeHostStr + '/' + dataSynNodeSlaveStr;
        $('.dataSynNodeShow').html(dataSynNodeStr);
        // 数据表空间存储目录
        let tablespaceDirSelectValue = $('#tablespaceDir option:selected').val();
        let customDirectoryVal = $('#customDirectory').val();
        if(tablespaceDirSelectValue == '1'){
            // 自定义
            $('.tablespaceDirShow').text(customDirectoryVal);
        }else{
            $('.tablespaceDirShow').text(LANG.UI_DB_CDP_DETAILS_DIRECTORY_OF_THE_SYSTEM_TABLE_SPACE);
        }
        // 自动接管
        let autoTakeOverStr = autoTakeoverFlag ? LANG.UI_PUBLIC_ON : LANG.UI_PUBLIC_OFF;
        $('.autoTakeoverShow').html(autoTakeOverStr);

        $('#serviceIpShiftdiv').hide();
        $('#failbackTargetIpdiv').hide();
        $('#heartFailurediv').hide();
        $('#numberConseFailurediv').hide();
        $('#faultIntervaldiv').hide();
        $('#takeOverNetdiv').hide();


        if(autoTakeoverFlag){
            // 服务IP漂移
            let serviceIpShiftStr = switchIpFlag ? LANG.UI_PUBLIC_ON : LANG.UI_PUBLIC_OFF;
            if(switchIpFlag){
                // 服务IP漂移开关打开
                $('#serviceIpShiftdiv').show();
                $('.serviceIpShiftshow').html(serviceIpShiftStr);
            }else{
                $('#serviceIpShiftdiv').hide();
            }
            // 接管网络配置
            if(isNetWorkShow){
                $('#takeOverNetdiv').show();
                let takeOverNetShowStr = '';
                for(var i =0;i<data.takeover_object.takeover_business_ip_map.length;i++){
                    takeOverNetShowStr += data.takeover_object.takeover_business_ip_map[i].conf_des;
                }
                $('.takeOverNetShow').html(takeOverNetShowStr);
            }

            // 回切通讯IP
            if(!data.sync_object.source_cluster_flag && switchIpFlag){
                $('#failbackTargetIpdiv').show();
                let failbackTargetIpStr = data.takeover_object.failback_target_ip;
                $('.failbackTargetIpShow').html(failbackTargetIpStr);
            }else{
                $('#failbackTargetIpdiv').hide();
            }

            $('#heartFailurediv').show();
            $('#numberConseFailurediv').show();
            $('#faultIntervaldiv').show();

            // 心跳失效次数
            let heartFailureNumStr = data.takeover_object.agent_heartbeat_failure_time + LANG.UI_PUBLIC_SECOND;
            $('.heartFailureShow').html(heartFailureNumStr);
            // 连续故障次数
            let failuresNumStr = data.takeover_object.app_consecutive_failure_num + LANG.UI_PUBLIC_UNIT_COUNT;
            $('.numberConseFailureShow').html(failuresNumStr);
            // 连续检测间隔
            let monitorIntervalStr = data.takeover_object.app_fault_detection_interval + LANG.UI_PUBLIC_SECOND;
            $('.faultIntervalShow').html(monitorIntervalStr);
        }else{
            data.takeover_object.failback_target_ip = '';
            data.takeover_object.switch_ip_flag = false;
            data.takeover_object.takeover_business_ip_map = [];
            data.takeover_object.app_consecutive_failure_num = '';
            data.takeover_object.app_fault_detection_interval = '';
            data.takeover_object.agent_heartbeat_failure_time = '';
        }


        // 传输策略
        let transferStrategyStr = '';
        // 传输网络
        let transferNetworkStr = '';
        if(networkFlag && data.node_pool_uuid == ''){
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
            transferNetworkStr += networkNode.str + '<br>';
            transferStrategyStr += LANG.UI_K8S_TRANSFER_NET + ':' + transferNetworkStr + '<br>';
        }
        // 加密传输
        let encrypttransferStr = $('#encrypttransfer').get(0).checked ? LANG.UI_FILE_ENABLE : LANG.UI_FILE_DISABLE;
        // 加密传输方式
        let encrypttransferMethodStr = $('#encrypttransfermethodselect option:selected').text();
        //压缩传输
        let compresstransferStr = $('#compresstransfer').get(0).checked ? LANG.UI_FILE_ENABLE : LANG.UI_FILE_DISABLE;
        // 压缩等级
        let compresstransferMethodStr = $('#compresstransferselect option:selected').text();
        // 导出线程
        let exp_threadStr = $('#exp_thread').val();
        // 导入线程
        let imp_threadStr = $('#imp_thread').val();


        // 传输限速
        let limitSpeed = getSpeedStrategyInfo().speedInfo;
        let transferSpeedLimitStr = '';
        if(limitSpeed.length == 0){
            transferSpeedLimitStr = LANG.UI_REPORT_NOHAVE;
        }else{
            for(let i = 0;i<limitSpeed.length;i++){
                transferSpeedLimitStr += getSpeedStrategyInfo().speedInfo[i].des + ';';
            }
        }

        transferStrategyStr += LANG.UI_KUBE_ENCRYPTION_TRANSFER + ':' + encrypttransferStr + '<br>';
        if($('#encrypttransfer').get(0).checked){
            transferStrategyStr += LANG.UI_DB_CDP_BACKUP_ENCRYPT_METHOD + ':' + encrypttransferMethodStr + '<br>';
        }
        transferStrategyStr += LANG.UI_FILE_COPY_TRANSFER_COMPRESS + ':' + compresstransferStr + '<br>';
        if($('#compresstransfer').get(0).checked){
            transferStrategyStr += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ':' + compresstransferMethodStr + '<br>';
        }
        transferStrategyStr += LANG.UI_DB_CDP_EXP_THREADS + ':' + exp_threadStr + '<br>';
        transferStrategyStr += LANG.UI_DB_CDP_IMP_THREADS + ':' + imp_threadStr + '<br>';
        transferStrategyStr += LANG.UI_DB_CDP_BACKUP_RATE_LIMITED_TRANSMISSION + ':' + transferSpeedLimitStr + '<br>';
        $('.transferStrategyShow').html(transferStrategyStr);

        // 高级策略
        let highStrategyStr = '';
        // 延迟加载
        let delayLoadStr = $('#delayedLoad').get(0).checked ? LANG.UI_PUBLIC_ON : LANG.UI_PUBLIC_OFF;
        // 延迟加载时间
        let times =  $('.days').val() + LANG.UI_PUBLIC_UNIT_DAY + $('.hours').val() + LANG.UI_PUBLIC_HOUR + $('.minutes').val() + LANG.UI_PUBLIC_MINUTE + $('.seconds').val() + LANG.UI_PUBLIC_SECOND;
        let delayLoadTimeStr = $('#delayedLoad').get(0).checked ? times : '00:00:00:00';
        // 源机文件缓存大小
        let sourcefileCacheSizeStr = sourceToggleLimitModeFlag ? LANG.UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE : $('#source_fileCacheSize').val() + $('select[name=source_file_cache_unit] option:selected').text();
        // 源机文件缓存路径
        let sourcefileCachePathStr = $('#source_fileCachePath').val();
        // 备机文件缓存大小
        let backupfileCacheSizeStr = backupToggleLimitModeFlag ? LANG.UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE : $('#backup_fileCacheSize').val() + $('select[name=backup_file_cache_unit] option:selected').text();
        // 备机文件缓存路径
        let backupfileCachePathStr = $('#backup_fileCachePath').val();

        highStrategyStr += LANG.UI_DB_CDP_BACKUP_LOAD_TIME + ':' + delayLoadStr + '<br>';
        if($('#delayedLoad').get(0).checked){
            highStrategyStr += LANG.UI_JOB_DELAY_LOAD_TIME + ':' + delayLoadTimeStr + '<br>';
        }
        highStrategyStr += $('#use_dbms_Switch').get(0).checked ? LANG.UI_DB_CDP_USE_DBMS + ':' + LANG.UI_PUBLIC_ON: LANG.UI_DB_CDP_USE_DBMS + ':' + LANG.UI_PUBLIC_OFF;
        highStrategyStr += '<br>';
        highStrategyStr += $('#sync_big_object_Switch').get(0).checked ? LANG.UI_DB_CDP_SYNC_BIG_OBJ + ':' + LANG.UI_PUBLIC_ON : LANG.UI_DB_CDP_SYNC_BIG_OBJ + ':' + LANG.UI_PUBLIC_OFF;
        highStrategyStr += '<br>';
        highStrategyStr += $('#replay_ddl_Switch').get(0).checked ? LANG.UI_DB_CDP_REPLAY_DDL + ':' + LANG.UI_PUBLIC_ON: LANG.UI_DB_CDP_REPLAY_DDL + ':' + LANG.UI_PUBLIC_OFF;
        highStrategyStr += '<br>';
        highStrategyStr += $('#ignore_nonsupport_data_type_Switch').get(0).checked ? LANG.UI_DB_CDP_IGNORE_NONSUPPORT_DATA_TYPE + ':'+ LANG.UI_PUBLIC_ON : LANG.UI_DB_CDP_IGNORE_NONSUPPORT_DATA_TYPE + ':' + LANG.UI_PUBLIC_OFF;
        highStrategyStr += '<br>';
        highStrategyStr += LANG.UI_DB_CDP_BACKUP_SOURCE_MACHINE_FILE_CACHE_SIZE + ':' + sourcefileCacheSizeStr + '<br>';
        highStrategyStr += LANG.UI_DB_CDP_BACKUP_SOURCE_MACHINE_FILE_CACHE_PATH + ':' + sourcefileCachePathStr + '<br>';
        highStrategyStr += LANG.UI_DB_CDP_BACKUP_STANDBY_FILE_CACHE + ':' + backupfileCacheSizeStr + '<br>';
        highStrategyStr += LANG.UI_DB_CDP_BACKUP_STANDBY_FILE_PATH + ':' + backupfileCachePathStr + '<br>';
        highStrategyStr += $('#ignoreResourceLimit').get(0).checked ? LANG.UI_DB_CDP_IGNORE_RESOURCE_LIMIT + ':' + LANG.UI_PUBLIC_ON : LANG.UI_DB_CDP_IGNORE_RESOURCE_LIMIT + ':' + LANG.UI_PUBLIC_OFF;
        $('.highLevelStrategyShow').html(highStrategyStr);
    }

    /**
     * 备份策略-传输网络
     */
    const initNetworkList = () => {
        //参数
        if(networkFlag && data.node_uuid != ""){   //初始化传输网络
            $('.transfernetworkDiv').show();
            $('#transferNetworkTree').transferNetwork({
                node_uuid: data.node_uuid,
                network_uuid: SETTINGS.network_uuid,
                network_pool_uuid: SETTINGS.node_pool_uuid
            });

        }else{
            $('.transfernetworkDiv').hide();
        }
    }

    /**
     * 获取任务名
     */
    const getTaskName = () => {
        // 默认任务名
        let param = {'job_name': LANG.UI_VIRTUAL_DATABASE_REAL_TIME};
        pAjaxRequest(param, '/api/v1/jobs/name', 'GET', function (d) {
            var data = d.data;
            $('#jobname').val(data.value);
        });
    }

    /**
     * 提交
     */
    const submit = () => {
        if ($.trim($("#jobname").val()) == '') {
            $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
            return;
        }
        $('.jobnametip').hide();
        // 任务名
        data.job_name = $.trim($("#jobname").val());
        data.job_uuid = $('#task_uuid').val();
        //TODO提交
        Metronic.blockUI({target: '#dbcdpbackupcontent', animate: true, cenrerY: true,});
        pAjaxRequest(data, '/api/v1/dbcdp/jobs/sync', 'PUT', function (d) {
            Metronic.unblockUI('#dbcdpbackupcontent');
            if (operateResponseList(d)) {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
        })
    }

    /**
     * 初始化历史任务信息
     */
    const initOldSettings = function () {
        var jobUuid = $('#task_uuid').val();
        pAjaxRequest({}, '/api/v1/dbcdp/jobs/'+jobUuid+'/sync', 'get', function (d) {
            SETTINGS = d.data.sync_object;
            // 网卡配置
            if(!SETTINGS.takeover_business_ip_map){
                SETTINGS.takeover_business_ip_map = {};
                SETTINGS.takeover_business_ip_map.takeover_business_ip_map = [];
                isNetWorkShow = false;
            }else{
                isNetWorkShow = true;
            }
            // 默认的服务IP漂移开关
            switchIpFlag = SETTINGS.switch_ip_flag;
            data.takeover_object.switch_ip_flag = switchIpFlag;
            // 自动接管开关
            autoTakeoverFlag = SETTINGS.auto_takeover_flag;
            // 延迟加载时间
            data.cache_config.delay_load_time = SETTINGS.delay_load_time;
            // 延迟加载开关标志
            delayedLoadFlag = SETTINGS.delay_load_time !=0 ? true : false;
            // 如果任务状态为新建(1)
            editFlag =  SETTINGS.task_status == 1 ? true :false;
            // 限速策略
            speedList = SETTINGS.speedInfo;
            // 资源池uuid
            node_pool_uuid = SETTINGS.node_pool_uuid;
            // 网络uuid
            network_uuid = SETTINGS.network_uuid;
            initInstanceTree();
            showTableSpaceDir();
            initExtendAdvancedConfig(SETTINGS.extend_advanced_config);
            initBackupTarget();
        });
    }

    /**
     * 秒转成时分秒
     * @param seconds
     * @return {`${string}:${string}:${string}`}
     */
    const secondsToTimeFormat = function (seconds) {
        const hours = Math.floor(seconds / 3600);
        seconds %= 3600;
        const minutes = Math.floor(seconds / 60);
        seconds %= 60;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    /**
     * @function 获取授权相关配置信息
     */
    let moduleAvailable = false;
    const licenseNumIsEnough = function(){
        pAjaxRequest({ type: 'a', module: 'copy_db' },'/api/v1/system/auth/base_info','GET',function (res){
            if (res.success) {
                if(!res.data.license_flag){
                    // 授权期限
                    UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE,LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
                }
                if (res.data.auth_type === CONF.FLAG.SET) {
                    // 数量授权（数据库复制无容量授权）
                    // 总授权数小于于已用授权数
                    if(res.data.total <= res.data.used){
                        UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_DB_CDP_AUTH_INSUFFICIENT_TIPS);
                    }else{
                        moduleAvailable = true;
                    }
                }else{
                    moduleAvailable = true;
                }
            }else{
                UIToastr.showWarning(LANG.UI_MACHINE_OS_LICENSE_CHECK, LANG.UI_MACHINE_OS_LICENSE_GET_FAILED);
            }
        },false)
    }

    return {
        //main function to initiate the module
        init: function () {
            wizardInit();
            licenseNumIsEnough();
            initListeners();
            initSpinner();
            initOldSettings();
            initDbTypeSelect();
        },
    };

}()


jQuery(document).ready(function () {
    Dbcdpbackup.init();
});