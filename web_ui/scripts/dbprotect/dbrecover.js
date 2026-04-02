var DBRecover = function () {
    var data = {
        pointInfo: {},
        recoverInfo: {},
        timeInfo: {},
        highInfo: {},
        taskName: '',
        strategygroupuuid: ',',
        detail: {},
        node_uuid: '',
    };
    // 数据库恢复方式枚举
    const PATH_TYPE_ENUM = {
        COVER: 1,               // 原数据库覆盖恢复
        CREATE: 2,              // 新建数据库恢复
        SPECIFY_FOLDER: 3,      // 指定文件夹恢复
        REDIRECT_DIR: 4,        // 重定向目录恢复
        EXPORT: 5,              // 导出恢复
        PDB: 6,                 // PDB恢复
        RESTORE_ARCHIVELOG: 7,  // 还原归档日志恢复
        FULL: 8,                // 完全恢复
        INCOMPLETE: 9,          // 不完全恢复
    };
    // 备份点枚举
    const TIMEPOINT_TYPE_ENUM = {
        FULL: 1,    // 完备
        INCR: 2,    // 增量
        DIFF: 3,    // 差异
        LOG: 4,     // 日志
    };
    // 恢复时间
    const RECOVERY_TIME_FLAG_ENUM = {
        NEWEST: 1,      // 最新点
        TIMEPOINT: 2,   // 备份点
        TIME: 3,        // 时间点
    };
    // oracle恢复时间点
    const ORACLE_RECOVERY_TIMEPOINT_ENUM = {
        NEWEST: 1,      // 最新点
        TIME: 2,        // 时间点
        SCN: 3,         // SCN
    };
    // 恢复源恢复方式
    const RECOVERY_SOURCE_TYPE_ENUM = {
        SPECIFY_TIMEPOINT: 1,  // 指定时间点
        TIMER_NEWEST: 2,       // 定时最新点
    };
    // MySQL数据库启动方式
    const MYSQL_START_TYPE_ENUM = {
        SERVICE: 1,  // 服务启动
        COMMAND: 2,  // 命令行启动
    };
    // TiDB时间点恢复方式
    const TIDB_RECOVERY_TIMEPOINT_ENUM = {
        NEWEST: 1,      // 最新点
        TIME: 2,        // 时间点
    };
    // MongoDB集群类型
    let MONGODB_CLUSTER_TYPE_ENUM = {
        single: 0,
        repset: 1,
        shard: 2,
    };
    let STORAGE_STATUS_ENUM = {
        ONLINE: 1,
        CREATING: 2,
        OFFLINE: 3,
        UNMOUNT: 4,
        WARNING: 5,
    };
    var pointtypetree;
    var hostTree;
    let createNewInstanceHostTree;
    var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;,\\[\\].<>/?！￥…（）【】‘；：”“。，、？+-]");
    var nodeParamList;
    var startTimepoint, endTimepoint;
    var _OSTYPE;
    var initSpeedFlag = false;
    var speedList = [];
    var networkFlag = false; //是否显示传输网络
    // 从备份数据跳转恢复页面
    const externalPointUuid = $('#externalPointUuid').val();
    const externalTaskUuid = $('#externalTaskUuid').val();
    const externalItemUuid = $('#externalItemUuid').val();
    const externalSubType = parseInt($('#externalSubType').val());
    const externalInstanceName = $('#externalInstanceName').val();
    const externalDbName = $('#externalDbName').val();
    const externalAgentUuid = $('#externalAgentUuid').val();
    const externalClusterUuid = $('#externalClusterUuid').val();
    let externalId = '';
    const defaultConfig = {
        store: false,
        reserve: false,
        recovery_time_flag: true,
    };
    /**
     * 多数据库恢复逻辑
     * 1. 非SQL server数据库只能选择一个备份点
     * 2. SQL server数据库实例下的每一个数据库都能选择且一个备份点
     *
     * 数据结构设计
     * 1. 需要区分是哪个类别数据库
     * 2. 需要区分SQL server实例下的数据库
     * {agent_uuid, task_uuid, dbType, timePointList: {任务uuid_客户端uuid_实例名: 备份点, 任务uuid_客户端uuid_实例名_数据库名: 备份点}}
     * @type {Object}
     */
    let recoverySource = {
        recovery_source_type: RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT,
        agent_uuid: '',
        db_type: 0,
        task_uuid: '',
        time_point_list: {},
        table_space: [],  // 表空间
        cluster_uuid: '',  // 需要dbt的db_config存储cluster_uuid，目前支持TIDB、MongoDB
        cluster_flag: false,
        timepoint_encrypt_password_map: {},
        agent_encrypt_password_map: {},
        agent_newest_timepoint_map: {},
    }
    const backupTimePointType = [
        TIMEPOINT_TYPE_ENUM.FULL,  // 完备
        TIMEPOINT_TYPE_ENUM.INCR,  // 增量
        TIMEPOINT_TYPE_ENUM.DIFF,  // 差异
        TIMEPOINT_TYPE_ENUM.LOG,   // 日志
    ];
    /**
     * 恢复目标
     * @type {{agent_uuid: string, instance_uuid: string, cluster_flag: boolean, cluster_uuid: string}}
     */
    let recoveryTarget = {
        agent_uuid: '',
        instance_uuid: '',
        cluster_flag: false,
        cluster_uuid: '',
        create_new_instance_flag: false,
    };
    /**
     * 恢复方式配置，目前仅用于SQL server
     * @type {{path_type: number, db_config: {}}}
     */
    let recoveryMethod = {
        path_type: 0,  // 恢复方式【1原数据库覆盖 2新建数据库 3指定文件夹 4重定向目录 5导出 6PDB 7还原归档日志】
        db_config: {},  // 数据库恢复方式配置
        create_new_instance_config: {  // 创建实例恢复配置
            instance_name: '',
            sys_password: '',
            re_sys_password: '',
            system_user: '',
            config_create_new_instance_flag: false,
            /**
             * @property {Array<String>} spfile内容
             */
            spfile_content: [],
        },
    };
    /**
     * 批量配置
     * @type {{rollback_time: {start_time: string, pick_time: string, end_time: string}, encrypt_password: string, is_rollback: boolean}}
     */
    let batchConfig = {};
    /**
     * Oracle恢复实例类别
     * @var {Number} oracleRecoveryType 【1正常 2空实例 ...】
     */
    let oracleRecoveryType = 1;
    let hideOpenDb = false;
    let oracleRecoveryContent = {
        pfile: '',
        pfile_flag: false,
        listener: '',
        listener_path: '',
        listener_flag: '',
        tnsnames: '',
        tnsnames_path: '',
        tnsnames_flag: '',
        sqlnet: '',
        sqlnet_path: '',
        sqlnet_flag: '',
        password: '',
        password_flag: false,
        password_path: '',
        tree: null,
    };

    /**
     * 恢复时间策略类型
     * @type {Object}
     */
    const TIME_STRATEGY_RECOVERY_TYPE_ENUM = {
        IMMEDIATELY: 1,  // 立即恢复
        STRATEGY: 2,         // 定时恢复
        ONCE_TIME: 4,         // 一次性恢复
    };
    /**
     * SAP HANA备份点列表
     * @type {{}} db_uuid: {timepoint}
     */
    let sapHanaTimepointList = {};
    let _UserPassword = null;
    let storage_type;  // 恢复源存储类型
    let drillStorageType;  // 演练存储类型
    let oldNodeUuid = null;  // 之前的节点uuid，用于初始化节点网络
    let oldNodeUuidForResourceLimit = null;  // 之前的节点uuid，用于初始化资源限制
    /**
     * Oracle备份点列表
     * @type {Object}
     */
    let oracleTimepointList;
    let oracleRecoveryTimeSlider = null;
    let oracleRecoveryTime = null;
    let oracleExportTree = null;
    let oracleRestoreTimeSlider = null;
    let oracleRestoreTimeShakeTimer = null;  // 输入抖动
    let editTaskFlag = false;  // 是否为修改任务
    let oldRecoveryTaskData = null;  // 旧的恢复任务数据
    let tidbTimepointSet;
    let tidbTimepointList;
    let tidbRecoveryTimeSlider = null;
    let tidbRecoveryTimeShakeTimer = null;  // 输入抖动
    const ORACLE_RECOVERY_SOURCE_ENUM = {
        ALL_TASK: 1,  // 所有任务
        SPECIFY_TASK: 2,  // 指定任务
    };
    let oracleRecoveryTaskTree = null;
    let oracleRecoveryChainTree = null;
    let recoverySourceTimepointList = {};

    var initListener = function () {
        // 恢复模式
        $('#recoverySourceTypeSelect').on('change', changeRecoverySourceType).hide();
        // 步骤一搜素
        $('#searchvm').on('propertychange', searchVM).on('input', searchVM);
        //跳转到客户端管理
        $('#toAddAgent1, #toAddAgent2').on('click', function () {
            LOCATION('./content/client/client.php', 'infrastructure');
        });

        //选择恢复方式
        $('#recovertype').on('change', function () {
            let timeStrategyRecoveryType = parseInt(this.value);
            // $('#setstrategy').hide();
            // $('#setOnceTimeDiv').hide();
            if (TIME_STRATEGY_RECOVERY_TYPE_ENUM.IMMEDIATELY === timeStrategyRecoveryType) {
                data.timeInfo.strategy = {};
                // setTimeStrategyDes();
            } else if (TIME_STRATEGY_RECOVERY_TYPE_ENUM.STRATEGY === timeStrategyRecoveryType) {
                // $('#setstrategy').show();
                $('#collapsebody7 .panel-body .timepicker-24').trigger('change');
            } else if (TIME_STRATEGY_RECOVERY_TYPE_ENUM.ONCE_TIME === timeStrategyRecoveryType) {
                // $('#setOnceTimeDiv').show();
                // setTimeStrategyDes();
            }
        });

        $('#tobackup').on('click', function () {
            LOCATION('./content/dbprotect/dbbackup.php', 'db_protect');
        });

        //恢复方式选择, 每个类别的切换都单独处理
        $('#pathType').on('change', function () {
            let pathType = parseInt(this.value);
            recoveryMethod.path_type = pathType;
            switch (recoverySource.db_type) {
                case CONF.DB_TYPE.SQLSERVER:
                    doSqlServerShowStep3Item(pathType);
                    break;
                case CONF.DB_TYPE.ORACLE:
                    doOracleShowStep3Item(pathType);
                    if (
                        (pathType === PATH_TYPE_ENUM.FULL || pathType === PATH_TYPE_ENUM.INCOMPLETE) &&
                        recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                        initOracleTimepointChainByRecoveryTime();
                    }
                    break;
                case CONF.DB_TYPE.MYSQL:
                case CONF.DB_TYPE.MARIA:
                    doMysqlShowStep3Item(pathType);
                    break;
                case CONF.DB_TYPE.DM:
                    doDMShowStep3Item(pathType);
                    break;
                case CONF.DB_TYPE.POSTGRE:
                case CONF.DB_TYPE.KINGBASE:
                case CONF.DB_TYPE.UXDB:
                case CONF.DB_TYPE.HIGHGO:
                case CONF.DB_TYPE.OPENGAUSS:
                case CONF.DB_TYPE.VASTBASE:
                case CONF.DB_TYPE.ANTDB:
                    doPostgresShowStep3Item(pathType);
                    break;
                case CONF.DB_TYPE.MONGODB:
                    doMongoDBShowStep3Item(pathType);
                    break;
                case CONF.DB_TYPE.TIDB:
                    doTiDBShowStep3Item(pathType);
                    break;
                case CONF.DB_TYPE.SAPHANA:
                    doSAPHANAShowStep3Item(pathType);
                    break;
            }
        });

        //清空时间
        $('#resetdate').on('click', function () {
            $('#logtime').val('');
            return false;
        });

        //自定义回归时间
        $('#logtimecheck').on('switchChange.bootstrapSwitch', logtimeChange);

        $('#recoveryThreadNum').on("change", function () {
            var value = this.value;
            if (!value || value < 1) {
                $('#recoveryThreadDiv').spinner("value", 1);
            }
            if (value > 32) {
                $('#recoveryThreadDiv').spinner("value", 32);
            }
        });
        $('#mongodbRecoveryThreadNum').on("change", function () {
            var value = this.value;
            if (!value || value < 1) {
                $('#mongodbRecoveryThreadDiv').spinner("value", 1);
            }
            if (value > 16) {
                $('#mongodbRecoveryThreadDiv').spinner("value", 16);
            }
        });

        //初始化添加限速策略模态框
        $('#addSpeedlimit').on('click', function () {
            $('#speedlimitModal').modal({'width': '800px', 'height': '380px'});
            if (!initSpeedFlag) {
                initSpeedTimeStrategy();
            }

        });
        //切换限速模式
        $('#speedModeType').on('change', speedModeHandler);

        //添加限速策略确定
        // $('#speed_submit').on('click', speedSubmit);
        // 客户端并行数量
        $('#parallelNum').on('change', function () {
            var value = this.value;
            if (!value || value < 1) {
                $('#parallelNumSpinner').spinner("value", 1);
            }
            if (value > 9999999) {
                $('#parallelNumSpinner').spinner("value", 9999999);
            }
        });
        // 批量配置
        $('#batchConfigBtn').on('click', function () {
            $('#batchConfigModal').modal({width: '800px', height: '380px'});
            // 有取消按钮，需要确保每次打开的内容都是相同的
            $('#batchEncryptPassword').val(batchConfig.encrypt_password);
            $('#batchLogTimeCheck').bootstrapSwitch('state', batchConfig.is_rollback);
            $('#batchLogTimeInput').val(batchConfig.rollback_time.pick_time);
        });
        // 批量回滚时间开关
        $('#batchLogTimeCheck').on('switchChange.bootstrapSwitch', batchLogTimeCheck);
        // 清空时间
        $('#batchResetTime').on('click', function () {
            $('#batchLogTimeInput').val('');
        });
        // 点击了批量配置确认按钮
        $('#batchConfigSubmit').on('click', submitBatchConfig);
        // 传输策略---加密传输
        $('#transferEncryptedCheck').on('switchChange.bootstrapSwitch', transferEncryptChange);
        // 归档日志还原方式
        $('#restoreArchivelogType').on('change', restoreArchivelogTypeChange);
        // 配置自定义参数文件
        $('#oracleRecoveryContent').on('click', '#editpfile', function () {
            showOracleCustomConfigFileModal('pfile');
        }).on('click', '#editlistener', function () {
            showOracleCustomConfigFileModal('listener');
        }).on('click', '#edittnsnames', function () {
            showOracleCustomConfigFileModal('tnsnames');
        }).on('click', '#editsqlnet', function () {
            showOracleCustomConfigFileModal('sqlnet');
        }).on('click', '#editpassword', function () {
            showOracleCustomConfigFileModal('password');
        });
        // 配置文件提交
        $('#customConfigFileSubmit').on('click', clickCustomConfigFileSubmit);
        // 显示sys密码的小眼睛按钮
        $('.sys-password-icon').on('click', clickSysPasswordIcon);
        // SAP HANA的恢复时间
        $('#sapHanaRecoveryTime').on('change', saphanaRecoveryTimeFlagChange);
        // MySQL数据库启动方式
        $('#mysqlStartType').on('change', mysqlStartTypeChange);
        // MySQL打开数据库
        $('#mysqlOpenDbCheck').on('switchChange.bootstrapSwitch', mysqlOpenDbChange);
        // Oracle恢复分支
        $('#oracleRecoveryIncarnation').on('change', oracleRecoveryIncarnationChange);
        // Oracle恢复时间点
        $('#oracleRecoveryTimepoint').on('change', oracleRecoveryTimepointTypeChange);
        $('#oracleRecoveryTimeSelectTime').on('change', oracleRecoveryTimeChange);
        // $('#confirmOracleRecoveryTimeSelectTime').on('click', clickOracleRecoveryTimeConfirmBtn);
        // Oracle还原归档日志
        $('#oracleRestoreTimeSelectStartTime, #oracleRestoreTimeSelectEndTime').on('input', oracleRestoreTimeChange).on('change', oracleRestoreTimeChange);
        // 恢复脚本切换
        $('#scriptConfigSwitch').on('switchChange.bootstrapSwitch', scriptConfigChange);
        // TiDB时间点恢复方式
        $('#tidbRecoveryTimepoint').on('change', tidbRecoveryTimepointTypeChange);
        $('#tidbRecoveryBackupSet').on('change', tidbRecoveryBackupSetChange);
        $('#tidbRecoveryTimeSelectTime').on('input', tidbRecoveryTimeChange).on('change', tidbRecoveryTimeChange);
        // 新建实例开关
        $('#createNewInstance').on('switchChange.bootstrapSwitch', createNewInstanceChange);
        // 新建实例配置
        $('#createNewInstanceBtn').on('click', showNewInstanceModal);
        // 加密密码眼睛
        $('.encryptDiv .show-encrypt-password-btn').on('click', showEncryptPassword);
        $('#createNewInstanceTab1 .show-password-btn').on('click', showOracleCreateNewInstanceSysPasswordEyes);
        // Oracle-恢复来源
        $('#oracleRecoverySource').on('change', oracleRecoverySourceChange);
        // Oracle-修改恢复时间按钮
        $('#oracleRecoveryTimeShowBack').on('click', clickOracleRecoveryTimeShowBackBtn);
        // Oracle-恢复时间模态框确定按钮
        $('#oracleRecoveryTimeModal .modal-footer .btn-primary').on('click', clickOracleRecoveryTimeModalSubmit);
        // 导出恢复
        initOracleExportListener();
        initOracleRecoveryBackupTimepointTreeEvent();
    };

    /**
     * 初始化Oracle恢复备份时间点树事件
     */
    const initOracleRecoveryBackupTimepointTreeEvent = () => {
        // 注册加密密码
        $('#oracleRecoveryBackupTimepointTree').off('change').on('change', 'input.oracleEncryptPassword', function () {
            let timepointUuid = $(this).data('uuid');
            let encryptPassword = $(this).val();
            if (!validEncryptPassword(encryptPassword, timepointUuid)) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_DATA_ENCRYPT_PASSWORD_ERROR);
                return false;
            }
        });
        // 注册显示密码
        $('#oracleRecoveryBackupTimepointTree').off('click').on('click', '.show-encrypt-password-btn', function () {
            let $encryptInput = $(this).siblings('input.oracleEncryptPassword');
            if ($encryptInput.attr('type') === 'password') {
                $encryptInput.attr('type', 'text');
                $(this).find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
            } else {
                $encryptInput.attr('type', 'password');
                $(this).find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
            }
        });
    };

    /**
     * 初始化Oracle导出恢复监听
     */
    const initOracleExportListener = () => {
        // 注册加密密码
        $('#oracleExportTree').on('change', 'input.oracleEncryptPassword', function () {
            let timepointUuid = $(this).data('uuid');
            let encryptPassword = $(this).val();
            if (!validEncryptPassword(encryptPassword, timepointUuid)) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_DATA_ENCRYPT_PASSWORD_ERROR);
                return false;
            }
        }).on('click', 'input.oracleEncryptPassword', function (ev) {  // 注册不取消勾选
            ev.stopPropagation();
        }).on('dblclick', 'input.oracleEncryptPassword,.show-encrypt-password-btn', function (ev) {  // 取消双击打开/这个子树功能
            ev.stopPropagation();
        }).on('click', '.show-encrypt-password-btn', function (ev) {  // 注册显示密码
            ev.stopPropagation();
            let $encryptInput = $(this).siblings('input.oracleEncryptPassword');
            if ($encryptInput.attr('type') === 'password') {
                $encryptInput.attr('type', 'text');
                $(this).find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
            } else {
                $encryptInput.attr('type', 'password');
                $(this).find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
            }
        });
    };

    /**
     * 点击了恢复时间模态框确定按钮
     */
    const clickOracleRecoveryTimeModalSubmit = () => {
        oracleRecoveryTimeChange();
        let oracleRangeTimepointList = oracleTimepointList;
        let oracleRecoverySourceType = parseInt($('#oracleRecoverySource').val());
        if (oracleRecoverySourceType === ORACLE_RECOVERY_SOURCE_ENUM.SPECIFY_TASK) {
            oracleRangeTimepointList = oracleTimepointList.select_task_chain;
        }
        let oracleRecoveryTimestamp = convertToUnixTimestamp(oracleRecoveryTime);
        // 判断恢复时间是否处于存储离线区域
        if (!judgeOracleRecoveryTimeNotInNonsupportRange(oracleRecoveryTimestamp, oracleRangeTimepointList)) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_ORACLE_NONSUPPORT_TIME, LANG.UI_DB_RECOVERY_ORACLE_NONSUPPORT_TIME_SLIDER_AVAILABLE_TIME)
            sliderToNextAvailableTime(oracleRangeTimepointList);
            return false;
        }
        // 判断恢复时间是否处于不可恢复区域
        if (!judgeOracleRecoveryTimeNotInNonsupportRange(oracleRecoveryTimestamp, oracleRangeTimepointList, 'nonsupport_time_range')) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_ORACLE_NONSUPPORT_TIME, LANG.UI_DB_RECOVERY_ORACLE_NONSUPPORT_TIME_SLIDER_AVAILABLE_TIME)
            sliderToNextAvailableTime(oracleRangeTimepointList);
            return false;
        }
        $('#oracleRecoveryTimeShowBackText').html(oracleRecoveryTime);
        initOracleTimepointChainByRecoveryTime();
        $('#oracleRecoveryTimeModal').modal('hide');
    };

    /**
     * 点击了修改恢复时间按钮
     */
    const clickOracleRecoveryTimeShowBackBtn = () => {
        $('#oracleRecoveryTimeModal').modal({width: '800px', height: '380px'});
    };

    /**
     * 设置恢复时间策略描述
     */
    const setTimeStrategyDes = () => {
        let timeStrategyRecoveryType = parseInt($('#recovertype').val());
        let des = $('#recovertype option:selected').html();
        if (TIME_STRATEGY_RECOVERY_TYPE_ENUM.IMMEDIATELY === timeStrategyRecoveryType) {
        } else if (TIME_STRATEGY_RECOVERY_TYPE_ENUM.ONCE_TIME === timeStrategyRecoveryType) {
            des += ', ' + LANG.UI_JOB_TIMING_RECOVER_TIME + ': ' + $('#onceTime').val();
        }
        $('.recoveryTimeDes').html(des);
    };

    /**
     * 显示加密密码
     */
    const showEncryptPassword = function () {
        if ($('#encryptPassword').attr('type') === 'password') {
            $('#encryptPassword').attr('type', 'text');
            $(this).find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
        } else {
            $('#encryptPassword').attr('type', 'password');
            $(this).find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
        }
    };

    /**
     * Oracle-恢复来源切换
     */
    const oracleRecoverySourceChange = function () {
        let oracleRecoverySourceType = parseInt($(this).val());
        if (oracleRecoverySourceType === ORACLE_RECOVERY_SOURCE_ENUM.ALL_TASK) {
            $('.oracleRecoverySpecifyTaskDiv').hide();
            initOracleRecoveryTimeRange();
        } else {
            $('.oracleRecoverySpecifyTaskDiv').show();
            initOracleRecoveryTimeRange();
        }
        initOracleTimepointChainByRecoveryTime();
    };

    /**
     * 显示Oracle新建实例sys密码的验证
     */
    const showOracleCreateNewInstanceSysPasswordEyes = function () {
        let inputType = $(this).siblings('input').attr('type');
        if (inputType === 'password') {
            $(this).siblings('input').attr('type', 'text');
            $(this).find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
        } else {
            $(this).siblings('input').attr('type', 'password');
            $(this).find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
        }
    };

    /**
     * 显示新建实例配置模态框
     */
    const showNewInstanceModal = () => {
        let checkedNodes = createNewInstanceHostTree.getCheckedNodes(true);
        $('#createNewInstanceConfigModal')
            .data('instance-name', checkedNodes[0].instance_name)
            .data('oracle-home', checkedNodes[0].oracle_home)
            .data('oracle-base', checkedNodes[0].oracle_base)
            .modal({width: '800px', height: '380px'});
        // 还原到步骤1
        if (!recoveryMethod.create_new_instance_config.config_create_new_instance_flag) {
            $('a[href="#createNewInstanceTab1"]').tab('show'); // 手动切换步骤页面
            createNewInstanceHandlerTitle(null, $('#createNewInstanceConfigModal .modal-body .form-wizard ul.nav'), 0);
        }
        $('#newInstanceSystemUser').val(checkedNodes[0].install_app_username)
    };

    /**
     * 新建实例开关
     */
    const createNewInstanceChange = function () {
        $('.host_tree_div').hide();
        $('.createNewInstanceHostDiv').hide();
        $('.createNewInstanceConfigDiv').hide();
        if (this.checked) {
            $('.createNewInstanceHostDiv').show();
            if (createNewInstanceHostTree) {
                let checkedNodes = createNewInstanceHostTree.getCheckedNodes(true);
                if (checkedNodes.length) {
                    $('.createNewInstanceConfigDiv').show();
                }
            }
        } else {
            $('.host_tree_div').show();
        }
    };

    /**
     * TiDB恢复时间变化
     */
    const tidbRecoveryTimeChange = () => {
        if (tidbRecoveryTimeShakeTimer !== null) {
            clearTimeout(tidbRecoveryTimeShakeTimer);
            tidbRecoveryTimeShakeTimer = null;
        }
        let selectTimestamp = convertToUnixTimestamp($('#tidbRecoveryTimeSelectTime').val());
        if (isNaN(selectTimestamp)) {
            return;
        }
        tidbRecoveryTimeShakeTimer = setTimeout(() => {
            let selectTimestamp = convertToUnixTimestamp($('#tidbRecoveryTimeSelectTime').val());
            if (selectTimestamp < tidbTimepointList.start_time) {
                tidbRecoveryTimeSlider.set([tidbTimepointList.start_time]);
                let startDatetime = getCurrentDatetimeStr(new Date(tidbTimepointList.start_time * 1000));
                $('#tidbRecoveryTimeSelectTime').val(startDatetime);
            } else if (selectTimestamp > tidbTimepointList.end_time) {
                tidbRecoveryTimeSlider.set([tidbTimepointList.end_time]);
                let endDatetime = getCurrentDatetimeStr(new Date(tidbTimepointList.end_time * 1000));
                $('#tidbRecoveryTimeSelectTime').val(endDatetime);
            } else {
                tidbRecoveryTimeSlider.set([selectTimestamp]);
                let selectDatetime = getCurrentDatetimeStr(new Date(selectTimestamp * 1000));
                $('#tidbRecoveryTimeSelectTime').val(selectDatetime);
            }
            doTiDBShowStep3Item(parseInt($('#pathType').val()));
        }, 1200);
    };

    /**
     * TiDB备份集改变了
     */
    const tidbRecoveryBackupSetChange = () => {
        initTiDBTimepointSet();
        doTiDBShowStep3Item(parseInt($('#pathType').val()));
    };

    /**
     * TiDB时间点恢复方式改变了
     */
    const tidbRecoveryTimepointTypeChange = function () {
        let tidbRecoveryTimepointFlag = parseInt($('#tidbRecoveryTimepoint').val());
        if (tidbRecoveryTimepointFlag === TIDB_RECOVERY_TIMEPOINT_ENUM.NEWEST) {
            $('.tidbRecoveryBackupSetDiv').hide();
            $('.tidbRecoveryTimeSelectDiv').hide();
        } else {
            $('.tidbRecoveryBackupSetDiv').show();
            let singlePointFlag = !!$('#tidbRecoveryBackupSet option:selected').data('single');
            if (!singlePointFlag) {
                $('.tidbRecoveryTimeSelectDiv').show();
            } else {
                $('.tidbRecoveryTimeSelectDiv').hide();
            }
        }
        doTiDBShowStep3Item(parseInt($('#pathType').val()));
    };

    /**
     * 恢复脚本配置切换
     */
    const scriptConfigChange = function () {
        if (this.checked) {
            $('.scriptConfigScriptDiv').show();
        } else {
            $('.scriptConfigScriptDiv').hide();
        }
    };

    /**
     * Oracle还原归档时间变化
     */
    const oracleRestoreTimeChange = () => {
        if (oracleRestoreTimeShakeTimer !== null) {
            clearTimeout(oracleRestoreTimeShakeTimer);
            oracleRestoreTimeShakeTimer = null;
        }
        oracleRestoreTimeShakeTimer = setTimeout(() => {
            let rangeData = oracleRestoreTimeSlider.get(true);
            let startTimestamp = convertToUnixTimestamp($('#oracleRestoreTimeSelectStartTime').val());
            let endTimestamp = convertToUnixTimestamp($('#oracleRestoreTimeSelectEndTime').val());
            if (isNaN(startTimestamp)) {
                startTimestamp = rangeData[0];
            }
            if (isNaN(endTimestamp)) {
                endTimestamp = rangeData[1];
            }
            if (startTimestamp < oracleTimepointList.start_time || startTimestamp > oracleTimepointList.end_time) {
                startTimestamp = rangeData[0];
            }
            if (endTimestamp < oracleTimepointList.start_time || endTimestamp > oracleTimepointList.end_time) {
                endTimestamp = rangeData[1];
            }
            oracleRestoreTimeSlider.set([startTimestamp, endTimestamp]);

            // 更新自身设置
            let startTime = getCurrentDatetimeStr(new Date(startTimestamp * 1000));
            let endTime = getCurrentDatetimeStr(new Date(endTimestamp * 1000));

            $('#oracleRestoreTimeSelectStartTime').val(startTime);
            $('#oracleRestoreTimeSelectEndTime').val(endTime);
        }, 1200);
    };

    /**
     * Oracle恢复时间变化了
     */
    const oracleRecoveryTimeChange = () => {
        let selectTimestamp = convertToUnixTimestamp($('#oracleRecoveryTimeSelectTime').val());
        if (isNaN(selectTimestamp)) {
            return;
        }
        if (selectTimestamp < oracleTimepointList.start_time) {
            oracleRecoveryTimeSlider.set([oracleTimepointList.start_time]);
            let startDatetime = getCurrentDatetimeStr(new Date(oracleTimepointList.start_time * 1000));
            $('#oracleRecoveryTimeSelectTime').val(startDatetime);
        } else if (selectTimestamp > oracleTimepointList.end_time) {
            oracleRecoveryTimeSlider.set([oracleTimepointList.end_time]);
            let endDatetime = getCurrentDatetimeStr(new Date(oracleTimepointList.end_time * 1000));
            $('#oracleRecoveryTimeSelectTime').val(endDatetime);
        } else {
            oracleRecoveryTimeSlider.set([selectTimestamp]);
            let selectDatetime = getCurrentDatetimeStr(new Date(selectTimestamp * 1000));
            $('#oracleRecoveryTimeSelectTime').val(selectDatetime);
        }
        oracleRecoveryTime = $('#oracleRecoveryTimeSelectTime').val();
    };

    /**
     * Oracle恢复时间点变化
     */
    const oracleRecoveryTimepointTypeChange = function () {
        let oracleRecoveryTimepointFlag = parseInt($('#oracleRecoveryTimepoint').val());
        if (oracleRecoveryTimepointFlag === ORACLE_RECOVERY_TIMEPOINT_ENUM.NEWEST) {
            $('.oracleRecoveryTimeShowBackDiv').hide();
            initOracleTimepointChainByRecoveryTime();
        } else if (oracleRecoveryTimepointFlag === ORACLE_RECOVERY_TIMEPOINT_ENUM.TIME) {
            $('.oracleRecoveryTimeShowBackDiv').show();
            $('.oracleRecoverySourceDiv').show();
            $('#oracleRecoverySource').trigger('change');
        } else {
            $('.oracleRecoveryTimeShowBackDiv').hide();
        }
    };

    /**
     * Oracle恢复分支变化
     */
    const oracleRecoveryIncarnationChange = () => {
        let resetlogsTime = parseInt($('#oracleRecoveryIncarnation').val());
        let dbIncarnation = parseInt($('#oracleRecoveryIncarnation option:selected').data('db-incarnation'));
        if (
            resetlogsTime === oracleTimepointList.resetlogs_time &&
            dbIncarnation === oracleTimepointList.db_incarnation
        ) {
            return;
        }

        oracleTimepointList.resetlogs_time = resetlogsTime;
        oracleTimepointList.db_incarnation = dbIncarnation;
        initOracleTimepointSetByResetlogsTime(resetlogsTime, dbIncarnation);
        let pathType = parseInt($('#pathType').val());
        switch (pathType) {
            case PATH_TYPE_ENUM.RESTORE_ARCHIVELOG:
                initRestoreArchivelog();
                break;
            case PATH_TYPE_ENUM.INCOMPLETE:
                initOracleRecoverySource();
                $('#oracleRecoveryTimepoint').trigger('change');
                break;
            default:
                break;
        }
    };

    /**
     * MySQL数据库打开数据库
     */
    const mysqlOpenDbChange = function () {
        if (this.checked) {
            $('.mysqlStartTypeDiv').show();
            $('#mysqlStartType').trigger('change');
        } else {
            $('.mysqlStartTypeDiv').hide();
            $('.startcommanddiv').hide();
            $('.mysqlCustomStartDiv').hide();
            $('.mysqlCustomStopDiv').hide();
        }
    };

    /**
     * MySQL数据库启动方式
     */
    const mysqlStartTypeChange = function () {
        let mysqlStartType = parseInt($('#mysqlStartType').val());
        if (mysqlStartType === MYSQL_START_TYPE_ENUM.SERVICE) {
            $('.startcommanddiv').show();
            $('.mysqlCustomStartDiv').hide();
            $('.mysqlCustomStopDiv').hide();
        } else {
            $('.startcommanddiv').hide();
            $('.mysqlCustomStartDiv').show();
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                $('.mysqlCustomStopDiv').show();
            } else {
                $('.mysqlCustomStopDiv').hide();
            }
        }
    };

    /**
     * SAP HANA的恢复时间变化了
     */
    const saphanaRecoveryTimeFlagChange = function () {
        let sapHanaRecoveryTimeFlag = parseInt(this.value);
        if (RECOVERY_TIME_FLAG_ENUM.NEWEST === sapHanaRecoveryTimeFlag) {
            $('#sapHanaRecoveryTimeTips1').show();
            $('#sapHanaRecoveryTimeTips2').hide();
            $('#sapHanaRecoveryTimeTips3').hide();
        } else if (RECOVERY_TIME_FLAG_ENUM.TIMEPOINT === sapHanaRecoveryTimeFlag) {
            $('#sapHanaRecoveryTimeTips1').hide();
            $('#sapHanaRecoveryTimeTips2').show();
            $('#sapHanaRecoveryTimeTips3').hide();
        } else {
            $('#sapHanaRecoveryTimeTips1').hide();
            $('#sapHanaRecoveryTimeTips2').hide();
            $('#sapHanaRecoveryTimeTips3').show();
        }
        doSAPHANAShowStep3Item(parseInt($('#pathType').val()));
    };

    /**
     * 恢复方式变化（恢复最新点、指定时间点恢复）
     */
    const changeRecoverySourceType = () => {
        $('#timepointStorageSelect option').removeAttr('selected');
        $('#pointDbTypeSelect option').removeAttr('selected');
        $('#VMGroupList').html('');
        $('#searchvm').val('');
        hideStep1Tips();
        recoverySource.time_point_list = {};  // 切换节点，设置为空
        recoverySource.timepoint_encrypt_password_map = {};
        recoverySource.recovery_source_type = parseInt($('#recoverySourceTypeSelect').val());
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {  // 按时间恢复
            $('#timepointStorageSelect').show();
            $('#pointDbTypeSelect').show();
            $("#dbTypeSelect").hide();
            $('#tab1 .src-wrap__content__itree').css('height', 'calc(100% - 136px)');
            initClusterInstancePointTree();
            $('#tab1 .addTitle span').html(LANG.UI_DB_RECOVERY_SELECT_POINT_TITLE);
        } else {  // 恢复最新时间点
            $('#timepointStorageSelect').hide();
            $('#pointDbTypeSelect').hide();
            $("#dbTypeSelect").show();
            $('#tab1 .src-wrap__content__itree').css('height', 'calc(100% - 92px)');
            initSourceAgentTree();
            $('#tab1 .addTitle span').html(LANG.UI_DB_RECOVERY_SELECT_AGENT_TITLE);
        }
    };

    /**
     * sys密码小眼睛
     */
    const clickSysPasswordIcon = function () {
        $('.sys-password-icon').hide();
        if ($(this).hasClass('fa-eye')) {
            $('.sys-password-icon.fa-eye-slash').show();
            $('#sysPassword').attr('type', 'password');
        } else {
            $('.sys-password-icon.fa-eye').show();
            $('#sysPassword').attr('type', 'text');
        }
    };

    const clickCustomConfigFileSubmit = () => {
        let modalTag = $('#customConfigFileModal');
        let eventtype = modalTag.data('eventtype');
        let path = $.fn.PathTreeSelector.getCheckPath('custom-config-modal__path');
        let content = $('#custom-config-modal__content').val();
        let treeNode = oracleRecoveryContent.tree.getNodeByParam('eventtype', eventtype);
        let name = ``;
        switch (eventtype) {
            case 'pfile':
                if (!content.trim()) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_SPFILE_NOT_EMPTY);
                    return;
                }
                oracleRecoveryContent.pfile = content;
                name = treeNode.config_name;
                break;
            case 'listener':
                if (!path.trim()) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_LISTENER_PATH_NOT_EMPTY);
                    return;
                }
                if (!content.trim()) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_LISTENER_NOT_EMPTY);
                    return;
                }
                oracleRecoveryContent.listener_path = path;
                oracleRecoveryContent.listener = content;
                name = treeNode.config_name + '(' + path + ')';
                treeNode.dir_path = path;
                break;
            case 'tnsnames':
                if (!path.trim()) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_TNSNAMES_PATH_NOT_EMPTY);
                    return;
                }
                if (!content.trim()) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_TNSNAMES_NOT_EMPTY);
                    return;
                }
                oracleRecoveryContent.tnsnames_path = path;
                oracleRecoveryContent.tnsnames = content;
                name = treeNode.config_name + '(' + path + ')';
                treeNode.dir_path = path;
                break;
            case 'sqlnet':
                if (!path.trim()) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_SQLNET_PATH_NOT_EMPTY);
                    return;
                }
                if (!content.trim()) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_SQLNET_NOT_EMPTY);
                    return;
                }
                oracleRecoveryContent.sqlnet_path = path;
                oracleRecoveryContent.sqlnet = content;
                name = treeNode.config_name + '(' + path + ')';
                treeNode.dir_path = path;
                break;
            case 'password':
                let oracleTimepoint = oracleTimepointList.select_timepoint;
                if (oracleRecoveryType === 2 && parseInt(oracleTimepoint.db_config.rac_flag) !== 0) {
                    let password = $('#sysPassword').val();
                    if (!judgeSysPassword(password)) {
                        return;
                    }
                    oracleRecoveryContent.password = password;
                    oracleRecoveryContent.password_path = '';
                    name = treeNode.config_name;
                    treeNode.dir_path = '';
                } else {
                    if (!path.trim()) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_PASSWORD_PATH_NOT_EMPTY);
                        return;
                    }
                    oracleRecoveryContent.password_path = path;
                    name = treeNode.config_name + '(' + path + ')';
                    treeNode.dir_path = path;
                }
                break;
        }
        treeNode.name = `<span class="edit-config-file" id="edit${eventtype}">
                            <i class="fa fa-pencil-square-o"></i>
                            <span class="text">${name}</span>
                        </span>`;
        oracleRecoveryContent.tree.updateNode(treeNode);
        modalTag.modal('hide');
    };

    const convertSpaceCharToSign = (text) => {
        return text.split('\n')
            .map(line => line.replace(/ /g, ' ').replace(/ /g, '&nbsp;'))
            .join('<br>');
    }

    const restoreArchivelogTypeChange = function () {
        let type = parseInt($(this).val());
        if (1 === type) {
            $('.oracleRestoreTimeSelectDiv').show();
            $('.restoreArchivelogSCNDiv').hide();
        } else {
            $('.oracleRestoreTimeSelectDiv').hide();
            $('.restoreArchivelogSCNDiv').show();
        }
    }

    // 显示加密算法
    var transferEncryptChange = function () {
        if (this.checked) {
            $('.transfer-encrypt-method-form').show();
        } else {
            $('.transfer-encrypt-method-form').hide();
        }
    }

    var batchLogTimeCheck = function () {
        if (this.checked) {
            $('.batchLogTimeInputDiv').show();
            if (!$('#batchLogTimeInput').hasClass('isLoadRollbackTime')) {
                $('#batchLogTimeInput').addClass('isLoadRollbackTime');
                if (recoverySource.db_type === CONF.DB_TYPE.SAPHANA) {
                    $('#batchLogTimeTips').html('');
                    $('#batchLogTimeInput').val(getCurrentDatetimeStr());
                    return;
                }
                Metronic.blockUI({target: '.batchLogTimeInputDiv', animate: true});
                let firstNode = Object.values(recoverySource.time_point_list)[0];
                let info = {
                    agentuuid: firstNode.agent_uuid,
                    dbuuid: firstNode.db_uuid,
                    type: firstNode.type,
                    timepointuuid: firstNode.timepoint_uuid
                };
                $.post(CONF.AJAXPATH, {
                    m: CONF.M.DBPROTECT,
                    f: 'getDBOldConifg',
                    p: JSON.stringify(info)
                }, function (backData) {
                    Metronic.unblockUI('.batchLogTimeInputDiv');
                    /**
                     * @type {{starttimepoint: String, endtimepoint: String}}
                     */
                    let jsonData = JSON.parse(backData);
                    let des = LANG.UI_DB_RECOVERY_LOG_ROLL_TIME_RANGE + ": " + jsonData.starttimepoint + " ~ " + jsonData.endtimepoint;
                    $('#batchLogTimeTips').html(des);
                    $('#batchLogTimeInput').val(jsonData.endtimepoint);
                    batchConfig.rollback_time.start_time = jsonData.starttimepoint;
                    batchConfig.rollback_time.end_time = jsonData.endtimepoint;
                });
            }
        } else {
            $('.batchLogTimeInputDiv').hide();
        }
    }

    var submitBatchConfig = function () {
        batchConfig.encrypt_password = $('#batchEncryptPassword').val();
        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.SAPHANA:
                // SAP HANA不需要验证加密密码，因为各个数据库备份点的加密密码可能不同
                batchConfig.is_rollback = !!$('#batchLogTimeCheck').get(0).checked
                // 验证回滚时间
                if (batchConfig.is_rollback) {
                    let rollbackTime = $('#batchLogTimeInput').val();
                    let reg = /^(?:19|20)[0-9][0-9]-(?:(0[1-9])|(1[0-2]))-(?:([0-2][1-9])|([1-3][0-1])) (?:([0-2][0-3])|([0-1][0-9])):[0-5][0-9]:[0-5][0-9]$/;
                    if (!reg.test(rollbackTime)) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_ROLL_TIME_ERROR, LANG.UI_DB_RECOVERY_ROLL_TIME_ERROR_TIPS);
                        batchConfig.is_rollback = false;
                        return;
                    }
                    batchConfig.rollback_time.pick_time = rollbackTime;
                }
                break;
        }

        $('#batchConfigModal').modal('hide');
        // 批量设置表格数据
        for (const nodeKey in recoveryMethod.db_config) {
            if (batchConfig.encrypt_password) {
                $('#db-config-tbody input.db_encrypted_password').val(batchConfig.encrypt_password);
                recoveryMethod.db_config[nodeKey].encrypt_password = batchConfig.encrypt_password;
            }
            recoveryMethod.db_config[nodeKey].is_rollback = batchConfig.is_rollback;
            recoveryMethod.db_config[nodeKey].rollback_time = deepCloneObject(batchConfig.rollback_time);
        }

        if (batchConfig.is_rollback) {
            $('#db-config-tbody tr.rollbackTimeTr').addClass('isLoadRollback');
            $('#db-config-tbody input.rollbackTime').val(batchConfig.rollback_time.pick_time);
            if (recoverySource.db_type !== CONF.DB_TYPE.SAPHANA) {
                $('#db-config-tbody span.help-block').html($('#batchLogTimeTips').html());
            } else {
                $('#db-config-tbody span.help-block').html('');
            }
        }
        // 开关得放在添加isLoadRollback这里，因为如果没有添加这个class，会去加载备份时间点的
        $('#db-config-tbody input.rollbackSwitch').bootstrapSwitch('state', batchConfig.is_rollback);
    }

    /**
     * 判断是否显示回滚时间
     * @return {boolean}
     */
    var judgeShowRollbackTime = function () {
        if (recoverySource.db_type === CONF.DB_TYPE.SAPHANA) {
            return false;
        }
        if (
            recoverySource.db_type === CONF.DB_TYPE.ORACLE &&
            recoveryMethod.path_type === PATH_TYPE_ENUM.RESTORE_ARCHIVELOG
        ) {
            $('.logdateDiv').hide();
            $('.logdateInputDiv').hide();
            return false;
        }
        let timePointNode = data.pointInfo.points[0];
        if (recoverySource.db_type === CONF.DB_TYPE.MONGODB) {
            if (!recoverySource.cluster_flag) {  // mongodb单机不需要日志回滚
                return false;
            }
            if (timePointNode.log_start_time_point && timePointNode.log_end_time_point) {
                return true;
            }
        } else if (timePointNode.time_point_type === TIMEPOINT_TYPE_ENUM.LOG) {
            return true;
        }
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {  // 恢复最新点不需要日志回滚
            return false;
        }
        $('.logdateDiv').hide();
        $('.logdateInputDiv').hide();
        return false;
    }

    /**
     * 显示步骤三的回滚时间
     */
    var showStep3RollbackTime = function () {
        if (judgeShowRollbackTime()) {  // 日志备份点要显示回滚时间
            $('.logdateDiv').show();
            if ($('#logtimecheck').get(0).checked) {
                $('.logdateInputDiv').show();
            } else {
                $('.logdateInputDiv').hide();
            }
        }
    }

    /**
     * 根据时间获取Oracle的备份点
     * @returns
     */
    const getOracleRecoveryTimepointByTime = () => {
        oracleTimepointList.latest_timepoint_uuid = '';
        /**
         * 找点逻辑：
         * 1、最新备份链
         * 2、最新备份点
         */
        if (!oracleTimepointList.recovery_timepoint_chain_data.length) {
            return null;
        }
        let newestTimepointChain = oracleTimepointList.recovery_timepoint_chain_data[oracleTimepointList.recovery_timepoint_chain_data.length - 1];
        let newestTimepoint = newestTimepointChain.full_timepoint_info;
        if (newestTimepointChain.depend_timepoint_list.length) {
            let tmpNewestTimepoint = newestTimepointChain.depend_timepoint_list[newestTimepointChain.depend_timepoint_list.length - 1];
            if (tmpNewestTimepoint.src_end_time_point > newestTimepoint.src_end_time_point) {  // 匹配最晚时间
                newestTimepoint = tmpNewestTimepoint;
            }
        }
        return newestTimepoint;
    };

    /**
     * 根据备份点初始化完整性策略
     * @param timepoint
     */
    const initCompleteCheckStrategyByTimepoint = timepoint => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {  // 演练任务不需要
            return;
        }
        $('#integrityCheck').completeStrategyCovery(CONF.MODULE_TYPE.DB, 0, !timepoint.integrity_check_flag);
    };

    /**
     * 显示oracle数据库在步骤3<恢复方式>切换后需要显示的配置项
     * @param {int} pathType 恢复方式
     */
    var doOracleShowStep3Item = function (pathType) {
        $('.timer-recovery-newest-tips').hide();
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            hideOpenDb = PATH_TYPE_ENUM.RESTORE_ARCHIVELOG === pathType || PATH_TYPE_ENUM.EXPORT === pathType
                || (!recoverySource.cluster_flag && recoveryTarget.cluster_flag);  // 单机恢复到RAC不显示打开数据库
        } else {
            hideOpenDb = false;  // 定时恢复最新点有打开数据库
            if (recoveryMethod.path_type === PATH_TYPE_ENUM.INCOMPLETE) {
                $('.timer-recovery-newest-tips').show();
            }
        }
        // 第二步点击下一步显示PFile开关
        if (!hideOpenDb) {
            $('.openDbDiv').show();
            $('#oracleOpenDbTips').show();
            $('#oracleOpenDbFullTips').hide();
            if (pathType === PATH_TYPE_ENUM.FULL) {
                $('#oracleOpenDbTips').hide();
                $('#oracleOpenDbFullTips').show();
            }
            $('#pgOpenDbTips').hide();
        } else {
            $('.openDbDiv').hide();
        }
        $('.exportDiv').hide();
        //隐藏回滚时间
        $('.logdateDiv').hide();
        $('.logdateInputDiv').hide();
        $('.oracleRecoveryDataPathDiv').hide();   // 隐藏恢复数据路径
        $('.oracleRecoveryTimeShowBackDiv').hide();  // 恢复时间
        $('.oracleExportTreeDiv').hide();  // 隐藏导出备份点树
        $('.oracleRestoreTimeSelectDiv').hide();  // 隐藏还原归档日志的时间选择
        $('.oracleRecoveryIncarnationDiv').hide();  // 隐藏恢复分支
        $('.oracleRecoverySourceDiv').hide();  // 隐藏恢复来源
        $('.oracleRecoverySpecifyTaskDiv').hide();  // 隐藏指定任务
        $('.oracleTimepointStorageOfflineDiv').hide();  // 隐藏存储离线提示信息
        $('.oracleTimepointRangeNonsupportDiv').hide();  // 隐藏不可恢复信息
        $('.oracleRecoveryBackupTimepointDiv').hide();  // 隐藏恢复备份点
        $('#oracleRecoveryBackupTimepointNoTimepointTips').hide();
        if (CONF.BD_STORAGE_TYPE.TAPE !== storage_type && drillStorageType !== CONF.BD_STORAGE_TYPE.TAPE) {
            $('.threadDiv').show();
        } else {  // 磁带没有传输线程数
            $('.threadDiv').hide();
        }
        $('.restoreArchivelogDiv').hide();
        $('.restoreArchivelogTypeDiv').hide();
        $('.restoreArchivelogSCNDiv').hide();

        showStep3RollbackTime();
        if (pathType === PATH_TYPE_ENUM.EXPORT) {
            //导出目录恢复
            $('.exportDiv').show();
            $('.threadDiv').hide();
            $('.oracleRecoveryContentDiv').hide();
            $('.oracleRecoveryTimepointDiv').hide();  // 隐藏恢复时间点
            $('.oracleExportTreeDiv').show();  // 显示导出备份点树
            // 加密密码
            let checkNodes = oracleExportTree.getCheckedNodes(true);
            let showEncrypted = false;
            if (checkNodes.length) {
                for (const checkNode of checkNodes) {
                    if (checkNode.eventtype === 'timepoint') {
                        if (checkNode.timepoint.is_encrypted && !checkNode.timepoint.config.password_auto_flag) {
                            showEncrypted = true;
                            break;
                        }
                    }
                }
            }
            if (showEncrypted) {
                $('.encryptDiv').show();
            } else {
                $('.encryptDiv').hide();
            }
            initOracleNetworkList();
            initOracleResourceLimit();
        } else if (PATH_TYPE_ENUM.RESTORE_ARCHIVELOG === pathType) {
            $('.restoreArchivelogDiv').show();
            $('.restoreArchivelogTypeDiv').show();
            $('#restoreArchivelogType').trigger('change');
            $('.oracleRecoveryContentDiv').hide();
            $('.oracleRecoveryTimepointDiv').hide();  // 隐藏恢复时间点
            $('.encryptDiv').hide();  // 隐藏数据加密密码
            $('.oracleRecoveryIncarnationDiv').show();
            initOracleNetworkList();
            initOracleResourceLimit();
        } else if (PATH_TYPE_ENUM.COVER === pathType) {
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                $('.oracleRecoveryContentDiv').show();
                $('.oracleRecoveryTimepointDiv').hide();  // 隐藏恢复时间点
            }
        } else if (PATH_TYPE_ENUM.FULL === pathType) {
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                $('.oracleRecoveryContentDiv').show();
                $('.oracleRecoveryTimepointDiv').hide();  // 隐藏恢复时间点
                // $('.oracleRecoveryDataPathDiv').show();
                oracleTimepointList.select_timepoint = getOracleRecoveryTimepointByTime();
                if (!oracleTimepointList.select_timepoint) {
                    return;
                }
                $('.encryptDiv').hide();  // 指定时间恢复的加密密码特殊
                // 显示恢复备份点
                $('#oracleRecoveryBackupTimepointTree').show();
                $('.oracleRecoveryBackupTimepointDiv').show();
                initCompleteCheckStrategyByTimepoint(oracleTimepointList.select_timepoint);
                initOracleNetworkList();
                initOracleResourceLimit();
                initOracleRecoveryContentTree();
            }
        } else if (PATH_TYPE_ENUM.INCOMPLETE === pathType) {
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                $('.oracleRecoveryIncarnationDiv').show();
                $('.oracleRecoveryContentDiv').show();
                $('.oracleRecoveryTimepointDiv').show();  // 显示恢复时间点
                $('.oracleRecoveryDataPathDiv').show();
                oracleTimepointList.select_timepoint = getOracleRecoveryTimepointByTime();
                if (!oracleTimepointList.select_timepoint) {
                    return;
                }
                let oracleRecoveryTimepointFlag = parseInt($('#oracleRecoveryTimepoint').val());
                if (oracleRecoveryTimepointFlag === ORACLE_RECOVERY_TIMEPOINT_ENUM.TIME) {
                    $('.oracleRecoverySourceDiv').show();  // 显示恢复来源
                    let oracleRecoverySourceType = parseInt($('#oracleRecoverySource').val());
                    if (oracleRecoverySourceType === ORACLE_RECOVERY_SOURCE_ENUM.SPECIFY_TASK) {
                        $('.oracleRecoverySpecifyTaskDiv').show();  // 显示指定任务
                    }
                    $('.oracleRecoveryTimeShowBackDiv').show();  // 恢复时间
                }
                $('.encryptDiv').hide();  // 指定时间恢复的加密密码特殊
                // 显示恢复备份点
                $('#oracleRecoveryBackupTimepointTree').show();
                $('.oracleRecoveryBackupTimepointDiv').show();
                initCompleteCheckStrategyByTimepoint(oracleTimepointList.select_timepoint);
                initOracleNetworkList();
                initOracleResourceLimit();
                initOracleRecoveryContentTree();
            } else {
                $('.oracleRecoveryTimepointDiv').show();
                $('.oracleRecoveryDataPathDiv').show();
                $('#oracleRecoveryTimepoint').val(ORACLE_RECOVERY_TIMEPOINT_ENUM.NEWEST).prop('disabled', 'disabled');
            }
        }
    }

    /**
     * 显示mysql数据库在步骤3<恢复方式>切换后需要显示的配置项
     * @param {int} pathType 恢复方式
     */
    var doMysqlShowStep3Item = function (pathType) {
        $('.timer-recovery-newest-tips').hide();
        $('#logfileDiv').hide();  // 隐藏重定向文件夹
        $('.mysqlStartTypeDiv').hide();  // 隐藏数据库服务名
        $('.startcommanddiv').hide();  // 隐藏数据库服务名
        $('.mysqlCustomStartDiv').hide();  // 隐藏数据库服务名
        $('.mysqlCustomStopDiv').hide();  // 隐藏数据库服务名
        $('.mysqlOpenDbDiv').hide();  // 打开数据库
        showStep3RollbackTime();

        if (RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT === recoverySource.recovery_source_type) {
        } else {
            $('.mysqlStartTypeDiv').show();  // 恢复最新备份点需要数据库服务名
            let mysqlStartType = parseInt($('#mysqlStartType').val());
            if (mysqlStartType === MYSQL_START_TYPE_ENUM.SERVICE) {
                $('.startcommanddiv').show();
            } else {
                $('.mysqlCustomStartDiv').show();
                $('.mysqlCustomStopDiv').show();
            }
        }

        if (pathType === PATH_TYPE_ENUM.COVER) { // 原数据库覆盖恢复
            if (judgeShowRollbackTime()) {
                $('.mysqlStartTypeDiv').show();  // 显示数据库服务名
                let mysqlStartType = parseInt($('#mysqlStartType').val());
                if (mysqlStartType === MYSQL_START_TYPE_ENUM.SERVICE) {
                    $('.startcommanddiv').show();
                } else {
                    $('.mysqlCustomStartDiv').show();
                    $('.mysqlCustomStopDiv').show();
                    if (RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT === recoverySource.recovery_source_type) {
                        $('.mysqlCustomStopDiv').hide();
                    }
                }
            }
            // 打开数据库,完备增量支持
            if (
                data.pointInfo.points[0].time_point_type === TIMEPOINT_TYPE_ENUM.FULL ||
                data.pointInfo.points[0].time_point_type === TIMEPOINT_TYPE_ENUM.INCR
            ) {
                $('.mysqlOpenDbDiv').show();
                $('#mysqlOpenDbCheck').trigger('switchChange.bootstrapSwitch');
            }
        } else if (pathType === PATH_TYPE_ENUM.REDIRECT_DIR) {  // 重定向目录恢复
            $('#logfileDiv').show();  // 显示重定向目录
        }
    }

    /**
     * 显示sql server数据库在步骤3<恢复方式>切换后需要显示的配置项
     * @param {int} pathType 恢复方式
     */
    var doSqlServerShowStep3Item = function (pathType) {
        $('.logdateDiv').hide();  // 隐藏公用的回滚时间
        $('.encryptDiv').hide();  // 隐藏公用的数据加密密码
        $('.multiDbConfig').show();

        recoveryMethod.path_type = pathType;
        recoveryMethod.db_config = {};
        for (const nodeKey in recoverySource.time_point_list) { // 初始化恢复方式
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                if (recoverySource.time_point_list[nodeKey].eventtype !== 'db') {
                    continue;
                }
            }
            recoveryMethod.db_config[nodeKey] = {
                name: recoverySource.time_point_list[nodeKey].db_name,
                new_db_name: '',
                db_uuid: recoverySource.time_point_list[nodeKey].db_uuid,
                timepoint_uuid: recoverySource.time_point_list[nodeKey].timepoint_uuid,
                db_datafile_path: '',
                db_logfile_path: '',
                encrypt_password: recoverySource.timepoint_encrypt_password_map[recoverySource.time_point_list[nodeKey].timepoint_uuid],
                is_rollback: false,
                rollback_time: {
                    start_time: '',
                    end_time: '',
                    pick_time: '',
                },
                initialize_log_area: false,
            };
        }

        $('.multiDbConfigHead').hide();
        $('.multiDbConfigOldDb').show();
        $('.timer-recovery-newest-tips').hide();  // 隐藏错误提示
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            $('.multiDbConfigEncrypt').show();
            $('.multiDbConfigOther').show();
        } else {  // 恢复最新时间点没有加密密码和其他配置
            $('.multiDbConfigEncrypt').hide();
            $('.multiDbConfigOther').show();
            if (recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) {
                $('.timer-recovery-newest-tips').show();
            }
        }
        if (pathType === PATH_TYPE_ENUM.COVER) {
            $('.multiDbConfigTableDiv').removeClass('col-md-8').addClass('col-md-6');
        } else {
            $('.multiDbConfigNewDb').show();  // 新数据库
            $('.multiDbConfigDatafile').show();
            $('.multiDbConfigLogfile').show();
            $('.multiDbConfigTableDiv').removeClass('col-md-6').addClass('col-md-8');
        }
        setMultiDbConfigAccordion();
    };

    /**
     * 设置SQL server多数据库恢复的折叠面板
     */
    const getSqlServerMultiConfigAccordion = () => {
        let html = ``;
        let expandFlag = true;
        for (const nodeKey in recoverySource.time_point_list) {
            let selectedNode = recoverySource.time_point_list[nodeKey];
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                if (selectedNode.eventtype !== 'db') {
                    continue;
                }
            }
            let rollbackHtml = ``;
            let newDbNameHtml = ``;
            let newDataFilePathHtml = ``;
            let newLogFilePathHtml = ``;

            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                // 回滚时间
                if (parseInt(selectedNode.backup_mode) === TIMEPOINT_TYPE_ENUM.LOG) {
                    rollbackHtml = `
                    <!-- 回滚时间开关 -->
                    <div class="form-group">
                        <label class="control-label col-md-3">${$('.logtimelabel').html()}</label>
                        <div class="col-md-9">
                            <input type="checkbox" id="rollback_switch_${nodeKey}" class="make-switch rollbackSwitch" data-on-color="primary"
                                data-off-color="info" data-size="small" />
                            <span class="help-block">${$('.logdateDiv span.help-block').html()}</span>
                        </div>
                    </div>
                    <!-- 回滚时间 -->
                    <div class="form-group display-none" id="select_rollback_${nodeKey}">
                        <label class="control-label col-md-3">${$('#logTimeDiv label.control-label').html()}</label>
                        <div class="col-md-9">
                            <div class="input-group date form_datetime" id="rollback_time_picker_${nodeKey}">
                                <input type="text" id="rollback_time_${nodeKey}" size="20" class="form-control rollbackTime">
                                <span class="input-group-btn">
                                    <button class="btn default" type="button" id="reset_rollback_${nodeKey}">
                                        <i class="fa fa-times"></i>
                                    </button>
                                    <button class="btn default date-set" type="button">
                                        <i class="viconfont vicon-ge_calendar"></i>
                                    </button>
                                </span>
                            </div>
                            <span class="help-block"></span>
                        </div>
                    </div>
                    `;
                }
            }
            if (recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) { // 新建恢复
                // 数据库名称
                let newDbName = clearString(selectedNode.db_name + '_' + selectedNode.time_point, true);
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                    newDbName = selectedNode.db_name + '_scheduled_recovery';
                }
                recoveryMethod.db_config[nodeKey].new_db_name = newDbName;
                newDbNameHtml = `
                <div class="form-group">
                    <label class="control-label col-md-3">${LANG.UI_DB_NEW_DB_NAME}</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control new_db_config" data-db-uuid="new_db_name_${selectedNode.db_uuid}" id="new_db_${nodeKey}" value="${newDbName}" />
                    </div>
                </div>
                `;
                // 数据文件路径
                newDataFilePathHtml = `
                <div class="form-group">
                    <label class="control-label col-md-3">${LANG.UI_DB_RECOVERY_DATA_FILE_PATH}</label>
                    <div class="col-md-9">
                        <div id="datafile_${nodeKey}" data-db-uuid="datafile_${selectedNode.db_uuid}" class="datafile_config"></div>
                    </div>
                </div>
                `;
                // 日志文件路径
                newLogFilePathHtml = `
                <div class="form-group">
                    <label class="control-label col-md-3">${LANG.UI_DB_RECOVERY_LOG_FILE_PATH}</label>
                    <div class="col-md-9">
                        <div id="logfile_${nodeKey}" data-db-uuid="logfile_${selectedNode.db_uuid}" class="logfile_config"></div>
                    </div>
                </div>
                `;
            } else {  // 覆盖恢复
                if (!rollbackHtml.length) {  // 没有回滚时间
                    continue;
                }
            }
            html += `
            <div class="multi-db-config-item">
                <div class="accordion sqlServerMultiDbConfig" id="sqlServerAccordion_${nodeKey}">
                    <div class="panel panel-default strategy-panel">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled popovers ${expandFlag ? '' : 'collapsed'}" data-container="body" data-trigger="hover"
                                    data-placement="top" data-toggle="collapse" data-parent=".sqlServerMultiDbConfig"
                                    href="#sqlServerMultiDbConfig_${nodeKey}" aria-expanded="${expandFlag ? 'true' : 'false'}">
                                    <i class="viconfont vicon-shili1 font-green-seagreen"></i>
                                    <span class="font-gree-seagreen">${selectedNode.db_name}</span>
                                    <span class="multiDbConfigDes"></span>
                                </a>
                            </h4>
                        </div>
                        <div id="sqlServerMultiDbConfig_${nodeKey}" class="panel-collapse collapse ${expandFlag ? 'in' : ''}" aria-expanded="${expandFlag ? 'true' : 'false'}">
                            <div class="panel-body">
                                ${newDbNameHtml}
                                ${newDataFilePathHtml}
                                ${newLogFilePathHtml}
                                ${rollbackHtml}
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
            expandFlag = false;
        }
        return html;
    };

    /**
     * 设置SAP HANA多数据库恢复的折叠面板
     */
    const getSAPHANAMultiConfigAccordion = () => {
        let html = ``;
        let sapHanaRecoveryTimeFlag = parseInt($('#sapHanaRecoveryTime').val());
        let expandFlag = true;
        for (const nodeKey in recoveryMethod.db_config) {
            let selectedNode = recoveryMethod.db_config[nodeKey];
            let newDbNameHtml = ``;
            let recoveryTimeHtml = ``;
            let timepointHtml = ``;
            let initLogAreaHtml = ``;
            let encryptHtml = ``;
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {  // 指定备份点恢复
                let nodeName = LANG.UI_DB_RECOVERY_SAPHANA_NODE + selectedNode.timepoint.storage_info.node_ip;
                let timepointName = `${selectedNode.timepoint.time_point} - ${nodeName}`;
                let storageOfflineClass = '';
                // 存储离线
                if (parseInt(selectedNode.timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                    timepointName += '(' + LANG.UI_PUBLIC_STORAGE_OFFLINE + ')';
                    storageOfflineClass = 'storageOffline';
                }
                if (recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) {
                    // 新数据库名
                    let newDbName = clearString(selectedNode.name + '_' + selectedNode.timepoint.time_point, true);
                    recoveryMethod.db_config[nodeKey].new_db_name = newDbName;
                    newDbNameHtml = `
                    <div class="form-group">
                        <label class="control-label col-md-3">${LANG.UI_DB_NEW_DB_NAME}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control new_db_config popovers" data-container="body" data-trigger="hover"
                                data-placement="top" data-html="true" data-content="${newDbName}" id="new_db_${nodeKey}" value="${newDbName}" />
                        </div>
                    </div>
                    `;
                }

                // 不同的备份点恢复方式有不同的配置
                if (sapHanaRecoveryTimeFlag === RECOVERY_TIME_FLAG_ENUM.NEWEST) {  // 恢复最新时间
                    // 备份点
                    timepointHtml = `
                    <div class="form-group">
                        <label class="control-label col-md-3">${LANG.UI_PUBLIC_TIMEPOINT}</label>
                        <div class="col-md-9">
                            <span title="${timepointName}" class="sapHanaTimepointText ${storageOfflineClass}" id="timepoint_text_${nodeKey}">${timepointName}</span>
                        </div>
                    </div>
                    `;
                } else if (sapHanaRecoveryTimeFlag === RECOVERY_TIME_FLAG_ENUM.TIMEPOINT) {  // 指定完备点
                    // 备份点
                    let timepointOption = ``;
                    for (const timepoint of selectedNode.timepoint_list) {
                        let selected = timepoint.time_point === selectedNode.timepoint.time_point;
                        let nodeName = LANG.UI_DB_RECOVERY_SAPHANA_NODE + timepoint.storage_info.node_ip;
                        let showName = `${timepoint.time_point} - ${nodeName}`;
                        let disabled = '';
                        // 存储离线
                        if (parseInt(timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                            disabled = 'disabled';
                            showName += '(' + LANG.UI_PUBLIC_STORAGE_OFFLINE + ')';
                        }
                        timepointOption += `<option value="${timepoint.time_point_uuid}" ${selected} ${disabled}>${showName}</option>`
                    }
                    timepointHtml = `
                    <div class="form-group">
                        <label class="control-label col-md-3">${LANG.UI_PUBLIC_TIMEPOINT}</label>
                        <div class="col-md-9">
                            <select class="form-control select2me sapHanaSelectTimepoint" id="select_timepoint_${nodeKey}">${timepointOption}</select>
                        </div>
                    </div>
                    `;
                } else {  // 指定时间恢复
                    // 恢复时间
                    recoveryTimeHtml = `
                    <div class="form-group">
                        <label class="control-label col-md-3">${LANG.UI_DB_RECOVERY_RECOVERY_TIME}</label>
                        <div class="col-md-9">
                            <div class="input-group date form_datetime" id="recovery_time_picker_${nodeKey}">
                                <input type="text" id="recovery_time_${nodeKey}" size="20" class="form-control rollbackTime">
                                <span class="input-group-btn">
                                    <button class="btn default" type="button" id="reset_recovery_${nodeKey}">
                                        <i class="fa fa-times"></i>
                                    </button>
                                    <button class="btn default date-set" type="button">
                                        <i class="viconfont vicon-ge_calendar"></i>
                                    </button>
                                </span>
                            </div>
                            <span class="help-block" id="recovery_time_tips_${nodeKey}"></span>
                        </div>
                    </div>
                    `;
                    // 备份点
                    timepointHtml = `
                    <div class="form-group">
                        <label class="control-label col-md-3">${LANG.UI_PUBLIC_TIMEPOINT}</label>
                        <div class="col-md-9">
                            <span title="${timepointName}" class="sapHanaTimepointText ${storageOfflineClass}" id="timepoint_text_${nodeKey}">${timepointName}</span>
                        </div>
                    </div>
                    `;
                }
                // 初始化日志
                initLogAreaHtml = `
                <div class="form-group">
                    <label class="control-label col-md-3">${LANG.UI_DB_RECOVERY_SAP_HANA_INIT_LOG_AREA}</label>
                    <div class="col-md-9">
                        <input type="checkbox" id="init_log_area_${nodeKey}" class="make-switch db_init_log_area" data-on-color="primary"
                            data-off-color="info" data-size="small">
                        <span class="help-block">${LANG.UI_DB_RECOVERY_SAP_HANA_INIT_LOG_AREA_TIPS1}</span>
                    </div>
                </div>
                `;
                // 加密密码
                if (selectedNode.timepoint.is_encrypted && !selectedNode.timepoint.config.password_auto_flag) {
                    encryptHtml = `
                    <div class="form-group">
                        <label class="control-label col-md-3">${$('.passwordlabel').html()}</label>
                        <div class="col-md-9">
                            <input type="password" class="form-control sap_hana_db_encrypted_password" id="encrypted_password_${nodeKey}"/>
                            <button type="button" class="btn btn-link show-encrypt-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                                <i class="viconfont vicon-a-lujing8232"></i>
                            </button>
                        </div>
                    </div>
                    `;
                }
            } else {  // 定时恢复最新备份点
                if (recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) {
                    let newDbName = selectedNode.name + '_scheduled_recovery';
                    // 新数据库名
                    recoveryMethod.db_config[nodeKey].new_db_name = newDbName;
                    newDbNameHtml = `
                    <div class="form-group">
                        <label class="control-label col-md-3">${LANG.UI_DB_NEW_DB_NAME}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control new_db_config popovers" data-container="body" data-trigger="hover"
                                data-placement="top" data-html="true" data-db-uuid="new_db_name_${selectedNode.db_uuid}"
                                data-content="${newDbName}" id="new_db_${nodeKey}" value="${newDbName}" />
                        </div>
                    </div>
                    `;
                }
                // 初始化日志
                initLogAreaHtml = `
                <div class="form-group">
                    <label class="control-label col-md-3">${LANG.UI_DB_RECOVERY_SAP_HANA_INIT_LOG_AREA}</label>
                    <div class="col-md-9">
                        <input type="checkbox" id="init_log_area_${nodeKey}" class="make-switch db_init_log_area" data-on-color="primary"
                            data-off-color="info" data-size="small">
                        <span class="help-block">${LANG.UI_DB_RECOVERY_SAP_HANA_INIT_LOG_AREA_TIPS1}</span>
                    </div>
                </div>
                `;
            }
            html += `
            <div class="multi-db-config-item">
                <div class="accordion sapHanaMultiDbConfig" id="sapHanaAccordion_${nodeKey}">
                    <div class="panel panel-default strategy-panel">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled popovers ${expandFlag ? '' : 'collapsed'}" data-container="body" data-trigger="hover"
                                    data-placement="top" data-toggle="collapse" data-parent=".sapHanaMultiDbConfig"
                                    href="#sapHanaMultiDbConfig_${nodeKey}" aria-expanded="${expandFlag ? 'true' : 'false'}">
                                    <i class="viconfont vicon-shili1 font-green-seagreen"></i>
                                    <span class="font-gree-seagreen">${selectedNode.name}</span>
                                    <span class="multiDbConfigDes"></span>
                                </a>
                            </h4>
                        </div>
                        <div id="sapHanaMultiDbConfig_${nodeKey}" class="panel-collapse collapse ${expandFlag ? 'in' : ''}" aria-expanded="${expandFlag ? 'true' : 'false'}">
                            <div class="panel-body">
                                ${newDbNameHtml}
                                ${recoveryTimeHtml}
                                ${timepointHtml}
                                ${initLogAreaHtml}
                                <div id="encrypted_password_td_${nodeKey}">${encryptHtml}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
            expandFlag = false;
        }
        return html;
    };

    /**
     * 设置多数据库恢复的折叠面板
     */
    const setMultiDbConfigAccordion = () => {
        let html = ``;
        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.SQLSERVER:
                html += getSqlServerMultiConfigAccordion();
                break;
            case CONF.DB_TYPE.SAPHANA:
                html += getSAPHANAMultiConfigAccordion();
                break;
        }
        $('#multiDbConfigWrapper').html(html);
        if (!html.length) {  // 没有任何配置就隐藏
            $('.multiDbConfig').hide();
        } else {
            $('.multiDbConfig').show();
            registerMultiConfigListener();
        }
    };

    /**
     * 验证SQL Server数据文件
     * @param datafile
     * @param id
     * @returns {boolean}
     */
    const validateSqlServerDatafilePath = (datafile, id) => {
        let nodeKey = id.replace(/^datafile_/, '');
        recoveryMethod.db_config[nodeKey].db_datafile_path = datafile;
        let msg = LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT_TIPS;
        if (CONF.DB_TYPE.CACHE === recoverySource.db_type || CONF.DB_TYPE.IRIS === recoverySource.db_type) {
            msg = LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT_TIPS2;
        }
        if (!datafile) {
            UIToastr.showInfo(LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT, msg);
            return false;
        }
        if (!checkPathWhiteSpace(datafile)) {
            return false;
        }
        if (!checkPath(datafile, _OSTYPE)) {
            UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, msg);
            return false;
        }
        return true;
    };

    /**
     * 验证SQL Server日志文件
     * @param logfile
     * @param id
     * @returns {boolean}
     */
    const validateSqlServerLogfilePath = (logfile, id) => {
        let nodeKey = id.replace(/^logfile_/, '');
        recoveryMethod.db_config[nodeKey].db_logfile_path = logfile;
        if (!logfile) {
            UIToastr.showInfo(LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT, LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT_TIPS);
            return false;
        }
        if (!checkPathWhiteSpace(logfile)) {
            return false;
        }
        if (!checkPath(logfile, _OSTYPE)) {
            UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR_TIPS);
            return false;
        }
        return true;
    };

    /**
     * 注册SQL Server多数据库恢复的事件监听
     */
    const registerSqlServerMultiConfigListener = () => {
        // 注册数据文件、日志文件路径选择器
        let allDatafileTag = $('.datafile_config');
        for (const datafileTag of allDatafileTag) {
            let datafileSelector = new $.fn.PathTreeSelector.cls(recoveryTarget.agent_uuid, {
                onChange: validateSqlServerDatafilePath,
                onSubmit: validateSqlServerDatafilePath,
            });
            let selectedPath = $(datafileTag).attr('data-value');
            let selectedDisabled = $(allDatafileTag).attr('data-disabled') === 'disabled';
            datafileSelector.init({
                target_id: $(datafileTag).attr('id'),
                select_mode: 2,
                selected_path: selectedPath,
                disabled: selectedDisabled,
                select_only: selectedDisabled,
            });
        }
        let allLogfileTag = $('.logfile_config');
        for (const logfileTag of allLogfileTag) {
            let logfileSelector = new $.fn.PathTreeSelector.cls(recoveryTarget.agent_uuid, {
                onChange: validateSqlServerLogfilePath,
                onSubmit: validateSqlServerLogfilePath,
            });
            logfileSelector.init({
                target_id: $(logfileTag).attr('id'),
                select_mode: 2,
                select_only: false,
            });
        }
        // 注册数据库新建事件
        $('.new_db_config').unbind('change').on('change', function () {
            let nodeKey = $(this).attr('id').replace(/^new_db_/, '');
            recoveryMethod.db_config[nodeKey].new_db_name = $(this).val();
            if (!recoveryMethod.db_config[nodeKey].new_db_name) {
                UIToastr.showInfo(LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT, LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT_TIPS);
            }
            // 输入了相同名称的数据库, 提示
            let newDbRepeat = {};
            for (const key in recoveryMethod.db_config) {
                let dbConfig = recoveryMethod.db_config[key];
                if (!dbConfig.new_db_name) {
                    continue;
                }
                if (newDbRepeat[dbConfig.new_db_name] !== undefined) {
                    UIToastr.showInfo(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_CREATE_SAME_NEW_DB);
                }
                newDbRepeat[dbConfig.new_db_name] = 1;
            }
        });
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {  // 指定点恢复
            // 注册回滚时间开关
            $('#multiDbConfigWrapper .sqlServerMultiDbConfig .panel-collapse input.make-switch.rollbackSwitch').bootstrapSwitch()
                .on('switchChange.bootstrapSwitch', function () {
                    let nodeKey = $(this).attr('id').replace(/^rollback_switch_/, '');
                    let selectRollback = $('#select_rollback_' + nodeKey);
                    recoveryMethod.db_config[nodeKey].is_rollback = this.checked;
                    if (this.checked) {
                        selectRollback.show();
                    } else {
                        selectRollback.hide();
                    }
                });

            // 初始化时间选取
            for (const nodeKey in recoveryMethod.db_config) {
                let selectedNode = recoverySource.time_point_list[nodeKey];
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                    if (selectedNode.eventtype !== 'db') {
                        continue;
                    }
                }
                if (parseInt(selectedNode.backup_mode) !== TIMEPOINT_TYPE_ENUM.LOG) {
                    continue;
                }
                initDatetimePicker('#select_rollback_' + nodeKey + ' .form_datetime', '#rollback_time_' + nodeKey, function () {
                    let reg = /^(?:19|20)[0-9][0-9]-(?:(0[1-9])|(1[0-2]))-(?:([0-2][1-9])|([1-3][0-1])) (?:([0-2][0-3])|([0-1][0-9])):[0-5][0-9]:[0-5][0-9]$/;
                    let rollbackTime = recoveryMethod.db_config[nodeKey].rollback_time;
                    let pickTime = $('#rollback_time_' + nodeKey).val();
                    recoveryMethod.db_config[nodeKey].rollback_time.pick_time = pickTime;
                    if (!pickTime) {
                        return;
                    }
                    if (pickTime < rollbackTime.start_time || pickTime > rollbackTime.end_time || !reg.test(pickTime)) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_ROLL_TIME_ERROR, LANG.UI_DB_RECOVERY_ROLL_TIME_ERROR_TIPS);
                    }
                });
                $('#reset_rollback_' + nodeKey).unbind('click').on('click', () => {
                    $('#rollback_time_' + nodeKey).val('');
                });

                // 设置时间范围
                let logStartTimepointStr = getCurrentDatetimeStr(new Date(selectedNode.log_start_time_point * 1000));
                let logEndTimepointStr = getCurrentDatetimeStr(new Date(selectedNode.log_end_time_point * 1000));
                let des = LANG.UI_DB_RECOVERY_LOG_ROLL_TIME_RANGE + ": " + logStartTimepointStr + " ~ " + logEndTimepointStr;
                $('#select_rollback_' + nodeKey + ' span.help-block').html(des);
                $('#rollback_time_' + nodeKey).val(logEndTimepointStr);
                recoveryMethod.db_config[nodeKey].rollback_time.start_time = logStartTimepointStr;
                recoveryMethod.db_config[nodeKey].rollback_time.end_time = logEndTimepointStr;
                // 限定组件范围
                $(`#rollback_time_picker_${nodeKey}`).data('daterangepicker').setStartDate(logEndTimepointStr);  // 这里设置最新时间
                $(`#rollback_time_picker_${nodeKey}`).data('daterangepicker').setEndDate(logEndTimepointStr);  // 这里设置最新时间
                $(`#rollback_time_picker_${nodeKey}`).data('daterangepicker').minDate = moment(logStartTimepointStr);
                $(`#rollback_time_picker_${nodeKey}`).data('daterangepicker').maxDate = moment(logEndTimepointStr);
                $(`#rollback_time_${nodeKey}`).val(logEndTimepointStr);
            }
        }
    };

    /**
     * 注册SAP HANA加密密码
     */
    const registerSAPHANAEncryptPassword = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {  // 指定点恢复
            // 注册数据加密事件
            $('.multi-db-config-item').unbind('change').unbind('input').on('input', '.sap_hana_db_encrypted_password', function () {
                $(this).val($(this).val().replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g, ''));
            }).on('change', '.sap_hana_db_encrypted_password', function () {
                let nodeKey = $(this).attr('id').replace(/^encrypted_password_/, '');
                let selectedNode = recoveryMethod.db_config[nodeKey];
                recoveryMethod.db_config[nodeKey].encrypt_password = $(this).val();
                if (!validEncryptPassword(recoveryMethod.db_config[nodeKey].encrypt_password, selectedNode.timepoint.time_point_uuid)) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_DATA_ENCRYPT_PASSWORD_ERROR);
                    return;
                }
            });
            $('.multi-db-config-item').unbind('click').on('click', '.show-encrypt-password-btn', function () {
                let $encryptInput = $(this).siblings('input.sap_hana_db_encrypted_password');
                if ($encryptInput.attr('type') === 'password') {
                    $encryptInput.attr('type', 'text');
                    $(this).find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
                } else {
                    $encryptInput.attr('type', 'password');
                    $(this).find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
                }
            })
        }
    };

    /**
     * 注册SAP HANA初始化日志
     */
    const registerSAPHANAInitLogArea = () => {
        $('.db_init_log_area').bootstrapSwitch();
        $('#db-config-tbody .popovers').popover();
        let sameHostRecoveryFlag = true;  // 源主机恢复
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            sameHostRecoveryFlag = false;
        }
        if (recoverySource.cluster_flag != recoveryTarget.cluster_flag) {
            sameHostRecoveryFlag = false;
        } else if (recoverySource.cluster_flag && recoverySource.cluster_uuid !== recoveryTarget.cluster_uuid) {
            sameHostRecoveryFlag = false;
        } else if (!recoverySource.cluster_flag && recoverySource.agent_uuid !== recoveryTarget.agent_uuid) {
            sameHostRecoveryFlag = false;
        }
        let sapHanaRecoveryTimeFlag = parseInt($('#sapHanaRecoveryTime').val());
        let disableInitLogFlag = !sameHostRecoveryFlag || sapHanaRecoveryTimeFlag === RECOVERY_TIME_FLAG_ENUM.TIMEPOINT;
        if (disableInitLogFlag) {  // 选择备份点
            $('.db_init_log_area').bootstrapSwitch('state', true);
            $('.db_init_log_area').bootstrapSwitch('disabled', true);
        } else {
            $('.db_init_log_area').bootstrapSwitch('state', false);
        }
        for (const nodeKey in recoveryMethod.db_config) {  // 初始化状态
            recoveryMethod.db_config[nodeKey].initialize_log_area = disableInitLogFlag;
        }
        $('.db_init_log_area').on('switchChange.bootstrapSwitch', function () {
            let nodeKey = $(this).attr('id').replace(/^init_log_area_/, '');
            recoveryMethod.db_config[nodeKey].initialize_log_area = !!this.checked;
        });
    };

    /**
     * 是否为一个有效的时间
     * @param dateTimeString
     * @return {boolean}
     */
    const isValidDateTime = dateTimeString => {
        // 正则表达式检查基本格式 yyyy-mm-dd hh:mm:ss
        const dateTimeRegex = /^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (0\d|1\d|2[0-3]):([0-5]\d):([0-5]\d)$/;

        if (!dateTimeRegex.test(dateTimeString)) {
            return false;
        }

        // 解析字符串为 Date 对象（仅使用日期部分进行基本验证）
        const [datePart, timePart] = dateTimeString.split(' ');
        const [year, month, day] = datePart.split('-').map(Number);
        const [hour, minute, second] = timePart.split(':').map(Number);

        // 创建 Date 对象进行进一步验证
        const date = new Date(year, month - 1, day, hour, minute, second);

        // 检查日期对象是否有效，以及是否与输入匹配（避免无效日期如 2023-02-30 被调整为 2023-03-02）
        return (
            date.getFullYear() === year &&
            date.getMonth() === month - 1 &&
            date.getDate() === day &&
            date.getHours() === hour &&
            date.getMinutes() === minute &&
            date.getSeconds() === second
        );
    };

    /**
     * 设置SAP HANA恢复时间
     */
    const setSAPHANARecoveryTime = (nodeKey) => {
        let pickTime = $('#recovery_time_' + nodeKey).val();
        recoveryMethod.db_config[nodeKey].pick_time = pickTime;
        // 根据选择的时间获取最近的备份点
        let timepoint = recoveryMethod.db_config[nodeKey].timepoint;
        for (const _timepoint of recoveryMethod.db_config[nodeKey].timepoint_list) {
            if (convertToUnixTimestamp(pickTime) >= convertToUnixTimestamp(_timepoint.time_point)) {
                timepoint = _timepoint;
                break;
            }
        }
        recoveryMethod.db_config[nodeKey].timepoint = timepoint;
        recoveryMethod.db_config[nodeKey].encrypt_password = '';
        initSAPHANACompleteStrategy();
        initSAPHANANetworkList();
        initSAPHANAResourceLimit();
        setSAPHANATapeConfig();
        let encryptedElement = '';
        if (timepoint.is_encrypted && !timepoint.config.password_auto_flag) {
            encryptedElement = `
            <div class="form-group">
                <label class="control-label col-md-3">${$('.passwordlabel').html()}</label>
                <div class="col-md-9">
                    <input type="password" class="form-control sap_hana_db_encrypted_password" id="encrypted_password_${nodeKey}"/>
                </div>
            </div>
            `;
        }
        let nodeName = LANG.UI_DB_RECOVERY_SAPHANA_NODE + timepoint.storage_info.node_ip;
        let showName = `${timepoint.time_point} - ${nodeName}`;
        // 存储离线
        if (parseInt(timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
            $('.sapHanaTimepointStorageOfflineDiv').show();
            $('.sapHanaTimepointStorageOfflineDiv .alert-danger').show();
            showName += '(' + LANG.UI_PUBLIC_STORAGE_OFFLINE + ')';
            $(`#timepoint_text_${nodeKey}`).addClass('storageOffline');
        } else {
            $(`#timepoint_text_${nodeKey}`).removeClass('storageOffline');
        }
        let timepointName = `
                    <span style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; word-break: break-all; -webkit-box-orient: vertical; -webkit-line-clamp: 3"
                        class="popovers" data-container="body" data-trigger="hover"
                        data-placement="top" data-html="true" data-content="${showName}">
                        ${showName}
                    </span>`;
        $(`#encrypted_password_td_${nodeKey}`).html(encryptedElement);
        $(`#timepoint_text_${nodeKey}`).html(timepointName);
        registerSAPHANAEncryptedPassword();
        // 判断所有存储都是否离线了
        let anyOfflineFlag = false;
        for (const nodeKey in recoveryMethod.db_config) {
            let dbConfig = recoveryMethod.db_config[nodeKey];
            if (!dbConfig.timepoint) {
                continue;
            }
            if (parseInt(dbConfig.timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {
                anyOfflineFlag = true;
                break;
            }
        }
        if (!anyOfflineFlag) {
            $('.sapHanaTimepointStorageOfflineDiv').hide();
        }
    };

    /**
     * 注册SAP HANA恢复时间
     */
    const registerSAPHANARecoveryTime = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            // 定时恢复最新备份点总是恢复最新时间，因此不需要执行后续代码
            return;
        }
        let sapHanaRecoveryTimeFlag = parseInt($('#sapHanaRecoveryTime').val());
        if (sapHanaRecoveryTimeFlag === RECOVERY_TIME_FLAG_ENUM.TIMEPOINT) {  // 选择备份点
            /**
             * 切换备份点事件
             * 1. 更新选择的备份点
             * 2. 清除已输入的存储加密密码
             * 3. 判断选择的备份点是否有存储加密
             */
            $('.sapHanaSelectTimepoint').off('change').on('change', function () {
                let nodeKey = $(this).attr('id').replace(/^select_timepoint_/, '');
                let timeointUuid = $(this).val();
                let timepoint = recoveryMethod.db_config[nodeKey].timepoint;
                for (const _timepoint of recoveryMethod.db_config[nodeKey].timepoint_list) {
                    if (_timepoint.time_point_uuid === timeointUuid) {
                        timepoint = _timepoint;
                        break;
                    }
                }
                recoveryMethod.db_config[nodeKey].timepoint = timepoint;
                recoveryMethod.db_config[nodeKey].encrypt_password = '';
                let encryptedElement = '';
                if (timepoint.is_encrypted && !timepoint.config.password_auto_flag) {
                    encryptedElement = `
                    <div class="form-group">
                        <label class="control-label col-md-3">${$('.passwordlabel').html()}</label>
                        <div class="col-md-9">
                            <input type="password" class="form-control sap_hana_db_encrypted_password" id="encrypted_password_${nodeKey}"/>
                        </div>
                    </div>
                    `;
                }
                $(`#encrypted_password_td_${nodeKey}`).html(encryptedElement);
                registerSAPHANAEncryptedPassword();
                // 这一步也要初始化节点信息
                initSAPHANACompleteStrategy();
                initSAPHANANetworkList();
                initSAPHANAResourceLimit();
                setSAPHANATapeConfig();
            });
        } else if (sapHanaRecoveryTimeFlag === RECOVERY_TIME_FLAG_ENUM.TIME) {  // 在时间区间选择时间点
            $.each(recoveryMethod.db_config, (nodeKey, selectedNode) => {
                let timePickerId = `#recovery_time_picker_${nodeKey}`;
                let timeValId = `#recovery_time_${nodeKey}`
                initDatetimePicker(timePickerId, timeValId, function () {
                    setSAPHANARecoveryTime(nodeKey);
                }, () => {  // 取消事件
                    $(`#encrypted_password_td_${nodeKey}`).html('');
                    $(`#timepoint_text_${nodeKey}`).html('--');
                    recoveryMethod.db_config[nodeKey].pick_time = '';
                    recoveryMethod.db_config[nodeKey].timepoint = null;
                    recoveryMethod.db_config[nodeKey].encrypt_password = '';
                });

                /**
                 * 设置选择的范围
                 */
                $(timePickerId).data('daterangepicker').setStartDate(selectedNode.pick_time);  // 这里设置最新时间
                $(timePickerId).data('daterangepicker').setEndDate(selectedNode.pick_time);  // 这里设置最新时间
                $(timePickerId).data('daterangepicker').minDate = moment(selectedNode.db_start_time);
                $(timePickerId).data('daterangepicker').maxDate = moment(selectedNode.db_end_time);
                $(timePickerId).val(selectedNode.pick_time);
                $(timeValId).val(selectedNode.pick_time);
                let tips = LANG.UI_DB_RECOVERY_RECOVERY_TIME_RANGE + ': ' + selectedNode.db_start_time
                    + ' ~ ' + selectedNode.db_end_time;
                $(`#recovery_time_tips_${nodeKey}`).html(tips);
                $('#reset_recovery_' + nodeKey).unbind('click').on('click', () => {
                    $(timePickerId).trigger('cancel.daterangepicker', [$(timePickerId)]);
                });
                $(timeValId).on('change', function () {
                    let pickTime = $(this).val().trim();
                    if (!pickTime) {
                        recoveryMethod.db_config[nodeKey].pick_time = '';
                        return;
                    }
                    if (!isValidDateTime(pickTime)) {
                        recoveryMethod.db_config[nodeKey].pick_time = selectedNode.db_end_time;
                        $(this).val(selectedNode.db_end_time);
                        return;
                    }
                    let pickTimestamp = convertToUnixTimestamp(pickTime);
                    if (pickTimestamp < convertToUnixTimestamp(selectedNode.db_start_time)) {
                        recoveryMethod.db_config[nodeKey].pick_time = selectedNode.db_start_time;
                        $(this).val(selectedNode.db_start_time);
                    } else if (pickTimestamp > convertToUnixTimestamp(selectedNode.db_end_time)) {
                        recoveryMethod.db_config[nodeKey].pick_time = selectedNode.db_end_time;
                        $(this).val(selectedNode.db_end_time);
                    } else {
                        recoveryMethod.db_config[nodeKey].pick_time = pickTime;
                    }
                    setSAPHANARecoveryTime(nodeKey);
                });
            });
        }
    };

    /**
     * 注册SAP HANA数据加密密码
     */
    const registerSAPHANAEncryptedPassword = () => {
        // 数据加密密码
        $('.db_encrypted_password').unbind('change').unbind('input').on('input', function () {
            $(this).val($(this).val().replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g, ''));
        }).on('change', function () {
            let nodeKey = $(this).attr('id').replace(/^encrypted_password_/, '');
            recoveryMethod.db_config[nodeKey].encrypt_password = $(this).val();
            let reqData = {
                encrypt_password: btoa(recoveryMethod.db_config[nodeKey].encrypt_password),
                time_point_uuid: recoveryMethod.db_config[nodeKey].timepoint.time_point_uuid,
            };
            pAjaxRequest(reqData, `/api/v1/db/jobs/backup/password`, 'POST', res => {
                if (!res.success) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_DATA_ENCRYPT_PASSWORD_ERROR);
                }
            });
        });
    };

    /**
     * 注册SAP HANA新建数据库
     */
    const registerSAPHANANewDbName = () => {
        // 新建数据库
        $('.new_db_config').unbind('change').unbind('input').on('input', function () {
            $(this).val($(this).val().replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g, ''));
        }).on('change', function () {
            let nodeKey = $(this).attr('id').replace(/^new_db_/, '');
            recoveryMethod.db_config[nodeKey].new_db_name = $(this).val();
            $(this).attr('data-content', recoveryMethod.db_config[nodeKey].new_db_name);
        });

    };

    /**
     * 注册SAP HANA多数据库恢复的事件监听
     */
    const registerSAPHANAMultiConfigListener = () => {
        // 注册新建数据库
        registerSAPHANANewDbName();
        // 注册恢复时间
        registerSAPHANARecoveryTime();
        // 注册初始化日志
        registerSAPHANAInitLogArea();
        // 注册加密密码
        registerSAPHANAEncryptPassword();
    };

    /**
     * 注册多数据库恢复的事件监听
     */
    const registerMultiConfigListener = () => {
        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.SQLSERVER:
                registerSqlServerMultiConfigListener();
                break;
            case CONF.DB_TYPE.SAPHANA:
                registerSAPHANAMultiConfigListener();
                break;
        }
    };

    /**
     * 检查路径的空白字符
     * @param {string} path
     * @returns {boolean}
     */
    var checkPathWhiteSpace = function (path) {
        if (path.length !== $.trim(path).length) {
            UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_RECOVERY_WHITE_SPACE);
            return false;
        }
        return true;
    }

    /**
     * 获取日期时间
     * @return {*}
     */
    var getCurrentDatetimeStr = function (date = null) {
        if (date === null) {
            date = new Date();
        }
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hour = String(date.getHours()).padStart(2, '0');
        const minute = String(date.getMinutes()).padStart(2, '0');
        const second = String(date.getSeconds()).padStart(2, '0');
        return `${year}-${month}-${day} ${hour}:${minute}:${second}`;
    }

    /**
     * 显示DM数据库在步骤3<恢复方式>切换后需要显示的配置项
     * @param pathType
     */
    const doDMShowStep3Item = (pathType) => {
        $('.dmfileDiv').hide();  // 隐藏指定文件夹路径
        showStep3RollbackTime();

        $('.timer-recovery-newest-tips').hide();
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            if (recoveryMethod.path_type === PATH_TYPE_ENUM.SPECIFY_FOLDER) {
                $('.timer-recovery-newest-tips').show();
            }
        }

        if (pathType === PATH_TYPE_ENUM.COVER) {  // 原数据库恢复
            //
        } else if (pathType === PATH_TYPE_ENUM.SPECIFY_FOLDER) {  // 指定文件夹恢复
            $('.dmfileDiv').show();  // 显示指定文件夹路径
        }
    };

    /**
     * 显示postgres数据库在步骤3<恢复方式>切换后需要显示的配置项
     * @param {int} pathType 恢复方式
     */
    var doPostgresShowStep3Item = function (pathType) {
        $('.pgNewInstanceDiv').hide();  // 隐藏新建实例恢复
        $('.pgSpecifyFolderDiv').hide();  // 隐藏指定文件夹恢复
        showStep3RollbackTime();

        $('.timer-recovery-newest-tips').hide();
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            if (
                recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE ||
                recoveryMethod.path_type === PATH_TYPE_ENUM.SPECIFY_FOLDER
            ) {
                $('.timer-recovery-newest-tips').show();
            }
        }

        if (pathType === PATH_TYPE_ENUM.COVER) {  // 原数据库恢复
            //
        } else if (pathType === PATH_TYPE_ENUM.SPECIFY_FOLDER) {  // 指定文件夹恢复
            $('.pgSpecifyFolderDiv').show();  // 显示自定义端口和自定义归档目录
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                $(`#pgSpecifyFolderHelpBlock`).hide();
                $(`#pgCustomArchivelogHelpBlock`).hide();
            } else {
                $(`#pgSpecifyFolderHelpBlock`).show();
                $(`#pgCustomArchivelogHelpBlock`).show();
            }
        } else if (pathType === PATH_TYPE_ENUM.CREATE) {  // 新建实例恢复
            $('.pgNewInstanceDiv').show();
        }
    }

    var doMongoDBShowStep3Item = function (pathType) {
        showStep3RollbackTime();
        $('.parallelNumDiv').show();  // 显示客户端并行数量
        $('.advancedDiv').show();     // 显示高级配置
        if (CONF.BD_STORAGE_TYPE.TAPE !== storage_type && drillStorageType !== CONF.BD_STORAGE_TYPE.TAPE) {
            if (!CONF.FUNCTIONS.includes('multithread')) {
                $('.mongodbThreadDiv').hide();  	  // 隐藏传输线程
                $('#mongodbRecoveryThreadDiv').spinner('value', 1);
            } else {
                $('.mongodbThreadDiv').show();  	  // 显示传输线程
            }
        } else {
            $('.mongodbThreadDiv').hide();  	  // 隐藏传输线程
        }
        $('.mongodbSelectDirDiv').hide();  // 隐藏选择文件夹
        if (PATH_TYPE_ENUM.COVER === pathType) {  // 原数据库覆盖恢复
        } else if (PATH_TYPE_ENUM.SPECIFY_FOLDER === pathType) {  // 指定文件夹恢复
            $('.mongodbSelectDirDiv').show();
        }
    }

    //////////////////// 开始-TiDB步骤三事件 ////////////////////

    /**
     * 初始化TiDB恢复时间轴
     */
    const initTiDBRecoveryTimeRange = () => {
        let singlePointFlag = !!$('#tidbRecoveryBackupSet option:selected').data('single');
        if (singlePointFlag) {
            return;
        }
        let startDatetime = getCurrentDatetimeStr(new Date(tidbTimepointList.start_time * 1000));
        let endDatetime = getCurrentDatetimeStr(new Date(tidbTimepointList.end_time * 1000));
        $('.tidbRecoveryTimeSelectWrapper .minTime').html(startDatetime);
        $('.tidbRecoveryTimeSelectWrapper .maxTime').html(endDatetime);

        if (tidbRecoveryTimeSlider !== null) {
            tidbRecoveryTimeSlider.destroy();
            tidbRecoveryTimeSlider = null;
        }
        tidbRecoveryTimeSlider = noUiSlider.create($('#tidbRecoveryTimeSelectRange').get(0), {
            start: [tidbTimepointList.end_time],
            range: {
                min: tidbTimepointList.start_time,
                max: tidbTimepointList.end_time,
            },
            connect: 'lower',
            step: 1,
            behaviour: 'smooth-tap-steps',
            tooltips: false,
        });
        $('#tidbRecoveryTimeSelectTime').val(endDatetime);
        registerTiDBRecoveryRangeSliderEvent();
    };

    /**
     * 注册事件
     */
    const registerTiDBRecoveryRangeSliderEvent = () => {
        tidbRecoveryTimeSlider.off();
        tidbRecoveryTimeSlider.on('start', tidbRecoveryRangeStart);
        tidbRecoveryTimeSlider.on('slide', tidbRecoveryRangeSlide);
        tidbRecoveryTimeSlider.on('change', tidbRecoveryRangeChange);
        tidbRecoveryTimeSlider.on('end', tidbRecoveryRangeEnd);
    };

    /**
     * TiDB恢复时间轴结束
     */
    const tidbRecoveryRangeEnd = () => {
        let rangeData = tidbRecoveryTimeSlider.get(true);
        let startTime = getCurrentDatetimeStr(new Date(rangeData * 1000));

        $('#tidbRecoveryTimeSelectTime').val(startTime);
        tidbRecoveryTimeSlider.updateOptions({
            tooltips: false,
        });
    };

    /**
     * TiDB恢复时间轴修改
     */
    const tidbRecoveryRangeChange = () => {
        let rangeData = tidbRecoveryTimeSlider.get(true);
        let startTime = getCurrentDatetimeStr(new Date(rangeData * 1000));

        $('#tidbRecoveryTimeSelectTime').val(startTime);
        doTiDBShowStep3Item(parseInt($('#pathType').val()));
    };

    /**
     * TiDB恢复时间轴更新
     */
    const tidbRecoveryRangeSlide = () => {
        let rangeData = tidbRecoveryTimeSlider.get(true);
        let startTime = getCurrentDatetimeStr(new Date(rangeData * 1000));

        $('#tidbRecoveryTimeSelectTime').val(startTime);
    };

    /**
     * TiDB恢复时间轴开始
     */
    const tidbRecoveryRangeStart = (values, handle) => {
        if (handle === 0) {
            tidbRecoveryTimeSlider.updateOptions({
                tooltips: {
                    to: function (value) {
                        return getCurrentDatetimeStr(new Date(value * 1000));
                    }
                },
            });
        } else {
            tidbRecoveryTimeSlider.updateOptions({
                tooltips: false,
            });
        }
    };

    /**
     * 划分TiDB备份集
     */
    const spliteTiDBTimepointSet = (timepointList) => {
        tidbTimepointSet = {
            newest_timepoint: null,
            backup_set: {},
            backup_timepoint_list: [],
        };
        /**
         * 备份链合并
         * 1. 构建备份链，开始时间，结束时间
         * 2. 备份链的开始时间等于另一条备份链的结束时间，那么合并这个备份链
         */
        // 备份点降序排序
        timepointList.sort((b, a) => {
            return a.src_end_time_point - b.src_end_time_point;
        });
        let backupChainList = {};
        // 先构建完备点
        for (const timepointInfo of timepointList) {
            if (parseInt(timepointInfo.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {  // 完全备份点
                backupChainList[timepointInfo.time_point_uuid] = {
                    full_time_point_uuid: timepointInfo.time_point_uuid,
                    full_time_point: timepointInfo,
                    start_time: timepointInfo.src_end_time_point,
                    end_time: timepointInfo.src_end_time_point,
                    timepoint_list: [timepointInfo],
                    single_point_flag: true,
                };
            }
            if (tidbTimepointSet.newest_timepoint === null) {
                tidbTimepointSet.newest_timepoint = timepointInfo;
            }
        }
        // 根据日志备份点构建备份链
        for (const timepointInfo of timepointList) {
            if (parseInt(timepointInfo.backup_mode) !== TIMEPOINT_TYPE_ENUM.LOG) {  // 日志备份点
                continue;
            }
            if (typeof backupChainList[timepointInfo.full_time_point_uuid] === 'undefined') {  // 找不到依赖的完备点
                console.log('error timepoint: ', timepointInfo);
                continue;
            }
            if (timepointInfo.src_start_time_point === timepointInfo.src_end_time_point) {  // 没有备份任务日志，直接跳过
                continue;
            }
            if (backupChainList[timepointInfo.full_time_point_uuid].start_time > timepointInfo.src_start_time_point) {
                backupChainList[timepointInfo.full_time_point_uuid].start_time = timepointInfo.src_start_time_point;
            }
            if (backupChainList[timepointInfo.full_time_point_uuid].end_time < timepointInfo.src_end_time_point) {
                backupChainList[timepointInfo.full_time_point_uuid].end_time = timepointInfo.src_end_time_point;
            }
            backupChainList[timepointInfo.full_time_point_uuid].single_point_flag = false;
            backupChainList[timepointInfo.full_time_point_uuid].timepoint_list.push(timepointInfo);
        }

        // 排序，按照开始时间升序排序
        backupChainList = Object.values(backupChainList);
        backupChainList.sort((a, b) => {
            return a.start_time - b.start_time;
        });
        // 合并备份链
        for (const backupChain of backupChainList) {
            if (!tidbTimepointSet.backup_timepoint_list.length) {
                tidbTimepointSet.backup_timepoint_list.push(backupChain);
                continue;
            }
            let lastTimepoint = tidbTimepointSet.backup_timepoint_list[tidbTimepointSet.backup_timepoint_list.length - 1];
            let sameTimepointFlag = backupChain.start_time === backupChain.end_time &&
                lastTimepoint.start_time === lastTimepoint.end_time &&
                backupChain.start_time === lastTimepoint.start_time &&
                backupChain.end_time === lastTimepoint.end_time;
            if (backupChain.start_time === lastTimepoint.end_time && !sameTimepointFlag) {
                tidbTimepointSet.backup_timepoint_list[tidbTimepointSet.backup_timepoint_list.length - 1].end_time = backupChain.end_time;
                for (const timepointInfo of backupChain.timepoint_list) {
                    tidbTimepointSet.backup_timepoint_list[tidbTimepointSet.backup_timepoint_list.length - 1].timepoint_list.push(timepointInfo);
                }
            } else {
                tidbTimepointSet.backup_timepoint_list.push(backupChain);
            }
        }

        tidbTimepointSet.backup_timepoint_list.sort((a, b) => {
            let reduce = b.start_time - a.start_time;
            if (reduce === 0) {
                return b.end_time - a.end_time;
            }
            return reduce;
        });
        for (const backupTimepoint of tidbTimepointSet.backup_timepoint_list) {
            tidbTimepointSet.backup_set[backupTimepoint.full_time_point_uuid] = backupTimepoint;
        }
    };

    /**
     * 获取TiDB备份集
     */
    const getTiDBBackupSet = () => {
        let firstNode = Object.values(recoverySource.time_point_list)[0];
        let reqData = {
            agent_list: [{
                cluster_flag: firstNode.cluster_flag,
                cluster_uuid: firstNode.cluster_uuid,
                instance_name: firstNode.instance_name,
                agent_uuid: firstNode.agent_uuid,
            }],
            db_type: CONF.DB_TYPE.TIDB,
            with_copy_flag: true,
            with_copy_back_flag: 1,
            storage_uuid: $('#timepointStorageSelect').val(),
        };
        return new Promise(resolve => {
            Metronic.blockUI({target: '#dbrecovercontent', animate: true});
            pAjaxRequest(reqData, `/api/v1/db/jobs/backup/time_point_list`, 'GET', res => {
                Metronic.unblockUI('#dbrecovercontent');
                if (!res.success) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, res.message);
                    return;
                }
                spliteTiDBTimepointSet(res.data.rows);
                // 构建备份集
                let options = ``;
                for (const timepointInfo of tidbTimepointSet.backup_timepoint_list) {
                    let startTime = getCurrentDatetimeStr(new Date(timepointInfo.start_time * 1000));
                    let endTime = getCurrentDatetimeStr(new Date(timepointInfo.end_time * 1000));
                    let showStr = `${startTime} ~ ${endTime}`;
                    let singlePointFlag = '0';
                    let disabled = '';
                    if (timepointInfo.single_point_flag) {
                        singlePointFlag = '1';
                        showStr = `${startTime} (${LANG.UI_DATA_TYPE_FULL})`;
                    }
                    // 存储离线
                    if (parseInt(timepointInfo.full_time_point.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                        disabled = 'disabled';
                        showStr += '(' + LANG.UI_PUBLIC_STORAGE_OFFLINE + ')';
                    }
                    options += `<option value="${timepointInfo.full_time_point_uuid}" data-single="${singlePointFlag}" ${disabled}>${showStr}</option>`;
                }
                let $tidbRecoveryBackupSet = $(`#tidbRecoveryBackupSet`);
                $tidbRecoveryBackupSet.next('div.searchable-select').remove();
                $tidbRecoveryBackupSet.html(options).searchableSelect();
                $tidbRecoveryBackupSet.next('div.searchable-select').find('.searchable-select-item').on('click', tidbRecoveryBackupSetChange);
                resolve();
            });
        });
    };

    /**
     * 初始化TiDB备份集
     */
    const initTiDBTimepointSet = () => {
        /**
         * TiDB恢复时间显示
         * 1. 游离的完备点单独显示
         * 2. 有日志备份点的备份链显示范围
         * 3. 游离的完备点与备份链之间禁止选择
         */
        tidbTimepointList = {
            start_time: 0,
            end_time: 0,
            timepoint_list: [],
            select_timepoint: null,
            old_select_timepoint: null,
            slider_data: {
                min: 0,
                max: 0,
            },
        }
        let fullTimepointUuid = $('#tidbRecoveryBackupSet').val();
        if (!fullTimepointUuid) {
            return;
        }
        tidbTimepointList.timepoint_list = tidbTimepointSet.backup_set[fullTimepointUuid].timepoint_list;
        tidbTimepointList.select_timepoint = tidbTimepointList.timepoint_list[0];

        tidbTimepointList.start_time = 0;
        tidbTimepointList.end_time = 0;
        for (const row of tidbTimepointList.timepoint_list) {
            if (parseInt(row.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {
                // 开始时间
                if (tidbTimepointList.start_time === 0) {
                    tidbTimepointList.start_time = row.src_end_time_point;
                } else if (tidbTimepointList.start_time > row.src_end_time_point) {
                    tidbTimepointList.start_time = row.src_end_time_point;
                }

                // 结束时间
                if (tidbTimepointList.end_time === 0) {
                    tidbTimepointList.end_time = row.src_end_time_point;
                } else if (tidbTimepointList.end_time < row.src_end_time_point) {
                    tidbTimepointList.end_time = row.src_end_time_point;
                }
                continue;
            }
            // 开始时间
            if (tidbTimepointList.start_time === 0) {
                tidbTimepointList.start_time = row.src_start_time_point;
            } else if (tidbTimepointList.start_time > row.src_start_time_point) {
                tidbTimepointList.start_time = row.src_start_time_point;
            }

            // 结束时间
            if (tidbTimepointList.end_time === 0) {
                tidbTimepointList.end_time = row.src_end_time_point;
            } else if (tidbTimepointList.end_time < row.src_end_time_point) {
                tidbTimepointList.end_time = row.src_end_time_point;
            }
        }
        tidbTimepointList.old_select_timepoint = null;
        // 根据结束时间倒序排序
        tidbTimepointList.timepoint_list.sort((a, b) => {
            return b.src_end_time_point - a.src_end_time_point;
        });

        // 初始化时间控件
        initTiDBRecoveryTimeRange();
    };

    /**
     * 初始化TiDB时间点
     */
    const initTiDBTimepoint = () => {
        return new Promise(resolve => {
            getTiDBBackupSet().then(() => {
                initTiDBTimepointSet();
                resolve();
            });
        });
    };

    /**
     * 根据时间获取TiDB的备份点
     */
    const getTiDBRecoveryTimepointByTime = (pathType) => {
        let tidbRecoveryTimepoint = parseInt($('#tidbRecoveryTimepoint').val());
        if (TIDB_RECOVERY_TIMEPOINT_ENUM.NEWEST === tidbRecoveryTimepoint) {
            return tidbTimepointSet.newest_timepoint;
        }

        let singlePointFlag = !!$('#tidbRecoveryBackupSet option:selected').data('single');
        if (singlePointFlag) {
            let fullTimepointUuid = $('#tidbRecoveryBackupSet').val();
            return tidbTimepointSet.backup_set[fullTimepointUuid].timepoint_list[0];
        }

        // 按时间搜索
        // 根据结束时间正序排序
        tidbTimepointList.timepoint_list.sort((b, a) => {
            return b.src_end_time_point - a.src_end_time_point;
        });
        let selectTimestamp = convertToUnixTimestamp($('#tidbRecoveryTimeSelectTime').val());
        let matchTimepoint = null;
        for (const row of tidbTimepointList.timepoint_list) {
            if (parseInt(row.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {
                if (row.src_end_time_point === selectTimestamp) {  // 完备点，直接返回
                    return row;
                }
                continue;
            }
            if (row.src_start_time_point <= selectTimestamp && row.src_end_time_point >= selectTimestamp) {
                matchTimepoint = row;
                break;
            }
        }
        if (matchTimepoint !== null) {
            return matchTimepoint;
        }
        // 根据结束时间倒序排序
        tidbTimepointList.timepoint_list.sort((a, b) => {
            return b.src_end_time_point - a.src_end_time_point;
        });
        // 这种情况不会发生
        return tidbTimepointList.timepoint_list[tidbTimepointList.timepoint_list.length - 1];
    };

    /**
     * 初始化TiDB资源限制
     */
    const initTiDBResourceLimit = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            // 不支持演练任务
            return;
        }
        let tidbTimepoint = tidbTimepointList.select_timepoint;
        let nodeUuid = tidbTimepoint.storage_info.node_uuid;
        if (oldNodeUuidForResourceLimit !== null && nodeUuid === oldNodeUuidForResourceLimit) {
            // 表示之前已经初始化了，不需要再重复初始化
            return;
        }
        oldNodeUuidForResourceLimit = nodeUuid;
        initResourceLimit([nodeUuid]);
    };

    /**
     * 初始化TiDB网络列表
     */
    const initTiDBNetworkList = () => {
        if (!networkFlag || recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            // 不支持网络及恢复最新备份点
            return;
        }
        let tidbTimepoint = tidbTimepointList.select_timepoint;
        let nodeUuid = tidbTimepoint.storage_info.node_uuid;
        if (oldNodeUuid !== null && nodeUuid === oldNodeUuid) {
            // 表示之前已经初始化了，不需要再重复初始化
            return;
        }
        oldNodeUuid = nodeUuid;
        $('#transferNetworkTree').transferNetwork({
            node_uuid: nodeUuid,
            onChange: function (transferNetworkNode) {
                if (!transferNetworkNode.checked) {
                    return;
                }
            }
        });
    };

    /**
     * 设置TiDB的磁带配置
     */
    const setTiDBTapeConfig = () => {
        storage_type = parseInt(tidbTimepointList.select_timepoint.storage_info.storage_type);
        setVisibleForTape();
    };

    /**
     * TiDB步骤3配置项
     */
    var doTiDBShowStep3Item = function (pathType) {
        // showEncrypted();
        // showStep3RollbackTime();
        $('.tidbRecoveryBackupSetDiv').hide();
        $('.tidbRecoveryTimepointDiv').hide();
        $('.tidbRecoveryTimeSelectDiv').hide();
        $('.tidbTimepointStorageOfflineDiv').hide();
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            tidbTimepointList.select_timepoint = getTiDBRecoveryTimepointByTime(pathType);
            $('.tidbRecoveryTimepointDiv').show();
            let tidbRecoveryTimepoint = parseInt($('#tidbRecoveryTimepoint').val());
            if (TIDB_RECOVERY_TIMEPOINT_ENUM.TIME === tidbRecoveryTimepoint) {
                $('.tidbRecoveryBackupSetDiv').show();
                let singlePointFlag = !!$('#tidbRecoveryBackupSet option:selected').data('single');
                if (!singlePointFlag) {
                    $('.tidbRecoveryTimeSelectDiv').show();
                } else {
                    $('.tidbRecoveryTimeSelectDiv').hide();
                }
            } else {
                // 存储离线
                if (!tidbTimepointList.select_timepoint || parseInt(tidbTimepointList.select_timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                    $('.tidbTimepointStorageOfflineDiv').show();
                    $('.tidbTimepointStorageOfflineDiv .alert-danger').show();
                    return;
                }
            }

            if (!tidbTimepointList.select_timepoint) {
                $('.tidbTimepointStorageOfflineDiv').show();
                $('.tidbTimepointStorageOfflineDiv .alert-danger').show();
                return;
            }
            // 压缩加密
            if (tidbTimepointList.select_timepoint.is_encrypted && !tidbTimepointList.select_timepoint.config.password_auto_flag) {
                // 开启压缩加密，非自动生成密码
                $('.encryptDiv').show();
            } else {
                $('.encryptDiv').hide();
            }
            initCompleteCheckStrategyByTimepoint(tidbTimepointList.select_timepoint);
            // 初始化网络列表
            if (
                tidbTimepointList.old_select_timepoint === null ||
                tidbTimepointList.old_select_timepoint.time_point_uuid !== tidbTimepointList.select_timepoint.time_point_uuid
            ) {
                tidbTimepointList.old_select_timepoint = tidbTimepointList.select_timepoint;
                initTiDBNetworkList();
                initTiDBResourceLimit();
            }
            setTiDBTapeConfig();
        }
    }

    /**
     * 将字符串格式化为秒级时间戳
     * @param datetimeStr
     * @returns {number}
     */
    const convertToUnixTimestamp = (datetimeStr) => {
        // 解析日期时间字符串创建Date对象
        let date = new Date(datetimeStr);

        // 获取Unix时间戳（毫秒）
        let timestamp = date.getTime();

        // 转换为秒
        return Math.floor(timestamp / 1000);
    }

    //////////////////// 开始-Oracle配置 ////////////////////

    /**
     * Oracle导出备份点点击
     * @param {*} treeId
     * @param {*} treeNode
     */
    const oracleExportNodeClick = (treeId, treeNode) => {
        oracleExportTree.expandNode(treeNode, true);
        oracleExportTree.checkNode(treeNode, !treeNode.checked, true, true);
    };

    /**
     * Oracle导出备份点选中/取消选中
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const oracleExportNodeCheck = (ev, treeId, treeNode) => {
        if (!treeNode.checked) {
            return;
        }
        // oracleExportTree.checkAllNodes(false);
        // oracleExportTree.checkNode(treeNode, true);
        let checkedNodes = oracleExportTree.getCheckedNodes(true);
        let disableIntegrityCheckFlag = true;
        for (const checkedNode of checkedNodes) {
            if (checkedNode.timepoint.integrity_check_flag) {
                disableIntegrityCheckFlag = false;
                break;
            }
        }
        // 寻找最新的备份点
        oracleTimepointList.select_timepoint = null;
        for (const checkedNode of checkedNodes) {
            if (oracleTimepointList.select_timepoint === null) {
                oracleTimepointList.select_timepoint = checkedNode.timepoint;
            } else {
                if (checkedNode.timepoint.src_end_time_point > oracleTimepointList.select_timepoint.src_end_time_point) {
                    oracleTimepointList.select_timepoint = checkedNode.timepoint;
                }
            }
            for (const dependTimepoint of checkedNode.chain_info.depend_timepoint_list) {
                if (dependTimepoint.src_end_time_point > oracleTimepointList.select_timepoint.src_end_time_point) {
                    oracleTimepointList.select_timepoint = dependTimepoint;
                }
            }
        }
        initCompleteCheckStrategyByTimepoint({integrity_check_flag: !disableIntegrityCheckFlag});
        initOracleNetworkList();
        initOracleResourceLimit();
    };

    /**
     * 获取Oracle导出树的设置
     * @returns {*}
     */
    const getOracleExportTreeSetting = () => {
        return {
            check: {
                enable: true,
                nocheckInherit: false,
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
                },
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: (treeId, treeNode) => {
                    let style = {};
                    if (treeNode.eventtype === 'timepoint') {
                        if (parseInt(treeNode.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                            style = {'color': 'grey'};
                        }
                    }
                    return style;
                },
                addDiyDom: (treeId, treeNode) => {
                    if (treeNode.eventtype === 'instance') {
                        let aNodeObj = $(`#${treeNode.tId}_ico`);
                        aNodeObj.css({
                            background: `url(${treeNode.icon}) 0 no-repeat`,
                        });
                    } else if (treeNode.eventtype === 'timepoint') {
                        let aNodeObj = $(`#${treeNode.tId}_span`);
                        aNodeObj.css({
                            'white-space': `nowrap`,
                        });
                    }
                },
            },
            callback: {
                beforeClick: oracleExportNodeClick,
                onCheck: oracleExportNodeCheck,
            },
        };
    };

    /**
     * 获取Oracle导出树的节点
     * @param timepointListChain
     * @param drawEncryptPasswordInputFlag
     */
    const getOracleExportTreeNodes = (timepointListChain, drawEncryptPasswordInputFlag = true) => {
        let firstNode = Object.values(recoverySource.time_point_list)[0];
        /**
         * @type {Object}
         */
        let nodes = [{
            id: firstNode.instance_name,
            pId: 0,
            name: firstNode.dir_path,
            title: firstNode.dir_path,
            icon: './img/db/oracle.png',
            isParent: true,
            open: true,
            nocheck: true,
            eventtype: 'instance'
        }];

        for (const chainInfo of timepointListChain) {
            let chkDisabled = false;
            let name = chainInfo.full_timepoint.time_point + '(' + LANG.UI_DATA_TYPE_FULL + ')';
            let pName = name;
            if (chainInfo.full_timepoint.is_encrypted && !chainInfo.full_timepoint.config.password_auto_flag) {  // 加密密码
                name += `<i class="fa fa-lock"></i>`;
            }
            // 存储离线
            if (parseInt(chainInfo.full_timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                chkDisabled = true;
                name += '(' + LANG.UI_PUBLIC_STORAGE_OFFLINE + ')';
            } else {
                // 在线再设置密码输入框
                if (chainInfo.full_timepoint.is_encrypted && !chainInfo.full_timepoint.config.password_auto_flag && drawEncryptPasswordInputFlag) {  // 手动加密
                    name += `
                    <div class="oracleEncryptPasswordWrapper">
                        <label class="control-label" for="oracle_export_encrypt_password_${chainInfo.full_timepoint.time_point_uuid}">${$('.passwordlabel').html()}: </label>
                        <input type="password" class="form-control oracleEncryptPassword" data-uuid="${chainInfo.full_timepoint.time_point_uuid}"
                            id="oracle_export_encrypt_password_${chainInfo.full_timepoint.time_point_uuid}" maxlength="256"
                            oninput="value=value.replace(/[\\u4E00-\\u9FA5]|[\\uFE30-\\uFFA0]|\\s+/g,'')"/>
                        <button type="button" class="btn btn-link show-encrypt-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                            <i class="viconfont vicon-a-lujing8232"></i>
                        </button>
                    </div>
                    `;
                }
            }
            nodes.push({
                id: firstNode.instance_name + '_' + chainInfo.full_timepoint.time_point_uuid,
                pId: firstNode.instance_name,
                name,
                title: chainInfo.full_timepoint.time_point,
                icon: './img/platform/timepoint-f.png',
                chkDisabled,
                open: true,
                eventtype: 'timepoint',
                p_name: pName,
                timepoint: chainInfo.full_timepoint,
                storage_status: chainInfo.full_timepoint.storage_info.storage_status,
                chain_info: chainInfo,
            });
            for (const oracleTimepoint of chainInfo.depend_timepoint_list) {
                let backupMode = parseInt(oracleTimepoint.backup_mode);
                let icon = './img/platform/timepoint-f.png';
                let name = oracleTimepoint.time_point;
                let id = firstNode.instance_name + '_' + chainInfo.full_timepoint.time_point_uuid + '_' + oracleTimepoint.time_point_uuid;
                let pId = firstNode.instance_name + '_' + chainInfo.full_timepoint.time_point_uuid;
                switch (backupMode) {
                    case TIMEPOINT_TYPE_ENUM.INCR:
                        icon = './img/platform/timepoint-i.png';
                        name += '(' + LANG.UI_DATA_TYPE_INCR + ')';
                        break;
                    case TIMEPOINT_TYPE_ENUM.DIFF:
                        icon = './img/platform/timepoint-d.png';
                        name += '(' + LANG.UI_DATA_TYPE_DIFF + ')';
                        break;
                    case TIMEPOINT_TYPE_ENUM.LOG:
                        icon = './img/platform/timepoint.png';
                        name += '(' + LANG.UI_DATA_TYPE_ARCHIVELOG + ')';
                        break;
                    default:
                        name += '(' + LANG.UI_DATA_TYPE_FULL + ')';
                        break;
                }
                nodes.push({
                    id,
                    pId,
                    name,
                    title: oracleTimepoint.time_point,
                    icon,
                    nocheck: true,
                    eventtype: 'timepoint',
                    p_name: name,
                    timepoint: oracleTimepoint,
                    storage_status: oracleTimepoint.storage_info.storage_status,
                });
            }
        }
        return nodes;
    };

    /**
     * 初始化导出树
     */
    const initOracleExportTree = () => {
        oracleExportTree = $.fn.zTree.init(
            $('#oracleExportTree'),
            getOracleExportTreeSetting(),
            getOracleExportTreeNodes(oracleTimepointList.timepoint_list_chain)
        );
    };

    /**
     * 初始化Oracle资源限制
     */
    const initOracleResourceLimit = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            // 不支持演练任务
            return;
        }
        let oracleTimepoint = oracleTimepointList.select_timepoint;
        let nodeUuid = oracleTimepoint.storage_info.node_uuid;
        if (oldNodeUuidForResourceLimit !== null && nodeUuid === oldNodeUuidForResourceLimit) {
            // 表示之前已经初始化了，不需要再重复初始化
            return;
        }
        oldNodeUuidForResourceLimit = nodeUuid;
        initResourceLimit([nodeUuid]);
    };

    /**
     * 初始化Oracle的传输网络
     */
    const initOracleNetworkList = () => {
        if (!networkFlag || recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            // 不支持网络及恢复最新备份点
            return;
        }
        let oracleTimepoint = oracleTimepointList.select_timepoint;
        let nodeUuid = oracleTimepoint.storage_info.node_uuid;
        if (oldNodeUuid !== null && nodeUuid === oldNodeUuid) {
            // 表示之前已经初始化了，不需要再重复初始化
            return;
        }
        oldNodeUuid = nodeUuid;
        $('#transferNetworkTree').transferNetwork({
            node_uuid: nodeUuid,
            onChange: function (transferNetworkNode) {
                if (!transferNetworkNode.checked) {
                    return;
                }
            }
        });
    };

    /**
     * 设置Oracle离线的范围
     * @param {*} oracleRangeTimepointList
     */
    const setOracleOfflineRange = (oracleRangeTimepointList) => {
        let gap = (oracleRangeTimepointList.end_time - oracleRangeTimepointList.start_time) / 100;
        let oracleOfflineHtml = ``;
        for (const offlineRange of oracleRangeTimepointList.offline_time_range) {
            let left = ((offlineRange.start_time - oracleRangeTimepointList.start_time) / gap).toFixed(2);
            let width = ((offlineRange.end_time - offlineRange.start_time) / gap).toFixed(2);
            if (parseFloat(width) < 1) {
                width = '1';
            }
            oracleOfflineHtml += `<div class="nonsupportArea" style="left: ${left}%; width: ${width}%;"></div>`;
        }
        return oracleOfflineHtml;
    };

    /**
     * 设置Oracle不可恢复范围
     * @param {*} oracleRangeTimepointList
     */
    const setOracleNonsupportRange = (oracleRangeTimepointList) => {
        let gap = (oracleRangeTimepointList.end_time - oracleRangeTimepointList.start_time) / 100;
        let oracleNonsupportHtml = ``;
        for (const nonsupportRange of oracleRangeTimepointList.nonsupport_time_range) {
            let left = ((nonsupportRange.start_time - oracleRangeTimepointList.start_time) / gap).toFixed(2);
            let width = ((nonsupportRange.end_time - nonsupportRange.start_time) / gap).toFixed(2);
            if (parseFloat(width) < 1) {
                width = '1';
            }
            oracleNonsupportHtml += `<div class="nonsupportArea" style="left: ${left}%; width: ${width}%;"></div>`;
        }
        return oracleNonsupportHtml;
    };

    /**
     * 注册事件
     * @param {*} oracleRangeTimepointList
     */
    const registerOracleRecoveryRangeSliderEvent = (oracleRangeTimepointList) => {
        console.log('build oracle recovery range slider', oracleRangeTimepointList)
        let nonsupportHtml = `<div class="nonsupportAreaWrapper">`;
        // 离线的范围
        nonsupportHtml += setOracleOfflineRange(oracleRangeTimepointList);
        // 不可恢复范围
        nonsupportHtml += setOracleNonsupportRange(oracleRangeTimepointList);
        nonsupportHtml += `</div>`;
        $('#oracleRecoveryTimeSelectRange .noUi-base').append(nonsupportHtml);

        oracleRecoveryTimeSlider.off();
        oracleRecoveryTimeSlider.on('start', (values, handle) => {
            oracleRecoveryRangeStart(values, handle, oracleRangeTimepointList);
        });
        oracleRecoveryTimeSlider.on('slide', oracleRecoveryRangeSlide);
        oracleRecoveryTimeSlider.on('change', () => {
            oracleRecoveryRangeChange(oracleRangeTimepointList);
        });
        oracleRecoveryTimeSlider.on('end', oracleRecoveryRangeEnd);
    };

    /**
     * oracle恢复时间轴结束
     */
    const oracleRecoveryRangeEnd = () => {
        oracleRecoveryTimeSlider.updateOptions({
            tooltips: false,
        });
    };

    /**
     * 滚动到下一个可用时间
     * @param oracleRangeTimepointList
     */
    const sliderToNextAvailableTime = (oracleRangeTimepointList) => {
        // 如果移动不可恢复区域，那么自动滑倒下一个可用区域
        let rangeData = oracleRecoveryTimeSlider.get(true);
        let oldData = rangeData;
        let tmpNonsupportRange = {
            offline_time_range: [],
        };
        for (const invalidRange of oracleRangeTimepointList.offline_time_range) {
            tmpNonsupportRange.offline_time_range.push({
                start_time: invalidRange.start_time,
                end_time: invalidRange.end_time,
            });
        }
        for (const invalidRange of oracleRangeTimepointList.nonsupport_time_range) {
            tmpNonsupportRange.offline_time_range.push({
                start_time: invalidRange.start_time,
                end_time: invalidRange.end_time,
            });
        }
        if (!judgeOracleRecoveryTimeNotInNonsupportRange(rangeData, tmpNonsupportRange)) {
            for (const invalidRange of tmpNonsupportRange.offline_time_range) {
                if (rangeData < invalidRange.end_time && rangeData > invalidRange.start_time) {
                    rangeData = invalidRange.end_time;
                    break;
                }
            }
        }
        if (oldData !== rangeData) {
            let startTime = getCurrentDatetimeStr(new Date(rangeData * 1000));
            $('#oracleRecoveryTimeSelectTime').val(startTime);
            oracleRecoveryTimeSlider.set([rangeData]);
        }
    };

    /**
     * oracle恢复时间轴修改
     */
    const oracleRecoveryRangeChange = (oracleRangeTimepointList) => {
        // initOracleTimepointChainByRecoveryTime();
        sliderToNextAvailableTime(oracleRangeTimepointList);
    };

    /**
     * oracle恢复时间轴更新
     */
    const oracleRecoveryRangeSlide = () => {
        let rangeData = oracleRecoveryTimeSlider.get(true);
        let startTime = getCurrentDatetimeStr(new Date(rangeData * 1000));

        $('#oracleRecoveryTimeSelectTime').val(startTime);
    };

    /**
     * 判断Oracle恢复时间是否不处于非法时间中
     * @param recoveryTime
     * @param rangeData
     * @param judgeKey
     * @returns {boolean}
     */
    const judgeOracleRecoveryTimeNotInNonsupportRange = (recoveryTime, rangeData, judgeKey = 'offline_time_range') => {
        for (const invalidRange of rangeData[judgeKey]) {
            if (recoveryTime > invalidRange.start_time && recoveryTime < invalidRange.end_time) {
                return false;
            }
            // 边界条件比较
            // 为最前面的开始时间
            if (recoveryTime === invalidRange.start_time && recoveryTime === rangeData.start_time) {
                return false;
            }
            // 为最后面的结束时间
            if (recoveryTime === invalidRange.end_time && recoveryTime === rangeData.end_time) {
                return false;
            }
        }
        return true;
    };

    /**
     * oracle恢复时间轴开始
     * @param {*} values
     * @param {*} handle
     * @param {*} oracleRangeTimepointList
     */
    const oracleRecoveryRangeStart = (values, handle, oracleRangeTimepointList) => {
        if (handle === 0) {
            oracleRecoveryTimeSlider.updateOptions({
                tooltips: {
                    to: function (value) {
                        value = parseInt(value);
                        let timeStr = getCurrentDatetimeStr(new Date(value * 1000));
                        let nonsupportFlag = false;
                        if (!nonsupportFlag) {
                            if (!judgeOracleRecoveryTimeNotInNonsupportRange(value, oracleRangeTimepointList)) {
                                timeStr += '(' + LANG.UI_PUBLIC_STORAGE_OFFLINE + ')';
                                nonsupportFlag = true;
                            }
                        }
                        if (!nonsupportFlag) {
                            if (!judgeOracleRecoveryTimeNotInNonsupportRange(value, oracleRangeTimepointList, 'nonsupport_time_range')) {
                                timeStr += '(' + LANG.UI_DB_RECOVERY_ORACLE_NONSUPPORT_TIME + ')';
                            }
                        }
                        return timeStr;
                    }
                },
            });
        } else {
            oracleRecoveryTimeSlider.updateOptions({
                tooltips: false,
            });
        }
    };

    /**
     * 根据恢复分支初始化Oracle备份集
     * @param {*} resetlogsTime
     * @param {*} dbIncarnation
     */
    const initOracleTimepointSetByResetlogsTime = (resetlogsTime, dbIncarnation) => {
        oracleTimepointList.offline_time_range = [];
        oracleTimepointList.nonsupport_time_range = [];
        oracleTimepointList.start_time = 0;
        oracleTimepointList.end_time = 0;
        oracleTimepointList.incarnation_timepoint_list = [];
        oracleTimepointList.task_chain_map = {};
        oracleTimepointList.select_task_chain = [];
        oracleTimepointList.timepoint_chain = [];
        for (const row of oracleTimepointList.timepoint_list) {
            if (
                parseInt(row.resetlogs_time) !== resetlogsTime ||
                parseInt(row.db_incarnation) !== dbIncarnation
            ) {
                continue;
            }

            oracleTimepointList.incarnation_timepoint_list.push(row);
        }
        // 升序排序
        oracleTimepointList.incarnation_timepoint_list.sort((a, b) => {
            return a.src_end_time_point - b.src_end_time_point;
        });
        // 构建备份链
        let timepointChain = {};
        for (const incarnationTimepoint of oracleTimepointList.incarnation_timepoint_list) {
            if (typeof timepointChain[incarnationTimepoint.chain_uuid] === 'undefined') {
                timepointChain[incarnationTimepoint.chain_uuid] = {
                    chain_uuid: incarnationTimepoint.chain_uuid,
                    full_timepoint: null,
                    start_time: incarnationTimepoint.src_start_time_point,
                    end_time: incarnationTimepoint.src_end_time_point,
                    timepoint_list: [],
                }
            }
            if (parseInt(incarnationTimepoint.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {
                timepointChain[incarnationTimepoint.chain_uuid].full_timepoint = incarnationTimepoint;
            } else {
                timepointChain[incarnationTimepoint.chain_uuid].timepoint_list.push(incarnationTimepoint);
            }
            if (incarnationTimepoint.src_start_time_point < timepointChain[incarnationTimepoint.chain_uuid].start_time) {
                timepointChain[incarnationTimepoint.chain_uuid].start_time = incarnationTimepoint.src_start_time_point;
            }
            if (incarnationTimepoint.src_end_time_point > timepointChain[incarnationTimepoint.chain_uuid].end_time) {
                timepointChain[incarnationTimepoint.chain_uuid].end_time = incarnationTimepoint.src_end_time_point;
            }
        }
        // 恢复时间范围
        for (const incarnationTimepoint of oracleTimepointList.incarnation_timepoint_list) {
            // 开始时间
            if (oracleTimepointList.start_time === 0) {
                if (parseInt(incarnationTimepoint.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {  // 开始时间采用最早完备点的checkpoint_time
                    oracleTimepointList.start_time = incarnationTimepoint.checkpoint_time;
                }
            }

            // 结束时间
            if (oracleTimepointList.end_time === 0) {
                oracleTimepointList.end_time = incarnationTimepoint.src_end_time_point;
            } else if (oracleTimepointList.end_time < incarnationTimepoint.src_end_time_point) {
                oracleTimepointList.end_time = incarnationTimepoint.src_end_time_point;
            }
        }
        let offlineStartTimepointMap = {};
        // 存储离线处理
        for (const incarnationTimepoint of oracleTimepointList.incarnation_timepoint_list) {
            // 存储离线的区域
            // 存储离线
            if (parseInt(incarnationTimepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                let tmpTimeInfo = {
                    start_time: incarnationTimepoint.src_start_time_point,
                    end_time: incarnationTimepoint.src_end_time_point,
                };
                if (parseInt(incarnationTimepoint.backup_mode) !== TIMEPOINT_TYPE_ENUM.FULL) {
                    tmpTimeInfo.start_time = offlineStartTimepointMap[incarnationTimepoint.full_time_point_uuid]
                } else {
                    tmpTimeInfo.start_time = incarnationTimepoint.checkpoint_time;
                    offlineStartTimepointMap[incarnationTimepoint.time_point_uuid] = incarnationTimepoint.checkpoint_time;
                }
                if (incarnationTimepoint.latest_timepoint_uuid.toString().length) {
                    tmpTimeInfo.start_time = incarnationTimepoint.latest_src_end_timepoint;
                }
                if (tmpTimeInfo.start_time < oracleTimepointList.start_time) {
                    tmpTimeInfo.start_time = oracleTimepointList.start_time;
                }
                oracleTimepointList.offline_time_range.push(tmpTimeInfo);
            }
        }
        // 不可恢复时间范围
        let lastEndTime = 0;
        let timepointChainList = Object.values(timepointChain);
        timepointChainList.sort((a, b) => {
            return a.start_time - b.start_time;
        });
        for (const chainInfo of timepointChainList) {
            if (lastEndTime !== 0) {
                if ((lastEndTime + 1) < chainInfo.start_time) {
                    let tmpNonsupportTimeInfo = {
                        start_time: lastEndTime,
                        end_time: chainInfo.full_timepoint.checkpoint_time,
                    };
                    oracleTimepointList.nonsupport_time_range.push(tmpNonsupportTimeInfo);
                }
            }
            lastEndTime = chainInfo.end_time;
        }
        console.log('Oracle nonsupport time range', oracleTimepointList.nonsupport_time_range)
        // 降序排序
        oracleTimepointList.incarnation_timepoint_list.sort((a, b) => {
            return b.src_end_time_point - a.src_end_time_point;
        });
        if (parseInt($('#pathType').val()) === PATH_TYPE_ENUM.FULL) {
            oracleTimepointList.select_timepoint = oracleTimepointList[0];
        } else {
            oracleTimepointList.select_timepoint = oracleTimepointList.incarnation_timepoint_list[0];
        }
        oracleTimepointList.old_select_timepoint = null;
    };

    /**
     * 初始化Oracle恢复时间轴
     */
    const initOracleRecoveryTimeRange = () => {
        let oracleRecoveryTimepointFlag = parseInt($('#oracleRecoveryTimepoint').val());
        if (oracleRecoveryTimepointFlag !== ORACLE_RECOVERY_TIMEPOINT_ENUM.TIME) {  // 如果不是指定时间，那么不渲染时间轴
            return;
        }
        let oracleRangeTimepointList = oracleTimepointList;
        let oracleRecoverySourceType = parseInt($('#oracleRecoverySource').val());
        if (oracleRecoverySourceType === ORACLE_RECOVERY_SOURCE_ENUM.SPECIFY_TASK) {
            oracleRangeTimepointList = oracleTimepointList.select_task_chain;
        }
        let startDatetime = getCurrentDatetimeStr(new Date(oracleRangeTimepointList.start_time * 1000));
        let endDatetime = getCurrentDatetimeStr(new Date(oracleRangeTimepointList.end_time * 1000));
        $('.oracleRecoveryTimeSelectWrapper .minTime').html(startDatetime);
        $('.oracleRecoveryTimeSelectWrapper .maxTime').html(endDatetime);

        if (oracleRecoveryTimeSlider !== null) {
            oracleRecoveryTimeSlider.destroy();
            oracleRecoveryTimeSlider = null;
        }
        oracleRecoveryTimeSlider = noUiSlider.create($('#oracleRecoveryTimeSelectRange').get(0), {
            start: [oracleRangeTimepointList.end_time],
            range: {
                min: oracleRangeTimepointList.start_time,
                max: oracleRangeTimepointList.end_time,
            },
            connect: 'lower',
            step: 1,
            behaviour: 'smooth-tap-steps',
            tooltips: false,
        })
        $('#oracleRecoveryTimeSelectTime').val(endDatetime);
        if (!judgeOracleRecoveryTimeNotInNonsupportRange(oracleRangeTimepointList.end_time, oracleRangeTimepointList)) {
            $('#oracleRecoveryTimeShowBackText').html(endDatetime + '(' + LANG.UI_PUBLIC_STORAGE_OFFLINE + ')');
        } else {
            $('#oracleRecoveryTimeShowBackText').html(endDatetime);
        }
        oracleRecoveryTime = null;
        registerOracleRecoveryRangeSliderEvent(oracleRangeTimepointList);
    };

    /**
     * 根据恢复备份链获取备份点
     */
    const getOracleRecoveryTimepointChain = () => {
        let oracleRecoveryBackupTimepointData = [];
        let timepointChainUuidList = oracleTimepointList.timepoint_chain.map(v => v.timepoint_uuid);
        let pathType = parseInt($('#pathType').val());
        let oracleMatchTimepointList = oracleTimepointList.timepoint_list;
        if (pathType === PATH_TYPE_ENUM.INCOMPLETE) {
            oracleMatchTimepointList = oracleTimepointList.incarnation_timepoint_list;
            let oracleRecoverySourceType = parseInt($('#oracleRecoverySource').val());
            if (oracleRecoverySourceType === ORACLE_RECOVERY_SOURCE_ENUM.SPECIFY_TASK) {
                oracleMatchTimepointList = oracleTimepointList.select_task_chain.timepoint_list;
            }
        }
        for (const timepointInfo of oracleMatchTimepointList) {
            if (timepointChainUuidList.includes(timepointInfo.time_point_uuid)) {
                oracleRecoveryBackupTimepointData.push(timepointInfo);
            }
        }
        let oracleRecoveryTimepointChainMap = {};
        // 先构建完备点
        for (const timepointInfo of oracleRecoveryBackupTimepointData) {
            if (timepointInfo.backup_mode === TIMEPOINT_TYPE_ENUM.FULL) {
                oracleRecoveryTimepointChainMap[timepointInfo.chain_uuid] = {
                    chain_uuid: timepointInfo.chain_uuid,
                    job_uuid: timepointInfo.job_uuid,
                    job_name: timepointInfo.job_name,
                    job_delete_flag: timepointInfo.job_delete_flag,
                    full_timepoint_info: timepointInfo,
                    full_timepoint_uuid: timepointInfo.time_point_uuid,
                    instance_name: timepointInfo.instance_name,
                    agent_uuid: timepointInfo.instance_name,
                    depend_timepoint_list: []
                };
            }
        }
        // 构建依赖点
        for (const timepointInfo of oracleRecoveryBackupTimepointData) {
            if (timepointInfo.backup_mode !== TIMEPOINT_TYPE_ENUM.FULL) {
                if (typeof oracleRecoveryTimepointChainMap[timepointInfo.chain_uuid] !== 'undefined') {
                    oracleRecoveryTimepointChainMap[timepointInfo.chain_uuid].depend_timepoint_list.push(timepointInfo);
                }
            }
        }
        let oracleRecoveryTimepointChain = Object.values(oracleRecoveryTimepointChainMap);
        oracleRecoveryTimepointChain.sort((a, b) => a.full_timepoint_info.src_end_time_point - b.full_timepoint_info.src_end_time_point);
        for (const index in oracleRecoveryTimepointChain) {
            oracleRecoveryTimepointChain[index].depend_timepoint_list.sort((a, b) => a.src_end_time_point - b.src_end_time_point);
        }
        return oracleRecoveryTimepointChain;
    };

    /**
     * 获取Oracle恢复备份点树配置
     */
    const getOracleRecoveryChainTreeSetting = () => {
        return {
            check: {
                enable: true,
                nocheckInherit: false,
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
                },
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                addDiyDom: (treeId, treeNode) => {
                    if (treeNode.eventtype === 'full') {
                        let aNodeObj = $(`#${treeNode.tId}_span`);
                        aNodeObj.css({
                            'white-space': `nowrap`,
                        });
                    }
                },
            },
            callback: {
                beforeClick: (treeId, treeNode) => {
                },
                onCheck: (ev, treeId, treeNode) => {
                },
            },
        };
    };

    /**
     * 获取Oracle恢复备份点树数据
     * @param drawEncryptPasswordInputFlag
     */
    const getOracleRecoveryBackupTimepointTreeData = (drawEncryptPasswordInputFlag = true) => {
        let nodes = {
            oracle_instance: {
                id: 'oracle',
                pId: 0,
                name: oracleTimepointList.recovery_timepoint_chain_data[0].instance_name,
                title: oracleTimepointList.recovery_timepoint_chain_data[0].instance_name,
                icon: './img/db/oracle.png',
                isParent: true,
                open: true,
                nocheck: true,
                eventtype: 'oracle',
            }
        };
        for (const chainInfo of oracleTimepointList.recovery_timepoint_chain_data) {
            if (typeof nodes[chainInfo.job_uuid] === 'undefined') {
                let name = chainInfo.job_name;
                if (chainInfo.job_delete_flag) {
                    name += '(' + LANG.UI_PUBLIC_TASK_DELETED + ')';
                }
                nodes[chainInfo.job_uuid] = {
                    id: 'oracle_' + chainInfo.job_uuid,
                    pId: 'oracle',
                    name,
                    title: name,
                    icon: './img/platform/flag.png',
                    isParent: true,
                    open: true,
                    nocheck: true,
                    eventtype: 'task'
                };
            }
            let name = chainInfo.full_timepoint_info.time_point + '(' + LANG.UI_DATA_TYPE_FULL + ')';
            if (chainInfo.full_timepoint_info.is_encrypted) {  // 存储加密
                if (!chainInfo.full_timepoint_info.config.password_auto_flag && drawEncryptPasswordInputFlag) {  // 手动加密
                    name += `<i class="fa fa-lock"></i>`;
                    name += `
                    <div class="oracleEncryptPasswordWrapper">
                        <label class="control-label" for="oracle_encrypt_password_${chainInfo.full_timepoint_uuid}">${$('.passwordlabel').html()}: </label>
                        <input type="password" class="form-control oracleEncryptPassword" data-uuid="${chainInfo.full_timepoint_uuid}"
                            id="oracle_encrypt_password_${chainInfo.full_timepoint_uuid}" maxlength="256"
                            oninput="value=value.replace(/[\\u4E00-\\u9FA5]|[\\uFE30-\\uFFA0]|\\s+/g,'')"/>
                        <button type="button" class="btn btn-link show-encrypt-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                            <i class="viconfont vicon-a-lujing8232"></i>
                        </button>
                    </div>
                    `;
                }
            }
            nodes[chainInfo.full_timepoint_uuid] = {
                id: 'oracle_' + chainInfo.job_uuid + '_' + chainInfo.full_timepoint_uuid,
                pId: 'oracle_' + chainInfo.job_uuid,
                name,
                title: chainInfo.full_timepoint_info.time_point + '(' + LANG.UI_DATA_TYPE_FULL + ')',
                icon: './img/platform/timepoint-f.png',
                isParent: chainInfo.depend_timepoint_list.length > 0,
                open: true,
                nocheck: true,
                eventtype: 'full',
                chain_uuid: chainInfo.chain_uuid,
                job_uuid: chainInfo.job_uuid,
                job_name: chainInfo.job_name,
                job_delete_flag: chainInfo.job_delete_flag,
                timepoint_uuid: chainInfo.full_timepoint_uuid,
                timepoint_info: chainInfo.full_timepoint_info,
                depend_timepoint_list: chainInfo.depend_timepoint_list,
            };
            for (const dependTimepointInfo of chainInfo.depend_timepoint_list) {
                let name = dependTimepointInfo.time_point;
                let icon = '';
                switch (parseInt(dependTimepointInfo.backup_mode)) {
                    case TIMEPOINT_TYPE_ENUM.INCR:
                        icon = './img/platform/timepoint-i.png';
                        name += '(' + LANG.UI_DATA_TYPE_INCR + ')';
                        break;
                    case TIMEPOINT_TYPE_ENUM.DIFF:
                        icon = './img/platform/timepoint-d.png';
                        name += '(' + LANG.UI_DATA_TYPE_DIFF + ')';
                        break;
                    case TIMEPOINT_TYPE_ENUM.LOG:
                        icon = './img/platform/timepoint.png';
                        name += '(' + LANG.UI_DATA_TYPE_ARCHIVELOG + ')';
                        break;
                }
                nodes[dependTimepointInfo.time_point_uuid] = {
                    id: 'oracle_' + chainInfo.job_uuid + '_' + chainInfo.full_timepoint_uuid + '_' + dependTimepointInfo.time_point_uuid,
                    pId: 'oracle_' + chainInfo.job_uuid + '_' + chainInfo.full_timepoint_uuid,
                    name,
                    title: name,
                    icon,
                    isParent: false,
                    open: true,
                    nocheck: true,
                    eventtype: 'depend',
                    timepoint_uuid: dependTimepointInfo.time_point_uuid,
                    timepoint_info: dependTimepointInfo,
                };
            }
        }
        return Object.values(nodes);
    };

    /**
     * 初始化Oracle恢复备份点树
     */
    const initOracleRecoveryBackupTimepointTree = () => {
        $('#oracleRecoveryBackupTimepointTree').show();
        $('#oracleRecoveryBackupTimepointNoTimepointTips').hide();

        oracleRecoveryChainTree = $.fn.zTree.init(
            $('#oracleRecoveryBackupTimepointTree'),
            getOracleRecoveryChainTreeSetting(),
            getOracleRecoveryBackupTimepointTreeData()
        );
    };

    /**
     * 根据恢复时间初始化Oracle任务链
     */
    const initOracleTimepointChainByRecoveryTime = () => {
        oracleTimepointList.timepoint_chain = [];
        oracleTimepointList.recovery_timepoint_chain_data = [];
        $('.oracleTimepointStorageOfflineDiv').hide();  // 隐藏存储离线提示信息
        $('.oracleTimepointRangeNonsupportDiv').hide();  // 隐藏不可恢复信息
        $('.oracleRecoveryBackupTimepointNoTimepointTips').hide();  // 隐藏没有备份点提示信息
        let recoveryTime = 0;
        let jobUuid = '';
        // 恢复恢复分支
        let resetlogsTime = 0;
        let dbIncarnation = 0;
        let pathType = parseInt($('#pathType').val());
        if (pathType === PATH_TYPE_ENUM.INCOMPLETE) {
            let oracleRecoveryTimepointFlag = parseInt($('#oracleRecoveryTimepoint').val());
            if (oracleRecoveryTimepointFlag === ORACLE_RECOVERY_TIMEPOINT_ENUM.NEWEST) {
                for (const incarnationTimepointInfo of oracleTimepointList.incarnation_timepoint_list) {
                    if (recoveryTime === 0) {
                        recoveryTime = incarnationTimepointInfo.src_end_time_point;
                    } else {
                        if (recoveryTime < incarnationTimepointInfo.src_end_time_point) {
                            recoveryTime = incarnationTimepointInfo.src_end_time_point;
                        }
                    }
                }
            } else {
                // 获取恢复时间
                oracleRecoveryTime = $('#oracleRecoveryTimeShowBackText').html();
                recoveryTime = convertToUnixTimestamp(oracleRecoveryTime);
                // 获取任务恢复来源
                let oracleRecoverySourceType = parseInt($('#oracleRecoverySource').val());
                let oracleRangeTimepointList = oracleTimepointList;
                if (oracleRecoverySourceType === ORACLE_RECOVERY_SOURCE_ENUM.SPECIFY_TASK) {
                    jobUuid = oracleTimepointList.select_task_chain.job_uuid;
                    oracleRangeTimepointList = oracleTimepointList.select_task_chain;
                }
                // 判断恢复时间是否处于存储离线区域
                if (!judgeOracleRecoveryTimeNotInNonsupportRange(recoveryTime, oracleRangeTimepointList)) {
                    $('.oracleTimepointStorageOfflineDiv').show();
                    $('.oracleTimepointStorageOfflineDiv .alert-danger').show();
                    $('.oracleRecoveryContentDiv').hide();
                    $('.oracleRecoveryBackupTimepointDiv').hide();  // 隐藏恢复备份点
                    return false;
                }
                // 判断恢复时间是否处于不可恢复区域
                if (!judgeOracleRecoveryTimeNotInNonsupportRange(recoveryTime, oracleRangeTimepointList, 'nonsupport_time_range')) {
                    $('.oracleTimepointRangeNonsupportDiv').show();
                    $('.oracleTimepointRangeNonsupportDiv .alert-danger').show();
                    $('.oracleRecoveryContentDiv').hide();
                    $('.oracleRecoveryBackupTimepointDiv').hide();  // 隐藏恢复备份点
                    return false;
                }
            }
            // 恢复恢复分支
            resetlogsTime = parseInt($('#oracleRecoveryIncarnation').val());
            dbIncarnation = parseInt($('#oracleRecoveryIncarnation option:selected').data('db-incarnation'));
            if (isNaN(recoveryTime)) {
                return;
            }
        }
        // 获取恢复源实例信息
        let firstNode = Object.values(recoverySource.time_point_list)[0];
        let sourceInstanceInfoList = [{
            instance_name: firstNode.instance_name,
            agent_uuid: firstNode.agent_uuid,
        }];
        if (firstNode.eventtype === 'cluster') {
            sourceInstanceInfoList = Object.values(firstNode.backup_instance_list).map(instanceInfo => {
                return {
                    instance_name: instanceInfo.instance_name,
                    agent_uuid: instanceInfo.agent_uuid,
                };
            });
        }
        let reqData = {
            source_instance_info_list: sourceInstanceInfoList,
            db_incarnation: dbIncarnation,
            resetlogs_time: resetlogsTime,
            job_uuid: jobUuid,
            recovery_time: recoveryTime,
            path_type: pathType,
            storage_uuid: $('#timepointStorageSelect').val(),
        };
        Metronic.blockUI({target: '#dbrecovercontent', animate: true});
        pAjaxRequest(reqData, `/api/v1/db/oracle/timepoint_chain`, `GET`, res => {
            Metronic.unblockUI('#dbrecovercontent');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_RECOVERY_DB_PROTECT_CREATE_TITLE, res.message);
                return;
            }

            oracleTimepointList.timepoint_chain = res.data.timepoint_chain;
            oracleTimepointList.recovery_timepoint_chain_data = getOracleRecoveryTimepointChain();
            if (!oracleTimepointList.recovery_timepoint_chain_data.length) {
                $('#oracleRecoveryBackupTimepointTree').hide();
                $('#oracleRecoveryBackupTimepointNoTimepointTips').show();
                $('#oracleRecoveryBackupTimepointNoTimepointTips .alert-danger').show();
                $('.oracleRecoveryBackupTimepointDiv').show();
                return false;
            }
            initOracleRecoveryBackupTimepointTree();
            doOracleShowStep3Item(parseInt($('#pathType').val()));
        });
    };

    /**
     * 获取Oracle任务链
     * @param timepointList
     * @return {[]}
     */
    const getOracleTaskChain = timepointList => {
        timepointList = JSON.parse(JSON.stringify(timepointList));
        // 升序排序
        timepointList.sort((a, b) => a.src_end_time_point - b.src_end_time_point);
        // 构建备份链
        let timepointChain = {};
        for (const row of timepointList) {
            if (typeof timepointChain[row.chain_uuid] === 'undefined') {
                timepointChain[row.chain_uuid] = {
                    chain_uuid: row.chain_uuid,
                    full_timepoint: null,
                    start_time: row.src_start_time_point,
                    end_time: row.src_end_time_point,
                    timepoint_list: [],
                }
            }
            if (parseInt(row.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {
                timepointChain[row.chain_uuid].full_timepoint = row;
            } else {
                timepointChain[row.chain_uuid].timepoint_list.push(row);
            }
            if (row.src_start_time_point < timepointChain[row.chain_uuid].start_time) {
                timepointChain[row.chain_uuid].start_time = row.src_start_time_point;
            }
            if (row.src_end_time_point > timepointChain[row.chain_uuid].end_time) {
                timepointChain[row.chain_uuid].end_time = row.src_end_time_point;
            }
        }
        let taskChain = {};
        for (const row of timepointList) {
            if (typeof taskChain[row.job_uuid] === 'undefined') {
                if (parseInt(row.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {
                    taskChain[row.job_uuid] = {
                        job_uuid: row.job_uuid,
                        job_name: row.job_name,
                        job_delete_flag: row.job_delete_flag,
                        start_time: row.checkpoint_time,
                        end_time: row.src_end_time_point,
                        instance_name: row.instance_name,
                        timepoint_list: [row],
                        offline_time_range: [],
                        nonsupport_time_range: [],
                        timepoint_chain_list: [],
                    };
                    for (const chainUuid in timepointChain) {
                        let chainInfo = timepointChain[chainUuid];
                        if (chainInfo.full_timepoint && chainInfo.full_timepoint.time_point_uuid === row.time_point_uuid) {
                            taskChain[row.job_uuid].timepoint_chain_list.push(chainInfo);
                        }
                    }
                } else {
                    if (typeof taskChain[row.depend_task_uuid] !== 'undefined') {
                        taskChain[row.depend_task_uuid].timepoint_list.push(row);
                        if (taskChain[row.depend_task_uuid].end_time < row.src_end_time_point) {
                            taskChain[row.depend_task_uuid].end_time = row.src_end_time_point;
                        }
                    } else {
                        console.log('error: ', row);
                    }
                }
            } else {
                taskChain[row.job_uuid].timepoint_list.push(row);
                taskChain[row.job_uuid].job_name = row.job_name;
                if (taskChain[row.job_uuid].end_time < row.src_end_time_point) {
                    taskChain[row.job_uuid].end_time = row.src_end_time_point;
                }
                if (parseInt(row.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {
                    for (const chainUuid in timepointChain) {
                        let chainInfo = timepointChain[chainUuid];
                        if (chainInfo.full_timepoint && chainInfo.full_timepoint.time_point_uuid === row.time_point_uuid) {
                            taskChain[row.job_uuid].timepoint_chain_list.push(chainInfo);
                        }
                    }
                }
            }
        }
        // 存储离线处理
        for (const taskUuid in taskChain) {
            let taskChainInfo = taskChain[taskUuid];
            for (const timepointInfo of taskChainInfo.timepoint_list) {
                // 存储离线的区域
                // 存储离线
                if (parseInt(timepointInfo.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                    let tmpTimeInfo = {
                        start_time: timepointInfo.src_start_time_point,
                        end_time: timepointInfo.src_end_time_point,
                    };
                    if (timepointInfo.latest_timepoint_uuid.toString().length) {
                        tmpTimeInfo.start_time = timepointInfo.latest_src_end_timepoint;
                    }
                    if (tmpTimeInfo.start_time < taskChainInfo.start_time) {
                        tmpTimeInfo.start_time = taskChainInfo.start_time;
                    }
                    taskChain[taskUuid].offline_time_range.push(tmpTimeInfo);
                }
            }
        }
        // 不可恢复时间范围
        for (const taskUuid in taskChain) {
            let taskChainInfo = taskChain[taskUuid];
            let lastEndTime = 0;
            taskChainInfo.timepoint_chain_list.sort((a, b) => a.start_time - b.start_time);
            for (const chainInfo of taskChainInfo.timepoint_chain_list) {
                if (lastEndTime !== 0) {
                    if ((lastEndTime + 1) < chainInfo.start_time) {
                        let tmpNonsupportTimeInfo = {
                            start_time: lastEndTime,
                            end_time: chainInfo.full_timepoint.checkpoint_time,
                        };
                        taskChain[taskUuid].nonsupport_time_range.push(tmpNonsupportTimeInfo);
                    }
                }
                lastEndTime = chainInfo.end_time;
            }
        }
        return taskChain;
    };

    /**
     * 获取Oracle任务树的设置
     * @return {Object}
     */
    const getOracleTaskTreeSetting = () => {
        return {
            check: {
                enable: true,
                nocheckInherit: false,
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
                },
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
            },
            callback: {
                beforeClick: (treeId, treeNode) => {
                    $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
                },
                onCheck: (ev, treeId, treeNode) => {
                    if (!treeNode.checked) {
                        $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, true);
                    } else {
                        oracleTimepointList.select_task_chain = oracleTimepointList.task_chain_map[treeNode.job_uuid]
                        oracleTimepointList.timepoint_chain = [];
                        $.fn.zTree.getZTreeObj(treeId).checkAllNodes(false);
                        $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, true);
                        // 初始化恢复时间
                        initOracleRecoveryTimeRange();
                        initOracleTimepointChainByRecoveryTime();
                    }
                },
            },
        };
    };

    /**
     * 初始化Oracle任务树
     * @param timepointList
     */
    const initOracleTaskTree = (timepointList) => {
        let oracleTaskChain = Object.values(oracleTimepointList.task_chain_map);
        oracleTaskChain.sort((a, b) => b.end_time - a.end_time);
        let taskData = {
            oracle_instance: {
                id: 'oracle',
                pId: 0,
                name: oracleTaskChain[0].instance_name,
                title: oracleTaskChain[0].instance_name,
                icon: './img/db/oracle.png',
                isParent: true,
                open: true,
                nocheck: true,
                eventtype: 'oracle'
            },
        };
        for (const row of oracleTaskChain) {
            let name = row.job_name;
            if (row.job_delete_flag) {
                name += '(' + LANG.UI_PUBLIC_TASK_DELETED + ')';
            }
            // 恢复时间范围
            name += `(${LANG.UI_DB_RECOVERY_ORACLE_RECOVERY_TIME_RANGE}: `
                + getCurrentDatetimeStr(new Date(row.start_time * 1000)) + ' ~ '
                + getCurrentDatetimeStr(new Date(row.end_time * 1000)) + ')';
            taskData[row.job_uuid] = {
                id: 'oracle_' + row.job_uuid,
                pId: 'oracle',
                name,
                title: name,
                icon: './img/platform/flag.png',
                isParent: false,
                open: true,
                nocheck: false,
                eventtype: 'task',
                job_uuid: row.job_uuid,
                job_name: row.job_name,
                timepoint_list: row.timepoint_list,
                offline_time_range: row.offline_time_range,
                nonsupport_time_range: row.nonsupport_time_range,
                start_time: row.start_time,
                end_time: row.end_time,
                instance_name: row.instance_name,
            };
            for (const timepoint of row.timepoint_list) {
                if (timepoint.depend_task_uuid === row.job_uuid && timepoint.job_uuid !== row.job_uuid) {
                    taskData[row.job_uuid].isParent = true;
                    taskData[row.job_uuid + '_' + timepoint.job_uuid] = {
                        id: 'oracle_' + row.job_uuid + '_' + timepoint.job_uuid,
                        pId: 'oracle_' + row.job_uuid,
                        name: timepoint.job_name,
                        title: timepoint.job_name,
                        icon: './img/platform/flag.png',
                        isParent: false,
                        nocheck: true,
                        eventtype: 'sub_task'
                    };
                }
            }
        }
        oracleRecoveryTaskTree = $.fn.zTree.init($('#oracleRecoverySpecifyTaskTree'), getOracleTaskTreeSetting(), Object.values(taskData));
        let taskNodes = oracleRecoveryTaskTree.getNodesByParam('eventtype', 'task');
        if (taskNodes.length) {
            oracleTimepointList.select_task_chain = oracleTimepointList.task_chain_map[taskNodes[0].job_uuid]
            oracleTimepointList.timepoint_chain = [];
            oracleRecoveryTaskTree.checkNode(taskNodes[0], true);
        }
    };

    /**
     * 初始化Oracle恢复来源
     */
    const initOracleRecoverySource = () => {
        $('#oracleRecoverySource').val(ORACLE_RECOVERY_SOURCE_ENUM.ALL_TASK);
        // 初始化指定任务
        oracleTimepointList.task_chain_map = getOracleTaskChain(oracleTimepointList.incarnation_timepoint_list);
        oracleTimepointList.select_task_chain = [];
        oracleTimepointList.timepoint_chain = [];
        initOracleTaskTree();
    };

    /**
     * 构建Oracle备份点链
     */
    const buildOracleTimepointListChain = () => {
        let chainMap = {};
        for (const timepoint of oracleTimepointList.timepoint_list) {
            if (typeof chainMap[timepoint.chain_uuid] === 'undefined') {
                chainMap[timepoint.chain_uuid] = [];
            }
            chainMap[timepoint.chain_uuid].push(timepoint);
        }
        let chainList = [];
        for (const chainUuid in chainMap) {
            let timepointList = chainMap[chainUuid];
            let tmpChainItem = {
                chain_uuid: chainUuid,
                full_timepoint: null,
                depend_timepoint_list: [],
            };
            for (const timepoint of timepointList) {
                if (parseInt(timepoint.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {
                    tmpChainItem.full_timepoint = timepoint;
                } else {
                    tmpChainItem.depend_timepoint_list.push(timepoint);
                }
            }
            tmpChainItem.depend_timepoint_list.sort((a, b) => a.src_end_time_point - b.src_end_time_point);
            chainList.push(tmpChainItem);
        }
        chainList.sort((a, b) => a.full_timepoint.src_end_time_point - b.full_timepoint.src_end_time_point);
        return chainList;
    };

    /**
     * 初始化Oracle备份点
     */
    const initOracleTimepoint = () => {
        oracleTimepointList = {
            newest_timepoint: null,
            start_time: 0,
            end_time: 0,
            start_scn: 0,
            end_scn: 0,
            timepoint_list: [],
            timepoint_list_chain: [],
            select_timepoint: null,
            old_select_timepoint: null,
            latest_timepoint_uuid: '',
            resetlogs_time_list: {},
            resetlogs_time: null,
            db_incarnation: null,
            offline_time_range: [],
            incarnation_timepoint_list: [],
            nonsupport_time_range: [],
            task_chain_map: {},
            select_task_chain: [],
            timepoint_chain: [],  // 请求到的备份链（完全恢复、不完全恢复）
            recovery_timepoint_chain_data: [],
        }
        return new Promise((resolve, reject) => {
            let firstNode = Object.values(recoverySource.time_point_list)[0];
            let reqData = {
                agent_list: [{
                    cluster_flag: firstNode.cluster_flag,
                    cluster_uuid: firstNode.cluster_uuid,
                    instance_name: firstNode.instance_name,
                    agent_uuid: firstNode.agent_uuid,
                    db_name: '',
                }],
                db_type: CONF.DB_TYPE.ORACLE,
                with_copy_flag: true,
                with_copy_back_flag: 1,
                storage_uuid: $('#timepointStorageSelect').val(),
            };
            Metronic.blockUI({target: '#dbrecovercontent', animate: true});
            pAjaxRequest(reqData, `/api/v1/db/jobs/backup/time_point_list`, 'GET', res => {
                Metronic.unblockUI('#dbrecovercontent');
                if (!res.success) {
                    reject();
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, res.message);
                    return;
                }
                for (const row of res.data.rows) {
                    oracleTimepointList.resetlogs_time_list[row.resetlogs_time + '_' + row.db_incarnation] = {
                        resetlogs_time: parseInt(row.resetlogs_time),
                        db_incarnation: parseInt(row.db_incarnation),
                    };
                }
                oracleTimepointList.resetlogs_time_list = Object.values(oracleTimepointList.resetlogs_time_list);
                oracleTimepointList.resetlogs_time_list.sort((a, b) => {
                    if (b.resetlogs_time > a.resetlogs_time) {
                        return 1;
                    } else if (b.resetlogs_time < a.resetlogs_time) {
                        return -1;
                    } else {
                        if (b.db_incarnation > a.db_incarnation) {
                            return 1;
                        } else {
                            return -1;
                        }
                    }
                })
                oracleTimepointList.resetlogs_time = oracleTimepointList.resetlogs_time_list[0].resetlogs_time;
                oracleTimepointList.db_incarnation = oracleTimepointList.resetlogs_time_list[0].db_incarnation;

                oracleTimepointList.timepoint_list = res.data.rows;
                // 根据结束时间倒序排序
                oracleTimepointList.timepoint_list.sort((a, b) => {
                    return b.src_end_time_point - a.src_end_time_point;
                });
                oracleTimepointList.timepoint_list_chain = buildOracleTimepointListChain();
                initOracleTimepointSetByResetlogsTime(oracleTimepointList.resetlogs_time, oracleTimepointList.db_incarnation);
                let options = ``;
                for (const resetItem of oracleTimepointList.resetlogs_time_list) {
                    let selected = '';
                    let resetlogsDatetime = getCurrentDatetimeStr(new Date(resetItem.resetlogs_time * 1000));
                    if (
                        oracleTimepointList.resetlogs_time === resetItem.resetlogs_time &&
                        oracleTimepointList.db_incarnation === resetItem.db_incarnation
                    ) {
                        selected = 'selected';
                    }
                    options += `<option value="${resetItem.resetlogs_time}" data-db-incarnation="${resetItem.db_incarnation}" ${selected}>Incarnation#${resetItem.db_incarnation}, Resetlogs_time${resetlogsDatetime}</option>`;
                }
                $('#oracleRecoveryIncarnation').html(options);

                // 初始化恢复来源
                initOracleRecoverySource();
                resolve();
            });
        });
    };

    /**
     * 设置Oracle选择的存储是否包含最新备份点
     */
    const setOracleStorageHasNewestTimepointFlag = () => {
        return new Promise((resolve, reject) => {
            let firstNode = Object.values(recoverySource.time_point_list)[0];
            let reqData = {
                agent_list: [{
                    cluster_flag: firstNode.cluster_flag,
                    cluster_uuid: firstNode.cluster_uuid,
                    instance_name: firstNode.instance_name,
                    agent_uuid: firstNode.agent_uuid,
                    db_name: '',
                }],
                db_type: CONF.DB_TYPE.ORACLE,
                with_copy_flag: true,
                with_copy_back_flag: 1,
            };
            Metronic.blockUI({target: '#dbrecovercontent', animate: true});
            pAjaxRequest(reqData, `/api/v1/db/jobs/backup/time_point_list`, 'GET', res => {
                Metronic.unblockUI('#dbrecovercontent');
                if (!res.success) {
                    reject();
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, res.message);
                    return;
                }
                if (!res.data.rows.length) {
                    resolve();
                    return;
                }
                res.data.rows.sort((a, b) => {
                    return b.src_end_time_point - a.src_end_time_point;
                });
                let newestTimepoint = null;
                for (const row of res.data.rows) {
                    if (parseInt(row.job_type) !== CONF.TASK_TYPE.DB_BACKUP) {
                        continue;
                    }
                    if (newestTimepoint === null) {
                        newestTimepoint = row;
                    } else if (newestTimepoint.src_end_time_point < row.src_end_time_point) {
                        newestTimepoint = row;
                    }
                }

                if (!newestTimepoint) {
                    $(`#fullRecovery`).attr('disabled', true).attr('title', LANG.UI_DB_RECOVERY_ORACLE_NOT_FOUND_NEWEST_TIMEPOINT);
                } else {
                    if ($('#timepointStorageSelect').val().trim() === newestTimepoint.storage_info.storage_uuid) {
                        $(`#fullRecovery`).attr('disabled', false).removeAttr('title');
                    } else {
                        $(`#fullRecovery`).attr('disabled', true).attr('title', LANG.UI_DB_RECOVERY_ORACLE_NEWEST_TIMEPOINT_NOT_IN_CURRENT_STORAGE);
                    }
                }
                resolve();
            });
        });
    };

    //////////////////// 结束-Oracle配置 ////////////////////

    /**
     * 初始化SAP HANA资源限制
     */
    const initSAPHANAResourceLimit = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            // 不支持演练任务
            return;
        }
        let dbConfig = Object.values(recoveryMethod.db_config)[0];
        let nodeUuid = dbConfig.timepoint.storage_info.node_uuid;
        if (oldNodeUuidForResourceLimit !== null && nodeUuid === oldNodeUuidForResourceLimit) {
            // 表示之前已经初始化了，不需要再重复初始化
            return;
        }
        oldNodeUuidForResourceLimit = nodeUuid;
        initResourceLimit([nodeUuid]);
    };

    /**
     * 初始化SAP HANA的传输网络
     */
    const initSAPHANANetworkList = () => {
        if (!networkFlag || recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            // 不支持网络及恢复最新备份点
            return;
        }
        let dbConfig = Object.values(recoveryMethod.db_config)[0];
        let nodeUuid = dbConfig.timepoint.storage_info.node_uuid;
        if (oldNodeUuid !== null && nodeUuid === oldNodeUuid) {
            // 表示之前已经初始化了，不需要再重复初始化
            return;
        }
        oldNodeUuid = nodeUuid;
        $('#transferNetworkTree').transferNetwork({
            node_uuid: nodeUuid,
            onChange: function (transferNetworkNode) {
                if (!transferNetworkNode.checked) {
                    return;
                }
            }
        });
    };

    /**
     * 初始化SAP HANA备份点
     */
    const initSAPHANATimepoint = () => {
        return new Promise((resolve, reject) => {
            sapHanaTimepointList = {};
            let agentList = [];
            for (const timepointNode of Object.values(recoverySource.time_point_list)) {
                agentList.push({
                    cluster_flag: timepointNode.cluster_flag,
                    cluster_uuid: timepointNode.cluster_uuid,
                    instance_name: timepointNode.instance_name,
                    agent_uuid: timepointNode.agent_uuid,
                    job_uuid: timepointNode.job_uuid,
                    db_name: timepointNode.db_name,
                });
            }
            let reqData = {
                agent_list: agentList,
                db_type: recoverySource.db_type,
                with_copy_flag: true,
                with_copy_back_flag: 1,
                storage_uuid: $('#timepointStorageSelect').val(),
            };
            Metronic.blockUI({target: '#dbrecovercontent', animate: true});
            pAjaxRequest(reqData, `/api/v1/db/jobs/backup/time_point_list`, 'GET', res => {
                Metronic.unblockUI('#dbrecovercontent');
                if (!res.success) {
                    reject();
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, res.message);
                    return;
                }
                for (const timepointNode of Object.values(recoverySource.time_point_list)) {
                    if (timepointNode.eventtype !== 'db') {
                        continue;
                    }
                    let sapHanaKey = timepointNode.agent_uuid + '_' + timepointNode.instance_name + '_' + timepointNode.db_name;
                    if (timepointNode.cluster_flag) {  // 集群
                        sapHanaKey = timepointNode.cluster_uuid + '_' + timepointNode.instance_name + '_' + timepointNode.db_name;
                    }
                    if (typeof sapHanaTimepointList[sapHanaKey] === 'undefined') {
                        sapHanaTimepointList[sapHanaKey] = {
                            start_time: 0,
                            end_time: 0,
                            full_list: {}
                        };
                    }
                }
                // 构建完备点 & 构建数据库的开始时间和结束时间
                for (const row of res.data.rows) {
                    let sapHanaKey = row.agent_uuid + '_' + row.instance_name + '_' + row.db_name;
                    if (row.is_cluster) {  // 集群
                        sapHanaKey = row.cluster_uuid + '_' + row.instance_name + '_' + row.db_name;
                    }
                    if (typeof sapHanaTimepointList[sapHanaKey] !== 'undefined') {
                        if (parseInt(row.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {
                            sapHanaTimepointList[sapHanaKey].full_list[row.time_point_uuid] = {
                                start_time: row.time_point,
                                end_time: row.time_point,
                                timepoint: row,
                                point_list: [],
                            };
                        }
                        // 更新开始时间
                        if (sapHanaTimepointList[sapHanaKey].start_time === 0) {
                            sapHanaTimepointList[sapHanaKey].start_time = row.time_point;
                        } else {
                            let currentStartTimestamp = convertToUnixTimestamp(sapHanaTimepointList[sapHanaKey].start_time);
                            let startTimestamp = convertToUnixTimestamp(row.time_point);
                            if (startTimestamp < currentStartTimestamp) {
                                sapHanaTimepointList[sapHanaKey].start_time = row.time_point;
                            }
                        }
                        // 更新结束时间
                        if (sapHanaTimepointList[sapHanaKey].end_time === 0) {
                            sapHanaTimepointList[sapHanaKey].end_time = row.time_point;
                        } else {
                            let currentEndTimestamp = convertToUnixTimestamp(sapHanaTimepointList[sapHanaKey].end_time);
                            let endTimestamp = convertToUnixTimestamp(row.time_point);
                            if (endTimestamp > currentEndTimestamp) {
                                sapHanaTimepointList[sapHanaKey].end_time = row.time_point;
                            }
                        }
                    }
                }
                // 构建其他备份点
                for (const row of res.data.rows) {
                    let sapHanaKey = row.agent_uuid + '_' + row.instance_name + '_' + row.db_name;
                    if (row.is_cluster) {  // 集群
                        sapHanaKey = row.cluster_uuid + '_' + row.instance_name + '_' + row.db_name;
                    }
                    if (typeof sapHanaTimepointList[sapHanaKey] !== 'undefined') {
                        if (parseInt(row.backup_mode) !== TIMEPOINT_TYPE_ENUM.FULL) {
                            if (typeof sapHanaTimepointList[sapHanaKey].full_list[row.full_time_point_uuid] === 'undefined') {
                                // sap hana备份链可能断开（日志点可能没有依赖其他备份点）
                                continue;
                            }
                            // 更新结束时间
                            let currentEndTimestamp = convertToUnixTimestamp(
                                sapHanaTimepointList[sapHanaKey].full_list[row.full_time_point_uuid].end_time
                            );
                            let endTimestamp = convertToUnixTimestamp(row.time_point);
                            if (endTimestamp > currentEndTimestamp) {
                                sapHanaTimepointList[sapHanaKey].full_list[row.full_time_point_uuid].end_time = row.time_point;
                            }
                            sapHanaTimepointList[sapHanaKey].full_list[row.full_time_point_uuid].point_list.push(row);
                        }
                    }
                }
                resolve();
            });
        });
    };

    /**
     * 获取SAP HANA备份点
     * @return {Object}
     */
    const getSAPHANATimepoint = () => {
        let ret = {};
        for (const timepointNode of Object.values(recoverySource.time_point_list)) {
            if (timepointNode.eventtype !== 'db') {
                continue;
            }
            let sapHanaKey = timepointNode.agent_uuid + '_' + timepointNode.instance_name + '_' + timepointNode.db_name;
            if (timepointNode.cluster_flag) {  // 集群
                sapHanaKey = timepointNode.cluster_uuid + '_' + timepointNode.instance_name + '_' + timepointNode.db_name;
            }
            let dbStartTime = convertToUnixTimestamp(sapHanaTimepointList[sapHanaKey].start_time);
            dbStartTime = dbStartTime + 2;  // #22898，开始时间需要加2秒才能正常恢复
            let dbEndTime = convertToUnixTimestamp(sapHanaTimepointList[sapHanaKey].end_time)
            dbEndTime = dbEndTime + 1;  // SAP HANA单个数据库的结束时间要加1秒，否则会因为自身原因恢复失败
            // #23590: 开始时间必小于结束时间
            if (dbStartTime >= dbEndTime) {
                dbStartTime -= 2;
            }
            dbStartTime = getCurrentDatetimeStr(new Date(dbStartTime * 1000));
            dbEndTime = getCurrentDatetimeStr(new Date(dbEndTime * 1000));
            ret[sapHanaKey] = {
                timepoint: null,
                timepoint_list: [],
                db_start_time: dbStartTime,
                db_end_time: dbEndTime,
                point_start_time: '',
                point_end_time: '',
            };
        }

        let sapHanaRecoveryTimeFlag = parseInt($('#sapHanaRecoveryTime').val());
        switch (sapHanaRecoveryTimeFlag) {
            case RECOVERY_TIME_FLAG_ENUM.NEWEST:  // 最新备份点
            case RECOVERY_TIME_FLAG_ENUM.TIME:  // 选择时间范围，默认选择最后点
            case RECOVERY_TIME_FLAG_ENUM.TIMEPOINT:  // 选择备份点恢复
                for (const timepointNode of Object.values(recoverySource.time_point_list)) {
                    if (timepointNode.eventtype !== 'db') {
                        continue;
                    }
                    let sapHanaKey = timepointNode.agent_uuid + '_' + timepointNode.instance_name + '_' + timepointNode.db_name;
                    if (timepointNode.cluster_flag) {  // 集群
                        sapHanaKey = timepointNode.cluster_uuid + '_' + timepointNode.instance_name + '_' + timepointNode.db_name;
                    }
                    let pointList = sapHanaTimepointList[sapHanaKey].full_list;
                    for (const fullItem of Object.values(pointList)) {
                        ret[sapHanaKey].timepoint_list.push(fullItem.timepoint);
                    }
                    // 对时间排序
                    ret[sapHanaKey].timepoint_list.sort((a, b) => {
                        return convertToUnixTimestamp(b.time_point) - convertToUnixTimestamp(a.time_point);
                    });
                    let timepoint = ret[sapHanaKey].timepoint_list[0];
                    ret[sapHanaKey].timepoint = timepoint;
                    ret[sapHanaKey].point_start_time = timepoint.start_time;
                    ret[sapHanaKey].point_end_time = timepoint.end_time;
                }
                break;
        }
        return ret;
    };

    /**
     * 获取SAP HANA选择的备份点
     */
    const getSAPHANASelectTimepoint = () => {
        let sapHanaRecoveryTimeFlag = parseInt($('#sapHanaRecoveryTime').val());
        for (const nodeKey in recoveryMethod.db_config) {
            let dbConfig = recoveryMethod.db_config[nodeKey];
            switch (sapHanaRecoveryTimeFlag) {
                case RECOVERY_TIME_FLAG_ENUM.NEWEST:
                case RECOVERY_TIME_FLAG_ENUM.TIME:
                    break;
                case RECOVERY_TIME_FLAG_ENUM.TIMEPOINT:
                    let selectedTimepointUuid = $(`#select_timepoint_${nodeKey}`).val();
                    if (!selectedTimepointUuid) {
                        recoveryMethod.db_config[nodeKey].timepoint = null;
                    } else {
                        for (const timepoint of dbConfig.timepoint_list) {
                            if (timepoint.time_point_uuid === selectedTimepointUuid) {
                                recoveryMethod.db_config[nodeKey].timepoint = timepoint;
                                break;
                            }
                        }
                    }
                    break;
            }
        }
    };

    /**
     * 初始化SAP HANA完整性策略
     */
    const initSAPHANACompleteStrategy = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            return;
        }
        let disableIntegrityCheckFlag = true;
        for (const nodeKey in recoveryMethod.db_config) {
            let dbConfig = recoveryMethod.db_config[nodeKey];
            if (!dbConfig.timepoint) {
                break;
            }
            if (dbConfig.timepoint.integrity_check_flag) {
                disableIntegrityCheckFlag = false;
                break;
            }
        }
        initCompleteCheckStrategyByTimepoint({integrity_check_flag: !disableIntegrityCheckFlag});
    };

    /**
     * 设置SAP HANA的磁带配置
     */
    const setSAPHANATapeConfig = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            return;
        }
        for (const nodeKey in recoveryMethod.db_config) {
            let dbConfig = recoveryMethod.db_config[nodeKey];
            if (!dbConfig.timepoint) {
                break;
            }
            storage_type = parseInt(dbConfig.timepoint.storage_info.storage_type);
            if (storage_type === CONF.BD_STORAGE_TYPE.TAPE || drillStorageType === CONF.BD_STORAGE_TYPE.TAPE) {
                break;
            }
        }
        setVisibleForTape();
    };

    /**
     * 初始化SAP HANA步骤3配置
     * @param pathType
     */
    var doSAPHANAShowStep3Item = function (pathType) {
        $('.logdateDiv').hide();  // 隐藏公用的回滚时间
        $('.encryptDiv').hide();  // 隐藏公用的数据加密密码

        let dbTimepointList = null;
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            dbTimepointList = getSAPHANATimepoint();
        }

        recoveryMethod.db_config = {};
        for (const nodeKey in recoverySource.time_point_list) { // 初始化恢复方式
            let selectedNode = recoverySource.time_point_list[nodeKey];
            if (selectedNode.eventtype !== 'db') {
                continue;
            }
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                let sapHanaKey = selectedNode.agent_uuid + '_' + selectedNode.instance_name + '_' + selectedNode.db_name;
                if (selectedNode.cluster_flag) {  // 集群
                    sapHanaKey = selectedNode.cluster_uuid + '_' + selectedNode.instance_name + '_' + selectedNode.db_name;
                }
                recoveryMethod.db_config[nodeKey] = {
                    name: selectedNode.name,
                    encrypt_password: '',
                    timepoint: dbTimepointList[sapHanaKey].timepoint,
                    timepoint_list: dbTimepointList[sapHanaKey].timepoint_list,
                    pick_time: dbTimepointList[sapHanaKey].db_end_time,
                    db_start_time: dbTimepointList[sapHanaKey].db_start_time,
                    db_end_time: dbTimepointList[sapHanaKey].db_end_time,
                    point_start_time: dbTimepointList[sapHanaKey].point_start_time,
                    point_end_time: dbTimepointList[sapHanaKey].point_end_time,
                    dir_path: selectedNode.dir_path,
                    initialize_log_area: false,
                    new_db_name: '',
                    db_uuid: '',
                };
            } else {
                recoveryMethod.db_config[nodeKey] = {
                    name: selectedNode.name,
                    encrypt_password: '',
                    timepoint: null,
                    timepoint_list: [],
                    pick_time: '',
                    db_start_time: '',
                    db_end_time: '',
                    point_start_time: '',
                    point_end_time: '',
                    dir_path: selectedNode.dir_path,
                    initialize_log_area: false,
                    new_db_name: '',
                    db_uuid: selectedNode.db_uuid,
                };
            }
        }

        $('.timer-recovery-newest-tips').hide();
        $('.sapHanaTimepointStorageOfflineDiv').hide();
        if (pathType === PATH_TYPE_ENUM.COVER) {
            $('.multiDbConfig').show();
            $('.multiDbConfigHead').hide();
            $('.multiDbConfigOldDb').show();        // 原数据库名
            $('.multiDbConfigInitLogArea').show();  // 初始化日志
            $('.multiDbConfigEncrypt').show();      // 数据加密密码
            $('.multiDbConfigTimepoint').show();    // 备份点
            setMultiDbConfigAccordion();
            // 初始化传输网络
            initSAPHANANetworkList();
            initSAPHANAResourceLimit();
        } else if (pathType === PATH_TYPE_ENUM.CREATE) {
            $('.multiDbConfig').show();
            $('.multiDbConfigHead').hide();
            $('.multiDbConfigOldDb').show();        // 原数据库名
            $('.multiDbConfigNewDb').show();        // 新数据库名
            $('.multiDbConfigInitLogArea').show();  // 初始化日志
            $('.multiDbConfigEncrypt').show();      // 数据加密密码
            $('.multiDbConfigTimepoint').show();    // 备份点
            setMultiDbConfigAccordion();
            // 初始化传输网络
            initSAPHANANetworkList();
            initSAPHANAResourceLimit();
        }
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {  // 定时恢复复最新点没有备份点、加密密码
            $('.multiDbConfigEncrypt').hide();      // 数据加密密码
            $('.multiDbConfigTimepoint').hide();    // 备份点
            $('.multiDbConfigTime').hide();   // 选择时间
            if (pathType === PATH_TYPE_ENUM.CREATE) {
                $('.timer-recovery-newest-tips').show();
                $('#timerRecoveryNewestTips').html(LANG.UI_DB_RECOVERY_SAPHANA_RECOVERY_NEWEST_TIPS1);
            }
        } else {
            getSAPHANASelectTimepoint();
        }
        let recoverySystemdbFlag = false;
        for (const nodeKey in recoverySource.time_point_list) {
            let selectedNode = recoverySource.time_point_list[nodeKey];
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                if (selectedNode.eventtype !== 'db') {
                    continue;
                }
            }
            if (selectedNode['name'].toUpperCase() === 'SYSTEMDB') {
                recoverySystemdbFlag = true;
                break;
            }
        }
        if (recoverySystemdbFlag) {
            $('#pathType').attr('disabled', 'disabled');
            $('.timer-recovery-newest-tips').show();
            $('#timerRecoveryNewestTips').html(LANG.UI_DB_RECOVERY_SAPHANA_RECOVERY_SYSTEMDB_TIPS1);
        }
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            // 存储离线
            for (const nodeKey in recoveryMethod.db_config) {
                let dbConfig = recoveryMethod.db_config[nodeKey];
                if (!dbConfig.timepoint || parseInt(dbConfig.timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                    $('.sapHanaTimepointStorageOfflineDiv').show();
                    $('.sapHanaTimepointStorageOfflineDiv .alert-danger').show();
                    break;
                }
            }
            // 完整性策略
            initSAPHANACompleteStrategy();
            setSAPHANATapeConfig();
        }
    }

    /**
     * 控制加密显示
     */
    var showEncrypted = function () {
        // 选择第一个备份点，非SQL server类别的数据库只能有一个备份点
        let firstNode = Object.values(recoverySource.time_point_list)[0];
        if (firstNode.is_encrypted && !firstNode.config.password_auto_flag) {
            // 开启压缩加密，非自动生成密码
            $('.encryptDiv').show();
        } else {
            $('.encryptDiv').hide();
        }
    }

    //添加限速策略
    var speedSubmit = function () {
        var info = {};
        var des = '';
        info.mode = parseInt($('#speedModeType').val());
        var speedUnit = getSpeedUnit();
        var speedNum = parseInt($('#speedSpinnerNumInput').val());
        if (!speedNum || speedNum <= 0) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_BACKUP_SPEED_LIMIT_TIPS);
            return false;
        }
        var unit = $('#unit').find('option:selected').text();
        var liId = getUuid();
        info.uuid = liId;
        info.value = speedNum * speedUnit;
        info.speednum = speedNum;
        info.unit = unit;
        if (info.mode === 1) {
            var strategyConfig = $('#speedstrategy').getSpeedStrategyConfig();
            info.type = strategyConfig.speedInfo.type;
            info.startTime = strategyConfig.speedInfo.startTime;
            info.endTime = strategyConfig.speedInfo.endTime;
            info.days = strategyConfig.speedInfo.days;
            info.des = strategyConfig.speedInfo.des + ', ' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE + ':' + speedNum + unit;
            des +=
                '<li class="list-group-item popovers speedTips list-group-item__speed" id="speed' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + info.des + '">' +
                '<div class="col1">' +
                '<div class="cont ">' +
                '<div class="cont-col1"></div>' +
                '<div class="cont-col2">' +
                '<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col2  pull-right delete-list">' +
                '<a class="del' + liId + '" >' +
                '<div class="label label-sm label-danger" style="padding:0;">' +
                '<i class="viconfont vicon-cuowu"></i>' +
                '</div>' +
                '</a>' +
                '</div>' +
                '</li>';
        } else {
            if (!checkSimpleForever(info.mode)) {
                UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD_FOREVER_TIPS);
                return false;
            }
            info.type = 4;
            info.startTime = '';
            info.endTime = '';
            info.days = [];
            info.des = LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER + ', ' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE + ':' + speedNum + unit;
            des +=
                '<li class="list-group-item popovers speedTips list-group-item__speed" id="speed' + liId + '"  data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + info.des + '">' +
                '<div class="col1">' +
                '<div class="cont ">' +
                '<div class="cont-col1"></div>' +
                '<div class="cont-col2">' +
                '<div class="desc list-one"> ' + info.des + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col2  pull-right delete-list">' +
                '<a class="del' + liId + '" >' +
                '<div class="label label-sm label-danger" style="padding:0;">' +
                '<i class="viconfont vicon-cuowu"></i>' +
                '</div>' +
                '</a>' +
                '</div>' +
                '</li>';
        }
        //检测结束时间是否大于开始时间
        if (!checkTime(info.startTime, info.endTime)) return;
        $('#speedList').append(des);
        $('.speedTips').popover(); //初始化tips
        $('.del' + liId).on('click', function () {
            $('.popover.in').remove();
            $('#speed' + liId).remove();
            for (var i = 0; i < speedList.length; i++) {
                if (liId === speedList[i].uuid) {
                    speedList.splice($.inArray(speedList[i], speedList), 1);
                }
            }
            initSpeedStrategyDes();
        });
        speedList.push(info);
        $('#speedlimitModal').modal('hide');
        initSpeedStrategyDes();
    }

    var checkTime = function (start, end) {
        var startnum = new Date("1970-01-01" + " " + start).getTime();
        var endnum = new Date("1970-01-01" + " " + end).getTime();
        if (endnum <= startnum && parseInt($("#speedModeType").val()) === 1) { //按策略限速才判断
            UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
            return false;
        } else {
            return true;
        }
    }

    var initSpeedStrategyDes = function () {
        var titleDes = "";
        var des = "";
        if (speedList.length) {
            des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
        }
        for (var i = 0; i < speedList.length; i++) {
            titleDes += speedList[i].des + '. ';
        }
        $('.speedlimitDes').html(des).prop('title', titleDes);
    }

    var speedModeHandler = function () {
        if (parseInt(this.value) === 2) {
            $('.setSpeedStrategy').hide();
        } else {
            $('.setSpeedStrategy').show();
        }
    }

    var checkSimpleForever = function (mode) {
        for (var i = 0; i < speedList.length; i++) {
            if (mode === speedList[i].mode) {
                return false;
            }
        }

        return true;
    }

    var initSpeedTimeStrategy = function () {
        var strategy = [];
        strategy[0] = {
            mode: 1,
            strategy_type: 2,
            days: [0, 0, 0, 0, 1, 0, 0],
            start_time: '23:00:00',
            end_time: '23:30:00',
        };
        //延迟设置,因为这里icheck会默认修改里面的选中事件
        $('#speedstrategy').speedstrategy({config: strategy});
        initSpeedFlag = true;
    }

    var logtimeChange = function () {
        if (this.checked) {
            $('.logdateInputDiv').show();
        } else {
            $('.logdateInputDiv').hide();
        }
    }

    //////////////////// 开始-恢复最新时间点的客户端树 ////////////////////

    const hideStep1Tips = () => {
        $('#nopointtips').hide();
        $('#noSourceAgentTips').hide();
        $('#nosearchpointtips').hide();
        $('#noSearchAgentTips').hide();
    };

    /**
     * 构建通用的恢复源树
     */
    const buildGenericSourceAgentTree = (agentList) => {
        /**
         * @type {Array<Object>}
         */
        let nodes = [{
            id: 'all-agent',
            pId: 0,
            name: LANG.UI_DB_RECOVERY_ALL_AGENT,
            title: LANG.UI_DB_RECOVERY_ALL_AGENT,
            isParent: true,
            open: true,
            nocheck: true,
            db_type: recoverySource.db_type,
            eventtype: 'all_agent',
            icon: './img/platform/flag.png',
        }];
        for (const agentInfo of agentList) {
            let name = agentInfo.hostname + '(' + agentInfo.agent_ip + ')';
            if (agentInfo.agent_ip !== agentInfo.alias) {
                name = agentInfo.alias + '(' + agentInfo.agent_ip + ')';
            }
            if (!agentInfo.online_status) {
                // 离线
                name = '(' + LANG.UI_VISUAL_OFF_LINE + ')' + name;
            }
            nodes.push({
                id: agentInfo.agent_uuid,
                pId: 'all-agent',
                name,
                title: agentInfo.agent_ip,
                isParent: true,
                open: false,
                nocheck: true,
                agent_uuid: agentInfo.agent_uuid,
                agent_ip: agentInfo.agent_ip,
                agent_alias: agentInfo.alias,
                hostname: agentInfo.hostname,
                agent_show_name: name,
                online_status: agentInfo.online_status,
                db_type: recoverySource.db_type,
                eventtype: 'agent',
                icon: './img/vm/host.png',
                expandFlag: false,
            });
        }
        return nodes;
    };

    /**
     * 构建MongoDB恢复源树
     * @param agentList
     */
    const buildMongoDBSourceAgentTree = (agentList) => {
        /**
         * 1. MongoDB
         *  集群 -> 客户端 -> 数据库
         *  单机 -> 实例 -> 数据库
         * 2. 交叉集群、单机
         */
        /**
         * @type {Array<Object>}
         */
        let nodes = [{
            id: 'all-agent',
            pId: 0,
            name: LANG.UI_DB_RECOVERY_ALL_AGENT,
            title: LANG.UI_DB_RECOVERY_ALL_AGENT,
            isParent: true,
            open: true,
            nocheck: true,
            db_type: recoverySource.db_type,
            eventtype: 'all_agent',
            icon: './img/platform/flag.png',
        }];

        let clusterList = {};
        for (const agentInfo of agentList) {
            let onlineFlag = true;
            if (!agentInfo.online_status) {
                // 离线
                onlineFlag = false;
            }
            let chkDisabled = false;
            for (const appInfo of agentInfo.app_list) {
                if (appInfo.cluster_flag) {  // 集群
                    let agentShowName = agentInfo.hostname + '(' + agentInfo.agent_ip + ')';
                    if (agentInfo.agent_ip !== agentInfo.alias) {
                        agentShowName = agentInfo.alias + '(' + agentInfo.agent_ip + ')';
                    }
                    if (typeof clusterList[appInfo.cluster_uuid] === 'undefined') {
                        clusterList[appInfo.cluster_uuid] = {
                            online_flag: onlineFlag,
                        };
                        nodes.push({
                            id: appInfo.cluster_uuid,
                            pId: 'all-agent',
                            name: appInfo.app_name,
                            title: appInfo.app_name,
                            isParent: true,
                            open: true,
                            nocheck: false,
                            chkDisabled,
                            db_type: recoverySource.db_type,
                            eventtype: 'cluster',
                            icon: './img/vm/hostcluster.png',
                            agent_uuid: agentInfo.agent_uuid,
                            agent_ip: agentInfo.agent_ip,
                            agent_alias: agentInfo.alias,
                            hostname: agentInfo.hostname,
                            cluster_show_name: appInfo.app_name,
                            online_status: onlineFlag,
                            app_uuid: '',
                            app_name: appInfo.app_name,
                            cluster_flag: true,
                            cluster_uuid: appInfo.cluster_uuid,
                            instance_name: appInfo.app_name,
                            dbname: appInfo.app_name,
                        });
                    } else {
                        clusterList[appInfo.cluster_uuid].online_flag |= onlineFlag;
                    }
                    nodes.push({
                        id: appInfo.cluster_uuid + '_' + agentInfo.agent_uuid,
                        pId: appInfo.cluster_uuid,
                        name: agentShowName,
                        title: agentInfo.agent_ip,
                        isParent: false,
                        nocheck: true,
                        agent_uuid: agentInfo.agent_uuid,
                        agent_ip: agentInfo.agent_ip,
                        agent_alias: agentInfo.alias,
                        hostname: agentInfo.hostname,
                        agent_show_name: agentShowName,
                        online_status: agentInfo.online_status,
                        db_type: recoverySource.db_type,
                        eventtype: 'agent',
                        icon: './img/vm/host.png',
                    });
                } else {  // 单机
                    let agentShowName = agentInfo.hostname + '(' + agentInfo.agent_ip + ')';
                    if (agentInfo.agent_ip !== agentInfo.alias) {
                        agentShowName = agentInfo.alias + '(' + agentInfo.agent_ip + ')';
                    }
                    if (!onlineFlag) {
                        chkDisabled = true;
                        agentShowName = '(' + LANG.UI_VISUAL_OFF_LINE + ')' + agentShowName;
                    }
                    nodes.push({
                        id: appInfo.app_uuid + '_' + agentInfo.agent_uuid,
                        pId: 'all-agent',
                        name: agentShowName,
                        title: agentInfo.agent_ip,
                        isParent: false,
                        nocheck: false,
                        instance_name: appInfo.app_name,
                        dbname: appInfo.app_name,
                        agent_uuid: agentInfo.agent_uuid,
                        agent_ip: agentInfo.agent_ip,
                        agent_alias: agentInfo.alias,
                        hostname: agentInfo.hostname,
                        agent_show_name: agentShowName,
                        online_status: agentInfo.online_status,
                        db_type: recoverySource.db_type,
                        eventtype: 'instance',
                        icon: './img/vm/host.png',
                        cluster_flag: false,
                        cluster_uuid: '',
                        expandFlag: true,
                    });
                }
            }
        }
        return nodes;
    };

    /**
     * 构建TiDB恢复源树
     * @param agentList
     */
    const buildTiDBSourceAgentTree = (agentList) => {
        /**
         * 1. TiDB
         *  集群 -> 角色 -> 数据库
         *  实例名 -> 客户端 -> 数据库
         * 2. 交叉集群、单机
         */
        /**
         * @type {Array<Object>}
         */
        let nodes = [{
            id: 'all-agent',
            pId: 0,
            name: LANG.UI_DB_RECOVERY_ALL_AGENT,
            title: LANG.UI_DB_RECOVERY_ALL_AGENT,
            isParent: true,
            open: true,
            nocheck: true,
            db_type: recoverySource.db_type,
            eventtype: 'all_agent',
            icon: './img/platform/flag.png',
        }];

        let clusterList = {};
        for (const agentInfo of agentList) {
            let onlineFlag = true;
            if (!agentInfo.online_status) {
                // 离线
                onlineFlag = false;
            }
            let chkDisabled = false;
            for (const appInfo of agentInfo.app_list) {
                if (appInfo.cluster_flag) {  // 集群
                    let appDetail = JSON.parse(appInfo.app_detail);
                    let roleName = appDetail.node_role + '(' + agentInfo.agent_ip + ')';
                    if (typeof clusterList[appInfo.cluster_uuid] === 'undefined') {
                        clusterList[appInfo.cluster_uuid] = {
                            online_flag: onlineFlag,
                        };
                        nodes.push({
                            id: appInfo.cluster_uuid,
                            pId: 'all-agent',
                            name: appInfo.app_name,
                            title: appInfo.app_name,
                            isParent: true,
                            open: true,
                            nocheck: false,
                            chkDisabled,
                            db_type: recoverySource.db_type,
                            eventtype: 'cluster',
                            icon: './img/vm/hostcluster.png',
                            agent_uuid: agentInfo.agent_uuid,
                            agent_ip: agentInfo.agent_ip,
                            agent_alias: agentInfo.alias,
                            hostname: agentInfo.hostname,
                            cluster_show_name: appInfo.app_name,
                            online_status: onlineFlag,
                            app_uuid: '',
                            app_name: appInfo.app_name,
                            cluster_flag: true,
                            cluster_uuid: appInfo.cluster_uuid,
                            instance_name: appInfo.app_name,
                            dbname: appInfo.app_name,
                        });
                    } else {
                        clusterList[appInfo.cluster_uuid].online_flag |= onlineFlag;
                    }
                    if (!onlineFlag) {
                        roleName = '(' + LANG.UI_VISUAL_OFF_LINE + ')' + roleName;
                    }
                    nodes.push({
                        id: appInfo.cluster_uuid + '_' + agentInfo.agent_uuid,
                        pId: appInfo.cluster_uuid,
                        name: roleName,
                        title: agentInfo.agent_ip,
                        isParent: false,
                        nocheck: true,
                        agent_uuid: agentInfo.agent_uuid,
                        agent_ip: agentInfo.agent_ip,
                        agent_alias: agentInfo.alias,
                        hostname: agentInfo.hostname,
                        agent_show_name: roleName,
                        online_status: agentInfo.online_status,
                        db_type: recoverySource.db_type,
                        eventtype: 'agent',
                        icon: './img/vm/host.png',
                    });
                } else {  // 单机
                    let instanceShowName = appInfo.app_name;
                    let agentShowName = agentInfo.hostname + '(' + agentInfo.agent_ip + ')';
                    if (agentInfo.agent_ip !== agentInfo.alias) {
                        agentShowName = agentInfo.alias + '(' + agentInfo.agent_ip + ')';
                    }
                    if (!onlineFlag) {
                        chkDisabled = true;
                        instanceShowName = '(' + LANG.UI_VISUAL_OFF_LINE + ')' + instanceShowName;
                        agentShowName = '(' + LANG.UI_VISUAL_OFF_LINE + ')' + agentShowName;
                    }
                    nodes.push({
                        id: appInfo.app_uuid,
                        pId: 'all-agent',
                        name: instanceShowName,
                        title: appInfo.app_name,
                        isParent: true,
                        open: true,
                        nocheck: false,
                        chkDisabled,
                        db_type: recoverySource.db_type,
                        eventtype: 'cluster',
                        icon: './img/vm/hostcluster.png',
                        agent_uuid: agentInfo.agent_uuid,
                        agent_ip: agentInfo.agent_ip,
                        agent_alias: agentInfo.alias,
                        hostname: agentInfo.hostname,
                        cluster_show_name: instanceShowName,
                        online_status: onlineFlag,
                        app_uuid: appInfo.app_uuid,
                        app_name: appInfo.app_name,
                        cluster_flag: false,
                        cluster_uuid: '',
                        instance_name: appInfo.app_name,
                        dbname: appInfo.app_name,
                    });
                    nodes.push({
                        id: appInfo.app_uuid + '_' + agentInfo.agent_uuid,
                        pId: appInfo.app_uuid,
                        name: agentShowName,
                        title: agentInfo.agent_ip,
                        isParent: false,
                        nocheck: true,
                        agent_uuid: agentInfo.agent_uuid,
                        agent_ip: agentInfo.agent_ip,
                        agent_alias: agentInfo.alias,
                        hostname: agentInfo.hostname,
                        agent_show_name: agentShowName,
                        online_status: agentInfo.online_status,
                        db_type: recoverySource.db_type,
                        eventtype: 'agent',
                        icon: './img/vm/host.png',
                    });
                }
            }
        }

        // 处理集群的在线、授权状态
        for (const index in nodes) {
            if (nodes[index].eventtype !== 'cluster') {
                continue;
            }
            if (!nodes[index].cluster_flag) {
                continue;
            }
            let chkDisabled = false;
            let showName = nodes[index].name;
            if (!clusterList[nodes[index].cluster_uuid].online_flag) {
                chkDisabled = true;
                showName = '(' + LANG.UI_VISUAL_OFF_LINE + ')' + showName;
            }
            nodes[index].name = showName;
            nodes[index].cluster_show_name = showName;
            nodes[index].chkDisabled = chkDisabled;
        }
        return nodes;
    };

    /**
     * 初始化恢复最新时间点的客户端树
     */
    const initSourceAgentTree = () => {
        recoverySource.time_point_list = {};
        recoverySource.timepoint_encrypt_password_map = {};
        recoverySource.db_type = parseInt($('#dbTypeSelect').val());
        $('#VMGroupList').html('');
        $('#searchvm').val('');
        hideStep1Tips();
        if (pointtypetree) {
            pointtypetree.destroy();
            pointtypetree = null;
        }
        if (!recoverySource.db_type) {
            $('#noSourceAgentTips').show();
            return;
        }
        initRecoverySourceTree();
    };

    /**
     * 定时恢复源节点点击了
     * @param {*} treeId
     * @param {*} treeNode
     */
    const clickRecoverySourceNode = (treeId, treeNode) => {
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
     * 将恢复源添加到右侧中
     * @param {*} treeNode
     */
    const addRecoverySourceNodetList = function (treeNode) {
        let selectedRecoverySourceKey;
        if (treeNode.eventtype === 'cluster') {
            selectedRecoverySourceKey = treeNode.cluster_uuid;
        } else if (treeNode.eventtype === 'instance') {
            selectedRecoverySourceKey = treeNode.app_uuid;
        }
        if (treeNode.checked) {
            let agentContent = `
            <div id="dbAgentDiv_${selectedRecoverySourceKey}" class="add-list">
                <div class="accordion fileAccordion">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                    style="display: inline-block; width: 99%;text-decoration: none;" data-container="body"
                                    data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".fileAccordion"
                                    href="#dbAgentInfo_${selectedRecoverySourceKey}" aria-expanded="true">
                                    <span class="font-green-seagreen">${treeNode.name}</span>
                                </a>
                            </h4>
                        </div>
                        <div id="dbAgentInfo_${selectedRecoverySourceKey}" class="panel-collapse collapse in"
                            style="height: calc(100% - 34px);">
                            <div class="panel-body" style="padding: 16px; max-height: 200px; overflow-y: auto">
                                <ul id="dbAgentTree_${selectedRecoverySourceKey}" class="ztree" style="overflow: hidden"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
            $('#VMGroupList').append(agentContent);
            recoverySource.time_point_list[selectedRecoverySourceKey] = treeNode;
            $('#VMGroupList #dbAgentInfo_' + selectedRecoverySourceKey).collapse('show');
        } else {
            if (treeNode.db_type == CONF.DB_TYPE.SQLSERVER || treeNode.db_type == CONF.DB_TYPE.SAPHANA) {
                if (treeNode.eventtype === 'cluster') {
                    let treeObj = $.fn.zTree.getZTreeObj(`dbAgentTree_${treeNode.cluster_uuid}`);
                    if (treeObj) {
                        let dbCheckNodes = treeObj.getCheckedNodes(true);
                        if (dbCheckNodes.length) {
                            for (const dbCheckNode of dbCheckNodes) {
                                delete recoverySource.time_point_list[dbCheckNode.cluster_uuid + '_' + dbCheckNode.db_index];
                                if (dbCheckNode.eventtype === 'timepoint') {
                                    delete recoverySource.timepoint_encrypt_password_map[dbCheckNode.timepoint_uuid];
                                }
                            }
                        }
                    }
                } else if (treeNode.eventtype === 'instance') {
                    let treeObj = $.fn.zTree.getZTreeObj(`dbAgentTree_${treeNode.app_uuid}`);
                    if (treeObj) {
                        let dbCheckNodes = treeObj.getCheckedNodes(true);
                        if (dbCheckNodes.length) {
                            for (const dbCheckNode of dbCheckNodes) {
                                delete recoverySource.time_point_list[dbCheckNode.app_uuid + '_' + dbCheckNode.db_index];
                                if (dbCheckNode.eventtype === 'timepoint') {
                                    delete recoverySource.timepoint_encrypt_password_map[dbCheckNode.timepoint_uuid];
                                }
                            }
                        }
                    }
                }
            }
            $('#dbAgentDiv_' + selectedRecoverySourceKey).remove();
            delete recoverySource.time_point_list[selectedRecoverySourceKey]
            if (treeNode.eventtype === 'timepoint') {
                delete recoverySource.timepoint_encrypt_password_map[treeNode.timepoint_uuid];
            }
        }
    };

    /**
     * 点击了定时恢复源已选的节点
     * @param {*} treeId
     * @param {*} treeNode
     */
    const clickSelectedRecoverySourceNode = (treeId, treeNode) => {
        switch (treeNode.eventtype) {
            case 'cluster':
            case 'instance':
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, false, true, true);
                break;
            case 'db':
                $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
                break;
            default:
                break;
        }
    };

    /**
     * 显示演练恢复源的加密密码(SQL Server/SAP HANA)
     * @param dbType
     * @param treeNode
     * @param treeId
     * @returns {Promise<unknown>}
     */
    const judgeRecoverySourceTimepointEncryptPasswordForDatabase = (dbType, treeNode, treeId) => {
        return new Promise((resolve, reject) => {
            if (!treeNode.checked) {
                resolve();
                return;
            }
            if (!Object.keys(recoverySourceTimepointList).length) {
                resolve();
                return;
            }
            let newestTimepoint = null;
            if (treeNode.cluster_flag) {
                for (const instanceInfo of treeNode.instance_list) {
                    let key = instanceInfo.agent_info.agent_uuid + '_' + instanceInfo.instance_name + '_' + treeNode.db_name;
                    if (typeof recoverySourceTimepointList[key] === 'undefined') {
                        continue;
                    }
                    if (newestTimepoint === null) {
                        newestTimepoint = recoverySourceTimepointList[key];
                    } else {
                        if (recoverySourceTimepointList[key].time_point > newestTimepoint.time_point) {
                            newestTimepoint = recoverySourceTimepointList[key];
                        }
                    }
                }
            } else {
                let key = treeNode.agent_uuid + '_' + treeNode.instance_name + '_' + treeNode.db_name;
                if (typeof recoverySourceTimepointList[key] !== 'undefined') {
                    newestTimepoint = recoverySourceTimepointList[key];
                }
            }
            if (!newestTimepoint) {
                resolve();
                return;
            }
            if (treeNode.cluster_flag) {
                for (const instanceInfo of treeNode.instance_list) {
                    let key = instanceInfo.agent_info.agent_uuid + '_' + instanceInfo.instance_name + '_' + instanceInfo.db_name;
                    recoverySource.agent_newest_timepoint_map[key] = newestTimepoint;
                }
            } else {
                let key = treeNode.agent_uuid + '_' + treeNode.instance_name + '_' + treeNode.db_name;
                recoverySource.agent_newest_timepoint_map[key] = newestTimepoint;
            }
            if (newestTimepoint.is_encrypted && !newestTimepoint.config.password_auto_flag) {
                showRecoverySourceTimepointEncryptPasswordDialog(
                    treeNode,
                    $.fn.zTree.getZTreeObj(treeId),
                    treeNode.getParentNode().name + '/' + treeNode.name,
                    newestTimepoint,
                    dbType
                ).then(resolve)
            } else {
                resolve();
            }
        });
    };

    /**
     * 选择了定时恢复源已选的节点
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const checkSelectedRecoverySourceNode = (ev, treeId, treeNode) => {
        let dbType = parseInt(treeNode.db_type);
        switch (treeNode.eventtype) {
            case 'cluster':
            case 'instance':
                if (typeof treeNode.children === 'undefined' || !treeNode.children.length) {
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, false, true, true);
                    $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, false, true);
                    return;
                }
                break;
            case 'db':
                judgeRecoverySourceTimepointEncryptPasswordForDatabase(dbType, treeNode, treeId).then(() => {
                    let selectedRecoverySourceKey;
                    if (treeNode.cluster_flag) {
                        selectedRecoverySourceKey = treeNode.cluster_uuid + '_' + treeNode.db_index;
                    } else {
                        selectedRecoverySourceKey = treeNode.app_uuid + '_' + treeNode.db_index;
                    }
                    if (treeNode.checked) {
                        recoverySource.time_point_list[selectedRecoverySourceKey] = treeNode;
                    } else {
                        delete recoverySource.time_point_list[selectedRecoverySourceKey];
                        if (treeNode.eventtype === 'timepoint') {
                            delete recoverySource.timepoint_encrypt_password_map[treeNode.timepoint_uuid];
                        }
                    }
                });
            default:
                break;
        }
        if (!treeNode.checked) {
            return;
        }
    };

    /**
     * 展开了了定时恢复源已选的节点
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const expandSelectedRecoverySourceNode = (treeId, treeNode) => {
        if (treeNode.eventtype !== 'cluster' && treeNode.eventtype !== 'instance') {
            return;
        }
        if (typeof treeNode.children !== 'undefined' && treeNode.children.length) {
            return;
        }
        let selectedRecoverySourceKey;
        let appUuid = treeNode.app_uuid;
        if (treeNode.eventtype === 'cluster') {
            selectedRecoverySourceKey = treeNode.cluster_uuid;
        } else if (treeNode.eventtype === 'instance') {
            selectedRecoverySourceKey = treeNode.app_uuid;
        }

        // 加载数据库/表空间信息
        Metronic.blockUI({target: `#dbAgentDiv_${selectedRecoverySourceKey}`, animate: true});
        pAjaxRequest({app_uuid: appUuid}, `/api/v1/db/databases`, 'GET', res => {
            Metronic.unblockUI(`#dbAgentDiv_${selectedRecoverySourceKey}`);
            if (!res.success) {
                return;
            }
            let nodes = [];
            for (const dbIndex in res.data.rows) {
                let dbInfo = res.data.rows[dbIndex];
                let nocheck = true;
                let checked = false;
                let title = dbInfo.db_name;
                let dbUuid = '';
                let recoveryTaskFlag = false;
                switch (dbInfo.db_type) {
                    case CONF.DB_TYPE.SQLSERVER:
                    case CONF.DB_TYPE.SAPHANA:
                        nocheck = false;
                        if (editTaskFlag && oldRecoveryTaskData !== null) {
                            for (const oldDbInfo of oldRecoveryTaskData.recovery_info.db_config_list) {
                                if (treeNode.cluster_flag) {
                                    if (
                                        oldDbInfo.source_cluster_flag &&
                                        oldDbInfo.source_cluster_uuid == treeNode.cluster_uuid &&
                                        oldDbInfo.source_db_name == dbInfo.db_name
                                    ) {
                                        checked = true;
                                        recoveryTaskFlag = true;
                                        title += `('${oldRecoveryTaskData.job_name}')`;
                                        dbUuid = oldDbInfo.db_uuid;
                                        break;
                                    }
                                } else {
                                    if (
                                        !oldDbInfo.source_cluster_flag &&
                                        oldDbInfo.source_agent_uuid == treeNode.agent_uuid &&
                                        oldDbInfo.source_instance_name == treeNode.instance_name &&
                                        oldDbInfo.source_db_name == dbInfo.db_name
                                    ) {
                                        checked = true;
                                        recoveryTaskFlag = true;
                                        title += `('${oldRecoveryTaskData.job_name}')`;
                                        dbUuid = oldDbInfo.db_uuid;
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    default:
                        break;
                }
                nodes.push({
                    id: dbInfo.db_name,
                    pId: selectedRecoverySourceKey,
                    name: dbInfo.db_name,
                    title,
                    eventtype: 'db',
                    app_uuid: dbInfo.app_uuid,
                    agent_uuid: dbInfo.agent_uuid,
                    instance_name: dbInfo.instance_name,
                    isParent: false,
                    nocheck,
                    checked,
                    icon: './img/platform/storage.png',
                    db_type: dbInfo.db_type,
                    db_name: dbInfo.db_name,
                    db_uuid: dbUuid,
                    cluster_flag: dbInfo.is_cluster,
                    cluster_uuid: dbInfo.cluster_uuid,
                    cluster_name: dbInfo.cluster_name,
                    cluster_type: dbInfo.cluster_type,
                    dir_path: treeNode.dir_path + '/' + dbInfo.db_name,
                    app_detail: dbInfo.app_detail,
                    recovery_task_flag: recoveryTaskFlag,
                    instance_list: treeNode.instance_list,
                    db_index: dbIndex,
                });
            }
            $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, nodes, true);
            if (editTaskFlag && oldRecoveryTaskData !== null) {
                let checkedNodes = $.fn.zTree.getZTreeObj(treeId).getCheckedNodes(true);
                for (const checkedNode of checkedNodes) {
                    $.fn.zTree.getZTreeObj(treeId).checkNode(checkedNode, true, true, true);
                }
            }
        });
    };

    /**
     * 获取Oracle备份树的设置
     */
    const getSelectedRecoverySourceNodeTreeSetting = () => {
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
                    // 恢复任务
                    if (!!treeNode.recovery_task_flag) {
                        css = {color: "green", "font-weight": "bold"};
                    }
                    return css;
                },
            },
            callback: {
                beforeClick: clickSelectedRecoverySourceNode,
                onCheck: checkSelectedRecoverySourceNode,
                beforeExpand: expandSelectedRecoverySourceNode
            }
        };
    };

    /**
     * 初始化已选择的恢复树
     * @param {*} selectedTreeNode
     */
    const initSelectedRecoverySourceNodeTree = (selectedTreeNode) => {
        let nodes = [];
        let selectedRecoverySourceKey;
        if (selectedTreeNode.eventtype === 'cluster') {
            let selectedChildren = selectedTreeNode.children;
            let agentUuid = '';
            let appUuid = '';
            let instanceName = '';
            let recoveryTaskFlag = false;
            for (const selectedChild of selectedChildren) {
                if (selectedChild.online_flag) {
                    agentUuid = selectedChild.agent_uuid;
                    appUuid = selectedChild.app_uuid;
                    instanceName = selectedChild.instance_name;
                    break;
                }
            }
            selectedRecoverySourceKey = selectedTreeNode.cluster_uuid;
            let name = selectedTreeNode.name;
            let title = selectedTreeNode.name;
            let inbackup = false;
            let instanceNodeList = selectedTreeNode.children;
            if (selectedTreeNode.db_type == CONF.DB_TYPE.MONGODB) {
                instanceNodeList = selectedTreeNode.instance_list;
            }
            // 恢复任务
            if (editTaskFlag && oldRecoveryTaskData !== null) {
                for (const oldDbInfo of oldRecoveryTaskData.recovery_info.db_config_list) {
                    if (oldDbInfo.source_cluster_flag && oldDbInfo.source_cluster_uuid == selectedTreeNode.cluster_uuid) {
                        recoveryTaskFlag = true;
                        title += `('${oldRecoveryTaskData.job_name}')`;
                    }
                }
            }

            nodes.push({
                id: selectedRecoverySourceKey,
                pId: 0,
                name,
                title,
                eventtype: 'cluster',
                icon: selectedTreeNode.icon,
                isParent: true,
                open: false,
                cluster_flag: true,
                cluster_uuid: selectedTreeNode.cluster_uuid,
                cluster_name: selectedTreeNode.cluster_name,
                cluster_type: selectedTreeNode.cluster_type,
                dir_path: selectedTreeNode.dir_path,
                nocheck: true,
                instance_name: instanceName,
                db_name: selectedTreeNode.db_name,
                app_uuid: appUuid,
                agent_uuid: agentUuid,
                db_type: selectedTreeNode.db_type,
                inbackup,
                instance_node_list: instanceNodeList,
                instance_list: selectedTreeNode.instance_list,
                app_service_name: selectedTreeNode.app_service_name,
                app_detail: selectedTreeNode.app_detail,
                recovery_task_flag: recoveryTaskFlag,
            });
        } else if (selectedTreeNode.eventtype === 'instance') {
            selectedRecoverySourceKey = selectedTreeNode.app_uuid;
            let name = selectedTreeNode.name;
            let title = selectedTreeNode.name;
            let inbackup = false;
            let recoveryTaskFlag = false;
            // 恢复任务
            if (editTaskFlag && oldRecoveryTaskData !== null) {
                for (const oldDbInfo of oldRecoveryTaskData.recovery_info.db_config_list) {
                    if (
                        !oldDbInfo.source_cluster_flag &&
                        oldDbInfo.source_agent_uuid == selectedTreeNode.agent_uuid &&
                        oldDbInfo.source_instance_name == selectedTreeNode.instance_name
                    ) {
                        recoveryTaskFlag = true;
                        title += `('${oldRecoveryTaskData.job_name}')`;
                    }
                }
            }

            nodes.push({
                id: selectedRecoverySourceKey,
                pId: 0,
                name,
                title,
                eventtype: 'instance',
                icon: selectedTreeNode.icon,
                isParent: true,
                open: false,
                cluster_flag: false,
                cluster_uuid: '',
                cluster_name: '',
                cluster_type: 0,
                dir_path: selectedTreeNode.dir_path,
                nocheck: true,
                instance_name: selectedTreeNode.instance_name,
                db_name: selectedTreeNode.db_name,
                app_uuid: selectedTreeNode.app_uuid,
                agent_uuid: selectedTreeNode.agent_uuid,
                db_type: selectedTreeNode.db_type,
                inbackup,
                instance_node_list: [],
                instance_list: [],
                app_service_name: selectedTreeNode.app_service_name,
                app_detail: selectedTreeNode.app_detail,
                recovery_task_flag: recoveryTaskFlag,
            });
        }

        let selectedTree = $.fn.zTree.init($('#dbAgentTree_' + selectedRecoverySourceKey), getSelectedRecoverySourceNodeTreeSetting(), nodes);
        // 默认展开
        let allNodes = selectedTree.getNodes();
        selectedTree.expandNode(allNodes[0], true, false, true, true);
        if (editTaskFlag && oldRecoveryTaskData !== null) {
            let checkedNodes = selectedTree.getCheckedNodes(true);
            for (const checkedNode of checkedNodes) {
                selectedTree.checkNode(checkedNode, true, true, true);
            }
        }
    };

    /**
     * 显示演练恢复源的加密密码的弹窗
     * @param treeNode
     * @param treeObj
     * @param name
     * @param timepoint
     * @param dbType
     * @returns {Promise<unknown>}
     */
    const showRecoverySourceTimepointEncryptPasswordDialog = (treeNode, treeObj, name, timepoint, dbType) => {
        return new Promise((resolve) => {
            let title = LANG.UI_DB_DRILL_INPUT_ENCRYPT_PASSWORD.replace('%S', `<span style="color: red">${name}</span>`);
            let message = `
            <div>
                <div>${title}</div>
                <div class="bootbox-input-wrapper" style="position: relative;margin-bottom: 15px;">
                    
                    <input class="bootbox-input bootbox-input-password" type="password" autocomplete="off"
                        style="border: 1px solid #E6E6E6;border-radius: 2px !important;height: 34px;width:100%;background-color: #FFFFFF;padding: 6px 12px"
                        oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\\s+/g,'')" />
                    <button type="button" class="btn btn-link show-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                        <i class="viconfont vicon-a-lujing8232"></i>
                    </button>
                </div>
            </div>
            `;
            bootbox.dialog({
                title: LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS,
                message,
                buttons: {
                    cancel: {
                        label: LANG.UI_PUBLIC_CANCEL,
                        className: 'btn btn-default',
                        callback: function () {
                            treeObj.checkNode(treeNode, false, true);
                        }
                    },
                    confirm: {
                        label: LANG.UI_PUBLIC_CONFIRM,
                        className: 'btn btn-primary',
                        callback: debounce(function () {
                            let $input = $(this).find('.bootbox-input-password');
                            let result = $input.val();
                            if (!result) {
                                return false;
                            }
                            if (!isNotLatinCode(result)) {
                                return false;
                            }
                            if (!validEncryptPassword(result, timepoint.time_point_uuid)) {
                                UIToastr.showWarning(LANG.UI_RECOVERY_DB_PROTECT_CREATE_DRILL_TITLE, LANG.UI_DB_DRILL_ENCRYPT_PASSWORD_ERROR.replace('%S', name));
                                return false;
                            }

                            // 存储加密密码
                            if (treeNode.cluster_flag) {
                                for (const instanceInfo of treeNode.instance_list) {
                                    let key = instanceInfo.agent_info.agent_uuid + '_' + instanceInfo.instance_name;
                                    if (dbType === CONF.DB_TYPE.SQLSERVER || dbType === CONF.DB_TYPE.SAPHANA) {
                                        key += '_' + instanceInfo.db_name;
                                    }
                                    recoverySource.agent_encrypt_password_map[key] = result;
                                }
                            } else {
                                let key = treeNode.agent_uuid + '_' + treeNode.instance_name;
                                if (dbType === CONF.DB_TYPE.SQLSERVER || dbType === CONF.DB_TYPE.SAPHANA) {
                                    key += '_' + treeNode.db_name;
                                }
                                recoverySource.agent_encrypt_password_map[key] = result;
                            }
                            $(this).modal('hide');
                            resolve();
                        }, 300, false),
                    }
                }
            }).on('shown.bs.modal', function () {
                // 点击关闭按钮
                $(this).find('button.bootbox-close-button.close').on('click', () => {
                    treeObj.checkNode(treeNode, false, true);
                });
                // 获取输入框和按钮
                let $input = $(this).find('.bootbox-input-password');
                let $btn = $(this).find('.show-password-btn');

                // 添加点击事件监听器
                $btn.on('click', function () {
                    let inputType = $input.attr('type');
                    if (inputType === 'password') {
                        $input.attr('type', 'text');
                        $btn.find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
                    } else {
                        $input.attr('type', 'password');
                        $btn.find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
                    }
                });
            });
        });
    };

    /**
     * 显示演练恢复源的加密密码
     * @param dbType
     * @param treeNode
     * @returns {Promise<unknown>}
     */
    const judgeRecoverySourceTimepointEncryptPassword = (dbType, treeNode) => {
        return new Promise((resolve, reject) => {
            if (!treeNode.checked) {
                resolve();
                return;
            }
            if (dbType === CONF.DB_TYPE.SQLSERVER || dbType === CONF.DB_TYPE.SAPHANA) {
                resolve();
                return;
            }
            if (!Object.keys(recoverySourceTimepointList).length) {
                resolve();
                return;
            }
            let newestTimepoint = null;
            if (treeNode.cluster_flag) {
                for (const instanceInfo of treeNode.instance_list) {
                    let key = instanceInfo.agent_info.agent_uuid + '_' + instanceInfo.instance_name;
                    if (typeof recoverySourceTimepointList[key] === 'undefined') {
                        continue;
                    }
                    if (newestTimepoint === null) {
                        newestTimepoint = recoverySourceTimepointList[key];
                    } else {
                        if (recoverySourceTimepointList[key].time_point > newestTimepoint.time_point) {
                            newestTimepoint = recoverySourceTimepointList[key];
                        }
                    }
                }
            } else {
                let key = treeNode.agent_uuid + '_' + treeNode.instance_name;
                if (typeof recoverySourceTimepointList[key] !== 'undefined') {
                    newestTimepoint = recoverySourceTimepointList[key];
                }
            }
            if (!newestTimepoint) {
                resolve();
                return;
            }
            if (treeNode.cluster_flag) {
                for (const instanceInfo of treeNode.instance_list) {
                    let key = instanceInfo.agent_info.agent_uuid + '_' + instanceInfo.instance_name;
                    recoverySource.agent_newest_timepoint_map[key] = newestTimepoint;
                }
            } else {
                let key = treeNode.agent_uuid + '_' + treeNode.instance_name;
                recoverySource.agent_newest_timepoint_map[key] = newestTimepoint;
            }
            if (newestTimepoint.is_encrypted && !newestTimepoint.config.password_auto_flag) {
                showRecoverySourceTimepointEncryptPasswordDialog(treeNode, pointtypetree, treeNode.name, newestTimepoint, dbType).then(resolve)
            } else {
                resolve();
            }
        });
    };

    /**
     * 定时恢复源节点选中了
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const checkRecoverySourceNode = (ev, treeId, treeNode) => {
        let dbType = parseInt(treeNode.db_type);
        judgeRecoverySourceTimepointEncryptPassword(dbType, treeNode).then(() => {
            if (treeNode.checked) {  // 单选
                let oldCheckNodes = pointtypetree.getCheckedNodes(true);
                if (oldCheckNodes.length && parseInt(oldCheckNodes[0].db_type) !== dbType) {
                    pointtypetree.checkAllNodes(false);
                    recoverySource.time_point_list = {};
                    recoverySource.timepoint_encrypt_password_map = {};
                    $('#VMGroupList').html('');
                }
                if (dbType !== CONF.DB_TYPE.SQLSERVER && dbType !== CONF.DB_TYPE.SAPHANA) {
                    pointtypetree.checkAllNodes(false);
                    recoverySource.time_point_list = {};
                    recoverySource.timepoint_encrypt_password_map = {};
                    $('#VMGroupList').html('');
                }
                pointtypetree.checkNode(treeNode, true);
            }
            if (
                treeNode.eventtype === 'cluster' ||  // 显示集群
                (treeNode.eventtype === 'instance' && treeNode.pId === 'standalone')  // 单实例
            ) {
                addRecoverySourceNodetList(treeNode);
                if (treeNode.checked) {
                    initSelectedRecoverySourceNodeTree(treeNode);
                }
            }
        });
    };

    /**
     * 获取恢复源树的配置
     * @returns
     */
    const getRecoverySourceTreeSetting = () => {
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
                beforeClick: clickRecoverySourceNode,
                onCheck: checkRecoverySourceNode,
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
                    // Oracle空实例
                    if (treeNode.db_type == CONF.DB_TYPE.ORACLE) {
                        if (treeNode.eventtype === 'instance' && parseInt(treeNode.app_auth_type) === 1) {
                            style = {'color': '#F19F00', 'font-weight': 'bold'};
                        }
                    }
                    return style;
                },
            }
        };
    };

    /**
     * 设置定时恢复树
     * @param {*} instanceList
     */
    const setRecoverySourceTree = (instanceList) => {
        let dbType = parseInt($('#dbTypeSelect').val());
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
            $('#noSourceAgentTips').show();
            return;
        }

        for (const standaloneNode of standaloneNodes) {
            let chkDisabled = false;
            let checked = false;
            let name = standaloneNode.instance_name + '(' + standaloneNode.agent_info.ip + ')';
            let dirPath = standaloneNode.instance_name + '(' + standaloneNode.agent_info.ip + '/' + standaloneNode.instance_name + ')';
            let dbBackupFlag = false;
            if (!standaloneNode.agent_info.online_flag) {
                chkDisabled = true;
                name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
            }
            let title = name;
            let dbUuid = '';

            if (Array.isArray(standaloneNode.db_backup_info) && standaloneNode.db_backup_info.length) {
                dbBackupFlag = true;
            }

            // Oracle空实例不能作为恢复源
            if (dbType === CONF.DB_TYPE.ORACLE) {
                if (parseInt(standaloneNode.app_auth_type) === 1) {
                    chkDisabled = true;
                    title = LANG.UI_DB_RECOVERY_ORACLE_OS_AUTH_SOURCE;
                }
            }
            if (editTaskFlag && oldRecoveryTaskData !== null) {  // 修改恢复任务
                for (const oldDbInfo of oldRecoveryTaskData.recovery_info.db_config_list) {
                    if (oldDbInfo.source_cluster_flag) {
                        continue;
                    }
                    if (
                        oldDbInfo.source_agent_uuid === standaloneNode.agent_info.agent_uuid &&
                        oldDbInfo.source_instance_name === standaloneNode.instance_name
                    ) {
                        checked = true;
                        dbUuid = oldDbInfo.db_uuid;
                        break;
                    }
                }
            }

            nodes.push({
                id: standaloneNode.app_uuid,
                pId: 'standalone',
                name,
                title,
                isParent: false,
                eventtype: 'instance',
                chkDisabled,
                checked,
                icon: './img/platform/storage.png',
                instance_name: standaloneNode.instance_name,
                app_uuid: standaloneNode.app_uuid,
                agent_uuid: standaloneNode.agent_info.agent_uuid,
                agent_ip: standaloneNode.agent_info.ip,
                db_uuid: dbUuid,
                db_name: standaloneNode.instance_name,
                cluster_flag: false,
                cluster_uuid: '',
                cluster_name: '',
                dir_path: dirPath,
                online_flag: standaloneNode.agent_info.online_flag,
                net_model: standaloneNode.agent_info.net_model,
                db_backup_info: standaloneNode.db_backup_info,
                dbBackupFlag,
                app_auth_type: standaloneNode.app_auth_type,
                db_type: parseInt(standaloneNode.db_type),
                group_uuid: standaloneNode.agent_info.group_uuid,
                group_name: standaloneNode.agent_info.group_name,
                app_detail: standaloneNode.app_detail,
            });
        }
        for (const clusterUuid in clusterNodes) {
            let clusterInfo = clusterNodes[clusterUuid].clsuter_info;
            let clusterOnlineFlag = false;
            let clusterNodeOfflineAgentUuidList = [];
            let clusterDbType = 0;
            let clusterDbBackupFlag = false;
            let clusterDbBackupInfo = [];
            let clusterAgentUuid = '';
            let clusterInstanceName = '';
            let clusterAppUuid = '';
            let clusterGroupUuid = '';
            let clusterGroupName = '';
            let clusterType = parseInt(clusterInfo.cluster_type);
            let clusterAppDetail = {};
            let clusterDirPath = clusterInfo.cluster_name;
            let clusterDirPathList = [];
            for (const instanceInfo of clusterNodes[clusterUuid].instance_list) {
                let chkDisabled = false;
                let name = instanceInfo.instance_name + '(' + instanceInfo.agent_info.ip + ')';
                let dirPath = instanceInfo.instance_name + '(' + instanceInfo.agent_info.ip + '/' + instanceInfo.instance_name + ')';
                let subDirPath = instanceInfo.agent_info.ip + '/' + instanceInfo.instance_name;
                switch (dbType) {
                    case CONF.DB_TYPE.TIDB:
                        // TiDB显示<角色名(IP)>
                        name = instanceInfo.app_detail.node_role + '(' + instanceInfo.agent_info.ip + ')';
                        subDirPath = instanceInfo.agent_info.ip + '/' + instanceInfo.app_detail.node_role;
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
                clusterDirPathList.push(subDirPath);
                if (!clusterAgentUuid) {
                    clusterAgentUuid = instanceInfo.agent_info.agent_uuid;
                    clusterInstanceName = instanceInfo.instance_name;
                    clusterAppUuid = instanceInfo.app_uuid;
                }
                let dbBackupFlag = false;
                // 离线状态
                if (!instanceInfo.agent_info.online_flag) {  // 离线
                    chkDisabled = true;
                    name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
                    clusterNodeOfflineAgentUuidList.push(instanceInfo.agent_info.agent_uuid);
                } else {  // 需要全部离线才离线
                    clusterOnlineFlag = true;
                    clusterAgentUuid = instanceInfo.agent_info.agent_uuid;
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
                    db_name: instanceInfo.instance_name,
                    cluster_flag: true,
                    cluster_uuid: instanceInfo.cluster_info.cluster_uuid,
                    cluster_name: instanceInfo.cluster_info.cluster_name,
                    dir_path: dirPath,
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
            let clusterChecked = false;
            let clusterDbUuid = '';
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
            if (editTaskFlag && oldRecoveryTaskData !== null) {  // 修改恢复任务
                for (const oldDbInfo of oldRecoveryTaskData.recovery_info.db_config_list) {
                    if (!oldDbInfo.source_cluster_flag) {
                        continue;
                    }
                    if (oldDbInfo.source_cluster_uuid === clusterInfo.cluster_uuid) {
                        clusterChecked = true;
                        clusterDbUuid = oldDbInfo.db_uuid;
                        break;
                    }
                }
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
                checked: clusterChecked,
                cluster_flag: true,
                cluster_uuid: clusterInfo.cluster_uuid,
                cluster_name: clusterInfo.cluster_name,
                cluster_service_ip: clusterInfo.cluster_service_ip,
                cluster_type: clusterType,
                dir_path: clusterDirPath + '(' + clusterDirPathList.join(', ') + ')',
                agent_uuid: clusterAgentUuid,
                app_uuid: clusterAppUuid,
                instance_name: clusterInstanceName,
                db_name: clusterInstanceName,
                app_service_name: clusterInfo.app_service_name,
                instance_list: clusterNodes[clusterUuid].instance_list,
                online_flag: clusterOnlineFlag,
                dbBackupFlag: clusterDbBackupFlag,
                db_type: clusterDbType,
                db_backup_info: clusterDbBackupInfo,
                db_uuid: clusterDbUuid,
                group_uuid: clusterGroupUuid,
                group_name: clusterGroupName,
                app_detail: clusterAppDetail,
            });
        }
        pointtypetree = $.fn.zTree.init($("#pointtypetree"), getRecoverySourceTreeSetting(), nodes);
        if (editTaskFlag && oldRecoveryTaskData !== null) {
            let checkedNodes = pointtypetree.getCheckedNodes(true);
            for (const checkedNode of checkedNodes) {
                pointtypetree.checkNode(checkedNode, true, true, true);
            }
        }

        // 加载演练恢复源的备份点
        loadRecoverySourceNewestTimepointList(standaloneNodes, clusterNodes, dbType).then();
    };

    /**
     * 查询数据库备份点
     * @param reqData
     * @return {Promise<unknown>}
     */
    const queryDbTimepointData = reqData => {
        return new Promise((resolve, reject) => {
            try {
                Metronic.blockUI({target: '#dbrecovercontent', animate: true});
                pAjaxRequest(reqData, `/api/v1/db/jobs/backup/time_point_list`, 'GET', response => {
                    Metronic.unblockUI('#dbrecovercontent');
                    if (!response.success || !response.data.rows.length) {
                        resolve([]);
                    } else {
                        resolve(response.data.rows);
                    }
                });
            } catch (error) {
                reject(error);
            }
        });
    };

    /**
     * 加载演练恢复源的备份点
     * @param standaloneNodes
     * @param clusterNodes
     * @param dbType
     */
    const loadRecoverySourceNewestTimepointList = async (standaloneNodes, clusterNodes, dbType) => {
        recoverySourceTimepointList = {};
        let reqData = {
            db_type: dbType,
            with_copy_flag: 1,
            with_copy_back_flag: 1,
            agent_list: [],
        };
        for (const standaloneNode of standaloneNodes) {
            reqData.agent_list.push({
                cluster_flag: false,
                cluster_uuid: '',
                instance_name: standaloneNode.instance_name,
                agent_uuid: standaloneNode.agent_info.agent_uuid,
                db_name: '',
            });
        }
        for (const clusterUuid in clusterNodes) {
            reqData.agent_list.push({
                cluster_flag: true,
                cluster_uuid: clusterUuid,
                instance_name: '',
                agent_uuid: '',
                db_name: '',
            });
        }

        let timepointList = await queryDbTimepointData(reqData);
        for (const row of timepointList) {
            let key = row.agent_uuid + '_' + row.instance_name;
            if (dbType === CONF.DB_TYPE.SQLSERVER || dbType === CONF.DB_TYPE.SAPHANA) {
                key += '_' + row.db_name;
            }
            if (typeof recoverySourceTimepointList[key] === 'undefined') {
                recoverySourceTimepointList[key] = row;
            } else {
                // 比较时间，取最新的
                if (recoverySourceTimepointList[key].time_point < row.time_point) {
                    recoverySourceTimepointList[key] = row;
                }
            }
        }
    };

    /**
     * 初始化定时恢复最新时间点的恢复源树
     */
    const initRecoverySourceTree = () => {
        Metronic.blockUI({target: '.two_tree', animate: true});
        pAjaxRequest({db_type: parseInt($('#dbTypeSelect').val())}, `/api/v1/db/instances`, 'GET', res => {
            Metronic.unblockUI('.two_tree');
            if (!res.success || !res.data.rows.length) {
                $('#noSourceAgentTips').show();
                return;
            }
            setRecoverySourceTree(res.data.rows);
        });
    };

    //////////////////// 结束-恢复最新时间点的客户端树 ////////////////////

    //////////////////// 开始-恢复指定时间点的集群-实例树 ////////////////////

    /**
     * 点击了集群实例的时间点树
     */
    const clickClusterInstancePointTreeNode = function (treeId, treeNode, clickFlag) {
        switch (treeNode.eventtype) {
            case 'category':  // 分组类别
            case 'db_type':  // 数据库类别
                pointtypetree.expandNode(treeNode, true);
                break;
            case 'cluster':  // 集群实例
                pointtypetree.expandNode(treeNode, true);
                if (treeNode.db_type == CONF.DB_TYPE.ORACLE || treeNode.db_type == CONF.DB_TYPE.TIDB) {  // 选择集群作为恢复源
                    pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
                }
                break;
            case 'cluster_instance':  // 集群节点
                if (treeNode.db_type == CONF.DB_TYPE.ORACLE || treeNode.db_type == CONF.DB_TYPE.TIDB) {  // 集群节点没有子节点
                    break;
                }
                // if (treeNode.last_cluster_node_flag) {  // 最后一个集群节点才可以展开
                //     pointtypetree.expandNode(treeNode, true);
                // }
                break;
            case 'instance':  // 单机实例
                if (treeNode.db_type == CONF.DB_TYPE.ORACLE || treeNode.db_type == CONF.DB_TYPE.TIDB) {  // 单机实例作为恢复源
                    pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
                    break;
                }
                pointtypetree.expandNode(treeNode, true);
                break;
            case 'db':  // 数据库
                if (treeNode.db_type == CONF.DB_TYPE.SAPHANA) {
                    pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);  // 数据库作为恢复源
                }
                pointtypetree.expandNode(treeNode, true);
                break;
            case 'task':  // 任务
                pointtypetree.expandNode(treeNode, true);
                expandClusterInstancePointTreeNode(treeId, treeNode);  // 任务展开后，需要进行加载备份点的操作
                break;
            case 'timepoint':  // 时间点
                if (treeNode.backup_mode == TIMEPOINT_TYPE_ENUM.FULL) {
                    pointtypetree.expandNode(treeNode, true);
                }
                pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
                break;
            default:
                break;
        }
    };

    /**
     * 检测选择的点是否都是磁带备份点或非磁带备份点（互斥）,如果不是,需要把之前的所有点都取消选择
     * @returns
     */
    const checkClusterInstancePointStorageTypePoint = function (treeId, treeNode) {
        if (treeNode.eventtype !== 'timepoint') {  // 只有选择了备份点才进行磁带判断(即多恢复的SQL server适用)
            return;
        }
        let treeObj = $.fn.zTree.getZTreeObj(treeId);
        var allNodes = treeObj.getCheckedNodes(true);
        for (var i = 0; i < allNodes.length; i++) {
            if (allNodes[i].eventtype !== 'timepoint') {
                continue;
            }
            var case1 = allNodes[i].storage_info.storage_type != treeNode.storage_info.storage_type && allNodes[i].storage_info.storage_type == CONF.BD_STORAGE_TYPE.TAPE; //之前有磁带的，但是现在选了和之前不同，也就是不是磁带的
            var case2 = treeNode.storage_info.storage_type == CONF.BD_STORAGE_TYPE.TAPE && allNodes[i].storage_info.storage_type != CONF.BD_STORAGE_TYPE.TAPE; //之前没有磁带的，但是现在选了磁带的
            if (case1 || case2) {
                treeObj.checkAllNodes(false);
                treeObj.checkNode(treeNode, !treeNode.checked, false, false);
                $('#VMGroupList').html('');
                return;
            }
        }
    };

    /**
     * 判断是否是同一个节点
     */
    const judgeCheckIsInSingleNode = function (treeNode, allNodes) {
        if (!treeNode.checked) {
            return true;
        }
        if (treeNode.eventtype !== 'timepoint') {  // 只有选择备份点才能确定节点
            return true;
        }
        for (let i = 0; i < allNodes.length; i++) {
            if (allNodes[i].eventtype !== 'timepoint') {
                continue;
            }
            if (allNodes[i].storage_info.node_uuid !== treeNode.storage_info.node_uuid) {
                UIToastr.showInfo(LANG.UI_RECOVERY_DATA_SOURCE_NOT_IN_ONE_NODE, LANG.UI_DB_RECOVERY_DATA_SOURCE_NOT_IN_ONE_NODE_TIPS);
                for (let j = 0; j < allNodes.length; j++) {
                    pointtypetree.checkNode(allNodes[j], false, false, false);
                }
                pointtypetree.checkNode(treeNode, true, false, false);
                return false;
            }
        }
        return true;
    };

    /**
     * 勾选添加虚拟机显示列表
     * @param {String} treeId
     * @param {Object} treeNode
     */
    const addCheckedClusterInstanceNode = function (treeNode) {
        if (!treeNode.checked) {
            return;
        }
        let subTitle = treeNode.db_name;
        let selectedKey;
        let icon = treeNode.icon;
        switch (treeNode.eventtype) {
            case 'cluster':
                selectedKey = treeNode.cluster_uuid;
                subTitle = treeNode.dir_path;
                break;
            case 'instance':
                selectedKey = treeNode.agent_uuid + '_' + treeNode.db_uuid;
                subTitle = treeNode.instance_name;
                break;
            case 'db':  // 分为集群和单机
                if (treeNode.cluster_flag) {
                    selectedKey = treeNode.cluster_uuid + '_' + treeNode.db_uuid;
                    subTitle = treeNode.dir_path;
                } else {
                    selectedKey = treeNode.agent_uuid + '_' + treeNode.db_uuid;
                    subTitle = treeNode.dir_path;
                }
                break;
            default:
                selectedKey = treeNode.job_uuid + '_' + treeNode.agent_uuid + '_' + treeNode.db_uuid;
                icon = './img/platform/storage.png';
                if (treeNode.cluster_flag) {
                    if (treeNode.db_type != CONF.DB_TYPE.SQLSERVER) {  // SQL Server显示数据库名
                        subTitle = treeNode.dir_path;
                    }
                }
                break;
        }
        let info = `
        <li class="list-group-item popovers VMTips list-group-item__recoverlist" id="${selectedKey}" data-container="body"
            data-trigger="hover" data-placement="top" data-html="true" data-content="${treeNode.dir_path}">
            <div class="col1">
                <div class="cont vmDetail">
                    <div class="cont-col1">
                        <div class="${treeNode.iconSkin}"></div>
                        <div style="width:20px;height:20px;background: url(${icon}) 0 no-repeat;"></div>
                    </div>
                    <div class="cont-col2">
                        <div class="desc list-one" style="font-size: 14px;color: #333;padding: 10px 4px 0 4px">${treeNode.name}</div>
                        <div class="desc list-one" style="font-size: 12px;color: #666;padding: 4px 4px 0 4px">${subTitle}</div>
                    </div>
                </div>
            </div>
            <div class="col2  pull-right delete-list">
                <a class="del${selectedKey}">
                    <div class="label label-sm label-danger" style="padding:0;">
                        <i class="viconfont vicon-guanbi"></i>
                    </div>
                </a>
            </div>
        </li>
        `;
        //根据虚拟机树类型添加每一列到列表
        $('#VMGroupList').append(info);
        $('#' + escapeJquery(selectedKey)).popover(); //初始化tips
        //移除虚拟机显示
        $('.del' + escapeJquery(selectedKey)).on('click', function () {
            $('.popover.in').remove();
            // 移出选择的节点
            delete recoverySource.time_point_list[selectedKey];
            if (treeNode.eventtype === 'timepoint') {
                delete recoverySource.timepoint_encrypt_password_map[treeNode.timepoint_uuid];
            }
            if (JSON.stringify(recoverySource.time_point_list) === '{}') {
                recoverySource.db_type = 0;
            }
            removeAddClusterInstanceNode(treeNode);
        });
    };

    const removeAddClusterInstanceNode = function (treeNode) {
        let selectedKey;
        switch (treeNode.eventtype) {
            case 'cluster':
                selectedKey = treeNode.cluster_uuid;
                break;
            case 'instance':
                selectedKey = treeNode.agent_uuid + '_' + treeNode.db_uuid;
                break;
            case 'db':  // 分为集群和单机
                if (treeNode.cluster_flag) {
                    selectedKey = treeNode.cluster_uuid + '_' + treeNode.db_uuid;
                } else {
                    selectedKey = treeNode.agent_uuid + '_' + treeNode.db_uuid;
                }
                break;
            default:
                selectedKey = treeNode.job_uuid + '_' + treeNode.agent_uuid + '_' + treeNode.db_uuid;
                break;
        }
        pointtypetree.checkNode(treeNode, false, false, false);
        $('#' + selectedKey).remove();
    }

    /**
     * 显示时间点加密密码输入框弹窗
     * @param treeNode
     */
    const showTimepointEncryptBootbox = treeNode => {
        recoverySource.timepoint_encrypt_password_map[treeNode.timepoint_uuid] = '';
        if (treeNode.eventtype !== 'timepoint') {
            return;
        }
        if (!treeNode.is_encrypted) {
            return;
        }
        if (treeNode.config.password_auto_flag) {
            return;
        }
        // 选择了备份点，显示加密密码
        bootbox.dialog({
            title: LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS,
            message: `<div class="bootbox-input-wrapper" style="position: relative;margin-bottom: 15px;">
                            <input class="bootbox-input bootbox-input-password" type="password" autocomplete="off"
                                style="border: 1px solid #E6E6E6;border-radius: 2px !important;height: 34px;width:100%;background-color: #FFFFFF;padding: 6px 12px"
                                oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\\s+/g,'')" />
                            <button type="button" class="btn btn-link show-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                                <i class="viconfont vicon-a-lujing8232"></i>
                            </button>
                        </div>`,
            buttons: {
                cancel: {
                    label: LANG.UI_PUBLIC_CANCEL,
                    className: 'btn btn-default',
                    callback: function () {
                        pointtypetree.checkNode(treeNode, false, true, true);
                        return;
                    }
                },
                confirm: {
                    label: LANG.UI_PUBLIC_CONFIRM,
                    className: 'btn btn-primary',
                    callback: debounce(function () {
                        let result = $('.bootbox-input-password').val();
                        if (!result) {
                            return false;
                        }
                        if (!isNotLatinCode(result)) {
                            return false;
                        }
                        if (!validEncryptPassword(result, treeNode.timepoint_uuid)) {
                            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_DATA_ENCRYPT_PASSWORD_ERROR);
                            return false;
                        }
                        recoverySource.timepoint_encrypt_password_map[treeNode.timepoint_uuid] = result;
                        $(this).modal('hide');
                    }, 300, false),
                }
            }
        }).on('shown.bs.modal', function () {
            // 点击关闭按钮
            $(this).find('button.bootbox-close-button.close').on('click', () => {
                pointtypetree.checkNode(treeNode, false, true, true);
            });
            // 获取输入框和按钮
            let $input = $(this).find('.bootbox-input-password');
            let $btn = $(this).find('.show-password-btn');

            // 添加点击事件监听器
            $btn.on('click', function () {
                let inputType = $input.attr('type');
                if (inputType === 'password') {
                    $input.attr('type', 'text');
                    $btn.find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
                } else {
                    $input.attr('type', 'password');
                    $btn.find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
                }
            });
        });
    };

    /**
     * 选中了集群实例的时间点树
     */
    const checkClusterInstancePointTreeNode = (ev, treeId, treeNode) => {
        /**
         * 可以选中的有
         * Oracle/TiDB的集群、单机实例  cluster instance
         * SAP HANA的数据库  db
         * 其他数据库的备份点 timepoint
         */
        if (
            treeNode.eventtype !== 'cluster' &&
            treeNode.eventtype !== 'instance' &&
            treeNode.eventtype !== 'db' &&
            treeNode.eventtype !== 'timepoint'
        ) {
            return;
        }
        checkClusterInstancePointStorageTypePoint(treeId, treeNode); //磁带需求，磁带备份点和非磁带备份点互斥，不可同时选择
        let dbType = parseInt(treeNode.db_type);  // 数据库类别
        // 选择备份点的键，根据任务uuid、客户端uuid、实例名、数据库名确保选择同一个实例/数据库的备份只选择一个
        let selectedKey;  // 这里的selectedKey与节点ID定义不同，这里的key要做html的id的，因此不能包含特殊字符
        switch (treeNode.eventtype) {
            case 'cluster':
                selectedKey = treeNode.cluster_uuid;
                break;
            case 'instance':
                selectedKey = treeNode.agent_uuid + '_' + treeNode.db_uuid;
                break;
            case 'db':  // 分为集群和单机
                if (treeNode.cluster_flag) {
                    selectedKey = treeNode.cluster_uuid + '_' + treeNode.db_uuid;
                } else {
                    selectedKey = treeNode.agent_uuid + '_' + treeNode.db_uuid;
                }
                break;
            default:
                selectedKey = treeNode.job_uuid + '_' + treeNode.agent_uuid + '_' + treeNode.db_uuid;
                break;
        }
        let allNodes = pointtypetree.getCheckedNodes(true);
        if (!judgeCheckIsInSingleNode(treeNode, allNodes)) {
            $('#VMGroupList').html('');
            addCheckedClusterInstanceNode(treeNode);
            recoverySource.db_type = dbType;
            recoverySource.time_point_list = {};  // 这里需要清空，不然跨节点会遗留备份点
            recoverySource.timepoint_encrypt_password_map = {};
            recoverySource.time_point_list[selectedKey] = treeNode;
            recoverySource.agent_uuid = treeNode.agent_uuid;
            recoverySource.task_uuid = treeNode.job_uuid;
            showTimepointEncryptBootbox(treeNode);
            return;
        }

        if (!treeNode.checked) {  // 取消勾选
            removeAddClusterInstanceNode(treeNode);
            delete recoverySource.time_point_list[selectedKey];
            if (treeNode.eventtype === 'timepoint') {
                delete recoverySource.timepoint_encrypt_password_map[treeNode.timepoint_uuid];
            }
            if (JSON.stringify(recoverySource.time_point_list) === '{}') {
                recoverySource.db_type = 0;
            }
            return;
        }

        for (const checkedNode of allNodes) {  // 先取消勾选所有节点，移除所有选择
            removeAddClusterInstanceNode(checkedNode);
        }
        switch (dbType) {
            case CONF.DB_TYPE.SQLSERVER:
                if (recoverySource.db_type !== dbType) {
                    // 表示之前选择的不是SQL server或同一个任务
                    recoverySource.time_point_list = {};
                    recoverySource.timepoint_encrypt_password_map = {};
                }
                // 同一个数据库不能有多个恢复点
                let deleteKeyList = [];
                for (const key in recoverySource.time_point_list) {
                    let oldTreeNode = recoverySource.time_point_list[key];
                    if (oldTreeNode.cluster_flag) {
                        if (oldTreeNode.cluster_uuid === treeNode.cluster_uuid && oldTreeNode.db_name == treeNode.db_name) {
                            deleteKeyList.push(key);
                        }
                    } else {
                        if (
                            oldTreeNode.agent_uuid === treeNode.agent_uuid &&
                            oldTreeNode.instance_name === treeNode.instance_name &&
                            oldTreeNode.db_name == treeNode.db_name
                        ) {
                            deleteKeyList.push(key);
                        }
                    }
                }
                for (const deleteKey of deleteKeyList) {
                    delete recoverySource.time_point_list[deleteKey];
                    if (treeNode.eventtype === 'timepoint') {
                        delete recoverySource.timepoint_encrypt_password_map[treeNode.timepoint_uuid];
                    }
                }
                break;
            case CONF.DB_TYPE.SAPHANA:
                if (recoverySource.db_type !== dbType) {
                    // 表示之前选择的不是SQL server或同一个任务
                    recoverySource.time_point_list = {};
                    recoverySource.timepoint_encrypt_password_map = {};
                }
                break;
            default:  // 其他类别的数据库，仅支持单选备份点
                recoverySource.time_point_list = {};
                recoverySource.timepoint_encrypt_password_map = {};
                break;
        }

        recoverySource.db_type = dbType;
        recoverySource.time_point_list[selectedKey] = treeNode;
        for (const key in recoverySource.time_point_list) {
            pointtypetree.checkNode(recoverySource.time_point_list[key], true, false, false);
            addCheckedClusterInstanceNode(recoverySource.time_point_list[key]);
        }
        recoverySource.agent_uuid = treeNode.agent_uuid;
        recoverySource.task_uuid = treeNode.job_uuid;
        recoverySource.cluster_flag = !!treeNode.cluster_flag;
        recoverySource.cluster_uuid = treeNode.cluster_uuid;
        showTimepointEncryptBootbox(treeNode);
    }

    /**
     * 展开集群实例的时间点树
     */
    const expandClusterInstancePointTreeNode = (treeId, treeNode) => {
        if (treeNode.eventtype !== 'task' || treeNode.expand_children_flag) {
            return;
        }
        // 展开备份点
        Metronic.blockUI({target: '#pointtypetree', animate: true});
        let reqData = {
            db_type: treeNode.db_type,
            with_copy_flag: 1,
            with_copy_back_flag: 1,
            agent_list: [{
                cluster_flag: treeNode.cluster_flag,
                cluster_uuid: treeNode.cluster_uuid,
                agent_uuid: treeNode.agent_uuid,
                instance_name: treeNode.instance_name,
                db_name: treeNode.db_name,
                job_uuid: treeNode.job_uuid,
            }],
            storage_uuid: $('#timepointStorageSelect').val(),
        };
        pAjaxRequest(reqData, `/api/v1/db/jobs/backup/time_point_list`, 'GET', res => {
            Metronic.unblockUI('#pointtypetree');
            if (!res.success || !res.data.rows.length) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY, LANG.UI_DB_RECOVERY_OBTAIN_TIMEPOINT_ERROR)
                return;
            }

            let nodes = [];
            for (const row of res.data.rows) {
                let pId = treeNode.id;
                let icon = './img/platform/timepoint-f.png';
                let name = row.time_point + '（' + LANG.UI_DATA_TYPE_FULL + '）';
                let title = LANG.UI_DB_RECOVERY_SOURCE_DIR_PATH + row.dir_path;
                let chkDisabled = false;
                if (row.backup_mode != TIMEPOINT_TYPE_ENUM.FULL) {
                    pId = pId + '_' + row.full_time_point_uuid;
                    icon = './img/platform/timepoint.png';
                    name = row.time_point + '（' + LANG.UI_DATA_TYPE_LOG + '）';
                    if (
                        treeNode.db_type == CONF.DB_TYPE.DM ||  // 归档日志备份点
                        treeNode.db_type == CONF.DB_TYPE.ORACLE ||
                        treeNode.db_type == CONF.DB_TYPE.POSTGRE ||
                        treeNode.db_type == CONF.DB_TYPE.ANTDB ||
                        treeNode.db_type == CONF.DB_TYPE.KINGBASE ||
                        treeNode.db_type == CONF.DB_TYPE.UXDB ||
                        treeNode.db_type == CONF.DB_TYPE.HIGHGO ||
                        treeNode.db_type == CONF.DB_TYPE.OPENGAUSS ||
                        treeNode.db_type == CONF.DB_TYPE.VASTBASE
                    ) {
                        name = row.time_point + '（' + LANG.UI_DATA_TYPE_ARCHIVELOG + '）';
                    }
                    if (row.backup_mode == TIMEPOINT_TYPE_ENUM.INCR) {
                        icon = './img/platform/timepoint-i.png';
                        name = row.time_point + '（' + LANG.UI_DATA_TYPE_INCR + '）';
                    } else if (row.backup_mode == TIMEPOINT_TYPE_ENUM.DIFF) {
                        icon = './img/platform/timepoint-d.png';
                        name = row.time_point + '（' + LANG.UI_DATA_TYPE_DIFF + '）';
                    }
                }
                // 加密备份点,仅支持手动加密
                if (row.is_encrypted && !row.config.password_auto_flag) {
                    name += `<i class="fa fa-lock"></i>`;
                }
                // 存储离线
                if (parseInt(row.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                    chkDisabled = true;
                    name += '(' + LANG.UI_PUBLIC_STORAGE_OFFLINE + ')';
                }
                // 备份点不可用
                if (!row.timepoint_status.avaliable_flag) {
                    chkDisabled = true;
                    title += '(' + row.timepoint_status.status_des + ')';
                } else if (row.timepoint_status.avaliable_flag) {
                    if (parseInt(row.timepoint_status.status) === 3) {  // 异常
                        title += '(' + row.timepoint_status.status_des + ')';
                    }
                }
                nodes.push({
                    id: pId + '_' + row.time_point_uuid,
                    pId,
                    name,
                    title,
                    eventtype: 'timepoint',
                    open: false,
                    nocheck: false,
                    chkDisabled,
                    icon,
                    backup_mode: row.backup_mode,
                    agent_uuid: row.agent_uuid,
                    time_point: row.time_point,
                    db_type: row.db_type,
                    job_uuid: row.job_uuid,
                    instance_name: row.instance_name,
                    db_name: row.db_name,
                    db_uuid: row.db_uuid,
                    dir_path: row.dir_path,
                    is_encrypted: row.is_encrypted,
                    config: row.config,
                    cluster_flag: row.is_cluster,
                    cluster_uuid: row.cluster_uuid,
                    db_config: row.db_config,
                    timepoint_uuid: row.time_point_uuid,
                    full_timepoint_uuid: row.full_time_point_uuid,
                    storage_info: row.storage_info,
                    log_start_time_point: row.src_start_time_point,
                    log_end_time_point: row.src_end_time_point,
                    storage_status: row.storage_info.storage_status,
                    integrity_check_flag: row.integrity_check_flag,
                    timepoint_status: row.timepoint_status,
                });
            }
            $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, nodes, true);
            treeNode.expand_children_flag = true;
            $.fn.zTree.getZTreeObj(treeId).updateNode(treeNode);
            // 选中节点
            if (externalId) {
                let targetNode = pointtypetree.getNodeByParam('timepoint_uuid', externalPointUuid);
                if (targetNode) {
                    pointtypetree.checkNode(targetNode, true, true, true);
                    if (targetNode.backup_mode != TIMEPOINT_TYPE_ENUM.FULL) {
                        pointtypetree.expandNode(targetNode.getParentNode(), true, true, true, true);
                    } else {
                        pointtypetree.expandNode(targetNode, true, true, true, true);
                    }
                }
            }
        });
    };

    /**
     * 添加集群实例的时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
    const addClusterInstancePointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.eventtype !== 'timepoint') {
            return;
        }
        if ($(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).length) {
            return;
        }
        let sObj = $(`#${treeNode.tId}_span`);
        sObj.after(`<span id="${treeNode.tId}_${treeNode.timepoint_uuid}" title="${LANG.UI_BACKUP_DATA_POINT_DETAIL_TITLE}"><i class='viconfont vicon-Frame11'></i></span>`);
        // 注册点击事件
        $(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).on("click", (ev) => {
            ev.stopPropagation();  // 阻止click事件向上冒泡
            $('.page-content').initPointDetailDrawer({timepoint_uuid: treeNode.timepoint_uuid});
        });
    };

    /**
     * 移除集群实例的时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
    const removeClusterInstancePointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.eventtype !== 'timepoint') {
            return;
        }
        $(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).off().remove();
    };

    /**
     * 获取集群实例的时间点树的设置
     */
    const getClusterInstancePointTreeSetting = () => {
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
                    title: "title"
                }
            },
            callback: {
                beforeClick: clickClusterInstancePointTreeNode,
                onCheck: checkClusterInstancePointTreeNode,
                beforeExpand: expandClusterInstancePointTreeNode
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: (treeId, treeNode) => {
                    let style = {};
                    if (treeNode.eventtype === 'cluster_instance') {
                        // style = {'padding-left': '16px'};  // 这个是设置集群节点的缩进
                    }
                    if (treeNode.eventtype === 'timepoint') {
                        if (parseInt(treeNode.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                            style = {'color': 'grey'};
                        } else if (!treeNode.timepoint_status.avaliable_flag) {  // 操作中
                            style = {'color': '#F19F00!important'};
                        } else if (treeNode.timepoint_status.avaliable_flag) {
                            if (parseInt(treeNode.timepoint_status.status) === 3) {
                                style = {'color': '#F1416C!important'};
                            }
                        }
                    }
                    return style;
                },
                addHoverDom: addClusterInstancePointTreeHoverDom,
                removeHoverDom: removeClusterInstancePointTreeHoverDom,
            }
        };
    };

    /**
     * 根据外部数据获取目标节点
     */
    const getExternalId = () => {
        /**
         * 对象说明
         * Oracle/TIDB: 选择到集群或单机实例实例, 因此ID为
         *   集群: cluster_<db_type>_<cluster_uuid>
         *   单机: standalone_<db_type>_<agent_uuid>_<instance_name>
         * SAP HANA: 选择到数据库, 但有单机和集群的区别，因此ID为
         *   集群: cluster_<db_type>_<cluster_uuid>_cluster_instance_<db_name>
         *   单机: standalone_<db_type>_<agent_uuid>_<instance_name>_<db_name>
         * SQL Serevr: 选择到备份点，多了一层数据库名，因此ID为
         *   集群: cluster_<db_type>_<cluster_uuid>_cluster_instance_<db_name>_<job_uuid>
         *   单机: standalone_<db_type>_<agent_uuid>_<instance_name>_<db_name>_<job_uuid>
         * 其他数据库: 选择到备份点，因此ID为
         *   集群: cluster_<db_type>_<cluster_uuid>_cluster_instance_<job_uuid>
         *   单机: standalone_<db_type>_<agent_uuid>_<instance_name>_<job_uuid>
         *
         * 其他说明
         *   1. Oracle、TiDB、SAP HANA：恢复源为备份对象，因此不需要找到实际的备份点, 备份点在步骤3才能确定
         *   2. 其他数据库是精确到任务这一层，需要加载任务下的备份点
         *
         * 参数说明
         * externalPointUuid: 备份点uuid
         * externalTaskUuid: 任务uuid
         * externalItemUuid: 备份对象uuid
         * externalSubType: 数据库类型
         * externalInstanceName: 实例名称
         * externalDbName: 数据库名
         * externalAgentUuid: 客户端uuid
         */
        let externalId = '';
        if (!externalPointUuid.length) {
            return externalId;
        }
        if (externalClusterUuid.length) {  // 集群
            switch (externalSubType) {
                case CONF.DB_TYPE.ORACLE:
                case CONF.DB_TYPE.TIDB:
                    externalId = 'cluster_' + externalSubType + '_' + externalClusterUuid;
                    break;
                case CONF.DB_TYPE.SAPHANA:
                    externalId = 'cluster_' + externalSubType + '_' + externalClusterUuid + '_' + externalDbName;
                    break;
                case CONF.DB_TYPE.SQLSERVER:
                    externalId = 'cluster_' + externalSubType + '_' + externalClusterUuid + '_' + externalDbName + '_' + externalTaskUuid;
                    break;
                default:
                    externalId = 'cluster_' + externalSubType + '_' + externalClusterUuid + '_' + externalTaskUuid;
                    break;
            }
        } else {  // 单机
            switch (externalSubType) {
                case CONF.DB_TYPE.ORACLE:
                case CONF.DB_TYPE.TIDB:
                    externalId = 'standalone_' + externalSubType + '_' + externalAgentUuid + '_' + externalInstanceName;
                    break;
                case CONF.DB_TYPE.SAPHANA:
                    externalId = 'standalone_' + externalSubType + '_' + externalAgentUuid + '_' + externalInstanceName + '_' + externalDbName;
                    break;
                case CONF.DB_TYPE.SQLSERVER:
                    externalId = 'standalone_' + externalSubType + '_' + externalAgentUuid + '_' + externalInstanceName + '_' + externalDbName + '_' + externalTaskUuid;
                    break;
                default:
                    externalId = 'standalone_' + externalSubType + '_' + externalAgentUuid + '_' + externalInstanceName + '_' + externalTaskUuid;
                    break;
            }
        }
        return externalId;
    };

    /**
     * 设置集群实例的时间点树
     */
    const setClusterInstancePointTree = clusterInstanceData => {
        /**
         * 显示层级
         * SQL Server
         * 分类 集群/单机
         *   数据库分类(SQL Server)
         *     集群/实例
         *      (集群节点)
         *       数据库
         *         任务
         *           完备点
         *             依赖点
         * Oracle
         * 分类 集群/单机
         *   数据库分类(SQL Server)
         *     集群/实例
         *      (集群节点)
         * DM
         * 分类 集群/单机
         *   数据库分类(SQL Server)
         *     集群/实例
         *      (集群节点)
         *       任务
         *         完备点
         *           依赖点
         */
        let clusterTypeList = {};
        let standaloneTypeList = {};
        let clusterList = [];
        let standaloneList = [];
        let nodes = [];
        for (const instanceInfo of clusterInstanceData.instance_data) {
            if (instanceInfo.is_cluster) {
                clusterTypeList[instanceInfo.db_type] = 1;
                clusterList.push(instanceInfo);
            } else {
                standaloneTypeList[instanceInfo.db_type] = 1;
                standaloneList.push(instanceInfo);
            }
        }
        // 分类
        if (Object.values(clusterTypeList).length) {
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
        if (Object.values(standaloneTypeList).length) {
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
        // 数据库类别
        for (const dbTypeInfo of clusterInstanceData.db_type_data) {
            if (typeof clusterTypeList[dbTypeInfo.db_type] !== 'undefined') {
                nodes.push({
                    id: 'cluster_' + dbTypeInfo.db_type,
                    pId: 'cluster',
                    name: dbTypeInfo.db_type_name,
                    title: dbTypeInfo.db_type_name,
                    open: true,
                    nocheck: true,
                    icon: './img/vm/host.png',
                    isParent: true,
                    eventtype: 'db_type',
                });
            }
            if (typeof standaloneTypeList[dbTypeInfo.db_type] !== 'undefined') {
                nodes.push({
                    id: 'standalone_' + dbTypeInfo.db_type,
                    pId: 'standalone',
                    name: dbTypeInfo.db_type_name,
                    title: dbTypeInfo.db_type_name,
                    open: true,
                    nocheck: true,
                    icon: './img/vm/host.png',
                    isParent: true,
                    eventtype: 'db_type',
                });
            }
        }
        // 集群实例
        for (const clusterInfo of clusterList) {
            let clusterShowName = clusterInfo.cluster_name;
            let clusterNocheck = true;
            switch (clusterInfo.db_type) {
                case CONF.DB_TYPE.ORACLE:  // Oracle集群显示为 集群应用名 或 集群应用名(集群别名)
                    clusterShowName = clusterInfo.db_name;
                    if (clusterInfo.cluster_name) {
                        clusterShowName = clusterInfo.db_name + '(' + clusterInfo.cluster_name + ')';
                    }
                    clusterNocheck = false;
                    break;
                case CONF.DB_TYPE.TIDB:
                    clusterNocheck = false;
                    clusterShowName = clusterInfo.instance_name + '(' + clusterInfo.cluster_name + ')';
                    break;
            }
            nodes.push({
                id: 'cluster_' + clusterInfo.db_type + '_' + clusterInfo.cluster_uuid,
                pId: 'cluster_' + clusterInfo.db_type,
                name: clusterShowName,
                title: clusterShowName,
                open: false,
                nocheck: clusterNocheck,
                icon: './img/platform/db-cluster.png',
                isParent: true,
                eventtype: 'cluster',
                cluster_flag: true,
                cluster_uuid: clusterInfo.cluster_uuid,
                db_type: clusterInfo.db_type,
                instance_name: clusterInfo.instance_name,
                dir_path: clusterInfo.dir_path,
                agent_uuid: clusterInfo.agent_uuid,
                backup_instance_list: clusterInfo.backup_instance_list,
            });
            for (let clusterNodeIndex in clusterInfo.cluster_node_list) {
                clusterNodeIndex = parseInt(clusterNodeIndex);
                let clusterNodeInfo = clusterInfo.cluster_node_list[clusterNodeIndex];
                let agentShowName = clusterNodeInfo.name + '(' + clusterNodeInfo.ip + ')';
                let clusterNodeIsParent = false;
                let lastClusterNodeFlag = false;
                let clusterInstanceId = 'cluster_' + clusterInfo.db_type + '_' + clusterInfo.cluster_uuid + '_' + clearString(clusterNodeInfo.ip);
                if (clusterNodeIndex === clusterInfo.cluster_node_list.length - 1) {  // 集群的最后一个才可以展开
                    clusterNodeIsParent = false;
                    lastClusterNodeFlag = true;
                    clusterInstanceId = 'cluster_' + clusterInfo.db_type + '_' + clusterInfo.cluster_uuid + '_cluster_instance';  // 特殊ID，方便寻址
                }
                switch (clusterInfo.db_type) {
                    case CONF.DB_TYPE.ORACLE:  // 不需要选择备份点
                    case CONF.DB_TYPE.TIDB:
                        clusterNodeIsParent = false;
                        break;
                }
                nodes.push({
                    id: clusterInstanceId,
                    pId: 'cluster_' + clusterInfo.db_type + '_' + clusterInfo.cluster_uuid,
                    name: agentShowName,
                    title: agentShowName,
                    open: true,
                    nocheck: true,
                    icon: './img/platform/storage.png',
                    isParent: clusterNodeIsParent,
                    eventtype: 'cluster_instance',
                    db_type: clusterInfo.db_type,
                    db_uuid: clusterInfo.db_uuid,
                    last_cluster_node_flag: lastClusterNodeFlag,
                });
            }
        }
        // 单机实例
        for (const standaloneInfo of standaloneList) {
            let standaloneShowName = standaloneInfo.instance_name + '(' + standaloneInfo.agent_ip + ')';
            let standaloneNodeIsParent = true;
            let standaloneNocheck = true;
            switch (standaloneInfo.db_type) {
                case CONF.DB_TYPE.ORACLE:  // 不需要选择备份点
                case CONF.DB_TYPE.TIDB:
                    standaloneNodeIsParent = false;
                    standaloneNocheck = false;
                    break;
            }
            nodes.push({
                id: 'standalone_' + standaloneInfo.db_type + '_' + standaloneInfo.agent_uuid + '_' + standaloneInfo.instance_name,
                pId: 'standalone_' + standaloneInfo.db_type,
                name: standaloneShowName,
                title: standaloneShowName,
                open: false,
                nocheck: standaloneNocheck,
                icon: './img/platform/storage.png',
                isParent: standaloneNodeIsParent,
                eventtype: 'instance',
                db_type: standaloneInfo.db_type,
                agent_uuid: standaloneInfo.agent_uuid,
                instance_name: standaloneInfo.instance_name,
                dir_path: standaloneInfo.dir_path,
                db_uuid: standaloneInfo.db_uuid,
                cluster_flag: false,
                cluster_uuid: '',
            });
        }
        // SQL Server 和 SAP HANA的数据库
        for (const dbInfo of clusterInstanceData.db_data) {
            if (dbInfo.db_type != CONF.DB_TYPE.SQLSERVER && dbInfo.db_type != CONF.DB_TYPE.SAPHANA) {
                continue;
            }
            let pId = 'standalone_' + dbInfo.db_type + '_' + dbInfo.agent_uuid + '_' + dbInfo.instance_name;
            if (dbInfo.is_cluster) {  // 集群的数据库与集群节点同级
                pId = 'cluster_' + dbInfo.db_type + '_' + dbInfo.cluster_uuid;
            }
            let dbNocheck = true;
            let dbIsParent = true;
            if (dbInfo.db_type == CONF.DB_TYPE.SAPHANA) {  // SAP HANA 不显示备份点
                dbNocheck = false;
                dbIsParent = false;
            }
            nodes.push({
                id: pId + '_' + dbInfo.db_name,
                pId: pId,
                name: dbInfo.db_name,
                title: dbInfo.db_name,
                open: true,
                nocheck: dbNocheck,
                icon: './img/vm/host.png',
                isParent: dbIsParent,
                eventtype: 'db',
                db_type: dbInfo.db_type,
                cluster_flag: dbInfo.is_cluster,
                cluster_uuid: dbInfo.cluster_uuid,
                agent_uuid: dbInfo.agent_uuid,
                instance_name: dbInfo.instance_name,
                db_name: dbInfo.db_name,
                db_uuid: dbInfo.db_uuid,
                backup_instance_list: dbInfo.backup_instance_list,
                dir_path: dbInfo.dir_path,
            });
        }
        // 构建任务下的实例和数据库
        let jobMap = {};
        for (const jobInfo of clusterInstanceData.job_data) {
            if (typeof jobMap[jobInfo.job_uuid] === 'undefined') {
                jobMap[jobInfo.job_uuid] = {
                    job_uuid: jobInfo.job_uuid,
                    job_name: jobInfo.job_name,
                    db_type: jobInfo.db_type,
                    create_time: jobInfo.create_time,
                    job_type: jobInfo.job_type,  // 用于区分数据库备份任务和副本任务
                    is_del: jobInfo.is_del,
                    instance_list: [],
                    db_list: [],
                };
            }
            // 实例区分：集群：cluster_uuid, 单机：agent_uuid/实例名
            for (const instanceInfo of clusterInstanceData.instance_data) {
                if (instanceInfo.job_uuid_list.includes(jobInfo.job_uuid)) {
                    jobMap[jobInfo.job_uuid].instance_list.push(instanceInfo);
                }
            }
            // 数据库区分(SQL Server、SAP HANA)：集群：cluster_uuid/数据库名, 单机：agent_uuid/实例名/数据库名
            for (const dbInfo of clusterInstanceData.db_data) {
                if (dbInfo.job_uuid_list.includes(jobInfo.job_uuid)) {
                    jobMap[jobInfo.job_uuid].db_list.push(dbInfo);
                }
            }
        }
        // 任务
        for (const jobUuid in jobMap) {
            let jobInfo = jobMap[jobUuid];
            let jobName = jobInfo.job_name;
            if (jobInfo.is_del) {
                jobName += '(' + LANG.UI_PUBLIC_TASK_DELETED + ')';
            }
            if (
                jobInfo.db_type == CONF.DB_TYPE.ORACLE ||
                jobInfo.db_type == CONF.DB_TYPE.TIDB ||
                jobInfo.db_type == CONF.DB_TYPE.SAPHANA
            ) {  // 不显示备份点
                continue;
            }
            if (jobInfo.db_type == CONF.DB_TYPE.SQLSERVER) {  // 展开数据库显示备份点
                for (const dbInfo of jobInfo.db_list) {
                    let pId = 'standalone_' + dbInfo.db_type + '_' + dbInfo.agent_uuid + '_' + dbInfo.instance_name + '_' + dbInfo.db_name;
                    if (dbInfo.is_cluster) {
                        pId = 'cluster_' + dbInfo.db_type + '_' + dbInfo.cluster_uuid + '_' + dbInfo.db_name;
                    }
                    nodes.push({
                        id: pId + '_' + jobUuid,
                        pId: pId,
                        name: jobName,
                        title: jobInfo.job_name,
                        open: false,
                        nocheck: true,
                        icon: './img/platform/flag.png',
                        isParent: true,
                        eventtype: 'task',
                        expand_children_flag: false,
                        db_type: jobInfo.db_type,
                        cluster_flag: dbInfo.is_cluster,
                        cluster_uuid: dbInfo.cluster_uuid,
                        backup_instance_list: dbInfo.backup_instance_list,
                        agent_uuid: dbInfo.agent_uuid,
                        instance_name: dbInfo.instance_name,
                        db_name: dbInfo.db_name,
                        db_uuid: dbInfo.db_uuid,
                        job_uuid: jobUuid,
                    });
                }
            } else {  // 展开实例显示备份点
                for (const instanceInfo of jobInfo.instance_list) {
                    let pId = 'standalone_' + instanceInfo.db_type + '_' + instanceInfo.agent_uuid + '_' + instanceInfo.instance_name;
                    if (instanceInfo.is_cluster) {  // 集群
                        pId = 'cluster_' + instanceInfo.db_type + '_' + instanceInfo.cluster_uuid;
                    }
                    nodes.push({
                        id: pId + '_' + jobUuid,
                        pId: pId,
                        name: jobName,
                        title: jobInfo.job_name,
                        open: false,
                        nocheck: true,
                        icon: './img/platform/flag.png',
                        isParent: true,
                        eventtype: 'task',
                        expand_children_flag: false,
                        db_type: jobInfo.db_type,
                        cluster_flag: instanceInfo.is_cluster,
                        cluster_uuid: instanceInfo.cluster_uuid,
                        backup_instance_list: instanceInfo.backup_instance_list,
                        agent_uuid: instanceInfo.agent_uuid,
                        instance_name: instanceInfo.instance_name,
                        db_name: instanceInfo.db_name,
                        db_uuid: instanceInfo.db_uuid,
                        job_uuid: jobUuid,
                    });
                }
            }
        }
        pointtypetree = $.fn.zTree.init($("#pointtypetree"), getClusterInstancePointTreeSetting(), nodes);
        // 从备份数据跳转恢复页面，展开对象下的时间点
        externalId = getExternalId();
        if (externalId) {
            let targetNode = pointtypetree.getNodeByParam('id', externalId);
            if (targetNode) {
                switch (externalSubType) {
                    case CONF.DB_TYPE.ORACLE:
                    case CONF.DB_TYPE.TIDB:
                        pointtypetree.checkNode(targetNode, true, true, true);
                        if (targetNode.eventtype === 'cluster') {
                            pointtypetree.expandNode(targetNode, true, true, true, true);
                        } else {
                            pointtypetree.expandNode(targetNode.getParentNode(), true, true, true, true);  // 这个是为了让它聚焦到屏幕上
                        }
                        break;
                    case CONF.DB_TYPE.SAPHANA:
                        pointtypetree.checkNode(targetNode, true, true, true);
                        if (targetNode.cluster_flag) {  // SAP选择到db，集群的父节点是集群节点，再父节点才是集群
                            pointtypetree.expandNode(targetNode.getParentNode(), true, true, true, true);
                            pointtypetree.expandNode(targetNode.getParentNode().getParentNode(), true, true, true, true);
                        } else {  // 单机的父节点就是单机实例节点
                            pointtypetree.expandNode(targetNode.getParentNode(), true, true, true, true);
                        }
                        break;
                    default:
                        pointtypetree.expandNode(targetNode, true, true, true, true);
                        break;
                }
            }
        }
    };

    /**
     * 初始化集群实例的时间点树
     */
    const initClusterInstancePointTree = () => {
        if (pointtypetree) {
            pointtypetree.destroy();
            pointtypetree = null;
        }

        let dbType = $('#pointDbTypeSelect').val();
        dbType = dbType ? parseInt(dbType) : 0;
        let reqData = {
            with_copy_data: true,
            recovery_flag: true,
            storage_uuid: $('#timepointStorageSelect').val(),
            db_type: dbType,
        };

        Metronic.blockUI({target: '.two_tree', animate: true});
        pAjaxRequest(reqData, `/api/v1/db/jobs/backup/data`, 'GET', res => {
            Metronic.unblockUI('.two_tree');
            hideStep1Tips();
            if (!res.success || !res.data.db_data.length) {
                $("#nopointtips").show();
                $('#pointtypetree').hide();
                return;
            }
            $('#pointtypetree').show();
            $("#two_tree").show();

            setClusterInstancePointTree(res.data);
        });
    };

    //////////////////// 结束-恢复指定时间点的集群-实例树 ////////////////////

    /**
     * 恢复目标节点点击了
     * @param {*} treeId
     * @param {*} treeNode
     */
    const clickRecoveryTargetNode = (treeId, treeNode) => {
        hostTree.checkNode(treeNode, !treeNode.checked, true, true);
    };

    /**
     * 恢复目节点选中了
     * @param {*} ev
     * @param {*} treeId
     * @param {*} treeNode
     */
    const checkRecoveryTargetNode = (ev, treeId, treeNode) => {
        if (!treeNode.checked) {
            return;
        }
        _OSTYPE = treeNode.os_type; //获取父节点的系统类型

        let allCheckNodes = hostTree.getCheckedNodes(true);
        for (const checkNode of allCheckNodes) {
            hostTree.checkNode(checkNode, false, false, false);
        }
        hostTree.checkNode(treeNode, true, false, false);

        recoveryTarget.agent_uuid = treeNode.agent_uuid;
        recoveryTarget.instance_uuid = treeNode.instance_name;
        recoveryTarget.cluster_uuid = treeNode.cluster_uuid;
        recoveryTarget.cluster_flag = !!treeNode.cluster_flag;
        data.recoverInfo.desagentuuid = treeNode.agent_uuid;
        data.recoverInfo.cluster_uuid = treeNode.cluster_uuid;
        data.recoverInfo.cluster_flag = recoveryTarget.cluster_flag;

        // 回滚设置
        //如果选择的日志备份点显示日志备份时间填写
        if (CONF.DB_TYPE.MONGODB === recoverySource.db_type) {
            // MongoDB数据库的回滚时间根据start_time和end_time决定
            let timePointNode = data.pointInfo.points[0];
            if (timePointNode.log_start_time_point && timePointNode.log_end_time_point) {
                $('.logdateDiv').show();
                startTimepoint = getCurrentDatetimeStr(new Date(timePointNode.log_start_time_point * 1000));
                endTimepoint = getCurrentDatetimeStr(new Date(timePointNode.log_end_time_point * 1000));
                let des = LANG.UI_DB_RECOVERY_LOG_ROLL_TIME_RANGE + ": " + startTimepoint + " ~ " + endTimepoint;
                $('#rolltimeTips').html(des);  // 设置回滚时间描述
            } else {
                $('#logtimecheck').bootstrapSwitch('state', false);
                $('.logdateDiv').hide();
                $('.logdateInputDiv').hide();
                $('#rolltimeTips').empty();
            }
        } else {
            if (judgeShowRollbackTime()) {
                $('.logdateDiv').show();
            } else {
                $('#logtimecheck').bootstrapSwitch('state', false);
                $('.logdateDiv').hide();
                $('.logdateInputDiv').hide();
            }
        }
    };

    /**
     * 获取备份树的配置
     * @returns
     */
    const getRecoveryTargetTree = () => {
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
                beforeClick: clickRecoveryTargetNode,
                onCheck: checkRecoveryTargetNode,
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
                    // Oracle空实例
                    switch (treeNode.db_type) {
                        case CONF.DB_TYPE.ORACLE:
                            if (treeNode.eventtype === 'instance' && treeNode.instance_name === '') {
                                style = {'color': '#F19F00', 'font-weight': 'bold'};
                            }
                            break;
                        case CONF.DB_TYPE.SAPHANA:
                            if (treeNode.eventtype === 'instance' || treeNode.eventtype === 'cluster') {
                                if (treeNode.recovery_task_flag) {
                                    style = {'color': 'green', 'font-weight': 'bold'};
                                }
                            }
                            break;
                        case CONF.DB_TYPE.TIDB:
                            if (treeNode.eventtype === 'instance' || treeNode.eventtype === 'cluster') {
                                if (treeNode.running_backup_task_flag) {
                                    style = {'color': '#F19F00', 'font-weight': 'bold'};
                                }
                            }
                            break;
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
        clusterDbType,
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
                        db_type: clusterDbType,
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
                    db_type: clusterDbType,
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
                db_type: clusterDbType,
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
                    db_type: clusterDbType,
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
            db_type: clusterDbType,
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
                db_type: clusterDbType,
                group_uuid: clusterGroupUuid,
                group_name: clusterGroupName,
                app_detail: clusterAppDetail,
            });
        }

        return nodes;
    };

    /**
     * 设置恢复目标树
     * @param {*} instanceList
     */
    const setRecoveryTargetTree = (instanceList) => {
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

        /**
         * 特殊逻辑
         * 定时恢复最新备份点的恢复目标不能为原机
         *
         * Oracle:
         * 1. 定时恢复最新备份点不能恢复到空实例上
         *
         * SAP HANA
         * 1. 恢复目标不能有恢复任务
         *
         * TiDB
         * 1. 如果备份任务在运行中，那么不能用于恢复
         *
         * MongoDB
         * 1. 只能恢复到相同的部署方式里面(单机 => 单机, 主备 => 主备, 分片 => 分片)
         */

        let sourceClusterType = 0;
        if (recoverySource.db_type === CONF.DB_TYPE.MONGODB) {
            const sourceTimepoint = Object.values(recoverySource.time_point_list)[0];
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                sourceClusterType = sourceTimepoint.cluster_flag ? parseInt(sourceTimepoint.cluster_type) : 0;
            } else {
                const dbConfig = sourceTimepoint.db_config;
                if (
                    typeof dbConfig === 'object' &&
                    Array.isArray(dbConfig['backup_object_list']) &&
                    dbConfig['backup_object_list'].length > 0
                ) {
                    sourceClusterType = parseInt(dbConfig['backup_object_list'][0].cluster_type);
                }
            }
        }

        for (const standaloneNode of standaloneNodes) {
            let chkDisabled = false;
            let checked = false;
            let recoveryTaskFlag = false;
            let runningBackupTaskFlag = false;
            let name = standaloneNode.instance_name + '(' + standaloneNode.agent_info.ip + ')';
            if (!standaloneNode.agent_info.online_flag) {
                chkDisabled = true;
                name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
            }
            let title = name;

            // 定时恢复最新备份点的恢复目标不能为原机
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                for (const selectedKey in recoverySource.time_point_list) {
                    let selectedNode = recoverySource.time_point_list[selectedKey];
                    if (selectedNode.eventtype === 'instance') {
                        if (selectedNode.agent_uuid === standaloneNode.agent_info.agent_uuid) {
                            chkDisabled = true;
                        }
                    }
                }
            }

            switch (recoverySource.db_type) {
                case CONF.DB_TYPE.ORACLE:
                    // 定时恢复最新备份点不能恢复到空实例上
                    if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                        if (parseInt(standaloneNode.app_auth_type) === 1) {
                            chkDisabled = true;
                            title = LANG.UI_DB_RECOVERY_ORACLE_OS_AUTH_TARGET;
                        }
                    }
                    break;
                case CONF.DB_TYPE.SAPHANA:
                    // 恢复目标不能有恢复任务
                    if (editTaskFlag && oldRecoveryTaskData !== null) {
                        if (Array.isArray(standaloneNode.db_target_recovery_info) && standaloneNode.db_target_recovery_info.length > 0) {
                            if (standaloneNode.db_target_recovery_info[0].job_uuid !== oldRecoveryTaskData.job_uuid) {
                                chkDisabled = true;
                                recoveryTaskFlag = true;
                                title = LANG.UI_DB_RECOVERY_SAP_HANA_TASK_EXISTS.replace('%s', standaloneNode.db_target_recovery_info[0].job_name);
                            }
                        }
                    } else {
                        if (Array.isArray(standaloneNode.db_target_recovery_info) && standaloneNode.db_target_recovery_info.length > 0) {
                            chkDisabled = true;
                            recoveryTaskFlag = true;
                            title = LANG.UI_DB_RECOVERY_SAP_HANA_TASK_EXISTS.replace('%s', standaloneNode.db_target_recovery_info[0].job_name);
                        }
                    }
                    break;
                case CONF.DB_TYPE.TIDB:
                    // 如果备份任务在运行中，那么不能用于恢复
                    if (Array.isArray(standaloneNode.db_backup_info) && standaloneNode.db_backup_info.length > 0) {
                        let runningBackupTaskNameList = [];
                        for (const dbBackupInfo of standaloneNode.db_backup_info) {
                            if (dbBackupInfo.job_status == CONF.TASK_STATUS.RUNNING) {
                                chkDisabled = true;
                                runningBackupTaskFlag = true;
                                runningBackupTaskNameList.push(dbBackupInfo.job_name);
                            }
                        }
                        if (runningBackupTaskNameList.length) {
                            title = LANG.UI_DB_RECOVERY_TIDB_BACKUP_TASK_IS_RUNNING.replace('%s', "'" + runningBackupTaskNameList.join("'、'") + "'");
                        }
                    }
                    break;
                case CONF.DB_TYPE.MONGODB:
                    if (sourceClusterType !== MONGODB_CLUSTER_TYPE_ENUM.single) {
                        chkDisabled = true;
                    }
                    break;
                default:
                    break;
            }
            if (editTaskFlag && oldRecoveryTaskData !== null) {  // 修改恢复任务
                if (
                    !oldRecoveryTaskData.recovery_target.cluster_flag &&
                    oldRecoveryTaskData.recovery_target.agent_uuid === standaloneNode.agent_info.agent_uuid &&
                    oldRecoveryTaskData.recovery_target.instance_name === standaloneNode.instance_name
                ) {
                    checked = true;
                }
            }

            nodes.push({
                id: standaloneNode.app_uuid,
                pId: 'standalone',
                name,
                title,
                isParent: false,
                eventtype: 'instance',
                chkDisabled,
                checked,
                icon: './img/platform/storage.png',
                instance_name: standaloneNode.instance_name,
                app_uuid: standaloneNode.app_uuid,
                agent_uuid: standaloneNode.agent_info.agent_uuid,
                app_detail: standaloneNode.app_detail,
                cluster_flag: false,
                cluster_uuid: '',
                db_type: standaloneNode.db_type,
                online_flag: standaloneNode.agent_info.online_flag,
                net_model: standaloneNode.agent_info.net_model,
                db_backup_info: standaloneNode.db_backup_info,
                os_type: standaloneNode.agent_info.os_type,
                app_auth_type: standaloneNode.app_auth_type,
                recovery_task_flag: recoveryTaskFlag,
                running_backup_task_flag: runningBackupTaskFlag,
            });
        }
        for (const clusterUuid in clusterNodes) {
            let clusterInfo = clusterNodes[clusterUuid].clsuter_info;
            let clusterOnlineFlag = false;
            let clusterAllOnlineFlag = true;
            let clusterNodeOfflineAgentUuidList = [];
            let clusterOsType = 'Linux';
            let clusterDbType = 0;
            let clusterInstanceName = '';
            let clusterAgentUuid = '';
            let clusterAppUuid = '';
            let clusterGroupUuid = '';
            let clusterGroupName = '';
            let clusterType = parseInt(clusterInfo.cluster_type);
            let clusterAppDetail = {};
            let clusterDbTargetRecoveryInfo = [];
            let clusterRecoveryTaskFlag = false;
            let clusterDbBackupInfo = [];
            let clusterRunningBackupTaskFlag = false;
            for (const instanceInfo of clusterNodes[clusterUuid].instance_list) {
                let chkDisabled = false;
                let name = instanceInfo.instance_name + '(' + instanceInfo.agent_info.ip + ')';
                switch (recoverySource.db_type) {
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
                if (!clusterAgentUuid) {
                    clusterAgentUuid = instanceInfo.agent_info.agent_uuid;
                    clusterInstanceName = instanceInfo.instance_name;
                    clusterAppUuid = instanceInfo.app_uuid;
                    clusterDbTargetRecoveryInfo = instanceInfo.db_target_recovery_info;
                    clusterDbBackupInfo = instanceInfo.db_backup_info;
                }
                // 离线状态
                if (!instanceInfo.agent_info.online_flag) {  // 离线
                    clusterAllOnlineFlag = false;
                    chkDisabled = true;
                    name = `(${LANG.UI_VISUAL_OFF_LINE})` + name;
                    clusterNodeOfflineAgentUuidList.push(instanceInfo.agent_info.agent_uuid);
                } else {  // 需要全部离线才离线
                    if (recoverySource.db_type === CONF.DB_TYPE.ORACLE) {
                        if (!clusterAgentUuid) {
                            clusterOnlineFlag = true;
                            clusterInstanceName = instanceInfo.instance_name;
                            clusterAgentUuid = instanceInfo.agent_info.agent_uuid;
                            clusterAppUuid = instanceInfo.app_uuid;
                            clusterDbTargetRecoveryInfo = instanceInfo.db_target_recovery_info;
                            clusterDbBackupInfo = instanceInfo.db_backup_info;
                        } else {
                            if (instanceInfo.app_detail.thread_num == 1) {
                                clusterOnlineFlag = true;
                                clusterInstanceName = instanceInfo.instance_name;
                                clusterAgentUuid = instanceInfo.agent_info.agent_uuid;
                                clusterAppUuid = instanceInfo.app_uuid;
                                clusterDbTargetRecoveryInfo = instanceInfo.db_target_recovery_info;
                                clusterDbBackupInfo = instanceInfo.db_backup_info;
                            }
                        }
                    } else {
                        clusterOnlineFlag = true;
                        clusterInstanceName = instanceInfo.instance_name;
                        clusterAgentUuid = instanceInfo.agent_info.agent_uuid;
                        clusterAppUuid = instanceInfo.app_uuid;
                        clusterDbTargetRecoveryInfo = instanceInfo.db_target_recovery_info;
                        clusterDbBackupInfo = instanceInfo.db_backup_info;
                    }
                }
                clusterOsType = instanceInfo.agent_info.os_type;
                clusterGroupUuid = instanceInfo.agent_info.group_uuid;
                clusterGroupName = instanceInfo.agent_info.group_name;
                clusterDbType = parseInt(instanceInfo.db_type);
                clusterAppDetail = instanceInfo.app_detail;
                // MongoDB分片集群只要一个离线了，那么就不能用于备份
                if (recoverySource.db_type == CONF.DB_TYPE.MONGODB) {
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
                    cluster_flag: instanceInfo.cluster_info.cluster_flag,
                    cluster_uuid: instanceInfo.cluster_info.cluster_uuid,
                    online_flag: instanceInfo.agent_info.online_flag,
                    net_model: instanceInfo.agent_info.net_model,
                    db_backup_info: instanceInfo.db_backup_info,
                    os_type: instanceInfo.agent_info.os_type,
                });
            }

            let clusterChkDisabled = false;
            let clusterChecked = false;
            let clusterName = clusterInfo.cluster_name;
            switch (recoverySource.db_type) {
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
                            clusterDbType,
                            clusterGroupUuid,
                            clusterGroupName,
                            clusterType
                        );
                        nodes = nodes.concat(mongodbNodes);
                    }
                    if (sourceClusterType !== clusterType) {
                        clusterChkDisabled = true;
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
            } else {
                // PG系集群只要一个离线了，那么就不能用于恢复目标
                if (
                    recoverySource.db_type == CONF.DB_TYPE.POSTGRE ||
                    recoverySource.db_type == CONF.DB_TYPE.KINGBASE ||
                    recoverySource.db_type == CONF.DB_TYPE.OPENGAUSS ||
                    recoverySource.db_type == CONF.DB_TYPE.VASTBASE ||
                    recoverySource.db_type == CONF.DB_TYPE.UXDB ||
                    recoverySource.db_type == CONF.DB_TYPE.ANTDB ||
                    recoverySource.db_type == CONF.DB_TYPE.HIGHGO
                ) {
                    if (!clusterAllOnlineFlag) {
                        clusterChkDisabled = true;
                        clusterName = `(${LANG.UI_VISUAL_OFF_LINE})` + clusterName;
                    }
                }
            }
            if (editTaskFlag && oldRecoveryTaskData !== null) {  // 修改恢复任务
                if (oldRecoveryTaskData.recovery_target.cluster_flag && oldRecoveryTaskData.recovery_target.cluster_uuid === clusterInfo.cluster_uuid) {
                    clusterChecked = true;
                }
            }

            // 定时恢复最新备份点的恢复目标不能为原机
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                for (const selectedKey in recoverySource.time_point_list) {
                    let selectedNode = recoverySource.time_point_list[selectedKey];
                    if (selectedNode.eventtype === 'cluster') {
                        if (selectedNode.cluster_uuid === clusterInfo.cluster_uuid) {
                            clusterChkDisabled = true;
                        }
                    }
                }
            }

            let clusterTitle = clusterName;
            switch (recoverySource.db_type) {
                case CONF.DB_TYPE.SAPHANA:
                    // 恢复目标不能有恢复任务
                    if (Array.isArray(clusterDbTargetRecoveryInfo) && clusterDbTargetRecoveryInfo.length > 0) {
                        clusterChkDisabled = true;
                        clusterRecoveryTaskFlag = true;
                        clusterTitle = LANG.UI_DB_RECOVERY_SAP_HANA_TASK_EXISTS.replace('%s', clusterDbTargetRecoveryInfo[0].job_name);
                    }
                    break;
                case CONF.DB_TYPE.TIDB:
                    // 如果备份任务在运行中，那么不能用于恢复
                    if (Array.isArray(clusterDbBackupInfo) && clusterDbBackupInfo.length > 0) {
                        let runningBackupTaskNameList = [];
                        for (const dbBackupInfo of clusterDbBackupInfo) {
                            if (dbBackupInfo.job_status == CONF.TASK_STATUS.RUNNING) {
                                clusterChkDisabled = true;
                                clusterRunningBackupTaskFlag = true;
                                runningBackupTaskNameList.push(dbBackupInfo.job_name);
                            }
                        }
                        if (runningBackupTaskNameList.length) {
                            clusterTitle = LANG.UI_DB_RECOVERY_TIDB_BACKUP_TASK_IS_RUNNING.replace('%s', "'" + runningBackupTaskNameList.join("'、'") + "'");
                        }
                    }
                    break;
                default:
                    break;
            }

            nodes.push({
                id: clusterUuid,
                pId: 'cluster',
                name: clusterName,
                title: clusterTitle,
                isParent: true,
                open: true,
                eventtype: 'cluster',
                icon: './img/platform/db-cluster.png',
                chkDisabled: clusterChkDisabled,
                checked: clusterChecked,
                agent_uuid: clusterAgentUuid,
                instance_name: clusterInstanceName,
                instance_list: clusterNodes[clusterUuid].instance_list,
                cluster_flag: true,
                cluster_uuid: clusterInfo.cluster_uuid,
                cluster_name: clusterInfo.cluster_name,
                cluster_service_ip: clusterInfo.cluster_service_ip,
                app_service_name: clusterInfo.app_service_name,
                online_flag: clusterOnlineFlag,
                os_type: clusterOsType,
                group_name: clusterGroupName,
                group_uuid: clusterGroupUuid,
                db_type: clusterDbType,
                app_detail: clusterAppDetail,
                recovery_task_flag: clusterRecoveryTaskFlag,
                running_backup_task_flag: clusterRunningBackupTaskFlag,
            });
        }
        hostTree = $.fn.zTree.init($("#host_tree"), getRecoveryTargetTree(), nodes);
        if (editTaskFlag && oldRecoveryTaskData !== null) {
            let checkedNodes = hostTree.getCheckedNodes(true);
            for (const checkedNode of checkedNodes) {
                hostTree.checkNode(checkedNode, true, true, true);
            }
        }
    };

    /**
     * 初始化恢复目标树
     */
    const initRecoveryTargetTree = () => {
        Metronic.blockUI({target: '.createNewInstanceDiv', animate: true});
        Metronic.blockUI({target: '.host_tree_div', animate: true});
        pAjaxRequest({db_type: recoverySource.db_type}, `/api/v1/db/instances`, 'GET', res => {
            Metronic.unblockUI('.host_tree_div');
            Metronic.unblockUI('.createNewInstanceDiv');
            if (!res.success || !res.data.rows.length) {
                $('#nohosttips').show();
                $('.host_tree_div').hide();
                return;
            }
            $('#nohosttips').hide();
            $('.host_tree_div').show();
            setRecoveryTargetTree(res.data.rows);
        });
    };

    //替换特殊字符
    var clearString = function (s, flag) {
        var rs = "";
        var str = '_';
        if (flag) {
            str = '';
        }
        for (var i = 0; i < s.length; i++) {
            rs = rs + s.substr(i, 1).replace(_VMNAMEREG, str);
        }
        return rs;
    }

    var escapeJquery = function (srcString) {
        // 转义之后的结果
        var escapseResult = srcString.toString();
        // javascript正则表达式中的特殊字符
        var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
            "]", "|", "{", "}"
        ];
        // jquery中的特殊字符,不是正则表达式中的特殊字符
        var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
            ":", ";", "<", ">", ",", "/"
        ];
        for (let i = 0; i < jsSpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp("\\" +
                jsSpecialChars[i], "g"), "\\" +
                jsSpecialChars[i]);
        }
        for (let i = 0; i < jquerySpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                "g"), "\\" + jquerySpecialChars[i]);
        }
        return escapseResult;
    }

    //初始化虚拟机配置
    var initDBConfig = function () {
        let timePointNode = data.pointInfo.points[0];
        let info = {
            agentuuid: timePointNode.agentuuid,
            dbuuid: timePointNode.dbuuid,
            type: recoverySource.db_type,
            timepointuuid: timePointNode.timepointuuid,

        };
        $.post(CONF.AJAXPATH, {m: CONF.M.DBPROTECT, f: 'getDBOldConifg', p: JSON.stringify(info)}, function (d) {
            var jsonData = JSON.parse(d);
            if (judgeShowRollbackTime()) {
                var rangeDes = LANG.UI_DB_RECOVERY_LOG_ROLL_TIME_RANGE;
                if (recoverySource.db_type === CONF.DB_TYPE.ORACLE || recoverySource.db_type === CONF.DB_TYPE.DM) {
                    rangeDes = LANG.UI_DB_RECOVERY_ARCHIVE_LOG_ROLL_TIME_RANGE;
                }
                //初始化归档日志回滚时间参考范围,和时间插件
                var des = rangeDes + ": " + jsonData.starttimepoint + " ~ " + jsonData.endtimepoint;
                $('#rolltimeTips').html(des);
                startTimepoint = jsonData.starttimepoint;
                endTimepoint = jsonData.endtimepoint;
            } else {
                $('#rolltimeTips').empty();
            }
        });
    }

    var wizardInit = function () {
        if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('#tab1 .alert-danger, #tab2 .alert-danger, #tab4 .alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function (tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
            // set wizard title
            //            $('.step-title', $('#dbrecovercontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#dbrecovercontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current === 1) {
                $('#dbrecovercontent').find('.button-previous').css('visibility', 'hidden');
                $('#dbrecovercontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#dbrecovercontent').find('.button-previous').css('visibility', 'visible');
                $('#dbrecovercontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#dbrecovercontent').find('.button-next').hide();
                $('#dbrecovercontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#dbrecovercontent').find('.button-next').show();
                $('#dbrecovercontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#dbrecovercontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                let validResult = false;
                switch (index) {
                    case 1:
                        validResult = step1Valid(() => {
                            $('a[href="#tab2"]').tab('show'); // 手动切换步骤页面
                            handleTitle(tab, navigation, index);
                        });
                        if (!validResult) {
                            return false;
                        }
                        break;
                    case 2:
                        validResult = step2Valid(() => {
                            handleTitle(tab, navigation, index);
                        });
                        if (!validResult) {
                            return false;
                        }
                        break;
                    case 3:
                        validResult = step3Valid(() => {  // 回调将步骤标题设置为done
                            handleTitle(tab, navigation, index);
                        });
                        if (!validResult) {
                            return false;
                        }
                        break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();
                // 还原tab-pane的高度
                $(".tab-pane__row").css('height', '100%');

                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#dbrecovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#dbrecovercontent').find('.button-previous').css('visibility', 'hidden');
        $('#dbrecovercontent .button-submit').click(() => {
            showCoverRecoveryDialog().then(showRestoreArchivelogDialog).then(submit);
        }).css('visibility', 'hidden');
    };

    //////////////////// 开始-创建新实例 ////////////////////

    const getCreateNewInstanceHostTreeSetting = () => {
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
                    createNewInstanceHostTree.checkNode(treeNode, !treeNode.checked, true, true);
                },
                onCheck: (ev, treeId, treeNode) => {
                    let checked = treeNode.checked;
                    createNewInstanceHostTree.checkAllNodes(false);
                    if (checked) {
                        recoveryTarget.agent_uuid = treeNode.agent_uuid;
                        createNewInstanceHostTree.checkNode(treeNode, true);
                        $('.createNewInstanceConfigDiv').show();
                    } else {
                        $('.createNewInstanceConfigDiv').hide();
                    }
                },
            }
        };
    };

    /**
     * 初始化创建新实例主机树
     */
    const initCreateNewInstanceHostTree = () => {
        Metronic.blockUI({target: '.createNewInstanceHostDiv', animate: true});
        let reqData = {
            h_online_status: 1,
            offset: 0,
            limit: 100,
            offset_flag: false,
            app_type: CONF.DB_TYPE.ORACLE,
        };
        pAjaxRequest(reqData, `/api/v1/agents`, `GET`, res => {
            Metronic.unblockUI('.createNewInstanceHostDiv');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, res.message);
                return;
            }
            let nodes = {};
            for (const row of res.data.rows) {
                nodes[row.group_uuid] = {
                    id: row.group_uuid,
                    pId: '',
                    name: row.group_name,
                    title: row.group_name,
                    isParent: true,
                    open: true,
                    icon: './img/platform/flag.png',
                    nocheck: true,
                    eventtype: 'new instance group',
                    group_uuid: row.group_uuid,
                };
                let name = row.hostname + '(' + row.agent_ip + ')';
                if (row.alias && row.alias != row.agent_ip) {
                    name = row.alias + '(' + row.agent_ip + ')';
                }
                let appDetail = {
                    oracle_home_path: '',
                    oracle_base_path: '',
                };
                try {
                    appDetail = JSON.parse(row.app_list[0].app_detail);
                } catch (e) {
                    appDetail = {
                        oracle_home_path: '',
                        oracle_base_path: '',
                    };
                    // 打印错误信息
                    console.log('parse oracle bd_agent_app.app_detail failed', row.app_list[0].app_detail, row.app_list[0]);
                }
                nodes[row.agent_uuid] = {
                    id: row.agent_uuid,
                    pId: row.group_uuid,
                    name,
                    title: name,
                    isParent: false,
                    icon: './img/vm/host.png',
                    eventtype: 'new instance agent',
                    agent_uuid: row.agent_uuid,
                    agent_ip: row.agent_ip,
                    net_model: row.net_model,
                    install_app_username: row.app_list[0].install_app_username,
                    instance_name: row.app_list[0].app_name,
                    instance_name_list: row.app_list.map(row => row.app_name),
                    oracle_home: appDetail.oracle_home_path,
                    oracle_base: appDetail.oracle_base_path,
                };
            }
            createNewInstanceHostTree = $.fn.zTree.init($("#createNewInstanceHostTree"), getCreateNewInstanceHostTreeSetting(), Object.values(nodes));
        });
    };

    //////////////////// 结束-创建新实例 ////////////////////

    /**
     * 获取指定时间点数据
     */
    const buildSpecifyTimePointData = () => {
        data.pointInfo.points = [];
        for (const selectedKey in recoverySource.time_point_list) {
            let selectedNode = recoverySource.time_point_list[selectedKey];
            switch (selectedNode.eventtype) {
                case 'cluster':
                    data.pointInfo.points.push({
                        dbuuid: '',
                        dbname: selectedNode.instance_name,
                        instanceuuid: selectedNode.instance_name,
                        timepointuuid: '',
                        time_point_type: 0,
                        agentuuid: selectedNode.agent_uuid,
                        cluster_uuid: selectedNode.cluster_uuid,
                        cluster_flag: true,
                        dir_path: selectedNode.dir_path,
                        oldDbname: selectedNode.instance_name,
                        encrypt_password: '',
                        is_create_db: false,
                        new_db_name: '',
                        datafile_path: '',
                        logfile_path: '',
                        is_rollback: false,
                        rollback_time: '',
                        recovery_time: '',
                        log_time_point: '',
                        log_start_time_point: selectedNode.log_start_time_point,
                        log_end_time_point: selectedNode.log_end_time_point,
                        node_uuid: '',
                        source_agent_uuid: '',
                        source_instance_name: '',
                        source_db_name: '',
                        initialize_log_area: false,
                        latest_timepoint_uuid: '',
                        before_task_script: [],
                        after_task_script: [],
                        verification_script: [],
                    });
                    break;
                case 'instance':
                    data.pointInfo.points.push({
                        dbuuid: '',
                        dbname: selectedNode.instance_name,
                        instanceuuid: selectedNode.instance_name,
                        timepointuuid: '',
                        time_point_type: 0,
                        agentuuid: selectedNode.agent_uuid,
                        cluster_uuid: '',
                        cluster_flag: false,
                        dir_path: selectedNode.dir_path,
                        oldDbname: selectedNode.instance_name,
                        encrypt_password: '',
                        is_create_db: false,
                        new_db_name: '',
                        datafile_path: '',
                        logfile_path: '',
                        is_rollback: false,
                        rollback_time: '',
                        recovery_time: '',
                        log_time_point: '',
                        log_start_time_point: selectedNode.log_start_time_point,
                        log_end_time_point: selectedNode.log_end_time_point,
                        node_uuid: '',
                        source_agent_uuid: '',
                        source_instance_name: '',
                        source_db_name: '',
                        initialize_log_area: false,
                        latest_timepoint_uuid: '',
                        before_task_script: [],
                        after_task_script: [],
                        verification_script: [],
                    });
                    break;
                case 'db':
                    data.pointInfo.points.push({
                        dbuuid: selectedNode.db_uuid,
                        dbname: selectedNode.db_name,
                        instanceuuid: selectedNode.instance_name,
                        timepointuuid: '',
                        time_point_type: 0,
                        agentuuid: selectedNode.agent_uuid,
                        cluster_uuid: selectedNode.cluster_flag ? selectedNode.cluster_uuid : '',
                        cluster_flag: selectedNode.cluster_flag,
                        dir_path: selectedNode.dir_path,
                        oldDbname: selectedNode.db_name,
                        encrypt_password: '',
                        is_create_db: false,
                        new_db_name: '',
                        datafile_path: '',
                        logfile_path: '',
                        is_rollback: false,
                        rollback_time: '',
                        recovery_time: '',
                        log_time_point: '',
                        log_start_time_point: selectedNode.log_start_time_point,
                        log_end_time_point: selectedNode.log_end_time_point,
                        node_uuid: '',
                        source_agent_uuid: '',
                        source_instance_name: '',
                        source_db_name: '',
                        initialize_log_area: false,
                        latest_timepoint_uuid: '',
                        before_task_script: [],
                        after_task_script: [],
                        verification_script: [],
                    });
                    break;
                case 'timepoint':
                    data.node_uuid = selectedNode.storage_info.node_uuid;
                    storage_type = parseInt(selectedNode.storage_info.storage_type);
                    data.pointInfo.points.push({
                        dbuuid: selectedNode.db_uuid,
                        dbname: selectedNode.db_name,
                        instanceuuid: selectedNode.instance_name,
                        timepointuuid: selectedNode.timepoint_uuid,
                        time_point_type: selectedNode.backup_mode,
                        agentuuid: selectedNode.agent_uuid,
                        cluster_uuid: selectedNode.cluster_flag ? selectedNode.cluster_uuid : '',
                        cluster_flag: selectedNode.cluster_flag,
                        dir_path: selectedNode.dir_path,
                        oldDbname: selectedNode.db_name,
                        encrypt_password: '',
                        is_create_db: false,
                        new_db_name: '',
                        datafile_path: '',
                        logfile_path: '',
                        is_rollback: false,
                        rollback_time: '',
                        recovery_time: '',
                        log_time_point: '',
                        log_start_time_point: selectedNode.log_start_time_point,
                        log_end_time_point: selectedNode.log_end_time_point,
                        node_uuid: selectedNode.storage_info.node_uuid,
                        source_agent_uuid: '',
                        source_instance_name: '',
                        source_db_name: '',
                        initialize_log_area: false,
                        latest_timepoint_uuid: '',
                        before_task_script: [],
                        after_task_script: [],
                        verification_script: [],
                    });
                    break;
            }
        }
        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.ORACLE:  // Oracle必须选择存储
                storage_type = parseInt($('#timepointStorageSelect option:selected').data('storage-type'));
                break;
            case CONF.DB_TYPE.SAPHANA:
            case CONF.DB_TYPE.TIDB:
                // TiDB和SAP HANA需要在后面设置存储类型
                break;
        }
    };

    /**
     * 获取恢复最新时间点数据
     */
    const buildTimerNewestTimePointData = () => {
        data.pointInfo.points = [];
        drillStorageType = null;
        for (const selectedKey in recoverySource.time_point_list) {
            let selectedNode = recoverySource.time_point_list[selectedKey];
            switch (recoverySource.db_type) {
                case CONF.DB_TYPE.SQLSERVER:
                case CONF.DB_TYPE.SAPHANA:
                    if (selectedNode.eventtype === 'cluster') {
                        let treeObj = $.fn.zTree.getZTreeObj(`dbAgentTree_${selectedNode.cluster_uuid}`);
                        if (!treeObj) {
                            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_TIMER_POINT_NO_DB);
                            return false;
                        }
                        let dbCheckNodes = treeObj.getCheckedNodes(true);
                        if (!dbCheckNodes.length) {
                            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_TIMER_POINT_NO_DB);
                            return false;
                        }
                    } else if (selectedNode.eventtype === 'instance') {
                        let treeObj = $.fn.zTree.getZTreeObj(`dbAgentTree_${selectedNode.app_uuid}`);
                        if (!treeObj) {
                            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_TIMER_POINT_NO_DB);
                            return false;
                        }
                        let dbCheckNodes = treeObj.getCheckedNodes(true);
                        if (!dbCheckNodes.length) {
                            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_TIMER_POINT_NO_DB);
                            return false;
                        }
                    }
                    if (selectedNode.eventtype !== 'db') {
                        continue;
                    }
                    break;
                default:
                    break;
            }
            if (selectedNode.cluster_flag) {
                for (const instanceInfo of selectedNode.instance_list) {
                    let key = instanceInfo.agent_info.agent_uuid + '_' + instanceInfo.instance_name;
                    if (recoverySource.db_type === CONF.DB_TYPE.SQLSERVER || recoverySource.db_type === CONF.DB_TYPE.SAPHANA) {
                        key += '_' + instanceInfo.db_name;
                    }
                    if (typeof recoverySource.agent_newest_timepoint_map[key] !== 'undefined') {
                        if (drillStorageType === CONF.BD_STORAGE_TYPE.TAPE) {
                            break;
                        }
                        drillStorageType = parseInt(recoverySource.agent_newest_timepoint_map[key].storage_info.storage_type);
                    }
                }
            } else {
                let key = selectedNode.agent_uuid + '_' + selectedNode.instance_name;
                if (recoverySource.db_type === CONF.DB_TYPE.SQLSERVER || recoverySource.db_type === CONF.DB_TYPE.SAPHANA) {
                    key += '_' + selectedNode.db_name;
                }
                if (typeof recoverySource.agent_newest_timepoint_map[key] !== 'undefined') {
                    if (drillStorageType === CONF.BD_STORAGE_TYPE.TAPE) {
                        break;
                    }
                    drillStorageType = parseInt(recoverySource.agent_newest_timepoint_map[key].storage_info.storage_type);
                }
            }
            data.pointInfo.points.push({
                dbuuid: '',
                dbname: selectedNode.db_name,
                instanceuuid: selectedNode.instance_name,
                timepointuuid: '',
                time_point_type: 0,
                agentuuid: selectedNode.agent_uuid,
                cluster_uuid: selectedNode.cluster_uuid,
                cluster_flag: selectedNode.cluster_flag,
                dir_path: selectedNode.dir_path,
                oldDbname: selectedNode.db_name,
                encrypt_password: '',
                is_create_db: false,
                new_db_name: '',
                datafile_path: '',
                logfile_path: '',
                is_rollback: false,
                rollback_time: '',
                recovery_time: '',
                log_time_point: '',
                log_start_time_point: '',
                log_end_time_point: '',
                node_uuid: '',
                source_agent_uuid: selectedNode.agent_uuid,
                source_instance_name: selectedNode.instance_name,
                source_db_name: selectedNode.db_name,
                initialize_log_area: false,
                latest_timepoint_uuid: '',
                before_task_script: [],
                after_task_script: [],
                verification_script: [],
            });
        }
        return true;
    };

    var step1Valid = function (showTitleCallback) {
        if (JSON.stringify(recoverySource.time_point_list) === '{}') {
            if (parseInt($('#recoverySourceTypeSelect').val()) === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                $(".selecttimepointtip").html(LANG.UI_RECOVERY_SELECT_DATA_SOURCE).show();
            } else {
                $(".selecttimepointtip").html(LANG.UI_DB_RECOVERY_SELECT_AGENT).show();
            }
            // 动态设置tab-pane的高度
            $(".tab-pane__row").css('height', 'calc(100% - 80px)');
            return false;
        }

        data.pointInfo.points = [];
        data.pointInfo.type = recoverySource.db_type;  // 数据库类别
        data.recovery_type = recoverySource.recovery_source_type;  // 恢复方式[1指定时间点恢复 2恢复最新时间点]
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            buildSpecifyTimePointData();
        } else {
            if (!buildTimerNewestTimePointData()) {
                return false;
            }
        }

        // 步骤2的提示信息
        $('#sqlservertips').hide();
        $('#oracletips').hide();
        $('#mysqltips').hide();
        $('#mariatips').hide();
        $('#dmtips').hide();
        $('#recoveryNewestTips').hide();
        $('.host_tree_div').show();
        $('.createNewInstanceDiv').hide();  // 隐藏新建实例
        $('.createNewInstanceHostDiv').hide();  // 隐藏新建实例主机
        $('.createNewInstanceConfigDiv').hide();  // 隐藏新建实例恢复配置

        $('#advancedDiv').hide();  // 隐藏高级配置
        $('#parallelNumDiv').hide();  // 隐藏客户端并行数量
        $('.pgNewInstanceDiv').hide();  // 隐藏新建实例恢复
        $('.pgSpecifyFolderDiv').hide();  // 隐藏指定文件夹
        $('.dmfileDiv').hide();  // 隐藏指定文件夹路径
        $('#pathType').val(PATH_TYPE_ENUM.COVER).attr('disabled', false);
        $('#pathType option').hide();  // 隐藏所有选项
        // 隐藏oracle恢复方式提示信息
        $('.recovery-type_full_in_exp').hide();
        $('.recovery-type_incomplete').hide();
        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.SQLSERVER: //sqlserver
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_RECOVERY).show();  // 原数据库覆盖
                $('#createRecovery').html(LANG.UI_DB_NEW_RECOVERY).show();  // 新建数据库
                $('#sqlservertips').show();  // 第二步的提示语
                break;
            case CONF.DB_TYPE.ORACLE: //oracle
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    // $('#restoreArchivelogRecovery').show(); // 还原归档日志
                    $('#openDbCheck').bootstrapSwitch('disabled', false).bootstrapSwitch('state', false);
                    $('#fullRecovery').show();  // 完全恢复
                    $('#incompleteRecovery').show();  // 不完全恢复
                    $('#exportRecovery').show();  // 导出恢复
                    $('.createNewInstanceDiv').show();  // 显示新建实例
                    $('#createNewInstance').bootstrapSwitch('state', false);  // 默认关闭新建实例
                    $('.recovery-type_full_in_exp').show();
                    $('.recovery-type_incomplete').hide();
                    initCreateNewInstanceHostTree();
                } else {
                    $('#openDbCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
                    $('#incompleteRecovery').show();  // 不完全恢复
                    $('.recovery-type_full_in_exp').hide();
                    $('.recovery-type_incomplete').show();
                }
                $('#oracletips').show();
                break;
            case CONF.DB_TYPE.DM: //达梦
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_INSTANCE_RECOVERY).show();  // 原数据库覆盖
                $('#specifyRecovery').show();  //指定文件夹
                $('#dmtips').show();
                break;
            case CONF.DB_TYPE.MYSQL: //mysql
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_INSTANCE_RECOVERY).show();  // 原数据库覆盖
                $('#redirectRecovery').show();  // 重定向
                $('#mysqltips').show();
                break;
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.UXDB:
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_INSTANCE_RECOVERY).show();  // 原数据库覆盖
                $('#specifyRecovery').show();  //指定文件夹
                $('#createRecovery').html(LANG.UI_DB_NEW_INSTANCE_RECOVERY).show();  // 新建实例恢复
                break;
            case CONF.DB_TYPE.OPENGAUSS:
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_INSTANCE_RECOVERY).show();  // 原数据库覆盖
                break;
            case CONF.DB_TYPE.VASTBASE:
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_INSTANCE_RECOVERY).show();  // 原数据库覆盖
                break;
            case CONF.DB_TYPE.MARIA: //maria
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_INSTANCE_RECOVERY).show();  // 原数据库覆盖
                $('#redirectRecovery').show();  // 重定向
                $('#mariatips').show();
                break;
            case CONF.DB_TYPE.MONGODB:
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_INSTANCE_RECOVERY).show();  // 原数据库覆盖
                $('#specifyRecovery').show();  //指定文件夹
                break;
            case CONF.DB_TYPE.TIDB:
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_INSTANCE_RECOVERY).show();  // 原数据库覆盖
                break;
            case CONF.DB_TYPE.SAPHANA:
                $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_RECOVERY).show();     // 原数据库覆盖
                $('#createRecovery').html(LANG.UI_DB_NEW_RECOVERY).show();  // 新建数据库
                break;
        }

        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            $('#sqlservertips').hide();
            $('#oracletips').hide();
            $('#mysqltips').hide();
            $('#mariatips').hide();
            $('#dmtips').hide();
            $('#recoveryNewestTips').show();
            $('#coverRecovery').html(LANG.UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY);
        }

        // 显示并初始化宿主机树
        initRecoveryTargetTree();
        if (CONF.DB_TYPE.SAPHANA === recoverySource.db_type) {
            // SAP HANA回滚时间常显示
        } else if (CONF.DB_TYPE.ORACLE === recoverySource.db_type) {
            // Oracle不显示回滚时间
        } else if (CONF.DB_TYPE.TIDB === recoverySource.db_type) {
            // TiDB不显示回滚时间
        } else if (CONF.DB_TYPE.SQLSERVER !== recoverySource.db_type && CONF.DB_TYPE.MONGODB !== recoverySource.db_type) {
            // sql server和mongodb不在这里设置回滚时间范围；SQL server是多数据库恢复，这里设置不了；mongodb根据start_time和end_time确认是否有，这里也设置不了
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {  // 按时间点恢复才需要设置回滚时间
                initDBConfig();
            }
        }

        // 恢复默认值
        recoverySource.table_space = [];

        getDbCurrentUseLicense().then(() => {
            showStep1();
            showTitleCallback();
        });
        return false;
    }

    /**
     * 获取数据库当前使用的授权信息
     * @returns {Promise<unknown>}
     */
    const getDbCurrentUseLicense = () => {
        return new Promise((resolve) => {
            if (recoverySource.recovery_source_type !== RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                resolve();
                return;
            }
            let params = {
                module: 'oracle',
                showMetronic: true,
                judge: false,
            }
            getModuleAuthInfo(params).then(res => {
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

    /**
     * 初始化任务名称
     * 这里是需要在选择目的地节点后再初始化.
     */
    var initTaskName = function () {
        if (editTaskFlag) {
            $('#jobname').val(oldRecoveryTaskData.job_name.replace('&lt;', '<').replace('&gt;', '>'));
            return;
        }
        let reqData = {
            job_name: 'WEB_DB_RECOVERY_TASKNAME',
        };
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            reqData.job_name = 'WEB_DB_DRILL_TASKNAME';
        }
        pAjaxRequest(reqData, `/api/v1/jobs/name`, `GET`, res => {
            if (!res.success) {
                $('#jobname').val('');
            } else {
                $('#jobname').val(res.data.value);
            }
        });
    }

    /**
     * 初始化步骤4的恢复源树
     */
    const initStep4RecoverySourceTree = () => {
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

        let clusterTopFlag = false;
        let standaloneTopFlag = false;

        let nodes = [];
        $.each(recoverySource.time_point_list, function (key, checkNode) {
            if (checkNode.eventtype === 'cluster') {
                if (!clusterTopFlag) {
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
                    clusterTopFlag = true;
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
                if (recoverySource.db_type == CONF.DB_TYPE.MONGODB) {
                    // MongoDB有多级
                    let recoverySourceCheckNodes = pointtypetree.getCheckedNodes(true);
                    let recoverySourceCheckNode = null;
                    for (const _recoverySourceCheckNode of recoverySourceCheckNodes) {
                        if (_recoverySourceCheckNode.eventtype === 'cluster' && _recoverySourceCheckNode.id === checkNode.id) {
                            recoverySourceCheckNode = _recoverySourceCheckNode;
                            break;
                        }
                    }
                    if (!recoverySourceCheckNode) {
                        return;
                    }
                    for (const child of recoverySourceCheckNode.children) {
                        nodes.push(child);
                    }
                } else {
                    for (const childNode of checkNode.children) {
                        nodes.push({
                            id: childNode.agent_ip + '_' + childNode.instance_name,
                            pId: checkNode.cluster_uuid,
                            name: childNode.name,
                            title: childNode.name,
                            isParent: false,
                            nocheck: true,
                            eventtype: 'cluster_instance',
                            icon: './img/platform/storage.png',
                        });
                    }
                }
                if (recoverySource.db_type == CONF.DB_TYPE.SQLSERVER) {  // SQL Server选择的是数据库
                    nodes[nodes.length - 1].isParent = true;
                    nodes[nodes.length - 1].open = true;
                    let lastInstanceId = nodes[nodes.length - 1].id;
                    for (const _selectKey in recoverySource.time_point_list) {
                        let dbNode = recoverySource.time_point_list[_selectKey];
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
                    isParent: false,
                    nocheck: true,
                    eventtype: 'instance',
                    icon: './img/platform/storage.png',
                });
                if (recoverySource.db_type == CONF.DB_TYPE.SQLSERVER) {  // SQL Server选择的是数据库
                    nodes[nodes.length - 1].isParent = true;
                    nodes[nodes.length - 1].open = true;
                    let lastInstanceId = nodes[nodes.length - 1].id;
                    for (const _selectKey in recoverySource.time_point_list) {
                        let dbNode = recoverySource.time_point_list[_selectKey];
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
        $.fn.zTree.init($("#recoverySourceTree").show(), setting, nodes);
    };

    var showStep1 = function () {
        /**
         * 显示第四步【确认配置】备份点信息
         * @type {string}
         */
        $("#recoverySourceTree").hide();
        let backupTimePointInfo = CONF.DB_DES[recoverySource.db_type] + LANG.UI_DB_RECOVERY + '<br>';
        $.each(recoverySource.time_point_list, function (key, selectedNode) {
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                let timePointType = parseInt(selectedNode.backup_mode);
                if (backupTimePointType.includes(timePointType)) {
                    backupTimePointInfo += selectedNode.dir_path + " (" + selectedNode.time_point + ")<br>";
                } else {
                    if (recoverySource.db_type === CONF.DB_TYPE.SAPHANA) {
                        backupTimePointInfo += selectedNode.dir_path + '<br>';
                    } else if (
                        recoverySource.db_type === CONF.DB_TYPE.ORACLE ||
                        recoverySource.db_type === CONF.DB_TYPE.TIDB
                    ) {
                        // backupTimePointInfo += selectedNode.dir_path + '<br>';
                        initStep4RecoverySourceTree();
                    }
                }
            } else {
                initStep4RecoverySourceTree();
                // if (recoverySource.db_type === CONF.DB_TYPE.TIDB) {
                //     backupTimePointInfo += selectedNode.name + '<br>';
                // } else if (recoverySource.db_type === CONF.DB_TYPE.ORACLE) {
                //     initStep4RecoverySourceTree();
                // } else {
                //     backupTimePointInfo += selectedNode.dir_path + '<br>';
                // }
            }
        });
        $('.vmtypeshow').html(backupTimePointInfo);

        // NOTE: mariadb的服务名不能是mysql，要为mariadb，因此要在第一步选择了数据库备份集后设置值
        switch (recoverySource.db_type) {  // 设置第三步的服务名
            case CONF.DB_TYPE.MYSQL:
                $('#startcommand').val('mysql');
                break;
            case CONF.DB_TYPE.MARIA:
                $('#startcommand').val('mariadb');
                break;
        }
    }

    //检查路径格式
    var checkPath = function (value, flag) {
        value = value.replace(/\//gi, "/"); //正则替换  把输入的\替换成/
        var re1 = '((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])'; // IPv4 IP Address 1
        var re2 = '(:)'; // Any Single Character 1
        var re3 = '((?:(\\/|\\\\|\\\\\\\\)[\\w]+)+)'; // Unix Path 1，追加匹配\和\\
        var re4 = '(.*)';
        //		var path = /^([a-zA-Z]:|\\\\[^\\\\/:*?"<>|]+)(\\[^\\\\/:*?"<>|]+)*\\?$/;
        // 		var linux_path =  '^\\/(\\w+\\/?)+$';//linux路径检测
        var linux_path = '^/[^%&\',;=?$\x22]+'; //linux路径检测，linux路径以/开头
        var cn_word = '[\u4e00-\u9fa5]'; //中文检测
        var specialChar = '[*?"|<>]';
        var p1 = new RegExp(re1 + re2 + re3, ["i"]);
        var p2 = new RegExp(re4 + re2 + re3, ["i"]);
        var p3 = new RegExp(linux_path);
        var p4 = new RegExp(cn_word, ["g"]);
        var p5 = new RegExp(specialChar, ['g'])
        if (flag === 'Linux') { //如果为linux系统则只能输入linux系统目录
            return !!(p3.exec(value) && !p4.exec(value) && !p5.exec(value))
        } else if (flag === 'Windows') {
            return !!(p2.exec(value) && !p4.exec(value) && !p5.exec(value))
        }
        return !!((p2.exec(value) || p3.exec(value)) && !p4.exec(value) && !p5.exec(value));
    }

    /**
     * 注册还原归档日志事件
     */
    const registerOracleRestoreRangeSliderEvent = () => {
        oracleRestoreTimeSlider.off();
        oracleRestoreTimeSlider.on('start', oracleRestoreRangeStart);
        oracleRestoreTimeSlider.on('slide', oracleRestoreRangeSlide);
        oracleRestoreTimeSlider.on('end', oracleRestoreRangeEnd);
    };

    /**
     * oracle还原归档日志时间轴结束
     */
    const oracleRestoreRangeEnd = () => {
        oracleRestoreTimeSlider.updateOptions({
            tooltips: [false, false],
        });
    };

    /**
     * oracle还原归档日志时间轴更新
     */
    const oracleRestoreRangeSlide = () => {
        let rangeData = oracleRestoreTimeSlider.get(true);
        let startTime = getCurrentDatetimeStr(new Date(rangeData[0] * 1000));
        let endTime = getCurrentDatetimeStr(new Date(rangeData[1] * 1000));

        $('#oracleRestoreTimeSelectStartTime').val(startTime);
        $('#oracleRestoreTimeSelectEndTime').val(endTime);
    };

    /**
     * oracle还原归档日志时间轴开始
     */
    const oracleRestoreRangeStart = (values, handle) => {
        if (handle === 0) {
            oracleRestoreTimeSlider.updateOptions({
                tooltips: [
                    {
                        to: function (value) {
                            return getCurrentDatetimeStr(new Date(value * 1000));
                        }
                    },
                    false
                ],
            });
        } else {
            oracleRestoreTimeSlider.updateOptions({
                tooltips: [
                    false,
                    {
                        to: function (value) {
                            return getCurrentDatetimeStr(new Date(value * 1000));
                        }
                    }
                ],
            });
        }
    };

    /**
     * 初始化Oracle还原归档日志
     */
    const initRestoreArchivelog = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            return;
        }

        let minTime = getCurrentDatetimeStr(new Date(oracleTimepointList.start_time * 1000));
        let maxTime = getCurrentDatetimeStr(new Date(oracleTimepointList.end_time * 1000))

        if (oracleRestoreTimeSlider !== null) {
            oracleRestoreTimeSlider.destroy();
            oracleRestoreTimeSlider = null;
        }
        /**
         * 这里noUiSlider接收int，而页面显示为datetime
         */
        oracleRestoreTimeSlider = noUiSlider.create($('#oracleRestoreTimeSelectRange').get(0), {
            start: [oracleTimepointList.start_time, oracleTimepointList.end_time],
            range: {
                min: oracleTimepointList.start_time,
                max: oracleTimepointList.end_time,
            },
            connect: true,
            step: 1,
            behaviour: 'smooth-tap-steps',
            tooltips: [false, false],
        });
        $('.oracleRestoreTimeSelectDiv .minTime').html(minTime);
        $('.oracleRestoreTimeSelectDiv .maxTime').html(maxTime);
        $('#oracleRestoreTimeSelectStartTime').val(minTime);
        $('#oracleRestoreTimeSelectEndTime').val(maxTime);
        registerOracleRestoreRangeSliderEvent();
    };

    //////////////////// 开始-Oracle恢复内容 ////////////////////

    /**
     * 重置oracleRecoveryContent配置
     */
    const resetOracleRecoveryContent = () => {
        oracleRecoveryContent = {
            pfile: '',
            pfile_flag: false,
            listener: '',
            listener_path: '',
            listener_flag: '',
            tnsnames: '',
            tnsnames_path: '',
            tnsnames_flag: '',
            sqlnet: '',
            sqlnet_path: '',
            sqlnet_flag: '',
            password_flag: false,
            password_path: '',
            password: '',
            tree: null,
        };
    };

    /**
     * 初始化Oracle恢复内容
     */
    const initOracleRecoveryContent = () => {
        return new Promise((resolve, reject) => {
            // 初始化配置文件
            let oracleTimepoint = oracleTimepointList.select_timepoint;
            Metronic.blockUI({target: '.oracleRecoveryContentDiv', animate: true});
            $.ajax({
                type: CONF.AJAXMETHOD,
                url: CONF.AJAXPATH,
                data: {
                    m: CONF.M.DBPROTECT,
                    f: 'getCustomConfigContent',
                    p: JSON.stringify({timepoint_uuid: oracleTimepoint.time_point_uuid})
                },
                success: result => {
                    Metronic.unblockUI('.oracleRecoveryContentDiv');
                    let data = JSON.parse(result);
                    if (data.re) {
                        oracleRecoveryContent.pfile = data.ext.pfile;
                        oracleRecoveryContent.listener = data.ext.listener
                        oracleRecoveryContent.listener_path = data.ext.listener_path;
                        oracleRecoveryContent.tnsnames = data.ext.tnsnames;
                        oracleRecoveryContent.tnsnames_path = data.ext.tnsnames_path;
                        oracleRecoveryContent.sqlnet = data.ext.sqlnet;
                        oracleRecoveryContent.sqlnet_path = data.ext.sqlnet_path;
                        oracleRecoveryContent.password_path = data.ext.password_path;
                        resolve();
                    } else {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, data.msg);
                        reject();
                    }
                }
            });
        });
    };

    /**
     * 显示Oracle自动义配置文件
     * @param eventtype
     */
    const showOracleCustomConfigFileModal = eventtype => {
        let customConfigPathSelector = new $.fn.PathTreeSelector.cls(recoveryTarget.agent_uuid);
        let option = {
            target_id: 'custom-config-modal__path',
            select_mode: 3,
            select_only: false,
            selected_path: '',
        };

        let treeNode = oracleRecoveryContent.tree.getNodeByParam('eventtype', eventtype);
        let title = `<span>
                        <i class="fa fa-pencil-square-o"></i>
                        <span class="text">${treeNode.config_name}</span>
                    </span>`;
        $('#customConfigFileModal .modal-header .modal-title').html(title);
        if ('pfile' === eventtype) {
            $('label[for="custom-config-modal__content"]').html(LANG.UI_DB_RECOVERY_PFILE);
            $('#customConfigFileModal .custom-config-modal__folder').hide();
            $('#customConfigFileModal .custom-config-modal__password').hide();
            $('#custom-config-modal__content').val(oracleRecoveryContent.pfile);
            $('#customConfigFileModal .custom-config-modal__content').show();
        } else if ('password' === eventtype) {
            let oracleTimepoint = oracleTimepointList.select_timepoint;
            if (oracleRecoveryType === 2 && parseInt(oracleTimepoint.db_config.rac_flag) !== 0) {  // RAC到空实例才显示密码
                $('#customConfigFileModal .custom-config-modal__password').show();
                $('#customConfigFileModal .custom-config-modal__folder').hide();
            } else {
                $('#customConfigFileModal .custom-config-modal__password').hide();
                $('#customConfigFileModal .custom-config-modal__folder').show();
            }
            $('#customConfigFileModal .custom-config-modal__content').hide();
            $('label[for="custom-config-modal__path"]').html(LANG.UI_DB_RECOVERY_PASSWORD_PATH);
            option.selected_path = oracleRecoveryContent.password_path;
            customConfigPathSelector.init(option);
        } else {
            $('#customConfigFileModal .custom-config-modal__folder').show();
            $('#customConfigFileModal .custom-config-modal__content').show();
            $('#customConfigFileModal .custom-config-modal__password').hide();
            if ('listener' === eventtype) {
                $('label[for="custom-config-modal__path"]').html(LANG.UI_DB_RECOVERY_LISTENER_PATH);
                $('label[for="custom-config-modal__content"]').html(LANG.UI_DB_RECOVERY_LISTENER);
                option.selected_path = oracleRecoveryContent.listener_path;
                $('#custom-config-modal__content').val(oracleRecoveryContent.listener);
            } else if ('tnsnames' === eventtype) {
                $('label[for="custom-config-modal__path"]').html(LANG.UI_DB_RECOVERY_TNSNAMES_PATH);
                $('label[for="custom-config-modal__content"]').html(LANG.UI_DB_RECOVERY_TNSNAMES);
                option.selected_path = oracleRecoveryContent.tnsnames_path;
                $('#custom-config-modal__content').val(oracleRecoveryContent.tnsnames);
            } else if ('sqlnet' === eventtype) {
                $('label[for="custom-config-modal__path"]').html(LANG.UI_DB_RECOVERY_SQLNET_PATH);
                $('label[for="custom-config-modal__content"]').html(LANG.UI_DB_RECOVERY_SQLNET);
                option.selected_path = oracleRecoveryContent.sqlnet_path;
                $('#custom-config-modal__content').val(oracleRecoveryContent.sqlnet);
            }
            customConfigPathSelector.init(option);
        }
        $('#customConfigFileModal').modal({width: '80%', height: '600px'}).data('eventtype', treeNode.eventtype);
    }

    /**
     * Oracle恢复内容节点点击事件
     * @param treeId
     * @param treeNode
     */
    const oracleRecoveryContentNodeClick = (treeId, treeNode) => {
        switch (treeNode.eventtype) {
            case 'pfile':
            case 'listener':
            case 'tnsnames':
            case 'sqlnet':
                if (treeNode.checked) {
                    // showOracleCustomConfigFileModal(treeNode);
                }
                break;
        }
    };

    /**
     * Oracle恢复内容节点点击事件
     * @param ev
     * @param treeId
     * @param treeNode
     */
    const oracleRecoveryContentNodeCheck = (ev, treeId, treeNode) => {
        let name = '';
        let oracleTimepoint = oracleTimepointList.select_timepoint;
        switch (treeNode.eventtype) {
            case 'pfile':
            case 'listener':
            case 'tnsnames':
            case 'sqlnet':
            case 'password':
                name = `${treeNode.p_name}(${treeNode.dir_path})`;
                if (treeNode.eventtype === 'pfile') {
                    name = `${treeNode.p_name}`;
                }
                oracleRecoveryContent[treeNode.eventtype + '_flag'] = false;
                if (treeNode.checked) {
                    name = `${treeNode.config_name}(${treeNode.dir_path})`;
                    if (treeNode.eventtype === 'pfile') {
                        name = `${treeNode.config_name}`;
                    } else if (treeNode.eventtype === 'password' && oracleRecoveryType === 2 && parseInt(oracleTimepoint.db_config.rac_flag) !== 0) {
                        name = `${treeNode.config_name}`;
                    }
                    name = `<span class="edit-config-file" id="edit${treeNode.eventtype}">
                                <i class="fa fa-pencil-square-o"></i>
                                <span class="text">${name}</span>
                            </span>`;
                    oracleRecoveryContent[treeNode.eventtype + '_flag'] = true;
                }
                treeNode.name = name;
                oracleRecoveryContent.tree.updateNode(treeNode);
                break;
            case 'config_file':
                let children = treeNode.children;
                for (const child of children) {
                    oracleRecoveryContentNodeCheck(ev, treeId, child);
                }
        }
    };

    /**
     * 获取Oracle恢复内容树的配置
     */
    const getOracleRecoveryContentTreeSetting = () => {
        return {
            check: {
                enable: true,
                nocheckInherit: false,
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
                },
            },
            view: {
                nameIsHTML: true,
                addDiyDom: (treeId, treeNode) => {
                    if (treeNode.eventtype === 'oracle') {
                        let aNodeObj = $(`#${treeNode.tId}_ico`);
                        aNodeObj.css({
                            background: `url(${treeNode.icon}) 0 no-repeat`,
                        });
                    }
                },
            },
            callback: {
                beforeClick: oracleRecoveryContentNodeClick,
                onCheck: oracleRecoveryContentNodeCheck,
            },
        };
    };

    /**
     * 获取Oracle恢复内容基本节点
     * @param oracleTimepoint
     * @returns {Object}
     */
    const getOracleBaseNodes = (oracleTimepoint) => {
        /**
         * 树节点列表
         * @type {Object}
         */
        let name = oracleTimepoint.instance_name;
        if (oracleTimepoint.is_cluster) {
            name = oracleTimepoint.db_name;  // Oracle的db_name为集群服务名
        }
        let nodes = [{
            id: oracleTimepoint.time_point_uuid + '_oracle',
            pId: 0,
            name: name,
            title: name,
            isParent: true,
            open: true,
            nocheck: true,
            eventtype: 'oracle',
            icon: './img/db/oracle.png',
        }];


        let databaseChkDisabled = true;
        let instanceChkDisabled = true;
        let pathType = parseInt($('#pathType').val());
        if (pathType === PATH_TYPE_ENUM.FULL) {  // 完全恢复允许勾选表空间
            instanceChkDisabled = false;
            databaseChkDisabled = false;
        }
        // 数据库
        nodes.push({
            id: oracleTimepoint.time_point_uuid + '_oracle_database',
            pId: oracleTimepoint.time_point_uuid + '_oracle',
            name: LANG.UI_DB_RECOVERY_CONTENT_DB,
            title: LANG.UI_DB_RECOVERY_CONTENT_DB,
            isParent: true,
            open: false,
            eventtype: 'database',
            icon: './img/vm/host.png',
            checked: true,
            chkDisabled: databaseChkDisabled,
        });

        // 实例
        nodes.push({
            id: oracleTimepoint.time_point_uuid + '_oracle_database_instance',
            pId: oracleTimepoint.time_point_uuid + '_oracle_database',
            name: oracleTimepoint.instance_name,
            title: oracleTimepoint.instance_name,
            isParent: true,
            open: false,
            eventtype: 'instance',
            icon: './img/vm/host.png',
            checked: true,
            chkDisabled: instanceChkDisabled,
        });

        // 控制文件
        nodes.push({
            id: oracleTimepoint.time_point_uuid + '_oracle_control',
            pId: oracleTimepoint.time_point_uuid + '_oracle',
            name: LANG.UI_DB_RECOVERY_CONTENT_CONTROL_FILE,
            title: LANG.UI_DB_RECOVERY_CONTENT_CONTROL_FILE,
            isParent: false,
            eventtype: 'control_file',
            icon: './img/fs/wenjianjiaopen.png',
            checked: true,
            chkDisabled: true,
        });

        // 配置文件
        nodes.push({
            id: oracleTimepoint.time_point_uuid + '_oracle_config',
            pId: oracleTimepoint.time_point_uuid + '_oracle',
            name: LANG.UI_DB_RECOVERY_CONTENT_CONFIG_FILE,
            title: LANG.UI_DB_RECOVERY_CONTENT_CONFIG_FILE,
            isParent: true,
            open: true,
            eventtype: 'config_file',
            icon: './img/fs/wenjianjiaopen.png',
        });
        return nodes;
    };

    /**
     * 获取Oracle配置文件节点
     * @param oracleTimepoint
     * @returns {Object}
     */
    const getOracleConfigFileNode = (oracleTimepoint) => {
        let nodes = [];
        // pfile
        nodes.push({
            id: oracleTimepoint.time_point_uuid + '_oracle_config_pfile',
            pId: oracleTimepoint.time_point_uuid + '_oracle_config',
            name: LANG.UI_DB_RECOVERY_PFILE,
            title: LANG.UI_DB_RECOVERY_PFILE,
            p_name: LANG.UI_DB_RECOVERY_PFILE,
            config_name: LANG.UI_DB_RECOVERY_PFILE_CONFIG,
            isParent: false,
            eventtype: 'pfile',
            icon: './img/fs/wenjian.png',
        });

        /**
         * listener、tnsnames、sqlnet空实例恢复勾选且不可取消勾选
         */
        let listenerName = LANG.UI_DB_RECOVERY_LISTENER + '(' + oracleRecoveryContent.listener_path + ')';
        let tnsnamesName = LANG.UI_DB_RECOVERY_TNSNAMES + '(' + oracleRecoveryContent.tnsnames_path + ')';
        let sqlnetName = LANG.UI_DB_RECOVERY_SQLNET + '(' + oracleRecoveryContent.sqlnet_path + ')';
        if (oracleRecoveryType === 2 && !recoveryTarget.create_new_instance_flag) {  // 空实例默认选择中listener、tnsnames、sqlnet，新建实例不勾选
            oracleRecoveryContent.listener_flag = true;
            oracleRecoveryContent.tnsnames_flag = true;
            oracleRecoveryContent.sqlnet_flag = true;
            listenerName = `<span class="edit-config-file" id="editlistener">
                                <i class="fa fa-pencil-square-o"></i>
                                <span class="text">${LANG.UI_DB_RECOVERY_LISTENER_CONFIG}(${oracleRecoveryContent.listener_path})</span>
                            </span>`;
            tnsnamesName = `<span class="edit-config-file" id="edittnsnames">
                                <i class="fa fa-pencil-square-o"></i>
                                <span class="text">${LANG.UI_DB_RECOVERY_TNSNAMES_CONFIG}(${oracleRecoveryContent.tnsnames_path})</span>
                            </span>`;
            sqlnetName = `<span class="edit-config-file" id="editsqlnet">
                                <i class="fa fa-pencil-square-o"></i>
                                <span class="text">${LANG.UI_DB_RECOVERY_SQLNET_CONFIG}(${oracleRecoveryContent.sqlnet_path})</span>
                            </span>`;
        }

        // listener
        nodes.push({
            id: oracleTimepoint.time_point_uuid + '_oracle_config_listener',
            pId: oracleTimepoint.time_point_uuid + '_oracle_config',
            name: listenerName,
            title: LANG.UI_DB_RECOVERY_LISTENER,
            p_name: LANG.UI_DB_RECOVERY_LISTENER,
            config_name: LANG.UI_DB_RECOVERY_LISTENER_CONFIG,
            dir_path: oracleRecoveryContent.listener_path,
            isParent: false,
            eventtype: 'listener',
            icon: './img/fs/wenjian.png',
            checked: oracleRecoveryType === 2,
            chkDisabled: oracleRecoveryType === 2,
        });

        // tnsnames
        nodes.push({
            id: oracleTimepoint.time_point_uuid + '_oracle_config_tnsnames',
            pId: oracleTimepoint.time_point_uuid + '_oracle_config',
            name: tnsnamesName,
            title: LANG.UI_DB_RECOVERY_TNSNAMES,
            p_name: LANG.UI_DB_RECOVERY_TNSNAMES,
            config_name: LANG.UI_DB_RECOVERY_TNSNAMES_CONFIG,
            dir_path: oracleRecoveryContent.tnsnames_path,
            isParent: false,
            eventtype: 'tnsnames',
            icon: './img/fs/wenjian.png',
            checked: oracleRecoveryType === 2,
            chkDisabled: oracleRecoveryType === 2,
        });

        // sqlnet
        nodes.push({
            id: oracleTimepoint.time_point_uuid + '_oracle_config_sqlnet',
            pId: oracleTimepoint.time_point_uuid + '_oracle_config',
            name: sqlnetName,
            title: LANG.UI_DB_RECOVERY_SQLNET,
            p_name: LANG.UI_DB_RECOVERY_SQLNET,
            config_name: LANG.UI_DB_RECOVERY_SQLNET_CONFIG,
            dir_path: oracleRecoveryContent.sqlnet_path,
            isParent: false,
            eventtype: 'sqlnet',
            icon: './img/fs/wenjian.png',
            checked: oracleRecoveryType === 2,
            chkDisabled: oracleRecoveryType === 2,
        });

        let passwordName = LANG.UI_DB_RECOVERY_PASSWORD + '(' + oracleRecoveryContent.password_path + ')';
        if (oracleRecoveryType === 2 && parseInt(oracleTimepoint.db_config.rac_flag) !== 0) {
            passwordName = LANG.UI_DB_RECOVERY_PASSWORD;
        }

        if (!recoverySource.cluster_flag && recoveryTarget.cluster_flag) {  // 单机恢复到RAC屏蔽恢复密码文件
            // pass
        } else {
            // 密码文件
            nodes.push({
                id: oracleTimepoint.time_point_uuid + '_oracle_config_password',
                pId: oracleTimepoint.time_point_uuid + '_oracle_config',
                name: passwordName,
                title: LANG.UI_DB_RECOVERY_PASSWORD,
                p_name: LANG.UI_DB_RECOVERY_PASSWORD,
                config_name: LANG.UI_DB_RECOVERY_PASSWORD_CONFIG,
                dir_path: oracleRecoveryContent.password_path,
                isParent: false,
                eventtype: 'password',
                icon: './img/fs/wenjian.png',
            });
        }

        return nodes;
    };

    /**
     * 获取Oracle表空间节点
     * @param oracleTimepoint
     * @returns {Object}
     */
    const getOracleTablespaceNodes = (oracleTimepoint) => {
        let tablespaceChkDisabled = true;
        let pathType = parseInt($('#pathType').val());
        if (pathType === PATH_TYPE_ENUM.FULL) {  // 完全恢复允许勾选表空间
            tablespaceChkDisabled = false;
        }
        let nodes = []
        // 表空间
        if (Array.isArray(oracleTimepoint.db_config['backuped_tablespace_list'])) {
            for (const tablespaceInfo of oracleTimepoint.db_config['backuped_tablespace_list']) {
                nodes.push({
                    id: oracleTimepoint.time_point_uuid + '_oracle_database_instance_' + tablespaceInfo['table_space_name'],
                    pId: oracleTimepoint.time_point_uuid + '_oracle_database_instance',
                    name: tablespaceInfo['table_space_name'],
                    title: tablespaceInfo['table_space_name'],
                    tablespace: tablespaceInfo['table_space_name'],
                    isParent: true,
                    open: false,
                    eventtype: 'table_space',
                    icon: './img/platform/storage.png',
                    checked: true,
                    chkDisabled: tablespaceChkDisabled,
                });
                if (Array.isArray(tablespaceInfo['data_file_list'])) {
                    for (const datafileInfo of tablespaceInfo['data_file_list']) {
                        nodes.push({
                            id: oracleTimepoint.time_point_uuid + '_oracle_database_instance_' + tablespaceInfo['table_space_name'] + '_data_file_' + datafileInfo['file_name'],
                            pId: oracleTimepoint.time_point_uuid + '_oracle_database_instance_' + tablespaceInfo['table_space_name'],
                            name: datafileInfo['file_name'],
                            title: datafileInfo['file_name'],
                            file_name: datafileInfo['file_name'],
                            file_id: datafileInfo['file_id'],
                            isParent: false,
                            eventtype: 'data_file',
                            icon: './img/fs/wenjian.png',
                            checked: true,
                            chkDisabled: tablespaceChkDisabled,
                        });
                    }
                }
            }
        }
        return nodes;
    };

    /**
     * 渲染Oracle恢复内容树
     * 结构
     *  数据库类别/
     *      数据库/                      =>  勾选且不可更改
     *          实例/                   =>  勾选且不可更改
     *              表空间              =>  勾选且不可更改
     *      控制文件/                   =>  勾选且不可更改
     *      配置文件/
     *          参数文件(pfile/spfile)  =>  空实例恢复勾选且不可更改
     *          监听文件(listener)      =>  空实例恢复勾选且不可更改
     *          网络(sqlnet)           =>  空实例恢复勾选且不可更改
     *          本地服务名(tnsnames)    =>  空实例恢复勾选且不可更改
     *          密码文件(password)      =>  RAC恢复空实例允许输入密码，其他运行修改路径
     */
    const renderOracleRecoveryContentTree = () => {
        let oracleTimepoint = oracleTimepointList.select_timepoint;
        let baseNodes = getOracleBaseNodes(oracleTimepoint);
        let configFileNodes = getOracleConfigFileNode(oracleTimepoint);
        let tablespaceNodes = getOracleTablespaceNodes(oracleTimepoint);

        oracleRecoveryContent.tree = $.fn.zTree.init(
            $('#oracleRecoveryContent'),
            getOracleRecoveryContentTreeSetting(),
            baseNodes.concat(configFileNodes, tablespaceNodes)
        );
    };

    /**
     * 初始化Oracle恢复内容树
     */
    const initOracleRecoveryContentTree = () => {
        if (parseInt(oracleTimepointList.select_timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {
            $('.oracleTimepointStorageOfflineDiv').show();
            $('.oracleTimepointStorageOfflineDiv .alert-danger').show();
            $('.oracleRecoveryContentDiv').hide();
            return;
        }

        $('.oracleRecoveryContentDiv').show();
        resetOracleRecoveryContent();
        initOracleRecoveryContent().then(() => {
            renderOracleRecoveryContentTree();
        });
    };

    //////////////////// 结束-Oracle恢复内容 ////////////////////

    /**
     * 设置老时间策略
     */
    const setOldTimeStrategy = () => {
        let oldTimeStrategy = oldRecoveryTaskData.time_strategy.strategy;
        var strategy = [{
            mode: 7,
            strategy_type: oldTimeStrategy.time_type,
            days: oldTimeStrategy.days,
            start_time: oldTimeStrategy.start_time,
            roll_flag: false,
            roll_interval: '01:00:00',
            roll_end_time: '23:59:59'
        }];
        //延迟设置,因为这里icheck会默认修改里面的选中事件
        setTimeout(function () {
            $('#recoveryTimestrategy').strategy({
                dom: $('#recoveryTimestrategy'),
                config: strategy,
                backup_flag: 2
            });
            $('.rollDiv').hide(); //隐藏滚动执行
        });
    };

    /**
     * 设置老传输策略
     */
    const setOldTransferStrategy = () => {
        let transferStrategy = oldRecoveryTaskData.transfer_strategy;
        $('#transferEncryptedCheck').bootstrapSwitch('state', transferStrategy.encrypt_flag).trigger('switchChange.bootstrapSwitch');
        if (transferStrategy.encrypt_flag) {
            $('#transferEncryptMethod').val(transferStrategy.encrypt_method);
        }
    };

    const addSpeedList = function (list) {
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
        $('#speedItemSet').show();
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
     * 设置老限速策略
     */
    const setOldSpeedStrategy = () => {
        if (Array.isArray(oldRecoveryTaskData.speed_strategy.speedInfo) && oldRecoveryTaskData.speed_strategy.speedInfo.length) {
            addGlobalStrategy.init({
                'strategy_type': oldRecoveryTaskData.speed_strategy.strategy_type,
                speedInfo: oldRecoveryTaskData.speed_strategy.speedInfo,
                initSpeedFlag: 1,
            });
        }
    };

    /**
     * 初始化恢复脚本
     */
    const setOldRecoveryScript = () => {
        let oldDbInfo = oldRecoveryTaskData.recovery_info.db_config_list[0];
        if (
            (!Array.isArray(oldDbInfo.before_task_script) || !oldDbInfo.before_task_script.length) &&
            (!Array.isArray(oldDbInfo.after_task_script) || !oldDbInfo.after_task_script.length) &&
            (!Array.isArray(oldDbInfo.verification_script) || !oldDbInfo.verification_script.length)
        ) {
            $('#scriptConfigSwitch').bootstrapSwitch('state', false).trigger('switchChange.bootstrapSwitch');
            return;
        }
        $('#scriptConfigSwitch').bootstrapSwitch('state', true).trigger('switchChange.bootstrapSwitch');
        $('.beforeRecoveryScriptConfig, .afterRecoveryScriptConfig, .validateScriptConfig').html('');
        let beforeTaskScript = undefined;
        if (oldDbInfo.before_task_script.length) {
            beforeTaskScript = oldDbInfo.before_task_script;
        }
        let afterTaskScript = undefined;
        if (oldDbInfo.after_task_script.length) {
            afterTaskScript = oldDbInfo.after_task_script;
        }
        let validateScript = undefined;
        if (oldDbInfo.verification_script.length) {
            validateScript = oldDbInfo.verification_script;
        }
        $.fn.initVinScript({
            class: 'beforeRecoveryScriptConfig',
            script_details: beforeTaskScript,
        });
        $.fn.initVinScript({
            class: 'afterRecoveryScriptConfig',
            script_details: afterTaskScript,
        });
        $.fn.initVinScript({
            class: 'validateScriptConfig',
            script_details: validateScript,
            include_list: [6],
        });
    };

    /**
     * 设置之前的过载保护
     */
    const setOldRecoveryOverload = () => {
        $('#ignoreResourceLimit').bootstrapSwitch('state', oldRecoveryTaskData.recovery_info.ignore_resource_limiting_flag);
    };

    /**
     * 设置SQL Server老恢复配置
     */
    const setSQLServerOldRecoveryConfig = () => {
        if (oldRecoveryTaskData.recovery_info.recovery_type != PATH_TYPE_ENUM.CREATE) {
            return;
        }
        for (const oldDbInfo of oldRecoveryTaskData.recovery_info.db_config_list) {
            let newDbInput = $(`input[data-db-uuid="new_db_name_${oldDbInfo.db_uuid}"]`);
            if (newDbInput.length) {
                newDbInput.val(oldDbInfo.new_db_name);
                newDbInput.attr('data-content', oldDbInfo.new_db_name);
            }

            let datafileDiv = $(`div[data-db-uuid="datafile_${oldDbInfo.db_uuid}"]`);
            if (datafileDiv.length) {
                $.fn.PathTreeSelector.getPathTreeSelector(datafileDiv.attr('id')).init({
                    target_id: datafileDiv.attr('id'),
                    select_mode: 2,
                    selected_path: oldDbInfo.data_file_path,
                    select_only: false,
                });
                for (const nodeKey in recoveryMethod.db_config) {
                    let dbConfig = recoveryMethod.db_config[nodeKey];
                    if (dbConfig.db_uuid === oldDbInfo.db_uuid) {
                        recoveryMethod.db_config[nodeKey].db_datafile_path = oldDbInfo.data_file_path;
                        break;
                    }
                }
            }
            let logfileDiv = $(`div[data-db-uuid="logfile_${oldDbInfo.db_uuid}"]`);
            if (logfileDiv.length) {
                $.fn.PathTreeSelector.getPathTreeSelector(logfileDiv.attr('id')).init({
                    target_id: logfileDiv.attr('id'),
                    select_mode: 2,
                    selected_path: oldDbInfo.log_file_path,
                    select_only: false,
                });
                for (const nodeKey in recoveryMethod.db_config) {
                    let dbConfig = recoveryMethod.db_config[nodeKey];
                    if (dbConfig.db_uuid === oldDbInfo.db_uuid) {
                        recoveryMethod.db_config[nodeKey].db_logfile_path = oldDbInfo.log_file_path;
                        break;
                    }
                }
            }
        }
    };

    /**
     * 设置Oracle老恢复配置
     */
    const setOracleOldRecoveryConfig = () => {
        let datafilePath = oldRecoveryTaskData.recovery_info.db_config_list[0].data_file_path;
        if (datafilePath.length) {
            $.fn.PathTreeSelector.getPathTreeSelector('oracleRecoveryDataPath').init({
                target_id: 'oracleRecoveryDataPath',
                select_mode: 2,
                select_only: false,
                selected_path: datafilePath,
            });
        }
        $('#recoveryThreadDiv').spinner('value', oldRecoveryTaskData.transfer_strategy.channel_count);
    };

    /**
     * 设置DM老恢复配置
     */
    const setDmOldRecoveryConfig = () => {
        if (oldRecoveryTaskData.recovery_info.recovery_type == PATH_TYPE_ENUM.SPECIFY_FOLDER) {
            $.fn.PathTreeSelector.getPathTreeSelector('dmFilePath').init({
                target_id: 'dmFilePath',
                select_mode: 2,
                selected_path: oldRecoveryTaskData.recovery_info.db_config_list[0].data_file_path,
                select_only: false,
            });
        }
    };

    /**
     * 设置Postgres老恢复配置
     */
    const setPostgresOldRecoveryConfig = () => {
        switch (oldRecoveryTaskData.recovery_info.recovery_type) {
            case PATH_TYPE_ENUM.CREATE:
                let datafilePath = oldRecoveryTaskData.recovery_info.db_config_list[0].data_file_path;
                let datafileList = datafilePath.split(':');
                if (datafileList.length == 2) {
                    $.fn.PathTreeSelector.getPathTreeSelector('pgDataFilePath').init({
                        target_id: 'pgDataFilePath',
                        select_mode: 2,
                        selected_path: datafileList[0],
                        select_only: false,
                    });
                    $('#pgInstancePort').val(datafileList[1]);
                }
                $.fn.PathTreeSelector.getPathTreeSelector('pgArchivelogFilePath').init({
                    target_id: 'pgArchivelogFilePath',
                    select_mode: 2,
                    selected_path: oldRecoveryTaskData.recovery_info.db_config_list[0].log_file_path,
                    select_only: false,
                });
                break;
            case PATH_TYPE_ENUM.SPECIFY_FOLDER:
                $.fn.PathTreeSelector.getPathTreeSelector('pgSpecifyFolder').init({
                    target_id: 'pgSpecifyFolder',
                    select_mode: 2,
                    selected_path: oldRecoveryTaskData.recovery_info.db_config_list[0].data_file_path,
                    select_only: false,
                });
                $.fn.PathTreeSelector.getPathTreeSelector('pgCustomArchivelog').init({
                    target_id: 'pgCustomArchivelog',
                    select_mode: 2,
                    selected_path: oldRecoveryTaskData.recovery_info.db_config_list[0].log_file_path,
                    select_only: false,
                });
                break;
            default:
                break;
        }
    };

    /**
     * 设置MySQL老恢复配置
     */
    const setMySQLOldRecoveryConfig = () => {
        if (oldRecoveryTaskData.recovery_info.db_config_list[0].detail.start_type == MYSQL_START_TYPE_ENUM.SERVICE) {
            $('#mysqlStartType').val(MYSQL_START_TYPE_ENUM.SERVICE).trigger('change');
            $('#startcommand').val(oldRecoveryTaskData.recovery_info.db_config_list[0].detail.start_command);
        } else {
            $('#mysqlStartType').val(MYSQL_START_TYPE_ENUM.COMMAND).trigger('change');
            $('#mysqlCustomStart').val(oldRecoveryTaskData.recovery_info.db_config_list[0].detail.start_command);
            $('#mysqlCustomStop').val(oldRecoveryTaskData.recovery_info.db_config_list[0].detail.stop_command);
        }
    };

    /**
     * 设置SAP HANA老恢复配置
     */
    const setSAPHANAOldRecoveryConfig = () => {
        if (oldRecoveryTaskData.recovery_info.recovery_type == PATH_TYPE_ENUM.CREATE) {
            for (const oldDbInfo of oldRecoveryTaskData.recovery_info.db_config_list) {
                let newDbInput = $(`input[data-db-uuid="new_db_name_${oldDbInfo.db_uuid}"]`);
                if (newDbInput.length) {
                    newDbInput.val(oldDbInfo.new_db_name);
                    newDbInput.attr('data-content', oldDbInfo.new_db_name).trigger('change');
                }
            }
        }
    };

    /**
     * 设置MongoDB老恢复配置
     */
    const setMongoDBOldRecoveryConfig = () => {
        // 传输线程
        $('#mongodbRecoveryThreadDiv').spinner('value', oldRecoveryTaskData.transfer_strategy.thread_num);
        // 客户端并行数量
        $('#parallelNumSpinner').spinner("value", oldRecoveryTaskData.transfer_strategy.max_object_transport_parallel_nums);
    };

    /**
     * 设置步骤3数据
     */
    const setStep3OldData = () => {
        if (recoverySource.recovery_source_type != RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            return;
        }
        if (!editTaskFlag) {
            return;
        }
        // 恢复方式
        $('#pathType').val(oldRecoveryTaskData.recovery_info.recovery_type).trigger('change');
        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.SQLSERVER:
                setSQLServerOldRecoveryConfig();
                break;
            case CONF.DB_TYPE.ORACLE:
                setOracleOldRecoveryConfig();
                break;
            case CONF.DB_TYPE.DM:
                setDmOldRecoveryConfig();
                break;
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.UXDB:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.OPENGAUSS:
            case CONF.DB_TYPE.VASTBASE:
                setPostgresOldRecoveryConfig();
                break;
            case CONF.DB_TYPE.MYSQL:
            case CONF.DB_TYPE.MARIA:
                setMySQLOldRecoveryConfig();
                break;
            case CONF.DB_TYPE.SAPHANA:
                setSAPHANAOldRecoveryConfig();
                break;
            case CONF.DB_TYPE.TIDB:
                break;
            case CONF.DB_TYPE.MONGODB:
                setMongoDBOldRecoveryConfig();
                break;
            default:
                break;
        }
        // 设置时间策略
        // setOldTimeStrategy();
        // 设置传输策略
        setOldTransferStrategy();
        // 设置限速策略
        // setOldSpeedStrategy();
        // 设置恢复脚本
        setOldRecoveryScript();
        // 设置过载保护
        setOldRecoveryOverload();
    };

    /**
     * 初始化完整性校验策略
     */
    const initCompleteCheckStrategy = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {  // 演练任务不需要
            return;
        }
        if (
            recoverySource.db_type === CONF.DB_TYPE.ORACLE ||
            recoverySource.db_type === CONF.DB_TYPE.TIDB ||
            recoverySource.db_type === CONF.DB_TYPE.SAPHANA
        ) {
            return;
        }
        let disableIntegrityCheckFlag = true;
        for (const selectedKey in recoverySource.time_point_list) {
            let selectedNode = recoverySource.time_point_list[selectedKey];
            if (selectedNode.eventtype === 'timepoint' && selectedNode.integrity_check_flag) {
                disableIntegrityCheckFlag = false;
                break;
            }
        }
        $('#integrityCheck').completeStrategyCovery(CONF.MODULE_TYPE.DB, 0, disableIntegrityCheckFlag);
    };

    /**
     * 初始化演练任务的资源限制显示
     */
    const initDrillResourceLimitShow = () => {
        // 1. 获取所有节点
        Metronic.blockUI({target: '#tab_advanced_overload', animate: true});
        pAjaxRequest({offset: 0, limit: 100}, `/api/v1/nodes`, `GET`, res => {
            Metronic.unblockUI('#tab_advanced_overload');
            if (!res.success) {
                return;
            }
            if (!res.data || !res.data.rows || res.data.rows.length === 0) {  // 避免没有分配节点的用户报错的问题
                return;
            }
            // 2. 遍历所有节点uuid, 并获取节点信息
            initResourceLimit(res.data.rows.map(v => v.node_uuid));
        });
    };

    /**
     * 根据磁带显示配置的可行性
     */
    const setVisibleForTape = () => {
        if (storage_type === CONF.BD_STORAGE_TYPE.TAPE || drillStorageType === CONF.BD_STORAGE_TYPE.TAPE) {
            $('#tab3 .safeStrategyLi').removeClass('active').hide();  // 隐藏安全策略
            $('#safeStrategyShowDiv').hide();
            $('#tab_safe_strategy').removeClass('active').removeClass('in').addClass('fade');
            $('#tab3 .recoveryConfigLi, #tab3 .commonLi, #tab3 .transferLi, #tab3 .scriptLi, #tab3 .safeStrategyLi, #tab3 .highLi').removeClass('active');
            $('#tab_recovery_config, #tab_common, #tab_transfer, #tab_script, #tab_safe_strategy, #tab_other').removeClass('active').removeClass('in').addClass('fade');
            $('#tab3 .recoveryConfigLi').addClass('active');
            $('#tab_recovery_config').addClass('active').addClass('in').removeClass('fade');
        } else {
            if (CONF.FUNCTIONS.includes('integrity')) {
                $('#tab3 .safeStrategyLi').show();  // 显示安全策略
                $('#safeStrategyShowDiv').show();
            }
        }
    };

    /**
     * 设置步骤3的授权功能
     */
    const setStep3AuthFunc = () => {
        if (!CONF.FUNCTIONS.includes('integrity')) {
            $('#tab3 .safeStrategyLi').hide();
        }
    };

    var initStep3Div = function () {
        $('.pathDiv').show();
        let targetNodes = [];
        if (recoveryTarget.create_new_instance_flag) {
            targetNodes = createNewInstanceHostTree.getCheckedNodes();
        } else {
            targetNodes = hostTree.getCheckedNodes();
        }
        var pathType = parseInt($('#pathType').val());

        $('.recoveryItem').hide();  // 先全部隐藏
        $('#db-config-caption').hide(); // 隐藏批量配置
        $('.threadDiv').hide();  		// 传输线程隐藏
        $('.mongodbThreadDiv').hide();
        $('.speedlimitDiv').show(); // 限速策略全都有, 需要显示出来
        $('.openDbDiv').hide();
        $('#transfer').collapse('show'); // 默认展开传输策略
        $('.timer-recovery-newest-tips').hide();  // 隐藏错误提示
        $('#pathType').prop('disabled', false);
        $('#oracleOpenDbTips').hide();
        $('#oracleOpenDbFullTips').hide();
        $('#pgOpenDbTips').hide();
        $('#mongodbOpenDbTips').hide();

        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            $('#scriptConfigTips').hide();
            $('#newestScriptConfigTips').show();
        } else {
            $('#scriptConfigTips').show();
            $('#newestScriptConfigTips').hide();
        }
        $('.scriptConfigDiv').show();
        $('#scriptConfigSwitch').bootstrapSwitch('state', false).trigger('switchChange.bootstrapSwitch');

        // 恢复默认打开忽略节点资源限制
        $('#ignoreResourceLimit').bootstrapSwitch('state', true);
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            // 演练任务默认关闭忽略节点资源限制
            $('#ignoreResourceLimit').bootstrapSwitch('state', false);
        }

        // 高级配置
        $('#tabAdvanceTabUl li').removeClass('active');
        $('#tabAdvancedContent .tab-pane').removeClass('active').removeClass('in');
        // 隐藏数据库配置
        $('#tabAdvancedDatabaseConfigTab').hide();
        $('.advancedDatabaseConfigShowDiv').hide();
        // 显示重试策略
        $('#tabAdvancedRetryTab').addClass('active');
        $('#tab_advanced_retry').addClass('active').addClass('in');
        // 隐藏oracle恢复方式提示信息
        $('.recovery-type_full_in_exp').hide();
        $('.recovery-type_incomplete').hide();

        data.recoverInfo.instancename = recoveryTarget.instance_uuid;
        let datafilePathSelector = new $.fn.PathTreeSelector.cls(recoveryTarget.agent_uuid);
        let logfilePathSelector = new $.fn.PathTreeSelector.cls(recoveryTarget.agent_uuid);
        let restoreArchivelogSelector = new $.fn.PathTreeSelector.cls(recoveryTarget.agent_uuid);
        // pg的指定文件夹恢复
        let specifyFolderSelector = new $.fn.PathTreeSelector.cls(recoveryTarget.agent_uuid);
        // pg的自定义归档目录
        let customArchivelogSelector = new $.fn.PathTreeSelector.cls(recoveryTarget.agent_uuid);
        recoveryMethod.path_type = pathType;
        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.SQLSERVER:
                /**
                 * 如果勾选的多个备份点里，有一个跟恢复目标实例不一致，就只有新建数据库恢复
                 * 原因: SQL Server的路径包含有实例名，因此不能覆盖恢复
                 */
                for (const node of data.pointInfo.points) {
                    if (recoveryTarget.instance_uuid !== node.instanceuuid) {
                        pathType = PATH_TYPE_ENUM.CREATE;
                        $('#pathType').val(pathType).prop('disabled', true);
                        break;
                    }
                }
                // 全局脚本
                // $('.scriptConfigDiv').hide();
                // $('.scriptConfigScriptDiv').hide();
                // 设置表格标题
                $('.multiDbConfigOldDb').html(LANG.UI_DB_OLD_DB_NAME); // 新旧数据库
                $('.multiDbConfigNewDb').html(LANG.UI_DB_NEW_DB_NAME);
                doSqlServerShowStep3Item(pathType);
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;

                // 恢复最新时间点目前只支持 新建数据库恢复
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                    $('#pathType').val(PATH_TYPE_ENUM.CREATE).attr('disabled', true).trigger('change');
                }
                $('#timerRecoveryNewestTips').html(LANG.UI_DB_RECOVERY_SQLSERVER_RECOVERY_NEWEST_TIPS2);
                break;
            case CONF.DB_TYPE.ORACLE:
                /**
                 * 完全恢复
                 *   1. 传最新备份点
                 *   2. 不需要回滚时间
                 *   3. 数据恢复目标路径
                 * 不完全恢复
                 *   1. 恢复到最新时间点
                 *     a. 传最新备份点
                 *     b. 回滚时间为最新备份点的src_end_timepoint（bd_backup_timepoint）
                 *     c. 数据恢复目标路径
                 *   2. 恢复到指定时间
                 *     a. 最接近选择时间的备份点
                 *       i. 选择的时间范围根据实例的第一个备份点的src_start_timepoint到最后一个备份点的src_end_timepoint
                 *       ii. 连续时间区间，不考虑缺失的时间段
                 *     b. 回滚时间为页面选择的时间
                 *     c. 数据恢复目标路径
                 * 导出恢复
                 *   1. 初始化备份点树，选择备份点
                 *   2. 选择导出目录
                 */
                $('.oracleRecoveryTimeShowBackDiv').hide();  // 恢复时间
                $('#oracleRecoveryPathHelp1').show();
                $('#oracleRecoveryPathHelp2').hide();
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    $('#pathType').val(PATH_TYPE_ENUM.INCOMPLETE);  // 默认选择不完全恢复
                    $('#oracleRecoveryTimepoint').val(ORACLE_RECOVERY_TIMEPOINT_ENUM.NEWEST).removeAttr('disbaled');  // 默认选择最新备份点
                    pathType = PATH_TYPE_ENUM.INCOMPLETE;
                    recoveryMethod.path_type = pathType;
                    oracleRecoveryTime = null;
                    setOracleStorageHasNewestTimepointFlag()
                        .then(initOracleTimepoint)
                        .then(() => {  // 获取备份点信息
                            initOracleTimepointChainByRecoveryTime();  // 初始化恢复树
                            oldNodeUuid = null;  // 每次点击下一步都要初始化网络
                            doOracleShowStep3Item(pathType);  // 根据恢复方式设置相应的配置项
                            // 初始化导出恢复备份点数
                            initOracleExportTree();
                            // 初始化还原归档日志恢复
                            initRestoreArchivelog();
                        });
                    logfilePathSelector.init({target_id: 'exportPath', select_mode: 2, select_only: false});
                    restoreArchivelogSelector.init({
                        target_id: 'restoreArchivelog',
                        select_mode: 2,
                        select_only: false
                    });
                    datafilePathSelector.init({
                        target_id: 'oracleRecoveryDataPath',
                        select_mode: 2,
                        select_only: false
                    });
                    if (recoveryTarget.create_new_instance_flag) {
                        $('#oracleRecoveryPathHelp1').hide();
                        $('#oracleRecoveryPathHelp2').show();
                        $('#pathType').val(PATH_TYPE_ENUM.INCOMPLETE);
                        $('#fullRecovery').hide();  // 完全恢复
                        $('#exportRecovery').hide();  // 导出恢复
                        $('.recovery-type_full_in_exp').hide();
                        $('.recovery-type_incomplete').show();
                        // 新实例名的提示信息
                        let helpBlock = `
                        1. ${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_NAME_TIPS1}<br>
                        2. ${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_NAME_TIPS2}<br>
                        3. ${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_NAME_TIPS3}
                        `;
                        $('#newInstanceNameHelpBlock').html(helpBlock);
                        unsetCreateNewInstanceConfigFlag();
                        recoveryMethod.create_new_instance_config.instance_name = '';  // 这里是恢复到新实例，所以实例名清空
                    } else {
                        $('#fullRecovery').show();  // 完全恢复
                        $('#exportRecovery').show();  // 导出恢复
                        $('.recovery-type_full_in_exp').show();
                        $('.recovery-type_incomplete').hide();
                    }
                } else {  // Oracle定时恢复最新点只支持 覆盖恢复和指定文件夹恢复
                    pathType = PATH_TYPE_ENUM.INCOMPLETE;
                    recoveryMethod.path_type = pathType;
                    $('#pathType').val(pathType).prop('disabled', 'disabled');
                    $('#exportRecovery').hide();
                    $('#restoreArchivelogRecovery').hide();
                    doOracleShowStep3Item(pathType);  // 根据恢复方式设置相应的配置项
                    datafilePathSelector.init({
                        target_id: 'oracleRecoveryDataPath',
                        select_mode: 2,
                        select_only: false
                    });
                }
                $('#timerRecoveryNewestTips').html(LANG.UI_DB_RECOVERY_ORACLE_RECOVERY_NEWEST_TIPS1);
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;
                break;
            case CONF.DB_TYPE.DM:
                if (recoveryTarget.agent_uuid === recoverySource.agent_uuid) {
                    $('#pathType').removeAttr('disabled');
                }
                doDMShowStep3Item(pathType);
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;
                datafilePathSelector.init({target_id: 'dmFilePath', select_mode: 2, select_only: false});
                // DM 恢复最新点支持覆盖和指定文件夹
                $('#timerRecoveryNewestTips').html(LANG.UI_DB_RECOVERY_DM_RECOVERY_NEWEST_TIPS1);
                break;
            case CONF.DB_TYPE.MYSQL:
            case CONF.DB_TYPE.MARIA:
                // 设置默认值
                for (const targetNode of targetNodes) {
                    if (targetNode.eventtype === 'instance') {
                        $('#mysqlCustomStart').val(targetNode.app_detail?.start_command);
                        break;
                    }
                }
                doMysqlShowStep3Item(pathType);
                logfilePathSelector.init({target_id: 'logfilePath', select_mode: 2, select_only: false});
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;

                // 恢复最新时间点目前只支持 原数据库覆盖恢复
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                    $('#pathType').val(PATH_TYPE_ENUM.COVER).attr('disabled', true).trigger('change');
                }
                break;
            case CONF.DB_TYPE.KINGBASE:
                if (recoveryTarget.cluster_flag) {  // KingBase集群不支持新建和指定
                    // 集群
                    pathType = PATH_TYPE_ENUM.COVER;
                    $('#pathType').val(pathType).prop('disabled', true);
                } else {
                    // 单机
                }
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;
                doPostgresShowStep3Item(pathType);
                datafilePathSelector.init({target_id: 'pgDataFilePath', select_mode: 2, select_only: false});
                logfilePathSelector.init({target_id: 'pgArchivelogFilePath', select_mode: 2, select_only: false});
                specifyFolderSelector.init({target_id: 'pgSpecifyFolder', select_mode: 2, select_only: false});
                customArchivelogSelector.init({target_id: 'pgCustomArchivelog', select_mode: 2, select_only: false});
                $('.openDbDiv').show();
                $('#oracleOpenDbTips').hide();
                $('#pgOpenDbTips').show();
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    $('#openDbCheck').bootstrapSwitch('disabled', false).bootstrapSwitch('state', false);
                } else {
                    $('#openDbCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
                }

                // KingBase恢复最新时间点目支持覆盖、新建、指定文件夹
                $('#timerRecoveryNewestTips').html(LANG.UI_DB_RECOVERY_PG_RECOVERY_NEWEST_TIPS1);
                break;
            case CONF.DB_TYPE.POSTGRE:  // pg、AntDB、UXDB不需要选择实例
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.UXDB:
                if (recoveryTarget.cluster_flag) {  // 恢复到集群仅允许覆盖恢复
                    // 集群
                    pathType = PATH_TYPE_ENUM.COVER;
                    $('#pathType').val(pathType).prop('disabled', true);
                } else {
                    // 单机
                }
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;
                doPostgresShowStep3Item(pathType);
                datafilePathSelector.init({target_id: 'pgDataFilePath', select_mode: 2, select_only: false});
                logfilePathSelector.init({target_id: 'pgArchivelogFilePath', select_mode: 2, select_only: false});
                specifyFolderSelector.init({target_id: 'pgSpecifyFolder', select_mode: 2, select_only: false});
                customArchivelogSelector.init({target_id: 'pgCustomArchivelog', select_mode: 2, select_only: false});
                $('.openDbDiv').show();
                $('#oracleOpenDbTips').hide();
                $('#pgOpenDbTips').show();
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    $('#openDbCheck').bootstrapSwitch('disabled', false).bootstrapSwitch('state', false);
                } else {
                    $('#openDbCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
                }

                // postgres、AntDB、UXDB恢复最新时间点目支持覆盖、新建、指定文件夹
                $('#timerRecoveryNewestTips').html(LANG.UI_DB_RECOVERY_PG_RECOVERY_NEWEST_TIPS1);
                break;
            case CONF.DB_TYPE.HIGHGO:
                if (recoveryTarget.cluster_flag) {
                    // 集群
                    pathType = PATH_TYPE_ENUM.COVER;
                    $('#pathType').val(pathType).prop('disabled', true);
                } else {
                    // 单机
                }
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;
                doPostgresShowStep3Item(pathType);
                datafilePathSelector.init({target_id: 'pgDataFilePath', select_mode: 2, select_only: false});
                logfilePathSelector.init({target_id: 'pgArchivelogFilePath', select_mode: 2, select_only: false});
                specifyFolderSelector.init({target_id: 'pgSpecifyFolder', select_mode: 2, select_only: false});
                customArchivelogSelector.init({target_id: 'pgCustomArchivelog', select_mode: 2, select_only: false});
                $('.openDbDiv').show();
                $('#oracleOpenDbTips').hide();
                $('#pgOpenDbTips').show();
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    $('#openDbCheck').bootstrapSwitch('disabled', false).bootstrapSwitch('state', false);
                } else {
                    $('#openDbCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
                }

                // HighGO DB恢复最新时间点目支持覆盖、新建、指定文件夹，恢复到集群只支持覆盖
                $('#timerRecoveryNewestTips').html(LANG.UI_DB_RECOVERY_PG_RECOVERY_NEWEST_TIPS1);
                break;
            case CONF.DB_TYPE.VASTBASE:
            case CONF.DB_TYPE.OPENGAUSS:
                if (recoveryTarget.cluster_flag) {
                    // 集群
                    pathType = PATH_TYPE_ENUM.COVER;
                    $('#pathType').val(pathType).prop('disabled', true);
                } else {
                    // 单机
                }
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;
                doPostgresShowStep3Item(pathType);
                datafilePathSelector.init({target_id: 'pgDataFilePath', select_mode: 2, select_only: false});
                logfilePathSelector.init({target_id: 'pgArchivelogFilePath', select_mode: 2, select_only: false});
                specifyFolderSelector.init({target_id: 'pgSpecifyFolder', select_mode: 2, select_only: false});
                customArchivelogSelector.init({target_id: 'pgCustomArchivelog', select_mode: 2, select_only: false});
                $('.openDbDiv').show();
                $('#oracleOpenDbTips').hide();
                $('#pgOpenDbTips').show();
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    $('#openDbCheck').bootstrapSwitch('disabled', false).bootstrapSwitch('state', false);
                } else {
                    $('#openDbCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
                }

                // 恢复最新时间点目前只支持 原数据库覆盖恢复
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                    $('#pathType').val(PATH_TYPE_ENUM.COVER).attr('disabled', true).trigger('change');
                }
                break;
            case CONF.DB_TYPE.MONGODB:
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;
                $('#tabAdvancedDatabaseConfigTab').addClass('active').show();
                $('#tab_advanced_database_config').addClass('active').addClass('in');
                $('.advancedDatabaseConfigShowDiv').show();
                $('#tabAdvancedRetryTab').removeClass('active');
                $('#tab_advanced_retry').removeClass('active').removeClass('in');
                // 打开数据库
                $('.openDbDiv').show();
                $('#mongodbOpenDbTips').show();
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    $('#openDbCheck').bootstrapSwitch('disabled', false).bootstrapSwitch('state', true);
                } else {
                    $('#openDbCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
                }
                doMongoDBShowStep3Item(pathType);
                datafilePathSelector.init({target_id: 'mongodbDatafilePath', select_mode: 2, select_only: false});

                // 恢复最新时间点目前只支持 原数据库覆盖恢复
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                    $('#pathType').val(PATH_TYPE_ENUM.COVER).attr('disabled', true).trigger('change');
                } else {
                    if (storage_type === CONF.BD_STORAGE_TYPE.TAPE || drillStorageType === CONF.BD_STORAGE_TYPE.TAPE) {  // 磁带隐藏客户端并行数量
                        $('#tabAdvancedDatabaseConfigTab').removeClass('active').hide();
                        $('#tab_advanced_database_config').removeClass('active').removeClass('in');
                        $('.advancedDatabaseConfigShowDiv').hide();
                        $('#parallelNumSpinner').spinner('value', 1);
                        $('#tabAdvancedRetryTab').addClass('active');
                        $('#tab_advanced_retry').addClass('active').addClass('in');
                    }
                }
                break;
            case CONF.DB_TYPE.TIDB:
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;

                // TiDB仅支持覆盖恢复
                $('#pathType').val(PATH_TYPE_ENUM.COVER);
                // 打开数据库
                $('.openDbDiv').show();
                $('#oracleOpenDbTips').hide();
                $('#pgOpenDbTips').show();
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    $('#openDbCheck').bootstrapSwitch('disabled', false).bootstrapSwitch('state', false);
                    initTiDBTimepoint().then(() => {
                        oldNodeUuid = null;  // 每次点击下一步都要初始化网络
                        doTiDBShowStep3Item(pathType);
                    });
                } else {
                    $('#openDbCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
                    doTiDBShowStep3Item(pathType);
                }
                break;
            case CONF.DB_TYPE.SAPHANA:  // SAP HANA支持覆盖、新建恢复
                // 全局脚本
                // $('.scriptConfigDiv').hide();
                // $('.scriptConfigScriptDiv').hide();
                // 设置表格标题
                $('.multiDbConfigOldDb').html(LANG.UI_DB_OLD_DB_NAME); // 新旧数据库
                $('.multiDbConfigNewDb').html(LANG.UI_DB_NEW_DB_NAME);
                data.recoverInfo.instancename = recoveryTarget.instance_uuid;
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    initSAPHANATimepoint().then(() => {
                        oldNodeUuid = null;  // 每次点击下一步都要初始化网络
                        doSAPHANAShowStep3Item(pathType);
                    });
                    $('#sapHanaRecoveryTime').val(RECOVERY_TIME_FLAG_ENUM.NEWEST).removeAttr('disabled');
                } else {
                    doSAPHANAShowStep3Item(pathType);
                    $('#sapHanaRecoveryTime').val(RECOVERY_TIME_FLAG_ENUM.NEWEST).attr('disabled', true);
                }
                $('.sapHanaRecoveryTimeDiv').show();
                $('#sapHanaRecoveryTimeTips1').show();
                $('#sapHanaRecoveryTimeTips2').hide();
                $('#sapHanaRecoveryTimeTips3').hide();
                break;
        }

        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            //初始化传输网络
            if (networkFlag) {
                if (recoverySource.db_type === CONF.DB_TYPE.SAPHANA) {  // SAP HANA备份点在后面选择，因此这里不初始化传输网络
                    $('.transfernetworkDiv').show();
                } else if (recoverySource.db_type === CONF.DB_TYPE.ORACLE) {  // Oracle传输网络需要在选择备份点后初始化
                    $('.transfernetworkDiv').show();
                } else if (recoverySource.db_type === CONF.DB_TYPE.TIDB) {  // TiDB传输网络需要在选择备份点后初始化
                    $('.transfernetworkDiv').show();
                } else {
                    $('.transfernetworkDiv').show();
                    initNetworkList();
                }
            } else {
                $('.transfernetworkDiv').hide();
            }
            $('.sqlValidateLi').hide();
            if (
                recoverySource.db_type !== CONF.DB_TYPE.SAPHANA &&
                recoverySource.db_type !== CONF.DB_TYPE.ORACLE &&
                recoverySource.db_type !== CONF.DB_TYPE.TIDB
            ) {  // 这三个数据库的备份点在后续步骤3确认的，这一步不初始化资源限制
                // 初始化资源限制
                initResourceLimit([data.pointInfo.points[0].node_uuid]);
            }
        } else {
            $('.sqlValidateLi').show();
            $('.transfernetworkDiv').hide();
            // 初始化演练任务的资源限制显示
            initDrillResourceLimitShow();
        }

        initCompleteCheckStrategy();

        // 设置步骤3数据
        setStep3OldData();
        setVisibleForTape();
        setStep3AuthFunc();
    }

    var step2Valid = function (showTitleCallback) {
        recoveryTarget.create_new_instance_flag = false;
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT && recoverySource.db_type === CONF.DB_TYPE.ORACLE) {
            recoveryTarget.create_new_instance_flag = !!$('#createNewInstance').get(0).checked;
        }
        let nodes = [];
        if (!recoveryTarget.create_new_instance_flag) {
            if (!hostTree || !hostTree.getCheckedNodes().length) {
                $(".setrecover2tip").html(LANG.UI_DB_RECOVERY_SELECT_INSTANCE).show();
                return false;
            }
            nodes = hostTree.getCheckedNodes();
        } else {  // 新建实例恢复
            if (!createNewInstanceHostTree || !createNewInstanceHostTree.getCheckedNodes().length) {
                $(".setrecover2tip").html(LANG.UI_DB_RECOVERY_SELECT_INSTANCE).show();
                return false;
            }
            nodes = createNewInstanceHostTree.getCheckedNodes();
        }
        //是否需要显示传输网络标记
        networkFlag = getNetworkFlag(nodes);
        var showStr = nodes[0].name;
        //如果选择的是实例
        if (parseInt(nodes[0].type) === 2) {
            var parent = nodes[0].getParentNode();
            showStr = parent.name + '/' + nodes[0].name;
        }
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            defaultConfig.recovery_type = ['atTime', 'immediate'];
            // $('.timeStrategyDiv').show();
            // $(`#recovertype option[value="${TIME_STRATEGY_RECOVERY_TYPE_ENUM.IMMEDIATELY}"]`).show();
            // $(`#recovertype option[value="${TIME_STRATEGY_RECOVERY_TYPE_ENUM.STRATEGY}"]`).hide();
            // $(`#recovertype option[value="${TIME_STRATEGY_RECOVERY_TYPE_ENUM.ONCE_TIME}"]`).show();
            // $('#recovertype').val(TIME_STRATEGY_RECOVERY_TYPE_ENUM.IMMEDIATELY).trigger('change');
        } else {
            defaultConfig.recovery_type = ['strategy'];
            // $('.timeStrategyDiv').show();
            // $(`#recovertype option[value="${TIME_STRATEGY_RECOVERY_TYPE_ENUM.IMMEDIATELY}"]`).hide();
            // $(`#recovertype option[value="${TIME_STRATEGY_RECOVERY_TYPE_ENUM.STRATEGY}"]`).show();
            // $(`#recovertype option[value="${TIME_STRATEGY_RECOVERY_TYPE_ENUM.ONCE_TIME}"]`).hide();
            // $('#recovertype').val(TIME_STRATEGY_RECOVERY_TYPE_ENUM.STRATEGY).trigger('change');
        }

        initTaskName();
        showStep2(showStr);
        //		initStrategyDes();
        showDiffTargetRecoveryDialog().then(loadSqlServerClusterActiveAgent).then(() => {
            if (recoverySource.db_type === CONF.DB_TYPE.ORACLE && recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {  // Oracle需要判断恢复到空实例的问题
                oracleRecoveryType = 1;  // 初始化
                if (nodes[0].eventtype === 'instance' && parseInt(nodes[0].app_auth_type) === 1) {
                    oracleRecoveryType = 2;
                }
                initStep3Div(); //初始化第三步界面
                $('a[href="#tab3"]').tab('show'); // 手动切换步骤页面
                showTitleCallback()
            } else {
                oracleRecoveryType = 1;
                initStep3Div(); //初始化第三步界面
                $('a[href="#tab3"]').tab('show'); // 手动切换步骤页面
                showTitleCallback()
            }
        });
        if (oldRecoveryTaskData) {
            defaultConfig.strategy = {
                time: {
                    type: 'strategy',
                    strategy: oldRecoveryTaskData.time_strategy.strategy
                },
                speedlimit: oldRecoveryTaskData.speed_strategy,
            };
        }

        $('#backupStrategyDiv').initBackupStrategy(defaultConfig);
        return false;
    }

    /**
     * 初始化步骤4的恢复目标树
     */
    const initStep4RecoveryTargetTree = () => {
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

        var checkNodes = hostTree.getCheckedNodes();
        let clusterTopFlag = false;
        let standaloneTopFlag = false;
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
                for (const childNode of checkNode.children) {
                    if (recoverySource.db_type == CONF.DB_TYPE.MONGODB) {
                        nodes.push(childNode);
                    } else {
                        nodes.push({
                            id: childNode.agent_uuid + '_' + childNode.instance_name,
                            pId: checkNode.cluster_uuid,
                            name: childNode.name,
                            title: childNode.name,
                            isParent: false,
                            nocheck: true,
                            eventtype: 'cluster_instance',
                            icon: './img/platform/storage.png',
                        });
                    }
                }
                selectFlag = true;
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
            }
        });
        $.fn.zTree.init($("#recoveryTargetTree"), setting, nodes);
    };

    var showStep2 = function (str) {
        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.ORACLE:
                if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                    str = `${$('.createNewInstanceLabel').html()}: ${getSwitchDes(recoveryTarget.create_new_instance_flag)}<br>`;
                    if (recoveryTarget.create_new_instance_flag) {
                        let checkedNodes = createNewInstanceHostTree.getCheckedNodes();
                        str += `${$('.createNewInstanceHostLabel').html()}: ${checkedNodes[0].name}<br>`;
                        $('#recoveryTargetTree').hide();
                    } else {
                        $('#recoveryTargetTree').show();
                        initStep4RecoveryTargetTree();
                    }
                    $('.recovershow').show().html(str);
                } else {
                    $('.recovershow').hide();
                    $('#recoveryTargetTree').show();
                    initStep4RecoveryTargetTree();
                }
                break;
            case CONF.DB_TYPE.SQLSERVER:
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
            case CONF.DB_TYPE.SAPHANA:
            case CONF.DB_TYPE.TIDB:
            case CONF.DB_TYPE.MONGODB:
                $('.recovershow').hide();
                $('#recoveryTargetTree').show();
                initStep4RecoveryTargetTree();
                break;
            default:
                $('.recovershow').show().html(str);
                $('#recoveryTargetTree').hide();
                break;
        }

        $('#logtime').val(endTimepoint);
    }

    function isNotLatinCode(string) {
        var latin1Regex = /[^\x00-\xFF]/;
        if (latin1Regex.test(string)) {
            UIToastr.showWarning(LANG.UI_DB_BACKUP_PASSWORD_ILLEAGLE, LANG.UI_DB_BACKUP_PASSWORD_TIPS);
            return false;
        }
        return true;
    }

    /**
     * 获取恢复脚本
     */
    const getRecoveryScript = () => {
        let scriptConfigSwitchFlag = !!$('#scriptConfigSwitch').get(0).checked;
        data.pointInfo.points[0].before_task_script = scriptConfigSwitchFlag ? $.fn.getVinScript('beforeRecoveryScriptConfig') : [];
        if (data.pointInfo.points[0].before_task_script === false) {
            return false;
        }
        data.pointInfo.points[0].after_task_script = scriptConfigSwitchFlag ? $.fn.getVinScript('afterRecoveryScriptConfig') : [];
        if (data.pointInfo.points[0].after_task_script === false) {
            return false;
        }
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            data.pointInfo.points[0].verification_script = [];
        } else {
            data.pointInfo.points[0].verification_script = scriptConfigSwitchFlag ? $.fn.getVinScript('validateScriptConfig') : [];
            if (data.pointInfo.points[0].verification_script === false) {
                return false;
            }
        }
        return true;
    };

    //////////////////// 开始-步骤3验证 ////////////////////

    var step3Valid = function (showTitleCallback) {
        // 验证时间策略
        if (!validateStep3TimeStrategy()) {
            return false;
        }
        let pathType = parseInt($('#pathType').val());

        data.recoverInfo.createflag = 0;
        data.recoverInfo.recoverposition = pathType; //恢复方式
        data.recoverInfo.olddbname = Object.values(recoverySource.time_point_list)[0].db_name;
        data.pointInfo.points[0].oldDbname = data.recoverInfo.olddbname;
        data.recoverInfo.newdbname = ''; //新数据库名
        data.recoverInfo.newfilepath = ''; //新建文件路径
        data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
        data.recoverInfo.newlogpath = ''; //新建日志路径|自定义归档目录
        data.pointInfo.points[0].logfile_path = data.recoverInfo.newlogpath;
        data.recoverInfo.password = ''; //数据库实例密码
        data.recoverInfo.logrolltime = ''; //日志回滚时间
        data.pointInfo.points[0].is_rollback = false;
        data.pointInfo.points[0].rollback_time = data.recoverInfo.logrolltime;
        data.recoverInfo.max_object_transport_parallel_nums = 1;  // 客户端并行数量
        data.recoverInfo.log_restore_start_time = '';
        data.recoverInfo.log_restore_end_time = '';
        data.recoverInfo.recovery_scene = 1;
        data.recoverInfo.open_db_flag = false;
        data.recoverInfo.redo_log_path = '';
        // 恢复脚本
        if (!getRecoveryScript()) {
            return false;
        }
        data.detail.start_command = ''; //启动命令
        data.highInfo.transfer = {}; //定义传输策略
        data.modify_pfile_flag = false;
        data.recovery_scene = 1;
        data.recovery_time_flag = parseInt($('#sapHanaRecoveryTime').val());
        $('#oracleRecoveryContentShowDiv').hide();
        $('#pfileShowDiv').hide();
        $('#listenerShowDiv').hide();
        $('#tnsnamesShowDiv').hide();
        $('#sqlnetShowDiv').hide();

        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.SQLSERVER:
                if (!validateSqlServerStep3(pathType)) {
                    return false;
                }
                break;
            case CONF.DB_TYPE.ORACLE:  // Oracle恢复
                if (!validateOracleStep3(pathType)) {
                    return false;
                }
                break;
            case CONF.DB_TYPE.MYSQL:
            case CONF.DB_TYPE.MARIA:
                if (!validateMySQLStep3(pathType)) {
                    return false;
                }
                break;
            case CONF.DB_TYPE.DM:
                if (!validateDMStep3(pathType)) {
                    return false;
                }
                break;
            case CONF.DB_TYPE.POSTGRE:
            case CONF.DB_TYPE.ANTDB:
            case CONF.DB_TYPE.KINGBASE:
            case CONF.DB_TYPE.UXDB:
            case CONF.DB_TYPE.HIGHGO:
            case CONF.DB_TYPE.OPENGAUSS:
            case CONF.DB_TYPE.VASTBASE:
                if (!validatePGStep3(pathType)) {
                    return false;
                }
                break;
            case CONF.DB_TYPE.MONGODB:
                if (!validateMongoDBStep3(pathType)) {
                    return false;
                }
                break;
            case CONF.DB_TYPE.TIDB:
                if (!validateTiDBStep3(pathType)) {
                    return false;
                }
                break;
            case CONF.DB_TYPE.SAPHANA:
                if (!validateSAPHANAStep3(pathType)) {
                    return false;
                }
                break;
        }

        // 限速策略
        if (!validateStep3SpeedLimit()) {
            return false;
        }
        // 传输策略
        if (!validateStep3Transfer()) {
            return false;
        }
        // 安全策略
        let integrityCheck = $('#integrityCheck').getCompleteStrategyCovery();
        data.safe_config_strategy = safeData('', '', integrityCheck);
        // 高级配置
        if (!validateStep3Advanced()) {
            return false;
        }

        // 提示SAP HANA恢复
        showInitLogAreaDialog()
            .then(showSAPHANARecoveryDialog)
            .then(showTimerRecoveryNewestDialog)
            .then(showSqlServerRecoverySystemDataDialog)
            .then(() => {
                $('a[href="#tab4"]').tab('show'); // 手动切换步骤页面
                showTitleCallback();
            });

        showStep3();
        return false;
    }

    /**
     * 验证步骤3的时间策略
     */
    const validateStep3TimeStrategy = () => {
        let commonStrategy = $('#backupStrategyDiv').getBackupStrategy();
        if (commonStrategy === false) {
            return false;
        }
        let timeInfo = commonStrategy.time.timeInfo;
        data.timeInfo.type = timeInfo.type_value;
        data.timeInfo.strategy = {};
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            $('.timeStrategyShowDiv').show();
        } else {
            $('.timeStrategyShowDiv').show();
        }
        if (TIME_STRATEGY_RECOVERY_TYPE_ENUM.IMMEDIATELY === data.timeInfo.type) {
            //立即恢复
        } else if (TIME_STRATEGY_RECOVERY_TYPE_ENUM.ONCE_TIME === data.timeInfo.type) {
            //一次性恢复
            data.timeInfo.strategy.startTime = timeInfo.start_time;
            data.timeInfo.strategy.type = timeInfo.type_value;
        } else {
            data.timeInfo.strategy = timeInfo.strategy;
        }
        $('.timeStrategyShow').html(timeInfo.des);
        return true;
    };

    /**
     * 验证步骤3的日志回滚
     */
    const validateStep3Rollback = () => {
        if (
            recoverySource.db_type === CONF.DB_TYPE.SQLSERVER ||
            recoverySource.db_type === CONF.DB_TYPE.SAPHANA ||
            recoverySource.db_type === CONF.DB_TYPE.ORACLE ||
            recoverySource.db_type === CONF.DB_TYPE.TIDB
        ) {  // SQLServer、SAP HANA、Oracle、TiDB日志回滚单独设置
            return '';
        }
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {  // 恢复最新点不需要回滚日志
            return '';
        }
        if (!judgeShowRollbackTime()) {
            return '';
        }
        var logtimeCheck = $('#logtimecheck').get(0).checked;
        let showStr = $('.logtimelabel').text() + ": " + getSwitchDes(logtimeCheck);
        //回滚时间开启
        data.pointInfo.points[0].is_rollback = !!logtimeCheck;
        if (logtimeCheck) {
            //标准时间格式正则表达式
            var reg = /^(?:19|20)[0-9][0-9]-(?:(0[1-9])|(1[0-2]))-(?:([0-2][1-9])|([1-3][0-1])) (?:([0-2][0-3])|([0-1][0-9])):[0-5][0-9]:[0-5][0-9]$/;
            data.recoverInfo.logrolltime = $('#logtime').val();
            data.pointInfo.points[0].rollback_time = data.recoverInfo.logrolltime;
            if (data.recoverInfo.logrolltime < startTimepoint || data.recoverInfo.logrolltime > endTimepoint || !reg.test(data.recoverInfo.logrolltime)) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_ROLL_TIME_ERROR, LANG.UI_DB_RECOVERY_ROLL_TIME_ERROR_TIPS);
                return false;
            }

            var logtimeDes = LANG.UI_DB_RECOVERY_LOG_ROLL_TIME;
            if (recoverySource.db_type === CONF.DB_TYPE.ORACLE || recoverySource.db_type === CONF.DB_TYPE.DM) {
                logtimeDes = LANG.UI_DB_RECOVERY_ARCHIVE_LOG_ROLL_TIME;
            }
            showStr += ", " + logtimeDes + ": " + data.recoverInfo.logrolltime;
        }
        return showStr;
    };

    /**
     * 验证步骤3的高级配置
     */
    const validateStep3Advanced = () => {
        // 重试策略
        data.retry_strategy = $('#tab_advanced_retry').retryStrategy({}, 'value');
        if (!data.retry_strategy) {
            return false;
        }
        // 数据库配置
        let advancedDatabaseConfigDes = ``;
        // 过载保护
        let advancedOverloadDes = ``;
        // 忽略节点资源限制
        data.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
        advancedOverloadDes += $('.ignoreResourceLimitLabel').html().trim() + ': ' + getSwitchDes(data.ignore_resource_limiting_flag) + '<br>';
        // 客户端并行数量
        advancedDatabaseConfigDes += $('.parallelNumLabel').html().trim() + ': ' + data.recoverInfo.max_object_transport_parallel_nums + '<br>';
        $('.advancedOverloadShow').html(advancedOverloadDes);
        $('.advancedDatabaseConfigShow').html(advancedDatabaseConfigDes);
        return true;
    };

    /**
     * 验证步骤3的传输策略
     */
    const validateStep3Transfer = () => {
        data.highInfo.transfer.encrypt = $('#transferEncryptedCheck').get(0).checked;  // 加密传输
        // 传输加密算法
        data.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
        data.highInfo.transfer.network = '';
        data.highInfo.transfer.network_pool_uuid = '';
        if (networkFlag) {  // 传输网络
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
            if (false === networkNode) {
                return false;
            }
            if (networkNode.transfer_network_mode === $.fn.transferNetwork('transfer_network_mode').CUSTOM_SELECT) {
                if (networkNode.eventtype === 'network') {
                    data.highInfo.transfer.network = networkNode.network_uuid;
                } else {
                    data.highInfo.transfer.network_pool_uuid = networkNode.network_pool_uuid;
                }
            }
        }
        // 传输线程/通道数
        if (CONF.BD_STORAGE_TYPE.TAPE !== storage_type && drillStorageType !== CONF.BD_STORAGE_TYPE.TAPE) {
            if (recoverySource.db_type === CONF.DB_TYPE.MONGODB) {
                data.highInfo.threadnum = $('#mongodbRecoveryThreadNum').val();
            } else {
                data.highInfo.threadnum = $('#recoveryThreadNum').val();
                if (recoverySource.db_type !== CONF.DB_TYPE.ORACLE) {  //非Oracle仅支持单线程
                    data.highInfo.threadnum = 1;
                } else {
                    if (recoveryMethod.path_type === PATH_TYPE_ENUM.EXPORT) {  //导出恢复为1
                        data.highInfo.threadnum = 1;
                    }
                }
            }
        } else {
            data.highInfo.threadnum = 1;
        }
        return true;
    };

    /**
     * 验证步骤3的数据加密密码
     */
    const validateStep3Encrypt = () => {
        if (
            recoverySource.db_type === CONF.DB_TYPE.SQLSERVER ||
            recoverySource.db_type === CONF.DB_TYPE.SAPHANA
        ) { // SQL Server和SAP HANA数据加密单独验证
            return true;
        }
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {  // 恢复最新点不需要加密密码
            return true;
        }
        //数据加密密码验证
        let firstNode = Object.values(recoverySource.time_point_list)[0];
        let storeEncryptPassword = recoverySource.timepoint_encrypt_password_map[firstNode.timepoint_uuid];
        data.recoverInfo.encrypt_password = btoa(storeEncryptPassword);
        data.recoverInfo.encrypt_password_clear = storeEncryptPassword;
        data.pointInfo.points[0].encrypt_password = btoa(storeEncryptPassword);
        data.pointInfo.points[0].encrypt_password_clear = storeEncryptPassword;
        //数据验证密码
        if (recoverySource.db_type === CONF.DB_TYPE.ORACLE) {
            let oracleTimepoint = oracleTimepointList.select_timepoint;
            let storeEncryptPassword = $('#encryptPassword').val().trim();
            if (!isNotLatinCode(storeEncryptPassword)) {
                return false;
            }
            data.recoverInfo.encrypt_password = btoa(storeEncryptPassword);
            data.recoverInfo.encrypt_password_clear = storeEncryptPassword;
            data.pointInfo.points[0].encrypt_password = btoa(storeEncryptPassword);
            data.pointInfo.points[0].encrypt_password_clear = storeEncryptPassword;
            switch (recoveryMethod.path_type) {
                case PATH_TYPE_ENUM.FULL:
                case PATH_TYPE_ENUM.INCOMPLETE:
                    // 时间点恢复方式
                    for (const chainInfo of oracleTimepointList.recovery_timepoint_chain_data) {
                        if (chainInfo.full_timepoint_info.is_encrypted && !chainInfo.full_timepoint_info.config.password_auto_flag) {
                            if (!validEncryptPassword($(`#oracle_encrypt_password_${chainInfo.full_timepoint_uuid}`).val(), chainInfo.full_timepoint_uuid)) {
                                // let treeNode = oracleRecoveryChainTree.getNodeByParam('timepoint_uuid', chainInfo.full_timepoint_uuid);
                                // oracleRecoveryChainTree.expandNode(treeNode, true, false, true)
                                UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_ORACLE_TIMEPOINT_ENCRYPT_PASSWORD_ERROR.replace('%S', chainInfo.full_timepoint_info.time_point));
                                return false;
                            }
                        }
                    }
                    break;
                case PATH_TYPE_ENUM.EXPORT:
                    let checkNodes = oracleExportTree.getCheckedNodes(true);
                    for (const checkNode of checkNodes) {
                        if (checkNode.eventtype === 'timepoint') {
                            if (checkNode.timepoint.is_encrypted && !checkNode.timepoint.config.password_auto_flag) {
                                if (!validEncryptPassword($(`#oracle_export_encrypt_password_${checkNode.timepoint.time_point_uuid}`).val(), checkNode.timepoint.time_point_uuid)) {
                                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_ORACLE_TIMEPOINT_ENCRYPT_PASSWORD_ERROR.replace('%S', checkNode.timepoint.time_point));
                                    return false;
                                }
                            }
                        }
                    }
                    break;
            }
        } else if (recoverySource.db_type === CONF.DB_TYPE.TIDB) {
            let storeEncryptPassword = $('#encryptPassword').val().trim();
            if (!isNotLatinCode(storeEncryptPassword)) {
                return false;
            }
            data.recoverInfo.encrypt_password = btoa(storeEncryptPassword);
            data.recoverInfo.encrypt_password_clear = storeEncryptPassword;
            data.pointInfo.points[0].encrypt_password = btoa(storeEncryptPassword);
            data.pointInfo.points[0].encrypt_password_clear = storeEncryptPassword;
            let tidbTimepoint = tidbTimepointList.select_timepoint;
            if (tidbTimepoint.is_encrypted && !tidbTimepoint.config.password_auto_flag) {
                if (!validEncryptPassword(data.recoverInfo.encrypt_password_clear, tidbTimepoint.time_point_uuid)) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_DATA_ENCRYPT_PASSWORD_ERROR);
                    return false;
                }
            }
        }
        return true;
    };

    /**
     * 验证步骤3的限速策略
     */
    const validateStep3SpeedLimit = () => {
        data.speedLimit = getSpeedStrategyInfo();
        //限速策略
        var speedlimitshow = $('.speedlimitshow');
        var speedLimitsStr = '';
        if (data.speedLimit.speedInfo.length != 0) {
            speedLimitsStr = '';
            for (let i = 0; i < data.speedLimit.speedInfo.length; i++) {
                speedLimitsStr += data.speedLimit.speedInfo[i].des + '<br>';
            }
        }

        if (speedLimitsStr == '') {
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
        speedlimitshow.html(speedLimitsStr);
        return true;
    };

    /**
     * 获取覆盖恢复的显示文案
     * @returns {string|*|string}
     */
    const getCoverShowStr = () => {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            return LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY + '<br>';
        } else {
            switch (recoverySource.db_type) {
                case CONF.DB_TYPE.SQLSERVER:
                case CONF.DB_TYPE.SAPHANA:
                    return LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_ORIGINAL_RECOVERY + '<br>';
                default:
                    return LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_ORIGINAL_INSTANCE_RECOVERY + '<br>';
            }
        }
    };

    const judgeSysPassword = (password, title = null, allow_empty = true) => {
        /**
         * 1. SYS密码允许为空
         * 2. 长度在8~30
         * 3. 至少包含1个数字
         * 4. 至少包含1个字母
         * 5. 至少包含1个特殊字符
         */
        if (title === null) {
            title = LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO;
        }
        if (!password) {  // SYS密码允许为空
            if (allow_empty) {
                return true;
            } else {
                UIToastr.showWarning(title, LANG.UI_DB_RECOVERY_SYS_PASSWORD_NOT_EMPTY);
                return false;
            }
        }
        if (!isNotLatinCode(password)) {
            return false;
        }
        let reg1 = /[0-9]/;
        let reg2 = /[A-z]/;
        let reg3 = /[.,;:'"?!()\[\]{}<>“”‘’\-–—\/\\@#$%^&*_+~|]/;
        if (
            (password.length < 8 || password.length > 30) ||
            !reg1.test(password) ||
            !reg2.test(password) ||
            !reg3.test(password)
        ) {
            UIToastr.showWarning(title, LANG.UI_DB_RECOVERY_SYS_PASSWORD_ERROR_FORMAT);
            return false;
        }
        return true;
    };

    /**
     * 验证自定义配置文件
     * @param firstNode
     * @return {Boolean}
     */
    const validateCustomConfigFile = (oracleTimepoint) => {
        // 验证配置文件
        if (data.detail.pfile_info.recovery_flag) {
            // pfile
            if (!oracleRecoveryContent.pfile.trim()) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_SPFILE_NOT_EMPTY);
                return false;
            }
            data.detail.pfile_info.file_content = oracleRecoveryContent.pfile;
            data.detail.recovery_content.config_file_list.push({
                name: LANG.UI_DB_RECOVERY_PFILE,
                dir_path: '',
            });
        }
        if (data.detail.listener_info.recovery_flag) {
            // listener
            if (!oracleRecoveryContent.listener.trim()) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_LISTENER_NOT_EMPTY);
                return false;
            }
            data.detail.listener_info.file_content = oracleRecoveryContent.listener;
            if (!oracleRecoveryContent.listener_path.trim()) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_LISTENER_PATH_NOT_EMPTY);
                return false;
            }
            data.detail.listener_info.file_path = oracleRecoveryContent.listener_path;
            data.detail.recovery_content.config_file_list.push({
                name: LANG.UI_DB_RECOVERY_LISTENER,
                dir_path: oracleRecoveryContent.listener_path,
            });
        }
        if (data.detail.tnsnames_info.recovery_flag) {
            // tnsnames
            if (!oracleRecoveryContent.tnsnames.trim()) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_TNSNAMES_NOT_EMPTY);
                return false;
            }
            data.detail.tnsnames_info.file_content = oracleRecoveryContent.tnsnames;
            if (!oracleRecoveryContent.tnsnames_path.trim()) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_TNSNAMES_PATH_NOT_EMPTY);
                return false;
            }
            data.detail.tnsnames_info.file_path = oracleRecoveryContent.tnsnames_path;
            data.detail.recovery_content.config_file_list.push({
                name: LANG.UI_DB_RECOVERY_TNSNAMES,
                dir_path: oracleRecoveryContent.tnsnames_path,
            });
        }
        if (data.detail.sqlnet_info.recovery_flag) {
            if (!oracleRecoveryContent.sqlnet.trim()) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_SQLNET_NOT_EMPTY);
                return false;
            }
            data.detail.sqlnet_info.file_content = oracleRecoveryContent.sqlnet;
            if (!oracleRecoveryContent.sqlnet_path.trim()) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_SQLNET_PATH_NOT_EMPTY);
                return false;
            }
            data.detail.sqlnet_info.file_path = oracleRecoveryContent.sqlnet_path;
            data.detail.recovery_content.config_file_list.push({
                name: LANG.UI_DB_RECOVERY_SQLNET,
                dir_path: oracleRecoveryContent.sqlnet_path,
            });
        }
        if (!recoverySource.cluster_flag && recoveryTarget.cluster_flag) {  // 单机恢复到RAC屏蔽恢复密码文件
            // pass
        } else {
            if (data.detail.password_info.recovery_flag) {
                // password
                if (oracleRecoveryType === 2 && parseInt(oracleTimepoint.db_config.rac_flag) !== 0) {
                    if (!judgeSysPassword(oracleRecoveryContent.password)) {
                        return false;
                    }
                    data.recoverInfo.password = oracleRecoveryContent.password;
                }
                data.detail.password_info.file_path = oracleRecoveryContent.password_path;
                data.detail.recovery_content.config_file_list.push({
                    name: LANG.UI_DB_RECOVERY_PASSWORD,
                    dir_path: oracleRecoveryContent.password_path,
                });
            }
        }
        return true;
    };

    /**
     * 显示步骤四的Oracle恢复内容
     * @param pathType
     * @return Boolean
     */
    const showStep4OracleRecoveryContent = (pathType) => {
        if (pathType !== PATH_TYPE_ENUM.FULL && pathType !== PATH_TYPE_ENUM.INCOMPLETE) {
            return true;
        }
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            return true;
        }
        let oracleTimepoint = oracleTimepointList.select_timepoint;
        data.detail = {
            pfile_info: {
                file_path: '',
                file_content: '',
                recovery_flag: oracleRecoveryContent.pfile_flag,
            },
            listener_info: {
                file_path: '',
                file_content: '',
                recovery_flag: oracleRecoveryContent.listener_flag,
            },
            tnsnames_info: {
                file_path: '',
                file_content: '',
                recovery_flag: oracleRecoveryContent.tnsnames_flag,
            },
            sqlnet_info: {
                file_path: '',
                file_content: '',
                recovery_flag: oracleRecoveryContent.sqlnet_flag,
            },
            password_info: {
                file_path: '',
                file_content: '',
                recovery_flag: oracleRecoveryContent.password_flag,
            },
            recovery_content: {
                instance_name: oracleTimepoint.instance_name,
                tablespace_list: oracleTimepoint.db_config['backuped_tablespace_list'].map(tablespaceInfo => {
                    let data_file_list = [];
                    if (Array.isArray(tablespaceInfo['data_file_list'])) {
                        data_file_list = tablespaceInfo.data_file_list.map(v => v.file_name);
                    }
                    return {
                        table_space_name: tablespaceInfo.table_space_name,
                        data_file_list,
                    };
                }),
                config_file_list: [],
            },
            create_instance_pfile_info: '',
            table_space_list: [],
            data_file_list: [],
        };
        if (recoveryTarget.create_new_instance_flag) {
            data.detail.create_instance_pfile_info = recoveryMethod.create_new_instance_config.spfile_content.join("\n") + "\n";
        }
        // 完全恢复可以勾选表空间
        if (pathType === PATH_TYPE_ENUM.FULL) {
            /**
             * 1. 勾选了所有的表空间和数据文件，table_space_list和data_file_list为空数组
             * 2. 只勾选了部分表空间，勾选了全部数据文件，table_space_list为勾选的表空间，data_file_list为空数组
             * 3. 只勾选了部分表空间，部分勾选的表空间只勾选了部分数据文件，部分勾选的表空间勾选了全部数据文件，table_space_list为勾选的表空间，data_file_list为勾选的数据文件
             * 4. 只勾选了部分表空间，勾选的全部表空间只勾选了部分数据文件，table_space_list为为空数组，data_file_list为勾选的数据文件

             */
            let checkedTablespaceShowList = [];  // WEB显示的表空间
            let checkedTablespaceList = [];      // 传给后台的表空间
            let checkedDatafileList = [];        // 传给后台的数据文件
            let checkedTablespaceNodes = oracleRecoveryContent.tree.getCheckedNodes(true);
            let tablespaceCount = oracleTimepoint.db_config['backuped_tablespace_list'].length;
            let currentCheckedTablespaceCount = 0;
            let allChecked = true;
            for (const checkedTablespaceNode of checkedTablespaceNodes) {
                if (checkedTablespaceNode.eventtype === 'table_space') {
                    currentCheckedTablespaceCount++;
                    let datafileNodes = checkedTablespaceNode.children;
                    let checkedTablespaceShow = {
                        table_space_name: checkedTablespaceNode.tablespace,
                        data_file_list: [],
                    };
                    if (!Array.isArray(datafileNodes)) {
                        checkedTablespaceList.push(checkedTablespaceNode.tablespace);
                        continue;
                    }
                    let checkedAllDatafile = true;
                    for (const datafileNode of datafileNodes) {
                        if (!datafileNode.checked) {
                            checkedAllDatafile = false;
                            allChecked = false;
                            continue;
                        }
                        checkedTablespaceShow.data_file_list.push(datafileNode.file_name);

                    }
                    if (checkedAllDatafile) {  // 勾选了全部数据文件，因此只传表空间
                        checkedTablespaceList.push(checkedTablespaceNode.tablespace);
                    } else {  // 只勾选了部分数据文件，因此传数据文件
                        for (const datafileNode of datafileNodes) {
                            if (datafileNode.checked) {
                                checkedDatafileList.push(datafileNode.file_id);
                            }
                        }
                    }
                    checkedTablespaceShowList.push(checkedTablespaceShow);
                }
            }
            if (!checkedTablespaceList.length && !checkedDatafileList.length) {
                UIToastr.showWarning(LANG.UI_RECOVERY_DB_PROTECT_CREATE_TITLE, LANG.UI_DB_RECOVERY_ORACLE_RECOVERY_TALESPACE_DADAFILE_EMPTY);
                return false;
            }
            if (!allChecked || tablespaceCount > currentCheckedTablespaceCount) {
                data.detail.table_space_list = checkedTablespaceList;
                data.detail.data_file_list = checkedDatafileList;
            }
            data.detail.recovery_content.tablespace_list = checkedTablespaceShowList;
        }
        if (!validateCustomConfigFile(oracleTimepoint)) {
            return false;
        }

        $('#oracleRecoveryContentShowDiv').show();
        // 恢复内容
        let nodes = [];
        let baseNodes = getOracleBaseNodes(oracleTimepoint);
        let configFileNodes = getOracleConfigFileNode(oracleTimepoint);
        let tablespaceNodes = getOracleTablespaceNodes(oracleTimepoint);
        if (pathType === PATH_TYPE_ENUM.FULL) {
            tablespaceNodes = [];
            for (const tablespaceInfo of data.detail.recovery_content.tablespace_list) {
                tablespaceNodes.push({
                    id: oracleTimepoint.time_point_uuid + '_oracle_database_instance_' + tablespaceInfo.table_space_name,
                    pId: oracleTimepoint.time_point_uuid + '_oracle_database_instance',
                    name: tablespaceInfo.table_space_name,
                    title: tablespaceInfo.table_space_name,
                    tablespace: tablespaceInfo.table_space_name,
                    isParent: false,
                    eventtype: 'table_space',
                    icon: './img/platform/storage.png',
                    checked: true,
                    chkDisabled: false,
                });
                for (const datafileName of tablespaceInfo.data_file_list) {
                    nodes.push({
                        id: oracleTimepoint.time_point_uuid + '_oracle_database_instance_' + tablespaceInfo.table_space_name + '_data_file_' + datafileName,
                        pId: oracleTimepoint.time_point_uuid + '_oracle_database_instance_' + tablespaceInfo.table_space_name,
                        name: datafileName,
                        title: datafileName,
                        file_name: datafileName,
                        isParent: false,
                        eventtype: 'data_file',
                        icon: './img/fs/wenjian.png',
                        checked: true,
                        chkDisabled: false,
                    });
                }
            }
        }
        for (const baseNode of baseNodes) {
            baseNode.open = true;
            baseNode.nocheck = true;
            nodes.push(baseNode);
        }
        for (const tablespaceNode of tablespaceNodes) {
            tablespaceNode.nocheck = true;
            nodes.push(tablespaceNode);
        }
        for (const configFileNode of configFileNodes) {
            configFileNode.open = true;
            configFileNode.nocheck = true;
            let checkFlag = true;
            switch (configFileNode.eventtype) {
                case 'pfile':
                    if (data.detail.pfile_info.recovery_flag) {
                        configFileNode.name = configFileNode.p_name;
                        $('#pfileShowDiv').show();
                        $('.pfileShow').html(convertSpaceCharToSign(oracleRecoveryContent.pfile));
                    } else {
                        checkFlag = false;
                    }
                    break;
                case 'listener':
                    if (data.detail.listener_info.recovery_flag) {
                        $('#listenerShowDiv').show();
                        $('.listenerShow').html(convertSpaceCharToSign(oracleRecoveryContent.listener));
                        configFileNode.name = configFileNode.p_name + '(' + oracleRecoveryContent.listener_path + ')';
                    } else {
                        checkFlag = false;
                    }
                    break;
                case 'tnsnames':
                    if (data.detail.tnsnames_info.recovery_flag) {
                        $('#tnsnamesShowDiv').show();
                        $('.tnsnamesShow').html(convertSpaceCharToSign(oracleRecoveryContent.tnsnames));
                        configFileNode.name = configFileNode.p_name + '(' + oracleRecoveryContent.tnsnames_path + ')';
                    } else {
                        checkFlag = false;
                    }
                    break;
                case 'sqlnet':
                    if (data.detail.sqlnet_info.recovery_flag) {
                        $('#sqlnetShowDiv').show();
                        $('.sqlnetShow').html(convertSpaceCharToSign(oracleRecoveryContent.sqlnet));
                        configFileNode.name = configFileNode.p_name + '(' + oracleRecoveryContent.sqlnet_path + ')';
                    } else {
                        checkFlag = false;
                    }
                    break;
                case 'password':
                    if (data.detail.password_info.recovery_flag) {
                        if (oracleRecoveryType === 2 && parseInt(oracleTimepoint.db_config.rac_flag) !== 0) {  // 恢复到空实例
                            configFileNode.name = configFileNode.p_name;
                        } else {
                            configFileNode.name = configFileNode.p_name + '(' + oracleRecoveryContent.password_path + ')';
                        }
                    } else {
                        checkFlag = false;
                    }
                    break;
            }
            if (checkFlag) {
                nodes.push(configFileNode);
            }
        }
        $.fn.zTree.init($('#oracleRecoveryContentShow'), {
            data: {
                simpleData: {
                    enable: true,
                    idKey: 'id',
                    pIdKey: 'pId',
                    rootPId: 0,
                },
                key: {
                    title: 'title',
                },
            },
        }, nodes);
        return true;
    };

    /**
     * 显示sql server恢复系统数据库的弹窗
     * @returns {Promise<unknown>}
     */
    const showSqlServerRecoverySystemDataDialog = () => {
        return new Promise((resolve, reject) => {
            if (recoverySource.db_type !== CONF.DB_TYPE.SQLSERVER) {  // 非SQL Server数据库
                resolve();
                return;
            }
            if (recoverySource.recovery_source_type !== RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {  // 非恢复任务
                resolve();
                return;
            }
            let pathType = parseInt($('#pathType').val());
            if (pathType !== PATH_TYPE_ENUM.COVER) {  // 非覆盖恢复
                resolve();
                return;
            }
            let systemDatabaseList = [
                'master',
                'model',
                'msdb',
                'tempdb',
            ];
            let recoverySystemDatabaseSet = new Set();
            for (const nodeKey in recoveryMethod.db_config) {
                let dbConfig = recoveryMethod.db_config[nodeKey];
                if (systemDatabaseList.includes(dbConfig.name.toLowerCase())) {
                    recoverySystemDatabaseSet.add(dbConfig.name);
                }
            }
            if (!recoverySystemDatabaseSet.size) {
                resolve();
                return;
            }
            let tips = `<span style="color: red">${Array.from(recoverySystemDatabaseSet).join('、')}</span>`;
            bootbox.dialog({
                title: LANG.UI_RECOVERY_DB_PROTECT_CREATE_TITLE,
                message: LANG.UI_DB_RECOVERY_SQLSERVER_COVER_SYSTEM_DATABASE_TIPS.replace('%s', tips),
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
     * 显示定时恢复最新点的提示信息，输入密码
     * @returns {Promise<unknown>}
     */
    const showTimerRecoveryNewestDialog = function () {
        return new Promise((resolve, reject) => {
            if (recoverySource.recovery_source_type !== RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                resolve();
                return;
            }
            if (
                recoveryMethod.path_type !== PATH_TYPE_ENUM.CREATE &&
                recoveryMethod.path_type !== PATH_TYPE_ENUM.SPECIFY_FOLDER &&
                recoveryMethod.path_type !== PATH_TYPE_ENUM.INCOMPLETE
            ) {
                resolve();
                return;
            }
            let title = '';
            switch (recoverySource.db_type) {
                case CONF.DB_TYPE.SQLSERVER:
                    title = LANG.UI_DB_RECOVERY_SQLSERVER_RECOVERY_NEWEST_TIPS2 + ', ' + LANG.UI_DB_RECOVERY_INPUT_PASSWORD_CONFIRM;
                    break;
                case CONF.DB_TYPE.ORACLE:
                    title = LANG.UI_DB_RECOVERY_ORACLE_RECOVERY_NEWEST_TIPS1 + ', ' + LANG.UI_DB_RECOVERY_INPUT_PASSWORD_CONFIRM;
                    break;
                case CONF.DB_TYPE.DM:
                    title = LANG.UI_DB_RECOVERY_DM_RECOVERY_NEWEST_TIPS1 + ', ' + LANG.UI_DB_RECOVERY_INPUT_PASSWORD_CONFIRM;
                    break;
                case CONF.DB_TYPE.POSTGRE:
                case CONF.DB_TYPE.KINGBASE:
                case CONF.DB_TYPE.UXDB:
                case CONF.DB_TYPE.HIGHGO:
                case CONF.DB_TYPE.ANTDB:
                case CONF.DB_TYPE.OPENGAUSS:
                case CONF.DB_TYPE.VASTBASE:
                    title = LANG.UI_DB_RECOVERY_PG_RECOVERY_NEWEST_TIPS1 + ', ' + LANG.UI_DB_RECOVERY_INPUT_PASSWORD_CONFIRM;
                    break;
                case CONF.DB_TYPE.SAPHANA:
                    title = LANG.UI_DB_RECOVERY_SAPHANA_RECOVERY_NEWEST_TIPS1 + ',' + LANG.UI_DB_RECOVERY_INPUT_PASSWORD_CONFIRM;
                    break;
            }
            bootbox.dialog({
                title,
                message: `<div class="bootbox-input-wrapper" style="position: relative;margin-bottom: 15px;">
                            <input class="bootbox-input bootbox-input-password" type="password" autocomplete="off"
                                style="border: 1px solid #E6E6E6;border-radius: 2px !important;height: 34px;width:100%;background-color: #FFFFFF;padding: 6px 12px"
                                oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\\s+/g,'')" />
                            <button type="button" class="btn btn-link show-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                                <i class="viconfont vicon-a-lujing8232"></i>
                            </button>
                        </div>`,
                buttons: {
                    cancel: {
                        label: LANG.UI_PUBLIC_CANCEL,
                        className: 'btn btn-default',
                        callback: function () {
                            return;
                        }
                    },
                    confirm: {
                        label: LANG.UI_PUBLIC_CONFIRM,
                        className: 'btn btn-primary',
                        callback: debounce(function () {
                            let result = $('.bootbox-input-password').val();
                            if (!result) {
                                return;
                            }
                            if (hex_md5(result) === _UserPassword) {
                                $(this).modal('hide');
                                resolve();
                            } else {
                                $('.bootbox-input').css('border-color', "#a94442");
                                if (!$('.bootbox-input').closest('.bootbox-form').find('.password-error').length) {
                                    let des = '<p class="password-error" style="margin-top:5px;color:#a94442;position:absolute">' + LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS + '</p>';
                                    $('.bootbox-input').after(des);
                                }
                                return false;
                            }
                        }, 300, false),
                    }
                }
            }).on('shown.bs.modal', function () {
                // 获取输入框和按钮
                let $input = $(this).find('.bootbox-input-password');
                let $btn = $(this).find('.show-password-btn');

                // 添加点击事件监听器
                $btn.on('click', function () {
                    let inputType = $input.attr('type');
                    if (inputType === 'password') {
                        $input.attr('type', 'text');
                        $btn.find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
                    } else {
                        $input.attr('type', 'password');
                        $btn.find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
                    }
                });
            });
        });
    };

    /**
     * 显示SAP HANA初始化日志弹窗
     * @returns {Promise<unknown>}
     */
    var showInitLogAreaDialog = function () {
        return new Promise((resolve, reject) => {
            if (recoverySource.db_type !== CONF.DB_TYPE.SAPHANA) {
                resolve();
                return;
            }
            let showFlag = false;
            for (const nodeKey in recoveryMethod.db_config) {  // 初始化状态
                if (recoveryMethod.db_config[nodeKey].initialize_log_area) {
                    showFlag = true;
                    break;
                }
            }
            if (showFlag) {
                bootbox.dialog({
                    title: LANG.UI_DB_RECOVERY_SAP_HANA_INIT_LOG_AREA,
                    message: LANG.UI_DB_RECOVERY_SAP_HANA_INIT_LOG_AREA_TIPS2,
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
            } else {
                resolve();
            }
        });
    }

    /**
     * 显示SAP HANA的恢复提示
     * @returns {Promise<unknown>}
     */
    var showSAPHANARecoveryDialog = function () {
        return new Promise((resolve, reject) => {
            if (recoverySource.db_type !== CONF.DB_TYPE.SAPHANA) {
                resolve();
                return;
            }
            bootbox.dialog({
                title: LANG.UI_RECOVERY_DB_PROTECT_CREATE_TITLE,
                message: LANG.UI_DB_RECOVERY_SAPHANA_TIPS,
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
    }

    /**
     * 显示原数据库覆盖恢复提示
     * @returns {Promise<unknown>}
     */
    var showCoverRecoveryDialog = function () {
        return new Promise((resolve, reject) => {
            if (recoverySource.db_type === CONF.DB_TYPE.TIDB) {
                resolve();
                // TiDB只有覆盖恢复，没有显示恢复方式，因此不显示弹窗提示
                return;
            }
            if (recoveryMethod.path_type !== PATH_TYPE_ENUM.COVER) {
                resolve();
                return;
            }
            if (recoverySource.db_type === CONF.DB_TYPE.SAPHANA) {  // SAP HANA不显示覆盖恢复提示框
                resolve();
                return;
            }
            let title = LANG.UI_RECOVERY_DB_PROTECT_CREATE_TITLE;
            let message = LANG.UI_RECOVERY_DB_PROTECT_COVER_RECOVERY_TIPS;
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                title = LANG.UI_RECOVERY_DB_PROTECT_CREATE_DRILL_TITLE;
                switch (recoverySource.db_type) {
                    case CONF.DB_TYPE.SQLSERVER:
                        message = LANG.UI_RECOVERY_DB_PROTECT_COVER_RECOVERY_TIPS2;
                        break;
                    default:
                        message = LANG.UI_RECOVERY_DB_PROTECT_COVER_RECOVERY_TIPS3;
                        break;
                }
            }
            bootbox.dialog({
                title,
                message,
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
    }

    /**
     * 显示还原归档日志提示
     * @returns {Promise<unknown>}
     */
    var showRestoreArchivelogDialog = function () {
        return new Promise((resolve, reject) => {
            if (recoveryMethod.path_type !== PATH_TYPE_ENUM.RESTORE_ARCHIVELOG) {
                resolve();
                return;
            }
            bootbox.dialog({
                title: LANG.UI_RECOVERY_DB_PROTECT_CREATE_TITLE,
                message: LANG.UI_RECOVERY_DB_PROTECT_RESTORE_ARCHIVELOG_TIPS,
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
     * 加载SQL server集群活动客户端
     */
    const loadSqlServerClusterActiveAgent = () => {
        return new Promise((resolve, reject) => {
            if (recoverySource.db_type !== CONF.DB_TYPE.SQLSERVER) {
                resolve();
                return;
            }
            if (!recoveryTarget.cluster_flag) { // 集群才加载
                resolve();
                return;
            }
            Metronic.blockUI({target: '#dbrecovercontent', animate: true});
            pAjaxRequest({cluster_uuid: recoveryTarget.cluster_uuid}, `/api/v1/db/sqlserver/active_agent`, 'GET', res => {
                Metronic.unblockUI('#dbrecovercontent');
                if (!res.success) {
                    UIToastr.showError(LANG.UI_RECOVERY_DB_PROTECT_CREATE_TITLE, LANG.UI_DB_RECOVERY_SQLSERVER_LOAD_CLUSTER_ACTIVE_NODE_ERROR);
                    resolve();
                    return;
                }
                recoveryTarget.agent_uuid = res.data.active_agent_uuid;
                resolve();
            });
        });
    };

    /**
     * 显示异机恢复的提示
     * @returns {Promise<unknown>}
     */
    var showDiffTargetRecoveryDialog = function () {
        return new Promise((resolve, reject) => {
            if (!judgeShowDiffRecoveryDialog()) {
                resolve();
                return;
            }
            let message = LANG.UI_RECOVERY_DB_PROTECT_DIFF_TARGET_SAME_METHOD;
            if (recoverySource.db_type === CONF.DB_TYPE.MONGODB) {
                message = LANG.UI_RECOVERY_DB_PROTECT_DIFF_TARGET_SAME_METHOD2;
            }
            bootbox.dialog({
                title: LANG.UI_RECOVERY_DB_PROTECT_CREATE_TITLE,
                message,
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
    }

    /**
     * 验证数据加密密码是否正确
     * @param password
     * @param timePointUuid
     * @returns {boolean}
     */
    var validEncryptPassword = function (password, timePointUuid) {
        let checkFlag = false;
        let reqData = {
            encrypt_password: btoa(password),
            time_point_uuid: timePointUuid,
        };
        pAjaxRequest(reqData, `/api/v1/db/jobs/backup/password`, 'POST', res => {
            if (res.success) {
                checkFlag = true;
            }
        }, false);
        return checkFlag;
    }

    /**
     * 判断是否显示异机源数据库覆盖恢复的提示弹窗
     * @return {boolean}
     */
    var judgeShowDiffRecoveryDialog = function () {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
            // 恢复最新点点不需要异机提示
            return false;
        }
        switch (recoverySource.db_type) {
            case CONF.DB_TYPE.SQLSERVER:
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
            case CONF.DB_TYPE.SAPHANA:
            case CONF.DB_TYPE.TIDB:
            case CONF.DB_TYPE.MONGODB:
                if (recoverySource.cluster_flag !== recoveryTarget.cluster_flag) {
                    // 单机到集群或集群到单机
                    return true;
                }
                if (recoverySource.cluster_flag && recoverySource.cluster_uuid !== recoveryTarget.cluster_uuid) {
                    // 恢复不同的集群上
                    return true;
                }
                if (!recoverySource.cluster_flag && recoverySource.agent_uuid !== recoveryTarget.agent_uuid) {
                    // 恢复不同的单机上
                    return true;
                }
                break;
            case CONF.DB_TYPE.ORACLE:
                if (recoveryTarget.create_new_instance_flag) {
                    // 新建实例不需要提示异机恢复
                    return false;
                }
                if (recoverySource.cluster_flag !== recoveryTarget.cluster_flag) {
                    // 单机到集群或集群到单机
                    return true;
                }
                if (recoverySource.cluster_flag && recoverySource.cluster_uuid !== recoveryTarget.cluster_uuid) {
                    // 恢复不同的集群上
                    return true;
                }
                if (!recoverySource.cluster_flag && recoverySource.agent_uuid !== recoveryTarget.agent_uuid) {
                    // 恢复不同的单机上
                    return true;
                }
                break;
            default:
                if (recoverySource.agent_uuid !== recoveryTarget.agent_uuid) {
                    // 恢复不同的单机上
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * 集中显示SQL Server恢复折叠
     */
    const focusSqlServerAccordion = (nodeKey) => {
        // 隐藏所有
        $('#multiDbConfigWrapper .sqlServerMultiDbConfig .panel-title a.accordion-toggle').addClass('collapsed').attr('aria-expanded', 'false');
        $('#multiDbConfigWrapper .sqlServerMultiDbConfig .panel-collapse').removeClass('in').attr('aria-expanded', 'false').css('height', '0');
        $(`a[href="#sqlServerMultiDbConfig_${nodeKey}"]`).removeClass('collapsed').attr('aria-expanded', 'true');
        $(`#sqlServerMultiDbConfig_${nodeKey}`).addClass('in').attr('aria-expanded', 'true').css('height', 'initial');
    };

    /**
     * 获取SQL server步骤四的恢复配置
     * @param dbConfig
     * @param nodeKey
     */
    const getSqlServerMultiDbConfigStep4Method = (dbConfig, nodeKey) => {
        let msg = ``;
        let selectedNode = recoverySource.time_point_list[nodeKey];
        let text = selectedNode.dir_path;
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            text += ' (' + selectedNode.time_point + ')';
        }
        msg += `<p>
                    <button data-toggle="collapse" class="step4_collapse" style="color: #999; width: 20px;
                        background: none; border: none" href="#step_4_db_${nodeKey}" aria-expanded="false"
                         aria-controls="step_4_db_${nodeKey}">
                        <i class="viconfont vicon-a-Leftzuo"></i>
                    </button>
                    <span>${text}</span>
                </p>`;
        msg += `<div>
                    <div class="collapse" id="step_4_db_${nodeKey}">
                        <div class="card card-body" style="margin-left: 40px">`;
        // 数据库配置
        if (recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) {
            // 新建数据库名
            msg += `<p>${LANG.UI_DB_NEW_DB_NAME}: ${dbConfig.new_db_name}</p>`;
            // 数据文件路径
            msg += `<p>${LANG.UI_DB_RECOVERY_DATA_FILE_PATH}: ${dbConfig.db_datafile_path}</p>`;
            // 日志文件路径
            msg += `<p>${LANG.UI_DB_RECOVERY_LOG_FILE_PATH}: ${dbConfig.db_logfile_path}</p>`;
        }
        // 数据加密密码
        if (selectedNode.is_encrypted && !selectedNode.config.password_auto_flag) {
            msg += `<p>${$('.passwordlabel').html()}: ${LANG.UI_DB_RECOVERY_RIGHT}</p>`;
        }
        // 回滚时间
        if (parseInt(selectedNode.backup_mode) === TIMEPOINT_TYPE_ENUM.LOG) {
            let rollbackDes = $('.logtimelabel').html();
            if (dbConfig.is_rollback) {
                msg += `<p>${rollbackDes}: ${LANG.UI_PUBLIC_ON}, ${LANG.UI_DB_RECOVERY_LOG_ROLL_TIME}: ${dbConfig.rollback_time.pick_time}</p>`;
            } else {
                msg += `<p>${rollbackDes}: ${LANG.UI_PUBLIC_OFF}</p>`;
            }
        }
        msg += `        </div>
                    </div>
                </div>`;
        return msg;
    };

    /**
     * 验证SQL server在步骤3<恢复方式>的配置
     * @returns {boolean}
     */
    var validateSqlServerStep3 = function (pathType) {
        let showStr = getCoverShowStr();
        if (PATH_TYPE_ENUM.CREATE === pathType) {
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_NEW_RECOVERY + '<br>';
        }
        let oldDbRepeat = {};
        let newDbRepeat = {};
        // 在步骤4<确认配置>的<数据库配置>
        showStr += `<div class="bd1de5" style="max-height: 300px; margin-top: 10px; padding: 5px; overflow-y: auto">`;
        for (const nodeKey in recoveryMethod.db_config) {
            let dbConfig = recoveryMethod.db_config[nodeKey];
            let selectedNode = recoverySource.time_point_list[nodeKey];
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                if (selectedNode.eventtype !== 'db') {
                    continue;
                }
            }
            // 覆盖恢复出现同名数据库，要弹出提示，并阻止进入下一步
            if (oldDbRepeat[dbConfig.name] !== undefined && recoveryMethod.path_type === PATH_TYPE_ENUM.COVER) {
                focusSqlServerAccordion(nodeKey);
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_COVER_SAME_OLD_DB);
                return false;
            }
            // 新建数据库恢复填写了相同的数据库名，要弹出提示，并阻止进入下一步
            if (newDbRepeat[dbConfig.new_db_name] !== undefined && recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) {
                focusSqlServerAccordion(nodeKey);
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_AGENT_MODULE_DB + ': '
                    + dbConfig.name + ', ' + LANG.UI_DB_RECOVERY_CREATE_SAME_NEW_DB);
                return false;
            }
            oldDbRepeat[dbConfig.name] = 1;
            newDbRepeat[dbConfig.new_db_name] = 1;

            // 回滚时间验证
            if (parseInt(selectedNode.backup_mode) === TIMEPOINT_TYPE_ENUM.LOG && dbConfig.is_rollback) {
                let reg = /^(?:19|20)[0-9][0-9]-(?:(0[1-9])|(1[0-2]))-(?:([0-2][1-9])|([1-3][0-1])) (?:([0-2][0-3])|([0-1][0-9])):[0-5][0-9]:[0-5][0-9]$/;
                let rollbackTime = dbConfig.rollback_time;
                let pickTime = $('#rollback_time_' + nodeKey).val();
                recoveryMethod.db_config[nodeKey].rollback_time.pick_time = pickTime;
                if (pickTime < rollbackTime.start_time || pickTime > rollbackTime.end_time || !reg.test(pickTime)) {
                    focusSqlServerAccordion(nodeKey);
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_ROLL_TIME_ERROR, LANG.UI_AGENT_MODULE_DB + ': '
                        + dbConfig.name + ', ' + LANG.UI_DB_RECOVERY_ROLL_TIME_ERROR_TIPS);
                    return false;
                }
            }

            // 新建数据库验证
            if (recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) {
                // 新建名和路径未填写返回错误
                if (!dbConfig.new_db_name || !dbConfig.db_datafile_path || !dbConfig.db_logfile_path) {
                    focusSqlServerAccordion(nodeKey);
                    UIToastr.showInfo(LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT, LANG.UI_AGENT_MODULE_DB + ': '
                        + dbConfig.name + ', ' + LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT_TIPS);
                    return false;
                }
                if (!checkPathWhiteSpace(dbConfig.db_datafile_path) || !checkPathWhiteSpace(dbConfig.db_logfile_path)) {
                    focusSqlServerAccordion(nodeKey);
                    return false;
                }
                // 检查数据库文件路径和日志文件路径
                if (!checkPath(dbConfig.db_datafile_path, _OSTYPE) || !checkPath(dbConfig.db_logfile_path, _OSTYPE)) {
                    focusSqlServerAccordion(nodeKey);
                    UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_AGENT_MODULE_DB + ': '
                        + dbConfig.name + ', ' + LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR_TIPS);
                    return false;
                }
            }

            showStr += getSqlServerMultiDbConfigStep4Method(dbConfig, nodeKey);
        }
        showStr += `</div>`;

        $('.recoveryConfigShow').html(showStr);
        $('.step4_collapse').on('click', function () {
            let icon = $(this).find('i.viconfont');
            if ($(this).hasClass('collapsed')) {
                icon.removeClass('vicon-a-Leftzuo').addClass('vicon-a-Upshang');
            } else {
                icon.removeClass('vicon-a-Upshang').addClass('vicon-a-Leftzuo');
            }
        });
        return true;
    };

    /**
     * 集中显示SAP HANA恢复折叠
     */
    const focusSAPHANAAccordion = (nodeKey) => {
        // 隐藏所有
        $('#multiDbConfigWrapper .sapHanaMultiDbConfig .panel-title a.accordion-toggle').addClass('collapsed').attr('aria-expanded', 'false');
        $('#multiDbConfigWrapper .sapHanaMultiDbConfig .panel-collapse').removeClass('in').attr('aria-expanded', 'false').css('height', '0');
        $(`a[href="#sapHanaMultiDbConfig_${nodeKey}"]`).removeClass('collapsed').attr('aria-expanded', 'true');
        $(`#sapHanaMultiDbConfig_${nodeKey}`).addClass('in').attr('aria-expanded', 'true').css('height', 'initial');
    };

    /**
     * 获取SAP HANA步骤四的恢复配置
     * @param dbConfig
     * @param nodeKey
     */
    const getSAPHANAMultiDbConfigStep4Method = (dbConfig, nodeKey) => {
        let msg = ``;
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            let sapHanaRecoveryTimeFlag = parseInt($('#sapHanaRecoveryTime').val());
            let text = dbConfig.dir_path + ' (' + dbConfig.timepoint.time_point + ')';
            msg += `<p>
                        <button data-toggle="collapse" class="step4_collapse" style="color: #999; width: 20px;
                            background: none; border: none" href="#step_4_db_${nodeKey}" aria-expanded="false"
                             aria-controls="step_4_db_${nodeKey}">
                            <i class="viconfont vicon-a-Leftzuo"></i>
                        </button>
                        <span>${text}</span>
                    </p>`;
            msg += `<div>
                        <div class="collapse" id="step_4_db_${nodeKey}">
                            <div class="card card-body" style="margin-left: 40px">`;
            if (recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) {
                // 新建数据库名
                msg += `<p>${LANG.UI_DB_NEW_DB_NAME}: ${dbConfig.new_db_name}</p>`;
            }
            // 恢复时间
            switch (sapHanaRecoveryTimeFlag) {
                case RECOVERY_TIME_FLAG_ENUM.NEWEST:
                case RECOVERY_TIME_FLAG_ENUM.TIME:
                    msg += `<p>${LANG.UI_DB_RECOVERY_RECOVERY_TIME}: ${dbConfig.pick_time}</p>`;
                    break;
            }
            // 初始化日志
            msg += `<p>${LANG.UI_DB_RECOVERY_SAP_HANA_INIT_LOG_AREA}: ${getSwitchDes(dbConfig.initialize_log_area)}</p>`;
            // 数据加密密码
            if (dbConfig.timepoint.is_encrypted && !dbConfig.timepoint.config.password_auto_flag) {
                msg += `<p>${$('.passwordlabel').html()}: ${LANG.UI_DB_RECOVERY_RIGHT}</p>`;
            }
            msg += `        </div>
                        </div>
                    </div>`;
        } else {
            let text = dbConfig.dir_path;
            msg += `<p>
                        <button data-toggle="collapse" class="step4_collapse" style="color: #999; width: 20px;
                            background: none; border: none" href="#step_4_db_${nodeKey}" aria-expanded="false"
                             aria-controls="step_4_db_${nodeKey}">
                            <i class="viconfont vicon-a-Leftzuo"></i>
                        </button>
                        <span>${text}</span>
                    </p>`;
            msg += `<div>
                        <div class="collapse" id="step_4_db_${nodeKey}">
                            <div class="card card-body" style="margin-left: 40px">`;
            if (recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) {
                // 新建数据库名
                msg += `<p>${LANG.UI_DB_NEW_DB_NAME}: ${dbConfig.new_db_name}</p>`;
            }
            // 初始化日志
            msg += `<p>${LANG.UI_DB_RECOVERY_SAP_HANA_INIT_LOG_AREA}: ${getSwitchDes(dbConfig.initialize_log_area)}</p>`;
            msg += `        </div>
                        </div>
                    </div>`;
        }
        return msg;
    };

    /**
     * 验证SAP HANA在步骤3<恢复方式>的配置
     * @returns {boolean}
     */
    var validateSAPHANAStep3 = function (pathType) {
        let showStr = getCoverShowStr();
        if (pathType === PATH_TYPE_ENUM.CREATE) {
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_NEW_RECOVERY + '<br>';
        }
        let sapHanaRecoveryTimeFlag = parseInt($('#sapHanaRecoveryTime').val());
        let now = new Date();
        let nextYearDate = new Date(now.setFullYear(now.getFullYear() + 1));
        let currentDatetime = getCurrentDatetimeStr(nextYearDate);
        let timepointInSameNodeFlag = true;  // 所有的备份点是否在同一节点上
        let nodeUuid = null;
        let newDbNameMap = {};
        // 恢复时间点
        showStr += $('label[for="sapHanaRecoveryTime"]').html() + ': ' + $('#sapHanaRecoveryTime option:selected').html() + '<br>';
        // 在步骤4<确认配置>的<数据库配置>
        showStr += `<div class="bd1de5" style="max-height: 300px; margin-top: 10px; padding: 5px; overflow-y: auto">`;
        for (const nodeKey in recoveryMethod.db_config) {
            let dbConfig = recoveryMethod.db_config[nodeKey];
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                // 存储离线
                if (!dbConfig.timepoint) {
                    focusSAPHANAAccordion(nodeKey);
                    if (sapHanaRecoveryTimeFlag === RECOVERY_TIME_FLAG_ENUM.TIME) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_AGENT_MODULE_DB + ': '
                            + dbConfig.name + ', ' + LANG.UI_DB_RECOVERY_RECOVERY_TIME_EMPTY);
                        return false;
                    } else {
                        $('.sapHanaTimepointStorageOfflineDiv .alert-danger').show();
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY, $('.sapHanaTimepointStorageOfflineDiv span').html().trim());
                        return false;
                    }
                } else if (parseInt(dbConfig.timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                    focusSAPHANAAccordion(nodeKey);
                    $('.sapHanaTimepointStorageOfflineDiv .alert-danger').show();
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY, $('.sapHanaTimepointStorageOfflineDiv span').html().trim());
                    return false;
                }
                if (nodeUuid === null) {
                    nodeUuid = dbConfig.timepoint.storage_info.node_uuid;
                } else if (nodeUuid !== dbConfig.timepoint.storage_info.node_uuid) {
                    timepointInSameNodeFlag = false;
                    break;
                }
                switch (sapHanaRecoveryTimeFlag) {
                    case RECOVERY_TIME_FLAG_ENUM.TIME:  // 选择时间恢复
                        // 验证是否选择了恢复时间
                        if (!dbConfig.timepoint || !dbConfig.pick_time) {
                            focusSAPHANAAccordion(nodeKey);
                            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_AGENT_MODULE_DB + ': '
                                + dbConfig.name + ', ' + LANG.UI_DB_RECOVERY_RECOVERY_TIME_EMPTY);
                            return false;
                        }
                    // fallthrough: 选择时间恢复需要验证数据加密密码
                    case RECOVERY_TIME_FLAG_ENUM.NEWEST:  // 恢复最新点验证数据加密密码
                    case RECOVERY_TIME_FLAG_ENUM.TIMEPOINT:  // 选择备份点恢复验证加密密码
                        if (dbConfig.timepoint.is_encrypted && !dbConfig.timepoint.config.password_auto_flag) {
                            if (!validEncryptPassword(dbConfig.encrypt_password, dbConfig.timepoint.time_point_uuid)) {
                                focusSAPHANAAccordion(nodeKey);
                                UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_AGENT_MODULE_DB + ': '
                                    + dbConfig.name + ', ' + LANG.UI_DB_RECOVERY_DATA_ENCRYPT_PASSWORD_ERROR);
                                return false;
                            }
                        }
                        break;
                }
                if (sapHanaRecoveryTimeFlag === RECOVERY_TIME_FLAG_ENUM.NEWEST) {
                    recoveryMethod.db_config[nodeKey].pick_time = currentDatetime;
                }
            }
            if (recoveryMethod.path_type === PATH_TYPE_ENUM.CREATE) {
                // 新建数据库验证
                if (!dbConfig.new_db_name) {
                    focusSAPHANAAccordion(nodeKey);
                    UIToastr.showInfo(LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT, LANG.UI_AGENT_MODULE_DB + ': '
                        + dbConfig.name + ', ' + LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT_TIPS4);
                    return false;
                }
                // 数据库名重复限制
                if (newDbNameMap[dbConfig.new_db_name] !== undefined) {
                    focusSAPHANAAccordion(nodeKey);
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_AGENT_MODULE_DB + ': '
                        + dbConfig.name + ', ' + LANG.UI_DB_RECOVERY_CREATE_SAME_NEW_DB);
                    return false;
                }
                newDbNameMap[dbConfig.new_db_name] = 1;
            }

            showStr += getSAPHANAMultiDbConfigStep4Method(dbConfig, nodeKey);
        }
        if (!timepointInSameNodeFlag) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_SAPHANA_DIFFERENT_NODE);
            return false;
        }
        showStr += `</div>`;

        $('.recoveryConfigShow').html(showStr);
        $('.step4_collapse').on('click', function () {
            let icon = $(this).find('i.viconfont');
            if ($(this).hasClass('collapsed')) {
                icon.removeClass('vicon-a-Leftzuo').addClass('vicon-a-Upshang');
            } else {
                icon.removeClass('vicon-a-Upshang').addClass('vicon-a-Leftzuo');
            }
        });
        return true;
    }

    /**
     * 构建备份链信息
     */
    const buildOracleRecoveryTimepointChain = () => {
        if (typeof data.detail !== 'object') {
            data.detail = {};
        }
        switch (recoveryMethod.path_type) {
            case PATH_TYPE_ENUM.FULL:
            case PATH_TYPE_ENUM.INCOMPLETE:
                data.detail.recovery_timepoint_chain_info_list = [];
                for (const chainInfo of oracleTimepointList.recovery_timepoint_chain_data) {
                    let encryptPassword = '';
                    if (chainInfo.full_timepoint_info.is_encrypted && !chainInfo.full_timepoint_info.config.password_auto_flag) {
                        encryptPassword = $(`#oracle_encrypt_password_${chainInfo.full_timepoint_uuid}`).val();
                    }
                    let tmpChainInfo = {
                        chain_uuid: chainInfo.chain_uuid,
                        encrypt_password: btoa(encryptPassword),
                        timepoint_uuid_list: [chainInfo.full_timepoint_uuid],
                    };
                    for (const dependTimepointInfo of chainInfo.depend_timepoint_list) {
                        tmpChainInfo.timepoint_uuid_list.push(dependTimepointInfo.time_point_uuid);
                    }
                    data.detail.recovery_timepoint_chain_info_list.push(tmpChainInfo);
                }
                break;
            case PATH_TYPE_ENUM.EXPORT:
                data.detail.recovery_timepoint_chain_info_list = [];
                let checkNodes = oracleExportTree.getCheckedNodes(true);
                for (const checkNode of checkNodes) {
                    let encryptPassword = '';
                    if (checkNode.chain_info.full_timepoint.is_encrypted && !checkNode.chain_info.full_timepoint.config.password_auto_flag) {
                        encryptPassword = $(`#oracle_export_encrypt_password_${checkNode.chain_info.full_timepoint.time_point_uuid}`).val();
                    }
                    let tmpChainInfo = {
                        chain_uuid: checkNode.chain_info.chain_uuid,
                        encrypt_password: btoa(encryptPassword),
                        timepoint_uuid_list: [checkNode.chain_info.full_timepoint.time_point_uuid],
                    };
                    for (const dependTimepointInfo of checkNode.chain_info.depend_timepoint_list) {
                        tmpChainInfo.timepoint_uuid_list.push(dependTimepointInfo.time_point_uuid);
                    }
                    data.detail.recovery_timepoint_chain_info_list.push(tmpChainInfo);
                }
                break;
        }
    };

    /**
     * 渲染Oracle恢复备份时间点树
     * @param pathType
     */
    const renderOracleRecoveryBackupTimepointShowTree = (pathType) => {
        if (recoverySource.recovery_source_type !== RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            return;
        }
        switch (pathType) {
            case PATH_TYPE_ENUM.INCOMPLETE:
            case PATH_TYPE_ENUM.FULL:
                $.fn.zTree.init(
                    $('#oracleRecoveryBackupTimepointShowTree'),
                    getOracleRecoveryChainTreeSetting(),
                    getOracleRecoveryBackupTimepointTreeData(false)
                );
                break;
            case PATH_TYPE_ENUM.EXPORT:
                timepointListChain = [];
                let checkNodes = oracleExportTree.getCheckedNodes(true);
                for (const checkNode of checkNodes) {
                    timepointListChain.push(checkNode.chain_info);
                }
                $.fn.zTree.init(
                    $('#oracleExportShowTree'),
                    getOracleExportTreeSetting(),
                    getOracleExportTreeNodes(timepointListChain, false)
                );
                break;
        }
    };

    /**
     * 验证Oracle在步骤3<恢复方式>的配置
     */
    const validateOracleStep3 = function (pathType) {
        // 覆盖恢复
        let showStr = getCoverShowStr();
        let firstNode = Object.values(recoverySource.time_point_list)[0];

        // 所有备份点、恢复方式都有自定义参数文件
        data.modify_pfile_flag = false;
        data.detail = '';
        data.recovery_time_flag = 0;
        // 存储离线判定
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            if (pathType === PATH_TYPE_ENUM.FULL || pathType === PATH_TYPE_ENUM.INCOMPLETE) {
                // 存储离线
                if (parseInt(oracleTimepointList.select_timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                    $('.oracleTimepointStorageOfflineDiv .alert-danger').show();
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY, $('.oracleTimepointStorageOfflineDiv span').html().trim());
                    return false;
                }
            }
        }
        if (!showStep4OracleRecoveryContent(pathType)) {
            return false;
        }
        if (pathType === PATH_TYPE_ENUM.COVER) {
            data.recoverInfo.olddbname = firstNode.instance_name;
        } else if (pathType === PATH_TYPE_ENUM.EXPORT) { //导出目录恢复
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_RECOVERY_EXPORT_DIR + '<br>';
            // 备份点验证
            let checkNodes = oracleExportTree.getCheckedNodes(true);
            if (!checkNodes.length) {
                UIToastr.showInfo(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_ORACLE_EXPORT_TIMEPOINT_EMPTY);
                return false;
            }
            for (const checkNode of checkNodes) {
                if (checkNode.eventtype === 'timepoint') {
                    showStr += $('label[for="oracleExportTree"]').html() + ': ' + checkNode.p_name + '<br>';
                    data.recoverInfo.olddbname = checkNode.timepoint.db_name;
                    break;
                }
            }
            showStr += $('label[for="oracleExportTree"]').html() + ': <br>';
            showStr += `<ul class="ztree ztree-fa bd1de5" id="oracleExportShowTree"></ul>`;
            // 构建备份链信息
            buildOracleRecoveryTimepointChain();
            data.recoverInfo.newfilepath = $.fn.PathTreeSelector.getCheckPath('exportPath');
            data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
            if (!data.recoverInfo.newfilepath) {
                UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_PDB_INPUT_SPECIFI_FDIR);
                return false;
            }
            if (!checkPathWhiteSpace(data.recoverInfo.newfilepath)) {
                return false;
            }
            //录格式不对返回错误
            if (!checkPath(data.recoverInfo.newfilepath, _OSTYPE)) {
                UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR_TIPS);
                return false;
            }
            showStr += $('.exportlabel').html() + ": " + data.recoverInfo.newfilepath + '<br>';
        } else if (PATH_TYPE_ENUM.RESTORE_ARCHIVELOG === pathType) {  // 还原归档日志恢复
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_RESTORE_ARCHIVELOG + '<br>';
            data.recoverInfo.olddbname = firstNode.instance_name;
            data.recoverInfo.newlogpath = $.fn.PathTreeSelector.getCheckPath('restoreArchivelog');
            data.pointInfo.points[0].logfile_path = data.recoverInfo.newlogpath;
            if (!data.recoverInfo.newlogpath) {
                UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_ORACLE_RESTORE_ARCHIVELOG_EMPTY);
                return false;
            }
            if (!checkPathWhiteSpace(data.recoverInfo.newfilepath)) {
                return false;
            }
            showStr += $('.restoreArchivelogLabel').html() + ': ' + data.recoverInfo.newlogpath + '<br>';
            // 还原归档日志方式
            let restoreArchivelogType = parseInt($('#restoreArchivelogType').val());
            if (1 === restoreArchivelogType) {
                data.recoverInfo.log_restore_start_time = $('#oracleRestoreTimeSelectStartTime').val();
                data.recoverInfo.log_restore_end_time = $('#oracleRestoreTimeSelectEndTime').val();
                if (!data.recoverInfo.log_restore_end_time || !data.recoverInfo.log_restore_start_time) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RESTORE_ARCHIVELOG_TYPE_TIME_TIPS2);
                    return false;
                }
                showStr += $('.restoreArchivelogTypeLabel').html() + ': ' + $('#restoreArchivelogType option:selected').html() + '<br>';
                showStr += $('label[for="oracleRestoreTimeSelectRange"]').html() + ': '
                    + data.recoverInfo.log_restore_start_time + ' ~ ' + data.recoverInfo.log_restore_end_time + '<br>';
            }
        } else if (PATH_TYPE_ENUM.FULL === pathType) {  // 完全恢复
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_RECOVERY_FULL + '<br>';
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                // 恢复备份点
                if (!oracleTimepointList.recovery_timepoint_chain_data.length || !oracleRecoveryChainTree) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_ORACLE_RECOVERY_BACKUP_TIMEPOINT_NO_TIMEPOINT);
                    return false;
                }
                showStr += $('label[for="oracleRecoveryBackupTimepoint"]').html() + ': <br>';
                showStr += `<ul class="ztree ztree-fa bd1de5" id="oracleRecoveryBackupTimepointShowTree"></ul>`;
                // 构建备份链信息
                buildOracleRecoveryTimepointChain();
                data.recoverInfo.olddbname = oracleTimepointList.select_timepoint.db_name;
                // data.recoverInfo.newfilepath = $.fn.PathTreeSelector.getCheckPath('oracleRecoveryDataPath');
                // data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
                // if (data.recoverInfo.newfilepath.length) {
                //     showStr += $('label[for="oracleRecoveryDataPath"]').html() + ': ' + data.recoverInfo.newfilepath + '<br>';
                // } else {
                //     showStr += $('label[for="oracleRecoveryDataPath"]').html() + ': ' + LANG.UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY + '<br>';
                // }
            }
        } else if (PATH_TYPE_ENUM.INCOMPLETE === pathType) {   // 不完全恢复
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_RECOVERY_FULL_INCOMPLETE + '<br>';
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                data.recoverInfo.olddbname = oracleTimepointList.select_timepoint.db_name;
                // 恢复分支
                showStr += $('label[for="oracleRecoveryIncarnation"]').html() + ': ' + $('#oracleRecoveryIncarnation option:selected').html() + '<br>';
                // 时间点恢复方式
                let oracleRecoveryTimepointFlag = parseInt($('#oracleRecoveryTimepoint').val());
                data.recovery_time_flag = oracleRecoveryTimepointFlag;
                showStr += $('label[for="oracleRecoveryTimepoint"]').html() + ': ' + $('#oracleRecoveryTimepoint option:selected').html() + '<br>';

                // 恢复时间显示
                let recoveryTime = '';
                if (oracleRecoveryTimepointFlag === ORACLE_RECOVERY_TIMEPOINT_ENUM.NEWEST) {
                    recoveryTime = getCurrentDatetimeStr(new Date(oracleTimepointList.select_timepoint.src_end_time_point * 1000));
                } else if (oracleRecoveryTimepointFlag === ORACLE_RECOVERY_TIMEPOINT_ENUM.TIME) {
                    // 恢复来源
                    showStr += $('label[for="oracleRecoverySource"]').html() + ': ' + $('#oracleRecoverySource option:selected').html() + '<br>';
                    let oracleRangeTimepointList = oracleTimepointList;
                    let oracleRecoverySourceType = parseInt($('#oracleRecoverySource').val());
                    if (oracleRecoverySourceType === ORACLE_RECOVERY_SOURCE_ENUM.SPECIFY_TASK) {  // 指定任务
                        oracleRangeTimepointList = oracleTimepointList.select_task_chain;
                        if (!oracleRecoveryTaskTree) {
                            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_ORACLE_NO_SPECIFY_TASK);
                            return false;
                        }
                        let oracleSpecifyTaskNodes = oracleRecoveryTaskTree.getCheckedNodes(true);
                        if (!oracleSpecifyTaskNodes.length) {
                            UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_ORACLE_NO_SPECIFY_TASK);
                            return false;
                        }
                        showStr += $('label[for="oracleRecoverySpecifyTask"]').html() + ': ' + oracleSpecifyTaskNodes[0].name + '<br>';
                    }
                    recoveryTime = oracleRecoveryTime;
                    let selectTimestamp = convertToUnixTimestamp(recoveryTime);
                    if (isNaN(selectTimestamp)) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_ORACLE_INVALID_TIME);
                        return false;
                    }
                    if (selectTimestamp < oracleTimepointList.start_time || selectTimestamp > oracleTimepointList.end_time) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_ORACLE_NONSUPPORT_TIMEPOINT_TIPS);
                        return false;
                    }
                    // 判断恢复时间是否处于存储离线区域
                    if (!judgeOracleRecoveryTimeNotInNonsupportRange(selectTimestamp, oracleRangeTimepointList)) {
                        $('.oracleTimepointStorageOfflineDiv').show();
                        $('.oracleTimepointStorageOfflineDiv .alert-danger').show();
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY, $('.oracleTimepointStorageOfflineDiv span').html().trim());
                        return false;
                    }
                    // 判断恢复时间是否处于不可恢复区域
                    if (!judgeOracleRecoveryTimeNotInNonsupportRange(recoveryTime, oracleRangeTimepointList, 'nonsupport_time_range')) {
                        $('.oracleTimepointRangeNonsupportDiv').show();
                        $('.oracleTimepointRangeNonsupportDiv .alert-danger').show();
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY, $('.oracleTimepointRangeNonsupportDiv span').html().trim());
                        return false;
                    }
                    showStr += LANG.UI_DB_RECOVERY_RECOVERY_TIME + ' : ' + recoveryTime + '<br>';
                }
                // 恢复备份点
                if (!oracleTimepointList.recovery_timepoint_chain_data.length || !oracleRecoveryChainTree) {
                    UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_ORACLE_RECOVERY_BACKUP_TIMEPOINT_NO_TIMEPOINT);
                    return false;
                }
                showStr += $('label[for="oracleRecoveryBackupTimepoint"]').html() + ': <br>';
                showStr += `<ul class="ztree ztree-fa bd1de5" id="oracleRecoveryBackupTimepointShowTree"></ul>`;
                // 构建备份链信息
                buildOracleRecoveryTimepointChain();

                // 数据恢复目录
                data.recoverInfo.newfilepath = $.fn.PathTreeSelector.getCheckPath('oracleRecoveryDataPath');
                data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
                if (data.recoverInfo.newfilepath.length) {
                    showStr += $('label[for="oracleRecoveryDataPath"]').html() + ': ' + data.recoverInfo.newfilepath + '<br>';
                } else {
                    showStr += $('label[for="oracleRecoveryDataPath"]').html() + ': ' + LANG.UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY + '<br>';
                }
                // 新建实例恢复
                if (recoveryTarget.create_new_instance_flag) {
                    // 数据恢复目录不能为空
                    if (!data.recoverInfo.newfilepath) {
                        UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_PDB_INPUT_SPECIFI_FDIR);
                        return false;
                    }
                    if (!recoveryMethod.create_new_instance_config.config_create_new_instance_flag) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_UNCONFIGURED);
                        return false;
                    }
                }
            } else {
                if (firstNode.eventtype == 'cluster') {
                    data.recoverInfo.olddbname = firstNode.app_service_name;
                } else if (firstNode.eventtype === 'instance') {
                    data.recoverInfo.olddbname = firstNode.instance_name;
                }
                // 时间点恢复方式
                let oracleRecoveryTimepointFlag = parseInt($('#oracleRecoveryTimepoint').val());
                data.recovery_time_flag = oracleRecoveryTimepointFlag;
                showStr += $('label[for="oracleRecoveryTimepoint"]').html() + ': ' + $('#oracleRecoveryTimepoint option:selected').html() + '<br>';
                // 数据恢复目录
                data.recoverInfo.newfilepath = $.fn.PathTreeSelector.getCheckPath('oracleRecoveryDataPath');
                data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
                if (data.recoverInfo.newfilepath.length) {
                    showStr += $('label[for="oracleRecoveryDataPath"]').html() + ': ' + data.recoverInfo.newfilepath + '<br>';
                } else {
                    showStr += $('label[for="oracleRecoveryDataPath"]').html() + ': ' + LANG.UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY + '<br>';
                }
            }
        }
        if (!hideOpenDb) {
            data.recoverInfo.open_db_flag = !!$('#openDbCheck').get(0).checked;
            showStr += $('.openDbLabel').html().trim() + ': ' + getSwitchDes(data.recoverInfo.open_db_flag) + '<br>';
        }

        // 数据加密密码
        if (!validateStep3Encrypt()) {
            return false;
        }

        $('.recoveryConfigShow').html(showStr);
        renderOracleRecoveryBackupTimepointShowTree(pathType);
        return true;
    };

    /**
     * 验证MySQL在步骤3<恢复方式>的配置
     */
    const validateMySQLStep3 = pathType => {
        let showStr = getCoverShowStr();
        var logfilelabel = $('.logfilelabel').html();
        var startcommandlabel = $('.startcommandlabel').html();
        if (RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT === recoverySource.recovery_source_type) {
            //没有填写启动命令，原数据库恢复支持
            if (
                data.pointInfo.points[0].time_point_type === TIMEPOINT_TYPE_ENUM.LOG &&
                pathType === PATH_TYPE_ENUM.COVER
            ) {
                data.detail.start_type = parseInt($('#mysqlStartType').val());
                showStr += $('label[for="mysqlStartType"]').html() + ": " + $('#mysqlStartType option:selected').html() + '<br>';
                if (data.detail.start_type === MYSQL_START_TYPE_ENUM.SERVICE) {
                    data.detail.start_command = $('#startcommand').val();
                    data.detail.stop_command = '';
                    if (!data.detail.start_command.trim().length) {
                        UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_INPUT_START_COMMAND);
                        return false;
                    }
                    showStr += startcommandlabel + ": " + data.detail.start_command + '<br>';
                } else {
                    data.detail.start_command = $('#mysqlCustomStart').val();
                    data.detail.stop_command = '';
                    if (!data.detail.start_command.trim().length) {
                        UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_INPUT_START_CUSTOM_COMMAND);
                        return false;
                    }
                    showStr += $('label[for="mysqlCustomStart"]').html() + ": " + data.detail.start_command + '<br>';
                }
            }
            // 打开数据库
            if (pathType === PATH_TYPE_ENUM.COVER) {
                if (
                    data.pointInfo.points[0].time_point_type === TIMEPOINT_TYPE_ENUM.FULL ||
                    data.pointInfo.points[0].time_point_type === TIMEPOINT_TYPE_ENUM.INCR
                ) {
                    data.recoverInfo.open_db_flag = !!$('#mysqlOpenDbCheck').get(0).checked;
                    showStr += $('label[for="mysqlOpenDbCheck"]').html() + ": " + getSwitchDes(data.recoverInfo.open_db_flag) + '<br>';
                    if (data.recoverInfo.open_db_flag) {
                        data.detail.start_type = parseInt($('#mysqlStartType').val());
                        showStr += $('label[for="mysqlStartType"]').html() + ": " + $('#mysqlStartType option:selected').html() + '<br>';
                        if (data.detail.start_type === MYSQL_START_TYPE_ENUM.SERVICE) {
                            data.detail.start_command = $('#startcommand').val();
                            data.detail.stop_command = '';
                            if (!data.detail.start_command.trim().length) {
                                UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_INPUT_START_COMMAND);
                                return false;
                            }
                            showStr += startcommandlabel + ": " + data.detail.start_command + '<br>';
                        } else {
                            data.detail.start_command = $('#mysqlCustomStart').val();
                            data.detail.stop_command = '';
                            if (!data.detail.start_command.trim().length) {
                                UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_INPUT_START_CUSTOM_COMMAND);
                                return false;
                            }
                            showStr += $('label[for="mysqlCustomStart"]').html() + ": " + data.detail.start_command + '<br>';
                        }
                    }
                }
            }
        } else {
            data.recoverInfo.newfilepath = '';
            data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
            // 恢复最新备份点需要数据库服务名
            data.detail.start_type = parseInt($('#mysqlStartType').val());
            showStr += $('label[for="mysqlStartType"]').html() + ": " + $('#mysqlStartType option:selected').html() + '<br>';
            if (data.detail.start_type === MYSQL_START_TYPE_ENUM.SERVICE) {
                data.detail.start_command = $('#startcommand').val();
                data.detail.stop_command = '';
                if (!data.detail.start_command.trim().length) {
                    UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_INPUT_START_COMMAND);
                    return false;
                }
                showStr += startcommandlabel + ": " + data.detail.start_command + '<br>';
            } else {
                data.detail.start_command = $('#mysqlCustomStart').val();
                data.detail.stop_command = $('#mysqlCustomStop').val();
                if (!data.detail.start_command.trim().length) {
                    UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_INPUT_START_CUSTOM_COMMAND);
                    return false;
                }
                if (!data.detail.stop_command.trim().length) {
                    UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_INPUT_STOP_CUSTOM_COMMAND);
                    return false;
                }
                showStr += $('label[for="mysqlCustomStart"]').html() + ": " + data.detail.start_command + '<br>';
                showStr += $('label[for="mysqlCustomStop"]').html() + ": " + data.detail.stop_command + '<br>';
            }
        }
        //重定性目录恢复
        if (pathType === PATH_TYPE_ENUM.REDIRECT_DIR) {
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_MYSQL_REDIRECT_RECOVERY + '<br>';
            data.recoverInfo.newlogpath = $.fn.PathTreeSelector.getCheckPath('logfilePath');
            data.pointInfo.points[0].logfile_path = data.recoverInfo.newlogpath;
            //没填写重定向目录返回错误
            if (!data.recoverInfo.newlogpath) {
                UIToastr.showInfo(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_INPUT_REDIRECT_DIR);
                return false;
            }
            if (!checkPathWhiteSpace(data.recoverInfo.newlogpath)) {
                return false;
            }
            //重定向目录格式不对返回错误
            if (!checkPath(data.recoverInfo.newlogpath, _OSTYPE)) {
                UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR_TIPS);
                return false;
            }
            showStr += logfilelabel + ": " + data.recoverInfo.newlogpath + '<br>';
        }

        // 数据加密密码
        if (!validateStep3Encrypt()) {
            return false;
        }

        /**
         * 验证日志回滚
         */
        let logRollback = validateStep3Rollback();
        if (logRollback === false) {
            return false;
        }
        showStr += logRollback;
        $('.recoveryConfigShow').html(showStr);
        return true;
    };

    /**
     * 验证DM在步骤3<恢复方式>的配置
     */
    const validateDMStep3 = pathType => {
        let showStr = getCoverShowStr();
        if (PATH_TYPE_ENUM.SPECIFY_FOLDER === pathType) {  // 指定文件夹恢复
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_DM_FILE_DIR_RECOVERY + '<br>';
            data.recoverInfo.newfilepath = $.fn.PathTreeSelector.getCheckPath('dmFilePath');
            data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
            //文件夹路径为空
            if (!data.recoverInfo.newfilepath) {
                UIToastr.showInfo(LANG.UI_DB_DM_FILE_DIR_INFO_INPUT, LANG.UI_DB_DM_FILE_DIR_INFO_INPUT_TIPS);
                return false;
            }
            if (!checkPathWhiteSpace(data.recoverInfo.newfilepath)) {
                return false;
            }
            // //检查文件夹路径格式
            // if (!checkPath(data.recoverInfo.newfilepath, _OSTYPE)) {
            //     UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR_TIPS);
            //     return false;
            // }
            showStr += LANG.UI_DB_RECOVERY_FILE_DIR_PATH + ": " + data.recoverInfo.newfilepath + '<br>';
        }

        // 数据加密密码
        if (!validateStep3Encrypt()) {
            return false;
        }

        /**
         * 验证日志回滚
         */
        let logRollback = validateStep3Rollback();
        if (logRollback === false) {
            return false;
        }
        showStr += logRollback;
        $('.recoveryConfigShow').html(showStr);
        return true;
    };

    /**
     * 验证PG在步骤3<恢复方式>的配置
     */
    const validatePGStep3 = pathType => {
        let showStr = getCoverShowStr();
        if (PATH_TYPE_ENUM.CREATE === pathType) {  // 新建实例恢复
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_NEW_INSTANCE_RECOVERY + '<br>';
            data.recoverInfo.newfilepath = $.fn.PathTreeSelector.getCheckPath('pgDataFilePath');
            data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
            data.recoverInfo.newlogpath = $.fn.PathTreeSelector.getCheckPath('pgArchivelogFilePath');
            data.pointInfo.points[0].logfile_path = data.recoverInfo.newlogpath;
            let instancePort = parseInt($('#pgInstancePort').val());

            // 数据库文件路径为空
            if (!data.recoverInfo.newfilepath) {
                UIToastr.showInfo(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT_TIPS2);
                return false;
            }
            if (!checkPathWhiteSpace(data.recoverInfo.newfilepath)) {
                return false;
            }
            // 检查数据库文件路径格式
            if (!checkPath(data.recoverInfo.newfilepath, _OSTYPE)) {
                UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR_TIPS);
                return false;
            }

            // 归档日志文件路径为空
            if (!data.recoverInfo.newlogpath) {
                UIToastr.showInfo(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_NEW_DATABASE_RECOVERY_INFO_INPUT_TIPS2);
                return false;
            }
            if (!checkPathWhiteSpace(data.recoverInfo.newlogpath)) {
                return false;
            }
            // 检查归档日志文件路径格式
            if (!checkPath(data.recoverInfo.newlogpath, _OSTYPE)) {
                UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR_TIPS);
                return false;
            }

            // 检查实例端口
            if (!instancePort || instancePort < 0 || instancePort > 65535) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY, LANG.UI_NODE_PORT_TIPS);
                return false;
            }
            // 实例端口不能与恢复目标一致
            if (instancePort === parseInt(recoveryTarget.instance_uuid)) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY, LANG.UI_DB_RECOVERY_PG_INSTANCE_PORT_TIPS1);
                return false;
            }
            data.recoverInfo.newfilepath += ':' + instancePort;
            data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
            showStr += LANG.UI_DB_RECOVERY_DATA_FILE_PATH + ': ' + data.recoverInfo.newfilepath + '<br>';
            showStr += LANG.UI_DB_RECOVERY_ARCHIVELOG_FILE_PATH + ': ' + data.recoverInfo.newlogpath + '<br>';
        } else if (PATH_TYPE_ENUM.SPECIFY_FOLDER === pathType) {  // 指定文件夹恢复
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_DM_FILE_DIR_RECOVERY + '<br>';
            data.recoverInfo.newfilepath = $.fn.PathTreeSelector.getCheckPath('pgSpecifyFolder');
            data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
            data.recoverInfo.newlogpath = $.fn.PathTreeSelector.getCheckPath('pgCustomArchivelog'); //自定义目录
            data.pointInfo.points[0].logfile_path = data.recoverInfo.newlogpath;
            //文件夹路径为空
            if (!data.recoverInfo.newfilepath) {
                UIToastr.showInfo(LANG.UI_DB_DM_FILE_DIR_INFO_INPUT, LANG.UI_DB_DM_FILE_DIR_INFO_INPUT_TIPS);
                return false;
            }
            if (!checkPathWhiteSpace(data.recoverInfo.newfilepath)) {
                return false;
            }
            //检查文件夹路径格式
            if (!checkPath(data.recoverInfo.newfilepath, _OSTYPE)) {
                UIToastr.showWarning(LANG.UI_DB_NEW_DATABASE_RECOVERY_PATH_ERROR, LANG.UI_DB_RECOVERY_SPECIFY_DIR_TIPS);
                return false;
            }
            // 自定义归档目录
            if (!data.recoverInfo.newlogpath) {
                UIToastr.showInfo(LANG.UI_DB_DM_FILE_DIR_INFO_INPUT, LANG.UI_DB_RECOVERY_CUSTOM_ARCHIVE_DIR_CHECK_TIPS2);
                return false;
            }
            if (!checkPathWhiteSpace(data.recoverInfo.newlogpath)) {
                return false;
            }
            //自定义归档目录
            if (!data.recoverInfo.newlogpath || !checkPath(data.recoverInfo.newlogpath, _OSTYPE)) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY, LANG.UI_DB_RECOVERY_CUSTOM_ARCHIVE_DIR_CHECK_TIPS);
                return false;
            }
            showStr += LANG.UI_DB_RECOVERY_FILE_DIR_PATH + ": " + data.recoverInfo.newfilepath + '<br>';
            showStr += LANG.UI_DB_RECOVERY_CUSTOM_ARCHIVE_DIR + ": " + data.recoverInfo.newlogpath + '<br>';
        }
        data.recoverInfo.open_db_flag = !!$('#openDbCheck').get(0).checked;
        showStr += $('.openDbLabel').html().trim() + ': ' + getSwitchDes(data.recoverInfo.open_db_flag) + '<br>';

        // 数据加密密码
        if (!validateStep3Encrypt()) {
            return false;
        }

        /**
         * 验证日志回滚
         */
        let logRollback = validateStep3Rollback();
        if (logRollback === false) {
            return false;
        }
        showStr += logRollback;
        $('.recoveryConfigShow').html(showStr);
        return true;
    };

    /**
     * 验证TiDB在步骤3<恢复方式>的配置
     */
    const validateTiDBStep3 = pathType => {
        let showStr = getCoverShowStr();
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            // 存储离线
            if (!tidbTimepointList.select_timepoint || parseInt(tidbTimepointList.select_timepoint.storage_info.storage_status) !== STORAGE_STATUS_ENUM.ONLINE) {  // 不在线即离线
                $('.tidbTimepointStorageOfflineDiv').show();
                $('.tidbTimepointStorageOfflineDiv .alert-danger').show();
                UIToastr.showWarning(LANG.UI_DB_RECOVERY, $('.tidbTimepointStorageOfflineDiv span').html().trim());
                return false;
            }
            data.recoverInfo.olddbname = tidbTimepointList.select_timepoint.db_name;
            // 时间点恢复方式
            let tidbRecoveryTimepointFlag = parseInt($('#tidbRecoveryTimepoint').val());
            data.recovery_time_flag = tidbRecoveryTimepointFlag;
            showStr += $('label[for="tidbRecoveryTimepoint"]').html() + ': ' + $('#tidbRecoveryTimepoint option:selected').html() + '<br>';
            if (tidbRecoveryTimepointFlag === TIDB_RECOVERY_TIMEPOINT_ENUM.TIME) {
                showStr += $('label[for="tidbRecoveryBackupSet"]').html() + ': ' + $('#tidbRecoveryBackupSet option:selected').html() + '<br>';
                let singlePointFlag = !!$('#tidbRecoveryBackupSet option:selected').data('single');
                if (!singlePointFlag) {
                    let recoveryTime = $('#tidbRecoveryTimeSelectTime').val();
                    if (recoveryTime === LANG.UI_DB_RECOVERY_TIDB_NONSUPPORT_TIMEPOINT) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_TIDB_NONSUPPORT_TIMEPOINT_TIPS);
                        return false;
                    }
                    let selectTimestamp = convertToUnixTimestamp(recoveryTime);
                    if (isNaN(selectTimestamp)) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_TIDB_INVALID_TIME);
                        return false;
                    }
                    if (selectTimestamp < tidbTimepointList.start_time || selectTimestamp > tidbTimepointList.end_time) {
                        UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, LANG.UI_DB_RECOVERY_TIDB_NONSUPPORT_TIMEPOINT_TIPS);
                        return false;
                    }
                    showStr += $('label[for="tidbRecoveryTimeSelectRange"]').html() + ': ' + $('#tidbRecoveryTimeSelectTime').val() + '<br>';
                }
            }
        }
        if (PATH_TYPE_ENUM.COVER === pathType) {  // 原数据库覆盖
            //
        }
        data.recoverInfo.open_db_flag = !!$('#openDbCheck').get(0).checked;
        showStr += $('.openDbLabel').html().trim() + ': ' + getSwitchDes(data.recoverInfo.open_db_flag) + '<br>';

        // 数据加密密码
        if (!validateStep3Encrypt()) {
            return false;
        }

        $('.recoveryConfigShow').html(showStr);
        return true;
    };

    /**
     * 验证MongoDB在步骤3<恢复方式>的配置
     */
    const validateMongoDBStep3 = pathType => {
        let showStr = getCoverShowStr();
        if (PATH_TYPE_ENUM.SPECIFY_FOLDER === pathType) {  // 指定文件夹恢复
            showStr = LANG.UI_DB_RECOVERY_TYPE + ': ' + LANG.UI_DB_DM_FILE_DIR_RECOVERY + '<br>';
            data.recoverInfo.newfilepath = $.fn.PathTreeSelector.getCheckPath('mongodbDatafilePath');
            data.pointInfo.points[0].datafile_path = data.recoverInfo.newfilepath;
            //文件夹路径为空
            if (!data.recoverInfo.newfilepath) {
                UIToastr.showInfo(LANG.UI_DB_DM_FILE_DIR_INFO_INPUT, LANG.UI_DB_MONGODB_SELECT_FILE_PATH_TIP);
                return false;
            }
            showStr += $('.mongodbSelectDirLabel').html() + ': ' + data.recoverInfo.newfilepath + '<br>';
        }
        data.recoverInfo.max_object_transport_parallel_nums = parseInt($('#parallelNum').val());  // 客户端并行数量

        // 数据加密密码
        if (!validateStep3Encrypt()) {
            return false;
        }
        // 打开数据库
        data.recoverInfo.open_db_flag = !!$('#openDbCheck').get(0).checked;
        showStr += $('.openDbLabel').html().trim() + ': ' + getSwitchDes(data.recoverInfo.open_db_flag) + '<br>';
        /**
         * 验证日志回滚
         */
        let logRollback = validateStep3Rollback();
        if (logRollback === false) {
            return false;
        }
        showStr += logRollback;

        $('.recoveryConfigShow').html(showStr);
        return true;
    };

    //////////////////// 结束-步骤3验证 ////////////////////

    /**
     * 设置步骤4恢复脚本描述
     */
    const setStep4RecoveryScriptDes = () => {
        setRecoveryScriptName();
        let scriptConfigSwitchFlag = !!$('#scriptConfigSwitch').get(0).checked;
        if (!scriptConfigSwitchFlag) {
            $('.scriptConfigShow').html(LANG.UI_DB_SCRIPT_CONFIG_UNSET);
        } else {
            let scriptConfigDes = '';
            if (data.pointInfo.points[0].before_task_script.length) {
                scriptConfigDes += LANG.UI_PUBLIC_BEFORE_RECOVERY_SCRIPT + ': ';
                scriptConfigDes += data.pointInfo.points[0].before_task_script.map(v => v.script_name).join('、') + '<br>';
            } else {
                scriptConfigDes += LANG.UI_PUBLIC_BEFORE_RECOVERY_SCRIPT + ': ' + LANG.UI_PUBLIC_NOTHING + '<br>';
            }
            if (data.pointInfo.points[0].after_task_script.length) {
                scriptConfigDes += LANG.UI_PUBLIC_AFTER_RECOVERY_SCRIPT + ': ';
                scriptConfigDes += data.pointInfo.points[0].after_task_script.map(v => v.script_name).join('、') + '<br>';
            } else {
                scriptConfigDes += LANG.UI_PUBLIC_AFTER_RECOVERY_SCRIPT + ': ' + LANG.UI_PUBLIC_NOTHING + '<br>';
            }
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                if (data.pointInfo.points[0].verification_script.length) {
                    scriptConfigDes += LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT + ': ';
                    scriptConfigDes += data.pointInfo.points[0].verification_script.map(v => v.script_name).join('、') + '<br>';
                } else {
                    scriptConfigDes += LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT + ': ' + LANG.UI_PUBLIC_NOTHING + '<br>';
                }
            }
            $('.scriptConfigShow').html(scriptConfigDes);
        }
    };

    /**
     * 设置步骤4安全策略描述
     */
    const setStep4SafeStrategyDes = () => {
        if (CONF.FUNCTIONS.includes('integrity')) {
            $('.safeStrategyShow').html($('#integrityCheck').getCompleteStrategyCovery().str);
        } else {
            $('#safeStrategyShowDiv').hide();
        }
    };

    var showStep3 = function () {
        // Oracle新建实例恢复
        $('.createNewInstanceConfigShowDiv').hide();
        if (
            recoverySource.db_type === CONF.DB_TYPE.ORACLE &&
            recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT &&
            recoveryTarget.create_new_instance_flag
        ) {
            let newInstanceStr = `${$('.newInstanceNameLabel').html()}: ${recoveryMethod.create_new_instance_config.instance_name}<br />`;
            // newInstanceStr += `${$('.newInstanceSystemUserLabel').html()}: ${recoveryMethod.create_new_instance_config.system_user}<br />`;
            newInstanceStr += `${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_SPFILE_CONFIG}: `;
            let style = [
                'width: 100%',
                'height: 300px',
                'overflow: auto',
                'padding: 8px',
                'border: 1px solid #eee',
                'margin-top: 8px',
            ];
            newInstanceStr += `<div style="${style.join('; ')}">${recoveryMethod.create_new_instance_config.spfile_content.join('<br />')}</div>`
            $('.createNewInstanceConfigShow').html(newInstanceStr);
            $('.createNewInstanceConfigShowDiv').show();
        }
        $('.transferShowDiv').show();  // 显示传输策略
        let msg = $.trim($('.transferEncryptedLabel').html()) + ': ' + getSwitchDes($('#transferEncryptedCheck').get(0).checked) + '<br>';
        // 传输加密算法
        if ($('#transferEncryptedCheck').get(0).checked) {
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
            }
            msg += encryptedMethodLabel + ": " + grade + "<br>";
        }
        if ($('.transfernetworkDiv').css('display') !== 'none') {  // 传输网络
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
            msg += $.trim($('.transfernetworklabel').html()) + ': ' + networkNode.str + '<br>';
        }
        if (CONF.BD_STORAGE_TYPE.TAPE !== storage_type && drillStorageType !== CONF.BD_STORAGE_TYPE.TAPE) {
            if ($('.threadDiv').css('display') !== 'none') {  // 传输线程
                msg += $.trim($('.threadnumlabel').html()) + ': ' + $('#recoveryThreadNum').val() + '<br>';
            } else if ($('.mongodbThreadDiv').css('display') !== 'none') {
                if (CONF.FUNCTIONS.includes('multithread')) {
                    msg += $.trim($('.mongodbthreadnumlabel').html()) + ': ' + $('#mongodbRecoveryThreadNum').val() + '<br>';
                }
            }
        }
        // 恢复脚本
        setStep4RecoveryScriptDes();
        // 安全策略
        setStep4SafeStrategyDes();
        $('.transferShow').html(msg);
    }

    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function (check) {
        if (check) {
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    /**
     * 设置SQL SERVER提交数据库的数据
     */
    const setSqlServerSubmitDBData = () => {
        data.pointInfo.points = [];
        let encryptPassword = '';
        let scriptConfigSwitchFlag = !!$('#scriptConfigSwitch').get(0).checked;
        let beforeTaskScript = [];
        let afterTaskScript = [];
        let verificationScript = [];
        if (scriptConfigSwitchFlag) {
            beforeTaskScript = $.fn.getVinScript('beforeRecoveryScriptConfig');
            afterTaskScript = $.fn.getVinScript('afterRecoveryScriptConfig');
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                verificationScript = $.fn.getVinScript('validateScriptConfig');
            }
        }
        $.each(recoveryMethod.db_config, (nodeKey, dbConfig) => {
            let selectedNode = recoverySource.time_point_list[nodeKey];
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                if (selectedNode.eventtype !== 'db') {
                    return;
                }
            }
            let isCreateDb = recoveryMethod.path_type !== PATH_TYPE_ENUM.COVER;
            encryptPassword = '';
            if (!encryptPassword) {
                encryptPassword = dbConfig.encrypt_password;
            }
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                data.pointInfo.points.push({
                    dbuuid: selectedNode.db_uuid,
                    dbname: selectedNode.db_name,
                    instanceuuid: selectedNode.instance_uuid,
                    timepointuuid: selectedNode.timepoint_uuid,
                    agentuuid: selectedNode.agent_uuid,
                    dir_path: selectedNode.dir_path,
                    oldDbname: dbConfig.name,
                    encrypt_password: btoa(dbConfig.encrypt_password),
                    encrypt_password_clear: dbConfig.encrypt_password,
                    is_create_db: isCreateDb,
                    new_db_name: isCreateDb ? dbConfig.new_db_name : '',
                    datafile_path: isCreateDb ? dbConfig.db_datafile_path : '',
                    logfile_path: isCreateDb ? dbConfig.db_logfile_path : '',
                    is_rollback: dbConfig.is_rollback,
                    rollback_time: dbConfig.rollback_time.pick_time,
                    initialize_log_area: false,
                    before_task_script: beforeTaskScript,
                    after_task_script: afterTaskScript,
                    verification_script: [],
                });
            } else {
                data.pointInfo.points.push({
                    dbuuid: '',
                    dbname: selectedNode.db_name,
                    instanceuuid: selectedNode.instance_name,
                    timepointuuid: '',
                    time_point_type: 0,
                    agentuuid: selectedNode.agent_uuid,
                    cluster_uuid: selectedNode.cluster_uuid,
                    cluster_flag: selectedNode.cluster_flag,
                    dir_path: selectedNode.dir_path,
                    oldDbname: selectedNode.db_name,
                    encrypt_password: '',
                    encrypt_password_clear: '',
                    is_create_db: isCreateDb,
                    new_db_name: isCreateDb ? dbConfig.new_db_name : '',
                    datafile_path: isCreateDb ? dbConfig.db_datafile_path : '',
                    logfile_path: isCreateDb ? dbConfig.db_logfile_path : '',
                    log_time_point: '',
                    log_start_time_point: '',
                    log_end_time_point: '',
                    node_uuid: '',
                    source_agent_uuid: selectedNode.agent_uuid,
                    source_instance_name: selectedNode.instance_name,
                    source_db_name: selectedNode.db_name,
                    initialize_log_area: false,
                    before_task_script: beforeTaskScript,
                    after_task_script: afterTaskScript,
                    verification_script: verificationScript,
                });
            }
        });
        data.recoverInfo.encrypt_password_clear = encryptPassword;
        data.recoverInfo.encrypt_password = btoa(encryptPassword);
    };

    /**
     * 获取SAP HANA提交数据库的数据
     */
    const setSAPHANASubmitDBData = () => {
        data.pointInfo.points = [];
        let encryptPassword = '';
        let scriptConfigSwitchFlag = !!$('#scriptConfigSwitch').get(0).checked;
        let beforeTaskScript = [];
        let afterTaskScript = [];
        let verificationScript = [];
        if (scriptConfigSwitchFlag) {
            beforeTaskScript = $.fn.getVinScript('beforeRecoveryScriptConfig');
            afterTaskScript = $.fn.getVinScript('afterRecoveryScriptConfig');
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                verificationScript = $.fn.getVinScript('validateScriptConfig');
            }
        }
        $.each(recoveryMethod.db_config, (nodeKey, dbConfig) => {
            let selectedNode = recoverySource.time_point_list[nodeKey];
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                if (selectedNode.eventtype !== 'db') {
                    return;
                }
            }
            let isCreateDb = recoveryMethod.path_type !== PATH_TYPE_ENUM.COVER;
            encryptPassword = '';
            if (!encryptPassword) {
                encryptPassword = dbConfig.encrypt_password;
            }
            if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                data.node_uuid = dbConfig.timepoint.storage_info.node_uuid;
                data.pointInfo.points.push({
                    dbuuid: dbConfig.timepoint.db_uuid,
                    dbname: dbConfig.timepoint.db_name,
                    instanceuuid: dbConfig.timepoint.instance_name,
                    timepointuuid: dbConfig.timepoint.time_point_uuid,
                    agentuuid: dbConfig.timepoint.agent_uuid,
                    cluster_uuid: selectedNode.cluster_uuid,
                    cluster_flag: selectedNode.cluster_flag,
                    dir_path: dbConfig.timepoint.dir_path,
                    oldDbname: dbConfig.name,
                    encrypt_password: btoa(dbConfig.encrypt_password),
                    encrypt_password_clear: dbConfig.encrypt_password,
                    is_create_db: isCreateDb,
                    new_db_name: isCreateDb ? dbConfig.new_db_name : '',
                    datafile_path: '',
                    logfile_path: '',
                    is_rollback: false,
                    rollback_time: '',
                    recovery_time: dbConfig.pick_time,
                    initialize_log_area: dbConfig.initialize_log_area,
                    before_task_script: beforeTaskScript,
                    after_task_script: afterTaskScript,
                    verification_script: [],
                });
            } else {
                data.pointInfo.points.push({
                    dbuuid: '',
                    dbname: selectedNode.db_name,
                    instanceuuid: selectedNode.instance_name,
                    timepointuuid: '',
                    agentuuid: selectedNode.agent_uuid,
                    cluster_uuid: selectedNode.cluster_uuid,
                    cluster_flag: selectedNode.cluster_flag,
                    dir_path: selectedNode.dir_path,
                    oldDbname: selectedNode.db_name,
                    encrypt_password: '',
                    encrypt_password_clear: '',
                    is_create_db: isCreateDb,
                    new_db_name: dbConfig.new_db_name,
                    datafile_path: '',
                    logfile_path: '',
                    is_rollback: false,
                    rollback_time: '',
                    recovery_time: '',
                    source_agent_uuid: selectedNode.agent_uuid,
                    source_instance_name: selectedNode.instance_name,
                    source_db_name: selectedNode.db_name,
                    initialize_log_area: dbConfig.initialize_log_area,
                    before_task_script: beforeTaskScript,
                    after_task_script: afterTaskScript,
                    verification_script: verificationScript,
                });
            }
        });
        data.recoverInfo.encrypt_password_clear = encryptPassword;
        data.recoverInfo.encrypt_password = btoa(encryptPassword);
    };

    /**
     * 设置Oracle提交数据库的数据
     */
    const setOracleSubmitDBData = () => {
        data.pointInfo.points = [];
        let encryptPassword = '';
        let firstNode = Object.values(recoverySource.time_point_list)[0];
        let scriptConfigSwitchFlag = !!$('#scriptConfigSwitch').get(0).checked;
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            // 设置新建实例恢复的消息：恢复目标、恢复实例名、密码、system User
            if (recoveryTarget.create_new_instance_flag) {
                data.recoverInfo.desagentuuid = recoveryTarget.agent_uuid;
                data.recoverInfo.cluster_uuid = false;
                data.recoverInfo.cluster_flag = false;
                data.recoverInfo.instancename = recoveryMethod.create_new_instance_config.instance_name;
                data.recoverInfo.newdbname = recoveryMethod.create_new_instance_config.instance_name;
                data.recoverInfo.createflag = 1;
                data.recoverInfo.password = recoveryMethod.create_new_instance_config.sys_password;
            }
            let oracleTimepoint = null;
            let recoveryTime = '';
            switch (recoveryMethod.path_type) {
                case PATH_TYPE_ENUM.FULL:
                    oracleTimepoint = oracleTimepointList.select_timepoint;
                    if (oracleTimepoint.is_encrypted && !oracleTimepoint.config.password_auto_flag) {
                        for (const chainInfo of data.detail.recovery_timepoint_chain_info_list) {
                            if (chainInfo.chain_uuid === oracleTimepoint.chain_uuid) {
                                encryptPassword = atob(chainInfo.encrypt_password);
                            }
                        }
                    }
                    break;
                case PATH_TYPE_ENUM.INCOMPLETE:
                    oracleTimepoint = oracleTimepointList.select_timepoint;
                    if (oracleTimepoint.is_encrypted && !oracleTimepoint.config.password_auto_flag) {
                        for (const chainInfo of data.detail.recovery_timepoint_chain_info_list) {
                            if (chainInfo.chain_uuid === oracleTimepoint.chain_uuid) {
                                encryptPassword = atob(chainInfo.encrypt_password);
                            }
                        }
                    }
                    let oracleRecoveryTimepointFlag = parseInt($('#oracleRecoveryTimepoint').val());
                    if (oracleRecoveryTimepointFlag === ORACLE_RECOVERY_TIMEPOINT_ENUM.NEWEST) {
                        recoveryTime = getCurrentDatetimeStr(new Date(oracleTimepointList.select_timepoint.src_end_time_point * 1000));
                    } else if (oracleRecoveryTimepointFlag === ORACLE_RECOVERY_TIMEPOINT_ENUM.TIME) {
                        recoveryTime = oracleRecoveryTime;
                    }
                    break;
                case PATH_TYPE_ENUM.EXPORT:
                    oracleTimepoint = oracleTimepointList.select_timepoint;
                    if (oracleTimepoint.is_encrypted && !oracleTimepoint.config.password_auto_flag) {
                        for (const chainInfo of data.detail.recovery_timepoint_chain_info_list) {
                            if (chainInfo.chain_uuid === oracleTimepoint.chain_uuid) {
                                encryptPassword = atob(chainInfo.encrypt_password);
                            }
                        }
                    }
                    break;
                case PATH_TYPE_ENUM.RESTORE_ARCHIVELOG:
                    oracleTimepoint = {
                        db_uuid: '',
                        db_name: firstNode.instance_name,
                        instance_name: firstNode.instance_name,
                        time_point_uuid: '',
                        agent_uuid: firstNode.agent_uuid,
                        dir_path: firstNode.dir_path,
                    };
                    break;
            }
            data.node_uuid = oracleTimepoint.storage_info.node_uuid;
            data.pointInfo.points.push({
                dbuuid: oracleTimepoint.db_uuid,
                dbname: oracleTimepoint.db_name,
                instanceuuid: oracleTimepoint.instance_name,
                timepointuuid: oracleTimepoint.time_point_uuid,
                agentuuid: oracleTimepoint.agent_uuid,
                cluster_uuid: firstNode.cluster_uuid,
                cluster_flag: true,
                dir_path: oracleTimepoint.dir_path,
                oldDbname: oracleTimepoint.db_name,
                encrypt_password: btoa(encryptPassword),
                encrypt_password_clear: encryptPassword,
                is_create_db: false,
                new_db_name: recoveryTarget.create_new_instance_flag ? recoveryMethod.create_new_instance_config.instance_name : '',
                datafile_path: data.recoverInfo.newfilepath,
                logfile_path: data.recoverInfo.newlogpath,
                is_rollback: false,
                rollback_time: '',
                recovery_time: recoveryTime,
                initialize_log_area: false,
                latest_timepoint_uuid: oracleTimepointList.latest_timepoint_uuid,
                before_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('beforeRecoveryScriptConfig') : [],
                after_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('afterRecoveryScriptConfig') : [],
                verification_script: [],
            });
        } else {
            if (firstNode.eventtype === 'cluster') {
                data.pointInfo.points.push({
                    dbuuid: firstNode.db_uuid,
                    dbname: firstNode.app_service_name,
                    instanceuuid: firstNode.instance_name,
                    timepointuuid: '',
                    agentuuid: firstNode.agent_uuid,
                    cluster_uuid: firstNode.cluster_uuid,
                    cluster_flag: firstNode.cluster_flag,
                    dir_path: firstNode.dir_path,
                    oldDbname: firstNode.app_service_name,
                    encrypt_password: '',
                    encrypt_password_clear: '',
                    is_create_db: false,
                    new_db_name: '',
                    datafile_path: data.recoverInfo.newfilepath,
                    logfile_path: data.recoverInfo.newlogpath,
                    is_rollback: false,
                    rollback_time: '',
                    recovery_time: '',
                    source_agent_uuid: firstNode.agent_uuid,
                    source_instance_name: firstNode.instance_name,
                    source_db_name: firstNode.app_service_name,
                    initialize_log_area: false,
                    latest_timepoint_uuid: '',
                    before_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('beforeRecoveryScriptConfig') : [],
                    after_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('afterRecoveryScriptConfig') : [],
                    verification_script: scriptConfigSwitchFlag ? $.fn.getVinScript('validateScriptConfig') : [],
                });
            } else if (firstNode.eventtype === 'instance') {
                data.pointInfo.points.push({
                    dbuuid: firstNode.db_uuid,
                    dbname: firstNode.instance_name,
                    instanceuuid: firstNode.instance_name,
                    timepointuuid: '',
                    agentuuid: firstNode.agent_uuid,
                    cluster_uuid: '',
                    cluster_flag: false,
                    dir_path: firstNode.dir_path,
                    oldDbname: firstNode.instance_name,
                    encrypt_password: '',
                    encrypt_password_clear: '',
                    is_create_db: false,
                    new_db_name: '',
                    datafile_path: data.recoverInfo.newfilepath,
                    logfile_path: data.recoverInfo.newlogpath,
                    is_rollback: false,
                    rollback_time: '',
                    recovery_time: '',
                    source_agent_uuid: firstNode.agent_uuid,
                    source_instance_name: firstNode.instance_name,
                    source_db_name: firstNode.instance_name,
                    initialize_log_area: false,
                    latest_timepoint_uuid: '',
                    before_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('beforeRecoveryScriptConfig') : [],
                    after_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('afterRecoveryScriptConfig') : [],
                    verification_script: scriptConfigSwitchFlag ? $.fn.getVinScript('validateScriptConfig') : [],
                });
            }

        }
        data.recoverInfo.encrypt_password_clear = encryptPassword;
        data.recoverInfo.encrypt_password = btoa(encryptPassword);
    };

    /**
     * 设置TiDB提交数据库的数据
     */
    const setTiDBSubmitDBData = () => {
        data.pointInfo.points = [];
        let encryptPassword = '';
        let firstNode = Object.values(recoverySource.time_point_list)[0];
        let scriptConfigSwitchFlag = !!$('#scriptConfigSwitch').get(0).checked;
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            let tidbTimepoint = tidbTimepointList.select_timepoint;
            let recoveryTime = '';
            if (tidbTimepoint.is_encrypted && !tidbTimepoint.config.password_auto_flag) {
                encryptPassword = $('#encryptPassword').val().trim();
            }
            let tidbRecoveryTimepointFlag = parseInt($('#tidbRecoveryTimepoint').val());
            if (tidbRecoveryTimepointFlag === TIDB_RECOVERY_TIMEPOINT_ENUM.NEWEST) {
                recoveryTime = '';
            } else if (tidbRecoveryTimepointFlag === TIDB_RECOVERY_TIMEPOINT_ENUM.TIME) {
                recoveryTime = $('#tidbRecoveryTimeSelectTime').val();
                if (parseInt(tidbTimepoint.backup_mode) === TIMEPOINT_TYPE_ENUM.FULL) {
                    recoveryTime = getCurrentDatetimeStr(new Date(tidbTimepoint.src_end_time_point * 1000));
                }
            }
            data.node_uuid = tidbTimepoint.storage_info.node_uuid;
            data.pointInfo.points.push({
                dbuuid: tidbTimepoint.db_uuid,
                dbname: tidbTimepoint.db_name,
                instanceuuid: tidbTimepoint.instance_name,
                timepointuuid: tidbTimepoint.time_point_uuid,
                agentuuid: tidbTimepoint.agent_uuid,
                cluster_uuid: firstNode.cluster_uuid,
                cluster_flag: true,
                dir_path: tidbTimepoint.dir_path,
                oldDbname: tidbTimepoint.db_name,
                encrypt_password: btoa(encryptPassword),
                encrypt_password_clear: encryptPassword,
                is_create_db: false,
                new_db_name: '',
                datafile_path: '',
                logfile_path: '',
                is_rollback: !!recoveryTime.length,
                rollback_time: recoveryTime,
                recovery_time: recoveryTime,
                initialize_log_area: false,
                latest_timepoint_uuid: '',
                before_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('beforeRecoveryScriptConfig') : [],
                after_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('afterRecoveryScriptConfig') : [],
                verification_script: [],
            });
        } else {
            data.pointInfo.points.push({
                dbuuid: '',
                dbname: firstNode.instance_name,
                instanceuuid: firstNode.instance_name,
                timepointuuid: '',
                agentuuid: firstNode.agent_uuid,
                cluster_uuid: firstNode.cluster_uuid,
                cluster_flag: firstNode.cluster_flag,
                dir_path: firstNode.dir_path,
                oldDbname: firstNode.instance_name,
                encrypt_password: '',
                encrypt_password_clear: '',
                is_create_db: false,
                new_db_name: '',
                datafile_path: '',
                logfile_path: '',
                is_rollback: false,
                rollback_time: '',
                recovery_time: '',
                source_agent_uuid: firstNode.agent_uuid,
                source_instance_name: firstNode.instance_name,
                source_db_name: firstNode.instance_name,
                initialize_log_area: false,
                latest_timepoint_uuid: '',
                before_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('beforeRecoveryScriptConfig') : [],
                after_task_script: scriptConfigSwitchFlag ? $.fn.getVinScript('afterRecoveryScriptConfig') : [],
                verification_script: scriptConfigSwitchFlag ? $.fn.getVinScript('validateScriptConfig') : [],
            });
        }
        data.recoverInfo.encrypt_password_clear = encryptPassword;
        data.recoverInfo.encrypt_password = btoa(encryptPassword);
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
    const setRecoveryScriptName = () => {
        for (const index in data.pointInfo.points) {
            let point = data.pointInfo.points[index];
            for (const j in point.before_task_script) {
                if (!point.before_task_script[j].script_name.length) {
                    point.before_task_script[j].script_name = getScriptName(point.before_task_script);
                }
            }
            for (const j in point.after_task_script) {
                if (!point.after_task_script[j].script_name.length) {
                    point.after_task_script[j].script_name = getScriptName(point.after_task_script);
                }
            }
            for (const j in point.verification_script) {
                if (!point.verification_script[j].script_name.length) {
                    point.verification_script[j].script_name = getScriptName(point.verification_script);
                }
            }
            data.pointInfo.points[index] = point;
        }
    };

    var submit = function () {
        let taskName = $.trim($("#jobname").val());
        if (!taskName) {
            $('.jobnametip').html(LANG.UI_RECOVERY_RENAME).show();
            return;
        }
        $('.jobnametip').hide();
        let jobName = $.trim($("#jobname").val());
        // 输入验证
        if (!customInputValidate('string', jobName)) {
            return false;
        }
        data.taskName = taskName;
        if (recoverySource.db_type === CONF.DB_TYPE.SQLSERVER) {
            setSqlServerSubmitDBData();
        } else if (recoverySource.db_type === CONF.DB_TYPE.SAPHANA) {
            setSAPHANASubmitDBData();
        } else if (recoverySource.db_type === CONF.DB_TYPE.ORACLE) {
            setOracleSubmitDBData();
        } else if (recoverySource.db_type === CONF.DB_TYPE.TIDB) {
            setTiDBSubmitDBData();
        }
        // 设置脚本名称
        setRecoveryScriptName();
        if (editTaskFlag) {
            editDBRecoveryJob(data);
        } else {
            createDBRecoveryJob(data);
        }
    }

    /**
     * 获取数据库恢复任务提交数据
     * @param data
     * @return {Object}
     */
    const getDBRecoveryJobData = data => {
        let speedStrategyType = 1;
        if (parseInt(data.speedLimit.type) === 1) {
            if (data.speedLimit.speedInfo.length) {
                speedStrategyType = data.speedLimit.speedInfo[0].type;
            }
        }
        return {
            task_name: data.taskName,
            db_type: recoverySource.db_type,
            recovery_source_type: recoverySource.recovery_source_type,
            recovery_type: parseInt($('#pathType').val()),
            recovery_time_flag: data.recovery_time_flag,
            node_uuid: data.node_uuid,
            time_strategy: {
                type: data.timeInfo.type,
                strategy: {
                    mode: data.timeInfo.strategy.mode,
                    type: data.timeInfo.strategy.type,
                    start_time: data.timeInfo.strategy.startTime,
                    days: data.timeInfo.strategy.days,
                },
            },
            recovery_target: {
                agent_uuid: recoveryTarget.agent_uuid,
                instance_name: recoveryTarget.instance_uuid,
                cluster_flag: recoveryTarget.cluster_flag,
                cluster_uuid: recoveryTarget.cluster_uuid,
            },
            speed_strategy: {
                level: data.speedLimit.level,
                type: data.speedLimit.type,
                uuid: data.speedLimit.uuid,
                name: data.speedLimit.name,
                strategy_type: speedStrategyType,
                speedInfo: data.speedLimit.speedInfo,
            },
            transfer_strategy: {
                encrypt_flag: data.highInfo.transfer.encrypt,
                encrypt_method: data.highInfo.transfer.encrypt_method,
                transport_mode: 1,
                network_uuid: data.highInfo.transfer.network,
                network_pool_uuid: data.highInfo.transfer.network_pool_uuid,
                thread_num: data.highInfo.threadnum,
                channel_count: data.highInfo.threadnum,
                max_object_transport_parallel_nums: data.recoverInfo.max_object_transport_parallel_nums,
            },
            recovery_source: data.pointInfo.points.map(dbInfo => {
                return {
                    instance_name: data.recoverInfo.instancename,
                    old_db_name: dbInfo.oldDbname,
                    db_uuid: dbInfo.dbuuid,
                    cluster_flag: dbInfo.cluster_flag,
                    cluster_uuid: dbInfo.cluster_uuid,
                    timepoint_uuid: dbInfo.timepointuuid,
                    encrypt_password: dbInfo.encrypt_password,
                    encrypt_password_clear: dbInfo.encrypt_password_clear,
                    new_db_password: data.recoverInfo.password,
                    new_db_name: dbInfo.new_db_name,
                    datafile_path: dbInfo.datafile_path,
                    logfile_path: dbInfo.logfile_path,
                    rollback_flag: dbInfo.is_rollback,
                    rollback_time: dbInfo.rollback_time,
                    source_agent_uuid: dbInfo.source_agent_uuid,
                    source_instance_name: dbInfo.source_instance_name,
                    source_db_name: dbInfo.source_db_name,
                    recovery_time: dbInfo.recovery_time,
                    open_db_flag: data.recoverInfo.open_db_flag,
                    initialize_log_area: dbInfo.initialize_log_area,
                    latest_timepoint_uuid: dbInfo.latest_timepoint_uuid,
                    log_restore_start_time: data.recoverInfo.log_restore_start_time,
                    log_restore_end_time: data.recoverInfo.log_restore_end_time,
                    detail: data.detail,
                    before_task_script: dbInfo.before_task_script,
                    after_task_script: dbInfo.after_task_script,
                    verification_script: dbInfo.verification_script,
                };
            }),
            retry_strategy: data.retry_strategy,
            safe_config_strategy: data.safe_config_strategy,
            ignore_resource_limiting_flag: data.ignore_resource_limiting_flag,
        };
    };

    /**
     * 修改数据库恢复任务
     * @param {*} data
     */
    const editDBRecoveryJob = data => {
        data.task_uuid = $('#externalTaskUuid').val();
        let reqData = getDBRecoveryJobData(data);
        Metronic.blockUI({target: '#dbrecovercontent', animate: true, cenrerY: true});
        pAjaxRequest(reqData, `/api/v1/db/jobs/${data.task_uuid}/recovery`, 'PUT', res => {
            Metronic.unblockUI('#dbrecovercontent');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_RECOVERY_DB_PROTECT_EDIT_DRILL_TITLE, res.message);
                return;
            }
            UIToastr.showSuccess(LANG.UI_RECOVERY_DB_PROTECT_EDIT_DRILL_TITLE, res.message);
            LOCATION('./content/platform/jobs/jobs.php', 'task');
        });
    };

    /**
     * 创建数据库恢复任务
     * @param {*} data
     */
    const createDBRecoveryJob = data => {
        let reqData = getDBRecoveryJobData(data);
        Metronic.blockUI({target: '#dbrecovercontent', animate: true, cenrerY: true});
        pAjaxRequest(reqData, `/api/v1/db/jobs/recovery`, 'POST', res => {
            Metronic.unblockUI('#dbrecovercontent');
            let title = LANG.UI_RECOVERY_DB_PROTECT_CREATE_TITLE;
            let recoverySourceType = parseInt($('#externalTimepointRecoveryType').val());
            if (recoverySourceType === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {
                title = LANG.UI_RECOVERY_DB_PROTECT_CREATE_DRILL_TITLE;
            }
            if (!res.success) {
                UIToastr.showWarning(title, res.message);
                return;
            }
            UIToastr.showSuccess(title, res.message);
            LOCATION('./content/platform/jobs/jobs.php', 'task');
        });
    };

    const initDbTypeSelect = () => {
        let reqData = {
            is_auth_db: true,
            is_client_online: true,
        };
        pAjaxRequest(reqData, `/api/v1/db/type`, 'GET', res => {
            if (!res.success) {
                return;
            }
            if (!res.data.rows.length) {
                $('#noSourceAgentTips').show();
                $('#dbTypeSelect').hide();
                $('.vm_tree_div').hide();
            } else {
                $('#dbTypeSelect').show();
                $('.vm_tree_div').show();
            }
            let options = ``;
            for (const item of res.data.rows) {
                options += `<option value="${item.type}">${item.name}</option>`;
            }
            $('#dbTypeSelect').html(options).on('change', initSourceAgentTree);
            initSourceAgentTree();
        });
    };

    /**
     * 初始化时间点数据库类别
     */
    const initPointDbTypeSelect = () => {
        let reqData = {
            timepoint_flag: true,
            timepoint_node_uuid: '',
        };
        Metronic.blockUI({target: '#tab1 .tab-pane__row__source', animate: true});
        pAjaxRequest(reqData, `/api/v1/db/type`, 'GET', res => {
            Metronic.unblockUI('#tab1 .tab-pane__row__source');
            if (!res.success) {
                return;
            }
            if (!res.data.rows.length) {
                $('#nopointtips').show();
                $('#pointDbTypeSelect').hide();
                $('#timepointStorageSelect').hide();
                $('.vm_tree_div').hide();
            } else {
                $('#pointDbTypeSelect').show();
                $('#timepointStorageSelect').show();
                $('.vm_tree_div').show();
            }
            let options = ``;
            for (const item of res.data.rows) {
                options += `<option value="${item.type}">${item.name}</option>`;
            }
            $('#pointDbTypeSelect').html(options);

            initTimepointStorageSelect();
        });
        $('#pointDbTypeSelect').on('change', () => {
            initTimepointStorageSelect();
            $('#VMGroupList').html('');
            $('#searchvm').val('');
            recoverySource.time_point_list = {};  // 切换节点，设置为
            recoverySource.timepoint_encrypt_password_map = {};
        });
    };

    /**
     * 获取存储设备显示名称
     * @param {*} storage_type
     * @param {*} storage_nickname
     * @param {*} storage_online_flag
     * @param {*} node_ip
     * @param {*} node_hostname
     * @param {*} node_nickname
     * @return {string}
     */
    const getStorageShowName = ({storage_type, storage_nickname, storage_online_flag, node_ip, node_hostname, node_nickname}) => {
        let shareStorageType = [
            CONF.BD_STORAGE_TYPE.NFS,
            CONF.BD_STORAGE_TYPE.CIFS,
            CONF.BD_STORAGE_TYPE.CLOUD,
        ];
        let storageName = storage_nickname;
        if (shareStorageType.includes(parseInt(storage_type))) {
            storageName += `(${CONF.STORAGE_TYPE_DES[storage_type]})`;
        } else {
            storageName += `(${CONF.STORAGE_TYPE_DES[storage_type]}, ${LANG.UI_NODE_DES}: ${node_hostname}(${node_ip}))`;
        }
        if (!storage_online_flag) {
            storageName = `(${LANG.UI_STORAGE_STATUS_OFFLINE})` + storageName;
        }
        return storageName;
    };

    /**
     * 初始化备份点存储下拉框
     */
    const initTimepointStorageSelect = () => {
        let dbType = parseInt($('#pointDbTypeSelect').val());
        let reqData = {
            db_type: dbType,
        };
        Metronic.blockUI({target: '#tab1 .tab-pane__row__source', animate: true});
        pAjaxRequest(reqData, `/api/v1/db/jobs/backup/storages`, 'GET', res => {
            Metronic.unblockUI('#tab1 .tab-pane__row__source');
            if (!res.success) {
                return;
            }
            let options = ``;
            if (dbType !== CONF.DB_TYPE.ORACLE) {
                options += `<option value="">${LANG.UI_STORAGE_ALL_STORAGE}</option>`;
            }
            if (!res.data.rows.length) {
                $('#nopointtips').show();
                $('#timepointStorageSelect').hide();
                $('.vm_tree_div').hide();
            } else {
                $('#timepointStorageSelect').show();
                $('.vm_tree_div').show();
            }
            for (const item of res.data.rows) {
                let disabled = '';
                if (!item.storage_online_flag) {
                    disabled = 'disabled';
                }
                options += `<option value="${item.storage_uuid}" ${disabled} data-storage-type="${item.storage_type}">${getStorageShowName(item)}</option>`;
            }
            $('#timepointStorageSelect').html(options);

            initClusterInstancePointTree();
        });
        $('#timepointStorageSelect').on('change', () => {
            initClusterInstancePointTree();
            $('#VMGroupList').html('');
            $('#searchvm').val('');
            recoverySource.time_point_list = {};  // 切换节点，设置为
            recoverySource.timepoint_encrypt_password_map = {};
        });
    };

    //搜索虚拟机
    var searchVM = function () {
        var value = $('#searchvm').val();
        // 输入验证
        if (!customInputValidate('string', value)) {
            return false;
        }
        hideStep1Tips();
        if (!pointtypetree) {
            if (parseInt($('#recoverySourceTypeSelect').val()) === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                $('#nosearchpointtips').show();
            } else {
                $('#noSearchAgentTips').show();
            }
            return;
        }
        var checkNode = pointtypetree.getCheckedNodes();
        var allNode = pointtypetree.transformToArray(pointtypetree.getNodes());
        nodeParamList = pointtypetree.getNodesByParamFuzzy('name', value);
        pointtypetree.hideNodes(allNode);  // 隐藏所有节点
        //连接搜索的和所勾选的
        nodeParamList = nodeParamList.concat(checkNode);
        var nodeParamList1 = pointtypetree.transformToArray(nodeParamList);
        for (var n in nodeParamList1) {
            findParent(pointtypetree, nodeParamList1[n]);
        }
        if (!nodeParamList.length) {
            if (parseInt($('#recoverySourceTypeSelect').val()) === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                $('#nosearchpointtips').show();
            } else {
                $('#noSearchAgentTips').show();
            }
        }
        pointtypetree.showNodes(nodeParamList);
    }

    //找到父节点
    var findParent = function (treeObj, node) {
        pointtypetree.expandNode(node, true, false, false);
        if (!node.children) {
            nodeParamList.push(node);
            pointtypetree.expandNode(node, false, false, false);
        }
        var pNode = node.getParentNode();
        if (pNode != null) {
            nodeParamList.push(pNode);
            findParent(pointtypetree, pNode);
        }
    }

    //初始化时间策略
    var initStrategy = function () {
        $('#backupStrategyDiv').initBackupStrategy(defaultConfig);
    }

    var getUuid = function () {
        var len = 36; //36长度
        var radix = 16; //16进制
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
                    uuid[i] = chars[(i === 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    }

    //获取速度单位换算大小
    var getSpeedUnit = function () {
        var type = parseInt($('#unit').val());
        var unit;
        switch (type) {
            case 1:
                unit = 1024;
                break;
            case 2:
                unit = 1024 * 1024;
                break;
            case 3:
                unit = 1024 * 1024 * 1024;
                break;
        }

        return unit;
    }

    var initSpinner = function () {
        $('#speedSpinnerNum').spinner({value: 10, step: 5, min: 1, max: 10000000000});
        $('#recoveryThreadDiv').spinner({value: 4, step: 1, min: 1, max: 32});
        $('#mongodbRecoveryThreadDiv').spinner({value: 1, step: 1, min: 1, max: 16});
        $('#reconnectTimesDiv').spinner({value: 60, step: 1, min: 1, max: 999});
        $('#reconnectIntervalDiv').spinner({value: 30, step: 5, min: 5, max: 60});
        $('#parallelNumSpinner').spinner({value: 3, step: 1, min: 1, max: 9999999});
    }

    /**
     * 格式化时间选择
     * @param {String} timePicker
     * @param {String} timePickerValue
     * @param {Function} callback
     * @param {Function} cancel
     */
    var initDatetimePicker = function (timePicker = '#rollbackFormDatetime', timePickerValue = '#logtime', callback = null, cancel = null) {
        let dateLocale = DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE);
        dateLocale.format = 'YYYY-MM-DD HH:mm:ss';  // 这里有秒
        //初始化日期时间选择控件
        $(timePicker).daterangepicker({
            "autoUpdateInput": false, //是否自动填充input
            "startDate": moment().subtract('days').startOf('day'), //默认开始时间
            "endDate": moment({
                hour: 23,
                minute: 59
            }),
            "maxDate": moment({
                hour: 23,
                minute: 59
            }), //最大可用时间
            singleDatePicker: true,
            showDropdowns: false,
            opens: 'right',
            timePicker: true, //是否显示时间,时分
            timePicker24Hour: true, //是否是24小时制
            timePickerSeconds: true,
            alwaysShowCalendars: true, //是否总是显示日期选择
            drops: 'auto',  // 自动设置显示位置
            ranges: DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE), //根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            locale: dateLocale, //根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function (start, end, label) {
            //			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
        });

        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        $(timePicker).on('apply.daterangepicker', function (ev, picker) {
            //给全局变量赋值,然后设置input
            $(timePickerValue).val('' + picker.startDate.format('YYYY-MM-DD HH:mm:ss') + '');
            if (!callback) {
                return;
            }
            callback();
        });

        $(timePicker).on('cancel.daterangepicker', function (ev, picker) {
            //清除全局变量,然后设置input
            $(timePickerValue).val('');
            if (cancel) {
                cancel();
            }
        });
    }

    //获取显示传输网络标志
    var getNetworkFlag = function (agent) {
        if (recoverySource.recovery_source_type === RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST) {  // 定时恢复最新点没有传输网络
            return false;
        }
        for (let i = 0; i < agent.length; i++) {
            if (agent[i].eventtype === 'instance') {
                if (parseInt(agent[i].net_model) === 2) {
                    return true;
                }
            } else if (agent[i].eventtype === 'cluster') {
                let children = agent[i].children;
                for (const child of children) {
                    if (parseInt(child.net_model) === 2) {
                        return false;
                    }
                }
            } else if (agent[i].eventtype === 'new instance agent') {
                if (parseInt(agent[i].net_model) === 2) {
                    return true;
                }
            }
        }

        return false;
    }

    //初始化节点传输网络列表
    var initNetworkList = function () {
        $('#transferNetworkTree').transferNetwork({
            node_uuid: data.pointInfo.points[0].node_uuid,
            onChange: function (transferNetworkNode) {
                if (!transferNetworkNode.checked) {
                    return;
                }
            }
        });
    }

    /**
     * 初始化用户密码
     */
    const initUserPassword = () => {
        pAjaxRequest({}, `/api/v1/users/password`, 'GET', res => {
            if (res.success) {
                _UserPassword = res.data.password;
            }
        });
    };

    /**
     * 初始化恢复脚本
     */
    const initRecoveryScript = () => {
        $.fn.initVinScript({
            class: 'beforeRecoveryScriptConfig',
        });
        $.fn.initVinScript({
            class: 'afterRecoveryScriptConfig',
        });
        $.fn.initVinScript({
            class: 'validateScriptConfig',
            include_list: [6],
        });
    };

    /**
     * 初始化重试策略
     */
    const initRetryStrategy = () => {
        if (editTaskFlag) {  // 修改任务
            $('#tab_advanced_retry').retryStrategy({'retry_strategy': oldRecoveryTaskData.retry_strategy}, 'edit');
        } else {
            $('#tab_advanced_retry').retryStrategy();
        }
    };

    /**
     * 初始化安全策略
     */
    const initSafeStrategy = () => {
        // 完整性校验
        if (editTaskFlag) {  // 修改任务
            $('#integrityCheck').completeStrategyCovery(
                CONF.MODULE_TYPE.DB,
                oldRecoveryTaskData.safe_config_strategy.integrity_check_config.recovery_error_policy
            );
        } else {
            $('#integrityCheck').completeStrategyCovery(CONF.MODULE_TYPE.DB);
        }
    };

    /**
     * 获取旧的恢复任务数据
     */
    const getOldRecoveryTaskData = () => {
        Metronic.blockUI({target: '#dbrecovercontent', animate: true});
        pAjaxRequest({}, `/api/v1/db/jobs/${$('#externalTaskUuid').val()}/recovery`, 'GET', res => {
            Metronic.unblockUI('#dbrecovercontent');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_RECOVERY_DB_PROTECT_EDIT_TITLE, res.message);
                return;
            }
            if (parseInt(res.data.recovery_info.recovery_source_type) === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                editTaskFlag = false;
                return;
            }
            oldRecoveryTaskData = res.data;
            $('#dbTypeSelect').val(oldRecoveryTaskData.db_type).attr('disabled', 'disabled');
            // $('#recoverySourceTypeSelect').val(RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST).attr('disabled', 'disabled').trigger('change');
            initRetryStrategy();
            initSafeStrategy();
            $('#recoverySourceTypeSelect').trigger('change');  // 获取到旧数据后才刷新页面
        });
    };

    /**
     * 初始化旧数据
     */
    const initOldData = () => {
        if (parseInt($('#externalEditFlag').val())) {
            editTaskFlag = true;
            $('#addRecoveryTaskTitle').hide();
            $('#addDrillTaskTitle').hide();
            $('#modifyRecoveryTaskTitle').show();
        } else {
            $('#modifyRecoveryTaskTitle').hide();
            let recoverySourceType = parseInt($('#externalTimepointRecoveryType').val());
            if (recoverySourceType === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
                $('#addRecoveryTaskTitle').show();
                $('#addDrillTaskTitle').hide();
            } else {
                $('#addRecoveryTaskTitle').hide();
                $('#addDrillTaskTitle').show();
            }
            initRetryStrategy();
            initSafeStrategy();
            return;
        }
        let editInterval = setInterval(() => {
            clearInterval(editInterval);
            // 禁用
            getOldRecoveryTaskData();
        }, 100);
    };

    ///////////////////// --------- 开始-新实例配置 ---------- /////////////////////

    /**
     * 验证新实例配置
     */
    const validateNewInstanceConfig = () => {
        let instanceName = $('#newInstanceName').val().trim();
        if (!instanceName) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_REQUIRE_NAME);
            return {
                result: false,
                change_instance_flag: true,
            };
        }
        if (instanceName.length > 12) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_NAME, LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_NAME_TIPS1);
            return {
                result: false,
                change_instance_flag: true,
            };
        }
        if (/[^a-zA-Z0-9]/.test(instanceName)) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_NAME, LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_NAME_TIPS2);
            return {
                result: false,
                change_instance_flag: true,
            };
        }
        if (!/^[a-zA-Z].*/.test(instanceName)) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_NAME, LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_NAME_TIPS3);
            return {
                result: false,
                change_instance_flag: true,
            };
        }
        let changeInstanceFlag = true;
        if (recoveryMethod.create_new_instance_config.instance_name === instanceName) {
            changeInstanceFlag = false;
        }

        // 验证新实例名称是否重复, 与存在的所有实例比较
        let checkNodes = createNewInstanceHostTree.getCheckedNodes();
        if (checkNodes[0].instance_name_list.includes(instanceName)) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_SAME_NAME.replace('%S%', checkNodes[0].instance_name_list.join('、')));
            return {
                result: false,
                change_instance_flag: true,
            };
        }

        recoveryMethod.create_new_instance_config.sys_password = $('#newInstanceSysPassword').val().trim();
        if (!judgeSysPassword(recoveryMethod.create_new_instance_config.sys_password, LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, false)) {
            return {
                result: false,
                change_instance_flag: true,
            };
        }
        recoveryMethod.create_new_instance_config.re_sys_password = $('#newInstanceReSysPassword').val().trim();
        if (recoveryMethod.create_new_instance_config.sys_password !== recoveryMethod.create_new_instance_config.re_sys_password) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_DIFF_SYS_PASSWORD);
            return {
                result: false,
                change_instance_flag: true,
            };
        }
        recoveryMethod.create_new_instance_config.system_user = $('#newInstanceSystemUser').val().trim();
        if (!recoveryMethod.create_new_instance_config.system_user) {
            UIToastr.showWarning(LANG.UI_DB_RECOVERY_INPUT_CONFIG_INFO, LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_REQUIRE_SYSTEM_USER);
            return {
                result: false,
                change_instance_flag: true,
            };
        }
        recoveryMethod.create_new_instance_config.instance_name = instanceName;
        return {
            result: true,
            change_instance_flag: changeInstanceFlag,
        };
    };

    /**
     * 渲染spfile参数项
     */
    const renderSpfileConfig = spfileConfigList => {
        let spfileHtml = ``;
        for (const configItem of spfileConfigList) {
            let allowDelete = '';
            let showValue = configItem.show_value;
            if (configItem.multi_value_flag) {
                allowDelete = 'allow-delete';
                showValue = configItem.multi_config_value_list[0].show_value;
            }
            let asterisk = configItem.asterisk_flag ? 'asterisk' : '';
            let singleQuote = configItem.single_quote_flag ? 'singleQuote' : '';
            let doubleQuote = configItem.double_quote_flag ? 'doubleQuote' : '';
            spfileHtml += `
            <div class="form-group ${allowDelete} old-config-group"
                data-asterisk="${asterisk}" data-single-quote="${singleQuote}" data-double-quote="${doubleQuote}">
                <label class="col-md-3 control-label config-label">${configItem.show_name}</label>
                <div class="col-md-8 config-item__wrapper">
                    <div class="config-item">
                        <input type="text" class="form-control config-value" name="${configItem.show_name}" value="${showValue}"
                            placeholder="${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_SPFILE_VALUE_PLACEHOLDER}" />
                    </div>
                    <div class="config-tips">
                        <span class=""></span><!-- 提示信息 -->
                    </div>
                </div>
            </div>
            `;
            if (configItem.multi_value_flag) {
                for (let index in configItem.multi_config_value_list) {
                    index = parseInt(index);
                    if (index === 0) {
                        continue;
                    }
                    spfileHtml += `
                    <div class="form-group allow-delete new-config-value-group"
                        data-asterisk="${asterisk}" data-single-quote="${singleQuote}" data-double-quote="${doubleQuote}">
                        <label class="col-md-3 control-label config-label"></label>
                        <div class="col-md-8 config-item__wrapper">
                            <div class="config-item">
                                <input type="text" class="form-control config-value" name="${configItem.show_name}" value="${configItem.multi_config_value_list[index].show_value}"
                                    placeholder="${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_SPFILE_VALUE_PLACEHOLDER}" />
                                <div class="delete-icon__wrapper delete-new-config-value-btn">
                                    <i class="viconfont vicon-a-Reduce-onejianshao delete-icon"></i>
                                </div>
                            </div>
                            <div class="config-tips">
                                <span class=""></span><!-- 提示信息 -->
                            </div>
                        </div>
                    </div>
                    `;
                }
                spfileHtml += `
                <div class="form-group ${allowDelete} add-config-value-group"
                    data-asterisk="${asterisk}" data-single-quote="${singleQuote}" data-double-quote="${doubleQuote}">
                    <label class="col-md-3 control-label config-label"></label>
                    <div class="col-md-8 config-item__wrapper">
                        <a class="addMoreConfigValue" data-config-name="${configItem.show_name}">
                            <i class="viconfont vicon-zhediekuanganniu"></i>
                            <span>${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_ADD_SPFILE_CONTENT}</span>
                        </a>
                    </div>
                </div>
                `;
            }
        }
        spfileHtml += `
        <div class="form-group add-config-group"
            data-asterisk="" data-single-quote="" data-double-quote="">
            <label class="col-md-3 control-label"></label>
            <div class="col-md-8">
                <a class="addMoreConfig">
                    <i class="viconfont vicon-zhediekuanganniu"></i>
                    <span>${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_ADD_SPFILE}</span>
                </a>
            </div>
        </div>
        `;
        $('#spfileConfigWrapper').html(spfileHtml);
    };

    /**
     * 注册spfile参数项事件
     */
    const registerSpfileConfigEvent = () => {
        // 添加新的参数值
        $('.addMoreConfigValue').on('click', function () {
            let configName = $(this).data('config-name').trim();
            let parentTag = $(this).closest('.form-group');
            let asterisk = parentTag.data('asterisk');
            let singleQuote = parentTag.data('single-quote');
            let doubleQuote = parentTag.data('double-quote');
            let configValueHtml = `
            <div class="form-group allow-delete new-config-value-group"
                data-asterisk="${asterisk}" data-single-quote="${singleQuote}" data-double-quote="${doubleQuote}">
                <label class="col-md-3 control-label config-label"></label>
                <div class="col-md-8 config-item__wrapper">
                    <div class="config-item">
                        <input type="text" class="form-control config-value" name="${configName}" placeholder="${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_SPFILE_VALUE_PLACEHOLDER}" />
                        <div class="delete-icon__wrapper delete-new-config-value-btn">
                            <i class="viconfont vicon-a-Reduce-onejianshao delete-icon"></i>
                        </div>
                    </div>
                    <div class="config-tips">
                        <span class=""></span><!-- 提示信息 -->
                    </div>
                </div>
            </div>
            `;
            parentTag.before(configValueHtml);
        });
        // 添加新的配置项
        $('.addMoreConfig').on('click', function () {
            let parentTag = $(this).closest('.form-group');
            let asterisk = parentTag.data('asterisk');
            let singleQuote = parentTag.data('single-quote');
            let doubleQuote = parentTag.data('double-quote');
            let configHtml = `
            <div class="form-group allow-delete new-config-group"
                data-asterisk="${asterisk}" data-single-quote="${singleQuote}" data-double-quote="${doubleQuote}">
                <div class="col-md-3 config-label">
                    <input type="text" class="form-control config-name" placeholder="${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_SPFILE_NAME_PLACEHOLDER}"  />
                </div>
                <div class="col-md-8 config-item__wrapper">
                    <div class="config-item">
                        <input type="text" class="form-control config-value" placeholder="${LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_SPFILE_VALUE_PLACEHOLDER}" />
                        <div class="delete-icon__wrapper delete-new-config-btn">
                            <i class="viconfont vicon-a-Reduce-onejianshao delete-icon"></i>
                        </div>
                    </div>
                    <div class="config-tips">
                        <span class=""></span><!-- 提示信息 -->
                    </div>
                </div>
            </div>
            `;
            parentTag.before(configHtml);
        });
        // 删除新的参数值
        $('#spfileConfigWrapper').on('click', '.delete-new-config-value-btn', function () {
            $(this).closest('.form-group').remove();
        });
        // 删除新的配置项
        $('#spfileConfigWrapper').on('click', '.delete-new-config-btn', function () {
            $(this).closest('.form-group').remove();
        });
    };

    /**
     * 初始化spfile参数项
     */
    const initSpfileConfig = () => {
        let reqData = {
            timepoint_uuid: oracleTimepointList.select_timepoint.time_point_uuid,
            new_instance_name: recoveryMethod.create_new_instance_config.instance_name,
            oracle_home_path: $('#createNewInstanceConfigModal').data('oracle-home'),
            oracle_base_path: $('#createNewInstanceConfigModal').data('oracle-base'),
            db_type: CONF.DB_TYPE.ORACLE,
        };
        Metronic.blockUI({target: '#createNewInstanceConfigModal', animate: true});
        pAjaxRequest(reqData, `/api/v1/db/config/file_parse`, 'GET', res => {
            Metronic.unblockUI('#createNewInstanceConfigModal');
            if (!res.success) {
                UIToastr.showWarning(LANG.UI_DB_RECOVERY_CREATE, res.message);
                return;
            }
            renderSpfileConfig(res.data.spfile_config_list);
            registerSpfileConfigEvent();
        });
    };

    /**
     * 验证创建新实例的第一步
     */
    const validCreateNewInstanceStep1 = () => {
        let validResult = validateNewInstanceConfig();
        if (!validResult.result) {
            return false;
        }
        if (validResult.change_instance_flag) {
            unsetCreateNewInstanceConfigFlag();
            // 初始化spfile参数项
            initSpfileConfig();
        }
        return true;
    };

    /**
     * 取消设置创建新实例的标识
     */
    const unsetCreateNewInstanceConfigFlag = () => {
        $('.createNewInstanceConfigDiv .new-instance-config-item__result-configured').hide();
        $('.createNewInstanceConfigDiv .new-instance-config-item__result-unconfigured').show();
        recoveryMethod.create_new_instance_config.config_create_new_instance_flag = false;
    };

    /**
     * 设置创建新实例的标识
     */
    const setCreateNewInstanceConfigFlag = () => {
        $('.createNewInstanceConfigDiv .new-instance-config-item__result-configured').show();
        $('.createNewInstanceConfigDiv .new-instance-config-item__result-unconfigured').hide();
        recoveryMethod.create_new_instance_config.config_create_new_instance_flag = true;
    };

    /**
     * 验证创建新实例的第二步
     */
    const validCreateNewInstanceStep2 = () => {
        $('#spfileConfigWrapper .form-group .config-tips span').removeClass('error').html(``);
        let configElementList = $('#spfileConfigWrapper .form-group');
        for (const configElement of configElementList) {
            if ($(configElement).hasClass('old-config-group')) {  // 旧配置
                let asteriskFlag = $(configElement).data('asterisk');
                let singleQuoteFlag = $(configElement).data('single-quote');
                let doubleQuoteFlag = $(configElement).data('double-quote');
                if ($(configElement).hasClass('allow-delete')) {  // 多参数配置
                    let configName = $(configElement).find('label.config-label').html().trim();
                    let configValueElementList = $(`#spfileConfigWrapper input.config-value[name="${configName}"]`);
                    let configValueList = [];
                    for (const configValueElement of configValueElementList) {
                        let configValue = $(configValueElement).val().trim();
                        if (singleQuoteFlag) {
                            configValue = `'${configValue}'`;
                        } else if (doubleQuoteFlag) {
                            configValue = `"${configValue}"`;
                        }
                        configValueList.push(configValue);
                    }
                    if (asteriskFlag) {
                        configName = '*.' + configName;
                    }
                    recoveryMethod.create_new_instance_config.spfile_content.push(`${configName}=${configValueList.join(',')}`);
                } else {
                    let configName = $(configElement).find('label.config-label').html().trim();
                    let configValue = $(configElement).find('input.config-value').val().trim();
                    if (asteriskFlag) {
                        configName = '*.' + configName;
                    }
                    if (singleQuoteFlag) {
                        configValue = `'${configValue}'`;
                    } else if (doubleQuoteFlag) {
                        configValue = `"${configValue}"`;
                    }
                    recoveryMethod.create_new_instance_config.spfile_content.push(`${configName}=${configValue}`);
                }
            } else if ($(configElement).hasClass('new-config-group')) {  // 新配置
                let configName = $(configElement).find('input.config-name').val().trim();
                let configValue = $(configElement).find('input.config-value').val().trim();
                if (!configName) {  // 新配置的参数名不能为空
                    $(configElement).find('.config-tips span').addClass('error').html(LANG.UI_DB_RECOVERY_ORACLE_NEW_INSTACNE_SPFILE_NAME_PLACEHOLDER);
                    return false;
                }
                configName = '*.' + configName;
                recoveryMethod.create_new_instance_config.spfile_content.push(`${configName}='${configValue}'`);
            } else {
                continue;
            }
        }

        setCreateNewInstanceConfigFlag();
        return true
    };

    /**
     * 创建新实例的向导标题
     */
    const createNewInstanceHandlerTitle = (tab, navigation, index) => {
        let total = navigation.find('li').length; //总共的步骤数
        let current = index + 1; //当前步骤
        // set wizard title
        //            $('.step-title', $('#vmbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
        // set done steps
        jQuery('li', $('#createNewInstanceConfigModal')).removeClass("done");
        let li_list = navigation.find('li');
        for (let i = 0; i < index; i++) {
            jQuery(li_list[i]).addClass("done");
        }

        //如果第一步 上一步按钮隐藏
        if (current === 1) {
            $('#createNewInstanceConfigModal').find('.button-previous').hide();
            $('#createNewInstanceConfigModal').find('.button-next').addClass('next-btn-margin-left');
        } else {
            $('#createNewInstanceConfigModal').find('.button-previous').show();
            $('#createNewInstanceConfigModal').find('.button-next').removeClass('next-btn-margin-left');
        }

        //如果是最后一步
        if (current >= total) {
            $('#createNewInstanceConfigModal').find('.button-next').hide();
            $('#createNewInstanceConfigModal').find('.button-submit').show();
        } else {
            $('#createNewInstanceConfigModal').find('.button-next').show();
            $('#createNewInstanceConfigModal').find('.button-submit').hide();
        }

        Metronic.scrollTo($('.page-title'));
    };

    /**
     * 参数化创建新实例的向导
     */
    const initCreateNewInstanceWizard = () => {
        $('#createNewInstanceConfigModal').bootstrapWizard({
            'nextSelector': '.button-next,#btInstance',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },

            //下一步
            onNext: function (tab, navigation, index) {
                switch (index) {
                    case 1:
                        if (!validCreateNewInstanceStep1()) {
                            return false;
                        }
                        break;
                }
                createNewInstanceHandlerTitle(tab, navigation, index);
            },

            //上一步
            onPrevious: function (tab, navigation, index) {
                createNewInstanceHandlerTitle(tab, navigation, index);
            },

            //进度条显示
            onTabShow: function (tab, navigation, index) {
                let total = navigation.find('li').length;
                let current = index + 1;
                let $percent = (current / total) * 100;
                $('#createNewInstanceConfigModal').find('.progress-bar').css({
                    width: $percent + '%'
                });
            },
            //回退到第一步
            onFirst: function (tab, navigation, index) {
                createNewInstanceHandlerTitle(tab, navigation, index);
            },
        });
        $('#createNewInstanceConfigModal').find('.button-previous').hide();
        $('#createNewInstanceConfigModal .button-submit').click(() => {
            if (!validCreateNewInstanceStep2()) {
                return false;
            }
            $('#createNewInstanceConfigModal').modal('hide');
        }).hide();
    };

    ///////////////////// --------- 结束-新实例配置 ---------- /////////////////////

    /**
     * 初始化时间点
     */

    /**
     * 初始化时间策略一次性恢复选择器
     */
    const initTimeStrategyOneTimePicker = () => {
        let onceTimePicker = '#timeStrategyFormDatetime';
        // 设置时间
        if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
            // 英文独有的
            $(onceTimePicker).datetimepicker({
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-mm-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        } else {
            $(onceTimePicker).datetimepicker({
                language: 'zh-CN',
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-MM-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }
        $('#onceTime').on('change', setTimeStrategyDes);
        $('#resetOnceTimeDate').on('click', function () {
            $('#onceTime').val('').trigger('change');
        });
    };
    const initTimepoint = () => {
        let recoverySourceType = parseInt($('#externalTimepointRecoveryType').val());
        if (recoverySourceType === RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT) {
            initPointDbTypeSelect();
            $('#recoverySourceTypeSelect').val(RECOVERY_SOURCE_TYPE_ENUM.SPECIFY_TIMEPOINT);
        } else {
            initDbTypeSelect();
            if (parseInt($('#externalEditFlag').val())) {  // 修改任务
                $('#recoverySourceTypeSelect').val(RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST);
            } else {
                $('#recoverySourceTypeSelect').val(RECOVERY_SOURCE_TYPE_ENUM.TIMER_NEWEST).trigger('change');
            }
        }
    };

    /**
     * 恢复逻辑说明
     * SAP HANA
     * 指定时间点恢复
     *   覆盖恢复：要求目标机必须有同名数据库，如果没有提示错误
     *   新建恢复：目标机如果没有同数据库，那么新建数据库；目标机如果有同名数据库，那么数据库名添加后缀(1)恢复
     * 定时恢复最新备份点恢复
     *   覆盖恢复：要求目标机必须有同名数据库，如果没有提示错误
     *   新建恢复：删除目标机存在的同名数据库，新建数据库
     *
     * SQL Server
     * 指定时间点恢复
     *   覆盖恢复：要求目标机必须有同名数据库，如果没有提示错误
     *   新建恢复：目标机如果没有同数据库，那么新建数据库；目标机如果有同名数据库，那么数据库名添加后缀(1)恢复
     * 定时恢复最新备份点恢复
     *   新建恢复：删除目标机存在的同名数据库，新建数据库
     */

    return {
        //main function to initiate the module
        init: function () {
            console.log('db-recovery');  // 这里打印是为了方便debug
            wizardInit();
            initListener();
            initTimeStrategyOneTimePicker();
            initTimepoint();
            initSpinner();
            initStrategy();
            initDatetimePicker();
            initDatetimePicker('.batchFormDatetime', '#batchLogTimeInput');  // 批量配置的时间选择初始化
            initUserPassword();
            initRecoveryScript();
            initOldData();
            initCreateNewInstanceWizard();
        },
    };
}();

jQuery(document).ready(function () {
    DBRecover.init();
});
