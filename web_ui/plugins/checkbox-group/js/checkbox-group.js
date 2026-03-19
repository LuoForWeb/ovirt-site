/*
 * @Author: ChengJiaFu
 * @Date: 2025-03-10 10:55:16
 * @Description: 复选框组组件
 * @version: 1.0
 */
(function($) {
    /**
     * 生成 checkbox group HTML
     * @param {Array} checkboxData - JSON数组，例如：[{id: 'chk-1', label: '选项一', value: 1}]
     * @param {Array} checkedValues - 需要默认选中的值数组
     * @returns {String} - HTML字符串
     */
    const getCheckboxGroupHtml = (checkboxData, checkedValues = []) => {
        if (!Array.isArray(checkboxData) || checkboxData.length === 0) {
            return '';
        }

        let resultHtml = '<div class="checkbox-group-wrapper">';

        checkboxData.forEach(item => {
            const isChecked = checkedValues.includes(item.value) || item.checked === true;
            const checkedAttr = isChecked ? ' checked="checked"' : '';

            resultHtml += `
                <div class="check-group-wrapper__item">
                    <input class="form-check-input" type="checkbox" value="${item.value}" id="${item.id}" ${checkedAttr}>
                    <label class="form-check-label" for="${item.id}">${item.label}</label>
                </div>
            `;
        });

        resultHtml += '</div>';
        return resultHtml;
    };

    /**
     * 初始化复选框组
     * @param {Object} options - 配置项
     * @param {Array} options.checkboxData - 复选框数据
     * @param {Array} options.checkedValues - 默认选中的值
     */
    $.fn.initCheckboxGroup = function(options) {
        const settings = $.extend({
            checkboxData: [],
            checkedValues: [],
            onChange: null
        }, options);

        return this.each(function() {
            const $this = $(this);
            const html = getCheckboxGroupHtml(settings.checkboxData, settings.checkedValues);
            $this.empty().html(html);

            // 绑定 onchange 回调
            if (typeof settings.onChange === 'function') {
                $this.on('change', '.form-check-input', function() {
                    const currentValue = isNaN(this.value) ? this.value : Number(this.value);
                    const allSelectedValues = $this.getCheckboxGroupValues();
                    settings.onChange(allSelectedValues, currentValue, this.checked);
                });
            }
        });
    };

    /**
     * 获取复选框组选中的值
     * @returns {Array} - 选中值的数组
     */
    $.fn.getCheckboxGroupValues = function() {
        const values = [];
        $(this).find('.form-check-input:checked').each(function() {
            // 将值转为数字（如果可能），以保持一致性
            const val = $(this).val();
            values.push(isNaN(val) ? val : Number(val));
        });
        return values;
    };

    /**
     * 设置复选框组选中的值
     * @param {Array} values - 要选中的值的数组
     */
    $.fn.setCheckboxGroupValues = function(values) {
        return this.each(function() {
            const $this = $(this);
            $this.find('.form-check-input').prop('checked', false); // 先重置所有
            if (Array.isArray(values)) {
                values.forEach(value => {
                    $this.find(`.form-check-input[value="${value}"]`).prop('checked', true);
                });
            }
        });
    };

    /**
     * 设置复选框组中某些项的禁用状态
     * @param {Array|String} values - 要操作的项的值
     * @param {Boolean} isDisabled - true为禁用, false为启用
     */
    $.fn.setCheckboxGroupDisabled = function(values, isDisabled) {
        return this.each(function() {
            const $container = $(this);
            let valuesToProcess = Array.isArray(values) ? values : [values];

            valuesToProcess.forEach(value => {
                const $input = $container.find(`.form-check-input[value="${value}"]`);
                $input.prop('disabled', isDisabled);
                $input.closest('.check-group-wrapper__item').toggleClass('disabled', isDisabled);
            });
        });
    };

})(jQuery);