//设置时间
var Settings_Set_Time = function () {
	var _OLDTIME = null;	//之前的时间
	var _AUTHFLAG = 0;		//系统授权状态
	var _AVAILABLE_NTPHOST = '';

	var handleValidation = function(){
		var settimeform = $('#settimeform');

		settimeform.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				cityinput: {
					required: true,
				},
				timeinput: {
					required: true,
					daterule: true,
				},
				ntphost: {
					ntpipv4Ordomain: true,
				}
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

		$.validator.addMethod("daterule", function(value, element) {
			return this.optional(element) || /^\d{4}\-\d{1,2}\-\d{1,2}[T ]\d{1,2}\:\d{1,2}\:\d{1,2}[Z]{0,1}$/.test(value);
		}, LANG.UI_SETTING_INPUT_TIME);

		$.validator.addMethod("ntpipv4Ordomain", function(value, element) {
			var ntpcheck = $('#ntpcheck').bootstrapSwitch('state');
			if(!ntpcheck) return true;
			var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			return domain || ipv4;
		}, LANG.UI_TOOLS_IP_OR_DOMAIN);

		$("#timesubmit").click(function(){
			if (settimeform.validate().form()) {
				submitCheck();
			}
		});

		$("#timecancel").click(function(){
			LOCATION('./content/platform/settings/setting_manager.php?tab=1', 'setting_manager');
		});
	}

	// 检测是否是正确的日期
	function isValidDateTime(input) {
		// 支持格式：
		// - "2025-11-19 14:30:45"
		// - "2025-11-19T14:30:45"
		const regex = /^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}$/;

		if (!regex.test(input)) {
			return false;
		}

		// 统一替换 T 为空格，方便 split
		const normalized = input.replace('T', ' ');
		const [datePart, timePart] = normalized.split(' ');

		// 解析日期部分
		const [year, month, day] = datePart.split('-').map(Number);
		// 解析时间部分
		const [hour, minute, second] = timePart.split(':').map(Number);

		// 边界检查（提前过滤明显非法值）
		if (
			year < 1000 || year > 9999 ||
			month < 1 || month > 12 ||
			day < 1 || day > 31 ||
			hour < 0 || hour > 23 ||
			minute < 0 || minute > 59 ||
			second < 0 || second > 59
		) {
			return false;
		}

		// 创建 Date 对象
		const date = new Date(year, month - 1, day, hour, minute, second);

		// 关键：反向验证所有字段是否一致
		return (
			date.getFullYear() === year &&
			date.getMonth() === month - 1 &&
			date.getDate() === day &&
			date.getHours() === hour &&
			date.getMinutes() === minute &&
			date.getSeconds() === second
		);
	}

	//提交修改检测
	var submitCheck = function(){
		//如果是NTP时间同步配置,直接提交
		if($('#ntpcheck').bootstrapSwitch('state')){
			submit();
			return;
		}
		if (!isValidDateTime($('input[name=timeinput]').val())){
			return UIToastr.showWarning(LANG.UI_SETTING_MODIFY_TIME, LANG.UI_SETTING_INPUT_TIME);
		}
		//如果修改后的时间在当前时间之前,需要进行提示
		var oldTimestamp = datetime_to_unix(_OLDTIME);
		var newTimestamp = datetime_to_unix($('input[name=timeinput]').val());
		if(_AUTHFLAG == 1 && oldTimestamp >= newTimestamp){
			bootbox.confirm({
				title: LANG.UI_SETTING_MODIFY_TIME,
				message: LANG.UI_SETTING_MODIFY_TIME_TIP1 + '<br>' +
					LANG.UI_SETTING_MODIFY_TIME_TIP2,
				callback: debounce(function(r) {
					if(!r) return;
					submit();
				}, 300)
			});
		}else{
			submit();
		}

	}

	//时间格式(2012-12-12 12:12:12)转时间戳
	var datetime_to_unix = function(datetime){
		var tmp_datetime = datetime.replace(/:/g,'-');
		tmp_datetime = tmp_datetime.replace(/ /g,'-');
		var arr = tmp_datetime.split("-");
		var now = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
		return parseInt(now.getTime()/1000);
	}

	//提交修改
	var submit = function(){
		var data = {};
		data.timezone = $('select[name=cityinput]').val();
		data.timeinput = $('input[name=timeinput]').val();
		data.ntpcheck = $('#ntpcheck').bootstrapSwitch('state');
		data.ntphost = _AVAILABLE_NTPHOST;
		if (datetime_to_unix(_OLDTIME) == datetime_to_unix(data.timeinput)) {
			// 表示未修改时间，那么就不传时间
			data.timeinput = '';
		}
		Metronic.blockUI({target: '#settimeform',animate: true});
		bootbox.prompt({
			title: LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM,
			inputType: 'password',
			callback: debounce(function (r) {
				Metronic.unblockUI('#settimeform');
				if (r == null) return;
				// 执行操作验证密码
				Metronic.blockUI({target: '#settimeform',animate: true,cenrerY: true});
				var encrypt = new JSEncrypt();
				encrypt.setPublicKey(CONF.PUBLIC_KEY);
				var password = encrypt.encrypt(r);
				var that = this;
				pAjaxRequest({password: password}, '/api/v1/users/check/password', 'POST', function (result) {
					if (result.code == 0) {
						$(that).modal('hide');
						// 执行操作
						pAjaxRequest(data, '/api/v1/system/times/info', 'POST', function (res) {
							Metronic.unblockUI('#settimeform');
							if (operateResponseList(res, LANG.UI_PLATFORM_SET_TIME)){
							}
						})
					} else {
						Metronic.unblockUI('#settimeform');
						UIToastr.showWarning(result.title, result.message);
					}
				});
			}, 300, false)
		})
	}

	var handlerInit = function(){
		//初始化城市
		var initCityInput = function(){
			Metronic.blockUI({target: '#settimeform',animate: true});
			pAjaxRequest({}, '/api/v1/system/times', 'GET', function (res) {
				Metronic.unblockUI('#settimeform');
				var cityinput = $('select[name=cityinput]');
				cityinput.empty();
				if (res.code == 0) {
					var data = res.data;
					for(var i=0; i<data.length; i++){
						var option = $("<option>").text(data[i]).val(data[i]);
						cityinput.append(option);
					}
				}

				initDefaultCityAndTime();
			})
		}
		//初始化默认城市和时间
		var initDefaultCityAndTime = function(){
			Metronic.blockUI({target: '#settimeform',animate: true});
			pAjaxRequest({}, '/api/v1/system/times/info', 'GET', function (res) {
				Metronic.unblockUI('#settimeform');
				if (res.code == 0) {
					var data = res.data;
					_OLDTIME = data.date;
					_AUTHFLAG = data.authFlag;
					$('select[name=cityinput]').val(data.timezone);
					$('input[name=timeinput]').val(data.date);
					if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
						$(".form_datetime").datetimepicker({
							language:  'zh-CN',
							autoclose: true,
							isRTL: Metronic.isRTL(),
							format: "yyyy-MM-dd hh:ii:ss",
							pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
							forceParse:false
						});
					}else{
						$(".form_datetime").datetimepicker({
							autoclose: true,
							isRTL: Metronic.isRTL(),
							format: "yyyy-mm-dd hh:ii:ss",
							pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
							forceParse:false
						});
					}
					//ntp配置初始化
					if(data.ntp.ntpFlag){
						//开启
						$('#ntpcheck').bootstrapSwitch('state', true);
						$('select[name=cityinput]').prop('disabled', true);
						$('input[name=timeinput]').prop('disabled', true);
						$('#ntphostdiv').show();
						$('#timesubmit').prop('disabled', true);
					}else{
						//关闭
						$('select[name=cityinput]').prop('disabled', false);
						$('input[name=timeinput]').prop('disabled', false);
						$('#ntphostdiv').hide();
						$('#timesubmit').prop('disabled', false);
					}
					var hosts = data.ntp.ntpServers;
					var ntphost = $('#ntphost');
					ntphost.empty();
					for(var i=0; i<hosts.length; i++){
						if(0 == i){
							var option = $("<option selected>").text(hosts[i]).val(hosts[i]);
						}else{
							var option = $("<option>").text(hosts[i]).val(hosts[i]);
						}

						ntphost.append(option);
					}

					$('#ntphost').editableSelect({ filter: false });
				}
			})
		}

		//初始化按钮
		var initButtonListerner = function(){
			//启动/关闭NTP配置
			$('#ntpcheck').bootstrapSwitch('onSwitchChange', function (e, data) {
				if(data){
					$('select[name=cityinput]').prop('disabled', true);
					$('input[name=timeinput]').prop('disabled', true);
					$('input[name=timeinput]').parent().find('.input-group-btn button').prop('disabled', true);
					$('#ntphostdiv').show();
					$('#timesubmit').prop('disabled', true);
				}else{
					$('select[name=cityinput]').prop('disabled', false);
					$('input[name=timeinput]').prop('disabled', false);
					$('input[name=timeinput]').parent().find('.input-group-btn button').prop('disabled', false);
					$('#ntphostdiv').hide();
					$('#timesubmit').prop('disabled', false);
				}
			});

			//立即更新
			$('#ntpupdate').on('click', function(){
				$('#settimeform').validate({
					ntphost: {
						ntpipv4Ordomain: true,
					}
				});
				if (!$('#settimeform').validate().form()) {
					return;
				}

				//有运行中的任务禁止同步
				var has_running_job;
				$.ajaxSettings.async = false;
				$.post(CONF.AJAXPATH, {m:CONF.M.JOB, f:'ntpSyncCheckRunningJob', p:''}, function(d){
					var data = JSON.parse(d);
					has_running_job = data.ext.has_running_job;
				});
				$.ajaxSettings.async = true;
				if (has_running_job) {
					UIToastr.showWarning(LANG.UI_SETTINGS_TIME_NTP_SYNC_CHECK_RUNNING_JOB, LANG.UI_SETTINGS_TIME_NTP_SYNC_HAS_RUNNING_JOB);
					return;
				}

				Metronic.blockUI({target: '#settimeform',animate: true});
				var data = {};
				data.ntphost = $('#ntphost').val();
				data.timezone = $('select[name=cityinput]').val();
				pAjaxRequest(data, '/api/v1/system/times/ntp', 'POST', function (res) {
					Metronic.unblockUI('#settimeform');
					if (operateResponseList(res, LANG.UI_PLATFORM_SET_TIME)){
						_AVAILABLE_NTPHOST = $('#ntphost').val();
						$('#timesubmit').prop('disabled', false);
						//系统时间更新为最新的时间
						var data = res.data;
						$('input[name=timeinput]').val(data.info.newTime);
					}
				})
			})
		}

		initCityInput();
		initButtonListerner();

	}
	return {
		init: function () {
			handlerInit();
			handleValidation();
		}
	};

}();

jQuery(document).ready(function(){
	Settings_Set_Time.init();
});