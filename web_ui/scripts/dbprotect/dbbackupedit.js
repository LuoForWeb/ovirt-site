var DbEditBackup = function () {
    var data = {srcInfo: {}, backupInfo: {}, highInfo: {}, agentInfo: {}};
    var zTreeAgent, zTreeDB = [];
    var nodeSelectFlag = false; //自定义节点选择加载标志
    var pageIndex = 0; //轮播索引
    var initOldFlag = false, initTimeFlag = false;
    var SETTINGS = null;
    var dbTypeGlob;//获取数据库类型
    var _archiveValue = 2;
    var initSpeedFlag = false;
    var speedList = [];
    var agentList = [];
    var _oldPassword = '';
    var passwordChangeFlag = false; // 修改任务时是否改变了密码框内容标记
    var firstInitPageFlag = false; // 首次进入页面标记

    var editFlag = false;
    var initStrategyFlag = false;
    var defaultStrategy = [];
    var globalStrategy = [];

    var oldNode, networkFlag = false;	//用于比对加载传输网络的节点
    var DBSubType = 0;
    const TIDB_COMPRESS_METHOD_ENUM = {
        lz4: 1,
        snappy: 2,
        zstd: 3,
    };
    var initBackupScriptFlag = false;
    var backupScriptList = [];
    let oracleCurrentIsMultiTaskFlag = false;  // 正在修改的是否为归档日志备份任务
    let oracleSkipBadBlockList = {};
    let MONGODB_CLUSTER_TYPE_ENUM = {
        single: 0,
        repset: 1,
        shard: 2,
    };
    let associatedTaskData = null;
    let sqlserverDbSimpleModeFlag = null;  // SQL Server的数据库是否为简单模式备份
    const SQLSERVER_RECOVERY_MODE_ENUM = {
        FULL: 1,  // 完整
        BULK_LOGGED: 2,  // 大容量
        SIMPLE: 3,  // 简单
    };

    /**
     * 构建MongoDB时间策略
     * @param backupInfo
     */
    const buildMongoDBTimeStrategy = backupInfo => {
        if (backupInfo.type !== 'strategy') {
            return backupInfo;
        }
        if (typeof backupInfo.fullInfo !== 'undefined' && Object.values(backupInfo.fullInfo).length) {
            backupInfo.pIncrInfo = {};
            // MongoDB增量和差异同时存在两个都取消
            if (
                typeof backupInfo.incrInfo !== 'undefined' &&
                typeof backupInfo.diffInfo !== 'undefined' &&
                Object.values(backupInfo.incrInfo).length &&
                Object.values(backupInfo.diffInfo).length
            ) {
                delete backupInfo.incrInfo;
                delete backupInfo.diffInfo;
            }
        } else {
            if (typeof backupInfo.pIncrInfo === 'undefined' || !Object.values(backupInfo.pIncrInfo).length) {
                backupInfo.pIncrInfo = backupInfo.incrInfo;
                backupInfo.pIncrInfo.mode = 9;
            }
            backupInfo.fullInfo = {};
            backupInfo.incrInfo = {};
            backupInfo.diffInfo = {};
        }
        return backupInfo;
    };

    var initData = function () {
        //step1
        data.srcInfo.dbInfo = SETTINGS.db_info;
        data.srcInfo.dbType = SETTINGS.dbtype;
        buildOldBackupScriptConfigList(parseInt(SETTINGS.dbtype));
        //step2
        //备份方式:策略/时间
        data.backupInfo.type = SETTINGS.timestrategy.type;
        //完备/日志备份/差备
        data.backupInfo.fullInfo = {};
        data.backupInfo.incrInfo = {};
        data.backupInfo.diffInfo = {};
        data.backupInfo.logInfo = {};
        data.backupInfo.pIncrInfo = {};
        var timestrategy = SETTINGS.timestrategy.data;

        //按时间备份的时间
        data.backupInfo.datetime = null;
        if (SETTINGS.timestrategy.type === "oncetime") {
            data.backupInfo.datetime = SETTINGS.timestrategy.data;
        } else {
            for (var i = 0; i < timestrategy.length; i++) {
                var info = {};
                var days = [];
                for (var j = 0; j < timestrategy[i].days.length; j++) {
                    if (timestrategy[i].days.length === 1 && !timestrategy[i].days[j]) {
                        days = [];
                    } else if (timestrategy[i].days[j]) {
                        timestrategy[i].days[j] = 1;
                        days.push(timestrategy[i].days[j]);
                    } else if (!timestrategy[i].days[j]) {
                        timestrategy[i].days[j] = 0;
                        days.push(timestrategy[i].days[j]);
                    }
                }
                info.days = days;
                info.mode = parseInt(timestrategy[i].mode);
                info.type = timestrategy[i].strategy_type;
                info.startTime = timestrategy[i].start_time;
                info.rollFlag = timestrategy[i].roll_flag;
                info.rollInterval = timestrategy[i].roll_interval;
                info.endTime = timestrategy[i].roll_end_time;
                info.frequency = timestrategy[i].frequency;
				info.full_backup_compensation_flag = timestrategy[i].full_backup_compensation_flag;
                if (info.mode === 1) {
                    data.backupInfo.fullInfo = info;
                } else if (info.mode === 2) {
                    data.backupInfo.incrInfo = info;
                } else if (info.mode === 3) {
                    data.backupInfo.diffInfo = info;
                } else if (info.mode === 4) {
                    data.backupInfo.logInfo = info;
                } else if (info.mode === 9) {
                    data.backupInfo.pIncrInfo = info;
                }
            }
        }
        //step3
        //保留策略
        data.highInfo.reserve = {
            type: SETTINGS.brs.type,
            value: SETTINGS.brs.number,
            strategyMode: SETTINGS.brs.strategyMode,  // 默认按链
            enable_flag: true,
        };
        if (CONF.BD_STORAGE_TYPE.TAPE === parseInt(SETTINGS.node.storage_type)) {  // 磁带没有保留策略
            data.highInfo.reserve.enable_flag = false;
        }

        //限速策略
        speedList = SETTINGS.speedInfo;
        data.speedLimit = SETTINGS.speedInfo;
        data.speedLimit.speedInfo = SETTINGS.speedInfo.speedInfo;
        data.speedLimit.uuid = SETTINGS.speedInfo.uuid;  // #22089

        //初始化传输信息
        data.highInfo.transfer = {};
        data.highInfo.transfer.encrypt = SETTINGS.bts.encrypt;
        data.highInfo.transfer.encrypt_method = SETTINGS.bts.encrypt_method;
        data.highInfo.transfer.mode = SETTINGS.bts.mode;
        data.highInfo.transfer.network = SETTINGS.bts.network;
        data.highInfo.transfer.threadnum = SETTINGS.bts.thread_num;

        data.highInfo.store = SETTINGS.bss;
        firstInitPageFlag = true;

        //初始化节点信息
        data.highInfo.node = {};
        data.highInfo.node.nodecheck = false;
        data.highInfo.node.nodeuuid = SETTINGS.node.nodeuuid;
        data.highInfo.node.storageuuid = SETTINGS.node.storageuuid;
        if (!data.highInfo.node.storageuuid) {
            data.highInfo.node.storagecheck = true;
        } else {
            data.highInfo.node.storagecheck = false;
        }
        //初始化代理配置
        data.agentInfo.checkdbflag = false;
        data.agentInfo.compressflag = false;
        data.agentInfo.checksumflag = false;
        data.agentInfo.archivenum = 0;
        data.agentInfo.delarchivelog = false;
        data.agentInfo.warningInfo = {};
        data.agentInfo.set_filesperset_flag = false;  // filesperset默认位置为空
        data.agentInfo.datafile_filesperset_num = 0;
        data.agentInfo.archivelog_filesperset_num = 0;
        data.agentInfo.max_object_transport_parallel_nums = 0;         // 客户端并行数量
        data.agentInfo.auto_log_backup_interval = 1;  // 日志备份间隔
        data.agentInfo.channel_count = 1;              // 通道数
        data.agentInfo.log_backup_days_flag = false;
        data.agentInfo.log_backup_days = 0;
        data.agentInfo.log_backup_times_flag = false;
        data.agentInfo.log_backup_times = 0;
        data.agentInfo.skip_inaccessible_file_flag = false;
        data.agentInfo.skip_offline_file_flag = false;
        data.agentInfo.enable_bct_flag = false;
        data.agentInfo.compress_method = 0;
        data.agentInfo.compress_level = 0;
        data.agentInfo.set_section_size_flag = false;
        data.agentInfo.section_size = 0;
        data.agentInfo.multi_task_flag = SETTINGS.agentInfo.multi_task_flag;
        data.agentInfo.set_filesperset_flag = false;  // filesperset默认位置为空
        data.agentInfo.custom_rman_cmd = '';
        data.agentInfo.ignore_resource_limiting_flag = SETTINGS.agentInfo.ignore_resource_limiting_flag;
        data.retry_strategy = SETTINGS.retry_strategy;
        data.safe_config_strategy = SETTINGS.safe_config_strategy;

        let dbType = parseInt(SETTINGS.dbtype);
        switch (dbType) {
            case CONF.DB_TYPE.SQLSERVER:
                data.agentInfo.checkdbflag = SETTINGS.agentInfo.checkdbflag;
                data.agentInfo.compressflag = SETTINGS.agentInfo.compressflag;
                data.agentInfo.checksumflag = SETTINGS.agentInfo.checksumflag;
                break;
            case CONF.DB_TYPE.ORACLE:
                data.agentInfo.compressflag = SETTINGS.agentInfo.compressflag;
                data.agentInfo.checkdbflag = SETTINGS.agentInfo.checkdbflag;
                data.agentInfo.delarchivelog = SETTINGS.agentInfo.delete_archive_log_flag;
                data.agentInfo.archivenum = SETTINGS.agentInfo.last_archive_days;
                data.agentInfo.set_filesperset_flag = SETTINGS.agentInfo.set_filesperset_flag;
                if (data.agentInfo.set_filesperset_flag) {
                    data.agentInfo.datafile_filesperset_num = SETTINGS.agentInfo.datafile_filesperset_num;
                    data.agentInfo.archivelog_filesperset_num = SETTINGS.agentInfo.archivelog_filesperset_num;
                }
                data.agentInfo.log_backup_times_flag = SETTINGS.agentInfo.log_backup_times_flag;
                if (data.agentInfo.log_backup_times_flag) {
                    data.agentInfo.log_backup_times = SETTINGS.agentInfo.log_backup_times;
                }
                data.agentInfo.log_backup_days_flag = SETTINGS.agentInfo.log_backup_days_flag;
                if (data.agentInfo.log_backup_days_flag) {
                    data.agentInfo.log_backup_days = SETTINGS.agentInfo.log_backup_days;
                }
                data.agentInfo.skip_inaccessible_file_flag = SETTINGS.agentInfo.skip_inaccessible_file_flag;
                data.agentInfo.skip_offline_file_flag = SETTINGS.agentInfo.skip_offline_file_flag;
                data.agentInfo.enable_bct_flag = SETTINGS.agentInfo.enable_bct_flag;
                data.agentInfo.set_section_size_flag = SETTINGS.agentInfo.set_section_size_flag;
                data.agentInfo.section_size = SETTINGS.agentInfo.section_size;
                data.agentInfo.channel_count = SETTINGS.agentInfo.channel_count;
                data.agentInfo.custom_rman_cmd = SETTINGS.agentInfo.custom_rman_cmd;
                break;
            case CONF.DB_TYPE.MYSQL:
            case CONF.DB_TYPE.MARIA:
                data.agentInfo.channel_count = SETTINGS.agentInfo.channel_count;
                data.agentInfo.compressflag = SETTINGS.agentInfo.compressflag;
                break;
            case CONF.DB_TYPE.DM:
                data.agentInfo.compressflag = SETTINGS.agentInfo.compressflag;
                data.agentInfo.compress_level = parseInt(SETTINGS.agentInfo.compress_level)
                data.agentInfo.delarchivelog = SETTINGS.agentInfo.delete_archive_log_flag;
                break;
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.UXDB:
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.OPENGAUSS:
            case CONF.DB_TYPE.VASTBASE:
                data.agentInfo.compressflag = SETTINGS.agentInfo.compressflag;
                data.agentInfo.delarchivelog = SETTINGS.agentInfo.delete_archive_log_flag;
                data.agentInfo.warningInfo = SETTINGS.agentInfo.warningInfo;
                break;
            case CONF.DB_TYPE.MONGODB:
                data.agentInfo.max_object_transport_parallel_nums = SETTINGS.agentInfo.max_object_transport_parallel_nums;
                break;
            case CONF.DB_TYPE.TIDB:
                // data.agentInfo.max_object_transport_parallel_nums = SETTINGS.agentInfo.max_object_transport_parallel_nums;
                data.agentInfo.compress_method = parseInt(SETTINGS.agentInfo.compress_method);
                data.agentInfo.compress_level = parseInt(SETTINGS.agentInfo.compress_level)
                break;
            case CONF.DB_TYPE.SAPHANA:
                data.agentInfo.compressflag = SETTINGS.agentInfo.compressflag;
                data.agentInfo.auto_log_backup_interval = SETTINGS.agentInfo.auto_log_backup_interval;
                data.agentInfo.channel_count = SETTINGS.agentInfo.channel_count;
                break;
        }
        let backupStrategy = {};
        //备份策略
        backupStrategy.time = {};
        backupStrategy.store = {};
        backupStrategy.reserve = {};
        backupStrategy.time.timeInfo = data.backupInfo;
        backupStrategy.time.type = data.backupInfo.type;
        backupStrategy.store.storeInfo = data.highInfo.store;
        backupStrategy.reserve.reserveInfo = data.highInfo.reserve;
        backupStrategy.speedlimit = data.speedLimit;
        if(dbType == CONF.DB_TYPE.SAPHANA) {
            backupStrategy.time.timeInfo.autoLogInfo = {
                mode: 10,
                value: data.agentInfo.auto_log_backup_interval / 60,
            }
        } else if (dbType === CONF.DB_TYPE.TIDB) {
            backupStrategy.time.timeInfo.logInfo = {};
        } else if (dbType === CONF.DB_TYPE.MONGODB) {
            backupStrategy.time.timeInfo = buildMongoDBTimeStrategy(data.backupInfo);
        }
        if (dbType === CONF.DB_TYPE.ORACLE) {
            oracleCurrentIsMultiTaskFlag = SETTINGS.agentInfo.multi_task_flag;
        }
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.DB, true, backupStrategy, dbType, afterFirstInitBackupStrategy);
    }

    /**
     * 构建之前的备份脚本配置列表
     */
    const buildOldBackupScriptConfigList = (dbType) => {
        for (const index in SETTINGS.db_info) {
            let oldDBInfo = SETTINGS.db_info[index];
            let uniqueId = index;
            let backupScriptInfo = {
                uuid: oldDBInfo.agentuuid,
                unique_id: uniqueId,
                cluster_flag: false,
                cluster_uuid: '',
                agent_uuid: oldDBInfo.agentuuid,
                instance_name: oldDBInfo.instanceuuid,
                db_name: oldDBInfo.instanceuuid,
                dir_path: oldDBInfo.dir_path,
                instance_node_list: [],
                script_config_wrapper: `backupScriptWrapper_${oldDBInfo.agentuuid}_${uniqueId}`,
                before_script_config_class: `beforeBackupScriptConfig_${oldDBInfo.agentuuid}_${uniqueId}`,
                after_script_config_class: `afterBackupScriptConfig_${oldDBInfo.agentuuid}_${uniqueId}`,
                before_task_script: oldDBInfo.before_task_script,
                after_task_script: oldDBInfo.after_task_script,
                accordion_icon: 'vicon-overview-database',
            };
            switch (dbType) {
                case CONF.DB_TYPE.ORACLE:
                    if (oldDBInfo.cluster_flag === 'cluster') {
                        backupScriptInfo.uuid = oldDBInfo.cluster_uuid;
                        backupScriptInfo.cluster_flag = true;
                        backupScriptInfo.cluster_uuid = oldDBInfo.cluster_uuid;
                        backupScriptInfo.agent_uuid = oldDBInfo.agentuuid;
                        backupScriptInfo.instance_name = oldDBInfo.instancename;
                        backupScriptInfo.db_name = oldDBInfo.dbname;
                        backupScriptInfo.script_config_wrapper = `backupScriptWrapper_${oldDBInfo.cluster_uuid}_${uniqueId}`;
                        backupScriptInfo.before_script_config_class = `beforeBackupScriptConfig_${oldDBInfo.cluster_uuid}_${uniqueId}`;
                        backupScriptInfo.after_script_config_class = `afterBackupScriptConfig_${oldDBInfo.cluster_uuid}_${uniqueId}`;
                        backupScriptInfo.accordion_icon = 'vicon-weibiaoti-2-02';

                        backupScriptList.push(backupScriptInfo);
                    } else {
                        backupScriptList.push(backupScriptInfo);
                    }
                    break;
                case CONF.DB_TYPE.TIDB:
                    backupScriptList.push(backupScriptInfo);
                    break;
                case CONF.DB_TYPE.MONGODB:
                    if (oldDBInfo.cluster_flag) {
                        backupScriptInfo.uuid = oldDBInfo.cluster_uuid;
                        backupScriptInfo.cluster_flag = true;
                        backupScriptInfo.script_config_wrapper = `backupScriptWrapper_${oldDBInfo.cluster_uuid}_${uniqueId}`;
                        backupScriptInfo.before_script_config_class = `beforeBackupScriptConfig_${oldDBInfo.cluster_uuid}_${uniqueId}`;
                        backupScriptInfo.after_script_config_class = `afterBackupScriptConfig_${oldDBInfo.cluster_uuid}_${uniqueId}`;
                        backupScriptInfo.accordion_icon = 'vicon-weibiaoti-2-02';

                        backupScriptList.push(backupScriptInfo);
                    } else {
                        backupScriptList.push(backupScriptInfo);
                    }
                    break;
                case CONF.DB_TYPE.SQLSERVER:
                case CONF.DB_TYPE.SAPHANA:
                    backupScriptInfo.db_name = oldDBInfo.dbname;
                    backupScriptList.push(backupScriptInfo);
                default:
                    backupScriptList.push(backupScriptInfo);
                    break;
            }
        }
    };

    /**
     * 初始化数据库类型下拉
     */
    const initDbTypeSelect = () => {
        const $dbType = $('#dbtype');
        for (let dbType in CONF.DB_DES) {
            dbType = parseInt(dbType);
            if (dbType === 0) {
                continue;
            }
            $dbType.append(`<option value="${dbType}">${CONF.DB_DES[dbType]}</option>`)
        }
    };

    var initListener = function () {
        // 初始化数据库类型下拉
        initDbTypeSelect();
        $('#dbtype').selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });

        $('#backuptype').on('change', backupTypeHandler);
        //选择备份策略复选框
        $('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);

        $('#oracleChannelNum').on("change", function () {
            var value = this.value;
            if (!value || value < 1) {
                $('.oracleChannelSpinner').spinner("value", 1);
            }
            if (value > 32) {
                $('.oracleChannelSpinner').spinner("value", 32);
            }
        });
        $('#mongodbBackupThreadNum').on("change", function () {
            var value = this.value;
            if (!value || value < 1) {
                $('.mongodbBackupThreadDiv').spinner("value", 1);
            }
            if (value > 16) {
                $('.mongodbBackupThreadDiv').spinner("value", 16);
            }
        });

        //归档存储告警开关
        $('#archiveAlarmCheck').bootstrapSwitch('onSwitchChange', function (e, data) {
            if (data) {
                $('.warnningdiv').show();	//开
                noticeTypeChange();
            } else {
                $('.warnningdiv').hide();	//关
            }
        });
        //告警类型选择
        $('select[name=noticetype]').on('change', noticeTypeChange);
        // //切换自动选择存储加密密码
        $('#passwordAutocheck').on('switchChange.bootstrapSwitch', () => {
            if (SETTINGS.bss.password_auto_flag) {
                passwordChangeFlag = true;
            }
        });

        //数据加密密码确认
        $('#repassword').on('input propertychange', function () {
            checkPassword();
        });
        //数据加密密码输入
        $('#password').on('input propertychange', function () {
            checkPassword();
        });
        $('#oracleFilesPerset').on('switchChange.bootstrapSwitch', filesPersetChange);
        $('#oracleFilesPersetDatafile').on("change", function () {
            if (!this.value || this.value < 1) {
                $('#oracleFilesPersetDatafileSpinner').spinner("value", 1);
            }
        });
        $('#oracleFilesPersetArchivelog').on("change", function () {
            if (!this.value || this.value < 1) {
                $('#oracleFilesPersetArchivelogSpinner').spinner("value", 1);
            }
        });

        // 压缩传输
        $('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);
        // 压缩等级改变
        $('#compressGrade').on('change', initStoreStrategyDes);
        // 告警阈值
        $('#warningpercent').on("change", function () {
            if (!this.value || this.value < 1) {
                $('#spinnerpercent').spinner("value", 1);
            } else if (this.value > 99) {
                $('#spinnerpercent').spinner("value", 99);
            }
        });
        $('#warningsize').on("change", function () {
            if (!this.value || this.value < 1) {
                $('#spinnersize').spinner("value", 1);
            } else if (this.value > 9999999) {
                $('#spinnersize').spinner("value", 9999999);
            }
        });
        $('#parallelNum').on("change", function () {
            if (!this.value || this.value < 1) {
                $('#parallelNumSpinner').spinner("value", 1);
            }

            if (this.value > 9999999) {
                $('#parallelNumSpinner').spinner("value", 1);
            }
        });
        // 传输策略---加密传输
        $('#encrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);

        // 通道数
        $('#channelCount').on('change', function () {
            if (!this.value || this.value < 1) {
                $('#channelCountSpinner').spinner('value', 1);
            }
            if (this.value > 32) {
                $('#channelCountSpinner').spinner('value', 32);
            }
        });
        // 保留x天的归档日志不删除变化
        $('#archivelogDays').on("change", archivelogDaysChange);
        // 归档日志保留策略
        $('input[name="deleteArchivelog"]').on('change', function () {
            let val = parseInt($(this).val());
            if (val === 3) {
                $('#archivelogDays').removeAttr('disabled');
            } else {
                $('#archivelogDays').attr('disabled', 'disabled');
            }
        });
        // 归档日志备份次数
        $('#archivelogBackupTimesCheck').on('switchChange.bootstrapSwitch', archivelogBackupTimesCheckChange);
        $('#archivelogBackupTimes').on("change", archivelogBackupTimesChange);
        // 归档日志备份天数
        $('#archivelogBackupDaysCheck').on('switchChange.bootstrapSwitch', archivelogBackupDaysCheckChange);
        $('#archivelogBackupDays').on("change", archivelogBackupDaysChange);
        $('#archivelogBackupDaysSpinner button').on('click', archivelogBackupDaysChange);
        // tidb压缩方式
        $('#tidbCompressMethod').on('change', tidbCompressMethodChange);
        $('#tidbCompressLevel').on("change", tidbCompressLevelChange);
        $('#tidbCompressLevelSpinner button').on('click', tidbCompressLevelChange);
        // Oracle多端传输
        $('#setSectionSizeFlag').on('switchChange.bootstrapSwitch', oracleSetSectionSizeFlagChange);
        $('#sectionSize').on("change", oracleSectionSizeChange);
        $('.sectionSizeSpinner button').on('click', oracleSectionSizeChange);
        // MySQL处理线程
        $('#mysqlParallel').on('change', mysqlParallelChange);
        $('#mysqlParallelSpinner button').on('click', mysqlParallelChange);
        // 备份脚本切换
        $('#scriptConfigSwitch').on('switchChange.bootstrapSwitch', scriptConfigChange);
        // DM-压缩
        $('#dmcompress').on('switchChange.bootstrapSwitch', dmCompressChange);
        $('#dmCompressLevel').on('change', dmCompressLevelChange);
        $('#dmCompressLevelSpinner button').on('click', dmCompressLevelChange);
        // Oracle跳过坏块-添加配置
        $('#skipDatafileBadBlockBtn').on('click', clickSkipDatafileBadBlockBtn);
        // Oracle跳过坏块Modal确定
        $('#skipDatafileBadBlockModal').on('change', '.bad-block-num-input', badBlockNumChange);
        $('#skipDatafileBadBlockSubmit').on('click', skipDatafileBadBlockSubmit);
        $('#batchBadBlockApply').on('click', batchBadBlockApply);
    }

    /**
     * Oracle跳过坏块批量应用
     */
    const batchBadBlockApply = () => {
        let rows = $('#skipDatafileBadBlockTable').bootstrapTable('getSelections');
        if (!rows.length) {
            return;
        }
        let batchValue = parseInt($('#batchBadBlockNum').val());
        for (const row of rows) {
            $(`#${row.skip_id}`).val(batchValue);
        }
    };

    /**
     * Oracle跳过坏块Modal确定
     */
    const skipDatafileBadBlockSubmit = () => {
        let rows = $('#skipDatafileBadBlockTable').bootstrapTable('getSelections');
        if (!rows.length) {
            UIToastr.showWarning(LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_NUM, LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_NUM_TIPS1);
            return;
        }
        let skipDatafileBadBlockModalTag = $('#skipDatafileBadBlockModal');
        let agentUuid = skipDatafileBadBlockModalTag.data('agent-uuid');
        let clusterUuid = skipDatafileBadBlockModalTag.data('cluster-uuid');
        let eventtype = skipDatafileBadBlockModalTag.data('eventtype');
        let instanceName =skipDatafileBadBlockModalTag.data('name');
        let key = agentUuid + '_' + instanceName;
        if (eventtype === 'cluster') {
            key = clusterUuid;
        }
        let tableSpaceIndex = skipDatafileBadBlockModalTag.data('table-space-index');
        let tableSpaceName = skipDatafileBadBlockModalTag.data('table-space-name');
        for (let index = oracleSkipBadBlockList[key].configure_table_space_list.length - 1; index >= 0; index--) {
            let datafileInfo = oracleSkipBadBlockList[key].configure_table_space_list[index];
            if (datafileInfo.table_space_name === tableSpaceName) {
                if (eventtype === 'cluster' && datafileInfo.cluster_uuid === clusterUuid) {
                    oracleSkipBadBlockList[key].configure_table_space_list.splice(index, 1);
                } else if (eventtype === 'instance' && datafileInfo.agent_uuid === agentUuid && datafileInfo.instance_name === instanceName) {
                    oracleSkipBadBlockList[key].configure_table_space_list.splice(index, 1);
                }
            }
        }
        for (const row of rows) {
            let badBlockNum = parseInt($(`#${row.skip_id}`).val());
            if (isNaN(badBlockNum) || badBlockNum < 0) {
                UIToastr.showWarning(LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_NUM, LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_NUM_TIPS2);
                return;
            }
            oracleSkipBadBlockList[key].configure_table_space_list.push({
                file_id: row.file_id,
                file_name: row.file_name,
                bad_block_num: badBlockNum,
                agent_uuid: agentUuid,
                cluster_uuid: clusterUuid,
                eventtype: eventtype,
                instance_name: instanceName,
                table_space_name: tableSpaceName,
            });
        }
        // 显示已经配置
        $(`#skipDatafileBadBlockConfigure_${key} .skip-datafile-bad-block-configure_item[data-table-space-index="${tableSpaceIndex}"] .table-space-result`).show();
        skipDatafileBadBlockModalTag.modal('hide');
    };

    /**
     * Oracle跳过坏块-数量变化
     */
    const badBlockNumChange = function () {
        let val = parseInt(this.value);
        if (isNaN(val)) {
            $(this).val(0);
            $(`#skipDatafileBadBlockTable`).bootstrapTable('uncheckBy', {field: 'skip_id', values: [$(this).attr('id')]});
        } else if (val < 0) {
            $(this).val(0);
            $(`#skipDatafileBadBlockTable`).bootstrapTable('uncheckBy', {field: 'skip_id', values: [$(this).attr('id')]});
        } else if (val === 0) {
            $(`#skipDatafileBadBlockTable`).bootstrapTable('uncheckBy', {field: 'skip_id', values: [$(this).attr('id')]});
        } else if (val > 9999) {
            $(this).val(9999);
            $(`#skipDatafileBadBlockTable`).bootstrapTable('checkBy', {field: 'skip_id', values: [$(this).attr('id')]});
        } else {
            $(this).val(val);
            $(`#skipDatafileBadBlockTable`).bootstrapTable('checkBy', {field: 'skip_id', values: [$(this).attr('id')]});
        }
    };

    /**
     * Oracle跳过坏块-添加配置
     */
    const clickSkipDatafileBadBlockBtn = () => {
        let key = $('#skipDatafileBadBlock').val();
        if (typeof oracleSkipBadBlockList[key] !== 'undefined' && !oracleSkipBadBlockList[key].edit_init_flag) {
            return;
        }
        if (typeof oracleSkipBadBlockList[key] !== 'undefined') {
            oracleSkipBadBlockList[key].edit_init_flag = false;
        }
        initOracleSkipDatafileBadBlockContent();
    };

    /**
     * DM-压缩等级变化
     */
    const dmCompressLevelChange = function () {
        let dmCompressLevel = $('#dmCompressLevel');
        let value = parseInt(dmCompressLevel.val());
        if (!dmCompressLevel.val() || value < 1) {
            value = 1;
        } else if (value > 9) {
            value = 9;
        }
        $('#dmCompressLevelSpinner').spinner('value', value);
    };

    /**
     * DM压缩切换
     */
    const dmCompressChange = function () {
        if (this.checked) {
            $('.dmCompressLevelDiv').show();
        } else {
            $('.dmCompressLevelDiv').hide();
        }
    };

    /**
     * MySQL处理线程变化
     */
    const mysqlParallelChange = function () {
        let mysqlParallel = $('#mysqlParallel');
        let value = parseInt(mysqlParallel.val());
        if (!mysqlParallel.val() || value < 1) {
            value = 1;
        } else if (value > 16) {
            value = 16;
        }
        $('#mysqlParallelSpinner').spinner('value', value);
    };

    /**
     * 备份脚本切换
     */
    const scriptConfigChange = function () {
        if (this.checked) {
            $('.scriptConfigDiv').show();
            if (!initBackupScriptFlag) {
                initBackupScript();
            }
        } else {
            $('.scriptConfigDiv').hide();
        }
    };

    /**
     * 初始化备份脚本
     */
    const initBackupScript = () => {
        Metronic.blockUI({target: '.scriptConfigSwitchDiv', animate: true});
        // 追加备份脚本内容
        initBackupScriptFlag = true;
        let scriptConfigTag = $('#scriptConfig');
        scriptConfigTag.html(``);  // 清除之前数据
        let collapseFlag = true;
        for (const backupScriptInfo of backupScriptList) {
            let html = `<div class="${backupScriptInfo.script_config_wrapper}">
                <div class="accordion scriptConfigOne">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled popovers ${collapseFlag ? '' : 'collapsed'}" data-container="body" data-trigger="hover"
                                    data-placement="top" data-toggle="collapse" data-parent=".scriptConfigOne" href="#scriptConfigPane_${backupScriptInfo.unique_id}"
                                    aria-expanded="${collapseFlag ? 'true' : 'false'}">
                                    <i class="viconfont ${backupScriptInfo.accordion_icon} font-green-seagreen"></i>
                                    <span class="font-green-seagreen">${backupScriptInfo.dir_path}</span>
                                </a>
                            </h4>
                        </div>

                        <div id="scriptConfigPane_${backupScriptInfo.unique_id}" class="panel-collapse collapse ${collapseFlag ? 'in' : ''}" aria-expanded="${collapseFlag ? 'true' : 'false'}">
                            <div class="panel-body" style="display: flex;">
                                <div class="col-md-2 nav-tab-radios" style="padding: 0">
                                    <ul class="nav nav-tabs tabs-left" style="display: flex; flex-direction: column; justify-content: flex-start; height: 100%;">
                                        <li class="active">
                                            <a href="#tab_before_backup_${backupScriptInfo.unique_id}" data-toggle="tab" aria-expanded="true">${LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT}</a>
                                        </li>
                                        <li>
                                            <a href="#tab_after_backup_${backupScriptInfo.unique_id}" data-toggle="tab" aria-expanded="false">${LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT}</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-10">
                                    <div class="tab-content" style="height: 100%">
                                        <div class="tab-pane active in" id="tab_before_backup_${backupScriptInfo.unique_id}">
                                            <div class="${backupScriptInfo.before_script_config_class}"></div>
                                        </div>
                                        <div class="tab-pane fade" id="tab_after_backup_${backupScriptInfo.unique_id}">
                                            <div class="${backupScriptInfo.after_script_config_class}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
            scriptConfigTag.append(html);
            $.fn.initVinScript({
                class: backupScriptInfo.before_script_config_class,
                script_details: backupScriptInfo.before_task_script,
            });
            $.fn.initVinScript({
                class: backupScriptInfo.after_script_config_class,
                script_details: backupScriptInfo.after_task_script,
            });
            collapseFlag = false;
        }
        Metronic.unblockUI('.scriptConfigSwitchDiv');
    };

    /**
     * Oracle分块大小变化
     */
    const oracleSectionSizeChange = function () {
        let sectionSize = $('#sectionSize');
        let value = parseInt(sectionSize.val());
        if (!sectionSize.val() || value < 1) {
            value = 1;
        } else if (value > 1024) {
            value = 1024;
        }
        $('.sectionSizeSpinner').spinner("value", value);
    };

    /**
     * Oracle多端传输开关切换了
     */
    const oracleSetSectionSizeFlagChange = function () {
        if (this.checked) {
            $('.sectionSizeDiv').show();
        } else {
            $('.sectionSizeDiv').hide()
        }
    };

    /**
     * tidb压缩等级变化
     */
    const tidbCompressLevelChange = function () {
        let tidbCompressLevelTag = $('#tidbCompressLevel');
        let value = parseInt(tidbCompressLevelTag.val());
        if (!tidbCompressLevelTag.val() || value < 1) {
            value = 1;
        } else if (value > 16) {
            value = 16;
        }
        $('#tidbCompressLevelSpinner').spinner("value", value);
    };

    /**
     * tidb压缩方式变化
     */
    const tidbCompressMethodChange = function () {
        let value = parseInt($(this).val());
        if (value === TIDB_COMPRESS_METHOD_ENUM.zstd) {
            $('.tidbCompressLevelDiv').show();
        } else {
            $('.tidbCompressLevelDiv').hide();
        }

    };

    const archivelogBackupDaysChange = function () {
        let archivelogBackupDaysTag = $('#archivelogBackupDays');
        let spinnerTag = $('#archivelogBackupDaysSpinner');
        let value = parseInt(archivelogBackupDaysTag.val());
        if (spinnerTag.data('spinner') === undefined) {
            return;
        }
        // 归档日志备份天数要大于归档日志的备份类别+1
        let timeStrategyArchiveDays = getTimeStrategyArchivelogDays();
        if (value <= timeStrategyArchiveDays || !archivelogBackupDaysTag.val()) {
            value = timeStrategyArchiveDays;
        } else if (value > 999) {
            value = 32;
        }
        spinnerTag.spinner("value", value);
    };

    const archivelogBackupDaysCheckChange = function () {
        if (this.checked) {
            $('.archivelogBackupDaysDiv').show();
            archivelogBackupDaysChange();
        } else {
            $('.archivelogBackupDaysDiv').hide();
        }
    };

    const archivelogBackupTimesChange = function () {
        let value = parseInt(this.value);
        if (value < 1 || !this.value) {
            value = 1;
        } else if (value > 999) {
            value = 999;
        }
        $('#archivelogBackupTimesSpinner').spinner("value", value);
    }

    const archivelogBackupTimesCheckChange = function () {
        if (this.checked) {
            $('.archivelogBackupTimesDiv').show();
        } else {
            $('.archivelogBackupTimesDiv').hide();
        }
    };

    const archivelogDaysChange = function () {
        let value = parseInt(this.value);
        if (value <= 0 || !this.value) {
            value = 1;
        } else if (value > 9999) {
            value = 7;
        }
        $('#archivelogDays').val(value);
    };

    // 显示加密算法
    var transferEncryptChange = function(){
        if(this.checked){
            $('.transfer-encrypt-method-form').show();
        }else{
            $('.transfer-encrypt-method-form').hide();
        }
    }

    // 显示压缩等级
    var compressChange = function () {
        if (this.checked) {
            $('.compressGradeDiv').show();
        } else {
            $('.compressGradeDiv').hide();
        }
    };

    var filesPersetChange = function () {
        if (this.checked) {
            if (!oracleCurrentIsMultiTaskFlag) {  // 归档日志备份任务没有设置数据文件个数
                $('#oracleFilesPersetDatafileDiv').show();
            } else {
                $('#oracleFilesPersetDatafileDiv').hide();
            }
            $('#oracleFilesPersetArchivelogDiv').show();
        } else {
            $('#oracleFilesPersetDatafileDiv').hide();
            $('#oracleFilesPersetArchivelogDiv').hide();
        }
    }

    //数据加密密码确认检测
    var checkPassword = function () {
        passwordChangeFlag = true;
    }

    //切换自动选择存储加密密码
    var passwordModeChange = function () {
        if ($('#passwordAutocheck').bootstrapSwitch('state')) {
            $('#password').val('');
            $('#repassword').val('');
            $('.passwordDiv').hide();
        } else {
            if (firstInitPageFlag) {
                passwordChangeFlag = false;
            } else {
                $('#password').attr('placeholder', LANG.UI_PUBLIC_ENTER_PASSWORD);
                $('#repassword').attr('placeholder', LANG.UI_PUBLIC_ENTER_REPASSWORD);
                passwordChangeFlag = true;
            }
            $('.passwordDiv').show();
        }

        firstInitPageFlag = false;
    }

    //存储加密切换
    var encryptChange = function () {
        if (this.checked) {
            $('#passwordAutocheck').bootstrapSwitch('state', true);
            $('#password').empty();
            $('#repassword').empty();
            $('.passwordModeDiv').show();
            $('.storage-encrypt-div').show();
        } else {
            $('.passwordModeDiv').hide();
            $('.passwordDiv').hide();
            $('.storage-encrypt-div').hide();
        }
    }

    //归档存储告警选择
    var noticeTypeChange = function () {
        var noticeType = parseInt($('select[name=noticetype]').val());
        if (1 === noticeType) {
            $('#percentdiv').show();
            $('#sizediv').hide();
        } else if (2 === noticeType) {
            $('#percentdiv').hide();
            $('#sizediv').show();
        }
    }

    //获取归档告警传参
    var getWarningSettings = function () {
        var warningSettings = {};
        warningSettings.warn_check = $('#archiveAlarmCheck').get(0).checked;
        warningSettings.warn_type = parseInt($('select[name=noticetype]').val());
        if (1 === warningSettings.warn_type) {
            warningSettings.warn_value = $('#warningpercent').val();
        } else {
            warningSettings.warn_value = $('#warningsize').val() * 1024 * 1024 * 1024;
        }
        return warningSettings;
    }

    //初始化时间策略描述
    var initSpeedStrategyDes = function () {
        var titleDes = "";
        var des = "";
        if (speedList.length == 0) {
            return;
        }

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
            }, 1500);
        } else {
            var speedInfo = speedList.speedInfo;
            // 自定义
            if (speedInfo.length > 0) {
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedInfo.length;
            }

            var strategy_type = speedList.strategy_type;
            for (var i in speedInfo) {
                titleDes += speedInfo[i].des + '. ';
            }
            addGlobalStrategy.init({'strategy_type': strategy_type, speedInfo: speedInfo, initSpeedFlag: 1});
            $('#speedList li.popovers').popover();

            setTimeout(function () {
                $('.speedlimitDes').html(des);
                $('.speedlimitDes').prop('title', titleDes);
            }, 1000);
        }
    }

    var checkTime = function (start, end) {
        var startnum = new Date("1970-01-01" + " " + start).getTime();
        var endnum = new Date("1970-01-01" + " " + end).getTime();
        if (endnum <= startnum && parseInt($("#speedModeType").val()) === 1) {//按策略限速才判断
            UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
            return false;
        } else {
            return true;
        }
    }

    //初始化存储策略配置信息
    var initStoreStrategyDes = function () {
        var des = "";
        des += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationCheck').get(0).checked) + ", ";
        des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);
        // 压缩等级
        if ($('#compressCheck').get(0).checked) {
            var gradeValue = $('#compressGrade').val();
            var grade = '';
            switch (parseInt(gradeValue, 10)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                    break;
                case 3:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                    break;
                case 4:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                    break;
            }
            des += "," + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
        }
        des += "," + LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);
        // 存储加密算法
        if ($('#encryptStorageCheck').get(0).checked) {
            var encryptedMethodLabel = $('.storage-encrypt-label').html();
            let method = $('#storageEncryptMethod').val();
            var grade = '';
            switch (parseInt(method)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            }
            des += "," + encryptedMethodLabel + ": " + grade;
        }
        $('.storeDes').html(des);
        $('.storeDes').attr('title', des);
    }

    const getTimeStrategyArchivelogDays = () => {
        let mode = $('.strategy-panel[data-mode=4] .dwm.active').data('type');
        let archiveValue = 2;
        switch (mode) {
            case 1:
                archiveValue = 1 + 1;	//选择每天归档天数+1
                break;
            case 2:
                archiveValue = 7 + 1;	//选择每周归档天数+1
                break;
            case 3:
                archiveValue = 31 + 1;	//选择每月归档天数+1
                break;
        }
        return archiveValue;
    };

    var strategyOnChange = function(event){
        setTimeout(() => {
            if ($('#archivelogBackupDaysCheck').get(0).checked) {
                let tag = $('#archivelogBackupDays');
                let value = parseInt(tag.val());
                let targetValue = getTimeStrategyArchivelogDays();
                if (value < targetValue) {
                    tag.val(targetValue).trigger('change');
                }
            }
        }, 200);
    }

    /**
     * 时间策略点击-MongoDB
     * @param {int} mode
     */
    var timeStrategyModeClickMongoDB = function (mode) {
        /**
         * 1、增量备份与差异备份互斥，不能同时选择增量备份和差异备份
         * 2、永久备份与完全备份、增量备份、差异备份互斥，选择了永久增量就不能选择完全备份、增量备份、差异备份
         * 3、日志备份在所有情况下都能选择
         * 4、必须选择完全备份或永久增量，选择了增量备份或差异备份必须选择完全备份
         * 5、所有的备份策略选择组合为：完全备份[增量备份|差异备份][日志备份]、永久增量[日志备份]。
         */
        switch (mode) {
            case 1:  // 完备
                // 取消永久增量备份
                $('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
                break
            case 2:  // 增量
                // 取消永久增量备份
                $('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
                // 取消差异备份
                $('#strategymode').find('input[data-mode=3]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
                // 选择完全备份
                $('#strategymode').find('input[data-mode=1]').iCheck('check');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
                break
            case 3:  // 差异
                // 取消永久增量备份
                $('#strategymode').find('input[data-mode=9]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=9]').hide();
                // 取消增量备份
                $('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
                // 选择完全备份
                $('#strategymode').find('input[data-mode=1]').iCheck('check');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
                break
            case 4:  // 日志
                // 日志备份所有都支持
                break
            case 9:  // 永久增量
                // 取消完全备份
                $('#strategymode').find('input[data-mode=1]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').hide();
                // 取消增量备份
                $('#strategymode').find('input[data-mode=2]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
                // 取消差异备份
                $('#strategymode').find('input[data-mode=3]').iCheck('uncheck');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
                break
        }
    }

    /**
     * 时间策略点击-SAP HANA
     * @param mode
     */
    var timeStrategyModeClickSAPHANA = function (mode) {
        /**
         * 增量和差异不能同时选
         */
        switch (mode) {
            case 2:  // 增量
                // 选择完全备份
                $('#strategymode').find('input[data-mode=1]').iCheck('check');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
                break
            case 3:  // 差异
                // 选择完全备份
                $('#strategymode').find('input[data-mode=1]').iCheck('check');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
                break
        }
    }

    /**
     * 时间策略点击-TiDB
     * @param mode
     */
    const timeStrategyModeClickTiDB = (mode, checked) => {
        /**
         * 选择日志备份，不显示具体策略
         */
        switch (mode) {
            case 1:  // 完备
                if ($('#logBackup').is(':checked')) {
                    $('#stragegyaccordion').find('.strategy-panel[data-mode=4]').hide();
                    setTimeout(() => {
                        // 设置时间策略描述
                        if (checked) {
                            let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
                            let des = strategyConfig.fullInfo.des + '.';
                            des += LANG.UI_STRATEGY_LOG + '.';
                            $('.backupTimeDes').html(des);
                        } else {
                            $('.backupTimeDes').html(LANG.UI_STRATEGY_LOG + '.');
                        }
                    }, 0);
                }
                if (checked) {
                    // 关闭完全备份补偿
                    $(`#fullSkipSwitch`).bootstrapSwitch('state', false);
                }
                break;
            case 4:  // 日志
                if (!checked) {
                    return;
                }
                // 选择完全备份
                $('#strategymode').find('input[data-mode=1]').iCheck('check');
                $('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
                $('#stragegyaccordion').find('.strategy-panel[data-mode=4]').hide();
                setTimeout(() => {
                    // 设置时间策略描述
                    let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
                    let des = strategyConfig.fullInfo.des + '.';
                    des += LANG.UI_STRATEGY_LOG + '.';
                    $('.backupTimeDes').html(des);
                }, 0);
                break;
        }
    };

    /**
     * 时间策略点击了SQL Server
     * @param mode
     * @param checked
     */
    const timeStrategyModeClickSQLServer = (mode, checked) => {
        switch (mode) {
            case 4:  // 日志
                setTimeout(() => {
                    if (sqlserverDbSimpleModeFlag === true && checked) {  // SQL Server数据库简单模式不能选择日志备份
                        UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, LANG.UI_DB_BACKUP_SQLSERVER_SIMPLE_MODE_NONSUPPORT_LOG_BACKUP);
                        $('#strategymode').find('input[data-mode=4]').iCheck('uncheck');
                        $('#stragegyaccordion').find('.strategy-panel[data-mode=4]').hide();
                    } else if (sqlserverDbSimpleModeFlag === false && !checked) {  // SQL Server数据库非简单模式不能取消日志备份
                        $('#strategymode').find('input[data-mode=4]').iCheck('check');
                        $('#stragegyaccordion').find('.strategy-panel[data-mode=4]').show();
                    }
                }, 0);
                break;
        }
    };

    //选中或取消某个时间策略
    var strategyModeClick = function (event) {
        var mode = parseInt($(this).data('mode'));
        if (event.target.checked) {
            //如果是取消选中
            $('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').hide();
            switch (data.srcInfo.dbType) {
                case CONF.DB_TYPE.TIDB:
                    timeStrategyModeClickTiDB(mode, false);
                    break;
                case CONF.DB_TYPE.SQLSERVER:
                    timeStrategyModeClickSQLServer(mode, false);
                    break;
            }
        } else {
            //如果是选中
            $('#stragegyaccordion').find('.strategy-panel[data-mode=' + mode + ']').show();
            switch (data.srcInfo.dbType) {
                case CONF.DB_TYPE.MONGODB:  // MongoDB时间策略的互斥操作
                    timeStrategyModeClickMongoDB(mode);
                    break;
                case CONF.DB_TYPE.SAPHANA:
                    timeStrategyModeClickSAPHANA(mode);
                    break;
                case CONF.DB_TYPE.TIDB:
                    timeStrategyModeClickTiDB(mode, true);
                    break;
                case CONF.DB_TYPE.SQLSERVER:
                    timeStrategyModeClickSQLServer(mode, true);
                    break;
            }
        }
        if (data.srcInfo.dbType === CONF.DB_TYPE.MONGODB) {
            // 刷新WORM保护
            if (mode === 9) {
                setTimeout(initWormStrategy, 0);
            }
        }
    }

    /**
     * 初始化备份目标
     */
    const initBackupTarget = (callback) => {
        Metronic.blockUI({target: '#dbbackupcontent', animate: true});
        let dbType = parseInt($('#dbtype').val());
        let options = {
            node_uuid: SETTINGS.node.nodeuuid,
            node_pool_uuid: SETTINGS.node.node_pool_uuid,
            storage_uuid: SETTINGS.node.storageuuid,
            storage_pool_uuid: SETTINGS.node.storage_pool_uuid,
            storage_pool_type: SETTINGS.node.storage_pool_type,
            after_init_callback: () => {
                Metronic.unblockUI('#dbbackupcontent');
                callback();
            },
        };
        switch (dbType) {
            case CONF.DB_TYPE.TIDB:
            case CONF.DB_TYPE.MONGODB:
                options.exclude_storage_type_list = [
                    CONF.BD_STORAGE_TYPE.REMOTE,  // 备份目标默认不支持远程备份存储
                    CONF.BD_STORAGE_TYPE.HUAWEICBR,  // 备份目标默认不支持华为CBR
                    CONF.BD_STORAGE_TYPE.TAPE,  // 不支持磁带存储
                ];
                break;
        }
        $('#backupTarget').backupTarget(options);
    };

    var initSpinner = function () {
        if (parseInt(SETTINGS.brs.type) === 2) {
            initReserveSpinner($('#spinnerDay'), SETTINGS.brs.number);
            initReserveSpinner($('#spinnerNum'));
        } else {
            initReserveSpinner($('#spinnerNum'), SETTINGS.brs.number);
            initReserveSpinner($('#spinnerDay'));
        }
        $('#speedSpinnerNum').spinner({value: 10, step: 5, min: 1, max: 10000000000});

        if (SETTINGS.bts && SETTINGS.bts.thread_num) {
            if (SETTINGS.dbtype === CONF.DB_TYPE.ORACLE) {
                $('.oracleChannelSpinner').spinner({value: SETTINGS.bts.thread_num, step: 1, min: 1, max: 32});
            } else {
                $('.mongodbBackupThreadDiv').spinner({value: SETTINGS.bts.thread_num, step: 1, min: 1, max: 16});
            }
        } else {
            if (SETTINGS.dbtype === CONF.DB_TYPE.ORACLE) {
                $('.oracleChannelSpinner').spinner({value: 4, step: 1, min: 1, max: 32});
            } else {
                $('.mongodbBackupThreadDiv').spinner({value: 1, step: 1, min: 1, max: 16});
            }
        }

        if (SETTINGS.agentInfo.warningInfo && SETTINGS.agentInfo.warningInfo.warn_check) {
            if (parseInt(SETTINGS.agentInfo.warningInfo.warn_type) === 1) {
                //百分比
                $('#spinnerpercent').spinner({
                    value: SETTINGS.agentInfo.warningInfo.warn_value,
                    step: 5,
                    min: 1,
                    max: 99
                });
                $('#spinnersize').spinner({value: 5, step: 5, min: 1, max: 9999999});
            } else {
                //按容量告警取值B转换为GB
                $('#spinnerpercent').spinner({value: 20, step: 5, min: 1, max: 99});
                $('#spinnersize').spinner({
                    value: SETTINGS.agentInfo.warningInfo.warn_value,
                    step: 5,
                    min: 1,
                    max: 9999999
                });
            }
        } else {
            $('#spinnerpercent').spinner({value: 20, step: 5, min: 1, max: 99});
            $('#spinnersize').spinner({value: 5, step: 5, min: 1, max: 9999999});
        }
        if (SETTINGS.agentInfo.set_filesperset_flag) {
            $('#oracleFilesPersetDatafileSpinner').spinner({
                value: SETTINGS.agentInfo.datafile_filesperset_num,
                step: 1,
                min: 1,
                max: 64
            });
            $('#oracleFilesPersetArchivelogSpinner').spinner({
                value: SETTINGS.agentInfo.archivelog_filesperset_num,
                step: 1,
                min: 1,
                max: 64
            });

        } else {
            $('#oracleFilesPersetDatafileSpinner').spinner({value: 64, step: 1, min: 1, max: 64});
            $('#oracleFilesPersetArchivelogSpinner').spinner({value: 64, step: 1, min: 1, max: 64});
        }
        if (SETTINGS.dbtype === CONF.DB_TYPE.MONGODB) {  // 客户端并行数量
            $('#parallelNumSpinner').spinner({value: SETTINGS.agentInfo.max_object_transport_parallel_nums, step: 1, min: 1, max: 9999999});
        }
        if (SETTINGS.dbtype === CONF.DB_TYPE.SAPHANA) {
            $('#channelCountSpinner').spinner({value: SETTINGS.agentInfo.channel_count, step: 1, min: 1, max: 32});
        }
        if (SETTINGS.agentInfo.log_backup_times_flag) {
            $('#archivelogBackupTimesSpinner').spinner({value: SETTINGS.agentInfo.log_backup_times, step: 1, min: 1, max: 999});
        } else {
            $('#archivelogBackupTimesSpinner').spinner({value: 1, step: 1, min: 1, max: 999});
        }
        if (SETTINGS.agentInfo.log_backup_days_flag) {
            $('#archivelogBackupDaysSpinner').spinner({value: SETTINGS.agentInfo.log_backup_days, step: 1, min: 1, max: 999});
        } else {
            $('#archivelogBackupDaysSpinner').spinner({value: 2, step: 1, min: 1, max: 999});
        }
        // TiDB等级
        if (SETTINGS.agentInfo.compress_method === TIDB_COMPRESS_METHOD_ENUM.zstd) {
            $('#tidbCompressLevelSpinner').spinner({value: SETTINGS.agentInfo.compress_level, step: 1, min: 1, max: 16});
        } else {
            $('#tidbCompressLevelSpinner').spinner({value: 1, step: 1, min: 1, max: 16});
        }
        // Oracle分块大小
        if (SETTINGS.agentInfo.set_section_size_flag) {
            $('.sectionSizeSpinner').spinner({value: SETTINGS.agentInfo.section_size, step: 1, min: 1, max: 1024});
        } else {
            $('.sectionSizeSpinner').spinner({value: 1, step: 1, min: 1, max: 1024});
        }
        // MySQL压缩
        $('#mysqlParallelSpinner').spinner({value: SETTINGS.agentInfo.channel_count, step: 1, min: 1, max: 16});
        // DM压缩等级
        if (SETTINGS.agentInfo.compressflag) {
            $('#dmCompressLevelSpinner').spinner({value: SETTINGS.agentInfo.compress_level, step: 1, min: 1, max: 9});
        } else {
            $('#dmCompressLevelSpinner').spinner({value: 5, step: 1, min: 1, max: 9});
        }
    }

    var backupTypeHandler = function () {
        data.backupInfo.type = this.value;
        $('.settimetip').hide();
        setTimeout(() => {
            setMultiTaskTimeStrategy();
            setAfterStrategyData();
        }, 0);
    }

    /**
     * 构建备份脚本配置列表
     */
    const buildBackupScriptConfigList = (dbType) => {
        // 备份脚本
        initBackupScriptFlag = false;
        backupScriptList = [];
        for (let i = 0; i < agentList.length; i++) {
            let nodes = zTreeDB[agentList[i]].getCheckedNodes();
            for (const j in nodes) {
                let uniqueId = `${i}_${j}`;
                let treeNode = nodes[j];
                let backupScriptInfo = {
                    uuid: treeNode.agent_uuid,
                    unique_id: uniqueId,
                    cluster_flag: false,
                    cluster_uuid: '',
                    agent_uuid: treeNode.agent_uuid,
                    instance_name: treeNode.instance_name,
                    db_name: treeNode.instance_name,
                    dir_path: treeNode.name,
                    instance_node_list: [],
                    script_config_wrapper: `backupScriptWrapper_${treeNode.agent_uuid}_${uniqueId}`,
                    before_script_config_class: `beforeBackupScriptConfig_${treeNode.agent_uuid}_${uniqueId}`,
                    after_script_config_class: `afterBackupScriptConfig_${treeNode.agent_uuid}_${uniqueId}`,
                    before_task_script: undefined,
                    after_task_script: undefined,
                    accordion_icon: 'vicon-overview-database',
                };
                switch (dbType) {
                    case CONF.DB_TYPE.ORACLE:
                        if (treeNode.eventtype === 'cluster') {
                            backupScriptInfo.uuid = treeNode.cluster_uuid;
                            backupScriptInfo.cluster_flag = true;
                            backupScriptInfo.cluster_uuid = treeNode.cluster_uuid;
                            backupScriptInfo.db_name = treeNode.app_service_name;
                            backupScriptInfo.instance_node_list = treeNode.instance_node_list;
                            backupScriptInfo.script_config_wrapper = `backupScriptWrapper_${treeNode.cluster_uuid}_${uniqueId}`;
                            backupScriptInfo.before_script_config_class = `beforeBackupScriptConfig_${treeNode.cluster_uuid}_${uniqueId}`;
                            backupScriptInfo.after_script_config_class = `afterBackupScriptConfig_${treeNode.cluster_uuid}_${uniqueId}`;
                            backupScriptInfo.accordion_icon = 'vicon-weibiaoti-2-02';
                        }
                        backupScriptList.push(backupScriptInfo);
                        break;
                    case CONF.DB_TYPE.DM:
                    case CONF.DB_TYPE.POSTGRE:
                    case CONF.DB_TYPE.ANTDB:
                    case CONF.DB_TYPE.KINGBASE:
                    case CONF.DB_TYPE.UXDB:
                    case CONF.DB_TYPE.HIGHGO:
                    case CONF.DB_TYPE.OPENGAUSS:
                    case CONF.DB_TYPE.VASTBASE:
                    case CONF.DB_TYPE.MYSQL:
                    case CONF.DB_TYPE.MARIA:
                    case CONF.DB_TYPE.MONGODB:
                        if (treeNode.eventtype === 'cluster') {
                            backupScriptInfo.uuid = treeNode.cluster_uuid;
                            backupScriptInfo.cluster_flag = true;
                            backupScriptInfo.cluster_uuid = treeNode.cluster_uuid;
                            backupScriptInfo.instance_node_list = treeNode.instance_node_list;
                            backupScriptInfo.script_config_wrapper = `backupScriptWrapper_${treeNode.cluster_uuid}_${uniqueId}`;
                            backupScriptInfo.before_script_config_class = `beforeBackupScriptConfig_${treeNode.cluster_uuid}_${uniqueId}`;
                            backupScriptInfo.after_script_config_class = `afterBackupScriptConfig_${treeNode.cluster_uuid}_${uniqueId}`;
                            backupScriptInfo.accordion_icon = 'vicon-weibiaoti-2-02';
                        }
                        backupScriptList.push(backupScriptInfo);
                        break;
                    case CONF.DB_TYPE.TIDB:
                        if (treeNode.eventtype === 'cluster') {
                            let agentUuid = treeNode.agent_uuid;
                            for (const instanceNode of treeNode.instance_node_list) {
                                if (instanceNode.app_detail.node_role.search('deploy') > -1) {  // TiDB使用中控机的客户端uuid
                                    agentUuid = instanceNode.agent_uuid;
                                }
                            }
                            backupScriptInfo.uuid = treeNode.cluster_uuid;
                            backupScriptInfo.cluster_flag = true;
                            backupScriptInfo.cluster_uuid = treeNode.cluster_uuid;
                            backupScriptInfo.agent_uuid = agentUuid;
                            backupScriptInfo.instance_node_list = treeNode.instance_node_list;
                            backupScriptInfo.script_config_wrapper = `backupScriptWrapper_${treeNode.cluster_uuid}_${uniqueId}`;
                            backupScriptInfo.before_script_config_class = `beforeBackupScriptConfig_${treeNode.cluster_uuid}_${uniqueId}`;
                            backupScriptInfo.after_script_config_class = `afterBackupScriptConfig_${treeNode.cluster_uuid}_${uniqueId}`;
                            backupScriptInfo.accordion_icon = 'vicon-weibiaoti-2-02';
                        }
                        backupScriptList.push(backupScriptInfo);
                        break;
                    case CONF.DB_TYPE.SQLSERVER:
                    case CONF.DB_TYPE.SAPHANA:
                        if ('db' === treeNode.eventtype) {  // SQL Server选择的是数据库
                            backupScriptInfo.db_name = treeNode.name;
                            if (treeNode.cluster_flag) {
                                backupScriptInfo.uuid = treeNode.cluster_uuid + '_' + treeNode.name;
                                backupScriptInfo.cluster_flag = treeNode.cluster_flag;
                                backupScriptInfo.cluster_uuid = treeNode.cluster_uuid;
                                backupScriptInfo.dir_path = treeNode.cluster_instance_show_name + '/' + treeNode.instance_name + '/' + treeNode.name;
                                backupScriptInfo.instance_node_list = treeNode.instance_node_list;
                                backupScriptInfo.script_config_wrapper = `backupScriptWrapper_${treeNode.cluster_uuid}_${uniqueId}`;
                                backupScriptInfo.before_script_config_class = `beforeBackupScriptConfig_${treeNode.cluster_uuid}_${uniqueId}`;
                                backupScriptInfo.after_script_config_class = `afterBackupScriptConfig_${treeNode.cluster_uuid}_${uniqueId}`;
                                backupScriptInfo.accordion_icon = 'vicon-weibiaoti-2-02';
                            } else {
                                backupScriptInfo.uuid = treeNode.app_uuid + '_' + treeNode.name;
                                backupScriptInfo.dir_path = treeNode.cluster_instance_show_name + '/' + treeNode.name;
                            }

                            backupScriptList.push(backupScriptInfo);
                        }
                        break;
                    default:
                        if ('instance' === treeNode.eventtype) {
                            backupScriptList.push(backupScriptInfo);
                        }
                        break;
                }
            }
        }

        let hasOldScript = false;
        for (const index in backupScriptList) {
            for (const oldDbInfo of SETTINGS.db_info) {
                if (
                    oldDbInfo.agentuuid === backupScriptList[index].agent_uuid &&
                    oldDbInfo.instancename === backupScriptList[index].instance_name &&
                    oldDbInfo.dbname === backupScriptList[index].db_name
                ) {
                    if (Array.isArray(oldDbInfo.before_task_script) && oldDbInfo.before_task_script.length > 0) {
                        hasOldScript = true
                    }
                    if (Array.isArray(oldDbInfo.after_task_script) && oldDbInfo.after_task_script.length > 0) {
                        hasOldScript = true
                    }
                }
            }
        }
        if (hasOldScript) {
            // 加载旧数据
            for (const index in backupScriptList) {
                for (const oldDbInfo of SETTINGS.db_info) {
                    if (
                        oldDbInfo.agentuuid === backupScriptList[index].agent_uuid &&
                        oldDbInfo.instancename === backupScriptList[index].instance_name &&
                        oldDbInfo.dbname === backupScriptList[index].db_name
                    ) {
                        if (Array.isArray(oldDbInfo.before_task_script) && oldDbInfo.before_task_script.length > 0) {
                            backupScriptList[index].before_task_script = oldDbInfo.before_task_script;
                        }
                        if (Array.isArray(oldDbInfo.after_task_script) && oldDbInfo.after_task_script.length > 0) {
                            backupScriptList[index].after_task_script = oldDbInfo.after_task_script;
                        }
                    }
                }
            }
            for (const index in data.srcInfo.dbInfo) {
                for (const oldDbInfo of SETTINGS.db_info) {
                    if (
                        oldDbInfo.agentuuid === data.srcInfo.dbInfo[index].agentuuid &&
                        oldDbInfo.instancename === data.srcInfo.dbInfo[index].instancename &&
                        oldDbInfo.dbname === data.srcInfo.dbInfo[index].dbname
                    ) {
                        if (Array.isArray(oldDbInfo.before_task_script) && oldDbInfo.before_task_script.length > 0) {
                            data.srcInfo.dbInfo[index].before_task_script = oldDbInfo.before_task_script;
                        } else {
                            data.srcInfo.dbInfo[index].before_task_script = [];
                        }
                        if (Array.isArray(oldDbInfo.after_task_script) && oldDbInfo.after_task_script.length > 0) {
                            data.srcInfo.dbInfo[index].after_task_script = oldDbInfo.after_task_script;
                        } else {
                            data.srcInfo.dbInfo[index].after_task_script = [];
                        }
                    }
                }
            }
        }
    };

    /**
     * 设置步骤2的日志备份内容
     */
    const setStep2MultiTask = () => {
        let backupNodeTree = $.fn.zTree.getZTreeObj('backupTargetNodeTree');
        let backupStorageTree = $.fn.zTree.getZTreeObj('backupTargetStorageTree');
        if (!oracleCurrentIsMultiTaskFlag) {  // 二次备份才填充
            // 取消之前的禁用
            let nodeNodes = backupNodeTree.getNodesByParam('oracle_multi_task', '1');
            let storageNodes = backupStorageTree.getNodesByParam('oracle_multi_task', '1');
            for (const nodeNode of nodeNodes) {
                nodeNode.oracle_multi_task = '0';
                backupNodeTree.setChkDisabled(nodeNode, false);
                backupNodeTree.updateNode(nodeNode, false);
            }
            for (const storageNode of storageNodes) {
                storageNode.oracle_multi_task = '0';
                backupStorageTree.setChkDisabled(storageNode, false);
                backupStorageTree.updateNode(storageNode, false);
            }
            return;
        }
        let dbType = parseInt($('#dbtype').val());
        let checkNodes = zTreeDB[agentList[0]].getCheckedNodes();
        switch (dbType) {
            case CONF.DB_TYPE.ORACLE:
                // 取消勾选
                backupNodeTree.checkAllNodes(false);
                backupStorageTree.checkAllNodes(false);
                // 选择默认存储设备
                let storageInfo = checkNodes[0].db_backup_info[0];
                if (storageInfo.storage_pool_uuid) {
                    // 选择存储池
                    let storagePoolNode = backupStorageTree.getNodeByParam('storage_pool_uuid', checkNodes[0].db_backup_info[0].storage_pool_uuid);
                    backupStorageTree.checkNode(storagePoolNode, true, true, true);
                } else {
                    let storageNode = backupStorageTree.getNodeByParam('storage_uuid', checkNodes[0].db_backup_info[0].storage_uuid);
                    backupStorageTree.checkNode(storageNode, true, true, true);
                }
                if (storageInfo.node_pool_uuid) {
                    // 选择节点池
                    let nodePoolNode = backupNodeTree.getNodeByParam('node_pool_uuid', checkNodes[0].db_backup_info[0].node_pool_uuid);
                    backupNodeTree.checkNode(nodePoolNode, true, true, true);
                } else {
                    let nodeNode = backupNodeTree.getNodeByParam('node_uuid', checkNodes[0].db_backup_info[0].node_uuid);
                    backupNodeTree.checkNode(nodeNode, true, true, true);
                }
                // 禁用存储和节点
                let nodeNodes = backupNodeTree.getNodesByParam('eventtype', 'node');
                let nodePoolNodes = backupNodeTree.getNodesByParam('eventtype', 'node_pool');
                let storageNodes = backupStorageTree.getNodesByParam('eventtype', 'storage');
                let storagePoolNodes = backupStorageTree.getNodesByParam('eventtype','storage_pool');
                for (const nodeNode of nodeNodes) {
                    // 节点需要判断是否在线
                    if (!nodeNode.online_flag) {
                        continue;
                    }
                    nodeNode.oracle_multi_task = '1';
                    backupNodeTree.setChkDisabled(nodeNode, true);
                    backupNodeTree.updateNode(nodeNode, false);
                }
                for (const nodePoolNode of nodePoolNodes) {
                    nodePoolNode.oracle_multi_task = '1';
                    backupNodeTree.setChkDisabled(nodePoolNode, true);
                    backupNodeTree.updateNode(nodePoolNode, false);
                }
                for (const storageNode of storageNodes) {
                    // 存储需要判断状态是否正常
                    if (parseInt(storageNode.storage_flag) !== 1) {
                        continue;
                    }
                    storageNode.oracle_multi_task = '1';
                    backupStorageTree.setChkDisabled(storageNode, true);
                    backupStorageTree.updateNode(storageNode, false);
                }
                for (const storagePoolNode of storagePoolNodes) {
                    storagePoolNode.oracle_multi_task = '1';
                    backupStorageTree.setChkDisabled(storagePoolNode, true);
                    backupStorageTree.updateNode(storagePoolNode, false);
                }
                break;
            default:
                break;
        }
    };

    //////////////////// 开始-Oracle跳过数据文件坏块 ////////////////////

    /**
     * 初始化跳过数据文件坏块的表格
     */
    const initSkipDatafileBadBlockTable = (key, tableSpaceName, tableSpaceIndex) => {
        let agentUuid = oracleSkipBadBlockList[key].agent_uuid;
        let instanceName = oracleSkipBadBlockList[key].instance_name;
        let clusterUuid = oracleSkipBadBlockList[key].cluster_uuid;
        let eventtype = oracleSkipBadBlockList[key].eventtype;
        let reqData = {
            db_type: CONF.DB_TYPE.ORACLE,
            table_space_name: tableSpaceName,
            agent_uuid: agentUuid,
            instance_name: instanceName,
        }

        // 加载数据库/表空间信息
        $(`#skipDatafileBadBlockTable`).bootstrapTable('destroy');
        Metronic.blockUI({target: `#skipDatafileBadBlockModal`, animate: true});
        pAjaxRequest(reqData, `/api/v1/db/data_file`, 'GET', res => {
            Metronic.unblockUI(`#skipDatafileBadBlockModal`);
            if (!res.success) {
                return;
            }

            let data = [];
            for (const row of res.data.rows) {
                let checked = false;
                let skipValue = 0;
                for (const datafileInfo of oracleSkipBadBlockList[key].configure_table_space_list) {
                    if (datafileInfo.table_space_name !== tableSpaceName ||
                        datafileInfo.eventtype !== eventtype) {
                        continue;
                    }
                    if (eventtype === 'instance') {
                        if (datafileInfo.instance_name !== instanceName || datafileInfo.agent_uuid !== agentUuid) {
                            continue;
                        }
                    }
                    if (eventtype === 'cluster') {
                        if (datafileInfo.cluster_uuid !== clusterUuid) {
                            continue;
                        }
                    }
                    if (datafileInfo.file_id == row.file_id) {
                        checked = true;
                        skipValue = datafileInfo.bad_block_num;
                        break;
                    }
                }
                data.push({
                    id: row.file_id,
                    checked,
                    file_id: row.file_id,
                    file_name: row.file_name,
                    skip: '',
                    skip_id: key + '_' + oracleSkipBadBlockList[key].instance_name + '_' + tableSpaceIndex + '_' + row.file_id,
                    skip_class: key + '_' + oracleSkipBadBlockList[key].instance_name + '_' + tableSpaceIndex,
                    skip_value: skipValue,
                })
            }

            $(`#skipDatafileBadBlockTable`).baseTableConfig().init({
                toolbarId: '#vin_skip_datafile_bad_block_toolbar',
                vin_toolbar: '.vin_skip_datafile_bad_block_toolbar',
                buttonsToolbar: '.vin_skip_datafile_bad_block_btnToolbar',
                // 搜索相关
                searchInput: true,
                placeholder: LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_TABLE_PLACEHOLDER,
                searchClass: 'skipDatafileBadBlockSearch',
                searchSelector: '.skipDatafileBadBlockSearch',
                clickToSelect: true,
                pagination: true,
                sidePagination: 'client',
                showColumns: false,
                showExport: false,
                resizable: false,
                customSearch: (data, text) => {
                    return data.filter(function (row) {
                        return row.file_name.indexOf(text) > -1;
                    });
                },
                columns: [{
                    checkbox: true,
                    width: '2',
                    widthUnit: '%',
                    sortable: false,
                    formatter: (value, row) => {
                        return {checked: row.checked};
                    }
                }, {
                    field: 'file_id',
                    title: LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_TABLE_FILE_ID,
                    width: '10',
                    widthUnit: '%',
                }, {
                    field: 'file_name',
                    title: LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_TABLE_FILE_NAME,
                    width: '50',
                    widthUnit: '%',
                }, {
                    field: 'skip_id',
                    title: LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_TABLE_FILE_SKIP,
                    width: '20',
                    widthUnit: '%',
                    sortable: false,
                    clickToSelect: false,
                    formatter: (value, row) => {
                        return `<input type="number" value="${row.skip_value}" id="${row.skip_id}" class="form-control ${row.skip_class} bad-block-num-input" />`;
                    },
                }],
                data,
            });
        });
    };

    /**
     * 初始化跳过数据文件坏块的模态框
     */
    const initSkipDatafileBadBlockModal = (key, tableSpaceName, tableSpaceIndex) => {
        let skipDatafileBadBlockModalTag = $('#skipDatafileBadBlockModal');
        skipDatafileBadBlockModalTag.modal({
            width: '693px',
            height: '394px',
        });
        let agentUuid = oracleSkipBadBlockList[key].agent_uuid;
        let clusterUuid = oracleSkipBadBlockList[key].cluster_uuid;
        let name = oracleSkipBadBlockList[key].instance_name;
        let eventtype = oracleSkipBadBlockList[key].eventtype;
        let currentAentUuid = skipDatafileBadBlockModalTag.data('agent-uuid');
        let currentClusterUuid = skipDatafileBadBlockModalTag.data('cluster-uuid');
        let currentEventtype = skipDatafileBadBlockModalTag.data('eventtype');
        let currentInstanceName = skipDatafileBadBlockModalTag.data('name');
        let currentTableSpaceIndex = skipDatafileBadBlockModalTag.data('table-space-index');
        if (currentTableSpaceIndex == tableSpaceIndex) {
            if (eventtype === currentEventtype) {
                if (eventtype === 'cluster') {
                    if (clusterUuid === currentClusterUuid) {
                        return;
                    }
                } else if (eventtype === 'instance') {
                    if (agentUuid === currentAentUuid && name === currentInstanceName) {
                        return;
                    }
                }
            }
        }
        skipDatafileBadBlockModalTag
            .data('agent-uuid', oracleSkipBadBlockList[key].agent_uuid)
            .data('cluster-uuid', oracleSkipBadBlockList[key].cluster_uuid)
            .data('eventtype', eventtype)
            .data('name', name)
            .data('table-space-index', tableSpaceIndex)
            .data('table-space-name', tableSpaceName);
        initSkipDatafileBadBlockTable(key, tableSpaceName, tableSpaceIndex);
    };

    /**
     * 初始化Oracle跳过数据文件坏块的内容
     */
    const initOracleSkipDatafileBadBlock = () => {
        $('.skipDatafileBadBlockAfterConfigureDiv').hide();
        $('#skipDatafileBadBlockAfterConfigure').html(``);
        let options = ``;
        let checkedSourceNodes = zTreeAgent.getNodesByParam('checked', true);
        for (const checkedSourceNode of checkedSourceNodes) {
            let name = checkedSourceNode.name;
            let value = checkedSourceNode.agent_uuid + '_' + checkedSourceNode.instance_name;
            if (checkedSourceNode.eventtype === 'cluster') {
                value = checkedSourceNode.cluster_uuid;
                let instanceList = [];
                for (const child of checkedSourceNode.children) {
                    instanceList.push(child.agent_ip + '/' + child.instance_name);
                }
                name = checkedSourceNode.app_service_name + '(' + instanceList.join(', ') + ')';
            }
            options += `<option value="${value}" data-agent-uuid="${checkedSourceNode.agent_uuid}" data-cluster-uuid="${checkedSourceNode.cluster_uuid}"
                data-eventtype="${checkedSourceNode.eventtype}" data-instance-name="${checkedSourceNode.instance_name}">${name}</option>`;
        }
        $('#skipDatafileBadBlock').html(options);
        oracleSkipBadBlockList = {};
    };

    /**
     * 获取Oracle跳过数据文件坏块的树的配置
     */
    const getOracleSkipDatafileBadBlockTreeSettings = (key) => {
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
            callback: {
                beforeClick: (treeId, treeNode) => {
                    oracleSkipBadBlockList[key].tree.checkNode(treeNode, !treeNode.checked, true, true)
                },
                onCheck: (ev, treeId, treeNode) => {
                    $(`#skipDatafileBadBlockConfigure_${key} .skip-datafile-bad-block-configure_item[data-table-space-index="${treeNode.table_space_index}"]`).remove();
                    if (treeNode.checked) {
                        let showResult = 'display-none';
                        for (const datafileInfo of oracleSkipBadBlockList[key].configure_table_space_list) {
                            if (datafileInfo.table_space_name === treeNode.table_space_name) {
                                if (oracleSkipBadBlockList[key].eventtype === 'cluster') {
                                    if (datafileInfo.cluster_uuid === key) {
                                        showResult = '';
                                        break;
                                    }
                                } else if (oracleSkipBadBlockList[key].eventtype === 'instance') {
                                    if (
                                        datafileInfo.agent_uuid === oracleSkipBadBlockList[key].agent_uuid &&
                                        datafileInfo.instance_name === oracleSkipBadBlockList[key].instance_name
                                    ) {
                                        showResult = '';
                                        break;
                                    }
                                }
                            }
                        }
                        let html = `<div class="skip-datafile-bad-block-configure_item" data-table-space-index="${treeNode.table_space_index}" title="${treeNode.table_space_name}">
                            <div class="table-space-label">${treeNode.table_space_name}</div>
                            <div class="table-space-btn">
                                <button type="button" class="btn btn-light-primary">${LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_NUM}</button>
                            </div>
                            <div class="table-space-result ${showResult}">
                                <i class="viconfont vicon-Frame-15"></i>
                                <span>${LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_CONFIGURED}</span>
                            </div>
                        </div>
                        `;
                        $(`#skipDatafileBadBlockConfigure_${key}`).append(html);
                        // 注册点击事件
                        $(`#skipDatafileBadBlockConfigure_${key} .skip-datafile-bad-block-configure_item[data-table-space-index="${treeNode.table_space_index}"] .table-space-btn .btn`).on('click', () => {
                            initSkipDatafileBadBlockModal(key, treeNode.table_space_name, treeNode.table_space_index);
                        });
                    } else {
                        for (let index = oracleSkipBadBlockList[key].configure_table_space_list.length - 1; index >= 0; index--) {
                            let datafileInfo = oracleSkipBadBlockList[key].configure_table_space_list[index];
                            if (oracleSkipBadBlockList[key].eventtype === 'cluster') {
                                if (datafileInfo.table_space_name === treeNode.table_space_name && datafileInfo.cluster_uuid === key) {
                                    oracleSkipBadBlockList[key].configure_table_space_list.splice(index, 1);
                                }
                            } else if (oracleSkipBadBlockList[key].eventtype === 'instance') {
                                if (
                                    datafileInfo.table_space_name === treeNode.table_space_name &&
                                    datafileInfo.agent_uuid === oracleSkipBadBlockList[key].agent_uuid &&
                                    datafileInfo.instance_name === oracleSkipBadBlockList[key].instance_name
                                ) {
                                    oracleSkipBadBlockList[key].configure_table_space_list.splice(index, 1);
                                }
                            }
                        }
                    }
                },
            }
        };
    };

    /**
     * 初始化Oracle跳过数据文件坏块的树
     */
    const initOracleSkipDatafileBadBlockTree = (key) => {
        let icon = './img/platform/storage.png';
        if (oracleSkipBadBlockList[key].eventtype === 'cluster') {
            icon = './img/platform/db-cluster.png';
        }
        /**
         * @type {Object}
         */
        let nodes = [{
            pId: '',
            id: 'instance',
            name: oracleSkipBadBlockList[key].name,
            title: oracleSkipBadBlockList[key].name,
            icon,
            isParent: true,
            open: true,
            nocheck: true,
        }];
        for (const index in oracleSkipBadBlockList[key].table_space_list) {
            let tableSpace = oracleSkipBadBlockList[key].table_space_list[index];
            nodes.push({
                pId: 'instance',
                id: 'table_space_' + tableSpace,
                name: tableSpace,
                title: tableSpace,
                icon: './img/platform/storage.png',
                isParent: false,
                table_space_name: tableSpace,
                table_space_index: index,
            });
        }
        oracleSkipBadBlockList[key].tree = $.fn.zTree.init($(`#skipDatafileBadBlockTree_${key}`), getOracleSkipDatafileBadBlockTreeSettings(key), nodes);
        // 选中之前的节点
        for (const datafileInfo of oracleSkipBadBlockList[key].configure_table_space_list) {
            let treeNode = oracleSkipBadBlockList[key].tree.getNodeByParam('table_space_name', datafileInfo.table_space_name);
            oracleSkipBadBlockList[key].tree.checkNode(treeNode, true, true, true);
        }
    };

    /**
     * 初始化跳过坏块配置内容
     */
    const initOracleSkipDatafileBadBlockContent = () => {
        let skipDatafileBadBlockTag = $('#skipDatafileBadBlock');
        let key = skipDatafileBadBlockTag.val();
        let agentUuid = skipDatafileBadBlockTag.find('option:selected').data('agent-uuid');
        let clusterUuid = skipDatafileBadBlockTag.find('option:selected').data('cluster-uuid');
        let instanceName = skipDatafileBadBlockTag.find('option:selected').data('instance-name');
        let eventtype = skipDatafileBadBlockTag.find('option:selected').data('eventtype');
        let name = skipDatafileBadBlockTag.find('option:selected').html();
        if (typeof oracleSkipBadBlockList[key] === 'undefined') {
            oracleSkipBadBlockList[key] = {
                agent_uuid: agentUuid,
                cluster_uuid: clusterUuid,
                table_space_list: [],
                name,
                eventtype,
                tree: null,
                configure_table_space_list: [],
                instance_name: instanceName,
                edit_init_flag: false,
            };
            for (let i = 0; i < agentList.length; i++) {
                let nodes = zTreeDB[agentList[i]].getCheckedNodes();
                for (const node of nodes) {
                    if (
                        (eventtype === 'cluster' && node.cluster_uuid === clusterUuid) ||
                        (eventtype === 'instance' && node.agent_uuid === agentUuid && node.instance_name === instanceName)
                    ) {
                        oracleSkipBadBlockList[key].table_space_list = node.children.map(item => {
                            return item.name;
                        });
                        break;
                    }
                }
            }
        }

        // 初始化页面
        let html = `<div class="skip-datafile-bad-block-item" id="skipDatafileBadBlockDiv_${key}">
            <div class="skip-datafile-bad-block-item__left">
                <div class="accordion skipDatafileBadBlock">
                    <div class="panel panel-default strategy-panel">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover"
                                    data-placement="top" data-toggle="collapse" data-parent=".skipDatafileBadBlock" href="#skipDatafileBadBlock_${key}" aria-expanded="true">
                                    ${name}
                                </a>
                            </h4>
                        </div>
                        <div id="skipDatafileBadBlock_${key}" class="panel-collapse collapse in" aria-expanded="true" style="">
                            <div class="panel-body">
                                <div class="col-md-12">
                                    <div class="skip-datafile-bad-block-label">${LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_NUM}</div>
                                    <div class="skip-datafile-bad-block-tree__wrapper">
                                        <ul class="ztree skip-datafile-bad-block-tree" id="skipDatafileBadBlockTree_${key}"></ul>
                                    </div>
                                    <div class="skip-datafile-bad-block-configure__wrapper" id="skipDatafileBadBlockConfigure_${key}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="skip-datafile-bad-block-item__right">
                <button type="button" id="skipDatafileBadBlockDeleteBtn_${key}"><i class="viconfont vicon-a-Deleteshanchu"></i></button>
            </div>
        </div>`;
        $('#skipDatafileBadBlockAfterConfigure').append(html);
        $('.skipDatafileBadBlockAfterConfigureDiv').show();
        initOracleSkipDatafileBadBlockTree(key);

        // 注册事件
        $(`#skipDatafileBadBlockDeleteBtn_${key}`).on('click', () => {
            delete oracleSkipBadBlockList[key];
            $(`#skipDatafileBadBlockDiv_${key}`).remove();
            if (!Object.values(oracleSkipBadBlockList).length) {
                $('.skipDatafileBadBlockAfterConfigureDiv').hide();
            }
        });
    };

    /**
     * 构建Oracle跳过坏块数据
     */
    const buildOracleOldSkipBadBlockData = () => {
        for (const oldDbInfo of SETTINGS.db_info) {
            for (let index = 0; index < data.srcInfo.dbInfo.length; index++) {
                if (oldDbInfo.cluster_flag !== data.srcInfo.dbInfo[index].cluster_flag) {
                    continue;
                }
                if (oldDbInfo.cluster_flag && oldDbInfo.cluster_uuid === data.srcInfo.dbInfo[index].cluster_uuid) {
                    data.srcInfo.dbInfo[index].detail = oldDbInfo.detail;
                } else if (
                    !oldDbInfo.cluster_flag &&
                    oldDbInfo.agentuuid === data.srcInfo.dbInfo[index].agentuuid &&
                    oldDbInfo.instancename === data.srcInfo.dbInfo[index].instancename
                ) {
                    data.srcInfo.dbInfo[index].detail = oldDbInfo.detail;
                }
            }
        }
    };

    /**
     * 渲染Oracle跳过坏块数据
     */
    const renderOracleOldBadBlockData = () => {
        // 构建老数据
        let tableSpaceMap = {};
        for (let i = 0; i < agentList.length; i++) {
            let nodes = zTreeDB[agentList[i]].getCheckedNodes();
            for (const node of nodes) {
                for (const oldDbInfo of SETTINGS.db_info) {
                    if (oldDbInfo.cluster_flag !== node.cluster_flag) {
                        continue;
                    }
                    if (oldDbInfo.cluster_flag && oldDbInfo.cluster_uuid === node.cluster_uuid) {
                        tableSpaceMap[node.cluster_uuid] = node.children.map(item => {
                            return item.name;
                        });

                    } else if (
                        !oldDbInfo.cluster_flag &&
                        oldDbInfo.agentuuid === node.agent_uuid &&
                        oldDbInfo.instancename === node.instance_name
                    ) {
                        tableSpaceMap[node.agent_uuid + '_' + node.instance_name] = node.children.map(item => {
                            return item.name;
                        });
                    }
                }
            }
        }
        for (const dbInfo of data.srcInfo.dbInfo) {
            if (!dbInfo.detail.skip_datafiles_bad_block_info.length) {
                continue;
            }
            let key = dbInfo.agent_uuid + '_' + dbInfo.instancename;
            if (dbInfo.cluster_flag) {
                key = dbInfo.cluster_uuid;
            }
            oracleSkipBadBlockList[key] = {
                agent_uuid: dbInfo.agentuuid,
                cluster_uuid: dbInfo.cluster_uuid,
                table_space_list: tableSpaceMap[key],
                name: dbInfo.dir_path,
                eventtype: dbInfo.cluster_uuid.length ? 'cluster' : 'instance',
                tree: null,
                configure_table_space_list: dbInfo.detail.skip_datafiles_bad_block_info,
                instance_name: dbInfo.instancename,
                edit_init_flag: true,
            };
        }

        // 页面渲染
        for (const key in oracleSkipBadBlockList) {
            $('#skipDatafileBadBlock').val(key).trigger('change');
            $('#skipDatafileBadBlockBtn').trigger('click');
        }
    };

    //////////////////// 结束-Oracle跳过数据文件坏块 ////////////////////

    /**
     * 构建备份源数据
     */
    const buildBackupSourceData = () => {
        let dbType = parseInt($('#dbtype').val());
        data.srcInfo.dbInfo = [];
        for (let i = 0; i < agentList.length; i++) {
            let nodes = zTreeDB[agentList[i]].getCheckedNodes();
            let selectFlag = false;
            let loadDatabaseFlag = true;
            $.each(nodes, function (i, d) {
                if (d.eventtype === 'instance' || d.eventtype === 'cluster') {
                    if (!Array.isArray(d.children) || !d.children.length) {
                        loadDatabaseFlag = false;
                        return false;
                    }
                }
                let dbInfo = {
                    dbname: d.instance_name,
                    dbuuid: d.db_uuid,
                    instancename: d.instance_name,
                    agentuuid: d.agent_uuid,
                    agent_uuid: d.agent_uuid,
                    groupuuid: d.group_uuid,
                    dir_path: d.name,
                    config: {},
                    cluster_flag: false,
                    cluster_uuid: '',
                    before_task_script: [],
                    after_task_script: [],
                    detail: {},
                    db_index: 0,
                };
                switch (dbType) {
                    case CONF.DB_TYPE.ORACLE:
                        if (SETTINGS.agentInfo.multi_task_flag) {  // 仅支持 完备修改为完备， 日志修改为日志，因此可以这样判断
                            oracleCurrentIsMultiTaskFlag = true;
                        }
                        if (d.eventtype === 'cluster') {
                            dbInfo['dbname'] = d.app_service_name;
                            let nodeList = [];
                            for (const instanceNode of d.instance_node_list) {
                                nodeList.push(instanceNode.agent_ip + '/' + instanceNode.instance_name);
                            }
                            dbInfo['dir_path'] = d.app_service_name + '(' + nodeList.join(', ') + ')';
                            dbInfo['cluster_flag'] = true;
                            dbInfo['cluster_uuid'] = d.cluster_uuid;
                            dbInfo['detail'].skip_datafiles_bad_block_info = [];
                            data.srcInfo.dbInfo.push(dbInfo);
                            selectFlag = true;
                        } else if (d.eventtype === 'instance') {
                            dbInfo['detail'].skip_datafiles_bad_block_info = [];
                            data.srcInfo.dbInfo.push(dbInfo);
                            selectFlag = true;
                        }
                        break;
                    case CONF.DB_TYPE.SQLSERVER:
                        // 恢复模式
                        if ('db' === d.eventtype) {  // SQL Server选择的是数据库
                            if (parseInt(d.recovery_mode) === SQLSERVER_RECOVERY_MODE_ENUM.SIMPLE) {
                                sqlserverDbSimpleModeFlag = true;
                            } else {
                                sqlserverDbSimpleModeFlag = false;
                            }
                        }
                    // fallthrough
                    case CONF.DB_TYPE.SAPHANA:
                        if ('db' === d.eventtype) {  // SQL Server选择的是数据库
                            let dirPath;
                            if (d.cluster_flag) {
                                dirPath = d.cluster_instance_show_name + '/' + d.instance_name + '/' + d.name;
                                dbInfo['cluster_flag'] = true;
                                dbInfo['cluster_uuid'] = d.cluster_uuid;
                            } else {
                                dirPath = d.cluster_instance_show_name + '/' + d.name;
                            }
                            dbInfo['dbname'] = d.name;
                            dbInfo['db_index'] = d.db_index;
                            dbInfo['dir_path'] = dirPath;
                            data.srcInfo.dbInfo.push(dbInfo);
                            selectFlag = true;
                        }
                        break;
                    case CONF.DB_TYPE.DM:
                    case CONF.DB_TYPE.POSTGRE:
                    case CONF.DB_TYPE.ANTDB:
                    case CONF.DB_TYPE.KINGBASE:
                    case CONF.DB_TYPE.UXDB:
                    case CONF.DB_TYPE.HIGHGO:
                    case CONF.DB_TYPE.OPENGAUSS:
                    case CONF.DB_TYPE.VASTBASE:
                    case CONF.DB_TYPE.MYSQL:
                    case CONF.DB_TYPE.MARIA:
                        if (d.eventtype === 'cluster') {
                            let nodeList = [];
                            for (const instanceNode of d.instance_node_list) {
                                nodeList.push(instanceNode.agent_ip + '/' + instanceNode.instance_name);
                            }
                            dbInfo['cluster_flag'] = true;
                            dbInfo['cluster_uuid'] = d.cluster_uuid;
                            dbInfo['dir_path'] = d.cluster_name + '(' + nodeList.join(', ') + ')';
                            data.srcInfo.dbInfo.push(dbInfo);
                            selectFlag = true;
                        } else if (d.eventtype === 'instance') {
                            data.srcInfo.dbInfo.push(dbInfo);
                            selectFlag = true;
                        }
                        break;
                    case CONF.DB_TYPE.MONGODB:
                        if (d.eventtype === 'cluster') {
                            let nodeList = [];
                            for (const instanceNode of d.instance_node_list) {
                                nodeList.push(instanceNode.agent_info.ip + '/' + instanceNode.instance_name);
                            }
                            dbInfo['cluster_flag'] = true;
                            dbInfo['cluster_uuid'] = d.cluster_uuid;
                            dbInfo['dir_path'] = d.cluster_name + '(' + nodeList.join(', ') + ')';
                            data.srcInfo.dbInfo.push(dbInfo);
                            selectFlag = true;
                        } else if (d.eventtype === 'instance') {
                            data.srcInfo.dbInfo.push(dbInfo);
                            selectFlag = true;
                        }
                        break;
                    case CONF.DB_TYPE.TIDB:
                        if (d.eventtype === 'cluster') {
                            let nodeList = [];
                            let agentUuid = d.agent_uuid;
                            for (const instanceNode of d.instance_node_list) {
                                nodeList.push(instanceNode.agent_ip + '/' + instanceNode.app_detail.node_role);
                                if (instanceNode.app_detail.node_role.search('deploy') > -1) {  // TiDB使用中控机的客户端uuid
                                    agentUuid = instanceNode.agent_uuid;
                                }
                            }
                            dbInfo['agentuuid'] = agentUuid;
                            dbInfo['agent_uuid'] = agentUuid;
                            dbInfo['cluster_flag'] = true;
                            dbInfo['cluster_uuid'] = d.cluster_uuid;
                            dbInfo['dir_path'] = d.cluster_name + '(' + nodeList.join(', ') + ')';
                            data.srcInfo.dbInfo.push(dbInfo);
                            selectFlag = true;
                        } else if (d.eventtype === 'instance') {
                            data.srcInfo.dbInfo.push(dbInfo);
                            selectFlag = true;
                        }
                        break;
                    default:
                        break;
                }
            });
            if (!loadDatabaseFlag) {
                return false;
            }
            if (!selectFlag) {
                let tips = LANG.UI_DB_BACKUP_NO_SELECT_INSTANCE_TIPS;
                if (dbType === CONF.DB_TYPE.SQLSERVER) {
                    tips = LANG.UI_DB_BACKUP_SQLSERVER_SELECT_DB_ERROR;
                } else if (dbType === CONF.DB_TYPE.SAPHANA) {
                    tips = LANG.UI_DB_BACKUP_SAPHANA_SELECT_DB_ERROR;
                }
                UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, tips);
                return false;
            }
        }
        return true;
    };

    /**
     * 获取数据库备份任务
     * @param reqData
     * @param taskUuid
     * @returns {Promise<unknown>}
     */
    const getDbBackupTask = (reqData, taskUuid) => {
        return new Promise((resolve, reject) => {
            Metronic.blockUI({target: '#dbbackupcontent', animate: true});
            pAjaxRequest(reqData, `/api/v1/db/jobs/${taskUuid}/backup`, `GET`, res => {
                Metronic.unblockUI('#dbbackupcontent');
                if (!res.success) {
                    reject(null);
                } else {
                    resolve(res.data);
                }
            });
        });
    };

    /**
     * 初始化关联任务数据
     */
    const initAssociatedTaskData = () => {
        return new Promise(resolve => {
            associatedTaskData = null;
            let dbType = parseInt($('#dbtype').val());
            if (dbType !== CONF.DB_TYPE.ORACLE) {
                resolve();
                return;
            }
            let existsTaskUuid = null;
            let nodes = zTreeDB[agentList[0]].getCheckedNodes();
            if (!nodes[0].db_backup_info.length) {
                resolve();
                return;
            }
            for (const dbBackupInfo of nodes[0].db_backup_info) {
                if (SETTINGS.taskuuid !== dbBackupInfo.job_uuid) {
                    existsTaskUuid = dbBackupInfo.job_uuid;
                    break;
                }
            }
            if (!existsTaskUuid) {
                resolve();
                return;
            }
            Metronic.blockUI({target: '#dbbackupcontent', animate: true});
            let reqData = {
                with_encrypt_password_flag: true,
            };
            getDbBackupTask(reqData, existsTaskUuid).then(resData => {
                associatedTaskData = resData;
            }).finally(resolve);
        });
    };

    var step1Valid = function (showTitleCallback) {
        if (!zTreeAgent) {
            UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, LANG.UI_DB_BACKUP_SELECT_CLIENT);
            return false;
        }
        let agentNodes = zTreeAgent.getNodesByParam('checked', true);
        if (!agentNodes.length) {
            UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, LANG.UI_DB_BACKUP_SELECT_CLIENT);
            return false;
        }
        //是否需要显示传输网络标记
        networkFlag = false;
        oracleCurrentIsMultiTaskFlag = false;

        let dbType = parseInt($('#dbtype').val());
        if (!buildBackupSourceData()) {
            return false;
        }
        // 构建备份脚本配置列表
        buildBackupScriptConfigList(dbType);

        data.srcInfo.dbType = dbType;
        $('.advancedDiv').hide()            // 隐藏所有高级配置
        $('.oracleChannelDiv').hide();		// 传输线程配置
        $('.mongodbThreadDiv').hide();		// 传输线程配置
        $('.time-strategy-tips').hide();    // 隐藏所有的时间策略tips
        $('.incr').hide();                  // 隐藏增量
        $('.diff').hide();                  // 隐藏差异
        $('.pIncr').hide();                 // 隐藏永久增量
        $('.full').show();                  // 显示完全备份
        $('.dbLog').show();                 // 显示日志备份
        $('#strategymode').find('input[data-mode=4]').iCheck('enable');   // 允许日志勾选
        $('.scriptConfigSwitchDiv').show(); // 脚本配置
        // 隐藏所有高级配置分类
        $('#tabAdvanceTabUl li').removeClass('active').hide();
        $('#tabAdvancedContent div.tab-pane').removeClass('active').removeClass('in').addClass('fade');
        $('.advancedCategoryShow').hide();
        $('#reserveShowDiv').show();  // 显示保留策略
        $('#tabAdvancedRetryTab').show();  // 重试策略
        $('.retryShow').show();
        $('#tabAdvancedOverloadTab').show();  // 过载保护
        $('.advancedOverloadShowDiv').show();
        $('.ignoreResourceLimitDiv').show(); // 忽略节点资源限制
        $('.safeStrategyLi').show();  // 显示安全策略
        $('#task_retry_flag').closest('.retry-strategy-content__group.retry-group-wrap').show();  // 显示重试策略的任务重试
        switch (dbType) {
            case CONF.DB_TYPE.SQLSERVER:
                $('.diff').show();			    //显示差异备份
                $('.sqlserverDiv').show();		//显示sqlserver功能选项
                $('.db-no-incr-tips').show();   // 时间策略提示

                $('#tabAdvancedCheckTab').addClass('active').show();  // 校验
                $('#tab_advanced_check').addClass('active').addClass('in').removeClass('fade');
                $('.advancedCheckShowDiv').show();
                $('#tabAdvancedPerformanceTab').show();  // 性能优化
                $('.advancedPerformanceShowDiv').show();
                if (sqlserverDbSimpleModeFlag === false) {
                    $('#strategymode').find('input[data-mode=4]').iCheck('check').iCheck('disable');
                    $('#stragegyaccordion').find('.strategy-panel[data-mode=4]').show();
                }
                break;
            case CONF.DB_TYPE.ORACLE:
                $('.incr').show();			    // 显示增量备份
                $('.diff').show();			    // 显示差异备份
                $('.oracleDiv').show();			// 显示oracle功能选项
                $('.oracleCheckArchivelogDiv').hide();  // 通用版本隐藏
                $('.oracleChannelDiv').show();	// 线程数
                $('.db-tips').show();           // 时间策略提示
                $('.customRmanCmdDiv').show();  // Oracle自定义RMAN命令
                $('#oracleFilesPerset').trigger('switchChange.bootstrapSwitch');  // 手动触发切换事件，用于隐藏/显示输入框
                $('#archivelogBackupTimesCheck').trigger('switchChange.bootstrapSwitch');  // 手动触发切换事件，用于隐藏/显示输入框
                $('#archivelogBackupDaysCheck').trigger('switchChange.bootstrapSwitch');  // 手动触发切换事件，用于隐藏/显示输入框
                $('#setSectionSizeFlag').trigger('switchChange.bootstrapSwitch');  // 手动触发切换事件，用于隐藏/显示输入框

                $('#tabAdvancedArchivelogTab').addClass('active').show();  // 归档日志
                $('#tab_advanced_archivelog').addClass('active').addClass('in').removeClass('fade');
                $('.advancedArchivelogShowDiv').show();
                $('#tabAdvancedWarningTab').show();  // 异常处理
                $('.advancedWarningShowDiv').show();
                $('#tabAdvancedPerformanceTab').show();  // 性能优化
                $('.advancedPerformanceShowDiv').show();
                // 构建之前的跳过数据文件坏块配置
                if (!oracleCurrentIsMultiTaskFlag) {
                    buildOracleOldSkipBadBlockData();
                    initOracleSkipDatafileBadBlock();
                } else {
                    $('.skipDatafileBadBlockDiv').hide();
                    $('.skipDatafileBadBlockAfterConfigureDiv').hide();
                    $('#reserveShowDiv').hide();  // 隐藏保留策略
                }
                break;
            case CONF.DB_TYPE.MARIA:
            case CONF.DB_TYPE.MYSQL:
                $('.incr').show();			        // 显示增量备份
                $('.mysqlParallelDiv').show();      // MySQL处理线程
                $('.mysqlSrcCompressedDiv').show(); // MySQL源端压缩
                $('.db-no-diff-tips').show();       // 时间策略提示

                $('#tabAdvancedPerformanceTab').addClass('active').show();  // 性能优化
                $('#tab_advanced_performance').addClass('active').addClass('in').removeClass('fade');
                $('.advancedPerformanceShowDiv').show();
                break;
            case CONF.DB_TYPE.DM:
                $('.incr').show();			        // 显示增量备份
                $('.diff').show();			        // 显示差异备份
                $('.dmDiv').show();			        // 显示dm功能选项
                $('.db-tips').show();               // 时间策略提示
                $('#dmcompress').trigger('switchChange.bootstrapSwitch');

                $('#tabAdvancedArchivelogTab').addClass('active').show();  // 归档日志
                $('#tab_advanced_archivelog').addClass('active').addClass('in').removeClass('fade');
                $('.advancedArchivelogShowDiv').show();
                $('#tabAdvancedPerformanceTab').show();  // 性能优化
                $('.advancedPerformanceShowDiv').show();
                break;
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.UXDB:
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.OPENGAUSS:
            case CONF.DB_TYPE.VASTBASE:
                $('.postgreDiv').show();	        // 显示postgres高级配置
                $('.db-log-tips').show();          // 时间策略提示
                if ($('#archiveAlarmCheck').get(0).checked) {
                    $('.warnningdiv').show();
                    if (parseInt($('select[name=noticetype]').val()) === 1) {
                        //百分比
                        $('#percentdiv').show();
                        $('#sizediv').hide();
                    } else {
                        //按容量告警取值B转换为GB
                        $('#percentdiv').hide();
                        $('#sizediv').show();
                    }
                } else {
                    $('.warnningdiv').hide();
                }

                $('#tabAdvancedArchivelogTab').addClass('active').show();  // 归档日志
                $('#tab_advanced_archivelog').addClass('active').addClass('in').removeClass('fade');
                $('.advancedArchivelogShowDiv').show();
                break;
            case CONF.DB_TYPE.MONGODB:
                $('.incr').show();			        // 显示增量备份
                $('.diff').show();			        // 显示差异备份
                $('.pIncr').show();		            // 显示永久增量备份
                $('.parallelNumDiv').show();        // 显示客户端并行数量
                $('.mongodbThreadDiv').show();		// 显示传输线程
                $('.db-all-tips').show();           // 时间策略提示

                $('#tabAdvancedPerformanceTab').addClass('active').show();  // 性能优化
                $('#tab_advanced_performance').addClass('active').addClass('in').removeClass('fade');
                $('.advancedPerformanceShowDiv').show();
                break;
            case CONF.DB_TYPE.TIDB:
                $('.db-tidb-tips').show();      // 显示TiDB时间策略描述
                $('.tidbDiv').show();           // 显示TiDB高级配置
                $('#tidbCompressMethod').trigger('change');

                $('#tabAdvancedPerformanceTab').addClass('active').show();  // 性能优化
                $('#tab_advanced_performance').addClass('active').addClass('in').removeClass('fade');
                $('.advancedPerformanceShowDiv').show();
                // $('.safeStrategyLi').hide();  // 隐藏安全策略
                // $('#wormConfig_check').bootstrapSwitch('state', false).trigger('switchChange.bootstrapSwitch');
                // $('#integrityCheck_check').bootstrapSwitch('state', false);
                // $('#task_retry_flag').closest('.retry-strategy-content__group.retry-group-wrap').hide();  // 隐藏重试策略的任务重试
                // $('#task_retry_flag').bootstrapSwitch('state', false);
                break;
            case CONF.DB_TYPE.SAPHANA:
                $('.incr').show();              // 显示增量备份
                $('.diff').show();              // 显示差异备份
                $('.dbLog').hide();             // 隐藏日志备份
                $('.db-no-log-tips').show();    // 时间策略提示
                $('.channelCountDiv').show();   // 显示通道数
                $('.sapHanaDiv').show();        // 显示SAP HANA高级配置

                $('#tabAdvancedPerformanceTab').addClass('active').show();  // 性能优化
                $('#tab_advanced_performance').addClass('active').addClass('in').removeClass('fade');
                $('.advancedPerformanceShowDiv').show();
                break;
        }
        DBSubType = dbType;
        setStep2MultiTask();
        showStep1();
        if(SETTINGS.timestrategy.type == 'manual' && SETTINGS.dbtype == CONF.DB_TYPE.SAPHANA) {
            $('.incr').hide();                  // 隐藏增量
            $('.diff').hide();                  // 隐藏差异
            $('.pIncr').hide();                 // 隐藏永久增量
            $('.full').hide();                  // 显示完全备份
            $('.backup_interval-div').spinner('value', data.agentInfo.auto_log_backup_interval / 60);
            $('.strategy-panel [data-mode=10] .strategyDes').html(LANG.UI_PUBLIC_BACKUP_LOG + " (" + LANG.UI_GLOBAL_STRATEGY_BACKUP_INTERVAL + "：" + data.agentInfo.auto_log_backup_interval / 60 + LANG.UI_JOB_MINUTE +  ")");
        }

        // 授权判断
        getDbCurrentUseLicense().then(initAssociatedTaskData).then(showTitleCallback);
        return false;
    }

    /**
     * 初始化步骤4的数据库备份源树
     */
    const initStep4BackupSourceTree = () => {
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
            let checkNodes = zTreeDB[agentKey].getCheckedNodes();
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
                            title: LANG.UI_DB_CLUSTER,
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
                    if (dbType === CONF.DB_TYPE.MONGODB) {
                        // MongoDB有多级
                        let hostCheckNodes = zTreeAgent.getNodesByParam('checked', true);
                        let hostCheckNode = null;
                        for (const _hostCheckNode of hostCheckNodes) {
                            if (_hostCheckNode.eventtype === 'cluster' && _hostCheckNode.id === checkNode.id) {
                                hostCheckNode = _hostCheckNode;
                                break;
                            }
                        }
                        if (!hostCheckNode) {
                            return;
                        }
                        for (const child of hostCheckNode.children) {
                            nodes.push(child);
                        }
                    } else {
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
                    }
                    if (
                        dbType === CONF.DB_TYPE.SQLSERVER ||
                        dbType === CONF.DB_TYPE.SAPHANA
                    ) {  // SQL Server选择的是数据库
                        nodes[nodes.length - 1].isParent = true;
                        nodes[nodes.length - 1].open = true;
                        let lastInstanceId = nodes[nodes.length - 1].id;
                        for (const dbNode of checkNodes) {
                            if (dbNode.eventtype === 'db') {
                                nodes.push({
                                    id: lastInstanceId + '_' + dbNode.name,
                                    pId: lastInstanceId,
                                    name: dbNode.name,
                                    title: dbNode.name,
                                    isParent: false,
                                    nocheck: true,
                                    eventtype: 'cluster_instance_db',
                                    icon: './img/platform/storage.png',
                                });
                            }
                        }
                    }
                } else if (checkNode.eventtype === 'instance') {
                    if (!standaloneTopFlag) {
                        standaloneTopFlag = true;
                        nodes.push({
                            id: 'standalone',
                            pId: 0,
                            name: LANG.UI_DB_STANDALONE,
                            title: LANG.UI_DB_STANDALONE,
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
                    if (
                        dbType === CONF.DB_TYPE.SQLSERVER ||
                        dbType === CONF.DB_TYPE.SAPHANA
                    ) {  // SQL Server选择的是数据库
                        nodes[nodes.length - 1].isParent = true;
                        nodes[nodes.length - 1].open = true;
                        let lastInstanceId = nodes[nodes.length - 1].id;
                        for (const dbNode of checkNodes) {
                            if (dbNode.eventtype === 'db') {
                                nodes.push({
                                    id: lastInstanceId + '_' + dbNode.name,
                                    pId: lastInstanceId,
                                    name: dbNode.name,
                                    title: dbNode.name,
                                    isParent: false,
                                    nocheck: true,
                                    eventtype: 'cluster_instance_db',
                                    icon: './img/platform/storage.png',
                                });
                            }
                        }
                    }
                }
            });
        }
        $.fn.zTree.init($("#dbShowTree"), setting, nodes);
    };

    var showStep1 = function () {
        initStep4BackupSourceTree();
        $('.dbshow').hide();
        $('#dbShowTree').show();

        $('.dbTypeShow').html(CONF.DB_DES[data.srcInfo.dbType] + " " + LANG.UI_BACKUP_DISK_MODE);
    }

    /**
     * 设置归档日志备份任务的时间策略
     */
    const setMultiTaskTimeStrategy = () => {
        $('#backuptype').removeAttr('disabled');
        let dbType = parseInt($('#dbtype').val());
        $('#backuptype option[value="oncetime"]').show();
        switch (dbType) {
            case CONF.DB_TYPE.ORACLE:
                if (!oracleCurrentIsMultiTaskFlag) {  // 归档日志备份任务才填充
                    $('.beforeTimeStrategyTipsDiv').hide();
                    $('#fullBackup').removeAttr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').removeClass('disabled')
                    $('#incrBackup').removeAttr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').removeClass('disabled')
                    $('#diffBackup').removeAttr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').removeClass('disabled')
                    $('#logBackup').removeAttr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').removeClass('disabled')
                } else {
                    $('.beforeTimeStrategyTipsDiv').show();
                    $('#beforeTimeStrategyTipsAlert').show();
                    $('#beforeTimeStrategyTips').html(LANG.UI_DB_BACKUP_ORACLE_LOG_BACKUP_TIPS);
                    $('#backuptype option[value="oncetime"]').hide();  // Oracle日志备份任务隐藏一次性备份
                    if ($('#backuptype').val() === 'oncetime') {
                        $('#backuptype').val('strategy').trigger('change');
                        return;
                    } else if ($('#backuptype').val() === 'manual') {
                        return;
                    }
                    $('#fullBackup').removeAttr('disabled', 'disable').iCheck('uncheck').attr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').addClass('disabled')
                    $('#incrBackup').removeAttr('disabled', 'disable').iCheck('uncheck').attr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').addClass('disabled')
                    $('#diffBackup').removeAttr('disabled', 'disable').iCheck('uncheck').attr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').addClass('disabled')
                    $('#logBackup').removeAttr('disabled', 'disable').iCheck('check').attr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').addClass('disabled')
                    $('#backupTimestrategy div.strategy-panel[data-mode=1]').hide();
                    $('#backupTimestrategy div.strategy-panel[data-mode=2]').hide();
                    $('#backupTimestrategy div.strategy-panel[data-mode=3]').hide();
                    $('#backupTimestrategy div.strategy-panel[data-mode=4]').show();
                }
                break;
            case CONF.DB_TYPE.TIDB:
                if ($('#backuptype').val() !== 'strategy') {
                    return;
                }
                /**
                 * TiDB选择完备和日志
                 */
                // $('#backuptype').removeAttr('disabled').val('strategy').trigger('change').attr('disabled', 'disabled');
                $('.incr').hide();			    // 隐藏增量备份
                $('.diff').hide();			    // 隐藏差异备份
                $('#backupTimestrategy div.strategy-panel[data-mode=2]').hide();
                $('#backupTimestrategy div.strategy-panel[data-mode=3]').hide();
                $('#backupTimestrategy div.strategy-panel[data-mode=4]').hide();  // 日志备份没有策略配置
                // $('#fullBackup').iCheck('check').attr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').addClass('disabled');
                // $('#fullBackup').iCheck('check');
                // $('#logBackup').iCheck('check').attr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').addClass('disabled')
                // $('#backupTimestrategy div.strategy-panel[data-mode=1]').show();
                if (SETTINGS.sub_task_flag) {
                    $('#fullBackup').removeAttr('disabled').iCheck('check').attr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').addClass('disabled');
                    $('#logBackup').removeAttr('disabled').iCheck('check').attr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').addClass('disabled');
                    $('#backupTimestrategy div.strategy-panel[data-mode=1]').show();
                    $('#backuptype').attr('disabled', 'disabled').val('strategy');
                } else {
                    // $('#logBackup').removeAttr('disabled').iCheck('uncheck').attr('disabled', 'disable').parents().closest('div.icheckbox_square-blue').addClass('disabled');
                }
                break;
            default:
                break;
        }
    };

    /**
     * 设置步骤3高级配置
     */
    const setMultiTaskAdvanced = () => {
        let dbType = parseInt($('#dbtype').val());
        switch (dbType) {
            case CONF.DB_TYPE.ORACLE:
                if (!oracleCurrentIsMultiTaskFlag) {  // 归档日志备份任务才填充
                    $('.beforeTimeStrategyTipsDiv').hide();
                    return;
                }
                // 隐藏跳过脱机文件
                $('.skipOfflineFileFlagDiv').hide();
                // 隐藏BCT
                $('.enableBctFlagDiv').hide();
                // 隐藏多段传输
                $('.setSectionSizeFlagDiv').hide();
                $('.sectionSizeDiv').hide();
                break;
            case CONF.DB_TYPE.TIDB:
                $('.beforeTimeStrategyTipsDiv').show();
                $('#beforeTimeStrategyTipsAlert').show();
                $('#beforeTimeStrategyTips').html(LANG.UI_DB_BACKUP_TIDB_LOG_BACKUP_TIPS);
                break;
            default:
                $('.beforeTimeStrategyTipsDiv').hide();
                break;
        }
    };

    /**
     * 获取显示传输网络标志
     */
    const getNetworkFlag = () => {
        let agentNodes = zTreeAgent.getNodesByParam('checked', true);
        let flag = false;
        for (let i = 0; i < agentNodes.length; i++) {
            if (agentNodes[i].eventtype === 'cluster') {
                // 2. 集群实例不需要显示传输网络
                flag = false;
                break;
            } else {
                // 3. 备份实例的主机网络为模式1不显示传输网络
                if (parseInt(agentNodes[i].net_model) === 2) {
                    flag = true;
                }
            }
        }

        let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
        if (false === backupTargetInfo || !backupTargetInfo.node_uuid) {
            // 4. 备份目标的节点不确定不显示传输网络
            flag = false;
        }
        if (flag) {
            let selectAgentUuidSet = new Set();
            for (const agentNode of agentNodes) {
                selectAgentUuidSet.add(agentNode.agent_uuid);
            }
            if (selectAgentUuidSet.size > 1) {
                // 1. 多主机备份不显示传输网络
                flag = false;
            }
        }

        return flag;
    };

    /**
     * 设置步骤3的安全策略
     */
    const setMultiTaskSafetyStrategy = () => {
        let dbType = parseInt($('#dbtype').val());
        let fullRestartFlag = parseInt(SETTINGS.safe_config_strategy.integrity_check_config.full_error_policy);
        if (fullRestartFlag === CONF.FULL_ABNORAL.REFULL) {
            $('#full_restart').prop('checked', true).trigger('change').closest('div.virus-item').show();
            $('#full_end').prop('checked', false).prop('disabled', false);
        } else {
            $('#full_restart').prop('checked', false).closest('div.virus-item').show();
            $('#full_end').prop('checked', true).prop('disabled', false).trigger('change');
        }
        $('#integrityCheck_check').bootstrapSwitch('state', SETTINGS.safe_config_strategy.integrity_check_flag).trigger('switchChange.bootstrapSwitch').bootstrapSwitch('disabled', false);
        $('#integrityCheck input[name="checkDay"]').prop('disabled', false).prop('checked', false);
        $(`#integrityCheck input[name="checkDay"][value="${SETTINGS.safe_config_strategy.integrity_check_config.check_strategy}"]`).prop('checked', true).trigger('change');
        if (dbType === CONF.DB_TYPE.ORACLE) {
            if (oracleCurrentIsMultiTaskFlag) {
                $('#full_restart').prop('checked', false).closest('div.virus-item').hide();
                $('#full_end').prop('checked', true).trigger('change');
                if (associatedTaskData) {
                    let safeConfigStrategy = associatedTaskData.safe_config_strategy;
                    if (safeConfigStrategy.integrity_check_flag) {
                        $('#integrityCheck_check').bootstrapSwitch('state', true).trigger('switchChange.bootstrapSwitch').bootstrapSwitch('disabled', true);
                        $('#full_end').prop('disabled', true);
                        let checkStrategy = parseInt(safeConfigStrategy.integrity_check_config.check_strategy);
                        $(`#integrityCheck input[name="checkDay"][value="${checkStrategy}"]`).prop('checked', true).trigger('change');
                        $('#integrityCheck input[name="checkDay"]').prop('disabled', true);
                    } else {
                        $('#integrityCheck_check').bootstrapSwitch('state', false).trigger('switchChange.bootstrapSwitch').bootstrapSwitch('disabled', true);
                    }
                } else {
                    $('#integrityCheck_check').bootstrapSwitch('disabled', true);
                    $('#full_end').prop('disabled', true);
                    $('#integrityCheck input[name="checkDay"]').prop('disabled', true);
                }
            }
        }
    };

    /**
     * 设置步骤3的存储策略是否启用
     * @param flag
     */
    const setStorageStrategyEnable = (flag = true) => {
        if (flag) {
            // 启用所有输入框
            $('#deduplicationCheck').bootstrapSwitch('disabled', false);
            $('#compressCheck').bootstrapSwitch('disabled', false);
            $('#compressGrade').removeAttr('disabled');
            $('#encryptStorageCheck').bootstrapSwitch('disabled', false);
            $('#storageEncryptMethod').removeAttr('disabled');
            $('#passwordAutocheck').bootstrapSwitch('disabled', false);
            $('#password').removeAttr('disabled');
            $('#repassword').removeAttr('disabled');
        } else {
            // 禁用所有输入框
            $('#deduplicationCheck').bootstrapSwitch('disabled', true);
            $('#compressCheck').bootstrapSwitch('disabled', true);
            $('#compressGrade').attr('disabled', 'disabled');
            $('#encryptStorageCheck').bootstrapSwitch('disabled', true);
            $('#storageEncryptMethod').attr('disabled', 'disabled');
            $('#passwordAutocheck').bootstrapSwitch('disabled', true);
            $('#password').attr('disabled', 'disabled');
            $('#repassword').attr('disabled', 'disabled');
        }
    };

    /**
     * 设置步骤3的存储策略
     */
    const setMultiTaskStorageStrategy = () => {
        // 取消禁用
        setStorageStrategyEnable();
        let dbType = parseInt($('#dbtype').val());
        if (dbType === CONF.DB_TYPE.ORACLE) {
            if (oracleCurrentIsMultiTaskFlag) {  // 归档日志备份任务才填充
                if (associatedTaskData) {  // 存在主任务
                    let storageStrategy = associatedTaskData.storage_strategy;
                    $('#deduplicationCheck').bootstrapSwitch('state', storageStrategy.deduplication_flag).trigger('switchChange.bootstrapSwitch');
                    $('#compressCheck').bootstrapSwitch('state', storageStrategy.compress_flag).trigger('switchChange.bootstrapSwitch');
                    if (storageStrategy.compress_flag) {
                        $('#compressGrade').val(storageStrategy.compress_method);
                    }
                    $('#encryptStorageCheck').bootstrapSwitch('state', storageStrategy.encrypt_flag).trigger('switchChange.bootstrapSwitch');
                    if (storageStrategy.encrypt_flag) {
                        $('#storageEncryptMethod').val(storageStrategy.encrypt_method);
                        $('#passwordAutocheck').bootstrapSwitch('state', storageStrategy.auto_password_flag).trigger('switchChange.bootstrapSwitch');
                        if (!storageStrategy.auto_password_flag) {
                            $('#password').val(atob(storageStrategy.encrypt_password));
                            $('#repassword').val(atob(storageStrategy.encrypt_password));
                            firstInitPageFlag = false;
                        }
                        passwordModeChange();
                    }
                } else {
                    // 没有主任务，设置当前任务存储策略
                    let storageStrategy = SETTINGS.bss;
                    $('#deduplicationCheck').bootstrapSwitch('state', storageStrategy.dataencrypt).trigger('switchChange.bootstrapSwitch');
                    $('#compressCheck').bootstrapSwitch('state', storageStrategy.compress_flag).trigger('switchChange.bootstrapSwitch');
                    if (storageStrategy.compress_flag) {
                        $('#compressGrade').val(storageStrategy.compress_method);
                    }
                    $('#encryptStorageCheck').bootstrapSwitch('state', storageStrategy.encrypt).trigger('switchChange.bootstrapSwitch');
                    if (storageStrategy.encrypt) {
                        $('#storageEncryptMethod').val(storageStrategy.encrypt_method);
                        $('#passwordAutocheck').bootstrapSwitch('state', storageStrategy.password_auto_flag).trigger('switchChange.bootstrapSwitch');
                        if (!storageStrategy.password_auto_flag) {
                            $('#password').val(atob(storageStrategy.password));
                            $('#repassword').val(atob(storageStrategy.password));
                            firstInitPageFlag = false;
                        }
                        passwordModeChange();
                    }
                }
                // 设置存储策略为禁用
                setStorageStrategyEnable(false);
            }
        }
    };

    /**
     * 设置不是花步骤3策略后要做的事
     */
    const setAfterInitStep3Strategy = () => {
        setStep3MultiTask();
        let dbType = parseInt($('#dbtype').val());
        if (dbType === CONF.DB_TYPE.MONGODB) {
            initWormStrategy();
            let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
            if (
                data.highInfo.node.storage_type === CONF.BD_STORAGE_TYPE.CLOUD ||
                backupTargetInfo.storage_pool_type === 3
            ) {  // MongoDB备份到云存储仅支持按链保留
                $('#reserveMode').val(2).trigger('change').attr('disabled', 'disabled');
            } else {
                $('#reserveMode').removeAttr('disabled');
            }
        } else if (dbType === CONF.DB_TYPE.TIDB) {
            // 关闭完全备份补偿
            $(`#fullSkipSwitch`).bootstrapSwitch('state', false);
        }
    };

    /**
     * 首次初始化备份策略后要做的事
     */
    const afterFirstInitBackupStrategy = () => {
        let dbType = parseInt($('#dbtype').val());
        if (dbType === CONF.DB_TYPE.TIDB) {
            // 关闭完全备份补偿
            $(`#fullSkipSwitch`).bootstrapSwitch('state', false);
        }
        setStep3MultiTask();
    };

    /**
     * 设置步骤3的日志备份配置
     */
    const setStep3MultiTask = () => {
        // 时间策略设置
        setMultiTaskTimeStrategy();
        setAfterStrategyData();
        // 存储策略设置
        setMultiTaskStorageStrategy();
        // 高级配置设置
        setMultiTaskAdvanced();
        // 设置安全策略
        setMultiTaskSafetyStrategy();
    };

    /**
     * 设置步骤3的授权功能
     */
    const setStep3AuthFunc = () => {
        // 重复数据删除
        if (!CONF.FUNCTIONS.includes('dedupication')) {
            $('.deduplicationDiv').hide();
        }
        // 多线程
        if (!CONF.FUNCTIONS.includes('multithread')) {
            $('.mongodbThreadDiv').hide();
            $('.mongodbBackupThreadDiv').spinner("value", 1);
        }
        // 安全策略
        if (!CONF.FUNCTIONS.includes('worm')) {
            $('#wormConfig').hide();
        }
        if (!CONF.FUNCTIONS.includes('integrity')) {
            $('#integrityCheck').hide();
        }
        if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
            $('.safeStrategyLi').hide();
        }
    };

    /**
     * 设置脚本配置
     */
    const setBackupScriptConfig = function () {
        let scriptConfigFlag = false;
        for (const dbInfo of SETTINGS.db_info) {
            if (Array.isArray(dbInfo.before_task_script) && dbInfo.before_task_script.length > 0) {
                scriptConfigFlag = true;
                break;
            }
            if (Array.isArray(dbInfo.after_task_script) && dbInfo.after_task_script.length > 0) {
                scriptConfigFlag = true;
                break;
            }
        }
        if (scriptConfigFlag) {
            $('#scriptConfigSwitch').bootstrapSwitch('state', true);
        }
    };

    /**
     * 初始化Worm策略
     */
    const initWormStrategy = () => {
        let dbType = parseInt($('#dbtype').val());
        if (dbType === CONF.DB_TYPE.MONGODB) {  // MongoDB支持永久增量
            let pIncrBackup = $('#pincrBackup').prop('checked');  // 永久增量是否选中
            if ($('#backuptype').val() === 'strategy' && pIncrBackup) {  // 按策略备份并选择了永久增量
                $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.p_incr_backup);
                return;
            }
        }
        // worm配置需要存储开启了worm才显示
        let backupTargetInfo = $('#backupTarget').backupTarget('getSelect')
        if (!backupTargetInfo.storage_uuid) {  // 没有选择存储
            $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.no_storage);
        } else {
            if (!backupTargetInfo.storage_worm_config.flag) {  // 存储未开启worm
                $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.no_worm);
            } else {
                let safeConfigStrategy = SETTINGS.safe_config_strategy;
                $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal, 'col-md-3', false, safeConfigStrategy.worm_flag, safeConfigStrategy.worm_protection_time);
                $('#wormConfig_check').trigger('switchChange.bootstrapSwitch');
            }
        }

        // Oracle归档日志任务的WORM设置
        if (dbType === CONF.DB_TYPE.ORACLE) {
            if (oracleCurrentIsMultiTaskFlag) {  // 归档日志备份任务需要与主任务同步
                if (associatedTaskData) {
                    let safeConfigStrategy = associatedTaskData.safe_config_strategy;
                    if (safeConfigStrategy.worm_flag) {
                        $('#wormConfig_check').bootstrapSwitch('state', true).trigger('switchChange.bootstrapSwitch').bootstrapSwitch('disabled', true);
                        $('#wormConfig_spinner').spinner('value', safeConfigStrategy.worm_protection_time);
                        $('#wormConfig_spinner').spinner('disable', true);
                    } else {
                        $('#wormConfig_check').bootstrapSwitch('state', false).trigger('switchChange.bootstrapSwitch').bootstrapSwitch('disabled', true);
                    }
                } else {
                    $('#wormConfig_check').bootstrapSwitch('disabled', true);
                    $('#wormConfig_spinner').spinner('disable', true);
                }
            }
        }
    };

    var step2Valid = function () {
        if (!$('#backupTarget').backupTarget('validateSelect')) {
            return false;
        }
        //是否需要显示传输网络标记
        networkFlag = getNetworkFlag();
        initWormStrategy();
        setStep3MultiTask();
        let dbType = parseInt($('#dbtype').val());
        if (dbType === CONF.DB_TYPE.ORACLE) {
            if (!oracleCurrentIsMultiTaskFlag) {
                renderOracleOldBadBlockData();
            }
        }

        // 脚本配置
        setBackupScriptConfig();

        showStep2();
        setStep3AuthFunc();
        return true;
    }

    var showStep2 = function () {
        //备份目的地(节点)
        let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
        data.highInfo.node.nodecheck = !backupTargetInfo.node_uuid;
        data.highInfo.node.storagecheck = !backupTargetInfo.storage_uuid;
        data.highInfo.node.storageuuid = backupTargetInfo.storage_uuid;
        data.highInfo.node.storage_pool_uuid = backupTargetInfo.storage_pool_uuid;
        data.highInfo.node.nodeuuid = backupTargetInfo.node_uuid;
        data.highInfo.node.node_pool_uuid = backupTargetInfo.node_pool_uuid;
        data.highInfo.node.storage_type = parseInt(backupTargetInfo.storage_type);
        initResourceLimit(backupTargetInfo.node_uuid_list);
        $('.nodeInfoShow').html(backupTargetInfo.node_text);
        $('.storageInfoShow').html(backupTargetInfo.storage_text);
        let dbType = parseInt($('#dbtype').val());
        //初始化传输网络
        if (networkFlag) {
            $('.transfernetworkDiv').show();
            initNetworkList(data.highInfo.node.nodeuuid);
        } else {
            $('.transfernetworkDiv').hide();
        }
        if (data.srcInfo.dbType === CONF.DB_TYPE.ORACLE) {
            $('.strategy-panel[data-mode=4] .dwm' ).find('.icheck').off().on('ifChecked', strategyOnChange);
        }
        //判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
        getTapeStrategy(data.highInfo.node.storageuuid, data.highInfo.node.storage_type, '.oracleChannelDiv', '.oracleChannelSpinner', '#tab_common', CONF.MODULE_TYPE.DB, true);
        if (CONF.BD_STORAGE_TYPE.TAPE !== data.highInfo.node.storage_type) {
            $('#reserveType').trigger('change');
            if (dbType === CONF.DB_TYPE.ORACLE) {
                $('#oracleChannelNum').prop('disabled', false);  // 设置默认值
                $('.channelCountDiv').hide();
                $('.mongodbThreadDiv').hide();
            } else if (dbType === CONF.DB_TYPE.SAPHANA) {
                $('.channelCountDiv').show();
                $('.oracleChannelDiv').hide();
                $('.mongodbThreadDiv').hide();
            } else if (dbType === CONF.DB_TYPE.MONGODB) {
                $('.channelCountDiv').hide();
                $('.oracleChannelDiv').hide();
                $('.mongodbThreadDiv').show();
                $('#tabAdvancedPerformanceTab').show();  // 性能优化
                $('.advancedPerformanceShowDiv').show();
                if (
                    data.highInfo.node.storage_type === CONF.BD_STORAGE_TYPE.CLOUD ||
                    backupTargetInfo.storage_pool_type === 3
                ) {  // MongoDB备份到云存储仅支持按链保留
                    $('#reserveMode').val(2).trigger('change').attr('disabled', 'disabled');
                } else {
                    $('#reserveMode').removeAttr('disabled');
                }
            } else {
                $('.channelCountDiv').hide();
                $('.oracleChannelDiv').hide();
                $('.mongodbThreadDiv').hide();
            }
            $('#tab3 .safeStrategyLi').show();  // 显示安全策略
            $('#safeStrategyShowDiv').show();
        } else {
            $('.channelCountDiv').hide();
            $('.oracleChannelDiv').hide();
            $('.mongodbThreadDiv').hide();
            if (dbType === CONF.DB_TYPE.MONGODB) {
                $('#tabAdvancedPerformanceTab').removeClass('active').hide();  // 性能优化
                $('#tabAdvancedContent div.tab-pane').removeClass('active').removeClass('in').addClass('fade');
                $('.advancedPerformanceShowDiv').hide();
                $('#parallelNumSpinner').spinner('value', 1);
                $('#tabAdvancedRetryTab').addClass('active');
                $('#tab_advanced_retry').addClass('active').addClass('in').removeClass('fade');
            }
            // 隐藏安全策略
            $('#tab3 .safeStrategyLi').removeClass('active').hide();  // 隐藏安全策略
            $('#safeStrategyShowDiv').hide();
            $('#tab_safe_strategy').removeClass('active').removeClass('in').addClass('fade');
            $('#tab3 .commonLi').addClass('active');
            $('#tab_common').addClass('active').addClass('in').removeClass('fade');
        }
    }

    /**
     * 设置步骤4的时间策略
     */
    var setStep4TimeStrategyDes = function () {
        let dbType = parseInt($('#dbtype').val());
        let strategyMode = $('#strategymode').find('input:checked');
        let backupTypeShowStr = '';
        let backupTypeInfoShowStr = '';
        $('.backupTypeInfoDiv').show();
        if ("strategy" === data.backupInfo.type) {
            backupTypeShowStr = LANG.UI_BACKUP_USE_STRATEGY;
            for (let i = 0; i < strategyMode.length; i++) {
                let timeStrategyMode = parseInt($(strategyMode[i]).data('mode'));
                if (1 === timeStrategyMode) {
                    backupTypeInfoShowStr += data.backupInfo.fullInfo.des + "<br>";
                } else if (2 === timeStrategyMode) {
                    backupTypeInfoShowStr += data.backupInfo.incrInfo.des + "<br>";
                } else if (3 === timeStrategyMode) {
                    backupTypeInfoShowStr += data.backupInfo.diffInfo.des + "<br>";
                } else if (4 === timeStrategyMode) {
                    if (dbType === CONF.DB_TYPE.TIDB) {
                        backupTypeInfoShowStr += LANG.UI_STRATEGY_LOG + "<br>";
                    } else {
                        backupTypeInfoShowStr += data.backupInfo.logInfo.des + "<br>";
                    }
                } else if (9 === timeStrategyMode) {
                    backupTypeInfoShowStr += data.backupInfo.pIncrInfo.des + "<br>";
                } else if (10 === timeStrategyMode) {
                    backupTypeInfoShowStr += data.backupInfo.autoLogInfo.des + "<br>";
                }
            }
        } else if ("oncetime" === data.backupInfo.type) {
            backupTypeShowStr = LANG.UI_BACKUP_ONCE;
            backupTypeInfoShowStr = LANG.UI_PUBLIC_START_TIME + ": " + data.backupInfo.datetime;
        } else if ('manual' === data.backupInfo.type) {
            backupTypeShowStr = LANG.UI_BACKUP_MANUAL;
            $('.backupTypeInfoDiv').hide();
            // SAP HANA有日志备份
            if (dbType === CONF.DB_TYPE.SAPHANA) {
                backupTypeShowStr = LANG.UI_BACKUP_MANUAL + '<br>';
                backupTypeShowStr += LANG.UI_STRATEGY_LOG + '(' + LANG.UI_GLOBAL_STRATEGY_BACKUP_INTERVAL + '：' + $('#backupInterval').val() + LANG.UI_JOB_MINUTE + ')';
            }
        }
        $('.backuptypeshow').html(backupTypeShowStr);
        $('.backuptypeinfoshow').html(backupTypeInfoShowStr);
    }

    /**
     * 设置步骤4的保留策略描述
     */
    var setStep4ReserveStrategyDes = function () {
        if (CONF.BD_STORAGE_TYPE.TAPE !== data.highInfo.node.storage_type) {
            $('.reserveDiv').show();
            let dbType = parseInt($('#dbtype').val());
            let reserveDes = ``;
            if (dbType === CONF.DB_TYPE.MONGODB) {
                reserveDes += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + $('#reserveMode option:selected').text() + '<br>';
            } else {
                reserveDes += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
            }
            reserveDes += LANG.UI_RESERVE_RETENTION_MODE + ': ' + $('#reserveType option:selected').text() + '<br>';
            var methoddes = LANG.UI_STRATEGY_VALUE;
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
                methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
            }
            reserveDes += methoddes + ': ' + data.highInfo.reserve.value + '<br>';
            $('.reservetypeshow').html(reserveDes);
            if (dbType === CONF.DB_TYPE.ORACLE) {
                if (oracleCurrentIsMultiTaskFlag) {
                    $('.reserveDiv').hide();
                }
            }
        }
    }

    /**
     * 设置步骤4的存储策略描述
     */
    var setStep4StoreStrategyDes = function () {
        let storeInfo = "";
        let software = parseInt(CONF.SOFTWARE);
        if (1 !== software && 4 !== software) {
            // 重复数据删除
            if (CONF.FUNCTIONS.includes('dedupication')) {
                storeInfo += $('.deduplicationLabel').html() + ": " + getSwitchDes(data.highInfo.store.deduplication) + "<br>";
            }
        }
        // 压缩存储
        storeInfo += $('.compressLabel').html() + ": " + getSwitchDes(data.highInfo.store.compress_flag) + '<br>';
        // 压缩等级
        if (data.highInfo.store.compress_flag) {
            let grade = '';
            switch (parseInt($('#compressGrade').val(), 10)) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                    break;
                case 3:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                    break;
                case 4:
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                    break;
            }
            storeInfo += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade + "<br>";
        }

        // 数据加密配置描述
        storeInfo += $('.encryptStorageLabel').html() + ": " + getSwitchDes(data.highInfo.store.encrypt) + '<br>';
        // 存储加密类别【aes-256 sm4】
        if ($('#encryptStorageCheck').get(0).checked) {
            let grade = '';
            switch (parseInt($('#storageEncryptMethod').val())) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            }
            storeInfo += $('.storage-encrypt-label').html() + ": " + grade + "<br>";
        }
        if (data.highInfo.store.encrypt) {
            storeInfo += $('.passwordAutoLabel').html() + ": " + getSwitchDes(data.highInfo.store.password_auto_flag) + '<br>';
        }
        $('.storageinfoshow').html(storeInfo);
    }

    /**
     * 设置步骤4的限速策略描述
     */
    var setStep4SpeedStrategyDes = function () {
        let speedLimitsStr = "";
        if (data.speedLimit.speedInfo.length != 0) {
            speedLimitsStr = '';
            for (let i = 0; i < data.speedLimit.speedInfo.length; i++) {
                speedLimitsStr += data.speedLimit.speedInfo[i].des + '<br>';
            }
        }
        if (!speedLimitsStr) {
            speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
        } else {
            if (parseInt(data.speedLimit.type) === 1) {  // 全局策略
                // 策略名称
                let _des = LANG.UI_STRATEGY_NAME + ': ' + data.speedLimit.name + '<br>';
                // 任务等级
                switch (parseInt(data.speedLimit.level)) {
                    case 1:  // 一般
                        _des += LANG.UI_JOB_TASK_PRIORITY + ': ' + LANG.UI_JOB_TASK_PRIORITY_PRIMARY + '<br>';
                        break;
                    case 2:  // 优先
                        _des += LANG.UI_JOB_TASK_PRIORITY + ': ' + LANG.UI_JOB_TASK_PRIORITY_HIGH + '<br>';
                        break;
                    default:  // 最高
                        _des += LANG.UI_JOB_TASK_PRIORITY + ': ' + LANG.UI_JOB_TASK_PRIORITY_HIGHEST + '<br>';
                        break;
                }
                speedLimitsStr = _des + speedLimitsStr;
            }
        }
        $('.speedlimitshow').html(speedLimitsStr);
    }

    /**
     * 设置步骤4的传输策略描述
     */
    var setStep4TransferStrategyDes = function () {
        let des = $('.encrypttransferlabel').html() + ": " + getSwitchDes(data.highInfo.transfer.encrypt) + '<br>';
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
            des += encryptedMethodLabel + ": " + grade + "<br>";
        }
        des += $('.transferlabel').html() + ": " + $('#transport_mode').find("option:selected").text() + '<br>';

        //节点传输网络信息显示
        if (networkFlag) {
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
            des += $('.transfernetworklabel').html() + ": " + networkNode.str + '<br>';
        }

        if (CONF.FUNCTIONS.includes('multithread')) {
            // 传输线程
            if (data.srcInfo.dbType === CONF.DB_TYPE.MONGODB) {
                if (CONF.BD_STORAGE_TYPE.TAPE !== data.highInfo.node.storage_type) {  // 非磁带才有通道数
                    des += $('.mongodbthreadnumlabel').html() + ": " + data.highInfo.transfer.threadnum + '<br>';
                }
            }
        }

        // 通道数
        if (CONF.BD_STORAGE_TYPE.TAPE !== data.highInfo.node.storage_type) {
            if (data.srcInfo.dbType === CONF.DB_TYPE.ORACLE) {
                des += `${$('.oracleChannelLabel').html()}: ${data.highInfo.transfer.threadnum}<br>`;
            } else if (data.srcInfo.dbType === CONF.DB_TYPE.SAPHANA) {
                des += $('.channelCountLabel').html() + ': ' + data.agentInfo.channel_count + '<br>';
            }
        }
        $('.transportinfoshow').html(des);
    }

    /**
     * 获取oracle跳过坏块描述
     */
    const getOracleSkipBadBlockDes = () => {
        let desList = [];
        for (const dbInfo of data.srcInfo.dbInfo) {
            if (dbInfo.detail.skip_datafiles_bad_block_info.length) {
                desList.push(dbInfo.dir_path + ': ' + LANG.UI_DB_BACKUP_SET)
            } else {
                desList.push(dbInfo.dir_path + ': ' + LANG.UI_DB_BACKUP_UNSET)
            }
        }
        return `${$('.skipDatafileBadBlockLabel').html()}: ` + desList.join('; ');
    };

    /**
     * 设置步骤4的代理配置描述 - 高级配置
     */
    var setStep4AgentStrategyDes = function () {
        let advancedArchivelogDes = '';
        let advancedWarningDes = '';
        let advancedCheckDes = '';
        let advancedValidateDataDes = '';
        let advancedSnapshotDes = '';
        let advancedIncrementDes = '';
        let advancedPerformanceDes = '';
        let advancedOverloadDes = '';

        switch (data.srcInfo.dbType) {
            case CONF.DB_TYPE.SQLSERVER:
                advancedCheckDes += $('.checkdblabel').html() + ": " + getSwitchDes(data.agentInfo.checkdbflag) + '<br>';
                advancedCheckDes += $('.checksumlabel').html() + ": " + getSwitchDes(data.agentInfo.checksumflag) + '<br>';
                advancedPerformanceDes += $('.sqlservercompresslabel').html() + ": " + getSwitchDes(data.agentInfo.compressflag) + '<br>';
                break;
            case CONF.DB_TYPE.ORACLE:
                if (!oracleCurrentIsMultiTaskFlag) {
                    var deleteArchivelogText = $('#deleteArchivelogLabel1').html().trim();
                    if (2 === data.agentInfo.delarchivelog) {
                        deleteArchivelogText = $('#deleteArchivelogLabel2').html().trim();
                    } else if (3 === data.agentInfo.delarchivelog) {
                        deleteArchivelogText = $('#deleteArchivelogLabel3-1').html().trim()
                            + data.agentInfo.archivenum + $('#deleteArchivelogLabel3-2').html().trim();
                    }
                    advancedPerformanceDes += $('.oraclecompresslabel').html() + ": " + getSwitchDes(data.agentInfo.compressflag) + '<br>';
                    // des += $('.oracleCheckArchivelogLabel').html() + ": " + getSwitchDes(data.agentInfo.checkdbflag) + '<br>';
                    advancedPerformanceDes += $('.oracleFilesPersetLabel').html() + ": " + getSwitchDes(data.agentInfo.set_filesperset_flag) + '<br>';
                    if (data.agentInfo.set_filesperset_flag) {
                        advancedPerformanceDes += $('.oracleFilesPersetDatafileLabel').html() + ": " + data.agentInfo.datafile_filesperset_num + '<br>';
                        advancedPerformanceDes += $('.oracleFilesPersetArchivelogDivLabel').html() + ": " + data.agentInfo.archivelog_filesperset_num + '<br>';
                    }
                    advancedArchivelogDes += `${$('.archivelogBackupTimesCheckLabel').html()}: ${getSwitchDes(data.agentInfo.log_backup_times_flag)}<br>`;
                    if (data.agentInfo.log_backup_times_flag) {
                        advancedArchivelogDes += `${$('.archivelogBackupTimesLabel').html()}: ${data.agentInfo.log_backup_times}${LANG.UI_PUBLIC_UNIT_COUNT}<br>`;
                    }
                    advancedArchivelogDes += `${$('.archivelogBackupDaysCheckLabel').html()}: ${getSwitchDes(data.agentInfo.log_backup_days_flag)}<br>`;
                    if (data.agentInfo.log_backup_days_flag) {
                        advancedArchivelogDes += `${$('.archivelogBackupDaysLabel').html()}: ${data.agentInfo.log_backup_days}${LANG.UI_PUBLIC_UNIT_DAY}<br>`;
                    }
                    advancedArchivelogDes += $('.delarchiveloglabel').html() + ": " + deleteArchivelogText + '<br>';
                    advancedWarningDes += `${$('.skipInaccessibleFileFlagLabel').html()}: ${getSwitchDes(data.agentInfo.skip_inaccessible_file_flag)}<br>`;
                    advancedWarningDes += `${$('.skipOfflineFileFlagLabel').html()}: ${getSwitchDes(data.agentInfo.skip_offline_file_flag)}<br>`;
                    // BCT
                    advancedPerformanceDes += `${$('.enableBctFlagLabel').html()}: ${getSwitchDes(data.agentInfo.enable_bct_flag)}<br>`;
                    advancedPerformanceDes += `${$('.setSectionSizeFlagLabel').html()}: ${getSwitchDes(data.agentInfo.set_section_size_flag)}<br>`;
                    if (data.agentInfo.set_section_size_flag) {
                        advancedPerformanceDes += `${$('.sectionSizeLabel').html()}: ${data.agentInfo.section_size}GB<br>`;
                    }
                    // 自定义RMAN命令
                    if (data.agentInfo.custom_rman_cmd) {
                        advancedPerformanceDes += `${$('.customRmanCmdLabel').html()}<br>`;
                        advancedPerformanceDes += `<div style="border: 1px solid #E0E0E0; padding: 12px; white-space: pre; max-height: 200px; overflow: auto">${data.agentInfo.custom_rman_cmd}</div>`;
                    } else {
                        advancedPerformanceDes += `${$('.customRmanCmdLabel').html()}: ${LANG.UI_DB_BACKUP_UNSET}<br>`;
                    }
                    // Oracle跳过坏块
                    advancedWarningDes += getOracleSkipBadBlockDes();
                } else {
                    var deleteArchivelogText = $('#deleteArchivelogLabel1').html().trim();
                    if (2 === data.agentInfo.delarchivelog) {
                        deleteArchivelogText = $('#deleteArchivelogLabel2').html().trim();
                    } else if (3 === data.agentInfo.delarchivelog) {
                        deleteArchivelogText = $('#deleteArchivelogLabel3-1').html().trim()
                            + data.agentInfo.archivenum + $('#deleteArchivelogLabel3-2').html().trim();
                    }
                    advancedPerformanceDes += $('.oraclecompresslabel').html() + ": " + getSwitchDes(data.agentInfo.compressflag) + '<br>';
                    // des += $('.oracleCheckArchivelogLabel').html() + ": " + getSwitchDes(data.agentInfo.checkdbflag) + '<br>';
                    advancedPerformanceDes += $('.oracleFilesPersetLabel').html() + ": " + getSwitchDes(data.agentInfo.set_filesperset_flag) + '<br>';
                    if (data.agentInfo.set_filesperset_flag) {
                        advancedPerformanceDes += $('.oracleFilesPersetArchivelogDivLabel').html() + ": " + data.agentInfo.archivelog_filesperset_num + '<br>';
                    }
                    advancedArchivelogDes += `${$('.archivelogBackupTimesCheckLabel').html()}: ${getSwitchDes(data.agentInfo.log_backup_times_flag)}<br>`;
                    if (data.agentInfo.log_backup_times_flag) {
                        advancedArchivelogDes += `${$('.archivelogBackupTimesLabel').html()}: ${data.agentInfo.log_backup_times}${LANG.UI_PUBLIC_UNIT_COUNT}<br>`;
                    }
                    advancedArchivelogDes += `${$('.archivelogBackupDaysCheckLabel').html()}: ${getSwitchDes(data.agentInfo.log_backup_days_flag)}<br>`;
                    if (data.agentInfo.log_backup_days_flag) {
                        advancedArchivelogDes += `${$('.archivelogBackupDaysLabel').html()}: ${data.agentInfo.log_backup_days}${LANG.UI_PUBLIC_UNIT_DAY}<br>`;
                    }
                    advancedArchivelogDes += $('.delarchiveloglabel').html() + ": " + deleteArchivelogText + '<br>';
                    advancedWarningDes += `${$('.skipInaccessibleFileFlagLabel').html()}: ${getSwitchDes(data.agentInfo.skip_inaccessible_file_flag)}<br>`;
                    // 自定义RMAN命令
                    if (data.agentInfo.custom_rman_cmd) {
                        advancedPerformanceDes += `${$('.customRmanCmdLabel').html()}<br>`;
                        advancedPerformanceDes += `<div style="border: 1px solid #E0E0E0; padding: 12px; white-space: pre; max-height: 200px; overflow: auto">${data.agentInfo.custom_rman_cmd}</div>`;
                    } else {
                        advancedPerformanceDes += `${$('.customRmanCmdLabel').html()}: ${LANG.UI_DB_BACKUP_UNSET}<br>`;
                    }
                }
                break;
            case CONF.DB_TYPE.MARIA:
            case CONF.DB_TYPE.MYSQL:
                // MySQL处理线程数
                advancedPerformanceDes += `${$('.mysqlParallelLabel').html()}: ${data.agentInfo.channel_count}<br>`;
                // MySQL源端压缩
                advancedPerformanceDes += `${$('.mysqlSrcCompressedLabel').html()}: ${getSwitchDes(data.agentInfo.compressflag)}<br>`;
                break;
            case CONF.DB_TYPE.DM:
                advancedPerformanceDes += $('.dmcompresslabel').html() + ": " + getSwitchDes(data.agentInfo.compressflag) + '<br>';
                if (data.agentInfo.compressflag) {
                    advancedPerformanceDes += $('.dmCompressLevelLabel').html() + ": " + data.agentInfo.compress_level + '<br>';
                }
                advancedArchivelogDes += $('.dmdelarchiveloglabel').html() + ": " + getSwitchDes(data.agentInfo.delarchivelog) + '<br>';
                break;
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.UXDB:
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.OPENGAUSS:
            case CONF.DB_TYPE.VASTBASE:
                advancedArchivelogDes += $('.delpostgreloglabel').html() + ": " + $('#delpostgrelog').find("option:selected").text() + '<br>';
                advancedArchivelogDes += $('.archiveAlarmlabel').html() + ": " + getSwitchDes(data.agentInfo.warningInfo.warn_check) + '<br>';
                //开启归档告警
                if (data.agentInfo.warningInfo.warn_check) {
                    let noticeType = $('.noticetype').html();
                    advancedArchivelogDes += noticeType + ": " + $('select[name=noticetype]').find("option:selected").text() + '<br>';
                    if (parseInt(data.agentInfo.warningInfo.warn_type) === 1) {
                        advancedArchivelogDes += noticeType + ": " + $('#warningpercent').val() + "%" + '<br>';
                    } else {
                        advancedArchivelogDes += noticeType + ": " + $('#warningsize').val() + "GB" + '<br>';
                    }
                }
                break;
            case CONF.DB_TYPE.MONGODB:
                advancedPerformanceDes += $('.parallelNumLabel').html() + ': ' + data.agentInfo.max_object_transport_parallel_nums + '<br>';
                break;
            case CONF.DB_TYPE.TIDB:
                advancedPerformanceDes = $('.tidbCompressMethodLabel').html() + ': ' + $('#tidbCompressMethod option:selected').text() + '<br>';
                if (data.agentInfo.compress_method === TIDB_COMPRESS_METHOD_ENUM.zstd) {
                    advancedPerformanceDes += $('.tidbCompressLevelLabel').html() + ': ' + data.agentInfo.compress_level + '<br>';
                }
                // des += $('.parallelNumLabel').html() + ': ' + data.agentInfo.max_object_transport_parallel_nums + '<br>';
                break;
            case CONF.DB_TYPE.SAPHANA:
                advancedPerformanceDes += $('.sapHanaCompressLabel').html() + ': ' + getSwitchDes(data.agentInfo.compressflag) + '<br>';
                break;
        }

        // 忽略节点资源限制
        advancedOverloadDes += $('.ignoreResourceLimitLabel').html().trim() + ': '+ getSwitchDes(data.agentInfo.ignore_resource_limiting_flag) + '<br>';

        $('.advancedArchivelogShow').html(advancedArchivelogDes);
        $('.advancedWarningShow').html(advancedWarningDes);
        $('.advancedCheckShow').html(advancedCheckDes);
        $('.advancedValidateDataShow').html(advancedValidateDataDes);
        $('.advancedSnapshotShow').html(advancedSnapshotDes);
        $('.advancedIncrementShow').html(advancedIncrementDes);
        $('.advancedPerformanceShow').html(advancedPerformanceDes);
        $('.advancedOverloadShow').html(advancedOverloadDes);
    }

    /**
     * 设置步骤4备份脚本描述
     */
    const setStep4BackupScriptDes = () => {
        setBackupScriptName();
        let scriptConfigSwitchFlag = !!$('#scriptConfigSwitch').get(0).checked;
        if (!scriptConfigSwitchFlag) {
            $('.scriptConfigShow').html(LANG.UI_DB_SCRIPT_CONFIG_UNSET);
        } else {
            let scriptConfigDes = '';
            for (const index in data.srcInfo.dbInfo) {
                let obj = null;
                for (const backupScriptInfo of backupScriptList) {
                    if (
                        backupScriptInfo.agent_uuid === data.srcInfo.dbInfo[index].agentuuid &&
                        backupScriptInfo.instance_name === data.srcInfo.dbInfo[index].instancename &&
                        backupScriptInfo.db_name === data.srcInfo.dbInfo[index].dbname
                    ) {
                        obj = backupScriptInfo;
                        break;
                    }
                }
                scriptConfigDes += obj.dir_path + ':<br>';
                if (data.srcInfo.dbInfo[index].before_task_script.length) {
                    scriptConfigDes += '&emsp;' + LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT + ': ';
                    scriptConfigDes += data.srcInfo.dbInfo[index].before_task_script.map(v => v.script_name).join('、') + '<br>';
                } else {
                    scriptConfigDes += '&emsp;' + LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT + ': ' + LANG.UI_PUBLIC_NOTHING + '<br>';
                }
                if (data.srcInfo.dbInfo[index].after_task_script.length) {
                    scriptConfigDes += '&emsp;' + LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT + ': ';
                    scriptConfigDes += data.srcInfo.dbInfo[index].after_task_script.map(v => v.script_name).join('、') + '<br>';
                } else {
                    scriptConfigDes += '&emsp;' + LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT + ': ' + LANG.UI_PUBLIC_NOTHING + '<br>';
                }
            }
            $('.scriptConfigShow').html(scriptConfigDes);
        }
    };

    /**
     * 设置步骤4安全策略描述
     */
    const setStep4SafeStrategyDes = () => {
        let safeStrategyDes = '';
        // 安全策略
        if (CONF.FUNCTIONS.includes('worm')) {
            let backupTargetInfo = $('#backupTarget').backupTarget('getSelect')
            safeStrategyDes += LANG.UI_SAFE_STRATEGY_WORM_PROTECT + ':' + getSwitchDes(data.safe_config_strategy.worm_flag) + '<br>';
            if (backupTargetInfo.storage_worm_config.flag && data.safe_config_strategy.worm_flag) {  // 存储开启worm
                safeStrategyDes += LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD + ':' + data.safe_config_strategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY + '<br>';
            }
        }
        if (CONF.FUNCTIONS.includes('integrity')) {
            let integrityCheck = $('#integrityCheck').getCompleteDetectionBackup();
            safeStrategyDes += integrityCheck.des + '<br>';
        }
        if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
            $('#safeStrategyShowDiv').hide();
        } else {
            $('.safeStrategyShow').html(safeStrategyDes);
        }
        // if (data.srcInfo.dbType == CONF.DB_TYPE.TIDB) {  // TiDB不显示安全策略
        //     $('#safeStrategyShowDiv').hide();
        // } else {
        //     $('#safeStrategyShowDiv').show();
        // }
    };

    var showStep3 = function () {
        setStep4TimeStrategyDes();        // 设置步骤4时间策略描述
        setStep4ReserveStrategyDes();     // 设置步骤4保留策略描述
        setStep4StoreStrategyDes();       // 设置步骤4存储策略描述
        setStep4SpeedStrategyDes();       // 设置步骤4限速策略描述
        setStep4TransferStrategyDes();    // 设置步骤4传输策略描述
        setStep4AgentStrategyDes();       // 设置步骤4代理配置描述
        setStep4BackupScriptDes();        // 设置步骤4备份脚本描述
        setStep4SafeStrategyDes();        // 设置步骤4安全策略描述
    }

    var step3Valid = function () {
        if (!getTimeStr()) {
            return false;
        }
        let result = getSpeedStr() & getReserveStr() & getArchiveStr() & getTransferStr() & getStoreStr() & getHighStr() & getBackupScriptConfig() & getSafeStrategy();
        if (result) {
            showStep3();
        }
        return result;
    }


    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function (check) {
        if (check) {
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    var getTimeStr = function () {
        let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
        data.backupInfo.autoLogInfo = {};
        data.backupInfo.fullInfo = {};
        data.backupInfo.logInfo = {};
        data.backupInfo.diffInfo = {};
        data.backupInfo.incrInfo = {};
        data.backupInfo.pIncrInfo = {};
        data.backupInfo.type = $('#backuptype').val();
        $('.jobNameDiv').show();  // 普通任务名
        $('.tidbFullJobNameDiv').hide();  // TiDB完全备份任务名
        $('.tidbLogJobNameDiv').hide();   // TiDB日志备份任务名
        let strategyMode = $('#strategymode').find('input:checked');
        let fullBakup = $("#fullBackup").prop("checked");//获取完全备份是否选中
        let logBackup = $("#logBackup").prop("checked");//归档日志是否选中
        let pincrBackup = $('#pincrBackup').prop('checked');  // 永久增量是否选中
        if ("strategy" === data.backupInfo.type) {
            switch (data.srcInfo.dbType) {
                case CONF.DB_TYPE.ORACLE:
                    if (!oracleCurrentIsMultiTaskFlag) {  // 归档日志备份任务才填充
                        if (!logBackup || !fullBakup) {  // Oracle类型的时候归档日志备份必须被选中
                            // UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_DB_BACKUP_FULL_CHOOSE_TIPS2);
                            // return false;
                        }
                        if (!fullBakup) {  // 完全备份必须选择
                            UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_DB_BACKUP_FULL_CHOOSE_TIPS);
                            return false;
                        }
                    }
                    break;
                case CONF.DB_TYPE.MONGODB:
                    if (!fullBakup && !pincrBackup) {  // MongoDB必须选择完备或永久增量
                        UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_DB_BACKUP_FULL_CHOOSE_TIPS3);
                        return false;
                    }
                    break;
                case CONF.DB_TYPE.TIDB:
                    if (!fullBakup) {  // TiDB必须选择完备
                        UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_DB_BACKUP_FULL_CHOOSE_TIPS);
                        return false;
                    }
                    $('.jobNameDiv').hide();  // 普通任务名
                    $('.tidbFullJobNameDiv').show();  // TiDB完全备份任务名
                    if (logBackup) {  // 选择了日志备份
                        $('.tidbLogJobNameDiv').show();   // TiDB日志备份任务名
                    }
                    break;
                default:
                    if (!fullBakup) {  // 完全备份必须选择
                        UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_DB_BACKUP_FULL_CHOOSE_TIPS);
                        return false;
                    }
                    break;
            }
            for (let i = 0; i < strategyMode.length; i++) {
                let timeStrategyMode = parseInt($(strategyMode[i]).data('mode'));
                if (1 === timeStrategyMode) {
                    data.backupInfo.fullInfo = strategyConfig.fullInfo;
                    if (strategyConfig.fullInfo.rollFlag && !checkTime(strategyConfig.fullInfo.startTime, strategyConfig.fullInfo.endTime)) return;
                } else if (2 === timeStrategyMode) {
                    data.backupInfo.incrInfo = strategyConfig.incrInfo;
                    if (strategyConfig.incrInfo.rollFlag && !checkTime(strategyConfig.incrInfo.startTime, strategyConfig.incrInfo.endTime)) return;
                } else if (3 === timeStrategyMode) {
                    data.backupInfo.diffInfo = strategyConfig.diffInfo;
                    if (strategyConfig.diffInfo.rollFlag && !checkTime(strategyConfig.diffInfo.startTime, strategyConfig.diffInfo.endTime)) return;
                } else if (4 === timeStrategyMode) {
                    if (data.srcInfo.dbType == CONF.DB_TYPE.TIDB) {  // TiDB不需要日志备份策略
                        continue;
                    }
                    data.backupInfo.logInfo = strategyConfig.logInfo;
                    if (strategyConfig.logInfo.rollFlag && !checkTime(strategyConfig.logInfo.startTime, strategyConfig.logInfo.endTime)) return;
                } else if (9 === timeStrategyMode) {
                    data.backupInfo.pIncrInfo = strategyConfig.pIncrInfo;
                    if (strategyConfig.pIncrInfo.rollFlag && !checkTime(strategyConfig.pIncrInfo.startTime, strategyConfig.pIncrInfo.endTime)) return;
                } else if (10 === timeStrategyMode) {
                    data.backupInfo.autoLogInfo = strategyConfig.autoLogInfo;
                }
            }
            $('.settimetip').hide();
        } else if ("oncetime" === data.backupInfo.type) {
            let onceTime = $('#oncetime').val();
            if (!onceTime) {
                UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_BACKUP_SET_TIME_TIPS);
                return false;
            }
            $('.settimetip').hide();
            let systemTime = $('#servertime').text();
            let onceTimeSize = new Date(onceTime).getTime();
            let systemTimeSize = new Date(systemTime).getTime();
            if (onceTimeSize <= systemTimeSize) {
                UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_BACKUP_SET_CORRENT_TIME_TIPS);
                return false;
            }
            data.backupInfo.datetime = onceTime;
            if (data.srcInfo.dbType === CONF.DB_TYPE.TIDB) {
                $('.jobNameDiv').hide();  // 普通任务名
                $('.tidbFullJobNameDiv').show();  // TiDB完全备份任务名
            }
        } else {
            if (data.srcInfo.dbType === CONF.DB_TYPE.TIDB) {
                $('.jobNameDiv').hide();  // 普通任务名
                $('.tidbFullJobNameDiv').show();  // TiDB完全备份任务名
            }
        }
        return true;
    }

    //得到保留策略
    var getReserveStr = function () {
        let dbType = parseInt($('#dbtype').val());
        // 备份数据保留类型
        data.highInfo.reserve.strategyMode = CONF.RESERVE_STRATEGY_MODE.CHIAN;  // 默认按链
        if (dbType === CONF.DB_TYPE.MONGODB) {  // MongoDB可以配置备份数据保留类型
            data.highInfo.reserve.strategyMode = parseInt($('#reserveMode').val());
        }
        data.highInfo.reserve.type = $('#reserveType').val();
        data.highInfo.reserve.enable_flag = true;
        if (data.highInfo.node.storage_type === CONF.BD_STORAGE_TYPE.TAPE) {  // 磁带不启用保留策略
            data.highInfo.reserve.enable_flag = false;
        }
        if (CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type) {
            data.highInfo.reserve.value = $('#spinnerNumInput').val();
        } else if (CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type) {
            data.highInfo.reserve.value = $('#spinnerDayInput').val();
        }

        if (dbType === CONF.DB_TYPE.ORACLE) {
            if (oracleCurrentIsMultiTaskFlag) {  // Oracle归档日志备份任务保留策略同完备任务
                if (associatedTaskData) {
                    data.highInfo.reserve.type = associatedTaskData.reserved_strategy.reserved_type;
                    data.highInfo.reserve.value = associatedTaskData.reserved_strategy.value;
                }
            }
        }
        if (data.highInfo.reserve.value > 0) {
            return true;
        } else {
            UIToastr.showWarning(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
            return false;
        }
    }
    //得到归档策略
    var getArchiveStr = function () {
        return true;
    }

    //得到限速策略
    var getSpeedStr = function () {
        data.speedLimit = getSpeedStrategyInfo();
        return true;
    }

    /**
     * 获取Oracle跳过坏块数据
     */
    const getOracleSkipBadBlockData = () => {
        for (const index in data.srcInfo.dbInfo) {
            data.srcInfo.dbInfo[index].detail = {
                skip_datafiles_bad_block_info: [],
            };
        }
        for (const index in data.srcInfo.dbInfo) {
            let dbInfo = data.srcInfo.dbInfo[index];
            for (const key in oracleSkipBadBlockList) {
                let oracleSkipBadBlockInfo = oracleSkipBadBlockList[key];
                if (oracleSkipBadBlockInfo.eventtype === 'cluster') {
                    if (dbInfo.cluster_uuid === oracleSkipBadBlockInfo.cluster_uuid) {
                        for (const datafileInfo of oracleSkipBadBlockInfo.configure_table_space_list) {
                            data.srcInfo.dbInfo[index].detail.skip_datafiles_bad_block_info.push({
                                file_id: datafileInfo.file_id,
                                file_name: datafileInfo.file_name,
                                bad_block_num: datafileInfo.bad_block_num,
                                agent_uuid: datafileInfo.agent_uuid,
                                cluster_uuid: datafileInfo.cluster_uuid,
                                eventtype: datafileInfo.eventtype,
                                instance_name: datafileInfo.instance_name,
                                table_space_name: datafileInfo.table_space_name,
                            });
                        }
                    }
                } else if (oracleSkipBadBlockInfo.eventtype === 'instance') {
                    if (dbInfo.instancename === oracleSkipBadBlockInfo.instance_name && oracleSkipBadBlockInfo.agent_uuid === dbInfo.agentuuid) {
                        for (const datafileInfo of oracleSkipBadBlockInfo.configure_table_space_list) {
                            data.srcInfo.dbInfo[index].detail.skip_datafiles_bad_block_info.push({
                                file_id: datafileInfo.file_id,
                                file_name: datafileInfo.file_name,
                                bad_block_num: datafileInfo.bad_block_num,
                                agent_uuid: datafileInfo.agent_uuid,
                                cluster_uuid: datafileInfo.cluster_uuid,
                                eventtype: datafileInfo.eventtype,
                                instance_name: datafileInfo.instance_name,
                                table_space_name: datafileInfo.table_space_name,
                            });
                        }
                    }
                }
            }
        }
    };

    //得到高级策略
    var getHighStr = function () {
        data.agentInfo.checkdbflag = false;
        data.agentInfo.compressflag = false;
        data.agentInfo.checksumflag = false;
        data.agentInfo.archivenum = 0;
        data.agentInfo.delarchivelog = false;
        data.agentInfo.warningInfo = {};
        data.agentInfo.set_filesperset_flag = false;  // filesperset默认位置为空
        data.agentInfo.datafile_filesperset_num = 0;
        data.agentInfo.archivelog_filesperset_num = 0;
        data.agentInfo.max_object_transport_parallel_nums = 0;         // 客户端并行数量
        data.agentInfo.auto_log_backup_interval = 1;  // 日志备份间隔
        data.agentInfo.channel_count = 1;              // 通道数
        data.agentInfo.log_backup_days_flag = false;
        data.agentInfo.log_backup_days = 0;
        data.agentInfo.log_backup_times_flag = false;
        data.agentInfo.log_backup_times = 0;
        data.agentInfo.skip_inaccessible_file_flag = false;
        data.agentInfo.skip_offline_file_flag = false;
        data.agentInfo.enable_bct_flag = false;
        data.agentInfo.compress_method = 0;
        data.agentInfo.compress_level = 0;
        data.agentInfo.set_section_size_flag = false;
        data.agentInfo.section_size = 0;
        data.agentInfo.multi_task_flag = false;
        data.agentInfo.custom_rman_cmd = '';
        data.agentInfo.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;  // 忽略节点资源限制
        switch (data.srcInfo.dbType) {
            case CONF.DB_TYPE.SQLSERVER:
                data.agentInfo.checkdbflag = !!$('#checkdb').get(0).checked;
                data.agentInfo.compressflag = !!$('#sqlservercompress').get(0).checked;
                data.agentInfo.checksumflag = !!$('#checksum').get(0).checked;
                break;
            case CONF.DB_TYPE.ORACLE:
                data.agentInfo.log_backup_times_flag = !!$('#archivelogBackupTimesCheck').get(0).checked;
                if (data.agentInfo.log_backup_times_flag) {
                    data.agentInfo.log_backup_times = parseInt($('#archivelogBackupTimes').val());
                }
                data.agentInfo.log_backup_days_flag = !!$('#archivelogBackupDaysCheck').get(0).checked;
                if (data.agentInfo.log_backup_days_flag) {
                    data.agentInfo.log_backup_days = parseInt($('#archivelogBackupDays').val());
                }
                data.agentInfo.compressflag = !!$('#oraclecompress').get(0).checked;
                data.agentInfo.delarchivelog = parseInt($('input[name="deleteArchivelog"]:checked').val());
                if (3 === data.agentInfo.delarchivelog) {
                    data.agentInfo.archivenum = parseInt($('#archivelogDays').val());
                } else {
                    data.agentInfo.archivenum = 0;
                }
                data.agentInfo.checkdbflag = !!$('#oracleCheckArchivelog').get(0).checked;
                data.agentInfo.set_filesperset_flag = !!$('#oracleFilesPerset').get(0).checked;  // oracle的filesperset设置
                if (data.agentInfo.set_filesperset_flag) {
                    data.agentInfo.datafile_filesperset_num = parseInt($('#oracleFilesPersetDatafile').val());
                    data.agentInfo.archivelog_filesperset_num = parseInt($('#oracleFilesPersetArchivelog').val());
                }
                data.agentInfo.skip_inaccessible_file_flag = !!$('#skipInaccessibleFileFlag').get(0).checked;
                data.agentInfo.skip_offline_file_flag = !!$('#skipOfflineFileFlag').get(0).checked;
                if (CONF.BD_STORAGE_TYPE.TAPE !== data.highInfo.node.storage_type) {  // 非磁带才有通道数
                    // 通道数
                    data.highInfo.transfer.threadnum = parseInt($('#oracleChannelNum').val());
                    data.agentInfo.channel_count = parseInt($('#oracleChannelNum').val());
                }
                // BCT
                data.agentInfo.enable_bct_flag = !!$('#enableBctFlag').get(0).checked;
                data.agentInfo.set_section_size_flag = !!$('#setSectionSizeFlag').get(0).checked;
                if (data.agentInfo.set_section_size_flag) {
                    data.agentInfo.section_size = parseInt($('#sectionSize').val());
                }
                // 归档日志备份任务
                data.agentInfo.multi_task_flag = oracleCurrentIsMultiTaskFlag;
                // 自定义RMAN命令
                data.agentInfo.custom_rman_cmd = $('#customRmanCmd').val();
                // 跳过坏块
                getOracleSkipBadBlockData();
                break;
            case CONF.DB_TYPE.MARIA:
            case CONF.DB_TYPE.MYSQL:
                data.agentInfo.channel_count = parseInt($('#mysqlParallel').val());
                data.agentInfo.compressflag = !!$('#mysqlSrcCompressed').get(0).checked;
                break;
            case CONF.DB_TYPE.DM:
                data.agentInfo.compressflag = !!$('#dmcompress').get(0).checked;
                if (data.agentInfo.compressflag) {
                    data.agentInfo.compress_level = parseInt($('#dmCompressLevel').val());
                }
                data.agentInfo.delarchivelog = !!$('#deldmarchivelog').get(0).checked;
                break;
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.UXDB:
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.OPENGAUSS:
            case CONF.DB_TYPE.VASTBASE:
                data.agentInfo.compressflag = $('#srccompress').val();
                data.agentInfo.delarchivelog = $('#delpostgrelog').val();
                data.agentInfo.warningInfo = getWarningSettings();
                break;
            case CONF.DB_TYPE.MONGODB:
                data.agentInfo.max_object_transport_parallel_nums = parseInt($('#parallelNum').val());
                break;
            case CONF.DB_TYPE.TIDB:
                // data.agentInfo.max_object_transport_parallel_nums = parseInt($('#parallelNum').val());
                data.agentInfo.compress_method = parseInt($('#tidbCompressMethod').val());
                if (data.agentInfo.compress_method === TIDB_COMPRESS_METHOD_ENUM.zstd) {
                    data.agentInfo.compress_level = parseInt($('#tidbCompressLevel').val());
                }
                break;
            case CONF.DB_TYPE.SAPHANA:
                data.agentInfo.compressflag = !!$('#sapHanaCompress').get(0).checked;
                data.agentInfo.auto_log_backup_interval = parseInt($('#backupInterval').val()) * 60;
                if (Number.isNaN(data.agentInfo.auto_log_backup_interval)) {
                    UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_DB_SAP_HANA_LOG_INTERVAL_ERROR);
                    return false;
                }
                if (CONF.BD_STORAGE_TYPE.TAPE !== data.highInfo.node.storage_type) {  // 非磁带才有通道数
                    data.agentInfo.channel_count = parseInt($('#channelCount').val());
                }
                break;
        }

        // 重试策略
        data.retry_strategy = $('#tab_advanced_retry').retryStrategy({} ,'value');
        if (!data.retry_strategy) {
            return false;
        }
        // if (data.srcInfo.dbType == CONF.DB_TYPE.TIDB) {  // TiDB不显示任务重试
        //     $('.task_retry_flag_show').hide();
        //     $('.task_retry_object_show').hide();
        //     $('.task_retry_times_show').hide();
        //     $('.task_retry_interval_show').hide();
        // } else {
        //     $('.task_retry_flag_show').show();
        //     $('.task_retry_object_show').show();
        //     $('.task_retry_times_show').show();
        //     $('.task_retry_interval_show').show();
        // }
        return true;
    }

    /**
     * 获取备份脚本配置
     */
    const getBackupScriptConfig = () => {
        if (!initBackupScriptFlag) { // 未初始化脚本配置
            return true;
        }
        let scriptConfigSwitchFlag = !!$('#scriptConfigSwitch').get(0).checked;
        // 注入到全局变量
        for (const index in data.srcInfo.dbInfo) {
            data.srcInfo.dbInfo[index].before_task_script = [];
            data.srcInfo.dbInfo[index].after_task_script = [];
            if (!scriptConfigSwitchFlag) {
                continue;
            }
            for (const backupScriptInfo of backupScriptList) {
                if (
                    backupScriptInfo.agent_uuid === data.srcInfo.dbInfo[index].agentuuid &&
                    backupScriptInfo.instance_name === data.srcInfo.dbInfo[index].instancename &&
                    backupScriptInfo.db_name === data.srcInfo.dbInfo[index].dbname
                ) {
                    let beforeScript = $.fn.getVinScript(backupScriptInfo.before_script_config_class);
                    if (beforeScript === false) {
                        return false;
                    }
                    data.srcInfo.dbInfo[index].before_task_script = beforeScript;
                    let afterScript = $.fn.getVinScript(backupScriptInfo.after_script_config_class);
                    if (afterScript === false) {
                        return false;
                    }
                    data.srcInfo.dbInfo[index].after_task_script = afterScript;
                }
            }
        }
        return true;
    };

    /**
     * 获取安全策略
     */
    const getSafeStrategy = () => {
        let wormConfig = $('#wormConfig').getWormProtectionSettings();
        let integrityCheck = $('#integrityCheck').getCompleteDetectionBackup();
        data.safe_config_strategy = safeData(wormConfig, '', integrityCheck);
        return true;
    };

    //得到传输策略
    var getTransferStr = function () {
        data.highInfo.transfer.encrypt = $('#encrypttransfer').get(0).checked;
        // 传输加密算法
        data.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
        data.highInfo.transfer.mode = $('#transport_mode').val();
        data.highInfo.transfer.network = '';
        data.highInfo.transfer.network_pool_uuid = '';
        if (networkFlag) {
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
            if (false === networkNode) {
                return false;
            }
            if (networkNode.eventtype === 'network') {
                data.highInfo.transfer.network = networkNode.network_uuid;
            } else {
                data.highInfo.transfer.network_pool_uuid = networkNode.network_pool_uuid;
            }
        }
        // 传输线程移到传输策略里面
        data.highInfo.transfer.threadnum = 1;

        switch (data.srcInfo.dbType) {  // 传输线程
            case CONF.DB_TYPE.MONGODB:
                if (CONF.BD_STORAGE_TYPE.TAPE !== data.highInfo.node.storage_type) {  // 非磁带才有通道数
                    data.highInfo.transfer.threadnum = parseInt($('#mongodbBackupThreadNum').val());
                }
                break;
        }
        return true;
    }
    //得到存储策略
    var getStoreStr = function () {
        data.highInfo.store.compress_method = 1;  // 默认较快
        data.highInfo.store.deduplication = $('#deduplicationCheck').get(0).checked;
        data.highInfo.store.blocksize = $('#blocksize').val();
        data.highInfo.store.compress_flag = $('#compressCheck').get(0).checked;
        // 压缩等级
        if ($('#compressCheck').get(0).checked) {
            data.highInfo.store.compress_method = $('#compressGrade').val();
        }
        data.highInfo.store.valid = true;
        data.highInfo.store.encrypt = $('#encryptStorageCheck').get(0).checked;
        // 存储加密
        data.highInfo.store.encrypt_method = parseInt($('#storageEncryptMethod').val());
        data.highInfo.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;

        // 非法字符串校验
        if (!isNotLatinCode(getPassword())) {
            return false;
        }
        //得到密码
        data.highInfo.store.password = btoa(getPassword());
        var repassword = $.trim($('#repassword').val());

        // 开启数据加密
        if (data.highInfo.store.encrypt) {
            // 开启自动生成密码
            if (data.highInfo.store.password_auto_flag) {
                data.highInfo.store.password = "";
            } else {
                var tmp = $.trim($('#password').val());
                // 未应用备份策略配置的密码
                if (!_oldPassword) {
                    // 是否触发过密码输入框的change事件且密码最终值还是空,则提示 请输入数据加密的密码;没有触发过则无需校验,直接向后端提交此前接口返回的密码
                    if (passwordChangeFlag && !data.highInfo.store.password) {
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                        return false;
                    }
                    // 触发过密码输入框的change事件,但和确认密码不一致时
                    if (passwordChangeFlag && tmp !== repassword) {
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
                        return false;
                    }
                    if(!data.highInfo.store.password){
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                        return false;
                    }
                } else {// 应用备份策略配置的密码
                    // 触发过密码框的change事件,且密码为空时
                    if (passwordChangeFlag && !tmp) {
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                        return false;
                    }
                    // 没有触发过密码输入框的change事件,则是备份策略配置过的原密码和原确认密码;触发过,就要比对改变后的密码和确认密码是否一致
                    if (passwordChangeFlag && tmp !== repassword) {
                        UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
                        return false;
                    }
                }
            }
        }
        return true;
    }

    //判断字符传知否在Latin1字符集中
    var isNotLatinCode = function (string) {
        var latin1Regex = /[^\x00-\xFF]/;
        if (latin1Regex.test(string)) {
            UIToastr.showWarning(LANG.UI_DB_BACKUP_PASSWORD_ILLEAGLE, LANG.UI_DB_BACKUP_PASSWORD_TIPS);
            return false;
        }
        return true;
    }

    //存储之前检查是否有修改过密码
    var getPassword = function(){
        var now_password = $.trim($('#password').val().replace(/\s+/g, ''));
        // 备份策略已配置密码时
        if(_oldPassword != ''){
            // 若密码输入框未触发过change事件,现密码和原配置的密码_oldPassword必然相等,则返回解码后的密码
            if (!passwordChangeFlag) {
                return atob(_oldPassword);
            } else { // 若密码输入框触发过change事件,判断现密码和原配置的密码_oldPassword是否相等,相等则返回解码后的密码;不相等则返回现密码
                if (now_password === _oldPassword) {
                    return atob(now_password);
                }
            }
        } else {
            // 未应用备份策略且未触发过密码输入框的change事件,则return之前接口返回的密码
            if (!passwordChangeFlag) {
                return atob(SETTINGS.bss.password);
            } else {
                // 触发过密码输入框的change事件则return密码输入框本身的值
                return now_password;
            }
        }
    }

    var wizardInit = function () {
        if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger:not(#beforeTimeStrategyTipsAlert)', form);
        var success = $('.alert-success', form);
        let $dbBackupContent = $('#dbbackupcontent');
        var handleTitle = function (tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
            jQuery('li', $dbBackupContent).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current === 1) {
                $dbBackupContent.find('.button-previous').css('visibility', 'hidden');
                $dbBackupContent.find('.button-next').addClass('next-btn-margin-left');
            } else {
                $dbBackupContent.find('.button-previous').css('visibility', 'visible');
                $dbBackupContent.find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $dbBackupContent.find('.button-next').css('visibility', 'hidden').hide();
                $dbBackupContent.find('.button-submit').css('visibility', 'visible').show();  // 点击下一步显示提交按钮
            } else {
                $dbBackupContent.find('.button-next').css('visibility', 'visible').show();
                $dbBackupContent.find('.button-submit').css('visibility', 'hidden').hide();
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $dbBackupContent.bootstrapWizard({
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
                        initBackupTarget(() => {
                            step1Valid(() => {
                                $('a[href="#tab2"]').tab('show'); // 手动切换步骤页面
                                handleTitle(tab, navigation, index);
                                pageIndex = 1;
                            });
                        });
                        return false;
                    case 2:
                        if (!step2Valid()) {
                            return false;
                        }
                        pageIndex = 2;
                        break;
                    case 3:
                        if (!step3Valid()) {
                            return false;
                        }
                        pageIndex = 3;
                        break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();
                pageIndex = parseInt(index);

                handleTitle(tab, navigation, index);
                $dbBackupContent.find('.button-submit').css('visibility', 'hidden').hide();  // 点击上一步隐藏提交按钮
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var percent = (current / total) * 100;
                $dbBackupContent.find('.progress-bar').css({
                    width: percent + '%'
                });
            }
        });

        $dbBackupContent.find('.button-previous').css('visibility', 'hidden');
        $('#dbbackupcontent .button-submit').click(() => {
            // 授权判断
            getDbCurrentUseLicense().then(showOracleSyncConfigDialog).then(() => {
                if (pageIndex === 0) {
                    initBackupTarget(() => {
                        step1Valid(() => {
                            submit();
                        });
                    });
                    return false;
                } else if (pageIndex === 1) {
                    var step2 = step2Valid();
                    if (!step2) return false;
                } else if (pageIndex === 2) {
                    var step3 = step3Valid();
                    if (!step3) return false;
                }
                submit();
            });
        }).css('visibility', 'hidden').hide();
    };

    /**
     * 获取当前需要消耗的授权
     */
    const getCurrentUseLicenseCount = () => {
        let dbType = parseInt($('#dbtype').val());
        let currentUseAgentSet = new Set();
        for (let i = 0; i < agentList.length; i++) {
            let nodes = zTreeDB[agentList[i]].getCheckedNodes();
            for (const node of nodes) {
                if (
                    dbType === CONF.DB_TYPE.SQLSERVER ||
                    dbType === CONF.DB_TYPE.SAPHANA
                ) {
                    if (node.eventtype === 'cluster') {
                        if (node.db_backup_info.length) {  // 存在备份任务
                            continue;
                        }
                        for (const nodeInstanceInfo of node.instance_node_list) {
                            currentUseAgentSet.add(nodeInstanceInfo.agent_uuid);
                        }
                    } else if (node.eventtype === 'instance') {
                        if (node.db_backup_info.length) {  // 存在备份任务
                            continue;
                        }
                        currentUseAgentSet.add(node.agent_uuid);
                    }
                } else if (dbType === CONF.DB_TYPE.ORACLE) {
                    if (node.db_backup_info.length) {  // 存在备份任务
                        continue;
                    }
                    if (node.eventtype === 'cluster') {
                        for (const nodeInstanceInfo of node.instance_node_list) {
                            currentUseAgentSet.add(nodeInstanceInfo.agent_uuid);
                        }
                    } else if (node.eventtype === 'instance') {
                        currentUseAgentSet.add(node.agent_uuid);
                    }
                } else {
                    if (node.eventtype === 'cluster') {
                        for (const nodeInstanceInfo of node.instance_node_list) {
                            currentUseAgentSet.add(nodeInstanceInfo.agent_uuid);
                        }
                    } else if (node.eventtype === 'instance') {
                        currentUseAgentSet.add(node.agent_uuid);
                    }
                }
            }
        }
        return currentUseAgentSet.size;
    };

    /**
     * 获取已备份的Agent UUID集合
     * @param currentTaskUuid 当前任务UUID
     * @returns {Set<any>}
     */
    const getBackedUpAgentUuidSet = (currentTaskUuid) => {
        let backedUpAgentUuidSet = new Set();
        let standaloneInstances = zTreeAgent.getNodesByParam('eventtype', 'instance');
        let clusterInstances = zTreeAgent.getNodesByParam('eventtype', 'cluster');
        for (const standaloneInstance of standaloneInstances) {
            if (standaloneInstance.dbBackupFlag) {
                for (const dbBackupInfo of standaloneInstance.db_backup_info) {
                    if (dbBackupInfo.job_uuid !== currentTaskUuid) {
                        backedUpAgentUuidSet.add(standaloneInstance.agent_uuid);
                        break;
                    }
                }
            }
        }
        for (const clusterInstance of clusterInstances) {
            if (clusterInstance.dbBackupFlag) {
                for (const dbBackupInfo of clusterInstance.db_backup_info) {
                    if (dbBackupInfo.job_uuid !== currentTaskUuid) {
                        for (const instanceInfo of clusterInstance.instance_list) {
                            backedUpAgentUuidSet.add(instanceInfo.agent_info.agent_uuid);
                        }
                        break;
                    }
                }
            }
        }
        return backedUpAgentUuidSet;
    };

    /**
     * 判断授权过期时间
     * @returns {Promise<void>}
     */
    const judgeLicenseExpireTime = () => {
        return new Promise(resolve => {
            let dbType = parseInt($('#dbtype').val());
            // 判断授权过期时间
            getModuleAuthInfo({
                module: dbType === CONF.DB_TYPE.SAPHANA ? 'oracle_hana' : 'oracle',
                showMetronic: true,
                judge: false,
            }).then(res => {
                if (!res.success) {
                    UIToastr.showWarning(LANG.UI_PUBLIC_OBTAIN_LICENSE_INFO, res.message);
                    return;
                }
                if (!res.data.license_flag) {  // 授权过期了
                    UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
                    return;
                }
                resolve();
            });
        });
    };

    const getDbCurrentUseLicense = () => {
        return new Promise((resolve) => {
            judgeLicenseExpireTime().then(() => {
                let dbType = parseInt($('#dbtype').val());
                let currentTaskUuid = $('#task_uuid').val();
                let backedUpAgentUuidSet = getBackedUpAgentUuidSet(currentTaskUuid);
                // 按客户端数量授权
                let currentAgentUuidSet = new Set();
                let excludeAgentUuidList = [];
                if (dbType === CONF.DB_TYPE.TIDB) {
                    if (SETTINGS.depend_task_uuid) {  // TIDB存在子任务，要把agent信息排除掉
                        for (const oldDbInfo of SETTINGS.db_info) {
                            excludeAgentUuidList = excludeAgentUuidList.concat(oldDbInfo.agent_uuid_list || []);
                        }
                    }
                }
                let checkedSources = zTreeAgent.getNodesByParam('checked', true);
                for (const checkedSource of checkedSources) {
                    if (
                        dbType === CONF.DB_TYPE.SQLSERVER ||
                        dbType === CONF.DB_TYPE.SAPHANA ||
                        dbType === CONF.DB_TYPE.ORACLE
                    ) {
                        /**
                         * SAP HANA和SQL Server备份粒度为数据库，客户端可以创建多次
                         * Oracle备份粒度为实例/集群，可以创建子任务
                         */
                        if (checkedSource.dbBackupFlag) {  // 存在备份任务
                            let taskUuidList = checkedSource.db_backup_info.map(item => item.job_uuid);
                            taskUuidList = taskUuidList.filter(item => item !== currentTaskUuid);
                            if (taskUuidList.length) {  // 除了当前任务，还存在其他任务
                                continue;
                            }
                        }
                    }
                    if (checkedSource.eventtype === 'cluster') {
                        for (const instanceInfo of checkedSource.instance_list) {
                            currentAgentUuidSet.add(instanceInfo.agent_info.agent_uuid);
                        }
                    } else {
                        currentAgentUuidSet.add(checkedSource.agent_uuid);
                    }
                }

                /**
                 * 排除掉当前已存在备份任务的Agent
                 */
                for (const backedUpAgentUuid of backedUpAgentUuidSet) {
                    if (currentAgentUuidSet.has(backedUpAgentUuid)) {
                        currentAgentUuidSet.delete(backedUpAgentUuid);
                    }
                }
                if (!currentAgentUuidSet.size) {
                    resolve();
                    return;
                }
                let module = 'oracle';
                if (dbType === CONF.DB_TYPE.SAPHANA) {
                    module = 'oracle_hana';
                }
                let params = {
                    module,
                    currentUse: currentAgentUuidSet.size,
                    showMetronic: true,
                    judge: true,
                    showAlertMsg: true,
                    task_uuid: currentTaskUuid,
                    uuids: excludeAgentUuidList,
                }
                getModuleAuthInfo(params).then(result => {
                    if (result) {
                        resolve();
                    }
                });
            });
        });
    };

    /**
     * 显示Oracle同步配置对话框
     */
    const showOracleSyncConfigDialog = () => {
        return new Promise((resolve) => {
            let dbType = parseInt($('#dbtype').val());
            if (dbType !== CONF.DB_TYPE.ORACLE) {
                resolve();
                return;
            }
            if (oracleCurrentIsMultiTaskFlag) {  // 当前不是完备任务
                resolve();
                return;
            }
            if (associatedTaskData === null) {  // 没有关联任务
                resolve();
                return;
            }
            if (pageIndex === 0) { // 步骤1不需要弹框
                resolve();
                return;
            }
            bootbox.dialog({
                title: LANG.UI_DB_BACKUP_ORACLE_SYNC_MASTER_TASK_CONFIG,
                message: LANG.UI_DB_BACKUP_ORACLE_SYNC_MASTER_TASK_CONFIG_TIPS.replace('%S', associatedTaskData.job_name),
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
                        callback: debounce(resolve, 300),
                    }
                }
            });
        });
    };

    /**
     * 获取脚本名称
     * @param scriptList
     */
    const getScriptName = scriptList => {
        let index = 0;
        let scriptName = `${LANG.UI_DB_SCRIPT_NAME_PREFIX}_${index}`;
        while (true) {
            let repeatFlag = false;
            for (const scriptInfo of scriptList) {
                if (scriptInfo.script_name === scriptName) {
                    repeatFlag = true;
                    break;
                }
            }
            if (!repeatFlag) {
                break;
            } else {
                index++;
                scriptName = `${LANG.UI_DB_SCRIPT_NAME_PREFIX}_${index}`;
            }
        }
        return scriptName;
    };

    /**
     * 设置脚本名称
     */
    const setBackupScriptName = () => {
        for (const index in data.srcInfo.dbInfo) {
            let dbInfo = data.srcInfo.dbInfo[index];
            for (const j in dbInfo.before_task_script) {
                if (!dbInfo.before_task_script[j].script_name.length) {
                    dbInfo.before_task_script[j].script_name = getScriptName(dbInfo.before_task_script);
                }
            }
            for (const j in dbInfo.after_task_script) {
                if (!dbInfo.after_task_script[j].script_name.length) {
                    dbInfo.after_task_script[j].script_name = getScriptName(dbInfo.after_task_script);
                }
            }
            data.srcInfo.dbInfo[index] = dbInfo;
        }
    };

    /**
     * 编辑数据库备份任务
     * @param {*} data
     */
    const editDBBackupJob = data => {
        let speedStrategyType = 1;
        if (parseInt(data.speedLimit.type) === 1) {
            if (data.speedLimit.speedInfo.length) {
                speedStrategyType = data.speedLimit.speedInfo[0].type;
            }
        }
        let reqData = {
            task_name: data.taskName,
            sub_task_name: data.logTaskName,
            depend_task_uuid: data.depend_task_uuid,
            db_type: parseInt($('#dbtype').val()),
            backup_source: data.srcInfo.dbInfo.map(dbInfo => {
                return {
                    db_name: dbInfo.dbname,
                    instance_name: dbInfo.instancename,
                    agent_uuid: dbInfo.agentuuid,
                    agent_group_uuid: dbInfo.groupuuid,
                    db_uuid: dbInfo.dbuuid,
                    dir_path: dbInfo.dir_path,
                    cluster_flag: !!dbInfo.cluster_uuid.length,
                    cluster_uuid: dbInfo.cluster_uuid,
                    before_task_script: dbInfo.before_task_script,
                    after_task_script: dbInfo.after_task_script,
                    detail: dbInfo.detail,
                };
            }),
            backup_target: {
                node_uuid: data.highInfo.node.nodeuuid,
                node_pool_uuid: data.highInfo.node.node_pool_uuid,
                storage_uuid: data.highInfo.node.storageuuid,
                storage_pool_uuid: data.highInfo.node.storage_pool_uuid,
            },
            time_strategy: {
                type: data.backupInfo.type,
                full_info: {
                    mode: data.backupInfo.fullInfo.mode,
                    type: data.backupInfo.fullInfo.type,
                    start_time: data.backupInfo.fullInfo.startTime,
                    roll_flag: data.backupInfo.fullInfo.rollFlag,
                    roll_interval: data.backupInfo.fullInfo.rollInterval,
                    end_time: data.backupInfo.fullInfo.endTime,
                    roll_end_time: data.backupInfo.fullInfo.endTime,
                    days: data.backupInfo.fullInfo.days,
                    frequency: data.backupInfo.fullInfo.frequency,
                    des: data.backupInfo.fullInfo.des,
                    full_backup_compensation_flag: data.backupInfo.fullInfo.full_backup_compensation_flag,
                },
                incr_info: {
                    mode: data.backupInfo.incrInfo.mode,
                    type: data.backupInfo.incrInfo.type,
                    start_time: data.backupInfo.incrInfo.startTime,
                    roll_flag: data.backupInfo.incrInfo.rollFlag,
                    roll_interval: data.backupInfo.incrInfo.rollInterval,
                    end_time: data.backupInfo.incrInfo.endTime,
                    roll_end_time: data.backupInfo.incrInfo.endTime,
                    days: data.backupInfo.incrInfo.days,
                    frequency: data.backupInfo.incrInfo.frequency,
                    des: data.backupInfo.incrInfo.des,
                },
                diff_info: {
                    mode: data.backupInfo.diffInfo.mode,
                    type: data.backupInfo.diffInfo.type,
                    start_time: data.backupInfo.diffInfo.startTime,
                    roll_flag: data.backupInfo.diffInfo.rollFlag,
                    roll_interval: data.backupInfo.diffInfo.rollInterval,
                    end_time: data.backupInfo.diffInfo.endTime,
                    roll_end_time: data.backupInfo.diffInfo.endTime,
                    days: data.backupInfo.diffInfo.days,
                    frequency: data.backupInfo.diffInfo.frequency,
                    des: data.backupInfo.diffInfo.des,
                },
                log_info: {
                    mode: data.backupInfo.logInfo.mode,
                    type: data.backupInfo.logInfo.type,
                    start_time: data.backupInfo.logInfo.startTime,
                    roll_flag: data.backupInfo.logInfo.rollFlag,
                    roll_interval: data.backupInfo.logInfo.rollInterval,
                    end_time: data.backupInfo.logInfo.endTime,
                    roll_end_time: data.backupInfo.logInfo.endTime,
                    days: data.backupInfo.logInfo.days,
                    frequency: data.backupInfo.logInfo.frequency,
                    des: data.backupInfo.logInfo.des,
                },
                pincr_info: {
                    mode: data.backupInfo.pIncrInfo.mode,
                    type: data.backupInfo.pIncrInfo.type,
                    start_time: data.backupInfo.pIncrInfo.startTime,
                    roll_flag: data.backupInfo.pIncrInfo.rollFlag,
                    roll_interval: data.backupInfo.pIncrInfo.rollInterval,
                    end_time: data.backupInfo.pIncrInfo.endTime,
                    roll_end_time: data.backupInfo.pIncrInfo.endTime,
                    days: data.backupInfo.pIncrInfo.days,
                    frequency: data.backupInfo.pIncrInfo.frequency,
                    des: data.backupInfo.pIncrInfo.des,
                },
                datetime: data.backupInfo.datetime,
            },
            speed_strategy: {
                level: data.speedLimit.level,
                type: data.speedLimit.type,
                uuid: data.speedLimit.uuid,
                name: data.speedLimit.name,
                strategy_type: speedStrategyType,
                speedInfo: data.speedLimit.speedInfo,
            },
            storage_strategy: {
                deduplication_flag: data.highInfo.store.deduplication,
                compress_flag: data.highInfo.store.compress_flag,
                compress_method: data.highInfo.store.compress_method,
                encrypt_flag: data.highInfo.store.encrypt,
                encrypt_method: data.highInfo.store.encrypt_method,
                auto_password_flag: data.highInfo.store.password_auto_flag,
                password: data.highInfo.store.password,
                block_size: data.highInfo.store.blocksize,
            },
            reserved_strategy: {
                reserved_type: data.highInfo.reserve.type,
                value: data.highInfo.reserve.value,
                enable_flag: data.highInfo.reserve.enable_flag,
                reserved_mode: data.highInfo.reserve.strategyMode,
            },
            transport_strategy: {
                encrypt_flag: data.highInfo.transfer.encrypt,
                encrypt_method: data.highInfo.transfer.encrypt_method,
                transport_mode: $('#transport_mode').val(),
                network_uuid: data.highInfo.transfer.network,
                network_pool_uuid: data.highInfo.transfer.network_pool_uuid,
                max_object_transport_parallel_nums: data.agentInfo.max_object_transport_parallel_nums,  // MongoDB: 客户端并行数量
            },
            advanced_strategy: {
                check_db_flag: data.agentInfo.checkdbflag,  // SQL server: 是否检测数据库【true检测 false不检测】
                compress_flag: data.agentInfo.compressflag,  // SQL Server: 是否开启SQL Server压缩【true开启 false不开启】Oracle: 是否开启Oracle压缩【true开启 false不开启】DM: 是否开启DM压缩【true开启 false不开启】SAP HANA: 是否开启SAP HANA压缩【true开启 false不开启】
                checksum_flag: data.agentInfo.checksumflag,  // SQL server: 是否开启SQL Server校验【true开启 false不开启】
                thread_num: data.highInfo.transfer.threadnum,  // 传输线程
                channel_count: data.agentInfo.channel_count,  // 通道数【SAPHANA MySQL MariaDB Oracle】
                delete_archivelog_flag: data.agentInfo.delarchivelog,  // DM: 是否删除已备份的归档日志【true删除 false不删除】 postgres: 删除归档日志类别：1归档备份后删除 2不删除 3删除全部oracle：归档日志保留策略：1删除所有已备份的归档日志 2不删除归档日志 3保留x天的归档日志不删除
                archive_num: data.agentInfo.archivenum,  // Oracle: delete_archivelog_type为3时，保留x天的归档日志不删除；否则为0
                warning_setting: data.agentInfo.warningInfo,  // 告警配置
                set_filesperset_flag: data.agentInfo.set_filesperset_flag,  // Oracle: 是否配置filesperset
                datafile_filesperset_num: data.agentInfo.datafile_filesperset_num,  // Oracle: datafile个数
                archivelog_filesperset_num: data.agentInfo.archivelog_filesperset_num,  // Oracle: archivelog个数
                auto_log_backup_interval: data.agentInfo.auto_log_backup_interval,  // SAP HANA: 日志备份间隔（秒）
                log_backup_times_flag: data.agentInfo.log_backup_times_flag,  // Oracle：归档日志备份次数【true开启 false关闭】
                log_backup_times: data.agentInfo.log_backup_times,  // Oracle：最大重复备份次数
                log_backup_days_flag: data.agentInfo.log_backup_days_flag,  // Oracle：归档日志备份天数【true开启 false关闭】
                log_backup_days: data.agentInfo.log_backup_days,  // Oracle：日志最近备份天数
                skip_inaccessible_file_flag: data.agentInfo.skip_inaccessible_file_flag,  // Oracle：跳过不可访问文件【true开启 false关闭】
                skip_offline_file_flag: data.agentInfo.skip_offline_file_flag,  // Oracle：跳过脱机文件【true开启 false关闭】
                enable_bct_flag: data.agentInfo.enable_bct_flag,  // Oracle：bct增量备份效率
                set_section_size_flag: data.agentInfo.set_section_size_flag,  // Oracle：是否分段传输【true是 false否】
                section_size: data.agentInfo.section_size,  // Oracle：分块大小【单位GB】
                compress_method: data.agentInfo.compress_method,  // TiDB：压缩方式【1lz4 2snappy 3zstd】
                compress_level: data.agentInfo.compress_level,  // TiDB：压缩等级【压缩方式为zstd时设置，1~16】 DM: 压缩方式【1~9 默认5】
                multi_task_flag: data.agentInfo.multi_task_flag,  // Oracle: 多任务备份任务标识【true多任务 false普通任务】
                custom_rman_cmd: data.agentInfo.custom_rman_cmd,  // Oracle：自定义RMA命令 其他数据库为空
                ignore_resource_limiting_flag: data.agentInfo.ignore_resource_limiting_flag,  // 过载保护-忽略节点资源限制【true忽略限制 false限制】
            },
            strategy_group_uuid: $('#strategySelect option:selected').val(),
            retry_strategy: data.retry_strategy,  // 重试策略
            safe_config_strategy: data.safe_config_strategy,  // 安全策略
        };
        Metronic.blockUI({target: '#dbbackupcontent', animate: true, cenrerY: true});
        pAjaxRequest(reqData, `/api/v1/db/jobs/${$('#task_uuid').val()}/backup`, 'PUT', res => {
            Metronic.unblockUI('#dbbackupcontent');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_DB_BACKUP_EDIT_TITLE, res.message);
            LOCATION('./content/platform/jobs/jobs.php', 'task');
        });
    };

    /**
     * 验证任务名称
     */
    const validateTaskName = () => {
        let dbType = parseInt($('#dbtype').val());
        data.logTaskName = '';
        data.depend_task_uuid = '';
        switch (dbType) {
            case CONF.DB_TYPE.TIDB:
                let tidbFullTaskName = $.trim($("#tidbFullJobName").val());
                if (!tidbFullTaskName) {
                    $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
                    return false;
                }
                data.taskName = tidbFullTaskName;
                if (SETTINGS.sub_task_flag) {
                    data.depend_task_uuid = SETTINGS.depend_task_uuid;
                }
                switch (data.backupInfo.type) {
                    case 'strategy':
                        if ($('#logBackup').prop('checked')) {  // 勾选了日志备份
                            let tidbLogTaskName = $.trim($("#tidbLogJobName").val());
                            if (!tidbLogTaskName) {
                                $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
                                return false;
                            }
                            data.logTaskName = tidbLogTaskName;
                        }
                        break;
                }
                $('.jobnametip').hide();
                break;
            case CONF.DB_TYPE.ORACLE:
                if (oracleCurrentIsMultiTaskFlag) {
                    data.depend_task_uuid = SETTINGS.depend_task_uuid;
                    let checkedNodes = zTreeAgent.getNodesByParam('checked', true);
                    let dbBackupList = checkedNodes[0].db_backup_info;
                    // 修改备份源后，depend_task_uuid会变
                    for (const dbBackupInfo of dbBackupList) {
                        if (!dbBackupInfo.multi_task_flag) {
                            data.depend_task_uuid = dbBackupInfo.job_uuid;
                            break;
                        }
                    }
                }
                // fallthrough
            default:
                let taskName = $.trim($("#jobname").val());
                if (!taskName) {
                    $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
                    return false;
                }
                $('.jobnametip').hide();
                let jobName = $.trim($("#jobname").val());
                // 输入验证
                if(!customInputValidate('string',jobName)){
                    return false;
                }
                data.taskName = taskName;
                break;
        }
        return true;
    };

    var submit = function () {
        var dbType = parseInt($('#dbtype').val());
        switch (dbType) {
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.UXDB:
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.OPENGAUSS:
            case CONF.DB_TYPE.VASTBASE:
                data.agentInfo.warningInfo = getWarningSettings();
                break;
        }
        data.speedLimit = getSpeedStrategyInfo();
        if (!validateTaskName()) {
            return;
        }
        // 设置脚本名称
        setBackupScriptName();
        //TODO提交
        editDBBackupJob(data);
    }

    //////////////////// 开始-初始化备份树 ////////////////////

    /**
     * 节点点击了
     * @param {*} treeId
     * @param {*} treeNode
     */
    const clickBackupNode = (treeId, treeNode) => {
        if (treeNode.eventtype === 'category' || treeNode.eventtype === 'cluster') {
            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true);
        }
        switch (treeNode.eventtype) {
            case 'category':
            case 'cluster_shard':
            case 'cluster_config_server':
            case 'cluster_mongos':
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true);
                break;
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
     * 将的备份节点添加到右侧中
     * @param {*} treeNode
     */
    const addBackupNodetList = function (treeNode) {
        let selectedBackupKey;
        if (treeNode.eventtype === 'cluster') {
            selectedBackupKey = treeNode.cluster_uuid;
        } else if (treeNode.eventtype === 'instance') {
            selectedBackupKey = treeNode.app_uuid;
        }
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
                '<div class="panel-body" style="padding: 16px; max-height: 200px; overflow-y: auto">' +
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
     * 点击了已选的节点
     * @param {*} treeId
     * @param {*} treeNode
     */
    const clickSelectedBackupNode = (treeId, treeNode) => {
        switch (treeNode.eventtype) {
            case 'cluster':
            case 'instance':
            case 'db':
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
                break;
        }
    };

    /**
     * 取消勾选备份源
     * @param treeNode
     */
    const uncheckClusterInstanceNode = (treeNode) => {
        if (treeNode.eventtype !== 'instance' && treeNode.eventtype !== 'cluster') {
            return;
        }
        let dbType = parseInt($('#dbtype').val());
        if (dbType === CONF.DB_TYPE.SQLSERVER || dbType === CONF.DB_TYPE.SAPHANA) {
            return;
        }
        let uncheckNode = zTreeAgent.getNodeByParam('id', treeNode.selected_tree_node_id);
        if (uncheckNode) {
            zTreeAgent.checkNode(uncheckNode, false, true, true);
        }
    };

    /**
     * 选择了已选的节点
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const checkSelectedBackupNode = (ev, treeId, treeNode) => {
        if (treeNode.eventtype === 'cluster' || treeNode.eventtype === 'instance') {
            if (typeof treeNode.children === 'undefined' || !treeNode.children.length) {
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, false, true, true);
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, false, true);
                return;
            }
        }
        if (!treeNode.checked) {
            uncheckClusterInstanceNode(treeNode);
            return;
        }
        let dbType = parseInt($('#dbtype').val());
        switch (dbType) {
            case CONF.DB_TYPE.SQLSERVER:
                // SQL Server数据库简单模式不能与非简单模式混合选择 #24683
                let checkedNodes = $.fn.zTree.getZTreeObj(treeId).getCheckedNodes(true);
                let dbSimpleModeFlag = false;
                if (parseInt(treeNode.recovery_mode) === SQLSERVER_RECOVERY_MODE_ENUM.SIMPLE) {
                    dbSimpleModeFlag = true;
                }
                let mixedRecoveryModeFlag = false;
                for (const checkedNode of checkedNodes) {
                    if (checkedNode.eventtype !== 'db') {
                        continue;
                    }
                    if (dbSimpleModeFlag) {
                        if (checkedNode.recovery_mode !== SQLSERVER_RECOVERY_MODE_ENUM.SIMPLE) {
                            mixedRecoveryModeFlag = true;
                            break;
                        }
                    } else {
                        if (checkedNode.recovery_mode === SQLSERVER_RECOVERY_MODE_ENUM.SIMPLE) {
                            mixedRecoveryModeFlag = true;
                            break;
                        }
                    }
                }
                if (mixedRecoveryModeFlag) {
                    $.fn.zTree.getZTreeObj(treeId).checkAllNodes(false);
                    $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, true, true, false);
                    UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, LANG.UI_DB_BACKUP_SQLSERVER_MIXED_SIMPLE_AND_OTHER_MODE);
                    if (treeNode.eventtype !== 'db') {
                        $.fn.zTree.getZTreeObj(treeId).checkAllNodes(false);
                    }
                    return;
                }
                break;
        }
    };

    /**
     * 加载了已选的节点的表空间
     * @param {*} treeId
     * @param {*} treeNode
     */
    const loadInstanceTableSpace = (treeId, treeNode) => {
        let dbType = parseInt($('#dbtype').val());
        if (treeNode.eventtype !== 'cluster' && treeNode.eventtype !== 'instance') {
            return;
        }
        if (typeof treeNode.children !== 'undefined' && treeNode.children.length) {
            return;
        }

        // 加载数据库/表空间信息
        Metronic.blockUI({target: `#dbAgentDiv_${treeNode.top_id}`, animate: true});
        pAjaxRequest({app_uuid: treeNode.app_uuid}, `/api/v1/db/databases`, 'GET', res => {
            Metronic.unblockUI(`#dbAgentDiv_${treeNode.top_id}`);
            if (!res.success) {
                if (dbType == CONF.DB_TYPE.SQLSERVER || dbType == CONF.DB_TYPE.SAPHANA) {  // 没有获取到数据库列表，不能够选备份
                    $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, false);
                    $.fn.zTree.getZTreeObj(treeId).setChkDisabled(treeNode, true);
                }
                return;
            }
            let nodes = [];
            let chkDisabledCount = 0;
            let checkedCount = 0;
            let oldDbInfo = SETTINGS.db_info;
            for (const dbIndex in res.data.rows) {
                let dbInfo = res.data.rows[dbIndex];
                let nocheck = true;
                let checked = false;
                let chkDisabled = false;
                let title = dbInfo.db_name;
                let inbackup = false;
                let isParent = false;
                let currentTaskFlag = false;
                let currentDbUuid = '';

                if (dbType == CONF.DB_TYPE.SQLSERVER || dbType == CONF.DB_TYPE.SAPHANA) {
                    nocheck = false;
                    for (const oldDb of oldDbInfo) {
                        if (oldDb.cluster_flag !== treeNode.cluster_flag) {
                            continue;
                        }
                        if (oldDb.cluster_flag) {
                            if (
                                oldDb.cluster_uuid === dbInfo.cluster_uuid &&
                                oldDb.dbname === dbInfo.db_name
                            ) {
                                currentTaskFlag = true;
                                currentDbUuid = oldDb.dbuuid;
                                break;
                            }
                        } else {
                            if (
                                oldDb.agentuuid === dbInfo.agent_uuid &&
                                oldDb.instancename === dbInfo.instance_name &&
                                oldDb.dbname === dbInfo.db_name
                            ) {
                                currentTaskFlag = true;
                                currentDbUuid = oldDb.dbuuid;
                                break;
                            }
                        }
                    }
                    checked = false;
                    if (currentTaskFlag) {
                        checked = true;
                        inbackup = true;
                        checkedCount++;
                        title += "('" + dbInfo.job_name + "'" + LANG.UI_DB_BACKUP_TASK_PROTECTED + ')';
                    } else {
                        if (dbInfo.is_backup_job) {
                            checked = false;
                            chkDisabled = true;
                            inbackup = true;
                            chkDisabledCount++;
                            title += "('" + dbInfo.job_name + "'" + LANG.UI_DB_BACKUP_TASK_PROTECTED + ')';
                        } else if (dbInfo.is_cluster_backup_job) {
                            checked = false;
                            chkDisabled = true;
                            inbackup = true;
                            chkDisabledCount++;
                            title += "('" + dbInfo.cluster_job_name + "'" + LANG.UI_DB_BACKUP_TASK_PROTECTED + ')';
                        }
                    }
                } else if (dbType == CONF.DB_TYPE.ORACLE) {
                    isParent = true;
                }
                nodes.push({
                    id: treeNode.id + dbInfo.db_name,
                    pId: treeNode.id,
                    name: dbInfo.db_name,
                    title,
                    eventtype: 'db',
                    app_uuid: dbInfo.app_uuid,
                    agent_uuid: dbInfo.agent_uuid,
                    instance_name: dbInfo.instance_name,
                    db_name: dbInfo.db_name,
                    recovery_mode: dbInfo.recovery_mode,  // SQL Server恢复模式
                    db_index: dbIndex,
                    agent_info: treeNode.agent_info,
                    isParent,
                    open: false,
                    nocheck,
                    chkDisabled,
                    checked,
                    icon: './img/platform/storage.png',
                    top_id: treeNode.top_id,
                    inbackup,
                    cluster_flag: treeNode.cluster_flag,
                    cluster_uuid: treeNode.cluster_uuid,
                    cluster_name: treeNode.cluster_name,
                    cluster_instance_show_name: treeNode.name,
                    instance_node_list: treeNode.instance_node_list,
                    group_uuid: treeNode.group_uuid,
                    group_name: treeNode.group_name,
                    db_uuid: currentDbUuid,
                });
            }
            $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, nodes, true);
            if (chkDisabledCount === nodes.length) {
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, false);
                $.fn.zTree.getZTreeObj(treeId).setChkDisabled(treeNode, true);
            }
        });
    };

    /**
     * 加载了已选的节点的表空间的数据文件
     * @param {*} treeId
     * @param {*} treeNode
     */
    const loadTableSpaceDataFile = (treeId, treeNode) => {
        let dbType = parseInt($('#dbtype').val());
        if (treeNode.eventtype !== 'db') {
            return;
        }
        if (typeof treeNode.children !== 'undefined' && treeNode.children.length) {  // 已加载数据库
            return;
        }

        let reqData = {
            db_type: dbType,
            table_space_name: treeNode.name,
            agent_uuid: treeNode.agent_uuid,
            instance_name: treeNode.instance_name,
        };
        // 加载数据库/表空间信息
        Metronic.blockUI({target: `#dbAgentDiv_${treeNode.top_id}`, animate: true});
        pAjaxRequest(reqData, `/api/v1/db/data_file`, 'GET', res => {
            Metronic.unblockUI(`#dbAgentDiv_${treeNode.top_id}`);
            if (!res.success) {
                return;
            }
            let nodes = [];
            for (const dataFileInfo of res.data.rows) {
                nodes.push({
                    id: treeNode.id + '_' +  dataFileInfo.file_name,
                    pId: treeNode.id,
                    name: dataFileInfo.file_name,
                    title: dataFileInfo.file_name,
                    eventtype: 'data_file',
                    isParent: false,
                    nocheck: true,
                    icon: './img/fs/wenjian.png',
                });
            }
            $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, nodes, true);
        });
    };

    /**
     * 展开了了已选的节点
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const expandSelectedBackupNode = (treeId, treeNode) => {
        let dbType = parseInt($('#dbtype').val());
        switch (treeNode.eventtype) {
            case 'cluster':
            case 'instance':
                loadInstanceTableSpace(treeId, treeNode);
                break;
            case 'db':  // 加载数据文件夹
                if (dbType == CONF.DB_TYPE.ORACLE) {
                    loadTableSpaceDataFile(treeId, treeNode);
                }
            default:
                break;
        }
    };

    /**
     * 获取备份树的设置
     */
    const getSelectedBackupNodeTreeSetting = () => {
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
                    var css = {color: "#333", "font-weight": "normal"};
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
                beforeClick: clickSelectedBackupNode,
                onCheck: checkSelectedBackupNode,
                beforeExpand: expandSelectedBackupNode
            }
        };
    };

    /**
     * 初始化已选择的备份树
     * @param {*} selectedTreeNode
     */
    const initSelectedBackupNodeTree = (selectedTreeNode) => {
        let dbType = parseInt($('#dbtype').val());
        let nodes = [];
        let selectedBackupKey;
        if (selectedTreeNode.eventtype === 'cluster') {
            let agentUuid = selectedTreeNode.agent_uuid;
            let appUuid = selectedTreeNode.app_uuid;
            let instanceName = selectedTreeNode.instance_name;
            let dbBackupInfo = selectedTreeNode.db_backup_info;
            let dbUuid = '';
            selectedBackupKey = selectedTreeNode.cluster_uuid;
            let chkDisabled = false;
            let name  = selectedTreeNode.name;
            let title = selectedTreeNode.name;
            let inbackup = false;
            let checked = true;
            let currentBackupTaskFlag = false;

            if (dbType != CONF.DB_TYPE.SQLSERVER && dbType != CONF.DB_TYPE.SAPHANA) {
                if (dbBackupInfo.length) {  // 存在备份任务
                    title += "('" + dbBackupInfo[0].job_name + "'" + LANG.UI_DB_BACKUP_TASK_PROTECTED + ')';
                    inbackup = true;
                    for (const oldDbInfo of SETTINGS.db_info) {
                        if (oldDbInfo.cluster_flag && oldDbInfo.cluster_uuid === selectedTreeNode.cluster_uuid) {
                            dbUuid = oldDbInfo.dbuuid;
                            break;
                        }
                    }
                    for (const dbBackupItem of dbBackupInfo) {
                        if (dbBackupItem.job_uuid === SETTINGS.taskuuid) {
                            currentBackupTaskFlag = true;
                            break;
                        }
                    }
                    if (currentBackupTaskFlag) {
                        chkDisabled = false;
                        checked = true;
                    } else {
                        chkDisabled = true;
                        checked = false;
                    }
                    if (dbType === CONF.DB_TYPE.ORACLE) {  // Oracle支持二次备份
                        let backupTaskNameList = [];
                        for (const dbBackupItem of dbBackupInfo) {
                            backupTaskNameList.push(dbBackupItem.job_name);
                        }
                        title = selectedTreeNode.name + "('" + backupTaskNameList.join("'、'") + "'" + LANG.UI_DB_BACKUP_TASK_PROTECTED + ')';
                        chkDisabled = false;
                        checked = true;
                    }
                }
            } else {
                checked = false;
            }
            let instanceNodeList = selectedTreeNode.children;
            if (dbType === CONF.DB_TYPE.MONGODB) {
                instanceNodeList = selectedTreeNode.instance_list;
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
                agent_uuid: agentUuid,
                agent_info: selectedTreeNode.agent_info,
                checked,
                chkDisabled,
                inbackup,
                instance_node_list: instanceNodeList,
                app_service_name: selectedTreeNode.app_service_name,
                db_uuid: dbUuid,
                db_backup_info: dbBackupInfo,
                top_id: selectedBackupKey,
                cluster_flag: true,
                cluster_name: selectedTreeNode.cluster_name,
                group_uuid: selectedTreeNode.group_uuid,
                group_name: selectedTreeNode.group_name,
                selected_tree_node_id: selectedTreeNode.id,
            });
        } else if (selectedTreeNode.eventtype === 'instance') {
            selectedBackupKey = selectedTreeNode.app_uuid;
            let chkDisabled = false;
            let name  = selectedTreeNode.name;
            let title = selectedTreeNode.name;
            let inbackup = false;
            let checked = true;
            let dbUuid = '';
            let currentBackupTaskFlag = false;

            if (dbType != CONF.DB_TYPE.SQLSERVER && dbType != CONF.DB_TYPE.SAPHANA) {
                if (selectedTreeNode.db_backup_info.length) {  // 存在备份任务
                    title += "('" + selectedTreeNode.db_backup_info[0].job_name + "'" + LANG.UI_DB_BACKUP_TASK_PROTECTED + ')';
                    inbackup = true;
                    for (const oldDbInfo of SETTINGS.db_info) {
                        if (
                            !oldDbInfo.cluster_flag &&
                            oldDbInfo.agentuuid === selectedTreeNode.agent_uuid &&
                            oldDbInfo.instancename === selectedTreeNode.instance_name
                        ) {
                            dbUuid = oldDbInfo.dbuuid;
                            break;
                        }
                    }
                    for (const dbBackupItem of selectedTreeNode.db_backup_info) {
                        if (dbBackupItem.job_uuid === SETTINGS.taskuuid) {
                            currentBackupTaskFlag = true;
                            break;
                        }
                    }
                    if (currentBackupTaskFlag) {
                        chkDisabled = false;
                        checked = true;
                    } else {
                        chkDisabled = true;
                        checked = false;
                    }
                    if (dbType === CONF.DB_TYPE.ORACLE) {  // Oracle支持二次备份
                        let backupTaskNameList = [];
                        for (const dbBackupItem of selectedTreeNode.db_backup_info) {
                            backupTaskNameList.push(dbBackupItem.job_name);
                        }
                        title = selectedTreeNode.name + "('" + backupTaskNameList.join("'、'") + "'" + LANG.UI_DB_BACKUP_TASK_PROTECTED + ')';
                        chkDisabled = false;
                        checked = true;
                    }
                }
            } else {
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
                agent_info: selectedTreeNode.agent_info,
                checked,
                chkDisabled,
                inbackup,
                instance_node_list: [],
                app_service_name: '',
                db_uuid: dbUuid,
                db_backup_info: selectedTreeNode.db_backup_info,
                top_id: selectedBackupKey,
                cluster_flag: false,
                cluster_uuid: '',
                cluster_name: '',
                group_uuid: selectedTreeNode.group_uuid,
                group_name: selectedTreeNode.group_name,
                selected_tree_node_id: selectedTreeNode.id,
            });
        }

        zTreeDB[selectedBackupKey] = $.fn.zTree.init($('#dbAgentTree_' + selectedBackupKey), getSelectedBackupNodeTreeSetting(), nodes);
        // 默认展开
        let allNodes = zTreeDB[selectedBackupKey].getNodes();
        zTreeDB[selectedBackupKey].expandNode(allNodes[0], true, false, true, true);
    };

    /**
     * 节点选中了
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const checkBackupNode = async (ev, treeId, treeNode) => {
        if (
            treeNode.eventtype === 'cluster' ||  // 显示集群
            (treeNode.eventtype === 'instance' && treeNode.pId ==='standalone')  // 单实例
        ) {
            if (CONF.DB_TYPE.ORACLE === treeNode.db_type) {
                /**
                 * Oracle归档日志备份任务特殊逻辑
                 * 1. 不能混合选择(有备份与非备份)
                 * 2. 不能跨备份任务选择
                 * 3. 仅能创建一个普通备份任务一个归档日志备份任务
                 * 4. 如果仅有归档日志备份任务，那么需删除后才能创建普通备份任务
                 * 5. 如果存在备份任务，且备份任务的状态不是停止状态，那么不能修改备份任务(修改任务外层适配了,当前不适配)
                 * 6. 归档日志任务只能是一对一的关系，不能选择多个实例
                 * 7. 如果当前任务是完备任务且没有归档日志任务，那么不能选择有完备任务的实例/集群
                 * 8. 如果当前任务是完备任务且有归档日志任务，那么不能取消备份源
                 * 9. 如果当前任务是归档日志任务，那么不能取消备份源
                 */
                // 修改任务套判断是否为当前编辑任务
                if (treeNode.checked) {
                    let mixedBackupFlag = false;
                    let diffBackupFlag = false;
                    let checkedNodes = zTreeAgent.getNodesByParam('checked', true);
                    let backupTaskFlag = false;
                    let logBackupTaskFlag = false;
                    let currentBackupTaskFlag = false;
                    for (const checkedNode of checkedNodes) {
                        // 判断混合
                        if (checkedNode.dbBackupFlag !== treeNode.dbBackupFlag) {
                            if (treeNode.dbBackupFlag) {
                                for (const dbBackupItem of treeNode.db_backup_info) {
                                    if (dbBackupItem.multi_task_flag) {
                                        mixedBackupFlag = true;
                                        break;
                                    }
                                }
                            }
                            if (checkedNode.dbBackupFlag) {
                                for (const dbBackupItem of checkedNode.db_backup_info) {
                                    if (dbBackupItem.multi_task_flag) {
                                        mixedBackupFlag = true;
                                        break;
                                    }
                                }
                            }
                            if (mixedBackupFlag) {
                                break;
                            }
                            break;
                        }
                        if (!treeNode.dbBackupFlag) {
                            continue;
                        }
                        if (treeNode.db_backup_info[0].job_uuid !== checkedNode.db_backup_info[0].job_uuid) {
                            diffBackupFlag = true;
                            break;
                        }
                        for (const dbBackupItem of treeNode.db_backup_info) {
                            if (dbBackupItem.job_uuid === SETTINGS.taskuuid) {
                                currentBackupTaskFlag = true;
                                continue;
                            }
                            if (dbBackupItem.multi_task_flag) {
                                logBackupTaskFlag = true;
                            } else {
                                backupTaskFlag = true;
                            }
                        }
                    }
                    if (mixedBackupFlag) {
                        zTreeAgent.checkNode(treeNode, false);
                        UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, LANG.UI_DB_BACKUP_ORACLE_MIXED_LOG_BACKUP);
                        return;
                    }
                    if (diffBackupFlag && !currentBackupTaskFlag) {
                        zTreeAgent.checkNode(treeNode, false);
                        UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, LANG.UI_DB_BACKUP_ORACLE_DIFF_LOG_BACKUP);
                        return;
                    }
                    if (logBackupTaskFlag && backupTaskFlag && !currentBackupTaskFlag) {
                        zTreeAgent.checkNode(treeNode, false);
                        let taskName = '';
                        let logTaskName = '';
                        for (const dbBackupInfo of treeNode.db_backup_info) {
                            if (dbBackupInfo.multi_task_flag) {
                                logTaskName = dbBackupInfo.job_name;
                            } else {
                                taskName = dbBackupInfo.job_name;
                            }
                        }
                        let message = LANG.UI_DB_BACKUP_ORACLE_BOTH_BACKUP_TIPS.replace('%1s', treeNode.name).replace('%2s', taskName).replace('%3s', logTaskName)
                        UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, message);
                        return;
                    }
                    if (logBackupTaskFlag && !currentBackupTaskFlag) {
                        zTreeAgent.checkNode(treeNode, false);
                        UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, LANG.UI_DB_BACKUP_ORACLE_ONLY_BACKUP_TIPS);
                        return;
                    }
                    if (backupTaskFlag) {  // 新实例拥有完备任务
                        try {
                            let dbBackupData = await getDbBackupTask({}, treeNode.db_backup_info[0].job_uuid);
                            // 6. 归档日志任务只能是一对一的关系，不能选择多个实例
                            if (dbBackupData.backup_source.length > 1) {
                                zTreeAgent.checkNode(treeNode, false);
                                let message = LANG.UI_DB_BACKUP_ORACLE_MASTER_TASK_HAS_MORE_INSTANCE
                                    .replace('%1s', treeNode.name)
                                    .replace('%2s', dbBackupData.job_name);
                                UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, message);
                                return;
                            } else {
                                if (!SETTINGS.agentInfo.multi_task_flag) {  // 当前修改的是完备任务
                                    if (!currentBackupTaskFlag) {  // 修改的不是当前任务
                                        // 7. 如果当前任务是完备任务，那么不能选择有完备任务的实例/集群
                                        let message = LANG.UI_DB_BACKUP_ORACLE_MASTER_TASK_NEW_INSTANCE_MASTER_TASK
                                            .replace('%1s', treeNode.name)
                                            .replace('%2s', dbBackupData.job_name);
                                        zTreeAgent.checkNode(treeNode, false);
                                        UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, message);
                                        return;
                                    }
                                } else {  // 当前修改的是归档日志任务
                                    if (!currentBackupTaskFlag) {
                                        // 9. 如果当前任务是归档日志任务，那么不能取消备份源
                                        let message = LANG.UI_DB_BACKUP_ORACLE_SLAVE_TASK_NOT_ALLOW_CHANGE_INSTANCE
                                            .replace('%1s', SETTINGS.taskname);
                                        zTreeAgent.checkNode(treeNode, false);
                                        UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, message);
                                        return;
                                    }
                                }
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    }

                    //  新实例没有任务
                    if (!backupTaskFlag && !logBackupTaskFlag && !currentBackupTaskFlag) {
                        if (!SETTINGS.agentInfo.multi_task_flag) {  // 当前修改的是完备任务
                            let dbBackupData = await getDbBackupTask({}, SETTINGS.taskuuid);
                            if (dbBackupData.associated_job_info.associated_job_flag) {  // 当前修改的任务存在子任务
                                // 8. 如果当前任务是完备任务且有归档日志任务，那么不能取消备份源
                                let message = LANG.UI_DB_BACKUP_ORACLE_MASTER_TASK_HAS_SLAVE_TASK_NOT_ALLOW_CHANGE_INSTANCE
                                    .replace('%1s', dbBackupData.backup_source[0].dir_path)
                                    .replace('%2s', dbBackupData.job_name)
                                    .replace('%3s', dbBackupData.associated_job_info.associated_job_name);
                                zTreeAgent.checkNode(treeNode, false);
                                UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, message);
                                return;
                            }
                        } else {  // 当前修改的是子任务
                            // 9. 如果当前任务是归档日志任务，那么不能取消备份源
                            let message = LANG.UI_DB_BACKUP_ORACLE_SLAVE_TASK_NOT_ALLOW_CHANGE_INSTANCE
                                .replace('%1s', SETTINGS.taskname);
                            zTreeAgent.checkNode(treeNode, false);
                            UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, message);
                            return;
                        }
                    }
                }
            }
            addBackupNodetList(treeNode);
            if (treeNode.checked) {
                initSelectedBackupNodeTree(treeNode);
                // TiDB不支持一个任务备份多个个对象
                if (CONF.DB_TYPE.TIDB === treeNode.db_type) {
                    let checkNodes = zTreeAgent.getNodesByParam('checked', true);
                    for (const checkNode of checkNodes) {
                        if (checkNode.id != treeNode.id) {
                            zTreeAgent.checkNode(checkNode, false, true, true);
                        }
                    }
                }
            }
        }

        var divChildren = $('#allDbTree').children();
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
     * 获取备份树的配置
     * @returns
     */
    const getBackupTreeSetting = () => {
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
                beforeClick: clickBackupNode,
                onCheck: checkBackupNode,
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
     * 获取MongoDB分片节点
     */
    const getMongoDBShardNodes = (
        clusterAppDetail,
        clusterNodeOfflineAgentUuidList,
        clusterInfo,
        clusterAppUuid,
        clusterAgentUuid,
        mongosList,
        clusterDbBackupInfo,
        clusterDbType,
        clusterDbBackupFlag,
        clusterGroupUuid,
        clusterGroupName,
        clusterType
    ) => {
        /**
         * MongoDB分片集群
         *   shard1
         *    node1
         *   shard2
         *    node1
         *   shard3
         *    node1
         *   config_server
         *    node1
         *   mongos
         *    node1
         */
        let nodes = [];
        // 构建分片节点
        if (Array.isArray(clusterAppDetail.shard_node) && clusterAppDetail.shard_node.length > 0) {
            let shardNodeList = [];
            for (const nodeInfo of clusterAppDetail.shard_node) {
                for (const shardType in nodeInfo) {
                    for (const shardInfo of nodeInfo[shardType]) {
                        shardInfo['shard_type'] = shardType;
                        shardNodeList.push(shardInfo);
                    }
                    // 构建分片类别
                    nodes.push({
                        id: clusterInfo.cluster_uuid + '_shard_' + shardType,
                        pId: clusterInfo.cluster_uuid,
                        name: shardType,
                        title: shardType,
                        eventtype: 'cluster_shard',
                        icon: './img/platform/storage.png',
                        nocheck: true,
                        chkDisabled: false,
                        isParent: true,
                        open: false,
                        instance_name: '',
                        app_uuid: clusterAppUuid,
                        agent_uuid: clusterAgentUuid,
                        agent_ip: '',
                        cluster_flag: true,
                        cluster_uuid: clusterInfo.cluster_uuid,
                        cluster_name: clusterInfo.cluster_name,
                        online_flag: true,
                        net_model: 1,
                        db_backup_info: clusterDbBackupInfo,
                        db_type: clusterDbType,
                        dbBackupFlag: clusterDbBackupFlag,
                        group_uuid: clusterGroupUuid,
                        group_name: clusterGroupName,
                        app_detail: clusterAppDetail,
                    });
                }
            }
            for (const shardNodeInfo of shardNodeList) {
                let name = shardNodeInfo.instance_name + '-' + shardNodeInfo.node_role + '(' + shardNodeInfo.listen_ip + ')';
                let onlineFlag = true;
                if (clusterNodeOfflineAgentUuidList.includes[shardNodeInfo.agent_uuid]) {
                    name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
                    onlineFlag = false;
                }
                nodes.push({
                    id: clusterInfo.cluster_uuid + '_shard_' + shardNodeInfo.shard_type + '_' + shardNodeInfo.instance_name,
                    pId: clusterInfo.cluster_uuid + '_shard_' + shardNodeInfo.shard_type,
                    name,
                    title: name,
                    isParent: false,
                    eventtype: 'cluster_shard_node',
                    icon: './img/vm/host.png',
                    nocheck: true,
                    chkDisabled: false,
                    instance_name: shardNodeInfo.instance_name,
                    app_uuid: clusterAppUuid,
                    agent_uuid: shardNodeInfo.agent_uuid,
                    agent_ip: shardNodeInfo.listen_ip,
                    cluster_flag: true,
                    cluster_uuid: clusterInfo.cluster_uuid,
                    cluster_name: clusterInfo.cluster_name,
                    online_flag: onlineFlag,
                    net_model: 1,
                    db_backup_info: clusterDbBackupInfo,
                    db_type: clusterDbType,
                    dbBackupFlag: clusterDbBackupFlag,
                    group_uuid: clusterGroupUuid,
                    group_name: clusterGroupName,
                    app_detail: clusterAppDetail,
                });
            }
        }
        // 构建config_server
        if (Array.isArray(clusterAppDetail.config_server) && clusterAppDetail.config_server.length > 0) {
            nodes.push({
                id: clusterInfo.cluster_uuid + '_config_server',
                pId: clusterInfo.cluster_uuid,
                name: 'config server',
                title: 'config server',
                eventtype: 'cluster_config_server',
                icon: './img/platform/storage.png',
                nocheck: true,
                chkDisabled: false,
                isParent: true,
                open: false,
                instance_name: '',
                app_uuid: clusterAppUuid,
                agent_uuid: clusterAgentUuid,
                agent_ip: '',
                cluster_flag: true,
                cluster_uuid: clusterInfo.cluster_uuid,
                cluster_name: clusterInfo.cluster_name,
                online_flag: true,
                net_model: 1,
                db_backup_info: clusterDbBackupInfo,
                db_type: clusterDbType,
                dbBackupFlag: clusterDbBackupFlag,
                group_uuid: clusterGroupUuid,
                group_name: clusterGroupName,
                app_detail: clusterAppDetail,
            });
            for (const configSererInfo of clusterAppDetail.config_server) {
                let name = configSererInfo.instance_name + '-' + configSererInfo.node_role + '(' + configSererInfo.listen_ip + ')';
                let onlineFlag = true;
                if (clusterNodeOfflineAgentUuidList.includes[configSererInfo.agent_uuid]) {
                    name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
                    onlineFlag = false;
                }
                nodes.push({
                    id: clusterInfo.cluster_uuid + '_config_server_' + configSererInfo.agent_uuid + '_' + configSererInfo.instance_name,
                    pId: clusterInfo.cluster_uuid + '_config_server',
                    name,
                    title: name,
                    eventtype: 'cluster_config_server_node',
                    icon: './img/vm/host.png',
                    nocheck: true,
                    chkDisabled: false,
                    isParent: false,
                    instance_name: configSererInfo.instance_name,
                    app_uuid: clusterAppUuid,
                    agent_uuid: configSererInfo.agent_uuid,
                    agent_ip: '',
                    cluster_flag: true,
                    cluster_uuid: clusterInfo.cluster_uuid,
                    cluster_name: clusterInfo.cluster_name,
                    online_flag: onlineFlag,
                    net_model: 1,
                    db_backup_info: clusterDbBackupInfo,
                    db_type: clusterDbType,
                    dbBackupFlag: clusterDbBackupFlag,
                    group_uuid: clusterGroupUuid,
                    group_name: clusterGroupName,
                    app_detail: clusterAppDetail,
                });
            }
        }
        // 构建mongos
        nodes.push({
            id: clusterInfo.cluster_uuid + '_mongos',
            pId: clusterInfo.cluster_uuid,
            name: 'mongos',
            title: 'mongos',
            eventtype: 'cluster_config_server',
            icon: './img/platform/storage.png',
            nocheck: true,
            chkDisabled: false,
            isParent: true,
            open: false,
            instance_name: '',
            app_uuid: clusterAppUuid,
            agent_uuid: clusterAgentUuid,
            agent_ip: '',
            cluster_flag: true,
            cluster_uuid: clusterInfo.cluster_uuid,
            cluster_name: clusterInfo.cluster_name,
            online_flag: true,
            net_model: 1,
            db_backup_info: clusterDbBackupInfo,
            db_type: clusterDbType,
            dbBackupFlag: clusterDbBackupFlag,
            group_uuid: clusterGroupUuid,
            group_name: clusterGroupName,
            app_detail: clusterAppDetail,
        });
        for (const mongosInfo of mongosList) {
            let name = mongosInfo.instance_name + '(' + mongosInfo.agent_info.ip + ')';
            let onlineFlag = true;
            if (clusterNodeOfflineAgentUuidList.includes[mongosInfo.agent_uuid]) {
                name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
                onlineFlag = false;
            }
            nodes.push({
                id: clusterInfo.cluster_uuid + '_mongos_' + mongosInfo.agent_info.agent_uuid + '_' + mongosInfo.instance_name,
                pId: clusterInfo.cluster_uuid + '_mongos',
                name,
                title: name,
                eventtype: 'cluster_mongos_node',
                icon: './img/vm/host.png',
                nocheck: true,
                chkDisabled: false,
                isParent: false,
                instance_name: mongosInfo.instance_name,
                app_uuid: clusterAppUuid,
                agent_uuid: mongosInfo.agent_info.agent_uuid,
                agent_ip: '',
                cluster_flag: true,
                cluster_uuid: clusterInfo.cluster_uuid,
                cluster_name: clusterInfo.cluster_name,
                online_flag: onlineFlag,
                net_model: 1,
                db_backup_info: clusterDbBackupInfo,
                db_type: clusterDbType,
                dbBackupFlag: clusterDbBackupFlag,
                group_uuid: clusterGroupUuid,
                group_name: clusterGroupName,
                app_detail: clusterAppDetail,
            });
        }

        return nodes;
    };

    /**
     * 设置备份树
     * @param {*} instanceList
     */
    const setBackupTree = (instanceList) => {
        let dbType = parseInt($('#dbtype').val());
        let standaloneNodes = [];
        let clusterNodes = {};

        for (const instanceInfo of instanceList) {
            if (!instanceInfo.cluster_info.cluster_flag) {
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
            nodes.push({
                id: 'cluster',
                pId: 0,
                name: LANG.UI_DB_CLUSTER,
                title: LANG.UI_DB_CLUSTER,
                open: true,
                nocheck: true,
                eventtype: 'category',
                isParent: true,
                icon: './img/vm/hostcluster.png',
            });
            noInstance = false;
        }
        if (standaloneNodes.length) {
            nodes.push({
                id: 'standalone',
                pId: 0,
                name: LANG.UI_DB_STANDALONE,
                title: LANG.UI_DB_STANDALONE,
                open: true,
                nocheck: true,
                eventtype: 'category',
                isParent: true,
                icon: './img/vm/host.png',
            });
            noInstance = false;
        }
        if (noInstance) {
            $("#noagent").show();
            $(".vcenter-tree").hide();
            $("#agent_tree").hide();
            return;
        }

        let oldDbInfo = SETTINGS.db_info;

        for (const standaloneNode of standaloneNodes) {
            let chkDisabled = false;
            let checked = false;
            let name = standaloneNode.instance_name + '(' + standaloneNode.agent_info.ip + ')';
            let dbBackupFlag = false;
            if (!standaloneNode.agent_info.online_flag) {
                chkDisabled = true;
                name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
            }

            for (const oldDb of oldDbInfo) {
                if (oldDb.cluster_flag) {
                    continue;
                }
                if (oldDb.agentuuid === standaloneNode.agent_info.agent_uuid && oldDb.instancename === standaloneNode.instance_name) {
                    checked = true;
                    break;
                }
            }

            if (Array.isArray(standaloneNode.db_backup_info) && standaloneNode.db_backup_info.length) {
                dbBackupFlag = true;
            }
            // Oracle
            if (dbType === CONF.DB_TYPE.ORACLE) {
                if (parseInt(standaloneNode.app_auth_type) === 1) {  // 操作系统不能作为备份源
                    continue;
                }
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
                checked,
                instance_name: standaloneNode.instance_name,
                app_uuid: standaloneNode.app_uuid,
                agent_uuid: standaloneNode.agent_info.agent_uuid,
                agent_ip: standaloneNode.agent_info.ip,
                agent_info: standaloneNode.agent_info,
                cluster_flag: false,
                cluster_uuid: '',
                cluster_name: '',
                online_flag: standaloneNode.agent_info.online_flag,
                net_model: standaloneNode.agent_info.net_model,
                db_backup_info: standaloneNode.db_backup_info,
                db_type: parseInt(standaloneNode.db_type),
                dbBackupFlag,
                group_uuid: standaloneNode.agent_info.group_uuid,
                group_name: standaloneNode.agent_info.group_name,
                app_detail: standaloneNode.app_detail,
            });
        }
        for (const clusterUuid in clusterNodes) {
            let clusterInfo = clusterNodes[clusterUuid].clsuter_info;
            let clusterOnlineFlag = false;
            let clusterNodeOfflineAgentUuidList = [];
            let clusterChecked = false;
            let clusterDbType = 0;
            let clusterDbBackupFlag = false;
            let clusterDbBackupInfo = [];
            let clusterAgentUuid = '';
            let clusterAgentInfo = '';
            let clusterInstanceName = '';
            let clusterAppUuid = '';
            let clusterGroupUuid = '';
            let clusterGroupName = '';
            let clusterType = parseInt(clusterInfo.cluster_type);
            let clusterAppDetail = {};
            for (const instanceInfo of clusterNodes[clusterUuid].instance_list) {
                let chkDisabled = false;
                let name = instanceInfo.instance_name + '(' + instanceInfo.agent_info.ip + ')';
                switch (dbType) {
                    case CONF.DB_TYPE.TIDB:
                        // TiDB显示<角色名(IP)>
                        name = instanceInfo.app_detail.node_role + '(' + instanceInfo.agent_info.ip + ')';
                        break;
                    case CONF.DB_TYPE.MONGODB:
                        // MongoDB副本集群显示<实例名-节点角色(IP)>
                        if (clusterType == MONGODB_CLUSTER_TYPE_ENUM.repset) {
                            name = instanceInfo.instance_name + '-' + instanceInfo.app_detail.node_role + '(' + instanceInfo.agent_info.ip + ')';
                        }
                        break;

                    default:
                        break;
                }
                let dbBackupFlag = false;
                if (!clusterAgentUuid) {
                    clusterAgentUuid = instanceInfo.agent_info.agent_uuid;
                    clusterAgentInfo = instanceInfo.agent_info;
                    clusterInstanceName = instanceInfo.instance_name;
                    clusterAppUuid = instanceInfo.app_uuid;
                }
                // 离线状态
                if (!instanceInfo.agent_info.online_flag) {  // 离线
                    chkDisabled = true;
                    name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
                    clusterNodeOfflineAgentUuidList.push(instanceInfo.agent_info.agent_uuid);
                } else {  // 需要全部离线才离线
                    clusterOnlineFlag = true;
                    clusterAgentUuid = instanceInfo.agent_info.agent_uuid;
                    clusterAgentInfo = instanceInfo.agent_info;
                    clusterInstanceName = instanceInfo.instance_name;
                    clusterAppUuid = instanceInfo.app_uuid;
                    if (Array.isArray(instanceInfo.db_backup_info) && instanceInfo.db_backup_info.length) {
                        dbBackupFlag = true;
                        clusterDbBackupFlag = true;
                        clusterDbBackupInfo = instanceInfo.db_backup_info;
                    }
                }

                clusterGroupUuid = instanceInfo.agent_info.group_uuid;
                clusterGroupName = instanceInfo.agent_info.group_name;
                clusterDbType = parseInt(instanceInfo.db_type);

                for (const oldDb of oldDbInfo) {
                    if (!oldDb.cluster_flag) {
                        continue;
                    }
                    if (oldDb.agentuuid === instanceInfo.agent_info.agent_uuid && oldDb.instancename === instanceInfo.instance_name) {
                        clusterChecked = true;
                        break;
                    }
                }
                clusterAppDetail = instanceInfo.app_detail;
                // MongoDB分片集群只要一个离线了，那么就不能用于备份
                if (dbType == CONF.DB_TYPE.MONGODB) {
                    if (!instanceInfo.agent_info.online_flag) {
                        clusterOnlineFlag = false;
                    }
                    // MongoDB分片集群节点根据app_detail.shard_node来显示
                    if (clusterType == MONGODB_CLUSTER_TYPE_ENUM.shard) {
                        continue;
                    }
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
                    agent_uuid: instanceInfo.agent_info.agent_uuid,
                    agent_ip: instanceInfo.agent_info.ip,
                    agent_info: instanceInfo.agent_info,
                    cluster_flag: instanceInfo.cluster_info.cluster_flag,
                    cluster_uuid: instanceInfo.cluster_info.cluster_uuid,
                    cluster_name: instanceInfo.cluster_info.cluster_name,
                    online_flag: instanceInfo.agent_info.online_flag,
                    net_model: instanceInfo.agent_info.net_model,
                    db_backup_info: instanceInfo.db_backup_info,
                    db_type: clusterDbType,
                    dbBackupFlag,
                    group_uuid: instanceInfo.agent_info.group_uuid,
                    group_name: instanceInfo.agent_info.group_name,
                    app_detail: instanceInfo.app_detail,
                });
            }

            let clusterChkDisabled = false;
            let clusterName = clusterInfo.cluster_name;
            switch (dbType) {
                case CONF.DB_TYPE.ORACLE:
                    // Oracle显示<集群服务名>或<集群服务名(集群别名)>
                    clusterName = clusterInfo.app_service_name + '(' + clusterInfo.cluster_name + ')';
                    if (!clusterInfo.cluster_name) {
                        clusterName = clusterInfo.app_service_name;
                    }
                    break;
                case CONF.DB_TYPE.MONGODB:
                    // MongoDB显示<集群别名(集群类型)>
                    if (clusterType === MONGODB_CLUSTER_TYPE_ENUM.repset) {
                        for (const instanceInfo of clusterNodes[clusterUuid].instance_list) {
                            if (instanceInfo.agent_info.online_flag) {
                                if (instanceInfo.app_detail.node_role.toLowerCase() == 'arbiter') {  // 仲裁节点不参与备份
                                    continue;
                                }
                                clusterAgentUuid = instanceInfo.agent_info.agent_uuid;
                                clusterInstanceName = instanceInfo.instance_name;
                                clusterAppUuid = instanceInfo.app_uuid;
                            }
                        }
                        clusterName = clusterInfo.cluster_name + '(' + LANG.UI_DB_MONGODB_CLUSTER_TYPE_REPSET + ')';
                    } else {
                        clusterName = clusterInfo.cluster_name + '(' + LANG.UI_DB_MONGODB_CLUSTER_TYPE_SHARD + ')';
                        let mongodbNodes = getMongoDBShardNodes(
                            clusterAppDetail,
                            clusterNodeOfflineAgentUuidList,
                            clusterInfo,
                            clusterAppUuid,
                            clusterAgentUuid,
                            clusterNodes[clusterUuid].instance_list,
                            clusterDbBackupInfo,
                            clusterDbType,
                            clusterDbBackupFlag,
                            clusterGroupUuid,
                            clusterGroupName,
                            clusterType
                        );
                        nodes = nodes.concat(mongodbNodes);
                    }
                    break;
                case CONF.DB_TYPE.TIDB:
                    // TiDB显示<实例名>(集群别名)
                    clusterName = clusterInstanceName + '(' + clusterInfo.cluster_name + ')';
                    break;
                default:
                    break;
            }
            if (!clusterOnlineFlag) {
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
                checked: clusterChecked,
                chkDisabled: clusterChkDisabled,
                cluster_flag: true,
                cluster_uuid: clusterInfo.cluster_uuid,
                cluster_name: clusterInfo.cluster_name,
                cluster_service_ip: clusterInfo.cluster_service_ip,
                agent_uuid: clusterAgentUuid,
                agent_info: clusterAgentInfo,
                app_uuid: clusterAppUuid,
                instance_name: clusterInstanceName,
                instance_list: clusterNodes[clusterUuid].instance_list,
                app_service_name: clusterInfo.app_service_name,
                online_flag: clusterOnlineFlag,
                db_type: clusterDbType,
                dbBackupFlag: clusterDbBackupFlag,
                db_backup_info: clusterDbBackupInfo,
                group_uuid: clusterGroupUuid,
                group_name: clusterGroupName,
            });
        }
        zTreeAgent = $.fn.zTree.init($("#agent_tree"), getBackupTreeSetting(), nodes);

        let checkedNodes = zTreeAgent.getCheckedNodes(true);
        let orderedCheckedNodes = [];  // #23106
        let addObjectMap = {};
        for (const oldDb of oldDbInfo) {
            for (const checkedNode of checkedNodes) {
                if (oldDb.cluster_flag) {
                    if (oldDb.cluster_uuid === checkedNode.cluster_uuid) {
                        if (typeof addObjectMap[oldDb.cluster_uuid] === 'undefined') {
                            addObjectMap[oldDb.cluster_uuid] = true;
                            orderedCheckedNodes.push(checkedNode);
                        }
                    }
                } else {
                    if (oldDb.agentuuid === checkedNode.agent_uuid && oldDb.instancename === checkedNode.instance_name) {
                        if (typeof addObjectMap[oldDb.agentuuid + '_' + oldDb.instancename] === 'undefined') {
                            addObjectMap[oldDb.agentuuid + '_' + oldDb.instancename] = true;
                            orderedCheckedNodes.push(checkedNode);
                        }
                    }
                }
            }
        }
        for (const orderedCheckedNode of orderedCheckedNodes) {
            zTreeAgent.checkNode(orderedCheckedNode, true, true, true);
            // 8. 如果当前任务是完备任务且有归档日志任务，那么不能取消备份源
            // 9. 如果当前任务是归档日志任务，那么不能取消备份源
            if (dbType === CONF.DB_TYPE.ORACLE) {
                if (SETTINGS.agentInfo.multi_task_flag) {  // 当前修改的是子任务
                    zTreeAgent.setChkDisabled(orderedCheckedNode, true);
                } else {
                    for (const dbBackupInfo of orderedCheckedNode.db_backup_info) {
                        if (dbBackupInfo.multi_task_flag) { // 主任务存在子任务
                            zTreeAgent.setChkDisabled(orderedCheckedNode, true);
                            break;
                        }
                    }
                }
            }
        }
    };

    /**
     * 初始化备份树
     */
    const initBackupTree = () => {
        Metronic.blockUI({target: '#tab1 .src-wrap__content__itree', animate: true})
        pAjaxRequest({db_type: parseInt($('#dbtype').val())}, `/api/v1/db/instances`, 'GET', res => {
            Metronic.unblockUI('#tab1 .src-wrap__content__itree');
            if (!res.success || !res.data.rows.length) {
                $('#noagent').show();
                $('.vcenter-tree').hide();
                return;
            }
            $('#noagent').hide();
            $('.vcenter-tree').show();
            setBackupTree(res.data.rows);
        });
    };

    //////////////////// 结束-初始化备份树 ////////////////////

    var inintDatatimePicker = function () {
        if (CONF.LANGUAGE !== "zh-cn" && CONF.LANGUAGE !== "zh-tw") {
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

    //初始化历史任务信息
    var initOldSettings = function () {
        var data = {};
        data.taskuuid = $('#task_uuid').val();
        data = JSON.stringify(data);
        $('#dbtype').prop('disabled', true)
        $.post(CONF.AJAXPATH, {m: CONF.M.DBPROTECT, f: 'getBackupTaskAllInfo', p: data}, function (d) {
            SETTINGS = JSON.parse(d);
            initData();
            initStep1Settings();
            initStep2Settings();
            initStep3Settings();
            initSpinner();
        });
    }

    //初始化第一步任务信息
    var initStep1Settings = function () {
        //设置数据库类型
        $('#dbtype').val(SETTINGS.dbtype).prop('disabled', true);
        dbTypeGlob = parseInt(SETTINGS.dbtype);
        //根据不同的数据库类型显示不同的备份提示
        $('.time-strategy-tips').hide();
        switch (dbTypeGlob) {
            case CONF.DB_TYPE.SQLSERVER:
                $('.db-no-incr-tips').show();
                break;
            case CONF.DB_TYPE.ORACLE:
                $('.db-tips').show();
                break;
            case CONF.DB_TYPE.MARIA:
            case CONF.DB_TYPE.MYSQL:
                $('.db-no-diff-tips').show();
                break;
            case CONF.DB_TYPE.DM:
                $('.db-tips').show();
                break;
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.UXDB:
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.OPENGAUSS:
            case CONF.DB_TYPE.VASTBASE:
                $('.db-log-tips').show();
                break;
            case CONF.DB_TYPE.MONGODB:
                $('.db-all-tips').show();
                break;
            case CONF.DB_TYPE.TIDB:
                $('.db-tidb-tips').show();
                break;
            case CONF.DB_TYPE.SAPHANA:
                $('.db-no-log-tips').show();
                break;
        }

        //禁用数据库类型切换
        $('#dbtype').prop('disabled', true);
        $('#dbtype').selectpicker('refresh');
        //勾选代理树
        initBackupTree();
    }

    //初始化第二步任务信息
    var initStep2Settings = function () {
        // initBackupTarget(callback);
    }

    //初始化第三部任务信息
    var initStep3Settings = function () {
        var timeStrategy = SETTINGS.timestrategy;
        //设置时间策略类型
        $('#backuptype').val(timeStrategy.type);
        if ('oncetime' === timeStrategy.type) {
            //一次性备份,设置时间
            data.backupInfo.type = 'oncetime';
        }

        //限速策略
        $('.speedlimitDes').empty();
        $('#speedList').empty();
        speedList = [];
        speedList = SETTINGS.speedInfo;
        for (let i = 0; i < speedList.length; i++) {
            addSpeedList(speedList[i]);
        }
        initSpeedStrategyDes();
        //传输策略bts
        $('#encrypttransfer').bootstrapSwitch('state', SETTINGS.bts.encrypt);	//加密传输
        // 传输加密算法
        if(SETTINGS.bts.encrypt){
            $('.transfer-encrypt-method-form').show();
        }
        if(SETTINGS.bts.encrypt_method){
            $('#transferEncryptMethod').val(SETTINGS.bts.encrypt_method);
        }
        $('#oracleChannelNum').val(SETTINGS.bts.thread_num);  // 通道数
        $('#mongodbBackupThreadNum').val(SETTINGS.bts.thread_num);  // 传输线程
        if (SETTINGS.bss.encrypt) {  // 数据加密
            //如果不是自动获取密码
            if (!SETTINGS.bss.password_auto_flag) {
                // 修改任务时密码框和确认密码框先不赋值,提示: 修改时填写
                $('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
                $('#repassword').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
            } else {
                // 自动生成密码默认为checked，首次进入页面不会触发change事件，因此手动触发一次从而改变firstInitPageFlag状态
                passwordModeChange();
            }
        }

        //任务名
        $('#jobname').val(SETTINGS.taskname.replace('&lt;', '<').replace('&gt;', '>'));
        $('#tidbFullJobName').val(SETTINGS.taskname.replace('&lt;', '<').replace('&gt;', '>'));
        $('#tidbLogJobName').val(SETTINGS.sub_task_name.replace('&lt;', '<').replace('&gt;', '>'));
        if (!SETTINGS.sub_task_flag) {
            $('#tidbLogJobName').val($('#tidbFullJobName').val() + '_' + LANG.UI_STRATEGY_LOG);
        }
        //任务UUID
        data.taskuuid = SETTINGS.taskuuid;

        //初始化代理配置
        $('#ignoreResourceLimit').bootstrapSwitch('state', SETTINGS.agentInfo.ignore_resource_limiting_flag);
        var dbType = SETTINGS.dbtype;
        switch (parseInt(dbType)) {
            case CONF.DB_TYPE.SQLSERVER:
                //代理配置
                $('#checkdb').bootstrapSwitch('state', SETTINGS.agentInfo.checkdbflag);
                $('#sqlservercompress').bootstrapSwitch('state', SETTINGS.agentInfo.compressflag);
                $('#checksum').bootstrapSwitch('state', SETTINGS.agentInfo.checksumflag);
                break;
            case CONF.DB_TYPE.ORACLE:
                //oracle
                var deleteArchivelog = parseInt(SETTINGS.agentInfo.delete_archive_log_flag);
                $('input[name="deleteArchivelog"]').prop('checked', false)
                    .parent('span').removeClass('checked');
                if (3 === deleteArchivelog) {
                    $('#archivelogDays').val(SETTINGS.agentInfo.last_archive_days).removeAttr('disabled');
                    $('#deleteArchivelogRadio3').prop('checked', true).parent('span').addClass('checked');
                } else if (1 === deleteArchivelog) {
                    $('#deleteArchivelogRadio1').prop('checked', true).parent('span').addClass('checked');
                } else {
                    $('#deleteArchivelogRadio2').prop('checked', true).parent('span').addClass('checked');
                }
                $('#oraclecompress').bootstrapSwitch('state', SETTINGS.agentInfo.compressflag);
                $('#oracleCheckArchivelog').bootstrapSwitch('state', SETTINGS.agentInfo.checkdbflag);
                $('#oracleFilesPerset').bootstrapSwitch('state', SETTINGS.agentInfo.set_filesperset_flag)
                if (SETTINGS.agentInfo.set_filesperset_flag) {  // 是否显示在step1Valid中开启
                    $('#oracleFilesPersetDatafile').val(SETTINGS.agentInfo.datafile_filesperset_num);
                    $('#oracleFilesPersetArchivelog').val(SETTINGS.agentInfo.archivelog_filesperset_num);
                } else {
                    $('#oracleFilesPersetDatafile').val(64);
                    $('#oracleFilesPersetArchivelog').val(64);
                }
                $('#archivelogBackupTimesCheck').bootstrapSwitch('state', SETTINGS.agentInfo.log_backup_times_flag);
                $('#archivelogBackupDaysCheck').bootstrapSwitch('state', SETTINGS.agentInfo.log_backup_days_flag);
                $('#skipInaccessibleFileFlag').bootstrapSwitch('state', SETTINGS.agentInfo.skip_inaccessible_file_flag);
                $('#skipOfflineFileFlag').bootstrapSwitch('state', SETTINGS.agentInfo.skip_offline_file_flag);
                $('#enableBctFlag').bootstrapSwitch('state', SETTINGS.agentInfo.enable_bct_flag);
                $('#setSectionSizeFlag').bootstrapSwitch('state', SETTINGS.agentInfo.set_section_size_flag);
                if (SETTINGS.agentInfo.set_section_size_flag) {
                    $('.sectionSizeSpinner').val(SETTINGS.agentInfo.section_size);
                } else {
                    $('.sectionSizeSpinner').val(1);
                }
                $('#customRmanCmd').val(SETTINGS.agentInfo.custom_rman_cmd);
                break;

            case CONF.DB_TYPE.MYSQL:
            case CONF.DB_TYPE.MARIA:
                $('#mysqlSrcCompressed').bootstrapSwitch('state', SETTINGS.agentInfo.compressflag);
                break;
            case CONF.DB_TYPE.DM:
                //DM
                $('#dmcompress').bootstrapSwitch('state', SETTINGS.agentInfo.compress_level);
                $('#deldmarchivelog').bootstrapSwitch('state', SETTINGS.agentInfo.delete_archive_log_flag);
                $('#dmcompress').bootstrapSwitch('state', SETTINGS.agentInfo.compressflag);
                break;
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.UXDB:
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.OPENGAUSS:
            case CONF.DB_TYPE.VASTBASE:
                $('#delpostgrelog').val(SETTINGS.agentInfo.delete_archive_log_flag);
                $('#srccompress').val(SETTINGS.agentInfo.compressflag);
                $('#archiveAlarmCheck').bootstrapSwitch('state', SETTINGS.agentInfo.warningInfo.warn_check);
                $('select[name=noticetype]').val(SETTINGS.agentInfo.warningInfo.warn_type);
                //判断告警开关是否开启
                if (SETTINGS.agentInfo.warningInfo.warn_check) {
                    $('.warnningdiv').show();
                    if (parseInt(SETTINGS.agentInfo.warningInfo.warn_type) === 1) {
                        //百分比
                        $('#percentdiv').show();
                        $('#sizediv').hide();
                    } else {
                        //按容量告警取值B转换为GB
                        $('#percentdiv').hide();
                        $('#sizediv').show();
                    }
                } else {
                    $('.warnningdiv').hide();
                }
                break;
            case CONF.DB_TYPE.MONGODB:
                $('#parallelNum').val(data.agentInfo.max_object_transport_parallel_nums);
                break;
            case CONF.DB_TYPE.TIDB:
                $('#tidbCompressMethod').val(data.agentInfo.compress_method);
                if (parseInt(data.agentInfo.compress_method) === TIDB_COMPRESS_METHOD_ENUM.zstd) {
                    $('.tidbCompressLevelDiv').show();
                } else {
                    $('.tidbCompressLevelDiv').hide();
                }
                // $('#parallelNum').val(data.agentInfo.max_object_transport_parallel_nums);
                break;
            case CONF.DB_TYPE.SAPHANA:
                $('#sapHanaCompress').bootstrapSwitch('state', data.agentInfo.compressflag);
                $('#autoMultiTaskIntervalSpinner').spinner('value', data.agentInfo.auto_log_backup_interval / 60);
                $('#channelCountSpinner').spinner('value', data.agentInfo.channel_count);
                break;
        }

        // 初始化重试策略
        initRetryStrategy();

        // 初始化安全策略
        initSafeStrategy();

        //数据加密打开
        if (SETTINGS.bss.encrypt) {
            $('.passwordModeDiv').show();
            if (!SETTINGS.bss.password_auto_flag) {
                $('.passwordDiv').show();
            }
        } else {
            $('.passwordDiv').hide();
        }
    }

    var addSpeedList = function (list) {
        var des = "";
        var uuid = list.uuid;
        des +=
            '<li class="list-group-item popovers speedTips list-group-item__speed" id="speed' + uuid + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + list.des + '">' +
            '<div class="col1">' +
            '<div class="cont">' +
            '<div class="cont-col1"></div>' +
            '<div class="cont-col2">' +
            '<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + list.des + '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col2  pull-right delete-list">' +
            '<a class="del' + uuid + '" >' +
            '<div class="label label-sm label-danger" style="padding:0;">' +
            '<i class="viconfont vicon-cuowu"></i>' +
            '</div>' +
            '</a>' +
            '</div>' +
            '</li>';
        $('#speedList').append(des);
        $('.del' + uuid).on('click', function () {
            $('.popover.in').remove();
            $('#speed' + uuid).remove();
            for (var j = 0; j < speedList.length; j++) {
                if (uuid === speedList[j].uuid) {
                    speedList.splice($.inArray(speedList[j], speedList), 1);
                }
            }
            initSpeedStrategyDes();
        });
    }

    /**
     * 初始化节点传输网络列表
     */
    const initNetworkList = nodeUuid => {
        if (oldNode === nodeUuid) {
            return;
        }
        $('#transferNetworkTree').transferNetwork({
            node_uuid: nodeUuid,
            network_uuid: SETTINGS.bts.network,
            network_pool_uuid: SETTINGS.bts.network_pool_uuid
        });
        oldNode = nodeUuid;
    };

    // ---------策略选择---------
    //初始化策略选择列表
    var initStrategySelect = function () {
        if (initStrategyFlag) return; //加载一次
        function initStrategyList(res) {
            if (!res.success) return;
            if (res.data.length > 0) {
                // 清空策略列表，再插入新的策略列表
                var data = res.data;
                var strategyselect = $('#strategySelect');
                strategyselect.empty();
                for (var i = 0; i < data.length; i++) {
                    var option = '<option  value="' + data[i].uuid + '">' + data[i].text + '</option>';
                    globalStrategy[data[i].uuid] = data[i].strategy;
                    strategyselect.append(option);
                }
                if (!initStrategyFlag) {
                    //初始化前先清除一遍
                    $('.searchable-select').remove();
                    $('#strategySelect').searchableSelect();
                    $('.searchable-select-item').on('click', strategyHandler);
                    initStrategyFlag = true;
                }
            }

        }

        pAjaxRequest({type: CONF.MODULE_TYPE.DB}, '/api/v1/strategies/select', "GET", initStrategyList, true);
    }

    /**
     * 初始化设置策略之后的数据
     */
    const setAfterStrategyData = () => {
        let dbType = parseInt($('#dbtype').val());
        switch (dbType) {
            case CONF.DB_TYPE.TIDB:
                if ($('#logBackup').prop('checked')) {  // 勾选了日志备份
                    // 设置时间策略描述
                    let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
                    let des = strategyConfig.fullInfo.des + '.';
                    des += LANG.UI_STRATEGY_LOG + '.';
                    $('.backupTimeDes').html(des);
                }
                break;
            case CONF.DB_TYPE.SQLSERVER:
                if (sqlserverDbSimpleModeFlag === false) {
                    if (!$("#logBackup").prop("checked")) {
                        $('#strategymode').find('input[data-mode=4]').iCheck('check').iCheck('disable');
                        $('#stragegyaccordion').find('.strategy-panel[data-mode=4]').show();
                    }
                } else if (sqlserverDbSimpleModeFlag === true) {
                    if ($("#logBackup").prop("checked")) {
                        UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, LANG.UI_DB_BACKUP_SQLSERVER_SIMPLE_MODE_NONSUPPORT_LOG_BACKUP);
                        $('#strategymode').find('input[data-mode=4]').iCheck('uncheck');
                        $('#stragegyaccordion').find('.strategy-panel[data-mode=4]').hide();
                    }
                }
                break;
            default:
                break;
        }
    };

    /**
     * 初始化全局策略之后的操作
     */
    const afterGlobalInitBackupStrategy = (globalStrategy) => {
        if (typeof globalStrategy.reserve === 'object' && typeof globalStrategy.reserve.reserveInfo !== 'undefined') {
            setAfterInitStep3Strategy();
        } else {  // 表示全局策略没有设置保留策略，因此设置默认为按备份链保留
            let dbType = parseInt($('#dbtype').val());
            if (dbType === CONF.DB_TYPE.MONGODB) {
                $('#reserveMode').val(2).trigger('change');
            }
            setAfterInitStep3Strategy();
        }
    };

    // 选择并初始化全局策略
    var strategyHandler = function () {
        editFlag = false;
        var index = $('#strategySelect option:selected').val();
        var strategy = globalStrategy[index];
        if (typeof strategy !== 'undefined') {
            if (typeof strategy.speedlimit !== 'undefined') {
                if (typeof strategy.speedlimit.speed !== 'undefined') {
                    strategy.speedlimit.speedInfo = strategy.speedlimit.speed;
                }
                if (!strategy.speedlimit.speedInfo) {
                    strategy.speedlimit.speedInfo = [];
                }
            } else {
                strategy.speedlimit = {
                    speedInfo: [],
                };
            }
        }
        if (typeof strategy.time === 'object' && typeof strategy.time.type !== 'undefined') {
            let dbType = parseInt($('#dbtype').val());
            strategy.time.timeInfo.type = strategy.time.type;
            if (dbType === CONF.DB_TYPE.MONGODB) {
                strategy.time.timeInfo = buildMongoDBTimeStrategy(strategy.time.timeInfo);
            }
        }
        $('#tab_common').backupStrategy(CONF.MODULE_TYPE.DB, true, strategy, DBSubType, () => {
            afterGlobalInitBackupStrategy(strategy);
        });
        // 限速策略
        if (index == 0) return false;
        _oldPassword = strategy?.store?.storeInfo?.password ?? '';;
        var speedInfo = strategy.speedlimit.speedInfo;
        var check = strategy.speedlimit.check;
        if (!check || !speedInfo) return;
        speedList = [];
        // 将限速策略放进消息体中
        for (var i = 0; i < speedInfo.length; i++) {
            speedList.push(speedInfo[i]);
        }
    }

    var initTimeCrowd = function () {
        // 更新时间区间
        $.post(CONF.AJAXPATH, {m: CONF.M.JOB, f: "getTimeCrowdList", p: {}}, function (d) {
            var jsonData = JSON.parse(d);
            if (jsonData.timeList.length) {
                $('#backupCrowd').taskCrowd({timeList: jsonData.timeList, showFlag: jsonData.showFlag});
            }
        });
    }

    /**
     * 初始化重试策略
     */
    const initRetryStrategy = () => {
        $('#tab_advanced_retry').retryStrategy({'retry_strategy': SETTINGS.retry_strategy},'edit');
    };

    /**
     * 初始化安全策略
     */
    const initSafeStrategy = () => {
        let safeConfigStrategy = SETTINGS.safe_config_strategy;
        // 完整性校验
        $('#integrityCheck').completeDetectionBackup(
            'col-md-3',
            safeConfigStrategy.integrity_check_flag,
            CONF.MODULE_TYPE.DB,
            true,
            safeConfigStrategy.integrity_check_config.check_strategy,
            safeConfigStrategy.integrity_check_config.full_error_policy,
            safeConfigStrategy.integrity_check_config.inc_error_policy
        );
    };

    return {
        //main function to initiate the module
        init: function () {
            console.log('db-backup-edit');  // 这里打印是为了方便debug
            initOldSettings();
            wizardInit();
            initListener();
            inintDatatimePicker();
            initStrategySelect();
            initTimeCrowd();
        },

    };

}();

jQuery(document).ready(function () {
    DbEditBackup.init();
});
