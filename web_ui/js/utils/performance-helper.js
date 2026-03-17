/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-28 17:01:43
 * @Description: 性能优化工具函数
 * @version: 1.0
 */
/**
 * bootbox的回调防抖
 * @param {*} func 要执行的函数
 * @param {*} wait 延迟的毫秒数
 * @param {*} hideDialogFlag 是否自动隐藏对话框（默认为true，直接隐藏）
 * @returns
 */
const debounce = (func, wait, hideDialogFlag = true) => {
    // 闭包内部变量，用来保存计时器的引用
    let timeout = null;

    // 返回一个新的函数，这个新函数会在一个延迟时间后执行原始函数func
    return function() {
        // 保存当前上下文和参数，以便稍后使用
        // eslint-disable-next-line consistent-this
        const context = this;
        const args = arguments;
        let $dialog = $(this);

        // 取消按钮不需要抖动
        if ($dialog.hasClass('bootbox-confirm') && args.length && args[0] === false) {  // confirm的取消按钮的参数是false
            $dialog.modal('hide');
        } else if ($dialog.hasClass('bootbox-prompt') && args.length && args[0] === null) {  // prompt的取消按钮的参数是null
            $dialog.modal('hide');
        }

        // 清除现有计时器，如果存在的话
        clearTimeout(timeout);

        // 设置新的计时器，延迟执行func
        timeout = setTimeout(function() {
            // 执行被防抖的函数，并传入之前保存的上下文和参数
            func.apply(context, args);
            if (hideDialogFlag) {
                $dialog.modal('hide');
            }
        }, wait);
        return false;
    };
};

/**
 * 防抖函数
 * @param {*} func 要执行的函数
 * @param {*} wait 延迟的毫秒数
 */
const antiShake = (func, wait) => {
    // 闭包内部变量，用来保存计时器的引用
    let timeout = null;

    // 返回一个新的函数，这个新函数会在一个延迟时间后执行原始函数func
    return function() {
        // 保存当前上下文和参数，以便稍后使用
        const context = this;
        const args = arguments;

        // 清除现有计时器，如果存在的话
        clearTimeout(timeout);

        // 设置新的计时器，延迟执行func
        timeout = setTimeout(function() {
            // 执行被防抖的函数，并传入之前保存的上下文和参数
            func.apply(context, args);
        }, wait);
    };
}