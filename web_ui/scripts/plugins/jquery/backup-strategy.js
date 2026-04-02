/*
 * @note: 策略初始化插件
 * @author: chenyunfeng@vinchin.com
 * @Description: 基于旧版策略插件优化升级，可扩展性更强，且兼容策略所需所有操作，更简洁
 * @function getBackupStrategy 获取策略所有配置信息
 * @Date: 2024-11-13 15:01:42
 * @LastEditTime: 2026-03-13 17:20:05
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */
/**
 * 定义一个策略功能的插件。
 * 包括时间策略、限速策略、存储策略、保留策略
 * 插件包含所有的配置
 * 不同模块的配置显示差异可以通过传入配置项来控制
 * 但是不同模块的自定义联动事件需要单独处理，插件只处理通用事件和展示内容
 * @param {Object} options 用户自定义的配置项。
 * @function initBackupStrategy 初始化备份策略
 * 1.生成所有策略的html内容
 * 2.设置所有的事件监听
 * 用法：
 * 1.页面入口 <div id="backupStrategyDiv" class="col-md-10 col-md-offset-1"></div>    id随意，class也行
 * 2.js初始化：$('#backupStrategyDiv').initBackupStrategy(defaultConfig);
 * 3.策略默认配置 默认显示时间、限速、保留、存储策略，可通过参数控制显示与否
    const defaultConfig = {
        mode: [1, 2, 3, 9], 时间策略类型 完备、增备、差异等
    };
 * @function refreshStrategy 刷新页面信息---备份模块用不到，主要在全局策略配置处使用
 * 根据传入的配置选择性刷新页面，而不是重新生成html
 * @function getBackupStrategy 获取策略所有配置信息
 *
 * css引用
 *<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
 *<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
 *<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
 *<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
 *<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
 *
 * js引用
 *<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
 *<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
 *<script type="text/javascript" src="./scripts/plugins/jquery/jquery.GFSStrategy.js"></script>
 *<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
 *<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
 *<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
 *<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
 *<script type="text/javascript" src="./scripts/platform/global_strategy/speed_strategy.js"></script>
 *<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
 *<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
 *<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
 *<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
 *
 *
*/
(function ($) {
    $.fn.initBackupStrategy = function (options, callback = null) {
        // 备份策略类型 按策略，一次性，手动
        const STRATEGY_TYPE = { STRATEGY: 'strategy', ONCE: 'oncetime', MANUAL: 'manual' };
        const STRATEGY_TYPE_CONFIG = { 'strategy': LANG.UI_BACKUP_USE_STRATEGY, 'oncetime': LANG.UI_BACKUP_ONCE, 'manual': LANG.UI_BACKUP_MANUAL, };
        // 恢复策略类型 立即启动，指定时间启动，按策略启动
        const STRATEGY_TYPE_RECOVERY = { IMMEDIATE: 'immediate', ATTIME: 'atTime', STRATEGY: 'strategy' };
        const STRATEGY_TYPE_RECOERY_CONFIG = { 'immediate': LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE, 'atTime': LANG.UI_GLOBAL_STRATEGY_TYPE_START_AT_TIME, 'strategy': LANG.UI_GLOBAL_STRATEGY_TYPE_BY_STRATEGY };
        const defaultTimeStrategy = [];
        // 默认时间策略类型
        const defaultTimeStrategyConfig = [
            { mode: 1, strategy_type: 2 },
            { mode: 2, strategy_type: 1 },
            { mode: 3, strategy_type: 1 },
            { mode: 4, strategy_type: 1 },
            { mode: 9, strategy_type: 1 }
        ];
        const defaultTimeDisplay = ['display-none', 'display-none', 'display-none', 'display-none', 'display-none'];
        const allTimeMode = [1, 2, 3, 4, 9];
        // 定义默认配置项
        const defaultOptions = {
            'mode': [1, 2, 3, 4, 9], // 时间策略模式
            'speedConfig': [{ // 限速策略默认配置
                mode: 1,
                strategy_type: 2,
                days: [0, 0, 0, 0, 1, 0, 0],
                start_time: '23:00:00',
                end_time: '23:30:00',
                speed: [],
            }],
            'GFS': false, // gfs展示标志
            'deduplication': false, // 重复数据删除展示标志
            'permanent': false, // 永久保留是否展示
            'passwordChange': false, //
            'time': true, //时间策略渲染标志
            'store': true, //存储策略渲染标志
            'reserve': true, //保留策略渲染标志
            'speed': true, //限速策略渲染标志
            'storage_type': 0,
            'timeFormateFlag': true, // 时间策略转换标志
            'strategy_group_flag': false, // 策略组标志-- 策略组需要渲染开关
            'module_type_des': '', // 用于模块特殊处理
            'db_type': '',
            'reserve_mode_chain_flag': false, // 保留类型只允许按链保留
            'reserve_num_only_flag': false, // 只按个数保留标志
            'recovery_type': ['atTime', 'immediate'], // 恢复  按策略恢复显示标志
            'copy_flag': false, // 副本标志
            'archive_flag': false, // 归档标志
        };
        // 当前页面位置
        const _dom = $(this);
        // 各个备份模式列表 mode -> 名称
        const MODE_LIST = {
            1: LANG.UI_PUBLIC_BACKUP_FULL, // 完全备份
            2: LANG.UI_PUBLIC_BACKUP_INCREMENT, // 差异备份
            3: LANG.UI_PUBLIC_BACKUP_DIFFRENCE, // 增量备份
            4: LANG.UI_GLOBAL_STRATEGY_ARCHIVE_LOG_BACKUP, // 日志备份
            9: LANG.UI_PUBLIC_PERMANENT_INCREMENT, // 永久增量
        }
        // 各个备份模式提示  mode -> 提示信息
        const MODE_TIPS = {
            1: LANG.UI_GLOBAL_STRATEGY_FULL_TOOLTIPS, // 完备提示
            2: LANG.UI_GLOBAL_STRATEGY_INCR_TOOLTIPS, // 增量提示
            3: LANG.UI_GLOBAL_STRATEGY_DIFF_TOOLTIPS, // 差异提示
            4: LANG.UI_GLOBAL_STRATEGY_LOG_TOOLTIPS, // 日志提示
            5: LANG.UI_GLOBAL_STRATEGY_THREE_TOOLTIPS, // 完备、增量、差异配合使用提示
            9: LANG.UI_GLOBAL_STRATEGY_PINCR_TOOLTIPS, //永久增量提示
        }
        // 限速策略任务等级  等级->描述
        const TASK_LEVEL = {
            1: LANG.UI_GLOBAL_STRATEGY_TASK_LEVEL_NORMAL, // 一般
            2: LANG.UI_GLOBAL_STRATEGY_TASK_LEVEL_FIRST, // 优先
            3: LANG.UI_GLOBAL_STRATEGY_TASK_LEVEL_HIGH, //最高
        }
        // 压缩等级
        const COMPRESS_METHOD = {
            1: LANG.UI_JOB_COMPRESS_PRIORITY_FASTER, // 极速压缩
            2: LANG.UI_JOB_COMPRESS_PRIORITY_NORMAL, // 标准雅俗
            3: LANG.UI_JOB_COMPRESS_PRIORITY_BETTER, // 最大压缩
            4: LANG.UI_JOB_COMPRESS_PRIORITY_BEST, // 极限压缩
        }
        // 保留方式
        const RESERVE_MODE = {
            1: LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT, // 按备份点保留
            2: LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN, // 按备份链保留
        }
        // 保留类型
        const RESERVE_TYPE = {
            1: LANG.UI_BACKUP_NUM, // 按个数保留
            2: LANG.UI_BACKUP_DAY, // 按天数保留
            3: LANG.UI_STRATEGY_PERMANENT_RESERVE, // 永久保留
        }
        // 加密算法
        const STORE_ENCRYPT_METHOD = {
            1: LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES, // aes-256
            2: LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM, // sm4
        }
        $.fn.initBackupStrategy.MODE_LIST = MODE_LIST;
        $.fn.initBackupStrategy.MODE_TIPS = MODE_TIPS;
        // 合并用户配置与默认配置
        const settings = $.extend({}, defaultOptions, options);
        // 暴露给其他方法使用
        $.fn.initBackupStrategy.settings = settings;
        // 读取配置config 得到对应的值 和 描述 生成选项option
        const getOptions = (setting, config, type = '') => {
            let html = "";
            for (let key in config) {
                if (config.hasOwnProperty(key)) {
                    // 虚拟机、操作系统以云存储为目标存储时，只能按链保留
                    if ((type == 'reserveMode' && ((settings.storage_type == 9 && ['os', 'vm'].includes(setting.module_type_des)) || settings.reserve_mode_chain_flag)) && key == 'POINT') {
                        // 按个数保留不渲染天数
                    } else if (type == 'reserveType' && key == "DAY" && settings.reserve_num_only_flag) {
                    } else {
                        // 副本归档需要修改对应描述
                        if (settings.copy_flag) {
                            html += `<option value="${config[key]}">${setting[config[key]].replaceAll(LANG.UI_VISUAL_BACKUP, LANG.UI_VISUAL_DRILLS_VM_COPY)}</option>`;
                        } else if (settings.archive_flag) {
                            html += `<option value="${config[key]}">${setting[config[key]].replaceAll(LANG.UI_VISUAL_BACKUP, LANG.UI_VISUAL_ARCHIVE)}</option>`;
                        } else {
                            html += `<option value="${config[key]}">${setting[config[key]]}</option>`;
                        }
                    }
                }
            }
            return html;
        }

        /******初始化时间策略内容 */
        // 组合时间策略
        const getTimeContent = () => {
            let html = `<div class="form-group strategy_time-content ${settings.strategy_group_flag ? 'display-none' : ''}">
                            <div class="strategy_time-div">
                                <div class="accordion strategyOne">
                                    <div class="panel panel-default strategy-panel">
                                    ${getTimePanelHeading()}
                                    ${getTimePanelBody()}
                                    </div>
                                </div>
                            </div>
                        </div>`;
            return html;
        }
        // 组合时间策略展开头
        const getTimePanelHeading = () => {
            return `<div class="panel-heading">
                        <h4 class="panel-title">
                            <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupTime" aria-expanded="true">
                                <i class="viconfont vicon-shijian2 font-green-seagreen"></i>
                                <span class="font-green-seagreen">` + LANG.UI_STRATEGY_TIME + `</span>
                                <span class="strategyDes backupTimeDes"></span>
                            </a>
                        </h4>
                    </div>`;
        }
        // 组合时间策略展开内容
        const getTimePanelBody = () => {
            return `<div id="backupTime" class="panel-collapse collapse in">
                        <div class="panel-body">
                        ${getTaskCrowdContent()}
                        ${getBackupTypeSelectContent()}
                        ${getBackupModeContent()}
                        ${timePickerInput}
                        </div>
                    </div>`;
        }
        // 组合任务分布图
        const getTaskCrowdContent = () => {
            return `<div class="form-group">
                        <label class="control-label col-md-2 form-group-label">` + LANG.UI_GLOBAL_STRATEGY_TASK_CROWD + `</label>
                        <div class="col-md-8">
                            <div class="taskCrowd mb15" id="backupCrowd"></div>
                        </div>
                    </div>`
        }
        // 组合时间策略类型选择框
        const getBackupTypeSelectContent = () => {
            // 根据自定义策略类型来生成option
            let html = '';
            for (let key in STRATEGY_TYPE_CONFIG) {
                html += `<option value="${key}">${STRATEGY_TYPE_CONFIG[key]}</option>`;
            }
            return `<div class="form-group">
                        <label class="control-label col-md-2">` + LANG.UI_BACKUP_TYPE + `</label>
                        <div class="col-md-3">
                            <select class="form-control select2me input-sm" id="backuptype">${html}</select>
                        </div>
                    </div>`;
        }
        // 组合时间策略配置框
        const getBackupModeContent = () => {
            return `<div class="form-group setStrategy">
                        <label class="control-label col-md-2 form-group-label"><span class="required">* </span> ` + LANG.UI_GLOBAL_STRATEGY_SET_STRATEGY + `</label>
                        <div class="col-md-10 form-group-content"  id="strategymode">
                        ${getModeButton(settings.mode, MODE_LIST, settings.storage_type)}
                        ${getModeTips(settings.mode, MODE_TIPS, settings.storage_type)}
                        </div>
                        <div class="col-md-offset-2 col-md-10">
                            <div class="portlet">
                                <div class="portlet-body">
                                    <div class="panel-group accordion" id="backupTimestrategy"></div>
                                </div>
                            </div>
                        </div>
                    </div>`;
        }
        const timePickerInput = `<div class="form-group setOnceTime display-hide">
                    <div class="onceTime-content">
                        <label class="control-label col-md-2">
                            <span class="required">* </span>
                            `+ LANG.UI_GLOBAL_STRATEGY_SET_TIME + `
                        </label>
                        <div class="onceTime-content-input">
                            <div class="input-group date form_datetime">
                                <input type="text" size="16" readonly id="oncetime" class="form-control input-sm" style="width: 160px;">
                                <span class="input-group-btn">
                                    <button class="btn default input-sm" id="resetdate" type="button"><i class="fa fa-times"></i></button>
                                    <button class="btn default date-set input-sm" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                </span>
                            </div>
                        </div>
                        <div class="onceTime-content-tips">
                            <a class="popovers " data-container="body" data-trigger="hover" data-placement="right" data-content="` + LANG.UI_GLOBAL_STRATEGY_DELTASK_ONETIME + `" data-original-title="" title="">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>
                </div>`;
        // 组合时间策略类型勾选框
        const getModeButton = (mode, list, storage_type = 0) => {
            // 根据传入的mode生成复选框
            let html = "";
            let timeMode = mode;
            if (storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
                timeMode = mode.filter(item => item !== 9);
            }
            timeMode.forEach((element, index) => {
                // 操作系统、虚拟机云存储屏蔽永久增量
                if (element == 9 && settings.storage_type == CONF.BD_STORAGE_TYPE.CLOUD && (settings.module_type_des == 'os' || settings.module_type_des == 'vm')) {
                } else {
                    html += `<label style="padding-right: 20px;" class="timeMode${element}"><input type="checkbox" data-checkbox="icheckbox_square-blue" data-mode="${element}" class="icheck">${list[element]}</label>`;
                }
            });
            return html;
        }
        // 组合可选策略类型提示信息
        const getModeTips = (mode, tips, storage_type = 0) => {
            let html = "";
            let timeMode = mode;
            if (storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
                timeMode = mode.filter(item => item !== 9);
            }
            // 获取备份备份模式对应的提示
            timeMode.forEach((element, index) => {
                html += tips[element] + "<br><br>";
            });
            // 完备增备差备都有增加一个特殊提示
            if ([1, 2, 3].every(element => timeMode.includes(element))) {
                html += tips[5] + "<br><br>";
            }
            return `<a class="popovers time-strategy-tips all-strategy-tips" data-container="body" data-trigger="hover" data-placement="right" data-html="true" data-content="${html}"><i class="viconfont vicon-tishi"></i></a>`
        }
        /******初始化时间策略内容 */


        /******初始化限速策略内容 */
        // 组合限速策略内容
        const getSpeedContent = () => {
            let html = `<div class="form-group strategy_speed-content ${settings.strategy_group_flag ? 'display-none' : ''}">
                            <div class="strategy_speed-div">
                                <div class="accordion strategyOne">
                                    <div class="panel panel-default strategy-panel">
                                    ${getSpeedPanelHeading()}
                                    ${getSpeedBody()}
                                    </div>
                                </div>
                            </div>
                        </div>`;
            return html;
        }
        // 组合限速策略展开头
        const getSpeedPanelHeading = () => {
            return `<div class="panel-heading">
                        <h4 class="panel-title">
                            <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
                                <i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
                                <span class="font-green-seagreen">` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE + `</span>
                                <span class=" strategyDes speedlimitDes"></span>
                            </a>
                        </h4>
                    </div>`
        };
        // 组合限速策略展开内容
        const getSpeedBody = () => {
            return `<div id="speed" class="panel-collapse collapse ">
                        <div class="panel-body">
                        ${getSpeedTypeSelector()}
                        ${getTaskLevel()}
                        ${getSelectSpeedForm()}
                        ${getCustomSpeedForm()}
                        </div>
                    </div>`;
        }
        // 组合限速策略选择框
        const getSpeedTypeSelector = () => {
            return `<div class="form-group">
                        <label class="control-label col-md-2">` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPES + `</label>
                        <div class="col-md-6">
                            <div class="">
                                <select class="bs-select dataBorder form-control" data-show-subtext="true" id="speedtypeselect">
                                    <option value="2">` + LANG.UI_GLOBAL_STRATEGY_AUTO + `</option>
                                    <option value="1">` + LANG.UI_GLOBAL_STRATEGY_SPEED_CHOOSE_NAME + `</option>
                                </select>
                                <a class="popovers ml12 speed-type-tip"  data-container="body" data-trigger="hover" data-placement="right"
                                data-content="` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_CHOOSE_TIPS + `" data-original-title="" title="">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                    </div>`;
        }
        // 组合任务等级选择框
        const getTaskLevel = () => {
            return `<div class="form-group display-none" id="task_type_global_speed_strategy">
                        <label class="control-label col-md-2">` + LANG.UI_GLOBAL_STRATEGY_TASK_LEVEL + `</label>
                        <div class="col-md-6">
                            <div class="">
                                <select class="bs-select dataBorder form-control" data-show-subtext="true" id="tasklevelselect">${getOptions(TASK_LEVEL, CONF.TASK_LEVEL)}</select>
                                <a class="popovers ml12"  data-container="body" data-trigger="hover" data-placement="right"
                                data-content="` + LANG.UI_GLOBAL_STRATEGY_TASK_LEVEL_TIPS + `" data-original-title="" title="">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                    </div>`;
        }
        // 组合全局限速策略表格信息
        const getSelectSpeedForm = () => {
            return `<div id="show_type_1" class="display-none">
                        <div class="form-group">
                            <label class="control-label col-md-2">` + LANG.UI_GLOBAL_STRATEGY_SELECT_STRATEGY + `</label>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-12" style="padding: 0">
                                        <div class="portlet box blue-hoki">
                                            <div class="portlet-body">
                                                <div class="row">
                                                    <div class="col-md-12 strategy-table">
                                                        <table id="table"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
        }
        // 组合自定义限速策略配置
        const getCustomSpeedForm = () => {
            return `<div id="show_type_2">
                        <div class="form-group setSpeedStrategy">
                            <label class="control-label col-md-2">` + LANG.UI_GLOBAL_STRATEGY_SET_STRATEGY + `</label>
                            <div class="col-md-10">
                                <div class="portlet">
                                    <div class="portlet-body">
                                        <div class="panel-group accordion" id="speedstrategy">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" id="speedItemSet">
                                <label class="control-label col-md-2">` + LANG.UI_GLOBAL_STRATEGY_MESSAGE + `</label>
                                <div class="col-md-10">
                                    <ul id="speedList"></ul>
                                </div>
                            </div>
                        </div>
                    </div>`;
        }
        /******初始化限速策略内容 */


        /******初始化存储策略内容 */
        // 组合保留策略
        const getStoreContent = () => {
            return `<div class="form-group strategy_store-content ${settings.strategy_group_flag ? 'display-none' : ''}">
                        <div class="strategy_store-div">
                            <div class="accordion strategyOne store-strategy-form">
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#store" aria-expanded="true">
                                                <i class="viconfont vicon-cunchu font-green-seagreen"></i>
                                                <span class="font-green-seagreen">` + LANG.UI_GLOBAL_STRATEGY_STORE + `</span>
                                                <span class="strategyDes storeDes"></span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="store" class="panel-collapse collapse ">
                                    ${getStorePanelBody()}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
        }
        // 组合保留策略配置
        const getStorePanelBody = () => {
            return `<div class="panel-body">
                        <div class="col-md-12 store-content">
                            <div class="form-group deduplicationDiv">
                            ${getDuplicationDiv(settings.deduplication)}
                            </div>
                            ${getCompressDiv()}
                            ${getEncryptDiv()}
                            ${getEncryptMethod()}
                            ${getPasswordDiv()}
                        </div>
                    </div>`;
        }
        // 组合重复数据删除开关
        const getDuplicationDiv = (show) => {
            if (!show || !CONF.FUNCTIONS.includes('dedupication')) {
                _dom.find('.deduplicationDiv').hide();
                return ``;
            }
            _dom.find('.deduplicationDiv').show();
            return `<label class="control-label col-md-2 deduplicationLabel form-group-top4-label">` + LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + `</label>
                        <div class="col-md-4"><input type="checkbox" id="deduplicationCheck" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="`+ LANG.UI_PUBLIC_ON_ONE + `" data-off-text="` + LANG.UI_PUBLIC_OFF_ONE + `">
                        <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="`+ LANG.UI_GLOBAL_STRATEGY_DEDUPLICATION_TIPS + `">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>`;
        }
        // 组合压缩存储配置
        const getCompressDiv = () => {
            return `<div class="form-group compressDiv">
                        <label class="control-label col-md-2 compressLabel form-group-top4-label">`+ LANG.UI_GLOBAL_STRATEGY_COMPRESS + `</label>
                        <div class="col-md-4">
                            <input type="checkbox" id="compressCheck" checked class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="`+ LANG.UI_PUBLIC_ON_ONE + `" data-off-text="` + LANG.UI_PUBLIC_OFF_ONE + `">
                            <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="`+ LANG.UI_GLOBAL_STRATEGY_COMPRESS_TIPS + `">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>
                    <!-- 压缩等级选择 -->
                    <div class="form-group compressGradeDiv">
                        <label class="control-label col-md-2">`+ LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + `</label>
                        <div class="col-md-4">
                            <select class="form-control select2me input-sm" id="compressGrade">
                            ${getOptions(COMPRESS_METHOD, CONF.COMPRESS_METHOD)}
                            </select>
                        </div>
                    </div>`;
        }
        // 组合数据加密开关
        const getEncryptDiv = () => {
            return `<div class="form-group">
                        <label class="control-label col-md-2 encryptStorageLabel form-group-top4-label">`+ LANG.UI_BACKUP_DATA_ENCRYPT + `</label>
                        <div class="col-md-4">
                            <input type="checkbox" id="encryptStorageCheck" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="`+ LANG.UI_PUBLIC_ON_ONE + `" data-off-text="` + LANG.UI_PUBLIC_OFF_ONE + `">
                            <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="`+ LANG.UI_GLOBAL_STRATEGY_DATE_ENCRYPT_TIPS + `">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>`;
        };
        // 组合加密算法选择
        const getEncryptMethod = () => {
            let encrypt_method = { ...CONF.STORE_ENCRYPT_METHOD };
            if (CONF.LANGUAGE !== 'zh-cn' && CONF.LANGUAGE !== 'zh-tw') {
                delete encrypt_method['SM4'];
            }
            return `<div class="form-group storage-encrypt-div display-none">
                        <label class="control-label col-md-2 storage-encrypt-label">`+ LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_METHOD + `</label>
                        <div class="col-md-4">
                            <select class="form-control select2me input-sm" id="storageEncryptMethod">
                            ${getOptions(STORE_ENCRYPT_METHOD, encrypt_method)}
                            </select>
                        </div>
                    </div>`;
        };
        // 组合密码组合输入框
        const getPasswordDiv = () => {
            return `<div class="form-group display-none passwordModeDiv">
                        <label class="control-label col-md-2 passwordAutoLabel form-group-top4-label">`+ LANG.UI_BACKUP_DATA_AUTO_CREATE_PASSWORD + `</label>
                        <div class="col-md-4">
                            <input type="checkbox" id="passwordAutocheck" class="make-switch" checked data-on-color="primary" data-off-color="info" data-size="small" data-on-text="`+ LANG.UI_PUBLIC_ON_ONE + `" data-off-text="` + LANG.UI_PUBLIC_OFF_ONE + `">
                        </div>
                    </div>
                    <div class="form-group display-none passwordDiv">
                        <label class="control-label col-md-2 passwordlabel">`+ LANG.UI_GLOBAL_STRATEGY_PASSWORD + `</label>
                        <div class="col-md-4" style="position: relative;">
                            <input type="password" autocomplete="off" maxlength="128" class="form-control input-sm" id="password" placeholder="" />
                            <button type="button" class="btn btn-link show-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                                <i class="viconfont vicon-a-lujing8232"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group display-none passwordDiv">
                        <label class="control-label col-md-2 repasswordlabel">`+ LANG.UI_GLOBAL_STRATEGY_PASSWORD_CONFIRM + `</label>
                        <div class="col-md-4" style="position: relative;">
                            <input type="password" autocomplete="off" maxlength="128" class="form-control input-sm" id="repassword" placeholder="" />
                            <button type="button" class="btn btn-link show-rePassword-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                                <i class="viconfont vicon-a-lujing8232"></i>
                            </button>
                        </div>
                        <div class="col-md-4 display-none passwordTips">`+ LANG.UI_GLOBAL_STRATEGY_PASSWORD_CONFIRM_ERROR + `</div>
                    </div>`;
        };
        /******初始化存储策略内容 */


        /******初始化保留策略内容 */
        // 组合保留策略
        const getReserveContent = () => {
            return `<div class="form-group strategy_reserve-content ${settings.strategy_group_flag ? 'display-none' : ''}">
                        <div class="strategy_reserve-div">
                            <div class="accordion strategyOne reserve-strategy-form">
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#reserve" aria-expanded="true">
                                                <i class="viconfont vicon-baoliucelve font-green-seagreen"></i>
                                                <span class="font-green-seagreen">`+ LANG.UI_STRATEGY_RESERVE + `</span>
                                                <span class="strategyDes reserveDes"></span>
                                            </a>
                                        </h4>
                                    </div>
                                    ${getReservePanelBody()}
                                </div>
                            </div>
                            <!-- 磁带策略开始 -->
                            <div class="accordion strategyOne tape-strategy-form display-hide">
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body"
                                               data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne"
                                               href="#tape" aria-expanded="true" data-original-title="" title="">
                                                <i class="iconfont icon-baoliu font-green-seagreen"></i>
                                                <span class="font-green-seagreen">`+ LANG.UI_COPY_STRATEGY_TAPE_STRATEGY + `</span>
                                                <span class="strategyDes tapeDes"
                                                      title="`+ LANG.UI_TAPE_USE_GROUP_STRATEGY + `">` + LANG.UI_TAPE_USE_GROUP_STRATEGY + `</span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="tape" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <!-- 磁带相关策略 -->
                                            <div class="form-group">
                                                <label class="control-label col-md-2">
                                                    `+ LANG.UI_TAPE_SELECT_GENERATE_STRATEGY + `
                                                </label>
                                                <div class="col-md-4 mt5">
                                                    <span class="generate"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-2">
                                                    `+ LANG.UI_TAPE_RESERVE_STRATEGY + `
                                                </label>
                                                <div class="col-md-4 mt5">
                                                    <span class="reserve"></span>
                                                </div>
                                            </div>
                                            <!-- 磁带相关策略END -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 磁带策略结束 -->
                        </div>
                    </div>
                    `;
        };
        // 组合保留策略配置
        const getReservePanelBody = () => {
            return `<div id="reserve" class="panel-collapse collapse">
                        <div class="panel-body">
                            <div class="form-group reserve_mode-div">
                            ${getReserveModeSelector(settings.storage_type)}
                            </div>
                            <div class="form-group reserve_type-div">
                            ${getReserveTypeSelector()}
                            </div>
                            ${getReserveNumInput()}
                            ${getReserveDayInput()}
                            <div class="form-group GFSdiv">
                            ${getGfsStrategy(settings.GFS)}
                            </div>
                        </div>
                    </div>`
        }
        // 组合保留策略类型选择
        const getReserveModeSelector = (storage_type = 0) => {
            let disable = "";
            if (storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
                disable = 'disabled';
            }
            // 数据库显示保留类型，MongoDB有按点保留，其他类型只能按链保留
            let strategy_mode = { ...CONF.RESERVE_STRATEGY_MODE };
            if ((settings.module_type == CONF.MODULE_TYPE.DB && settings.db_type !== CONF.DB_TYPE.MONGODB) || settings.module_type == CONF.MODULE_TYPE.M365) {
                delete strategy_mode['POINT'];
            }
            _dom.find('.reserve_mode-div').show()
            return `<label class="control-label col-md-2">` + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE + `</label>
                    <div class="col-md-4">
                        <select class="form-control select2me input-sm" id="reserveMode" ${disable}>
                        ${getOptions(RESERVE_MODE, strategy_mode, 'reserveMode')}
                        </select>
                    </div>`
        };
        // 组合保留策略方式选择
        const getReserveTypeSelector = () => {
            let newReserveType = {};
            let tips = LANG.UI_GLOBAL_STRATEGY_RESERVER_TIPS;
            // 只按个数保留提示
            if (settings.reserve_num_only_flag) {
                tips = LANG.UI_GLOBAL_STRATEGY_RESERVER_NUM_TIPS;
            }
            newReserveType = JSON.parse(JSON.stringify(CONF.RESERVE_TYPE));
            delete newReserveType.PERMANENT;
            return `<label class="control-label col-md-2">` + LANG.UI_GLOBAL_STRATEGY_DATA_RESERVE_TYPE + `</label>
                    <div class="col-md-4">
                        <select class="form-control select2me input-sm" id="reserveType">
                        ${getOptions(RESERVE_TYPE, newReserveType, 'reserveType')}
                        </select>
                    </div>
                    <div class="mt5">
                        <a class="popovers reserveTips1" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="${tips}">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                        <a class="popovers reserveTips2 display-none" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="`+ LANG.UI_GLOBAL_STRATEGY_PERMANENT_TIPS + `">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>`;
        };
        // 组合按个数保留输入框
        const getReserveNumInput = () => {
            return `<div class="form-group reserveNum">
                        <label class="control-label col-md-2">`+ LANG.UI_GLOBAL_STRATEGY_RESERVE_NUM + `</label>
                        <div class="col-md-2">
                            <div id="spinnerNum">
                                <div class="input-group spinner-group">
                                    <input id="spinnerNumInput" class="spinner-input form-control input-sm" maxlength="3">
                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                        <button type="button" class="btn spinner-up default input-sm" >
                                            <i class="fa fa-angle-up"></i>
                                        </button>
                                        <button type="button" class="btn spinner-down default input-sm">
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
        };
        // 组合按天数保留输入框
        const getReserveDayInput = () => {
            return `<div class="form-group reserveDay display-hide">
                        <label class="control-label col-md-2">`+ LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY + `</label>
                        <div class="col-md-2">
                            <div id="spinnerDay">
                                <div class="input-group spinner-group">
                                    <input id="spinnerDayInput" class="spinner-input form-control" maxlength="3">
                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                        <button type="button" class="btn spinner-up default">
                                            <i class="fa fa-angle-up"></i>
                                        </button>
                                        <button type="button" class="btn spinner-down default">
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
        };
        // 组合gfs保留策略
        const getGfsStrategy = (showGFS) => {
            if (showGFS !== true) {
                return ``;
            }
            return `<div class="form-group">
                        <label class="control-label col-md-2 GFSLable form-group-label">`+ LANG.UI_SETTING_GFS_RETENTION_POLICY + `</label>
                        <div class="col-md-2 form-group-content">
                            <input type="checkbox" id="GFSflag" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="`+ LANG.UI_PUBLIC_ON_ONE + `" data-off-text="` + LANG.UI_PUBLIC_OFF_ONE + `">
                            <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-html="true" data-content="`+ LANG.UI_GLOBAL_STRATEGY_GFS_RESERVE_STRATEGY_TIPS + `">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div id="GFSDiv" class="display-none"></div>`;
        }
        /******初始化保留策略内容 */

        /******初始化时间策略事件 */
        const addTimeListeners = () => {
            // 切换时间策略备份模式
            _dom.find('#strategymode input').each(function () {
                $(this).on('ifChecked ifUnchecked ', getTimeDes);
            });
            _dom.find('#oncetime').on('change', getTimeDes); // 一次性备份输入修改 渲染描述
            _dom.find('#backuptype').on('change', backupTypeHandler);
            _dom.find('#resetDate').on('click', () => {
                _dom.find('#oncetime').val('');
                getTimeDes();
            });
            _dom.find('#strategymode').find('.icheck').on('ifClicked', timeModeClick);
        }
        // 获取时间策略信息描述
        let getTimeDes = () => {
            if (!_dom.length || settings.recovery_time_flag) {
                return;
            }
            let des = "";
            let diffDes = "";
            let backupType = _dom.find('#backuptype').val(); // 时间策略类型
            let strategyConfig = _dom.find('#backupTimestrategy').getStrategyConfig(); // 获取时间策略配置
            let strategyMode = _dom.find('#strategymode').find('input'); // 获取备份类型
            let modeDescMap = {
                1: strategyConfig.fullInfo ? strategyConfig.fullInfo.des : '', // 完备
                2: strategyConfig.incrInfo ? strategyConfig.incrInfo.des : '', // 增量
                3: strategyConfig.diffInfo ? strategyConfig.diffInfo.des : '', // 差异
                4: strategyConfig.logInfo ? strategyConfig.logInfo.des : '', // 日志
                9: strategyConfig.pIncrInfo ? strategyConfig.pIncrInfo.des : '', // 永久增量
            };
            // 处理策略模式 获取备份类型的对应描述
            switch (backupType) {
                case STRATEGY_TYPE.STRATEGY:
                    strategyMode.each((i, el) => {
                        let check = $(el).is(':checked');
                        if (check) {
                            let mode = $(el).data('mode');
                            if (mode in modeDescMap) {
                                des += modeDescMap[mode] + ". ";
                                diffDes += modeDescMap[mode] + "<br>";
                            }
                        }
                    });
                    break;
                case STRATEGY_TYPE.ONCE:
                    let onceTime = _dom.find('#oncetime').val();
                    // 确保 onceTime 是安全的输入
                    if (onceTime && onceTime.trim() !== '') {
                        des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + onceTime;
                        diffDes += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + onceTime;
                    }
                    break;
                case STRATEGY_TYPE.MANUAL:
                    des = LANG.UI_BACKUP_MANUAL;
                    diffDes = LANG.UI_BACKUP_MANUAL;
                    break;
                case STRATEGY_TYPE.IMMEDIATE:
                    des = LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE;
                    diffDes = LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE;
                    break;
            }
            // 在tab头渲染描述信息
            _dom.find('.backupTimeDes').html(des);
            _dom.find('.backupTimeDes').prop('title', des);
        };
        // 切换时间策略类型
        let backupTypeHandler = () => {
            let backupType = _dom.find('#backuptype').val();
            // 默认可选择类型，且默认为个数保留
            _dom.find('#reserveType').removeAttr("disabled");
            _dom.find('#spinnerNum').spinner('enable');
            _dom.find('#reserveType').val(CONF.RESERVE_TYPE.NUM)
            _dom.find('#reserveType option[value=3]').hide();
            _dom.find('.reserveNum').show();
            _dom.find('#reserveMode').removeAttr("disabled");
            // 不同时间类型展示不同配置
            switch (backupType) {
                case STRATEGY_TYPE.STRATEGY:
                    //按策略备份
                    _dom.find('.setStrategy').show();
                    _dom.find('.setOnceTime').hide();
                    _dom.find('#GFSflag').bootstrapSwitch('disabled', false);
                    // 切换到策略有默认勾选永久增量，需要禁用保留类型选择
                    let pincrCheck = _dom.find('#strategymode input[data-mode=9]').is(':checked');
                    if (pincrCheck && settings.permanent) {
                        // exchang永久增量显示永久保留
                        _dom.find('#reserveType').append('<option value="3" selected>' + LANG.UI_FILE_PERMANENT + '</option>');
                        _dom.find('#reserveType').prop("disabled", "disabled");
                        _dom.find('.reserveDay').hide();
                        _dom.find('.reserveNum').hide();
                        _dom.find('.reserveTips1').hide();
                        _dom.find('.reserveTips2').show();
                        initReserveDes(settings);
                    }
                    break;
                case STRATEGY_TYPE.ONCE:
                    //一次性备份
                    _dom.find('.setStrategy').hide();
                    _dom.find('.setOnceTime').show();
                    _dom.find('#reserveType').val(CONF.RESERVE_TYPE.NUM).prop("disabled", "disabled");
                    _dom.find('.reserveDay').hide();
                    _dom.find('.reserveNum').show();
                    _dom.find('#spinnerNum').spinner('value', 1);
                    _dom.find('#spinnerNum').spinner('disable');
                    _dom.find('#GFSflag').bootstrapSwitch('state', false);
                    _dom.find('#GFSflag').bootstrapSwitch('disabled', true);
                    break;
                case STRATEGY_TYPE.MANUAL:
                    // 手动启动
                    _dom.find('.setStrategy').hide();
                    _dom.find('.setOnceTime').hide();
                    break;
                case STRATEGY_TYPE.IMMEDIATE:
                    // 立即启动
                    _dom.find('.setStrategy').hide();
                    _dom.find('.setOnceTime').hide();
                    break;
            }
            initReserveDes(settings);
        }
        //选中或取消某个时间策略
        let timeModeClick = function (event) {
            const strategyMode = _dom.find('#strategymode');
            const strategyAccordion = _dom.find('#stragegyaccordion');
            let mode = parseInt($(this).data('mode'), 10);
            const isChecked = event.target.checked;
            // 默认显示天数保留、删除永久保留选项
            if (settings.permanent) {
                _dom.find('.reserveTips1').hide();
                _dom.find('.reserveTips2').show();
                _dom.find('#reserveType').val(CONF.RESERVE_TYPE.NUM).removeAttr('disabled');
                _dom.find('#reserveType option[value=3]').remove();
                _dom.find('.reserveDay').hide();
                _dom.find('.reserveNum').show();
            }
            if (settings.GFS) {
                _dom.find('#GFSflag').bootstrapSwitch("disabled", false);//禁用
            }
            if (isChecked) {
                //如果是取消选中
                strategyAccordion.find(`.strategy-panel[data-mode='${mode}']`).hide();
            } else {
                //如果是选中
                strategyAccordion.find(`.strategy-panel[data-mode='${mode}']`).show();
            }
            switch (mode) {
                //完全备份
                case CONF.TIME_STRATEGY_MODE.FULL:
                    if (isChecked) {
                        //取消完备同时取消增量和差异
                        strategyMode.find('input[data-mode=2]').iCheck('uncheck');
                        strategyAccordion.find('.strategy-panel[data-mode=2]').hide();
                        strategyMode.find('input[data-mode=3]').iCheck('uncheck');
                        strategyAccordion.find('.strategy-panel[data-mode=3]').hide();
                    } else {
                        //选中完备同时取消永久增量
                        strategyMode.find('input[data-mode=9]').iCheck('uncheck');
                        strategyAccordion.find('.strategy-panel[data-mode=9]').hide();
                    }
                    break;
                case CONF.TIME_STRATEGY_MODE.INCR:
                    //增量备份
                    if (!isChecked) {
                        //选中增量同时选中完备 取消差异和永久增量
                        strategyMode.find('input[data-mode=1]').iCheck('check');
                        strategyAccordion.find('.strategy-panel[data-mode=1]').show();
                        strategyMode.find('input[data-mode=9]').iCheck('uncheck');
                        strategyAccordion.find('.strategy-panel[data-mode=9]').hide();
                        // mongoDB可以增备差备同时做
                        if (settings.module_type_des != 'db') {
                            strategyMode.find('input[data-mode=3]').iCheck('uncheck');
                            strategyAccordion.find('.strategy-panel[data-mode=3]').hide();
                        }
                    }
                    break;
                case CONF.TIME_STRATEGY_MODE.DIFF:
                    //差异备份
                    if (!isChecked) {
                        //选中差异同时选中完备 取消增量和永久增量
                        strategyMode.find('input[data-mode=1]').iCheck('check');
                        strategyAccordion.find('.strategy-panel[data-mode=1]').show();
                        strategyMode.find('input[data-mode=9]').iCheck('uncheck');
                        strategyAccordion.find('.strategy-panel[data-mode=9]').hide();
                        // mongoDB可以增备差备同时做
                        if (settings.module_type_des != 'db') {
                            strategyMode.find('input[data-mode=2]').iCheck('uncheck');
                            strategyAccordion.find('.strategy-panel[data-mode=2]').hide();
                        }
                    }
                    break;
                // 日志备份
                case CONF.TIME_STRATEGY_MODE.DB_LOG:
                    if (!isChecked) {
                        strategyMode.find('input[data-mode=1]').iCheck('check');
                        // 数据库tidb允许勾选日志备份，但是显示配置信息
                        if (settings.db_type == '' || (settings.db_type != CONF.DB_TYPE.TIDB && settings.module_type_des != 'db')) {
                            strategyAccordion.find('.strategy-panel[data-mode=1]').show();
                        }
                    }
                    break;
                case CONF.TIME_STRATEGY_MODE.PER_INCR:
                    //永久增量
                    if (!isChecked) {
                        //选中永久增量其他全部取消
                        strategyMode.find('input[data-mode=1]').iCheck('uncheck');
                        strategyAccordion.find('.strategy-panel[data-mode=1]').hide();
                        strategyMode.find('input[data-mode=2]').iCheck('uncheck');
                        strategyAccordion.find('.strategy-panel[data-mode=2]').hide();
                        strategyMode.find('input[data-mode=3]').iCheck('uncheck');
                        strategyAccordion.find('.strategy-panel[data-mode=3]').hide();
                        // exchang永久增量显示永久保留
                        if (settings.permanent) {
                            _dom.find('#reserveType').append('<option value="3" selected>' + LANG.UI_FILE_PERMANENT + '</option>');
                            _dom.find('#reserveType').prop("disabled", "disabled");
                            _dom.find('.reserveDay').hide();
                            _dom.find('.reserveNum').hide();
                            _dom.find('.reserveTips1').hide();
                            _dom.find('.reserveTips2').show();
                            initReserveDes(settings);
                        }
                    }
                    break;
            }
            // 更新策略描述
            initReserveDes(settings);
        }

        // 初始化日期选择器
        let initDataTimePicker = function () {
            if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
                //英文独有的
                _dom.find(".form_datetime").datetimepicker({
                    autoclose: true,
                    isRTL: Metronic.isRTL(),
                    format: "yyyy-mm-dd hh:ii:ss",
                    pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                    startDate: new Date(),
                }).on('show', function (e) {
                    //移除滚动条
                    $('.form .form-body').css({ 'overflow': 'hidden' }); // 禁用滚动
                    $('.bakuptab .tab-pane .row-stepthree .tabbable-custom .tab-content').css({ 'overflow': 'hidden' }); // 禁用滚动
                }).on('hide', function (e) {
                    //还原滚动条
                    $('.form .form-body').css({ 'overflow': 'auto' }); // 启用滚动
                    $('.bakuptab .tab-pane .row-stepthree .tabbable-custom .tab-content').css({ 'overflow': 'auto' }); // 启用滚动
                });
            } else {
                _dom.find(".form_datetime").datetimepicker({
                    language: 'zh-CN',
                    autoclose: true,
                    isRTL: Metronic.isRTL(),
                    format: "yyyy-MM-dd hh:ii:ss",
                    pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                    startDate: new Date(),

                }).on('show', function (e) {
                    //移除滚动条
                    $('.form .form-body').css({ 'overflow': 'hidden' }); // 禁用滚动
                    $('.bakuptab .tab-pane .row-stepthree .tabbable-custom .tab-content').css({ 'overflow': 'hidden' }); // 禁用滚动
                }).on('hide', function (e) {
                    //还原滚动条
                    $('.form .form-body').css({ 'overflow': 'auto' }); // 启用滚动
                    $('.bakuptab .tab-pane .row-stepthree .tabbable-custom .tab-content').css({ 'overflow': 'auto' }); // 启用滚动
                });
            }
        }
        /**
         * 恢复策略切换启动时间
         */
        let recoveryTypeHandler = function () {
            let recoveryType = $(this).val();
            // 指定时间恢复
            switch (recoveryType) {
                case STRATEGY_TYPE_RECOVERY.ATTIME:
                    // 指定时间
                    _dom.find('.setOnceTime').show();
                    _dom.find('.recovery_strategy-div').hide();
                    break;
                case STRATEGY_TYPE_RECOVERY.IMMEDIATE:
                    // 立即启动
                    _dom.find('.setOnceTime').hide();
                    _dom.find('.recovery_strategy-div').hide();
                    break;
                case STRATEGY_TYPE_RECOVERY.STRATEGY:
                    // 按策略恢复
                    _dom.find('.recovery_strategy-div').show();
                    _dom.find('.setOnceTime').hide();
                    break;
            }
            // 策略配置
            initRecoveryTimeDes();
        }
        /**
         * 恢复策略配置信息
         */
        const initRecoveryTimeDes = () => {
            let recoveryType = _dom.find('#recoveryType').val();
            let des = "";
            switch (recoveryType) {
                // 指定时间恢复
                case STRATEGY_TYPE_RECOVERY.ATTIME:
                    let startTime = _dom.find('#oncetime').val();
                    des = LANG.UI_GLOBAL_STRATEGY_TYPE_START_AT_TIME + ':' + startTime;
                    break;
                // 立即启动
                case STRATEGY_TYPE_RECOVERY.IMMEDIATE:
                    des = LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE;
                    break;
                // 按策略恢复
                case STRATEGY_TYPE_RECOVERY.STRATEGY:
                    let strategy = _dom.find('#recoveryTimestrategy').getStrategyConfig();
                    des = strategy.recInfo.des;
                    break;
            }
            _dom.find('.recoveryTimeDes').html(des);
            _dom.find('.recoveryTimeDes').prop('title', des);
        }
        // 组合时间策略类型选择框
        const getRecoveryTypeSelectContent = () => {
            // 根据自定义策略类型来生成option
            let html = '';
            for (let key in STRATEGY_TYPE_RECOERY_CONFIG) {
                if (settings.recovery_type.includes(key)) {
                    html += `<option value="${key}">${STRATEGY_TYPE_RECOERY_CONFIG[key]}</option>`;
                }
            }
            return `<div class="form-group">
                        <label class="control-label col-md-2">` + LANG.UI_GLOBAL_STRATEGY_TYPE_SELECT_START_TIME + `</label>
                        <div class="col-md-3">
                            <select class="form-control select2me input-sm" id="recoveryType">${html}</select>
                        </div>
                    </div>`;
        }
        const getRecoverStrategyContent = () => {
            if (!settings.recovery_type.includes(STRATEGY_TYPE_RECOVERY.STRATEGY)) {
                return '';
            }
            let dispaly = 'display-none';
            if (settings.recovery_type.length == 1) {
                dispaly = '';
            }
            return `<div class="form-group recovery_strategy-div ${dispaly}">
                        <label class="control-label col-md-2 form-group-label"><span class="required">* </span> ` + LANG.UI_GLOBAL_STRATEGY_SET_STRATEGY + `</label>
                        <div class="col-md-10">
                            <div class="portlet">
                                <div class="portlet-body">
                                    <div class="panel-group accordion" id="recoveryTimestrategy"></div>
                                </div>
                            </div>
                        </div>
                    </div>`;
        };
        // 生成策略配置
        const getRecoveryTimePanelBody = () => {
            return `<div id="backupTime" class="panel-collapse collapse in">
                        <div class="panel-body">
                        ${getRecoveryTypeSelectContent()}
                        ${settings.recovery_type.includes(STRATEGY_TYPE_RECOVERY.ATTIME) ? timePickerInput : ''}
                        ${getRecoverStrategyContent()}
                        </div>
                    </div>`;
        }
        // 初始化恢复策略配置
        const initRecoveryTimeStrategy = () => {
            if (!settings.time) {
                return true;
            }
            let html = `<div class="form-group strategy_time-content">
                            <div class="strategy_time-div">
                                <div class="accordion strategyOne">
                                    <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupTime" aria-expanded="true">
                                                <i class="viconfont vicon-shijian2 font-green-seagreen"></i>
                                                <span class="font-green-seagreen">` + LANG.UI_STRATEGY_TIME + `</span>
                                                <span class="strategyDes recoveryTimeDes"></span>
                                            </a>
                                        </h4>
                                    </div>
                                    ${getRecoveryTimePanelBody()}
                                    </div>
                                </div>
                            </div>
                        </div>`;
            _dom.append(html);
            let strategy = [{
                mode: 7,
                strategy_type: 2,
                days: [0, 0, 0, 0, 1, 0, 0],
                start_time: '23:00:00',
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: '23:59:59'
            }];
            if (settings.strategy) {
                strategy[0] = {
                    mode: 7,
                    strategy_type: settings.strategy.time.strategy.time_type,
                    days: settings.strategy.time.strategy.days,
                    start_time: settings.strategy.time.strategy.start_time,
                    roll_flag: false,
                    roll_interval: '01:00:00',
                    roll_end_time: '23:59:59'
                }
            }
            //延迟设置,因为这里icheck会默认修改里面的选中事件
            $('#recoveryTimestrategy').strategy({
                dom: $('#recoveryTimestrategy'),
                config: strategy,
                backup_flag: 2
            });
            // 初始化时间策略模式提示
            _dom.find('.popovers').popover();
            // 初始化开关
            $('.make-switch').bootstrapSwitch();
            initDataTimePicker();
            initRecoveryTimeDes();
            _dom.find('#recoveryType').on('change', recoveryTypeHandler);
            _dom.find('#oncetime').on('change', initRecoveryTimeDes); // 一次性备份输入修改 渲染描述
            _dom.find('#resetDate').on('click', () => {
                _dom.find('#oncetime').val('');
                initRecoveryTimeDes();
            });
            _dom.find('#recoveryTimestrategy .rollDiv').hide(); //隐藏滚动执行
        }
        // 初始化时间策略
        let initTimeStrategy = () => {
            if (!settings.time) {
                return true;
            }
            let html = '';
            if (settings.strategy_group_flag) {
                html = `<label class="control-label col-md-3 form-group-label">${LANG.UI_GLOBAL_STRATEGY_TIME}</label>
                        <div class="col-md-8" style="height: unset;">
                            <div class="form-group-content" style="margin-bottom: 12px">
                                <input type="checkbox" id="timeStrategyCheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="${LANG.UI_PUBLIC_ON_ONE}" data-off-text="${LANG.UI_PUBLIC_OFF_ONE}">
                                <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="${LANG.UI_GLOBAL_STRATEGY_BACKUP_TIME_TIPS}">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                            ${getTimeContent()}
                        </div>`;
            } else {
                html = getTimeContent();
            }
            _dom.append(html);
            initDataTimePicker();
            // 初始化icheck
            _dom.find(`.icheck`).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue strategy-radio-custom',
                increaseArea: '20%'
            });
            // 初始化开关
            $('.make-switch').bootstrapSwitch();
            // 初始化时间策略模式提示
            _dom.find('.popovers').popover();
            // 发起请求获取备份时间配置信息
            pAjaxRequest({}, "/api/v1/jobs/time/crow/list", "GET", function (res) {
                let suggestInfo = res.data.suggest_time;
                // 配置默认时间策略
                defaultTimeStrategyConfig.forEach((config, index) => {
                    defaultTimeStrategy[index] = {
                        ...config,
                        days: index == 0 ? [0, 0, 0, 0, 1, 0, 0] : [],
                        frequency: '',
                        start_time: suggestInfo.start_time,
                        roll_flag: false,
                        roll_interval: '01:00:00',
                        roll_end_time: suggestInfo.roll_end_time,
                    };
                });
                // 初始化任务分布区间
                _dom.find('#backupCrowd').taskCrowd({ timeList: res.data.time_list, showFlag: res.data.show_flag });
                if (settings.strategy) { //如果带策略配置就渲染数据
                    renderTime(settings.strategy);
                    if (settings.strategy.type != 'strategy') {
                        // 初始化时间类型配置
                        _dom.find('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: defaultTimeStrategy, display: defaultTimeDisplay, backup_flag: 1 });
                    }
                } else {
                    // 初始化时间类型配置
                    _dom.find('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: defaultTimeStrategy, display: defaultTimeDisplay, backup_flag: 1 });
                }
            }, true);
            // 提供方法入口供其他方法调用
            $.fn.initBackupStrategy.getModeButton = getModeButton;
            $.fn.initBackupStrategy.getModeTips = getModeTips;
            $.fn.initBackupStrategy.addTimeListeners = addTimeListeners;
            $.fn.initBackupStrategy.getGfsStrategy = getGfsStrategy;
        }
        /******初始化时间策略事件 */
        /******初始化限速策略事件 */
        let addSpeedListener = () => {
            // 切换限速策略获取配置方式
            _dom.find('#speedtypeselect').on('change', function () {
                //获取所有文本内容
                let value = $(this).val();
                _dom.find('.speedlimitDes').html('');
                // 自定义限速策略
                if (value == 1) {
                    _dom.find('#show_type_1').show();
                    _dom.find('#show_type_2').hide();
                    _dom.find('#task_type_global_speed_strategy').show();
                    // 修改限速策略tab展示信息
                    modifyDelStyle();
                } else { // 选择全局限速策略
                    _dom.find('#show_type_2').show();
                    _dom.find('#show_type_1').hide();
                    // 不显示任务级别
                    _dom.find('#task_type_global_speed_strategy').hide();
                    // 修改限速策略tab展示信息
                    getCustomSpeedNum();
                }
            })
        }
        // 初始化全局限速列表 及相应事件
        let initSpeedGird = (uuid = null) => {
            let strategy_uuid = uuid;
            // 策略管理详情显示
            let lastIndex = [-1, -1];
            // 详情展示
            let current_detail = function (index, data, element) {
                // 控制只显示一个
                if (index != lastIndex[1]) {
                    lastIndex.push(index);
                    $('.strategy-table #table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                    lastIndex.splice(0, 1);
                }
                let content = '<table><tbody>';
                content += '<tr><td style="width:100px;">' + LANG.UI_PLATFORM_ASSOCIA_TASK + '：</td><td>' + data.job_list + '</td></tr>';
                content += '<tr><td style="width:100px;">' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE + '：</td><td>' + data.strategy_type + '</td></tr>';
                content += '<tr><td style="width:100px;">' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SETTING + '：</td><td>' + data.detail + '</td></tr>';
                content += '</tbody></table>';
                $(element).append(content);
            }
            // 全局限速表格配置信息
            let options = {
                pagination: true,
                pageList: [5, 10, 25, 50],
                detailView: true,
                resizable: false,
                detailFormatter: current_detail, //详情展开
                singleSelect: true,
                vin_url: "/api/v1/storages/global_speed",
                vin_method: "GET",
                vin_params: function () {
                    if (strategy_uuid && strategy_uuid != '') {
                        return {
                            uuid: strategy_uuid
                        }
                    }
                    return {};
                },
                onPostBody: function (rowData) {
                    let des = '', titleDes = '';
                    // 获取配置信息描述
                    let pData = [];
                    for (let j in rowData) {
                        if (rowData[j].checked) {
                            pData = rowData[j].detail;
                            pData = pData.split('</br>');
                        }
                    }
                    if (pData.length != 0) {
                        des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + pData.length;
                    }

                    for (let i in pData) {
                        titleDes += pData[i] + '. ' + "\n";
                    }
                    if (uuid) {
                        // 渲染限速配置描述
                        _dom.find('.speedlimitDes').html(des);
                        _dom.find('.speedlimitDes').prop('title', titleDes);
                    }
                },
                columns: [{
                    field: 'checked',
                    checkbox: true,
                    sortable: false,
                },
                {
                    field: 'name',
                    title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_NAME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'strategy_type',
                    title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'detail',
                    title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SETTING,
                    sortable: true,
                    align: 'center',
                    formatter: (detailStr) => {
                        // bug#29238 title显示</br>
                        if (!detailStr) {
                            return detailStr;
                        }
                        return `<span title="${detailStr.split('</br>').join('\n')}">${detailStr}</span>`
                    },
                },
                ],
                onCheck: function () {
                    modifyDelStyle();
                    strategy_uuid = '';
                },
                onUncheck: function () {
                    modifyDelStyle();
                    strategy_uuid = '';
                },
            }
            _dom.find('.strategy-table #table').baseTableConfig().init(options);
        }
        // 修改限速策略展开头显示信息  针对选择全局限速
        let modifyDelStyle = () => {
            let selectedRow = _dom.find('.strategy-table #table').bootstrapTable('getSelections');
            _dom.find('.speedTips').popover();	   //初始化tips
            let titleDes = "";
            let des = "";
            if (selectedRow.length == 1) {
                // 选中复选框
                let rowData = selectedRow[0].detail;
                rowData = rowData.split('</br>');
                if (rowData.length != 0) {
                    des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + rowData.length;
                }

                for (let i in rowData) {
                    titleDes += rowData[i] + '. ';
                }
                // 渲染限速信息
                _dom.find('.speedlimitDes').html(des);
                _dom.find('.speedlimitDes').prop('title', titleDes);
            } else {// 渲染限速信息
                _dom.find('.speedlimitDes').html(des);
                _dom.find('.speedlimitDes').prop('title', titleDes);
            }
        };
        // 获取自定义限速描述
        let getCustomSpeedNum = () => {
            let titleDes = "";
            let des = "";
            let list = _dom.find('.list-group-item__speed'); // 配置限速策略信息
            if (list.length > 0) {
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + list.length;
            }
            for (let i = 0; i < list.length; i++) {
                titleDes += $(list[i]).data('content') + '. ';
            }
            // 渲染描述
            _dom.find('.speedlimitDes').html(des);
            _dom.find('.speedlimitDes').prop('title', titleDes);
        }
        // 初始化限速策略
        let initSpeedStrategy = () => {
            if (!settings.speed) {
                return true;
            }
            // 渲染限速内容
            let html = '';
            if (settings.strategy_group_flag) {
                html = `<label class="control-label col-md-3 form-group-label">${LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE}</label>
                        <div class="col-md-8" style="height: unset;">
                            <div class="form-group-content" style="margin-bottom: 12px">
                                <input type="checkbox" id="speedStrategyCheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="${LANG.UI_PUBLIC_ON_ONE}" data-off-text="${LANG.UI_PUBLIC_OFF_ONE}">
                                <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="${LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TIPS}">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                            ${getSpeedContent()}
                        </div>`;
            } else {
                html = getSpeedContent();
            }
            _dom.append(html);
            // 初始化自定义配置数据信息
            _dom.find('#speedstrategy').speedstrategy({ config: settings.speedConfig, module_type: settings.module_type });
            // 初始化限速策略模式提示
            _dom.find('.popovers').popover(); // 初始化提示
            // 初始化开关
            let global_speed_limit = '';
            $('.make-switch').bootstrapSwitch();
            if (settings.speed && settings.strategy && !isEmptyObjectOrArray(settings.strategy.speedlimit) && settings.strategy.speedlimit.type == 1) {
                _dom.find('#show_type_1').show();
                _dom.find('#show_type_2').hide();
                _dom.find('#task_type_global_speed_strategy').show();
                global_speed_limit = settings.strategy.speedlimit.uuid;
            }
            initSpeedGird(global_speed_limit); // 初始化全局限速表格
        }
        /******初始化限速策略事件 */
        /******初始化存储策略事件 */
        let addStoreListener = () => {
            _dom.find('#deduplicationCheck').on('switchChange.bootstrapSwitch', initStoreDes);
            _dom.find('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);
            _dom.find('#compressGrade').on('change', initStoreDes);
            _dom.find('#storageEncryptMethod').on('change', initStoreDes);
            _dom.find('#encryptStorageCheck').on('switchChange.bootstrapSwitch', encryptChange);
            _dom.find('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
            _dom.find('#repassword').on('input propertychange', checkPassword);
            _dom.find('#password').on('input propertychange', function () {
                this.value = this.value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g, '');
            });
            // 永久增量提示
            _dom.find('#deduplicationCheck').on('switchChange.bootstrapSwitch', function () {
                if (_dom.find('#strategymode').find('input[data-mode=9]').prop('checked') && _dom.find('#deduplicationCheck').get(0).checked) {
                    //永久增量建议关闭提示
                    UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_STORE, LANG.UI_BACKUP_VM_PERMANENT_INCREMENT_TIPS);
                }
            });
            _dom.find('#compressCheck').on('switchChange.bootstrapSwitch', function () {
                if (_dom.find('#strategymode').find('input[data-mode=9]').prop('checked') && _dom.find('#compressCheck').get(0).checked) {
                    //永久增量建议关闭提示
                    UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_STORE, LANG.UI_BACKUP_VM_PERMANENT_INCREMENT_TIPS);
                }
            });
        };
        let addPasswordEyeListener = () => {
            _dom.find('.show-password-btn').on('click', showPassword);
            _dom.find('.show-rePassword-btn').on('click', showPassword);
        }
        let initStoreDes = () => {
            let des = "";
            // 重复数据删除
            if (settings.deduplication && _dom.find('#deduplicationCheck').length && CONF.FUNCTIONS.includes('dedupication')) {
                des += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes(_dom.find('#deduplicationCheck').get(0).checked) + ", ";
            }
            // 压缩传输
            des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes(_dom.find('#compressCheck').get(0).checked);
            // 压缩等级
            if (_dom.find('#compressCheck').get(0).checked) {
                let gradeValue = _dom.find('#compressGrade').val();
                let grade = '';
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
                };
                des += "," + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
            }
            // 存储加密
            des += "," + LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes(_dom.find('#encryptStorageCheck').get(0).checked);
            // 存储加密算法
            if (_dom.find('#encryptStorageCheck').get(0).checked) {
                let encryptedMethodLabel = _dom.find('.storage-encrypt-label').html();
                let method = _dom.find('#storageEncryptMethod').val();
                let grade = '';
                switch (parseInt(method)) {
                    case 1:
                        grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                        break;
                    case 2:
                        grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                        break;
                };
                des += "," + encryptedMethodLabel + ": " + grade;
            }
            _dom.find('.storeDes').html(des);
            _dom.find('.storeDes').attr('title', des);
        };
        let compressChange = function () {
            if (this.checked) {
                _dom.find('.compressGradeDiv').show();
            } else {
                _dom.find('.compressGradeDiv').hide();
            }
            initStoreDes();
        };
        let encryptChange = function () {
            if (this.checked) {
                _dom.find('#passwordAutocheck').bootstrapSwitch('state', true); //自动密码
                _dom.find('#password').empty();
                _dom.find('#repassword').empty();
                _dom.find('.passwordModeDiv').show();
                _dom.find('.storage-encrypt-div').show();
            } else {
                _dom.find('.passwordModeDiv').hide();
                _dom.find('.passwordDiv').hide();
                _dom.find('.storage-encrypt-div').hide();
            }
            initStoreDes();
        }
        let passwordModeChange = function () {
            if (this.checked) {
                _dom.find('.passwordDiv').hide();
            } else {
                _dom.find('.passwordDiv').show();
            }
        }
        let checkPassword = function () {
            settings.passwordChange = true;
            this.value = this.value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g, '');
            _dom.find('#password').attr('placeholder', '');
            _dom.find('#repassword').attr('placeholder', '');
            let password = $.trim(_dom.find('#password').val());
            let repassword = $.trim(_dom.find('#repassword').val());
            if (password != repassword) {
                _dom.find('.passwordTips').show();
            } else {
                _dom.find('.passwordTips').hide();
            }
        }
        let getSwitchDes = function (check) {
            if (check) {
                return LANG.UI_PUBLIC_ON;
            }
            return LANG.UI_PUBLIC_OFF;
        };
        /**
         * 点击小眼睛显示隐藏密码
         */
        let showPassword = function () {
            let $input = $(this).parent().find('input');
            let $btn = $(this);
            let inputType = $input.attr('type');
            // 修改输入框的类型，控制显示隐藏
            if (inputType === 'password') {
                $input.attr('type', 'text');
                $btn.find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
            } else {
                $input.attr('type', 'password');
                $btn.find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
            }
        }
        let initStoreStrategy = () => {
            if (!settings.store) {
                return true;
            }
            let html = '';
            if (settings.strategy_group_flag) {
                html = `<label class="control-label col-md-3 form-group-label">${LANG.UI_GLOBAL_STRATEGY_STORE}</label>
                    <div class="col-md-8" style="height: unset;">
                        <div class="form-group-content" style="margin-bottom: 12px">
                            <input type="checkbox" id="storeStrategyCheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="${LANG.UI_PUBLIC_ON_ONE}" data-off-text="${LANG.UI_PUBLIC_OFF_ONE}">
                            <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="${LANG.UI_GLOBAL_STRATEGY_STORE_TIPS}">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                        ${getStoreContent()}
                    </div>`;
            } else {
                html = getStoreContent();
            }
            _dom.append(html);
            _dom.find('.make-switch').bootstrapSwitch();
            // 初始化存储策略模式提示
            _dom.find('.popovers').popover();
            // 初始化开关
            $('.make-switch').bootstrapSwitch();
            initStoreDes();
            $.fn.initBackupStrategy.getDuplicationDiv = getDuplicationDiv;
            $.fn.initBackupStrategy.initStoreDes = initStoreDes;
            $.fn.initBackupStrategy.addStoreListener = addStoreListener;
        };
        /******初始化存储策略事件 */
        /******初始化保留策略事件 */
        let addReserveListeners = (config) => {
            // 切换保留类型
            _dom.find('#reserveType').on('change', function () { reserveTypeHandler(config) });
            // 切换保留方式
            _dom.find('#reserveMode').on('change', () => { initReserveDes(config) });
            // 按个数输入
            _dom.find('#spinnerNumInput').on('input propertychange', () => { initReserveDes(config) });
            // 按天数输入
            _dom.find('#spinnerDayInput').on('input propertychange', () => { initReserveDes(config) });
            // 配置gfs
            _dom.find('#GFSflag').on('switchChange.bootstrapSwitch', GFSChange);
            // spinner数量增减
            _dom.find('.spinner-up').on('click', () => { initReserveDes(config) });
            _dom.find('.spinner-down').on('click', () => { initReserveDes(config) });
        };
        // 切换保留类型
        let reserveTypeHandler = function (config) {
            let value = _dom.find('#reserveType').val();
            // 个数保留
            _dom.find('.reserveTips1').show();
            _dom.find('.reserveTips2').hide();
            if (CONF.RESERVE_TYPE.NUM == value) {
                _dom.find('.reserveNum').show();
                _dom.find('.reserveDay').hide();
            } else if (CONF.RESERVE_TYPE.DAY == value) {// 天数保留
                _dom.find('.reserveNum').hide();
                _dom.find('.reserveDay').show();
            } else if (CONF.RESERVE_TYPE.PERMANENT == value) { // 永久保留
                _dom.find('.reserveNum').hide();
                _dom.find('.reserveDay').hide();
                _dom.find('.reserveTips1').hide();
                _dom.find('.reserveTips2').show();
            }
            // 更新描述信息
            initReserveDes(config);
        };
        // 设置描述
        let initReserveDes = (config) => {
            // 合并传入配置
            config = $.extend(true, {}, settings, config);
            let des = "";
            let type = parseInt(_dom.find('#reserveType').val());
            let value = 0;
            // 保留值
            let spinnerNumInputVal = _dom.find('#spinnerNumInput').val();
            let spinnerDayInputVal = _dom.find('#spinnerDayInput').val();
            let label = _dom.find('#reserveMode option:selected').text();
            // 是否显示保留类型
            des += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + label + ', ';
            // 获取描述信息
            const updateDescription = (reserveType, num, langKey, inputId) => {
                if (CONF.RESERVE_TYPE[reserveType] === type) {
                    des += LANG.UI_RESERVE_RETENTION_MODE + ': ' + langKey;
                    // num不合法则重置为空
                    value = isNaN(parseInt(num, 10)) || !num ? '' : parseInt(num, 10);
                    var methoddes = LANG.UI_STRATEGY_VALUE
                    if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && reserveType == 'DAY') {
                        methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
                    }
                    des += `, ${methoddes}: ${value < 0 ? 1 : value}`;
                    // 如果输入不合法则重置为空
                    if (isNaN(parseInt(num, 10))) {
                        _dom.find(`#${inputId}`).val('');
                    }
                }
            };
            // 获取按个数保留描述
            if (CONF.RESERVE_TYPE.NUM) {
                updateDescription('NUM', spinnerNumInputVal, LANG.UI_BACKUP_NUM, 'spinnerNumInput');
            }
            // 获取按天数保留描述
            if (CONF.RESERVE_TYPE.DAY) {
                updateDescription('DAY', spinnerDayInputVal, LANG.UI_BACKUP_DAY, 'spinnerDayInput');
            }
            // 获取永久保留描述
            if (CONF.RESERVE_TYPE.PERMANENT == type) { // 永久保留
                des += LANG.UI_STRATEGY_PERMANENT_RESERVE;
            }
            // gfs
            if (config.GFS == true) {
                des += `, ${LANG.UI_SETTING_GFS_RETENTION_POLICY}: ${getSwitchDes(_dom.find('#GFSflag').get(0).checked)}`;
            }
            _dom.find('.reserveDes').html(des);
            _dom.find('.reserveDes').attr('title', des);
        }
        // gfs改变事件
        let GFSChange = function () {
            //如果勾选
            if (this.checked) {
                _dom.find("#GFSDiv").show();
            } else {
                _dom.find("#GFSDiv").hide();
            }
            // 更新描述
            initReserveDes(settings);
        }
        let initReserveStrategy = () => {
            if (!settings.reserve) {
                return true;
            }
            // 渲染保留策略内容
            let html = '';
            if (settings.strategy_group_flag) {
                html = `<label class="control-label col-md-3 form-group-label">${LANG.UI_STRATEGY_RESERVE}</label>
                        <div class="col-md-8" style="height: unset;">
                            <div class="form-group-content" style="margin-bottom: 12px">
                                <input type="checkbox" id="reserveStrategyCheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="${LANG.UI_PUBLIC_ON_ONE}" data-off-text="${LANG.UI_PUBLIC_OFF_ONE}">
                                <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right" data-content="${LANG.UI_GLOBAL_STRATEGY_RESERVE_TIPS}">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                            ${getReserveContent()}
                        </div>`;
            } else {
                html = getReserveContent();
            }
            _dom.append(html);
            // 初始化个数天数输入框
            _dom.find('#spinnerNum').spinner({ value: 30, step: 5, min: 1, max: 9999 });
            _dom.find('#spinnerDay').spinner({ value: 30, step: 5, min: 1, max: 9999 });
            // 初始化开关
            _dom.find('.make-switch').bootstrapSwitch();
            // 初始化gfs保留策略数据配置
            if (settings.GFS) {
                _dom.find("#GFSDiv").initGFSPlug();
            }
            // 初始化提示
            _dom.find('.popovers').popover();
            // 初始化勾选框
            _dom.find('.icheck').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue strategy-radio-custom',
                increaseArea: '20%'
            });
            _dom.find('#copyGFSflag').on('switchChange.bootstrapSwitch', function () {
                //如果勾选
                if (this.checked) {
                    $("#copyGFSDiv").show();
                } else {
                    $("#copyGFSDiv").hide();
                }
            });

            // 初始化开关
            $('.make-switch').bootstrapSwitch();
            // 云存储只能按链保留
            switch (settings.module_type_des) {
                case 'nas':
                case 'file':
                case 'hadoop':
                case 'obs':
                    _dom.find('#reserveMode').prop('disabled', false);
                    break;
                default:
                    if (settings.storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
                        _dom.find('#reserveMode').val(CONF.RESERVE_STRATEGY_MODE.CHIAN).prop('disabled', true);
                    } else {
                        _dom.find('#reserveMode').prop('disabled', false);
                    }
                    break;
            }
            // 永久保留改为不可见
            $('#reserveMode option[value=3]').addClass('display-nonde');
            // 暴露方法
            $.fn.initBackupStrategy.getReserveModeSelector = getReserveModeSelector;
            $.fn.initBackupStrategy.getReserveTypeSelector = getReserveTypeSelector;
            $.fn.initBackupStrategy.initReserveDes = initReserveDes;
            $.fn.initBackupStrategy.addReserveListeners = addReserveListeners;
        };
        /******初始化保留策略事件 */
        /******渲染数据 */

        // 按策略备份，时间策略格式化
        const groupTimeStrategy = (data) => {
            if (!Array.isArray(data)) {
                return false;
            }
            const result = { time: { timeInfo: {} } };
            data.forEach(item => {
                const { days, mode, strategy_type, start_time, roll_flag, roll_interval, roll_end_time, frequency } = item;
                const processedDays = days.map(day => day ? 1 : 0);
                const info = {
                    days: processedDays,
                    mode,
                    type: strategy_type,
                    startTime: start_time,
                    rollFlag: roll_flag,
                    rollInterval: roll_interval,
                    endTime: roll_end_time,
                    frequency
                };
                switch (mode) {
                    case 1:
                        result.time.timeInfo.fullInfo = info;
                        result.time.timeInfo.fullInfo.full_backup_compensation_flag = item.full_backup_compensation_flag;
                        break;
                    case 2:
                        if (data.length == 1) {
                            result.time.timeInfo.pIncrInfo = info;
                        } else {
                            result.time.timeInfo.incrInfo = info;
                        }
                        break;
                    case 3:
                        result.time.timeInfo.diffInfo = info;
                        break;
                    case 4:
                        result.time.timeInfo.logInfo = info;
                        break;
                    case 9:
                        result.time.timeInfo.pIncrInfo = info;
                        break;
                }
            });
            return result;
        };
        const checkStrategyMode = (checkMode) => {
            if (!checkMode) {
                return true;
            }
            for (let key in checkMode) {
                _dom.find(`#strategymode input[data-mode=${checkMode[key]}]`).iCheck('check');
                _dom.find(`#stragegyaccordion [data-mode=${checkMode[key]}]`).show();
            }
        }
        function isEmptyObjectOrArray(obj) {
            if (Array.isArray(obj)) {
                return obj.length === 0;
            } else if (typeof obj === 'object' && obj !== null) {
                return Object.keys(obj).length === 0;
            }
            return false;
        }
        // 渲染时间策略数据
        let renderTime = (strategy) => {
            // 无时间策略或者未配置直接返回
            if (!settings.time || isEmptyObjectOrArray(strategy.time)) {
                return true;
            }
            // 全局备份策略开关
            if (settings.strategy_group_flag) {
                _dom.find('#timeStrategyCheck').bootstrapSwitch('state', true)
            }
            _dom.find('#backuptype').val(strategy.time.type);
            // 根据策略类型显示配置项
            switch (strategy.time.type) {
                case STRATEGY_TYPE.STRATEGY:
                    let configTime = {};
                    // 备份策略修改时初始化
                    if (settings.timeFormateFlag) {
                        // 勾选已配置策略
                        configTime = groupTimeStrategy(strategy.time.data)
                    } else {
                        // 勾选已配置策略
                        configTime = strategy;
                    }
                    // config为空则退出
                    if (!configTime) {
                        return true;
                    }
                    _dom.find('.setStrategy').show();
                    _dom.find('.setOnceTime').hide();
                    let checkMode = [];
                    let config = defaultTimeStrategy;
                    // 处理传入策略，格式化
                    function configureBackupStrategy(strategyType, infoObject) {
                        let index = allTimeMode.indexOf(strategyType)
                        // 检查infoObject是否为空，避免直接访问可能未定义的属性
                        if (!infoObject || Object.keys(infoObject).length === 0) {
                            return;
                        }
                        config[index] = {
                            mode: strategyType,
                            strategy_type: infoObject.type,
                            days: infoObject.days,
                            frequency: infoObject.frequency,
                            start_time: infoObject.startTime,
                            roll_flag: infoObject.rollFlag,
                            roll_interval: infoObject.rollInterval,
                            roll_end_time: infoObject.endTime,
                        }
                        if(strategyType == 1){
                            config[index].full_backup_compensation_flag = infoObject.full_backup_compensation_flag;
                        }
                        defaultTimeDisplay[index] = '';
                        checkMode.push(strategyType);
                    }
                    // 遍历data.time.fullInfo
                    configureBackupStrategy(1, configTime.time.timeInfo.fullInfo); // 完全备份
                    configureBackupStrategy(2, configTime.time.timeInfo.incrInfo); // 增量备份
                    configureBackupStrategy(3, configTime.time.timeInfo.diffInfo); // 差异备份
                    configureBackupStrategy(4, configTime.time.timeInfo.logInfo); // 日志备份
                    configureBackupStrategy(9, configTime.time.timeInfo.pIncrInfo); // 永久增量备份
                    // 数据库tidb不显示日志备份的配置
                    if (settings.db_type == CONF.DB_TYPE.TIDB && settings.module_type_des == 'db') {
                        defaultTimeDisplay[3] = 'dispaly-none';
                    }
                    // 勾选已配置策略
                    checkStrategyMode(checkMode);
                    _dom.find('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: config, display: defaultTimeDisplay, backup_flag: 1 });
                    addTimeListeners();
                    break;
                case STRATEGY_TYPE.ONCE:
                    _dom.find('.setStrategy').hide();
                    _dom.find('.setOnceTime').show();
                    _dom.find('#oncetime').val(strategy.time.data)
                    break;
                case STRATEGY_TYPE.MANUAL:
                    _dom.find('.setStrategy').hide();
                    _dom.find('.setOnceTime').hide();
                    break;
                case STRATEGY_TYPE.IMMEDIATE:
                    _dom.find('.setStrategy').hide();
                    _dom.find('.setOnceTime').hide();
                    break;
            }
            // 渲染描述
            getTimeDes();
        };
        // 渲染限速策略数据
        let renderSpeed = (strategy) => {
            // 无限速策略或者未配置直接返回
            if (!settings.speed || isEmptyObjectOrArray(strategy.speedlimit)) {
                return true;
            }
            // 全局备份策略开关
            if (settings.strategy_group_flag) {
                _dom.find('#speedStrategyCheck').bootstrapSwitch('state', true)
            }
            let speed = strategy.speedlimit;
            let speedInfo = strategy.speedlimit.speedInfo;
            let speedList = [];
            for (let i = 0; i < speedInfo.length; i++) {
                speedList.push(speedInfo[i]);
            }
            _dom.find('.speedTips').popover();	   //初始化tips
            let titleDes = "";
            let des = "";
            _dom.find('#tasklevelselect').val(speed.level);
            _dom.find('#speedtypeselect').val(speed.type);
            // 如果是之前的自定义的 方式不变 但是如果是选择的全局限速策略的话，那么需要读取出所有的全局限速策略列表，然后根据列表的id取出限速信息
            if (speed.type == 1) {
            } else {
                // 自定义
                _dom.find('#show_type_2').show();
                _dom.find('#show_type_1').hide();
                _dom.find('#task_type_global_speed_strategy').hide();
                if (speedList.length != 0) {
                    des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
                }
                let strategy_type = 1;
                for (let i = 0; i < speedList.length; i++) {
                    titleDes += speedList[i].des + '. ' + "\n";
                    strategy_type = speedList[i].type;
                }
                addGlobalStrategy.init({ 'strategy_type': strategy_type, speedInfo: speedList, initSpeedFlag: 1, module_type: settings.module_type });

                // 渲染限速配置描述
                _dom.find('.speedlimitDes').html(des);
                _dom.find('.speedlimitDes').prop('title', titleDes);
            }
        }
        // 渲染保留策略数据
        let renderStore = (strategy) => {
            // 无保留策略或者未配置直接返回
            if (!settings.store || isEmptyObjectOrArray(strategy.store)) {
                return true;
            }
            // 全局备份策略开关
            if (settings.strategy_group_flag) {
                _dom.find('#storeStrategyCheck').bootstrapSwitch('state', true)
            }
            let store = strategy.store.storeInfo;
            if (settings.deduplication && CONF.FUNCTIONS.includes('dedupication')) {
                // 重复数据删除
                _dom.find('#deduplicationCheck').bootstrapSwitch('state', store.deduplication);
            }
            // 压缩存储
            _dom.find('#compressCheck').bootstrapSwitch('state', store.compress);
            // 压缩等级
            if (store.compress && store.compress_method) {
                _dom.find('#compressGrade').val(store.compress_method);
            }
            // 数据加密
            _dom.find('#encryptStorageCheck').bootstrapSwitch('state', store.encrypt);
            if (store.encrypt && store.encrypt_method) {
                // 数据加密
                _dom.find('#storageEncryptMethod').val(store.encrypt_method);
            }
            // 自动生成密码
            _dom.find('#passwordAutocheck').bootstrapSwitch('state', store.password_auto_flag);
            if (!store.password_auto_flag && store.encrypt) {
                _dom.find('.passwordDiv').show();
            } else {
                _dom.find('.passwordDiv').hide();
            }
            if (store.password !== '') {
                _dom.find('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
                _dom.find('#repassword').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
            }
            initStoreDes();
        }
        // 初始化gfs
        let initThisGFS = function (data) {
            if (data == "") {
                return false;
            }
            let info = {};
            for (each in data) {
                switch (data[each].level1_type) {
                    case 1:
                        info.week = [data[each].level2_type, data[each].retention_num, 'checked'];
                        break;
                    case 2:
                        info.month = [data[each].level2_type, data[each].retention_num, 'checked'];
                        break;
                    case 3:
                        info.year = [data[each].level2_type, data[each].retention_num, 'checked'];
                        break;
                }
            }
            return info;
        }
        // 渲染保留策略数据
        let renderReserve = (strategy) => {
            // 无保留策略或者未配置保留策略
            if (!settings.reserve || isEmptyObjectOrArray(strategy.reserve)) {
                return true;
            }
            // 全局备份策略开关
            if (settings.strategy_group_flag) {
                _dom.find('#reserveStrategyCheck').bootstrapSwitch('state', true)
            }
            let reserve = strategy.reserve.reserveInfo;
            // 保留类型
            if (reserve.strategy_mode) {
                if ((settings.storage_type == CONF.BD_STORAGE_TYPE.CLOUD && (settings.module_type_des == 'vm' || settings.module_type_des == 'os')) || settings.reserve_mode_chain_flag) {
                } else {
                    _dom.find('#reserveMode').val(reserve.strategy_mode)
                }
            }
            switch (reserve.type) {
                // 按个数保留
                case CONF.RESERVE_TYPE.NUM:
                    _dom.find('#spinnerNum').spinner('value', reserve.value);
                    _dom.find('.reserveNum').show();
                    _dom.find('.reserveDay').hide();
                    break;
                // 按天数保留
                case CONF.RESERVE_TYPE.DAY:
                    _dom.find('#spinnerDay').spinner('value', reserve.value);
                    _dom.find('.reserveDay').show();
                    _dom.find('.reserveNum').hide();
                    break;
                // 永久保留
                case CONF.RESERVE_TYPE.PERMANENT:
                    _dom.find('#reserveType').append('<option value="3" selected>' + LANG.UI_FILE_PERMANENT + '</option>');
                    _dom.find('#reserveType').prop("disabled", "disabled");
                    _dom.find('.reserveDay').hide();
                    _dom.find('.reserveNum').hide();
                    _dom.find('.reserveTips1').hide();
                    _dom.find('.reserveTips2').show();
                    break;
            }
            _dom.find('#reserveType').val(reserve.type);
            // gfs保留策略
            if (settings.GFS && reserve.gfs_strategy_item_list.length) {
                _dom.find("#GFSDiv").initGFSPlug(initThisGFS(reserve.gfs_strategy_item_list));
                _dom.find('#GFSflag').bootstrapSwitch('state', true);
                _dom.find("#GFSDiv").show();
            }
            initReserveDes(settings);
        }
        // 渲染策略数据
        let renderData = (data) => {
            renderSpeed(data);
            renderStore(data);
            renderReserve(data);
        };
        const initListener = () => {
            // 设置点击事件监听
            addTimeListeners();
            addSpeedListener();
            addStoreListener();
            addPasswordEyeListener();
            // 设置点击监听事件
            addReserveListeners(settings);
            initReserveDes(settings);
            addStratgeyCheckListener();
        }
        /**
         * 全局策略需要开关控制策略配置与否
         */
        const addStratgeyCheckListener = () => {
            _dom.find('#timeStrategyCheck').on('switchChange.bootstrapSwitch', function () { showStratgeyContent.call(this, 'strategy_time-content'); })
            _dom.find('#speedStrategyCheck').on('switchChange.bootstrapSwitch', function () { showStratgeyContent.call(this, 'strategy_speed-content') })
            _dom.find('#storeStrategyCheck').on('switchChange.bootstrapSwitch', function () { showStratgeyContent.call(this, 'strategy_store-content') })
            _dom.find('#reserveStrategyCheck').on('switchChange.bootstrapSwitch', function () { showStratgeyContent.call(this, 'strategy_reserve-content') })
        }
        const showStratgeyContent = function ($domClass) {
            if (this.checked) {
                $(`.${$domClass}`).show();
            } else {
                $(`.${$domClass}`).hide();
            }
        }
        /******渲染数据 */
        let init = () => {
            Metronic.blockUI({ target: "#tab_common", animate: true });
            _dom.empty();
            if (settings.recovery_time_flag) {
                initRecoveryTimeStrategy();
            } else {
                initTimeStrategy();
            }
            initSpeedStrategy();
            initStoreStrategy();
            initReserveStrategy();
            //  初始化时间策略监听事件
            initListener();
            if (settings.strategy) { //如果带策略配置就渲染数据
                renderData(settings.strategy);
            }
            //增加回调函数，用于各个模块调用插件时执行一些模块内部特有的操作
            setTimeout(() => {
                // 无时间策略更新描述
                if (settings.time) {
                    getTimeDes();
                }
                Metronic.unblockUI("#tab_common");
                if (callback && typeof callback == 'function') {
                    callback();
                    initReserveDes();
                }
            }, 500);
        };
        return init();
    };
    $.fn.refreshStrategy = function (options) {
        const MODE_LIST = $.fn.initBackupStrategy.MODE_LIST;
        const MODE_TIPS = $.fn.initBackupStrategy.MODE_TIPS;
        // 合并用户配置与默认配置
        const settings = $.extend($.fn.initBackupStrategy.settings, options);
        $.fn.refreshStrategy.settings = settings;
        // 当前页面位置
        const _dom = $(this);
        let initPlugins = () => {
            // 初始化复选框
            $(`.strategy_time-div .icheck`).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue strategy-radio-custom',
                increaseArea: '20%'
            });
            $(`.strategy_store-div .icheck`).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue strategy-radio-custom',
                increaseArea: '20%'
            });
            $(`.strategy_reserve-div .icheck`).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue strategy-radio-custom',
                increaseArea: '20%'
            });
            // 初始化提示信息
            _dom.find('.popovers').popover();
            // 初始化开关
            _dom.find('.make-switch').bootstrapSwitch();
            // 初始化监听事件
            $.fn.initBackupStrategy.addTimeListeners()
        }
        // 更新时间策略
        let refreshTimeStrategy = () => {
            if (Array.isArray(settings.mode) && settings.mode.length > 0) {
                let modeDom = _dom.find('#strategymode');
                // 更新备份模式展示
                modeDom.empty().append($.fn.initBackupStrategy.getModeButton(settings.mode, MODE_LIST, settings.storage_type));
                // 更新备份模式提示信息
                modeDom.append($.fn.initBackupStrategy.getModeTips(settings.mode, MODE_TIPS, settings.storage_type));
                // 初始化插件
                initPlugins();
            }
            // 隐藏所有的类型
            let strategyAccordion = _dom.find('#backupTimestrategy .strategy-panel[data-mode]');
            for (let i = 0; i < strategyAccordion.length; i++) {
                strategyAccordion[i].style.display = 'none';
            }
            // 清空时间策略描述
            _dom.find('.backupTimeDes').html('');
        }
        // 更新存储策略
        let refreshStoreStrategy = () => {
            let _dom = $('.deduplicationDiv');
            // 更新重复数据删除展示
            _dom.empty().append($.fn.initBackupStrategy.getDuplicationDiv(settings.deduplication));
            // 更新描述信息
            $.fn.initBackupStrategy.initStoreDes();
            // 设置监听
            $.fn.initBackupStrategy.addStoreListener($('#store'));
            // 初始化插件
            initPlugins();
        };
        let refreshReserveStrategy = () => {
            // 更新保留类型展示
            _dom.find('.reserve_mode-div').empty().append($.fn.initBackupStrategy.getReserveModeSelector());
            _dom.find('.reserve_type-div').empty().append($.fn.initBackupStrategy.getReserveTypeSelector());
            _dom.find('.reserveNum').show();
            _dom.find('.GFSdiv').empty().append($.fn.initBackupStrategy.getGfsStrategy(settings.GFS));
            // 更新策略描述
            $.fn.initBackupStrategy.initReserveDes(settings);
            // 设置监听事件
            $.fn.initBackupStrategy.addReserveListeners($('#reserve'), settings);
            // 初始化插件
            initPlugins();
        };
        let refreshSettings = () => {
            // 更新时间策略
            refreshTimeStrategy();
            // 更新存储策略
            refreshStoreStrategy();
            // 更新保留策略
            refreshReserveStrategy();
        }
        let init = () => {
            refreshSettings();
        }
        return init();
    };
    $.fn.getBackupStrategy = function () {
        // 备份策略类型 按策略，一次性，手动
        const STRATEGY_TYPE = { STRATEGY: 'strategy', ONCE: 'oncetime', MANUAL: 'manual' };
        // 恢复策略类型 立即启动，指定时间启动，按策略启动
        const STRATEGY_TYPE_RECOVERY = { IMMEDIATE: 'immediate', ATTIME: 'atTime', STRATEGY: 'strategy' };
        const config = {};
        const settings = $.fn.refreshStrategy.settings ?? $.fn.initBackupStrategy.settings;
        // 当前页面位置
        const _dom = $(this);
        /**
         * 获取恢复启动时间
         * @returns {type: STRATEGY_TYPE.STRATEGY, start_time: ''}
         */
        let getRecoveryTimeStrategy = () => {
            let recoveryType = _dom.find('#recoveryType').val();
            switch (recoveryType) {
                case STRATEGY_TYPE_RECOVERY.IMMEDIATE:
                    return { type: STRATEGY_TYPE_RECOVERY.IMMEDIATE, start_time: '', des: LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE, type_value: 1 }
                case STRATEGY_TYPE_RECOVERY.ATTIME:
                    let start_time = _dom.find('#oncetime').val();
                    if ("" != start_time) {
                        let systemTime = $('#systemTimeTop').text();
                        let onceTimeSize = new Date(start_time).getTime();
                        let systemTimeSize = new Date(systemTime).getTime();
                        if (onceTimeSize <= systemTimeSize) {
                            UIToastr.showWarning(LANG.UI_STRATEGY_TIME, LANG.UI_GLOBAL_STRATEGY_TIME_TIPS);
                            return false;
                        }
                        return { type: STRATEGY_TYPE_RECOVERY.ATTIME, start_time: start_time, des: LANG.UI_GLOBAL_STRATEGY_TYPE_START_AT_TIME + ': ' + start_time, type_value: 4 }
                    } else {
                        UIToastr.showWarning(LANG.UI_STRATEGY_TIME, LANG.UI_GLOBAL_STRATEGY_TIME_SET_TIPS);
                        return false;
                    }
                case STRATEGY_TYPE_RECOVERY.STRATEGY:
                    let strategy = _dom.find('#recoveryTimestrategy').getStrategyConfig();
                    return { type: STRATEGY_TYPE_RECOVERY.STRATEGY, strategy: strategy.recInfo, des: strategy.recInfo.des, type_value: 2 }
            }
        };
        let getTimeStrategy = () => {
            if (!settings.time) {
                return {};
            }
            if (settings.recovery_time_flag) {
                let info = { timeInfo: {} }
                info.timeInfo = getRecoveryTimeStrategy();
                return info.timeInfo ? info : false;
            }
            // 全局策略组未勾选表示未配置改策略
            if (settings.strategy_group_flag) {
                let time_check_flag = _dom.find('#timeStrategyCheck').get(0).checked;
                if (!time_check_flag) {
                    return {};
                }
            }
            let strategyInfo = {};
            let info = { timeInfo: {}, des: '' };
            let strategyConfig = _dom.find('#backupTimestrategy').getStrategyConfig();
            let strategyMode = _dom.find('#strategymode').find('input:checked');
            let strategyType = _dom.find('#backuptype').val();
            info.type = strategyType;
            function handleBackupType(type, config) {
                const { des, rollFlag, startTime, endTime } = config[type];
                // 判断开始时间个结束时间的合理性
                if (!config[type] || rollFlag && !checkTime(startTime, endTime)) {
                    return false;
                }
                info.des += `${des}<br>`;
                strategyInfo[type] = config[type];

                return true;
            }
            switch (strategyType) {
                case STRATEGY_TYPE.STRATEGY:
                    if (strategyMode.length == 0) {
                        UIToastr.showWarning(LANG.UI_STRATEGY_TIME, LANG.UI_GLOBAL_STRATEGY_TIME_NO_SET_TIPS);
                        return false;
                    } else {
                        for (let i = 0; i < strategyMode.length; i++) {
                            const mode = $(strategyMode[i]).data('mode');

                            // 使用switch语句来提高代码的清晰度和可扩展性
                            switch (mode) {
                                case 1: // 完全备份
                                    if (!handleBackupType('fullInfo', strategyConfig)) return false;
                                    break;
                                case 2: // 增量备份
                                    if (!handleBackupType('incrInfo', strategyConfig)) return false;
                                    break;
                                case 3: // 差异备份
                                    if (!handleBackupType('diffInfo', strategyConfig)) return false;
                                    break;
                                case 4: // 日志备份
                                    if (!handleBackupType('logInfo', strategyConfig)) return false;
                                    break;
                                case 9: // 永久增量
                                    if (!handleBackupType('pIncrInfo', strategyConfig)) return false;
                                    break;
                                default:
                                    // 处理未知的备份类型
                                    return false;
                            }
                        }
                    }
                    break;
                case STRATEGY_TYPE.ONCE:
                    let onceTime = $('#oncetime').val();
                    if ("" != onceTime) {
                        let systemTime = $('#systemTimeTop').text();
                        let onceTimeSize = new Date(onceTime).getTime();
                        let systemTimeSize = new Date(systemTime).getTime();
                        if (onceTimeSize <= systemTimeSize) {
                            UIToastr.showWarning(LANG.UI_STRATEGY_TIME, LANG.UI_GLOBAL_STRATEGY_TIME_TIPS);
                            return false;
                        }
                        info.data = onceTime;
                        info.des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + onceTime;
                    } else {
                        UIToastr.showWarning(LANG.UI_STRATEGY_TIME, LANG.UI_GLOBAL_STRATEGY_TIME_SET_TIPS);
                        return false;
                    }
                    break;
                case STRATEGY_TYPE.MANUAL:
                    info.des = LANG.UI_BACKUP_MANUAL;
                    break;
                case STRATEGY_TYPE.IMMEDIATE:
                    info.des = LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE;
                    break;
            }
            info.timeInfo = strategyInfo;
            return info;
        };
        // 检验开始时间是否小于结束时间
        let checkTime = (start, end) => {
            let startnum = new Date("1970-01-01" + " " + start).getTime();
            let endnum = new Date("1970-01-01" + " " + end).getTime();
            if (endnum <= startnum) {
                UIToastr.showWarning(LANG.UI_STRATEGY_TIME, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
                return false;
            } else {
                return true;
            }
        }
        let getSpeedStrategy = () => {
            if (!settings.speed) {
                return {};
            }
            // 全局策略组未勾选表示未配置改策略
            if (settings.strategy_group_flag) {
                let speed_check_flag = _dom.find('#speedStrategyCheck').get(0).checked;
                if (!speed_check_flag) {
                    return {};
                }
            }
            let info = getSpeedStrategyInfo();
            if (info.speedInfo.length > 0) {
                info.des = '';
                for (let i = 0; i < info.speedInfo.length; i++) {
                    info.des += info.speedInfo[i].des + '<br>';
                }
            }
            return info;
        };
        //判断字符传知否在Latin1字符集中
        let isNotLatinCode = (string) => {
            let latin1Regex = /[^\x00-\xFF]/;
            if (latin1Regex.test(string)) {
                UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_STORE, LANG.UI_DB_BACKUP_PASSWORD_TIPS);
                return false;
            }
            return true;
        }
        //得到开关的结果描述   开启/关闭
        let getSwitchDes = (check) => {
            if (check) {
                return LANG.UI_PUBLIC_ON;
            }
            return LANG.UI_PUBLIC_OFF;
        }
        let getStoreStrategy = () => {
            let _oldPassword = '';
            // 如果是修改或者使用全局策略，缓存加密密码
            if (!settings.store) {
                return {};
            }
            // 全局策略组未勾选表示未配置改策略
            if (settings.strategy_group_flag) {
                let store_check_flag = _dom.find('#storeStrategyCheck').get(0).checked;
                if (!store_check_flag) {
                    return {};

                }
            }
            if (settings.strategy && settings.strategy.store && settings.strategy.store.storeInfo && settings.strategy.store.storeInfo.encrypt && !settings.strategy.store.storeInfo.password_auto_flag) {
                _oldPassword = settings.strategy.store.storeInfo.password;
            }
            let info = { storeInfo: {}, des: '' };
            let storeInfo = {};
            storeInfo.deduplication = false;
            // 重复数据删除
            if (settings.deduplication && CONF.FUNCTIONS.includes('dedupication')) {
                storeInfo.deduplication = _dom.find('#deduplicationCheck').get(0).checked;
            }
            // 数据压缩
            storeInfo.compress = _dom.find('#compressCheck').get(0).checked;
            // 压缩等级
            storeInfo.compress_method = parseInt(_dom.find('#compressGrade').val()) ?? 0;
            // 传输加密
            storeInfo.dataencrypt = _dom.find('#encryptStorageCheck').get(0).checked;
            storeInfo.encrypt = _dom.find('#encryptStorageCheck').get(0).checked;
            // 存储加密
            storeInfo.encrypt_method = parseInt(_dom.find('#storageEncryptMethod').val()) ?? 0;
            // 自动密码
            storeInfo.password_auto_flag = _dom.find('#passwordAutocheck').get(0).checked;
            if (!isNotLatinCode($.trim(_dom.find('#password').val()))) {
                return false;
            }
            storeInfo.password = btoa($.trim(_dom.find('#password').val()));
            if (settings.deduplication && CONF.FUNCTIONS.includes('dedupication')) {
                info.des += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes(storeInfo.deduplication) + "<br>";
            }
            // 压缩存储
            info.des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes(storeInfo.compress);
            if (storeInfo.compress) {
                let gradeValue = _dom.find('#compressGrade').val();
                let grade = '';
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
                };
                info.des += "<br>" + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
            }
            // 数据加密
            info.des += "<br>" + LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes(_dom.find('#encryptStorageCheck').get(0).checked);
            if (storeInfo.dataencrypt) {
                let method = parseInt(_dom.find('#storageEncryptMethod').val());
                if (method == 1) {
                    info.des += "<br>" + LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_METHOD + ": " + LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                }
                if (method == 2) {
                    info.des += "<br>" + LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_METHOD + ": " + LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                }
                info.des += "<br>" + LANG.UI_BACKUP_DATA_AUTO_CREATE_PASSWORD + ": " + getSwitchDes(storeInfo.password_auto_flag);
            }
            //1.自动生成密码  密码设置为空
            if (storeInfo.password_auto_flag) {
                storeInfo.password = "";
                // 2.当前页面开启加密  手动加密密码
            } else if (storeInfo.dataencrypt && !storeInfo.password_auto_flag) {
                // 3.未输入密码
                if (storeInfo.password == "") {
                    // 原策略有密码  没有输入过新密码
                    if (_oldPassword != '' && !settings.passwordChange) {
                        storeInfo.password = _oldPassword;
                        // 无原策略 或  输入过新密码
                    } else {
                        // 给出提示
                        UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_STORE, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
                        return false;
                    }
                    // 4.输入了密码
                } else {
                    // 密码和确认密码不一致
                    if (storeInfo.password != btoa($.trim(_dom.find('#repassword').val()))) {//没有选择策略管理
                        UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_STORE, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
                        return false;
                    }
                }
            }
            info.storeInfo = storeInfo;
            return info;
        };
        let getReservedStrategy = () => {
            if (!settings.reserve) {
                return {};
            }
            // 全局策略组未勾选表示未配置改策略
            if (settings.strategy_group_flag) {
                let reserve_check_flag = _dom.find('#reserveStrategyCheck').get(0).checked;
                if (!reserve_check_flag) {
                    return {};
                }
            }
            let info = { reserveInfo: { enable_flag: true }, des: '' };
            info.des += _dom.find('.reserveDes').html().replaceAll(',', '<br>');
            let reserveInfo = {};
            reserveInfo.strategy_mode = 0;
            reserveInfo.strategyMode = 0;
            reserveInfo.strategy_mode = parseInt(_dom.find('#reserveMode').val());
            reserveInfo.strategyMode = parseInt(_dom.find('#reserveMode').val());
            // 全局策略数据库类型不需要配置保留方式，默认按链保留
            if (settings.strategy_group_flag && settings.module_type_des == 'db') {
                reserveInfo.strategy_mode = 2;
                reserveInfo.strategyMode = 2;
            }
            reserveInfo.type = parseInt(_dom.find('#reserveType').val());
            // 按个数保留
            if (CONF.RESERVE_TYPE.NUM == reserveInfo.type) {
                reserveInfo.value = parseInt(_dom.find('#spinnerNumInput').val());
                // 保留值必填且不为零
                if (!reserveInfo.value || reserveInfo.value == 0) {
                    UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
                    return false;
                }
                // 按天数保留
            } else if (CONF.RESERVE_TYPE.DAY == reserveInfo.type) {
                reserveInfo.value = parseInt(_dom.find('#spinnerDayInput').val());
                // 保留值必填且不为零
                if (!reserveInfo.value || reserveInfo.value == 0) {
                    UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
                    return false;
                }
            } else {
                reserveInfo.value = 0;
            }
            //得到GFS策略
            //如果开启GFS保留策略,配置GFS策略必须和时间策略相关联,则要先开启时间策略并且时间策略配置对
            if (settings.GFS && _dom.find('#GFSflag').get(0).checked) {
                if (!(config.time.timeInfo && config.time.timeInfo.fullInfo)) {
                    UIToastr.showWarning(LANG.UI_STRATEGY_RESERVE, LANG.UI_SETTING_GFS_RETENTION_POLICY_TIPS)
                    return false;
                }
                //得到完全备份的勾选类型
                let checkInfo = "";
                if (config.time.timeInfo.fullInfo == null || config.time.timeInfo.fullInfo == "" || config.time.timeInfo.fullInfo == undefined) {
                    UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_SETTING_VM_GFS_MUST_SELECT_CONTAIN);
                    return false;
                }
                checkInfo = config.time.timeInfo.fullInfo.type;
                reserveInfo.gfs_strategy_item_list = _dom.find("#GFSDiv").getGFSData(checkInfo);
                //如果返回是false  则退出
                if (!reserveInfo.gfs_strategy_item_list) {
                    return false;
                }
                //添加描述
                info.des += ", " + _dom.find('.GFSstrategydes').html();
            } else {
                reserveInfo.gfs_strategy_item_list = [];
            }
            info.reserveInfo = reserveInfo;
            return info;
        };
        let getStrategyInfo = () => {
            // 报错之后不能进行下一步，需要返回false
            config.time = getTimeStrategy();
            if (!config.time) {
                return false;
            }
            config.speedlimit = getSpeedStrategy();
            if (!config.speedlimit) {
                return false;
            }
            config.store = getStoreStrategy();
            if (!config.store) {
                return false;
            }
            config.reserve = getReservedStrategy();
            if (!config.reserve) {
                return false;
            }
            return config;
        };
        let init = function () {
            return getStrategyInfo();
        };
        return init();
    };
})(jQuery)