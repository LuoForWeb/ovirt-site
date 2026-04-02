function updateSaveButtonState() {

}

//企业微信通知
var Settings_EnWechat_Notice = function () {
	var wechatConf2 = {};

	//设置开关通知函数, 开关状态bool|开关ID|响应div class
	var setSwitch = function(flag, switchID, divClass){
		//添加change事件
		$('#' + switchID).bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.' + divClass).show();	//开
			}else{
				$('.' + divClass).hide();	//关
			}
		});
		//设置默认值
		if(flag){
			$('#' + switchID).bootstrapSwitch('state', true);
			$('.' + divClass).show();
		}else{
			$('#' + switchID).bootstrapSwitch('state', false);
			$('.' + divClass).hide();
		}
	}

	//设置通知等级默认值, 开关|等级[]|设置哪个DIV
	var setNoticeLevel = function(flag, level, levelDiv){
		if(!flag) return;
		for(var i=0; i<level.length; i++){
			switch(level[i]){
				case "1":
					$('.' + levelDiv).find('input[name=level1]').iCheck('check');
					break;
				case "2":
					$('.' + levelDiv).find('input[name=level2]').iCheck('check');
					break;
				case "3":
					$('.' + levelDiv).find('input[name=level3]').iCheck('check');
					break;
			}
		}
	}

	//设置企业微信通知默认配置
	var setWechat2Default = function(){
		var wechatConf = wechatConf2;
		//设置三个开关
		setSwitch(wechatConf.flag, 'wechatcheck2', 'wechatcontent2');
		setSwitch(wechatConf.system_flag, 'systemchecks22', 'systemchecks22div');
		setSwitch(wechatConf.task_flag, 'taskchecks22', 'taskchecks22div');
		//设置两个等级
		setNoticeLevel(wechatConf.system_flag, wechatConf.system_level, 'systemchecks22div');
		setNoticeLevel(wechatConf.task_flag, wechatConf.task_level, 'taskchecks22div');

		//初始化测试配置
		var deviceConfig = wechatConf.config;
		$('input[name=wechat_url]').val(deviceConfig.wechat_url);
		$('input[name=wechat_core_id]').val(deviceConfig.wechat_core_id);
		$('input[name=wechat_app_id]').val(deviceConfig.wechat_app_id);
		$('input[name=wechat_app_secret]').val(deviceConfig.wechat_app_secret);

		//当未初始化的时候,需要先设置通知开关不可用
		if(!wechatConf.flag){
			$('#wechatcheck2').bootstrapSwitch('disabled', true);
			$('#testwechat2submit').prop('disabled', true);
		}
	}

	//得到默认信息
	var initDefaultConf = function(){
		setSwitch(false, 'wechat2check', 'wechatcontent2'); //默认设置为关闭，防止页面闪烁
		Metronic.blockUI({target:'#wechattab2', animate: true});
		pAjaxRequest({}, '/api/v1/system/notice/wecom', 'GET', function (res) {
			Metronic.unblockUI('#wechattab2');
			if (res.code == 0) {
				wechatConf2 = res.data;
				setWechat2Default();
			}
		})
	}

	//得到设置等级
	var getLevelSetting = function(divClass){
		var level1 = $('.' + divClass).find('input[name=level1]').is(':checked');
		var level2 = $('.' + divClass).find('input[name=level2]').is(':checked');
		var level3 = $('.' + divClass).find('input[name=level3]').is(':checked');
		var level = [level1, level2, level3];
		return level;
	}

	var addListeners = function(){
		//切换消息推送配置开关
		$('#pushCheck').on('switchChange.bootstrapSwitch', function(){
			if(this.checked){
				$('.pushChild').show();
			}else{
				$('.pushChild').hide();
			}
		});

		$("#wechat2cancel").click(function(){
			CTLSIDEBAR('setting_manager');
			LOCATION('./content/platform/settings/setting_manager.php?tab=2', 'setting_manager');
		});

		$('#testwechat2').on('click', testWechat2);

		$('#wechat2submit').on('click', function(){
			var data = getWechat2Conf();
			data.flag = $('#wechatcheck2').bootstrapSwitch('state');
			data.system_flag = $('#systemchecks22').bootstrapSwitch('state');
			data.system_level = getLevelSetting('systemchecks22div');
			data.task_flag = $('#taskchecks22').bootstrapSwitch('state');
			data.task_level = getLevelSetting('taskchecks22div');

			Metronic.blockUI({target:'#wechattab2', animate: true});
			pAjaxRequest(data, '/api/v1/system/notice/wecom', 'POST', function (res) {
				Metronic.unblockUI('#wechattab2');
				if (operateResponseList(res, LANG.UI_WECOM_NOTICE_SETTING)){
				}
			})
		});

	}

	var getWechat2Conf = function(mode = ''){
		const data = {};
		data.wechat_url = $('input[name=wechat_url]').val();
		data.wechat_core_id = $('input[name=wechat_core_id]').val();
		data.wechat_app_id = $('input[name=wechat_app_id]').val();
		data.wechat_app_secret = $('input[name=wechat_app_secret]').val();
		if (mode) {
			// 表示测试
			data.test_notice = 1;
		}
		return data;
	}

	//配置企业微信测试
	var testWechat2 = function(){
		//$('#testwechat2div').modal();
		$('#current_pending_task_drawer3').drawer('show');
		handleWechatValidation2();
	}

	//配置企业微信测试验证
	var handleWechatValidation2 = function() {
		var setipform = $('#testwechat2form');

		setipform.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				wechat_core_id: {
					required: true,
				},
				wechat_app_id: {
					required: true,
				},
				wechat_app_secret: {
					required: true,
				},
				wechat_url: {
					required: true,
					ipv4v6: true,
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
				$('#sendtestwechat2').prop('disabled', false).removeClass('btn-default').addClass('btn-success');
			}

		});

		//IP验证格式
		$.validator.addMethod("ipv4v6", function(value, element) {
			var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			//http|https
			var domainHTTP = this.optional( element ) || /^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			return domain || ipv4 || domainHTTP || ipv4HTTP;
		}, LANG.UI_SETTING_INPUT_EXISTS_DOMAIN);

		//测试企业微信通知发送
		$('#sendtestwechat2').unbind().on('click', function(){
			if (setipform.validate().form()) {
				var jsonData = getWechat2Conf('test');
				Metronic.blockUI({target:'#testwechat2div', animate: true});
				pAjaxRequest(jsonData, '/api/v1/system/notice/test_wecom', 'POST', function (res) {
					Metronic.unblockUI('#testwechat2div');
					if (operateResponseList(res, LANG.UI_WECOM_NOTICE_SETTING)){
						if(res.code == 0){
							$('#testwechat2submit').prop('disabled', false);
						}
					}
				})
			}
		});

		// 保存企业微信配置信息
		$("#testwechat2submit").unbind().on('click', function(){
			if (setipform.validate().form()) {
				var jsonData = getWechat2Conf();
				Metronic.blockUI({target:'#testwechat2div', animate: true});
				pAjaxRequest(jsonData, '/api/v1/system/notice/wecom_conf', 'POST', function (res) {
					Metronic.unblockUI('#testwechat2div');
					if (operateResponseList(res, LANG.UI_WECOM_NOTICE_SETTING)){
						if(res.code == 0){
							$('#testwechat2div').modal('hide');
							$('#wechatcheck2').bootstrapSwitch('disabled', false);
						}
					}
				})
			}
		});
	};


	// 定义更新发送按钮状态的函数
	function updateSendButtonState() {
		var button = $('#sendtestwechat2'); // 获取发送测试按钮元素
		// 检查所有必填字段是否都有值（非空）
		if ($('input[name=wechat_core_id]').val() && // 企业微信ID
			$('input[name=wechat_app_id]').val() && // AppID
			$('input[name=wechat_app_secret]').val() && // AppSecret
			$('input[name=wechat_url]').val()) { // 回调URL
			// 所有字段已填写，启用按钮并设为绿色
			button.removeClass('btn-default disabled')
				.addClass('btn-success enabled')
				.prop('disabled', false)
		} else {
			// 任一必填字段为空，禁用按钮并设为灰色
			button.removeClass('btn-success enabled')
				.addClass('btn-default disabled')
				.prop('disabled', true)

		}
	}





	$('input[name=wechat_core_id], input[name=wechat_app_id], input[name=wechat_app_secret], input[name=wechat_url]').blur(function(){
		updateSendButtonState();  // 调用更新发送按钮状态
		updateSaveButtonState();  // 调用更新保存按钮状态
	});

// 防止页面刷新后，已填写的表单但按钮仍为禁用状态
	$(document).ready(function() {
		updateSendButtonState();
		updateSaveButtonState();
	});

	return {
		init: function () {
			//初始化默认选项
			initDefaultConf();
			//添加事件
			addListeners();
		}
	};
}();

jQuery(document).ready(function(){
	Settings_EnWechat_Notice.init();
});