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
	$.fn.strategy = function(options){
		var initDesFlag = false;
		var oldDes;
		var defaults = {
			//多维数组,每一项表示每一个策略配置
			//mode: 1,	备份模式 1 完全备份 2 增量备份  3差异备份 4 恢复策略 5.数据验证策略
			//strategy_type: 2,	策略类型 1 每天 2 每周  3 每月
			//days: [0, 0, 0, 0, 0, 1, 0],
			//start_time: '23:00:00',
			//roll_flag: false,
			//roll_interval: '01:00:00',
			//roll_end_time: '23:59:59'
			//strategyDes：null 默认其他模块可以不传，目前只有华为CBR使用此字段，华为CBR的值为"cbr"
			'dom' : '', //初始化操作元素
			'config' : [],
			'display': ['', '', '', ''],	//是否显示,默认显示
			'backup_flag' : 1 //是否全局策略显示描述

		}
		var option = $.extend(defaults,options);
		//得到每一个 策略
		var getEachStrategy = function(config, i, display){
			var html = '<div class="panel panel-default strategy-panel ' + display[i] + '" data-mode="' + config.mode + '">' +
				getHead(config, i) +
				getBody(config, i) +
				'</div>';
			return html;
		}

		//得到panel-heading
		var getHead = function(config, i){
			let openClass = i==0 ? '' : 'collapsed';
			var html = '<div class="panel-heading">' +
				'<h4 class="panel-title">' +
				`<a class="accordion-toggle accordion-toggle-styled ${openClass} popovers" ` +
				'data-toggle="collapse" data-parent="#stragegyaccordion" data-trigger="hover" data-placement="top" href="#collapsebody' + config.mode + '">' +
				'<i></i> ' +
				'<span class="panel-title-mode font-green-seagreen"></span>' +
				'<span class="strategyDes"></span></a>' +
				'</h4>' +
				'</div>';
			return html;
		}

		//得到panel-body
		var getBody = function(config, i){
			let openClass = i==0 ? 'in' : '';
			var html = '<div id="collapsebody' + config.mode + `" class="panel-collapse collapse ${openClass}">` +
				'<div class="panel-body">' +
				'<div class="row" style="margin: 0;">' +
				'<div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios" style="border-right: none;">' +
				'<ul class="nav nav-tabs tabs-left">';
			var radioDes = ['', LANG.UI_STRATEGY_DAY, LANG.UI_STRATEGY_WEEK_TITLE, LANG.UI_STRATEGY_MONTH];
			//英文版描述
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
				radioDes = ['', LANG.UI_STRATEGY_DAY_EN, LANG.UI_STRATEGY_WEEK_EN, LANG.UI_STRATEGY_MONTH_EN];
			}
			var tabDes = ['', 'tab_day', 'tab_week', 'tab_month'];
			for(var j=1; j<4; j++){
				//初始化每天每周每月radio
				var active = '', checked = '';
				if(config.strategy_type == j){
					active = 'active';
					checked = 'checked';
				}
				html += '<li class="dwm  ' + active + '" data-type="' + j + '">' +
					'<a href="#' + tabDes[j] + config.mode + '" data-toggle="tab">' +
					'<input type="radio" data-radio="iradio_square-blue" ' + checked + ' class="icheck" name="bakradio' + i + '"/> ' + radioDes[j] + ' </a>' +
					'</li>';
			}
			// 新增需求：当次完备触发时如当前任务有其他备份模式运行，则在该模式运行完以后立即触发完备
			let skipFullHtml = '';
			if(config.mode == 1){
				skipFullHtml = `
						<div class="fullSkipWrapper">
							<span class="control-label form-group-label fullSkipLabel">${LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN}</span>
							<input type="checkbox" id="fullSkipSwitch" ${config.full_backup_compensation_flag ? 'checked' : ''}
								class="make-switch"
								data-on-color="primary"
								data-off-color="info" data-size="small"
								data-on-text="${LANG.UI_PUBLIC_ON_ONE}"
								data-off-text="${LANG.UI_PUBLIC_OFF_ONE}">
							<a class="popovers ml12px" data-container="body" data-trigger="hover" data-placement="right" data-html="true" data-content="${LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP}">
								<i class="viconfont vicon-tishi"></i>
							</a>
						</div>`;
			}

			html += `</ul>
							</div>
							<div class="col-md-10 col-sm-10 col-xs-10">
								<div class="tab-content" style="border-left: 1px solid #ddd;">
									${getEachTab(config, i)}
								</div>
							</div>
						</div>
						${skipFullHtml}
					</div>
				</div>`;

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
			var html = '<div class="tab-pane fade in ' + active + '" id="tab_day' + config.mode + '">' +
				getUnifyCentent(thisConfig, i) +
				'</div>';
			return html;
		}
		//每周
		var getTabWeek = function(config, i){
			var thisConfig = JSON.parse(JSON.stringify(config));
			//如果有设置就要用设置的值,如果没有就用默认的值
			var days = thisConfig.days, active = 'active';
			var s1 = '', s2 = '', s3 = '', s4 = '',
				s5 = '', s6 = '', s7 = '', s8 = '',
				s9 = '', s10 = '', s11 = '', s12 = '',
				s13 = '', s14 = '', s15 = '', s16 = '',
				s17 = '', s18 = '', s19 = '', s20 = '';
			var list = [s1, s2, s3, s4, s5, s6, s7, s8, s9, s10, s11, s12, s13, s14, s15, s16, s17, s18, s19, s20];
			if(2 != thisConfig.strategy_type){
				//如果不是每周备份,就用默认的设置
				days = [0, 0, 0, 0, 1, 0, 0];
				active = '';
				thisConfig.roll_flag = false;	//如果不是选择的本类型备份,使用默认不开启
			}

			var html = '<div class="tab-pane fade in ' + active + '" id="tab_week' + config.mode + '">';

			//如果是完全备份,增加备份频率
			if(1 == thisConfig.mode || 5 == thisConfig.mode || 6 == thisConfig.mode){
				//设置默认频率,如果有设置,用设置的频率
				if(thisConfig.frequency && "" != thisConfig.frequency){
					for(var j=1;j<=52;j++){
						if(thisConfig.frequency == "s"+j){
							list[j-1] = 'selected="selected"';
						}
					}
				}
				html += `<div class="form-group"><label class="control-label col-md-3 form-group-label">` + LANG.UI_STRATEGY_WEEK_FREQUENCY + `</label>` +
					`<div class="col-md-5 form-group-content">` +
					`<div id="frequencyNum"><div class="input-group spinner-group"><input type="number" id="frequency" class="spinner-input form-control input-sm frequency" name="frequency" maxlength="3">
						<div class="spinner-buttons input-group-btn spinner-group-btn">
							<button type="button" class="btn spinner-up default input-sm" >
								<i class="fa fa-angle-up"></i>
							</button>
							<button type="button" class="btn spinner-down default input-sm">
								<i class="fa fa-angle-down"></i>
							</button>
						</div>
					</div>
				</div>`;

				html +='</div>' +
					'</div>';
			}

			html += '<div class="form-group">' +
				'<label class="control-label col-md-3 form-group-top2-label">' + LANG.UI_STRATEGY_WEEK_TITLE + '</label>' +
				'<div class="col-md-9">' +
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
				html += '<label style="padding-right: 10px;"><input type="checkbox" data-checkbox="icheckbox_square-blue" ' + checked + ' class="icheck"> ' + weedDes[j] + ' </label>';
			}

			html += '</div>' +
				'</row>' +
				'</div>' +
				'</div>' +
				'</div>';	//form-group结束

			html += getUnifyCentent(thisConfig, i) +
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
			var html = '<div class="tab-pane fade in ' + active + '" id="tab_month' + config.mode + '">' +
				'<div class="form-group">' +
				'<label class="control-label col-md-3">' + LANG.UI_STRATEGY_MONTH + '</label>' +
				'<div class="col-md-9">' +
				'<div class="input-group monthcheck">';

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
			var roll_flag = config.roll_flag ? 'checked' : '';
			var roll_interval = config.roll_interval;
			var roll_end_time = config.roll_end_time;
			var display = config.roll_flag ? '' : 'display-hide';
			var html =   '<div class="form-group">' +
				'<label class="control-label col-md-3 form-group-label">' + LANG.UI_PUBLIC_START_TIME + '</label>' +
				'<div class="col-md-5 form-group-content">' +
				'<div class="input-group">' +
				'<input type="text" value="' + start_time + '" class="form-control timepicker timepicker-24 starttime">' +
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
				'<label class="control-label col-md-3 form-group-label">' + LANG.UI_STRATEGY_ROLL_INTERVAL + '</label>' +
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
				'<label class="control-label col-md-3 form-group-label">' + LANG.UI_STRATEGY_ROLL_OVER_TIME + '</label>' +
				'<div class="col-md-5 form-group-content">' +
				'<div class="input-group">' +
				'<input type="text" value="' + roll_end_time + '" class="form-control timepicker timepicker-24 endtime">' +
				'<span class="input-group-btn">' +
				'<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>' +
				'</span>' +
				'</div>' +
				'</div>' +
				'</div>';
			return html;
		}

		//添加事件
		var addListeners = function(_this, flag){
			var flag = flag;
			$(_this).find('.starttime').timepicker({
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
			$(_this).find('.endtime').timepicker({
				autoclose: true,
				minuteStep: 5,
				showSeconds: true,
				showMeridian: false,
//              defaultTime:'23:59:59'
			});
			//检测其删除了没有再填值为空的情况
			//检测开始时间
			$(_this).find('.starttime').on('blur',function(){
				let startval = $(this).val();
				if(startval == "" || startval == null){
					$(this).val("0:00:00");
				};
			})
			//检测滚动时间
			$(_this).find('.rolltime').on('blur',function(){
				let startval = $(this).val();
				if(startval == "" || startval == null){
					$(this).val("0:00:00");
				};
			})
			//检测结束时间
			$(_this).find('.endtime').on('blur',function(){
				let startval = $(this).val();
				if(startval == "" || startval == null){
					$(this).val("0:00:00");
				};
			})


			//禁用tab切换
			$('.timepicker').keydown(function(e) {
				if (e.which == 9) { // 检测Tab键按压，keyCode为9
					e.preventDefault(); // 阻止默认行为，即不允许Tab切换
				}
			});

			//滚动隐藏时间选择器
			$('.tab-content').scroll(function() {
				$('.timepicker').timepicker('hideWidget');
			});
			//滚动隐藏时间选择器
			$('.form-body').scroll(function() {
				$('.timepicker').timepicker('hideWidget');
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
				}else{
					//OFF
					rollInterval.hide();
					endTime.hide();
				}
				strategyChange(this);
				initTimeStrategyDes(flag);
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
				initTimeStrategyDes(flag);
			});

			//时间改变
			_this.find('.panel-body').find('.timepicker-24').on('change', function(){
				strategyChange(this);
				initTimeStrategyDes(flag);
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
				initTimeStrategyDes(flag);
			});

			//备份频率改变
			_this.find('.panel-body').find('#frequency').on('input propertychange', function(){
				if ($(this).val() > 52) {
					$(this).val(52);
				}
				strategyChange(this);
				initTimeStrategyDes(flag);
			});

			_this.find('#frequencyNum').find('.spinner-up').on('click', function(){
				strategyChange(this);
				initTimeStrategyDes(flag);
			});
			_this.find('#frequencyNum').find('.spinner-down').on('click', function(){
				strategyChange(this);
				initTimeStrategyDes(flag);
			});
			_this.find('#fullSkipSwitch').on('switchChange.bootstrapSwitch', function(event, state) {
				strategyChange(this);
				initTimeStrategyDes(flag);
			})

		}

		//得到时间策略描述组合
		var initTimeStrategyDes = function(type){
			var des = "";
			if(type == 1){
				//备份
				var backuptype = $('#backuptype').val();
				var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
				var strategyMode = $('#strategymode').find('input:checked');
				if('strategy' == backuptype){
					for(var i=0; i<strategyMode.length; i++){
						if (0 == $(strategyMode[i]).data('mode')) {
							des += strategyConfig.comInfo.des + ". ";
						} else if (1 == $(strategyMode[i]).data('mode')) {
							des += strategyConfig.fullInfo.des + ". ";
						} else if (2 == $(strategyMode[i]).data('mode')) {
							des += strategyConfig.incrInfo.des + ". ";
						} else if (3 == $(strategyMode[i]).data('mode')) {
							des += strategyConfig.diffInfo.des + ". ";
						} else if (4 == $(strategyMode[i]).data('mode')){
							des += strategyConfig.logInfo.des + ". ";
						} else if (7 == $(strategyMode[i]).data('mode')){
							des += strategyConfig.recInfo.des + ". ";
						} else if (9 == $(strategyMode[i]).data('mode')) {
							des += strategyConfig.pIncrInfo.des + ". ";
						}
					}
					if(strategyConfig.copyInfo){
						des += strategyConfig.copyInfo.des + ". ";
					}
					if(strategyConfig.archiveInfo){
						des += strategyConfig.archiveInfo.des + ". ";
					}
				}else if('oncetime' == backuptype){
					des += LANG.UI_GLOBAL_STRATEGY_ONCE_TIME_START + ": " + $('#oncetime').val();
				}else if('manual' == backuptype){
					des = LANG.UI_BACKUP_MANUAL;
				}
				var strategyIndex = $('#strategySelect').val();
				if(strategyIndex && strategyIndex != ""){
					if(!initDesFlag){
						oldDes = $('.backupTimeDes').html();
						initDesFlag = true;
					}
					if(oldDes != des){
						$('.backupTimeDes').addClass('font-green-seagreen');
					}else{
						$('.backupTimeDes').removeClass('font-green-seagreen');
					}
				}else{
					$('.backupTimeDes').removeClass('font-green-seagreen');
				}
				$('.backupTimeDes').html(des);
				$('.backupTimeDes').attr('title', des);
			}else if(type == 2){
				var strategyConfig = $('#recoveryTimestrategy').getStrategyConfig();
				des += strategyConfig.recInfo.des;
				var strategyIndex = $('#strategySelect').val();
				if(strategyIndex && strategyIndex != ""){
					if(!initDesFlag){
						oldDes = $('.recoveryTimeDes').html();
						initDesFlag = true;
					}
					if(oldDes != des){
						$('.recoveryTimeDes').addClass('font-green-seagreen');
					}else{
						$('.recoveryTimeDes').removeClass('font-green-seagreen');
					}
				}else{
					$('.recoveryTimeDes').removeClass('font-green-seagreen');
				}
				$('.recoveryTimeDes').html(des);
				$('.recoveryTimeDes').attr('title', des);
			}else if(type == 3){
				var strategyConfig = $('#verifyTimestrategy').getStrategyConfig();
				des += strategyConfig.verifyInfo.des;
			}

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
			//获取未改改变之前是不是华为cbr,由于华为cbr默认只设置了config第一个（完全备份），故只需取第一个就好
			var settingtype =  option.config[0].strategyDes;
			var settings = {};
			settings.strategyDes =  settingtype;
			settings.mode = strategyPanel.data('mode');
			settings.strategy_type = strategyPanel.find('.dwm.active').data('type');
			var tabPane = strategyPanel.find('.tab-pane.active');

			settings.start_time = formatTimeString(tabPane.find('.starttime').val());
			if(!settings.start_time){
				settings.start_time = "00:00:00"
			}
			settings.roll_flag = tabPane.find('.rollflag').get(0).checked;
			settings.roll_interval = formatTimeString(tabPane.find('.rolltime').val());
			if(!settings.roll_interval){
				settings.roll_interval = "00:00:00"
			}
			settings.roll_end_time = formatTimeString(tabPane.find('.endtime').val());
			if(!settings.roll_end_time){
				settings.roll_end_time = "00:00:00"
			}
			settings.days = getDays(settings.strategy_type, tabPane);
			settings.frequency = tabPane.find('.frequency').val() == 0 ? '' : 's' + tabPane.find('.frequency').val();

			setStrategyDes(strategyPanel, settings);
		}

		//设置策略描述
		var setStrategyDes = function(strategyPanel, settings){
			var modeDes = [
				LANG.UI_STRATEGY_TIME,
				LANG.UI_PUBLIC_BACKUP_FULL,
				LANG.UI_PUBLIC_BACKUP_INCREMENT,
				LANG.UI_PUBLIC_BACKUP_DIFFRENCE,
				LANG.UI_BACKUP_ARCHIVE_LOG_BACKUP,
				LANG.UI_COPY_TIME_STRATEGY,
				LANG.UI_ARCHIVE_TIME_STRATEGY,
				LANG.UI_STRATEGY_RECOVERY,
				LANG.UI_ARCHIVE_DATA_VERTIFY_STRATEGY,
				LANG.UI_PUBLIC_PERMANENT_INCREMENT];
			var typeDes = ['', LANG.UI_STRATEGY_DAY, LANG.UI_STRATEGY_WEEK, LANG.UI_STRATEGY_MONTH];
			var weekDes = ['', LANG.UI_STRATEGY_MONDAY, LANG.UI_STRATEGY_TUESDAY, LANG.UI_STRATEGY_WEDNESDAY, LANG.UI_STRATEGY_THURSDAY, LANG.UI_STRATEGY_FRIDAY, LANG.UI_STRATEGY_SATURDAY, LANG.UI_STRATEGY_SUNDAY];
			var des = '';
			if(settings.strategyDes == null || settings.strategyDes == undefined || settings.strategyDes == ""){
				des += modeDes[settings.mode];
			}
			//华为CBR不需要括号 省略类型
			if(settings.strategyDes == "cbr"){
				des+="";
			}
			//英文版描述
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
				typeDes = ['', LANG.UI_STRATEGY_DAY, LANG.UI_STRATEGY_EVERY, LANG.UI_STRATEGY_MONTH];
			}
			if(settings.strategyDes != "cbr"){
				des += " (";
			}
			//目前只有完全备份|归档策略的每周有这个显示
			if(settings.mode == 1 || settings.mode == 5 || settings.mode == 6){
				if(settings.strategy_type == 2 && "" != settings.frequency && undefined != settings.frequency){
					des += strategyPanel.find("#frequency").val() == 0 ? LANG.UI_STRATEGY_WEEK_TITLE : LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS.replace('x', strategyPanel.find("#frequency").val());
					des += ","
				}
			}
			des += typeDes[settings.strategy_type];
			for(var i=0; i<settings.days.length; i++){
				if(1 == settings.strategy_type) break;	//每天的话不读取days
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
			if(settings.mode == 6){
				des += settings.start_time + LANG.UI_STRATEGY_START;
			}else{
				//数据验证策略没有滚动执行
				if(settings.mode == 8){
					des += formatTimeString(settings.start_time) + " " + LANG.UI_STRATEGY_START;
				}else{
					des += formatTimeString(settings.start_time) + " " + LANG.UI_STRATEGY_START + ", ";
					if(settings.roll_flag){
						des += LANG.UI_STRATEGY_ROLL_INTERVAL + formatTimeString(settings.roll_interval) + ", ";
						if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
							//英文版
							des += formatTimeString(settings.roll_end_time) + LANG.UI_STRATEGY_END;
						}else{
							//中文版
							des += LANG.UI_STRATEGY_ROLL_OVER_TIME + formatTimeString(settings.roll_end_time);
						}
					}else{
						des += LANG.UI_STRATEGY_ROLL_NO;
					}
				}
			}
			//华为cbr不需要括号
			if(settings.strategyDes != "cbr"){
				des += ")";
			}

			// 不同备份策略设置不同viconfont
			switch(settings.mode) {
				case 0: // 通用策略
					strategyPanel.find('h4').find('i').addClass('iconfont icon-timestrategy font-green-seagreen');
					break;
				case 1: // 完全备份
					strategyPanel.find('h4').find('i').addClass('viconfont vicon-wanquanbeifen1 font-green-seagreen');
					let fullSkipFlag = $(`#fullSkipSwitch`).get(0).checked;
					let switchDes = fullSkipFlag ? LANG.UI_PUBLIC_ON : LANG.UI_PUBLIC_OFF;
					let fullSkipDes = LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + switchDes;
					des += ", " + fullSkipDes;
					break;
				case 2:	// 增量备份
					strategyPanel.find('h4').find('i').addClass('viconfont vicon-zengliangbeifen1 font-green-seagreen');
					break;
				case 3: // 差异备份
					strategyPanel.find('h4').find('i').addClass('viconfont vicon-chayibeifen font-green-seagreen');
					break;
				case 4: // 日志备份
					strategyPanel.find('h4').find('i').addClass('viconfont vicon-log font-green-seagreen');
					break;
				case 9: // 永久增量
					strategyPanel.find('h4').find('i').addClass('viconfont vicon-a-Group255 font-green-seagreen');
					break;
				default:
					break;
			}
			// 设置面板的策略mode值
			strategyPanel.find('.panel-title-mode').html(modeDes[settings.mode]);
			strategyPanel.find('.strategyDes').html(des);
			///如果是华为CBR 显示同步
			if(settings.strategyDes == "cbr"){
				strategyPanel.find('.panel-title-mode').html(LANG.UI_VCENTER_SYNC);
			}
		}

		// 小时小于10补0
		var formatTimeString = function(str) {
			if (!str) return;
			var hour = str.split(':')[0];
			if (Number(hour) < 10) {
				return `0${hour.replaceAll('0','')}:${str.split(':')[1]}:${str.split(':')[2]}`;
			} else {
				return str;
			}
		}

		//初始化设置描述
		var initSettingDes = function(config, dom){
			var strategyPanel = dom.find('.strategy-panel');
			for(var i=0; i<config.length; i++){
				setStrategyDes($(strategyPanel[i]), config[i]);
			}
		}

		return this.each(function(){
			var _this = $(this);
			var vmInfo = '<div class="panel-group accordion" id="stragegyaccordion">';
			for(var i=0; i<option.config.length; i++){
				vmInfo += getEachStrategy(option.config[i], i, option.display);
			}
			vmInfo += '</div>';
			//初始化html
			_this.empty().html(vmInfo);
			_this.find('#frequencyNum').spinner({ value: 0, step: 1, min: 0, max: 52 });
			for(var i=0; i<option.config.length; i++){
				if(option.config[i].strategy_type == 2 && option.config[i].frequency != ''){
					if(option.config[i].frequency){
						_this.find('#frequency').val(parseInt(option.config[i].frequency.replace(/\D/g, '')))
					}
				};
			}
			$('.fullSkipWrapper input[type="checkbox"].make-switch').bootstrapSwitch();
			//初始化事件
			addListeners(_this, option.backup_flag);
			//初始化设置
			initSettingDes(option.config, option.dom);
		});
	}

	$.fn.getStrategyConfig = function(){
		//获取最终配置信息
		var getResultInfo = function(_this){
			var strategyPanel = _this.find('.strategy-panel');
			var info = {};
			for(var i=0; i<strategyPanel.length;i++){ //时间策略 0 通用时间策略 1.完全备份 2.增量备份 3. 差异备份 4.恢复策略 5.副本策略 6.归档策略 8.验证 9.永久增量
				if (0 == $(strategyPanel[i]).data('mode')) {
					info.comInfo = getEachStrategyConfig(strategyPanel[i]);
				} else if (1 == $(strategyPanel[i]).data('mode')) {
					info.fullInfo = getEachStrategyConfig(strategyPanel[i]);
				} else if (2 == $(strategyPanel[i]).data('mode')) {
					info.incrInfo = getEachStrategyConfig(strategyPanel[i]);
				} else if (3 == $(strategyPanel[i]).data('mode')) {
					info.diffInfo = getEachStrategyConfig(strategyPanel[i]);
				} else if (4 == $(strategyPanel[i]).data('mode')) {
					info.logInfo = getEachStrategyConfig(strategyPanel[i]);
				} else if (5 == $(strategyPanel[i]).data('mode')) {
					info.copyInfo = getEachStrategyConfig(strategyPanel[i]);
				} else if (6 == $(strategyPanel[i]).data('mode')) {
					info.archiveInfo = getEachStrategyConfig(strategyPanel[i]);
				} else if (7 == $(strategyPanel[i]).data('mode')){
					info.recInfo = getEachStrategyConfig(strategyPanel[i]);
				} else if (8 == $(strategyPanel[i]).data('mode')) {
					info.verifyInfo = getEachStrategyConfig(strategyPanel[i]);
				} else if (9 == $(strategyPanel[i]).data('mode')) {
					info.pIncrInfo = getEachStrategyConfig(strategyPanel[i]);
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
			info.startTime = tabPane.find('.starttime').val();
			info.rollFlag = tabPane.find('.rollflag').get(0).checked;
			info.rollInterval = tabPane.find('.rolltime').val();
			info.endTime = tabPane.find('.endtime').val();
			info.days = getDays(info.type, tabPane);
			info.frequency = (tabPane.find('.frequency').val() == 0 || !tabPane.find('.frequency').val()) ? '' : 's' + tabPane.find('.frequency').val();
			info.des = strategyPanel.find('.strategyDes').html();
			if(info.mode == 1){
				info.full_backup_compensation_flag = $(`#fullSkipSwitch`).get(0).checked
			}
			return info;
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