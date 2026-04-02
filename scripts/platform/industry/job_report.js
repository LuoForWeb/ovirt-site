var JobReportDetail = function () {
    var uuid = '';
    var agent_uuid = '';
    var status = ''; // 报告状态
    var submitData = {}; // 提交的数据汇总
    var oldData = {}; // 报告详情数据
    var oldTitleData = {}; // 报告详情名称数据
    var itemPlanEditor,itemResultEditor;
    // 记录下所有的单选组件的默认html内容
    var itemFieldArr = {};
    var logo = './img/platform/logo.png';
    let initScreenTag = 0;// 标识下当前的截图 0 是完整的，1只有生产 2只有验证
    var itemPositionArr = [];   // 记录可以更改内容的每个元素的位置
    var initDocumentTable = false,initScreenTable = false;
    var negativeArr = [];// 所有定位有向上偏移的
    var isEnCn = 1; // 是否需要中英对照，默认开启
    var pre = 0; // 是否预览
    var pre_loading = true; // 标识预览的是否加载完毕
    let source = 1; // 来源 1是报告列表， 2是任务详情
    let _taskStatus = 0; // 任务状态标记，从任务详情打开会传递
    let _history = 0; // 是否是从历史任务过来

    // 获取默认的一些内容
    function getDefaultItem(){
        itemFieldArr = {
            'item_document': meakeDocumentBlock(LANG.UI_PLATFORM_INDUSTRY_FILE_COMPARSION, 'FILE COMPARSION'),
            'item_make_time': makeBottomBlock('item_make_time', 'item-make_time','item_make_time', LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE_TIME, 'Report Generation Time', ''),
            'item_download_time': makeBottomBlock('item_download_time','item-download_time', 'item_download_time', LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD_TIME, 'Report Download Time', ''),
            'item_operator_sign': makeBottomBlock('item_operator_sign', 'item-operator_sign','item_operator_sign', LANG.UI_PLATFORM_INDUSTRY_OPERATOR_SIGN, 'Operator(Signature)', ''),
            'item_auditor_sign': makeBottomBlock('item_auditor_sign', 'item-auditor_sign','item_auditor_sign', LANG.UI_PLATFORM_INDUSTRY_AUDITOR_SIGN,'Auditor(Signature)', ''),
            'item_operator_sign_sys': makeBottomBlock('item_operator_sign_sys', 'item-operator_sign_sys','item_operator_sign_sys', LANG.UI_PLATFORM_INDUSTRY_OPERATOR_SYS_SIGN, 'Operator(Sys_signature)', ''),
            'item_auditor_sign_sys': makeBottomBlock('item_auditor_sign_sys', 'item-auditor_sign_sys','item_auditor_sign_sys', LANG.UI_PLATFORM_INDUSTRY_AUDITOR_SYS_SIGN,'Auditor(Sys_signature)', ''),
        };
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
    function resizeReport() {
        var viewportHeight = $(window).height();
        var height = viewportHeight - 164;
        $('#container').parent().css('height', height);
        // console.log("新的视口高度: " + height + "px");
    }

    // 生成对应的文件比对
    function meakeDocumentBlock(name= LANG.UI_PLATFORM_INDUSTRY_FILE_COMPARSION, en_name='File comparison'){
        return `<pagebreak /><div class="pagebreak-marker"></div><div class="draggable item-2 item-document item-block-width1" id="item_document" data-field="item_document">
                    <div class="basic-info basic-title-plan document-title-l">
                        `+name+`
                        <span class="item-line item_en"></span>
                        <span class="item_en span-title-basic">`+en_name+`</span>
                    </div>
                    <div class="document-title-r">
                        `+LANG.UI_PLATFORM_INDUSTRY_MORE+` <i class="viconfont vicon-a-Leftzuo"></i>
                    </div>
                    <div class="document-title-desc">
                        
                    </div>
                    <div class="img-items open-result-title-div">
                       `+LANG.UI_PLATFORM_INDUSTRY_NO_RESULTS+`
                    </div>
                </div>`;
    }

    // 生成对应的截图按钮
    function makeScreenBtn(name= LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_COMPARSION, en_name='SCREENSHOT COMPARSION',value = []){
        var html = '<div class="open-result-title-div">';

        html += getScreenHtml(value);

        html += '</div><div class="screen-pic-btn"><span class="open-result-title-spanl">' +
            '<button type="button" id="productScreen" class="btn default" style="margin-right:12px;">'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_PRODUCT+'</button>\n' +
            '<button type="button" id="verifyScreen" class="btn default">'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_VERIFY+'</button>\n' +
            '<button type="button" id="allScreen" class="btn green-haze btn-confirm" style="\n' +
            '    margin-left: 12px;\n' +
            '">'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_ALL+'</button>' +
            '</span>' +
            '<span class="open-result-title-spanr">' +
            '<label class="label open_verify_machine"><span>'+LANG.UI_PLATFORM_INDUSTRY_OPEM_VERIFY_MACHINE+'</span><i class="viconfont vicon-a-Leftzuo"></i></label>' +
            '<label class="label open_prod_machine display-none"><span>'+LANG.UI_PLATFORM_INDUSTRY_OPEM_PRODUCT_MACHINE+'</span><i class="viconfont vicon-a-Leftzuo"></i></label>' +
            '</span></div>\n';

        return `<pagebreak /><div class="pagebreak-marker"></div><div class="draggable item-2 item-screen item-block-width1" id="item_screen" data-field="item_screen">
                    <div class="basic-info basic-title-plan">
                        `+name+`
                        <span class="item-line item_en"></span>
                        <span class="item_en span-title-basic">`+en_name+`</span>
                    </div>
                    <div class="img-items">
                        `+html+`
                    </div>
                </div>`;
    }

    // 生成对应审批流
    function makeApproval(name= LANG.UI_PLATFORM_INDUSTRY_APPROVAL, en_name='APPROVAL PROCESS',value = []){
        var middle = '';
        var j = value.length;
        var k = 0;
        for (var i in value) {
            k++;
            var change = '';
            var height = 32;
            // 如果有临时更改人
            if (value[i].change != undefined && value[i].change.length > 0) {
                for (var kk in value[i].change) {
                    change += '<div class="change">'+value[i].change[kk].time+value[i].change[kk].des+'</div>';
                }
                height = (value[i].change.length) * 24 + 32;
            }

            middle += '<li>';
            var style = '';
            if (value[i].status == 2) {
                style = 'style="background:#EC282C;"';
            }else if (value[i].status != 1) {
                style = 'style="background:#ccc;"';
            }
            middle +=  '<div class="circle" '+ style+'>&nbsp;</div>';
            if (k < j) {
                middle +=    '<div class="circle-line" style="height: '+height+'px;">&nbsp;</div>';
            }

            middle +=     '<div class="time">'+(value[i].create_time ? value[i].create_time : '--')+'</div>';

            middle +=    '<div><div><div class="name">'+value[i].position + ':' +value[i].user_name+'</div>' +
                '<div class="line">|</div>';
            var is = '<i class="viconfont vicon-a-dengdaidaishenpi"></i>';
            if (value[i].status == 1) {
                // 审核通过
                is = '<i class="viconfont vicon-tongguo" style="color: #2A87C8;"></i>';
            } else if (value[i].status == 2) {
                // 驳回
                is = '<i class="viconfont vicon-bohui" style="color: #EC282C;"></i>';
            }
            if (i == 0) {
                // 发起审批的不要图标
                is = '';
            }

            if (value[i].desc != '') {
                middle +=     '<div class="desc">' + is +value[i].desc+'</div>';
            }

            if (value[i].remark != '') {
                middle +=     '<div class="remark">'+value[i].remark+'</div>';
            }
            middle +=         '</div>';

            middle += change;
            middle +=         '</div></li>';
        }

        middle = '<ul id="approval_list_ul">' + middle + '</ul>';
        return `<div class="draggable item-1 item-approval item-block-width1 item-items" id="item_approval" data-field="item_approval">
                    <div class="basic-info basic-title-plan">
                        `+name+`
                        <span class="item-line item_en"></span>
                        <span class="item_en span-title-basic">`+en_name+`</span>
                    </div>
                    <div class="img-items">
                        `+middle+`
                    </div>
                </div>`;
    }

    // 生成logo
    function makeItemLogo(url = ''){
        var upload = '<img class="upload-img upload-flag" src="'+url+'">';

        return `<div class="draggable item-logo item-items" id="item_logo" data-field="item_logo">
                    <label class="upload-div">
                       `+upload+`
                    </label>
                </div>`;
    }

    // 生成二维码
    function makeItemQrcode(url) {
        return `<div class="draggable item-qrcode item-items" title="`+LANG.UI_PLATFORM_INDUSTRY_QRCODE_TIPS+`" id="item_qrcode" data-field="item_qrcode">
                    <label style="width: 50px;"><img src="`+url+`"></label>
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
            </div>`;
    }

    // 生成不可更改名称的字段
    function makeDiyVal(type = 1, id = 'item_host', item1 = 'item_host', name = LANG.UI_PLATFORM_INDUSTRY_HOST_NAME, en_name = 'Host Name', value = '', palceholder = LANG.UI_PLATFORM_INDUSTRY_EMPTY_TIPS){
        var val = '<input type="text" name="item_field_value" placeholder="'+palceholder+'" value="'+value+'" class="form-control input-inline input input-sm input-val">';
        if (type == 2) {
            // 不可编辑内容
            val = '<div class="span-val">'+value+'</div>';
        }
        return `<div class="draggable item-1 item-items item-fields" id="`+id+`" data-field="`+item1+`">
                    <label class="item-label-title">
                        <span class="span-title">
                            <span class="span-ch">`+name+` </span><span class="span-en item_en">`+en_name+`</span>
                         </span>
                        <input type="hidden" class="form-control input-inline input-ch" name="item_field_name" value="`+name+`">
                        <input type="hidden" class="form-control input-inline item_en input-en" name="item_field_name_en" value="`+en_name+`">
                        <br>
                       `+val+`
                    </label>
                </div>`;
    }

    // 生成时间
    function makeReportTime(id='item_verify_time', item1 = 'item_verify_time', name= LANG.UI_PLATFORM_INDUSTRY_MAKE_TIME, en_name='Generation Time',value= LANG.UI_PLATFORM_INDUSTRY_YMD){
        return `<div class="draggable item-items" id="`+id+`" data-field="`+item1+`">
                    <span class="">
                        <img src="/img/platform/industry/time.svg" class="make-time-icon" style="width:16px;">
                        <span class="make-time-ch">`+name+`</span>
                        <span class="make-time-en item_en">`+en_name+`</span>
                        <span class="item-line"></span>
                        <span class="make-time-val">`+value+`</span>
                    </span>
                </div>`;
    }

    // 编辑器生成
    function makeEditor(id= 'item_plan', item1 = 'item-plan', item2 = 'item_plan',name = LANG.UI_PLATFORM_INDUSTRY_PLAN, en_name='PLAN', value= LANG.UI_PLATFORM_INDUSTRY_PLAN_DES){
        return `<div class="draggable item-1 `+item1+` item-block-width1 item-items" id="`+id+`" data-field="`+item2+`">
                        <div class="basic-info basic-title-plan">
                            `+name+`
                            <span class="item-line item_en"></span>
                            <span class="item_en span-title-basic">`+en_name+`</span>
                            <button class="btn b-btn item-plan-right" title="`+LANG.UI_PLATFORM_INDUSTRY_EDITOR_TOOLS+`" type="button">
                                <i class="viconfont vicon-gongjuyincang"></i>
                            </button>
                        </div>
                        <textarea id="`+id+`_editor">`+value+`</textarea>
                    </div>`;
    }

    // 标题加图片生成
    function makeTitlePic(type = 1,id='item_approval', item1 = 'item-approval', item2 = 'item_approval', name= LANG.UI_PLATFORM_INDUSTRY_APPROVAL,en_name='APPROVAL PROCESS',value='./img/platform/industry/approval.png'){
        // type 2表示中间验证的元素的生成的
        var drag = 'draggable';
        if (type == 2) {
            drag = 'draggable2';
        }
        return `<div class="`+drag+` item-`+type+` `+item1+` item-block-width1 item-items" id="`+id+`" data-field="`+item2+`">
                    <div class="basic-info basic-title-plan">
                        `+name+`
                        <span class="item-line item_en"></span>
                        <span class="item_en span-title-basic">`+en_name+`</span>
                    </div>
                    <div class="img-items">
                        <img src="`+value+`">
                    </div>
                </div>`;
    }

    // 验证信息2块的生成
    function makeVerfiyBlock(id='item_module', item2='item_module', name= LANG.UI_PLATFORM_INDUSTRY_HOST_NAMES,en_name='Host Name',value=''){
        return `<div class="draggable2 item-2 item-items item-verify-items" id="`+id+`" data-field="`+item2+`">
                    <span class="item-verify-items-block">
                        <span class="item-verify-ch">`+name+` </span><span class="item_en item-verify-en"> `+en_name+`</span>
                        <div class="item-verify-val">`+value+`</div>
                    </span>
                </div>`;
    }

    // 验证信息的三块生成
    function makeVerfiy2Block(id='item_integrality', item2='item_integrality', name= LANG.UI_PLATFORM_INDUSTRY_OPEN_VERIFY,en_name='Boot Verification Result',value= LANG.UI_PLATFORM_INDUSTRY_RESULTS){
        return `<div class="draggable2 item-2 item-items item-verify2-items" id="`+id+`" data-field="`+item2+`">
                    <span class="item-verify2-items-block">
                        <span>`+name+` </span><div class="item_en item-verify-en"> `+en_name+`</div>
                        <div class="item-verify-val">`+value+`</div>
                    </span>
                </div>`;
    }

    // 底部6个签字等元素生成
    function makeBottomBlock(id='item_make_time', item1='item-make_time', item2='item_make_time',name= LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE_TIME,en_name='Report Generation Time',value=LANG.UI_PLATFORM_INDUSTRY_YMD){
        var middle = `<span class="item-verify-bottom-ch">`+name+`：</span>
                        <span class="item-verify-bottom-val `+item2+`">`+value+`</span>
                         <div class="item_en item-verify-bottom-en">`+en_name+`</div>`;
        if (id == 'item_auditor_sign_sys') {
            middle = `<div class="item-verify-bottom-ch">`+name+`：
                         <div class="item_en item-verify-bottom-en">`+en_name+`</div>
                    </div>
                    <div class="item-verify-bottom-val `+item2+`">`+value+`</div>`;
        }

        return  `<div class="draggable item-1 item-items `+item1+` item-bottom-items" id="`+id+`" data-field="`+item2+`">
                    <div class="margin-r-10">
                        `+middle+`
                    </div>
                </div>`;
    }

    // 渲染截图的html
    function getScreenHtml(value, type = 0){
        var stop = '<div class="screen-item-pic">';
        var stimel = '<div class="shadow-box display-none">' +
            '<label class="labels"><i class="viconfont vicon-a-Zoom-infangda" style="\n' +
            '    margin-right: 4px;\n' +
            '"></i>'+LANG.UI_PLATFORM_INDUSTRY_LOOK_MAX_PIC+'</label></div>' +
            '<div class="screen-time">';
        var stimer = '<i class="viconfont vicon-zujianshanchu screen-icon"></i></div>';
        var sproductl = '<div class="product"><img src="';
        var sproductr = '"><div class="titles">'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_PRODUCTS+'<i class="viconfont vicon-zujianshanchu screen-icon"></i></div></div>';
        var sverifyl = '<div class="verify"><img src="';
        var sverifyr = '"> <div class="titles">'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_VERIFYS+'<i class="viconfont vicon-zujianshanchu screen-icon"></i></div></div>';
        var sdescl = '<div class="screen-desc">';
        var sdescl2 = '<textarea cols="50" rows="2" placeholder="'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_TIPS+'">';
        var sdescr = '</textarea></div></div>';
        var html = '';
        // 这里进行数据渲染
        if (type == 1) {
            // 表示ping上生产截图
            html = sproductl + value + sproductr;
        } else if (type == 2) {
            // 表示ping上验证截图
            html = sverifyl + value + sverifyr;
        } else {
            var numTime = '<span class="num-time">';
            // 渲染列表
            var k = $('#industry_report .screen-item-pic').length;
            for (var j in value) {
                k = k + 1;
                var input = '<span class="screen-time-value">'+ LANG.UI_EMERGENCY_RECOVERY_THE + k + LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_TITLE +'</span>';
                html += stop + stimel + numTime + '</span>' + input + stimer;
                if (value[j].product != '') {
                    html += sproductl + value[j].product + sproductr;
                }
                if (value[j].verify != '') {
                    html += sverifyl + value[j].verify + sverifyr;
                }
                var radio = getUuid();
                var result = '<div class="result">'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT+'：<label><input type="radio" name="'+radio+'" value="1"><span>'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME+'</span></label>' +
                    '<label><input type="radio" name="'+radio+'" value="2"><span>'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME+'</span></label></div>';
                var results = parseInt(value[j].result);
                if (results > 0) {
                    var check1 = results == 1 ? 'checked': '';
                    var check2 = results == 2 ? 'checked': '';

                    result = '<div class="result">'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT+'：<label><input type="radio" name="'+radio+'" '+ check1 +' value="1"><span>'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME+'</span></label>' +
                        '<label><input type="radio" name="'+radio+'" '+ check2 +' value="2"><span>'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME+'</span></label></div>'
                }
                html += sdescl + result + sdescl2 + value[j].remark + sdescr;
            }
        }

        return html;
    }

    // 预览事件
    function preListener() {
        var previewContainer = createHtml();
        previewContainer.find('#item_auditor_sign_sys').css({
            'height': '45px'
        })
        previewContainer.find('.item-editor_pre').css({
            'padding-top': '20px',
        })
        previewContainer.find('.item-qrcode').css({
            'margin-left': 'calc(100% - 45px)',
            'left': 0,
        });
        previewContainer.find('.title-line').css({
            'width': 'calc(100% - 44px)',
            'left': '22px'
        })
        previewContainer.find('#item_verify_time').css({
            'margin-left': 'calc(100% - 393px)',
        })
        previewContainer.find('.item-block-width1').css({
            'margin': 0,
            'width': '100%'
        })
        previewContainer.find('.item-editor_pre').css({
            'width': '100%'
        })
        previewContainer.find('.basic-title-plan').css({
            'margin-left': 0
        })
        // 每个大标题都是距离上面 40px
        var arr = ['item_plan', 'item_open', 'item_document', 'item_screen', 'item_approval', 'item_result'];
        for(var j in arr) {
            previewContainer.find('#'+arr[j]).css({
                'margin-top': '40px'
            })
        }
        $('#pre-report').html(previewContainer.html());
        if (pre == 100) {
            // 只预览部分的结果  开机验证 文件比对 截屏比对 三项
            $('#pre-report').find('.draggable').hide();
            $('#pre-report').find('.draggable2').hide();
            $('#pre-report').find('.title-line').hide();
            $('#pre-report').find('.basic-title2').hide();
            $('#pre-report').find('.basic-title1s').hide();
            $('#pre-report').find('.title-line').hide();

            $('#pre-report').find('.report-title').hide();
            $('#pre-report').find('#item_open').show();
            $('#pre-report').find('#item_document').show();
            $('#pre-report').find('#item_screen').show();
        } else {
           /* $('#pre-report').append('<div id="page-guide"></div>');
            // 假设你的内容在 #pre-report 中
            const contentEl = document.getElementById('pre-report');
            const guideEl = document.getElementById('page-guide'); // 你指定的目标
            showPageBreakPreview(contentEl, guideEl, {
                marginTop: 56.7,
                marginBottom: 56.7,
                pageWidth: 680.4, // 180mm
                color: 'rgba(255, 0, 0, 0.4)',
                pageNumber: true
            });*/
            // 去除所有的 <pagebreak>
            previewContainer.find('pagebreak').each(function () {
                $(this).contents().unwrap();
            });
            renderPagedPreview(previewContainer.find('#container').html(), document.getElementById('pre-report'));
        }

        // 更多文件比对点击事件
        $('#pre-report').find('.document-title-r').off('click').on('click', function() {
            lookMoreDocument();
        })
        // 显示抽屉
        $('#drawer-pre').drawer('show');
    }

    /**
     * 将 HTML 内容按 A4 页面分割并渲染（支持 <> 强制分页）
     * @param {HTMLElement|string} content - 原始内容（DOM 元素 或 HTML 字符串）
     * @param {HTMLElement} targetContainer - 渲染目标容器
     */
    function renderPagedPreview(content, targetContainer) {
        // 清空目标
        targetContainer.innerHTML = '';

        // 统一处理为 DOM
        let container = document.createElement('div');
        if (typeof content === 'string') {
            container.innerHTML = content.trim();
        } else {
            container = content.cloneNode(true);
        }

        // 创建测量容器（模拟 A4 页面样式）
        const measureContainer = document.createElement('div');
        measureContainer.style.cssText = `
        position: absolute;
        top: -9999px;
        left: -9999px;
        width: 180mm;
        padding: 20mm;
        margin: 0 auto;
        font-family: Arial, sans-serif;
        font-size: 12px;
        line-height: 1.5;
        box-sizing: border-box;
        visibility: hidden;
        overflow: hidden;`;

        // 🔥 关键：复制你表格的样式，确保渲染一致
        /*measureContainer.style.lineHeight = '1.4';
        measureContainer.style.padding = '0 10px';*/

        document.body.appendChild(measureContainer);

        const pages = [];
        let currentPage = document.createElement('div'); // 当前页内容容器
       // console.log('all node container', container);

        // 遍历所有子节点
        Array.from(container.childNodes).forEach(node => {
            if (node.nodeType === Node.ELEMENT_NODE && node.classList.contains('pagebreak-marker')) {
                // 遇到分页符：保存当前页，开启新页
                if (currentPage.children.length > 0 || currentPage.textContent.trim()) {
                    pages.push(currentPage);
                }
                currentPage = document.createElement('div');
                return;
            }
           // console.log('node.id', node.id);
            // 处理下文件比对的
            if (node.nodeType === Node.ELEMENT_NODE && node.id === 'item_document') {
                // 获取标题元素（仅第一页需要）
                const screen_title = node.querySelector('.document-title-l');
                const screen_title2 = node.querySelector('.document-title-desc');
                // 把查看更多复制下来
                const screen_title3 = node.querySelector('.document-title-r');

                // 获取表格中的所有行（排除可能的空行或非数据行，这里只取 tbody 下的 tr）
                const tbody = node.querySelector('tbody');
                if (tbody === null) {
                    return;
                }
                const allRows = Array.from(tbody.querySelectorAll('tr'));
                if (allRows.length === 0) return;

                // 用于复用的 table 结构（带 thead）
                const createTableWithHeader = () => {
                    const table = document.createElement('table');
                    table.style.borderCollapse = 'collapse';
                    table.style.borderSpacing = '0';

                    // 复用原始 thead
                    const thead = node.querySelector('thead');
                    if (thead) {
                        table.appendChild(thead.cloneNode(true));
                    }

                    const tbody = document.createElement('tbody');
                    table.appendChild(tbody);
                    return table;
                };

                // 分页处理
                const PAGE_MAX_HEIGHT = 800;
                const AVAILABLE_HEIGHT = PAGE_MAX_HEIGHT - 40; // 预留边距

                let currentPageIndex = 0;
                let currentHeight = 0;
                let currentPage = null;
                let currentTableBody = null;
                let currentPageContainer = null;

                const startNewPage = () => {
                    currentPage = document.createElement('div');
                    currentPage.className = 'draggable item-2 item-document item-block-width1';
                    currentPage.style.top = '0px';

                    if (currentPageIndex === 0 && screen_title) {
                        currentPage.appendChild(screen_title.cloneNode(true));
                        screen_title3 != undefined && currentPage.appendChild(screen_title3.cloneNode(true));
                        currentPage.appendChild(screen_title2.cloneNode(true));
                    }

                    const screen_div = document.createElement('div');
                    screen_div.className = 'img-items open-result-title-div';
                    currentPage.appendChild(screen_div);

                    const table = createTableWithHeader();
                    currentTableBody = table.querySelector('tbody');
                    screen_div.appendChild(table);

                    currentPageContainer = document.createElement('div');
                    currentPageContainer.appendChild(currentPage);
                    pages.push(currentPageContainer);

                    currentPageIndex++;
                    currentHeight = 0;
                };

                startNewPage();

                allRows.forEach(row => {
                    const clonedRow = row.cloneNode(true);

                    // 使用隐藏容器测量高度
                    measureContainer.innerHTML = '';
                    measureContainer.appendChild(clonedRow);
                    const rowHeight = clonedRow.offsetHeight;

                    const style = window.getComputedStyle(clonedRow);
                    const marginTop = parseFloat(style.marginTop) || 0;
                    const marginBottom = parseFloat(style.marginBottom) || 0;
                    const totalHeight = rowHeight + marginTop + marginBottom;

                    if (currentHeight + totalHeight > AVAILABLE_HEIGHT) {
                        startNewPage();
                    }

                    currentTableBody.appendChild(clonedRow);
                    currentHeight += totalHeight;
                });

                currentPage = document.createElement('div');
                return;
            }

            if (node.nodeType === Node.ELEMENT_NODE && node.id === 'item_screen') {
                // 创建 DOM 元素
                const screen_title = node.querySelector('.basic-info');

                const screen_div = document.createElement('div');
                screen_div.className = 'img-items';
                const openDiv = document.createElement('div');
                openDiv.className = 'open-result-title-div';
                screen_div.appendChild(openDiv);

                // 获取所有 screen-item-pic 分组
                const screenshotGroups = node.querySelectorAll('.screen-item-pic');

                screenshotGroups.forEach((group, index) => {
                    const currentPage = document.createElement('div'); // 每页一个容器
                    if (index === 0 && screen_title) {
                        currentPage.appendChild(screen_title.cloneNode(true)); // clone 内容
                    }

                    const newScreenDiv = screen_div.cloneNode(true); // clone 包含子节点
                    // 但注意：此时 open-result-title-div 是空的，我们需要把当前 group 放进去
                    const openResultDiv = newScreenDiv.querySelector('.open-result-title-div');

                    // 获取所有 img 元素
                    //const images = group.querySelectorAll('img');

                    /*// 遍历每个 img，设置高度
                    images.forEach(img => {
                        img.style.height = '350px';
                    });*/

                    openResultDiv.appendChild(group.cloneNode(true)); // 把当前这组截屏插入

                    currentPage.appendChild(newScreenDiv);
                    pages.push(currentPage);
                });
                currentPage = document.createElement('div');
                return;
            }

            const clone = node.cloneNode(true);

            // 尝试添加到当前页
            const tempWrapper = document.createElement('div');
            tempWrapper.appendChild(clone);
            measureContainer.appendChild(tempWrapper);

            const isOverflow = measureContainer.scrollHeight > 1123 - 2 * 56.7; // A4 可用高度
            if (isOverflow) {
                // 放不下，先保存当前页
                if (currentPage.children.length > 0 || currentPage.textContent.trim()) {
                    pages.push(currentPage);
                }
                // 开启新页，并放入此节点
                currentPage = document.createElement('div');
                currentPage.appendChild(clone);
                // 重置测量容器
                measureContainer.innerHTML = '';
                measureContainer.appendChild(tempWrapper);
            } else {
                // 可以放下
                currentPage.appendChild(clone);
            }

            // 清理临时 wrapper
            if (measureContainer.firstChild) {
                measureContainer.removeChild(measureContainer.firstChild);
            }
        });

        // 添加最后一页
        if (currentPage.children.length > 0 || currentPage.textContent.trim()) {
            pages.push(currentPage);
        }

        // 移除测量容器
        document.body.removeChild(measureContainer);
        var total = pages.length;
        // 渲染每一页
        pages.forEach((pageContent, index) => {
            const page = document.createElement('div');
            page.className = 'page_report';
            page.style.cssText = ``;

            // 内容区
            const contentWrap = document.createElement('div');
            contentWrap.className = 'page-content_report';
            contentWrap.style.cssText = 'width: 100%; min-height: 100%;';
            while (pageContent.firstChild) {
                contentWrap.appendChild(pageContent.firstChild);
            }
            //console.log('index', index)
            //console.log('contentWrap', contentWrap)
            page.appendChild(contentWrap);

            // 页码
            const pageNumber = document.createElement('div');
            pageNumber.className = 'page-number_report';
            pageNumber.style.cssText = ``;
            var now = index + 1;

            pageNumber.textContent = '第 '+now+' 页/共'+total+'页';
            page.appendChild(pageNumber);

            targetContainer.appendChild(page);
        });
    }

    /**
     * 在指定容器上叠加分页预览线
     * @param {HTMLElement} contentElement - 内容容器（已渲染）
     * @param {HTMLElement} guideContainer - 分页线渲染的目标容器（通常与 contentElement 同父容器）
     * @param {Object} options - 配置项
     */
    function showPageBreakPreview(contentElement, guideContainer, options = {}) {
        const config = {
            pageHeight: 1250,           // A4 总高度 px
            marginTop: 0,            // 上边距
            marginBottom: 0,         // 下边距
            pageWidth: 180 * 3.78,      // 180mm ≈ 680.4px
            gap: 10,                    // 分页视觉间隔
            color: 'rgba(0, 120, 215, 0.3)',
            lineWidth: 2,
            pageNumber: true,           // 是否显示页码
            ...options
        };

        const usablePageHeight = config.pageHeight; // ≈1010px

        // 清空目标容器
        guideContainer.innerHTML = '';
        // 等下一帧
        requestAnimationFrame(() => {
            // 设置 guideContainer 样式（确保它能正确叠加）
            Object.assign(guideContainer.style, {
                position: 'absolute',
                top: '0',
                left: '0',
                width: '100%',
                height: `${contentElement.scrollHeight}px`,
                pointerEvents: 'none',
                zIndex: '9999',
                transform: 'translateZ(0)', // 启用硬件加速
            });

            // 获取 contentElement 相对于 guideContainer 的偏移
            const contentRect = contentElement.getBoundingClientRect();
            const containerRect = guideContainer.parentElement.getBoundingClientRect();

            const offsetX = contentRect.left - containerRect.left;
            const offsetY = contentRect.top - containerRect.top;

            // 计算页数
            const contentHeight = contentElement.scrollHeight;
            const pageCount = Math.ceil(contentHeight / usablePageHeight);

            // 生成每一页的分页线
            for (let i = 1; i <= pageCount; i++) {
                const pageBreakTop = i * usablePageHeight; // 相对于 contentElement 顶部的偏移

                const line = document.createElement('div');
                line.className = 'page-break-guide';
                Object.assign(line.style, {
                    position: 'absolute',
                    top: `${pageBreakTop - config.marginBottom + offsetY}px`, // 在上一页底部边距处
                    left: `22px`,
                    width: `calc(100% - 44px)`,
                    height: `30px`,
                    borderBottom: `${config.lineWidth}px dashed ${config.color}`,
                    boxSizing: 'border-box',
                    pointerEvents: 'none',
                    opacity: '0.7',
                });

                // 可选：添加页码标签
                if (config.pageNumber) {
                    const label = document.createElement('span');
                    label.textContent = `第 ${i} 页/ 共 ${pageCount} 页`;
                    Object.assign(label.style, {
                        position: 'absolute',
                        top: '-20px',
                        right: '10px',
                        fontSize: '12px',
                        color: config.color,
                        background: 'white',
                        padding: '2px 6px',
                        borderRadius: '3px',
                        border: `1px solid ${config.color}`,
                        whiteSpace: 'nowrap',
                    });
                    line.appendChild(label);
                }

                guideContainer.appendChild(line);
            }
        });

    }

    // 获取报告的html
    function createHtml(witdh = 1200){
        // 创建一个新的 div 来存放预览内容
        var previewContainer = $('<div></div>');
        // 获取页面所有的css和html，然后打开dialog
        previewContainer.html($('#parent_container').html());
        var title = '<div class="input-title-ch">'+ $('#report_title').val() +'</div>';
        if (isEnCn == 1) {
            title += '<span class="input-title-en" style="display: block;margin-top: -4px;">'+$('#report_title2').val()+'</span>';
        }
        previewContainer.find('.report-title').html(title);
        previewContainer.find('#container').attr('class', '');

        // 替换下两个可能存在的编辑器的内容
        // 方案编辑器处理
        if (itemPlanEditor) {
            var item_plan = itemPlanEditor.getContent(); // 结论的内容
            if (pre == 1) {
                // 是直接预览的，那么取接口的数据
                item_plan = oldData.item_plan;
            }
            var editor_title = '<div class="basic-info basic-title-plan">' + $('#item_plan').find('.basic-title-plan').html() + '</div>';
            item_plan = '<div class="item-editor_pre"><div style="margin:0 20px;">' + item_plan + '</div></div>';
            previewContainer.find('#item_plan').html(editor_title + item_plan);
            previewContainer.find('#item_plan').css({
                'height': 'auto',
            });
        }
        // 结论编辑器处理
        if (itemResultEditor) {
            var item_result = itemResultEditor.getContent(); // 结论的内容
            if (pre == 1) {
                // 是直接预览的，那么取接口的数据
                item_result = oldData.item_result;
            }
            var editor_title = '<div class="basic-info basic-title-plan">' + $('#item_result').find('.basic-title-plan').html() + '</div>';
            item_result = '<div class="item-editor_pre"><div style="margin:0 20px;">' + item_result + '</div></div>';
            previewContainer.find('#item_result').html(editor_title + item_result);

            previewContainer.find('#item_result').css({
                'height': 'auto',
            });
        }
        // 编辑器工具栏按钮处理
        previewContainer.find('.item-plan-right').remove();

        // 截屏按钮处理
        previewContainer.find('.screen-pic-btn').remove();
        previewContainer.find('.vicon-zujianshanchu').remove();
        previewContainer.find('.open-result-title-spanr').remove();
        // 截屏描述处理
        previewContainer.find('.screen-desc').each(function (index, element){
            var des = LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_UNKNOWN;
            var result =  $('#container .screen-desc').eq(index).find('input[type="radio"]:checked').val();
            if (result == 1) {
                des = LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME;
            } else if (result == 2) {
                des = LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME;
            }
            var val = $('#container .screen-desc').eq(index).find('textarea').val();

            $(this).html(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT +'：' + des + '</br>' +val);
        })
        previewContainer.find('#item_screen .screen-item-pic').after('<pagebreak><div class="pagebreak-marker"></div>');
        // 截屏标题处理
        previewContainer.find('.screen-item-pic .screen-time').each(function (index, element){
            var val = $('#container #item_screen .screen-time').eq(index).find('.screen-time-value').html();
            $(this).html(val);
        })

        previewContainer.find('#item_approval ul').css({
            'background': '#EBEBEB',
            'list-style-type': 'disc',
        })

        previewContainer.find('#item_approval ul li').css({
            'line-height': '32px',
        })

        previewContainer.find('#item_approval ul li .remark').css({
            'background': 'none',
            'color': '#333333',
        })

        previewContainer.find('#item_approval ul li .time').css({
            'font-size': '14px',
            'width': '138px',
        })

        // 去除审批流的虚线
        previewContainer.find('#item_approval .circle').remove();
        previewContainer.find('#item_approval .circle-line').remove();
        previewContainer.find('#item_approval .line').remove();

        previewContainer.find('.item-verify-items').css({
            'background': '#EBEBEB',
        })
        // 所有的input转为html，因为mpdf对input不友好
        previewContainer.find('input').each(function (){
            var name = $(this).attr('name');
            var id = $(this).parent().attr('id');
            if (id == undefined) {
                id = $(this).parent().parent().attr('id');
            }
            if (id == undefined) {
                id = $(this).parent().parent().parent().attr('id');
            }
            if ($('#container').find('#'+id +' input[name='+name+']').attr('type') == 'hidden') {
                previewContainer.find('#'+id +' input[name='+name+']').replaceWith('');
                return;
            }
            var val = $('#container').find('#'+id +' input[name='+name+']').val();
            if (val == '') {
                val = '\u00A0';
            }
            // 替换input的内容为html
            var newDiv;
            if ($.inArray(name,[ 'item_field_value', 'item_field_value_en']) !== -1) {
                var clasa = '';
                if ($(this).hasClass('color-yellow')) {
                    clasa = 'color-yellow';
                }
                newDiv = '<div class="item-label'+clasa+'" style="\n' +
                    '    height: 34px;margin-top: 8px;\n' +
                    '    display: inline-block;\n' +
                    '    text-align: unset;\n' +
                    '">'+val+'</div>';
            } else {
                var vlass = 'span-ch';
                if (name == 'item_field_name_en') {
                    // 英文
                    vlass = 'span-en item_en'
                }
                newDiv = '<span class="'+ vlass +'">'+val+' </span>\n';
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

            var val = $('#container').find('#'+id +' textarea[name='+name+']').val();
            if (val == '') {
                val = '\u00A0';
            }
            // 替换input的内容为html
            var newDiv;
            if ($.inArray(name,['item_field_value', 'item_field_value_en']) !== -1) {
                var clasa = '';
                if ($(this).hasClass('color-yellow')) {
                    clasa = 'color-yellow';
                }
                newDiv = '<div class="item-label'+clasa+'" style="\n' +
                    '    width:850px;height:34px;margin-top:8px;\n' +
                    '    display:inline-block;\n' +
                    '    text-align:unset;\n' +
                    '">'+val+'</div>';
            }

            // 替换当前的input元素
            previewContainer.find('#'+id +' textarea[name='+name+']').replaceWith(newDiv);
        })

        var newWidth = witdh / 2 * 0.8 - 25;
        //previewContainer.find('.item-label').attr('style', 'width: ' + newWidth + 'px !important');
        previewContainer.find('.item-label').css({
            'font-size': '16px',
            'color': '#333333'
        })
        previewContainer.find('.item-label-title .span-val').css({
            'font-size': '16px',
            'color': '#333333'
        })

        // 动态调整表格
        // 动态调整字段的宽度
        previewContainer.find(' #container').css({
            'overflow-y': 'hidden',
            'padding':0
        });

        previewContainer.find('.report-title').css({
            'width': '550px'
        });

        previewContainer.find('#item_result').css({
            'color': '#666666'
        });
        previewContainer.find('#item_plan').css({
            'color': '#666666'
        });
        previewContainer.find('.span-en').css({
            'color': '#666666'
        });
        previewContainer.find('.item-field').css({
            'line-height': '34px'
        });
        previewContainer.find('.item-host').css({
            'line-height': '34px'
        });
        previewContainer.find('.item-ip').css({
            'line-height': '34px'
        });
        previewContainer.find('#item_results .span-val').css({
            'margin-top': '4px'
        })

        previewContainer.find('.item-operator').css({
            'line-height': '34px'
        });

        /* new */
        previewContainer.find('.item-qrcode').css({
            'margin-left': 'calc(100% - 72px)',
            'left': 0,
        });
        previewContainer.find('.title-line').css({
            'width': 'calc(100% - 20px)',
            'left': 0,
        });
        previewContainer.find('.basic-title1s').css({
            'margin-top': '104px',
        });
        previewContainer.find('.basic-title-line').css({
            'width': 'calc(100% - 20px)',
        });
        previewContainer.find('#item_verify_time').css({
            'margin-left': 'calc(100% - 412px)',
        });
        previewContainer.find('.item-editor_pre').css({
            'width': 'calc(100% - 10px)',
        });
        previewContainer.find('.item-block-width1').css({
            'width': 'calc(100% - 20px)',
        });

        if (oldData.status ==  9 || (
            $.inArray(oldData.status, [3, 4]) !== -1 && oldData.is_now_user == true
        )) {
            // 报告未生成 报告 3已驳回，4已撤回，并且属于当前用户。那么可以编辑报告
           // previewContainer.find('#item_document .document-title-r').remove();
        }

        previewContainer.find('#item_screen .shadow-box').remove();
        previewContainer.find('#item_document .document-title-desc').css({
            'float': 'right',
            'clear': 'none',
            'margin': '4px 10px 20px 0',
        });
        previewContainer.find('.item-document table tbody tr:nth-child(odd)').css({
            'border-top': '1px solid #B3B3B3',
            'border-bottom': '1px solid #B3B3B3'
        })
        previewContainer.find('.file_result_fail').css({
            'font-weight': 'bold',
        })
        previewContainer.find('.item-verify-bottom-en').css({
            'color': '#666666'
        })

        previewContainer.find('#item_screen .product').css({
            'margin': '0 auto',
            'float': 'unset',
            'width': '100%',
            'text-align': 'center',
        });
        previewContainer.find('#item_screen .product img').css({
            'border': '2px solid #ccc',
            'display': 'inline-block',
            'margin': '0 auto',
        });
        previewContainer.find('#item_screen .verify').css({
            'margin': '20px auto 0',
            'float': 'unset',
            'width': '100%',
            'text-align': 'center',
        });
        previewContainer.find('#item_screen .verify img').css({
            'border': '2px solid #ccc',
            'display': 'inline-block',
            'margin': '0 auto',
        });

        previewContainer.find('.item-verify-items-block').css({
            'border-left': 'none',
        });

        previewContainer.find('.item-textarea').css({
            'height': 'auto',
            'margin-bottom': '20px',
        })

        for (var i in negativeArr){
            if ($.inArray(negativeArr[i].id, ['item_point', 'item_module']) !== -1 && parseInt(negativeArr[i].left) > 0) {
                previewContainer.find('#'+ negativeArr[i].id).css({
                    'margin-left': '49.9%',
                })
            }
        }

        return previewContainer;
    }

    // 提交和生成事件
    function addListenerSub(){
        // 获取暂存的时候的一些数据
        function tempSubmit(type = 1){
            let check = getDiyValue(type);
            if (!check && type == 1) {
                return false;
            }
            // ping上报告名称信息
            itemPositionArr.push({
                'id': 'item_title', 'uuid': oldTitleData.uuid,
                'position': oldTitleData.top + "," + oldTitleData.left,
                'name': 'item_title', val: $('#report_title').val(),
                'en_value': $('#report_title2').val(),
                'value': $('#report_title').val(),
                'type': '.draggable'
            });

            submitData.data = itemPositionArr; // 自定义的diy的内容
            if (itemPlanEditor) {
                submitData.item_plan = itemPlanEditor.getContent(); // 方案的内容
            }
            if (itemResultEditor) {
                submitData.item_result = itemResultEditor.getContent(); // 结论的内容
            }
            var screen_list = [];
            if ($('#item_screen').length>0) {
                // 获取所有的截屏
                $('#item_screen').find('.screen-item-pic').each(function() {
                    var create_time = '';
                    var product = $(this).find('.product img').attr('src');
                    if (product == undefined) {
                        product = '';
                    }
                    var verify = $(this).find('.verify img').attr('src');
                    if (verify == undefined) {
                        verify = '';
                    }
                    var remark = $(this).find('.screen-desc textarea').val();
                    var result = $(this).find('.screen-desc input[type="radio"]:checked').val();

                    screen_list.push(
                        {'product': product, 'verify': verify, 'create_time': create_time, 'remark': remark, 'result': result}
                    );
                })
            }
            submitData.screen_list = screen_list;
            submitData.report_uuid = oldData.report_uuid;
            submitData.report_title = $('#report_title').val();
            return true;
        }

        // 暂存按钮事件
        $('#tempSubmit').off('click').on('click', function() {
            // 请求接口保存数据
            tempSubmit(2);
           // console.log('data', submitData);
            Metronic.blockUI({target: '#industry_report',animate: true,cenrerY: true});
            pAjaxRequest(submitData, "/api/v1/industry/report", "PUT", function (result) {
                Metronic.unblockUI('#industry_report');
                operateResponseList(result, LANG.UI_PLATFORM_INDUSTRY_REPORT_SETTING);
            });
        });

        // 生成报告事件
        $('#makeSubmit').off('click').on('click', function() {
            // 二次提示
            bootbox.confirm({
                title: LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE,
                message: LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE_TIPS,
                callback: function (r) {
                    if (!r) return;
                    makeSubmit();
                }
            });
        })

        function makeSubmit(){
            if ($('#item_screen').length) {
                // 需要判断下页面是否有截屏比对
                var screen_result = 1;
                var hasError = false; // 添加一个标志变量
                // 判断下页面是否还有未选择的截屏比对结果
                $('#container #item_screen .screen-item-pic .screen-desc').each(function (){
                    // 检查是否有 input[type="radio"] 元素
                    var radioInputs = $(this).find('input[type="radio"]');
                    if (radioInputs.length === 0) {
                        // console.warn('当前元素中没有 radio 输入框');
                        return; // 跳过当前循环
                    }

                    // 获取选中的单选按钮的值
                    var checkedRadio = radioInputs.filter(':checked');
                    if (checkedRadio.length === 0) {
                       // console.warn('当前元素中没有选中的 radio 输入框');
                        screen_result = 0;
                        hasError = true;
                        return false; // 停止遍历
                    }

                    var vals = $(this).find('input[type="radio"]:checked').val();
                    if (parseInt(vals) == 1) {

                    } else if (parseInt(vals) == 2){
                        screen_result = 2;
                    } else {
                        screen_result = 0;
                        hasError = true;
                        return false;
                    }
                })
                if (hasError) {
                    UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE, LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE_SCREEN_TIPS);
                    // 获取当前打开的 bootbox 对话框
                    bootbox.hideAll(); // 关闭所有对话框
                    return false;
                }
                submitData.screen_result = screen_result; // 截屏比对结果，所有一致就是1，否则就是2
                var screen_results = screen_result == 1 ? LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME : LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME;
                $('#item_screen_verify .item-verify-val').text(screen_results);
            }

            // 需要再次的初始化获取下自定义的输入
            var html = createHtml();
            var tep = 6,bottomSign = 9;
            for (var i in negativeArr){
               if ($.inArray(negativeArr[i].id, ['item_point', 'item_module']) !== -1) {
                   var top = 0;
                   if (negativeArr[i].top < -100) {
                       top = -113;
                   } else if (negativeArr[i].top < -70) {
                       top = -93;
                   } else if (negativeArr[i].top < -30) {
                       top = -20;
                   }
                    html.find('#'+ negativeArr[i].id).css({
                        'margin-top': top,
                    })
                } else if ($.inArray(negativeArr[i].id, ['item_download_time', 'item_make_time', 'item_operator_sign', 'item_auditor_sign', 'item_operator_sign_sys', 'item_auditor_sign_sys']) !== -1) {
                    html.find('#'+ negativeArr[i].id).css({
                        'margin-top': negativeArr[i].top + tep - bottomSign
                    })
                } else {
                    html.find('#'+ negativeArr[i].id).css({
                        'margin-top': negativeArr[i].top + tep - 15
                    })
                }
            }

            var widths = 1200 / 2 * 0.5 - 20 + 'px';
            widths = 350 + 'px';
            html.find('.item-field').css({
                'width': widths,
            });
            html.find('#container').css({
                'margin': 0,
            });
            html.find('.basic-info').css({
                'margin-left': 0,
            });

            html.find('.item-bottom-items').css({
                'width': widths
            });

            html.find('#item_document tr td').css({
                'height': '68px',
                'border-bottom': '1px solid #B3B3B3',
                'line-height': '24px',
                'padding': '8px 1px',
            });
            html.find('#item_document .file_result_fail td').css({
                'color': '#EAA40E',
                'font-weight': 'bold',
            });
            html.find('#item_document tr th').css({
                'height': '56px',
                'border-top': '1px solid #B3B3B3',
                'border-bottom': '1px solid #B3B3B3',
                'line-height': '20px',
            });
            html.find('.screen-desc').css({
                'width': '100%',
                'margin': '0 2% 48px'
            });
            html.find('.item-operator').css({
                'width': widths,
            });

            html.find('#item_verify_time').css({
                'margin-left': '800px'
            })

            html.find('#item_logo img').css({
                'height': '50px',
            });
            html.find('#item_qrcode').css({
                'margin-top': '-50px',
                'margin-left': 'unset',
                'margin-right': '0px',
                'float': 'right',
                'padding': 0,
            });
            html.find('#item_qrcode img').css({
                'height': '50px',
            });
            html.find('.report-title').css({
                'clear': 'both',
            });
            html.find('.basic-title1s').css({
                'margin-top': '40px'
            })
            html.find('.basic-title-line').css({
                'margin-top': '8px'
            })
            html.find('#item_screen img').css({
                'width': '700px',
                'height': '350px'
            })
            html.find('.item-label-title').css({
                'color': '#666666'
            })
            html.find('.span-input-title').css({
                'color': '#666666'
            })
            html.find('.item-span-title').css({
                'color': '#666666'
            })
            html.find('.span-title').css({
                'color': '#666666'
            })

            // 处理下表格的表头的样式
            html.find('#item_document thead tr .tr-ch').css({
                'font-weight': 400,
                'font-size': '14px',
                'color': '#333333',
                'line-height': '20px',
                'text-align': 'left',
            })

            html.find('#item_document thead tr .tr-en').css({
                'font-weight': 400,
                'font-size': '12px',
                'color': '#666666',
                'line-height': '18px',
                'text-align': 'left',
            })
            // 给所有失败的文件比对表格加粗
            html.find('.file_result_fail td div').css({
                'font-weight': 'bold'
            })
            // 处理下最后的结论不在一行的情况
            html.find('.change-document-result').each(function (){
                var val = $(this).attr('data-val');
                var text = $(this).attr('data-text');
                var ims = '/img/platform/industry/success.svg';
                if (val != 1) {
                    ims = '/img/platform/industry/fail.svg';
                }
                var htmls = `<table style="width: 100%;">
                \t\t\t<tr style="border:none;">
                \t\t\t\t<td style="padding:0 0 0 4px;vertical-align: middle;border:none;background:unset;text-align:left;">
                \t\t\t\t\t<span>`+text+`</span>
                \t\t\t\t</td>
                \t\t\t</tr>
                \t\t</table>`;
                $(this).html(htmls);
            })

            html.find('.make-time-icon').css({
                'margin-bottom': '-2px'
            })

            // 处理下所有的 item-line
            html.find('.item-line').replaceWith(
                '<span style="width:2px;color:#2A87C8;margin: 0 4px;">|</span>'
            );

            html.find('.item-field').each(function (){
                if ($(this).attr('data-field') == 'item_textarea') {
                    $(this).css({
                        'width': '100%',
                    })
                    $(this).find('.item-label').css({
                        'width': '100%',
                    })
                }
            })
            if (!tempSubmit(1)) {
                // 提示必须填写完成
                return false;
                // return UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE, '基本信息必须填写');
            }

            // 生成报告之前需要先保存报告，因为不知道页面是否进行过修改
            var data = {
                'report_uuid': oldData.report_uuid,
                'data': html.html(),
                'report_data': submitData,
                'source': source
            };
            Metronic.blockUI({target: '#industry_report',animate: true,cenrerY: true});
            pAjaxRequest(data, "/api/v1/industry/report/", "POST", function (result) {
                Metronic.unblockUI('#industry_report');
                if (result.code == 0) {
                   // 更新状态 和 审批流以及去除截屏的删除按钮
                    // 关闭
                    $('#drawer-config').drawer('hide');
                    // 刷新列表
                    $('#report_table').bootstrapTable('refresh');
                   // LOCATION('./content/platform/industry/job_report.php?uuid='+uuid, 'job_report');
                    UIToastr.showSuccess(LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE, result.message);
                } else {
                    UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE, result.message);
                }
            });
        }

        // 审批报告事件
        $('#reportApproval').off('click').on('click', function (){
            // 先关闭当前的抽屉，然后打开列表的审批抽屉
            $('#drawer-pre').drawer('hide');
            $('#'+uuid).find('.approve a').click();
        })

        // 下载报告事件
        $('#downloadReport').off('click').on('click', function() {
            // 请求接口，然后返回下载的url。最后 location 跳转
            Metronic.blockUI({target: '#industry_report',animate: true,cenrerY: true});
            pAjaxRequest({}, "/api/v1/industry/report/"+ oldData.report_uuid+"/download", "POST", function (result) {
                Metronic.unblockUI('#industry_report');
                if (result.code == 0) {
                    location.href = result.data.url;
                    UIToastr.showSuccess(LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD, LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD_TIPS);
                } else {
                    UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD, result.message);
                }
            });
        })

        // 下载所有的文件结果
        $('#downloadlFile').off('click').on('click', function() {
            // 请求接口，然后返回下载的url。最后 location 跳转
            Metronic.blockUI({target: '#drawer-document',animate: true,cenrerY: true});
            pAjaxRequest({}, "/api/v1/industry/report/"+ oldData.report_uuid+"/download_file", "POST", function (result) {
                Metronic.unblockUI('#drawer-document');
                if (result.code == 0) {
                    downloadFilesAndNotifyServer([result.data]);
                } else {
                    UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_REPORT_DOWNLOAD, result.message);
                }
            });
        })

        // 发送到邮箱
        $('#reportEmail').off('click').on('click', function() {
            // 请求接口
            Metronic.blockUI({target: '#industry_report',animate: true,cenrerY: true});
            pAjaxRequest({}, "/api/v1/industry/report/"+ oldData.report_uuid+"/send", "POST", function (result) {
                Metronic.unblockUI('#industry_report');
                operateResponseList(result, LANG.UI_PLATFORM_INDUSTRY_REPORT_SEND);
            });
        })
    }

    // 以下是下载文件的
    function downloadFilesAndNotifyServer(fileUrls) {
        Metronic.blockUI({target: '#drawer-document',animate: true});
        var completedDownloads = 0;
        var totalFiles = fileUrls.length;
        function notifyIfAllCompleted() {
            if (completedDownloads === totalFiles) {
                notifyServer(fileUrls);
            }
        }
        fileUrls.forEach(function(fileUrl) {
            downloadFile(fileUrl, function(success) {
                var fileName = extractFileNameFromUrl(fileUrl);
                if (success) {
                    // console.log('文件 ' + fileUrl + ' 下载完成');
                    UIToastr.showSuccess(LANG.UI_PUBLIC_TIPS, LANG.UI_PUBLIC_DOWNLOAD + fileName + LANG.UI_PUBLIC_SUCCESS)
                } else {
                    // console.error('文件 ' + fileUrl + ' 下载失败');
                    UIToastr.showError(LANG.UI_PUBLIC_TIPS, LANG.UI_PUBLIC_DOWNLOAD + fileName + LANG.UI_PUBLIC_FAILED)
                }
                completedDownloads++;
                notifyIfAllCompleted();
            });
        });
    }
    // 从URL中提取文件名
    function extractFileNameFromUrl(url) {
        return url.substring(url.lastIndexOf('/') + 1);
    }
    // 下载操作
    function downloadFile(fileUrl, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', fileUrl, true);
        xhr.responseType = 'blob';

        xhr.onload = function() {
            if (xhr.status === 200) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(xhr.response);
                a.href = url;
                a.download = extractFileNameFromUrl(fileUrl); // 提取文件名
                document.body.appendChild(a);
                a.click();

                // 在点击之后立即撤销对象 URL
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                callback(true);
            } else {
                // console.error('文件下载失败:', xhr.statusText);
                UIToastr.showError(LANG.UI_PUBLIC_TIPS, xhr.statusText)
                callback(false);
            }
        };
        xhr.onerror = function() {
            UIToastr.showError(LANG.UI_PUBLIC_TIPS, LANG.UI_PUBLIC_NETWORK_ERROR)
            // console.error('请求错误');
            callback(false);
        };
        xhr.send();
    }

    // 下载完后请求回调
    function notifyServer(fileUrls) {
        Metronic.unblockUI('#drawer-document');
        // 请求后台接口。删除对应的资源
        pAjaxRequest({files: fileUrls}, '/api/v1/recovery/graininess_file/', "DELETE", function (result) {
            // 不管结果
        });
    }

    // 从URL中提取文件名
    function extractFileNameFromUrl(url) {
        return url.substring(url.lastIndexOf('/') + 1);
    }


    // 初始化监听事件
    function initListener() {
        resizeReport();
        // 如果你需要在窗口大小改变时重新获取高度，可以这样做：
        $(window).resize(function() {
            resizeReport();
        });

        // 提交事件
        addListenerSub()
        getDefaultItem();

        limitLength();

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

    // 详情初始化
    function initView(uuid){
        var url = "/api/v1/industry/report/"+uuid;
        var param = {};
        if (agent_uuid != '') {
            // 那么是从任务那边过来的配置
            url = "/api/v1/industry/report";
            param = {
                job_uuid: uuid,
                agent_uuid:agent_uuid
            }
        }

        // 那么需要读取接口
        Metronic.blockUI({target: '#industry_report',animate: true,cenrerY: true});
        pAjaxRequest(param, url, "GET", function (result) {
            Metronic.unblockUI('#industry_report');
            if (result.code == 0) {
                oldData = result.data
                uuid = oldData.report_uuid;
                agent_uuid = '';
                consoleView(result.data);
            } else {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_REPORT_SETTING, result.message);
            }
        });
    }

    // 处理初始化详情的数据解析
    function consoleView(data){
        if (data.logo.flag == true) {
            logo = data.logo.value;
        }
        status = data.status;
        $('#tempSubmit').show();
        $('#makeSubmit').show();
        $('#downloadReport').show();
        $('#reportEmail').show();
        if (data.status ==  9) {
            // 报告未生成
            $('#downloadReport').hide();
            $('#reportEmail').hide();
        } else if ($.inArray(data.status, [3, 4]) !== -1 && data.is_now_user == true) {
            // 报告 3已驳回，4已撤回，并且属于当前用户。那么可以编辑报告
            $('#downloadReport').hide();
            $('#reportEmail').hide();
        } else if (data.status == 1) {
            // 已归档 可下载
            $('#tempSubmit').hide();
            $('#makeSubmit').hide();
        } else {
            // 报告已生成 审批流中
            $('#tempSubmit').hide();
            $('#makeSubmit').hide();
            $('#downloadReport').hide();
            $('#reportEmail').hide();
        }
        if (data.approval_flag && _history == 0) {
            $('#reportApproval').show();
        } else {
            $('#reportApproval').hide();
        }
        initItemShow(data);
    }

    // 初始化处理下组件的显示问题
    function initItemShow(data){
        var width = $("#container").width(); // 记录此时的内容的宽度
        var scale = width / (data.item_width ? data.item_width : width);
        analysis(data, 1);// 概要

        // 只保留截图验证的最后一个生产和验证的删除按钮
        calcDel();

        // 添加截屏按钮事件
        addListenerScreen(data.report_uuid);

        // 判断下最后一个截屏集合是否有生成和验证
        var product = $('.screen-item-pic').last().find('.product');
        var verify = $('.screen-item-pic').last().find('.verify');
        if (product.length > 0 && verify.length > 0) {
            initScreenTag = 0;
        } else if(product.length > 0 ) {
            initScreenTag = 1;
        } else if (verify.length > 0) {
            initScreenTag = 2;
        } else {
            initScreenTag = 0;
        }

        // 这里请求病毒查杀，文件比对
       /* getVirtusList(params);*/
        if ($('#item_document').length) {
            var params = {
                'offset': 0,
                'limit': data.document_num
            };
            getDocumentList(params);
        } else {
            pre_loading = false;
        }

       /* // 更多病毒查杀点击
        moreVirtus();*/

        // 更多比对文件点击
        moreDocument();

        // 限制所有的input框的最大长度
        // 监听 input 事件
        $('input[name="item_field_name"]').on('input', function() {
            // 获取当前输入的值
            var value = $(this).val();
            // 如果输入的值长度超过 8 个字符
            if (value.length > 8) {
                // 截断输入，只保留前 8 个字符
                $(this).val(value.substring(0, 8));
            }
        });

        $('input[name="item_field_name_en"]').on('input', function() {
            // 获取当前输入的值
            var value = $(this).val();
            // 如果输入的值长度超过 16 个字符
            if (value.length > 16) {
                // 截断输入，只保留前 16 个字符
                $(this).val(value.substring(0, 16));
            }
        });
        $('input[name="item_field_value"]').on('input', function() {
            // 获取当前输入的值
            var value = $(this).val();
            // 如果输入的值长度超过 36 个字符
            if (value.length > 36) {
                // 截断输入，只保留前 36 个字符
                $(this).val(value.substring(0, 36));
            }
        });

        $('#parent_container .basic-info').css({
            'margin-left': 0
        })

        if ($.inArray(data.status, [3, 4, 9]) === -1) {
            // 不属于 报告未生成 或者 3已驳回，4已撤回
            // 那么报告不能显示能修改的内容
            $('#parent_container #container').find('input').css({
                'background': '#fff',
                'border': 'none',
            })
            $('#parent_container #container').find('input').attr('disabled', 'true');

            $('#parent_container #container').find('input[name="item_field_value"]').css({
                'color': '#333333',
            })

            $('#parent_container #container').find('textarea').css({
                'background': '#fff',
                'border': 'none',
                'color': '#333333',
                'padding': 0
            })
            $('#parent_container #container').find('textarea').attr('placeholder', '');
            $('#parent_container #container').find('textarea').attr('readonly', 'true');
        }

        // 去除 :hover 效果
        $(".item-items").removeClass('item-items');
    }

    // 更多病毒查杀事件监听
    function moreVirtus(){
        // 更多文件比对点击事件
        $('#item_virtus').find('.open-result-title-spanr').off('click').on('click', function() {
            lookMoreVirtus();
        })
        $('.job-search').keypress(function (e) {
            if (e.which == 13) {
                var params = {};
                params['search'] = $('.job-virtus-search').val();
                $('#virtus-table').bootstrapTable('refresh', {
                    query: params,
                    offset:0
                });
            }
        });
        $('#vin_job_report_virtus_toolbar .search-btn').off('click').on('click', function() {
            var params = {};
            params['search'] = $('.job-virtus-search').val();
            $('#virtus-table').bootstrapTable('refresh', {
                query: params,
                offset:0
            });
        })
    }

    // 更多文件比对事件监听
    function moreDocument(){
        // 更多文件比对点击事件
        $('#item_document').find('.document-title-r').off('click').on('click', function() {
            lookMoreDocument();
        })
        $('.job-search').keypress(function (e) {
            if (e.which == 13) {
                var params = {};
                params['search'] = $('.job-search').val();
                $('#document-table').bootstrapTable('refresh', {
                    query: params,
                    offset:0
                });
            }
        });
        $('#vin_job_report_document_toolbar .search-btn').off('click').on('click', function() {
            var params = {};
            params['search'] = $('.job-search').val();
            $('#document-table').bootstrapTable('refresh', {
                query: params,
                offset:0
            });
        })
    }

    // 获取病毒查杀列表
    function getVirtusList(data){
        Metronic.blockUI({target: '#item_virtus',animate: true,cenrerY: true});
        pAjaxRequest(data, "/api/v1/industry/report/"+ oldData.report_uuid+"/virtus", "GET", function (result) {
            Metronic.unblockUI('#item_virtus');
            if (result.code == 0) {
                // 渲染数据
                var results = result.data;
                var html = '';
                if (results.total > 0) {
                    var top = '<table class="item-table">' +
                        '<thead><tr><th width="5%"></th><th width="40%" class="text-left">'+LANG.UI_DATA_FILE_FILE+'</th>' +
                        '<th width="40%" class="text-left">'+LANG.UI_ALARM_DETAILS+'</th>' +
                        '<th width="15%" class="text-left">'+LANG.UI_PLATFORM_INDUSTRY_RESULTS+'</th></tr></thead>';
                    var bottom = '</table>';
                    for (var k in results.rows) {
                        var classs = 'normal';
                        if (results.rows[k].status == 1) {
                            // 正常
                            classs = 'normal-td';
                        } else {
                            // 异常
                            classs = 'warnning-td';
                        }
                        html += '<tr>  \n' +
                            '    <td class="text-td">'+ results.rows[k].num+'</td>  \n' +
                            '    <td class="text-td">'+ results.rows[k].file+'</td>  \n' +
                            '    <td class="text-td">'+ results.rows[k].desc+'</td>  \n' +
                            '    <td class="text-td '+classs+'">'+ results.rows[k].status_value+'</td>  \n' +
                            '  </tr>';
                    }
                    html = top + '<tbody>'+html +'</tbody>' + bottom;
                    var warnning_num = results.total - results.normal;
                    var title = '<span class="item-all-results">（'+LANG.UI_PLATFORM_INDUSTRY_REPORT_ALL_VIRUS+'：<span style="font-size: 16px;">'+results.total+'</span>'+LANG.UI_PUBLIC_NUM+' '+LANG.UI_PLATFORM_DES_NORMAL+'：<span style="font-size: 16px;">'+results.normal+'</span>'+LANG.UI_PUBLIC_NUM+' '+LANG.UI_NODE_ABNORMAL+'：<span style="font-size: 16px;">'+warnning_num+'</span>'+LANG.UI_PUBLIC_NUM+'）</span>';
                    $('#item_virtus .open-result-title').find('.open-result-title-spanl').append(title);
                    if (isEnCn) {
                        title = '<span class="item-all-results">（Jointly investigate and kill：<span style="font-size: 16px;">'+results.total+'</span>individual normal：<span style="font-size: 16px;">'+results.normal+'</span>individual abnormal：<span style="font-size: 16px;">'+warnning_num+'</span>individual）</span>';
                        $('#item_virtus .open-result-title').find('.open-result-title-spanl_en').append(title);
                    }
                } else {
                    html = '<div style="padding: 20px;">'+LANG.UI_PLATFORM_INDUSTRY_NO_RESULTS+'</div>';
                }
                $('#item_virtus').find('.open-result-title-div').html(html);
            } else {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_TEMPLATE_SETTING, result.message);
            }
        });
    }

    // 获取文件比对列表
    function getDocumentList(data){
        Metronic.blockUI({target: '#item_document',animate: true,cenrerY: true});
        pAjaxRequest(data, "/api/v1/industry/report/"+ oldData.report_uuid+"/document", "GET", function (result) {
            Metronic.unblockUI('#item_document');
            if (result.code == 0) {
                // 渲染数据
                var results = result.data;
                var html = '';
                if (_taskStatus == CONF.TASK_STATUS.STOPPED) {
                    results.total = 0;
                }
                if (results.total > 0) {
                    // 这里需要判断下当前报告设置的展示哪些属性
                    var file_size = oldData.document_custom.file_size; // 是否展示文件大小
                    var file_position = oldData.document_custom.file_position; // 是否展示文件属性
                    var file_create = oldData.document_custom.file_create; // 是否展示创建时间
                    var file_update = oldData.document_custom.file_update; // 是否展示修改时间

                    var top = '<table style="border-collapse: collapse;border-spacing: 0;">' +
                        '<thead><tr>' +
                        '<th class="text-left"><span class="tr-ch">'+LANG.UI_PLATFORM_INDUSTRY_PRODUCT_AND_VERIFY_FILES+'</span>' +
                        '<div class="item_en tr-en">' +
                        'Source files & backup files' +
                        '</div></th>' +
                        '<th width="275" class="text-left">' +
                        '<div class="tr-ch">'+oldData.file_method+ LANG.UI_PLATFORM_INDUSTRY_VERIFY_VALUE+'</div>' +
                        '<div class="item_en tr-en" style="line-height: 28px;">' +
                        ''+oldData.file_method+' Verification value' +
                        '</div></th>';

                    if (file_size == true) {
                        top += '<th width="70" class="text-left file_size">' +
                            '<div class="tr-ch">'+LANG.UI_BACKUP_DATA_VRIUS_TABLE_HEAD_LABEL_SIZE+'</div>' +
                            '<div class="item_en tr-en" style="line-height: 28px;height: 28px;">' +
                            '\t\t\t\t\tFiles Size' +
                            '</div></th>';
                    }
                    if (file_position == true) {
                        top +=  '<th width="70" class="text-left file_attr">' +
                            '<div class="tr-ch">'+LANG.UI_PLATFORM_INDUSTRY_FILES_ATTR+'</div>' +
                            '<div class="item_en tr-en" style="line-height: 28px;height: 28px;">' +
                            'Permission' +
                            '</div></th>';
                    }
                    if (file_create == true) {
                        top += '<th width="80" class="text-left file_create">' +
                            '<div class="tr-ch">'+LANG.UI_PUBLIC_CREATE_TIME+'</div>\n' +
                            '\t\t\t\t<div class="item_en tr-en" style="line-height: 28px;height: 28px;">\n' +
                            '\t\t\t\t\tCreate Time\n' +
                            '\t\t\t\t</div></th>';
                    }
                    if (file_update == true) {
                        top += '<th width="80" class="text-left file_modify">' +
                            '<div class="tr-ch">'+LANG.UI_SETTING_MODIFY_TIME+'</div>\n' +
                            '\t\t\t\t<div class="item_en tr-en" style="line-height: 28px;height: 28px;">\n' +
                            '\t\t\t\t\tModify Time\n' +
                            '\t\t\t\t</div></th>';
                    }

                    top += '<th width="70" class="text-left document_result">' +
                        '<div class="tr-ch">'+LANG.UI_PLATFORM_INDUSTRY_RESULTS+'</div>\n' +
                        '\t\t\t\t<div class="item_en tr-en" style="line-height: 28px;height: 28px;">\n' +
                        '\t\t\t\t\tResult\n' +
                        '\t\t\t\t</div></th>' +
                        '</tr>';

                    top += '</thead>';
                    var bottom = '</table>';
                    for (var k in results.rows) {
                        var classs_name = results.rows[k].status == 1 ? 'file_result_ok' : 'file_result_fail';
                        html += '<tr class="'+classs_name+'">  \n' +
                            '      \n';
                        // 这里组装下生产和验证的对比
                        html += ' <td class="text-left"><div>'+ results.rows[k].produce.name + '</div><div style="line-height: 28px;">' + results.rows[k].verify.name + '</div></td>  \n';

                        html += '  <td class="text-left"><div>'+ results.rows[k].produce.encryption + '</div><div style="line-height: 28px;height: 28px;">' + results.rows[k].verify.encryption + '</div></td>  \n';

                        if (file_size == true) {
                            html += '<td class="text-left file_size"><div>' + results.rows[k].produce.size + '</div><div style="line-height: 28px;height: 28px;">' + results.rows[k].verify.size + '</div></td>  \n';
                        }

                        if (file_position == true) {
                            html += '<td class="text-left file_attr"><div>' + results.rows[k].produce.attribute + '</div><div style="line-height: 28px;height: 28px;">' + results.rows[k].verify.attribute + '</div></td>  \n';
                        }
                        if (file_create == true) {
                            html += '<td class="text-left file_create"><div>' + results.rows[k].produce.create_time + '</div><div>' + results.rows[k].verify.create_time + '</div></td>  \n';
                        }
                        if (file_update == true) {
                            html += '<td class="text-left file_modify"><div>' + results.rows[k].produce.modify_time + '</div><div>' + results.rows[k].verify.modify_time + '</div></td>  \n';
                        }
                        var syl = 'width:16px;height:16px;margin-right:4px;line-height:24px;display:inline-block;';
                        var clsv = '<img src="/img/platform/industry/success.svg" style="'+syl+'">'
                        if (results.rows[k].status != 1) {
                            clsv = '<img src="/img/platform/industry/fail.svg" style="'+syl+'">'
                        }
                        html +=  '<td class="text-td change-document-result" data-val="'+results.rows[k].status+'" data-text="'+results.rows[k].status_value+'">' + clsv+ results.rows[k].status_value+'</td>\n' +
                            '  </tr>';
                    }
                    html = top + '<tbody>' + html + '</tbody>' + bottom;
                    var warnning_num = results.totals - results.normal;

                    var title = LANG.UI_PLATFORM_INDUSTRY_FILES_ALL + '：<span class="num">'+results.totals+'</span>，'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME+'：<span class="num">'+results.normal+'</span>，'+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME+'：<span class="num">'+warnning_num+'</span>';
                    if (isEnCn) {
                        title += '<span class="item_en en">（Total comparison：'+results.totals+'，individual consistent：'+results.normal+'，individual inconsistent：'+warnning_num+'）</span>';
                    }
                    $('#item_document').find('.document-title-desc').append(title);
                } else {
                    html = '<div style="padding: 20px;">'+LANG.UI_PLATFORM_INDUSTRY_NO_RESULTS+'</div>';
                }
                $('#item_document').find('.open-result-title-div').html(html);
            } else {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_REPORT_SETTING, result.message);
            }
            pre_loading = false;
        }, false);
    }

    // 解析组合元素
    function analysis(data, scale, container = '#container'){
        var description = data.position; // 位置信息
        var approval_list = data.approval_list;// 审批流
        var html = '',preId = '',height = 0,preTop = 0,isFirst = false,isVerify = false,_tops = 0;
        var three = 0; // 记录三块的当前的次数
        itemPositionArr = [];
        for (var j in description) {
            if (description[j].id == 'item_description') {
                continue;
            }
            var items = description[j]['type'];
            if (items == 'item_title') {
                // 报告名称
                $('#report_title').val(description[j].value);
                if (isEnCn == 1) {
                    $('#report_title2').val(description[j].en_value);
                } else {
                    $('.item_en').remove();
                }
                oldTitleData = description[j];
                continue;
            }
            // 处理下位置 取相对的位置
            if (items == 'item_field') {
                html = makeDiyField(1, description[j].id, description[j].name, description[j].en_name, description[j].value);
                itemPositionArr.push({'id': description[j].id, 'uuid': description[j].uuid, 'position': description[j].top + "," + description[j].left, 'name': items, val:description[j].name, en_val:description[j].en_name, value:description[j].value, 'type': '.draggable'});
            } else if (items == 'item_logo') {
                html = makeItemLogo(logo);
            }else if (items == 'item_qrcode') {
                html = makeItemQrcode(data.qrcode);
            }else if (items == 'item_textarea') {
                html = makeDiyField(3, description[j].id, description[j].name, description[j].en_name, description[j].value, LANG.UI_PLATFORM_INDUSTRY_DIY_FIELDS_TIPS);
                itemPositionArr.push({'id': description[j].id, 'uuid': description[j].uuid, 'position': description[j].top + "," + description[j].left, 'name': items, val:description[j].name, en_val:description[j].en_name, value:description[j].value, 'type': '.draggable'});
            } else if (items == 'item_datetime') {
                html = makeDiyField(2, description[j].id, description[j].name, description[j].en_name, description[j].value);
                itemPositionArr.push({'id': description[j].id, 'uuid': description[j].uuid, 'position': description[j].top + "," + description[j].left, 'name': items, val:description[j].name, en_val:description[j].en_name, value:description[j].value, 'type': '.draggable'});
            } else if (items == 'item_approval') {
                // 审批流
                html = makeApproval(LANG.UI_PLATFORM_INDUSTRY_APPROVAL,'APPROVAL PROCESS',approval_list);
            } else if (items == 'item_module') {
                // 模块内容替换
                html = makeVerfiyBlock('item_module', 'item_module', LANG.UI_PLATFORM_INDUSTRY_HOST_NAMES, 'Host Name', description[j].value != '' ? description[j].value : data.host);
            } else if (items == 'item_point') {
                // 模块内容替换
                html = makeVerfiyBlock('item_point', 'item_point', LANG.UI_PLATFORM_INDUSTRY_POINT_NAMES, 'Backup Time Point', data.timepoint);
            } else if (items == 'item_integrality') {
                // 模块内容替换
                var status = LANG.UI_PLATFORM_DES_NORMAL;
                if (data['integrality'] == false) {
                    status = LANG.UI_NODE_ABNORMAL;
                }
                html =  makeVerfiy2Block('item_integrality', 'item_integrality', LANG.UI_PLATFORM_INDUSTRY_OPEN_VERIFY, 'Boot Verification Result', status);
            } else if (items == 'item_screen_verify') {
                // 模块内容替换
                // 有个特殊的判断，如果是没有选择所有的截屏结果，那么这个就是待定
                var status = LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_UNKNOWN;
                if (data['screen_result'] == 1) {
                    status = LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME;
                } else if (data['screen_result'] == 2) {
                    status = LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME;
                }
                html =  makeVerfiy2Block('item_screen_verify', 'item_screen_verify', LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULTS, 'Screenshot Comparison Results', status);
            } else if (items == 'item_files') {
                // 模块内容替换
                var status = LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME;
                if (data['files'] == false) {
                    status = LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME;
                }
                html =  makeVerfiy2Block('item_files', 'item_files', LANG.UI_PLATFORM_INDUSTRY_FILES_RESULTS, 'File Comparison Result', status);
            } else if (items == 'item_open') {
                if (_taskStatus == CONF.TASK_STATUS.STOPPED) {
                    data.open = '';
                }
                html = makeTitlePic(2, 'item_open', 'item-open', 'item_open', LANG.UI_PLATFORM_INDUSTRY_OPEN_RESULTS,'BOOT VERIFICATION', data.open);
            } else if (items == 'item_results') {
                // 最终结果
                var status = LANG.UI_PLATFORM_DES_NORMAL;
                if (data.results == false) {
                    status = LANG.UI_NODE_ABNORMAL;
                }
                html = makeDiyVal(2, 'item_results', 'item_results', LANG.UI_PLATFORM_INDUSTRY_FINAL_RESULTS, 'Final Result', status);

            } else if (items == 'item_verify_time') {
                html = makeReportTime('item_verify_time', 'item_verify_time', LANG.UI_PLATFORM_INDUSTRY_MAKE_TIME, 'Generation Time', data.create_time);
            }else if (items == 'item_operator') {
                var values = description[j].value;
                if (values == '') {
                    values = data.user_name;
                }
                html = makeDiyVal(1, 'item_operator','item_operator', LANG.UI_PLATFORM_INDUSTRY_OPERATOR, 'Operator', values, '系统自动填充');
                itemPositionArr.push({'id': items, 'uuid': description[j].uuid, 'position': description[j].top + "," + description[j].left, 'name': items, val: LANG.UI_PLATFORM_INDUSTRY_OPERATOR,en_val: 'Operator', value:values, 'type': '.draggable'});
            } else if (items == 'item_host') {
                // 主机名
                var values = description[j].value;
                if (values == '') {
                    values = data.host;
                }
                html = makeDiyVal(1, 'item_host', 'item_host', LANG.UI_PLATFORM_INDUSTRY_HOST_NAME, 'Host Name', values, '系统自动填充');
                itemPositionArr.push({'id': items, 'uuid': description[j].uuid, 'position': description[j].top + "," + description[j].left, 'name': items, val: LANG.UI_PLATFORM_INDUSTRY_HOST_NAME,en_val: 'Host Name', value:values, 'type': '.draggable'});
            } else if (items == 'item_ip') {
                // ip
                var values = description[j].value;
                if (values == '') {
                    values = data.ip;
                }
                html = makeDiyVal(1, 'item_ip', 'item_ip', LANG.UI_PUBLIC_IP_ADDRESS, 'IP Address', values, '系统自动填充');
                itemPositionArr.push({'id': items, 'uuid': description[j].uuid, 'position': description[j].top + "," + description[j].left, 'name': items, val: 'ip','en_val': 'IP Address', value:values, 'type': '.draggable'});
            } else if (items == 'item_sys_code') {
                // 系统编号
                var values = description[j].value;
                html = makeDiyVal(1, 'item_sys_code', 'item_sys_code', '系统编号', 'System Code', values, '请填写系统编号');
                itemPositionArr.push({'id': items, 'uuid': description[j].uuid, 'position': description[j].top + "," + description[j].left, 'name': items, val: '','en_val': 'System Code', value:values, 'type': '.draggable'});
            }  else if (items == 'item_sys_name') {
                // 系统名称
                var values = description[j].value;
                html = makeDiyVal(1, 'item_sys_name', 'item_sys_name', '系统名称', 'System Name', values, '请填写系统名称');
                itemPositionArr.push({'id': items, 'uuid': description[j].uuid, 'position': description[j].top + "," + description[j].left, 'name': items, val: '','en_val': 'System Name', value:values, 'type': '.draggable'});
            } else if (items == 'item_plan') {
                // 这是方案编辑器
                html = makeEditor('item_plan', 'item-plan', 'item_plan', LANG.UI_PLATFORM_INDUSTRY_VERIFY_PLAN, 'VERFICATION PLAN',  data.item_plan);
            }else if (items == 'item_result') {
                // 这是自定义结论编辑器
                html = makeEditor('item_result', 'item-result', 'item_result', LANG.UI_PLATFORM_INDUSTRY_VERIFY_RESULT, 'VERFICATION CONCLUSION',  data.item_result);
            } else if (items == 'item_screen') {
                // 截图对比
                if (_taskStatus == CONF.TASK_STATUS.STOPPED) {
                    data.screen_list = [];
                }
                html = makeScreenBtn(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_COMPARSION, ' SCREENSHOT COMPARSION', data.screen_list);
            } else if (items == 'item_auditor_sign_sys') {
                // 审核员-系统签字
                html = makeBottomBlock('item_auditor_sign_sys', 'item-auditor_sign_sys','item_auditor_sign_sys', LANG.UI_PLATFORM_INDUSTRY_AUDITOR_SYS_SIGN,'Auditor(Sys_signature)', data.auditor_sign_sys);
            } else if (items == 'item_operator_sign_sys') {
                // 审核员-系统签字
                html = makeBottomBlock('item_operator_sign_sys', 'item-operator_sign_sys','item_operator_sign_sys', LANG.UI_PLATFORM_INDUSTRY_OPERATOR_SYS_SIGN, 'Operator(Sys_signature)', data.operator_sign_sys)
            } else if (items == 'item_basic_info') {
                html = '<div class="basic-title-line" id="item_basic_info" style="padding-top: 20px;"></div>';
            } else {
                html = itemFieldArr[items];
            }

            if (!isVerify && $.inArray(items, ['item_module', 'item_point', 'item_integrality', 'item_files']) !== -1) {
                // 需要特别处理下验证信息的标题
                isVerify = true;
                html = '<pagebreak><div class="pagebreak-marker"></div><div class="basic-info basic-title2">\n' +
                    '                            '+LANG.UI_PLATFORM_INDUSTRY_VERIFY_INFO+'\n' +
                    '                            <span class="item-line item_en"></span>\n' +
                    '                            <span class="item_en span-title-basic">VERIFIVATION INFORMATION</span>\n' +
                    '                        </div>' + html;
                _tops = 20; // 标记此时验证元素的第一个距离上面的位置
            } else {
                _tops = 0;
            }

            var tops = description[j].top;
            if (tops <= 40) {
                // 那么需要再标题的前面
                $(container).find('.report-title').before(html);
                $(container).find('.report-title').css({
                   'margin-top':'-50px'
                })
            } else {
                $(container).append(html);
            }

            if (items == 'item_make_time' && oldData.report_time) {
                // 处理下生成时间
                $(container + ' #item_make_time .item_make_time').html(oldData.report_time);
            }

            if (!isFirst && $.inArray(items, ['item_qrcode', 'item_logo', 'item_basic_title', 'item_title']) === -1) {
                // 需要特别处理下距离基本信息的位置
                height += 55;
                isFirst = true;
            }

            var left = parseInt(description[j].left, 10) * scale;

            if (preId != '') {
                // 距离上一个元素的距离 = 本身的top - （上一个元素的高度+上一个个元素的距离顶部的top）
                if (preTop == tops) {
                    // 同一行的，需要减去第一个元素的top的值
                    tops = tops - (height + preTop);
                } else {
                    tops = tops - (height + preTop);
                }
            }

            height =  $('#'+description[j].id).outerHeight();
            if (items == 'item_document') {
                // 如果是文件比对的话，需要减去最小的高度 500
                height = Math.max(height, 500);
            }

            preId = description[j].id;
            preTop = description[j].top;

            var width = $('#container').width();

            var mleft = left/width * 100;
            if (mleft != 0) {
                mleft = mleft + '%';
            }

            tops = _tops !== 0 ? _tops : tops;

            if ($.inArray(items, ['item_module', 'item_point']) !== -1 && parseInt(description[j].left) > 40) {
                mleft = '50%';
            } else if ($.inArray(items, ['item_integrality', 'item_screen_verify', 'item_files']) !== -1) {
                mleft = 0;
                // 三块一行
                if (three == 0) {
                    mleft = 0;
                } else if (three == 1){
                    mleft = '33%';
                    tops = tops - 8;
                } else {
                    tops = tops - 8;
                    mleft = '67%';
                }
                $('#'+description[j].id).css({
                    width: three == 1 ? '34%' : '33%',
                });
                three ++;
            } else if (items == 'item_open') {
                tops = 40;
            } else if (items == 'item_verify_time') {
                tops = -24;
                mleft = 'calc(100% - 390px)';
            } else if (items == 'item_qrcode'){
                mleft = 'calc(100% - 48px)';
            } else  if ($.inArray(items, ['item_logo','item_open', 'item_document', 'item_screen']) !== -1) {
                // 这些应该是 没得left
                mleft = 0;
            }

            // 每个大标题都是距离上面 40px
            if ($.inArray(items, ['item_plan', 'item_open', 'item_document', 'item_screen', 'item_approval', 'item_result']) !== -1) {
                tops = 40;
            }

            $('#'+description[j].id).css({
                'margin-top': tops + 'px',
                'top': 0,
                'left': 0,
                'margin-left': mleft,
            });
            var arrs = [
                'item_verify_time', 'item_point', 'item_download_time', 'item_make_time',
                'item_operator_sign', 'item_auditor_sign', 'item_operator_sign_sys', 'item_auditor_sign_sys',
               /* 'item_files', 'item_integrality', 'item_screen_verify','item_results','item_host','item_ip','item_operator', 'item_field'*/
            ];

            if (tops < 0 && $.inArray(items, arrs) !== -1) {
                negativeArr.push({'id': description[j].id, 'top': tops, 'left': left});
            }

            items == 'item_datetime' && initDatetimePicker(); // 日期控件需要初始化

            if ($.inArray(oldData.status, [3, 4, 9]) !== -1) {
                // 不属于 报告未生成 或者 3已驳回，4已撤回
                if ($.inArray(items, ['item_plan']) !== -1) {
                    itemPlanEditor = UE.getEditor(items + '_editor', {
                        // 隐藏工具栏
                        //toolbars: [],
                        // 隐藏元素路径提示
                        elementPathEnabled: false,
                        initialFrameHeight: 220,
                        zIndex: 10090, // 编辑器层级的基数
                        wordCountMsg: LANG.UI_PLATFORM_INDUSTRY_EDITOR_LEFT+' {#count} '+LANG.UI_PLATFORM_INDUSTRY_EDITOR_RIGHT
                    }); //编辑器控件需要初始化
                    listenEditor(itemPlanEditor, items);
                }
                if ($.inArray(items, ['item_result']) !== -1) {
                    itemResultEditor = UE.getEditor(items + '_editor', {
                        // 隐藏工具栏
                        //toolbars: [],
                        // 隐藏元素路径提示
                        elementPathEnabled: false,
                        initialFrameHeight: 220,
                        zIndex: 10090, // 编辑器层级的基数
                        wordCountMsg: LANG.UI_PLATFORM_INDUSTRY_EDITOR_LEFT+' {#count} '+LANG.UI_PLATFORM_INDUSTRY_EDITOR_RIGHT
                    }); //编辑器控件需要初始化
                    listenEditor(itemResultEditor, items);
                }
            } else {
                $('#item_plan_editor').replaceWith('<div class="item-editor_pre"><div style="margin:0 20px;margin-top: 20px;">' +data.item_plan+'</div></div>'); // 方案的内容
                $('.item-plan').css({
                    'height': 'auto'
                })
                $('#item_result_editor').replaceWith('<div class="item-editor_pre"><div style="margin:0 20px;margin-top: 20px;">' +data.item_result+'</div></div>'); // 方案的内容
                $('.item-result').css({
                    'height': 'auto'
                })

                // 处理下textarea文本框的问题
                $('#container').find('textarea[name="item_field_value"]').each(function (){
                    $(this).replaceWith(
                        '<div style="color: rgb(51, 51, 51);margin-top: 8px;width: 1188px;\n' +
                        '    word-wrap: break-word;">'+$(this).val()+'</div>'
                    );
                })
                $('.item-textarea').css({
                    'height': 'auto'
                })

                // 所有的input转为html，因为mpdf对input不友好
                $('#container').find('input').each(function (){
                    var name = $(this).attr('name');
                    var id = $(this).parent().attr('id');
                    if (id == undefined) {
                        id = $(this).parent().parent().attr('id');
                    }
                    if (id == undefined) {
                        id = $(this).parent().parent().parent().attr('id');
                    }
                    if ($('#container').find('#'+id +' input[name='+name+']').attr('type') == 'hidden') {
                        $('#container').find('#'+id +' input[name='+name+']').replaceWith('');
                        return;
                    }
                    var val = $('#container').find('#'+id +' input[name='+name+']').val();
                    if (val == '') {
                        val = '\u00A0';
                    }
                    // 替换input的内容为html
                    var newDiv;
                    if ($.inArray(name,[ 'item_field_value', 'item_field_value_en']) !== -1) {
                        var clasa = '';
                        if ($(this).hasClass('color-yellow')) {
                            clasa = 'color-yellow';
                        }
                        newDiv = '<div class="item-label'+clasa+'" style="\n' +
                            '    height: 34px;margin-top: 8px;\n' +
                            '    display: inline-block;\n' +
                            '    text-align: unset;    font-size: 16px;\n' +
                            '    color: rgb(51, 51, 51);\n' +
                            '">'+val+'</div>';
                    } else {
                        var vlass = 'span-ch';
                        if (name == 'item_field_name_en') {
                            // 英文
                            vlass = 'span-en item_en'
                        }
                        newDiv = '<span class="'+ vlass +'">'+val+' </span>\n';
                    }
                    // 替换当前的input元素
                    $('#container').find('#'+id +' input[name='+name+']').replaceWith(newDiv);
                })
            }

            if (items == 'item_approval'){
                // 审批流，动态的更改下最外层的高度
               var heights = $('#'+items).find('ul').outerHeight();
                $('#'+items).css({
                    'height': 'auto'
                });
            } else if (items == 'item_point') {
                $('#'+items).find('.item-span-title').css({
                    'width': '100%'
                });
            }else if ($.inArray(items, ['item_open', 'item_virtus', 'item_document', 'item_screen']) !== -1) {
                $('#'+items).css({
                    'padding': 0,
                    'height': 'auto'
                });
                if (items == 'item_screen') {
                    // 截屏的话需要动态的调整下图片的高度，满足比例 4：3
                    changeScreenHeight();
                    twoOpenListenter();
                }
            }

            if (isEnCn !== 1) {
                // 不是中英文对照
                $('.item_en').html('&nbsp;');
                $('.item_en').removeClass('item-line');
            }
        }
    }

    // 监听截屏的两个按钮事件
    function twoOpenListenter(){
        // 验证主机打开
        $('#item_screen .open_verify_machine').off('click').on('click', function (){
            if (oldData.vm_uuid == '' || oldData.vnc_url == '') {
                UIToastr.showError(LANG.UI_VM_MACHINE_OPERATION, LANG.UI_PUBLIC_UNKNOWN_ERROR);
                return;
            }
            Metronic.blockUI({target: '#container',animate: true,cenrerY: true});
            pAjaxRequest({type:'look'}, "/api/v1/virtual/operate/"+oldData.vm_uuid, "POST", function (result) {
                Metronic.unblockUI('#container');
                if (result.code == 0) {
                    window.open(oldData.vnc_url, '_blank');
                } else {
                    UIToastr.showError(LANG.UI_VM_MACHINE_OPERATION, result.message);
                }
            });
        })
        // 验证生产打开
        $('#item_screen .open_prod_machine').off('click').on('click', function (){
            window.open('https://www.vinchin.com', '_blank');
        })
    }

    // 动态调整截屏下面的图片高度，满足比例 4：3
    function changeScreenHeight(){
        $('#item_screen').find('.screen-item-pic img').css({
            'height': '100%'
        });
        setTimeout(function (){
            var $screenItemPics = $('#item_screen').find('.screen-item-pic');
            $screenItemPics.each(function () {
                var $thisPic = $(this);
                var $images = $thisPic.find('img');

                if ($images.length === 0) {
                    return;
                }

                var minHeight = $images.toArray().reduce(function (min, img) {
                    var height = $(img).height();
                    return (height > 0 && height < min) ? height : min;
                }, Infinity);

                if (minHeight > 0) {
                    $thisPic.find('.shadow-box').css({
                        'height': minHeight,
                    });

                    if (status == 9 && pre != 1 && pre != 100) {
                        // not make
                        $thisPic.find('img').css({
                            'height': minHeight,
                        });
                    }

                    $thisPic.find('.shadow-box .labels').css({
                        'margin-top': (minHeight / 2 - 30) + 'px'
                    });
                }
            });
        }, 500);
    }

    // 编辑器监听事件
    function listenEditor(editor, id){
        var is_show = false;
        // 等待编辑器加载完成
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
                'height': 300
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
                        'height': 300
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
                        'height': 220
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
                    'height': 220
                });
            }).mouseleave(function(){
                // 鼠标离开编辑器区域时隐藏工具栏
                $('#' + id + '_editor').find('*').filter(function() {
                    return this.id && this.id.endsWith('_toolbarbox');
                }).hide();
                $('#' + id + '_editor').find('*').filter(function() {
                    return this.id && this.id.endsWith('_iframeholder');
                }).css({
                    'height': 300
                });
            });*/
        });
    }

    // 只保留截图验证的最后一个生产和验证的删除按钮
    function calcDel(){
        // 使用jQuery选择页面上最后一个具有'screen-desc'类的'textarea'元素
        $('.screen-item-pic .product .titles').find('i').remove(); // 移除所有的
        $('.screen-item-pic .verify .titles').find('i').remove(); // 移除所有的
        $('.screen-item-pic .screen-time').find('i').remove(); // 移除所有的

        if (status == 9 || ($.inArray(status, [3, 4]) !== -1 && oldData.is_now_user == true)) {
            $('.screen-item-pic').last().find('.screen-time').append('<i class="viconfont vicon-zujianshanchu screen-icon"></i>'); // 给最后一个增加删除图标
            $('.screen-item-pic').last().find('.product .titles').append('<i class="viconfont vicon-zujianshanchu screen-icon"></i>'); // 给最后一个增加删除图标
            $('.screen-item-pic').last().find('.verify .titles').append('<i class="viconfont vicon-zujianshanchu screen-icon"></i>'); // 给最后一个增加删除图标
        } else {
            // 移除截图的所有删除
            $('.screen-item-pic .screen-time').find('i').remove(); // 移除所有的
            // 移除截屏的按钮
            $('#item_screen .screen-pic-btn').remove();
        }

        // 增加截图的鼠标移入事件
        $(".screen-item-pic .product,.screen-item-pic .verify").mouseenter(function(){
            $(this).closest('.screen-item-pic').find('.shadow-box').show(); // 显示遮罩
        });
        $(".screen-item-pic").mouseleave(function(){
            $(this).closest('.screen-item-pic').find('.shadow-box').hide(); // 隐藏遮罩
        });

        // 增加监听点击事件
        $(".screen-item-pic .shadow-box").off().on('click', function (){
            var doms = $(this).closest('.screen-item-pic');
            var pic1 = doms.find('.product img').attr('src');
            var pic2 = doms.find('.verify img').attr('src');
            var pictime = doms.find('.screen-time-value').html();
            var pic1_div = '',pic2_div = '';
            if (pic1 != undefined) {
                pic1_div = `<img src="`+pic1+`">
                            <div class="title-img">`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_PRODUCTS+`</div>`;
            }
            if (pic2 != undefined) {
                pic2_div = `<img src="`+pic2+`">
                            <div class="title-img">`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_VERIFYS+`</div>`;
            }

            var title = doms.find('.screen-time input').val();

            var htmls = `<div class="full-screen-block">
                            <div class="close-btn">
                                <i class="viconfont vicon-tuichuquanping"></i><div class="text-title">`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_OUT+`</div>
                            </div>
                            <div class="content-div">
                                <div class="title">`+title+`</div>
                                    <div class="contnt-img">
                                        `+pic1_div+`
                                         `+pic2_div+`
                                    </div>
                                </div>
                            </div>
                        </div>`;
            $('#container').append(htmls);
            // 增加关闭事件
            $('.full-screen-block .close-btn').off('click').on('click', function() {
                $('.full-screen-block').remove();
            });
        })

        addListenerDel();
    }

    // 删除按钮事件
    function addListenerDel(){
        // 绑定删除事件
        // 截屏时间那删除是删除整个，生产和验证删除是删除对应的
        $('.screen-item-pic .product .titles').find('i').off('click').on('click', function() {
            // 生产截屏删除事件
            // 需要判断下同级的验证截屏是否还存在，不存在就删除整个
            if ($(this).parent().parent().parent().find('.verify').length > 0) {
                $(this).parent().parent().remove();
                initScreenTag = 2;
               // console.log('initScreenTag', initScreenTag)
            } else {
                $(this).parent().parent().parent().remove();
                calcDel();
                initScreenTag = 0;
            }
        })
        $('.screen-item-pic .verify .titles').find('i').off('click').on('click', function() {
            // 验证截屏删除事件
            // 需要判断下同级的生产截屏是否还存在，不存在就删除整个
            if ($(this).parent().parent().parent().find('.product').length > 0) {
                $(this).parent().parent().remove();
                initScreenTag = 1;
               // console.log('initScreenTag', initScreenTag)
            } else {
                $(this).parent().parent().parent().remove();
                calcDel();
                initScreenTag = 0;
            }
        })
        $('.screen-item-pic .screen-time>i').off('click').on('click', function() {
            // 整个截屏删除事件
            $(this).parent().parent().remove();
            initScreenTag = 0; // 只有最后一组才有全部删除
            calcDel();
        })
    }

    // 添加截屏按钮事件
    function addListenerScreen(uuid){
        var param = {report_uuid: uuid};
        $('#productScreen').off('click').on('click', function() {
          // 生产截屏事件
            if (initScreenTag == 1) {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_PRODUCT, LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_EXISTS);
                return;
            }

            // 请求接口，组装返回数据
            Metronic.blockUI({target: '.screen-pic-btn',animate: true,cenrerY: true});
            pAjaxRequest(param, "/api/v1/industry/screenshot/produce", "POST", function (result) {
                Metronic.unblockUI('.screen-pic-btn');
                if (result.code == 0) {
                    // 数据渲染
                    var data = result.data;
                    var html = '';
                    if (initScreenTag == 2) {
                        // 存在验证截屏
                        initScreenTag = 0;
                        html = getScreenHtml(data.url ,1);
                        $('.screen-item-pic').last().find('.verify').before(html);
                    } else {
                        // 都不存在，那么存在生产截屏
                        initScreenTag = 1;
                        var pa = [{
                            'product': data.url,
                            'verify': '',
                            'remark': '',
                            'create_time': data.create_time
                        }];
                        html = getScreenHtml(pa);
                        $('#item_screen .open-result-title-div').append(html);
                    }
                    // 截屏的话需要动态的调整下图片的高度，满足比例 4：3
                    changeScreenHeight();
                    calcDel();
                } else {
                    UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_PRODUCT, result.message);
                }
            });
        })

        $('#verifyScreen').off('click').on('click', function() {
            // 验证截屏事件
            if (initScreenTag == 2) {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_VERIFY, LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_EXISTS2);
                return;
            }

            // 请求接口，组装返回数据
            Metronic.blockUI({target: '.screen-pic-btn',animate: true,cenrerY: true});
            pAjaxRequest(param, "/api/v1/industry/screenshot/verify", "POST", function (result) {
                Metronic.unblockUI('.screen-pic-btn');
                if (result.code == 0) {
                    // 数据渲染
                    var data = result.data;
                    var html = '';
                    if (initScreenTag == 1) {
                        // 存在生产截屏
                        initScreenTag = 0;
                        html = getScreenHtml(data.url ,2);
                        $('.screen-item-pic').last().find('.product').after(html);
                    } else {
                        // 都不存在，那么存在验证截屏
                        initScreenTag = 2;
                        var pa = [{
                            'verify': data.url,
                            'product': '',
                            'remark': '',
                            'create_time': data.create_time
                        }];
                        html = getScreenHtml(pa);
                        $('#item_screen .open-result-title-div').append(html);
                    }
                    // 截屏的话需要动态的调整下图片的高度，满足比例 4：3
                    changeScreenHeight();
                    calcDel();
                } else {
                    UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_VERIFY, result.message);
                }
            });
        })

        $('#allScreen').off('click').on('click', function() {
            // 一键截屏事件
            if (initScreenTag == 1) {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_ALL, LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_EXISTS_ALL);
                return;
            }
            if (initScreenTag == 2) {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_ALL, LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_EXISTS2_ALL);
                return;
            }
            // 请求接口，组装返回数据
            Metronic.blockUI({target: '.screen-pic-btn',animate: true,cenrerY: true});
            pAjaxRequest(param, "/api/v1/industry/screenshot", "POST", function (result) {
                Metronic.unblockUI('.screen-pic-btn');
                if (result.code == 0) {
                    // 数据渲染
                    var data = result.data;
                    var html = '';
                    var pa = [{
                        'verify': data.verify_url,
                        'product': data.produce_url,
                        'remark': '',
                        'create_time': data.create_time
                    }];
                    html = getScreenHtml(pa);
                    $('#item_screen .open-result-title-div').append(html);
                    // 截屏的话需要动态的调整下图片的高度，满足比例 4：3
                    changeScreenHeight();
                    calcDel();
                } else {
                    UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_ALL, result.message);
                }
            });
        })
    }

    // 获取所有的自定义的内容
    function getDiyValue(type = 1){
        let check = true;
        let msg = '基本信息必须填写';
        $('.draggable').each(function() {
            var id = $(this).attr('id');
            var name = $(this).attr('data-field');
            var val = $(this).find('input[name="item_field_name"]').val();
            var en_val = '',value = '';
            if (isEnCn == 1) {
                en_val = $(this).find('input[name="item_field_name_en"]').val();
            }
            value = $(this).find('input[name="item_field_value"]').val();
            if (name == 'item_textarea') {
                value = $(this).find('textarea[name="item_field_value"]').val();
            }

            if (value == '' && type == 1) {
                check = false;
                msg = '请填写' + val;
                return false;
            }

            // 替换对应的
            for(var i in itemPositionArr) {
                if (itemPositionArr[i].id == id) {
                    if (val != undefined || value != undefined || en_val != undefined) {
                        if (val != undefined) {
                            itemPositionArr[i]['val'] = val;
                        }
                        if (en_val != undefined) {
                            itemPositionArr[i]['en_val'] = en_val;
                        }
                        if (value != undefined) {
                            itemPositionArr[i]['value'] = value;
                        }
                    }
                }
            }
        });
        !check && UIToastr.showWarning(LANG.UI_PLATFORM_INDUSTRY_REPORT_MAKE, msg);
        return check;
    }

    // 生成uuid
    var getUuid = function () {
        var len = 36;//36长度
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if (len) {
            for (i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
        } else {
            var r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for (i = 0; i < 36; i++) {
                if (!uuid[i]) {
                    r = 0 | Math.random() * 16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    }

    // 点击查看病毒查杀
    function lookMoreVirtus(){
        // 打开 drawer
        $('#drawer-virtus').drawer('show');
        var table_docum = $('#virtus-table');
        if (initScreenTable) {
            // 刷新
            table_docum.bootstrapTable('refresh', {
                query: {offset:0}
            });
        } else {
            // 初始化
            var options = {
                toolbarId: '#vin_job_report_virtus_toolbar',
                pagination:true,
                pageList: [5,10,25,50],
                detailView:false,
                resizable: false,
                vin_url:"/api/v1/industry/report/"+ oldData.report_uuid+"/virtus",
                vin_method:"GET",
                showExport: false, //是否开启导出按钮
                showColumns: false, //是否开启列选择按钮
                searchInput: true, //搜索框
                searchClass: 'job-virtus-search',
                searchSelector: '.job-virtus-search', //使用哪个搜索框
                searchOnEnterKey:true, //回车搜索
                placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
                columns:[
                    {
                        field: 'num',
                        title: LANG.UI_PUBLIC_TABLE_ID,
                        sortable: false,
                        align: 'left',
                    },
                    {
                        field: 'file',
                        title: LANG.UI_DATA_FILE_FILE,
                        sortable: false,
                        align: 'left',
                    },
                    {
                        field: 'desc',
                        title: LANG.UI_ALARM_DETAILS,
                        sortable: false,
                        align: 'left',
                    },
                    {
                        field: 'status',
                        title: LANG.UI_PLATFORM_INDUSTRY_RESULTS,
                        sortable: false,
                        align: 'left',
                        formatter: function (value, row, index){
                            var classs_name = row.status == 1 ? 'normal-td' : 'warnning-td';
                            return  '<span class="'+classs_name+'">'+ row.status_value+'</span>';
                        }
                    },
                ],
                PostBody: function () {
                    table_docum.find('th[data-field="num"]').css('width','5%');
                    table_docum.find('th[data-field="file"]').css('width','40%');
                    table_docum.find('th[data-field="desc"]').css('width','40%');
                    table_docum.find('th[data-field="status"]').css('width','15%');
                    initTableHeight();
                }
            }

            table_docum.bootstrapTable('destroy').baseTableConfig().init(options);
            initScreenTable = true;
        }
    }

    // 点击查看更多文件比对
    function lookMoreDocument(){
        // 打开 drawer
        $('#drawer-document').modal();
        $('#drawer-document').parent().css({
            'z-index': '10086'
        });
        var table_docum = $('#document-table');
        if (initDocumentTable) {
            // 刷新
            table_docum.bootstrapTable('refresh', {
                query: {offset:0}
            });
        } else {
            var num = '';
            var name = '';
            var encryption = '';
            var attribute = '';
            var size = '';
            var create_time = '';
            var modify_time = '';
            var result = '';
            if (isEnCn == 1) {
                // 是中英文版本，需要显示英文
                num = '</br>num';
                name = '</br>Source files & backup files';
                encryption = '</br>Verification value';
                attribute = '</br>Attributes';
                size = '</br>Files Size';
                create_time = '</br>Create Time';
                modify_time = '</br>Modify Time';
                result = '</br>Result';
            }
            var colums = [
                {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID + num,
                    sortable: false,
                    align: 'left',
                },
                {
                    field: 'name',
                    title: LANG.UI_PLATFORM_INDUSTRY_PRODUCT_AND_VERIFY_FILES + name,
                    sortable: false,
                    align: 'left',
                    formatter: function (value, row, index){
                        var class1 = '',class2 = '';
                        if (row.status != 1) {
                            class1 = 'document-normal';
                            class2 = 'document-error';
                        }
                        return  ' <span class="'+class1+'">'+ row.produce.name + '</span></br><span class="'+class2+'">' + row.verify.name + '</span>';
                    }
                },
                {
                    field: 'encryption',
                    title: LANG.UI_PLATFORM_INDUSTRY_VERIFY_VALUE + encryption,
                    sortable: false,
                    align: 'left',
                    formatter: function (value, row, index){
                        var class1 = '',class2 = '';
                        if (row.status != 1) {
                            class1 = 'document-normal';
                            class2 = 'document-error';
                        }
                        return  ' <span class="'+class1+'">'+ row.produce.encryption + '</span></br><span class="'+class2+'">' + row.verify.encryption + '</span>';
                    }
                },
            ];
            if (oldData.document_custom.file_position == true) {
                colums.push({
                    field: 'size',
                    title: LANG.UI_PLATFORM_INDUSTRY_FILES_ATTR + attribute,
                    sortable: false,
                    align: 'left',
                    formatter: function (value, row, index){
                        var class1 = '',class2 = '';
                        if (row.status != 1) {
                            class1 = 'document-normal';
                            class2 = 'document-error';
                        }
                        return  ' <span class="'+class1+'">'+ row.produce.attribute + '</span></br><span class="'+class2+'">' + row.verify.attribute + '</span>';
                    }
                },);
            }
            if (oldData.document_custom.file_size == true) {
                colums.push({
                    field: 'size',
                    title: LANG.UI_PLATFORM_INDUSTRY_FILES_SIZE + size,
                    sortable: false,
                    align: 'left',
                    formatter: function (value, row, index){
                        var class1 = '',class2 = '';
                        if (row.status != 1) {
                            class1 = 'document-normal';
                            class2 = 'document-error';
                        }
                        return  ' <span class="'+class1+'">'+ row.produce.size + '</span></br><span class="'+class2+'">' + row.verify.size + '</span>';
                    }
                },);
            }
            if (oldData.document_custom.file_create == true) {
                colums.push({
                    field: 'create_time',
                    title: LANG.UI_PUBLIC_CREATE_TIME + create_time,
                    sortable: false,
                    align: 'left',
                    formatter: function (value, row, index){
                        var class1 = '',class2 = '';
                        if (row.status != 1) {
                            class1 = 'document-normal';
                            class2 = 'document-error';
                        }
                        return  ' <span class="'+class1+'">'+ row.produce.create_time + '</span></br><span class="'+class2+'">' + row.verify.create_time + '</span>';
                    }
                },);
            }
            if (oldData.document_custom.file_update == true) {
                colums.push( {
                    field: 'modify_time',
                    title: LANG.UI_SETTING_MODIFY_TIME + modify_time,
                    sortable: false,
                    align: 'left',
                    formatter: function (value, row, index){
                        var class1 = '',class2 = '';
                        if (row.status != 1) {
                            class1 = 'document-normal';
                            class2 = 'document-error';
                        }
                        return  ' <span class="'+class1+'">'+ row.produce.modify_time + '</span></br><span class="'+class2+'">' + row.verify.modify_time + '</span>';
                    }
                },);
            }
            colums.push({
                field: 'status',
                title: LANG.UI_PLATFORM_INDUSTRY_RESULTS + result,
                sortable: false,
                align: 'left',
                formatter: function (value, row, index){
                    var classs_name = row.status == 1 ? 'normal-td' : 'warnning-td';
                    var i = ' <i class="viconfont vicon-tongguo-copy"></i>';
                    if (row.status != 1) {
                        i = '  <i class="viconfont vicon-error-warning-fill"></i>';
                    }
                    return  '<span class="'+classs_name+'">' + i+ row.status_value+'</span>';
                }
            },)
            // 初始化
            var options = {
                toolbarId: '#vin_job_report_document_toolbar',
                pagination:true,
                pageList: [5,10,25,50],
                detailView:false,
                resizable: false,
                vin_url:"/api/v1/industry/report/"+ oldData.report_uuid+"/document",
                vin_method:"GET",
                showExport: false, //是否开启导出按钮
                showColumns: false, //是否开启列选择按钮
                searchInput: true, //搜索框
                searchClass: 'job-search',
                searchSelector: '.job-search', //使用哪个搜索框
                searchOnEnterKey:true, //回车搜索
                placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
                columns:colums,
                PostBody: function () {
                    table_docum.find('th[data-field="num"]').css('width','5%');
                   // table_docum.find('th[data-field="name"]').css('width','30%');
                    table_docum.find('th[data-field="encryption"]').css('width','25%');
                    table_docum.find('th[data-field="size"]').css('width','8%');
                    table_docum.find('th[data-field="create_time"]').css('width','10%');
                    table_docum.find('th[data-field="modify_time"]').css('width','10%');
                    table_docum.find('th[data-field="status"]').css('width','5%');
                    initTableHeight();
                }
            }

            table_docum.bootstrapTable('destroy').baseTableConfig().init(options);
            initDocumentTable = true;
        }
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 381;

        $("#dragle_virtus .fixed-table-body").css({
            "height": height
        });

        $("#dragel_document .fixed-table-body").css({
            "height": height
        });
    }

    return {
        //main function to initiate the module
        init: function (options) {
            // 这里来设置是否中英文对照的展示
            if (CONF.ENTERPRISE == 'enterprise_en') {
                // 英文版本
                isEnCn = 0; // 模拟设置为开启中英文对照
            } else {
                // 中文版本
                isEnCn = 1;
            }
            itemPositionArr = [];
            $('#container').html(`<div class="report-title">
                                    <input type="text" class="form-control input-inline input-title-ch" id="report_title" value="`+LANG.UI_PLATFORM_INDUSTRY_REPORT_TITLE+`">
                                    <input type="text" class="form-control input-inline input-title-en item_en" id="report_title2" value="xxx Verification Report">
                                </div>
                                <div class="title-line"></div>
                                <div class="basic-info basic-title1s">
                                    `+LANG.UI_PLATFORM_INDUSTRY_BASIC_INFO+`
                                    <span class="item-line item_en"></span>
                                    <span class="item_en span-title-basic">BASIC INFORMATION</span>
                                </div>
                                <div class="basic-title-line display-visibility"></div>`);

            initListener();
            uuid = options.uuid;
            pre = options.pre;
            if (pre == 2) {
                if (itemPlanEditor != null) {
                    itemPlanEditor.destroy();
                    itemPlanEditor = null;
                }
                if (itemResultEditor != null) {
                    itemResultEditor.destroy();
                    itemResultEditor = null;
                }
            }
            if (options.agent_uuid != undefined) {
                agent_uuid = options.agent_uuid;
                source  = 2;
            } else {
                source = 1;
            }
            if (options.job_status != undefined) {
                _taskStatus = options.job_status
            } else {
                _taskStatus = 0;
            }
            if (options.history != undefined) {
                _history = options.history;
            } else {
                _history = 0;
            }
            initDocumentTable = false;
            initScreenTable = false;
            // 初始化详情
            initView(uuid);

            if (pre == 2) {
                // 配置
                $('#drawer-config').drawer('show');
            } else {
                // 预览 是还需要判断下一些异步的接口是否已经加载完毕了
               pre_loading = true;
               Metronic.blockUI({target: '.col-md-12',animate: true,cenrerY: true});
               var pre_time = setInterval(function (){
                   if (!pre_loading) {
                       Metronic.unblockUI('.col-md-12');
                       clearInterval(pre_time);
                       preListener();
                   }}
               , 1000);
            }
        }
    };
}();
