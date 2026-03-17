/**
 * 事件总线系统 - 防重复监听版
 * 
 * 提供全局事件管理功能：
 * - $emit: 触发事件
 * - $on: 监听事件（防重复添加）
 * - $off: 移除事件监听
 * - $hasListener: 检查事件是否有监听器
 * - $listenerCount: 获取事件监听器数量
 * - $once: 一次性事件监听
 * - $clearAll: 清理所有事件
 */

(function(window) {
    'use strict';

    // 用于存储自定义事件的全局对象
    const handlerGather = {};

    /**
     * 全局派发更新方法
     * @param {string|Array} event - 派发更新的事件名
     * @param {*} data - 回调函数传递的数据
     * @returns {Window} 返回 window 对象
     */
    window.$emit = function(event, data) {
        const fn = (ev, d) => {
            // 检查事件是否存在且为数组
            if (handlerGather[ev] && Array.isArray(handlerGather[ev])) {
                const handlers = handlerGather[ev];
                for (let i = 0; i < handlers.length; i++) {
                    const handler = handlers[i];
                    // 派发更新数据
                    try {
                        handler(d);
                    } catch (error) {
                        console.error(`Error in event handler for "${ev}":`, error);
                    }
                }
            }
        };
        
        if (Array.isArray(event)) {
            // 事件是一个数组时
            event.forEach((item) => {
                fn(item, data);
            });
        } else {
            // 事件只有一个时
            fn(event, data);
        }
        return window;
    };

    /**
     * 全局监听方法 - 自动移除之前的相同回调，防止重复监听
     * @param {string|Array} event - 监听的事件名
     * @param {Function} fn - 回调函数
     * @returns {Window} 返回 window 对象
     */
    window.$on = function(event, fn) {
        // 检查回调是否为函数
        if (typeof fn !== 'function') {
            console.warn('EventBus: callback must be a function');
            return window;
        }
        
        if (Array.isArray(event)) {
            // 监听的事件是一个数组时：遍历取出每个事件单独监听
            event.forEach((item) => {
                window.$on(item, fn);
            });
        } else {
            // 检查是否已经存在相同的回调函数，避免重复添加
            if (!handlerGather[event]) {
                handlerGather[event] = [];
            } else {
                // 移除已存在的相同回调函数
                const existingIndex = handlerGather[event].indexOf(fn);
                if (existingIndex !== -1) {
                    handlerGather[event].splice(existingIndex, 1);
                }
            }
            // 添加新的回调函数
            handlerGather[event].push(fn);
        }
        return window;
    };

    /**
     * 销毁全局自定义事件
     * @param {string|Array} event - 需要销毁的事件名
     * @param {Function} fn - 要移除的具体回调函数，如果不传则移除所有
     * @returns {Window} 返回 window 对象
     */
    window.$off = function(event, fn) {
        // 如果提供了具体的回调函数，则只移除该回调，否则移除所有
        if (fn) {
            if (Array.isArray(event)) {
                event.forEach((item) => {
                    if (handlerGather[item]) {
                        const index = handlerGather[item].indexOf(fn);
                        if (index !== -1) {
                            handlerGather[item].splice(index, 1);
                        }
                    }
                });
            } else {
                if (handlerGather[event]) {
                    const index = handlerGather[event].indexOf(fn);
                    if (index !== -1) {
                        handlerGather[event].splice(index, 1);
                    }
                }
            }
        } else {
            // 移除所有回调
            if (Array.isArray(event)) {
                // 销毁的事件为数组时
                event.forEach((item) => {
                    if (handlerGather[item]) {
                        // 销毁对应事件
                        handlerGather[item] = [];
                    }
                });
            } else {
                // 修复BUG：检查事件是否存在
                if (handlerGather[event]) {
                    handlerGather[event] = [];
                }
            }
        }
        return window;
    };

    /**
     * 检查事件是否有监听器
     * @param {string} event - 事件名
     * @returns {boolean}
     */
    window.$hasListener = function(event) {
        return handlerGather[event] && 
               Array.isArray(handlerGather[event]) && 
               handlerGather[event].length > 0;
    };

    /**
     * 获取事件监听器数量
     * @param {string} event - 事件名
     * @returns {number}
     */
    window.$listenerCount = function(event) {
        if (handlerGather[event] && Array.isArray(handlerGather[event])) {
            return handlerGather[event].length;
        }
        return 0;
    };

    /**
     * 一次性事件监听 - 监听器在触发后自动移除
     * @param {string} event - 监听的事件名
     * @param {Function} fn - 回调函数
     * @returns {Window} 返回 window 对象
     */
    window.$once = function(event, fn) {
        if (typeof fn !== 'function') {
            console.warn('EventBus: callback must be a function');
            return window;
        }
        
        const onceFn = function(...args) {
            fn.apply(this, args);
            window.$off(event, onceFn);
        };
        
        return window.$on(event, onceFn);
    };

    /**
     * 清理所有事件（谨慎使用）
     */
    window.$clearAll = function() {
        for (let event in handlerGather) {
            if (handlerGather.hasOwnProperty(event)) {
                handlerGather[event] = [];
            }
        }
    };

})(window);