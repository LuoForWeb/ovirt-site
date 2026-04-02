(function ($) {
	$.fn.initGFSPlug = function (options) {
		var defaults = {
			//初始化数值,数组中第一个代表level2_type即周几/第几周/哪月   第二个代表保留数量,第三个代表是否勾选(默认值为checked选中和''不选中)
			'week': [7, 5, ''],
			'month': [1, 5, ''],
			'year': [1, 5, ''],
		}

		var thisOption = $.extend(defaults, options);
		var Alldiv = '';//最终DIV

		var getDiv = function () {
			optionWeek = '<option value ="7">' + LANG.UI_VISUAL_SUNDAY + '</option>' +
				'<option value ="1">' + LANG.UI_VISUAL_MONDAY + '</option>' +
				'<option value ="2">' + LANG.UI_VISUAL_TUESDAY + '</option>' +
				'<option value ="3">' + LANG.UI_VISUAL_WEDNESDAY + '</option>' +
				'<option value ="4">' + LANG.UI_VISUAL_THURSDAY + '</option>' +
				'<option value ="5">' + LANG.UI_VISUAL_FRIDAY + '</option>' +
				'<option value ="6">' + LANG.UI_VISUAL_SATURDAY + '</option>';

			optionMonth = '<option value ="1">' + LANG.UI_SETTING_VM_GFS_FIRST_WEEK + '</option>' +
				'<option value ="2">' + LANG.UI_SETTING_VM_GFS_LAST_WEEK + '</option>';

			optionYear = '<option value ="1">' + LANG.UI_PUBLIC_JANUARY + '</option>' +
				'<option value ="2">' + LANG.UI_PUBLIC_FEBRUARY + '</option>' +
				'<option value ="3">' + LANG.UI_PUBLIC_MARCH + '</option>' +
				'<option value ="4">' + LANG.UI_PUBLIC_APRIL + '</option>' +
				'<option value ="5">' + LANG.UI_PUBLIC_MAY + '</option>' +
				'<option value ="6">' + LANG.UI_PUBLIC_JUNE + '</option>' +
				'<option value ="7">' + LANG.UI_PUBLIC_JULY + '</option>' +
				'<option value ="8">' + LANG.UI_PUBLIC_AUGUST + '</option>' +
				'<option value ="9">' + LANG.UI_PUBLIC_SEPTEMBER + '</option>' +
				'<option value ="10">' + LANG.UI_PUBLIC_OCTOBER + '</option>' +
				'<option value ="11">' + LANG.UI_PUBLIC_NOVEMBER + '</option>' +
				'<option value ="12">' + LANG.UI_PUBLIC_DECEMBER + '</option>';
			if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
				Alldiv = '<div class="form-group" id="GFSDIV">' +
					'<label class="control-label col-md-2"></label>' +
					'<div class="col-md-10 mt5">' +
					'<div class="panel panel-default strategy-panel">' +
					'<div class="panel-heading">' +
					'<h4 class="panel-title">' +
					'<span class="accordion-toggle">' +
					'<i class="viconfont vicon-gfs font-green-seagreen"></i>' +
					'<span class="font-green-seagreen">' + LANG.UI_SETTING_GFS + '</span>' +
					'<span class="strategyDes GFSstrategydes">--</span>' +
					'</span>' +
					'</h4>' +
					'</div>' +
					'<div id="collapsebody1">' +
					'<div class="panel-body">' +
					'<div class="panel-body__gfs" style="align-items:flex-start">' +
					'<label class="panel-body__gfs__checkbox">' +
					'<input type="checkbox" name="week-check" class="icheck" ' + defaults.week[2] + '>' +
					'</label>' +
					'<div class="panel-body__gfs__strategy">' +
					`<span><b>${LANG.UI_SETTING_VM_GFS_EVERY_WEEK}</b>${LANG.UI_SETTING_VM_GFS_FROM}</span>` +
					'<select class="form-control select2me panel-body__gfs__strategy__select" name="week-select">'
					+ optionWeek +
					'</select>' +
					'<span>' +
					LANG.UI_SETTING_VM_GFS_START +
					'<span style="font-weight:bold;margin:0 4px">' + LANG.UI_SETTING_VM_GFS_BACKUP_POINT + '</span>' +
					LANG.UI_SETTING_VM_GFS_THEN_INCREASE +
					'<i class="icon mark-week"></i>' +
					LANG.UI_SETTING_VM_GFS_MARK_KEEP +
					'</span>' +
					'<div name="input-spinner-week" class="panel-body__gfs__strategy__input">' +
					'<div class="input-group spinner-group">' +
					'<input type="text" name="week-input" oninput="this.value = this.value.replace(/[^0-9]/g,\'\')" style="text-align: left;" class="spinner-input form-control input-sm-sm" >' +
					'<div class="spinner-buttons input-group-btn spinner-group-btn">' +
					'<button type="button" class="btn spinner-up default input-sm-sm">' +
					'<i class="fa fa-angle-up"></i>' +
					'</button>' +
					'<button type="button" class="btn spinner-down default input-sm-sm">' +
					'<i class="fa fa-angle-down"></i>' +
					'</button>' +
					'</div>' +
					'</div>' +
					'</div>' +
					LANG.UI_SETTING_GFS_WEEKS +
					'</div>' +
					'</div>' +

					'<div class="panel-body__gfs" style="align-items:flex-start">' +
					'<label class="panel-body__gfs__checkbox">' +
					'<input type="checkbox" name="month-check" class="icheck" ' + defaults.month[2] + '>' +
					'</label>' +
					'<div class="panel-body__gfs__strategy">' +
					`<span><b>${LANG.UI_SETTING_VM_GFS_EVERY_MONTH}</b>${LANG.UI_SETTING_VM_GFS_FROM}</span>` +
					'<select class="form-control select2me panel-body__gfs__strategy__select" name="month-select">' +
					optionMonth +
					'</select>' +
					'<span>' +
					LANG.UI_SETTING_VM_GFS_START +
					'<span style="font-weight:bold;margin:0 4px">' + LANG.UI_SETTING_VM_GFS_BACKUP_POINT + '</span>' +
					LANG.UI_SETTING_VM_GFS_THEN_INCREASE +
					'<i class="icon mark-month"></i>' +
					LANG.UI_SETTING_VM_GFS_MARK_KEEP +
					'</span>' +
					'<div name="input-spinner-month" class="panel-body__gfs__strategy__input">' +
					'<div class="input-group spinner-group">' +
					'<input type="text" name="month-input"  oninput="this.value = this.value.replace(/[^0-9]/g,\'\')" style="text-align: left;" class="spinner-input form-control input-sm-sm" >' +
					'<div class="spinner-buttons input-group-btn spinner-group-btn">' +
					'<button type="button" class="btn spinner-up default input-sm-sm">' +
					'<i class="fa fa-angle-up"></i>' +
					'</button>' +
					'<button type="button" class="btn spinner-down default input-sm-sm">' +
					'<i class="fa fa-angle-down"></i>' +
					'</button>' +
					'</div>' +
					'</div>' +
					'</div>' +
					LANG.UI_SETTING_GFS_MONTHS +
					'</div>' +
					'</div>' +

					'<div class="panel-body__gfs" style="align-items:flex-start">' +
					'<label class="panel-body__gfs__checkbox">' +
					'<input type="checkbox" name="year-check" class="icheck" ' + defaults.year[2] + '>' +
					'</label>' +
					'<div class="panel-body__gfs__strategy">' +
					`<span><b>${LANG.UI_SETTING_VM_GFS_EVERY_YEAR}</b>${LANG.UI_SETTING_VM_GFS_FROM}</span>` +
					'<select class="form-control select2me panel-body__gfs__strategy__select" name="year-select">' +
					optionYear +
					'</select>' +
					'<span>' +
					LANG.UI_SETTING_VM_GFS_START +
					'<span style="font-weight:bold;margin:0 4px">' + LANG.UI_SETTING_VM_GFS_BACKUP_POINT + '</span>' +
					LANG.UI_SETTING_VM_GFS_THEN_INCREASE +
					'<i class="icon mark-year"></i>' +
					LANG.UI_SETTING_VM_GFS_MARK_KEEP +
					'</span>' +
					'<div name="input-spinner-year" class="panel-body__gfs__strategy__input">' +
					'<div class="input-group spinner-group">' +
					'<input type="text" name="year-input" oninput="this.value = this.value.replace(/[^0-9]/g,\'\')"  style="text-align: left;"  class="spinner-input form-control input-sm-sm" >' +
					'<div class="spinner-buttons input-group-btn spinner-group-btn">' +
					'<button type="button" class="btn spinner-up default input-sm-sm">' +
					'<i class="fa fa-angle-up"></i>' +
					'</button>' +
					'<button type="button" class="btn spinner-down default input-sm-sm">' +
					'<i class="fa fa-angle-down"></i>' +
					'</button>' +
					'</div>' +
					'</div>' +
					'</div>' +
					LANG.UI_SETTING_GFS_YEARS +
					'</div>' +
					'</div>' +


					'</div>' +
					'</div>' +
					'</div>' +
					'</div>' +
					'</div>';
			} else {
				Alldiv = '<div class="form-group" id="GFSDIV">' +
					'<label class="control-label col-md-2"></label>' +
					'<div class="col-md-10 mt5">' +
					'<div class="panel panel-default strategy-panel">' +
					'<div class="panel-heading">' +
					'<h4 class="panel-title">' +
					'<span class="accordion-toggle">' +
					'<i class="viconfont vicon-gfs font-green-seagreen"></i>' +
					'<span class="font-green-seagreen">' + LANG.UI_SETTING_GFS + '</span>' +
					'<span class="strategyDes GFSstrategydes">--</span>' +
					'</span>' +
					'</h4>' +
					'</div>' +
					'<div id="collapsebody1">' +
					'<div class="panel-body">' +
					'<div class="panel-body__gfs" style="align-items:flex-start">' +
					'<label class="panel-body__gfs__checkbox">' +
					'<input type="checkbox" name="week-check" class="icheck" ' + defaults.week[2] + '>' +
					'<i class="icon mark-week"></i>' +
					'</label>' +
					'<div class="panel-body__gfs__strategy">' +
					`<span><b>${LANG.UI_SETTING_VM_GFS_EVERY_WEEK}</b>${LANG.UI_SETTING_VM_GFS_FROM}</span>` +
					'<select class="form-control select2me panel-body__gfs__strategy__select" name="week-select">' +
					optionWeek +
					'</select>' +
					LANG.UI_SETTING_VM_GFS_POINT_ADD + LANG.UI_SETTING_VM_GFS_MARK_KEEP +
					'<div name="input-spinner-week" class="panel-body__gfs__strategy__input">' +
					'<div class="input-group spinner-group">' +
					'<input type="text" name="week-input" oninput="this.value = this.value.replace(/[^0-9]/g,\'\')" style="text-align: left;" class="spinner-input form-control input-sm-sm" >' +
					'<div class="spinner-buttons input-group-btn spinner-group-btn">' +
					'<button type="button" class="btn spinner-up default input-sm-sm">' +
					'<i class="fa fa-angle-up"></i>' +
					'</button>' +
					'<button type="button" class="btn spinner-down default input-sm-sm">' +
					'<i class="fa fa-angle-down"></i>' +
					'</button>' +
					'</div>' +
					'</div>' +
					'</div>' +
					LANG.UI_SETTING_GFS_WEEKS +
					'</div>' +
					'</div>' +

					'<div class="panel-body__gfs" style="align-items:flex-start">' +
					'<label class="panel-body__gfs__checkbox">' +
					'<input type="checkbox" name="month-check" class="icheck" ' + defaults.month[2] + '>' +
					'<i class="icon mark-month"></i>' +
					'</label>' +
					'<div class="panel-body__gfs__strategy">' +
					`<span><b>${LANG.UI_SETTING_VM_GFS_EVERY_MONTH}</b>${LANG.UI_SETTING_VM_GFS_FROM}</span>` +
					'<select class="form-control select2me panel-body__gfs__strategy__select" name="month-select">' +
					optionMonth +
					'</select>' +
					LANG.UI_SETTING_VM_GFS_POINT_ADD + LANG.UI_SETTING_VM_GFS_MARK_KEEP +
					'<div name="input-spinner-month" class="panel-body__gfs__strategy__input">' +
					'<div class="input-group spinner-group">' +
					'<input type="text" name="month-input"  oninput="this.value = this.value.replace(/[^0-9]/g,\'\')" style="text-align: left;" class="spinner-input form-control input-sm-sm" >' +
					'<div class="spinner-buttons input-group-btn spinner-group-btn">' +
					'<button type="button" class="btn spinner-up default input-sm-sm">' +
					'<i class="fa fa-angle-up"></i>' +
					'</button>' +
					'<button type="button" class="btn spinner-down default input-sm-sm">' +
					'<i class="fa fa-angle-down"></i>' +
					'</button>' +
					'</div>' +
					'</div>' +
					'</div>' +
					LANG.UI_SETTING_GFS_MONTHS +
					'</div>' +
					'</div>' +

					'<div class="panel-body__gfs" style="align-items:flex-start">' +
					'<label class="panel-body__gfs__checkbox">' +
					'<input type="checkbox" name="year-check" class="icheck" ' + defaults.year[2] + '>' +
					'<i class="icon mark-year"></i>' +
					'</label>' +
					'<div class="panel-body__gfs__strategy">' +
					`<span><b>${LANG.UI_SETTING_VM_GFS_EVERY_YEAR}</b>${LANG.UI_SETTING_VM_GFS_FROM}</span>` +
					'<select class="form-control select2me select2me panel-body__gfs__strategy__select" name="year-select">' +
					optionYear +
					'</select>' +
					LANG.UI_SETTING_VM_GFS_POINT_ADD + LANG.UI_SETTING_VM_GFS_MARK_KEEP +
					'<div name="input-spinner-year" class="panel-body__gfs__strategy__input">' +
					'<div class="input-group spinner-group">' +
					'<input type="text" name="year-input" oninput="this.value = this.value.replace(/[^0-9]/g,\'\')"  style="text-align: left;"  class="spinner-input form-control input-sm-sm" >' +
					'<div class="spinner-buttons input-group-btn spinner-group-btn">' +
					'<button type="button" class="btn spinner-up default input-sm-sm">' +
					'<i class="fa fa-angle-up"></i>' +
					'</button>' +
					'<button type="button" class="btn spinner-down default input-sm-sm">' +
					'<i class="fa fa-angle-down"></i>' +
					'</button>' +
					'</div>' +
					'</div>' +
					'</div>' + LANG.UI_SETTING_GFS_YEARS +
					'</div>' +
					'</div>' +
					'</div>' +
					'</div>' +
					'</div>' +
					'</div>' +
					'</div>';
			}
		}

		//初始化描述
		var initGFSdes = function (_this) {
			if (_this == null || _this == "" || _this == undefined) {
				var _this = $("#GFSDIV");
			}
			var des = "";
			//得到勾选状态
			var week = _this.find("input[name=week-check]").get(0).checked;
			var month = _this.find("input[name=month-check]").get(0).checked;
			var year = _this.find("input[name=year-check]").get(0).checked;
			if (!week && !month && !year) {
				_this.find('.GFSstrategydes').empty().html('');
				return "";
			}
			if (week) {
				//得到week时间
				var weekSelect = _this.find('select[name=week-select]').find("option:selected").text();
				var week_input = _this.find('input[name=week-input]').val();
				des += LANG.UI_SETTING_VM_GFS_EVERY_WEEK + weekSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + week_input + LANG.UI_PUBLIC_NUM;
				if (month || year) {
					des += "; "
				}
			}
			if (month) {
				//得到month时间
				var monthSelect = _this.find('select[name=month-select]').find("option:selected").text();
				var month_input = _this.find('input[name=month-input]').val();
				des += LANG.UI_SETTING_VM_GFS_EVERY_MONTH + monthSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + month_input + LANG.UI_PUBLIC_NUM;
				if (year) {
					des += "; "
				}
			}
			if (year) {
				//得到year时间
				var yearSelect = _this.find('select[name=year-select]').find("option:selected").text();
				var year_input = _this.find('input[name=year-input]').val();
				des += LANG.UI_SETTING_VM_GFS_EVERY_YEAR + yearSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + year_input + LANG.UI_PUBLIC_NUM;
			}
			var headDes = "(" + des + ")";
			_this.find('.GFSstrategydes').empty().html(headDes);
			return des;
		}
		//检测输入
		var inputChange = function (_this) {
			var num = this.value;
			if (num > 999 || num < 1 || num == "") {
				//还原默认值并给出提示
				$(this).val(5);
				UIToastr.showWarning(LANG.UI_SETTING_GFS_RETENTION_POLICY, LANG.UI_SETTING_VM_GFS_DEFAULT_FIVE);
			}
			initGFSdes();
		}


		return this.each(function () {
			var _this = $(this);
			//初始化div
			getDiv();
			//载入div
			_this.empty().html(Alldiv);
			//初始化ickeck
			_this.find('.icheck').iCheck({
				checkboxClass: 'icheckbox_square-blue',
				radioClass: 'iradio_square-blue',
				//		    	    increaseArea: '20%' // optional
			});
			//初始化inpnner
			_this.find('div[name=input-spinner-week]').spinner({ value: thisOption.week[1], step: 1, min: 1, max: 999 });
			_this.find('div[name=input-spinner-month]').spinner({ value: thisOption.month[1], step: 1, min: 1, max: 999 });
			_this.find('div[name=input-spinner-year]').spinner({ value: thisOption.year[1], step: 1, min: 1, max: 999 });
			//初始化时间从什么时候开始
			_this.find('select[name=week-select]').val(thisOption.week[0]);
			_this.find('select[name=month-select]').val(thisOption.month[0]);
			_this.find('select[name=year-select]').val(thisOption.year[0]);
			//初始化描述
			initGFSdes(_this);


			//改变check事件
			_this.find("input[name=week-check]").on("ifChanged ", function () {
				initGFSdes(_this);
			});
			_this.find("input[name=month-check]").on("ifChanged ", function () {
				initGFSdes(_this);
			});
			_this.find("input[name=year-check]").on("ifChanged ", function () {
				initGFSdes(_this);
			});
			//input改变事件
			_this.find("input[name=week-input]").on("input propertychange", function () {
				initGFSdes(_this);
			});
			_this.find("input[name=month-input]").on("input propertychange", function () {
				initGFSdes(_this);
			});
			_this.find("input[name=year-input]").on("input propertychange", function () {
				initGFSdes(_this);
			});
			//select改变事件
			_this.find("select[name=week-select]").on("change", function () {
				initGFSdes(_this);
			});
			_this.find("select[name=month-select]").on("change", function () {
				initGFSdes(_this);
			});
			_this.find("select[name=year-select]").on("change", function () {
				initGFSdes(_this);
			});
			//spinner插件改变事件
			$('.spinner-up').on('click', function () {
				initGFSdes(_this);
			});
			$('.spinner-down').on('click', function () {
				initGFSdes(_this);
			});
			//input失去焦点事件,防止不输入或者输入为空的情况
			_this.find("input[name=week-input]").blur(inputChange);
			_this.find("input[name=month-input]").blur(inputChange);
			_this.find("input[name=year-input]").blur(inputChange);
		});

	}

	//获取最终配置函数
	//_checkedInfo为完全备份勾选的时间类型每天1,每周2,每月3,如果没勾选完全备份 则传入false
	$.fn.getGFSData = function (_checkedInfo) {
		var _this = $(this);
		//得到最后配置
		var getInfo = function () {
			if (!_checkedInfo) {
				UIToastr.showInfo(LANG.UI_SETTING_GFS_RETENTION_POLICY, LANG.UI_SETTING_VM_GFS_MUST_SELECT);
				return false
			}

			var gfs_strategy_item_list = [];
			//得到勾选状态
			var week = _this.find("input[name=week-check]").get(0).checked;
			var month = _this.find("input[name=month-check]").get(0).checked;
			var year = _this.find("input[name=year-check]").get(0).checked;
			if (!week && !month && !year) {
				UIToastr.showInfo(LANG.UI_SETTING_GFS_RETENTION_POLICY, LANG.UI_SETTING_VM_GFS_PLEASE_SELECT_ONE);
				return false
			}
			if (week) {
				//勾选检测
				if (_checkedInfo == 3) {
					UIToastr.showInfo(LANG.UI_SETTING_GFS_RETENTION_POLICY, LANG.UI_SETTING_VM_GFS_MUST_CONTAIN_WEEK);
					return false
				}
				var thisList = {};
				thisList.level1_type = parseInt(1);
				thisList.level2_type = parseInt(_this.find('select[name=week-select]').val());
				thisList.retention_num = parseInt(_this.find('input[name=week-input]').val());
				if (thisList.retention_num == 0) {
					UIToastr.showInfo(LANG.UI_SETTING_GFS_RETENTION_POLICY, LANG.UI_SETTING_VM_GFS_NOT_BE_ZERO);
					return false
				}
				gfs_strategy_item_list.push(thisList);
			}
			if (month) {
				//勾选检测
				if (_checkedInfo == 3) {
					UIToastr.showInfo(LANG.UI_SETTING_GFS_RETENTION_POLICY, LANG.UI_SETTING_VM_GFS_MUST_CONTAIN_MONTH);
					return false
				}
				var thisList = {};
				thisList.level1_type = parseInt(2);
				thisList.level2_type = parseInt(_this.find('select[name=month-select]').val());
				thisList.retention_num = parseInt(_this.find('input[name=month-input]').val());
				if (thisList.retention_num == 0) {
					UIToastr.showInfo(LANG.UI_SETTING_GFS_RETENTION_POLICY, LANG.UI_SETTING_VM_GFS_NOT_BE_ZERO);
					return false
				}
				gfs_strategy_item_list.push(thisList);
			}
			if (year) {
				//因为完全备份是天周月 所以一旦勾选完全备份再勾选每年保留必定生效
				var thisList = {};
				thisList.level1_type = parseInt(3);
				thisList.level2_type = parseInt(_this.find('select[name=year-select]').val());
				thisList.retention_num = parseInt(_this.find('input[name=year-input]').val());
				if (thisList.retention_num == 0) {
					UIToastr.showInfo(LANG.UI_SETTING_GFS_RETENTION_POLICY, LANG.UI_SETTING_VM_GFS_NOT_BE_ZERO);
					return false
				}
				gfs_strategy_item_list.push(thisList);
			}
			return gfs_strategy_item_list;
		};
		return getInfo();
	}

	//获取描述 用插件的数据生成描述
	$.fn.getGFSDes = function () {
		var _this = $(this);
		var des = "";
		//得到勾选状态
		var week = _this.find("input[name=week-check]").get(0).checked;
		var month = _this.find("input[name=month-check]").get(0).checked;
		var year = _this.find("input[name=year-check]").get(0).checked;
		if (!week && !month && !year) {
			return "";
		}
		if (week) {
			//得到week时间
			var weekSelect = _this.find('select[name=week-select]').find("option:selected").text();
			var week_input = _this.find('input[name=week-input]').val();
			des += LANG.UI_SETTING_VM_GFS_EVERY_WEEK + weekSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + week_input + LANG.UI_PUBLIC_NUM;
			if (month || year) {
				des += "; "
			}
		}
		if (month) {
			//得到month时间
			var monthSelect = _this.find('select[name=month-select]').find("option:selected").text();
			var month_input = _this.find('input[name=month-input]').val();
			des += LANG.UI_SETTING_VM_GFS_EVERY_MONTH + monthSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + month_input + LANG.UI_PUBLIC_NUM;
			if (year) {
				des += "; "
			}
		}
		if (year) {
			//得到year时间
			var yearSelect = _this.find('select[name=year-select]').find("option:selected").text();
			var year_input = _this.find('input[name=year-input]').val();
			des += LANG.UI_SETTING_VM_GFS_EVERY_YEAR + yearSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + year_input + LANG.UI_PUBLIC_NUM;
		}
		var headDes = "(" + des + ")";
		return des;
	}

	//获取描述 用传入的值来生成描述 独立使用
	$.fn.getGFSStr = function (GFSinfo) {
		if (GFSinfo.length == 0) {
			return "";
		}
		var des = "";
		if (GFSinfo.week && GFSinfo.week.length != 0) {
			//得到week时间
			switch (GFSinfo.week[0]) {
				case 1:
					var weekSelect = LANG.UI_VISUAL_MONDAY;
					break;
				case 2:
					var weekSelect = LANG.UI_VISUAL_TUESDAY;
					break;
				case 3:
					var weekSelect = LANG.UI_STRATEGY_WEDNESDAY;
					break;
				case 4:
					var weekSelect = LANG.UI_STRATEGY_THURSDAY;
					break;
				case 5:
					var weekSelect = LANG.UI_STRATEGY_FRIDAY;
					break;
				case 6:
					var weekSelect = LANG.UI_STRATEGY_SATURDAY;
					break;
				case 7:
					var weekSelect = LANG.UI_STRATEGY_SUNDAY;
					break;
			}


			if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
				des += LANG.UI_STRATEGY_WEEK_TITLE + weekSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + GFSinfo.week[1] + LANG.UI_PUBLIC_NUM;
			}
			else {
				//英文版需要加空格
				des += LANG.UI_STRATEGY_WEEK_TITLE + " " + weekSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + GFSinfo.week[1] + LANG.UI_PUBLIC_NUM;
			}
			if (GFSinfo.month || GFSinfo.year) {
				des += "; "
			}
		}
		if (GFSinfo.month && GFSinfo.month.length != 0) {
			//得到month时间
			switch (GFSinfo.month[0]) {
				case 1:
					var monthSelect = LANG.UI_SETTING_VM_GFS_FIRST_WEEK;
					break;
				case 2:
					var monthSelect = LANG.UI_SETTING_VM_GFS_LAST_WEEK;
					break;
			}

			if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
				des += LANG.UI_SETTING_VM_GFS_EVERY_MONTH + monthSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + GFSinfo.month[1] + LANG.UI_PUBLIC_NUM;
			}
			else {
				//英文版需要加空格
				des += LANG.UI_SETTING_VM_GFS_EVERY_MONTH + " " + monthSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + GFSinfo.month[1] + LANG.UI_PUBLIC_NUM;
			}
			if (GFSinfo.year) {
				des += "; "
			}
		}
		if (GFSinfo.year && GFSinfo.year.length != 0) {
			//得到year时间
			switch (GFSinfo.year[0]) {
				case 1:
					var yearSelect = LANG.UI_PUBLIC_JANUARY;
					break;
				case 2:
					var yearSelect = LANG.UI_PUBLIC_FEBRUARY;
					break;
				case 3:
					var yearSelect = LANG.UI_PUBLIC_MARCH;
					break;
				case 4:
					var yearSelect = LANG.UI_PUBLIC_APRIL;
					break;
				case 5:
					var yearSelect = LANG.UI_PUBLIC_MAY;
					break;
				case 6:
					var yearSelect = LANG.UI_PUBLIC_JUNE;
					break;
				case 7:
					var yearSelect = LANG.UI_PUBLIC_JULY;
					break;
				case 8:
					var yearSelect = LANG.UI_PUBLIC_AUGUST;
					break;
				case 9:
					var yearSelect = LANG.UI_PUBLIC_SEPTEMBER;
					break;
				case 10:
					var yearSelect = LANG.UI_PUBLIC_OCTOBER;
					break;
				case 11:
					var yearSelect = LANG.UI_PUBLIC_NOVEMBER;
					break;
				case 12:
					var yearSelect = LANG.UI_PUBLIC_DECEMBER;
					break;
			}

			if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
				des += LANG.UI_SETTING_VM_GFS_EVERY_YEAR + yearSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + GFSinfo.year[1] + LANG.UI_PUBLIC_NUM;
			}
			else {
				//英文版需要加空格
				des += LANG.UI_SETTING_VM_GFS_EVERY_YEAR + " " + yearSelect + LANG.UI_SETTING_VM_GFS_BEGIN_KEEP + GFSinfo.year[1] + LANG.UI_PUBLIC_NUM;
			}

		}
		return des;
	}



})(jQuery)