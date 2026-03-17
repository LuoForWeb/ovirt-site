/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-09 09:31:22
 * @Description: 级联型多复选框组件
 * @version: 1.0
 */

(function($) {
    $.fn.initCascaderCheckboxGroup = function (options) {
        let settings = $.extend({ checkboxGroupsId: 'cascader_checkbox_group', checkboxGroups: [] }, options);

        /**
         * 获取级联单项中的checkbox group组html
         * @param {*} data 
         * @returns 
         */
        const getSingleCheckboxGroup = (data) => {
            if (!Array.isArray(data) || data.length === 0) {
                return '';
            }

            let result = '';

            data.forEach(item => {
                const checkedAttr = item.checked ? 'checked' : ''; // 处理默认选中项场景，用于回显操作
                let html = '';
                html = '<div class="checkboxes-wrap__item form-check form-check-sm">' + 
                            '<input class="form-check-input" name="checkbox" value="' + item.value + '" type="checkbox" id="' + item.id + '" ' + checkedAttr + '>' +
                            '<label class="form-check-label" for="' + item.id + '" title="' + item.text + '">' +
                                item.text +
                            '</label>' +
                        '</div>';

                result += html;
            });

            return result;
        }

        /**
         * 获取级联型复选框组HTML
         * @param {*} data 级联型复选框树形数组（只有两级）
         * @returns 
         */
        const getCascaderCheckboxGroupContent = (data) => {
            if (!Array.isArray(data) || data.length === 0) {
                return '';
            }

            let itemsHtml = '';

            data.forEach(item => {
                itemsHtml += '<div class="cascader-checkbox-group__item">' + 
                            '<div class="header">' + 
                                '<div class="form-check form-check-sm">' + 
                                    '<input class="form-check-input" name="checkbox" value="' + item.value + '" type="checkbox" id="' + item.id + '">' +
                                    '<label class="form-check-label" for="' + item.id + '" title="' + item.text + '">' +
                                        item.text +
                                    '</label>' +
                                '</div>' + 
                            '</div>' +
                            '<div class="content checkboxes-wrap">' + 
                                getSingleCheckboxGroup(item.children) +
                            '</div>' + 
                        '</div>';
            });

            let result = '<div class="cascader-checkbox-group" id="' + settings.checkboxGroupsId + '">';
            result += itemsHtml;
            result += '</div>';

            return result;
        }

        /**
         * 根据子节点状态，更新父节点checkbox的状态
         * @param {*} $parentCheckbox 
         */
        const updateParentState = ($parentCheckbox) => {
            const $parentItem = $parentCheckbox.closest('.cascader-checkbox-group__item');
            const $childrenCheckboxes = $parentItem.find('.content .form-check-input');
            const totalCount = $childrenCheckboxes.length;
            const checkedCount = $childrenCheckboxes.filter(':checked').length;

            const $parentCheckWrapper = $parentCheckbox.closest('.form-check');

            if (checkedCount === 0) {
                // 全部未选
                $parentCheckbox.prop('checked', false);
                $parentCheckbox.prop('indeterminate', false);
                $parentCheckWrapper.removeClass('indeterminate');
            } else if (checkedCount > 0 && checkedCount < totalCount) {
                // 部分选中
                $parentCheckbox.prop('checked', false);
                $parentCheckbox.prop('indeterminate', true);
                $parentCheckWrapper.addClass('indeterminate');
            } else if (checkedCount === totalCount) {
                // 全部选中
                $parentCheckbox.prop('checked', true);
                $parentCheckbox.prop('indeterminate', false);
                $parentCheckWrapper.removeClass('indeterminate');
            }
        }

        const initListener = ($element) => {
            $element.on('change', '.form-check-input', function() {
                const $this = $(this);
                const $parentItem = $this.closest('.cascader-checkbox-group__item');

                if ($this.closest('.header').length > 0) {
                    // --- 点击的是父Checkbox ---
                    const isChecked = $this.prop('checked');
                    const $childrenCheckboxes = $parentItem.find('.content .form-check-input');
                    $childrenCheckboxes.prop('checked', isChecked);
                    
                    // 更新父级自身的半选状态
                    updateParentState($this);

                } else {
                    // --- 点击的是子Checkbox ---
                    const $parentCheckbox = $parentItem.find('.header .form-check-input');
                    updateParentState($parentCheckbox);
                }
            });
        }

        return this.each(function() {
            const $this = $(this);

            // 初始化级联型复选框组
            let html = getCascaderCheckboxGroupContent(settings.checkboxGroups);
            $this.empty().html(html);

            // 根据初始子节点状态，更新父节点checkbox的状态（用于回显）
            $this.find('.cascader-checkbox-group__item').each(function() {
                const $parentCheckbox = $(this).find('.header .form-check-input');
                updateParentState($parentCheckbox);
            });

            // 初始化监听器
            initListener($this);
        });
    }

    $.fn.getCascaderCheckboxGroupValues = function (checkboxGroupsId) {
        const $container = $(`#${checkboxGroupsId}`);

        const result = {};
        $container.find('.cascader-checkbox-group__item').each(function() {
            const $item = $(this);
            const $headerCheckbox = $item.find('.header .form-check-input');
            const headerId = $headerCheckbox.attr('id');
            
            const checkedValues = [];
            $item.find('.content .form-check-input:checked').each(function() {
                checkedValues.push($(this).val());
            });

            if (checkedValues.length > 0) {
                result[headerId] = checkedValues;
            }
        });

        return result;
    }
})(jQuery);