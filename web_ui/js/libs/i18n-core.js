/**
 * SPA国际化核心管理器 - 多页面语言包支持版
 */
class SPAI18n {
    constructor(options = {}) {
        this.config = Object.assign({
            defaultLang: 'zh-cn',
            apiEndpoint: '/api/v2/lang',
            autoLoadCommon: true,
            debug: true,
            preventFlash: true,
            cacheEnabled: true,
            cacheDuration: 3600000 // 1小时
        }, options);

        // 初始化属性
        this.translations = {};
        this.pageTranslations = {};
        this.currentLang = 'zh-cn';
        this.loadedPages = new Set();
        this.initialized = false;
        this.isLoading = false;

        // 新增：待翻译队列 & 加载状态
        this.pendingElements = new Map(); // pageName → [elements]
        this.pageLoadPromises = new Map(); // pageName → Promise

        console.log('🚀 SPA I18n 创建');
        this.startInitialization();
    }

    async startInitialization() {
        if (this.isLoading) return;
        this.isLoading = true;

        try {
            this.currentLang = this.getCurrentLanguage();
            document.documentElement.lang = this.currentLang;

            if (this.config.autoLoadCommon) {
                await this.loadTranslationData('common');
            }

            this.translatePageImmediately();
            this.setupMutationObserver();
            this.initialized = true;
            this.fireEvent('ready', { lang: this.currentLang });

            console.log('✅ i18n初始化完成');
        } catch (error) {
            //console.error('❌ i18n初始化失败:', error);
            this.initialized = true;
            this.fireEvent('error', { error });
        } finally {
            this.isLoading = false;
        }
    }

    getCurrentLanguage() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const urlLang = urlParams.get('lang');
            if (urlLang) {
                localStorage.setItem('spa_i18n_lang', urlLang);
                return urlLang;
            }
            const storedLang = localStorage.getItem('spa_i18n_lang');
            if (storedLang) return storedLang;
            const browserLang = navigator.language || '';
            return browserLang.startsWith('zh') ? 'zh-cn' : 'en-us';
        } catch (error) {
            return this.config.defaultLang;
        }
    }

    async loadTranslationData(pageName, lang = this.currentLang) {
        console.log(`📥 加载翻译:  ${pageName} ( ${lang})`);

        // 缓存（调试时可临时关闭）
        if (this.config.cacheEnabled) {
            const cacheKey = `spa_i18n_ ${pageName}_ ${lang}`;
            const cached = localStorage.getItem(cacheKey);
            if (cached) {
                try {
                    const cachedData = JSON.parse(cached);
                    const cacheAge = Date.now() - cachedData.timestamp;
                    if (cacheAge < this.config.cacheDuration) {
                        console.log(`📦 从缓存加载:  ${pageName}`);
                        // ✅ 关键：将当前模块的语言包合并到全局 LANG
                        Object.assign(LANG, cachedData.data);
                        this.pageTranslations[pageName] = cachedData.data;
                        this.mergeTranslations();
                        return cachedData.data;
                    }
                } catch (e) {
                    console.warn('缓存读取失败:', e);
                }
            }
        }

        try {
            const url = ` ${this.config.apiEndpoint}?module=${encodeURIComponent(pageName)}&lang=${encodeURIComponent(lang)}&_= ${Date.now()}`;
            const token = localStorage.getItem('csrf_token') || '';
            const authToken = localStorage.getItem('access_token') || '';

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Csrf-Token': token,
                    'x-api-version': '2.0-rev0',
                    'Authorization': authToken
                },
                credentials: 'include'
            });

            if (!response.ok) throw new Error(`HTTP  ${response.status}`);
            const result = await response.json();

            if (result.code === 0) {
                // ✅ 关键：将当前模块的语言包合并到全局 LANG
                Object.assign(LANG, result.data);

                this.pageTranslations[pageName] = result.data;
                this.mergeTranslations();

                if (this.config.cacheEnabled) {
                    localStorage.setItem(`spa_i18n_ ${pageName}_ ${lang}`, JSON.stringify({
                        data: result.data,
                        timestamp: Date.now()
                    }));
                }

                console.log(`✅ 加载完成:  ${pageName}`);
                return result.data;
            } else {
                throw new Error(`API错误:  ${result.message}`);
            }
        } catch (error) {
           // console.error(`❌ 加载失败:  ${pageName}`, error);
            throw error;
        }
    }

    mergeTranslations() {
        this.translations = {};
        Object.values(this.pageTranslations).forEach(pageData => {
            if (pageData && typeof pageData === 'object') {
                Object.assign(this.translations, pageData);
            }
        });
        console.log('🔗 合并翻译完成，总数:', Object.keys(this.translations).length);
    }

    translatePageImmediately() {
        this.translateCriticalElements();
        setTimeout(() => this.translateAllElements(), 10);
    }

    translateCriticalElements() {
        const titleEl = document.querySelector('title[data-i18n]');
        if (titleEl) {
            const key = titleEl.getAttribute('data-i18n');
            if (key && this.translations[key]) {
                document.title = this.translations[key];
            }
        }
    }

    translateAllElements() {
        let count = 0;
        document.querySelectorAll('[data-i18n], [data-i18n-placeholder], [data-i18n-title]').forEach(el => {
            this._doTranslateElement(el);
            count++;
        });
        console.log(`🎯 翻译了  ${count} 个元素`);
    }

    /**
     * 对外接口：翻译指定区域（用于 AJAX 后手动触发）
     */
    translateRegion(container, pageName = 'common') {
        if (!container || !this.initialized) return;

        // 先确保语言包加载
        this.ensurePageLoaded(pageName).then(() => {
            // 扫描并翻译
            const elements = container.querySelectorAll(
                '[data-i18n], [data-i18n-placeholder], [data-i18n-title]'
            );
            elements.forEach(el => this._doTranslateElement(el));
            console.log(`🌍 区域翻译完成 ( ${pageName})`);
        }).catch(err => {
           // console.warn('区域翻译跳过:', err);
        });
    }

    /**
     * 确保某页面语言包已加载（带防重）
     */
    async ensurePageLoaded(pageName) {
        if (this.pageTranslations[pageName]) return;
        if (this.pageLoadPromises.has(pageName)) {
            return this.pageLoadPromises.get(pageName);
        }

        const promise = this.loadTranslationData(pageName);
        this.pageLoadPromises.set(pageName, promise);

        try {
            await promise;
            this._flushPendingElements(pageName);
        } catch (error) {
            this._flushPendingElements(pageName, true); // 强制清空
            throw error;
        } finally {
            this.pageLoadPromises.delete(pageName);
        }
    }

    /**
     * MutationObserver 调用：翻译单个新元素
     */
    translateNewElement(element, pageName = 'common') {
        if (this.initialized && this.pageTranslations[pageName]) {
            this._doTranslateElement(element);
            return;
        }

        // 加入待翻译队列
        if (!this.pendingElements.has(pageName)) {
            this.pendingElements.set(pageName, []);
        }
        this.pendingElements.get(pageName).push(element);
        this.ensurePageLoaded(pageName).catch(() => {});
    }

    /**
     * 安全翻译单个元素：仅当翻译存在时才替换
     */
    _doTranslateElement(el) {
        // 保存原始内容（用于 fallback）
        if (!el._originalText && el.hasAttribute('data-i18n')) {
            el._originalText = el.textContent;
        }
        if (!el._originalPlaceholder && el.hasAttribute('data-i18n-placeholder')) {
            el._originalPlaceholder = el.placeholder;
        }
        if (!el._originalTitle && el.hasAttribute('data-i18n-title')) {
            el._originalTitle = el.title;
        }

        // 文本内容
        const key = el.getAttribute('data-i18n');
        if (key) {
            const translated = this.translations[key];
            if (translated !== undefined && translated !== null && translated !== '') {
                el.textContent = translated;
            } else {
                // 没有翻译 → 恢复原始内容（如果有）
                el.textContent = el._originalText || key;
            }
        }

        // placeholder
        const phKey = el.getAttribute('data-i18n-placeholder');
        if (phKey) {
            const translated = this.translations[phKey];
            if (translated !== undefined && translated !== null && translated !== '') {
                el.placeholder = translated;
            } else {
                el.placeholder = el._originalPlaceholder || phKey;
            }
        }

        // title
        const titleKey = el.getAttribute('data-i18n-title');
        if (titleKey) {
            const translated = this.translations[titleKey];
            if (translated !== undefined && translated !== null && translated !== '') {
                el.title = translated;
            } else {
                el.title = el._originalTitle || titleKey;
            }
        }
    }

    _flushPendingElements(pageName, force = false) {
        const elements = this.pendingElements.get(pageName) || [];
        elements.forEach(el => {
            if (force || this.pageTranslations[pageName]) {
                this._doTranslateElement(el);
            }
        });
        this.pendingElements.delete(pageName);
    }

    async changeLanguage(lang) {
        if (lang === this.currentLang) return;
        this.currentLang = lang;
        localStorage.setItem('spa_i18n_lang', lang);
        document.documentElement.lang = lang;

        this.translations = {};
        this.pageTranslations = {};
        this.pendingElements.clear();

        if (this.config.autoLoadCommon) {
            await this.loadTranslationData('common', lang);
        }
        this.translatePageImmediately();
        this.fireEvent('languageChanged', { lang });
    }

    setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType !== 1) return;

                    // 尝试获取 pageName（从最近的 data-i18n-page 容器）
                    let pageName = 'common';
                    let el = node;
                    while (el && el !== document) {
                        if (el.dataset?.i18nPage) {
                            pageName = el.dataset.i18nPage;
                            break;
                        }
                        el = el.parentElement;
                    }

                    // 翻译当前节点及子节点
                    if (node.hasAttribute('data-i18n') ||
                        node.hasAttribute('data-i18n-placeholder') ||
                        node.hasAttribute('data-i18n-title')) {
                        this.translateNewElement(node, pageName);
                    }
                    node.querySelectorAll('[data-i18n], [data-i18n-placeholder], [data-i18n-title]')
                        .forEach(child => this.translateNewElement(child, pageName));
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    t(key, defaultValue = '') {
        return this.translations[key] || defaultValue || key;
    }

    fireEvent(event, data = {}) {
        try {
            document.dispatchEvent(new CustomEvent(`spa.i18n. ${event}`, {
                detail: { i18n: this, ...data }
            }));
        } catch (error) {
           // console.error('触发事件失败:', error);
        }
    }
}

// 创建全局实例
try {
    window.spaI18n = new SPAI18n({
        apiEndpoint: '/api/v2/lang',
        autoLoadCommon: true,
        debug: true,
        preventFlash: true
    });
} catch (error) {
    //console.error('❌ 创建 SPA I18n 实例失败:', error);
    window.spaI18n = {
        t: (key, def) => def || key,
        translateRegion: () => {},
        changeLanguage: () => Promise.resolve(),
        initialized: true
    };
    setTimeout(() => {
        document.dispatchEvent(new CustomEvent('spa.i18n.ready'));
    }, 100);
}

// 快捷函数
window.__ = (key, def) => window.spaI18n?.t?.(key, def) || def || key;