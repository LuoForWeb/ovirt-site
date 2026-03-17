/*
 * @Author: ChengJiaFu
 * @Date: 2025-04-23 09:55:16
 * @Description: 高级搜索 - 展示搜索条件dropdown组件
 * @version: 1.0
 */
(function($) {

    /**
     * 生成所有条件menu-item html
     * @param {*} conditions 
     * @returns 
     */
    const generateMenuItems = (conditions) => {
        if (!Array.isArray(conditions) || conditions.length === 0) {
            return '';
        }

        let result = '';

        conditions.forEach(item => {
            let html = '';
            html = `<div class="menu-content__item">
                        <span class="menu-content__item__left">
                            <span class="menu-content__item__left__lebel me-4" value="${item.conditionType}">${item.conditionTypeText}：</span>
                            <span class="menu-content__item__left__value" value="${item.conditionVal}">${item.conditionValText}</span>
                        </span>
                        <span class="menu-content__item__clear">
                            <i class="viconfont vicon-a-Close-oneguanbi-copy"></i>
                        </span>
                    </div>`;

            result += html;
        });

        return result;
    }

    $.fn.initAdvancedSearch = function(options) {
        return this.each(function() {
            const $this = $(this);
            const instanceSettings = $.extend({
                advancedSearchSlotId: 'advanced_search_wrapper',
                advancedSearchBtnId: 'advanced_search_btn',
                searchConditions: [],
                totalConditionNumbers: 0
            }, options);
    
            // 将设置和状态保存到当前元素上
            $this.data('advancedSearchSettings', instanceSettings);
            $this.data('selectedConditionNumbers', instanceSettings.searchConditions.length);
            $this.data('totalConditionNumbers', instanceSettings.totalConditionNumbers);
    
            // 初始化 UI 和事件监听器
            const getAdvancedSearchDropdown = () => {
                const selectedCount = $this.data('selectedConditionNumbers');
                const totalCount = $this.data('totalConditionNumbers');
    
                let buttonHtml = `<button id="${instanceSettings.advancedSearchBtnId}" class="btn btn-sm green-haze me-8">
                                    <i class="fa fa-search me-4"></i>${LANG.UI_JOB_SEARCH_EXP}
                                    (<span class="checked-count">${selectedCount}</span>/${totalCount})
                                </button>`;
                
                let dropdownHtml = `<div class="advanced-search-dropdown-menu display-none" id="${instanceSettings.advancedSearchBtnId}_dropdown_menu">
                                        <div class="menu-header">
                                            <span class="menu-header__titme">${LANG.UI_TOOLS_TABLE_SEARCH_CRITERIA}</span>
                                            <span class="menu-header__btn" id="${instanceSettings.advancedSearchBtnId}_clear_all">${LANG.UI_SEARCH_CLEAR}</span>
                                        </div>
                                        <div class="menu-content hover-scroll-y"></div>
                                    </div>`;
                
                return `${buttonHtml}${dropdownHtml}`;
            }
    
            const watchMouseEvent = () => {
                const slotId = instanceSettings.advancedSearchSlotId;
                $(`#${slotId}`).on('mouseenter', function() {
                    const conditions = $this.data('advancedSearchSettings').searchConditions;
                    if (conditions.length > 0) {
                        $(this).find('.advanced-search-dropdown-menu').removeClass('display-none');
                    }
                }).on('mouseleave', function() {
                    const conditions = $this.data('advancedSearchSettings').searchConditions;
                    if (conditions.length > 0) {
                        $(this).find('.advanced-search-dropdown-menu').addClass('display-none');
                    }
                });
            }
    
            const cleanAllConditions = () => {
                const btnId = instanceSettings.advancedSearchBtnId;
                $(`#${btnId}_clear_all`).on('click', function() {
                    const removedConditions = instanceSettings.searchConditions;
                    const removedCondition = {};
                    removedConditions.forEach(item => {
                        removedCondition[item.conditionType] = item.conditionVal;
                    });
    
                    // 清空当前实例数据
                    instanceSettings.searchConditions = [];
                    $this.data('advancedSearchSettings', instanceSettings);
                    $this.data('selectedConditionNumbers', 0);
    
                    // 更新 UI
                    $(`#${btnId}`).find('.checked-count').html(0);
                    $(`#${btnId}_dropdown_menu .menu-content`).html('');
                    $(`#${btnId}_dropdown_menu`).addClass('display-none');
    
                    window.$emit(`${btnId}-removeConditionEvent`, removedCondition);
                });
            }
    
            const initListener = () => {
                watchMouseEvent();
                cleanAllConditions();
            }
    
            // 初始化 UI
            $this.empty().html(getAdvancedSearchDropdown());
            initListener();
        });
    }

    $.fn.updateSearchConditons = function(options) {
        return this.each(function() {
            const $this = $(this);
            const instanceSettings = $this.data('advancedSearchSettings') || {};
            const btnId = options.advancedSearchBtnId;
    
            // 更新搜索条件和选中数
            instanceSettings.searchConditions = options.searchConditions || [];
            $this.data('advancedSearchSettings', instanceSettings);
            $this.data('selectedConditionNumbers', instanceSettings.searchConditions.length);
    
            // 更新按钮计数
            $(`#${btnId}`).find('.checked-count').html(instanceSettings.searchConditions.length);
    
            // 生成菜单项
            const menuItemsHtml = generateMenuItems(instanceSettings.searchConditions);
            $(`#${btnId}_dropdown_menu .menu-content`).html(menuItemsHtml);
    
            // 清除单项逻辑保持不变，但注意使用 $this.data()
            const cleanSingleCondition = () => {
                $(`#${btnId}_dropdown_menu .menu-content`).on('click', '.menu-content__item__clear', function() {
                    const itemElement = $(this).closest('.menu-content__item');
                    const conditionType = itemElement.find('.menu-content__item__left__lebel').attr('value');
                    const conditionVal = itemElement.find('.menu-content__item__left__value').attr('value');
    
                    const removedCondition = { [conditionType]: conditionVal };
                    window.$emit(`${btnId}-removeConditionEvent`, removedCondition);
    
                    const index = instanceSettings.searchConditions.findIndex(
                        item => item.conditionType === conditionType && item.conditionVal === conditionVal
                    );
    
                    if (index !== -1) {
                        instanceSettings.searchConditions.splice(index, 1);
                        $this.data('advancedSearchSettings', instanceSettings);
                        $this.data('selectedConditionNumbers', instanceSettings.searchConditions.length);
    
                        $(`#${btnId}`).find('.checked-count').html(instanceSettings.searchConditions.length);
    
                        if (instanceSettings.searchConditions.length === 0) {
                            $(`#${btnId}_dropdown_menu`).addClass('display-none');
                        }
    
                        $(`#${btnId}_dropdown_menu .menu-content`).html(generateMenuItems(instanceSettings.searchConditions));
                    }
                });
            }
    
            cleanSingleCondition();
        });
    }

})(jQuery);