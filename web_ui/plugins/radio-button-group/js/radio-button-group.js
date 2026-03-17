/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-30 18:18:31
 * @Description: 单选按钮组插件
 * @version: 1.0
 */

(function($) {
    $.fn.initRadioButtonGroup = function(options) {
        // 获取默认设置
        const settings = $.extend({
            radioGroupId: `radio_group_${options.radioGroupId}`,
            data: [], // { useSvg: false, svgUrl: '', useIcon: true, iconClass: 'vicon-cunchushebei', text: '存储', value: '1' }
            defaultValue: null, // 默认选中的值
            onchange: null // change事件回调
        }, options);

        // 动态生成HTML
        const getRadioGroupHtml = (data) => {
            if (!Array.isArray(data) || data.length === 0) {
                return '';
            }

            let radiosHtml = data.map(item => {
                const radioId = `${settings.radioGroupId}_${item.value}`;
                // 检查是否需要默认选中
                const isChecked = settings.defaultValue !== null && settings.defaultValue == item.value;
                const isDisabled = item.disabled === true;
                let resultHtml = '';

                if (item.useSvg) { // 使用 svg 形式的单选按钮
                    resultHtml = `
                        <label class="radio-group__item with-svg ${isChecked ? 'active' : ''} ${isDisabled ? 'disabled' : ''}" id="${radioId}" value="${item.value}">
                            <img src="${item.svgUrl}" />
                        </label>
                    `;
                } else {
                    resultHtml = `
                        <label class="radio-group__item ${isChecked ? 'active' : ''} ${isDisabled ? 'disabled' : ''}" id="${radioId}" value="${item.value}">
                            ${item.useIcon ? `<i class="viconfont ${item.iconClass}"></i>` : ''}
                            ${item.text}
                        </label>
                    `;
                }

                return resultHtml;
            }).join('');

            return `<div class="radio-group">${radiosHtml}</div>`;
        };

        // 初始化监听器
        const initListener = ($element) => {
            // 使用事件委托，为容器内的 .radio-group__item 绑定点击事件
            $element.on('click', '.radio-group__item', function(e) {
                const $currentItem = $(this);

                // 如果点击的项是禁用的，则不执行任何操作
                if ($currentItem.hasClass('disabled')) {
                    return;
                }

                // 如果点击的已经是选中项，则不执行任何操作，避免重复触发 onchange
                if ($currentItem.hasClass('active')) {
                    return;
                }

                // 移除所有兄弟元素的 active 类，并为当前项添加
                $currentItem.addClass('active').siblings().removeClass('active');

                // 如果定义了 onchange 回调，则执行它
                if (settings.onchange && typeof settings.onchange === 'function') {
                    const currentValue = $currentItem.attr('value');
                    // 将当前选中的值和原始事件对象作为参数传递
                    settings.onchange(currentValue);
                }
            });
        };
        
        // 主逻辑：遍历并初始化每个目标元素
        return this.each(function() {
            const $this = $(this);

            // 将 onchange 回调函数存储在元素的 data 属性中，以便 setRadioButtonGroupValue 方法调用
            $this.data('onchange', settings.onchange);

            // 生成HTML并插入
            const html = getRadioGroupHtml(settings.data);
            $this.empty().html(html);

            // 初始化事件监听
            initListener($this);
        });
    };
    
    /**
     * 设置单选按钮组的选中值
     * @param {*} value 要设置为选中的值
     * @param {*} extraParam 可选的额外参数，用于在 onchange 回调中传递
     * @returns 
     */
    $.fn.setRadioButtonGroupValue = function(value, extraParam) {
         // return this 以支持链式调用
        return this.each(function() {
            const $container = $(this);
            const $items = $container.find('.radio-group__item'); // 找到容器内所有的按钮项
            const onchange = $container.data('onchange'); // 获取初始化时存储的 onchange 回调
            
            // 获取当前激活项的值
            const currentValue = $container.find('.radio-group__item.active').attr('value');

            // 如果设置的值与当前值相同，则不执行任何操作
            if (value === currentValue) {
                // 但如果值相同，但有额外参数，可能意味着需要用额外参数更新下游组件
                if (typeof onchange === 'function' && extraParam !== undefined) {
                    onchange(value, extraParam);
                }
                return;
            }

            $items.removeClass('active');

            if (value !== null && value !== undefined) {
                $container.find(`.radio-group__item[value="${value}"]`).addClass('active');
            }

            // 如果存在 onchange 回调，则执行它，并传递额外参数
            if (value !== null && value !== undefined && typeof onchange === 'function') {
                onchange(value, extraParam);
            }
        });
    };

    $.fn.getRadioButtonGroupValue = function() {
        // 找到当前容器下被选中的项
        const $activeItem = this.find('.radio-group__item.active');
        // 如果有选中项，则返回它的 value，否则返回 undefined
        return $activeItem.length > 0 ? $activeItem.attr('value') : undefined;
    };

    /**
     * 动态设置一个或多个单选按钮的禁用状态
     * @param {string|string[]} values - 要设置禁用状态的按钮的 value 值，可以是单个值或一个数组
     * @param {boolean} isDisabled - true 表示禁用，false 表示启用
     * @returns 
     */
    $.fn.setRadioButtonGroupDisabled = function(values, isDisabled) {
        return this.each(function() {
            const $container = $(this);
            const $items = $container.find('.radio-group__item'); // 找到容器内所有的按钮项
            $items.removeClass('disabled'); // 先重置所有项的 disabled 类
            
            let valueArr = values;
            if (!Array.isArray(valueArr)) {
                valueArr = [valueArr];
            }

            valueArr.forEach(value => {
                const $item = $container.find(`.radio-group__item[value="${value}"]`);
                if (isDisabled) {
                    $item.addClass('disabled');
                } else {
                    $item.removeClass('disabled');
                }
            });
        });
    };

})(jQuery);