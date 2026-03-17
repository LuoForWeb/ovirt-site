/**
 * Ajax页面加载的国际化适配器 - 多页面语言包支持版
 */
class AjaxI18nAdapter {
    constructor() {
        this.originalAjax = null;
        this.initialized = false;
        console.log('🔌 AjaxI18nAdapter 初始化...');
        this.waitAndInit();
    }

    waitAndInit() {
        const checkInterval = setInterval(() => {
            if (window.jQuery && window.spaI18n) {
                clearInterval(checkInterval);
                this.init();
            }
        }, 100);

        setTimeout(() => {
            clearInterval(checkInterval);
            if (!this.initialized) {
                console.warn('⚠️ AjaxI18nAdapter 初始化超时');
            }
        }, 10000);
    }

    init() {
        if (this.initialized) return;
        console.log('✅ jQuery 和 spaI18n 已就绪');

        if (window.jQuery) {
            this.originalAjax = jQuery.ajax;
            this.patchJQueryAjax();
        }

        this.setupEventListeners();
        this.initialized = true;
        console.log('✅ AjaxI18nAdapter 初始化完成');
    }

    patchJQueryAjax() {
        const self = this;
        jQuery.ajax = function(options) {
            if (options.url && self.isPageLoadRequest(options)) {
                // 这里需要解析下当前url对应的模块标识，就是语言包的模块 .... 未完待续
                const pageName = self.extractModuleName(options.url);
                const originalSuccess = options.success;
                console.log('pageName', pageName)
                options.success = function(data, textStatus, jqXHR) {
                    if (originalSuccess) {
                        originalSuccess.call(this, data, textStatus, jqXHR);
                    }

                    setTimeout(() => {
                        const container = self.findContentContainer();
                        if (container && window.spaI18n) {
                            // 标记容器所属页面（供 MutationObserver 使用）
                            container.dataset.i18nPage = pageName;

                            // 主动翻译（更可靠）
                            if (typeof window.spaI18n.translateRegion === 'function') {
                                window.spaI18n.translateRegion(container, pageName);
                            }

                            self.registerLoadedPage(pageName, container.id);
                        }

                        document.dispatchEvent(new CustomEvent('ajax.pageLoaded', {
                            detail: { url: options.url, pageName, containerId: container?.id }
                        }));
                    }, 100);
                };
            }
            return self.originalAjax.call(this, options);
        };
    }

    extractModuleName(path) {
        // 处理 platform: 数据保护取第一级
        const dataMatch = path.match(/\/data-management\/([^\/]+)/);
        if (dataMatch) {
            return dataMatch[1];
        }
        // 处理 platform: 取第一级
        const platformMatch = path.match(/\/platform\/([^\/]+)/);
        if (platformMatch) {
            return platformMatch[1];
        }

        // 处理 module: 取第一级
        const moduleMatch = path.match(/\/module\/([^\/]+)/);
        if (moduleMatch) {
            return moduleMatch[1];
        }

        // 处理 resources: 取第一级
        const resourcesMatch = path.match(/\/resources\/([^\/]+)/);
        if (resourcesMatch) {
            return resourcesMatch[1];
        }

        // 处理 system: 取第一级
        const systemMatch = path.match(/\/system\/([^\/]+)/);
        if (systemMatch) {
            return systemMatch[1];
        }

        return 'common';
    }

    isPageLoadRequest(options) {
        return (
            options.url.endsWith('.php') ||
            options.url.includes('/platform/') ||
            options.url.includes('/resources/') ||
            options.url.includes('/system/') ||
            options.url.includes('/module/') ||
            (options.data && typeof options.data === 'string' && options.data.includes('page=')) ||
            (options.data && typeof options.data === 'object' && options.data.page)
        );
    }

    extractPageName(url) {
        if (!url) return 'unknown';
        const match = url.match(/\/([^\/]+)\.html $ /) ||
            url.match(/page=([^&]+)/) ||
            url.match(/\/([^\/]+) $ /);
        return match ? match[1].replace(/\.html|\.php/g, '') : 'unknown';
    }

    findContentContainer() {
        return document.getElementById('main-content') ||
            document.getElementById('content-area') ||
            document.querySelector('.content-wrapper') ||
            document.querySelector('.page-content-body') ||
            document.querySelector('.content-container');
    }

    setupEventListeners() {
        document.addEventListener('spa.i18n.languageChanged', () => {
            this.reTranslateCurrentPage();
        });
    }

    reTranslateCurrentPage() {
        const container = this.findContentContainer();
        const pageName = container?.dataset?.i18nPage || 'common';
        if (container && window.spaI18n?.translateRegion) {
            window.spaI18n.translateRegion(container, pageName);
        }
    }

    registerLoadedPage(pageName, containerId) {
        if (window.spaI18n?.loadedPages) {
            window.spaI18n.loadedPages.add(pageName);
        }
    }
}

// 安全初始化
(function() {
    console.log('🔌 AjaxI18nAdapter 开始加载...');
    const init = () => {
        setTimeout(() => {
            if (!window.ajaxI18n) {
                window.ajaxI18n = new AjaxI18nAdapter();
            }
        }, 500);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();