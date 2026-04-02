(function($) {
    const VALID_DATETIME_EXP = /(^([1-9]\d{3}-)(([0]{0,1}[1-9]-)|([1][0-2]-))([0-3]{0,1}[0-9])((\s+([0-1]?[0-9]|2[0-3]):([0-9]|[0-5][0-9]):([0-9]|[0-5][0-9]))?))$/;

    /**
     * 校验日期时间参数是否合法
     * @param time 开始时间 | 结束时间
     * @returns {string}
     */
    const checkValidDateTime = (time) => {
        let resultTime = '';

        if (time) {
            if (VALID_DATETIME_EXP.test(time)) {
                resultTime = time;
            }
        }

        return resultTime;
    };

    $.fn.initDateRangePicker = function(options) {
        let settings = $.extend({ slotId: '', dateRangePickerId: '' }, options); // 配置项
        let result = {
            startTime: '',
            endTime: ''
        }; // 返回给父组件的数据

        let clearButton = '<button id="' + settings.slotId + '_clear_daterange" class="btn btn-secondary-primary">' + LANG.UI_CLEAR + '</button>';

        /**
         * 获取日期范围组件面板内容
         * @param s 开始时间
         * @param e 结束时间
         * @returns {string}
         */
        const getDateRangeText = (s, e) => {
            let resultText = '';

            if (s && e && VALID_DATETIME_EXP.test(s) && VALID_DATETIME_EXP.test(e)) {
                resultText = `${s} - ${e}`;
            } else {
                resultText = LANG.UI_DATERANGEPICKER_NO_TIME;
            }

            return resultText;
        };

        /**
         * 生成日期范围组件button
         * @param data 配置项
         * @returns {string}
         */
        const getDataRangePicker = (data) => {

            return '<button id="' + settings.dateRangePickerId + '" class="btn btn-dropdown-daterange text-overflow-ellipsis">' +
                        '<i class="viconfont vicon-rili2 me-4"></i>' +
                        '<span class="daterangepicker-text">' + getDateRangeText(data.startTime, data.endTime) + '</span>' +
                    '</button>';
        };

        /**
         * 初始化日期范围组件
         */
        const initDateRangePicker = () => {
            let startTime = checkValidDateTime(settings.startTime);
            let endTime = checkValidDateTime(settings.endTime);

            // 日期范围初始化
            $(`#${settings.dateRangePickerId}`).daterangepicker({
                startDate: startTime || moment().startOf('day'), // 默认开始时间
                endDate: endTime || moment({
                    hour: 23,
                    minute: 59
                }), // 默认结束时间
                maxDate: settings.maxDate === 'now' ? moment({hour: 23,minute: 59}) : '', // 最大可用时间
                timePickerSeconds: settings.timePickerSeconds ? settings.timePickerSeconds : true,
                timePicker: settings.timePicker ? settings.timePicker : true, // 是否显示时间,时分
                timePicker24Hour: settings.timePicker24Hour ? settings.timePicker24Hour : true, // 是否是24小时制
                alwaysShowCalendars: settings.alwaysShowCalendars ? settings.alwaysShowCalendars : true, // 是否总是显示日期选择
                ranges: DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),
                locale: DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),
                parentEl: `#${settings.slotId}` || 'body'
            }).on('show.daterangepicker', () => {
                $('.btn-dropdown-daterange').addClass('show');

                let clearDateRange = $(`#${settings.slotId}`).find(`.daterangepicker.toolbar-daterangepicker #${settings.slotId}_clear_daterange`);

                if (clearDateRange.length === 0) {
                    // 增加清空按钮
                    $(`#${settings.slotId}`).find('.daterangepicker.toolbar-daterangepicker .cancelBtn').after(clearButton);

                    // 增加清空按钮click 监听
                    $(`#${settings.slotId}_clear_daterange`).on('click', () => {
                        $(`#${settings.dateRangePickerId} .daterangepicker-text`).html(LANG.UI_DATERANGEPICKER_NO_TIME);
                        // 模拟cancel按钮的关闭
                        $(`#${settings.slotId} .daterangepicker.toolbar-daterangepicker`).find('.cancelBtn').click();

                        result.startTime = '';
                        result.endTime = '';

                        // 将选择的时间派发给父组件
                        window.$emit(`${settings.dateRangePickerId}-updateDateRangeEvent`, result);

                        // 重置开始时间和结束时间
                        initDateRangePicker();

                        // 重新给dateRangePicker加上自定义class
                        $(`#${settings.slotId}`).find('.daterangepicker').addClass('toolbar-daterangepicker');
                    });
                }
            }).on('hide.daterangepicker', () => {
                $('.btn-dropdown-daterange').removeClass('show');
            }).on('apply.daterangepicker', function(ev, picker) {
                result.startTime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
                result.endTime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');

                let daterangepickerText = `${result.startTime} - ${result.endTime}`;
                $(`#${settings.dateRangePickerId} .daterangepicker-text`).html(daterangepickerText);

                // 将选择的时间派发给父组件
                window.$emit(`${settings.dateRangePickerId}-updateDateRangeEvent`, result);
            });
        };

        const initListener = () => {
            // 初始化日期范围选择器
            initDateRangePicker();

            // 给dateRangePicker加上自定义class
            $(`#${settings.slotId}`).find('.daterangepicker').addClass('toolbar-daterangepicker');
        };

        return this.each(function() {
            // 初始化过滤器
            let html = getDataRangePicker(settings);

            $(this).empty().html(html);

            // 初始化监听器
            initListener();
        });
    };

    $.fn.resetDateRangePicker = function(options) {
        if (!options.dateRangePickerId) {
            return;
        }

        let $dateRangePicker = $(`#${options.dateRangePickerId}`);
        let startTime = '';
        let endTime = '';

        // 清空上一个tab页的日期选择
        $(`#${options.dateRangePickerId} .daterangepicker-text`).html(LANG.UI_DATERANGEPICKER_NO_TIME);

        if (options.startTime) {
            // 校验不合法的时间参数，checkValidDateTime 返回空，此时 startTime 赋默认开始时间
            startTime = checkValidDateTime(options.startTime) ? checkValidDateTime(options.startTime) : moment().startOf('day');
        } else {
            // 未传 startTime 亦赋默认开始时间
            startTime = moment().startOf('day');
        }

        if (options.endTime) {
            // 校验不合法的时间参数，checkValidDateTime 返回空，此时 endTime 赋默认结束时间
            endTime = checkValidDateTime(options.endTime) ? checkValidDateTime(options.endTime) : moment({hour: 23, minute: 59});
        } else {
            // 未传 endTime 亦赋默认结束时间
            endTime = moment({hour: 23, minute: 59});
        }

        $dateRangePicker.data('daterangepicker').setStartDate(startTime);
        $dateRangePicker.data('daterangepicker').setEndDate(endTime);
    };
})(jQuery);