var SETTING_DATA_SAFE = function () {
	var isThreePowers = false; // 是否是三权模式
	var _UserPassword = '';

	var handleValidation = function() {
		var dataform = $('#datasafeform');

		dataform.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				history_days: {
					required: true,
					numchecked: true,
				},
				job_log_days: {
					required: true,
					numchecked: true,
				},
				system_log_days: {
					required: true,
					numchecked: true,
				},
				job_alarm_dayas: {
					required: true,
					numchecked: true
				},
				system_alarm_days: {
					required: true,
					numchecked: true
				},
			},

			invalidHandler: function (event, validator) { //display error alert on form submit
			},

			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
			},

			highlight: function (element) { // hightlight error inputs
				$(element)
					.closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},

			unhighlight: function (element) { // revert the change done by hightlight

			},

			success: function (label, element) {
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			},

			submitHandler: function (form) {

			}

		});

		// 验证必须输入数字的校验 大于 180
		$.validator.addMethod("numchecked", function(value, element) {
			let returns = this.optional(element) || /^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/i.test(value);
			return returns && value >= 180
		}, LANG.UI_SETTING_DATA_SAFE_INPUT_MIN_NUM);


		$("#datasubmit").click(function(){
			if (dataform.validate().form()) {
				submitData();
			}
		});

		$("#datacancel").click(function(){
			LOCATION('./content/platform/settings/setting_manager.php?tab=9', 'setting_manager');
		});

	};

	var submitData = function(){
		var data = makeConfig();
		if (data == false) {
			return;
		}
		bootbox.prompt({
			title: LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM,
			inputType: 'password',
			callback: function (result) {
				if (result == null) return;
				if (hex_md5(result) == _UserPassword) {
					Metronic.blockUI({target: '#datasafeform',animate: true,cenrerY: true});
					// 所有的最低保留天数都是180 天
					pAjaxRequest(data, '/api/v1/system/safe/data', 'POST', function (res) {
						Metronic.unblockUI('#datasafeform');
						if (operateResponseList(res, LANG.UI_SETTING_DATA_SAFE)){

						}
					})
				} else {
					UIToastr.showWarning(LANG.UI_SETTING_DATA_SAFE, LANG.UI_SETTINGS_STORAGE_SAFE_PWD_ERR);
					return false;
				}
			}
		});
	}

	// 获取当前页面的编辑配置信息
	var makeConfig = function (){
		// web和后台的结构分别组装
		var web_strategy = {},back_strategy = [];
		var arrars = [
			{'id': 'history_job_check', 'item': 'history_task', 'items': 1},
			{'id': 'job_log_check', 'item': 'task', 'items': 2},
			{'id': 'system_log_check', 'item': 'system', 'items': 3},
			{'id': 'high_log_check', 'item': 'cluster', 'items': 4},
			{'id': 'job_alarm_check', 'item': 'task_alarm', 'items': 5},
			{'id': 'system_alarm_check', 'item': 'system_alarm', 'items': 6},
		];
		for (var j in arrars) {
			var arr,arr2;
			var item = arrars[j]['item'];
			var items = arrars[j]['items'];
			var dom1 = $('#'+arrars[j]['id']).closest('.safe-div-content');
			var strategy_type = parseInt(dom1.find('.web-safe-strategy select[name="strategy_type"]').val());
			var number = parseInt(dom1.find('.web-safe-strategy input[name="number"]').val(), 10);
			number = isNaN(number) ? 0 : number;
			if (strategy_type != 3) {
				// 不是永久的 那么必须要有数量设置
				if (number == 0) {
					UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_DATA_LOG_SAFE_TIPS1);
					return false;
				}
				if (number < 180 && isThreePowers) {
					UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_DATA_LOG_SAFE_TIPS1S);
					return false;
				}
			}
			arr = {'strategy_status': 1, 'strategy_type': strategy_type,'number': number, 'system_configuration_item': items};

			web_strategy[item] = arr;
			arr2 = arr;
			if (!$('#'+arrars[j]['id']).get(0).checked) {
				arr2 = {'strategy_status': 2, 'strategy_type': strategy_type,'number': number, 'system_configuration_item': items};
			}
			back_strategy.push(arr2);
		}
		return {
			'web_strategy': web_strategy,
			'back_strategy': back_strategy,
		};
	}

	var initOldData = function(){
		pAjaxRequest({}, '/api/v1/system/safe/data', 'GET', function (res) {
			let data = res.data;
			// 自动清理全部为永久
			$('#history_job_check').bootstrapSwitch('state', false, true);
			$('#job_log_check').bootstrapSwitch('state', false, true);
			$('#system_log_check').bootstrapSwitch('state', false, true);
			$('#high_log_check').bootstrapSwitch('state', false, true);
			$('#job_alarm_check').bootstrapSwitch('state', false, true);
			$('#system_alarm_check').bootstrapSwitch('state', false, true);
			if(data['back'].length == 0) {
				$('.show-number').spinner({value: 0, step: 10, min: 1,max: 9999999});
			} else {
				var back = data['back'];
				if (back['history_task'].strategy_status == 1) {
					$('#history_job_check').bootstrapSwitch('state', true, true);
				}

				if (back['task'].strategy_status == 1) {
					$('#job_log_check').bootstrapSwitch('state', true, true);
				}

				if (back['system'].strategy_status == 1) {
					$('#system_log_check').bootstrapSwitch('state', true, true);
				}

				if (back['cluster'].strategy_status == 1) {
					$('#high_log_check').bootstrapSwitch('state', true, true);
				}

				if (back['task_alarm'].strategy_status == 1) {
					$('#job_alarm_check').bootstrapSwitch('state', true, true);
				}

				if (back['system_alarm'].strategy_status == 1) {
					$('#system_alarm_check').bootstrapSwitch('state', true, true);
				}
			}

			var web_strategy = data['web'];
			if (web_strategy.length != 0) {
				batchControll(web_strategy['history_task'], 'history_job_check', 'web-safe-strategy');
				batchControll(web_strategy['task'], 'job_log_check', 'web-safe-strategy');
				batchControll(web_strategy['system'], 'system_log_check', 'web-safe-strategy');
				batchControll(web_strategy['cluster'], 'high_log_check', 'web-safe-strategy');
				batchControll(web_strategy['task_alarm'], 'job_alarm_check', 'web-safe-strategy');
				batchControll(web_strategy['system_alarm'], 'system_alarm_check', 'web-safe-strategy');
			}
		})
	}

	function batchControll(data, id, classfy){
		var $checkbox = $('#' + id);
		var $container = $checkbox.closest('.safe-div-content');
		var $select = $container.find('.' + classfy + ' select[name="strategy_type"]');
		var $input = $container.find('.' + classfy + ' input[name="number"]');

		if (data['strategy_status'] == 1) {
			$select.val(data['strategy_type']).trigger('change', ['init']); // 带参数表示是初始化
			// $select.val(data['strategy_type']).change();
			$input.val(data['number']).change();
			$container.find('.show-number').spinner({value: data['number'], step: 10, min: 1, max: 9999999});
		} else {
			//$select.val(3).change(); // 这里会触发 strategy_type change 事件
			$select.val(3).trigger('change', ['init']);
		}
	}

	// 初始化监听事件
	var initListener = function (){
		$('.show-number').spinner({value: 30, step: 1, min: 1, max: 9999999});
		isThreePowers = $('#isThreePowers').val();
		// 历史任务
		checkChange($('#history_job_check'));
		checkChange($('#job_log_check'));
		checkChange($('#system_log_check'));
		checkChange($('#high_log_check'));
		checkChange($('#job_alarm_check'));
		checkChange($('#system_alarm_check'));

		$('select[name="strategy_type"]').each(function () {
			var $select = $(this);
			var $container = $select.closest('.safe-div-content');
			var $checkbox = $container.find('input[type="checkbox"]');
			var dom = $(this).closest('.item-label');
			// 只有在用户主动选择 "永久" 时才自动关闭开关
			$select.on('change', function () {
				var val = $(this).val();
				if (val == 3) {
					$checkbox.bootstrapSwitch('state', false, true);
					$checkbox.bootstrapSwitch('disabled', true);
				} else {
					$checkbox.bootstrapSwitch('disabled', false);
				}
				if (val == 3) {
					// 永久
					dom.find('.show-number').hide();
					dom.find('.show-type').hide();
				} else if (val == 1) {
					// 个数
					dom.find('.show-number').show();
					dom.find('.show-type').show();
					dom.find('.show-type').text(LANG.UI_PUBLIC_NUM);
				} else {
					// 天数
					dom.find('.show-number').show();
					dom.find('.show-type').show();
					dom.find('.show-type').text(LANG.UI_PUBLIC_UNIT_DAY);
				}
			});
		});

		$('input[name="number"]').on('input', function (){
			// 移除所有非数字字符
			var value = $(this).val().replace(/[^\d]/g, '');
			// 检查长度是否超过10位
			if (value.length > 10) {
				// 截断到10位
				value = value.substring(0, 10);
			}
			$(this).val(value);
		})
	}

	function checkChange(elem) {
		// 先确保移除所有现有监听器
		// elem.off('switchChange.bootstrapSwitch');
		elem.on('switchChange.bootstrapSwitch',function(event, state) {

			event.stopPropagation(); // 确保事件不会冒泡到父元素
			event.preventDefault(); // 阻止默认行为
			console.log("Switch state changed to: " + state);
			var container = $(this).closest('.safe-div-content')
			if (state) {
				console.log("Show elements");
				//container.find('.web-safe-strategy').show();
				//container.find('.back-safe-strategy').show();
			} else {
				console.log("Hide elements");
				//container.find('.web-safe-strategy').hide();
				//container.find('.back-safe-strategy').hide();
			}
		});
	}

	//初始化当前用户密码用于删除二次确认
	var initUserPassword = function () {
		pAjaxRequest({}, "/api/v1/users/password", "GET", function (result) {
			if (result.success) {
				_UserPassword = result.data.password;
			}
		});
	}

	return {
		init: function(){
			// 监听开关状态变化
			$("input[type='checkbox']").bootstrapSwitch();

			initListener();
			initOldData();
			handleValidation();
			initUserPassword();
		}
	}

}();

jQuery(document).ready(function(){
	SETTING_DATA_SAFE.init();
});