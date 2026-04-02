var DbCdpJobDetails = function () {

    let taskUuid = '';
    let flowChart;
    let takeoverChart;
    // 任务曲线图初始化标志
    let initChartFlag = false;
    // 节点信息
    let nodeInfo;
    // 服务IP漂移开关(接管)
    let switchIpFlag = false;
    let manual_switchIpFlag = false;
    // 任务类型
    let taskType;
    // 任务阶段
    let taskStage;
    // 任务状态
    let taskStatus;
    // 任务名
    let taskName = '';


    let encryptMethod = {
        'RSA':1,
        'SM2':2,
    };

    let compressMethod = {
        [LANG.UI_DB_CDP_COMPRESS_METHOD1]:1,
        [LANG.UI_DB_CDP_COMPRESS_METHOD2]:2,
        [LANG.UI_DB_CDP_COMPRESS_METHOD3]:3,
        [LANG.UI_DB_CDP_COMPRESS_METHOD4]:4
    };

    // 网卡是否配置
    let ipMapFlag = false;
    // 延迟加载时间
    let delayLoadTime = '';
    let delay_load_time_value = '';
    // 是否配置了延迟时间
    var delayTimeFlag = false;
    // 在运行状态新加操作 允许回切
    let allowFailbackFlag = false;
    //
    let isfailbackConfigFlag = false;

    let istakoverConfigFlag = false;

    // 手动接管标志
    let manualTakeoverFlag = false;
    let ipMaplist = {};

    // 手动接管
    let manualTakeoverNetConfigFlag = false;

    // 用户密码
    let _UserPassword; // 用户的用户密码

    // 验证密码正确性
    let userIsVerify = false;

    //回切配置信息
    var failback_object = {
        'target_host_uuid': '',
        'target_host_cluster_uuid':'',
        'target_cluster_flag': false,
        'target_app_uuid': '',
        'source_app_type': '',
        'target_app_type': '',
        'source_app_version': '',
        'target_app_version': '',
        'source_host_uuid': '',
        'source_host_cluster_uuid':'',
        'source_app_uuid': '',
        'source_cluster_flag': false,
        'target_cluster_ip': '',
        'source_cluster_ip': '',
        'failback_mode': 0,
        'target_ip': '',  //回切目标机为单机时
        'target_uuid': '',
        'target_hostname': '',
    };

    let taskStageObj = {
        // 任务阶段
        // 回切
        'taskStageFailback':[40,41,50,51,52,53],
        'taskStageFailbackRunning':[1,40,41,50,51,52,54],
        'taskStageFailbackSuccessed':[53],
    }

    // 数据流向缓存
    let dataCache = null;

    // 同步源信息
    let backupInfo = {
        'source_app_uuid':'',
        'source_agent_uuid':'',
        'source_agent_cluster_flag':false,
        'target_agent_uuid':'',
        'target_agent_cluster_flag':false,
        'source_agent_online_flag':0,  //源心跳
        'target_agent_online_flag':0,  //目标心跳
        'agent_heartbeat_failure_time':0,
        'source_agent_cluster_uuid':'',
        'target_agent_cluster_uuid':'',
    }

    // 同步信息
    let backupTargetInfo = {
        'source_agent_app_uuid':'',
        'source_agent_uuid':'',
        'source_agent_cluster_uuid':'',
        'source_agent_cluster_flag':false,
        'target_agent_app_uuid':'',
        'target_agent_uuid':'',
        'target_agent_cluster_flag':false,
        'target_agent_cluster_uuid':'',
    }

    let dataFlowObj = {
        //任务状态
        'taskStatusNormal': [13],  //正常状态
        'taskStatusRunning': [2],  //运行状态
        'taskStatusPause': [3, 11],  //暂停状态
        'taskStatusWaitSuccess': [1, 12, 17], //等待/成功状态
        'taskStatusOffline': [4, 5],  //离线状态/停止/停止中
        'taskStatusError': [6, 7, 8],  //错误状态
        'taskStatusAbnormal': [11],  //异常状态
    }
    let takeover_ip_scope_info = '';
    let failback_ip_scope_info = '';

    //-----------------------------------手动接管-----------------------------------------
    // 重放时间时间戳
    let _lastReplayTimestamp;
    // 最新接收事务时间戳
    let _lastRecvTransactionTimestamp;
    // 最新时间 date
    let newTime = '';
    // 时间范围标志
    let time_unit;
    // dataZoom的起始百分比,此处暂定70%,根据实际效果调整.(可以根据加载的时间区间范围确定起始百分比)
    let _chartStartPercent = 70;
    let _chartEndPercent = 100;
    let _hisPortletBodyWidth = 0;
    let _hisportletBodyHeight =0;
    // 时间点类型，0：无效时间点，1：任意时间点；2：事件点；
    let _timepointType = 1;
    // 选中的时间点
    let _checkTime = '';
    // 事务点:2
    let _timepointEventType;
    // 接管事件点时是否有效flag
    let _checkTimeValid = false;
    // 时间轴默认加载的间隔
    let _timeInterval = 1;
    // 回切源机对应的网卡信息
    let agentNetworkInfo = [];
    // 回切目标机对应的网卡信息
    let standbyNetworkInfo = [];
    // 区分是接管还是回切选择网卡信息
    let triggeredElementId = '';
    //最新接收事务时间点
    let _lastRecvTransactionTimePoint = '';

    let takeoverFlag= false;
    let initGridFlag = false;
    let initTransactionGridFlag = false;
    let initTransactionDtailsGridFlag = false;
    let failbackTimeRangeFlag = false;  // 回切时间范围默认是时间段
    //手动接管参数
    var taskOverTaskParams = {
        'job_uuids':[],
        'takeover_timestamp':'',
        'failback_target_ip':'',
        'switch_ip_flag':false,
        'takeover_business_ip_map':[],
        'takeover_scn':'',
    };
    // 备机网关设置
    let standbyGatewayConf = [];
    let _takeoverIpMapList =[];
    let _takeoverIpMapListView = [];
    // 映射ip列表
    let ipMapInfoList = [];
    let data = {
        'takeover_object': {
            // 是否启用服务IP漂移
            'switch_ip_flag': false,
            'takeover_business_ip_map': [],
        }
    }

    let last_redo_replay_scn = '';
    let latest_recv_transaction_scn = '';
    let timepoint_type = '';
    let confirmTakeoverFlag = false;

    //-----------------------------------启动回切-----------------------------------------
    // 提交回切配置
    let isInitFailbck = false;
    // 可选择的回切目标
    let failback_target_object = [];
    // 源机的应用uuid/集群uuid
    let selectedBackupUuid = '';
    // 源映射树
    let sourceInstanceUserTree = [];
    // 目标映射树
    let targetInstanceUserTree = [];
    // 源机的应用信息
    let sourceAgentAppInfo = {};
    // 目标机的客户端信息
    let targetAgentAppInfo = {};
    // 回切策略选择时间段
    let failBackStrategyTimeFlag = false;
    // 选择同步任务的源节点标志
    let syncSourceNodeFlag = false;
    let sourcetreeNode = {};
    // 是否配置回切
    let failbackConfigFlag = false;
    // 是否开启自动接管
    let autoTakeoverFlag = false;
    // 是否禁用自动接管
    let allow_auto_takeover_flag = false;
    // 回切通讯IP显示标志
    let isIpShow = false;
    // 回切通讯IP测试
    let ipCheckFlag = false;
    let _failbackIpVerify = false;
    //
    let takeoverNetConfFlag = false;
    //
    let allIpMapInfoList = [];

    let failbackFlag= false;

    let targettreeNode = {};
    let select_copy_users_value = [];
    let sync_direction = '';
    let default_target_file_cache_path = '';
    let $dateInput;
    // 回切参数
    var failbackConfigParams = {
        'task_uuid':'',
        'transport_strategy': {
            'transport_encrypt_flag':0, // 0:unknown 1:set 2:unset
            'transport_compress_flag':0, // 0:unknown 1:set 2:unset
            'transport_block_size':0,
            'transport_compress_method':0,
            'transport_encrypt_method':0
        },
        'exp_max_process_num':'',
        'imp_max_process_num':'',

        'failback_object':{
            'source_host_uuid':'', 	//source_agent_uuid
            'target_host_uuid':'',	//target_agent_uuid
            'source_app_uuid':'',
            'target_app_uuid':'',
            'source_cluster_flag':0, // 0:unknown 1:set 2:unset
            'target_cluster_flag':0, // 0:unknown 1:set 2:unset
            'failback_mode':0, // 1:full+log	2:log	3:self-adaptive
            'failback_business_ip_map':[
            ]
        },

        'cache_config':{
            'source_file_cache_alloc_space':0,
            'file_cache_alloc_space':0,
            'file_cache_storage_path':'',
        },

        'service_failback_config': {
            'service_failback_mode':3, 	//1:自动 3:手动
            'service_failback_start_time':'',
            'service_failback_end_time':'',
            'service_failback_auto_trigger_threshold':''
        },

        'extend_advanced_config':{
            'use_dbms':false,
            'timed_gen_txn':true,
            'sync_big_object':true,
            'replay_ddl':true,
            'ignore_nonsupport_data_type': true,
        },

        'retry_strategy':{
            'reconnect_times':0,
            'reconnect_interval':0
        }
    }



    /**
     * 点击操作按钮
     */
    const initListeners = function () {
        // 启动同步
        $('.startSyn').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },startJob)
        });

        // 删除同步 不需二次提醒
        $('.deleteSyn').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },deleteJob)
        });

        // 删除恢复任务
        $('.deleteRecovery').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },deleteJob)
        });

        // 启动接管
        $('.startTakeover').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },startTakeover)
        });

        // 暂停任务
        $('.pauseSyn').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },pauseSynJob)
        });

        // 停止同步
        $('.stopSyn').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },stopJob)
        });

        // 停止恢复
        $('.stopRecovery').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },stopJob)
        });

        // 停止接管
        $('.stopTakeover').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },stopTakeoverJob)
        });

        // 强制停止
        $('.forcedStop').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },stopJob)
        });

        // 启动恢复
        $('.startRecovery').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },startJob)
        });

        // 启动回切
        $('.startFailBack').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },startFallBackJob)
        });


        // 修改同步
        $('.modifySyn').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },editJob)
        });

        // 禁用自动接管
        $('.disableSrEnable').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },autoTakeoverEnableAndDisableConf)
        });

        // 暂停状态修改延时重放时间和文件缓存大小
        $('.editHighConfig').off().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },editHighConfig)
        });

        // 提交高级配置
        $('#submit_high_config').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },submitHighConfig)
        });

        // 接管时间类型
        $('.takeoverTimeType').on('click',changeTakeoverTimeType);
        // 加密传输
        $('#failbackTranEncryptSwitch').on('switchChange.bootstrapSwitch', encryptTransfer)
        // 压缩传输
        $('#failbackTranCompressSwitch').on('switchChange.bootstrapSwitch', compressTransfer)
        // 回切策略
        $('#failBackStrategy_select').on('change',changeFailBackStrategy);
        // 提交回切配置
        $('#failBackConfigSubmit').unbind().on('click', function () {
            checkOperateAuth({
                type: 1,
                user_uuid: task_user_uuid,
                auth: 'current_job'
            },failBackConfigSubmit)
        });

        // 回切网卡配置
        $('#faiback_network_conf').off().on('click',getFailbackNet);
        // 查看/修改回切配置
        $('#taskSwitchbackConf').unbind('click').on('click',taskSwitchbackConf);
        // 点击li事件信息  获取所有的事件信息
        $('#timepointType li').on('click', getTabInfo);
        // 数据表空间存储目录
        $('#tablespaceDir').on('change',changeTableSpaceDirSelect);
        // 文件缓存路径
        fileCachePathChange();
        // 接管时间点
        $('#inputTakeoverTimepoint').blur(function () {checkdate()});
        // 修改时间控件
        $('#inputTakeoverTimepoint').on('change', changeRecoverTime);
        // 接管回切通信IP
        $('#linkSelectIP').unbind('click').click(checkTakeoverResIp);
        // 回切通讯ip
        $('#failbackTargetIp').off().blur(failbackTargetIpChange);
        // 导入/导出详情
        $('#import_export_details').off().on('click',getImportExportDetails);
        // 事务详情
        $('#transactions_details').off().on('click',getTransactionsDetails);
        $('.source_fileCacheSizeDiv').spinner({value: 4, step: 1, min: 4, max: 1024});
        $('.backup_fileCacheSizeDiv').spinner({value: 64, step: 1, min: 4,max: 1024});
        // 文件缓存大小
        $('.unlimit_limit_toggle').off().on('click',toggleLimitMode);
        // 关闭抽屉
        $('#takeover_conf_drawer .cancel').on('click', function () {
            $('#takeover_conf_drawer').drawer('hide');
        });
        // 切换时间范围
        $('.any_time_toggle').off().on('click',toggleTimeRange);
    }

    /**
     * 校验输入时间格式正确性
     */
    var checkdate = function () {
        var reg = /^[1-9]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+(20|21|22|23|[0-1]\d):[0-5]\d:[0-5]\d$/
        var str1 = $('#inputTakeoverTimepoint').val();
        if (!reg.test(str1)) {
            $('#inputTakeoverTimepoint').val('');
            _timepointType = 0;
            $('#inputTakeoverTimepoint').css('color', "#F3565D");
            $('#inputTakeoverTimepoint').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
        }
    }

    /**
     * 修改时间控件
     */
    const changeRecoverTime = () => {
        var restoreTimepoint = $('#inputTakeoverTimepoint').val();
        verifyTimepointisValid(restoreTimepoint);
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
     * 回切通讯IP失去焦点时
     * @return {boolean}
     */
    const failbackTargetIpChange = () => {
        ipCheckFlag = false;
        let ip = $('#failbackTargetIp').val();
        if (!ipV4V6(ip) && ip.length > 0 || ip == '') {
            UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP_MESSAGE);
            return false;
        }
    }

    /**
     * 导入/导出详情
     */
    const getImportExportDetails = () => {
        let options = {
            vin_url: '/api/v1/dbcdp/import/export_details',
            vin_method: 'GET',
            vin_params: function () {
                var params = {};
                params.task_uuid = $("#task_uuid").val();
                params.sync_direction = sync_direction;
                return params;
            },
            onResetView: initTableHeight,
            columns: [ //列定义
                {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'combined_field',
                    title: LANG.UI_DB_CDP_RECOVER_TABLE_NAME
                },
                {
                    field: 'status_type_export',
                    title: LANG.UI_DB_CDP_EXPORT_STATUS,
                    formatter:function(value,row){
                        let str = '';
                        if(row.status_type_export == CONF.FLAG.SET){
                            str = `<span class="label label-success">`+LANG.UI_PUBLIC_SUCCESS+`</span>`;
                        }else if(row.status_type_export == CONF.FLAG.UNSET){
                            str = `<span class="label label-danger">`+LANG.UI_DATACENTER_FAILURE+`</span>`;
                        }else{
                            str = `<span class="label label-warning">`+LANG.UI_CLOUD_PLATFORM_UNKNOWN+`</span>`;
                        }
                        return str;
                    }
                },
                {
                    field: 'status_type_import',
                    title: LANG.UI_DB_CDP_IMP_STATUS,
                    formatter:function(value,row){
                        let str = '';
                        if(row.status_type_import == CONF.FLAG.SET){
                            str = `<span class="label label-success">`+LANG.UI_PUBLIC_SUCCESS+`</span>`;
                        }else if(row.status_type_import == CONF.FLAG.UNSET){
                            str = `<span class="label label-danger">`+LANG.UI_DATACENTER_FAILURE+`</span>`;
                        }else{
                            str = `<span class="label label-warning">`+LANG.UI_CLOUD_PLATFORM_UNKNOWN+`</span>`;
                        }
                        return str;
                    }
                },
            ],
        }
        if (!initGridFlag) {
            $('#import_export_table').baseTableConfig().init(options);
            initGridFlag = true;
        } else {
            $('#import_export_table').bootstrapTable('refresh');
        }
    }

    /**
     * 事务详情
     */
    const getTransactionsDetails = () => {
        let options = {
            vin_url: '/api/v1/dbcdp/restore_data/' + backupInfo.target_agent_uuid + '/sync/' + backupInfo.source_agent_uuid + '/transaction',
            vin_params: function () {
                var params = {};
                params.task_uuid = $("#task_uuid").val();
                let transaction_id = '';
                switch (sync_direction){
                    case 1:
                        transaction_id = 71;
                        break;
                    case 2:
                        transaction_id = 72;
                        break;
                }
                params.transaction_id = transaction_id;
                return params;
            },
            vin_method: 'GET',
            onResetView: initTableHeight,
            detailView: true,        //需要更新的表格配置项,此项为是否开启展开详情视图
            detailFormatter: (index, row, $detail) => {
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
                var widths = ['5%','95%'];
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
            },
            onRefresh: function (params) {
                $("#transaction_details_table").bootstrapTable('hideLoading');
            },
            columns: [ //列定义
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
            ],
        }
        if (!initTransactionDtailsGridFlag) {
            $('#transaction_details_table').baseTableConfig().init(options);
            initTransactionDtailsGridFlag = true;
        } else {
            $('#transaction_details_table').bootstrapTable('refresh');
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

    /**
     * 切换时间范围（时间段/任意时间）
     */
    var toggleTimeRange = function (){
        if(!failbackTimeRangeFlag){
            $('.any_time_div').show();
            $('.daterangepickerdiv').hide();
            $('.any_time_toggle').html(LANG.UI_DB_CDP_DETAILS_SWITCH_TIME_RANGE);
            failbackTimeRangeFlag = true;
        }else{
            $('.any_time_div').hide();
            $('.daterangepickerdiv').show();
            $('.any_time_toggle').html(LANG.UI_DB_CDP_DETAILS_SWITCH_ANY_TIME);
            failbackTimeRangeFlag = false;
        }
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 130;

        $("#import_export_div .fixed-table-body").css({
            "height": height
        });

        $("#transaction_details_div .fixed-table-body").css({
            "height": height
        });
    }

    /**
     * 如果没有开启延迟重放，则任务类型只有“恢复到最新时间”
     */
    const initTakeoverTimeTypeStatus = () => {
        const takeover_time_type = document.getElementById('takeover_time_type');
        takeover_time_type.innerHTML = '';
        let options = `<option value="1">`+ LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST +`</option>`;
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
        takeover_time_type.innerHTML = options;
    }

    /**
     * 切换接管时间类型
     */
    var changeTakeoverTimeType = function (){
        $('.takeoverTimeDiv').hide();
        $('.takeoverScnDIv').hide();
        $('.inputTakeoverTimeDIv').hide();
        var selectedValue = parseInt($(this).val());
        if(selectedValue == 2){
            // 选择指定时间点
            if(delayTimeFlag){
                // 打开了延迟重放
                $('.takeoverTimeDiv').show();
            }else{
                $('.inputTakeoverTimeDIv').show();
            }
        }else if(selectedValue == 3){
            // 选择指定scn
            $('.takeoverScnDIv').show();
        }
    }

    const encryptTransfer= function () {
        if (this.checked) {
            $(".encrypttransfermethoddiv").show();
        } else {
            $(".encrypttransfermethoddiv").hide();
        }
    }

    const compressTransfer = function(){
        if (this.checked) {
            $(".compressgradediv").show();
        } else {
            $(".compressgradediv").hide();
        }
    }

    const changeFailBackStrategy = function(){
        let selectedValue = $('#failBackStrategy_select').find('option:selected').val();
        if(selectedValue == CONF.FLAG.SET){
            // 手动
            $('.manualStrategyTypediv').show();
            $('.failBackStrategyTimediv').show();
        }else {
            $('.manualStrategyTypediv').hide();
            $('.failBackStrategyTimediv').hide();
        }
    }

    /**
     * 提交回切配置
     */
    var failBackConfigSubmit = function () {
        // 回切目标主机
        if (Object.keys(targettreeNode).length == 0 || targettreeNode.agent_uuid == '') {
            UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_DB_CDP_DETAILS_MATCHING_TARGET_MACHINE_SELECT);
            return false;
        }

        //回切策略
        // 回切时间段
        failbackConfigParams.service_failback_config.service_failback_mode = parseInt($('#failBackStrategy_select').find('option:selected').val());
        if(failbackConfigParams.service_failback_config.service_failback_mode == 1){
            if(!failbackTimeRangeFlag){
                var validDateRangePickerValue = $('input[name=failback_strategy_time_picker]').val();
                var validDateRangePickerArr = validDateRangePickerValue.split(' - ');
                failbackConfigParams.service_failback_config.service_failback_start_time = validDateRangePickerArr[0]??'';
                failbackConfigParams.service_failback_config.service_failback_end_time = validDateRangePickerArr[1]??'';
            }else{
                failbackConfigParams.service_failback_config.service_failback_start_time = '';
                failbackConfigParams.service_failback_config.service_failback_end_time = '';
            }
            failbackConfigParams.service_failback_config.service_failback_auto_trigger_threshold = parseInt($('#free_time_interval').val());
        }
        //文件缓存路径
        var failbackFileCachePath = $("#file_cache_path").val(); //文件缓存路径,当前阶段填充默认值,后期自定义时需要校验路径是否存在
        if (failbackFileCachePath == LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT) {
            failbackFileCachePath = "";
        }else{
            if(!checkFilePath(failbackFileCachePath,'Linux')){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
                return false;
            }
        }
        let source_fileCacheAllocSpace = '';
        if(!sourceToggleLimitModeFlag){
            let source_file_cache_unit = $('select[name=source_file_cache_failback_unit] option:selected').val();
            source_fileCacheAllocSpace = parseInt($('#failback_source_fileCacheSize').val()) * Math.pow(1024,(parseInt(source_file_cache_unit)+2));
        }else{
            source_fileCacheAllocSpace = -1;
        }
        // 备机文件缓存大小 单位：字节
        let fileCacheAllocSpace = '';
        if (!backupToggleLimitModeFlag){
            let backup_file_cache_unit = $('select[name=backup_file_cache_failback_unit] option:selected').val();
            fileCacheAllocSpace = parseInt($('#failback_backup_fileCacheSize').val()) * Math.pow(1024,(parseInt(backup_file_cache_unit)+2));
        }else{
            fileCacheAllocSpace = -1;
        }
        //压缩传输
        var transportCompressFlag = false;
        if ($('#failbackTranCompressSwitch').is(':checked')) {
            failbackConfigParams.transport_strategy.transport_compress_method = $('#compresstransferselect option:selected').val();
            transportCompressFlag = true;
        }
        //加密传输
        var transportEncryptFlag = false;
        if ($('#failbackTranEncryptSwitch').is(':checked')) {
            transportEncryptFlag = true;
            failbackConfigParams.transport_strategy.transport_encrypt_method = $('#encrypttransfermethodselect option:selected').val();
        }


        // 回切主节点、从属节点不能相同
        failbackConfigParams.failback_object.source_host_uuid = $('#hostNode option:selected').val();
        failbackConfigParams.failback_object.target_host_uuid = $('#slaveNode option:selected').val();

        if(failbackConfigParams.failback_object.source_host_uuid === failbackConfigParams.failback_object.target_host_uuid && (failback_object.source_cluster_flag || failback_object.target_cluster_flag)){
            UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_DB_CDP_BACKUP_SOURCE_TARGET_TIPS);
            return false;
        }

        // 数据表空间存储目录
        let tablespaceDirSelectValue = $('#tablespaceDir option:selected').val();
        if(tablespaceDirSelectValue == '1'){
            failbackConfigParams.app_tablespace_dir = $('#customDirectory').val();
            if(failbackConfigParams.app_tablespace_dir == ''){
                UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK,LANG.UI_DB_CDP_DETAILS_STORAGE_DIR_TIPS);
                return false;
            }
            // 1、如果目标端是单机，则路径应该是以Linux的路径，如：/DATA_01/app/oracle/oradata/monitor/；
            // 2、如果目标端是集群，则路径应该是以+开头的路径，如：+DATADG/capcom/datafile/；
            if(failbackConfigParams.app_tablespace_dir != '' && !checkFilePath(failbackConfigParams.app_tablespace_dir,'Linux') && !failbackConfigParams.failback_object.target_cluster_flag){
                UIToastr.showWarning(LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG, LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG_TIPS);
                return false;
            }
            if(failbackConfigParams.failback_object.target_cluster_flag){
                const appTablespaceDir = failbackConfigParams.app_tablespace_dir;
                let pathFlag = appTablespaceDir.startsWith('+');
                let newPath = '/' + appTablespaceDir.slice(1);
                if(!pathFlag || !checkFilePath(newPath)){
                    UIToastr.showWarning(LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG, LANG.UI_DB_CDP_DETAILS_DIRECTORY_CONFIG_TIPS);
                    return false;
                }
            }
        }else{
            failbackConfigParams.app_tablespace_dir = '';
        }

        //封装回切配置消息结构
        failbackConfigParams.task_uuid = taskUuid;
        failbackConfigParams.transport_strategy.transport_encrypt_flag = transportEncryptFlag;
        failbackConfigParams.transport_strategy.transport_compress_flag = transportCompressFlag;
        failbackConfigParams.exp_max_process_num = parseInt($('#exp_thread').val());
        failbackConfigParams.imp_max_process_num = parseInt($('#imp_thread').val());
        failback_object.failback_business_ip_map = [];
        failbackConfigParams.failback_object.source_host_uuid = failback_object.source_host_uuid;
        failbackConfigParams.failback_object.source_app_uuid = failback_object.source_app_uuid;
        failbackConfigParams.failback_object.source_cluster_flag = failback_object.source_cluster_flag;
        failbackConfigParams.cache_config.source_file_cache_alloc_space = source_fileCacheAllocSpace;
        failbackConfigParams.cache_config.file_cache_alloc_space = fileCacheAllocSpace;
        failbackConfigParams.cache_config.file_cache_storage_path = failbackFileCachePath;
        failbackConfigParams.failback_object.failback_mode = $('#selectdataTypeconf option:selected').val();
        failbackConfigParams.failback_object.failback_business_ip_map = [];
        if(data.takeover_object.takeover_business_ip_map !== 0){
            for(var i =0;i<data.takeover_object.takeover_business_ip_map.length;i++){
                failbackConfigParams.failback_object.failback_business_ip_map.push(data.takeover_object.takeover_business_ip_map[i]);
            }
        }

        // 没有配置ip映射
        if(failbackConfigParams.failback_object.failback_business_ip_map.length === 0 && switchIpFlag && !failback_object.target_cluster_flag){
            UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_DB_CDP_BACKUP_TAKEOVER_NIC_CONF_TIPS);
            return false;
        }
        // dbms接口等高级配置
        failbackConfigParams.extend_advanced_config.use_dbms = $('#use_dbms_Switch').get(0).checked;
        failbackConfigParams.extend_advanced_config.timed_gen_txn = $('#timed_gen_txn_Switch').get(0).checked;
        failbackConfigParams.extend_advanced_config.sync_big_object = $('#sync_big_object_Switch').get(0).checked;
        failbackConfigParams.extend_advanced_config.replay_ddl = $('#replay_ddl_Switch').get(0).checked;
        failbackConfigParams.extend_advanced_config.ignore_nonsupport_data_type = $('#ignore_nonsupport_data_type_Switch').get(0).checked;
        failbackConfigParams.retry_strategy.reconnect_times = $('#network_retry_times').val();
        failbackConfigParams.retry_strategy.reconnect_interval = $('#network_retry_interval').val();
        Metronic.blockUI({target: '#taskFailbackupModal', animate: true});
        pAjaxRequest(failbackConfigParams, '/api/v1/dbcdp/jobs/' + taskUuid + '/failback', 'POST', function (d) {
            Metronic.unblockUI('#taskFailbackupModal');
            var data = d.success;
            if (!data) {
                var msg = d.message;
                UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, msg);
                return;
            } else {
                failbackConfigFlag = true;
                $('#taskFailbackupModal').modal('hide');
            }
        })
    }

    // 提取通用的文件大小格式化函数
    function formatFileCacheSize(sizeValue) {
        if (sizeValue === -1) {
            return LANG.UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE;
        }

        // 转换为GB单位
        const sizeInGB = parseInt(sizeValue) / 1024 / 1024 / 1024;

        if (sizeInGB >= 1024) {
            // 大于等于1024GB时使用TB单位，并保留两位小数
            return (sizeInGB / 1024).toFixed(2) + 'TB';
        } else {
            // 小于1024GB时使用GB单位，并保留两位小数
            return sizeInGB.toFixed(2) + 'GB';
        }
    }

    const showFailbackConfig = ()=>{
        var jobUuid = $("#task_uuid").val();
        pAjaxRequest({}, '/api/v1/dbcdp/jobs/' + jobUuid + '/failback', 'GET',function (d){
            // 记住回切配置的展示
            let data = d.data;
            $('#manual_takeover_config').show();
            $('.failback_config_show').show();
            // 回切目标
            var failbackTargetStr = data.agent_info[0].hostname+'('+ data.agent_info[0].ip +')';
            $('.failbackConfig #failbackTargetStr').html(failbackTargetStr);
            // 网络配置
            var conf_des = '';
            var takeoverBusinessIpMap = d.data.failback_business_ip_map;
            if(takeoverBusinessIpMap.takeover_business_ip_map.length ==0){
                conf_des = LANG.UI_PUBLIC_NOTHING;
            }else{
                takeoverBusinessIpMap.takeover_business_ip_map.forEach((item)=>{
                    conf_des += item.conf_des;
                });
            }
            $('#failbackNetStr').html(conf_des);
            // 数据类型
            var failbackMode = data.failback_mode;
            var failbackModeStr = '';
            switch (failbackMode){
                case 1:
                    failbackModeStr = LANG.UI_DB_CDP_DETAILS_FAILBACK_MODE1;
                    break;
                case 2:
                    failbackModeStr = LANG.UI_DB_CDP_DETAILS_FAILBACK_MODE2;
                    break;
            }
            $('.failbackConfig #dataTypeStr').html(failbackModeStr);
            // 数据表空间存储目录
            var failback_app_tablespace_dir = data.failback_app_tablespace_dir;
            var failbackAppTableSpaceDirStr = '';
            if(failback_app_tablespace_dir != ''){
                failbackAppTableSpaceDirStr = failback_app_tablespace_dir;
            }else{
                failbackAppTableSpaceDirStr = LANG.UI_DB_CDP_DETAILS_DIRECTORY_OF_THE_SYSTEM_TABLE_SPACE;
            }
            $('.failbackConfig #tableSpaceDirStr').html(failbackAppTableSpaceDirStr);
            // 回切策略
            var serviceFailbackMode = data.service_failback_mode;
            var serviceFailbackStartTimeStr = data.service_failback_start_time;
            var serviceFailbackEndTimeStr = data.service_failback_end_time;
            var serviceFailbackModeStr = '';
            switch (serviceFailbackMode){
                case 1:
                    serviceFailbackModeStr = LANG.UI_DB_CDP_DETAILS_SERVICE_FAILBACK_MODE1;
                    break;
                case 2:
                    serviceFailbackModeStr = LANG.UI_DB_CDP_DETAILS_SERVICE_FAILBACK_MODE2;
                    break;
                case 3:
                    serviceFailbackModeStr = LANG.UI_DB_CDP_DETAILS_SERVICE_FAILBACK_MODE3;
                    break;
            }
            $('.failbackConfig #failbackStrategyStr').html(serviceFailbackModeStr);
            if(serviceFailbackMode == CONF.FLAG.SET){
                // 自动
                $('.freeTimeIntervalDiv').show();
                $('.serviceFailbackStartTimeDiv').show();
                $('.serviceFailbackEndTimeDiv').show();
                $('.failbackConfig #serviceFailbackStartTimeStr').html(serviceFailbackStartTimeStr !== '0000-00-00 00:00:00' ? serviceFailbackStartTimeStr : '--');
                $('.failbackConfig #serviceFailbackEndTimeStr').html(serviceFailbackEndTimeStr != '0000-00-00 00:00:00' ? serviceFailbackEndTimeStr : '--');
                // 触发自动回切阈值
                $('.failbackConfig #freeTimeIntervalStr').html(data.service_failback_auto_trigger_threshold);
            }else{
                $('.freeTimeIntervalDiv').hide();
                $('.serviceFailbackStartTimeDiv').hide();
                $('.serviceFailbackEndTimeDiv').hide();
            }
            // 源文件缓存大小处理
            const sourceFileCacheAllocSpace = data.source_file_cache_alloc_space;
            const sourceFileCacheUsedSpace = data.source_file_cache_used_space;
            $('#failback_source_fileCacheSizeStr').html(
                sourceFileCacheUsedSpace + '/' + formatFileCacheSize(sourceFileCacheAllocSpace)
            );
            // 备机文件缓存大小处理
            const backupFileCacheAllocSpace = data.file_cache_alloc_space;
            const backupFileCacheUsedSpace = data.file_cache_used_space;
            $('#fileCacheSizeStr').html(
                backupFileCacheUsedSpace + '/' +formatFileCacheSize(backupFileCacheAllocSpace)
            );
            // 文件缓存路径
            var fileCacheStoragePathStr = data.file_cache_storage_path;
            $('.failbackConfig #fileCachePathStr').html(fileCacheStoragePathStr);
            // 传输数据压缩
            var transportCompressFlag = data.transport_compress_flag;
            $('#dataCompressFlag').html(getFlagLevelInfo(transportCompressFlag));
            var transportCompressMethod = data.transport_compress_method;
            if(transportCompressFlag){
                $('.dataCompressStrDiv').show();
                for(var key in compressMethod){
                    if(transportCompressMethod == compressMethod[key]){
                        $('#dataCompressStr').text(key);
                    }
                }
            }else{
                $('.dataCompressStrDiv').hide();
            }

            // 加密传输
            var transportEncryptFlag = data.transport_encrypt_flag;
            $('#encryptTransmissFlag').html(getFlagLevelInfo(transportEncryptFlag));
            var transportEncryptMethod = data.transport_encrypt_method;
            if(transportEncryptFlag){
                $('.encryptTransmissStrDiv').show();
                for(var key in encryptMethod){
                    if(transportEncryptMethod == encryptMethod[key]){
                        $('#encryptTransmissStr').text(key);
                    }
                }
            }else{
                $('.encryptTransmissStrDiv').hide();
            }

            //线程个数
            var exp_max_process_num = data.exp_max_process_num;
            var imp_max_process_num = data.imp_max_process_num;
            $('#exp_thread_num_str').html(exp_max_process_num);
            $('#imp_thread_num_str').html(imp_max_process_num);
            $('#network_retry_times_spinner').spinner("value", data.reconnect_times);
            $('#network_retry_interval_spinner').spinner("value", data.reconnect_interval);
            $('.failbackConfig #use_dbms_value').html(getFlagLevelInfo(data.extend_advanced_config.use_dbms));
            $('.failbackConfig #sync_big_object_value').html(getFlagLevelInfo(data.extend_advanced_config.sync_big_object));
            $('.failbackConfig #replay_ddl_value').html(getFlagLevelInfo(data.extend_advanced_config.replay_ddl));
            $('.failbackConfig #ignore_nonsupport_data_type_value').html(getFlagLevelInfo(data.extend_advanced_config.ignore_nonsupport_data_type));
            $('.failbackConfig #network_retry_times_value').html(data.reconnect_times);
            $('.failbackConfig #network_retry_interval_value').html(data.reconnect_interval);

        },false);
    }

    const getFailbackNet = function (){
        // 区分
        loadAgentNetworkInfo(failback_object.source_host_uuid,false,true);
        takeoverNetworkConfModal(this);
    }

    /**
     * 操作按钮-启动同步
     * @return {boolean}
     */
    const startJob = function () {
        if ($(this).find('a').hasClass('disablebtn')) {
            return true;
        }
        if(taskType == CONF.TASK_TYPE.CDP_DB_BACKUP){
            startJobUnify('startJob', 1);
        }else if(taskType == CONF.TASK_TYPE.CDP_DB_RECOVERY){
            var initErrorFlag = false;
            getUserPassword();
            var bootBoxText = LANG.UI_DB_CDP_JOB_START_RECOVERY_CONFIRM;
            bootbox.prompt({
                title: bootBoxText,
                inputType: 'password',
                callback: debounce(function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        userIsVerify = true;
                        startJobUnify('startJob', 2);
                        $(this).modal('hide');
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                },300,false)
            });
        }
    }

    /**
     * 启动任务
     */
    const startJobUnify = function (funName, type) {
        Metronic.blockUI({
            target: '#jobDetail',
            animate: true
        });

        var data = {};
        data.task_uuid = $('#task_uuid').val();
        data.module = CONF.MODULE_TYPE.DB_CDP;
        data.startType = type;
        Metronic.blockUI({target: '#jobDetail', animate: true});
        let param = {'start_type': data.startType};
        pAjaxRequest(param, '/api/v1/jobs/start/' + data.task_uuid, 'POST', function (d) {
            Metronic.unblockUI('#jobDetail');
            var op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
            if(d.success){
                $('#manual_takeover_config').hide();
                operateResponseList(d, op)
            }
        })
    }

    /**
     * 操作按钮-删除
     * @return {boolean}
     */
    const deleteJob = function () {
        if ($(this).find('a').hasClass('disablebtn')) {
            return true;
        }
        Metronic.blockUI({target: '#jobDetail', animate: true});
        pAjaxRequest({}, '/api/v1/jobs/' + taskUuid, 'delete', function (d) {
            var op = LANG.UI_OS_SEND_DELETE_TASK_MESSAGE;
            Metronic.unblockUI('#jobDetail');
            if (operateResponseList(d, op)) {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
        })
    }

    /**
     * 初始化当前用户密码用于删除二次确认
     */
    const getUserPassword = function () {
        pAjaxRequest({}, "/api/v1/users/password", "GET", function (res) {
            _UserPassword = res.data.password;
        }, true);
    }

    //______________________启动接管_____________________________________

    /**
     * 启动手动接管
     * @return {boolean}
     */
    const startTakeover = function () {
        if ($(this).find('a').hasClass('disablebtn')) {
            return true;
        }
        // 显示手动接管模态框
        $('#taskTakeOverModal').modal({'width': '940px', 'height': '500px'});
        initTakeoverTimeTypeStatus();
        // 获取时间范围
        getTimeRange(failback_object.source_host_uuid);
        // echarts图
        getAgentTimelineData();
        // 切换时间范围
        $('#changeTimeInterval').on('change', changeTimeIntervalType);
        // 点击li事件信息  获取所有的事件信息
        $('#timepointType li').on('click', loadCheckAgentEventInfo);
        // 显示回切通讯ip、服务ip漂移
        takeOverConfig();
        // 启动接管 启动函数,模块类型
        $('#takeOverConfigSubmit').off().on('click', function (event) {
            startTakeoverJobUnify();
        });
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
    const getTimeRange = function (agentuuid) {
        pAjaxRequest({}, '/api/v1/dbcdp/jobs/restore_data/' + agentuuid + '/time_range', 'GET',  (d) => {
            let data = d.data;
            // 这里要改接口的返回值
            let lastReplayTime = data.last_replay_time;
            let latestRecvTransactionTime = data.latest_recv_transaction_time;
            let last_redo_replay_scn = data.last_redo_replay_scn;
            let latest_recv_transaction_scn = data.latest_recv_transaction_scn;
            newTime = latestRecvTransactionTime;
            // 时间戳
            _lastReplayTimestamp = lastReplayTime == '0000-00-00 00:00:00' ? 0 : new Date(lastReplayTime).getTime();
            _lastRecvTransactionTimestamp = new Date(latestRecvTransactionTime).getTime();
            $('#timeRange').html(lastReplayTime + '-' + latestRecvTransactionTime);
            $('#replay_scn').html(last_redo_replay_scn);
            $('#transaction_scn').html(latest_recv_transaction_scn);
        },false);
    }

    /**
     * @function 获取时间轴data
     * @desc 通过选定数据源UUID获取客户端备份时间轴对应的数据，并加载和渲染时间轴chart
     */
    const getAgentTimelineData =  () => {
        let time_data = [];
        Metronic.blockUI({target: '#takeoverTimeline', animate: true});
        let agent_uuid = failback_object.source_host_uuid;
        time_unit = $('#changeTimeInterval option:selected').val();
        let param = {'time_unit': time_unit,'task_uuid':taskUuid};
        pAjaxRequest(param, '/api/v1/dbcdp/restore_data/' + agent_uuid + '/data_flow', 'GET',  (d) => {
            Metronic.unblockUI('#takeoverTimeline');
            let data = d.data;
            let dataLength = data.time_data.length;
            _chartStartPercent = dataZoomUnit(dataLength);
            if (dataLength > 0) {
                time_data = data['time_data'];
                // 最新时间点
                _lastRecvTransactionTimePoint = time_data[time_data.length - 1][0];
                loadTimelineChart(time_data);
                //初始化时间控件
                loadDatatimePicker();
            } else {
                UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA, LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA_MESSAGE);
                loadTimelineChart(time_data);
            }
        },false);
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
    const loadTimelineChart =  (rawData) => {

        // $('#takeoverTimeline').css({"width": '1016.33' + 'px', "height": (349 - 34) + 'px'});

        echarts.init(document.getElementById('takeoverTimeline')).dispose(); //销毁chart
        let chartDom = document.getElementById('takeoverTimeline');
        takeoverChart = echarts.init(chartDom);
        window.addEventListener('resize', function () {
            takeoverChart.resize();
        });
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
                        show: true,
                        title:LANG.UI_DB_CDP_RECOVER_REFRESH_GET_LATEST_TIME_POINT,
                    },
                },
                right:'15%',
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
        takeoverChart.setOption(option);

        // 点击刷新获取最新时间点
        takeoverChart.on('restore', function (){
            getTimeRange(backupInfo.target_agent_uuid);
            getAgentTimelineData();
        });

        //时间轴流量范围内的点击事件
        takeoverChart.getZr().on("click", params => {
            // 获取点击位置
            _timepointType = 0;
            const pointInPixel = [params.offsetX, params.offsetY];
            if (takeoverChart.containPixel("grid", pointInPixel)) {
                // 获取点击位置的坐标系[x，y]
                let xIndex = takeoverChart.convertFromPixel({seriesIndex: 0}, [params.offsetX, params.offsetY])[0];
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
                        $('#inputTakeoverTimepoint').val(checkTime);
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
     */
    const loadCheckAgentEventInfo = () => {
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
            },
        );

        var options = {
            vin_url: '/api/v1/dbcdp/restore_data/' + backupInfo.target_agent_uuid + '/sync/' + backupInfo.source_agent_uuid + '/transaction',
            vin_params: function () {
                var params = {};
                params.event_time = _checkTime;
                params.transaction_id = 70;
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
            },
            onCheck :(row) =>{
                // 如果开启了延迟重放，选择的事务在时间范围内就填充到 恢复时间点
                // 事务时间
                let transactionTime = new Date(row.transaction_time).getTime();
                if(delayTimeFlag && transactionTime >= _lastReplayTimestamp && transactionTime <= _lastRecvTransactionTimestamp){
                    $('#inputTakeoverTimepoint').val(row.transaction_time);
                    verifyTimepointisValid(row.transaction_time);
                }
            },
            resizable: false,
            singleSelect: true,
            columns: columns,
        }
        if (!initTransactionGridFlag) {
            $('#transactionInfoTable').baseTableConfig().init(options);
            initTransactionGridFlag = true;
        } else {
            $('#transactionInfoTable').bootstrapTable('refresh');
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
     * 切换时间范围
     */
    const changeTimeIntervalType = function () {
        var timeIntervalType = $('#changeTimeInterval').val();
        _chartStartPercent = 70;
        _timeInterval = timeIntervalType;
        getAgentTimelineData();
    }

    /**
     * 手动接管的配置项
     */
    const takeOverConfig = function (){
        $('#serviceIpDriftTakeover').bootstrapSwitch('state', false);
        manual_switchIpFlag = false;
        $('#serviceIpDriftTakeover').on('switchChange.bootstrapSwitch', function () {
            manual_switchIpFlag = this.checked;
            if (this.checked) {
                taskOverTaskParams.switch_ip_flag = true;
                $('.takeoverNetConf').show();
                if(!backupInfo.source_agent_cluster_flag){
                    // 同步的源端为单节点 显示回切通讯ip
                    $('#failbackTargetIpContent').show();
                }
            } else {
                taskOverTaskParams.switch_ip_flag = false;
                taskOverTaskParams.failback_target_ip = '';
                taskOverTaskParams.takeover_business_ip_map = [];
                $('.takeoverNetConf').hide();
                $('#failbackTargetIpContent').hide();
            }
        });
        $('#takeover_network_conf').off().on('click',function (){
            backupInfo.source_agent_cluster_flag ? loadClusterNetworkInfo(backupInfo.source_agent_uuid):loadAgentNetworkInfo(backupInfo.source_agent_uuid,true,false);
            backupInfo.target_agent_cluster_flag ? loadClusterNetworkInfo(backupInfo.target_agent_uuid,true):loadStandbyNetworkInfo(backupInfo.target_agent_uuid);
			if(backupInfo.source_agent_cluster_flag){
                scanIP(backupInfo.source_agent_uuid,backupInfo.source_app_uuid);
            }else{
                takeoverNetworkConfModal(this);
            }
        });
    }

    /**
     * 初始化接管网络配置
     */
    const initTakeoverConfig  = () => {
        // 业务IP配置
        $('#host_ip_server_conf').takeoverIpServerMapConfig({
            agentNetwork:agentNetworkInfo,      //主机的网卡信息
            standbyNetwork:standbyNetworkInfo,  //备机的网卡信息
            takeoverHisNetwork:[],
            failbackHisNetwork:[],
        });
        //  备机网关信息
        $('#standby_gateway_conf').takeoverStandbyGatewayConfig({
            agentNetwork:agentNetworkInfo,      //主机的网卡信息
            standbyNetwork:standbyNetworkInfo,  //备机的网卡信息
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
                    'node_uuid':'',
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
            // 提交配置
            $("#submit_host_network_conf").off().click(submitHostNetworkConf);
        });
    }

    /**
     * 集群IP网卡信息
     */
    const loadClusterNetworkInfo = function (agent_uuid,targetFlag = false) {
        let param = {'agent_uuid':agent_uuid};
        pAjaxRequest(param,'/api/v1/dbcdp/jobs/sourcecluster/networkInfo','GET',function (d){
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
        },false);
    }

    /**
     * 获取客户端网卡信息(回切源)
     * 回切、同步(接管)单机相同、集群取字段不同
     * 回切的集群也在函数loadAgentNetworkInfo取
     * 接管/同步的集群在函数loadClusterNetworkInfo取
     */
    const loadAgentNetworkInfo = function (agent_uuid,takeoverFlag = false,failbackFlag = false){
        let param = {'agent_uuid':agent_uuid};
        pAjaxRequest(param,'/api/v1/dbcdp/jobs/networkInfo','GET',function (d){
            agentNetworkInfo = d.data;
            // 如果源机是单机 则需要过滤 ip_scope_info 中的 ip
            if (!backupInfo.source_agent_cluster_flag && takeoverFlag) {
                // 遍历数组中的每个 agentNetworkInfo 对象
                agentNetworkInfo.forEach(networkInfo => {
                    networkInfo.ip_set = networkInfo.ip_set.filter(item => {
                        // 检查当前IP是否存在于ip_scope_info数组的任何元素中
                        return !takeover_ip_scope_info.some(scope => scope.ip === item.ip_addr);
                    });
                });

            }
            if (!failback_object.source_cluster_flag && failbackFlag) {
                // 遍历数组中的每个 agentNetworkInfo 对象
                if(failback_ip_scope_info.length >0 ){
                    agentNetworkInfo.forEach(networkInfo => {
                        networkInfo.ip_set = networkInfo.ip_set.filter(item => {
                            // 检查当前IP是否存在于ip_scope_info数组的任何元素中
                            return !failback_ip_scope_info.some(scope => scope.ip === item.ip_addr);
                        });
                    });
                }
            }
            return agentNetworkInfo;
        },false);
    }

    /**
     *  获取目标端网卡信息
     */
    const loadStandbyNetworkInfo = function (agent_uuid) {
        let param = {'agent_uuid':agent_uuid};
        pAjaxRequest(param,'/api/v1/dbcdp/jobs/networkInfo','GET',function (d){
            standbyNetworkInfo = d.data;
        },false);
    }

    /**
     * 接管网络配置
     */
    const takeoverNetworkConfModal =  (buttonElement) => {
        // 区分是接管还是回切选择网卡信息
        triggeredElementId = buttonElement.id;
        // 备机的val
        let targetAgentVal = $('#selectstandby option:selected').val();
        //
        if(targetAgentVal == 0 ){
            UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION, LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION_TIPS);
            $('#script_takeover_switch').bootstrapSwitch('state', false);
            return false;
        }
        initTakeoverConfig();
        // 提交配置
        $("#submit_host_network_conf").off().click(submitHostNetworkConf);
    }

    /**
     * 获取配置网卡描述信息，并渲染配置
     */
    const submitHostNetworkConf = function (){
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
            $('#failback_ip_map_list li').remove();
            hostGatewayConfDes();
        }

        $('#takeoverNetworkConfModal').modal('hide');

    }

    /**
     * 获取备机网卡配置
     */
    const getStandbyGateWayConf = function(){
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
     * 获取配置网卡描述信息，并渲染
     */
    var hostGatewayConfDes = function(){
        var des = "";
        data.takeover_object.takeover_business_ip_map = [];
        $('#failback_ip_map_list').empty();
        $('#takeover_ip_map_list').empty();
        for(var i=0;i<_takeoverIpMapList.length;i++){
            var conf_id = _takeoverIpMapList[i].conf_id;
            var des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+ _takeoverIpMapList[i].conf_id
                +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
                + _takeoverIpMapList[i].conf_des + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 80%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
                + _takeoverIpMapList[i].conf_des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="takeover_netcard_del'+  _takeoverIpMapList[i].conf_id +'" >'
                +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
            triggeredElementId == 'faiback_network_conf' ? $('#failback_ip_map_list').append(des) : $('#takeover_ip_map_list').append(des);
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
    var clickHandler = function(event) {
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


    //_________________________________________________

    /**
     * 启动手动接管
     */
    const startTakeoverJobUnify = function () {
        bootbox.confirm({
            title: LANG.UI_DB_CDP_DETAILS_START_MANUAL_TAKEOVER,
            message: LANG.UI_DB_CDP_DETAILS_DETERMINE_TO_START_THE_MANUAL_TAKEOVER_TASK,
            callback: debounce(function (r) {
                if (!r) return;
                if (!takeoverFlag) {
                    startTakeoverAjax();
                }
            },300)
        });
    }

    const startTakeoverAjax = () =>{
        taskOverTaskParams.job_uuids.push(taskUuid);
        let syncSourceNodeCluster = backupInfo.source_agent_cluster_flag;
        taskOverTaskParams.failback_target_ip = $('#failbackTargetIp').val();
        // 校验回切通讯IP
        if(manual_switchIpFlag && !syncSourceNodeCluster && !taskOverTaskParams.failback_target_ip){
            UIToastr.showWarning(LANG.UI_DB_CDP_DETAILS_NETWORK_CONFIG_EXCEPTION, LANG.UI_DB_CDP_DETAILS_FAILBACK_IP_TIPS);
            return;
        }
        if(manual_switchIpFlag && !syncSourceNodeCluster && !ipCheckFlag){
            UIToastr.showWarning(LANG.UI_DB_CDP_DETAILS_NETWORK_CONFIG_EXCEPTION, LANG.UI_DB_CDP_TEST_FAILBACK_IP);
            return false;
        }
        if(manual_switchIpFlag && !syncSourceNodeCluster && ipCheckFlag && !_failbackIpVerify){
            UIToastr.showWarning(LANG.UI_DB_CDP_DETAILS_NETWORK_CONFIG_EXCEPTION, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP_MESSAGE);
            return false;
        }

        // 选择恢复时间点
        let takeoverTimeVal = $('.takeoverTimeType option:selected').val();
        if(parseInt(takeoverTimeVal) == CONF.FLAG.SET || !delayTimeFlag){
            // 最新时间点
            taskOverTaskParams.takeover_timestamp = '';
            taskOverTaskParams.takeover_timepoint_type = CONF.FLAG.SET;
        }else if(parseInt(takeoverTimeVal) == CONF.FLAG.UNSET && delayTimeFlag){
            let takeoverTimepoint = $('#inputTakeoverTimepoint').val();
            taskOverTaskParams.takeover_timestamp = takeoverTimepoint;
            taskOverTaskParams.takeover_timepoint_type = CONF.FLAG.UNSET;
            if ((delayTimeFlag && takeoverTimepoint == '') || (!_checkTimeValid && delayTimeFlag)) {
                // 开启了延迟重放且恢复时间点为空
                // 开启了延迟重放且恢复时间点无效
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_DB_CDP_RECOVER_PLEASE_CHECK_THE_RECOVERY_TIME_POINT);
                return false;
            }
        }else if(parseInt(takeoverTimeVal) == 3){
            let takeover_scn = $('.takeoverScn').val();
            taskOverTaskParams.takeover_timepoint_type = 3;
            taskOverTaskParams.takeover_scn = takeover_scn;
        }

        // 打开服务IP漂移但是没有进行网卡配置
        if($('#serviceIpDriftTakeover').is(':checked') && data.takeover_object.takeover_business_ip_map.length === 0 && !backupInfo.target_agent_cluster_flag){
            UIToastr.showWarning(LANG.UI_DB_CDP_DETAILS_NETWORK_CONFIG_EXCEPTION, LANG.UI_DB_CDP_DETAILS_NETWORK_CONFIG_TIPS);
            return;
        }

        // 校验网卡配置
        if( data.takeover_object.takeover_business_ip_map !== 0){
            taskOverTaskParams.takeover_business_ip_map = [];
            for(var i =0;i<data.takeover_object.takeover_business_ip_map.length;i++){
                taskOverTaskParams.takeover_business_ip_map.push(data.takeover_object.takeover_business_ip_map[i]);
            }
        }

        Metronic.blockUI({target: '#jobDetail', animate: true});
        pAjaxRequest(taskOverTaskParams, '/api/v1/jobs/start_takeover', 'POST', function (d) {
            var op = LANG.UI_OS_SEND_START_TAKEOVER_TASK_MESSAGE;
            Metronic.unblockUI('#jobDetail');
            if (operateResponseList(d, op)) {
                var data = d.success;
                if (!data) {
                    UIToastr.showWarning('', LANG.UI_DB_CDP_DETAILS_NETWORK_CONFIG_EXCEPTION);
                    return;
                } else {
                    $('#taskTakeOverModal').modal('hide');
                    // 显示手动接管的信息
                    $('#manual_takeover_config').show();
                    getManualTakeoverConfig();
                }
            }
        })
    }

    /**
     * 显示自动/手动接管的信息
     */
    const getManualTakeoverConfig = function (){
        let param = {'jobUuid':taskUuid}
        pAjaxRequest(param,'/api/v1/dbcdp/jobs/takeover/info','GET',function (d){
            if(!d.success){
                return false;
            }
            let data = d.data;
            let manual_takeover = data.manual_takeover;
            let auto_takeover = data.auto_takeover;
            let auto_takeover_flag = auto_takeover.auto_takeover_flag;
            let auto_takeover_business_ip_map = auto_takeover.takeover_business_ip_map;
            let auto_failback_target_ip = auto_takeover.failback_target_ip;
            let auto_switch_ip_flag = auto_takeover.switch_ip_flag;
            let manual_takeover_flag = manual_takeover.manual_takeover_flag;
            let manual_failback_target_ip = manual_takeover.failback_target_ip;
            let manual_switch_ip_flag = manual_takeover.switch_ip_flag;
            let manual_takeover_scn = manual_takeover.takeover_scn;
            let manual_takeover_timepoint_type = manual_takeover.takeover_timepoint_type;
            let manual_takeover_business_ip_map = manual_takeover.takeover_business_ip_map;
            // 自动接管
            if(auto_takeover_flag){
                $('#auto_takeover_config').show();
                // 网卡配置
                if(!!auto_takeover_business_ip_map){
                    $('#auto_takeover_config .takeoverBusinessIpMapDiv').show();
                    var takeoverBusinessIpMapStr = '';
                    var takeoverBusinessIpMap = JSON.parse(auto_takeover_business_ip_map);
                    takeoverBusinessIpMap.takeover_business_ip_map.forEach(item => {
                        takeoverBusinessIpMapStr += item.conf_des;
                    });
                    $('#auto_takeover_config #takeoverBusinessIpMapStr').html(takeoverBusinessIpMapStr);
                }else{
                    $('#auto_takeover_config .takeoverBusinessIpMapDiv').hide();
                }
                // 回切通讯IP
                if(!!auto_failback_target_ip){
                    $('#auto_takeover_config .takeoverSwitchIpDiv').show();
                    $('#auto_takeover_config #takeoverSwitchIpStr').html(auto_failback_target_ip);
                }else{
                    $('#auto_takeover_config .takeoverSwitchIpDiv').hide();
                }
                // 服务IP漂移
                if(auto_switch_ip_flag){
                    $('#auto_takeover_config .takeoverIpdriftDiv').show();
                    $('#auto_takeover_config #takeoverIpdriftStr').html(getFlagLevelInfo(auto_switch_ip_flag));
                }else{
                    $('#auto_takeover_config #takeoverIpdriftStr').html(getFlagLevelInfo(auto_switch_ip_flag));
                }

            }else{
                $('#auto_takeover_config').hide();
            }
            // 手动接管
            if(manual_takeover_flag){
                manualTakeoverFlag = true;
                $('#manual_takeover_config').show();
                $('#manualTakeoverStr').html('<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>');
                if(!!manual_failback_target_ip){
                    $('#manual_takeover_config_view .failbackIPDiv').show();
                    $('#manual_takeover_config_view #failbackIPStr').html(manual_failback_target_ip);
                }else{
                    $('#manual_takeover_config_view .failbackIPDiv').hide();
                }
                if(manual_switch_ip_flag){
                    $('#manual_takeover_config_view .serviceIpDriftDiv').show();
                    $('#manual_takeover_config_view #serviceIpDriftStr').html(getFlagLevelInfo(manual_switch_ip_flag));
                }else{
                    $('#manual_takeover_config_view #serviceIpDriftStr').html(getFlagLevelInfo(manual_switch_ip_flag));
                }
                if(manual_takeover_timepoint_type == 3){
                    // 指定scn
                    $('.takeoverScnDiv').show();
                    $('#takeoverScnStr').html(manual_takeover_scn);
                }
                let manual_takeover_timepoint_type_str = '';
                switch(manual_takeover_timepoint_type){
                    case 0:
                        manual_takeover_timepoint_type_str = LANG.UI_PUBLIC_UNKNOWN;
                        break;
                    case 1:
                        manual_takeover_timepoint_type_str = LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST;
                        break;
                    case 2:
                        manual_takeover_timepoint_type_str = LANG.UI_VERIFY_SELECT_TIMEPOINT_SELECT;
                        break;
                    case 3:
                        manual_takeover_timepoint_type_str = LANG.UI_DB_CDP_DETAILS_TIME_METHOD_SPECIFIED_SCN;
                        break;

                }
                $('#manual_takeover_config_view #takeoverTimePointStr').html(manual_takeover_timepoint_type_str);
                // 网卡配置
                if(!!manual_takeover_business_ip_map){
                    $('#manual_takeover_config_view .takeoverNetDiv').show();
                    var takeoverBusinessIpMapStr = '';
                    var takeoverBusinessIpMap = JSON.parse(manual_takeover_business_ip_map);
                    takeoverBusinessIpMap.takeover_business_ip_map.forEach(item => {
                        takeoverBusinessIpMapStr += item.conf_des;
                    });
                    $('#manual_takeover_config_view #takeoverNetStr').html(takeoverBusinessIpMapStr);
                }else{
                    $('#manual_takeover_config_view .takeoverNetDiv').hide();
                }

            }else{
                $('#manualTakeoverStr').html('<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>');
                $('#manual_takeover_config').hide();
            }
        });
    }

    /**
     * 初始化时间控件
     */
    const loadDatatimePicker = () => {
        $(".takeovertimepointview").datetimepicker({
            language:  'zh-CN',
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-MM-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "top-right" : "top-left"),
            forceParse:false
        });
        if(delayTimeFlag){
            // newTime是echarts图最新的时间点
            $('#inputTakeoverTimepoint').val(newTime);
            verifyTimepointisValid(newTime);
        }else {
            // 关闭延迟重放 默认最新时间点
            $('.inputTakeoverTimeInput').val(newTime);
        }
    }

    //_________________________________________________________________________________________

    /**
     * 暂停任务
     */
    const pauseSynJob = function () {
        if ($(this).find('a').hasClass('disablebtn')) {
            return true;
        }
        //暂停单个任务
        pauseJobUnify();
    }

    /**
     * 暂停任务
     */
    const pauseJobUnify = function () {
        var data = {};
        data.job_uuids = [];
        var job_uuids = taskUuid;
        data.job_uuids.push(job_uuids);
        Metronic.blockUI({target: '#jobDetail', animate: true});
        let param = {'job_uuids': data.job_uuids};
        pAjaxRequest(param, '/api/v1/jobs/pause', 'post', function (d) {
            var op = LANG.UI_DB_CDP_DETAILS_SEND_PAUSE_TASK_MESSAGE;
            Metronic.unblockUI('#jobDetail');
            if (operateResponseList(d, op)) {
            }
        })
    }

    /**
     * 停止任务 需要输入密码
     */
    const stopJob = function () {
        var initErrorFlag = false;
        getUserPassword();
        var bootBoxText = '';
        if(taskType == CONF.TASK_TYPE.CDP_DB_BACKUP){
            bootBoxText = LANG.UI_DB_CDP_DETAILS_STOP_BACKUP_TASK_TIPS;
        }else if(taskType == CONF.TASK_TYPE.CDP_DB_RECOVERY){
            bootBoxText = LANG.UI_DB_CDP_DETAILS_STOP_RECOVER_TASK_TIPS;
        }
        bootbox.prompt({
            title: bootBoxText,
            inputType: 'password',
            callback: debounce(function (result) {
                if (result == null) return;
                if (hex_md5(result) == _UserPassword) {
                    userIsVerify = true;
                    stopJobAjax();
                    $(this).modal('hide');
                } else {
                    $('.bootbox-input').css('border-color', "#a94442");
                    if (!initErrorFlag) {
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                    }
                    return false;
                }
            },300,false)
        });
    }

    /**
     * 停止任务
     */
    var stopJobAjax = function () {
        if ($(this).find('a').hasClass('disablebtn')) {
            return true;
        }
        Metronic.blockUI({target: '#jobDetail', animate: true});
        pAjaxRequest({}, '/api/v1/jobs/stop/' + taskUuid, 'post', function (d) {
            Metronic.unblockUI('#jobDetail');
            var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
            if (operateResponseList(d, op)) {
            }
        })
    }

    /**
     * 停止接管
     */
    const stopTakeoverJob = function () {
        var initErrorFlag = false;
        var initStopFlag = false;
        confirmTakeoverFlag = false;
        getUserPassword();
        if(taskStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER){
            const dialog = bootbox.dialog({
                title: LANG.UI_JOB_STOP_TAKEOVER,
                message:'<div class="form-group" id="takeoverPause">' +
                    '<label class="control-label col-md-4 text-align_r mb-16 pl5">' +
                    LANG.UI_DB_CDP_DETAILS_STOP_TAKEOVER_TITLE +
                    '</label>' +
                    '<div class="col-md-7 pr0">' +
                    '<label class="pr20 radio-label control-label">' +
                    '<input type="radio" id="box1" name="radiobox" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck">'+ LANG.UI_PUBLIC_YES +
                    '</label>'+
                    '<label class="radio-label control-label mb-16">' +
                    '<input type="radio" id="box2" name="radiobox" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck">'+ LANG.UI_PUBLIC_NO +
                    '</label>'+
                    '</div>'+
                    '</div>'+
                    '<div class="form-group">' +
                    '<label class="control-label col-md-4 text-align_r mb-20 pl5 pt7">' + LANG.UI_DB_CDP_ENTER_PASSWORD_CONFIRM +
                    '</label>'+
                    '<div class="col-md-7 mb-20 pr0">' +
                    '<input type="password" class="form-control bootbox-input" name="password" placeholder="' + LANG.UI_DB_CDP_ENTER_CURRENT_PASSWORD_CONFIRM + '">' +
                    '</div>'+
                    '</div>'+
                    '<div class="col-md-12 pl5 pr5 mb5">' +
                    '<div class="alert alert-block alert-info fade in mb5" id="marktips">' +
                    '<h4 class="alert-heading">' +
                    '<strong>' +
                    LANG.UI_PUBLIC_TIPS +
                    '</strong>' +
                    '</h4>' +
                    '<ol class="alert-ol">' +
                    '<li>' + LANG.UI_DB_CDP_DETAILS_STOP_TAKEOVER_TIP1 + '</li>' +
                    '<li>' + LANG.UI_DB_CDP_DETAILS_STOP_TAKEOVER_TIP2 + '</li>' +
                    '<li>' + LANG.UI_DB_CDP_DETAILS_STOP_TAKEOVER_TIP3 + '</li>' +
                    '<li>' + LANG.UI_DB_CDP_DETAILS_STOP_TAKEOVER_TIP4 + '</li>' +
                    '<li>' + LANG.UI_DB_CDP_DETAILS_STOP_TAKEOVER_TIP5 + '</li>' +
                    '</ol>' +
                    '</div>' +
                    '</div>',
                buttons:{
                    cancel:{
                        label: LANG.UI_PUBLIC_CANCEL,
                        className:'btn default cancel',
                    },
                    confirm:{
                        label:LANG.UI_PUBLIC_CONFIRM,
                        className:'btn green-haze btn-confirm ml10',
                        callback:function () {
                            let password = $('input[name=password]').val();
                            if (!password) return false;
                            if (hex_md5(password) == _UserPassword) {
                                userIsVerify = true;
                                stopTakeOverAjax();
                            } else {
                                $('.bootbox-input').css('border-color', "#a94442");
                                if (!initErrorFlag) {
                                    var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                                    $('.bootbox-input').after(des);
                                    initStopFlag = true;
                                    initErrorFlag = true;
                                }
                                return false;
                            }
                        }
                    }
                },
            });
            // 关键：监听弹窗显示完成事件
            dialog.on('shown.bs.modal', function() {
                // 初始化iCheck
                $(this).find('input[type=radio]').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                    increaseArea: '20%'
                });
                $('#takeoverPause').find('input[data-mode=1]').iCheck("check");
            });
            // 是否还原各节点初始配置
            dialog.on('ifChecked', function(event) {
                const originalRadio = event.target;
                const mode = $(originalRadio).data('mode'); // 正确获取data-mode
                if (mode == CONF.FLAG.SET) {
                    confirmTakeoverFlag = false;
                } else {
                    confirmTakeoverFlag = true;
                }
            });
        }else if(taskStage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_LOG_SYNC){
            bootbox.prompt({
                title: LANG.UI_DB_CDP_DETAILS_STOP_CONFIRM_FAILBACK_TIPS,
                inputType: 'password',
                callback: debounce(function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        userIsVerify = true;
                        stopTakeOverAjax();
                        $(this).modal('hide');
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initStopFlag = true;
                            initErrorFlag = true;
                        }
                        return false;
                    }
                },300,false)
            });
        }
    }

    /**
     * 停止接管
     */
    const stopTakeOverAjax = function () {
        if ($(this).find('a').hasClass('disablebtn')) {
            return true;
        }
        var data = {};
        data.uuid = [];
        data.uuid.push(taskUuid);
        Metronic.blockUI({target: '#jobDetail', animate: true});
        let param = {'job_uuids': data.uuid,'confirmTakeoverFlag':confirmTakeoverFlag};
        pAjaxRequest(param, '/api/v1/jobs/stop_takeover', 'POST', function (d) {
            var op = LANG.UI_OS_SEND_STOP_TAKEOVER_TASK_MESSAGE;
            Metronic.unblockUI('#jobDetail');
            if(d.success){
                operateResponseList(d, op)
            }
        })
    }
    //______________________________________启动回切__________________________________
    /**
     * 启动回切
     */
    var startFallBackJob = function () {
        startFailbackAjax();
    }

    let startFailbackAjax = function(){
        if ($(this).find('a').hasClass('disablebtn')) {
            return true;
        }
        //任务阶段处于接管中、是否配置回切
        let data = {};
        data.job_uuids = [];
        data.job_uuids.push(taskUuid);
        data.module = CONF.MODULE_TYPE.DB_CDP;
        let param = {'job_uuids': data.job_uuids};
        getUserPassword();
        let initErrorFlag = false;
        bootbox.prompt({
            title: LANG.UI_DB_CDP_DETAILS_START_FAILBACK_TIPS,
            inputType:'password',
            callback: debounce(function (result) {
                if(result == null) return;
                if(hex_md5(result)==_UserPassword){
                    userIsVerify = true;
                    pAjaxRequest(param, '/api/v1/jobs/start_failback', 'POST', function (d) {
                        if (!d.success) {
                            // 未配置回切
                            // 回切备机
                            taskSwitchbackConf();
                        }else{
                            if (!failbackFlag) {
                                var op = LANG.UI_DB_CDP_DETAILS_SEND_START_SWITCHBACK_TASK_MESSAGE;
                                if (operateResponseList(d, op)) {
                                }
                            }
                        }
                    });
                    $(this).modal('hide');
                }else {
                    $('.bootbox-input').css('border-color', "#a94442");
                    if (!initErrorFlag) {
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                        return false;
                    }
                    return false;
                }
            },300,false)
        });
    }
    /**
     * 获配置或修改同步任务回切相关配置View
     */
    var taskSwitchbackConf = function () {
        // 获取实例树（回切源）
        initInstanceTree();
        isInitFailbck = false;
        Metronic.blockUI({target: '#taskFailbackupModal', animate: true});
        pAjaxRequest({}, '/api/v1/dbcdp/jobs/' + taskUuid + '/failback', 'GET', function (d) {
            Metronic.unblockUI('#taskFailbackupModal');
            var data = d.data;
            $('#taskFailbackupModal').modal({'width': '1000px', 'height': '460px'});
            $('.failbackNetConf').hide();
            $('#failback_ip_map_list').empty();
            targettreeNode = {};
            var target_cluster_flag = data['target_cluster_flag'];
            var failback_mode = data['failback_mode'];
            var source_file_cache_alloc_space = data['source_file_cache_alloc_space'];
            var file_cache_alloc_space = data['file_cache_alloc_space'];
            var file_cache_storage_path = data['file_cache_storage_path'];
            var transport_encrypt_flag = data['transport_encrypt_flag'];
            var transport_encrypt_method = data['transport_encrypt_method'];
            var transport_compress_method = data['transport_compress_method'];
            var transport_compress_flag = data['transport_compress_flag'];
            var transport_exp_thread_num = data['exp_max_process_num'];
            var transport_imp_thread_num = data['imp_max_process_num'];
            var takeover_business_ip_map = {};
            triggeredElementId = 'faiback_network_conf';
            if(data['failback_business_ip_map']){
                takeover_business_ip_map = data['failback_business_ip_map']['takeover_business_ip_map'];
            }
            var service_failback_mode = data['service_failback_mode'];
            var service_failback_start_time = data['service_failback_start_time'];
            var service_failback_end_time = data['service_failback_end_time'];
            $('.takeoverNetConf').hide();
            //无论回切目标是集群还是单节点，只要在创建同步任务的时候打开服务IP漂移开关
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
                    if(!takeoverNetConfFlag){
                        takeoverNetConfFlag = true;
                        _takeoverIpMapListView.push(list);
                        allIpMapInfoList.push(list);
                        ipMapInfoList.push(list);
                    }
                })
                hostGatewayConfDes();
            }
            // 回切目标机的存储
            let agent_info = data.agent_info;
            if (target_cluster_flag) {
                failback_object.target_cluster_flag = true;
            } else {
                failback_object.target_cluster_flag = false;
            }
            // 回切目标机
            parseFailBackInfo(agent_info);

            //回切策略
            if(service_failback_mode){
                var failBackStrategySelect = document.getElementById('failBackStrategy_select');
                $('.manualStrategyTypediv').hide();
                $('.failBackStrategyTimediv').hide();
                for( var i =0;i<failBackStrategySelect.options.length;i++){
                    if(failBackStrategySelect.options[i].value == service_failback_mode){
                        failBackStrategySelect.options[i].selected = true;
                        if(parseInt(service_failback_mode) == CONF.FLAG.SET){
                            // 自动
                            $('.manualStrategyTypediv').show();
                            $('.failBackStrategyTimediv').show();
                            // 触发自动回切阈值
                            $('.free_time_interval_div').spinner("value",data.service_failback_auto_trigger_threshold);
                            //回切策略 时间选择
                            if(service_failback_start_time != '0000-00-00 00:00:00' && service_failback_end_time != '0000-00-00 00:00:00'){
                                // 时间段
                                $('.daterangepickerdiv').show();
                                var dateFormat = 'YYYY-MM-DD HH:mm:ss';
                                var newStartDate = moment($.trim(service_failback_start_time), dateFormat).format(dateFormat);
                                var newEndDate = moment($.trim(service_failback_end_time), dateFormat).format(dateFormat);
                                $('#failback_strategy_time_picker').val(newStartDate + ' - ' + newEndDate);
                                $('.any_time_toggle').html(LANG.UI_DB_CDP_DETAILS_SWITCH_ANY_TIME);
                                failbackTimeRangeFlag = false;
                            }else{
                                $('.daterangepickerdiv').hide();
                                $('.any_time_div').show();
                                $('.any_time_toggle').html(LANG.UI_DB_CDP_DETAILS_SWITCH_TIME_RANGE);
                                failbackTimeRangeFlag = true;
                            }
                        }
                    }
                }
            }
            // 已经配置回切
            if (agent_info.length != 0) {
                if (failback_mode) {
                    getDataTypeInfo(failback_mode);  //数据类型
                }
                // 源文件缓存大小
                if(source_file_cache_alloc_space == '-1'){
                    $('#failback_cache_conf .source_custom_cache_size_div').hide();
                    $('#failback_cache_conf .source_cache_size_unlimited_div').show();
                    // 使用原生 JavaScript 的 innerHTML 属性
                    document.querySelector('button[data-value="source_cache_button"]').innerHTML = LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM;
                    sourceToggleLimitModeFlag = true;
                }else{
                    let failback_source_file_cache_alloc_space = source_file_cache_alloc_space/1024/1024/1024;
                    const source_Select = document.querySelector('select[name="source_file_cache_failback_unit"]');
                    if(failback_source_file_cache_alloc_space >= 1024){
                        failback_source_file_cache_alloc_space = source_file_cache_alloc_space/1024/1024/1024/1024;
                        source_Select.value = '2';
                    }else{
                        source_Select.value = '1';
                    }
                    $('.source_failbackfilecachediv').spinner('value', failback_source_file_cache_alloc_space);
                    sourceToggleLimitModeFlag = false;
                }
                // 备机文件缓存大小
                if(file_cache_alloc_space == '-1'){
                    $('#failback_cache_conf .backup_custom_cache_size_div').hide();
                    $('#failback_cache_conf .backup_cache_size_unlimited_div').show();
                    // 使用原生 JavaScript 的 innerHTML 属性
                    document.querySelector('button[data-value="backup_cache_button"]').innerHTML = LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM;
                    backupToggleLimitModeFlag = true;
                }else{
                    let failback_backup_file_cache_alloc_space = file_cache_alloc_space/1024/1024/1024;
                    const backup_Select = document.querySelector('select[name="backup_file_cache_failback_unit"]');
                    if(failback_backup_file_cache_alloc_space >= 1024){
                        failback_backup_file_cache_alloc_space = file_cache_alloc_space/1024/1024/1024/1024;
                        backup_Select.value = '2';
                    }else{
                        backup_Select.value = '1';
                    }
                    $('.failbackfilecachediv').spinner('value', failback_backup_file_cache_alloc_space);
                    backupToggleLimitModeFlag = false;
                }
                //文件缓存路径
                if (file_cache_storage_path) {
                    $('#file_cache_path').val(file_cache_storage_path);
                }
                //传输线程个数
                if (transport_exp_thread_num) $('#exp_thread').val(transport_exp_thread_num);
                if (transport_imp_thread_num) $('#imp_thread').val(transport_imp_thread_num);
                //压缩传输
                if (transport_compress_flag) {
                    $('#failbackTranCompressSwitch').bootstrapSwitch('state', true);
                } else {
                    $('#failbackTranCompressSwitch').bootstrapSwitch('state', false);
                }
                //加密传输
                if (transport_encrypt_flag) {
                    $('#failbackTranEncryptSwitch').bootstrapSwitch('state', true);
                } else {
                    $('#failbackTranEncryptSwitch').bootstrapSwitch('state', false);
                }
                // 压缩等级
                getCompressMethod(transport_compress_method);
                // 加密传输方式
                getEncryptTransportMethod(transport_encrypt_method);
                // dbms接口等高级配置
                // 同步大对象等高级配置
                let extend_advanced_config = data.extend_advanced_config;
                $('#use_dbms_Switch').bootstrapSwitch('state', extend_advanced_config.use_dbms);
                $('#sync_big_object_Switch').bootstrapSwitch('state', extend_advanced_config.sync_big_object);
                $('#timed_gen_txn_Switch').bootstrapSwitch('state', extend_advanced_config.timed_gen_txn);
                $('#replay_ddl_Switch').bootstrapSwitch('state', extend_advanced_config.replay_ddl);
                $('#ignore_nonsupport_data_type_Switch').bootstrapSwitch('state', extend_advanced_config.ignore_nonsupport_data_type);
            }else{
                getFailbackDefaultConfig(data);
            }
        })
    }

    /**
     * 没有配置回切配置时，再打开是默认配置
     */
    var getFailbackDefaultConfig = function (data){
        // 填充复制任务时的配置
        let syn_object = data['syn_info'];
        // 填充数据表空间存储目录
        $('.customDirectory_div').hide();
        let failback_app_tablespace_dir = data['failback_app_tablespace_dir'];
        if(!!failback_app_tablespace_dir){
            // 自定义
            $('#tablespaceDir option[value="1"]').prop('selected', true);
            $('.customDirectory_div').show();
            $('#customDirectory').val(failback_app_tablespace_dir);
        }else{
            $('#tablespaceDir option[value="0"]').prop('selected', true);
        }
        // 回切策略
        $('#failBackStrategy_select option[value="3"]').prop('selected', true);
        // 源机文件缓存大小
        if(syn_object.source_file_cache_alloc_space == '-1'){
            $('#failback_cache_conf .source_custom_cache_size_div').hide();
            $('#failback_cache_conf .source_cache_size_unlimited_div').show();
            // 使用原生 JavaScript 的 innerHTML 属性
            document.querySelector('#failback_cache_conf button[data-value="source_cache_button"]').innerHTML = LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM;
            sourceToggleLimitModeFlag_failback = true;
        }else{
            let source_file_cache_alloc_space = syn_object.source_file_cache_alloc_space/1024/1024/1024;
            let source_Select = document.querySelector('select[name="source_file_cache_failback_unit"]');
            if(source_file_cache_alloc_space >= 1024){
                source_file_cache_alloc_space = syn_object.source_file_cache_alloc_space/1024/1024/1024/1024;
                source_Select.value = '2';
            }else{
                source_Select.value = '1';
            }
            $('#failback_cache_conf .source_fileCacheSizeDiv').spinner('value', source_file_cache_alloc_space);
            sourceToggleLimitModeFlag_failback = false;
        }
        // 备机文件缓存大小
        if(syn_object.file_cache_alloc_space == '-1'){
            $('#failback_cache_conf .backup_custom_cache_size_div').hide();
            $('#failback_cache_conf .backup_cache_size_unlimited_div').show();
            // 使用原生 JavaScript 的 innerHTML 属性
            document.querySelector('#failback_cache_conf button[data-value="backup_cache_button"]').innerHTML = LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM;
            backupToggleLimitModeFlag_failback = true;
        }else{
            let backup_file_cache_alloc_space = syn_object.file_cache_alloc_space/1024/1024/1024;
            let backup_Select = document.querySelector('#failback_cache_conf select[name="backup_file_cache_failback_unit"]');
            if(backup_file_cache_alloc_space >= 1024){
                backup_file_cache_alloc_space = syn_object.file_cache_alloc_space/1024/1024/1024/1024;
                backup_Select.value = '2';
            }else{
                backup_Select.value = '1';
            }
            $('#failback_cache_conf .source_fileCacheSizeDiv').spinner('value', backup_file_cache_alloc_space);
            backupToggleLimitModeFlag_failback = false;
        }



        // 文件缓存路径
        $('#file_cache_path').val(syn_object.file_cache_storage_path);
        $('#file_cache_path').attr('title', syn_object.file_cache_storage_path);
        // 压缩传输开关
        $('#failbackTranCompressSwitch').bootstrapSwitch('state', false);
        $('#compresstransferselect option[value="1"]').prop('selected', true);
        // 加密传输
        $('#failbackTranEncryptSwitch').bootstrapSwitch('state', false);
        $('#encrypttransfermethodselect option[value="1"]').prop('selected', true);
        // 线程传输个数
        $('.exp_thread_div').spinner('value',syn_object.exp_max_process_num);
        $('.imp_thread_div').spinner('value',syn_object.imp_max_process_num);
    }

    /**
     * 文件缓存路径-切换自定义按钮
     */
    var fileCachePathChange = function () {
        //自定义
        $('#customFileCachePath').off().click(function () {
            $('#defaultFileCachePath').show();
            $('#customFileCachePath').hide();
            $('#file_cache_path').removeAttr("readonly");
            $('#file_cache_path').val("");
        });
        //默认
        $('#defaultFileCachePath').off().click(function () {
            $('#defaultFileCachePath').hide();
            $('#customFileCachePath').show();
            $('#file_cache_path').attr("readonly", "readonly");
            $('#file_cache_path').val(default_target_file_cache_path);
        });
    }

    /**
     * 获取存储的数据类型
     */
    var getDataTypeInfo = function (failback_mode) {
        var selectdataTypeconf = $('#selectdataTypeconf');
        for (var i = 0; i < selectdataTypeconf.find('option').length; i++) {
            if (selectdataTypeconf.find('option')[i].value == failback_mode) {
                selectdataTypeconf.find('option')[i].selected = true;
            }
        }
    }

    /**
     * 压缩等级
     */
    var getCompressMethod = function (transport_compress_method) {
        var selectcompressmethodconf = $('#compresstransferselect');
        for (var i = 0; i < selectcompressmethodconf.find('option').length; i++) {
            if (selectcompressmethodconf.find('option')[i].value == transport_compress_method) {
                selectcompressmethodconf.find('option')[i].selected = true;
            }
        }
    }

    /**
     * 加密传输方式
     */
    var getEncryptTransportMethod = function (transport_encrypt_method) {
        var selecttransfermethodconf = $('#encrypttransfermethodselect');
        for (var i = 0; i < selecttransfermethodconf.find('option').length; i++) {
            if (selecttransfermethodconf.find('option')[i].value == transport_encrypt_method) {
                selecttransfermethodconf.find('option')[i].selected = true;
            }
        }
    }

    /**
     * @function:解析可供回切的目标主机及应用信息等
     */
    var parseFailBackInfo = function (agent_info) {
        Metronic.blockUI({target: '#taskFailbackupModal', animate: true});
        let param = {db_type: CONF.DB_TYPE.ORACLE};
        pAjaxRequest(param, '/api/v1/db/instances', 'GET', res => {
            Metronic.unblockUI('#taskFailbackupModal');
            let rows = res.data.rows;
            let failbackAgentArr = [];
            let agent_uuid = '';
            let cluster_uuid = '';
            if(agent_info.length != 0){
                agent_uuid = agent_info[0].agent_uuid
                cluster_uuid = agent_info[0].app_info.cluster_uuid;
            }
            for(row of rows){
                let clusterFlag = row.cluster_info.cluster_flag;
                if(!clusterFlag){
                    if(row.db_cdp_backup_info.length == 0 && row.agent_info.online_flag ||(row.agent_info.agent_uuid == backupTargetInfo.source_agent_uuid) || (row.agent_info.agent_uuid == agent_uuid)){
                        // 无任务、在线、可以是源机
                        failbackAgentArr.push(row);
                    }
                }else{
                    if(row.db_cdp_backup_info.length == 0 && row.agent_info.online_flag || (row.cluster_info.cluster_uuid == backupTargetInfo.source_agent_cluster_uuid) || (row.cluster_info.cluster_uuid == cluster_uuid)){
                        // 集群(不能是和源相同的集群且无任务)
                        failbackAgentArr.push(row);
                    }
                }
            }
            if(failbackAgentArr.length == 0){
                UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_SELECT_DATABASE_MESSAGE);
                return false;
            }
            $('.dbMapDetaildiv').hide();
            $('.synNodediv').hide();
            setOracleTargetTree(failbackAgentArr,'#target_agent_tree',checkOraclesStandbyNode,agent_info);
        });
    }

    /**
     * 实例树(回切源)
     */
    const initInstanceTree = function () {
        let param = {db_type: CONF.DB_TYPE.ORACLE};
        // 获取实例列表
        pAjaxRequest(param, '/api/v1/db/instances', 'GET', res => {
            let rows = [];
            if(backupTargetInfo.target_agent_cluster_flag){
                // 集群
                rows = res.data.rows.filter(item => item.cluster_info.cluster_uuid  == backupTargetInfo.target_agent_cluster_uuid);
            }else{
                // 单机
                rows = res.data.rows.filter(item => item.app_uuid  == backupTargetInfo.target_agent_app_uuid);
            }
            setOracleBackupTree(rows,'','');
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
        // 分组下的所有节点
        let nodeSelected = zTreeAgent.getNodes();
        sourcetreeNode = nodeSelected[0].children[0];
        zTreeAgent.checkNode(nodeSelected[0].children[0], true, true, true);
    }

    /**
     * 构建目标树
     * @param instanceList
     * @param id
     * @param checkedFn
     */
    const setOracleTargetTree = (instanceList,id,checkedFn,agent_info) => {
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
        // 配置了回切
        if(agent_info.length !=0){
            // 分组下的所有节点
            let nodeSelected = zTreetTargetAgent.getNodes();
            // 区分回切目标是否是集群
            if(agent_info[0].app_info.cluster_flag){
                // 集群
                for(let i=0;i<nodeSelected.length;i++){
                    // 如果配置了回切
                    if(agent_info[0].app_info.cluster_uuid == nodeSelected[i].children[0].cluster_uuid){
                        zTreetTargetAgent.checkNode(nodeSelected[i].children[0], true, true, true);
                    }
                }
            }else{
                //单
                for(let i=0;i<nodeSelected.length;i++){
                    // 如果配置了回切
                    for(let j =0;j<nodeSelected[i].children.length;j++){
                        if(agent_info[0].agent_uuid == nodeSelected[i].children[j].agent_uuid){
                            zTreetTargetAgent.checkNode(nodeSelected[i].children[j], true, true, true);
                        }
                    }
                }
            }

        }
    };

    /**
     * 选择目标机节点
     */
    const checkOraclesStandbyNode = (ev, treeId, treeNode) =>{
        $('.dbMapDetaildiv').show();
        $('.synNodediv').show();
        isIpShow = false;
        targettreeNode = treeNode;
        // 单选节点
        let allNodes = zTreetTargetAgent.getCheckedNodes(true);
        for(let i = 0; i < allNodes.length; i++){
            zTreetTargetAgent.checkNode(allNodes[i], false, false, false);
        }
        zTreetTargetAgent.checkNode(treeNode, true, false, false);
        // 显示网卡配置
        if(switchIpFlag){
            $('.failbackNetConf').show();
        }
        if (treeNode.eventtype === 'cluster') {
            // 显示集群
            failbackConfigParams.failback_object.target_cluster_flag = true;
            failback_object.target_cluster_flag = true;
            addInstanceMapList(treeNode);
            initInstanceUserTree(sourcetreeNode,true);
            initInstanceUserTree(treeNode,false);
            // 遍历集群下的单节点
            for(let node of treeNode.children){
                if(node.online_flag){
                    // 在线集群下的单节点
                    failbackConfigParams.failback_object.target_app_uuid = node.app_uuid;
                    targetAgentAppInfo.app_type = node.app_type;
                    targetAgentAppInfo.app_version = node.app_version;
                    syncSourceNodeFlag = node.cluster_uuid == backupInfo.source_agent_cluster_uuid ? true : false;
                    loadClusterNetworkInfo(node.agent_uuid,true);
                    break;
                }
            }
        } else if (treeNode.eventtype === 'instance' && treeNode.pId ==='standalone') {
            failbackConfigParams.failback_object.target_cluster_flag = false;
            failback_object.target_cluster_flag = false;
            syncSourceNodeFlag = treeNode.agent_uuid == backupInfo.source_agent_uuid ? true : false;
            // 消息结构
            addInstanceMapList(treeNode);
            initInstanceUserTree(sourcetreeNode,true);
            initInstanceUserTree(treeNode,false);
            loadStandbyNetworkInfo(treeNode.agent_uuid);
            failbackConfigParams.failback_object.target_app_uuid = treeNode.app_uuid;
            failbackConfigParams.failback_object.target_host_uuid = treeNode.agent_uuid;
            failback_object.target_host_uuid = treeNode.agent_uuid;
            targetAgentAppInfo.app_type = treeNode.app_type;
            targetAgentAppInfo.app_version = treeNode.app_version;
        }
        addDataSynNode(treeNode);
        contrastApp(sourceAgentAppInfo, targetAgentAppInfo);
        initDataType();
        getFailbackNet();
    }

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
                '<span class="font-green-seagreen">' + LANG.UI_PUBLIC_HOST +':' + sourcetreeNode.name + '</span>' +
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
                `<option value="${targettreeNode.agent_uuid}">${targettreeNode.title}</option>`
            );
        }else{
            for(const node of targettreeNode.children){
                slaveNodeEle.append(
                    `<option value="${node.agent_uuid}">${node.name}</option>`
                );
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
            if (dbcdpBackupInfo.length != 0) {  // 存在备份任务
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
            sourceInstanceUserTree[selectedBackupUuid] = $.fn.zTree.init($('#dbsourceAgentTree_' + selectedBackupUuid), getInstanceUserTreeSetting(), nodes);
            // 默认展开
            let allNodes = sourceInstanceUserTree[selectedBackupUuid].getNodes();
            sourceInstanceUserTree[selectedBackupUuid].expandNode(allNodes[0], true, false, true, true);
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
        if(treeId == 'dbsourceAgentTree_'+selectedBackupUuid){
            // 源
            select_copy_users_flag = true;
        }
        // 加载实例用户信息
        Metronic.blockUI({target: '#taskFailbackupModal', animate: true});
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
            Metronic.unblockUI('#taskFailbackupModal');
            if (!res.success) {
                return;
            }
            let nodes = [];
            let users = [];
            default_target_file_cache_path = res.data.agent_install_dir;
            $('#file_cache_path').val(default_target_file_cache_path);
            $('#file_cache_path').attr('title',default_target_file_cache_path);
            if(treeNode.agent_uuid == backupInfo.target_agent_uuid){
                // 回切源
                failback_ip_scope_info = res.data.ip_scope_info;
            }
            let rows = res.data.rows;
            let select_copy_users = res.data.select_copy_users;
            for(const row of rows){
                if(row.is_cdb){
                    for(const user of select_copy_users){
                        if(row.con_id == user.con_id){
                            users.push(user);
                        }
                    }
                }else{
                    for(const user of select_copy_users){
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
        $('#selectdataTypeconf').empty();
        //如果选择的目标客户端是源机，数据类型为全量数据+增量数据、增量
        //如果选择的目标客户端是异机，数据类型为全量+增量
        var option = '<option value="1">' + LANG.UI_DB_CDP_RECOVER_DATA_TYPE3 + '</option>';
        $('#selectdataTypeconf').append(option);
    }


    /**
     * 修改任务
     */
    const editJob = function (){
        LOCATION('./content/dbcdp/dbcdp_edit.php?uuid=' + taskUuid);
    }

    /**
     * 禁用/启用自动接管配置
     */
    var autoTakeoverEnableAndDisableConf = function (){
        let enableFlag;
        if(allow_auto_takeover_flag){
            enableFlag = CONF.FLAG.UNSET;
        }else if(!allow_auto_takeover_flag){
            enableFlag = CONF.FLAG.SET;
        }
        let uuids = [];
        uuids.push(taskUuid);
        Metronic.blockUI({target: '#jobDetail', animate: true});
        pAjaxRequest({'job_uuid':uuids , 'enable_flag': enableFlag}, '/api/v1/dbcdp/switch_autotakeover', 'POST', (res)=>{
            Metronic.unblockUI('#jobDetail');
            operateResponseList(res);
        });
    }

    /**
     * 暂停状态修改延时重放时间和文件缓存大小
     */
    var editHighConfig = function (){
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            $('#pauseEditConfigModel').modal({'width': '850px'});
        }else{
            $('#pauseEditConfigModel').modal({'width': '700px'});
        }
        $('.delay_time_switch').bootstrapSwitch('state', delayTimeFlag);
        let times = secondsToTime(delay_load_time_value);
        $('.days').val(times.days);
        $('.hours').val(times.hours);
        $('.minutes').val(times.minutes);
        $('.seconds').val(times.seconds);
        $('#delay_time').parent('.input-group').on('click', '.input-group-btn', function(e){
            e.preventDefault();
            $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
        });
        $('.delay_time_switch').on('switchChange.bootstrapSwitch', function (event,state){
            $('#delay_time_div').toggle(state);
        });

        // 源机文件缓存大小
        if(source_fileCacheSize_value == '-1'){
            $('.source_custom_cache_size_div').hide();
            $('.source_cache_size_unlimited_div').show();
            // 使用原生 JavaScript 的 innerHTML 属性
            document.querySelector('button[data-value="source_cache_button"]').innerHTML = LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM;
            sourceToggleLimitModeFlag = true;
        }else{
            let source_file_cache_alloc_space = source_fileCacheSize_value/1024/1024/1024;
            let source_Select = document.querySelector('select[name="source_file_cache_unit"]');
            if(source_file_cache_alloc_space >= 1024){
                source_file_cache_alloc_space = source_fileCacheSize_value/1024/1024/1024/1024;
                source_Select.value = '2';
            }else{
                source_Select.value = '1';
            }
            $('.source_fileCacheSizeDiv').spinner('value', source_file_cache_alloc_space);
            sourceToggleLimitModeFlag = false;
        }
        // 备机文件缓存大小
        if(backup_fileCacheSize_value == '-1'){
            $('.backup_custom_cache_size_div').hide();
            $('.backup_cache_size_unlimited_div').show();
            // 使用原生 JavaScript 的 innerHTML 属性
            document.querySelector('button[data-value="backup_cache_button"]').innerHTML = LANG.UI_DB_CDP_BACKUP_SWITCH_TO_CUSTOM;
            backupToggleLimitModeFlag = true;
        }else{
            let backup_file_cache_alloc_space = backup_fileCacheSize_value/1024/1024/1024;
            let backup_Select = document.querySelector('select[name="backup_file_cache_unit"]');
            if(backup_file_cache_alloc_space >= 1024){
                backup_file_cache_alloc_space = backup_fileCacheSize_value/1024/1024/1024/1024;
                backup_Select.value = '2';
            }else{
                backup_Select.value = '1';
            }
            $('.backup_fileCacheSizeDiv').spinner('value', backup_file_cache_alloc_space);
            backupToggleLimitModeFlag = false;
        }
        // 配置了自动接管则不可开启延迟重放
        if(autoTakeoverFlag){
            $('.delay_time_switch_div').hide();
            $('#delay_time_div').hide();
        }
        if(!delayTimeFlag){
            $('#delay_time_div').hide();
        }else{
            $('#delay_time_div').show();
        }
    }

    /**
     * 提交高级配置
     */
    var submitHighConfig = function (){
        let params = {};
        params.delay_load_time = 0;
        let delay_time_switch_status = $('.delay_time_switch').get(0).checked;
        if(!autoTakeoverFlag && delay_time_switch_status){
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
            params.delay_load_time = delayTime;
        }
        // 源机文件缓存大小 单位：字节
        if(!sourceToggleLimitModeFlag){
            let source_file_cache_unit = $('select[name=source_file_cache_unit] option:selected').val();
            params.source_file_cache_alloc_space = parseInt($('#source_fileCacheSize').val()) * Math.pow(1024,(parseInt(source_file_cache_unit)+2));
        }else{
            params.source_file_cache_alloc_space = -1;
        }
        // 备机文件缓存大小 单位：字节
        if (!backupToggleLimitModeFlag){
            let backup_file_cache_unit = $('select[name=backup_file_cache_unit] option:selected').val();
            params.file_cache_alloc_space = parseInt($('#backup_fileCacheSize').val()) * Math.pow(1024,(parseInt(backup_file_cache_unit)+2));
        }else{
            params.file_cache_alloc_space = -1;
        }
        params.auto_takeover_flag = autoTakeoverFlag;
        params.task_uuid = taskUuid;
        pAjaxRequest(params,'/api/v1/dbcdp/edit_high_config','PUT',function (res){
            $('#pauseEditConfigModel').modal('hide');
            operateResponseList(res);
        })
    }

    /**
     * 初始化流量
     */
    const initSpeed = () => {
        // 根据不同分辨率动态计算echart的高度和宽度
        let chartWidth = $('.portlet-charts__body__speedchart').width();
        let chartHeight = $('.portlet-charts__body__speedchart').height();
        $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
        // 基于准备好的dom，初始化echarts实例
        flowChart = echarts.init(document.getElementById('speedchart'));
        // 指定图表的配置项和数据
        let option = {
            tooltip: {
                trigger: 'axis',
                icon: "circle",
                formatter: function (params, ticket, callback) {
                    let tips = params[0].name
                    for (let i = 0; i < params.length; i++) {
                        var pvalue = params[i].data;
                        pvalue = storageCalculateSize(pvalue)+'/s';
                        tips += '<br/>' + ' <span style="display:inline-block;margin-right:5px;width:10px;height:10px;border-radius: 50%!important;background-color:'+ params[i].color +';"></span>' +'  '+ params[i].seriesName +'  '+ pvalue;
                    }
                    return tips;
                }
            },
            grid: {
                top: 15,
                left: 5,
                right: 5,
                bottom: 5,
                containLabel: true,
                show: false,
                borderWidth: 0,
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                splitLine: {
                    show: false
                },
                axisLabel: {
                    show: true,
                    interval: 12,
                    fontSize: 12,
                },
                axisLine: {lineStyle: {color: '#8e8e8e'}},
                data: []
            },
            yAxis: {
                type: 'value',
                splitLine: {
                    show: true
                },
                axisLine: {lineStyle: {color: '#999999'}},
                axisLabel: {
                    fontSize: 12,
                    formatter: function (value, index) {
                        let arrayUnit = [LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_B, LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_KB, LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_MB, LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_GB, LANG.UI_DB_CDP_DETAILS_SIZE_TB, LANG.UI_DB_CDP_DETAILS_SIZE_PB, LANG.UI_DB_CDP_DETAILS_SIZE_EB, LANG.UI_DB_CDP_DETAILS_SIZE_ZB, LANG.UI_DB_CDP_DETAILS_SIZE_YB, LANG.UI_DB_CDP_DETAILS_SIZE_BB, LANG.UI_DB_CDP_DETAILS_SIZE_NB, LANG.UI_DB_CDP_DETAILS_SIZE_DB];
                        let j = 0 ;
                        while(value >= 1024 ){
                            value = value / 1024;
                            j++;
                        }
                        if(j >= 1){
                            return value.toFixed(1) + arrayUnit[j];
                        }else{
                            return value + arrayUnit[j];
                        }
                    }
                },
            },
            series: [
                // 曲线1 全量导入数据
                {
                    name: LANG.UI_DB_CDP_FULL_SYNC,
                    type: 'line',
                    showSymbol: false,
                    hoverAnimation: false,
                    smoothMonotone: 'x',
                    animation: false,
                    smooth: true,
                    itemStyle: {
                        normal: {
                            lineStyle: {
                                width: 1,
                                color: '#44b6ae'
                            }
                        }
                    },
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0,
                                color: '#44b6ae'
                            }, {
                                offset: 1,
                                color: '#fff'
                            }])
                        }
                    },
                    data: []
                },
                // 曲线2 日志同步
                {
                    name: LANG.UI_DB_CDP_LOG_SYNC,
                    type: 'line',
                    showSymbol: false,
                    hoverAnimation: false,
                    smoothMonotone: 'x',
                    animation: false,
                    smooth: true,
                    itemStyle: {
                        normal: {
                            lineStyle: {
                                width: 1,
                                color: '#4486B6'
                            }
                        }
                    },
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0,
                                color: '#4486B6'
                            }, {
                                offset: 1,
                                color: '#fff'
                            }])
                        }
                    },
                    data: []
                },
                // 曲线3 日志抓取数据
                {
                    name: LANG.UI_DB_CDP_SYNC_REDO_LOG,
                    type: 'line',
                    showSymbol: false,
                    hoverAnimation: false,
                    smoothMonotone: 'x',
                    animation: false,
                    smooth: true,
                    itemStyle: {
                        normal: {
                            lineStyle: {
                                width: 1,
                                color: '#ddaa01'
                            }
                        }
                    },
                    areaStyle: {
                        normal: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0,
                                color: '#ddaa01'
                            }, {
                                offset: 1,
                                color: '#fff'
                            }])
                        }
                    },
                    data: []
                },

            ],
            color: ['#44b6ae','#4486B6','#ddaa01']
        };
        if (flowChart != undefined) {
            flowChart.clear(); //清空实例 否则会与上一个合并 数据残留
        }
        let updateInterval = 5000;
        let data1 = [], data2 = [], data3 = [], nowTime = [];

        let parseNum = function (num) {
            num = parseInt(num);
            num = num >= 10 ? num : "0" + num;
            return num;
        }

        let getShowTime = function (timeStamp) {
            let myDate = new Date(parseInt(timeStamp));
            let date = myDate.toLocaleDateString();
            let hours = myDate.getHours();
            let minutes = myDate.getMinutes();
            let seconds = myDate.getSeconds();
            return parseNum(hours) + ":" + parseNum(minutes) + ":" + parseNum(seconds);
        }

        /**
         * 初始化任务进度曲线图
         * @param d
         */
        let initTaskSpeed =  (d) => {
            if (initChartFlag) return; //初始化了就直接返回
            let serverTime = d.timestamp * 1000;
            for (let i = 100; i > 0; i--) {
                data1.push(0);  //push100个0
                data2.push(0);
                data3.push(0);
                nowTime.push(getShowTime(serverTime - i * 3000));
            }
            option.series[0].data = data1;   //曲线1y轴数值
            option.series[1].data = data2;   //曲线2y轴数值
            option.series[2].data = data3;   //曲线3y轴数值
            option.xAxis.data = nowTime;     //x轴数值
            flowChart.setOption(option);

            initChartFlag = true;
        }
        // 需要传给接口的参数
        taskUuid = $("#task_uuid").val();

        function update() {
            if (timerTask.dbcdpJobDetails_speed) {
                clearTimeout(timerTask.dbcdpJobDetails_speed);
            }
            pAjaxRequest({}, '/api/v1/dbcdp/jobs/' + taskUuid + '/flow', 'GET', (d) => {
                // FULL_SYNC:20，读transmission_speed（显示为全量导入数据）LOG_SYNC:21，读transmission_speed（显示为日志同步数据）
                let dataNow = d.data;
                initTaskSpeed(dataNow);
                let transmission_speed_value = parseInt(dataNow.transmission_speed_value);
                let sync_redo_log_speed_value = parseInt(dataNow.sync_redo_log_speed_value);
                // 全量导入数据
                data1.shift();  //去除数组第一个数字  data：y轴数值
                if(taskStage == CONF.DBCDP_TASK_RUNNING_STAGE.FULL_SYNC){
                    data1.push(transmission_speed_value);
                }else{
                    data1.push(0);
                }
                // 日志同步数据
                data2.shift();  //去除数组第一个数字  data：y轴数值
                if(taskStage == CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC){
                    data2.push(transmission_speed_value);
                }else{
                    data2.push(0);
                }
                // 日志抓取数据
                data3.shift();  //去除数组第一个数字  data：y轴数值
                if (isNaN(sync_redo_log_speed_value)) {
                    sync_redo_log_speed_value = 0;
                }
                data3.push(sync_redo_log_speed_value);
                option.series[0].data = data1;
                option.series[1].data = data2;
                option.series[2].data = data3;

                nowTime.shift();
                let currentTime = getCurrentTime();  //获取到当前的时间点
                nowTime.push(currentTime);  //
                option.xAxis.data = nowTime;  //x轴数值
                flowChart.setOption(option);
            });
            timerTask.dbcdpJobDetails_speed = setTimeout(update, updateInterval);
        }

        update();
        window.onresize = function () {
            flowChart.resize();
        }
    }

    /**
     * 获取当前时间
     */
    const getCurrentTime =  () => {
        var now = new Date(); // 创建Date对象
        var hour = now.getHours(); // 获取小时（24小时制）
        var minute = now.getMinutes(); // 获取分钟
        var second = now.getSeconds(); // 获取秒数

        // 在单个数字前加0以保持两位数格式
        if (hour < 10) {
            hour = "0" + hour;
        }
        if (minute < 10) {
            minute = "0" + minute;
        }
        if (second < 10) {
            second = "0" + second;
        }

        return hour + ":" + minute + ":" + second;
    }


    /**
     * 初始化详情流量图和数据流向tab
     */
    const initEchart = () => {
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var tab = e.target;
            if(tab.hash == "#tab_chart"){
                $('#tab_chart').show();
                $('#tab_task_map').hide();
                // 根据不同分辨率动态计算echart的高度和宽度
                var chartWidth = $('.portlet-charts__body__speedchart').width();
                var chartHeight = $('.portlet-charts__body__speedchart').height();
                $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                flowChart.resize();
            }else if(tab.hash == "#tab_task_map"){
                $('#tab_chart').hide();
                $('#tab_task_map').show();
            }
        });
    }

    /**
     * echarts 图表大小自适应
     */
    const watchEchartSizeChange = function () {
        window.onresize = function () {
            var chartWidth = $('.portlet-charts__body__speedchart').width();
            var chartHeight = $('.portlet-charts__body__speedchart').height();
            $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            flowChart.resize();
        }

        //  监听左侧菜单导航伸缩/展开触发的echart-resize事件
        $(window).on('echart-resize', function () {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                let chartWidth = $('.portlet-charts__body__speedchart').width();
                let chartHeight = $('.portlet-charts__body__speedchart').height();

                $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                flowChart.resize();
            }, 300);
        });
    }

    /**
     * 初始化日志表格
     */
    const initLogGrid =  () => {
        $('#runninglog').runningLog({
            job_uuid: $('#task_uuid').val()
        });
    }

    function addIsSelectFlag(arrA, arrB) {
        // 从arrB中收集所有匹配的用户名
        const matchingUsers = new Set();
        arrB.forEach(pdb => {
            pdb.user_list.forEach(user => {
                matchingUsers.add(user.name);
            });
        });

        // 遍历arrA添加is_select标志
        return arrA.map(item => {
            const isSelect = matchingUsers.has(item.name);
            return {
                ...item,
                is_select: isSelect
            };
        });
    }

    /**
     * 初始化监控数据
     */
    const initmonitorData =  () => {
        var updateInterval = 5000;
        var initFlag = false;
        var init = function () {
            if (timerTask.dbCdpJobDetailsMonitorGrid) {
                clearTimeout(timerTask.dbCdpJobDetailsMonitorGrid);
            }
            if (0 == $('#task_uuid').size()) {
                clearTimeout(timerTask.dbCdpJobDetailsMonitorGrid);
                return;
            }
            var options = {
                vin_url: '/api/v1/dbcdp/jobs/' + taskUuid + '/monitor_data',
                vin_method: 'get',
                detailView: true,
                pagination: false,
                detailFormatter: function (index, row, $detail) {
                    // 树的数据
                    var content = '<div id="tree' + index + '" class="ztree"></p></div>';
                    // 返回详细视图内容字符串
                    return content;
                },
                onExpandRow: function (index, row, $detail) {
                    var monitorDetails = JSON.parse(row.monitor_application_details);
                    var treeId = "tree" + index;
                    var databaseNode = {
                        name:monitorDetails.database_name,
                        iconSkin: 'iconSkin-dbcdp-database', // 类名
                        children:[],
                        chkDisabled:true,
                        open:true
                    };
                    var database_list = monitorDetails.database_list.filter((item)=>item.open_mode == 1);
                    database_list.forEach((database_list)=>{
                        if(database_list.con_id == 0){
                            // 容器数据库
                            var arr =  addIsSelectFlag(database_list.user_list,select_copy_users_value);
                            // 遍历用户列表
                            arr.forEach(user => {
                                var userNode = {
                                    name: user.name,
                                    iconSkin: 'iconSkin-dbcdp-user', // 类名
                                    checked: user.is_select,
                                    noCheck:false,
                                    chkDisabled:true,
                                    open:true
                                };
                                databaseNode.children.push(userNode);
                            });
                        }else{
                            var pdbNode = {
                                name: database_list.pdb_name,
                                iconSkin: 'iconSkin-dbcdp-database', // 类名
                                children:[],
                                chkDisabled:true,
                                open:true
                            };
                            databaseNode.children.push(pdbNode);
                            // 遍历用户列表
                            // 处理函数
                            var arr =  addIsSelectFlag(database_list.user_list,select_copy_users_value);
                            arr.forEach(user => {
                                var userNode = {
                                    name: user.name,
                                    iconSkin: 'iconSkin-dbcdp-user', // 类名
                                    checked: user.is_select,
                                    noCheck:false,
                                    chkDisabled:true,
                                    open:true
                                };
                                pdbNode.children.push(userNode);
                            });
                        }
                    })
                    var setting = {
                        check: {
                            enable: true,
                            nocheckInherit: false,
                        },
                        data: {
                            simpleData: {
                                enable: true
                            }
                        }
                    };

                    // 初始化 ZTree
                    let zTreeObj = $.fn.zTree.init($("#tree" + index), setting, [databaseNode]);
                    zTreeObj.expandAll(true);  // 初始化后立即展开所有节点
                },
                onRefresh: function (params) {
                    $("#monitorTable").bootstrapTable('hideLoading');
                },
                onPostBody:function (params){
                    $("#monitorTable").bootstrapTable('expandAllRows');
                },
                columns: [
                    {
                        field: 'host_info',
                        title: LANG.UI_JOB_DETAIL_SOURCE_DB_INSTANCE,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'master_node',
                        title: LANG.UI_DB_CDP_SOURCE_DATA_NODE,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'application_type',
                        title: LANG.UI_DB_CDP_BACKUP_APP_TYPE,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'monitor_application',
                        title: LANG.UI_VOL_CDP_JOB_DETAILS_MONITOR_APP,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'processing_data_size',
                        title: LANG.UI_DB_SIZE,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'total_object_transport_size',
                        title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'processing_speed',
                        title: LANG.UI_DB_CDP_DETAILS_PROCESSING_SPEED,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'standby_info',
                        title: LANG.UI_JOB_DETAIL_TARGET_DB_INSTANCE,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'slave_node',
                        title: LANG.UI_DB_CDP_TARGET_DATA_NODE,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'app_tablespace_dir',
                        title: LANG.UI_DB_CDP_DETAILS_DATA_TABLE_SPACE_STORAGE_DIRECTORY,
                        sortable: false,
                        align: 'center',
                        formatter:(value,row)=>{
                            if(value != ''){
                                return value;
                            }else{
                                return LANG.UI_DB_CDP_DETAILS_DIRECTORY_OF_THE_SYSTEM_TABLE_SPACE;
                            }
                        }
                    },
                ],
            }
            if (!initFlag) {
                initFlag = true;
                $('#monitorTable').baseTableConfig().init(options);
            } else {
                $('#monitorTable').bootstrapTable('hideLoading');
                $('#monitorTable').bootstrapTable('refresh');
            }
            timerTask.dbCdpJobDetailsMonitorGrid = setTimeout(init, updateInterval);
        }
        init();
    }

    /**
     * 初始化历史任务表格
     */
    const initHistoryGrid = function () {
        var updateInterval = 10000;
        var initFlag = false;
        var init = function () {
            if (timerTask.dbCdpJobDetailsHistoryGrid) {
                clearTimeout(timerTask.dbCdpJobDetailsHistoryGrid);
            }
            if (0 == $('#task_uuid').size()) {
                clearTimeout(timerTask.dbCdpJobDetailsHistoryGrid);
                return;
            }
            var options = {
                vin_url: '/api/v1/jobs/' + taskUuid + '/history',
                vin_method: 'get',
                detailView: true,
                detailFormatter: function (index, row, $detail) {
                    let html = '<table id = "historyDetailtab">';
                    var table = "";
                    if(row.detail.list.resource_limiting_node_config !== undefined){
                        html += `<tr>
                            <th width="25%">` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                            <th width="25%">` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                            <th width="35%">` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
                        </tr>`;

                        let resourceLimitingNodeConfig = row.detail.list.resource_limiting_node_config;
                        html += `<tr>
                        <td>` + LANG.UI_PUBLIC_ON + `</td>
                        <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                        <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                    </tr>`;
                        html += '</table>';
                        $detail.append(html);
                    }else{
                        var initSecondTableInside = function (d) {
                            table = initSecondTable(d);
                            $detail.append(table);
                        }
                        //任务状态、描述信息
                        pAjaxRequest({}, '/api/v1/dbcdp/jobs/'+ row.job_uuid + '/history/' + row.history_uuid + '/details', 'get', initSecondTableInside);
                    }
                },
                onRefresh: function (params) {
                    $("#historyTable").bootstrapTable('hideLoading');
                },
                sortName:'start_time',
                sortOrder:'desc',
                columns: [
                    {
                        field: 'num',
                        title: LANG.UI_PUBLIC_TABLE_ID,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'job_name',
                        title: LANG.UI_DB_CDP_DETAILS_TASK_NAME,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'job_type',
                        title: LANG.UI_TASK_AWS_JOB_TYPE,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'job_status',
                        title: LANG.UI_VISUAL_RESULT,
                        sortable: true,
                        align: 'center',
                        formatter: function (value, data, row) {
                            let html = '';
                            switch (value) {
                                case LANG.UI_PUBLIC_SUCCESS:
                                    html = '<div class="tag tag-success"><span>' + LANG.UI_PUBLIC_SUCCESS + '</span></div>';
                                    break;
                                case LANG.UI_DATACENTER_FAILURE:
                                    html = '<div class="tag tag-error"><span>' + LANG.UI_DATACENTER_FAILURE + '</span></div>';
                                    break;
                                case LANG.UI_DATACENTER_ZHONGZHI:
                                    html = '<div class="tag tag-primary"><span>' + LANG.UI_DATACENTER_ZHONGZHI + '</span></div>';
                                    break;
                                case LANG.UI_NODE_ABNORMAL:
                                    html = '<div class="tag tag-unnormal"><span>' + LANG.UI_NODE_ABNORMAL + '</span></div>';
                                    break;
                            }
                            return html;
                        },
                    },
                    {
                        field: 'speed_size',
                        title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                        sortable: false,
                        align: 'center',
                    },
                    {
                        field: 'write_size',
                        title: LANG.UI_PUBLIC_REAL_SIZE,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'start_time',
                        title: LANG.UI_PUBLIC_START_TIME,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'finish_time',
                        title: LANG.UI_PUBLIC_END_TIME,
                        sortable: true,
                        align: 'center',
                    },
                ],
            }

            if (!initFlag) {
                initFlag = true;
                $('#historyTable').baseTableConfig().init(options);
            }else {
                $('#historyTable').bootstrapTable('hideLoading');
                $('#historyTable').bootstrapTable('refresh');
            }
            timerTask.dbCdpJobDetailsHistoryGrid = setTimeout(init, updateInterval);
        }
        init();
    }

    const initSecondTable = function (datas) {
        var dataRows = datas.data;
        if (dataRows.length == 0) {
            return LANG.UI_SYSTEM_MONITOR_NULL_DATA;
        }
        var tableList = dataRows;
        var tableContent = '';
        $.each(tableList, function (index, value) {
            let currentTaskStatusValue = '';
            let selectedUser = '';
            switch (value.task_status) {
                case 0:
                    currentTaskStatusValue = LANG.UI_PUBLIC_UNKNOWN;
                    break;
                case 1:
                    currentTaskStatusValue = LANG.UI_PUBLIC_WAIT;
                    break;
                case 2:
                    currentTaskStatusValue = LANG.UI_PUBLIC_RUNNING;
                    break;
                case 3:
                    currentTaskStatusValue = LANG.UI_JOB_PAUSE;
                    break;
                case 4:
                    currentTaskStatusValue = LANG.UI_JOB_STOP;
                    break;
                case 5:
                    currentTaskStatusValue = LANG.UI_VISUAL_STOPPING;
                    break;
                case 6:
                    currentTaskStatusValue = LANG.UI_VISUAL_NETWORK_ERROR;
                    break;
                case 7:
                    currentTaskStatusValue = LANG.UI_DB_CDP_DETAILS_STATUS_TASK_EXCEPTION;
                    break;
                case 8:
                    currentTaskStatusValue = LANG.UI_PLATFORM_DES_ERROR;
                    break;
                case 11:
                    currentTaskStatusValue = LANG.UI_PUBLIC_PAUSING;
                    break;
                case 17:
                    currentTaskStatusValue = LANG.UI_PUBLIC_SUCCESS;
                    break;
            }
            let errorDes = value.error_code_des;
            if(value.error_code == 0){
                errorDes = '--';
            }
            for (const select_copy_users of value.select_copy_users) {
                if (select_copy_users.pdb_name !== '') {
                    selectedUser += select_copy_users.pdb_name + ':';
                }
                selectedUser += select_copy_users.user_list.map(user => user.name).join(', ');
                selectedUser += '<br>';
            }

            tableContent += "<tr>" +
                "<td class='pl0' style='width: 8%; word-break: break-all;white-space: normal;'>" + value.host_name + "</td>" +
                "<td class='pl0' style='width: 8%; word-break: break-all;white-space: normal;'>" + value.master_node + "</td>" +
                "<td class='pl0' style='width: 8%; word-break: break-all;white-space: normal;'>" + value.standby_name + "</td>" +
                "<td class='pl0' style='width: 8%; word-break: break-all;white-space: normal;'>" + value.slave_node + "</td>" +
                "<td class='pl0' style='width: 8%; word-break: break-all;white-space: normal;'>" + value.source_monitor_app + "</td>" +
                "<td class='pl0' style='width: 8%; word-break: break-all;white-space: normal;'>" + value.target_monitor_app + "</td>" +
                "<td class='pl0' style='width: 8%; word-break: break-all;white-space: normal;'>" + value.average_speed + 'B/S' + "</td>" +
                "<td class='pl0' style='width: 8%; word-break: break-all;white-space: normal;'>" + value.completed_size + "</td>" +
                "<td class='pl0' style='width: 23%; word-break: break-all;white-space: normal;'>" + selectedUser + "</td>" +
                "<td class='pl0' style='width: 5%; word-break: break-all;white-space: normal;'>" + currentTaskStatusValue + "</td>" +
                "<td class='pl0' style='width: 8% word-break: break-all;white-space: normal;'>" + errorDes + "</td>" +
                "</tr>";
        });

        //开始封装表格
        var detailsTable = "<table>" +
            "<tr>" +
            "<th>"+LANG.UI_JOB_HOST_INFO+"</th>" +
            "<th>"+LANG.UI_DB_CDP_BACKUP_SOURCE_NODE+"</th>" +
            "<th>"+LANG.UI_DB_CDP_DETAILS_STANDBY_INFO+"</th>" +
            "<th>"+LANG.UI_DB_CDP_BACKUP_TARGET_NODE+"</th>" +
            "<th>"+LANG.UI_DB_CDP_BACKUP_APP_TYPE+"</th>" +
            "<th>"+LANG.UI_VOL_CDP_JOB_DETAILS_MONITOR_APP+"</th>" +
            "<th>"+LANG.UI_JOB_TRANSFER_SPEED+"</th>" +
            "<th>"+LANG.UI_DB_CDP_DETAILS_PROCESS_DATA_SIZE+"</th>" +
            "<th>"+LANG.UI_DB_CDP_DETAILS_SELECTED_USER+"</th>" +
            "<th>"+LANG.UI_SEARCH_TASK_STATUS+"</th>" +
            "<th>"+LANG.UI_DB_CDP_DETAILS_ERROR_DESCRIPTION+"</th>" +
            "</tr>" + tableContent
        "</table>";
        return detailsTable;
    }

    /**
     * 获取当前任务的基本信息
     */
    const initBasicInfo =  ()=> {
        var updateInterval = 5000;
        var init = function () {
            if (timerTask.DbCdpJobDetailsSummayInfo) {
                clearTimeout(timerTask.DbCdpJobDetailsSummayInfo);
            }
            if (0 == $('#task_uuid').size()) {
                clearTimeout(timerTask.DbCdpJobDetailsSummayInfo);
                return;
            }
            pAjaxRequest({}, '/api/v1/jobs/' + taskUuid + '/info', 'get', function (d) {
                let data = d.data;
                setBasicInfo(data);
            },false);
            timerTask.DbCdpJobDetailsSummayInfo = setTimeout(init, updateInterval);
        }
        init();
    }

    /**
     * 设置基本信息
     * @param data 接口数据
     * @param timeoutID  定时器名称
     */
    const setBasicInfo =  (data, timeoutID) => {
        // 当前任务运行阶段 数字
        taskStage = data.job_stage;
        // 任务状态 数字
        taskStatus = data.job_status;
        // 任务类型 数字
        taskType = data.job_type;
        // 当前任务阶段值
        var taskStageValue = '';
        // 任务名
        taskName =  data.job_name
        // 存储节点ip
        nodeInfo = data.ip
        let complateSize = data.complate_size;
        let startTime = data.start_time;
        let intervalTime = data.interval_time;
        let speedLimitDes = data.speed_limit.des;
        let transportIp = data.transport_strategy.transport_ip;
        //任务阶段
        switch (taskStage) {
            case 0:
                taskStageValue = '--';
                break;
            case 1:
                taskStageValue = LANG.UI_PUBLIC_WAIT;
                break;
            case 10:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_DATA_DICTIONARY_EXPORT;
                break;
            case 11:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_DATA_DICTIONARY_IMPORT;
                break;
            case 20:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_FULL_SYNC;
                break;
            case 21:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_LOG_SYNC;
                break;
            case 22:
                taskStageValue = LANG.UI_DB_CDP_CONSTRAINT_IMPORT;
                break;
            case 30:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_IN_TAKEOVER_STARTING;
                break;
            case 31:
                taskStageValue = LANG.UI_PUBLIC_TAKEOVERING;
                break;
            case 40:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_FAILBACK_DICT_EXPORT;
                break;
            case 41:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_FAILBACK_DICT_IMPORT;
                break;
            case 50:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_FAILBACK_FULL_SYNC;
                break;
            case 51:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_FAILBACK_LOG_SYNC;
                break;
            case 52:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_SERVICE_FAILBACK;
                break;
            case 53:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_FAILBACK_SUCCESSED;
                break;
            case 54:
                taskStageValue = LANG.UI_DB_CDP_FAILBACK_CONSTRAINT_IMPORT;
                break;
            case 60:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_RESTORE_IN_RUNNING;
                break;
            case 61:
                taskStageValue = LANG.UI_DB_CDP_DETAILS_STAGE_RESTORE_RESTORE_SUCCESSED;
                break;
        }

        //当前任务类型-值
        var currentTaskTypeValue = '';
        switch (taskType) {
            case CONF.TASK_TYPE.CDP_DB_BACKUP:
                currentTaskTypeValue = LANG.UI_CM_CDP_REPLICATION;
                break;
            case CONF.TASK_TYPE.CDP_DB_RECOVERY:
                currentTaskTypeValue = LANG.UI_VISUAL_RECOVERY;
                break;
        }
        var currentTaskStatusValue = '';
        switch (taskStatus) {
            case 0:
                currentTaskStatusValue = LANG.UI_PUBLIC_UNKNOWN;
                break;
            case 1:
                currentTaskStatusValue = LANG.UI_PUBLIC_WAIT;
                break;
            case 2:
                currentTaskStatusValue = LANG.UI_PUBLIC_RUNNING;
                break;
            case 3:
                currentTaskStatusValue = LANG.UI_JOB_PAUSE;
                break;
            case 4:
                currentTaskStatusValue = LANG.UI_JOB_STOP;
                break;
            case 5:
                currentTaskStatusValue = LANG.UI_VISUAL_STOPPING;
                break;
            case 6:
                currentTaskStatusValue = LANG.UI_VISUAL_NETWORK_ERROR;
                break;
            case 7:
                currentTaskStatusValue = LANG.UI_DB_CDP_DETAILS_STATUS_TASK_EXCEPTION;
                break;
            case 8:
                currentTaskStatusValue = LANG.UI_PLATFORM_DES_ERROR;
                break;
            case 11:
                currentTaskStatusValue = LANG.UI_PUBLIC_PAUSING;
                break;
            case 12:
                currentTaskStatusValue = LANG.UI_VISUAL_STARTING;
                break;
            case 17:
                currentTaskStatusValue = LANG.UI_PUBLIC_SUCCESS;
                break;
        }
        $('#type').html('<span>' + currentTaskTypeValue + '</span>');
        $('#taskStage').html('<span class = "label '
            + getRunningStageClass(taskStage) + '" >' + taskStageValue + '</span>');

        // 除了任务状态为未知
        if (taskStatus) {
            $('#status').html('<span class="label ' + getStatusLevelClass(taskStatus) + '" >' + currentTaskStatusValue + '</span>');
        }
        // 基本信息
        // 任务名
        $('#taskName').html(taskName);
        // 已处理容量-php端已经转换且带单位
        $('#currentSize').html(complateSize);
        // 开始时间
        $('#startTime').html(startTime);
        // 持续时间
        $('#intervalTime').html(intervalTime);
        // 限速策略描述
        $('#limitTimeInfo').html(speedLimitDes);
        // 传输网络
        if (transportIp) {
            $('.transportNetworkDiv').show();
            $('#transportNetworkInfo').html(transportIp);
        } else {
            $('.transportNetworkDiv').hide();
        }

        // 解析任务基础信息,根据状态和阶段显示或隐藏某些元素
        parseTaskBasicInfo(data);
        // //获取任务数据流向主备机状态
        getMachineStatus();
        // 接管中阶段显示回切配置按钮
        showFailbackButton(taskStatus,taskStage);
    };

    const showFailbackButton = (taskStatus,taskStage) => {
        if(taskStatus == CONF.DBCDP_TASK_STATUS.SUCCESSED && taskStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER){
            // 成功状态 接管中阶段
            $('.taskSwitchbackConfigDiv').show();
        }else{
            $('.taskSwitchbackConfigDiv').hide();
        }
    };


    /**
     * 得到任务阶段的显示底色  运行状态
     * @param taskStatus
     * @param runningStage
     * @return {string}
     */
    var getRunningStageClass = function (taskStage) {
        var levelClass = '';
        switch (taskStage) {
            case CONF.DBCDP_TASK_RUNNING_STAGE.WAIT_EXEC:
                levelClass = "label-info";
                break;
            case CONF.DBCDP_TASK_RUNNING_STAGE.FULL_SYNC:
            case CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC:
            case CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_SUCCESSED:
                levelClass = "label-success";
                break;
            case CONF.DBCDP_TASK_RUNNING_STAGE.DICT_EXPORT:
            case CONF.DBCDP_TASK_RUNNING_STAGE.DICT_IMPORT:
                levelClass = "label-primary";
                break;
            case CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
            case CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                levelClass = "label-green";
                break;
            case CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_DICT_EXPORT:  //回切数据字典导出
            case CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_DICT_IMPORT:  //回切数据字典导入
            case CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_FULL_SYNC:  //回切全量数据同步
            case CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_LOG_SYNC:  //逆向实时同步
            case CONF.DBCDP_TASK_RUNNING_STAGE.SERVICE_FAILBACK:  //回切生产业务
                levelClass = "label-blue-madison";
                break;
            default:
                levelClass = "label-default";
                break;
        }
        return levelClass;
    }

    /**
     * 得到状态的显示类型
     * @param level
     * @return {string}
     */
    var getStatusLevelClass = function (level) {
        var levelClass = '';
        switch (level) {
            case 1:
                levelClass = "label-info";
                break;
            case 2:
            case 3:
            case 9:
                levelClass = "label-success";
                break;
            case 4:
                levelClass = "label-default";
                break;
            case 7:
                levelClass = "label-warning";
                break;
            case 6:
            case 8:
                levelClass = "label-danger";
                break;
            default:
                levelClass = "label-info";
                break;
        }
        return levelClass;
    }

    /**
     * 解析任务基础信息,根据状态和阶段显示或隐藏某些元素
     */
    const parseTaskBasicInfo = function (data) {
        // 压缩传输
        let compressFlag = data.transport_strategy.compress_flag;
        // 加密
        let encryptFlag = data.transport_strategy.encrypt_flag;
        // 加密值
        let encryptFlagValue = data.transport_strategy.encrypt_flag_value;
        let encryptMethodValue =  data.transport_strategy.encrypt_method;

        // 压缩
        let compressFlagValue = data.transport_strategy.compress_flag_value;
        let compressMethodValue =  data.transport_strategy.compress_method;
        $('#transportCompress').html(getFlagLevelInfo(compressFlagValue));
        $("#transportEncrypt").html(getFlagLevelInfo(encryptFlagValue));
        // 加密文本显示
        if(encryptFlagValue){
            $('.transportEncryptMethoddiv').show();
            for(key in encryptMethod){
                if( encryptMethodValue == encryptMethod[key]){
                    $('#transportEncryptMethod').text(key);
                }
            }
        }else{
            $('.transportEncryptMethoddiv').hide();
        }


        // 压缩文本显示
        if(compressFlagValue){
            $('.transportCompressMethoddiv').show();
            for(key in compressMethod){
                if(compressMethodValue == compressMethod[key]){
                    $('#transportCompressMethod').text(key);
                }
            }
        }else{
            $('.transportCompressMethoddiv').hide();
        }

        switch (taskType) {
            //同步
            case CONF.TASK_TYPE.CDP_DB_BACKUP:
                $('.dataTypediv').hide();  //数据类型
                break;
            case CONF.TASK_TYPE.CDP_DB_RECOVERY:
                $("#currentSizeView").hide();
                $('#takeoverli').hide();
                $('.delayLoadTimediv').hide();
                $('.sourceFileCachePathDiv').hide();
                $('.replay_error_transaction_num_div').hide();
                $('.failbackTimeDiv').hide();
                break;
        }
    }

    /**
     * 得到开启和关闭的HTML内容
     * @param flag
     * @return {string}
     */
    const getFlagLevelInfo = function (flag) {
        var html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        if (flag || flag == LANG.UI_PUBLIC_ON) {
            html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        }
        return html;
    }


    /**
     * 获取任务详情公共接口没有的信息
     */
    let lastValidDelay = 0;
    // 定义一个变量存储上一次有效的正延迟时间（可放在外层作用域，避免每次执行被重置）
    const getBasicInfo = function () {
        var updateInterval = 5000;
        var init = function () {
            if (timerTask.DbCdpJobDetailBasicInfo) {
                clearTimeout(timerTask.DbCdpJobDetailBasicInfo);
            }
            if (0 == $('#task_uuid').size()) {
                clearTimeout(timerTask.DbCdpJobDetailBasicInfo);
                return;
            }
            pAjaxRequest({}, '/api/v1/dbcdp/jobs/' + taskUuid + '/basicinfo', 'GET', function (d) {
                if(!d.success){
                    LOCATION('./content/platform/jobs/jobs.php', 'task');
                }
                let data = d.data;
                task_user_uuid = data.task_user_uuid;
                // 复制:1  接管回切:2  恢复:3
                sync_direction = data.sync_direction;
                // 复制的用户
                select_copy_users_value = data.select_copy_users;
                // 已完成的表
                let completedTables = data.completed_tables;
                // 总表数
                let totalTables = data.total_tables;
                // 传输模式
                let transportMode = data.transportStrategy_mode;
                // 源机文件缓存路径
                let source_fileCachePath = data.source_file_cache_storage_path != '' ? data.source_file_cache_storage_path : LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT;
                // 源机文件缓存大小(已用空间)
                let source_fileCacheUsedSize = data.source_file_cache_used_space;
                // 源机文件缓存大小(可用空间)
                let source_fileCacheSize = data.source_file_cache_alloc_space_value != '-1' ? data.source_file_cache_alloc_space : LANG.UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE;
                source_fileCacheSize_value = data.source_file_cache_alloc_space_value;
                // 备机文件缓存路径
                let backup_fileCachePath = data.file_cache_storage_path != '' ? data.file_cache_storage_path : LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT;
                // 备机文件缓存大小(已用空间)
                let backup_fileCacheUsedSize = data.file_cache_used_space;
                // 备机文件缓存大小(可用空间)
                let backup_fileCacheSize = data.file_cache_alloc_space_value != '-1' ? data.file_cache_alloc_space : LANG.UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE;
                backup_fileCacheSize_value = data.file_cache_alloc_space_value;
                // 传输线程个数
                $('#exp_thread_num').html(data.exp_max_process_num);
                $('#imp_thread_num').html(data.imp_max_process_num);
                if(taskStatus == CONF.DBCDP_TASK_STATUS.RUNNING){
                    if(taskStage == CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC){
                        // 数据延迟
                        let lastReplayTime = new Date(data.last_redo_replay_time);
                        let latestRedoTime = new Date(data.latest_redo_time);
                        let currentDelay = Math.floor(Math.floor(latestRedoTime.getTime() / 1000 - lastReplayTime.getTime() / 1000));
                        if(currentDelay > 0){
                            // 源库最新日志时间
                            $('#latestRedoTime').html(data.latest_redo_time);
                            // 目标库重放日志时间
                            $('#lastRedoReplayTime').html(data.last_redo_replay_time);
                            let displayDelay = currentDelay > 0 ? (lastValidDelay = currentDelay) : lastValidDelay;
                            $('#dataDelayTime').html(displayDelay + 's');
                        }
                    }else if(taskStage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_LOG_SYNC){
                        // 数据延迟
                        let lastReplayTime = new Date(data.failback_last_redo_replay_time);
                        let latestRedoTime = new Date(data.failback_latest_redo_time);
                        let currentDelay = Math.floor(Math.floor(latestRedoTime.getTime() / 1000 - lastReplayTime.getTime() / 1000));
                        if(currentDelay > 0){
                            // 源库最新日志时间
                            $('#latestRedoTime').html(data.failback_latest_redo_time);
                            // 目标库重放日志时间
                            $('#lastRedoReplayTime').html(data.failback_last_redo_replay_time);
                            let displayDelay = currentDelay > 0 ? (lastValidDelay = currentDelay) : lastValidDelay;
                            $('#dataDelayTime').html(displayDelay + 's');
                        }
                    }else{
                        if([40,41,50,51,52,53,54].includes(taskStage)){
                            // 源库最新日志时间
                            $('#latestRedoTime').html(data.failback_latest_redo_time ?? '--');
                        }else{
                            $('#latestRedoTime').html(data.latest_redo_time ?? '--');
                        }
                        // 目标库重放日志时间
                        $('#lastRedoReplayTime').html('--');
                        // 数据延迟
                        $('#dataDelayTime').html('--');
                    }
                }else{
                    // 源库最新日志时间
                    $('#latestRedoTime').html('--');
                    // 目标库重放日志时间
                    $('#lastRedoReplayTime').html('--');
                    // 数据延迟
                    $('#dataDelayTime').html('--');
                }
                // 同步的源
                backupTargetInfo.source_agent_uuid = data.host_uuid;
                backupTargetInfo.source_agent_cluster_uuid = data.host_cluster_uuid;
                backupTargetInfo.source_agent_cluster_flag = data.host_cluster_flag;
                backupTargetInfo.source_agent_app_uuid = data.host_app_uuid;

                // 同步的目标
                backupTargetInfo.target_agent_uuid = data.target_host_uuid;
                backupTargetInfo.target_agent_cluster_flag = data.target_cluster_flag;
                backupTargetInfo.target_agent_cluster_uuid = data.target_cluster_uuid;
                backupTargetInfo.target_agent_app_uuid = data.target_app_uuid;
                if(backupTargetInfo.target_agent_cluster_flag){
                    // 集群
                    selectedBackupUuid = data.target_cluster_uuid;
                }else{
                    // 单
                    selectedBackupUuid = data.target_app_uuid;
                    // 回切源机为单节点，高级配置dbms接口只能为关闭状态
                    $('#use_dbms_Switch').bootstrapSwitch('disabled', true);
                }

                $('#transportMode').html(transportMode);

                // 同步是否开启服务IP漂移
                switchIpFlag = data.switch_ip_flag;
                $('#source_fileCachePathStr').html(source_fileCachePath);
                $('#source_fileCacheSizeStr').html(source_fileCacheUsedSize + '/' + source_fileCacheSize);
                $('#backup_fileCachePathStr').html(backup_fileCachePath);
                $('#backup_fileCacheSizeStr').html(backup_fileCacheUsedSize + '/' + backup_fileCacheSize);

                // 延迟加载时间
                delay_load_time_value = data.delay_load_time_value;
                delayLoadTime = data.delay_load_time;
                // 是否配置延迟加载时间
                delayTimeFlag = data.delay_load_time_flag;
                if(data.task_type == CONF.TASK_TYPE.CDP_DB_RECOVERY){
                    $('.delayLoadTimestatusdiv').hide();
                }
                delayTimeFlag ? $('.delayLoadTimediv').show() : $('.delayLoadTimediv').hide();
                $('#delayLoadStatus').html(getFlagLevelInfo(delayTimeFlag));
                //
                let recoveryType = data.recovery_type;

                $('#delayLoadTimeStr').html(delayLoadTime);
                $('#recover_dataTypeStr').html(recoveryType);

                allowFailbackFlag = data.allow_failback_flag;

                // 是否配置接管
                istakoverConfigFlag = data.istakoverConfigFlag;
                isfailbackConfigFlag = data.isfailbackConfigFlag;
                getManualTakeoverConfig();
                // 是否配置回切
                if(isfailbackConfigFlag && sync_direction == 2){
                    showFailbackConfig();
                }else{
                    $('.failback_config_show').hide();
                }

                autoTakeoverFlag = data.auto_takeover_flag;
                allow_auto_takeover_flag = data.allow_auto_takeover_flag;
                let backupInfo = data.backup_info;
                let failbackTargetIp = data.failback_target_ip;
                let takeoverBusinessIP = data.takeover_business_ip_map;
                let agentHeartbeatFailureTime = data.agent_heartbeat_failure_time;
                let appFaultDetectionInterval = data.app_fault_detection_interval;
                let appConsecutiveFailureNum = data.app_consecutive_failure_num;
                takeover_ip_scope_info = data.ip_scope_info;
                //如果开始自动接管
                if (autoTakeoverFlag) {
                    $('#takeoverStr').html('<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>');
                    $('#standByStr').html(backupInfo);
                    $('#heartFailurevalDivNum').html(agentHeartbeatFailureTime + 's');
                    $('#faultIntervalStr').html(appFaultDetectionInterval + 's');
                    $('#conseFailureNum').html(Math.round(appConsecutiveFailureNum) + LANG.UI_PUBLIC_UNIT_COUNT);
                } else {
                    $('#auto_takeover_config').hide();
                    $('#takeoverStr').html('<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>');
                }

                // scn范围
                last_redo_replay_scn = data.last_redo_replay_scn;
                latest_recv_transaction_scn = data.latest_recv_transaction_scn;
                timepoint_type = data.timepoint_type;
                // dbms接口等配置
                let extend_advanced_config = d.data.extend_advanced_config;
                $('#use_dbms_value').html(getFlagLevelInfo(extend_advanced_config.use_dbms));
                $('#sync_big_object_value').html(getFlagLevelInfo(extend_advanced_config.sync_big_object));
                $('#timed_gen_txn_value').html(getFlagLevelInfo(extend_advanced_config.timed_gen_txn));
                $('#replay_ddl_value').html(getFlagLevelInfo(extend_advanced_config.replay_ddl));
                $('#ignore_nonsupport_data_type_value').html(getFlagLevelInfo(extend_advanced_config.ignore_nonsupport_data_type));
                $('#ignore_resource_limiting_value').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));
                // 操作按钮
                initOpButton();
            });
            timerTask.DbCdpJobDetailBasicInfo = setTimeout(init, updateInterval);
        }
        init();
    }

    /**
     * 初始化操作按钮
     */
    const initOpButton = function () {
        var opCode = [];
        if (taskType == CONF.TASK_TYPE.CDP_DB_BACKUP) {  //同步
            opCode = getBackupControlCode();
        } else if (taskType == CONF.TASK_TYPE.CDP_DB_RECOVERY) {  //恢复
            opCode = getRecoverControllCode();
        }
        let disableSrEnableDesc = '';
        if(allow_auto_takeover_flag){
            disableSrEnableDesc = LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_DISABLE;  //停用自动接管
        }else{
            disableSrEnableDesc = LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_ENABLE;  //启用自动接管
        }
        var button = "";
        $.each(opCode, function (i, d) {
            // opCode-可执行操作:1启动同步/2删除同步/3修改同步/4停止同步/5暂停同步/20启动接管/21停止接管/40启动回切/60启动恢复/61删除恢复/62修改恢复/63停止恢复/80强制停止
            switch (d) {
                case 1:
                    button += '<li class="startSyn"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START + '</a></li>';
                    break;
                case 2:
                    button += '<li class="deleteSyn"><a href="javascript:;"><i class="viconfont vicon-shanchu"></i> ' + LANG.UI_JOB_DELETE + '</a></li>';
                    break;
                case 3:
                    button += '<li class="modifySyn"><a href="javascript:;"><i class="viconfont vicon-ge_modify"></i> ' + LANG.UI_JOB_MODIFY + '</a></li>';
                    break;
                case 4:
                    button += '<li class="stopSyn"><a href="javascript:;" ><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
                    break;
                case 5:
                    button += '<li class="pauseSyn"><a href="javascript:;" ><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_PAUSE + '</a></li>';
                    break;
                case 20:
                    button += '<li class="startTakeover"><a href="javascript:;" ><i class="viconfont vicon-vol_cdp_takeover"></i> ' + LANG.UI_JOB_START_TAKEOVER + '</a></li>';
                    break;
                case 21:
                    button += '<li class="stopTakeover"><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i>' + LANG.UI_JOB_STOP_TAKEOVER + ' </a></li>';
                    break;
                case 40:
                    button += '<li class="startFailBack"><a href="javascript:;" ><i class="viconfont vicon-ge_cutback"></i>' + LANG.UI_JOB_START_DBCDP_FAILBACK + '</a></li>';
                    break;
                case 60:
                    button += '<li class="startRecovery"><a href="javascript:;"><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START + '</a></li>';
                    break;
                case 61:
                    button += '<li class="deleteRecovery"><a href="javascript:;"><i class="viconfont vicon-shanchu"></i> ' + LANG.UI_JOB_DELETE + '</a></li>';
                    break;
                case 63:
                    button += '<li class="stopRecovery"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
                    break;
                case 80:
                    button += '<li class="forcedStop"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a></li>';
                    break;
                case 111:
                    button += '<li class="disableSrEnable"><a href="javascript:;"><i class="viconfont vicon-ge_data_flow"></i> ' + disableSrEnableDesc + '</a></li>';
                    break;
                case 112:
                    button += '<li class="editHighConfig"><a href="javascript:;"><i class="viconfont vicon-ge_data_flow"></i> ' + LANG.UI_DB_CDP_EDIT_ADVANCED_CONFIG + '</a></li>';
                    break;
            }
        });
        $('#dbCdpOpList').html(button);
        initListeners();
    }

    /**
     * @function 获取同步任务可控制项
     * @return opCode array
     */
    const getBackupControlCode = function () {
        var opCode = [];
        var runningStage = taskStage;  //运行阶段
        var disOrEnableTakeoverNumber = 111; //启用或禁用自动接管
        // opCode-可执行操作:1启动同步/2删除同步/3修改同步/4停止同步/5暂停同步/20启动接管/21停止接管/40启动回切/41停止回切/60启动恢复/61删除恢复/62修改恢复/63停止恢复/接管配置70/接管回切71/80强制停止/111禁用自动接管
        //_taskStatus:任务状态
        switch (taskStatus) {
            case CONF.DBCDP_TASK_STATUS.WAITTING:		//任务等待运行
                opCode = [1, 2, 3];			//启动,删除
                break;
            case CONF.DBCDP_TASK_STATUS.RUNNING:		//运行中
                if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_EXPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_IMPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FULL_SYNC || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.CONSTRAINT_IMPORT) { //任务阶段:初始同步
                    opCode = [4];
                } else if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC) {  //实时同步
                    opCode = [4, 5, 20];
                } else if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_LOG_SYNC) {  //接管启动中 接管中 逆向初始同步 逆向实时同步
                    opCode = [21];
                }
                break;
            case CONF.DBCDP_TASK_STATUS.STOPPED:	//任务停止
                if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_EXPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_IMPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FULL_SYNC || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.CONSTRAINT_IMPORT) { //初始同步
                    opCode = [1, 2, 3];
                } else if(runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC){
                    opCode = [1, 2, 3, 20];
                } else if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_DICT_IMPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_LOG_SYNC) {  //接管中+接管启动中+逆向实时同步
                    opCode = [1, 2];
                }
                break;
            case CONF.DBCDP_TASK_STATUS.STOPPING:   //任务停止中
                opCode = [80];
                break;
            case CONF.DBCDP_TASK_STATUS.SUCCESSED:   //任务成功
                if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER) {
                    opCode = [21, 40];
                } else if(runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_SUCCESSED){
                    opCode = [2];
                }
                break;
            case CONF.DBCDP_TASK_STATUS.ERROR:   //任务失败
                if(runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.UNKNOWN){
                    opCode = [1, 2, 3];
                }  else if(runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.WAIT_EXEC){
                    opCode = [1, 2, 3];
                } else if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING) {
                    //接管中/接管启动中
                    opCode = [1, 2, 3, 20];
                } else if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_DICT_IMPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_LOG_SYNC) {
                    //逆向初始同步  逆向实时同步
                    opCode = [21, 40];
                } else if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_EXPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_IMPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FULL_SYNC || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.CONSTRAINT_IMPORT) {
                    opCode = [1, 2, 3];
                }  else if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_DICT_EXPORT){
                    opCode = [2, 21, 40];
                }
                break;
            case CONF.DBCDP_TASK_STATUS.PAUSED:   //任务暂停
                if (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC) {
                    opCode = [1, 4, 20, 112];
                }
                break;
            case CONF.DBCDP_TASK_STATUS.NETWORK_FAULT:
                if(runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.WAIT_EXEC || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_EXPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_IMPORT || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FULL_SYNC || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.CONSTRAINT_IMPORT){
                    opCode = [4];
                }else if(runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC){
                    opCode = [4,20];
                }else if(runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER){
                    opCode = [21];
                }
                break;
        }
        // 配置自动接管，并且任务阶段处于等待、数据字典导出、数据字典导入、全量数据同步、日志数据同步 时可以操作禁用或启用自动接管
        if(autoTakeoverFlag
            && (runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.WAIT_EXEC
                || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_EXPORT
                || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.DICT_IMPORT
                || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.FULL_SYNC
                || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC
                || runningStage == CONF.DBCDP_TASK_RUNNING_STAGE.CONSTRAINT_IMPORT) && taskStatus != CONF.DBCDP_TASK_STATUS.STARTING) {
            opCode.push(disOrEnableTakeoverNumber);
        }
        return opCode;
    }

    /**
     * @function 获取恢复任务可控制项
     * @return opCode array
     */
    const getRecoverControllCode = function () {
        var opCode = [];
        switch (taskStatus) {
            // opCode-可执行操作:1启动同步/2删除同步/3修改同步/4停止同步/5暂停同步/20启动接管/21停止接管/40启动回切/41停止回切/60启动恢复/61删除恢复/62修改恢复/63停止恢复/80强制停止
            case CONF.DBCDP_TASK_STATUS.WAITTING:		//任务等待
                opCode = [60, 61];			            //启动,删除
                break;
            case CONF.DBCDP_TASK_STATUS.RUNNING:		//任务正在运行
                opCode = [63];			                //停止
                break;
            case CONF.DBCDP_TASK_STATUS.STOPPED:	    //任务停止
                opCode = [60, 61];			            //启动,删除,修改
                break;
            case CONF.DBCDP_TASK_STATUS.STOPPING:	    //任务停止中
                opCode = [80];			                //强制停止
                break;
            case CONF.DBCDP_TASK_STATUS.SUCCESSED:      //成功
                opCode = [61];			                //删除恢复
                break;
            case CONF.DBCDP_TASK_STATUS.ERROR:          //失败
                opCode = [60, 61];
                break;
            case CONF.DBCDP_TASK_STATUS.NETWORK_FAULT:  //网络错误
                opCode = [63];
                break;
        }
        return opCode;
    }

    /**
     * 获取任务数据流向主备机状态
     */
    const getMachineStatus = function () {
        var init = function () {
            if(0 == $("#task_uuid").size()){
                return;
            }

            // 更新DOM函数，仅在数据变化时更新
            function updateDOM(data) {
                // 检查数据是否发生变化
                if (!isDataEqual(data, dataCache)) {
                    // 更新DOM
                    // 同步的目标机是回切的源机
                    // 源机uuid  同步的目标机是回切的源机
                    failback_object.source_host_uuid = data.target_agent_uuid;
                    failback_object.source_host_cluster_uuid = data.target_cluster_uuid;
                    // 源应用uuid
                    failback_object.source_app_uuid = data.target_app_uuid;
                    // 源应用版本
                    failback_object.source_app_version = data.target_app_version;
                    // 源应用类型
                    failback_object.source_app_type = data.target_app_type;
                    // 源机是否是集群
                    failback_object.source_cluster_flag = data.target_cluster_flag;
                    // 源机的集群ip
                    failback_object.source_cluster_ip = data.target_cluster_service_ip;
                    // 源机是单机的hostname
                    failback_object.source_host_name = data.target_hostname;
                    // 源机是单机的ip
                    failback_object.source_ip = data.target_ip;
                    failback_object.source_os_type = data.target_os_type;
                    backupInfo.source_agent_uuid = data.source_agent_uuid;
                    backupInfo.source_app_uuid = data.source_app_uuid;
                    backupInfo.source_agent_cluster_flag = data.source_cluster_flag;
                    backupInfo.source_agent_cluster_uuid = data.source_cluster_uuid;
                    backupInfo.target_agent_uuid = data.target_agent_uuid;
                    backupInfo.target_agent_cluster_uuid = data.target_cluster_uuid;
                    backupInfo.target_agent_cluster_flag = data.target_cluster_flag;
                    backupInfo.source_agent_online_flag = data.source_agent_online_flag;
                    // 0:unkonwn, 1:在线,2:掉线,3:停止
                    backupInfo.target_agent_online_flag = data.target_agent_online_flag;
                    // 心跳时间
                    backupInfo.agent_heartbeat_failure_time = data.agent_heartbeat_failure_time;
                    // 回切源机的应用信息
                    sourceAgentAppInfo.app_type = data.source_app_type;
                    sourceAgentAppInfo.app_version = data.source_app_version;

                    $('#topContent').empty();
                    $('#bottomContent').empty();
                    $('#machineContent').empty();
                    let failbackOtherFlag = data.failback_other_flag;
                    if((failbackOtherFlag && taskStageObj.taskStageFailback.includes(taskStage)) || (failbackOtherFlag && data.sync_direction == 2)){
                        // 回切到异机
                        var element = '';
                        var infoTop = '';
                        var infoBottom = '';
                        if (data.source_cluster_flag) {
                            element += '<div class="backupDes" style="position: absolute;top: 27rem;left: -0.5rem;color: #999999">' + data.source_cluster_ctrl_app_name + '<span class="sourceIP" style="color: #0FBF98">' + '(' + 'localhost.localdomain(192.168.19.11)' + ')' + '</span></div>';
                            //如果同步源机是集群
                            if (data.source_online_flag) {
                                element +=  '<img id="hostClusterOn" src="./img/dbcdp/hostClusterOn_failback.png" alt="" style="position: absolute;left: -1.5rem;top: 15rem; z-index: 1">';
                            } else {
                                element +=  '<img id="hostClusterOff" src="./img/dbcdp/hostClusterOff_failback.png" alt="" style="position: absolute;left: -1.5rem;top: 15rem; z-index: 1">';
                            }
                        } else if (!data.source_cluster_flag) {
                            element += '<div class="backupDes" style="position: absolute;top: 27rem;left: 0.5rem;color: #999999">' + data.source_app_service_name + '<span class="sourceIP" style="color: #0FBF98">' + '(' + data.source_ip + ')' + '</span></div>';
                            //如果同步源机是单机
                            if (data.source_online_flag) {
                                element += '<img id="backupOn" src="./img/dbcdp/hostOn_failback.png" alt="" style="position: absolute;left: 2.5rem;top: 15rem; z-index: 1">';
                            } else {
                                element += '<img id="backupOff" src="./img/dbcdp/hostOff_failback.png" alt="" style="position: absolute;left: 2.5rem;top: 15rem; z-index: 1">';
                            }
                        }
                        if (data.target_cluster_flag) {
                            //如果回切源机是集群
                            element += '<div class="failbackSourceDes" style="position: absolute;top: 27rem;left: 21.5rem;color: #999999">' + data.target_cluster_ctrl_app_name + '<span class="failbackSourceIP" style="color: #0FBF98">' + '(' + 'localhost.localdomain(192.168.19.11)' + ')' + '</span></div>';
                            if (data.target_online_flag) {
                                element += '<img class="backupClusterOn" src="./img/dbcdp/backupClusterOn_failback.png" alt="" style="position: absolute; left: 26rem;top: 15rem;z-index: 1">';
                            } else {
                                element += '<img class="backupOn" src="./img/dbcdp/backupClusterOff_failback.png" alt="" style="position: absolute; left: 26rem;top: 15rem;z-index: 1">';
                            }
                        } else if (!data.target_cluster_flag) {
                            //如果回切源机是单机
                            element += '<div class="failbackSourceDes" style="position: absolute;top: 27rem;left: 26.5rem;color: #999999">' + data.target_app_service_name + '<span class="failbackSourceIP" style="color: #0FBF98">' + '(' + data.target_ip + ')' + '</span></div>';
                            if (data.target_online_flag) {
                                element += '<img class="backupOn" src="./img/dbcdp/backupOn_failback.png" alt="" style="position: absolute; left: 30rem;top: 15rem;z-index: 1">';
                            } else {
                                element += '<img class="backupOff" src="./img/dbcdp/backupOff_failback.png" alt="" style="position: absolute; left: 30rem;top: 15rem;z-index: 1">';
                            }
                        }
                        if(data.failback_target_cluster_flag){
                            // 如果回切目标机是集群
                            if(data.target_cluster_flag){
                                // 如果回切源机是集群
                                element += '<div class="failbackTragetDes" style="position: absolute;top: 27rem;left: 53.5rem;color: #999999">' + data.failback_host_name + '<span class="failbackTragetIP" style="color: #0FBF98">' + '(' + data.failback_agent_name + ')' + '</span></div>';
                            }else{
                                element += '<div class="failbackTragetDes" style="position: absolute;top: 27rem;left: 49.5rem;color: #999999">' + data.failback_host_name + '<span class="failbackTragetIP" style="color: #0FBF98">' + '(' + data.failback_agent_name + ')' + '</span></div>';
                            }
                            if (data.failback_target_online_flag) {
                                element += '<img class="failbackClusterOn" src="./img/dbcdp/targetClusterOn_failback.png" alt="" style="position: absolute; right: -6rem;top: 15rem;z-index: 1">';
                            } else {
                                element += '<img class="failbackClusterOff" src="./img/dbcdp/targetClusterOff_failback.png" alt="" style="position: absolute; right: -6rem;top: 15rem;z-index: 1">';
                            }
                        }else if(!data.failback_target_cluster_flag){
                            // 如果回切目标机是单机
                            if(data.target_cluster_flag){
                                // 如果回切源机是集群
                                element += '<div class="failbackTragetDes" style="position: absolute;top: 27rem;left: 55.5rem;color: #999999">' + data.failback_host_name + '<span class="failbackTragetIP" style="color: #0FBF98">' + '(' + data.failback_agent_name + ')' + '</span></div>';
                            }else{
                                element += '<div class="failbackTragetDes" style="position: absolute;top: 27rem;left: 49.5rem;color: #999999">' + data.failback_host_name + '<span class="failbackTragetIP" style="color: #0FBF98">' + '(' + data.failback_agent_name + ')' + '</span></div>';
                            }
                            if (data.failback_target_online_flag) {
                                element += '<img class="failbackOn" src="./img/dbcdp/targetOn_failback.png" alt="" style="position: absolute; right: -3rem;top: 15rem;z-index: 1">';
                            } else {
                                element += '<img class="failbackOff" src="./img/dbcdp/targetOff_failback.png" alt="" style="position: absolute; right: -3rem;top: 15rem;z-index: 1">';
                            }
                        }


                        infoTop += ' <img id="backupServerOn" src="./img/dbcdp/backupServerOn.png" alt="" style="position: absolute;left: 30.2rem;top: 4.5rem;z-index: 100">' +
                            '<div id="backupServerDes" style="position: absolute;top: 2.5rem;left: 24.5rem;color: #999999">'+ LANG.UI_VOL_CDP_TAKEOVER_BACKUP_SERVER + ':'+ '<span id="backupServerId" style="color: #0FBF98">' + nodeInfo + '</span></div>';  //备份服务器

                        infoTop += ' <img id="ServerToClient" src="./img/dbcdp/ServerToClient.png" alt="" style="position: absolute;left: 32.5rem;top: 12rem">';

                        infoTop += '<img id="topline_transmitting" src="./img/dbcdp/topline_transmitting_failback.png" alt="" style="position: absolute;left: 5rem;top: 7rem;">';
                        infoBottom += '<img id="Host_Connection" src="./img/dbcdp/Host_Connection.png" alt="" style="position: absolute;top: 23.5rem;left: 8rem;">';
                        if(taskStageObj.taskStageFailbackRunning.includes(data.current_job_stage)){
                            // 回切中
                            if(taskStatus == CONF.DBCDP_TASK_STATUS.RUNNING){
                                infoBottom += '<img id="PicState_normal" src="./img/dbcdp/PicState_normal.gif" alt="" style="position: absolute;left: 34rem;top: 22rem;">';
                            }else{
                                infoBottom += '<img id="PicState_normal" src="./img/dbcdp/Host_Connection.png" alt="" style="position: absolute;top: 23.5rem;left: 31rem;">';
                            }
                        }else if(dataFlowObj.taskStatusError.includes(data.job_status)){
                            // 回切失败
                            infoBottom += '<img id="Host_Connection_error" src="./img/dbcdp/Host_Connection_error.png" alt="" style="position: absolute;top: 23.5rem;left: 33rem;">';
                            infoBottom += '<img id="PicState_error" src="./img/dbcdp/PicState_error.png" alt="" style="position: absolute;left: 44rem;top: 22rem;">';
                        }

                        $('#topContent').append(infoTop);
                        $('#bottomContent').append(infoBottom);
                        $('#machineContent').append(element);

                    }else{
                        // 回切到同步的源机
                        var element = '';
                        if (data.source_cluster_flag) {
                            element += '<div class="backupDes" style="position: absolute;top: 11.5rem;left: -1.5rem;color: #999999">' + data.source_cluster_ctrl_app_name + '<span class="sourceIP" style="color: #0FBF98">' + '(' + data.source_cluster_ctrl_ip + ')' + '</span></div>';
                            //如果源机是集群
                            if (data.source_online_flag) {
                                element +=  '<img id="hostClusterOn" src="./img/dbcdp/hostClusterOn.png" alt="" style="position: absolute;left: -1rem;top: -2rem; z-index: 1">';
                            } else {
                                element +=  '<img id="hostClusterOff" src="./img/dbcdp/hostClusterOff.png" alt="" style="position: absolute;left: -1rem;top: -2rem; z-index: 1">';
                            }
                        } else if (!data.source_cluster_flag) {
                            element += '<div class="backupDes" style="position: absolute;top: 11.5rem;left: 0.5rem;color: #999999">' + data.source_app_service_name + '<span class="sourceIP" style="color: #0FBF98">' + '(' + data.source_ip + ')' + '</span></div>';
                            //如果源机不是集群
                            if (data.source_online_flag) {
                                element += '<img id="backupOn" src="./img/dbcdp/hostOn.png" alt="" style="position: absolute;left: 3.5rem;top: -2rem; z-index: 1">';
                            } else {
                                element += '<img id="backupOff" src="./img/dbcdp/hostOff.png" alt="" style="position: absolute;left: 3.5rem;top: -2rem; z-index: 1">';
                            }
                        }

                        if (data.target_cluster_flag) {
                            //如果目标机是集群
                            element += '<div class="targetDes" style="position: absolute;top: 11.5rem;left: 45.5rem;color: #999999">' + data.target_cluster_ctrl_app_name + '<span class="targetIP" style="color: #0FBF98">' + '(' + data.target_cluster_ctrl_ip + ')' + '</span></div>';
                            if (data.target_online_flag) {
                                element += '<img id="backupClusterOn" src="./img/dbcdp/backupClusterOn.png" alt="" style="position: absolute; left: 46.5rem;top: -2rem;z-index: 1">';
                            } else {
                                element += '<img id="backupOn" src="./img/dbcdp/backupClusterOff.png" alt="" style="position: absolute; left: 46.5rem;top: -2rem;z-index: 1">';
                            }
                            selectedBackupUuid = data.target_cluster_uuid;
                        } else if (!data.target_cluster_flag) {
                            //如果目标机机不是集群
                            element += '<div class="targetDes" style="position: absolute;top: 11.5rem;left: 46.5rem;color: #999999">' + data.target_app_service_name + '<span class="targetIP" style="color: #0FBF98">' + '(' + data.target_ip + ')' + '</span></div>';
                            if (data.target_online_flag) {
                                element += '<img id="backupClusterOff" src="./img/dbcdp/backupOn.png" alt="" style="position: absolute; left: 50.5rem;top: -2rem;z-index: 1">';
                            } else {
                                element += '<img id="backupOff" src="./img/dbcdp/backupOff.png" alt="" style="position: absolute; left: 50.5rem;top: -2rem;z-index: 1">';
                            }
                            selectedBackupUuid = data.target_app_uuid;
                        }
                        $('#machineContent').append(element);

                        //上面线
                        $('#topContent').empty();
                        //下面线
                        $('#bottomContent').empty();
                        var infoTop = '';
                        var infoBottom = '';
                        if (dataFlowObj.taskStatusNormal.includes(data.job_status)) {
                            //任务状态-正常
                            if (data.job_type == CONF.TASK_TYPE.CDP_DB_BACKUP) {
                                //任务类型-同步
                                infoTop += '<img id="topline_transmitting" src="./img/dbcdp/topline_transmitting.png" alt="" style="display: block;margin: 0 auto;margin-top: 9.4rem">';
                                infoBottom = '<img id="bottomline_transmitting_syn" src="./img/dbcdp/bottomline_transmitting_syn.gif" alt="" style="position: absolute;top: 6.5rem;left: 9rem;">';
                            } else if (data.job_type == CONF.TASK_TYPE.CDP_DB_RECOVERY) {
                                infoTop += '<img id="topline_transmitting" src="./img/dbcdp/topline_transmitting.png" alt="" style="display: block;margin: 0 auto;margin-top: 9.4rem">'
                                infoBottom = '<img id="bottomline_transmitting_recover" src="./img/dbcdp/bottomline_transmitting_syn.gif" alt="" style="position: absolute;top: 6.5rem;left: 9rem;">';
                            }

                        } else if (dataFlowObj.taskStatusRunning.includes((data.job_status))) {
                            //任务状态-运行
                            if (data.job_type == CONF.TASK_TYPE.CDP_DB_BACKUP) {
                                if((data.current_job_stage == 10 || data.current_job_stage == 11 || data.current_job_stage == 20 || data.current_job_stage == 21) || (data.current_job_stage == 1 && data.sync_direction == 1) && data.agent_heartbeat_failure_time != 0 ){
                                    var heartbeatRunningLeft = '';
                                    var heartbeatRunningRight = '';
                                    if (data.source_agent_online_flag === 1) {
                                        heartbeatRunningLeft = 'heartbeat_running_left.gif';
                                        infoTop += '<img id="heart_beat_left" src="./img/dbcdp/' + heartbeatRunningLeft + '" alt="" style="position: absolute;left: 6rem;top:-1.2rem">';
                                    } else if (data.source_agent_online_flag === 2) {
                                        heartbeatRunningLeft = 'heartbeat_error_left.svg';
                                        infoTop += '<img id="heart_beat_left" src="./img/dbcdp/' + heartbeatRunningLeft + '" alt="" style="position: absolute;left: 17rem;top:-1rem">';
                                    }
                                    if (data.target_agent_online_flag === 1) {
                                        heartbeatRunningRight = 'heartbeat_running_right.gif';
                                        infoTop += '<img id="heart_beat_right" src="./img/dbcdp/' + heartbeatRunningRight + '" alt="" style="position: absolute;right: 6.1rem;top:-1.2rem">';
                                    } else if (data.target_agent_online_flag === 2) {
                                        heartbeatRunningRight = 'heartbeat_error_right.svg';
                                        infoTop += '<img id="heart_beat_right" src="./img/dbcdp/' + heartbeatRunningRight + '" alt="" style="position: absolute;right: 17rem;top:-1rem">';
                                    }
                                }
                                //任务阶段为接管中并且任务类型为同步
                                if (data.target_cluster_flag && data.current_job_stage == 31) {
                                    infoBottom = '<img id="aureole" src="./img/dbcdp/aureole.png" alt="" style="position: absolute;left: 44rem;top: -3rem; z-index: 0;animation: lightClusterFade 4s infinite;-webkit-animation: lightClusterFade 3s infinite;">';
                                } else if (!data.target_cluster_flag && data.current_job_stage == 31) {
                                    infoBottom = '<img id="Rectangle" src="./img/dbcdp/Rectangle.png" alt="" style="position: absolute;left: 47.5rem;top: -4rem; z-index: 0;animation: lightFade 4s infinite;-webkit-animation: lightFade 3s infinite;">';
                                }
                                if(data.sync_direction == 2){
                                    infoBottom += '<img id="bottomline_transmitting_recover" src="./img/dbcdp/bottomline_transmitting_recover.gif" alt="" style="position: absolute;top: 6.5rem;left: 9.58rem;">';
                                }else{
                                    infoBottom += '<img id="bottomline_transmitting_syn" src="./img/dbcdp/bottomline_transmitting_syn.gif" alt="" style="position: absolute;top: 6.5rem;left: 9.58rem;">';
                                }
                            } else {
                                if (data.target_cluster_flag && data.current_job_stage == 31) {
                                    infoBottom = '<img id="aureole" src="./img/dbcdp/aureole.png" alt="" style="position: absolute;left: 44rem;top: -3rem; z-index: 0;animation: lightClusterFade 4s infinite;-webkit-animation: lightClusterFade 3s infinite;">';
                                } else if (!data.target_cluster_flag && data.current_job_stage == 31) {
                                    infoBottom = '<img id="Rectangle" src="./img/dbcdp/Rectangle.png" alt="" style="position: absolute;left: 47.5rem;top: -4rem; z-index: 0;animation: lightFade 4s infinite;-webkit-animation: lightFade 3s infinite;">';
                                }
                                // 恢复
                                infoBottom += '<img id="bottomline_transmitting_recover" src="./img/dbcdp/bottomline_transmitting_syn.gif" alt="" style="position: absolute;top: 6.5rem;left: 9.58rem;">'
                            }
                            // 运行速度
                            infoBottom += '<div id="runningSpeed" class="c0FBF98" style="position: absolute;left: 28rem;top:9rem;"></div>';
                            infoTop += '<img id="topline_transmitting" src="./img/dbcdp/topline_transmitting.png" alt="" style="display: block;margin: 0 auto;margin-top: 9.4rem">';
                        } else if (dataFlowObj.taskStatusPause.includes((data.job_status))) {
                            if(data.job_type == CONF.TASK_TYPE.CDP_DB_BACKUP && data.agent_heartbeat_failure_time != 0 && data.source_agent_online_flag == 1 && data.target_agent_online_flag == 1){
                                infoTop += '<img id="heartbeat_pause_left_icon" src="./img/dbcdp/heartbeat_running_left.gif" alt="" style="position: absolute;left: 6rem;top: -1.2rem;">';
                                infoTop += '<img id="heartbeat_pause_right_icon" src="./img/dbcdp/heartbeat_running_right.gif" alt="" style="position: absolute;right: 6.1rem;top: -1.2rem;">';
                            }
                            infoTop += '<img id="topline_transmitting" src="./img/dbcdp/topline_transmitting.png" alt="" style="display: block;margin: 0 auto;margin-top: 9.4rem">';
                            infoBottom = '<img id="bottomline_success" src="./img/dbcdp/bottomline_success.png" alt="" style="position: absolute;top: 6.5rem;left: 9.58rem;">' +
                                '<img id="PicState_transmitting" src="./img/dbcdp/PicState_transmitting.png" alt="" style="position: absolute;left: 28rem;top: 5.3rem;">';
                        } else if (dataFlowObj.taskStatusOffline.includes(data.job_status)) {
                            //任务状态-离线
                            //任务状态-停止
                            if(data.job_type == CONF.TASK_TYPE.CDP_DB_BACKUP && data.job_status == 4 && data.agent_heartbeat_failure_time != 0 && (data.current_job_stage == 10 || data.current_job_stage == 11 || data.current_job_stage == 20 || data.current_job_stage == 21)){
                                var heartbeatRunningLeft = '';
                                var heartbeatRunningRight = '';
                                if (data.source_agent_online_flag === 3) {
                                    heartbeatRunningLeft = 'heartbeat_pause_left.svg';
                                }
                                if (data.target_agent_online_flag === 3) {
                                    heartbeatRunningRight = 'heartbeat_pause_right.svg';
                                }
                            }
                            infoTop += '<img id="heart_beat_left" src="./img/dbcdp/' + heartbeatRunningLeft + '" alt="" style="position: absolute;left: 17rem;top:-1rem">';
                            infoTop += '<img id="heart_beat_right" src="./img/dbcdp/' + heartbeatRunningRight + '" alt="" style="position: absolute;right: 17rem;top:-1rem">';
                            infoTop += '<img id="topline_offline" src="./img/dbcdp/topline_offline.png" alt="" style="display: block;margin: 0 auto;margin-top: 9.4rem">';
                            infoBottom = '<img id="bottomline_error" src="./img/dbcdp/bottomline_offline.png" alt="" style="position: absolute;top: 6.5rem;left: 9.58rem;">' +
                                '<img id="PicState_offline" src="./img/dbcdp/PicState_offline.png" alt="" style="position: absolute;left: 28rem;top: 5.3rem;">';
                        } else if (dataFlowObj.taskStatusError.includes(data.job_status)) {
                            //任务状态-错误
                            //网络错误
                            if(data.job_type == CONF.TASK_TYPE.CDP_DB_BACKUP && data.job_status == 6 && data.agent_heartbeat_failure_time != 0){
                                var heartbeatRunningLeft = '';
                                var heartbeatRunningRight = '';
                                if (data.source_agent_online_flag === 1) {
                                    heartbeatRunningLeft = 'heartbeat_running_left.gif';
                                    infoTop += '<img id="heart_beat_left" src="./img/dbcdp/' + heartbeatRunningLeft + '" alt="" style="position: absolute;left: 6rem;top:-1.2rem">';
                                } else if (data.source_agent_online_flag === 2) {
                                    heartbeatRunningLeft = 'heartbeat_error_left.svg';
                                    infoTop += '<img id="heart_beat_left" src="./img/dbcdp/' + heartbeatRunningLeft + '" alt="" style="position: absolute;left: 17rem;top:-1rem">';
                                }
                                if (data.target_agent_online_flag === 1) {
                                    heartbeatRunningRight = 'heartbeat_running_right.gif';
                                    infoTop += '<img id="heart_beat_right" src="./img/dbcdp/' + heartbeatRunningRight + '" alt="" style="position: absolute;right: 6.1rem;top:-1.2rem">';
                                } else if (data.target_agent_online_flag === 2) {
                                    heartbeatRunningRight = 'heartbeat_error_right.svg';
                                    infoTop += '<img id="heart_beat_right" src="./img/dbcdp/' + heartbeatRunningRight + '" alt="" style="position: absolute;right: 17rem;top:-1rem">';
                                }
                            }
                            infoTop += '<img id="topline_error" src="./img/dbcdp/topline_error.png" alt="" style="display: block;margin: 0 auto;margin-top: 9.4rem">';
                            infoBottom = '<img id="bottomline_error" src="./img/dbcdp/bottomline_error.png" alt="" style="position: absolute;top: 6.5rem;left: 9.58rem;">' +
                                '<img id="PicState_error" src="./img/dbcdp/PicState_error.png" alt="" style="position: absolute;left: 28rem;top: 5.3rem;">';
                        } else if (dataFlowObj.taskStatusWaitSuccess.includes(data.job_status)) {
                            //任务状态-等待/成功
                            if (data.job_type == CONF.TASK_TYPE.CDP_DB_BACKUP && data.target_cluster_flag && data.current_job_stage == 31) {
                                //同步-成功-接管
                                infoBottom = '<img id="aureole" src="./img/dbcdp/aureole.png" alt="" style="position: absolute;left: 44rem;top: -3rem; z-index: 0;animation: lightClusterFade 4s infinite;-webkit-animation: lightClusterFade 3s infinite;">';
                            } else if (data.job_type == CONF.TASK_TYPE.CDP_DB_BACKUP && !data.target_cluster_flag && data.current_job_stage == 31) {
                                infoBottom = '<img id="Rectangle" src="./img/dbcdp/Rectangle.png" alt="" style="position: absolute;left: 47.5rem;top: -4rem; z-index: 0;animation: lightFade 4s infinite;-webkit-animation: lightFade 3s infinite;">';
                            }
                            infoTop += '<img id="topline_transmitting" src="./img/dbcdp/topline_transmitting.png" alt="" style="display: block;margin: 0 auto;margin-top: 9.4rem">';
                            infoBottom += '<img id="bottomline_success" src="./img/dbcdp/bottomline_success.png" alt="" style="position: absolute;top: 6.5rem;left: 9.58rem;">';
                        } else {
                            //任务状态-异常
                            infoTop += '<img id="topline_abnormal" src="./img/dbcdp/topline_abnormal.png" alt="" style="display: block;margin: 0 auto;margin-top: 9.4rem">';
                            infoBottom = '<img id="bottomline_error" src="./img/dbcdp/bottomline_abnormal.png" alt="" style="position: absolute;top: 6.5rem;left: 9.58rem;">' +
                                '<img id="PicState__abnormal" src="./img/dbcdp/PicState__abnormal.png" alt="" style="position: absolute;left: 28rem;top: 5.3rem;">';
                        }
                        infoTop += ' <img id="backupServerOn" src="./img/dbcdp/backupServerOn.png" alt="" style="position: absolute;left: 27.5rem;top: -3rem">' +
                            '<div id="backupServerDes" style="position: absolute;top: 4.5rem;left: 23.5rem;color: #999999">'+ LANG.UI_VOL_CDP_TAKEOVER_BACKUP_SERVER +':<span id="backupServerId" style="color: #0FBF98">' + nodeInfo + '</span></div>';  //备份服务器

                        $('#topContent').append(infoTop);
                        $('#bottomContent').append(infoBottom);
                        $('#runningSpeed').html(data.speed);
                    }


                    // 更新缓存
                    dataCache = data;
                }
            };


            pAjaxRequest({}, '/api/v1/dbcdp/jobs/' + taskUuid + '/machineInfo', 'get', function (d) {
                var data = d.data;
                if (data == {}) {
                    return;
                }
                updateDOM(data);

            }, true);
        }
        init();
    }


    /**
     * 检查两个数据是否相等的辅助函数
     * @param data1
     * @param data2
     * @returns {boolean}
     */
    function isDataEqual(data1, data2) {
        // 这里可以根据你的数据结构进行相等性比较
        // 简单比较示例：JSON.stringify(data1) === JSON.stringify(data2)
        return JSON.stringify(data1) === JSON.stringify(data2);
    }


    /**
     * 初始化时间插件
     * 需要引入daterangepicker.js、daterangepicker.locales.js、bootstrap-switch.min.js
     */
    const inintDatatimePicker = () => {
        //初始化日期时间选择控件
        $dateInput = $('#failback_strategy_time_picker').daterangepicker({
            "autoUpdateInput": false, //是否自动填充input
            "startDate": moment().subtract(6, 'days').startOf('day'), //默认开始时间
            "endDate": moment({
                hour: 23,
                minute: 59
            }), //默认结束时间
            "maxDate":false,
            "linkedCalendars": false, // 取消面板联动
            "timePicker": true, //是否显示时间,时分
            "timePicker24Hour": true, //是否是24小时制
            "alwaysShowCalendars": true, //是否总是显示日期选择
            "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE), //根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE), //根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "timePickerSeconds": true
        }, function (start, end, label) {
        });

        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        $('#failback_strategy_time_picker').on('apply.daterangepicker', function (ev, picker) {
            //给全局变量赋值,然后设置input
            _daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_range = picker.chosenLabel;
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
        });

        $('#failback_strategy_time_picker').on('cancel.daterangepicker', function (ev, picker) {
            //清除全局变量,然后设置input
            _daterangepicker_starttime = "";
            _daterangepicker_endtime = "";
            _daterangepicker_range = "";
            $(this).val('');
        });

        //input右侧的图标事件
        $('.daterangepickerdiv i').click(function () {
            $(this).parent().find('input').click();
        });
    }

    /**
     * 初始化下对应控件下拉取值范围,默认值
     */
    const initSpinner = function () {
        $('.transferThreadDiv').spinner({value: 1, step: 1, min: 1, max: 8});
        $('#transfer_thread').off().on('blur',function (){
            var newValue = parseInt($(this).val());
            if(newValue <1 || newValue > 8){
                $('.transferThreadDiv').spinner('value', 1);
            }
        });
        $('.source_failbackfilecachediv').spinner({value: 4, step: 1, min: 4, max: 1024});
        $('.failbackfilecachediv').spinner({value: 64, step: 1, min: 4, max: 1024});
        $('.manualStrategyTypediv').spinner({value: 60, step: 1, min: 30, max: 300});
        $('#network_retry_times_spinner').spinner({value: 10, step: 1, min: 1, max: 60});
        $('#network_retry_interval_spinner').spinner({value: 60, step: 5, min: 5, max: 60});
        $('.free_time_interval_div').spinner({value: 1, step: 1, min: 1});
    }

    /**
     * 任务进度条
     */
    const totalpmgressbarDivControl = function () {
        var updateInterval = 1000;
        var init = function () {
            if (timerTask.dbCdpProgressInfo) {
                clearTimeout(timerTask.dbCdpProgressInfo);
            }
            pAjaxRequest({}, '/api/v1/dbcdp/jobs/' + taskUuid + '/progress', 'get', function (d) {
                var data = d.data;
                if (data == {}) {
                    clearTimeout(timerTask.dbCdpProgressInfo);
                    return;
                }
                // 总表数
                if (data.completed_table_num === 0 && data.total_table_num === 0) {
                    $('#totalSize').html('--/--');
                } else {
                    $('#totalSize').html(data.completed_table_num + '/' + data.total_table_num);
                }
                // 存储事务重放失败数
                if(data.replay_error_transaction_num === 0){
                    $('#replay_error_transaction_num').html('--/--');
                }else{
                    $('#replay_error_transaction_num').html(data.replay_error_transaction_num);
                }
                $('#progressInfo').hide();
                // 10:字典导出 11:字典导入
                let progressVal = 0;
                if(data.task_status == 2){
                    $('#progressInfo').show();
                    // 清空
                    $('#dictExportInfo').css({width: 0 + '%'});
                    $('#dictImportInfo').css({width: 0 + '%'});
                    $('#synTabInfo').css({width: 0 + '%'});
                    $('#constraintDictInfo').css({width: 0 + '%'});
                    $('#importprogressVal').html('');
                    $('#exportprogressVal').html('');
                    $('#currentObjTitle').html('');
                    $('#syntabprogressVal').html('');
                    $('#constraintprogressVal').html('');

                    if (data.execution_object_type == 10 || data.execution_object_type == 40) {
                        // 字典导出
                        $('#currentObjTitle').html(LANG.UI_DB_CDP_DETAILS_CURRENT_EXPORTED_DICTIONARY);
                        $('#currentSynObj').html(data.execution_object);
                        data.total == 0 ? progressVal = 0:progressVal = (data.completed / data.total) * 100;
                        $('#dictExportInfo').css({width: progressVal + '%'});
                        $('#exportprogressVal').html(LANG.UI_DB_CDP_DETAILS_DICTIONARY_EXPORT + Math.round(progressVal) + '%');
                    } else if (data.execution_object_type == 11 || data.execution_object_type == 41) {
                        //说明字典导出已经加载到100%
                        $('#dictExportInfo').css({width: 100 + '%'});
                        $('#exportprogressVal').html(LANG.UI_DB_CDP_DETAILS_DICTIONARY_EXPORT + 100 + '%');
                        //字典导入
                        $('#currentObjTitle').html(LANG.UI_DB_CDP_DETAILS_CURRENT_IMPORTED_DICTIONARY);
                        $('#currentSynObj').html(data.execution_object);
                        data.total == 0 ? progressVal = 0:progressVal = (data.completed / data.total) * 100;
                        $('#dictImportInfo').css({width: progressVal + '%'});
                        $('#importprogressVal').html(LANG.UI_DB_CDP_DETAILS_DICTIONARY_IMPORT + Math.round(progressVal)  + '%');
                    } else if (data.execution_object_type == 20 || data.execution_object_type == 50) {
                        //说明字典导入、导出已经加载到100%
                        $('#dictExportInfo').css({width: 100 + '%'});
                        $('#dictImportInfo').css({width: 100 + '%'});
                        $('#importprogressVal').html(LANG.UI_DB_CDP_DETAILS_DICTIONARY_IMPORT + 100 + '%');
                        $('#exportprogressVal').html(LANG.UI_DB_CDP_DETAILS_DICTIONARY_EXPORT + 100 + '%');
                        $('#currentObjTitle').html(LANG.UI_DB_CDP_DETAILS_CURRENT_SYNC_TABLE);
                        $('#currentSynObj').html(data.execution_object);
                        data.total_table_num == 0 ? progressVal = 0:progressVal = (data.completed_table_num / data.total_table_num) * 100;
                        $('#synTabInfo').css({width: progressVal + '%'});
                        $('#syntabprogressVal').html(LANG.UI_DB_CDP_DETAILS_TRANSFER_TABLE_PROGRESS + Math.round(progressVal) + '%');
                    }else if(data.execution_object_type == 22 || data.execution_object_type == 54){
                        //说明字典导入、导出已经加载到100%
                        $('#dictExportInfo').css({width: 100 + '%'});
                        $('#dictImportInfo').css({width: 100 + '%'});
                        $('#synTabInfo').css({width: 100 + '%'});

                        $('#importprogressVal').html(LANG.UI_DB_CDP_DETAILS_DICTIONARY_IMPORT + 100 + '%');
                        $('#exportprogressVal').html(LANG.UI_DB_CDP_DETAILS_DICTIONARY_EXPORT + 100 + '%');
                        $('#syntabprogressVal').html(LANG.UI_DB_CDP_DETAILS_TRANSFER_TABLE_PROGRESS + 100 + '%');

                        $('#currentObjTitle').html(LANG.UI_DB_CDP_CURRENT_DICT);
                        $('#currentSynObj').html(data.execution_object);
                        data.total == 0 ? progressVal = 0:progressVal = (data.completed / data.total) * 100;
                        $('#constraintDictInfo').css({width: progressVal + '%'});
                        $('#constraintprogressVal').html(LANG.UI_DB_CDP_CONSTRAINT_IMPORT + Math.round(progressVal)  + '%');
                    }else{
                        $('#progressInfo').hide();
                    }
                }
            },true);
            timerTask.dbCdpProgressInfo = setTimeout(init,updateInterval);
        };
        init();
    }

    /**
     * 点击nav触发事件
     */
    const getTabInfo = function () {
        let attrId = $(this).attr("id")
        switch (attrId) {
            case "recoveryAnytimeLi":
                $('#anyPointTime').show();
                $('#transactionInfo').hide();
                break;
            case "agentTransactionInfoLi":
                $('#anyPointTime').hide();
                $('#transactionInfo').show();
                break;
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
     * 初始化开关
     */
    const initSwitch = function (){
        // dbms接口等高级配置
        // 同步大对象等高级配置
        $('#sync_big_object_Switch').bootstrapSwitch('state', true);
        $('#timed_gen_txn_Switch').bootstrapSwitch('state', true);
        $('#replay_ddl_Switch').bootstrapSwitch('state', true);
    }

    return {
        init: function () {
            // 任务流量  5s/次
            initSpeed();
            initEchart();
            watchEchartSizeChange();
            // 运行日志 5s/次
            initLogGrid();
            // 历史任务 10s/次
            initHistoryGrid();
            // 获取当前任务的基本信息 5s/次
            initBasicInfo();
            // 获取公共接口没有的模块信息
            getBasicInfo();
            inintDatatimePicker();
            initSpinner();
            // 监控数据 5s/次
            initmonitorData();
            // 进度条
            totalpmgressbarDivControl();
            initSwitch();
            initTableHeight();
        }
    }

}()

jQuery(document).ready(function () {
    DbCdpJobDetails.init();
});