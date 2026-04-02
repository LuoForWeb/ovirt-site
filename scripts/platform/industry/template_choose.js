var TemplateChoose = function () {
    var uuid;
    var isInits = false; // 标记本次是否已经初始化过
    return {
        //main function to initiate the module
        init: function (options) {
            // 根据传递的模板uuid初始化
            if (options.uuid == undefined) {
                return;
            }
            uuid = options.uuid;


            var viewportHeight = $(window).height();
            var height = viewportHeight - 176;
            height = height + 75;
            // height = Math.max(930, height);

            $('#drawer-indust_template_config #parent_container').css({
                'height': height
            });

            // 隐藏某些元素
            $('#drawer-indust_template_config #industry_template').css({
                'overflow': 'hidden',
                'height': viewportHeight,
                'margin-left': 0,
                'margin-right': 0,
            });
            $('#drawer-indust_template_config #industry_template>.col-md-12>.col-md-2').hide();
            $('#drawer-indust_template_config #industry_template .item-right-div').css({
                'width': '100%',
                'left': 0,
            });
            $('#drawer-indust_template_config #addItems').hide();
            $('#drawer-indust_template_config #chooseItems').show();
            $('#drawer-indust_template_config #cancelBut').attr('data-dismiss', 'drawer');
            if (options.init == undefined) {
                // 显示抽屉
                $('#drawer-indust_template_config').drawer('show');
            }

            if (options.again_init != undefined) {
                isInits = !options.again_init;
            }

            if (isInits == false) {
                // 初始化模板详情
                TemplateConfig.init(options);
            }

            isInits = true;
        },
        getInfo: function (){
            return TemplateConfig.getInfo();
        }
    };
}();

