/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 一键复制插件
 * @Date: 2024-10-17 15:52:10
 * @LastEditTime: 2025-06-19 15:29:47
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */
/**
 * 一键复制插件
 * @param {Object} options - 插件配置选项
 * @param {string|jQuery} options.target - 包含需要复制文本的元素选择器或jQuery对象
 */
(function ($) {
    /**
     * 初始化myClipboard插件
     * @param {Object} options - 插件的配置选项，包括target属性以指定要复制内容的元素选择器
     */
    $.fn.myClipboard = function (options) {
        const _dom = $(this);
        // 默认配置，target属性为null，表示必须通过options参数明确指定
        const defaults = {
            target: null,
        };
        // 将用户提供的选项与默认选项合并，确保settings对象包含所有必要的配置
        const settings = $.extend(defaults, options);

        /**
         * 异步函数，用于复制内容到剪贴板
         * @param {jQuery} $textElement - 包含要复制文本的jQuery元素
         */
        async function copyContent($textElement) {
            // 获取需要复制的文本内容
            const textToCopy = $textElement.text();

            try {
                // 使用Navigator API异步将文本内容写入剪贴板
                await navigator.clipboard.writeText(textToCopy);
                // 复制成功后显示成功提示
                let html = `<div class="clipboard-popup">
                                <i class="viconfont vicon-Frame-15"></i>
                                <span class="popup-text">${LANG.UI_BACKUP_DATA_DETAIL_COPY_SUCCESS}</span>
                            </div>`;

                _dom.append(html);
                setTimeout(() => {
                    document.querySelector('.clipboard-popup').remove();
                }, 3000)
            } catch (err) {
                // 如果复制失败，打印错误信息到控制台
                console.error(LANG.UI_VISUAL_FAIL, err);
            }
        }

        /**
         * 创建一个防抖函数，用于限制函数的执行频率
         * 当频繁触发某个函数时，希望它在一段时间内只执行一次，这便是防抖函数的作用
         * 
         * @param {Function} func 需要进行防抖处理的函数
         * @param {number} wait 防抖等待的时间间隔，单位为毫秒
         * @returns {Function} 返回一个新的防抖函数
         */
        function debounce(func, wait) {
            // 用于存储 setTimeout 的句柄
            let timeout;

            // 返回一个新的函数，该函数会接受传入的参数
            return function (...args) {
                // 在每次调用时，取消之前的 setTimeout，以重新计算等待时间
                clearTimeout(timeout);

                // 设置一个新的 setTimeout，如果在等待时间内没有再次调用函数，那么将执行 func 函数
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // 返回一个jQuery对象，确保插件可以链式调用
        return this.each(function () {
            // 在当前上下文中，$this引用当前DOM元素的jQuery包装
            const $this = $(this);
            // 根据配置选项中的target属性选择元素
            const textElement = $(settings.target);
            // 为当前元素绑定click事件处理函数
            $this.on('click', debounce(function (e) {
                // 当元素被点击时，调用copyContent函数复制内容
                copyContent(textElement);
            }, 300));
        });
    }
})(jQuery);