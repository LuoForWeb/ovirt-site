/**
 * @file toastr.js 的封装器，用于适配项目既有的 API。
 * @description 该文件会暴露一个全局的 UIToastr 对象，其包含了 showSuccess, showInfo 等方法。
 * 为了实现无缝集成，方法的参数顺序从原版的 (message, title) 调整为 (title, message)。
 *
 * @requires jquery.js - toastr.js 插件的依赖
 * @requires toastr.js - 核心通知插件
 */
var UIToastr = (function () {
    // 检查核心的 toastr 对象是否存在。
    if (typeof toastr === 'undefined') {
        // 如果 toastr.js 未加载，在控制台打印错误，方便调试。
        console.error('UIToastr 依赖 toastr.js，请检查该文件是否正确加载。');

        // 定义一个空的“虚拟”函数。
        var dummy = function() {};
        // 返回一个所有方法都指向空函数的对象，以防止在生产环境中因插件缺失而导致页面脚本崩溃。
        return {
            showSuccess: dummy,
            showInfo: dummy,
            showWarning: dummy,
            showError: dummy,
            clear: dummy,
            remove: dummy,
            options: {}
        };
    }

    // --- 全局默认配置 ---
    // 在这里可以统一配置所有 toast 通知的默认行为。
    // 使用 jQuery.extend 是为了在不覆盖 toastr.js 内部其它默认值的情况下，仅修改我们关心的部分。
    toastr.options = $.extend(toastr.options, {
        closeButton: true, // 是否显示关闭按钮
        progressBar: true, // 是否显示进度条
        positionClass: 'toast-top-center', // 默认显示位置
        timeOut: 3000, // 默认3秒后自动关闭 (0 表示不自动关闭)
        extendedTimeOut: 1000, // 鼠标悬停后，移开的延长显示时间
        preventDuplicates: true, // 防止弹出内容完全相同的通知
        newestOnTop: true, // 新弹出的通知显示在最上面
        escapeHtml: false // 不对 title 和 message 进行 HTML 转义，方便传入链接等
    });


    /**
     * 这是将要作为 UIToastr 暴露到全局的最终对象。
     */
    return {
        /**
         * 显示一个成功的通知。
         * @param {string} title - 通知的标题。
         * @param {string} message - 通知的主体内容。
         * @param {object} [optionsOverride] - (可选) 针对本次调用覆盖全局默认配置。
         */
        showSuccess: function (title, message = '', optionsOverride) {
            // 注意参数顺序的交换：toastr 的第一个参数是 message，第二个是 title。
            return toastr.success(message, title, optionsOverride);
        },

        /**
         * 显示一个信息的通知。
         * @param {string} title - 通知的标题。
         * @param {string} message - 通知的主体内容。
         * @param {object} [optionsOverride] - (可选) 针对本次调用覆盖全局默认配置。
         */
        showInfo: function (title, message = '', optionsOverride) {
            return toastr.info(message, title, optionsOverride);
        },

        /**
         * 显示一个警告的通知。
         * @param {string} title - 通知的标题。
         * @param {string} message - 通知的主体内容。
         * @param {object} [optionsOverride] - (可选) 针对本次调用覆盖全局默认配置。
         */
        showWarning: function (title, message = '', optionsOverride) {
            return toastr.warning(message, title, optionsOverride);
        },

        /**
         * 显示一个错误的通知。
         * @param {string} title - 通知的标题。
         * @param {string} message - 通知的主体内容。
         * @param {object} [optionsOverride] - (可选) 针对本次调用覆盖全局默认配置。
         */
        showError: function (title, message = '', optionsOverride) {
            return toastr.error(message, title, optionsOverride);
        },

        // --- 保持对原版其他重要方法的引用 ---
        // 这样做可以确保如果项目中有代码用到了 toastr 的其他功能（如手动清除），也能平滑过渡。

        /**
         * 清除页面上所有或指定的 toast 通知。
         * @function
         */
        clear: toastr.clear,

        /**
         * 移除页面上所有或指定的 toast 通知（与 clear 略有不同）。
         * @function
         */
        remove: toastr.remove,

        /**
         * 获取 toast 通知的容器元素。
         * @function
         */
        getContainer: toastr.getContainer,

        /**
         * 订阅 toast 的事件。
         * @function
         */
        subscribe: toastr.subscribe,

        /**
         * 暴露原版的 options 对象，允许在外部进行更灵活的全局配置。
         * 例如：UIToastr.options.positionClass = 'toast-bottom-right';
         */
        options: toastr.options
    };
})();