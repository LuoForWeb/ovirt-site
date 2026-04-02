var Dbcdprecover = function () {

    var data = {
        "job_name":"",
        "module_type":CONF.MODULE_TYPE.DBCDP,
        "task_type":CONF.TASK_TYPE.CDP_DB_RECOVERY,
        'node_uuid':'',  //管理节点
        'exp_max_process_num':'',
        'imp_max_process_num':'',
        'net_model': 2,  //网络模式
        'speed_limit_strategy_list': {    //限速策略

        },
        'recovery_target_datetime': '',
        'recovery_object': {
            'backup_id': '',
            'source_agent_uuid': '',            //第一步选择的备机uuid
            'task_source_agent_uuid':'',        //同步的源机
            'source_app_uuid': '',              //数据源应用uuid
            'source_app_type': '',              //数据源应用类型
            'source_app_version': '',           //数据源应用版本
            'source_app_name': '',              //数据源（备机）应用名
            'target_agent_cluster_uuid' :'',    //如果选择的是集群，目标机的cluster_uuid
            'target_app_uuid': '',              //目标应用uuid
            'target_app_type': '',              //目标应用类型
            'target_app_version': '',           //目标机应用版本
            'target_app_name': '',              //目标机应用名
            'target_agent_uuid': '',            //目标机的uuid
            'target_agent_name': '',            //目标机名
            'recovery_level': 1,                //恢复级别，0:unknown 1：instance 2:database 3:user 4:tabl
            'recovery_type': 1,                 //恢复类型2:增量数据  3:全量+增量
            'source_cluster_flag': false,       //1：true 2：false
            'target_cluster_flag': false,       //目标机器
        },
        'cache_config': {
            "source_file_cache_alloc_space": 0,
            'file_cache_alloc_space': 0,
            'file_cache_storage_path': '',
        },
        'transport_strategy': {
            'transport_encrypt_flag': false,  //0:unknown 1:set 2:unset
            'transport_compress_flag': false,
            'transport_block_size': 0,
            'network_uuid': '',
            "transport_compress_method":"",
            "transport_encrypt_method":""
        },
        'app_tablespace_dir':'',
        'recovery_timepoint_type':'',
        'recovery_target_scn':'',
        'extend_advanced_config':{
            'use_dbms':false,
            'timed_gen_txn':true,
            'sync_big_object':true,
            'replay_ddl':true,
            'ignore_nonsupport_data_type': true,
        },
        'select_copy_users':[],
    }

    /*_______________________________*/
    /*新的*/
    //STEP1
    // 选中的源机树节点
    let sourcetreeNode = {};
    // 源机树(所有)
    let zTreeAgent= [];
    // 存储选择源的uuid
    let agentList = [];
    // 存储选择目标的uuid
    let targetAgentList = [];
    // 是否可以作为恢复源的条件
    let backup_info_status;
    // 刷新icon是否显示
    let isRefreshShow = false;
    // scn
    let latest_recv_transaction_scn = '';
    let last_redo_replay_scn = '';
    // 源机的应用信息
    let sourceAgentAppInfo = {};
    // 源机的应用uuid/集群uuid
    let selectedBackupUuid = '';
    // 时间范围标志
    let time_unit;
    // 时间轴
    var myChart;
    // dataZoom的结束百分比,此处暂定100%,根据实际效果调整.
    var _chartEndPercent = 100;
    // 选中的时间点
    var _checkTime = '';
    // 恢复-恢复源相关联的同步源(同步任务的源机uuid)
    let syncSourceAgentUuid = '';
    // 恢复-恢复源相关联的同步源(同步任务的源机集群uuid)
    let syncSourceClusterUuid = '';
    // 重放时间时间戳
    let _lastReplayTimestamp;
    // 最新接收事务时间戳
    let _lastRecvTransactionTimestamp;
    // dataZoom的起始百分比,此处暂定70%,根据实际效果调整.(可以根据加载的时间区间范围确定起始百分比)
    let _chartStartPercent = 70;
    //最新接收事务时间点
    let _lastRecvTransactionTimePoint = '';
    let _hisPortletBodyWidth = 0;
    let _hisportletBodyHeight = 0;
    // 时间点类型，0：无效时间点，1：任意时间点；2：事件点；
    let _timepointType = 1;
    // 事务点:2
    let _timepointEventType;
    // 恢复事件点时是否有效flag
    let _checkTimeValid = false;
    // 延迟时间标志
    let delayTimeFlag = false;
    // 未开启延迟存放时的最新时间点
    let newTime;
    // 时间轴默认加载的间隔
    let _timeInterval = 1;
    // 源机文件默认缓存路径标志
    let sourcefilePathDefault = true;
    // 备机文件默认缓存路径标志
    let backupfilePathDefault = true;
    // 管理节点uuid
    let nodeUuid = '';
    //STEP2
    // 目标机树(所有)
    let targettreeNode = {};
    // 目标机树(已选)
    let targetArr = [];
    // 目标机的客户端信息
    let targetAgentAppInfo = {};
    // 源机实例用户树
    let sourceInstanceUserTree = [];
    // 目标机实例用户树
    let targetInstanceUserTree = [];
    // 选择同步任务的源节点标志
    let syncSourceNodeFlag = false;
    // 应用有效标志
    let appValidFlag = false;
    // 文件默认缓存路径标志
    let filePathDefault = true;
    let select_copy_users_value = [];
    let timepoint_type = '';
    let networkFlag = false;
    let net_model = '';
    let backupTargetInfo;
    let default_target_file_cache_path = '';

    var initListeners = function () {
        $(".popovers").popover();

        $('.recoveryTimeType').on('click',changeRecoveryTimeType);

        $('#inputRecoveryTimepoint').blur(function () {checkdate()});

        // 切换时间范围
        $('#changeTimeInterval').on('change', changeTimeIntervalType);

        // 修改时间控件
        $('#inputRecoveryTimepoint').on('change', changeRecoverTime);

        // 数据表空间存储目录
        $('#tablespaceDir').on('change',changeTableSpaceDirSelect);

        // 自定义备机文件缓存路径
        $('#backup_customFileCachePath').on('click', clickbackupCustomFileCachePath);

        // 默认备机文件缓存路径
        $('#backup_defaultFileCachePath').on('click', clickbackupfaultFileCachePath);

        // 同步大对象等高级配置
        $('#sync_big_object_Switch').bootstrapSwitch('state', true);
        $('#timed_gen_txn_Switch').bootstrapSwitch('state', true);
        $('#replay_ddl_Switch').bootstrapSwitch('state', true);

        // 点击li事件信息  获取所有的事件信息
        $('#timepointType li').on('click', getTabInfo);

        // 管理节点改变
        $('#manageSelectnode').on('change', switchManageNodeSelect);

        // 跳转复制页面
        $('#tobackup').on('click', function () {
            LOCATION('./content/dbcdp/dbcdp_backup.php', 'dbcdpcopy');
        });

        // 加密传输
        $('#encrypttransfer').on('switchChange.bootstrapSwitch', encryptTransfer)

        // 压缩传输
        $('#compresstransfer').on('switchChange.bootstrapSwitch', compressTransfer)

        // 源机/备机文件缓存大小
        $('.unlimit_limit_toggle').off().on('click',toggleLimitMode);
    }

    /**
     * 如果没有开启延迟重放，则任务类型只有“恢复到最新时间”
     */
    let initRecoveryTimeTypeStatus = () => {
        const recovery_time_type = document.getElementById('recovery_time_type');
        recovery_time_type.innerHTML = '';

        let options = `<option value="1">` + LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST + `</option>`;
        if(delayTimeFlag) {
            switch(timepoint_type) {
                case 0:
                    options += `<option value="2">`+ LANG.UI_VERIFY_SELECT_TIMEPOINT_SELECT +`</option><option value="3">`+ LANG.UI_DB_CDP_DETAILS_TIME_METHOD_SPECIFIED_SCN +`</option>`;
                    break;
                case 2:
                    options += `<option value="2">`+ LANG.UI_VERIFY_SELECT_TIMEPOINT_SELECT +`</option>`;
                    break;
                case 3:
                    options += `<option value="3">`+ LANG.UI_DB_CDP_DETAILS_TIME_METHOD_SPECIFIED_SCN +`</option>`;
                    break;
            }
        }
        recovery_time_type.innerHTML = options;
    }

    /**
     * 切换恢复时间类型
     */
    var changeRecoveryTimeType = function (){
        $('.recoveryTimeDiv').hide();
        $('.inputRecoveryTimeDIv').hide();
        $('.takeoverScnDIv').hide();
        var selectedValue = parseInt($(this).val());
        if(selectedValue == 2){
            // 选择指定时间点
            if(delayTimeFlag){
                // 打开了延迟重放
                $('.recoveryTimeDiv').show();
            }else{
                $('.inputRecoveryTimeDIv').show();
            }
        }else if(selectedValue == 3){
            // 选择指定scn
            $('.takeoverScnDIv').show();
        }
    }

    /**
     * 校验输入时间格式正确性
     */
    var checkdate = function () {
        var reg = /^[1-9]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+(20|21|22|23|[0-1]\d):[0-5]\d:[0-5]\d$/
        var str1 = $('#inputRecoveryTimepoint').val();
        if (!reg.test(str1)) {
            $('#inputRecoveryTimepoint').val('');
            _timepointType = 0;
            $('#inputRecoveryTimepoint').css('color', "#F3565D");
            $('#inputRecoveryTimepoint').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
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
        }else{
            $('.customDirectory_div').hide();
        }
    }

    /**
     * 修改时间控件
     */
    const changeRecoverTime = () => {
        var restoreTimepoint = $('#inputRecoveryTimepoint').val();
        verifyTimepointisValid(restoreTimepoint);
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
     * 默认备机文件缓存路径
     */
    const clickbackupfaultFileCachePath = () => {
        backupfilePathDefault = true;
        $('#backup_defaultFileCachePath').hide();
        $('#backup_customFileCachePath').show();
        $('#backup_fileCachePath').attr("readonly", "readonly");
        $('#backup_fileCachePath').val(default_target_file_cache_path);
    }

    /**
     * 切换管理节点
     */
    const switchManageNodeSelect = function () {
        data.node_uuid = $('#manageSelectnode option:selected').val();
    }

    const encryptTransfer= function () {
        if (this.checked) {
            $(".encrypttransfermethoddiv").show();
        } else {
            $(".encrypttransfermethoddiv").hide();
        }
    }

    const compressTransfer = function () {
        if (this.checked) {
            $(".compressgradediv").show();
        } else {
            $(".compressgradediv").hide();
        }
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



    var wizardInit = function () {
        if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function (tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
            // set wizard title
            // set done steps
            jQuery('li', $('#dbcdprecovercontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#dbcdprecovercontent').find('.button-previous').css('visibility', 'hidden');
                $('#dbcdprecovercontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#dbcdprecovercontent').find('.button-previous').css('visibility', 'visible');
                $('#dbcdprecovercontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#dbcdprecovercontent').find('.button-next').hide();
                $('#dbcdprecovercontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#dbcdprecovercontent').find('.button-next').show();
                $('#dbcdprecovercontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#dbcdprecovercontent').bootstrapWizard({
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
                        if (!step1Valid()) {
                            return false;
                        }
                        break;
                    case 2:
                        if (!step2Valid()) {
                            return false;
                        }
                        break;
                    case 3:
                        if (!step3Valid()) {
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
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#dbcdprecovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#dbcdprecovercontent').find('.button-previous').css('visibility', 'hidden');
        $('#dbcdprecovercontent .button-submit').click(submit).css('visibility', 'hidden');
    };

    /**
     * 初始化数据库类型
     */
    const initDbTypeSelect = function () {
        let dbType = $('#dbtype');
        let option = $("<option>").text('ORACLE').val('ORACLE');
        dbType.append(option);
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
            setOracleRecoverTree(res.data.rows,'#agent_tree',checkOracleRecoverNode);
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
                app_name:standaloneNode.app_name,
                app_uuid: standaloneNode.app_uuid,
                app_type:standaloneNode.app_type,
                app_version:standaloneNode.app_version,
                agent_uuid: standaloneNode.agent_info.agent_uuid,
                cluster_flag: false,
                cluster_uuid: '',
                online_flag: standaloneNode.agent_info.online_flag,
                net_model: standaloneNode.agent_info.net_model,
                auth_flag: standaloneNode.agent_info.authorization_module.dbcdp,
                db_cdp_recover_info:standaloneNode.db_cdp_recover_info,
                db_cdp_recover_task:standaloneNode.db_cdp_recover_task,
                delay_time_flag:standaloneNode.delay_time_flag,
                source_agent_uuid:standaloneNode.source_agent_uuid,
                timepoint_uuid:standaloneNode.timepoint_uuid,
                last_redo_replay_scn:standaloneNode.last_redo_replay_scn,
                latest_recv_transaction_scn:standaloneNode.latest_recv_transaction_scn,
                select_copy_users:standaloneNode.select_copy_users,
                timepoint_type:standaloneNode.timepoint_type,
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
                    app_name:instanceInfo.app_name,
                    app_uuid: instanceInfo.app_uuid,
                    app_type:instanceInfo.app_type,
                    app_version:instanceInfo.app_version,
                    agent_uuid: instanceInfo.agent_info.agent_uuid,
                    cluster_flag: instanceInfo.cluster_info.cluster_flag,
                    cluster_uuid: instanceInfo.cluster_info.cluster_uuid,
                    online_flag: instanceInfo.agent_info.online_flag,
                    net_model: instanceInfo.agent_info.net_model,
                    auth_flag: instanceInfo.agent_info.authorization_module.dbcdp,
                    db_cdp_recover_info:instanceInfo.db_cdp_recover_info,
                    db_cdp_recover_task:instanceInfo.db_cdp_recover_task,
                    delay_time_flag:instanceInfo.delay_time_flag,
                    source_agent_uuid:instanceInfo.source_agent_uuid,
                    timepoint_uuid:instanceInfo.timepoint_uuid,
                    last_redo_replay_scn:instanceInfo.last_redo_replay_scn,
                    latest_recv_transaction_scn:instanceInfo.latest_recv_transaction_scn,
                    select_copy_users:instanceInfo.select_copy_users,
                    timepoint_type:instanceInfo.timepoint_type
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
                db_cdp_recover_info:clusterInfo.db_cdp_recover_info,
                db_cdp_recover_task:clusterInfo.db_cdp_recover_task,
                app_name:clusterInfo.app_name,
                app_service_name: clusterInfo.app_service_name,
                app_type:clusterInfo.app_type,
                app_version:clusterInfo.app_version,
                online_flag: clusterOnlineFlag,
                auth_flag: clusterAuthFlag,
                timepoint_uuid:clusterInfo.timepoint_uuid,
                instanceInfo:clusterInfo.instanceInfo,
                last_redo_replay_scn:clusterInfo.last_redo_replay_scn,
                latest_recv_transaction_scn:clusterInfo.latest_recv_transaction_scn,
                select_copy_users:clusterInfo.select_copy_users,
                timepoint_type:clusterInfo.timepoint_type
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

    /**
     * Oracle节点选中了
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const checkOracleBackupNode = (ev, treeId, treeNode) => {

    };

    /**
     * 设置未授权或者离线节点的样式
     */
    function setFontCss(treeId, treeNode) {
        if (!treeNode.onlineFlag && treeNode.eventtype != 'group') {
            var css = {color: "gray", "font-weight": "normal"};
        }
        return css;
    };

    /**
     * 点击左边树节点
     * @param e
     * @param treeId
     * @param treeNode
     */
    const checkOracleRecoverNode = function (e, treeId, treeNode) {
        sourcetreeNode = treeNode;
        delayTimeFlag = treeNode.delay_time_flag;
        net_model = treeNode.net_model;
        // 客户端连接服务端
        if(net_model == 2){
            networkFlag = true;
        }
        if(treeNode.cluster_flag){
            delayTimeFlag = treeNode.children[0].delay_time_flag;
        }
        select_copy_users_value = treeNode.select_copy_users;
        $('.recoveryTimeDiv').hide();
        agentList = [];
        // 单选节点
        let allNodes = zTreeAgent.getCheckedNodes();
        for(let i = 0; i < allNodes.length; i++){
            zTreeAgent.checkNode(allNodes[i], false, false, false);
        }
        zTreeAgent.checkNode(treeNode, true, false, false);
        if (treeNode.eventtype === 'cluster') {
            // 显示集群
            data.recovery_object.source_cluster_flag = true;
            selectedBackupUuid = treeNode.cluster_uuid;
            // 遍历集群下的单节点
            for(let node of treeNode.children){
                if(node.online_flag){
                    // 在线单节点
                    data.recovery_object.source_app_uuid = node.app_uuid;
                    data.recovery_object.source_agent_uuid = node.agent_uuid;
                    data.recovery_object.source_cluster_flag = true;
                    data.recovery_object.source_cluster_uuid = node.cluster_uuid;
                    data.timepoint_uuid = node.timepoint_uuid;
                    timepoint_type = node.timepoint_type;
                    break;
                }
            }
        }else if (treeNode.eventtype === 'instance' && treeNode.pId ==='standalone') {
            // 消息结构
            data.recovery_object.source_app_uuid = treeNode.app_uuid;
            data.recovery_object.source_agent_uuid = treeNode.agent_uuid;
            data.recovery_object.source_cluster_flag = false;
            data.recovery_object.source_cluster_uuid = treeNode.cluster_uuid;
            data.timepoint_uuid = treeNode.timepoint_uuid;
            timepoint_type = treeNode.timepoint_type;
            selectedBackupUuid = treeNode.app_uuid;
            // 源机为单节点，高级配置dbms接口只能为关闭状态
            $('#use_dbms_Switch').bootstrapSwitch('disabled', true);
        }
        backup_info_status = treeNode.db_cdp_recover_info.backup_info_status;
        let is_not_recovery_source_status  = [0,2,3,4];
        isRefreshShow = !is_not_recovery_source_status.includes(backup_info_status);
        // 获取源机的应用信息
        sourceAgentAppInfo.app_type = treeNode.app_type;
        sourceAgentAppInfo.app_version = treeNode.app_version;
        agentList.push(selectedBackupUuid);
        initRecoveryTimeTypeStatus();
        // 获取源机的主机信息
        getHostInfo(data.recovery_object.source_agent_uuid);

        // 获取时间范围
        getTimeRange(data.recovery_object.source_agent_uuid);

        // 获取流量信息
        getAgentTimelineData(data.recovery_object.source_agent_uuid);

        // 显示时间点内容
        $('.timepointContent').show();
        $('.notipsDiv').hide();

        // scn范围
        last_redo_replay_scn = treeNode.last_redo_replay_scn;
        latest_recv_transaction_scn = treeNode.latest_recv_transaction_scn;
        // 获取scn范围
        getScnRange();
        // scn范围检测
        $('.takeoverScn').off().on('blur',function(){
            let takeoverScnVal = $(this).val();
            if(takeoverScnVal < last_redo_replay_scn || takeoverScnVal > latest_recv_transaction_scn){
                UIToastr.showWarning(LANG.UI_CM_TAKEOVER_CONFIGURE, LANG.UI_DB_CDP_SCN_TIPS);
                return;
            }
        })
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
            let checkNodes = tree[agentKey].getCheckedNodes();
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
     * 获取选取恢复源的主机(同步时的源机)信息
     */
    const getHostInfo =  (agentuuid) => {
        pAjaxRequest({}, '/api/v1/dbcdp/restore_data/' + agentuuid + '/agentInfo', 'GET',  (d) => {
            let data = d.data;
            // 同步时的源机信息
            syncSourceAgentUuid = data.agent_uuid;
            syncSourceClusterUuid = data.cluster_uuid;
            let agentName = data.agent_name;
            let hostName = data.agent_hostname;
            $('#hostInfo').html(agentName + '(' + hostName + ')');
        })
    }

    /**
     * 获取scn范围
     */
    const getScnRange = () => {
        $('#replay_scn').html(last_redo_replay_scn);
        $('#transaction_scn').html(latest_recv_transaction_scn);
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
            let last_redo_replay_scn = data.last_redo_replay_scn;
            let latest_recv_transaction_scn = data.latest_recv_transaction_scn;
            newTime = latestRecvTransactionTime;
            _lastReplayTimestamp = lastReplayTime == '0000-00-00 00:00:00' ? 0 : new Date(lastReplayTime).getTime();
            _lastRecvTransactionTimestamp = new Date(data.latest_recv_transaction_time).getTime();
            $('#timeRange').html(lastReplayTime + '-' + latestRecvTransactionTime);
            $('#replay_scn').html(last_redo_replay_scn);
            $('#transaction_scn').html(latest_recv_transaction_scn);
        },false);
    }

    /**
     * 切换时间范围
     */
    var changeTimeIntervalType = function () {
        // 时间范围
        var timeIntervalType = $('#changeTimeInterval').val();
        _chartStartPercent = 70;
        _timeInterval = timeIntervalType;
        getAgentTimelineData(data.recovery_object.source_agent_uuid);
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
            Metronic.unblockUI({target: '#RecoveryTimeline'});
            let data = d.data;
            var dataLength = data.time_data.length;
            _chartStartPercent = dataZoomUnit(dataLength);  //数据的个数
            if (dataLength > 0) {
                // 所有得时间点
                time_data = data['time_data'];
                // 最新时间点
                _lastRecvTransactionTimePoint = time_data[time_data.length - 1][0];
                //echarts图
                loadTimelineChart(time_data);
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
            if (rawData[i][3] == 2) {
                // 事务点
                size.push(7);  	//圆点大小
                lablePointNumber = rawData[i][2];
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
                        show: isRefreshShow,
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
                // 开启延迟加载时间
                if(delayTimeFlag){
                    verifyTimepointisValid(checkTime);
                    if(_checkTimeValid) {
                        $('#inputRecoveryTimepoint').val(checkTime);
                    }
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
            language:  'zh-CN',
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-MM-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "top-right" : "top-left"),
            forceParse:false
        });
        if(delayTimeFlag){
            // 开启延迟重放
            $('#inputRecoveryTimepoint').val(_lastRecvTransactionTimePoint);  //_lastRecvTransactionTimePoint是echarts图最新的时间点
            verifyTimepointisValid(_lastRecvTransactionTimePoint);
        }else {
            // 关闭延迟重放
            $('.inputRecoveryTimeInput').val(newTime);
        }
    }

    /**
     * 检查时间是否有效
     * @param checkTime
     * @return {boolean}
     */
    const verifyTimepointisValid = (checkTime) => {
        _checkTimeValid = false;
        let checkTimestamp = new Date(checkTime).getTime();
        if (checkTimestamp >= _lastReplayTimestamp && checkTimestamp <= _lastRecvTransactionTimestamp) {
            //如果选中的时间在时间范围内
            $('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_EFFECTIVE_TIME);
            $('#timePointValidity').css('color', "#45B6AF");
            _checkTimeValid = true;
        }else{
            $('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
            $('#timePointValidity').css('color', "#F3565D");
        }
    }

    /**
     * 解析选中时间的事件信息
     */
    const parseMarkPointInfo =  () => {
        // 事务
        $("#recoveryAnytimeLi").removeClass("active");
        $("#agentTransactionInfoLi").attr("class", "active");
        $('#anyPointTime').hide();
        $('#transactionInfo').show();
        loadCheckAgentEventInfo();
    };




    /**
     * 初始化表格
     * type==1:事件信息
     * type==2:事务信息
     * 恢复这没有时间信息，会改
     */
    var loadCheckAgentEventInfo = () => {
        var columns = [];
        if(delayTimeFlag){
            columns.push(
                {
                    field: 'checkbox',
                    checkbox:true
                },
            )
        };
        columns.push(
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
            });
        var options = {
            vin_url: '/api/v1/dbcdp/restore_data/' + data.recovery_object.source_agent_uuid +'/sync/'+syncSourceAgentUuid+'/transaction',
            vin_params: function () {
                var params = {};
                params.transaction_id = 70;
                params.event_time = _checkTime;
                return params;
            },
            vin_method: 'GET',
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
            singleSelect:true,
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
                //
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
            onCheck :(row) =>{
                // 如果开启了延迟重放，选择的事务在时间范围内就填充到 恢复时间点
                // 事务时间
                let transactionTime = new Date(row.transaction_time).getTime();
                if(delayTimeFlag && transactionTime >= _lastReplayTimestamp && transactionTime <= _lastRecvTransactionTimestamp){
                    $('#inputRecoveryTimepoint').val(row.transaction_time);
                    verifyTimepointisValid(row.transaction_time);
                }
            },
            resizable: false,
            singleSelect: true,
            columns: columns,
        }

        $('#transactionInfoTable').bootstrapTable('destroy');
        $('#transactionInfoTable').baseTableConfig().init(options);
    }

    /**
     * step1有效
     * @return {boolean}
     */
    const step1Valid = function () {
        // 选择源
        let agentNodes = zTreeAgent.getCheckedNodes();
        if (!agentNodes.length) {
            UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, LANG.UI_DB_BACKUP_SELECT_CLIENT);
            return false;
        }

        // 不符合创建恢复任务的条件
        let warmTips = '';
        switch (backup_info_status) {
            case 0:
                warmTips = LANG.UI_DB_CDP_RECOVER_CREATE_TIP1;
                break;
            case 2:
                warmTips = LANG.UI_DB_CDP_RECOVER_CREATE_TIP2;
                break;
            case 3:
                warmTips = LANG.UI_DB_CDP_RECOVER_CREATE_TIP3;
                break;
            case 4:
                warmTips = LANG.UI_DB_CDP_RECOVER_CREATE_TIP4;
                break;

        }
        if(backup_info_status == 0 || backup_info_status == 2 || backup_info_status == 3 || backup_info_status == 4){
            UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, warmTips);
            return false;
        }

        // 选择的恢复源已在恢复任务中
        if(!!agentNodes[0].db_cdp_recover_task.task_name){
            UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, LANG.UI_DB_CDP_RECOVER_TASK_TIP +'(' + agentNodes[0].db_cdp_recover_task.task_name + ')'+LANG.UI_DB_CDP_RECOVER_TASK_TIP2);
            return false;
        }

        // 选择恢复时间点
        let recoveryTypeVal = $('.recoveryTimeType option:selected').val();
        if(parseInt(recoveryTypeVal) == CONF.FLAG.SET || !delayTimeFlag){
            // 最新时间点
            data.recovery_target_datetime = '';
            data.recovery_timepoint_type = CONF.FLAG.SET;
            data.recovery_target_scn = '';
        }else if(parseInt(recoveryTypeVal) == CONF.FLAG.UNSET && delayTimeFlag){
            let recoveryTimepoint = $('#inputRecoveryTimepoint').val();
            data.recovery_target_datetime = recoveryTimepoint;
            data.recovery_timepoint_type = CONF.FLAG.UNSET;
            data.recovery_target_scn = '';
            if ((delayTimeFlag && recoveryTimepoint == '') || (!_checkTimeValid && delayTimeFlag)) {
                // 开启了延迟重放且恢复时间点为空
                // 开启了延迟重放且恢复时间点无效
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_DB_CDP_RECOVER_PLEASE_CHECK_THE_RECOVERY_TIME_POINT);
                return false;
            }
        }else if(parseInt(recoveryTypeVal) == 3){
            let takeover_scn = $('.takeoverScn').val();
            data.recovery_timepoint_type = 3;
            data.recovery_target_scn = takeover_scn;
        }

        // 预加载备机
        initRecoveryTarget();
        if(targetArr.length == 0 ){
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_RECOVER, LANG.UI_DB_CDP_BACKUP_NO_BACKUP_MACHINES_AVAILABLE);
            return false;
        }
        // 因为在step1之后会重新初始化step2中的数据 所以目标uuid先置空
        data.recovery_object.target_agent_uuid = '';
        return true;
    }


    /**
     * step2-初始化恢复目标客户端
     */
    const initRecoveryTarget =  () => {
        let param = {db_type: CONF.DB_TYPE.ORACLE};
        // 获取实例列表
        // 对目标机的要求
        // 1、恢复可以恢复到原机(此时数据类型为全量+增量)或者异机(数据类型为全量+增量)
        // 2、不能为已有同步任务的源端和目的端
        // 3、不能为已有恢复任务的目的端
        pAjaxRequest(param, '/api/v1/db/instances', 'GET', res => {
            let rows = res.data.rows;
            // 返回的task_uuid和task_name为空则可作为恢复目标、复制任务的源端可以作为恢复的目的端(且不需要进行应用的校验)
            let backupAgentArr = [];
            for(row of rows){
                let agentInfo = row.agent_info;
                let recoverInfo = row.db_cdp_recover_info;
                let clusterFlag = row.cluster_info.cluster_flag;
                let clusterUuid = row.cluster_info.cluster_uuid;
                let cluster_flag = row.cluster_info.online_flag;
                if(!clusterFlag){
                    // 单
                    if((recoverInfo.length == 0 && agentInfo.online_flag && agentInfo.agent_uuid != data.recovery_object.source_agent_uuid) || syncSourceAgentUuid == agentInfo.agent_uuid){
                        // 无任务、在线、不是源机
                        // 应用类型相同 源机应用版本需大于等于主机
                        // 同步的源可以作为恢复的目的端
                        backupAgentArr.push(row);
                    }
                }else{
                    // 集群
                    if((recoverInfo.length == 0 && cluster_flag && clusterUuid != data.recovery_object.source_cluster_uuid) || syncSourceClusterUuid == clusterUuid){
                        // 集群(不能是和源相同的集群且无任务)
                        backupAgentArr.push(row);
                    }else if(recoverInfo.length !=0){
                        for(var i =0;i<recoverInfo.length;i++){
                            if(syncSourceAgentUuid == recoverInfo[i].source_agent_uuid){
                                backupAgentArr.push(row);
                            }
                        }
                    }
                }
            }
            targetArr = backupAgentArr;
            $('.dbMapDetaildiv').hide();
            $('.synNodediv').hide();
            setOracleTargetTree(backupAgentArr,'#target_agent_tree',checkOraclesStandbyNode);
        },false);
    }

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
                db_cdp_recover_info:standaloneNode.db_cdp_recover_info,
                db_cdp_recover_task:standaloneNode.db_cdp_recover_task,
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
                    db_backup_info: instanceInfo.db_backup_info,
                    auth_flag: instanceInfo.agent_info.authorization_module.dbcdp,
                    db_cdp_recover_info:instanceInfo.db_cdp_recover_info,
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
                cluster_flag: true,
                cluster_name: clusterInfo.cluster_name,
                cluster_service_ip: clusterInfo.cluster_service_ip,
                app_service_name: clusterInfo.app_service_name,
                online_flag: clusterOnlineFlag,
                auth_flag: clusterAuthFlag,
            });
        }
        zTreetTargetAgent = $.fn.zTree.init($(id), getBackupTreeSetting(checkedFn), nodes);
    };

    /**
     * 选择备机节点
     */
    const checkOraclesStandbyNode = (ev, treeId, treeNode) =>{
        $('.dbMapDetaildiv').show();
        $('.synNodediv').show();
        $('.standByTips').hide();
        targettreeNode = treeNode;

        // 单选节点
        let allNodes = zTreetTargetAgent.getCheckedNodes(true);
        for(let i = 0; i < allNodes.length; i++){
            zTreetTargetAgent.checkNode(allNodes[i], false, false, false);
        }
        zTreetTargetAgent.checkNode(treeNode, true, false, false);
        targetAgentList = [];
        if (treeNode.eventtype === 'cluster') {
            // 显示集群
            data.recovery_object.target_cluster_flag = true;
            addInstanceMapList(treeNode);
            initInstanceUserTree(sourcetreeNode,true);
            initInstanceUserTree(treeNode,false);
            targetAgentList.push(treeNode.cluster_uuid);
            // 遍历集群下的单节点
            for(let node of treeNode.children){
                if(node.online_flag){
                    // 在线单节点
                    // data.recovery_object.target_app_uuid = node.app_uuid;
                    data.recovery_object.target_agent_uuid = node.agent_uuid;
                    data.recovery_object.target_cluster_flag = true;
                    targetAgentAppInfo.app_type = node.app_type;
                    targetAgentAppInfo.app_version = node.app_version;
                    break;
                }
            }
        } else if (treeNode.eventtype === 'instance' && treeNode.pId ==='standalone') {
            // 消息结构
            addInstanceMapList(treeNode);
            initInstanceUserTree(sourcetreeNode,true);
            initInstanceUserTree(treeNode,false);
            // data.recovery_object.target_app_uuid = treeNode.app_uuid;
            data.recovery_object.target_agent_uuid = treeNode.agent_uuid;
            data.recovery_object.target_cluster_flag = false;
            targetAgentAppInfo.app_type = treeNode.app_type;
            targetAgentAppInfo.app_version = treeNode.app_version;
            targetAgentList.push(treeNode.app_uuid);
        }
        addDataSynNode(treeNode);
        contrastApp(sourceAgentAppInfo, targetAgentAppInfo);
    }

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
                `<option value="${sourcetreeNode.agent_uuid}">${sourcetreeNode.title}</option>`
            );
        }else{
            for(const node of sourcetreeNode.children){
                hostNodeEle.append(
                    `<option value="${node.agent_uuid}">${node.name}</option>`
                );
            }
        }

        // 目标机
        if(!targettreeNode.cluster_flag){
            slaveNodeEle.append(
                `<option value="${targettreeNode.agent_uuid}" data-app="${targettreeNode.app_uuid}" data-cluster="${targettreeNode.cluster_uuid}">${targettreeNode.title}</option>`
            );
        }else{
            for(const node of targettreeNode.children){
                slaveNodeEle.append(
                    `<option value="${node.agent_uuid}" data-app="${node.app_uuid}" data-cluster="${node.cluster_uuid}">${node.name}</option>`
                );
            }
        }
    }


    /**
     * 将Oracle的备份节点添加到右侧中
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
                '<li>'+ LANG.UI_DB_CDP_BACKUP_SOURCE_DB + ':</li>' +
                '</ul>' +
                '<ul id="dbtargetAgentTree_' + selectedBackupKey + '" class="ztree col-md-6" style="overflow: hidden">' +
                '<li>'+ LANG.UI_DB_TARGET_DATABASE + ':</li>' +
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
     * 初始化已选择的备份树
     * @param {*} selectedTreeNode
     */
    const initInstanceUserTree = (selectedTreeNode,flag) => {
        let nodes = [];
        let selectedBackupKey;
        if (selectedTreeNode.eventtype === 'cluster') {
            let selectedChildren = selectedTreeNode.children;
            let agentUuid = '';
            let appUuid = '';
            let appType = '';
            let appVersion = '';
            let instanceName = '';
            // 是否存在备份任务
            let dbcdpBackupInfo = [];
            for (const selectedChild of selectedChildren) {
                if (selectedChild.online_flag && selectedChild.auth_flag) {
                    agentUuid = selectedChild.agent_uuid;
                    appUuid = selectedChild.app_uuid;
                    appType = selectedChild.app_type;
                    appVersion = selectedChild.app_version;
                    instanceName = selectedChild.instance_name;
                    dbcdpBackupInfo = selectedChild.db_cdp_recover_info;
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
        if(flag){
            sourceInstanceUserTree[selectedBackupKey] = $.fn.zTree.init($('#dbsourceAgentTree_' + selectedBackupKey), getInstanceUserTreeSetting(), nodes);
            // 默认展开
            let allNodes = sourceInstanceUserTree[selectedBackupKey].getNodes();
            sourceInstanceUserTree[selectedBackupKey].expandNode(allNodes[0], true, false, true, true);
            // 全选所有节点
            sourceInstanceUserTree[selectedBackupKey].checkAllNodes(true);
        }else {
            targetInstanceUserTree[selectedBackupKey] = $.fn.zTree.init($('#dbtargetAgentTree_' + selectedBackupKey), getInstanceUserTreeSetting(), nodes);
            // 默认展开
            let allNodes = targetInstanceUserTree[selectedBackupKey].getNodes();
            targetInstanceUserTree[selectedBackupKey].expandNode(allNodes[0], true, false, true, true);
            // 全选所有节点
            targetInstanceUserTree[selectedBackupKey].checkAllNodes(true);
        }

    };

    /**
     * 获取Oracle备份树的设置
     */
    const getInstanceUserTreeSetting = () => {
        return {
            check: {
                enable: true,           // 启用复选框
                chkStyle: "checkbox",   // 使用多选框样式
                chkboxType: {
                    "Y": "ps",          // 勾选时联动父节点和子节点
                    "N": "s"            // 取消勾选时只联动子节点
                },
                autoCheckTrigger: true,  // 自动触发勾选事件
                nocheckInherit: true,    // 子节点继承父节点的nocheck属性
                chkDisabledInherit: true // 子节点继承父节点的禁用状态
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
                beforeExpand: expandInstanceUserTreeNode,
                beforeCheck: function(treeId, treeNode) {
                    // 只拦截取消勾选操作
                    if (treeNode.checked) {
                        var zTree = $.fn.zTree.getZTreeObj(treeId);
                        var checkedCount = 0;

                        // 递归统计所有子节点的勾选数量
                        function countCheckedNodes(nodes) {
                            for (var i = 0; i < nodes.length; i++) {
                                var node = nodes[i];
                                // 如果是子节点（或叶子节点）且被勾选，则计数
                                if (!node.children || node.children.length === 0) {
                                    if (node.checked) checkedCount++;
                                }
                                // 递归检查子级
                                if (node.children) {
                                    countCheckedNodes(node.children);
                                }
                            }
                        }

                        // 从根节点开始统计
                        countCheckedNodes(zTree.getNodes());

                        // 如果当前节点是最后一个被勾选的子节点，阻止取消
                        if (checkedCount === 1 && treeNode.checked) {
                            return false;
                        }
                    }
                    return true; // 允许其他操作
                }
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
        let isNoCheck = treeId !== `dbsourceAgentTree_${selectedBackupKey}`;
        let select_copy_users_flag = false;
        if(treeId == 'dbsourceAgentTree_'+selectedBackupKey){
            // 源
            select_copy_users_flag = true;
        }
        // 加载实例用户信息
        Metronic.blockUI({target: '#dbcdprecovercontent', animate: true, cenrerY: true,});
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
            "task_uuid":"",
            "select_copy_users_flag":select_copy_users_flag
        }
        pAjaxRequest(param, `/api/v1/dbcdp/databases`, 'POST', res => {
            Metronic.unblockUI(`#dbcdprecovercontent`);
            if (!res.success) {
                return;
            }
            let nodes = [];
            let users = [];
            default_target_file_cache_path = res.data.agent_install_dir;
            $('#backup_fileCachePath').val(default_target_file_cache_path);
            $('#backup_fileCachePath').attr('title',default_target_file_cache_path);
            let rows = res.data.rows;
            let select_copy_users = res.data.select_copy_users;
            if(select_copy_users_flag){
                select_copy_users = select_copy_users_value;
            }
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
                            nocheck: isNoCheck,
                            checked:true,
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
                            nocheck: isNoCheck,
                            checked:true,
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
                        nocheck: isNoCheck,
                        checked:true,
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
     * 比较源机和目标机的应用
     * 应用类型相同 源机应用版本需大于等于主机
     */

    const contrastApp =  (sourceApp, targetApp) => {
        // 源、目标机类型需相同
        // 目标机版本应大于等于源机

        // 选择同步任务的源机时不用校验应用
        if(syncSourceNodeFlag){
            appValidFlag = true;
            return true;
        }

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
     * 初始化数据类型
     * 1.如果恢复源是原机，恢复类型为全量数据+增量数据、增量数据、自适应
     * 2.如果恢复源是异机，恢复类型为全量+增量
     * 暂时屏蔽增量
     */
    const initDataType =  () => {
        $('.dataType').empty();
        //如果选择的目标客户端是源机，数据类型为全量数据+增量数据、增量
        //如果选择的目标客户端是异机，数据类型为全量+增量
        // if (syncSourceNodeFlag) {
        //     //源机
        //     var option = '<option value="1">' + LANG.UI_DB_CDP_RECOVER_DATA_TYPE3 + '</option>' +
        //         '<option value="2">' + LANG.UI_DB_CDP_RECOVER_DATA_TYPE2 + '</option>';
        //     $('.dataType').append(option);
        // } else {
        //     var option = '<option value="1">' + LANG.UI_DB_CDP_RECOVER_DATA_TYPE3 + '</option>';
        //     $('.dataType').append(option);
        // }

        var option = '<option value="1">' + LANG.UI_DB_CDP_RECOVER_DATA_TYPE3 + '</option>';
        $('.dataType').append(option);

    }


    /**
     * 管理节点-初始化节点下拉框
     */
    const initBackupTarget = () => {
        $('#backupTarget').backupTarget({
            hide_backup_storage_flag: true,  // 是否隐藏备份存储
            hide_tips_flag: true,            // 是否隐藏提示
        });
    }

    /**
     * 第二步有效
     * @return {boolean}
     */
    var step2Valid =  () => {
        // 验证备机
        if(data.recovery_object.target_agent_uuid == ''){
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_RECOVER, LANG.UI_VOL_CDP_BACKUP_STANDBY_MACHINE_SELECT);
            return false;
        }
        // 目标
        let target_agent_uuid =  $('#slaveNode option:selected').val();
        let target_app_uuid =  $('#slaveNode option:selected').attr('data-app');
        let target_cluster_uuid =  $('#slaveNode option:selected').attr('data-cluster');
        if(target_agent_uuid == syncSourceAgentUuid || (syncSourceClusterUuid != '' && syncSourceClusterUuid == target_cluster_uuid)){
            syncSourceNodeFlag = true;
        }

        data.recovery_object.target_agent_uuid = target_agent_uuid;
        data.recovery_object.target_app_uuid = target_app_uuid;

        // 验证应用有效
        if(!appValidFlag){
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_RECOVER,LANG.UI_DB_CDP_BACKUP_APP_SELECT_TIPS);
            return false;
        }

        // 数据表空间存储目录
        let tablespaceDirSelectValue = $('#tablespaceDir option:selected').val();
        if(tablespaceDirSelectValue == '1'){
            data.app_tablespace_dir = $('#customDirectory').val();
            if(data.app_tablespace_dir == ''){
                UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_SYNC,LANG.UI_DB_CDP_DETAILS_STORAGE_DIR_TIPS);
                return false;
            }
            if(data.app_tablespace_dir != '' && !checkFilePath(data.app_tablespace_dir,'Linux')){
                UIToastr.showWarning(LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG, LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG_TIPS);
                return false;
            }
        }else{
            data.app_tablespace_dir = '';
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
        // 获取默认选中的最底层节点
        var bottomNodes = getCheckedBottomNodes("dbsourceAgentTree_"+selectedBackupUuid);
        data.select_copy_users = [];
        initNetworkList();
        const mergedUsers = bottomNodes.reduce((result, user) => {
            // 如果 user.type 为 'dpb' 则跳过本次处理
            if (user.eventtype === 'pdb' && user.children.length == 0) {
                return result;
            }
            const existing = result.find(item => item.con_id === user.con_id);
            if (existing) {
                existing.user_list.push({
                    name: user.name,
                });
            } else {
                result.push({
                    con_id: user.con_id,
                    pdb_name: user.pdb_name,
                    user_list: [{
                        name: user.name,
                    }]
                });
            }
            return result;
        }, []);
        data.select_copy_users = mergedUsers;
        initDataType();
        initStep4Tree(agentList,$('#dbcdpShowSourceTree'),sourceInstanceUserTree);
        initStep4Tree(targetAgentList,$('#dbcdpShowTargetTree'),targetInstanceUserTree);
        return true;
    }

    function getCheckedBottomNodes(treeId) {
        var treeObj = $.fn.zTree.getZTreeObj(treeId);
        var allNodes = treeObj.transformToArray(treeObj.getNodes());
        var bottomNodes = [];

        for (var i = 0; i < allNodes.length; i++) {
            if (allNodes[i].checked) {  // 检查节点的 checked 属性
                var isBottom = true;
                if (allNodes[i].children && allNodes[i].children.length > 0) {
                    // 检查是否有被选中的子节点
                    for (var j = 0; j < allNodes[i].children.length; j++) {
                        if (allNodes[i].children[j].checked) {
                            isBottom = false;
                            break;
                        }
                    }
                }
                if (isBottom) {
                    bottomNodes.push(allNodes[i]);
                }
            }
        }
        return bottomNodes;
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

    //step3-传输网络
    var initNetworkList = function () {
        if(networkFlag && data.node_uuid!=""){   //初始化传输网络
            $('.transfernetworkDiv').show();
            $('#transferNetworkTree').transferNetwork({node_uuid: data.node_uuid}); 	//初始化节点传输网络列表

        }else{
            $('.transfernetworkDiv').hide();
        }
    }

    const checkTime = function (start, end) {
        var startnum = new Date("1970-01-01" + " " + start).getTime();
        var endnum = new Date("1970-01-01" + " " + end).getTime();
        if (endnum <= startnum && $("#speedModeType").val() == 1) {//按策略限速才判断
            UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
            return false;
        } else {
            return true;
        }
    }

    //————————————————————————————————————————————————————————————————————————————————————————————————限速策略函数end


    /**
     * step3有效
     * @return {boolean}
     */
    var step3Valid = function () {
        // 加密传输是否打开
        data.transport_strategy.transport_encrypt_flag = $('#encrypttransfer').get(0).checked;
        // 加密传输方式
        if(data.transport_strategy.transport_encrypt_flag){
            data.transport_strategy.transport_encrypt_method = $('#encrypttransfermethodselect option:selected').val();
        }
        // 压缩传输是否打开
        data.transport_strategy.transport_compress_flag = $('#compresstransfer').get(0).checked;
        // 压缩等级
        if(data.transport_strategy.transport_compress_flag){
            data.transport_strategy.transport_compress_method = $('#compresstransferselect option:selected').val();
        }
        // 线程个数
        data.exp_max_process_num = parseInt($('#exp_thread').val());
        data.imp_max_process_num = parseInt($('#imp_thread').val());

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

        //传输限速
        data.speed_limit_strategy_list = getSpeedStrategyInfo();

        // 传输网络
        if(networkFlag && data.node_pool_uuid == ''){
            data.transport_strategy.network_uuid = $('#transferNetworkTree').transferNetwork('getSelect').network_uuid;
        }

        // 选择了自定义路径但是没填写
        if(!filePathDefault && data.cache_config.file_cache_storage_path == ''){
            UIToastr.showWarning(LANG.UI_VISUAL_DBCDP_RECOVER, '自定义路径不能为空');
            return false;
        }

        // 数据类型
        data.recovery_object.recovery_type = $('.dataType option:selected').val();

        // dbms接口等高级配置
        data.extend_advanced_config.use_dbms = $('#use_dbms_Switch').get(0).checked;
        data.extend_advanced_config.timed_gen_txn = $('#timed_gen_txn_Switch').get(0).checked;
        data.extend_advanced_config.sync_big_object = $('#sync_big_object_Switch').get(0).checked;
        data.extend_advanced_config.replay_ddl = $('#replay_ddl_Switch').get(0).checked;
        data.extend_advanced_config.ignore_nonsupport_data_type = $('#ignore_nonsupport_data_type_Switch').get(0).checked;

        // 预加载STEP4
        preStep4();

        return true;
    }

    /**
     * 预加载step4
     */
    const preStep4 = () => {
        // 恢复数据源
        let dataSourceStr = sourcetreeNode.name + '/' + sourcetreeNode.instance_name + '<br>' + LANG.UI_DB_CDP_RECOVER_ORACLE_RECOVER;
        $('.recoveryDataSourceShow').html(dataSourceStr);

        // 恢复时间点
        let recoveryTimePointStr = data.recovery_target_datetime !== '' ? data.recovery_target_datetime : LANG.UI_DB_CDP_DETAILS_TIME_METHOD_LATEST_TIMEPOINT;
        $('.recoveryTimePointShow').html(recoveryTimePointStr);

        // 恢复应用
        let recoveryAppStr = sourcetreeNode.app_name;
        $('.recoveryAppShow').html(recoveryAppStr);

        // 恢复目标主机
        let recoveryTargetStr = targettreeNode.name;
        $('.recoveryTargetShow').html(recoveryTargetStr);

        // 数据表空间存储目录
        let tablespaceDirSelectValue = $('#tablespaceDir option:selected').val();
        let customDirectoryVal = $('#customDirectory').val();
        if(tablespaceDirSelectValue == '1'){
            // 自定义
            $('.tablespaceDirShow').text(customDirectoryVal);
        }else{
            $('.tablespaceDirShow').text(LANG.UI_DB_CDP_DETAILS_DIRECTORY_OF_THE_SYSTEM_TABLE_SPACE);
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
        let encrypttransferStr = $('#encrypttransfer').get(0).checked ? LANG.UI_PUBLIC_ON : LANG.UI_PUBLIC_OFF;
        // 加密传输方式
        let encrypttransferMethodStr = $('#encrypttransfermethodselect option:selected').text();
        // 压缩传输
        let compresstransferStr = $('#compresstransfer').get(0).checked ? LANG.UI_PUBLIC_ON : LANG.UI_PUBLIC_OFF;
        // 压缩等级
        let compresstransferMethodStr = $('#compresstransferselect option:selected').text();

        // 传输限速
        let limitSpeed = getSpeedStrategyInfo().speedInfo;
        let transferSpeedLimitStr = '';
        if(limitSpeed.length == 0){
            transferSpeedLimitStr = LANG.UI_PUBLIC_NOTHING;
        }else {
            for (let i = 0; i < limitSpeed.length; i++) {
                transferSpeedLimitStr += getSpeedStrategyInfo().speedInfo[i].des + ';';
            }
        }

        transferStrategyStr += LANG.UI_COPY_BACK_ENCRYPT + ':' + encrypttransferStr + '<br>';
        if($('#encrypttransfer').get(0).checked){
            transferStrategyStr += LANG.UI_DB_CDP_BACKUP_ENCRYPT_METHOD + ':' + encrypttransferMethodStr + '<br>';
        }
        transferStrategyStr += LANG.UI_FILE_COPY_TRANSFER_COMPRESS + ':' + compresstransferStr + '<br>';
        if($('#compresstransfer').get(0).checked){
            transferStrategyStr += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ':' + compresstransferMethodStr + '<br>';
        }
        // 导出线程
        let exp_threadStr = $('#exp_thread').val();
        // 导入线程
        let imp_threadStr = $('#imp_thread').val();
        transferStrategyStr += LANG.UI_DB_CDP_EXP_THREADS + ':' + exp_threadStr + '<br>';
        transferStrategyStr += LANG.UI_DB_CDP_IMP_THREADS + ':' + imp_threadStr + '<br>';
        transferStrategyStr += LANG.UI_DB_CDP_BACKUP_RATE_LIMITED_TRANSMISSION + ':' + transferSpeedLimitStr + '<br>';
        $('.transferStrategyShow').html(transferStrategyStr);

        // 高级策略
        let highStrategyStr = '';
        // 源机文件缓存大小
        let sourcefileCacheSizeStr = sourceToggleLimitModeFlag ? LANG.UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE : $('#source_fileCacheSize').val() + $('select[name=source_file_cache_unit] option:selected').text();
        // 备机文件缓存大小
        let backupfileCacheSizeStr = backupToggleLimitModeFlag ? LANG.UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE : $('#backup_fileCacheSize').val() + $('select[name=backup_file_cache_unit] option:selected').text();

        // 备机文件缓存路径
        let backupfileCachePathStr = $('#backup_fileCachePath').val();
        // 数据类型
        let dataTypeStr = $('.dataType option:selected').text();
        highStrategyStr += LANG.UI_DB_CDP_BACKUP_SOURCE_MACHINE_FILE_CACHE_SIZE + ':' + sourcefileCacheSizeStr + '<br>';
        highStrategyStr += LANG.UI_DB_CDP_RECOVER_BACKUP_CACHE_SIZE + ':' + backupfileCacheSizeStr + '<br>';
        highStrategyStr += LANG.UI_DB_CDP_RECOVER_CACHE_PATH + ':' + backupfileCachePathStr + '<br>';
        highStrategyStr += LANG.UI_JOB_DATA_TYPE + ':' + dataTypeStr + '<br>';
        highStrategyStr += $('#use_dbms_Switch').get(0).checked ? LANG.UI_DB_CDP_USE_DBMS + ':' + LANG.UI_PUBLIC_ON: LANG.UI_DB_CDP_USE_DBMS + ':' + LANG.UI_PUBLIC_OFF;
        highStrategyStr += '<br>';
        highStrategyStr += $('#sync_big_object_Switch').get(0).checked ? LANG.UI_DB_CDP_SYNC_BIG_OBJ + ':' + LANG.UI_PUBLIC_ON: LANG.UI_DB_CDP_SYNC_BIG_OBJ + ':' + LANG.UI_PUBLIC_OFF;
        highStrategyStr += '<br>';
        highStrategyStr += $('#replay_ddl_Switch').get(0).checked ? LANG.UI_DB_CDP_REPLAY_DDL + ':' + LANG.UI_PUBLIC_ON: LANG.UI_DB_CDP_REPLAY_DDL + ':' + LANG.UI_PUBLIC_OFF;
        highStrategyStr += '<br>';
        highStrategyStr += $('#ignore_nonsupport_data_type_Switch').get(0).checked ? LANG.UI_DB_CDP_IGNORE_NONSUPPORT_DATA_TYPE + ':' + LANG.UI_PUBLIC_ON : LANG.UI_DB_CDP_IGNORE_NONSUPPORT_DATA_TYPE + ':' + LANG.UI_PUBLIC_OFF;
        $('.highLevelStrategyShow').html(highStrategyStr);
    }


    /**
     * 初始化spinner
     */
    var initSpinner = function () {
        $('.numberFailuresDiv ').spinner({value: 1, step: 1, min: 1, max: 10000000000});
        $('.faultMonitorIntervalDiv').spinner({value: 30, step: 1, min: 5, max: 3600});
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
        $('#delayedLoadTimeDiv').spinner({value: 1, step: 1, min: 1, max: 10000000000});
    }

    const watchEchartSizeChange = function() {
        window.onresize = function() {
            var portletBodyWidth = $('#anyPointTime').width();
            var portletBodyHeight = $('#anyPointTime').height();
            $('#RecoveryTimeline').css({"width": portletBodyWidth + 'px', "height": (portletBodyHeight - 34) + 'px'});
            myChart.resize();
        }
    }

    /**
     * 获取任务名
     */
    const getTaskName = () => {
        // 默认任务名
        let param = {'job_name': LANG.UI_DB_CDP_RECOVER_TASK};
        pAjaxRequest(param, '/api/v1/jobs/name', 'GET', function (d) {
            var data = d.data;
            $('#jobname').val(data.value);
        });
    }

    /**
     * submit
     */
    const submit = function () {
        if ($.trim($("#jobname").val()) == '') {
            $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
            return;
        }
        $('.jobnametip').hide();
        data.job_name = $.trim($("#jobname").val());
        Metronic.blockUI({target: '#dbcdprecovercontent', animate: true, cenrerY: true,});
        pAjaxRequest(data, '/api/v1/dbcdp/jobs/restore', 'POST',  (d) => {
            Metronic.unblockUI('#dbcdprecovercontent');
            if (operateResponseList(d)) {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
        });
    }


    return {
        init: function () {
            wizardInit();
            initDbTypeSelect();
            initAgentTree();
            initListeners();
            initSpinner();
            initBackupTarget();	  //初始化管理节点选择
            watchEchartSizeChange();
            getTaskName();
        }
    }
}();

jQuery(document).ready(function () {
    Dbcdprecover.init();
});