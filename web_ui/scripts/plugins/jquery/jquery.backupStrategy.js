/*
 * @note: 保留策略初始化插件
 * @author: chenyunfeng@vinchin.com
 * @Description: 作为第一版插件，代码很多冗余、功能处理杂乱、扩展性较差（建议后续新模块不使用此插件，改用优化之后的策略插件/scripts/plugins/jquery/backup-strategy.js）
 * @Date: 2024-05-13 11:15:03
 * @LastEditTime: 2026-03-09 11:56:58
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */
(function ($) {
    $.fn.backupStrategy = function (moduleType, hasParams, strategy, specialType, callback = null) {
        // 无增量备份
        const DB_NO_INCR = [1, 5, 6, 7, 8, 10, 11, 12, 14];
        // 无差异备份
        const DB_NO_DIFF = [3, 5, 6, 7, 8, 9, 10, 11, 12, 14];
        const VM_NO_DIFF = [40];
        // 操作系统云存储 不能永久增量
        const OS_NO_PINCR = ['cloud'];
        // 时间策略初始化配置
        const defaultTimeStrategy = [];
        const defaultTimeDisplay = ['display-none', 'display-none', 'display-none', 'display-none', 'display-none', 'display-none'];
        const checkArr = ['uncheck', 'uncheck', 'uncheck', 'uncheck', 'uncheck', 'uncheck'];
        // 一次性备份
        let isOnce = false;
        // 永久增量
        let ispIncr = false;
        // 云存储
        let isCloud = false;
        //限速策略初始化配置
        var defaultSpeedStrategy = [];
        defaultSpeedStrategy[0] = {
            mode: 1,
            strategy_type: 2,
            days: [0, 0, 0, 0, 1, 0, 0],
            start_time: '23:00:00',
            end_time: '23:30:00',
        };
        let initData = function (_this) {
            initShowModule();
            // 初始化时间策略
            // 初始化任务分布区间
            var timeBack = function (res) {
                if (res.data.time_list.length != 0) {
                    $('#backupCrowd').taskCrowd({ timeList: res.data.time_list, showFlag: res.data.show_flag });
                }
                let suggestInfo = res.data.suggest_time;
                defaultTimeStrategy[0] = {// 完全
                    mode: 1,
                    strategy_type: 2,
                    days: [0, 0, 0, 0, 1, 0, 0],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time,
                    dbtype: specialType,
                    full_backup_compensation_flag: false,
                };
                defaultTimeStrategy[1] = {// 增量
                    mode: 2,
                    strategy_type: 1,
                    days: [],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time,
                    dbtype: specialType,
                };
                defaultTimeStrategy[2] = {// 差异
                    mode: 3,
                    strategy_type: 1,
                    days: [],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time,
                    dbtype: specialType,
                };
                defaultTimeStrategy[3] = {// 日志
                    mode: 4,
                    strategy_type: 1,
                    days: [],
                    frequency: '',
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time,
                    dbtype: specialType,
                };
                defaultTimeStrategy[4] = {// 永久增量
                    mode: 9,
                    strategy_type: 1,
                    days: [],
                    start_time: suggestInfo.start_time,
                    end_time: suggestInfo.end_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: suggestInfo.roll_end_time,
                    dbtype: specialType,
                };
                // hana数据库新增策略
                if (specialType == CONF.DB_TYPE.SAPHANA) {
                    defaultTimeStrategy[5] = {
                        mode: 10,
                        value: 15
                    }
                }
                // 带参初始化时间策略
                if (strategy?.time?.timeInfo) {
                    initOldTime(_this);
                } else {
                    initDefaultTime(_this);
                }
                if (specialType == CONF.DB_TYPE.SAPHANA) {
                    $('.autoLog').show();             // 显示日志备份
                    $('#autoLogBackup').iCheck('check');
                    $('#autoLogBackup').iCheck('disable');
                    $('.strategy-panel [data-mode=10]').show(); // 显示日志备份
                }

                $(_this).find('#fullBackup').iCheck(checkArr[0]);
                $(_this).find('#incrBackup').iCheck(checkArr[1]);
                $(_this).find('#diffBackup').iCheck(checkArr[2]);
                $(_this).find('#logBackup').iCheck(checkArr[3]);
                $(_this).find('#pincrBackup').iCheck(checkArr[4]);
                getTimeDes(); // 获取时间策略描述
            }
            pAjaxRequest({}, "/api/v1/jobs/time/crow/list", "GET", timeBack, true);
            // 隐藏差异备份
            if (DB_NO_DIFF.includes(specialType)) {
                $('.diff').hide();
            }
            if (CONF.MODULE_TYPE.VM == moduleType && VM_NO_DIFF.includes(specialType)) {
                $('.diff').hide();
            }
            // 隐藏增量备份
            if (DB_NO_INCR.includes(specialType) && moduleType == CONF.MODULE_TYPE.DB) {
                $('.incr').hide();
            }
            // 隐藏永久增量
            if (OS_NO_PINCR.includes(specialType)) {
                $('.pIncr').hide();
                isCloud = true;
            }
            // 初始化时间策略展示信息
            // 初始化限速策略
            if (strategy?.speedlimit?.uuid || (strategy?.speedlimit?.speedInfo && !(Object.keys(strategy.speedlimit.speedInfo).length === 0))) {
                let check = false;
                let speedInfo = [];
                let level = 1;
                let type = 1;
                check = strategy.speedlimit.check;
                level = strategy.speedlimit.level;
                type = strategy.speedlimit.type;
                speedInfo = strategy.speedlimit.speedInfo;
                if (check) {
                    $('#speedlimitCheck').bootstrapSwitch('state', true);
                }
                let speedList = [];
                for (var i = 0; i < speedInfo.length; i++) {
                    speedList.push(speedInfo[i]);
                }
                $('.speedTips').popover();	   //初始化tips
                var titleDes = "";
                var des = "";
                $('#tasklevelselect').val(level);
                $('#speedtypeselect').val(type);
                // 如果是之前的自定义的 方式不变 但是如果是选择的全局限速策略的话，那么需要读取出所有的全局限速策略列表，然后根据列表的id取出限速信息
                if (type == 1) {
                    $('#show_type_1').show();
                    $('#show_type_2').hide();
                    $('#task_type_global_speed_strategy').show();
                    let global_speed_limit = strategy.speedlimit.uuid;
                    $(".strategy-table #table").bootstrapTable('refresh', { query: {uuid: global_speed_limit} });
                    // 下面的需要初始化全局限速策略表格并携带参数
                    let rowData = $(".strategy-table #table").bootstrapTable("getData");

                    let pData = [];
                    for (var j in rowData) {
                        if (rowData[j].uuid == global_speed_limit) {
                            pData = rowData[j].detail;
                            pData = pData.split('</br>');
                        }
                    }
                    if (pData.length != 0) {
                        des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + pData.length;
                    }

                    for (var i in pData) {
                        titleDes += pData[i] + '. ' + "\n";
                    }
                    $('.speedlimitDes').html(des);
                    $('.speedlimitDes').prop('title', titleDes);
                } else {
                    // 自定义
                    $('#show_type_2').show();
                    $('#show_type_1').hide();
                    $('#task_type_global_speed_strategy').hide();
                    if (speedList.length != 0) {
                        des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
                    }
                    var strategy_type = 1;
                    for (var i = 0; i < speedList.length; i++) {
                        titleDes += speedList[i].des + '. ' + "\n";
                        strategy_type = speedList[i].type;
                    }
                    addGlobalStrategy.init({ 'strategy_type': strategy_type, speedInfo: speedList, initSpeedFlag: 1, module_type: moduleType });
                    setTimeout(function () {
                        $('.speedlimitDes').html(des);
                        $('.speedlimitDes').prop('title', titleDes);
                    }, 1000);
                }
            } else {
                if (!OS_NO_PINCR.includes(specialType)) {
                    $(_this).find(".strategy-table #table").bootstrapTable('uncheckAll');
                    $(_this).find('#speedstrategy').speedstrategy({ config: defaultSpeedStrategy });
                    $(_this).find('.speedlimitDes').empty();
                    $(_this).find('#speedList').empty();
                }
            }
            // 初始化存储策略
            if (strategy && strategy.store && strategy.store.storeInfo && !(Object.keys(strategy.store.storeInfo).length === 0)) {
                let check = strategy.store.check;
                $(_this).find('.store-strategy-form').store(moduleType, hasParams, strategy.store.storeInfo);
                $(_this).find('#storeStrategyCheck').bootstrapSwitch('state', check);
            } else {
                $(_this).find('.store-strategy-form').store(moduleType, hasParams)
            }
            // 判断一次性备份
            if (strategy?.time?.timeInfo?.type == 'oncetime') {
                isOnce = true;
            }
            // 判断是否是永久增量
            if (isValidPIncrStrategy(strategy, specialType)) {
                ispIncr = true;
            }
            // 初始化保留策略
            if (strategy && strategy.reserve && strategy.reserve.reserveInfo && !(Object.keys(strategy.reserve.reserveInfo).length === 0)) {
                let check = strategy.reserve.check;
                $(_this).find('.reserve-strategy-form').reserve(isOnce, isCloud, moduleType, ispIncr, strategy.reserve.reserveInfo, specialType);
                $(_this).find('#reserveStrategyCheck').bootstrapSwitch('state', check);
            } else {
                $(_this).find('.reserve-strategy-form').reserve(isOnce, isCloud, moduleType, ispIncr, { strategyMode: 1, type: 1, value: 30 }, specialType);
            }
        };
        // 判断是否是永久增量
        function isValidPIncrStrategy(strategy, specialType) {
            // 检查基本结构
            const timeInfo = strategy?.time?.timeInfo;
            const pIncrInfo = timeInfo?.pIncrInfo;
            if (!timeInfo || !pIncrInfo) return false;

            // 检查是否为strategy类型且对象不为空
            const isStrategyType = strategy.time.type == 'strategy';
            const hasTimeInfo = Object.keys(timeInfo).length > 0;
            const hasPIncrInfo = Object.keys(pIncrInfo).length > 0;

            // 检查specialType是否支持
            const isSupportedType = !OS_NO_PINCR.includes(specialType);
            return isStrategyType && hasTimeInfo && hasPIncrInfo && isSupportedType;
        }
        let initOldTime = function (_this) {
            let check = strategy.time.check;
            let timeInfo = strategy.time.timeInfo;
            let diffIncrFlag = false; // 增量和差异互斥标志
            $(_this).find('#backupTimeCheck').bootstrapSwitch('state', check);
            //设置时间策略类型
            $(_this).find('#backuptype').val(strategy.time.type);
            if ('strategy' == strategy.time.type) {
                $('.setStrategy').show();
                $('.setOnceTime').hide();
                $('#oncetime').val('');
                //按策略备份,设置时间策略
                if (timeInfo.fullInfo && !(Object.keys(timeInfo.fullInfo).length === 0)) {
                    //完全备份
                    defaultTimeStrategy[0] = {
                        mode: timeInfo.fullInfo.mode,
                        strategy_type: timeInfo.fullInfo.type,
                        days: timeInfo.fullInfo.days,
                        frequency: timeInfo.fullInfo.frequency,
                        start_time: timeInfo.fullInfo.startTime,
                        roll_flag: timeInfo.fullInfo.rollFlag,
                        roll_interval: timeInfo.fullInfo.rollInterval,
                        roll_end_time: timeInfo.fullInfo.endTime,
                        dbtype: specialType,
                        full_backup_compensation_flag: timeInfo.fullInfo.full_backup_compensation_flag,
                    }
                    defaultTimeDisplay[0] = '';
                    checkArr[0] = 'check';
                }
                if (timeInfo.incrInfo && !(DB_NO_INCR.includes(specialType) && moduleType == CONF.MODULE_TYPE.DB) && !(Object.keys(timeInfo.incrInfo).length === 0)) {
                    //增量备份
                    defaultTimeStrategy[1] = {
                        mode: timeInfo.incrInfo.mode,
                        strategy_type: timeInfo.incrInfo.type,
                        days: timeInfo.incrInfo.days,
                        frequency: timeInfo.incrInfo.frequency,
                        start_time: timeInfo.incrInfo.startTime,
                        roll_flag: timeInfo.incrInfo.rollFlag,
                        roll_interval: timeInfo.incrInfo.rollInterval,
                        roll_end_time: timeInfo.incrInfo.endTime,
                        dbtype: specialType,
                    }
                    defaultTimeDisplay[1] = '';
                    checkArr[1] = 'check';
                    diffIncrFlag = true;
                }
                // 其他类型 diffIncrFlag 用于判断是否有增量，没有增量才能读取差备；只有MongoDB不能差备增备一起使用
                if (timeInfo.diffInfo && !(DB_NO_DIFF.includes(specialType) && moduleType == CONF.MODULE_TYPE.DB) && !(CONF.MODULE_TYPE.VM == moduleType && VM_NO_DIFF.includes(specialType)) && !(Object.keys(timeInfo.diffInfo).length === 0)) {
                    if (moduleType == CONF.MODULE_TYPE.DB && specialType == CONF.DB_TYPE.MONGODB && diffIncrFlag) {

                    } else {
                        //差异备份
                        defaultTimeStrategy[2] = {
                            mode: timeInfo.diffInfo.mode,
                            strategy_type: timeInfo.diffInfo.type,
                            days: timeInfo.diffInfo.days,
                            frequency: timeInfo.diffInfo.frequency,
                            start_time: timeInfo.diffInfo.startTime,
                            roll_flag: timeInfo.diffInfo.rollFlag,
                            roll_interval: timeInfo.diffInfo.rollInterval,
                            roll_end_time: timeInfo.diffInfo.endTime,
                            dbtype: specialType,
                        }
                        defaultTimeDisplay[2] = '';
                        checkArr[2] = 'check';
                    }
                }
                if (timeInfo.logInfo && !(Object.keys(timeInfo.logInfo).length === 0) && !(specialType == CONF.DB_TYPE.SAPHANA && moduleType == CONF.MODULE_TYPE.DB)) {
                    //（归档）日志备份
                    defaultTimeStrategy[3] = {
                        mode: timeInfo.logInfo.mode,
                        strategy_type: timeInfo.logInfo.type,
                        days: timeInfo.logInfo.days,
                        frequency: timeInfo.logInfo.frequency,
                        start_time: timeInfo.logInfo.startTime,
                        roll_flag: timeInfo.logInfo.rollFlag,
                        roll_interval: timeInfo.logInfo.rollInterval,
                        roll_end_time: timeInfo.logInfo.endTime,
                        dbtype: specialType,
                    }
                    if (!(specialType == CONF.DB_TYPE.TIDB && moduleType == CONF.MODULE_TYPE.DB)) {
                        defaultTimeDisplay[3] = '';
                    }
                    checkArr[3] = 'check';
                }
                // 云存储不能永久增量；数据库只有MongoDB有永久增量
                if (timeInfo.pIncrInfo && !(Object.keys(timeInfo.pIncrInfo).length === 0) && !OS_NO_PINCR.includes(specialType)) {
                    if (moduleType == CONF.MODULE_TYPE.DB && specialType != CONF.DB_TYPE.MONGODB) {

                    } else {
                        //永久增量备份
                        defaultTimeStrategy[4] = {
                            mode: timeInfo.pIncrInfo.mode,
                            strategy_type: timeInfo.pIncrInfo.type,
                            days: timeInfo.pIncrInfo.days,
                            frequency: timeInfo.pIncrInfo.frequency,
                            start_time: timeInfo.pIncrInfo.startTime,
                            roll_flag: timeInfo.pIncrInfo.rollFlag,
                            roll_interval: timeInfo.pIncrInfo.rollInterval,
                            roll_end_time: timeInfo.pIncrInfo.endTime,
                            dbtype: specialType,
                        }
                        defaultTimeDisplay[4] = '';
                        checkArr[4] = 'check';
                    }
                }
                if (timeInfo.autoLogInfo && !(Object.keys(timeInfo.autoLogInfo).length === 0)) {
                    defaultTimeStrategy[5] = {
                        mode: 10,
                        value: timeInfo.autoLogInfo.value,
                    }
                }
            } else if ('oncetime' == strategy.time.type) {
                $('.setStrategy').hide();
                $('.setOnceTime').show();
                if (timeInfo.datetime) {
                    $('#oncetime').val(timeInfo.datetime);
                }
                if (strategy.time.data) {
                    $('#oncetime').val(strategy.time.data);
                }
            } else if ('manual' == strategy.time.type) {
                // 手动启动
                $('.setOnceTime').hide();
                if (specialType == CONF.DB_TYPE.SAPHANA) {
                    $('.setStrategy').show();
                    $('.full').hide();//完全备份
                    $('.incr').hide();//增量备份
                    $('.diff').hide(); //差异备份
                } else {
                    $('.setStrategy').hide();
                }
            }
            $(_this).find('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: defaultTimeStrategy, display: defaultTimeDisplay });

            if (OS_NO_PINCR.includes(specialType) && timeInfo.pIncrInfo && !(Object.keys(timeInfo.pIncrInfo).length === 0)) {
                $('.pIncr').hide();
                UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_APPLY, LANG.UI_GLOBAL_STRATEGY_APPLY_CLOUD_ERROR);
            }
        };
        // 初始化默认时间策略
        let initDefaultTime = function () {
            // 初始化策略配置
            $('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: defaultTimeStrategy, display: defaultTimeDisplay, backup_flag: 1 });
            // 默认显示按策略备份
            $('#backuptype').val('strategy');
            $('.setStrategy').show();
            $('.setOnceTime').hide();
            // 所有时间策略重置可选
            // 完备
            $('#fullBackup').iCheck('uncheck');
            $('#fullBackup').iCheck('enable');
            // 增备
            $('#incrBackup').iCheck('uncheck');
            $('#incrBackup').iCheck('enable');
            // 差备
            $('#diffBackup').iCheck('uncheck');
            $('#diffBackup').iCheck('enable');
            // 永久增量
            $('#pincrBackup').iCheck('uncheck');
            $('#pincrBackup').iCheck('enable');
            // 日志
            $('#logBackup').iCheck('uncheck');
            $('#logBackup').iCheck('enable');
            getTimeDes(); // 获取时间策略描述
        }

        let initShowModule = function () {
            if (moduleType == 12) {
                $('.backupReserveStrategy').hide();
            } else {
                $('.backupReserveStrategy').show();
            }
            // 初始化保留策略
            // $('.reserve-strategy-form').reserve(isOnce, isCloud, moduleType, ispIncr, {}, specialType);
            switch (parseInt(moduleType)) {
                //虚拟机
                case CONF.MODULE_TYPE.VM:
                    //时间策略
                    $('.backupStrategy').show();
                    $('.full').show();//完全备份
                    $('.incr').show();//增量备份
                    $('.diff').show(); //差异备份
                    $('.dbLog').hide();//日志归档
                    $('.pIncr').show();//永久增量
                    // 时间策略提示
                    $('.all-strategy-tips').show(); //虚拟机
                    $('.pIncr-tips').hide();// exchange
                    $('.no-pIncr-tips').hide(); // 文件、nas、os
                    $('.db-tips').hide(); //数据库
                    $('.no-diff-tips').hide(); //exchange
                    //存储策略
                    $('.deduplicationDiv').show();//重复数据删除
                    // 保留策略
                    $('.GFSdiv').show();//GFS
                    break;
                //文件
                case CONF.MODULE_TYPE.FS:
                //nas
                case CONF.MODULE_TYPE.NAS:
                case CONF.MODULE_TYPE.OBS:
                case CONF.MODULE_TYPE.HADOOP:
                    //时间策略
                    $('.backupStrategy').show();
                    $('.full').show();//完全备份
                    $('.incr').show();//增量备份
                    $('.diff').show(); //差异备份
                    // $('.pIncr').hide();//永久增量
                    $('.dbLog').hide();//日志归档
                    // 时间策略提示
                    $('.all-strategy-tips').hide(); //虚拟机、os
                    $('.pIncr-tips').hide();// exchange
                    $('.no-pIncr-tips').show(); // 文件、nas
                    $('.db-tips').hide(); //数据库
                    $('.no-diff-tips').hide(); //exchange
                    //存储策略
                    $('.deduplicationDiv').hide();//重复数据删除
                    // 保留策略
                    $('.GFSdiv').hide();//GFS
                    break;
                //数据库
                case CONF.MODULE_TYPE.DB://数据库
                    //时间策略
                    $('.backupStrategy').show();
                    $('.full').show();//完全备份
                    $('.incr').show();//增量备份
                    $('.diff').show(); //差异备份
                    $('.dbLog').show();//日志归档
                    $('.pIncr').hide();//永久增量
                    // 时间策略提示
                    $('.all-strategy-tips').hide(); //虚拟机、os
                    $('.pIncr-tips').hide();// exchange
                    $('.no-pIncr-tips').hide(); // 文件、nas
                    $('.db-tips').show(); //数据库
                    $('.no-diff-tips').hide(); //exchange
                    $('.autoLog').hide(); // hana独有
                    $('.reserveModeTips').hide();

                    $('.time-strategy-tips').hide();    // 隐藏所有的时间策略tips
                    switch (specialType) {
                        case CONF.DB_TYPE.SQLSERVER:
                            $('.db-no-incr-tips').show();   // 时间策略提示
                            break;
                        case CONF.DB_TYPE.ORACLE:
                            $('.db-tips').show();  // 时间策略提示
                            break;
                        case CONF.DB_TYPE.MARIA:
                        case CONF.DB_TYPE.MYSQL:
                            $('.db-no-diff-tips').show();       // 时间策略提示
                            break;
                        case CONF.DB_TYPE.DM:
                            $('.db-tips').show();               // 时间策略提示
                            break;
                        case CONF.DB_TYPE.POSTGRE:
                        case CONF.DB_TYPE.ANTDB:
                        case CONF.DB_TYPE.KINGBASE:
                        case CONF.DB_TYPE.UXDB:
                        case CONF.DB_TYPE.HIGHGO:
                        case CONF.DB_TYPE.OPENGAUSS:
                        case CONF.DB_TYPE.VASTBASE:
                            $('.db-log-tips').show();          // 时间策略提示
                            break;
                        case CONF.DB_TYPE.CACHE:
                        case CONF.DB_TYPE.IRIS:
                            $('.db-all-tips').show();           // 时间策略提示
                            break;
                        case CONF.DB_TYPE.TIDB:
                            $('.db-tidb-tips').show();      // 显示TiDB时间策略描述
                            break;
                        case CONF.DB_TYPE.SAPHANA:
                            $('.db-no-log-tips').show();    // 时间策略提示
                            $('.dbLog').hide();
                            $('.autoLog').show();
                            break;
                        case CONF.DB_TYPE.MONGODB:
                            $('.db-all-tips').show();          // 时间策略提示
                            $('.pIncr').show();//永久增量
                            $('.reserveModeTips').show();
                            break;
                    }
                    //存储策略
                    $('.deduplicationDiv').show();//重复数据删除
                    // 保留策略
                    $('.GFSdiv').hide();//GFS
                    break;
                //操作系统
                case CONF.MODULE_TYPE.OS:
                    //时间策略
                    $('.backupStrategy').show();
                    $('.full').show();//完全备份
                    $('.incr').show();//增量备份
                    $('.diff').show(); //差异备份
                    $('.dbLog').hide();//日志归档
                    $('.pIncr').show();//永久增量
                    // 时间策略提示
                    $('.all-strategy-tips').show(); //虚拟机、os
                    $('.pIncr-tips').hide();// exchange
                    $('.no-pIncr-tips').hide(); // 文件、nas
                    $('.db-tips').hide(); //数据库
                    //存储策略
                    $('.deduplicationDiv').show();//重复数据删除
                    // 保留策略
                    $('.GFSdiv').hide();//GFS
                    break;
                // exchange
                case CONF.MODULE_TYPE.M365:
                    //时间策略
                    $('.backupStrategy').show();
                    $('.pIncr').show();//永久增量
                    $('.full').show();//完备
                    $('.incr').show();//增备
                    $('.diff').hide();//差异
                    $('.dbLog').hide();//日志归档
                    // 时间策略提示
                    $('.all-strategy-tips').hide(); //虚拟机、os
                    $('.pIncr-tips').hide();// exchange
                    $('.no-diff-tips').show();// exchange
                    $('.no-pIncr-tips').hide(); // 文件、nas
                    $('.db-tips').hide(); //数据库
                    // 存储策略
                    $('.deduplicationDiv').hide();//重复数据删除
                    $('#encryptStorageCheck').bootstrapSwitch('state', false);
                    // 保留策略
                    $('.GFSdiv').hide();//GFS
                    break;
                case CONF.MODULE_TYPE.PUBLIC_CLOUD:
                    //时间策略
                    $('.backupStrategy').show();
                    $('.pIncr').show();//永久增量
                    $('.full').show();//完备
                    $('.incr').show();//增备
                    $('.diff').show();//差异
                    $('.dbLog').hide();//日志归档
                    // 时间策略提示
                    $('.all-strategy-tips').show(); //虚拟机、os
                    $('.pIncr-tips').hide();// exchange
                    $('.no-pIncr-tips').hide(); // 文件、nas
                    $('.db-tips').hide(); //数据库
                    // 存储策略
                    $('.deduplicationDiv').show();//重复数据删除
                    break;
            }
        };

        // 获取时间策略信息描述
        let getTimeDes = function () {
            var des = "";
            var diffDes = "";
            //备份
            var backupType = $('#backuptype').val();
            var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
            if (!strategyConfig.fullInfo) return;
            var strategyMode = $('#strategymode').find('input:checked');
            if ('strategy' == backupType) {
                for (var i = 0; i < strategyMode.length; i++) {
                    if (1 == $(strategyMode[i]).data('mode')) {
                        des += strategyConfig.fullInfo.des + ". ";
                        diffDes += strategyConfig.fullInfo.des + "<br>";
                    } else if (2 == $(strategyMode[i]).data('mode')) {
                        des += strategyConfig.incrInfo.des + ". ";
                        diffDes += strategyConfig.incrInfo.des + "<br>";
                    } else if (3 == $(strategyMode[i]).data('mode')) {
                        des += strategyConfig.diffInfo.des + ". ";
                        diffDes += strategyConfig.diffInfo.des + "<br>";
                    } else if (9 == $(strategyMode[i]).data('mode')) {
                        des += strategyConfig.pIncrInfo.des + ". ";
                        diffDes += strategyConfig.pIncrInfo.des + "<br>";
                    } else if (4 == $(strategyMode[i]).data('mode')) {
                        if (!(specialType == CONF.DB_TYPE.TIDB && moduleType == CONF.MODULE_TYPE.DB)) {
                            des += strategyConfig.logInfo.des + ". ";
                            diffDes += strategyConfig.logInfo.des + "<br>";
                        } else {
                            des += LANG.UI_STRATEGY_LOG + ". ";
                            diffDes += LANG.UI_STRATEGY_LOG + "<br>";
                        }
                    } else if (10 == $(strategyMode[i]).data('mode') && specialType == CONF.DB_TYPE.SAPHANA) {
                        des += strategyConfig.autoLogInfo.des + ". ";
                        diffDes += strategyConfig.autoLogInfo.des + "<br>";
                    }
                }
            } else if ('oncetime' == backupType) {
                des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
                diffDes += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
            }
            $('.backupTimeDes').html(des);
            $('.backupTimeDes').prop('title', des);
        }

        let backupTypeHandler = function () {
            if ('strategy' == this.value) {
                //按策略备份
                $('.setStrategy').show();
                $('.setOnceTime').hide();
                $('#GFSflag').bootstrapSwitch('disabled', false);
                $('#reserveType').removeAttr("disabled");
                $('#spinnerNum').spinner('enable');
                $('#spinnerDay').spinner('enable');
                $('#reserveType').val(1);
                $('#reserveType option[value="3"]').hide();
                $('#strategymode .icheck').iCheck('uncheck');
                $('#stragegyaccordion .strategy-panel').hide();
                // 永久增量
                $('#pincrBackup').iCheck('uncheck');
                if (specialType == CONF.DB_TYPE.SAPHANA) {
                    $('.full').show();//完全备份
                    $('.incr').show();//增量备份
                    $('.diff').show(); //差异备份
                    // 显示内容框
                    let strategyPanel = $('#backupTimestrategy .strategy-panel');
                    strategyPanel.each(function () {
                        if (parseInt($(this).data('mode')) == 10) {
                            $(this).show();
                        }
                    });
                }
            } else if ('oncetime' == this.value) {
                //一次性备份
                $('.setStrategy').hide();
                $('.setOnceTime').show();
                $('.reserveNum').show();
                $('.reserveDay').hide();
                $('#reserveType').removeAttr("disabled");
                $('#reserveType option[value="3"]').hide();
                $('#reserveType').val(1);
            } else if ('manual' == this.value) {
                // 手动启动
                $('.setOnceTime').hide();
                $('#reserveType').removeAttr("disabled");
                $('#spinnerNum').spinner('enable');
                $('#spinnerDay').spinner('enable');
                if (specialType == CONF.DB_TYPE.SAPHANA) {
                    $('.setStrategy').show();
                    // 隐藏勾选框
                    let modeLabel = $('#strategymode label');
                    modeLabel.each(function () {
                        if (!$(this).hasClass('autoLog')) {
                            $(this).hide();  // 或者使用 css 方法
                        }
                    });
                    // 隐藏内容框
                    let strategyPanel = $('#backupTimestrategy .strategy-panel');
                    strategyPanel.each(function () {
                        if (parseInt($(this).data('mode')) != 10) {
                            $(this).hide();
                        }
                    });
                    // 取消勾选其他类型
                    let modeInput = $('#strategymode input');
                    modeInput.each(function () {
                        if (parseInt($(this).data('mode')) != 10) {
                            $(this).iCheck('uncheck');
                        }
                    });
                } else {
                    $('.setStrategy').hide();
                }
            };
            getTimeDes();
        }

        let initListener = function (_this) {
            // 切换时间策略备份模式
            $(_this).find('#fullBackup').on('ifChecked ifUnchecked ', getTimeDes); // 完备
            $(_this).find('#incrBackup').on('ifChecked ifUnchecked ', getTimeDes); // 增备
            $(_this).find('#diffBackup').on('ifChecked ifUnchecked ', getTimeDes); // 差备
            $(_this).find('#pincrBackup').on('ifChecked ifUnchecked ', getTimeDes); // 永久增量
            $(_this).find('#logBackup').on('ifChecked ifUnchecked ', getTimeDes); // 日志
            $(_this).find('#autoLogBackup').on('ifChecked ifUnchecked ', getTimeDes); // 日志
            $(_this).find('#oncetime').on('change', getTimeDes); // 一次性
            $(_this).find('#backuptype').on('change', backupTypeHandler);
            $(_this).find('#resetdate').on('click', function () {
                $('#oncetime').val('');
                getTimeDes();
            });
        };
        let init = function (_this) {
            initData(_this);
            initListener(_this);
            //增加回调函数，用于各个模块调用插件时执行一些模块内部特有的操作
            setTimeout(() => {
                if (callback && typeof callback == 'function') {
                    callback();
                }
            }, 500);
        };
        return init($(this));
    }
})(jQuery)