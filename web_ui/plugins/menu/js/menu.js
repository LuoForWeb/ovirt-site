/*
 * @Author: ChengJiaFu
 * @Date: 2025-04-16 10:55:16
 * @Description: 菜单级联组件，用于路由跳转
 * @version: 1.0
 */
(function($) {

    $.fn.initMenu = function(options) {

        let settings = $.extend({ menuSlotId: 'menu_wrapper', menuBtnId: 'menu_btn', operateBtnName: LANG.UI_JOB_NEW_TASK, menuTreeData: [] }, options);

        /**
         * 获取单个menu item html
         * @param {*} m 
         * @returns 
         */
        const getMenuItemHtml = (m) => {
            if (m.route) { // 包含路由时返回带a标签的HTML
                return `<div class="menu-item level${m.level}">
                            <a class="ajaxify menu-item-href" name="${m.routeId}" value="${m.routeUrl}">
                                <span class="menu-item-text level${m.level}">${m.name}</span>
                            </a>
                        </div>`;
            } else { // 不包含路由则直接返回纯文字HTML
                return `<div class="menu-item level${m.level}">
                            <span class="menu-item-text level${m.level}">${m.name}</span>
                        </div>`;
            }
        }

        /**
         * 获取带子项的 menu item html
         * @param {*} m 
         * @returns 
         */
        const getMenuLinkHtml = (m) => {
            return `<div class="menu-item level${m.level}">
                        <div class="menu-link" data-has-sub="true">
                            <span class="menu-item-text level${m.level}">${m.name}</span>
                            <i class="viconfont vicon-gengduo level${m.level}"></i>
                        </div>`;
        }

        /**
         * 获取菜单内容html
         * @param {*} data 
         * @returns 
         */
        const getMenuDropdownContent = (data) => {
            if (!Array.isArray(data) || data.length === 0) {
                return '';
            }

            let result = '';

            data.forEach(item => {
                result += `${item.children && item.children.length > 0 ? getMenuLinkHtml(item) : getMenuItemHtml(item)}`;

                if (item.children && item.children.length > 0) {
                    result += `<div class="menu-sub-dropdown level${item.level + 1}">
                                    ${getMenuDropdownContent(item.children)}
                               </div>`;
                }

                result += `${item.children && item.children.length > 0 ? '</div>' : ''}`;
            });

            return result;
        }

        const getMenuDropdown = (data) => {
            let buttonHtml = `<button id="${settings.menuBtnId}" class="btn btn-dropdown-menu dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                <i class="viconfont vicon-biaogetianjia me-4"></i>${settings.operateBtnName}
                            </button>`;
            
            let dropdownMenuHtml = `<div class="dropdown-menu menu-wrapper menu-sub-dropdown level1" id="${settings.menuBtnId}_dropdown_menu">
                                        ${getMenuDropdownContent(data)}
                                    </div>`;

            return buttonHtml + dropdownMenuHtml;
        }

        const watchMenuMouseEvent = () => {
            $(`#${settings.menuSlotId}`).on('mouseenter', '.menu-item.level1:has(.menu-link)', function() {
                $(this).find('.menu-sub-dropdown.level2').css({
                    'z-index': '106',
                    'position': 'absolute',
                    'inset': '0px auto auto 0px',
                    'margin': '0px',
                    'transform': 'translateX(200px)',
                    'display': 'flex',
                    'background': '#FFFFFF',
                    'box-shadow': '0px 8px 30px 0px rgba(0, 0, 0, 0.08)',
                    'animation': 'menu-sub-dropdown-animation-fade-in .3s ease 1,menu-sub-dropdown-animation-move-up .3s ease 1'
                });
            }).on('mouseleave', '.menu-item.level1:has(.menu-link)', function() {
                $(this).find('.menu-sub-dropdown.level2').css({
                    'z-index': '',
                    'position': '',
                    'inset': '',
                    'margin': '',
                    'transform': '',
                    'display': '',
                    'background': '',
                    'box-shadow': ''
                });
            });

            $(`#${settings.menuSlotId}`).on('mouseenter', '.menu-item.level2:has(.menu-link)', function() {
                $(this).find('.menu-sub-dropdown.level3').css({
                    'z-index': '106',
                    'position': 'absolute',
                    'inset': '0px auto auto 0px',
                    'margin': '0px',
                    'transform': 'translateX(200px)',
                    'display': 'flex',
                    'background': '#FFFFFF',
                    'box-shadow': '0px 8px 30px 0px rgba(0, 0, 0, 0.08)',
                    'animation': 'menu-sub-dropdown-animation-fade-in .3s ease 1,menu-sub-dropdown-animation-move-up .3s ease 1'
                });
            }).on('mouseleave', '.menu-item.level2:has(.menu-link)', function() {
                $(this).find('.menu-sub-dropdown.level3').css({
                    'z-index': '',
                    'position': '',
                    'inset': '',
                    'margin': '',
                    'transform': '',
                    'display': '',
                    'background': '',
                    'box-shadow': ''
                });
            });

            $(`#${settings.menuSlotId} .menu-link[data-has-sub="true"]`).on('click', function(e) {
                e.stopPropagation(); // 阻止事件冒泡，防止 dropdown 关闭
            });
        }

        const watchLinkJump = () => {
            $(`#${settings.menuBtnId}_dropdown_menu .ajaxify`).on('click', function(e) {
                e.stopPropagation(); // 阻止冒泡到 page-content 所监听的 .ajaxify 的 click 事件
                let url = $(this).attr('value');
                let navigation = $(this).attr('name');

                LOCATION(url, navigation);
            })
        }

        const initListener = () => {
            watchMenuMouseEvent();

            watchLinkJump();
        }

        return this.each(function() {
            // 初始化菜单
            let html = getMenuDropdown(settings.menuTreeData);

            $(this).empty().html(html);

            // 初始化监听器
            initListener();
        });
    }

})(jQuery);