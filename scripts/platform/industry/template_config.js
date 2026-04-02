var TemplateConfig = function () {
    var uuid = '',viewportHeight;
    var submitData = {}; // 提交的数据汇总
    var source = 1;// 来源。默认1是模板编辑 2是任务选择模板配置
    var oldData = {}; // 修改获取到的数据
    var isLoading = false; // 标识是否还在加载数据
    var itemPlanEditor,itemResultEditor,reportWater,screenWater,emailNotice;
    var isEnCn = 1; // 是否需要中英对照，默认开启
    var gridSize = 70; // 网格大小
    var firstItemTop = 220; // 第一个元素距离顶部的距离
    var editorHeight = 300;// 编辑器的高度
    var isInit = false; // 标记是否是创建任务初始化
    var jobUuid = '';// 编辑任务的时候携带的任务uuid
    // 记录下所有的单选组件的默认html内容
    var itemFieldArr = {};
    // 不应用拖拽的组件名称
    var notDaragle = ['item_logo', 'item_qrcode', 'item_verify_time'];
    var approvalList = []; // 审批流列表
    var planList = []; // 方案列表
    // 单选的那些的状态
    var itemField = {
        item_approval: true,// 审批流是否选中
        item_logo: true,// logo
        item_qrcode: true,// 二维码
        item_sys_code: true,
        item_sys_name: true,
        item_host: true,
        item_ip: true,
        item_plan: true,
        item_result: true,
        item_results: true,
        item_operator: true,
        item_verify_time: false,
        item_download_time: true,
        item_make_time: true,
        item_operator_sign: true,
        item_auditor_sign: true,
        item_operator_sign_sys: false,
        item_auditor_sign_sys: false,
        item_network: true,
        item_ping: true,
        item_screen_verify: true,
        item_open: true,
        item_virtus: true,
        item_integrality: true,
        item_files: true,
        item_document: true,
        item_screen: true,
        item_module: true,
        item_point: true,
    };
    // 可以重复选择的组件标识
    var itemArr = [
        'item_field',
        'item_textarea',
        'item_datetime',
    ]; // 单选的组合
    var draggingShadow = null; // 左边的按钮拖拽
    var isExpend = true; // 左边的按钮是否展开
    var sameArea = []; // 记录有折叠的元素集合
    var itemPositionArr = [];   // 记录每个元素的位置
    function isOverlapping(elem, classfy = '.draggable') {
        return false; // 不会重叠了，因为做了判断
        var isOverlapping = false; // 外部变量来跟踪重叠
        $(classfy).not(elem).each(function() {
            var otherRect = this.getBoundingClientRect();
            var elemRect = elem[0].getBoundingClientRect(); // 移动到这里以避免在每次迭代中都重新计算
            if (!(elemRect.right < otherRect.left ||
                elemRect.left > otherRect.right ||
                elemRect.bottom < otherRect.top ||
                elemRect.top > otherRect.bottom)) {
                // 这里处理下，
                // 如果本身的宽度
                isOverlapping = true; // 设置重叠标志
                return false; // 退出.each()循环（可选，但可以提高性能）
            }
        });
        return isOverlapping; // 返回最终的重叠状态
    }

    // 获取默认的一些内容
    function getDefaultItem(){
        itemFieldArr = {
            'item_logo': makeItemLogo(),
            'item_qrcode': makeItemQrcode(),
            'item_verify_time': makeReportTime(),
            'item_point': makeVerfiyBlock('item_point', 'item_point', LANG.UI_PLATFORM_INDUSTRY_POINT_NAMES, 'Backup Time Point', LANG.UI_PLATFORM_INDUSTRY_YMD),
            'item_integrality': makeVerfiy2Block('item_integrality', 'item_integrality', LANG.UI_PLATFORM_INDUSTRY_OPEN_VERIFY, 'Boot Verification Result', LANG.UI_PLATFORM_INDUSTRY_RESULTS),
            'item_screen_verify': makeVerfiy2Block('item_screen_verify', 'item_screen_verify', LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULTS, 'Screenshot Comparison Results', LANG.UI_PLATFORM_INDUSTRY_RESULTS),
            'item_files': makeVerfiy2Block('item_files', 'item_files', LANG.UI_PLATFORM_INDUSTRY_FILES_RESULTS, 'File Comparison Result', LANG.UI_PLATFORM_INDUSTRY_RESULTS),
            'item_module': makeVerfiyBlock('item_module', 'item_module', LANG.UI_PLATFORM_INDUSTRY_HOST_NAMES, 'Host Name', 'host name'),
            'item_results': makeDiyVal(2, 'item_results', 'item_results', LANG.UI_PLATFORM_INDUSTRY_VERIFY_RESULTS, 'Final Result', LANG.UI_PLATFORM_INDUSTRY_RESULTS),
            'item_open': makeTitlePic(2, 'item_open', 'item-open', 'item_open', LANG.UI_PLATFORM_INDUSTRY_OPEN_RESULTS,'BOOT VERIFICATION', './img/platform/industry/open.png'),
            'item_screen': makeTitlePic(2, 'item_screen', 'item-screen','item_screen', LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_COMPARSION, ' SCREENSHOT COMPARSION','./img/platform/industry/screen.png'),
            'item_approval': makeTitlePic(1, 'item_approval','item-approval','item_approval', LANG.UI_PLATFORM_INDUSTRY_APPROVAL, 'APPROVAL PROCESS', './img/platform/industry/approval.png'),
            'item_make_time': makeBottomBlock('item_make_time', 'item-make_time','item_make_time', LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE_TIME, 'Report Generation Time', LANG.UI_PLATFORM_INDUSTRY_YMD),
            'item_download_time': makeBottomBlock('item_download_time','item-download_time', 'item_download_time', LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD_TIME, 'Report Download Time', LANG.UI_PLATFORM_INDUSTRY_YMD),
            'item_operator_sign': makeBottomBlock('item_operator_sign', 'item-operator_sign','item_operator_sign', LANG.UI_PLATFORM_INDUSTRY_OPERATOR_SIGN, 'Operator(Signature)', ''),
            'item_auditor_sign': makeBottomBlock('item_auditor_sign', 'item-auditor_sign','item_auditor_sign', LANG.UI_PLATFORM_INDUSTRY_AUDITOR_SIGN,'Auditor(Signature)', ''),
            'item_operator_sign_sys': makeBottomBlock('item_operator_sign_sys', 'item-operator_sign_sys','item_operator_sign_sys', LANG.UI_PLATFORM_INDUSTRY_OPERATOR_SYS_SIGN, 'Operator(Sys_signature)', ''),
            'item_auditor_sign_sys': makeBottomBlock('item_auditor_sign_sys', 'item-auditor_sign_sys','item_auditor_sign_sys', LANG.UI_PLATFORM_INDUSTRY_AUDITOR_SYS_SIGN,'Auditor(Sys_signature)', ''),
            'item_host': makeDiyVal(1, 'item_host', 'item_host', LANG.UI_PLATFORM_INDUSTRY_HOST_NAME, 'Host Name', '', '系统自动填充'),
            'item_sys_code': makeDiyVal(1, 'item_sys_code', 'item_sys_code', '系统编号', 'System Code', '', '生成报告时才能填写'),
            'item_sys_name': makeDiyVal(1, 'item_sys_name', 'item_sys_name', '系统名称', 'System Name', '', '生成报告时才能填写'),
            'item_ip': makeDiyVal(1, 'item_ip', 'item_ip', LANG.UI_PUBLIC_IP_ADDRESS, 'IP Address', '', '系统自动填充'),
            'item_operator': makeDiyVal(1, 'item_operator','item_operator', LANG.UI_PLATFORM_INDUSTRY_OPERATOR, 'Operator', '', '系统自动填充'),
            'item_document': meakeDocumentBlock(), // 文件比对
        };
    }

    // 初始化存储默认的模板的位置
    function getPosition(elem, classfy, show = 0) {
        var pos = elem.position();
        var id = elem.attr("id");
        var name = elem.attr("data-field");
        var value = {};

        if ($.inArray(name, itemArr) !== -1) {
            // 自定义字段，文本，时间控件 获取名称和内容
            value.name = elem.find('input[name="item_field_name"]').val();
            if (isEnCn == 1) {
                value.en_name = elem.find('input[name="item_field_name_en"]').val();
            }
            value.val = elem.find('input[name="item_field_value"]').val();

            if (name == 'item_textarea') {
                value.val = elem.find('textarea[name="item_field_value"]').val();
            }
        }
        // 如果是主机名、ip和操作人员有自定义内容
        if ($.inArray(name, ['item_ip', 'item_host', 'item_operator']) !== -1) {
            value.val = elem.find('input[name="item_field_value"]').val();
        }
        // 记录元素位置
        // 移除记录的位置
        for(var i in itemPositionArr) {
            if (itemPositionArr[i].id == id) {
                itemPositionArr.splice(i, 1);
            }
        }
        var type = classfy == ".draggable2" ? 2 : 1;
        if ($.inArray(name, ['item_logo', 'item_qrcode']) !== -1) {
            type = 1;
        } else if ($.inArray(name, ['item_module', 'item_point']) !== -1) {
            type = 2;
        }
        itemPositionArr.push({'id': id, 'position': pos.top + "," + pos.left, 'name': name, value:value, 'type': type});

        if (isOverlapping(elem, classfy)) {
            sameArea.push(id);
           // console.log("Element ID: " + id + ", Position: " + pos.top + ", " + pos.left, sameArea, show);
            if (show == 0) {
                showSameTip();
            }
            return;
        }
        // 其他停止拖拽时想要执行的代码...
        sameArea = sameArea.filter(item => item !== id); // 创建一个新数组，不包含值为id的元素
    }

    function makeDraggable(selector, container = '.container-industry-template', classfy = '.draggable') {
        // 初始化位置
        $(classfy).each(function() {
            getPosition($(this), classfy, 1);
        });

        $(classfy).find('.del').click(function() {
            delItem($(this));
        });
        // 二维码和logo不应用拖拽
        if ($.inArray($(selector).attr('data-field'), notDaragle) === -1) {
            $(selector).draggable({
                containment: container, // 限制拖拽在#container内
                drag: function(event, ui) {
                    // 计算当前元素的位置，
                    // 这里的代码会在拖拽过程中每次移动时执行
                    // ui.position 包含当前元素的左和上位置
                   // console.log("拖拽中，当前位置：", ui.position);

                    // 你可以在这里调用其他函数或执行其他操作
                    // 例如，更新某个元素的位置显示
                    // updatePositionDisplay(ui.position);
                    if (classfy == '.draggable') {
                        var parent = $(this).parent().height();
                        var selfh = $(this).height();
                        if (ui.position.top + selfh + 10 > parent) {
                            $(this).parent().css({
                                'height': parent + 50 + 'px',
                            });
                            // 强制父元素重新计算滚动条
                        }
                    }
                },
                stop: function(event, ui) {
                    repositionItems();
                    repositionItems2();
                    getPosition($(this), classfy);
                }
            });
        }
    }

    function repositionItems(itemss = '.item-1', containers = '.container-industry-template') {
        var verticalSpacing = 32; // 垂直间距
        var items = $(itemss);
        var container = $(containers);
        var rowHeight = gridSize + verticalSpacing;
        var currentRowY = firstItemTop; // 第一个元素的初始位置
        var itemsInCurrentRow = 0;
        var maxTop = 0; // 用于记录当前所有元素的最大顶部位置
        var changeTop = 0; // 用于记录独占一行并且高度变化的
        var basicLine = false;

        items.sort(function(a, b) {
            return $(a).position().top - $(b).position().top || $(a).position().left - $(b).position().left;
        }).each(function() {
            var item = $(this);
            // 检查 item 是否存在
            if (!item || !item.length) {
                console.error('Button is not defined or not found.');
                return;
            }

            if (itemsInCurrentRow >= 2 || item.outerWidth() * 2 > container.width() || changeTop > 0) {
                // 如果当前行已经有两个元素，则移动到下一行 或者当前元素的宽度比容器的一半要大，也换行b
                if (itemsInCurrentRow > 0) {
                    currentRowY += rowHeight;
                }
                itemsInCurrentRow = 0;
            }

            currentRowY += changeTop;

            var leftPosition = itemsInCurrentRow === 0 ? 0 : '50%';
            if ($.inArray(item.attr('data-field'),['item_make_time', 'item_download_time', 'item_operator_sign', 'item_auditor_sign', 'item_auditor_sign_sys', 'item_operator_sign_sys']) !== -1) {
                // 下面的签字那些又不一样
                leftPosition = itemsInCurrentRow === 0 ? 0 : '50%';
            }

            // 处理下基本信息最下面的line的高度
            if (!basicLine &&
                $.inArray(item.attr('data-field'),
                ['item_plan', 'item_result', 'container2', 'item_approval', 'item_make_time', 'item_download_time', 'item_operator_sign', 'item_auditor_sign', 'item_operator_sign_sys', 'item_auditor_sign_sys']
            ) !== -1
            ) {
                basicLine = true;
                currentRowY += 60;
            }

            item.css({
                left: leftPosition,
                top: currentRowY
            });

            if (item.attr('data-field') == 'item_textarea') {
                changeTop = item.outerHeight() - 85;
            } else if (item.attr('data-field') == 'item_basic_line') {
                changeTop = item.outerHeight() - 45;
            } else if ($.inArray(item.attr('data-field'), ['item_plan', 'item_approval', 'item_description', 'item_result', 'item_textarea']) !== -1) {
                changeTop = item.outerHeight() - gridSize;
            } else {
                changeTop = 0;
            }
            itemsInCurrentRow++;
            maxTop = Math.max(maxTop, currentRowY + item.outerHeight());
        });
        // 这里处理下面的元素的位置 ，比如如果高度大于100 那么下面的元素的高度就相应的变化
        container.css({
            'height': maxTop + 100 + 'px'
        })
      //  console.log('maxTop', maxTop)
        bindButtionMove();
        return true;
    }

    function repositionItems2(itemss = '.item-2', containers = '.container2-industry-template') {
        var verticalSpacing = 20; // 垂直间距
        var firstItemTop = 52; // 第一个元素距离顶部的距离
        var items = $(itemss);
        var container = $(containers);
        var rowHeight = gridSize + verticalSpacing;
        var currentRowY = firstItemTop; // 第一个元素的初始位置
        var itemsInCurrentRow = 0;
        var changeTop = 0; // 用于记录独占一行并且高度变化的
        var maxTop = 0; // 用于记录当前所有元素的最大顶部位置
        var three = 0; // 记录三块的当前的次数
        var threeTop = 0;// 记录第一次出现的三块的高度

        items.sort(function(a, b) {
            return $(a).position().top - $(b).position().top || $(a).position().left - $(b).position().left;
        }).each(function() {
            var item = $(this);
            // 检查 item 是否存在
            if (!item || !item.length) {
                console.error('Button is not defined or not found.');
                return;
            }
            if ($.inArray(item.attr('data-field'), ['item_integrality', 'item_screen_verify', 'item_files']) !== -1) {
                // 三块一行
                if (three == 0) {
                    currentRowY += rowHeight;
                    itemsInCurrentRow = 0;
                    currentRowY += changeTop + 20;
                    threeTop = currentRowY;
                    leftPosition = 0;
                } else if (three == 1){
                    leftPosition = '33%';
                } else {
                    leftPosition = '67%';
                }

                item.css({
                    left: leftPosition,
                    top: threeTop,
                    width: three == 1 ? '34%' : '33%',
                });
                three ++;
            } else {
                // 两块或一块的
                if (itemsInCurrentRow >= 2 || item.outerWidth() * 2 - 10 > container.outerWidth() || changeTop > 0) {
                    // 如果当前行已经有两个元素，则移动到下一行 或者当前元素的宽度比容器的一半要大，也换行
                    currentRowY += rowHeight;
                    itemsInCurrentRow = 0;
                }

                currentRowY += changeTop;

                var leftPosition = itemsInCurrentRow === 0 ? 0 : '50%';
                item.css({
                    left: leftPosition,
                    top: currentRowY
                });
            }

            if ($.inArray(item.attr('data-field'), ['item_virtus', 'item_open', 'item_document', 'item_screen']) !== -1) {
                changeTop = item.outerHeight() - gridSize;
            }else {
                changeTop = 0;
            }
            itemsInCurrentRow++;
            maxTop = Math.max(maxTop, currentRowY + item.outerHeight());
        });
        // 这里处理下面的元素的位置 ，比如如果高度大于10 那么下面的元素的高度就相应的变化
        container.css({
            'height': maxTop + 50 + 'px'
        })
       // console.log('maxTop2', maxTop)
        bindButtionMove('.addItem2', '.container2-industry-template');
        return true;
    }

    // 组件按钮删除事件初始化
    function initDelItem(){
        $('.item-gaiyao').find('.close-icon').on('click', function (event){
            var elem = $(this).parent().attr('data-id');
            delItem($('#'+elem).find('.del'));
            event.stopPropagation();// 阻止冒泡
        })
    }

    // 封装删除组件事件
    function delItem(elem){
        var id = elem.parent().attr('id');
        var field = elem.parent().attr('data-field');
        var parent = 0;
        if (id == undefined) {
            id = elem.parent().parent().attr('id');
            field = elem.parent().parent().attr('data-field');
            parent = 1;
        }

        if (!($.inArray(field, itemArr) !== -1)) {
            itemField[field] = false;// 更新状态
            controllItem($('#container-item').find('.'+field), 1);
        }
        sameArea = sameArea.filter(item => item !== id); // 创建一个新数组，不包含值为id的元素
        // 移除记录的位置
        for (var i = itemPositionArr.length - 1; i >= 0; i--) {
            if (itemPositionArr[i].id === id) {
                itemPositionArr.splice(i, 1); // 安全地删除元素
            }
        }
        // 销毁编辑器
        if (field == 'item_plan') {
            if (itemPlanEditor) {
                itemPlanEditor.destroy();
                itemPlanEditor = null;
            }
        }
        if (field == 'item_result') {
            if (itemResultEditor) {
                itemResultEditor.destroy();
                itemResultEditor = null;
            }
        }
        if (parent) {
            elem.parent().parent().remove();
        } else {
            elem.parent().remove();
        }

        repositionItems();
        repositionItems2();
        resizeTemp();
    }

    var initDatetimePicker = function (timePicker = '.form_datetime', callback = null) {
        //初始化日期时间选择控件
        $(timePicker).daterangepicker({
            "autoUpdateInput": false, //是否自动填充input
            "startDate": moment().subtract('days').startOf('day'), //默认开始时间
            "endDate": moment({ year: 2099, month: 11, day: 31, hour: 23, minute: 59 }),
            "maxDate":  moment({ year: 2099, month: 11, day: 31, hour: 23, minute: 59 }), //最大可用时间
            singleDatePicker: true,
            showDropdowns: false,
            'opens':'right',
            "timePicker": true, //是否显示时间,时分
            "timePicker24Hour": true, //是否是24小时制
            timePickerSeconds: true,
            "alwaysShowCalendars": true, //是否总是显示日期选择
            "drops": 'auto',  // 自动设置显示位置
            // "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE), //根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE), //根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function (start, end, label) {
        });

        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        $(timePicker).on('apply.daterangepicker', function (ev, picker) {
            //给全局变量赋值,然后设置input
            $(this).find('input').val('' + picker.startDate.format('YYYY-MM-DD HH:mm:ss') + '');
            if (!callback) {
                return;
            }
            callback();
        });

        $(timePicker).on('cancel.daterangepicker', function (ev, picker) {
            //清除全局变量,然后设置input
            $(this).find('input').val('');
        });

        //日期手动改变
        $(timePicker).find('input').on('change', function(){
            var val = $(this).val();
            // 日期时间正则表达式（YYYY-MM-DD HH:mm:ss）
            var dateTimeRegex = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;

            if (!dateTimeRegex.test(val)){
                val = getNowDate() + ' 00:00:00';
            }
            $(this).val(val);
            if (!callback) {
                return;
            }
            callback();
        });
    }
    // 获取今天的日期
    var getNowDate = function (){
        var d = new Date();  //日期方法
        var yearDate = d.getFullYear();  //今年
        var monthDate = yearDate + '-' + Appendzero(d.getMonth() + 1);  //本月
        //当天日期，相当于c# 里的 DateTime.Now
        var nowDate = monthDate + '-' + Appendzero(d.getDate());  //当天
        return nowDate;
    }
    //小于10补一个0，解决一个页面有开始时间和结束时间进行时间比较时，因为缺少0导致比较结果有误
    function Appendzero(obj) { if (obj < 10) return "0" + obj; else return obj; }

    // 页面改变高度
    function resizeTemp() {
        if (source == 1) {
            var width = $('#industry_template').width();
            if (width < 770 * 2 && isExpend) {
                $('#industry_template .col-md-12 .col-md-10').css({
                    'left': '336px'
                })
            } else {
                $('#industry_template .col-md-12 .col-md-10').css({
                    'left': 'calc(50% - 433px)'
                })
            }
            if (width < 1100) {
                $('#industry_template').css({
                    'overflow': 'scroll hidden',
                })
            } else {
                $('#industry_template').css({
                    'overflow': 'hidden hidden',
                })
            }
            viewportHeight = $(window).height();
            var height = viewportHeight - 176;
            // height = Math.max(930, height);
            $('#parent_container').css({
                'height': height
            });
            $('#container-item').parent().css({
                'height':viewportHeight - 125
            });
            $('#step2').css({
                'height': height - 10
            });
            $('#step3').css({
                'height': height - 10
            });
           // console.log("新的视口高度: " + height + "px");
        }

        if (itemPlanEditor) {
            $('#item_plan_editor>div').css({
                'width': '100%'
            })
            $('#item_plan_editor>div>div').css({
                'width': '100%'
            })
        }

        if (itemResultEditor) {
            $('#item_result_editor>div').css({
                'width': '100%'
            })
            $('#item_result_editor>div>div').css({
                'width': '100%'
            })
        }
        limitLength();

        if (isEnCn !== 1) {
            // 不是中英文对照
            $('.item_en').remove();
        }

        // 这里来计算下基本信息的高度
        var arr = [
            $('#item_plan'),
            $('#item_result'),
            $('.container2-industry-template'),
            $('#item_approval'),
            $('#item_make_time'),
            $('#item_download_time'),
            $('#item_operator_sign'),
            $('#item_auditor_sign'),
            $('#item_operator_sign_sys'),
            $('#item_auditor_sign_sys'),
        ];
        var baseHeightArr = [];
        for (var j in arr) {
            if (arr[j].length > 0) {
                baseHeightArr.push(arr[j].position().top);
            }
        }
        var baseHeight = Math.min(...baseHeightArr) - 60;

        $('#basic_info_line').css({
            'top': baseHeight + 'px',
        })
       // console.log('基本信息的最大高度：' + baseHeight + 'px');
    }

    // 生成对应的组件元素
    // 生成logo
    function makeItemLogo(url = ''){
        var upload = '<span class="upload-flag"><i class="viconfont vicon-shangchuan"></i>'+LANG.UI_PLATFORM_INDUSTRY_UPLOAD_LOGO+'</span>';
        if (url != '') {
            upload = '<img class="upload-img upload-flag" src="'+url+'">';
        }
        return `<div class="draggable-s item-logo item-items" id="item_logo" data-field="item_logo">
                    <span class="margin-r--15">
                        <label class="upload-div" title="`+LANG.UI_PLATFORM_INDUSTRY_UPLOAD_LOGO_TIPS+`：120x50 px">
                           `+upload+`
                            <input type="file" accept="image/*" name="files" id="diy_logo_value" style="display:none;">
                            <input type="hidden" id="diy_logo_value_url" value="`+url+`">
                        </label>
                    </span>
                    <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                </div>`;
    }

    // 生成二维码
    function makeItemQrcode() {
        return `<div class="draggable-s item-qrcode item-items" title="`+LANG.UI_PLATFORM_INDUSTRY_QRCODE_TIPS+`" id="item_qrcode" data-field="item_qrcode">
                    <span class="margin-r--15"><img src="./img/platform/industry/qrcode.png"></span>
                    <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                </div>`;
    }

    // 生成自定义字段
    function makeDiyField(type = 1, id = 'item_field', name = LANG.UI_PLATFORM_INDUSTRY_DIY_FIELDS, en_name = 'Custom Fields',value = '', palceholder = LANG.UI_PLATFORM_INDUSTRY_INPUT) {
        // type 1字段 2时间 3文本框
        var classs = '',item1 = 'item-fields', item2 = 'item_field';
        var vals = '<input type="text" name="item_field_value" value="'+value+'" placeholder="'+palceholder+'" class="form-control input-inline input input-sm input-val">';
        if (type == 2) {
            classs = 'date form_datetime'
            item2 = 'item_datetime';
        } else if (type == 3) {
            item1 = 'item-textarea';
            item2 = 'item_textarea';
            vals = '<textarea name="item_field_value" class="form-control" placeholder="'+palceholder+'">'+value+'</textarea>';
        }
        return `<div class="draggable item-1 item-items  `+item1+`" id="`+id+`" data-field="`+item2+`">
                <i class="viconfont vicon-tuozhuai show-hover-icon display-none"></i>
                <label class="item-label-title">
                    <span class="span-input-title">
                        <input type="text" class="form-control input-inline input-ch" name="item_field_name" value="`+name+`">
                        <input type="text" class="form-control input-inline item_en input-en" name="item_field_name_en" value="`+en_name+`">
                    </span>
                    <br>
                    <span class="`+classs+`">
                       `+vals+`
                    </span>
                </label>
                <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
            </div>`;
    }

    // 生成不可更改名称的字段
    function makeDiyVal(type = 1, id = 'item_host', item1 = 'item_host', name = LANG.UI_PLATFORM_INDUSTRY_HOST_NAME, en_name = 'Host name', value = '', palceholder = LANG.UI_PLATFORM_INDUSTRY_EMPTY_TIPS){
        var readonly = '';
        if ($.inArray(item1, ['item_sys_code', 'item_sys_name', 'item_operator', 'item_ip', 'item_host']) !== -1) {
            readonly = 'readonly'
        }
        var val = '<input type="text" name="item_field_value" ' + readonly + ' placeholder="'+palceholder+'" value="'+value+'" class="form-control input-inline input input-sm input-val">';
        if (type == 2) {
            // 不可编辑内容
            val = '<span class="span-val">'+value+'</span>';
        }
        var title = '';
        if (id == 'item_results') {
            title = 'title="'+LANG.UI_PLATFORM_INDUSTRY_AUTO_MAKE_AFTER_REPORT+'"';
        }
        return `<div class="draggable item-1 item-items item-fields" `+title+` id="`+id+`" data-field="`+item1+`">
                    <i class="viconfont vicon-tuozhuai show-hover-icon display-none"></i>
                    <label class="item-label-title">
                        <span class="span-title">
                            <span class="span-ch">`+name+`</span><span class="span-en item_en">`+en_name+`</span>
                         </span>
                        <input type="hidden" class="form-control input-inline input-ch" name="item_field_name" value="`+name+`">
                        <input type="hidden" class="form-control input-inline item_en input-en" name="item_field_name_en" value="`+en_name+`">
                        <br>
                       `+val+`
                    </label>
                    <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                </div>`;
    }

    // 生成时间
    function makeReportTime(id='item_verify_time', item1 = 'item_verify_time', name=LANG.UI_PLATFORM_INDUSTRY_MAKE_TIME, en_name='Generation time',value=LANG.UI_PLATFORM_INDUSTRY_YMD){
        return `<div class="draggable-s item-items" id="`+id+`" data-field="`+item1+`" style="line-height: 30px;width: 360px;">
                    <span class="">
                        <i class="viconfont vicon-shengchengshijian" style="display:table-caption;line-height: 10px;margin-right: 4px;"></i>
                        <span class="make-time-ch">`+name+`</span>
                        <span class="make-time-en item_en">`+en_name+`</span>
                        <span class="item-line"></span>
                        <span class="make-time-val">`+value+`</span>
                    </span>
                    <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                </div>`;
    }

    // 编辑器生成
    function makeEditor(id= 'item_plan', item1 = 'item-plan', item2 = 'item_plan',name = LANG.UI_PLATFORM_INDUSTRY_VERIFY_PLAN, en_name='VERFICATION PLAN', value=LANG.UI_PLATFORM_INDUSTRY_PLAN_DES){
        var select = ``;
        if (id == 'item_plan') {
            select = `<select class="form-control" name="plan_uuid" id="plan_uuid">
                            </select>`;
        }
        return `<div class="draggable item-1 `+item1+` item-block-width1 item-items" id="`+id+`" data-field="`+item2+`">
                        <i class="viconfont vicon-tuozhuai display-none show-hover-icon"></i>
                        <div class="basic-info basic-title-plan">
                            `+name+`
                            <span class="item-line item_en"></span>
                            <span class="item_en span-title-basic">`+en_name+`</span>
                            <button class="btn b-btn item-plan-right" title="`+LANG.UI_PLATFORM_INDUSTRY_EDITOR_TOOLS+`" type="button">
                                <i class="viconfont vicon-gongjuyincang"></i>
                            </button>
                            `+select+`
                        </div>
                        <textarea id="`+id+`_editor">`+value+`</textarea>
                        <div class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></div>
                    </div>`;
    }

    // 标题加图片生成
    function makeTitlePic(type = 1,id='item_approval', item1 = 'item-approval', item2 = 'item_approval', name=LANG.UI_PLATFORM_INDUSTRY_APPROVAL,en_name='APPROVAL PROCESS',value='./img/platform/industry/approval.png'){
        // type 2表示中间验证的元素的生成的
        var drag = 'draggable';
        if (type == 2) {
            drag = 'draggable2';
        }
        var select = '';
        if (id == 'item_approval') {
            select = `<select class="form-control" name="approval_uuid" id="approval_uuid">
                                <option value="0">`+LANG.UI_PLATFORM_INDUSTRY_APPROVAL_SELECT+`</option>
                            </select>`;
        }
        return `<div class="`+drag+` item-`+type+` `+item1+` item-block-width`+type+` item-items" id="`+id+`" data-field="`+item2+`">
                    <i class="viconfont vicon-tuozhuai display-none show-hover-icon"></i>
                    <div class="basic-info basic-title-plan">
                        `+name+`
                        <span class="item-line item_en"></span>
                        <span class="item_en span-title-basic">`+en_name+`</span>
                        `+select+`
                    </div>
                    <div class="img-items">
                        <img src="`+value+`">
                    </div>
                    <div class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></div>
                </div>`;
    }

    // 验证信息2块的生成
    function makeVerfiyBlock(id='item_module', item2='item_module', name=LANG.UI_PLATFORM_INDUSTRY_HOST_NAMES,en_name='Host name',value='host name'){
        return `<div class="draggable2 item-2 item-items item-verify-items" id="`+id+`" data-field="`+item2+`">
                    <i class="viconfont vicon-tuozhuai display-none show-hover-icon" style="left:0;top:16px;"></i>
                    <span class="item-verify-items-block">
                        <span class="item-verify-ch">`+name+`</span><span class="item_en item-verify-en">`+en_name+`</span>
                        <div class="item-verify-val">`+value+`</div>
                    </span>
                    <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                </div>`;
    }
    // 验证信息下面的三块的生成
    function makeVerfiy2Block(id='item_integrality', item2='item_integrality', name=LANG.UI_PLATFORM_INDUSTRY_OPEN_VERIFY,en_name='Boot Verification Result',value=LANG.UI_PLATFORM_INDUSTRY_RESULTS) {
        return `<div class="draggable2 item-2 item-items item-verify2-items" id="`+id+`" data-field="`+item2+`">
                    <i class="viconfont vicon-tuozhuai display-none show-hover-icon" style="left:0;top:20px"></i>
                    <span class="item-verify2-items-block">
                        <span>`+name+`</span>
                        <div class="item_en item-verify2-en">`+en_name+`</div>
                        <div class="item-verify-val">`+value+`</div>
                    </span>
                    <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                </div>`;
    }

    // 文件比对生成
    function meakeDocumentBlock(){
        return `<div class="draggable2 item-2 item-document item-block-width2 item-items" id="item_document" data-field="item_document">
                    <i class="viconfont vicon-tuozhuai display-none show-hover-icon" style="left:0;"></i>
                    <div class="columns columns-right btn-group file-lie">
                        <div class="keep-open btn-group open" title="列">
                            <button class="btn b-btn btn-title btn b-btn btn-title-default dropdown-toggle" type="button" data-toggle="dropdown" title="`+LANG.UI_TOOLS_TABLE_COLUMN+`" aria-expanded="true">
                                <i class="glyphicon icon-columns"></i>
                            </button>
                            <ul class="dropdown-menu file-lie-ul" role="menu">
                                <li class="dropdown-item-marker" role="menuitem">
                                    <label>
                                        <input type="checkbox" id="file_size" checked data-id="file_size" data-checkbox="icheckbox_square-blue" class="icheck">
                                        <span class="file-item-title">`+LANG.UI_PLATFORM_INDUSTRY_FILES_SIZE+`</span>
                                    </label>
                                </li>
                                <li class="dropdown-item-marker" role="menuitem">
                                    <label>
                                        <input type="checkbox" id="file_position" checked data-id="file_attr" data-checkbox="icheckbox_square-blue" class="icheck">
                                        <span class="file-item-title">`+LANG.UI_PLATFORM_INDUSTRY_FILES_ATTR+`</span>
                                    </label>
                                </li>
                                <li class="dropdown-item-marker" role="menuitem">
                                    <label>
                                        <input type="checkbox" id="file_create" checked data-id="file_create" data-checkbox="icheckbox_square-blue" class="icheck">
                                        <span class="file-item-title">`+LANG.UI_PUBLIC_CREATE_TIME+`</span>
                                    </label>
                                </li>
                                <li class="dropdown-item-marker" role="menuitem">
                                    <label>
                                        <input type="checkbox" id="file_update" checked data-id="file_modify" data-checkbox="icheckbox_square-blue" class="icheck">
                                        <span class="file-item-title">`+LANG.UI_SETTING_MODIFY_TIME+`</span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="basic-info basic-title-plan">
                        `+LANG.UI_PLATFORM_INDUSTRY_FILE_COMPARSION+`
                        <span class="item-line item_en"></span>
                        <span class="item_en span-title-basic">FILE COMPARSION</span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th class="text-left">
                                    <span class="tr-ch">`+LANG.UI_PLATFORM_INDUSTRY_PRODUCT_AND_VERIFY_FILES+`</span>
                                    <span class="item_en tr-en">
                                        </br>Source files & backup files
                                    </span>
                                </th>
                                <th width="248" class="text-left verify_method">
                                    <span class="tr-ch">MD5 `+LANG.UI_PLATFORM_INDUSTRY_VERIFY_VALUE+`</span>
                                    <span class="item_en tr-en">
                                        </br>MD5 Verification value
                                    </span>
                                </th>
                                <th style="width:8%;" class="text-left file_size">
                                    <span class="tr-ch">`+LANG.UI_PLATFORM_INDUSTRY_FILES_SIZE+`</span>
                                    <span class="item_en tr-en">
                                        </br>Files Size
                                    </span>
                                </th>
                                <th width="60" class="text-left file_attr">
                                    <span class="tr-ch">`+LANG.UI_PLATFORM_INDUSTRY_FILES_ATTR+`</span>
                                    <span class="item_en tr-en">
                                        </br>Permission
                                    </span>
                                </th>
                                <th width="102" class="text-left file_create">
                                    <span class="tr-ch">`+LANG.UI_PUBLIC_CREATE_TIME+`</span>
                                    <span class="item_en tr-en">
                                        </br>Create Time
                                    </span>
                                </th>
                                <th width="102" class="text-left file_modify">
                                    <span class="tr-ch">`+LANG.UI_SETTING_MODIFY_TIME+`</span>
                                    <span class="item_en tr-en">
                                        </br>Modify Time
                                    </span>
                                </th>
                                <th width="100" class="text-left">
                                    <span class="tr-ch">`+LANG.UI_PLATFORM_INDUSTRY_RESULTS+`</span>
                                    <span class="item_en tr-en">
                                        </br>Result
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="file_result_ok">
                                <td class="text-left">
                                    <span>`+LANG.UI_PLATFORM_INDUSTRY_FILES_PATH_PRODUCTS+`</span>
                                    <br>
                                    <span>`+LANG.UI_PLATFORM_INDUSTRY_FILES_PATH_VERIFYS+`</span>
                                </td>
                                <td class="text-left">
                                    <span>`+LANG.UI_PLATFORM_INDUSTRY_FILES_MD5_PRODUCTS+`</span>
                                    <br>
                                    <span>`+LANG.UI_PLATFORM_INDUSTRY_FILES_MD5_VERIFYS+`</span>
                                </td>
                                <td class="text-left file_size">
                                    <span>10MB</span>
                                    <br>
                                    <span class="">10MB</span>
                                </td>
                                <td class="text-left file_attr">
                                    <span>--<span class="item_en">(-)</span></span>
                                    <br>
                                    <span>--<span class="item_en">(-)</span></span>
                                </td>
                                <td class="text-left file_create">
                                    <span>xxxx/xx/xx xx:xx:xx</span>
                                    <br>
                                    <span>xxxx/xx/xx xx:xx:xx</span>
                                </td>
                                <td class="text-left file_modify">
                                    <span>xxxx/xx/xx xx:xx:xx</span>
                                    <br>
                                    <span>xxxx/xx/xx xx:xx:xx</span>
                                </td>
                                <td class="text-left">
                                    <i class="viconfont vicon-tongguo"></i>
                                    `+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME+`
                                </td>
                            </tr>
                            <tr class="file_result_fail">
                               <td class="text-left">
                                    <span>`+LANG.UI_PLATFORM_INDUSTRY_FILES_PATH_PRODUCTS+`</span>
                                    <br>
                                    <span>`+LANG.UI_PLATFORM_INDUSTRY_FILES_PATH_VERIFYS+`</span>
                                </td>
                                <td class="text-left">
                                    <span>`+LANG.UI_PLATFORM_INDUSTRY_FILES_MD5_PRODUCTS+`</span>
                                    <br>
                                    <span>`+LANG.UI_PLATFORM_INDUSTRY_FILES_MD5_VERIFYS+`</span>
                                </td>
                                <td class="text-left file_size">
                                    <span>10MB</span>
                                    <br>
                                    <span class="">10MB</span>
                                </td>
                                <td class="text-left file_attr">
                                    <span>--<span class="item_en">(-)</span></span>
                                    <br>
                                    <span>--<span class="item_en">(-)</span></span>
                                </td>
                                <td class="text-left file_create">
                                    <span>xxxx/xx/xx xx:xx:xx</span>
                                    <br>
                                    <span>xxxx/xx/xx xx:xx:xx</span>
                                </td>
                                <td class="text-left file_modify">
                                    <span>xxxx/xx/xx xx:xx:xx</span>
                                    <br>
                                    <span>xxxx/xx/xx xx:xx:xx</span>
                                </td>
                                <td class="text-td">
                                    <i class="viconfont vicon-Frame6"></i>
                                    `+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME+`
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></div>
                </div>`;
    }

    // 底部6个签字等元素生成
    function makeBottomBlock(id='item_make_time', item1='item-make_time', item2='item_make_time',name=LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE_TIME,en_name='Report generation time',value=LANG.UI_PLATFORM_INDUSTRY_YMD){
        return  `<div class="draggable item-1 item-items `+item1+` item-bottom-items" id="`+id+`" data-field="`+item2+`">
                    <i class="viconfont vicon-tuozhuai display-none show-hover-icon del-bottom-items"></i>
                    <div class="margin-r-10">
                        <span class="item-verify-bottom-ch">`+name+`：</span>
                        <span class="item-verify-bottom-val">`+value+`</span>
                        <div class="item_en item-verify-bottom-en">`+en_name+`</div>
                    </div>
                    <span class="del"><i class="viconfont vicon-zujianshanchu display-visibility"></i></span>
                </div>`;
    }

    // switch开关改变事件
    function addListenerSwitch() {
        // 报告水印事件
        $('#report_water').on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                reportWater = true;
                $('.report_water_div_style').show();
            } else {
                reportWater = false;
                $('.report_water_div_style').hide();
                $('.report_water_div_diy').hide();
                $('#report_water_select').val(0);
            }
        })
        $('#report_water_select').on('change', function (){
            if ($(this).val() != 0) {
                // 自定义
                $('.report_water_div_diy').show();
            } else {
                $('.report_water_div_diy').hide();
            }
        });

        // 截屏水印事件
        $('#screen_water').on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                screenWater = true;
                $('.screen_water_div_diy').show();
            } else {
                screenWater = false;
                $('.screen_water_div_diy').hide();
            }
        })
        $('#screen_water_select').on('change', function (){
            if ($(this).val() == 1) {
                // 自定义
                $('.screen_water_div_diy').show();
            } else {
                $('.screen_water_div_diy').hide();
            }
        });

        // 通知邮件事件
        $('#email_notice').on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                emailNotice = true;
                $('.email_notice_div').show();
            } else {
                emailNotice = false;
                $('.email_notice_div').hide();
            }
        })
    }

    // 添加新元素并使其可拖拽
    function addItem(elem){
        var item = elem.attr('data-id');
        var html = '';
        var newItemId = item + ($(".draggable").length + 1);
        if (item == 'item_field') {
            html = makeDiyField(1, newItemId, LANG.UI_PLATFORM_INDUSTRY_DIY_FIELDS, 'Custom Fields', '');
        } else if (item == 'item_textarea') {
            html = makeDiyField(3, newItemId, LANG.UI_PLATFORM_INDUSTRY_DIY_TEXTAREA, 'Custom Text','', LANG.UI_PLATFORM_INDUSTRY_DIY_FIELDS_TIPS);
        } else if (item == 'item_datetime') {
            html = makeDiyField(2, newItemId, LANG.UI_PLATFORM_INDUSTRY_DIY_DATE, 'Custom Date','', '');
        } else if (!itemField[item]) {
            if ($.inArray(item, ['item_plan', 'item_result']) !== -1) {
                // 编辑器
                var item1 = 'item-plan',item2 = 'item_plan',name = LANG.UI_PLATFORM_INDUSTRY_VERIFY_PLAN,en_name = 'VERFICATION PLAN',val = oldData.item_plan ? oldData.item_plan : '';
                if (item == 'item_result') {
                    item1 = 'item-result';
                    item2 = 'item_result';
                    name = LANG.UI_PLATFORM_INDUSTRY_VERIFY_RESULT;
                    en_name = 'VERFICATION CONCLUSION';
                    val = oldData.item_result ? oldData.item_result : LANG.UI_PLATFORM_INDUSTRY_VERIFY_RESULT_TIPS;
                }
                html = makeEditor(item, item1, item2,name, en_name, val);
            } else {
                html = itemFieldArr[item];
            }

            newItemId = item;
            controllItem(elem);
            itemField[item] = true;
        } else {
            return;
        }

        $(".container-industry-template").append(html);
        makeDraggable("#" + newItemId); // 单独对新元素应用拖拽
        item == 'item_datetime' && initDatetimePicker(); //日期控件需要初始化
        item == 'item_logo' && initUploadLogo(); //需要初始化
        if ($.inArray(item, ['item_plan']) !== -1) {
            itemPlanEditor = UE.getEditor(item+'_editor', {
                // 隐藏工具栏
               // toolbars: [],
                // 隐藏元素路径提示
                elementPathEnabled: false,
                initialFrameHeight:editorHeight - 80,
                wordCountMsg: LANG.UI_PLATFORM_INDUSTRY_EDITOR_LEFT + ' {#count} ' + LANG.UI_PLATFORM_INDUSTRY_EDITOR_RIGHT
            }); //编辑器控件需要初始化
            listenEditor(itemPlanEditor, item);
            makePlan(planList);
            // 绑定事件
            $('#plan_uuid').on('change', function (){
                var val = $(this).val();
                bindPlan(val);
            })
        }
        if ($.inArray(item, ['item_result']) !== -1) {
            itemResultEditor = UE.getEditor(item+'_editor', {
                // 隐藏工具栏
               // toolbars: [],
                // 隐藏元素路径提示
                elementPathEnabled: false,
                initialFrameHeight:editorHeight - 80,
                wordCountMsg: LANG.UI_PLATFORM_INDUSTRY_EDITOR_LEFT + ' {#count} ' + LANG.UI_PLATFORM_INDUSTRY_EDITOR_RIGHT
            }); //编辑器控件需要初始化
            listenEditor(itemResultEditor, item);
        }
        if (item == 'item_approval') {
            makeApproval(approvalList);
            // 绑定事件
            $('#approval_uuid').on('change', function (){
                var val = $(this).val();
                bindApproval(val);
            })
        }
        //console.log('source:' + source + ' isLoading' + isLoading)
        mouceUpDown();
        !isLoading && repositionItems() && repositionItems2();
        resizeTemp();
        if (!isLoading) {
            // 不是编辑初始化组件
            // 平滑滚动到某个元素的位置
            $('#parent_container').animate({scrollTop: $("#" + newItemId).position().top}, 'slow');
        }
    }

    // 编辑器监听事件
    function listenEditor(editor, id){
        // 等待编辑器加载完成
        var is_show = false;
        editor.ready(function() {
            if (id == 'item_plan') {
                // create ing plan can not edit
                editor.setDisabled();
            }
            // 鼠标离开编辑器区域时隐藏工具栏
            $('#' + id + '_editor').find('*').filter(function() {
                return this.id && this.id.endsWith('_toolbarbox');
            }).hide();
            $('#' + id + '_editor').find('*').filter(function() {
                return this.id && this.id.endsWith('_iframeholder');
            }).css({
                'height': editorHeight
            });

            // 获取工具栏 DOM 元素
            $('#'+id + ' .item-plan-right').on('click', function (){
                if (is_show) {
                    // 鼠标离开编辑器区域时隐藏工具栏
                    $('#' + id + '_editor').find('*').filter(function() {
                        return this.id && this.id.endsWith('_toolbarbox');
                    }).hide();
                    $('#' + id + '_editor').find('*').filter(function() {
                        return this.id && this.id.endsWith('_iframeholder');
                    }).css({
                        'height': editorHeight
                    });
                    $(this).html('<i class="viconfont vicon-gongjuyincang"></i>');
                    $(this).attr('title', LANG.UI_PLATFORM_INDUSTRY_EDITOR_TOOLS);
                    is_show = false;
                } else {
                    // 鼠标进入编辑器区域时显示工具栏
                    $('#' + id + '_editor').find('*').filter(function() {
                        return this.id && this.id.endsWith('_toolbarbox');
                    }).show();
                    $('#' + id + '_editor').find('*').filter(function() {
                        return this.id && this.id.endsWith('_iframeholder');
                    }).css({
                        'height': editorHeight - 80
                    });
                    $(this).attr('title', LANG.UI_PLATFORM_INDUSTRY_EDITOR_TOOLS2);
                    $(this).html('<i class="viconfont vicon-gongju"></i>');
                    is_show = true;
                }
            })
            /*$('#'+id).mouseenter(function(){
                // 鼠标进入编辑器区域时显示工具栏
                $('#' + id + '_editor').find('*').filter(function() {
                    return this.id && this.id.endsWith('_toolbarbox');
                }).show();
                $('#' + id + '_editor').find('*').filter(function() {
                    return this.id && this.id.endsWith('_iframeholder');
                }).css({
                    'height': editorHeight - 80
                });
            }).mouseleave(function(){
                // 鼠标离开编辑器区域时隐藏工具栏
                $('#' + id + '_editor').find('*').filter(function() {
                    return this.id && this.id.endsWith('_toolbarbox');
                }).hide();
                $('#' + id + '_editor').find('*').filter(function() {
                    return this.id && this.id.endsWith('_iframeholder');
                }).css({
                    'height': editorHeight
                });
            });*/
        });
    }

    // 中间那块添加组件
    function addItem2(elem){
        var item = elem.attr('data-id');
        var html = '';
        if (!itemField[item]) {
            html = itemFieldArr[item];
            itemField[item] = true;
            controllItem(elem);
        }

        $(".container2-industry-template").append(html);
        if (item == 'item_document') {
            $('#file_size').iCheck({
                checkboxClass : 'icheckbox_square-blue'
            });
            $('#file_position').iCheck({
                checkboxClass : 'icheckbox_square-blue'
            });
            $('#file_create').iCheck({
                checkboxClass : 'icheckbox_square-blue'
            });
            $('#file_update').iCheck({
                checkboxClass : 'icheckbox_square-blue'
            });
            changeDocument();
        }

        makeDraggable("#" + item, '.container2-industry-template', '.draggable2'); // 单独对新元素应用拖拽
        mouceUpDown();
        !isLoading && repositionItems2() && repositionItems();
        if (!isLoading) {
            // 不是编辑初始化组件
            var base = $('.container2-industry-template').position().top;
            // 平滑滚动到某个元素的位置
            $('#parent_container').animate({scrollTop: $("#" + item).position().top + base}, 'slow');
        }
        resizeTemp();
    }

    // 添加或者删除组件的样式控制
    function controllItem(elem, type = 0){
        if (type == 0) {
            // 添加
            elem.addClass('item-active');
            elem.attr('title', LANG.UI_COMPONENT_SELECTED);
           // elem.prepend('<span class="close-icon"> <i class="viconfont vicon-zujianshanchu"></i></span>');
            initDelItem();
        } else {
            // 删除
            elem.removeClass('item-active');
            elem.attr('title', '');
          //  elem.find('.close-icon').remove();
        }
        if (elem.hasClass('addItem')) {
            bindButtionMove();
        } else {
            bindButtionMove('.addItem2', '.container2-industry-template');
        }
    }

    // 限制只能输入正整数
    function limitInt(elem){
        $(elem).keydown(function(e) {
            // 允许退格键、方向键、Ctrl+A（全选）、Ctrl+C（复制）、Ctrl+V（粘贴）
            if ($.inArray(e.keyCode, [8, 9, 27, 37, 38, 39, 40, 46, 65, 67, 86, 110, 190]) !== -1 ||
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode >= 35 && e.keyCode <= 40) || // 方向键
                (e.keyCode >= 48 && e.keyCode <= 57) || // 数字键
                (e.keyCode >= 96 && e.keyCode <= 105)) { // 数字键盘上的数字键
                return;
            }
            // 阻止其他键
            e.preventDefault();
        });
        $(elem).on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, ''); // 移除所有非数字字符
            // 如果需要排除0（仅允许正整数），可以添加以下行
            if (this.value === '0' || this.selectionStart === 0) {
                 this.value = ''; // 当用户试图输入0且输入框为空时，清空输入框
            }

            // 或者，如果你想要允许0但不允许前导0（除了0本身），可以使用以下逻辑
            // if (this.value !== '' && this.value[0] === '0') {
            //     this.value = this.value.slice(1); // 移除前导0
            // }
        })
        // 阻止粘贴非数字内容
        $(elem).on('paste', function(e) {
            var clipboardData, pastedData;

            // 阻止默认粘贴动作
            e.preventDefault();

            // 获取粘贴板数据
            clipboardData = e.originalEvent.clipboardData || window.clipboardData;
            pastedData = clipboardData.getData('Text');

            // 使用正则表达式检查粘贴内容是否只包含数字
            if (/^\d+$/.test(pastedData)) {
                // 如果只包含数字，则允许粘贴
                document.execCommand('insertText', false, pastedData);
            }
        });
    }

    // 限制input框的输入长度
    function limitLength() {
        // 使用事件委托：绑定到 document，但只触发指定 input 的 input 事件
        $(document).on('input', 'input[name="item_field_name"]', function() {
            limitMaxBlob($(this).val(), $(this), 16);
        });

        $(document).on('input', 'input[name="item_field_name_en"]', function() {
            limitMaxBlob($(this).val(), $(this), 16);
        });

        $(document).on('input', 'input[name="item_field_value"]', function() {
            limitMaxBlob($(this).val(), $(this)); // 默认限制 36 字节（假设你的 limitMaxBlob 默认是 36）
        });
    }

    function limitMaxBlob(value, $dom, length = 36) {
        const inputEl = $dom[0];
        if (!inputEl || typeof inputEl.setSelectionRange !== 'function') return;

        // 更精确的字节计算
        function getByteLength(str) {
            // 使用 TextEncoder 获取准确字节数
            return new TextEncoder().encode(str).length;
        }

        const byteLength = getByteLength(value);
        //console.log('计算出的 byteLength:', byteLength);
        //console.log('比较: byteLength <= length ?', byteLength <= length);
        if (byteLength <= length) return;

        const cursorPos = inputEl.selectionStart || 0;

        // 使用 TextEncoder 进行准确截断
        const encoder = new TextEncoder();
        const decoder = new TextDecoder();

        let encoded = encoder.encode(value);
        if (encoded.length > length) {
            // 准确截断到指定字节
            const truncatedEncoded = encoded.slice(0, length);
            // 确保不截断在 UTF-8 多字节字符的中间
            let safeLength = length;
            while (safeLength > 0 && (truncatedEncoded[safeLength - 1] & 0xC0) === 0x80) {
                safeLength--;
            }
            const finalEncoded = encoded.slice(0, safeLength);
            const truncated = decoder.decode(finalEncoded);

            $dom.val(truncated);
            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS,
                `内容超过长度，仅保存前${length}个字节（实际字符数：${truncated.length}）`);

            // 恢复光标位置
            if (document.activeElement === inputEl) {
                const newCursorPos = Math.min(cursorPos, truncated.length);
                requestAnimationFrame(() => {
                    if (document.activeElement === inputEl) {
                        inputEl.setSelectionRange(newCursorPos, newCursorPos);
                    }
                });
            }
        }
    }

    // 提交和生成事件
    function addListenerSub(){
        // 取消按钮，返回列表
        $('#cancelBut').on('click', function (){
            // 记录来源，根据url的type来判断，返回报告列表或者任务详情
            if (source == 1) {
                // 返回列表
                LOCATION('./content/platform/industry/template_list.php');
            } else {
                // 关闭抽屉
                $('#drawer-indust_template_config').drawer('hide');
            }
        })

        // 生成按钮点击事件
        $('#addItems').click(function() {
            if (!checkDiyValue()) {
                return false;
            }

            makeFinalMsg();

            // 隐藏第一步 显示第二步
            $('#industry_template').hide();
            $('#industry_template_step2').show();
        })

        // 上一步下一步显示
        $('#template_pre').click(function (){
            // 第二步的上一步
            $('#industry_template_step2').hide();
            $('#industry_template').show();
        })
        $('#template_next').click(function (){
            // 第二步的下一步
            if (!calcSetting()) {
                return;
            }
            $('#industry_template_step2').hide();
            $('#industry_template_pre').show();

            var previewContainer = createHtml();
            $('#step3').html(previewContainer.html());
        })
        $('#template_pre2').click(function (){
            // 第三步的上一步
            $('#step3').html('');
            $('#industry_template_pre').hide();
            $('#industry_template_step2').show();
        })
        $('#template_submit2').click(function (){
            addSubmit();
        })
        // 提交按钮事件
        $('#template_submit').click(function (){
            addSubmit();
        })
    }

    // 提交
    function addSubmit(){
        if (!calcSetting()) {
            return;
        }
        Metronic.blockUI({target: '#drawer-1',animate: true,cenrerY: true});
        pAjaxRequest(submitData, "/api/v1/industry/templates/" + uuid, "POST", function (result) {
            Metronic.unblockUI('#drawer-1');
            if (result.code == 0) {
                $('#drawer-1').drawer('hide');
                // 返回模板列表
                UIToastr.showSuccess(LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING, result.message);
                LOCATION('./content/platform/industry/template_list.php');
            } else {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING, result.message);
            }
        });
    }

    // 组合高级配置
    function calcSetting() {
        submitData.name = $('#temp_name').val(); // 模板名称
        submitData.virtus_num = $('#virtus_num').val(); // 病毒查杀展示数量
        submitData.document_num = $('#document_num').val(); // 文件比对展示数量

        if (submitData.name == '') {
            UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING, LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING_TIPS);
            $('#temp_name').focus();
            return false;
        }

        /*if (submitData.virtus_num == '') {
            $('#virtus_num').focus();
            UIToastr.showWarning('模板配置', '病毒查杀展示数量不能为空！');
            return false;
        }*/
        if (submitData.document_num == '') {
            $('#document_num').focus();
            UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING, LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_FILES_TIPS);
            return false;
        }

        if (submitData.document_num > 9999) {
            $('#document_num').focus();
            UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING, LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_FILES_TIPS2 + '9999');
            return false;
        }

        // 组合下文件比对属性的内容
        submitData.document_custom = {
            file_size: $('#file_size').is(':checked'),
            file_position: $('#file_position').is(':checked'),
            file_create: $('#file_create').is(':checked'),
            file_update: $('#file_update').is(':checked'),
        };
        // 设置选中 $('#copyCheck').iCheck('check');

        var report_water = {flag: false, type: 0, value: ''}; // 报告水印
        if (reportWater) {
            report_water.flag = true;
            report_water.type = $('#report_water_select').val();
            if (report_water.type != 0) {
                report_water.value = $('#report_water_value').val();
            }
        }
        submitData.report_water = report_water;

        var screen_water = {flag: false, type: 0, value: ''}; // 截屏水印
        if (screenWater) {
            screen_water.flag = true;
            screen_water.type = 1;
            if (screen_water.type == 1) {
                screen_water.value = $('#screen_water_value').val();
            }
        }
        submitData.screen_water = screen_water;

        var diy_log = {flag: false, value: ''}; // logo
        if ($('.upload-flag').attr('src') != undefined) {
            diy_log.flag = true;
            diy_log.value = $('#diy_logo_value_url').val();
        }
        submitData.logo = diy_log;

        var email_notice = {flag: false, value: ''}; // 邮箱通知
        if (emailNotice) {
            email_notice.flag = true;
            email_notice.value = $('#email_notice_value').val();
        }
        submitData.email_notice = email_notice;

        submitData.remark = $('#remark').val();
        // 请求接口
       // console.log('submitData', submitData);
        return true;
    }

    // 封装上传图片事件
    function uploadImage(elem, fun){
        $(elem).fileupload({
            url: `/api/v1/system/upload`,
            dataType: 'json',
            acceptFileTypes: 'image',
            method: 'POST',
            headers: {
                'x-api-version': '1.0-rev0',
                'Authorization': window.localStorage.getItem('access_token')
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .bar').css('width', progress + '%');
            },
            done: function (e, data) {
                var result = data.result;
               if (result.code == 0) {
                   UIToastr.showSuccess(LANG.UI_SETTING_UPLOAD_FILE, LANG.UI_SETTINGS_UPDATE_UPLOAD_SUCCESS);
                   // 上传成功
                    $(elem + '_url').val(result.data.info);
                   var img = '<a href="'+result.data.info+'" target="_blank"><img src="'+result.data.info+'"></a>';
                    $(elem+'_url_show').html(img);
                    // 回调函数
                   fun(result.data);
               } else {
                   UIToastr.showError(LANG.UI_SETTING_UPLOAD_FILE, result.message);
                   return false;
               }
            },
            start: function (e) {
            },
            stop: function (e) {
            },
            fail: function (e, data) {
                UIToastr.showWarning(LANG.UI_SETTING_UPLOAD_FILE, LANG.UI_SETTINGS_UPDATE_UPLOAD_ERROR);
                return false;
            },
        });
    }

    // 提示重叠
    function showSameTip(){
        UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING, LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM);
        return false;
    }

    //初始化当前创建的审批流
    const initApproval = function () {
        pAjaxRequest({offset:0}, '/api/v1/approvals/list', 'GET', (res) => {
          //  console.log(res);
            var data = res.data.rows;
            approvalList = data;
            makeApproval(data);
        });
    }

    // 初始化当前创建的方案
    const initPlanList = function () {
        pAjaxRequest({offset:0, type: 6}, '/api/v1/industry/plan', 'GET', (res) => {
            //  console.log(res);
            var data = res.data.rows;
            planList = data;
            makePlan(data);
        });
    }

    function makeApproval(data) {
        var select = $('#approval_uuid');
        select.empty();
        var option = $("<option>").text(LANG.UI_PLATFORM_INDUSTRY_APPROVAL_SELECT).val('0');
        select.append(option);
        for (var i = 0; i < data.length; i++) {
            option = $("<option>").text(data[i].name).val(data[i].approval_uuid);
            select.append(option);
        }
        select.val('0');
        if (oldData.approval_uuid != undefined) {
            select.val(oldData.approval_uuid);
            select.change();
        }
    }

    function makePlan(data) {
        var select = $('#plan_uuid');
        select.empty();
        var option = $("<option>").text(LANG.UI_PLATFORM_INDUSTRY_PLAN_SELECT).val('0');
        select.append(option);
        for (var i = 0; i < data.length; i++) {
            option = $("<option>").text(data[i].name + '【'+data[i].version+'】').val(data[i].uuid);
            select.append(option);
        }
        select.val('0');
        if (oldData.plan_uuid != undefined && oldData.plan_uuid != '') {
            select.val(oldData.plan_uuid);
            select.change();
        }
    }

    function getDepthContent(depthContent, createName) {
        var html = '';
        var isPass = 0; //是否通过, 0否，1是，2驳回
        var stageDes = LANG.UI_PLATFORM_INDUSTRY_APPROVAL_START;
        var color = '#2A87C8'; //默认蓝色
        var labelClass = 'label-success'; //默认绿色label
        var icon = 'vicon-tongguo'; //默认通过
        html += `<div class="depth-parent">
                    <div class="depth-title">
                        <div class="circle" style="color:${color}"><i class="viconfont ${icon}"></i></div><span class="label label-sm ${labelClass} ml15" id="create_user">${stageDes}</span>
                    </div>
                    <div class="depth-connect mt8"><span style="
                    font-size: 14px;
                    color: #999999;
                    line-height: 16px;
                ">`+LANG.UI_PLATFORM_INDUSTRY_APPROVAL_START_PEOPLE+`：</span><span id="create_user">${createName}</span></div>
                </div>`;
        for (let i = 0; i < depthContent.length; i++) {
            var eachContent = depthContent[i];
            color = '#1296db';
            labelClass = 'label-info';
            icon = "vicon-a-dengdaidaishenpi";

            html += `<div class="depth-parent">
                        <div class="depth-title">
                            <div class="circle" style="color:${color}"><i class="viconfont ${icon}"></i></div><span class="label label-sm ${labelClass} ml15" id="create_user">`+LANG.UI_PLATFORM_INDUSTRY_APPROVAL_OPERATE+`</span>
                        </div>
                        <div class="depth-connect mt8"><span style="
                        font-size: 14px;
                        color: #999999;
                        line-height: 16px;
                    ">`+LANG.UI_PLATFORM_INDUSTRY_APPROVAL_OPERATE_PEOPLE+`：</span><span>${eachContent.approval_user_name}</span></div>
                    </div>`;
        }

        html += `<div class="depth-parent end">
                    <div class="depth-title">
                        <div class="circle" style="color:#2A87C8"><i class="viconfont vicon-tongguo"></i></div><span class="label label-sm label-success ml15" id="create_user">`+LANG.UI_PLATFORM_INDUSTRY_APPROVAL_COMPLETE+`</span>
                    </div>
                    <div class="depth-connect mt8"><span style="
                    font-size: 14px;
                    color: #999999;
                    line-height: 16px;
                ">`+LANG.UI_PLATFORM_INDUSTRY_APPROVAL_OPERATE_PEOPLE+`：</span><span>system</span></div>
                </div>`;
        return html;
    }

    // 绑定审批流监听事件
    function bindApproval(val) {
        if (val != 0) {
            // 循环取出当前选中的审批流的层级
            for (var k in approvalList){
                if (approvalList[k]['approval_uuid'] == val) {
                    var html = getDepthContent(approvalList[k]['content'], 'operator');
                    html = '<div style="margin-left:78px;">'+html+'</div>';
                    $('#item_approval .img-items').html(html);
                    $('#item_approval').css({
                        'height': 'auto',
                    })
                    repositionItems();
                    return;
                }
            }
        } else {
            $('#item_approval .img-items').html('<img src="./img/platform/industry/approval.png">');
            $('#item_approval').css({
                'height': '260px',
            })
            repositionItems();
        }
    }
    // 绑定方案监听事件
    function bindPlan(val) {
        if (val != 0) {
            // 请求接口获取方案内容以填充
            Metronic.blockUI({target: '#item_plan',animate: true,cenrerY: true});
            pAjaxRequest({}, '/api/v1/industry/plan/'+val, 'GET', function (result) {
                Metronic.unblockUI('#item_plan');
                if (result.code == 0) {
                    itemPlanEditor.setContent(result.data.content);
                } else {
                    UIToastr.showError(LANG.UI_PUBLIC_TIPS, result.message);
                }
            });
        } else {
           itemPlanEditor.setContent('');
        }
    }
    // 初始化监听事件
    function initListener() {
        resizeTemp();
        // 如果你需要在窗口大小改变时重新获取高度，可以这样做：
        $(window).resize(function() {
            repositionItems();
            repositionItems2();
            resizeTemp();
        });

        // 监听开关事件
        addListenerSwitch();

        // 提交事件
        addListenerSub();

        // 初始化上传按钮
        initUploadLogo();

        // 文件比对属性改变事件
        changeDocument();

        // 限制正整数的输入
        limitInt('#virtus_num');
        limitInt('#document_num');

        mouceUpDown();

        $('#approval_uuid').on('change', function (){
            var val = $(this).val();
            bindApproval(val);
        })

        $('#plan_uuid').on('change', function (){
            var val = $(this).val();
            bindPlan(val);
        })

        // 报告标题修改事件
        $('.edit-title i').on('click', function (){
            fouces();
            var inputElement = $('#report_title');
            inputElement.focus();
            setTimeout(function (){
                // 将光标移动到文本的末尾
                if (inputElement.setSelectionRange) {
                    var length = inputElement.val().length;
                    inputElement.setSelectionRange(length, length);
                }
            }, 0);
        });
        $('#report_title').blur(function (){
            if (!$('#report_title2').is(':focus')) {
                $('.edit-title i').show();
                $('.report-title').find('input').css({
                    'border-bottom': 'none'
                })
                $('#report_title').removeClass('hovers')
                $('#report_title2').removeClass('hovers')
            }
        }).focus(function (){
            fouces()
        });
        $('#report_title2').blur(function (){
            if (!$('#report_title').is(':focus')) {
                $('.edit-title i').show();
                $('.report-title').find('input').css({
                    'border-bottom': 'none'
                })
                $('#report_title').removeClass('hovers')
                $('#report_title2').removeClass('hovers')
            }
        }).focus(function (){
            fouces()
        });

        function fouces(){
            $('.edit-title i').hide();
            $('.report-title').find('input').css({
                'border-bottom': '1px solid #E6E6E6'
            })
            $('#report_title').addClass('hovers')
            $('#report_title2').addClass('hovers')
        }

        $('#container').find('input[type="text"]').focus(function (){
            $(this).closest('.item-items').addClass('hovers');
        }).blur(function (){
            $(this).closest('.item-items').removeClass('hovers');
        });

        // 监听 input 事件
        $('#report_title').on('input', function() {
            // 获取当前输入框的值
            var inputValue = $(this).val();
            // 计算输入框的长度
            var inputLength = inputValue.length;
            // 使用正则表达式匹配汉字
            var chineseChars = inputValue.match(/[\u4e00-\u9fa5]/g);
            // 计算汉字的数量
            var charCount = chineseChars ? chineseChars.length : 0;

            var right = Math.max(388 - (charCount * 10 + (inputLength - charCount) * 6), 186);
            $('.edit-title').css({
                'right': right + 'px',
            })
            // 在控制台输出长度
           // console.log('Current length: ' + inputLength);
        });

        /**
         * 使用事件委托，监听动态生成的 .input-en 输入框
         * 支持 input 和 paste 事件
         */
        $('#container').on('input paste', 'input.input-en[name="item_field_name_en"]', function(e) {
            let $input = $(this);

            // 延迟执行，确保 paste 的内容已写入
            setTimeout(() => {
                let value = $input.val();
                // 保留 ASCII 可打印字符：\x20-\x7E（空格到 ~）
                let filtered = value.replace(/[^\x20-\x7E]/g, '');

                if (filtered !== value) {
                    $input.val(filtered);

                    // 可选：给出提示（可注释掉）
                    if (e.type === 'input') {
                        // 避免频繁弹窗
                        if (value.length > filtered.length) {
                            // console.warn('已过滤非法字符（中文、全角、Emoji 等）');
                        }
                    } else if (e.type === 'paste') {
                        // alert('粘贴内容包含非法字符，已自动过滤！');
                    }
                }
            }, 10);
        });

        // 展开收起事件绑定
        $('.showItems').on('click', function (){
            if (isExpend == true) {
                // 默认是展开的 那么就是收起操作
                isExpend = false;
                $('#container-item').parent().css({
                    'visibility': 'hidden'
                });
                $('.showItems2').show();
            } else {
                // 是收起的 那么就是展开操作
                isExpend = true;
                $('#container-item').parent().css({
                    'visibility': 'visible'
                });
                $('.showItems2').hide();
            }
            repositionItems();
            repositionItems2();
            resizeTemp();
        })
    }

    // 文件比对属性改变事件
    function changeDocument(){
        // 文件比对属性自定义
        $('#item_document').find('.icheck').on('ifClicked', function (event){
            var classs = $(this).data('id');
            var checkedCount = $('#item_document').find('.icheck:checked').length;
            if(event.target.checked){
                //如果是取消选中
                $('#item_document').find('.'+classs).hide();
                checkedCount --;
            }else{
                $('#item_document').find('.'+classs).show();
            }
            // 获取选中的复选框数量
            if (checkedCount == 0) {
                // 那么动态的调整下
                $('.verify_method').attr('width', '');
            } else {
                $('.verify_method').attr('width', '248');
            }
        });
    }

    function initUploadLogo(){
        // 初始化上传按钮
        uploadImage('#diy_logo_value', function (data){
            var img = ' <img class="upload-img upload-flag" title="'+LANG.UI_PLATFORM_INDUSTRY_UPLOAD_LOGO_TIPS+'：120x50 px" src="'+data.info+'">';
            $('#item_logo .upload-div').find('.upload-flag').replaceWith(img)
            $('#item_logo .upload-div').attr('title', LANG.UI_PLATFORM_INDUSTRY_UPLOAD_LOGO_TIPS2);
            $('#item_logo .upload-div').css({
                'background': '#fff'
            });
        });
    }

    // 鼠标移入事件
    function mouceUpDown(){
        $(".item-items").mouseenter(function(){
            $(this).find('.show-hover-icon').show(); // 显示图标
            $(this).find('.del i').removeClass('display-visibility'); // 显示图标
        }).mouseleave(function(){
            $(this).find('.show-hover-icon').hide(); // 隐藏图标
            $(this).find('.del i').addClass('display-visibility');
        });
    }

    // 新增初始化
    function initAdd(){
        // 初始化现有元素
        makeDraggable(".draggable");
        makeDraggable(".draggable2", '.container2-industry-template', '.draggable2');

        $('.draggable-s').find('.del').click(function() {
            delItem($(this));
        });

        // 初始化编辑器
        itemPlanEditor = UE.getEditor('item_plan_editor', {
            // 隐藏工具栏
           // toolbars: [],
            // 隐藏元素路径提示
            elementPathEnabled: false,
            initialFrameHeight:editorHeight - 80,
            wordCountMsg: LANG.UI_PLATFORM_INDUSTRY_EDITOR_LEFT + ' {#count} '+LANG.UI_PLATFORM_INDUSTRY_EDITOR_RIGHT
        });
        listenEditor(itemPlanEditor, 'item_plan');
        itemResultEditor = UE.getEditor('item_result_editor', {
            // 隐藏工具栏
           // toolbars: [],
            // 隐藏元素路径提示
            elementPathEnabled: false,
            initialFrameHeight:editorHeight - 80,
            wordCountMsg: LANG.UI_PLATFORM_INDUSTRY_EDITOR_LEFT + ' {#count} '+LANG.UI_PLATFORM_INDUSTRY_EDITOR_RIGHT
        });
        listenEditor(itemResultEditor, 'item_result');

        $('#report_water').bootstrapSwitch('state', false); // 默认报告水印开关关闭
        $('#screen_water').bootstrapSwitch('state', false); // 默认截屏水印开关关闭
        $('#diy_logo').bootstrapSwitch('state', false); // 自定义logo开关关闭
        $('#email_notice').bootstrapSwitch('state', false); // 邮箱通知开关关闭

        // 初始化那些组件按钮是否选中
        for (var j in itemField){
            if (itemField[j] == true) {
                controllItem($('#container-item').find('.'+j));
            }
        }

        setTimeout(function (){
            repositionItems();
            repositionItems2();
        }, 500);

    }

    // 详情初始化
    function initView(uuid){
        // 那么需要读取接口
        Metronic.blockUI({target: '#industry_template',animate: true,cenrerY: true});
        pAjaxRequest({job_uuid: jobUuid}, "/api/v1/industry/templates/"+uuid, "GET", function (result) {
            Metronic.unblockUI('#industry_template');
            if (result.code == 0) {
                oldData = result.data
                consoleView(result.data);
            } else {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING, result.message);
                if (source == 1) {
                    // 返回列表
                    LOCATION('./content/platform/industry/template_list.php');
                } else {
                    // 关闭抽屉
                    $('#drawer-indust_template_config').drawer('hide');
                }
            }
        },false);
    }

    // 处理初始化详情的数据解析
    function consoleView(data){
        if (source == 1) {
            $('#temp_name').val(data.name);
            $('#virtus_num').val(data.virus_num);
            $('#document_num').val(data.document_num);

            // 报告水印
            $('#report_water').bootstrapSwitch('state', data.report_water.flag);
            reportWater = data.report_water.flag;
            reportWater && $('.report_water_div_style').show();
            reportWater && $('#report_water_select').val(data.report_water.type);
            reportWater && data.report_water.type != '0' && $('.report_water_div_diy').show();
            $('#report_water_value').val(data.report_water.value);

            // 截屏水印
            $('#screen_water').bootstrapSwitch('state', data.screen_water.flag);
            screenWater = data.screen_water.flag;
            screenWater && $('.screen_water_div_diy').show();
            $('#screen_water_value').val(data.screen_water.value);

            // 邮箱通知
            $('#email_notice').bootstrapSwitch('state', data.email_notice.flag);
            emailNotice = data.email_notice.flag;
            emailNotice && $('.email_notice_div').show();
            $('#email_notice_value').val(data.email_notice.value);

            $('#remark').val(data.remark);
        }

        // 处理下组件的显示问题
        $('.container-industry-template').find('[id^="item_"]').remove(); // 清除默认的一些组件元素
        initItemShow(data);
    }

    // 初始化处理下组件的显示问题
    function initItemShow(data){
        // 1, 所有的组件默认为未选择 并且remove这些个的元素
        for (var k in itemField) {
            itemField[k] = false;
        }

        var width = $(".container-industry-template").width(); // 记录此时的内容的宽度
        var scale = width / (data.item_width ? data.item_width : width);

        analysis(data['summary'], scale, 1);// 概要
        analysis(data['description'], scale, 2);// 详情

        // 组合下文件比对属性的内容
        if (data.document_custom.file_size) {
            $('#file_size').iCheck('check');
        } else {
            $('#item_document').find('.file_size').hide();
        }
        if (data.document_custom.file_position) {
            $('#file_position').iCheck('check');
        } else {
            $('#file_position').iCheck('uncheck');
            $('#item_document').find('.file_position').hide();
        }
        if (data.document_custom.file_create) {
            $('#file_create').iCheck('check');
        } else {
            $('#file_create').iCheck('uncheck');
            $('#item_document').find('.file_create').hide();
        }
        if (data.document_custom.file_update) {
            $('#file_update').iCheck('check');
        } else {
            $('#file_update').iCheck('uncheck');
            $('#item_document').find('.file_modify').hide();
        }

        if (source == 2) {
            $('#industry_template .item-items').find('.del').css({
                'visibility': 'hidden'
            });
            $('#industry_template .img-items').css({
                'margin-top': 0,
            });
            $('#industry_template .item-items').removeClass('item-items');
            $('#item_approval select').hide();
            $('#item_document .file-lie').hide();
        }
        makeDraggable(".draggable");
        makeDraggable(".draggable2", '.container2-industry-template', '.draggable2');

        mouceUpDown();
        repositionItems();
        repositionItems2();
        resizeTemp();
        isLoading = false;
    }

    // 获取预览的html
    function createHtml(){
        // 创建一个新的 div 来存放预览内容
        var previewContainer = $('<div></div>');
        // 获取页面所有的css和html，然后打开dialog
        previewContainer.html($('#parent_container').html());
        // 替换下两个可能存在的编辑器的内容
        // 方案编辑器处理
        if (itemPlanEditor) {
            var item_plan = itemPlanEditor.getContent(); // 方案的内容
            var editor_title = '<div class="basic-info basic-title-plan">' + $('#item_plan').find('.basic-title-plan').html() + '</div>';
            item_plan = '<div style="margin: 10px 0 0 10px;height: calc(100% - 50px);overflow-y: scroll">' + item_plan + '</div>';

            previewContainer.find('#item_plan').html(editor_title + item_plan);
        }
        // 结论编辑器处理
        if (itemResultEditor) {
            var item_result = itemResultEditor.getContent(); // 结论的内容
            var editor_title = '<div class="basic-info basic-title-plan">' + $('#item_result').find('.basic-title-plan').html() + '</div>';
            item_result = '<div style="margin: 10px 0 0 10px;height: calc(100% - 50px);overflow-y: scroll">' + item_result + '</div>';
            previewContainer.find('#item_result').html(editor_title + item_result);
        }
        var report_title = $('#report_title').val();
        if (isEnCn == 1) {
            report_title += '</br>' + $('#report_title2').val();
        }
        previewContainer.find('.report-title').html(report_title);

        // 处理下logo的显示 如果没有自定义，那么显示默认的logo
        if (previewContainer.find('.upload-flag').attr('src') == undefined) {
            var logo = '<img class="upload-img upload-flag" src="./img/platform/logo.png">';
            previewContainer.find('.upload-flag').replaceWith(logo);
        }
        previewContainer.find('.upload-div').css({
            'background': '#fff'
        });


        // 编辑器工具栏按钮处理
        previewContainer.find('.item-plan-right').remove();

        // 去除文件比对的右边的属性
        previewContainer.find('#item_document .file-lie').remove();

        // 所有的input转为html，因为mpdf对input不友好
        previewContainer.find('input').each(function (){
            if ($.inArray($(this).attr('id'), ['report_title', 'report_title2']) !== -1) {
                var val = $('#report_title').val();
                previewContainer.find('.report-title').html(val);
                return;
            }
            var name = $(this).attr('name');
            var id = $(this).parent().attr('id');
            if (id == undefined) {
                id = $(this).parent().parent().attr('id');
            }
            if (id == undefined) {
                id = $(this).parent().parent().parent().attr('id');
            }
            var val = $('#parent_container').find('#'+id +' input[name='+name+']').val();
            if (val == '') {
                val = '\u00A0';
            }
            // 替换input的内容为html
            var newDiv;
            if ($.inArray(name, ['item_field_value', 'item_field_value_en']) !== -1) {
                var clasa = '';
                if ($(this).hasClass('color-yellow')) {
                    clasa = 'color-yellow';
                }
                newDiv = '<span class="item-label '+clasa+'" style="\n' +
                    '    height: 34px;    line-height: 34px;\n' +
                    '    margin-top: 7px;\n' +
                    '    display: inline-block;\n' +
                    '">'+val+'</span>';
            } else {
                if ($(this).attr('type') !== 'hidden') {
                    newDiv = '<span class="margin-r-10 item-span-title text-left">'+val+'</span>';
                }
            }

            // 替换当前的input元素
            previewContainer.find('#'+id +' input[name='+name+']').replaceWith(newDiv);
        })
        // 所有的textarea转为html，因为mpdf对textarea不友好
        previewContainer.find('textarea').each(function (){
            var name = $(this).attr('name');
            var id = $(this).parent().attr('id');
            if (id == undefined) {
                id = $(this).parent().parent().attr('id');
            }
            if (id == undefined) {
                id = $(this).parent().parent().parent().attr('id');
            }
            previewContainer.find('#' + id).css({
                'overflow-y': 'scroll'
            });
            var val = $('#parent_container .container-industry-template').find('#'+id +' textarea[name='+name+']').val();
            if (val == '') {
                val = '\u00A0';
            }
            // 替换input的内容为html
            var newDiv;
            if ($.inArray(name, ['item_field_value', 'item_field_value_en']) !== -1) {
                newDiv = '<span class="item-label" style="\n' +
                    '    height: 34px;    line-height: 34px;\n' +
                    '    margin-top: 7px;\n' +
                    '    display: inline-block;    width: calc(200% + 48px) !important;\n' +
                    '">'+val+'</span>';
            } else if (uuid != '') {
                newDiv = '<span class="margin-r-10 item-span-title text-left">'+val+'</span>';
            }
            // 替换当前的input元素
            previewContainer.find('#'+id +' textarea[name='+name+']').replaceWith(newDiv);
        })

        previewContainer.find('.draggable').addClass('draggable-s').removeClass('draggable');
        previewContainer.find('.draggable2').addClass('draggable-s').removeClass('draggable2');
        previewContainer.find('.del').remove();
        previewContainer.find('.item-items').removeClass('item-items');
        previewContainer.find('#item_approval select').remove();
        previewContainer.find('#item_plan select').remove();

        return previewContainer;
    }

    // 解析组合元素
    function analysis(description, scale, type = 1){
        // 获取所有以'item_'开头的键
        var showItemd = Object.keys(description).filter(key => key.startsWith('item_'));
        for (var j in showItemd) {
            var items = showItemd[j];
            // 处理下位置 取相对的位置
            if ($.inArray(items, itemArr) !== -1) {
                // 多选的那些
                var list = description[items].list;
                // 先组装html 然后手动的渲染和初始化拖拽事件
                var html = '';
                for (var k in list){
                    var newItemId = items + k;
                    var types = 1,placeholder = LANG.UI_PLATFORM_INDUSTRY_INPUT;
                    if (items == 'item_textarea') {
                        types = 3;
                        placeholder = LANG.UI_PLATFORM_INDUSTRY_DIY_FIELDS_TIPS;
                    } else if (items == 'item_datetime') {
                        types = 2;
                    }

                    html = makeDiyField(types, newItemId, list[k].name, list[k].en_name,list[k].value, placeholder);
                    $(".container-industry-template").append(html);

                    items == 'item_datetime' && initDatetimePicker(); //日期控件需要初始化
                    var item = list[k].position.split(',');
                    var left = parseInt(item[1], 10) * scale;
                    var top = parseInt(item[0], 10);
                    $('#'+newItemId).css({
                        'top': top + 'px',
                        'left': left + 'px',
                    });
                    makeDraggable("#" + newItemId); // 单独对新元素应用拖拽
                }
            } else {
                if (items == 'item_title') {
                    // 报告名称
                    $('#report_title').val(description[items].value);
                    if (isEnCn == 1) {
                        $('#report_title2').val(description[items].en_value);
                    }
                } else {
                    if (type == 1){
                        addItem($('#container-item').find('.'+items));
                    } else {
                        addItem2($('#container-item').find('.'+items));
                    }

                    // 如果是logo和二维码 那么位置固定的
                    if ($.inArray(items, notDaragle) === -1) {
                        var item = description[items].position.split(',');
                        var left = parseInt(item[1], 10) * scale;
                        var top = parseInt(item[0], 10);
                        $('#'+items).css({
                            'top': top + 'px',
                            'left': left + 'px',
                        });
                        // 如果是主机名、ip和操作人员有自定义内容
                        if ($.inArray(items, ['item_ip', 'item_host', 'item_operator']) !== -1) {
                            var value = description[items].value;
                            $('#'+items).find('input[name="item_field_value"]').val(value);
                        }
                    }

                    if (items == 'item_logo' && oldData.logo.flag) {
                        var url = oldData.logo.value;
                        var img = ' <img class="upload-img upload-flag" src="'+url+'">';
                        $('#item_logo .upload-div').find('.upload-flag').replaceWith(img)
                        $('#item_logo .upload-div').attr('title', LANG.UI_PLATFORM_INDUSTRY_UPLOAD_LOGO_TIPS2);
                        $('#item_logo .upload-div').css({
                            'background': '#fff'
                        });
                        $('#diy_logo_value_url').val(url);
                    }

                    $('.draggable-s').find('.del').click(function() {
                        delItem($(this));
                    });
                }
            }
        }
    }

    // 左边的拖拽事件绑定
    function bindButtionMove(classify = '.addItem', content = '.container-industry-template'){
        $(classify).each(function() {
            var $button = $(this);
            moveButtion(classify, $button, content);
        });
    }

    // 实现按钮拖拽事件
    function moveButtion(classify = '.addItem', $button, content = '.container-industry-template'){
        // 移除之前绑定的所有拖拽相关事件
        $button.off('.drag');
        $(document).off('.drag');
        // 标记为已拖拽
        var draggableType = $button.hasClass('item-active');
        if (draggableType) {
            $button.css('cursor', 'default'); // 禁用拖拽
            $button.off('mousedown'); // 移除 mousedown 事件
            return;
        }
        $button.css('cursor', 'move'); // 启用拖拽
        var $content = $(content);
        var isDragging = false; // 用于标记是否正在拖拽

        $button.one('mousedown', function(event) {
        //$button.mousedown(function(event) {
            if (draggableType || isDragging) return; // 如果是只能拖拽一次且已经拖拽过，则不执行
            isDragging = true;
           // console.log('Mousedown event triggered');
            // 创建拖拽虚影
            if (draggingShadow) {
                draggingShadow.remove();
            }
            draggingShadow = $('<div>').addClass('dragging-shadow').css({
                width: $button.width(),
                height: $button.height(),
                top: event.clientY,
                left: event.clientX
            }).text($button.text()); // 使用按钮文本作为虚影内容
            $('body').append(draggingShadow);

            // 开始拖拽
            $(document).on('mousemove.drag', function(event) {
                draggingShadow.css({
                    left: event.clientX - $button.width() / 2,
                    top: event.clientY - $button.height() / 2
                });

                var shadowRect = draggingShadow[0].getBoundingClientRect();
                var contentRect = $content[0].getBoundingClientRect();

                if (
                    shadowRect.left > contentRect.left &&
                    shadowRect.right < contentRect.right &&
                    shadowRect.top > contentRect.top &&
                    shadowRect.bottom < contentRect.bottom
                ) {
                    draggingShadow.addClass('inside-content');
                   // console.log('isDragginginga', isDragging)
                } else {
                    draggingShadow.removeClass('inside-content');
                   // console.log('isDraggingingr', isDragging)
                }
            });

            // 结束拖拽
            $(document).one('mouseup.drag', function() {
                if (draggingShadow != null && draggingShadow.hasClass('inside-content')) {
                    // 显示自定义内容
                    if (classify == '.addItem') {
                        addItem($button);
                    } else {
                        addItem2($button);
                    }
                    // $content.append($button.data('content'));
                    $('.draggable-s').find('.del').click(function() {
                        delItem($(this));
                    });
                } else {
                    // 左边的拖拽事件绑定
                    moveButtion(classify, $button, content);
                }

                // 移除拖拽虚影
                $('.dragging-shadow').remove();

                draggingShadow = null;

                // 重置拖拽状态
                isDragging = false;

                // 移除事件监听器
                $(document).off('.drag');

                // 标记为已拖拽
                if (draggableType) {
                    $button.css('cursor', 'default'); // 禁用拖拽
                    $button.off('mousedown'); // 移除 mousedown 事件
                }
                resizeTemp();
            });
        });
    }

    // 提交或获取信息的处理
    function makeFinalMsg(){
        // 需要再次的初始化获取下自定义的输入
        $('.draggable').each(function() {
            getPosition($(this), '.draggable');
        });
        $('.draggable2').each(function() {
            getPosition($(this), '.draggable2');
        });
        $('.draggable-s').each(function() {
            getPosition($(this), '.draggable-s');
        });

        // console.log(sameArea);
        // console.log(itemPositionArr);
        var width = $("#industry_template .container-industry-template").width(); // 记录此时的内容的宽度，因为最终展示会是百分比计算
        submitData.width = width;
        // 这里ping上下报告的名称
        itemPositionArr.push(
            {
                id: 'item_title',
                'name': 'item_title',
                'position': '400,10',
                type:1,
                value: {
                    val: $('#report_title').val(),
                    en_val: $('#report_title2').val(),
                },
            }
        );
        // 还要ping上基本信息的位置
        itemPositionArr.push(
            {
                id: 'item_basic_title',
                'name': 'item_basic_title',
                'position': '0,100',
                type:1,
                value: {
                    val: LANG.UI_PLATFORM_INDUSTRY_BASIC_INFO,
                    en_val: 'BASIC INFORMATION',
                }
            }
        );
        // 还要ping上基本信息的位置
        /*itemPositionArr.push(
            {
                id: 'item_basic_info',
                'name': 'item_basic_info',
                'position': $('#basic_info_line').position().top + ',0',
                type:1,
                value: {
                    val: '基本信息line',
                    en_val: 'BASIC INFO LINE',
                }
            }
        );*/
        submitData.item = itemPositionArr;
        if (sameArea.length > 0) {
            showSameTip();
            return false;
        }
        submitData.item_plan =  submitData.item_result = '';
        if (itemPlanEditor) {
            submitData.item_plan = itemPlanEditor.getContent(); // 方案的内容
        }

        if (itemResultEditor) {
            submitData.item_result = itemResultEditor.getContent(); // 结论的内容
        }
        submitData.approval_uuid = $('#approval_uuid').val(); // 审批流
        submitData.plan_uuid = $('#plan_uuid').val(); // 方案模板
        return true;
    }

    // 获取所有的自定义的内容
    function checkDiyValue(){
        let check = true;
        let msg = '基本信息的标题不能为空';
        // 判断模板的中英文标题不能同时为空
        if ($('#report_title').val() == '' && $('#report_title2').val() == '') {
            UIToastr.showWarning('模板配置', '模板标题不能为空');
            return false;
        }
        $('.draggable').each(function() {
            var val = $(this).find('input[name="item_field_name"]').val();
            var en_val = 1;
            if (isEnCn == 1) {
                en_val = $(this).find('input[name="item_field_name_en"]').val();
            }
            $(this).css('border', 'none');
            if (val == '' && en_val == '') {
                check = false;
                $(this).css('border', '1px solid red');
                msg = '标题不能为空';
            }
        });
        !check && UIToastr.showWarning('模板配置', msg);
        return check;
    }

    // 重复初始化未刷新窗口，重置默认的值
    function initDefault() {
        submitData = {}; // 提交的数据汇总
        oldData = {}; // 修改获取到的数据
        isLoading = false; // 标识是否还在加载数据
        // 记录下所有的单选组件的默认html内容
        itemFieldArr = {};
        approvalList = []; // 审批流列表
        planList = []; // 方案列表
        draggingShadow = null; // 左边的按钮拖拽
        isExpend = true; // 左边的按钮是否展开
        sameArea = []; // 记录有折叠的元素集合
        itemPositionArr = [];   // 记录每个元素的位置
    }
    return {
        //main function to initiate the module
        init: function (options = {}) {
            if (options.uuid != undefined) {
                uuid = options.uuid
            } else {
                uuid = $('#template_uuid').val();
            }
            if (options.source != undefined) {
                source = options.source; // 创建任务配置模板
            } else {
                source = 1; // 模板编辑
            }

            if (options.job_uuid != undefined) {
                jobUuid = options.job_uuid;
            } else {
                jobUuid = '';
            }

            if (itemPlanEditor) {
                itemPlanEditor.destroy();
                itemPlanEditor = null;
            }

            if (itemResultEditor) {
                itemResultEditor.destroy();
                itemResultEditor = null;
            }

            if (options.init != undefined) {
                isInit = true;
            } else {
                isInit = false;
            }

            // 这里来设置是否中英文对照的展示
            if (CONF.ENTERPRISE == 'enterprise_en') {
                // 英文版本
                isEnCn = 0; // 模拟设置为开启中英文对照
            } else {
                // 中文版本
                isEnCn = 1;
            }
            initDefault();
            $('#is_en_cn').val(isEnCn);
            firstItemTop = 208;
            gridSize = 58;
            if (isEnCn !== 1) {
                // 不是中英文对照
                $('.item_en').remove();
            }
            initApproval(); //初始化当前创建的审批流
            initPlanList(); //初始化当前创建的方案模板
            // 获取默认的html
            getDefaultItem();

            if (uuid != '') {
                // 初始化详情
                isLoading = true;
                initView(uuid);
            } else {
                isLoading = false;
                // 初始化添加
                initAdd();
            }

            // 组件按钮删除事件初始化
            initDelItem();

            initListener();
        },

        // 对外提供获取配置信息
        getInfo: function (){
            if (makeFinalMsg()) {
                return submitData;
            }
            return [];
        }
    };
}();
