/**
 * CSS引用
 *	<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
 *	<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
 *	<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
 *	<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
 *
 * JS引用
 * <script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
 * <script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
 * <script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
 * <script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
 * <script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
 *
 */
(function($){
    // 全局的所有配置放这里，为了控制切换类型下面的配置更改
    var speedInfos = {1:[],2:[],3:[],4:[],5:[]};
    var check_type = 2;
    let module_type = '';
    $.fn.speedstrategy = function(options){
        var defaults = {
            //多维数组,每一项表示每一个策略配置
            //mode: 1,	1 normal
            //strategy_type: 2,	策略类型 1 每天 2 每周  3 每月 4永久 5自定义
            //days: [0, 0, 0, 0, 0, 1, 0],
            //start_time: '23:00:00',
            //end_time: '23:30:00'
            'config' : [],
            'display': ['', '', '', '', '', ''],	//是否显示,默认显示

        }
        module_type = options.module_type;
        var option = $.extend(defaults,options);
        //得到每一个 策略
        var getEachStrategy = function(config, i){
            var html = '<div class="panel panel-default strategy-panel" data-mode="' + config.mode + '" style="height: 460px;">' +
                getHead(config, i) +
                getBody(config, i) +
                '</div>';
            return html;
        }

        //得到panel-heading
        var getHead = function(config, i){
            var html = '<div class="panel-heading display-none">' +
                '<h4 class="panel-title">' +
                '<a class="accordion-toggle accordion-toggle-styled collapsed" ' +
                'data-toggle="collapse" data-parent="#speedstragegyaccordion" href="#collapsebody' + i + '">' +
                '<i class="viconfont vicon-ge_authorization2 font-green-seagreen"></i> ' +
                '<span class="speedstrategydes"></span></a>' +
                '</h4>' +
                '</div>';
            return html;
        }

        //得到panel-body
        var getBody = function(config, i){
            var html = '<div class=" " >' +
                '<div class="panel-body" style="padding: 5px 0;">' +
                '<div class="row">' +
                '<div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">' +
                '<ul class="nav nav-tabs tabs-left">';
            var radioDes = ['', LANG.UI_STRATEGY_DAY, LANG.UI_STRATEGY_WEEK_TITLE, LANG.UI_STRATEGY_MONTH, LANG.UI_STRATEGY_FOREVER,LANG.UI_STRATEGY_CUSTOM];
            //英文版描述
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                radioDes = ['', LANG.UI_STRATEGY_DAY_EN, LANG.UI_STRATEGY_WEEK_EN, LANG.UI_STRATEGY_MONTH_EN, LANG.UI_STRATEGY_FOREVER_EN, LANG.UI_STRATEGY_CUSTOM_EN];
            }
            var tabDes = ['', 'tabspeed_day', 'tabspeed_week', 'tabspeed_month', 'tabspeed_forever', 'tabspeed_custom'];
            for(var j=1; j<6; j++){
                //初始化每天每周每月radio
                var active = '', checked = '';
                if(config.strategy_type == j){
                    active = 'active';
                    checked = 'checked';
                }
                html += '<li class="dwm  ' + active + '" data-type="' + j + '">' +
                    '<a href="#' + tabDes[j] + i + '" data-toggle="tab">' +
                    '<input type="radio" data-radio="iradio_square-blue" ' + checked + ' class="icheck" name="bakradio' + i + '"/> ' + radioDes[j] + ' </a>' +
                    '</li>';
            }

            html += '</ul>' +
                '</div>' +
                '<div class="col-md-9 col-sm-9 col-xs-9" style="padding-left: 20px;">' +
                '<div class="tab-content" style="width: 328px;">' +
                getEachTab(config, i) +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';

            return html;
        }

        //得到每天,每周,每月
        var getEachTab = function(config, i){
            var html = getTabDay(config, i);
            html += getTabWeek(config, i);
            html += getTabMonth(config, i);
            html += getTabForever(config, i);
            html += getTabCustom(config, i);
            return html;
        }

        //每天
        var getTabDay = function(config, i){
            var thisConfig = JSON.parse(JSON.stringify(config));
            var active = 'active';
            if(1 != thisConfig.strategy_type){
                active = '';
                thisConfig.roll_flag = false;	//如果不是选择的本类型备份,使用默认不开启
            }
            var html = '<div class="tab-pane fade in ' + active + '" id="tabspeed_day' + i + '">' +
                getUnifyCentent(thisConfig, i) +
                '</div>';
            return html;
        }
        //每周
        var getTabWeek = function(config, i){
            var thisConfig = JSON.parse(JSON.stringify(config));
            //如果有设置就要用设置的值,如果没有就用默认的值
            var days = thisConfig.days, active = 'active';
            if(2 != thisConfig.strategy_type){
                //如果不是每周备份,就用默认的设置
                days = [0, 0, 0, 0, 1, 0, 0];
                active = '';
                thisConfig.roll_flag = false;	//如果不是选择的本类型备份,使用默认不开启
            }

            var html = '<div class="tab-pane fade in ' + active + '" id="tabspeed_week' + i + '">' +
                '<div class="form-group">' +
                '<label class="control-label col-md-2 form-group-top2-label" style="padding:0;line-height:34px;">' + LANG.UI_STRATEGY_WEEK_TITLE + '</label>' +
                '<div class="col-md-10">' +
                '<div class="input-group weekcheck">' +
                '<row>' +
                '<div>';

            for(var j=0; j<7; j++){
                var checked = '';
                var weedDes = [LANG.UI_STRATEGY_MONDAY , LANG.UI_STRATEGY_TUESDAY , LANG.UI_STRATEGY_WEDNESDAY , LANG.UI_STRATEGY_THURSDAY , LANG.UI_STRATEGY_FRIDAY , LANG.UI_STRATEGY_SATURDAY , LANG.UI_STRATEGY_SUNDAY];
                //初始化每周复选框
                if(days[j]){
                    checked = 'checked';
                }
                html += '<label style="padding-right: 12px;"><input type="checkbox" data-checkbox="icheckbox_square-blue" ' + checked + ' class="icheck"> ' + weedDes[j] + ' </label>';
            }

            html += '</div>' +
                '</row>' +
                '</div>' +
                '</div>' +
                '</div>' +
                getUnifyCentent(thisConfig, i) +
                '</div>';
            return html;
        }
        //每月
        var getTabMonth = function(config, i){
            var thisConfig = JSON.parse(JSON.stringify(config));
            //如果有设置就要用设置的值,如果没有就用默认的值
            var days = thisConfig.days, active = 'active';
            days = [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            if(3 != thisConfig.strategy_type){
                //如果不是每周备份,就用默认的设置
                days = [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                active = '';
                thisConfig.roll_flag = false;	//如果不是选择的本类型备份,使用默认不开启
            }
            var html = '<div class="tab-pane fade in ' + active + '" id="tabspeed_month' + i + '" style="overflow-y: scroll;">' +
                '<div class="form-group">' +
                '<label class="control-label col-md-2 form-group-top2-label" style="padding:0;line-height:34px;">' + LANG.UI_STRATEGY_MONTH + '</label>' +
                '<div class="col-md-10" style="padding-right: 0;">' +
                '<div class="input-group monthcheck">';

            for(var j=0; j<31; j++){
                var checked = '';
                if(days[j]){
                    checked = 'checked';
                }
                var thisDay = j + 1;
                if (thisDay < 10) {
                    thisDay = '0' + thisDay;
                }
                html += '<label class="label60" style="width: 48px;"><input type="checkbox" data-checkbox="icheckbox_square-blue" ' + checked + ' class="icheck"> ' + thisDay + ' </label>';
            }
            html += '</div></div>' + getUnifyCentent(thisConfig, i) + '</div></div>';
            return html;
        }

        //自定义
        var getTabCustom = function(config, i){
            var thisConfig = JSON.parse(JSON.stringify(config));
            var active = 'active';
            if(5 != thisConfig.strategy_type){
                active = '';
                thisConfig.roll_flag = false;	//如果不是选择的本类型备份,使用默认不开启
            }
            var html = '<div class="tab-pane fade in ' + active + '" id="tabspeed_custom' + i + '">' +
                getUnifyCentent(thisConfig, i, 1) +
                '</div>';
            return html;
        }

        //永久
        var getTabForever = function(config, i){
            var thisConfig = JSON.parse(JSON.stringify(config));
            var active = 'active';
            if(4 != thisConfig.strategy_type){
                active = '';
                thisConfig.roll_flag = false;	//如果不是选择的本类型备份,使用默认不开启
            }
            var html = '<div class="tab-pane fade in ' + active + '" id="tabspeed_forever' + i + '">' +
                getUnifyCentent(thisConfig, i, 2) +
                '</div>';
            return html;
        }
        //得到每天每周每月内容
        var getUnifyCentent = function(config, i, j) {
            var start_time = config.start_time;
            var end_time = config.end_time;
            // var roll_flag = config.roll_flag ? 'checked' : '';
            // var roll_interval = config.roll_interval;
            // var roll_end_time = config.roll_end_time;
            // var display = config.roll_flag ? '' : 'display-hide';
            let arr = [1, 2];
            let file_copy_tips = '';//文件复制限速为0的提示
            if (module_type == CONF.MODULE_TYPE.FILE_COPY && config.strategy_type != 4) {
                file_copy_tips = '<div><span class="help-block file-copy-tips">'+ LANG.UI_FILE_COPY_SPEED_TIPS +'</span></div>';
            }
            var html =
                ' <div class="form-group">' +
                '                        <label class="control-label col-md-2 form-group-label" style="padding:0;line-height:34px;">' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE +
                '                        </label>' +
                '                        <div class="col-md-9">' +
                '                            <div id="rateDiv" style="display:inline-flex;">' +
                '                                <div class="input-icon">' +
                '                                    <div class="speedSpinnerNum">' +
                '                                        <div class="input-group spinner-group" style="width: 151px;">' +
                '                                            <input style="text-align: left;" class="spinner-input form-control speedSpinnerNumInput">' +
                '                                            <div class="spinner-buttons input-group-btn spinner-group-btn">' +
                '                                                <button type="button" class="btn spinner-up default">' +
                '                                                    <i class="fa fa-angle-up"></i>' +
                '                                                </button>' +
                '                                                <button type="button" class="btn spinner-down default">' +
                '                                                    <i class="fa fa-angle-down"></i>' +
                '                                                </button>' +
                '                                            </div>' +
                '                                        </div>' +
                '                                    </div>' +
                file_copy_tips +
                '                                </div>' +
                '                                <div class="">' +
                '                                    <select class="unit"  style="width: 72px; margin-left: 10px;height: 33px;text-align:center; border: 1px solid #E6E6E6;">' +
                '                                        <option value="1">KB/s</option>' +
                '                                        <option selected value="2">MB/s</option>' +
                '                                        <option value="3">GB/s</option>' +
                '                                    </select>' +
                '                                </div>' +
                '                                <a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="' + LANG.UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS + '"' +
                '                                    <i class="viconfont vicon-tishi"></i>' +
                '                                </a>' +
                '                            </div>' +
                '                        </div>' +
                '                    </div>';
            if (!arr.includes(j)) {
                // 是之前的
                html += '<div class="form-group">' +
                    '<label class="control-label col-md-2 form-group-label" style="padding:0;line-height:34px;">' + LANG.UI_PUBLIC_START_TIME + '</label>' +
                    '<div class="col-md-6 form-group-content">' +
                    '<div class="input-group" style="width: 151px;">' +
                    '<input type="text" value="' + start_time + '" class="form-control timepicker timepicker-24 speedstarttime">' +
                    '<span class="input-group-btn">' +
                    '<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>' +
                    '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group endtimeDiv">' +
                    '<label class="control-label col-md-2 form-group-label" style="padding:0;line-height:34px;">' + LANG.UI_PUBLIC_END_TIME + '</label>' +
                    '<div class="col-md-6 form-group-content" style="width:246px;">' +
                    '<div class="input-group" style="float: left;max-width: 81%;width: 151px;">' +
                    '<input type="text" value="' + end_time + '" class="form-control timepicker timepicker-24 speedendtime">' +
                    '<span class="input-group-btn">' +
                    '<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>' +
                    '</span>' +
                    '</div>' +
                    ' <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right"\n' +
                    '                       data-content="'+ LANG.UI_GLOBAL_STRATEGY_SPEED_TIME_TIPS +'" data-original-title="" title="" style="float: left;">\n' +
                    '                        <i class="viconfont vicon-tishi"></i>\n' +
                    '                    </a>' +
                    '</div>' +
                    '</div>';
            } else if (j == 1) {
                var nowDate = getNowDate();
                // 自定义 是日期
                html += '<div class="form-group">' +
                    '<label class="control-label col-md-2 form-group-label" style="padding:0;line-height:34px;">' + LANG.UI_PUBLIC_START_TIME + '</label>' +
                    '<div class="col-md-9 form-group-content">' +
                    '<div class="input-group date form_datetime_speed">' +
                    '<input type="text" value="'+nowDate +' 23:00:00" class="form-control speedstarttime2">' +
                    '<span class="input-group-btn">' +
                    '<button class="btn default date-set" type="button" style="height: 34px"><i class="viconfont vicon-rili"></i></button>' +
                    '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="form-group endtimeDiv">' +
                    '<label class="control-label col-md-2 form-group-label" style="padding:0;line-height:34px;">' + LANG.UI_PUBLIC_END_TIME + '</label>' +
                    '<div class="col-md-9 form-group-content">' +
                    '<div class="input-group date form_datetime_speed">' +
                    '<input type="text" value="'+nowDate +' 23:30:00" class="form-control speedendtime2">' +
                    '<span class="input-group-btn">' +
                    '<button class="btn default date-set" type="button" style="height: 34px"><i class="viconfont vicon-rili"></i></button>' +
                    '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            }

            // if (j != 2) {
            // 加上添加按钮
            html += '<div class="form-group">' +
                '<label class="control-label col-md-2 form-group-label""></label>' +
                '<div class="col-md-9 form-group-content"><button type="button" class="btn btn-sm green-haze add_speed_item">'+
                '<i class="viconfont vicon-ge_add_task"></i> '+LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD+'							</button></div></div>';
            // }

            return html;
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

        //添加事件
        var addListeners = function(_this){
            $(_this).find('.speedstarttime').timepicker({
                autoclose: true,
                minuteStep: 5,
                showSeconds: true,
                showMeridian: false,
                //              defaultTime:'00:00:00'
            });
            $(_this).find('.speedendtime').timepicker({
                autoclose: true,
                minuteStep: 5,
                showSeconds: true,
                showMeridian: false,
                //              defaultTime:'23:59:59'
            });

            initDatetimePicker();
            // 限速策略
            $('.speedSpinnerNum').spinner({ value: 10, step: 5, min: 1, max: 9999 });

            //添加限速策略确定
            $(_this).find('.add_speed_item').on('click', speedSubmitItem);

            // handle input group button click
            $(_this).find('.timepicker').parent('.input-group').on('click', '.input-group-btn', function(e){
                e.preventDefault();
                $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
            });

            //初始化ickeck
            _this.find('.icheck').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue strategy-radio-custom',
                increaseArea: '16.6%' // optional
            });

            //提示初始化
            _this.find(".popovers").popover();

            //每天每周每月点击事件
            _this.find(".dwm").on('click', function(){
                $(this).find('.icheck').iCheck('check');
            });

            //每天每周每月radio选中事件
            _this.find(".dwm").on('click', function(){
                $(this).closest('ul').find('li').removeClass('active');
                $(this).closest('li').addClass('active');

                $(this).closest('.panel-body').find('.tab-pane').removeClass('active');
                var activeTabPane = $(this).closest('li').find('a').get(0).hash;
                $(activeTabPane).addClass('in active');
                strategyChange(this, 1);
            });

            //时间改变
            _this.find('.panel-body').find('.timepicker-24').on('change', function(){
                strategyChange(this);
            });

            //速度改变
            _this.find('.panel-body').find('.speedSpinnerNumInput').on('change', function(){
                strategyChange(this);
            });

            _this.find('.panel-body').find('.spinner-up').on('click', function(){
                strategyChange(this);
            });

            _this.find('.panel-body').find('.spinner-down').on('click', function(){
                strategyChange(this);
            });

            _this.find('.panel-body').find('.unit').on('change', function(){
                strategyChange(this);
            });

            //每周 每月 icheck改变
            _this.find('.tab-content').find('.icheck').on('ifChanged', function(event){
                var checks = $(this).closest('.input-group').find('.icheck');
                if(!event.target.checked){
                    //如果是取消选中,需要检查是不是最后一个取消的,最后一个不能取消,因为需要默认一个
                    var checkedCount = 0;
                    for(var i=0; i<checks.length; i++){
                        if(checks[i].checked){
                            checkedCount++;
                        }
                    }
                    if(0 == checkedCount){
                        //如果是最后一个,再选中,这里有点问题,因为实在回调函数内部,永远都会取消样式,所以这里延迟设置
                        var e = this;
                        setTimeout(function(){
                            $(e).iCheck('check');
                        },50);
                    }
                }
                strategyChange(this);
            });

        }

        // 添加一个策略配置
        var speedSubmitItem = function (){
            var strategyConfig = $('#speedstrategy').getSpeedStrategyConfig();
            // console.log(strategyConfig)
            // 进行数据输出
            if (strategyConfig.type == 4 && speedInfos[strategyConfig.type].length > 0) {
                UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD_FOREVER_TIPS);
                return false;
            }
            // 需要进行去重判读
            var checkConfig = speedInfos[strategyConfig.type];
            for (var z in checkConfig) {
                if (strategyConfig.des == checkConfig[z].des) {
                    UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_GLOBAL_STRATEGY_SPEED_EXIST);
                    return false;
                }
            }
            if (!strategyConfig) {
                return;
            }
            speedInfos[strategyConfig.type].push(strategyConfig);
            // 取出所有的当前模型的配置
            var titleDes = "";
            var des2 = "";
            let array = [];
            let speedInfosArr = speedInfos[strategyConfig.type];
            for (const j in speedInfosArr) {
                array.push(speedInfosArr[j]);
                titleDes += speedInfosArr[j].des + '. ';
            }
            if(array.length !=0){
                des2 += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + array.length;
            }
            // 显示策略
            const info = strategyConfig;
            var liId = info.uuid;

            var des =
                '<li class="list-group-item popovers speedTips list-group-item__speed" id="speed' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + info.des + '">' +
                '<div class="col1">' +
                '<div class="cont ">' +
                '<div class="cont-col1"></div>' +
                '<div class="cont-col2">' +
                '<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col2  pull-right delete-list">' +
                '<a class="del' + liId + '"  data-type="'+ info.type +'">'+
                '<div class="label label-sm label-danger" style="padding:0;line-height:34px;">' +
                '<i class="viconfont vicon-cuowu"></i>' +
                '</div>' +
                '</a>' +
                '</div>' +
                '</li>';

            $('#speedList').append(des);
            $('.speedTips').popover();	   //初始化tips
            $('#speedItemSet').show();

            $('.speedlimitDes').html(des2);
            $('.speedlimitDes').prop('title', titleDes);

            $('.del' + liId).on('click', function () {
                $('.popover.in').remove();
                $('#speed' + liId).remove();
                var type = $(this).attr('data-type');

                var titleDes = "";
                var des2 = "";

                var speedList = speedInfos[type];
                for (var i in speedList) {
                    if (liId == speedList[i].uuid) {
                        speedList.splice($.inArray(speedList[i], speedList), 1);
                    }
                }
                speedInfos[type] = speedList;
                if (speedList.length <= 0) {
                    $('#speedItemSet').hide();
                }else {
                    $('#speedItemSet').show();
                }

                if(speedList.length !=0){
                    des2 += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
                }
                for (const j in speedList) {
                    titleDes += speedList[j].des + '. ' + "<br>";
                }

                $('.speedlimitDes').html(des2);
                $('.speedlimitDes').prop('title', titleDes);
            });
        };

        //得到日期
        var getDays = function(strategy_type, tabPane){
            var days = [];
            var checkGroup = '';
            if(2 == strategy_type){
                //每周
                checkGroup = '.weekcheck';
            }else if(3 == strategy_type){
                //每月
                checkGroup = '.monthcheck';
            }else{
                //每天
                return days;
            }
            var input = tabPane.find(checkGroup).find('input');
            input.each(function(i, d){
                if(d.checked){
                    days[i] = 1;
                }else{
                    days[i] = 0;
                }
            });
            return days;
        }

        //添加策略选择改变事件
        var strategyChange = function(element, types = 0){
            var strategyPanel = $(element).closest('.strategy-panel');
            var settings = {};
            settings.mode = strategyPanel.data('mode');
            settings.strategy_type = strategyPanel.find('.dwm.active').data('type');
            var tabPane = strategyPanel.find('.tab-pane.active');

            if (settings.strategy_type == 5) {
                // 是自定义的日期
                settings.start_time = formatTimeString(tabPane.find('.speedstarttime2').val());
                settings.end_time = formatTimeString(tabPane.find('.speedendtime2').val());
            } else if (settings.strategy_type == 4) {
                // 永久
                settings.start_time = 0;
                settings.end_time = 0;
            } else {
                settings.start_time = formatTimeString(tabPane.find('.speedstarttime').val());
                settings.end_time = formatTimeString(tabPane.find('.speedendtime').val());
            }

            // 获取下速度相关的信息
            // 获取限速大小
            // 速度单位
            settings.speedUnit = getSpeedUnit(tabPane);
            settings.speedNum = parseInt(tabPane.find('.speedSpinnerNumInput').val());
            switch (module_type) {
                case CONF.MODULE_TYPE.FILE_COPY:
                    if (settings.strategy_type == 4) {
                        //永久限速不允许为0，并屏蔽相关文字提示
                        $('.help-block.file-copy-tips').hide();
                    } else {
                        $('.help-block.file-copy-tips').show();
                    }
                    break;
                default:
                    if (!settings.speedNum || settings.speedNum <= 0) {
                        UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_BACKUP_SPEED_LIMIT_TIPS);
                        //return false;
                    }
                    break;
            }
            settings.value = settings.speedNum * settings.speedUnit;
            settings.unit = tabPane.find('option:selected').text();

            settings.days = getDays(settings.strategy_type, tabPane);
            setStrategyDes(strategyPanel, settings);
            if (types > 0) {
                setStrategyTips(settings.strategy_type);
            }
        }

        // 改变类型显示事件
        var setStrategyTips = function (strategy_type) {
            check_type = strategy_type
            $('.speedTips').popover();	   //初始化tips
            var des = '';
            $('#speedList').html(des);
            // 表示是左边的类型切换 那么需要重新计算下配置信息的展示
            var speedInfosType = speedInfos[strategy_type];
            var titleDes = "";
            var des = "";
            for (var j in speedInfosType) {
                const info = speedInfosType[j];
                des +=
                    '<li class="list-group-item popovers speedTips list-group-item__speed" id="speed' + info.uuid + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + info.des + '">' +
                    '<div class="col1">' +
                    '<div class="cont ">' +
                    '<div class="cont-col1"></div>' +
                    '<div class="cont-col2">' +
                    '<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col2  pull-right delete-list">' +
                    '<a class="del' + info.uuid + '"  data-type="'+ info.type +'">'+
                    '<div class="label label-sm label-danger" style="padding:0;line-height:34px;">' +
                    '<i class="viconfont vicon-cuowu"></i>' +
                    '</div>' +
                    '</a>' +
                    '</div>' +
                    '</li>';
                titleDes += info.des + '. ' + "<br>";
            }
            $('#speedList').append(des);
            des = '';
            if (speedInfosType.length> 0) {
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedInfosType.length;
            }

            if (check_type == 4) {
                // 永久的话，只会有一条
                // des = LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": 1";
                // 那么就读取速度即可
                // var strategyConfig = $('#speedstrategy').getSpeedStrategyConfig();
                // 进行数据输出
                // titleDes = strategyConfig.des;
            }
            $('.speedlimitDes').html(des);
            $('.speedlimitDes').prop('title', titleDes);

            if (speedInfosType.length <= 0) {
                $('#speedItemSet').hide();
            }else {
                for(const i in speedInfosType) {
                    const info = speedInfosType[i];
                    const liId = info.uuid;
                    $('.del' + liId).on('click', function () {
                        $('.popover.in').remove();
                        $('#speed' + liId).remove();
                        //console.log('liId' + liId);
                        var type = $(this).attr('data-type');
                        var titleDes = "";
                        var des2 = "";

                        var speedList = speedInfos[type];
                        for (var i in speedList) {
                            if (liId == speedList[i].uuid) {
                                speedList.splice($.inArray(speedList[i], speedList), 1);
                            }
                        }
                        speedInfos[type] = speedList;
                        if (speedList.length <= 0) {
                            $('#speedItemSet').hide();
                        }else {
                            $('#speedItemSet').show();
                        }

                        if(speedList.length !=0){
                            des2 += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
                        }
                        for (const j in speedList) {
                            titleDes += speedList[j].des + '. ' + "<br>";
                        }

                        $('.speedlimitDes').html(des2);
                        $('.speedlimitDes').prop('title', titleDes);
                    });
                }
                $('#speedItemSet').show();
            }
        }

        //设置策略描述
        var setStrategyDes = function(strategyPanel, settings){
            var modeDes = [''];
            var typeDes = ['', LANG.UI_STRATEGY_DAY, LANG.UI_STRATEGY_WEEK, LANG.UI_STRATEGY_MONTH, LANG.UI_STRATEGY_FOREVER, LANG.UI_STRATEGY_CUSTOM];
            var weekDes = ['', LANG.UI_STRATEGY_MONDAY, LANG.UI_STRATEGY_TUESDAY, LANG.UI_STRATEGY_WEDNESDAY, LANG.UI_STRATEGY_THURSDAY, LANG.UI_STRATEGY_FRIDAY, LANG.UI_STRATEGY_SATURDAY, LANG.UI_STRATEGY_SUNDAY];
            var des = '';
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                //英文版
                typeDes = ['', LANG.UI_STRATEGY_DAY, LANG.UI_STRATEGY_EVERY, LANG.UI_STRATEGY_MONTH, LANG.UI_STRATEGY_FOREVER, LANG.UI_STRATEGY_CUSTOM];
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_AS_SCHEDULED + " (";
            }else{
                //中文版
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT + " (";
            }
            if (settings.strategy_type != 4) {
                // 不是永久限速
                des += typeDes[settings.strategy_type];
            }

            if (settings.strategy_type != 4) {
                for(var i=0; i<settings.days.length; i++){
                    if(1 == settings.strategy_type || 5 == settings.strategy_type) break;	//每天的话不读取days
                    if(settings.days[i]){
                        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                            //英文版
                            if(2 == settings.strategy_type){
                                des += weekDes[i+1] + ", ";
                            }else{
                                des += LANG.UI_EN_DAY + (i + 1) + ", ";
                            }
                        }else{
                            //中文版
                            des += (i + 1) + ", ";
                        }
                    }
                }
            }

            if (settings.strategy_type == 4) {
                // 永久限速
                des += LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER;
            } else {
                des += settings.start_time + LANG.UI_STRATEGY_START + ", ";
                des += settings.end_time + LANG.UI_STRATEGY_END ;
            }

            des += ")";
            // 设置下默认的速度和单位
            if (settings.speedNum === '' || settings.speedNum == undefined) {
                settings.speedNum = 10;
                settings.unit = "MB/s";
            }
            des += ', ' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE + ':' + settings.speedNum + settings.unit;
            strategyPanel.find('.speedstrategydes').html(des);
        }

        var formatTimeString = function(str) {
            if (!str) return;
            var hour = str.split(':')[0];
            if (Number(hour) < 10) {
                return `0${hour}:${str.split(':')[1]}:${str.split(':')[2]}`;
            } else {
                return str;
            }
        }

        //初始化设置描述
        var initSettingDes = function(config){
            var strategyPanel = $("#speedstragegyaccordion").find('.strategy-panel');
            for(var i=0; i<config.length; i++){
                if (config[i]['strategy_type'] == 5) {
                    // 自定义的是日期 不是时间
                    config[i]['start_time'] = $('#tabspeed_custom0 .speedstarttime2').val();
                    config[i]['end_time'] = $('#tabspeed_custom0 .speedendtime2').val();
                }
                // console.log(config[i])
                setStrategyDes($(strategyPanel[i]), config[i]);
            }
        }

        // 初始化策略设置信息
        var initSettingSet = function (config) {
            // 所有的都给干掉
            speedInfos = {1:[],2:[],3:[],4:[],5:[]};
            for (var i in config) {
                let info = config[i]
                check_type = info.strategy_type
                if (info.speedInfo && info.speedInfo.length>0) {
                    speedInfos[check_type] = info.speedInfo;
                }
                // 手动触发一次改变类型改变事件
                setStrategyTips(check_type);
            }
        }

        var initDatetimePicker = function (timePicker = '.form_datetime_speed', callback = null) {
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
                //			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
            });


            //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
            $(timePicker).on('apply.daterangepicker', function (ev, picker) {
                //给全局变量赋值,然后设置input
                $(this).find('input').val('' + picker.startDate.format('YYYY-MM-DD HH:mm:ss') + '');
                strategyChange($(this).find('input'));
                if (!callback) {
                    return;
                }
                callback();
            });

            $(timePicker).on('cancel.daterangepicker', function (ev, picker) {
                //清除全局变量,然后设置input

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
                strategyChange($(this));

                if (!callback) {
                    return;
                }
                callback();
            });
        }


        return this.each(function(){
            var _this = $(this);
            var vmInfo = '<div class="panel-group accordion" id="speedstragegyaccordion">';
            for(var i=0; i<option.config.length; i++){
                vmInfo += getEachStrategy(option.config[i], i);
            }
            vmInfo += '</div>';
            //初始化html
            _this.empty().html(vmInfo);
            //初始化事件
            addListeners(_this);
            //初始化设置
            initSettingDes(option.config);
            // 初始化下面的策略信息
            initSettingSet(option.config);
        });
    }

    $.fn.getSpeedStrategyConfigFinal = function(){
        // 获取最终的所有配置信息
        /*if (check_type == 4) {
            // 永久的只有一条，那么就读取速度即可
            var strategyConfig = $('#speedstrategy').getSpeedStrategyConfig();
            // 进行数据输出
            speedInfos[strategyConfig.type]= [strategyConfig];
            // 取出所有的当前模型的配置
        }*/
        let type = $(this).find('.strategy-panel').find('.dwm.active').data('type');
        return speedInfos[type];
    };

    $.fn.getSpeedStrategyConfig = function(){
        //获取本次的最终配置信息
        var getResultInfo = function(_this){
            var strategyPanel = _this.find('.strategy-panel');
            var info = {};
            for(var i=0; i<strategyPanel.length;i++){
                if(1 == $(strategyPanel[i]).data('mode')){
                    info = getEachStrategyConfig(strategyPanel[i]);
                }
            }
            return info;
        }

        //得到每个mode的时间策略
        var getEachStrategyConfig = function(strategyPanel){
            var strategyPanel = $(strategyPanel);
            var info = {};
            info.mode = strategyPanel.data('mode');
            info.type = strategyPanel.find('.dwm.active').data('type');
            var tabPane = strategyPanel.find('.tab-pane.active');

            info.uuid = getUuid();
            // 获取限速大小
            // 速度单位
            info.speedUnit = getSpeedUnit(tabPane);

            info.speedNum = parseInt(tabPane.find('.speedSpinnerNumInput').val());
            switch (module_type) {
                case CONF.MODULE_TYPE.FILE_COPY:
                    if (info.type == 4) {
                        if (!info.speedNum || info.speedNum <= 0) {
                            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_BACKUP_SPEED_LIMIT_TIPS);
                            return false;
                        }
                    } else {
                        if (isNaN(info.speedNum)) {//不能为空
                            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_BACKUP_SPEED_LIMIT_NOT_NULL_TIPS);
                            return false;
                        }
                    }
                    break;
                default:
                    if (!info.speedNum || info.speedNum <= 0) {
                        UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_BACKUP_SPEED_LIMIT_TIPS);
                        return false;
                    }
                    break;
            }
            info.value = info.speedNum * info.speedUnit;
            info.unit = tabPane.find('option:selected').text();

            if (info.type == 5) {
                // 自定义的 是日期
                info.startTime = tabPane.find('.speedstarttime2').val();
                info.endTime = tabPane.find('.speedendtime2').val();
                // 需要对时间的输入进行校验 如果是自定义的，那么结束时间不能大于开始时间,并且不能为空
                if (info.startTime == '' || info.startTime == undefined || info.endTime == '' || info.endTime == undefined) {
                    UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_STRATEGY_SELECT_DATE);
                    return false;
                }
                // 校验日期有效性
                if (!isValidDateTime(info.startTime)) {
                    UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_STRATEGY_ERROR_DATE_RULE_TIPS);
                    return false;
                }
                if (!isValidDateTime(info.endTime)) {
                    UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_STRATEGY_ERROR_DATE_RULE_TIPS2);
                    return false;
                }

                if (info.endTime <= info.startTime) {
                    UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT,LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
                    return false;
                }
            } else if (info.type == 4) {
                info.startTime = 0;
                info.endTime = 0;
            } else {
                info.startTime = tabPane.find('.speedstarttime').val();
                info.endTime = tabPane.find('.speedendtime').val();
                // 需要对时间的输入进行校验
                if (info.startTime == '' || info.startTime == undefined || info.endTime == '' || info.endTime == undefined) {
                    UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, LANG.UI_STRATEGY_SELECT_TIME);
                    return false;
                }
                // 开始结束不能相等
                if (info.endTime == info.startTime) {
                    UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT,LANG.UI_STRATEGY_ERROR_DATE);
                    return false;
                }
            }

            info.days = getDays(info.type, tabPane);
            info.des = strategyPanel.find('.speedstrategydes').html();
            return info;
        }

        function isValidDateTime(dateTimeStr) {
            const match = dateTimeStr.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/);
            if (!match) return false;

            const [_, year, month, day, hour, minute, second] = match.map(Number);

            // 手动校验范围
            if (
                year < 1 || year > 9999 ||            // 年份范围
                month < 1 || month > 12 ||           // 月份范围
                day < 1 || day > daysInMonth(year, month) || // 天数范围
                hour < 0 || hour > 23 ||             // 小时范围
                minute < 0 || minute > 59 ||         // 分钟范围
                second < 0 || second > 59            // 秒数范围
            ) {
                return false;
            }

            return true;
        }

        // 计算某年某月的最大天数
        function daysInMonth(year, month) {
            return new Date(year, month, 0).getDate();
        }

        //得到日期
        var getDays = function(strategy_type, tabPane){
            var days = [];
            var checkGroup = '';
            if(2 == strategy_type){
                //每周
                checkGroup = '.weekcheck';
            }else if(3 == strategy_type){
                //每月
                checkGroup = '.monthcheck';
            }else{
                //每天 自定义 永久
                return days;
            }
            var input = tabPane.find(checkGroup).find('input');
            input.each(function(i, d){
                if(d.checked){
                    days[i] = 1;
                }else{
                    days[i] = 0;
                }
            });
            return days;
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

        return getResultInfo($(this));
    }

    //获取速度单位换算大小
    var getSpeedUnit = function (tabPane) {
        var type = parseInt(tabPane.find('.unit').val());
        var unit;
        switch (type) {
            case 1:
                unit = 1024;
                break;
            case 2:
                unit = 1024 * 1024;
                break;
            case 3:
                unit = 1024 * 1024 * 1024;
                break;
        }

        return unit;
    }


})(jQuery)