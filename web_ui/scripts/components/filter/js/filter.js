/*
 * @Author: ChengJiaFu
 * @Date: 2025-03-26 10:55:16
 * @Description: 表格toolbar过滤器组件
 * @version: 1.1
 */
(function($) {
    $.fn.initFilter = function(options) {
        let settings = $.extend({ filterBtnId: 'filter_btn', filters: [] }, options);

        let totalCheckBoxes = [];
        let initialCheckedNumbers = 0;
        if (Array.isArray(settings.filters) && settings.filters.length > 0) {
            settings.filters.forEach(item => {
                totalCheckBoxes = totalCheckBoxes.concat(item.value);
                if (Array.isArray(item.value)) {
                    item.value.forEach(opt => {
                        if (opt.checked) {
                            initialCheckedNumbers++;
                        }
                    });
                }
            });
        }

        const getBadge = (badge) => {
            if (badge.tag) {
                switch (badge.type) {
                    case 'success':
                        return `<span class="new-badge new-badge-success">${badge.text}</span>`;
                    case 'warning':
                        return `<span class="new-badge new-badge-warning">${badge.text}</span>`;
                    case 'danger':
                        return `<span class="new-badge new-badge-danger">${badge.text}</span>`;
                    case 'secondary':
                        return `<span class="new-badge new-badge-secondary">${badge.text}</span>`;
                    case 'primary':
                        return `<span class="new-badge new-badge-primary">${badge.text}</span>`;
                    default:
                        return '';
                }
            } else {
                return badge.text;
            }
        };

        const getCheckboxGroupContent = (data) => {
            if (!Array.isArray(data) || data.length === 0) {
                return '';
            }

            let result = '';

            data.forEach(item => {
                const checkedAttr = item.checked ? 'checked' : '';
                let html = '';
                html = '<div class="checkbox-wrapper__content__item form-check form-check-sm">' +
                            '<input class="form-check-input" name="checkbox" value="' + item.value + '" type="checkbox" id="' + item.id + '" ' + checkedAttr + '>' +
                            '<label class="form-check-label" for="' + item.id + '" title="' + item.text + '">' +
                                getBadge(item) +
                            '</label>' +
                        '</div>';

                result += html;
            });

            return result;
        };

        const getDropdownMenuContent = (data) => {
            if (!Array.isArray(data) || data.length === 0) {
                return '';
            }

            let result = '';

            data.forEach(item => {
                let html = '';
                html = '<div class="filter-dropdown-menu__content__item checkbox-wrapper">' +
                            '<span class="checkbox-wrapper__label" name="' + item.field + '">' + item.label + '</span>' +
                            '<div class="checkbox-wrapper__content">' + getCheckboxGroupContent(item.value) + '</div>' +
                        '</div>';

                result += html;
            });

            return result;
        };

        const getFilterDropdown = (data) => {
            let buttonHtml = '<button type="button" id="' + settings.filterBtnId + '" class="btn btn-dropdown-filter dropdown-toggle" data-toggle="dropdown" aria-expanded="false">' +
                                        '<i class="viconfont vicon-shaixuan me-4"></i>' + LANG.UI_JOB_FILTER + '(' +
                                        '<span class="check-count">' + initialCheckedNumbers + '/' + totalCheckBoxes.length + '</span>)' +
                                    '</button>';

            let toggleCheckAllText = (initialCheckedNumbers === totalCheckBoxes.length && totalCheckBoxes.length > 0) ? LANG.UI_JOB_FILTER_UNSELECTALL : LANG.UI_JOB_FILTER_SELECTALL;

            let dropdownMenuHtml = '<div class="dropdown-menu filter-dropdown-menu" id="' + settings.filterBtnId + '_dropdown_menu">' +
                                                '<div class="filter-dropdown-menu__header">' +
                                                    '<span class="filter-dropdown-menu__header__left">' + LANG.UI_JOB_FILTER_OPTIONS + '</span>' +
                                                    '<span class="filter-dropdown-menu__header__right select-wrapper">' +
                                                        '<span class="select-wrapper__item" id="' + settings.filterBtnId + '_toggle_check_all">' + toggleCheckAllText + '</span>' +
                                                    '</span>' +
                                                '</div>' +
                                                '<div class="filter-dropdown-menu__content">' +
                                                    getDropdownMenuContent(data) +
                                                '</div>' +
                                                '<div class="filter-dropdown-menu__footer">' +
                                                    '<div>' +
                                                        '<button type="button" class="btn btn-secondary-primary me-12" id="' + settings.filterBtnId + '_cancel">' + LANG.UI_PUBLIC_CANCEL + '</button>' +
                                                        '<button type="button" class="btn btn-primary" id="' + settings.filterBtnId + '_confirm">' + LANG.UI_PUBLIC_CONFIRM + '</button>' +
                                                    '</div>' +
                                                '</div>' +
                                            '</div>';

            return buttonHtml + dropdownMenuHtml;
        };

        const initListener = ($element) => {
            const state = $element.data('filterState');

            // dropdown-menu 阻止事件冒泡
            $('.filter-dropdown-menu').click(function(e) {
                e.stopPropagation();
            });

            // 全选/取消全选
            const $toggleCheckAllBtn = $(`#${settings.filterBtnId}_toggle_check_all`);
            $toggleCheckAllBtn.off().on('click', () => {
                const isCheckAll = $toggleCheckAllBtn.text() === LANG.UI_JOB_FILTER_SELECTALL;
                const formCheckInputs = $(`#${settings.filterBtnId}_dropdown_menu .form-check-input`);
                const state = $element.data('filterState');

                formCheckInputs.prop('checked', isCheckAll);

                if (isCheckAll) {
                    state.checkedNumbers = state.totalNumbers;
                    $toggleCheckAllBtn.text(LANG.UI_JOB_FILTER_UNSELECTALL);
                } else {
                    state.checkedNumbers = 0;
                    $toggleCheckAllBtn.text(LANG.UI_JOB_FILTER_SELECTALL);
                }

                $(`#${settings.filterBtnId} .check-count`).html(`${state.checkedNumbers}/${state.totalNumbers}`);
                $element.data('filterState', state);
            });

            // checkbox change 事件
            $(`#${settings.filterBtnId}_dropdown_menu .form-check-input`).on('change', function() {
                const currentState = $element.data('filterState');
                if (this.checked) {
                    currentState.checkedNumbers++;
                } else {
                    currentState.checkedNumbers--;
                }
                $(`#${settings.filterBtnId} .check-count`).html(`${currentState.checkedNumbers}/${currentState.totalNumbers}`);

                const $toggleCheckAllBtn = $(`#${settings.filterBtnId}_toggle_check_all`);
                if (currentState.checkedNumbers === currentState.totalNumbers) {
                    $toggleCheckAllBtn.text(LANG.UI_JOB_FILTER_UNSELECTALL);
                } else {
                    $toggleCheckAllBtn.text(LANG.UI_JOB_FILTER_SELECTALL);
                }

                $element.data('filterState', currentState); // 更新状态
            });

            // 取消
            $(`#${settings.filterBtnId}_cancel`).on('click', () => {
                $(`#${settings.filterSlotId}`).removeClass('open');
            });

            // 确定
            $(`#${settings.filterBtnId}_confirm`).on('click', () => {
                let result = [];

                let dropdownMenuContents = $(`#${settings.filterBtnId}_dropdown_menu .filter-dropdown-menu__content`).children('.checkbox-wrapper');

                for (let i = 0; i < dropdownMenuContents.length; i++) {
                    let checkboxLabel = $(dropdownMenuContents[i]).find('.checkbox-wrapper__label');
                    let item = { key: $(checkboxLabel).attr('name'), value: [] };
                    let checkboxContent = $(dropdownMenuContents[i]).find('.checkbox-wrapper__content').find('.checkbox-wrapper__content__item');

                    for (let j = 0; j < checkboxContent.length; j++) {
                        let checkedBox = $(checkboxContent[j]).find('.form-check-input:checked');
                        if (checkedBox.length > 0) {
                            item.value.push($(checkedBox).prop('value'));
                        }
                    }

                    result.push(item);
                }

                if (result.every(i => i.value.length === 0)) {
                    result = [];
                }

                // 关闭dropdown
                $(`#${settings.filterSlotId}`).removeClass('open');

                // 将勾选的过滤数据派发给父组件
                window.$emit(`${settings.filterBtnId}-updateFilterEvent`, result);
            });
        };

        return this.each(function() {
            const $this = $(this);

            // 初始化状态
            $this.data('filterState', {
                checkedNumbers: initialCheckedNumbers,
                totalNumbers: totalCheckBoxes.length
            });

            // 初始化过滤器
            let html = getFilterDropdown(settings.filters);
            $this.empty().html(html);

            // 初始化监听器
            initListener($this);
        });
    };

    $.fn.resetFilter = function(options) {
        return this.each(function() {
            const $this = $(this);
            const state = $this.data('filterState') || {};

            state.checkedNumbers = 0;

            let totalCheckBoxes = [];
            if (Array.isArray(options.filters) && options.filters.length > 0) {
                options.filters.forEach(item => {
                    totalCheckBoxes = totalCheckBoxes.concat(item.value);
                });
            }
            state.totalNumbers = totalCheckBoxes.length;

            $(`#${options.filterBtnId} .check-count`).html(`${state.checkedNumbers}/${state.totalNumbers}`);
            $(`#${options.filterBtnId}_toggle_check_all`).text(LANG.UI_JOB_FILTER_SELECTALL);

            let dropdownMenuContents = $(`#${options.filterBtnId}_dropdown_menu .filter-dropdown-menu__content`).children('.checkbox-wrapper');

            for (let i = 0; i < dropdownMenuContents.length; i++) {
                let checkboxContent = $(dropdownMenuContents[i]).find('.checkbox-wrapper__content').find('.checkbox-wrapper__content__item');

                for (let j = 0; j < checkboxContent.length; j++) {
                    let checkedBox = $(checkboxContent[j]).find('.form-check-input:checked');
                    if (checkedBox.length > 0) {
                        $(checkboxContent[j]).find('.form-check-input').prop('checked', false);
                    }
                }
            }

            $this.data('filterState', state);
        });
    };
})(jQuery);