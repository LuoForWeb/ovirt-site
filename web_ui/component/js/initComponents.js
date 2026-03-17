var initComponents = (() => {

    /**
     * 初始化按钮样式的单选框组
     */
    const initRadioButtons = () => {
        const LEFT_AND_RIGHT_PADDING_WIDTH = 40; // 左右总padding宽度
        const LEFT_AND_RIGHT_BORDER_WIDTH = 2; // 左右总border宽度
        let radioButtonElList = [].slice.call(document.querySelectorAll('.radio-group'));

        radioButtonElList.forEach(el => {
            let max_width = 0;
            $(el).find('.radio-group__item:not(.with-svg)').each(function() {  
                // 获取每一组 radio-group 中 radio-group__item 的完整宽度（自身宽度 + 左右padding + 左右border）
                let fullWidth = $(this).width() + LEFT_AND_RIGHT_PADDING_WIDTH + LEFT_AND_RIGHT_BORDER_WIDTH;  
                if(fullWidth > max_width) {
                    max_width = fullWidth;
                }
            });
            $(el).find('.radio-group__item:not(.with-svg)').each(function() {
                // 将每一组 radio-group 中 radio-group__item 的最大宽度设置为其余项的宽度
                $(this).width(max_width - LEFT_AND_RIGHT_PADDING_WIDTH - LEFT_AND_RIGHT_BORDER_WIDTH);
            });

            $(el).on('click', '.radio-group__item', function(e) {
                let oldValue = $(el).find('.radio-group__item.active').attr("value");
                // 移除上一个的active样式
                $(el).find('.radio-group__item.active').removeClass('active');

                // 触发 change 事件
                let newValue = $(this).attr("value");
                if (oldValue !== newValue) {
                    $(el).trigger('change', [oldValue, newValue]);
                }

                let radioButtonItemList = [].slice.call(el.querySelectorAll('.radio-group__item'));

                radioButtonItemList.forEach(i => {
                    let value = $(i).attr("value");
                    let currentValue = $(e.target).attr("value");

                    if (value === currentValue) {
                        $(i).addClass('active');
                    }
                });
            });
        })
    };

    return {
        init: () => {
            initRadioButtons();
        }
    };
})();

jQuery(document).ready(() => {
    initComponents.init();
});