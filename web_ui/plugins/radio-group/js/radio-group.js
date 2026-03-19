/*
 * @Author: ChengJiaFu
 * @Date: 2025-03-10 17:30:00
 * @Description: 单选框组组件
 * @version: 1.0
 */
(function($) {
    /**
     * 生成 radio group HTML
     * @param {Array} radioData - JSON数组，例如：[{id: 'radio-1', label: '选项一', value: 1}]
     * @param {Object} settings - 配置项，包含 name 和 selectedValue
     * @returns {String} - HTML字符串
     */
    const getRadioGroupHtml = (radioData, settings) => {
        if (!Array.isArray(radioData) || radioData.length === 0) {
            return '';
        }

        let resultHtml = '<div class="radio-group-wrapper">';
        const groupName = settings.name || 'default-radio-group';

        radioData.forEach(item => {
            const isChecked = settings.selectedValue !== undefined && String(item.value) === String(settings.selectedValue);
            const checkedAttr = isChecked ? ' checked="checked"' : '';

            resultHtml += `
                <div class="form-check me-10">
                    <input class="form-check-input" type="radio" name="${groupName}" id="${item.id}" value="${item.value}" ${checkedAttr}>
                    <label class="form-check-label" for="${item.id}">${item.label}</label>
                </div>
            `;
        });

        resultHtml += '</div>';
        return resultHtml;
    };

    /**
     * 初始化单选框组
     * @param {Object} options - 配置项
     * @param {String} options.name - radio input的name属性，必须提供（在单选框组里，name 是浏览器用来把多个 <input type="radio"> 识别为“同一组”的唯一标识）
     * @param {Array} options.radioData - 单选框数据
     * @param {*} options.selectedValue - 默认选中的值
     */
    $.fn.initRadioGroup = function(options) {
        const settings = $.extend({
            name: null,
            radioData: [],
            selectedValue: undefined,
            onChange: null
        }, options);

        if (!settings.name) {
            console.error('Radio group requires a "name" option.');
            return this;
        }

        return this.each(function() {
            const $this = $(this);
            const html = getRadioGroupHtml(settings.radioData, settings);
            $this.empty().html(html);

            // 绑定 onchange 回调
            if (typeof settings.onChange === 'function') {
                $this.on('change', '.form-check-input', function() {
                    const currentValue = isNaN(this.value) ? this.value : Number(this.value);
                    settings.onChange(currentValue);
                });
            }
        });
    };

    /**
     * 获取单选框组选中的值
     * @returns {*} - 选中项的值，如果没有选中则返回 undefined
     */
    $.fn.getRadioGroupValue = function() {
        const $checked = $(this).find('.form-check-input:checked');
        if ($checked.length > 0) {
            const val = $checked.val();
            return isNaN(val) ? val : Number(val);
        }
        return undefined;
    };

    /**
     * 设置单选框组选中的值
     * @param {*} value - 要选中的项的值
     */
    $.fn.setRadioGroupValue = function(value) {
        return this.each(function() {
            $(this).find(`.form-check-input[value="${value}"]`).prop('checked', true);
        });
    };

    /**
     * 设置单选框组中某些项的禁用状态
     * @param {Array|String} values - 要操作的项的值
     * @param {Boolean} isDisabled - true为禁用, false为启用
     */
    $.fn.setRadioGroupDisabled = function(values, isDisabled) {
        return this.each(function() {
            const $container = $(this);
            let valuesToProcess = Array.isArray(values) ? values : [values];

            valuesToProcess.forEach(value => {
                const $input = $container.find(`.form-check-input[value="${value}"]`);
                $input.prop('disabled', isDisabled);
                $input.closest('.form-check').toggleClass('disabled', isDisabled);
            });
        });
    };

})(jQuery);