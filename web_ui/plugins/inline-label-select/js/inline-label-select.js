(function($) {
    /**
     * @author: ChengJiaFu
     * @description: 内联标签下拉选择框 jQuery 插件
     * @param {object} options - 插件配置
     * @param {string} options.label - 内联标签文本
     * @param {Array<object>} options.options - 下拉选项数组, e.g., [{value: 'val1', text: 'Text 1'}]
     * @param {string|number} [options.defaultValue] - 默认选中项的 value
     * @param {string} [options.name] - 隐藏 input 的 name 属性，用于表单提交
     * @param {function(value: string|number, text: string, event: Event|null): void} [options.onchange] - 值改变时的回调函数
     */
    $.fn.initInlineLabelSelect = function(options) {
        // 1. 合并默认设置与用户选项
        const settings = $.extend({
            label: 'Label:',
            options: [],
            defaultValue: null,
            name: '',
            onchange: null // onchange 回调
        }, options);

        // 2. HTML 模板生成函数
        const getComponentHtml = (id) => {
            let optionsHtml = '';
            settings.options.forEach(opt => {
                const customAttributes = Object.keys(opt)
                    .filter(key => key !== 'value' && key !== 'text' && key !== 'disabled')
                    .map(key => `data-${key}="${opt[key]}"`)
                    .join(' ');

                const disabledAttribute = opt.disabled ? 'disabled' : '';

                optionsHtml += `<li data-value="${opt.value}" ${customAttributes} ${disabledAttribute}>${opt.text}</li>`;
            });

            let initialText = '';
            let initialValue = settings.defaultValue;

            if (settings.defaultValue) {
                const defaultOption = settings.options.find(o => o.value == settings.defaultValue);
                if (defaultOption) {
                    initialText = defaultOption.text;
                }
            } else if (settings.options.length > 0) {
                initialText = settings.options[0].text;
                initialValue = settings.options[0].value;
            }

            const hiddenInputName = settings.name || `${id}_value`;
            const inputDisabledAttribute = settings.disabled ? 'disabled' : '';

            return `
                <div class="inline-label-select ${inputDisabledAttribute ? 'disabled' : ''}">
                    <span class="inline-label">${settings.label}</span>
                    <input type="text" class="selected-value" value="${initialText}" ${inputDisabledAttribute} readonly>
                    <input type="hidden" name="${hiddenInputName}" value="${initialValue}">
                    <i class="viconfont vicon-a-Upshang"></i>
                </div>
                <ul class="options-list">
                    ${optionsHtml}
                </ul>
            `;
        };

        // 3. 事件监听初始化函数
        const initListener = ($element) => {
            const $select = $element.find('.inline-label-select');
            const $optionsList = $element.find('.options-list');
            const $selectedValue = $element.find('.selected-value');
            const $hiddenInput = $element.find('input[type="hidden"]');

            // 点击选择框，切换下拉列表
            $select.on('click', function(e) {
                e.stopPropagation();

                if ($selectedValue.prop('disabled')) {
                    return;
                }

                // 先关闭页面上其他可能打开的同类组件
                $('.custom-select-container.open').not($element).removeClass('open');
                $element.toggleClass('open');
            });

            // 点击选项
            $optionsList.on('click', 'li', function(e) {
                const $this = $(this);
                if ($this.is('[disabled]')) {
                    e.stopPropagation();
                    return;
                }
                const newValue = $this.data('value');
                const newText = $this.text();
                const oldValue = $hiddenInput.val();

                // 只有当值改变时才执行后续操作
                if (oldValue == newValue) {
                    $element.removeClass('open');
                    return;
                }

                // 更新显示值和隐藏域的值
                $selectedValue.val(newText);
                $hiddenInput.val(newValue);

                // 更新选中样式
                $this.addClass('selected').siblings().removeClass('selected');

                // 关闭下拉列表
                $element.removeClass('open');

                // 触发原生 change 事件和自定义事件，方便外部监听
                $hiddenInput.trigger('change');
                $element.trigger('selectionChanged.inline.select', { value: newValue, text: newText });

                // 执行 onchange 回调
                const onchangeCallback = $element.data('onchange');
                if (typeof onchangeCallback === 'function') {
                    onchangeCallback.call($element[0], newValue, newText, e);
                }
            });
        };

        // 4. 插件主逻辑
        const result = this.each(function() {
            const $this = $(this);
            const id = this.id;
            if (!id) {
                console.error('Target element for inlineLabelSelect must have an ID.');
                return;
            }

            // 添加基础 class
            $this.addClass('custom-select-container');

            // 生成并注入 HTML
            const html = getComponentHtml(id);
            $this.empty().html(html);

            // 存储 onchange 回调
            $this.data('onchange', settings.onchange);

            // 获取初始值并存储，用于 reset
            const initialValue = $this.find('input[type="hidden"]').val();
            $this.data('initial-value', initialValue);

            // 初始化列表中的选中项
            if (initialValue) {
                $this.find(`.options-list li[data-value="${initialValue}"]`).addClass('selected');
            }

            // 绑定事件
            initListener($this);
        });

        // 点击外部区域关闭所有打开的下拉
        $(document).on('click.inlineSelect', function(e) {
            if (!$(e.target).closest('.custom-select-container').length) {
                $('.custom-select-container.open').removeClass('open');
            }
        });

        return result;
    };

    /**
     * @description: 外部方法：设置选中值
     * @param {string|number} value - 要设置的项的 value
     */
    $.fn.setInlineSelectValue = function(value) {
        return this.each(function() {
            const $container = $(this);
            if (!$container.hasClass('custom-select-container')) return;

            const $optionsList = $container.find('.options-list');
            const $selectedValue = $container.find('.selected-value');
            const $hiddenInput = $container.find('input[type="hidden"]');
            const oldValue = $hiddenInput.val();

            // 只有当值改变时才执行
            if (oldValue == value) {
                return;
            }

            const $targetOption = $optionsList.find(`li[data-value="${value}"]`);
            if ($targetOption.length) {
                const newText = $targetOption.text();

                $selectedValue.val(newText);
                $hiddenInput.val(value);

                $targetOption.addClass('selected').siblings().removeClass('selected');

                // 触发事件
                $hiddenInput.trigger('change');
                $container.trigger('selectionChanged.inline.select', { value: value, text: newText });

                // 执行 onchange 回调
                const onchangeCallback = $container.data('onchange');
                if (typeof onchangeCallback === 'function') {
                    onchangeCallback.call($container[0], value, newText, null);
                }
            }
        });
    };

    /**
     * @description: 外部方法：重置为初始值
     */
    $.fn.resetInlineSelect = function() {
        return this.each(function() {
            const $container = $(this);
            const initialValue = $container.data('initial-value');
            if (initialValue !== undefined) {
                $container.setInlineSelectValue(initialValue);
            }
        });
    };

})(jQuery);