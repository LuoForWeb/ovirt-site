/*
 * @note: 副本时间策略
 * @author: chenyunfeng@vinchin.com
 * @Description: 由于副本只有一种时间策略类型，所以单独封装一个插件
 * @Date: 2025-01-06 10:20:58
 * @LastEditTime: 2026-01-15 16:48:47
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 * 1.初始化
		$('.copy-strategy-config').copyTimeStrategy({strategy_type: strategy_type, showDom: $('.timeStrategyShow'),strategyConfig:strategy_config});
 * 2.获取时间策略信息
 * getStrategyConfig()
 *
 */
(function ($) {
    $.fn.copyTimeStrategy = function (options) {
        const defaults = {
            strategy_type: 'strategy',
            archive_flag: false,
        };
        const settings = $.extend({}, defaults, options);
        const _dom = $(this);
        // 时间策略类型配置
        const STRATEGY_TYPE_CONFIG = { 'strategy': LANG.UI_COPY_AS_STRATEGY, 'oncetime': LANG.UI_COPY_AS_ONCETIME }
        // 获取时间策略展开头部
        const getStrategyHeading = () => {
            return `<div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#copyTime" aria-expanded="true">
                                    <i class="viconfont vicon-shijian2 font-green-seagreen"></i>
                                    <span class="font-green-seagreen">${LANG.UI_STRATEGY_TIME}</span>
                                    <span class="strategyDes backupTimeDes"></span>
                                </a>
                            </h4>
                        </div>`;
        };
        // 获取策略配置内容
        const getStrategyContent = () => {
            return `<div class="form-group setStrategy ${settings.strategy_type == 'oncetime' ? 'display-none' : ''}">
                        <label class="control-label col-md-2">${LANG.UI_GLOBAL_STRATEGY_SET_STRATEGY}</label>
                        <div class="col-md-10">
                            <div class="portlet">
                                <div class="portlet-body">
                                    <div class="panel-group accordion" id="backupTimestrategy">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
        };
        // 获取一次性配置内容
        const getOnceContent = () => {
            return `<div class="form-group setOnceTime ${settings.strategy_type == 'strategy' ? 'display-none' : ''}">
                        <div class="onceTime-content">
                            <label class="control-label col-md-2">${LANG.UI_GLOBAL_STRATEGY_SET_TIME}</label>
                            <div class="onceTime-content-input">
                                <div class="input-group date form_datetime">
                                    <input type="text" size="16" readonly id="oncetime" class="form-control input-sm" style="width: 160px;">
                                    <span class="input-group-btn">
                                        <button class="btn default input-sm" id="resetTime" type="button"><i class="fa fa-times"></i></button>
                                        <button class="btn default date-set input-sm" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                    </span>
                                </div>
                            </div>
                            <div class="onceTime-content-tips">
                                <a class="popovers " data-container="body" data-trigger="hover" data-placement="right" data-content="${LANG.UI_GLOBAL_STRATEGY_DELTASK_ONETIME}" data-original-title="" title="">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                    </div>`;
        };
        // 获取策略类型选项
        const getStrategyTypeOptions = () => {
            let html = ``;
            for (let key in STRATEGY_TYPE_CONFIG) {
                html += `<option value="${key}" ${key == settings.strategy_type ? 'selected' : ''}>${settings.archive_flag ? STRATEGY_TYPE_CONFIG[key].replaceAll(LANG.UI_VISUAL_COPY, LANG.UI_VISUAL_ARCHIVE_CHART) : STRATEGY_TYPE_CONFIG[key]}</option>`;
            }
            return html;
        }
        // 获取时间策略配置内容
        const getStrategyBody = () => {
            return `<div id="copyTime" class="panel-collapse collapse in">
                        <div class="panel-body">
                            <div class="col-md-12">
                                <div class="form-group" id="copyTypeDiv">
                                    <label class="control-label col-md-2">${LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE}</label>
                                    <div class="col-md-6">
                                        <select class="form-control select2me input-sm" id="backuptype" ${settings.strategy_type == 'oncetime' ? 'disabled' : ''}>
                                            ${getStrategyTypeOptions()}
                                        </select>
                                    </div>
                                </div>
                                ${getStrategyContent()}
                                ${getOnceContent()}
                            </div>
                        </div>
                    </div>`;
        };
        //初始化时间选择器
        const initDataTimePicker = function () {
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
        // 初始化页面显示配置
        const getTimeStrategyContent = () => {
            let html = `<div class="panel panel-default strategy-panel">
                        ${getStrategyHeading()}
                        ${getStrategyBody()}
                        </div>`;
            _dom.html(html);
            initDataTimePicker();
            // 初始化提示信息
            _dom.find('.popovers').popover();
        };
        // 初始化时间策略配置
        const initTimeConfig = () => {
            let strategy = [{
                mode: 5,
                strategy_type: 2,
                days: [0, 0, 0, 0, 1, 0, 0],
                start_time: '23:00:00',
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: '23:59:59',
                frequency: '',
            }];
            // 带参初始化
            if (settings.strategyConfig) {
                if ('oncetime' == settings.strategyConfig.type) {
                    $('.setStrategy').hide();
                    $('.setOnceTime').show();
                    $('#oncetime').val(settings.strategyConfig.data);
                } else {
                    strategy = settings.strategyConfig.data
                    strategy[0].mode = 5;
                }
            }
            // 归档mode为6
            if (settings.archive_flag) {
                strategy[0].mode = 6;
            }
            //延迟设置,因为这里icheck会默认修改里面的选中事件
            $('#backupTimestrategy').strategy({ dom: $('#backupTimestrategy'), config: strategy, display: [], copyFlag: settings.archive_flag ? false : true, archive_flag: settings.archive_flag });
            initTimeStrategyDes();
        }
        //副本备份方式（按策略、一次性）
        let strategyTypeHandler = function () {
            if ('strategy' == this.value) {
                //按策略备份
                $('.setStrategy').show();	//显示时间策略
                $('.setOnceTime').hide();   //隐藏一次性设置时间
            } else if ('oncetime' == this.value) {
                //一次性备份
                $('.setOnceTime').show();	//显示一次性设置时间
                $('.setStrategy').hide();	//隐藏时间策略
            }
            initTimeStrategyDes();
        }
        // 时间策略描述
        let initTimeStrategyDes = function () {
            let des = "";
            let backupType = _dom.find('#backuptype').val();
            let strategyConfig = _dom.find('#backupTimestrategy').getStrategyConfig();
            if ('strategy' == backupType) {
                des += settings.archive_flag ? strategyConfig.archiveInfo.des + ". " : strategyConfig.copyInfo.des + ". ";
            } else if ('oncetime' == backupType && _dom.find('#oncetime').val()) {
                des += settings.archive_flag ? LANG.UI_ARCHIVE_AS_ONCE_TIME_START : LANG.UI_COPY_AS_ONCE_TIME_START;
                des += "： " + _dom.find('#oncetime').val()
            }
            _dom.find('.backupTimeDes').html(des);
            _dom.find('.backupTimeDes').prop('title', des);
        }
        const initListener = () => {
            _dom.find('#backuptype').on('change', strategyTypeHandler);
            _dom.find('#resetTime').on('click', function () {//清空一次性副本时间信息
                $(this).val('');
                initTimeStrategyDes();
            });
            // 一次性副本
            _dom.find('#oncetime').on('change', initTimeStrategyDes);
        };
        function checkTime(startTime, endTime) {
            // 将时间字符串转换为 Date 对象
            const startDate = new Date("1970-01-01" + " " + startTime).getTime();
            const endDate = new Date("1970-01-01" + " " + endTime).getTime();
            // 比较两个时间
            if (endDate > startDate) {
                return true; // 结束时间在开始时间之后
            } else {
                return false; // 结束时间不在开始时间之后
            }
        };
        // 获取时间策略配置
        const getStrategyConfig = () => {
            let data = { flag: true, strategy_info: {}, type: 'strategy', start_time: '' };
            data.type = _dom.find('#backuptype').val();
            let strategyConfig = _dom.find('#backupTimestrategy').getStrategyConfig();
            switch (data.type) {
                case 'strategy': // 按策略配置
                    data.strategy_info = settings.archive_flag ? strategyConfig.archiveInfo : strategyConfig.copyInfo;
                    // 检查滚动时间是否合理
                    if (data.strategy_info.rollFlag && !checkTime(data.strategy_info.startTime, data.strategy_info.endTime)) {
			            UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
                        data.flag = false;
                        return data;
                    }
                    break;
                case 'oncetime': // 一次性配置
                    let onceTime = _dom.find('#oncetime').val();
                    if ("" != onceTime) {
                        let systemTime = $('#systemTimeTop').text();
                        let onceTimeSize = new Date(onceTime).getTime();
                        let systemTimeSize = new Date(systemTime).getTime();
                        // 检查一次性开始时间是否合理
                        if (onceTimeSize <= systemTimeSize) {
                            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_BACKUP_SET_CORRENT_TIME_TIPS);
                            data.flag = false;
                            return data;
                        }
                        data.start_time = onceTime;
                    } else {
                        // 未配置时间给出提示
                        UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_BACKUP_SET_TIME_TIPS);
                        data.flag = false;
                        return data;
                    }
                    break;
            }
            // 在确认配置显示策略描述
            if (settings.showDom) {
                settings.showDom.html(_dom.find('.backupTimeDes').html());
            }
            return data;
        };
        const init = () => {
            getTimeStrategyContent();
            initTimeConfig();
            initListener();
        };
        return this.each(function () {
            init();
            const privateMethod = {
                getStrategyConfig: getStrategyConfig
            }
            // 暴露获取副本源信息方法
            $(this).data('copyStrategy', privateMethod);
        });
    }
})(jQuery)