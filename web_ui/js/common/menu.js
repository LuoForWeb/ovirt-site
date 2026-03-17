/**
 * 页面导航菜单渲染js
*/
var commonMenu = function () {
    /**
     * 导航菜单数据（从接口获取）
     */
    let navPages = []; // 从API获取的数据
    let $pageContent = $('.page-content');

    const initHomePage = () => {
        pAjaxRequest({}, "/api/v2/system/homepage", "GET", function (res) {
            try {
                if (res.success) {
                    // 渲染导航
                    let route = res.data;
                    LOCATION(route.url, route.menuId, route.name);
                }
            } catch (error) {}
        });
    }

    /**
     * 初始化导航菜单
     */
    function initNavigation() {
        try {
            pAjaxRequest({}, "/api/v2/system/menu", "GET", function (d) {
                if (d.success) {
                    // 渲染导航
                    navPages = d.data;
                    renderNavigation();
                } else {
                    operateResponseList(d);
                }
            }, true);
        } catch (error) {}
    }

    /**
     * 渲染导航菜单
     */
    function renderNavigation() {
        const accordion = document.getElementById('sidebar_accordion');
        if (!accordion) return;

        let navHTML = '';

        // 遍历一级菜单
        navPages.forEach(page => {
            // 跳过全局观察者等不需要显示的页面
            if (['global_observer', 'global_read', 'global_write'].includes(page.name)) {
                return;
            }

            navHTML += generateLevelOneNav(page);
        });

        accordion.innerHTML = navHTML;
    }

    /**
     * 生成一级导航
     */
    const generateLevelOneNav = (page) => {
        const hasChild = page.child && page.child.length > 0;

        let accordionItemHtml = '';
        let selected = '';

        if (!hasChild) { // 无子菜单的一级菜单（首页）
            selected = page.name === "homepage" ? "selected" : ""; // 默认选中首页

            // 拼接无子菜单的accordion header
            accordionItemHtml = `
                <div class="accordion-item accordion-item-first first-nav">
                    <div class="accordion-header header-index" id="parent_${page.name}">
                        <a href="${page.path}" name="${page.name}" class="ajaxify accordion-button header-index__button ${selected}" title="${page.title}" data-bs-toggle="collapse" data-bs-target="#child_${page.name}" aria-expanded="true" aria-controls="child_${page.name}">
                            <i class="sidebar-icon first-menu-header-icon ${page.class}"></i>
                            <span class="accordion-button-text header-first__button__text">${page.title}</span>
                        </a>
                    </div>
            `;
        } else { // 有子菜单的一级菜单
            // 拼接有子菜单的accordion header
            accordionItemHtml = `
                <div class="accordion-item accordion-item-first">
                    <div class="accordion-header header-first" id="parent_${page.name}">
                        <button class="accordion-button header-first__button collapsed" data-bs-toggle="collapse" data-bs-target="#child_${page.name}" aria-expanded="true" aria-controls="child_${page.name}">
                            <i class="sidebar-icon first-menu-header-icon ${page.class}"></i>
                            <span class="accordion-button-text header-first__button__text">${page.title}</span>
                        </button>
                    </div>
            `;

            let childHTML = '';
            let hasThirdLevel = false;

            page.child.forEach(child => {
                if (child.level !== 10) { // 跳过level=10的权限项
                    childHTML += generateLevelTwoNav(page.name, child);
                    if (child.showChild && child.child && child.child.length > 0) {
                        hasThirdLevel = true;
                    }
                }
            });

            if (childHTML) {
                accordionItemHtml += `
                    <div class="accordion-collapse collapse accordion-collapse-first" id="child_${page.name}"  data-bs-parent="#sidebar_accordion" aria-labelledby="parent_${page.name}">
                        <div class="accordion-body menu-first-body list-group list-group-flush list-group-first">
                            ${hasThirdLevel
                            ? `<div class="accordion accordion-second" id="accordion_${page.name}">${childHTML}</div>`
                            : childHTML
                        }
                        </div>
                    </div>
                `;
            }
        }

        accordionItemHtml += '</div>';

        return accordionItemHtml;
    }

    /**
     * 生成二级导航
     */
    const generateLevelTwoNav = (parentName, child) => {
        const title = child.title;
        const hasThirdLevel = child.showChild && child.child && child.child.length > 0;

        if (!hasThirdLevel) {
            // 无三级菜单的二级菜单项
            return `
                <a href="${child.path || '#'}" name="${child.name}" class="ajaxify list-group-item list-group-item-action list-group-first__item">
                    <i class="sidebar-icon first-menu-header-icon ${child.class}"></i>
                    <span class="list-group-first__item__text text-overflow-ellipsis" title="${title}">${title}</span>
                </a>
            `;
        } else {
            // 有三级菜单的二级菜单项
            let thirdLevelHTML = '';
            child.child.forEach(thirdChild => {
                thirdLevelHTML += generateLevelThreeNav(thirdChild);
            });

            return `
                <div class="accordion-item accordion-item-second">
                    <div class="accordion-header header-second" id="parent_${child.name}">
                        <button class="accordion-button collapsed header-second__button" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#child_${child.name}" 
                                aria-expanded="true" 
                                aria-controls="child_${child.name}">
                                <i class="sidebar-icon first-menu-header-icon ${child.class}"></i>
                            <span class="accordion-button-text header-second__button__text">${title}</span>
                        </button>
                    </div>
                    <div aria-labelledby="parent_${child.name}" 
                        data-bs-parent="#accordion_${parentName}" 
                        class="accordion-collapse collapse accordion-collapse-second" 
                        id="child_${child.name}">
                        <div class="accordion-body accordion-body-second">
                            <div class="list-group list-group-flush list-group-second">
                                ${thirdLevelHTML}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    /**
     * 生成三级导航
     */
    function generateLevelThreeNav(child) {
        const title = child.title;

        return `
            <a href="${child.path || '#'}" name="${child.name}" class="ajaxify list-group-item list-group-item-action list-group-second__item">
                <i class="sidebar-icon first-menu-header-icon ${child.class}"></i>
                <span class="list-group-second__item__text text-overflow-ellipsis" title="${title}">${title}</span>
            </a>
        `;
    }



    return {
        init: function (options) {
            // 获取导航菜单
            initNavigation();
            // 获取首页url
            initHomePage();
        }
    };
}();

jQuery(document).ready(function() {
    commonMenu.init();
});
