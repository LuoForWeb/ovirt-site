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
    $.fn.tagpointstrategy = function(options){
    	var _rollIntervalFlag = false;
        var defaults = {
              //多维数组,每一项表示每一个策略配置
              //mode: 1,	1 normal
              //strategy_type: 2,	策略类型 1 每天 2 每周  3 每月
              //days: [0, 0, 0, 0, 0, 1, 0],
              //start_time: '23:00:00',
              //end_time: '23:30:00'
            'config' : [],
            'display': ['', '', '', ''],	//是否显示,默认显示
            
        }
        var option = $.extend(defaults,options);
        //得到每一个 策略
        var getEachStrategy = function(config, i){
            var html = '<div class="panel panel-default strategy-panel" data-mode="' + config.mode + '">' +
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
                               'data-toggle="collapse" data-parent="#tagpointstrategyaction" href="#collapsebody' + i + '">' +
                               '<i class="viconfont vicon-ge_authorization2 font-green-seagreen"></i> ' +
                             '<span class="tagpointstrategydes"></span></a>' +
                          '</h4>' +
                       '</div>';
            return html;
        }
        
        //得到panel-body
        var getBody = function(config, i){
            var html = '<div class=" " >' +
                          '<div class="panel-body">' +
                              '<div class="row">' +
                                  '<div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">' +
                                      '<ul class="nav nav-tabs tabs-left">';
            var radioDes = ['', LANG.UI_STRATEGY_DAY, LANG.UI_STRATEGY_WEEK_TITLE, LANG.UI_STRATEGY_MONTH];
            var tabDes = ['', 'tabtagpoint_data', 'tabtagpoint_week', 'tabtagpoint_month'];
            for(var j=1; j<4; j++){
                //初始化每天每周每月radio
                var active = '', checked = '';
                if(config.strategy_type == j){
                    active = 'active';
                    checked = 'checked';
                }
                html += '<li class="dwm  ' + active + '" data-type="' + j + '">' +
                          '<a href="#' + tabDes[j] + i + '" data-toggle="tab">' +
                          '<input type="radio" data-radio="iradio_square-blue" ' + checked + ' class="icheck" name="tagpointbakradio' + i + '"/> ' + radioDes[j] + ' </a>' +
                        '</li>';
            }
            
            html += '</ul>' +
                              '</div>' +
                              '<div class="col-md-9 col-sm-9 col-xs-9">' +
                                  '<div class="tab-content">' +
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
            var html = '<div class="tab-pane fade in ' + active + '" id="tabtagpoint_data' + i + '">' + 
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
            
            var html = '<div class="tab-pane fade in ' + active + '" id="tabtagpoint_week' + i + '">' + 
                          '<div class="form-group">' + 
                              '<label class="control-label col-md-3">' + LANG.UI_STRATEGY_WEEK_TITLE + '</label>' + 
                              '<div class="col-md-9">' + 
                                  '<div class="input-group tagpointweekcheck">' + 
                                      '<row>' + 
                                          '<div>';
            
            for(var j=0; j<7; j++){
                var checked = '';
                var weedDes = [LANG.UI_STRATEGY_MONDAY , LANG.UI_STRATEGY_TUESDAY , LANG.UI_STRATEGY_WEDNESDAY , LANG.UI_STRATEGY_THURSDAY , LANG.UI_STRATEGY_FRIDAY , LANG.UI_STRATEGY_SATURDAY , LANG.UI_STRATEGY_SUNDAY];
                //初始化每周复选框
                if(days[j]){
                    checked = 'checked';
                }
                html += '<label style="padding-right: 10px;"><input type="checkbox" data-checkbox="icheckbox_square-blue" ' + checked + ' class="icheck"> ' + weedDes[j] + ' </label>';
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
            if(3 != thisConfig.strategy_type){
                //如果不是每周备份,就用默认的设置
                days = [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                active = '';
                thisConfig.roll_flag = false;	//如果不是选择的本类型备份,使用默认不开启
            }
            var html = '<div class="tab-pane fade in ' + active + '" id="tabtagpoint_month' + i + '">' + 
                          '<div class="form-group">' + 
                              '<label class="control-label col-md-3">' + LANG.UI_STRATEGY_MONTH + '</label>' + 
                              '<div class="col-md-9">' + 
                                  '<div class="input-group tagpointmonthcheck">';
            
            for(var j=0; j<31; j++){
                var checked = '';
                if(days[j]){
                    checked = 'checked';
                }
                var thisDay = j + 1;
                html += '<label class="label60"><input type="checkbox" data-checkbox="icheckbox_square-blue" ' + checked + ' class="icheck"> ' + thisDay + ' </label>';
            }
            html += '</div></div>' + getUnifyCentent(thisConfig, i) + '</div></div>';
            return html;					
        }
        
        //得到每天每周每月内容
        var getUnifyCentent = function(config, i){
            var start_time = config.start_time;
//            var end_time = config.end_time;
            var roll_end_time = config.end_time;
            
            var roll_flag = config.roll_flag ? 'checked' : '';
            var roll_interval = config.roll_interval;
//            var roll_end_time = config.roll_end_time;
            var display = config.roll_flag ? '' : 'display-hide';
            var html =   '<div class="form-group">' +
                              '<label class="control-label col-md-3">' + LANG.UI_PUBLIC_START_TIME + '</label>' +
                              '<div class="col-md-5 form-group-content">' +
                                  '<div class="input-group">' +
                                      '<input type="text" value="' + start_time + '" class="form-control timepicker timepicker-24 tagpointstarttime">' +
                                      '<span class="input-group-btn">' +
                                      '<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>' +
                                      '</span>' +
                                  '</div>' +
                              '</div>' +
                          '</div>' +
                          '<div class="form-group rollDiv">' +
							'<label class="control-label col-md-3 form-group-label">' + LANG.UI_STRATEGY_ROOL_DO + '</label>' +
							'<div class="col-md-5 form-group-content">' +
								'<input type="checkbox" ' + roll_flag + ' class="make-switch rollflag" data-on-color="primary" data-size="small"' +
								'data-off-color="info" data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' +
								'<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" ' +
								  ' data-content="' + LANG.UI_STRATEGY_ROOL_DO_TIPS + '" >' +
			                       '<i class="viconfont vicon-tishi"></i>' +
			                   ' </a>' +
							'</div>' +
						'</div>' +
						'<div class="form-group roll ' + display + '">' +
							'<label class="control-label col-md-3">' + LANG.UI_STRATEGY_ROLL_INTERVAL + '</label>' +
							'<div class="col-md-5 form-group-content">' +
								'<div class="input-group">' +
									'<input type="text" value="' + roll_interval + '" class="form-control timepicker timepicker-24 rolltime">' +
									'<span class="input-group-btn">' +
									'<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>' +
									'</span>' +
								'</div>' +
							'</div>' +
						'</div>' +
						'<div class="form-group roll ' + display + '">' +
							'<label class="control-label col-md-3">' + LANG.UI_STRATEGY_ROLL_OVER_TIME + '</label>' +
							'<div class="col-md-5 form-group-content">' +
								'<div class="input-group">' +
									'<input type="text" value="' + roll_end_time + '" class="form-control timepicker timepicker-24 tagpointendtime">' +
									'<span class="input-group-btn">' +
									'<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>' +
									'</span>' +
								'</div>' +
							'</div>' +
						'</div>';
            return html;
        }
        
        //添加事件
        var addListeners = function(_this){
            $(_this).find('.tagpointstarttime').timepicker({
                autoclose: true,
                minuteStep: 5,
                showSeconds: true,
                showMeridian: false,
  //              defaultTime:'00:00:00'
            });
            
  		  	$(_this).find('.rolltime').timepicker({
              autoclose: true,
              minuteStep: 5,
              showSeconds: true,
              showMeridian: false,
//              defaultTime:'01:00:00'
  		  	});
  		  	
            $(_this).find('.tagpointendtime').timepicker({
                autoclose: true,
                minuteStep: 5,
                showSeconds: true,
                showMeridian: false,
  //              defaultTime:'23:59:59'
            });
  
            // handle input group button click
            $(_this).find('.timepicker').parent('.input-group').on('click', '.input-group-btn', function(e){
                e.preventDefault();
                $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
            });
            //滚动执行设置
  		  	$(".rollflag").on("switchChange.bootstrapSwitch",function(e, data){
  				var checked = this.checked;
  				var rollInterval = $(this).closest(".form-group").next();
  				var endTime = $(rollInterval).next();
  				if(checked){
  					//ON
  					rollInterval.show();
  					endTime.show();
  					_rollIntervalFlag = true;
  				}else{
  					//OFF
  					_rollIntervalFlag = false;
  					rollInterval.hide();
  					endTime.hide();
  				}
  				strategyChange(this);
  			});
  		  
  		   //初始化bootstrap-switch
  		   _this.find('.rollflag').bootstrapSwitch();
            
           //初始化ickeck
            _this.find('.icheck').iCheck({
                  checkboxClass: 'icheckbox_square-blue',
                  radioClass: 'iradio_square-blue strategy-radio-custom',
                  increaseArea: '20%' // optional
            });
            
            //提示初始化
            _this.find(".popovers").popover();
            
            //每天每周每月点击事件
            _this.find(".dwm").on('click', function(){
                $(this).find('.icheck').iCheck('check');
            });
            
            //每天每周每月radio选中事件
            _this.find(".dwm").find('.icheck').on('ifChecked', function(){
                $(this).closest('ul').find('li').removeClass('active');
                $(this).closest('li').addClass('active');
                
                $(this).closest('.panel-body').find('.tab-pane').removeClass('active');
                var activeTabPane = $(this).closest('li').find('a').get(0).hash;
                $(activeTabPane).addClass('in active');
                strategyChange(this);
            });
            
            //时间改变
            _this.find('.panel-body').find('.timepicker-24').on('change', function(){
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
        
        //得到日期 
        var getDays = function(strategy_type, tabPane){
            var days = [];
            var checkGroup = '';
            if(2 == strategy_type){
                //每周
                checkGroup = '.tagpointweekcheck';
            }else if(3 == strategy_type){
                //每月
                checkGroup = '.tagpointmonthcheck';
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
        var strategyChange = function(element){
            var strategyPanel = $(element).closest('.strategy-panel');
            var settings = {};
            settings.mode = strategyPanel.data('mode');
            settings.strategy_type = strategyPanel.find('.dwm.active').data('type');
            var tabPane = strategyPanel.find('.tab-pane.active');
            
            settings.start_time = tabPane.find('.tagpointstarttime').val();
            settings.roll_flag = tabPane.find('.rollflag').get(0).checked;
            
            settings.roll_interval = tabPane.find('.rolltime').val();
  		  	settings.roll_end_time = tabPane.find('.tagpointendtime').val();
  		  	
            settings.days = getDays(settings.strategy_type, tabPane);
            setStrategyDes(strategyPanel, settings);
        }
        
        //设置策略描述
        var setStrategyDes = function(strategyPanel, settings){
            var modeDes = [''];
            var typeDes = ['', LANG.UI_STRATEGY_DAY, LANG.UI_STRATEGY_WEEK, LANG.UI_STRATEGY_MONTH];
            var des = '';
            des += LANG.UI_VOL_CDP_JOB_DETAILS_LABEL_STRATEGY + '(';
            des += typeDes[settings.strategy_type];
            for(var i=0; i<settings.days.length; i++){
                if(1 == settings.strategy_type) break;	//每天的话不读取days
                if(settings.days[i]){
                    des += (i + 1) + ", ";
                }
            }
            var rollDes = LANG.UI_STRATEGY_ROLL_NO;
            if(_rollIntervalFlag){
            	rollDes =LANG.UI_STRATEGY_ROLL_INTERVAL+ settings.roll_interval;
            }
            des += settings.start_time + LANG.UI_STRATEGY_START + ", ";
            des += rollDes;
            if(_rollIntervalFlag){
            	des += ","+LANG.UI_STRATEGY_ROLL_OVER_TIME+":"+settings.roll_end_time;
            }
            des += ")";
            strategyPanel.find('.tagpointstrategydes').html(des);
        }
        
        //初始化设置描述
        var initSettingDes = function(config){
            var strategyPanel = $("#tagpointstrategyaction").find('.strategy-panel');
            for(var i=0; i<config.length; i++){
                setStrategyDes($(strategyPanel[i]), config[i]);
            }
        }
        
        return this.each(function(){
            var _this = $(this);
            var vmInfo = '<div class="panel-group accordion" id="tagpointstrategyaction">';
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
       });
    }
    
//    $.fn.getSpeedStrategyConfig = function(){
	$.fn.getTagPointStrategyConfig = function(){
        //获取最终配置信息
        var getResultInfo = function(_this){
            var strategyPanel = _this.find('.strategy-panel');
            var info = {};
            for(var i=0; i<strategyPanel.length;i++){
                if(7 == $(strategyPanel[i]).data('mode')){
                    info.tagInfo = getEachStrategyConfig(strategyPanel[i]);
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
            info.startTime = tabPane.find('.tagpointstarttime').val();
            info.rollFlag = tabPane.find('.rollflag').get(0).checked;
  		  	info.rollInterval = tabPane.find('.rolltime').val();
  		  	
            info.endTime = tabPane.find('.tagpointendtime').val();
            info.days = getDays(info.type, tabPane);
            info.des = strategyPanel.find('.tagpointstrategydes').html();
            return info;
        }
        
        //得到日期 
        var getDays = function(strategy_type, tabPane){
            var days = [];
            var checkGroup = '';
            if(2 == strategy_type){
                //每周
                checkGroup = '.tagpointweekcheck';
            }else if(3 == strategy_type){
                //每月
                checkGroup = '.tagpointmonthcheck';
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
        
        return getResultInfo($(this));
    }
    
  })(jQuery)