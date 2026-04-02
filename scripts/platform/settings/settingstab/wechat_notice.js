//微信通知
var Settings_Wechat_Notice = function () {
	var wechatConf = {};
	var ipGrid;
	var init = 0;

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

	//设置微信通知默认配置
	var setWechatDefault = function(){
		//设置三个开关
		setSwitch(wechatConf.data.flag, 'wechatcheck', 'wechatcontent');
		setSwitch(wechatConf.data.system_flag, 'systemchecks2', 'systemchecks2div');
		setSwitch(wechatConf.data.task_flag, 'taskchecks2', 'taskchecks2div');
		//设置两个等级
		setNoticeLevel(wechatConf.data.system_flag, wechatConf.data.system_level, 'systemchecks2div');
		setNoticeLevel(wechatConf.data.task_flag, wechatConf.data.task_level, 'taskchecks2div');

		//如果是深信服及白牌OEM 通知模式屏蔽系统默认选项 不用提取语言包
		if(!CONF.SYSTEMNAME.includes("云祺") && !CONF.SYSTEMNAME.includes("Vinchin Backup")){
			$('select[name=wechat_mode]').find("option[value=1]").remove();
			wechatConf.data.config.wechat_mode = 2;
			wechatTypeChange();
		}

		//初始化测试配置
		var deviceConfig = wechatConf.data.config;

		if (deviceConfig.wechat_mode == 1) {
			// 系统默认
			$('#wechatsendtypedes').html($('#wechat_type').val());
			$('.show_wechat_type').hide();
		} else {
			$('#wechatsendtypedes').html($('#wechat_type2').val());
			$('.show_wechat_type').show();
		}

		$('select[name=wechat_mode]').on('change', wechatTypeChange);

		$('select[name=wechat_mode]').val(deviceConfig.wechat_mode);

		$('input[name=wechat_gh_id]').val(deviceConfig.gh_id);
		$('input[name=wechat_appid]').val(deviceConfig.appid);
		$('input[name=wechat_appsecret]').val(deviceConfig.appsecret);
		$('input[name=wechat_template_id]').val(deviceConfig.template_id);
		$('input[name=wechat_template_param1]').val(deviceConfig.template_param1);
		$('input[name=wechat_template_param2]').val(deviceConfig.template_param2);
		$('input[name=wechat_template_url]').val(deviceConfig.template_url);
		$('#auth_qrcode').attr('src', deviceConfig.auth_qrcode);
		$('#wechat_qrcode').attr('src', deviceConfig.wechat_qrcode);
		if (deviceConfig.template_url == '') {
			$('#auth_qrcode').parent().parent().hide();
			$('#wechat_qrcode').parent().parent().hide();
		}
		if(!CONF.SYSTEMNAME.includes("云祺") && !CONF.SYSTEMNAME.includes("Vinchin Backup")){
			$('#wechat_qrcode').parent().parent().hide();
		}
		//当未初始化的时候,需要先设置通知开关不可用
		if(!wechatConf.data.flag){
			$('#wechatcheck').bootstrapSwitch('disabled', true);
			$('#testwechatsubmit').prop('disabled', true);
		}

		$('input[name=wechat_gh_id]').blur(function(){
			// 在输入框失去焦点时执行的代码
			const gh_id = $('input[name=wechat_gh_id]').val();
			if (gh_id != '') {
				// 重新赋值公众号二维码
				checkAppChange();
			}
		});
		$('input[name=wechat_appid]').blur(function(){
			checkAppChange();
		});
		$('input[name=wechat_appsecret]').blur(function(){
			checkAppChange();
		});
		$('input[name=wechat_template_url]').blur(function(){
			if ($(this).val() == '') {
				// 重新赋值公众号二维码
				$('#auth_qrcode').attr('src', '');
				$('#wechat_qrcode').parent().parent().hide();
				$('#auth_qrcode').parent().parent().hide();
			}
		});
	}

	function updateButtonState() {
		var button = $('#sendtestwechat');
		if ($('input[name=wechat_gh_id]').val() &&
			$('input[name=wechat_appid]').val() &&
			$('input[name=wechat_appsecret]').val() &&
			$('input[name=wechat_template_url]').val()) {
			// 已配置，启用按钮并设为绿色
			button.removeClass('btn-default disabled')
				.addClass('btn-success enabled')
				.prop('disabled', false)

		} else {
			// 未配置，禁用按钮并设为灰色
			button.removeClass('btn-success enabled')
				.addClass('btn-default disabled')
				.prop('disabled', true)

		}
	}
	$('input[name=wechat_gh_id], input[name=wechat_appid], input[name=wechat_appsecret], input[name=wechat_template_url]').blur(function(){
		checkAppChange();
		updateButtonState(); // 调用更新按钮状态
	});

	// 监听appid和appsecret和回调域名的变化
	var checkAppChange = function (){
		const gh_id = $('input[name=wechat_gh_id]').val();
		const appid = $('input[name=wechat_appid]').val();
		const appsecret = $('input[name=wechat_appsecret]').val();
		const template_url = $('input[name=wechat_template_url]').val();

		if (gh_id !== '' && appid !== '' && appsecret !== '' && template_url !== '') {
			var wechat_mode = $('select[name=wechat_mode]').val();
			const data = {
				wechat_mode: wechat_mode,
				gh_id: gh_id,
				appid: appid,
				appsecret: appsecret,
				template_url: template_url,
			};

			Metronic.blockUI({target:'#testwechatform', animate: true});

			pAjaxRequest(data, '/api/v1/system/notice/wechat_qrcode', 'POST', function (res) {
				Metronic.unblockUI('#testwechatform');
				if(res.code == 0){
					var pData = res.data;



					//正确使用服务器返回的二维码 URL
					$('#wechat_qrcode').attr('src', pData.wechat_qrcode);
					$('#auth_qrcode').attr('src', pData.auth_qrcode);

					//显示二维码容器
					$('#wechat_qrcode').parent().parent().show();
					$('#auth_qrcode').parent().parent().show();

					updateButtonState(); // 更新按钮状态
				}
			});
		} else {
			// 如果有任意一个字段为空，则隐藏二维码并禁用按钮
			$('#wechat_qrcode').parent().parent().hide().css('display','block');
			$('#auth_qrcode').parent().parent().hide();
			updateButtonState();
		}
	};

	//类型改变事件
	var wechatTypeChange = function(){
		var type = $('select[name=wechat_mode]').val();
		let deviceConfig = wechatConf.conf;
		if (type == 1) {
			$('.show_wechat_type').hide();
			// 那么给系统的赋予默认的值
			$('input[name=wechat_gh_id]').val(deviceConfig.gh_id);
			$('input[name=wechat_appid]').val(deviceConfig.appid);
			$('input[name=wechat_appsecret]').val(deviceConfig.appsecret);
			$('input[name=wechat_template_id]').val(deviceConfig.template_id);
			$('input[name=wechat_template_param1]').val(deviceConfig.template_param1);
			$('input[name=wechat_template_param2]').val(deviceConfig.template_param2);
		} else {
			// 切换的时候做个判断，如果 wechat_gh_id 等于 默认的，那么就清除所有的 会一直进入这个判断需要优化缓存下自定义的配置
			if ($('input[name=wechat_gh_id]').val() == deviceConfig.gh_id) {
				$('input[name=wechat_gh_id]').val('');
				$('input[name=wechat_appid]').val('');
				$('input[name=wechat_appsecret]').val('');
				$('input[name=wechat_template_id]').val('');
				$('input[name=wechat_template_param1]').val('');
				$('input[name=wechat_template_param2]').val('');
				// 重新赋值公众号二维码
				$('#wechat_qrcode').attr('src', '');
				$('#auth_qrcode').attr('src', '');
				$('#wechat_qrcode').parent().parent().hide();
				$('#auth_qrcode').parent().parent().hide();
			}
			$('.show_wechat_type').show();
		}
		setTimeout(function (){
			checkAppChange();
		}, 1000);

	}

	//得到默认信息
	var initDefaultConf = function(){
		setSwitch(false, 'wechatcheck', 'wechatcontent'); //默认设置为关闭，防止页面闪烁
		Metronic.blockUI({target:'#wechattab', animate: true});
		pAjaxRequest({}, '/api/v1/system/notice/wechat', 'GET', function (res) {
			Metronic.unblockUI('#wechattab');
			if (res.code == 0) {
				wechatConf = res.data;
				setWechatDefault();
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

		$('#cencel3').on('click', () => {
			// 关闭当前任务页面提示tips时，动态调整表格高度
			$('.form-body').css('height', 'calc(100% + 50px)')
		});

		$("#wechatcancel").click(function(){
			CTLSIDEBAR('setting_manager');
			LOCATION('./content/platform/settings/setting_manager.php?tab=2', 'setting_manager');
		});

		$('#testwechat').on('click', testWechat);
		$('#testwechat_member').on('click', testWechatMember);
		$('#wechatsubmit').on('click', function(){
			Metronic.blockUI({target: '#wechattab',animate: true});
			var data = {};
			data.flag = $('#wechatcheck').bootstrapSwitch('state');
			data.system_flag = $('#systemchecks2').bootstrapSwitch('state');
			data.system_level = getLevelSetting('systemchecks2div');
			data.task_flag = $('#taskchecks2').bootstrapSwitch('state');
			data.task_level = getLevelSetting('taskchecks2div');

			Metronic.blockUI({target:'#wechattab', animate: true});
			pAjaxRequest(data, '/api/v1/system/notice/wechat', 'POST', function (res) {
				Metronic.unblockUI('#wechattab');
				if (operateResponseList(res, LANG.UI_WECHAT_NOTICE_SETTING)){

				}
			})
		});
	}

	//配置微信公众号测试
	var testWechat = function(){
		//$('#testwechatdiv').modal({'height': "750px"});
		$('#current_pending_task_drawer2').drawer('show');
		handleWechatValidation();
	}

	// 人员管理
	var testWechatMember = function(){
		initMemberInfo();//初始化用户
		$('#testwechatmemberdiv').modal({'width':"600px", 'height': "400px"});
	}

	$('#authClientRemove').on('click', function(){
		var selectedRow = $('#memberIpDatatable').bootstrapTable('getSelections');
		if (selectedRow.length == 0) {
			UIToastr.showWarning(LANG.UI_WECHAT_MANAGE_DELETE, LANG.UI_WECHAT_MANAGE_SELECT_TO_DELETE);
			return;
		}

		bootbox.dialog({
			title: LANG.UI_WECHAT_MANAGE_DELETE,
			message: LANG.UI_WECHAT_MANAGE_DELETE_CONFIRE,
			buttons: {
				cancel: {
					label: LANG.UI_GLOBAL_STRATEGY_DISPENSE_CANCEL,
					className: 'btn-default',
					callback: function(){
					}
				},
				ok: {
					label: LANG.UI_PUBLIC_CONFIRM,
					className: 'btn-primary',
					callback: function(){
						authClientRemove(selectedRow);
					}
				}
			}
		});
	});

	//取消人员授权
	var authClientRemove = function(select){
		var data = [];

		for (var j in select) {
			data.push(select[j].openid);
		}

		Metronic.blockUI({target:'#memberIpDatatable', animate: true});
		pAjaxRequest({openid: data}, '/api/v1/system/notice/wechat_user', 'DELETE', function (res) {
			Metronic.unblockUI('#memberIpDatatable');
			if (operateResponseList(res, LANG.UI_WECHAT_MANAGE_DELETE)){
				//删除成功删除列表
				$('#memberIpDatatable').bootstrapTable('refresh', {
					query: {offset:0}
				});
			}
		})
	}

	//配置微信公众号测试验证
	var handleWechatValidation = function() {
		var setipform = $('#testwechatform');

		setipform.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				wechat_gh_id: {
					required: true,
				},
				wechat_appid: {
					required: true,
				},
				wechat_appsecret: {
					required: true,
				},
				wechat_template_id: {
					required: true,
				},
				wechat_template_param1: {
					required: true,
				},
				wechat_template_param2: {
					required: true,
				},
				wechat_template_url: {
					required: true,
					ipv4v6: true
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

			},
		});
		//IP验证格式
		$.validator.addMethod("ipv4v6", function(value, element) {
			var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			//http|https
			var domainHTTP = this.optional( element ) || /^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);

			var returns = domain || ipv4 || domainHTTP || ipv4HTTP;
			if (returns) {
				checkAppChange();
			} else {
				$('#auth_qrcode').attr('src', '');
				$('#wechat_qrcode').parent().parent().hide();
				$('#auth_qrcode').parent().parent().hide();
			}

			return returns;
		}, LANG.UI_SETTING_INPUT_EXISTS_DOMAIN);

		var getWechatConf = function(mode = ''){
			const data = {};
			data.gh_id = $('input[name=wechat_gh_id]').val();
			data.appid = $('input[name=wechat_appid]').val();
			data.appsecret = $('input[name=wechat_appsecret]').val();
			data.template_id = $('input[name=wechat_template_id]').val();
			data.template_param1 = $('input[name=wechat_template_param1]').val();
			data.template_param2 = $('input[name=wechat_template_param2]').val();
			data.template_url = $('input[name=wechat_template_url]').val();
			data.wechat_mode = $('select[name=wechat_mode]').val();
			if (mode) {
				// 表示测试
				data.test_notice = 1;
			}
			return data;
		}

		//测试微信通知发送
		$('#sendtestwechat').unbind().on('click', function(){
			if(setipform.validate().form()) {
				var jsonData = getWechatConf('test');
				Metronic.blockUI({target:'#testwechatdiv', animate: true});
				pAjaxRequest(jsonData, '/api/v1/system/notice/test_wechat', 'POST', function (res) {
					Metronic.unblockUI('#testwechatdiv');
					if (operateResponseList(res, LANG.UI_WECHAT_NOTICE_SETTING)){
						if(res.code == 0){
							$('#testwechatsubmit').prop('disabled', false);
						}
					}
				})
			}
		});

		// 保存微信公众号配置信息
		$("#testwechatsubmit").unbind().on('click', function(){
			if (setipform.validate().form()) {
				var jsonData = getWechatConf();
				Metronic.blockUI({target:'#testwechatdiv', animate: true});
				pAjaxRequest(jsonData, '/api/v1/system/notice/wechat_conf', 'POST', function (res) {
					Metronic.unblockUI('#testwechatdiv');
					if (operateResponseList(res, LANG.UI_WECHAT_NOTICE_SETTING)){
						if(res.code == 0){
							$('#testwechatdiv').modal('hide');
							$('#wechatcheck').bootstrapSwitch('disabled', false);
							if ($('select[name=wechat_mode]').val() == 1) {
								$('#wechatsendtypedes').html($('#wechat_type').val());
							} else {
								$('#wechatsendtypedes').html($('#wechat_type2').val());
							}
						}
					}
				})
			}
		});

		// 取消微信公众号保存设置
		$("#closewechat").unbind().on('click', function(){
			let deviceConfig = wechatConf.data.config
			$('select[name=wechat_mode]').val(deviceConfig.wechat_mode)
			wechatTypeChange()
			$('#testwechatdiv').modal('hide');
		});

	};

	var initMemberInfo = function () {
		const options = {
			toolbarId: '#vin_current_toolbar_network_vm',
			buttonsToolbar: '#vin_current_toolbar_network_vm .vin_btnToolbar',
			vin_url: "/api/v1/system/notice/wechat_user",
			vin_method: "GET",
			pagination: true, //分页
			paginationParts: ['pageInfo', 'pageList'],
			pageList: [5, 10], //每页数量
			resizable: true, //可变宽度
			columns: [
				{
					checkbox: true,
					sortable: false,
					disabled: true,
				},
				{
					field: 'user_name',
					title: LANG.UI_VM_MACHINE_NAME,
					sortable: false,
					align: 'center',
				},
				{
					field: 'openid',
					title: LANG.UI_VM_MACHINE_MEMS,
					sortable: false,
					align: 'center',
				},
				{
					field: 'headimg_url',
					title: LANG.UI_PUBLIC_STATUS,
					sortable: false,
					align: 'center',
					formatter: statusFormatter,
				}
			],
		};
		// 状态
		function statusFormatter(value, row, index) {
			return '<a href="'+ row.href_url+'" target="_blank" title="click to view"><img style="height: 50px;" src="' +row.headimg_url + '"></a>';
		}
		$('#memberIpDatatable').baseTableConfig().init(options);
	};

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
	Settings_Wechat_Notice.init();
});