/**
 * @author JackC
 * @description 时间策略组件,设置每天、每周、每月、永久、自定义
 *
 * ////////////////////////////////////
 * TODO 待完成:
 * 1. 实现自定义配置项(如：限速大小)
 * 2. 实现自定义策略类别(如：按年)
 *
 * ////////////////////////////////////
 * 依赖插件
 * <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
 * <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
 *
 * <script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
 * <script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
 * <script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
 * <script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
 * <script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
 * <script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
 */

(() => {
    let cache = {};

    $.fn.customTimeStrategy = function (options = {}, params = {}) {
        let id = $(this).attr('id');
        if ('object' === typeof options) {
            cache[id] = getOptions(options);
            $(this).html(initContent(id));
            initRender(id);
            initListener(id);
            initOldData(id);
        } else if ('string' === typeof options) {
            let allowMethod = [
                'getOptions', 'getConfigData', 'getConfigDes', 'getConfigIdx'
            ];
            if (!allowMethod.includes(options)) {
                return null;
            }
            switch (options) {
                case 'getConfigData':
                    return typeof cache[id] === 'undefined' ? null : getConfigData(id);
                case 'getConfigDes':
                    return getConfigDes(params);
                case 'getConfigIdx':
                    return getConfigIdx(params);
                default:
                    return typeof cache[id] === 'undefined' ? null : cache[id];
            }
        }
    };

    const getOptions = options => {
        let retOptions = {
            hide: ['forever'],
            onNavChange: null,
            showBackDivId: '',
            showBackUlId: '',
            active: 'day',
            title: LANG.UI_STRATEGY_TIME,
            singleType: true,
        };
        for (const key in options) {
            retOptions[key] = options[key];
        }
        retOptions.list = {
            day: extendTimeItem('day', options.list),
            week: extendTimeItem('week', options.list),
            month: extendTimeItem('month', options.list),
            forever: extendTimeItem('forever', options.list),
            custom: extendTimeItem('custom', options.list),
        };
        return retOptions;
    };

    const extendTimeItem = (type, timeList) => {
        let ret = {
            default_checked: [],
            default_start_time: '23:00:00',
            default_end_time: '23:30:00',
            config: [],
            onChange: null,
            title: LANG.UI_STRATEGY_DAY,
            type: 1,
        };
        switch (type) {
            case 'day':
                break;
            case 'week':
                ret.type = 2;
                ret.title = LANG.UI_STRATEGY_WEEK_EN;
                ret.default_checked = [4];
                break;
            case 'month':
                ret.type = 3;
                ret.title = LANG.UI_STRATEGY_MONTH;
                ret.default_checked = [0, 14];
                break;
            case 'forever':
                ret.type = 5;
                ret.title = LANG.UI_STRATEGY_FOREVER;
                break;
            default:
                ret.type = 4;
                ret.title = LANG.UI_STRATEGY_CUSTOM;
                ret.default_start_time = moment({hour: 23, minute: 0, second: 0});
                ret.default_end_time = moment({hour: 23, minute: 30, second: 0});
                break;
        }
        if (typeof timeList === 'undefined' || typeof timeList[type] === 'undefined') {
            return ret;
        }
        for (const key in timeList[type]) {
            ret[key] = timeList[type][key];
        }
        return ret;
    }

    //////////////////// 开始-添加页面元素 ////////////////////

    const initContent = (prefixId) => {
        return `
            <div class="custom-time-div">
                <div class="custom-time__wrapper">
                    <div class="custom-time__left col-md-3">
                        <ul class="nav nav-tabs-tabs-left">
                            ${initTabHeader(prefixId)}
                        </ul>
                    </div>
                    <div class="custom-time__right col-md-9">
                        <div class="tab-content">
                            ${initTabBody(prefixId)}
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    /**
     * 初始化tab头
     * @param prefixId
     */
    const initTabHeader = (prefixId) => {
        let tabHeaders = ``;
        for (const key in cache[prefixId].list) {
            if (cache[prefixId].hide.includes(key)) {
                continue;
            }
            let tabConfig = cache[prefixId].list[key];
            tabHeaders += `
            <li class="nav-item ${key === cache[prefixId].active ? 'active' : ''}" data-type="${key}">
                <a href="#${prefixId}-tab_${key}" data-toggle="tab">${tabConfig.title}</a>
            </li>
            `;
        }
        return tabHeaders;
    };

    /**
     * 获取tab的内容
     * @param prefixId
     */
    const initTabBody = (prefixId) => {
        let tabBody = ``;
        for (const key in cache[prefixId].list) {
            if (cache[prefixId].hide.includes(key)) {
                continue;
            }
            let tabContent = ``;
            switch (key) {
                case 'day':
                    tabContent = getDayTabContent(prefixId)
                    break;
                case 'week':
                    tabContent = getWeekTabContent(prefixId);
                    break;
                case 'month':
                    tabContent = getMonthTabContent(prefixId);
                    break;
                case 'forever':
                    tabContent = getForeverTabContent(prefixId);
                    break;
                default:
                    tabContent = getCustomTabContent(prefixId);
                    break;
            }
            tabBody += `<div class="tab-pane fade in ${key === cache[prefixId].active ? 'active' : ''}" id="${prefixId}-tab_${key}">
                ${tabContent}
            </div>`;
        }
        return tabBody;
    };

    const getDayTabContent = prefixId => {
        return `
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_PUBLIC_START_TIME}</label>
            ${getTimePicker({id: prefixId + '-day-start-time'})}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_PUBLIC_END_TIME}</label>
            ${getTimePicker({id: prefixId + '-day-end-time', popover: LANG.UI_GLOBAL_STRATEGY_TIME_TIPS1})}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label"></label>
            <div class="col-md-9">
                <button class="btn btn-sm green-haze add-btn" id="${prefixId}-day-btn">
                    <i class="viconfont vicon-ge_add_task"></i>
                    ${LANG.UI_PUBLIC_ADD}
                </button>
            </div>
        </div>
        `
    };

    const getWeekTabContent = prefixId => {
        return `
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_STRATEGY_WEEK_EN}</label>
            ${getWeekDays(prefixId)}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_PUBLIC_START_TIME}</label>
            ${getTimePicker({id: prefixId + '-week-start-time'})}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_PUBLIC_END_TIME}</label>
            ${getTimePicker({id: prefixId + '-week-end-time', popover: LANG.UI_GLOBAL_STRATEGY_TIME_TIPS1})}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label"></label>
            <div class="col-md-9">
                <button class="btn btn-sm green-haze add-btn" id="${prefixId}-week-btn">
                    <i class="viconfont vicon-ge_add_task"></i>
                    ${LANG.UI_PUBLIC_ADD}
                </button>
            </div>
        </div>
        `;
    };

    /**
     * 获取每周的配置项
     * @param prefixId
     */
    const getWeekDays = prefixId => {
        let id = `${prefixId}-week-days`;
        let weekItems = ``;
        let dayDes = [
            LANG.UI_STRATEGY_MONDAY,
            LANG.UI_STRATEGY_TUESDAY,
            LANG.UI_STRATEGY_WEDNESDAY,
            LANG.UI_STRATEGY_THURSDAY,
            LANG.UI_STRATEGY_FRIDAY,
            LANG.UI_STRATEGY_SATURDAY,
            LANG.UI_STRATEGY_SUNDAY
        ];
        for (let i = 0; i < 7; i++) {
            let checkedId = `${id}-checked-${i}`;
            let checked = cache[prefixId].list.week.default_checked.includes(i) ? 'checked' : '';
            weekItems += `
            <div class="week-days-item">
                <input type="checkbox" ${checked} id="${checkedId}" value="${i}" name="week-days" />
                <label for="${checkedId}">${dayDes[i]}</label>
            </div>
            `;
        }
        return `
        <div class="col-md-9">
            <div class="week-days__wrapper" id="${id}">
                ${weekItems}
            </div>
        </div>`;
    };

    const getMonthTabContent = prefixId => {
        return `
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_STRATEGY_MONTH}</label>
            ${getMonthDays(prefixId)}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_PUBLIC_START_TIME}</label>
            ${getTimePicker({id: prefixId + '-month-start-time'})}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_PUBLIC_END_TIME}</label>
            ${getTimePicker({id: prefixId + '-month-end-time', popover: LANG.UI_GLOBAL_STRATEGY_TIME_TIPS1})}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label"></label>
            <div class="col-md-9">
                <button class="btn btn-sm green-haze add-btn" id="${prefixId}-month-btn">
                    <i class="viconfont vicon-ge_add_task"></i>
                    ${LANG.UI_PUBLIC_ADD}
                </button>
            </div>
        </div>
        `;
    };

    /**
     * 零填充
     * @param num
     * @param size
     * @returns {string}
     */
    const padZero = (num, size = 2) => {
        let s = num + '';
        while (s.length < size) {
            s = "0" + s;
        }
        return s;
    };

    /**
     * 获取每月的配置项
     * @param prefixId
     */
    const getMonthDays = prefixId => {
        let id = `${prefixId}-month-days`;
        let monthItems = ``;
        for (let i = 0; i < 31; i++) {
            let checkboxId = `${id}-checkbox-${i}`;
            let checked = cache[prefixId].list.month.default_checked.includes(i) ? 'checked' : '';
            monthItems += `
            <div class="month-days-item">
                <input type="checkbox" ${checked} value="${i}" id="${checkboxId}" name="month-days" />
                <label for="${checkboxId}">${padZero(i + 1)}</label>
            </div>
            `;
        }
        return `
        <div class="col-md-9">
            <div class="month-days__wrapper" id="${id}">
                ${monthItems}
            </div>
        </div>
        `;
    };

    const getForeverTabContent = prefixId => {
        return `
        <label class="control-label col-md-3 form-group-label"></label>
            <div class="col-md-9">
                <button class="btn btn-sm green-haze add-btn" id="${prefixId}-forever-btn">
                    <i class="viconfont vicon-ge_add_task"></i>
                    ${LANG.UI_PUBLIC_ADD}
                </button>
            </div>
        </div>
        `;
    };

    const getCustomTabContent = prefixId => {
        return `
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_PUBLIC_START_TIME}</label>
            ${getDatetimePicker({id: prefixId + '-custom-start-time'})}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">${LANG.UI_PUBLIC_END_TIME}</label>
            ${getDatetimePicker({id: prefixId + '-custom-end-time'})}
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label"></label>
            <div class="col-md-9">
                <button class="btn btn-sm green-haze add-btn" id="${prefixId}-custom-btn">
                    <i class="viconfont vicon-ge_add_task"></i>
                    ${LANG.UI_PUBLIC_ADD}
                </button>
            </div>
        </div>
        `;
    };

    /**
     * 获取时间选择控件
     * @param id
     * @param tips
     * @param popover
     */
    const getTimePicker = ({id, tips = '', popover = ''}) => {
        return `
        <div id="${id}__wrapper" class="col-md-7">
            <div class="input-group">
                <input type="text" class="form-control timepicker timepicker-24" id="${id}" 
                    autocomplete="off" />
                <span class="input-group-btn">
                    <button class="btn default btn-time" style="height: 34px">
                        <i class="viconfont vicon-beifenshijiandian"></i>
                    </button>
                </span>
            </div>
            <span class="help-block ${tips ? '' : 'display-none'}" id="${id}-tips">${tips}</span>
            <a class="popovers popover-abs-position ${popover ? '' : 'display-none'}" data-container="body" data-trigger="hover" data-html="true"
                data-placement="right" data-content="${popover}">
                <i class="viconfont vicon-tishi"></i>
            </a>
        </div>
        `;
    };

    /**
     * 获取日期时间选择控件
     * @param id
     * @param tips
     * @param popover
     */
    const getDatetimePicker = ({id, tips = '', popover = ''}) => {
        return `
        <div id="${id}__wrapper" class="col-md-7">
            <div class="daterangepickerdiv">
                <input type="text" class="form-control" id="${id}" autocomplete="off" />
                <i class="viconfont vicon-ge_calendar"></i>
            </div>
            <span class="help-block ${tips ? '' : 'display-none'}" id="${id}-tips">${tips}</span>
            <a class="popovers popover-abs-position ${popover ? '' : 'display-none'}" data-container="body" data-trigger="hover" data-html="true"
                data-placement="right" data-content="${popover}">
                <i class="viconfont vicon-tishi"></i>
            </a>
        </div>
        `;
    };

    const initRender = prefixId => {
        // 初始化日期
        initTimePicker(`${prefixId}-day-start-time`, cache[prefixId].list.day.default_start_time);
        initTimePicker(`${prefixId}-day-end-time`, cache[prefixId].list.day.default_end_time);
        initTimePicker(`${prefixId}-week-start-time`, cache[prefixId].list.week.default_start_time);
        initTimePicker(`${prefixId}-week-end-time`, cache[prefixId].list.week.default_end_time);
        initTimePicker(`${prefixId}-month-start-time`, cache[prefixId].list.month.default_start_time);
        initTimePicker(`${prefixId}-month-end-time`, cache[prefixId].list.month.default_end_time);
        initDatetimePicker(`${prefixId}-custom-start-time`, cache[prefixId].list.custom.default_start_time);
        initDatetimePicker(`${prefixId}-custom-end-time`, cache[prefixId].list.custom.default_end_time);

        // 提示信息
        $(`#${prefixId} .popover__wrapper .popovers`).popover();

        // 设置高度
        let offsetHeight = (5 - $(`#${prefixId} ul.nav li.nav-item`).length) * 44;
        let height = 300 - offsetHeight;
        height = height < 200 ? 200 : height;
        $(`#${prefixId} .custom-time-div`).css('height', height + 'px');
    };

    /**
     * 初始化时间选择控件
     * @param pickerId
     * @param defaultTime
     */
    const initTimePicker = (pickerId, defaultTime = '') => {
        $(`#${pickerId}`).timepicker({
            autoclose: true,
            minuteStep: 5,
            showSeconds: true,
            showMeridian: false,
            defaultTime: defaultTime ? defaultTime : 'current',
        }).closest('.input-group').on('click', '.input-group-btn', function (e) {
            e.preventDefault();
            $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
        });
    };

    /**
     * 初始化日期时间选择控件
     * @param pickerId
     * @param defaultTime
     */
    const initDatetimePicker = (pickerId, defaultTime = '') => {
        if (!defaultTime) {
            defaultTime = moment();
        }
        let picker = $(`#${pickerId}`);
        picker.val(defaultTime.format('YYYY-MM-DD HH:mm:ss'));
        picker.daterangepicker({
            autoUpdateInput: false,											//是否自动填充input
            startDate: defaultTime,
            timePicker: true,													//是否显示时间,时分
            timePicker24Hour: true,											//是否是24小时制
            singleDatePicker: true,
            timePickerSeconds: true,                                            //是否显示秒
            alwaysShowCalendars: true,										//是否总是显示日期选择
            locale: DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function (start, end, label) {
        });

        picker.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'));
        });

        picker.on('cancel.daterangepicker', function (ev, picker) {
            // $(this).val('');
        });

        $(`#${pickerId}__wrapper .daterangepickerdiv i`).on('click', function () {
            $(this).parent().find('input').trigger('click');
        });
    };

    //////////////////// 结束-添加页面元素 ////////////////////

    //////////////////// 开始-数据处理 ////////////////////

    /**
     * 添加监听
     * @param prefixId
     */
    const initListener = prefixId => {
        // 回显的Id
        if (!cache[prefixId].showBackDivId) {
            cache[prefixId].showBackDivId = `${prefixId}ShowDiv`;
            cache[prefixId].showBackUlId = `${prefixId}ShowUl`;
        }

        // 添加配置策略
        $(`#${prefixId}-day-btn`).on('click', () => {
            clickAddDayBtn(prefixId)
        });
        $(`#${prefixId}-week-btn`).on('click', () => {
            clickAddWeekBtn(prefixId);
        });
        $(`#${prefixId}-month-btn`).on('click', () => {
            clickAddMonthBtn(prefixId);
        });
        $(`#${prefixId}-forever-btn`).on('click', () => {
            clickAddForeverBtn(prefixId);
        });
        $(`#${prefixId}-custom-btn`).on('click', () => {
            clickAddCustomBtn(prefixId);
        });

        // 每周、每月至少选择一个
        // 监听每周check事件
        $(`#${prefixId} .week-days-item input[name="week-days"]`).on('change', function () {
            weekCheckChange(prefixId, this);
        });
        $(`#${prefixId} .month-days-item input[name="month-days"]`).on('change', function () {
            monthCheckChange(prefixId, this);
        });

        // 切换策略类别
        $(`#${prefixId} .custom-time__left .nav-item`).on('click', function () {
            clickNavItem(prefixId, this);
        });
    };

    const convertDayTimeToSeconds = dayTime => {
        let timeList = dayTime.split(':');
        return 3600 * parseInt(timeList[0]) + 60 * parseInt(timeList[1]) + parseInt(timeList[2]);
    };

    const clickAddDayBtn = prefixId => {
        let startTime = $(`#${prefixId}-day-start-time`).val();
        let endTime = $(`#${prefixId}-day-end-time`).val();
        if (!startTime || !endTime) {
            UIToastr.showWarning(cache[prefixId].title, LANG.UI_STRATEGY_SELECT_TIME);
            return;
        }
        if (startTime === endTime) {
            UIToastr.showWarning(cache[prefixId].title, LANG.UI_STRATEGY_ERROR_DATE);
            return;
        }
        startTime = padZeroDaytime(startTime);
        endTime = padZeroDaytime(endTime);
        let startTimestamp = convertDayTimeToSeconds(startTime);
        let endTimestamp = convertDayTimeToSeconds(endTime);
        let idx = getConfigIdx({type: 'day', prefixId, startTimestamp, endTimestamp});
        for (const item of cache[prefixId].list.day.config) {
            if (idx === item.idx) {
                UIToastr.showWarning(cache[prefixId].title, LANG.UI_GLOBAL_STRATEGY_EXIST);
                return;
            }
        }

        let configItem = {
            idx,
            start_time: startTime,
            end_time: endTime,
            start_timestamp: startTimestamp,
            end_timestamp: endTimestamp,
            des: getConfigDes({type: 'day', title: cache[prefixId].title, startTime, endTime}),
            days: [],
        };
        cache[prefixId].list.day.config.push(configItem);
        if (cache[prefixId].list.day.onChange && typeof cache[prefixId].list.day.onChange === 'function') {
            cache[prefixId].list.day.onChange('day', configItem, cache[prefixId].list.day);
        }
        showConfig(prefixId, configItem, 'day');
    }

    const padZeroDaytime = dayTime => {
        let list = dayTime.split(':');
        return padZero(list[0]) + ':' + padZero(list[1]) + ':' + padZero(list[2]);
    };

    const getShowBack = (des, id) => {
        return `
        <li class="list-group-item popovers time-strategy-show-item" data-container="body"
            data-trigger="hover" data-placement="top" data-html="true"
            data-content="${des}" id="${id}">
            <div class="col1">
                <div class="cont">
                    <div class="cont-col1"></div>
                    <div class="cont-col2">
                        <div class="desc list-one">
                            ${des}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col2 pull-right delete-list">
                <a data-id="${id}" class="del-item">
                    <div class="label label-sm label-danger">
                        <i class="viconfont vicon-cuowu"></i>
                    </div>
                </a>
            </div>
        </li>
        `;
    };

    const showConfig = (prefixId, configItem, key) => {
        $(`#${cache[prefixId].showBackDivId}`).show();
        $(`#${cache[prefixId].showBackUlId}`).append(getShowBack(configItem.des, configItem.idx));
        $(`#${configItem.idx}`).popover().find('div.delete-list a.del-item').on('click', () => {
            for (const index in cache[prefixId].list[key].config) {
                let item = cache[prefixId].list[key].config[index];
                if (item.idx === configItem.idx) {
                    cache[prefixId].list[key].config.splice(index, 1);
                }
            }
            $(`#${configItem.idx}`).popover('hide').remove();
            if (!$(`#${cache[prefixId].showBackUlId} .time-strategy-show-item`).length) {
                $(`#${cache[prefixId].showBackDivId}`).hide();
            }
        });
    };

    const clickAddWeekBtn = prefixId => {
        let days = [];
        $(`#${prefixId}-week-days input[name="week-days"]`).map((k, v) => {
            if (v.checked) {
                days.push(1);
            } else {
                days.push(0);
            }
        });
        let startTime = $(`#${prefixId}-week-start-time`).val();
        let endTime = $(`#${prefixId}-week-end-time`).val();

        if (!startTime || !endTime) {
            UIToastr.showWarning(cache[prefixId].title, LANG.UI_STRATEGY_SELECT_TIME);
            return;
        }
        if (startTime === endTime) {
            UIToastr.showWarning(cache[prefixId].title, LANG.UI_STRATEGY_ERROR_DATE);
            return;
        }
        startTime = padZeroDaytime(startTime);
        endTime = padZeroDaytime(endTime);
        let startTimestamp = convertDayTimeToSeconds(startTime);
        let endTimestamp = convertDayTimeToSeconds(endTime);
        let idx = getConfigIdx({type: 'week', prefixId, days, startTimestamp, endTimestamp});
        for (const item of cache[prefixId].list.week.config) {
            if (idx === item.idx) {
                UIToastr.showWarning(cache[prefixId].title, LANG.UI_GLOBAL_STRATEGY_EXIST);
                return;
            }
        }

        let configItem = {
            idx,
            start_time: startTime,
            end_time: endTime,
            start_timestamp: startTimestamp,
            end_timestamp: endTimestamp,
            des: getConfigDes({type: 'week', title: cache[prefixId].title, days, startTime, endTime}),
            days: days,
        };
        cache[prefixId].list.week.config.push(configItem);
        if (cache[prefixId].list.week.onChange && typeof cache[prefixId].list.week.onChange === 'function') {
            cache[prefixId].list.week.onChange('week', configItem, cache[prefixId].list.week);
        }
        showConfig(prefixId, configItem, 'week');
    }

    const clickAddMonthBtn = prefixId => {
        let days = [];
        $(`#${prefixId}-month-days input[name="month-days"]`).map((k, v) => {
            if (v.checked) {
                days.push(1);
            } else {
                days.push(0);
            }
        });
        let startTime = $(`#${prefixId}-month-start-time`).val();
        let endTime = $(`#${prefixId}-month-end-time`).val();

        if (!startTime || !endTime) {
            UIToastr.showWarning(cache[prefixId].title, LANG.UI_STRATEGY_SELECT_TIME);
            return;
        }
        if (startTime === endTime) {
            UIToastr.showWarning(cache[prefixId].title, LANG.UI_STRATEGY_ERROR_DATE);
            return;
        }
        startTime = padZeroDaytime(startTime);
        endTime = padZeroDaytime(endTime);
        let startTimestamp = convertDayTimeToSeconds(startTime);
        let endTimestamp = convertDayTimeToSeconds(endTime);
        let idx = getConfigIdx({type: 'month', prefixId, days, startTimestamp, endTimestamp});
        for (const item of cache[prefixId].list.month.config) {
            if (idx === item.idx) {
                UIToastr.showWarning(cache[prefixId].title, LANG.UI_GLOBAL_STRATEGY_EXIST);
                return;
            }
        }

        let configItem = {
            idx,
            start_time: startTime,
            end_time: endTime,
            start_timestamp: startTimestamp,
            end_timestamp: endTimestamp,
            des: getConfigDes({type: 'month', title: cache[prefixId].title, days, startTime, endTime}),
            days: days,
        };
        cache[prefixId].list.month.config.push(configItem);
        if (cache[prefixId].list.month.onChange && typeof cache[prefixId].list.month.onChange === 'function') {
            cache[prefixId].list.month.onChange('month', configItem, cache[prefixId].list.month);
        }
        showConfig(prefixId, configItem, 'month');
    }

    const clickAddForeverBtn = function () {
        console.log('forever')
    }

    const covertDatetimeToSecond = time => {
        return Math.ceil((new Date(time)).getTime() / 1000);
    };

    const clickAddCustomBtn = prefixId => {
        let startTime = $(`#${prefixId}-custom-start-time`).val();
        let endTime = $(`#${prefixId}-custom-end-time`).val();
        if (!startTime || !endTime) {
            UIToastr.showWarning(cache[prefixId].title, LANG.UI_STRATEGY_SELECT_TIME);
            return;
        }
        if (startTime === endTime) {
            UIToastr.showWarning(cache[prefixId].title, LANG.UI_STRATEGY_ERROR_DATE);
            return;
        }
        let startTimestamp = covertDatetimeToSecond(startTime);
        let endTimestamp = covertDatetimeToSecond(endTime);
        if (startTimestamp > endTimestamp) {
            UIToastr.showWarning(cache[prefixId].title, LANG.UI_STRATEGY_ERROR_DATE2);
            return;
        }
        let idx = getConfigIdx({type: 'custom', prefixId, startTimestamp, endTimestamp});
        for (const item of cache[prefixId].list.custom.config) {
            if (idx === item.idx) {
                UIToastr.showWarning(cache[prefixId].title, LANG.UI_GLOBAL_STRATEGY_EXIST);
                return;
            }
        }

        let configItem = {
            idx,
            start_time: startTime,
            end_time: endTime,
            start_timestamp: startTimestamp,
            end_timestamp: endTimestamp,
            des: getConfigDes({type: 'custom', title: cache[prefixId].title, startTime, endTime}),
            days: [],
        };
        cache[prefixId].list.custom.config.push(configItem);
        if (cache[prefixId].list.custom.onChange && typeof cache[prefixId].list.custom.onChange === 'function') {
            cache[prefixId].list.custom.onChange('custom', configItem, cache[prefixId].list.custom);
        }
        showConfig(prefixId, configItem, 'custom');
    }

    const clickNavItem = (prefixId, self) => {
        let key = $(self).data('type');
        if (cache[prefixId].active === key) {
            return;
        }
        cache[prefixId].active = key;
        if (cache[prefixId].onNavChange && typeof cache[prefixId].onNavChange === 'function') {
            cache[prefixId].onNavChange(key, cache[prefixId].list[key]);
        }
        if (!cache[prefixId].singleType) {
            return;
        }
        $(`#${cache[prefixId].showBackUlId}`).html('');
        if (!cache[prefixId].list[key].config.length) {
            $(`#${cache[prefixId].showBackDivId}`).hide();
        } else {
            cache[prefixId].list[key].config.map(v => {
                showConfig(prefixId, v, key);
            });
        }
    }

    const weekCheckChange = (prefixId, self) => {
        if (!$(`#${prefixId} .week-days-item input[name="week-days"]:checked`).length) {
            $(self).prop('checked', 'checked');
        }
    };

    const monthCheckChange = (prefixId, self) => {
        if (!$(`#${prefixId} .month-days-item input[name="month-days"]:checked`).length) {
            $(self).prop('checked', 'checked');
        }
    };

    //////////////////// 结束-数据处理 ////////////////////

    //////////////////// 开始-初始化旧数据 ////////////////////

    const initOldData = prefixId => {
        $(`#${cache[prefixId].showBackUlId}`).html('');
        if (!cache[prefixId].list[cache[prefixId].active].config.length) {
            $(`#${cache[prefixId].showBackDivId}`).hide();
        } else {
            cache[prefixId].list[cache[prefixId].active].config.map(v => {
                showConfig(prefixId, v, cache[prefixId].active);
            });
        }
    };

    //////////////////// 结束-初始化旧数据 ////////////////////

    //////////////////// 开始-公共接口 ////////////////////

    /**
     * 获取配置的描述信息
     * @param type
     * @param title
     * @param days
     * @param startTime
     * @param endTime
     * @returns String
     */
    const getConfigDes = ({type = 'custom', title, days = [], startTime, endTime}) => {
        let des;
        let daysDes = days.map((v, k) => {
            if (v === 1) {
                return k + 1;
            }
            return undefined;
        });
        switch (type) {
            case 'day':
                des = `${title}: ${LANG.UI_STRATEGY_DAY}${padZeroDaytime(startTime)}${LANG.UI_STRATEGY_START}, ${padZeroDaytime(endTime)}${LANG.UI_STRATEGY_END}`;
                break;
            case 'week':
                des = `${title}: ${LANG.UI_STRATEGY_WEEK}${daysDes.filter(v => v).join(', ')},`;
                des += ` ${padZeroDaytime(startTime)}${LANG.UI_STRATEGY_START}, ${padZeroDaytime(endTime)}${LANG.UI_STRATEGY_END}`;
                break;
            case 'month':
                des = `${title}: ${LANG.UI_STRATEGY_MONTH}${daysDes.filter(v => v).join(', ')},`;
                des +=` ${padZeroDaytime(startTime)}${LANG.UI_STRATEGY_START}, ${padZeroDaytime(endTime)}${LANG.UI_STRATEGY_END}`;
                break;
            default:
                des = `${title}: ${LANG.UI_STRATEGY_CUSTOM}${startTime}${LANG.UI_STRATEGY_START}, ${endTime}${LANG.UI_STRATEGY_END}`;
                break;
        }
        return des;
    };

    /**
     * 获取配置的idx
     * @param type
     * @param prefixId
     * @param days
     * @param startTimestamp
     * @param endTimestamp
     * @returns {string}
     */
    const getConfigIdx = ({type = 'custom', prefixId, days = [], startTimestamp, endTimestamp}) => {
        let idx = `${prefixId}-li-${type}-${startTimestamp}-${endTimestamp}`;
        if (type === 'week' || type === 'month') {
            idx += `-${days.join('')}`;
        }
        return idx;
    };

    const getConfigData = prefixId => {
        if (!cache[prefixId].singleType) {
            return cache[prefixId].list;
        }
        return cache[prefixId].list[cache[prefixId].active];
    };

    //////////////////// 结束-公共接口 ////////////////////
})();